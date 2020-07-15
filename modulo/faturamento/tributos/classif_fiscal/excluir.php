<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CLASSIFICAÇÃO FISCAL EXCLUIDA COM SUCESSO.</font>";

if($passo == 1) {
    $chkt_pa_comercializado_pelo_grupo = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['chkt_pa_comercializado_pelo_grupo'] : $_GET['chkt_pa_comercializado_pelo_grupo'];
    if(!empty($chkt_pa_comercializado_pelo_grupo)) $condicao = " AND pa_comercializado_pelo_grupo = 'S' ";

    switch($opt_opcao) {
        case 1:
            $sql = "SELECT cf.*, u.`sigla` 
                    FROM `classific_fiscais` cf 
                    LEFT JOIN `unidades` u ON u.`id_unidade` = cf.`id_unidade` 
                    WHERE cf.`classific_fiscal` LIKE '$txt_consultar%' 
                    AND cf.`ativo` = '1' 
                    $condicao ORDER BY cf.`classific_fiscal` ";
        break;
        default:
            $sql = "SELECT cf.*, u.`sigla` 
                    FROM `classific_fiscais` cf 
                    LEFT JOIN `unidades` u ON u.`id_unidade` = cf.`id_unidade` 
                    WHERE cf.`ativo` = '1' 
                    $condicao ORDER BY cf.`classific_fiscal` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'excluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Classificação Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Excluir Classificação(ões) Fiscal(is)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Id
        </td>
        <td>
            Classificação <br/>Fiscal
        </td>
        <td>
            Unidade
        </td>
        <td>
            CEST
        </td>
        <td>
            IPI
        </td>
        <td>
            <font title='Imposto de Importação' style='cursor:help'>
                II
            </font>
        </td>
        <td>
            Texto da Nota
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['id_classific_fiscal'];?>
        </td>
        <td>
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td>
            <?=$campos[$i]['cest'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['ipi'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['imposto_importacao'], 2, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['reducao_governo'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_classific_fiscal[]' value='<?=$campos[$i]['id_classific_fiscal'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'excluir.php'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
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
    foreach ($_POST['chkt_classific_fiscal'] as $id_classific_fiscal) {
        $sql = "UPDATE `classific_fiscais` SET `ativo` = '0' WHERE `id_classific_fiscal` = '$id_classific_fiscal' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'excluir.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Classificação Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value   = ''
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
<body onLoad='document.form.txt_consultar.focus()'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir Classificação Fiscal
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar classificações fiscais por: Classificação Fiscal' id='label1' onclick='iniciar()' checked>
            <label for='label1'>Classificação Fiscal</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='chkt_pa_comercializado_pelo_grupo' value='S' id='pa_comercializado_pelo_grupo' class='checkbox' checked>
            <label for='pa_comercializado_pelo_grupo'>
                <font color='red'>
                    <b>PA(s) comercializado(s) pelo Grupo</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todas as classificações fiscais' onclick='limpar()' id='todos' class='checkbox'>
            <label for='todos'>Todos os registros</label>
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
