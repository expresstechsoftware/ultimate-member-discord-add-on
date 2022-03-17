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
	 * Instance of Ultimate_Member_Discord_Add_On_Public class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Ultimate_Member_Discord_Add_On_Public
	 */
	private $ultimatemember_discord_public_instance; 
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $ultimatemember_discord_public_instance ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->ultimatemember_discord_public_instance = $ultimatemember_discord_public_instance;                                

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles( $hook ) {

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
		if ( $hook !== 'ultimate-member_page_ultimatemember-discord' )
                
                    return;
                
		wp_enqueue_style( $this->plugin_name . 'discord_tabs_css', plugin_dir_url( __FILE__ ) . 'css/skeletabs.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ultimate-member-discord-add-on-admin.css', array(), $this->version, 'all' );
				wp_enqueue_style( $this->plugin_name . 'fa-icon', '//use.fontawesome.com/releases/v5.5.0/css/all.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {

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
		if ( $hook !== 'ultimate-member_page_ultimatemember-discord' )
                
                    return;
            
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
			'admin_ajax'                       => admin_url( 'admin-ajax.php' ),
			'permissions_const'                => ULTIMATE_MEMBER_DISCORD_BOT_PERMISSIONS,
			'is_admin'                         => is_admin(),
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

					$pre_location = $_SERVER['HTTP_REFERER'] . '&save_settings_msg=' . $message . '#ets_ultimatemember_application_details';
					wp_safe_redirect( $pre_location );
				}
			}
		}
	}
	/**
	 * Save Role mapping settings
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

		$ets_ultimatemember_discord_default_role_id = isset( $_POST['ultimate-member_defaultRole'] ) ? sanitize_textarea_field( trim( $_POST['ultimate-member_defaultRole'] ) ) : '';

		$ets_discord_roles   = stripslashes( $ets_discord_roles );
		$save_mapping_status = update_option( 'ets_ultimatemember_discord_role_mapping', $ets_discord_roles );
		if ( isset( $_POST['ets_ultimatemember_discord_role_mappings_nonce'] ) && wp_verify_nonce( $_POST['ets_ultimatemember_discord_role_mappings_nonce'], 'ultimatemember_discord_role_mappings_nonce' ) ) {
			if ( ( $save_mapping_status || isset( $_POST['ets_ultimatemember_discord_role_mapping'] ) ) && ! isset( $_POST['flush'] ) ) {
				if ( $ets_ultimatemember_discord_default_role_id ) {
					update_option( 'ets_ultimatemember_discord_default_role_id', $ets_ultimatemember_discord_default_role_id );
				}

				$message = 'Your mappings are saved successfully.';
			}
			if ( isset( $_POST['flush'] ) ) {
				delete_option( 'ets_ultimatemember_discord_role_mapping' );
				delete_option( 'ets_ultimatemember_discord_default_role_id' );

				$message = 'Your settings flushed successfully.';
			}
			$pre_location = $_SERVER['HTTP_REFERER'] . '&save_settings_msg=' . $message . '#ets_ultimatemember_discord_role_mapping';
			wp_safe_redirect( $pre_location );
		}
	}

		/**
		 * Save advanced settings
		 *
		 * @param NONE
		 * @return NONE
		 */
	public function ets_ultimatemember_discord_save_advance_settings() {

		if ( ! current_user_can( 'administrator' ) || ! wp_verify_nonce( $_POST['ets_ultimatemember_discord_advance_settings_nonce'], 'ultimatemember_discord_advance_settings_nonce' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}

			$ets_ultimatemember_discord_send_welcome_dm = isset( $_POST['ets_ultimatemember_discord_send_welcome_dm'] ) ? sanitize_textarea_field( trim( $_POST['ets_ultimatemember_discord_send_welcome_dm'] ) ) : '';
			$ets_ultimatemember_discord_welcome_message = isset( $_POST['ets_ultimatemember_discord_welcome_message'] ) ? sanitize_textarea_field( trim( $_POST['ets_ultimatemember_discord_welcome_message'] ) ) : '';
			$retry_failed_api                           = isset( $_POST['retry_failed_api'] ) ? sanitize_textarea_field( trim( $_POST['retry_failed_api'] ) ) : '';
			$retry_api_count                            = isset( $_POST['ets_ultimatemember_retry_api_count'] ) ? sanitize_textarea_field( trim( $_POST['ets_ultimatemember_retry_api_count'] ) ) : '';
			$set_job_cnrc                               = isset( $_POST['set_job_cnrc'] ) ? sanitize_textarea_field( trim( $_POST['set_job_cnrc'] ) ) : '';
			$set_job_q_batch_size                       = isset( $_POST['set_job_q_batch_size'] ) ? sanitize_textarea_field( trim( $_POST['set_job_q_batch_size'] ) ) : '';
			$log_api_res                                = isset( $_POST['log_api_res'] ) ? sanitize_textarea_field( trim( $_POST['log_api_res'] ) ) : '';

		if ( isset( $_POST['ets_ultimatemember_discord_advance_settings_nonce'] ) && wp_verify_nonce( $_POST['ets_ultimatemember_discord_advance_settings_nonce'], 'ultimatemember_discord_advance_settings_nonce' ) ) {
			if ( isset( $_POST['adv_submit'] ) ) {

				if ( isset( $_POST['ets_ultimatemember_discord_send_welcome_dm'] ) ) {
					update_option( 'ets_ultimatemember_discord_send_welcome_dm', true );
				} else {
					update_option( 'ets_ultimatemember_discord_send_welcome_dm', false );
				}
				if ( isset( $_POST['ets_ultimatemember_discord_welcome_message'] ) && $_POST['ets_ultimatemember_discord_welcome_message'] != '' ) {
					update_option( 'ets_ultimatemember_discord_welcome_message', $ets_ultimatemember_discord_welcome_message );
				} else {
					update_option( 'ets_ultimatemember_discord_welcome_message', '' );
				}
				if ( isset( $_POST['retry_failed_api'] ) ) {
					update_option( 'ets_ultimatemember_retry_failed_api', true );
				} else {
					update_option( 'ets_ultimatemember_retry_failed_api', false );
				}
				if ( isset( $_POST['ets_ultimatemember_retry_api_count'] ) ) {
					if ( $retry_api_count < 1 ) {
						update_option( 'ets_ultimatemember_retry_api_count', 1 );
					} else {
						update_option( 'ets_ultimatemember_retry_api_count', $retry_api_count );
					}
				}
				if ( isset( $_POST['set_job_cnrc'] ) ) {
					if ( $set_job_cnrc < 1 ) {
						update_option( 'ets_ultimatemember_discord_job_queue_concurrency', 1 );
					} else {
						update_option( 'ets_ultimatemember_discord_job_queue_concurrency', $set_job_cnrc );
					}
				}
				if ( isset( $_POST['set_job_q_batch_size'] ) ) {
					if ( $set_job_q_batch_size < 1 ) {
						update_option( 'ets_ultimatemember_discord_job_queue_batch_size', 1 );
					} else {
						update_option( 'ets_ultimatemember_discord_job_queue_batch_size', $set_job_q_batch_size );
					}
				}
				if ( isset( $_POST['log_api_res'] ) ) {
					update_option( 'ets_ultimatemember_discord_log_api_response', true );
				} else {
					update_option( 'ets_ultimatemember_discord_log_api_response', false );
				}

				$message = 'Your settings are saved successfully.';
				if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
						$pre_location = $_SERVER['HTTP_REFERER'] . '&save_settings_msg=' . $message . '#ets_ultimatemember_discord_advanced';
						wp_safe_redirect( $pre_location );
				}
			}
		}

	}

	/**
	 * Add new member into discord guild
	 *
	 * @return OBJECT REST API response
	 */
	public function ets_ultimatemember_discord_load_discord_roles() {

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		// Check for nonce security
		if ( ! wp_verify_nonce( $_POST['ets_ultimatemember_discord_nonce'], 'ets-ultimatemember-ajax-nonce' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		$user_id = get_current_user_id();

		$guild_id          = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_server_id' ) ) );
		$discord_bot_token = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		if ( $guild_id && $discord_bot_token ) {
			$discod_server_roles_api = ETS_UM_DISCORD_API_URL . 'guilds/' . $guild_id . '/roles';
			$guild_args              = array(
				'method'  => 'GET',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bot ' . $discord_bot_token,
				),
			);
			$guild_response          = wp_remote_post( $discod_server_roles_api, $guild_args );

			ets_ultimatemember_discord_log_api_response( $user_id, $discod_server_roles_api, $guild_args, $guild_response );

			$response_arr = json_decode( wp_remote_retrieve_body( $guild_response ), true );

			if ( is_array( $response_arr ) && ! empty( $response_arr ) ) {
				if ( array_key_exists( 'code', $response_arr ) || array_key_exists( 'error', $response_arr ) ) {
									Ultimate_Member_Discord_Add_On_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				} else {
					$response_arr['previous_mapping'] = get_option( 'ets_ultimatemember_discord_role_mapping' );

					$discord_roles = array();
					foreach ( $response_arr as $key => $value ) {
						$isbot = false;
						if ( is_array( $value ) ) {
							if ( array_key_exists( 'tags', $value ) ) {
								if ( array_key_exists( 'bot_id', $value['tags'] ) ) {
									$isbot = true;
								}
							}
						}
						if ( $key != 'previous_mapping' && $isbot == false && isset( $value['name'] ) && $value['name'] != '@everyone' ) {
							$discord_roles[ $value['id'] ] = $value['name'];
						}
					}
					update_option( 'ets_ultimatemember_discord_all_roles', serialize( $discord_roles ) );
				}
			}
				return wp_send_json( $response_arr );
		}

				exit();

	}
	/**
	 * Update discord role when user profile is updated
	 *
	 * @param int $user_id
	 * @param WP_User $old_user_data
	 * @param array $userdata
	 * 
	 * @return NONE
	 */
	public function ets_ultimatemember_discord_update_user_profil( $user_id, $old_user_data, $userdata ) {

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}

		$access_token = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_access_token', true ) ) );                
		$ets_ultimatemember_discord_role_mapping = json_decode( get_option( 'ets_ultimatemember_discord_role_mapping' ), true );
		$old_discord_role = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_role_id', true ) ) );
		$default_role = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_default_role_id' ) ) ); 
		$user_default_role = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_default_role', true ) ) );                                        
		$new_user_role = substr ( $userdata['role'], 3 );
		$current_user_role = ets_ultimatemember_discord_get_user_roles( $user_id );
                
		if ( $access_token ){
                                    
			if ( isset( $new_user_role ) && !empty( $new_user_role ) ){
                        
				if ( is_array( $ets_ultimatemember_discord_role_mapping ) && array_key_exists( 'ultimate-member_level_id_' . $new_user_role, $ets_ultimatemember_discord_role_mapping ) ) {
					$new_discord_role = sanitize_text_field( trim( $ets_ultimatemember_discord_role_mapping[ 'ultimate-member_level_id_' . $new_user_role ] ) );
					if( $old_discord_role != $new_discord_role ){
						update_user_meta( $user_id, '_ets_ultimatemember_discord_role_id', $new_discord_role );                        
						$this->ultimatemember_discord_public_instance->put_discord_role_api( $user_id, $new_discord_role ); 
						$this->ultimatemember_discord_public_instance->delete_discord_role($user_id, $old_discord_role );
					}                       
				}
			}else{
				if( $current_user_role ){
					$this->ultimatemember_discord_public_instance->delete_discord_role($user_id, $current_user_role );
					delete_user_meta( $user_id, '_ets_ultimatemember_discord_role_id', $current_user_role );
			

				}
			}
                        
      			if ( $default_role && $default_role != 'none' ) {
				update_user_meta( $user_id, '_ets_ultimatemember_discord_default_role', $default_role );                        
				$this->ultimatemember_discord_public_instance->put_discord_role_api( $user_id, $default_role );
				if( $user_default_role ){
					$this->ultimatemember_discord_public_instance->delete_discord_role($user_id, $user_default_role );  
				}
                                
			}                 
		}	
	}

}
