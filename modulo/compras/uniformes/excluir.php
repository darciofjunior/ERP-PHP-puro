<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>UNIFORME EXCLUÍDO PARA O FUNCIONÁRIO COM SUCESSO.</font>";

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
            window.location = 'excluir.php?valor=1'
        </Script>
<?
	}else {
?>
<html>
<head>
<title>.:: Excluir Uniforme(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='7'>
            Excluir Uniforme(s)
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
            <td>
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
            <td>
                <label for='todos'>Todos </label>
                <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox" id='todos'>
            </td>
    </tr>
<?
            for ($i=0;  $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
            <td align="left">
                <?=$campos[$i]['nome'];?>
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
            <td>
                <input type="checkbox" name="chkt_uniforme[]" value="<?=$campos[$i]['id_uniforme'];?>" onclick="checkbox('form', 'chkt','<?=$i;?>', '#E8E8E8')" class="checkbox">
            </td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align='center'>
            <td colspan='7'>
                <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class='botao'>
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
    foreach($_POST['chkt_uniforme'] as $id_uniforme) {
        $sql = "DELETE FROM `uniformes` WHERE `id_uniforme` = '$id_uniforme' LIMIT 1 ";
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
<title>.:: Excluir Uniforme(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onLoad="document.form.txt_funcionario.focus()">
<form name="form" method="post" action=''>
<input type='hidden' name='passo' value='1'>
<table width='70%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            Excluir Uniforme
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
                    <select name="cmb_calcado" title="Selecione um Calçado" class="combo">
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
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="document.form.txt_funcionario.focus()" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>