<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('af/form/submission', function( $form, $fields, $args ) {
	if ( $form['key'] !== 'form_enviar_link' ) return;
	
	//$url = esc_url_raw( $fields['field_link_url']['value'] );
	$title = af_get_field('link_title');
    //$description = sanitize_textarea_field( $fields['field_link_description']['value'] );
    //$tags = $fields['field_link_tags']['value'] ?? [];
    $url = af_get_field('link_url');
    $description = af_get_field('link_description');
    $tags = af_get_field('link_tag');
	$fotografo = af_get_field('link_fotografo');

    // Evita duplicatas
    $exists = new WP_Query([
        'post_type' => 'link_entry',
        'meta_query' => [[
            'key' => 'link_url',
            'value' => $url,
            'compare' => '=',
        ]],
        'fields' => 'ids'
    ]);
    // Se for duplicado e não for o mesmo post do formulário, cancela
    if ( $exists->found_posts && ( ! isset($args['post_id']) || ! in_array($args['post_id'], $exists->posts) ) ) {
        wp_die('Este link já foi cadastrado.');
    }

    // Verifica se a URL é válida
    /*$response = wp_remote_head( $url );
    if ( is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200 ) {
        wp_die('URL inválida ou inacessível.');
    }*/

    // Cria post
    $link_post_id = wp_insert_post([
        'post_title' => $title,
        'post_content' => $description . " por " . $fotografo,
        'post_type' => 'link_entry',
        'post_status' => 'publish',
    ]);

    if ( is_wp_error( $link_post_id ) || ! $link_post_id ) {
        wp_die('Erro ao criar o post do link.');
    }
	
/*	// Helper: atualiza ACF tentando field key -> field name -> postmeta direto
    $update_acf = function( $field_name_or_key, $value ) use ( $post_id ) {
        // Se ACF não estiver ativo, cai para update_post_meta
        if ( ! function_exists('update_field') ) {
            update_post_meta($post_id, $field_name_or_key, $value);
            return false;
        }

        // Se passou uma field_key (começa com 'field_'), usa direto
        if ( is_string($field_name_or_key) && strpos($field_name_or_key, 'field_') === 0 ) {
            return update_field($field_name_or_key, $value, $post_id);
        }

        // Tenta obter o objeto do campo para ter a key correta
        $fobj = get_field_object($field_name_or_key, $post_id);
        if ( $fobj && ! empty($fobj['key']) ) {
            return update_field($fobj['key'], $value, $post_id);
        }

        // Fallback: salva como postmeta com o nome de campo
        return update_post_meta($post_id, $field_name_or_key, $value);
    };
	
	// Atualiza campos ACF com fallback seguro
    $update_acf('link_title', $title);
    $update_acf('link_description', $description);
    $update_acf('link_url', $url);
    $update_acf('link_tags', $tags_raw);*/
	
	// Atualiza o novo "vocabulo" com os dados adicionais
	//update_post_meta($link_post_id, 'link_title', $title);
	//update_post_meta($link_post_id, 'link_description', $description);
	update_post_meta($link_post_id, 'link_url', $url);
	/*update_post_meta($link_post_id, 'link_tags', $tags_raw);
	update_post_meta($link_post_id, 'genero', $genero);
	update_post_meta($link_post_id, 'nivel_engracado', $nivel_graca);
	update_post_meta($link_post_id, 'data_criacao', $data_criacao);*/
	
	// Cria e associa tags reais
	if ( $tags_array ) {
		$tag_ids = [];
		foreach ( $tags_array as $tag_name ) {
			$tag_obj = term_exists( $tag_name, 'post_tag' );
			if ( ! $tag_obj ) {
				$tag_obj = wp_insert_term( $tag_name, 'post_tag' );
			}
			if ( ! is_wp_error($tag_obj) ) {
				$tag_ids[] = (int) $tag_obj['term_id'];
			}
		}
		wp_set_post_terms( $post_id, $tag_ids, 'post_tag' );
	}
}, 10, 3);