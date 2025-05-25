<?php
class UPM_Module_Projects {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_project_meta_boxes']);
        add_action('save_post_upm_project', [__CLASS__, 'save_project_meta']);
    }

    public static function register_post_type() {
        $labels = [ /* mismo código de antes */ ];

        $args = [
            'labels'       => $labels,
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'upm_dashboard',
            'supports'     => ['title', 'editor', 'custom-fields'],
        ];

        register_post_type('upm_project', $args);
    }

    public static function add_project_meta_boxes() {
        add_meta_box(
            'upm_project_details',
            'Detalles del proyecto',
            [__CLASS__, 'render_project_meta_box'],
            'upm_project',
            'normal',
            'default'
        );
    }

    public static function render_project_meta_box($post) {
        $client_id = get_post_meta($post->ID, '_upm_client_id', true);
        $start_date = get_post_meta($post->ID, '_upm_start_date', true);
        $due_date = get_post_meta($post->ID, '_upm_due_date', true);
        $status = get_post_meta($post->ID, '_upm_status', true);
        $status_options = ['activo' => 'Activo', 'pausado' => 'Pausado', 'finalizado' => 'Finalizado'];

        // Lista de usuarios con rol "customer"
        $customers = get_users(['role' => 'customer']);
        ?>
        <p><label><strong>Cliente asignado:</strong></label><br>
            <select name="upm_client_id" style="width:100%;">
                <option value="">— Seleccionar —</option>
                <?php foreach ($customers as $user): ?>
                    <option value="<?= esc_attr($user->ID) ?>" <?= selected($client_id, $user->ID) ?>>
                        <?= esc_html($user->display_name . ' (' . $user->user_email . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p><label><strong>Fecha de inicio:</strong></label><br>
            <input type="date" name="upm_start_date" value="<?= esc_attr($start_date) ?>" />
        </p>

        <p><label><strong>Fecha de entrega estimada:</strong></label><br>
            <input type="date" name="upm_due_date" value="<?= esc_attr($due_date) ?>" />
        </p>

        <p><label><strong>Estado del proyecto:</strong></label><br>
            <select name="upm_status">
                <?php foreach ($status_options as $key => $label): ?>
                    <option value="<?= esc_attr($key) ?>" <?= selected($status, $key) ?>>
                        <?= esc_html($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public static function save_project_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $fields = [
            'upm_client_id'  => '_upm_client_id',
            'upm_start_date' => '_upm_start_date',
            'upm_due_date'   => '_upm_due_date',
            'upm_status'     => '_upm_status',
        ];

        foreach ($fields as $form_field => $meta_key) {
            if (isset($_POST[$form_field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_field]));
            }
        }
    }
}
UPM_Module_Projects::init();
