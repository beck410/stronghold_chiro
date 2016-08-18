jQuery(document).ready(function(){

	jQuery("#interactiveSpine div").mouseover(function () {
		
	//remove class "hover" from all vertabrae
		jQuery("#interactiveSpine div").removeClass("hover");
	//add the class "hover" to this vertabrae
		jQuery(this).addClass("hover");

	//hide all the info lists and boxes on the right side
		jQuery("#spineInformation > ul").hide();
		jQuery("#spineInformation div").hide();

	//get the id of the element being hovers and covert to a string
		var vertabraeID = "spineInfo" + (jQuery(this).attr("ID"));
		// console.og to test as I go
		console.log(vertabraeID);
	//get the info box whose ID equals (spineInfo + verabraeID) defined as the vertabraeID variable above
		jQuery("#" + vertabraeID).show();
	//then show only that list and it's contained info boxes
		jQuery("#" + vertabraeID).find(".topSpineInfo").show("normal", function() {
			jQuery("#" + vertabraeID).find(".organSpineInfo").show("normal", function() {
				jQuery("#" + vertabraeID).find(".midSpineInfo").show("normal", function() {
					jQuery("#" + vertabraeID).find(".bottomSpineInfo").show("normal", function() {
					});
				});
			});
		});
	});
})