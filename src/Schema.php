<?php

namespace CAC\TAPoR;

class Schema {
	private function __construct() {}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public function set_up_hooks() {
		add_action( 'init', [ __CLASS__, 'register_post_type' ] );
		add_action( 'init', [ __CLASS__, 'register_taxonomies' ], 15 );
	}

	public static function post_type() {
		return 'tapor_tool';
	}

	public static function used_by_taxonomy() {
		return 'tapor_used_by_user';
	}

	public static function category_taxonomy() {
		return 'tapor_category';
	}

	public static function register_post_type() {
		register_post_type(
			self::post_type(),
			[
				'label'  => __( 'TAPoR Tools', 'tapor-client' ),
				'public' => true,
				'has_archive' => true,
				'rewrite' => array(
					'slug' => _x( 'tool', 'Tool rewrite slug', 'dirt-directory-client' ),
					'with_front' => false,
				),
			]
		);
	}

	public static function register_taxonomies() {
		register_taxonomy(
			self::used_by_taxonomy(),
			self::post_type(),
			[
				'label'  => __( 'TAPoR Tool Users', 'tapor-client' ),
				'public' => false,
			]
		);

		register_taxonomy(
			self::category_taxonomy(),
			self::post_type(),
			[
				'label'  => __( 'TAPoR Tool Category', 'tapor-client' ),
				'public' => true,
			]
		);
	}
}
