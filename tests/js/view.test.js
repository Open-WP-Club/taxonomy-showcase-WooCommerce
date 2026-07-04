'use strict';

// jsdom does not implement IntersectionObserver — provide a no-op stub so the
// IIFE in view.js can attach its scroll-spy without throwing.
global.IntersectionObserver = class IntersectionObserver {
	constructor( callback ) {}
	observe() {}
	unobserve() {}
	disconnect() {}
};

// ── helpers ───────────────────────────────────────────────────────────────────

function fireInput( input, value ) {
	input.value = value;
	input.dispatchEvent( new Event( 'input' ) );
}

function loadView() {
	jest.resetModules();
	require( '../../src/taxonomy-grid/view' );
}

// ── live search (flat grid) ────────────────────────────────────────────────────

describe( 'live search — flat grid', () => {
	beforeEach( () => {
		document.body.innerHTML = `
			<div class="wtb-taxonomy-grid" data-search="1">
				<div class="wtb-search">
					<input class="wtb-search__input" type="search" />
				</div>
				<a class="wtb-card"><span class="wtb-card__name">Accessories</span></a>
				<a class="wtb-card"><span class="wtb-card__name">Bags</span></a>
				<a class="wtb-card"><span class="wtb-card__name">Clothes</span></a>
			</div>
		`;
		loadView();
	} );

	it( 'hides cards that do not match the query', () => {
		const input = document.querySelector( '.wtb-search__input' );
		fireInput( input, 'bag' );

		const [ accessories, bags, clothes ] = document.querySelectorAll( '.wtb-card' );
		expect( accessories.hidden ).toBe( true );
		expect( bags.hidden ).toBe( false );
		expect( clothes.hidden ).toBe( true );
	} );

	it( 'shows all cards when the query is cleared', () => {
		const input = document.querySelector( '.wtb-search__input' );
		fireInput( input, 'bag' );
		fireInput( input, '' );

		document.querySelectorAll( '.wtb-card' ).forEach( ( card ) => {
			expect( card.hidden ).toBe( false );
		} );
	} );

	it( 'is case-insensitive', () => {
		const input = document.querySelector( '.wtb-search__input' );
		fireInput( input, 'BAGS' );

		const [ accessories, bags, clothes ] = document.querySelectorAll( '.wtb-card' );
		expect( accessories.hidden ).toBe( true );
		expect( bags.hidden ).toBe( false );
		expect( clothes.hidden ).toBe( true );
	} );

	it( 'hides all cards when nothing matches', () => {
		fireInput( document.querySelector( '.wtb-search__input' ), 'zzz' );

		document.querySelectorAll( '.wtb-card' ).forEach( ( card ) => {
			expect( card.hidden ).toBe( true );
		} );
	} );

	it( 'matches partial substrings', () => {
		fireInput( document.querySelector( '.wtb-search__input' ), 'cess' );

		const [ accessories, bags, clothes ] = document.querySelectorAll( '.wtb-card' );
		expect( accessories.hidden ).toBe( false ); // "Accessories" contains "cess"
		expect( bags.hidden ).toBe( true );
		expect( clothes.hidden ).toBe( true );
	} );
} );

// ── live search in alphabet mode ──────────────────────────────────────────────

describe( 'live search — alphabet mode', () => {
	beforeEach( () => {
		document.body.innerHTML = `
			<div class="wtb-taxonomy-grid" data-alpha="1" data-search="1">
				<nav class="wtb-alpha-nav">
					<a class="wtb-alpha-nav__btn" href="#grp-A" data-letter="A">A</a>
					<a class="wtb-alpha-nav__btn" href="#grp-B" data-letter="B">B</a>
				</nav>
				<div class="wtb-search">
					<input class="wtb-search__input" type="search" />
				</div>
				<div class="wtb-letter-group" id="grp-A" data-letter="A">
					<a class="wtb-card"><span class="wtb-card__name">Accessories</span></a>
				</div>
				<div class="wtb-letter-group" id="grp-B" data-letter="B">
					<a class="wtb-card"><span class="wtb-card__name">Bags</span></a>
					<a class="wtb-card"><span class="wtb-card__name">Belts</span></a>
				</div>
			</div>
		`;
		loadView();
	} );

	it( 'hides a letter group when all its cards are filtered out', () => {
		fireInput( document.querySelector( '.wtb-search__input' ), 'belt' );

		expect( document.querySelector( '.wtb-letter-group[data-letter="A"]' ).hidden ).toBe( true );
		expect( document.querySelector( '.wtb-letter-group[data-letter="B"]' ).hidden ).toBe( false );
	} );

	it( 'adds is-empty to nav buttons whose group is fully hidden', () => {
		fireInput( document.querySelector( '.wtb-search__input' ), 'belt' );

		const navA = document.querySelector( '.wtb-alpha-nav a[data-letter="A"]' );
		const navB = document.querySelector( '.wtb-alpha-nav a[data-letter="B"]' );
		expect( navA.classList.contains( 'is-empty' ) ).toBe( true );
		expect( navB.classList.contains( 'is-empty' ) ).toBe( false );
	} );

	it( 'restores hidden groups and nav buttons when query is cleared', () => {
		const input = document.querySelector( '.wtb-search__input' );
		fireInput( input, 'belt' );
		fireInput( input, '' );

		expect( document.querySelector( '.wtb-letter-group[data-letter="A"]' ).hidden ).toBe( false );

		const navA = document.querySelector( '.wtb-alpha-nav a[data-letter="A"]' );
		expect( navA.classList.contains( 'is-empty' ) ).toBe( false );
	} );

	it( 'keeps a group visible when at least one card matches', () => {
		fireInput( document.querySelector( '.wtb-search__input' ), 'bag' );

		// Group B has "Bags" (match) and "Belts" (no match) → group stays visible
		expect( document.querySelector( '.wtb-letter-group[data-letter="B"]' ).hidden ).toBe( false );
		// "Belts" is hidden, "Bags" is not
		const [ bags, belts ] = document.querySelectorAll( '.wtb-letter-group[data-letter="B"] .wtb-card' );
		expect( bags.hidden ).toBe( false );
		expect( belts.hidden ).toBe( true );
	} );
} );
