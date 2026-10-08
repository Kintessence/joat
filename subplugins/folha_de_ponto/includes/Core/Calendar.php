<?php
namespace PMVV_Folha\Core;

use PMVV_Folha\Helpers;

if (!defined('ABSPATH')) exit;

class Calendar {
    public static function get_available_months(int $ano, int $user_id): array {
    // Garante a leitura correta do ACF ou User Meta
    $liberacao_individual = function_exists('get_field') 
        ? (bool) get_field('liberar_folha_antecipada', 'user_' . $user_id) 
        : (bool) get_user_meta($user_id, 'liberar_folha_antecipada', true);

    // Se for administrador ou tiver liberação individual no perfil, vê o ano todo
    if (current_user_can('manage_options') || $liberacao_individual) {
        return range(1, 12);
    }

    $meses = [];
    $hoje  = current_time('Y-m-d'); // Ex: '2026-08-17'

    for ($mes = 1; $mes <= 12; $mes++) {
        // Timestamp seguro ao meio-dia para evitar desvios de fuso horário
        $ts_primeiro_dia = strtotime(sprintf('%04d-%02d-01 12:00:00', $ano, $mes));
        
        // Pega a data exata do último dia daquele mês (ex para Agosto: '2026-08-31')
        $ultimo_dia_do_mes = wp_date('Y-m-t', $ts_primeiro_dia);

        // O mês SÓ entra na lista se o último dia dele já ficou no passado em relação a hoje
        if ($ultimo_dia_do_mes < $hoje) {
            $meses[] = $mes;
        }
    }

    return $meses;
}

    public static function parse_acf_textarea(string $raw, int $ano): array {
        $datas = [];
        $linhas = preg_split('/\R/', $raw);

        foreach ($linhas as $linha) {
            $linha = trim($linha);
            if ($linha !== '' && preg_match('/^(\d{2})-(\d{2})/', $linha, $m)) {
                $datas[] = sprintf('%04d-%02d-%02d', $ano, $m[2], $m[1]);
            }
        }
        return $datas;
    }

    public static function get_horarios_especiais(int $ano): array {
        $raw = get_field('horarios_especiais', 'option');
        if (!$raw) return [];

        $jornadas = [];
        $linhas = preg_split('/\R/', $raw);

        foreach ($linhas as $linha) {
            $partes = array_map('trim', explode(',', $linha));
            if (!preg_match('/^(\d{2})-(\d{2})$/', $partes[0], $m)) continue;

            $data_key = sprintf('%04d-%02d-%02d', $ano, $m[2], $m[1]);
            $count = count($partes);

            if ($count >= 3 && $count < 5) {
                $jornadas[$data_key] = [
                    'e1' => Helpers::time_to_minutes($partes[1]), 's1' => null,
                    'e2' => null, 's2' => Helpers::time_to_minutes($partes[2]),
                ];
            } elseif ($count >= 5) {
                $jornadas[$data_key] = [
                    'e1' => Helpers::time_to_minutes($partes[1]), 's1' => Helpers::time_to_minutes($partes[2]),
                    'e2' => Helpers::time_to_minutes($partes[3]), 's2' => Helpers::time_to_minutes($partes[4]),
                ];
            }
        }
        return $jornadas;
    }

    public static function classify_day(string $data, array $feriados, array $pontos, array $afastamentos): ?string {
        foreach ($afastamentos as $afastamento) {
            if ($data >= $afastamento['inicio'] && $data <= $afastamento['fim']) {
                if ($afastamento['tipo'] === 'OUTRO' && !empty($afastamento['descricao'])) {
                    return mb_strtoupper($afastamento['descricao'], 'UTF-8');
                }
                $tipos = [
                    'FERIAS' => 'FÉRIAS', 'LICENCA_MATERNIDADE' => 'LICENÇA-MATERNIDADE',
                    'LICENCA_PATERNIDADE' => 'LICENÇA-PATERNIDADE', 'LICENCA_MEDICA' => 'LICENÇA MÉDICA',
                    'LICENCA_SEM_VENCIMENTO' => 'LICENÇA SEM VENCIMENTO', 'ATESTADO' => 'ATESTADO',
                ];
                return $tipos[$afastamento['tipo']] ?? 'AFASTAMENTO';
            }
        }

        if (in_array($data, $feriados, true)) return 'FERIADO';
        if (in_array($data, $pontos, true)) return 'PONTO FACULTATIVO';

        //$dia_semana = (int) wp_date('N', strtotime($data));
		$timestamp = strtotime($data . ' 12:00:00'); //alteracao
		$dia_semana = (int) date('N', $timestamp); //alteracao
        if ($dia_semana === 6) return 'SÁBADO';
        if ($dia_semana === 7) return 'DOMINGO';

        return null;
    }
}