<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
    <title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	global $page, $paged;

	wp_title( '|', true, 'right' );

	// Add the blog name.
	bloginfo( 'name' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		echo " | $site_description";

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		echo ' | ' . sprintf( __( 'Page %s', 'twentyten' ), max( $paged, $page ) );

	?></title>
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
     <?php 
     if ( is_singular() && get_option( 'thread_comments' ) )
		wp_enqueue_script( 'comment-reply' );
     wp_head();
     ?>
   
  </head>
<body <?php body_class(); ?>>
      <div id="drawer" style="display:none;">
      <div id="drawer_inner">
         <div id="follow_drawer" style="display: block; overflow:hidden ">
             <div id="follow_drawer_inner">
            <div class="follow_container clearfix" style="width:720px">
              <div class="follow_desc">
               <?php _e( 'Get the Daily Deal for', 'wpec-group-deals' ); ?>:
              </div>
            <?php if( function_exists( 'wpec_dd_ajax_registration_form' ) )
                    wpec_dd_ajax_registration_form();
            ?>
                <span class="close"></span>
            </div>
          </div></div>
      </div>
    </div>
    <div id='header'>
      <div id='header_inner'>
        <h1 id='logo'>
          <a href="<?php echo esc_url( home_url() ); ?>" class="G_event E-Logo_click_groupdealsLogo" title="Group Deals: Collective Buying Power">Group Deals: Collective Buying Power</a>
        </h1>
        <div class='header_content layer2'>
          <div class='drawer_links clearfix'>
            <ul class='toggles'>
              <li class='drawer_toggle' id='follow_drawer_anchor'><a href="#" class="reg_toggle"><?php _e( 'Get The Daily Email', 'wpec-group-deals' ); ?></a></li>
            </ul>
          </div>
          <div id='header_content_inner'>
            <p class='tagline'>Daily Deals on the Best in</p>
            <h2 class='division_name'><a href="/portland/">Portland</a></h2>
          </div>
        </div>
      </div>
    </div>
    <div id='headernew'>
      <div id='header_inner'>
        <h1 id='logo'>
          <a href="<?php echo esc_url( home_url() ); ?>" class="G_event E-LogoNH_click_groupdealsLogo" title="Group Deals: Collective Buying Power">Group Deals: Collective Buying Power</a>
        </h1>
        <div class='layer2 clearfix'>
          <ul id='header_nav'>
           <?php wp_nav_menu( array( 'container_class' => 'menu-header', 'theme_location' => 'primary' ) ); ?>
          </ul>
          <ul class='account_links'>
            <?php if ( ! is_user_logged_in() ) : ?>
                <li class='sign_in'><a href="<?php echo home_url( 'your-account' ); ?>"><?php _e( 'Sign In', 'wpec-group-deals' ); ?></a></li>
                <li><a href="<?php echo home_url( 'your-account' ); ?>" class="reg_toggle"><?php _e( 'Sign Up', 'wpec-group-deals' ); ?></a></li>
            <?php else : ?>
                <li class='sign_out'><a href="<?php echo home_url( 'your-account' ); ?>" title="My Account"><?php _e( 'My Account', 'wpec-group-deals' ); ?></a></li>
              <li class='sign_out'><a href="<?php echo wp_logout_url( get_permalink() ); ?>" title="Logout"><?php _e( 'Log Out', 'wpec-group-deals' ); ?></a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
    <div id='nav'>
      <div class='clearfix' id='nav_inner'>
        <ul class='nav left'>
          <li class='todays_deal_btn'>
            <a href="/portland/" class="G_event E-GobalTopNav_TodaysDeal_PortlandDealOfTheDayLocalDailyDealsFromGroup Deals todays_deal G_event E-GobalTopNav_TodaysDeal current">Today&rsquo;s Deal</a>
          </li>
          <li><a href="/portland/deals" class="G_event E-GobalTopNav_RecentDeals_PortlandDealOfTheDayLocalDailyDealsFromGroup Deals">Recent Deals</a></li>
          <li><a href="/learn" class="G_event E-GobalTopNav_HowWorks_PortlandDealOfTheDayLocalDailyDealsFromGroup Deals">How Group Deals Works</a></li>

        </ul>
        <div class='right' id='user_nav'>
          <ul class='clearfix login'>
            <li class='fb'><fb:login-button onlogin="Group Deals.FacebookConnect.onConnect()" perms="publish_stream,email,user_birthday,offline_access,user_checkins,friends_checkins">Connect</fb:login-button></li>
            <li class='sign_in'><a href="https://www.groupon.com/login">Sign in</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class='burst_with_rays' id='container'>
        <div class='clearfix' id='content_container'>
          <div class='clearfix' id='cities' style="height:0px;">
          <div class='clearfix' id='cities_inner'>
           <div class='drawer_toggle' id='follow_drawer_anchor'>
              <a href="/subscriptions/new" class="reg_toggle" style="position:relative; left:840px"><?php _e( 'Get Deals By Email', 'wpec-group-deals' ); ?></a>

            </div>
          </div>
        </div>
 <div class='clearfix' id='main'>
          <div id='content' style="width:960px">

            <div class='deals hproduct  '>
               <div class='deal clearfix'>