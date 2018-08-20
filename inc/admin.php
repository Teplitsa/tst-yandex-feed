<?php 
if(!defined('ABSPATH')) die; // Die if accessed directly

/**
 * Admin setup
 **/

class La_Yandex_Feed_Admin {
	
	private static $instance = NULL; //instance store
	
	private static $analytics_types = array('Yandex', 'LiveInternet', 'Google', 'MailRu', 'Rambler', 'Mediascope');
	private static $turbo_logo_settings_url = 'https://webmaster.yandex.ru/site/turbo/settings/';
	
	private function __construct() {
				
		/* options page */
		add_action( 'admin_menu', array( $this, 'admin_menu' ));
		
		/* options */
		add_action( 'admin_init', array($this, 'settings_init'));
		add_action('update_option_layf_feed_cache_ttl', array($this, 'update_option_feed_cache_ttl'), 10, 2);
		
				
		/* metabox */
		add_action('add_meta_boxes', array($this, 'create_metaboxes'));
		add_action('save_post', array($this, 'save_custom_data'));
		
		/* style */
		add_action('admin_enqueue_scripts', array($this, 'enqueue_cssjs'));
		
		/* links in description */
		add_filter('plugin_row_meta', array($this, 'plugin_links'), 10, 2);
		add_filter('plugin_action_links', array($this, 'action_links'), 10, 2);

    }
		
		
	/** instance */
    public static function get_instance(){
        
        if (NULL === self :: $instance)
			self :: $instance = new self;
					
		return self :: $instance;
    }
	
	
	/* plugin links */
	public function action_links($links, $file) {
		
		//var_dump($file); die();
		
		if (false !== strpos($file, 'tst-yandex-feed.php')) {
			$txt = __('Settings', 'yandexnews-feed-by-teplitsa');		
			$links[] = "<a href='".admin_url('options-general.php?page=layf_settings')."'>{$txt}</a>";
		}
		
		return $links;
	}
	
