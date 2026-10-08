<?php
if (!defined('ABSPATH')) exit;

// Hook universal de avisos/conteúdo do topo do painel
add_action('all_admin_notices', 'pmvv_renderizar_box_topo_painel', 5);
function pmvv_renderizar_box_topo_painel() {
    global $pagenow;

    // 1. Garante que só carrega na tela inicial (index.php) para qualquer papel
    if ($pagenow !== 'index.php') {
        return;
    }

    // 2. Garante o carregamento dos Dashicons nativos do WordPress
    wp_enqueue_style('dashicons');

    $current_user = wp_get_current_user();
    if (!$current_user || !$current_user->ID) {
        return;
    }

    // Botão base inicial
    $botoes = array(
        'perfil' => array(
            'titulo'    => __('Meu Perfil', 'gestao-usuarios'),
            'descricao' => __('Dados cadastrais e senha', 'gestao-usuarios'),
            'url'       => admin_url('profile.php'),
            'icone'     => 'dashicons-admin-users',
            'cor'       => '#2271b1',
            'ordem'     => 10,
        )
    );

    /**
     * HOOK DINÂMICO:
     * Subplugins (como Folha de Ponto) injetam botões aqui
     */
    $botoes = apply_filters('painel_colaborador_botoes', $botoes, $current_user);

    // Ordenação dos botões
    uasort($botoes, function($a, $b) {
        return ($a['ordem'] ?? 50) <=> ($b['ordem'] ?? 50);
    });
    ?>

    <style>
        .pmvv-hub-card {
            background: #ffffff;
            border: 1px solid #c3c4c7;
            border-left: 4px solid #2271b1;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            border-radius: 4px;
            padding: 16px 20px;
            margin: 16px 20px 20px 0;
            display: block !important;
            clear: both;
        }
        .pmvv-hub-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #f0f0f1;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .pmvv-hub-header h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #1d2327;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .pmvv-hub-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 14px;
        }
        .pmvv-hub-item {
            display: flex !important;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            background: #f6f7f7;
            border: 1px solid #dcdcde;
            border-radius: 6px;
            text-decoration: none !important;
            color: #1d2327 !important;
            transition: all 0.15s ease-in-out;
            box-sizing: border-box;
        }
        .pmvv-hub-item:hover {
            background: #ffffff;
            border-color: #2271b1;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            transform: translateY(-1px);
        }
        .pmvv-hub-icone {
            display: flex !important;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 6px;
            flex-shrink: 0;
        }
        .pmvv-hub-icone .dashicons {
            font-size: 20px;
            width: 20px;
            height: 20px;
            color: #ffffff;
            line-height: 20px;
        }
        .pmvv-hub-info {
            overflow: hidden;
        }
        .pmvv-hub-titulo {
            font-weight: 600;
            font-size: 13px;
            line-height: 1.3;
            color: #1d2327;
        }
        .pmvv-hub-desc {
            font-size: 11px;
            color: #646970;
            margin-top: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>

    <div class="pmvv-hub-card">
        <div class="pmvv-hub-header">
            <h2>
                <span class="dashicons dashicons-grid-view" style="color: #2271b1;"></span>
                <?php printf(__('Central de Acessos — Olá, %s', 'gestao-usuarios'), esc_html($current_user->display_name)); ?>
            </h2>
            <span style="font-size: 12px; color: #646970;">
                <?php _e('Serviços e ferramentas disponíveis', 'gestao-usuarios'); ?>
            </span>
        </div>

        <div class="pmvv-hub-grid">
            <?php foreach ($botoes as $botao) : 
                $cor   = !empty($botao['cor']) ? $botao['cor'] : '#2271b1';
                $icone = !empty($botao['icone']) ? $botao['icone'] : 'dashicons-arrow-right-alt';
            ?>
                <a href="<?php echo esc_url($botao['url']); ?>" class="pmvv-hub-item">
                    <div class="pmvv-hub-icone" style="background-color: <?php echo esc_attr($cor); ?>;">
                        <span class="dashicons <?php echo esc_attr($icone); ?>"></span>
                    </div>
                    <div class="pmvv-hub-info">
                        <div class="pmvv-hub-titulo"><?php echo esc_html($botao['titulo']); ?></div>
                        <?php if (!empty($botao['descricao'])) : ?>
                            <div class="pmvv-hub-desc" title="<?php echo esc_attr($botao['descricao']); ?>">
                                <?php echo esc_html($botao['descricao']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}