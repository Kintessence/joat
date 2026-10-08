<?php
return [
	[
		'key' => 'field_medidas',
		'label' => 'Medidas',
		'name' => 'medidas',
		'type' => 'radio',
		'layout' => 'horizontal',
		'choices' => [
			'4x2' => '4x2',
			'6x3' => '6x3',
			'outra' => 'outra'
		],
		'conditional_logic' =>
		[[[ 'field' => 'field_pecas', 'operator' => '==', 'value' => 'placa' ]]]
	],
	[
		'key' => 'field_largura',
		'label' => 'Largura (m)',
		'name' => 'largura',
		'wrapper' => ['width' => '50'],
		'type' => 'number',
		'conditional_logic' =>
		[[[ 'field' => 'field_medidas', 'operator' => '==', 'value' => 'outra' ]]]
	],
	[
		'key' => 'field_altura',
		'label' => 'Altura (m)',
		'name' => 'altura',
		'wrapper' => ['width' => '50'],
		'type' => 'number',
		'conditional_logic' =>
		[[[ 'field' => 'field_medidas', 'operator' => '==', 'value' => 'outra' ]]]
	]
];