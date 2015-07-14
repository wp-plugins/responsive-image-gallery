<?php
class ResponsiveImageGalleryAdmin extends Utility{
	function __construct(){

		add_action('init', array($this, 'adminInit'));
		add_action( 'add_meta_boxes', array($this, 'responsive_image_gallery_add_meta_box') );
		add_action( 'save_post', array($this, 'responsive_image_gallery_save_meta_box_data') );
		
		add_action('wp_ajax_get_galleries', array($this, 'responsive_galleries'));
	}
	
	function responsive_galleries(){
		$args = array(
			'posts_per_page'   => 0,
			'offset'           => 0,
			'category'         => '',
			'category_name'    => '',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'include'          => '',
			'exclude'          => '',
			'meta_key'         => '',
			'meta_value'       => '',
			'post_type'        => 'responsive_gallery',
			'post_mime_type'   => '',
			'post_parent'      => '',
			'author'	   => '',
			'post_status'      => 'publish',
			'suppress_filters' => true 
		);
		$posts_array = get_posts( $args );
		
		$response = array();
		
		foreach($posts_array as $value):
			$response[] = (object) array('text' => $value->post_title, 'value' => $value->ID);
		endforeach;
		
		echo json_encode($response);
		
		die();
	}
	
	function adminInit(){
		register_post_type( 'responsive_gallery',
	        array(
	            'labels' => array(
	                'name' => 'Responsive Image Galleries',
	                'singular_name' => 'Responsive Image Gallery',
	                'add_new' => 'Add New',
	                'add_new_item' => 'Add New Image Gallery',
	                'edit' => 'Edit',
	                'edit_item' => 'Edit Image Gallery',
	                'new_item' => 'New Image Gallery',
	                'view' => 'View',
	                'view_item' => 'View Image Gallery',
	                'search_items' => 'Search Image galleries',
	                'not_found' => 'No Galleries found',
	                'not_found_in_trash' => 'No Galleries found',
	                'parent' => 'Parent Image Gallery'
	            ),
	 			
	            'public' => true,
	            'show_in_menu'       => true,
	            'capability_type' => 'page',
	            'supports' => array( 'title', 'thumbnail'),
	            'taxonomies' => array( '' ),
	            'has_archive' => true
	        )
	    );
		
		add_action( 'admin_print_scripts-post-new.php', array($this, 'admn_scripts'), 11);
		add_action( 'admin_print_scripts-post.php', array($this, 'admn_scripts'), 11);
		
		add_filter('mce_external_plugins', array($this, 'tinymcePlugin'));
		add_filter('mce_buttons', array($this, 'tinymceButton'));
	}
	
	function tinymcePlugin($plugin_array){
		$plugin_array['responsive_image_gallery'] = plugins_url('tinymce/plugin.js', __FILE__);
   		return $plugin_array;
	}
	
	function tinymceButton($buttons){
		array_push($buttons, 'responsive_galleries');
		return $buttons;
	}
	
	function admn_scripts(){
		global $post_type;
    	
    	if( 'responsive_gallery' == $post_type ){
    		wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
			wp_enqueue_style('responsive-image-gallery-style', plugins_url('admins/custom.css', __FILE__));
				
			wp_enqueue_media();
			wp_enqueue_script('responsive-image-gallery-script', plugins_url('admins/custom.js', __FILE__), array('jquery', 'jquery-ui-core', 'jquery-ui-dialog'));
		}
	}
	
	function responsive_image_gallery_add_meta_box(){
		add_meta_box(
			'responsive_image_gallery',
			__( 'Images', 'images' ),
			array($this, 'responsive_gallery_images'),
			'responsive_gallery'
		);
		
		add_meta_box(
			'responsive_collage_setting_gallery',
			__( 'Collage Setting', 'collage_settings' ),
			array($this, 'responsive_gallery_collage_setting'),
			'responsive_gallery'
		);
		
		add_meta_box(
			'responsive_fancybox_setting_gallery',
			__('Fancybox setting', 'fancybox_settings'),
			array($this, 'responsive_gallery_fancybox_setting'),
			'responsive_gallery'
		);
		
		add_action('wp_enqueue_scripts', array($this, 'admn_scripts'));
	}
	
	function responsive_gallery_images($post){
		wp_nonce_field( 'responsive_image_gallery_save_images', 'responsive_image_gallery_nonce' );
		
		$value = get_post_meta( $post->ID, 'responsive_image_gallery_images', true );
		?>
		<div>
		    <input type="hidden" id="responsive_image_gallery_images" name="responsive_image_gallery_images" value='<?php echo $value; ?>'>
		    <input type="button" name="upload-btn" id="upload-btn" class="button-primary" value="Add Images" style="margin-left: 5px; margin-top: 10px;">
		</div>
		
		<div class="images-" style="margin-top: 10px;">
			<?php
				if($value != ''){
					$array = (array) json_decode($value);
					$array = array_reverse($array);
					echo "<ul class='responsive-image-gallery-list'>";
					foreach($array as $value){
						echo '<li class="gallery-item" id="'.$value->id.'"><span class="update"></span><span class="delete"></span>'.wp_get_attachment_image($value->image, 'medium').'</li>';
					}
					echo "</ul>";
				}else{
					echo "<ul class='responsive-image-gallery-list'>";
						echo "<li>No images yet!</li>";
					echo "</ul>";
				}
			?>
			
			<div id="dialog-form" title="Image Description">
				<fieldset>
					<input type="hidden" id="image-id" value="" />
					<textarea id='image-description'></textarea>
					<input type="submit" tabindex="-1" style="position:absolute; top:-1000px">
				</fieldset>
			</div>
		</div>
		<?php
	}
	
