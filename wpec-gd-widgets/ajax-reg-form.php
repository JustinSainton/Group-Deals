<?php

/**
 * Widget that displays the purchase progress bar information
 *
 */

class WPEC_AJAX_RegForm extends WP_Widget {
    /** constructor */
    function WPEC_AJAX_RegForm() {
        parent::WP_Widget( false, $name = __( 'GD Ajax Registration Form', 'wpec-group-deals' ), array( 'classname' => 'widget_reg_form', 'description' => __( 'Creates AJAX Registration form for new users to sign up for group deals.', 'wpec-group-deals' ) ) );
    }

    /** @see WP_Widget::widget */
    function widget( $args, $instance ) {
        if( is_daily_deal() ) {
        extract($args);
        echo $before_widget;
           wpec_dd_ajax_registration_form();
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
          <input class="widefat" id="<?php echo $this->get_field_id ( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php
    }

}

add_action( 'widgets_init', create_function( '', 'return register_widget( "WPEC_AJAX_RegForm" );' ) );
?>
