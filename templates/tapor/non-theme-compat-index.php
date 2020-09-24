<?php
	get_header(); ?>

	<div id="content" role="main" class="<?php do_action( 'content_class' ); ?>">
		<?php if ( tapor_is_tool_directory() ) : ?>
			<?php include( TAPOR_PLUGIN_DIR . 'templates/tapor/directory.php' ) ?>
		<?php elseif ( tapor_is_tool_page() ) : ?>
			<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
				<div <?php post_class(); ?> id="post-<?php the_ID(); ?>">
					<div class="post-content">
						<h1 class="post-title">
							<?php the_title(); ?>
							<?php edit_post_link(' ✍','',' ');?>
						</h1>

						<?php include( TAPOR_PLUGIN_DIR . 'templates/tapor/single.php' ) ?>
					</div>
				</div>
			<?php endwhile; endif; ?>
		<?php endif; ?>
	</div>
<?php
	get_sidebar();
	get_footer();
?>
