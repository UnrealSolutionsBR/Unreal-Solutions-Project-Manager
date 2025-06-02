<?php
class UPM_Shortcode_Projects {
    public static function register() {
        add_shortcode('upm_projects', [__CLASS__, 'render']);
    }

    public static function render() {
        if (!is_user_logged_in()) {
            return '<p>Debe iniciar sesión para ver sus proyectos.</p>';
        }

        $user     = wp_get_current_user();
        $user_id  = $user->ID;
        $projects = get_posts([
            'post_type'      => 'upm_project',
            'meta_key'       => '_upm_client_id',
            'meta_value'     => $user_id,
            'posts_per_page' => -1
        ]);

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
                <div class="upm-header">
                    <h2>Mis Proyectos</h2>
                    <p>Gestiona y haz seguimiento a todos tus proyectos activos</p>
                </div>

                <div class="upm-invoice-filters">
                    <select id="filter-service">
                        <option value="todos">Todos los Servicios</option>
                        <option value="Web Development">Web Development</option>
                        <option value="Hosting">Hosting</option>
                        <option value="Social Media">Social Media</option>
                        <option value="Design">Design</option>
                        <option value="Marketing">Marketing</option>
                    </select>

                    <select id="filter-status">
                        <option value="todos">Todos los Estados</option>
                        <option value="activo">Activo</option>
                        <option value="en-curso">En curso</option>
                        <option value="esperando-revision">Esperando revisión</option>
                        <option value="completado">Completado</option>
                    </select>
                </div>

                <div id="upm-projects-container">
                    <?php foreach ($projects as $project):
                        $area        = get_post_meta($project->ID, '_upm_area', true);
                        $due_date    = get_post_meta($project->ID, '_upm_due_date', true);
                        $progress    = get_post_meta($project->ID, '_upm_progress', true) ?: 0;
                        $status      = get_post_meta($project->ID, '_upm_status', true);
                        $updated     = human_time_diff(get_the_modified_time('U', $project), current_time('timestamp')) . ' ago';
                        ?>
                        <div class="upm-project-invoice-block"
                             data-service="<?= esc_attr($area) ?>"
                             data-status="<?= esc_attr($status) ?>">
                            <div class="upm-project-header">
                                <strong><?= esc_html($project->post_title) ?></strong>
                                <span class="badge badge-<?= esc_attr($status) ?>">
                                    <?= ucwords(str_replace('-', ' ', $status)) ?>
                                </span>
                                <div class="upm-project-meta">
                                    <span class="meta-area"><?= esc_html($area) ?></span>
                                    <span class="meta-date">
                                        <span class="meta-icon"><?= file_get_contents(UPM_PATH . 'public/icons/calendar.svg'); ?></span>
                                        <?= $due_date ?: 'Sin fecha' ?>
                                    </span>
                                    <span class="meta-date">Actualizado <?= esc_html($updated) ?></span>
                                </div>
                            </div>
                            <div class="progress">
                                <div class="bar" style="width: <?= intval($progress) ?>%"></div>
                            </div>
                            <div class="progress-footer">
                                <span class="progress-percent"><?= intval($progress) ?>% completado</span>
                            </div>
                            <div style="text-align:right;">
                                <a href="#" class="upm-btn primary">Gestionar</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }
}
UPM_Shortcode_Projects::register();
