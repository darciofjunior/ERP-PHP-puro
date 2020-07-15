<?
require('../../../../lib/segurancas.php');
$pop_up = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];
/*Significa se essa Tela, foi acessada de algum outro lugar, fora do Menu, então eu não 
verifico se o usuário tem essa permissão na Sessão e não trago o Menu ...*/
if(empty($pop_up)) {
    require('../../../../lib/menu/menu.php');
    segurancas::geral($PHP_SELF, '../../../../');
}
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$valor_dolar_dia = genericas::moeda_dia('dolar');
?>
<html>
<head>
<title>.:: Relatório de Comissões ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
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
//Controle com as Datas, porque a Data Final não pode ser menor do que a Data Inicial ...
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6, 4) + data_inicial.substr(3, 2) + data_inicial.substr(0, 2)
    data_final          = data_final.substr(6, 4) + data_final.substr(3, 2) + data_final.substr(0, 2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 5 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > (365 * 5)) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A CINCO ANOS !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
//Representante ...
    if(document.form.cmb_representante.value == '') {
        //Pergunto se deseja gerar comissão p/ todos os representantes da Empresa ...
        var resposta = confirm('DESEJA GERAR COMISSÃO P/ TODO(S) O(S) REPRESENTANTE(S) ?\n\nATENÇÃO: ISTO IMPLICARÁ NA VELOCIDADE DO SISTEMA E NOS CÁLCULOS FINANCEIROS ')
        if(resposta == false) return false//Não deseja gerar Comissão p/ todos Representantes ...
    }
//Projeção de Faturamento ...
    if(typeof(document.form.txt_projecao_faturamento) == 'object') {
        if(!texto('form', 'txt_projecao_faturamento', '1', '0123456789,.', 'PROJEÇÃO DE FATURAMENTO', '1')) {
            return false
        }
        var projecao_faturamento = eval(strtofloat(document.form.txt_projecao_faturamento.value))
        if(projecao_faturamento == 0) {
            alert('PROJEÇÃO DE FATURAMENTO INVÁLIDO !!!\n\nPROJEÇÃO DE FATURAMENTO COM VALOR IGUAL ZERO !')
            document.form.txt_projecao_faturamento.focus()
            document.form.txt_projecao_faturamento.select()
            return false
        }else {
            var id_funcionario                  = eval('<?=$_SESSION['id_funcionario'];?>')
            /*Funcionários que podem rodar "simular" o Relatório de Comissão mesmo sem o Faturamento ter sido 
            fechado por Completo, não tendo fechado o Período do mês - 62 Roberto, 98 Darcio, 136 Nishimura ...*/
            var id_funcionarios_com_permissao   = [62, 98, 136]

            if(id_funcionarios_com_permissao.indexOf(id_funcionario) == - 1) {
                alert('USUÁRIO SEM PERMISSÃO PARA RODAR O RELATÓRIO DE COMISSÃO !!!')
                return false
            }
        }
    }
    document.form.target = 'ifr_relatorio_comissoes'
}

