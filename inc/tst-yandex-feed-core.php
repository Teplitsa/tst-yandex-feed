<?php
if(!defined('ABSPATH')) die; // Die if accessed directly

class La_Yandex_Feed_Core {
	
    private $query_cache_key = 'tst_yandex_news_cache';
    private $query_cache_data = NULL;
    private $query_cache_expire = 0;
    private static $yandex_turbo_allowed_tags = '<p><a><h1><h2><h3><figure><img><figcaption><header><ul><ol><li><video><source><br><b><strong><i><em><sup><sub><ins><del><small><big><pre><abbr><u><table><tr><td><th><tbody><col><thead><tfoot><button><iframe><embed><object><param>';
    public static $yandex_turbo_feed_min_limit = 300;
    public static $get_post_cache = null;
    public static $get_post_cache_max_length = 50;
    
	private static $instance = NULL; //instance store
		
	private function __construct() {
		
		/* request */
        add_action('init', array($this,'custom_query_vars') );
        add_action('template_redirect', array($this, 'custom_templates_redirect'));
		add_action('parse_query', array($this, 'custom_request'), 11 );
		add_filter( 'status_header', array($this, 'set_empty_feed_20ok_status'), 10, 2 );
		
		/* cache */
		add_filter( 'posts_results', array($this, 'cache_post_query'), 10, 2 );
		add_filter( 'posts_request', array($this, 'cache_pre_query'), 10, 2 );
				
		/* formatting */
		add_filter('the_title_rss', array($this, 'full_text_formatting'), 15);
		add_filter('the_excerpt_rss', array($this, 'full_text_formatting'), 15);

        add_filter('layf_category', array($this, 'full_text_formatting'), 15);
		add_filter('layf_author', array($this, 'full_text_formatting'), 15);
		add_filter('layf_related_link_text', array($this, 'full_text_formatting'), 15);
		add_filter('layf_content_feed', array($this, 'full_text_formatting'), 15);
				
		/* robots txt */
		add_filter('robots_txt', array($this, 'robots_txt_permission'), 2, 2);
		
		/* admin */
		$this->admin_setup();
    }
    
    public function cache_pre_query( $request, $query ){
    
        if(isset($query->query_vars['yandex_feed']) && $query->query_vars['yandex_feed'] == 'news') {
            
            $feed_cache_ttl = (int)get_option('layf_feed_cache_ttl', 0);
            
            if( $feed_cache_ttl ) {
                
                if ( $this->cache_get() !== NULL ){
                    $request = NULL;
                }
            }
        }
    
        return $request;
    }
    
    public function cache_post_query( $posts, $query ){
        if(isset($query->query_vars['yandex_feed']) && $query->query_vars['yandex_feed'] == 'news') {
            
            $feed_cache_ttl = (int)get_option('layf_feed_cache_ttl', 0);
            
            if( $feed_cache_ttl ) {
                
                $cached_posts = $this->cache_get();
                
                if ( $cached_posts !== NULL ) {
                    $posts = $cached_posts;
                }
                else {
                    $this->cache_set( $posts, $feed_cache_ttl * 60 * 60 );
                }
            }
        }
    
        return $posts;
    }
    
    public function cache_get() {
        $key = $this->query_cache_key;
        
        if( $this->query_cache_data === NULL ) {
            
            $data = get_option( $key, NULL );
            $data = maybe_unserialize( $data );
            
            if( $data ) {
                
                $this->query_cache_data = $data['data'];
                $this->query_cache_expire = $data['expire'];
                
                if( time() > $data['expire'] ) {
                    update_option( $key, '' );
                    $this->query_cache_data = NULL;
                    $this->query_cache_expire = 0;
                }
            }
            else {
                $this->query_cache_data = NULL;
            }
        }
        
        return $this->query_cache_data;
    }
    
    public function cache_set( $data, $ttl ) {
        $key = $this->query_cache_key;
        $data = array( 'data' => $data, 'expire' => time() + $ttl );
        update_option( $key, maybe_serialize( $data ) );
    }
    
    public function clear_cache() {
        delete_option($this->query_cache_key);
    }
	
	public function set_empty_feed_20ok_status($status_header, $header) {
		global $wp_query;
		$qv = get_query_var('yandex_feed'); 
		if('news' == $qv){
			if((int) $header == 404) {
				return status_header( 200 );
			}
		}
		return $status_header;			
	}
	
	/** instance */
    public static function get_instance(){
        
        if (NULL === self :: $instance)
			self :: $instance = new self;
					
		return self :: $instance;
    }       
	
	static function on_activation() {
		/* forse rewrite flush on time */
		update_option('layf_permalinks_flushed', 0);
	}
	
	static function on_deactivation() {
		/* forse rewrite flush on time */
		delete_option('layf_permalinks_flushed');
	}
	
	
	
	public function admin_setup(){
		
		if(!is_admin())
			return;
		
		require_once(LAYF_PLUGIN_DIR.'inc/admin.php');
		La_Yandex_Feed_Admin::get_instance();
	}
	
	
	public function get_supported_post_types() {
		$pt = get_option('layf_post_types', 'post');
		if(!trim($pt)) {
			$pt = 'post';
		}
		$pt = explode(',', $pt);
		$pt = array_map('trim', $pt);
		
		return $pt;
	}
	
	
	/** request */	
	public function custom_query_vars(){
        global $wp;
        
        $wp->add_query_var('yandex_feed');
		//deafult
		add_rewrite_rule('^yandex/([^/]*)/?', 'index.php?yandex_feed=$matches[1]', 'top');
		
		//custom
		$slug = trailingslashit(get_option('layf_custom_url', 'yandex/news')); //var_dump($slug);
		
		if(!empty($slug) && $slug != '/' && $slug != 'yandex/news/'){
			add_rewrite_rule("^$slug?", 'index.php?yandex_feed=news', 'top');
		}
		
		if( !get_option('layf_permalinks_flushed') ) {
			
                flush_rewrite_rules(false);
                update_option('layf_permalinks_flushed', 1);
           
        }
    }
	
