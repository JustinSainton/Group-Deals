<?php

/**
 * Widget that displays the purchase progress bar information
 *
 */

class WPEC_GD_ProgressBar extends WP_Widget {
    /** constructor */
    function WPEC_GD_ProgressBar() {
        parent::WP_Widget( false, $name = __( 'GD Progress Bar Box', 'wpec-group-deals' ), array( 'description' => __( 'Displays widget with the progress bar and amount currently purchased.', 'wpec-group-deals' ) ) );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        if( is_daily_deal() ) {

            extract($args);
            echo $before_widget;        
    ?>

        <div id='number_sold_container'>
            <table class='status'>
                <tr class="sum">
                    <td class="left">
                        <span class="number"><?php echo wpec_deals_purchased(); ?></span> <?php echo apply_filters( 'wpec_gd_widget_purchased', __( 'purchased', 'wpec-group-deals' ) ); ?>
                    </td>
                </tr>
            </table>
        <table class='status'>
            <tr class="limited">
                <td class="full" colspan="2">
                        <?php wpec_is_quantity_limited(); ?>
                </td>
            </tr>
        </table>
            <?php wpec_is_deal_on(); ?>
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

add_action( 'widgets_init', create_function( '', 'return register_widget( "WPEC_GD_ProgressBar" );' ) );
?>
