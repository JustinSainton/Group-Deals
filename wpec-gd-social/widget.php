<?php
/*
 * Gratefully adapted from Valentinas Bakaitas' plugin
 */
class FB_Connect_Widget extends WP_Widget {
    /** constructor */
    function FB_Connect_Widget() {
        parent::WP_Widget( false, $name = __( 'GD FB Connect', 'wpec-group-deals' ), array( 'description' => __( 'GD FB Connect Widget. Creates Facebook Login/Logout button. Smart enough to create accounts for new users, and associate accounts for current users.', 'wpec-group-deals' ) ) );
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {

        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );
        $size = $instance['size'] ? $instance['size'] : 'medium';
        $login_text = $instance['login_text'] ? $instance['login_text'] : 'Login';
        $logout_text = $instance['logout_text'] ? $instance['logout_text'] : 'Logout';
        $connect_text = $instance['connect_text'] ? $instance['connect_text'] : 'Connect';

        echo $before_widget;
        if ( $title )
            echo $before_title . $title . $after_title;
        ?>
        <div>
            <?php fb_login( $size, $login_text, $logout_text, $connect_text ); ?>
        </div>
        <?php

        echo $after_widget;
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
	$instance = $old_instance;
	$instance['title'] = strip_tags( $new_instance['title'] );
	$instance['size'] = strip_tags( $new_instance['size'] );
	$instance['login_text'] = strip_tags( $new_instance['login_text'] );
	$instance['logout_text'] = strip_tags( $new_instance['logout_text'] );
	$instance['connect_text'] = strip_tags( $new_instance['connect_text'] );
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $title = esc_attr( $instance['title'] );
        $size = esc_attr( $instance['size'] );
        $login_text = esc_attr( $instance['login_text'] );
        $logout_text = esc_attr( $instance['logout_text'] );
        $connect_text = esc_attr( $instance['connect_text'] );
        ?>
            <p>
            	<label for="<?php echo $this->get_field_id( 'title' ); ?>">
            		<?php _e( 'Widget title:', 'wpec-group-deals' ); ?>
            		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id( 'login_text' ); ?>">
            		<?php _e( 'Login button text:', 'wpec-group-deals' ); ?>
            		<input class="widefat" id="<?php echo $this->get_field_id( 'login_text' ); ?>" name="<?php echo $this->get_field_name( 'login_text' ); ?>" type="text" value="<?php echo $login_text; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id( 'logout_text' ); ?>">
            		<?php _e( 'Logout button text:', 'wpec-group-deals' ); ?>
            		<input class="widefat" id="<?php echo $this->get_field_id( 'logout_text' ); ?>" name="<?php echo $this->get_field_name( 'logout_text' ); ?>" type="text" value="<?php echo $logout_text; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id( 'connect_text' ); ?>">
            		<?php _e( 'Connect button text:', 'wpec-group-deals' ); ?>
            		<input class="widefat" id="<?php echo $this->get_field_id( 'connect_text' ); ?>" name="<?php echo $this->get_field_name( 'connect_text' ); ?>" type="text" value="<?php echo $connect_text; ?>" />
            	</label>
            </p>
            <p>
            	<label for="<?php echo $this->get_field_id( 'size' ); ?>">
            		<?php _e( 'Size:', 'wpec-group-deals' ); ?>
            		<select id="<?php echo $this->get_field_id( 'size' ); ?>" name="<?php echo $this->get_field_name( 'size' ); ?>" >
         				<?php $options = array( 'small', 'medium', 'large', 'xlarge' ); ?>
         				<?php foreach( (array)$options as $option ){ ?>
         				<?php
         				if( $option != $size )
         					$selected='';
         				else
         					$selected = 'selected="selected"';
         				  ?>
         				<option value="<?php echo $option; ?>" <?php echo $selected; ?> ><?php echo $option; ?></option>
         				<?php } ?>
         		   	</select>
         		</label>
            </p>
        <?php 
    }
}
/*
 * Gratefully adapted from AJ Batac's plugun
 */
class FB_Like_Widget extends WP_Widget {
    /** constructor */
    function FB_Like_Widget() {
        parent::WP_Widget( false, $name = __( 'GD FB Like Button', 'wpec-group-deals' ), array( 'description' => __( 'Creates Facebook Like Button on Group Deal page.  You can manually integrate with the wpec_gd_fb_like() function.', 'wpec-group-deals' ) ) );
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        if( is_daily_deal() ) {
            extract( $args );
            echo wpec_gd_fb_like( $instance );
        }
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
	$instance = $old_instance;
	$instance['wp_fb_like_layout'] = strip_tags( $new_instance['wp_fb_like_layout'] );
	$instance['wp_fb_like_show_faces'] = strip_tags( $new_instance['wp_fb_like_show_faces'] );
	$instance['wp_fb_like_action'] = strip_tags( $new_instance['wp_fb_like_action'] );
	$instance['wpec_tweet_this'] = strip_tags( $new_instance['wpec_tweet_this'] );
	$instance['wpec_fb_box_height'] = strip_tags( $new_instance['wpec_fb_box_height'] );
	$instance['wpec_fb_box_width'] = strip_tags( $new_instance['wpec_fb_box_width'] );
        return $instance;
    }

