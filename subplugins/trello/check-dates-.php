<?php

function briefing_parse_acf_date(string $value): ?DateTimeImmutable {

    $value = trim($value);
    if ($value === '') return null;

    // dd/mm/yyyy
    $date = DateTimeImmutable::createFromFormat('d/m/Y', $value);

    if ($date === false) {
        return null;
    }

    return $date;
}

function briefing_get_holidays(): array {

    $raw = get_field('feriados', 'option');
    if (!$raw) return [];

    $lines = preg_split('/\r\n|\r|\n/', $raw);
    $holidays = [];

    foreach ($lines as $line) {
        if (preg_match('/^(\d{2}-\d{2})/', trim($line), $m)) {
            $holidays[] = $m[1]; // MM-DD
        }
    }

    return $holidays;
}

function briefing_is_business_day(DateTimeInterface $date): bool {

    $weekday = (int) $date->format('N'); // 1 = seg, 7 = dom
    if ($weekday >= 6) return false;

    $holidays = briefing_get_holidays();
    $md = $date->format('m-d');

    return !in_array($md, $holidays, true);
}

function briefing_business_hours_until(
    DateTimeInterface $from,
	DateTimeInterface $to
): int {
	
	if ($to <= $from) return 0;
	
    $hours = 0;
    $cursor = DateTimeImmutable::createFromInterface($from);

    while ($cursor < $to) {

        if (briefing_is_business_day($cursor)) {
            $hours++;
        }

        $cursor = $cursor->modify('+1 hour');
    }

    return $hours;
}

function briefing_is_within_72_business_hours(string $acf_date): bool {

    $target = briefing_parse_acf_date($acf_date);
    if (!$target) return false;

    $now = new DateTimeImmutable('now');

    $hours = briefing_business_hours_until($now, $target);

    return $hours <= 72;
}

function briefing_add_business_hours(
	DateTimeInterface $base,
	int $hours = 72
): DateTimeImmutable {
	
	$candidate = DateTimeImmutable::createFromInterface($base);
	
	while (briefing_business_hours_until($base, $candidate) < $hours) {
		$candidate = $candidate->modify('+1 hour');
	}
	
    return $candidate;
}

add_action( 'af/form/validate', 'validate_spec_fields', 10, 2 );
function validate_spec_fields( $form, $args ) {
	
	if ( af_get_field( 'prioridade_demanda' ) == 'Urgente' ) return;

    $campos_para_validar = [
        'campanha_inicio',
        'evento_data',
    ];

    foreach ( $campos_para_validar as $campo ) {
        $nome = af_get_field( $campo, $post_id );

        if (briefing_is_within_72_business_hours($nome)) {
			af_add_error( $campo, 'Mínimo de 72 horas úteis' );
		}
	}
}
