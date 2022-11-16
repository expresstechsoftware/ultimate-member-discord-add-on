<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.expresstechsoftwares.com
 * @since      1.0.0
 *
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/includes
 * @author     ExpressTech Softwares Solutions Pvt Ltd <contact@expresstechsoftwares.com>
 */
class Ultimate_Member_Discord_Add_On_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
			update_option( 'ets_ultimatemember_discord_uuid_file_name', wp_generate_uuid4() );
			self::set_default_setting_values();
			self::auto_save_redirect_url();
	}

	/**
	 * Set default settings on activation
	 */
	public static function set_default_setting_values() {

		update_option( 'ets_ultimatemember_discord_log_api_response', false );
		update_option( 'ets_ultimatemember_retry_failed_api', true );
		update_option( 'ets_ultimatemember_discord_job_queue_concurrency', 1 );
		update_option( 'ets_ultimatemember_discord_job_queue_batch_size', 7 );

		update_option( 'ets_ultimatemember_retry_api_count', '5' );
		update_option( 'ets_ultimatemember_discord_send_welcome_dm', true );
		update_option( 'ets_ultimatemember_discord_welcome_message', 'Hi [MEMBER_USERNAME] ([MEMBER_EMAIL]), Welcome, Your  [MEMBER_ROLE] at [SITE_URL] Thanks, Kind Regards, [BLOG_NAME]' );
		update_option( 'ets_ultimatemember_discord_embed_messaging_feature', false );
		update_option( 'ets_ultimatemember_discord_connect_button_bg_color', '#7bbc36' );
		update_option( 'ets_ultimatemember_discord_disconnect_button_bg_color', '#ff0000' );
		update_option( 'ets_ultimatemember_discord_loggedin_button_text', 'Connect With Discord' );
		update_option( 'ets_ultimatemember_discord_non_login_button_text', 'Login With Discord' );
		update_option( 'ets_ultimatemember_discord_disconnect_button_text', 'Disconnect From Discord' );

	}

	/**
	 * Auto save redirect URL from Ultimate member settings
	 *
	 * @since    1.0.0
	 */
	public static function auto_save_redirect_url() {

		$ets_ultimatemember_discord_redirect_url = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_redirect_url' ) ) );

		if ( $ets_ultimatemember_discord_redirect_url ) {
			return;
		}
		$um_account_page_id = sanitize_text_field( get_option( 'um_options' )['core_account'] );
		$url                = esc_url( get_permalink( $um_account_page_id ) );
		update_option( 'ets_ultimatemember_discord_redirect_url', $url .= '?via=ultimate-discord' );

	}

}
