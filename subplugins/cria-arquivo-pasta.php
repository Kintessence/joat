<?php

function cria_arquivo_pasta($nome_pasta, $arquivos = array(), $apagar_pasta = false) {
    // Verifique as permissões do usuário, pois esta ação pode exigir privilégios adequados.
    if (current_user_can('manage_options')) {
        // Use a variável global $plugin_base_dir para obter o diretório base do plugin
        global $path;

        // Diretório onde você deseja criar a pasta
        $diretorio_pasta = $path . $nome_pasta;

        // Verifique se a pasta deve ser criada
        if (!empty($nome_pasta) && !file_exists($diretorio_pasta)) {
            wp_mkdir_p($diretorio_pasta);
        }

        // Itere sobre os arquivos para criação ou exclusão
        foreach ($arquivos as $arquivo) {
            // Caminho completo do arquivo
            $caminho_arquivo = $diretorio_pasta . '/' . $arquivo;

            // Conteúdo inicial do arquivo PHP apenas se a extensão for .php
            $conteudo_inicial = pathinfo($arquivo, PATHINFO_EXTENSION) === 'php' ? "<?php" : '';

            // Verifique se o arquivo já existe
            if (!file_exists($caminho_arquivo)) {
                // Crie um arquivo e adicione o conteúdo inicial apropriado
                file_put_contents($caminho_arquivo, $conteudo_inicial);
            } else {
                // Se já existir, você pode tomar a ação apropriada, como mostrar uma mensagem de erro.
            }
        }

        // Apagar a pasta se a opção estiver habilitada
        if ($apagar_pasta && file_exists($diretorio_pasta)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($diretorio_pasta, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }

            rmdir($diretorio_pasta);
        }
    }
}
//add_action('init', function() { cria_arquivo_pasta('subplugins/controle-demandas',array('controle-demandas.php')); });
