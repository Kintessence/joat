<?php
return [
	'key' => 'field_placa',
	'label' => 'Placa',
	'name' => 'placa',
	'type' => 'text',
	'conditional_logic' => [
		[
			[
				'field' => 'field_pecas',
				'operator' => '==',
				'value' => 'placa',
			],
		],
	],
];