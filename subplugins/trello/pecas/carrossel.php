<?php
return [
	'key' => 'field_carrossel_repeater',
	'label' => 'Carrossel',
	'name' => 'carrossel_repeater',
	'type' => 'repeater',
	'layout' => 'row',
	'button_label' => 'adicionar item',
	'conditional_logic' => [[['field' => 'field_pecas', 'operator' => '==', 'value' => 'carrossel',],],],
	'sub_fields' => [
		[
			'key' => 'field_titulo_carrossel',
			'label' => 'Título',
			'name' => 'titulo_carrossel',
			'type' => 'text',
			'wrapper' => [],
		],
		[
			'key' => 'field_conteudo_carrossel',
			'label' => 'Conteúdo',
			'name' => 'conteudo_carrossel',
			'type' => 'wysiwyg',
			'wrapper' => [],
		],
	]
	
];