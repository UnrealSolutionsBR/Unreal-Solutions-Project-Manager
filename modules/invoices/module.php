<?php
class UPM_Module_Invoices {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_invoice_meta_boxes']);
        add_action('save_post_upm_invoice', [__CLASS__, 'save_invoice_meta']);
    }

    public static function register_post_type() {
        $labels = [
            'name'               => 'Facturas',
            'singular_name'      => 'Factura',
            'menu_name'          => 'Facturas',
            'name_admin_bar'     => 'Factura',
            'add_new'            => 'Agregar nueva',
            'add_new_item'       => 'Agregar nueva factura',
            'edit_item'          => 'Editar factura',
            'view_item'          => 'Ver factura',
            'all_items'          => 'Todas las facturas',
            'search_items'       => 'Buscar facturas',
            'not_found'          => 'No se encontraron facturas',
        ];

        $args = [
            'labels'       => $labels,
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'upm_dashboard',
            'supports'     => ['title', 'editor'],
        ];

        register_post_type('upm_invoice', $args);
    }

    public static function add_invoice_meta_boxes() {
        add_meta_box(
            'upm_invoice_details',
            'Detalles de la factura',
            [__CLASS__, 'render_invoice_meta_box'],
            'upm_invoice',
            'normal',
            'default'
        );
    }

    public static function render_invoice_meta_box($post) {
        $client_id = get_post_meta($post->ID, '_upm_invoice_client_id', true);
        $amount = get_post_meta($post->ID, '_upm_invoice_amount', true);
        $status = get_post_meta($post->ID, '_upm_invoice_status', true);
        $status_options = ['pendiente' => 'Pendiente', 'pagada' => 'Pagada'];

        $customers = get_users(['role' => 'customer']);
        ?>
        <p><label><strong>Cliente:</strong></label><br>
            <select name="upm_invoice_client_id" style="width:100%;">
                <option value="">— Seleccionar —</option>
                <?php foreach ($customers as $user): ?>
                    <option value="<?= esc_attr($user->ID) ?>" <?= selected($client_id, $user->ID) ?>>
                        <?= esc_html($user->display_name . ' (' . $user->user_email . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p><label><strong>Monto (USD):</strong></label><br>
            <input type="number" step="0.01" name="upm_invoice_amount" value="<?= esc_attr($amount) ?>" />
        </p>

        <p><label><strong>Estado:</strong></label><br>
            <select name="upm_invoice_status">
                <?php foreach ($status_options as $key => $label): ?>
                    <option value="<?= esc_attr($key) ?>" <?= selected($status, $key) ?>>
                        <?= esc_html($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    public static function save_invoice_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $fields = [
            'upm_invoice_client_id' => '_upm_invoice_client_id',
            'upm_invoice_amount'    => '_upm_invoice_amount',
            'upm_invoice_status'    => '_upm_invoice_status',
        ];

        foreach ($fields as $form_field => $meta_key) {
            if (isset($_POST[$form_field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_field]));
            }
        }
    }
}
UPM_Module_Invoices::init();
