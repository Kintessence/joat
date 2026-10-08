<?php
return [
	'key' => 'field_manual',
	'label' => 'Manual',
	'name' => 'manual',
	'type' => 'text',
	'conditional_logic' => [[['field' => 'field_pecas', 'operator' => '==', 'value' => 'manual',],],],
];