<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/ops/controle_processo/controle_processo.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

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
<title>.:: Relatório de Processo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
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
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 1 ano. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 366) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
//Máquina ...
    if(!combo('form', 'cmb_maquina', '', 'SELECIONE UMA MÁQUINA !')) {
        return false
    }
    document.form.submit()
}

function carregar_dados() {
    if(document.form.cmb_maquina.value != '') {
        document.getElementById('lbl_combos').style.visibility = 'visible'
    }else {
        document.getElementById('lbl_combos').style.visibility = 'hidden'
    }
    ajax('carregar_dados.php?valor=1', 'cmb_funcionario', '<?=$_POST['cmb_funcionario'];?>')
    ajax('carregar_dados.php?valor=2', 'cmb_processo_operacao', '<?=$_POST['cmb_processo_operacao'];?>')
    document.form.txt_consultar.focus()
}
</Script>
</head>
<body onload='carregar_dados()'>
<form name='form' method='POST' action='' onsubmit="return validar()">
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Relatório de Processo
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='10'>
            Data Inicial:
            <?
    //Sugestão de Período na Primeira vez em que carregar a Tela ...
                if(empty($txt_data_inicial)) {
                    $txt_data_inicial   = date('d/m/').(date('Y') - 1);
                    $txt_data_final     = date('d/m/Y');
                }
            ?>
            <input type = 'text' name = 'txt_data_inicial' value="<?=$txt_data_inicial;?>" onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type = 'text' name = 'txt_data_final' value="<?=$txt_data_final;?>" onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;
            Máquina: <select name='cmb_maquina' title='Selecione a Máquina' onchange='carregar_dados()' class='combo'>
                <?
                    $sql = "SELECT DISTINCT(m.id_maquina), m.nome 
                            FROM maquinas m 
                            INNER JOIN ops_vs_processos ovp ON ovp.id_maquina = m.id_maquina 
                            ORDER BY m.nome ";
                    echo combos::combo($sql, $_POST['cmb_maquina']);
                ?>
            </select>
            <label id='lbl_combos' style='visibility:hidden'>
                <br/>
                Funcionário:
                <select name='cmb_funcionario' id='cmb_funcionario' title='Selecione o Funcionario' class='combo'>
                </select>  
                Processo / Operação:
                <select name='cmb_processo_operacao' id='cmb_processo_operacao' title='Selecione o Processo / Operação' onclick='return false' class='combo'>
                </select>
                <br/>
            </label>
            <?
                if(empty($_POST['opt_tipo_filtro'])) {//Aqui é para o caso de quando carrega a Tela e não dar erro ...
                    $_POST['opt_tipo_filtro']   = 1;
                    $checkedr                   = 'checked';
                }else {
                    if($_POST['opt_tipo_filtro'] == 1) {
                        $checkedr   = 'checked';
                    }else if($_POST['opt_tipo_filtro'] == 2) {
                        $checkedd   = 'checked';
                    }else {
                        $checkedo   = 'checked';
                    }
                }
            ?>
            <input type='radio' name='opt_tipo_filtro' value='1' title='Selecione o Tipo de Filtro' id='tipo_filtro1' <?=$checkedr;?>>
            <label for='tipo_filtro1'>Referência</label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_tipo_filtro' value='2' title='Selecione o Tipo de Filtro' id='tipo_filtro2' <?=$checkedd;?>>
            <label for='tipo_filtro2'>Discriminação</label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_tipo_filtro' value='3' title='Selecione o Tipo de Filtro' id='tipo_filtro3' <?=$checkedo;?>>
            <label for='tipo_filtro3'>N.º OP</label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type = 'text' name='txt_consultar' value="<?=$_POST['txt_consultar'];?>" size='40' class='caixadetexto'>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?

