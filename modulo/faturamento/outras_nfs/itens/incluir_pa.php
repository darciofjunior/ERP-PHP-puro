<?
require('../../../../lib/segurancas.php');
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ITEM(NS) INCLUIDO(S) COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ITEM(NS) JÁ EXISTENTE.</font>";
$mensagem[4] = "<font class='atencao'>NEM TODOS OS ITEM(NS) PODEM SER IMPORTADO(S), POIS EXISTE(M) ITEM(NS) QUE JÁ FORAM IMPORTADO(S) ANTERIORMENTE.</font>";

if($passo == 1) {
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $chkt_mostrar_especiais = $_POST['chkt_mostrar_especiais'];
        $txt_referencia         = $_POST['txt_referencia'];
        $txt_discriminacao      = $_POST['txt_discriminacao'];
        $id_nf_outra            = $_POST['id_nf_outra'];
        $id_cfop                = $_POST['id_cfop'];
    }else {
        $chkt_mostrar_especiais = $_GET['chkt_mostrar_especiais'];
        $txt_referencia         = $_GET['txt_referencia'];
        $txt_discriminacao      = $_GET['txt_discriminacao'];
        $id_nf_outra            = $_GET['id_nf_outra'];
        $id_cfop                = $_GET['id_cfop'];
    }
/*Aqui eu tenho esse Tratamento devido com o % e |, devido o usuário utilizar o % 
como caracter ...*/
    $txt_discriminacao = str_replace('|', '%', $txt_discriminacao);
//Se essa opção estiver desmarcada, então eu só mostro os P.A(s) que são do Tipo normais de Linha ...
    if(empty($chkt_mostrar_especiais)) $condicao_esp = " AND pa.`referencia` <> 'ESP' ";

    $sql = "SELECT ged.`id_empresa_divisao` AS id_empresa_divisao_produto, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
            pa.`operacao_custo` 
            FROM `produtos_acabados` pa 
            INNER JOIN `estoques_acabados` ea ON ea.`id_produto_acabado` = pa.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`referencia` LIKE '%$txt_referencia%' 
            AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
            AND gpa.`id_familia` <> '23' 
            AND pa.`ativo` = '1' 
            $condicao_esp ORDER BY pa.`referencia` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        var nao_perguntar_novamente = eval('<?=$nao_perguntar_novamente;?>')
/*Significa que já foi feita uma pergunta referente ao Filtro anteriormente e sendo assim
só irá redirecionar p/ a Tela de Filtro novamente ...*/
        if(nao_perguntar_novamente == 1) {
            window.location = 'incluir_pa.php?id_nf_outra=<?=$id_nf_outra;?>&valor=1'
        }else {
/*Se não foi encontrado nenhum P.A. pelo filtro normal, então o Sistema pergunta p/ o usuário 
se ele deseja visualizar os ESP(s) de acordo com o Filtro que ele fez ...*/
            var resposta = confirm('DESEJA CONSULTAR OS ESPECIAIS ?')
            if(resposta == true) {//Irá manter o Filtro do Usuário, acrescentando apenas a opção de Especiais ...
                <?
/*Aqui eu tenho esse Tratamento devido com o % e |, devido o usuário utilizar o % 
como caracter ...*/
                    $txt_discriminacao = str_replace('%', '|', $txt_discriminacao);
                ?>
                window.location = 'incluir_pa.php?passo=1&txt_referencia=<?=$txt_referencia;?>&txt_discriminacao=<?=$txt_discriminacao;?>&id_nf_outra=<?=$id_nf_outra;?>&chkt_mostrar_especiais=1&nao_perguntar_novamente=1'
            }else {
                window.location = 'incluir_pa.php?id_nf_outra=<?=$id_nf_outra;?>&valor=1'
            }
        }
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
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_incluir_pas.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    var cont_checkbox_selecionados = 0
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) {
                valor = true
                cont_checkbox_selecionados++
            }
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
    
    if(typeof(elementos['chkt_produto_acabado[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_produto_acabado[]'].length)
    }

//A variável objetos_fim, representa os elementos que estão fora do Loop ...
    for(var i = 0; i < linhas; i++) {
//Força o Preenchimento do Campo Quantidade ...
        if(document.getElementById('chkt_produto_acabado'+i).checked == true) {
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
        }
    }
//Prepara no formato moeda antes de submeter para o BD ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_produto_acabado'+i).checked == true) {
            document.getElementById('txt_qtde'+i).value             = strtofloat(document.getElementById('txt_qtde'+i).value)
            document.getElementById('txt_valor_unitario'+i).value   = strtofloat(document.getElementById('txt_valor_unitario'+i).value)
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Incluir Item(ns)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Produto
        </td>
        <td>
            Qtde
        </td>
        <td>
            Valor Unitário R$
        </td>
    </tr>
