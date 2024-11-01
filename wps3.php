<?php

class wps3
{
	/**
	 * @var object - WPDB
	 */
	private $_wpdb;
	
	
	/**
	 * @var array - cached items
	 */
	private $_items = array();
	
	
	/**
	 * @var array
	 */
	private $_errors = array();
	
	
	/**
	 * @var array
	 */
	public $attributes = array();
	
	
	/**
	 * The class constructor
	 */
	public function __construct()
	{
		global $wpdb;
		
		$this->_wpdb = $wpdb;
	}
	
	
	/**
	 * @var string
	 */
	public $form = '';
	
	
	/**
	 * check gallery exists
	 *
	 * @param int - The gallery id
	 * @return boolean
	 */
	public function galleryExists( $id )
	{
		$sql = "SELECT * FROM {$this->_wpdb->prefix}wps3_galleries WHERE id=$id";
		$row = $this->_wpdb->get_row($sql);
		
		if($row) {
			$sql = "SELECT * FROM {$this->_wpdb->prefix}wps3_items WHERE gallery_id=$id";
			foreach($this->_wpdb->get_results($sql) as $item)
				$this->_items[] = $item;
			
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * List Galleries
	 *
	 * @return object
	 */
	public function galleries()
	{
		$sql = "SELECT * FROM {$this->_wpdb->prefix}wps3_galleries";
		return $this->_wpdb->get_results($sql);
	}
	
	
	/**
	 * action
	 *
	 * @param string the action
	 * @return boolean
	 */
	public function action( $action )
	{
		if(isset($_GET['action'])) {
			return $action == $_GET['action'];
		}
		
		return false;
	}
	
	
	/**
	 * validate
	 *
	 * @return boolean
	 */
	public function validate()
	{
		// globally verify nonces
		if(!wp_verify_nonce($this->attributes['token'], $this->form)) {
			die('Security check failed'); // nothing can happen if security check fails
		}
			
		// validate new gallery
		if($this->form == 'NewGallery') {
			if(empty($this->attributes['name']))
				array_push($this->_errors, 'Please give the gallery a name');
			
			if(!preg_match('/^#?([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?$/',$this->attributes['overlay_colour']))
				array_push($this->_errors, 'Invalid colour specified. Colours must be in HEX format');
				
			if(!preg_match('/^[0-9]+$/', $this->attributes['timeout']))
				array_push($this->_errors, 'Timeout value must be an integer e.g. 4000 = 4 seconds');
			
		}
		
		// validate edit gallery
		if($this->form == 'EditGallery') {
			if(empty($this->attributes['name']))
				array_push($this->_errors, 'Please give the gallery a name');
			
			if(!preg_match('/^#?([a-f]|[A-F]|[0-9]){3}(([a-f]|[A-F]|[0-9]){3})?$/',$this->attributes['overlay_colour']))
				array_push($this->_errors, 'Invalid colour specified. Colours must be in HEX format');
			
			if(!preg_match('/^[0-9]+$/', $this->attributes['timeout']))
				array_push($this->_errors, 'Timeout value must be an integer e.g. 4000 = 4 seconds');
		}
		
		// item add
		if($this->form == 'ItemAdd') {
			
			// general upload errors
			if($this->attributes['file']['error'] != 0) {
				switch($this->attributes['file']['error']) {
					case 1 : array_push($this->_errors, 'The uploaded file exceeds the upload_max_filesize directive in php.ini'); break;
					case 2 : array_push($this->_errors, 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form'); break;
					case 3 : array_push($this->_errors, 'The uploaded file was only partially uploaded.'); break;
					case 4 : array_push($this->_errors, 'No file was uploaded - please select on from you computer'); break;
					case 6 : array_push($this->_errors, 'Missing a temporary folder'); break;
					case 7 : array_push($this->_errors, 'Failed to write file to disk'); break;
					case 8 : array_push($this->_errors, 'A PHP extension stopped the file upload'); break;
				}
			} else {
				if(!in_array($this->attributes['file']['type'], $this->validMimes())) {
					array_push($this->_errors, 'Upload file is not an image');
				}
			}
			
			$upload_directory = dirname(__FILE__).'/uploads/'.date('Y/m/d');
			if(!file_exists($upload_directory)) {
				if(!@mkdir($upload_directory,0777,true)) {
					array_push($this->_errors, 'Unable to create the upload directory [' . $upload_directory . '] please check permission of the upload folder');
					return false;
				}
			}
			
			$bits = explode('.', $this->attributes['file']['name']);
			$this->attributes['file']['save_path'] = $upload_directory.'/'.sha1(mt_rand()).'.'.end($bits);
			if(!@move_uploaded_file($this->attributes['file']['tmp_name'], $this->attributes['file']['save_path'])) {
				array_push($this->_errors, 'There was a problem moving the upload file to the upload folder. Please check permission in the upload directory');
				return false;
			}
			
			// gallery selection
			if(empty($this->attributes['gallery']))
				array_push($this->_errors, 'No gallery selcted. May try creating one?');
		}
		
		
		// item edit
		if($this->form == 'ItemEdit') {
			
		}
		
		return !$this->has_errors();
	}
	

    /**
     * Valid mimes
     *
     * @return array - a list of mimes
     */
    public function validMimes()
    {
        return array(
            'image/pjpeg',
            'image/jpeg',
            'image/jpg',
            'image/jp_',
            'application/jpg',
            'application/x-jpg',
            'application/pjpeg',
            'image/pjpeg',
            'image/vnd.swiftview-jpeg',
            'image/x-xbitmap',
            'image/png',
            'image/gif'
        );
    }


	/**
	 * Save
	 *
	 * @return string
	 */
	public function save()
	{
		if(!$this->has_errors()) {
			
			// new gallery
			if($this->form == 'NewGallery') {
				$this->_wpdb->insert(
					$this->_wpdb->prefix.'wps3_galleries',
					array(
						'name'=>$_POST['NewGallery']['name'], 
						'tstamp'=>time(),
						'overlay_colour'=>$this->attributes['overlay_colour'],
						'opacity'=>$this->attributes['opacity'],
						'text_colour'=>$this->attributes['text_colour'],
						'width'=>$this->attributes['width'],
						'height'=>$this->attributes['height'],
						'timeout'=>empty($this->attributes['timeout']) ? 4000 : $this->attributes['timeout']
					),
					array('%s','%d','%s','%s','%s','%d','%d','%d')
				);
				
				return '<p><strong>Gallery saved succesfully</strong></p>';
			}
			
			// edit gallery
			if($this->form == 'EditGallery') {
				$this->_wpdb->update(
					$this->_wpdb->prefix.'wps3_galleries',
					array(
						'name'=>$this->attributes['name'], 
						'overlay_colour'=>$this->attributes['overlay_colour'],
						'opacity'=>$this->attributes['opacity'],
						'text_colour'=>$this->attributes['text_colour'],
						'width'=>$this->attributes['width'],
						'height'=>$this->attributes['height'],
						'timeout'=>empty($this->attributes['timeout']) ? 4000 : $this->attributes['timeout']
					),
					array('id'=>(int)$_POST['EditGallery']['id']),
					array('%s','%s','%s','%s','%d','%d','%d'),
					array('%d')
				);
				
				return '<p><strong>Galelry saved succesfully</strong></p>';
			}
			
			
			// save item
			if($this->form == 'ItemAdd') {
				$this->_wpdb->insert(
					$this->_wpdb->prefix.'wps3_items',
					array(
						'gallery_id'=>$this->attributes['gallery'],
						'image_path'=>str_replace(realpath(dirname(__FILE__).'/../../../'), '',$this->attributes['file']['save_path']),
						'href'=>$this->attributes['image_link'],
						'overlay_text'=>$this->attributes['text'],
						'order'=>$this->attributes['order'],
						'span_location'=>$this->attributes['span_location']
					),
					array('%d','%s','%s','%s','%d','%s')
				);
			}
			
			// update items
			if($this->form == 'ItemEdit') {
				foreach($this->attributes['items'] as $item) {
					$this->_wpdb->update(
						$this->_wpdb->prefix.'wps3_items',
						array(
							'href'=>$item['image_link'],
							'overlay_text'=>$item['text'],
							'order'=>$item['order'],
							'span_location'=>$item['span_location']
						),
						array('id'=>(int)$item['id']),
						array('%s','%s','%d','%s'),
						array('%d')
					);
				}
				
				return '<p><strong>Items saved succesfully</strong></p>';
			}
		}
	}
	
	
	/**
	 * Has errors
	 *
	 * @return boolean
	 */
	public function has_errors()
	{
		return count($this->_errors) > 0 ? true : false;
	}
	
	
	/**
	 * Error Summary
	 *
	 * @return mixed
	 */
	public function error_summary()
	{
		if(count($this->_errors) > 0) {
			$ret = '<p>Please fix the following errors</p><ul>';
			foreach($this->_errors as $error) {
				$ret .= '<li>'.$error.'</li>';
			}
			$ret .= '</ul>';
		}
		
		return isset($ret) ? $ret : false;
	}
	
	
	/**
	 * Delete a gallery
	 *
	 * @param int - the gallery id
	 * @return boolean
	 */
	public function delete_gallery( $id )
	{
		$gallery = $this->get_gallery((int)$id);
		if($gallery) {
			foreach($this->_items as $item) {
				if(file_exists(realpath(dirname(__FILE__).'/../../../').$item->image_path)) {
					@unlink(realpath(dirname(__FILE__).'/../../../').$item->image_path);
				}
			}
			$this->_wpdb->query("DELETE FROM {$this->_wpdb->prefix}wps3_galleries WHERE id = {$id}");
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Delete an item
	 *
	 * @param int - the item id
	 * @return boolean
	 */
	public function delete_item( $id )
	{
		$item = $this->_wpdb->get_row("SELECT * FROM {$this->_wpdb->prefix}wps3_items WHERE id = {$id}");
		if($item) {
			if(!@unlink(realpath(dirname(__FILE__).'/../../../').$item->image_path)) {
				array_push($this->_errors, 'Unable to remove the file from the server');
				return false;
			}
			
			$this->_wpdb->query("DELETE FROM {$this->_wpdb->prefix}wps3_items WHERE id = {$id}");
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Get gallery info
	 *
	 * @param int - the gallery id
	 * @return mixed
	 */
	public function get_gallery( $id )
	{
		$sql = "SELECT * FROM {$this->_wpdb->prefix}wps3_galleries WHERE id=$id";
		$row = $this->_wpdb->get_row($sql);
		
		if($row) {
			$sql = "SELECT * FROM {$this->_wpdb->prefix}wps3_items WHERE gallery_id=$id ORDER BY `order` ASC";
			foreach($this->_wpdb->get_results($sql) as $item)
				$this->_items[] = $item;
			
			return $row;
		}
		
		return false;
	}
	
	
	/**
	 * Has items
	 *
	 * @param int - the galelry id
	 * @return boolean
	 */
	public function has_items( $id )
	{
		$query = $this->_wpdb->get_results("SELECT * FROM {$this->_wpdb->prefix}wps3_items WHERE gallery_id = {$id}");
		if($query) {
			return true;
		}
		
		return false;
	}
	
	
	/**
	 * Get items
	 *
	 * @return object
	 */
	public function items()
	{
		if(count($this->_items) > 0) {
			return $this->_items;
		}
		
		return false;
	}
	
	
	/**
	 * Reset items
	 */
	public function reset()
	{
		$this->_items = array();
	}
}

?>