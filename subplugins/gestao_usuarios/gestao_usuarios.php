<?php
/**
 * Plugin Name: Gestão Estendida de Usuários
 * Description: Módulo completo para validação de matrícula, campos ACF, integração com Trello e relatório de cargos.
 * Version: 2.0
 * Author: Seu Nome
 */

if (!defined('ABSPATH')) {
    exit; // Segurança contra acesso direto
}

// Carrega os módulos do plugin
require_once plugin_dir_path(__FILE__) . 'includes/acf-fields.php';
require_once plugin_dir_path(__FILE__) . 'includes/trello-profile.php';
require_once plugin_dir_path(__FILE__) . 'includes/user-registration.php';
require_once plugin_dir_path(__FILE__) . 'includes/user-login.php';
//require_once plugin_dir_path(__FILE__) . 'includes/custom-routes.php';
// Carrega o Hub de Acessos para qualquer usuário logado no admin
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'includes/admin-dashboard.php';
}

// Adicione os hooks de ativação/desativação para atualizar os links amigáveis (Permalinks)
register_activation_hook(__FILE__, 'custom_plugin_ativar');
function custom_plugin_ativar() {
    require_once plugin_dir_path(__FILE__) . 'includes/custom-routes.php';
    custom_registrar_rota_cadastro();
    flush_rewrite_rules(); // Atualiza a tabela de URLs do WordPress ao ativar
}

register_deactivation_hook(__FILE__, 'custom_plugin_desativar');
function custom_plugin_desativar() {
    flush_rewrite_rules(); // Limpa as regras ao desativar
}

// Carrega o relatório apenas no painel administrativo
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'includes/admin-report.php';
}