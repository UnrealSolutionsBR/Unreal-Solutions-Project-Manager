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

// Cargar el loader principal
require_once UPM_PATH . 'includes/class-upm-loader.php';

// Ejecutar el plugin
function run_unreal_project_manager() {
    $loader = new UPM_Loader();
    $loader->run();
}
run_unreal_project_manager();
