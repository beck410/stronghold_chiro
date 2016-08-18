var tribe_eventbrite = {

	existing_events_opts: {
		ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
			url: ajaxurl,
			dataType: 'json',
			quietMillis: 250,
			data: function ( term, page ) {
				return {
					action: 'tribe_eb_search_existing',
					q: term, // search term
				};
			},
			results: function (data, page) { // parse the results into the format expected by Select2.
				// since we are using custom formatting functions we do not need to alter the remote JSON data
				return { results: data.items };
			}
		}
	}
};

(function( $, my ) {
	'use strict';

	my.init = function() {
		my.$selector = $( document.getElementById( 'select-eventbrite-existing' ) );
		my.$text_id = $( document.getElementById( 'eventbrite_id' ) );

		my.existing_events_opts.placeholder = my.$selector.data( 'placeholder' );

		my.$selector.select2( my.existing_events_opts );

		my.$text_id.on( 'keyup', function() {
			my.$text_id.next( 'p' ).hide();
			if ( '' != my.$text_id.val() && '' != my.$selector.select2( 'val' ) ) {
				my.$text_id.next( 'p' ).show();
			}
		} );
	};

	$( document ).ready( my.init );
})( jQuery, tribe_eventbrite );
