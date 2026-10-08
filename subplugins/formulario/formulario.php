<?php
// Função para registrar os campos ACF programaticamente
function registrar_campos_formulario_contato() {
    
    // Verifica se a função acf_add_local_field_group existe
    if(!function_exists('acf_add_local_field_group')) return;
    
    acf_add_local_field_group(array(
        'key' => 'group_formulario_contato',
        'title' => 'Formulário de Contato - Dados Pessoais',
        'fields' => array(
            // Nome Completo
            array(
                'key' => 'field_nome_completo',
                'label' => 'Nome Completo',
                'name' => 'nome_completo',
                'type' => 'text',
                'required' => 1,
                'wrapper' => array(
                    'width' => '100%',
                ),
            ),
            
            // CPF com validação
            array(
                'key' => 'field_cpf',
                'label' => 'CPF',
                'name' => 'cpf',
                'type' => 'text',
                'required' => 1,
                'wrapper' => array(
                    'width' => '50%',
                ),
                'custom_validation' => true,
            ),
            
            // Data de Nascimento
            array(
                'key' => 'field_data_nascimento',
                'label' => 'Data de Nascimento',
                'name' => 'data_nascimento',
                'type' => 'date_picker',
                'required' => 1,
                'display_format' => 'd/m/Y',
                'return_format' => 'd/m/Y',
                'first_day' => 0,
                'wrapper' => array(
                    'width' => '50%',
                ),
            ),
            
            // Telefone
            array(
                'key' => 'field_telefone',
                'label' => 'Telefone',
                'name' => 'telefone',
                'type' => 'text',
                'required' => 1,
                'wrapper' => array(
                    'width' => '50%',
                ),
            ),
            
            // É Whatsapp?
            array(
                'key' => 'field_whatsapp',
                'label' => 'É Whatsapp?',
                'name' => 'whatsapp',
                'type' => 'radio',
                'required' => 0,
                'choices' => array(
                    'sim' => 'Sim',
                    'nao' => 'Não',
                ),
                'default_value' => 'nao',
                'layout' => 'horizontal',
                'return_format' => 'value',
                'wrapper' => array(
                    'width' => '50%',
                ),
            ),
            
            // Escolaridade
            array(
                'key' => 'field_escolaridade',
                'label' => 'Escolaridade',
                'name' => 'escolaridade',
                'type' => 'select',
                'required' => 1,
                'choices' => array(
                    'fundamental_incompleto' => 'Fundamental Incompleto',
                    'fundamental_completo' => 'Fundamental Completo',
                    'medio_incompleto' => 'Médio Incompleto',
                    'medio_completo' => 'Médio Completo',
                    'superior_incompleto' => 'Superior Incompleto',
                    'superior_completo' => 'Superior Completo',
                    'pos_graduacao' => 'Pós-graduação',
                    'mestrado' => 'Mestrado',
                    'doutorado' => 'Doutorado',
                ),
                'default_value' => 'medio_completo',
                'wrapper' => array(
                    'width' => '100%',
                ),
            ),
            
            // CEP
            array(
                'key' => 'field_cep',
                'label' => 'CEP',
                'name' => 'cep',
                'type' => 'text',
                'required' => 1,
                'wrapper' => array(
                    'width' => '50%',
                ),
                'custom_dynamic' => true,
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'post',
                ),
				
            ),[['param' => 'af_form','operator' => '==','value' => 'form_690ba12297dd5']]
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
    ));
}
add_action('acf/init', 'registrar_campos_formulario_contato');

// Função para validação customizada do CPF
function validar_cpf_formulario($valid, $value, $field, $input) {
    if(!$valid) return $valid;
    
    if($field['key'] === 'field_cpf') {
        // Remove caracteres não numéricos
        $cpf = preg_replace('/[^0-9]/', '', $value);
        
        // Verifica se tem 11 dígitos
        if(strlen($cpf) != 11) {
            $valid = 'CPF deve conter 11 dígitos';
        }
        
        // Verifica se não é uma sequência de números iguais
        if(preg_match('/(\d)\1{10}/', $cpf)) {
            $valid = 'CPF inválido';
        }
        
        // Aqui você pode adicionar a validação completa do CPF
        // com cálculo dos dígitos verificadores
    }
    
    return $valid;
}
add_filter('acf/validate_value/key=field_cpf', 'validar_cpf_formulario', 10, 4);

