<?php

namespace CAC\TAPoR\BuddyPress;

class GroupIntegration {
	private function __construct() {}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public function set_up_hooks() {
		bp_register_group_extension( '\CAC\TAPoR\BuddyPress\GroupExtension' );
	}
}
