<?php 

function controles_dio() {
	global $post;

    // Verifica se o post é do tipo 'diario'
    if ($post && $post->post_type === 'diario') {
    ?>
    <style>
		.vermelho {
			color:red;
		}
    </style>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Array de valores de maxlength para os campos textarea
            var maxLengthArray = [0, 1065, 1065, 1000];
			var maxLengthArray2 = [75, 160];

            function adicionarContadoresNaLinha($linha, indice) {
				if($linha.hasClass('acf-clone')) {
					return;
					}
                $linha.find('input[type="text"], textarea').each(function(index) {
					
                    var $campo = $(this);
					
                    // Adiciona contador para todos os campos
                    var $contador = $campo.next('.campo-contador');
					var maxLength = maxLengthArray2[index]; //$campo.attr('maxlength');
					var idealLimit = maxLength;
					var currentLength = $campo.val().length;
					
                    // Se for o segundo textarea, define o maxlength a partir do array
					if ($campo.is('textarea') && index === 2) {
						var idealLimit = maxLengthArray[indice];
						if(indice === 0) {
							$campo.attr('maxlength', maxLength);
						} else {
							$campo.removeAttr('maxlength');
						}						
					} else {
						$campo.removeAttr('maxlength');
					}
					
					$campo.attr('data-ideal-limit', idealLimit);
					
					var remainingCharacters = idealLimit - currentLength;
					
					if (!$contador.length) {
                        $campo.after('<p class="campo-contador"></p>');
                        var $contador = $campo.next('.campo-contador');
                    }
					
					$contador.text(remainingCharacters + ' caracteres restantes');
					
					if (remainingCharacters < 0) {
						$contador.text('Excedeu o limite ideal ('+ idealLimit +') por ' + Math.abs(remainingCharacters) + ' caracteres');
						$contador.addClass("vermelho");
					}
					
					$campo.on('input', function() {
						var $contador = $campo.next('.campo-contador');
						var idealLimit = $campo.attr('data-ideal-limit');
						var currentLength = $campo.val().length;
						var remainingCharacters = idealLimit - currentLength;
						
						$contador.removeClass("vermelho");
						$contador.text(remainingCharacters + ' caracteres restantes');
						
						if (remainingCharacters < 0) {
							$contador.addClass("vermelho");
							$contador.text('Excedeu o limite ideal ('+ idealLimit +') por ' + Math.abs(remainingCharacters) + ' caracteres');
						}
					});
				});
			}
			
			// Função para adicionar contadores em todas as linhas existentes
			function adicionarContadoresEmTodasAsLinhas() {
				$('.acf-table .acf-row').each(function(index) {
					adicionarContadoresNaLinha($(this), index);
				});
			}
			
			// Chama a função ao carregar a página
			adicionarContadoresEmTodasAsLinhas();
			
			$('a[data-event="add-row"]').on('mouseup', function() {
				setTimeout(function() {
					var $novaLinha = $('.acf-table .acf-row').last().prev();
					var indice = $('.acf-table .acf-row').index($novaLinha);
					
					// Copiar valores da linha anterior para a nova linha
					if($novaLinha.hasClass('.acf-clone')) {
						return;
					}
					if(indice === 1){
						var $linhaAnterior = $novaLinha.prev('.acf-row');
						$novaLinha.find('input[type="text"], textarea, input[type="url"], select').each(
						function(index) {
							var $campo = $(this);
							var $campoAnterior = $linhaAnterior.find('input[type="text"], textarea, input[type="url"], select').eq(index);
							$campo.val($campoAnterior.val());
						});
					}
					adicionarContadoresNaLinha($novaLinha, indice);
				}, 0);
			});
		});
    </script>
    <?php
	}
}
//add_action('wp_footer', 'controles_dio');
if (is_admin()) {
	add_action('admin_footer', 'controles_dio');
}

