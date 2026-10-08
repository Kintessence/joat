<?php

function filter_email_attachments( $attachments, $email, $form, $fields ) {
  // Add an uploaded file from a file field as an attachment
  // The file field should have return format "FileArray"
  $file = af_get_field( 'anexo' );
  $attachments[] = get_attached_file( $file['id'] );

  return $attachments;
}
add_filter( 'af/form/email/attachments/key=form_63b6fa976f69e', 'filter_email_attachments', 10, 4 );

/*
 * Shortcode para uso no contrato
 * */
function contrato_function($atts){
	
	$cpf = get_field('cpf') ? get_field('cpf') : 'CPF';
	$cnpj = get_field('cnpj') ? get_field('cnpj') : 'CNPJ';
	$desc = get_field('descricao_detalhada') ? get_field('descricao_detalhada') : get_field('descricao_geral');
	$end = get_field('endereco') ? get_field('endereco') : 'Endereço';
	$ec = get_field('empresa') ?  get_field('empresa') : 'Empresa';
	$ece = get_field('empresa_extenso') ? get_field('empresa_extenso').' '.$cnpj : 'Empresa';
	$rec = get_field('representante') ? get_field('representante') : 'Representante';
	$reccpf = $rec.' ('.$cpf.')';
	$valor = get_field('valor') ? 'R$'.get_field('valor').' ('.clsTexto::valorPorExtenso(get_field('valor'), true, false).')' : '$$$';
	$suporte = get_field('suporte') ? 'Acompanhamento e suporte serão fornecidos por '.get_field('suporte').' após a entrega formal do <em>website</em>.' : null;
	
	extract(shortcode_atts(array(
		'dq' => 'Dr.Quem!',
		'dqe' => 'Dr.Quem Estúdio Gráfico e Digital 07.524.685-0001/14',
		'socio' => 'Fábio Barros de Melo',
		'endereco' => $end,
		'ec' => $ec,
		'ece' => $ece,
		'rec' => $rec,
		'reccpf' => $reccpf,
		'valor' => $valor,
		'descricao' => $desc,
		'cronograma' => 'Pagamento na entrega do produto final',
		'data' => date_i18n('l j \d\e F \d\e Y'),
		'suporte' => $suporte,
		), $atts));
	
	$tag = 'strong';
	$style = '';
	switch($atts[0]):
	case 'dq': $r = $dq ; break;
	case 'dqe': $r = $dqe." ({$dq})" ; break;
	case 'socio': $r = $socio ; break;
	case 'endereco':
	$tag = 'em';
	if($end): $r = ', localizada na '.$end.', '; endif; break;
	case 'ec': $r = $ec ; break;
	case 'ece':
	if(get_field('empresa_extenso')): $r = $ece." ({$ec})"; else: $r = $ec; endif; break;
	case 'rec': $r = $rec ; break;
	case 'reccpf': $r = $reccpf ; break;
	case 'valor': $r = $valor ; break;
	case 'descricao':
	$tag = 'em';
	$r = $descricao ; break;
	case 'cronograma':	
	$tag = 'em';
	if(get_field('parcelas')):
	$p = clsTexto::removerFormatacaoNumero(get_field('valor'))/get_field('parcelas');
	$p = clsTexto::removerFormatacaoNumero(get_field('valor'));
	$r = get_field('parcelas')." parcelas de R\${$p},00 (".clsTexto::valorPorExtenso($p, true, false).") a serem pagas todo dia ".get_field('vencimento')." de cada mês"; else: $r = $cronograma; endif; break;
	case 'suporte':	$tag = 'p'; $r = $suporte; break;
	case 'data': $r = $data ; break;
	default:
	$style = ' style="color: #ff0000;"';
	$r = 'FALTA CAMPO';
	endswitch;
	
	return '<'.$tag.$style.'>'.$r.'</'.$tag.'>';
}
add_shortcode('c', 'contrato_function');




/*
 * Shortcode com texto do contrato
 * */