// Função para buscar endereço via CEP (API ViaCEP)
function buscar_endereco_por_cep() {
    // Esta função pode ser usada com AJAX para preencher automaticamente
    // os campos de endereço quando o CEP for preenchido
    
    $cep = sanitize_text_field($_POST['cep'] ?? '');
    $cep = preg_replace('/[^0-9]/', '', $cep);
    
    if(strlen($cep) === 8) {
        $url = "https://viacep.com.br/ws/{$cep}/json/";
        $response = wp_remote_get($url);
        
        if(!is_wp_error($response)) {
            $data = json_decode(wp_remote_retrieve_body($response), true);
            
            if(!isset($data['erro'])) {
                wp_send_json_success($data);
            }
        }
    }
    
    wp_send_json_error('CEP não encontrado');
}

// Adiciona o endpoint AJAX para buscar CEP
add_action('wp_ajax_buscar_cep', 'buscar_endereco_por_cep');
add_action('wp_ajax_nopriv_buscar_cep', 'buscar_endereco_por_cep');

// Script para funcionalidade dinâmica do CEP
function adicionar_script_cep_dinamico() {
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Busca automática do endereço ao preencher CEP
        $('input[name="acf[field_cep]"]').on('blur', function() {
            var cep = $(this).val().replace(/\D/g, '');
            
            if(cep.length === 8) {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'buscar_cep',
                        cep: cep
                    },
                    success: function(response) {
                        if(response.success) {
                            var data = response.data;
                            console.log('Endereço encontrado:', data);
                            // Aqui você pode preencher automaticamente outros campos
                            // com os dados retornados (logradouro, bairro, cidade, etc.)
                        }
                    }
                });
            }
        });
        
        // Máscaras para os campos
        // Máscara para CPF
        $('input[name="acf[field_cpf]"]').on('input', function() {
            var cpf = $(this).val().replace(/\D/g, '');
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
            cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            $(this).val(cpf);
        });
        
        // Máscara para Telefone
        $('input[name="acf[field_telefone]"]').on('input', function() {
            var telefone = $(this).val().replace(/\D/g, '');
            if(telefone.length === 11) {
                telefone = telefone.replace(/(\d{2})(\d)/, '($1) $2');
                telefone = telefone.replace(/(\d{5})(\d)/, '$1-$2');
            } else if(telefone.length === 10) {
                telefone = telefone.replace(/(\d{2})(\d)/, '($1) $2');
                telefone = telefone.replace(/(\d{4})(\d)/, '$1-$2');
            }
            $(this).val(telefone);
        });
        
        // Máscara para CEP
        $('input[name="acf[field_cep]"]').on('input', function() {
            var cep = $(this).val().replace(/\D/g, '');
            cep = cep.replace(/(\d{5})(\d)/, '$1-$2');
            $(this).val(cep);
        });
    });
    </script>
    <?php
}
add_action('acf/input/admin_footer', 'adicionar_script_cep_dinamico');

function listar_af_entries_shortcode($atts) {
    // 🔧 Parâmetros opcionais (ex: [listar_af_entries form="123"])
    $atts = shortcode_atts(array(
        'form' => '', // ID ou key do formulário específico (se quiser filtrar)
        'limit' => -1, // limite opcional
    ), $atts);

    // 🔍 Query das entradas do tipo af_entry
    $args = array(
        'post_type'      => 'af_entry',
        'posts_per_page' => intval($atts['limit']),
        'post_status'    => 'publish',
		'meta_key'       => 'nome_completo',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
    );

    // Se o formulário for especificado, filtra por meta_key do Advanced Forms
    if (!empty($atts['form'])) {
        $args['meta_query'] = array(
            array(
                'key'   => 'entry_form',
                'value' => $atts['form'],
                //'compare' => '='
            )
        );
    }

    $entries = get_posts($args);

    if (!$entries) {
        return '<p>Nenhuma entrada encontrada.</p>';
    }

    // 🧩 Montar o HTML da lista
    $output = '<ol class="lista-af-entries">';
    foreach ($entries as $entry) {
        $nome_completo = get_field('field_nome_completo', $entry->ID);
        if ($nome_completo) {
            $output .= '<li><a href="' . get_edit_post_link($entry->ID) . '">' . esc_html($nome_completo) . '</a> - <a href="' . get_delete_post_link($entry->ID) . '">Apagar</a></li>' ;
        }
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('listar_af_entries', 'listar_af_entries_shortcode');

