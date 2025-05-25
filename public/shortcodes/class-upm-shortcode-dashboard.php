<?php
class UPM_Shortcode_Dashboard {
    public static function register() {
        add_shortcode('upm_dashboard', [__CLASS__, 'render']);
    }

    public static function render() {
        if (!is_user_logged_in()) {
            return '<p>Debe iniciar sesión para acceder a su panel.</p>';
        }
    
        $user = wp_get_current_user();
        $user_id = $user->ID;
    
        // Cargar estilos
        wp_enqueue_style('upm-dashboard-css', UPM_URL . 'public/css/dashboard.css', [], UPM_VERSION);
    
        ob_start();
        ?>
        <div class="upm-wrapper">
            <aside class="upm-sidebar">
                <div class="upm-brand">Unreal Solutions</div>
                <div class="upm-user-info">
                    <strong><?= esc_html($user->display_name) ?></strong><br>
                    <small><?= esc_html($user->user_email) ?></small>
                </div>
                <ul class="upm-menu">
                    <li class="active">Dashboard</li>
                    <li>Proyectos</li>
                    <li>Facturas</li>
                    <li>Soporte</li>
                    <li>Mi cuenta</li>
                </ul>
            </aside>
    
            <main class="upm-main">
                <div class="upm-header">
                    <h2>¡Bienvenido, <?= esc_html($user->first_name ?: $user->display_name) ?>!</h2>
                    <p>Esto es lo que está sucediendo con sus proyectos hoy.</p>
                </div>
    
                <div class="upm-cards">
                    <div class="upm-card"><h3>12</h3><p>Proyectos activos</p></div>
                    <div class="upm-card"><h3>24</h3><p>Proyectos completados</p></div>
                    <div class="upm-card"><h3>3</h3><p>Tickets de soporte</p></div>
                    <div class="upm-card"><h3>3 días</h3><p>Próxima entrega</p></div>
                </div>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }    
}
UPM_Shortcode_Dashboard::register();
