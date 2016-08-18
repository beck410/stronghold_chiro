(function ($) {

/*    $(document).on("scroll", function () {
        //console.log('scroll');
        if ($(document).scrollTop() > 0) {
            $("header").removeClass("largeHeader").addClass("smallHeader");
        }
        else {
           $("header").removeClass("smallHeader").addClass("largeHeader");
        }

    }); */

    $(document).ready(function () {
        $('.flip-container').click(function (e) {
            $(this).toggleClass('flipped');
        });
        $(".storeLink a").prop("href", function () {
            return this.href += $.trim($('#storeID').text());
        });
        $("#mapFrame").prop("src", function () {
            return this.src += $.trim($('#encodeURL').text());
        });
        $(".replaceBreak").each(function (index, element) {
            var newhtml = $(this).html().replace(/linebreak/g, '<br/>');
            $(this).html(newhtml);
        });

		if ($("#welcomeMessage .replaceBreak").text() == "") {
		// console.log("blank welcome message");
    		$("#welcomeMessage .replaceBreak").css("display", "none");
    		$("#welcomeMessage .defaultMessage").css("display", "block");
		}

        if($('p.post-meta').html()){
            $('p.post-meta').html($('p.post-meta').html().replace(/\|/g,''));
        }

        $(".pp-files a").attr("target", "_blank")

        // if($(".our-team-wrap").html()){
        //     $(".our-team-wrap img").each(function(){
        //         link = $(this).attr("src");
        //         // console.log(link);

        //         if(link == "")
        //         {
        //             $(this).hide();
        //         }
        //     });
        // }
		
        var faxNumberContainer = $('.fax_number_container').text().trim();

        if(faxNumberContainer == "fax:")
        {
            $('.fax_number_container').css('display', 'none');
        }

		var drNumber = $('#drNumber').text();
		// console.log(drNumber);


        if(drNumber)
        {
            setDrNumberCarousel(drNumber);
        }
        else
        {
            $('#drsParent').css('display', 'none');
        }

		/* old conditional
		
		if (drNumber < 1) {
		$('#drsParent').css('display', 'none');
		} else if (drNumber == 1) {
		$('#drCarousel').owlCarousel({
		items : 1,
		navigation : true
		})
		} else {
		$('#drCarousel').owlCarousel({
	    items : drNumber, //10 items >= 1080px browser width
        itemsDesktop : [1079,(drNumber - 1)], //5 items between 1079px and 801px
        itemsDesktopSmall : [800,3], // betweem 800px and 601px
        itemsTablet: [600,2], //2 items between 600 and 0
        navigation : true
		})
		}; */
		/* old bs
		
		$("#drCarousel").data('owlCarousel').removeItem();
		$('#drCarousel .item h1:empty').parentsUntil('.owl-wrapper').remove();
		 $('#drCarousel').owlCarousel({
	    items : drNumber, //10 items >= 1080px browser width
        itemsDesktop : [1079,(drNumber - 1)], //5 items between 1079px and 801px
        itemsDesktopSmall : [800,3], // betweem 800px and 601px
        itemsTablet: [600,2], //2 items between 600 and 0
        navigation : true
		})*/
        function navs() {
        }
			
        var $window = $(window),
                $html = $('#menu-main-header-menu .menu-item');

        $window.resize(function resize() {
            if ($window.width() < 990) {
                return $html.addClass('mobile');
            }

            $html.removeClass('mobile').addClass('desktop');
        }).trigger('resize');

        $(document).on('click', '.mobile', function () {
            $(this).find(".sub-menu").slideToggle("slow");
        });
        jQuery("#menu-main-header-menu .menu-item-has-children>a").click(function () {
            jQuery("#menu-main-header-menu .menu-item-has-children>a").next().each(function(){
                jQuery(this).removeClass( "block" );
            });
            jQuery(this).next().toggleClass('block');
        })
    });


    $(window).load(function () {
        var maxHeight = 0;

        var featuredArticles = $('#featured-articles article').html();

        if(featuredArticles)
        {

            $('#featured-articles article')
                .each(function () {
                    divisor = $(this).height();
                    maxHeight = Math.max(maxHeight, $(this).height());
                    
                }).height(maxHeight+(divisor*.25));   
        }
        var maxHeight2 = 0;

        var docs = $('#drsParent .item').html();

        if(docs)
        {

            $('#drsParent .item')
                .each(function () {
					// console.log(this);
                    divisor = $(this).height();
                    maxHeight2 = Math.max(maxHeight2, $(this).height());
					// console.log(maxHeight2);                  
                }).height(maxHeight2);           
        }
				

        if(window.location.hash) {
            var target = window.location.hash
            jQuery.target = jQuery(target);


            jQuery('html, body').stop().animate({
                'scrollTop': jQuery.target.offset().top -100
            }, 0, 'swing', function () {
                window.location.hash = target;

            });
        }
    });
/*
$( window ).resize(function() {
        var maxHeight2 = 0;
        var docs = $('#drsParent .item').html();
        if(docs)
        {
            console.log("changing size" + docs);

            $('#drsParent .item')
                .each(function () {
                    divisor = $(this).height();
					console.log(divisor);
                    maxHeight2 = Math.max(maxHeight2, $(this).height());
                    
                }).height(maxHeight2 + 50);           
        }
}); */

function setDrNumberCarousel(drNumber)
{

    switch (true) {
    case drNumber == 0:
        $('#drsParent').css('display', 'none');
        break;
    case drNumber == 1:
        $('#drCarousel').owlCarousel({
        singleItem:true,       
        navigation : false
        });
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        break;
    case drNumber == 2:
        $('#drCarousel').owlCarousel({
        items : 2, // all sizes
        navigation : false
        });
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        break;
    case drNumber == 3:
        $('#drCarousel').owlCarousel({
        items : 3, //10 items >= 1080px browser width
        itemsDesktop : [1079,3], //5 items between 1079px and 801px
        itemsDesktopSmall : [800,3], // between 800px and 601px
        itemsTablet: [600,2], // between 600 and 0
        navigation : true,
        navigationText: [
      "<span class='glyphicons glyphicons-chevron-left'></span>",
      "<span class='glyphicons glyphicons-chevron-right'></span>"
        ]
        });
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        break;
    case drNumber == 4:
        $('#drCarousel').owlCarousel({
        items : 4, //10 items >= 1080px browser width
        itemsDesktop : [1079,4], //5 items between 1079px and 801px
        itemsDesktopSmall : [800,3], // betweem 800px and 601px
        itemsTablet: [600,2], //2 items between 600 and 0
        navigation : true,
        navigationText: [
      "<span class='glyphicons glyphicons-chevron-left'></span>",
      "<span class='glyphicons glyphicons-chevron-right'></span>"
        ]
        });
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        break;
    case drNumber == 5:
        $('#drCarousel').owlCarousel({
        items : 5, //10 items >= 1080px browser width
        itemsDesktop : [1079,4], //5 items between 1079px and 801px
        itemsDesktopSmall : [800,3], // betweem 800px and 601px
        itemsTablet: [600,2], //2 items between 600 and 0
        navigation : true,
        navigationText: [
      "<span class='glyphicons glyphicons-chevron-left'></span>",
      "<span class='glyphicons glyphicons-chevron-right'></span>"
        ]
        });
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        break;
    case drNumber == 6:
        $('#drCarousel').owlCarousel({
        items : 5, //10 items >= 1080px browser width
        itemsDesktop : [1079,4], //5 items between 1079px and 801px
        itemsDesktopSmall : [800,3], // betweem 800px and 601px
        itemsTablet: [600,2], //2 items between 600 and 0
        navigation : true,
        navigationText: [
      "<span class='glyphicons glyphicons-chevron-left'></span>",
      "<span class='glyphicons glyphicons-chevron-right'></span>"
        ]
        });
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        break;
    case drNumber == 7:
        $('#drCarousel').owlCarousel({
        items : 5, //10 items >= 1080px browser width
        itemsDesktop : [1079,4], //5 items between 1079px and 801px
        itemsDesktopSmall : [800,3], // betweem 800px and 601px
        itemsTablet: [600,2], //2 items between 600 and 0
        navigation : true,
        navigationText: [
      "<span class='glyphicons glyphicons-chevron-left'></span>",
      "<span class='glyphicons glyphicons-chevron-right'></span>"
        ]
        });
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        break;
    case drNumber == 8:
        $('#drCarousel').owlCarousel({
        items : 5, //10 items >= 1080px browser width
        itemsDesktop : [1079,4], //5 items between 1079px and 801px
        itemsDesktopSmall : [800,3], // betweem 800px and 601px
        itemsTablet: [600,2], //2 items between 600 and 0
        navigation : true,
        navigationText: [
      "<span class='glyphicons glyphicons-chevron-left'></span>",
      "<span class='glyphicons glyphicons-chevron-right'></span>"
        ]
        });
        $("#drCarousel").data('owlCarousel').removeItem();
        $("#drCarousel").data('owlCarousel').removeItem();
        break;
    case drNumber == 9:
        $('#drCarousel').owlCarousel({
        items : 5, //10 items >= 1080px browser width
        itemsDesktop : [1079,4], //5 items between 1079px and 801px
        itemsDesktopSmall : [800,3], // betweem 800px and 601px
        itemsTablet: [600,2], //2 items between 600 and 0
        navigation : true,
        navigationText: [
      "<span class='glyphicons glyphicons-chevron-left'></span>",
      "<span class='glyphicons glyphicons-chevron-right'></span>"
        ]
        });
        $("#drCarousel").data('owlCarousel').removeItem();
        break;
    case drNumber == 10:
        $('#drCarousel').owlCarousel({
        items : 5, //10 items >= 1080px browser width
        itemsDesktop : [1079,4], //5 items between 1079px and 801px
        itemsDesktopSmall : [800,3], // betweem 800px and 601px
        itemsTablet: [600,2], //2 items between 600 and 0
        navigation : true,
        navigationText: [
      "<span class='glyphicons glyphicons-chevron-left'></span>",
      "<span class='glyphicons glyphicons-chevron-right'></span>"
        ]
        });
        break;
    }
}
    

})(jQuery);


