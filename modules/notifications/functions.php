<?php
if (!defined('ABSPATH')) exit;

/**
 * Crea una notificación para un usuario específico.
 *
 * @param int $user_id ID del usuario destinatario.
 * @param string $message Contenido de la notificación.
 * @param string $icon Emoji o nombre de ícono SVG (opcional).
 * @param string $status Estado de la notificación ('unread' o 'read').
 */
if (!function_exists('upm_add_notification')) {
    function upm_add_notification($user_id, $message, $icon = '🔔', $status = 'unread') {
        if (!$user_id || !$message) return;

        $notification_id = wp_insert_post([
            'post_type'   => 'upm_notification',
            'post_title'  => wp_strip_all_tags($message),
            'post_status' => 'publish',
        ]);

        if (!is_wp_error($notification_id)) {
            update_post_meta($notification_id, '_upm_user_id', $user_id);
            update_post_meta($notification_id, '_upm_icon', $icon);
            update_post_meta($notification_id, '_upm_timestamp', current_time('mysql'));
            update_post_meta($notification_id, '_upm_status', $status);
        }
    }
}

/**
 * Hook: Genera una notificación automáticamente cuando se crea un nuevo proyecto.
 */
add_action('save_post_upm_project', 'upm_notify_on_new_project', 20, 3);

function upm_notify_on_new_project($post_id, $post, $update) {
    error_log("🔔 Hook ejecutado: save_post_upm_project. Post ID: $post_id");

    $user_id = get_post_meta($post_id, '_upm_client_id', true);
    if (!$user_id) {
        error_log("⛔ No se encontró _upm_client_id para el proyecto $post_id");
        return;
    }

    $project_title = $post->post_title;

    // Si es creación (nuevo proyecto)
    if (!$update) {
        $message = "Nuevo proyecto: $project_title";
        upm_add_notification($user_id, $message, '🆕');
        update_post_meta($post_id, '_upm_last_status', get_post_meta($post_id, '_upm_status', true));
        error_log("✅ Notificación por creación enviada al usuario $user_id");
        return;
    }

    // Si es actualización
    $previous_status = get_post_meta($post_id, '_upm_last_status', true);
    $current_status  = get_post_meta($post_id, '_upm_status', true);

    error_log("🔄 Estado antiguo: $previous_status / Nuevo: $current_status");

    if ($current_status && $current_status !== $previous_status) {
        switch ($current_status) {
            case 'en-curso':
                $message = "Hemos iniciado el desarrollo de $project_title";
                $icon = '🛠️';
                break;
            case 'esperando-revision':
                $message = "$project_title requiere revisión";
                $icon = '🧐';
                break;
            case 'completado':
                $message = "$project_title ha sido completado";
                $icon = '✅';
                break;
            default:
                $message = "El estado del proyecto $project_title ha cambiado a: $current_status.";
                $icon = '⚙️';
        }

        upm_add_notification($user_id, $message, $icon);
        update_post_meta($post_id, '_upm_last_status', $current_status);
        error_log("✅ Notificación por cambio de estado enviada al usuario $user_id");
    }
}


