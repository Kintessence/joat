<?php
return [
	'key' => 'field_camisa',
	'label' => 'Camisa',
	'name' => 'camisa',
	'type' => 'text',
	'conditional_logic' => [[['field' => 'field_pecas', 'operator' => '==', 'value' => 'camisa',],],],
];