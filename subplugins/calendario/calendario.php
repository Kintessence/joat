<?php
/**
 * Formata CPF.
 */
function cpf(string $cpf): string {
    $cpf = preg_replace('/\D/', '', $cpf);

    if (strlen($cpf) !== 11) {
        return $cpf;
    }

    return substr($cpf, 0, 3) . '.'
        . substr($cpf, 3, 3) . '.'
        . substr($cpf, 6, 3) . '-'
        . substr($cpf, 9, 2);
}

/**
 * Converte o textarea ACF em um array de datas Y-m-d.
 */
function parse_datas_acf(
    string $raw,
    int $ano
): array {
    $datas = [];
    $linhas = preg_split('/\R/', $raw);

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha === '') {
            continue;
        }

        if (
            preg_match(
                '/^(\d{2})-(\d{2})/',
                $linha,
                $m
            )
        ) {
            $datas[] = sprintf(
                '%04d-%02d-%02d',
                $ano,
                $m[2],
                $m[1]
            );
        }
    }

    return $datas;
}

/**
 * Converte hora "HH:MM" em minutos passados do dia.
 */
function hora_para_minutos(?string $hora): ?int {
    if (!$hora) {
        return null;
    }

    $hora = trim($hora);
    if (!preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9])$/', $hora, $m)) {
        return null;
    }

    return ((int) $m[1] * 60) + (int) $m[2];
}

/**
 * Converte o textarea de Horários Especiais em um mapa por data.
 */
function parse_jornadas_especiais_acf(
    string $raw,
    int $ano
): array {
    $jornadas = [];
    $linhas = preg_split('/\R/', $raw);

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha === '') {
            continue;
        }

        $partes = array_map('trim', explode(',', $linha));

        if (!preg_match('/^(\d{2})-(\d{2})$/', $partes[0], $m)) {
            continue;
        }

        $data_key = sprintf('%04d-%02d-%02d', $ano, $m[2], $m[1]);
        $total_partes = count($partes);

        if ($total_partes >= 3 && $total_partes < 5) {
            $jornadas[$data_key] = [
                'e1' => hora_para_minutos($partes[1]),
                's1' => null,
                'e2' => null,
                's2' => hora_para_minutos($partes[2]),
            ];
        } elseif ($total_partes >= 5) {
            $jornadas[$data_key] = [
                'e1' => hora_para_minutos($partes[1]),
                's1' => hora_para_minutos($partes[2]),
                'e2' => hora_para_minutos($partes[3]),
                's2' => hora_para_minutos($partes[4]),
            ];
        }
    }

    return $jornadas;
}

/**
 * Obtém os horários especiais cadastrados no ACF.
 */
function obter_horarios_especiais(int $ano): array {
    $raw = get_field('horarios_especiais', 'option');

    return $raw ? parse_jornadas_especiais_acf($raw, $ano) : [];
}

/**
 * Obtém o cargo do usuário.
 */
function obter_cargo_usuario(int $user_id): string {
    $cargo = get_user_meta($user_id, 'cargo', true);

    if (!$cargo) {
        $cargo = get_field('cargo', 'user_' . $user_id);
    }

    return $cargo ?: 'Servidor';
}

/**
 * Obtém a função exercida pelo usuário.
 */
function obter_funcao_usuario(int $user_id): string {
    $funcao = get_user_meta($user_id, 'funcao', true);

    if (!$funcao) {
        $funcao = get_field('funcao', 'user_' . $user_id);
    }

    return $funcao ?: '';
}

/**
 * Obtém a jornada do usuário.
 */
function obter_jornada_usuario(int $user_id): string {
    $jornada = get_user_meta($user_id, 'jornada', true);

    if (!$jornada) {
        $jornada = get_field('jornada', 'user_' . $user_id);
    }

    $jornada = strtoupper(trim((string)$jornada));

    return in_array($jornada, ['INTEGRAL', 'INTEGRAL_1H', 'MANHA', 'TARDE'], true) ? $jornada : 'INTEGRAL';
}

/**
 * Retorna os horários base conforme a jornada.
 */
