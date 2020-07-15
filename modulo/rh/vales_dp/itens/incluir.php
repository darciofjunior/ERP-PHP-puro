<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>N�O EXISTE(M) FUNCION�RIO(S) CADASTRADO(S) NESTE TIPO DE VALE.</font>";
$mensagem[2] = "<font class='atencao'>N�O EXISTE(M) CONS�RCIO(S) A SER(EM) IMPORTADO(S).</font>";
?>
<html>
<head>
<title>.:: Incluir Vale(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
//Aqui eu verifico se existe pelo menos 1 option selecionado ...
    var elementos = document.form.elements
    var radios_selec = 0
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'radio') {
            if(elementos[i].checked == true) radios_selec++
        }
    }
//Se n�o existir nenhuma op��o selecionada ...
    if(radios_selec == 0) {
        alert('SELECIONE PELO MENOS UMA OP��O !')
        document.form.opt_item[0].focus()
        return false
    }else {
//Se j� existir alguma op��o selecionada ent�o ...
        if(document.form.opt_item[0].checked == true) {//Dia 20
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                window.location = '../vales_dia20/incluir.php?cmb_data_holerith='+cmb_data_holerith
            }
        }else if(document.form.opt_item[1].checked == true) {//Combust�vel
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                window.location = '../combustivel/incluir_alterar.php?cmb_data_holerith='+cmb_data_holerith
            }
        }else if(document.form.opt_item[2].checked == true) {//Cons�rcio
            window.location = '../consorcio/importar.php?cmb_data_holerith='+cmb_data_holerith
        }else if(document.form.opt_item[3].checked == true) {//Conv�nio M�dico
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                window.location = '../convenio_medico/incluir_alterar.php?cmb_data_holerith='+cmb_data_holerith
            }
        }else if(document.form.opt_item[4].checked == true) {//Conv�nio Odontol�gico
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                window.location = '../convenio_odonto/incluir_alterar.php?cmb_data_holerith='+cmb_data_holerith
            }
        }else if(document.form.opt_item[5].checked == true) {//Transporte
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                window.location = '../transporte/incluir.php?cmb_data_holerith='+cmb_data_holerith
            }
        }else if(document.form.opt_item[6].checked == true) {//Empr�stimo
            window.location = '../emprestimo/incluir.php'
        }else if(document.form.opt_item[7].checked == true) {//Celular
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                window.location = '../celular/incluir_alterar.php?cmb_data_holerith='+cmb_data_holerith
            }
        }else if(document.form.opt_item[8].checked == true) {//Mensalidade Sindical
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                window.location = '../mensalidade_sindical/incluir_alterar.php?cmb_data_holerith='+cmb_data_holerith
            }
        }else if(document.form.opt_item[9].checked == true) {//Contribui��o Confederativa
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                window.location = '../contribuicao_confederativa/incluir_alterar.php?cmb_data_holerith='+cmb_data_holerith
            }
        }else if(document.form.opt_item[10].checked == true) {//Imposto Sindical
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                var mes = cmb_data_holerith.substr(5, 2)
//Se o M�s = Abril, ent�o eu direciono p/ estar colhendo o Imposto Sindical de Todos os Funcion�rios ...
                if(mes == 4 || mes == '04') {
                    window.location = '../imposto_sindical/incluir_alterar.php?cmb_data_holerith='+cmb_data_holerith
                }else {//Colhe o Imposto Sindical de forma Unit�ria, ou seja de apenas 1 func
                    window.location = '../imposto_sindical/incluir_unitario.php?cmb_data_holerith='+cmb_data_holerith
                }
            }
        }else if(document.form.opt_item[11].checked == true) {//Contribui��o Assistencial
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                window.location = '../contribuicao_assistencial/incluir_alterar.php?cmb_data_holerith='+cmb_data_holerith
            }
        }else if(document.form.opt_item[12].checked == true) {//Cr�dito Consignado
            window.location = '../credito_consignado/incluir.php'
        }else if(document.form.opt_item[13].checked == true) {//Mensalidade MetalCred
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                window.location = '../mensalidade_metalcred/incluir_alterar.php?cmb_data_holerith='+cmb_data_holerith
            }
        }else if(document.form.opt_item[14].checked == true) {//Saldo (-) Holerith PF
            if(document.form.cmb_data_holerith.value == '') {
                alert('SELECIONE UMA DATA DE HOLERITH !')
                document.form.cmb_data_holerith.focus()
                return false
            }else {
                var cmb_data_holerith = document.form.cmb_data_holerith.value
                window.location = '../saldo_negativo_holerith_pf/incluir.php?cmb_data_holerith='+cmb_data_holerith
            }
        }
    }
}

