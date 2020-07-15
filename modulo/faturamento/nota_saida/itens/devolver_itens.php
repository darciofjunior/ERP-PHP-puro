<?
require('../../../../lib/segurancas.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/faturamentos.php');
//Significa que veio do Menu de Devolu��o
segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ITEM(NS) DEVOLVIDO(S) COM SUCESSO.</font>";

//Logo de cara j� verifico se est� Nota j� foi importada p/ o Financeiro ...
$importado_financeiro = faturamentos::importado_financeiro($_GET['id_nf']);
if($importado_financeiro == 'S') {//Significa que a NF j� est� importada no Financeiro ...
    echo '<font color="red"><div align="center"><b>EST� NF N�O PODE SER + ALTERADA DEVIDO ESTAR IMPORTADA NO FINANCEIRO !</b></div></font>';
    exit;
}

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_nf                  = $_POST['id_nf'];
        $txt_referencia         = $_POST['txt_referencia'];
        $txt_discriminacao      = $_POST['txt_discriminacao'];
        $txt_numero_nf_saida    = $_POST['txt_numero_nf_saida'];
    }else {
        $id_nf                  = $_GET['id_nf'];
        $txt_referencia         = $_GET['txt_referencia'];
        $txt_discriminacao      = $_GET['txt_discriminacao'];
        $txt_numero_nf_saida    = $_GET['txt_numero_nf_saida'];
    } 
    /*Aqui eu busco alguns dados da pr�pria NF de Devolu��o que servir� de controle p/ a tela e aux�lio p/ o SQL 
    abaixo onde exibir� as NF(s) de Sa�da ...*/
    $sql = "SELECT c.`id_pais`, nfs.`id_cliente`, nfs.`id_empresa`, nfs.`id_nf_num_nota`, nfs.`snf_devolvida` 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            WHERE nfs.`id_nf` = '$id_nf' LIMIT 1 ";
    $campos_nf_devolucao    = bancos::sql($sql);
    $id_cliente             = $campos_nf_devolucao[0]['id_cliente'];
    $id_empresa_nf          = $campos_nf_devolucao[0]['id_empresa'];
    $id_nf_num_nota         = $campos_nf_devolucao[0]['id_nf_num_nota'];
    $snf_devolvida          = $campos_nf_devolucao[0]['snf_devolvida'];
    $tipo_moeda             = ($campos_nf_devolucao[0]['id_pais']) ? 'R$ ' : 'U$ ';

    /*Seleciono todos os itens de NF(s) de Sa�da do Cliente e da Empresa da NF de Devolu��o que est�o como Despachadas, 
    enquanto a Qtde de Sa�da for maior do que a Qtde Devolvida ...*/
    $sql = "SELECT pvi.`id_pedido_venda`, pvi.`preco_liq_final`, 
            DATE_FORMAT(nfs.`data_emissao`, '%d/%m/%Y') AS data_emissao, nfsi.`id_nfs_item`, nfsi.`id_nf`, 
            nfsi.`id_produto_acabado`, nfsi.`qtde`, nfsi.`valor_unitario`, nfsi.`comissao_new` 
            FROM `nfs_itens` nfsi 
            INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` AND nfs.`id_cliente` = '$id_cliente' AND nfs.`id_empresa` = '$id_empresa_nf' AND nfs.`status` = '4' 
            INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` AND nnn.`numero_nf` LIKE '%$txt_numero_nf_saida%' 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
            WHERE nfsi.`status` < '2' 
            AND (nfsi.`qtde` - nfsi.`qtde_devolvida` > '0') ORDER BY nfs.`data_emissao` DESC ";
    $campos = bancos::sql($sql, $inicio, 15, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'devolver_itens.php?id_nf=<?=$id_nf;?>&valor=1'
    </Script>
<?
    }else {
/**************************Novo Controle de N.� **************************/
/*Aqui eu verifico se o Cabe�alho de Nota Fiscal foi preenchido com um N�mero de Nosso Talon�rio isso quando 
Albafer ou Tool Master ou preenchido com um N�mero de Nota Fiscal do Cliente ...*/
	if(($id_nf_num_nota == 0 && $id_empresa_nf != 4) && empty($snf_devolvida)) {//N�o foi preenchido com Nenhum N.�, ent�o ...
?>
	<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
	<Script Language = 'JavaScript'>
            alert('SELECIONE UM N.� PARA ESTA NF DE DEVOLU��O !')
            window.close()
/*Aqui eu passo a opcao como sendo 1, porque somente no primeiro Menu que eu posso 
incluir Itens de Nota Fiscal*/
            nova_janela('../dados_iniciais.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>&acao=G', 'DADOS_INICIAIS', '', '', '', '', '290', '750', 'c', 'c', '', '', 's', 's', '', '', '')
	</Script>
<?
            exit;
	}
/*************************************************************************/
?>
<html>
<head>
<title>.:: Devolver Itens de Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_devolver_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var checkbox_selecionados   = 0, valor = false, elementos = document.form.elements
    var id_funcionario          = eval('<?=$_SESSION['id_funcionario'];?>')
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo')  {
            if(elementos[i].checked == true) {
                valor = true
                checkbox_selecionados++
            }
        }
    }

    if(valor == false) {
        alert('SELECIONE UMA OP��O !')
        return false
    }

    if(typeof(elementos['chkt_nfs_item[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['chkt_nfs_item[]'].length)
    }
    
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_nfs_item'+i).checked == true) {
            //Quantidade ...
            if(document.getElementById('txt_qtde_devolver'+i).value == '') {
                alert('DIGITE A QUANTIDADE !')
                document.getElementById('txt_qtde_devolver'+i).focus()
                return  false
            }
            if(document.getElementById('txt_qtde_devolver'+i).value == 0) {
                alert('QUANTIDADE INV�LIDA !')
                document.getElementById('txt_qtde_devolver'+i).focus()
                document.getElementById('txt_qtde_devolver'+i).select()
                return  false
            }
            //Verifica se o valor digitado na Nota Fiscal � > do que o valor q est� em Pedido e q j� foi faturado
            var qtde_devolver   = eval(strtofloat(document.getElementById('txt_qtde_devolver'+i).value))
            var qtde_original   = eval(strtofloat(document.getElementById('hdd_qtde_original'+i).value))
            if(qtde_devolver > qtde_original) {
                alert('QUANTIDADE A DEVOLVER INV�LIDA !\nQUANTIDADE A DEVOLVER MAIOR DO QUE A QUANTIDADE INICIAL !')
                document.getElementById('txt_qtde_devolver'+i).focus()
                document.getElementById('txt_qtde_devolver'+i).select()
                return false
            }
            
            //Pre�o L�quido Final ...
            if(document.getElementById('txt_preco_liq_devolver'+i).value == '') {
                alert('DIGITE O PRE�O L�QUIDO � DEVOLVER !')
                document.getElementById('txt_preco_liq_devolver'+i).focus()
                document.getElementById('txt_preco_liq_devolver'+i).select()
                return  false
            }
            //Se o funcion�rio logado for Diferente de Roberto 62 e D�rcio 98, o sistema ir� fazer a verifica��o abaixo ...
            if(id_funcionario != 62 && id_funcionario != 98) {
                var preco_liq_devolver  = eval(strtofloat(document.getElementById('txt_preco_liq_devolver'+i).value))
                var preco_liq_original  = eval(strtofloat(document.getElementById('hdd_preco_liq_original'+i).value))
                
                if(preco_liq_devolver != preco_liq_original) {
                    alert('PRE�O L�QUIDO � DEVOLVER INV�LIDO !!!\n\nPRE�O L�QUIDO � DEVOLVER DIFERENTE DO PRE�O INICIAL !')
                    document.getElementById('txt_preco_liq_devolver'+i).focus()
                    document.getElementById('txt_preco_liq_devolver'+i).select()
                    return false
                }
            }
            
            //Comiss�o % ...
            if(document.getElementById('txt_comissao_devolver'+i).value == '') {
                alert('DIGITE O PRE�O L�QUIDO � DEVOLVER !')
                document.getElementById('txt_comissao_devolver'+i).focus()
                document.getElementById('txt_comissao_devolver'+i).select()
                return  false
            }
            //Se o funcion�rio logado for Diferente de Roberto 62 e D�rcio 98, o sistema ir� fazer a verifica��o abaixo ...
            if(id_funcionario != 62 && id_funcionario != 98) {
                var comissao_devolver   = eval(strtofloat(document.getElementById('txt_comissao_devolver'+i).value))
                var comissao_original   = eval(strtofloat(document.getElementById('hdd_comissao_original'+i).value))
                
                if(comissao_devolver != comissao_original) {
                    alert('% COMISS�O INV�LIDA !!!\n\nCOMISS�O � DEVOLVER DIFERENTE DA COMISS�O INICIAL !')
                    document.getElementById('txt_comissao_devolver'+i).focus()
                    document.getElementById('txt_comissao_devolver'+i).select()
                    return false
                }
            }
        }
    }
