( function( wp ) {
    if(!wp || !wp.data || !wp.data.select( 'core/editor' )) {
        return
    }

    const { getCurrentPostId, getCurrentPostAttribute } = wp.data.select("core/editor");

    let wasSavingPost = wp.data.select( 'core/editor' ).isSavingPost();
    let wasAutosavingPost = wp.data.select( 'core/editor' ).isAutosavingPost();
    let wasPreviewingPost = wp.data.select( 'core/editor' ).isPreviewingPost();
    
    wp.data.subscribe( () => {
      
        const isSavingPost = wp.data.select( 'core/editor' ).isSavingPost();
        const isAutosavingPost = wp.data.select( 'core/editor' ).isAutosavingPost();
        const isPreviewingPost = wp.data.select( 'core/editor' ).isPreviewingPost();
        const isSavePostError = wp.data.select('core/editor').didPostSaveRequestFail();
        const post_id = getCurrentPostId();
        const post_meta = getCurrentPostAttribute('meta');
        const post_tstyn_error = post_meta ? post_meta['tstyn_error'] : null;
        console.log('post_tstyn_error:', post_tstyn_error);

        const isDoneSaving = (
            ( wasSavingPost && ! isSavingPost && ! wasAutosavingPost ) ||
            ( wasAutosavingPost && wasPreviewingPost && ! isPreviewingPost )
        );
    
        wasSavingPost = isSavingPost;
        wasAutosavingPost = isAutosavingPost;
        wasPreviewingPost = isPreviewingPost;
    
        if ( isDoneSaving ) {  
            console.log("post_id:", post_id);
            let cookieName = 'tstyn-post2yandex-error';
            const post2yandexError = post_tstyn_error;
            // let post2yandexError = wpCookies.get( cookieName );

            console.log("isDoneSaving...");
            console.log("post2yandexError:", post2yandexError);

            wp.data.dispatch( 'core/notices' ).removeNotice( cookieName );

            if(post2yandexError) {
                let errorMessage = decodeURIComponent(post2yandexError.replace(/[+]/g, " "));

                wp.data.dispatch( 'core/notices' ).createNotice(
                    'error', // success, info, warning, error.
                    errorMessage,
                    {
                        isDismissible: true,
                        id: cookieName,
                    }
                );
            
                // wpCookies.set( cookieName, '', - 48 * 60 * 60, "/" );
        }
        }
    });

} )( window.wp );

postboxes.add_postbox_toggles( 'settings_page_layf_settings' );