function obter_base_horarios(string $jornada): array {
    switch ($jornada) {
        case 'MANHA':
            return [
                'e1' => 480,  // 08h00
                's1' => null,
                'e2' => null,
                's2' => 780,  // 13h00
            ];

        case 'TARDE':
            return [
                'e1' => 780,  // 13h00
                's1' => null,
                'e2' => null,
                's2' => 1080, // 18h00
            ];

        case 'INTEGRAL_1H':
            return [
                'e1' => 480,  // 08h00
                's1' => 720,  // 12h00
                'e2' => 780,  // 13h00
                's2' => 1020, // 17h00
            ];

        case 'INTEGRAL':
        default:
            return [
                'e1' => 480,  // 08h00
                's1' => 720,  // 12h00
                'e2' => 840,  // 14h00
                's2' => 1080, // 18h00
            ];
    }
}

/**
 * Obtém todos os afastamentos cadastrados para o usuário.
 */
function obter_afastamentos_usuario(int $user_id): array {
    $afastamentos = [];

    if (
        function_exists('have_rows')
        && have_rows('ferias_periodos', 'user_' . $user_id)
    ) {
        while (have_rows('ferias_periodos', 'user_' . $user_id)) {
            the_row();

            $afastamentos[] = [
                'tipo'      => get_sub_field('tipo'),
                'inicio'    => get_sub_field('inicio'),
                'fim'       => get_sub_field('fim'),
                'descricao' => get_sub_field('descricao'),
            ];
        }
    }

    return $afastamentos;
}

/**
 * Verifica se a data pertence a algum afastamento.
 */
function obter_afastamento_data(string $data, array $afastamentos): ?string {
    foreach ($afastamentos as $afastamento) {
        if ($data >= $afastamento['inicio'] && $data <= $afastamento['fim']) {
            if ($afastamento['tipo'] === 'OUTRO' && !empty($afastamento['descricao'])) {
                return mb_strtoupper($afastamento['descricao'], 'UTF-8');
            }

            $tipos = [
                'FERIAS'                 => 'FÉRIAS',
                'LICENCA_MATERNIDADE'    => 'LICENÇA-MATERNIDADE',
                'LICENCA_PATERNIDADE'    => 'LICENÇA-PATERNIDADE',
                'LICENCA_MEDICA'         => 'LICENÇA MÉDICA',
                'LICENCA_SEM_VENCIMENTO' => 'LICENÇA SEM VENCIMENTO',
                'ATESTADO'               => 'ATESTADO',
            ];

            return $tipos[$afastamento['tipo']] ?? 'AFASTAMENTO';
        }
    }

    return null;
}

/**
 * Classifica uma data.
 */
function classificar_dia(
    string $data,
    array $feriados,
    array $pontos_facultativos,
    array $afastamentos
): ?string {
    $afastamento = obter_afastamento_data($data, $afastamentos);

    if ($afastamento !== null) {
        return $afastamento;
    }

    if (in_array($data, $feriados, true)) {
        return 'FERIADO';
    }

    if (in_array($data, $pontos_facultativos, true)) {
        return 'PONTO FACULTATIVO';
    }

    $dia_semana = (int) date('N', strtotime($data));

    if ($dia_semana === 6) {
        return 'SÁBADO';
    }

    if ($dia_semana === 7) {
        return 'DOMINGO';
    }

    return null;
}

/**
 * Obtém os feriados cadastrados no ACF.
 */
function obter_feriados(int $ano): array {
    $raw = get_field('feriados', 'option');

    return $raw ? parse_datas_acf($raw, $ano) : [];
}

/**
 * Obtém os pontos facultativos cadastrados no ACF.
 */
function obter_pontos_facultativos(int $ano): array {
    $raw = get_field('pontos_facultativos', 'option');

    return $raw ? parse_datas_acf($raw, $ano) : [];
}

/**
 * Carrega todos os dados necessários para a folha.
 */