function texto_contrato($atts){
	
	$class = get_field('servico') == 'site' ? '':'hide';
	$textoContrato =
		'<div class="page-footer">
		<div class="logo"></div>
		<div>Dr. Quem! Estúdio Gráfico e Digital<br>
		Avenida Hugo Viola, nº 360, sala 5<br>
		Jardim da Penha – Vitória/ES<br>
		CEP 29060-420<br>
		CNPJ 07.524.685/0001-14</div>
		<div>+55 (27) 9 9771 0550<br>
		+55 (27) 3324 5000<br>
		www.drquem.com<br>
		drquem@drquem.com</div>
		</div>
		<table>
		<thead>
		<tr>
		<td><div class="page-header-space"></div></td>
		</tr>
		</thead>
		<tfoot>
		<tr>
		<td><div class="page-footer-space"></div></td>
		</tr>
		</tfoot>
		<tbody>
		<tr>
		<td><div class="break">
		<h1>Contrato de Prestação de serviço</h1>
		<p>Entre<br>
		<strong>Dr.Quem Estúdio Gráfico e Digital 07.524.685-0001/14 (Dr.Quem!)</strong><br>
		e<br>
		<strong>[c ece]</strong></p>
		</div>
		<div class="">
		<h2>Sumário</h2>
		<p>A [c dq] fará o melhor para satisfazer suas necessidades e atender suas expectativas, mas é importante registrar tudo por escrito para que todos conheçam suas responsabilidades (quem faz o quê) e saibam o que acontecerá se algo der errado.</p>
		<p>Neste contrato você não encontrará termos jurídicos complicados ou textos longos e incompreensíveis. A [c dq] não tem interesse em fazer a [c ec] assinar algo do qual possa se arrepender depois. O que a [c dq] quer é o melhor para os dois lados, agora e no futuro.</p>
		<p>Então, em resumo, a [c ec][c endereco]está contratando a empresa [c dq], localizada na Av. Hugo Viola, 360, sl. 5, Jardim da Penha, Vitória/ES para: [c descricao] pelo valor de [c valor], conforme combinado anteriormente. Vamos tratar dos detalhes mais abaixo.</p>
		</div>
		<div class="break">
		<h2 id="acordo">O que ambas as partes acordam em fazer?</h2>
		<p>[c rec] – confirma ter autoridade para contratar em nome da [c ec] e fornecer informações e materiais necessários para concluir o projeto, no momento e nos formatos solicitados. [c rec] também irá avaliar nosso trabalho, opinar e aprovar em tempo hábil.</p>
		<p>Prazos funcionarão para ambas as partes, e sendo assim, [c rec] também se comprometerá com as datas definidas em conjunto.</p>
		<p>[c rec] também concorda em manter o <a href="#pagamento" class="smooth-scroll">cronograma de pagamento</a> estabelecido ao final deste contrato.</p>
		<p>[c dq] – possui a experiência e capacidade de realizar os serviços solicitados e fará tudo de maneira profissional e tempestiva, procurando cumprir todos os prazos definidos e, além disso, mantendo o sigilo de qualquer informação que nos for confidenciada.</p>
		</div>
		<div class="break">
		<h2>O âmago da questão</h2>
		<div class="break">
		<h3>Design</h3>
		<p><span class="'.$class.'">A [c dq] criará <em>layouts</em> flexíveis e atraentes, que se adaptem a vários dispositivos e tamanhos de tela. A empresa criará projetos de forma iterativa, utilizando predominantemente HTML e CSS em Gerenciadores de Conteúdo, evitando o tempo gasto com a montagem de cada página de forma estática. Serão usadas composições visuais estáticas para indicar uma direção de arte (cor, textura e tipografia), denominadas de forma genérica de "design do site".</span></p>
		<p>A [c ec] terá muitas oportunidades de avaliar o trabalho da [c dq] e fornecer <em>feedbacks</em><span class="'.$class.'">. Será compartilhado um site de desenvolvimento e haverá contato regular, possivelmente diário, por telefone, e-mail ou WhatsApp.</p>
		<p>Se, em qualquer momento, a [c ec] não estiver satisfeita com a direção do trabalho da [c dq], poderá cancelar este contrato mediante o pagamento do que tiver sido produzido até o momento.</p>
		</div>
		<div class="break">
		<h3>Conteúdo textual</h3>
		<p>A menos que acordado separadamente, não somos responsáveis por escrever ou incluir textos. Se isso for necessário, podemos fornecer uma proposta à parte.</p>
		</div>
		<div class="break">
		<h3>Imagens e fotos</h3>
		<p>A [c ec] deve fornecer arquivos gráficos em formato digital e vetorial e fotos em formato digital e de alta resolução. Se optar por comprar fotos, podemos sugerir alguns bancos de imagens profissionais. Se precisar que pesquisemos as imagens, podemos fornecer uma proposta à parte.</p>
		</div>
		<div class="'.$class.' break">
		<h3>Sopa de letrinhas: HTML, CSS, Javascript, PHP, MySQL</h3>
		<p>Nós entregaremos um <em>website </em>formado por páginas desenvolvidas em HTML, folhas de estilo CSS para formatação e Javascript para detecção de funcionalidades e comportamentos, funcionando em um Gerenciador de Conteúdo à escolha – Drupal ou WordPress –, construído na linguagem PHP, com banco de dados MySQL.</p>
		</div>
		<div class="'.$class.' break">
		<h3>Compatibilidade com navegadores e <em>responsividade</em></h3>
		<p>Compatibilidade com navegadores não significa mais a tentativa de fazer um <em>website</em> ter a mesma aparência em navegadores de diferentes capacidades ou em dispositivos com telas de tamanhos diferentes. Significa garantir que a experiência de uma pessoa seja adequada à capacidade do seu navegador ou dispositivo.</p>
		<p>Garantimos a entrega de um <em>website</em> responsivo, ou seja, que se adapte e se adeque ao dispositivo pelo qual está sendo acessado, <em>mobile</em> ou <em>desktop</em>.</p>
		</div>
		<div class='.$class.' "break">
		<h3>Testes nos navegadores</h3>
		<p>Testamos nosso trabalho nas versões atuais dos principais navegadores de <em>desktop</em>, incluindo os fabricados pela Apple (Safari), Google (Chrome), Microsoft (Edge) e Mozilla Firefox. Não testaremos em outros navegadores antigos, a menos que acordemos separadamente.</p>
		</div>
		<div class="'.$class.' break">
		<h3>Testes nos dispositivos móveis</h3>
		<p>Testar nos dispositivos móveis mais populares é essencial para garantir que a experiência de uma pessoa seja apropriada aos recursos do dispositivo que ela está usando. Testamos nossos trabalhos em:</p>
		<p><strong>iOS</strong>: Safari e Google Chrome<br>
		<strong>Android</strong>: Google Chrome</p>
		<p>Não testaremos no Opera Mini/Mobile, dispositivos Android específicos ou outros navegadores móveis, a menos que acordemos separadamente. Se a [c ec] precisar de testes nesses navegadores, podemos fornecer uma proposta em separado para isso.</p>
		</div>
		<div class="'.$class.' break">
		<h3>Suporte técnico</h3>
		<p>Não somos uma empresa de hospedagem de sites, mas podemos configurar o seu site em um servidor, além de qualquer <em>software</em> estatístico (como o <em>Google</em> Analytics, por exemplo), além de cuidar de atualização de <em>plugins</em> e do Gerenciador de Conteúdo, mas atualizações o gerenciamento <strong>do servidor</strong> ficam por sua conta. Não podemos oferecer suporte a questões relativas ao servidor de hospedagem, mas podemos apontá-las quando e se ocorrerem.</p>
		[c suporte]
		</div>
		<div class="break">
		<h3>Mudanças e revisões</h3>
		<p>Reconhecemos que mudanças e revisões podem ser necessárias durante a execução do serviço. O valor estimado no início deste contrato foi baseado no tempo necessário para atender às suas solicitações. No entanto, estamos abertos a ser flexíveis caso haja mudança de ideia ou necessidade de incluir novos itens. Se a [c ec] mudar de ideia e precisar de alterações ou acréscimos, teremos prazer em fornecer uma nova proposta separada para atender às novas necessidades.</p>
		</div>
		<div class="break">
		<h3>Informações legais</h3>
		<p>Realizaremos nosso trabalho de acordo com as boas práticas do setor e no padrão esperado de uma pessoa adequadamente qualificada e com experiência relevante. Dito isso, não podemos garantir resultados e <span class="hide">que nosso trabalho será livre de erros e por isso</span> não poderemos ser responsabilizados por você ou por terceiros, por eventuais danos, queda nos lucros, perdas ou quaisquer incidentes, consectários ou especiais, ainda que tenham nos alertado sobre isso.</p>
		<p>Sua responsabilidade para conosco também será limitada ao valor pago sob este contrato e a [c ec] não será responsável perante nós ou terceiros, por eventuais danos, queda nos lucros, perdas ou quaisquer incidentes, consectários ou especiais, mesmo se tivermos avisado sobre eles.</p>
		<p>Finalmente, se qualquer disposição deste contrato for considerada ilegal, nula ou por qualquer motivo inexequível, essa disposição será considerada separável deste contrato e não afetará a validade e a aplicabilidade de quaisquer disposições restantes.</p>
		</div>
		<div class="break">
		<h3>Direitos autorais</h3>
		<p>Primeiramente, a [c ec] nos garante que todos os elementos de texto, imagens ou outros trabalhos artísticos fornecidos são de sua propriedade ou que seu uso foi autorizado por quem de direito.</p>
		<div class="'.$class.'">Ao recebimento da última parcela de pagamento, o direito autoral ficará assim determinado:</div>
		<p>O direito autoral ficará assim determinado:</p>
		<p>Caberão à [c ec] os gráficos e outros elementos visuais que criarmos. Podemos fornecer os arquivos originais e finalizados, sendo de sua responsabilidade guardá-los e protegê-los, não nos cabendo nenhuma responsabilidade ou obrigação de manter, guardar ou providenciar nenhuma outra cópia além dessa. A [c ec] já possui os elementos textuais, imagens e todos os dados fornecidos, a menos que sejam de propriedade de outra pessoa.</p>
		<p>A [c dq] será proprietária da combinação única de elementos criados e vai licenciá-la para uso da [c ec], exclusivamente e em perpetuidade<span class="'.$class.'"> somente para este projeto</span>, a menos que seja acordado o contrário. Neste caso, forneceremos uma proposta em separado para esse fim.</p>
		</div>
		<div class="break">
		<h3>Divulgação do trabalho</h3>
		<p>Gostamos de divulgar nosso trabalho e compartilhar o que aprendemos com outras pessoas, por isso nós nos reservamos o direito de, com sua permissão, exibir todos os aspectos de nosso trabalho criativo, incluindo esboços, designs de trabalhos em andamento e o projeto concluído em nosso portfólio e escrever sobre ele em sites, artigos de revistas e livros.</p>
		</div>
		</div>
		<div class="">
		<h2 id="pagamento" class="smooth-scroll">Cronograma de Pagamento</h2>
		<p>Temos certeza que a [c ec] entende como o pagamento das faturas que serão enviadas é importante para uma pequena empresa como a nossa. Como temos certeza que vamos continuar nosso bom relacionamento, a [c ec] concorda em honrar o seguinte cronograma de pagamento:</p>
		<p>[c cronograma] (<a href="#acordo" class="smooth-scroll">Voltar para o tópico <em>O que ambas as partes acordam em fazer?</em></a>)</p>
		</div>
		<div class="">
		<h2>Não tem aquelas letras miúdas?</h2>
		<p>Este contrato é válido e não precisa ser renovado. Se alguma parte deste contrato for considerada ilegal, nula, ou por qualquer razão não vinculante, esta deverá ser extirpada e não afetará a validade e vinculabilidade do restante.</p>
		<p>Embora a linguagem seja simples, as intenções são sérias, sendo este contrato um instrumento válido segundo o Código Civil Brasileiro.</p>
		</div>
		<div class="break">
		<h2>A linha pontilhada</h2>
		<div class="assinatura" style="display:flex">
		<div>
		<div>Assinado por [c socio],<br>
		em nome da [c dq]</div>
		</div>
		<div>
		<div>Assinado por [c rec],<br>
		em nome da [c ec]</div>
		</div>
		</div>
		<div class="data">[c data]</strong></div>
		<p>Todos devem assinar acima e manter uma cópia para seus registros.</p>
		</div></td>
		</tr>
		</tbody>
		</table>';
	return do_shortcode($textoContrato);
}
add_shortcode('contrato', 'texto_contrato');

