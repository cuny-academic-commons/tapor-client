<h2><?php esc_html_e( 'Digital Research Tools', 'tapor-client' ) ?></h2>

<p><?php esc_html_e( 'TAPoR is a registry of digital research tools for scholarly use.', 'tapor-client' ) ?></p>

<p><?php printf( __( 'Explore more tools from TAPoR on the <a href="%s">Digital Tools Directory</a>.', 'tapor-client' ), esc_attr( get_post_type_archive_link( tapor_app()->schema::post_type() ) ) ) ?></p>

<?php /* Tools in use by the member */ ?>
<?php $member_tools = tapor_get_tools_of_user( bp_displayed_user_id() ); ?>
<?php if ( ! empty( $member_tools ) ) : ?>
	<?php if ( bp_is_my_profile() ) : ?>
		<h3><?php esc_html_e( 'My Tools', 'tapor-client' ) ?></h3>
	<?php else : ?>
		<h3><?php printf( esc_html__( '%s&#8217;s Tools', 'tapor-client' ), bp_core_get_user_displayname( bp_displayed_user_id() ) ) ?></h3>
	<?php endif; ?>

	<p><?php printf( _n( '%s uses %s tool from <a href="http://tapor.ca">TAPoR</a>:', '%s uses %s tools from <a href="http://tapor.ca">TAPoR</a>:', count( $member_tools ), 'tapor-client' ), bp_core_get_user_displayname( bp_displayed_user_id() ), number_format_i18n( count( $member_tools ) ) ) ?></p>
	<ul class="tapor-tools tapor-tools-of-group">
	<?php foreach ( $member_tools as $member_tool ) : ?>
		<li><?php echo tapor_tool_markup( array(
			'link'        => $member_tool->get_link(),
			'title'       => $member_tool->get_title(),
			'tapor_id'    => $member_tool->get_tapor_id(),
			'description' => $member_tool->get_description(),
			'image'       => $member_tool->get_image(),
		) ) ?></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>



