<?php
/**
 * Plugin Name:       Nutri Login
 * Plugin URI:        https://example.com/plugins/nutri-login/
 * Description:       Custom login system for nutritionists and patients.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       nutri-login
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'NUTRI_LOGIN_VERSION', '1.0.0' );
define( 'NUTRI_LOGIN_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NUTRI_LOGIN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once NUTRI_LOGIN_PLUGIN_DIR . 'includes/class-nutri-db.php';
require_once NUTRI_LOGIN_PLUGIN_DIR . 'admin/class-nutri-admin-menu.php';
require_once NUTRI_LOGIN_PLUGIN_DIR . 'includes/nutri-shortcodes.php';
require_once NUTRI_LOGIN_PLUGIN_DIR . 'includes/class-nutri-auth.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-nutri-login-activator.php
 */
function activate_nutri_login() {
    require_once NUTRI_LOGIN_PLUGIN_DIR . 'includes/class-nutri-db.php'; // Ensure it's loaded if not already
    Nutri_DB::create_tables();

    if ( ! get_option( 'nutri_login_installed' ) ) {
        update_option( 'nutri_login_installed', time() );
    }
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-nutri-login-deactivator.php
 */
function deactivate_nutri_login() {
    // Placeholder for deactivation code
    delete_option( 'nutri_login_installed' );
    delete_option( 'nutri_login_db_version' );
}

register_activation_hook( __FILE__, 'activate_nutri_login' );
register_deactivation_hook( __FILE__, 'deactivate_nutri_login' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_nutri_login() {
    if ( is_admin() ) { // Only load admin classes if in admin area
        new Nutri_Admin_Menu();
    }
    // Always load shortcodes for frontend and backend (e.g. page builders)
    new Nutri_Shortcodes();
    new Nutri_Auth(); // Instantiate the authentication handler
}
run_nutri_login(); // Call the function to kick things off

?>