/*
 * Classe que escreve o valor por extenso
 * */
class clsTexto 
{   
	public static function removerFormatacaoNumero( $strNumero )
    {
 
        $strNumero = trim( str_replace( "R$", null, $strNumero ) );
 
        $vetVirgula = explode( ",", $strNumero );
        if ( count( $vetVirgula ) == 1 )
        {
            $acentos = array(".");
            $resultado = str_replace( $acentos, "", $strNumero );
            return $resultado;
        }
        else if ( count( $vetVirgula ) != 2 )
        {
            return $strNumero;
        }
 
        $strNumero = $vetVirgula[0];
        $strDecimal = mb_substr( $vetVirgula[1], 0, 2 );
 
        $acentos = array(".");
        $resultado = str_replace( $acentos, "", $strNumero );
        $resultado = $resultado . "." . $strDecimal;
 
        return $resultado;
 
    }	
	
    public static function valorPorExtenso( $valor = 0, $bolExibirMoeda = true, $bolPalavraFeminina = false )		
    {
 
        $valor = self::removerFormatacaoNumero( $valor );
        
        $singular = null;
        $plural = null;
 
        if ( $bolExibirMoeda )
        {
            $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
        }
        else
        {
            $singular = array("", "", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
            $plural = array("", "", "mil", "milhões", "bilhões", "trilhões","quatrilhões");
        }
 
        $c = array("", "cem", "duzentos", "trezentos", "quatrocentos","quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
        $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta","sessenta", "setenta", "oitenta", "noventa");
        $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze","dezesseis", "dezessete", "dezoito", "dezenove");
        $u = array("", "um", "dois", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
 
 
        if ( $bolPalavraFeminina )
        {
        
            if ($valor == 1) 
            {
                $u = array("", "uma", "duas", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
            }
            else 
            {
                $u = array("", "um", "duas", "três", "quatro", "cinco", "seis","sete", "oito", "nove");
            }            
            
            $c = array("", "cem", "duzentas", "trezentas", "quatrocentas","quinhentas", "seiscentas", "setecentas", "oitocentas", "novecentas");            
            
        } 
 
        $z = 0;
 
        $valor = number_format( $valor, 2, ".", "." );
        $inteiro = explode( ".", $valor );
 
        for ( $i = 0; $i < count( $inteiro ); $i++ ) 
        {
            for ( $ii = mb_strlen( $inteiro[$i] ); $ii < 3; $ii++ ) 
            {
                $inteiro[$i] = "0" . $inteiro[$i];
            }
        }
 
        // $fim identifica onde que deve se dar junção de centenas por "e" ou por "," ;)
        $rt = null;
        $fim = count( $inteiro ) - ($inteiro[count( $inteiro ) - 1] > 0 ? 1 : 2);
        for ( $i = 0; $i < count( $inteiro ); $i++ )
        {
            $valor = $inteiro[$i];
            $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
            $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
            $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";
 
            $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd && $ru) ? " e " : "") . $ru;
            $t = count( $inteiro ) - 1 - $i;
            $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
            if ( $valor == "000")
                $z++;
            elseif ( $z > 0 )
                $z--;
                
            if ( ($t == 1) && ($z > 0) && ($inteiro[0] > 0) )
                $r .= ( ($z > 1) ? " de " : "") . $plural[$t];
                
            if ( $r )
                $rt = $rt . ((($i > 0) && ($i <= $fim) && ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
        }
 
        $rt = mb_substr( $rt, 1 );
 
        return($rt ? trim( $rt ) : "zero");
 
    }
}


/*
 * Exibe o shortcode de contrato como conteúdo padrão para CPT Contratos
 * */
add_filter( 'default_content', 'contrato_conteudo_padrao', 10, 2 );
function contrato_conteudo_padrao( $content, $post ) {
	
	switch( $post->post_type ) {
			case 'contrato': $content = '[contrato]'; break;
			default: $content = ''; break;
	}
    return $content;
}


/*
 * Cola texto na primeiro save do conteudo Proposta
 * */

add_action('save_post', 'wpse120996_on_creation_not_update');
function wpse120996_on_creation_not_update($post_id) {
    //get_post( $post_id ) == null checks if the post is not yet in the database
    if( get_post( $post_id ) == null ) {
		add_filter( 'the_content', 'texto');
	}
}
function texto(){
	$class = get_field('servico') == 'site' ? '':'hide';
$textoContrato =
'<div class="page-footer">
  <div class="logo"></div>
  <div>Dr. Quem! Estúdio Gráfico e Digital<br>
    Avenida Hugo Viola, nº 360, sala 5<br>
    Jardim da Penha – Vitória/ES<br>
    CEP 29060-420<br>
    CNPJ 07.524.685/0001-14</div>
  <div>+55 (27) 9 9771 0550<br>
    +55 (27) 3324 5000<br>
    www.drquem.com<br>
    drquem@drquem.com</div>
</div>
<table>
  <thead>
    <tr>
      <td><div class="page-header-space"></div></td>
    </tr>
  </thead>
  <tfoot>
    <tr>
      <td><div class="page-footer-space"></div></td>
    </tr>
  </tfoot>
  <tbody>
    <tr>
      <td><div class="break">
          <h1>Contrato de Prestação de serviço</h1>
          <p>Entre<br>
            <strong>Dr.Quem Estúdio Gráfico e Digital 07.524.685-0001/14 (Dr.Quem!)</strong><br>
            e<br>
            <strong>Associação dos Docentes da Ufes CNPJ (Adufes)</strong></p>
        </div>
        <div class="">
          <h2>Sumário</h2>
          <p>A <strong>Dr.Quem!</strong> fará o melhor para satisfazer suas necessidades e atender suas expectativas, mas é importante registrar tudo por escrito para que todos conheçam suas responsabilidades (quem faz o quê) e saibam o que acontecerá se algo der errado.</p>
          <p>Neste contrato você não encontrará termos jurídicos complicados ou textos longos e incompreensíveis. A <strong>Dr.Quem!</strong> não tem interesse em fazer a <strong>Adufes</strong> assinar algo do qual possa se arrepender depois. O que a <strong>Dr.Quem!</strong> quer é o melhor para os dois lados, agora e no futuro.</p>
          <p>Então, em resumo, a <strong>Adufes</strong><span>, localizada na Endereço,</span> está contratando a empresa <strong>Dr.Quem!</strong>, localizada na Av. Hugo Viola, 360, sl. 5, Jardim da Penha, Vitória/ES para: <em></em></p>
          <p><em>– criação de posts/cards<br>
            – jornal ou impresso (ex.: Fique por Dentro)<br>
            – criação de mini-campanhas e peças derivadas<br>
            – marcas para eventos<br>
            – atualização no site antigo ou novo (ex.: criação de novas soluções não previstas, revisões, etc.)</em></p>
          <em> </em> pelo valor de <strong>R$200 (duzentos reais)</strong> mensais, conforme combinado anteriormente. Vamos tratar dos detalhes mais abaixo.
          <p></p>
        </div>
        <div class="">
          <h2>O que ambas as partes acordam em fazer?</h2>
          <p><strong>Ana Carolina Galvão</strong> – confirma ter autoridade para contratar em nome da <strong>Adufes</strong> e fornecer informações e materiais necessários para concluir o projeto, no momento e nos formatos solicitados. <strong>Ana Carolina Galvão</strong> também irá avaliar nosso trabalho, opinar e aprovar em tempo hábil.</p>
          <p>Prazos funcionarão para ambas as partes, e sendo assim, <strong>Ana Carolina Galvão</strong> também se comprometerá com as datas definidas em conjunto.</p>
          <p><strong>Ana Carolina Galvão</strong> também concorda em manter o <a href="#pagamento" class="smooth-scroll">cronograma de pagamento</a> estabelecido ao final deste contrato.</p>
          <p><strong>Dr.Quem!</strong> – possui a experiência e capacidade de realizar os serviços solicitados e fará tudo de maneira profissional e tempestiva, procurando cumprir todos os prazos definidos e, além disso, mantendo o sigilo de qualquer informação que nos for confidenciada.</p>
        </div>
        <div class="">
          <h2>O âmago da questão</h2>
          <div class="break">
            <h3>Design</h3>
            <p><span class="'.$class.'">Criamos <em>layouts</em> atraentes e flexíveis que se adaptam a vários dispositivos e tamanhos de tela. Criamos projetos de forma iterativa e usamos predominantemente HTML e CSS em Gerenciadores de Conteúdo para não gastar tempo montando cada página de forma estática. Podemos usar composições visuais (estáticas) para indicar uma direção de arte (cor, textura e tipografia) e chamamos a isso de forma genérica de “design do site".</span></p>
            <p>A <strong>Adufes</strong> terá muitas oportunidades de avaliar nosso trabalho e dar <em>feedbacks</em><span class="'.$class.'">. Compartilharemos um site de desenvolvimento e teremos</span> via contato regular, possivelmente diário, por telefone, e-mail ou WhatsApp.</p>
            <p>Se, em qualquer momento, a <strong>Adufes</strong> não estiver satisfeita com a direção do nosso trabalho, poderá cancelar esse contrato mediante o pagamento do que foi produzido até aquele momento.</p>
          </div>
          <div class="break">
            <h3>Conteúdo textual</h3>
            <p>A menos que acordado separadamente, não somos responsáveis por escrever ou incluir textos. Se isso for necessário, podemos fornecer uma proposta à parte.</p>
          </div>
          <div class="break">
            <h3>Imagens e fotos</h3>
            <p>A <strong>Adufes</strong> deve fornecer arquivos gráficos em formato digital e vetorial e fotos em formato digital e de alta resolução. Se optar por comprar fotos, podemos sugerir alguns bancos de imagens profissionais. Se precisar que pesquisemos as imagens, podemos fornecer uma proposta à parte.</p>
          </div>
          <div class="break">
            <h3>Sopa de letrinhas: HTML, CSS, Javascript, PHP, MySQL</h3>
            <p>Nós entregaremos um <em>website </em>formado por páginas desenvolvidas em HTML, folhas de estilo CSS para formatação e Javascript para detecção de funcionalidades e comportamentos, funcionando em um Gerenciador de Conteúdo à escolha, Drupal ou WordPress, construído sobre a linguagem PHP, com banco de dados MySQL.</p>
          </div>
          <div class="break">
            <h3>Compatibilidade com navegadores e <em>responsividade</em></h3>
            <p>Compatibilidade com navegadores não significa mais a tentativa de fazer um <em>website</em> ter a mesma aparência em navegadores de diferentes capacidades ou em dispositivos com telas de tamanhos diferentes. Significa garantir que a experiência de uma pessoa seja adequada à capacidade do seu navegador ou dispositivo.</p>
            <p>Garantimos a entrega de um <em>website</em> responsivo, ou seja, que se adapte e se adeque ao dispositivo pelo qual está sendo acessado, <em>mobile</em> ou <em>desktop</em>.</p>
          </div>
          <div class="break">
            <h3>Testes nos navegadores</h3>
            <p>Testamos nosso trabalho nas versões atuais dos principais navegadores de desktop, incluindo os fabricados pela Apple (Safari), Google (Chrome), Microsoft (Edge) e Mozilla Firefox. Não testaremos em outros navegadores antigos, a menos que acordemos separadamente.</p>
          </div>
          <div class="break">
            <h3>Testes nos dispositivos móveis</h3>
            <p>Testar nos dispositivos móveis mais populares é essencial para garantir que a experiência de uma pessoa seja apropriada aos recursos do dispositivo que ela está usando. Testamos nossos trabalhos em:</p>
            <p><strong>iOS</strong>: Safari e Google Chrome<br>
              <strong>Android</strong>: Google Chrome</p>
            <p>Não testaremos no Opera Mini/Mobile, dispositivos Android específicos ou outros navegadores móveis, a menos que acordemos separadamente. Se a <strong>Adufes</strong> precisar de testes nesses navegadores, podemos fornecer uma proposta em separado para isso.</p>
          </div>
          <div class="break">
            <h3>Suporte técnico</h3>
            <p>Não somos uma empresa de hospedagem de sites, mas podemos configurar o seu site em um servidor, além de qualquer <em>software</em> estatístico (como o <em>Google</em> Analytics, por exemplo), além de cuidar de atualização de <em>plugins</em> e do Gerenciador de Conteúdo, mas atualizações o gerenciamento <strong>do servidor</strong> ficam por sua conta. Não podemos oferecer suporte a questões relativas ao servidor de hospedagem, mas podemos apontá-las quando e se ocorrerem. </p>
            <p>O acompanhamento e suporte serão fornecidos por 3 meses (90 dias) após a entrega formal do <em>website</em>.</p>
          </div>
          <div class="break">
            <h3>Mudanças e revisões</h3>
            <p>Não queremos limitar sua capacidade de mudar de ideia. O preço estimado no início deste contrato é baseado no tempo que estimamos ser necessário para realizar tudo o que nos seja pedido, mas estamos felizes em sermos flexíveis. Se a <strong>Adufes</strong> mudar de ideia ou quiser acrescentar algo novo, não será um problema para nós fornecer uma proposta em separado para isso.</p>
          </div>
          <div class="break">
            <h3>Informações legais</h3>
            <p>Realizaremos nosso trabalho de acordo com as boas práticas do setor e no padrão esperado de uma pessoa adequadamente qualificada e com experiência relevante. Dito isso, não podemos garantir resultados e<span class="hide">que nosso trabalho será livre de erros e por isso</span> não poderemos ser responsabilizados por você ou por terceiros, por eventuais danos, queda nos lucros, perdas ou quaisquer incidentes, consectários ou especiais, ainda que tenham nos alertado sobre isso.</p>
            <p>Sua responsabilidade para conosco também será limitada ao valor pago sob este contrato e a <strong>Adufes</strong> não será responsável perante nós ou terceiros, por eventuais danos, queda nos lucros, perdas ou quaisquer incidentes, consectários ou especiais, mesmo se tivermos avisado sobre eles.</p>
            <p>Finalmente, se qualquer disposição deste contrato for considerada ilegal, nula ou por qualquer motivo inexequível, essa disposição será considerada separável deste contrato e não afetará a validade e a aplicabilidade de quaisquer disposições restantes.</p>
          </div>
          <div class="break">
            <h3>Direitos autorais</h3>
            <p>Primeiramente, a <strong>Adufes</strong> nos garante que todos os elementos de texto, imagens ou outros trabalhos artísticos fornecidos são de sua propriedade ou que seu uso foi autorizado por quem de direito.</p>
            <div class="'.$class.'">Ao recebimento da última parcela de pagamento, o direito autoral ficará assim determinado:</div>
            <p>O direito autoral ficará assim determinado:</p>
            <p>Caberão à <strong>Adufes</strong> os gráficos e outros elementos visuais que criarmos. Podemos fornecer os arquivos originais e finalizados, sendo de sua responsabilidade guardá-los e protegê-los, não nos cabendo nenhuma responsabilidade ou obrigação de manter, guardar ou providenciar nenhuma outra cópia além dessa. A <strong>Adufes</strong> já possui os elementos textuais, imagens e todos os dados fornecidos, a menos que sejam de propriedade de outra pessoa.</p>
            <p>A <strong>Dr.Quem!</strong> será proprietária da combinação única de elementos criados e vai licenciá-la para uso da <strong>Adufes</strong>, exclusivamente e em perpetuidade<span class="'.$class.'"> somente para este projeto</span>, a menos que seja acordado o contrário. Neste caso, forneceremos uma proposta em separado para esse fim.</p>
          </div>
          <div class="break">
            <h3>Divulgação do trabalho</h3>
            <p>Gostamos de divulgar nosso trabalho e compartilhar o que aprendemos com outras pessoas, por isso nós nos reservamos o direito de, com sua permissão, exibir todos os aspectos de nosso trabalho criativo, incluindo esboços, designs de trabalhos em andamento e o projeto concluído em nosso portfólio e escrever sobre ele em sites, artigos de revistas e livros.</p>
          </div>
        </div>
        <div class="">
          <h2 id="pagamento" class="smooth-scroll">Cronograma de Pagamento</h2>
          <p>Temos certeza que a <strong>Adufes</strong> entende como o pagamento das faturas que serão enviadas é importante para uma pequena empresa como a nossa. Como temos certeza que vamos continuar nosso bom relacionamento, a <strong>Adufes</strong> concorda em honrar o seguinte cronograma de pagamento:</p>
          <p align="center">[c cronograma]</p>
        </div>
        <div class="">
          <h2>Não tem aquelas letras miúdas?</h2>
          <p>Este contrato é válido e não precisa ser renovado. Se alguma parte deste contrato for considerada ilegal, nula, ou por qualquer razão não vinculante, esta deverá ser extirpada e não afetará a validade e vinculabilidade do restante.</p>
          <p>Embora a linguagem seja simples, as intenções são sérias, sendo este contrato um instrumento válido segundo o Código Civil Brasileiro.</p>
        </div>
        <div class="">
          <h2>A linha pontilhada</h2>
          <div class="assinatura" style="display:flex">
            <div>
              <div>Assinado por <strong>Fábio Barros de Melo</strong>,<br>
                em nome da <strong>Dr.Quem!</strong></div>
            </div>
            <div>
              <div>Assinado por <strong>Ana Carolina Galvão</strong>,<br>
                em nome da <strong>Adufes</strong></div>
            </div>
          </div>
          <div class="data">[c data]</strong></div>
          <p>Todos devem assinar acima e manter uma cópia para seus registros.</p>
        </div></td>
    </tr>
  </tbody>
</table>';
	$content .= $textoContrato;
	return $content;
}


// Criar página de calendário e preencher com dados do CSV
function create_calendar_page_from_csv( $new_status, $old_status, $post ) {
    global $post;
    if ( $post->post_type !== 'product' ) return;
    if ( 'publish' !== $new_status or 'publish' === $old_status ) return;
	
    $post_type = get_post_type($post->ID);
	$product_id = $post->ID;

    //if ($post_type === 'product' && (wp_is_post_revision($product_id) || wp_is_post_autosave($product_id))) {
    if ($post_type === 'product' && (wp_is_post_autosave($product_id))) {
        return; // Evita processamento desnecessário durante revisões e salvamentos automáticos
    }
	
	/*// Verificar se a página do calendário já foi criada para o produto
    $calendar_page_id = get_field('calendar_page', $product_id);

    if ($calendar_page_id) {
        return; // Se a página já existe, não faz nada
    }*/
	
    // Obter o arquivo CSV do campo ACF
    $csv_file = get_field('csv_file', $product_id);
    
    if ($csv_file) {
        // Ler o arquivo CSV
        $csv_data = array_map('str_getcsv', file($csv_file['url']));
        
		//if(!$update){
			// Criar a página do calendário
			$calendar_post = array(
				'post_title' => 'Calendário do Produto ' . $product_id,
				'post_type' => 'calendario',
				'post_status' => 'publish',
			);
			
			$calendar_id = wp_insert_post($calendar_post);
			
		//}
        
        // Preencher o campo ACF repeater com os dados do CSV
        if ($calendar_id) {
            foreach ($csv_data as $row) {
            //for($i=0;$i<5;$i++){
                $fields = array(
                    'field_648fbe20bd30a' => $row[0],
                    'field_648fbe35bd30b' => $row[1],
                    'field_648fbe38bd30c' => $row[2],
                    'field_648fbe3abd30d' => $row[3],
                    'field_648fbe3ebd30e' => $row[4],
                );
                
                add_row('field_648fbe06bd309', $fields, $calendar_id);
            }
            
            // Vincular a página do produto ao calendário
            update_field('field_648fc55125e2b', $csv_file['url']/*$product_id*/, $calendar_id );
            
            // Apagar o arquivo CSV
            wp_delete_file($csv_file['ID']);
        }
    }
}
//add_action( 'transition_post_status', 'create_calendar_page_from_csv', 10, 3);
//add_action('save_post_product', 'create_calendar_page_from_csv', 10, 3);


function add_this_to_new_products( $new_status, $old_status, $post ) {
    global $post;
    if ( $post->post_type !== 'product' ) return;
    if ( 'publish' !== $new_status or 'publish' === $old_status ) return;
	if ($post->post_type === 'product' && (wp_is_post_autosave($post->ID))) {
        return; // Evita processamento desnecessário durante revisões e salvamentos automáticos
    }
    
	$csv_file = get_field('csv_file', $post->ID);
	$csv_data = array_map('str_getcsv', file($csv_file['url']));
	$calendar_post = array(
				'post_title' => 'Calendário do Produto ' . $product_id,
				'post_type' => 'calendario',
				'post_status' => 'publish',
			);
			
			$calendar_id = wp_insert_post($calendar_post);
	if ($calendar_id) {
            foreach ($csv_data as $row) {
            //for($i=0;$i<5;$i++){
                $fields = array(
                    'field_648fbe20bd30a' => $row[0],
                    'field_648fbe35bd30b' => $row[1],
                    'field_648fbe38bd30c' => $row[2],
                    'field_648fbe3abd30d' => $row[3],
                    'field_648fbe3ebd30e' => $row[4],
                );
                
                add_row('field_648fbe06bd309', $fields, $calendar_id);
            }
            
            // Vincular a página do produto ao calendário
            update_field('field_648fc55125e2b', $csv_file['url']/*$product_id*/, $calendar_id );
            
            // Apagar o arquivo CSV
            //wp_delete_file($csv_file['ID']);
        }
}

//add_action( 'transition_post_status', 'add_this_to_new_products', 10, 3 );

function delete_all_posts_of_custom_type() {
    $post_type = 'calendario';
    $args = array(
        'post_type' => $post_type,
        'posts_per_page' => -1,
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            wp_delete_post(get_the_ID(), true);
        }
    }

    wp_reset_postdata();
}

// Executar a função para excluir todos os posts do tipo 'calendario'
//add_action('init', 'delete_all_posts_of_custom_type');
//



/*
 * 
 *
 */
function calendario_shortcode($atts) {
// Obter o mês atual
$mesAtual = date('n');

// Obter o ano atual
$anoAtual = date('Y');

// Verificar se foi enviado um mês pelo campo select
if (isset($_POST['mes'])) {
    $mesSelecionado = $_POST['mes'];
} else {
    $mesSelecionado = $mesAtual;
}

// Verificar se foi clicado na seta de avançar ou retroceder
if (isset($_GET['mes'])) {
    $mesSelecionado = $_GET['mes'];
}

// Avançar para o próximo ano se o mês for dezembro
if ($mesSelecionado == 12) {
    $anoSelecionado = $anoAtual + 1;
} else {
    $anoSelecionado = $anoAtual;
}

// Retroceder para o ano anterior se o mês for janeiro
if ($mesSelecionado == 1) {
    $anoSelecionado = $anoAtual - 1;
} else {
    $anoSelecionado = $anoAtual;
}

// Número de dias no mês selecionado
$numeroDias = cal_days_in_month(CAL_GREGORIAN, $mesSelecionado, $anoSelecionado);

// Data atual
$dataAtual = date('j');

// Mostrar o calendário
echo "<h2>Calendário - Ano $anoSelecionado</h2>";
echo "<form method='post'>";
echo "<select name='mes' onchange='this.form.submit()'>";
for ($mes = 1; $mes <= 12; $mes++) {
    $selecionado = ($mes == $mesSelecionado) ? 'selected' : '';
    echo "<option value='$mes' $selecionado>" . date('F', mktime(0, 0, 0, $mes, 1, $anoSelecionado)) . "</option>";
}
echo "</select>";
echo "</form>";

echo "<table>";
echo "<tr><th>Dom</th><th>Seg</th><th>Ter</th><th>Qua</th><th>Qui</th><th>Sex</th><th>Sáb</th></tr>";

// Dia inicial do mês
$diaInicial = date('N', mktime(0, 0, 0, $mesSelecionado, 1, $anoSelecionado));

// Número de células vazias antes do primeiro dia do mês
$celulasVazias = ($diaInicial - 1) % 7;

// Contador de dias
$contadorDias = 1;

// Loop para exibir os dias do calendário
for ($linha = 1; $linha <= 6; $linha++) {
    echo "<tr>";
    for ($coluna = 1; $coluna <= 7; $coluna++) {
        if ($celulasVazias > 0) {
            echo "<td></td>";
            $celulasVazias--;
        } elseif ($contadorDias <= $numeroDias) {
            $classeDiaAtual = ($contadorDias == $dataAtual && $mesSelecionado == $mesAtual && $anoSelecionado == $anoAtual) ? 'dia-atual' : '';
            echo "<td class='$classeDiaAtual'>$contadorDias</td>";
            $contadorDias++;
        }
    }
    echo "</tr>";
}

echo "</table>";
}
add_shortcode('calendario', 'calendario_shortcode');

// Adicione este código ao functions.php do seu tema ou em um plugin personalizado

function calendario2_shortcode($atts) {
    ob_start();
    ?>
    <style>
        .calendar-container {
            width: 100%;
            overflow-x: scroll;
            white-space: nowrap;
        }

        .calendar {
            display: inline-block;
            width: 100%;
            margin-right: 20px;
            box-sizing: border-box;
        }

        .month {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .calendar table {
            width: 100%;
        }

        .calendar th,
        .calendar td {
            padding: 5px;
            text-align: center;
        }

        .today {
            background-color: #f5f5f5;
        }
    </style>

    <div class="calendar-container">
        <?php
        $meses = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];

        foreach ($meses as $numero => $nome) {
            echo '<div class="calendar">';
            echo '<div class="month">' . $nome . '</div>';

            // Obter o número de dias no mês atual
            $numDias = cal_days_in_month(CAL_GREGORIAN, $numero, date('Y'));

            echo '<table>';
            echo '<tr><th>Domingo</th><th>Segunda-feira</th><th>Terça-feira</th><th>Quarta-feira</th><th>Quinta-feira</th><th>Sexta-feira</th><th>Sábado</th></tr>';
            echo '<tr>';
            $contador = 0;

            for ($i = 1; $i <= $numDias; $i++) {
                $contador++;
                if ($contador == date('j') && $numero == date('n') && date('Y')) {
                    echo '<td class="today">' . $i . '</td>';
                } else {
                    echo '<td>' . $i . '</td>';
                }
                if ($contador % 7 == 0) {
                    echo '</tr><tr>';
                }
            }

            echo '</tr>';
            echo '</table>';
            echo '</div>';
        }
        ?>
    </div>

    <script>
        window.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.calendar-container');
            const calendars = document.querySelectorAll('.calendar');
            const totalCalendars = calendars.length;
            let currentIndex = 0;

            function scrollToCurrentCalendar() {
                const currentCalendar = calendars[currentIndex];
                container.scrollLeft = currentCalendar.offsetLeft;
            }

            function updateCurrentIndex(newIndex) {
                if (newIndex >= 0 && newIndex < totalCalendars) {
                    currentIndex = newIndex;
                    scrollToCurrentCalendar();
                }
            }

            // Setas para avançar e retroceder o mês
            const prevButton = document.createElement('button');
            prevButton.innerText = '<';
            prevButton.addEventListener('click', function() {
                updateCurrentIndex(currentIndex - 1);
            });

            const nextButton = document.createElement('button');
            nextButton.innerText = '>';
            nextButton.addEventListener('click', function() {
                updateCurrentIndex(currentIndex + 1);
            });

            document.body.prepend(prevButton);
            document.body.appendChild(nextButton);

            // Atualizar o índice atual quando o valor do select for alterado
            const select = document.querySelector('select[name="mes"]');
            select.addEventListener('change', function() {
                const newIndex = parseInt(select.value) - 1;
                updateCurrentIndex(newIndex);
            });

            scrollToCurrentCalendar();
        });
    </script>
    <?php

    return ob_get_clean();
}
add_shortcode('calendario2', 'calendario2_shortcode');