<?php	 	
/**
 * @package WordPress
 * @subpackage getshopped
 *
 * Header
 */
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=UTF-8" />
<!--
	<meta http-equiv="Content-Type"	content="text/html;" />
	<meta charset="<?php	 	 bloginfo( 'charset' ); ?>" />
-->
	<title><?php	 	 wp_title( '' ); ?></title>
	<link rel="pingback" href="<?php	 	 bloginfo( 'pingback_url' ); ?>" />
	<?php	 	 if ( is_singular() && get_option( 'thread_comments' ) )
				wp_enqueue_script( 'comment-reply' ); ?>
<?php	 	 wp_head(); ?>
</head>
<body <?php	 	 body_class(); ?>>

<header>
	<div class="inner">
		<div class="wrapper">
			<div class="page-title"><a href="<?php	 	 echo home_url(); ?>"><h1><?php	 	 bloginfo( 'name' ); ?></h1></a></div>
			<p class="description"><?php	 	 bloginfo( 'description' ); ?></p>

			<ul class="header_blocks"> 
				<li class="twitter_links">
				<?php	 	 if( is_user_logged_in() && function_exists('bbp_user_profile_url')) { ?>
					<a id="facebook_like" href="<?php	 	 bbp_user_profile_url( bbp_get_current_user_id() ); ?>"><span>Your Account</span></a>
				<?php	 	 } else { ?>
			<!--
				now using a login  plugin so need this class (simplemodal-login) you can dl the plugin from here
				http://wordpress.org/extend/plugins/simplemodal-login/ -->
				
					<a id="facebook_like" href="/wp-login.php" class="simplemodal-login"><span>Login / Register</span></a>
				<?php	 	 } 
	//get the correct path the the plugin for download
						include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
						$plugin_info = plugins_api( 'plugin_information', array( 'slug' => stripslashes( 'wp-e-commerce' ) ) );
						$link = $plugin_info->download_link;
?>
					<a id="twitter" href="<?php	 	 echo $link ?>"><span>Download Plugin Now</span></a>
				</li>
			</ul>
			<nav>
				<?php	 	
					/* Top menu itself */
					wp_nav_menu(
						array(
							'theme_location' => 'main-menu',
							'container'      => '',
							'menu_class'     => '',
						)
					);
				?>
			</nav>
			<?php	 	 if( function_exists('wpsc_cart_item_count') ) { ?>
				<ul class="view-cart">
					<li>
						View Cart (<span class="cart-widget-count"><?php	 	 echo wpsc_cart_item_count(); ?></span>)
						<div class="shoppingcart">
						</div>
						<ul class="view-cart-pulldown">
							<li>
								<h2>Your Shopping Cart</h2>
								<?php	 	 echo wpsc_cart_item_count(); ?> Items
								<ul>
									<?php	 	 
									while ( wpsc_have_cart_items() ) : wpsc_the_cart_item();
									if ( wpsc_cart_item_count() > 0 ) : ?>
									<li><a href="<?php	 	 echo wpsc_cart_item_url(); ?>"><?php	 	 echo wpsc_cart_item_name(); ?></a></li>
									<?php	 	 endif; endwhile; ?>
								</ul>
								<?php	 	
								if ( wpsc_cart_item_count() > 0 ){
								$url = get_option('shopping_cart_url');
									echo '<a href="' . $url . '" class="learn-more">Checkout &raquo;</a>';
							
								}	?>
							</li>
						</ul>
					</li>
				</ul>
			<?php	 	 } ?>
			<?php	 	 if ( is_front_page() ) : ?>
			<!-- Featured -->
			<div class="featured">
				<div class="anythingFader">
					<div>
						<ul>
<?php	 	
$args = array(
	'post_type' => 'slider_gallery',
	'numberposts' => -1,
	'post_status' => null,
	'post_parent' => null, // any parent
);
$slider_gallery = get_posts($args);
if ( $slider_gallery ) {
	foreach ( $slider_gallery as $post ) {
		setup_postdata( $post );
		?>
	<li>
		<?php	 	 the_post_thumbnail( 'slider' ); ?>
		<div class="wp-e-commerce">
			<?php	 	 the_content(); 
				// Download
				if ( get_post_meta( $post->ID, 'download', true ) )
					echo '<div class="download-free"><a href="' . $link . '">Download <strong>Free</strong></a></div>';

				// Purchase
				if ( get_post_meta( $post->ID, 'purchase', true ) )
					echo '<div class="purchase-upgrades"><a href="' . get_post_meta( $post->ID, 'purchase', true ) . '">Purchase <strong>Upgrades</strong></a></div>';
			?>
		</div>
	</li>
	<?php	 	
	}
}
?>
						</ul>
					</div>
				</div>
			</div>
			<?php	 	 endif; ?>

		</div>
	</div>

</header>
