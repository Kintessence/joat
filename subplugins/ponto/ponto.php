<?php
//date_default_timezone_set(get_option('timezone_string'));
function acf_add_local_field_groups() {
	// Cria o grupo de campos
	$group = array(
		'key' => 'campos_ponto_form',
		'title' => 'Campos da folha de ponto',
		'fields' => array(
			array(
				'key' => 'entrada_form',
				'name' => 'entrada',
				'label' => 'Entrada',
				'type' => 'text',
				'wrapper' => array(
					'width' => '',
					'class' => 'campo',
					'id' => '',
				),
			),
			array(
				'key' => 'intervalo_form',
				'name' => 'intervalo',
				'label' => 'Intervalo',
				'type' => 'text',
				'wrapper' => array(
					'width' => '',
					'class' => 'campo',
					'id' => '',
				),
			),
			array(
				'key' => 'retorno_form',
				'name' => 'retorno',
				'label' => 'Retorno',
				'type' => 'text',
				'wrapper' => array(
					'width' => '',
					'class' => 'campo',
					'id' => '',
				),
			),
			array(
				'key' => 'saida_form',
				'name' => 'saida',
				'label' => 'Saída',
				        'type' => 'text',
				'wrapper' => array(
					'width' => '',
					'class' => 'campo',
					'id' => '',
				),
			),
		),
		'location' => array(
			array(
				array(
					'param' => 'af_form',
					'operator' => '==',
					'value' => 'form_ponto',
				),
			),
		),
	);	
	//acf_add_local_field_group($grupo_campos_usuario);
	acf_add_local_field_group($group);
}

add_action( 'acf/init', 'add' );

function add() {
  $group2 = array(
    'key' => 'campos_ponto',
    'title' => 'Registro de ponto',
    'fields' => array(
      array(
        'key' => 'repeater_ponto',
        'label' => '',
        'name' => 'rp',
        'aria-label' => '',
        'type' => 'repeater',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => 0,
        'wrapper' => array(
          'width' => '',
          'class' => '',
          'id' => '',
        ),
        'hide_admin' => 0,
        'layout' => 'block',
        'pagination' => 0,
        'min' => 0,
        'max' => 31,
        'collapsed' => '',
        'button_label' => 'Adicionar',
        'rows_per_page' => '',
        'sub_fields' => array(
          array(
            'key' => 'entrada_ponto',
            'label' => 'Entrada',
            'name' => 'entrada',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '22',
              'class' => '',
              'id' => '',
            ),
            'hide_admin' => 0,
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'parent_repeater' => 'repeater_ponto',
          ),
          array(
            'key' => 'intervalo_ponto',
            'label' => 'Intervalo',
            'name' => 'intervalo',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '22',
              'class' => '',
              'id' => '',
            ),
            'hide_admin' => 0,
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'parent_repeater' => 'repeater_ponto',
          ),
          array(
            'key' => 'retorno_ponto',
            'label' => 'Retorno',
            'name' => 'retorno',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '22',
              'class' => '',
              'id' => '',
            ),
            'hide_admin' => 0,
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'parent_repeater' => 'repeater_ponto',
          ),
          array(
            'key' => 'saida_ponto',
            'label' => 'Saída',
            'name' => 'saida',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '22',
              'class' => '',
              'id' => '',
            ),
            'hide_admin' => 0,
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'parent_repeater' => 'repeater_ponto',
          ),
			array(
            'key' => 'minutos_ponto',
            'label' => 'Minutos',
            'name' => 'minutos',
            'aria-label' => '',
            'type' => 'text',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array(
              'width' => '12',
              'class' => '',
              'id' => '',
            ),
            'hide_admin' => 0,
            'default_value' => '',
            'maxlength' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'parent_repeater' => 'repeater_ponto',
          ),
        ),
      ),
    ),
    'location' => array(
      array(
        array(
          'param' => 'post_type',
          'operator' => '==',
          'value' => 'ponto',
        ),
      ),
    ),
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => '',
    'show_in_rest' => 0,
  );

  acf_add_local_field_group( $group2 );
}

function register_form() {
  $form = array(
    'title' => 'Form title',
    'key' => 'form_ponto',
    'display' => array(
      'description' => '',
      'success_message' => '',
    ),
    'create_entries' => false,
    'restrict_entries' => false,
    'entries_limit' => 0,
    'entries_restriction_message' => '',
  );

  af_register_form( $form );
}
add_action( 'af/register_forms', 'register_form' );

