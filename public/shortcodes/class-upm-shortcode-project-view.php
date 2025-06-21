<?php
class UPM_Shortcode_Project_View {
    public static function register() {
        add_shortcode('upm_project_view', [__CLASS__, 'render']);
    }

    public static function render($atts) {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('Debe iniciar sesión para ver este proyecto.', 'upm') . '</p>';
        }

        $atts = shortcode_atts(['id' => 0], $atts);
        $project_id = absint($atts['id'] ?: ($_GET['id'] ?? 0));
        $user_id = get_current_user_id();
        $user = wp_get_current_user();

        $project = get_post($project_id);
        if (!$project || $project->post_type !== 'upm_project') {
            return '<p>' . esc_html__('Proyecto no encontrado.', 'upm') . '</p>';
        }

        $client_id = (int) get_post_meta($project_id, '_upm_client_id', true);
        if ($client_id !== $user_id) {
            return '<p>' . esc_html__('No tienes permiso para ver este proyecto.', 'upm') . '</p>';
        }

        // Metadatos del proyecto
        $project_type = get_post_meta($project_id, '_upm_project_type', true);
        $status = get_post_meta($project_id, '_upm_status', true);
        $progress = get_post_meta($project_id, '_upm_progress', true);
        $due = get_post_meta($project_id, '_upm_due_date', true);
        $start = get_post_meta($project_id, '_upm_start_date', true);
        $amount = get_post_meta($project_id, '_upm_project_amount', true);
        $short_description = get_post_meta($project->ID, '_upm_short_description', true);

        //Notas
        $requests = get_posts([
            'post_type' => 'upm_request',
            'meta_key' => '_upm_request_project_id',
            'meta_value' => $project_id,
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => 3,
        ]);

        //Actividades
        $activities = get_posts([
            'post_type'      => 'upm_activity',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => [
                [
                    'key'   => '_upm_activity_project_id',
                    'value' => $project_id,
                ]
            ]
        ]);

        //Hitos del proyecto
        $milestones = get_posts([
            'post_type'      => 'upm_milestone',
            'meta_key'       => '_upm_milestone_date',
            'orderby'        => 'meta_value',
            'meta_query'     => [
                [
                    'key'   => '_upm_milestone_project_id',
                    'value' => $project_id,
                ]
            ],
            'order'          => 'ASC',
            'posts_per_page' => -1,
        ]);

