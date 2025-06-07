<?php
/**
 * Admin View for managing Nutricionistas.
 *
 * @package    Nutri_Login
 * @subpackage Nutri_Login/admin/views
 */
?>
<div class="wrap">
    <h1><?php echo esc_html__( 'Manage Nutricionistas', 'nutri-login' ); ?></h1>

    <?php
    // Display messages
    if ( isset( $_GET['message'] ) ) {
        $message = sanitize_key( $_GET['message'] );
        echo '<div id="message" class="updated notice is-dismissible">';
        if ( $message === 'success_add' ) {
            echo '<p>' . esc_html__( 'Nutricionista added successfully.', 'nutri-login' ) . '</p>';
        } elseif ( $message === 'error_add' ) {
            echo '<p>' . esc_html__( 'Error adding nutricionista.', 'nutri-login' ) . '</p>';
        } elseif ( $message === 'error_empty' ) {
            echo '<p>' . esc_html__( 'All fields are required.', 'nutri-login' ) . '</p>';
        } elseif ( $message === 'error_email' ) {
            echo '<p>' . esc_html__( 'Invalid email format.', 'nutri-login' ) . '</p>';
        } elseif ( $message === 'error_exists' ) {
            echo '<p>' . esc_html__( 'Email already exists.', 'nutri-login' ) . '</p>';
        } elseif ( $message === 'success_edit' ) {
            echo '<p>' . esc_html__( 'Nutricionista updated successfully.', 'nutri-login' ) . '</p>';
        } elseif ( $message === 'error_edit' ) {
            echo '<p>' . esc_html__( 'Error updating nutricionista.', 'nutri-login' ) . '</p>';
        } elseif ( $message === 'success_delete' ) {
            echo '<p>' . esc_html__( 'Nutricionista deleted successfully.', 'nutri-login' ) . '</p>';
        } elseif ( $message === 'error_delete' ) {
            echo '<p>' . esc_html__( 'Error deleting nutricionista.', 'nutri-login' ) . '</p>';
        }
        echo '</div>';
    }
    ?>

    <?php
    $action = isset( $_GET['action'] ) ? sanitize_key( $_GET['action'] ) : 'list';
    $nutricionista_id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
    $form_action = 'add_nutricionista';
    $nonce_action = 'add_nutricionista_action';
    $nonce_name = 'add_nutricionista_nonce';
    $submit_button_text = __( 'Add Nutricionista', 'nutri-login' );
    $form_title = __( 'Add New Nutricionista', 'nutri-login' );
    $current_nutricionista = null;

    if ( $action === 'edit' && $nutricionista_id > 0 ) {
        global $wpdb;
        $table_name_for_edit = Nutri_DB::get_nutricionistas_table_name(); // Renamed to avoid conflict
        $current_nutricionista = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name_for_edit WHERE id = %d", $nutricionista_id ) );
        if ( $current_nutricionista ) {
            $form_action = 'edit_nutricionista';
            $nonce_action = 'edit_nutricionista_action_' . $nutricionista_id;
            $nonce_name = 'edit_nutricionista_nonce';
            $submit_button_text = __( 'Update Nutricionista', 'nutri-login' );
            $form_title = __( 'Edit Nutricionista', 'nutri-login' );
        } else {
            // Nutricionista not found, show error message
            echo '<div class="error"><p>' . esc_html__( 'Nutricionista not found for editing.', 'nutri-login' ) . '</p></div>';
            $action = 'list'; // Revert to list view so the add form is shown
            $form_title = __( 'Add New Nutricionista', 'nutri-login' ); // Reset title for add form
        }
    }
    ?>

    <?php if ( $action === 'edit' || $action === 'list' ) : // Show form for add (if action=list or edit failed) or edit ?>
        <h2><?php echo esc_html( $form_title ); ?></h2>
        <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <input type="hidden" name="action" value="<?php echo esc_attr( $form_action ); ?>">
            <?php if ( $action === 'edit' && $current_nutricionista ) : ?>
                <input type="hidden" name="id" value="<?php echo esc_attr( $current_nutricionista->id ); ?>">
            <?php endif; ?>
            <?php wp_nonce_field( $nonce_action, $nonce_name ); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="nombre"><?php echo esc_html__( 'Name', 'nutri-login' ); ?></label>
                    </th>
                    <td>
                        <input type="text" id="nombre" name="nombre" class="regular-text" value="<?php echo $current_nutricionista ? esc_attr( $current_nutricionista->nombre ) : ''; ?>" required />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="correo"><?php echo esc_html__( 'Email', 'nutri-login' ); ?></label>
                    </th>
                    <td>
                        <input type="email" id="correo" name="correo" class="regular-text" value="<?php echo $current_nutricionista ? esc_attr( $current_nutricionista->correo ) : ''; ?>" required />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="contrasena"><?php echo esc_html__( 'Password', 'nutri-login' ); ?></label>
                    </th>
                    <td>
                        <input type="password" id="contrasena" name="contrasena" class="regular-text" <?php echo ( $action === 'list' || ! $current_nutricionista ) ? 'required' : ''; ?> />
                        <?php if ( $action === 'edit' && $current_nutricionista ) : ?>
                            <p class="description"><?php echo esc_html__( 'Leave blank to keep current password.', 'nutri-login' ); ?></p>
                        <?php else : ?>
                            <p class="description"><?php echo esc_html__( 'Set a password for the new nutritionist.', 'nutri-login' ); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            <?php submit_button( $submit_button_text ); ?>
        </form>
    <?php endif; ?>

    <h2><?php echo esc_html__( 'List of Nutricionistas', 'nutri-login' ); ?></h2>
    <?php
    global $wpdb;
    $table_name = Nutri_DB::get_nutricionistas_table_name();
    $nutricionistas = $wpdb->get_results( "SELECT id, nombre, correo, fecha_registro FROM $table_name ORDER BY nombre ASC" );

    if ( empty( $nutricionistas ) ) {
        echo '<p>' . esc_html__( 'No nutritionists found.', 'nutri-login' ) . '</p>';
    } else {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th scope="col">' . esc_html__( 'ID', 'nutri-login') . '</th><th scope="col">' . esc_html__( 'Name', 'nutri-login') . '</th><th scope="col">' . esc_html__( 'Email', 'nutri-login') . '</th><th scope="col">' . esc_html__( 'Registered', 'nutri-login') . '</th><th scope="col">' . esc_html__('Actions', 'nutri-login') . '</th></tr></thead>';
        echo '<tbody>';
        foreach ( $nutricionistas as $nutricionista ) {
            echo '<tr>';
            echo '<td>' . esc_html( $nutricionista->id ) . '</td>';
            echo '<td>' . esc_html( $nutricionista->nombre ) . '</td>';
            echo '<td>' . esc_html( $nutricionista->correo ) . '</td>';
            echo '<td>' . esc_html( $nutricionista->fecha_registro ) . '</td>';
            echo '<td>';
            // Edit Link
            $edit_link = admin_url( 'admin.php?page=nutri_login_nutricionistas&action=edit&id=' . $nutricionista->id );
            echo '<a href="' . esc_url( $edit_link ) . '">' . esc_html__( 'Edit', 'nutri-login' ) . '</a> | ';

            // Delete Link (using admin-post.php)
            $delete_url = wp_nonce_url(
                admin_url( 'admin-post.php?action=delete_nutricionista&id=' . $nutricionista->id . '&page=nutri_login_nutricionistas'),
                'delete_nutricionista_action_' . $nutricionista->id,
                '_wpnonce' // Nonce name
            );
            echo '<a href="' . esc_url( $delete_url ) . '" onclick="return confirm(\'' . esc_js( __( 'Are you sure you want to delete this nutritionist?', 'nutri-login' ) ) . '\');">' . esc_html__( 'Delete', 'nutri-login' ) . '</a>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
    ?>
</div>
