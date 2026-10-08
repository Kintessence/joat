<?php

//$settings = ['api_key'   => get_field('trello_api_key', 'option'),'api_token' => get_field('trello_api_token', 'option'),'board_id'  => get_field('trello_board_id', 'option'),'list_id'   => get_field('trello_list_id', 'option'),'member_id' => get_field('trello_member_id', 'option'),];

// ===== CONFIG TRELL0 =====
define('AD_TRELLO_KEY','ed94e47d2503f68b89675fde9a3a8156');
define('AD_TRELLO_TOKEN','ATTA4a6056999d7161fd138da3836aedb54a360bf5a529e998daf4511339a70ab37c0886CF69');
define('AD_TRELLO_LIST_ENTRADA','60887409f8ac184ffb099f08');
define('AD_TRELLO_LIST_PRODUCAO', '691486172186d51ff8907dfa');

// IDs dos 4 membros oficiais
define('AD_TRELLO_MEMBROS_OFICIAIS', [
    '51841622127b7449510056e9', //Birous (fbirous) - ID: 51841622127b7449510056e9
    '61adfe82068cc44d810785cb', //Ian Santos (iansanto) - ID: 61adfe82068cc44d810785cb
    '60009eee97534b2991e1f2fe', //Marcus Luiz Moreira Tourinho Jr (marcusluizt) - ID: 60009eee97534b2991e1f2fe
    '64a6c95b852a91bfe7191607', //Roberto Costa (prrobertocosta) - ID: 64a6c95b852a91bfe7191607
]);

function ad_trello_request($method, $endpoint, $params = []) {

    $url = "https://api.trello.com/1/" . $endpoint;

    $query = array_merge($params, [
        'key' => AD_TRELLO_KEY,
        'token' => AD_TRELLO_TOKEN,
    ]);

    if ($method === 'GET') {
        $url = add_query_arg($query, $url);
        $response = wp_remote_get($url);
    } else {
        $response = wp_remote_request($url, [
            'method' => $method,
            'body'   => $query,
        ]);
    }

    if (is_wp_error($response)) {
        return false;
    }

    return json_decode(wp_remote_retrieve_body($response), true);
}

add_action('wp_ajax_ad_assign_member', 'ad_assign_member');
add_action('wp_ajax_nopriv_ad_assign_member', 'ad_assign_member');

function ad_assign_member() {

    $card_id   = sanitize_text_field($_POST['card_id'] ?? '');
    $member_id = sanitize_text_field($_POST['member_id'] ?? '');

    if (!$card_id || !$member_id) {
        wp_send_json_error();
    }

    // 1️⃣ Adicionar membro (sem substituir os existentes)
    $add_member = ad_trello_request(
        'POST',
        'cards/' . $card_id . '/idMembers',
        [
            'value' => $member_id
        ]
    );

    if (!$add_member) {
        wp_send_json_error();
    }

    // 2️⃣ Mover para Produção
    $move_card = ad_trello_request(
        'PUT',
        'cards/' . $card_id,
        [
            'idList' => AD_TRELLO_LIST_PRODUCAO
        ]
    );

    if (!$move_card) {
        wp_send_json_error();
    }

    wp_send_json_success();
}

add_action('wp_ajax_ad_get_unassigned_cards', 'ad_get_unassigned_cards');
add_action('wp_ajax_nopriv_ad_get_unassigned_cards', 'ad_get_unassigned_cards');

function ad_get_unassigned_cards() {

    $cards = ad_trello_request(
        'GET',
        'lists/' . AD_TRELLO_LIST_ENTRADA . '/cards',
        [
            'fields' => 'name,idMembers,id,labels,url,start,due'
        ]
    );

    if (!$cards) {
        wp_send_json([]);
    }

    $membros_oficiais = AD_TRELLO_MEMBROS_OFICIAIS;

    $filtrados = [];

    foreach ($cards as $card) {

        $tem_membro_oficial = false;

        foreach ($card['idMembers'] as $member_id) {
            if (in_array($member_id, $membros_oficiais)) {
                $tem_membro_oficial = true;
                break;
            }
        }

        if (!$tem_membro_oficial) {
            $filtrados[] = [
                'id'   => $card['id'],
                'name' => $card['name'],
            ];
        }
    }

    wp_send_json($filtrados);
}

add_action('wp_ajax_ad_get_producao_cards', 'ad_get_producao_cards');
add_action('wp_ajax_nopriv_ad_get_producao_cards', 'ad_get_producao_cards');

function ad_get_producao_cards() {

    $cards = ad_trello_request(
        'GET',
        'lists/' . AD_TRELLO_LIST_PRODUCAO . '/cards',
        [
            'fields' => 'name,idMembers,labels,url,start,due'
        ]
    );

    if (!$cards) {
        wp_send_json([]);
    }

    wp_send_json($cards);
}

