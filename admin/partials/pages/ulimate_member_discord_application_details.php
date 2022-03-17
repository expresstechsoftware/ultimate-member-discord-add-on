<?php
$ets_ultimatemember_discord_client_id     = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_client_id' ) ) );
$ets_ultimatemember_discord_client_secret = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_client_secret' ) ) );
$ets_ultimatemember_discord_bot_token     = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_bot_token' ) ) );
$ets_ultimatemember_discord_redirect_url  = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_redirect_url' ) ) );
$ets_ultimatemember_discord_roles         = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_role_mapping' ) ) );
$ets_ultimatemember_discord_server_id     = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_server_id' ) ) );
?>
<form method="post" action="<?php echo get_site_url() . '/wp-admin/admin-post.php'; ?>">
  <input type="hidden" name="action" value="ultimatemember_discord_application_settings">
  <input type="hidden" name="current_url" value="<?php echo ultimatemember_discord_get_current_screen_url()?>">   
	<?php wp_nonce_field( 'save_ultimatemember_discord_general_settings', 'ets_ultimatemember_discord_save_settings' ); ?>
  <div class="ets-discord-input-group">
	<label><?php echo __( 'Client ID', 'ultimate-member-discord-add-on' ); ?> :</label>
	<input type="text" class="ets-input" name="ets_ultimatemember_discord_client_id" value="<?php
	if ( isset( $ets_ultimatemember_discord_client_id ) ) {
		echo esc_attr( $ets_ultimatemember_discord_client_id ); }
	?>" required placeholder="Discord Client ID">
  </div>
	<div class="ets-discord-input-group">
	  <label><?php echo __( 'Client Secret', 'ultimate-member-discord-add-on' ); ?> :</label>
		<input type="text" class="ets-input" name="ets_ultimatemember_discord_client_secret" value="<?php
		if ( isset( $ets_ultimatemember_discord_client_secret ) ) {
			echo esc_attr( $ets_ultimatemember_discord_client_secret ); }
    ?>" required placeholder="Discord Client Secret">
	</div>
	<div class="ets-discord-input-group">
    <label><?php echo __( 'Redirect URL', 'ultimate-member-discord-add-on' ); ?> :</label>
		<input type="text" class="ets-input" name="ets_ultimatemember_discord_redirect_url"
		placeholder="Discord Redirect Url" value="<?php
		if ( isset( $ets_ultimatemember_discord_redirect_url ) ) {
			echo esc_attr( $ets_ultimatemember_discord_redirect_url ); }
		?>" required>
		<p class="description"><?php echo __( 'Registered discord app url', 'ultimate-member-discord-add-on' ); ?></p>
	</div>
	<div class="ets-discord-input-group">
	  <label><?php echo __( 'Bot Token', 'ultimate-member-discord-add-on' ); ?> :</label>
          <input type="password" class="ets-input" name="ets_ultimatemember_discord_bot_token" value="<?php
		if ( isset( $ets_ultimatemember_discord_bot_token ) ) {
			echo esc_attr( $ets_ultimatemember_discord_bot_token ); }
		?>" required placeholder="Discord Bot Token">
	</div>
	<div class="ets-discord-input-group">
	  <label><?php echo __( 'Server ID', 'ultimate-member-discord-add-on' ); ?> :</label>
		<input type="text" class="ets-input" name="ets_ultimatemember_discord_server_id"
		placeholder="Discord Server Id" value="<?php
		if ( isset( $ets_ultimatemember_discord_server_id ) ) {
			echo esc_attr( $ets_ultimatemember_discord_server_id ); }
		?>" required>
	</div>
	<?php if ( empty( $ets_ultimatemember_discord_client_id ) || empty( $ets_ultimatemember_discord_client_secret ) || empty( $ets_ultimatemember_discord_bot_token ) || empty( $ets_ultimatemember_discord_redirect_url ) || empty( $ets_ultimatemember_discord_server_id ) ) { ?>
	  <p class="ets-danger-text description">
		<?php echo __( 'Please save your form', 'ultimate-member-discord-add-on' ); ?>
	  </p>
	<?php } ?>
	<p>
	  <button type="submit" name="submit" value="ets_discord_submit" class="ets-btn-submit ets-bg-green">
		<?php echo __( 'Save Settings', 'ultimate-member-discord-add-on' ); ?>
	  </button>
	  <?php if ( get_option( 'ets_ultimatemember_discord_client_id' ) ) : ?>
            <a href="?action=discord-connect-to-bot" class="ets-btn-submit ultimatemember-btn-connect-to-bot" id="ultimatemember-connect-discord-bot"><?php echo __( 'Connect your Bot', 'ultimate-member-discord-add-on' ) . Ultimate_Member_Discord_Add_On::get_discord_logo_white(); ?> </a>
	  <?php endif; ?>
	</p>
</form>
