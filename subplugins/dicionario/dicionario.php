<?php

// Função para registrar os campos ACF
function registra_campos_acf_dicionario() {
    if( function_exists('acf_add_local_field_group') ):

        acf_add_local_field_group(array(
            'key' => 'grupo_entrada_dicionario',
            'title' => 'Entrada do Dicionário',
            'fields' => array(
                array(
                    'key' => 'campo_palavra',
                    'label' => 'Palavra/Expressão',
                    'name' => 'palavra',
                    'type' => 'text',
                ),
				array(
                    'key' => 'campo_pronuncia',
                    'label' => 'Pronúncia',
                    'name' => 'pronuncia',
                    'type' => 'text',
                ),
                array(
                    'key' => 'campo_definicao',
                    'label' => 'Definição',
                    'name' => 'definicao',
                    'type' => 'textarea',
                ),
                array(
                    'key' => 'campo_exemplo',
                    'label' => 'Exemplo de Uso',
                    'name' => 'exemplo',
                    'type' => 'textarea',
                ),
                array(
                    'key' => 'campo_categoria',
                    'label' => 'Categoria/Tema',
                    'name' => 'categoria',
                    'type' => 'taxonomy',
                    'taxonomy' => 'categoria', // Nome da taxonomia registrada
                    'field_type' => 'select',
                    'allow_null' => 0,
                    'multiple' => 0,
                    'add_term' => 1, // Permitir adicionar novas categorias
                ),
                array(
                    'key' => 'campo_funcao_sintatica',
                    'label' => 'Classe gramatical',
                    'name' => 'funcao_sintatica',
                    'type' => 'select',
                    'choices' => array(
                        'substantivo' => 'Substantivo',
                        'adjetivo' => 'Adjetivo',
                        'adverbio' => 'Advérbio',
						'artigo' => 'Artigo',
						'numeral' => 'Numeral',
						'pronome' => 'Pronome',
						'verbo' => 'Verbo',
						'conjuncao' => 'Conjunção',
						'interjeicao' => 'Interjeição',
						'preposicao' => 'Preposição',
                    ),
                ),
                array(
                    'key' => 'campo_genero',
                    'label' => 'Gênero',
                    'name' => 'genero',
                    'type' => 'select',
                    'choices' => array(
                        'masculino' => 'Masculino',
                        'feminino' => 'Feminino',
                    ),
                ),
                array(
                    'key' => 'campo_nivel_graca',
                    'label' => 'Nível de graça',
                    'name' => 'nivel_graca',
                    'type' => 'range',
                    'min' => 1,
                    'max' => 10,
                ),
                array(
                    'key' => 'campo_data_criacao',
                    'label' => 'Data de Criação',
                    'name' => 'data_criacao',
                    'type' => 'date_picker',
                    'default_value' => date('Ymd'), // Define o valor padrão para a data atual
                ),
            ),
            'location' => array(
                array(
					array(
						'param' => 'af_form',
						'operator' => '==',
						'value' => 'form_dicionario',
					),
				),array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'vocabulo',
					),
				),
            ),
			'hide_on_screen' => array(
				0 => 'the_content',
			),
        ));

    endif;
}

// Chama a função de registro de campos ACF
add_action('acf/init', 'registra_campos_acf_dicionario');

function registra_form_dicionario() {
	af_register_form( array(
		'key' => 'form_dicionario',
		'title' => 'Form Dicionário',
		'display' => array(
			'description' => '',
			'success_message' => '',
		),
		'create_entries' => false,
		'restrictions' => array(
			'entries' => false,
			'user' => false,
			'schedule' => false,
		),
		'emails' => array(
			array(),
		),
	) );
}
add_action( 'af/register_forms', 'registra_form_dicionario' );

