<?php

namespace CAC\TAPoR\BuddyPress;

class ActivityIntegration {
	private function __construct() {}

	public static function get_instance() {
		static $instance;

		if ( empty( $instance ) ) {
			$instance = new self();
		}

		return $instance;
	}

	public function set_up_hooks() {
		add_action( 'bp_register_activity_actions', [ $this, 'register_activity_actions' ] );
		add_action( 'tapor_associated_tool_with_user', [ $this, 'create_tool_marked_used_activity' ], 10, 2 );
		add_action( 'tapor_dissociated_tool_from_user', [ $this, 'delete_tool_marked_used_activity' ], 10, 2 );
	}

	/**
	 * Register activity actions.
	 *
	 * @since 1.0.0
	 */
	public function register_activity_actions() {
		bp_activity_set_action(
			'tapor',
			'tool_marked_used',
			__( 'Digital Research Tool Used', 'tapor-client' ),
			[ $this, 'format_activity_action_tool_marked_used' ],
			__( 'Digital Research Tools Used', 'tapor-client' ),
			[ 'activity', 'member', 'member_groups' ]
		);
	}

	/**
	 * Activity action format callback.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action   Action string.
	 * @param object $activity Activity object.
	 * @return string Formatted activity string.
	 */
	public function format_activity_action_tool_marked_used( $action, $activity ) {
		$user_link = sprintf(
			'<a href="%s">%s</a>',
			bp_core_get_user_domain( $activity->user_id ) . 'dirt/',
			bp_core_get_user_displayname( $activity->user_id )
		);

		$tool = get_post( $activity->item_id );
		if ( ! $tool ) {
			return '';
		}

		$tool_link = sprintf(
			'<a href="%s">%s</a>',
			get_permalink( $tool ),
			esc_html( $tool->post_title )
		);

		$action = sprintf(
			__( '%1$s uses the digital research tool %2$s', 'tapor-client' ),
			$user_link,
			$tool_link
		);

		return $action;
	}

	/**
	 * Generate a "uses the tool" activity item on tool association.
	 *
	 * @since 1.0.0
	 *
	 * @param Tool Tool     Tool object.
	 * @param int  $user_id ID of the user.
	 */
	public function create_tool_marked_used_activity( \CAC\TAPoR\Tool $tool, $user_id ) {
		bp_activity_add( array(
			'component' => 'tapor',
			'type'      => 'tool_marked_used',
			'user_id'   => $user_id,
			'item_id'   => $tool->get_id(),
		) );
	}

	/**
	 * Delete tool_marked_used activity item when dissociating.
	 *
	 * @since 1.0.0
	 *
	 * @param Tool $tool    Tool object.
	 * @param int  $user_id ID of the user.
	 */
	public function delete_tool_marked_used_activity( \CAC\TAPoR\Tool $tool, $user_id ) {
		$activity = bp_activity_get( array(
			'filter' => array(
				'object'     => 'tapor',
				'user_id'    => $user_id,
				'action'     => 'tool_marked_used',
				'primary_id' => $tool->get_id(),
			),
		) );

		if ( ! empty( $activity['activities'] ) ) {
			bp_activity_delete_by_activity_id( $activity['activities'][0]->id );
			return true;
		}

		return false;
	}
}
