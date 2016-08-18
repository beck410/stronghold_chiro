

(function() {
	tinymce.PluginManager.add('tp_carosuel_button', function( editor, url ) {
		editor.addButton( 'tp_carosuel_button', {
			icon: 'chukku',
			type: 'menubutton',
			title : 'Smart Carousel',
					menu: [
						{
							text: 'Smart Carousel',
							onclick: function() {
								editor.windowManager.open( {
									title: 'Insert Carosul Shortcode',
									width: 600,
									height: 440,
									body: [
										{
											type: 'textbox',
											name: 'carosulID',
											label: 'Carosuel ID',
											value: 'Write Anything'
											
										},
										{
											type: 'listbox',
											name: 'Carosuelitems',
											label: 'Select Items',
											'values': [
												{text: '1', value: '1'},
												{text: '2', value: '2'},
												{text: '3', value: '3'},
												{text: '4', value: '4'},
												{text: '5', value: '5'},
												{text: '6', value: '6'},
												{text: '7', value: '7'},
												{text: '8', value: '8'},
												{text: '9', value: '9'},
												{text: '10', value: '10'}
											]
										},
										{
											type: 'listbox',
											name: 'Carosuelpagination',
											label: 'Select Pagination',
											'values': [
												{text: 'On', value: 'true'},
												{text: 'Off', value: 'false'}
											]
										},
										{
											type: 'listbox',
											name: 'Carosuelnavigation',
											label: 'Carosuel Navigation',
											'values': [
												{text: 'Off', value: 'false'},			
												{text: 'on', value: 'true'}
											]
										},
										{
											type: 'listbox',
											name: 'carosulAutoplay',
											label: 'Autoplay',
											'values': [
												{text: 'On', value: 'true'},
												{text: 'Off', value: 'false'}
											]
										},
										{
											type: 'listbox',
											name: 'Carosulcontentstyle',
											label: 'Carosuel Content',
											'values': [
												{text: 'off', value: 'none'},
												{text: 'on', value: 'block'}
											]
										},
										{
											type: 'listbox',
											name: 'carosulMargin',
											label: 'Carosuel Margin',
											'values': [
												{text: '0px', value: '0px'},
												{text: '1px', value: '1px'},
												{text: '2px', value: '2px'},
												{text: '3px', value: '3px'},
												{text: '4px', value: '4px'},
												{text: '5px', value: '5px'},
												{text: '6px', value: '6px'},
												{text: '7px', value: '7px'},
												{text: '8px', value: '8px'}
											]
										},
										{
											type: 'listbox',
											name: 'CarosuelPostType',
											label: 'Carosul Type',
											'values': [
												{text: 'Custom Post', value: 'tp-carousel-items'},
												{text: 'Post', value: 'Post'}
											]
										},
										{
											type: 'textbox',
											name: 'CarosuelPostCategory',
											label: 'Post Category'
										},
										{
											type: 'textbox',
											name: 'carouselcustomCategory',
											label: 'Custom Category'
										}										
									],
									onsubmit: function( e ) {
										editor.insertContent( '[tp_carousel id="' + e.data.carosulID + '" items="' + e.data.Carosuelitems + '" pagination="' + e.data.Carosuelpagination + '" navigation="' + e.data.Carosuelnavigation + '" autoplay="' + e.data.carosulAutoplay + '" margin="' + e.data.carosulMargin + '" post_type="' + e.data.CarosuelPostType + '" post_category="' + e.data.CarosuelPostCategory + '" custom_category="' + e.data.carouselcustomCategory + '" content_style="' + e.data.Carosulcontentstyle + '"]');
									}
								});
							}
						}
					]
		});
	});
})();