	public function custom_request($query) {
		
		if(isset($query->query_vars['yandex_feed']) && $query->query_vars['yandex_feed'] == 'news') {
            $layf_enable_turbo = get_option('layf_enable_turbo');
			$pt = $this->get_supported_post_types();			
			
			$query->query_vars['post_type'] = $pt;
			
			$feed_items_limit_option = (int)get_option('layf_feed_items_limit', '');
			if($feed_items_limit_option > 0) {
			    $query->query_vars['posts_per_page'] = $feed_items_limit_option;
			}
			else {
			    $query->query_vars['posts_per_page'] = -1;
			}
			
            if($layf_enable_turbo) {
                if(empty($query->query_vars['posts_per_page'])) {
                    $query->query_vars['posts_per_page'] = self::$yandex_turbo_feed_min_limit;
                }
            }
            else {
                $layf_post_max_age = get_option('layf_post_max_age', LAYF_DEFAULT_MAX_POST_AGE);
                
                $limit = strtotime(sprintf('- %s days', $layf_post_max_age)); //Limited by Yandex rules
                $query->query_vars['date_query'] = array(
                    array(
                        'after' => array(
                            'year'  => date('Y', $limit),
                            'month' => date('m', $limit),
                            'day'   => date('d', $limit),
                        )
                    )
                );
            }
            
			$query->is_page = false;
			$query->is_home = false;
			
			//filtering by category
			$terms = get_option('layf_filter_terms', '');
			$terms_slug = get_option('layf_filter_terms_slug', '');
			if(!empty($terms) || !empty($terms_slug)){
				$tax = get_option('layf_filter_taxonomy', 'category');
				$terms = !empty($terms) ? array_map('intval', explode(',', $terms)) : array();
				
				if(!empty($terms_slug)) {
				    $terms_slug = explode(',', $terms_slug);
				    if(count($terms_slug)) {
				        $terms = array_merge($terms, get_terms(array('taxonomy' => $tax, 'hide_empty' => false, 'slug' => $terms_slug, 'fields' => 'ids')));
				    }
				}
				
				$query->query_vars['tax_query'][] = array(
					'taxonomy' => $tax,
					'field' => 'id',
					'terms' => $terms
				);
			}

			//exclude taxonomy terms
			$terms = get_option('layf_exclude_terms', '');
			$terms_slug = get_option('layf_exclude_terms_slug', '');
			if(!empty($terms) || !empty($terms_slug)){
				$tax = get_option('layf_exclude_taxonomy', 'category');
				$terms = !empty($terms) ? array_map('intval', explode(',', $terms)) : array();
				
				if(!empty($terms_slug)) {
				    $terms_slug = explode(',', $terms_slug);
				    if(count($terms_slug)) {
				        $terms = array_merge($terms, get_terms(array('taxonomy' => $tax, 'hide_empty' => false, 'slug' => $terms_slug, 'fields' => 'ids')));
				    }
				}
				
				$query->query_vars['tax_query'][] = array(
					'taxonomy' => $tax,
					'field' => 'id',
					'terms' => $terms,
					'operator' => 'NOT IN'
				);
			}

			//filtering by exclusion
			$query->query_vars['meta_query'] = array(
				array(
					'key' => 'layf_exclude_from_feed',
					'compare' => 'NOT EXISTS'
				),
				array(
					'key' => 'layf_exclude_from_feed',
					'value' => 1,
					'compare' => '!='
				),
				'relation' => 'OR'
			);
			
			//var_dump($query->query_vars); die();
		}
		
	}

	public function custom_templates_redirect(){
		global $wp_query;
        
		$qv = get_query_var('yandex_feed'); 
		
		if('news' == $qv){
			
			include(LAYF_PLUGIN_DIR.'inc/feed.php');
			die();
		}
	}
	
	public function robots_txt_permission($output, $public){
		
		if($public == 0)
			return $output;
		
$dir = "User-agent: Yandex
Allow: /yandex/news/

";
		$output = $dir.$output;
		
		return $output;
	}
		
	
	/** formatting */
	function full_text_formatting($text){
	
		$pattern = '\[(\[?)(embed|wp_caption|caption|gallery|playlist|audio|video)(?![\w-])([^\]\/]*(?:\/(?!\])[^\]\/]*)*?)(?:(\/)\]|\](?:([^\[]*+(?:\[(?!\/\2\])[^\[]*+)*+)\[\/\2\])?)(\]?)';
		$text = preg_replace_callback( "/$pattern/s", 'strip_shortcode_tag', $text );
		
		global $wp_query;
		
		if(empty($wp_query->query_vars['yandex_feed']))
			return $text;
		
		$text = wp_strip_all_tags($text);
		
		//remove multiply spaces
		$text = preg_replace('/\s\s+/', ' ', $text);
		$text = preg_replace('/(\r|\n|\r\n){3,}/', '', $text);
		
		
		
// 		return $text;
		return self::_valid_characters($text);
	}
	
	static function _valid_characters($text) {
		
		$text = htmlentities ($text, ENT_QUOTES, 'UTF-8', false);
		$ent_table = layf_get_chars_table();
		$text = strtr($text, $ent_table);
		
		
		
		return $text;
	}
	
	
	
	
	/** template helpers */
	static function get_the_content_feed() {
	    
		$post = layf_get_post();
		$content = $post->post_content;
		
		if(get_option('layf_remove_teaser_from_fulltext', '')) {
		    if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
		        $content_parts = explode( $matches[0], $content, 2 );
		        if(count($content_parts) > 1 && !empty($content_parts[1])) {
		            $content = $content_parts[1];
		        }
		    }
		}
		$content = str_replace(']]>', ']]&gt;', $content);
		
		add_filter('img_caption_shortcode', 'layf_filter_image_caption', 20, 3); //filter caption text
		add_filter( 'layf_content_feed', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 ); //embed media to HTML
		
		add_filter( 'layf_content_feed', 'wptexturize'        );
		add_filter( 'layf_content_feed', 'convert_smilies'    );
		add_filter( 'layf_content_feed', 'convert_chars'      );
		add_filter( 'layf_content_feed', 'wpautop'            );
		add_filter( 'layf_content_feed', 'shortcode_unautop'  );
		add_filter( 'layf_content_feed', 'do_shortcode'       );
        add_filter( 'layf_content_feed', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 ); //embed media to HTML
		
		if(get_option('layf_remove_shortcodes', '')) {
		    add_filter( 'layf_content_feed', 'layf_strip_all_shortcodes'   );
		}
        
		$content = preg_replace('/<p>\s*<\/p>/', '', $content );
        
