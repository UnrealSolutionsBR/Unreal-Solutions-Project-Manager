<?php
class UPM_Module_Dashboard {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_dashboard_page']);
    }

    public static function register_dashboard_page() {
        add_submenu_page(
            'upm_dashboard',
            'Panel Principal',
            'Dashboard',
            'manage_options',
            'upm_dashboard',
            [__CLASS__, 'render_dashboard']
        );
    }

    public static function render_dashboard() {
        ?>
        <div class="wrap">
            <h1>Bienvenido a Unreal Project Manager</h1>
            <p>Esto es lo que está sucediendo con sus proyectos hoy.</p>

            <div style="display:flex; gap:20px; margin-top: 30px;">
                <div style="background:#fff; border:1px solid #ddd; padding:20px; border-radius:10px; width:250px;">
                    <h2>Proyectos activos</h2>
                    <p><strong>0</strong></p>
                </div>
                <div style="background:#fff; border:1px solid #ddd; padding:20px; border-radius:10px; width:250px;">
                    <h2>Facturas pendientes</h2>
                    <p><strong>0</strong></p>
                </div>
                <div style="background:#fff; border:1px solid #ddd; padding:20px; border-radius:10px; width:250px;">
                    <h2>Tickets abiertos</h2>
                    <p><strong>0</strong></p>
                </div>
            </div>
        </div>
        <?php
    }
}
UPM_Module_Dashboard::init();
