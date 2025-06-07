<?php
/**
 * Frontend login form view.
 *
 * @package    Nutri_Login
 * @subpackage Nutri_Login/public/views
 */
?>
<div class="nutri-login-form-container">
    <form id="nutri-login-form" method="POST" action="">
        <h3><?php esc_html_e( 'Login', 'nutri-login' ); ?></h3>

        <?php
        // Display error messages if any
        if ( isset( $_GET['login_error'] ) ) {
            echo '<p class="nutri-login-error">';
            switch ( $_GET['login_error'] ) {
                case 'empty_fields':
                    esc_html_e( 'Email and password are required.', 'nutri-login' );
                    break;
                case 'invalid_credentials':
                    esc_html_e( 'Invalid email or password.', 'nutri-login' );
                    break;
                case 'invalid_email':
                    esc_html_e( 'Invalid email format.', 'nutri-login' );
                    break;
                case 'nonce_fail':
                    esc_html_e( 'Security check failed. Please try again.', 'nutri-login' );
                    break;
                 case 'access_denied':
                     esc_html_e( 'You do not have permission to access that page. Please login with appropriate credentials.', 'nutri-login' );
                     break;
                 case 'no_login_page': // Error if the login page itself isn't found by the redirect logic
                     esc_html_e( 'Login page not configured. Please contact administrator.', 'nutri-login' );
                     break;
                 case 'logged_out':
                     // This message is a success message, so ideally use a different class.
                     // For now, it will appear like an error, but the text indicates success.
                     // A better implementation would have different notice types on the login form.
                     echo '</p><p class="nutri-login-success">'; // Close previous error p, start new success p
                     esc_html_e( 'You have been successfully logged out.', 'nutri-login' );
                     break;
                default:
                    esc_html_e( 'An unknown login error occurred.', 'nutri-login' );
            }
            echo '</p>';
        }
        ?>

        <p>
            <label for="nutri_email"><?php esc_html_e( 'Email', 'nutri-login' ); ?></label>
            <input type="email" name="nutri_email" id="nutri_email" required />
        </p>
        <p>
            <label for="nutri_password"><?php esc_html_e( 'Password', 'nutri-login' ); ?></label>
            <input type="password" name="nutri_password" id="nutri_password" required />
        </p>
        <p>
            <?php wp_nonce_field( 'nutri_login_action', 'nutri_login_nonce' ); ?>
            <input type="hidden" name="nutri_action" value="login" />
            <input type="submit" name="nutri_login_submit" value="<?php esc_attr_e( 'Log In', 'nutri-login' ); ?>" />
        </p>
    </form>
</div>
