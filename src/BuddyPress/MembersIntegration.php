<?php

namespace CAC\TAPoR\BuddyPress;

class MembersIntegration {
	private function __construct() {}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public function set_up_hooks() {
		buddypress()->tapor = new Component();
	}
}
