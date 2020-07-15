<?
require('../../../lib/segurancas.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

//Se retornar pelo menos 1 registro
if($passo == 1) {
    $sql = "SELECT `id_pais`, `id_uf`, `nomefantasia`, `razaosocial`, `insc_estadual`, `cnpj_cpf`, 
            `rg`, `orgao`, `ddi_com`, `ddd_com`, `telcom`, `ddi_fax`, `ddd_fax`, `telfax`, `endereco`, `num_complemento`, 
            `bairro`, `cep`, `cidade`, `email`, `pagweb` 
            FROM `clientes` 
            WHERE `nomefantasia` LIKE '%$txt_nome_fantasia%' 
            AND `razaosocial` LIKE '%$txt_razao_social%' 
            AND `ativo` = '1' 
            ORDER BY `razaosocial` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'clonar_cliente.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Clonar Clientes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function transportar_dados(id_pais, nome_fantasia, razaosocial, insc_estadual, cnpj_cpf, rg, orgao, endereco, num_complemento, cep, bairro, cidade, estado, ddd_com, tel_com, ddd_fax, tel_fax, email, pagina_web) {
    parent.document.form.txt_cnpj_cpf.value = cnpj_cpf
    setTimeout('consultar_fornecedor("'+id_pais+'","'+nome_fantasia+'","'+razaosocial+'","'+insc_estadual+'","'+rg+'","'+orgao+'","'+endereco+'","'+num_complemento+'","'+cep+'","'+bairro+'","'+cidade+'","'+estado+'","'+ddd_com+'","'+tel_com+'","'+ddd_fax+'","'+tel_fax+'","'+email+'","'+pagina_web+'")', 200)
}

function consultar_fornecedor(id_pais, nome_fantasia, razaosocial, insc_estadual, rg, orgao, endereco, num_complemento, cep, bairro, cidade, estado, ddd_com, tel_com, ddd_fax, tel_fax, email, pagina_web) {//Essa função verifica se a DIV retorno algum Fornecedor através do AJAX ...
    msn_div = parent.document.getElementById('incluir_dados_basicos').innerHTML
    if(msn_div.indexOf('FORNECEDOR') == -1) {//Significa que não encontrou o Fornecedor no BD, então eu completo os campos abaixo ...
        parent.document.form.cmb_pais.value             = id_pais
        parent.document.form.txt_nome_fantasia.value 	= nome_fantasia
        parent.document.form.txt_razao_social.value 	= razaosocial
        parent.document.form.hdd_insc_estadual.value 	= insc_estadual
        parent.document.form.hdd_rg.value               = rg
        parent.document.form.hdd_orgao.value            = orgao
        parent.document.form.txt_endereco.value         = endereco
        parent.document.form.txt_num_complemento.value 	= num_complemento
        parent.document.form.txt_cep.value              = cep
        parent.document.form.txt_bairro.value           = bairro
        parent.document.form.txt_cidade.value           = cidade
        parent.document.form.txt_estado.value           = estado
        parent.document.form.txt_ddd_comercial.value 	= ddd_com
        parent.document.form.txt_tel_comercial.value 	= tel_com
        parent.document.form.txt_ddd_fax.value          = ddd_fax
        parent.document.form.txt_tel_fax.value          = tel_fax
        parent.document.form.txt_email.value            = email
        parent.document.form.txt_pagina_web.value       = pagina_web
    }
    parent.html5Lightbox.finish()
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Clonar Cliente(s)
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
            //Busca de Sigla da UF ...
            $sql = "SELECT `sigla` 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
            $campos_uf 	= bancos::sql($sql);
            $estado 	= $campos_uf[0]['sigla'];
            //Função ...
            $url = "javascript:transportar_dados('".$campos[$i]['id_pais']."', '".$campos[$i]['nomefantasia']."', '".$campos[$i]['razaosocial']."', '".$campos[$i]['insc_estadual']."', '".$campos[$i]['cnpj_cpf']."', '".$campos[$i]['rg']."', '".$campos[$i]['orgao']."', '".$campos[$i]['endereco']."', '".$campos[$i]['num_complemento']."', '".$campos[$i]['cep']."', '".$campos[$i]['bairro']."', '".$campos[$i]['cidade']."', '".$estado."', '".$campos[$i]['ddd_com']."', '".$campos[$i]['telcom']."', '".$campos[$i]['ddd_fax']."', '".$campos[$i]['telfax']."', '".$campos[$i]['email']."', '".$campos[$i]['pagweb']."') ";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$url;?>">
            <a href="<?=$url;?>" class='link'>
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
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'clonar_cliente.php'" class='botao'>
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
<title>.:: Clonar Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_nome_fantasia.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Clonar Cliente(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome Fantasia
        </td>
        <td>
            <input type='text' name='txt_nome_fantasia' title='Digite a Nome Fantasia' class='caixadetexto'>
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
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick='document.form.txt_nome_fantasia.focus()' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>