	public function plugin_links ($links, $file) {
		
		if (false !== strpos($file, 'tst-yandex-feed.php')) {
			$links[] = '<a href="'.layf_github_link().'" target="_blank">' . __('GitHub', 'yandexnews-feed-by-teplitsa') . '</a>';			
		}
		return $links;
	}
	
	
	/** settings */
	function admin_menu() {
		
		add_options_page(
			__('Yandex.News Feed Settings', 'yandexnews-feed-by-teplitsa'),
			__('Yandex.News', 'yandexnews-feed-by-teplitsa'),
			'manage_options',
			'layf_settings',
			array($this,'layf_settings_screen')
		);
	}
    function settings_init() {
        add_settings_section ( 'layf_base', __ ( 'General', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'layf_base_section_screen' 
        ), 'layf_settings' );
        
        add_settings_field ( 'layf_custom_url', __ ( 'URL for feed', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settngs_custom_url_callback' 
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_post_types', __ ( 'Post types for feed', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settngs_post_types_callback' 
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_post_max_age', __ ( 'Max posts age for feed', 'yandexnews-feed-by-teplitsa' ), array (
        	$this,
        	'settngs_post_max_age_callback'
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_feed_logo', __ ( 'Logo URL for feed description', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_feed_logo_callback' 
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_feed_logo_square', __ ( 'Square Logo URL for feed description', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_feed_logo_square_callback' 
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_filter_taxonomy', __ ( 'Taxonomy to filter entries for feed', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_filter_taxonomy_callback' 
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_filter_terms', __ ( 'Terms to filter entries for feed', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_filter_terms_callback' 
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_filter_terms_slug', __ ( 'Terms slug to filter entries for feed', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_filter_terms_slug_callback'
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field( 'layf_exclude_taxonomy', __('Taxonomy to exclude from feed', 'yandexnews-feed-by-teplitsa'), array(
            $this,
            'settings_exclude_taxonomy_callback'
        ), 'layf_settings', 'layf_base' );

        add_settings_field( 'layf_exclude_terms', __('Terms exclude from feed', 'yandexnews-feed-by-teplitsa'), array(
            $this, 
            'settings_exclude_terms_callback'
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_exclude_terms_slug', __ ( 'Terms slugs exclude from feed', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_exclude_terms_slug_callback'
        ), 'layf_settings', 'layf_base' );
	
        add_settings_field ( 'layf_include_post_thumbnail', __ ( 'Include post thumbnails into feed', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_include_post_thumbnail_callback' 
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_enable_turbo', __ ( 'Enable Yandex.Turbo Pages', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_enable_turbo_callback'
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_analytics_type', __ ( 'Analytics and ads', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_analytics_type_callback'
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_remove_pdalink', __ ( 'Remove pdalink tag from feed', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_remove_pdalink_callback' 
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_hide_author', __ ( 'Remove post author name from feed', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_hide_author_callback'
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_remove_shortcodes', __ ( 'Remove all unexecuted shortcodes', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_remove_shortcodes_callback' 
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_remove_teaser_from_fulltext', __ ( 'Remove teaser from yandex:full-text tag', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_remove_teaser_from_fulltext_callback'
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_feed_items_limit', __ ( 'Feed items limit', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_feed_items_limit_callback'
        ), 'layf_settings', 'layf_base' );
        
        add_settings_field ( 'layf_feed_cache_ttl', __ ( 'Feed cache lifetime', 'yandexnews-feed-by-teplitsa' ), array (
            $this,
            'settings_feed_cache_ttl_callback'
        ), 'layf_settings', 'layf_base' );
        
        register_setting ( 'layf_settings', 'layf_post_types' );
        register_setting ( 'layf_settings', 'layf_post_max_age' );
        register_setting ( 'layf_settings', 'layf_feed_logo' );
        register_setting ( 'layf_settings', 'layf_feed_logo_square' );
        register_setting ( 'layf_settings', 'layf_filter_taxonomy' );
        register_setting ( 'layf_settings', 'layf_filter_terms' );
        register_setting ( 'layf_settings', 'layf_filter_terms_slug' );
        register_setting ( 'layf_settings', 'layf_exclude_taxonomy' );
        register_setting ( 'layf_settings', 'layf_exclude_terms' );
        register_setting ( 'layf_settings', 'layf_exclude_terms_slug' );
        register_setting ( 'layf_settings', 'layf_custom_url' );
        register_setting ( 'layf_settings', 'layf_include_post_thumbnail' );
        register_setting ( 'layf_settings', 'layf_enable_turbo' );
        register_setting ( 'layf_settings', 'layf_analytics_type' );
        register_setting ( 'layf_settings', 'layf_analytics_id' );
        register_setting ( 'layf_settings', 'layf_adnetwork_id_header' );
        register_setting ( 'layf_settings', 'layf_adnetwork_id_footer' );
        register_setting ( 'layf_settings', 'layf_remove_pdalink' );
        register_setting ( 'layf_settings', 'layf_hide_author' );
        register_setting ( 'layf_settings', 'layf_feed_items_limit' );
        register_setting ( 'layf_settings', 'layf_feed_cache_ttl' );
        register_setting ( 'layf_settings', 'layf_remove_shortcodes' );
        register_setting ( 'layf_settings', 'layf_remove_teaser_from_fulltext' );
    }
		
	function layf_settings_screen(){
		
	?>
<div class="wrap">
    <h2><?php _e('Yandex.News Feed Settings', 'yandexnews-feed-by-teplitsa');?></h2>

    <div class="layf-columns">
        <div class="layf-form">
            <form method="POST" action="options.php">
					<?php
						settings_fields( 'layf_settings' );	
						do_settings_sections( 'layf_settings' ); 	
						submit_button();
					?>
					</form>
        </div>
        <div class="layf-sidebar"><?php layf_itv_info_widget();?></div>
    </div>
</div>
<?php	
	}
	
	function layf_base_section_screen($args) {
		//may be some description
	}
	
	function settngs_custom_url_callback() {
		
		$value = trailingslashit(get_option('layf_custom_url', 'yandex/news'));
			
		update_option('layf_permalinks_flushed', 0); //is it ok?
		
	?>
<label for="layf_custom_url">
			<?php echo home_url('/');?><input name="layf_custom_url"
    id="layf_custom_url" type="text" class="regular-text code"
    value="<?php echo $value;?>">
</label>
<p class="description"><?php echo sprintf(__('Customoze the URL of the feed if needed', 'yandexnews-feed-by-teplitsa'), '<a href="'.home_url('/index.php?yandex_feed=news').'">'.home_url('/index.php?yandex_feed=news').'</a>');?></p>
<?php	
	}
 
	function settngs_post_types_callback() {
		
		$value = get_option('layf_post_types', '');
		?>
<label for="layf_post_types"><input name="layf_post_types"
    id="layf_post_types" type="text" class="regular-text code"
    value="<?php echo $value;?>"> </label>
<p class="description"><?php _e('Comma separated list of post types', 'yandexnews-feed-by-teplitsa');?></p>
<?php
	}
	
	function settngs_post_max_age_callback() {
		
		$value = get_option('layf_post_max_age', LAYF_DEFAULT_MAX_POST_AGE);
		?>
		<label for="layf_post_max_age"><input name="layf_post_max_age"
		    id="layf_post_max_age" type="text" class="regular-text code"
		    value="<?php echo $value;?>"> </label>
		<p class="description"><?php _e('Max age of feed posts in days', 'yandexnews-feed-by-teplitsa');?></p>
		<p class="description"><?php printf( __('For Yandex.Turbo this parameter will be ignored. Numeric feed limit will be used instead it.', 'yandexnews-feed-by-teplitsa'), La_Yandex_Feed_Core::$yandex_turbo_feed_min_limit, La_Yandex_Feed_Core::$yandex_turbo_feed_min_limit);?></p>
		<?php
	}
	
	function settings_feed_logo_callback() {
		
		$value = get_option('layf_feed_logo', '');
		?>
<label for="layf_feed_logo_square"><input name="layf_feed_logo"
    id="layf_feed_logo" type="text" class="code widefat"
    value="<?php echo $value;?>"> </label>
<p class="description"><?php _e('Direct link to .jpg, .png, .gif file (100px size of max side)', 'yandexnews-feed-by-teplitsa');?></p>
<p class="description"><?php printf( __('Logo for Yandex.Turbo should be set directly in Yandex.Webmaster: <a href="%s" target="_blank">%s</a>', 'yandexnews-feed-by-teplitsa'), self::$turbo_logo_settings_url, self::$turbo_logo_settings_url);?></p>
<?php
	}
	
	function settings_feed_logo_square_callback() {
		
		$value = get_option('layf_feed_logo_square', '');
		?>
<label for="layf_feed_logo_square"><input name="layf_feed_logo_square"
    id="layf_feed_logo_square" type="text" class="code widefat"
    value="<?php echo $value;?>"> </label>
<p class="description"><?php _e('Direct link to .jpg, .png, .gif file (180x180px size as min)', 'yandexnews-feed-by-teplitsa');?></p>
<?php
	}
	
	function settings_filter_taxonomy_callback() {
		
		$value = get_option('layf_filter_taxonomy', 'category');
		$taxes = get_taxonomies(array('public' => true), 'objects'); 
		if(!empty($taxes)){			
		?>
<select name="layf_filter_taxonomy">
			<?php foreach($taxes as $key => $tax_obj) { ?>
				<option value="<?php echo esc_attr($key);?>"
        <?php selected($key, $value);?>><?php echo esc_attr($tax_obj->labels->name);?></option>
			<?php } ?>
			</select>
<?php	
		}
	}
	
	function settings_filter_terms_callback() {
		
		$value = esc_attr(get_option('layf_filter_terms', ''));
	?>
<label for="layf_filter_terms"><input name="layf_filter_terms"
    id="layf_filter_terms" type="text" class="code regular-text"
    value="<?php echo $value;?>"> </label>
<p class="description"><?php _e('Comma separated list of term IDs', 'yandexnews-feed-by-teplitsa');?></p>
<?php
		
	}
	
	function settings_filter_terms_slug_callback() {
	
	    $value = esc_attr(get_option('layf_filter_terms_slug', ''));
	    ?>
	<label for="layf_filter_terms_slug"><input name="layf_filter_terms_slug"
	    id="layf_filter_terms_slug" type="text" class="code regular-text"
	    value="<?php echo $value;?>"> </label>
	<p class="description"><?php _e('Comma separated list of term slugs', 'yandexnews-feed-by-teplitsa');?></p>
	<?php
			
	}
	
	function settings_exclude_taxonomy_callback() {

		$value = get_option('layf_exclude_taxonomy', 'category');
		$taxes = get_taxonomies(array('public' => true), 'objects');
		if(!empty($taxes)){
		?>
			<select name="layf_exclude_taxonomy">
			<?php foreach($taxes as $key => $tax_obj) { ?>
				<option value="<?php echo esc_attr($key);?>" <?php selected($key, $value);?>><?php echo esc_attr($tax_obj->labels->name);?></option>
			<?php } ?>
			</select>
		<?php
		}
	}

	function settings_exclude_terms_callback() {

		$value = esc_attr(get_option('layf_exclude_terms', ''));
	?>
		<label for="layf_exclude_terms"><input name="layf_exclude_terms" id="layf_exclude_terms" type="text" class="code regular-text" value="<?php echo $value;?>"> </label>
		<p class="description"><?php _e('Comma separated list of term IDs', 'yandexnews-feed-by-teplitsa');?></p>
	<?php

	}
	
	function settings_exclude_terms_slug_callback() {
	
	    $value = esc_attr(get_option('layf_exclude_terms_slug', ''));
    ?>
		<label for="layf_exclude_terms_slug"><input name="layf_exclude_terms_slug" id="layf_exclude_terms_slug" type="text" class="code regular-text" value="<?php echo $value;?>"> </label>
		<p class="description"><?php _e('Comma separated list of term slugs', 'yandexnews-feed-by-teplitsa');?></p>
	<?php

	}
	
	function settings_include_post_thumbnail_callback() {
	    $value = get_option('layf_include_post_thumbnail', '');
        ?>
<input type="checkbox" name="layf_include_post_thumbnail" value="1"
    <?php if($value):?> checked="checked" <?php endif;?> />
<?php	
	}
	
	function settings_analytics_type_callback() {
        ?>
<p class="description"><?php printf( __('Analytics and ads should be set up directly in Yandex.Webmaster: <a href="%s" target="_blank">%s</a>', 'yandexnews-feed-by-teplitsa'), self::$turbo_logo_settings_url, self::$turbo_logo_settings_url);?></p>
		<?php
	}
	
    function settings_remove_pdalink_callback() {
	    $value = get_option('layf_remove_pdalink', '');
	    ?>
<input type="checkbox" name="layf_remove_pdalink" value="1"
    <?php if($value):?> checked="checked" <?php endif;?> />
<?php	
	}
	
	function settings_hide_author_callback() {
	    $value = get_option('layf_hide_author', '');
	    ?>
	<input type="checkbox" name="layf_hide_author" value="1"
	    <?php if($value):?> checked="checked" <?php endif;?> />
	<?php	
	}
		
	function settings_enable_turbo_callback() {
	    $value = get_option('layf_enable_turbo', '');
    ?>
<input type="checkbox" name="layf_enable_turbo" value="1"
    <?php if($value):?> checked="checked" <?php endif;?> />
<?php	
	}
		
	
	function settings_remove_shortcodes_callback() {
	    $value = get_option('layf_remove_shortcodes', '');
	    ?>
<input type="checkbox" name="layf_remove_shortcodes" value="1"
    <?php if($value):?> checked="checked" <?php endif;?> />
<?php	
	}
	
	function settings_remove_teaser_from_fulltext_callback() {
	    $value = get_option('layf_remove_teaser_from_fulltext', '');
	    ?>
<input type="checkbox" name="layf_remove_teaser_from_fulltext" value="1"
    <?php if($value):?> checked="checked" <?php endif;?> />
<?php	
	}
		
	function settings_feed_items_limit_callback() {
	    $value = get_option('layf_feed_items_limit', '');
	    ?>
<label for="layf_feed_items_limit"><input name="layf_feed_items_limit"
    id="layf_feed_items_limit" type="text" class="regular-text code"
    value="<?php echo $value;?>"> </label>
<p class="description"><?php _e('Numeric limit or empty for no limit', 'yandexnews-feed-by-teplitsa');?></p>
<p class="description"><?php printf( __('For Yandex.Turbo it should be %s or more. If not, %s will be used.', 'yandexnews-feed-by-teplitsa'), La_Yandex_Feed_Core::$yandex_turbo_feed_min_limit, La_Yandex_Feed_Core::$yandex_turbo_feed_min_limit);?></p>
<?php
	}
	
	function settings_feed_cache_ttl_callback() {
	    $value = get_option('layf_feed_cache_ttl', '');
	    ?>
<label for="layf_feed_cache_ttl"><input name="layf_feed_cache_ttl"
    id="layf_feed_cache_ttl" type="text" class="regular-text code"
    value="<?php echo $value;?>"> </label>
<p class="description"><?php _e('Cache lifetime in hours. Leave it empty to disable cache.', 'yandexnews-feed-by-teplitsa');?></p>
<?php
	}
		
	/* styles */
	function enqueue_cssjs() {
		
		$screen = get_current_screen(); 
		if('settings_page_layf_settings' != $screen->id)
			return;
      
        wp_enqueue_style('layf-admin', LAYF_PLUGIN_BASE_URL.'css/admin.css', array(), LAYF_VERSION);
	}
	
	
	/* create metabox */
	function create_metaboxes() {
		
		$pt = $this->get_supported_post_types();
		$callback = array($this, 'setting_metabox');
		
		if(!empty($pt)){ foreach($pt as $post_type){
			add_meta_box('layf_related_links', __('Yandex.News settings', 'yandexnews-feed-by-teplitsa'), $callback, $post_type, 'advanced');
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
    <label for="layf_related_links" class="label-title"><?php _e('Related links', 'yandexnews-feed-by-teplitsa');?></label>
    <textarea id="layf_related_links" name="layf_related_links"
        cols="40" rows="4" class="widefat"><?php echo $value;?></textarea>
    <p><?php _e('Enter related links URL and descrioption separated by space, one link per string.', 'yandexnews-feed-by-teplitsa');?></p>
</fieldset>
<fieldset>
    <label class="label-title"><?php _e('Exclude entry from Yandex.News feed', 'yandexnews-feed-by-teplitsa');?></label><br>
    <label for="layf_exclude_from_feed"><input type="checkbox" name="layf_exclude_from_feed"
        value="1" <?php checked($exclude, 1);?>><?php _e('Exclude despite the global settings', 'yandexnews-feed-by-teplitsa');?></label>
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
	
	function update_option_feed_cache_ttl($feed_cache_ttl, $feed_cache_ttl_new) {
	    if(!$feed_cache_ttl_new) {
	        $layf = La_Yandex_Feed_Core::get_instance();
	        $layf->clear_cache();
	    }
	}
	
	
} //class


/** ITV info-widget **/
function layf_itv_info_widget(){
	//only in Russian as for now
    $locale = get_locale();
    
    if($locale != 'ru_RU')
        return;
    
    
    $src = LAYF_PLUGIN_BASE_URL.'img/logo-itv.png';
    $domain = parse_url(home_url()); 
    $itv_url = "https://itv.te-st.ru/?ynfeed=".$domain['host'];
?>
<div id="itv-card">
    <div class="itv-logo">
        <a href="<?php echo esc_url($itv_url);?>" target="_blank"><img
            src="<?php echo esc_url($src);?>"></a>
    </div>

    <p>
        Вам нужна помощь в настройке
        плагина на вашем сайте? Вы
        являетесь социальным или
        некоммерческим проектом?
        Опубликуйте задачу на платформе <a
            href="<?php echo esc_url($itv_url);?>" target="_blank">it-волонтер</a>
    </p>

    <p>
        <a href="<?php echo esc_url($itv_url);?>" target="_blank"
            class="button">Опубликовать задачу</a>
    </p>
</div>

<p>
    Есть вопросы к разработчикм плагина?
    Хотите предложить новую функцию?
    Напишите свой вопрос или предложение
    на <a href="<?php echo layf_github_link();?>" target="_blank">GitHub</a>
</p>
<?php
}

function layf_github_link(){
	
	return 'https://github.com/Teplitsa/tst-yandex-feed';
}