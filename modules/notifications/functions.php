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

    if (!$update) {
        $message = 'Nuevo proyecto creado: ' . $post->post_title;
        upm_add_notification($user_id, $message, '🆕');
        error_log("✅ Notificación por creación enviada al usuario $user_id");
        return;
    }

    $old_status = get_post_meta($post_id, '_upm_status', true);
    if (isset($_POST['upm_status'])) {
        $new_status = sanitize_text_field($_POST['upm_status']);
        error_log("🔄 Estado antiguo: $old_status / Nuevo: $new_status");

        if ($new_status !== $old_status) {
            $message = "El estado del proyecto {$post->post_title} ha cambiado a: $new_status.";
            upm_add_notification($user_id, strip_tags($message), '⚙️');
            error_log("✅ Notificación por cambio de estado enviada al usuario $user_id");
        }
    }
}

