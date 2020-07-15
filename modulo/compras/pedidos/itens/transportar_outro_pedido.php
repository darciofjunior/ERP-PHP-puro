<?
require('../../../../lib/segurancas.php');
require('../../../../lib/biblioteca.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/custos.php');
require('../../../../lib/vendas.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>NÃO HÁ ITEM(NS) HÁ SER(EM) TRANSPORTADO(S).</font>";
$mensagem[2] = "<font class='atencao'>NÃO HÁ PEDIDO(S) PARA SER(EM) TRANSPORTADO(S).</font>";
$mensagem[3] = "<font class='confirmacao'>ITEM(NS) TRANSPORTADO(S) COM SUCESSO.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $chkt_item_pedido 	= $_POST['chkt_item_pedido'];
            $txt_quantidade		= $_POST['txt_quantidade'];
    }else {
            $chkt_item_pedido 	= $_GET['chkt_item_pedido'];
            $txt_quantidade		= $_GET['txt_quantidade'];
    }
//Aqui disparo o loop de Itens de Pedido p/ poder acumular no vetor de Itens de Pedido
    foreach($chkt_item_pedido as $id_item_pedido) 	$vetor_item_pedido.= $id_item_pedido.', ';
    foreach($txt_quantidade as $quantidade) 		$vetor_quantidade.= $quantidade.', ';
    $vetor_item_pedido 	= substr($vetor_item_pedido, 0, strlen($vetor_item_pedido) - 2);
    $vetor_quantidade 	= substr($vetor_quantidade, 0, strlen($vetor_quantidade) - 2);
//Aqui busco alguns dados do Pedido corrente ...
    $sql = "SELECT f.id_fornecedor, f.razaosocial, p.id_empresa, p.tipo_nota, CONCAT(tm.simbolo, ' ') AS moeda 
                    FROM `pedidos` p 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
                    INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = p.id_tipo_moeda 
                    WHERE p.id_pedido = '$id_pedido' ";
    $campos 		= bancos::sql($sql);
    $id_fornecedor 	= $campos[0]['id_fornecedor'];
    $razao_social 	= $campos[0]['razaosocial'];
    $id_empresa_pedido = $campos[0]['id_empresa'];
    $tipo_nota 		= $campos[0]['tipo_nota'];
    $moeda 			= $campos[0]['moeda'];

/*Aqui eu trago todos os pedidos q estão em aberto desse Fornecedor com exceção do 
pedido corrente*/
    $sql = "SELECT DISTINCT(p.id_pedido), p.vendedor, p.prazo_pgto_a, p.prazo_pgto_b, p.prazo_pgto_c, p.desc_ddl, p.desconto_especial_porc, p.prazo_entrega, p.prazo_navio, p.tipo_export, p.id_funcionario_cotado, p.data_emissao, e.nomefantasia, f.id_pais 
            FROM `pedidos` p 
            INNER JOIN `empresas` e ON e.id_empresa = p.id_empresa 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
            WHERE p.`id_pedido` <> '$id_pedido' 
            AND p.`id_fornecedor` = '$id_fornecedor' 
            AND p.`status` < 2 ORDER BY p.id_pedido ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