		return apply_filters('layf_content_feed', $content);		
	}
	
	static function get_the_turbo_content() {
	    $post = layf_get_post();
	    $content = $post->post_content;
	    
	    if(get_option('layf_remove_teaser_from_fulltext', '')) {
	        if ( preg_match( '/<!--more(.*?)?-->/', $content, $matches ) ) {
	            $content_parts = explode( $matches[0], $content, 2 );
	            if(count($content_parts) > 1 && !empty($content_parts[1])) {
	                $content = $content_parts[1];
	            }
	        }
	    }
	    $content = str_replace(']]>', ']]&gt;', $content);
	    
	    add_filter( 'layf_turbo_content_feed', 'layf_process_site_video_shortcodes' );
	    add_filter('img_caption_shortcode', 'layf_filter_image_caption', 20, 3); //filter caption text
	    add_filter( 'layf_turbo_content_feed', array( $GLOBALS['wp_embed'], 'autoembed' ), 8 ); //embed media to HTML
	    
	    add_filter( 'layf_turbo_content_feed', 'wptexturize'        );
	    add_filter( 'layf_turbo_content_feed', 'convert_smilies'    );
	    add_filter( 'layf_turbo_content_feed', 'convert_chars'      );
	    add_filter( 'layf_turbo_content_feed', 'wpautop'            );
	    add_filter( 'layf_turbo_content_feed', 'shortcode_unautop'  );
        add_filter( 'layf_turbo_content_feed', 'do_shortcode'       );
        add_filter( 'layf_turbo_content_feed', array( $GLOBALS['wp_embed'], 'run_shortcode' ), 8 ); //embed media to HTML
        add_filter( 'layf_turbo_content_feed', 'layf_strip_all_shortcodes' );
	    add_filter( 'layf_turbo_content_feed', 'layf_process_site_video_tags', 12 );
        
	    $turbo_content = apply_filters('layf_turbo_content_feed', $content);
	    
	    $turbo_content = strip_tags( $turbo_content, self::$yandex_turbo_allowed_tags );
	    
	    $turbo_content = preg_replace('/<p>\s*<\/p>/', '', $turbo_content );
	    $turbo_content = preg_replace('/class\s*=\s*".*?"/', '', $turbo_content );
	    $turbo_content = preg_replace('/class\s*=\s*\'.*?\'/', '', $turbo_content );
	    $turbo_content = preg_replace('/\s+>/', '>', $turbo_content );
	    
	    $turbo_content = self::wrap_turbo_images($turbo_content);
	    $turbo_content = self::add_ads_blocks($turbo_content);
	    $turbo_content = self::add_header_with_thumbnail($turbo_content);
	    
	    $turbo_content = layf_wxr_cdata( $turbo_content );
	    
	    return $turbo_content;
	}
	
	static function wrap_turbo_images($turbo_content) {
	    
	    $post = layf_get_post();
	    $thumb_id = get_post_thumbnail_id($post->ID);
	    $thumb_url_no_suffix = '';
	    if(!empty($thumb_id)){
	        $thumb_url = wp_get_attachment_url($thumb_id);
	        $thumb_url = preg_replace('/http[s]?:/', '', $thumb_url);
	        $thumb_url_no_suffix = preg_replace('/(?:-\d+x\d+)?(\.\w+)$/', '$1', $thumb_url);
	    }
	    
	    preg_match_all('!(<img.*>)!Ui', $turbo_content, $matches);
	     
	    if(isset($matches[1]) && !empty($matches)){
	        foreach($matches[1] as $k => $v) {
	            if($thumb_url_no_suffix && strpos($v, $thumb_url_no_suffix) && $thumb_url != $thumb_url_no_suffix) {
	                $turbo_content = str_replace($v, "", $turbo_content);
	            }
	            #var_dump(preg_match('!<figure>(?:(?!<figure>).)*'. preg_quote($v).'.*?</figure>!is', $turbo_content));
	            elseif(!preg_match('!'. preg_quote($v).'\s*?</figure>!is', $turbo_content)) {
	                $turbo_content = str_replace($v, "<figure>{$v}</figure>", $turbo_content);
	            }
	        }
	    }
	     
	    return $turbo_content;
	}
	
	static function add_ads_blocks($turbo_content) {
	    
	    $layf_adnetwork_id_header = trim(get_option('layf_adnetwork_id_header', ''));
	    if($layf_adnetwork_id_header) {
	        $turbo_content = '<figure data-turbo-ad-id="header_ad_place"></figure>'.$turbo_content;
	    }
	    
	    $layf_adnetwork_id_footer = trim(get_option('layf_adnetwork_id_footer', ''));
	    if($layf_adnetwork_id_footer) {
	        $turbo_content = $turbo_content . '<figure data-turbo-ad-id="footer_ad_place"></figure>';
	    }
	    
	    return $turbo_content;
	}
	
	static function add_header_with_thumbnail($turbo_content) {
		
		$img_html = '';
		
		$post = layf_get_post();
	    $thumb_id = get_post_thumbnail_id($post->ID);
	    
	    if(!empty($thumb_id)){
	        
	        $attachment = layf_get_post( $thumb_id );
	        if($attachment) {
	            $caption = $attachment->post_excerpt;
	            if(!$caption) {
	                $caption = $attachment->post_content;
	            }
	        }
	        
	        if($caption) {
	            
	            add_filter( 'layf_figure_caption_content_feed', 'wptexturize'        );
	            add_filter( 'layf_figure_caption_content_feed', 'convert_smilies'    );
	            add_filter( 'layf_figure_caption_content_feed', 'convert_chars'      );
	            add_filter( 'layf_figure_caption_content_feed', 'wpautop'            );
	            add_filter( 'layf_figure_caption_content_feed', 'shortcode_unautop'  );
	            add_filter( 'layf_figure_caption_content_feed', 'do_shortcode'       );
                add_filter( 'layf_figure_caption_content_feed', 'layf_strip_all_shortcodes'   );
	            
                $caption = apply_filters('layf_figure_caption_content_feed', $caption);
	        }
	        
	        if( $caption ) {
	            $caption = '<figcaption>'.$caption.'</figcaption>';
	        }
	        
	        
	        $img_html = '<figure><img src="'.wp_get_attachment_url($thumb_id).'" />'.$caption.'</figure>';
	    }
	    
	    $header_html = '<header>'.$img_html.'<h1>'. get_the_title_rss() .'</h1></header>';
	    $turbo_content = $header_html . $turbo_content;
	     
	    return $turbo_content;
	}
	
	static function custom_the_excerpt_rss() {
	    
	    $excerpt = get_the_excerpt();
	    $excerpt = wp_strip_all_tags( $excerpt );
	    
	    add_filter( 'layf_excerpt_feed', 'layf_strip_all_shortcodes' );
	    add_filter( 'layf_excerpt_feed', 'layf_remove_more_tag', 1 );
        
	    $excerpt = apply_filters('layf_excerpt_feed', $excerpt);
        $excerpt = apply_filters('the_title_rss', $excerpt);
        
	    echo $excerpt;
	}
	
	/* @to-do: add support for support video files */
	static function item_enclosure(){ 
		global $post;
		
		$enclosure_from_content = $enclosure = $matches = $res = array();
		
		if(get_option('layf_include_post_thumbnail')) {
		    $thumb_id = get_post_thumbnail_id($post->ID);
		    if(!empty($thumb_id)){
		        $enclosure[0] = wp_get_attachment_url($thumb_id);
		    }
		}
		
		$out = do_shortcode($post->post_content);
		
		//preg_match_all('!http://.+\.(?:jpe?g|png|gif)!Ui' , $out , $matches);
		preg_match_all('!<img(.*)src(.*)=(.*)"(.*)"!U', $out, $matches);
		
		$site_domain = preg_replace('/http[s]?:\/\//', '', site_url());
		$site_domain = preg_replace('/\/.*/', '', $site_domain);
		
		if(isset($matches[4]) && !empty($matches)){
		    foreach($matches[4] as $k => $v) {
		        if(preg_match('/^(http[s]?:)?\/\/.*/', $v)) {
		            if(strpos($v, $site_domain) !== false) {
		                $enclosure_from_content[] = $v;
		            }
		        }
		        else {
		            $enclosure_from_content[] = $v;
		        }
		    }
		}
		$enclosure = array_merge($enclosure, $enclosure_from_content);
		
		if(empty($enclosure))
			return $enclosure;
				
		$enclosure = array_unique($enclosure);
		foreach($enclosure as $i => $img){
		    $enclosure[$i] = preg_replace('/-\d+x\d+(\.\w+)$/', '$1', $img);
		}
		$enclosure = array_unique($enclosure);
		
		foreach($enclosure as $i => $img){
			
			$mime = self::_get_mime($img);
			if(!empty($mime)){
				$res[] = array('url' => self::add_protocol($img), 'mime' => $mime);
			}
		}
		
		//var_dump($res);
		return $res;
	}
	
	static function _get_mime($img){
		//@to-do make this poetic
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
	
	public static function add_protocol( $url ) {
	    $url = preg_replace( '/^(http:|https:)/', '', $url );
	    $url = self::get_site_protocol() . $url;
	    return $url;
	}
	
	public static function get_site_protocol() {
	    $site_protocol = preg_replace( '/(.*?)\/\/.*/', '\1', site_url() );
	    return $site_protocol ? $site_protocol : ( is_ssl() ? 'https' : 'http' );
	}
	
	/* videos */
	static function item_media(){
		global $post;
		
		$matches = $res = array();
		$return = array();
		
		//include shorcodes and oembeds
        $out = $post->post_content;
		$out = do_shortcode($out);
		$out = $GLOBALS['wp_embed']->autoembed($out);
        
        //youtube
        $content_list = array($post->post_content, $out);
        $youtube_videos = array();
        foreach($content_list as $youtube_out) {
            $youtube_regexp = "/(?:http(?:s)?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([\w-]{10,12})/";
            preg_match_all($youtube_regexp, $youtube_out, $matches);		
            if(isset($matches[0]) && !empty($matches[0]))
                $youtube_videos = array_merge($res, $matches[0]); //append links
        }
        $youtube_videos = array_unique($youtube_videos);
			
		//modify $youtube_videos to be able add thumbnails
		if(!empty($youtube_videos)){ foreach($youtube_videos as $i => $url) {
			$thumbnail_url = self::get_youtube_thumbnail_url($url);
			$return[] = array('player' => $url, 'thumb' => $thumbnail_url);
		}}
		// youtube end
		
		$videos = get_attached_media( 'video', $post->ID );
		foreach($videos as $video) {
		    $return[] = array('content' => set_url_scheme($video->guid), 'type' => $video->post_mime_type);
		}
		
		//@to_do: add another video providers
		
		return apply_filters('layf_video_embeds', $return, $post->ID);
	}
	
	/* build related links block */	
	static function item_related() {
		global $post;
		
		$links = array();
		$links_data_raw = get_post_meta($post->ID, 'layf_related_links', true);
		if(empty($links_data_raw))
			return $links;
		
		$links_data_raw = str_replace("\n\r", "\n", $links_data_raw);
		$links_data_raw = explode("\n", $links_data_raw);
		$links_data_raw = array_map('trim', $links_data_raw);
		
		if(!empty($links_data_raw)){ foreach($links_data_raw as $link_raw) {
			$url = explode(' ', $link_raw);
			$link = array();
			if(isset($url[0]) && !empty($url[0])){
				$link['url'] = $url[0];
				$link['text'] = trim(str_replace($url[0], '', $link_raw));
								
				$links[] = $link;
			}
		}}
		//var_dump($links);		
		
		return $links;
	}
	
	static function get_proper_category($post_id) {
		$terms = get_option('layf_filter_terms', '');			
		$filter_tax = '';
		if(!empty($terms)){
			$filter_tax = $tax = get_option('layf_filter_taxonomy', 'category');
			$terms = array_map('intval', explode(',', $terms));
			$query->query_vars['tax_query'][] = array(
				'taxonomy' => $tax,
				'field' => 'id',
				'terms' => $terms
			);
		}
	
		$category_tax = apply_filters('layf_category_taxonomy', 'category', $post_id);
		$category = wp_get_object_terms($post_id, $category_tax);
		
		$category_name = '';
		if($filter_tax && $filter_tax == $category_tax && is_array($category) && !empty($terms)) {
			foreach($category as $cat) {
				if(array_search($cat->term_id, $terms) !== false && $cat->slug != 'uncategorized') {
					$category_name = $cat->name;
					break;
				}
			}
		}
		
		if(empty($category_name)) {
			if(count($category) > 1 && $category[0]->slug == 'uncategorized')
				$category = $category[1]->name;
			else
				$category = $category ? reset($category)->name : '-';
		}
		else {
			$category = $category_name;
		}

		$category = apply_filters('layf_category', $category, get_the_ID());
		return $category;
	}
	
	static function get_youtube_thumbnail_url($url) {
		$ret = '';
		if(preg_match('/youtube\.com/', $url) || preg_match('/youtu\.be/', $url)) {
			preg_match("#(?<=vi\/)[^&?/\n]+|(?<=v\/)[^&?/\n]+|(?<=user\/)[^&?/\n]+|(?<=embed\/)[^&?/\n]+|(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&?/\n]+(?=\?)|(?<=v=)[^&?/\n]+|(?<=youtu.be/)[^&?/\n]+#", $url, $youtube_id_matches);
			if($youtube_id_matches && count($youtube_id_matches)) {
				$youtube_video_id = $youtube_id_matches[0];
				$ret = 'https://img.youtube.com/vi/' . $youtube_video_id . '/0.jpg';
			}
		}
		return $ret;
	}
	
} //class


