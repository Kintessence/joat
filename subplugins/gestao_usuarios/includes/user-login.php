<?php
if (!defined('ABSPATH')) exit;

add_action('login_form', 'custom_add_login_field');
function custom_add_login_field() {
    $matricula = isset($_POST['matricula']) ? sanitize_text_field($_POST['matricula']) : '';
    ?>
    <p class="login-matricula">
        <label for="matricula"><?php esc_html_e('Matrícula', 'text-domain'); ?><br />
            <input type="text" name="matricula" id="matricula" class="input" value="<?php echo esc_attr($matricula); ?>" size="20" placeholder="" />
        </label>
    </p>
    <?php
}

add_filter('authenticate', 'custom_validate_and_check_matricula', 30, 3);
function custom_validate_and_check_matricula($user, $username, $password) {
    if (is_wp_error($user) || empty($username)) {
        return $user;
    }

    $matricula_cadastrada = get_user_meta($user->ID, 'matricula', true);
    $matricula_cadastrada_limpa = preg_replace('/[^0-9]/', '', $matricula_cadastrada);

    $matricula_digitada = isset($_POST['matricula']) ? $_POST['matricula'] : '';
    $matricula_digitada_limpa = preg_replace('/[^0-9]/', '', $matricula_digitada);

    if (!empty($matricula_cadastrada_limpa)) {
        if (empty($matricula_digitada_limpa)) {
            return new WP_Error('empty_matricula', __('<strong>ERRO</strong>: Sua conta requer o preenchimento da matrícula.', 'text-domain'));
        }

        if ($matricula_digitada_limpa !== $matricula_cadastrada_limpa) {
            return new WP_Error('invalid_matricula', __('<strong>ERRO</strong>: Matrícula incorreta para este usuário.', 'text-domain'));
        }
    }

    return $user;
}

// Adicione em includes/user-login.php

add_filter('login_redirect', 'custom_direcionar_para_painel', 10, 3);
function custom_direcionar_para_painel($redirect_to, $request, $user) {
    // Se houver erro ou não for um usuário válido, mantém o padrão
    if (!($user instanceof WP_User)) {
        return $redirect_to;
    }

    // Administradores podem ir para o painel padrão se desejado
    if (user_can($user, 'manage_options')) {
        return admin_url(); // ou admin_url('index.php')
    }

    // Redireciona os demais colaboradores para a página do Painel Central
    // Pode ser uma página administrativa customizada ou uma página pública do site
    return admin_url('index.php');
}