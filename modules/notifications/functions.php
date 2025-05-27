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
    // Solo en creación (no actualización)
    if ($update) return;

    $user_id = get_post_meta($post_id, '_upm_client_id', true);
    if (!$user_id) return;

    $message = 'Nuevo proyecto creado: ' . $post->post_title;
    $icon = '🆕';

    upm_add_notification($user_id, $message, $icon);
}
