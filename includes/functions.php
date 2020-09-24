<?php

/**
 * Add non-persistent caching group.
 *
 * @since 1.2.0
 */
function tapor_add_non_persistent_caching_group() {
	wp_cache_add_non_persistent_groups( array(
		'tapor_bp_group_members',
		'tapor_tools',
	) );
}

/**
 * Register CSS and JS assets.
 *
 * @since 1.0.0
 */
function tapor_register_assets() {
	wp_register_style( 'tapor-client', TAPOR_PLUGIN_URL . 'assets/css/screen.css' );
	wp_register_script( 'tapor-client', TAPOR_PLUGIN_URL . 'assets/js/ddc.js', array( 'jquery' ) );

	wp_localize_script( 'tapor-client', 'DDC', array(
		'add_gloss'    => __( 'Click to show that you use this tool', 'tapor-client' ),
		'remove_gloss' => __( 'Click to remove this tool from your list', 'tapor-client' ),
	) );
}
add_action( 'init', 'tapor_register_assets', 0 );

/**
 * Enqueue assets on CPT pages.
 *
 * BP pages are handled by BP integration pieces.
 *
 * @since 1.0.0
 */
function tapor_enqueue_assets() {
	if ( tapor_is_tool_directory() || tapor_is_tool_page() ) {
		wp_enqueue_style( 'tapor-client' );
		wp_enqueue_script( 'tapor-client' );
	}
}
add_action( 'wp_enqueue_scripts', 'tapor_enqueue_assets' );


/**
 * Is this the Tool directory?
 *
 * @since 1.0.0
 *
 * @return bool
 */
function tapor_is_tool_directory() {
	return is_post_type_archive( 'tapor_tool' );
}

/**
 * Is this a single Tool page?
 *
 * @since 1.0.0
 *
 * @return bool
 */
function tapor_is_tool_page() {
	$is_tool_page = false;

	if ( is_single() ) {
		$o = get_queried_object();
		$is_tool_page = ( $o instanceof WP_Post ) && 'tapor_tool' === $o->post_type;
	}

	return $is_tool_page;
}

/**
 * Get an array of TaDiRAH category data.
 *
 * Data includes local taxonomy term ID and taxonomy 'name'. Data is cached locally for performance.
 *
 * @since 1.0.0
 *
 * @return array
 */
function tapor_categories() {
	// @todo Better cache busting.
	$cats = get_transient( 'tapor_categories' );

	if ( ! $cats ) {
		$cats = [];

		$c = tapor_app()->get_client();

		$attribute_types = $c->get_attribute_types();

		foreach ( $attribute_types as $attribute_type ) {
			if ( 'Type of analysis' === $attribute_type->name ) {
				$cats = $attribute_type->attribute_values;
				break;
			}
		}

		set_transient( 'tapor_categories', $cats, DAY_IN_SECONDS );
	}

	return array_map(
		function( $cat ) {
			return [
				'tid'  => $cat->id,
				'name' => $cat->name,
			];
		},
		$cats
	);
}

/**
 * Get local tools.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     Array of optional parameters.
 *     @type string $order Sort order. Accepts 'ASC' or 'DESC'. Default 'ASC'.
 *     @type string $orderby Field to order by. Accepts 'name' or 'date'. Default 'name'.
 *     @type int $posts_per_page Number of posts to return per page. Default -1 (no limit).
 *     @type int|bool $user_id If present, limit results to those used by given user ID. Default false.
 *     @type string $search_terms Filter results based on search terms.
 *     @type string $categories Array of categories to match. Should be passed as taxonomy term 'name' properties.
 * }
 * @return array Array of results (WP_Post objects).
 */
