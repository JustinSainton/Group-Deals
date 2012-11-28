<?php get_header(); ?>
    <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
        <h2 class='fn control_title'>
            <?php
                if( function_exists( 'wpec_dd_price' ) )
                    printf( _x( '%1$s for %2$s', 'deal-title', 'wpec-group-deals' ), get_wpec_dd_price( get_the_ID(), 'deal' ), get_the_title() );
            ?>
        </h2>
        <div class='primary'>
                <?php
                    get_sidebar();
                ?>
        </div>
        <?php the_content(); ?>
    <?php endwhile; endif; ?>
<?php get_footer(); ?>
