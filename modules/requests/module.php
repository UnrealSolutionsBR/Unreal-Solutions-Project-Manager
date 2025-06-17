<?php
class UPM_Module_Requests {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_upm_request', [__CLASS__, 'save_meta']);
    }

    public static function register_post_type() {
        $labels = [
            'name'               => 'Solicitudes',
            'singular_name'      => 'Solicitud',
            'menu_name'          => 'Solicitudes',
            'name_admin_bar'     => 'Solicitud',
            'add_new'            => 'Agregar nueva',
            'add_new_item'       => 'Agregar nueva solicitud',
            'edit_item'          => 'Editar solicitud',
            'view_item'          => 'Ver solicitud',
            'all_items'          => 'Todas las solicitudes',
            'search_items'       => 'Buscar solicitudes',
            'not_found'          => 'No se encontraron solicitudes',
        ];

        $args = [
            'labels'        => $labels,
            'public'        => false,
            'show_ui'       => true,
            'show_in_menu'  => 'upm_dashboard',
            'supports'      => ['title'],
            'menu_icon'     => 'dashicons-format-status',
        ];

        register_post_type('upm_request', $args);
    }

    public static function add_meta_boxes() {
        add_meta_box(
            'upm_request_fields',
            'Detalles de la solicitud',
            [__CLASS__, 'render_meta_box'],
            'upm_request',
            'normal',
            'default'
        );
    }

    public static function render_meta_box($post) {
        $fields = [
            '_upm_request_type'        => 'Tipo de solicitud',
            '_upm_request_message'     => 'Mensaje del cliente',
            '_upm_request_project_id'  => 'ID del proyecto',
            '_upm_request_client_id'   => 'ID del cliente',
        ];

        foreach ($fields as $key => $label) {
            $value = get_post_meta($post->ID, $key, true);

            echo '<p><label><strong>' . esc_html($label) . ':</strong></label><br>';

            if ($key === '_upm_request_message') {
                echo '<textarea name="' . esc_attr($key) . '" rows="4" style="width:100%;">' . esc_textarea($value) . '</textarea>';
            } else {
                echo '<input type="text" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" style="width:100%;" />';
            }

            echo '</p>';
        }
    }

    public static function save_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $keys = [
            '_upm_request_type',
            '_upm_request_message',
            '_upm_request_project_id',
            '_upm_request_client_id',
        ];

        foreach ($keys as $key) {
            if (isset($_POST[$key])) {
                update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
            }
        }
    }
}

UPM_Module_Requests::init();
