<?php
if (!defined('ABSPATH')) exit;

// 1. Registra a regra de reescrita /cadastro
add_action('init', 'custom_registrar_rota_cadastro');
function custom_registrar_rota_cadastro() {
    add_rewrite_rule('^cadastro/?$', 'index.php?custom_page=cadastro', 'top');
}

// 2. Registra a query var
add_filter('query_vars', 'custom_adicionar_query_vars');
function custom_adicionar_query_vars($vars) {
    $vars[] = 'custom_page';
    return $vars;
}

// 3. Intercepta a requisição e exibe o formulário completo do site
add_action('template_redirect', 'custom_processar_pagina_cadastro');
function custom_processar_pagina_cadastro() {
    if (get_query_var('custom_page') === 'cadastro') {
        if (is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }

        // Processa o envio do formulário via POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['custom_register_nonce'])) {
            custom_processar_registro_completo();
        }

        // Exibe o layout da página
        get_header();
        echo '<div style="max-width: 600px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">';
        echo '<h2>Cadastro de Colaborador</h2>';
        custom_renderizar_formulario_completo();
        echo '</div>';
        get_footer();
        exit;
    }
}