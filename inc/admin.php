<?php 
if(!defined('ABSPATH')) die; // Die if accessed directly

/**
 * Admin setup
 **/

class La_Yandex_Feed_Admin {
	
	private static $instance = NULL; //instance store
	
	
	private function __construct() {
				
		/* options page */
		add_action( 'admin_menu', array( $this, 'admin_menu' ));
		
		/* options */
		add_action( 'admin_init', array($this, 'settings_init'));
				
		/* metabox */
		add_action('add_meta_boxes', array($this, 'create_metaboxes'));
		add_action('save_post', array($this, 'save_custom_data'));
		
    }
		
		
	/** instance */
    public static function get_instance(){
        
        if (NULL === self :: $instance)
			self :: $instance = new self;
					
		return self :: $instance;
    }       
	
	
	/** settings */
	function admin_menu() {
		
		add_options_page(
			__('Yandex.News Feed Settings', 'layf'),
			__('Yandex.News', 'layf'),
			'manage_options',
			'layf_settings',
			array($this,'layf_settings_screen')
		);
	}
	
	function settings_init() {
 	 	
		add_settings_section(
			'layf_base',
			__('General', 'layf'),
			array($this, 'layf_base_section_screen'),
			'layf_settings'
		);
		
		add_settings_field(
			'layf_post_types',
			__('Post types for feed', 'layf'),
			array($this, 'settngs_post_types_callback'),
			'layf_settings',
			'layf_base'
		);
		
		add_settings_field(
			'layf_feed_logo',
			__('Logo URL for feed description', 'layf'),
			array($this, 'settings_feed_logo_callback'),
			'layf_settings',
			'layf_base'
		);
		
		add_settings_field(
			'layf_filter_taxonomy',
			__('Taxonomy to filter entries for feed', 'layf'),
			array($this, 'settings_filter_taxonomy_callback'),
			'layf_settings',
			'layf_base'
		);
		
		add_settings_field(
			'layf_filter_terms',
			__('Terms to filter entries for feed', 'layf'),
			array($this, 'settings_filter_terms_callback'),
			'layf_settings',
			'layf_base'
		);
 	
		register_setting( 'layf_settings', 'layf_post_types' );
		register_setting( 'layf_settings', 'layf_feed_logo' );
		register_setting( 'layf_settings', 'layf_filter_taxonomy' );
		register_setting( 'layf_settings', 'layf_filter_terms' );
		

	}
	
	function layf_settings_screen(){
		
	?>
		<div class="wrap">
			<h2><?php _e('Yandex.News Feed Settings', 'layf');?></h2>
			
			<form method="POST" action="options.php">
			<?php
				settings_fields( 'layf_settings' );	
				do_settings_sections( 'layf_settings' ); 	
				submit_button();
			?>
			</form>			
		</div>
	<?php	
	}
	
	function layf_base_section_screen($args) {
		//may be some description
	}
 
	function settngs_post_types_callback() {
		
		$value = get_option('layf_post_types', '');
		?>
		<label for="layf_post_types"><input name="layf_post_types" id="layf_post_types" type="text" class="regular-text code" value="<?php echo $value;?>"> </label>
		<p class="description"><?php _e('Comma separated list of post types', 'layf');?></p>
	<?php
	}
	
	function settings_feed_logo_callback() {
		
		$value = get_option('layf_feed_logo', '');
		?>
		<label for="layf_feed_logo"><input name="layf_feed_logo" id="layf_feed_logo" type="text" class="code widefat" value="<?php echo $value;?>"> </label>
		<p class="description"><?php _e('Direct link to .jpg, .png, .gif file (100px size of max side)', 'layf');?></p>
	<?php
	}
	
	function settings_filter_taxonomy_callback() {
		
		$value = get_option('layf_filter_taxonomy', 'category');
		$taxes = get_taxonomies(array('public' => true), 'objects'); 
		if(!empty($taxes)){			
		?>
			<select name="layf_filter_taxonomy">
			<?php foreach($taxes as $key => $tax_obj) { ?>
				<option value="<?php echo esc_attr($key);?>" <?php selected($key, $value);?>><?php echo esc_attr($tax_obj->labels->name);?></option>
			<?php } ?>
			</select>
		<?php	
		}
	}
	
	function settings_filter_terms_callback() {
		
		$value = esc_attr(get_option('layf_filter_terms', ''));
	?>
		<label for="layf_filter_terms"><input name="layf_filter_terms" id="layf_filter_terms" type="text" class="code regular-text" value="<?php echo $value;?>"> </label>
		<p class="description"><?php _e('Comma separated list of term IDs', 'layf');?></p>
	<?php
		
	}
	
	
	/* create metabox */
	function create_metaboxes() {
		
		$pt = $this->get_supported_post_types();
		$callback = array($this, 'setting_metabox');
		
		if(!empty($pt)){ foreach($pt as $post_type){
			add_meta_box('layf_related_links', __('Yandex.News settings', 'layf'), $callback, $post_type, 'advanced');
		}}
			
	}
	
	function setting_metabox() {
		global $post;
		
		$value = get_post_meta($post->ID, 'layf_related_links', true);
		$value = esc_textarea($value);		
		$exclude = (int)get_post_meta($post->ID, 'layf_exclude_from_feed', true);
	?>
		<style>
			.label-title {
				font-weight: bold;
				display: inline-block;
				padding: 4px 0;
			}
		</style>
		<fieldset class="layf">
			<label for="layf_related_links" class="label-title"><?php _e('Related links','layf');?></label>
			<textarea id="layf_related_links" name="layf_related_links" cols="40" rows="4" class="widefat"><?php echo $value;?></textarea>
			<p><?php _e('Enter related links URL and descrioption separated by space, one link per string.', 'layf');?></p>
		</fieldset>
		<fieldset>
			<label class="label-title"><?php _e('Exclude entry from Yandex.News feed', 'layf');?></label><br>
			<label for=""><input type="checkbox" name="layf_exclude_from_feed" value="1" <?php checked($exclude, 1);?>><?php _e('Exclude despite the global settings', 'layf');?></label>
		</fieldset>
	<?php
	}
	
	/* save data */
	function save_custom_data($post_id){
		
		$rel_links_value = (!empty($_REQUEST['layf_related_links'])) ? trim($_REQUEST['layf_related_links']) : '';
		$exclude_value = (!empty($_REQUEST['layf_exclude_from_feed'])) ? (int)$_REQUEST['layf_exclude_from_feed'] : 0;
		
		$post_type = get_post_type($post_id);
		
		
		if(in_array($post_type, $this->get_supported_post_types())){
			
			update_post_meta( $post_id, 'layf_related_links', $rel_links_value);
			update_post_meta( $post_id, 'layf_exclude_from_feed', $exclude_value);
		}
	}
	
	function get_supported_post_types() {
		
		$layf = La_Yandex_Feed_Core::get_instance();
		return $layf->get_supported_post_types();
	}
	
	
	
	
} //class