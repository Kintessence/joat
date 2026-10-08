<?php
/**
 * Plugin Name: PMVV - Folha de Ponto Digital
 * Description: Gerador e simulador de folha de ponto para servidores com suporte a afastamentos e jornadas especiais.
 * Version:     2.0.0
 * Author:      Estúdio Dr.Quem!
 * Text Domain: pmvv-folha-ponto
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PMVV_FOLHA_PATH', plugin_dir_path(__FILE__));
define('PMVV_FOLHA_URL', plugin_dir_url(__FILE__));

// Carrega os Módulos
require_once PMVV_FOLHA_PATH . 'includes/Helpers.php';
require_once PMVV_FOLHA_PATH . 'includes/acf/fields.php';
require_once PMVV_FOLHA_PATH . 'includes/Core/User.php';
require_once PMVV_FOLHA_PATH . 'includes/Core/Calendar.php';
require_once PMVV_FOLHA_PATH . 'includes/Core/Engine.php';
require_once PMVV_FOLHA_PATH . 'includes/Frontend/Shortcode.php';
require_once PMVV_FOLHA_PATH . 'includes/Admin/Entregas.php';

// Inicializa o controle de entregas
add_action('init', function () {
    \PMVV_Folha\Admin\Entregas::init();
});

// Registra Estilos
add_action('wp_enqueue_scripts', function () {
    wp_register_style('pmvv-folha-print', PMVV_FOLHA_URL . 'assets/css/print.css', [], '2.0.0');
});

// Inicializa Shortcode
add_action('init', function () {
    add_shortcode('folha_ponto', ['PMVV_Folha\Frontend\Shortcode', 'render']);
});

add_filter('painel_colaborador_botoes', 'pmvv_adicionar_folha_ponto_ao_hub', 10, 2);
function pmvv_adicionar_folha_ponto_ao_hub($botoes, $current_user) {
    
    $botoes['folha_ponto'] = array(
        'titulo'    => __('Folha de Ponto', 'pmvv-folha-ponto'),
        'descricao' => __('Consultar espelho e frequências', 'pmvv-folha-ponto'),
        'url'       => admin_url('folha-ponto'), // ou a URL da página pública/shortcode
        'icone'     => 'dashicons-clock',
        'cor'       => '#008a20', // Verde
        'ordem'     => 1,         // Prioridade 1 (aparece primeiro)
    );

    return $botoes;
}