function div_valor_faturamento() {
    if(document.form.txt_data_inicial.value.length == 10 && document.form.txt_data_final.value.length == 10) {//Significa que o Usuário preencheu por completo toda a Data Final ...
        //Controle com as Datas, porque a Data Final não pode ser menor do que a Data Inicial ...
        var data_inicial    = document.form.txt_data_inicial.value
        var data_final      = document.form.txt_data_final.value
        data_inicial        = data_inicial.substr(6, 4) + data_inicial.substr(3, 2) + data_inicial.substr(0, 2)
        data_final          = data_final.substr(6, 4) + data_final.substr(3, 2) + data_final.substr(0, 2)
        data_inicial        = eval(data_inicial)
        data_final          = eval(data_final)
        
        if(data_final < data_inicial) {
            alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
            document.form.txt_data_final.focus()
            document.form.txt_data_final.select()
            ajax('div_valor_faturamento.php?irregularidade=S', 'div_valor_faturamento')
        }else {
            ajax('div_valor_faturamento.php?txt_data_inicial='+document.form.txt_data_inicial.value+'&txt_data_final='+document.form.txt_data_final.value, 'div_valor_faturamento')
        }
    }else {
        ajax('div_valor_faturamento.php?irregularidade=S', 'div_valor_faturamento')
    }
}
</Script>
</head>
<body onload='div_valor_faturamento()'>
<form name='form' action='relatorio_pdf/relatorio.php' method='post' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1' onclick='div_valor_faturamento()'>
<!--Se essa opção estiver marcada, então eu tenho que manter a combo desabilitada
Também significa que essa Tela, foi acessada de algum outro lugar, fora do Menu-->
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <?
                if($cmb_empresa == 1) {
                    $empresa = 'ALBAFER';
                }else if($cmb_empresa == 2) {
                    $empresa = 'TOOL MASTER';
                }else if($cmb_empresa == 4) {
                    $empresa = 'GRUPO';
                }else {
                    $empresa = 'TODAS EMPRESAS';
                }
            ?>
            Relat&oacute;rio de Comiss&otilde;es - 
            <font color='yellow'>
                <?=$empresa;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <?
                //Sugestão apenas na 1ª vez em que o sistema acabar de carregar essa Tela ...
                if(empty($_POST['txt_data_inicial'])) {
                    $datas          = genericas::retornar_data_relatorio(1);
                    $data_inicial   = $datas['data_inicial'];
                    $data_final     = $datas['data_final'];
                }else {
                    $data_inicial   = data::datatodate($_POST['txt_data_inicial'], '-');
                    $data_final     = data::datatodate($_POST['txt_data_final'], '-');
                }
            ?>
            <p/>Data Inicial:
            <!--Eu preciso colocar um "Timeout" ao chamar a função "div_valor_faturamento()" porque para o 
            JavaScript entender os dados que foram retornados do Ajax aqui nesta tela se gasta um tempo 
            de milésimos de segundos ??? ...-->
            <input type='text' name='txt_data_inicial' value='<?=$data_inicial;?>' title='Digite a Data Inicial' onkeyup="verifica(this, 'data', '', '', event);div_valor_faturamento()" size='12' maxlength='10' class='caixadetexto'/>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1&caixa_auxiliar=passo', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"/>
            Data Final:
            <input type='text' name='txt_data_final' value='<?=$data_final;?>' title='Digite a Data Final' onkeyup="verifica(this, 'data', '', '', event);div_valor_faturamento()" size='12' maxlength='10' class='caixadetexto'/>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1&caixa_auxiliar=passo', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"/>
            Relatório por:
            <?
                //Significa que essa Tela, foi acessada de algum outro lugar, fora do Menu ...
                if($pop_up == 1) {
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                }else {
                    $class      = 'combo';
                    $disabled   = '';
                }
            ?> 
            <select name='cmb_representante' title='Selecione o Representante' class='<?=$class;?>' <?=$disabled;?>>
            <?
                $sql = "SELECT `id_representante`, CONCAT(`nome_fantasia`, ' / ', `zona_atuacao`) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY `nome_fantasia` ";
                echo combos::combo($sql, $cmb_representante);
            ?>
            </select>
            <?
                //Esse checkbox só aparecerá para os seguintes usuários ...
                $id_funcionarios_com_permissao   = array(62, 98, 106, 111);//Roberto 62, Dárcio 98 porque programa, Patrícia 106, Graziella 111 ...
                
                if(in_array($_SESSION['id_funcionario'], $id_funcionarios_com_permissao)) {
            ?>
            &nbsp;
            <input type='checkbox' name='chkt_gerar_comissao_antes_prazo' id='chkt_gerar_comissao_antes_prazo' value='S' checkbox>
            <label for='chkt_gerar_comissao_antes_prazo'>
                Gerar Comissão antes do Prazo (Usado em dezembro)
            </label>
            <?
                }
            ?>
            <div name='div_valor_faturamento' id='div_valor_faturamento' style='height:25px; width:500px; font:bold 16px verdana'></div>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <hr/>
            <iframe name='ifr_relatorio_comissoes' width='100%' height='450' frameborder='0'></iframe>
        </td>
    </tr>
</table>
</form>
</body>
</html>