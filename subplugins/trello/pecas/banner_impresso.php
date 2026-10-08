<?php
return [
	'key' => 'field_banner_impresso_grupo',
	'label' => 'Banner (Impresso)',
	'name' => 'banner_impresso_grupo',
	'type' => 'group',			
	'wrapper' => ['width' => 80],
	'conditional_logic' => [
		[
			[
				'field' => 'field_pecas',
				'operator' => '==',
				'value' => 'banner_impresso'
			]
		]
	],
	'sub_fields' => [
		
		[
			'key' => 'field_banner_impresso_dimensoes',
			'label' => 'Dimensões',
			'name' => 'dimensoes',
			'type' => 'text',
		],
		
		[
			'key' => 'field_banner_impresso_local',
			'label' => 'Local de Instalação',
			'name' => 'local_instalacao',
			'type' => 'group',
			'sub_fields' => [], // usa seu grupo existente
		],
		
		[
			'key' => 'field_banner_impresso_texto',
			'label' => 'Texto Principal',
			'name' => 'texto_principal',
			'type' => 'textarea',
		]
	]
];