// Função para registrar os campos ACF no WordPress
function registra_campos_acf() {
    if( function_exists('acf_add_local_field_group') ):
        acf_add_local_field_group(array(
            'key' => 'grupo_repeater',
            'title' => 'Repeater de Bloco de Texto',
            'fields' => array(
				array(
                    'key' => 'field_repeater_bloco_texto',
                    'label' => '',
                    'name' => 'repeater_bloco_texto',
                    'type' => 'repeater',
					'layout' => 'block',
					'max' => 4,  // Define o número máximo de linhas desejado
                    'sub_fields' => array(
						array(
							'key' => 'field_secretaria',
                            'label' => 'Secretaria',
                            'name' => 'secretaria',
                            'type' => 'select',
							'choices' => get_choices('acf','secretarias_lista'),
							'wrapper' => array(
								'width' => '33.333%', // Defina a largura desejada, por exemplo, 80%
							),
						),
						array(
							'key' => 'field_texto',
							'label' => 'Texto',
							'name' => 'texto',
							'type' => 'select',
							'choices' => get_choices('acf','assessores_lista'),
							'wrapper' => array(
								'width' => '33.3333%', // Defina a largura desejada, por exemplo, 80%
							),
						),						
						array(
							'key' => 'field_foto',
							'label' => 'Foto',
							'name' => 'foto',
							'type' => 'select',
							'choices' => get_choices('acf','fotografos_lista'),
							'wrapper' => array(
								'width' => '33.3333%', // Defina a largura desejada, por exemplo, 80%
							),
						),
						
						array(
							'key' => 'field_titulo',
							'label' => 'Título',
							'name' => 'titulo',
							'type' => 'text',
							//'maxlength' => 75,
							'wrapper' => array(
								'width' => '50%', // Defina a largura desejada, por exemplo, 80%
							),
						),
						array(
							'key' => 'field_subtitulo',
							'label' => 'Subtítulo',
							'name' => 'subtitulo',
							'type' => 'textarea',
							//'maxlength' => 160,
							'rows' => 2,
							'wrapper' => array(
								'width' => '50%', // Defina a largura desejada, por exemplo, 80%
							),
						),
						array(
							'key' => 'field_materia',
							'label' => 'Matéria',
							'name' => 'materia',
							'type' => 'textarea',
							'rows' => 15,
							'wrapper' => array(
								'width' => '70%', // Defina a largura desejada, por exemplo, 80%
							),
						),
						array(
							'key' => 'field_imagem',
							'label' => 'Imagem',
							'name' => 'imagem',
							'type' => 'file',
							'return_format' => 'array',
							'library' => 'all',
							'wrapper' => array(
								'width' => '30%', // Defina a largura desejada, por exemplo, 80%
							),
						),
						array(
							'key' => 'field_link',
							'label' => 'Link para matéria',
							'name' => 'link',
							'type' => 'url',
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'af_form',
						'operator' => '==',
						'value' => 'form_dio',
					),
				),
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'diario', // Substitua pelo nome do seu tipo de post
					),
				)
			),
			'hide_on_screen' => array(
				0 => 'the_content',
			),
		));
	endif;
}
add_action('acf/init', 'registra_campos_acf');

function register_forms() {
	af_register_form( array(
		'key' => 'form_dio',
		'title' => 'Form Diario',
		'display' => array(
			'description' => '',
			'success_message' => '',
		),
		'create_entries' => false,
		'restrictions' => array(
			'entries' => false,
			'user' => false,
			'schedule' => false,
		),
		'emails' => array(
			array(
				'name' => 'Teste',
				'active' => true,
				'recipient_type' => 'custom',
				'recipient_field' => false,
				'recipient_custom' => 'fbmelo+rwy0uejxxnul2yychy83@boards.trello.com',
				'from' => 'contato@drquem.com.br',
				'subject' => '',
				'content' => '',
			),
		),
	) );
}
add_action( 'af/register_forms', 'register_forms' );

function filter_email_attach( $attachments, $email, $form, $fields ) {
  // Inicialize uma contagem para numerar os anexos
  $count = 1;

  // Obtém os dados do campo 'repeater_bloco_texto'
  $repeater_data = af_get_field( 'repeater_bloco_texto' );

  // Itera sobre cada item no repetidor
  foreach ( $repeater_data as $item ) {
    // Verifica se o campo 'imagem' está presente no item do repetidor
    if ( isset( $item['imagem'] ) ) {
      // Obtém o ID do anexo da imagem
      $attachment_id = $item['imagem']['id'];

      // Obtém o caminho do arquivo da imagem
      $file_path = get_attached_file( $attachment_id );

      // Obtém a extensão original do arquivo
      $file_extension = pathinfo( $file_path, PATHINFO_EXTENSION );

      // Constrói o novo nome do arquivo
      $new_file_name = "Materia_0{$count}.{$file_extension}";

      // Obtém o diretório do arquivo
      $file_directory = pathinfo( $file_path, PATHINFO_DIRNAME );

      // Constrói o caminho completo do novo arquivo
      $new_file_path = $file_directory . '/' . $new_file_name;

      // Renomeia o arquivo
      rename( $file_path, $new_file_path );

      // Adiciona o novo arquivo aos anexos
      $attachments[] = $new_file_path;

      // Incrementa a contagem
      $count++;
    }
  }
	$attachments = array_reverse($attachments);
	
	// Adiciona o arquivo dados.txt aos anexos
    global $path;
    $dados_txt_path = $path . 'dados_dio.txt';
    if (file_exists($dados_txt_path)) {
        $attachments[] = $dados_txt_path;
    }
	
	return $attachments;
}

