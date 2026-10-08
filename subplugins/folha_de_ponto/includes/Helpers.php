<?php
namespace PMVV_Folha;

if (!defined('ABSPATH')) exit;

class Helpers {
    public static function format_cpf(string $cpf): string {
        $cpf = preg_replace('/\D/', '', $cpf);
        if (strlen($cpf) !== 11) return $cpf;

        return substr($cpf, 0, 3) . '.'
            . substr($cpf, 3, 3) . '.'
            . substr($cpf, 6, 3) . '-'
            . substr($cpf, 9, 2);
    }

    public static function time_to_minutes(?string $hora): ?int {
        if (!$hora) return null;
        $hora = trim($hora);
        if (!preg_match('/^([0-1]?[0-9]|2[0-3]):([0-5][0-9])$/', $hora, $m)) {
            return null;
        }
        return ((int) $m[1] * 60) + (int) $m[2];
    }
}