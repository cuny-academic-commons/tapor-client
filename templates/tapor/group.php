<h2><?php esc_html_e( 'Digital Research Tools', 'tapor-client' ) ?></h2>

<p><?php esc_html_e( 'TAPoR is a registry of digital research tools for scholarly use.', 'tapor-client' ) ?></p>

<p><?php printf( __( 'Explore more tools from TAPoR on the <a href="%s">Digital Tools Directory</a>.', 'tapor-client' ), get_post_type_archive_link( tapor_app()->schema::post_type() ) ) ?></p>

<?php /* Tools in use by the group */ ?>
<?php $group_tools = tapor_get_tools_used_by_group( bp_get_current_group_id() ); ?>
<?php if ( ! empty( $group_tools ) ) : ?>
	<h3><?php esc_html_e( 'This Group&#8217;s Tools', 'tapor-client' ) ?></h3>
	<p><?php printf( _n( 'Members of this group use %s tool from <a href="http://tapor.ca">TAPoR</a>:', 'Members of this group use %s tools from <a href="http://tapor.ca">TAPoR</a>:', count( $group_tools ), 'tapor-client' ), number_format_i18n( count( $group_tools ) ) ) ?></p>
	<ul class="tapor-tools tapor-tools-of-group">
	<?php foreach ( $group_tools as $group_tool ) : ?>
		<li><?php echo tapor_tool_markup( array(
			'link'        => $group_tool->get_link(),
			'title'       => $group_tool->get_title(),
			'tapor_id'    => $group_tool->get_tapor_id(),
			'description' => $group_tool->get_description(),
			'image'       => $group_tool->get_image(),
		) ) ?></li>
	<?php endforeach; ?>
	</ul>
<?php endif; ?>
