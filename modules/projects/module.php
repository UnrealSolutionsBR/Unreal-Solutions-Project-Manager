<?php
class UPM_Module_Projects {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_project_meta_boxes']);
        add_action('add_meta_boxes', [__CLASS__, 'add_milestone_meta_box']);
        add_action('save_post_upm_project', [__CLASS__, 'save_project_meta']);
    }

    public static function register_post_type() {
        $labels = [
            'name'               => 'Proyectos',
            'singular_name'      => 'Proyecto',
            'menu_name'          => 'Proyectos',
            'name_admin_bar'     => 'Proyecto',
            'add_new'            => 'Agregar nuevo',
            'add_new_item'       => 'Agregar nuevo proyecto',
            'edit_item'          => 'Editar proyecto',
            'view_item'          => 'Ver proyecto',
            'all_items'          => 'Proyectos',
            'search_items'       => 'Buscar proyectos',
            'not_found'          => 'No se encontraron proyectos',
            'not_found_in_trash' => 'No hay proyectos en la papelera',
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => 'upm_dashboard',
            'supports'           => ['title'],
            'capability_type'    => 'post',
        ];

        register_post_type('upm_project', $args);
    }

    public static function add_project_meta_boxes() {
        add_meta_box('upm_project_overview', 'Project Overview', [__CLASS__, 'render_overview_meta_box'], 'upm_project');
        add_meta_box('upm_project_timeline', 'Timeline', [__CLASS__, 'render_timeline_meta_box'], 'upm_project');
        add_meta_box('upm_project_budget', 'Budget', [__CLASS__, 'render_budget_meta_box'], 'upm_project');
        add_meta_box('upm_project_objectives', 'Project Objectives', [__CLASS__, 'render_objectives_meta_box'], 'upm_project');
        add_meta_box('upm_project_scope', 'Scope of Work', [__CLASS__, 'render_scope_meta_box'], 'upm_project');
        add_meta_box('upm_project_tech', 'Technical Requirements', [__CLASS__, 'render_tech_meta_box'], 'upm_project');
    }

    public static function render_overview_meta_box($post) {
        $client_id = get_post_meta($post->ID, '_upm_client_id', true);
        $project_type = get_post_meta($post->ID, '_upm_project_type', true);
        $short_description = get_post_meta($post->ID, '_upm_short_description', true);
        $customers = get_users(['role' => 'customer']);
        ?>
        <p><label><strong>Cliente asignado:</strong></label><br>
            <select name="upm_client_id" style="width:100%;">
                <option value="">— Seleccionar —</option>
                <?php foreach ($customers as $user): ?>
                    <option value="<?= esc_attr($user->ID) ?>" <?= selected($client_id, $user->ID) ?>>
                        <?= esc_html($user->display_name . ' (' . $user->user_email . ')') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p><label><strong>Tipo de proyecto:</strong></label><br>
        <select name="upm_project_type" style="width:100%;">
            <option value="">— Seleccionar tipo —</option>
            <option value="Desarrollo web" <?= selected($project_type, 'Desarrollo web') ?>>Desarrollo web</option>
            <option value="Marketing digital" <?= selected($project_type, 'Marketing digital') ?>>Marketing digital</option>
            <option value="Automatización" <?= selected($project_type, 'Automatización') ?>>Automatización</option>
            <option value="Edición de video" <?= selected($project_type, 'Edición de video') ?>>Edición de video</option>
        </select>
        </p>
        <p><label><strong>Descripción breve:</strong></label><br>
            <textarea name="upm_short_description" rows="3" style="width:100%;"><?= esc_textarea($short_description) ?></textarea>
        </p>
        <?php
    }

    public static function render_timeline_meta_box($post) {
        $start_date = get_post_meta($post->ID, '_upm_start_date', true);
        $due_date = get_post_meta($post->ID, '_upm_due_date', true);
        $status = get_post_meta($post->ID, '_upm_status', true);
        $progress = get_post_meta($post->ID, '_upm_progress', true);
        $status_options = [
            'activo' => 'Activo',
            'en-curso' => 'En curso',
            'completado' => 'Completado',
            'esperando-revision' => 'Esperando revisión'
        ];
        ?>
        <p><label><strong>Fecha de inicio:</strong></label><br>
            <input type="date" name="upm_start_date" value="<?= esc_attr($start_date) ?>" />
        </p>
        <p><label><strong>Fecha de entrega:</strong></label><br>
            <input type="date" name="upm_due_date" value="<?= esc_attr($due_date) ?>" />
        </p>
        <p><label><strong>Estado:</strong></label><br>
            <select name="upm_status">
                <?php foreach ($status_options as $key => $label): ?>
                    <option value="<?= esc_attr($key) ?>" <?= selected($status, $key) ?>>
                        <?= esc_html($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <p><label><strong>Progreso (%):</strong></label><br>
            <input type="number" name="upm_progress" value="<?= esc_attr($progress) ?>" min="0" max="100" />
        </p>
        <?php
    }

    public static function render_budget_meta_box($post) {
        $amount = get_post_meta($post->ID, '_upm_project_amount', true);
        $billing_type = get_post_meta($post->ID, '_upm_billing_type', true) ?: 'pago-unico';
        $billing_installments = get_post_meta($post->ID, '_upm_billing_installments', true) ?: 2;
        $billing_frequency = get_post_meta($post->ID, '_upm_billing_frequency', true) ?: 'mensual';
        ?>
        <p><label><strong>Monto total (USD):</strong></label><br>
            <input type="number" name="upm_project_amount" value="<?= esc_attr($amount) ?>" step="0.01" />
        </p>
        <p><label><strong>Tipo de facturación:</strong></label><br>
            <select name="upm_billing_type" id="upm_billing_type">
                <option value="pago-unico" <?= selected($billing_type, 'pago-unico') ?>>Pago único</option>
                <option value="suscripcion" <?= selected($billing_type, 'suscripcion') ?>>Suscripción</option>
            </select>
        </p>
        <div id="cuotas_section" style="<?= $billing_type === 'pago-unico' ? '' : 'display:none;' ?>">
            <p><label><strong>Número de cuotas:</strong></label><br>
                <input type="number" name="upm_billing_installments" value="<?= esc_attr($billing_installments) ?>" min="1" max="12" />
            </p>
        </div>
        <div id="frecuencia_section" style="<?= $billing_type === 'suscripcion' ? '' : 'display:none;' ?>">
            <p><label><strong>Frecuencia de facturación:</strong></label><br>
                <select name="upm_billing_frequency">
                    <option value="mensual" <?= selected($billing_frequency, 'mensual') ?>>Mensual</option>
                    <option value="trimestral" <?= selected($billing_frequency, 'trimestral') ?>>Trimestral</option>
                </select>
            </p>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const type = document.getElementById('upm_billing_type');
            const cuotas = document.getElementById('cuotas_section');
            const freq = document.getElementById('frecuencia_section');
            function toggleBillingSections() {
                cuotas.style.display = type.value === 'pago-unico' ? '' : 'none';
                freq.style.display = type.value === 'suscripcion' ? '' : 'none';
            }
            type.addEventListener('change', toggleBillingSections);
        });
        </script>
        <?php
    }

    public static function render_objectives_meta_box($post) {
        $value = get_post_meta($post->ID, '_upm_objectives', true);
        echo '<textarea name="upm_objectives" style="width:100%; min-height:120px;">' . esc_textarea($value) . '</textarea>';
    }

    public static function render_scope_meta_box($post) {
        $value = get_post_meta($post->ID, '_upm_scope', true);
        echo '<textarea name="upm_scope" style="width:100%; min-height:120px;">' . esc_textarea($value) . '</textarea>';
    }

    public static function render_tech_meta_box($post) {
        $value = get_post_meta($post->ID, '_upm_tech_requirements', true);
        echo '<textarea name="upm_tech_requirements" style="width:100%; min-height:120px;">' . esc_textarea($value) . '</textarea>';
    }

    public static function save_project_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $fields = [
            'upm_client_id'             => '_upm_client_id',
            'upm_start_date'            => '_upm_start_date',
            'upm_due_date'              => '_upm_due_date',
            'upm_status'                => '_upm_status',
            'upm_project_type'          => '_upm_project_type',
            'upm_progress'              => '_upm_progress',
            'upm_project_amount'        => '_upm_project_amount',
            'upm_short_description'     => '_upm_short_description',
            'upm_billing_type'          => '_upm_billing_type',
            'upm_billing_installments'  => '_upm_billing_installments',
            'upm_billing_frequency'     => '_upm_billing_frequency',
            'upm_objectives'            => '_upm_objectives',
            'upm_scope'                 => '_upm_scope',
            'upm_tech_requirements'     => '_upm_tech_requirements',
        ];

        foreach ($fields as $form_field => $meta_key) {
            if (isset($_POST[$form_field])) {
                update_post_meta($post_id, $meta_key, sanitize_textarea_field($_POST[$form_field]));
            }
        }
    }

    public static function add_milestone_meta_box() {
        add_meta_box(
            'upm_project_milestones',
            'Hitos del proyecto',
            [__CLASS__, 'render_milestone_box'],
            'upm_project',
            'normal',
            'default'
        );
    }

    public static function render_milestone_box($post) {
        $project_id = $post->ID;
        $milestones = get_posts([
            'post_type'  => 'upm_milestone',
            'meta_key'   => '_upm_milestone_project_id',
            'meta_value' => $project_id,
            'orderby'    => 'meta_value',
            'order'      => 'ASC',
            'posts_per_page' => -1,
        ]);

        echo '<p><a href="' . admin_url('post-new.php?post_type=upm_milestone') . '&upm_project_id=' . $project_id . '" class="button button-primary">Agregar nuevo hito</a></p>';

        if (empty($milestones)) {
            echo '<p>No hay hitos para este proyecto.</p>';
        } else {
            echo '<ul style="padding-left:20px;">';
            foreach ($milestones as $milestone) {
                $date = get_post_meta($milestone->ID, '_upm_milestone_date', true);
                echo '<li><strong>' . esc_html($milestone->post_title) . '</strong> – ' . esc_html($date) . '</li>';
            }
            echo '</ul>';
        }
    }
}

UPM_Module_Projects::init();