<?php

namespace CAC\TAPoR\BuddyPress;

/**
 * Implementation of \BP_Group_Extension.
 *
 * @since 1.0.0
 */
class GroupExtension extends \BP_Group_Extension {

	/**
	 * Current group ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $current_group_id;

	/**
	 * Current group object.
	 *
	 * @since 1.0.0
	 * @var BP_Groups_Group
	 */
	protected $current_group;

	/**
	 * Group settings.
         *
	 * @since 1.0.0
	 * @var array
	 */
	protected $current_group_settings = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::init( array(
			'name'   => __( 'Digital Research Tools', 'tapor-client' ),
			'slug'   => 'tapor',
			'access' => $this->access_setting(),
			'screens' => array(
				'edit' => array(
					'enabled' => true,
				),
			),
		) );
	}

	/**
	 * Determine access setting for the main plugin tab.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function access_setting() {
		// Has the admin enabled?
		$settings = $this->get_current_group_settings();

		if ( empty( $settings['enabled'] ) ) {
			return 'noone';
		}

		// If it's enabled, return a value based on group status
		$group = $this->get_current_group();

		if ( 'public' === $group->status ) {
			return 'anyone';
		} else {
			return 'member';
		}
	}

	/**
	 * Main tab display.
	 *
	 * @since 1.0.0
	 *
	 * @param int $group_id ID of the current group.
	 */
	public function display( $group_id = null ) {
		wp_enqueue_style( 'tapor-client' );
		wp_enqueue_script( 'tapor-client' );

		bp_get_template_part( 'tapor/group' );
	}

	/**
	 * Settings screen markup.
	 *
	 * @since 1.0.0
	 *
	 * @param int $group_id ID of the current group.
	 */
	public function settings_screen( $group_id = null ) {
		$settings = $this->get_current_group_settings();

		?>

		<label for="tapor-enabled"><?php _e( 'Enable "Digital Research Tools" tab for this group?', 'tapor-client' ) ?></label>
		<select id="tapor-enabled" name="tapor-enabled">
			<option value="1" <?php selected( '1', $settings['enabled'] ) ?>><?php _e( 'Enabled', 'tapor-client' ) ?></option>
			<option value="0" <?php selected( '0', $settings['enabled'] ) ?>><?php _e( 'Disabled', 'tapor-client' ) ?></option>
		</select>

		<br /><br />

		<?php
	}

	/**
	 * Settings screen save callback.
	 *
	 * @since 1.0.0
	 *
	 * @param int $group_id ID of the current group.
	 */
	public function settings_screen_save( $group_id = null ) {
		$old_settings = $new_settings = $this->get_current_group_settings();

		if ( isset( $_POST['tapor-enabled'] ) ) {
			if ( '0' == $_POST['tapor-enabled'] ) {
				$new_settings['enabled'] = '0';
			} else {
				$new_settings['enabled'] = '1';
			}
		}

		if ( $old_settings !== $new_settings ) {
			groups_update_groupmeta( $group_id, 'tapor_settings', $new_settings );
			bp_core_add_message( __( 'Settings updated!', 'tapor-client' ) );
		}
	}

	/**
	 * Get the current group ID.
	 *
	 * @since 1.0.0
	 *
	 * @return int ID of the current group.
	 */
	protected function get_current_group_id() {
		if ( is_null( $this->current_group_id ) ) {
			$this->current_group_id = bp_get_current_group_id();
		}

		return $this->current_group_id;
	}

	/**
	 * Get the current group.
	 *
	 * @since 1.0.0
	 *
	 * @return BP_Groups_Group Group object.
	 */
	protected function get_current_group() {
		if ( is_null( $this->current_group ) ) {
			$this->current_group = groups_get_group( array(
				'group_id' => $this->get_current_group_id(),
			) );
		}

		return $this->current_group;
	}

	/**
	 * Get settings for the current group.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_current_group_settings() {
		if ( empty( $this->current_group_settings ) ) {
			$saved_settings = groups_get_groupmeta( $this->get_current_group_id(), 'tapor_settings' );

			if ( ! is_array( $saved_settings ) ) {
				$saved_settings = array();
			}

			$this->current_group_settings = array_merge( array(
				'enabled' => '0',
			), $saved_settings );
		}

		return $this->current_group_settings;
	}
}
