<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/cascates.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>MÁQUINA(S) EXCLUÍDA(S) COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT * 
                    FROM `maquinas` 
                    WHERE `nome` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' ORDER BY nome ";
        break;
        default:
            $sql = "SELECT * 
                    FROM `maquinas` 
                    WHERE `ativo` = '1' ORDER BY nome ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'excluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Máquinas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Excluir Máquina(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Máquina
        </td>
        <td>
            Característica
        </td>		
        <td>
            Valor
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['caracteristica'];?>
        </td>		
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td>
        <?
//Aki verifica se a Máquina está sendo utilizada em lugares comprometedores
            if(cascate::consultar('id_maquina', 'pacs_vs_maquinas, maquinas_vs_funcionarios', $campos[$i]['id_maquina']) == 1) {
//Aki eu verifico a situação do PI em relação ao Estoque
        ?>
            <a href='locais_atrelados.php?id_maquina=<?=$campos[$i]['id_maquina'];?>' class='html5lightbox'>?</a>
            <!--Esse objeto é para não dar erro de JS-->
            <input type='hidden'>
        <?
            }else {
        ?>
            <input type='checkbox' name='chkt_maquina[]' value="<?=$campos[$i]['id_maquina'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        <?
            }
        ?>

        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
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
    foreach($_POST['chkt_maquina'] as $id_maquina) {
        $sql = "UPDATE `maquinas` SET `ativo` = '0' WHERE `id_maquina` = '$id_maquina' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'excluir.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
?>
<title>.:: Excluir Máquina(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onSubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir Máquina(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='label' value='1' title='Consultar máquinas por: Máquina' onclick='document.form.txt_consultar.focus()' checked>
            <label for='label'>Máquina</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' id='label2' value='2' title='Consultar todos as máquinas' onclick='limpar()' class='checkbox'>
            <label for='label2'>Todos os registros</label>
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