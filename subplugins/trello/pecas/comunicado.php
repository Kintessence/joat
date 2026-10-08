<?php
return [
	'key' => 'field_comunicado',
	'label' => 'Comunicado',
	'name' => 'comunicado',
	'type' => 'text',
	'conditional_logic' => [[['field' => 'field_pecas', 'operator' => '==', 'value' => 'comunicado',],],],
];