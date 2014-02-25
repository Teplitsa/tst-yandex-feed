<?php

class La_Yandex_Feed_Core {
	
	private static $instance = NULL; //instance store
		
	private function __construct() {
        
		/* request */
        add_action('init', array($this,'custom_query_vars') );
        add_action('template_redirect', array($this, 'custom_templates_redirect'));
		add_action('parse_query', array($this, 'custom_request'));
		
		/* settings */
		add_action( 'admin_init', array($this, 'settings_init'));
		
		/* formatting */
		add_filter('the_title_rss', array($this, 'full_text_formatting'));
		add_filter('the_excerpt_rss', array($this, 'full_text_formatting'));
		add_filter('the_content_feed', array($this, 'full_text_formatting'));
		add_filter('layf_category', array($this, 'full_text_formatting'), 10);
		add_filter('layf_author', array($this, 'full_text_formatting'), 10);
		
		/* robots txt */
		add_filter('robots_txt', array($this, 'robots_txt_permission'), 2, 2);
    }
		
	/* instance */
    public static function get_instance(){
        
        if (NULL === self :: $instance)
			self :: $instance = new self;
					
		return self :: $instance;
    }       
	
	static function on_activation() {
		flush_rewrite_rules(false);
	}
	
	static function on_deactivation() {
		flush_rewrite_rules(false);
	}
	
	
	/* request */	
	function custom_query_vars(){
        global $wp;
        
        $wp->add_query_var('yandex_feed');
		add_rewrite_rule('^yandex/([^/]*)/?', 'index.php?yandex_feed=$matches[1]', 'top');
    }
	
	function custom_request($query) {
		
		if(isset($query->query_vars['yandex_feed']) && $query->query_vars['yandex_feed'] == 'news') {
			$pt = get_option('layf_post_types', 'post');
			$pt = explode(',', $pt);
			$pt = array_map('trim', $pt);
			
			$query->query_vars['post_type'] = $pt;
			$query->query_vars['posts_per_page'] = -1;
			
			$limit = strtotime('- 8 days'); //Limited by Yandex rules
			$query->query_vars['date_query'] = array(
				array(
					'after' => array(
						'year'  => date('Y', $limit),
						'month' => date('m', $limit),
						'day'   => date('d', $limit),
					)
				)
			);
			//var_dump($query->query_vars); die();
		}
		
	}
	
	function custom_templates_redirect(){
		global $wp_query;
        
		$qv = get_query_var('yandex_feed');
		
		if('news' == $qv){
			
			include(LAYF_PLUGIN_DIR.'inc/feed.php');
			die();
		}
	}
	
	function robots_txt_permission($output, $public){
		
		if($public == 0)
			return $output;
		
$dir = "User-agent: Yandex
Allow: /yandex/news/

";
		$output = $dir.$output;
		
		return $output;
	}
	
	
	/* settings */
	function settings_init() {
 	 	
		add_settings_field(
			'layf_post_types',
			__('Post types for Ynadex.News feed', 'layf'),
			array($this, 'settngs_post_types_callback'),
			'reading',
			'default'
		);
		
		add_settings_field(
			'layf_feed_logo',
			__('Logo URL for feed description', 'layf'),
			array($this, 'settings_feed_logo_callback'),
			'reading',
			'default'
		);
 	
		register_setting( 'reading', 'layf_post_types' );
		register_setting( 'reading', 'layf_feed_logo' );
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
	
	/* Formatting */	
	
	function full_text_formatting($text){
		global $wp_query;
		
		if(!isset($wp_query->query_vars['yandex_feed']))
			return $text;
		
		$text = strip_tags($text);
		$text = htmlentities ($text, ENT_QUOTES, 'UTF-8', false);
		
		return $text;
	}
	
	
	/* Template helpers */
	static function item_enclosure(){
		global $post;
		
		$enclosure = $matches = array();
		$out = do_shortcode($post->post_content);
		//preg_match_all('!http://.+\.(?:jpe?g|png|gif)!Ui' , $out , $matches);
		preg_match_all('/<img(.*)src(.*)=(.*)"(.*)"/U', $out, $matches);
		
		
		
		if(!isset($matches[4]) || empty($matches)){
			$thumb_id = get_post_thumbnail_id($post->ID);
			if(!empty($thumb_id)){
				$enclosure[0] = wp_get_attachment_url($thumb_id);
			}
		}
		else {
			$enclosure = $matches[4];
		}
		
		if(empty($enclosure))
			return $enclosure;
		
		$res = array();
		foreach($enclosure as $i => $img){
			
			$mime = self::_get_mime($img);
			if(!empty($mime)){
				$res[] = array('url' => $img, 'mime' => $mime);
			}
		}
		
		//var_dump($res);		
		return $res;
	}
	
	static function _get_mime($img){
		//@to-do make this correct
		$mime = '';
		if(false !== strpos($img,'.jpg') || false !== strpos($img,'.jpeg')){
			$mime = 'image/jpeg';
		}
		elseif(false !== strpos($img, '.png')){
			$mime = 'image/png';
		}
		elseif(false !== strpos($img, '.gif')){
			$mime = 'image/gif';
		}
		
		return $mime;
	}
	
	static function item_related() {
		global $post;
		
		$links = array();
		/*$dom = new DOMDocument;
		$dom->loadHTML($post->post_content);
		$home = home_url();
		foreach ($dom->getElementsByTagName('a') as $node) {
			$parts = array();
			$parts = parse_url($node->getAttribute( 'href' ));
			if(isset($parts['host']) & false !== strpos($home, $parts['host'])){
				$links[]['url'] = $parts['host'];
			}
		}*/
		
		
		return $links;
	}
	
} //class

?>