function obter_usuario_folha(int $user_id): array {
    $user = get_userdata($user_id);

    $nome = trim($user->first_name . ' ' . $user->last_name);

    if (!$nome) {
        $nome = $user->display_name;
    }

    $signature_id = get_field('user_signature', 'user_' . $user_id);

    return [
        'id'           => $user_id,
        'nome'         => $nome,
        'cpf'          => cpf(get_user_meta($user_id, 'cpf', true)),
        'matricula'    => get_user_meta($user_id, 'matricula', true),
        'cargo'        => obter_cargo_usuario($user_id),
        'funcao'       => obter_funcao_usuario($user_id),
        'jornada'      => obter_jornada_usuario($user_id),
        'afastamentos' => obter_afastamentos_usuario($user_id),
        'assinatura'   => $signature_id
            ? wp_get_attachment_image($signature_id, [0, 100])
            : '',
    ];
}

/**
 * Determina qual usuário será utilizado.
 */
function resolver_usuario_folha(array $atts = []): int {
    return get_current_user_id();
}

/**
 * Retorna os meses cujo último dia já ocorreu.
 */
function meses_fechados_disponiveis(int $ano): array {
    $meses = [];
    $hoje = current_time('Y-m-d');

    for ($mes = 1; $mes <= 12; $mes++) {
        $ultimo_dia = date('Y-m-t', strtotime("$ano-$mes-01"));

        if ($ultimo_dia < $hoje) {
            $meses[] = $mes;
        }
    }

    return $meses;
}

/**
 * Modos de geração dos horários.
 */
function folha_modos_disponiveis(): array {
    return [
        'fixo'     => 'Fixo',
        'leve'     => 'Leve',
        'moderado' => 'Moderado',
        'estrito'  => 'Estrito',
    ];
}

/**
 * Resolve o modo ativo.
 */
function folha_resolver_modo(array $atts): string {
    $modos = folha_modos_disponiveis();

    if (!empty($_GET['modo']) && isset($modos[$_GET['modo']])) {
        return sanitize_key($_GET['modo']);
    }

    if (!empty($atts['modo']) && isset($modos[$atts['modo']])) {
        return sanitize_key($atts['modo']);
    }

    return 'fixo';
}

/**
 * Define se haverá variação nos horários do dia.
 */
function gerar_evento_diario(string $modo): array {
    switch ($modo) {
        case 'fixo':
            return ['tipo' => 'nenhum'];

        case 'estrito':
            $chance = rand(1, 100);

            if ($chance <= 90) {
                return ['tipo' => 'nenhum'];
            }

            return [
                'tipo'  => 'simples',
                'delta' => rand(-3, 3),
            ];

        case 'moderado':
            $chance = rand(1, 100);

            if ($chance <= 40) {
                return ['tipo' => 'nenhum'];
            }

            if ($chance <= 85) {
                return [
                    'tipo'  => 'simples',
                    'delta' => rand(-10, 10),
                ];
            }

            return [
                'tipo'    => 'composto',
                'entrada' => rand(-5, 5),
                'almoco'  => rand(5, 15),
            ];

        case 'leve':
        default:
            $chance = rand(1, 100);

            if ($chance <= 65) {
                return ['tipo' => 'nenhum'];
            }

            if ($chance <= 95) {
                return [
                    'tipo'  => 'simples',
                    'delta' => rand(-5, 5),
                ];
            }

            return [
                'tipo'    => 'composto',
                'entrada' => rand(-5, 5),
                'almoco'  => rand(5, 10),
            ];
    }
}

/**
 * Gera os horários de trabalho. Aceita $base_customizada para dias com horário especial.
 */
function gerar_horarios_dia_util(
    string $modo,
    string $jornada,
    ?array $base_customizada = null
): array {
    $base = $base_customizada ?? obter_base_horarios($jornada);
    $evento = gerar_evento_diario($modo);

    $is_continua = ($base['s1'] === null || $base['e2'] === null);

    if (!$is_continua && $evento['tipo'] === 'simples') {
        $d = $evento['delta'];
        $base['e1'] += $d;
        $base['s2'] += $d;
    }

    if (!$is_continua && $evento['tipo'] === 'composto') {
        $base['e1'] += $evento['entrada'];
        $base['e2'] += $evento['almoco'];
        $base['s2'] += ($evento['entrada'] + $evento['almoco']);
    }

    if ($is_continua && $evento['tipo'] === 'simples') {
        $d = $evento['delta'];
        $base['e1'] += $d;
        $base['s2'] += $d;
    }

    return array_map(
        function ($min) {
            if ($min === null) {
                return '';
            }

            return sprintf(
                '%02dh%02d',
                intdiv($min, 60),
                $min % 60
            );
        },
        $base
    );
}

