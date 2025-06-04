<?php
/**
 * Plugin Name: Unreal Solutions Project Manager
 * Description: Panel modular para clientes de Unreal Solutions para gestionar proyectos, facturas, tickets y más.
 * Version: 1.3.2
 * Author: Unreal Solutions
 * Plugin URI: https://unrealsolutions.com.br/
 */

defined('ABSPATH') || exit;

// Definiciones globales
define('UPM_VERSION', '1.3.2');
define('UPM_PATH', plugin_dir_path(__FILE__));
define('UPM_URL', plugin_dir_url(__FILE__));

// Activador
require_once UPM_PATH . 'includes/class-upm-activator.php';
register_activation_hook(__FILE__, ['UPM_Activator', 'activate']);

// Loader principal
require_once UPM_PATH . 'includes/class-upm-loader.php';

// Shortcodes públicos
require_once UPM_PATH . 'public/shortcodes/class-upm-shortcode-dashboard.php';
require_once UPM_PATH . 'public/shortcodes/class-upm-shortcode-projects.php';
//require_once UPM_PATH . 'public/shortcodes/class-upm-shortcode-invoices.php';

// Reemplazar versión cacheada de CSS usando filemtime()
add_action('wp_enqueue_scripts', function () {
    if (!is_user_logged_in()) return;

    $base_dirs = [
        'css' => [
            'dir' => UPM_PATH . 'public/css/',
            'url' => UPM_URL . 'public/css/',
            'ext' => '*.css',
            'fn'  => 'wp_enqueue_style',
        ],
        'js' => [
            'dir' => UPM_PATH . 'public/js/',
            'url' => UPM_URL . 'public/js/',
            'ext' => '*.js',
            'fn'  => 'wp_enqueue_script',
        ],
    ];

    foreach ($base_dirs as $type => $data) {
        foreach (glob($data['dir'] . $data['ext']) as $file_path) {
            $file_name = basename($file_path);
            $handle = 'upm-' . str_replace('.' . $type, '', $file_name);

            call_user_func(
                $data['fn'],
                $handle,
                $data['url'] . $file_name,
                [],
                filemtime($file_path),
                $type === 'js' // true = cargar en footer
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