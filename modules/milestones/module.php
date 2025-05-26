<?php

defined('ABSPATH') || exit;

class UPM_Milestone_Post_Type {
    public static function register() {
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
            'not_found_in_trash' => 'No hay hitos en la papelera',
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'unreal-project-manager',
            'capability_type'    => 'post',
            'hierarchical'       => false,
            'menu_icon'          => 'dashicons-flag',
            'supports'           => ['title'],
            'has_archive'        => false,
            'rewrite'            => false,
            'show_in_rest'       => false,
        ];

        register_post_type('upm_milestone', $args);
    }
}

add_action('init', ['UPM_Milestone_Post_Type', 'register']);
