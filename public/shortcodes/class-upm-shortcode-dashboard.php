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

        $milestones = get_posts([
            'post_type'  => 'upm_milestone',
            'meta_key'   => '_upm_milestone_client_id',
            'meta_value' => $user_id,
            'meta_query' => [
                [
                    'key'     => '_upm_milestone_date',
                    'value'   => date('Y-m-d'),
                    'compare' => '>=',
                    'type'    => 'DATE',
                ]
            ],
            'orderby'    => 'meta_value',
            'order'      => 'ASC',
            'posts_per_page' => 1,
        ]);
        
        $milestone_days  = '--';
        $milestone_title = 'Sin hitos programados';
            
        if (!empty($milestones)) {
            $milestone = $milestones[0];
            $milestone_title = $milestone->post_title;
        
            $date_str = get_post_meta($milestone->ID, '_upm_milestone_date', true);
            $date     = new DateTime($date_str);
            $now      = new DateTime();
            $interval = $now->diff($date);
            $milestone_days = $interval->days;
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
    <div class="upm-card active-projects">
        <div class="upm-card-inner">
            <div class="upm-icon-box active-projects">
                <?= file_get_contents(UPM_PATH . 'public/icons/active-projects.svg'); ?>
            </div>
            <div class="upm-card-text">
                <h3><?= $active_projects ?></h3>
                <p>Proyectos activos</p>
                <small>+<?= $active_projects ?> este mes</small>
            </div>
        </div>
    </div>

    <div class="upm-card completed-projects">
        <div class="upm-card-inner">
            <div class="upm-icon-box completed-projects">
                <?= file_get_contents(UPM_PATH . 'public/icons/completed-projects.svg'); ?>
            </div>
            <div class="upm-card-text">
                <h3><?= count($projects->posts) ?></h3>
                <p>Total de proyectos</p>
                <small>Todos entregados puntualmente</small>
            </div>
        </div>
    </div>

    <div class="upm-card support-tickets">
        <div class="upm-card-inner">
            <div class="upm-icon-box support-tickets">
                <?= file_get_contents(UPM_PATH . 'public/icons/support-tickets.svg'); ?>
            </div>
            <div class="upm-card-text">
                <h3><?= $open_tickets ?></h3>
                <p>Tickets de soporte</p>
                <small><?= $open_tickets === 1 ? '1 pendiente' : "$open_tickets pendientes" ?></small>
            </div>
        </div>
    </div>

    <div class="upm-card milestone">
    <div class="upm-card-inner">
        <div class="upm-icon-box milestone">
            <?= file_get_contents(UPM_PATH . 'public/icons/milestone.svg'); ?>
        </div>
        <div class="upm-card-text">
            <h3><?= $milestone_days ?> días</h3>
            <p>Para el próximo hito</p>
            <small><?= esc_html($milestone_title ?: 'Lanzamiento de e-commerce') ?></small> <!--CREAR VARIABLES PARA UPM_MILESTONE-->
        </div>
    </div>
</div>
    </div>
    <div class="upm-next-projects">
    <h2>Start Your Next Project</h2>
    <p>Choose from our professional services to grow your business</p>

    <div class="upm-services-grid">
        <div class="upm-service-card">
            <div class="upm-service-icon" style="background-color: #3B82F6;">🌐</div>
            <div class="upm-service-info">
                <strong>Web Development</strong>
                <p>Custom websites and web applications</p>
                <a href="#">Starting at $2,500</a>
            </div>
        </div>

        <div class="upm-service-card">
            <div class="upm-service-icon" style="background-color: #10B981;">📱</div>
            <div class="upm-service-info">
                <strong>Mobile App</strong>
                <p>iOS and Android applications</p>
                <a href="#">Starting at $4,000</a>
            </div>
        </div>

        <div class="upm-service-card">
            <div class="upm-service-icon" style="background-color: #EC4899;">🔗</div>
            <div class="upm-service-info">
                <strong>Social Media</strong>
                <p>Content creation and management</p>
                <a href="#">Starting at $800/month</a>
            </div>
        </div>

        <div class="upm-service-card">
            <div class="upm-service-icon" style="background-color: #F97316;">🎨</div>
            <div class="upm-service-info">
                <strong>Brand Design</strong>
                <p>Logo, branding, and visual identity</p>
                <a href="#">Starting at $1,200</a>
            </div>
        </div>

        <div class="upm-service-card">
            <div class="upm-service-icon" style="background-color: #8B5CF6;">💾</div>
            <div class="upm-service-info">
                <strong>Hosting & Maintenance</strong>
                <p>Website hosting and ongoing support</p>
                <a href="#">Starting at $50/month</a>
            </div>
        </div>
    </div>

    <div class="upm-consultation-btn">
        <a href="#" class="upm-btn">Schedule Free Consultation</a>
    </div>
</div>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }
}
UPM_Shortcode_Dashboard::register();
