<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
require('../../../../lib/vendas.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$valor_dolar_dia = genericas::moeda_dia('dolar');
?>
<html>
<head>
<title>.:: Relatório de Pedidos Emitidos ::.</title>
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
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
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
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 4 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 1465) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A QUATRO ANOS !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='90%' border='1' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Relat&oacute;rio de Pedidos Emitidos
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='11'>
            <p>Data Inicial: 
            <?
                if(empty($txt_data_inicial)) {
                    $datas = genericas::retornar_data_relatorio();
                    $txt_data_inicial = $datas['data_inicial'];
                    $txt_data_final = $datas['data_final'];
                }
                $data_inicial = data::datatodate($txt_data_inicial, '-');
                $data_final = data::datatodate($txt_data_final, '-');
            ?>
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Data Final:
            <input type='text' name='txt_data_final' value='<?=$txt_data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;Tipo de Data: 
            <select name='cmb_tipo_data' title='Selecione o Tipo de Data' class='combo'>
                <?
                    if(empty($_POST['cmb_tipo_data'])) {
                        $selected_emissao = 'selected';
                    }else {
                        if($_POST['cmb_tipo_data'] == 'emissao') {
                            $selected_emissao = 'selected';
                        }else if($_POST['cmb_tipo_data'] == 'programada') {
                            $selected_programada = 'selected';
                        }
                    }
                ?>
                <option value='emissao' <?=$selected_emissao;?>>EMISSÃO</option>
                <option value='programada' <?=$selected_programada;?>>PROGRAMADA</option>
            </select>
            <br>Tipo de Filtro: 
            <select name='cmb_tipo' title='Selecione o Tipo de Filtro' class='combo'>
                <?
                    if($_POST['cmb_tipo'] == 1) {
                        $selected1 = 'selected';
                    }else if($_POST['cmb_tipo'] == 2) {
                        $selected2 = 'selected';
                    }else if($_POST['cmb_tipo'] == 3) {
                        $selected3 = 'selected';
                    }else if($_POST['cmb_tipo'] == '3.5') {
                        $selected3_5 = 'selected';
                    }else if($_POST['cmb_tipo'] == 4) {
                        $selected4 = 'selected';
                    }
                ?>
                <option value='1' <?=$selected1;?>>Divisão</option>
                <option value='2' <?=$selected2;?>>Família</option>
                <option value='3' <?=$selected3;?>>Representante</option>
                <?
                    //Essa opção só aparece para p/ Dárcio, Roberto, Wilson Chefe, Nishimura e Netto devido ser um relatório + pesado ...
                    if($_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147) {
                ?>
                <option value='3.5' <?=$selected3_5;?>>Representante Adm</option>
                <?
                    }
                ?>
                <option value='4' <?=$selected4;?>>UF (Estado)</option>
            </select>
            <?
                    if($_POST['chkt_livre_debito'] == 'S') $checked_livre_debito = 'checked';
            ?>
            &nbsp;
            <input type='checkbox' name='chkt_livre_debito' value='S' id='chkt_livre_debito' class='checkbox' <?=$checked_livre_debito;?>>
            <label for='chkt_livre_debito'>
                <font color='yellow'>
                    <b>Livre de Débito (LD)</b>
                </font>
            </label>
            -
            <?
                    if($_POST['chkt_expresso'] == 'S') $checked_expresso = 'checked';
            ?>
            &nbsp;
            <input type='checkbox' name='chkt_expresso' value='S' id='chkt_expresso' class='checkbox' <?=$checked_expresso;?>>
            <label for='chkt_expresso'>
                <font color='yellow'>
                    <b>EXPRESSO</b>
                </font>
            </label>
            <?
                    if($_POST['chkt_queima_estoque'] == 'S') $chkt_queima_estoque = 'checked';
            ?>
            -
            <input type='checkbox' name='chkt_queima_estoque' value='S' id='chkt_queima_estoque' class='checkbox' <?=$chkt_queima_estoque;?>>
            <label for='chkt_queima_estoque'>
                <font color='yellow'>
                    <b>EXCESSO DE ESTOQUE (ESTÁ SEM FUNÇÃO)</b>
                </font>
            </label>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <br>
            Dias úteis do Mês Contábil: <?='<font color="yellow">'.number_format(genericas::variavel(24), 0).'</font>';?>
            &nbsp;-&nbsp;
            <?
                $qtde_dias_uteis_ate_ontem      = 0;
                $qtde_dias_uteis_ate_data_final = 0;

                //A princípio o Período de Vendas será a Data Inicial que foi digitada no Relatório ...
                $periodo_vendas     = data::datetodata($data_inicial, '/');
                //Verifico quantos dias se passaram da Data Inicial até a Data de Hoje ...
                if($data_final < date('Y-m-d')) {//Se a Data Final for menor que a Data Atual, o sistema irá verificar um período fechado ...
                    $retorno        = data::diferenca_data($data_inicial, $data_final);
                    $dias_passados  = $retorno[0];
                    for($j = 0; $j < $dias_passados; $j++) {
                        $dia_semana = data::dia_semana($periodo_vendas);
                        //Se o dia da Semana for diferente de Sábado e Domingo, então irei contabilizar na Quantidade de dias Úteis até Hoje ...
                        if($dia_semana <> 6 && $dia_semana <> 0) $qtde_dias_uteis_ate_data_final++;
                        $periodo_vendas = data::adicionar_data_hora($periodo_vendas, 1);
                    }
                    $qtde_dias_uteis_ate_hoje   = $qtde_dias_uteis_ate_data_final;
                }else {
                    $retorno        = data::diferenca_data($data_inicial, date('Y-m-d'));
                    $dias_passados  = $retorno[0];

                    //Não contabilizo o dia Atual "Hoje" ...
                    for($j = 0; $j < $dias_passados; $j++) {
                        $dia_semana = data::dia_semana($periodo_vendas);
                        //Se o dia da Semana for diferente de Sábado e Domingo, então irei contabilizar na Quantidade de dias Úteis até Hoje ...
                        if($dia_semana <> 6 && $dia_semana <> 0) $qtde_dias_uteis_ate_ontem++;
                        $periodo_vendas = data::adicionar_data_hora($periodo_vendas, 1);
                    }
                    //Só contabilizo essas horas diárias de Hoje quando a Data não cair em Sábado e Domingo ...
                    $dia_semana                 = data::dia_semana($periodo_vendas);//Verificação p/ o dia de Hoje ...
                    $horas_passadas_hoje        = ($dia_semana <> 6 && $dia_semana <> 0) ? data::calcular_horas('08:00:00', date('H:i:s'), '-') : 0;
                    $qtde_dias_uteis_ate_hoje   = $qtde_dias_uteis_ate_ontem + $horas_passadas_hoje / 10;
                }
            ?>
            Dias úteis até Hoje (Mês Contábil): <?='<font color="yellow">'.number_format($qtde_dias_uteis_ate_hoje, 1, ',', '.').'</font>';?>
            &nbsp;-&nbsp;
            Qtde de Dias do Período Filtrado: 
            <?
                $qtde_dias_periodo_filtrado  = data::diferenca_data($data_inicial, $data_final);
                $qtde_dias_periodo_filtrado = intval($qtde_dias_periodo_filtrado[0]);
                echo '<font color="yellow">'.$qtde_dias_periodo_filtrado.'</font>';
            ?>
        </td>
    </tr>
<? 
    switch($_POST['cmb_tipo']) {
        case 1://Divisão
            require('rel_divisoes.php');
        break;
        case 2://Família
            require('rel_familias.php');
        break;
        case 3://Representante
            require('rel_representantes.php');
        break;
        case '3.5'://Representante Adm ...
            require('rel_representantes_adm.php');
        break;
        case 4://UF (Estado)
            require('rel_ufs.php');
        break;
    }
?>
</table>
</form>
</body>
</html>