<?php
return [
	'key' => 'field_windbanner',
	'label' => 'Windbanner',
	'name' => 'windbanner',
	'type' => 'text',
	'conditional_logic' => [[['field' => 'field_pecas', 'operator' => '==', 'value' => 'windbanner',],],],
];