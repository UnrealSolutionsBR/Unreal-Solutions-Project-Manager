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

        $recent_projects = get_posts([
            'post_type'      => 'upm_project',
            'meta_key'       => '_upm_client_id',
            'meta_value'     => $user_id,
            'numberposts'    => 4,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

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
    <h2>Inicie su próximo proyecto</h2>
    <p>Seleccione entre nuestros servicios profesionales para hacer crecer su negocio</p>

    <div class="upm-services-grid">
        <div class="upm-service-card">
            <div class="upm-service-icon-box web-development">
                <?= file_get_contents(UPM_PATH . 'public/icons/web-design.svg'); ?>
            </div>
            <div class="upm-service-info">
                <h3>Desarrollo web</h3>
                <p>Sitios web y aplicaciones personalizadas</p>
                <small>Desde $100,00</small>
            </div>
        </div>

        <div class="upm-service-card">
            <div class="upm-service-icon-box hosting">
                <?= file_get_contents(UPM_PATH . 'public/icons/hosting.svg'); ?>
            </div>
            <div class="upm-service-info">
                <h3>Hosting</h3>
                <p>Hosting web y soporte continuo</p>
                <small>Desde $5,00 / mes</small>
            </div>
        </div>

        <div class="upm-service-card">
            <div class="upm-service-icon-box social-media">
                <?= file_get_contents(UPM_PATH . 'public/icons/social-media.svg'); ?>
            </div>
            <div class="upm-service-info">
                <h3>Gestión de redes sociales</h3>
                <p>Creación y gestión de contenido</p>
                <small>Desde $80,00 / mes</small>
            </div>
        </div>
    </div>

    <div class="upm-consultation-btn" style="margin-top: 20px;">
        <a href="#" class="upm-btn">Agendar consultoria</a>
    </div>
</div>
<div class="upm-overview-section">
    <div class="upm-overview-grid">
        
        <!-- Columna izquierda -->
        <div class="upm-card-block">
            <div class="upm-section-header">
                <h2>Proyectos recientes</h2>
                <a href="#">Ver todos</a>
            </div>

            <?php if (!empty($recent_projects)) : ?>
                                <?php foreach ($recent_projects as $project):
                                    $status = get_post_meta($project->ID, '_upm_status', true);
                                    $progress = get_post_meta($project->ID, '_upm_progress', true) ?: 0;
                                    $area = get_post_meta($project->ID, '_upm_area', true) ?: 'General';
                                ?>
                                <div class="upm-project-card">
                                    <div>
                                        <strong><?= esc_html($project->post_title) ?></strong>
                                        <p><?= esc_html($area) ?></p>
                                    </div>
                                    <span class="upm-badge <?= esc_attr('badge-' . sanitize_title($status)) ?>">
                                        <?= ucfirst($status) ?>
                                    </span>
                                    <div class="upm-progress-bar"><div style="width: <?= $progress ?>%"></div></div>
                                    <small><?= $progress ?>%</small>
                                </div>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <p>No hay proyectos recientes.</p>
                            <?php endif; ?>
                        </div>

        <!-- Columna derecha con acciones y salud -->
        <div class="upm-right-column">
            <div class="upm-card-block">
                <h2>Acciones rápidas</h2>
                <div class="upm-actions-grid">
                    <a class="upm-btn primary">Nuevo ticket de soporte</a>
                    <a class="upm-btn">Descargar factura</a>
                    <a class="upm-btn">Enviar feedback</a>
                    <a class="upm-btn">Ver portafolio</a>
                </div>
            </div>

            <div class="upm-card-block">
                <h2>Salud del servicio</h2>
                <ul class="upm-service-health">
                    <li><span class="dot online"></span> Entrega de proyectos</li>
                    <li><span class="dot online"></span> Respuesta de soporte</li>
                    <li><span class="dot online"></span> Comunicación</li>
                </ul>
            </div>
        </div>

    </div>
</div>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }
}
UPM_Shortcode_Dashboard::register();
