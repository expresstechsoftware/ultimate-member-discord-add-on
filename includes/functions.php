<?php
/*
Common functions
*/

// function to get formated redirect url
function ets_get_ultimatemember_discord_formated_discord_redirect_url( $url ) {
	$parsed = parse_url( $url, PHP_URL_QUERY );
	if ( $parsed === null ) {
		return $url .= '?via=discord';
	} else {
		if ( stristr( $url, 'via=discord' ) !== false ) {
			return $url;
		} else {
			return $url .= '&via=discord';
		}
	}
}

/**
 * To check settings values saved or not
 *
 * @param NONE
 * @return BOOL $status
 */
function ultimatemember_discord_check_saved_settings_status() {
	$ets_ultimatemember_discord_client_id     = get_option( 'ets_ultimatemember_discord_client_id' );
	$ets_ultimatemember_discord_client_secret = get_option( 'ets_ultimatemember_discord_client_secret' );
	$ets_ultimatemember_discord_bot_token     = get_option( 'ets_ultimatemember_discord_bot_token' );
	$ets_ultimatemember_discord_redirect_url  = get_option( 'ets_ultimatemember_discord_redirect_url' );
	$ets_ultimatemember_discord_server_id      = get_option( 'ets_ultimatemember_discord_server_id' );

	if ( $ets_ultimatemember_discord_client_id && $ets_ultimatemember_discord_client_secret && $ets_ultimatemember_discord_bot_token && $ets_ultimatemember_discord_redirect_url && $ets_ultimatemember_discord_server_id ) {
			$status = true;
	} else {
			 $status = false;
	}

		 return $status;
}

  /**
   * Log API call response
   *
   * @param INT          $user_id
   * @param STRING       $api_url
   * @param ARRAY        $api_args
   * @param ARRAY|OBJECT $api_response
   */
function ets_ultimatemember_discord_log_api_response( $user_id, $api_url = '', $api_args = array(), $api_response = '' ) {
	$log_api_response = get_option( 'ets_pmpro_discord_log_api_response' );
	if ( $log_api_response == true ) {
		$log_string  = '==>' . $api_url;
		$log_string .= '-::-' . serialize( $api_args );
		$log_string .= '-::-' . serialize( $api_response );

		$logs = new PMPro_Discord_Logs();
		$logs->write_api_response_logs( $log_string, $user_id );
	}
}