function tapor_get_tools( $args = array() ) {
	$r = array_merge( array(
		'order'          => 'ASC',
		'orderby'        => 'name',
		'posts_per_page' => -1,
		'user_id'        => false,
		'search_terms'   => '',
		'categories'     => array(), // By 'name'.
	), $args );

	$cache_key = md5( json_encode( $r ) );
	$tools     = wp_cache_get( $cache_key, 'tapor_tools' );

	if ( false === $tools ) {
		$query_args = array(
			'post_type'   => 'tapor_tool',
			'post_status' => 'publish',
			'tax_query'   => array(),
			'orderby'     => 'name',
			'order'       => 'ASC',
		);

		// posts_per_page
		// @todo Sanitize?
		$query_args['posts_per_page'] = $r['posts_per_page'];

		// orderby
		if ( in_array( $r['orderby'], array( 'name', 'date' ) ) ) {
			$query_args['orderby'] = $r['orderby'];
		}

		// order
		if ( 'DESC' === strtoupper( $r['order'] ) ) {
			$query_args['order'] = 'DESC';
		}

		// @todo support for multiple users
		if ( false !== $r['user_id'] ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => 'tapor_used_by_user',
//				'terms' => ddc_get_user_term( $r['user_id'] ),
				'field' => 'slug',
			);
		}

		if ( ! empty( $r['categories'] ) ) {
			// Can't pass 'name' properly to tax query. Fixed in WP 4.2 - #WP27810.
			$cat_ids = array();
			foreach ( (array) $r['categories'] as $cat_name ) {
				$_cat = get_term_by( 'name', $cat_name, 'tapor_category' );
				if ( $_cat ) {
					$cat_ids[] = $_cat->term_id;
				}
			}

			$query_args['tax_query'][] = array(
				'taxonomy' => 'tapor_category',
				'terms' => $cat_ids,
				'field' => 'id',
			);
		}

		// search_terms
		if ( ! empty( $r['search_terms'] ) ) {
			$query_args['s'] = $r['search_terms'];
		}

		$tools_query = new WP_Query( $query_args );

		// Add DiRT-specific info to post objects
		foreach ( $tools_query->posts as &$post ) {
			$post->dirt_node_id   = get_post_meta( $post->ID, 'dirt_node_id', true );
			$post->dirt_link      = get_post_meta( $post->ID, 'dirt_link', true );
			$post->dirt_thumbnail = get_post_meta( $post->ID, 'dirt_thumbnail', true );
			$post->dirt_image     = get_post_meta( $post->ID, 'dirt_image', true );
		}

		$tools = $tools_query->posts;

		wp_cache_add( $cache_key, $tools, 'tapor_tools' );
	}

	return $tools;
}

/**
 * Procedural wrapper for making API queries.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     @type string $type         Query type. 'search'.
 *     @type string $search_terms Terms to search.
 * }
 * @return array Array of formatted results.
 */
function tapor_query_tools( $args ) {
	$tools = array();

	if ( empty( $args['type'] ) ) {
		return $tools;
	}

	$c = tapor_app()->get_client();

	switch ( $args['type'] ) {
		case 'search' :
			if ( empty( $args['search_terms'] ) ) {
				return $tools;
			}

			$tools = $c->get_items_by_search_term( $args['search_terms'] );
			break;

		case 'category' :
			if ( empty( $args['cat_id'] ) ) {
				return $tools;
			}

			$tools = $c->get_items_for_tadirah_term( $args['cat_id'] );
			break;
	}

	// Normalize. This is awful.
	$parsed_tools = array();
	if ( ! empty( $tools->tools ) ) {
		foreach ( $tools->tools as $tool ) {
			$parsed_tool = tapor_parse_tool( $tool );
			if ( $parsed_tool ) {
				$parsed_tools[] = $parsed_tool;
			}
		}
	}

	return $parsed_tools;
}

/**
 * Parse raw tool data into a standard format.
 *
 * Depending on the particular endpoint, the API returns results that are formatted slightly differently. The current
 * function, surely the worst one in this entire plugin, standardizes them.
 *
 * @since 1.0.0
 *
 * @param object $tool Tool data from an API request.
 * @return array|bool A standardized array on success, false if the wrong kind of `$tool` is passed.
 */
function tapor_parse_tool( $tool ) {
	// The API returns an error string when nothing is found.
	if ( is_string( $tool ) ) {
		return false;
	}

	$retval = [
		'title' => $tool->name,
		'link' => TAPOR_URL . 'tools/' . $tool->id,
 		'categories' => [],
		'image' => TAPOR_URL . $tool->image_url,
		'description' => $tool->detail,
	];

	$tapor_cats = tapor_categories();
	$att_ids    = explode( '-', $tool->attribute_value_ids );
	foreach ( $tool->attribute_value_ids as $att_id ) {
		if ( empty( $att_id ) ) {
			continue;
		}

		// Whee!
		foreach ( $tapor_cats as $tapor_cat ) {
			if ( $att_id == $tapor_cat['tid'] ) {
				$retval['categories'][] = $tapor_cat['name'];
				break;
			}
		}
	}
	_b( $tool );
	_b( $retval );

	return $retval;
}

/**
 * Get the unique slug for tapor_used_by_user terms.
 *
 * @since 1.0.0
 *
 * @param int $user_id
 * @return string
 */
function tapor_get_user_term( $user_id ) {
	return 'tapor_tool_is_used_by_user_' . $user_id;
}

/**
 * Generate the markup for a tool.
 *
 * @param array $args {
 *     Tool data.
 *     @type string $link    URI of the DiRT page of the tool.
 *     @type string $title   Title of the tool.
 *     @type int    $node_id DiRT node ID.
 * }
 * @return string $html
 */