        // Obtener archivos del proyecto actual
        $args = [
            'post_type'      => 'upm_file',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'   => '_upm_file_project_id',
                    'value' => $project_id,
                    'compare' => '='
                ]
            ]
        ];

        $file_query = new WP_Query($args);
        $files = [];

        if ($file_query->have_posts()) {
            foreach ($file_query->posts as $file_post) {
                $category   = get_post_meta($file_post->ID, '_upm_file_category', true) ?: 'Sin categoría';
                $title      = get_the_title($file_post);
                $url        = get_post_meta($file_post->ID, '_upm_file_url', true);
                $extension = strtoupper(pathinfo($url, PATHINFO_EXTENSION));
                $size       = get_post_meta($file_post->ID, '_upm_file_size', true);
                $uploaded   = get_the_date('Y-m-d', $file_post->ID);
            
                $description = "{$extension} - {$size} - {$uploaded}";
            
                $files[$category][] = [$title, $description, $url];
            }
        }

        //Facturas y calculos de pagos
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
                    <a href="/dashboard/projects" class="upm-btn-action">
                        <?= file_get_contents(UPM_PATH . 'public/icons/arrow.svg'); ?>
                    </a>
                        <div>
                        <h2><?= esc_html($project->post_title) ?></h2>
                        <p><?= esc_html($project_type) ?></p>
                        </div>
                    </div>
                    <span class="project-badge badge-<?= esc_attr($status) ?>">
                        <?= esc_html(ucwords(str_replace('-', ' ', $status))) ?>
                    </span>
                </div>
                <div class="upm-overview-section">
                    <div class="upm-overview-grid">
                        <!-- Columna izquierda -->
                        <div class="upm-left-column">
                            <div class="upm-card-block">
                                <h3><?= esc_html__('Resumen del Proyecto', 'upm') ?></h3>
                                <p class="upm-project-description"><?= esc_html($short_description) ?></p>
                                <div class="upm-project-dates">
                                    <div class="upm-date-item">
                                        <small><?= esc_html__('Fecha de inicio:', 'upm') ?></small>
                                        <p><?= esc_html($start) ?></p>
                                    </div>
                                    <div class="upm-date-item">
                                        <small><?= esc_html__('Fecha de entrega:', 'upm') ?></small>
                                        <p><?= esc_html($due) ?></p>
                                    </div>
                                </div>
                                <div class="progress-header">
                                    <span class="progress-title"><?= esc_html__('Progreso', 'upm') ?></span>
                                    <span class="progress-amount"><?= intval($progress) ?>%</span>
                                </div>
                                <div class="upm-progress-bar upm-progress-md">
                                    <div style="width: <?= intval($progress) ?>%"></div>
                                </div>
                                <div class="client-notes">
                                    <h4><?= esc_html__('Solicitudes del cliente:', 'upm') ?></h4>
                                    <?php if ($requests) : ?>
                                        <?php foreach ($requests as $index => $req) :
                                            $author_name = get_the_author_meta('display_name', $req->post_author);
                                            $date        = get_the_date('M d, Y', $req);
                                        ?>
                                            <div class="client-note-item">
                                                <div class="client-note-meta">
                                                    <span>
                                                        <?= file_get_contents(UPM_PATH . 'public/icons/user.svg'); ?>
                                                        <?= esc_html($author_name) ?>
                                                    </span>
                                                    <span>
                                                        <?= file_get_contents(UPM_PATH . 'public/icons/calendar.svg'); ?>
                                                        <?= esc_html($date) ?>
                                                    </span>
                                                </div>
                                                <p>
                                                    <?= esc_html($req->post_content) ?>
                                                </p>
                                            </div>
                                            <?php if ($index < count($requests) - 1) : ?>
                                                <hr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <div class="client-note-item"><?= esc_html__('Sin solicitudes disponibles.', 'upm') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="upm-card-block">
                                <h3><?= esc_html__('Entregas Programadas', 'upm') ?></h3>
                                <ul class="upm-list">
                                    <?php foreach ($milestones as $m): 
                                        $title  = $m->post_title;
                                        $date   = get_post_meta($m->ID, '_upm_milestone_date', true);
                                        $status = get_post_meta($m->ID, '_upm_milestone_status', true) ?: 'pending';
                                    
                                        $icon_file = match ($status) {
                                            'completed'   => 'completed-projects.svg',
                                            'in_progress' => 'clock.svg',
                                            default       => 'clock.svg',
                                        };
                                        $icon_svg = file_get_contents(UPM_PATH . 'public/icons/' . $icon_file);
                                    
                                        $badge_class = match ($status) {
                                            'completed'   => 'badge-completado',
                                            'in_progress' => 'badge-esperando-revision',
                                            'pending'     => 'badge-pendiente',
                                            default       => 'badge-pendiente',
                                        };
                                        // Traducción del estado
                                        $translated_status = match ($status) {
                                            'completed'   => 'Completado',
                                            'in_progress' => 'En progreso',
                                            'pending'     => 'Pendiente',
                                            default       => ucfirst($status),
                                        };
                                    ?>
                                    <li class="upm-item">
                                        <span class="upm-item-icon"><?= $icon_svg ?></span>
                                        <div class="upm-item-content">
                                            <p><?= esc_html($title) ?></p>
                                            <div class="upm-item-date"><?= esc_html__('Entrega:', 'upm') ?> <?= esc_html($date) ?></div>
                                        </div>
                                        <span class="project-badge <?= esc_attr($badge_class) ?>">
                                            <?= esc_html($translated_status) ?>
                                        </span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="upm-card-block">
                                <h3><?= esc_html__('Archivos adjuntos', 'upm') ?></h3>
                                <?php foreach ($files as $section => $items): ?>
                                    <?php
                                        $category_class_map = [
                                            'Legal'         => 'upm-file-legal',
                                            'Facturación'   => 'upm-file-invoice',
                                            'Diseño'        => 'upm-file-design',
                                            'Documentación' => 'upm-file-doc'
                                        ];
                                        $class = $category_class_map[$section] ?? 'upm-file-default';
                                    ?>
                                    <div class="upm-file-category <?= esc_attr($class) ?>">
                                        <h4><?= esc_html($section) ?></h4>
                                    </div>
                                    <ul class="upm-list">
                                        <?php foreach ($items as $f): ?>
                                            <?php [$name, $meta, $url, $autogen] = $f; ?>
                                            <li class="upm-item">
                                                <span class="upm-item-icon"><?= file_get_contents(UPM_PATH . 'public/icons/docs.svg') ?></span>
                                                <div class="upm-item-content">
                                                    <p>
                                                        <?= esc_html($name) ?>
                                                        <?php if ($autogen): ?>
                                                            <span class="project-badge badge-muted"><?= esc_html__('Auto-generado', 'upm') ?></span>
                                                        <?php endif; ?>
                                                    </p>
                                                    <div class="upm-item-date"><?= esc_html($meta) ?></div>
                                                </div>
                                                <a href="<?= esc_url($url) ?>" target="_blank" class="upm-btn-action" download>
                                                    <?= file_get_contents(UPM_PATH . 'public/icons/download.svg') ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Columna derecha -->
                        <div class="upm-right-column">
                            <div class="upm-card-block">
                                <h3><?= esc_html__('Acciones rápidas', 'upm') ?></h3>
                                <a href="#" id="upm-request-update-btn" class="upm-btn full primary">
                                    <?= file_get_contents(UPM_PATH . 'public/icons/chat-bubbles.svg') ?>
                                    <?= esc_html__('Solicitar actualización', 'upm') ?>
                                </a><br><br>
                                <a href="#" id="upm-brief-btn" class="upm-btn full secondary">
                                    <?= file_get_contents(UPM_PATH . 'public/icons/view.svg') ?>
                                    <?= esc_html__('Ver brief', 'upm') ?>
                                </a>
                            </div>

                            <div class="upm-card-block">
                                <h3><?= esc_html__('Presupuesto', 'upm') ?></h3>

                                <div class="upm-budget-row">
                                    <p><?= esc_html__('Presupuesto Total:', 'upm') ?></p>
                                    <span class="upm-budget-total">$<?= number_format($amount, 2) ?></span>
                                </div>

                                <div class="upm-budget-row">
                                    <p><?= esc_html__('Total Cancelado:', 'upm') ?></p>
                                    <span class="upm-budget-paid">$<?= number_format($paid, 2) ?></span>
                                </div>

                                <div class="upm-budget-row">
                                    <p><?= esc_html__('Saldo Pendiente:', 'upm') ?></p>
                                    <span class="upm-budget-remaining">$<?= number_format($amount - $paid, 2) ?></span>
                                </div>
                            </div>

                            <div class="upm-card-block">
                                <h3><?= esc_html__('Actividad reciente', 'upm') ?></h3>
                                <ul class="upm-activity-list">
                                    <?php if (!empty($activities)): ?>
                                        <?php foreach ($activities as $a): 
                                            $title     = $a->post_title;
                                            $date      = get_the_date('Y-m-d', $a);
                                            $short_desc = get_post_meta($a->ID, '_upm_activity_description', true);
                                        ?>
                                        <li class="upm-activity-item">
                                            <span class="upm-activity-bullet"></span>
                                            <div class="upm-activity-content">
                                                <p class="upm-activity-title"><?= esc_html($title) ?></p>
                                                <p class="upm-activity-desc"><?= esc_html($short_desc ?: $content) ?></p>
                                                <small class="upm-activity-date"><?= esc_html($date) ?></small>
                                            </div>
                                        </li>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <li><?= esc_html__('No hay actividad reciente.', 'upm') ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="upm-request-modal" class="upm-modal upm-modal-request hidden animated">
                    <div class="upm-modal-content">
                        <button class="upm-modal-close" aria-label="Cerrar modal">&times;</button>
                        <h3><?= esc_html__('Solicitar Actualización', 'upm') ?></h3>
                        <label for="upm-update-type"><?= esc_html__('Tipo de Actualización', 'upm') ?></label>
                        <select id="upm-update-type" class="upm-update-type">
                            <option value="change_request">Solicitar cambio</option>
                            <option value="approval_request">Aprobación</option>
                            <option value="suggestion">Sugerencia</option>
                            <option value="review_request">Solicitar revisión</option>
                            <option value="pending_delivery">Entrega pendiente</option>
                            <option value="other">Otros</option>
                        </select>
                        <label for="upm-update-message"><?= esc_html__('Mensaje', 'upm') ?></label>
                        <textarea id="upm-update-message" class="upm-update-message" rows="4" placeholder="<?= esc_attr__('Describa brevemente la actualización que necesita...', 'upm') ?>"></textarea>
                        <div class="upm-modal-actions">
                            <button class="upm-btn secondary" id="upm-cancel-request"><?= esc_html__('Cancelar', 'upm') ?></button>
                            <button class="upm-btn primary" id="upm-send-request">
                                <?= file_get_contents(UPM_PATH . 'public/icons/send.svg') ?>
                                <?= esc_html__('Enviar solicitud', 'upm') ?>                        
                            </button>
                        </div>
                    </div>
                </div>
                <div id="upm-brief-modal" class="upm-modal upm-modal-brief hidden">
                  <div class="upm-modal-content upm-brief-content">
                    <button class="upm-modal-close" aria-label="Cerrar modal">&times;</button>
                    <h2><?= file_get_contents(UPM_PATH . 'public/icons/docs.svg'); ?><?= esc_html__('Brief del proyecto', 'upm') ?></h2>

                    <div class="upm-brief-section">
                      <h3><?= esc_html__('Detalles del proyecto', 'upm') ?></h3>
                      <p><strong><?= esc_html__('Nombre del proyecto:', 'upm') ?></strong><br><?= get_the_title($project_id) ?></p>
                      <p><strong><?= esc_html__('Tipo de servicio:', 'upm') ?></strong><br><?= esc_html(get_post_meta($project_id, '_upm_project_type', true)) ?></p>
                      <p><strong><?= esc_html__('Descripción:', 'upm') ?></strong><br><?= esc_html(get_post_meta($project_id, '_upm_short_description', true)) ?></p>
                    </div>

                    <div class="upm-brief-grid">
                      <div class="upm-brief-box">
                        <h4><?= file_get_contents(UPM_PATH . 'public/icons/calendar.svg'); ?><?= esc_html__('Cronograma', 'upm') ?></h4>
                        <p><strong><?= esc_html__('Fecha de inicio:', 'upm') ?></strong><br><?= esc_html(get_post_meta($project_id, '_upm_start_date', true)) ?></p>
                        <p><strong><?= esc_html__('Fecha de entrega:', 'upm') ?></strong><br><?= esc_html(get_post_meta($project_id, '_upm_due_date', true)) ?></p>
                      </div>
                      <div class="upm-brief-box">
                        <h4><?= file_get_contents(UPM_PATH . 'public/icons/dollar.svg'); ?><?= esc_html__('Presupuesto', 'upm') ?></h4>
                        <p><strong><?= esc_html__('Presupuesto total:', 'upm') ?></strong><br>$<?= number_format((float)get_post_meta($project_id, '_upm_project_amount', true), 0, '.', ',') ?></p>
                      </div>
                    </div>

                    <div class="upm-brief-section">
                      <h3><?= file_get_contents(UPM_PATH . 'public/icons/target.svg'); ?><?= esc_html__('Objetivos del proyecto', 'upm') ?></h3>
                      <ul>
                        <?php foreach (explode("\n", get_post_meta($project_id, '_upm_objectives', true)) as $item): ?>
                          <?php if (trim($item)) echo '<li>' . esc_html(trim($item)) . '</li>'; ?>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                        
                    <div class="upm-brief-section">
                      <h3><?= file_get_contents(UPM_PATH . 'public/icons/list.svg'); ?><?= esc_html__('Alcance del proyecto', 'upm') ?></h3>
                      <ul>
                        <?php foreach (explode("\n", get_post_meta($project_id, '_upm_scope', true)) as $item): ?>
                          <?php if (trim($item)) echo '<li>' . esc_html(trim($item)) . '</li>'; ?>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                        
                    <div class="upm-brief-section">
                      <h3><?= file_get_contents(UPM_PATH . 'public/icons/setting.svg'); ?><?= esc_html__('Requisitos técnicos', 'upm') ?></h3>
                      <ul>
                        <?php foreach (explode("\n", get_post_meta($project_id, '_upm_tech_requirements', true)) as $item): ?>
                          <?php if (trim($item)) echo '<li>' . esc_html(trim($item)) . '</li>'; ?>
                        <?php endforeach; ?>
                      </ul>
                    </div>
                  </div>
                </div>
                <div id="upm-toast-container" class="upm-toast-container"></div>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }
}
UPM_Shortcode_Project_View::register();
