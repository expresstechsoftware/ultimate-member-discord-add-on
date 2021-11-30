<?php

/**
 * Provide a admin area view for the plugin
 *
 *
 * @link       https://www.expresstechsoftwares.com
 * @since      1.0.0
 *
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/admin/partials
 */
?>
<?php
if ( isset( $_GET['save_settings_msg'] ) ) {
?>
<div class="notice notice-success is-dismissible support-success-msg">
    <p><?php echo esc_html( $_GET['save_settings_msg'] ); ?></p>
</div>
<?php
}
?>
<h1><?php echo __( 'Ultimate member Discord Add On Settings', 'ultimate-member-discord-add-on' ); ?></h1>
<div id="ultimate-discord-outer" class="skltbs-theme-light" data-skeletabs='{ "startIndex": 1 }'>
  <ul class="skltbs-tab-group">
  <li class="skltbs-tab-item">
		<button class="skltbs-tab" data-identity="settings" ><?php echo __( 'Application details', 'ultimate-member-discord-add-on' ); ?><span class="initialtab spinner"></span></button>
  </li>
  <li class="skltbs-tab-item">
      <?php if ( ultimatemember_discord_check_saved_settings_status() ): ?>
      <button class="skltbs-tab" data-identity="level-mapping" ><?php echo __( 'Role Mappings', 'ultimate-member-discord-add-on' ); ?></button>
      <?php endif; ?>
  </li>
  <li class="skltbs-tab-item">
     <button class="skltbs-tab" data-identity="loge" ><?php echo __( 'Logs', 'ultimate-member-discord-add-on' ); ?></button> 
  </li>  
  </ul>
  <div class="skltbs-panel-group">
		<div id="ets_setting" class="ultimate-discord-tab-conetent skltbs-panel">
		<?php
			require_once ULTIMATE_MEMBER_DISCORD_PLUGIN_DIR_PATH . 'admin/partials/pages/ulimate_member_discord_application_details.php';
    ?>
		</div>
		<div id="ets_ultimatemember_discord_role_mapping" class="ultimate-discord-tab-conetent skltbs-panel">
		<?php
			require_once ULTIMATE_MEMBER_DISCORD_PLUGIN_DIR_PATH . 'admin/partials/pages/ulimate_member_discord_role_mapping.php';
    ?>
		</div>   
		<div id="ets_ultimatemember_discord_erro_log" class="ultimate-discord-tab-conetent skltbs-panel">
		<?php
			require_once ULTIMATE_MEMBER_DISCORD_PLUGIN_DIR_PATH . 'admin/partials/pages/ulimate_member_discord_error_log.php';
    ?>
		</div>
  </div>  
    

</div>
