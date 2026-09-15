/**
 * Živé filtrování už vypsaných položek na /oddeleni/ a /osoby/ (seskupených
 * podle patra, resp. písmene příjmení) — na rozdíl od department-search.js
 * nejde o rozbalovací našeptávač s vlastním seznamem výsledků, ale o
 * skrývání/zobrazování řádků přímo ve stávající struktuře podle toho, co
 * uživatel píše (porovnává se s atributem data-search na každém řádku).
 */
( function () {
	'use strict';

	function stripDiacritics( str ) {
		return str
			.toLowerCase()
			.normalize( 'NFD' )
			.replace( /[̀-ͯ]/g, '' );
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var input  = document.getElementById( 'kolf-search-input' );
		var groups = document.querySelectorAll( '.kolf-directory__group' );
		var empty  = document.querySelector( '.kolf-directory__empty' );

		if ( ! input || ! groups.length ) {
			return;
		}

		function filter() {
			var q             = stripDiacritics( input.value.trim() );
			var visibleTotal  = 0;

			groups.forEach( function ( group ) {
				var rows          = group.querySelectorAll( '.kolf-directory__row' );
				var visibleInGroup = 0;

				rows.forEach( function ( row ) {
					var needle = stripDiacritics( row.getAttribute( 'data-search' ) || '' );
					var match  = ! q || needle.indexOf( q ) !== -1;
					row.hidden = ! match;
					if ( match ) {
						visibleInGroup++;
					}
				} );

				group.hidden = 0 === visibleInGroup;
				visibleTotal += visibleInGroup;
			} );

			if ( empty ) {
				empty.hidden = visibleTotal > 0;
			}
		}

		input.addEventListener( 'input', filter );
	} );
}() );
