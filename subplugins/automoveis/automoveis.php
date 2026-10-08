<?php

// Adiciona campos de opções para os automóveis disponíveis
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'page_title' => 'Veículos Secom',
        'menu_title' => 'Veículos',
        'menu_slug' => 'veiculos',
		'parent_slug' => 'config-secom',
        'capability' => 'manage_options',
        //'position' => 30,
        'icon_url' => 'dashicons-car', // Ícone para o menu
        'redirect' => false, // Redirecionamento após salvar
        'post_id' => 'options', // ID do post que armazenará as opções
        'autoload' => true, // Carregar automaticamente as opções no front-end
        'update_button' => 'Atualizar', // Texto do botão "Atualizar"
        'updated_message' => "Veículos atualizados", // Mensagem exibida após atualizar as opções
    ));

    acf_add_local_field_group(array(
        'key' => 'group_automoveis_disponiveis',
        'title' => 'Automóveis Disponíveis',
        'fields' => array(
            array(
                'key' => 'field_automoveis',
                'label' => 'Automóveis',
                'name' => 'automoveis',
                'type' => 'repeater',
                'layout' => 'table',
                'button_label' => 'Adicionar Automóvel',
                'sub_fields' => array(
                    array(
                        'key' => 'field_marca',
                        'label' => 'Marca',
                        'name' => 'marca',
                        'type' => 'select',
                        'required' => true,
                        'choices' => array(
                            'Audi' => 'Audi',
                            'BMW' => 'BMW',
                            'Chevrolet' => 'Chevrolet',
                            'Fiat' => 'Fiat',
                            'Ford' => 'Ford',
                            'Honda' => 'Honda',
                            'Hyundai' => 'Hyundai',
                            'Jeep' => 'Jeep',
                            'Mercedes-Benz' => 'Mercedes-Benz',
                            'Nissan' => 'Nissan',
                            'Renault' => 'Renault',
                            'Toyota' => 'Toyota',
                            'Volkswagen' => 'Volkswagen',
                        ),
                    ),
                    array(
                        'key' => 'field_modelo',
                        'label' => 'Modelo',
                        'name' => 'modelo',
                        'type' => 'text',
                        'required' => true,
                    ),
                    array(
                        'key' => 'field_placa_veiculo',
                        'label' => 'Placa do Veículo',
                        'name' => 'placa_veiculo',
                        'type' => 'text',
                        'required' => true,
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
					'value' => 'veiculos',
                ),
            ),
        ),
    ));
}

// Verifica se o ACF está ativo
if( function_exists('acf_add_local_field_group') ):

