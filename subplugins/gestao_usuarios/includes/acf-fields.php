<?php
if (!defined('ABSPATH')) exit;

add_action('acf/init', 'campos_usuario_acf');
function campos_usuario_acf() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array( 
            'key' => 'grupo_campos_usuario',
            'title' => 'Informações do Usuário',
            'fields' => array(
                array(
                    'key' => 'campo_cpf',
                    'label' => 'CPF',
                    'name' => 'cpf',
                    'type' => 'text',
                ),
                array(
                    'key' => 'campo_data_nascimento',
                    'label' => 'Data de Nascimento',
                    'name' => 'data_nascimento',
                    'type' => 'date_picker',
                    'display_format' => 'd/m/Y',
                    'return_format' => 'Y-m-d',
                ),
                array(
                    'key' => 'campo_seletor_cargo',
                    'label' => 'Cargo',
                    'name' => 'cargo_select',
                    'type' => 'select',
                    'choices' => [
                        'sec' => 'Secretário/a',
                        'ss' => 'Subsecretário/a',
                        'asa' => 'Assessor Adjunto',
                        'cc1_g' => 'Gerente',
                        'cc1' => 'Assessor Técnico I',
                        'cc2_c' => 'Coordenador',
                        'cc2' => 'Assessor Técnico II',
                        'cc3' => 'Assistente Técnico I',
                        'apa' => 'Assistente Público Administrativo',
                        'anpa' => 'Analista Público em Administração',
                        'ens' => 'Estagiário Nível Superior',
                    ],
                    'default_value' => 'cc1',
                ),
                array(
                    'key' => 'campo_cargo_descricao',
                    'label' => 'Descrição do Cargo',
                    'name' => 'cargo',
                    'type' => 'text',
                ),
                array(
                    'key' => 'campo_setor',
                    'label' => 'Setor',
                    'name' => 'setor',
                    'type' => 'select',
                    'choices' => [
                        'assessoria' => 'Assessoria',
                        'marketing' => 'Marketing',
                        'foto-video' => 'Foto e Vídeo',
                        'administrativo' => 'Administrativo',
                    ]
                ),
                array(
                    'key' => 'campo_jornada',
                    'label' => 'Jornada',
                    'name' => 'jornada',
                    'type' => 'select',
                    'choices' => [
                        'INTEGRAL' => 'Integral (8h às 18h)',
                        'INTEGRAL_1H' => 'Integral (8h às 17h)',
                        'MANHA' => 'Manhã (8h às 13h)',
                        'TARDE' => 'Tarde (13h às 18h)',
                    ],
                    'default_value' => 'INTEGRAL',
                ),
                array(
                    'key' => 'campo_matricula',
                    'label' => 'Matrícula',
                    'name' => 'matricula',
                    'type' => 'text',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'user_form',
                        'operator' => '==',
                        'value' => 'edit',
                    ),
                ),
            ),
        ));
		
		acf_add_local_field_group([
			'key' => 'group_ferias_usuario',
			'title' => 'Gestão de Férias e Folha de Ponto',
			'location' => [[['param' => 'user_form', 'operator' => '==', 'value' => 'edit']]],
			'fields' => [
				/*[
					'key' => 'field_liberar_folha_antecipada',
					'label' => 'Liberar Folha Antecipada',
					'name' => 'liberar_folha_antecipada',
					'type' => 'true_false',
					'instructions' => 'Permite que este usuário visualize a folha de ponto do mês atual antes do fechamento.',
					'ui' => 1,
				],*/
				[
					'key' => 'field_ferias_periodos',
					'label' => 'Períodos de férias e afastamentos',
					'name' => 'ferias_periodos',
					'type' => 'repeater',
					'layout' => 'table',
					'button_label' => 'Adicionar período',
					'sub_fields' => [
						[
							'key' => 'field_tipo',
							'label' => 'Tipo',
							'name' => 'tipo',
							'type' => 'select',
							'choices' => [
								'FERIAS' => 'Férias',
								'LICENCA_MATERNIDADE' => 'Licença-maternidade',
								'LICENCA_PATERNIDADE' => 'Licença-paternidade',
								'LICENCA_MEDICA' => 'Licença médica',
								'LICENCA_SEM_VENCIMENTO' => 'Licença sem vencimento',
								'ATESTADO' => 'Atestado',
								'OUTRO' => 'Outro',
							],
							'return_format' => 'value',
						],
						[
							'key' => 'field_ferias_inicio',
							'label' => 'Início',
							'name' => 'inicio',
							'type' => 'date_picker',
							'display_format' => 'd/m/Y',
							'return_format' => 'Y-m-d',
							'required' => 1,
						],
						[
							'key' => 'field_ferias_fim',
							'label' => 'Fim',
							'name' => 'fim',
							'type' => 'date_picker',
							'display_format' => 'd/m/Y',
							'return_format' => 'Y-m-d',
							'required' => 1,
						],
						[
							'key' => 'field_descricao',
							'label' => 'Descrição',
							'name' => 'descricao',
							'type' => 'text',
						],
					],
				],
			],
		]);
	}
}

// Validação de Data Fim < Data Início no ACF
add_filter('acf/validate_value/name=fim', function ($valid, $value, $field, $input) {
    if (!$valid) return $valid;
    $inicio = $_POST['acf'][str_replace('fim', 'inicio', $field['key'])] ?? null;

    if ($inicio && strtotime($value) < strtotime($inicio)) {
        return 'A data final não pode ser anterior à data inicial.';
    }
    return true;
}, 10, 4);