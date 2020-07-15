<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ITEM(NS) INCLUIDO(S) COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ITEM(NS) JÁ EXISTENTE.</font>";
$mensagem[4] = "<font class='atencao'>NEM TODOS OS ITEM(NS) PODEM SER IMPORTADO(S), POIS EXISTE(M) ITEM(NS) QUE JÁ FORAM IMPORTADO(S) ANTERIORMENTE.</font>";

if($passo == 1) {
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_referencia     = $_POST['txt_referencia'];
        $txt_discriminacao  = $_POST['txt_discriminacao'];
        $id_nf_outra        = $_POST['id_nf_outra'];
        $id_cfop            = $_POST['id_cfop'];
    }else {
        $txt_referencia     = $_GET['txt_referencia'];
        $txt_discriminacao  = $_GET['txt_discriminacao'];
        $id_nf_outra        = $_GET['id_nf_outra'];
        $id_cfop            = $_GET['id_cfop'];
    }
/*Aqui eu tenho esse Tratamento devido com o % e |, devido o usuário utilizar o % 
como caracter ...*/
    $txt_discriminacao = str_replace('|', '%', $txt_discriminacao);

    $sql = "SELECT g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao` 
            FROM `produtos_insumos` pi 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`referencia` LIKE '%$txt_referencia%' 
            WHERE pi.`discriminacao` LIKE '%$txt_discriminacao%' 
            AND pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas  == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir_pi.php?id_nf_outra=<?=$id_nf_outra;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Incluir Item(ns) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_incluir_pis.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }

    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
  
    if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_produto_insumo[]'].length)
    }
//A variável objetos_fim, representa os elementos que estão fora do Loop ...
    for(var i = 0; i < linhas; i++) {
//Força o Preenchimento do Campo Quantidade ...
        if(document.getElementById('chkt_produto_insumo'+i).checked == true) {
//Quantidade ...
            if(document.getElementById('txt_qtde'+i).value == '') {
                alert('DIGITE A QUANTIDADE !')
                document.getElementById('txt_qtde'+i).focus()
                return false
            }
            if(document.getElementById('txt_qtde'+i).value == 0) {
                alert('QUANTIDADE INVÁLIDA !')
                document.getElementById('txt_qtde'+i).focus()
                document.getElementById('txt_qtde'+i).select()
                return false
            }
//Valor Unitário ...
            if(document.getElementById('txt_valor_unitario'+i).value == '') {
                alert('DIGITE O VALOR UNITÁRIO !')
                document.getElementById('txt_valor_unitario'+i).focus()
                return false
            }
            if(document.getElementById('txt_valor_unitario'+i).value == 0) {
                alert('VALOR UNITÁRIO INVÁLIDO !')
                document.getElementById('txt_valor_unitario'+i).focus()
                document.getElementById('txt_valor_unitario'+i).select()
                return false
            }
//Peso Unitário ...
            if(document.getElementById('txt_peso_unitario'+i).value == '') {
                alert('DIGITE O PESO UNITÁRIO !')
                document.getElementById('txt_peso_unitario'+i).focus()
                return false
            }
            if(document.getElementById('txt_peso_unitario'+i).value == 0) {
                alert('PESO UNITÁRIO INVÁLIDO !')
                document.getElementById('txt_peso_unitario'+i).focus()
                document.getElementById('txt_peso_unitario'+i).select()
                return false
            }
        }
    }
//Prepara no formato moeda antes de submeter para o BD ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_produto_insumo'+i).checked == true) {
            document.getElementById('txt_qtde'+i).value             = strtofloat(document.getElementById('txt_qtde'+i).value)
            document.getElementById('txt_valor_unitario'+i).value   = strtofloat(document.getElementById('txt_valor_unitario'+i).value)
            document.getElementById('txt_peso_unitario'+i).value    = strtofloat(document.getElementById('txt_peso_unitario'+i).value)
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit="return validar()">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Incluir Item(ns)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            Produto
        </td>
        <td>
            Qtde
        </td>
        <td>
            <font title='Valor Unitário R$' style='cursor:help'>
                Vlr Unit. R$
            </font>
        </td>
        <td>
            <font title='Peso Unitário Kg' style='cursor:help'>
                Peso Unit. Kg
            </font>
        </td>
    </tr>
