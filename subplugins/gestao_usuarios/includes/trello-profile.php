<?php
if (!defined('ABSPATH')) exit;

add_action('show_user_profile', 'adicionar_campo_trello_id');
add_action('edit_user_profile', 'adicionar_campo_trello_id');

function adicionar_campo_trello_id($user) {
    if (!current_user_can('manage_options')) return;

    $trello_id = get_user_meta($user->ID, 'trello_id', true);
    ?>
    <h3><?php esc_html_e('Integrações', 'text-domain'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="trello_id"><?php esc_html_e('ID do Trello', 'text-domain'); ?></label></th>
            <td>
                <input type="text" name="trello_id" id="trello_id" value="<?php echo esc_attr($trello_id); ?>" class="regular-text" />
                <p class="description"><?php esc_html_e('Coloque aqui o ID de usuário do Trello para automações.', 'text-domain'); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

add_action('personal_options_update', 'salvar_campo_trello_id');
add_action('edit_user_profile_update', 'salvar_campo_trello_id');

function salvar_campo_trello_id($user_id) {
    if (!current_user_can('manage_options')) return;
    if (isset($_POST['trello_id'])) {
        update_user_meta($user_id, 'trello_id', sanitize_text_field($_POST['trello_id']));
    }
}