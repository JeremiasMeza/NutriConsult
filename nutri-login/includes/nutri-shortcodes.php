<?php
/**
 * Defines shortcodes for the Nutri Login plugin.
 *
 * @package    Nutri_Login
 * @subpackage Nutri_Login/includes
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

class Nutri_Shortcodes {

    public function __construct() {
        add_shortcode( 'nutri_login_form', [ $this, 'render_login_form' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
    }

    /**
     * Renders the login form.
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML output for the login form.
     */
    public function render_login_form( $atts ) {
        // Prevent logged-in users from seeing the login form by default.
        // This logic might be expanded in the authentication step.
        if ( Nutri_Auth::is_user_logged_in() ) { // New check
            // User is already logged in.
            // Check user type and redirect them appropriately or show a message.
            $user_type = Nutri_Auth::get_logged_in_user_type();
            $redirect_url = home_url('/');
            if ($user_type === 'nutricionista') {
                $redirect_url = home_url('/panel-nutricionista/');
            } elseif ($user_type === 'paciente') {
                $redirect_url = home_url('/panel-paciente/');
            }
            // It might be better to redirect from the auth handler itself.
            // For now, just show a message if already logged in.

            // Generate a secure logout URL
            $logout_nonce = wp_create_nonce( 'nutri_logout_action' );
            $logout_url = add_query_arg( [
                'nutri_logout' => 'true',
                'nutri_logout_nonce' => $logout_nonce
            ], home_url('/') ); // Or self::get_login_page_url() if preferred to land there

            return '<p>' . sprintf(
                wp_kses(
                    __( 'You are already logged in as a %1$s. <a href="%2$s">Go to your panel</a> or <a href="%3$s">logout</a>.', 'nutri-login' ),
                    [ 'a' => [ 'href' => [] ], 'strong' => [] ]
                ),
                '<strong>' . esc_html($user_type) . '</strong>', // Display user type
                esc_url($redirect_url),
                esc_url($logout_url)
            ) . '</p>';
        }

        ob_start();
        // Define path to the view file
        $form_view_path = NUTRI_LOGIN_PLUGIN_DIR . 'public/views/view-login-form.php';
        if ( file_exists( $form_view_path ) ) {
            include $form_view_path;
        } else {
             echo '<p>' . esc_html__( 'Error: Login form view file not found.', 'nutri-login' ) . '</p>';
        }
        return ob_get_clean();
    }

    public function enqueue_styles() {
        // Only enqueue if the shortcode is present or on specific pages later
        // For now, let's assume it might be on any page.
        wp_enqueue_style(
            'nutri-login-public-style',
            NUTRI_LOGIN_PLUGIN_URL . 'public/assets/css/public-style.css',
            [],
            NUTRI_LOGIN_VERSION
        );
    }
}
?>
