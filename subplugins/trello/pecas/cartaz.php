<?php
return [
	'key' => 'field_cartaz',
	'label' => 'Cartaz',
	'name' => 'cartaz_grupo',
	'type' => 'group',
	'wrapper' => ['width' => 80],
	'conditional_logic' => [
		[
			[
				'field' => 'field_pecas',
				'operator' => '==',
				'value' => 'cartaz'
			]
		]
	],
	'sub_fields' => [
		[
			'key' => 'field_cartaz_texto_base',
			'label' => 'Texto-base',
			'name' => 'texto_base',
			'type' => 'textarea',
		],
	]
];