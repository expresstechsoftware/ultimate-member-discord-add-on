<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.expresstechsoftwares.com
 * @since      1.0.0
 *
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/public/partials
 */


Class Ultimate_Member_Discord_Add_On_Public_Display{
    
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;    
    
        public function __construct( $plugin_name ) {
            
		$this->plugin_name = $plugin_name;            

        }
        /**
        * Add button to make connection in between user and discord
        *
        * @param NONE
        * @return NONE
        */
        public function ets_ultimatemember_discord_add_connect_discord_button(){
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}
		$user_id = sanitize_text_field( trim( get_current_user_id() ) );

		$access_token = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_access_token', true ) ) );

		$default_role                   = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_default_role_id' ) ) );
		$ets_ultimatemember_discord_role_mapping = json_decode( get_option( 'ets_ultimatemember_discord_role_mapping' ), true );
		$all_roles                      = unserialize( get_option( 'ets_ultimatemember_discord_all_roles' ) );
		$curr_level_id                  = ets_ultimatemember_discord_get_current_level_id( $user_id );
		$mapped_role_name               = '';
		if ( $curr_level_id && is_array( $all_roles ) ) {
			if ( is_array( $ets_ultimatemember_discord_role_mapping ) && array_key_exists( 'ultimate-member_level_id_' . $curr_level_id, $ets_ultimatemember_discord_role_mapping ) ) {
				$mapped_role_id = $ets_ultimatemember_discord_role_mapping[ 'ultimate-member_level_id_' . $curr_level_id ];
				if ( array_key_exists( $mapped_role_id, $all_roles ) ) {
					$mapped_role_name = $all_roles[ $mapped_role_id ];
				}
			}
		}
		$default_role_name = '';
		if ( $default_role != 'none' && is_array( $all_roles ) && array_key_exists( $default_role, $all_roles ) ) {
			$default_role_name = $all_roles[ $default_role ];
		}
                $restrictcontent_discord = '';
		if ( ultimatemember_discord_check_saved_settings_status() ) {

			if ( $access_token ) {
				
                                $restrictcontent_discord .= '<div class="um-field um-field-text">';
                                $restrictcontent_discord .='<div class="um-field-label">';
				$restrictcontent_discord .= '<label class="ets-connection-lbl">' . esc_html__( 'Discord connection', 'ultimate-member-discord-add-on' ) . '</label>';
                                $restrictcontent_discord .= '</div>';
                                $restrictcontent_discord .= '<div class="um-field-area">';
				$restrictcontent_discord .= '<a href="#" class="ets-btn ultimate-member-btn-disconnect" id="ultimate-member-disconnect-discord" data-user-id="'. esc_attr( $user_id ) .'">'. esc_html__( 'Disconnect From Discord ', 'ultimate-member-discord-add-on' ) . '<i class="fab fa-discord"></i></a>';
				$restrictcontent_discord .= '<span class="ets-spinner"></span>';
                                $restrictcontent_discord .= '</div>';
                                $restrictcontent_discord .= '</div>';
				
		
                        } else {
				
                                $restrictcontent_discord .= '<div class="um-field um-field-text">';
                                $restrictcontent_discord .= '<div class="um-field-label">';
				$restrictcontent_discord .= '<label class="ets-connection-lbl">' . esc_html__( 'Discord connection', 'ultimate-member-discord-add-on' ) .'</label>';
                                $restrictcontent_discord .= '</div>';
                                $restrictcontent_discord .= '<div class="um-field-area">';
				$restrictcontent_discord .= '<a href="?action=discord-login" class="ultimate-member-btn-connect ets-btn" >' . esc_html__( 'Connect To Discord', 'ultimate-member-discord-add-on' ) . '<i class="fab fa-discord"></i></a>';
                                $restrictcontent_discord .= '</div>';
				if ( $mapped_role_name ) {
					$restrictcontent_discord .= '<p class="ets_assigned_role">';
					
					$restrictcontent_discord .= __( 'Following Roles will be assigned to you in Discord: ', 'ultimate-member-discord-add-on' );
					$restrictcontent_discord .= esc_html( $mapped_role_name );
					if ( $default_role_name ) {
						$restrictcontent_discord .= ', ' . esc_html( $default_role_name ); 
                                                
                                        }
					
					$restrictcontent_discord .= '</p>';
				 } elseif( $default_role_name ) {
                                        $restrictcontent_discord .= '<p class="ets_assigned_role">';
					
					$restrictcontent_discord .= esc_html__( 'Following Role will be assigned to you in Discord: ', 'ultimate-member-discord-add-on' );
                                        $restrictcontent_discord .= esc_html( $default_role_name ); 
					
                                        $restrictcontent_discord .= '</p>';
                                         
                                 }
                                   
                                $restrictcontent_discord .= '</div>';
			
			}
		}
                wp_enqueue_style( $this->plugin_name );
                wp_enqueue_script( $this->plugin_name ); 
                
                return $restrictcontent_discord ;
        }
    
	/**
	 * Show status of Ultimate Member connection with Discord user
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function ets_ultimatemember_show_discord_button() {
		echo do_shortcode( '[restrictcontent_discord]' );
	}
    
}
