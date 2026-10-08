<?php
if (!defined('ABSPATH')) exit;

class CG_Templates {

    /**
     * Retorna o catálogo de presets registrados no plugin.
     */
    public static function get_all() {
        return [
            'convite-oficial' => [
                'id'            => 'convite-oficial',
                'name'          => 'Convite Oficial PMVV',
                'aspect_ratio'  => '9:16',
                'export_width'  => 1080,
                'export_height' => 1920,
                'masks'         => [
                    [
                        'label' => 'Prefeitura',
                        'url'   => 'https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/bg_pmvv.png'
                    ],
                    [
                        'label' => 'Parceria com Governo',
                        'url'   => 'https://intra-semcom.vilavelha.es.gov.br/wp-content/uploads/bg_gov.png'
                    ],
                    [
                        'label' => 'Sem máscara (Limpo)',
                        'url'   => ''
                    ]
                ],
                'field_2_config' => [
                    'max_chars'  => 32,
                    'util_width' => 325,
                    'max_font'   => 38,
                    'min_font'   => 30
                ]
            ]
        ];
    }

    /**
     * Obtém um template específico. Fallback para 'convite-oficial'.
     */
    public static function get($template_id = 'convite-oficial') {
        $templates = self::get_all();
        return isset($templates[$template_id]) ? $templates[$template_id] : $templates['convite-oficial'];
    }
}