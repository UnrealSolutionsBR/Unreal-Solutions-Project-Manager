<?php
if (!defined('ABSPATH')) {
    exit;
}

class UPM_Notifications_Module {
    public static function register() {
        add_action('init', [__CLASS__, 'register_cpt']);
    }

    public static function register_cpt() {
        $labels = [
            'name'               => 'Notificaciones',
            'singular_name'      => 'Notificación',
            'menu_name'          => 'Notificaciones',
            'name_admin_bar'     => 'Notificación',
            'add_new'            => 'Añadir nueva',
            'add_new_item'       => 'Añadir nueva notificación',
            'new_item'           => 'Nueva notificación',
            'edit_item'          => 'Editar notificación',
            'view_item'          => 'Ver notificación',
            'all_items'          => 'Todas las notificaciones',
            'search_items'       => 'Buscar notificaciones',
            'not_found'          => 'No se encontraron notificaciones',
            'not_found_in_trash' => 'No hay notificaciones en la papelera',
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_position'      => 30,
            'menu_icon'          => 'dashicons-megaphone',
            'supports'           => ['title'],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
        ];

        register_post_type('upm_notification', $args);
    }
}

UPM_Notifications_Module::register();
