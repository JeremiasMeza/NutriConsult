<?php
/**
 * Admin View for managing Pacientes.
 * @package    Nutri_Login
 * @subpackage Nutri_Login/admin/views
 */
global $wpdb;
// Fetch Nutricionistas for the dropdown
$nutricionistas_table = Nutri_DB::get_nutricionistas_table_name();
$all_nutricionistas = $wpdb->get_results( "SELECT id, nombre FROM $nutricionistas_table ORDER BY nombre ASC" );
?>
<div class="wrap">
    <h1><?php echo esc_html__( 'Manage Pacientes', 'nutri-login' ); ?></h1>

    <?php
    // Display messages
    if ( isset( $_GET['message'] ) ) {
        $message = sanitize_key( $_GET['message'] );
        $notice_class = 'notice-success'; // Default to success
        $user_message = '';

        switch ( $message ) {
            case 'success_add': $user_message = __( 'Paciente added successfully.', 'nutri-login' ); break;
            case 'success_edit': $user_message = __( 'Paciente updated successfully.', 'nutri-login' ); break;
            case 'success_delete': $user_message = __( 'Paciente deleted successfully.', 'nutri-login' ); break;
            case 'error_add': $user_message = __( 'Error adding paciente.', 'nutri-login' ); $notice_class = 'notice-error'; break;
            case 'error_edit': $user_message = __( 'Error updating paciente.', 'nutri-login' ); $notice_class = 'notice-error'; break;
            case 'error_delete': $user_message = __( 'Error deleting paciente.', 'nutri-login' ); $notice_class = 'notice-error'; break;
            case 'error_empty': $user_message = __( 'Required fields cannot be empty.', 'nutri-login' ); $notice_class = 'notice-error'; break;
            case 'error_email': $user_message = __( 'Invalid email format.', 'nutri-login' ); $notice_class = 'notice-error'; break;
            case 'error_exists': $user_message = __( 'Email already exists for another paciente.', 'nutri-login' ); $notice_class = 'notice-error'; break;
            default: $user_message = ''; break;
        }
        if ( $user_message ) {
            echo '<div id="message" class="notice ' . $notice_class . ' is-dismissible"><p>' . esc_html( $user_message ) . '</p></div>';
        }
    }

    $action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
    $paciente_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
    $form_action_name = 'add_paciente';
    $nonce_action = 'add_paciente_action';
    $nonce_name = 'add_paciente_nonce';
    $submit_button_text = __( 'Add Paciente', 'nutri-login' );
    $form_title = __( 'Add New Paciente', 'nutri-login' );
    $current_paciente = null;

    if ( $action === 'edit' && $paciente_id > 0 ) {
        $table_name = Nutri_DB::get_pacientes_table_name();
        $current_paciente = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $paciente_id ) );
        if ( $current_paciente ) {
            $form_action_name = 'edit_paciente';
            $nonce_action = 'edit_paciente_action_' . $paciente_id;
            $nonce_name = 'edit_paciente_nonce';
            $submit_button_text = __( 'Update Paciente', 'nutri-login' );
            $form_title = __( 'Edit Paciente', 'nutri-login' );
        } else {
            echo '<div class="error"><p>' . esc_html__( 'Paciente not found for editing.', 'nutri-login' ) . '</p></div>';
            $action = 'list';
        }
    }
    ?>

    <?php if ( $action === 'edit' || $action === 'list' ) : ?>
        <h2><?php echo esc_html( $form_title ); ?></h2>
        <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr( $form_action_name ); ?>">
            <?php if ( $action === 'edit' && $current_paciente ) : ?>
                <input type="hidden" name="id" value="<?php echo esc_attr( $current_paciente->id ); ?>">
            <?php endif; ?>
            <?php wp_nonce_field( $nonce_action, $nonce_name ); ?>

            <table class="form-table">
                <tr><th scope="row"><label for="nombre"><?php _e( 'Name', 'nutri-login' ); ?></label></th>
                    <td><input type="text" id="nombre" name="nombre" class="regular-text" value="<?php echo $current_paciente ? esc_attr( $current_paciente->nombre ) : ''; ?>" required /></td></tr>
                <tr><th scope="row"><label for="correo"><?php _e( 'Email', 'nutri-login' ); ?></label></th>
                    <td><input type="email" id="correo" name="correo" class="regular-text" value="<?php echo $current_paciente ? esc_attr( $current_paciente->correo ) : ''; ?>" required /></td></tr>
                <tr><th scope="row"><label for="contrasena"><?php _e( 'Password', 'nutri-login' ); ?></label></th>
                    <td><input type="password" id="contrasena" name="contrasena" class="regular-text" <?php echo ( $action === 'list' ) ? 'required' : ''; ?> />
                        <?php if ( $action === 'edit' ) : ?><p class="description"><?php _e( 'Leave blank to keep current password.', 'nutri-login' ); ?></p><?php endif; ?></td></tr>
                <tr><th scope="row"><label for="nutricionista_id"><?php _e( 'Assign Nutricionista', 'nutri-login' ); ?></label></th>
                    <td><select id="nutricionista_id" name="nutricionista_id">
                            <option value="0"><?php _e( '-- Unassigned --', 'nutri-login' ); ?></option>
                            <?php foreach ( $all_nutricionistas as $nutricionista ) : ?>
                                <option value="<?php echo esc_attr( $nutricionista->id ); ?>" <?php echo $current_paciente ? selected( $current_paciente->nutricionista_id, $nutricionista->id, false ) : ''; ?>>
                                    <?php echo esc_html( $nutricionista->nombre ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select></td></tr>
            </table>
            <?php submit_button( $submit_button_text ); ?>
        </form>
    <?php endif; ?>

    <h2><?php echo esc_html__( 'List of Pacientes', 'nutri-login' ); ?></h2>
    <?php
    $pacientes_table = Nutri_DB::get_pacientes_table_name();
    // Query to join paciente with nutricionista to get nutricionista name
    $query = $wpdb->prepare( "
        SELECT p.id, p.nombre, p.correo, p.fecha_registro, n.nombre AS nutricionista_nombre
        FROM %i AS p
        LEFT JOIN %i AS n ON p.nutricionista_id = n.id
        ORDER BY p.nombre ASC", $pacientes_table, $nutricionistas_table );
    $pacientes = $wpdb->get_results( $query );

    if ( empty( $pacientes ) ) {
        echo '<p>' . esc_html__( 'No pacientes found.', 'nutri-login' ) . '</p>';
    } else {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>' . __( 'ID', 'nutri-login') . '</th><th>' . __( 'Name', 'nutri-login') . '</th><th>' . __( 'Email', 'nutri-login') . '</th><th>' . __( 'Assigned Nutricionista', 'nutri-login') . '</th><th>' . __( 'Registered', 'nutri-login') . '</th><th>' . __( 'Actions', 'nutri-login') . '</th></tr></thead>';
        echo '<tbody>';
        foreach ( $pacientes as $paciente ) {
            echo '<tr>';
            echo '<td>' . esc_html( $paciente->id ) . '</td>';
            echo '<td>' . esc_html( $paciente->nombre ) . '</td>';
            echo '<td>' . esc_html( $paciente->correo ) . '</td>';
            echo '<td>' . esc_html( $paciente->nutricionista_nombre ? $paciente->nutricionista_nombre : '-- Unassigned --' ) . '</td>';
            echo '<td>' . esc_html( $paciente->fecha_registro ) . '</td>';
            echo '<td>';
            $edit_link = admin_url( 'admin.php?page=nutri_login_pacientes&action=edit&id=' . $paciente->id );
            echo '<a href="' . esc_url( $edit_link ) . '">' . __( 'Edit', 'nutri-login' ) . '</a> | ';
            $delete_url = wp_nonce_url( admin_url( 'admin-post.php?action=delete_paciente&id=' . $paciente->id . '&page=nutri_login_pacientes'), 'delete_paciente_action_' . $paciente->id, '_wpnonce' );
            echo '<a href="' . esc_url( $delete_url ) . '" onclick="return confirm(\'' . esc_js( __( 'Are you sure you want to delete this paciente?', 'nutri-login' ) ) . '\');">' . __( 'Delete', 'nutri-login' ) . '</a>';
            echo '</td></tr>';
        }
        echo '</tbody></table>';
    }
    ?>
</div>
