<?php
/**
 * Class to handle discord API calls
 */
class Ultimate_Member_Discord_API {
	function __construct() {
		// Discord api callback
		add_action( 'init', array( $this, 'ets_ultimatemember_discord_discord_api_callback' ) );

		// front ajax function to disconnect from discord
		//add_action( 'wp_ajax_disconnect_from_discord', array( $this, 'ets_pmpro_discord_disconnect_from_discord' ) );

		// front ajax function to disconnect from discord
		add_action( 'wp_ajax_ets_ultimatemember_discord_load_discord_roles', array( $this, 'ets_ultimatemember_discord_load_discord_roles' ) );
               

//		add_action( 'pmpro_after_change_membership_level', array( $this, 'ets_pmpro_discord_change_discord_role_from_pmpro' ), 10, 4 );
//
//		add_action( 'ets_pmpro_discord_as_handle_pmpro_expiry', array( $this, 'ets_pmpro_discord_as_handler_pmpro_expiry' ), 10, 2 );
//
//		add_action( 'ets_pmpro_discord_as_handle_pmpro_cancel', array( $this, 'ets_pmpro_discord_as_handler_pmpro_cancel' ), 10, 3 );
//
//		add_action( 'ets_pmpro_discord_as_handle_add_member_to_guild', array( $this, 'ets_pmpro_discord_as_handler_add_member_to_guild' ), 10, 3 );
//
//		add_action( 'ets_pmpro_discord_as_schedule_delete_member', array( $this, 'ets_pmpro_discord_as_handler_delete_member_from_guild' ), 10, 2 );
//
//		add_action( 'ets_pmpro_discord_as_schedule_member_put_role', array( $this, 'ets_pmpro_discord_as_handler_put_memberrole' ), 10, 3 );
//
//		add_action( 'ets_pmpro_discord_as_schedule_delete_role', array( $this, 'ets_pmpro_discord_as_handler_delete_memberrole' ), 10, 3 );
//
//		add_action( 'wp_ajax_ets_pmpro_discord_member_table_run_api', array( $this, 'ets_pmpro_discord_member_table_run_api' ) );
//
//		add_action( 'pmpro_stripe_subscription_deleted', array( $this, 'ets_pmpro_discord_stripe_subscription_deleted' ), 10, 1 );
//
//		add_action( 'pmpro_subscription_payment_failed', array( $this, 'ets_pmpro_discord_subscription_payment_failed' ), 10, 1 );
//
//		add_action( 'action_scheduler_failed_execution', array( $this, 'ets_pmpro_discord_reschedule_failed_action' ), 10, 3 );
//
//		add_action( 'ets_pmpro_discord_as_send_dm', array( $this, 'ets_pmpro_discord_handler_send_dm' ), 10, 3 );
//
//		add_action( 'ets_pmrpo_discord_schedule_expiration_warnings', array( $this, 'ets_pmpro_discord_send_expiration_warning_DM' ) );

	}

	/**
	 * Send expiration warning DM to discord members.
	 *
	 * @param NONE
	 * @param NONE
	 */

	public function ets_pmpro_discord_send_expiration_warning_DM() {
		global $wpdb;
		// clean up errors in the memberships_users table that could cause problems.
		pmpro_cleanup_memberships_users_table();
		$today                                        = date( 'Y-m-d 00:00:00', current_time( 'timestamp' ) );
		$pmpro_email_days_before_expiration           = apply_filters( 'pmpro_email_days_before_expiration', 7 );
		$ets_pmpro_discord_send_expiration_warning_dm = sanitize_text_field( trim( get_option( 'ets_pmpro_discord_send_expiration_warning_dm' ) ) );

		if ( $ets_pmpro_discord_send_expiration_warning_dm == false ) {
			return;
		}
		// Configure the interval to select records from
		$interval_start = $today;
		$interval_end   = date( 'Y-m-d 00:00:00', strtotime( "{$today} +{$pmpro_email_days_before_expiration} days", current_time( 'timestamp' ) ) );

		// look for memberships that are going to expire within one week (but we haven't emailed them within a week)
		$sqlQuery      = $wpdb->prepare(
			"SELECT DISTINCT 
			mu.user_id, mu.membership_id, mu.startdate, mu.enddate 
			FROM {$wpdb->pmpro_memberships_users} AS mu 
			WHERE mu.enddate 
			BETWEEN %s AND %s
			AND ( mu.status = 'active' )
			AND ( mu.membership_id <> 0 OR mu.membership_id <> NULL ) 
			ORDER BY mu.enddate",
			$today,
			$interval_end
		);
		$expiring_soon = $wpdb->get_results( $sqlQuery );

		if ( ! empty( $expiring_soon ) ) {
			// foreach members and send DM
			foreach ( $expiring_soon as $key => $user_obj ) {
				// check if the message is not already sent
				$membership_level = pmpro_getMembershipLevelForUser( $user_obj->user_id );
				$already_sent     = get_user_meta( $user_obj->user_id, '_ets_pmpro_discord_expitration_warning_dm_for_' . $membership_level->ID, true );
				$access_token     = get_user_meta( $user_id, '_ets_pmpro_discord_access_token', true );
				if ( ! empty( $access_token ) && $membership_level !== false && $already_sent != 1 ) {
					as_schedule_single_action( ets_pmpro_discord_get_random_timestamp( ets_pmpro_discord_get_highest_last_attempt_timestamp() ), 'ets_pmpro_discord_as_send_dm', array( $user_obj->user_id, $membership_level->ID ), 'ets-pmpro-discord' );
				}
			}
		}

	}

