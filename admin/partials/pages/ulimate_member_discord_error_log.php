<div class="error-log">
<?php
	$uuid     = get_option( 'ets_ultimatemember_discord_uuid_file_name' );
	$filename = $uuid . Ultimate_Member_Discord_Add_On_Logs::$log_file_name;
	$handle   = fopen( WP_CONTENT_DIR . '/' . $filename, 'a+' );
while ( ! feof( $handle ) ) {
	echo esc_html( fgets( $handle ) ) . '<br />';
}
	fclose( $handle );
?>
</div>
<div class="ultimate-member-clrbtndiv">
	<div class="form-group">
		<input type="button" class="ets-ultimate-member-clrbtn ets-submit ets-bg-red" id="ets-ultimate-member-clrbtn" name="ultimate-member_clrbtn" value="Clear Logs !">
		<span class="clr-log spinner" ></span>
	</div>
	<div class="form-group">
		<input type="button" class="ets-submit ets-bg-green" value="Refresh" onClick="window.location.reload()">
	</div>
	<div class="form-group">
		<a href="<?php echo esc_url( content_url( '/' ) . $filename ); ?>" class="ets-submit ets-ultimate-member-bg-download" download><?php esc_html_e( 'Download', 'ultimate-member-discord-add-on' ); ?></a>
	</div>
	<div class="form-group">
			<a href="<?php echo esc_url( get_admin_url( '', 'tools.php' ) ) . '?page=action-scheduler&status=pending&s=ultimatemember'; ?>" class="ets-submit ets-ultimate-member-bg-scheduled-actions"><?php esc_html_e( 'API Queue', 'ultimate-member-discord-add-on' ); ?></a>
	</div>    
</div>
