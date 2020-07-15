<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/apv/apv.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $sql = "SELECT c.`id_cliente`, c.`razaosocial`, c.`cnpj_cpf`, c.`endereco`, c.`num_complemento`, c.`bairro`, c.`cep`, c.`email`, 
            c.`cidade`, u.`sigla` 
            FROM `clientes` c 
            INNER JOIN `ufs` u ON u.`id_uf` = c.`id_uf` 
            WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
            AND c.`razaosocial` LIKE '%$txt_razao_social%' 
            AND c.`ativo` = '1' ORDER BY c.`razaosocial` ";
    $campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'consultar_cliente.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Clientes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function transportar_dados(id_cliente, razaosocial, endereco, num_complemento, cep, email, bairro, cidade, estado) {
    opener.document.form.hdd_cliente_solicitante.value 	= id_cliente
    opener.document.form.txt_razao_social.value 	= razaosocial
    opener.document.form.txt_endereco.value 		= endereco
    opener.document.form.txt_num_complemento.value 	= num_complemento
    opener.document.form.txt_cep.value                  = cep
    opener.document.form.txt_email.value                = email
    opener.document.form.txt_bairro.value               = bairro
    opener.document.form.txt_cidade.value               = cidade
    opener.document.form.txt_estado.value               = estado
    window.close()
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar Cliente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Endereço
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = "javascript:transportar_dados('".$campos[$i]['id_cliente']."', '".$campos[$i]['razaosocial']."', '".$campos[$i]['endereco']."', '".$campos[$i]['num_complemento']."', '".$campos[$i]['cep']."', '".$campos[$i]['email']."', '".$campos[$i]['bairro']."', '".$campos[$i]['cidade']."', '".$campos[$i]['sigla']."') ";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="<?=$url;?>" width='10'>
            <a href="#" class='link'>
                <img src='../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="<?=$url;?>">
            <a href="#" class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td>
        <?
            echo $campos[$i]['endereco'];
            if(!empty($campos[$i]['endereco'])) echo ', '.$campos[$i]['num_complemento'];
        ?>
        </td>
        <td align='center'>
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
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_cliente.php'" class='botao'>
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
}else {
?>
<html>
<head>
<title>.:: Consultar Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_nome_fantasia.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cliente(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome Fantasia
        </td>
        <td>
            <input type='text' name='txt_nome_fantasia' title='Digite a Nome Fantasia' class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Razão Social
        </td>
        <td>
            <input type='text' name='txt_razao_social' title='Digite a Razão Social' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_nome_fantasia.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>