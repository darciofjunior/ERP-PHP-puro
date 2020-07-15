<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial   = $_POST['txt_data_inicial'];
    $txt_data_final     = $_POST['txt_data_final'];
    $cmd_consultar      = $_POST['cmd_consultar'];
}else {
    $txt_data_inicial   = $_GET['txt_data_inicial'];
    $txt_data_final     = $_GET['txt_data_final'];
    $cmd_consultar      = $_GET['cmd_consultar'];
}
?>
<html>
<head>
<title>.:: Relat�rio de Apura��o de ICMS � Creditar (ST) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'IN�CIO')) {
        return false
    }
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial = document.form.txt_data_inicial.value
    var data_final = document.form.txt_data_final.value
    data_inicial = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial = eval(data_inicial)
    data_final = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INV�LIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas � > do que 1 ano. Fa�o essa verifica��o porque se o usu�rio 
colocar um intervalo de datas muito distantes, ent�o acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 365) {
        alert('INTERVALO DE DATAS INV�LIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Relat�rio de Apura��o de ICMS � Creditar (ST)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td colspan='3'> 
            Data Inicial:
            <?
//Sugest�o de Per�odo na Primeira vez em que carregar a Tela ...
                if(empty($txt_data_inicial)) {
                    $txt_data_inicial = '01'.date('/m/Y');
                    $txt_data_final = date('t/m/Y');
                }
            ?>
            <input type="text" name="txt_data_inicial" value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type="text" name="txt_data_final" value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp; Estado:
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
        </td>
    </tr>
<?
//Se foram digitadas as Datas acima, ent�o realizo o SQL abaixo ...
if(!empty($cmd_consultar) || !empty($cmd_atualizar)) {
//Campos de Data ...
    $data_inicial = data::datatodate($txt_data_inicial, '-');
    $data_final = data::datatodate($txt_data_final, '-');
/*Busca das NFs que estejam no Per�odo digitado pelo Usu�rio e com Itens que possuem 
valor de ICMS � Creditar em R$ ...*/
    $sql = "SELECT c.id_pais, nfs.id_nf, nfs.id_empresa, nfs.id_nf_num_nota, nfs.snf_devolvida, DATE_FORMAT(nfs.data_emissao, '%d/%m/%Y') AS data_emissao, nfs.suframa, nfs.status, sum(nfsi.icms_creditar_rs) AS total_icms_creditar_rs 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf AND nfsi.icms_creditar_rs > '0' 
            WHERE nfs.`data_emissao` BETWEEN '$data_inicial' and '$data_final' 
            GROUP BY nfs.id_nf ORDER BY nfs.id_empresa, nfs.data_emissao ";
    $campos = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
        $id_empresa_anterior = '';
        for($i = 0; $i < $linhas; $i++) {
/*Aqui eu verifico se a Empresa Anterior � Diferente da Empresa Atual que est� sendo listada 
no loop, se for ent�o eu atribuo o Empresa Atual p/ a Empresa Anterior ...*/
            if($id_empresa_anterior != $campos[$i]['id_empresa']) {
                $id_empresa_anterior = $campos[$i]['id_empresa'];
//S� n�o mostro essa linha quando acaba de Entrar no Loop ...
                if($i > 0) {
?>
    <tr class='linhacabecalho' align="right">
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Valor ICMS � Creditar por Empresa => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($total_icms_creditar_rs_empresa, 2, ',', '.');?>
        </td>
    </tr>
<?
                    $total_icms_creditar_rs_empresa = 0;
                }
?>
    <tr class='linhadestaque'>
        <td colspan='3'>
            <font color='yellow'>
                <b>Empresa: </b>
            </font>
            <?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <b>N.� da NF</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Data de Emiss�o</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Valor R$</b>
        </td>
    </tr>
<?
            }
            if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {
                $nota_sgd = 'N';//var surti efeito l� embaixo
            }else {
                $nota_sgd = 'S'; //var surti efeito l� embaixo
            }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href = '../../nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[$i]['id_nf'];?>&pop_up=1' style='cursor:help' class='html5lightbox'>
            <?
/*******************Controle com a Parte de Apresenta��o dos N�meros da NF*******************/
                if($campos[$i]['status'] == 6) {//Est� sendo acessada uma NF de Devolu��o ...
                    if(!empty($campos[$i]['snf_devolvida'])) {
                        echo '<font color="red" title="NF de Devolu��o" style="cursor:help"><b>'.$campos[$i]['snf_devolvida'].'</font>';
                    }else {
                        echo '<font color="red" title="NF de Devolu��o" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'D').'</font>';
                    }
                }else {//Est� sendo acessada uma NF normal ...
                    echo '<font title="NF de Sa�da" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($campos[$i]['id_nf_num_nota'], 'S').'</font>';
                }
/********************************************************************************************/
            ?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['status'] == 6) {//Se for NF de Devolu��o, ent�o mostro na Cor vermelha ...
                echo '<font color="red">'.number_format($campos[$i]['total_icms_creditar_rs'], 2, ',', '.').'</font>';
            }else {
                echo number_format($campos[$i]['total_icms_creditar_rs'], 2, ',', '.');
            }
            $total_icms_creditar_rs_empresa+= $campos[$i]['total_icms_creditar_rs'];
            $total_icms_creditar_rs_geral+= $campos[$i]['total_icms_creditar_rs'];
        ?>
        </td>
    </tr>
<?
        }
?>
<!--Apresenta fora do Loop o Total Geral da �ltima Empresa-->
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Valor ICMS � Creditar por Empresa => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($total_icms_creditar_rs_empresa, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Valor ICMS � Creditar Geral => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($total_icms_creditar_rs_geral, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='submit' name='cmd_atualizar' value='Atualizar Relat�rio' title='Atualizar Relat�rio' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
//Se n�o foi passado nenhum representante por par�metro ...
    }else {
?>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            <?=$mensagem[1];?>
        </td>
    </tr>
</table>
<?
    }
}
?>
</form>
</body>
</html>