/**
 * Renderiza uma linha da tabela.
 */
function render_linha_dia(
    int $dia,
    ?string $estado,
    ?array $horarios = null
): string {
    $html = '<tr><td style="text-align:center;">' . $dia . '</td>';

    if ($estado !== null) {
        for ($i = 0; $i < 4; $i++) {
            $html .= '<td style="text-align:center;">' . $estado . '</td>';
        }

        return $html . '</tr>';
    }

    foreach (array_values($horarios) as $hora) {
        $html .= '<td style="text-align:center;">' . ($hora ?: '—') . '</td>';
    }

    return $html . '</tr>';
}

/**
 * Renderiza tabela do empregador.
 */
function render_cabecalho_empregador(string $empregador): string {
    $html = '';
    $html .= '<table class="folha-ponto">';
    $html .= '<tr><td class="legenda"><strong>Empregador(a)</strong></td></tr>';
    $html .= '<tr><td>' . esc_html($empregador) . '</td></tr>';
    $html .= '</table>';

    return $html;
}

/**
 * Renderiza os dados do funcionário.
 */
function render_cabecalho_empregado(array $usuario): string {
    $html = '';
    $html .= '<table class="folha-ponto">';
    $html .= '<tr><td colspan="2" class="legenda"><strong>Empregado(a)</strong></td></tr>';
    $html .= '<tr><td>Nome: ' . esc_html($usuario['nome']) . '</td><td>CPF: ' . esc_html($usuario['cpf']) . '</td></tr>';
    $html .= '<tr><td>Cargo: ' . esc_html($usuario['cargo']) . '</td><td>Matrícula: ' . esc_html($usuario['matricula']) . '</td></tr>';

    if (!empty($usuario['funcao'])) {
        $html .= '<tr><td colspan="2">Função: ' . esc_html($usuario['funcao']) . '</td></tr>';
    }

    $html .= '</table>';

    return $html;
}

/**
 * Início da tabela mensal.
 */
function render_inicio_tabela(): string {
    return '
        <table class="folha-ponto">
            <tr>
                <th width="15">Dia</th>
                <th>Entrada</th>
                <th>Intervalo</th>
                <th>Retorno</th>
                <th>Saída</th>
            </tr>
    ';
}

/**
 * Renderiza a assinatura.
 */
function render_assinatura(array $usuario): string {
    $html = '';
    $html .= '<table class="assinatura">';
    $html .= '<tr>';
    $html .= '<td style="text-align:center;">';
    $html .= $usuario['assinatura'];
    $html .= '<div class="linha"></div>';
    $html .= '<br>';
    $html .= 'ASSINATURA';
    $html .= '</td>';
    $html .= '</tr>';
    $html .= '</table>';

    return $html;
}

/**
 * Título da folha.
 */
function render_titulo_mes(int $ano, int $mes): string {
    return '<h5 align="center"><strong>Folha de ponto – '
        . mb_strtoupper(date_i18n('F / Y', strtotime("$ano-$mes-01")), 'UTF-8')
        . '</strong></h5>';
}

/**
 * Fecha a tabela principal.
 */
function render_fim_tabela(): string {
    return '</table>';
}

/**
 * Resolve o ano da folha.
 */
function resolver_ano_folha(array $atts): int {
    if (!empty($atts['ano'])) {
        return (int) $atts['ano'];
    }

    return (int) current_time('Y');
}

/**
 * Resolve o mês selecionado.
 */
