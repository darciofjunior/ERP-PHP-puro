<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

//Carrega a Listagem dos Logins
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' || !empty($pagina)) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $cmb_empresa        = $_POST['cmb_empresa'];
        $cmb_funcionario    = $_POST['cmb_funcionario'];
        $cmb_origem         = $_POST['cmb_origem'];
        $txt_endereco       = $_POST['txt_endereco'];
        $txt_data_inicial   = $_POST['txt_data_inicial'];
        $txt_data_final     = $_POST['txt_data_final'];
    }else {
        $cmb_empresa        = $_GET['cmb_empresa'];
        $cmb_funcionario    = $_GET['cmb_funcionario'];
        $cmb_origem         = $_GET['cmb_origem'];
        $txt_endereco       = $_GET['txt_endereco'];
        $txt_data_inicial   = $_GET['txt_data_inicial'];
        $txt_data_final     = $_GET['txt_data_final'];
    }   
    if($cmb_origem == '') $cmb_origem = '%';
/******************************************Intervalo******************************************/
//Aqui eu verifico a partir de qual Intervalo que eu vou fazer a busca de Dados ...
/*********************************************************************************************/
//Intervalo Inicial ...
    $dia_inicial = substr($txt_data_inicial, 0, 2);
    $mes_inicial = substr($txt_data_inicial, 3, 2);
    $ano_inicial = substr($txt_data_inicial, 6, 4);
//Intervalo Final ...
    $dia_final = substr($txt_data_final, 0, 2);
    $mes_final = substr($txt_data_final, 3, 2);
//Através do Ano, eu já sei em qual Base de Dados que eu vou me conectar ...
    $database = 'logs_'.$ano_inicial;
    /*****************************************************************************************/
    //Nova Conexão com o Banco de Dados de Logs ...
    $host = mysql_connect($_SERVER['SERVER_ADDR'], 'root', 'w1l50n');
    mysql_select_db($database, $host);
    unset($sql);
    /*****************************************************************************************/
    $vetor_meses = array('1_janeiro', '2_fevereiro', '3_março', '4_abril', '5_maio', '6_junho', '7_julho', '8_agosto', '9_setembro', '10_outubro', '11_novembro', '12_dezembro');
    for($i = ($mes_inicial - 1); $i < $mes_final; $i++) {
        if(!empty($sql)) {//Só irá entrar dentro desse laço a partir da Segunda Vez ...
            $union = " UNION ";
        }
//Quando 
        if($i == ($mes_inicial - 1)) {//Só quando for o primeiro Registro ...
            $where = " WHERE DAY(`data_ocorrencia`) >= '$dia_inicial' AND `id_funcionario` = '$cmb_funcionario' AND `origem` LIKE '$cmb_origem' AND `url` LIKE '%$txt_endereco%' ";
        }else if(($i + 1) == $mes_final) {//Só quando for o último Registro ...
            $where = " WHERE DAY(`data_ocorrencia`) <= '$dia_final' AND `id_funcionario` = '$cmb_funcionario' AND `origem` LIKE '$cmb_origem' AND `url` LIKE '%$txt_endereco%' ";
        }else {//Durante os outros Meses é Vazio ...
            $where = " WHERE `id_funcionario` = '$cmb_funcionario' AND `origem` LIKE '$cmb_origem' AND `url` LIKE '%$txt_endereco%' ";
        }
//Aqui eu estou montando a Estrutura do SQL no período de Meses solicitados pelo usuário ...
        $sql.= $union."SELECT * 
                        FROM $database.`logs_acessos_telas_".$vetor_meses[(int)$i].'`'.$where;
    }
    $sql.= " ORDER BY data_ocorrencia DESC ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
