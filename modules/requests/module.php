<?php
class UPM_Module_Requests {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_meta_boxes']);
        add_action('save_post_upm_request', [__CLASS__, 'save_meta']);

        // AJAX handler para solicitudes desde el frontend
        add_action('wp_ajax_upm_create_request', [__CLASS__, 'handle_ajax_request']);
        add_action('wp_ajax_nopriv_upm_create_request', '__return_false');
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

    public static function handle_ajax_request() {
        if (!is_user_logged_in()) {
            wp_send_json_error(['message' => 'No autorizado.']);
        }

        $user_id    = get_current_user_id();
        $type       = sanitize_text_field($_POST['type'] ?? '');
        $message    = sanitize_textarea_field($_POST['message'] ?? '');
        $project_id = absint($_POST['project_id'] ?? 0);

        if (!$project_id || empty($message)) {
            wp_send_json_error(['message' => 'Datos incompletos.']);
        }

        $request_id = wp_insert_post([
            'post_type'   => 'upm_request',
            'post_title'  => 'Solicitud de actualización',
            'post_status' => 'publish',
            'post_author' => $user_id,
            'post_content'=> $message,
            'meta_input'  => [
                '_upm_request_project_id' => $project_id,
                '_upm_request_type'       => $type,
                '_upm_request_message'    => $message,
                '_upm_request_client_id'  => $user_id,
            ],
        ]);

        if ($request_id) {
            wp_send_json_success(['id' => $request_id]);
        } else {
            wp_send_json_error(['message' => 'Error al guardar la solicitud.']);
        }
    }
}

UPM_Module_Requests::init();
