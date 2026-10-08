<?php
/**
 * Plugin Name: Gerador de Convites PMVV
 * Description: Editor e diagramador modular (1080x1920 real com scale 0.375) para convites institucionais (9:16).
 * Version: 10.0.1
 * Author: IntraSemcom PMVV
 */

namespace ConvitesPMVV;

if (!defined('ABSPATH')) exit;

/**
 * 1. CLASSE DO CUSTOM POST TYPE (CPT)
 */
class PostType {
    public const POST_TYPE = 'saved_cards';

    public static function register(): void {
        register_post_type(self::POST_TYPE, [
            'labels' => [
                'name'          => 'Convites Salvos',
                'singular_name' => 'Convite Salvo',
                'add_new_item'  => 'Adicionar Novo Convite',
                'edit_item'     => 'Editar Convite'
            ],
            'public'             => true,
            'has_archive'        => false,
            'publicly_queryable' => true,
            'supports'           => ['title', 'editor', 'thumbnail'],
            'menu_icon'          => 'dashicons-format-image',
        ]);
    }

    public static function redirect_single(string $template): string {
        if (is_singular(self::POST_TYPE)) {
            get_header();
            echo '<div style="padding: 40px 0; max-width: 1200px; margin: 0 auto;">';
            echo do_shortcode('[gerador_master]');
            echo '</div>';
            get_footer();
            return plugin_dir_path(__FILE__) . 'index.php';
        }
        return $template;
    }
}

/**
 * 2. PROCESSAMENTO BACKEND & AJAX
 */
class AjaxHandler {
    public static function register(): void {
        add_action('wp_ajax_cg_save_card_data', [__CLASS__, 'handle_save']);
        add_action('wp_ajax_nopriv_cg_save_card_data', [__CLASS__, 'handle_save']);
    }

    public static function handle_save(): void {
        $card_id      = isset($_POST['card_id']) ? intval($_POST['card_id']) : 0;
        $title        = sanitize_text_field($_POST['title'] ?? '');
        $body_content = wp_kses_post($_POST['f4'] ?? '');

        $f1 = sanitize_text_field($_POST['f1'] ?? '');
        $f2 = sanitize_text_field($_POST['f2'] ?? '');
        $f3 = sanitize_text_field($_POST['f3'] ?? '');

        if (empty($title)) {
            if (strtolower($f1) === 'cole ou digite o tema aqui') $f1 = '';
            if (strtolower($f2) === 'cole ou digite o título neste campo') $f2 = '';
            if (strtolower($f3) === 'nome do bairro aqui') $f3 = '';

            $partes = [];
            $prefixo = trim("{$f1} {$f2}");
            if (!empty($prefixo)) $partes[] = $prefixo;
            if (!empty($f3)) $partes[] = $f3;

            $title = !empty($partes) ? implode(' – ', $partes) : 'Convite Gerado em ' . date_i18n('d/m/Y H:i');
        }

        $post_data = [
            'post_title'   => $title,
            'post_content' => $body_content,
            'post_status'  => 'publish',
            'post_type'    => PostType::POST_TYPE
        ];

        if ($card_id === 0) {
            $card_id = wp_insert_post($post_data);
        } else {
            $post_data['ID'] = $card_id;
            wp_update_post($post_data);
        }

        if (!$card_id || is_wp_error($card_id)) {
            wp_send_json_error(['message' => 'Erro ao salvar o post.']);
        }

        update_post_meta($card_id, '_cg_field_1', $f1);
        update_post_meta($card_id, '_cg_field_2', $f2);
        update_post_meta($card_id, '_cg_field_3', $f3);
        update_post_meta($card_id, '_cg_field_4', $body_content);
        update_post_meta($card_id, '_cg_f4_font_size', sanitize_text_field($_POST['f4_font_size'] ?? '48'));

        update_post_meta($card_id, '_cg_field_5_1_raw', sanitize_text_field($_POST['date_raw'] ?? ''));
        update_post_meta($card_id, '_cg_field_5_1_time_raw', sanitize_text_field($_POST['time_raw'] ?? ''));
        update_post_meta($card_id, '_cg_field_5_1', wp_kses_post($_POST['f5_1_compiled'] ?? ''));
        update_post_meta($card_id, '_cg_field_5_1_time', sanitize_text_field($_POST['f5_1_time_compiled'] ?? ''));
        update_post_meta($card_id, '_cg_field_5_2', sanitize_text_field($_POST['f5_2'] ?? ''));
        update_post_meta($card_id, '_cg_field_5_3', sanitize_text_field($_POST['f5_3'] ?? ''));

        update_post_meta($card_id, '_cg_bg_position_y', sanitize_text_field($_POST['bg_position_y'] ?? '0'));
        update_post_meta($card_id, '_cg_bg_zoom', sanitize_text_field($_POST['bg_zoom'] ?? '100'));
        update_post_meta($card_id, '_cg_mask_img', esc_url_raw($_POST['mask_img'] ?? ''));
        update_post_meta($card_id, '_cg_overlay_img', esc_url_raw($_POST['overlay_img'] ?? ''));

        if (!empty($_POST['bg_img'])) {
            $img_data = $_POST['bg_img'];
            if (strpos($img_data, 'data:image') === 0) {
                @list(, $img_data) = explode(',', $img_data);
                $decoded = base64_decode($img_data);
                $upload = wp_upload_bits('bg-convite-' . time() . '.png', null, $decoded);
                if (empty($upload['error'])) {
                    update_post_meta($card_id, '_cg_bg_img', esc_url_raw($upload['url']));
                }
            } else {
                update_post_meta($card_id, '_cg_bg_img', esc_url_raw($img_data));
            }
        }

        if (!empty($_POST['exported_img']) && strpos($_POST['exported_img'], 'data:image') === 0) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';

            @list(, $exp_data) = explode(',', $_POST['exported_img']);
            $decoded_export = base64_decode($exp_data);
            $filename = 'convite-render-' . $card_id . '-' . time() . '.png';
            $upload_export = wp_upload_bits($filename, null, $decoded_export);

            if (empty($upload_export['error'])) {
                $attach_id = wp_insert_attachment([
                    'post_mime_type' => 'image/png',
                    'post_title'     => sanitize_file_name($filename),
                    'post_status'    => 'inherit'
                ], $upload_export['file'], $card_id);

                $attach_data = wp_generate_attachment_metadata($attach_id, $upload_export['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
                set_post_thumbnail($card_id, $attach_id);
            }
        }

        wp_send_json_success(['card_id' => $card_id, 'title' => $title]);
    }
}

/**
 * 3. INTERFACE E SHORTCODES
 */
class Shortcodes {
    public static function register(): void {
        add_shortcode('gerador_master', [__CLASS__, 'render_master']);
        add_shortcode('lista_de_cards', [__CLASS__, 'render_listagem']);
        add_shortcode('gerador_de_cards', [__CLASS__, 'render_editor']);
    }

