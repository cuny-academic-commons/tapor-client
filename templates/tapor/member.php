<h2><?php _e( 'Digital Research Tools', 'tapor-client' ) ?></h2>

<p><?php _e( 'TAPoR is a registry of digital research tools for scholarly use.', 'tapor-client' ) ?></p>

<p><?php printf( __( 'Explore more tools from TAPoR on the <a href="%s">Digital Tools Directory</a>.', 'tapor-client' ), tapor_get_tool_directory_url() ) ?></p>

<?php /* Tools in use by the member */ ?>
<?php $member_tools = tapor_get_tools_of_user( bp_displayed_user_id() ); ?>
<?php if ( ! empty( $member_tools ) ) : ?>
	<?php if ( bp_is_my_profile() ) : ?>
		<h3><?php _e( 'My Tools', 'tapor-client' ) ?></h3>
	<?php else : ?>
		<h3><?php printf( __( '%s&#8217;s Tools', 'tapor-client' ), bp_core_get_user_displayname( bp_displayed_user_id() ) ) ?></h3>
	<?php endif; ?>

	<p><?php printf( _n( '%s uses %s tool from <a href="http://dirtdirectory.org">TAPoR</a>:', '%s uses %s tools from the <a href="http://dirtdirectory.org">TAPoR</a>:', count( $member_tools ), 'tapor-client' ), bp_core_get_user_displayname( bp_displayed_user_id() ), number_format_i18n( count( $member_tools ) ) ) ?></p>
	<ul class="dirt-tools dirt-tools-of-group">
	<?php foreach ( $member_tools as $member_tool ) : ?>
		<li><?php echo tapor_tool_markup( array(
			'link' => $member_tool->dirt_link,
			'title' => $member_tool->post_title,
			'node_id' => $member_tool->dirt_node_id,
			'description' => $member_tool->post_content,
			'thumbnail' => $member_tool->dirt_thumbnail,
			'image' => $member_tool->dirt_image,
		) ) ?></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>



