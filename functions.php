<?php
//Nav menu items

function tinymce_excerpt_js(){ 
    global $current_screen;
    if( 'wpsc-product' != $current_screen->id )
        return;
    ?>
<script type="text/javascript">
	jQuery(document).ready( tinymce_excerpt );
            function tinymce_excerpt() {
		jQuery("#additional_description").addClass("theEditor");
		tinyMCE.execCommand("mceAddControl", false, "excerpt");
	    }
</script>
<?php }
add_action( 'admin_head-post.php', 'tinymce_excerpt_js');
add_action( 'admin_head-post-new.php', 'tinymce_excerpt_js');
function tinymce_css(){ 
    global $current_screen;
    if( 'wpsc-product' != $current_screen->id )
        return;
    ?>
<style type='text/css'>
	    #wpsc_additional_desc .inside{margin:0;padding:0;background:#fff;}
	    #wpsc_additional_desc .inside p{padding:0px 0px 5px 10px;}
	    #wpsc_additional_desc #excerpteditorcontainer { border-style: solid; padding: 0; }
</style>
<?php }

remove_filter( 'excerpt_more', 'twentyten_auto_excerpt_more' );
add_action( 'admin_head-post.php', 'tinymce_css');
add_action( 'admin_head-post-new.php', 'tinymce_css');
add_filter( 'excerpt_more', 'group_deals_excerpt_more' );

function group_deals_excerpt_more() {
    return '';
}

add_action( 'after_setup_theme', 'wpec_gd_remove_custom_header', 13 );

function wpec_gd_remove_custom_header() {
    remove_action( 'post_thumbnail_html','wpsc_the_featured_image_fix' );
}

add_action( 'after_setup_theme', 'group_deals_theme_setup' );

function group_deals_theme_setup() {

    // Add default posts and comments RSS feed links to <head>.
    add_theme_support( 'automatic-feed-links' );

    // This theme uses wp_nav_menu() in one location.
    register_nav_menu( 'primary', __( 'Primary Menu', 'twentyeleven' ) );

    // Add support for a variety of post formats
    add_theme_support( 'post-formats', array( 'aside', 'link', 'gallery', 'status', 'quote', 'image' ) );

    // Add support for custom backgrounds
    add_custom_background();

    // This theme uses Featured Images (also known as post thumbnails) for per-post/per-page Custom Header images
    add_theme_support( 'post-thumbnails' );

    // Turn on random header image rotation by default.
    add_theme_support( 'custom-header', array( 'random-default' => true ) );
}

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 */
function twentyeleven_page_menu_args( $args ) {
    $args['show_home'] = true;
    return $args;
}
add_filter( 'wp_page_menu_args', 'twentyeleven_page_menu_args' );

/**
 * Register our sidebars and widgetized areas. Also register the default Epherma widget.
 *
 * @since Twenty Eleven 1.0
 */