	function responsive_gallery_collage_setting($post){
		wp_nonce_field( 'responsive_image_gallery_save_collage', 'responsive_image_collage_nonce' );
		
		$value = get_post_meta( $post->ID, 'responsive_image_collage', true );
		if($value != ''){
			$value_ = (array) json_decode($value);
		}else{
			$value_ = array(
	            "fadeSpeed"       => "500",
	            "effect"        => "default",
	            "direction"       => "vertical",
	            "allowPartialLastRow"       => true
	        );
		}
		
		?>
			<input type="hidden" name="responsive_image_collage" id="responsive_image_collage" value='<?php echo ($value == '')? json_encode($value_) : $value; ?>' />
			
			<label for="fadeSpeed">Transition Fade Speed</label>
			<select id="fadeSpeed" class="settingCollage">
				<option value="slow" <?php echo (($value_['fadeSpeed']=='slow')? 'selected=""': '' ); ?> >Slow</option>
				<option value="fast" <?php echo (($value_['fadeSpeed']=='fast')? 'selected=""': '' ); ?> >Fast</option>
			</select>
			
			<br><br>
			
			<label for="effect">Effect type</label>
			<select id="effect" class="settingCollage">
				<option value="default" <?php echo (($value_['effect']=='default')? 'selected=""': '' ); ?> >No effect</option>
				<?php
					for($i=1;$i<7;$i++):
						?>
							<option value="effect-<?php echo $i; ?>"  <?php echo (($value_['effect']== 'effect-'.$i)? 'selected=""': '' ); ?>  >Effect <?php echo $i ?></option>
						<?php
					endfor;
				?>
			</select>
			
			<br><br>
			
			<label for="derection">Direction</label>
			<select id="direction" class="settingCollage">
				<option value="vertical" <?php echo (($value_['direction']== 'vertical')? 'selected=""': '' ); ?>>Vertical</option>
				<option value="horizontal" <?php echo (($value_['direction']== 'horizontal')? 'selected=""': '' ); ?>>Horizontal</option>
			</select>
			
			<br><br>
			
			<label for="last-image">Sometimes the images on the last row will scale a lot if, for example, there is just one image and it needs to fill the parent element width.</label>
			
			<select id="last-image" class="settingCollage">
				<option value="true" <?php echo (($value_['allowPartialLastRow']== 'true')? 'selected=""': '' ); ?>>Do not Scale last image</option>
				<option value="false" <?php echo (($value_['allowPartialLastRow']== 'false')? 'selected=""': '' ); ?>>Scale last image</option>
			</select>
		<?php
		
	}

