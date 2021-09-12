<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.expresstechsoftwares.com
 * @since             1.0.0
 * @package           Ultimate_Member_Discord_Add_On
 *
 * @wordpress-plugin
 * Plugin Name:       Ultimate member discord add on
 * Plugin URI:        https://www.expresstechsoftwares.com/ultimate-member-discord-addon
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            ExpressTech Softwares Solutions Pvt Ltd
 * Author URI:        https://www.expresstechsoftwares.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ultimate-member-discord-add-on
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ULTIMATE_MEMBER_DISCORD_ADD_ON_VERSION', '1.0.0' );

/**
 * Define plugin directory path
 */
define( 'ULTIMATE_MEMBER_DISCORD_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-ultimate-member-discord-add-on-activator.php
 */
function activate_ultimate_member_discord_add_on() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ultimate-member-discord-add-on-activator.php';
	Ultimate_Member_Discord_Add_On_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-ultimate-member-discord-add-on-deactivator.php
 */
function deactivate_ultimate_member_discord_add_on() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-ultimate-member-discord-add-on-deactivator.php';
	Ultimate_Member_Discord_Add_On_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_ultimate_member_discord_add_on' );
register_deactivation_hook( __FILE__, 'deactivate_ultimate_member_discord_add_on' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-ultimate-member-discord-add-on.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_ultimate_member_discord_add_on() {

	$plugin = new Ultimate_Member_Discord_Add_On();
	$plugin->run();

}
run_ultimate_member_discord_add_on();
