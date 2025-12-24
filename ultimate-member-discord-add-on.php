<?php
/**
 *
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
 * Plugin Name:       Ultimate Member Discord Add-On
 * Plugin URI:        https://www.expresstechsoftwares.com/ultimate-member-discord-addon
 * Description:       Connect Ultimate Memeber with Discord and create an engaging community/forum of your Ultimate Member online communities.
 * Version:           1.0.9
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
define( 'ULTIMATE_MEMBER_DISCORD_ADD_ON_VERSION', '1.0.9' );

/**
 * Define plugin directory path
 */
define( 'ULTIMATE_MEMBER_DISCORD_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Define plugin directory URL
 */
define( 'ULTIMATE_MEMBER_DISCORD_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

// discord API url.
define( 'ETS_UM_DISCORD_API_URL', 'https://discord.com/api/v10/' );

// discord Bot Permissions.
define( 'ULTIMATE_MEMBER_DISCORD_BOT_PERMISSIONS', 8 );

// discord api call scopes.
define( 'ETS_UM_DISCORD_OAUTH_SCOPES', 'identify email connections guilds guilds.join gdm.join rpc rpc.notifications.read rpc.voice.read rpc.voice.write rpc.activities.write bot webhook.incoming applications.builds.upload applications.builds.read applications.commands applications.store.update applications.entitlements activities.read activities.write relationships.read' );

// Follwing response codes not cosider for re-try API calls.
define( 'ETS_ULTIMATE_MEMBER_DISCORD_DONOT_RETRY_THESE_API_CODES', array( 0, 10003, 50033, 10004, 50025, 10013, 10011 ) );

// following http response codes should not get re-try. except 429 !
define( 'ETS_ULTIMATE_MEMBER_DISCORD_DONOT_RETRY_HTTP_CODES', array( 400, 401, 403, 404, 405, 502 ) );

// define group name for action scheduler actions.
define( 'ETS_UM_DISCORD_AS_GROUP_NAME', 'ets-ultimatemember-discord' );

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