// Adiciona um grupo de campos para o registro de automóveis
acf_add_local_field_group(array(
    'key' => 'group_registro_automoveis',
    'title' => 'Registro de Automóveis',
    'fields' => array(
		array(
            'key' => 'field_data',
            'label' => 'Data',
            'name' => 'data',
            'type' => 'date_picker',
            'required' => true,
            'display_format' => 'd/m/Y', // Formato de exibição da data
            'return_format' => 'Ymd', // Formato de retorno da data,
			'wrapper' => array(
				'width' => '33',
			),
        ),
		array(
			'key' => 'field_automovel',
			'label' => 'Carro',
			'name' => 'carro',
			'type' => 'select',
			'required' => true,
			'choices' => array(
			),
			'ui' => 0,
			'ajax' => 0,
			'wrapper' => array(
				'width' => '33',
			),
		),
        array(
            'key' => 'field_matricula_usuario',
            'label' => 'Matrícula do Usuário',
            'name' => 'matricula_usuario',
            'type' => 'text',
            'required' => true,
			'wrapper' => array(
				'width' => '33',
			),
        ),
        array(
            'key' => 'field_nome_usuario',
            'label' => 'Nome do Usuário',
            'name' => 'nome_usuario',
            'type' => 'text',
            'required' => true,
        ),
        array(
            'key' => 'field_horario_inicial',
            'label' => 'Horário Inicial',
            'name' => 'horario_inicial',
            'type' => 'time_picker',
            'required' => true,
            'display_format' => 'H:i', // Formato de exibição do horário
            'return_format' => 'H:i',
			'wrapper' => array(
				'width' => '50',
			),
        ),
        array(
            'key' => 'field_km_inicial',
            'label' => 'Km Inicial',
            'name' => 'km_inicial',
            'type' => 'number',
            'required' => true,
			'wrapper' => array(
				'width' => '50',
			),
        ),
        array(
            'key' => 'field_horario_final',
            'label' => 'Horário Final',
            'name' => 'horario_final',
            'type' => 'time_picker',
            'required' => true,
            'display_format' => 'H:i', // Formato de exibição do horário
            'return_format' => 'H:i',
			'wrapper' => array(
				'width' => '50',
			),
        ),
        array(
            'key' => 'field_km_final',
            'label' => 'Km Final',
            'name' => 'km_final',
            'type' => 'number',
            'required' => true,
			'wrapper' => array(
				'width' => '50',
			),
        ),
        array(
            'key' => 'field_local_saida',
            'label' => 'Local de Saída',
            'name' => 'local_saida',
            'type' => 'text',
            'required' => true,
			'wrapper' => array(
				'width' => '50',
			),
        ),
        array(
            'key' => 'field_destino',
            'label' => 'Local de Destino',
            'name' => 'local_destino',
            'type' => 'text',
            'required' => true,
			'wrapper' => array(
				'width' => '50',
			),
        ),
        array(
            'key' => 'field_assinatura',
            'label' => 'Assinatura',
            'name' => 'assinatura',
            'type' => 'image',
            'required' => true,
            'return_format' => 'url', // Salva apenas a URL da imagem
            'preview_size' => 'thumbnail', // Tamanho da miniatura para exibição no painel de administração
            'library' => 'all', // Permite selecionar imagens da biblioteca de mídia
        ),
    ),
    'location' => array(
        array(
            array(
                'param' => 'post_type',
                'operator' => '==',
                'value' => 'diario-bordo', // Substitua 'registro_automovel' pelo nome do seu CPT de registros de automóveis
            ),
        ),
		array(
            array(
                'param' => 'af_form',
                'operator' => '==',
                'value' => 'form_veiculos', // Substitua 'registro_automovel' pelo nome do seu CPT de registros de automóveis
            ),
        ),
    ),
));

endif;

function register_form_veiculos() {
	$form = array(
		'title' => 'Form title',
		'key' => 'form_veiculos',
		'display' => array(
			'description' => '',
			'success_message' => '',
		),
		'create_entries' => false,
		'restrict_entries' => false,
		'entries_limit' => 0,
		'entries_restriction_message' => '',
	);
	af_register_form( $form );
}
add_action( 'af/register_forms', 'register_form_veiculos' );

// Função para carregar as opções do campo "automovel"
function load_modelos_choices($field)
{
    // Reset escolhas
    $field['choices'] = array();

    // Obtém os automóveis da página de opções
    $automoveis = get_field('field_automoveis', 'option');

    // Verifica se há automóveis disponíveis
    if ($automoveis) {
        // Loop através dos automóveis e adiciona-os como opções
        foreach ($automoveis as $automovel) {
            // Verifica se a marca está definida e não está vazia
            if (isset($automovel['modelo']) && !empty($automovel['modelo'])) {
                // Adiciona a marca como opção
                $field['choices'][$automovel['modelo']] = $automovel['modelo'];
            }
        }
    }

    // Retorna o campo com as opções atualizadas
    return $field;
}

// Adiciona o filtro para carregar as opções do campo "automovel"
add_filter('acf/load_field/key=field_automovel', 'load_modelos_choices');

function prefill_field_matricula_usuario( $value, $field, $form, $args ) {
	$value = get_field('campo_matricula','user_'.get_current_user_id());
    return $value;
}
add_filter( 'af/field/prefill_value/key=field_matricula_usuario', 'prefill_field_matricula_usuario', 10, 4 );
function prefill_field_nome_usuario( $value, $field, $form, $args ) {
	$user_id = get_current_user_id();
	$n = esc_html( get_user_meta( $user_id, 'first_name', true ) );
	$sn = esc_html( get_user_meta( $user_id, 'last_name', true ) );
	$value = $n.' '.$sn;
    return $value;
}
add_filter( 'af/field/prefill_value/key=field_nome_usuario', 'prefill_field_nome_usuario', 10, 4 );