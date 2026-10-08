<?php
if (!defined('ABSPATH')) exit;

final class Card_Generator_Plugin {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }

    private function define_constants() {
        define('CG_VERSION', '9.1.0');
        define('CG_PLUGIN_DIR', plugin_dir_path(__FILE__));
        define('CG_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('CG_PAGE_SLUG', 'gerador-cards');
    }

    private function includes() {
        require_once CG_PLUGIN_DIR . 'includes/class-gc-cpt.php';
        require_once CG_PLUGIN_DIR . 'includes/class-gc-router.php';
        require_once CG_PLUGIN_DIR . 'includes/class-gc-ajax.php';		
        require_once CG_PLUGIN_DIR . 'includes/class-gc-templates.php';
    }

    private function init_hooks() {
        add_action('plugins_loaded', function() {
            new CG_CPT();
            new CG_Router();
            new CG_AJAX();
        });

        // Garante a existência da página hospedeira sem depender de ativadores externos
        add_action('init', [ $this, 'garantir_pagina_hospedeira' ], 20);
    }

    public function garantir_pagina_hospedeira() {
        if (get_option('cg_page_created_v91') === CG_VERSION) return;

        $pagina = get_page_by_path(CG_PAGE_SLUG);
        if (!$pagina) {
            wp_insert_post([
                'post_title'     => 'Gerador de Cards',
                'post_name'      => CG_PAGE_SLUG,
                'post_status'    => 'publish',
                'post_type'      => 'page',
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
                'post_content'   => '<!-- Página gerada automaticamente pelo Gerador de Cards -->'
            ]);
        }
        update_option('cg_page_created_v91', CG_VERSION);
    }
}

function card_generator_init() {
    return Card_Generator_Plugin::get_instance();
}
card_generator_init();