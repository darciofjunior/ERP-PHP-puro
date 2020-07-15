<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/holerith/gerenciar_folha/gerenciar_folha.php', '../../../../../');

$mensagem[1] = 'HOLERITH - CRÉDITO ALTERADO COM SUCESSO !';

if($passo == 1) {
//Atualizando os Holerith(s) Crédito(s) do Funcionário ...
    for($i = 0; $i < count($_POST['hdd_funcionario_vs_holerith']); $i++) {
//Aqui eu verifico o Tipo de Salário do Funcionário ...
        $sql = "SELECT f.`tipo_salario` 
                FROM `funcionarios_vs_holeriths` fh 
                INNER JOIN `funcionarios` f ON fh.`id_funcionario` = f.`id_funcionario` 
                WHERE fh.`id_funcionario_vs_holerith` = '".$_POST['hdd_funcionario_vs_holerith'][$i]."' LIMIT 1 ";
        $campos = bancos::sql($sql);
//Atualizando ...
        $sql = "UPDATE `funcionarios_vs_holeriths` SET `valor_liquido_holerith` = '".$_POST['txt_valor_liq_holerith'][$i]."', `dias_horas_trabalhadas` = '".$_POST['txt_dias_horas_trabalhados'][$i]."', `outros_rend_prop` = '".$_POST['txt_outros_rend_prop'][$i]."', `faltas_dia_hr` = '".$_POST['txt_dias_hs_faltas'][$i]."', `atrasos_hr_min` = '".$_POST['txt_hs_min_atrasos'][$i]."', `dsr_hr_min` = '".$_POST['txt_dias_hs_min_dsr'][$i]."', `hora_extra` = '".$_POST['txt_qtde_h_ext_fer_sab'][$i]."' WHERE `id_funcionario_vs_holerith` = ".$_POST['hdd_funcionario_vs_holerith'][$i]." LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'itens.php?cmb_data_holerith=<?=$_POST['cmb_data_holerith'];?>&cmb_empresa=<?=$_POST['cmb_empresa'];?>&valor=1'
    </Script>
<?
}else {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $cmb_data_holerith          = $_POST['cmb_data_holerith'];
        $cmb_empresa                = $_POST['cmb_empresa'];
    }else {
        $cmb_data_holerith          = $_GET['cmb_data_holerith'];
        $cmb_empresa                = $_GET['cmb_empresa'];
    }
//Esses campos de Data de Holerith vou estar utilizando + abaixo ...
    $sql = "SELECT * 
            FROM `vales_datas` 
            WHERE `id_vale_data` = '$cmb_data_holerith' LIMIT 1 ";
    $campos_data_holerith   = bancos::sql($sql);
    $data_holerith          = $campos_data_holerith[0]['data'];
    $qtde_hrs_trabalhadas   = $campos_data_holerith[0]['qtde_hrs_trabalhadas'];
    $qtde_dias_trabalhados  = $campos_data_holerith[0]['qtde_dias_trabalhados'];
    $hora_extra             = $campos_data_holerith[0]['hora_extra'];

/*Aqui eu verifico se existe pelo menos 1 Holerith p/ o Funcionário na Data de Holerith e Empresa 
Especificados na Tela anterior ...*/
    $sql = "SELECT fh.* 
            FROM `funcionarios_vs_holeriths` fh 
            INNER JOIN `funcionarios` f ON f.id_funcionario = fh.id_funcionario AND f.`status` < '3' 
            INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa AND e.`id_empresa` = '$cmb_empresa' 
            WHERE fh.`id_vale_data` = '$cmb_data_holerith' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
/****************************************Somente na primeira vez****************************************/
//Se não foi inserido nenhum Holerith nessa Data de Holerith, então ...
    if($linhas == 0) {//Vai ser gerado os Holeriths (Créditos)...
/*Busca de todos os funcionários da Empresa com exceção dos funcionários Default (1,2), 
ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, Dona Sandra 66 e Wilson 68 porque estes não são 
funcionários, simplesmente só possuem cadastrado no Sistema p/ poder acessar algumas telas ...
Só mostro os funcionários que ainda não foram demitidos*/
        $sql = "SELECT f.id_funcionario, f.tipo_salario 
                FROM `funcionarios` f 
                INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa AND e.id_empresa = '$cmb_empresa' 
                WHERE f.`status` < '3' 
                AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY f.nome ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        $data_sys = date('Y-m-d H:i:s');
//Gerando os Holeriths ...
        for($i = 0; $i < $linhas; $i++) {
            $id_funcionario_loop = $campos[$i]['id_funcionario'];
            if($campos[$i]['tipo_salario'] == 1) {//Se o Tipo do Salário do Funcionário = Horista
                $dias_horas_trabalhadas = $qtde_hrs_trabalhadas;
            }else {
                $dias_horas_trabalhadas = $qtde_dias_trabalhados;
            }
            $sql = "INSERT INTO `funcionarios_vs_holeriths` (`id_funcionario_vs_holerith`, `id_funcionario`, `id_vale_data`, `valor_liquido_holerith`, `dias_horas_trabalhadas`, `outros_rend_prop`, `faltas_dia_hr`, `atrasos_hr_min`, `dsr_hr_min`, `hora_extra`, `data_sys`) VALUES (null, '$id_funcionario_loop', '$cmb_data_holerith', '0', '$dias_horas_trabalhadas', '0', '0', '0', '0', '$hora_extra', '$data_sys') ";
            bancos::sql($sql);
        }
    }
/*Aqui busco os Holerith (Créditos) p/ os Funcionários na Data de Holerith e Empresa Especificados 
na Tela anterior ...
/*Só busca os funcionários da Empresa com exceção dos funcionários Default (1,2), 
ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, Dona Sandra 66 e Wilson 68 porque estes não são 
funcionários, simplesmente só possuem cadastrado no Sistema p/ poder acessar algumas telas ...
Só mostro os funcionários que ainda não foram demitidos*/
    $sql = "SELECT f.id_funcionario, f.tipo_salario, fh.id_funcionario_vs_holerith, fh.id_vale_data, 
            fh.valor_liquido_holerith, fh.valor_total_receber, fh.dias_horas_trabalhadas, fh.outros_rend_prop, 
            fh.faltas_dia_hr, fh.atrasos_hr_min, fh.dsr_hr_min, fh.comissao_alba, fh.comissao_tool, fh.comissao_grupo, 
            fh.dsr_alba, fh.dsr_tool, fh.dsr_grupo, fh.hora_extra, fh.data_sys_comissao, fh.data_sys, fh.observacao 
            FROM `funcionarios_vs_holeriths` fh 
            RIGHT JOIN `funcionarios` f ON f.`id_funcionario` = fh.`id_funcionario` AND fh.`id_vale_data` = '$cmb_data_holerith' 
            WHERE f.`status` < '3' 
            AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
            AND f.`id_empresa` = '$cmb_empresa' ORDER BY f.nome ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
//Verifica se tem pelo menos um Holerith (Crédito) Cadastrado ...
    if($linhas > 0) {
        $datas = genericas::retornar_periodo_folha($data_holerith);
?>
<html>
<head>
<title>.:: Gerenciar Folha ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
///Tratamento com os Objetos na hora de gravar no BD ...
    var elementos = document.form.elements
    if(typeof(elementos['hdd_funcionario_vs_holerith[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario_vs_holerith[]'].length)
    }
//Tratamento nos objetos Vlr Pedido e Vlr Liberado p/ gravar os objetos no BD ...
    for(var i = 0; i < linhas; i++) {
        //Aqui eu desabilito essa caixa quando for para o caso da Empresa Grupo ...
        document.getElementById('txt_valor_liq_holerith'+i).disabled    = false
//Tratamento com os campos na hora de Gravar no Banco de Dados ...
        document.getElementById('txt_dias_horas_trabalhados'+i).value   = document.getElementById('txt_dias_horas_trabalhados'+i).value.replace(':', '.')
        document.getElementById('txt_valor_liq_holerith'+i).value       = strtofloat(document.getElementById('txt_valor_liq_holerith'+i).value)
        document.getElementById('txt_dias_hs_faltas'+i).value           = strtofloat(document.getElementById('txt_dias_hs_faltas'+i).value)
        document.getElementById('txt_hs_min_atrasos'+i).value           = document.getElementById('txt_hs_min_atrasos'+i).value.replace(':', '.')
        document.getElementById('txt_dias_hs_min_dsr'+i).value          = document.getElementById('txt_dias_hs_min_dsr'+i).value.replace(':', '.')
        document.getElementById('txt_outros_rend_prop'+i).value         = strtofloat(document.getElementById('txt_outros_rend_prop'+i).value)
        document.getElementById('txt_qtde_h_ext_fer_sab'+i).value       = document.getElementById('txt_qtde_h_ext_fer_sab'+i).value.replace(':', '.')
        document.getElementById('hdd_funcionario_vs_holerith'+i).value  = document.getElementById('hdd_funcionario_vs_holerith'+i).value.replace(':', '.')
    }
    document.form.passo.value = 1
}

function travar_link() {
//Aqui eu travo o Link p/ garantir que o usuário vai salvar os dados primeiros ...
    document.form.hdd_travar_link.value = 1
}

function dados_funcionario(id_funcionario_loop) {
    if(document.form.hdd_travar_link.value == 0) {
//Coloquei esse nome de id_funcionario_loop, p/ não dar conflito com a variável 'id_funcionário' da sessão
        nova_janela('../../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop='+id_funcionario_loop+'&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '')
    }else {
        resposta = confirm('ANTES DE ALTERAR ALGUM DADO DE FUNCIONÁRIO, SALVE OS DADOS PRIMEIRO !!!\nDESEJA SALVAR ESSES DADOS ?')
        if(resposta == true) {
            validar()//Aqui tem todo o Tratamento com os campos e tal ...
            document.form.submit()
        }else {
            return false
        }
    }
}

/*Criei essa função p/ impedir que o usuário digite nas caixas de texto que estão 
com o layout de desabilitadas, não desabilitei as caixas porque retardava muito o servidor 
na hora de habilitar as caixas via JavaScript na hora enviar p/ o banco de dados*/
function cursor_dias_horas_trabalhados(indice) {
    document.getElementById('txt_dias_horas_trabalhados'+indice).focus()
}

/*Esse parâmetro id_funcionario_current é p/ saber que essa Tela de Relatório foi acessada de Dentro do Gerenciar 
Folha Holerith e vai me auxiliar p/ fazer uns controles diferenciais nessa Tela ...*/
function visualizar_relatorio(id_funcionario_current) {
    nova_janela('../../../atraso_falta/relatorio.php?passo=1&txt_data_inicial=<?=$datas['data_inicial'];?>&txt_data_final=<?=$datas['data_final'];?>&id_funcionario_current='+id_funcionario_current, 'CONSULTAR', '', '', '', '', '420', '980', 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo'>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Gerenciar Folha - 
            <font color='yellow'>
                Data de Holerith: 
            </font>
            <?=data::datetodata($data_holerith, '/');?> - 
            <font color='yellow'>
                Período da Folha: 
            </font>
            <?
                echo $periodo = $datas['data_inicial'].' à '.$datas['data_final'];
//Variáveis que serão utilizadas mais abaixo p/ SQL ...
                $data_inicial_folha_usa = data::datatodate($datas['data_inicial'], '-');
                $data_final_folha_usa = data::datatodate($datas['data_final'], '-');
            ?>
            - 
            <font color='yellow'>
                Empresa: 
            </font>
            <?=genericas::nome_empresa($cmb_empresa);?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='11'>
            <font color='yellow'>
                Qtde de Horas Trabalhadas: 
            </font>
            <?=number_format($qtde_hrs_trabalhadas, 2, ':', '');?>
            &nbsp;-&nbsp;
            <font color='yellow'>
                Qtde de Dias Trabalhados: 
            </font>
            <?=$qtde_dias_trabalhados;?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <b>Funcionário</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Obs</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Tipo de Salário</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Salário PD</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Dias / Horas<br>Trabalhados</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Valor Líquido do <br>Holerith PD</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Dias / Hs <br>Faltas</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Hs / Min <br>Atrasos</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Dias - Hs / Mins <br>DSR</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Outros Rend. <br>S/ Proporc.</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Qtde H. Extras <br>Feriado ao Sábado</b>
        </td>
    </tr>
<?
        $tab_index = 1000;//Vai servir de controle p/ as tabulações na vertical ...
        $tab_index2 = 10000;//Vai servir de controle p/ as tabulações na vertical ...
        $tab_index3;//Vai servir de controle p/ as tabulações na horizontal ...
        $tab_index4 = 100000;//Vai servir de controle p/ as tabulações na vertical ...
        $tab_index5 = 100;//Vai servir de controle p/ as tabulações na vertical ...
//Listando os Holerith(s) Crédito(s) ...
        for($i = 0; $i < $linhas; $i++) {
            $id_funcionario_vs_holerith = $campos[$i]['id_funcionario_vs_holerith'];
/*Aqui é uma segurança p/ q o Registro do funcionário venha ser inserido na tabela relacional de Holeriths
caso tenha furado algum registro ...*/
            if(empty($id_funcionario_vs_holerith)) {
                if($campos[$i]['tipo_salario'] == 1) {//Se o Tipo do Salário do Funcionário = Horista
                    $dias_horas_trabalhadas = $qtde_hrs_trabalhadas;
                }else {
                    $dias_horas_trabalhadas = $qtde_dias_trabalhados;
                }
                $sql = "INSERT INTO `funcionarios_vs_holeriths` (`id_funcionario_vs_holerith`, `id_funcionario`, `id_vale_data`, `valor_liquido_holerith`, `dias_horas_trabalhadas`, `outros_rend_prop`, `faltas_dia_hr`, `atrasos_hr_min`, `dsr_hr_min`, `hora_extra`, `data_sys`) VALUES (null, '".$campos[$i]['id_funcionario']."', '$cmb_data_holerith', '0', '$dias_horas_trabalhadas', '0', '0', '0', '0', '$hora_extra', '$data_sys') ";
                bancos::sql($sql);
                $id_funcionario_vs_holerith = bancos::id_registro();
            }
            //Busca de alguns dados do funcionário com o id_funcionario do Holerith (Crédito) ...
            $sql = "SELECT nome, ultimas_ferias_data_inicial, ultimas_ferias_data_final, tipo_salario, salario_pd 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <a href="javascript:dados_funcionario('<?=$campos[$i]['id_funcionario'];?>')" title='Detalhes Funcionário' class='link'>
                    <?=$campos_funcionario[0]['nome'];?>
                </a>
                <img src = '../../../../../imagem/visualizar_detalhes.png' border='0' title='Visualizar Relatório' alt='Visualizar Relatório' onclick="visualizar_relatorio('<?=$campos[$i]['id_funcionario'];?>')">
            </font>
            <?
                //Controle p/ saber se o Funcionário está de Férias ...
                if($campos_funcionario[0]['ultimas_ferias_data_inicial'] < $data_holerith && $campos_funcionario[0]['ultimas_ferias_data_final'] > $data_holerith) {
                    $vetor      = data::diferenca_data($campos_funcionario[0]['ultimas_ferias_data_inicial'], $campos_funcionario[0]['ultimas_ferias_data_final']);
                    //Tenho que somar + 1 na váriavel vetor[0], pq a função diferenca_data não leva em conta o 1º dia p/ as Férias ...
                    echo '<font color="red"><b> (Férias - '.($vetor[0] + 1).' dias)</b></font>';
                }
            ?>
        </td>
        <td>
            <a href = 'observacao.php?id_funcionario_vs_holerith=<?=$campos[$i]['id_funcionario_vs_holerith'];?>' class='html5lightbox'>
            <?
                if(empty($campos[$i]['observacao'])) {//Se a Observação não estiver preenchida ...
                    echo '<font style="cursor:help" title="Não possui Observação" color="red">N</font>';
                }else {//Se estiver preenchida ...
                    echo '<font style="cursor:help" title="Possui Observação"><b>S</b></font>';
                }
            ?>
            </a>
        </td>
        <td>
        <?
            if($campos_funcionario[0]['tipo_salario'] == 1) {
                echo 'HORISTA';
            }else {
                echo 'MENSALISTA';
            }
        ?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos_funcionario[0]['salario_pd'], 2, ',', '.');?>
        </td>
        <?
//Controle com o Campo de Horas Trabalhadas ...
            if($campos_funcionario[0]['tipo_salario'] == 1) {//Se o Tipo do Salário do Funcionário = Horista
                $onkeyup                = "verifica(this, 'hora', '', '', event);travar_link()";
                $dias_horas_trabalhadas = number_format($campos[$i]['dias_horas_trabalhadas'], 2, ':', '');
                $dias_hs_min_dsr        = number_format($campos[$i]['dsr_hr_min'], 2, ':', '');
            }else {//Mensalista ...
                $onkeyup                = "verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0 || this.value > 30) {this.value = ''};travar_link()";
                $dias_horas_trabalhadas = number_format($campos[$i]['dias_horas_trabalhadas'], 0, '', '');
                $dias_hs_min_dsr        = number_format($campos[$i]['dsr_hr_min'], 0, '', '');
            }
        ?>
        <td>
            <input type='text' name='txt_dias_horas_trabalhados[]' id='txt_dias_horas_trabalhados<?=$i;?>' value='<?=$dias_horas_trabalhadas;?>' title='Digite o Valor de Dias / Horas Trabalhadas' size='7' maxlength='6' onkeyup="<?=$onkeyup;?>" tabindex='<?=++$tab_index;?>' id='txt_dias_horas_trabalhados<?=$i;?>' class='caixadetexto'>
        </td>
        <td>
        <?
//P/ este campo, existe um controle especial de travar e destravar o campo ...
            if($cmb_empresa != 4) {//Qualquer empresa diferente de Grupo, eu deixo habilitado ...
                $class      = 'caixadetexto';
                $disabled   = '';
            }else {//Somente quando for Grupo que eu travo essa caixa
                $class      = 'textdisabled';
                $disabled   = 'disabled';
            }
        ?>
            <input type='text' name='txt_valor_liq_holerith[]' id='txt_valor_liq_holerith<?=$i;?>' value='<?=number_format($campos[$i]['valor_liquido_holerith'], 2, ',', '.');?>' title='Digite o Valor Líquido do Holerith' size='9' maxlength='8' onkeyup="verifica(this, 'moeda_especial', '2', '', event);travar_link()" tabindex='<?=++$tab_index2;?>' class='<?=$class;?>' <?=$disabled;?>>
        </td>
        <td>
        <?
//Aqui eu zero a variável p/ não herdar valor do loop anterior ...
            $total_dias_horas = 0;
/*Aqui eu busco todos as "Faltas" do Funcionário que foram lançadas na da Portaria "Eletrônica" 
no Período da Folha de Pagamento e que estão com a Marcação de Descontar 'S' ...*/
            $sql = "SELECT hora_inicial_descontar, hora_final_descontar 
                    FROM `funcionarios_acompanhamentos` 
                    WHERE `id_funcionario_acompanhado` = '".$campos[$i]['id_funcionario']."' 
                    AND SUBSTRING(`data_ocorrencia`, 1, 10) BETWEEN '$data_inicial_folha_usa' AND '$data_final_folha_usa' 
                    AND `descontar` = 'S' 
                    AND `motivo` = '2' 
                    AND `registro_portaria` = 'S' ORDER BY data_ocorrencia DESC ";
            $campos_portaria = bancos::sql($sql);
            $linhas_portaria = count($campos_portaria);
            for($j = 0; $j < $linhas_portaria; $j++) {
                if($campos_funcionario[0]['tipo_salario'] == 1) {//Se o Tipo do Salário do Funcionário = Horista
//1) Cálculo feito em cima das Horas ...
                    $hora_inicial       = strtok($campos_portaria[$j]['hora_inicial_descontar'], '.');
                    $hora_final         = strtok($campos_portaria[$j]['hora_final_descontar'], '.');
                    $diferenca_horas    = $hora_final - $hora_inicial;

                    echo $diferenca_horas.' - ';
//2) Cálculo feito em cima das Minutos ...
                    $minuto_inicial     = substr(strchr($campos_portaria[$j]['hora_inicial_descontar'], '.'), 1, 2);
                    $minuto_final       = substr(strchr($campos_portaria[$j]['hora_final_descontar'], '.'), 1, 2);
                    $diferenca_minutos  = ($minuto_final - $minuto_inicial) / 60;

                    echo $diferenca_minutos.' - ';
//3) Cálculo do Total de Horas ...
                    $total_dias_horas+= ($diferenca_horas + $diferenca_minutos) - 1;//Desconta 1 Hora de Almoço ...
                }else {
                    $total_dias_horas++;
                }
            }
/***********************************************************************************************************/
//Enquanto o Valor desse campo for Zero, então eu vou sugerindo o cálculo baseado na Portaria ...
            if($campos[$i]['faltas_dia_hr'] == '0.0') {
                $faltas_dia_hr = round($total_dias_horas, 1);
            }else {
                $faltas_dia_hr = number_format($campos[$i]['faltas_dia_hr'], 1, ',', '.');
            }
            echo round($total_dias_horas, 1);
        ?>
            <!--onfocus="cursor_dias_horas_trabalhados('<?=$i;?>')"--> 
            <input type='text' name='txt_dias_hs_faltas[]' id='txt_dias_hs_faltas<?=$i;?>' value='<?=$faltas_dia_hr;?>' title='Digite os Dias / Hs Faltas' size='7' maxlength='6' onkeyup="verifica(this, 'moeda_especial', '1', '', event);if(this.value == 0) {this.value = ''};travar_link()" tabindex='<?=++$tab_index3;?>' class='textdisabled'>
        </td>
        <td>
        <?
//Aqui eu zero a variável p/ não herdar valor do loop anterior ...
            $total_horas = 0;
/*Aqui eu busco todos os Atrasos do Funcionário "Entrada e Saída " que foram lançadas na da Portaria "Eletrônica" 
no Período da Folha de Pagamento e que estão com a Marcação de Descontar 'S' ...*/
            $sql = "SELECT hora_inicial_descontar, hora_final_descontar 
                    FROM `funcionarios_acompanhamentos` 
                    WHERE `id_funcionario_acompanhado` = '".$campos[$i]['id_funcionario']."' 
                    AND SUBSTRING(`data_ocorrencia`, 1, 10) BETWEEN '$data_inicial_folha_usa' AND '$data_final_folha_usa' 
                    AND `descontar` = 'S' 
                    AND `motivo` IN (0, 1) 
                    AND `registro_portaria` = 'S' ORDER BY data_ocorrencia DESC ";
            $campos_portaria = bancos::sql($sql);
            $linhas_portaria = count($campos_portaria);
            for($j = 0; $j < $linhas_portaria; $j++) {
//1) Cálculo feito em cima das Horas ...
                $hora_inicial       = strtok($campos_portaria[$j]['hora_inicial_descontar'], '.');
                $hora_final         = strtok($campos_portaria[$j]['hora_final_descontar'], '.');
                $diferenca_horas    = $hora_final - $hora_inicial;

                echo $diferenca_horas.' - ';

//2) Cálculo feito em cima das Minutos ...
                $minuto_inicial     = substr(strchr($campos_portaria[$j]['hora_inicial_descontar'], '.'), 1, 2);
                $minuto_final       = substr(strchr($campos_portaria[$j]['hora_final_descontar'], '.'), 1, 2);
                $diferenca_minutos  = ($minuto_final - $minuto_inicial) / 60;

                echo $diferenca_minutos;
                echo '<br>';

//3) Cálculo do Total de Horas ...
                $total_horas+= $diferenca_horas + $diferenca_minutos;
            }
            $somente_horas      = (integer)$total_horas;//Pego a parte Inteira das Horas ...
            $somente_minutos    = round(($total_horas - $somente_horas) * 60, 2);
            if(strlen($somente_minutos) == 1) {$somente_minutos.= '0';}//P/ que os Minutos fique com 2 casas ...
            $hs_min_atrasos     = $somente_horas.':'.$somente_minutos;//Aqui eu faço a junção do cálculo de H e M ...
/***********************************************************************************************************/
//Enquanto o Valor desse campo for Zero, então eu vou sugerindo o cálculo baseado na Portaria ...
            if($campos[$i]['atrasos_hr_min'] == '0.00') {
                $atrasos_hr_min = $hs_min_atrasos;
            }else {
                $atrasos_hr_min = number_format($campos[$i]['atrasos_hr_min'], 2, ':', '');
            }
            echo $hs_min_atrasos;
        ?>
            <!--onfocus="cursor_dias_horas_trabalhados('<?=$i;?>')"-->
            <input type='text' name='txt_hs_min_atrasos[]' id='txt_hs_min_atrasos<?=$i;?>' value='<?=$atrasos_hr_min;?>' title='Digite as Hs / Min Atrasos' size='7' maxlength='6' onkeyup="verifica(this, 'hora', '', '', event);travar_link()" tabindex='<?=++$tab_index3;?>' class='textdisabled'>
        </td>
        <td>
            <input type='text' name='txt_dias_hs_min_dsr[]' id='txt_dias_hs_min_dsr<?=$i;?>' value='<?=$dias_hs_min_dsr;?>' title='Digite as Hs / Min DSR' size='7' maxlength='6' onkeyup="<?=$onkeyup;?>" tabindex='<?=++$tab_index3;?>' class='caixadetexto'>
            <img src='../../../../../imagem/visualizar_detalhes.png' border='0' title='Visualizar Relatório' alt='Visualizar Relatório' onclick="visualizar_relatorio('<?=$campos[$i]['id_funcionario'];?>')">
        </td>
        <td>
            <input type='text' name='txt_outros_rend_prop[]' id='txt_outros_rend_prop<?=$i;?>' value='<?=number_format($campos[$i]['outros_rend_prop'], 2, ',', '.');?>' title='Digite os Outros Rendimentos Proporcionais' size="10" maxlength="9" onkeyup="verifica(this, 'moeda_especial', '2', '', event);travar_link()" tabindex='<?=++$tab_index4;?>' class='caixadetexto'>
        </td>
        <td>
            <input type='text' name='txt_qtde_h_ext_fer_sab[]' id='txt_qtde_h_ext_fer_sab<?=$i;?>' value='<?=number_format($campos[$i]['hora_extra'], 2, ':', '.');?>' title='Digite a Qtde H. Extras (Feriado ao Sábado)' size="7" maxlength="6" onkeyup="verifica(this, 'hora', '', '', event);travar_link()" tabindex='<?=++$tab_index5;?>' class='textdisabled'>
        </td>
<!--Aqui é o Id do Holerith (Crédito) -->
        <input type='hidden' name='hdd_funcionario_vs_holerith[]' id='hdd_funcionario_vs_holerith<?=$i;?>' value='<?=$id_funcionario_vs_holerith;?>'>
    </tr>
<?
            $total_creditos+= $campos[$i]['valor_liquido_holerith'];
        }
?>
    <tr class='linhadestaque'>
        <td colspan='3' align='right'>
            <font color='yellow'>
                Total da Folha: 
            </font>
        </td>
        <td align='right'>
            R$ <?=number_format($total_creditos, 2, ',', '.');?>
        </td>
        <td colspan='7'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../gerenciar_folha.php'" class='botao'>
            <?
                /********************Margem com no máximo de até mais 5 dias********************/
/*Se a "Data de Holerith + 15 dias" for Menor do que a Data Atual, então o usuário já não pode mais fazer 
alterações na Folha de Pagamento ...*/
                $data_holerith_mais_quinze = data::adicionar_data_hora(data::datetodata($data_holerith, '/'), 15);
                $data_holerith_mais_quinze = data::datatodate($data_holerith_mais_quinze, '-');
            
                if($data_holerith_mais_quinze < date('Y-m-d')) {
                    $disabled_botao = 'disabled';
                    $class_botao    = 'textdisabled';
                }else {
                    $class_botao    = 'botao';
                }
                /*******************************************************************************/
            ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' tabindex='<?=$tab_index;?>' style='color:green' class='<?=$class_botao;?>' <?=$disabled_botao;?>>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<!--Controle de Tela p/ não perder a paginação e a ordenação dos Itens da Página-->
<input type='hidden' name='hdd_travar_link'>
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='cmb_empresa' value='<?=$cmb_empresa;?>'>
<!-- ******************************************** -->
</form>
</body>
</html>
<?
        if(!empty($valor)) {
?>
        <Script Language = 'Javascript'>
            alert('<?=$mensagem[$valor];?>')
        </Script>
<?
        }
    }else {
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<body>
<form name='form'>
<table width="950" border='0' cellspacing='0' cellpadding='0' align='center'>
	<tr class="atencao" align="center">
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size="-1" color="#FF0000">
				<b>Não há Folha cadastrado(s)
			</font>
		</td>
	</tr>
	<tr>
		<td></td>
	</tr>
	<tr align='center'>
		<td>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../gerenciar_folha.php'" class='botao'>
		</td>
	</tr>
</table>
</form>
</body>
</html>
<?
        if(!empty($valor)) {
?>
            <Script Language = 'JavaScript'>
                alert('<?=$mensagem[$valor];?>')
            </Script>
<?
        }
    }
}
?>