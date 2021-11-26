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
            self::set_default_setting_values();
	}

	/**
	 * Set default settings on activation
	 */
	public static function set_default_setting_values() {
                update_option( 'ets_ultimatemember_discord_uuid_file_name', wp_generate_uuid4() );
		update_option( 'ets_ultimatemember_discord_payment_failed', true );
		update_option( 'ets_ultimatemember_discord_log_api_response', false );
		update_option( 'ets_ultimatemember_retry_failed_api', true );
		update_option( 'ets_ultimatemember_discord_job_queue_concurrency', 1 );
		update_option( 'ets_ultimatemember_discord_job_queue_batch_size', 7 );
		
		update_option( 'ets_ultimatemember_retry_api_count', '5' );
		update_option( 'ets_ultimatemember_send_welcome_dm', true );
		update_option( 'ets_ultimatemember_discord_welcome_message', 'Hi [ULTIMATEMEMBER_USERNAME] ([ULTIMATEMEMBER_EMAIL]), Welcome, Your membership [ULTIMATEMEMBER_LEVEL] is starting from [ULTIMATEMEMBER_STARTDATE] at [SITE_URL] the last date of your membership is [ULTIMATEMEMBER_ENDDATE] Thanks, Kind Regards, [BLOG_NAME]' );
		update_option( 'ets_ultimatemember_discord_send_expiration_warning_dm', true );
		update_option( 'ets_ultimatemember_discord_expiration_warning_message', 'Hi [ULTIMATEMEMBER_USERNAME] ([ULTIMATEMEMBER_EMAIL]), Your membership [ULTIMATEMEMBER_LEVEL] is expiring at [ULTIMATEMEMBER_ENDDATE] at [SITE_URL] Thanks, Kind Regards, [BLOG_NAME]' );
		update_option( 'ets_ultimatemember_discord_send_membership_expired_dm', true );
		update_option( 'ets_ultimatemember_discord_expiration_expired_message', 'Hi [ULTIMATEMEMBER_USERNAME] ([ULTIMATEMEMBER_EMAIL]), Your membership [ULTIMATEMEMBER_LEVEL] is expired at [ULTIMATEMEMBER_ENDDATE] at [SITE_URL] Thanks, Kind Regards, [BLOG_NAME]' );
		update_option( 'ets_ultimatemember_discord_send_membership_cancel_dm', true );
		update_option( 'ets_ultimatemember_discord_cancel_message', 'Hi [ULTIMATEMEMBER_USERNAME], ([ULTIMATEMEMBER_EMAIL]), Your membership [ULTIMATEMEMBER_LEVEL] at [BLOG_NAME] is cancelled, Regards, [SITE_URL]' );
	}

}
