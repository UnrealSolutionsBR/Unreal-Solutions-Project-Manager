<?php
class UPM_Shortcode_Invoices {
    public static function register() {
        add_shortcode('upm_invoices', [__CLASS__, 'render']);
    }

    public static function render() {
        if (!is_user_logged_in()) {
            return '<p>Debe iniciar sesión para ver sus facturas.</p>';
        }

        $user = wp_get_current_user();
        $user_id = $user->ID;

        $projects = get_posts([
            'post_type'      => 'upm_project',
            'meta_key'       => '_upm_client_id',
            'meta_value'     => $user_id,
            'posts_per_page' => -1
        ]);

        $project_data = [];

        foreach ($projects as $project) {
            $project_id = $project->ID;
            $total_amount = (float) get_post_meta($project_id, '_upm_project_amount', true);
            $area         = get_post_meta($project_id, '_upm_area', true);
            $start_date   = get_post_meta($project_id, '_upm_start_date', true);
            $status       = get_post_meta($project_id, '_upm_status', true);

            $invoices = get_posts([
                'post_type'   => 'upm_invoice',
                'meta_key'    => '_upm_invoice_project_id',
                'meta_value'  => $project_id,
                'numberposts' => -1,
                'orderby'     => 'date',
                'order'       => 'ASC',
            ]);

            $paid = 0;
            $entries = [];
            $statuses = [];

            foreach ($invoices as $invoice) {
                $amount = (float) get_post_meta($invoice->ID, '_upm_invoice_amount', true);
                $state  = get_post_meta($invoice->ID, '_upm_invoice_status', true);
                $date   = get_the_date('Y-m-d', $invoice);

                if ($state === 'pagada') {
                    $paid += $amount;
                }

                $invoice_code = get_post_meta($invoice->ID, '_upm_invoice_code', true);

                $entries[] = [
                    'amount' => $amount,
                    'date'   => $date,
                    'state'  => $state,
                    'code'   => $invoice_code,
                ];

                $statuses[] = $state;
            }

            $progress = $total_amount > 0 ? round(($paid / $total_amount) * 100) : 0;

            $project_data[] = [
                'title'    => get_the_title($project),
                'area'     => $area,
                'date'     => $start_date,
                'status'   => $status,
                'amount'   => $total_amount,
                'paid'     => $paid,
                'progress' => $progress,
                'invoices' => $entries,
                'statuses' => array_unique($statuses),
            ];
        }

        ob_start();
        ?>
        <div class="upm-wrapper">
            <aside class="upm-sidebar">
                <div class="upm-brand">Unreal Solutions</div>
                <div class="upm-user-info">
                    <img src="<?= esc_url(get_avatar_url($user->ID)) ?>" alt="Foto de perfil">
                    <div class="upm-user-details">
                        <strong><?= esc_html($user->display_name) ?></strong>
                        <small><?= esc_html($user->user_email) ?></small>
                    </div>
                </div>
                <ul class="upm-menu">
                    <li><a href="/dashboard">Dashboard</a></li>
                    <li><a href="/dashboard/projects">Proyectos</a></li>
                    <li class="active"><a href="/dashboard/invoices">Facturas</a></li>
                    <li><a href="/dashboard/invoices">Soporte</a></li>
                    <li><a href="/dashboard/my-account">Mi cuenta</a></li>
                </ul>
            </aside>

            <main class="upm-main">
                <div class="upm-header">
                    <h2>Facturas y Pagos</h2>
                    <p>Gestiona el historial de facturación y pagos por cuotas</p>
                </div>

                <div class="upm-invoice-filters">
                    <select id="filter-status">
                        <option value="todos">Todos los Estados</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="pagada">Pagada</option>
                    </select>

                    <select id="filter-service">
                        <option value="todos">Todos los Servicios</option>
                        <option value="Web Development">Web Development</option>
                        <option value="Hosting">Hosting</option>
                        <option value="Social Media">Social Media</option>
                    </select>
                </div>

                <div class="upm-cards" style="margin-bottom: 30px;">
                    <div class="upm-card completed-projects">
                        <div class="upm-card-inner">
                            <div class="upm-card-text">
                                <h3>$<?= number_format(array_sum(array_column($project_data, 'paid')), 2) ?></h3>
                                <p>Total Pagado (<?= date('Y') ?>)</p>
                            </div>
                        </div>
                    </div>

                    <div class="upm-card support-tickets">
                        <div class="upm-card-inner">
                            <div class="upm-card-text">
                                <h3>$<?= number_format(array_sum(array_map(function ($p) {
                                    return $p['amount'] - $p['paid'];
                                }, $project_data)), 2) ?></h3>
                                <p>Pagos Pendientes</p>
                            </div>
                        </div>
                    </div>

                    <div class="upm-card active-projects">
                        <div class="upm-card-inner">
                            <div class="upm-card-text">
                                <h3><?= count($project_data) ?></h3>
                                <p>Proyectos Activos</p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php foreach ($project_data as $project): ?>
                    <div class="upm-project-invoice-block"
                         data-statuses="<?= esc_attr(implode(',', $project['statuses'])) ?>"
                         data-service="<?= esc_attr($project['area']) ?>">
                        <div class="upm-project-header">
                            <strong><?= esc_html($project['title']) ?></strong>
                            <span class="badge badge-<?= sanitize_title($project['status']) ?>">
                                <?= esc_html(ucwords(str_replace('-', ' ', $project['status']))) ?>
                            </span>
                            <div class="upm-project-meta">
                                <span class="meta-area"><?= esc_html($project['area']) ?></span>                                    
                                <span class="meta-date">
                                    <span class="meta-icon"><?= file_get_contents(UPM_PATH . 'public/icons/calendar.svg'); ?></span>
                                    <?= esc_html($project['date']) ?>
                                </span>
                                <span class="meta-amount">
                                    $<?= number_format($project['amount'], 2) ?>
                                </span>
                            </div>
                        </div>

                        <div class="progress-header">
                            <span class="progress-title">Progreso de pagos</span>
                            <span class="progress-amount">
                                $<?= number_format($project['paid'], 2) ?> / $<?= number_format($project['amount'], 2) ?>
                            </span>
                        </div>

                        <div class="progress">
                            <div class="bar" style="width: <?= $project['progress'] ?>%"></div>
                        </div>

                        <div class="progress-footer">
                            <span class="progress-percent"><?= $project['progress'] ?>% completado</span>
                        </div>

                        <div class="invoices">
                            <?php foreach ($project['invoices'] as $inv): ?>
                                <div class="invoice-entry">
                                    <div class="invoice-main">
                                        <div class="state-wrapper">
                                            <span class="state <?= esc_attr($inv['state']) ?>">
                                                <span class="state-icon">
                                                    <?php
                                                    if ($inv['state'] === 'pendiente') {
                                                        echo file_get_contents(UPM_PATH . 'public/icons/clock.svg');
                                                    } elseif ($inv['state'] === 'pagada') {
                                                        echo file_get_contents(UPM_PATH . 'public/icons/completed-projects.svg');
                                                    }
                                                    ?>
                                                </span>
                                                <?= ucfirst($inv['state']) ?>
                                            </span>
                                        </div>
                                        <div class="info-block">
                                            <div class="meta-amount">$<?= number_format($inv['amount'], 2) ?></div>
                                            <div class="meta-date"><?= esc_html($inv['date']) ?></div>
                                        </div>
                                    </div>

                                    <div class="code">#<?= esc_html($inv['code']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }
}

UPM_Shortcode_Invoices::register();
