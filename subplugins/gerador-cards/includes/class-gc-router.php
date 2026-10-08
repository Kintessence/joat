<?php
if (!defined('ABSPATH')) exit;

class CG_Router {

    public function __construct() {
        add_filter('template_include', [ $this, 'interceptar_template' ], 999);
        add_action('wp_enqueue_scripts', [ $this, 'enqueue_assets' ]);
    }

    public function is_app_page() {
        return is_page(CG_PAGE_SLUG);
    }

    public function interceptar_template($template) {
        if ($this->is_app_page()) {
            // Renderiza a aplicação diretamente montando o cabeçalho e rodapé do WP
            $this->render_full_page();
            exit; // Interrompe a renderização para evitar conflito com o layout do tema
        }
        return $template;
    }

    public function enqueue_assets() {
        if (!$this->is_app_page()) return;

        $active_template = CG_Templates::get('convite-oficial');

        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css', [], '6.4.2');
        wp_enqueue_style('cg-editor-style', CG_PLUGIN_URL . 'assets/css/card-editor.css', [], CG_VERSION);
        
        if (is_user_logged_in()) {
            wp_enqueue_media();
        }

        wp_enqueue_script('html2canvas', 'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js', [], '1.4.1', true);
        wp_enqueue_script('cg-engine-js', CG_PLUGIN_URL . 'assets/js/card-engine.js', [ 'jquery', 'html2canvas' ], CG_VERSION, true);

        wp_localize_script('cg-engine-js', 'CG_ENV', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'app_url'  => home_url('/' . CG_PAGE_SLUG . '/'),
            'f2_config' => $active_template['field_2_config']
        ]);
    }

    private function render_full_page() {
        get_header();
        
        $view = isset($_GET['view']) ? sanitize_text_field($_GET['view']) : 'dashboard';

        echo '<div class="cg-app-wrapper" style="padding: 40px 0; max-width: 1200px; margin: 0 auto; min-height: 600px;">';
        if ($view === 'editor') {
            $this->render_editor();
        } else {
            $this->render_dashboard();
        }
        echo '</div>';

        get_footer();
    }

    private function render_dashboard() {
        $query = new WP_Query([
            'post_type'      => 'saved_cards',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ]);
        ?>
        <div class="cg-gestao-container">
            <div class="cg-gestao-header">
                <h2>Central de Convites Salvos</h2>
                <a href="<?php echo esc_url(home_url('/' . CG_PAGE_SLUG . '/?view=editor&card_id=0')); ?>" class="cg-btn-add-new">
                    <span class="dashicons dashicons-plus-alt"></span> Criar Novo Card
                </a>
            </div>
            <div class="cg-grid-cards">
                <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); 
                    $c_id = get_the_ID();
                    $thumb = get_post_meta($c_id, '_cg_bg_img', true) ?: 'https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/bg.png';
                    $edit_link = home_url('/' . CG_PAGE_SLUG . '/?view=editor&card_id=' . $c_id);
                ?>
                    <div class="cg-card-item">
                        <div class="cg-card-thumb" style="background-image: url('<?php echo esc_url($thumb); ?>');"></div>
                        <div class="cg-card-meta">
                            <h4><?php the_title(); ?></h4>
                            <div class="cg-card-actions">
                                <a href="<?php echo esc_url($edit_link); ?>" class="cg-btn-action cg-btn-edit">
                                    <span class="dashicons dashicons-edit"></span> Editar
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); else : ?>
                    <p style="grid-column: 1/-1; text-align: center; color: #666; padding: 40px 0;">Nenhum card foi criado ainda. Comece clicando no botão acima!</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_editor() {
        $card_id = isset($_GET['card_id']) ? intval($_GET['card_id']) : 0;
        $is_new = ($card_id === 0);

        $template_data = CG_Templates::get('convite-oficial');

        $field_1 = !$is_new ? get_post_meta($card_id, '_cg_field_1', true) : 'cole ou digite o tema aqui';
        $field_2 = !$is_new ? get_post_meta($card_id, '_cg_field_2', true) : 'cole ou digite o título neste campo';
        $field_3 = !$is_new ? get_post_meta($card_id, '_cg_field_3', true) : 'nome do bairro aqui';
        $field_4 = !$is_new ? get_post_field('post_content', $card_id) : 'Texto descritivo do evento';
        $field_5_1 = !$is_new ? get_post_meta($card_id, '_cg_field_5_1', true) : 'D/M dia-semana';
        $field_5_1_time = !$is_new ? get_post_meta($card_id, '_cg_field_5_1_time', true) : '16h';
        $field_5_2 = !$is_new ? get_post_meta($card_id, '_cg_field_5_2', true) : 'Endereço, n';
        $field_5_3 = !$is_new ? get_post_meta($card_id, '_cg_field_5_3', true) : 'Bairro';

        $bg_img = !$is_new ? get_post_meta($card_id, '_cg_bg_img', true) : '';
        $mask_img = !$is_new ? get_post_meta($card_id, '_cg_mask_img', true) : $template_data['masks'][0]['url'];
        $bg_position_y = !$is_new ? get_post_meta($card_id, '_cg_bg_position_y', true) : '0';
        $bg_zoom = !$is_new ? get_post_meta($card_id, '_cg_bg_zoom', true) : '100';
        ?>
        <div style="margin-bottom: 20px;">
            <a href="<?php echo esc_url(home_url('/' . CG_PAGE_SLUG . '/')); ?>" style="text-decoration: none; color: #06305d; font-weight: bold; display: inline-flex; align-items: center; gap: 5px;">
                <span class="dashicons dashicons-arrow-left-alt2"></span> Voltar para a Central de Gestão
            </a>
        </div>

        <div class="cg-main-wrapper">
            <div class="cg-sidebar">
                <h3>Painel Interativo</h3>
                <input type="hidden" id="card-post-id" value="<?php echo $card_id; ?>">

                <div class="cg-control-group">
                    <label>Título Interno do Registro:</label>
                    <input type="text" id="card-internal-title" value="<?php echo !$is_new ? get_the_title($card_id) : 'Card Gerado em ' . date_i18n('d/m/Y H:i'); ?>">
                </div>

                <div class="cg-control-group">
                    <label>Tipo de Convite (Campo 1):</label>
                    <select id="cg-convite-selector">
                        <option value="">Selecione um tipo...</option>
                        <option value="INAUGURAÇÃO">INAUGURAÇÃO</option>
                        <option value="ORDEM DE SERVIÇO">ORDEM DE SERVIÇO</option>
                        <option value="ENTREGA">ENTREGA</option>
                        <option value="APRESENTAÇÃO DE PROJETO">APRESENTAÇÃO DE PROJETO</option>
                        <option value="outro">OUTRO (Digitar livremente)</option>
                    </select>
                    <input type="text" id="cg-convite-custom" style="display:none; margin-top: 8px;" placeholder="Escreva o tipo do convite...">
                </div>

                <div class="cg-control-group">
                    <label>Selecione o Bairro:</label>
                    <select id="cg-bairro-selector">
                        <option value="">Selecione um bairro...</option>
                        <?php 
                        if (function_exists('btt_get_choices')) {
                            $bairros = btt_get_choices('acf', 'bairros_vv');
                            if (!empty($bairros)) {
                                foreach ($bairros as $b) {
                                    $selected = ($field_3 === $b) ? 'selected' : '';
                                    echo '<option value="' . esc_attr($b) . '" ' . $selected . '>' . esc_html($b) . '</option>';
                                }
                            }
                        }
                        ?>
                    </select>
                </div>

                <div class="cg-control-group">
                    <label>Modelo (Máscara):</label>
                    <select id="cg-mask-selector">
                        <?php foreach ($template_data['masks'] as $m) : ?>
                            <option value="<?php echo esc_url($m['url']); ?>" <?php selected($mask_img, $m['url']); ?>>
                                <?php echo esc_html($m['label']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="cg-control-group">
                    <label>Data do Evento:</label>
                    <input type="date" id="cg-date-input" value="<?php echo ($card_id > 0) ? get_post_meta($card_id, '_cg_field_5_1_raw', true) : ''; ?>">
                </div>

                <div class="cg-control-group">
                    <label>Horário do Evento:</label>
                    <input type="time" id="time-input" value="<?php echo ($card_id > 0) ? get_post_meta($card_id, '_cg_field_5_1_time_raw', true) : ''; ?>">
                </div>

                <div class="cg-control-group">
                    <label>Foto do Card (Fundo):</label>
                    <?php if (is_user_logged_in()) : ?>
                        <button class="cg-btn cg-btn-media" id="cg-media-uploader"><span class="dashicons dashicons-format-image"></span> Escolher Imagem (Biblioteca)</button>
                        <input type="hidden" id="cg-uploader-type" value="wp_media">
                    <?php else : ?>
                        <input type="file" id="cg-local-uploader" accept="image/*" style="width:100%; padding:5px; border:1px solid #ccc; border-radius:4px;">
                        <input type="hidden" id="cg-uploader-type" value="local">
                    <?php endif; ?>
                </div>

                <div class="cg-control-group" id="cg-position-y-wrapper" style="<?php echo $bg_img ? 'display:block;' : 'display:none;'; ?>">
                    <label>Posicionamento Vertical (Eixo Y): <span id="cg-val-y"><?php echo esc_html($bg_position_y); ?></span>px</label>
                    <input type="range" id="cg-range-y" min="-400" max="400" value="<?php echo esc_attr($bg_position_y); ?>" step="1">
                </div>

                <div class="cg-control-group" id="cg-zoom-wrapper" style="<?php echo $bg_img ? 'display:block;' : 'display:none;'; ?>">
                    <label>Zoom da Imagem: <span id="cg-val-zoom"><?php echo esc_html($bg_zoom); ?></span>%</label>
                    <input type="range" id="cg-range-zoom" min="100" max="250" value="<?php echo esc_attr($bg_zoom); ?>" step="1">
                </div>

                <button class="cg-btn cg-btn-save <?php echo $is_new ? 'disabled-gray' : ''; ?>" id="cg-btn-save" <?php echo $is_new ? 'disabled' : ''; ?>>
                    <span class="dashicons dashicons-cloud-upload"></span> Salvar Post
                </button>
                <button class="cg-btn cg-btn-generate <?php echo empty($bg_img) ? 'disabled-gray' : ''; ?>" id="cg-btn-generate" <?php echo empty($bg_img) ? 'disabled' : ''; ?>>
                    <span class="dashicons dashicons-download"></span> Baixar Imagem (1080x1920)
                </button>

                <div id="cg-status-message" style="margin-top:10px; font-weight:bold; font-size:13px; text-align:center;"></div>
            </div>

            <div class="cg-preview-zone">
                <div id="card-to-render" class="card-container">
                    <div class="card-bg" id="card-bg-render" style="background-image: url('<?php echo esc_url($bg_img); ?>'); background-position: center <?php echo esc_attr($bg_position_y); ?>px; background-size: <?php echo esc_attr($bg_zoom); ?>% auto !important;"></div>
                    <div class="card-mask" id="card-mask-render" style="background-image: url('<?php echo esc_url($mask_img); ?>');"></div>

                    <div class="card-content-flow">
                        <div class="field-1 <?php echo $field_1 ? 'convite-definido' : ''; ?>" id="f1" contenteditable="true"><?php echo esc_html($field_1); ?></div>
                        <div class="field-2" id="f2" contenteditable="true"><?php echo esc_html($field_2); ?></div>
                        <div class="field-3 <?php echo ($field_3 && $field_3 !== 'nome do bairro aqui') ? 'bairro-definido' : ''; ?>" id="f3" contenteditable="true"><?php echo esc_html($field_3); ?></div>
                        <div class="field-4" id="f4" contenteditable="true"><p><?php echo wp_kses_post($field_4); ?></p></div>

                        <div class="field-5" id="f5">
                            <div class="field-5-row">
                                <i class="fa-solid fa-calendar-days"></i>
                                <span id="f5_1_date_display"><?php echo wp_kses_post($field_5_1); ?></span>
                                <i class="fa-solid fa-clock" style="margin-left: 10px;"></i>
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
        <?php
    }
}