	/**
	 * Discord DM a member using bot.
	 *
	 * @param INT    $user_id
	 * @param STRING $type (warning|expired)
	 */
	public function ets_pmpro_discord_handler_send_dm( $user_id, $membership_level_id, $type = 'warning' ) {
		$discord_user_id                              = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_pmpro_discord_user_id', true ) ) );
		$discord_bot_token                            = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$ets_pmpro_discord_expiration_warning_message = sanitize_text_field( trim( get_option( 'ets_pmpro_discord_expiration_warning_message' ) ) );
		$ets_pmpro_discord_expiration_expired_message = sanitize_text_field( trim( get_option( 'ets_pmpro_discord_expiration_expired_message' ) ) );
		$ets_pmpro_discord_welcome_message            = sanitize_text_field( trim( get_option( 'ets_pmpro_discord_welcome_message' ) ) );
		$ets_pmpro_discord_cancel_message             = sanitize_text_field( trim( get_option( 'ets_pmpro_discord_cancel_message' ) ) );
		// Check if DM channel is already created for the user.
		$user_dm = get_user_meta( $user_id, '_ets_pmpro_discord_dm_channel', true );

		if ( ! isset( $user_dm['id'] ) || $user_dm === false || empty( $user_dm ) ) {
			$this->ets_pmpro_discord_create_member_dm_channel( $user_id );
			$user_dm       = get_user_meta( $user_id, '_ets_pmpro_discord_dm_channel', true );
			$dm_channel_id = $user_dm['id'];
		} else {
			$dm_channel_id = $user_dm['id'];
		}

		if ( $type == 'warning' ) {
			update_user_meta( $user_id, '_ets_pmpro_discord_expitration_warning_dm_for_' . $membership_level_id, true );
			$message = ets_pmpro_discord_get_formatted_dm( $user_id, $membership_level_id, $ets_pmpro_discord_expiration_warning_message );
		}
		if ( $type == 'expired' ) {
			update_user_meta( $user_id, '_ets_pmpro_discord_expired_dm_for_' . $membership_level_id, true );
			$message = ets_pmpro_discord_get_formatted_dm( $user_id, $membership_level_id, $ets_pmpro_discord_expiration_expired_message );
		}
		if ( $type == 'welcome' ) {
			update_user_meta( $user_id, '_ets_pmpro_discord_welcome_dm_for_' . $membership_level_id, true );
			$message = ets_pmpro_discord_get_formatted_dm( $user_id, $membership_level_id, $ets_pmpro_discord_welcome_message );
		}

		if ( $type == 'cancel' ) {
			update_user_meta( $user_id, '_ets_pmpro_discord_cancel_dm_for_' . $membership_level_id, true );
			$message = ets_pmpro_discord_get_formatted_dm( $user_id, $membership_level_id, $ets_pmpro_discord_cancel_message );
		}

		$creat_dm_url = ETS_DISCORD_API_URL . '/channels/' . $dm_channel_id . '/messages';
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
		ets_pmpro_discord_log_api_response( $user_id, $creat_dm_url, $dm_args, $dm_response );
		$dm_response_body = json_decode( wp_remote_retrieve_body( $dm_response ), true );
		if ( ets_pmpro_discord_check_api_errors( $dm_response ) ) {
			PMPro_Discord_Logs::write_api_response_logs( $dm_response_body, $user_id, debug_backtrace()[0] );
			// this should be catch by Action schedule failed action.
			throw new Exception( 'Failed in function ets_pmpro_discord_send_dm' );
		}
	}