function layf_get_chars_table() {
	
$table = array(
    '&nbsp;'     => '&#160;',  # no-break space = non-breaking space, U+00A0 ISOnum
    '&iexcl;'    => '&#161;',  # inverted exclamation mark, U+00A1 ISOnum
    '&cent;'     => '&#162;',  # cent sign, U+00A2 ISOnum
    '&pound;'    => '&#163;',  # pound sign, U+00A3 ISOnum
    '&curren;'   => '&#164;',  # currency sign, U+00A4 ISOnum
    '&yen;'      => '&#165;',  # yen sign = yuan sign, U+00A5 ISOnum
    '&brvbar;'   => '&#166;',  # broken bar = broken vertical bar, U+00A6 ISOnum
    '&sect;'     => '&#167;',  # section sign, U+00A7 ISOnum
    '&uml;'      => '&#168;',  # diaeresis = spacing diaeresis, U+00A8 ISOdia
    '&copy;'     => '&#169;',  # copyright sign, U+00A9 ISOnum
    '&ordf;'     => '&#170;',  # feminine ordinal indicator, U+00AA ISOnum
    '&laquo;'    => '&#171;',  # left-pointing double angle quotation mark = left pointing guillemet, U+00AB ISOnum
    '&not;'      => '&#172;',  # not sign, U+00AC ISOnum
    '&shy;'      => '&#173;',  # soft hyphen = discretionary hyphen, U+00AD ISOnum
    '&reg;'      => '&#174;',  # registered sign = registered trade mark sign, U+00AE ISOnum
    '&macr;'     => '&#175;',  # macron = spacing macron = overline = APL overbar, U+00AF ISOdia
    '&deg;'      => '&#176;',  # degree sign, U+00B0 ISOnum
    '&plusmn;'   => '&#177;',  # plus-minus sign = plus-or-minus sign, U+00B1 ISOnum
    '&sup2;'     => '&#178;',  # superscript two = superscript digit two = squared, U+00B2 ISOnum
    '&sup3;'     => '&#179;',  # superscript three = superscript digit three = cubed, U+00B3 ISOnum
    '&acute;'    => '&#180;',  # acute accent = spacing acute, U+00B4 ISOdia
    '&micro;'    => '&#181;',  # micro sign, U+00B5 ISOnum
    '&para;'     => '&#182;',  # pilcrow sign = paragraph sign, U+00B6 ISOnum
    '&middot;'   => '&#183;',  # middle dot = Georgian comma = Greek middle dot, U+00B7 ISOnum
    '&cedil;'    => '&#184;',  # cedilla = spacing cedilla, U+00B8 ISOdia
    '&sup1;'     => '&#185;',  # superscript one = superscript digit one, U+00B9 ISOnum
    '&ordm;'     => '&#186;',  # masculine ordinal indicator, U+00BA ISOnum
    '&raquo;'    => '&#187;',  # right-pointing double angle quotation mark = right pointing guillemet, U+00BB ISOnum
    '&frac14;'   => '&#188;',  # vulgar fraction one quarter = fraction one quarter, U+00BC ISOnum
    '&frac12;'   => '&#189;',  # vulgar fraction one half = fraction one half, U+00BD ISOnum
    '&frac34;'   => '&#190;',  # vulgar fraction three quarters = fraction three quarters, U+00BE ISOnum
    '&iquest;'   => '&#191;',  # inverted question mark = turned question mark, U+00BF ISOnum
    '&Agrave;'   => '&#192;',  # latin capital letter A with grave = latin capital letter A grave, U+00C0 ISOlat1
    '&Aacute;'   => '&#193;',  # latin capital letter A with acute, U+00C1 ISOlat1
    '&Acirc;'    => '&#194;',  # latin capital letter A with circumflex, U+00C2 ISOlat1
    '&Atilde;'   => '&#195;',  # latin capital letter A with tilde, U+00C3 ISOlat1
    '&Auml;'     => '&#196;',  # latin capital letter A with diaeresis, U+00C4 ISOlat1
    '&Aring;'    => '&#197;',  # latin capital letter A with ring above = latin capital letter A ring, U+00C5 ISOlat1
    '&AElig;'    => '&#198;',  # latin capital letter AE = latin capital ligature AE, U+00C6 ISOlat1
    '&Ccedil;'   => '&#199;',  # latin capital letter C with cedilla, U+00C7 ISOlat1
    '&Egrave;'   => '&#200;',  # latin capital letter E with grave, U+00C8 ISOlat1
    '&Eacute;'   => '&#201;',  # latin capital letter E with acute, U+00C9 ISOlat1
    '&Ecirc;'    => '&#202;',  # latin capital letter E with circumflex, U+00CA ISOlat1
    '&Euml;'     => '&#203;',  # latin capital letter E with diaeresis, U+00CB ISOlat1
    '&Igrave;'   => '&#204;',  # latin capital letter I with grave, U+00CC ISOlat1
    '&Iacute;'   => '&#205;',  # latin capital letter I with acute, U+00CD ISOlat1
    '&Icirc;'    => '&#206;',  # latin capital letter I with circumflex, U+00CE ISOlat1
    '&Iuml;'     => '&#207;',  # latin capital letter I with diaeresis, U+00CF ISOlat1
    '&ETH;'      => '&#208;',  # latin capital letter ETH, U+00D0 ISOlat1
    '&Ntilde;'   => '&#209;',  # latin capital letter N with tilde, U+00D1 ISOlat1
    '&Ograve;'   => '&#210;',  # latin capital letter O with grave, U+00D2 ISOlat1
    '&Oacute;'   => '&#211;',  # latin capital letter O with acute, U+00D3 ISOlat1
    '&Ocirc;'    => '&#212;',  # latin capital letter O with circumflex, U+00D4 ISOlat1
    '&Otilde;'   => '&#213;',  # latin capital letter O with tilde, U+00D5 ISOlat1
    '&Ouml;'     => '&#214;',  # latin capital letter O with diaeresis, U+00D6 ISOlat1
    '&times;'    => '&#215;',  # multiplication sign, U+00D7 ISOnum
    '&Oslash;'   => '&#216;',  # latin capital letter O with stroke = latin capital letter O slash, U+00D8 ISOlat1
    '&Ugrave;'   => '&#217;',  # latin capital letter U with grave, U+00D9 ISOlat1
    '&Uacute;'   => '&#218;',  # latin capital letter U with acute, U+00DA ISOlat1
    '&Ucirc;'    => '&#219;',  # latin capital letter U with circumflex, U+00DB ISOlat1
    '&Uuml;'     => '&#220;',  # latin capital letter U with diaeresis, U+00DC ISOlat1
    '&Yacute;'   => '&#221;',  # latin capital letter Y with acute, U+00DD ISOlat1
    '&THORN;'    => '&#222;',  # latin capital letter THORN, U+00DE ISOlat1
    '&szlig;'    => '&#223;',  # latin small letter sharp s = ess-zed, U+00DF ISOlat1
    '&agrave;'   => '&#224;',  # latin small letter a with grave = latin small letter a grave, U+00E0 ISOlat1
    '&aacute;'   => '&#225;',  # latin small letter a with acute, U+00E1 ISOlat1
    '&acirc;'    => '&#226;',  # latin small letter a with circumflex, U+00E2 ISOlat1
    '&atilde;'   => '&#227;',  # latin small letter a with tilde, U+00E3 ISOlat1
    '&auml;'     => '&#228;',  # latin small letter a with diaeresis, U+00E4 ISOlat1
    '&aring;'    => '&#229;',  # latin small letter a with ring above = latin small letter a ring, U+00E5 ISOlat1
    '&aelig;'    => '&#230;',  # latin small letter ae = latin small ligature ae, U+00E6 ISOlat1
    '&ccedil;'   => '&#231;',  # latin small letter c with cedilla, U+00E7 ISOlat1
    '&egrave;'   => '&#232;',  # latin small letter e with grave, U+00E8 ISOlat1
    '&eacute;'   => '&#233;',  # latin small letter e with acute, U+00E9 ISOlat1
    '&ecirc;'    => '&#234;',  # latin small letter e with circumflex, U+00EA ISOlat1
    '&euml;'     => '&#235;',  # latin small letter e with diaeresis, U+00EB ISOlat1
    '&igrave;'   => '&#236;',  # latin small letter i with grave, U+00EC ISOlat1
    '&iacute;'   => '&#237;',  # latin small letter i with acute, U+00ED ISOlat1
    '&icirc;'    => '&#238;',  # latin small letter i with circumflex, U+00EE ISOlat1
    '&iuml;'     => '&#239;',  # latin small letter i with diaeresis, U+00EF ISOlat1
    '&eth;'      => '&#240;',  # latin small letter eth, U+00F0 ISOlat1
    '&ntilde;'   => '&#241;',  # latin small letter n with tilde, U+00F1 ISOlat1
    '&ograve;'   => '&#242;',  # latin small letter o with grave, U+00F2 ISOlat1
    '&oacute;'   => '&#243;',  # latin small letter o with acute, U+00F3 ISOlat1
    '&ocirc;'    => '&#244;',  # latin small letter o with circumflex, U+00F4 ISOlat1
    '&otilde;'   => '&#245;',  # latin small letter o with tilde, U+00F5 ISOlat1
    '&ouml;'     => '&#246;',  # latin small letter o with diaeresis, U+00F6 ISOlat1
    '&divide;'   => '&#247;',  # division sign, U+00F7 ISOnum
    '&oslash;'   => '&#248;',  # latin small letter o with stroke, = latin small letter o slash, U+00F8 ISOlat1
    '&ugrave;'   => '&#249;',  # latin small letter u with grave, U+00F9 ISOlat1
    '&uacute;'   => '&#250;',  # latin small letter u with acute, U+00FA ISOlat1
    '&ucirc;'    => '&#251;',  # latin small letter u with circumflex, U+00FB ISOlat1
    '&uuml;'     => '&#252;',  # latin small letter u with diaeresis, U+00FC ISOlat1
    '&yacute;'   => '&#253;',  # latin small letter y with acute, U+00FD ISOlat1
    '&thorn;'    => '&#254;',  # latin small letter thorn, U+00FE ISOlat1
    '&yuml;'     => '&#255;',  # latin small letter y with diaeresis, U+00FF ISOlat1
    '&fnof;'     => '&#402;',  # latin small f with hook = function = florin, U+0192 ISOtech
    '&Alpha;'    => '&#913;',  # greek capital letter alpha, U+0391
    '&Beta;'     => '&#914;',  # greek capital letter beta, U+0392
    '&Gamma;'    => '&#915;',  # greek capital letter gamma, U+0393 ISOgrk3
    '&Delta;'    => '&#916;',  # greek capital letter delta, U+0394 ISOgrk3
    '&Epsilon;'  => '&#917;',  # greek capital letter epsilon, U+0395
    '&Zeta;'     => '&#918;',  # greek capital letter zeta, U+0396
    '&Eta;'      => '&#919;',  # greek capital letter eta, U+0397
    '&Theta;'    => '&#920;',  # greek capital letter theta, U+0398 ISOgrk3
    '&Iota;'     => '&#921;',  # greek capital letter iota, U+0399
    '&Kappa;'    => '&#922;',  # greek capital letter kappa, U+039A
    '&Lambda;'   => '&#923;',  # greek capital letter lambda, U+039B ISOgrk3
    '&Mu;'       => '&#924;',  # greek capital letter mu, U+039C
    '&Nu;'       => '&#925;',  # greek capital letter nu, U+039D
    '&Xi;'       => '&#926;',  # greek capital letter xi, U+039E ISOgrk3
    '&Omicron;'  => '&#927;',  # greek capital letter omicron, U+039F
    '&Pi;'       => '&#928;',  # greek capital letter pi, U+03A0 ISOgrk3
    '&Rho;'      => '&#929;',  # greek capital letter rho, U+03A1
    '&Sigma;'    => '&#931;',  # greek capital letter sigma, U+03A3 ISOgrk3
    '&Tau;'      => '&#932;',  # greek capital letter tau, U+03A4
    '&Upsilon;'  => '&#933;',  # greek capital letter upsilon, U+03A5 ISOgrk3
    '&Phi;'      => '&#934;',  # greek capital letter phi, U+03A6 ISOgrk3
    '&Chi;'      => '&#935;',  # greek capital letter chi, U+03A7
    '&Psi;'      => '&#936;',  # greek capital letter psi, U+03A8 ISOgrk3
    '&Omega;'    => '&#937;',  # greek capital letter omega, U+03A9 ISOgrk3
    '&alpha;'    => '&#945;',  # greek small letter alpha, U+03B1 ISOgrk3
    '&beta;'     => '&#946;',  # greek small letter beta, U+03B2 ISOgrk3
    '&gamma;'    => '&#947;',  # greek small letter gamma, U+03B3 ISOgrk3
    '&delta;'    => '&#948;',  # greek small letter delta, U+03B4 ISOgrk3
    '&epsilon;'  => '&#949;',  # greek small letter epsilon, U+03B5 ISOgrk3
    '&zeta;'     => '&#950;',  # greek small letter zeta, U+03B6 ISOgrk3
    '&eta;'      => '&#951;',  # greek small letter eta, U+03B7 ISOgrk3
    '&theta;'    => '&#952;',  # greek small letter theta, U+03B8 ISOgrk3
    '&iota;'     => '&#953;',  # greek small letter iota, U+03B9 ISOgrk3
    '&kappa;'    => '&#954;',  # greek small letter kappa, U+03BA ISOgrk3
    '&lambda;'   => '&#955;',  # greek small letter lambda, U+03BB ISOgrk3
    '&mu;'       => '&#956;',  # greek small letter mu, U+03BC ISOgrk3
    '&nu;'       => '&#957;',  # greek small letter nu, U+03BD ISOgrk3
    '&xi;'       => '&#958;',  # greek small letter xi, U+03BE ISOgrk3
    '&omicron;'  => '&#959;',  # greek small letter omicron, U+03BF NEW
    '&pi;'       => '&#960;',  # greek small letter pi, U+03C0 ISOgrk3
    '&rho;'      => '&#961;',  # greek small letter rho, U+03C1 ISOgrk3
    '&sigmaf;'   => '&#962;',  # greek small letter final sigma, U+03C2 ISOgrk3
    '&sigma;'    => '&#963;',  # greek small letter sigma, U+03C3 ISOgrk3
    '&tau;'      => '&#964;',  # greek small letter tau, U+03C4 ISOgrk3
    '&upsilon;'  => '&#965;',  # greek small letter upsilon, U+03C5 ISOgrk3
    '&phi;'      => '&#966;',  # greek small letter phi, U+03C6 ISOgrk3
    '&chi;'      => '&#967;',  # greek small letter chi, U+03C7 ISOgrk3
    '&psi;'      => '&#968;',  # greek small letter psi, U+03C8 ISOgrk3
    '&omega;'    => '&#969;',  # greek small letter omega, U+03C9 ISOgrk3
    '&thetasym;' => '&#977;',  # greek small letter theta symbol, U+03D1 NEW
    '&upsih;'    => '&#978;',  # greek upsilon with hook symbol, U+03D2 NEW
    '&piv;'      => '&#982;',  # greek pi symbol, U+03D6 ISOgrk3
    '&bull;'     => '&#8226;', # bullet = black small circle, U+2022 ISOpub
    '&hellip;'   => '&#8230;', # horizontal ellipsis = three dot leader, U+2026 ISOpub
    '&prime;'    => '&#8242;', # prime = minutes = feet, U+2032 ISOtech
    '&Prime;'    => '&#8243;', # double prime = seconds = inches, U+2033 ISOtech
    '&oline;'    => '&#8254;', # overline = spacing overscore, U+203E NEW
    '&frasl;'    => '&#8260;', # fraction slash, U+2044 NEW
    '&weierp;'   => '&#8472;', # script capital P = power set = Weierstrass p, U+2118 ISOamso
    '&image;'    => '&#8465;', # blackletter capital I = imaginary part, U+2111 ISOamso
    '&real;'     => '&#8476;', # blackletter capital R = real part symbol, U+211C ISOamso
    '&trade;'    => '&#8482;', # trade mark sign, U+2122 ISOnum
    '&alefsym;'  => '&#8501;', # alef symbol = first transfinite cardinal, U+2135 NEW
    '&larr;'     => '&#8592;', # leftwards arrow, U+2190 ISOnum
    '&uarr;'     => '&#8593;', # upwards arrow, U+2191 ISOnum
    '&rarr;'     => '&#8594;', # rightwards arrow, U+2192 ISOnum
    '&darr;'     => '&#8595;', # downwards arrow, U+2193 ISOnum
    '&harr;'     => '&#8596;', # left right arrow, U+2194 ISOamsa
    '&crarr;'    => '&#8629;', # downwards arrow with corner leftwards = carriage return, U+21B5 NEW
    '&lArr;'     => '&#8656;', # leftwards double arrow, U+21D0 ISOtech
    '&uArr;'     => '&#8657;', # upwards double arrow, U+21D1 ISOamsa
    '&rArr;'     => '&#8658;', # rightwards double arrow, U+21D2 ISOtech
    '&dArr;'     => '&#8659;', # downwards double arrow, U+21D3 ISOamsa
    '&hArr;'     => '&#8660;', # left right double arrow, U+21D4 ISOamsa
    '&forall;'   => '&#8704;', # for all, U+2200 ISOtech
    '&part;'     => '&#8706;', # partial differential, U+2202 ISOtech
    '&exist;'    => '&#8707;', # there exists, U+2203 ISOtech
    '&empty;'    => '&#8709;', # empty set = null set = diameter, U+2205 ISOamso
    '&nabla;'    => '&#8711;', # nabla = backward difference, U+2207 ISOtech
    '&isin;'     => '&#8712;', # element of, U+2208 ISOtech
    '&notin;'    => '&#8713;', # not an element of, U+2209 ISOtech
    '&ni;'       => '&#8715;', # contains as member, U+220B ISOtech
    '&prod;'     => '&#8719;', # n-ary product = product sign, U+220F ISOamsb
    '&sum;'      => '&#8721;', # n-ary sumation, U+2211 ISOamsb
    '&minus;'    => '&#8722;', # minus sign, U+2212 ISOtech
    '&lowast;'   => '&#8727;', # asterisk operator, U+2217 ISOtech
    '&radic;'    => '&#8730;', # square root = radical sign, U+221A ISOtech
    '&prop;'     => '&#8733;', # proportional to, U+221D ISOtech
    '&infin;'    => '&#8734;', # infinity, U+221E ISOtech
    '&ang;'      => '&#8736;', # angle, U+2220 ISOamso
    '&and;'      => '&#8743;', # logical and = wedge, U+2227 ISOtech
    '&or;'       => '&#8744;', # logical or = vee, U+2228 ISOtech
    '&cap;'      => '&#8745;', # intersection = cap, U+2229 ISOtech
    '&cup;'      => '&#8746;', # union = cup, U+222A ISOtech
    '&int;'      => '&#8747;', # integral, U+222B ISOtech
    '&there4;'   => '&#8756;', # therefore, U+2234 ISOtech
    '&sim;'      => '&#8764;', # tilde operator = varies with = similar to, U+223C ISOtech
    '&cong;'     => '&#8773;', # approximately equal to, U+2245 ISOtech
    '&asymp;'    => '&#8776;', # almost equal to = asymptotic to, U+2248 ISOamsr
    '&ne;'       => '&#8800;', # not equal to, U+2260 ISOtech
    '&equiv;'    => '&#8801;', # identical to, U+2261 ISOtech
    '&le;'       => '&#8804;', # less-than or equal to, U+2264 ISOtech
    '&ge;'       => '&#8805;', # greater-than or equal to, U+2265 ISOtech
    '&sub;'      => '&#8834;', # subset of, U+2282 ISOtech
    '&sup;'      => '&#8835;', # superset of, U+2283 ISOtech
    '&nsub;'     => '&#8836;', # not a subset of, U+2284 ISOamsn
    '&sube;'     => '&#8838;', # subset of or equal to, U+2286 ISOtech
    '&supe;'     => '&#8839;', # superset of or equal to, U+2287 ISOtech
    '&oplus;'    => '&#8853;', # circled plus = direct sum, U+2295 ISOamsb
    '&otimes;'   => '&#8855;', # circled times = vector product, U+2297 ISOamsb
    '&perp;'     => '&#8869;', # up tack = orthogonal to = perpendicular, U+22A5 ISOtech
    '&sdot;'     => '&#8901;', # dot operator, U+22C5 ISOamsb
    '&lceil;'    => '&#8968;', # left ceiling = apl upstile, U+2308 ISOamsc
    '&rceil;'    => '&#8969;', # right ceiling, U+2309 ISOamsc
    '&lfloor;'   => '&#8970;', # left floor = apl downstile, U+230A ISOamsc
    '&rfloor;'   => '&#8971;', # right floor, U+230B ISOamsc
    '&lang;'     => '&#9001;', # left-pointing angle bracket = bra, U+2329 ISOtech
    '&rang;'     => '&#9002;', # right-pointing angle bracket = ket, U+232A ISOtech
    '&loz;'      => '&#9674;', # lozenge, U+25CA ISOpub
    '&spades;'   => '&#9824;', # black spade suit, U+2660 ISOpub
    '&clubs;'    => '&#9827;', # black club suit = shamrock, U+2663 ISOpub
    '&hearts;'   => '&#9829;', # black heart suit = valentine, U+2665 ISOpub
    '&diams;'    => '&#9830;', # black diamond suit, U+2666 ISOpub
//     '&quot;'     => '&#34;',   # quotation mark = APL quote, U+0022 ISOnum
//     '&amp;'      => '&#38;',   # ampersand, U+0026 ISOnum
//     '&lt;'       => '&#60;',   # less-than sign, U+003C ISOnum
//     '&gt;'       => '&#62;',   # greater-than sign, U+003E ISOnum
    '&OElig;'    => '&#338;',  # latin capital ligature OE, U+0152 ISOlat2
    '&oelig;'    => '&#339;',  # latin small ligature oe, U+0153 ISOlat2
    '&Scaron;'   => '&#352;',  # latin capital letter S with caron, U+0160 ISOlat2
    '&scaron;'   => '&#353;',  # latin small letter s with caron, U+0161 ISOlat2
    '&Yuml;'     => '&#376;',  # latin capital letter Y with diaeresis, U+0178 ISOlat2
    '&circ;'     => '&#710;',  # modifier letter circumflex accent, U+02C6 ISOpub
    '&tilde;'    => '&#732;',  # small tilde, U+02DC ISOdia
    '&ensp;'     => '&#8194;', # en space, U+2002 ISOpub
    '&emsp;'     => '&#8195;', # em space, U+2003 ISOpub
    '&thinsp;'   => '&#8201;', # thin space, U+2009 ISOpub
    '&zwnj;'     => '&#8204;', # zero width non-joiner, U+200C NEW RFC 2070
    '&zwj;'      => '&#8205;', # zero width joiner, U+200D NEW RFC 2070
    '&lrm;'      => '&#8206;', # left-to-right mark, U+200E NEW RFC 2070
    '&rlm;'      => '&#8207;', # right-to-left mark, U+200F NEW RFC 2070
    '&ndash;'    => '&#8211;', # en dash, U+2013 ISOpub
    '&mdash;'    => '&#8212;', # em dash, U+2014 ISOpub
    '&lsquo;'    => '&#8216;', # left single quotation mark, U+2018 ISOnum
    '&rsquo;'    => '&#8217;', # right single quotation mark, U+2019 ISOnum
    '&sbquo;'    => '&#8218;', # single low-9 quotation mark, U+201A NEW
    '&ldquo;'    => '&#8220;', # left double quotation mark, U+201C ISOnum
    '&rdquo;'    => '&#8221;', # right double quotation mark, U+201D ISOnum
    '&bdquo;'    => '&#8222;', # double low-9 quotation mark, U+201E NEW
    '&dagger;'   => '&#8224;', # dagger, U+2020 ISOpub
    '&Dagger;'   => '&#8225;', # double dagger, U+2021 ISOpub
    '&permil;'   => '&#8240;', # per mille sign, U+2030 ISOtech
    '&lsaquo;'   => '&#8249;', # single left-pointing angle quotation mark, U+2039 ISO proposed
    '&rsaquo;'   => '&#8250;', # single right-pointing angle quotation mark, U+203A ISO proposed
    '&euro;'     => '&#8364;', # euro sign, U+20AC NEW
//     '&apos;'     => '&#39;',   # apostrophe = APL quote, U+0027 ISOnum
);

return $table;
}