add_filter( 'af/form/email/attachments/key=form_dio', 'filter_email_attach', 10, 4 );

function filter_email_content( $content, $email, $form, $fields ) {
    // Obtém os dados do campo 'repeater_bloco_texto'
    $repeater_data = af_get_field( 'repeater_bloco_texto' );

    // Arrays para armazenar dados formatados
    $csv = []; // Array para os dados em formato CSV
    $materia_array = []; // Array para os cabeçalhos das matérias
    $iMateria = 1; // Contador para o número da matéria

    // Itera sobre cada item no repeater
    foreach ( $repeater_data as $item ) {
		
		if($iMateria > 1){
			// Adiciona cabeçalho de matéria para todas as matérias, exceto a primeira
			$materia_array[] = '<h3>Matéria pág.'. $iMateria .'</h3>' . $item['materia'];
			$materia_array[] = $item['link'];
		}
		
        // Adiciona os outros valores formatados entre aspas duplas ao array principal
		$csv[] = '"Secretaria de ' . $item['secretaria'] . '"';
        $csv[] = '"' . $item['titulo'] . '"';
        $csv[] = '"' . $item['subtitulo'] . '"';
        $csv[] = '"' . $item['texto'] . '"';
        $csv[] = '"' . $item['foto'] . '"';
		
		$iMateria++;
    }

    // Concatena os valores do array 'materia' separados por <br><br>
    $materia_string = implode('<br><br>', $materia_array);

    // Concatena os valores do array 'csv' separados por vírgula
    $csv_string = implode(",", $csv);
	
	// Cria ou abre o arquivo dados.txt para escrita
    global $path;
	$file_path = $path . 'dados_dio.txt';
    $file = fopen( $file_path, 'w' );
	$dados_dio = '';

    // Escreve o cabeçalho no arquivo
    $header = "secretaria_1, titulo_1, subtitulo_1, texto_1, foto_1, secretaria_2, titulo_2, subtitulo_2, texto_2, foto_2, secretaria_3, titulo_3, subtitulo_3, texto_3, foto_3, secretaria_4, titulo_4, subtitulo_4, texto_4, foto_4\n";
	
	$dados_dio .= $header.$csv_string;
	
	$conteudoANSI = mb_convert_encoding($dados_dio, 'Windows-1252', 'UTF-8');
	
	// Escreve no arquivo
    fwrite($file, $conteudoANSI);
	
	// Fecha o arquivo
	fclose($file);
	
	// Adiciona a string de matérias ao final do conteúdo do e-mail
	$csv_string = '<br><br>'.$csv_string.$materia_string;

    // Retorna o conteúdo do e-mail modificado
    return $csv_string;
}

// Adiciona o filtro à ação de envio do formulário 'form_dio'
add_filter( 'af/form/email/content/key=form_dio', 'filter_email_content', 10, 4 );

function obter_data_formatada() {
    // Define a localização para português do Brasil
    setlocale(LC_TIME, 'pt_BR.utf-8');

    // Verifica se hoje é sexta-feira
    $hoje = date('N');

    // Obtém a data de amanhã. Se hoje for sexta-feira, ajusta a data para segunda-feira	
	$amanha = ($hoje == 5) ? strtotime("next Monday") : strtotime("+1 day");

    // Titulo do e-mail/card com a data no formato desejado
    $data_formatada = "DIO de " . strftime("%e de %B de %Y", $amanha);

    // Restaura a localização para a original
    setlocale(LC_TIME, '');

    return $data_formatada;
}

function filter_email_subject( $subject, $email, $form, $fields ) {
  // Alter the subject line
  $subject = obter_data_formatada();
  return $subject;
}
add_filter( 'af/form/email/subject/key=form_dio', 'filter_email_subject', 10, 4 );