function tapor_tool_markup( $tool_data ) {
	$html = '';

	$tool = tapor_get_tool( 'node_id', $tool_data['node_id'] );

	$tool_id = false;
	if ( $tool ) {
		$tool_id = $tool->ID;
	}

	if ( $tool_data['thumbnail'] && 'dirt_logo_default.png' !== $tool_data['thumbnail'] ) {
		$image_url = DDC_IMAGE_BASE . 'styles/thumbnail/public/logos/' . $tool_data['thumbnail'];
	} else {
		$image_url = str_replace( 'public://', DDC_IMAGE_BASE, $tool_data['image'] );
	}

	$local_tool_url = '';
	if ( $tool ) {
		$local_tool_url = get_permalink( $tool );
	}

	$img_tag = '';
	if ( $image_url ) {
		$img_tag = sprintf(
			'<a href="%s"><img src="%s" /></a>',
			$local_tool_url ? esc_attr( $local_tool_url ) : esc_attr( $tool_data['link'] ),
			esc_attr( $image_url )
		);
	}

	$html .= sprintf(
		'<div class="tapor-tool-image">%s</div>',
		$img_tag
	);

	// Tool name
	if ( $local_tool_url ) {
		$tool_title = sprintf(
			'<a href="%s">%s</a>',
			esc_attr( $local_tool_url ),
			esc_html( $tool_data['title'] )
		);
	} else {
		$tool_title = esc_html( $tool_data['title'] );
	}

	$html .= sprintf(
		'<div class="tapor-tool-name">%s</div>',
		$tool_title
	);

	$used_by_group_members = array();
	if ( function_exists( 'bp_is_group' ) && bp_is_group() ) {
		$used_by_group_members = ddc_get_users_of_tool( $tool->ID, array(
			'count' => false,
			'group_id' => bp_get_current_group_id(),
		) );
	}

	$exclude = false;
	if ( ! empty( $used_by_group_members ) ) {
		$exclude = $used_by_group_members;
	}

	$used_by_users = array();
	if ( $tool ) {
		$used_by_users = tapor_get_users_of_tool( $tool->ID, array(
			'count' => 3,
			'exclude' => $exclude,
		) );
	}

	// Action button
	if ( is_user_logged_in() ) {
		$html .= tapor_get_action_checkbox( $tool_id, $tool_data['node_id'] );
	}

	// Tool description
	if ( ! empty( $tool_data['description'] ) ) {
		$link = $tool_data['link'];
		if ( ! $link ) {
			$link = 'http://dirtdirectory.org/node/' . $tool_data['node_id'];
		}

		$description  = strip_tags( trim( $tool_data['description'] ) );
		$description .= sprintf(
			'<a class="tapor-external-link" target="_blank" href="%s">%s</a>',
			esc_attr( $link ),
			__( 'Learn more on tapor.ca', 'tapor-client' )
		);

		$html .= sprintf(
			'<div class="tapor-tool-description-toggle"><a class="tapor-tool-description-toggle-link tapor-tools-description-toggle-link-show" href="#">%s</a><a class="tapor-tool-description-toggle-link tapor-tool-description-toggle-link-hide" href="#">%s</a></div><div class="tapor-tool-description">%s</div>',
			__( 'Show Description', 'tapor-client' ),
			__( 'Hide Description', 'tapor-client' ),
			wpautop( $description )
		);
	}

	$users_to_list = array();
	if ( ! empty( $used_by_group_members ) ) {
		$users_to_list = $used_by_group_members;
	} else {
		$users_to_list = $used_by_users;
	}

	if ( ! empty( $users_to_list ) ) {
		foreach ( $users_to_list as $used_by_user_id ) {
			$used_by_list_items[] = sprintf(
				'<span class="taportool-user tapor-tool-user-%d"><a href="%s">%s</a></span>',
				$used_by_user_id,
				bp_core_get_user_domain( $used_by_user_id ) . ddc_get_slug() . '/',
				bp_core_get_user_displayname( $used_by_user_id )
			);
		}

		if ( ! empty( $used_by_group_members ) ) {
			$used_by_count = count( $used_by_group_members );
			$text = sprintf(
				_n( 'Used by group member %s &mdash; <a href="%s">Show all users</a>', 'Used by group members %s &mdash; <a href="%s">Show all users</a>', $used_by_count, 'tapor-client' ),
				implode( ', ', $used_by_list_items ),
				$local_tool_url . '#users'
			);
		} else if ( ! empty( $used_by_list_items ) ) {
			$total_user_count = ddc_get_users_of_tool( $tool_id, array(
				'fields' => 'count',
			) );

			$used_by_list_item_count = $total_user_count - 3;
			if ( $used_by_list_item_count < 0 ) {
				$used_by_list_item_count = 0;
			}

			if ( $used_by_list_item_count ) {
				$text = sprintf(
					_n( 'Used by %s and %s other user &mdash; <a href="%s">Show all users</a>', 'Used by %s and %s other users &mdash; <a href="%s">Show all users</a>', $used_by_list_item_count, 'tapor-client' ),
					implode( ', ', $used_by_list_items ),
					number_format_i18n( $used_by_list_item_count ),
					$local_tool_url . '#users'
				);
			} else {
				$text = sprintf(
					__( 'Used by %s &mdash; <a href="%s">Show all users</a>', 'tapor-client' ),
					implode( ', ', $used_by_list_items ),
					$local_tool_url . '#users'
				);
			}
		}

		if ( ! empty( $text ) ) {
			$html .= sprintf(
				'<div class="tapor-tool-users" id="tapor-tool-%d-users">%s</div>',
				$tool_id,
				$text
			);
		}
	}

	return $html;
}

