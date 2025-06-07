<?php
/**
 * Registers the admin menu pages for Nutri Login.
 *
 * @package    Nutri_Login
 * @subpackage Nutri_Login/admin
 */
class Nutri_Admin_Menu {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_plugin_admin_menu' ] );
        add_action( 'admin_post_add_nutricionista', [ $this, 'handle_add_nutricionista' ] );
        add_action( 'admin_post_edit_nutricionista', [ $this, 'handle_edit_nutricionista' ] );
        add_action( 'admin_post_delete_nutricionista', [ $this, 'handle_delete_nutricionista' ] );
        add_action( 'admin_post_add_paciente', [ $this, 'handle_add_paciente' ] );
        add_action( 'admin_post_edit_paciente', [ $this, 'handle_edit_paciente' ] );
        add_action( 'admin_post_delete_paciente', [ $this, 'handle_delete_paciente' ] );
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            __( 'Nutri Login', 'nutri-login' ), // Page title
            __( 'Nutri Login', 'nutri-login' ), // Menu title
            'manage_options', // Capability
            'nutri_login_main', // Menu slug
            [ $this, 'display_main_admin_page' ], // Function
            'dashicons-admin-users', // Icon
            76 // Position
        );

        add_submenu_page(
            'nutri_login_main', // Parent slug
            __( 'Nutricionistas', 'nutri-login' ), // Page title
            __( 'Nutricionistas', 'nutri-login' ), // Menu title
            'manage_options', // Capability
            'nutri_login_nutricionistas', // Menu slug
            [ $this, 'display_nutricionistas_page' ] // Function
        );

        add_submenu_page(
            'nutri_login_main', // Parent slug
            __( 'Pacientes', 'nutri-login' ), // Page title
            __( 'Pacientes', 'nutri-login' ), // Menu title
            'manage_options', // Capability
            'nutri_login_pacientes', // Menu slug
            [ $this, 'display_pacientes_page' ] // Function
        );
    }

    public function display_main_admin_page() {
        echo '<h1>' . esc_html__( 'Nutri Login Dashboard', 'nutri-login' ) . '</h1>';
        echo '<p>' . esc_html__( 'Welcome to the Nutri Login plugin. Manage your nutritionists and patients from the submenus.', 'nutri-login' ) . '</p>';
    }

    public function display_nutricionistas_page() {
        // Ensure this path is correct for including the view file
        $view_path = NUTRI_LOGIN_PLUGIN_DIR . 'admin/views/view-nutricionista-admin.php';
        if ( file_exists( $view_path ) ) {
            include $view_path;
        } else {
            echo '<p>' . esc_html__( 'Error: Nutricionista admin view file not found at ', 'nutri-login' ) . esc_html($view_path) . '</p>';
        }
    }

    public function display_pacientes_page() {
        $view_path = NUTRI_LOGIN_PLUGIN_DIR . 'admin/views/view-paciente-admin.php';
        if ( file_exists( $view_path ) ) {
            include $view_path;
        } else {
            echo '<p>' . esc_html__( 'Error: Paciente admin view file not found at ', 'nutri-login' ) . esc_html($view_path) . '</p>';
        }
    }

    public function handle_add_nutricionista() {
        if ( ! isset( $_POST['add_nutricionista_nonce'] ) || ! wp_verify_nonce( $_POST['add_nutricionista_nonce'], 'add_nutricionista_action' ) ) {
            wp_die( 'Security check failed' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied' );
        }

        $nombre = sanitize_text_field( $_POST['nombre'] );
        $correo = sanitize_email( $_POST['correo'] );
        $contrasena = $_POST['contrasena']; // Raw password

        if ( empty( $nombre ) || empty( $correo ) || empty( $contrasena ) ) {
            // Redirect back with error
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&message=error_empty' ) );
            exit;
        }

        if ( ! is_email( $correo ) ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&message=error_email' ) );
            exit;
        }

        global $wpdb;
        $table_name = Nutri_DB::get_nutricionistas_table_name();

        // Check if email already exists
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT correo FROM $table_name WHERE correo = %s", $correo ) );
        if ( $exists ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&message=error_exists' ) );
            exit;
        }

        $hashed_password = wp_hash_password( $contrasena );

        $result = $wpdb->insert(
            $table_name,
            [
                'nombre' => $nombre,
                'correo' => $correo,
                'contrasena' => $hashed_password,
                'fecha_registro' => current_time( 'mysql', 1 )
            ],
            [ '%s', '%s', '%s', '%s' ]
        );

        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&message=success_add' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&message=error_add' ) );
        }
        exit;
    }

    public function handle_edit_nutricionista() {
        if ( ! isset( $_POST['edit_nutricionista_nonce'] ) || ! wp_verify_nonce( $_POST['edit_nutricionista_nonce'], 'edit_nutricionista_action_' . $_POST['id'] ) ) {
            wp_die( 'Security check failed' );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied' );
        }

        $id = absint( $_POST['id'] );
        $nombre = sanitize_text_field( $_POST['nombre'] );
        $correo = sanitize_email( $_POST['correo'] );
        // Password handling: only update if a new password is provided
        $contrasena = $_POST['contrasena'];

        if ( empty( $nombre ) || empty( $correo ) ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&action=edit&id=' . $id . '&message=error_empty' ) );
            exit;
        }
        if ( ! is_email( $correo ) ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&action=edit&id=' . $id . '&message=error_email' ) );
            exit;
        }

        global $wpdb;
        $table_name = Nutri_DB::get_nutricionistas_table_name();

        // Check if email already exists for another user
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE correo = %s AND id != %d", $correo, $id ) );
        if ( $exists ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&action=edit&id=' . $id . '&message=error_exists' ) );
            exit;
        }

        $data_to_update = [
            'nombre' => $nombre,
            'correo' => $correo,
        ];
        $data_format = [ '%s', '%s' ];

        if ( ! empty( $contrasena ) ) {
            $data_to_update['contrasena'] = wp_hash_password( $contrasena );
            $data_format[] = '%s';
        }

        $result = $wpdb->update(
            $table_name,
            $data_to_update,
            [ 'id' => $id ], // WHERE clause
            $data_format,    // Format of $data_to_update
            [ '%d' ]         // Format of WHERE clause
        );

        if ( $result !== false ) { // $wpdb->update returns number of rows affected, or false on error. 0 is a valid success if no data changed.
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&message=success_edit' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&action=edit&id=' . $id . '&message=error_edit' ) );
        }
        exit;
    }

    public function handle_delete_nutricionista() {
        // Check if id is set and is a number
        if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
            wp_die( 'Item ID not provided or invalid.' );
        }
        $id = absint( $_GET['id'] );

        // Verify nonce
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'delete_nutricionista_action_' . $id ) ) {
            wp_die( 'Security check failed' );
        }

        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied' );
        }

        global $wpdb;
        $table_name = Nutri_DB::get_nutricionistas_table_name();
        $result = $wpdb->delete( $table_name, [ 'id' => $id ], [ '%d' ] );

        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&message=success_delete' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_nutricionistas&message=error_delete' ) );
        }
        exit;
    }

    public function handle_add_paciente() {
        if ( ! isset( $_POST['add_paciente_nonce'] ) || ! wp_verify_nonce( $_POST['add_paciente_nonce'], 'add_paciente_action' ) ) {
            wp_die( 'Security check failed (add)' );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied' );
        }

        $nombre = sanitize_text_field( $_POST['nombre'] );
        $correo = sanitize_email( $_POST['correo'] );
        $contrasena = $_POST['contrasena'];
        $nutricionista_id = isset( $_POST['nutricionista_id'] ) ? absint( $_POST['nutricionista_id'] ) : null;
        if ($nutricionista_id === 0) $nutricionista_id = null; // Treat 0 as NULL

        if ( empty( $nombre ) || empty( $correo ) || empty( $contrasena ) ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&message=error_empty' ) );
            exit;
        }
        if ( ! is_email( $correo ) ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&message=error_email' ) );
            exit;
        }

        global $wpdb;
        $table_name = Nutri_DB::get_pacientes_table_name();
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT correo FROM $table_name WHERE correo = %s", $correo ) );
        if ( $exists ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&message=error_exists' ) );
            exit;
        }

        $hashed_password = wp_hash_password( $contrasena );
        $result = $wpdb->insert(
            $table_name,
            [
                'nombre' => $nombre,
                'correo' => $correo,
                'contrasena' => $hashed_password,
                'nutricionista_id' => $nutricionista_id,
                'fecha_registro' => current_time( 'mysql', 1 )
            ],
            [ '%s', '%s', '%s', '%d', '%s' ]
        );

        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&message=success_add' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&message=error_add' ) );
        }
        exit;
    }

    public function handle_edit_paciente() {
        if ( ! isset( $_POST['edit_paciente_nonce'] ) || ! wp_verify_nonce( $_POST['edit_paciente_nonce'], 'edit_paciente_action_' . $_POST['id'] ) ) {
            wp_die( 'Security check failed (edit)' );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied' );
        }

        $id = absint( $_POST['id'] );
        $nombre = sanitize_text_field( $_POST['nombre'] );
        $correo = sanitize_email( $_POST['correo'] );
        $contrasena = $_POST['contrasena'];
        $nutricionista_id = isset( $_POST['nutricionista_id'] ) ? absint( $_POST['nutricionista_id'] ) : null;
        if ($nutricionista_id === 0) $nutricionista_id = null;

        if ( empty( $nombre ) || empty( $correo ) ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&action=edit&id=' . $id . '&message=error_empty' ) );
            exit;
        }
        if ( ! is_email( $correo ) ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&action=edit&id=' . $id . '&message=error_email' ) );
            exit;
        }

        global $wpdb;
        $table_name = Nutri_DB::get_pacientes_table_name();
        $exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM $table_name WHERE correo = %s AND id != %d", $correo, $id ) );
        if ( $exists ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&action=edit&id=' . $id . '&message=error_exists' ) );
            exit;
        }

        $data_to_update = [
            'nombre' => $nombre,
            'correo' => $correo,
            'nutricionista_id' => $nutricionista_id,
        ];
        $data_format = [ '%s', '%s', '%d' ];

        if ( ! empty( $contrasena ) ) {
            $data_to_update['contrasena'] = wp_hash_password( $contrasena );
            $data_format[] = '%s';
        }

        $result = $wpdb->update( $table_name, $data_to_update, [ 'id' => $id ], $data_format, [ '%d' ] );

        if ( $result !== false ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&message=success_edit' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&action=edit&id=' . $id . '&message=error_edit' ) );
        }
        exit;
    }

    public function handle_delete_paciente() {
        $id = absint( $_GET['id'] ); // Assuming ID comes from GET for admin-post link
        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'delete_paciente_action_' . $id ) ) {
            wp_die( 'Security check failed (delete)' );
        }
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Permission denied' );
        }

        global $wpdb;
        $table_name = Nutri_DB::get_pacientes_table_name();
        $result = $wpdb->delete( $table_name, [ 'id' => $id ], [ '%d' ] );

        if ( $result ) {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&message=success_delete' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=nutri_login_pacientes&message=error_delete' ) );
        }
        exit;
    }
}
?>
