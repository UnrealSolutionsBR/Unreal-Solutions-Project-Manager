<?php
class UPM_Admin_Menu {
    public static function register_menu() {
        add_menu_page(
            'Project Manager',
            'Project Manager',
            'manage_options',
            'upm_dashboard',
            '__return_null', // lo manejará el módulo dashboard
            'dashicons-portfolio',
            30
        );
    }
}
