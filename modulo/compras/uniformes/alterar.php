<?
require('../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require '../../../lib/menu/menu.php';//Significa que essa Tela foi aberta como sendo Pop-UP ...
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>UNIFORME ALTERADO PARA O FUNCIONÁRIO COM SUCESSO.</font>";

if($passo == 1) {
//Listagem somente dos Funcionários que ainda estão trabalhando na Empresa ...
    if(empty($cmb_calcado)) $cmb_calcado    = '%';
    if(empty($cmb_camisa))  $cmb_camisa     = '%';
    if(empty($cmb_calca))   $cmb_calca      = '%';
    if(empty($cmb_avental)) $cmb_avental    = '%';

    $sql = "SELECT u.*, f.nome 
            FROM `uniformes` u 
            INNER JOIN `funcionarios` f ON f.id_funcionario = u.id_funcionario AND f.nome LIKE '%$txt_funcionario%' 
            WHERE u.`calcado` LIKE '$cmb_calcado' 
            AND u.`camisa` LIKE '$cmb_camisa' 
            AND u.`calca` LIKE '$cmb_calca' 
            AND u.`avental` LIKE '$cmb_avental' 
            AND f.`status` < '3' ORDER BY f.nome ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'alterar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Uniforme(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Alterar Uniforme(s)
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td colspan="2">
            Funcionário
        </td>
        <td>
            N.º Calçado
        </td>
        <td>
            Camisa
        </td>
        <td>
            Calça
        </td>
        <td>
            Avental
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
		for ($i = 0;  $i < $linhas; $i ++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=2&id_uniforme=<?=$campos[$i]['id_uniforme'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href="#">
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align="left">
            <a href="#" class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
        <?
            if($campos[$i]['calcado'] == 0) {
                echo '-';
            }else {
                echo $campos[$i]['calcado'];
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['camisa'];?>
        </td>
        <td>
            <?=$campos[$i]['calca'];?>
        </td>
        <td>
            <?=$campos[$i]['avental'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
		}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'alterar.php'" class='botao'>
            <input type='button' name='cmd_relatorio' value='Relatório' title='Relatório' onclick="nova_janela('relatorio.php', 'CONSULTAR', 'F')" class='botao'>
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
}elseif($passo == 2) {
    //Aqui eu busco dados do Uniforme do Funcionário ...
    $sql = "SELECT u.*, f.nome 
            FROM `uniformes` u 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = u.`id_funcionario` 
            WHERE u.`id_uniforme` = '$_GET[id_uniforme]' ORDER BY f.nome ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Uniforme(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Variáveis
    var calcado = document.form.cmb_calcado.value
    var camisa = document.form.cmb_camisa.value
    var calca = document.form.cmb_calca.value
    var avental = document.form.cmb_avental.value

    if(calcado == '' && camisa == '' && calca == '' && avental == '') {
        alert('SELECIONE PELO MENOS UMA OPÇÃO !')
        document.form.cmb_calcado.focus()
        return false
    }
}
</Script>
</head>
<body>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=3';?>" onSubmit="return validar()">
<input type='hidden' name='id_uniforme' value="<?=$_GET['id_uniforme'];?>">
<table border='0' width='60%' align='center' cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
            <td colspan='2'>
                Alterar Uniforme
            </td>
	</tr>
	<tr class='linhanormal'>
            <td width='20%'>
                <b>Funcionário:</b>
            </td>
            <td width='80%'>
                <?=$campos[0]['nome'];?>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                N.º Calçado:
            </td>
            <td>
                <select name="cmb_calcado" title="Selecione um Calçado" class='combo'>
                    <option value="" style="color:red">SELECIONE</option>
                    <?
                            if($campos[0]['calcado'] == 0) {
                    ?>
                    <option value="<?=$campos[0]['calcado'];?>" selected>-</option>
                    <?
                            }
                            for($i = 35; $i < 46; $i++) {
                                    if($i == $campos[0]['calcado']) {
                    ?>
                    <option value="<?=$i;?>" selected><?=$i;?></option>
                    <?
                                    }else {
                    ?>
                    <option value="<?=$i;?>"><?=$i;?></option>
                    <?
                                    }
                            }
                    ?>
                </select>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                Camisa:
            </td>
            <td>
                <select name="cmb_camisa" title="Selecione uma Camisa" class='combo'>
                    <option value='' style='color:red'>SELECIONE</option>
                    <?
                        if($campos[0]['camisa'] == 'P') {
                            $camisa_p = 'selected';
                        }else if($campos[0]['camisa'] == 'M') {
                            $camisa_m = 'selected';
                        }else if($campos[0]['camisa'] == 'G') {
                            $camisa_g = 'selected';
                        }else if($campos[0]['camisa'] == 'GG') {
                            $camisa_gg = 'selected';
                        }else if($campos[0]['camisa'] == 'XG') {
                            $camisa_xg = 'selected';
                        }else if($campos[0]['camisa'] == 'EXG') {
                            $camisa_exg = 'selected';
                        }else if($campos[0]['camisa'] == 'EXGG') {
                            $camisa_exgg = 'selected';
                        }
                    ?>
                    <option value='P' <?=$camisa_p;?>>P</option>
                    <option value='M' <?=$camisa_m;?>>M</option>
                    <option value='G' <?=$camisa_g;?>>G</option>
                    <option value='GG' <?=$camisa_gg;?>>GG</option>
                    <option value='XG' <?=$camisa_xg;?>>XG</option>
                    <option value='EXG' <?=$camisa_exg;?>>EXG</option>
                    <option value='EXGG' <?=$camisa_exgg;?>>EXGG</option>
                </select>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                Calça:
            </td>
            <td>
                <select name="cmb_calca" title="Selecione uma Calça" class='combo'>
                    <option value='' style='color:red'>SELECIONE</option>
                    <?
                        if($campos[0]['calca'] == 'P') {
                            $calca_p = 'selected';
                        }else if($campos[0]['calca'] == 'M') {
                            $calca_m = 'selected';
                        }else if($campos[0]['calca'] == 'G') {
                            $calca_g = 'selected';
                        }else if($campos[0]['calca'] == 'GG') {
                            $calca_gg = 'selected';
                        }else if($campos[0]['calca'] == 'XG') {
                            $calca_xg = 'selected';
                        }else if($campos[0]['calca'] == 'EXG') {
                            $calca_exg = 'selected';
                        }else if($campos[0]['calca'] == 'EXGG') {
                            $calca_exgg = 'selected';
                        }
                    ?>
                    <option value='P' <?=$calca_p;?>>P</option>
                    <option value='M' <?=$calca_m;?>>M</option>
                    <option value='G' <?=$calca_g;?>>G</option>
                    <option value='GG' <?=$calca_gg;?>>GG</option>
                    <option value='XG' <?=$calca_xg;?>>XG</option>
                    <option value='EXG' <?=$calca_exg;?>>EXG</option>
                    <option value='EXGG' <?=$calca_exgg;?>>EXGG</option>
                </select>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                Avental:
            </td>
            <td>
                <select name="cmb_avental" title="Selecione um Avental" class='combo'>
                    <option value='' style='color:red'>SELECIONE</option>
                    <?
                        if($campos[0]['avental'] == 'P') {
                            $avental_p = 'selected';
                        }else if($campos[0]['avental'] == 'M') {
                            $avental_m = 'selected';
                        }else if($campos[0]['avental'] == 'G') {
                            $avental_g = 'selected';
                        }else if($campos[0]['avental'] == 'GG') {
                            $avental_gg = 'selected';
                        }else if($campos[0]['avental'] == 'XG') {
                            $avental_xg = 'selected';
                        }else if($campos[0]['avental'] == 'EXG') {
                            $avental_exg = 'selected';
                        }else if($campos[0]['avental'] == 'EXGG') {
                            $avental_exgg = 'selected';
                        }
                    ?>
                    <option value='P' <?=$avental_p;?>>P</option>
                    <option value='M' <?=$avental_m;?>>M</option>
                    <option value='G' <?=$avental_g;?>>G</option>
                    <option value='GG' <?=$avental_gg;?>>GG</option>
                    <option value='XG' <?=$avental_xg;?>>XG</option>
                    <option value='EXG' <?=$avental_exg;?>>EXG</option>
                    <option value='EXGG' <?=$avental_exgg;?>>EXGG</option>
                </select>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                Observação:
            </td>
            <td>
                <textarea name='txt_observacao' title="Digite a Observação" maxlength='85' cols='85' rows='1' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
            </td>
	</tr>
	<tr class='linhacabecalho' align='center'>
            <td colspan="2">
                <?
                    //Pode ser que essa Tela foi aberta como sendo Pop-UP ...
                    if(empty($_GET['pop_up'])) {//Se não exibo normalmente os botões ...
                ?>
                <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
                <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form','REDEFINIR')" style="color:#ff9900;" class='botao'>
                <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
                <?
                    }
                ?>
                &nbsp;
            </td>
	</tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    $data_sys = date('Y-m-d H:i:s');
    $sql = "UPDATE `uniformes` SET `calcado` = '$_POST[cmb_calcado]', `camisa` = '$_POST[cmb_camisa]', `calca` = '$_POST[cmb_calca]', `avental` = '$_POST[cmb_avental]', `observacao` = '$_POST[txt_observacao]', `data_sys` = '$data_sys' WHERE `id_uniforme` = '$_POST[id_uniforme]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Uniforme(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onLoad="document.form.txt_funcionario.focus()">
<form name="form" method="post" action=''>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
            <td colspan="2">
                Alterar Uniforme
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Funcionário
            </td>
            <td>
                    <input type="text" name="txt_funcionario" title="Digite o Funcionário" size="40" class="caixadetexto">
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    N.º Calçado
            </td>
            <td>
                    <select name="cmb_calcado" title="Selecione um Calçado" class='combo'>
                            <option value="" style="color:red" selected>SELECIONE</option>
                            <?
                                    for($i = 35; $i < 46; $i++) {
                            ?>
                            <option value="<?=$i;?>"><?=$i;?></option>
                            <?
                                    }
                            ?>
                    </select>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Camisa
            </td>
            <td>
                    <select name='cmb_camisa' title='Selecione uma Camisa' class='combo'>
                        <option value='' style='color:red'>SELECIONE</option>
                        <option value='P'>P</option>
                        <option value='M'>M</option>
                        <option value='G'>G</option>
                        <option value='GG'>GG</option>
                        <option value='XG'>XG</option>
                        <option value='EXG'>EXG</option>
                        <option value='EXGG'>EXGG</option>
                    </select>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Calça
            </td>
            <td>
                    <select name="cmb_calca" title="Selecione uma Calça" class='combo'>
                        <option value='' style='color:red'>SELECIONE</option>
                        <option value='P'>P</option>
                        <option value='M'>M</option>
                        <option value='G'>G</option>
                        <option value='GG'>GG</option>
                        <option value='XG'>XG</option>
                        <option value='EXG'>EXG</option>
                        <option value='EXGG'>EXGG</option>
                    </select>
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Avental
            </td>
            <td>
                    <select name="cmb_avental" title="Selecione um Avental" class='combo'>
                        <option value='' style='color:red'>SELECIONE</option>
                        <option value='P'>P</option>
                        <option value='M'>M</option>
                        <option value='G'>G</option>
                        <option value='GG'>GG</option>
                        <option value='XG'>XG</option>
                        <option value='EXG'>EXG</option>
                        <option value='EXGG'>EXGG</option>
                    </select>
            </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
            <td colspan="2">
                    <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="document.form.txt_funcionario.focus()" class='botao'>
                    <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class='botao'>
            </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>