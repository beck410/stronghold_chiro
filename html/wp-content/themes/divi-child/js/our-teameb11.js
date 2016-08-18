/**
 * Created by sergey on 12.11.15.
 */
(function ($) {
    $(window).load(function () {
        var our_team_texts = jQuery.makeArray(jQuery('.su-expand-content'))
        jQuery.each(our_team_texts, function (n) {
            if (jQuery(this).height() < 100) {
                jQuery(this).parent().find('.su-expand-link-more').hide();
            }
        })
    });
})(jQuery);