    /** @see WP_Widget::form */
    function form( $instance ) {
        
        $layout = esc_attr( $instance['wp_fb_like_layout'] );
        $layout_options = array(
            __( 'Standard', 'wpec-group-deals' ),
            __( 'Button Count', 'wpec-group-deals' ),
            __( 'Box Count', 'wpec-group-deals' )
        );
        $faces = esc_attr( $instance['wp_fb_like_show_faces'] );
        $faces_options = array(
            __( 'Yes', 'wpec-group-deals' ),
            __( 'No', 'wpec-group-deals' )
        );
        $verb = esc_attr( $instance['wp_fb_like_action'] );
        $verb_options = array(
            __( 'Like', 'wpec-group-deals' ),
            __( 'Recommend', 'wpec-group-deals' )
        );
        $tweet = esc_attr( $instance['wpec_tweet_this'] );
        $tweet_options = array(
            __( 'Yes', 'wpec-group-deals' ),
            __( 'No', 'wpec-group-deals' )
        );

        $box_width = esc_attr( $instance['wpec_fb_box_width'] );
        $box_height = esc_attr( $instance['wpec_fb_box_height'] );

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'wp_fb_like_layout' ); ?>">
                <?php _e( 'Facebook "Like" Layout:', 'wpec-group-deals' ); ?>
                <select name="<?php echo $this->get_field_name( 'wp_fb_like_layout' ); ?>" id="<?php echo $this->get_field_id( 'wp_fb_like_layout' ); ?>">
                    <?php foreach( (array)$layout_options as $option ){ ?>
                        <option value="<?php echo $option; ?>" <?php selected( $option, $layout ); ?>><?php echo $option; ?></option>
                    <?php } ?>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'wp_fb_like_show_faces' ); ?>">
                <?php _e( 'Show Faces?:', 'wpec-group-deals' ); ?>
                <select name="<?php echo $this->get_field_name( 'wp_fb_like_show_faces' ); ?>" id="<?php echo $this->get_field_id( 'wp_fb_like_show_faces' ); ?>">
                    <?php foreach( (array)$faces_options as $option ){ ?>
                        <option value="<?php echo $option; ?>" <?php selected( $option, $faces ); ?>><?php echo $option; ?></option>
                    <?php } ?>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'wp_fb_like_action' ); ?>">
                <?php _e( 'Verb to use:', 'wpec-group-deals' ); ?>
                <select name="<?php echo $this->get_field_name( 'wp_fb_like_action' ); ?>" id="<?php echo $this->get_field_id( 'wp_fb_like_action' ); ?>">
                    <?php foreach( (array)$verb_options as $option ){ ?>
                        <option value="<?php echo $option; ?>" <?php selected( $option, $verb ); ?>><?php echo $option; ?></option>
                    <?php } ?>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'wpec_tweet_this' ); ?>">
                <?php _e( 'Show Twitter Share Link?:', 'wpec-group-deals' ); ?>
                <select name="<?php echo $this->get_field_name( 'wpec_tweet_this' ); ?>" id="<?php echo $this->get_field_id( 'wpec_tweet_this' ); ?>">
                    <?php foreach( (array)$tweet_options as $option ){ ?>
                        <option value="<?php echo $option; ?>" <?php selected( $option, $tweet ); ?>><?php echo $option; ?></option>
                    <?php } ?>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'wpec_fb_box_width' ); ?>">
                <?php _e( 'Box Width?:', 'wpec-group-deals' ); ?>
                <input name="<?php echo $this->get_field_name( 'wpec_fb_box_width' ); ?>" value="<?php echo $box_width; ?>" type="text" id="<?php echo $this->get_field_id( 'wpec_fb_box_width' ); ?>" />
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'wpec_fb_box_height' ); ?>">
                <?php _e( 'Box Height?:', 'wpec-group-deals' ); ?>
                <input name="<?php echo $this->get_field_name( 'wpec_fb_box_height' ); ?>" value="<?php echo $box_height; ?>" type="text" id="<?php echo $this->get_field_id( 'wpec_fb_box_height' ); ?>" />
            </label>
        </p>
        <?php
    }
}

?>