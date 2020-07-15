<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial   = $_POST['txt_data_inicial'];
    $txt_data_final     = $_POST['txt_data_final'];
    $cmb_representante  = $_POST['cmb_representante'];
    $txt_cliente        = $_POST['txt_cliente'];
    $cmd_consultar      = $_POST['cmd_consultar'];
}else {
    $txt_data_inicial   = $_GET['txt_data_inicial'];
    $txt_data_final 	= $_GET['txt_data_final'];
    $cmb_representante 	= $_GET['cmb_representante'];
    $txt_cliente        = $_GET['txt_cliente'];
    $cmd_consultar      = $_GET['cmd_consultar'];
}
?>
<html>
<head>
<title>.:: Relatório de Entrega(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial ...
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final ...
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
<body>
<form name='form' action='' method='post' onsubmit='return validar()'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Relatório de Controle de Entrega
            <p/>
            Data Inicial: 
            <?
                if(empty($txt_data_inicial)) {//Sugestão p/ a primeira vez que se carrega a Tela ...
                    $txt_data_inicial 	= date('01/m/Y');
                    $txt_data_final 	= date('t/m/Y');
                }
                $data_inicial   = data::datatodate($txt_data_inicial, '-');
                $data_final     = data::datatodate($txt_data_final, '-');
            ?>
            <input type = 'text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Data Final:
            <input type = 'text' name='txt_data_final' value='<?=$txt_data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size="11" maxlength="10" class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">           
            &nbsp;-&nbsp;
            Motorista:
            <select name='cmb_motorista' title='Selecione o Motorista' class='combo'>
                <?
                    if($_POST['cmb_motorista'] == 'Kalifa'){
                        $selectedk = 'selected';
                    }else if($_POST['cmb_motorista'] == 'Michael') {
                        $selectedm = 'selected';
                    }else {
                        $selecteds = 'selected';
                    }
                ?>
                <option value = '' style='color:red' <?=$selecteds;?>>SELECIONE</option>
                <option value = 'Kalifa' <?=$selectedk;?>>Kalifa</option>
                <option value = 'Michael' <?=$selectedm;?>>Michael</option>
            </select>
            &nbsp;-&nbsp;
            Cliente:
            <input type='text' name='txt_cliente' value='<?=$_POST[txt_cliente];?>' title='Digite o Cliente' class='caixadetexto'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
/*****************Se já submeteu então*****************/
if(!empty($cmd_consultar)) {
    if($_POST['txt_cliente'] != '')     $condicao_cliente = " AND (c.`razaosocial` LIKE '%".$_POST['txt_cliente']."%' OR c.`nomefantasia` LIKE '%".$_POST['txt_cliente']."%') ";
    if($_POST['cmb_motorista'] != '')   $condicao_motorista = " AND ce.`motorista` = '".$_POST['cmb_motorista']."' ";
    
    //SQL principal
    $sql = "SELECT ce.`qtde_volume`, ce.`peso`, ce.`motorista`, SUBSTRING(ce.`data_sys`,1 , 10) AS data, 
            ce.`id_nf`, CONCAT(c.razaosocial, '(', c.nomefantasia, ')') AS cliente, c.`endereco`, c.`num_complemento`, c.`bairro`, c.`cidade` 
            FROM `controles_entregas` ce
            INNER JOIN `nfs` ON nfs.`id_nf` = ce.`id_nf`
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` $condicao_cliente 
            WHERE SUBSTRING(ce.`data_sys`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
            $condicao_motorista ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Se não trazer nenhum registro então ...
?>
    <tr class='atencao' align='center'>
        <td colspan='7'>
            <?=$mensagem[1];?>
        <td>
    </tr>
<?
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Data
        </td>
        <td>
            N° NF
        </td>
        <td>
            Cliente
        </td>
        <td>
            Endereço
        </td>
        <td>
            Qtde Volume
        </td>
        <td>
            Peso
        </td>
        <td>
            Motorista
        </td>
    </tr>
<?        
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td>
            <a href='../nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[$i]['id_nf'];?>&pop_up=1' class='html5lightbox'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['endereco'].', '.$campos[$i]['num_complemento'].' - '.$campos[$i]['bairro'].' - '.$campos[$i]['cidade'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde_volume'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['peso'], 1, ',', '.').' Kg(s)';?>
        </td>
        <td align='center'>
            <?=$campos[$i]['motorista'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho'>
        <td colspan='7'>
            &nbsp;
        </td>
    </tr>
<?
    }
}
?>
</table>
</form>
</body>
</html>