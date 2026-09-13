/**
 * Tlačítko "+ Přidat rozvrh" u opakovatelného seznamu ordinačních/provozních
 * hodin (oddělení / osoba). Čistý JS, jen pro admin obrazovky — viz inc/meta-boxes.php.
 *
 * Na rozdíl od telefonů má každý řádek vlastní klíč přímo v názvu polí
 * (kolf_hours[KLIC][den][od1] apod.), takže při klonování bloku je potřeba
 * tenhle klíč ve všech name atributech přepsat na nový, unikátní.
 */
( function () {
	'use strict';

	function blockKey( block ) {
		var input = block.querySelector( '[name^="kolf_hours["]' );
		if ( ! input ) {
			return null;
		}
		var match = input.name.match( /^kolf_hours\[([^\]]+)\]/ );
		return match ? match[ 1 ] : null;
	}

	document.addEventListener( 'click', function ( e ) {
		var button = e.target.closest( '.kolf-add-hours' );
		if ( ! button ) {
			return;
		}
		e.preventDefault();

		var repeater = button.previousElementSibling;
		if ( ! repeater || ! repeater.classList.contains( 'kolf-hours-repeater' ) ) {
			return;
		}

		var blocks = repeater.querySelectorAll( '.kolf-hours-block' );
		var lastBlock = blocks[ blocks.length - 1 ];
		var oldKey = blockKey( lastBlock );
		if ( null === oldKey ) {
			return;
		}
		var newKey = 'new' + Date.now();

		var clone = lastBlock.cloneNode( true );
		clone.querySelectorAll( '[name]' ).forEach( function ( el ) {
			el.name = el.name.replace( 'kolf_hours[' + oldKey + ']', 'kolf_hours[' + newKey + ']' );
			if ( 'SELECT' === el.tagName ) {
				el.value = 'ordinacni';
			} else {
				el.value = '';
			}
		} );
		repeater.appendChild( clone );
	} );
} )();
