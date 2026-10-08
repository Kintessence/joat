<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('init', function() {
    $labels = [
        'name' => 'Links',
        'singular_name' => 'Link',
        'add_new' => 'Adicionar Novo',
        'add_new_item' => 'Adicionar Novo Link',
        'edit_item' => 'Editar Link',
        'new_item' => 'Novo Link',
        'view_item' => 'Ver Link',
        'search_items' => 'Buscar Links',
        'menu_name' => 'Links'
    ];

    $args = [
        'labels' => $labels,
        'public' => true,
        'menu_icon' => 'dashicons-admin-links',
        'supports' => ['title', 'editor', 'author', 'thumbnail', 'excerpt'],
        'taxonomies' => ['post_tag'],
        'has_archive' => true,
        'rewrite' => ['slug' => 'links'],
        'show_in_rest' => true,
    ];

    register_post_type('link_entry', $args);
});
