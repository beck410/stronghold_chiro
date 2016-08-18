jQuery(document).ready(function($){

	// hide/show EventBrite fields
	$('.EBForm').hide();

	if ( $("#EventBriteToggleOn:checked").length ) {
		$(".EBForm").show();
	}

	$("#EventBriteToggleOn").click(function(){
		$(".EBForm").slideDown('slow');
	});
	$("#EventBriteToggleOff").click(function(){
		$(".EBForm").slideUp(200);
	});

	var paymentType = $("input[name='EventBriteIsDonation']:checked");

	if ( $('.EBForm:visible').length > 0 && paymentType.val() == 0 ) {
	    $('.eb-tec-payment-options').show(0);
	} else if ( paymentType.val() == 1 ) {
		$('.eb-tec-payment-options').show(0);
	} else {
			$('.eb-tec-payment-options').hide(0);
	}

	var togglePaymentOptions = function(){
		if ( $("#EventBriteToggleOn:checked").length === 0 ){
			return;
		}

	    var paymentType = $("input[name='EventBriteIsDonation']:checked");
		if ( paymentType.val() == 0 ) {
			$('.eb-tec-payment-options').show(0);
			$('#EventBriteEventCost').parents('tr').show(0);
		} else if ( paymentType.val() == 1 ) {
			$('#EventBriteEventCost').parents('tr').hide(0);
			$('.eb-tec-payment-options').show(0);
		} else {
			$('#EventBriteEventCost').parents('tr').hide(0);
			$('.eb-tec-payment-options').hide(0);
		}
	}

	togglePaymentOptions();

	$("input[name='EventBriteIsDonation']").change( togglePaymentOptions );

	// hide/show additional payment option fields
	var ebTecAcceptPaymentInputs = $('#eb-tec-payment-options-checkboxes input');
	if( ebTecAcceptPaymentInputs.is(':checked') ){
		$(".tec-eb-offline-pay-options").show();
	} else {
		$(".tec-eb-offline-pay-options").hide();
	}
	function ebTecShowHideAdditionalPaymentOptions(event) {
		if ( event && $('.EBForm:visible').length > 0 ) {
			var divIndex = ebTecAcceptPaymentInputs.index(this);
			var notSelectedIndex = ebTecAcceptPaymentInputs.index( $('#eb-tec-payment-options-checkboxes input:radio:not(:checked)') );
			if(this.checked) {
				$('.eb-tec-payment-instructions:eq('+divIndex+')').slideDown(200);
				$(".tec-eb-offline-pay-options").show();
			} else {
			 $('.eb-tec-payment-instructions:eq('+divIndex+')').slideUp(200);
			}
		$('#eb-tec-payment-options-checkboxes input:radio:not(:checked)').each(function(index) {
		 var notSelectedIndex = ebTecAcceptPaymentInputs.index($(this));
		 if(notSelectedIndex >= 0)
		 $('.eb-tec-payment-instructions:eq('+notSelectedIndex+')').slideUp(200)
		});
		} else {
			$.each('#eb-tec-payment-options-checkboxes ~ #eb-tec-payment-options div', function() {
				var thisInput = $(this).find('input');
				if(thisInput.val() != null) {
					thisInput.closest('div').slideDown(200);
					$(".tec-eb-offline-pay-options").show();
				}
			});
		}
		$('.eb-tec-payment-details td').css('display', $('#eb-tec-payment-options-checkboxes input:checked').not('#EventBritePayment_accept_online-none').size() > 0 ? 'table-cell' : 'none');
	}

	ebTecAcceptPaymentInputs.bind('focus click', ebTecShowHideAdditionalPaymentOptions);
	ebTecAcceptPaymentInputs.focus();
	$('#title').focus();

	// Define error checking routine on submit
	$("form[name='post']").submit(function() {
			var EventStartDate = $("#EventStartDate").val();

			var currentDate = new Date();
			var EventDate = new Date();
			if( $("input[name='EventRegister']:checked").val() == 'yes' &&  (typeof( EventStartDate ) == 'undefined' || !EventStartDate.length || EventDate.toDateString() < currentDate.toDateString())) {
				alert("<?php esc_attr_e( 'Eventbrite only allows events to be saved that start in the future.', 'tribe-eventbrite' ) ?>");

				$('#EventStartDate').focus();
				return false;
			}

	});

	$("form[name='post']").submit(function() {
		var ticket_name = $("input[name='EventBriteTicketName']").val();
		if( $("#EventBriteToggleOn").attr('checked') == true && typeof( ticket_name ) != 'undefined' ) {
			var ticket_price = $("input[name='EventBriteEventCost']").val();
			var ticket_quantity = $("input[name='EventBriteTicketQuantity']").val();
			var is_donation = $("input[name='EventBriteIsDonation']:checked").val();
			if( typeof( ticket_name ) == 'undefined' || !ticket_name.length ) {
				alert("<?php esc_attr_e( 'Please provide a ticket name for the Eventbrite ticket.', 'tribe-eventbrite' ); ?>");
				$("input[name='EventBriteTicketName']").focus();
				return false;
			}
			if( !ticket_price.length && !is_donation) {
				alert("<?php esc_attr_e( 'You must set a price for the ticket', 'tribe-eventbrite' ); ?>" + ticket_name);
				$("input[name='EventBriteEventCost']").focus();
				return false;
			}
			if( (parseInt(ticket_quantity) == 0 || isNaN(parseInt(ticket_quantity) ) ) ) {
				alert("<?php esc_attr_e( 'Ticket quantity is not a number', 'tribe-eventbrite' ); ?>");
				$("input[name='EventBriteTicketQuantity']").focus();
				return false;
			}
			if( $('input[name="EventBritePayment_accept_paypal"]').is(':checked') ) {
				var emailField = $('input[name="EventBritePayment_paypal_email"]');
				if( !emailField.val().length ) {
					alert("<?php esc_attr_e( 'A Paypal email address must be provided.', 'tribe-eventbrite' ); ?>");
					emailField.focus();
					return false;
				}
			}
			return true;
		}
	});

	var datepickerOpts = {
		dateFormat: tribe_events_admin.get_datepicker_format(),
		showOn: 'focus',
		showAnim: 'fadeIn',
		minDate: new Date(),
		changeMonth: true,
		changeYear: true,
		numberOfMonths: 3,
		showButtonPanel: true,
		onSelect: function(selectedDate) {
			var option = "minDate";
			var instance = $(this).data("datepicker");
			var date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, selectedDate, instance.settings);
		}
	};
	var dates = $(".etp-datepicker").datepicker(datepickerOpts);

	$(".etp-datepicker").bind( 'click', function() {
			var startDate = $('#EventStartDate').val();
		if ( startDate ) {
			$(this).datepicker( 'option', 'maxDate', startDate );
			$(this).datepicker( 'show' );
		}
	});
}); // end document ready
