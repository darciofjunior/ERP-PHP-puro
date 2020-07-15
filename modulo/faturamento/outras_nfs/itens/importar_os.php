<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_os         = $_POST['txt_os'];
        $id_nf_outra 	= $_POST['id_nf_outra'];
        $id_cfop        = $_POST['id_cfop'];
    }else {
        $txt_os         = $_GET['txt_os'];
        $id_nf_outra 	= $_GET['id_nf_outra'];
        $id_cfop        = $_GET['id_cfop'];
    }
//Busca da Empresa da NF e do "CNPJ ou CPF" do Cliente da NF, que serão utilizados logo abaixo no SQL da OS ...
    $sql = "SELECT nfso.`id_empresa`, c.`cnpj_cpf` 
            FROM `nfs_outras` nfso 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
            WHERE nfso.`id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
    $campos_nfso 	= bancos::sql($sql);
    $id_empresa_nf 	= $campos_nfso[0]['id_empresa'];
    $cnpj_cpf           = $campos_nfso[0]['cnpj_cpf'];

/*Listagem de todas as OS(s) que possuem:
 * 
 * Mesma empresa selecionada no Cabeçalho da NF Outra, ou seja NF da Albafer, OSs apenas da Albafer ...
 * Pelo menos 1 item;
 * Que ainda estão em aberto;
 * Somente do Tipo NF (que não possuem nenhum N.º no campo NNF da OS), ou seja em q este campo se encontra vazio;
 * Que não estão atreladas a nenhuma NF Outra;
 * Em que o Fornecedor seja o mesmo Cliente, uso o CNPJ p/ fazer essa consistência ...*/
    $sql = "SELECT DISTINCT(oss.`id_os`), f.`razaosocial`, DATE_FORMAT(oss.`data_saida`, '%d/%m/%Y') AS data_saida, oss.`observacao` 
            FROM `oss` 
            INNER JOIN `oss_itens` ossi ON ossi.`id_os` = oss.`id_os` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` AND f.`cnpj_cpf` = '$cnpj_cpf' 
            WHERE oss.`id_empresa` = '$id_empresa_nf' 
            AND oss.`id_os` LIKE '%$txt_os%' 
            AND oss.`id_nf_outra` IS NULL 
            AND oss.`nnf` = '' 
            AND oss.`ativo` = '1' 
            AND oss.`status_nf` < '2' 
            ORDER BY oss.`id_os` DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas  == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'importar_os.php?id_nf_outra=<?=$id_nf_outra;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Importar OS(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_os) {
    resposta = confirm('DESEJA IMPORTAR ESSA O.S. P/ ESTA NOTA FISCAL ?')
    if(resposta == true) {
        window.location = 'importar_os.php?passo=2&id_nf_outra=<?=$id_nf_outra;?>&id_cfop=<?=$id_cfop;?>&id_os='+id_os
    }
}
</Script>
</head>
<body>
<form name='form' method='post'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Importar OS(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º OS
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Data de Saída
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
/************************************************************/
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_os'];?>')" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_os'];?>')" class='link'>
                <?=$campos[$i]['id_os'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?if($campos[$i]['data_saida'] != '00/00/0000') echo $campos[$i]['data_saida'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'importar_os.php?id_nf_outra=<?=$id_nf_outra;?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
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
//Busca dos Impostos na Tabela de CFOP de acordo com a CFOP passado por parâmetro ...
    $sql = "SELECT `ipi`, `icms` 
            FROM `cfops` 
            WHERE `id_cfop` = '$_GET[id_cfop]' LIMIT 1 ";
    $campos_cfop = bancos::sql($sql);
/*************************************************************************************/
/***********************************Parte de Itens************************************/
/*************************************************************************************/
/*Busca dos Itens de OS de acordo com o "id_os" passado por parâmetro e na ordem que foram inseridos 
os mesmos para que se insira aqui na NF na mesma ordem para melhores conferências ...*/
    $sql = "SELECT * 
            FROM `oss_itens` 
            WHERE `id_os` = '$_GET[id_os]' ORDER BY `id_os_item` ";
    $campos_itens   = bancos::sql($sql);
    $linhas_itens   = count($campos_itens);
    for($i = 0; $i < $linhas_itens; $i++) {
//Busca de alguns Dados do PI ...
        $sql = "SELECT cf.`id_classific_fiscal`, cf.`ipi`, CONCAT(u.`sigla`, ' * ', ctts.`aplicacao_usual`, ' ', ctts.`descricao`, ' / ', ctts.`codigo`) AS discriminacao, 
                g.`referencia`, i.`icms`, i.`reducao`, pi.`id_unidade`, pi.`observacao` 
                FROM `produtos_insumos` pi 
                INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                LEFT JOIN `ctts` ON ctts.`id_ctt` = pi.`id_ctt` 
                INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = pi.`id_classific_fiscal` 
                INNER JOIN `icms` i ON i.`id_classific_fiscal` = cf.`id_classific_fiscal` AND i.`id_uf` = '1' 
                WHERE pi.`id_produto_insumo` = '".$campos_itens[$i]['id_produto_insumo_ctt']."' LIMIT 1 ";
        $campos_pis             = bancos::sql($sql);
        $id_classific_fiscal    = $campos_pis[0]['id_classific_fiscal'];
/************************Tratamento com o IPI************************/
        if($campos_cfop[0]['ipi'] == 1) {//Tributa - Busca da Classificação Fiscal ...
            $ipi = $campos_pis[0]['ipi'];
        }else if($campos_cfop[0]['ipi'] == 2 || $campos_cfop[0]['ipi'] == 3) {//Isento ou Dig. Manualmente ...
            $ipi = 0;
        }
/************************Tratamento com o ICMS************************/
        if($campos_cfop[0]['icms'] == 1) {//Tributa - Busca da Classificação Fiscal ...
            $icms       = $campos_pis[0]['icms'];
            $reducao    = $campos_pis[0]['reducao'];
        }else if($campos_cfop[0]['icms'] == 2 || $campos_cfop[0]['icms'] == 3) {//Isento ou Dig. Manualmente
            $icms       = 0;
            $reducao    = 0;
        }
        $id_unidade 	= $campos_pis[0]['id_unidade'];
        if(empty($campos_pis[0]['discriminacao'])) {//Busca os dados do PI normal sem o CTT ...
            $sql = "SELECT g.`referencia`, pi.`discriminacao` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE pi.`id_produto_insumo` = '".$campos_itens[$i]['id_produto_insumo_ctt']."' LIMIT 1 ";
            $campos_pi      = bancos::sql($sql);
            $referencia     = $campos_pi[0]['referencia'];
            $discriminacao  = $campos_pi[0]['discriminacao'];
        }else {
            $referencia     = $campos_pis[0]['referencia'];
            $discriminacao  = $campos_pis[0]['discriminacao'].' (OP '.$campos_itens[$i]['id_op'].')';
        }
        $peso_unitario 	= $campos_pis[0]['peso_unitario'];
        $observacao 	= $campos_pis[0]['observacao'];
        //Coloco 50 que é "Suspensão" na Situação Tributária, como sempre fizemos até hoje p/ esses Tipos de Notas Fiscais ...
//1)Insere os dados de Itens de OS na tabela de Itens de NFs Outras ...
        $sql = "INSERT INTO `nfs_outras_itens` (`id_nf_outra_item`, `id_nf_outra`, `id_unidade`, `referencia`, `discriminacao`, `id_classific_fiscal`, `situacao_tributaria`, `qtde`, `valor_unitario`, `peso_unitario`, `ipi`, `icms`, `reducao`, `observacao`, `data_sys`) VALUES (NULL, '$_GET[id_nf_outra]', '$id_unidade', '$referencia', '$discriminacao', '$id_classific_fiscal', '50', '".$campos_itens[$i]['peso_total_saida']."', '".$campos_itens[$i]['preco_pi']."', '0', '$ipi', '$icms', '$reducao', '$observacao', '$data_sys') ";
        bancos::sql($sql);
        $id_nf_outra_item = bancos::id_registro();
//2)Atrelo o id_nf_outra_item na tabela de 'oss_itens' da OS que está sendo importada ...
        $sql = "UPDATE `oss_itens` SET `id_nf_outra_item` = '$id_nf_outra_item' WHERE `id_os_item` = '".$campos_itens[$i]['id_os_item']."' LIMIT 1 ";
        bancos::sql($sql);
    }
/*************************************************************************************/
/*********************************Parte de Cabeçalho**********************************/
/*************************************************************************************/
//Busca de alguns dados de cabeçalho da OS p/ atualizar os mesmos no cabeçalho da NF ...
    $sql = "SELECT `qtde_caixas`, `peso_liq`, (`peso_caixas` + `peso_liq`) AS peso_bruto 
            FROM `oss` 
            WHERE `id_os` = '$_GET[id_os]' LIMIT 1 ";
    $campos_os      = bancos::sql($sql);
    $qtde_caixas    = $campos_os[0]['qtde_caixas'];
    $peso_liq       = $campos_os[0]['peso_liq'];
    $peso_bruto     = $campos_os[0]['peso_bruto'];

//Cabeçalho da NF ...
    $sql = "UPDATE `nfs_outras` SET `id_funcionario` = '$_SESSION[id_funcionario]', `qtde_volume` = '$qtde_caixas', `especie_volume` = 'CAIXA(S)', `peso_bruto_volume` = '$peso_bruto', `peso_liquido_volume` = '$peso_liq', `data_sys` = '$data_sys' WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
    bancos::sql($sql);

//Cabeçalho da OS ...
//Obs: Já atrelo o id_nf_outra na OS que está sendo importada ...
    $sql = "UPDATE `oss` SET `id_nf_outra` = '$_GET[id_nf_outra]' WHERE `id_os` = '$_GET[id_os]' LIMIT 1 ";
    bancos::sql($sql);
/***********************************************************************************************/
?>
    <Script Language = 'JavaScript'>
        alert('OS IMPORTADA COM SUCESSO !')
//Recarregando as Telas de Baixo ...
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
        window.close()
    </Script>
<?
}else {
//Busca de alguns dados de NF p/ poder Importar OS ...
    $sql = "SELECT `id_cfop` 
            FROM `nfs_outras` 
            WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $id_cfop    = $campos[0]['id_cfop'];
?>
<html>
<head>
<title>.:: Importar OS(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
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
<body onload='document.form.txt_os.focus()' onunload='atualizar_abaixo()'>
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
            Importar OS(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º OS
        </td>
        <td>
            <input type='text' name='txt_os' title='Digite o N.º da OS' size='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="document.form.nao_atualizar.value = 1;window.location = 'incluir.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>'" class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick='document.form.nao_atualizar.value = 1' class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_os.focus()' style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color="darkgreen"><b>
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