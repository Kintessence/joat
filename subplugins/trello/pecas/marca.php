<?php
return [
	'key' => 'field_marca_grupo',
	'label' => 'Marca',
	'name' => 'marca_grupo',
	'type' => 'group',
	'wrapper' => ['width' => 80],
	'conditional_logic' => [[['field' => 'field_pecas', 'operator' => '==', 'value' => 'marca']]],
	'sub_fields' => [
		
		[
			'key' => 'field_marca_existente',
			'label' => 'Marca existente?',
			'name' => 'marca_existente',
			'type' => 'true_false',
			'ui' => 1,
		],
		
		[
			'key' => 'field_marca_upload',
			'label' => 'Upload da marca atual',
			'name' => 'marca_upload',
			'type' => 'image',
			'conditional_logic' => [
				[
					[
						'field' => 'field_marca_existente',
						'operator' => '==',
						'value' => '1'
					]				
				]
			],
			
			[
				'key' => 'field_marca_direcao',
				'label' => 'Direção desejada',
				'name' => 'direcao',
				'type' => 'textarea',
			],
		]
	],
];