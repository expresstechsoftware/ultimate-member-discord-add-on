<?php
$ets_ultimatemember_discord_send_welcome_dm            = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_send_welcome_dm' ) ) );
$ets_ultimatemember_discord_welcome_message            = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_welcome_message' ) ) );
$retry_failed_api                             = sanitize_text_field( trim( get_option( 'ets_ultimatemember_retry_failed_api' ) ) );
$retry_api_count                              = sanitize_text_field( trim( get_option( 'ets_ultimatemember_retry_api_count' ) ) );
$set_job_cnrc                                 = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_job_queue_concurrency' ) ) );
$set_job_q_batch_size                         = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_job_queue_batch_size' ) ) );
$log_api_res                                  = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_log_api_response' ) ) );

?>
<form method="post" action="<?php echo get_site_url().'/wp-admin/admin-post.php' ?>">
 <input type="hidden" name="action" value="ultimatemember_discord_save_advance_settings">
<input type="hidden" name="current_url" value="<?php echo ultimatemember_discord_get_current_screen_url()?>">   
<?php wp_nonce_field( 'ultimatemember_discord_advance_settings_nonce', 'ets_ultimatemember_discord_advance_settings_nonce' ); ?>
  <table class="form-table" role="presentation">
	<tbody>
  <tr>
		<th scope="row"><?php echo __( 'Send welcome message', 'ultimate-member-discord-add-on' ); ?></th>
		<td> <fieldset>
		<input name="ets_ultimatemember_discord_send_welcome_dm" type="checkbox" id="ets_ultimatemember_discord_send_welcome_dm" 
		<?php
		if ( $ets_ultimatemember_discord_send_welcome_dm == true ) {
			echo 'checked="checked"'; }
		?>
		 value="1">
		</fieldset></td>
	  </tr>
	<tr>
		<th scope="row"><?php echo __( 'Membership welcome message', 'ultimate-member-discord-add-on' ); ?></th>
		<td> <fieldset>
		<textarea class="ets_ultimate-member_discord_dm_textarea" name="ets_ultimatemember_discord_welcome_message" id="ets_ultimatemember_discord_welcome_message" row="25" cols="50"><?php if ( $ets_ultimatemember_discord_welcome_message ) { echo wp_unslash($ets_ultimatemember_discord_welcome_message); } ?></textarea> 
	<br/>
	<small>Merge fields: [MEMBER_USERNAME], [MEMBER_EMAIL], [MEMBER_ROLE], [SITE_URL], [BLOG_NAME]</small>
		</fieldset></td>
	  </tr>

	  </tr>
		<th scope="row"><?php echo __( 'Retry Failed API calls', 'ultimate-member-discord-add-on' ); ?></th>
		<td> <fieldset>
		<input name="retry_failed_api" type="checkbox" id="retry_failed_api" 
		<?php
		if ( $retry_failed_api == true ) {
			echo 'checked="checked"'; }
		?>
		 value="1">
		</fieldset></td>
	  </tr>
	<tr>
		<th scope="row"><?php echo __( 'How many times a failed API call should get re-try', 'ultimate-member-discord-add-on' ); ?></th>
		<td> <fieldset>
		<input name="ets_ultimatemember_retry_api_count" type="number" min="1" id="ets_ultimatemember_retry_api_count" value="<?php if ( isset( $retry_api_count ) ) { echo intval($retry_api_count); } else { echo 1; } ?>">
		</fieldset></td>
	  </tr> 
	  <tr>
		<th scope="row"><?php echo __( 'Set job queue concurrency', 'ultimate-member-discord-add-on' ); ?></th>
		<td> <fieldset>
		<input name="set_job_cnrc" type="number" min="1" id="set_job_cnrc" value="<?php if ( isset( $set_job_cnrc ) ) { echo intval($set_job_cnrc); } else { echo 1; } ?>">
		</fieldset></td>
	  </tr>
	  <tr>
		<th scope="row"><?php echo __( 'Set job queue batch size', 'ultimate-member-discord-add-on' ); ?></th>
		<td> <fieldset>
		<input name="set_job_q_batch_size" type="number" min="1" id="set_job_q_batch_size" value="<?php if ( isset( $set_job_q_batch_size ) ) { echo intval($set_job_q_batch_size); } else { echo 10; } ?>">
		</fieldset></td>
	  </tr>
	<tr>
		<th scope="row"><?php echo __( 'Log API calls response (For debugging purpose)', 'ultimate-member-discord-add-on' ); ?></th>
		<td> <fieldset>
		<input name="log_api_res" type="checkbox" id="log_api_res" 
		<?php
		if ( $log_api_res == true ) {
			echo 'checked="checked"'; }
		?>
		 value="1">
		</fieldset></td>
	  </tr>
	
	</tbody>
  </table>
  <div class="bottom-btn">
	<button type="submit" name="adv_submit" value="ets_submit" class="ets-submit ets-bg-green">
	  <?php echo __( 'Save Settings', 'ultimate-member-discord-add-on' ); ?>
	</button>
  </div>
</form>