/*Desabilito as caixas abaixo e tamb�m fa�o tratamento destas p/ gravar no BD, isso � feito porque n�o s�o 
desabilitadas essas caixas quando o produto � do tipo ESP ...*/
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_nfs_item'+i).checked == true) {
            document.getElementById('txt_qtde_devolver'+i).disabled         = false
            document.getElementById('txt_preco_liq_devolver'+i).disabled    = false
            document.getElementById('txt_comissao_devolver'+i).disabled     = false
            document.getElementById('txt_qtde_devolver'+i).value            = strtofloat(document.getElementById('txt_qtde_devolver'+i).value)
            document.getElementById('txt_preco_liq_devolver'+i).value       = strtofloat(document.getElementById('txt_preco_liq_devolver'+i).value)
            document.getElementById('txt_comissao_devolver'+i).value        = strtofloat(document.getElementById('txt_comissao_devolver'+i).value)
        }
    }
    //Aqui � para n�o atualizar o frames abaixo desse Pop-UP ...
    document.form.nao_atualizar.value = 1
}

function redefinir_formulario() {
    var resposta = confirm('DESEJA REDEFINIR ?')
    if(resposta == true) {
        document.form.chkt_tudo.checked = false
        selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Devolver Itens p/ a NF de Entrada N.� 
            <font color='yellow'>
                <?=faturamentos::buscar_numero_nf($id_nf, 'D');?>
            </font>
        </td>
    </tr>
</table>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            NF Sa�da <br/>N.�
        </td>
        <td>
            Data de Emiss�o
        </td>
        <td>
            <b>Qtde <br/>Real</b>
        </td>
        <td>
            <b>Qtde <br/>Devolvida</b>
        </td>
        <td>
            <b>Qtde <br/>� Devolver</b>
        </td>
        <td>
            <b>Produto</b>
        </td>
        <td>
            <b>Pre�o <br/>L�q. Final <?=$tipo_moeda;?></b>
        </td>
        <td>
            <b>Comiss�o %</b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_nfs_item[]' id='chkt_nfs_item<?=$i;?>' value='<?=$campos[$i]['id_nfs_item'];?>' onclick="checkbox('<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <a href = 'detalhes_nota_fiscal.php?id_nf=<?=$campos[$i]['id_nf'];?>&pop_up=1' class='html5lightbox'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td>
            <font color='darkblue'>
                <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
            </font>
        </td>
        <td>
        <?
//Aqui eu busco a qtde devolvida do Item referente a Nota Fiscal de Entrada "Secund�ria" 
            $sql = "SELECT SUM(`qtde_devolvida`) AS qtde_devolvida 
                    FROM `nfs_itens` 
                    WHERE `id_nf_item_devolvida` = '".$campos[$i]['id_nfs_item']."' ";
            $campos_devolvida = bancos::sql($sql);
        ?>
            <font color='red'>
                <b><?=number_format($campos_devolvida[0]['qtde_devolvida'], 2, ',', '.');?></b>
            </font>
        </td>
        <td>
        <?
//Utilizo abs, para me retornar somente o n�mero positivo
            $restante_importar = $campos[$i]['qtde'] - $campos_devolvida[0]['qtde_devolvida'];
        ?>
            <input type='text' name='txt_qtde_devolver[]' id='txt_qtde_devolver<?=$i;?>' value='<?=number_format($restante_importar, 0, ',', '');?>' title='Digite a Quantidade' maxlength='8' size='8' onclick="checkbox('<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="if(this.value == 0) {this.value = ''};verifica(this, 'moeda_especial', '2', '', event)" class='textdisabled' disabled>
            <input type='hidden' name='hdd_qtde_original[]' id='hdd_qtde_original<?=$i;?>' value='<?=number_format($restante_importar, 0, ',', '');?>'>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
        </td>
        <td align='right'>
            <input type='text' name='txt_preco_liq_devolver[]' id='txt_preco_liq_devolver<?=$i;?>' value='<?=number_format($campos[$i]['valor_unitario'], 2, ',', '.');?>' title='Digite o Pre�o L�quido a Devolver <?=$tipo_moeda;?>' maxlength='8' size='8' onclick="checkbox('<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="if(this.value == 0) {this.value = ''};verifica(this, 'moeda_especial', '2', '', event)" class='textdisabled' disabled>
            <input type='hidden' name='hdd_preco_liq_original[]' id='hdd_preco_liq_original<?=$i;?>' value='<?=number_format($campos[$i]['valor_unitario'], 2, ',', '.');?>'>
        </td>
        <td>
            <input type='text' name='txt_comissao_devolver[]' id='txt_comissao_devolver<?=$i;?>' value='<?=number_format($campos[$i]['comissao_new'], 2, ',', '.');?>' title='Digite a % de Comiss�o' maxlength='8' size='8' onclick="checkbox('<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="if(this.value == 0) {this.value = ''};verifica(this, 'moeda_especial', '2', '', event)" class='textdisabled' disabled>
            <input type='hidden' name='hdd_comissao_original[]' id='hdd_comissao_original<?=$i;?>' value='<?=number_format($campos[$i]['comissao_new'], 2, ',', '.');?>'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'devolver_itens.php?id_nf=<?=$id_nf;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='redefinir_formulario()' style='color:#ff9900;' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
    <?/****************************************************************************************************/?>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<!--****************Controles de Tela***************-->
<input type='hidden' name='id_nf' value='<?=$id_nf;?>'>
<input type='hidden' name='nao_atualizar'>
<!--************************************************-->
</form>
</body>
</html>
<pre>
<font color="darkgreen"><b>
    CFOP(s) definidas no Cabe�alho da NF: 
</b></font>
<?
        $sql = "SELECT id_cfop_revenda, concat(cfop, '.', num_cfop) AS cfop_industrial, concat(cfop, '.', num_cfop, ' - ', natureza_operacao_resumida) AS cfop_industrial_descritivo 
                FROM `cfops` 
                WHERE `id_cfop` = '$id_cfop' LIMIT 1 ";
        $campos_cfop = bancos::sql($sql);
        echo '<b>CFOP 1: </b>'.$campos_cfop[0]['cfop_industrial_descritivo'];

        if($campos_cfop[0]['id_cfop_revenda'] != 0) {
            $sql = "SELECT CONCAT(cfop, '.', num_cfop, ' - ', natureza_operacao_resumida) AS cfop_revenda_descritivo 
                    FROM `cfops` 
                    WHERE `id_cfop` = ".$campos_cfop[0]['id_cfop_revenda']." 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_cfop_revenda = bancos::sql($sql);
            echo '<br/><b>CFOP 2: </b>'.$campos_cfop_revenda[0]['cfop_revenda_descritivo'];
        }
    }
}else if($passo == 2) {
    $data_sys = date('Y-m-d H:i:s');
//Aqui � a parte da devolu��o dos itens da Nota Fiscal de Devolu��o
    for($i = 0; $i < count($_POST['chkt_nfs_item']); $i++) {
/* Observa��o muito importante: s� lembrando que se a NF de Sa�da for de Exporta��o, ent�o 
na NF de Entrada com certeza ir� gravar zerado os impostos de ipi, icms, redu��o ...*/
//1) Busca de Dados da Nota Fiscal de Sa�da "Principal" ...
        $sql = "SELECT * 
                FROM nfs_itens 
                WHERE `id_nfs_item` = '".$_POST['chkt_nfs_item'][$i]."' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $id_pedido_venda_item   = $campos[0]['id_pedido_venda_item'];
        $id_produto_acabado     = $campos[0]['id_produto_acabado'];
        $id_representante       = $campos[0]['id_representante'];
        $id_classific_fiscal    = $campos[0]['id_classific_fiscal'];
        $qtde                   = $campos[0]['qtde'];
        $valor_unitario_exp     = $campos[0]['valor_unitario_exp'];
        $ipi                    = $campos[0]['ipi'];
        $icms                   = $campos[0]['icms'];
        $reducao                = $campos[0]['reducao'];
        $icms_intraestadual     = $campos[0]['icms_intraestadual'];
        $iva                    = $campos[0]['iva'];
//Aqui eu verifico se existe uma Devolu��o anterior desse Item anteriormente na NF corrente que estou trab ...
        $sql = "SELECT id_nfs_item 
                FROM `nfs_itens` 
                WHERE `id_nf` = '$_POST[id_nf]' 
                AND `id_nf_item_devolvida` = '".$_POST['chkt_nfs_item'][$i]."' LIMIT 1 ";
        $campos_devolvida = bancos::sql($sql);
        if(count($campos_devolvida) == 0) {//N�o existe ...
//1.1) Inserindo Item na Tabela de Notas Fiscais - NF Secund�ria
            $sql = "INSERT INTO `nfs_itens` (`id_nfs_item`, `id_nf`, `id_pedido_venda_item`, `id_produto_acabado`, `id_representante`, `id_nf_item_devolvida`, `id_classific_fiscal`, `qtde`, `qtde_devolvida`, `valor_unitario`, `valor_unitario_exp`, `comissao_new`, `ipi`, `icms`, `reducao`, `icms_intraestadual`, `iva`, `data_sys`) 
                    VALUES (NULL, '$_POST[id_nf]', '$id_pedido_venda_item', '$id_produto_acabado', '$id_representante', '$chkt_nfs_item[$i]', '$id_classific_fiscal', '$qtde', '".$_POST['txt_qtde_devolver'][$i]."', '".$_POST['txt_preco_liq_devolver'][$i]."', '$valor_unitario_exp', '".$_POST['txt_comissao_devolver'][$i]."', '$ipi', '$icms', '$reducao', '$icms_intraestadual', '$iva', '$data_sys') ";
            bancos::sql($sql);
            $id_nfs_item = bancos::id_registro();
        }else {//J� existe pelo menos 1 Devolu��o - NF Secund�ria ...
            $sql = "UPDATE `nfs_itens` SET `qtde_devolvida` = `qtde_devolvida`+ '".$_POST['txt_qtde_devolver'][$i]."', 
                    `valor_unitario` = '$txt_preco_liq_devolver[$i]', `comissao_new` = '$txt_comissao_devolver[$i]', 
                    `data_sys` = '$data_sys' 
                    WHERE `id_nfs_item` = '".$campos_devolvida[0]['id_nfs_item']."' LIMIT 1 ";
            bancos::sql($sql);
        }
/*Atualizo na Tabela "pedidos_vendas_itens" o campo Quantidade "Qtde Devolvida", esse campo foi criado 
na inten��o de corrigir e agilizar alguns "Relat�rios de Pedidos de Vendas" ...*/
        $sql = "UPDATE `pedidos_vendas_itens` SET `qtde_devolvida` = `qtde_devolvida` + '".$_POST['txt_qtde_devolver'][$i]."' WHERE `id_pedido_venda_item` = '$id_pedido_venda_item' LIMIT 1 ";
        bancos::sql($sql);
//Aqui eu busco a Qtde Total do Item da Nota Principal que est� sendo devolvido ...
        $sql = "SELECT qtde 
                FROM `nfs_itens` 
                WHERE `id_nfs_item` = '$chkt_nfs_item[$i]' LIMIT 1 ";
        $campos     = bancos::sql($sql);
        $qtde_real  = $campos[0]['qtde'];
//Aqui eu busco a Qtde do Item da Nota Secund�ria p/ ver o quanto q j� se foi devolvido referente ao Item ...
        $sql = "SELECT qtde_devolvida 
                FROM `nfs_itens` 
                WHERE `id_nf_item_devolvida` = '$chkt_nfs_item[$i]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $qtde_devolvida = $campos[0]['qtde_devolvida'];
//Controle de Status do Item que foi Importado da Nota Fiscal ...
        if(($qtde_real - $qtde_devolvida) == 0) {//Importado Total
            $status_item = 2;
        }else {//Importado Parcial
            $status_item = 1;
        }
//Mudo o Status do Item Devolvido p/ Parcial ou Total ...
        $sql = "UPDATE `nfs_itens` SET `status` = '$status_item' WHERE `id_nfs_item`='$chkt_nfs_item[$i]' LIMIT 1 ";
        bancos::sql($sql);
        faturamentos::controle_estoque($_POST['id_nf'], $id_pedido_venda_item, $txt_qtde_devolver[$i], 0, 0, 3);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'devolver_itens.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar NF(s) de Sa�da p/ Devolver Item(ns) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onload="document.form.txt_referencia.focus()" onunload="atualizar_abaixo()">
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<!--***********Aqui eu guardo o id da NF de Devolu��o passado por par�metro ...************-->
<input type='hidden' name='id_nf' value='<?=$_GET['id_nf'];?>'>
<!--**Controle p/ atualizar os Frames abaixo desse Pop-UP somente quando fechar pelo X...**-->
<input type='hidden' name='nao_atualizar'>
<!--***************************************************************************************-->
<input type='hidden' name='passo' value='1'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar NF(s) de Sa�da p/ Devolver Item(ns)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Refer�ncia
        </td>
        <td>
            <input type='text' name='txt_referencia' title="Digite a Refer�ncia" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discrimina��o
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title="Digite a Discrimina��o" size='40' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.� da NF de Sa�da
        </td>
        <td>
            <input type='text' name='txt_numero_nf_saida' title="Digite o N.� da NF de Sa�da do Cliente" size='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="document.form.txt_referencia.focus()" style="color:#ff9900;" class="botao">
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick='document.form.nao_atualizar.value = 1' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>