function resolver_mes_folha(int $ano): int {
    $meses = meses_fechados_disponiveis($ano);

    if (!empty($_GET['mes'])) {
        $mes = (int) $_GET['mes'];

        if (in_array($mes, $meses, true)) {
            return $mes;
        }
    }

    return end($meses);
}

/**
 * Renderiza os filtros da folha.
 */
function folha_render_filtros(
    int $ano,
    int $mes_ativo,
    string $modo_ativo
): string {
    $meses = meses_fechados_disponiveis($ano);
    $modos = folha_modos_disponiveis();

    $html = '';
    $html .= '<form method="get" class="folha-filtros">';
    $html .= '<select name="mes">';

    foreach ($meses as $mes) {
        $selected = $mes === $mes_ativo ? 'selected' : '';
        $html .= '<option value="' . $mes . '" ' . $selected . '>';
        $html .= mb_strtoupper(date_i18n('F', strtotime("$ano-$mes-01")), 'UTF-8');
        $html .= '</option>';
    }

    $html .= '</select>';

    if (current_user_can('manage_options')) {
        $html .= '<select name="modo">';

        foreach ($modos as $key => $label) {
            $selected = $key === $modo_ativo ? 'selected' : '';
            $html .= '<option value="' . $key . '" ' . $selected . '>' . $label . '</option>';
        }

        $html .= '</select>';
    }

    $html .= '<button type="submit">Aplicar</button>';
    $html .= '</form>';

    return $html;
}

/**
 * Redireciona usuários não logados.
 */
function exigir_login_folha(): void {
    if (is_user_logged_in()) {
        return;
    }

    wp_redirect(wp_login_url(esc_url_raw($_SERVER['REQUEST_URI'])));
    exit;
}

/**
 * Shortcode principal.
 */
function gerar_folha_ponto($atts): string {
    exigir_login_folha();

    $atts = shortcode_atts(
        [
            'empregador' => 'Prefeitura Municipal de Vila Velha – SEMCOM/PMVV',
            'ano'        => current_time('Y'),
            'modo'       => 'fixo',
        ],
        $atts
    );

    $user_id = resolver_usuario_folha($atts);
    $usuario = obter_usuario_folha($user_id);
    $ano     = resolver_ano_folha($atts);
    $mes     = resolver_mes_folha($ano);
    $modo_ativo = folha_resolver_modo($atts);

    // Calendário e Horários Especiais
    $feriados = obter_feriados($ano);
    $pontos_facultativos = obter_pontos_facultativos($ano);
    $horarios_especiais  = obter_horarios_especiais($ano);

    // ----------------------------------------------------------------------
    // NOVO: Monta o nome do PDF com base no nome do funcionário e no mês filtrado
    // ----------------------------------------------------------------------
    $mes_nome_extenso = mb_strtoupper(date_i18n('F \d\e Y', strtotime("$ano-$mes-01")), 'UTF-8');
    $nome_pdf = "Folha de Ponto de {$usuario['nome']} - {$mes_nome_extenso}";

    $html = '';
    
    // Injeta o script JS de renomear PDF durante a impressão
    $html .= '<script>
        window.addEventListener("beforeprint", function() {
            window._origTitle = document.title;
            document.title = ' . json_encode($nome_pdf) . ';
        });
        window.addEventListener("afterprint", function() {
            if (window._origTitle) document.title = window._origTitle;
        });
    </script>';

    $html .= render_css_folha();
    $html .= folha_render_filtros($ano, $mes, $modo_ativo);
    $html .= render_titulo_mes($ano, $mes);
    $html .= render_cabecalho_empregador($atts['empregador']);
    $html .= render_cabecalho_empregado($usuario);
    $html .= render_inicio_tabela();

    $dias_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano);

    for ($dia = 1; $dia <= $dias_mes; $dia++) {
        $data = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);

        $estado = classificar_dia(
            $data,
            $feriados,
            $pontos_facultativos,
            $usuario['afastamentos']
        );

        if ($estado === null) {
            $base_customizada = $horarios_especiais[$data] ?? null;

            $horarios = gerar_horarios_dia_util(
                $modo_ativo,
                $usuario['jornada'],
                $base_customizada
            );
        } else {
            $horarios = null;
        }

        $html .= render_linha_dia($dia, $estado, $horarios);
    }

    $html .= render_fim_tabela();
    $html .= render_assinatura($usuario);

    return $html;
}