/**
 * Fetch a Tool object.
 *
 * @since 1.0.0
 *
 * @param string $by Field to query by. 'node_id', 'link', 'title'.
 * @param int|string $value Value to query by.
 * @return null|WP_Post
 */
function tapor_get_tool( $by, $value ) {
	$tool = null;

	switch ( $by ) {
		// Postmeta
		case 'node_id' :
		case 'link' :
			$posts = new WP_Query( array(
				'post_type' => 'ddc_tool',
				'post_status' => 'publish',
				'meta_query' => array(
					array(
						'key' => 'dirt_' . $by,
						'value' => $value,
					),
				),
				'posts_per_page' => 1,
			) );

			if ( ! empty( $posts->posts ) ) {
				$tool = $posts->posts[0];
			}

			break;

		case 'title' :
			// No way to do this in the API
			global $wpdb;
			$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'ddc_tool' AND post_status = 'publish' AND post_title = %s LIMIT 1", $value ) );

			if ( $post_id ) {
				$tool = get_post( $post_id );
			}

			break;
	}

	return $tool;
}

/**
 * Get the action checkbox markup for a tool (I use this).
 *
 * @since 1.0.0
 *
 * @param int $tool_id      Local ID of the tool.
 * @param int $tool_node_id Optional. DiRT tool node. Used if $tool_id is not present (ie it's not yet a local tool).
 * @return string
 */
function tapor_get_action_checkbox( $tool_id, $tool_node_id = '' ) {
	$url_base = bp_get_requested_url();
	if ( is_user_logged_in() ) {
		$my_tools = tapor_get_tools_of_user( get_current_user_id() );

		if ( ! $tool_node_id ) {
			$tool_node_id = get_post_meta( $tool_id, 'dirt_node_id', true );
		}

		if ( in_array( $tool_id, wp_list_pluck( $my_tools, 'ID' ) ) ) {
			$url_base = add_query_arg( 'remove_dirt_tool', $tool_node_id );
			$button = sprintf(
				'<div class="tapor-tool-action dirt-tool-action-remove"><label for="tapor-tool-remove-%1$d" class="tapor-tool-action-label"><a href="%2$s">' . __( 'I use this', 'tapor-client' ) . '</a></label> <input checked="checked" type="checkbox" value="%d" name="tapor-tool-remove[%1$d]" id="tapor-tool-remove-%1$d" data-tool-id="%1$d" data-tool-node-id="%5$d" data-nonce="%4$s"><span class="tapor-tool-action-question tapor-tool-action-question-remove">%3$s</span></div>',
				$tool_id,
				wp_nonce_url( $url_base, 'ddc_remove_tool' ),
				__( 'Click to remove this tool from your list', 'tapor-client' ),
				wp_create_nonce( 'ddc_toggle_tool_' . $tool_node_id ),
				$tool_node_id
			);
		} else {
			$url_base = add_query_arg( 'add_dirt_tool', $tool_node_id );
			$button = sprintf(
				'<div class="tapor-tool-action tapor-tool-action-add"><label for="tapor-tool-add-%1$d" class="tapor-tool-action-label"><a href="%2$s">' . __( 'I use this', 'tapor-client' ) . '</a></label> <input type="checkbox" value="%d" name="tapor-tool-add[%1$d]" id="tapor-tool-add-%1$d" data-tool-id="%1$d" data-tool-node-id="%5$d" data-nonce="%4$s"><span class="tapor-tool-action-question tapor-tool-action-question-add">%3$s</span></div>',
				$tool_id,
				wp_nonce_url( $url_base, 'ddc_add_tool' ),
				__( 'Click to show that you use this tool', 'tapor-client' ),
				wp_create_nonce( 'ddc_toggle_tool_' . $tool_node_id ),
				$tool_node_id
			);
		}
	}

	return $button;
}

/**
 * Get tools of a given user.
 *
 * @since 1.0.0
 *
 * @param int   $user_id ID of the user.
 * @param array $args    See {@see tapor_get_tools()} for a description.
 * @return array
 */
function tapor_get_tools_of_user( $user_id, $args = array() ) {
	$args['user_id'] = $user_id;
	return tapor_get_tools( $args );
}
