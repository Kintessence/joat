<?php

function script_inline_baixar_svg() {
    // Defina os IDs das páginas onde o script deve ser carregado
    $paginas_permitidas = array(1766); // Substitua pelos IDs das páginas desejadas

    // Verifica se está na homepage ou em uma das páginas permitidas
    if (is_front_page() || is_home() || is_page($paginas_permitidas)) {
        ?>
        <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".gallery a").forEach((link) => {
                link.addEventListener("click", function (event) {
                    event.preventDefault(); // Impede a abertura do SVG no navegador
                    
					const url = link.href;

                    if (url.endsWith(".svg")) {
                        const nomeArquivo = url.split("/").pop(); // Extrai o nome do arquivo da URL
                        iniciarDownload(url, nomeArquivo);
                    }
                });
            });

            document.querySelectorAll(".gallery svg").forEach((element) => {
                element.addEventListener("click", function () {
                    baixarSVGEmbutido(element);
                });
            });

            function baixarSVGEmbutido(svgElement) {
                const svgData = new XMLSerializer().serializeToString(svgElement);
                const blob = new Blob([svgData], { type: "image/svg+xml" });
                const url = URL.createObjectURL(blob);
                iniciarDownload(url, "download.svg");
            }

            function iniciarDownload(url, filename) {
                const link = document.createElement("a");
                link.href = url;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'script_inline_baixar_svg');