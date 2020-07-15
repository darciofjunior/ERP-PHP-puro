<?
//Tratamento com as variáveis que vem por parâmetro ...
$representante  = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['representante'] : $_GET['representante'];
$pop_up         = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];

require('../../../../lib/segurancas.php');
//Se for acessado de dentro do OPC, não exibo o menu ...
if(empty($pop_up)) require('../../../../lib/menu/menu.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/financeiros.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');//Essa biblioteca é utilizada dentro da Biblioteca 'custos' ...
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>NÃO EXISTE(M) COMPRA(S) PARA ESSA FAMÍLIA.</font>";

if($passo == 1) {
    //Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_nome_fantasia 	= $_POST['txt_nome_fantasia'];
        $txt_razao_social 	= $_POST['txt_razao_social'];
        $opt_tipo_opc           = $_POST['opt_tipo_opc'];
    }else {
        $txt_nome_fantasia 	= $_GET['txt_nome_fantasia'];
        $txt_razao_social 	= $_GET['txt_razao_social'];
        $opt_tipo_opc           = $_GET['opt_tipo_opc'];
    }
    if(empty($representante)) $representante = '%';
    
//Aqui eu listo todos os Clientes do Representante logado ...
    $sql = "SELECT DISTINCT(c.`id_cliente`), c.`cod_cliente`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, 
            c.`id_uf`, c.`endereco`, c.`cidade`, c.`ddi_com`, c.`ddd_com`, c.`telcom`, c.`cnpj_cpf`, ct.`tipo` 
            FROM `clientes` c 
            INNER JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
            INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`id_representante` LIKE '$representante' 
            WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
            AND c.`razaosocial` LIKE '%$txt_razao_social%' 
            AND c.`ativo` = '1' ORDER BY c.`razaosocial` ";

    $sql_extra = "SELECT COUNT(DISTINCT(c.`id_cliente`)) AS total_registro 
                FROM `clientes` c 
                INNER JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
                INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`id_representante` LIKE '$representante' 
                WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
                AND c.`razaosocial` LIKE '%$txt_razao_social%' 
                AND c.`ativo` = '1' ORDER BY c.`razaosocial` ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
	
	if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = '<?=$PHP_SELF;?>?representante=<?=$representante;?>&valor=1'
    </Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Projetar OPC - Consultar Clientes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function projetar_opc(id_cliente, cliente) {
    /*Para o Cliente Teruya, será aberto um arquivo à parte porque esse arquivo de OPC será p/ fazermos 
    uma nova lista de Preço diferenciada para ele e para o Cofema ...*/
    /*if(id_cliente == 279 || id_cliente == 280 || id_cliente == 490 || id_cliente == 2068) {
        alert('P/ CLIENTE "'+cliente+'", VOCÊ SERÁ DIRECIONADO P/ UM ARQUIVO DE OPC DIFERENCIADO !')
        window.location = 'projetar_opc_teruya.php?representante=<?=$representante;?>&opt_tipo_opc=<?=$opt_tipo_opc;?>&id_cliente='+id_cliente
    }else {*/
        window.location = 'projetar_opc.php?passo=2&representante=<?=$representante;?>&opt_tipo_opc=<?=$opt_tipo_opc;?>&id_cliente='+id_cliente
    //}
}
</Script>
</head>
<body>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
	<tr class='linhacabecalho' align='center'>
            <td colspan='7'>
                Projetar OPC - Consultar Cliente(s)
                    <?
                            /****************Se foi passado Representante por parâmetro****************/
                            if(!empty($representante)) {
                    ?>
                            <font color='black' size='-1'>
                                     - Representante: 
                            </font>
                    <?
                                    //Busca o nome do Representante que foi passado por parâmetro ...
                                    $sql = "Select nome_fantasia 
                                            from `representantes` 
                                            where id_representante = '$representante' limit 1 ";
                                    $campos_rep = bancos::sql($sql);
                                    echo $campos_rep[0]['nome_fantasia'];
                            }
                            /**************************************************************************/
                    ?>
            </td>
	</tr>
	<tr class='linhadestaque' align='center'>
            <td colspan='2'>
                Cliente
            </td>
            <td>
                Tipo de Cliente
            </td>
            <td>
                Tel Com
            </td>
            <td>
                Endereço
            </td>
            <td>
                Cidade / UF
            </td>
            <td>
                CNPJ / CPF
            </td>
	</tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = "javascript:projetar_opc('".$campos[$i]['id_cliente']."', '".$campos[$i]['cliente']."')";
?>
	<tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
		<td onclick="<?=$url;?>" width='10'>
                    <a href="#">
                        <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td onclick="<?=$url;?>" align="left">
			<a href="#" class="link">
				<?=$campos[$i]['cliente'];?>
			</a>
		</td>
		<td>
			<?=$campos[$i]['tipo'];?>
		</td>
		<td>
		<?
                    if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
                    if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
                    if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
                    if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
		?>
		</td>
		<td align="left">
		<?
			echo $campos[$i]['endereco'];
			if(!empty($campos[$i]['endereco'])) {//Daí sim printa o complemento ...
				echo ', '.$campos[$i]['num_complemento'];
			}
		?>
		</td>
		<td>
		<?
			$sql = "SELECT `sigla` 
                                FROM `ufs` 
                                WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
			$campos_uf 	= bancos::sql($sql);
			echo $campos[$i]['cidade'].' / '.$campos_uf[0]['sigla'];
		?>
		</td>
		<td>
		<?
			if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                            if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                                echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                            }else {//CNPJ ...
                                echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                            }
                        }
		?>
		</td>
	</tr>
<?
	}
