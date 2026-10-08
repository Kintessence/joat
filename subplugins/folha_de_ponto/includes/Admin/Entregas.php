<?php
namespace PMVV_Folha\Admin;

use PMVV_Folha\Core\Calendar;

if (!defined('ABSPATH')) exit;

class Entregas {

    public static function init() {
        // Exibe o card no topo do Painel Geral (wp-admin/index.php)
        add_action('all_admin_notices', [__CLASS__, 'render_box_painel_principal'], 6);

        // Exibe o card individual no perfil do usuário
        add_action('show_user_profile', [__CLASS__, 'render_box_entregas'], 15);
        add_action('edit_user_profile', [__CLASS__, 'render_box_entregas'], 15);

        // Cria a página de menu consolidada para supervisores
        add_action('admin_menu', [__CLASS__, 'registrar_menu_supervisao']);

        // Ação Ajax para alternar entrega (usada tanto no perfil quanto na matriz geral)
        add_action('wp_ajax_pmvv_toggle_entrega_folha', [__CLASS__, 'ajax_toggle_entrega']);
    }

    public static function pode_supervisionar(?int $user_id = null): bool {
    if (!$user_id) {
		$user_id = get_current_user_id();
	}
		
		if (!$user_id) return false;
		
		// Administradores gerais sempre podem supervisionar
		if (user_can($user_id, 'manage_options')) {
			return true;
		}
		
		// Checa a atribuição individual (via ACF ou get_user_meta)
		$is_supervisor = function_exists('get_field')
			? (bool) get_field('is_supervisor_folha', 'user_' . $user_id)
			: (bool) get_user_meta($user_id, 'is_supervisor_folha', true);
		
		return $is_supervisor;
	}

    public static function get_entregas(int $user_id, int $ano): array {
        $entregas = get_user_meta($user_id, "folhas_entregues_{$ano}", true);
        return is_array($entregas) ? $entregas : [];
    }