<html>
<head>
<title>.:: Transportar Itens de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[2];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_pedido=<?=$id_pedido;?>'" class='botao'>
            &nbsp;
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
    }else {
?>
<html>
<head>
<title>.:: Transportar Itens de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_pedido_transportar) {
    var pergunta = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA TRANSPORTAR ESSE(S) ITEM(NS) PARA O PED N.º '+id_pedido_transportar+' ?')
    if(pergunta == false) {
        return false
    }else {
        document.form.id_pedido_transportar.value = id_pedido_transportar
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Transportar Itens do Pedido N.º&nbsp;<font color="yellow"><?=$id_pedido;?></font>
        </td>
    </tr>
<?
    $printar_nota = ($tipo_nota == 1) ? 'NF' : 'SGD';
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='9'>
            <font color='yellow'>
                Pedido(s) em aberto do fornecedor: 
                <font color='#FFFFFF'>
                    <?=$razao_social;?>
                </font> 
                - Tipo de Pedido: 
                <font color='#FFFFFF'>
                    <?=$printar_nota;?>
                </font>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <font title='N.&ordm; do Pedido' style='cursor:help'>
                N.&ordm; Ped
            </font>
        </td>
        <td>
            <font title='Data de Emissão' style='cursor:help'>
                Data Em.
            </font>
        </td>
        <td>
            Empresa
        </td>
        <td>
            Vendedor
        </td>
        <td>
            Funcionário <br/>Cotador
        </td>
        <td>
            Desconto <br/>Especial
        </td>
        <td>
            Data de <br/>Chegada
        </td>
        <td>
            Descrição DLL
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $executar = 'javascript:prosseguir('.$campos[$i]['id_pedido'].')';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$executar;?>" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$executar;?>">
            <a href="#" class='link'>
                <?=$campos[$i]['id_pedido'];?>
            </a>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['vendedor'];?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario_cotado']."' LIMIT 1 ";
            $campos_func = bancos::sql($sql);
            echo $campos_func[0]['nome'];
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['desconto_especial_porc'], 2, ',', '.').' %';?>
        </td>
        <td>
        <?
            $data_entrega = data::datetodata($campos[$i]['prazo_entrega'], '/');
//Verifica se o fornecedor é internacional
            if($campos[$i]['id_pais'] == 31) {
                echo $data_entrega;
            }else {
                echo data::adicionar_data_hora($data_entrega, $campos[$i]['prazo_navio']);
            }
        ?>
        </td>
        <td align='left'>
        <?
            if($campos[$i]['tipo_export'] == 'E') {
                $tipo_export = 'Exportação';
            }else if($campos[$i]['tipo_export'] == 'N') {
                $tipo_export = 'Nacional';
            }else if($campos[$i]['tipo_export'] == 'I') {
                $tipo_export = 'Importação';
            }
            $condicao_ddl = $campos[$i]['desc_ddl'].' - '.$tipo_export;
/****************************************************************************************************/
/*************************************** Financiamento  *********************************************/
/****************************************************************************************************/
//Aqui eu busco todas as Parcelas do Financiamento que foi feitas p/ o Pedido ...
            $sql = "SELECT pf.*, tm.simbolo 
                    FROM `pedidos_financiamentos` pf 
                    INNER JOIN `pedidos` p ON p.id_pedido = pf.id_pedido 
                    INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = p.id_tipo_moeda 
                    WHERE pf.id_pedido = ".$campos[$i]['id_pedido']." ORDER BY pf.dias ";
            $campos_financiamento = bancos::sql($sql);
            $linhas_financiamento = count($campos_financiamento);
            if($linhas_financiamento > 0) {//Se foi encontrado pelo menos 1 Financiamento ...
                for($j = 0; $j < $linhas_financiamento; $j++) {
                    if($j == 0) {//Se eu estiver na Primeira parcela
                        $primeira_parcela = $campos_financiamento[$j]['dias'];
                    }else if($j + 1 == $linhas_financiamento) {//Última Parcela
                        $ultima_parcela = $campos_financiamento[$j]['dias'];
                    }
                }
                $exibir_nota = ($tipo_nota == 1) ? 'NF' : 'SGD';
                $condicao_ddl = $linhas_financiamento.' parc. ('.$primeira_parcela.' à '.$ultima_parcela.' DDL) '.$exibir_nota.' '.$tipo_nota_porc.' %';
            }
            echo $condicao_ddl;
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'transportar_outro_pedido.php?id_pedido=<?=$id_pedido;?>'" class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_pedido' value='<?=$id_pedido;?>'>
<input type='hidden' name='hdd_item_pedido' value='<?=$vetor_item_pedido;?>'>
<input type='hidden' name='hdd_quantidade' value='<?=$vetor_quantidade;?>'>
<!--Essa variável aponta para qual o pedido que eu desejo transportar os dados-->
<input type='hidden' name='id_pedido_transportar'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
//Aqui transforma em um vetor para poder disparar o loop com os itens
    $vetor_item_pedido 	= explode(',', $_POST['hdd_item_pedido']);
    $vetor_quantidade 	= explode(',', $_POST['hdd_quantidade']);
    
    //Aqui eu verifico as Iniciais do Nome da Importação do Pedido que será transportado ...
    $sql = "SELECT i.nome 
            FROM `pedidos` p 
            LEFT JOIN `importacoes` i ON i.id_importacao = p.id_importacao 
            WHERE p.`id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
    $campos_importacao = bancos::sql($sql);
    //CO é uma ordem de Pedido que a Great manda - só acontecerá isso mesmo quando o Fornecedor do Pedido for Estrangeiro ...
    if(substr(strtoupper($campos_importacao[0]['nome']), 0, 2) == 'CO') {
        //Atualizo todas as Marcas / Obs de todos os Itens do Pedido com o nome da Importação ...
        $marca = $campos_importacao[0]['nome'];
    }
    for($i = 0; $i < count($vetor_item_pedido); $i++) {
        //Busca todos os campos do Item de Pedido que irá para o Novo Pedido ...
        $sql = "SELECT * 
                FROM `itens_pedidos` 
                WHERE `id_item_pedido` = '$vetor_item_pedido[$i]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        //Se não existir uma Marca de Importação, então eu adapto a marca do Item do Pedido ...
        if(empty($marca)) $marca = $campos[0]['marca'];
        //Aqui eu verifico se a Qtde digitada pelo usuário é exatamente a Qtde Total do Pedido ...
        if($vetor_quantidade[$i] == $campos[0]['qtde']) {//Se sim apenas transporto o Item do Pedido para o Novo Pedido ...
            $sql = "UPDATE `itens_pedidos` SET `id_pedido` = '$id_pedido_transportar', `qtde` = '$vetor_quantidade[$i]', `marca` = '$marca' WHERE `id_item_pedido` = '$vetor_item_pedido[$i]' LIMIT 1 ";
            bancos::sql($sql);
        }else {//Se não eu transporto a Qtde parcial digitada pelo usuário, e deixo no Pedido original o restante ...
            //Insere no Novo Pedido o mesmo Item com a Qtde que o usuário solicitou ...
            $id_fornecedor          = (is_null($campos[0]['id_fornecedor'])) ? 'NULL' : $campos[0]['id_fornecedor'];
            $id_fornecedor_terceiro = (is_null($campos[0]['id_fornecedor_terceiro'])) ? 'NULL' : $campos[0]['id_fornecedor_terceiro'];
            
            $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `id_fornecedor`, `id_fornecedor_terceiro`, `cod_tipo_ajuste`, `preco_unitario`, `desconto_especial`, `qtde`, `ipi`, `marca`, `estocar`, `status`) VALUES (NULL, '$id_pedido_transportar', '".$campos[0]['id_produto_insumo']."', $id_fornecedor, $id_fornecedor_terceiro, '".$campos[0]['cod_tipo_ajuste']."', '".$campos[0]['preco_unitario']."', '".$campos[0]['desconto_especial']."', '$vetor_quantidade[$i]', '".$campos[0]['ipi']."', '$marca', '".$campos[0]['estocar']."', '0') "; 
            bancos::sql($sql);
            
            $sql = "UPDATE `itens_pedidos` SET `qtde` = `qtde` - $vetor_quantidade[$i] WHERE `id_item_pedido` = '$vetor_item_pedido[$i]' LIMIT 1 ";
            bancos::sql($sql);
        }
        /*********************************Importação*********************************/
        //Busca de alguns dados para tratar caso seja Importação no "Pedido Original" somente ...
        $sql = "SELECT f.id_pais, p.data_emissao, p.prazo_entrega, p.prazo_navio 
                FROM `pedidos` p 
                INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos_pedidos = bancos::sql($sql);
        //Significa que esse Pedido é uma Importação ...
        if($campos_pedidos[0]['id_pais'] != 31) {
            $data_emissao_para_funcao 	= substr($campos_pedidos[0]['data_emissao'], 0, 10);
            $prazo_entrega_para_funcao 	= $campos_pedidos[0]['prazo_entrega'];
            $prazo_entrega_funcao       = data::diferenca_data($data_emissao_para_funcao, $prazo_entrega_para_funcao);
            $entrega                    = $prazo_entrega_funcao[0];
            //Aqui eu atualizo a importação do Pedido caso exista alguma ...
            //Aqui adiciona os 3 dias padrão + o valor do navio + o prazo de entrega
            $soma_prazo                 = 3 + (integer)$campos_pedidos[0]['prazo_navio'] + (integer)$entrega;
            //Aqui soma a data de emissão mais a somatória de prazos
            compras_new::atualizar_importacao($id_pedido, $soma_prazo);
        }
/****************************************************************************/
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'transportar_outro_pedido.php?id_pedido=<?=$id_pedido;?>&valor=3'
    </Script>
<?
}else {
//Busca do nome do Fornecedor e do Tipo de Moeda ...
    $sql = "SELECT f.razaosocial, CONCAT(tm.simbolo, ' ') AS moeda 
                FROM `pedidos` p 
                INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = p.id_tipo_moeda 
                WHERE p.id_pedido = '$id_pedido' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $razao_social = $campos[0]['razaosocial'];
    $moeda = $campos[0]['moeda'];
//Busca dos Itens do Pedido ...
    $sql = "SELECT ip.*, g.referencia, pi.id_produto_insumo, pi.discriminacao, u.sigla 
            FROM `itens_pedidos` ip 
            INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo 
            INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
            WHERE ip.`id_pedido` = '$id_pedido' 
            AND ip.`status` = '0' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
<html>
<head>
<title>.:: Transportando Itens de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'outras_opcoes.php?id_pedido=<?=$id_pedido;?>'" class='botao'>
            &nbsp;
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
    }else {
?>
<html>
<head>
<title>.:: Transportando Itens de Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'transportar_outro_pedido.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') if(elementos[i].checked == true) valor = true
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        var linhas = (typeof(elementos['chkt_item_pedido[]'][0]) == 'undefined') ? 1 : elementos['chkt_item_pedido[]'].length
        for(var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_item_pedido'+i).checked == true) {
                if(document.getElementById('txt_quantidade'+i).value == '' || document.getElementById('txt_quantidade'+i).value == '0,00') {
                    alert('DIGITE A QUANTIDADE À TRANSPORTAR DO ITEM !')
                    document.getElementById('txt_quantidade'+i).focus()
                    return false
                }
                //Verifica se a Quantidade que será transportada 
                if(eval(strtofloat(document.getElementById('txt_quantidade'+i).value)) > document.getElementById('hdd_quantidade'+i).value) {
                    alert('QUANTIDADE À TRANSPORTAR INVÁLIDA !\nQUANTIDADE MAIOR DO QUE A QUANTIDADE ORIGINAL !')
                    document.getElementById('txt_quantidade'+i).focus()
                    document.getElementById('txt_quantidade'+i).select()
                    return false
                }
            }
        }
        //Prepara para gravar no Banco de Dados ...
        for(var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_item_pedido'+i).checked == true) {
                document.getElementById('txt_quantidade'+i).value = eval(strtofloat(document.getElementById('txt_quantidade'+i).value))
            }
        }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
        document.form.nao_atualizar.value = 1
        return true
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
<?
//Só atualiza a tela de baixo, no caso de ter transportado pelo menos 1 item p/ outro Pedido
    if(!empty($valor)) {
?>
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
        if(document.form.nao_atualizar.value == 0) {
            window.opener.parent.itens.document.form.submit()
            window.opener.parent.rodape.document.form.submit()
        }
<?
    }
?>
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit="return validar()">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Transportando Itens do Pedido N.º&nbsp;
            <font color='yellow'>
                <?=$id_pedido;?> - <?=$razao_social;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' onClick="selecionar(totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Qtde
        </td>
        <td>
            Un
        </td>
        <td>
            Produto
        </td>
        <td>
            Preço Unit. <?=$moeda;?>
        </td>
        <td>
            Valor Total
        </td>
        <td>
            Ipi %
        </td>
        <td>
            Valor c/ IPI
        </td>
        <td>
            Marca / Obs
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_item_pedido[]' id="chkt_item_pedido<?=$i;?>" value="<?=$campos[$i]['id_item_pedido'];?>" onclick="checkbox('<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <input type='text' name='txt_quantidade[]' id="txt_quantidade<?=$i;?>" value="<?=number_format($campos[$i]['qtde'], 2, ',', '.');?>" onKeyUp="verifica(this, 'moeda_especial', 2, '', event);calcular_total_geral('<?=$i;?>')" onclick="checkbox('<?=$i;?>', '#E8E8E8');focos(this)" maxlength='7' size='9' class='textdisabled' disabled>
            <input type='hidden' name='hdd_quantidade[]' id="hdd_quantidade<?=$i;?>" value="<?=$campos[$i]['qtde'];?>">
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=$campos[$i]['sigla'];?>
            </font>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif'>
            <?
                    $referencia = genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia'], 0);
/*Aqui eu verifico qual é o Tipo de Produto que estou utilizando, isso porque se for aço 1020 ou 1045
tenho que estar apresentando uma mensagem a mais no fim do Pedido*/
                    $discriminacao_corrent = strtok(strchr($campos[$i]['discriminacao'], 'TREFILADO'), 'I');
                    if($referencia == 'ACO' && $discriminacao_corrent == 'TREF') $aco_trefilado++;
//Apresentado o Produto normalmente ...
                    echo $referencia.' * '.$campos[$i]['discriminacao'];
//Significa que é um Produto do Tipo não Estocável
                    if($campos[$i]['estocar'] == 0) {
//Se eu não vou estocar, esse Produto, então significa que este vai para alguém, então busco p/ qual fornec
                            if($campos[$i]['id_fornecedor_terceiro'] != 0) {
//Busca o nome do Fornecedor que deve ser cobrado
                                    $sql = "SELECT razaosocial 
                                                    FROM `fornecedores` 
                                                    WHERE `id_fornecedor` = ".$campos[$i]['id_fornecedor_terceiro']." LIMIT 1 ";
                                    $campos_dados = bancos::sql($sql);
                            }
                            echo "<font color='red' title='Não Estocar - Enviar p/: ".$campos_dados[0]['razaosocial']."' style='cursor:help'><b> (N.E) </b></font>";
                    }
//Significa que esse Produto tem débito com Fornecedor
                    if($campos[$i]['id_fornecedor'] != 0) {
//Busca o nome do Fornecedor que deve ser cobrado
                            $sql = "SELECT razaosocial 
                                            FROM `fornecedores` 
                                            WHERE `id_fornecedor` = ".$campos[$i]['id_fornecedor']." LIMIT 1 ";
                            $campos_dados = bancos::sql($sql);
                            echo "<font color='red' title='Debitar do(a): ".$campos_dados[0]['razaosocial']."' style='cursor:help'><b> (DEB) </b></font>";
                    }
//Aqui eu verifico qual que é o PA referente a esse PI devido, esse Pedido ser atrelado a uma OS
                    if($tem_os_importada == 1) {
                            $sql = "SELECT pa.id_produto_acabado, pa.referencia 
                                            FROM `oss_itens` oi 
                                            INNER JOIN `ops` ON ops.id_op = oi.id_op 
                                            INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ops.id_produto_acabado 
                                            WHERE oi.id_os = '$id_os' 
                                            AND oi.id_produto_insumo_ctt = ".$campos[$i]['id_produto_insumo']." ";
                            $campos_dados = bancos::sql($sql);
                            echo ' / '.intermodular::pa_discriminacao($campos_dados[0]['id_produto_acabado']);
                    }
    ?>
                </font>
        </td>
        <td>
            <input type='text' name='txt_preco_unitario[]' id="txt_preco_unitario<?=$i;?>" value="<?=number_format($campos[$i]['preco_unitario'], 2, ',', '.');?>" maxlength='7' size='9' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_valor_total[]' id="txt_valor_total<?=$i;?>" value="<?=number_format($campos[$i]['qtde'] * $campos[$i]['preco_unitario'], 2, ',', '.');?>" maxlength='7' size='9' class='textdisabled' disabled>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
                if(($campos[$i]['ipi'] == 0) or ($tipo_nota == 2)) {//SGD
                    echo '&nbsp;';
                }else {//NF
                    echo $campos[$i]['ipi'];
                }
            ?>
            </font>
        </td>
        <td align='right'>
        <?
            $ipi            = ($tipo_nota == 2) ? 0 : $campos[$i]['ipi'];//IPI só para NFs do Tipo NF ...
            $valor_com_ipi 	= ($campos[$i]['qtde'] * $campos[$i]['preco_unitario'] * $ipi) / 100;
            echo $moeda.number_format($valor_com_ipi, 2, ',', '');
            $valor_total_ipi+= $valor_com_ipi; 
        ?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['marca'])) {
                echo $campos[$i]['marca'];
            }else {
                echo '&nbsp';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'outras_opcoes.php?id_pedido=<?=$id_pedido;?>'" class='botao'>
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class='botao'>
            <input type='submit' name="cmd_avancar" value="&gt;&gt; Avançar &gt;&gt;" title="Avançar" class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_pedido' value="<?=$id_pedido;?>">
<input type='hidden' name='nao_atualizar'>
</form>
</body>
</html>
<?
    }
}
?>