<?php

$loader = realpath(dirname(__FILE__).'/../../../').'/wp-load.php';
require_once($loader);
require_once('wps3.php');
$s3 = new wps3();

if (function_exists('admin_url')) {
	wp_admin_css_color('classic', __('Blue'), admin_url("css/colors-classic.css"), array('#073447', '#21759B', '#EAF3FA', '#BBD8E7'));
	wp_admin_css_color('fresh', __('Gray'), admin_url("css/colors-fresh.css"), array('#464646', '#6D6D6D', '#F1F1F1', '#DFDFDF'));
} else {
	wp_admin_css_color('classic', __('Blue'), get_bloginfo('wpurl').'/wp-admin/css/colors-classic.css', array('#073447', '#21759B', '#EAF3FA', '#BBD8E7'));
	wp_admin_css_color('fresh', __('Gray'), get_bloginfo('wpurl').'/wp-admin/css/colors-fresh.css', array('#464646', '#6D6D6D', '#F1F1F1', '#DFDFDF'));
}

wp_enqueue_script( 'common' );
wp_enqueue_script( 'jquery-color' );

@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

if (!current_user_can('manage_options'))
	wp_die(__('You do not have permission to view this page.'));
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php do_action('admin_xml_ns'); ?> <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
	<title><?php bloginfo('name') ?> &rsaquo; <?php _e('Uploads'); ?> &#8212; <?php _e('WordPress'); ?></title>
	<?php
		wp_enqueue_style( 'global' );
		wp_enqueue_style( 'wp-admin' );
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'media' );
	?>
    
    <script type="text/javascript">
	//<![CDATA[
		function addLoadEvent(func) {if ( typeof wpOnload!='function'){wpOnload=func;}else{ var oldonload=wpOnload;wpOnload=function(){oldonload();func();}}}
	//]]>
	</script>
	<?php
	do_action('admin_print_styles');
	do_action('admin_print_scripts');
	do_action('admin_head');
	if ( isset($content_func) && is_string($content_func) )
		do_action( "admin_head_{$content_func}" );
	?>
</head>
<body>

<div style="padding:0 20px 0 20px;">
<h2>Your galleries</h2>

	  <table class="widefat">
        <thead>
          <tr>
            <th>ID</th>
            <th>Gallery Name</th>
            <th>Size (W x H)</th>
            <th>Colours</th>
            <th>Opacity</th>
            <th>Insert</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($s3->galleries() as $gallery) : ?>
          <tr>
            <td><?php echo $gallery->id; ?></td>
            <td valign="top"><strong><?php echo $gallery->name; ?></strong></td>
            <td><?php echo $gallery->width; ?> x <?php echo $gallery->height; ?></td>
            <td><?php echo $gallery->overlay_colour; ?> / <?php echo $gallery->text_colour; ?></td>
            <td><?php echo $gallery->opacity; ?></td>
            <td><p><a class="button wps3-insert" href="#" id="wps3-<?php echo $gallery->id; ?>">Insert</a></p></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
</div>

<script type="text/javascript">
//<![CDATA[
jQuery(document).ready(function($)
{
	$('a.wps3-insert').click(function() {
		var _id = $(this).attr('id').substr(5);
		window.prompt('Cut and paste this into the editor', '[wps3 id="' + _id + '"]');
		return false;
	});
});
//]]>
</script>
</body>
</html>