add_shortcode('acompanhamento_demandas', function () {
    ob_start();
    ?>
    <style>
        .ad-wrapper { display: flex; gap: 30px; flex-wrap: wrap; }
        .ad-column { flex: 1 1 300px; border: 1px solid #ccc; padding: 10px; border-radius: 8px; background: #f9f9f9; }
        .ad-column h3 { margin-top: 0; }
        .ad-task { margin-left: 5px; }
        .ad-export { width: 100%; height: 150px; font-family: monospace; }
    </style>

    <hr>
    <h3>Cards sem responsável</h3>
    <div id="ad-unassigned"></div>
    <hr>
    <h3>Lista (automática) do Chefe</h3>
    <textarea id="ad-chefe-output" rows="20" style="width:100%;"></textarea>
    <button id="ad-gerar-chefe">Gerar Lista</button>
    <button id="ad-copiar-chefe">📋 Copiar</button>
    <span id="ad-feedback-chefe" style="margin-left:10px;color:green;display:none;">Copiado!</span>
    <hr>
    <div id="ad-box">
        <h3>Lista do Chefe</h3>
        <textarea id="ad-textarea" rows="30" style="width:100%;"></textarea>
        <button id="ad-atualizar">Atualizar Lista</button>
        <hr>
        <div id="ad-render" style="display:flex;flex-wrap:wrap;gap:20px;margin-top:20px;"></div>
        <hr>
		<h3>Texto para WhatsApp:</h3>
        <textarea id="ad-export" rows="10" style="width:100%;"></textarea>
        <button id="ad-copiar">📋 Copiar para WhatsApp</button>
        <span id="ad-feedback" style="margin-left:10px;color:green;display:none;">Copiado!</span>
    </div>

    <script>
		const AD_MEMBROS = <?php echo json_encode([
		['id' => AD_TRELLO_MEMBROS_OFICIAIS[0], 'nome' => 'Birous'],
		['id' => AD_TRELLO_MEMBROS_OFICIAIS[1], 'nome' => 'Ian'],
		['id' => AD_TRELLO_MEMBROS_OFICIAIS[2], 'nome' => 'Marcus'],
		['id' => AD_TRELLO_MEMBROS_OFICIAIS[3], 'nome' => 'Roberto'],
	]); ?>;
		
    document.addEventListener('DOMContentLoaded', () => {
		const textarea = document.getElementById('ad-textarea');
		const renderArea = document.getElementById('ad-render');
        const exportArea = document.getElementById('ad-export');
        const btnAtualizar = document.getElementById('ad-atualizar');
        const btnCopiar = document.getElementById('ad-copiar');
        const feedback = document.getElementById('ad-feedback');

        let savedStatus = {};
		
		function carregarTudo() {
			Promise.all([
				fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=ad_get_status&ts=' + Date.now())
				.then(r => r.json()),
				fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=ad_get_texto&ts=' + Date.now())
				.then(r => r.text())
			]).then(([status, texto]) => {
				savedStatus = status;
				textarea.value = texto;
				render(texto);
			});
		}
		
		function carregarNaoAtribuidos() {
			fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=ad_get_unassigned_cards&ts=' + Date.now())
				.then(r => r.json())
				.then(cards => {
				
				const container = document.getElementById('ad-unassigned');
				container.innerHTML = '';
				
				if (!cards.length) {
					container.innerHTML = '<p>Todos os cards já possuem responsável.</p>';
					return;
				}
				
				cards.forEach(card => {
					const wrapper = document.createElement('div');
					wrapper.style.display = 'flex';
					wrapper.style.justifyContent = 'space-between';
					wrapper.style.alignItems = 'center';
					wrapper.style.marginBottom = '8px';
					
					const titulo = document.createElement('div');
					titulo.style.flex = '1';
					
					// Titulo do card
					const span = document.createElement('span');
					span.textContent = ' ' + card.name + ' ';
					
					titulo.appendChild(span);
					//span.style.fontWeight = '600';
					// Link para card
					const link = document.createElement('a');
					link.href = card.url;
					link.target = '_blank';
					link.textContent = '[link]'; //card.name;
					//link.style.fontWeight = '600';
					
					titulo.appendChild(link);
					
					// Etiquetas
					if (card.labels && card.labels.length) {
						const labelsDiv = document.createElement('div');
						labelsDiv.style.fontSize = '12px';
						labelsDiv.style.opacity = '0.7';
						
						const nomesLabels = card.labels
						.map(l => l.name || l.color)
						.join(', ');
						
						labelsDiv.textContent = 'Etiquetas: ' + nomesLabels;
						titulo.appendChild(labelsDiv);
					}
					
					// Datas
					if (card.start || card.due) {
						const datasDiv = document.createElement('div');
						datasDiv.style.fontSize = '12px';
						datasDiv.style.opacity = '0.7';
						
						let textoDatas = '';
						
						if (card.start) {
							const inicio = new Date(card.start).toLocaleDateString('pt-BR');
							textoDatas += 'Início: ' + inicio + ' ';
						}
						
						if (card.due) {
							const fim = new Date(card.due).toLocaleDateString('pt-BR');
							textoDatas += 'Entrega: ' + fim;
						}
						
						datasDiv.textContent = textoDatas;
						titulo.appendChild(datasDiv);
					}
					
					const select = document.createElement('select');
					
					const defaultOption = document.createElement('option');
					defaultOption.textContent = 'Selecionar responsável';
					defaultOption.value = '';
					select.appendChild(defaultOption);
					
					AD_MEMBROS.forEach(membro => {
						const opt = document.createElement('option');
						opt.value = membro.id;
						opt.textContent = membro.nome;
						select.appendChild(opt);
					});
					
					select.addEventListener('change', function () {
						
						if (!this.value) return;
						
						fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
							method: 'POST',
							headers: {'Content-Type': 'application/x-www-form-urlencoded'},
							body: new URLSearchParams({
								action: 'ad_assign_member',
								card_id: card.id,
								member_id: this.value
							})
						})
							.then(r => r.json())
							.then(resp => {
							if (resp.success) {
								wrapper.remove(); // remove da lista visual
								gerarListaChefe();
							} else {
								alert('Erro ao atribuir.');
							}
						});						
					});
					wrapper.appendChild(titulo);
					wrapper.appendChild(select);
					container.appendChild(wrapper);
				});
			});
		}

        // Inicial
        carregarTudo();
		carregarNaoAtribuidos();
		gerarListaChefe();

        btnAtualizar.onclick = () => {
            const texto = textarea.value;
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'ad_salvar_texto',
                    texto: texto
                })
            }).then(() => carregarTudo());
        };

        btnCopiar.onclick = () => {
            exportArea.select();
            document.execCommand('copy');
            feedback.style.display = 'inline';
            setTimeout(() => feedback.style.display = 'none', 2000);
        };

        function gerarHash(nome, tarefa, index) {
			const base = (nome + '::' + tarefa + '::' + index)
			.normalize("NFD")
			.replace(/[\u0300-\u036f]/g, '')
			.toLowerCase()
			.replace(/\s+/g, '_');
			
			return 'hash_' + btoa(unescape(encodeURIComponent(base)))
				.replace(/[^a-z0-9]/gi, '');
		}

        function render(textoOriginal) {
            const linhas = textoOriginal.split(/\r?\n/);
            let grupos = {}, nomeAtual = null;

            linhas.forEach(l => {
                const linha = l.trim();
                if (!linha) return;

                const matchNome = linha.match(/^[A-ZÁÉÍÓÚÂÊÔÃÕÇ]{2,}(?:\s+[A-Z]{2,})*$/);
                if (matchNome) {
                    nomeAtual = linha.trim();
                    grupos[nomeAtual] = [];
                } else if (nomeAtual) {
                    grupos[nomeAtual].push(linha);
                }
            });

            renderArea.innerHTML = '';
            let exportTxt = '';

            for (let nome in grupos) {
                const coluna = document.createElement('div');
                coluna.className = 'ad-column';
                coluna.style.flex = '1 1 300px';
                coluna.innerHTML = `<h3>${nome}</h3>`;
				exportTxt += `*${nome}*\n`;

                grupos[nome].forEach(tarefa => {
                    const hash = gerarHash(nome, tarefa);
                    const checked = savedStatus[hash] ? 'checked' : '';
                    const taskHtml = `<div class="ad-task"><label><input type="checkbox" data-hash="${hash}" ${checked}> ${tarefa.replace(/^[-.•*]+/, '').trim()}</label></div>`;
                    coluna.innerHTML += taskHtml;

                    let tarefaExport = tarefa.replace(/^[-.•*]+/, '').trim();
                    if (savedStatus[hash]) tarefaExport = `~${tarefaExport}~`;
                    exportTxt += tarefaExport + '\n';
                });

                renderArea.appendChild(coluna);
                exportTxt += '\n';
            }

            //exportArea.value = exportTxt.trim();
            // Gera data formatada
			const hoje = new Date();
			const dia = String(hoje.getDate()).padStart(2, '0');
			const mes = hoje.toLocaleString('pt-BR', { month: 'long' }); // ex: outubro
			const prefixo = `*DEMANDAS TRELLO ${dia} DE ${mes.toUpperCase()}*`;
			// Aplica no textarea de exportação
			exportArea.value = prefixo + "\n\n" + exportTxt.trim();
			
			renderArea.querySelectorAll('input[type=checkbox]').forEach(chk => {
				chk.addEventListener('change', () => {
					const hash = chk.dataset.hash;
					savedStatus[hash] = chk.checked;
					
					fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
						method: 'POST',
						headers: {'Content-Type': 'application/x-www-form-urlencoded'},
						body: new URLSearchParams({
							action: 'ad_salvar_status',
							hash: hash,
							status: chk.checked ? '1' : '0'
						})
					}).then(() => carregarTudo());
				});
			});
		}
	});
		document.getElementById('ad-copiar-chefe').addEventListener('click', function () {
			
			const textarea = document.getElementById('ad-chefe-output');
			textarea.select();
			document.execCommand('copy');
			
			const feedback = document.getElementById('ad-feedback-chefe');
			feedback.style.display = 'inline';
			
			setTimeout(() => {
				feedback.style.display = 'none';
			}, 2000);
		});	
		
		document.getElementById('ad-gerar-chefe').addEventListener('click', gerarListaChefe); 
		function gerarListaChefe() {			
			fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=ad_get_producao_cards&ts=' + Date.now())
				.then(r => r.json())
				.then(cards => {
				
				const membros = AD_MEMBROS;
				let agrupado = {};
				
				membros.forEach(m => {
					agrupado[m.id] = {
						nome: m.nome,
						tarefas: []
					};
				});
				
				cards.forEach(card => {
					membros.forEach(m => {
						if (card.idMembers.includes(m.id)) {
							agrupado[m.id].tarefas.push(card);
						}
					});
				});
				
				const hoje = new Date();
				const dia = String(hoje.getDate()).padStart(2, '0');
				const mes = hoje.toLocaleString('pt-BR', { month: 'long' });
				const prefixo = `Demandas Trello ${dia} de ${mes.charAt(0).toUpperCase() + mes.slice(1)}`;
				
				let textoFinal = prefixo + "\n\n";
				
				Object.values(agrupado).forEach(grupo => {
					if (grupo.tarefas.length) {
						textoFinal += grupo.nome + "\n";
						grupo.tarefas.forEach(t => {
							let linha = "- " + t.name;
							
							// Etiquetas
							if (t.labels && t.labels.length) {
								const nomesLabels = t.labels.map(l => l.name || l.color).join(", ");
								linha += ` [${nomesLabels}]`;
							}
							
							// Data início
							if (t.start) {
								const inicio = new Date(t.start).toLocaleDateString('pt-BR');
								linha += ` | Início: ${inicio}`;
							}
							
							// Data fim
							if (t.due) {
								const fim = new Date(t.due).toLocaleDateString('pt-BR');
								linha += ` | Entrega: ${fim}`;
							}
							
							// Link
							linha += ` | ${t.url}`;
							
							textoFinal += linha + "\n";
						});
						textoFinal += "\n";
					}
				});				
				document.getElementById('ad-chefe-output').value = textoFinal.trim();
			});
		};
