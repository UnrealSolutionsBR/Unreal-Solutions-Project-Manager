<?php
class UPM_Loader {

    public function __construct() {
        $this->load_dependencies();
    }

    private function load_dependencies() {
        require_once UPM_PATH . 'includes/class-upm-activator.php';
        require_once UPM_PATH . 'admin/class-upm-admin-menu.php';
        require_once UPM_PATH . 'admin/class-upm-admin-loader.php';

        // Cargar módulos aquí
        foreach (glob(UPM_PATH . 'modules/*/module.php') as $module_file) {
            require_once $module_file;
        }
    }

    public function run() {
        add_action('admin_menu', ['UPM_Admin_Menu', 'register_menu']);
    }
}
