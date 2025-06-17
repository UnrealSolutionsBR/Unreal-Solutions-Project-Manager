<?php
class UPM_Module_Request {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('wp_ajax_upm_submit_request', [__CLASS__, 'handle_request_submission']);
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
            'labels'       => $labels,
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'upm_dashboard',
            'supports'     => ['title', 'custom-fields'],
            'menu_icon'    => 'dashicons-format-status',
        ];

        register_post_type('upm_request', $args);
    }

    public static function handle_request_submission() {
        check_ajax_referer('upm_request_nonce', 'nonce');

        $client_id  = get_current_user_id();
        $project_id = absint($_POST['project_id'] ?? 0);
        $type       = sanitize_text_field($_POST['request_type'] ?? '');
        $message    = sanitize_textarea_field($_POST['message'] ?? '');

        if (!$client_id || !$project_id || !$message) {
            wp_send_json_error(['message' => 'Datos incompletos.']);
        }

        $request_id = wp_insert_post([
            'post_type'   => 'upm_request',
            'post_status' => 'publish',
            'post_title'  => 'Solicitud de actualización - ' . current_time('Y-m-d H:i'),
            'meta_input'  => [
                '_upm_request_client_id'  => $client_id,
                '_upm_request_project_id' => $project_id,
                '_upm_request_type'       => $type,
                '_upm_request_message'    => $message,
                '_upm_request_status'     => 'pendiente',
            ]
        ]);

        if (is_wp_error($request_id)) {
            wp_send_json_error(['message' => 'Error al registrar la solicitud.']);
        }

        wp_send_json_success(['message' => 'Solicitud enviada correctamente.']);
    }
}

UPM_Module_Request::init();
