<a name="explore"></a>
<h3><?php esc_html_e( 'Explore', 'tapor-client' ) ?></h3>

<?php
$url = remove_query_arg( array( 'tapor-category', 'tapor-search' ), bp_get_requested_url() );

$current_cat    = isset( $_GET['tapor-category'] ) ? $_GET['tapor-category'] : null;
$current_search = isset( $_GET['tapor-search'] ) ? $_GET['tapor-search'] : '';
?>

<form method="get" action="<?php echo esc_attr( $url ) ?>#tapor-results">
	<p><?php esc_html_e( 'Find tools from TAPoR:', 'tapor-client' ) ?></p>

	<p>
		<label for="tapor-category" class="explore-type-label"><?php esc_html_e( 'By category', 'tapor-client' ) ?></label>
		<?php $categories = tapor_categories() ?>
		<select name="tapor-category" id="tapor-category">
			<option value=""></option>
			<?php foreach ( $categories as $cat ) : ?>
				<option value="<?php echo intval( $cat['tid'] ) ?>" <?php selected( $cat['tid'], $current_cat ); ?>><?php echo esc_html( $cat['name'] ) ?></option>
			<?php endforeach ?>
		</select>
		<input class="tapor-explore-button" type="submit" value="<?php esc_attr_e( 'Go', 'tapor-client' ) ?>" />
	</p>

	<p class="tapor-explore-or"><?php esc_html_e( 'or', 'tapor-client' ) ?></p>

	<p>
		<label for="tapor-search" class="explore-type-label"><?php esc_html_e( 'By keyword', 'tapor-client' ) ?></label>
		<input type="text" name="tapor-search" id="tapor-search" value="<?php echo esc_attr( $current_search ); ?>" />
		<input class="tapor-explore-button" type="submit" value="<?php esc_attr_e( 'Go', 'tapor-client' ) ?>" />
	</p>
</form>
