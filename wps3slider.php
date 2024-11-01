<?php

/*
Plugin Name: WPS3Slider
Plugin URI: http://www.kevinbradwick.co.uk/2010/06/wps3slider-plugin-for-wordpress/ 
Description: A plugin that integrates the jQuery s3slider plugin
Version: 1.1.1
Author: Kevin Bradwick
Author URI: http://www.kevinbradwick.co.uk
Licence: GPL2

Copyright 2010 Kevin Bradwick  (email : kbradwick@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/


/**
 * Define constants
 */
defined('WP_PLUGIN_URL') || define(WP_PLUGIN_URL, WP_CONTENT_URL . '/plugins', true);


require_once('wps3.php');
require_once('adminscreens.php');
$s3 = new wps3();




/**
 * Register plugin activation
 */
function wps3_activation()
{
	global $wpdb;
	$schema = file_get_contents(dirname(__FILE__).'/schema.sql');
	$schema = str_replace('{PREFIX}', $wpdb->prefix, $schema);

    $queries = explode('-NEXT-', $schema);

    foreach($queries as $sql) {
        $wpdb->query(trim($sql));
    }
    
}
register_activation_hook(__FILE__, 'wps3_activation');


/**
 * Deactivation
 */
function wps3_deactivate()
{
	global $wpdb;
	
	// remove the old tables as it can cause problems reactivating
	$wpdb->query("DROP TABLE {$wpdb->prefix}wps3_items");
	$wpdb->query("DROP TABLE {$wpdb->prefix}wps3_galleries");
}
register_deactivation_hook(__FILE__, 'wps3_deactivate');

/**
 * The template function
 */
function wps3_slider( $content )
{
	$pattern = '/\[wps3\sid=\"([0-9]+)\"\]/i';
	
	if(preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
		
		global $s3;
    	
		foreach($matches as $gallery) {
			
			$gallery_id = $gallery[1];
			
			if(!$gallery = $s3->get_gallery((int)$gallery_id))
				return preg_replace($pattern,'', $content);
			
			if(!$s3->items())
				return preg_replace($pattern,'', $content);
				
			?>
            <div class="s3slider-wrap">
                <div id="s3slider-<?php echo $gallery->id; ?>">
                  <ul id="s3slider-<?php echo $gallery->id; ?>Content" class="nostyle">
                    <?php foreach($s3->items() as $item) : ?>
                    <li class="s3slider-<?php echo $gallery->id; ?>Image">
                      <?php if(!empty($item->href)) : ?>
                      <a href="<?php echo $item->href; ?>">
                        <img src="<?php echo WP_PLUGIN_URL; ?>/wps3slider/scripts/timthumb.php?src=<?php echo $item->image_path; ?>&amp;w=<?php echo $gallery->width; ?>&amp;h=<?php echo $gallery->height; ?>" alt="thumbnail" />
                      </a>
                      <?php else : ?>
                      <img src="<?php echo WP_PLUGIN_URL; ?>/wps3slider/scripts/timthumb.php?src=<?php echo $item->image_path; ?>&amp;w=<?php echo $gallery->width; ?>&amp;h=<?php echo $gallery->height; ?>" alt="thumbnail" />
                      <?php endif; ?>
                    <span class="<?php echo $item->span_location; ?>"><?php echo stripslashes($item->overlay_text); ?></span>
                    </li>
                    <?php endforeach; ?>
                    <li><div class="clear s3slider-<?php echo $gallery->id; ?>Image"></div></li>
                  </ul>
                </div>
            </div>
			
			<script type="text/javascript">
			//<![CDATA[
			jQuery(document).ready(function($)
			{
				$('#s3slider-<?php echo $gallery->id; ?>').s3Slider({ timeOut: <?php echo (int)$gallery->timeout; ?> });
			});
			//]]>
			</script>
			<?php
			$s3->reset();
		}
		
		// return main content minus shortcode
		return preg_replace($pattern,'', $content);
	}
	
	return $content;
}
add_filter('the_content','wps3_slider');





/**
 * Register plugin scripts
 */
function wps3_register_scripts()
{
	wp_register_script('s3slider', WP_PLUGIN_URL . '/wps3slider/scripts/s3Slider.js', array('jquery'), '1.0');
	wp_enqueue_script('s3slider');
}
add_action('init', 'wps3_register_scripts');





/**
 * CSS Rules
 */
function wps3_css()
{
	?>
    <link rel="stylesheet" type="text/css" href="<?php echo WP_PLUGIN_URL; ?>/wps3slider/scripts/wps3slider-style.php" media="screen, projection" />
    <?php
}
add_action('wp_head','wps3_css');



/**
 * Editor
 */
function wps3_editor_button()
{
	$url = WP_PLUGIN_URL . '/wps3slider/gallery.php?TB_iframe=true&amp;height=400&amp;width=640';
	?>
    <a href="<?php echo $url; ?>" class="thickbox">
      <img src="<?php echo WP_PLUGIN_URL; ?>/wps3slider/pictures.png" alt="WPS3" />
    </a>
    <?php
}
add_action('media_buttons','wps3_editor_button',200);


/**
 * Shrotcode
 */
function wps3_shortcode( $atts )
{
	global $s3;
	
	if(!preg_match('/^[0-9]+$/', $atts['id']))
		return false;
	
	if(!$gallery = $s3->get_gallery((int)$atts['id']))
		return false;
	
	if(!$items = $s3->items())
		return false;
	
	?>
    <div class="s3slider-wrap">
        <div id="s3slider-<?php echo $gallery->id; ?>">
          <ul id="s3slider-<?php echo $gallery->id; ?>Content" class="nostyle">
            <?php foreach($items as $item) : ?>
            <li class="s3slider-<?php echo $gallery->id; ?>Image">
              <a href="<?php echo $item->href; ?>">
                <img src="<?php echo WP_PLUGIN_URL; ?>/wps3slider/scripts/timthumb.php?src=<?php echo $item->image_path; ?>&amp;w=<?php echo $gallery->width; ?>&amp;h=<?php echo $gallery->height; ?>" alt="thumbnail" />
              </a>
            <span class="<?php echo $item->span_location; ?>"><?php echo stripslashes($item->overlay_text); ?></span>
            </li>
            <?php endforeach; ?>
            <li><div class="clear s3slider-<?php echo $gallery->id; ?>Image"></div></li>
          </ul>
        </div>
    </div>
    
    <script type="text/javascript">
    //<![CDATA[
    jQuery(document).ready(function($)
    {
        $('#s3slider-<?php echo $gallery->id; ?>').s3Slider({ timeOut: <?php echo (int)$gallery->timeout; ?> });
    });
    //]]>
    </script>
    <?php
    $s3->reset();
}
add_shortcode('wps3','wps3_shortcode');
?>
