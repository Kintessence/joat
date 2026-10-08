<?php
function html_para_markdown($html) {
    // Remove tags desnecessárias e converte para markdown básico
    $conteudo = $html;
	
    // Converter headings: <h1> a <h6> -> # a ######
    $conteudo = preg_replace('/<h1[^>]*>(.*?)<\/h1>/', "# $1\n\n", $conteudo);
    $conteudo = preg_replace('/<h2[^>]*>(.*?)<\/h2>/', "## $1\n\n", $conteudo);
    $conteudo = preg_replace('/<h3[^>]*>(.*?)<\/h3>/', "### $1\n\n", $conteudo);
    $conteudo = preg_replace('/<h4[^>]*>(.*?)<\/h4>/', "#### $1\n\n", $conteudo);
    $conteudo = preg_replace('/<h5[^>]*>(.*?)<\/h5>/', "##### $1\n\n", $conteudo);
    $conteudo = preg_replace('/<h6[^>]*>(.*?)<\/h6>/', "###### $1\n\n", $conteudo);
    
	// Converter negrito: <strong>, <b> -> **texto**
    $conteudo = preg_replace('/<strong>(.*?)<\/strong>/', '**$1**', $conteudo);
    $conteudo = preg_replace('/<b>(.*?)<\/b>/', '**$1**', $conteudo);
    
    // Converter itálico: <em>, <i> -> *texto*
    $conteudo = preg_replace('/<em>(.*?)<\/em>/', '*$1*', $conteudo);
    $conteudo = preg_replace('/<i>(.*?)<\/i>/', '*$1*', $conteudo);
    
    // Converter listas não ordenadas
    $conteudo = preg_replace('/<ul>(.*?)<\/ul>/s', "$1", $conteudo);
    $conteudo = preg_replace('/<li>(.*?)<\/li>/', "- $1\n", $conteudo);
    
    // Converter listas ordenadas
    $conteudo = preg_replace('/<ol>(.*?)<\/ol>/s', "$1", $conteudo);
    $linhas = explode("\n", $conteudo);
    $contador = 1;
    foreach ($linhas as &$linha) {
        if (preg_match('/<li>(.*?)<\/li>/', $linha)) {
            $linha = preg_replace('/<li>(.*?)<\/li>/', "$contador. $1", $linha);
            $contador++;
        }
    }
    $conteudo = implode("\n", $linhas);
    
    // Converter links: <a href="url">texto</a> -> [texto](url)
    $conteudo = preg_replace('/<a href="(.*?)">(.*?)<\/a>/', '[$2]($1)', $conteudo);
    
    // Remover outras tags HTML
    $conteudo = strip_tags($conteudo);
    
    // Limpar espaços em branco extras
    $conteudo = trim($conteudo);
    
    return $conteudo;
}
