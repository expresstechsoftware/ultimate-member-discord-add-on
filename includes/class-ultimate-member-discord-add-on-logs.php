<?php
/**
 * Class to handle log of API errors
 */
class Ultimate_Member_Discord_Add_On_Logs {
	function __construct() {
		// Ajax Call Clear all existing logs.
		
	}

	/**
	 * Static property to define log file name
	 *
	 * @param None
	 * @return string $log_file_name
	 */
	public static $log_file_name = 'ultimatemember_discord_api_logs.txt';

	/**
	 * Add API error logs into log file
	 *
	 * @param array  $response_arr
	 * @param array  $backtrace_arr
	 * @param string $error_type
	 * @return None
	 */
	static function write_api_response_logs( $response_arr, $user_id, $backtrace_arr = array() ) {

		$error        = current_time( 'mysql' );
		$user_details = '';
		if ( $user_id ) {
			$user_details = '::User Id:' . $user_id;
		}
		$log_api_response = get_option( 'ets_ultimatemember_discord_log_api_response' );
		$uuid             = get_option( 'ets_ultimatemember_discord_uuid_file_name' );
		$log_file_name    = $uuid . self::$log_file_name;
		if ( is_array( $response_arr ) && array_key_exists( 'code', $response_arr ) ) {
			$error .= '==>File:' . $backtrace_arr['file'] . $user_details . '::Line:' . $backtrace_arr['line'] . '::Function:' . $backtrace_arr['function'] . '::' . $response_arr['code'] . ':' . $response_arr['message'];
			if ( $response_arr['code'] == '50001' ) {
				$error .= '<br><b> Solution: The BOT role need to the TOP priority among the other roles. discord.com > Server Settings > Roles > Drag the BOT role to the TOP priority</b>';
			}
			file_put_contents( WP_CONTENT_DIR . '/' . $log_file_name, $error . PHP_EOL, FILE_APPEND | LOCK_EX );
		} elseif ( is_array( $response_arr ) && array_key_exists( 'error', $response_arr ) ) {
			$error .= '==>File:' . $backtrace_arr['file'] . $user_details . '::Line:' . $backtrace_arr['line'] . '::Function:' . $backtrace_arr['function'] . '::' . $response_arr['error'];
			file_put_contents( WP_CONTENT_DIR . '/' . $log_file_name, $error . PHP_EOL, FILE_APPEND | LOCK_EX );
		} elseif ( $log_api_response == true ) {
			$error .= json_encode( $response_arr ) . '::' . $user_id;
			file_put_contents( WP_CONTENT_DIR . '/' . $log_file_name, $error . PHP_EOL, FILE_APPEND | LOCK_EX );
		}

	}
}
