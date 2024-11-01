<?php


/**
 * The menu builder
 */
function wps3_admin_menus()
{
    add_media_page('WPS3Slider', 'WPS3Slider', 'manage_options', 'wps3-top', 'wps3_galleries');
}
add_action('admin_menu', 'wps3_admin_menus');



/**
 * Gallery viewer and creator
 */
function wps3_galleries()
{
	$error = false;
	$success = false;
	
	global $s3;
	
	// add a new gallery
	if(isset($_POST['NewGallery'])) {
		$s3->form = 'NewGallery';
		$s3->attributes = $_POST['NewGallery'];
		if(!$s3->validate()) {
			$error = $s3->error_summary();
		} else {
			$success = $s3->save();
		}
	}
	
	// edit gallery
	if(isset($_POST['EditGallery'])) {
		$s3->form = 'EditGallery';
		$s3->attributes = $_POST['EditGallery'];
		if(!$s3->validate()) {
			$error = $s3->error_summary();
		} else {
			$success = $s3->save();
		}
	}
	
	// add item
	if(isset($_POST['ItemAdd'])) {
		$s3->form = 'ItemAdd';
		$s3->attributes = $_POST['ItemAdd'];
		$s3->attributes['file'] = $_FILES['upload_file'];
		
		if($s3->validate()) {
			$s3->save();
			$success = 'Item added succesfully';
		} else {
			$error = $s3->error_summary();
		}
	}
	
	
	// item edit
	if(isset($_POST['ItemEdit'])) {
		$s3->form = 'ItemEdit';
		$s3->attributes = $_POST['ItemEdit'];
		if($s3->validate()) {
			$success = $s3->save();
		} else {
			$error = $s3->error_summary();
		}
	}
	
	// delete gallery
	if($s3->action('delete-gal')) {
		if($s3->delete_gallery((int)$_GET['id']))
			$success = 'Gallery deleted';
		else
			$error = 'There was a problem deleting the gallery';
	}
	
	// delete item
	if($s3->action('delete-item') && isset($_GET['id'])) {
		if(!wp_verify_nonce($_GET['token'], 'delete-item'))
			die('Security check failed');
		
		if($s3->delete_item((int)$_GET['id']))
			$success = 'Item deleted succesfully';
		else
			$error = $s3->error_summary();
	}
	
	$base_url = get_bloginfo('url') . '/wp-admin/upload.php?page=wps3-top';
	wp_register_style('jui-style', WP_PLUGIN_URL . '/wps3slider/jqueryui/smoothness/jquery-ui-1.7.3.custom.css');
	wp_register_style('wps3-admin', WP_PLUGIN_URL . '/wps3slider/admin.css');
	wp_print_styles('jui-style');
	wp_print_styles('wps3-admin');
	wp_print_scripts('jquery');
	wp_print_scripts('jquery-ui-core');
	wp_print_scripts('jquery-ui-tabs');
	
	
?>

<script type="text/javascript">
//<![CDATA[

jQuery(document).ready(function()
{
	jQuery('#tabs').tabs({
		selected:<?php echo isset($_GET['action']) && $_GET['action'] == 'item-add' ? 1 : 0; ?>				 
	});
});
//]]>
</script>

<div class="wrap">
    <h2>WPS3Slider Galleries</h2>
    
    <!-- display errors -->
    <?php if($error) : ?>
    <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- display messages -->
    <?php if($success) : ?>
    <div id="message" class="updated fade"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <!-- tabs -->
    <div id="tabs">
    	<ul>
		<li><a href="#tabs-1">Galleries</a></li>
        <li><a href="#tabs-2">Add Item</a></li>
	</ul>
    
    <!-- tab content | galleries -->
	<div id="tabs-1">
	  <table class="widefat">
        <thead>
          <tr>
            <th>ID</th>
            <th>Gallery Name</th>
            <th>Size (W x H)</th>
            <th>Colours</th>
            <th>Opacity</th>
            <th>Timeout</th>
            <th>Actions</th>
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
            <td><?php echo $gallery->timeout; ?></td>
            <td><a href="<?php echo $base_url; ?>&amp;action=edit-gal&amp;id=<?php echo $gallery->id; ?>">Edit</a> | 
            <a href="<?php echo $base_url; ?>&amp;action=delete-gal&amp;id=<?php echo $gallery->id; ?>">Delete</a> | 
            <a href="<?php echo $base_url; ?>&amp;action=items&amp;id=<?php echo $gallery->id; ?>">Edit Items</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      
      <p><a class="button" href="<?php echo $base_url; ?>&amp;action=new-gal">Add new gallery</a></p>
      <?php if($s3->action('new-gal')) : ?>
      <p>&nbsp;</p>
      <h3>Add a new gallery</h3>
      <form method="post" action="<?php echo $base_url; ?>">
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><label for="NewGallery[name]">Gallery Name</label></th>
              <td><input type="text" class="regular-text" value="" name="NewGallery[name]" id="gal_name" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="NewGallery[overlay_colour]">Overlay Colour</label></th>
              <td><input type="text" class="regular-text" name="NewGallery[overlay_colour]" value="#000000" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="NewGallery[opacity]">Overlay Opacity</label></th>
              <td><select name="NewGallery[opacity]">
                  <option value="0.1">10%</option>
                  <option value="0.2">20%</option>
                  <option value="0.3">30%</option>
                  <option value="0.4">40%</option>
                  <option value="0.5">50%</option>
                  <option value="0.6">60%</option>
                  <option value="0.7" selected="selected">70%</option>
                  <option value="0.8">80%</option>
                  <option value="0.9">90%</option>
                  <option value="1.0">100%</option>
              </select></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="NewGallery[text_colour]">Text Colour</label></th>
              <td><input type="text" class="regular-text" name="NewGallery[text_colour]" value="#FFFFFF" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="NewGallery[width]">Width</label></th>
              <td><input type="text" class="regular-text" name="NewGallery[width]" value="400" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="NewGallery[height]">Height</label></th>
              <td><input type="text" class="regular-text" name="NewGallery[height]" value="200" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="NewGallery[timeout]">Fader Timeout</label></th>
              <td><input type="text" class="regular-text" name="NewGallery[timeout]" value="4000" /></td>
            </tr>
            <tr valign="top">
              <th scope="row">&nbsp;</th>
              <td><p class="submit"><input type="submit" class="button-primary" value="Save" /></p></td>
            </tr>
          </tbody>
        </table>
        <input type="hidden" name="NewGallery[token]" value="<?php echo wp_create_nonce('NewGallery'); ?>" />
      </form>
      <?php elseif($s3->action('edit-gal') && $gallery = $s3->get_gallery((int)$_GET['id'])) : ?>
      <p>&nbsp;</p>
      <h3>Edit Gallery &#8220;<?php echo $gallery->name; ?>&#8221;</h3>
      <form method="post" action="<?php echo $base_url; ?>&amp;action=edit-gal&amp;id=<?php echo $gallery->id; ?>">
        <table class="form-table">
          <tbody>
            <tr valign="top">
              <th scope="row"><label for="EditGallery[name]">Gallery Name</label></th>
              <td><input type="text" class="regular-text" name="EditGallery[name]" id="gal_name" value="<?php echo $gallery->name; ?>" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="EditGallery[overlay_colour]">Overlay Colour</label></th>
              <td><input type="text" class="regular-text" name="EditGallery[overlay_colour]" value="<?php echo empty($gallery->overlay_colour) ? '#000000' : $gallery->overlay_colour; ?>" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="EditGallery[opacity]">Overlay Opacity</label></th>
              <td><select name="EditGallery[opacity]">
                  <option value="0.1"<?php if($gallery->opacity == '0.1') : ?> selected="selected"<?php endif; ?>>10%</option>
                  <option value="0.2"<?php if($gallery->opacity == '0.2') : ?> selected="selected"<?php endif; ?>>20%</option>
                  <option value="0.3"<?php if($gallery->opacity == '0.3') : ?> selected="selected"<?php endif; ?>>30%</option>
                  <option value="0.4"<?php if($gallery->opacity == '0.4') : ?> selected="selected"<?php endif; ?>>40%</option>
                  <option value="0.5"<?php if($gallery->opacity == '0.5') : ?> selected="selected"<?php endif; ?>>50%</option>
                  <option value="0.6"<?php if($gallery->opacity == '0.6') : ?> selected="selected"<?php endif; ?>>60%</option>
                  <option value="0.7"<?php if($gallery->opacity == '0.7') : ?> selected="selected"<?php endif; ?>>70%</option>
                  <option value="0.8"<?php if($gallery->opacity == '0.8') : ?> selected="selected"<?php endif; ?>>80%</option>
                  <option value="0.9"<?php if($gallery->opacity == '0.9') : ?> selected="selected"<?php endif; ?>>90%</option>
                  <option value="1.0"<?php if($gallery->opacity == '1.0') : ?> selected="selected"<?php endif; ?>>100%</option>
              </select></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="EditGallery[text_colour]">Text Colour</label></th>
              <td><input type="text" class="regular-text" name="EditGallery[text_colour]" value="<?php echo empty($gallery->text_colour) ? '#FFFFFF' : $gallery->text_colour; ?>" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="EditGallery[width]">Width</label></th>
              <td><input type="text" class="regular-text" name="EditGallery[width]" value="<?php echo empty($gallery->width) ? '400' : $gallery->width; ?>" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="EditGallery[height]">Height</label></th>
              <td><input type="text" class="regular-text" name="EditGallery[height]" value="<?php echo empty($gallery->height) ? '200' : $gallery->height; ?>" /></td>
            </tr>
            <tr valign="top">
              <th scope="row"><label for="EditGallery[timeout]">Fader Timeout</label></th>
              <td><input type="text" class="regular-text" name="EditGallery[timeout]" value="<?php echo $gallery->timeout; ?>" /></td>
            </tr>
            <tr valign="top">
              <th scope="row">&nbsp;</th>
              <td><p class="submit"><input type="submit" class="button-primary" value="Save" /></p></td>
            </tr>
          </tbody>
        </table>
        <input type="hidden" name="EditGallery[id]" value="<?php echo $gallery->id; ?>" />
        <input type="hidden" name="EditGallery[token]" value="<?php echo wp_create_nonce('EditGallery'); ?>" />
      </form>
      <?php elseif($s3->action('items') && $gallery = $s3->get_gallery((int)$_GET['id'])) : ?>
      <p>&nbsp;</p>
      <h3>Gallery items for &#8220;<?php echo $gallery->name; ?>&#8221;</h3>
      <?php if($s3->has_items((int)$_GET['id'])) : ?>
      <form method="post" action="<?php echo $base_url; ?>&amp;action=items&id=<?php echo $gallery->id; ?>">
      <table class="widefat form-table">
        <thead>
          <tr>
            <th>Thumbnail</th>
            <th>Image Link</th>
            <th>Text</th>
            <th>Order</th>
            <th>Span Location</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if($s3->items()) : $i=0; foreach($s3->items() as $item) : ?>
          <tr>
            <td><img src="<?php echo WP_PLUGIN_URL; ?>/wps3slider/scripts/timthumb.php?src=<?php echo $item->image_path; ?>&amp;w=120&amp;h=60" alt="thumbnail" /></td>
            <td><input type="text" name="ItemEdit[items][<?php echo $i; ?>][image_link]" value="<?php echo $item->href; ?>" /></td>
            <td><textarea name="ItemEdit[items][<?php echo $i; ?>][text]" id="ItemEdit_<?php echo $item->id; ?>_text" cols="30" rows="4"><?php echo stripslashes($item->overlay_text); ?></textarea></td>
            <td><input type="text" name="ItemEdit[items][<?php echo $i; ?>][order]" value="<?php echo $item->order; ?>" />
            <input type="hidden" name="ItemEdit[items][<?php echo $i; ?>][id]" value="<?php echo $item->id; ?>" /></td>
            <td><select name="ItemEdit[items][<?php echo $i; ?>][span_location]">
            	<option value="top"<?php if($item->span_location == 'top') : ?> selected="selected"<?php endif; ?>>Top</option>
                <option value="bottom"<?php if($item->span_location == 'bottom') : ?> selected="selected"<?php endif; ?>>Bottom</option>
                <option value="left"<?php if($item->span_location == 'left') : ?> selected="selected"<?php endif; ?>>Left</option>
                <option value="right"<?php if($item->span_location == 'right') : ?> selected="selected"<?php endif; ?>>Right</option>
            </select></td>
            <td><a href="<?php echo $base_url; ?>&amp;action=delete-item&amp;token=<?php echo wp_create_nonce('delete-item'); ?>&amp;id=<?php echo $item->id; ?>">Delete</a></td>
          </tr>
        <?php $i++; endforeach; endif; ?>
        </tbody>
      </table>
      <input type="hidden" name="ItemEdit[token]" value="<?php echo wp_create_nonce('ItemEdit'); ?>" />
      <p class="submit"><input type="submit" class="button-primary" value="Save Items" /></p>
      </form>
      <?php else : ?>
      <p><strong>There are currently no items to this gallery</strong></p>
      <p><a href="<?php echo $base_url; ?>&amp;action=item-add">Add a new item</a></p>
      <?php endif; ?>
      <?php endif; ?>
      
	</div>
    
	<div id="tabs-2">
    <form enctype="multipart/form-data" method="post" action="<?php echo $base_url; ?>&amp;action=item-add">
      <table class="form-table">
        <tr>
          <th scope="row"><label for="upload_file">Select a file</label></th>
          <td><input type="file" name="upload_file" id="upload_file" /></td>
        </tr>
        <tr>
          <th scope="row"><label for="ItemAdd[gallery]">Select the gallery</label></th>
          <td><select name="ItemAdd[gallery]">
          		<?php foreach($s3->galleries() as $gallery) : ?>
                <option value="<?php echo $gallery->id; ?>"><?php echo $gallery->name; ?></option>
                <?php endforeach; ?>
          </select></td>
        </tr>
        <tr>
          <th scope="row"><label for="ItemAdd[order]">Order</label></th>
          <td><input type="text" class="regular-text" value="0" name="ItemAdd[order]" id="ItemAdd_order" /></td>
        </tr>
        <tr>
          <th scope="row"><label for="ItemAdd[image_link]">Image Link</label></th>
          <td><input type="text" class="regular-text" value="" name="ItemAdd[image_link]" id="ItemAdd_image_link" /></td>
        </tr>
        <tr>
          <th scope="row"><label for="ItemAdd[text]">Overlay Message</label></th>
          <td><input type="text" class="regular-text" value="" name="ItemAdd[text]" id="ItemAdd_text" /></td>
        </tr>
        <tr>
          <th scope="row"><label for="ItemAdd[span_location]">Span Location</label></th>
          <td><select name="ItemAdd[span_location]">
            	<option value="top">Top</option>
                <option value="bottom">Bottom</option>
                <option value="left">Left</option>
                <option value="right">Right</option>
            </select></td>
        </tr>
      </table>
      <p class="submit"><input type="submit" class="button-primary" value="Add Item" /></p>
      <input type="hidden" name="ItemAdd[token]" value="<?php echo wp_create_nonce('ItemAdd'); ?>" />
    </form>
	</div>
    
    </div>
    
</div>
<?php
}


?>