( function( wp ) {
    if(!wp || !wp.data || !wp.data.select( 'core/editor' )) {
        // console.log("no editor");
        return
    }

    const { getCurrentPostAttribute } = wp.data.select("core/editor");

    let wasSavingPost = wp.data.select( 'core/editor' ).isSavingPost();
    let wasAutosavingPost = wp.data.select( 'core/editor' ).isAutosavingPost();
    let wasPreviewingPost = wp.data.select( 'core/editor' ).isPreviewingPost();
    
    wp.data.subscribe( () => {
      
        const isSavingPost = wp.data.select( 'core/editor' ).isSavingPost();
        const isAutosavingPost = wp.data.select( 'core/editor' ).isAutosavingPost();
        const isPreviewingPost = wp.data.select( 'core/editor' ).isPreviewingPost();
        const postMeta = wp.data.select( 'core/editor' ).getCurrentPostAttribute('meta');
        const post2yandexError = postMeta ? postMeta['tstyn_error'] : null;

        const isDoneSaving = (
            ( wasSavingPost && ! isSavingPost && ! wasAutosavingPost ) ||
            ( wasAutosavingPost && wasPreviewingPost && ! isPreviewingPost )
        );
    
        wasSavingPost = isSavingPost;
        wasAutosavingPost = isAutosavingPost;
        wasPreviewingPost = isPreviewingPost;
    
        if ( isDoneSaving ) {  
            let noticeId = 'tstyn-post2yandex-error';

            wp.data.dispatch( 'core/notices' ).removeNotice( noticeId );

            if(post2yandexError) {
                let errorMessage = decodeURIComponent(post2yandexError.replace(/[+]/g, " "));

                wp.data.dispatch( 'core/notices' ).createNotice(
                    'error', // success, info, warning, error.
                    errorMessage,
                    {
                        isDismissible: true,
                        id: noticeId,
                    }
                );
        }
        }
    });

} )( window.wp );
