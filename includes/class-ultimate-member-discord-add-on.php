<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.expresstechsoftwares.com
 * @since      1.0.0
 *
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Ultimate_Member_Discord_Add_On
 * @subpackage Ultimate_Member_Discord_Add_On/includes
 * @author     ExpressTech Softwares Solutions Pvt Ltd <contact@expresstechsoftwares.com>
 */
class Ultimate_Member_Discord_Add_On {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Ultimate_Member_Discord_Add_On_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ULTIMATE_MEMBER_DISCORD_ADD_ON_VERSION' ) ) {
			$this->version = ULTIMATE_MEMBER_DISCORD_ADD_ON_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'ultimate-member-discord-add-on';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Ultimate_Member_Discord_Add_On_Loader. Orchestrates the hooks of the plugin.
	 * - Ultimate_Member_Discord_Add_On_i18n. Defines internationalization functionality.
	 * - Ultimate_Member_Discord_Add_On_Admin. Defines all hooks for the admin area.
	 * - Ultimate_Member_Discord_Add_On_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
            
                
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'libraries/action-scheduler/action-scheduler.php';
                
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ultimate-member-discord-add-on-logs.php';
            
                /**
                 * 
                 */
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/functions.php';

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ultimate-member-discord-add-on-loader.php';
                
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-ultimate-member-discord-add-on-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-ultimate-member-discord-add-on-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-ultimate-member-discord-add-on-public.php';
                require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/ultimate-member-discord-add-on-public-display.php';

		$this->loader = new Ultimate_Member_Discord_Add_On_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Ultimate_Member_Discord_Add_On_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Ultimate_Member_Discord_Add_On_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Ultimate_Member_Discord_Add_On_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'ets_ultimatemember_add_settings_menu' );
                $this->loader->add_action( 'admin_post_ultimatemember_discord_application_settings', $plugin_admin, 'ets_ultimatemember_discord_application_settings' );
                $this->loader->add_action( 'admin_post_ultimatemember_discord_save_role_mapping', $plugin_admin, 'ets_ultimatemember_discord_save_role_mapping' );
                $this->loader->add_action( 'admin_post_ultimatemember_discord_save_advance_settings', $plugin_admin, 'ets_ultimatemember_discord_save_advance_settings' );
                $this->loader->add_action( 'wp_ajax_ets_ultimatemember_discord_load_discord_roles', $plugin_admin, 'ets_ultimatemember_discord_load_discord_roles' );
                
               
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Ultimate_Member_Discord_Add_On_Public( $this->get_plugin_name(), $this->get_version()  );
                
                $plugin_public_display = new Ultimate_Member_Discord_Add_On_Public_Display();
                
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_shortcode( 'restrictcontent_discord', $plugin_public_display, 'ets_ultimatemember_discord_add_connect_discord_button' );
		$this->loader->add_action( 'um_after_account_general', $plugin_public_display, 'ets_ultimatemember_show_discord_button' );                
                $this->loader->add_action( 'init', $plugin_public, 'ets_ultimatemember_discord_api_callback' );
                $this->loader->add_action( 'wp_ajax_disconnect_from_discord', $plugin_public, 'ets_ultimatemember_discord_disconnect_from_discord' );
                $this->loader->add_action( 'ets_ultimatemember_discord_as_schedule_delete_member', $plugin_public, 'ets_ultimatemember_discord_as_handler_delete_member_from_guild', 10, 3 );
                $this->loader->add_action( 'ets_ultimatemember_discord_as_schedule_delete_role', $plugin_public, 'ets_ultimatemember_discord_as_handler_delete_memberrole', 10, 3 );
                $this->loader->add_action( 'ets_ultimatemember_discord_as_handle_add_member_to_guild', $plugin_public, 'ets_ultimatemember_discord_as_handler_add_member_to_guild', 10, 3 );
                $this->loader->add_action( 'ets_ultimatemember_discord_as_send_dm', $plugin_public, 'ets_ultimatemember_discord_handler_send_dm', 10, 3 );
                $this->loader->add_action( 'ets_ultimatemember_discord_as_schedule_member_put_role', $plugin_public, 'ets_ultimatemember_discord_as_handler_put_memberrole', 10, 3 );
                
               
               
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Ultimate_Member_Discord_Add_On_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
