              </div>
            </div>
            <?php if( ! empty( $post->post_excerpt ) ) : ?>
            <div class="module">
                <div class="module_content" style="padding:10px">
                    <?php echo apply_filters( 'the_excerpt', wpautop( $post->post_excerpt ) ); ?>
                </div>
            </div>
            <?php endif; ?>
          </div>
         </div>
<?php get_sidebar( 'footer' ); ?>
        </div>
</div>
<?php wp_footer(); ?>
</body>
</html>