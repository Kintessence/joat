<?php 
function verificar_acesso_com_token_unificado() {
    if (is_user_logged_in()) return;

    $token_geral = "1jqsod079q"; //https://intra-semcom.vilavelha.es.gov.br/?liberado=1jqsod079q
    $cookie_name = "acesso_liberado";
	$min = 1; // 30; default: 30 min
	$sec = 1; // 60; default: 60 sec
    $tempo_expiracao = time() + ($sec * $min); // Token válido por 30 minutos

    $paginas_liberadas = [89, 575, 1766, 2912, 3105, 3168];
    $cpts_liberados = ['diario'];
    $urls_liberadas = [home_url('/trello/'),home_url('/ponto/'),home_url('/acompanhamento-demandas/'),home_url('/?mvp_mock=1')];

    $url_atual = home_url($_SERVER['REQUEST_URI']);
    $usuario_id = $_COOKIE['token_user_id'] ?? null;
    $token_usuario = $_COOKIE['token_usuario'] ?? null;

    // Checa se está liberado via token geral ou token de usuário válido
    $valido =
        (isset($_COOKIE[$cookie_name]) && $_COOKIE[$cookie_name] === $token_geral) ||
        (
            $usuario_id &&
            $token_usuario &&
            get_user_meta($usuario_id, 'token_usuario', true) === $token_usuario &&
            get_user_meta($usuario_id, 'acesso_autorizado', true) === '1'
        );

    if (!$valido) {
        // Se tem token geral na URL
        if (isset($_GET['liberado']) && $_GET['liberado'] === $token_geral) {
            setcookie($cookie_name, $token_geral, $tempo_expiracao, "/");
            return;
        }

        // Se tem token de usuário na URL
        if (isset($_GET['user']) && isset($_GET['token'])) {
            $uid = intval($_GET['user']);
            $tkn = sanitize_text_field($_GET['token']);
            $tkn_valido = get_user_meta($uid, 'token_usuario', true);
            $autorizado = get_user_meta($uid, 'acesso_autorizado', true);

            if ($tkn_valido === $tkn && $autorizado === '1') {
                setcookie('token_user_id', $uid, $tempo_expiracao, "/");
                setcookie('token_usuario', $tkn, $tempo_expiracao, "/");
                return;
            }
        }

        // Permissões por exceções
        if (!is_page($paginas_liberadas) &&
            !(is_singular() && in_array(get_post_type(), $cpts_liberados)) &&
            !in_array($url_atual, $urls_liberadas)) {
            wp_die("Acesso negado! Você precisa de um token válido.");
        }
    }
}
add_action('template_redirect', 'verificar_acesso_com_token_unificado');


function shortcode_tokens_usuarios() {
    if (!current_user_can('manage_options')) return "Sem permissão.";

    // Salvar POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_token_update'])) {
        foreach ($_POST['user_token_update'] as $user_id => $dados) {
            $token = sanitize_text_field($dados['token']);
            $ativo = isset($dados['ativo']) ? '1' : '0';
            update_user_meta($user_id, 'token_usuario', $token);
            update_user_meta($user_id, 'acesso_autorizado', $ativo);
        }
        echo "<div class='updated'><p>Tokens atualizados.</p></div>";
    }

    $usuarios = get_users(['fields' => ['ID', 'display_name']]);
    $token_geral = '1jqsod079q';

    ob_start(); ?>
    <form method="post" autocomplete="off">
        <table>
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Token</th>
                    <th>Ativo</th>
                    <th>Link</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $user): 
                $t = get_user_meta($user->ID, 'token_usuario', true);
                $a = get_user_meta($user->ID, 'acesso_autorizado', true); ?>
                <tr>
                    <td><?php echo esc_html($user->display_name); ?></td>
                    <td><input type="text" name="user_token_update[<?php echo $user->ID; ?>][token]" value="<?php echo esc_attr($t); ?>" /></td>
                    <td><input type="checkbox" name="user_token_update[<?php echo $user->ID; ?>][ativo]" <?php checked($a, '1'); ?> /></td>
                    <td><code>?user=<?php echo $user->ID; ?>&token=<?php echo urlencode($t); ?></code></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <button type="submit">Salvar Tokens</button>
    </form>

    <hr>
    <p><strong>Token geral:</strong> <code>?liberado=<?php echo $token_geral; ?></code></p>

    <?php return ob_get_clean();
}
add_shortcode('tokens_usuarios', 'shortcode_tokens_usuarios');