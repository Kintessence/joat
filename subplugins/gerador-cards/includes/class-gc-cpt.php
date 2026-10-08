<?php
if (!defined('ABSPATH')) exit;

class CG_CPT {

    public function __construct() {
        add_action('init', [ $this, 'register_cpt' ], 5);
    }

    public function register_cpt() {
        register_post_type('saved_cards', [
            'labels' => [
                'name'          => 'Cards Salvos',
                'singular_name' => 'Card Salvo',
                'add_new_item'  => 'Adicionar Novo Card',
                'edit_item'     => 'Editar Card'
            ],
            'public'             => false,
            'show_ui'            => true,
            'has_archive'        => false,
            'publicly_queryable' => false,
            'supports'           => [ 'title', 'editor' ],
            'menu_icon'          => 'dashicons-format-image',
            'show_in_rest'       => false,
        ]);
    }
}