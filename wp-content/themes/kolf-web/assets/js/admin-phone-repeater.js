/**
 * Tlačítko "+ Přidat číslo" u opakovatelného seznamu telefonů (oddělení / osoba).
 * Čistý JS, jen pro admin obrazovky — viz inc/meta-boxes.php.
 */
( function () {
	'use strict';

	document.addEventListener( 'click', function ( e ) {
		var button = e.target.closest( '.kolf-add-phone' );
		if ( ! button ) {
			return;
		}
		e.preventDefault();

		var repeater = button.previousElementSibling;
		if ( ! repeater || ! repeater.classList.contains( 'kolf-phone-repeater' ) ) {
			return;
		}

		var rows = repeater.querySelectorAll( '.kolf-phone-row' );
		var lastRow = rows[ rows.length - 1 ];
		var clone = lastRow.cloneNode( true );
		clone.querySelectorAll( 'input' ).forEach( function ( input ) {
			input.value = '';
		} );
		repeater.appendChild( clone );
	} );
} )();
