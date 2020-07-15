<?
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='erro'>JÁ FOI GERADA UMA CARTA DE CORREÇÃO P/ ESTA NF.</font>";

if($passo == 1) {
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_consultar 	= $_POST['txt_consultar'];
        $cmb_empresa 	= $_POST['cmb_empresa'];
    }else {
        $txt_consultar  = $_GET['txt_consultar'];
        $cmb_empresa    = $_GET['cmb_empresa'];
    }
    if(empty($cmb_empresa)) $cmb_empresa = '%';
    switch($opt_opcao) {
        case 'NNF'://Se for Nossa Nota Fiscal - Vendas ...
/**************************************NFs Saída/Devolução*****************************************/
//Essa Tabela de NFs está totalmente relacionada com as NFs de Saída que vem de Pedidos, Orçamentos ...
            $sql = "(SELECT DISTINCT(nfs.id_nf) 
                    FROM `nfs` 
                    INNER JOIN `nfs_num_notas` nnn ON nnn.id_nf_num_nota = nfs.id_nf_num_nota AND nnn.numero_nf LIKE '$txt_consultar%' 
                    WHERE nfs.`id_empresa` LIKE '$cmb_empresa') 
                    UNION 
                    (SELECT DISTINCT(nfs.id_nf) 
                    FROM `nfs` 
                    WHERE nfs.`snf_devolvida` LIKE '$txt_consultar%' 
                    AND nfs.`id_empresa` LIKE '$cmb_empresa') ORDER BY id_nf DESC ";
            $campos_nfs = bancos::sql($sql);
            $linhas_nfs = count($campos_nfs);
            for($l = 0; $l < $linhas_nfs; $l++) $id_nfs[] = $campos_nfs[$l]['id_nf'];
            //Arranjo Ténico
            if(count($id_nfs) == 0) {$id_nfs[]='0';}
            $vetor_nfs = implode(',', $id_nfs);
            $condicao_nfs = " AND nfs.`id_nf` IN ($vetor_nfs) ";
/********************************************NFs Outras********************************************/
//Essa Tabela de NFs Outras só está relacionada com as NFs de Saída ...
            $sql = "SELECT DISTINCT(nfso.id_nf_outra) 
                    FROM `nfs_outras` nfso 
                    INNER JOIN `nfs_num_notas` nnn ON nnn.id_nf_num_nota = nfso.id_nf_num_nota AND nnn.numero_nf LIKE '$txt_consultar%' 
                    WHERE nfso.`id_empresa` LIKE '$cmb_empresa' ORDER BY nfso.id_nf_outra DESC ";
            $campos_nfs_outras = bancos::sql($sql);
            $linhas_nfs_outras = count($campos_nfs_outras);
            for($l = 0; $l < $linhas_nfs_outras; $l++) {$id_nfs_outras[] = $campos_nfs_outras[$l]['id_nf_outra'];}
            //Arranjo Ténico
            if(count($id_nfs_outras) == 0) $id_nfs_outras[]='0';
            $vetor_nfs_outras = implode(',', $id_nfs_outras);
            $condicao_nfs_outras = " AND nfso.`id_nf_outra` IN ($vetor_nfs_outras) ";