function twentyeleven_widgets_init() {

    register_sidebar( array(
        'name' => __( 'Main Sidebar', 'twentyeleven' ),
        'id' => 'primary-widget-area',
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => "</aside>",
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ) );

    register_sidebar( array(
        'name' => __( 'Secondary Sidebar', 'twentyeleven' ),
        'id' => 'secondary-widget-area',
        'description' => __( 'The sidebar for widget underneath company info', 'twentyeleven' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => "</aside>",
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ) );

    register_sidebar( array(
        'name' => __( 'Footer Area One', 'twentyeleven' ),
        'id' => 'first-footer-widget-area',
        'description' => __( 'An optional widget area for your site footer', 'twentyeleven' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => "</aside>",
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ) );

    register_sidebar( array(
        'name' => __( 'Footer Area Two', 'twentyeleven' ),
        'id' => 'second-footer-widget-area',
        'description' => __( 'An optional widget area for your site footer', 'twentyeleven' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => "</aside>",
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ) );

    register_sidebar( array(
        'name' => __( 'Footer Area Three', 'twentyeleven' ),
        'id' => 'third-footer-widget-area',
        'description' => __( 'An optional widget area for your site footer', 'twentyeleven' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => "</aside>",
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ) );

    register_sidebar( array(
        'name' => __( 'Footer Area Four', 'twentyeleven' ),
        'id' => 'fourth-footer-widget-area',
        'description' => __( 'An optional widget area for your site footer', 'twentyeleven' ),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => "</aside>",
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ) );
}
add_action( 'widgets_init', 'twentyeleven_widgets_init' );

/**
 * Display navigation to next/previous pages when applicable
 */
function twentyeleven_content_nav( $nav_id ) {
    global $wp_query;

    if ( $wp_query->max_num_pages > 1 ) : ?>
        <nav id="<?php echo $nav_id; ?>">
            <h3 class="assistive-text"><?php _e( 'Post navigation', 'twentyeleven' ); ?></h3>
            <div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', 'twentyeleven' ) ); ?></div>
            <div class="nav-next"><?php previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', 'twentyeleven' ) ); ?></div>
        </nav><!-- #nav-above -->
    <?php endif;
}

/**
 * Return the URL for the first link found in the post content.
 *
 * @since Twenty Eleven 1.0
 * @return string|bool URL or false when no link is present.
 */
function twentyeleven_url_grabber() {
    if ( ! preg_match( '/<a\s[^>]*?href=[\'"](.+?)[\'"]/is', get_the_content(), $matches ) )
        return false;

    return esc_url_raw( $matches[1] );
}

/**
 * Count the number of footer sidebars to enable dynamic classes for the footer
 */
function twentyeleven_footer_sidebar_class() {
    $count = 0;

    if ( is_active_sidebar( 'sidebar-3' ) )
        $count++;

    if ( is_active_sidebar( 'sidebar-4' ) )
        $count++;

    if ( is_active_sidebar( 'sidebar-5' ) )
        $count++;

    $class = '';

    switch ( $count ) {
        case '1':
            $class = 'one';
            break;
        case '2':
            $class = 'two';
            break;
        case '3':
            $class = 'three';
            break;
    }

    if ( $class )
        echo 'class="' . $class . '"';
}

if ( ! function_exists( 'twentyeleven_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own twentyeleven_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Eleven 1.0
 */
function twentyeleven_comment( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    switch ( $comment->comment_type ) :
        case 'pingback' :
        case 'trackback' :
    ?>
    <li class="post pingback">
        <p><?php _e( 'Pingback:', 'twentyeleven' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( 'Edit', 'twentyeleven' ), '<span class="edit-link">', '</span>' ); ?></p>
    <?php
            break;
        default :
    ?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
        <article id="comment-<?php comment_ID(); ?>" class="comment">
            <footer class="comment-meta">
                <div class="comment-author vcard">
                    <?php
                        $avatar_size = 68;
                        if ( '0' != $comment->comment_parent )
                            $avatar_size = 39;

                        echo get_avatar( $comment, $avatar_size );

                        /* translators: 1: comment author, 2: date and time */
                        printf( __( '%1$s on %2$s <span class="says">said:</span>', 'twentyeleven' ),
                            sprintf( '<span class="fn">%s</span>', get_comment_author_link() ),
                            sprintf( '<a href="%1$s"><time pubdate datetime="%2$s">%3$s</time></a>',
                                esc_url( get_comment_link( $comment->comment_ID ) ),
                                get_comment_time( 'c' ),
                                /* translators: 1: date, 2: time */
                                sprintf( __( '%1$s at %2$s', 'twentyeleven' ), get_comment_date(), get_comment_time() )
                            )
                        );
                    ?>

                    <?php edit_comment_link( __( 'Edit', 'twentyeleven' ), '<span class="edit-link">', '</span>' ); ?>
                </div><!-- .comment-author .vcard -->

                <?php if ( $comment->comment_approved == '0' ) : ?>
                    <em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'twentyeleven' ); ?></em>
                    <br />
                <?php endif; ?>

            </footer>

            <div class="comment-content"><?php comment_text(); ?></div>

            <div class="reply">
                <?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply <span>&darr;</span>', 'twentyeleven' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
            </div><!-- .reply -->
        </article><!-- #comment-## -->

    <?php
            break;
    endswitch;
}
endif; // ends check for twentyeleven_comment()

if ( ! function_exists( 'twentyeleven_posted_on' ) ) :
/**
 * Prints HTML with meta information for the current post-date/time and author.
 * Create your own twentyeleven_posted_on to override in a child theme
 *
 * @since Twenty Eleven 1.0
 */
function twentyeleven_posted_on() {
    printf( __( '<span class="sep">Posted on </span><a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s" pubdate>%4$s</time></a><span class="by-author"> <span class="sep"> by </span> <span class="author vcard"><a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a></span></span>', 'twentyeleven' ),
        esc_url( get_permalink() ),
        esc_attr( get_the_time() ),
        esc_attr( get_the_date( 'c' ) ),
        esc_html( get_the_date() ),
        esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
        sprintf( esc_attr__( 'View all posts by %s', 'twentyeleven' ), get_the_author() ),
        esc_html( get_the_author() )
    );
}
endif;

/**
 * Adds two classes to the array of body classes.
 * The first is if the site has only had one author with published posts.
 * The second is if a singular post being displayed
 *
 * @since Twenty Eleven 1.0
 */
function twentyeleven_body_classes( $classes ) {

    if ( ! is_multi_author() ) {
        $classes[] = 'single-author';
    }

    if ( is_singular() && ! is_home() && ! is_page_template( 'showcase.php' ) && ! is_page_template( 'sidebar-page.php' ) )
        $classes[] = 'singular';

    return $classes;
}
add_filter( 'body_class', 'twentyeleven_body_classes' );


?>