function diario($post_id) {
    if (get_post_type($post_id) == 'diario') {
        // Obtém o título atual do post
        $titulo_atual = get_the_title($post_id);
		
		// Verifica se o título está vazio
        if (empty($titulo_atual)) {
            // A lógica abaixo será executada somente se o título estiver vazio
			$hoje = date('Y-m-d');
			$diasAjuste = (date('N', strtotime($hoje)) == 5) ? '+3 days' : '+1 day';
			$amanha = date('Y-m-d', strtotime($hoje . $diasAjuste));
			$titulo_formatado = "DIO de " . date_i18n('j \d\e F \d\e Y', strtotime($amanha));
			$post_data = array(
				'ID' => $post_id,
				'post_title' => $titulo_formatado,
				'post_name' => sanitize_title($titulo_formatado),
			);
			
			wp_update_post($post_data);
			
			// O código abaixo foi mantido para fins de referência, mas você pode removê-lo se não for necessário
            $post_status = get_post_status($post_id);
		}

        if ($post_status === 'publish') {
		$repeater_data = get_field('repeater_bloco_texto', $post_id);
		$destino_email = 'fbmelo+rwy0uejxxnul2yychy83@boards.trello.com';
		$email_subject = $titulo_formatado;
		$email_message = '';
		$csv = [];
		$csv_header = "secretaria_1, titulo_1, subtitulo_1, texto_1, foto_1, secretaria_2, titulo_2, subtitulo_2, texto_2, foto_2, secretaria_3, titulo_3, subtitulo_3, texto_3, foto_3, secretaria_4, titulo_4, subtitulo_4, texto_4, foto_4";
		$materia_array = [];
		$paginas_array = array("p01", "p02", "p03", "p04");
		$attachments = [];
		
		foreach ($repeater_data as $i => $item) {
			$csv[] = [
				"secretaria_$i" => "Secretaria de " . $item['secretaria'],
				"titulo_$i" => $item['titulo'],
				"subtitulo_$i" => $item['subtitulo'],
				"texto_$i" => $item['texto'],
				"foto_$i" => $item['foto'],
			];
			
			if ($i > 0) {
				$materia_array[] = "Matéria pág. " . ($i + 1) . "\n{$item['materia']}\n{$item['link']}\n\n";
			}
			
			if (isset($item['imagem'])) {
				$attachment_id = attachment_url_to_postid($item['imagem']['url']);
				if ($attachment_id && current_user_can('edit_post', $attachment_id)) {
					$new_attachment_name = 'foto_' . ($i + 1) . '.' . pathinfo($item['imagem']['filename'], PATHINFO_EXTENSION);
					
					// Crie uma cópia renomeada do arquivo
					$copied_attachment_path = wp_get_upload_dir()['path'] . '/' . $new_attachment_name;
					copy(get_attached_file($attachment_id), $copied_attachment_path);
					
					// Adicione a cópia renomeada ao array de anexos
					$attachments[] = $copied_attachment_path;
				}
			}
		}
		
		$csv_string = implode(',', array_map(function($item) {
			return implode(',', array_map(function($subitem) {
				return '"' . $subitem . '"';
			}, $item));
		}, $csv));
		
		$materia_string = implode('', $materia_array);
		
		$email_message = $csv_header . "\r\r" . $csv_string . "\n\n" . $materia_string;
		
		global $path;
		$file_path = $path . 'dados_dio.txt';
		$file = fopen($file_path, 'wt');
		$dados_dio = '';
		$dados_dio .= $csv_header . "\n" . $csv_string;
		
		fwrite($file, $dados_dio);
		fclose($file);
		
		//$attachments = array_reverse($attachments);
		$attachments[] = $file_path;
		
		// Envia o e-mail
		//$result = wp_mail($destino_email, $email_subject, $email_message, '', $attachments);
		wp_mail($destino_email, $email_subject, $email_message, '', $attachments);
		
		/* Adicione esta verificação para exibir informações de depuração
		if ($result) {
		error_log('E-mail enviado com sucesso.');
		} else {
		error_log('Falha ao enviar o e-mail.');
		}*/
		
		// Remova as cópias renomeadas após o envio
		foreach ($attachments as $attachment) {
			unlink($attachment);
		}
		
		// Remova o arquivo temporário
            unlink($file_path);
        }
    }
}

//add_action('acf/save_post', 'diario', 30);

function get_choices($source = 'both', $field = 'secretarias_lista') {
    $choices = [];

    // Opção: Buscar do ACF
    if ($source === 'acf' || $source === 'both') {
        $textarea = get_field($field, 'option');
        
        if ($textarea) {
            $linhas = explode("\n", $textarea);
            foreach ($linhas as $linha) {
                $linha = trim($linha);
                if (!empty($linha)) {
                    $choices[$linha] = $linha;
                }
            }
        }
    }

    // Opção: Buscar do CPT 'secretarias'
    if ($source === 'cpt' || $source === 'both') {
        $query = new WP_Query([
            'post_type'      => 'secretarias',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        if ($secretarias_query->have_posts()) {
            while ($squery->have_posts()) {
                $query->the_post();
                $titulo = get_the_title();
                $choices[$titulo] = $titulo;
            }
            wp_reset_postdata();
        }
    }
    return $choices;
}