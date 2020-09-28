window.wp = window.wp || {};

( function( $ ) {
	var checked,
		tool_id,
		$clicked,
		$current_action,
		$current_checkbox,
		$current_tool,
		$tools,
		$tool_description_toggles,
		$tool_users_toggles;

	$( document ).ready( function() {
		// Add 'js' class - ugh
		$( 'body' ).removeClass( 'no-js' ).addClass( 'js' );

		$tools = $( '.tapor-tools' );

		init_tool_description_toggle();

		init_tool_checkboxes();
	} );

	/**
	 * Initialize "descritpion" toggles
	 */
	function init_tool_description_toggle() {
		$tools.find( '.tapor-tool-description-toggle-link-hide' ).hide();
		$tools.find( '.tapor-tool-description' ).hide();

		$tool_description_toggles = $( '.tapor-tool-description-toggle a' );

		$tool_description_toggles.on( 'click', function() {
			$clicked = $( this );
			$clicked.closest( '.tapor-tools > li' ).find( '.tapor-tool-description' ).toggle();
			$clicked.siblings( '.tapor-tool-description-toggle-link' ).show();
			$clicked.hide();
			return false;
		} );
	}
	/**
	 * Initialize the "I use this" checkbox toggles.
	 */
	function init_tool_checkboxes() {
		$tools.find( '.tapor-tool-action' ).each( function() {
			$current_tool = $(this);
			$current_tool.find( 'input[type="checkbox"], label' ).on( 'click', function( e ) {
				e.preventDefault();

				$current_action = $(this).closest( '.tapor-tool-action' );

				// Don't allow multiple clicks when AJAX is in effect.
				if ( $current_action.hasClass( 'spinner' ) ) {
					return false;
				}

				$current_action.addClass( 'spinner' );

				// Gah. Clicking on the input means that 'checked' gets disabled
				// BEFORE we can check it, so we must flip
				if ( this.tagName === 'INPUT' ) {
					$current_checkbox = $(this);
					checked = ! $current_checkbox.is( ':checked' );
				} else {
					$current_checkbox = $current_action.find( 'input[type="checkbox"]' );
					checked = $current_checkbox.is( ':checked' );
				}

				tool_id = $current_checkbox.data( 'tool-id' );

				$.ajax( {
					url: ajaxurl,
					method: 'POST',
					data: {
						'tool_id': tool_id,
						'action': 'tapor_tool_use_toggle',
						'tapor_id': $current_checkbox.data( 'tapor-id' ),
						'nonce': $current_checkbox.data( 'nonce' ),
						'toggle': checked ? 'remove' : 'add'
					},
					success: function( response ) {
						if ( response.success ) {
							if ( 'add' == response.data.toggle ) {
								$current_checkbox.prop( 'checked', true );
								$current_checkbox.closest( '.tapor-tool-action' ).find( '.tapor-tool-action-question' ).html( TAPoR.remove_gloss );
							} else {
								$current_checkbox.removeProp( 'checked' );
								$current_checkbox.closest( '.tapor-tool-action' ).find( '.tapor-tool-action-question' ).html( TAPoR.add_gloss );
							}
						}

						$current_action.removeClass( 'spinner' );
					}
				} );
			} );
		} );
	}
} )( jQuery );