	/**
	 * Get discord channel by channel ID
	 *
	 * @param INT $user_id
	 * @param INT $channel_id
	 */
	private function ets_pmpro_discord_get_dm_channel( $user_id, $channel_id ) {
		$discord_bot_token       = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$get_channel_url         = ETS_DISCORD_API_URL . '/channels/' . $channel_id;
		$get_channel_args        = array(
			'method'  => 'GET',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bot ' . $discord_bot_token,
			),
		);
		$response_arr            = wp_remote_get( $get_channel_url, $get_channel_args );
		$getchannel_response_arr = json_decode( wp_remote_retrieve_body( $response_arr ), true );
		return $getchannel_response_arr;
	}


	/**
	 * Create DM channel for a give user_id
	 *
	 * @param INT $user_id
	 * @return MIXED
	 */
	public function ets_pmpro_discord_create_member_dm_channel( $user_id ) {
		$discord_user_id       = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_pmpro_discord_user_id', true ) ) );
		$discord_bot_token     = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$create_channel_dm_url = ETS_DISCORD_API_URL . '/users/@me/channels';
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
		ets_pmpro_discord_log_api_response( $user_id, $create_channel_dm_url, $dm_channel_args, $created_dm_response );
		$response_arr = json_decode( wp_remote_retrieve_body( $created_dm_response ), true );

		if ( is_array( $response_arr ) && ! empty( $response_arr ) ) {
			// check if there is error in create dm response
			if ( array_key_exists( 'code', $response_arr ) || array_key_exists( 'error', $response_arr ) ) {
				PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				if ( ets_pmpro_discord_check_api_errors( $created_dm_response ) ) {
					// this should be catch by Action schedule failed action.
					throw new Exception( 'Failed in function ets_pmpro_discord_create_member_dm_channel' );
				}
			} else {
				update_user_meta( $user_id, '_ets_pmpro_discord_dm_channel', $response_arr );
			}
		}
		return $response_arr;
	}

	/**
	 * Check if the failed action is the PMPRO Discord Add-on and re-schedule it
	 *
	 * @param INT            $action_id
	 * @param OBJECT         $e
	 * @param OBJECT context
	 * @return NONE
	 */
	public function ets_pmpro_discord_reschedule_failed_action( $action_id, $e, $context ) {
		// First check if the action is for PMPRO discord.
		$action_data = ets_pmpro_discord_as_get_action_data( $action_id );
		if ( $action_data !== false ) {
			$hook              = $action_data['hook'];
			$args              = json_decode( $action_data['args'] );
			$retry_failed_api  = sanitize_text_field( trim( get_option( 'ets_pmpro_retry_failed_api' ) ) );
			$hook_failed_count = ets_pmpro_discord_count_of_hooks_failures( $hook );
			$retry_api_count   = absint( sanitize_text_field( trim( get_option( 'ets_pmpro_retry_api_count' ) ) ) );
			if ( $hook_failed_count < $retry_api_count && $retry_failed_api == true && $action_data['as_group'] == ETS_DISCORD_AS_GROUP_NAME && $action_data['status'] = 'failed' ) {
				as_schedule_single_action( ets_pmpro_discord_get_random_timestamp( ets_pmpro_discord_get_highest_last_attempt_timestamp() ), $hook, array_values( $args ), 'ets-pmpro-discord' );
			}
		}
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
		$allow_none_member = sanitize_text_field( trim( get_option( 'ets_pmpro_allow_none_member' ) ) );
		$curr_level_id     = sanitize_text_field( trim( ets_pmpro_discord_get_current_level_id( $user_id ) ) );
		if ( $curr_level_id == null && $allow_none_member == 'no' ) {
			return;
		}
		$response              = '';
		$refresh_token         = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_pmpro_discord_refresh_token', true ) ) );
		$token_expiry_time     = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_pmpro_discord_expires_in', true ) ) );
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
						'client_secret' => sanitize_text_field( trim( get_option( 'ets_pmpro_discord_client_secret' ) ) ),
						'grant_type'    => 'refresh_token',
						'refresh_token' => $refresh_token,
						'redirect_uri'  => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_redirect_url' ) ) ),
						'scope'         => ETS_DISCORD_OAUTH_SCOPES,
					),
				);
				$response = wp_remote_post( $discord_token_api_url, $args );
				ets_pmpro_discord_log_api_response( $user_id, $discord_token_api_url, $args, $response );
				if ( ets_pmpro_discord_check_api_errors( $response ) ) {
					$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
					PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
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
					'client_secret' => sanitize_text_field( trim( get_option( 'ets_pmpro_discord_client_secret' ) ) ),
					'grant_type'    => 'authorization_code',
					'code'          => $code,
					'redirect_uri'  => sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_redirect_url' ) ) ),
					'scope'         => ETS_DISCORD_OAUTH_SCOPES,
				),
			);
			$response = wp_remote_post( $discord_token_api_url, $args );
			ets_pmpro_discord_log_api_response( $user_id, $discord_token_api_url, $args, $response );
			if ( ets_pmpro_discord_check_api_errors( $response ) ) {
				$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
				PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
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
		ets_pmpro_discord_log_api_response( $user_id, $discord_cuser_api_url, $param, $user_response );

		$response_arr = json_decode( wp_remote_retrieve_body( $user_response ), true );
		PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
		$user_body = json_decode( wp_remote_retrieve_body( $user_response ), true );
		return $user_body;

	}

	/**
	 * Add new member into discord guild
	 *
	 * @param INT    $_ets_pmpro_discord_user_id
	 * @param INT    $user_id
	 * @param STRING $access_token
	 * @return NONE
	 */
	public function add_discord_member_in_guild( $_ets_pmpro_discord_user_id, $user_id, $access_token ) {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}
		$curr_level_id = sanitize_text_field( trim( ets_pmpro_discord_get_current_level_id( $user_id ) ) );
		if ( $curr_level_id !== null ) {
			// It is possible that we may exhaust API rate limit while adding members to guild, so handling off the job to queue.
			as_schedule_single_action( ets_pmpro_discord_get_random_timestamp( ets_pmpro_discord_get_highest_last_attempt_timestamp() ), 'ets_pmpro_discord_as_handle_add_member_to_guild', array( $_ets_pmpro_discord_user_id, $user_id, $access_token ), ETS_DISCORD_AS_GROUP_NAME );
		}
	}

	/**
	 * Method to add new members to discord guild.
	 *
	 * @param INT    $_ets_pmpro_discord_user_id
	 * @param INT    $user_id
	 * @param STRING $access_token
	 * @return NONE
	 */
	public function ets_pmpro_discord_as_handler_add_member_to_guild( $_ets_pmpro_discord_user_id, $user_id, $access_token ) {
		// Since we using a queue to delay the API call, there may be a condition when a member is delete from DB. so put a check.
		if ( get_userdata( $user_id ) === false ) {
			return;
		}
		$guild_id                          = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_server_id' ) ) );
		$discord_bot_token                 = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$default_role                      = sanitize_text_field( trim( get_option( '_ets_pmpro_discord_default_role_id' ) ) );
		$ets_pmpor_discord_role_mapping    = json_decode( get_option( 'ets_pmpor_discord_role_mapping' ), true );
		$discord_role                      = '';
		$curr_level_id                     = sanitize_text_field( trim( ets_pmpro_discord_get_current_level_id( $user_id ) ) );
		$ets_pmpro_discord_send_welcome_dm = sanitize_text_field( trim( get_option( 'ets_pmpro_discord_send_welcome_dm' ) ) );

		if ( is_array( $ets_pmpor_discord_role_mapping ) && array_key_exists( 'pmpro_level_id_' . $curr_level_id, $ets_pmpor_discord_role_mapping ) ) {
			$discord_role = sanitize_text_field( trim( $ets_pmpor_discord_role_mapping[ 'pmpro_level_id_' . $curr_level_id ] ) );
		} elseif ( $discord_role = '' && $default_role ) {
			$discord_role = $default_role;
		}

		$guilds_memeber_api_url = ETS_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_ets_pmpro_discord_user_id;
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

		ets_pmpro_discord_log_api_response( $user_id, $guilds_memeber_api_url, $guild_args, $guild_response );
		if ( ets_pmpro_discord_check_api_errors( $guild_response ) ) {

			$response_arr = json_decode( wp_remote_retrieve_body( $guild_response ), true );
			PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
			// this should be catch by Action schedule failed action.
			throw new Exception( 'Failed in function ets_as_handler_add_member_to_guild' );
		}

		update_user_meta( $user_id, '_ets_pmpro_discord_role_id', $discord_role );
		if ( $discord_role && $discord_role != 'none' && isset( $user_id ) ) {
			$this->put_discord_role_api( $user_id, $discord_role );
		}

		if ( $default_role && $default_role != 'none' && isset( $user_id ) ) {
			$this->put_discord_role_api( $user_id, $default_role );
		}
		if ( empty( get_user_meta( $user_id, '_ets_pmpro_discord_join_date', true ) ) ) {
			update_user_meta( $user_id, '_ets_pmpro_discord_join_date', current_time( 'Y-m-d H:i:s' ) );
		}

		// Send welcome message.
		if ( $ets_pmpro_discord_send_welcome_dm == true ) {
			as_schedule_single_action( ets_pmpro_discord_get_random_timestamp( ets_pmpro_discord_get_highest_last_attempt_timestamp() ), 'ets_pmpro_discord_as_send_dm', array( $user_id, $curr_level_id, 'welcome' ), 'ets-pmpro-discord' );
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
					$discord_exist_user_id = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_pmpro_discord_user_id', true ) ) );
					if ( is_array( $res_body ) ) {
						if ( array_key_exists( 'access_token', $res_body ) ) {
							$access_token = sanitize_text_field( trim( $res_body['access_token'] ) );
							update_user_meta( $user_id, '_ets_pmpro_discord_access_token', $access_token );
							if ( array_key_exists( 'refresh_token', $res_body ) ) {
								$refresh_token = sanitize_text_field( trim( $res_body['refresh_token'] ) );
								update_user_meta( $user_id, '_ets_pmpro_discord_refresh_token', $refresh_token );
							}
							if ( array_key_exists( 'expires_in', $res_body ) ) {
								$expires_in = $res_body['expires_in'];
								$date       = new DateTime();
								$date->add( DateInterval::createFromDateString( '' . $expires_in . ' seconds' ) );
								$token_expiry_time = $date->getTimestamp();
								update_user_meta( $user_id, '_ets_pmpro_discord_expires_in', $token_expiry_time );
							}
							$user_body = $this->get_discord_current_user( $access_token );

							if ( is_array( $user_body ) && array_key_exists( 'discriminator', $user_body ) ) {
								$discord_user_number           = $user_body['discriminator'];
								$discord_user_name             = $user_body['username'];
								$discord_user_name_with_number = $discord_user_name . '#' . $discord_user_number;
								update_user_meta( $user_id, '_ets_pmpro_discord_username', $discord_user_name_with_number );
							}
							if ( is_array( $user_body ) && array_key_exists( 'id', $user_body ) ) {
								$_ets_pmpro_discord_user_id = sanitize_text_field( trim( $user_body['id'] ) );
								if ( $discord_exist_user_id == $_ets_pmpro_discord_user_id ) {
									$_ets_pmpro_discord_role_id = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_pmpro_discord_role_id', true ) ) );
									if ( ! empty( $_ets_pmpro_discord_role_id ) && $_ets_pmpro_discord_role_id != 'none' ) {
										$this->delete_discord_role( $user_id, $_ets_pmpro_discord_role_id );
									}
								}
								update_user_meta( $user_id, '_ets_pmpro_discord_user_id', $_ets_pmpro_discord_user_id );
								$this->add_discord_member_in_guild( $_ets_pmpro_discord_user_id, $user_id, $access_token );
							}
						}
					}
				}
			}
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

			as_schedule_single_action( ets_pmpro_discord_get_random_timestamp( ets_pmpro_discord_get_highest_last_attempt_timestamp() ), 'ets_pmpro_discord_as_schedule_delete_member', array( $user_id, $is_schedule ), ETS_DISCORD_AS_GROUP_NAME );
		} else {
			if ( isset( $user_id ) ) {
				$this->ets_pmpro_discord_as_handler_delete_member_from_guild( $user_id, $is_schedule );
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
	public function ets_pmpro_discord_as_handler_delete_member_from_guild( $user_id, $is_schedule ) {
		$guild_id                      = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_server_id' ) ) );
		$discord_bot_token             = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$_ets_pmpro_discord_user_id    = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_pmpro_discord_user_id', true ) ) );
		$guilds_delete_memeber_api_url = ETS_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_ets_pmpro_discord_user_id;
		$guild_args                    = array(
			'method'  => 'DELETE',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bot ' . $discord_bot_token,
			),
		);
		$guild_response                = wp_remote_post( $guilds_delete_memeber_api_url, $guild_args );

		ets_pmpro_discord_log_api_response( $user_id, $guilds_delete_memeber_api_url, $guild_args, $guild_response );
		if ( ets_pmpro_discord_check_api_errors( $guild_response ) ) {
			$response_arr = json_decode( wp_remote_retrieve_body( $guild_response ), true );
			PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
			if ( $is_schedule ) {
				// this exception should be catch by action scheduler.
				throw new Exception( 'Failed in function ets_pmpro_discord_as_handler_delete_member_from_guild' );
			}
		}

		/*Delete all usermeta related to discord connection*/
		delete_user_meta( $user_id, '_ets_pmpro_discord_user_id' );
		delete_user_meta( $user_id, '_ets_pmpro_discord_access_token' );
		delete_user_meta( $user_id, '_ets_pmpro_discord_refresh_token' );
		delete_user_meta( $user_id, '_ets_pmpro_discord_role_id' );
		delete_user_meta( $user_id, '_ets_pmpro_discord_default_role_id' );
		delete_user_meta( $user_id, '_ets_pmpro_discord_username' );
		delete_user_meta( $user_id, '_ets_pmpro_discord_expires_in' );

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
			as_schedule_single_action( ets_pmpro_discord_get_random_timestamp( ets_pmpro_discord_get_highest_last_attempt_timestamp() ), 'ets_pmpro_discord_as_schedule_member_put_role', array( $user_id, $role_id, $is_schedule ), ETS_DISCORD_AS_GROUP_NAME );
		} else {
			$this->ets_pmpro_discord_as_handler_put_memberrole( $user_id, $role_id, $is_schedule );
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
	public function ets_pmpro_discord_as_handler_put_memberrole( $user_id, $role_id, $is_schedule ) {
		$access_token                = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_pmpro_discord_access_token', true ) ) );
		$guild_id                    = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_server_id' ) ) );
		$_ets_pmpro_discord_user_id  = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_pmpro_discord_user_id', true ) ) );
		$discord_bot_token           = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
		$discord_change_role_api_url = ETS_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_ets_pmpro_discord_user_id . '/roles/' . $role_id;

		if ( $access_token && $_ets_pmpro_discord_user_id ) {
			$param = array(
				'method'  => 'PUT',
				'headers' => array(
					'Content-Type'   => 'application/json',
					'Authorization'  => 'Bot ' . $discord_bot_token,
					'Content-Length' => 0,
				),
			);

			$response = wp_remote_get( $discord_change_role_api_url, $param );

			ets_pmpro_discord_log_api_response( $user_id, $discord_change_role_api_url, $param, $response );
			if ( ets_pmpro_discord_check_api_errors( $response ) ) {
				$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
				PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				if ( $is_schedule ) {
					// this exception should be catch by action scheduler.
					throw new Exception( 'Failed in function ets_pmpro_discord_as_handler_put_memberrole' );
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
			as_schedule_single_action( ets_pmpro_discord_get_random_timestamp( ets_pmpro_discord_get_highest_last_attempt_timestamp() ), 'ets_pmpro_discord_as_schedule_delete_role', array( $user_id, $ets_role_id, $is_schedule ), ETS_DISCORD_AS_GROUP_NAME );
		} else {
			$this->ets_pmpro_discord_as_handler_delete_memberrole( $user_id, $ets_role_id, $is_schedule );
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
	public function ets_pmpro_discord_as_handler_delete_memberrole( $user_id, $ets_role_id, $is_schedule = true ) {

			$guild_id                    = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_server_id' ) ) );
			$_ets_pmpro_discord_user_id  = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_pmpro_discord_user_id', true ) ) );
			$discord_bot_token           = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
			$discord_delete_role_api_url = ETS_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_ets_pmpro_discord_user_id . '/roles/' . $ets_role_id;
		if ( $_ets_pmpro_discord_user_id ) {
			$param = array(
				'method'  => 'DELETE',
				'headers' => array(
					'Content-Type'   => 'application/json',
					'Authorization'  => 'Bot ' . $discord_bot_token,
					'Content-Length' => 0,
				),
			);

			$response = wp_remote_request( $discord_delete_role_api_url, $param );
			ets_pmpro_discord_log_api_response( $user_id, $discord_delete_role_api_url, $param, $response );
			if ( ets_pmpro_discord_check_api_errors( $response ) ) {
				$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
				PMPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				if ( $is_schedule ) {
					// this exception should be catch by action scheduler.
					throw new Exception( 'Failed in function ets_pmpro_discord_as_handler_delete_memberrole' );
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
	public function ets_pmpro_discord_disconnect_from_discord() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}

		// Check for nonce security
		if ( ! wp_verify_nonce( $_POST['ets_discord_nonce'], 'ets-discord-ajax-nonce' ) ) {
				wp_send_json_error( 'You do not have sufficient rights', 403 );
				exit();
		}
		$user_id = sanitize_text_field( trim( $_POST['user_id'] ) );
		if ( $user_id ) {
			$this->delete_member_from_guild( $user_id, false );
			delete_user_meta( $user_id, '_ets_pmpro_discord_access_token' );
		}
		$event_res = array(
			'status'  => 1,
			'message' => 'Successfully disconnected',
		);
		wp_send_json( $event_res );
	}

	/**
	 * Manage user roles api calls
	 *
	 * @param NONE
	 * @return OBJECT JSON response
	 */
	public function ets_pmpro_discord_member_table_run_api() {
		if ( ! is_user_logged_in() && current_user_can( 'edit_user' ) ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}

		// Check for nonce security
		if ( ! wp_verify_nonce( $_POST['ets_discord_nonce'], 'ets-discord-ajax-nonce' ) ) {
				wp_send_json_error( 'You do not have sufficient rights', 403 );
				exit();
		}
		$user_id = sanitize_text_field( $_POST['user_id'] );
		$this->ets_pmpro_discord_set_member_roles( $user_id, false, false, false );

		$event_res = array(
			'status'  => 1,
			'message' => __( 'success', 'pmpro-discord-add-on' ),
		);
		return wp_send_json( $event_res );
	}

	/**
	 * Method to adjust level mapped and default role of a member.
	 *
	 * @param INT  $user_id
	 * @param INT  $expired_level_id
	 * @param INT  $cancel_level_id
	 * @param BOOL $is_schedule
	 */
	private function ets_pmpro_discord_set_member_roles( $user_id, $expired_level_id = false, $cancel_level_id = false, $is_schedule = true ) {
		$allow_none_member                            = sanitize_text_field( trim( get_option( 'ets_pmpro_allow_none_member' ) ) );
		$default_role                                 = sanitize_text_field( trim( get_option( '_ets_pmpro_discord_default_role_id' ) ) );
		$_ets_pmpro_discord_role_id                   = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_pmpro_discord_role_id', true ) ) );
		$ets_pmpor_discord_role_mapping               = json_decode( get_option( 'ets_pmpor_discord_role_mapping' ), true );
		$curr_level_id                                = sanitize_text_field( trim( ets_pmpro_discord_get_current_level_id( $user_id ) ) );
		$previous_default_role                        = get_user_meta( $user_id, '_ets_pmpro_discord_default_role_id', true );
		$ets_pmpro_discord_send_membership_expired_dm = sanitize_text_field( trim( get_option( 'ets_pmpro_discord_send_membership_expired_dm' ) ) );
		$ets_pmpro_discord_send_membership_cancel_dm  = sanitize_text_field( trim( get_option( 'ets_pmpro_discord_send_membership_cancel_dm' ) ) );
		$access_token                                 = get_user_meta( $user_id, '_ets_pmpro_discord_access_token', true );
		if ( ! empty( $access_token ) ) {
			if ( $expired_level_id ) {
				$curr_level_id = $expired_level_id;
			}
			if ( $cancel_level_id ) {
				$curr_level_id = $cancel_level_id;
			}
			// delete already assigned role.
			if ( isset( $_ets_pmpro_discord_role_id ) && $_ets_pmpro_discord_role_id != '' && $_ets_pmpro_discord_role_id != 'none' ) {
					$this->delete_discord_role( $user_id, $_ets_pmpro_discord_role_id, $is_schedule );
					delete_user_meta( $user_id, '_ets_pmpro_discord_role_id', true );
			}
			if ( $curr_level_id !== null ) {
				// Assign role which is mapped to the mmebership level.
				if ( is_array( $ets_pmpor_discord_role_mapping ) && array_key_exists( 'pmpro_level_id_' . $curr_level_id, $ets_pmpor_discord_role_mapping ) ) {
					$mapped_role_id = sanitize_text_field( trim( $ets_pmpor_discord_role_mapping[ 'pmpro_level_id_' . $curr_level_id ] ) );
					if ( $mapped_role_id && $expired_level_id == false && $cancel_level_id == false ) {
						$this->put_discord_role_api( $user_id, $mapped_role_id, $is_schedule );
						update_user_meta( $user_id, '_ets_pmpro_discord_role_id', $mapped_role_id );
					}
				}
			}
			// Assign role which is saved as default.
			if ( $default_role != 'none' ) {
				if ( isset( $previous_default_role ) && $previous_default_role != '' && $previous_default_role != 'none' ) {
						$this->delete_discord_role( $user_id, $previous_default_role, $is_schedule );
				}
				delete_user_meta( $user_id, '_ets_pmpro_discord_default_role_id', true );
				$this->put_discord_role_api( $user_id, $default_role, $is_schedule );
				update_user_meta( $user_id, '_ets_pmpro_discord_default_role_id', $default_role );
			} elseif ( $default_role == 'none' ) {
				if ( isset( $previous_default_role ) && $previous_default_role != '' && $previous_default_role != 'none' ) {
					$this->delete_discord_role( $user_id, $previous_default_role, $is_schedule );
				}
				update_user_meta( $user_id, '_ets_pmpro_discord_default_role_id', $default_role );
			}

			if ( isset( $user_id ) && $allow_none_member == 'no' && $curr_level_id == null ) {
				$this->delete_member_from_guild( $user_id, false );
			}

			delete_user_meta( $user_id, '_ets_pmpro_discord_expitration_warning_dm_for_' . $curr_level_id );

			// Send DM about expiry, but only when allow_none_member setting is yes
			if ( $ets_pmpro_discord_send_membership_expired_dm == true && $expired_level_id !== false && $allow_none_member = 'yes' ) {
				as_schedule_single_action( ets_pmpro_discord_get_random_timestamp( ets_pmpro_discord_get_highest_last_attempt_timestamp() ), 'ets_pmpro_discord_as_send_dm', array( $user_id, $expired_level_id, 'expired' ), 'ets-pmpro-discord' );
			}

			// Send DM about cancel, but only when allow_none_member setting is yes
			if ( $ets_pmpro_discord_send_membership_cancel_dm == true && $cancel_level_id !== false && $allow_none_member = 'yes' ) {
				as_schedule_single_action( ets_pmpro_discord_get_random_timestamp( ets_pmpro_discord_get_highest_last_attempt_timestamp() ), 'ets_pmpro_discord_as_send_dm', array( $user_id, $cancel_level_id, 'cancel' ), 'ets-pmpro-discord' );
			}
		}
	}
	/**
	 * Manage user roles on cancel payment
	 *
	 * @param INT $user_id
	 */
	public function ets_pmpro_discord_stripe_subscription_deleted( $user_id ) {
		if ( isset( $user_id ) ) {
			$this->ets_pmpro_discord_set_member_roles( $user_id, false, false, true );
		}
	}

	/**
	 * Manage user roles on subscription  payment failed
	 *
	 * @param ARRAY $old_order
	 */
	public function ets_pmpro_discord_subscription_payment_failed( $old_order ) {
		$user_id         = $old_order->user_id;
		$ets_payment_fld = sanitize_text_field( trim( get_option( 'ets_pmpro_discord_payment_failed' ) ) );

		if ( $ets_payment_fld == true && isset( $user_id ) ) {
			$this->ets_pmpro_discord_set_member_roles( $user_id, false, false, true );
		}
	}

	/*
	* Action scheduler method to process expired pmpro members.
	* @param INT $user_id
	* @param INT $expired_level_id
	*/
	public function ets_pmpro_discord_as_handler_pmpro_expiry( $user_id, $expired_level_id ) {
		$this->ets_pmpro_discord_set_member_roles( $user_id, $expired_level_id, false, true );
	}

	/*
	* Method to process queue of canceled pmpro members.
	*
	* @param INT $user_id
	* @param INT $level_id
	* @param INT $cancel_level_id
	* @return NONE
	*/
	public function ets_pmpro_discord_as_handler_pmpro_cancel( $user_id, $level_id, $cancel_level_id ) {
		$this->ets_pmpro_discord_set_member_roles( $user_id, false, $cancel_level_id, true );
	}

	/**
	 * Change discord role from admin user edit.
	 *
	 * @param INT $level_id
	 * @param INT $user_id
	 * @param INT $cancel_level
	 * @return NONE
	 */
	public function ets_pmpro_discord_change_discord_role_from_pmpro( $level_id, $user_id, $cancel_level ) {
		$this->ets_pmpro_discord_set_member_roles( $user_id, false, false, true );
	}
}
new Ultimate_Member_Discord_API();
