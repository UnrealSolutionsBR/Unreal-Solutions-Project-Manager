<?php
/**
 * Plugin Name: Unreal Solutions Project Manager
 * Description: Plugin modular para gestionar proyectos, facturas, tickets y más.
 * Version: 1.0.0
 * Author: Unreal Solutions
 * Plugin URI: https://unrealsolutions.com.br/
 */

defined('ABSPATH') || exit;

define('UPM_VERSION', '1.0.0');
define('UPM_PATH', plugin_dir_path(__FILE__));
define('UPM_URL', plugin_dir_url(__FILE__));

require_once UPM_PATH . 'includes/class-upm-loader.php';

function run_unreal_project_manager() {
    $loader = new UPM_Loader();
    $loader->run();
}
run_unreal_project_manager();
