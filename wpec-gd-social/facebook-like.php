<?php

    function wpec_gd_fb_like( $args ) {
      
            $layout = $args["wp_fb_like_layout"];
            
            if( empty( $layout ) )
                $layout = 'standard';
            else
                $layout = strtolower( str_replace( " ", "_", $layout ) );

            $show_faces = $args["wp_fb_like_show_faces"];
            if( empty( $show_faces ) )
                $show_faces = 'true';
            else
                $show_faces = ( $show_faces == "Yes" ) ? 'true' : 'false';

            $action = $args["wp_fb_like_action"];
            if( empty( $action ) )
                $action = 'like';
            else
                $action = strtolower( $action );

            $width = ( empty( $args["wpec_fb_box_width"] ) ) ? '450' : $args["wpec_fb_box_width"];
            $height = ( empty( $args["wpec_fb_box_height"] ) ) ? '100' : $args["wpec_fb_box_height"];


            $font = apply_filters( 'wpec_gd_fb_like_font', 'arial' );
            $colorscheme = apply_filters( 'wpec_gd_fb_like_colorscheme', 'light' );

            $permalink = get_permalink();
            $scheme = ( is_ssl() ) ? 'https' : 'http';

            $output = '<div id="wp_fb_like_button"><iframe src="'.$scheme.'://www.facebook.com/plugins/like.php?href='.rawurlencode($permalink).'&amp;layout='.$layout.'&amp;show_faces='.$show_faces.'&amp;action='.$action.'&amp;font='.$font.'&amp;colorscheme='.$colorscheme.'&amp;width='.$width.'&amp;height='.$height.'" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width: '.$width.'px; height: '.$height.'px;"></iframe></div>';
           

            if( $args["wpec_tweet_this"] == "Yes" )                
                $output .= '<a href="http://twitter.com/share" class="twitter-share-button" data-count="horizontal">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>';
            

            return $output;
    }

?>