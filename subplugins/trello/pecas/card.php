<?php
return [
	'key' => 'field_card_grupo',
	'label' => 'Card',
	'name' => 'card_grupo',
	'type' => 'group',
	'wrapper' => ['width' => 80],
	'conditional_logic' => [[['field' => 'field_pecas', 'operator' => '==', 'value' => 'card']]],
	'sub_fields' => [
		[
			'key' => 'field_card_tema',
			'label' => 'Título',
			'name' => 'tema',
			'type' => 'text',
		],
		[
			'key' => 'field_card_texto_base',
			'label' => 'Texto-base',
			'name' => 'texto_base',
			'type' => 'textarea',
		],
	]
];