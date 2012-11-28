<?php

/**
 * Widget that shows Side Deals.
 *
 */

class SideDeals extends WP_Widget {
    /** constructor */
    function SideDeals() {
        parent::WP_Widget( false, $name = __( 'GD Side Deals', 'wpec-group-deals' ), array( 'description' => __( 'Displays a list of other currently running deals.  Hello &beta;eta!', 'wpec-group-deals' ) ) );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        extract( $args );
        echo $before_widget;
        ?>
        <div class='clearfix ' id='side_deals_widget'>

        <?php
        if( ! empty( $instance['wpec_sd_count'] ) )
        	$show_sd = $instance['wpec_sd_count']; 
        else
        	$show_sd = 5; 
        
        if( ! $instance['showf_sd'] )
        	$show_featured_sd = get_option( 'sticky_products' );
        else 
        	$show_featured_sd = "";

		if( ! $instance['wpec_sd_rand'] )
			$randomize_sd = "id";
		else 
			$randomize_sd = "rand";

        $args = array(
        	'post_type' => 'wpsc-product',
        	'showposts' => $show_sd,
        	'post__not_in' => $show_featured_sd,
        	'orderby' => '' . $randomize_sd . ''
        );
        
        $loop = new WP_Query( $args );
        while ( $loop->have_posts() ) : $loop->the_post();
        ?>
        
        	<div><a href="<?php the_permalink(); ?>">
        	<?php
        	if( has_post_thumbnail() ) :
        		the_post_thumbnail( 'wpec_dd_home_image' );
        		echo '<br />';
        	endif;

            if( function_exists( 'wpec_dd_price' ) )
                printf( _x( '%1$s for %2$s', 'deal-title', 'wpec-group-deals' ), get_wpec_dd_price( get_the_ID(), 'deal' ), get_the_title() );
            ?>
        	</a></div>
        <?php
            endwhile;
        ?>
   </div>
   <?php

       echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
	$instance = $old_instance;
	$instance['title'] = strip_tags( $new_instance['title'] );
	$instance['wpec_sd_count'] = strip_tags( $new_instance['wpec_sd_count'] );
	$instance['wpec_sd_rand'] = strip_tags( $new_instance['wpec_sd_rand'] );
	$instance['showf_sd'] = strip_tags( $new_instance['showf_sd'] );
        return $instance;
    }


    /** @see WP_Widget::form */
    function form($instance) {
        $title = esc_attr( $instance['title'] );
        $count_sd = esc_attr( $instance['wpec_sd_count'] );
        $rand_sd = esc_attr( $instance['wpec_sd_rand'] );
        $showf_sd = esc_attr( $instance['showf_sd'] );
        ?>
        <p>
          <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'wpec-group-deals' ); ?>:</label>
          <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <p>
          <label for="<?php echo $this->get_field_id( 'wpec_sd_count' ); ?>"><?php _e( 'Side deals to show', 'wpec-group-deals' ); ?>:</label>
          <input id="<?php echo $this->get_field_id( 'wpec_sd_count' ); ?>" name="<?php echo $this->get_field_name( 'wpec_sd_count' ); ?>" type="text" value="<?php if( ! empty( $count_sd ) ) : echo $count_sd; else : echo "5"; endif; ?>" value="5" size="3" />
        </p>
        <p>
          <input id="<?php echo $this->get_field_id( 'wpec_sd_rand' ); ?>" name="<?php echo $this->get_field_name( 'wpec_sd_rand' ); ?>" type="checkbox" <?php if( $rand_sd ) echo " checked"; ?> />
          <label for="<?php echo $this->get_field_id( 'wpec_sd_rand' ); ?>"><?php _e( 'Randomize Side Deals', 'wpec-group-deals' ); ?></label>
		</p>
		  <input id="<?php echo $this->get_field_id( 'showf_sd' ); ?>" name="<?php echo $this->get_field_name( 'showf_sd' ); ?>" type="checkbox" <?php if( $showf_sd ) echo " checked"; ?> />
		  <label for="<?php echo $this->get_field_id( 'showf_sd' ); ?>"><?php _e( 'Show Featured Deals', 'wpec-group-deals' ); ?></label>
		</p>
        <?php
    }
}

add_action( 'widgets_init', create_function( '', 'return register_widget( "SideDeals" );' ) );

?>