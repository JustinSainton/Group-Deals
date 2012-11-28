<?php

/**
 * Widget that displays the Buy Now button and the price stats.
 *
 */

class BuyNowPriceBox extends WP_Widget {
    /** constructor */
    function BuyNowPriceBox() {
        $widget_ops = array( 'classname' => 'widget_wpec_group_deals_buy_now','description' => __( 'Group Deals Buy Now & Price Box.  Will only show on home page.', 'wpec-group-deals' ) );
        $this->WP_Widget( 'wpec_group_deals_', __( 'GD Buy Now & Price Box', 'wpec-group-deals' ), $widget_ops );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        if( is_daily_deal() ) {
            extract($args);
            echo $before_widget;        
        ?>

            <div id='price_tag'>
                <div id='price_tag_inner'>
                  <div id="amount"><?php wpec_dd_price( get_the_ID(), 'deal' ); ?></div>
                    <form class="product_form_dd" enctype="multipart/form-data" action="<?php echo get_option('shopping_cart_url'); ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_<?php echo wpsc_the_product_id(); ?>">
                        <input type="hidden" value="add_to_cart" name="wpsc_ajax_action" />
                        <input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id" />
                        <a href="#" id="buy_btn"><?php echo apply_filters( 'wpec_gd_buy_button', __( 'Buy', 'wpec-group-deals' ) ); ?></a>
                    </form><!--close product_form-->

                </div>
            </div>
                  <div class='clearfix' id='deal_discount' style="border-bottom:1px solid #64C8E2;">
                    <dl>
                      <dt><?php echo apply_filters( 'wpec_gd_value', __( 'Value', 'wpec-group-deals' ) ); ?></dt>
                      <dd><?php wpec_dd_price( get_the_ID(), 'retail' ); ?></dd>
                    </dl>
                    <dl class='discount'>
                      <dt><?php echo apply_filters( 'wpec_gd_discount', __( 'Discount', 'wpec-group-deals' ) ); ?></dt>
                      <dd><?php wpec_dd_price( get_the_ID(), 'discount' ); ?></dd>
                    </dl>
                    <dl class='save'>
                      <dt><?php echo apply_filters( 'wpec_gd_you_save', __( 'You Save', 'wpec-group-deals' ) ); ?></dt>
                      <dd><?php wpec_dd_price( get_the_ID(), 'savings' ); ?></dd>
                    </dl>
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

add_action( 'widgets_init', create_function( '', 'return register_widget( "BuyNowPriceBox" );' ) );

?>
