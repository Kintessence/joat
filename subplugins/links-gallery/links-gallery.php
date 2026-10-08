<?php
/**
 * Plugin Name: Links Gallery
 * Description: Sistema de curadoria de links com formulário ACF/Advanced Forms e exibição em cards.
 * Version: 0.1.0
 * Author: Bira
 *

if ( ! defined( 'ABSPATH' ) ) exit;

// Includes
require_once __DIR__ . '/includes/cpt.php';
require_once __DIR__ . '/includes/acf-fields.php';
require_once __DIR__ . '/includes/form-handler.php';
require_once __DIR__ . '/includes/frontend.php';

// Assets
function lg_enqueue_assets() {
    $plugin_url = plugin_dir_url( __FILE__ );
    wp_enqueue_style('links-gallery-style', $plugin_url . 'assets/css/style.css', [], null);
    wp_enqueue_script('links-gallery-search', $plugin_url . 'assets/js/search.js', ['jquery'], null, true);
}
add_action('wp_enqueue_scripts', 'lg_enqueue_assets');

function lg_render_shortcode() {
    lg_enqueue_assets(); // Garante o carregamento
    ob_start();
    include plugin_dir_path( __FILE__ ) . 'includes/frontend.php';
    return ob_get_clean();
}
add_shortcode('link_cards', 'lg_render_shortcode');

//echo plugin_dir_url(__FILE__); */