<?php
namespace PMVV_Folha\Core;

use PMVV_Folha\Helpers;

if (!defined('ABSPATH')) exit;

class User {
    public static function get_data(int $user_id): array {
        $user = get_userdata($user_id);
        $nome = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if (!$nome) $nome = $user->display_name ?? 'Servidor';

        $signature_id = get_field('user_signature', 'user_' . $user_id);

        return [
            'id'           => $user_id,
            'nome'         => $nome,
            'cpf'          => Helpers::format_cpf(get_user_meta($user_id, 'cpf', true) ?: ''),
            'matricula'    => get_user_meta($user_id, 'matricula', true) ?: '',
            'cargo'        => self::get_cargo($user_id),
            'funcao'       => self::get_funcao($user_id),
            'jornada'      => self::get_jornada($user_id),
            'afastamentos' => self::get_afastamentos($user_id),
            'assinatura'   => $signature_id ? wp_get_attachment_image($signature_id, [0, 100]) : '',
        ];
    }

    public static function get_jornada(int $user_id): string {
        $jornada = get_user_meta($user_id, 'jornada', true) ?: get_field('jornada', 'user_' . $user_id);
        $jornada = strtoupper(trim((string)$jornada));

        return in_array($jornada, ['INTEGRAL', 'INTEGRAL_1H', 'MANHA', 'TARDE'], true) ? $jornada : 'INTEGRAL';
    }

    private static function get_cargo(int $user_id): string {
        $cargo = get_user_meta($user_id, 'cargo', true) ?: get_field('cargo', 'user_' . $user_id);
        return $cargo ?: 'Servidor';
    }

    private static function get_funcao(int $user_id): string {
        return get_user_meta($user_id, 'funcao', true) ?: (get_field('funcao', 'user_' . $user_id) ?: '');
    }

    public static function get_afastamentos(int $user_id): array {
        $afastamentos = [];
        if (function_exists('have_rows') && have_rows('ferias_periodos', 'user_' . $user_id)) {
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
}