<?php
if (!defined('ABSPATH')) exit;

// Registra Grupo de Campos de Férias e Trava Antecipada no Usuário
add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) return;

    // Campo de liberação antecipada da folha no perfil do usuário
    acf_add_local_field_group([
        'key' => 'group_folha_ponto_usuario',
        'title' => 'Configurações da Folha de Ponto',
        'location' => [[['param' => 'user_form', 'operator' => '==', 'value' => 'edit']]],
        'fields' => [
            [
                'key' => 'field_liberar_folha_antecipada',
                'label' => 'Liberar Folha Antecipada',
                'name' => 'liberar_folha_antecipada',
                'type' => 'true_false',
                'instructions' => 'Permite que este usuário visualize a folha de ponto do mês atual antes do fechamento.',
                'ui' => 1,
            ],
        ],
    ]);
	
	acf_add_local_field_group([
        'key' => 'group_folha_ponto_permissoes',
        'title' => 'Permissões da Folha de Ponto',
        'location' => [
            [
                [
                    'param'    => 'user_form',
                    'operator' => '==',
                    'value'    => 'edit', // Aparece na edição de usuários
                ],
            ],
        ],
        'fields' => [
            [
                'key'          => 'field_is_supervisor_folha',
                'label'        => 'Supervisor de Folhas de Ponto',
                'name'         => 'is_supervisor_folha',
                'type'         => 'true_false',
                'instructions' => 'Marque para conceder a este servidor acesso à supervisão e homologação de entregas de folhas.',
                'ui'           => 1,
                'default_value'=> 0,
            ],
        ],
    ]);
	
});

// Proteção do campo (Oculta para quem não for administrador)
add_filter('acf/prepare_field/name=is_supervisor_folha', 'ocultaCampo');
add_filter('acf/prepare_field/name=liberar_folha_antecipada', 'ocultaCampo');

// Proteção do campo (Oculta para quem não for administrador)
function ocultaCampo ($field) {
    if (!current_user_can('manage_options')) {
        return false; // Não renderiza o campo para usuários comuns
    }
    return $field;
};