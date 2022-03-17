<?php


$role_keys = get_option( 'um_roles', array() );


$um_roles = array();
foreach( UM()->roles()->get_roles() as $k => $v ){ 
    foreach( $role_keys as $i => $j ){
        if( $k === "um_".$j ){
            $um_roles[$j] = $v;
            
        }
        
        }
}

$default_role        = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_default_role_id' ) ) );
?>
<div class="notice notice-warning ets-notice">
  <p><i class='fas fa-info'></i> <?php echo __( 'Drag and Drop the Discord Roles over to the Ultimate Member Roles', 'ultimate-member-discord-add-on' ); ?></p>
</div>

<div class="row-container">
  <div class="ets-column ultimate-discord-roles-col">
	<h2><?php echo __( 'Discord Roles', 'ultimate-member-discord-add-on' ); ?></h2>
	<hr>
	<div class="ultimate-member-discord-roles">
	  <span class="spinner"></span>
	</div>
  </div>
  <div class="ets-column">
	<h2><?php echo __( 'Ultimate Member', 'ultimate-member-discord-add-on' ); ?></h2>
	<hr>
	<div class="ultimate-discord-levels">
	<?php
	foreach ( $um_roles as $key => $value ) {
		
			?>
		  <div class="makeMeDroppable" data-ultimate-member_level_id="<?php echo esc_attr($key); ?>" ><span><?php echo esc_html($value); ?></span></div>
			<?php
		
	}
	?>
	</div>
  </div>
</div>
<form method="post" action="<?php echo get_site_url().'/wp-admin/admin-post.php' ?>">
 <input type="hidden" name="action" value="ultimatemember_discord_save_role_mapping">
 <input type="hidden" name="current_url" value="<?php echo ultimatemember_discord_get_current_screen_url()?>">   
  <table class="form-table" role="presentation">
	<tbody>
	  <tr>
		<th scope="row"><label for="ultimate-member-defaultRole"><?php echo __( 'Default Role', 'ultimate-member-discord-add-on' ); ?></label></th>
		<td>
		  <?php wp_nonce_field( 'ultimatemember_discord_role_mappings_nonce', 'ets_ultimatemember_discord_role_mappings_nonce' ); ?>
		  <input type="hidden" id="selected_default_role" value="<?php echo esc_attr( $default_role ); ?>">
		  <select id="ultimate-member-defaultRole" name="ultimate-member_defaultRole">
			<option value="none"><?php echo __( '-None-', 'ultimate-member-discord-add-on' ); ?></option>
		  </select>
		  <p class="description"><?php echo __( 'This Role will be assigned to all roles members', 'ultimate-member-discord-add-on' ); ?></p>
		</td>
	  </tr>

	</tbody>
  </table>
	<br>
  <div class="mapping-json">
	<textarea id="ultimate-member_maaping_json_val" name="ets_ultimatemember_discord_role_mapping">
	<?php
	if ( isset( $ets_discord_roles ) ) {
		echo stripslashes( esc_html( $ets_discord_roles ));}
	?>
	</textarea>
  </div>
  <div class="bottom-btn">
	<button type="submit" name="submit" value="ets_submit" class="ets-submit ets-btn-submit ets-bg-green">
	  <?php echo __( 'Save Settings', 'ultimate-member-discord-add-on' ); ?>
	</button>
	<button id="revertMapping" name="flush" class="ets-submit ets-btn-submit ets-bg-red">
	  <?php echo __( 'Flush Mappings', 'ultimate-member-discord-add-on' ); ?>
	</button>
  </div>
</form>