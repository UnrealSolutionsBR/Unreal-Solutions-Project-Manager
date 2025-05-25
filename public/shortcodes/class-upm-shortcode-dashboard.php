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

        // Consultas
        $projects = new WP_Query([
            'post_type'      => 'upm_project',
            'meta_key'       => '_upm_client_id',
            'meta_value'     => $user_id,
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);

        $invoices = get_posts([
            'post_type'  => 'upm_invoice',
            'meta_key'   => '_upm_invoice_client_id',
            'meta_value' => $user_id,
        ]);

        $tickets = get_posts([
            'post_type'  => 'upm_ticket',
            'meta_key'   => '_upm_ticket_client_id',
            'meta_value' => $user_id,
        ]);

        $active_projects = 0;
        foreach ($projects->posts as $p) {
            $status = get_post_meta($p, '_upm_status', true);
            if ($status === 'activo') $active_projects++;
        }

        $pending_invoices = 0;
        foreach ($invoices as $i) {
            if (get_post_meta($i->ID, '_upm_invoice_status', true) === 'pendiente') {
                $pending_invoices++;
            }
        }

        $open_tickets = 0;
        foreach ($tickets as $t) {
            if (get_post_meta($t->ID, '_upm_ticket_status', true) === 'abierto') {
                $open_tickets++;
            }
        }

        // Estilos
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
                    <div class="upm-card">
                        <img src="<?= esc_url(UPM_URL . 'public/icons/active-projects.svg') ?>" class="upm-icon" alt="Active Projects Icon" />
                        <h3><?= $active_projects ?></h3>
                        <p>Proyectos activos</p>
                    </div>

                    <div class="upm-card">
                        <img src="<?= esc_url(UPM_URL . 'public/icons/completed-projects.svg') ?>" class="upm-icon" alt="Completed Projects Icon" />
                        <h3><?= count($projects->posts) ?></h3>
                        <p>Total de proyectos</p>
                    </div>

                    <div class="upm-card">
                        <img src="<?= esc_url(UPM_URL . 'public/icons/support-tickets.svg') ?>" class="upm-icon" alt="Support Tickets Icon" />
                        <h3><?= $open_tickets ?></h3>
                        <p>Tickets de soporte</p>
                    </div>

                    <div class="upm-card">
                        <img src="<?= esc_url(UPM_URL . 'public/icons/pending-invoices.svg') ?>" class="upm-icon" alt="Pending Invoices Icon" />
                        <h3><?= $pending_invoices ?></h3>
                        <p>Facturas pendientes</p>
                    </div>
                </div>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }
}
UPM_Shortcode_Dashboard::register();