//Não encontrou logs registrados desse funcionário, nos intervalos de Datas Especificados
    if($linhas == 0) {
        $valor = 1;
//Encontrou sendo assim, faz uma listagem ...
    }else {
//Busca do Nome do Funcionário e Empresa ...
        $sql = "SELECT f.nome, e.nomefantasia 
                FROM `funcionarios` f 
                INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                WHERE f.`id_funcionario` = '$cmb_funcionario' LIMIT 1 ";
        $campos_func    = bancos::sql($sql);
        $nome           = $campos_func[0]['nome'];
        $nomefantasia   = $campos_func[0]['nomefantasia'];

        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_funcionario` = '$cmb_funcionario' LIMIT 1 ";
        $campos_login = bancos::sql($sql);
        if(count($campos_login) == 1) $login = $campos_login[0]['login'];
?>
<html>
<head>
<title>.:: Consultar Log(s) de Acesso de Tela ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar Log(s) de Acesso de Tela
            <font color='yellow'>
                <?=$nome;?>
            </font>
             - 
            <font color='yellow'>
                <?=$nomefantasia;?>
            </font>
            <?
                if(!empty($login)) {
                    echo '-';
            ?>
            <font color='yellow'>
                <?=$login;?>
            </font>
            <?
                }
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Identificação
        </td>
        <td>
            Origem
        </td>
        <td>
            Endereço
        </td>
        <td> 
            IP
        </td>
        <td> 
            Data
        </td>
        <td>
            Hora
        </td>
    </tr>
<?
        $vetor = array('', 'ORÇAMENTO', 'PEDIDO', 'PENDÊNCIA');
        for($i = 0;$i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['identificacao'];?>
        </td>
        <td>
            <?=$vetor[$campos[$i]['origem']];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['url'];?>
        </td>
        <td> 
            <?=$campos[$i]['ip'];?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_ocorrencia'], 0, 10), '/');?>
        </td>
        <td>
            <?=substr($campos[$i]['data_ocorrencia'], 11, 5);?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?
        exit;
    }
}
?>
<html>
<head>
<title>.:: Consultar Log(s) de Acesso de Tela ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Empresa
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
        return false
    }
//Funcionário
    if(!combo('form', 'cmb_funcionario', '', 'SELECIONE UM FUNCIONÁRIO !')) {
        return false
    }
//Comparação com as Datas ...
    var data_inicial = document.form.txt_data_inicial.value
    var data_final = document.form.txt_data_final.value

    data_inicial = data_inicial.substr(6,4) + data_inicial.substr(3,2) + data_inicial.substr(0,2)
    data_final = data_final.substr(6,4) + data_final.substr(3,2) + data_final.substr(0,2)
    data_inicial = eval(data_inicial)
    data_final = eval(data_final)
//A Data Final jamais pode ser menor do que a Data Inicial ...
    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    ano_inicial = eval(document.form.txt_data_inicial.value.substr(6,4))
    ano_final = eval(document.form.txt_data_final.value.substr(6,4))
//Nunca que os Anos das Datas poderão ser diferentes ...
    if(ano_inicial != ano_final) {
        alert('DATA FINAL INVÁLIDA !!! O ANO DA DATA FINAL É DIFERENTE DO ANO DA DATA INICIAL !\nO SISTEMA SÓ PERMITE FAZER FILTRAGEM DE LOG(S) QUE SEJAM DO MESMO ANO !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
}
</Script>
</head>
<body onload='document.form.cmb_empresa.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Consultar Log(s) de Acesso de Tela
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <b>Empresa:</b>
            <select name='cmb_empresa' title='Selecione a Empresa' onchange='document.form.submit()' class='combo'>
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql, $_POST['cmb_empresa']);
            ?>
            </select>
            &nbsp;&nbsp;&nbsp;
            <b>Funcion&aacute;rio:</b>
            <select name='cmb_funcionario' title='Selecione o Funcionário' class='combo'>
            <?
                //Traz todos os funcionários que ainda trabalham da Empresa selecionada ...
                $sql = "SELECT id_funcionario, nome 
                        FROM `funcionarios` 
                        WHERE `id_empresa` = '$_POST[cmb_empresa]' 
                        AND `status` < '3' 
                        ORDER BY nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            Origem:
            <select name='cmb_origem' title='Selecione a Origem' class='combo'>
                <option value='' style="color:red">SELECIONE</option>
                <option value='1'>ORÇAMENTO</option>
                <option value='2'>PEDIDO</option>
                <option value='3'>PENDÊNCIA</option>
            </select>
            &nbsp;&nbsp;&nbsp;
            Endereço:
            <input type='text' name='txt_endereco' title='Endereço' size='45' maxlength='40' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <b>Data Inicial:</b>
            <input type='text' name='txt_data_inicial' value='<?=date('01/m/Y');?>' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp; <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; 
            <b>Data Final:</b>
            <input type='text' name='txt_data_final' value="<?=date('d/m/Y');?>" size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp; <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_empresa.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>