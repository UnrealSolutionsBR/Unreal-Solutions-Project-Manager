<?php
/**
 * Plugin Name: Unreal Solutions Project Manager
 * Description: Panel modular para clientes de Unreal Solutions para gestionar proyectos, facturas, tickets y más.
 * Version: 1.0.0
 * Author: Unreal Solutions
 * Plugin URI: https://unrealsolutions.com.br/
 */

defined('ABSPATH') || exit;

// Definiciones globales
define('UPM_VERSION', '1.0.0');
define('UPM_PATH', plugin_dir_path(__FILE__));
define('UPM_URL', plugin_dir_url(__FILE__));

// Activador
require_once UPM_PATH . 'includes/class-upm-activator.php';
register_activation_hook(__FILE__, ['UPM_Activator', 'activate']);

// Loader principal
require_once UPM_PATH . 'includes/class-upm-loader.php';

// Shortcodes públicos
require_once UPM_PATH . 'public/shortcodes/class-upm-shortcode-dashboard.php';
require_once UPM_PATH . 'public/shortcodes/class-upm-shortcode-invoices.php';

// Reemplazar versión cacheada de CSS usando filemtime()
add_action('wp_enqueue_scripts', function () {
    if (is_user_logged_in()) {
        $css_file = UPM_PATH . 'public/css/dashboard.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'upm-dashboard-css',
                UPM_URL . 'public/css/dashboard.css',
                [],
                filemtime($css_file)
            );
        }
    }
});

// Ejecutar el plugin
function run_unreal_project_manager() {
    $loader = new UPM_Loader();
    $loader->run();
}
run_unreal_project_manager();