<?php

class TstYandexNewsHooks {
    private static $error_transient = 'tst_yandex_news_error';

    public static function update_turbo_page_in_yandex($post_id) {

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }
        
        error_log("update_turbo_page_in_yandex...");
        $yandex_client = TstYandexNewsAPIClient::get_instance();

        TstYandexNewsShortcodes::setup_shortcodes();        

        // $yandex_client->update_current_post_in_yandex();

        try {
            $yandex_client->update_current_post_in_yandex();
        }
        catch(TstYandexNewsInvalidAuthTokenException $e) {
            set_transient(self::$error_transient, __( 'Turbo page update error: invalid auth token error', 'yandexnews-feed-by-teplitsa' ));
        }
        catch(TstYandexNewsHostNotVerifiedException $e) {
            set_transient(self::$error_transient, __( 'Turbo page update error: hot not verified', 'yandexnews-feed-by-teplitsa' ));
        }
        catch(Exception $e) {
            set_transient(self::$error_transient, __( 'Turbo page update error', 'yandexnews-feed-by-teplitsa' ));
        }

    }

    public static function show_update_turbo_page_in_yandex_error() {
        $error_message = get_transient( self::$error_transient );
        error_log("error_message: " .  $error_message);

        if(!$error_message) {
            return;
        }

        delete_transient( self::$error_transient );        
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo $error_message; ?></p>
        </div>
        <?php
    }

    public static function remove_plugin_shortcodes_if_not_if_feed($content) {
        error_log("is_shortcodes_setup=" . TstYandexNewsShortcodes::is_shortcodes_setup());

        if(!TstYandexNewsShortcodes::is_shortcodes_setup()) {
            error_log("content-BEFORE=" . $content);
            $content = TstYandexNewsShortcodes::strip_shortcodes($content);
            error_log("content-AFTER=" . $content);
        }

        return $content;
    }
}

add_action( 'admin_notices', 'TstYandexNewsHooks::show_update_turbo_page_in_yandex_error' );
add_action( 'save_post', 'TstYandexNewsHooks::update_turbo_page_in_yandex' );
add_filter( 'the_content', 'TstYandexNewsHooks::remove_plugin_shortcodes_if_not_if_feed', 1 );