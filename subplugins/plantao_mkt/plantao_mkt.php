<?php
// Subplugin Gerado: plantao_mkt.php
if (!defined('ABSPATH')) exit;

add_shortcode('plantao_status', function() {

    $status = get_status_plantao();
    if (!$status) return '';

    $plantonistas = get_field('plantonistas', 'option');
    $total = count($plantonistas);

    $indice_atual = $status['indice_atual'];

    $indice_anterior = ($indice_atual - 1 + $total) % $total;
    $indice_proximo  = ($indice_atual + 1) % $total;

    $anterior = $plantonistas[$indice_anterior]['nome'];
    $atual    = $plantonistas[$indice_atual]['nome'];
    $proximo  = $plantonistas[$indice_proximo]['nome'];

    $inicio = $status['inicio']->format('d/m/Y H:i');
    $fim    = $status['fim']->format('d/m/Y H:i');

    ob_start();
    ?>

    <div class="plantao-box">

        <div class="plantao-periodo">
            <strong>Período do plantão</strong><br>
            <?php echo $inicio; ?> → <?php echo $fim; ?>
        </div>

        <ul class="plantao-lista">

            <li class="plantao-anterior">
                <span class="rotulo">Plantão anterior</span>
                <span class="nome"><?php echo esc_html($anterior); ?></span>
            </li>

            <li class="plantao-atual">
                <span class="rotulo">De plantão agora</span>
                <span class="nome"><?php echo esc_html($atual); ?></span>
            </li>

            <li class="plantao-proximo">
                <span class="rotulo">Próximo plantão</span>
                <span class="nome"><?php echo esc_html($proximo); ?></span>
            </li>

        </ul>

    </div>

    <?php
    return ob_get_clean();

});

if (function_exists('acf_add_local_field_group')) {

    acf_add_local_field_group(array(
        'key' => 'group_plantao',
        'title' => 'Plantão',
        'fields' => array(

            array(
                'key' => 'field_plantonistas',
                'label' => 'Sequência de Plantão',
                'name' => 'plantonistas',
                'type' => 'repeater',
                'instructions' => 'Arraste para definir a ordem da fila de plantão.',
                'layout' => 'table',
                'button_label' => 'Adicionar plantonista',
                'collapsed' => 'field_plantonista',
                'min' => 1,
                'sub_fields' => array(

                    array(
                        'key' => 'field_plantonista',
                        'label' => 'Plantonista',
                        'name' => 'plantonista',
                        'type' => 'text',
                        /*'role' => '',
                        'multiple' => 0,
                        'allow_null' => 0,
                        'return_format' => 'array',*/
                    ),

                ),
            ),

        ),

        'location' => array(
            array(
                array(
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'plantao',
                ),
            ),
        ),

        'position' => 'normal',
        'style' => 'default',
        'active' => true,
    ));
}

function get_status_plantao() {
    // 1. Definições do Plantão (Ajuste a DURAÇÃO em horas conforme sua necessidade)
    $duracao_horas = 168; // Exemplo: Troca de plantão a cada 24 horas
    
    // Data/hora base para início da contagem da rotação (ex: 01/01/2025 às 08:00)
    // Ajuste essa data para coincidir com o dia em que a lista começou a rodar!
    $data_inicial = new DateTime('2026-07-31 18:00:00', new DateTimeZone('America/Sao_Paulo'));
    $agora        = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

    // Se ainda não chegou na data inicial, ajusta para não quebrar
    if ($agora < $data_inicial) {
        $agora = clone $data_inicial;
    }

    // 2. Descobrir quantos plantões já se passaram desde a data inicial
    $diferenca_segundos = $agora->getTimestamp() - $data_inicial->getTimestamp();
    $duracao_segundos   = $duracao_horas * 3600;
    
    $numero_plantoes = (int) floor($diferenca_segundos / $duracao_segundos);

    // 3. Pegar o total de plantonistas cadastrados no ACF
    $plantonistas = get_field('plantonistas', 'option');
    $total_plantonistas = is_array($plantonistas) ? count($plantonistas) : 0;

    if ($total_plantonistas === 0) {
        return false;
    }

    // 4. O índice atual é o resto da divisão entre os plantões ocorridos e o total de pessoas na fila
    $indice_atual = $numero_plantoes % $total_plantonistas;

    // 5. Calcular horário de Início e Fim do plantão atual
    $inicio = clone $data_inicial;
    $inicio->modify('+' . ($numero_plantoes * $duracao_horas) . ' hours');

    $fim = clone $inicio;
    $fim->modify('+' . $duracao_horas . ' hours');

    return array(
        'indice_atual' => $indice_atual,
        'inicio'       => $inicio,
        'fim'          => $fim,
    );
}

