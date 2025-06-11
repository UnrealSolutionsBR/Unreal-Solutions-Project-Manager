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
        $area = get_post_meta($project_id, '_upm_area', true);
        $status = get_post_meta($project_id, '_upm_status', true);
        $progress = get_post_meta($project_id, '_upm_progress', true);
        $due = get_post_meta($project_id, '_upm_due_date', true);
        $start = get_post_meta($project_id, '_upm_start_date', true);
        $amount = get_post_meta($project_id, '_upm_project_amount', true);
        $short_description = get_post_meta($project->ID, '_upm_short_description', true);

        //Notas
        $notes = get_posts([
            'post_type' => 'upm_note',
            'meta_key' => '_upm_note_project_id',
            'meta_value' => $project_id,
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => 3,
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
                    <a href="/dashboard/projects" class="upm-back-btn">
                        <?= file_get_contents(UPM_PATH . 'public/icons/arrow.svg'); ?>
                    </a>
                        <div>
                        <h2><?= esc_html($project->post_title) ?></h2>
                        <p><?= esc_html($area) ?></p>
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
                                <div class="upm-progress-bar">
                                    <div style="width: <?= intval($progress) ?>%"></div>
                                </div>
                                <div class="client-notes">
                                    <h4><?= esc_html__('Notas del cliente:', 'upm') ?></h4>
                                    <?php if ($notes) : ?>
                                        <?php foreach ($notes as $index => $note) : 
                                            $author_name = get_the_author_meta('display_name', $note->post_author);
                                            $date = get_the_date('M d, Y', $note);
                                        ?>
                                            <div class="client-note-item">
                                                <div class="client-note-meta">
                                                    <span>
                                                        <?= file_get_contents(UPM_PATH . 'public/icons/support-tickets.svg'); ?>
                                                        <?= esc_html($author_name) ?>
                                                    </span>
                                                    <span>
                                                        <?= file_get_contents(UPM_PATH . 'public/icons/calendar.svg'); ?>    
                                                        <?= esc_html($date) ?>
                                                    </span>
                                                </div>
                                                <p><?= esc_html($note->post_content) ?></p>
                                            </div>
                                            <?php if ($index < count($notes) - 1) : ?>
                                                <hr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <div class="client-note-item"><?= esc_html__('Sin notas disponibles.', 'upm') ?></div>
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
                                    <li class="upm-deliverable-item">
                                        <span class="upm-deliverable-icon"><?= $icon_svg ?></span>
                                        <div class="upm-deliverable-content">
                                            <p><?= esc_html($title) ?></p>
                                            <div class="upm-deliverable-date"><?= esc_html__('Entrega:', 'upm') ?> <?= esc_html($date) ?></div>
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
                                            <?php [$name, $meta, $url] = $f; ?>
                                            <li class="upm-file-item">
                                                <span class="upm-file-icon"><?= file_get_contents(UPM_PATH . 'public/icons/docs.svg') ?></span>
                                                <div class="upm-file-content">
                                                    <strong><?= esc_html($name) ?></strong><br>
                                                    <small><?= esc_html($meta) ?></small><br>
                                                    <a href="<?= esc_url($url) ?>" target="_blank" class="upm-btn tiny"><?= esc_html__('Descargar', 'upm') ?></a>
                                                </div>
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
                                <a href="#" class="upm-btn primary"><?= esc_html__('Solicitar actualización', 'upm') ?></a><br><br>
                                <a href="#" class="upm-btn secondary"><?= esc_html__('Ver brief', 'upm') ?></a>
                            </div>

                            <div class="upm-card-block">
                                <h3><?= esc_html__('Presupuesto', 'upm') ?></h3>
                                <p><strong><?= esc_html__('Presupuesto Total:', 'upm') ?></strong> $<?= number_format($amount, 2) ?></p>
                                <p><strong><?= esc_html__('Total Cancelado:', 'upm') ?></strong> $<?= number_format($paid, 2) ?></p>
                                <p><strong><?= esc_html__('Saldo Pendiente:', 'upm') ?></strong> $<?= number_format($amount - $paid, 2) ?></p>
                            </div>

                            <div class="upm-card-block">
                            <h3><?= esc_html__('Actividad reciente', 'upm') ?></h3>
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