	function responsive_gallery_fancybox_setting($post){
		wp_nonce_field( 'responsive_image_gallery_save_fancybox', 'responsive_image_fancybox_nonce' );
		
		$value = get_post_meta( $post->ID, 'responsive_image_fancybox', true );
		if($value != ''){
			$value_ = (array) json_decode($value);
		}else{
			//title = 'float', 'inside', 'outside' or 'over'
			$value_ = array(
				'arrows' => true, //can be false
				'closeBtn' => true, //can be false
				'autoPlay' => false,
				'playSpeed' => 3000,
	            'helpers' => (object) array(
					'title' => (object) array('type' => 'float'),
					//'thumbs' => array('width' => 50, 'height' => 50)
				)
	        );
		}
		
		
		?>
			<input type="hidden" name="responsive_image_fancybox" id="responsive_image_fancybox" value='<?php echo ($value == '')? json_encode($value_) : $value; ?>' />
			
			<label for="navigation_button">Navigation Button</label>
			<select id="navigation_button" class="settingFancybox">
				<option value="1" <?php echo (($value_['arrows']== '1')? 'selected=""': '' ); ?>>Show</option>
				<option value="0" <?php echo (($value_['arrows']== '0')? 'selected=""': '' ); ?>>Hide</option>
			</select>
			
			<br><br>
			
			<label for="close_button">Close Button</label>
			<select id="close_button" class="settingFancybox">
				<option value="1" <?php echo (($value_['closeBtn']== '1')? 'selected=""': '' ); ?>>Show</option>
				<option value="0"<?php echo (($value_['closeBtn']== '0')? 'selected=""': '' ); ?>>Hide</option>
			</select>
			
			<br><br>
			
			<label for="autoplay">Sideshow Fancybox</label>
			<select id="autoplay" class="settingFancybox">
				<option value="1" <?php echo (($value_['autoPlay']== '1')? 'selected=""': '' ); ?>>Yes</option>
				<option value="0" <?php echo (($value_['autoPlay']== '0')? 'selected=""': '' ); ?>>No</option>
			</select>
			
			<br><br>
			
			<label for="slideshow_speed">Slideshow Speed</label>
			<select id="slideshow_speed" class="settingFancybox">
				<?php
					for($i=500;$i<10000;$i=$i+500){
						?>
						<option value="<?php echo $i; ?>" <?php echo (($value_['playSpeed']== "$i")? 'selected=""': '' ); ?>><?php echo $i; ?></option>
						<?php
					}
				?>
			</select> Milliseconds
			
			<br><br>
			
			<label for="title_placing">Title Placing</label>
			<select id="title_placing" class="settingFancybox">
				<option value="float" <?php echo (($value_['helpers']->title->type == 'float')? 'selected=""': '' ); ?>>Float</option>
				<option value="inside" <?php echo (($value_['helpers']->title->type == 'inside')? 'selected=""': '' ); ?>>Inside</option>
				<option value="outside" <?php echo (($value_['helpers']->title->type == 'outside')? 'selected=""': '' ); ?>>Outside</option>
				<option value="over" <?php echo (($value_['helpers']->title->type == 'over')? 'selected=""': '' ); ?>>Over</option>
			</select>
			
			<br><br>
			
			<label for="thumbnails">Show thumbnails</label>
			<select class="settingFancybox" id="thumbnails">
				<option value="1" <?php echo ( isset($value_['helpers']->thumbs)? 'selected=""': '' ); ?>>Yes</option>
				<option value="0" <?php echo ( !isset($value_['helpers']->thumbs)? 'selected=""': '' ); ?>>No</option>
			</select>
			
			<br><br>
			
			<label for="thumbnail_dimension_x">Thumbnail dimensions</label>
			<select class="settingFancybox" id="thumbnail_dimension_x">
				<?php
					for($i=50;$i<=300;$i=$i+50){
						?>
						<option value="<?php echo $i; ?>" <?php if(isset($value_['helpers']->thumbs)) echo (($value_['helpers']->thumbs->width == "$i")? 'selected=""': '' ); ?>><?php echo $i; ?></option>
						<?php
					}
				?>
			</select>
			
			<select class="settingFancybox" id="thumbnail_dimension_y">
				<?php
					for($i=50;$i<=300;$i=$i+50){
						?>
						<option value="<?php echo $i; ?>"  <?php if(isset($value_['helpers']->thumbs)) echo (($value_['helpers']->thumbs->height == "$i")? 'selected=""': '' ); ?>><?php echo $i; ?></option>
						<?php
					}
				?>
			</select>Pixels
		<?php
	}
	
	function responsive_image_gallery_save_meta_box_data( $post_id ){
		// Check if our nonce is set.
		if ( ! isset( $_POST['responsive_image_gallery_nonce'])
			&& ! isset($_POST['responsive_image_collage_nonce']) 
			&& ! isset($_POST['responsive_image_fancybox_nonce'])) {
			return;
		}
	
		// Verify that the nonce is valid.
		if (
		! wp_verify_nonce( $_POST['responsive_image_gallery_nonce'], 'responsive_image_gallery_save_images' )
		&& ! wp_verify_nonce( $_POST['responsive_image_collage_nonce'], 'responsive_image_gallery_save_collage' )
		&& ! wp_verify_nonce( $_POST['responsive_image_fancybox_nonce'], 'responsive_image_gallery_save_fancybox' )	
		 ) {
			return;
		}
	
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
	
		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'responsive_gallery' == $_POST['post_type'] ) {
	
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
	
		} else {
	
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}
	
		/* OK, it's safe for us to save the data now. */
		
		// Make sure that it is set.
		if (isset( $_POST['responsive_image_gallery_images'] ) ) {
			// Sanitize user input.
			$my_data = sanitize_text_field( $_POST['responsive_image_gallery_images'] );
		
			// Update the meta field in the database.
			update_post_meta( $post_id, 'responsive_image_gallery_images', $my_data );
		}else{
			return;
		}
		
		if(isset( $_POST['responsive_image_collage'] ) ){
			// Sanitize user input.
			$my_data = sanitize_text_field( $_POST['responsive_image_collage'] );
		
			// Update the meta field in the database.
			update_post_meta( $post_id, 'responsive_image_collage', $my_data );
		}else{
			return;
		}
		
		if(isset($_POST['responsive_image_fancybox'])){
			// Sanitize user input.
			$my_data = sanitize_text_field( $_POST['responsive_image_fancybox'] );
		
			// Update the meta field in the database.
			update_post_meta( $post_id, 'responsive_image_fancybox', $my_data );	
		}else{
			return;
		}
	}
}
