<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/cascates.php');
require('../../../../../lib/vendas.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>GRUPO P.A. EXCLUIDO COM SUCESSO.</font>";
$mensagem[3] = '<font class="erro">GRUPO P.A. NÃO PODE SER EXCLUÍDO, POIS CONSTA EM USO.</font>';

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT gpa.*, cf.classific_fiscal, f.nome as familia 
                    FROM `grupos_pas` gpa 
                    INNER JOIN `familias` f ON f.id_familia = gpa.id_familia 
                    INNER JOIN `classific_fiscais` cf ON cf.id_classific_fiscal = f.id_classific_fiscal 
                    WHERE gpa.`nome` LIKE '%$txt_consultar%' 
                    AND gpa.`ativo` = '1' ORDER BY gpa.nome ";
        break;
        default:
            $sql = "SELECT gpa.*, cf.classific_fiscal, f.nome as familia 
                    FROM `grupos_pas` gpa 
                    INNER JOIN `familias` f ON f.id_familia = gpa.id_familia 
                    INNER JOIN `classific_fiscais` cf ON cf.id_classific_fiscal = f.id_classific_fiscal 
                    WHERE gpa.`ativo` = '1' ORDER BY gpa.nome ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas  == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'excluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Grupo P.A. ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Excluir Grupo P.A.
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Grupo
        </td>
        <td>
            Família
        </td>
        <td>
            Lote Mín. Prod.
        </td>
        <td>
            Prazo de Entrega
        </td>
        <td>
            Tolerância
        </td>			
        <td>
            Classificação Fiscal
        </td>
        <td>
            Observação
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['familia'];?>
        </td>
        <td align="right">
            <?='R$ '.number_format($campos[$i]['lote_min_producao_reais'], 2, ',', '.');?>
        </td>
        <td>
        <?
            $vetor_prazos_entrega = vendas::prazos_entrega();
            foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
                //Compara o valor do Banco com o valor do Vetor
                if($campos[$i]['prazo_entrega'] == $indice) {//Se igual seleciona esse valor
                    echo $prazo_entrega;
                }
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['tolerancia'];?>
        </td>			
        <td>
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_grupo_pa[]' value="<?=$campos[$i]['id_grupo_pa'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
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
}elseif ($passo == 2) {
    foreach ($_POST['chkt_grupo_pa'] as $id_grupo_pa) {
        //Verifica se o Grupo PA esta em uso ...
        if(cascate::consultar('id_grupo_pa', 'gpas_vs_emps_divs', $id_grupo_pa)) {//Em uso
            $valor = 3;
        }else {//Não está em uso, pode apagar o Grupo P.A.
            $sql = "UPDATE `grupos_pas` SET `ativo` = '0' WHERE `id_grupo_pa` = '$id_grupo_pa' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 2;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'excluir.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Grupo P.A. ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
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
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir Grupo P.A.
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type="radio" name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title="Consultar Grupos P.A. por: Grupo P.A." id='label' checked>
            <label for='label'>Grupo P.A.</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title="Consultar todos os Grupos P.A." class="checkbox" id='label2'>
            <label for='label2'>Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>