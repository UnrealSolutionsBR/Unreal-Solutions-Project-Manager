<?php
class UPM_Module_Tickets {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_ticket_meta_boxes']);
        add_action('save_post_upm_ticket', [__CLASS__, 'save_ticket_meta']);
    }

    public static function register_post_type() {
        $labels = [
            'name'               => 'Tickets',
            'singular_name'      => 'Ticket',
            'menu_name'          => 'Tickets',
            'name_admin_bar'     => 'Ticket',
            'add_new'            => 'Nuevo Ticket',
            'add_new_item'       => 'Crear nuevo ticket',
            'edit_item'          => 'Editar ticket',
            'view_item'          => 'Ver ticket',
            'all_items'          => 'Tickets',
            'search_items'       => 'Buscar tickets',
            'not_found'          => 'No hay tickets',
        ];

        $args = [
            'labels'       => $labels,
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'upm_dashboard',
            'supports'     => ['title', 'editor', 'comments'],
        ];

        register_post_type('upm_ticket', $args);
    }

    public static function add_ticket_meta_boxes() {
        add_meta_box(
            'upm_ticket_details',
            'Detalles del ticket',
            [__CLASS__, 'render_ticket_meta_box'],
            'upm_ticket',
            'normal',
            'default'
        );
    }

    public static function render_ticket_meta_box($post) {
        $client_id = get_post_meta($post->ID, '_upm_ticket_client_id', true);
        $priority = get_post_meta($post->ID, '_upm_ticket_priority', true);
        $status = get_post_meta($post->ID, '_upm_ticket_status', true);

        $priority_options = ['baja' => 'Baja', 'media' => 'Media', 'alta' => 'Alta'];
        $status_options = ['abierto' => 'Abierto', 'progreso' => 'En progreso', 'cerrado' => 'Cerrado'];

        $customers = get_users(['role' => 'customer']);
        ?>
        <p><label><strong>Cliente:</strong></label><br>
            <select name="upm_ticket_client_id" style="width:100%;">
                <option value="">— Seleccionar —</option>
                <?php foreach ($customers as $user): ?>
                    <option value="<?= esc_attr($user->ID) ?>" <?= selected($client_id, $user->ID) ?>>
                        <?= esc_html($user->display_name . ' (' . $user->user_email . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p><label><strong>Prioridad:</strong></label><br>
            <select name="upm_ticket_priority">
                <?php foreach ($priority_options as $key => $label): ?>
                    <option value="<?= esc_attr($key) ?>" <?= selected($priority, $key) ?>>
                        <?= esc_html($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p><label><strong>Estado:</strong></label><br>
            <select name="upm_ticket_status">
                <?php foreach ($status_options as $key => $label): ?>
                    <option value="<?= esc_attr($key) ?>" <?= selected($status, $key) ?>>
                        <?= esc_html($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public static function save_ticket_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $fields = [
            'upm_ticket_client_id' => '_upm_ticket_client_id',
            'upm_ticket_priority'  => '_upm_ticket_priority',
            'upm_ticket_status'    => '_upm_ticket_status',
        ];

        foreach ($fields as $form_field => $meta_key) {
            if (isset($_POST[$form_field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_field]));
            }
        }
    }
}
UPM_Module_Tickets::init();
