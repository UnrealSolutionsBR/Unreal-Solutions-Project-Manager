<?php
if (!defined('ABSPATH')) exit;

class UPM_Module_Notes {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_note_meta_boxes']);
        add_action('save_post_upm_note', [__CLASS__, 'save_note_meta']);
    }

    public static function register_post_type() {
        $labels = [
            'name'               => 'Notas del cliente',
            'singular_name'      => 'Nota del cliente',
            'menu_name'          => 'Notas',
            'name_admin_bar'     => 'Nota',
            'add_new'            => 'Agregar nueva',
            'add_new_item'       => 'Agregar nueva nota',
            'edit_item'          => 'Editar nota',
            'view_item'          => 'Ver nota',
            'all_items'          => 'Todas las notas',
            'search_items'       => 'Buscar notas',
            'not_found'          => 'No se encontraron notas',
        ];

        $args = [
            'labels'       => $labels,
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'upm_dashboard',
            'supports'     => ['title', 'editor'],
            'menu_icon'    => 'dashicons-edit',
        ];

        register_post_type('upm_note', $args);
    }

    public static function add_note_meta_boxes() {
        add_meta_box(
            'upm_note_details',
            'Detalles de la nota',
            [__CLASS__, 'render_note_meta_box'],
            'upm_note',
            'normal',
            'default'
        );
    }

    public static function render_note_meta_box($post) {
        $client_id  = get_post_meta($post->ID, '_upm_note_client_id', true);
        $project_id = get_post_meta($post->ID, '_upm_note_project_id', true);
        $customers  = get_users(['role' => 'customer']);

        if (!$client_id && isset($_GET['upm_project_id'])) {
            $project_id = (int) $_GET['upm_project_id'];
            $client_id = get_post_meta($project_id, '_upm_client_id', true);
        }
        ?>
        <p><label><strong>Cliente:</strong></label><br>
            <select name="upm_note_client_id" style="width:100%;">
                <option value="">— Seleccionar —</option>
                <?php foreach ($customers as $user): ?>
                    <option value="<?= esc_attr($user->ID) ?>" <?= selected($client_id, $user->ID) ?>>
                        <?= esc_html($user->display_name . ' (' . $user->user_email . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p><label><strong>Proyecto relacionado:</strong></label><br>
            <input type="number" name="upm_note_project_id" value="<?= esc_attr($project_id) ?>" placeholder="ID del proyecto" />
        </p>
        <?php
    }

    public static function save_note_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $fields = [
            'upm_note_client_id'  => '_upm_note_client_id',
            'upm_note_project_id' => '_upm_note_project_id',
        ];

        foreach ($fields as $form_field => $meta_key) {
            if (isset($_POST[$form_field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_field]));
            }
        }
    }
}

UPM_Module_Notes::init();
