<?php
class UPM_Shortcode_Projects {
    public static function register() {
        add_shortcode('upm_projects', [__CLASS__, 'render']);
        add_action('wp_ajax_upm_filter_projects', [__CLASS__, 'ajax_filter']);
    }

    public static function render() {
        if (!is_user_logged_in()) {
            return '<p>Debe iniciar sesión para acceder a sus proyectos.</p>';
        }

        wp_enqueue_script('upm-projects-filters', UPM_URL . 'public/js/projects-filters.js', [], UPM_VERSION, true);
        wp_localize_script('upm-projects-filters', 'upm_ajax', [
            'url' => admin_url('admin-ajax.php'),
        ]);

        $user = wp_get_current_user();
        $user_id = $user->ID;

        // Obtener todos los proyectos para armar filtros
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
                <li class="active"><a href="/dashboard/projects">Proyectos</a></li>
                <li><a href="/dashboard/invoices">Facturas</a></li>
                <li><a href="/dashboard/support">Soporte</a></li>
                <li><a href="/dashboard/my-account">Mi cuenta</a></li>
            </ul>
        </aside>
            <main class="upm-main">
            <div class="upm-projects-header">
                <div>
                    <h2>Projects</h2>
                    <p>Manage and track all your active projects</p>
                </div>
                <div class="upm-filters">
                    <form id="upm-project-filter-form">
                        <select name="service">
                            <option value="">All Services</option>
                            <?php foreach ($services as $s): ?>
                                <option value="<?= esc_attr($s) ?>">
                                    <?= esc_html($s) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <select name="status">
                            <option value="">All Status</option>
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= esc_attr($s) ?>">
                                    <?= ucwords(str_replace('-', ' ', $s)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <div class="upm-project-list" id="upm-project-cards">
                <?php self::render_cards(); ?>
            </div>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function render_cards() {
        $user_id = get_current_user_id();
        $current_service = sanitize_text_field($_REQUEST['service'] ?? '');
        $current_status  = sanitize_text_field($_REQUEST['status'] ?? '');

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

        if (empty($projects)) {
            echo '<p>No hay proyectos que coincidan con los filtros.</p>';
            return;
        }

        foreach ($projects as $project) {
            $status     = get_post_meta($project->ID, '_upm_status', true);
            $area       = get_post_meta($project->ID, '_upm_area', true);
            $due_date   = get_post_meta($project->ID, '_upm_due_date', true);
            $progress   = get_post_meta($project->ID, '_upm_progress', true) ?: 0;
            $updated    = human_time_diff(strtotime($project->post_modified), current_time('timestamp')) . ' ago';
            $short_description = get_post_meta($project->ID, '_upm_project_short_description', true);
            ?>
            <div class="upm-project-card">
                <div class="upm-project-header">
                    <h3><?= esc_html($project->post_title) ?></h3>
                    <div class="upm-project-actions">
                        <span class="project-badge badge-<?= esc_attr($status) ?>">
                            <?= ucwords(str_replace('-', ' ', $status)) ?>
                        </span>
                        <a class="upm-btn" href="/dashboard/project?id=<?= $project->ID ?>">
                        <?= file_get_contents(UPM_PATH . 'public/icons/settings.svg'); ?>
                        Gestionar
                        </a>
                    </div>
                </div>
                <?php if (!empty($short_description)) : ?>
                    <p class="upm-project-description"><?= esc_html($short_description) ?></p>
                <?php endif; ?>
                <div class="upm-project-meta">
                    <span class="meta-area"><?= esc_html($area) ?></span>
                    <span class="meta-date">
                        <span class="meta-icon"><?= file_get_contents(UPM_PATH . 'public/icons/calendar.svg'); ?></span>
                        <?= $due_date ? 'Due: ' . esc_html($due_date) : 'Due: Ongoing' ?>
                    </span>
                    <span class="meta-updated"> Updated <?= esc_html($updated) ?></span>
                </div>
                <div class="progress-header">
                    <span class="progress-title">Progreso</span>
                    <span class="progress-amount"><?= intval($progress) ?>%</span>
                </div>
                <div class="upm-progress-bar upm-progress-sm">
                    <div style="width: <?= intval($progress) ?>%"></div>
                </div>
            </div>
            <?php
        }
    }

    public static function ajax_filter() {
        self::render_cards();
        wp_die();
    }
}
UPM_Shortcode_Projects::register();