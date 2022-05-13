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
		$_ets_ultimatemember_discord_username = sanitize_text_field( trim( get_user_meta( $user_id, '_ets_ultimatemember_discord_username', true ) ) );
		$default_role                   = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_default_role_id' ) ) );
		$ets_ultimatemember_discord_role_mapping = json_decode( get_option( 'ets_ultimatemember_discord_role_mapping' ), true );
		$all_roles                      = unserialize( get_option( 'ets_ultimatemember_discord_all_roles' ) );
		$roles_color = unserialize( get_option( 'ets_ultimatemember_discord_roles_color' ) );
		$ets_ultimatemember_discord_connect_button_bg_color    = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_connect_button_bg_color' ) ) );
		$ets_ultimatemember_discord_disconnect_button_bg_color = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_disconnect_button_bg_color' ) ) );                
		$ets_ultimatemember_discord_disconnect_button_text = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_disconnect_button_text' ) ) );                
		$ets_ultimatemember_discord_loggedin_button_text = sanitize_text_field( trim( get_option( 'ets_ultimatemember_discord_loggedin_button_text' ) ) );                
		$curr_level_id                  = ets_ultimatemember_discord_get_current_level_id( $user_id );
		$mapped_role_name               = '';
		if ( $curr_level_id && is_array( $all_roles ) ) {
			if ( is_array( $ets_ultimatemember_discord_role_mapping ) && array_key_exists( 'ultimate-member_level_id_' . $curr_level_id, $ets_ultimatemember_discord_role_mapping ) ) {
				$mapped_role_id = $ets_ultimatemember_discord_role_mapping[ 'ultimate-member_level_id_' . $curr_level_id ];
				if ( array_key_exists( $mapped_role_id, $all_roles ) ) {
					$mapped_role_name .= '<span> <i style="background-color:#' . dechex( $roles_color[ $mapped_role_id ] ) . '"></i>' . $all_roles[ $mapped_role_id ] . '</span>';                                        
				}
			}
		}
		$default_role_name = '';
		if ( $default_role != 'none' && is_array( $all_roles ) && array_key_exists( $default_role, $all_roles ) ) {
			$default_role_name = '<span><i style="background-color:#' . dechex( $roles_color[ $default_role ] ) . '"></i> ' . $all_roles[ $default_role ] . '</span>';                        
		}
                $restrictcontent_discord = '';
		if ( ultimatemember_discord_check_saved_settings_status() ) {

			if ( $access_token ) {
				$disconnect_btn_bg_color = 'style="background-color:' . $ets_ultimatemember_discord_disconnect_button_bg_color . '"'; 				
                                $restrictcontent_discord .= '<div class="um-field um-field-text">';
                                $restrictcontent_discord .='<div class="um-field-label">';
				$restrictcontent_discord .= '<label class="ets-connection-lbl">' . esc_html__( 'Discord connection', 'ultimate-member-discord-add-on' ) . '</label>';
                                $restrictcontent_discord .= '</div>';
                                $restrictcontent_discord .= '<div class="um-field-area">';
				$restrictcontent_discord .= '<a href="#" class="ets-btn ultimate-member-btn-disconnect" ' . $disconnect_btn_bg_color . '  id="ultimate-member-disconnect-discord" data-user-id="'. esc_attr( $user_id ) .'">'. esc_html__( $ets_ultimatemember_discord_disconnect_button_text, 'ultimate-member-discord-add-on' ) . Ultimate_Member_Discord_Add_On::get_discord_logo_white() . '</a>';
				$restrictcontent_discord .= '<p>' . esc_html__( sprintf( 'Connected account: %s', $_ets_ultimatemember_discord_username ), 'ultimate-member-discord-add-on' ) . '</p>';
				$restrictcontent_discord  = ets_ultimatemember_discord_roles_assigned_message ( $mapped_role_name, $default_role_name, $restrictcontent_discord );
				$restrictcontent_discord .= '<span class="ets-spinner"></span>';
                                $restrictcontent_discord .= '</div>';
                                $restrictcontent_discord .= '</div>';
				
		
                        } else {
				$connect_btn_bg_color = 'style="background-color:' . $ets_ultimatemember_discord_connect_button_bg_color . '"';				
                                $restrictcontent_discord .= '<div class="um-field um-field-text">';
                                $restrictcontent_discord .= '<div class="um-field-label">';
				$restrictcontent_discord .= '<label class="ets-connection-lbl">' . esc_html__( 'Discord connection', 'ultimate-member-discord-add-on' ) .'</label>';
                                $restrictcontent_discord .= '</div>';
                                $restrictcontent_discord .= '<div class="um-field-area">';
				$restrictcontent_discord .= '<a href="?action=discord-login" class="ultimate-member-btn-connect ets-btn"' . $connect_btn_bg_color . ' >' . esc_html__( $ets_ultimatemember_discord_loggedin_button_text, 'ultimate-member-discord-add-on' ) . Ultimate_Member_Discord_Add_On::get_discord_logo_white() . '</a>';
                                $restrictcontent_discord .= '</div>';
				$restrictcontent_discord  = ets_ultimatemember_discord_roles_assigned_message ( $mapped_role_name, $default_role_name, $restrictcontent_discord );                                   
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
