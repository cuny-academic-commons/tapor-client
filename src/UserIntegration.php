<?php

namespace CAC\TAPoR;

class UserIntegration {
	private function __construct() {}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public function set_up_hooks() {
		add_action( 'wp_ajax_tapor_tool_use_toggle', [ $this, 'tool_use_toggle_ajax_cb' ] );
	}

	/**
	 * AJAX callback for tool use toggle.
	 *
	 * @since 1.0.0
	 */
	public function tool_use_toggle_ajax_cb() {
		$data = array(
			'nonce'    => '',
			'tool_id'  => '',
			'toggle'   => '',
			'tapor_id' => '',
		);

		foreach ( $data as $dkey => &$dvalue ) {
			if ( isset( $_POST[ $dkey ] ) ) {
				$dvalue = stripslashes( $_POST[ $dkey ] );
			}
		}

		// Nonce check.
		if ( ! wp_verify_nonce( $data['nonce'], "tapor_toggle_tool_{$data['tapor_id']}" ) ) {
			wp_send_json_error( __( 'Could not perform requested action.', 'tapor-client' ) );
		}

		if ( $data['tool_id'] ) {
			$tool = Tool::get_instance_by_id( $data['tool_id'] );
		} else {
			$tool = Tool::get_instance_by_tapor_id( $data['tapor_id'] );
			if ( ! $tool ) {
				// Must create local tool if it does not yet exist.
				$tool_data = tapor_app()->get_client()->get_item_by_id( $data['tapor_id'] );
				if ( ! empty( $tool_data ) ) {
					$_tool = tapor_parse_tool( $tool_data );

					$tool_obj = new Tool();
					$tool_obj->set_tapor_id( $data['tapor_id'] );
					$tool_obj->set_title( $_tool['title'] );
					$tool_obj->set_description( $_tool['description'] );
					$tool_obj->set_image( $_tool['image'] );
					$tool_obj->set_link( $_tool['link'] );
					// todo categories

					$saved = $tool_obj->save();

					if ( $saved ) {
						$tool = $tool_obj;
					}
				}
			}
		}

		if ( ! $tool ) {
			wp_send_json_error( __( 'Could not find tool.', 'tapor-client' ) );
		}

		$success = false;
		$message = __( 'Could not perform requested action', 'tapor-client' );

		switch ( $data['toggle'] ) {
			case 'remove' :
				$removed = tapor_dissociate_tool_from_user( $tool->get_id(), bp_loggedin_user_id() );

				if ( $removed ) {
					$message = __( 'You have successfully removed this tool.', 'tapor-client' );
					$success = true;
				} else {
					$message = __( 'There was a problem removing this tool.', 'tapor-client' );
					$success = false;
				}

				break;

			case 'add' :
				$added = $tool->associate_with_user( bp_loggedin_user_id() );

				if ( $added ) {
					$message = __( 'You have successfully added this tool.', 'tapor-client' );
					$success = true;
				} else {
					$message = __( 'There was a problem adding this tool.', 'tapor-client' );
					$success = false;
				}

				break;
		}

		$retval = array(
			'message' => $message,
			'toggle'  => $data['toggle'],
		);

		if ( $success ) {
			wp_send_json_success( $retval );
		} else {
			wp_send_json_error( $retval );
		}
	}
}
