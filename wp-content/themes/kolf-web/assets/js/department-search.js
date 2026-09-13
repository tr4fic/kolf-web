/**
 * Živé vyhledávání v oddělení / lékařích na úvodní stránce.
 * Čistý JS, žádná knihovna. Data (kolfDepartments) jsou předána přes wp_localize_script.
 */
( function () {
	'use strict';

	function stripDiacritics( str ) {
		return str
			.toLowerCase()
			.normalize( 'NFD' )
			.replace( /[̀-ͯ]/g, '' );
	}

	function escapeHtml( str ) {
		var div = document.createElement( 'div' );
		div.textContent = str;
		return div.innerHTML;
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		var input   = document.getElementById( 'kolf-search-input' );
		var results = document.getElementById( 'kolf-search-results' );
		var data    = ( typeof kolfDepartments !== 'undefined' ) ? kolfDepartments : [];

		if ( ! input || ! results ) {
			return;
		}

		var index = data.map( function ( d ) {
			return {
				name: d.name,
				meta: d.meta,
				url: d.url,
				needle: stripDiacritics( d.name + ' ' + d.meta ),
			};
		} );

		function render( query ) {
			var q = stripDiacritics( query.trim() );

			if ( ! q ) {
				results.classList.remove( 'is-open' );
				results.innerHTML = '';
				return;
			}

			var matches = index.filter( function ( d ) {
				return d.needle.indexOf( q ) !== -1;
			} ).slice( 0, 6 );

			if ( 0 === matches.length ) {
				var i18n = ( typeof kolfSearchI18n !== 'undefined' ) ? kolfSearchI18n : {};
				results.innerHTML = '<div class="kolf-search__empty">' + escapeHtml( i18n.noResults || 'Nic jsme nenašli.' ) + '</div>';
			} else {
				results.innerHTML = matches.map( function ( d ) {
					return (
						'<a class="kolf-search__result" href="' + d.url + '">' +
							'<span class="kolf-search__result-name">' + escapeHtml( d.name ) + '</span>' +
							'<span class="kolf-search__result-meta">' + escapeHtml( d.meta ) + '</span>' +
						'</a>'
					);
				} ).join( '' );
			}

			results.classList.add( 'is-open' );
		}

		input.addEventListener( 'input', function () {
			render( input.value );
		} );

		document.addEventListener( 'click', function ( e ) {
			if ( ! e.target.closest( '.kolf-search' ) ) {
				results.classList.remove( 'is-open' );
			}
		} );
	} );
} )();
