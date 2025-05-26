<?php
class UPM_Module_Milestones {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_milestone_meta_boxes']);
        add_action('save_post_upm_milestone', [__CLASS__, 'save_milestone_meta']);
    }

    public static function register_post_type() {
        $labels = [
            'name'               => 'Hitos',
            'singular_name'      => 'Hito',
            'menu_name'          => 'Hitos',
            'name_admin_bar'     => 'Hito',
            'add_new'            => 'Agregar nuevo',
            'add_new_item'       => 'Agregar nuevo hito',
            'edit_item'          => 'Editar hito',
            'view_item'          => 'Ver hito',
            'all_items'          => 'Todos los hitos',
            'search_items'       => 'Buscar hitos',
            'not_found'          => 'No se encontraron hitos',
        ];

        $args = [
            'labels'       => $labels,
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'unreal-project-manager',
            'supports'     => ['title'],
            'menu_icon'    => 'dashicons-flag',
        ];

        register_post_type('upm_milestone', $args);
    }

    public static function add_milestone_meta_boxes() {
        add_meta_box(
            'upm_milestone_details',
            'Detalles del hito',
            [__CLASS__, 'render_milestone_meta_box'],
            'upm_milestone',
            'normal',
            'default'
        );
    }

    public static function render_milestone_meta_box($post) {
        $client_id = get_post_meta($post->ID, '_upm_milestone_client_id', true);
        $date      = get_post_meta($post->ID, '_upm_milestone_date', true);
        $customers = get_users(['role' => 'customer']);
        ?>
        <p><label><strong>Cliente:</strong></label><br>
            <select name="upm_milestone_client_id" style="width:100%;">
                <option value="">— Seleccionar —</option>
                <?php foreach ($customers as $user): ?>
                    <option value="<?= esc_attr($user->ID) ?>" <?= selected($client_id, $user->ID) ?>>
                        <?= esc_html($user->display_name . ' (' . $user->user_email . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p><label><strong>Fecha del hito:</strong></label><br>
            <input type="date" name="upm_milestone_date" value="<?= esc_attr($date) ?>" />
        </p>
        <?php
    }

    public static function save_milestone_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $fields = [
            'upm_milestone_client_id' => '_upm_milestone_client_id',
            'upm_milestone_date'      => '_upm_milestone_date',
        ];

        foreach ($fields as $form_field => $meta_key) {
            if (isset($_POST[$form_field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_field]));
            }
        }
    }
}
UPM_Module_Milestones::init();
