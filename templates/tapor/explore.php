<a name="explore"></a>
<h3><?php esc_html_e( 'Explore', 'tapor-client' ) ?></h3>

<?php
$url          = remove_query_arg( array( 'tapor-category', 'tapor-search' ), bp_get_requested_url() );
$selected_cat = isset( $_GET['tapor-category'] ) ? $_GET['tapor-category'] : null;
?>

<form method="get" action="<?php echo esc_attr( $url ) ?>#tapor-results">
	<p><?php _e( 'Find tools from TAPoR:', 'tapor-client' ) ?></p>

	<p>
		<label for="tapor-category" class="explore-type-label"><?php _e( 'By category', 'tapor-client' ) ?></label>
		<?php $categories = tapor_categories() ?>
		<select name="tapor-category" id="tapor-category">
			<option value=""></option>
			<?php foreach ( $categories as $cat ) : ?>
				<option value="<?php echo intval( $cat['tid'] ) ?>" <?php selected( $cat['tid'], $selected_cat ); ?>><?php echo esc_html( $cat['name'] ) ?></option>
			<?php endforeach ?>
		</select>
		<input class="dirt-explore-button" type="submit" value="<?php _e( 'Go', 'dirt-directory-client' ) ?>" />
	</p>

	<p class="dirt-explore-or"><?php _e( 'or', 'dirt-directory-client' ) ?></p>

	<p>
		<label for="dirt-search" class="explore-type-label"><?php _e( 'By keyword', 'dirt-directory-client' ) ?></label>
		<input type="text" name="dirt-search" id="dirt-search" value="" />
		<input class="dirt-explore-button" type="submit" value="<?php _e( 'Go', 'dirt-directory-client' ) ?>" />
	</p>
</form>