// Shortcode para renderizar a página com formulario de registro de ponto
function sc_pagina_ponto() {
	// Verifica se o usuário está logado
    //if (1==1) {
	//if (is_user_logged_in()) {
		//$current_user = wp_get_current_user();
		//$user_id = $current_user->ID;
		$user_id = get_current_user_id();
		
		$primeiro_nome = esc_html( get_user_meta( $user_id, 'first_name', true ) );
		$sobrenome = esc_html( get_user_meta( $user_id, 'last_name', true ) );
		$nome = ( $primeiro_nome ) ? $primeiro_nome . ' ' . $sobrenome: get_userdata( $user_id )->user_login;
		
		//date_default_timezone_set(get_option('timezone_string'));
		//date_default_timezone_set('Pacific/Kiritimati');
		
		$current_date = date( 'Y-m-d H:i:s' );
		$ano = date( 'Y', strtotime( $current_date ) );
		$mes = date( 'n', strtotime( $current_date ) );
		$hoje = date('j');
		$meses_ext = array( 'janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro' );
		
		$args = array(
			'author' => $user_id,
			'post_type' => 'ponto',
			'post_status' => 'publish',
			'date_query' => array(
				'year' => $ano,
				'month' => $mes,
			),
		);
		
		$query = new WP_Query( $args );
		/* caso não haja CPT ponto, cria um no mes atual */
		if ( !$query->have_posts() ) {
			
			$new_post = array(
				'post_title' => 'Folha de ponto – ' . $meses_ext[ $mes-1 ] . '/' . $ano . ' de ' . $nome,
				'post_type' => 'ponto',
				'post_author' => $user_id,
				'post_status' => 'publish',
			);
			$post_id = wp_insert_post( $new_post );

		}
		
		// Obtém o número de dias no mês vigente
		$dias_no_mes = cal_days_in_month( CAL_GREGORIAN, date( 'n' ), date( 'Y' ) );
		
		/* lista de feriados */
		$feriados = array(
			date( 'Y' ).'-01-01' => 'Confraternização Universal',
			'2024-03-29' => 'Sexta-feira da Paixão',
			date( 'Y' ).'-04-08' => 'Nossa Senhora da Penha',
			date( 'Y' ).'-04-21' => 'Tiradentes',
			date( 'Y' ).'-05-01' => 'Dia do Trabalho',
			date( 'Y' ).'-05-23' => 'Colonização do Solo Espírito-santense',
			date( 'Y' ).'-05-30' => 'Corpus Christi',
			date( 'Y' ).'-09-07' => 'Independência do Brasil',
			date( 'Y' ).'-10-12' => 'Nossa Senhora Aparecida',
			date( 'Y' ).'-11-02' => 'Finados',
			date( 'Y' ).'-11-15' => 'Proclamação da República',
			date( 'Y' ).'-11-20' => 'Dia da Consciência Negra',
			date( 'Y' ).'-12-25' => 'Natal'
		);
		$pontosFacultativos = array(
			'2024-03-28' => 'Sexta-feira da Paixão',
		);
		
		$keys = array('entrada','intervalo','retorno','saida');
		
		// Verifica se há linhas existentes no repeater
		if ( !have_rows( 'repeater_ponto', $post_id ) ) {
			for ( $i = 1; $i <= $dias_no_mes; $i++ ) {
				/* array vazio para o conteudo dos períodos */
				$aDias = array();
				/* gera o dia da semana em número */
				$n_dia_semana = date('N', mktime(0, 0, 0, date('n'), $i, date('Y')));
				/* gera a data formato Y-m-d para verificar se é feriado */
				$data_atual = date('Y-m-d', strtotime(date('Y-m') . "-$i"));
								
				/* seta variável $dia */
				$dia = $n_dia_semana == 6 ? 'sábado' : ($n_dia_semana == 7 ? 'domingo' : (array_key_exists($data_atual, $feriados) ? 'feriado' : (array_key_exists($data_atual, $pontosFacultativos) ? 'ponto facultativo' : '')));
				
				/* preenche $dia */
				if($dia) {
					foreach ($keys as $chave) {
						$aDias[$chave] = $dia;
					}
				}
				
				/* insere as linhas no repeater */
				$row_number = add_row( 'repeater_ponto', $aDias , $post_id );
				
				if ( $row_number !== false ) {
					// Não é necessário definir valores inicialmente, pois os campos serão vazios
				} else {
					// Tratamento de erro se não for possível adicionar a nova linha
				}
			}
			
		} else {
			$ponto_id = get_posts($args)[0]->ID; /* retorna o CPT ponto mais recente criado */
			$repeater_values = get_field('repeater_ponto', $ponto_id);
			
			function filter_submit_button_attributes( $attributes, $form, $args ) {
				$attributes['disabled'] = '';
				return $attributes;
			}
			add_filter( 'af/form/button_attributes/key=form_ponto', 'filter_submit_button_attributes', 10, 3 );
		}
		
		advanced_form( 'form_ponto', array(
			'values' => $repeater_values[$hoje-1],
			//'ajax' => 1,
			'submit_text' => '',
			'filter_mode' => true,
			'honeypot' => false
		));
				
		?>
<div class="lin tac">
	<div class="col f-50">
		<a href="<?php echo get_edit_post_link($ponto_id); ?>"><span class="dashicons dashicons-edit"></span></a>
	</div>
	<div class="col f-50">
		<a href="<?php echo get_permalink($ponto_id); ?>"><span class="dashicons dashicons-visibility"></span></a>
	</div>
</div>
<?php
		ob_start();
?>
<script>
	var botao = document.getElementById('botao_submit');
	botao.disabled = true;
	
	// Função para obter o horário atual no formato HHhMM
    function horaAtual() {
		const agora = new Date();
        const horas = agora.getHours();
        let minutos = agora.getMinutes();
        
        // Se os minutos forem zero, defina como '00' em vez de 0
        minutos = minutos < 10 ? '0' + minutos : minutos;
		
        return `${horas}h${minutos}`;
    }
	
	let arrayEstados = ['sábado','domingo','feriado','ponto facultativo'];
	
	let campos = document.querySelectorAll('.campo');
	
	Array.from(campos).forEach(
		//function(campo, i, array) {
		(campo, index) => {
			const input = campo.querySelector('input');
			const value = input.value.trim();
			
			if (index == 0 && arrayEstados.includes(value.toLowerCase())) {
				campo.style.display = 'block';
				campo.nextElementSibling.style.display = 'noane';
			} else if (value && index <= document.querySelectorAll('.campo').length - 1) {
				campo.style.display = 'none';
				campo.nextElementSibling.style.display = 'block';
			} else if (!value && index < document.querySelectorAll('.campo').length - 1) {
				campo.nextElementSibling.style.display = 'noane';
			}  
			
			campo.addEventListener('click', function() {
				const estados = [horaAtual(), 'FOLGA', 'PLANTÃO', ''];
				if(estados.length < 5 && !['folga', 'plantão', ''].includes(value.toLowerCase())) estados.push(value);
				
				// Obtendo o índice atual do dataset
				let indice = parseInt(campo.dataset.indice) || 0;
				input.value = estados[indice];
				
				campo.dataset.indice = (indice + 1) % estados.length
				botao.disabled = input.value ? false : true;				
			});
		});
</script>

<?php		
		return ob_get_clean();
		
	//}
}

