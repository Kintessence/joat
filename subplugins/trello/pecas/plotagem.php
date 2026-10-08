<?php
return [
	[
		'key' => 'field_plotagem',
		'label' => 'Plotagem',
		'name' => 'plotagem',
		'type' => 'text',
		'conditional_logic' => [[[ 'field' => 'field_pecas', 'operator' => '==', 'value' => 'plotagem',],],],	
	],
	[
		'key' => 'field_largura_plotagem',
		'label' => 'Largura (cm)',
		'name' => 'largura_plotagem',
		'wrapper' => ['width' => '50'],
		'type' => 'number',
		'conditional_logic' => [],
		'minimum_value' => 1
	],
	[
		'key' => 'field_altura_plotagem',
		'label' => 'Altura (cm)',
		'name' => 'altura',
		'wrapper' => ['width' => '50'],
		'type' => 'number',
		'conditional_logic' => [],
		'minimum_value' => 1
	],
];