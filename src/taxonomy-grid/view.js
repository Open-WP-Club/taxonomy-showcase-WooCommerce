(function () {

	// ── Alphabet index ────────────────────────────────────────────────────────

	document.querySelectorAll( '.wtb-taxonomy-grid[data-alpha]' ).forEach( function ( grid ) {
		var groups   = Array.from( grid.querySelectorAll( '.wtb-letter-group' ) );
		var navLinks = Array.from( grid.querySelectorAll( '.wtb-alpha-nav a[data-letter]' ) );

		if ( ! groups.length || ! navLinks.length ) return;

		// Highlight the nav letter whose section is nearest the top of the viewport.
		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						var letter = entry.target.dataset.letter;
						navLinks.forEach( function ( link ) {
							link.classList.toggle( 'is-current', link.dataset.letter === letter );
						} );
					}
				} );
			},
			{ rootMargin: '-20% 0px -70% 0px' }
		);

		groups.forEach( function ( group ) { observer.observe( group ); } );

		// Smooth scroll on click (CSS scroll-behavior is a fallback).
		navLinks.forEach( function ( link ) {
			link.addEventListener( 'click', function ( e ) {
				var target = document.getElementById(
					link.getAttribute( 'href' ).replace( '#', '' )
				);
				if ( ! target ) return;
				e.preventDefault();
				target.scrollIntoView( { behavior: 'smooth', block: 'start' } );
			} );
		} );
	} );

	// ── Live search ───────────────────────────────────────────────────────────

	document.querySelectorAll( '.wtb-taxonomy-grid[data-search]' ).forEach( function ( grid ) {
		var input    = grid.querySelector( '.wtb-search__input' );
		var isAlpha  = grid.hasAttribute( 'data-alpha' );
		var navLinks = Array.from( grid.querySelectorAll( '.wtb-alpha-nav a[data-letter]' ) );

		if ( ! input ) return;

		input.addEventListener( 'input', function () {
			var query = input.value.trim().toLowerCase();
			var cards = Array.from( grid.querySelectorAll( '.wtb-card' ) );

			// Show / hide individual cards.
			cards.forEach( function ( card ) {
				var name    = ( card.querySelector( '.wtb-card__name' ) || {} ).textContent || '';
				card.hidden = query.length > 0 && name.trim().toLowerCase().indexOf( query ) === -1;
			} );

			// In alphabet mode: hide empty letter groups and dim their nav buttons.
			if ( isAlpha ) {
				grid.querySelectorAll( '.wtb-letter-group' ).forEach( function ( group ) {
					var hasVisible = Array.from( group.querySelectorAll( '.wtb-card' ) )
						.some( function ( c ) { return ! c.hidden; } );
					group.hidden = ! hasVisible;

					var letter = group.dataset.letter;
					navLinks.forEach( function ( link ) {
						if ( link.dataset.letter === letter ) {
							link.classList.toggle( 'is-empty', ! hasVisible );
						}
					} );
				} );
			}
		} );
	} );

}() );