add_shortcode('plantao', function() {

    if (!function_exists('get_status_plantao')) {
        return '<!-- Erro: A função get_status_plantao() não foi encontrada. -->';
    }

    $status = get_status_plantao();
    if (!$status || empty($status['inicio']) || empty($status['fim'])) {
        return '';
    }

    if (!function_exists('get_field')) {
        return '<!-- Erro: O plugin ACF não está ativo. -->';
    }

    $plantonistas = get_field('plantonistas', 'option');
    if (!is_array($plantonistas) || empty($plantonistas)) {
        return '';
    }

    $total = count($plantonistas);
    if ($total === 0) return '';

    // Cálculos de índice
    $indice_atual = isset($status['indice_atual']) ? (int)$status['indice_atual'] : 0;
    $indice_anterior = ($indice_atual - 1 + $total) % $total;
    $indice_proximo  = ($indice_atual + 1) % $total;

    // Nomes dos plantonistas
    $anterior = isset($plantonistas[$indice_anterior]['plantonista']) ? $plantonistas[$indice_anterior]['plantonista'] : '';
    $atual    = isset($plantonistas[$indice_atual]['plantonista'])    ? $plantonistas[$indice_atual]['plantonista']    : '';
    $proximo  = isset($plantonistas[$indice_proximo]['plantonista'])  ? $plantonistas[$indice_proximo]['plantonista']  : '';

    // Formatação de datas em português
    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'portuguese');
    
    $dt_inicio = $status['inicio'] instanceof DateTimeInterface ? $status['inicio'] : new DateTime();
    $dt_fim    = $status['fim'] instanceof DateTimeInterface    ? $status['fim']    : new DateTime();

    // Dias da semana em português
    $dias_semana = array('domingo', 'segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado');
    
    $dia_inicio = $dias_semana[(int)$dt_inicio->format('w')];
    $dia_fim    = $dias_semana[(int)$dt_fim->format('w')];

    // Texto descritivo exato
    $texto_inicio = sprintf("Das %sh de %s (%s)", $dt_inicio->format('H'), $dia_inicio, $dt_inicio->format('d/m/Y H:i:s'));
    $texto_fim    = sprintf("às %sh de %s (%s)", $dt_fim->format('H'), $dia_fim, $dt_fim->format('d/m/Y H:i:s'));

    ob_start();
    ?>

    <style>
        .plantao-card-container {
            max-width: 380px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #1a1a1a;
        }

        .plantao-card-container .plantao-titulo {
            font-size: 2.2rem;
            font-weight: 800;
            margin: 0 0 10px 0;
            line-height: 1.1;
        }

        .plantao-card-container .plantao-periodo {
            font-size: 0.95rem;
            color: #222;
            line-height: 1.35;
            margin-bottom: 25px;
        }

        .plantao-card-container .plantao-atual-nome {
            font-size: 2.8rem;
            font-weight: 900;
            line-height: 1.05;
            margin: 0 0 25px 0;
            word-break: break-word;
        }

        .plantao-card-container .plantao-outros {
            font-size: 1.45rem;
            line-height: 1.25;
            color: #1a1a1a;
        }

        .plantao-card-container .plantao-outros .rotulo {
            font-weight: 800;
        }

        .plantao-card-container .plantao-outros .nome {
            font-weight: 400;
        }

        .plantao-card-container .divisor {
            margin: 0 4px;
            font-weight: 300;
        }
    </style>

    <div class="plantao-card-container">
        
        <h2 class="plantao-titulo">Plantão</h2>

        <div class="plantao-periodo">
            <?php echo esc_html($texto_inicio); ?><br>
            <?php echo esc_html($texto_fim); ?>
        </div>

        <div class="plantao-atual-nome">
            <?php echo esc_html($atual); ?>
        </div>

        <div class="plantao-outros">
            <span class="rotulo">Anterior:</span> 
            <span class="nome"><?php echo esc_html($anterior); ?></span> 
            <span class="divisor">|</span><br>
            <span class="rotulo">Próximo:</span> 
            <span class="nome"><?php echo esc_html($proximo); ?></span>
        </div>

    </div>

    <?php
    return ob_get_clean();
});