<?php
class UPM_Shortcode_Projects {
    public static function register() {
        add_shortcode('upm_projects', [__CLASS__, 'render']);
    }

    public static function render() {
        if (!is_user_logged_in()) {
            return '<p>Debe iniciar sesión para acceder a sus proyectos.</p>';
        }

        $user = wp_get_current_user();
        $user_id = $user->ID;

        // Obtener filtros de URL
        $current_service = sanitize_text_field($_GET['service'] ?? '');
        $current_status  = sanitize_text_field($_GET['status'] ?? '');

        // Obtener proyectos del cliente
        $args = [
            'post_type'      => 'upm_project',
            'meta_key'       => '_upm_client_id',
            'meta_value'     => $user_id,
            'posts_per_page' => -1,
        ];

        if ($current_service) {
            $args['meta_query'][] = [
                'key'     => '_upm_area',
                'value'   => $current_service,
                'compare' => '='
            ];
        }

        if ($current_status) {
            $args['meta_query'][] = [
                'key'     => '_upm_status',
                'value'   => $current_status,
                'compare' => '='
            ];
        }

        $projects = get_posts($args);

        // Obtener todos los servicios y estados
        $all_projects = get_posts([
            'post_type'      => 'upm_project',
            'meta_key'       => '_upm_client_id',
            'meta_value'     => $user_id,
            'posts_per_page' => -1,
        ]);

        $services = [];
        $statuses = [];

        foreach ($all_projects as $p) {
            $services[] = get_post_meta($p->ID, '_upm_area', true);
            $statuses[] = get_post_meta($p->ID, '_upm_status', true);
        }

        $services = array_unique(array_filter($services));
        $statuses = array_unique(array_filter($statuses));

        // Estilos
        wp_enqueue_style('upm-projects-css', UPM_URL . 'public/css/projects.css', [], UPM_VERSION);

        ob_start();
        ?>
        <div class="upm-wrapper">
            <main class="upm-main">
                <div class="upm-projects-header">
                    <h2>Projects</h2>
                    <p>Manage and track all your active projects</p>
                    <div class="upm-filters">
                        <form method="get">
                            <select name="service" onchange="this.form.submit()">
                                <option value="">All Services</option>
                                <?php foreach ($services as $s): ?>
                                    <option value="<?= esc_attr($s) ?>" <?= selected($current_service, $s, false) ?>>
                                        <?= esc_html($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <select name="status" onchange="this.form.submit()">
                                <option value="">All Status</option>
                                <?php foreach ($statuses as $s): ?>
                                    <option value="<?= esc_attr($s) ?>" <?= selected($current_status, $s, false) ?>>
                                        <?= ucwords(str_replace('-', ' ', $s)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>

                <div class="upm-project-list">
                    <?php if (empty($projects)): ?>
                        <p>No hay proyectos que coincidan con los filtros.</p>
                    <?php else: ?>
                        <?php foreach ($projects as $project): 
                            $status     = get_post_meta($project->ID, '_upm_status', true);
                            $area       = get_post_meta($project->ID, '_upm_area', true);
                            $due_date   = get_post_meta($project->ID, '_upm_due_date', true);
                            $progress   = get_post_meta($project->ID, '_upm_progress', true) ?: 0;
                            $updated    = human_time_diff(strtotime($project->post_modified), current_time('timestamp')) . ' ago';
                        ?>
                        <div class="upm-project-card">
                            <div class="upm-project-header">
                                <h3><?= esc_html($project->post_title) ?></h3>
                                <span class="upm-badge badge-<?= esc_attr($status) ?>">
                                    <?= ucwords(str_replace('-', ' ', $status)) ?>
                                </span>
                                <a class="upm-btn" href="/dashboard/projects?pid=<?= $project->ID ?>">Manage</a>
                            </div>
                            <p class="upm-project-description"><?= esc_html(wp_trim_words($project->post_content, 20)) ?></p>
                            <div class="upm-project-meta">
                                <span class="upm-project-service"><?= esc_html($area) ?></span>
                                <span class="upm-project-dates">
                                    <?= $due_date ? 'Due: ' . esc_html($due_date) : 'Due: Ongoing' ?> &nbsp; • &nbsp;
                                    Updated <?= esc_html($updated) ?>
                                </span>
                            </div>
                            <div class="upm-progress-bar">
                                <div style="width: <?= intval($progress) ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }
}
UPM_Shortcode_Projects::register();