function layf_filter_image_caption($out, $attr, $content) {
		
	return $content;			
}


function layf_strip_all_shortcodes($text){
    $text = preg_replace("/\[[^\]]+\]/", '', $text);  #strip shortcode
    return $text;
}

function layf_get_post_thumbnail_img($post_id) {
    $thumb_id = get_post_thumbnail_id($post_id);
    if($thumb_id) {
        $thumb_url = wp_get_attachment_url($thumb_id);
    }
    else {
        $thumb_url = site_url(); // dirty hack, that works in sandbox
    }
    
    $thumb_url = preg_replace('/http[s]?:/', '', $thumb_url);
    $video_preview_img = "<img src=\"{$thumb_url}\" />";
    
    return $video_preview_img;
}

function layf_compose_video_figure($video_url, $video_preview_img, $video_mime_type=null) {
    if(!$video_mime_type) {
        $video_mime_type = "video/mp4";
    }
    return "<figure><video><source src=\"{$video_url}\" type=\"{$video_mime_type}\"/></video>{$video_preview_img}</figure>";
}

function layf_get_post_mime_type_by_guid($guid) {
    global $wpdb;    
    return $wpdb->get_var( $wpdb->prepare( "SELECT post_mime_type FROM $wpdb->posts WHERE guid=%s", $guid ) );            
}