?>
	<tr class="linhacabecalho" align='center'>
		<td colspan='7'>
			<input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = '<?=$PHP_SELF;?>?representante=<?=$representante;?>'" class="botao">
		</td>
	</tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else if($passo == 2) {
//Tratamento com as variáveis que vem por parâmetro ...
    $id_cliente     = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_cliente'] : $_GET['id_cliente'];
    $opt_tipo_opc   = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['opt_tipo_opc'] : $_GET['opt_tipo_opc'];
    
    $taxa_financeira_vendas         = genericas::variavel(16);
    $fator_taxa_financeira_diaria   = pow((1 + $taxa_financeira_vendas / 100), 1 / 30);
    /*No cálculo da Tx Finaceira da OPC, usamos o prazo medio - 30 dias pois o nosso custo já saiu 
    com o prazo de 30 DDL ...*/
    $fator_taxa_financeira_opc      = pow($fator_taxa_financeira_diaria, ($_POST['txt_prazo_medio'] - 30));
    $fator_margem_lucro             = genericas::variavel(22);
    $impostos_federais              = genericas::variavel(34);

    //Se o País do Cliente for do Brasil, a moeda está em R$, do contrário será em Dólar ...
    $sql = "SELECT id_pais, IF(id_pais = 31, 'R$ ', 'U$ ') AS tipo_moeda, IF(nomefantasia = '', razaosocial, nomefantasia) AS cliente 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos_cliente                         = bancos::sql($sql);
    $id_pais                                = $campos_cliente[0]['id_pais'];
    $tipo_moeda                             = $campos_cliente[0]['tipo_moeda'];
    
    $vetor_logins_com_acesso_margens_lucro  = vendas::logins_com_acesso_margens_lucro();
?>
<html>
<head>
<title>.:: Projetar OPC - Consultar Clientes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/pecas_por_embalagem.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function controlar_combo(combo_multiplo) {
    var lima_selecionada = 0
    if(combo_multiplo.value == '') {
        for(i = 1; i < combo_multiplo.length; i++) combo_multiplo[i].selected = false
    }
}

function copiar_desconto_extra() {
    if(document.form.txt_desconto_extra_geral.value == '') {
        alert('DIGITE O DESCONTO EXTRA PARA COPIAR !')
        document.form.txt_desconto_extra_geral.focus()
        return false
    }
    //Verifico o N.º de Linhas ...
    var elementos = document.form.elements
    if(typeof(elementos['txt_qtde_proposta[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde_proposta[]'].length)
    }    
    //Se for Zero eu jogo vazio nas caixinhas ...
    if(document.form.txt_desconto_extra_geral.value == 0) document.form.txt_desconto_extra_geral.value = ''
    //Aqui eu copio o Desconto Extra p/ todas as linhas ...
    for(var i = 0; i < linhas; i++) {
        document.getElementById('txt_desconto_extra'+i).value = document.form.txt_desconto_extra_geral.value
        calcular_total_proposto(i)//Aqui eu passo o índice como zero, para que o sistema calcule o Desconto desde o 1º Item ...
    }
}

function copiar_qtde_geral() {
    if(document.form.txt_qtde_geral.value == '') {
        alert('DIGITE A QTDE PARA COPIAR !')
        document.form.txt_qtde_geral.focus()
        return false
    }
    //Verifico o N.º de Linhas ...
    var elementos = document.form.elements
    if(typeof(elementos['txt_qtde_proposta[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde_proposta[]'].length)
    }
    //Se for Zero eu jogo vazio nas caixinhas ...
    if(document.form.txt_qtde_geral.value == 0) document.form.txt_qtde_geral.value = '0'
    //Aqui eu copio o Desconto Extra p/ todas as linhas ...
    for(var i = 0; i < linhas; i++) {
        document.getElementById('txt_qtde_proposta'+i).value = document.form.txt_qtde_geral.value
        calcular_total_proposto(i)//Aqui eu passo o índice como zero, para que o sistema calcule o Desconto desde o 1º Item ...
    }
}

function calcular_margem_lucro(indice) {
    var preco_unitario_proposto         = eval(strtofloat(document.getElementById('txt_preco_unitario_proposto'+indice).value))
    var custo_ml_zero_proposta_pz_medio = eval(strtofloat(document.getElementById('hdd_custo_ml_zero_proposta_pz_medio'+indice).value))
    var margem_lucro_proposta           = (preco_unitario_proposto / custo_ml_zero_proposta_pz_medio - 1) * 100
    if(margem_lucro_proposta < 0) {//Se negativo ...
        document.getElementById('txt_margem_lucro_proposta'+indice).style.background    = 'red'
        document.getElementById('txt_margem_lucro_proposta'+indice).style.color         = 'white'
    }else {
        document.getElementById('txt_margem_lucro_proposta'+indice).style.background    = '#FFFFE1'
        document.getElementById('txt_margem_lucro_proposta'+indice).style.color         = 'gray'
    }
    document.getElementById('txt_margem_lucro_proposta'+indice).value = (preco_unitario_proposto / custo_ml_zero_proposta_pz_medio - 1) * 100
    document.getElementById('txt_margem_lucro_proposta'+indice).value = arred(document.getElementById('txt_margem_lucro_proposta'+indice).value, 1, 1)
}

function calcular_total_proposto(indice, ignorar_calculo_desconto_extra) {
    var preco_escolhido_para_usar   = document.getElementById('hdd_preco_unitario_proposto'+indice).value
    var qtde_proposta               = (document.getElementById('txt_qtde_proposta'+indice).value != '') ? eval(document.getElementById('txt_qtde_proposta'+indice).value) : 0
    if(typeof(ignorar_calculo_desconto_extra) == 'undefined') {//Por aqui eu cálculo o Preço baseado no Desconto digitado ...
        var desconto_extra  = (document.getElementById('txt_desconto_extra'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_desconto_extra'+indice).value)) : 0
        //Se existir Desconto Extra então ...
        var valor_desconto_extra    =   eval(preco_escolhido_para_usar) * (1 - desconto_extra / 100)
        document.getElementById('txt_preco_unitario_proposto'+indice).value = valor_desconto_extra
        document.getElementById('txt_preco_unitario_proposto'+indice).value = arred(document.getElementById('txt_preco_unitario_proposto'+indice).value, 2, 1)
    }else {
        var preco_unitario_digitado = eval(strtofloat(document.getElementById('txt_preco_unitario_proposto'+indice).value))
        var preco_unitario_com_20   = eval(preco_escolhido_para_usar)
        //Por aqui eu cálculo o Desconto, baseado no Preço Digitado ...
        document.getElementById('txt_desconto_extra'+indice).value = (1 - preco_unitario_digitado / preco_unitario_com_20) * 100
        document.getElementById('txt_desconto_extra'+indice).value = arred(document.getElementById('txt_desconto_extra'+indice).value, 2, 1)
    }
    var preco_unitario_proposto                                 = (document.getElementById('txt_preco_unitario_proposto'+indice).value != '') ? eval(strtofloat(document.getElementById('txt_preco_unitario_proposto'+indice).value)) : 0
    document.getElementById('txt_total_proposto'+indice).value  = qtde_proposta * preco_unitario_proposto
    document.getElementById('txt_total_proposto'+indice).value  = arred(document.getElementById('txt_total_proposto'+indice).value, 2, 1)
//Aqui eu recalculo o valor total geral proposto ...
    var elementos = document.form.elements
//Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['txt_qtde_proposta[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde_proposta[]'].length)
    }
    var total_custo_ml_zero_proposta_60ddl  = 0
    var total_geral_proposto                = 0
    for(var i = 0; i < linhas; i++) {
        var qtde_proposta   = (document.getElementById('txt_qtde_proposta'+i).value != '') ? document.getElementById('txt_qtde_proposta'+i).value : '0'
        if(qtde_proposta.indexOf('.') == -1) {
            qtde_proposta   = qtde_proposta.replace(',', '.')
        }else {
            qtde_proposta   = strtofloat(qtde_proposta)
        }
        var custo_ml_zero_proposta_60ddl   = (document.getElementById('hdd_custo_ml_zero_proposta_pz_medio'+i).value != '') ? document.getElementById('hdd_custo_ml_zero_proposta_pz_medio'+i).value : '0'
        if(custo_ml_zero_proposta_60ddl.indexOf('.') == -1) {
            custo_ml_zero_proposta_60ddl   = custo_ml_zero_proposta_60ddl.replace(',', '.')
        }else {
            custo_ml_zero_proposta_60ddl   = strtofloat(custo_ml_zero_proposta_60ddl)
        }
        total_custo_ml_zero_proposta_60ddl+= eval(qtde_proposta) * eval(custo_ml_zero_proposta_60ddl)
        
        //Só irá calcular o Total Proposto quando existir Preço Unitário Proposto ...
        if(document.getElementById('txt_preco_unitario_proposto'+i).value != '') total_geral_proposto+= eval(qtde_proposta) * eval(strtofloat(document.getElementById('txt_preco_unitario_proposto'+i).value))
    }
    document.getElementById('txt_total_geral_proposto').value = total_geral_proposto
    document.getElementById('txt_total_geral_proposto').value = arred(document.getElementById('txt_total_geral_proposto').value, 2, 1)
    
    document.getElementById('hdd_total_custo_ml_zero_proposta_60ddl').value = total_custo_ml_zero_proposta_60ddl
    document.getElementById('hdd_total_custo_ml_zero_proposta_60ddl').value = arred(document.getElementById('hdd_total_custo_ml_zero_proposta_60ddl').value, 1, 1)
    
    var total_geral_proposto                = eval(strtofloat(document.getElementById('txt_total_geral_proposto').value))
    var total_custo_ml_zero_proposta_60ddl  = eval(strtofloat(document.getElementById('hdd_total_custo_ml_zero_proposta_60ddl').value))
    
    if(total_custo_ml_zero_proposta_60ddl > 0) {
        document.getElementById('txt_mlm_proposta').value = (total_geral_proposto / total_custo_ml_zero_proposta_60ddl - 1) * 100
        document.getElementById('txt_mlm_proposta').value = arred(document.getElementById('txt_mlm_proposta').value, 1, 1)
    }else {
        document.getElementById('txt_mlm_proposta').value = '0,0'
    }
    //Calcula a Margem de Lucro depois que foi recalculado o Preço Proposto através do Desconto Extra concedido ...
    calcular_margem_lucro(indice)
}

function calcular_qtde_proposta() {
    var elementos                   = document.form.elements
//Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['txt_qtde_proposta[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde_proposta[]'].length)
    }
    if(document.form.txt_valor_proposto_pedido_rs.value != '') {
        var total_valor_mmv_pa          = 0
        var valor_proposto_pedido_rs    = eval(strtofloat(document.form.txt_valor_proposto_pedido_rs.value))     
        for(var i = 0; i < linhas; i++) total_valor_mmv_pa+= eval(document.getElementById('hdd_valor_mmv_pa'+i).value)
        
        var perc_pedido_vs_mmv          = (valor_proposto_pedido_rs / total_valor_mmv_pa)
        for(var i = 0; i < linhas; i++) {
            var qtde_prop_vlr_prop_ped = perc_pedido_vs_mmv * document.getElementById('hdd_mmv'+i).value
            if(document.getElementById('hdd_pecas_por_emb'+i).value == 0) document.getElementById('hdd_pecas_por_emb'+i).value  = 1
            var sugestao    = (parseInt(qtde_prop_vlr_prop_ped / document.getElementById('hdd_pecas_por_emb'+i).value) + 1) * document.getElementById('hdd_pecas_por_emb'+i).value
            document.getElementById('txt_qtde_proposta'+i).value = sugestao//A qtde proposta passa a ser a sugestão das Embalagens Fechadas ...
        }
    }else {
        for(var i = 0; i < linhas; i++) document.getElementById('txt_qtde_proposta'+i).value = 0
    }
}

function gravar_opc() {
    var prazo_medio     = document.form.txt_prazo_medio.value
    var prazo_medio_php = '<?=$_POST['txt_prazo_medio'];?>'
    if(prazo_medio != prazo_medio_php) {
        alert('O(S) PRAZO(S) DE PAGAMENTO(S) FOI(RAM) ALTERADO(S) !!!\n\nATUALIZE OS DADOS ANTES DE IMPRIMIR !')
        return false
    }
    var elementos = document.form.elements
//Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['txt_qtde_proposta[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_qtde_proposta[]'].length)
    }
//Aqui nessa parte do Script compara a quantidade de peças por embalagem para os produtos normais de linha
    for(var i = 0; i < linhas; i++) {
        //Aqui nessa parte do Script compara a quantidade de peças por embalagem para os produtos normais de linha
        if(document.getElementById('hdd_referencia'+i).value != 'ESP') {
            /***********************************Controle de Peças por Embalagem***********************************/
            //Todo o controle é feito dentro da Função de Peças por Embalagem ...
            var resultado = pecas_por_embalagem(document.getElementById('hdd_referencia'+i).value, document.getElementById('hdd_familia'+i).value, document.getElementById('txt_qtde_proposta'+i).value, document.getElementById('hdd_pecas_por_emb'+i).value)
            if(resultado == 0) {
                document.getElementById('txt_qtde_proposta'+i).focus()
                document.getElementById('txt_qtde_proposta'+i).select()
                return false
            }
            /*****************************************************************************************************/
        }
    }
    document.form.action = 'gravar_opc.php'
    document.form.target = 'GRAVAR_OPC'
    nova_janela('gravar_opc.php', 'GRAVAR_OPC', 'F')
    document.form.submit()
}

