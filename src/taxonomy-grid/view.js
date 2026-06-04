(function () {
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
}() );