add_shortcode('folha_ponto', 'gerar_folha_ponto');

/**
 * CSS da folha de ponto.
 */
function render_css_folha(): string {
    return <<<CSS
<style>
@media print {
@page {
	font-size: 10pt;
	margin: 12.5mm;
	width: 210mm;
	height: 297mm;
	}
    #meta-inspector-toggle {display:none;}
	body {
	margin: auto !important;
    }
	.entry-content {
	margin: 0 !important;
	}
	.inside-article {
	padding: 0 !important;
	}
	.legenda {
	}
	h5 {
	margin-top: 1em;
	margin-bottom: 1em;
	}
	.linha {
    border-top: .5mm solid #000;
	margin: 24mm 0 0;
	}
	table, td, th {
	border-color: rgba(0, 0, 0, 1);
	}
	th, td {
	padding: 1mm 1mm .5mm;
	line-height: 12pt;
    }
	th:first-child {
	width: 10%;
	}
	.assinatura {
	max-width: 14cm;
	margin: 0 auto;
	border: 0;
	}
	.assinatura td {
	border: 0;
	}
	.pagebreak {
	break-before: page;
	page-break-before: always;
	}
	.entry-content {
	display: block !important;
	}
	nav, .folha-filtros {
	display: none !important;
	}
	.assinatura {
	position: relative;
	}
	.assinatura img {
	position: absolute;
	transform: translateX(-50%);
	}
	}
</style>
CSS;
}

add_filter('acf/validate_value/name=fim', function ($valid, $value, $field, $input) {
    if (!$valid) return $valid;

    $inicio = $_POST['acf'][str_replace('fim', 'inicio', $field['key'])] ?? null;

    if ($inicio && strtotime($value) < strtotime($inicio)) {
        return 'A data final não pode ser anterior à data inicial.';
    }

    return true;
}, 10, 4);

add_action('acf/init', function () {
    if (!function_exists('acf_add_local_field_group')) {
        return;
    }

    acf_add_local_field_group([
        'key' => 'group_ferias_usuario',
        'title' => 'Períodos de Férias',
        'location' => [[['param' => 'user_form', 'operator' => '==', 'value' => 'edit',],],],
        'fields' => [
            [
                'key' => 'field_ferias_periodos',
                'label' => 'Períodos de férias',
                'name' => 'ferias_periodos',
                'type' => 'repeater',
                'layout' => 'table',
                'button_label' => 'Adicionar período',
                'sub_fields' => [
                    [
                        'key' => 'field_tipo',
                        'label' => 'Tipo',
                        'name' => 'tipo',
                        'type' => 'select',
                        'choices' => [
                            'FERIAS' => 'Férias',
                            'LICENCA_MATERNIDADE' => 'Licença-maternidade',
                            'LICENCA_PATERNIDADE' => 'Licença-paternidade',
                            'LICENCA_MEDICA' => 'Licença médica',
                            'LICENCA_SEM_VENCIMENTO' => 'Licença sem vencimento',
                            'ATESTADO' => 'Atestado',
                            'OUTRO' => 'Outro',
                        ],
                        'return_format' => 'value',
                    ],
                    [
                        'key' => 'field_ferias_inicio',
                        'label' => 'Início',
                        'name' => 'inicio',
                        'type' => 'date_picker',
                        'display_format' => 'd/m/Y',
                        'return_format' => 'Y-m-d',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_ferias_fim',
                        'label' => 'Fim',
                        'name' => 'fim',
                        'type' => 'date_picker',
                        'display_format' => 'd/m/Y',
                        'return_format' => 'Y-m-d',
                        'required' => 1,
                    ],
                    [
                        'key' => 'field_descricao',
                        'label' => 'Descrição',
                        'name' => 'descricao',
                        'type' => 'text',
                    ],
                ],
            ],
        ],
    ]);
});