//Se foram digitadas as Datas acima, então realizo o SQL abaixo ...
if(!empty($cmd_consultar)) {
    $condicao_datas = " WHERE `data_inicial` >= '".data::datatodate($_POST['txt_data_inicial'], '-')."' AND `data_final` <= '".data::datatodate($_POST['txt_data_final'], '-')."' ";
    
    $maquina        = (!empty($cmb_maquina)) ? $_POST['cmb_maquina'] : '%';
    $funcionario    = (!empty($cmb_funcionario)) ? $_POST['cmb_funcionario'] : '%';
    $operacao       = (!empty($cmb_processo_operacao)) ? $_POST['cmb_processo_operacao'] : '%';
    
    if(!empty($_POST['txt_consultar'])) {
        if($_POST['opt_tipo_filtro'] == 1) {//Se consultou por Referência ...
            $condicao_referencia    = " AND pa.`referencia` LIKE '$_POST[txt_consultar]%' ";
        }else if($_POST['opt_tipo_filtro'] == 2) {//Se consultou por Discriminação ...
            $condicao_discriminacao = " AND pa.`discriminacao` LIKE '$_POST[txt_consultar]%' ";
        }else {//Se consultou por N.º de OP ...
            $condicao_numero_op     = " AND ops.`id_op` LIKE '$_POST[txt_consultar]%' ";
        }
    }
    
    $sql = "SELECT ops.`qtde_produzir`, ovp.`id_op`, ovp.`qtde_produzida`, m.`nome`, f.`nome` AS funcionario, 
            mvp.`operacao`, pa.`discriminacao`, ovp.`data_inicial`, ovp.`hora_inicial`, ovp.`data_final`, ovp.`hora_final` 
            FROM `ops_vs_processos` ovp 
            INNER JOIN `ops` ON ops.`id_op` = ovp.`id_op` $condicao_numero_op 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` $condicao_referencia $condicao_discriminacao 
            INNER JOIN `maquinas` m ON m.`id_maquina` = ovp.`id_maquina` AND ovp.`id_maquina` LIKE '$maquina' 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = ovp.`id_funcionario` AND ovp.`id_funcionario` LIKE '$funcionario' 
            INNER JOIN `maquinas_vs_operacoes` mvp ON mvp.`id_maquina_operacao` = ovp.`id_maquina_operacao` AND ovp.`id_maquina_operacao` LIKE '$operacao' 
            $condicao_datas ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Se não encontrou nenhum Registro de acordo com o Filtro ...
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
    }else {//Se encontrou alguma coisa ...
?>
    <tr class='linhacabecalho' align='center'>
        <td>OP</td>
        <td>Qtde à Produzir</td>
        <td>Qtde Produzida</td>        
        <td>Produto</td>
        <td>Máquina</td>
        <td>Funcionário</td>
        <td>Processo / Operação</td>
        <td>Data Inicial - Hora</td>
        <td>Data Final - Hora</td>
        <td>Tempo Gasto</td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = '../detalhes.php?id_op='.$campos[$i]['id_op'];
            $data_inicial = data::datetodata($campos[$i]['data_inicial'], '/').' - '.$campos[$i]['hora_inicial'];
            $data_final   = data::datetodata($campos[$i]['data_final'], '/').' - '.$campos[$i]['hora_final'];
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['id_op'];?>
            </a>
        </td>
        <td>
            <?=intval($campos[$i]['qtde_produzir']);?>
        </td>
        <td>
            <?=$campos[$i]['qtde_produzida'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td align="left">
            <?=$campos[$i]['funcionario'];?>
        </td>
        <td>
            <?=$campos[$i]['operacao'];?>
        </td>
        <td>
            <?=$data_inicial;?>
        </td>
        <td>
            <?echo ($data_final > 0 ? $data_final : '');?>
        </td>
        <td>
        <?
            if($data_final > 0) {//Só irá calcular quando tivermos a Data Final ...
                //A princípio o Período do Processo será a Data Inicial que foi iniciado o Processo da OP ...
                $periodo_processo       = data::datetodata($campos[$i]['data_inicial'], '/');
                $retorno                = data::diferenca_data($campos[$i]['data_inicial'], $campos[$i]['data_final']);
                //Eu adiciono + 1 pq se o usuário começou e terminou o processo no mesmo dia, de qq modo representa que ele gastou um 1 dia ...
                $total_de_dias_corridos = $retorno[0] + 1;
                $total_dias_trabalhados = 0;
                $total_horas            = '00:00';
                if($total_de_dias_corridos > 1) {
                    for($j = 1; $j <= $total_de_dias_corridos; $j++) {
                        //Se o dia da Semana for diferente de Sábado e Domingo, então irei contabilizar as horas trabalhadas no relatório ...
                        $dia_semana = data::dia_semana($periodo_processo);//Verifico em qual dia da Semana caiu essa Data ...
                        if($dia_semana <> 6 && $dia_semana <> 0) {
                            $total_dias_trabalhados++;
                            //Se o Dia da Semana for Sexta-feira então o usuário descansa uma Hora mais cedo ...                          
                            if($total_dias_trabalhados == 1) {//Representa o 1º dia de trabalho do funcionario ...
                                if($campos[$i]['hora_inicial'] <= '11:30') {
                                    //coloca 15:00 na final pq desconta 1 hora de sexta feira e 1 hora de desconto...
                                    if($dia_semana == 5) {
                                        $total_horas_diaria = data::calcular_horas($campos[$i]['hora_inicial'], '15:00', '-');
                                    }else {
                                        //coloco 16:00 na final por que houve desconto de 1 hora do almoço...
                                        $total_horas_diaria = data::calcular_horas($campos[$i]['hora_inicial'], '16:00', '-');
                                    }
                                }else {
                                    //coloca 16:00 na final pq desconta 1 hora de sexta feira...
                                    if($dia_semana == 5) {
                                        $total_horas_diaria = data::calcular_horas($campos[$i]['hora_inicial'], '16:00', '-');
                                    }else {
                                        //coloco 17 pois nao houve desconto e eh != de 6 feira...
                                        $total_horas_diaria = data::calcular_horas($campos[$i]['hora_inicial'], '17:00', '-');
                                    }
                                }
                            }else if($j == $total_de_dias_corridos) {//Representa o último dia de trabalho do funcionário ...
                                //echo '<br>total dias trab:'.$total_dias_trabalhados.'<br>';             
                                if($campos[$i]['hora_final'] > '11:30') {
                                    $descontar = '01:00';
                                    $descontado = data::calcular_horas($descontar, $campos[$i]['hora_final'], '-');
                                    $total_horas_diaria = data::calcular_horas('07:00', $descontado, '-');
                                }else {
                                    $total_horas_diaria = data::calcular_horas('07:00', $campos[$i]['hora_final'], '-'); 
                                }
                            }else {//Representa os demais dias ...
                                //Inicio o Expediente como se fosse às 08:00 porque estamos descontando o Almoço ...
                                if($dia_semana == 5) {
                                    $total_horas_diaria = data::calcular_horas('08:00', '16:00', '-');
                                }else {
                                    $total_horas_diaria = data::calcular_horas('08:00', '17:00', '-');
                                }
                            }
                            $total_horas = data::calcular_horas($total_horas, $total_horas_diaria, '+');
                        }
                        $periodo_processo = data::adicionar_data_hora($periodo_processo, 1);//Aqui é para analisar o próximo dia da Data de Processo ...
                    }
                    echo $total_horas;
                }else {//Terminou o trampo no mesmo dia ...
                    if($campos[$i]['hora_inicial'] <= '11:30' && $campos[$i]['hora_final'] > '11:31') {
                        $descontar = '01:00';
                    }else {
                        $descontar = '00:00';
                    }
                    $total_horas = data::calcular_horas($campos[$i]['hora_inicial'], $campos[$i]['hora_final'], '-');
                    //Passamos o desconto na frente como sendo a hora inicial para nao dar erro no resultado...
                    echo data::calcular_horas($descontar, $total_horas, '-');
                }
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
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