<?php
return [
	'key' => 'field_cracha',
	'label' => 'Crachá',
	'name' => 'cracha',
	'type' => 'text',
	'conditional_logic' => [[['field' => 'field_pecas', 'operator' => '==', 'value' => 'cracha']]],
];