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
        $project_id = absint($atts['id']);
        $user_id = get_current_user_id();
        $user = wp_get_current_user();

        // Validar existencia del proyecto
        $project = get_post($project_id);
        if (!$project || $project->post_type !== 'upm_project') {
            return '<p>Proyecto no encontrado.</p>';
        }

        // Verificar que el proyecto pertenezca al usuario actual
        $client_id = (int) get_post_meta($project_id, '_upm_client_id', true);
        if ($client_id !== $user_id) {
            return '<p>No tienes permiso para ver este proyecto.</p>';
        }

        // Obtener metadatos
        $area = get_post_meta($project_id, '_upm_area', true);
        $status = get_post_meta($project_id, '_upm_status', true);
        $progress = get_post_meta($project_id, '_upm_progress', true);
        $due = get_post_meta($project_id, '_upm_due_date', true);
        $short_description = get_post_meta($project_id, '_upm_short_description', true);
        $start = get_post_meta($project_id, '_upm_start_date', true);

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
                    <li><a href="/dashboard/projects">Proyectos</a></li>
                    <li><a href="/dashboard/invoices">Facturas</a></li>
                    <li><a href="/dashboard/support">Soporte</a></li>
                    <li><a href="/dashboard/my-account">Mi cuenta</a></li>
                </ul>
            </aside>

            <main class="upm-main">
                <h2><?= esc_html($project->post_title) ?></h2>
                <?php if ($short_description): ?>
                    <p><?= esc_html($short_description) ?></p>
                <?php endif; ?>

                <div class="upm-project-meta">
                    <p><strong>Área:</strong> <?= esc_html($area) ?></p>
                    <p><strong>Estado:</strong> <?= esc_html(ucwords(str_replace('-', ' ', $status))) ?></p>
                    <p><strong>Inicio:</strong> <?= esc_html($start ?: 'No definido') ?></p>
                    <p><strong>Entrega estimada:</strong> <?= esc_html($due ?: 'No definida') ?></p>
                </div>

                <div class="progress-header">
                    <span class="progress-title">Progreso</span>
                    <span class="progress-amount"><?= intval($progress) ?>%</span>
                </div>
                <div class="upm-progress-bar">
                    <div style="width: <?= intval($progress) ?>%"></div>
                </div>

                <a href="/dashboard/projects" class="upm-btn" style="margin-top: 20px;">← Volver a proyectos</a>
            </main>
        </div>
        <?php
        return ob_get_clean();
    }
}
UPM_Shortcode_Project_View::register();
