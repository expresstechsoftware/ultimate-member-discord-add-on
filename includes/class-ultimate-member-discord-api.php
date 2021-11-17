<?php
/**
 * Class to handle discord API calls
 */
class Ultimate_Member_Discord_API {
	function __construct() {
		// Discord api callback
		add_action( 'init', array( $this, 'ets_ultimatemember_discord_discord_api_callback' ) );

		// front ajax function to disconnect from discord
		add_action( 'wp_ajax_ets_ultimatemember_discord_load_discord_roles', array( $this, 'ets_ultimatemember_discord_load_discord_roles' ) );
               

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
		// We must check IF NONE members is set to NO and user having no active membership.
		$allow_none_member = sanitize_text_field( trim( get_option( 'ets_ultimatemember_allow_none_member' ) ) );
		$curr_level_id     = sanitize_text_field( trim( ets_ultimatemember_discord_get_current_level_id( $user_id ) ) );
		if ( $curr_level_id == null && $allow_none_member == 'no' ) {
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
				//ets_ultimatemember_discord_log_api_response( $user_id, $discord_token_api_url, $args, $response );
				if ( ets_ultimatemember_discord_check_api_errors( $response ) ) {
					$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
					//PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
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
			//ets_ultimatemember_discord_log_api_response( $user_id, $discord_token_api_url, $args, $response );
			if ( ets_ultimatemember_discord_check_api_errors( $response ) ) {
				$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
				//PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
			}
		}
		return $response;
	}

	/**
	 * For authorization process call discord API
	 *
	 * @param NONE
	 * @return OBJECT REST API response
	 */
	public function ets_ultimatemember_discord_discord_api_callback() {
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
							//$user_body = $this->get_discord_current_user( $access_token );

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








}
new Ultimate_Member_Discord_API();