function incluir_data_holerith() {
    nova_janela('../class_data_holerith/incluir.php', 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
}

function alterar_data_holerith() {
    if(document.form.cmb_data_holerith.value == '') {
        alert('SELECIONE A DATA DE HOLERITH !')
        document.form.cmb_data_holerith.focus()
        return false
    }else {
        nova_janela('../class_data_holerith/alterar.php?data='+document.form.cmb_data_holerith.value, 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function atualizar() {
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='passo' onclick='atualizar()'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Incluir Vale(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td> Data de Holerith: 
            <select name="cmb_data_holerith" title="Selecione a Data de Holerith" class='combo'>
            <?
                $data_atual_menos_180 = data::adicionar_data_hora(date('d/m/Y'), -180);
                $data_atual_menos_180 = data::datatodate($data_atual_menos_180, '-');
                
//S� listo nessa Combo as Datas de Holeriths que sejam > que a Data de Atual ...
                $sql = "SELECT data, date_format(data, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        WHERE data >= '$data_atual_menos_180' ORDER BY data ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;&nbsp; <img src = '../../../../imagem/menu/incluir.png' border='0' title='Incluir Data de Holerith' alt='Incluir Data de Holerith' onclick='incluir_data_holerith()'>
            &nbsp;&nbsp; <img src = '../../../../imagem/menu/alterar.png' border='0' title='Alterar Data de Holerith' alt='Alterar Data de Holerith' onclick='alterar_data_holerith()'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='1' title='Dia 20' id='opt1' checked>
            <label for='opt1'>
                <font color='darkblue'><b>Dia 20</b></font>
            </label>
        </td>
    </tr>
    <!--A op��o 2 que existia como sendo Vale Avulso, foi unificada com a de Empr�stimo a partir do 
    dia 24/09/2012 ...-->
    <tr class='linhanormal'>
        <td>
        <input type='radio' name='opt_item' value='3' title='Combust�vel' id='opt3'>
        <label for='opt3'>
            <font color='darkblue'><b>Combust�vel</b></font>
        </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='4' title='Cons�rcio' id='opt4'>
            <label for="opt4">
                <font color='darkblue'>Cons�rcio (Importar Vales)</font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='5' title='Conv�nio M�dico' id='opt5'>
            <label for='opt5'>
                <font color='darkblue'><b>Conv�nio M�dico</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='6' title='Conv�nio Odontol�gico' id='opt6'>
            <label for='opt6'>
                <font color='darkblue'><b>Conv�nio Odontol�gico</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='7' title='Transporte' id='opt7'>
            <label for='opt7'>
                <font color='darkblue'><b>Transporte</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='8' title='Avulso / Empr�stimo' id='opt8'>
            <label for='opt8'>
                <font color='darkblue'>Avulso / Empr�stimo</font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='9' title='Celular' id='opt9'>
            <label for='opt9'>
                <font color='darkblue'><b>Celular</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='10' title='Mensalidade Sindical' id='opt10'>
            <label for='opt10'>
                <font color='darkblue'><b>Mensalidade Sindical</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='11' title='Contribui��o Confederativa' id='opt11'>
            <label for='opt11'>
                <font color='darkblue'><b>Contribui��o Confederativa</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='12' title='Imposto Sindical' id='opt12'>
            <label for='opt12'>
                <font color='darkblue'><b>Imposto Sindical (Imposto Anual do M�s de Abril)</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='13' title='Contribui��o Assistencial' id='opt13'>
            <label for='opt13'>
                <font color='darkblue'><b>Contribui��o Assistencial</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='14' title='Cr�dito Consignado' id='opt14'>
            <label for='opt14'>
                <font color='darkblue'>Cr�dito Consignado</font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='15' title='Mensalidade MetalCred' id='opt15'>
            <label for='opt15'>
                <font color='darkblue'><b>Mensalidade MetalCred</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_item' value='16' title='Saldo (-) Holerith PF' id='opt16'>
            <label for='opt16'>
                <font color='red'><b>Saldo (-) Holerith PF</b></font>
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avan�ar &gt;&gt;' title='Avan�ar' onclick='avancar()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>