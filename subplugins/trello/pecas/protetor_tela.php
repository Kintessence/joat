<?php
return [
	'key' => 'field_protetor_tela',
	'label' => 'Protetor de tela',
	'name' => 'protetor_tela',
	'type' => 'text',
	'conditional_logic' => [[['field' => 'field_pecas', 'operator' => '==', 'value' => 'protetor_tela',],],],
];