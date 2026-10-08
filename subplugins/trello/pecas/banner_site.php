<?php
return [
	'key' => 'field_banner_site_group',
	'label' => 'Banner (site)',
	'name' => 'banner_site',
	'type' => 'group',
	'conditional_logic' => [
		[
			[
				'field' => 'field_pecas',
				'operator' => '==',
				'value' => 'banner_site',
			],
		],
	],
	'sub_fields' => [
		[
			'key' => 'field_largura_banner_site',
			'label' => 'Largura (px)',
			'name' => 'largura_banner',
			'wrapper' => ['width' => '50'],
			'type' => 'number',
			'min' => 100,
			'step' => 5,
			'conditional_logic' => []
		],
        [
            'key' => 'field_altura_banner_site',
            'label' => 'Altura (px)',
            'name' => 'altura_banner',
            'wrapper' => ['width' => '50'],
            'type' => 'number',
			'min' => 100,
			'step' => 5,
            'conditional_logic' => []
        ],
        [
            'key' => 'field_url_banner_site',
            'label' => 'Página do banner (url)',
            'name' => 'url_banner_site',
            'type' => 'url',
            'conditional_logic' => []
        ]
	]
];