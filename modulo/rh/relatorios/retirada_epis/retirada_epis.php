<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../../');
?>
<html>
<head>
<title>.:: Retirada de EPI�s ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial ...
    if(!data('form', 'txt_data_inicial', '4000', 'IN�CIO')) {
        return false
    }
//Data Final ...
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6,4)+data_inicial.substr(3,2)+data_inicial.substr(0,2)
    data_final          = data_final.substr(6,4)+data_final.substr(3,2)+data_final.substr(0,2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INV�LIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas � > do que 4 anos. Fa�o essa verifica��o porque se o usu�rio 
colocar um intervalo de datas muito distantes, ent�o acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 1465) {
        alert('INTERVALO DE DATAS INV�LIDO !!!\n INTERVALO DE DATAS SUPERIOR A QUATRO ANOS !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
//Funcion�rio ...
    if(!combo('form', 'cmb_funcionario', '', 'SELECIONE O FUNCION�RIO !')) {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' action='imprimir_relatorio.php' method='post' onsubmit='return validar()' target='ifr_relatorio_semanal_visita'>
<input type='hidden' name='passo' value='1'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Retirada de EPI�s
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <p>Data Inicial: 
            <?
                if(empty($txt_data_inicial)) {
                    $txt_data_inicial = date("01/m/Y");
                    $txt_data_final = date("t/m/Y");
                }
                $data_inicial = data::datatodate($txt_data_inicial, '-');
                $data_final = data::datatodate($txt_data_final, '-');
            ?>
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Data Final:
            <input type='text' name='txt_data_final' value='<?=$txt_data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;
            <select name='cmb_funcionario' title='Selecione o Funcion�rio' class='combo'>
            <?
                //Aqui eu listo todos os Funcs que ainda trabalham na Empresa, do Depto. de Vendas ...
                $sql = "SELECT id_funcionario, nome 
                        FROM `funcionarios` 
                        ORDER BY nome ";
                echo combos::combo($sql, $cmb_funcionario);
            ?>
            </select>
            &nbsp;            
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <iframe name='ifr_relatorio_semanal_visita' width='100%' height='450' frameborder='0'></iframe>
        </td>
    </tr>
    <tr id='linha_imprimir' style='visibility:hidden' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='parent.ifr_relatorio_semanal_visita.print()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>