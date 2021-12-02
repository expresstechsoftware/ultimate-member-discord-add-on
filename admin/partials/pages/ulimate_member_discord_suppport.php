<?php
  $currUserName = '';
  $currentUser  = wp_get_current_user();
if ( $currentUser ) {
	$currUserName = sanitize_text_field( trim( $currentUser->user_login ) );
}
?>
<div class="contact-form ">
  <form accept="#" method="post">
	  <div class="ets-container">
		<div class="top-logo-title">
		  <img src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) . '../../images/ets-logo.png '); ?>" class="img-fluid company-logo" alt="">
		  <h1><?php echo __( 'ExpressTech Softwares Solutions Pvt. Ltd.', 'ultimate-member-discord-add-on' ); ?></h1>
		  <p><?php echo __( 'ExpressTech Software Solution Pvt. Ltd. is the leading Enterprise WordPress development company.', 'ultimate-member-discord-add-on' ); ?><br>
		  <?php echo __( 'Contact us for any WordPress Related development projects.', 'ultimate-member-discord-add-on' ); ?></p>
		</div>
		<div class="form-fields-box ">
		  <div class="ets-row ets-mt-5 ets-align-items-center">
			<div class="ets-col-7 ets-offset-md-1">
			  <div class="contact-fields pr-100">
				<div class="ets-form-group">
				  <label><?php echo __( 'Full Name', 'ultimate-member-discord-add-on' ); ?></label>
				  <input type="text" name="ets_user_name" value="<?php echo esc_attr($currUserName); ?>" class="form-control contact-input" placeholder="Write Your Full Name">
				  <?php wp_nonce_field( 'ets_um_discord_get_support', 'ets_um_discord_get_support_nonce' ); ?>
				</div>
				<div class="ets-form-group">
				  <label><?php echo __( 'Contact Email', 'ultimate-member-discord-add-on' ); ?></label>
				  <input type="text" name="ets_user_email" class="form-control contact-input" value="<?php echo esc_attr(get_option( 'admin_email' )); ?>" placeholder="Write Your Email">
				</div>
				<div class="ets-form-group">
				  <label><?php echo __( 'Subject', 'ultimate-member-discord-add-on' ); ?></label>
				  <input type="text" name="ets_support_subject" class="form-control contact-input" placeholder="Write Your Subject" required="">
				</div>
				<div class="ets-form-group">
				  <label><?php echo __( 'Message', 'ultimate-member-discord-add-on' ); ?></label>
				  <textarea name="ets_support_msg" class="form-control contact-textarea" required=""></textarea>
				</div>
				<div class="submit-btn d-flex align-items-center w-100 pt-3">
				  <input type="submit" name="sendmail-support" id="sendmail-support" class="btn btn-submit ets-bg-green" value="Submit">                  
				  <a href="skype:ravi.soni971?chat" class="btn btn-skype ml-auto"><?php echo __( 'Skype', 'ultimate-member-discord-add-on' ); ?></a>
				</div>
			  </div>
			</div>
			<div class="ets-col-3">
			  <div class="right-side-box">
				<div class="contact-details d-inline-block w-100 mb-4">
				  <div class="top-icon-title d-flex align-items-center w-100">
					<i class="fas fa-envelope title-icon fa-lg fa-inverse" aria-hidden="true"></i>
					<p><?php echo __( 'Email', 'ultimate-member-discord-add-on' ); ?></p>
				  </div>
				  <div class="contact-body mt-3">
					<p><a href="mailto:contact@expresstechsoftwares.com">contact@expresstechsoftwares.com</a></p>
					<p><a href="mailto:business@expresstechsoftwares.com">business@expresstechsoftwares.com</a></p>
				  </div>
				</div>
				<div class="contact-details d-inline-block w-100 mb-4">
				  <div class="top-icon-title d-flex align-items-center w-100">
					<i class="fab fa-skype title-icon fa-lg fa-inverse" aria-hidden="true"></i>
					<p><?php echo __( 'Skype', 'ultimate-member-discord-add-on' ); ?></p>
				  </div>
				  <div class="contact-body mt-3">
					<p>ravi.soni971</p>
				  </div>
				</div>
				<div class="contact-details d-inline-block w-100">
				  <div class="top-icon-title d-flex align-items-center w-100">
					<i class="fab fa-whatsapp title-icon fa-lg fa-inverse" aria-hidden="true"></i>
					<p><?php echo __( 'Whatsapp / Phone', 'ultimate-member-discord-add-on' ); ?></p>
				  </div>
				  <div class="contact-body mt-3">
					<p>+91-9806724185</p>
				  </div>
				</div>
			  </div>
			</div>
		  </div>
		</div>
	  </div>
  </form>
</div>
<?php
	/**
	 * Send mail to support form current user
	 *
	 * @param NONE
	 * @return NONE
	 */

		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}

		if ( isset( $_POST['sendmail-support'] ) ) {
                    
                    
			// Check for nonce security
			if ( ! wp_verify_nonce( $_POST['ets_um_discord_get_support_nonce'], 'ets_um_discord_get_support' ) ) {
				wp_send_json_error( 'You do not have sufficient rights', 403 );
				exit();
			}
                        
			$etsUserName  = isset( $_POST['ets_user_name'] ) ? sanitize_text_field( trim( $_POST['ets_user_name'] ) ) : '';
			$etsUserEmail = isset( $_POST['ets_user_email'] ) ? sanitize_text_field( trim( $_POST['ets_user_email'] ) ) : '';
			$message      = isset( $_POST['ets_support_msg'] ) ? sanitize_text_field( trim( $_POST['ets_support_msg'] ) ) : '';
			$sub          = isset( $_POST['ets_support_subject'] ) ? sanitize_text_field( trim( $_POST['ets_support_subject'] ) ) : '';

			if ( $etsUserName && $etsUserEmail && $message && $sub ) {

				$subject   = $sub;
				$to        = 'contact@expresstechsoftwares.com';
				$content   = 'Name: ' . $etsUserName . '<br>';
				$content  .= 'Contact Email: ' . $etsUserEmail . '<br>';
				$content  .= 'Message: ' . $message;
				$headers   = array();
				$blogemail = get_bloginfo( 'admin_email' );
				$headers[] = 'From: ' . get_bloginfo( 'name' ) . ' <' . $blogemail . '>' . "\r\n";
				$mail      = wp_mail( $to, $subject, $content, $headers );

				if ( $mail ) {
					?>
						<div class="notice notice-success is-dismissible support-success-msg">
							<p><?php echo __( 'Your request have been successfully submitted!', 'ultimate-member-discord-add-on' ); ?></p>
						</div>
					<?php
				}
			}
		}
	