function layf_process_site_video_shortcodes($turbo_content) {
    
    preg_match_all('!(\[video.*mp4="(.*?)".*\]\[/video\])!Ui', $turbo_content, $matches);
    
    if(isset($matches[2]) && !empty($matches)){
        
        $post = layf_get_post();
        $video_preview_img = layf_get_post_thumbnail_img($post->ID);
        
        foreach($matches[2] as $k => $v) {
            $shortcode = isset($matches[1][$k]) ? $matches[1][$k] : null;
            if($shortcode) {
                $mime_type = layf_get_post_mime_type_by_guid($v);
                $turbo_content = str_replace($shortcode, layf_compose_video_figure($v, $video_preview_img, $mime_type), $turbo_content);
            }
        }
    }
    
    return $turbo_content;
}

function layf_is_in_tags($video_tag, $video_tags) {
    $tag_already_exist = false;
    
    foreach($video_tags as $tag) {
        if(strpos($tag, $video_tag) !== false) {
            $tag_already_exist = true;
            break;
        }
    }
    
    return $tag_already_exist;
}

function layf_process_site_video_tags($turbo_content) {

    preg_match_all('!(<figure.*?>\s*<video.*?>\s*<source.*?src="(.*?)".*?>.*?</video>.*?</figure>)!i', $turbo_content, $matches);
    $ok_video_tags = array();
    if(isset($matches[2]) && !empty($matches)){
        foreach($matches[2] as $k => $v) {
            $video_tag = isset($matches[1][$k]) ? $matches[1][$k] : null;
            if($video_tag) {
                $ok_video_tags[] = $video_tag;
            }
        }
    }

    $video_tags = array();
    
    preg_match_all('!(<figure.*?>\s*<video[^>]*?\s+src="(.*?)">\s*?</video>.*?</figure>)!i', $turbo_content, $matches);
    if(isset($matches[2]) && !empty($matches)){
        foreach($matches[2] as $k => $v) {
            $video_tag = isset($matches[1][$k]) ? $matches[1][$k] : null;
            if($video_tag) {
                if(layf_is_in_tags($video_tag, $ok_video_tags)) {
                    continue;
                }
                
                $video_tags[] = array('tag' => $video_tag, 'src' => $v);
            }
        }
    }
        
    preg_match_all('!(<video[^>]*?\s+src="(.*?)">\s*?</video>)!i', $turbo_content, $matches);
    if(isset($matches[2]) && !empty($matches)){
        foreach($matches[2] as $k => $v) {
            $video_tag = isset($matches[1][$k]) ? $matches[1][$k] : null;
            
            if($video_tag) {
                if(layf_is_in_tags($video_tag, $ok_video_tags)) {
                    continue;
                }
                
                $tag_already_exist = false;
                foreach($video_tags as $v) {
                    if(strpos($v['tag'], $video_tag) !== false) {
                        $tag_already_exist = true;
                        break;
                    }
                }
                
                if(!$tag_already_exist) {
                    $video_tags[] = array('tag' => $video_tag, 'src' => $v);
                }
            }
        }
    }

    preg_match_all('!(<video.*?>\s*<source.*?src="(.*?)".*?>.*?</video>)!i', $turbo_content, $matches);
    if(isset($matches[2]) && !empty($matches)){
        foreach($matches[2] as $k => $v) {
            $video_tag = isset($matches[1][$k]) ? $matches[1][$k] : null;
            
            if($video_tag) {
                if(layf_is_in_tags($video_tag, $ok_video_tags)) {
                    
                    if(@$_GET['debug'] && get_the_ID() == 104747) {
                        echo "\n\n\n=================================\n\n\n";
                        echo "skip in ok";
                    }
                    continue;
                }
            
                $video_params = array('tag' => $video_tag, 'src' => $v);
                
                preg_match_all('!type="(video/.*?)"!i', $video_tag, $mime_type_matches);
                if(isset($mime_type_matches[1]) && !empty($mime_type_matches[1])){
                    $video_params['mime_type'] = $mime_type_matches[1][0];
                }

                preg_match_all('!<img[^>]*src="(.*?)"!i', $video_tag, $mime_type_matches);
                if(isset($mime_type_matches[1]) && !empty($mime_type_matches[1])){
                    $video_params['preview_url'] = $mime_type_matches[1][0];
                }
                
                $video_tags[] = $video_params;
            }
        }
    }
    
    if(!empty($video_tags)) {
        
        $post = layf_get_post();
        $preview_img = layf_get_post_thumbnail_img($post->ID);

        foreach($video_tags as $v) {
            $mime_type = empty($v['mime_type']) ? layf_get_post_mime_type_by_guid($v['src']) : $v['mime_type'];
            $video_preview_img = empty($v['preview_url']) ? $preview_img : "<img src=\"{$v['preview_url']}\" />";
            $turbo_content = str_replace($v['tag'], layf_compose_video_figure($v['src'], $video_preview_img, $mime_type), $turbo_content);
        }
    }
    
    return $turbo_content;
}

function layf_remove_more_tag($text) {
    $text = preg_replace("/<!--more-->/i", '', $text);
    $text = preg_replace("/&nbsp;more&nbsp;&raquo;/i", '', $text);
    return $text;
}


function layf_wxr_cdata( $str ) {
    if ( ! seems_utf8( $str ) ) {
        $str = utf8_encode( $str );
    }
    // $str = ent2ncr(esc_html($str));
    $str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

    return $str;
}

function layf_get_post($post_id = null) {
    
    if(!La_Yandex_Feed_Core::$get_post_cache) {
        La_Yandex_Feed_Core::$get_post_cache = array();
    }
    
    if(!$post_id) {
        $post_id = get_the_ID();
    }
    
    if($post_id && !isset(La_Yandex_Feed_Core::$get_post_cache[$post_id])) {
        # clean cache array to save memory
        if(count(La_Yandex_Feed_Core::$get_post_cache) > La_Yandex_Feed_Core::$get_post_cache_max_length) {
            La_Yandex_Feed_Core::$get_post_cache = array();
        }
        
        La_Yandex_Feed_Core::$get_post_cache[$post_id] = get_post($post_id);
    }
    
    return $post_id ? La_Yandex_Feed_Core::$get_post_cache[$post_id] : null;
}

?>