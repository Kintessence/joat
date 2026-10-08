<?php
namespace PMVV_Folha\Frontend;

use PMVV_Folha\Core\User;
use PMVV_Folha\Core\Calendar;
use PMVV_Folha\Core\Engine;

if (!defined('ABSPATH')) exit;

class Shortcode {
    public static function render($atts): string {
        if (!is_user_logged_in()) {
            return sprintf(
                '<p class="folha-aviso">%s <a href="%s">%s</a>.</p>',
                esc_html__('Você precisa estar logado para acessar a folha de ponto.', 'pmvv-folha-ponto'),
                esc_url(wp_login_url(get_permalink())),
                esc_html__('Clique aqui para entrar', 'pmvv-folha-ponto')
            );
        }

        wp_enqueue_style('pmvv-folha-print');

        $atts = shortcode_atts([
            'empregador' => 'Prefeitura Municipal de Vila Velha – SEMCOM/PMVV',
            'ano'        => current_time('Y'),
            'modo'       => 'fixo',
        ], $atts);

        $user_id = get_current_user_id();
        $usuario = User::get_data($user_id);
        $ano     = (int) $atts['ano'];

        $meses = Calendar::get_available_months($ano, $user_id);
        
        // Fallback caso meses esteja vazio (ex: início do ano)
        if (empty($meses)) {
            //$meses = [(int) current_time('m')];
            
			return '<p class="folha-aviso" style="text-align:center; padding: 20px;">' .
				esc_html__('A folha de ponto do mês atual ainda não foi fechada.', 'pmvv-folha-ponto') . 
				'</p>';
		}

        $mes = (!empty($_GET['mes']) && in_array((int)$_GET['mes'], $meses, true)) ? (int)$_GET['mes'] : end($meses);
        $modo_ativo = (current_user_can('manage_options') && !empty($_GET['modo'])) ? sanitize_key($_GET['modo']) : $atts['modo'];

        $feriados  = Calendar::parse_acf_textarea(get_field('feriados', 'option') ?: '', $ano);
        $pontos    = Calendar::parse_acf_textarea(get_field('pontos_facultativos', 'option') ?: '', $ano);
        $especiais = Calendar::get_horarios_especiais($ano);

        $timestamp_mes = strtotime(sprintf('%04d-%02d-01 12:00:00', $ano, $mes));
        $mes_extenso   = mb_strtoupper(wp_date('F \d\e Y', $timestamp_mes), 'UTF-8');
        $nome_pdf      = "Folha de Ponto de {$usuario['nome']} - {$mes_extenso}";

        ob_start();
        ?>
        <style>
            /* Transição suave e feedback de carregamento */
            .folha-ponto-wrapper {
                transition: opacity 0.3s ease, filter 0.3s ease;
            }
            .folha-ponto-wrapper.is-updating {
                opacity: 0.45;
                filter: blur(1px);
                pointer-events: none;
            }
            .folha-filtros {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 18px;
            }
            .folha-status-feedback {
                font-size: 13px;
                color: #008a20;
                font-weight: 500;
                opacity: 0;
                transition: opacity 0.2s ease;
            }
            .folha-status-feedback.visible {
                opacity: 1;
            }
        </style>

        <script>
            window.addEventListener('beforeprint', function() {
                window._origTitle = document.title;
                document.title = <?php echo json_encode($nome_pdf); ?>;
            });
            window.addEventListener('afterprint', function() {
                if (window._origTitle) document.title = window._origTitle;
            });

            document.addEventListener('DOMContentLoaded', function () {
                const form = document.querySelector('.folha-filtros');
                const selects = form ? form.querySelectorAll('select') : [];
                const wrapper = document.querySelector('.folha-ponto-wrapper');
                const feedback = document.querySelector('.folha-status-feedback');
                let debounceTimer = null;

                selects.forEach(function(select) {
                    select.addEventListener('change', function () {
                        // Feedback imediato
                        if (feedback) feedback.classList.add('visible');

                        // Limpa o timer anterior se o usuário mudar de ideia rapidamente
                        clearTimeout(debounceTimer);

                        // Delay de 600ms para confirmar a escolha
                        debounceTimer = setTimeout(function () {
                            if (wrapper) wrapper.classList.add('is-updating');
                            form.submit();
                        }, 600);
                    });
                });
            });
        </script>

        <form method="get" class="folha-filtros">
            <select name="mes" id="folha-select-mes">
                <?php foreach ($meses as $m): ?>
                    <?php $ts_m = strtotime(sprintf('%04d-%02d-01 12:00:00', $ano, $m)); ?>
                    <option value="<?php echo $m; ?>" <?php selected($m, $mes); ?>>
                        <?php echo mb_strtoupper(wp_date('F', $ts_m), 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (current_user_can('manage_options')): ?>
                <select name="modo" id="folha-select-modo">
                    <option value="fixo" <?php selected($modo_ativo, 'fixo'); ?>>Fixo</option>
                    <option value="leve" <?php selected($modo_ativo, 'leve'); ?>>Leve</option>
                    <option value="moderado" <?php selected($modo_ativo, 'moderado'); ?>>Moderado</option>
                    <option value="estrito" <?php selected($modo_ativo, 'estrito'); ?>>Estrito</option>
                </select>
            <?php endif; ?>
            <span class="folha-status-feedback">Carregando...</span>
        </form>

        <div class="folha-ponto-wrapper">
            <h5 align="center"><strong>Folha de ponto – <?php echo mb_strtoupper(wp_date('F / Y', $timestamp_mes), 'UTF-8'); ?></strong></h5>

            <table class="folha-ponto">
                <tr><td class="legenda"><strong>Empregador(a)</strong></td></tr>
                <tr><td><?php echo esc_html($atts['empregador']); ?></td></tr>
            </table>

            <table class="folha-ponto">
                <tr><td colspan="2" class="legenda"><strong>Empregado(a)</strong></td></tr>
                <tr><td>Nome: <?php echo esc_html($usuario['nome']); ?></td><td>CPF: <?php echo esc_html($usuario['cpf']); ?></td></tr>
                <tr><td>Cargo: <?php echo esc_html($usuario['cargo']); ?></td><td>Matrícula: <?php echo esc_html($usuario['matricula']); ?></td></tr>
                <?php if (!empty($usuario['funcao'])): ?>
                    <tr><td colspan="2">Função: <?php echo esc_html($usuario['funcao']); ?></td></tr>
                <?php endif; ?>
            </table>

            <table class="folha-ponto">
                <tr>
                    <th width="15">Dia</th>
                    <th>Entrada</th>
                    <th>Intervalo</th>
                    <th>Retorno</th>
                    <th>Saída</th>
                </tr>
                <?php
                $dias_mes = (int) wp_date('t', $timestamp_mes);

                for ($dia = 1; $dia <= $dias_mes; $dia++) {
                    $data = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
                    $estado = Calendar::classify_day($data, $feriados, $pontos, $usuario['afastamentos']);
                    $horarios = ($estado === null) ? Engine::generate_day_schedule($modo_ativo, $usuario['jornada'], $especiais[$data] ?? null) : null;
                    
                    echo '<tr><td style="text-align:center;">' . $dia . '</td>';
                    if ($estado !== null) {
                        for ($i = 0; $i < 4; $i++) echo '<td style="text-align:center;">' . $estado . '</td>';
                    } else {
                        foreach (array_values($horarios) as $hora) {
                            echo '<td style="text-align:center;">' . ($hora ?: '—') . '</td>';
                        }
                    }
                    echo '</tr>';
                }
                ?>
            </table>

            <table class="assinatura">
                <tr>
                    <td style="text-align:center;">
                        <?php echo $usuario['assinatura']; ?>
                        <div class="linha"></div><br>ASSINATURA
                    </td>
                </tr>
            </table>
        </div>
        <?php
        return ob_get_clean();
    }
}