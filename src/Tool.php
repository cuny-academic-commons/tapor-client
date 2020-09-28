<?php

namespace CAC\TAPoR;

use \WP_Query;

class Tool {
	protected $post_type;

	protected $data = [
		'id'          => null,
		'tapor_id'    => null,
		'title'       => '',
		'link'        => '',
		'image'       => '',
		'description' => '',
		'categories'  => [],
	];

	public function __construct() {
		$this->post_type = tapor_app()->schema::post_type();
	}

	public static function get_instance_by_id( $id ) {
		$post = get_post( $id );

		$tool = null;
		if ( $post ) {
			$tool = new self();
			$tool->set_id( $id );
			$tool->set_title( $post->post_title );
			$tool->set_description( $post->post_content );

			$tapor_id = get_post_meta( $id, 'tapor_id', true );
			$tool->set_tapor_id( $tapor_id );

			$link = get_post_meta( $id, 'tapor_link', true );
			$tool->set_link( $link );

			$image = get_post_meta( $id, 'tapor_image', true );
			$tool->set_image( $image );

			$categories = get_the_terms( $id, tapor_app()->schema::category_taxonomy() );
			if ( $categories ) {
				$cat_names = wp_list_pluck( $categories, 'name' );
			} else {
				$cat_names = [];
			}
			$tool->set_categories( $cat_names );
		}

		return $tool;
	}

	public static function get_instance_by_tapor_id( $tapor_id ) {
		$posts = new WP_Query(
			[
				'post_type'      => 'tapor_tool',
				'post_status'    => 'publish',
				'meta_query'     => [
					array(
						'key'   => 'tapor_id',
						'value' => $tapor_id,
					),
				],
				'posts_per_page' => 1,
				'fields'         => 'ids',
			]
		);

		if ( ! empty( $posts->posts ) ) {
			$tool_id = $posts->posts[0];

			if ( $tool_id ) {
				$instance = self::get_instance_by_id( $posts->posts[0] );
				return $instance;
			}
		}

		return null;
	}

	public function save() {
		$postarr = [
			'post_type'    => tapor_app()->schema::post_type(),
			'post_title'   => $this->get_title(),
			'post_status'  => 'publish',
			'post_content' => $this->get_description(),
		];

		$id = $this->get_id();
		if ( $id ) {

		} else {
			$id = wp_insert_post( $postarr );

			$saved = (bool) $id;

			if ( $saved ) {
				$this->set_id( $id );
			}
		}

		// Refetch the ID.
		if ( $saved ) {
			update_post_meta( $id, 'tapor_link', $this->get_link() );
			update_post_meta( $id, 'tapor_id', $this->get_tapor_id() );
			update_post_meta( $id, 'tapor_image', $this->get_image() );

			wp_set_object_terms( $id, $this->get_categories(), tapor_app()->schema::category_taxonomy() );
		}

		return $saved;
	}

	public function get_id() {
		return (int) $this->data['id'];
	}

	public function get_title() {
		return $this->data['title'];
	}

	public function get_description() {
		return $this->data['description'];
	}

	public function get_tapor_id() {
		return (int) $this->data['tapor_id'];
	}

	public function get_link() {
		return $this->data['link'];
	}

	public function get_image() {
		return $this->data['image'];
	}

	public function get_categories() {
		return $this->data['categories'];
	}

	public function set_id( $id ) {
		$this->data['id'] = (int) $id;
	}

	public function set_tapor_id( $tapor_id ) {
		$this->data['tapor_id'] = (int) $tapor_id;
	}

	public function set_title( $title ) {
		$this->data['title'] = $title;
	}

	public function set_description( $description ) {
		$this->data['description'] = $description;
	}

	public function set_image( $image ) {
		$this->data['image'] = $image;
	}

	public function set_link( $link ) {
		$this->data['link'] = $link;
	}

	public function set_categories( $categories ) {
		$this->data['categories'] = $categories;
	}

	/**
	 * Associate a tool with a user.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id ID of the user.
	 * @return bool
	 */
	public function associate_with_user( $user_id ) {
		$tt_ids = wp_set_object_terms( $this->get_id(), tapor_get_user_term( $user_id ), tapor_app()->schema::used_by_taxonomy() );

		if ( ! empty( $tt_ids ) ) {
			do_action( 'tapor_associated_tool_with_user', $this, $user_id, $tt_ids );
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get users of a given tool.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args
	 * @return bool|array $users False on failure, user IDs on success.
	 */
	public function get_users_of_tool( $args = [] ) {
		$args = array_merge(
			[
				'group_id'     => false,
				'include_self' => true,
				'count'        => false,
				'exclude'      => false,
				'fields'       => 'all',
			],
			$args
		);

		$terms = get_the_terms( $this->get_id(), tapor_app()->schema::used_by_taxonomy() );

		$exclude = array();
		if ( ! empty( $args['exclude'] ) ) {
			$exclude = wp_parse_id_list( $args['exclude'] );
		}

		$user_ids = array();

		$group_ids = null;
		if ( false !== $args['group_id'] ) {
			$group_ids = wp_parse_id_list( $args['group_id'] );
		}

		$group_member_ids = array();
		if ( ! empty( $group_ids ) && bp_is_active( 'groups' ) ) {
			foreach ( $group_ids as $group_id ) {
				$group_members = wp_cache_get( $group_id, 'ddc_bp_group_members' );
				if ( false === $group_members ) {
					$group_member_query = new \BP_Group_Member_Query( array(
						'group_id'   => $args['group_id'],
						'type'       => 'alphabetical',
						'group_role' => array( 'admin', 'mod', 'member' ),
					) );
					wp_cache_add( $group_id, $group_member_query->results, 'ddc_bp_group_members' );
					$this_group_member_ids = wp_list_pluck( $group_member_query->results, 'ID' );
				} else {
					$this_group_member_ids = wp_list_pluck( $group_members, 'ID' );
				}
			}

			$group_member_ids = array_merge( $group_member_ids, $this_group_member_ids );
		}

		if ( ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$user_id = tapor_get_user_id_from_usedby_term_slug( $term->slug );

				if ( in_array( $user_id, $exclude ) ) {
					continue;
				}

				// If limiting to a group, check that the user is a member first.
				if ( ! empty( $args['group_id'] ) && bp_is_active( 'groups' ) ) {
					if ( ! in_array( $user_id, $group_member_ids ) && ( ! $args['include_self'] || $user_id !== bp_loggedin_user_id() ) ) {
						continue;
					}
				}

				$user_ids[] = $user_id;
			}
		}

		if ( empty( $user_ids ) ) {
			$user_ids = array( 0 );
		} elseif ( $args['count'] && $args['count'] < count( $user_ids ) ) {
			$keys = array_rand( $user_ids, $args['count'] );
			$_user_ids = array();
			foreach ( $keys as $key ) {
				$_user_ids[] = (int) $user_ids[ $key ];
			}
			$user_ids = $_user_ids;
		}

		if ( 'count' === $args['fields'] ) {
			return count( $user_ids );
		}

		return $user_ids;
	}
}
