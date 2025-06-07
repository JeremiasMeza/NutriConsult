<?php
/**
 * Handles database operations for the Nutri Login plugin.
 *
 * @package    Nutri_Login
 * @subpackage Nutri_Login/includes
 * @author     Your Name <email@example.com>
 */
class Nutri_DB {

    /**
     * Get the nutricionistas table name.
     *
     * @return string
     */
    public static function get_nutricionistas_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'nutricionistas';
    }

    /**
     * Get the pacientes table name.
     *
     * @return string
     */
    public static function get_pacientes_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'pacientes';
    }

    /**
     * Create custom database tables on plugin activation.
     */
    public static function create_tables() {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $charset_collate = $wpdb->get_charset_collate();
        $table_nutricionistas = self::get_nutricionistas_table_name();
        $table_pacientes = self::get_pacientes_table_name();

        $sql_nutricionistas = "CREATE TABLE $table_nutricionistas (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL,
            correo VARCHAR(255) NOT NULL,
            contrasena VARCHAR(255) NOT NULL,
            fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY correo (correo)
        ) $charset_collate;";

        $sql_pacientes = "CREATE TABLE $table_pacientes (
            id BIGINT(20) NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(255) NOT NULL,
            correo VARCHAR(255) NOT NULL,
            contrasena VARCHAR(255) NOT NULL,
            nutricionista_id BIGINT(20) DEFAULT NULL,
            fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY correo (correo)
            -- No direct FK constraint for dbDelta compatibility with all MySQL versions
            -- We will handle relation logic in the application layer
        ) $charset_collate;";

        dbDelta( $sql_nutricionistas );
        dbDelta( $sql_pacientes );

        // Option to store current DB version for future upgrades
        update_option( 'nutri_login_db_version', '1.0.0' );
    }
}
?>
