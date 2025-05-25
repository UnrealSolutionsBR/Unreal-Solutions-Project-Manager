<?php
class UPM_Module_Customers {
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register_customers_page']);
    }

    public static function register_customers_page() {
        add_submenu_page(
            'upm_dashboard',
            'Clientes',
            'Clientes',
            'manage_options',
            'upm_customers',
            [__CLASS__, 'render_customers_page']
        );
    }

    public static function render_customers_page() {
        $customers = get_users(['role' => 'customer']);

        echo '<div class="wrap">';
        echo '<h1>Clientes registrados</h1>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>
            <th>Nombre</th>
            <th>Correo</th>
            <th>Proyectos</th>
            <th>Facturas (Pend/Pag)</th>
            <th>Tickets abiertos</th>
        </tr></thead><tbody>';

        foreach ($customers as $user) {
            $user_id = $user->ID;

            // Contar proyectos asignados
            $projects = new WP_Query([
                'post_type'  => 'upm_project',
                'meta_key'   => '_upm_client_id',
                'meta_value' => $user_id,
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);
            $project_count = count($projects->posts);

            // Facturas pendientes y pagadas
            $invoices = get_posts([
                'post_type'  => 'upm_invoice',
                'meta_key'   => '_upm_invoice_client_id',
                'meta_value' => $user_id,
                'posts_per_page' => -1,
                'fields' => 'all'
            ]);

            $pendientes = 0;
            $pagadas = 0;
            foreach ($invoices as $invoice) {
                $status = get_post_meta($invoice->ID, '_upm_invoice_status', true);
                if ($status === 'pagada') {
                    $pagadas++;
                } else {
                    $pendientes++;
                }
            }

            // Tickets abiertos
            $tickets = new WP_Query([
                'post_type'  => 'upm_ticket',
                'meta_query' => [
                    [
                        'key' => '_upm_ticket_client_id',
                        'value' => $user_id,
                    ],
                    [
                        'key' => '_upm_ticket_status',
                        'value' => 'abierto',
                    ]
                ],
                'posts_per_page' => -1,
                'fields' => 'ids'
            ]);
            $ticket_count = count($tickets->posts);

            echo "<tr>
                <td>{$user->display_name}</td>
                <td>{$user->user_email}</td>
                <td>{$project_count}</td>
                <td>{$pendientes} / {$pagadas}</td>
                <td>{$ticket_count}</td>
            </tr>";
        }

        echo '</tbody></table></div>';
    }
}
UPM_Module_Customers::init();
