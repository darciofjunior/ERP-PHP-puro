<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca � chamada aqui porque a mesma � utilizada dentro do Custos ...
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>N�O H� ITEM(NS) H� SER(EM) TRANSPORTADO(S).</font>";
$mensagem[2] = "<font class='atencao'>N�O H� OR�AMENTO(S) DO MESMO TIPO DE NOTA QUE N�O ESTEJAM CONGELADO(S) PARA SER(EM) TRANSPORTADO(S).</font>";

if($passo == 1) {
//Aqui � controle para o q vai aparecer na mensagem ...
//Quando o par�metro acao = 0, significa q deseja transportar os itens para o mesmo cliente; ou
//Quando o par�metro acao = 1, significa q deseja clonar os itens para o mesmo cliente; 
    $escrever = ($acao == 0) ? 'Transportando' : 'Clonando';
//Aqui disparo o loop de Itens de Or�amento p/ poder acumular no vetor de Itens de Or�amento
    foreach($_POST['chkt_orcamento_venda_item'] as $id_orcamento_venda_item) $vetor_orcamento_venda_item = $vetor_orcamento_venda_item.$id_orcamento_venda_item.', ';
    $vetor_orcamento_venda_item = substr($vetor_orcamento_venda_item, 0, strlen($vetor_orcamento_venda_item) - 2);

//Aqui busca a raz�o social e o cliente do or�amento corrente
    $sql = "SELECT ov.nota_sgd, c.id_cliente, c.id_pais, c.razaosocial 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
            WHERE ov.id_orcamento_venda = '$id_orcamento_venda' LIMIT 1 ";
    $campos 	= bancos::sql($sql);
    $id_cliente     = $campos[0]['id_cliente'];
    $id_pais 	= $campos[0]['id_pais'];
    $razaosocial    = $campos[0]['razaosocial'];
    $tipo_nota 	= $campos[0]['nota_sgd'];
	
//Verifica se o Cliente � do Tipo Internacional ou Nacional ...
    $tipo_moeda = ($id_pais != 31) ? 'U$' : 'R$';

/*Aqui eu trago todos os or�amentos q est�o em aberto desse Cliente e do mesmo Tipo de Nota e q n�o estejam
congelados, com exce��o do orc. corrente*/
    $sql = "SELECT DISTINCT(ov.`id_orcamento_venda`), ov.`nota_sgd`, DATE_FORMAT(ov.`data_emissao`, '%d/%m/%Y') AS data_emissao, 
            ov.`prazo_a`, ov.`prazo_b`, ov.`prazo_c`, ov.`prazo_d`, c.`credito`, cc.`nome` 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = ov.`id_cliente_contato` 
            INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` AND c.`id_cliente` = '$id_cliente' 
            WHERE ov.`nota_sgd` = '$tipo_nota' 
            AND ov.`id_orcamento_venda` <> '$id_orcamento_venda' 
            AND ov.`status` < '2' 
            AND ov.`congelar` = 'N' ORDER BY ov.`id_orcamento_venda` DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
<html>
<head>
<title>.:: <?=$escrever;?> Itens do Or�amento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar_novo_orcamento() {
    var pergunta = confirm('VOC� TEM CERTEZA DE QUE DESEJA TRANSPORTAR ESSE(S) ITEM(NS) PARA UM NOVO OR�AMENTO ? \n(OBSERVA��O: O CABE�ALHO DO NOVO OR�AMENTO HERDAR� AS MESMAS CARACTER�STICAS DE CABE�ALHO DO OR�AMENTO ATUAL !)')
    if(pergunta == false) {
        return false
    }else {
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[2];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'transportar_outro_orcamento.php?id_orcamento_venda=<?=$id_orcamento_venda;?>&acao=<?=$acao;?>'" class='botao'>
            &nbsp;
            <input type='submit' name='cmd_novo_orcamento' value='Novo Or�amento' title='Novo Or�amento' onclick="validar_novo_orcamento()" class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_orcamento_venda' value="<?=$id_orcamento_venda;?>">
<input type='hidden' name='chkt_orcamento_venda_item' value="<?=$vetor_orcamento_venda_item;?>">
</form>
</body>
</html>
<?
        exit;
    }
?>
<html>
<head>
<title>.:: <?=$escrever;?> Itens do Or�amento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(acao, id_orcamento_venda_transportar) {
//Pode prosseguir
    if(acao == 1) {
        var pergunta = confirm('VOC� TEM CERTEZA DE QUE DESEJA TRANSPORTAR ESSE(S) ITEM(NS) PARA O OR�AMENTO N.� '+id_orcamento_venda_transportar+' ?')
        if(pergunta == false) {
            return false
        }else {
            document.form.id_orcamento_venda_transportar.value = id_orcamento_venda_transportar
            document.form.submit()
        }
    }else {
        alert('EXCEDIDO A QUANTIDADE DE ITEM(NS) PARA ESSE OR�AMENTO N.� '+id_orcamento_venda_transportar+'!\n\nOBS: A QTDE M�XIMA PERMITIDA POR OR�AMENTO � DE NO M�XIMO 75 ITEM(NS) !')
    }
}

function validar_novo_orcamento() {
    var pergunta = confirm('VOC� TEM CERTEZA DE QUE DESEJA TRANSPORTAR ESSE(S) ITEM(NS) PARA UM NOVO OR�AMENTO ? \n(OBSERVA��O: O CABE�ALHO DO NOVO OR�AMENTO HERDAR� AS MESMAS CARACTER�STICAS DE CABE�ALHO DO OR�AMENTO ATUAL !)')
    if(pergunta == false) {
        return false
    }else {
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr>
        <td></td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <?=$escrever;?> Itens do Or�amento N.�&nbsp;
            <font color='yellow'>
                <?=$id_orcamento_venda;?>
            </font>
        </td>
    </tr>
    <?
        $printar_nota = ($tipo_nota == 'N') ? 'NF' : 'SGD';
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='10'>
            Or�amento(s) em Aberto do Cliente: 
            <font color='yellow'>
                <?=$razaosocial;?> 
            </font>
            - Tipo: 
            <font color='yellow'>
                <?=$printar_nota;?> 
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            N.&ordm; Orc
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Contato
        </td>
        <td>
            Prazo A
        </td>
        <td>
            Prazo B
        </td>
        <td>
            Prazo C
        </td>
        <td>
            Prazo D
        </td>
        <td>
            Prazo M�dio
        </td>
        <td>
            Data de Validade
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
//Aqui verifica a qtde de itens existentes para o Or�amento do Loop
        $sql = "SELECT COUNT(`id_orcamento_venda_item`) AS total_itens_orcamentos 
                FROM `orcamentos_vendas_itens` 
                WHERE `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' ";
        $campos_total_itens     = bancos::sql($sql);
        $total_itens_orcamentos = $campos_total_itens[0]['total_itens_orcamentos'];

        if($total_itens_orcamentos <= 100) {//Pode prosseguir
            $executar = 'javascript:prosseguir(1, '.$campos[$i]['id_orcamento_venda'].')';
        }else {//N�o pode prosseguir, excedeu do limite de registros
            $executar = 'javascript:prosseguir(2, '.$campos[$i]['id_orcamento_venda'].')';
        }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$executar;?>" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$executar;?>">
            <a href="#" class='link'>
                <?=$campos[$i]['id_orcamento_venda'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['prazo_a'];?>
        </td>
        <td>
            <?=$campos[$i]['prazo_b'];?>
        </td>
        <td>
            <?=$campos[$i]['prazo_c'];?>
        </td>
        <td>
            <?=$campos[$i]['prazo_d'];?>
        </td>
        <td>
            <?=$campos[$i]['prazo_medio'];?>
        </td>
        <td>
        <?
            $vetor_dados_gerais     = vendas::dados_gerais_orcamento($campos[$i]['id_orcamento_venda']);
            echo data::datetodata($vetor_dados_gerais['data_validade_orc'], '/');
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'transportar_outro_orcamento.php?id_orcamento_venda=<?=$id_orcamento_venda;?>&acao=<?=$acao;?>'" class='botao'>
            <input type='submit' name='cmd_novo_orcamento' value='Novo Or�amento' title='Novo Or�amento' onclick='validar_novo_orcamento()' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_orcamento_venda' value="<?=$id_orcamento_venda;?>">
<input type='hidden' name='chkt_orcamento_venda_item' value="<?=$vetor_orcamento_venda_item;?>">
<!--Essa vari�vel aponta para qual o or�amento que eu desejo transportar os dados-->
<input type='hidden' name='id_orcamento_venda_transportar'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
}else if($passo == 2) {
//Aqui transforma em um vetor para poder disparar o loop com os itens
    $vetor_orcamento_venda_item = explode(',', $_POST['chkt_orcamento_venda_item']);
//Significa que s� estou transportando os itens do or�amento antigo para o or�amento selecionado atrav�s do link ...
    if(!empty($id_orcamento_venda_transportar)) {
        for($i = 0; $i < count($vetor_orcamento_venda_item); $i++) {
            $sql = "UPDATE `orcamentos_vendas_itens` SET `id_orcamento_venda` = '$id_orcamento_venda_transportar' WHERE `id_orcamento_venda_item` = '$vetor_orcamento_venda_item[$i]' LIMIT 1 ";
            bancos::sql($sql);
/*******************************************************************************************************/
            //Aqui eu atualizo a ML Est do Iem do Or�amento ...
            custos::margem_lucro_estimada($vetor_orcamento_venda_item[$i]);
/*************Rodo a fun��o de Comiss�o depois de ter gravado a ML Estimada*************/
            vendas::calculo_ml_comissao_item_orc($id_orcamento_venda_transportar, $vetor_orcamento_venda_item[$i]);
        }
?>
    <Script Language = 'JavaScript'>
        alert('ITEM(NS) TRANSPORTADO(S) COM SUCESSO !')
        window.parent.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'
    </Script>
<?
    }else {//Aqui significa que o usu�rio preferiu criar um or�amento novo
//Aqui busca os dados do or�amento atual ...
        $sql = "SELECT * 
                FROM `orcamentos_vendas` 
                WHERE `id_orcamento_venda` = '$id_orcamento_venda' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $id_cliente 		= $campos[0]['id_cliente'];
        $id_cliente_contato     = $campos[0]['id_cliente_contato'];
        $finalidade             = $campos[0]['finalidade'];
        $nota_sgd               = $campos[0]['nota_sgd'];
        $conceder_pis_cofins    = $campos[0]['conceder_pis_cofins'];
        $prazo_a 		= $campos[0]['prazo_a'];
        $prazo_b 		= $campos[0]['prazo_b'];
        $prazo_c 		= $campos[0]['prazo_c'];
        $prazo_d 		= $campos[0]['prazo_d'];
        $prazo_medio            = $campos[0]['prazo_medio'];
        $data_sys 		= $campos[0]['data_sys'];
//Aqui � a inser��o dos dados de cabe�alho no novo or�amento
        $sql = "INSERT INTO `orcamentos_vendas` (`id_orcamento_venda`, `id_cliente_contato`, `id_cliente`, `id_funcionario`, `finalidade`, `nota_sgd`, `conceder_pis_cofins`, `data_emissao`, `prazo_a`, `prazo_b`, `prazo_c`, `prazo_d`, `prazo_medio`, `data_sys`) VALUES (NULL, '$id_cliente_contato', '$id_cliente', '$_SESSION[id_funcionario]', '$finalidade', '$nota_sgd', '$conceder_pis_cofins', '".date('Y-m-d')."', '$prazo_a', '$prazo_b', '$prazo_c', '$prazo_d', '$prazo_medio', '$data_sys') ";
        bancos::sql($sql);
        $id_orcamento_venda_novo = bancos::id_registro();

//Aqui � a parte da inser��o dos itens no novo Or�amento
        foreach($vetor_orcamento_venda_item as $id_orcamento_venda_item) {
            $sql = "UPDATE `orcamentos_vendas_itens` SET `id_orcamento_venda` = '$id_orcamento_venda_novo' WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
            bancos::sql($sql);
/*******************************************************************************************************/
            vendas::calculo_preco_liq_final_item_orc($id_orcamento_venda_item);
//Aqui eu atualizo a ML Est do Iem do Or�amento ...
            custos::margem_lucro_estimada($id_orcamento_venda_item);
/*************Rodo a fun��o de Comiss�o depois de ter gravado a ML Estimada*************/
            vendas::calculo_ml_comissao_item_orc($id_orcamento_venda_novo, $id_orcamento_venda_item);
        }
?>
    <Script Language = 'JavaScript'>
        alert('NOVO OR�AMENTO N.� '+<?=$id_orcamento_venda_novo;?>+' GERADO COM SUCESSO !')
        window.parent.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$id_orcamento_venda_novo;?>'
    </Script>
<?
    }
}else {
//Somente todos os itens em aberto com o id_orcamento q foi passado
//Quando o par�metro acao = 0, significa q deseja transportar os itens para o mesmo cliente; ou
    if($acao == 0) {
        $escrever = 'Transportando';//Aqui � controle para o q vai aparecer na mensagem
        $condicao = " AND ovi.status = '0' ";
//Quando o par�metro acao = 1, significa q deseja clonar os itens para o mesmo cliente; ou
//Todos os itens normal do id_orcamento
    }else {
        $escrever = 'Clonando';//Aqui � controle para o q vai aparecer na mensagem
    }

    $sql = "SELECT c.id_pais, ged.id_empresa_divisao, ov.id_cliente, ov.congelar, ov.nota_sgd, ov.prazo_a, 
            ov.prazo_b, ov.prazo_c, ov.prazo_d, ov.data_sys, ovi.id_orcamento_venda_item, ovi.id_produto_acabado, 
            ovi.qtde, ovi.preco_liq_fat, ovi.desc_extra, ovi.acrescimo_extra, 
            ovi.preco_liq_final, pa.referencia 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ovi.id_produto_acabado 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `orcamentos_vendas` ov ON ov.id_orcamento_venda = ovi.id_orcamento_venda 
            INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
            WHERE ovi.id_orcamento_venda = '$id_orcamento_venda' 
            $condicao ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
<html>
<head>
<title>.:: <?=$escrever;?> Itens do Or�amento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<form name='form'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
        exit;
    }
//Verifica se o Cliente � do Tipo Internacional ou Nacional ...
    $tipo_moeda = ($campos[0]['id_pais'] != 31) ? 'U$' : 'R$';
?>
<html>
<head>
<title>.:: <?=$escrever;?> Itens do Or�amento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++)   {
        if(elementos[i].type == 'checkbox')  {
            if (elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OP��O !')
        return false
    }else {
        var acao = eval('<?=$acao;?>')
        //S� quando o Usu�rio estiver Transportando itens de Or�amento que atualizo o hidden abaixo ...
        if(acao == 0) document.form.passo.value = 1
        return true
    }
}
</Script>
</head>
<body>
<?
/*Aqui verifica se continua no mesmo arquivo caso for transporte ou se vai para a consulta de clientes
caso seje um clone de itens de orcamento*/
    if($acao == 1) {//Significa q se deseja realizar um clone deste or�amento para outro cliente
        $destino = 'consultar_cliente.php';
    }else {//Continua no mesmo arquivo, porque s� est� sendo feito um transporte
        $destino = $PHP_SELF.'?passo=1';
    }
?>
<form name='form' method='post' action='<?=$destino;?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='10'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10"'>
            <?=$escrever;?> Itens do Or�amento N.�&nbsp;
            <font color='yellow'>
                <?=$id_orcamento_venda;?>
            </font>
        </td>
    </tr>
	<tr class='linhadestaque' align='center'>
		<td>
			<input type='checkbox' name='chkt' onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox">
		</td>
		<td>
			<font title="Quantidade" style="cursor:help">
				Qtde
			</font>
		</td>
		<td>Produto</td>
		<td>
			<font title="Pre�o Liquido Farurado <?=$tipo_moeda;?>/ Pe�a" style="cursor:help">
				Pre�o<br>L.F. <?=$tipo_moeda;?>/P�
			</font>
		</td>
		<td>
			<font title="Desconto Extra" style="cursor:help">
				Desc. <br>Extra
			</font>
		</td>
		<td>
			<font title="Acrescimo Extra" style="cursor:help">
				Acr�sc. <br>Extra %
			</font>
		</td>
		<td>
			<font title="Representante" style="cursor:help">
				Repres.
			</font>
		</td>
		<td>
			<font title="Pre�o L. Final <?=$tipo_moeda;?>" style="cursor:help">
				Pre�o L. <br>Final <?=$tipo_moeda;?>
			</font>
		</td>
		<td>IPI %</td>
		<td>
			<font title="Total <?=$tipo_moeda;?> Lote" style="cursor:help">
				Total <?=$tipo_moeda;?><br>Lote
			</font>
		</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class='linhanormal' onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8', '<?=$campos[$i]['referencia'];?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
		<td>
			<input type='checkbox' name='chkt_orcamento_venda_item[]' value="<?=$campos[$i]['id_orcamento_venda_item'];?>" onclick="checkbox('form', 'chkt','<?=$i;?>', '#E8E8E8', '<?=$campos[$i]['referencia'];?>')" class='checkbox'>
		</td>
		<td>
			<?=number_format($campos[$i]['qtde'], 0, ',', '.');?>
		</td>
		<td align='left'>
		<?
			if($campos[$i]['referencia'] != 'ESP') {
				echo $campos[$i]['referencia'].' * '.intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'],0);
			}else {
		?>
				<?=$campos[$i]['referencia'].' * '.intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'],0);?>
		<?
			}
		?>
		</td>
		<td align='right'>
			<?=number_format($campos[$i]['preco_liq_fat'], 2, ',', '.');?>
		</td>
		<td align='right'>
			<?=number_format($campos[$i]['desc_extra'], 2, ',', '.');?>
		</td>
		<td align='right'>
			<?=number_format($campos[$i]['acrescimo_extra'], 2, ',', '.');?>
		</td>
		<td>
		<?
			$sql = "SELECT id_representante 
                                FROM `clientes_vs_representantes` 
                                WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' 
                                AND `id_empresa_divisao` = ".$campos[$i]['id_empresa_divisao']." LIMIT 1 ";
			$campos_representante = bancos::sql($sql);
			if(count($campos_representante) > 0) {
                            $sql = "SELECT nome_fantasia 
                                    FROM `representantes` 
                                    WHERE `id_representante` = '".$campos_representante[0]['id_representante']."' LIMIT 1 ";
                            $campos_rep = bancos::sql($sql);
                            echo $campos_rep[0]['nome_fantasia'];
			}
		?>
		</td>
		<td align='right'>
			<?=number_format($campos[$i]['preco_liq_final'], 2, ',', '.');?>
		</td>
		<td align='right'>
		<?
//Quando o pa�s � do Tipo Internacional n�o existe IPI
			if($id_pais != 31) {
				echo 'S/IPI';
			}else {//Quando o pa�s � Nacional verifica se exite IPI
				$sql = "SELECT cf.ipi, cf.id_classific_fiscal 
                                        FROM `produtos_acabados` pa 
                                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                                        INNER JOIN `grupos_pas` gp ON gp.id_grupo_pa = ged.id_grupo_pa 
                                        INNER JOIN `familias` fm ON fm.id_familia = gp.id_familia 
                                        INNER JOIN `classific_fiscais` cf ON cf.id_classific_fiscal = fm.id_classific_fiscal 
                                        WHERE pa.`id_produto_acabado` =' ".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
				$campos_temp = bancos::sql($sql);
				if(count($campos_temp) > 0) {
					echo number_format($campos_temp[0]['ipi'], 2, ',', '.');
					$id_classific_fiscal = $campos_temp[0]['id_classific_fiscal'];
				}else {
					$id_classific_fiscal = '';
					echo '&nbsp;';
				}
			}
		?>
		</td>
		<td align='right'>
			<?=number_format($campos[$i]['preco_liq_final'] * $campos[$i]['qtde'], 2, ',', '.');?>
		</td>
	</tr>
<?
		}
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan='10'>
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'" class='botao'>
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class='botao'>
			<input type="submit" name="cmd_avancar" value="&gt;&gt; Avan�ar &gt;&gt;" title="Avan�ar" class='botao'>
		</td>
	</tr>
</table>
<input type='hidden' name='id_orcamento_venda' value='<?=$id_orcamento_venda;?>'>
<?//Quando o par�metro acao = 0, significa q deseja transportar os itens para o mesmo cliente; ou
//Quando o par�metro acao = 1, significa q deseja transportar os itens para outro cliente
?>
<input type='hidden' name='acao' value='<?=$acao;?>'>
<input type='hidden' name='passo'>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observa��o:</font></b>
<pre>
* S� exibir� Or�amento(s) que sejam do mesmo Tipo de Nota que n�o estejam congelados.
</pre>
<?}?>