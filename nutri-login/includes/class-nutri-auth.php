<?php
/**
 * Handles authentication for the Nutri Login plugin.
 *
 * @package    Nutri_Login
 * @subpackage Nutri_Login/includes
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

class Nutri_Auth {

    public function __construct() {
        add_action( 'init', [ $this, 'start_session' ], 1 );
        add_action( 'init', [ $this, 'handle_login_submission' ] );
        add_action( 'template_redirect', [ $this, 'protect_panel_pages' ] );
        add_action( 'init', [ $this, 'handle_logout' ] );
    }

    public function handle_logout() {
        if ( isset( $_GET['nutri_logout'] ) && $_GET['nutri_logout'] === 'true' ) {
            // Verify nonce
            if ( ! isset( $_GET['nutri_logout_nonce'] ) || ! wp_verify_nonce( $_GET['nutri_logout_nonce'], 'nutri_logout_action' ) ) {
                // Potentially redirect to login with an error, or just to home.
                // For simplicity, redirect to login page without a specific error for nonce fail on logout.
                wp_redirect( self::get_login_page_url() );
                exit;
            }

            // Clear specific session variables
            unset( $_SESSION['nutri_user_id'] );
            unset( $_SESSION['nutri_user_email'] );
            unset( $_SESSION['nutri_user_name'] );
            unset( $_SESSION['nutri_user_type'] );
            unset( $_SESSION['nutri_logged_in_at'] );

            // Optional: A more complete cleanup if the session is only for this plugin.
            // session_unset(); // Removes all session variables
            // session_regenerate_id(true); // Regenerate session ID to prevent fixation

            // Hook for actions after successful logout if needed
            // do_action('nutri_logout_successful');

            // Redirect to the login page (or home page)
            wp_redirect( self::get_login_page_url_with_error('logged_out') ); // Add a success message
            exit;
        }
    }

    // Add this method to Nutri_Auth class
    public static function get_login_page_url() {
        // This should ideally be an option saved in the DB.
        // For now, assuming the login page has the slug 'login'.
        // If no such page, redirect to home_url and hope the shortcode is there or handle error.
        $login_page = get_page_by_path('login');
        if ($login_page) {
            return get_permalink($login_page->ID);
        }
        // Fallback if /login page doesn't exist - this is not ideal
        // A settings page would define this properly.
        return add_query_arg('login_error', 'no_login_page', home_url('/'));
    }

    // Add this helper method to Nutri_Auth class
    public static function get_login_page_url_with_error($error_code) {
        $login_url = self::get_login_page_url();
        return add_query_arg( 'login_error', $error_code, $login_url );
    }

    public function protect_panel_pages() {
        // Define panel slugs - these pages must exist in WordPress
        $panel_nutricionista_slug = 'panel-nutricionista';
        $panel_paciente_slug = 'panel-paciente';

        // Check if we are on one of the panel pages
        if ( is_page( $panel_nutricionista_slug ) || is_page( $panel_paciente_slug ) ) {

            if ( ! self::is_user_logged_in() ) {
                // Not logged in, redirect to login page
                wp_redirect( self::get_login_page_url() );
                exit;
            }

            $user_type = self::get_logged_in_user_type();

            if ( is_page( $panel_nutricionista_slug ) && $user_type !== 'nutricionista' ) {
                // Logged in, but wrong user type for nutricionista panel
                // Redirect to login or a generic 'access denied' page, or their own panel if applicable
                wp_redirect( self::get_login_page_url_with_error('access_denied') );
                exit;
            }

            if ( is_page( $panel_paciente_slug ) && $user_type !== 'paciente' ) {
                // Logged in, but wrong user type for paciente panel
                wp_redirect( self::get_login_page_url_with_error('access_denied') );
                exit;
            }

            // If we reach here, user is logged in and has the correct type for the panel.
            // Optionally, prevent caching of these panel pages
            nocache_headers();
        }
    }

    public function start_session() {
        if ( ! session_id() && ! headers_sent() ) {
            session_start();
        }
    }

    public function handle_login_submission() {
        if ( isset( $_POST['nutri_action'] ) && $_POST['nutri_action'] === 'login' ) {
            // Verify nonce
            if ( ! isset( $_POST['nutri_login_nonce'] ) || ! wp_verify_nonce( $_POST['nutri_login_nonce'], 'nutri_login_action' ) ) {
                wp_redirect( add_query_arg( 'login_error', 'nonce_fail', wp_get_referer() ?: home_url('/') ) );
                exit;
            }

            $email = isset( $_POST['nutri_email'] ) ? sanitize_email( $_POST['nutri_email'] ) : '';
            $password = isset( $_POST['nutri_password'] ) ? $_POST['nutri_password'] : ''; // Do not sanitize password before check

            if ( empty( $email ) || empty( $password ) ) {
                wp_redirect( add_query_arg( 'login_error', 'empty_fields', wp_get_referer() ?: home_url('/') ) );
                exit;
            }

            if ( ! is_email( $email ) ) {
                wp_redirect( add_query_arg( 'login_error', 'invalid_email', wp_get_referer() ?: home_url('/') ) );
                exit;
            }

            global $wpdb;
            $user_data = null;
            $user_type = null;

            // Attempt to log in as a nutricionista first
            $table_nutricionistas = Nutri_DB::get_nutricionistas_table_name();
            $nutricionista = $wpdb->get_row( $wpdb->prepare( "SELECT id, correo, contrasena, nombre FROM $table_nutricionistas WHERE correo = %s", $email ) );

            if ( $nutricionista && wp_check_password( $password, $nutricionista->contrasena, $nutricionista->id ) ) {
                $user_data = $nutricionista;
                $user_type = 'nutricionista';
            } else {
                // If not a nutricionista, attempt to log in as a paciente
                $table_pacientes = Nutri_DB::get_pacientes_table_name();
                $paciente = $wpdb->get_row( $wpdb->prepare( "SELECT id, correo, contrasena, nombre FROM $table_pacientes WHERE correo = %s", $email ) );

                if ( $paciente && wp_check_password( $password, $paciente->contrasena, $paciente->id ) ) {
                    $user_data = $paciente;
                    $user_type = 'paciente';
                }
            }

            if ( $user_data && $user_type ) {
                // Authentication successful
                $_SESSION['nutri_user_id'] = $user_data->id;
                $_SESSION['nutri_user_email'] = $user_data->correo;
                $_SESSION['nutri_user_name'] = $user_data->nombre;
                $_SESSION['nutri_user_type'] = $user_type;
                $_SESSION['nutri_logged_in_at'] = time();

                // Determine redirect URL
                $redirect_url = home_url('/'); // Default redirect
                if ( $user_type === 'nutricionista' ) {
                    $redirect_url = home_url( '/panel-nutricionista/' ); // Ensure these pages exist
                } elseif ( $user_type === 'paciente' ) {
                    $redirect_url = home_url( '/panel-paciente/' ); // Ensure these pages exist
                }

                // Hook for actions after successful login if needed
                // do_action('nutri_login_successful', $user_data->id, $user_type);

                wp_redirect( $redirect_url );
                exit;
            } else {
                // Authentication failed
                wp_redirect( add_query_arg( 'login_error', 'invalid_credentials', wp_get_referer() ?: home_url('/') ) );
                exit;
            }
        }
    }

    public static function is_user_logged_in() {
        // Helper function to check login status
        return isset( $_SESSION['nutri_user_id'] );
    }

    public static function get_logged_in_user_type() {
         if (self::is_user_logged_in() && isset($_SESSION['nutri_user_type'])) {
             return $_SESSION['nutri_user_type'];
         }
         return null;
    }

    public static function get_logged_in_user_id() {
         if (self::is_user_logged_in() && isset($_SESSION['nutri_user_id'])) {
             return $_SESSION['nutri_user_id'];
         }
         return null;
    }
}
?>
