<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $representante          = $_POST['representante'];
    $cmb_representante      = $_POST['cmb_representante'];
    $cmb_subordinado        = $_POST['cmb_subordinado'];
    $cmd_consultar          = $_POST['cmd_consultar'];
}else {
    $representante          = $_GET['representante'];
    $cmb_representante      = $_GET['cmb_representante'];
    $cmb_subordinado        = $_GET['cmb_subordinado'];
    $cmd_consultar          = $_GET['cmd_consultar'];
}
?>
<html>
<head>
<title>.:: Espelho(s) de Produto(s) Projetados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data 
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial 	= document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final          = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
}
</Script>
</head>
<?
//Se anteriormente foi selecionado um Subordinado, faço rodar a função de Ajax automaticamente quando carregar a Tela ...
if(!empty($cmb_subordinado)) $onload = "ajax('carregar_subordinados.php', 'cmb_subordinado', '$cmb_subordinado')";
?>
<body onload='<?=$onload;?>'>
<form name='form' action='' method='post' onsubmit='return validar()'>
<table width='70%' border='1' cellspacing ='0' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Espelho(s) de Produto(s) Projetados - 
            <font color='yellow' size='-1'>
                Representante(s): 
            </font>
            <?
                if(!empty($representante)) {//Verifico se o Vendedor foi passado por Parâmetro ...
                    $sql = "SELECT nome_fantasia 
                            FROM `representantes` 
                            WHERE `id_representante` = '$representante' LIMIT 1 ";
                    $campos_representante = bancos::sql($sql);
                    echo $campos_representante[0]['nome_fantasia'];
            ?>
                <input type="hidden" name="representante" value="<?=$representante;?>">
            <?
                }else {//Se não foi passado nenhum Representante por parâmetro, então eu apresento a combo abaixo ...
            ?>
                <select name="cmb_representante" title="Selecione o Representante" onchange="ajax('carregar_subordinados.php', 'cmb_subordinado')" class="combo">
            <?
                    //Aqui eu listo todos os Representantes que não são Subordinados a ninguém ...
                    $sql = "SELECT id_representante, nome_fantasia AS dados 
                            FROM `representantes` 
                            WHERE `ativo` = '1' 
                            AND `id_representante` NOT IN 
                            (SELECT r.id_representante 
                            FROM `representantes_vs_supervisores` rs 
                            INNER JOIN `representantes` r ON r.id_representante = rs.id_representante AND r.ativo = '1') 
                            ORDER BY nome_fantasia ";
                    echo combos::combo($sql, $cmb_representante);
            ?>
                </select>
                &nbsp;
                <font color='yellow' size='-1'>
                    Subordinado(s): 
                </font>
                <select name="cmb_subordinado" title="Selecione o Subordinado" class='combo'>
                    <option value='' style='color:red'>SELECIONE</option>
                </select>
            <?
                }
            ?>
            &nbsp;
            <p>Data Inicial: 
            <?
                if(empty($txt_data_inicial)) {//Aqui é p/ quando acabar de carregar a tela, não submeteu nenhuma vez ...
                    $txt_data_inicial   = '01/'.date('m/Y');
                    $txt_data_final     = date('t/m/Y');
                }
            ?>
            <input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class='caixadetexto'>
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Data Final:
            <input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class='caixadetexto'>
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
if(!empty($cmd_consultar)) {//Só processará dados de Relatório, quando o usuário clicar no botão Consultar ...
    $data_inicial   = data::datatodate($txt_data_inicial, '-');
    $data_final     = data::datatodate($txt_data_final, '-');

    if(!empty($cmb_subordinado)) {
        $representante = $cmb_subordinado;
    }else {
        if(!empty($cmb_representante)) {
            $representante = (!empty($representante)) ? $representante : $cmb_representante;
        }else {
            $representante = '%';
        }
    }
//Aqui eu listo todos os Clientes que possuem uma Data de Espelho ...
    $sql = "SELECT DISTINCT(c.id_cliente), IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) AS cliente, c.data_ultimo_espelho_produtos, ufs.sigla 
            FROM `clientes` c 
            INNER JOIN clientes_vs_representantes cr ON c.id_cliente = cr.id_cliente AND cr.id_representante LIKE '$representante'
            LEFT JOIN `ufs` ON ufs.id_uf = c.id_uf 
            WHERE SUBSTRING(`data_ultimo_espelho_produtos`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
            AND c.`ativo` = '1' ORDER BY c.data_ultimo_espelho_produtos DESC ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Se não encontrou nenhum registro ...
?>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente
        </td>
        <td>
            UF
        </td>
        <td>
            Último Espelho <br/>de Produtos(s) Projetado
        </td>
    </tr>
<?    
	for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>        
        <td>
        <?
            if($campos[$i]['data_ultimo_espelho_produtos'] == '0000-00-00 00:00:00') {
                echo '&nbsp;';
            }else {
                echo data::datetodata(substr($campos[$i]['data_ultimo_espelho_produtos'], 0, 10), '/').' às '.substr($campos[$i]['data_ultimo_espelho_produtos'], 11, 8);
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
}
?>
</form>
</body>
</html>