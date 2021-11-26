<?php
/**
 * Class to handle discord API calls
 */
class Ultimate_Member_Discord_API {
	function __construct() {
		// Discord api callback
		add_action( 'init', array( $this, 'ets_ultimatemember_discord_api_callback' ) );
                
                // front ajax function to disconnect from discord
		add_action( 'wp_ajax_disconnect_from_discord', array( $this, 'ets_ultimatemember_discord_disconnect_from_discord' ) );

		// front ajax function to load discord roles
		add_action( 'wp_ajax_ets_ultimatemember_discord_load_discord_roles', array( $this, 'ets_ultimatemember_discord_load_discord_roles' ) );
                
                add_action( 'ets_ultimatemember_discord_as_handle_add_member_to_guild', array( $this, 'ets_ultimatemember_discord_as_handler_add_member_to_guild' ), 10, 3 );
                add_action( 'ets_ultimatemember_discord_as_send_dm', array( $this, 'ets_ultimatemember_discord_handler_send_dm' ), 10, 3 );
               

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
			$discod_server_roles_api = ETS_DISCORD_API_URL . 'guilds/' . $guild_id . '/roles';
			$guild_args              = array(
				'method'  => 'GET',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bot ' . $discord_bot_token,
				),
			);
			$guild_response          = wp_remote_post( $discod_server_roles_api, $guild_args );

			//ets_ultimatemember_discord_log_api_response( $user_id, $discod_server_roles_api, $guild_args, $guild_response );

			$response_arr = json_decode( wp_remote_retrieve_body( $guild_response ), true );

			if ( is_array( $response_arr ) && ! empty( $response_arr ) ) {
				if ( array_key_exists( 'code', $response_arr ) || array_key_exists( 'error', $response_arr ) ) {
					//PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
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
		$discord_token_api_url = ETS_DISCORD_API_URL . 'oauth2/token';
		if ( $refresh_token ) {
			$date              = new DateTime();
			$current_timestamp = $date->getTimestamp();
			if ( $current_timestamp > $token_expiry_time ) {
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
						'scope'         => ETS_DISCORD_OAUTH_SCOPES,
					),
				);
				$response = wp_remote_post( $discord_token_api_url, $args );
				ets_ultimatemember_discord_log_api_response( $user_id, $discord_token_api_url, $args, $response );
				if ( ets_ultimatemember_discord_check_api_errors( $response ) ) {
					$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
					Ultimate_Member_Discord_Add_On_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				}
			}
		} else {
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
					'scope'         => ETS_DISCORD_OAUTH_SCOPES,
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

		$discord_cuser_api_url = ETS_DISCORD_API_URL . 'users/@me';
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
				$discord_authorise_api_url = ETS_DISCORD_API_URL . 'oauth2/authorize?' . http_build_query( $params );

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
				$discord_authorise_api_url = ETS_DISCORD_API_URL . 'oauth2/authorize?' . http_build_query( $params );

				wp_redirect( $discord_authorise_api_url, 302, get_site_url() );
				exit;
			}
			if ( isset( $_GET['code'] ) && isset( $_GET['via'] ) ) {
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
										//$this->delete_discord_role( $user_id, $_ets_ultimatemember_discord_role_id );
									}
								}
								update_user_meta( $user_id, '_ets_ultimatemember_discord_user_id', $_ets_ultimatemember_discord_user_id );
								//$this->add_discord_member_in_guild( $_ets_ultimatemember_discord_user_id, $user_id, $access_token );
							}
						}
					}
				}
			}
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

		$guilds_memeber_api_url = ETS_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_ets_ultimatemember_discord_user_id;
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

		//ets_pmpro_discord_log_api_response( $user_id, $guilds_memeber_api_url, $guild_args, $guild_response );
		if ( ets_ultimatemember_discord_check_api_errors( $guild_response ) ) {

			$response_arr = json_decode( wp_remote_retrieve_body( $guild_response ), true );
			//PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
			// this should be catch by Action schedule failed action.
			throw new Exception( 'Failed in function ets_as_ultimatemember_add_member_to_guild' );
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
		if ( $ets_pmpro_discord_send_welcome_dm == true ) {
			as_schedule_single_action( ets_ultimatemember_discord_get_random_timestamp( ets_ultimatemember_discord_get_highest_last_attempt_timestamp() ), 'ets_pmpro_discord_as_send_dm', array( $user_id, $curr_level_id, 'welcome' ), 'ets-pmpro-discord' );
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
			as_schedule_single_action( ets_ultimatemember_discord_get_random_timestamp( ets_pmpro_discord_get_highest_last_attempt_timestamp() ), 'ets_pmpro_discord_as_schedule_member_put_role', array( $user_id, $role_id, $is_schedule ), ETS_DISCORD_AS_GROUP_NAME );
		} else {
			//$this->ets_pmpro_discord_as_handler_put_memberrole( $user_id, $role_id, $is_schedule );
		}
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

			//as_schedule_single_action( ets_ultimatemember_discord_get_random_timestamp( ets_pmpro_discord_get_highest_last_attempt_timestamp() ), 'ets_pmpro_discord_as_schedule_delete_member', array( $user_id, $is_schedule ), ETS_DISCORD_AS_GROUP_NAME );
		} else {
			if ( isset( $user_id ) ) {
				//$this->ets_pmpro_discord_as_handler_delete_member_from_guild( $user_id, $is_schedule );
			}
		}
	}
        
	/**
	 * Discord DM a member using bot.
	 *
	 * @param INT    $user_id
	 * @param STRING $type (warning|expired)
	 */
	public function ets_ultimatemember_discord_handler_send_dm( $user_id, $membership_level_id, $type = 'warning' ) {
            
            //
	}








}
new Ultimate_Member_Discord_API();