// Registra o shortcode
add_shortcode( 'sc_ponto', 'sc_pagina_ponto' );

/* Funcão após o envio do formulario */
function handle_form_submission( $form, $field, $args ) {
		
	$entrada = af_get_field('entrada');
	$intervalo = af_get_field('intervalo');
	$retorno = af_get_field('retorno');
	$saida = af_get_field('saida');
	
	$valores_iguais = array('feriado', 'FOLGA', 'PLANTÃO', 'ponto facultativo', 'sábado', 'domingo');
	
	if (in_array($entrada, $valores_iguais)){ // && $entrada == $intervalo) {
		
		$intervalo = $retorno = $saida = $entrada;
		/*$intervalo = "";
		$retorno = "";
		$saida = "";
	} else {
		$intervalo = $retorno = $saida = $entrada;*/
	}
	
	// Obter o ID do usuário logado
	$user_id = get_current_user_id();
	
	$dia_vigente = date( 'j' );
	
	// Obter o post "ponto" mais recente criado pelo usuário
	$args = array(
		'post_type' => 'ponto',
		'author' => $user_id,
		'year' => date( 'Y' ), // Ano atual
		'month' => date( 'm' ), // Mês atual
		'order' => 'DESC',
		'posts_per_page' => 1,
	);
	
	$posts = get_posts( $args );
	
	if ($posts) {
		$post = $posts[0];
		$post_id = $post->ID;
		
		// Itera sobre as linhas existentes
		while (have_rows('repeater_ponto', $post_id)) {
			the_row();
			// Obtém o índice da linha atual
			$indice_linha_atual = get_row_index();
			// Verifica se o índice é igual ao dia vigente
			if ($indice_linha_atual == $dia_vigente) {
				// Atualiza os subfields da linha atual
				$novos_valores = array(
					'data' => date( 'j-m' ),
					'entrada_ponto' => $entrada,
					'intervalo_ponto' => $intervalo,
					'retorno_ponto' => $retorno,
					'saida_ponto' => $saida,
				);
				update_row( 'rp', $indice_linha_atual, $novos_valores, $post_id );
				
				// Sai do loop após encontrar a linha correspondente
				break;
			}
		}
	}
}
add_action( 'af/form/submission/key=form_ponto', 'handle_form_submission', 10, 3 );