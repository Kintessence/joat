<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_shortcode('links_gallery', function() {
    $links = get_posts(['post_type' => 'link_entry', 'numberposts' => -1]);
    ob_start(); ?>
    <div>
		<input type="text" id="linkSearch" placeholder="Buscar por título ou tag..." class="link-search">
    </div>
    
    <div class="links-gallery">
        <?php foreach ( $links as $link ) :
            $url = get_field('link_url', $link->ID);
            $desc = get_post_field('post_content', $link->ID);
            $tags = wp_get_post_terms($link->ID, 'post_tag', ['fields' => 'names']);
        ?>
             
				<a href="<?php echo esc_url($url); ?>" target="_blank" class="link-card" data-title="<?php echo esc_attr($link->post_title); ?>" data-tags="<?php echo esc_attr(implode(',', $tags)); ?>">
					<div>
						<h3><?php echo esc_html($link->post_title); ?></h3>
						<p><?php echo esc_html($desc); ?></p>
						<?php if ($tags): ?><span class="tags"><?php echo implode(', ', $tags); ?></span><?php endif; ?>						
					</div>
				</a>
            
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
});
