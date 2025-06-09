<?php
class UPM_Module_Files {
    public static function init() {
        add_action('init', [__CLASS__, 'register_post_type']);
        add_action('add_meta_boxes', [__CLASS__, 'add_file_meta_boxes']);
        add_action('save_post_upm_file', [__CLASS__, 'save_file_meta']);
    }

    public static function register_post_type() {
        $labels = [
            'name'               => 'Archivos',
            'singular_name'      => 'Archivo',
            'menu_name'          => 'Archivos',
            'name_admin_bar'     => 'Archivo',
            'add_new'            => 'Agregar nuevo',
            'add_new_item'       => 'Agregar nuevo archivo',
            'edit_item'          => 'Editar archivo',
            'view_item'          => 'Ver archivo',
            'all_items'          => 'Archivos',
            'search_items'       => 'Buscar archivos',
            'not_found'          => 'No se encontraron archivos',
        ];

        $args = [
            'labels'       => $labels,
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => 'upm_dashboard',
            'supports'     => ['title', 'custom-fields'],
        ];

        register_post_type('upm_file', $args);
    }

    public static function add_file_meta_boxes() {
        add_meta_box(
            'upm_file_details',
            'Detalles del archivo',
            [__CLASS__, 'render_file_meta_box'],
            'upm_file',
            'normal',
            'default'
        );
    }

    public static function render_file_meta_box($post) {
        $project_id     = get_post_meta($post->ID, '_upm_file_project_id', true);
        $file_url       = get_post_meta($post->ID, '_upm_file_url', true);
        $category       = get_post_meta($post->ID, '_upm_file_category', true);
        $auto_generated = get_post_meta($post->ID, '_upm_auto_generated', true);
        $file_type      = get_post_meta($post->ID, '_upm_file_type', true);
        $file_size      = get_post_meta($post->ID, '_upm_file_size', true);

        ?>
        <p><label><strong>Proyecto asociado (ID):</strong></label><br>
            <input type="number" name="upm_file_project_id" value="<?= esc_attr($project_id) ?>" />
        </p>

        <p><label><strong>Archivo (URL):</strong></label><br>
            <input type="text" name="upm_file_url" value="<?= esc_attr($file_url) ?>" style="width:100%;" placeholder="https://..." />
        </p>

        <p><label><strong>Categoría:</strong></label><br>
            <select name="upm_file_category">
                <option value="Legal" <?= selected($category, 'Legal') ?>>Legal</option>
                <option value="Facturación" <?= selected($category, 'Facturación') ?>>Facturación</option>
                <option value="Diseño" <?= selected($category, 'Diseño') ?>>Diseño</option>
                <option value="Documentación" <?= selected($category, 'Documentación') ?>>Documentación</option>
            </select>
        </p>

        <p><label><strong>Generado por el sistema:</strong></label><br>
            <select name="upm_auto_generated">
                <option value="1" <?= selected($auto_generated, 1) ?>>Sí</option>
                <option value="0" <?= selected($auto_generated, 0) ?>>No</option>
            </select>
        </p>

        <p><label><strong>Tipo de archivo:</strong></label><br>
            <input type="text" name="upm_file_type" value="<?= esc_attr($file_type) ?>" placeholder="PDF, DOCX, etc." />
        </p>

        <p><label><strong>Tamaño del archivo:</strong></label><br>
            <input type="text" name="upm_file_size" value="<?= esc_attr($file_size) ?>" placeholder="1.2 MB, 500 KB..." />
        </p>
        <?php
    }

    public static function save_file_meta($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $fields = [
            'upm_file_project_id' => '_upm_file_project_id',
            'upm_file_url'        => '_upm_file_url',
            'upm_file_category'   => '_upm_file_category',
            'upm_auto_generated'  => '_upm_auto_generated',
            'upm_file_type'       => '_upm_file_type',
            'upm_file_size'       => '_upm_file_size',
        ];

        foreach ($fields as $form_field => $meta_key) {
            if (isset($_POST[$form_field])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$form_field]));
            }
        }
    }
}
UPM_Module_Files::init();
