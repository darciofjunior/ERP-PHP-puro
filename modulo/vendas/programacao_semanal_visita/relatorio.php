<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');
?>
<html>
<head>
<title>.:: Relatório de Programação Semanal de Visita ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
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
//Representante ...
    if(!combo('form', 'cmb_representante', '', 'SELECIONE O REPRESENTANTE !')) {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' action='imprimir_relatorio.php' method='post' onsubmit='return validar()' target='ifr_relatorio_semanal_visita'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Relatório de Programação Semanal de Visita
            <p/>
            Data Inicial: 
            <?
                if(empty($_POST['txt_data_inicial'])) {//Sugestão p/ a primeira vez que se carrega a Tela ...
                    $data_inicial   = date('01/m/Y');
                    $data_final     = date('t/m/Y');
                }else {//Demais vezes ...
                    $data_inicial   = $_POST['txt_data_inicial'];
                    $data_final     = $_POST['txt_data_final'];
                }
            ?>
            <input type = 'text' name='txt_data_inicial' value='<?=$data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Data Final:
            <input type = 'text' name='txt_data_final' value='<?=$data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;-&nbsp;
            Representante:
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                /*Observação: O Nishimura é o único Representante que pode ter acesso a todos Representantes 
                porque ele é Gerente de Vendas ...*/
            
                //Aqui eu verifico se o Funcionário que está Logado no Sistema é um Representante ...
                $sql = "SELECT id_representante 
                        FROM `representantes_vs_funcionarios` 
                        WHERE id_funcionario = '$_SESSION[id_funcionario]' LIMIT 1 ";
                $campos_representante   = bancos::sql($sql);
                if(count($campos_representante) == 1 && $_SESSION['id_funcionario'] != 136) {//Sim é um Representante e diferente do Nishimura ...
                    //Aqui eu busco o próprio Representante logado + os seus subordinados no 2º SQL ...
                    $sql = "(SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                            FROM `representantes` 
                            WHERE `id_representante` = '".$campos_representante[0]['id_representante']."') 
                            UNION 
                            (SELECT r.`id_representante`, CONCAT(r.`nome_fantasia`, ' / ', r.`zona_atuacao`) AS dados 
                            FROM `representantes_vs_supervisores` rs 
                            INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante` AND r.`ativo` = '1' 
                            WHERE rs.`id_representante_supervisor` = '".$campos_representante[0]['id_representante']."') 
                            ORDER BY dados ";
                }else {//Não é Representante, então trago todos os Representantes que estão cadastrados no Sistema ...
                    $sql = "SELECT `id_representante`, CONCAT(`nome_fantasia`, ' / ', `zona_atuacao`) AS dados 
                            FROM `representantes` 
                            WHERE `ativo` = '1' ORDER BY dados ";
                }
                echo combos::combo($sql, $_POST['cmb_representante']);
            ?>
            </select>
            &nbsp;-&nbsp;
            Cliente:
            <input type='text' name='txt_cliente' value='<?=$_POST[txt_cliente];?>' title='Digite o Cliente' class='caixadetexto'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
    <tr>
        <td>
            <iframe name='ifr_relatorio_semanal_visita' width='100%' height='400' frameborder='0'></iframe>
        </td>
    </tr>
</table>
</form>
</body>
</html>