    public static function get_sobrecamadas(): array {
        return [
            '' => 'Nenhuma sobrecamada',
            'https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/selo_governo.png'      => 'Selo Governo do Estado',
            'https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/selo_obras.png'        => 'Selo Obras Estruturantes',
            'https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/selo_comemorativo.png' => 'Selo Comemorativo / Especial',
        ];
    }

    public static function render_master(): string {
        $action  = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
        $card_id = isset($_GET['card_id']) ? intval($_GET['card_id']) : 0;

        ob_start();
        if ($action === 'edit' || $action === 'new' || $card_id > 0) {
            echo '<div style="margin-bottom: 20px; font-family: Arial, sans-serif;">';
            echo '  <a href="' . esc_url(strtok($_SERVER["REQUEST_URI"], '?')) . '" style="text-decoration: none; color: #06305d; font-weight: bold; display: inline-flex; align-items: center; gap: 5px;">';
            echo '      <span class="dashicons dashicons-arrow-left-alt2" style="font-size: 18px; width: 18px; height: 18px;"></span> Voltar para a Central de Gestão';
            echo '  </a>';
            echo '</div>';
            echo do_shortcode('[gerador_de_cards card_id="' . $card_id . '"]');
        } else {
            echo do_shortcode('[lista_de_cards]');
        }
        return ob_get_clean();
    }

