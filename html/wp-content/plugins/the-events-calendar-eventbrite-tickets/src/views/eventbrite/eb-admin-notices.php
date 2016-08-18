<?php
/**
 * EventBrite notices on the Post Edit page
 *
 * @package Tribe__Events__Tickets__Eventbrite__Main
 * @since  3.11
 * @author Modern Tribe Inc.
 */

?>
<div class="notice is-dismissible error tribe-eventbrite-notices">
	<h4><?php esc_html_e( 'We were unable to sync your event to Eventbrite. Here\'s what happened:', 'tribe-eventbrite' ); ?></h4>
	<ul>
	<?php foreach ( $notices as $key => $message ) { ?>
		<li><?php echo wp_kses( $message, $tags ); ?></li>
	<?php } ?>
	</ul>
</div>