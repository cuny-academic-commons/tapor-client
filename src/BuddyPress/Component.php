<?php

namespace CAC\TAPoR\BuddyPress;

use \WP_Query;
use \BP_Component;

/**
 * Implementation of BP_Component.
 *
 * Integrates into user profiles.
 *
 * @since 1.0.0
 */
class Component extends BP_Component {
	/**
	 * Does the given user have tools?
	 *
	 * @since 1.1.0
	 * @var array
	 */
	protected $user_has_tools = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::start(
			'tapor',
			__( 'Digital Research Tools', 'tapor-client' ),
			TAPOR_PLUGIN_DIR,
			array(
				'adminbar_myaccount_order' => 83,
			)
		);
	}

	/**
	 * Set up global data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args See {@see BP_Component::setup_globals()}.
	 */
	public function setup_globals( $args = array() ) {
		parent::setup_globals( array(
			'slug'          => 'tools',
			'has_directory' => false,
		) );
	}

	/**
	 * Set up nav items.
	 *
	 * @since 1.0.0
	 *
	 * @param array $main_nav See {@see BP_Component::setup_nav()}.
	 * @param array $sub_nav  See {@see BP_Component::setup_nav()}.
	 */
	public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
		$main_nav = array(
			'name'                    => __( 'Digital Research Tools', 'tapor-client' ),
			'slug'                    => $this->slug,
			'position'                => 83,
			'screen_function'         => array( $this, 'template_loader' ),
			'default_subnav_slug'     => 'tools',
			'show_for_displayed_user' => true, // Going to change this later
		);

		add_action( 'init', array( $this, 'change_tab_visibility' ), 100 );

		$sub_nav[] = array(
			'name'            => __( 'Tools', 'tapor-client' ),
			'slug'            => 'tools',
			'parent_url'      => bp_displayed_user_domain() . $this->slug . '/',
			'parent_slug'     => $this->slug,
			'screen_function' => array( $this, 'template_loader' )
		);

		parent::setup_nav( $main_nav, $sub_nav );
	}

	/**
	 * Should the tab be shown for the user?
	 *
	 * Current logic: show if the user has any tools.
	 *
	 * Have to do it like this because post types are not registered at the
	 * time that the nav is set up. Blargh.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function change_tab_visibility() {
		// Don't bother with query if there's no user.
		if ( ! bp_displayed_user_id() ) {
			return;
		}

		buddypress()->members->nav->edit_nav( array(
			'show_for_displayed_user' => $this->user_has_tools( bp_displayed_user_id() ),
		), 'dirt' );
	}

	/**
	 * Does the user have tools?
	 *
	 * @since 1.1.0
	 *
	 * @return bool
	 */
	protected function user_has_tools( $user_id ) {
		if ( isset( $this->user_has_tools[ $user_id ] ) ) {
			return (bool) $this->user_has_tools[ $user_id ];
		}

		$tools_query = new WP_Query( array(
			'post_type'              => tapor_app()->schema::post_type(),
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ID',
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => tapor_app()->schema::used_by_taxonomy(),
					'terms'    => tapor_get_user_term( $user_id ),
					'field'    => 'slug',
				),
			),
		) );

		$this->user_has_tools[ $user_id ] = $tools_query->have_posts();

		return $this->user_has_tools[ $user_id ];
	}

	/**
	 * Template loader.
	 *
	 * Also responsible for enqueing assets.
	 *
	 * @since 1.0.0
	 */
	public function template_loader() {
		add_action( 'bp_template_content', array( $this, 'template_content_loader' ) );
		wp_enqueue_style( 'tapor-client' );
		wp_enqueue_script( 'tapor-client' );
		bp_core_load_template( 'members/single/plugins' );
	}

	/**
	 * Template content loader.
	 *
	 * @since 1.0.0
	 */
	public function template_content_loader() {
		bp_get_template_part( 'tapor/member' );
	}

	/**
	 * Set up the component entries in the WordPress Admin Bar.
	 *
	 * @since 1.1.0
	 *
	 * @param array $wp_admin_nav See BP_Component::setup_admin_bar() for a description.
	 */
	public function setup_admin_bar( $wp_admin_nav = array() ) {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! $this->user_has_tools( bp_loggedin_user_id() ) ) {
			return;
		}

		$wp_admin_nav[] = array(
			'parent' => buddypress()->my_account_menu_id,
			'id'     => 'my-account-tapor',
			'title'  => __( 'Digital Research Tools', 'tapor-client' ),
			'href'   => bp_loggedin_user_domain() . $this->slug . '/',
		);

		// Add a subnav just so that the styling isn't weird.
		$wp_admin_nav[] = array(
			'parent' => 'my-account-tapor',
			'id'     => 'my-account-tapor-tools',
			'title'  => __( 'My Tools', 'tapor-client' ),
			'href'   => bp_loggedin_user_domain() . $this->slug . '/',
		);

		parent::setup_admin_bar( $wp_admin_nav );
	}
}
