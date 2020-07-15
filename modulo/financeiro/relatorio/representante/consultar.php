<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/financeiros.php');
require('../../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) DUPLICATA(S) EXISTENTE(S) PARA ESSE REPRESENTANTE.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial   = $_POST['txt_data_inicial'];
    $txt_data_final     = $_POST['txt_data_final'];
    $opt_data           = $_POST['opt_data'];
    $cmb_representante  = $_POST['cmb_representante'];
}else {
    $txt_data_inicial   = $_GET['txt_data_inicial'];
    $txt_data_final     = $_GET['txt_data_final'];
    $opt_data           = $_GET['opt_data'];
    $cmb_representante  = $_GET['cmb_representante'];
}
?>
<html>
<head>
<title>.:: Relatório de Conta(s) do Representante(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.txt_data_inicial.value != '' || document.form.txt_data_final.value != '') {
        //Data Inicial ...
        if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
            return false
        }
        //Data Final ...
        if(!data('form', 'txt_data_final', '4000', 'FIM')) {
            return false
        }
    }
    var data_inicial    = document.form.txt_data_inicial.value.substr(6,4)+document.form.txt_data_inicial.value.substr(3,2)+document.form.txt_data_inicial.value.substr(0,2)
    var data_final      = document.form.txt_data_final.value.substr(6,4)+document.form.txt_data_final.value.substr(3,2)+document.form.txt_data_final.value.substr(0,2)
    if(data_inicial > data_final) {
        alert('DATA INVÁLIDA !\nDATA INICIAL MAIOR QUE A DATA FINAL !')
        document.form.txt_data_inicial.focus()
        return false
    }
//Representante ...
    if(document.form.cmb_representante.value == '') {
        alert('SELECIONE UM REPRESENTANTE !')
        document.form.cmb_representante.focus()
        return false
    }
    document.form.target = ''
    document.form.action = ''
    document.form.submit()
}

function imprimir() {
    document.form.target = 'IMPRIMIR'
    document.form.action = 'pdf/relatorio.php'
    nova_janela('pdf/relatorio.php', 'IMPRIMIR', 'F')
    document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_data_inicial.focus()'>
<form name='form' method='post' action=''>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Relatório de Conta(s) do Representante(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='9'>
            <b>Data Inicial:</b>
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' title='Digite a Data Inicial' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c','c')">
            &nbsp;
            <b>Data Final:</b>
            <input type='text' name='txt_data_final' value='<?=$txt_data_final;?>' title='Digite a Data Final' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c','c')">
            <?
                
                if(empty($opt_data)) {//Sugestão Inicial quando o sistema acabar de carregar Tela ...
                    $checked1 = 'checked';
                }else {//Demais vezes que já submeteu ...
                    if($opt_data == 1) {
                        $checked1 = 'checked';
                    }else if($opt_data == 2) {
                        $checked2 = 'checked';
                    }
                }
            ?>
            &nbsp;
            <input type='radio' name='opt_data' value='1' id='data_emissao' <?=$checked1;?>>
            <label for='data_emissao'>Data de Emissão</label>
            <input type='radio' name='opt_data' value='2' id='data_vencimento' <?=$checked2;?>>
            <label for='data_vencimento'>Data de Vencimento</label>

            <br>
            <b>Representante</b>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql, $cmb_representante);
            ?>
            </select>
            &nbsp;
            <input type='button' name='cmd_consultar' value='Consultar' title='Consultar' onclick='return validar()' class='botao'>
        </td>
    </tr>
