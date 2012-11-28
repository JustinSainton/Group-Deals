<?php

/**
 * Widget that displays the Time Left to Buy countdown.
 *
 */

class TimeLeftToBuy extends WP_Widget {
    /** constructor */
    function TimeLeftToBuy() {
        parent::WP_Widget( false, $name = __( 'GD Time Left to Buy Box', 'wpec-group-deals' ), array( 'description' => __( 'Displays AJAX countdown for active deal.  Should be compatible with JS disabled.', 'wpec-group-deals' ) ) );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
    
    $meta = get_post_meta( get_the_ID(), '_wpec_dd_details', true );
    
   $meta = isset( $meta['stopn'] ) ? $meta['stopn'] : '';
        
    if( is_daily_deal() &&  $meta == 0 ) {
        extract($args);
        echo $before_widget;
        ?>
        <div class='clearfix hourglass hourglass008' id='remaining_time_container'>
             <ul id='counter'>

                  <li id='countdown'><h5><?php _e( 'Time Left to buy', 'wpec-group-deals' ); ?></h5></li>
                  <li id="ajax"><?php wpec_ajax_countdown( get_the_ID(), 'noscript' ); ?><li>
              
            </ul>
        </div>
<?php
            echo $after_widget;
        }
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
	$instance = $old_instance;
	$instance['title'] = strip_tags( $new_instance['title'] );
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $title = esc_attr( $instance['title'] );
        ?>
         <p>
          <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'wpec-group-deals' ); ?>:</label>
          <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php
    }

}

add_action( 'widgets_init', create_function( '', 'return register_widget( "TimeLeftToBuy" );' ) );
?>