</script>
    <?php
    return ob_get_clean();
});

// Texto original (visível por todos)
add_action('wp_ajax_ad_get_texto', function () {
    echo get_option('ad_demandas_texto_original', '');
    exit;
});
add_action('wp_ajax_nopriv_ad_get_texto', fn() => do_action('wp_ajax_ad_get_texto'));

// Salvar texto original
add_action('wp_ajax_ad_salvar_texto', function () {
    if (isset($_POST['texto'])) {
        update_option('ad_demandas_texto_original', wp_unslash($_POST['texto']));
        echo 'ok';
    } else {
        echo 'erro';
    }
    exit;
});
add_action('wp_ajax_nopriv_ad_salvar_texto', fn() => do_action('wp_ajax_ad_salvar_texto'));

// Obter status dos checkboxes
add_action('wp_ajax_ad_get_status', function () {
    echo json_encode(get_option('ad_demandas_status', []));
    exit;
});
add_action('wp_ajax_nopriv_ad_get_status', fn() => do_action('wp_ajax_ad_get_status'));

// Salvar status dos checkboxes
add_action('wp_ajax_ad_salvar_status', function () {
    $status = get_option('ad_demandas_status', []);
    $hash = sanitize_text_field($_POST['hash'] ?? '');
    $valor = $_POST['status'] === '1' ? true : false;

    if ($hash) {
        $status[$hash] = $valor;
        update_option('ad_demandas_status', $status);
    }
    echo 'ok';
    exit;
});
add_action('wp_ajax_nopriv_ad_salvar_status', fn() => do_action('wp_ajax_ad_salvar_status'));