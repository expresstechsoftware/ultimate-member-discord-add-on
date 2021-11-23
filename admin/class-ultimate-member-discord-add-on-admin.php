<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.expresstechsoftwares.com
 * @since      1.0.0
 *
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/admin
 * @author     ExpressTech Softwares Solutions Pvt Ltd <contact@expresstechsoftwares.com>
 */
class Ultimate_Member_Discord_Add_On_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ultimate_Member_Discord_Add_On_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ultimate_Member_Discord_Add_On_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name . 'discord_tabs_css', plugin_dir_url( __FILE__ ) . 'css/skeletabs.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ultimate-member-discord-add-on-admin.min.css', array(), $this->version, 'all' );
                wp_enqueue_style( $this->plugin_name . 'fa-icon', '//use.fontawesome.com/releases/v5.5.0/css/all.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ultimate_Member_Discord_Add_On_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ultimate_Member_Discord_Add_On_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name . '-tabs-js', plugin_dir_url( __FILE__ ) . 'js/skeletabs.js', array( 'jquery' ), $this->version, false );
		
               
                
                wp_register_script(
                        'ultimate-member-discord-add-on-admin',
			plugin_dir_url( __FILE__ ) . 'js/ultimate-member-discord-add-on-admin.js',
			array( 'jquery' ),
			$this->version, 
                        false
		);
                
                wp_enqueue_script( 'ultimate-member-discord-add-on-admin' );
		
                wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
               
                
		$script_params = array(
			'admin_ajax'        => admin_url( 'admin-ajax.php' ),
			'permissions_const' => ULTIMATE_MEMBER_DISCORD_BOT_PERMISSIONS,
			'is_admin'          => is_admin(),
			'ets_ultimatemember_discord_nonce' => wp_create_nonce( 'ets-ultimatemember-ajax-nonce' ),
		);
		wp_localize_script( 'ultimate-member-discord-add-on-admin', 'etsUltimateMemberParams', $script_params );                

	}

	/*
	Method to add discord setting sub-menu under top level menu of ultimate-member.
	*/
	public function ets_ultimatemember_add_settings_menu() {
		add_submenu_page( 'ultimatemember', __( 'Discord Settings', 'ultimate-member-discord-add-on' ), __( 'Discord Settings', 'ultimate-member-discord-add-on' ), 'manage_options', 'ultimatemember-discord', array( $this, 'ets_utlimatemember_discord_setting_page' ) );
	}

	/*
	Callback to Display settings page
	*/
	public function ets_utlimatemember_discord_setting_page() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		require_once ULTIMATE_MEMBER_DISCORD_PLUGIN_DIR_PATH . 'admin/partials/ultimate-member-discord-add-on-admin-display.php';
	}

	/*
	Save application details
	*/
	public function ets_ultimatemember_discord_application_settings() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		$ets_ultimatemember_discord_client_id = isset( $_POST['ets_ultimatemember_discord_client_id'] ) ? sanitize_text_field( trim( $_POST['ets_ultimatemember_discord_client_id'] ) ) : '';

		$ets_ultimatemember_discord_client_secret = isset( $_POST['ets_ultimatemember_discord_client_secret'] ) ? sanitize_text_field( trim( $_POST['ets_ultimatemember_discord_client_secret'] ) ) : '';

		$ets_ultimatemember_discord_bot_token = isset( $_POST['ets_ultimatemember_discord_bot_token'] ) ? sanitize_text_field( trim( $_POST['ets_ultimatemember_discord_bot_token'] ) ) : '';

		$ets_ultimatemember_discord_redirect_url = isset( $_POST['ets_ultimatemember_discord_redirect_url'] ) ? sanitize_text_field( trim( $_POST['ets_ultimatemember_discord_redirect_url'] ) ) : '';

		$ets_ultimatemember_discord_server_id = isset( $_POST['ets_ultimatemember_discord_server_id'] ) ? sanitize_text_field( trim( $_POST['ets_ultimatemember_discord_server_id'] ) ) : '';

		if ( isset( $_POST['submit'] ) ) {
			if ( isset( $_POST['ets_ultimatemember_discord_save_settings'] ) && wp_verify_nonce( $_POST['ets_ultimatemember_discord_save_settings'], 'save_ultimatemember_discord_general_settings' ) ) {
				if ( $ets_ultimatemember_discord_client_id ) {
					update_option( 'ets_ultimatemember_discord_client_id', $ets_ultimatemember_discord_client_id );
				}

				if ( $ets_ultimatemember_discord_client_secret ) {
					update_option( 'ets_ultimatemember_discord_client_secret', $ets_ultimatemember_discord_client_secret );
				}

				if ( $ets_ultimatemember_discord_bot_token ) {
					update_option( 'ets_ultimatemember_discord_bot_token', $ets_ultimatemember_discord_bot_token );
				}

				if ( $ets_ultimatemember_discord_redirect_url ) {
					// add a query string param `via` GH #185.
					$ets_ultimatemember_discord_redirect_url = ets_get_ultimatemember_discord_formated_discord_redirect_url( $ets_ultimatemember_discord_redirect_url );
					update_option( 'ets_ultimatemember_discord_redirect_url', $ets_ultimatemember_discord_redirect_url );
				}

				if ( $ets_ultimatemember_discord_server_id ) {
					update_option( 'ets_ultimatemember_discord_server_id', $ets_ultimatemember_discord_server_id );
				}

				$message = 'Your settings are saved successfully.';
				if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
					
					
					$pre_location = $_SERVER['HTTP_REFERER'] . '&save_settings_msg=' . $message . '#skeletabsTab1';
					wp_safe_redirect( $pre_location );
				}
			}
		}
	}
	/**
	 * Save Role mappiing settings
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function ets_ultimatemember_discord_save_role_mapping() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		$ets_discord_roles = isset( $_POST['ets_ultimatemember_discord_role_mapping'] ) ? sanitize_textarea_field( trim( $_POST['ets_ultimatemember_discord_role_mapping'] ) ) : '';

		$_ets_ultimatemember_discord_default_role_id = isset( $_POST['ultimate-member_defaultRole'] ) ? sanitize_textarea_field( trim( $_POST['ultimate-member_defaultRole'] ) ) : '';

		

		$ets_discord_roles   = stripslashes( $ets_discord_roles );
		$save_mapping_status = update_option( 'ets_ultimatemember_discord_role_mapping', $ets_discord_roles );
		if ( isset( $_POST['ets_ultimatemember_discord_role_mappings_nonce'] ) && wp_verify_nonce( $_POST['ets_ultimatemember_discord_role_mappings_nonce'], 'ultimatemember_discord_role_mappings_nonce' ) ) {
			if ( ( $save_mapping_status || isset( $_POST['ets_ultimatemember_discord_role_mapping'] ) ) && ! isset( $_POST['flush'] ) ) {
				if ( $_ets_ultimatemember_discord_default_role_id ) {
					update_option( '_ets_ultimatemember_discord_default_role_id', $_ets_ultimatemember_discord_default_role_id );
				}


				$message = 'Your mappings are saved successfully.';
			}
			if ( isset( $_POST['flush'] ) ) {
				delete_option( 'ets_ultimatemember_discord_role_mapping' );
				delete_option( '_ets_ultimatemember_discord_default_role_id' );
				
				$message = 'Your settings flushed successfully.';
			}
			$pre_location = $_SERVER['HTTP_REFERER'] . '&save_settings_msg=' . $message . '#ets_ultimatemember_discord_role_mapping';
			wp_safe_redirect( $pre_location );
		}
	}


}