function imprimir_html() {
    var prazo_medio     = document.form.txt_prazo_medio.value
    var prazo_medio_php = '<?=$_POST['txt_prazo_medio'];?>'
    if(prazo_medio != prazo_medio_php) {
        alert('O(S) PRAZO(S) DE PAGAMENTO(S) FOI(RAM) ALTERADO(S) !!!\n\nATUALIZE OS DADOS ANTES DE IMPRIMIR !')
        return false
    }
    window.print()
}

function calcular_prazo_medio() {
    var qtde_prazos = 0
    var prazo_a     = (document.form.txt_prazo_a.value != 0) ? eval(document.form.txt_prazo_a.value) : 0
    var prazo_b     = (document.form.txt_prazo_b.value != 0) ? eval(document.form.txt_prazo_b.value) : 0
    var prazo_c     = (document.form.txt_prazo_c.value != 0) ? eval(document.form.txt_prazo_c.value) : 0
    var prazo_d     = (document.form.txt_prazo_d.value != 0) ? eval(document.form.txt_prazo_d.value) : 0
    
    if(prazo_a > 0 && prazo_b > 0 && prazo_c > 0 && prazo_d > 0)  {
        var qtde_prazos = 4
    }else if(prazo_a > 0 && prazo_b > 0 && prazo_c > 0)  {
        var qtde_prazos = 3
    }else if(prazo_a > 0 && prazo_b > 0)  {
        var qtde_prazos = 2
    }else if(prazo_a > 0) {
        var qtde_prazos = 1
    }
    if(qtde_prazos > 0) {//Se existe pelo menos 1 prazo, o sistema cai na fórmula abaixo ...
        document.form.txt_prazo_medio.value = (prazo_a + prazo_b + prazo_c + prazo_d) / qtde_prazos
        document.form.txt_prazo_medio.value = arred(document.form.txt_prazo_medio.value, 1, 1)
    }else {
        document.form.txt_prazo_medio.value = ''
    }
}

function verificar_prazos() {
    var prazo_a     = (document.form.txt_prazo_a.value != 0) ? eval(document.form.txt_prazo_a.value) : 0
    var prazo_b     = (document.form.txt_prazo_b.value != 0) ? eval(document.form.txt_prazo_b.value) : 0
    var prazo_c     = (document.form.txt_prazo_c.value != 0) ? eval(document.form.txt_prazo_c.value) : 0
    var prazo_d     = (document.form.txt_prazo_d.value != 0) ? eval(document.form.txt_prazo_d.value) : 0
    //Segurança com o preenchimento dos Prazos ...
    
    if(document.form.txt_prazo_d.value != '') {
        if(prazo_d <= prazo_c) {
            alert('PRAZO D INVÁLIDO !!!')
            document.form.txt_prazo_d.select()
            return false
        }
    }
    if(document.form.txt_prazo_c.value != '') {
        if(prazo_c <= prazo_b) {
            alert('PRAZO C INVÁLIDO !!!')
            document.form.txt_prazo_c.select()
            return false
        }
    }
    if(document.form.txt_prazo_b.value != '') {
        if(prazo_b <= prazo_a) {
            alert('PRAZO B INVÁLIDO !!!')
            document.form.txt_prazo_b.select()
            return false
        }
    }
    /********************************************/
}

function atualizar_dados() {
//Tipo de Preço ...
    if(!combo('form', 'cmb_tipo_preco', '', 'SELECIONE O TIPO DE PREÇO !')) {
        return false
    }
    alert('DEPENDENDO DO FILTRO QUE FOR FEITO ... O SISTEMA PODERÁ DEMORAR UM TEMPO BEM ELEVADO PORQUE ESTÁ SENDO FEITO UM LEVANTAMENTO DE TODO HISTÓRICO DO CLIENTE !')
    document.form.hdd_submetido.value   = 1
    document.form.action                = ''
    document.form.submit()
}
</Script>
</head>
<body onload="calcular_prazo_medio();document.form.txt_referencia_discriminacao.focus()">
<form name="form" method="post" action=''>
<!--***********Controles de Tela***********-->
<input type="hidden" name="id_cliente" value="<?=$id_cliente;?>">
<input type="hidden" name="pop_up" value="<?=$pop_up;?>">
<input type="hidden" name="hdd_submetido" value='0'>
<input type="hidden" name="opt_tipo_opc" value='<?=$opt_tipo_opc;?>'>
<!--***************************************-->
<table width='95%' border='1' cellspacing ='1' cellpadding='1' align='center'>
    <tr class="linhacabecalho" align='center'>
        <td colspan='27'>
            <?
                if($opt_tipo_opc == 1) {//Produtos Adquiridos ...
                    echo 'PRODUTO(S) ADQUIRIDO(S) PELO CLIENTE: ';
                }else {//Produtos Top(s) não Adquiridos (Curva ABC) ...
                    echo 'PRODUTO(S) TOP(S) NÃO ADQUIRIDO(S) PELO CLIENTE (CURVA ABC): ';
                    /*Nessa opção esse checkbox sempre terá que ficar desabilitado, afinal 
                    nunca houve nenhuma venda nesse sentido ...*/
                    $disabled_options = 'disabled';
                }
                //Somente para o Roberto que nunca irá travar nada ...
                if($_SESSION['id_funcionario'] == 62) $disabled_options = '';
                
            ?>
            <a href = '../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$id_cliente;?>&pop_up=1' class='html5lightbox'>
                <font color='black' size='-1'>
                    <?=$campos_cliente[0]['cliente'];?>
                </font>
                &nbsp;
                <img src = '../../../../imagem/propriedades.png' title="Detalhes de Cliente" alt="Detalhes de Cliente" style="cursor:pointer" border="0">
            </a>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='17'>
            <font size='2'>
                <b>Família: </b>
            </font>
            <select name='cmb_familia[]' title='Selecione uma Família' onchange='controlar_combo(this)' multiple size='5' class='combo'>
                <option value='' style="color:red" <?=$selected_familia;?>>SELECIONE</option>
                <?
                        $sql = "SELECT id_familia, UPPER(nome) AS nome 
                                FROM `familias` 
                                WHERE ativo = '1' ORDER BY nome ";
                        $campos_familia = bancos::sql($sql);
                        $linhas_familia = count($campos_familia);
                        for($i = 0; $i < $linhas_familia; $i++) {
                            $selected_familia = '';//Limpo a variável para não herdar valor do Loop Anterior ...
                            if(!empty($_POST['cmb_familia'])) {
                                if(in_array($campos_familia[$i]['id_familia'], $_POST['cmb_familia'])) $selected_familia = 'selected';
                            }
                ?>
                <option value="<?=$campos_familia[$i]['id_familia'];?>" <?=$selected_familia;?>><?=$campos_familia[$i]['nome'];?></option>
                <?
                        }
                ?>
            </select>
            &nbsp;
            <font size='2'>
                <b>Empresa Divisão: </b>
            </font>
            <select name='cmb_empresa_divisao[]' title='Selecione uma Família' onchange='controlar_combo(this)' multiple size='5' class='combo'>
                <option value="" style="color:red" <?=$selected_emp_div;?>>SELECIONE</option>
                <?
                        $sql = "SELECT id_empresa_divisao, UPPER(razaosocial) AS razaosocial 
                                FROM `empresas_divisoes` 
                                WHERE ativo = '1' ORDER BY razaosocial ";
                        $campos_empresa_divisao = bancos::sql($sql);
                        $linhas_empresa_divisao = count($campos_empresa_divisao);
                        for($i = 0; $i < $linhas_empresa_divisao; $i++) {
                            $selected_emp_div = '';//Limpo a variável para não herdar valor do Loop Anterior ...
                            if(!empty($_POST['cmb_empresa_divisao'])) {
                                if(in_array($campos_empresa_divisao[$i]['id_empresa_divisao'], $_POST['cmb_empresa_divisao'])) $selected_emp_div = 'selected';
                            }
                ?>
                <option value="<?=$campos_empresa_divisao[$i]['id_empresa_divisao'];?>" <?=$selected_emp_div;?>><?=$campos_empresa_divisao[$i]['razaosocial'];?></option>
                <?
                        }
                ?>
            </select>
            <br/>
            <font size='2'>
                <b>Ref / Disc: </b>
            </font>
            <input type='text' name='txt_referencia_discriminacao' value='<?=$_POST['txt_referencia_discriminacao'];?>' size='60' class='caixadetexto'>
            <?
                $checked_mostrar_esp = (!empty($_POST['chkt_mostrar_esp'])) ? 'checked' : '';
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='checkbox' name='chkt_mostrar_esp' value='S' id='chkt_mostrar_esp' class='checkbox' <?=$checked_mostrar_esp;?> <?=$disabled_options;?>>
            <label for='chkt_mostrar_esp'>
                Mostrar ESP
            </label>
        </td>
        <td colspan='10'>
            <font size='2'>
                <b>Tipo de Preço: </b>
            </font>
            <select name='cmb_tipo_preco' title='Selecione um Tipo de Preço' class='combo'>
                <?
                    if($_POST['cmb_tipo_preco'] == 1) {//Preço c/ 20% ...
                        $selectedtipopreco1  = 'selected';
                    }else if($_POST['cmb_tipo_preco'] == 2) {//Preço do último Pedido ...
                        $selectedtipopreco2  = 'selected';
                    }else if($_POST['cmb_tipo_preco'] == 3) {
                        $selectedtipopreco3  = 'selected';
                    }else {
                        $selectedtipopreco  = 'selected';
                    }
                ?>
                <option value='' style='color:red' <?=$selectedtipopreco;?>>SELECIONE</option>
                <option value='1' <?=$selectedtipopreco1;?>>USAR PREÇO C/ 20%</option>
                <option value='2' <?=$selectedtipopreco2;?>>USAR PREÇO DO ÚLTIMO PEDIDO</option>
                <option value='3' <?=$selectedtipopreco3;?>>USAR PREÇO PROMOCIONAL</option>
            </select>
            &nbsp;
            <font size='2'>
                <b>Ano(s): </b>
            </font>
            <select name='cmb_qtde_anos' title='Selecione a Qtde de Anos' class='combo' <?=$disabled_options;?>>
            <?
                if(empty($_POST['cmb_qtde_anos'])) $_POST['cmb_qtde_anos'] = 3;

                for($i = 1; $i <= 6; $i++) {
                    $selected = ($i == $_POST['cmb_qtde_anos']) ? 'selected' : '';
            ?>
                    <option value='<?=$i;?>' <?=$selected;?>><?=$i;?> ANO(S)</option>
            <?
                }
            ?>
            </select>
            &nbsp;
            <font size='2'>
                <b>Tipo de Nota: </b>
            </font>
            <?
                if(empty($_POST['opt_tipo_nota'])) {//Aqui é para o caso de quando carrega a Tela e não dar erro ...
                    $_POST['opt_tipo_nota'] = 'NF';
                    $checkedn       = 'checked';
                }else {
                    if($_POST['opt_tipo_nota'] == 'NF') {
                        $checkedn   = 'checked';
                    }else {
                        $checkeds   = 'checked';
                    }
                }
            ?>
            <input type='radio' name='opt_tipo_nota' value='NF' title='Selecione o Tipo de Nota' id='tipo_nf' <?=$checkedn;?>>
            <label for='tipo_nf'>NF</label>
            <input type='radio' name='opt_tipo_nota' value='SGD' title='Selecione o Tipo de Nota' id='tipo_sgd' <?=$checkeds;?>>
            <label for='tipo_sgd'>SGD</label>
            <br/><br/>
            &nbsp;
            <?
                //Sugestão para quando acabar de carregar a tela ...
                if(empty($_POST['txt_prazo_a'])) $_POST['txt_prazo_a'] = 60;
            ?>
            <font size='2'>
                <b>Prazo A: </b>
            </font>
            <input type="text" name='txt_prazo_a' value="<?=$_POST['txt_prazo_a'];?>" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};calcular_prazo_medio()" onblur='verificar_prazos()' maxlength='3' size='5' class="caixadetexto">
            &nbsp;
            <font size='2'>
                <b>Prazo B: </b>
            </font>
            <input type="text" name='txt_prazo_b' value="<?=$_POST['txt_prazo_b'];?>" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};calcular_prazo_medio()" onblur='verificar_prazos()' maxlength='3' size='5' class="caixadetexto">
            &nbsp;
            <font size='2'>
                <b>Prazo C: </b>
            </font>
            <input type="text" name='txt_prazo_c' value="<?=$_POST['txt_prazo_c'];?>" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};calcular_prazo_medio()" onblur='verificar_prazos()' maxlength='3' size='5' class="caixadetexto">
            &nbsp;
            <font size='2'>
                <b>Prazo D: </b>
            </font>
            <input type="text" name='txt_prazo_d' value="<?=$_POST['txt_prazo_d'];?>" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};calcular_prazo_medio()" onblur='verificar_prazos()' maxlength='3' size='5' class="caixadetexto">
            &nbsp;-&nbsp;
            <font size='2'>
                <b>Prazo Médio: </b>
            </font>
            <input type="text" name='txt_prazo_medio' value="<?=$_POST['txt_prazo_medio'];?>" onfocus="document.form.txt_referencia_discriminacao.focus()" maxlength='3' size='5' class='textdisabled'>
            
            <br/><br/>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <?
                $checked_agrupar_filiais = (!empty($_POST['chkt_agrupar_filiais'])) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_agrupar_filiais' value='S' id='chkt_agrupar_filias' class='checkbox' <?=$checked_agrupar_filiais;?>>
            <label for='chkt_agrupar_filias'>
                Agrupar Filiais
            </label>
            <?
                //Somente por essa opção é que eu mostro esse campo ...
                if($opt_tipo_opc == 2) {//Produtos Top(s) não Adquiridos (Curva ABC) ...
            ?>
            -&nbsp;
            Valor Proposto do Pedido em R$
            <input type="text" name="txt_valor_proposto_pedido_rs" value="<?=$_POST['txt_valor_proposto_pedido_rs'];?>" maxlength='10' size='12' onKeyUp="verifica(this, 'moeda_especial', '2', '', event);calcular_qtde_proposta()" class="caixadetexto">
            -&nbsp;
            TOP:
            <select name='cmb_status_top' title='Selecione o status do TOP' class='combo'>
                <?
                    if($_POST['cmb_status_top'] == 1) {
                        $selected_status_top1 = 'selected';
                    }else if($_POST['cmb_status_top'] == 2) {
                        $selected_status_top2 = 'selected';
                    }
                ?>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1' <?=$selected_status_top1;?>>TOP (A)</option>
                <option value='2' <?=$selected_status_top2;?>>TOP (B)</option>
            </select>
            <?
                }
            ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='button' name='cmd_atualizar_dados' value='Atualizar Dados' title='Atualizar Dados' onclick='return atualizar_dados()' class='botao'>
        </td>
    </tr>