<?
/************************************************************/
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_produto_acabado[]' id='chkt_produto_acabado<?=$i;?>' value="<?=$campos[$i]['id_produto_acabado'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0);?>
        </td>
        <td>
            <input type='text' name='txt_qtde[]' id='txt_qtde<?=$i;?>' title='Digite a Quantidade' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='9' size='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_valor_unitario[]' id='txt_valor_unitario<?=$i;?>' title='Digite o Valor Unitário' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='9' size='10' class='textdisabled' disabled>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir_pa.php?id_nf_outra=<?=$id_nf_outra;?>'" class='botao'>
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
/*************************************************************************************/
//Busca de alguns dados do $id_nf_outra passado por parâmetro ...
    $sql = "SELECT nfso.`id_cliente`, nfso.`id_empresa`, nfso.`finalidade`, ufs.`id_uf` 
            FROM `nfs_outras` nfso 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
            LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
            WHERE nfso.`id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    $campos_dados   = bancos::sql($sql);
/*************************************************************************************/
    for($i = 0; $i < count($_POST['chkt_produto_acabado']); $i++) {
//Observação: O mesmo PA, pode ser inserido mais de uma vez na mesma NF ...
//Busca de alguns Dados do PA ...
        $sql = "SELECT `id_unidade`, `origem_mercadoria`, `referencia`, `peso_unitario`, `observacao` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '".$_POST['chkt_produto_acabado'][$i]."' LIMIT 1 ";
        $campos_pas             = bancos::sql($sql);
        $id_unidade             = $campos_pas[0]['id_unidade'];
        $origem_mercadoria      = $campos_pas[0]['origem_mercadoria'];
        $referencia             = $campos_pas[0]['referencia'];
        $peso_unitario          = $campos_pas[0]['peso_unitario'];
        $observacao             = $campos_pas[0]['observacao'];
        
        $dados_produto          = intermodular::dados_impostos_pa($_POST['chkt_produto_acabado'][$i], $campos_dados[0]['id_uf'], $campos_dados[0]['id_cliente'], $campos_dados[0]['id_empresa'], $campos_dados[0]['finalidade']);
        $id_classific_fiscal    = $dados_produto['id_classific_fiscal'];
        $ipi                    = $dados_produto['ipi'];
        $icms                   = $dados_produto['icms'];
        $icms_intraestadual     = $dados_produto['icms_intraestadual'];
        $reducao                = $dados_produto['reducao'];
        $iva                    = $dados_produto['iva'];
        $situacao_tributaria    = $dados_produto['situacao_tributaria'];
        
        $sql = "INSERT INTO `nfs_outras_itens` (`id_nf_outra_item`, `id_nf_outra`, `id_produto_acabado`, `id_unidade`, `referencia`, `id_classific_fiscal`, `origem_mercadoria`, `situacao_tributaria`, `qtde`, `valor_unitario`, `peso_unitario`, `ipi`, `icms`, `reducao`, `icms_intraestadual`, `iva`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_nf_outra]', '".$_POST['chkt_produto_acabado'][$i]."', '$id_unidade', '$referencia', '$id_classific_fiscal', '$origem_mercadoria', '$situacao_tributaria', '".$_POST['txt_qtde'][$i]."', '".$_POST['txt_valor_unitario'][$i]."', '$peso_unitario', '$ipi', '$icms', '$reducao', '$icms_intraestadual', '$iva', '$observacao', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
    $sql = "UPDATE `nfs_outras` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    bancos::sql($sql);
/**********************************************************************************/
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_pa.php?id_nf_outra=<?=$_POST['id_nf_outra'];?>&valor=2'
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
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
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
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1'>
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
    <tr class='linhanormal'>
        <td>
        </td>
        <td>
            <input type='checkbox' name='chkt_mostrar_especiais' value='1' title='Mostrar Especiais' class='checkbox' id='label1'>
            <label for='label1'>
                Mostrar Especiais
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="document.form.nao_atualizar.value = 1;window.location = 'incluir.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>'" class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick='document.form.nao_atualizar.value=1' class='botao'>
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

NF - Consumo            => Ped - Consumo
NF - Industrialização   => Ped - Industrialização
NF - Revenda            => Ped - Revenda
NF - SGD                => Ped - SGD
NF - NF                 => Ped - NF
</pre>