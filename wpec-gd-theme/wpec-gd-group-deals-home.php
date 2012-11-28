<div class='secondary' style="float:left; margin-right: 18px">
    <div class='photos'>
        <ul>
            <li><?php if( has_post_thumbnail() ) the_post_thumbnail( 'wpec_dd_home_image' ); ?></li>
        </ul>
    </div>
    <div class='fine_print'>
        <h3><?php _e( 'The Fine Print', 'wpec-group-deals' ); ?></h3>
        <p><?php wpec_dd_theme_fine_print(); ?></p>
    </div>
    <div class='highlights'>
        <h3><?php _e( 'Highlights', 'wpec-group-deals' ); ?></h3>
            <ul>
                <?php wpec_dd_theme_highlights(); ?>
            </ul>
    </div>
    <br style="clear:both" />
    <h3><?php _e( 'Description', 'wpec-group-deals' ); ?></h3>
    <p><?php echo get_the_content(); ?></p>
</div>
<div class="tertiary" style="float:left; width:225px">
    <div class='write_up_rail'>
        <div class='company vcard'>
            <h3><?php _e( 'The Company', 'wpec-group-deals' ); ?></h3>
            <?php wpec_dd_company_info(); ?>
        </div>
    </div>
</div>
             