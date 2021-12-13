<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.expresstechsoftwares.com
 * @since      1.0.0
 *
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/public
 * @author     ExpressTech Softwares Solutions Pvt Ltd <contact@expresstechsoftwares.com>
 */
class Ultimate_Member_Discord_Add_On_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ultimate-member-discord-add-on-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ultimate-member-discord-add-on-public.js', array( 'jquery' ), $this->version, false );
                
                $script_params = array(
			'admin_ajax'        => admin_url( 'admin-ajax.php' ),
			'permissions_const' => ULTIMATE_MEMBER_DISCORD_BOT_PERMISSIONS,
			'ets_ultimatemember_discord_nonce' => wp_create_nonce( 'ets-ultimatemember-ajax-nonce' ),
		);
		wp_localize_script( $this->plugin_name , 'etsUltimateMemberParams', $script_params );

	}
	/**
	 * For authorization process call discord API
	 *
	 * @param NONE
	 * @return OBJECT REST API response
	 */
	public function ets_ultimatemember_discord_api_callback() {
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			if ( isset( $_GET['action'] ) && $_GET['action'] == 'discord-login' ) {
				$params                    = array(
					'client_id'     => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_client_id' ) ) ),
					'redirect_uri'  => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_redirect_url' ) ) ),
					'response_type' => 'code',
					'scope'         => 'identify email connections guilds guilds.join messages.read',
				);
				$discord_authorise_api_url = ETS_UM_DISCORD_API_URL . 'oauth2/authorize?' . http_build_query( $params );

				wp_redirect( $discord_authorise_api_url, 302, get_site_url() );
				exit;
			}

			if ( isset( $_GET['action'] ) && $_GET['action'] == 'discord-connect-to-bot' ) {
				$params                    = array(
					'client_id'   => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_client_id' ) ) ),
					'permissions' => ULTIMATE_MEMBER_DISCORD_BOT_PERMISSIONS,
					'scope'       => 'bot',
					'guild_id'    => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_server_id' ) ) ),
				);
				$discord_authorise_api_url = ETS_UM_DISCORD_API_URL . 'oauth2/authorize?' . http_build_query( $params );

				wp_redirect( $discord_authorise_api_url, 302, get_site_url() );
				exit;
			}
			if ( isset( $_GET['code'] ) && isset( $_GET['via'] ) && $_GET['via'] == 'ultimate-discord' ) {
				$code     = sanitize_text_field( trim( $_GET['code'] ) );
				$response = $this->create_discord_auth_token( $code, $user_id );

				if ( ! empty( $response ) && ! is_wp_error( $response ) ) {
					$res_body              = json_decode( wp_remote_retrieve_body( $response ), true );
					$discord_exist_user_id = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_user_id', true ) ) );
					if ( is_array( $res_body ) ) {
                                           
						if ( array_key_exists( 'access_token', $res_body ) ) {
                                                    
							$access_token = sanitize_text_field( trim( $res_body['access_token'] ) );
							update_user_meta( $user_id, '_ets_ultimatemember_discord_access_token', $access_token );
							if ( array_key_exists( 'refresh_token', $res_body ) ) {
								$refresh_token = sanitize_text_field( trim( $res_body['refresh_token'] ) );
								update_user_meta( $user_id, '_ets_ultimatemember_discord_refresh_token', $refresh_token );
							}
							if ( array_key_exists( 'expires_in', $res_body ) ) {
								$expires_in = $res_body['expires_in'];
								$date       = new DateTime();
								$date->add( DateInterval::createFromDateString( '' . $expires_in . ' seconds' ) );
								$token_expiry_time = $date->getTimestamp();
								update_user_meta( $user_id, '_ets_ultimatemember_discord_expires_in', $token_expiry_time );
							}
							$user_body = $this->get_discord_current_user( $access_token );

							if ( is_array( $user_body ) && array_key_exists( 'discriminator', $user_body ) ) {
								$discord_user_number           = $user_body['discriminator'];
								$discord_user_name             = $user_body['username'];
								$discord_user_name_with_number = $discord_user_name . '#' . $discord_user_number;
								update_user_meta( $user_id, '_ets_ultimatemember_discord_username', $discord_user_name_with_number );
							}
							if ( is_array( $user_body ) && array_key_exists( 'id', $user_body ) ) {
								$_ets_ultimatemember_discord_user_id = sanitize_text_field( trim( $user_body['id'] ) );
								if ( $discord_exist_user_id == $_ets_ultimatemember_discord_user_id ) {
									$_ets_ultimatemember_discord_role_id = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_role_id', true ) ) );
									if ( ! empty( $_ets_ultimatemember_discord_role_id ) && $_ets_ultimatemember_discord_role_id != 'none' ) {
										$this->delete_discord_role( $user_id, $_ets_ultimatemember_discord_role_id );
									}
								}
								update_user_meta( $user_id, '_ets_ultimatemember_discord_user_id', $_ets_ultimatemember_discord_user_id );
								$this->add_discord_member_in_guild( $_ets_ultimatemember_discord_user_id, $user_id, $access_token );
							}
						}else{
                                                   
                                                }
					}else{
                                           
                                        }
				}
			}
		}
	}
        
	/**
	 * Add new member into discord guild
	 *
	 * @param INT    $_ets_ultimatemember_discord_user_id
	 * @param INT    $user_id
	 * @param STRING $access_token
	 * @return NONE
	 */
	public function add_discord_member_in_guild( $_ets_ultimatemember_discord_user_id, $user_id, $access_token ) {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}
		$curr_level_id = sanitize_text_field( trim( ets_ultimatemember_discord_get_current_level_id( $user_id ) ) );
		if ( $curr_level_id !== null ) {
			// It is possible that we may exhaust API rate limit while adding members to guild, so handling off the job to queue.
			as_schedule_single_action( ets_ultimatemember_discord_get_random_timestamp( ets_ultimatemember_discord_get_highest_last_attempt_timestamp() ), 'ets_ultimatemember_discord_as_handle_add_member_to_guild', array( $_ets_ultimatemember_discord_user_id, $user_id, $access_token ), ETSULTIMATE_MEMBER_DISCORD_AS_GROUP_NAME );
		}
	}
        
     
	/**
	 * Method to add new members to discord guild.
	 *
	 * @param INT    $_ets_ultimatemember_discord_user_id
	 * @param INT    $user_id
	 * @param STRING $access_token
	 * @return NONE
	 */
	public function ets_ultimatemember_discord_as_handler_add_member_to_guild( $_ets_ultimatemember_discord_user_id, $user_id, $access_token ) {
		// Since we using a queue to delay the API call, there may be a condition when a member is delete from DB. so put a check.
		if ( get_userdata( $user_id ) === false ) {
			return;
		}
		$guild_id                          = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_server_id' ) ) );
		$discord_bot_token                 = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$default_role                      = sanitize_text_field( trim( get_option( '_ets_ultimatemember_discord_default_role_id' ) ) );
		$ets_ultimatemember_discord_role_mapping    = json_decode( get_option( 'ets_ultimatemember_discord_role_mapping' ), true );
		$discord_role                      = '';
		$curr_level_id                     = sanitize_text_field( trim( ets_ultimatemember_discord_get_current_level_id( $user_id ) ) );
		$ets_ultimatemember_discord_send_welcome_dm = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_send_welcome_dm' ) ) );

		if ( is_array( $ets_ultimatemember_discord_role_mapping ) && array_key_exists( 'ultimate-member_level_id_' . $curr_level_id, $ets_ultimatemember_discord_role_mapping ) ) {
			$discord_role = sanitize_text_field( trim( $ets_ultimatemember_discord_role_mapping[ 'ultimate-member_level_id_' . $curr_level_id ] ) );
		} elseif ( $discord_role = '' && $default_role ) {
			$discord_role = $default_role;
		}

		$guilds_memeber_api_url = ETS_UM_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_ets_ultimatemember_discord_user_id;
		$guild_args             = array(
			'method'  => 'PUT',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bot ' . $discord_bot_token,
			),
			'body'    => json_encode(
				array(
					'access_token' => $access_token,
					'roles'        => array(
						$discord_role,
					),
				)
			),
		);
		$guild_response         = wp_remote_post( $guilds_memeber_api_url, $guild_args );

		ets_ultimatemember_discord_log_api_response( $user_id, $guilds_memeber_api_url, $guild_args, $guild_response );
		if ( ets_ultimatemember_discord_check_api_errors( $guild_response ) ) {

			$response_arr = json_decode( wp_remote_retrieve_body( $guild_response ), true );
			Ultimate_Member_Discord_Add_On_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
			// this should be catch by Action schedule failed action.
			throw new Exception( 'Failed in function ets_ultimatemember_discord_as_handler_add_member_to_guild' );
		}

		update_user_meta( $user_id, '_ets_ultimatemember_discord_role_id', $discord_role );
		if ( $discord_role && $discord_role != 'none' && isset( $user_id ) ) {
			$this->put_discord_role_api( $user_id, $discord_role );
		}

		if ( $default_role && $default_role != 'none' && isset( $user_id ) ) {
			$this->put_discord_role_api( $user_id, $default_role );
		}
		if ( empty( get_user_meta( $user_id, '_ets_ultimatemember_discord_join_date', true ) ) ) {
			update_user_meta( $user_id, '_ets_ultimatemember_discord_join_date', current_time( 'Y-m-d H:i:s' ) );
		}

		// Send welcome message.
		if ( $ets_ultimatemember_discord_send_welcome_dm == true ) {
			as_schedule_single_action( ets_ultimatemember_discord_get_random_timestamp( ets_ultimatemember_discord_get_highest_last_attempt_timestamp() ), 'ets_ultimatemember_discord_as_send_dm', array( $user_id, $curr_level_id, 'welcome' ), 'ultimate-member-discord-add-on' );
		}
	}
        
        
	/**
	 * API call to change discord user role
	 *
	 * @param INT  $user_id
	 * @param INT  $role_id
	 * @param BOOL $is_schedule
	 * @return object API response
	 */
	public function put_discord_role_api( $user_id, $role_id, $is_schedule = true ) {
		if ( $is_schedule ) {
			as_schedule_single_action( ets_ultimatemember_discord_get_random_timestamp( ets_ultimatemember_discord_get_highest_last_attempt_timestamp() ), 'ets_ultimatemember_discord_as_schedule_member_put_role', array( $user_id, $role_id, $is_schedule ), ETS_UM_DISCORD_AS_GROUP_NAME );
		} else {
			$this->ets_ultimatemember_discord_as_handler_put_memberrole( $user_id, $role_id, $is_schedule );
		}
	} 
        
        
	/**
	 * Action Schedule handler for mmeber change role discord.
	 *
	 * @param INT  $user_id
	 * @param INT  $role_id
	 * @param BOOL $is_schedule
	 * @return object API response
	 */
	public function ets_ultimatemember_discord_as_handler_put_memberrole( $user_id, $role_id, $is_schedule ) {
		$access_token                = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_access_token', true ) ) );
		$guild_id                    = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_guild_id' ) ) );
		$_ets_ultimatemember_discord_user_id  = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_user_id', true ) ) );
		$discord_bot_token           = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$discord_change_role_api_url = ETS_UM_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_ets_ultimatemember_discord_user_id . '/roles/' . $role_id;

		if ( $access_token && $_ets_ultimatemember_discord_user_id ) {
			$param = array(
				'method'  => 'PUT',
				'headers' => array(
					'Content-Type'   => 'application/json',
					'Authorization'  => 'Bot ' . $discord_bot_token,
					'Content-Length' => 0,
				),
			);

			$response = wp_remote_get( $discord_change_role_api_url, $param );

			ets_ultimatemember_discord_log_api_response( $user_id, $discord_change_role_api_url, $param, $response );
			if ( ets_ultimatemember_discord_check_api_errors( $response ) ) {
				$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
				Ultimate_Member_Discord_Add_On_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				if ( $is_schedule ) {
					// this exception should be catch by action scheduler.
					throw new Exception( 'Failed in function ets_ultimatemember_discord_as_handler_put_memberrole' );
				}
			}
		}
	}
        
	/**
	 * Schedule delete discord role for a member
	 *
	 * @param INT  $user_id
	 * @param INT  $ets_role_id
	 * @param BOOL $is_schedule
	 * @return OBJECT API response
	 */
	public function delete_discord_role( $user_id, $ets_role_id, $is_schedule = true ) {
		if ( $is_schedule ) {
			as_schedule_single_action( ets_ultimatemember_discord_get_random_timestamp( ets_ultimatemember_discord_get_highest_last_attempt_timestamp() ), 'ets_ultimatemember_discord_as_schedule_delete_role', array( $user_id, $ets_role_id, $is_schedule ), ETS_UM_DISCORD_AS_GROUP_NAME );
		} else {
			$this->ets_ultimatemember_discord_as_handler_delete_memberrole( $user_id, $ets_role_id, $is_schedule );
		}
	}
        
	/**
	 * Action Schedule handler to process delete role of a member.
	 *
	 * @param INT  $user_id
	 * @param INT  $ets_role_id
	 * @param BOOL $is_schedule
	 * @return OBJECT API response
	 */
	public function ets_ultimatemember_discord_as_handler_delete_memberrole( $user_id, $ets_role_id, $is_schedule = true ) {

		$guild_id                    = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_server_id' ) ) );
		$_ets_ultimatemember_discord_user_id  = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_user_id', true ) ) );
		$discord_bot_token           = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$discord_delete_role_api_url = ETS_UM_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_ets_ultimatemember_discord_user_id . '/roles/' . $ets_role_id;
		if ( $_ets_ultimatemember_discord_user_id ) {
			$param = array(
				'method'  => 'DELETE',
				'headers' => array(
					'Content-Type'   => 'application/json',
					'Authorization'  => 'Bot ' . $discord_bot_token,
					'Content-Length' => 0,
				),
			);

			$response = wp_remote_request( $discord_delete_role_api_url, $param );
			ets_ultimatemember_discord_log_api_response( $user_id, $discord_delete_role_api_url, $param, $response );
			if ( ets_ultimatemember_discord_check_api_errors( $response ) ) {
				$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
				Ultimate_Member_Discord_Add_On_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				if ( $is_schedule ) {
					// this exception should be catch by action scheduler.
					throw new Exception( 'Failed in function ets_ultimatemember_discord_as_handler_delete_memberrole' );
				}
			}
			return $response;
		}
	}
        
	/**
	 * Disconnect user from discord
	 *
	 * @param NONE
	 * @return OBJECT JSON response
	 */
	public function ets_ultimatemember_discord_disconnect_from_discord() {
            
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}

		// Check for nonce security
		if ( ! wp_verify_nonce( $_POST['ets_ultimatemember_discord_nonce'], 'ets-ultimatemember-ajax-nonce' ) ) {
				wp_send_json_error( 'You do not have sufficient rights', 403 );
				exit();
		}
		$user_id = sanitize_text_field( trim( $_POST['user_id'] ) );
		if ( $user_id ) {
			$this->delete_member_from_guild( $user_id, false );
			delete_user_meta( $user_id, '_ets_ultimatemember_discord_access_token' );
		}
		$event_res = array(
			'status'  => 1,
			'message' => 'Successfully disconnected',
		);
		wp_send_json( $event_res );
	}

	/**
	 * Schedule delete existing user from guild
	 *
	 * @param INT  $user_id
	 * @param BOOL $is_schedule
	 * @param NONE
	 */
	public function delete_member_from_guild( $user_id, $is_schedule = true ) {
		if ( $is_schedule && isset( $user_id ) ) {

			as_schedule_single_action( ets_ultimatemember_discord_get_random_timestamp( ets_ultimatemember_discord_get_highest_last_attempt_timestamp() ), 'ets_ultimatemember_discord_as_schedule_delete_member', array( $user_id, $is_schedule ), ETS_UM_DISCORD_AS_GROUP_NAME );
		} else {
			if ( isset( $user_id ) ) {
				$this->ets_ultimatemember_discord_as_handler_delete_member_from_guild( $user_id, $is_schedule );
			}
		}
	}
        
	/**
	 * AS Handling member delete from huild
	 *
	 * @param INT  $user_id
	 * @param BOOL $is_schedule
	 * @return OBJECT API response
	 */
	public function ets_ultimatemember_discord_as_handler_delete_member_from_guild( $user_id, $is_schedule ) {
		$guild_id                      = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_guild_id' ) ) );
		$discord_bot_token             = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$_ets_ultimatemember_discord_user_id    = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_user_id', true ) ) );
		$guilds_delete_memeber_api_url = ETS_UM_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_ets_ultimatemember_discord_user_id;
		$guild_args                    = array(
			'method'  => 'DELETE',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bot ' . $discord_bot_token,
			),
		);
		$guild_response                = wp_remote_post( $guilds_delete_memeber_api_url, $guild_args );

		ets_ultimatemember_discord_log_api_response( $user_id, $guilds_delete_memeber_api_url, $guild_args, $guild_response );
		if ( ets_ultimatemember_discord_check_api_errors( $guild_response ) ) {
			$response_arr = json_decode( wp_remote_retrieve_body( $guild_response ), true );
			Ultimate_Member_Discord_Add_On_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
			if ( $is_schedule ) {
				// this exception should be catch by action scheduler.
				throw new Exception( 'Failed in function ets_ultimatemember_discord_as_handler_delete_member_from_guild' );
			}
		}

		/*Delete all usermeta related to discord connection*/
		delete_user_meta( $user_id, '_ets_ultimatemember_discord_user_id' );
		delete_user_meta( $user_id, '_ets_ultimatemember_discord_access_token' );
		delete_user_meta( $user_id, '_ets_ultimatemember_discord_refresh_token' );
		delete_user_meta( $user_id, '_ets_ultimatemember_discord_role_id' );
		delete_user_meta( $user_id, '_ets_ultimatemember_discord_default_role_id' );
		delete_user_meta( $user_id, '_ets_ultimatemember_discord_username' );
		delete_user_meta( $user_id, '_ets_ultimatemember_discord_expires_in' );

	}
        
        
        
	/**
	 * Create authentication token for discord API
	 *
	 * @param STRING $code
	 * @param INT    $user_id
	 * @return OBJECT API response
	 */
	public function create_discord_auth_token( $code, $user_id ) {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}
		// stop users who having the direct URL of discord Oauth.
		$curr_level_id     = sanitize_text_field( trim( ets_ultimatemember_discord_get_current_level_id( $user_id ) ) );
		if ( $curr_level_id == null ) {
			return;
		}
		$response              = '';
		$refresh_token         = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_refresh_token', true ) ) );
		$token_expiry_time     = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_expires_in', true ) ) );
		$discord_token_api_url = ETS_UM_DISCORD_API_URL . 'oauth2/token';
		if ( $refresh_token ) {
			$date              = new DateTime();
			$current_timestamp = $date->getTimestamp();
                        update_option('refresh_1', '1');
			if ( $current_timestamp > $token_expiry_time ) {
                            update_option('time', '1');
				$args     = array(
					'method'  => 'POST',
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
					),
					'body'    => array(
						'client_id'     => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_client_id' ) ) ),
						'client_secret' => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_client_secret' ) ) ),
						'grant_type'    => 'refresh_token',
						'refresh_token' => $refresh_token,
						'redirect_uri'  => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_redirect_url' ) ) ),
						'scope'         => ETS_UM_DISCORD_OAUTH_SCOPES,
					),
				);
				$response = wp_remote_post( $discord_token_api_url, $args );
                                
				ets_ultimatemember_discord_log_api_response( $user_id, $discord_token_api_url, $args, $response );
				if ( ets_ultimatemember_discord_check_api_errors( $response ) ) {
					$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
					Ultimate_Member_Discord_Add_On_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				}
			}else{
                            update_option('time', '2');
                        }
		} else {
                    update_option('refresh_2', '2');
			$args     = array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'client_id'     => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_client_id' ) ) ),
					'client_secret' => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_client_secret' ) ) ),
					'grant_type'    => 'authorization_code',
					'code'          => $code,
					'redirect_uri'  => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_redirect_url' ) ) ),
					'scope'         => ETS_UM_DISCORD_OAUTH_SCOPES,
				),
			);
			$response = wp_remote_post( $discord_token_api_url, $args );
			ets_ultimatemember_discord_log_api_response( $user_id, $discord_token_api_url, $args, $response );
			if ( ets_ultimatemember_discord_check_api_errors( $response ) ) {
				$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
				Ultimate_Member_Discord_Add_On_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
			}
		}
		return $response;
	}
        
 	/**
	 * Get Discord user details from API
	 *
	 * @param STRING $access_token
	 * @return OBJECT REST API response
	 */
	public function get_discord_current_user( $access_token ) {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}
		$user_id = get_current_user_id();

		$discord_cuser_api_url = ETS_UM_DISCORD_API_URL . 'users/@me';
		$param                 = array(
			'headers' => array(
				'Content-Type'  => 'application/x-www-form-urlencoded',
				'Authorization' => 'Bearer ' . $access_token,
			),
		);
		$user_response         = wp_remote_get( $discord_cuser_api_url, $param );
		ets_ultimatemember_discord_log_api_response( $user_id, $discord_cuser_api_url, $param, $user_response );

		$response_arr = json_decode( wp_remote_retrieve_body( $user_response ), true );
		Ultimate_Member_Discord_Add_On_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
		$user_body = json_decode( wp_remote_retrieve_body( $user_response ), true );
		return $user_body;

	}
        
        
	/**
	 * Discord DM a member using bot.
	 *
	 * @param INT    $user_id
	 * @param STRING $type (warning|expired)
	 */
	public function ets_ultimatemember_discord_handler_send_dm( $user_id, $um_role_id, $type = 'warning' ) {
		$discord_user_id                              = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_user_id', true ) ) );
		$discord_bot_token                            = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$ets_ultimatemember_discord_expiration_warning_message = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_expiration_warning_message' ) ) );
		$ets_ultimatemember_discord_expiration_expired_message = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_expiration_expired_message' ) ) );
		$ets_ultimatemember_discord_welcome_message            = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_welcome_message' ) ) );
		$ets_ultimatemember_discord_cancel_message             = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_cancel_message' ) ) );
		// Check if DM channel is already created for the user.
		$user_dm = get_user_meta( $user_id, '_ets_ultimatemember_discord_dm_channel', true );

		if ( ! isset( $user_dm['id'] ) || $user_dm === false || empty( $user_dm ) ) {
			$this->ets_ultimatemember_discord_create_member_dm_channel( $user_id );
			$user_dm       = get_user_meta( $user_id, '_ets_ultimatemember_discord_dm_channel', true );
			$dm_channel_id = $user_dm['id'];
		} else {
			$dm_channel_id = $user_dm['id'];
		}

		if ( $type == 'warning' ) {
			update_user_meta( $user_id, '_ets_ultimatemember_discord_expitration_warning_dm_for_' . $um_role_id, true );
			$message = ets_ultimatemember_discord_get_formatted_dm( $user_id, $um_role_id, $ets_ultimatemember_discord_expiration_warning_message );
		}
		if ( $type == 'expired' ) {
			update_user_meta( $user_id, '_ets_ultimatemember_discord_expired_dm_for_' . $um_role_id, true );
			$message = ets_ultimatemember_discord_get_formatted_dm( $user_id, $um_role_id, $ets_ultimatemember_discord_expiration_expired_message );
		}
		if ( $type == 'welcome' ) {
			update_user_meta( $user_id, '_ets_ultimatemember_discord_welcome_dm_for_' . $um_role_id, true );
			$message = ets_ultimatemember_discord_get_formatted_dm( $user_id, $um_role_id, $ets_ultimatemember_discord_welcome_message );
		}

		if ( $type == 'cancel' ) {
			update_user_meta( $user_id, '_ets_ultimatemember_discord_cancel_dm_for_' . $um_role_id, true );
			$message = ets_ultimatemember_discord_get_formatted_dm( $user_id, $um_role_id, $ets_ultimatemember_discord_cancel_message );
		}

		$creat_dm_url = ETS_UM_DISCORD_API_URL . '/channels/' . $dm_channel_id . '/messages';
		$dm_args      = array(
			'method'  => 'POST',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bot ' . $discord_bot_token,
			),
			'body'    => json_encode(
				array(
					'content' => sanitize_text_field( trim( wp_unslash( $message ) ) ),
				)
			),
		);
		$dm_response  = wp_remote_post( $creat_dm_url, $dm_args );
		ets_ultimatemember_discord_log_api_response( $user_id, $creat_dm_url, $dm_args, $dm_response );
		$dm_response_body = json_decode( wp_remote_retrieve_body( $dm_response ), true );
		if ( ets_ultimatemember_discord_check_api_errors( $dm_response ) ) {
                    Ultimate_Member_Discord_Add_On_Logs::write_api_response_logs( $dm_response_body, $user_id, debug_backtrace()[0] );
			// this should be catch by Action schedule failed action.
			throw new Exception( 'Failed in function ets_ultimatemember_discord_handler_send_dm' );
		}
	}
        
	/**
	 * Create DM channel for a give user_id
	 *
	 * @param INT $user_id
	 * @return MIXED
	 */
	public function ets_ultimatemember_discord_create_member_dm_channel( $user_id ) {
		$discord_user_id       = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_user_id', true ) ) );
		$discord_bot_token     = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$create_channel_dm_url = ETS_UM_DISCORD_API_URL . '/users/@me/channels';
		$dm_channel_args       = array(
			'method'  => 'POST',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bot ' . $discord_bot_token,
			),
			'body'    => json_encode(
				array(
					'recipient_id' => $discord_user_id,
				)
			),
		);

		$created_dm_response = wp_remote_post( $create_channel_dm_url, $dm_channel_args );
		ets_ultimatemember_discord_log_api_response( $user_id, $create_channel_dm_url, $dm_channel_args, $created_dm_response );
		$response_arr = json_decode( wp_remote_retrieve_body( $created_dm_response ), true );
                
		if ( is_array( $response_arr ) && ! empty( $response_arr ) ) {
			// check if there is error in create dm response
			if ( array_key_exists( 'code', $response_arr ) || array_key_exists( 'error', $response_arr ) ) {
                            Ultimate_Member_Discord_Add_On_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				if ( ets_ultimatemember_discord_check_api_errors( $created_dm_response ) ) {
					// this should be catch by Action schedule failed action.
					throw new Exception( 'Failed in function ets_ultimatemember_discord_create_member_dm_channel' );
				}
			} else {
				update_user_meta( $user_id, '_ets_ultimatemember_discord_dm_channel', $response_arr );
			}
		}
		return $response_arr;
	}

}
