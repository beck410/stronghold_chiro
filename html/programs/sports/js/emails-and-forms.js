/**
 * Write an email address to the screen. Helps so that bots can't parse email addresses from the site.
 *
 *     writeEmail("info", "info", "domain.com");
 *
 * @param string
 * @param string
 * @param string
 */
function writeEmail(contact, email, emailHost) {
  document.write("<a href=" + "&#109a&#105l" + "&#116&#111:" + email + "@" + emailHost+ ">" + contact + "@" + emailHost+"</a>");
}

jQuery(document).ready(function ($) {

 	$('.banner').unslider({
		speed: 500,               //  The speed to animate each slide (in milliseconds)
		delay: 8000,              //  The delay between slide animations (in milliseconds)
		keys: true,               //  Enable keyboard (left, right) arrow shortcuts
	});	  

  $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
    disableOn: 700,
    type: 'iframe',
    mainClass: 'mfp-fade',
    removalDelay: 160,
    preloader: false,

    fixedContentPos: false
  });

	$("#buttonOpen").click(function(){
	  $(".have_been").show();
	});    

	$(".buttonClose").click(function(){
	  $(".have_been").hide();
	});    
  
	/**
	*
	*     This code is for Dynamic Forms
	*
	*/

  
	$("#from").validate({
		errorPlacement: function(error,element) {
		   return true;
		 },
		 highlight: function(element) {
	         $(element).parent('div').addClass('error');
	         $(".error_message").show(); // Show error div
	     },
	     unhighlight: function(element) {
	         $(element).parent('div').removeClass('error');
	     },
	     // ajax call
	     submitHandler: function(form) {
	         $.ajax({
	             url: form.action,
	             type: form.method,
	             data: $(form).serialize(),
	             success: function(response) {
	             	 // Show confirmation and hide error div
	                 $("#divConfirmation").show();
	                 $(".error_message").hide();
	                 $("#divForm").hide();
	                 //$('#answers').html(response);
	             }            
	         });
	     }
	     
	});
  
  
});


/**
 *
 *     This code is for Campaign  Monitor
 *
 */


$(function () {
    $('#formEmail').submit(function (e) {
        e.preventDefault();
        $.getJSON(
        this.action + "?callback=?",
        $(this).serialize(),
        function (data) {
            if (data.Status === 400) {
                alert("Error: " + data.Message);
            } else { // 200
                $('#divEmailConfirmation').show();
                $('#divEmail').hide();
            }
        });
    });
});

/**
 *
 *     This code is for Scrolling Menu
 *
 */


function setupScrollspy() {
	$('.sticky_nav').scrollspy()	
	$('[data-spy="scroll"]').each(function () {
	  var $spy = $(this).scrollspy('refresh')
	});
}

function highlightNav(){

	var sections = $(".sections");
	var navigation_links = $(".sticky_nav li a");
	
	sections.waypoint({
		handler: function(event, direction) {
		
			var active_section;
			active_section = $(this);
			if (direction === "up") active_section = active_section.prev();
	
			var active_link = $('.sticky_nav li a[href="#' + active_section.attr("id") + '"]');
			navigation_links.removeClass("active");
			active_link.addClass("active");
	
		},
		offset: '25%'
	})	
	
}

$(window).load(function() {
	/* get window size */
	var windowSize = $(window); 
	
	windowSize.scroll(function() {
		
		/* Get distance from the top */
		var top = windowSize.scrollTop();
		
		/* If distance from the top is bigger than 50px, than change the top valeu from -50px to 0px  (make it appear)*/
		if(top > 700) {
			$(".stickymenu_wrap").css("top","0");
		}
		
		/* If distance from the top is equals 0, than change the top valeu from 0 to -50px (hide it)*/
		if(top < 700) {
			$(".stickymenu_wrap").css("top","-80px");
		}
		
		/* Show the top value on the p element within the .container */
		$('.container p').text(top);
	});				
});


/**
 *
 *     This code is for Hiding Search Bar on Mobile
 *
 */


function setupHideSearchBar() {
	// When ready...
	window.addEventListener("load",function() {
	  // Set a timeout...
	  setTimeout(function(){
	    // Hide the address bar!
	    window.scrollTo(0, 1);
	  }, 0);
	});
}

function isiPhone(){
    return (
        //Detect iPhone
    //var isiPad = navigator.userAgent.match(/iPad/i) != null;
        (navigator.platform.indexOf("iPhone") != -1) ||
        //Detect iPod
        (navigator.platform.indexOf("iPad") != -1)
    );
}

