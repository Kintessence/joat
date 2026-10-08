<?php
if (!defined('ABSPATH')) exit;

add_action('admin_menu', 'relatorio_usuarios_cargos_menu');
function relatorio_usuarios_cargos_menu() {
    add_users_page(
        'Relatório de Usuários e Cargos',
        'Exportar Cargos',
        'manage_options',
        'relatorio-usuarios-cargos',
        'render_relatorio_usuarios_cargos'
    );
}

function render_relatorio_usuarios_cargos() {
    if (!current_user_can('manage_options')) return;

    $usuarios = get_users(array(
        'orderby' => 'display_name',
        'order'   => 'ASC',
    ));

    $linhas_excel = array();
    $linhas_excel[] = "Login\tNome Completo\tE-mail\tCargo (Select)\tDescrição do Cargo";

    $linhas_padrao = array();
    $linhas_padrao[] = "================================================================================";
    $linhas_padrao[] = "RELATÓRIO DE USUÁRIOS E CARGOS";
    $linhas_padrao[] = "================================================================================";

    $linhas_whatsapp = array();
    $linhas_whatsapp[] = "*📋 RELATÓRIO DE USUÁRIOS E CARGOS*";
    $linhas_whatsapp[] = "----------------------------------------";

    ?>
    <div class="wrap">
        <h1>Relatório de Usuários e Cargos</h1>
        <p>Escolha o formato desejado abaixo e clique no botão para copiar o relatório completo.</p>

        <div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <button id="btn-copiar-excel" class="button button-primary">📊 Copiar para Excel / Tabela</button>
            <button id="btn-copiar-padrao" class="button button-secondary">📄 Copiar Lista Padrão (Texto)</button>
            <button id="btn-copiar-whatsapp" class="button button-secondary" style="background-color: #25D366; color: white; border-color: #128C7E;">💬 Copiar Formato WhatsApp</button>
            <span id="msg-copiado" style="display:none; color: #46b450; font-weight: bold; margin-left: 10px;">✓ Copiado para a área de transferência!</span>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><strong>Nome de Acesso (Login)</strong></th>
                    <th><strong>Nome Completo</strong></th>
                    <th><strong>E-mail</strong></th>
                    <th><strong>Cargo (Select)</strong></th>
                    <th><strong>Descrição do Cargo</strong></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $user) : 
                    $cargo_select = get_user_meta($user->ID, 'cargo_select', true);
                    $cargo_desc   = get_user_meta($user->ID, 'cargo', true);
                    
                    $nome_completo = trim($user->first_name . ' ' . $user->last_name);
                    if (empty($nome_completo)) {
                        $nome_completo = $user->display_name;
                    }

                    $select_display = !empty($cargo_select) ? esc_html($cargo_select) : '<em style="color:red;">[Sem Select]</em>';
                    $desc_display   = !empty($cargo_desc) ? esc_html($cargo_desc) : '<em style="color:red;">[Sem Descrição]</em>';

                    $link_edicao = get_edit_user_link($user->ID);

                    $linhas_excel[] = "{$user->user_login}\t{$nome_completo}\t{$user->user_email}\t" . ($cargo_select ? $cargo_select : 'SEM_SELECT') . "\t" . ($cargo_desc ? $cargo_desc : 'SEM_DESCRICAO');

                    $str_padrao  = "Usuário: {$user->user_login} | Nome: {$nome_completo}\n";
                    $str_padrao .= "E-mail: {$user->user_email}\n";
                    $str_padrao .= "Cargo (Select): " . ($cargo_select ? $cargo_select : "[PENDENTE]") . "\n";
                    $str_padrao .= "Descrição Cargo: " . ($cargo_desc ? $cargo_desc : "[PENDENTE]") . "\n";
                    $str_padrao .= "--------------------------------------------------------------------------------";
                    $linhas_padrao[] = $str_padrao;

                    $status_wa = (!empty($cargo_select) || !empty($cargo_desc)) ? "✅" : "⚠️ *PENDENTE*";
                    $str_wa  = "{$status_wa} *{$nome_completo}* (@{$user->user_login})\n";
                    $str_wa .= "✉️ {$user->user_email}\n";
                    $str_wa .= "🔹 *Select:* " . ($cargo_select ? $cargo_select : "_Nenhum_") . "\n";
                    $str_wa .= "🔹 *Descrição:* " . ($cargo_desc ? $cargo_desc : "_Nenhuma_") . "\n";
                    $linhas_whatsapp[] = $str_wa;
                ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo esc_url($link_edicao); ?>" target="_blank" title="Editar este usuário em nova aba">
                                    <?php echo esc_html($user->user_login); ?> ↗
                                </a>
                            </strong>
                        </td>
                        <td><?php echo esc_html($nome_completo); ?></td>
                        <td><?php echo esc_html($user->user_email); ?></td>
                        <td><code><?php echo $select_display; ?></code></td>
                        <td><?php echo $desc_display; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <textarea id="texto-excel" style="position: absolute; left: -9999px;"><?php echo esc_textarea(implode("\n", $linhas_excel)); ?></textarea>
        <textarea id="texto-padrao" style="position: absolute; left: -9999px;"><?php echo esc_textarea(implode("\n", $linhas_padrao)); ?></textarea>
        <textarea id="texto-whatsapp" style="position: absolute; left: -9999px;"><?php echo esc_textarea(implode("\n\n", $linhas_whatsapp)); ?></textarea>
    </div>

    <script>
    function copiarTexto(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select();
        document.execCommand('copy');
        
        var msg = document.getElementById('msg-copiado');
        msg.style.display = 'inline';
        setTimeout(function() {
            msg.style.display = 'none';
        }, 3000);
    }

    document.getElementById('btn-copiar-excel').addEventListener('click', function() { copiarTexto('texto-excel'); });
    document.getElementById('btn-copiar-padrao').addEventListener('click', function() { copiarTexto('texto-padrao'); });
    document.getElementById('btn-copiar-whatsapp').addEventListener('click', function() { copiarTexto('texto-whatsapp'); });
    </script>
    <?php
}