<?
/************************************************************/
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_produto_insumo[]' id='chkt_produto_insumo<?=$i;?>' value='<?=$campos[$i]['id_produto_insumo'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align="left">
            <?=$campos[$i]['referencia'].'-'.$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <input type='text' name='txt_qtde[]' id='txt_qtde<?=$i;?>' title='Digite a Quantidade' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='9' size='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_valor_unitario[]' id='txt_valor_unitario<?=$i;?>' title='Digite o Valor Unitário' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='9' size='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_peso_unitario[]' id='txt_peso_unitario<?=$i;?>' title='Digite o Peso Unitário em KG' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '4', '', event)" size='14' maxlength='12' class='textdisabled' disabled>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir_pi.php?id_nf_outra=<?=$id_nf_outra;?>'" class='botao'>
            <input type='submit' name='cmd_incluir' value='Incluir' title='Incluir' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
<!--Controle de Tela-->
<input type='hidden' name='id_nf_outra' value='<?=$id_nf_outra;?>'>
<input type='hidden' name='id_cfop' value='<?=$id_cfop;?>'>
<!--****************-->
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
//Aki atualiza a Tabela de Nota Fiscal ...
    $data_sys = date('Y-m-d H:i:s');
/*************************************************************************************/
//Variáveis p/ fazer o controle da Mensagem
    $cont_itens_aceitos = 0;
    $cont_itens_ignorados = 0;