/**************************************NFs Saída/Devolução e NFs Outras*************************************/
//NFs Saída / Devolução que equivale ao Status 6 - nfs ...
//NFs Outras - nfs_outras ...
            $sql = "(SELECT nfs.id_nf, nfs.id_empresa, nfs.id_nf_num_nota, nfs.data_emissao, nfs.vencimento1, nfs.vencimento2, nfs.vencimento3, nfs.vencimento4, nfs.status, nfs.tipo_despacho, c.nomefantasia, c.razaosocial, c.credito, t.nome AS transportadora 
                    FROM `nfs` 
                    INNER JOIN `transportadoras` t ON t.id_transportadora = nfs.id_transportadora 
                    INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente AND c.ativo = '1' 
                    WHERE nfs.`id_empresa` LIKE '$cmb_empresa' 
                    AND nfs.`ativo` = '1' 
                    $condicao_nfs GROUP BY nfs.id_nf) 
                    UNION ALL 
                    (SELECT /*Esse Pipe é um Macete ...*/ concat('|', nfso.id_nf_outra), nfso.id_empresa, nfso.id_nf_num_nota, nfso.data_emissao, nfso.vencimento1, nfso.vencimento2, nfso.vencimento3, nfso.vencimento4, nfso.status, nfso.tipo_despacho, c.nomefantasia, c.razaosocial, c.credito, t.nome as transportadora 
                    FROM `nfs_outras` nfso 
                    INNER JOIN `transportadoras` t ON t.id_transportadora = nfso.id_transportadora 
                    INNER JOIN `clientes` c ON c.id_cliente = nfso.id_cliente 
                    WHERE nfso.`id_empresa` LIKE '$cmb_empresa' 
                    AND nfso.`ativo` = '1' 
                    $condicao_nfs_outras GROUP BY nfso.id_nf_outra) ORDER BY data_emissao DESC ";
            require('consultar_nnfs.php');
            exit;
        break;
        default://Se for Vossa Nota Fiscal - Compras ...
            $sql = "SELECT DISTINCT(id_nfe) 
                    FROM `nfe` 
                    WHERE `num_nota` LIKE '$txt_consultar%' 
                    AND `id_empresa` LIKE '$cmb_empresa' 
                    AND `ativo` = '1' GROUP BY id_nfe ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            for($l = 0; $l < $linhas; $l++) $id_nfes[] = $campos[$l]['id_nfe'];
            //Arranjo Ténico
            if(count($id_nfes) == 0) {$id_nfes[]='0';}
            $vetor_nfe = implode(',', $id_nfes);
            $condicao_nfe = " nfe.id_nfe IN ($vetor_nfe) ";

            $sql = "SELECT nfe.*, f.razaosocial, e.nomefantasia 
                    FROM `nfe` 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
                    INNER JOIN `empresas` e ON e.id_empresa = nfe.id_empresa 
                    WHERE $condicao_nfe 
                    AND nfe.`id_empresa` LIKE '$cmb_empresa' order by nfe.data_entrega DESC ";
            require('consultar_snfs.php');
            exit;
        break;
    }
//Nesse passo aqui é aonde eu gero a Carta de Correção ...
}else if($passo == 2) {
    if(!empty($id_nf)) {//NF de Vendas - Saída
        $condicao = " `id_nf` = '$_GET[id_nf]' ";
        $coluna_insert = "`id_nf`";
        $valor_insert = "'$_GET[id_nf]'";
    }else if(!empty($id_nf_outra)) {//NF de Outras - Saída
        $condicao = " `id_nf_outra` = '$_GET[id_nf_outra]' ";
        $coluna_insert = "`id_nf_outra`";
        $valor_insert = "'$_GET[id_nf_outra]'";
    }else {//NF de Compras - Entrada
        $condicao = " `id_nfe` = '$_GET[id_nfe]' ";
        $coluna_insert = "`id_nfe`";
        $valor_insert = "'$_GET[id_nfe]'";
    }
//Aqui eu verifico se já foi feita alguma Carta de Correção na NF passada por parâmetro ...
    $sql = "SELECT id_carta_correcao 
            FROM `cartas_correcoes` 
            WHERE $condicao LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $data_sys = date('Y-m-d H:i:s');
        $sql = "INSERT INTO `cartas_correcoes` (`id_carta_correcao`, $coluna_insert, `data_sys`) VALUES (NULL, $valor_insert, '$data_sys') ";
        bancos::sql($sql);
        $id_carta_correcao = bancos::id_registro();
?>
    <Script Language = 'JavaScript'>
        window.location = 'itens/consultar.php?passo=1&id_carta_correcao=<?=$id_carta_correcao;?>'
    </Script>
<?
//Significa que já foi feita uma Carta de Correção p/ esta NF ...
    }else {
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?valor=2'
    </Script>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Incluir Carta de Correção ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Consultar
	if(document.form.txt_consultar.value == '') {
		alert('DIGITE O CAMPO CONSULTAR !')
		document.form.txt_consultar.focus()
		return false
	}
}
</Script>
</head>
<body onload="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Carta de Correção
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="NNF" title="Consultar NF(s) de Vendas" onclick="document.form.txt_consultar.focus()" id="opt1" checked>
            <label for="opt1">NNF</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="SNF" title="Consultar NF(s) de Compras" onclick="document.form.txt_consultar.focus()" id="opt2">
            <label for="opt2">SNF</label>
        </td>
    </tr>
    <tr class="linhanormal" align="center">
        <td colspan="2">
            Empresa 
            <select name="cmb_empresa" title="Selecione a Empresa" class="combo">
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_consultar.focus()" style="color:#ff9900" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>