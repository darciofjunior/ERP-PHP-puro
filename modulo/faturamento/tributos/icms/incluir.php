<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `classific_fiscais` 
                    WHERE `classific_fiscal` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY `classific_fiscal` ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `classific_fiscais` 
                    WHERE `ativo` = '1' ORDER BY `classific_fiscal` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Incluir ICMS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan="4">
            Consultar Classificação(ões) Fiscal(is)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan="2">
            Classificação Fiscal
        </td>
        <td>
            IPI
        </td>
        <td>
            Qtde de Ufs não atreladas
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
            //Total de Estados ...
            $sql = "SELECT COUNT(`id_uf`) AS total_ufs 
                    FROM `ufs` 
                    WHERE `ativo` = '1' ";
            $campos_total_ufs   = bancos::sql($sql);
            $total_ufs          = $campos_total_ufs[0]['total_ufs'];
            //Total de Icms ...
            $sql = "SELECT COUNT(`id_icms`) AS total_icms 
                    FROM `icms` 
                    WHERE `id_classific_fiscal` = ".$campos[$i]['id_classific_fiscal']." 
                    AND `ativo` = '1' ";
            $campos_total_icms  = bancos::sql($sql);
            $total_icms         = $campos_total_icms[0]['total_icms'];
            //Diferença
            $diferenca = $total_ufs - $total_icms;

            if($diferenca == 0) {//Significa q já cadastrou todas as classificações
                $comando = '';
            }else {//Ainda falta para cadastrar
                $comando = "javascript:window.location = 'incluir_icms.php?id_classific_fiscal=".$campos[$i]['id_classific_fiscal']."'";
            }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$comando;?>" width='10'>
        <?
            if($diferenca == 0) {
        ?>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        <?
            }else {
        ?>
                <a href="<?=$comando;?>'" class='link'>
                    <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                </a>
        <?
            }
        ?>
        </td>
        <td onclick="<?=$comando;?>">
        <?
            if($diferenca == 0) {
                echo $campos[$i]['classific_fiscal'];
            }else {
    ?>
                <a href="<?=$comando;?>" class='link'>
                    <?=$campos[$i]['classific_fiscal'];?>
                </a>
    <?
            }
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['ipi'], 2, ',', '.');?>
        </td>
        <td>
            <?=$diferenca;?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
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
<title>.:: Incluir ICMS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir ICMS
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='opt_opcao' value='1' title='Consultar Classificações Fiscais por: Classificação Fiscal' onclick='document.form.txt_consultar.focus()' checked>
            <label for='opt_opcao'>Classificação Fiscal</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' id='opcao' value='1' title='Consultar todas as Classificações Fiscais' onclick='limpar()' class='checkbox'>
            <label for='opcao'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>