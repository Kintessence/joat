<?php
return [
	'key' => 'field_backdrop',
	'label' => 'Backdrop',
	'name' => 'backdrop',
	'type' => 'text',
	'conditional_logic' => [[['field' => 'field_pecas', 'operator' => '==', 'value' => 'backdrop',],],],
];