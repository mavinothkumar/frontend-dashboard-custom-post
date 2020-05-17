jQuery( document ).ready( function ( $ ) {
		var b = $( '.bc_fed' );
		var fed_dashboard_item_field_wrapper = $( '.fed_dashboard_item_field_wrapper' );
		b.on( 'keyup', '.fed_convert_space_to_underscore', function ( e ) {
			var value, original_value;
			value = original_value = $( this ).val();
			if ( e.keyCode !== 9 && e.keyCode !== 37 && e.keyCode !== 38 && e.keyCode !== 39 && e.keyCode !== 40 ) {
				value = value.replace( / /g, "_" );
				value = value.toLowerCase();
				value = replaceDiacritics( value );
				value = transliterate( value );
				value = replaceSpecialCharacters( value );
				if ( value !== original_value ) {
					$( this ).attr( 'value', value );
				}
			}
		} );

		b.on( 'click', '.fd_cp_custom_post_delete', function ( e ) {
			var btn = $( this );
			swal( {
				title: "Are you sure?",
				text: "You want to do delete this custom post type?",
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#DD6B55",
				confirmButtonText: "Yes, Please Proceed",
				cancelButtonText: "No, Cancel it"
			} ).then(
				function () {
					fed_toggle_loader();
					$.ajax( {
						type: 'POST',
						url: btn.data( 'url' ),
						data: { 'id': btn.data( 'id' ) },
						success: function ( results ) {
							fed_toggle_loader();
							fedAdminAlert.adminSettings( results );
						}

					} );
				}, function ( dismiss ) {
					if ( dismiss === 'cancel' ) {
						swal( {
								title: "Cancelled",
								type: "error",
								confirmButtonColor: '#0AAAAA'
							}
						);
					}
				} );
			e.preventDefault();
		} );

		$( '.fed_post_status_on_hover' ).hide();

		fed_dashboard_item_field_wrapper.on( 'mouseover', function () {
			$( this ).find( '.fed_post_status_on_hover' ).show();
		} );

		fed_dashboard_item_field_wrapper.on( 'mouseleave', function () {
			$( this ).find( '.fed_post_status_on_hover' ).hide();
		} );

		function fed_toggle_loader() {
			$( '.preview-area' ).toggleClass( 'hide' );
		}
	}
);


function replaceDiacritics( s ) {
	var diacritics = [
		/[\300-\306]/g, /[\340-\346]/g,  // A, a
		/[\310-\313]/g, /[\350-\353]/g,  // E, e
		/[\314-\317]/g, /[\354-\357]/g,  // I, i
		/[\322-\330]/g, /[\362-\370]/g,  // O, o
		/[\331-\334]/g, /[\371-\374]/g,  // U, u
		/[\321]/g, /[\361]/g, // N, n
		/[\307]/g, /[\347]/g  // C, c
	];

	var chars = [ 'A', 'a', 'E', 'e', 'I', 'i', 'O', 'o', 'U', 'u', 'N', 'n', 'C', 'c' ];

	for ( var i = 0; i < diacritics.length; i++ ) {
		s = s.replace( diacritics[ i ], chars[ i ] );
	}

	return s;
}

function transliterate( word ) {
	var cyrillic = {
		"Ё": "YO",
		"Й": "I",
		"Ц": "TS",
		"У": "U",
		"К": "K",
		"Е": "E",
		"Н": "N",
		"Г": "G",
		"Ш": "SH",
		"Щ": "SCH",
		"З": "Z",
		"Х": "H",
		"Ъ": "'",
		"ё": "yo",
		"й": "i",
		"ц": "ts",
		"у": "u",
		"к": "k",
		"е": "e",
		"н": "n",
		"г": "g",
		"ш": "sh",
		"щ": "sch",
		"з": "z",
		"х": "h",
		"ъ": "'",
		"Ф": "F",
		"Ы": "I",
		"В": "V",
		"А": "a",
		"П": "P",
		"Р": "R",
		"О": "O",
		"Л": "L",
		"Д": "D",
		"Ж": "ZH",
		"Э": "E",
		"ф": "f",
		"ы": "i",
		"в": "v",
		"а": "a",
		"п": "p",
		"р": "r",
		"о": "o",
		"л": "l",
		"д": "d",
		"ж": "zh",
		"э": "e",
		"Я": "Ya",
		"Ч": "CH",
		"С": "S",
		"М": "M",
		"И": "I",
		"Т": "T",
		"Ь": "'",
		"Б": "B",
		"Ю": "YU",
		"я": "ya",
		"ч": "ch",
		"с": "s",
		"м": "m",
		"и": "i",
		"т": "t",
		"ь": "'",
		"б": "b",
		"ю": "yu"
	};
	return word.split( '' ).map( function ( char ) {
		return cyrillic[ char ] || char;
	} ).join( "" );
}

function replaceSpecialCharacters( s ) {

	s = s.replace( /[^a-z0-9\s]/gi, '_' );

	return s;
}
