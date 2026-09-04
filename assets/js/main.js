/**
 * Frontend scripts — vanilla JS, no dependencies.
 */
( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		// Mobile navigation toggle.
		var nav = document.querySelector( '.site-nav' );
		var toggle = document.querySelector( '.site-nav__toggle' );

		if ( nav && toggle ) {
			toggle.addEventListener( 'click', function () {
				var isOpen = nav.classList.toggle( 'is-open' );
				toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
			} );
		}

		// Smooth scroll for same-page anchors (respects reduced motion via CSS).
		document.querySelectorAll( 'a[href^="#"]:not([href="#"])' ).forEach( function ( link ) {
			link.addEventListener( 'click', function ( e ) {
				var target = document.querySelector( link.getAttribute( 'href' ) );
				if ( target ) {
					e.preventDefault();
					target.scrollIntoView( { behavior: 'smooth' } );
				}
			} );
		} );
	} );
} )();