<?
    if(!empty($_POST['hdd_submetido'])) {
        /*Como esse processamento pode ser muito pesado, deixo o servidor operar excepcionalmente em até 
        20 minutos para essa tela ...*/
        set_time_limit(1200);
        
        if($_POST['cmb_familia'][0] == '') {//Significa que não foi selecionada nenhuma opção, sendo assim trago todas as Famílias ...
            $familias_selecionadas  = '%';
        }else {
            for($i = 0; $i < count($_POST['cmb_familia']); $i++) {
                if($_POST['cmb_familia'][$i] != '') $familias_selecionadas.= $_POST['cmb_familia'][$i].', ';
            }
            $familias_selecionadas  = substr($familias_selecionadas, 0, strlen($familias_selecionadas) - 2);
        }

        if($familias_selecionadas == '%') {
            $condicao_familia = " AND f.id_familia LIKE '$familias_selecionadas' ";
        }else {
            $condicao_familia = " AND f.id_familia IN ($familias_selecionadas) ";
        }
        $cmb_familia_filtro = (!empty($_POST['cmb_familia'])) ? $_POST['cmb_familia'] : '%';
        
        
        if($_POST['cmb_empresa_divisao'][0] == '') {//Significa que não foi selecionada nenhuma opção, sendo assim trago todas as Divisões ...
            $emp_div_selecionadas   = '%';
        }else {
            for($i = 0; $i < count($_POST['cmb_empresa_divisao']); $i++) {
                if($_POST['cmb_empresa_divisao'][$i] != '') $emp_div_selecionadas.= $_POST['cmb_empresa_divisao'][$i].', ';
            }
            $emp_div_selecionadas  = substr($emp_div_selecionadas, 0, strlen($emp_div_selecionadas) - 2);
        }

        if($emp_div_selecionadas == '%') {
            $condicao_emp_divisao = " AND ed.id_empresa_divisao LIKE '$emp_div_selecionadas' ";
        }else {
            $condicao_emp_divisao = " AND ed.id_empresa_divisao IN ($emp_div_selecionadas) ";
        }
        if(!empty($_POST['chkt_agrupar_filiais'])) {
            $sql = "SELECT `id_cliente` 
                    FROM `clientes` 
                    WHERE `id_cliente_matriz` = '$id_cliente' ";
            $campos     = bancos::sql($sql);
            $linhas	= count($campos);
            for($i = 0; $i < $linhas; $i++) $id_clientes_filiais.= "'".$campos[$i]['id_cliente']."', ";//Aqui são as filiais ...
            $id_clientes_filiais.= "'".$id_cliente."'";//Aqui eu também concateno com a própria Matriz, que foi passada por parâmetro ...
            $condicao_cliente_nfs       = "AND nfs.`id_cliente` IN ($id_clientes_filiais) ";
            $condicao_cliente_pedidos   = " pv.`id_cliente` IN ($id_clientes_filiais) ";
        }else {
            $condicao_cliente_nfs       = "AND nfs.`id_cliente` = '$id_cliente' ";
            $condicao_cliente_pedidos   = " pv.`id_cliente` = '$id_cliente' ";
        }
        $condicao_status_top = (!empty($_POST['cmb_status_top'])) ? " AND pa.`status_top` = '$_POST[cmb_status_top]' " : " AND pa.`status_top` LIKE '%' ";
        if(empty($_POST['chkt_mostrar_esp'])) $condicao_esp = " AND pa.referencia <> 'ESP' ";
/************************Produtos Vendidos para o Cliente desde 2006**********************/
        if($opt_tipo_opc == 1) {//Produtos Adquiridos ...
            $sql = "SELECT SUM(nfsi.qtde - nfsi.qtde_devolvida) AS qtde_anual, c.id_uf, CONCAT(c.razaosocial, ' - ',c.nomefantasia) AS cliente, 
                    f.id_familia, ged.id_empresa_divisao, ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac, 
                    pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.preco_unitario, pa.preco_promocional_b, 
                    pa.preco_export, pa.mmv, pa.status_top, nfs.data_emissao, YEAR(nfs.data_emissao) AS ano, 
                    pvi.`id_orcamento_venda_item` 
                    FROM `clientes` c 
                    INNER JOIN `nfs` on nfs.id_cliente = c.id_cliente 
                    INNER JOIN `nfs_itens` nfsi on nfsi.id_nf = nfs.id_nf 
                    INNER JOIN `pedidos_vendas_itens` pvi on pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
                    INNER JOIN `produtos_acabados` pa on pa.id_produto_acabado = pvi.id_produto_acabado AND (pa.referencia LIKE '$_POST[txt_referencia_discriminacao]%' OR pa.discriminacao LIKE '%$_POST[txt_referencia_discriminacao]%') $condicao_esp 
                    INNER JOIN `gpas_vs_emps_divs` ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `empresas_divisoes` ed on ed.id_empresa_divisao = ged.id_empresa_divisao $condicao_emp_divisao 
                    INNER JOIN `grupos_pas` gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN `familias` f ON f.id_familia = gpa.id_familia $condicao_familia 
                    WHERE YEAR(nfs.data_emissao) >= '2006' 
                    $condicao_cliente_nfs 
                    GROUP BY pa.id_produto_acabado, YEAR(nfs.data_emissao) ORDER BY pa.discriminacao, YEAR(nfs.data_emissao) ";
        }else {//Produtos Top(s) não Adquiridos (Curva ABC) ...
            //Aqui eu busco todos os PA(s) que são TOP (A e B) ...
            $sql = "SELECT id_produto_acabado 
                    FROM `produtos_acabados` 
                    WHERE `status_top` IN (1, 2) ORDER BY id_produto_acabado ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            for($i = 0; $i < $linhas; $i++) $id_pas_tops.= $campos[$i]['id_produto_acabado'].', ';
            $id_pas_tops    = substr($id_pas_tops, 0, strlen($id_pas_tops) - 2);
            
            //Aqui eu verifico tudo o que foi Vendido dos PA(s) TOP(s) acima p/ esse Cliente ...
            $sql = "SELECT DISTINCT(pvi.id_produto_acabado) 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda AND pvi.id_produto_acabado IN ($id_pas_tops) 
                    WHERE $condicao_cliente_pedidos GROUP BY pvi.id_produto_acabado ORDER BY pvi.id_produto_acabado ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            $vetor_pas_tops = explode(',', $id_pas_tops);//Esse vetor será muito importante ...
            
            for($i = 0; $i < $linhas; $i++) {
                /*Se esse PA TOP foi faturado dentre todos os Top´s acima, esse não precisa ser 
                oferecido para o Cliente, afinal ele já comprou algum dia ...*/
                if(in_array($campos[$i]['id_produto_acabado'], $vetor_pas_tops)) {
                    $indice = array_search($campos[$i]['id_produto_acabado'], $vetor_pas_tops); //Localizo o Índice no Vetor ...
                    unset($vetor_pas_tops[$indice]);//Apaga o Índice do Vetor ...
                }
            }
            /*Esses Top´s $id_pas_tops_nao_comprados, são os que realmente me interessam p/ ofertar 
            para o Cliente comprar ...*/
            $id_pas_tops_nao_comprados = implode(',', $vetor_pas_tops);
            
            $sql = "SELECT f.id_familia, ged.id_empresa_divisao, ged.desc_base_a_nac, ged.desc_base_b_nac, ged.acrescimo_base_nac, 
                    pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.preco_unitario, pa.preco_promocional_b, pa.preco_export, pa.mmv, pa.status_top 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged on ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `empresas_divisoes` ed on ed.id_empresa_divisao = ged.id_empresa_divisao $condicao_emp_divisao 
                    INNER JOIN `grupos_pas` gpa on gpa.id_grupo_pa = ged.id_grupo_pa 
                    INNER JOIN `familias` f ON f.id_familia = gpa.id_familia $condicao_familia 
                    WHERE (pa.referencia LIKE '$_POST[txt_referencia_discriminacao]%' OR pa.discriminacao LIKE '%$_POST[txt_referencia_discriminacao]%') 
                    AND pa.`id_produto_acabado` IN ($id_pas_tops_nao_comprados) 
                    $condicao_status_top 
                    ORDER BY pa.discriminacao ";
        }
        $campos = bancos::sql($sql, $inicio, 2000, 'sim', $pagina);
        $linhas	= count($campos);
    }
    //Variáveis que serão utilizadas mais abaixo ...
    $indice = 0;
    $ano_inicial_para_media = (date('Y') - $_POST['cmb_qtde_anos']);//Sempre pego a média a partir do ano anterior, pq é um ano que já está fechado a Qtde Comprada ...
    if($linhas == 0) {//Significa que não existe nenhuma Compra do Cliente p/ a Família selecionada ...
?>
<table width="95%" align='center'>
	<tr align='center'>
            <td>
                <?=$mensagem[2];?>
            </td>
	</tr>
        <tr align='center'>
            <td>
                <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '<?=$PHP_SELF.$parametro;?>'" style='color:red' class='botao'>
            </td>
	</tr>
</table>
<?
    }else {//Existe pelo menos uma Compra da Família ou Famílias ...
?>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>Ref</td>
        <td rowspan='2'>Discriminação</td>
        <td rowspan='2'>T<br>O<br>P</td>
<?
            //Lista desde 2006 pq foram anos em que as vendas bombaram ...    
            for($ano = 2006; $ano <= date('Y'); $ano++) {
?>
        <td rowspan='2'><?=$ano;?></td>
<?
            }
?>
        <td rowspan='2'>
            Média <?=$_POST['cmb_qtde_anos'];?> anos
        </td>
        <td rowspan='2'>
        <?
            if($opt_tipo_opc == 1) {//Produtos Adquiridos ...
        ?>
            <font title='Média Anual de Vendas' style='cursor:help'>
                MAV
            </font>
        <?
            }else {//Produtos Top(s) não Adquiridos (Curva ABC) ...
        ?>
            <font title='Média Mensal de Vendas' style='cursor:help'>
                MMV
            </font>
        <?
            }
        ?>
        </td>
        <td colspan='5'>Último Pedido</td>
        <td rowspan='2'>
        <?
            if($id_pais == 31) {//Se for do Brasil, mostra este rótulo ...
        ?>
            Pço. <?=$tipo_moeda;?> Desc Extra 20%
            <font color='yellow'>
                <?if($_POST['opt_tipo_nota'] == 'NF') {echo '+Dif.ICMS NF';}else {echo 'SGD';}?>
            </font>
        <?
            }else {//Se for do Estrangeiro ...
        ?>
            Pço. <?=$tipo_moeda;?> Desc Extra 20%
        <?
            }
        ?>
        </td>
        <td title='Estoque Comprometido' style='cursor:help' rowspan='2'>
            E.C.
        </td>
        <td rowspan='2'>
            Qtde Proposta 
            <?
                //Produtos Adquiridos ...
                if($opt_tipo_opc == 1) echo 'p/ o Semestre';
            ?>
            <input type="text" name="txt_qtde_geral" title="Digite a Qtde Geral" onKeyUp="verifica(this, 'aceita', 'numeros', '', event)" size="3" maxlength="3" class="caixadetexto">
            <img src="../../../../imagem/seta_abaixo.gif" title='Copiar Qtde' style='cursor:help' width="12" height="12" onclick="copiar_qtde_geral()">
        </td>
        <td rowspan='2'>
            Desc. Extra 20% 
            <font color='purple'>
                <b>+ ?</b>
            </font>
            <br>
            <input type="text" name="txt_desconto_extra_geral" title="Digite o Desconto Extra Geral" onKeyUp="verifica(this, 'moeda_especial', '2', '', event)" size="7" maxlength="6" class="caixadetexto">
            <img src="../../../../imagem/seta_abaixo.gif" title='Copiar Desconto Extra' style='cursor:help' width="12" height="12" onclick="copiar_desconto_extra()">
        </td>
        <td rowspan='2'>Pço Unit <?=$tipo_moeda;?> Proposto</td>
        <td rowspan='2'>
            M.L. % Proposta
            <font color='yellow'>
                <?=$_POST['txt_prazo_medio'];?> DDL
            </font>
        </td>
        <td rowspan='2'>
            Com. % Estimada
        </td>
        <td rowspan='2'>Total <?=$tipo_moeda;?> Proposto</td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>N.º / Data</td>
        <td>Vlr. Unit. <?=$tipo_moeda;?></td>
        <td>Forma Pgto.</td>
        <td>ML Atual %</td>
        <td>Com. %</td>
    </tr>
<?
            if($opt_tipo_opc == 2) {//Produtos Top(s) não Adquiridos (Curva ABC) ...
                //Essa UF, será utilizada mais abaixo nos cálculos ...
                $sql = "SELECT id_uf 
                        FROM `clientes` 
                        WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
                $campos_uf  = bancos::sql($sql);
                //Preciso do Pço de Lista Líquido de cada item p/ calcular o Valor Total de MMV do PA ...
                for($i = 0; $i < $linhas; $i++) {
                    if($id_pais == 31) {//Se for do Brasil ...
                        $desconto_cliente                   = 20;
                        $pco_lista_liq_para_calc_geral      = $campos[$i]['preco_unitario'] * (1 - $campos[$i]['desc_base_a_nac'] / 100) * (1 - $campos[$i]['desc_base_b_nac'] / 100) * (1 + $campos[$i]['acrescimo_base_nac'] / 100) * (1 - $desconto_cliente / 100);
                        $dados_impostos_pa_sp               = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], 1);
                        $icms_reducao_sp                    = round($dados_impostos_pa_sp['icms'] * (1 - $dados_impostos_pa_sp['reducao'] / 100), 2);

                        $dados_impostos_pa_cliente          = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], $campos_uf[0]['id_uf'], $id_cliente);
                        $icms_reducao_cliente               = round($dados_impostos_pa_cliente['icms'] * (1 - $dados_impostos_pa_cliente['reducao'] / 100), 2);

                        if($_POST['opt_tipo_nota'] == 'SGD') {//Se for SGD, sempre abate o ICMS de SP ...
                            $pco_lista_liq_para_calc_geral*= (1 - $icms_reducao_sp / 100);
                        }else {//Se for NF ...
                            $diferenca_icms = $icms_reducao_sp - $icms_reducao_cliente;
                            $pco_lista_liq_para_calc_geral*= (1 - $diferenca_icms / 100);
                        }
                        $pco_lista_liq_para_calc_geral      = round($pco_lista_liq_para_calc_geral, 2);//Arredondo esse valor para evitar futuros erros ...
                    }else {//Se for Internacional ...
                        $pco_lista_liq_para_calc_geral      = $campos[$i]['preco_export'];
                    }
                    $valor_mmv_pa               = $campos[$i]['mmv'] * $pco_lista_liq_para_calc_geral;
                    $total_valor_mmv_pa+=       $valor_mmv_pa;
                }               
                /*Cálculo da Perc de Pedido vs MMV é um fator de Correção do MMV para o Valor 
                Proposto do Pedido ...*/
                $valor_proposto_pedido_rs   = str_replace('.', '', $_POST['txt_valor_proposto_pedido_rs']);
                $valor_proposto_pedido_rs   = str_replace(',', '.', $valor_proposto_pedido_rs);
                $perc_pedido_vs_mmv         = ($valor_proposto_pedido_rs / $total_valor_mmv_pa);
            }
            //Aqui eu guardo no Vetor do PA a respectiva Qtde Comprada para o Respectivo ano ...
            for($i = 0; $i < $linhas; $i++) $vetor_qtde_do_ano_pa[$campos[$i]['id_produto_acabado']][$campos[$i]['ano']] = $campos[$i]['qtde_anual'];
            for($i = 0; $i < $linhas; $i++) {
                if($id_pa_antigo != $campos[$i]['id_produto_acabado']) {
?>
	<tr class="linhanormal" align='center'>
		<td>
                    <a href="javascript:nova_janela('../../relatorio/pedidos_emitidos/rel_venda_produto.php?passo=1&id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>&sumir_botao=1', 'VISUALIZAR_PEDIDOS', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Pedidos - Últimos 6 meses" class="link">
                        <?=$campos[$i]['referencia'];?>
                    </a>
                    <!--Esses hiddens serão utilizados para controles de JavaScript-->
                    <input type="hidden" name="hdd_produto_acabado[]" value="<?=$campos[$i]['id_produto_acabado'];?>">
                    <input type="hidden" name="hdd_referencia[]" id="hdd_referencia<?=$indice;?>" value="<?=$campos[$i]['referencia'];?>">
		</td>
		<td align="left">
                    <?=$campos[$i]['discriminacao'];?>
		</td>
                <td>
                <?
                    if($campos[$i]['status_top'] == 1) {
                        echo  "<font color='green' style='cursor:help;' title='1º 50% dos PA´s TOP'><b>A</b></font>";
                    }else if($campos[$i]['status_top'] == 2) {
                        echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'><b>B</b></font>";
                    }else {
                        echo '&nbsp;';
                    }
                ?>
		</td>
                <?
                    for($ano = 2006; $ano <= date('Y'); $ano++) {
                        //Somente o Ano atual que eu mudo a cor da coluna ...
                        $bgcolor    = ($ano == date('Y')) ? '#FFFFDF' : '';
                        $color      = ($ano == date('Y')) ? 'red' : '';
                        if($vetor_qtde_do_ano_pa[$campos[$i]['id_produto_acabado']][$ano] > 0) {
                ?>
		<td align="right" bgcolor="<?=$bgcolor;?>">
                    <font color='<?=$color;?>'>	
                        <b><?=number_format($vetor_qtde_do_ano_pa[$campos[$i]['id_produto_acabado']][$ano], 0, '', '.');?></b>
                    </font>
		</td>
                <?
                        }else {
                            echo '<td bgcolor="'.$bgcolor.'">&nbsp;</td>';
                        }
                        //Eu só não adiciono a Qtde do ano atual porque, não é um ano cheio p/ entrar nas fórmulas ...
                        if($ano >= $ano_inicial_para_media && $ano < date('Y')) {
                            $vetor_soma_qtde_anos_pa[$campos[$i]['id_produto_acabado']]+= $vetor_qtde_do_ano_pa[$campos[$i]['id_produto_acabado']][$ano];
                        }
                        if($ano == date('Y')) $qtde_do_ultimo_ano_pa = $vetor_qtde_do_ano_pa[$campos[$i]['id_produto_acabado']][$ano];
                    }
                    $media_qtde_pa = round($vetor_soma_qtde_anos_pa[$campos[$i]['id_produto_acabado']] / $_POST['cmb_qtde_anos'], 0);
                ?>
                <td>
                    <?=$media_qtde_pa;?>
		</td>
                <td>
                    <?=number_format($campos[$i]['mmv'], 2, ',', '.');?>
		</td>
                <td bgcolor='#CECECE'>
                <?
                    //Aqui eu limpo essas variáveis para não dar problema com valores de Loop Anterior ...
                    $prazo_faturamento              = '';
                    $ultimo_preco_unitario_vendido  = '';
                    $condicao_empresa_pedido        = ($_POST['opt_tipo_nota'] == 'NF') ? ' AND pv.id_empresa IN (1, 2) ' : " AND pv.id_empresa = '4' ";
                    
                    /*Busca a Último Preço negociado em Pedido do Cliente específico p/ o PA atual que está no Loop e baseado 
                    de Tipo de Nota que foi selecionado pelo usuário ...*/
                    $sql = "SELECT c.`id_uf`, ovi.`id_orcamento_venda_item`, 
                            ovi.`id_orcamento_venda`, pv.`id_pedido_venda`, 
                            CASE pv.id_empresa 
                                WHEN '1' THEN 'NF' 
                                WHEN '2' THEN 'NF' 
                            ELSE 
                                'SGD'
                            END AS nota_sgd, 
                            DATE_FORMAT(pv.`data_emissao`, '%d/%m/%Y') AS data_emissao, pv.`vencimento1`, 
                            pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, 
                            ovi.`queima_estoque`, pvi.`comissao_new`, pvi.`comissao_extra`, pvi.`preco_liq_final` 
                            FROM `pedidos_vendas_itens` pvi 
                            INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
                            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
                            INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND pv.`id_cliente` = '$id_cliente' $condicao_empresa_pedido 
                            WHERE pvi.`id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' ORDER BY id_pedido_venda_item DESC LIMIT 1 ";
                    $campos_dados_ultimo_pedido     = bancos::sql($sql);
                    if(count($campos_dados_ultimo_pedido) == 1) {
                        $ultimo_preco_unitario_vendido  = $campos_dados_ultimo_pedido[0]['preco_liq_final'];

                        if($campos_dados_ultimo_pedido[0]['vencimento4'] > 0) {
                            $prazo_faturamento = $campos_dados_ultimo_pedido[0]['vencimento1'].'/'.$campos_dados_ultimo_pedido[0]['vencimento2'].'/'.$campos_dados_ultimo_pedido[0]['vencimento3'].'/'.$campos_dados_ultimo_pedido[0]['vencimento4'];
                        }else if($campos_dados_ultimo_pedido[0]['vencimento3'] > 0) {
                            $prazo_faturamento = $campos_dados_ultimo_pedido[0]['vencimento1'].'/'.$campos_dados_ultimo_pedido[0]['vencimento2'].'/'.$campos_dados_ultimo_pedido[0]['vencimento3'];
                        }else if($campos[$i]['vencimento2'] > 0) {
                            $prazo_faturamento = $campos_dados_ultimo_pedido[0]['vencimento1'].'/'.$campos_dados_ultimo_pedido[0]['vencimento2'];
                        }else {
                            if($campos_dados_ultimo_pedido[0]['vencimento1'] == 0) {
                                $prazo_faturamento = 'À vista';
                            }else {
                                $prazo_faturamento = $campos_dados_ultimo_pedido[0]['vencimento1'];
                            }
                        }
                        echo $campos_dados_ultimo_pedido[0]['id_pedido_venda'].' '.$campos_dados_ultimo_pedido[0]['data_emissao'];
                    }else {
                        echo '<font color="red"><b>S/ Último Pedido nessa Condição => '.$_POST['opt_tipo_nota'].'</b></font>';
                    }
                ?>
                </td>
		<td bgcolor='#CECECE' align="right">
                <?
                    echo number_format($ultimo_preco_unitario_vendido, 2, ',', '.');
                    if($campos_dados_ultimo_pedido[0]['queima_estoque'] == 'S') {
                ?>
                    <img src='../../../../imagem/queima_estoque.png' title='Excesso de Estoque' alt='Excesso de Estoque' style='cursor:help' border="0">
                <?
                    }
                ?>
                &nbsp;
		</td>
                <td bgcolor='#CECECE'>
                    <?=$prazo_faturamento.' - '.$campos_dados_ultimo_pedido[0]['nota_sgd'];?>
                </td>
                <td bgcolor='#CECECE' align="right">
                <?
                    if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                        if(count($campos_dados_ultimo_pedido) == 1) {//Se existir uma Compra no ano Anterior ...
                            $tx_financeira  = custos::calculo_taxa_financeira($campos_dados_ultimo_pedido[0]['id_orcamento_venda']);
                            $margem         = custos::margem_lucro($campos_dados_ultimo_pedido[0]['id_orcamento_venda_item'], $tx_financeira, $campos_dados_ultimo_pedido[0]['id_uf'], $ultimo_preco_unitario_vendido);
                            
                            if(str_replace(' %', '', $margem[1]) < 0) {//Se for Negativo, vem na cor em Vermelho ...
                                echo '<font color="red">'.str_replace(' %', '', $margem[1]).'</font>';
                            }else {//Se positivo ...
                                echo str_replace(' %', '', $margem[1]);
                            }
                        }else {
                            echo '&nbsp;';
                        }
                    }else {
                        echo '&nbsp;';
                    }
                ?>
		</td>
                <td>
                    <font color='brown' title='Comiss&atilde;o Extra => <?=number_format($campos_dados_ultimo_pedido[0]['comissao_extra'], '2', ',', '.');?>' style='cursor:help'>
                        <?=number_format($campos_dados_ultimo_pedido[0]['comissao_new'] + $campos_dados_ultimo_pedido[0]['comissao_extra'], 2, ',', '.');?>
                    </font>
                </td>
                <td>
                <?
                    if($opt_tipo_opc == 1) {//Produtos Adquiridos ...
                        $id_uf = $campos[$i]['id_uf'];//Aki eu tenho a UF por causa dos Join´s das NF´s ...
                    }else {//Produtos Top(s) não Adquiridos (Curva ABC) ...
                        $id_uf = $campos_uf[0]['id_uf'];//Nesse caso sou obrigado a buscar a UF a parte ...
                    }
                    if($id_pais == 31) {//Se for do Brasil ...
                        $desconto_cliente       = 20;
                        $preco_lista_liquido    = $campos[$i]['preco_unitario'] * (1 - $campos[$i]['desc_base_a_nac'] / 100) * (1 - $campos[$i]['desc_base_b_nac'] / 100) * (1 + $campos[$i]['acrescimo_base_nac'] / 100) * (1 - $desconto_cliente / 100);
                        $dados_impostos_pa_sp   = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], 1);
                        $icms_reducao_sp        = round($dados_impostos_pa_sp['icms'] * (1 - $dados_impostos_pa_sp['reducao'] / 100), 2);
                        
                        $dados_impostos_pa_cliente  = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], $id_uf);
                        $icms_reducao_cliente       = round($dados_impostos_pa_cliente['icms'] * (1 - $dados_impostos_pa_cliente['reducao'] / 100), 2);
                        if($_POST['opt_tipo_nota'] == 'SGD') {//Se for SGD, sempre abate o ICMS de SP ...
                            $preco_lista_liquido*= (1 - $icms_reducao_sp / 100);
                        }else {//Se for NF ...
                            $diferenca_icms     = $icms_reducao_sp - $icms_reducao_cliente;
                            $preco_lista_liquido*= (1 - $diferenca_icms / 100);
                        }
                        $preco_lista_liquido = round($preco_lista_liquido, 2);//Arredondo esse valor para evitar futuros erros ...
                    }else {//Se for Internacional ...
                        $preco_lista_liquido = $campos[$i]['preco_export'];
                    }
                    echo number_format($preco_lista_liquido, 2, ',', '.');
                ?>
                </td>
                <td align="right">
                    <a href="javascript:nova_janela('../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Estoque" class="link">
                        <?
                            $qtde_estoque   = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado']);
                            $color          = ($qtde_estoque[8] < 0) ? 'red' : '';
                        ?>
                        <font color='<?=$color;?>'><?=number_format($qtde_estoque[8], 2, ',', '.');?></font>
                    </a>
                    <?
                        $retorno_pas_atrelados  = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos[$i]['id_produto_acabado']);
                        $font_ec_atrelado       = ($retorno_pas_atrelados['total_ec_pas_atrelados'] < 0) ? 'red' : 'black';
                        echo '<br><font color="'.$font_ec_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help">Atrel = '.number_format($retorno_pas_atrelados['total_ec_pas_atrelados'], 0, '', '.').'</font>';
                        echo '<br><font color="'.$font_ec_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help">Est p/ '.number_format($retorno_pas_atrelados['estoque_para_x_meses_pas_atrelados'], 0, '', '.').' Meses</font>';
                    ?>
		</td>
                <td>
                <?
                    //Traz a quantidade de peças por embalagem da embalagem principal daquele produto ...
                    $sql = "SELECT pecas_por_emb 
                            FROM `pas_vs_pis_embs` 
                            WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                            AND `embalagem_default` = '1' LIMIT 1 ";
                    $campos_pecas_emb   = bancos::sql($sql);
                    $pecas_por_emb      = (count($campos_pecas_emb) == 1) ? number_format($campos_pecas_emb[0]['pecas_por_emb'], 0, ',', '.') : 0;
                    
                    //Esse controle de oferecer qtde semestral, só será feito até o dia 30/06 de cada ano ...
                    if(intval(date('m')) <= 6) $media_qtde_pa/= 2;
                    
                    if($opt_tipo_opc == 1) {//Produtos Adquiridos ...
                        if($pecas_por_emb > 0) {
                            //Aqui eu sugiro uma Qtde Proposta como sendo Embalagem Fechada do PA ...
                            $qtde_proposta  = intval($media_qtde_pa) - intval($qtde_do_ultimo_ano_pa);
                            if($qtde_proposta > 0) {
                                $sugestao       = (intval($qtde_proposta / $pecas_por_emb) + 1) * $pecas_por_emb;
                                $qtde_proposta  = $sugestao;//A qtde proposta passa a ser a sugestão das Embalagens Fechadas ...
                            }else {
                                $qtde_proposta  = 0;//Ou seja o cliente já comprou o que era necessário ...
                            }
                        }else {
                            //Aqui eu mantenho a Qtde Proposta baseado na Média anual ...
                            $qtde_proposta = intval($media_qtde_pa) - intval($qtde_do_ultimo_ano_pa);
                        }
                        if($media_qtde_pa == '') $qtde_proposta = 0; // Se ele nao comprou nos ultimos 3 anos entao qtde proposta é 0. 
                        if($qtde_proposta < 0) $qtde_proposta = 0;//Não existe qtde negativa ...
                    }else {//Produtos Top(s) não Adquiridos (Curva ABC) ...
                        $qtde_prop_vlr_prop_ped = $perc_pedido_vs_mmv * $campos[$i]['mmv'];
                        if($pecas_por_emb == 0) $pecas_por_emb  = 1;
                        //Se a Qtde Proposta for menor do que 50% do Valor da Embalagem fechada, então já zero esse valor p/ nem oferecer esse PA ...
                        if($qtde_prop_vlr_prop_ped <= 0.5 * $pecas_por_emb) {
                            $qtde_proposta  = 0;
                        }else {
                            $sugestao       = (intval($qtde_prop_vlr_prop_ped / $pecas_por_emb) + 1) * $pecas_por_emb;
                            $qtde_proposta  = $sugestao;//A qtde proposta passa a ser a sugestão das Embalagens Fechadas ...
                        }
                    }
                    $tabIndex+=1;
                ?>
                    <input type="text" name="txt_qtde_proposta[]" id="txt_qtde_proposta<?=$indice;?>" value="<?=$qtde_proposta;?>" title="Digite a Quantidade Proposta" onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular_margem_lucro('<?=$indice;?>');calcular_total_proposto('<?=$indice;?>')" size="7" maxlength="6" tabIndex="<?=$tabIndex;?>" class="caixadetexto">
                    <!--Esses hiddens serão utilizados nos cálculos e controles de JavaScript-->
                    <input type="hidden" name="hdd_pecas_por_emb[]" id="hdd_pecas_por_emb<?=$indice;?>" value="<?=$pecas_por_emb;?>">
                    <input type="hidden" name="hdd_familia[]" id="hdd_familia<?=$indice;?>" value="<?=$campos[$i]['id_familia'];?>">
                    <input type="hidden" name="hdd_mmv[]" id="hdd_mmv<?=$indice;?>" value="<?=$campos[$i]['mmv'];?>">
		</td>
                <td>
                   <input type="text" name="txt_desconto_extra[]" id="txt_desconto_extra<?=$indice;?>" title="Digite o Desconto Extra" onKeyUp="verifica(this, 'moeda_especial', '2', '', event);calcular_margem_lucro('<?=$indice;?>');calcular_total_proposto('<?=$indice;?>')" size="7" maxlength="6" class="caixadetexto">
		</td>
                <td>
                    <?
                        //Se a Última Venda foi feita por Queima, então sugiro o Preço como sendo o Preço de Lista Líquido ...
                        if($campos_dados_ultimo_pedido[0]['queima_estoque'] == 'S') $ultimo_preco_unitario_vendido = $preco_lista_liquido;
                        //Aqui eu trago o Preço de acordo com a Opção selecionada na Combo Tipo de Preço ...
                        if($_POST['cmb_tipo_preco'] == 1) {//Preço c/ 20% ...
                            $preco_escolhido_para_usar  = $preco_lista_liquido;
                        }else if($_POST['cmb_tipo_preco'] == 2) {//Preço do último Pedido ...
                            $preco_escolhido_para_usar  = $ultimo_preco_unitario_vendido;
                        }else if($_POST['cmb_tipo_preco'] == 3) {
                            $preco_escolhido_para_usar  = $campos[$i]['preco_promocional_b'];
                            if($_POST['opt_tipo_nota'] == 'SGD') {//Se for SGD, sempre abate o ICMS de SP ...
                                $preco_escolhido_para_usar*= (1 - $icms_reducao_sp / 100);
                            }else {//Se for NF eu dou a diferença de ICMS p/ a UF do Cliente ...
                                $preco_escolhido_para_usar*= (1 - $diferenca_icms / 100);
                            }
                        }
                        $valor_mmv_pa               = $campos[$i]['mmv'] * $preco_escolhido_para_usar;
                    ?>
                    <input type="text" name="txt_preco_unitario_proposto[]" id="txt_preco_unitario_proposto<?=$indice;?>" value="<?=number_format($preco_escolhido_para_usar, 2, ',', '.');?>" title="Digite o Preço Unitário Proposto" onKeyUp="verifica(this, 'moeda_especial', '2', '', event);calcular_margem_lucro('<?=$indice;?>');calcular_total_proposto('<?=$indice;?>', 'SIM')" size="10" maxlength="8" class="caixadetexto">
                    <!--Esses hiddens serão utilizados nos cálculos e controles de JavaScript-->
                    <input type="hidden" name="hdd_preco_unitario_proposto[]" id="hdd_preco_unitario_proposto<?=$indice;?>" value="<?=round($preco_escolhido_para_usar, 2);?>">
                    <input type="hidden" name="hdd_valor_mmv_pa[]" id="hdd_valor_mmv_pa<?=$indice;?>" value="<?=$valor_mmv_pa;?>">
		</td>
                <td>
                    <?
                        $custo_ml_zero_pz_medio_nf_sp  = custos::preco_custo_pa($campos[$i]['id_produto_acabado']) / $fator_margem_lucro * $fator_taxa_financeira_opc;
                        $custo_ml_zero_pz_medio_sgd    = $custo_ml_zero_pz_medio_nf_sp * (1 - $icms_reducao_sp / 100) * (1 - $impostos_federais / 100);

                        if($_POST['opt_tipo_nota'] == 'NF') {
                            $custo_ml_zero_proposta_pz_medio = $custo_ml_zero_pz_medio_nf_sp * (1 - $diferenca_icms / 100);
                        }else {
                            $custo_ml_zero_proposta_pz_medio = $custo_ml_zero_pz_medio_sgd;
                        }
                        $custo_ml_zero_proposta_pz_medio    = round($custo_ml_zero_proposta_pz_medio, 2);
                        $margem_lucro_proposta              = ($preco_escolhido_para_usar / $custo_ml_zero_proposta_pz_medio - 1) * 100;
                        
                        if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                            $type = 'text';
                        }else {//P/ outros usuários não posso exibir a Margem de Lucro ...
                            $type = 'hidden';
                            echo '-';
                        }
                    ?>
                    <input type='<?=$type;?>' name="txt_margem_lucro_proposta[]" value="<?=number_format($margem_lucro_proposta, 1, ',', '.');?>" id="txt_margem_lucro_proposta<?=$indice;?>" title="Margem de Lucro Proposta" size="10" maxlength="8" onfocus="document.getElementById('txt_qtde_proposta<?=$indice;?>').focus()" class="textdisabled">
                    <!--Aqui eu guardo esse valor porque será utilizado na função de Margem de Lucro em JavaScript ...-->
                    <input type='hidden' name='hdd_custo_ml_zero_proposta_pz_medio[]' value="<?=number_format($custo_ml_zero_proposta_pz_medio, 2, ',', '.');?>" id="hdd_custo_ml_zero_proposta_pz_medio<?=$indice;?>">
                    &nbsp;
		</td>
                <td>
                    <input type="text" name="txt_comissao_estimada[]" id="txt_comissao_estimada<?=$indice;?>" title="Comissão % Estimada" size="10" maxlength="8" class="textdisabled" disabled>
		</td>
                <td>
                    <input type="text" name="txt_total_proposto[]" id="txt_total_proposto<?=$indice;?>" value="<?=number_format($qtde_proposta * $preco_escolhido_para_usar, 2, ',', '.');?>" title="Total Proposto" size="10" maxlength="8" class="textdisabled" disabled>
		</td>
	</tr>
<?
                    $total_geral_proposto+=                 $qtde_proposta * $preco_lista_liquido;
                    $total_custo_ml_zero_proposta_60ddl+=   $qtde_proposta * $custo_ml_zero_proposta_pz_medio;
                    $indice++;
		}
                $id_pa_antigo = $campos[$i]['id_produto_acabado'];
            }
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='26' align='right'>
            <font size='2' color='black'>
                <b>TOTAL GERAL DA PROPOSTA => </b>
            </font>
        </td>
        <td>
            <!--***********************Controles de Tela***********************-->
            <input type="hidden" name="hdd_total_valor_mmv_pa" id="hdd_total_valor_mmv_pa" value="<?=$total_valor_mmv_pa;?>">
            <input type="hidden" name="hdd_total_custo_ml_zero_proposta_60ddl" id="hdd_total_custo_ml_zero_proposta_60ddl" value="<?=number_format($total_custo_ml_zero_proposta_60ddl, 1, ',', '.');?>">
            <!--***************************************************************-->
            <input type="text" name="txt_total_geral_proposto" id="txt_total_geral_proposto" value="<?=number_format($total_geral_proposto, 2, ',', '.');?>" title="Total Geral Proposto" size="10" maxlength="8" class="textdisabled" disabled>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='25' align='right'>
            <font size='2' color='black'>
                <b>MLM % DA PROPOSTA => </b>
            </font>
        </td>
        <td>
            <?
                if(in_array($_SESSION['id_login'], $vetor_logins_com_acesso_margens_lucro)) {
                    if($total_custo_ml_zero_proposta_60ddl == 0) {
                        $mlm_proposta = 0;
                    }else {
                        $mlm_proposta = ($total_geral_proposto / $total_custo_ml_zero_proposta_60ddl - 1) * 100;
                    }
                }else {
                    $mlm_proposta = 0;
                }
            ?>
            <input type="text" name="txt_mlm_proposta" id="txt_mlm_proposta" value="<?=number_format($mlm_proposta, 1, ',', '.');?>" title="Total Geral Proposto" size="10" maxlength="8" class="textdisabled" disabled>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='27'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '<?=$PHP_SELF.$parametro;?>'" class='botao'>
            <input type='button' name='cmd_imprimir_html' value='Imprimir HTML' title='Imprimir HTML' onclick="imprimir_html()" style="color:blue" class='botao'>
            <input type='button' name='cmd_gravar_opc' value='Imprimir / Gravar OPC' title='Imprimir / Gravar OPC' onclick="gravar_opc()" style="color:red" class='botao'>
        </td>
    </tr>  
<?
        if($opt_tipo_opc == 1) {//Produtos Adquiridos ...
?>
    <tr class="atencao">
        <td colspan='27'>
            <font color='black'>
                * Qtde Proposta = Média Anual dos últimos <?=$_POST['cmb_qtde_anos'];?> anos - Qtde Comprada do Ano Vigente (Até 30/06 usamos o MAV / 2 p/ acharmos a média Semestral)
            </font>
        </td>
    </tr>
<?
        }
?>
</table>
<center>
    <font color='darkgreen' face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='4'><b>Total de Registro(s): <?=$indice;?></b></font><p>
</center>
<?
    }
?>
</form>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
* Quando marcado <b>"Usar Preço do Último Pedido"</b>, caso o item esteja <b>"EM EXCESSO DE ESTOQUE"</b>, propomos o preço com Desconto Extra 20% + Dif. ICMS.
</pre>
<?
}else {
?>
<html>
<head>
<title>.:: Projetar OPC - Consultar Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
</head>
<body onload='document.form.txt_razao_social.focus()'>
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table border='0' width='60%' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Projetar OPC - Consultar Cliente(s)
        </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Razão Social
            </td>
            <td>
                    <input type="text" name="txt_razao_social" title="Digite a Razão Social" maxlength='50' size='60' class="caixadetexto">
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Nome Fantasia
            </td>
            <td>
                    <input type="text" name="txt_nome_fantasia" title="Digite a Nome Fantasia" maxlength='40' size='50' class="caixadetexto">
            </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type="radio" name="opt_tipo_opc" value="1" title="Produtos Adquiridos" id='opt_tipo_opc1' checked>
            <label for='opt_tipo_opc1'>Produtos Adquiridos</label>
            &nbsp;
            <input type="radio" name="opt_tipo_opc" value="2" title="Produtos Top(s) não Adquiridos (Curva ABC)" id='opt_tipo_opc2'>
            <label for='opt_tipo_opc2'>Produtos Top(s) não Adquiridos (Curva ABC)</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.reset()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>