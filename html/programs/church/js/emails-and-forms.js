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

  $(".infieldlabel").inFieldLabels();

	$("#buttonChurches").click(function(){
	  $( "#divChurches" ).slideToggle( "slow", function() {
	    // Animation complete.
	  });
	});  

	$("#buttonChurchesHide").click(function(){
	  $( "#divChurches" ).slideToggle( "slow", function() {
	    // Animation complete.
	  });
	});  

	$("#buttonForm").click(function(){
	  $("#divForm").show();
	  $("#divHow").hide();
	});  

	$("#buttonFormHide").click(function(){
	  $("#divForm").hide();
	  $("#divHow").show();
	});
	
  $('.popup-youtube, .popup-vimeo, .popup-gmaps').magnificPopup({
    disableOn: 700,
    type: 'iframe',
    mainClass: 'mfp-fade',
    removalDelay: 160,
    preloader: false,

    fixedContentPos: false
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
    $('#formChurch').submit(function (e) {
        e.preventDefault();
        $.getJSON(
        this.action + "?callback=?",
        $(this).serialize(),
        function (data) {
            if (data.Status === 400) {
                alert("Error: " + data.Message);
            } else { // 200
                $('#divChurchConfirmation').show();
                $('#divChurch').hide();
            }
        });
    });

});