<?
//Aki todos os Cheques das Datas, Cheque e Banco Selecionados
if(!empty($cmb_representante)) {
    if(!empty($txt_data_inicial)) {
        $data_inicial 	= data::datatodate($txt_data_inicial, '-');
        $data_final 	= data::datatodate($txt_data_final, '-');

        if($opt_data == 1) {//Data de Emissão ...
            $condicao = " AND cr.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' ";
        }else if($opt_data == 2) {//Data de Vencimento ...
            $condicao = " AND cr.`data_vencimento` BETWEEN '$data_inicial' AND '$data_final' ";
        }
    }

    $sql = "SELECT r.nome_representante, cr.*, c.razaosocial, c.credito, tr.recebimento, CONCAT(tm.simbolo, ' ') AS simbolo, tm.id_tipo_moeda 
            FROM `representantes` r 
            INNER JOIN `contas_receberes` cr ON cr.id_representante = r.id_representante AND r.`id_representante` = '$cmb_representante' $condicao 
            INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente 
            INNER JOIN `tipos_recebimentos` tr ON tr.id_tipo_recebimento = cr.id_tipo_recebimento 
            INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda ORDER BY cr.data_vencimento DESC ";
    $campos = bancos::sql($sql, $inicio, 10000, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Não encontrou nenhuma Duplicata do Representante ...
?>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            Semana
        </td>
        <td>
            Cliente
        </td>
        <td>
            Crédito
        </td>
        <td>
            Data de Vencimento
        </td>
        <td>
            Tipo Recebimento
        </td>
        <td>
            Praça de <br>
            Recebimento
        </td>
        <td>
            Valor
        </td>
        <td>
            Valor Recebido
        </td>
        <td>
            Valor Extra
        </td>
    </tr>
    <?
        //Essas variáveis me servem de controle numa parte mais abaixo do código ...
        $moedas             = genericas::moeda_dia();
        $valor_dolar_dia    = $moedas['dolar'];
        $valor_euro_dia     = $moedas['euro'];
    
        $somatoria_valor        = 0;
        $somatoria_valor_pago   = 0;
        $soma_valores_extra     = 0;

        for($i = 0; $i < $linhas; $i++) {
    ?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['semana'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=$campos[$i]['credito'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
        </td>
        <td>
            <?=$campos[$i]['recebimento'];?>
        </td>
        <td>
        <?
            $sql = "SELECT b.banco 
                    FROM `contas_receberes` cr 
                    INNER JOIN `bancos` b ON b.id_banco = cr.id_banco 
                    WHERE cr.`id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
            $campos_bancos = bancos::sql($sql);
            if(count($campos_bancos) > 0) {
                echo $campos_bancos[0]['banco'];
            }else {
                if($campos[$i]['id_tipo_recebimento'] == 7) {
                    echo '<font color="red"><b> (PROTESTADO)</b></font>';
                }else {
                    echo '&nbsp';
                }
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['valor'] == '0.00') {
                echo '&nbsp;';
            }else {
                if($campos[$i]['id_tipo_moeda'] == 2) {
                    $valor = $campos[$i]['valor'] * $valor_dolar_dia;
                }else if($campos[$i]['id_tipo_moeda'] == 3) {
                    $valor = $campos[$i]['valor'] * $valor_euro_dia;
                }else {
                    $valor = $campos[$i]['valor'];
                }
                $somatoria_valor+= $valor;
                echo $campos[$i]['simbolo'].' '.number_format($campos[$i]['valor'], 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['id_tipo_moeda'] == 2) {
                $valor_pago = $campos[$i]['valor_pago'] * $valor_dolar_dia;
            }else if($campos[$i]['id_tipo_moeda'] == 3) {
                $valor_pago = $campos[$i]['valor_pago'] * $valor_euro_dia;
            }else {
                $valor_pago = $campos[$i]['valor_pago'];
            }
            $somatoria_valor_pago+= $valor_pago;
            echo $campos[$i]['simbolo'].$campos[$i]['valor_pago'];
        ?>
        </td>
        <td align='right'>
        <?
            $calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
            echo $campos[$i]['simbolo'].number_format($calculos_conta_receber['valores_extra'], 2, ',', '.');
            $soma_valores_extra+= $calculos_conta_receber['valores_extra'];
        ?>
        </td>
    </tr>
    <?}?>
    <tr class='linhadestaque' align='right'>
        <td colspan='6'>
            Valores Totais
        </td>
        <td>
            <?='R$ '.number_format($somatoria_valor, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($somatoria_valor_pago, 2, ',', '.');?>
        </td>
        <td>
        <?
            $somatoria_valor_extra = $somatoria_valor - $somatoria_valor_pago + $soma_valores_extra;
            echo 'R$ '.number_format($somatoria_valor_extra, 2, ',', '.');
        ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='imprimir()' style='color:darkblue' class='botao'>
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
}
?>