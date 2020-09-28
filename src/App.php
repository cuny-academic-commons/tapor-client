<?php

namespace CAC\TAPoR;

class App {
	protected static $post_type = 'tapor_tool';

	public $schema;
	public $theme_compat;
	public $user_integration;
	public $bp_groups_integration;
	public $bp_activity_integration;

	/**
	 * Constructor.
	 *
	 * @since 0.1.0
	 *
	 * @return CAC\TAPoR\App
	 */
	private function __construct() {
		return $this;
	}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public function init() {
		require TAPOR_PLUGIN_DIR . '/includes/functions.php';

		$this->schema = Schema::get_instance();
		$this->schema->set_up_hooks();

		$this->theme_compat = ThemeCompat::get_instance();
		$this->theme_compat->set_up_hooks();

		$this->user_integration = UserIntegration::get_instance();
		$this->user_integration->set_up_hooks();

		if ( function_exists( 'buddypress' ) ) {
			$this->init_bp();
		}
	}

	public function init_bp() {
		if ( bp_is_active( 'groups' ) ) {
			$this->bp_groups_integration = BuddyPress\GroupIntegration::get_instance();
			$this->bp_groups_integration->set_up_hooks();
		}

		if ( bp_is_active( 'activity' ) ) {
			$this->bp_activity_integration = BuddyPress\ActivityIntegration::get_instance();
			$this->bp_activity_integration->set_up_hooks();
		}
	}

	public function get_client() {
		static $client;

		if ( empty( $client ) ) {
			$client = new Client();
		}

		return $client;
	}
}
