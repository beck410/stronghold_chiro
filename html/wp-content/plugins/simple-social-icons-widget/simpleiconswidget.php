<?php
/**
 * @package simple_social_icons_widget
*/
/*
Plugin Name: Simple Social Icons Widget
Plugin URI: http://connexstudios.com/
Description: Thanks for installing Simple Social Icons Widget
Version: 1.0
Author: Connex Studios
Author URI: http://connexstudios.com/
*/

class Simpleicons extends WP_Widget{
    
    public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_plugin_styles_simple_icons' ) );
        $params = array(
            'description' => 'Thanks for installing Simple Icons',
            'name' => ' Simple Social Icons'
        );
        parent::__construct('Simpleicons','',$params);
    }
	
public function register_plugin_styles_simple_icons() {
        wp_register_style( 'Simpleicons', plugins_url( 'simple-social-icons-widget/css/icons.css' ) );
        wp_enqueue_style( 'Simpleicons' );
    }

    
    public function form($instance) {
        extract($instance);
        
        ?>
        
        <script type="text/javascript">
//<![CDATA[
    jQuery(document).ready(function()
    {
	// colorpicker field
	jQuery('.cw-color-picker').each(function(){
	var $this = jQuery(this),
        id = $this.attr('rel');
 	$this.farbtastic('#' + id);
    });
});		
//]]>   
</script>
       
<!-- here will put all widget configuration -->
			<p>
				<label for="<?php echo $this->get_field_id('title');?>">Title : </label>
				<input
				class="widefat"
				id="<?php echo $this->get_field_id('title');?>"
				name="<?php echo $this->get_field_name('title');?>"
					value="<?php echo !empty($title) ? $title : "Simple Social Icons Widget"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('backgroundColor');?>">Background Color : </label>
				<input
				class="widefat"
				id="<?php echo $this->get_field_id('backgroundColor');?>"
				name="<?php echo $this->get_field_name('backgroundColor');?>"
					value="<?php echo !empty($backgroundColor) ? $backgroundColor : "#e0e6e4"; ?>" />
			</p>
			<div class="cw-color-picker backgroundColorHide" rel="<?php echo $this->get_field_id('backgroundColor'); ?>"></div>
			<p>
				<label for="<?php echo $this->get_field_id('width');?>">width : </label>
				<input
				class="widefat"
				id="<?php echo $this->get_field_id('width');?>"
				name="<?php echo $this->get_field_name('width');?>"
					value="<?php echo !empty($width) ? $width : "400"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('margin');?>">margin : </label>
				<input
				class="widefat"
				id="<?php echo $this->get_field_id('margin');?>"
				name="<?php echo $this->get_field_name('margin');?>"
					value="<?php echo !empty($margin) ? $margin : "3"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('borderRadius');?>">Border Radius : </label>
				<input
				class="widefat"
				id="<?php echo $this->get_field_id('borderRadius');?>"
				name="<?php echo $this->get_field_name('borderRadius');?>"
					value="<?php echo !empty($borderRadius) ? $borderRadius : "5"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('facebook');?>">Facebook : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('facebook');?>"
				name="<?php echo $this->get_field_name('facebook');?>"
				value="<?php echo !empty($facebook) ? $facebook : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('twitter');?>">Twitter : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('twitter');?>"
				name="<?php echo $this->get_field_name('twitter');?>"
				value="<?php echo !empty($twitter) ? $twitter : "#"; ?>" />
			</p>
			</p>
				<label for="<?php echo $this->get_field_id('youtube');?>">Youtube : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('youtube');?>"
				name="<?php echo $this->get_field_name('youtube');?>"
				value="<?php echo !empty($youtube) ? $youtube : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('linkedin');?>">Linkedin : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('linkedin');?>"
				name="<?php echo $this->get_field_name('linkedin');?>"
				value="<?php echo !empty($linkedin) ? $linkedin : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('mail');?>">Email : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('mail');?>"
				name="<?php echo $this->get_field_name('mail');?>"
				value="<?php echo !empty($mail) ? $mail : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('dropbox');?>">Dropbox : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('dropbox');?>"
				name="<?php echo $this->get_field_name('dropbox');?>"
				value="<?php echo !empty($dropbox) ? $dropbox : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('pinterest');?>">Pinterest : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('pinterest');?>"
				name="<?php echo $this->get_field_name('pinterest');?>"
				value="<?php echo !empty($pinterest) ? $pinterest : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('myspace');?>">MySpace : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('myspace');?>"
				name="<?php echo $this->get_field_name('myspace');?>"
				value="<?php echo !empty($myspace) ? $myspace : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('skype');?>">Skype : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('skype');?>"
				name="<?php echo $this->get_field_name('skype');?>"
				value="<?php echo !empty($skype) ? $skype : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('soundcloud');?>">Soundcloud : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('soundcloud');?>"
				name="<?php echo $this->get_field_name('soundcloud');?>"
				value="<?php echo !empty($soundcloud) ? $soundcloud : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('vimeo');?>">Vimeo : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('vimeo');?>"
				name="<?php echo $this->get_field_name('vimeo');?>"
				value="<?php echo !empty($vimeo) ? $vimeo : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('wordpress');?>">Wordpress : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('wordpress');?>"
				name="<?php echo $this->get_field_name('wordpress');?>"
				value="<?php echo !empty($wordpress) ? $wordpress : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('googleplus');?>">Google Plus : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('googleplus');?>"
				name="<?php echo $this->get_field_name('googleplus');?>"
				value="<?php echo !empty($googleplus) ? $googleplus : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('yahoo');?>">Yahoo : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('yahoo');?>"
				name="<?php echo $this->get_field_name('yahoo');?>"
				value="<?php echo !empty($yahoo) ? $yahoo : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('reddit');?>">Reddit : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('reddit');?>"
				name="<?php echo $this->get_field_name('reddit');?>"
				value="<?php echo !empty($reddit) ? $reddit : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('rss');?>">RSS : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('rss');?>"
				name="<?php echo $this->get_field_name('rss');?>"
				value="<?php echo !empty($rss) ? $rss : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('digg');?>">Digg : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('digg');?>"
				name="<?php echo $this->get_field_name('digg');?>"
				value="<?php echo !empty($blogger) ? $blogger : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('tumblr');?>">Tumblr : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('tumblr');?>"
				name="<?php echo $this->get_field_name('tumblr');?>"
				value="<?php echo !empty($tumblr) ? $tumblr : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('delicious');?>">Delicious : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('delicious');?>"
				name="<?php echo $this->get_field_name('delicious');?>"
				value="<?php echo !empty($delicious) ? $delicious : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('flickr');?>">Flickr : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('flickr');?>"
				name="<?php echo $this->get_field_name('flickr');?>"
				value="<?php echo !empty($flickr) ? $flickr : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('amazon');?>">Amazon : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('amazon');?>"
				name="<?php echo $this->get_field_name('amazon');?>"
				value="<?php echo !empty($amazon) ? $amazon : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('bing');?>">Bing : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('bing');?>"
				name="<?php echo $this->get_field_name('bing');?>"
				value="<?php echo !empty($bing) ? $bing : "#"; ?>" />
			</p>
			<p>
				<label for="<?php echo $this->get_field_id('share');?>">Share : </label>
				<input class="widefat"
				id="<?php echo $this->get_field_id('share');?>"
				name="<?php echo $this->get_field_name('share');?>"
				value="<?php echo !empty($share) ? $share : "#"; ?>" />
			</p>



<?php
    }
    
    public function widget($args, $instance) {
        extract($args);
        extract($instance);
        $title = apply_filters('widget_title', $title);
        $description = apply_filters('widget_description', $description);
		if(empty($backgroundColor)) $backgroundColor = "#e0e6e4";
		if(empty($width)) $width = "400";
		if(empty($margin)) $margin = "3";
		if(empty($borderRadius)) $borderRadius = "5";
	    if(empty($facebook)) $facebook = "https://www.facebook.com";
        echo $before_widget;
        echo $before_title . $title . $after_title;
            
            ?>
    <?php $img = plugins_url( '/css/icons.png' , __FILE__ ); ?>
    <style>
    .social-icons-custom li a{
			background:<?Php echo $backgroundColor;?> url('<?php echo $img;?>')no-repeat 0 -43px;
			display: block;
			margin: <?php echo $margin;?>px;
			width: 42px;
			height: 42px;
			border-radius:<?php echo $borderRadius;?>px;
			-webkit-transition: 0.3s;
			-moz-transition: 0.3s;
	}
        
   </style> 
             
<div class="simple_social_icons">
   <ul class="social-icons-custom" style="max-width:<?php echo $width; ?>px;">
	  <?php if($facebook != "") : ?>
      <li><a class="cfc-facebook" href="<?php echo $facebook;?>" target="_blank"></a></li>
	  <?php endif;?>
	  <?php if($twitter != "") : ?>
      <li><a class="cfc-twitter" href="<?php echo $twitter;?>" target="_blank"></a></li>
      <?php endif;?>
	  <?php if($youtube != "") : ?>
      <li><a class="cfc-youtube" href="<?php echo $youtube;?>" target="_blank"></a></li>
	  <?php endif;?>
	  <?php if($linkedin != "") : ?>
      <li><a class="cfc-linkedin" href="<?php echo $linkedin;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($pinterest != "") : ?>
      <li><a class="cfc-pinterest" href="<?php echo $pinterest;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($flickr != "") : ?>
      <li><a class="cfc-flickr" href="<?php echo $flickr;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($rss != "") : ?>
      <li><a class="cfc-rss" href="<?php echo $rss;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($skype != "") : ?>
      <li><a class="cfc-skype" href="<?php echo $skype;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($vimeo != "") : ?>
      <li><a class="cfc-vimeo" href="<?php echo $vimeo;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($digg != "") : ?>
      <li><a class="cfc-digg" href="<?php echo $digg;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($tumblr != "") : ?>
      <li><a class="cfc-tumblr" href="<?php echo $tumblr;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($mail != "") : ?>
      <li><a class="cfc-mail" href="<?php echo $mail;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($wordpress != "") : ?>
      <li><a class="cfc-wordpress" href="<?php echo $wordpress;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($yahoo != "") : ?>
      <li><a class="cfc-yahoo" href="<?php echo $yahoo;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($reddit != "") : ?>
      <li><a class="cfc-reddit" href="<?php echo $reddit;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($myspace!= "") : ?>
      <li><a class="cfc-myspace" href="<?php echo $myspace;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($soundcloud != "") : ?>
      <li><a class="cfc-soundcloud" href="<?php echo $soundcloud;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($amazon != "") : ?>
      <li><a class="cfc-amazon" href="<?php echo $amazon;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($bing != "") : ?>
      <li><a class="cfc-bing" href="<?php echo $bing;?>" target="_blank"></a></li>    
      <?php endif;?>
      <?php if($delicious != "") : ?>
      <li><a class="cfc-delicious" href="<?php echo $delicious;?>" target="_blank"></a></li>
      <?php endif;?>
      <?php if($share != "") : ?>
      <li><a class="cfc-share" href="<?php echo $share;?>" target="_blank"></a></li> 
      <?php endif;?>
      <div class="clr"></div>
   </ul>
</div>



      
<?php
        echo $after_widget;
    }
}


//registering the color picker
function social_icon_color_picker_script() {
	wp_enqueue_script('farbtastic');
}
function social_icon_color_picker_style() {
	wp_enqueue_style('farbtastic');
}
add_action('admin_print_scripts-widgets.php', 'social_icon_color_picker_script');
add_action('admin_print_styles-widgets.php', 'social_icon_color_picker_style');
add_action('widgets_init','register_Simpleicons');
function register_Simpleicons(){
    register_widget('Simpleicons');
}