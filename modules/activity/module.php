<?php
class UPM_Module_Activity {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_activity_meta_boxes']);
        add_action('save_post_upm_activity', [__CLASS__, 'save_activity_meta']);
    }

    public static function register_post_type() {
        $labels = [
            'name'               => 'Actividades',
            'singular_name'      => 'Actividad',
            'menu_name'          => 'Actividades',
            'name_admin_bar'     => 'Actividad',
            'add_new'            => 'Agregar nueva',
            'add_new_item'       => 'Agregar nueva actividad',
            'edit_item'          => 'Editar actividad',
            'view_item'          => 'Ver actividad',
            'all_items'          => 'Todas las actividades',
            'search_items'       => 'Buscar actividades',
            'not_found'          => 'No se encontraron actividades',
        ];

        $args = [
            'labels'       => $labels,
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'upm_dashboard',
            'supports'     => ['title'],
            'menu_icon'    => 'dashicons-format-status',
        ];

        register_post_type('upm_activity', $args);
    }

    public static function add_activity_meta_boxes() {
        add_meta_box(
            'upm_activity_details',
            'Detalles de la actividad',
            [__CLASS__, 'render_activity_meta_box'],
            'upm_activity',
            'normal',
            'default'
        );
    }

    public static function render_activity_meta_box($post) {
        $client_id  = get_post_meta($post->ID, '_upm_activity_client_id', true);
        $project_id = get_post_meta($post->ID, '_upm_activity_project_id', true);
        $date       = get_post_meta($post->ID, '_upm_activity_date', true);
        $type       = get_post_meta($post->ID, '_upm_activity_type', true) ?: 'custom';
        $author_id  = get_post_meta($post->ID, '_upm_activity_author_id', true);

        $customers  = get_users(['role' => 'customer']);
        $admins     = get_users(['role__in' => ['administrator', 'editor']]);

        if (!$client_id && isset($_GET['upm_project_id'])) {
            $project_id = (int) $_GET['upm_project_id'];
            $client_id = get_post_meta($project_id, '_upm_client_id', true);
        }
        ?>
        <p><label><strong>Cliente:</strong></label><br>
            <select name="upm_activity_client_id" style="width:100%;">
                <option value="">— Seleccionar —</option>
                <?php foreach ($customers as $user): ?>
                    <option value="<?= esc_attr($user->ID) ?>" <?= selected($client_id, $user->ID) ?>>
                        <?= esc_html($user->display_name . ' (' . $user->user_email . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p><label><strong>Proyecto relacionado:</strong></label><br>
            <input type="number" name="upm_activity_project_id" value="<?= esc_attr($project_id) ?>" placeholder="ID del proyecto" />
        </p>

        <p><label><strong>Fecha de la actividad:</strong></label><br>
            <input type="datetime-local" name="upm_activity_date" value="<?= esc_attr($date) ?>" />
        </p>

        <p><label><strong>Tipo de actividad:</strong></label><br>
            <select name="upm_activity_type">
                <option value="custom" <?= selected($type, 'custom') ?>>Otro</option>
                <option value="project_created" <?= selected($type, 'project_created') ?>>Proyecto creado</option>
                <option value="note_added" <?= selected($type, 'note_added') ?>>Nota añadida</option>
                <option value="file_uploaded" <?= selected($type, 'file_uploaded') ?>>Archivo subido</option>
                <option value="status_changed" <?= selected($type, 'status_changed') ?>>Estado actualizado</option>
                <option value="payment_made" <?= selected($type, 'payment_made') ?>>Pago recibido</option>
            </select>
        </p>

        <p><label><strong>Autor de la actividad:</strong></label><br>
            <select name="upm_activity_author_id" style="width:100%;">
                <option value="">— Seleccionar —</option>
                <?php foreach ($admins as $user): ?>
                    <option value="<?= esc_attr($user->ID) ?>" <?= selected($author_id, $user->ID) ?>>
                        <?= esc_html($user->display_name . ' (' . $user->user_email . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public static function save_activity_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $fields = [
            'upm_activity_client_id'   => '_upm_activity_client_id',
            'upm_activity_project_id'  => '_upm_activity_project_id',
            'upm_activity_date'        => '_upm_activity_date',
            'upm_activity_type'        => '_upm_activity_type',
            'upm_activity_author_id'   => '_upm_activity_author_id',
        ];

        foreach ($fields as $form_field => $meta_key) {
            if (isset($_POST[$form_field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_field]));
            }
        }
    }
}

UPM_Module_Activity::init();
