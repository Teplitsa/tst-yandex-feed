<?php

class TstYandexNewsHooks {
    private static $error_transient = 'tst_yandex_news_error';
    private static $sync_delay = 10 * 60;

    public static function update_turbo_page_in_yandex($post_id) {

        // error_log("REQUEST_URI: " . $_SERVER["REQUEST_URI"]);
        // error_log("post_id:" . $post_id);
        // error_log("get_the_ID:" . get_the_ID());

        if(empty($GLOBALS['wp']->query_vars['rest_route']) 
            && ( empty($_POST['action']) || $_POST['action'] !== 'editpost' || empty($_POST['content']) )
        ) {
            // error_log('SKIP tstyn update');
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        if(!get_option('layf_enable_turbo')) {
            return;
        }

        // setup_postdata($post_id);
        global $post;
        if(!get_the_ID() && $post_id) {
            $post = get_post($post_id);
        }
        // error_log("after post setup get_the_ID:" . get_the_ID());

        if(!get_the_ID() || !get_the_title()) {
            return;
        }

        // error_log("run update in yandex...");
        
        // error_log("update_turbo_page_in_yandex...");
        $yandex_client = TstYandexNewsAPIClient::get_instance();

        TstYandexNewsShortcodes::setup_shortcodes();

        $sync_time = get_post_meta( $post_id, 'tstyn_sync_time', true);
        if($sync_time) {
            if(time() - intval($sync_time) <= self::$sync_delay) {
                return;
            }
        }

        $error = "";
        try {
            $yandex_client->update_current_post_in_yandex();
            update_post_meta( $post_id, 'tstyn_sync_time', time());
        }
        catch(TstYandexNewsInvalidAuthTokenException $e) {
            $error = __( 'Turbo page update error: invalid auth token error', 'yandexnews-feed-by-teplitsa' );
        }
        catch(TstYandexNewsHostNotVerifiedException $e) {
            $error = __( 'Turbo page update error: host not verified', 'yandexnews-feed-by-teplitsa' ) . " " . La_Yandex_Feed_Core::get_host();
        }
        catch(TstYandexNewsResourceNotFoundException $e) {
            $error = __( 'Turbo page update error: resource not found', 'yandexnews-feed-by-teplitsa' ) . " " . La_Yandex_Feed_Core::get_host_id();
        }
        catch(Exception $e) {
            $error = __( 'Turbo page update error:', 'yandexnews-feed-by-teplitsa' ) . " " . $e->getMessage();
        }

        // error_log("get_the_ID: " . get_the_ID());
        // error_log("wp_is_post_revision: " . wp_is_post_revision( $post_id ));
        // error_log("get_the_ID: " . get_the_title());
        // error_log("sync result error: " . $error);

        if($error) {
            set_transient(self::$error_transient . $post_id, $error);

            update_post_meta( $post_id, 'tstyn_error', $error);
        }
        else {
            delete_post_meta( $post_id, 'tstyn_error');
        }

        wp_reset_postdata();
    }

    public static function show_update_turbo_page_in_yandex_error() {
        $post_id = get_the_ID();
        if(!$post_id) {
            return;
        }

        // error_log("show_update_turbo_page_in_yandex_error...");
        // error_log("post_id:" . $post_id);

        $error_message = get_transient( self::$error_transient . $post_id );

        if(!$error_message) {
            return;
        }

        delete_transient( self::$error_transient . $post_id );        
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo $error_message; ?></p>
        </div>
        <?php
    }

    public static function remove_plugin_shortcodes_if_not_feed($content) {
        if(!TstYandexNewsShortcodes::is_shortcodes_setup()) {
            $content = TstYandexNewsShortcodes::strip_shortcodes($content);
        }

        return $content;
    }

    public static function register_post_meta() {
        $post_types = La_Yandex_Feed_Core::get_instance()->get_supported_post_types();
        foreach($post_types as $post_type) {
            register_post_meta( $post_type, 'tstyn_error', array(
                'show_in_rest' => true,
                'single' => true,
                'type' => 'string',
            ) );        
        }
    }
}

add_action( 'admin_notices', 'TstYandexNewsHooks::show_update_turbo_page_in_yandex_error' );
add_action( 'save_post', 'TstYandexNewsHooks::update_turbo_page_in_yandex' );
add_filter( 'the_content', 'TstYandexNewsHooks::remove_plugin_shortcodes_if_not_feed', 1 );
add_filter( 'init', 'TstYandexNewsHooks::register_post_meta', 10000 );