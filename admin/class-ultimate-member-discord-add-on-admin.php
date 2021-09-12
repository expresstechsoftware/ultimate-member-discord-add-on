<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.expresstechsoftwares.com
 * @since      1.0.0
 *
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/admin
 * @author     ExpressTech Softwares Solutions Pvt Ltd <contact@expresstechsoftwares.com>
 */
class Ultimate_Member_Discord_Add_On_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ultimate_Member_Discord_Add_On_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ultimate_Member_Discord_Add_On_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name . 'discord_tabs_css', plugin_dir_url( __FILE__ ) . 'css/skeletabs.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/ultimate-member-discord-add-on-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Ultimate_Member_Discord_Add_On_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Ultimate_Member_Discord_Add_On_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script( $this->plugin_name . '-tabs-js', plugin_dir_url( __FILE__ ) . 'js/skeletabs.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/ultimate-member-discord-add-on-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );

	}

	/*
	Method to add discord setting sub-menu under top level menu of ultimate-member.
	*/
	public function ets_ultimatemember_add_settings_menu() {
		add_submenu_page( 'ultimatemember', __( 'Discord Settings', 'ultimate-member-discord-add-on' ), __( 'Discord Settings', 'ultimate-member-discord-add-on' ), 'manage_options', 'ultimatemember-discord', array( $this, 'ets_utlimatemember_discord_setting_page' ) );
	}

	/*
	Callback to Display settings page
	*/
	public function ets_utlimatemember_discord_setting_page() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		require_once ULTIMATE_MEMBER_DISCORD_PLUGIN_DIR_PATH . 'admin/partials/ultimate-member-discord-add-on-admin-display.php';
	}

}
