<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://example.com/
 * @since      1.0.0
 *
 * @package    Nutri_Login
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Placeholder for uninstall actions.
// For example, clean up options:
// delete_option( 'nutri_login_installed' );
// delete_option( 'nutri_login_db_version' );

// Future: Drop custom tables if an option is set to do so.
// global $wpdb;
// $table_nutricionistas = $wpdb->prefix . 'nutricionistas';
// $table_pacientes = $wpdb->prefix . 'pacientes';
// $wpdb->query( "DROP TABLE IF EXISTS {$table_pacientes}" );
// $wpdb->query( "DROP TABLE IF EXISTS {$table_nutricionistas}" );

?>
