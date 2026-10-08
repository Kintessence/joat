<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('acf/init', function() {
    if( ! function_exists('acf_add_local_field_group') ) return;

    acf_add_local_field_group([
        'key' => 'group_link_entry_fields',
        'title' => 'Campos de Link',
        'fields' => [
            [
                'key' => 'field_link_title',
                'label' => 'Título',
                'name' => 'link_title',
                'type' => 'text',
                'required' => 1,
            ],
            [
                'key' => 'field_link_url',
                'label' => 'URL',
                'name' => 'link_url',
                'type' => 'url',
                'required' => 1,
            ],
            [
                'key' => 'field_link_description',
                'label' => 'Descrição',
                'name' => 'link_description',
                'type' => 'textarea',
                'rows' => 3,
            ],
			[
                'key' => 'field_link_fotografo',
							'label' => 'Fotógrafo',
							'name' => 'link_fotografo',
							'type' => 'select',
							'choices' => get_choices('acf','fotografos_lista'),
							'wrapper' => array(
								//'width' => '33.3333%', // Defina a largura desejada, por exemplo, 80%
							),
			],	
            [
                'key' => 'field_link_tags',
                'label' => 'Tags',
                'name' => 'link_tags',
                'type' => 'taxonomy',
                'taxonomy' => 'post_tag',
                'field_type' => 'multi_select',
                'add_term' => 1,
                'save_terms' => 1,
                'load_terms' => 1,
            ],
        ],
        'location' => [
			[['param' => 'af_form','operator' => '==','value' => 'form_enviar_link']],
		],
    ]);
	acf_add_local_field_group([
        'key' => 'group_link_entry_url_field',
        'title' => 'Campo link',
        'fields' => [
            [
                'key' => 'field_link_url',
                'label' => 'URL',
                'name' => 'link_url',
                'type' => 'url',
                'required' => 1,
            ],
        ],
        'location' => [
			[['param' => 'post_type','operator' => '==','value' => 'link_entry']]
		],
    ]);
});
add_action( 'af/register_forms', function() {
	if ( function_exists('af_register_form') ) {
		//function register_links_form(){
			af_register_form([
				'key' => 'form_enviar_link',
				'title' => 'Enviar Link',
			]);
		//}
	}
});