    public static function render_listagem(): string {
        ob_start();
        $query = new \WP_Query([
            'post_type'      => PostType::POST_TYPE,
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ]);
        ?>
        <style>
            .cg-gestao-container { font-family: 'Lato', sans-serif; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
            .cg-gestao-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #f4f5f7; padding-bottom: 15px; margin-bottom: 25px; }
            .cg-gestao-header h2 { margin: 0; font-family: 'Qanelas', sans-serif; font-weight: 700; color: #06305d; }
            .cg-grid-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
            .cg-card-item { background: #f4f5f7; border-radius: 6px; overflow: hidden; border: 1px solid #e0e2e6; display: flex; flex-direction: column; transition: transform 0.2s ease; }
            .cg-card-item:hover { transform: translateY(-3px); }
            .cg-card-thumb { height: 180px; background-size: cover; background-position: center top; background-color: #ddd; }
            .cg-card-meta { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; }
            .cg-card-meta h4 { margin: 0 0 10px 0; font-family: 'Qanelas', sans-serif; font-weight: 700; color: #333; font-size: 14px; line-height: 1.3; }
            .cg-btn-action { padding: 8px; border-radius: 4px; font-size: 12px; font-weight: bold; cursor: pointer; text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center; gap: 4px; background: #007cba; color: #fff; margin-top: auto; }
            .cg-btn-add-new { background: #46b450; color: #fff; text-decoration: none; padding: 10px 16px; border-radius: 4px; font-weight: bold; font-size: 14px; display: flex; align-items: center; gap: 6px; }
        </style>

        <div class="cg-gestao-container">
            <div class="cg-gestao-header">
                <h2>Gerador de Convites — Convites Salvos</h2>
                <a href="<?php echo esc_url(add_query_arg('action', 'new', strtok($_SERVER["REQUEST_URI"], '?'))); ?>" class="cg-btn-add-new">
                    <span class="dashicons dashicons-plus-alt"></span> Criar Novo Convite
                </a>
            </div>
            <div class="cg-grid-cards">
                <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); 
                    $c_id = get_the_ID();
                    $thumb = get_the_post_thumbnail_url($c_id, 'medium') ?: (get_post_meta($c_id, '_cg_bg_img', true) ?: 'https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/bg.png');
                    $edit_link = add_query_arg(['action' => 'edit', 'card_id' => $c_id], strtok($_SERVER["REQUEST_URI"], '?'));
                ?>
                    <div class="cg-card-item">
                        <div class="cg-card-thumb" style="background-image: url('<?php echo esc_url($thumb); ?>');"></div>
                        <div class="cg-card-meta">
                            <h4><?php the_title(); ?></h4>
                            <a href="<?php echo esc_url($edit_link); ?>" class="cg-btn-action">
                                <span class="dashicons dashicons-edit"></span> Editar
                            </a>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); else : ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #666; padding: 40px 0;">Nenhum convite criado ainda.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function render_editor($atts): string {
        if (is_user_logged_in()) wp_enqueue_media();
        wp_enqueue_script('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', [], '1.4.1', true);

        $args = shortcode_atts(['card_id' => 0], $atts);
        $card_id = intval($args['card_id']) ?: intval($_GET['card_id'] ?? 0);
        $is_new = ($card_id === 0);

        $field_1  = !$is_new ? get_post_meta($card_id, '_cg_field_1', true) : 'cole ou digite o tema aqui';
        $field_2  = !$is_new ? get_post_meta($card_id, '_cg_field_2', true) : 'cole ou digite o título neste campo';
        $field_3  = !$is_new ? get_post_meta($card_id, '_cg_field_3', true) : 'nome do bairro aqui';

        $saved_body   = !$is_new ? get_post_field('post_content', $card_id) : '';
        $field_4      = !empty($saved_body) ? $saved_body : (get_post_meta($card_id, '_cg_field_4', true) ?: 'Texto');
        
        $raw_f4_size = !$is_new ? (get_post_meta($card_id, '_cg_f4_font_size', true) ?: '48') : '48';
        if (floatval($raw_f4_size) <= 24) {
            $f4_font_size = strval(round(floatval($raw_f4_size) * 2.6666));
        } else {
            $f4_font_size = $raw_f4_size;
        }

        $field_5_1      = !$is_new ? get_post_meta($card_id, '_cg_field_5_1', true) : 'D/M dia-semana';
        $field_5_1_time = !$is_new ? get_post_meta($card_id, '_cg_field_5_1_time', true) : '16h';
        $field_5_2      = !$is_new ? get_post_meta($card_id, '_cg_field_5_2', true) : 'Endereço, n';
        $field_5_3      = !$is_new ? get_post_meta($card_id, '_cg_field_5_3', true) : 'Bairro';

        $bg_img        = !$is_new ? get_post_meta($card_id, '_cg_bg_img', true) : '';
        $mask_img      = !$is_new ? get_post_meta($card_id, '_cg_mask_img', true) : 'https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/bg_pmvv.png';
        $overlay_img   = !$is_new ? get_post_meta($card_id, '_cg_overlay_img', true) : '';
        $bg_position_y = !$is_new ? get_post_meta($card_id, '_cg_bg_position_y', true) : '0';
        $bg_zoom       = !$is_new ? get_post_meta($card_id, '_cg_bg_zoom', true) : '100';

        $sobrecamadas = self::get_sobrecamadas();

        ob_start();
        ?>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;0,900;1,400&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

        <style>
            :root {
                --azul-pmvv: #0057a4;
                --azul-escuro-pmvv: #06305d;
                --vermelho-pmvv: #e30421;
                --cg-max-height-f4: 427px; /* 160px originais x 2.666 */
                --cg-f2-size-l1: 101px;    /* 38px originais x 2.666 */
                --cg-f2-size-l2: 80px;     /* 30px originais x 2.666 */
                --cg-f2-size-l3: 53px;     /* 20px originais x 2.666 */
            }

            @font-face { font-family: 'Qanelas'; src: url('<?php echo plugin_dir_url(__FILE__); ?>fonts/Qanelas-Regular.woff2') format('woff2'); font-weight: 400; font-style: normal; }
            @font-face { font-family: 'Qanelas'; src: url('<?php echo plugin_dir_url(__FILE__); ?>fonts/Qanelas-SemiBold.woff2') format('woff2'); font-weight: 600; font-style: normal; }
            @font-face { font-family: 'Qanelas'; src: url('<?php echo plugin_dir_url(__FILE__); ?>fonts/Qanelas-Bold.woff2') format('woff2'); font-weight: 700; font-style: normal; }
            @font-face { font-family: 'Qanelas'; src: url('<?php echo plugin_dir_url(__FILE__); ?>fonts/Qanelas-Black.woff2') format('woff2'); font-weight: 850; font-style: normal; }

            .cg-main-wrapper { display: grid; grid-template-columns: 320px 1fr; gap: 30px; font-family: 'Lato', sans-serif; background: #f4f5f7; padding: 25px; border-radius: 12px; color: #333; }
            .cg-sidebar { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
            .cg-sidebar h3 { margin-top: 0; font-size: 18px; color: #1e1e1e; font-family: 'Qanelas', sans-serif; font-weight: 700; }
            .cg-control-group { margin-bottom: 20px; }
            .cg-control-group label { display: block; font-weight: 600; margin-bottom: 6px; font-size: 14px; }
            .cg-control-group select, .cg-control-group input[type="text"], .cg-control-group input[type="date"], .cg-control-group input[type="time"], .cg-control-group input[type="range"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
            .cg-btn { width: 100%; padding: 12px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; margin-bottom: 10px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px; font-family: 'Qanelas', sans-serif; }
            .cg-btn-save { background: #007cba; color: #fff; }
            .cg-btn-generate { background: #46b450; color: #fff; }
            .cg-btn-media { background: #f7f7f7; color: #555; border: 1px solid #ccc; font-size: 13px; padding: 8px; justify-content: flex-start; }
            .cg-preview-zone { display: flex; justify-content: center; align-items: center; background: #e0e2e6; padding: 20px; border-radius: 8px; }

            /* VIEWPORT PROTEGIDA: Encolhe o container gigante 1080x1920 para caber na tela */
            .card-viewport { 
                width: 405px; 
                height: 720px; 
                overflow: hidden; 
                position: relative; 
                border-radius: 8px; 
                box-shadow: 0 12px 35px rgba(0,0,0,0.2); 
                background: #fff;
            }

            /* CONTAINER GIGANTE HD 1:1 */
            .card-container { 
                width: 1080px; 
                height: 1920px; 
                position: absolute; 
                top: 0; left: 0; 
                transform: scale(0.375); /* 405 / 1080 = 0.375 */
                transform-origin: top left;
                background: #fff; 
                box-sizing: border-box; 
            }

            .card-bg { position: absolute !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; background-repeat: no-repeat !important; z-index: 1 !important; background-size: <?php echo esc_attr($bg_zoom); ?>% auto !important; }
            .card-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: contain; background-repeat: no-repeat; background-position: center top; z-index: 2; pointer-events: none; }
            .card-mask { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-size: 100% 100%; background-repeat: no-repeat; background-position: center top; z-index: 3; pointer-events: none; image-rendering: -webkit-optimize-contrast; }

            /* AFASTAMENTOS E ESPAÇAMENTOS DO CONVITE LÍQUIDO */
            .card-content-flow { position: relative; z-index: 4; height: 100%; display: flex; flex-direction: column; padding: 693px 106px 213px; box-sizing: border-box; }

            /* RESTAURAÇÃO EXATA DO ESTILO ORIGINAL (SEM CAIXAS COLORIDAS) */
            .field-1, .field-2, .field-3 { text-transform: uppercase; font-family: 'Qanelas', sans-serif !important; }
            
            .field-1 { text-align: center; font-size: 42px; font-weight: 600; color: var(--vermelho-pmvv); margin-bottom: 0; line-height: 1; letter-spacing: 0.045em; }
            .field-1.convite-definido:before { content: "CONVITE "; }

            .field-2 { 
                text-align: center !important; font-size: var(--cg-f2-size-l1) !important; font-weight: 850 !important; color: var(--azul-escuro-pmvv) !important; line-height: 1 !important; margin: 11px 0 !important;
                word-wrap: normal !important; width: 866px !important; max-width: 866px !important; display: block !important; white-space: nowrap !important; overflow: hidden !important; text-overflow: ellipsis !important;
                transition: font-size 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }

            .field-3 { text-align: center; font-size: 50px; font-weight: 400; color: #666; margin-top: 0; margin-bottom: 48px; }
            .field-3.bairro-definido:before { content: "BAIRRO "; }

            .field-4 { font-family: 'Lato', sans-serif; line-height: 1.15 !important; margin: 0 -104px 40px -104px; padding: 0 96px; color: var(--azul-pmvv); outline: none; text-align: left; max-height: var(--cg-max-height-f4); overflow: hidden; border-left: 11px solid var(--vermelho-pmvv); box-sizing: border-box; flex-grow: 1; }
            .field-4 p { margin-top: 0; margin-bottom: 21px; font-size: inherit !important; line-height: inherit !important; }
            .field-4 p:last-child { margin-bottom: 0; }
			.field-4 p:empty { 
				display: none !important; 
			}
            .overflow-warning { border: 2px dashed var(--vermelho-pmvv) !important; background: rgba(227, 4, 33, 0.05) !important; animation: pulse-warn 1.5s infinite; }
            @keyframes pulse-warn { 0% { opacity: 1; } 50% { opacity: 0.6; } 100% { opacity: 1; } }

            .field-5 { font-family: 'Lato', sans-serif; font-size: 48px; color: #333; font-weight: 800; line-height: 1.1; max-width: 100%; display: flex; flex-direction: column; align-items: flex-start; margin: auto; width: fit-content; background: transparent; padding: 0; box-shadow: none; }
            
            .field-5-row { 
                color: var(--azul-escuro-pmvv) !important; 
                display: flex; 
                align-items: flex-start; 
                gap: 13px; 
                margin: 3px 0; 
                text-align: left; 
            }
            .field-5-row i { 
                color: var(--vermelho-pmvv) !important; 
                font-size: 48px; 
                width: 53px; 
                height: 48px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0; 
                margin-top: 5px;
                text-align: center;
            }
            .field-5-row span { flex: 1; word-break: break-word; }

            #f5_1_date_display { color: inherit !important; text-transform: uppercase; white-space: nowrap; }
            .cg-dia-semana { color: var(--azul-pmvv) !important; margin-left: 5px !important; text-transform: uppercase !important; font-family: 'Lato', sans-serif; }
            #f5_1_time_display { color: inherit !important; }
            #f5_2, #f5_3 { font-size: 42px; font-weight: 400; color: inherit !important; }
            #f5_3 { font-weight: 400; color: var(--azul-pmvv) !important; }
            
            [contenteditable="true"]:focus { outline: 2px dashed #007cba; background: rgba(0,124,186,0.03); }
        </style>

        <div class="cg-main-wrapper">
            <div class="cg-sidebar">
                <h3>Gerador de Convites HD</h3>
                <input type="hidden" id="card-post-id" value="<?php echo esc_attr($card_id); ?>">

                <div class="cg-control-group">
                    <label>Título Interno do Registro:</label>
                    <input type="text" id="card-internal-title" value="<?php echo !$is_new ? esc_attr(get_the_title($card_id)) : ''; ?>">
                </div>

                <div class="cg-control-group">
                    <label>Tipo de Convite (Campo 1):</label>
                    <select id="cg-convite-selector">
                        <option value="">Selecione um tipo...</option>
                        <option value="Inauguração">Inauguração</option>
                        <option value="Ordem de Serviço">Ordem de Serviço</option>
                        <option value="Entrega">Entrega</option>
                        <option value="Apresentação de Projeto">Apresentação de Projeto</option>
                        <option value="outro">Outro (Digitar livremente)</option>
                    </select>
                    <input type="text" id="cg-convite-custom" style="display:none; margin-top: 8px;" placeholder="Escreva o tipo...">
                </div>

                <div class="cg-control-group">
                    <label>Selecione o Bairro:</label>
                    <select id="cg-bairro-selector">
                        <option value="">Selecione um bairro...</option>
                        <?php 
                        if (function_exists('btt_get_choices')) {
                            $bairros = btt_get_choices('acf', 'bairros_vv');
                            if (!empty($bairros)) {
                                foreach ($bairros as $bairro) {
                                    $selected = ($field_3 === $bairro) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($bairro) . '" ' . $selected . '>' . esc_html($bairro) . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="cg-control-group">
                    <label>Tamanho do Texto: <span id="cg-val-f4-size"><?php echo esc_html($f4_font_size); ?></span>px</label>
                    <input type="range" id="cg-range-f4-size" min="32" max="64" value="<?php echo esc_attr($f4_font_size); ?>" step="1">
                </div>

                <div class="cg-control-group">
                    <label>Modelo (Máscara):</label>
                    <select id="cg-mask-selector">
                        <option value="https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/bg_gov.png" <?php selected($mask_img, 'https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/bg_gov.png'); ?>>Parceria com Governo</option>
                        <option value="https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/bg_pmvv.png" <?php selected($mask_img, 'https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/bg_pmvv.png'); ?>>Prefeitura</option>
                        <option value="">Sem máscara</option>
                    </select>
                </div>

                <div class="cg-control-group">
                    <label>Sobrecamada / Selo Extra:</label>
                    <select id="cg-overlay-selector">
                        <?php foreach ($sobrecamadas as $path => $label) : ?>
                            <option value="<?php echo esc_attr($path); ?>" <?php selected($overlay_img, $path); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cg-control-group">
                    <label>Data do Evento:</label>
                    <input type="date" id="cg-date-input" value="<?php echo isset($_GET['card_id']) ? esc_attr(get_post_meta($card_id, '_cg_field_5_1_raw', true)) : ''; ?>">
                </div>

                <div class="cg-control-group">
                    <label>Horário do Evento:</label>
                    <input type="time" id="time-input" value="<?php echo isset($_GET['card_id']) ? esc_attr(get_post_meta($card_id, '_cg_field_5_1_time_raw', true)) : ''; ?>">
                </div>

                <div class="cg-control-group">
                    <label>Foto de Fundo:</label>
                    <?php if (is_user_logged_in()) : ?>
                        <button class="cg-btn cg-btn-media" id="cg-media-uploader"><span class="dashicons dashicons-format-image"></span> Escolher Imagem</button>
                        <input type="hidden" id="cg-uploader-type" value="wp_media">
                    <?php else : ?>
                        <input type="file" id="cg-local-uploader" accept="image/*">
                        <input type="hidden" id="cg-uploader-type" value="local">
                    <?php endif; ?>
                </div>

                <div class="cg-control-group" id="cg-position-y-wrapper" style="<?php echo $bg_img ? 'display:block;' : 'display:none;'; ?>">
                    <label>Posicionamento Vertical (Eixo Y): <span id="cg-val-y"><?php echo esc_html($bg_position_y); ?></span>px</label>
                    <input type="range" id="cg-range-y" min="-1066" max="1066" value="<?php echo esc_attr($bg_position_y); ?>" step="1">
                </div>

                <div class="cg-control-group" id="cg-zoom-wrapper" style="<?php echo $bg_img ? 'display:block;' : 'display:none;'; ?>">
                    <label>Zoom da Imagem: <span id="cg-val-zoom"><?php echo esc_html($bg_zoom); ?></span>%</label>
                    <input type="range" id="cg-range-zoom" min="100" max="250" value="<?php echo esc_attr($bg_zoom); ?>" step="1">
                </div>

                <button class="cg-btn cg-btn-save" id="cg-btn-save"><span class="dashicons dashicons-cloud-upload"></span> Salvar Rascunho</button>
                <button class="cg-btn cg-btn-generate" id="cg-btn-generate"><span class="dashicons dashicons-download"></span> Salvar & Baixar Convite</button>
                <div id="cg-status-message" style="margin-top:10px; font-weight:bold; font-size:13px; text-align:center;"></div>
            </div>

            <div class="cg-preview-zone">
                <div class="card-viewport">
                    <div id="card-to-render" class="card-container">
                        <div class="card-bg" id="card-bg-render" style="background-image: url('<?php echo esc_url($bg_img); ?>'); background-position: center <?php echo esc_attr($bg_position_y); ?>px;"></div>
                        <div class="card-overlay" id="card-overlay-render" style="background-image: <?php echo $overlay_img ? "url('" . esc_url($overlay_img) . "')" : 'none'; ?>;"></div>
                        <div class="card-mask" id="card-mask-render" style="background-image: url('<?php echo esc_url($mask_img); ?>');"></div>

                        <div class="card-content-flow">
                            <div class="field-1 <?php echo $field_1 ? 'convite-definido' : ''; ?>" id="f1" contenteditable="true"><?php echo esc_html($field_1); ?></div>
                            <div class="field-2" id="f2" contenteditable="true"><?php echo esc_html($field_2); ?></div>
                            <div class="field-3 <?php echo ($field_3 && $field_3 !== 'nome do bairro aqui') ? 'bairro-definido' : ''; ?>" id="f3" contenteditable="true"><?php echo esc_html($field_3); ?></div>

                            <div class="field-4" id="f4" contenteditable="true" style="font-size: <?php echo esc_attr($f4_font_size); ?>px !important;"><p><?php echo wp_kses_post($field_4); ?></p></div>

                            <div class="field-5" id="f5">
                                <div class="field-5-row">
                                    <i class="fa-solid fa-calendar-days"></i>
                                    <span id="f5_1_date_display"><?php echo wp_kses_post($field_5_1); ?></span>
                                    <i class="fa-solid fa-clock" style="margin-left: 21px;"></i>
                                    <span id="f5_1_time_display"><?php echo esc_html($field_5_1_time); ?></span>
                                </div>
                                <div class="field-5-row">
                                    <i class="fa-solid fa-location-dot"></i>
                                    <span id="f5_2" contenteditable="true"><?php echo esc_html($field_5_2); ?></span>
                                </div>
                                <div class="field-5-row">
                                    <i class="fa-solid fa-map-pin"></i>
                                    <span id="f5_3" contenteditable="true"><?php echo esc_html($field_5_3); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener("DOMContentLoaded", function() {
            const conviteSelector   = document.getElementById('cg-convite-selector');
            const conviteCustom     = document.getElementById('cg-convite-custom');
            const bairroSelector    = document.getElementById('cg-bairro-selector');
            const maskSelector      = document.getElementById('cg-mask-selector');
            const overlaySelector   = document.getElementById('cg-overlay-selector');
            const cardContainer     = document.getElementById('card-to-render');
            const bgRender          = document.getElementById('card-bg-render');
            const overlayRender     = document.getElementById('card-overlay-render');
            const maskRender        = document.getElementById('card-mask-render');
            const f1Type            = document.getElementById('f1');
            const f2Title           = document.getElementById('f2');
            const f3Bairro          = document.getElementById('f3');
            const f5_3Bairro        = document.getElementById('f5_3');
            const f4Body            = document.getElementById('f4');
            const internalTitleInput= document.getElementById('card-internal-title');
            const rangeY            = document.getElementById('cg-range-y');
            const valY              = document.getElementById('cg-val-y');
            const uploaderType      = document.getElementById('cg-uploader-type').value;
            const rangeZoom         = document.getElementById('cg-range-zoom');
            const valZoom           = document.getElementById('cg-val-zoom');
            const zoomWrapper       = document.getElementById('cg-zoom-wrapper');
            const rangeF4           = document.getElementById('cg-range-f4-size');
            const valF4             = document.getElementById('cg-val-f4-size');
            const dateInput         = document.getElementById('cg-date-input');
            const timeInput         = document.getElementById('time-input');
            const dateDisplay       = document.getElementById('f5_1_date_display');
            const timeDisplay       = document.getElementById('f5_1_time_display');
            const diasSemana        = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];

            let tituloManual = internalTitleInput.value.trim() !== '';
            internalTitleInput.addEventListener('input', () => { tituloManual = internalTitleInput.value.trim() !== ''; });

            function gerarTituloComposto() {
                let t1 = f1Type.innerText.trim();
                let t2 = f2Title.innerText.trim();
                let t3 = f3Bairro.innerText.trim();

                if (t1.toLowerCase() === 'cole ou digite o tema aqui') t1 = '';
                if (t2.toLowerCase() === 'cole ou digite o título neste campo') t2 = '';
                if (t3.toLowerCase() === 'nome do bairro aqui') t3 = '';

                let partes = [];
                let prefixo = [t1, t2].filter(Boolean).join(' ');
                if (prefixo) partes.push(prefixo);
                if (t3) partes.push(t3);

                if (partes.length > 0) return partes.join(' – ');

                const agora = new Date();
                return `Convite Gerado em ${String(agora.getDate()).padStart(2, '0')}/${String(agora.getMonth() + 1).padStart(2, '0')}/${agora.getFullYear()} ${String(agora.getHours()).padStart(2, '0')}:${String(agora.getMinutes()).padStart(2, '0')}`;
            }

            function sincronizarTitulo() {
                if (!tituloManual) internalTitleInput.value = gerarTituloComposto();
            }

            rangeY.addEventListener('input', function() {
                valY.innerText = this.value;
                bgRender.style.backgroundPositionY = `${this.value}px`;
            });

            rangeZoom.addEventListener('input', function() {
                valZoom.innerText = this.value;
                bgRender.style.setProperty('background-size', `${this.value}% auto`, 'important');
            });

            maskSelector.addEventListener('change', function() {
                maskRender.style.backgroundImage = this.value ? `url(${this.value})` : 'none';
            });

            overlaySelector.addEventListener('change', function() {
                overlayRender.style.backgroundImage = this.value ? `url(${this.value})` : 'none';
            });

            conviteSelector.addEventListener('change', function() {
                if (this.value === 'outro') {
                    f1Type.classList.add('convite-definido');
                    conviteCustom.style.display = 'block';
                    f1Type.innerText = conviteCustom.value || "Outro";
                } else if (this.value !== "") {
                    f1Type.classList.add('convite-definido');
                    conviteCustom.style.display = 'none';
                    f1Type.innerText = this.value;
                } else {
                    f1Type.classList.remove('convite-definido');
                    f1Type.innerText = "cole ou digite o tema aqui";
                }
                sincronizarTitulo();
            });

            conviteCustom.addEventListener('input', function() {
                f1Type.innerText = this.value;
                sincronizarTitulo();
            });

            bairroSelector.addEventListener('change', function() {
                if (this.value !== "") {
                    f3Bairro.classList.add('bairro-definido');
                    f3Bairro.innerText = this.value;
                    if (f5_3Bairro) f5_3Bairro.innerText = this.value;
                } else {
                    f3Bairro.classList.remove('bairro-definido');
                    f3Bairro.innerText = "nome do bairro aqui";
                    if (f5_3Bairro) f5_3Bairro.innerText = "Bairro";
                }
                sincronizarTitulo();
            });

            function atualizarDataHora() {
                if (dateInput.value) {
                    const p = dateInput.value.split('-');
                    const d = new Date(p[0], p[1] - 1, p[2]);
                    dateDisplay.innerHTML = `${String(d.getDate()).padStart(2, '0')}/${String(d.getMonth() + 1).padStart(2, '0')} <span class="cg-dia-semana">${diasSemana[d.getDay()]}</span>`;
                }
                if (timeInput && timeInput.value) {
                    const tp = timeInput.value.split(':');
                    const h = parseInt(tp[0], 10), m = parseInt(tp[1], 10);
                    timeDisplay.innerText = (m === 0) ? `${h}h` : `${h}h${String(m).padStart(2, '0')}`;
                }
            }
            if (dateInput) dateInput.addEventListener('input', atualizarDataHora);
            if (timeInput) timeInput.addEventListener('input', atualizarDataHora);

            if (uploaderType === 'wp_media') {
                let customUploader;
                document.getElementById('cg-media-uploader').addEventListener('click', function(e) {
                    e.preventDefault();
                    if (customUploader) { customUploader.open(); return; }
                    customUploader = wp.media({
                        title: 'Selecione a Imagem de Fundo',
                        button: { text: 'Aplicar Imagem' },
                        multiple: false
                    });
                    customUploader.on('select', function() {
                        const att = customUploader.state().get('selection').first().toJSON();
                        bgRender.style.backgroundImage = `url(${att.url})`;
                        bgRender.style.setProperty('background-size', `${rangeZoom.value}% auto`, 'important');
                        document.getElementById('cg-position-y-wrapper').style.display = 'block';
                        if (zoomWrapper) zoomWrapper.style.display = 'block';
                    });
                    customUploader.open();
                });
            } else {
                const localUploader = document.getElementById('cg-local-uploader');
                if (localUploader) {
                    localUploader.addEventListener('change', function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(event) {
                                bgRender.style.backgroundImage = `url(${event.target.result})`;
                                bgRender.style.setProperty('background-size', `${rangeZoom.value}% auto`, 'important');
                                document.getElementById('cg-position-y-wrapper').style.display = 'block';
                                if (zoomWrapper) zoomWrapper.style.display = 'block';
                            };
                            reader.readAsDataURL(file);
                        }
                    });
                }
            }

            // SIMULADOR DE CAPACIDADE DE LINHAS EM ESCALA HD (#f2)
            let f2Timer = null;
            function simularLinhas() {
                const texto = f2Title.innerText || f2Title.textContent;
                const s1 = '101px', s2 = '80px', s3 = '53px';

                if (!texto.trim()) {
                    f2Title.style.setProperty('font-size', s1, 'important');
                    f2Title.style.setProperty('white-space', 'nowrap', 'important');
                    return;
                }

                const sim = document.createElement('div');
                sim.style.fontFamily = getComputedStyle(f2Title).fontFamily;
                sim.style.fontWeight = "850";
                sim.style.lineHeight = getComputedStyle(f2Title).lineHeight;
                sim.style.position = "absolute";
                sim.style.visibility = "hidden";
                sim.style.width = "866px"; 
                sim.style.textTransform = "uppercase";
                sim.style.whiteSpace = "pre-wrap";
                sim.style.wordBreak = "break-word";
                document.body.appendChild(sim);

                sim.style.fontSize = s1; sim.innerText = "A"; const h1 = sim.clientHeight;
                sim.style.fontSize = s2; sim.innerText = "A"; const h2 = sim.clientHeight;
                sim.style.fontSize = s3; sim.innerText = "A"; const h3 = sim.clientHeight;

                sim.innerText = texto;
                sim.style.fontSize = s1; const l1 = Math.round(sim.scrollHeight / h1);
                sim.style.fontSize = s2; const l2 = Math.round(sim.scrollHeight / h2);

                if (l1 <= 1) {
                    f2Title.style.setProperty('font-size', s1, 'important');
                    f2Title.style.setProperty('white-space', 'nowrap', 'important');
                } else if (l2 <= 2) {
                    f2Title.style.setProperty('font-size', s2, 'important');
                    f2Title.style.setProperty('white-space', (l2 === 2) ? 'pre-wrap' : 'nowrap', 'important');
                } else {
                    f2Title.style.setProperty('font-size', s3, 'important');
                    f2Title.style.setProperty('display', '-webkit-box', 'important');
                    f2Title.style.setProperty('-webkit-line-clamp', '3', 'important');
                    f2Title.style.setProperty('-webkit-box-orient', 'vertical', 'important');
                    f2Title.style.setProperty('overflow', 'hidden', 'important');
                    f2Title.style.setProperty('white-space', 'pre-wrap', 'important');
                }
                document.body.removeChild(sim);
            }

            f2Title.addEventListener('input', () => {
                clearTimeout(f2Timer);
                f2Timer = setTimeout(simularLinhas, 300);
                sincronizarTitulo();
            });

            function verificarVazamentoF4() {
                if (f4Body.scrollHeight > 427) f4Body.classList.add('overflow-warning');
                else f4Body.classList.remove('overflow-warning');
            }

            if (rangeF4 && f4Body) {
                rangeF4.addEventListener('input', function() {
                    valF4.innerText = this.value;
                    f4Body.style.setProperty('font-size', `${this.value}px`, 'important');
                    verificarVazamentoF4();
                });
                f4Body.addEventListener('input', verificarVazamentoF4);
				
				f4Body.addEventListener('paste', function(e) {
					e.preventDefault();
					// Intercepta a colagem e extrai apenas o texto puro, descartando as tags <p> extras
					const text = (e.clipboardData || window.clipboardData).getData('text/plain');
					document.execCommand('insertText', false, text);
				});
            }

            window.addEventListener('load', async () => {
                if (document.fonts) await document.fonts.ready;
                simularLinhas();
                atualizarDataHora();
                verificarVazamentoF4();
                sincronizarTitulo();
            });

            function persistir(exportedImg = null) {
                const titulo = internalTitleInput.value.trim() || gerarTituloComposto();
                const payload = {
                    action: 'cg_save_card_data',
                    card_id: document.getElementById('card-post-id').value,
                    title: titulo,
                    f1: f1Type.innerText,
                    f2: f2Title.innerText,
                    f3: f3Bairro.innerText,
                    f4: f4Body.innerHTML,
                    f4_font_size: rangeF4 ? rangeF4.value : '48',
                    f5_1: dateDisplay.innerHTML,
                    f5_2: document.getElementById('f5_2').innerText,
                    f5_3: document.getElementById('f5_3').innerText,
                    bg_img: bgRender.style.backgroundImage.replace(/^url\(["']?/, '').replace(/["']?\)$/, ''),
                    mask_img: maskSelector.value,
                    overlay_img: overlaySelector.value,
                    bg_position_y: rangeY.value,
                    date_raw: dateInput.value,
                    time_raw: timeInput.value,
                    f5_1_compiled: dateDisplay.innerHTML,
                    f5_1_time_compiled: timeDisplay.innerText,
                    bg_zoom: rangeZoom.value,
                    exported_img: exportedImg || ''
                };

                return fetch('<?php echo esc_url(admin_url('admin-ajax.php')); ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams(payload)
                }).then(r => r.json());
            }

            document.getElementById('cg-btn-save').addEventListener('click', function() {
                const status = document.getElementById('cg-status-message');
                status.style.color = '#007cba';
                status.innerText = 'Salvando rascunho...';

                persistir().then(res => {
                    if (res.success) {
                        status.style.color = '#46b450';
                        status.innerText = '✓ Convite salvo com sucesso!';
                        document.getElementById('card-post-id').value = res.data.card_id;
                    } else {
                        status.style.color = '#cc0000';
                        status.innerText = 'Erro ao salvar rascunho.';
                    }
                });
            });

            document.getElementById('cg-btn-generate').addEventListener('click', async function() {
                const status = document.getElementById('cg-status-message');
                status.style.color = '#007cba';
                status.innerText = 'Renderizando imagem em 1080x1920 nativo...';

                if (document.fonts) await document.fonts.ready;
                const nomeArquivo = (internalTitleInput.value.trim() || gerarTituloComposto()).replace(/[/\\?%*:|"<>]/g, '-') + '.png';

                try {
                    const canvas = await html2canvas(cardContainer, {
                        scale: 1,
                        useCORS: true,
                        allowTaint: true,
                        logging: false,
                        onclone: function(clonedDoc) {
                            clonedDoc.getElementById('card-to-render').style.transform = 'none';
                        }
                    });

                    const dataUrl = canvas.toDataURL('image/png');
                    const res = await persistir(dataUrl);

                    if (res.success) {
                        document.getElementById('card-post-id').value = res.data.card_id;
                        const dl = document.createElement('a');
                        dl.download = nomeArquivo;
                        dl.href = dataUrl;
                        dl.click();

                        status.style.color = '#46b450';
                        status.innerText = '✓ Convite salvo, imagem vinculada e download concluído sem borrões!';
                    } else {
                        throw new Error();
                    }
                } catch (e) {
                    status.style.color = '#cc0000';
                    status.innerText = 'Erro ao gerar ou salvar imagem.';
                }
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }
}

/**
 * 4. CLASSE ORQUESTRADORA DO PLUGIN
 */
class Plugin {
    public static function init(): void {
        add_action('init', [PostType::class, 'register']);
        add_filter('template_include', [PostType::class, 'redirect_single']);
        AjaxHandler::register();
        Shortcodes::register();
    }
}

Plugin::init();