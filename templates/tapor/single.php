<div class="tapor-tool tapor-tools">
	<?php
	$tool_id  = get_the_ID();
	$tool_obj = \CAC\TAPoR\Tool::get_instance_by_id( $tool_id );
	?>

	<?php $tool_avatar = $tool_obj->get_image(); ?>
	<?php if ( $tool_avatar ) : ?>
		<div class="tapor-tool-avatar">
			<img src="<?php echo esc_attr( $tool_avatar ) ?>" />
		</div>
	<?php endif; ?>

	<div class="tapor-tool-data">
		<p class="tapor-tool-link">
			<?php printf(
				esc_html__( 'On TAPoR: %s', 'tapor-client' ),
				sprintf( '<a href="%1$s">%1$s</a>', esc_attr( $tool_obj->get_link() ) )
			); ?>
		</p>

		<?php the_content() ?>
	</div>

	<div class="tapor-tool-all-users">
		<h3><?php echo esc_html( sprintf( __( 'Users on %s', 'tapor-client' ), get_option( 'blogname' ) ) ); ?></h3>

		<?php if ( is_user_logged_in() ) : ?>
			<?php echo tapor_get_action_checkbox( $tool_id ); ?>
		<?php endif ?>

		<?php $tool_users = $tool_obj->get_users_of_tool( [ 'count' => false ] ); ?>

		<ul class="tapor-tool-all-users-list">
		<?php foreach ( $tool_users as $tool_user_id ) : ?>
			<li>
				<?php printf(
					'%s <a href="%s">%s</a>',
					bp_core_fetch_avatar( array( 'item_id' => $tool_user_id, 'width' => 25, 'height' => 25, ) ),
					esc_attr( trailingslashit( bp_core_get_user_domain( $tool_user_id ) ) . trailingslashit( tapor_get_slug() ) ),
					esc_html( bp_core_get_user_displayname( $tool_user_id ) )
				); ?>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>

	<hr /><br />
	<p><?php printf( __( 'Explore more tools from TAPoR on the <a href="%s">Digital Tools Directory</a>.', 'tapor-client' ), esc_attr( get_post_type_archive_link( tapor_app()->schema::post_type() ) ) ); ?></p>

</div>
