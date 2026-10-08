<?php
if (!defined('ABSPATH')) exit;

// 1. Renderiza os campos na tela de registro
add_action('register_form', 'custom_adicionar_campos_registro');
function custom_adicionar_campos_registro() {
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name  = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $cpf        = isset($_POST['cpf']) ? sanitize_text_field($_POST['cpf']) : '';
    $data_nasc  = isset($_POST['data_nascimento']) ? sanitize_text_field($_POST['data_nascimento']) : '';
    $matricula  = isset($_POST['matricula']) ? sanitize_text_field($_POST['matricula']) : '';
    ?>
    <p>
        <label for="first_name"><?php esc_html_e('Nome', 'text-domain'); ?><br />
            <input type="text" name="first_name" id="first_name" class="input" value="<?php echo esc_attr($first_name); ?>" size="25" required />
        </label>
    </p>
    <p>
        <label for="last_name"><?php esc_html_e('Sobrenome', 'text-domain'); ?><br />
            <input type="text" name="last_name" id="last_name" class="input" value="<?php echo esc_attr($last_name); ?>" size="25" required />
        </label>
    </p>
    <p>
        <label for="cpf"><?php esc_html_e('CPF', 'text-domain'); ?><br />
            <input type="text" name="cpf" id="cpf" class="input" value="<?php echo esc_attr($cpf); ?>" size="25" placeholder="000.000.000-00" required />
        </label>
    </p>
    <p>
        <label for="data_nascimento"><?php esc_html_e('Data de Nascimento', 'text-domain'); ?><br />
            <input type="date" name="data_nascimento" id="data_nascimento" class="input" value="<?php echo esc_attr($data_nasc); ?>" required />
        </label>
    </p>
    <p>
        <label for="matricula"><?php esc_html_e('Matrícula (Opcional)', 'text-domain'); ?><br />
            <input type="text" name="matricula" id="matricula" class="input" value="<?php echo esc_attr($matricula); ?>" size="20" />
        </label>
    </p>
    <?php
}

// 2. Valida se os campos obrigatórios foram preenchidos
add_filter('registration_errors', 'custom_validar_campos_registro', 10, 3);
function custom_validar_campos_registro($errors, $sanitized_user_login, $user_email) {
    if (empty($_POST['first_name'])) {
        $errors->add('first_name_error', __('<strong>ERRO</strong>: Por favor, informe seu Nome.', 'text-domain'));
    }
    if (empty($_POST['last_name'])) {
        $errors->add('last_name_error', __('<strong>ERRO</strong>: Por favor, informe seu Sobrenome.', 'text-domain'));
    }
    if (empty($_POST['cpf'])) {
        $errors->add('cpf_error', __('<strong>ERRO</strong>: Por favor, informe seu CPF.', 'text-domain'));
    }
    if (empty($_POST['data_nascimento'])) {
        $errors->add('data_nasc_error', __('<strong>ERRO</strong>: Por favor, informe sua Data de Nascimento.', 'text-domain'));
    }
    return $errors;
}

// 3. Salva os metadados no perfil do novo usuário
add_action('user_register', 'custom_salvar_campos_registro');
function custom_salvar_campos_registro($user_id) {
    if (!empty($_POST['first_name'])) {
        update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
    }
    if (!empty($_POST['last_name'])) {
        update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['last_name']));
    }
    if (!empty($_POST['cpf'])) {
        $cpf_limpo = preg_replace('/[^0-9]/', '', $_POST['cpf']);
        update_user_meta($user_id, 'cpf', $cpf_limpo);
    }
    if (!empty($_POST['data_nascimento'])) {
        // Formata para YYYY-MM-DD para bater nativamente com o formato do Date Picker do ACF
        $data_formatada = sanitize_text_field($_POST['data_nascimento']);
        update_user_meta($user_id, 'data_nascimento', $data_formatada);
    }
    if (!empty($_POST['matricula'])) {
        $matricula_limpa = preg_replace('/[^0-9]/', '', $_POST['matricula']);
        update_user_meta($user_id, 'matricula', $matricula_limpa);
    }
}