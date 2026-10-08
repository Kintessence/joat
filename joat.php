<?php
/**
 * Plugin Name: JOAT – Jack of all Trades
 * Description: Subplugin Manager: Gerenciador, criador e orchestrator de subplugins com UX avançada, cria estrutura via textarea, ativa/desativa via AJAX e remove arquivos/pastas com -x- preservando hifens.
 * Version: 1.7
 * Author: Fabio Birous
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// 1. Menu e Interface no Admin
add_action( 'admin_menu', 'spm_add_admin_menu' );
function spm_add_admin_menu() {
    add_menu_page(
        'Subplugin Manager',
        'Subplugins',
        'manage_options',
        'subplugin-manager',
        'spm_render_manager_page',
        'dashicons-admin-plugins',
        105
    );
}

function spm_render_manager_page() {
    $structure_nonce = wp_create_nonce( 'spm_create_structure' );
    $status_nonce    = wp_create_nonce( 'spm_update_status' );
    ?>
    <div class="wrap">
        <h1>Subplugin Manager</h1>
        <h2>Criar / Atualizar / Apagar Arquivos</h2>
        <form id="spm-structure-form">
            <textarea name="structure" rows="8" style="width:100%;font-family:monospace;" placeholder="nome_da_pasta&#10;-meu_plugin.php&#10;-assets&#10;--style.css&#10;--x-arquivo_para_deletar.txt"></textarea><br>
            <p><button class="button button-primary" type="submit">Executar Estrutura</button></p>
        </form>
        <div id="spm-structure-feedback" style="margin-top:15px;"></div>
        <hr>
        <h2>Subplugins Instalados</h2>
        <div style="max-width: 650px;">
            <?php spm_display_subplugins(); ?>
        </div>
    </div>

    <style>
        textarea[name="structure"]::placeholder { color:#999; font-style:italic; }
        .spm-card { background:#fff; border:1px solid #ccc; padding:12px 15px; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; border-radius:4px; transition: all 0.2s ease; }
        .spm-card.spm-disabled { background-color:#f6f7f7; border-color:#dcdcde; opacity:0.65; }
        .spm-badge-ok { background:#e7f4e8; color:#1e4620; padding:3px 8px; border-radius:3px; font-size:11px; font-weight:bold; }
        .spm-badge-err { background:#fce8e6; color:#a10f0a; padding:3px 8px; border-radius:3px; font-size:11px; font-weight:bold; }
        .spm-badge-disabled { background:#e0e0e0; color:#50575e; padding:3px 8px; border-radius:3px; font-size:11px; font-weight:bold; }
    </style>

    <script>
    jQuery(document).ready(function($){
        // Submeter Estrutura via Textarea
        $('#spm-structure-form').on('submit', function(e){
            e.preventDefault();
            var $btn = $(this).find('button');
            $btn.prop('disabled', true).text('Processando...');

            $.post(ajaxurl, {
                action: 'spm_create_structure',
                nonce: '<?php echo $structure_nonce; ?>',
                structure: $('textarea[name="structure"]').val()
            }, function(res){
                $btn.prop('disabled', false).text('Executar Estrutura');
                if(res.success) {
                    location.reload();
                } else {
                    $('#spm-structure-feedback').html('<div class="notice notice-error"><p>'+res.data+'</p></div>');
                }
            });
        });

        // Alternar Status via AJAX instantâneo
        $('.spm-status-select').on('change', function(e){
            var $select   = $(e.target || this);
            var $card     = $select.closest('.spm-card');
            var $badge    = $card.find('.spm-badge');
            var spName    = $select.data('sp-name');
            var mainFile  = $select.data('main-file');
            var newStatus = $select.val();

            $select.prop('disabled', true);

            $.post(ajaxurl, {
                action: 'spm_update_status',
                nonce: '<?php echo $status_nonce; ?>',
                subplugin_name: spName,
                subplugin_status: newStatus
            }, function(res){
                $select.prop('disabled', false);
                if(res.success) {
                    if (newStatus === 'disabled') {
                        $card.addClass('spm-disabled');
                        $badge.attr('class', 'spm-badge spm-badge-disabled').text('Desativado');
                    } else {
                        $card.removeClass('spm-disabled');
                        if (mainFile) {
                            $badge.attr('class', 'spm-badge spm-badge-ok').text('Pronto (' + mainFile + ')');
                        } else {
                            $badge.attr('class', 'spm-badge spm-badge-err').text('Atenção: Nenhum arquivo .php na raiz');
                        }
                    }
                } else {
                    alert('Erro ao atualizar status: ' + (res.data || 'Erro desconhecido.'));
                }
            });
        });
    });
    </script>
    <?php
}

// 2. Localizador de Arquivo Principal (Resiliente)
function spm_get_main_file( $dir_path, $sp_slug ) {
    if ( file_exists( $dir_path . '/' . $sp_slug . '.php' ) ) {
        return $dir_path . '/' . $sp_slug . '.php';
    }
    if ( file_exists( $dir_path . '/index.php' ) ) {
        return $dir_path . '/index.php';
    }
    $php_files = glob( $dir_path . '/*.php' );
    if ( ! empty( $php_files ) ) {
        return $php_files[0];
    }
    return false;
}

// 3. Listagem de subplugins e status
function spm_display_subplugins() {
    $base = plugin_dir_path(__FILE__) . 'subplugins/';
    if ( ! is_dir( $base ) ) {
        wp_mkdir_p( $base );
    }

    $subs = array_diff( scandir( $base ), array( '.', '..' ) );
    $subs = array_filter( $subs, function( $item ) use ( $base ) {
        return is_dir( $base . $item );
    });

    if ( empty( $subs ) ) {
        echo '<p>Nenhum subplugin encontrado na pasta <code>/subplugins/</code>.</p>';
        return;
    }

    foreach ( $subs as $sp ) {
        $san         = spm_sanitize_name( $sp );
        $dir_path    = $base . $sp;
        $main_file   = spm_get_main_file( $dir_path, $sp );
        $file_name   = $main_file ? basename( $main_file ) : '';
        $st          = get_option( 'subplugin_status_' . $san, 'enabled' );
        $is_disabled = ( $st === 'disabled' );
        ?>
        <div class="spm-card <?php echo $is_disabled ? 'spm-disabled' : ''; ?>" id="spm-card-<?php echo esc_attr( $san ); ?>">
            <div>
                <strong style="font-size:14px; display:block;"><?php echo esc_html( $sp ); ?></strong>
                <?php if ( $is_disabled ) : ?>
                    <span class="spm-badge spm-badge-disabled">Desativado</span>
                <?php elseif ( $main_file ) : ?>
                    <span class="spm-badge spm-badge-ok">Pronto (<?php echo esc_html( $file_name ); ?>)</span>
                <?php else : ?>
                    <span class="spm-badge spm-badge-err">Atenção: Nenhum arquivo .php na raiz</span>
                <?php endif; ?>
            </div>

            <div style="display:flex; align-items:center; gap:10px;">
                <select class="spm-status-select" 
                        data-sp-name="<?php echo esc_attr( $sp ); ?>" 
                        data-main-file="<?php echo esc_attr( $file_name ); ?>">
                    <option value="enabled" <?php selected( $st, 'enabled' ); ?>>Ativado</option>
                    <option value="disabled" <?php selected( $st, 'disabled' ); ?>>Desativado</option>
                </select>

                <form method="post" style="margin:0;" onsubmit="return confirm('Tem certeza que deseja apagar a pasta deste subplugin?');">
                    <?php wp_nonce_field( 'spm_delete_subplugin', 'spm_delete_nonce' ); ?>
                    <input type="hidden" name="delete_subplugin_name" value="<?php echo esc_attr( $sp ); ?>">
                    <button type="submit" class="button button-small button-link-delete">Excluir</button>
                </form>
            </div>
        </div>
        <?php
    }
}

// 4. Handler AJAX para atualização de Status
add_action( 'wp_ajax_spm_update_status', 'spm_ajax_update_status' );
function spm_ajax_update_status() {
    if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'spm_update_status', 'nonce', false ) ) {
        wp_send_json_error( 'Acesso negado.' );
    }

    if ( isset( $_POST['subplugin_name'], $_POST['subplugin_status'] ) ) {
        $san_name   = spm_sanitize_name( wp_unslash( $_POST['subplugin_name'] ) );
        $san_status = sanitize_text_field( wp_unslash( $_POST['subplugin_status'] ) );
        
        update_option( 'subplugin_status_' . $san_name, $san_status );
        wp_send_json_success( 'Status atualizado.' );
    }

    wp_send_json_error( 'Parâmetros inválidos.' );
}

// 5. Exclusão de pasta (POST nativo)
add_action( 'admin_init', 'spm_handle_actions' );
function spm_handle_actions() {
    if ( isset( $_POST['delete_subplugin_name'] ) && check_admin_referer( 'spm_delete_subplugin', 'spm_delete_nonce' ) ) {
        if ( ! current_user_can( 'manage_options' ) ) return;
        $sp_name  = spm_sanitize_name( wp_unslash( $_POST['delete_subplugin_name'] ) );
        $dir_path = plugin_dir_path( __FILE__ ) . 'subplugins/' . $sp_name;

        if ( is_dir( $dir_path ) ) {
            spm_recursive_rmdir( $dir_path );
            delete_option( 'subplugin_status_' . $sp_name );
        }
    }
}

// 6. Carregamento dos Módulos Habilitados
add_action( 'plugins_loaded', 'spm_load_enabled_subplugins' );
function spm_load_enabled_subplugins() {
    $base = plugin_dir_path( __FILE__ ) . 'subplugins/';
    if ( ! is_dir( $base ) ) return;

    $subs = array_diff( scandir( $base ), array( '.', '..' ) );

    foreach ( $subs as $sp ) {
        $dir_path = $base . $sp;
        if ( ! is_dir( $dir_path ) ) continue;

        $san    = spm_sanitize_name( $sp );
        $status = get_option( 'subplugin_status_' . $san, 'enabled' );

        if ( $status === 'enabled' ) {
            $main_file = spm_get_main_file( $dir_path, $sp );
            if ( $main_file ) {
                include_once $main_file;
            }
        }
    }
}

// 7. Manipulação via Textarea AJAX
add_action( 'wp_ajax_spm_create_structure', 'spm_create_structure' );
function spm_create_structure() {
    if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( 'spm_create_structure', 'nonce', false ) ) {
        wp_send_json_error( 'Acesso negado.' );
    }

    if ( empty( $_POST['structure'] ) ) {
        wp_send_json_error( 'A estrutura não pode estar vazia.' );
    }

    $lines = preg_split( '/\r?\n/', wp_unslash( $_POST['structure'] ) );
    $base  = plugin_dir_path( __FILE__ ) . 'subplugins/';
    
    if ( ! is_dir( $base ) ) {
        wp_mkdir_p( $base );
    }

    $stack = array( $base );

    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( $line === '' ) continue;

        $is_del = false;

        if ( preg_match( '/^(-+)x-(.+)$/', $line, $m ) ) {
            $is_del = true;
            $lvl    = strlen( $m[1] );
            $name   = trim( $m[2] );
        } elseif ( preg_match( '/^(-+)?(.+)$/', $line, $m ) ) {
            $lvl  = isset( $m[1] ) ? strlen( $m[1] ) : 0;
            $name = trim( $m[2] );
        } else {
            continue;
        }

        $name = spm_sanitize_name( $name );

        $stack = array_slice( $stack, 0, $lvl + 1 );
        $parent = end( $stack );
        $curr   = trailingslashit( $parent ) . $name;

        if ( strpos( $curr, $base ) !== 0 ) {
            continue;
        }

        if ( $is_del ) {
            if ( is_file( $curr ) ) {
                unlink( $curr );
            } elseif ( is_dir( $curr ) ) {
                spm_recursive_rmdir( $curr );
                delete_option( 'subplugin_status_' . $name );
            }
            continue;
        }

        if ( strpos( $name, '.' ) !== false ) {
            $dir = dirname( $curr );
            if ( ! is_dir( $dir ) ) {
                wp_mkdir_p( $dir );
            }
            if ( ! file_exists( $curr ) ) {
                $ext = pathinfo( $curr, PATHINFO_EXTENSION );
                $hdr = ( $ext === 'php' ) ? "<?php\n// Subplugin Gerado: $name\nif (!defined('ABSPATH')) exit;\n" : "// Gerado: $name\n";
                file_put_contents( $curr, $hdr );
            }
        } else {
            if ( ! is_dir( $curr ) ) {
                wp_mkdir_p( $curr );
            }
            $stack[] = $curr;
        }
    }

    wp_send_json_success( 'Estrutura processada com sucesso!' );
}

function spm_sanitize_name( $name ) {
    $name = str_replace( ' ', '_', $name );
    return preg_replace( '/[^A-Za-z0-9_\.\-]/', '', $name );
}

function spm_recursive_rmdir( $dir ) {
    if ( is_dir( $dir ) ) {
        $objects = scandir( $dir );
        foreach ( $objects as $object ) {
            if ( $object != "." && $object != ".." ) {
                if ( is_dir( $dir . "/" . $object ) && ! is_link( $dir . "/" . $object ) ) {
                    spm_recursive_rmdir( $dir . "/" . $object );
                } else {
                    unlink( $dir . "/" . $object );
                }
            }
        }
        rmdir( $dir );
    }
}