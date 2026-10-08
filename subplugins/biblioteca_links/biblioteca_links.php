<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/* ==========================================================================
   1. REGISTRO DO CPT (Custom Post Type)
   ========================================================================== */
function lg_register_cpt() {
	$labels = [
		'name'          => 'Links',
		'singular_name' => 'Link',
		'add_new'       => 'Adicionar Novo',
		'add_new_item'  => 'Adicionar Novo Link',
		'edit_item'     => 'Editar Link',
		'new_item'      => 'Novo Link',
		'view_item'     => 'Ver Link',
		'search_items'  => 'Buscar Links',
		'menu_name'     => 'Links Gallery',
	];

	$args = [
		'labels'       => $labels,
		'public'       => true,
		'menu_icon'    => 'dashicons-admin-links',
		'supports'     => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt' ],
		'taxonomies'   => [ 'post_tag' ],
		'has_archive'  => true,
		'rewrite'      => [ 'slug' => 'links' ],
		'show_in_rest' => true,
	];

	register_post_type( 'link_entry', $args );
}
add_action( 'init', 'lg_register_cpt' );


/* ==========================================================================
   2. HELPER SEGURO PARA CHOICES (Previne Fatal Error)
   ========================================================================== */
function lg_get_fotografos_choices() {
	if ( function_exists( 'get_choices' ) ) {
		return (array) get_choices( 'acf', 'fotografos_lista' );
	}
	return [ 'geral' => 'Geral' ]; // Fallback seguro
}


/* ==========================================================================
   3. REGISTRO DOS CAMPOS ACF
   ========================================================================== */
function lg_register_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

	// Grupo de Campos para o Formulário Frontend
	acf_add_local_field_group([
		'key'      => 'group_link_entry_fields',
		'title'    => 'Campos de Link (Formulário)',
		'fields'   => [
			[
				'key'      => 'field_link_title',
				'label'    => 'Título',
				'name'     => 'link_title',
				'type'     => 'text',
				'required' => 1,
			],
			[
				'key'      => 'field_link_url',
				'label'    => 'URL',
				'name'     => 'link_url',
				'type'     => 'url',
				'required' => 1,
			],
			[
				'key'   => 'field_link_description',
				'label' => 'Descrição',
				'name'  => 'link_description',
				'type'  => 'textarea',
				'rows'  => 3,
			],
			[
				'key'     => 'field_link_fotografo',
				'label'   => 'Fotógrafo',
				'name'    => 'link_fotografo',
				'type'    => 'select',
				'choices' => lg_get_fotografos_choices(),
			],
			[
				'key'        => 'field_link_tags',
				'label'      => 'Tags',
				'name'       => 'link_tags',
				'type'       => 'taxonomy',
				'taxonomy'   => 'post_tag',
				'field_type' => 'multi_select',
				'add_term'   => 1,
				'save_terms' => 1,
				'load_terms' => 1,
			],
		],
		'location' => [
			[ [ 'param' => 'af_form', 'operator' => '==', 'value' => 'form_enviar_link' ] ],
		],
	]);

	// Campo exibido no Painel Admin do Post Type
	acf_add_local_field_group([
		'key'      => 'group_link_entry_url_field',
		'title'    => 'Metadados do Link',
		'fields'   => [
			[
				'key'      => 'field_admin_link_url',
				'label'    => 'URL Alvo',
				'name'     => 'link_url',
				'type'     => 'url',
				'required' => 1,
			],
		],
		'location' => [
			[ [ 'param' => 'post_type', 'operator' => '==', 'value' => 'link_entry' ] ],
		],
	]);
}
add_action( 'acf/init', 'lg_register_acf_fields' );


/* ==========================================================================
   4. REGISTRO DO FORMULÁRIO (Advanced Forms)
   ========================================================================== */
function lg_register_af_forms() {
	if ( function_exists( 'af_register_form' ) ) {
		af_register_form([
			'key'   => 'form_enviar_link',
			'title' => 'Enviar Link',
		]);
	}
}
add_action( 'af/register_forms', 'lg_register_af_forms' );


/* ==========================================================================
   5. PROCESSAMENTO DO FORMULÁRIO (Form Handler)
   ========================================================================== */
