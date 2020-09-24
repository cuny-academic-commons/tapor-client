<div class="tapor-tool tapor-tools">
	<?php $tool_id = get_the_ID() ?>

	<?php $tool_avatar = tapor_get_tool_avatar_url( $tool_id ); ?>
	<?php if ( $tool_avatar ) : ?>
		<div class="tapor-tool-avatar">
			<img src="<?php echo esc_url( $tool_avatar ) ?>" />
		</div>
	<?php endif; ?>

	<div class="tapor-tool-data">
		<p class="tapor-tool-link">
			<?php printf(
				__( 'On TAPoR: %s', 'tapor-client' ),
				sprintf( '<a href="%1$s">%1$s</a>', get_post_meta( get_the_ID(), 'dirt_link', true ) )
			); ?>
		</p>

		<?php the_content() ?>
	</div>

	<div class="tapor-tool-all-users">
		<h3><?php printf( __( 'Users on %s', 'tapor-client' ), get_option( 'blogname' ) ) ?></h3>

		<?php if ( is_user_logged_in() ) : ?>
			<?php echo tapor_get_action_checkbox( $tool_id ); ?>
		<?php endif ?>

		<?php $tool_users = tapor_get_users_of_tool( get_the_ID(), array( 'count' => false, ) ); ?>

		<ul class="tapor-tool-all-users-list">
		<?php foreach ( $tool_users as $tool_user_id ) : ?>
			<li>
				<?php printf(
					'%s <a href="%s">%s</a>',
					bp_core_fetch_avatar( array( 'item_id' => $tool_user_id, 'width' => 25, 'height' => 25, ) ),
					trailingslashit( bp_core_get_user_domain( $tool_user_id ) ) . trailingslashit( tapor_get_slug() ),
					bp_core_get_user_displayname( $tool_user_id )
				); ?>
			</li>
		<?php endforeach; ?>
		</ul>
	</div>

	<hr /><br />
	<p><?php printf( __( 'Explore more tools from TAPoR on the <a href="%s">Digital Tools Directory</a>.', 'tapor-client' ), tapor_get_tool_directory_url() ) ?></p>

</div>
