/**
 * Shared block interactivity: accordions, tabs.
 * Only enqueued when a block calls cn_block_needs_js() in its render.php.
 */
( function () {
	'use strict';

	function init( root ) {
		// FAQ accordions.
		root.querySelectorAll( '.faq__question' ).forEach( function ( btn ) {
			if ( btn.dataset.cnBound ) {
				return;
			}
			btn.dataset.cnBound = '1';
			btn.addEventListener( 'click', function () {
				var item = btn.closest( '.faq__item' );
				var expanded = btn.getAttribute( 'aria-expanded' ) === 'true';
				btn.setAttribute( 'aria-expanded', expanded ? 'false' : 'true' );
				item.classList.toggle( 'is-open', ! expanded );
			} );
		} );

		// Tabs.
		root.querySelectorAll( '.tabs' ).forEach( function ( tabs ) {
			if ( tabs.dataset.cnBound ) {
				return;
			}
			tabs.dataset.cnBound = '1';
			var buttons = tabs.querySelectorAll( '.tabs__button' );
			var panels = tabs.querySelectorAll( '.tabs__panel' );

			buttons.forEach( function ( btn, i ) {
				btn.addEventListener( 'click', function () {
					buttons.forEach( function ( b ) {
						b.classList.remove( 'is-active' );
						b.setAttribute( 'aria-selected', 'false' );
					} );
					panels.forEach( function ( p ) {
						p.hidden = true;
					} );
					btn.classList.add( 'is-active' );
					btn.setAttribute( 'aria-selected', 'true' );
					if ( panels[ i ] ) {
						panels[ i ].hidden = false;
					}
				} );
			} );
		} );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		init( document );
	} );

	// Re-init inside the block editor after previews render.
	if ( window.acf ) {
		window.acf.addAction( 'render_block_preview', function ( $el ) {
			init( $el[ 0 ] || document );
		} );
	}
} )();