/*************************************************************************************/
//Busca dos Impostos na Tabela de CFOP de acordo com a CFOP passado por parâmetro ...
    $sql = "SELECT `ipi`, `icms` 
            FROM `cfops` 
            WHERE `id_cfop` = '$_POST[id_cfop]' LIMIT 1 ";
    $campos_cfop = bancos::sql($sql);

    foreach($_POST['chkt_produto_insumo'] as $i => $id_produto_insumo) {
//Verifico se já existe esse PA p/ está NF ...
        $sql = "SELECT `id_nf_outra` 
                FROM `nfs_outras_itens` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' 
                AND `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
        $campos_itens = bancos::sql($sql);
        if(count($campos_itens) == 0) {//Significa que este PA ainda não foi incluido nessa NF ...
//Busca de alguns Dados do PI ...
            $sql = "SELECT cf.`id_classific_fiscal`, cf.`ipi`, g.`referencia`, i.`icms`, i.`reducao`, pi.`id_unidade`, pi.`observacao` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = pi.`id_classific_fiscal` 
                    INNER JOIN `icms` i ON i.`id_classific_fiscal` = cf.`id_classific_fiscal` AND i.`id_uf` = '1' 
                    WHERE pi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            $campos_pis = bancos::sql($sql);
/************************Tratamento com o IPI************************/
            if($campos_cfop[0]['ipi'] == 1) {//Tributa - Busca da Classificação Fiscal ...
                $ipi = $campos_pis[0]['ipi'];
            }else if($campos_cfop[0]['ipi'] == 2 || $campos_cfop[0]['ipi'] == 3) {//Isento ou Dig. Manualmente ...
                $ipi = 0;
            }
/************************Tratamento com o ICMS************************/
            if($campos_cfop[0]['icms'] == 1) {//Tributa - Busca da Classificação Fiscal ...
                $icms = $campos_pis[0]['icms'];
                $reducao = $campos_pis[0]['reducao'];
            }else if($campos_cfop[0]['icms'] == 2 || $campos_cfop[0]['icms'] == 3) {//Isento ou Dig. Manualmente
                $icms = 0;
                $reducao = 0;
            }
            $id_unidade             = $campos_pis[0]['id_unidade'];
            $id_classific_fiscal    = $campos_pis[0]['id_classific_fiscal'];
            $observacao             = $campos_pis[0]['observacao'];
            
            $sql = "INSERT INTO `nfs_outras_itens` (`id_nf_outra_item`, `id_nf_outra`, `id_produto_insumo`, `id_unidade`, `referencia`, `id_classific_fiscal`, `origem_mercadoria`, `situacao_tributaria`, `qtde`, `valor_unitario`, `peso_unitario`, `ipi`, `icms`, `reducao`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_nf_outra]', '$id_produto_insumo', '$id_unidade', '".$campos_pis[0]['id_grupo']."', '$id_classific_fiscal', '0', '00', '".$_POST['txt_qtde'][$i]."', '".$_POST['txt_valor_unitario'][$i]."', '".$_POST['txt_peso_unitario'][$i]."', '$ipi', '$icms', '$reducao', '$observacao', '$data_sys') ";
            bancos::sql($sql);
            $cont_itens_aceitos++;
        }else {
            $cont_itens_ignorados++;
        }
    }
    $sql = "UPDATE `nfs_outras` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '$data_sys' WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
    bancos::sql($sql);

//Significa que foram inclusos todos os itens do Pedido perfeitamente na Nota Fiscal
    if($cont_itens_aceitos != 0 && $cont_itens_ignorados == 0) $valor = 2;
//Significa que nenhum dos itens do Pedido podem ter sido Importado
    if($cont_itens_aceitos == 0 && $cont_itens_ignorados != 0) $valor = 3;
//Nem todos os item(ns) podem ter sido importado(s)
    if($cont_itens_aceitos != 0 && $cont_itens_ignorados != 0) $valor = 4;
/**********************************************************************************/
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_pi.php?id_nf_outra=<?=$_POST['id_nf_outra'];?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
//Busca de alguns dados de NF p/ poder incluir os Itens ...
    $sql = "SELECT `id_cfop` 
            FROM `nfs_outras` 
            WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $id_cfop    = $campos[0]['id_cfop'];
?>
<html>
<head>
<title>.:: Incluir Item(ns) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        opener.parent.itens.document.form.submit()
        opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onload='document.form.txt_referencia.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='id_nf_outra' value='<?=$_GET['id_nf_outra'];?>'>
<input type='hidden' name='id_cfop' value='<?=$id_cfop;?>'>
<input type='hidden' name='passo' value='1'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Item(ns)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' size='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>'" class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick='document.form.nao_atualizar.value = 1' class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_referencia.focus()' style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color='darkgreen'><b>
CFOP(s) definidas no Cabeçalho da NF: 
</b></font>
<?
    $sql = "SELECT `id_cfop_revenda`, CONCAT(`cfop`, '.', `num_cfop`) AS cfop_industrial, CONCAT(`cfop`, '.', `num_cfop`, ' - ', `natureza_operacao_resumida`) AS cfop_industrial_descritivo 
            FROM `cfops` 
            WHERE `id_cfop` = '$id_cfop' LIMIT 1 ";
    $campos_cfop = bancos::sql($sql);
    echo '<b>CFOP 1: </b>'.$campos_cfop[0]['cfop_industrial_descritivo'];

    if($campos_cfop[0]['id_cfop_revenda'] != 0) {
        $sql = "SELECT CONCAT(`cfop`, '.', `num_cfop`, ' - ', `natureza_operacao_resumida`) AS cfop_revenda_descritivo 
                FROM `cfops` 
                WHERE `id_cfop` = ".$campos_cfop[0]['id_cfop_revenda']." 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_cfop_revenda = bancos::sql($sql);
        echo '<br><b>CFOP 2: </b>'.$campos_cfop_revenda[0]['cfop_revenda_descritivo'];
    }
?>
<br>
<font color='red'><b>Observação:</b></font>

Só exibe Pedido(s) do mesmo Tipo de Nota que foi selecionado em NF

NF - Consumo => Ped - Consumo
NF - Revenda => Ped - Revenda
NF - SGD => Ped - SGD
NF - NF  => Ped - NF
</pre>