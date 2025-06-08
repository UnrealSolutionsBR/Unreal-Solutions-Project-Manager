<?php
class UPM_Shortcode_Project_View {
    public static function register() {
        add_shortcode('upm_project_view', [__CLASS__, 'render']);
    }

    public static function render($atts) {
        if (!is_user_logged_in()) {
            return '<p>Debe iniciar sesión para ver este proyecto.</p>';
        }

        $atts = shortcode_atts(['id' => 0], $atts);
        $project_id = absint($atts['id'] ?: ($_GET['id'] ?? 0));
        $user_id = get_current_user_id();
        $user = wp_get_current_user();

        $project = get_post($project_id);
        if (!$project || $project->post_type !== 'upm_project') {
            return '<p>Proyecto no encontrado.</p>';
        }

        $client_id = (int) get_post_meta($project_id, '_upm_client_id', true);
        if ($client_id !== $user_id) {
            return '<p>No tienes permiso para ver este proyecto.</p>';
        }

        $area = get_post_meta($project_id, '_upm_area', true);
        $status = get_post_meta($project_id, '_upm_status', true);
        $progress = get_post_meta($project_id, '_upm_progress', true);
        $due = get_post_meta($project_id, '_upm_due_date', true);
        $start = get_post_meta($project_id, '_upm_start_date', true);
        $amount = get_post_meta($project_id, '_upm_project_amount', true);
        $short_description = get_post_meta($project->ID, '_upm_short_description', true);

        $notes = get_posts([
            'post_type' => 'upm_note',
            'meta_key' => '_upm_note_project_id',
            'meta_value' => $project_id,
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => 3,
        ]);

        $milestones = get_posts([
            'post_type' => 'upm_milestone',
            'meta_key' => '_upm_milestone_project_id',
            'meta_value' => $project_id,
            'orderby' => 'meta_value',
            'meta_key' => '_upm_milestone_date',
            'order' => 'ASC',
            'posts_per_page' => -1
        ]);

        $files = [
            'Legal' => [['Contrato de Servicios', 'PDF - 1.2 MB - 2024-01-01']],
            'Facturación' => [['Factura #001 - Anticipo 50%', 'PDF - 0.8 MB - 2024-01-01'], ['Factura #002 - Saldo Final 50%', 'PDF - 0.8 MB - 2024-02-15']],
            'Diseño' => [['Design Mockups v1.0', 'PDF - 2.4 MB - 2024-01-20']],
            'Documentación' => [['Project Brief', 'DOC - 1.1 MB - 2024-01-01']]
        ];

        $invoices = get_posts([
            'post_type' => 'upm_invoice',
            'meta_key' => '_upm_invoice_project_id',
            'meta_value' => $project_id,
            'posts_per_page' => -1
        ]);

        $paid = 0;
        foreach ($invoices as $inv) {
            if (get_post_meta($inv->ID, '_upm_invoice_status', true) === 'pagada') {
                $paid += (float) get_post_meta($inv->ID, '_upm_invoice_amount', true);
            }
        }

        ob_start();
        ?>
        <div class="upm-wrapper">
            <aside class="upm-sidebar">
                <div class="upm-brand">Unreal Solutions</div>
                <div class="upm-user-info">
                    <img src="<?= esc_url(get_avatar_url($user_id)) ?>" alt="Foto de perfil">
                    <div class="upm-user-details">
                        <strong><?= esc_html($user->display_name) ?></strong>
                        <small><?= esc_html($user->user_email) ?></small>
                    </div>
                </div>
                <ul class="upm-menu">
                    <li><a href="/dashboard">Dashboard</a></li>
                    <li class="active"><a href="/dashboard/projects">Proyectos</a></li>
                    <li><a href="/dashboard/invoices">Facturas</a></li>
                    <li><a href="/dashboard/support">Soporte</a></li>
                    <li><a href="/dashboard/my-account">Mi cuenta</a></li>
                </ul>
            </aside>

            <main class="upm-main">
                <div class="upm-project-view-header">
                    <div class="upm-project-title-group">
                    <a href="/dashboard/projects" class="upm-back-btn">
                        <?= file_get_contents(UPM_PATH . 'public/icons/arrow.svg'); ?>
                    </a>
                        <div>
                        <h2><?= esc_html($project->post_title) ?></h2>
                        <p><?= esc_html($area) ?></p>
                        </div>
                    </div>
                    <span class="project-badge badge-<?= esc_attr($status) ?>">
                        <?= ucwords(str_replace('-', ' ', $status)) ?>
                    </span>
                </div>
                <div class="upm-overview-section">
                    <div class="upm-overview-grid">
                        <!-- Columna izquierda -->
                        <div class="upm-left-column">
                            <div class="upm-card-block">
                                <h3>Resumen del Proyecto</h3>
                                <p class="upm-project-description"><?= esc_html($short_description) ?></p>
                                <div class="upm-project-dates">
                                    <div class="upm-date-item">
                                        <small>Fecha de inicio:</small>
                                        <p><?= esc_html($start) ?></p>
                                    </div>
                                    <div class="upm-date-item">
                                        <small>Fecha de entrega:</small>
                                        <p><?= esc_html($due) ?></p>
                                    </div>
                                </div>
                                <div class="progress-header">
                                    <span class="progress-title">Progreso</span>
                                    <span class="progress-amount"><?= intval($progress) ?>%</span>
                                </div>
                                <div class="upm-progress-bar">
                                    <div style="width: <?= intval($progress) ?>%"></div>
                                </div>
                                <div class="client-notes">
                                    <h4>Notas del cliente:</h4>
                                    <?php if ($notes) : ?>
                                        <?php foreach ($notes as $note) : ?>
                                            <div class="client-note-item"><?= esc_html($note->post_content) ?></div>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <div class="client-note-item">Sin notas disponibles.</div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="upm-card-block">
                                <h3>Entregas Programadas</h3>
                                <ul>
                                    <?php foreach ($milestones as $m): 
                                        $label = get_post_meta($m->ID, '_upm_milestone_date', true);
                                        echo '<li>' . esc_html($m->post_title) . ' <small>Due: ' . esc_html($label) . '</small></li>';
                                    endforeach; ?>
                                </ul>
                            </div>

                            <div class="upm-card-block">
                                <h3>Archivos adjuntos</h3>
                                <?php foreach ($files as $section => $items): ?>
                                    <h4><?= esc_html($section) ?></h4>
                                    <ul>
                                        <?php foreach ($items as $f): ?>
                                            <li><?= esc_html($f[0]) ?> <small><?= esc_html($f[1]) ?></small> <button>Descargar</button></li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Columna derecha -->
                        <div class="upm-right-column">
                            <div class="upm-card-block">
                                <h3>Acciones rápidas</h3>
                                <a href="#" class="upm-btn primary">Solicitar actualización</a><br><br>
                                <a href="#" class="upm-btn secondary">Ver brief</a>
                            </div>

                            <div class="upm-card-block">
                                <h3>Budget</h3>
                                <p><strong>Presupuesto Total:</strong> $<?= number_format($amount, 2) ?></p>
                                <p><strong>Total Cancelado:</strong> $<?= number_format($paid, 2) ?></p>
                                <p><strong>Saldo Pendiente:</strong> $<?= number_format($amount - $paid, 2) ?></p>
                            </div>

                            <div class="upm-card-block">
                                <h3>Actividad reciente</h3>
                                <ul>
                                    <?php foreach ($milestones as $m): ?>
                                        <li><strong><?= esc_html($m->post_title) ?></strong><br>
                                        <small><?= esc_html(get_post_meta($m->ID, '_upm_milestone_date', true)) ?></small></li>
                                    <?php endforeach; ?>
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
UPM_Shortcode_Project_View::register();