function lg_handle_form_submission( $form, $fields, $args ) {
	if ( 'form_enviar_link' !== $form['key'] ) return;

	$title       = function_exists( 'af_get_field' ) ? af_get_field( 'link_title' ) : '';
	$url         = function_exists( 'af_get_field' ) ? af_get_field( 'link_url' ) : '';
	$description = function_exists( 'af_get_field' ) ? af_get_field( 'link_description' ) : '';
	$fotografo   = function_exists( 'af_get_field' ) ? af_get_field( 'link_fotografo' ) : '';
	$tags        = function_exists( 'af_get_field' ) ? af_get_field( 'link_tags' ) : [];

	if ( empty( $url ) || empty( $title ) ) {
		wp_die( 'Por favor, preencha todos os campos obrigatórios.' );
	}

	// Checagem de duplicatas pela URL
	$exists = new WP_Query([
		'post_type'      => 'link_entry',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => [
			[
				'key'     => 'link_url',
				'value'   => esc_url_raw( $url ),
				'compare' => '=',
			],
		],
	]);

	if ( $exists->have_posts() ) {
		wp_die( 'Este link já foi cadastrado anteriormente.' );
	}

	// Prepara o conteúdo do post
	$content = sanitize_textarea_field( $description );
	if ( ! empty( $fotografo ) ) {
		$content .= ' | Fotógrafo: ' . sanitize_text_field( $fotografo );
	}

	// Cria o post
	$post_id = wp_insert_post([
		'post_title'   => sanitize_text_field( $title ),
		'post_content' => $content,
		'post_type'    => 'link_entry',
		'post_status'  => 'publish',
	]);

	if ( is_wp_error( $post_id ) || ! $post_id ) {
		wp_die( 'Erro ao salvar o link. Tente novamente.' );
	}

	// Salva a meta da URL
	update_post_meta( $post_id, 'link_url', esc_url_raw( $url ) );

	// Processa e vincula as Tags do WP
	if ( ! empty( $tags ) ) {
		$tag_ids   = [];
		$tags_list = is_array( $tags ) ? $tags : explode( ',', (string) $tags );

		foreach ( $tags_list as $single_tag ) {
			if ( is_numeric( $single_tag ) ) {
				$tag_ids[] = (int) $single_tag;
			} elseif ( is_string( $single_tag ) && ! empty( trim( $single_tag ) ) ) {
				$term = term_exists( trim( $single_tag ), 'post_tag' );
				if ( ! $term ) {
					$term = wp_insert_term( trim( $single_tag ), 'post_tag' );
				}
				if ( ! is_wp_error( $term ) ) {
					$tag_ids[] = (int) $term['term_id'];
				}
			}
		}

		if ( ! empty( $tag_ids ) ) {
			wp_set_post_terms( $post_id, $tag_ids, 'post_tag' );
		}
	}
}
add_action( 'af/form/submission', 'lg_handle_form_submission', 10, 3 );


/* ==========================================================================
   6. ASSETS E SHORTCODE DA GALERIA
   ========================================================================== */
function lg_enqueue_assets() {
	$plugin_url = plugin_dir_url( __FILE__ );
	wp_register_style( 'links-gallery-style', $plugin_url . 'assets/css/style.css', [], '1.0.0' );
	wp_register_script( 'links-gallery-search', $plugin_url . 'assets/js/search.js', [ 'jquery' ], '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'lg_enqueue_assets' );

function lg_render_shortcode() {
	wp_enqueue_style( 'links-gallery-style' );
	wp_enqueue_script( 'links-gallery-search' );

	$links = get_posts([
		'post_type'      => 'link_entry',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	]);

	ob_start();
	?>
	<style>
		.links-gallery { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1rem; }
		.link-card { flex: 0 1 calc(20% - 1rem); border: 1px solid #ddd; padding: 1rem; border-radius: 6px; background: #fafafa; text-decoration: none; color: inherit; }
		.link-card h3 { margin-top: 0; font-size: 1.1rem; }
		.link-card p { margin: 0; font-size: 0.9rem; color: #444; }
		.link-card .tags { display: block; margin-top: .5rem; color: #666; font-size: .8em; font-weight: bold; }
		.link-search { width: 100%; padding: .5rem; border: 1px solid #ccc; border-radius: 4px; }
		@media(max-width:800px){ .link-card { flex: 1 1 calc(50% - 1rem); } }
		@media(max-width:500px){ .link-card { flex: 1 1 100%; } }
	</style>

	<div class="links-gallery-wrapper">
		<input type="text" id="linkSearch" placeholder="Buscar por título ou tag..." class="link-search">
		
		<div class="links-gallery">
			<?php if ( ! empty( $links ) ) : ?>
				<?php foreach ( $links as $link ) :
					$url = get_post_meta( $link->ID, 'link_url', true );
					if ( empty( $url ) && function_exists( 'get_field' ) ) {
						$url = get_field( 'link_url', $link->ID );
					}
					$desc        = get_post_field( 'post_content', $link->ID );
					$tags        = wp_get_post_terms( $link->ID, 'post_tag', [ 'fields' => 'names' ] );
					$tags_string = ! is_wp_error( $tags ) ? implode( ',', $tags ) : '';
				?>
					<a href="<?php echo esc_url( $url ? $url : '#' ); ?>" target="_blank" class="link-card" data-title="<?php echo esc_attr( $link->post_title ); ?>" data-tags="<?php echo esc_attr( $tags_string ); ?>">
						<div>
							<h3><?php echo esc_html( $link->post_title ); ?></h3>
							<p><?php echo esc_html( $desc ); ?></p>
							<?php if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) : ?>
								<span class="tags"><?php echo esc_html( implode( ', ', $tags ) ); ?></span>
							<?php endif; ?>						
						</div>
					</a>
				<?php endforeach; ?>
			<?php else : ?>
				<p>Nenhum link encontrado.</p>
			<?php endif; ?>
		</div>
	</div>

	<script>
		jQuery(document).ready(function($){
			$('#linkSearch').on('input', function(){
				var term = $(this).val().toLowerCase();
				$('.link-card').each(function(){
					var title = $(this).data('title') ? $(this).data('title').toString().toLowerCase() : '';
					var tags = $(this).data('tags') ? $(this).data('tags').toString().toLowerCase() : '';
					$(this).toggle(title.includes(term) || tags.includes(term));
				});
			});
		});
	</script>
	<?php
	return ob_get_clean();
}

// Suporte a ambos os nomes de shortcode
add_shortcode( 'link_cards', 'lg_render_shortcode' );
add_shortcode( 'links_gallery', 'lg_render_shortcode' );