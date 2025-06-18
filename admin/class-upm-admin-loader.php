<?php
/**
 * Clase UPM_Admin_Loader
 * Responsable de cargar funcionalidades de administración (scripts, estilos, etc.)
 */

defined('ABSPATH') || exit;

class UPM_Admin_Loader {
    public static function init() {
        // Aquí puedes registrar futuros scripts o hooks para el admin
        // add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
    }

    public static function enqueue_admin_assets() {
        // wp_enqueue_style('upm-admin-style', UPM_URL . 'assets/css/admin.css', [], UPM_VERSION);
        // wp_enqueue_script('upm-admin-script', UPM_URL . 'assets/js/admin.js', ['jquery'], UPM_VERSION, true);
    }
}

// Activar si es necesario:
// UPM_Admin_Loader::init();
