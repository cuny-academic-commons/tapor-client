<p><?php esc_html_e( 'The TAPoR Directory is a registry of digital research tools for scholarly use.', 'tapor' ) ?></p>

<?php bp_get_template_part( 'tapor/explore' ); ?>

<hr />

<h3><?php esc_html_e( 'On this site', 'tapor-client' ) ?></h3>

<?php
$search_terms = isset( $_GET['tapor-search'] ) ? urldecode( $_GET['tapor-search'] ) : '';
$cat_id       = isset( $_GET['tapor-category'] ) ? intval( $_GET['tapor-category'] ) : '';

if ( isset( $_GET['orderby'] ) && 'newest' === $_GET['orderby'] ) {
	$tool_orderby = 'date';
	$tool_order   = 'DESC';
} else {
	$tool_orderby = 'name';
	$tool_order   = 'ASC';
}

$cat_name = '';
if ( $cat_id ) {
	$cats = tapor_categories();
	$cat_name = '';
	foreach ( $cats as $cat ) {
		if ( $cat['tid'] == $cat_id ) {
			$cat_name = $cat['name'];
			break;
		}
	}
}

$used_tool_args = array(
	'search_terms'   => $search_terms,
	'orderby'        => $tool_orderby,
	'order'          => $tool_order,
	'posts_per_page' => -1,
);

if ( $cat_name ) {
	$used_tool_args['categories'] = $cat_name;
}

$used_tools = tapor_get_tools( $used_tool_args );

?>

<p><?php printf( esc_html__( 'The following tools are in use by members on %s.', 'tapor-client' ), esc_html( get_option( 'blogname' ) ) ) ?>

<?php if ( ! empty( $used_tools ) ) : ?>
	<a name="local-results"> </a>
	<ul class="tapor-tools">
	<?php foreach ( $used_tools as $used_tool ) : ?>
		<li><?php echo tapor_tool_markup( array(
			'link'        => $used_tool->get_link(),
			'title'       => $used_tool->get_title(),
			'tapor_id'    => $used_tool->get_tapor_id(),
			'description' => $used_tool->get_description(),
			'image'       => $used_tool->get_image(),
		) ) ?></li>
	<?php endforeach; ?>
	</ul>
<?php else : ?>
	<p><?php esc_html_e( 'No tools found.', 'tapor-client' ) ?></p>
<?php endif ?>

<?php if ( $search_terms || $cat_id ) : ?>
	<a name="tapor-results"> </a>
	<h3><?php esc_html_e( 'On TAPoR', 'tapor-client' ) ?></h3>
	<?php
	if ( $search_terms ) {
		$args = array(
			'search_terms' => $search_terms,
			'type'         => 'search',
		);

		$results_string = sprintf( __( 'We found these tools that match your query: %s', 'tapor-client' ), '<span class="tapor-search-terms">' . esc_html( $search_terms ) . '</span>' );
	} else if ( $cat_id ) {
		$args = array(
			'cat_id' => $cat_id,
			'type'   => 'category',
		);

		$results_string = sprintf( __( 'We found these tools in the category: %s', 'tapor-client' ), '<span class="tapor-search-terms">' . esc_html( $cat_name ) . '</span>' );
	}

	$search_results = tapor_query_tools( $args );

	?>

	<?php if ( ! empty( $search_results ) ) : ?>
		<p><?php echo $results_string ?></p>

		<ol class="tapor-tools">
		<?php foreach ( $search_results as $search_result ) : ?>
			<li><?php echo tapor_tool_markup( $search_result ) ?></li>
		<?php endforeach; ?>
		</ol>
	<?php else : ?>
		<p><?php printf( __( 'We couldn&#8217;t find any tools that matched the following query: %s', 'tapor-client' ), '<span class="tapor-search-terms">' . esc_html( $search_terms ) . '</span>') ?></p>
	<?php endif; ?>
<?php endif ?>