function dicionario_form_submission( $form, $field, $args ) {
	// Verifica se é uma ação de salvamento automático e se o tipo de postagem é o desejado
	// if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
	// if (get_post_type($post_id) !== 'sua_post_type') return;
	
	// Obtém os dados do ACF
	$palavra = af_get_field('campo_palavra');
	$definicao = af_get_field('campo_definicao');
	$exemplo = af_get_field('campo_exemplo');
	$categoria = af_get_field('campo_categoria');
	$funcao_sintatica = af_get_field('campo_funcao_sintatica');
	$genero = af_get_field('campo_genero');
	$nivel_graca = af_get_field('campo_nivel_graca');
	$data_criacao = af_get_field('campo_data_criacao');
	
	// Cria um novo "vocabulo"
	$vocabulo_args = array(
		'post_type' => 'vocabulo',
		'post_title' => $palavra,
		'post_content' => $definicao,
		'post_status' => 'publish',
	);
	
	$vocabulo_id = wp_insert_post($vocabulo_args);
	
	// Atualiza o novo "vocabulo" com os dados adicionais
	update_post_meta($vocabulo_id, 'definicao', $definicao);
	update_post_meta($vocabulo_id, 'exemplo', $exemplo);
	update_post_meta($vocabulo_id, 'categoria', $categoria);
	update_post_meta($vocabulo_id, 'funcao_sintatica', $funcao_sintatica);
	update_post_meta($vocabulo_id, 'genero', $genero);
	update_post_meta($vocabulo_id, 'nivel_engracado', $nivel_graca);
	update_post_meta($vocabulo_id, 'data_criacao', $data_criacao);
}
add_action( 'af/form/submission/key=form_dicionario', 'dicionario_form_submission', 10, 3 );

add_shortcode( 'dicionario', 'dicionario_shortcode' );
function dicionario_shortcode($atts) {
    // Defina o tipo de post desejado
    $post_type = isset($atts['post_type']) ? $atts['post_type'] : 'vocabulo';

    // Consulte todos os posts do tipo especificado
    $args = array(
        'post_type' => $post_type, // Para obter todos os posts do tipo especificado
        'posts_per_page' => -1,
		'orderby' => 'title',  // Ordena por título
		'order' => 'ASC',     // Ordem crescente
		
    );

    $output = ''; // Inicializa a saída dos campos ACF

    $vocabulos = new WP_Query($args);

    if ($vocabulos->have_posts()) {
        while ($vocabulos->have_posts()) {
            $vocabulos->the_post();
            $titulo_pagina = get_the_title(); // Obtém o título da página
			
			// Obtém o valor do campo 'campo_definicao'
            $d = get_field('campo_definicao');
			$p = get_field('campo_pronuncia');
			$fs = get_field('funcao_sintatica');
			$g = get_field('campo_genero');
			$def = get_field('campo_definicao');
			$ex = get_field('campo_exemplo');
			
			$output .= '<div>';
			$output .= '<h4>' . $titulo_pagina . '</h4>';
			$output .= '<p>' . $p .'</p>';
			$output .= '<p>' . $fs . ' ' . $g . '</p>';
			$output .= '<div>' . $def . '</div>';
			$output .= '<div class="exemplo mt-10">' . $ex . '</div>';
			
			if (current_user_can('manage_options')) {
				// Botões de Edição e de Exclusão
				$output .= '<hr class="mt-20 mb-20">';
				$output .=  '<div class="linha-botao"><a href="' . get_edit_post_link($post->ID) . '">Editar</a> | <a href="' . get_delete_post_link($post->ID) . '">Excluir</a></div>';
			}
			$output .= '</div>';						

            // Obtém todos os campos ACF associados ao post
            /*$fields = get_fields();

            if ($fields) {
                $custom_fields_output .= '<h2>' . $titulo_pagina . '</h2>'; // Adiciona o título da página

                // Itera pelos campos ACF e os adiciona à saída se tiverem conteúdo
                foreach ($fields as $field_name => $field_value) {
                    if (!empty($field_value)) {
                        $custom_fields_output .= '<p>' . $field_name . ': ' . $field_value . '</p>';
                    }
                }

                $custom_fields_output .= '<hr>';
            }*/
        }

        wp_reset_postdata(); // Restaura os dados originais do post global do WordPress
    }

    return $output;
}