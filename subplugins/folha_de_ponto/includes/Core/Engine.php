<?php
namespace PMVV_Folha\Core;

if (!defined('ABSPATH')) exit;

class Engine {
    public static function get_base_schedule(string $jornada): array {
        switch ($jornada) {
            case 'MANHA':
                return ['e1' => 480, 's1' => null, 'e2' => null, 's2' => 780];
            case 'TARDE':
                return ['e1' => 780, 's1' => null, 'e2' => null, 's2' => 1080];
            case 'INTEGRAL_1H':
                return ['e1' => 480, 's1' => 720, 'e2' => 780, 's2' => 1020];
            case 'INTEGRAL':
            default:
                return ['e1' => 480, 's1' => 720, 'e2' => 840, 's2' => 1080];
        }
    }

    public static function generate_day_schedule(string $modo, string $jornada, ?array $base_custom = null): array {
        $base = $base_custom ?? self::get_base_schedule($jornada);
        $evento = self::generate_event($modo);
        $is_continua = ($base['s1'] === null || $base['e2'] === null);

        if (!$is_continua && $evento['tipo'] === 'simples') {
            $base['e1'] += $evento['delta'];
            $base['s2'] += $evento['delta'];
        } elseif (!$is_continua && $evento['tipo'] === 'composto') {
            $base['e1'] += $evento['entrada'];
            $base['e2'] += $evento['almoco'];
            $base['s2'] += ($evento['entrada'] + $evento['almoco']);
        } elseif ($is_continua && $evento['tipo'] === 'simples') {
            $base['e1'] += $evento['delta'];
            $base['s2'] += $evento['delta'];
        }

        return array_map(function ($min) {
            if ($min === null) return '';
            return sprintf('%02dh%02d', intdiv($min, 60), $min % 60);
        }, $base);
    }

    private static function generate_event(string $modo): array {
        switch ($modo) {
            case 'estrito':
                return (rand(1, 100) <= 90) ? ['tipo' => 'nenhum'] : ['tipo' => 'simples', 'delta' => rand(-3, 3)];
            case 'moderado':
                $c = rand(1, 100);
                if ($c <= 40) return ['tipo' => 'nenhum'];
                if ($c <= 85) return ['tipo' => 'simples', 'delta' => rand(-10, 10)];
                return ['tipo' => 'composto', 'entrada' => rand(-5, 5), 'almoco' => rand(5, 15)];
            case 'leve':
            default:
                $c = rand(1, 100);
                if ($c <= 65) return ['tipo' => 'nenhum'];
                if ($c <= 95) return ['tipo' => 'simples', 'delta' => rand(-5, 5)];
                return ['tipo' => 'composto', 'entrada' => rand(-5, 5), 'almoco' => rand(5, 10)];
            case 'fixo':
                return ['tipo' => 'nenhum'];
        }
    }
}