    /**
     * Card de Folhas de Ponto no Topo da Tela Inicial (Painel)
     */
    public static function render_box_painel_principal() {
        global $pagenow;
        if ($pagenow !== 'index.php') return;

        $user_id = get_current_user_id();
        if (!$user_id) return;

        $ano_atual = (int) current_time('Y');
        $ano = !empty($_GET['ano_folha']) ? (int) $_GET['ano_folha'] : $ano_atual;

        $meses_disponiveis = Calendar::get_available_months($ano, $user_id);
        $entregas          = self::get_entregas($user_id, $ano);
        $pode_editar       = self::pode_supervisionar();
        $url_folha         = home_url('/folha-ponto/'); // URL da página com o shortcode

        $nomes_meses = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
        ];
        ?>
        <style>
            .pmvv-painel-folhas-card {
                background: #ffffff;
                border: 1px solid #c3c4c7;
                border-left: 4px solid #008a20;
                box-shadow: 0 1px 3px rgba(0,0,0,0.06);
                border-radius: 4px;
                padding: 16px 20px;
                margin: 16px 20px 20px 0;
                display: block !important;
                clear: both;
            }
            .pmvv-painel-folhas-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 1px solid #f0f0f1;
                padding-bottom: 12px;
                margin-bottom: 15px;
            }
            .pmvv-painel-folhas-header h2 {
                margin: 0;
                font-size: 16px;
                font-weight: 600;
                color: #1d2327;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .pmvv-painel-grid-meses {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(68px, 1fr));
                gap: 10px;
                margin: 12px 0 15px 0;
            }
            .pmvv-painel-mes-btn {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 8px 4px;
                border-radius: 6px;
                border: 1px solid #dcdcde;
                font-size: 12px;
                font-weight: 600;
                text-decoration: none !important;
                background: #f6f7f7;
                color: #646970;
                transition: all 0.2s ease;
            }
            .pmvv-painel-mes-btn.entregue {
                background: #e7f6ea;
                border-color: #008a20;
                color: #008a20 !important;
            }
            .pmvv-painel-mes-btn.pendente {
                background: #fcf0f0;
                border-color: #d63638;
                color: #d63638 !important;
            }
            .pmvv-painel-mes-btn.bloqueado {
                opacity: 0.45;
                background: #f0f0f1;
                border-color: #dcdcde;
                cursor: not-allowed;
                pointer-events: none;
            }
            .pmvv-painel-mes-btn:hover:not(.bloqueado) {
                transform: translateY(-2px);
                box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            }
            .pmvv-painel-mes-btn .status-icon {
                font-size: 13px;
                margin-top: 2px;
            }
            .pmvv-painel-folhas-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-top: 1px solid #f0f0f1;
                padding-top: 12px;
                font-size: 12px;
                color: #646970;
                flex-wrap: wrap;
                gap: 10px;
            }
            .pmvv-painel-legenda {
                display: flex;
                align-items: center;
                gap: 14px;
            }
            .pmvv-dot {
                width: 9px;
                height: 9px;
                border-radius: 50%;
                display: inline-block;
                margin-right: 4px;
            }
            .pmvv-dot.entregue { background: #008a20; }
            .pmvv-dot.pendente { background: #d63638; }
            .pmvv-dot.bloqueado { background: #8c8f94; }
        </style>

        <div class="pmvv-painel-folhas-card">
            <div class="pmvv-painel-folhas-header">
                <h2>
                    <span class="dashicons dashicons-calendar-alt" style="color: #008a20;"></span>
                    <?php printf(esc_html__('Minhas Folhas de Ponto — %d', 'pmvv-folha-ponto'), $ano); ?>
                </h2>
                <div>
                    <?php if ($pode_editar): ?>
                        <a href="<?php echo esc_url(admin_url('users.php?page=pmvv-supervisao-folhas')); ?>" class="button button-secondary" style="font-size: 12px; margin-right: 6px;">
                            <span class="dashicons dashicons-list-view" style="font-size: 14px; vertical-align: middle;"></span> <?php esc_html_e('Painel de Supervisão Geral', 'pmvv-folha-ponto'); ?>
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url($url_folha); ?>" target="_blank" class="button button-primary" style="background: #008a20; border-color: #008a20; font-size: 12px;">
                        <span class="dashicons dashicons-printer" style="font-size: 14px; vertical-align: middle;"></span> <?php esc_html_e('Abrir Folha', 'pmvv-folha-ponto'); ?>
                    </a>
                </div>
            </div>

            <div class="pmvv-painel-grid-meses">
                <?php for ($m = 1; $m <= 12; $m++): 
                    $disponivel = in_array($m, $meses_disponiveis, true);
                    $foi_entregue = in_array($m, $entregas, true);
                    $link_mes = add_query_arg(['mes' => $m, 'ano' => $ano], $url_folha);

                    if (!$disponivel) {
                        $classe = 'bloqueado';
                        $icone  = '—';
                        $label  = __('Mês ainda não encerrado', 'pmvv-folha-ponto');
                    } elseif ($foi_entregue) {
                        $classe = 'entregue';
                        $icone  = '✓';
                        $label  = __('Entregue (Clique para visualizar)', 'pmvv-folha-ponto');
                    } else {
                        $classe = 'pendente';
                        $icone  = '✕';
                        $label  = __('Pendente de entrega (Clique para visualizar)', 'pmvv-folha-ponto');
                    }
                ?>
                    <a href="<?php echo $disponivel ? esc_url($link_mes) : '#'; ?>" 
                       target="<?php echo $disponivel ? '_blank' : '_self'; ?>" 
                       class="pmvv-painel-mes-btn <?php echo esc_attr($classe); ?>" 
                       title="<?php echo esc_attr($label); ?>">
                        <span><?php echo esc_html($nomes_meses[$m]); ?></span>
                        <span class="status-icon"><?php echo esc_html($icone); ?></span>
                    </a>
                <?php endfor; ?>
            </div>

            <div class="pmvv-painel-folhas-footer">
                <div class="pmvv-painel-legenda">
                    <div><span class="pmvv-dot entregue"></span> <?php esc_html_e('Entregue', 'pmvv-folha-ponto'); ?></div>
                    <div><span class="pmvv-dot pendente"></span> <?php esc_html_e('Pendente', 'pmvv-folha-ponto'); ?></div>
                    <div><span class="pmvv-dot bloqueado"></span> <?php esc_html_e('Mês em curso / Futuro', 'pmvv-folha-ponto'); ?></div>
                </div>
                <span><?php esc_html_e('Clique no mês liberado para abrir e imprimir o espelho.', 'pmvv-folha-ponto'); ?></span>
            </div>
        </div>
        <?php
    }

    /**
     * Registra o menu no painel WordPress para Supervisores
     */
    public static function registrar_menu_supervisao() {
        if (!self::pode_supervisionar()) return;

        add_users_page(
            __('Supervisão de Folhas', 'pmvv-folha-ponto'),
            __('Supervisão de Folhas', 'pmvv-folha-ponto'),
            'read',
            'pmvv-supervisao-folhas',
            [__CLASS__, 'render_pagina_supervisao']
        );
    }

    /**
     * Renderiza o Painel Geral com todos os servidores (Visão da Chefia/RH)
     */
    public static function render_pagina_supervisao() {
        if (!self::pode_supervisionar()) {
            wp_die(__('Você não tem permissão para acessar esta página.', 'pmvv-folha-ponto'));
        }

        $ano_atual = (int) current_time('Y');
        $ano = !empty($_GET['ano']) ? (int) $_GET['ano'] : $ano_atual;
        $busca = !empty($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

        $args = [
            'number' => 200,
            'orderby' => 'display_name',
            'order' => 'ASC',
        ];
        if ($busca) {
            $args['search'] = '*' . $busca . '*';
            $args['search_columns'] = ['user_login', 'user_nicename', 'display_name', 'user_email'];
        }

        $usuarios = get_users($args);
        $nomes_meses = [1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'];

        wp_nonce_field('pmvv_entrega_nonce', 'pmvv_entrega_nonce_field');
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline">
                <span class="dashicons dashicons-clipboard" style="font-size: 28px; width: 28px; height: 28px; vertical-align: middle;"></span>
                <?php esc_html_e('Supervisão de Entregas de Folhas de Ponto', 'pmvv-folha-ponto'); ?>
            </h1>

            <form method="get" style="margin: 15px 0; display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="page" value="pmvv-supervisao-folhas">
                
                <label for="ano" class="screen-reader-text"><?php esc_html_e('Ano', 'pmvv-folha-ponto'); ?></label>
                <select name="ano" id="ano" onchange="this.form.submit()">
                    <?php for ($y = $ano_atual; $y >= $ano_atual - 3; $y--): ?>
                        <option value="<?php echo $y; ?>" <?php selected($ano, $y); ?>><?php echo $y; ?></option>
                    <?php endfor; ?>
                </select>

                <input type="search" name="s" value="<?php echo esc_attr($busca); ?>" placeholder="<?php esc_attr_e('Buscar servidor...', 'pmvv-folha-ponto'); ?>">
                <button type="submit" class="button"><?php esc_html_e('Filtrar', 'pmvv-folha-ponto'); ?></button>
                <?php if ($busca): ?>
                    <a href="?page=pmvv-supervisao-folhas&ano=<?php echo $ano; ?>" class="button"><?php esc_html_e('Limpar', 'pmvv-folha-ponto'); ?></a>
                <?php endif; ?>
            </form>

            <style>
                .pmvv-tabela-supervisao { border-collapse: collapse; width: 100%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
                .pmvv-tabela-supervisao th { background: #f6f7f7; text-align: center; padding: 8px 4px; font-size: 13px; }
                .pmvv-tabela-supervisao th.col-servidor { text-align: left; padding-left: 12px; width: 260px; }
                .pmvv-tabela-supervisao td { text-align: center; padding: 6px 2px; border-top: 1px solid #f0f0f1; }
                .pmvv-tabela-supervisao td.col-servidor { text-align: left; padding-left: 12px; font-weight: 600; }
                .pmvv-btn-status { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 4px; font-size: 13px; font-weight: bold; cursor: pointer; border: 1px solid transparent; transition: transform 0.1s ease; }
                .pmvv-btn-status:hover { transform: scale(1.15); }
                .pmvv-btn-status.entregue { background: #e7f6ea; color: #008a20; border-color: #008a20; }
                .pmvv-btn-status.pendente { background: #fcf0f0; color: #d63638; border-color: #d63638; }
                .pmvv-btn-status.bloqueado { background: #f0f0f1; color: #a7aaad; cursor: not-allowed; opacity: 0.6; }
                .pmvv-matricula-sub { font-size: 11px; color: #646970; font-weight: normal; }
            </style>

            <table class="wp-list-table widefat fixed striped pmvv-tabela-supervisao">
                <thead>
                    <tr>
                        <th class="col-servidor"><?php esc_html_e('Servidor', 'pmvv-folha-ponto'); ?></th>
                        <?php foreach ($nomes_meses as $num => $nome): ?>
                            <th><?php echo esc_html($nome); ?></th>
                        <?php endforeach; ?>
                        <th style="width: 70px;"><?php esc_html_e('Total', 'pmvv-folha-ponto'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr><td colspan="14" style="text-align: center; padding: 20px;"><?php esc_html_e('Nenhum servidor encontrado.', 'pmvv-folha-ponto'); ?></td></tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $u): 
                            $uid = $u->ID;
                            $meses_disponiveis = Calendar::get_available_months($ano, $uid);
                            $entregas = self::get_entregas($uid, $ano);
                            $matricula = get_user_meta($uid, 'matricula', true);
                            $total_entregues = count(array_intersect($entregas, $meses_disponiveis));
                            $total_disponiveis = count($meses_disponiveis);
                        ?>
                            <tr>
                                <td class="col-servidor">
                                    <a href="<?php echo esc_url(get_edit_user_link($uid)); ?>" target="_blank" style="text-decoration: none; color: #1d2327;">
                                        <?php echo esc_html($u->display_name); ?>
                                    </a>
                                    <?php if ($matricula): ?>
                                        <div class="pmvv-matricula-sub">Matrícula: <?php echo esc_html($matricula); ?></div>
                                    <?php endif; ?>
                                </td>
                                <?php for ($m = 1; $m <= 12; $m++): 
                                    $disp = in_array($m, $meses_disponiveis, true);
                                    $foi = in_array($m, $entregas, true);
                                    
                                    if (!$disp) {
                                        $classe = 'bloqueado'; $label = '—'; $clicavel = false;
                                    } elseif ($foi) {
                                        $classe = 'entregue'; $label = '✓'; $clicavel = true;
                                    } else {
                                        $classe = 'pendente'; $label = '✕'; $clicavel = true;
                                    }
                                ?>
                                    <td>
                                        <span class="pmvv-btn-status <?php echo esc_attr($classe); ?> <?php echo $clicavel ? 'js-toggle-entrega' : ''; ?>"
                                              data-user-id="<?php echo esc_attr($uid); ?>"
                                              data-ano="<?php echo esc_attr($ano); ?>"
                                              data-mes="<?php echo esc_attr($m); ?>"
                                              title="<?php echo $disp ? ($foi ? "Entregue ($nomes_meses[$m])" : "Pendente ($nomes_meses[$m])") : 'Mês não encerrado'; ?>">
                                            <?php echo esc_html($label); ?>
                                        </span>
                                    </td>
                                <?php endfor; ?>
                                <td>
                                    <strong><?php echo esc_html("{$total_entregues}/{$total_disponiveis}"); ?></strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const nonce = document.getElementById('pmvv_entrega_nonce_field')?.value || '';
                
                document.querySelectorAll('.js-toggle-entrega').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const el = this;
                        const userId = el.getAttribute('data-user-id');
                        const ano = el.getAttribute('data-ano');
                        const mes = el.getAttribute('data-mes');

                        el.style.opacity = '0.4';

                        const params = new URLSearchParams({
                            action: 'pmvv_toggle_entrega_folha',
                            user_id: userId,
                            ano: ano,
                            mes: mes,
                            security: nonce
                        });

                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: params.toString()
                        })
                        .then(r => r.json())
                        .then(res => {
                            el.style.opacity = '1';
                            if (res.success) {
                                if (res.data.status === 'entregue') {
                                    el.classList.remove('pendente');
                                    el.classList.add('entregue');
                                    el.innerText = '✓';
                                } else {
                                    el.classList.remove('entregue');
                                    el.classList.add('pendente');
                                    el.innerText = '✕';
                                }
                            } else {
                                alert(res.data || 'Erro ao processar');
                            }
                        })
                        .catch(() => {
                            el.style.opacity = '1';
                            alert('Erro de conexão ao salvar.');
                        });
                    });
                });
            });
        </script>
        <?php
    }

    /**
     * Card Individual no Perfil (wp-admin/profile.php)
     */
    public static function render_box_entregas(\WP_User $user) {
        $user_id   = $user->ID;
        $ano_atual = (int) current_time('Y');
        $ano       = !empty($_GET['ano_folha']) ? (int) $_GET['ano_folha'] : $ano_atual;
        
        $meses_disponiveis = Calendar::get_available_months($ano, $user_id);
        $entregas          = self::get_entregas($user_id, $ano);
        $pode_editar       = self::pode_supervisionar();

        $nomes_meses = [1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr', 5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'];

        wp_nonce_field('pmvv_entrega_nonce', 'pmvv_entrega_nonce_field');
        ?>
        <style>
            .pmvv-entregas-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 8px; padding: 18px 20px; margin: 20px 0; max-width: 850px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
            .pmvv-entregas-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #f0f0f1; padding-bottom: 12px; margin-bottom: 15px; }
            .pmvv-entregas-header h3 { margin: 0; display: flex; align-items: center; gap: 8px; font-size: 15px; }
            .pmvv-grid-meses { display: grid; grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)); gap: 10px; }
            .pmvv-mes-box { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 10px 4px; border-radius: 6px; border: 1px solid #dcdcde; font-size: 12px; font-weight: 600; background: #f6f7f7; color: #646970; }
            .pmvv-mes-box.entregue { background: #e7f6ea; border-color: #008a20; color: #008a20; }
            .pmvv-mes-box.pendente { background: #fcf0f0; border-color: #d63638; color: #d63638; }
            .pmvv-mes-box.bloqueado { opacity: 0.45; background: #f0f0f1; cursor: not-allowed; }
            .pmvv-mes-box.clicavel { cursor: pointer; }
            .pmvv-mes-box.clicavel:hover { transform: translateY(-2px); box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
            .pmvv-legenda-entregas { display: flex; gap: 16px; margin-top: 16px; font-size: 12px; color: #646970; padding-top: 10px; border-top: 1px solid #f0f0f1; }
            .pmvv-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
            .pmvv-dot.entregue { background: #008a20; }
            .pmvv-dot.pendente { background: #d63638; }
            .pmvv-dot.bloqueado { background: #8c8f94; }
        </style>

        <div class="pmvv-entregas-card">
            <div class="pmvv-entregas-header">
                <h3>
                    <span class="dashicons dashicons-clipboard" style="color: #008a20;"></span>
                    <?php printf(esc_html__('Controle de Entrega de Folhas de Ponto — %d', 'pmvv-folha-ponto'), $ano); ?>
                </h3>
                <?php if ($pode_editar): ?>
                    <a href="<?php echo esc_url(admin_url('users.php?page=pmvv-supervisao-folhas')); ?>" class="button button-secondary" style="font-size: 12px;">
                        <span class="dashicons dashicons-list-view" style="font-size: 14px; vertical-align: middle;"></span> <?php esc_html_e('Abrir Painel Geral', 'pmvv-folha-ponto'); ?>
                    </a>
                <?php endif; ?>
            </div>

            <div class="pmvv-grid-meses">
                <?php for ($m = 1; $m <= 12; $m++): 
                    $disp = in_array($m, $meses_disponiveis, true);
                    $foi = in_array($m, $entregas, true);
                    
                    if (!$disp) {
                        $cls = 'bloqueado'; $ic = '—';
                    } elseif ($foi) {
                        $cls = 'entregue'; $ic = '✓';
                    } else {
                        $cls = 'pendente'; $ic = '✕';
                    }
                    $pode_clicar = $pode_editar && $disp;
                ?>
                    <div class="pmvv-mes-box <?php echo esc_attr($cls); ?> <?php echo $pode_clicar ? 'clicavel js-box-perfil' : ''; ?>"
                         data-user-id="<?php echo esc_attr($user_id); ?>"
                         data-ano="<?php echo esc_attr($ano); ?>"
                         data-mes="<?php echo esc_attr($m); ?>"
                         title="<?php echo $disp ? ($foi ? 'Folha entregue' : 'Entrega pendente') : 'Mês não encerrado'; ?>">
                        <span><?php echo esc_html($nomes_meses[$m]); ?></span>
                        <span class="status-icon" style="font-size: 14px; margin-top: 4px;"><?php echo esc_html($ic); ?></span>
                    </div>
                <?php endfor; ?>
            </div>

            <div class="pmvv-legenda-entregas">
                <div><span class="pmvv-dot entregue"></span> <?php esc_html_e('Entregue', 'pmvv-folha-ponto'); ?></div>
                <div><span class="pmvv-dot pendente"></span> <?php esc_html_e('Pendente', 'pmvv-folha-ponto'); ?></div>
                <div><span class="pmvv-dot bloqueado"></span> <?php esc_html_e('Mês em curso / Futuro', 'pmvv-folha-ponto'); ?></div>
            </div>
        </div>

        <?php if ($pode_editar): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const nonce = document.getElementById('pmvv_entrega_nonce_field')?.value || '';
                document.querySelectorAll('.js-box-perfil').forEach(box => {
                    box.addEventListener('click', function() {
                        const el = this;
                        const userId = el.getAttribute('data-user-id');
                        const ano = el.getAttribute('data-ano');
                        const mes = el.getAttribute('data-mes');

                        el.style.opacity = '0.5';

                        const data = new URLSearchParams({
                            action: 'pmvv_toggle_entrega_folha',
                            user_id: userId,
                            ano: ano,
                            mes: mes,
                            security: nonce
                        });

                        fetch(ajaxurl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: data.toString()
                        })
                        .then(r => r.json())
                        .then(res => {
                            el.style.opacity = '1';
                            if (res.success) {
                                if (res.data.status === 'entregue') {
                                    el.classList.remove('pendente');
                                    el.classList.add('entregue');
                                    el.querySelector('.status-icon').innerText = '✓';
                                } else {
                                    el.classList.remove('entregue');
                                    el.classList.add('pendente');
                                    el.querySelector('.status-icon').innerText = '✕';
                                }
                            }
                        })
                        .catch(() => { el.style.opacity = '1'; });
                    });
                });
            });
        </script>
        <?php endif;
    }

    public static function ajax_toggle_entrega() {
        check_ajax_referer('pmvv_entrega_nonce', 'security');

        if (!self::pode_supervisionar()) {
            wp_send_json_error(__('Permissão negada.', 'pmvv-folha-ponto'));
        }

        $user_id = (int) ($_POST['user_id'] ?? 0);
        $ano     = (int) ($_POST['ano'] ?? 0);
        $mes     = (int) ($_POST['mes'] ?? 0);

        if (!$user_id || !$ano || !$mes) {
            wp_send_json_error(__('Dados incompletos.', 'pmvv-folha-ponto'));
        }

        $meta_key = "folhas_entregues_{$ano}";
        $entregas = self::get_entregas($user_id, $ano);

        if (in_array($mes, $entregas, true)) {
            $entregas = array_diff($entregas, [$mes]);
            $status = 'pendente';
        } else {
            $entregas[] = $mes;
            $status = 'entregue';
        }

        update_user_meta($user_id, $meta_key, array_values(array_unique($entregas)));

        wp_send_json_success(['status' => $status]);
    }
}