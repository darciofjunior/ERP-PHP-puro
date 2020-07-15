<?
require('../../../lib/segurancas.php');
//Procedimento normal de quando se carrega a Tela ...
$pop_up = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['pop_up'] : $_GET['pop_up'];
if(empty($pop_up))  require '../../../lib/menu/menu.php';//Só exibo esse Menu quando essa Tela não foi aberta como sendo Pop-UP ...

require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>BANCO DE HORA(S) INCLUIDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>DATA DE LANÇAMENTO INVÁLIDA.</font>";

if($passo == 1) {
/****************************************************************************************************/
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas e menos do cargo AUTONÔMO*/
    $sql = "SELECT DISTINCT (f.id_funcionario), f.id_funcionario_superior, f.nome, f.rg, f.codigo_barra, f.ddd_residencial, f.telefone_residencial, e.nomefantasia, c.cargo 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
            INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo AND c.`id_cargo` <> '82' 
            WHERE f.`nome` LIKE '%$txt_nome%' 
            AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
            AND f.`status` < '3' ORDER BY f.nome ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Funcionário(s) p/ Incluir Banco de Horas ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Funcionário(s) p/ Incluir Banco de Horas
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Código
        </td>
        <td>
            Nome
        </td>
        <td>
            Cargo
        </td>
        <td>
            Chefe
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
            $url = "incluir.php?passo=2&id_funcionario_loop=".$campos[$i]['id_funcionario'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10' onclick="window.location = '<?=$url;?>'">
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="window.location = '<?=$url;?>'" align='center'>
            <a href="<?=$url;?>" title='Visualizar Detalhes' class='link'>
                <?=$campos[$i]['codigo_barra'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
        <?
//Busca do Nome do Chefe do Funcionário ...
            $sql = "SELECT nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = ".$campos[$i]['id_funcionario_superior']." LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            echo $campos_funcionario[0]['nome'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
?>
<html>
<head>
<title>.:: Incluir Banco de Hora(s) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data de Ocorrência ...
    if(!data('form', 'txt_data_lancamento', '4000', 'OCORRÊNCIA')) {
        return false
    }
//Verifico se foi selecionado o Contabilizar ...
    if(document.getElementById('lbl_credito').checked == false && document.getElementById('lbl_debito').checked == false) {
        alert('SELECIONE O CONTABILIZAR !')
        document.getElementById('lbl_credito').focus()
        return false
    }
//Hora Inicial
    if(!texto('form', 'txt_hora_inicial', '1', '1234567890:', 'HORA INICIAL', '1')) {
        return false
    }
//Hora Final
    if(!texto('form', 'txt_hora_final', '1', '1234567890:', 'HORA FINAL', '1')) {
        return false
    }
/*******Aqui eu verifico se o Usuário não digitou valores incoerentes na Hora e no Minuto da Hora Inicial ...*******/
    var vetor_qtde_horas_inicial= document.form.txt_hora_inicial.value.split(':')
    var horas_inicial           = vetor_qtde_horas_inicial[0]
    var minutos_inicial         = vetor_qtde_horas_inicial[1]
    if(horas_inicial > 23) {
        alert('QTDE DE HORA(S) INICIAL(IS) INVÁLIDA !!!\n\nDIGITE HORA(S) INICIAL(IS) CORRETA ATÉ 23 !')
        document.form.txt_hora_inicial.focus()
        document.form.txt_hora_inicial.select()
        return false
    }
//Aqui eu verifico se os Minutos digitados pelo usuário estão Inválidos ...
    if(minutos_inicial > 59) {
        alert('QTDE DE MINUTO(S) INICIAL(IS) INVÁLIDO !!!\n\nDIGITE MINUTO(S) INICIAL(IS) CORRETO(S) ATÉ 59 !')
        document.form.txt_hora_inicial.focus()
        document.form.txt_hora_inicial.select()
        return false
    }
/*******Aqui eu verifico se o Usuário não digitou valores incoerentes na Hora e no Minuto da Hora Final ...*******/
    var vetor_qtde_horas_final  = document.form.txt_hora_final.value.split(':')
    var horas_final             = vetor_qtde_horas_final[0]
    var minutos_final           = vetor_qtde_horas_final[1]
/*
//Aqui eu verifico se as Horas digitadas pelo usuário estão Inválidas, mas somente quando for Crédito ...
    if(document.getElementById('lbl_credito').checked == true) {
        if(horas_final > 23) {
            alert('QTDE DE HORA(S) FINAL(IS) INVÁLIDA !!!\n\nDIGITE HORA(S) FINAL(IS) CORRETA ATÉ 23 !')
            document.form.txt_hora_final.focus()
            document.form.txt_hora_final.select()
            return false
        }
    }*/
//Aqui eu verifico se os Minutos digitados pelo usuário estão Inválidos ...
    if(minutos_final > 59) {
        alert('QTDE DE MINUTO(S) FINAL(IS) INVÁLIDO !!!\n\nDIGITE MINUTO(S) FINAL(IS) CORRETO(S) ATÉ 59 !')
        document.form.txt_hora_final.focus()
        document.form.txt_hora_final.select()
        return false
    }
/*******************************************************************************************************************/
/*Aqui eu verifico se todo o Horário Final Digitado é menor do que todo o Horário Inicial Digitado, 
o que não podemos hoje ...*/
    var horario_inicial = eval(horas_inicial + minutos_inicial)
    var horario_final   = eval(horas_final   + minutos_final)
    if(horario_final < horario_inicial) {
        alert('HORA(S) FINAL(IS) INVÁLIDA !!!\n\nHORA(S) FINAL(IS) MENOR DO QUE A HORA INICIAL(IS) !')
        document.form.txt_hora_final.focus()
        document.form.txt_hora_final.select()
        return false
    }
    //Aqui preparo p/ gravar no Banco de Dados ...
    document.form.txt_qtde_horas.disabled   = false
}

function calcular_qtde_horas() {
    //Quando o campo Hora Inicial e a Hora Final estiverem preenchidas, faço o Cálculo de Qtde Horas ...
    if(document.form.txt_hora_inicial.value != '' && document.form.txt_hora_final.value != '') {
        var vetor_qtde_horas_inicial= document.form.txt_hora_inicial.value.split(':')
        var horas_inicial           = eval(vetor_qtde_horas_inicial[0])
        var minutos_inicial         = eval(vetor_qtde_horas_inicial[1])

        var vetor_qtde_horas_final  = document.form.txt_hora_final.value.split(':')
        var horas_final             = eval(vetor_qtde_horas_final[0])
        var minutos_final           = eval(vetor_qtde_horas_final[1])

        var qtde_horas              = horas_final - horas_inicial
        if(minutos_final < minutos_inicial) {
            var qtde_minutos = (minutos_final - minutos_inicial) + 60
            qtde_horas-= 1//Aqui eu subtraio uma hora ...
        }else {
            var qtde_minutos = (minutos_final - minutos_inicial)
        }
        //Se foi selecionada a opção de Descontar Hora de Almoço ...
        if(document.getElementById('id_descontar_hora_almoco').checked == true) qtde_horas-= 1//Aqui eu subtraio uma hora ...
        
        //Tratamento p/ exibir os minutos com 2 dígitos ...
        if(qtde_minutos < 10) qtde_minutos = '0' + qtde_minutos
        if(qtde_horas >= 0 && qtde_horas < 10) qtde_horas = '0' + qtde_horas
        //Se foi selecionada a opção de Debitar então acrescento o Sinal Negativo na frente da hora se é que esse ainda não existe ...
        if(document.getElementById('lbl_debito').checked == true) {
            //Transformo a Hora em String de Propósito, senão eu não consigo usar as funções de String ...
            qtde_horas = String(qtde_horas)
            if(qtde_horas.substr(0, 1) != '-') qtde_horas = '-' + qtde_horas
        }
        document.form.txt_qtde_horas.value = qtde_horas + ':' + qtde_minutos
    }else {//Se não limpo a variável ...
        document.form.txt_qtde_horas.value = ''
    }
}
</Script>
</head>
<body onload='document.form.txt_hora_inicial.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit='return validar()'>
<!--********************************Controles de Tela********************************-->
<!--Coloquei esse nome p/ não dar conflito com a variável id_funcionario da Sessão-->
<input type='hidden' name='id_funcionario_loop' value='<?=$_GET['id_funcionario_loop'];?>'>
<!--Essa variável só será abastecida quando essa tela for aberta como sendo Pop-UP ...-->
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<!--*********************************************************************************-->
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Banco de Hora(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            Funcionário:
        </td>
        <td width='40%'>
        <?
            $sql = "SELECT nome, id_funcionario_superior 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '$_GET[id_funcionario_loop]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            echo $campos[0]['nome'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Lançamento:</b>
        </td>
        <td>
            <?
                //Isso só acontece quando que essa Tela foi aberta como sendo Pop-UP ...
                $data_lancamento = (!empty($_GET['data_ocorrencia'])) ? $_GET['data_ocorrencia'] : date('d/m/Y');
            ?>
            <input type="text" name="txt_data_lancamento" value="<?=$data_lancamento;?>" title="Digite a Data de Lançamento" onkeyup="verifica(this, 'data', '', '', event)" size="12" maxlength="10" class="caixadetexto">
            &nbsp;<img src="../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_lancamento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Contabilizar:</b>
        </td>
        <td>
            <input type='radio' name='opt_opcao' id='lbl_credito' value='Crédito' onclick='calcular_qtde_horas()'>
            <label for='lbl_credito'>
                Crédito
            </label>
            &nbsp;
            <input type='radio' name='opt_opcao' id='lbl_debito' value='Débito' onclick='calcular_qtde_horas()'>
            <label for='lbl_debito'>
                Débito
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Hora Inicial:</b>
        </td>
        <td>
            <input type='text' name='txt_hora_inicial' title='Digite a Hora Inicial' onkeyup="verifica(this, 'hora', '', '', event);calcular_qtde_horas()" onblur='calcular_qtde_horas()' size='8' maxlength='5' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Hora Final:</b>
        </td>
        <td>
            <input type='text' name='txt_hora_final' title='Digite a Hora Final' onkeyup="verifica(this, 'hora', '', '', event);calcular_qtde_horas()" onblur='calcular_qtde_horas()' size='8' maxlength='6' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Qtde de Horas:
        </td>
        <td>
            <input type='text' name='txt_qtde_horas' title='Qtde de Horas' size='8' maxlength='5' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_descontar_hora_almoco' id='id_descontar_hora_almoco' title='Descontar Hora de Almoço' onclick='calcular_qtde_horas()' class='checkbox'>
            <label for='id_descontar_hora_almoco'>
                Descontar Hora de Almoço
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' title='Digite a Observação' cols='51' rows='5' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
            //Só irá exbir esse Botão de Voltar quando essa tela for aberta de modo Normal e não como sendo Pop-UP ...
            if(empty($pop_up)) {
        ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
        <?
            }
        ?>
            <input type='button' name="cmd_limpar" value="Limpar" title="Limpar" class='botao' style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_hora_inicial.focus()">
            <input type='submit' name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    if($_POST['txt_data_lancamento'] == '00/00/0000') {//Caso a Data de Lançamento esteja vazia não permite incluir registro ...
        $valor = 3;
    }else {//Caso a Data esteja preenchida de forma correta, então registra a ocorrência ...
        $data_lancamento        = data::datatodate($_POST['txt_data_lancamento'], '-');
        $descontar_hora_almoco  = (!empty($_POST['chkt_descontar_hora_almoco'])) ? 'S' : 'N';
        
        //Inserindo na Base de Dados ...
        $sql = "INSERT INTO `bancos_horas` (`id_banco_hora`, `id_funcionario`, `data_lancamento`, `hora_inicial`, `hora_final`, `qtde_horas`, `descontar_hora_almoco`, `observacao`, `data_sys`) VALUES (NULL, '".$_POST['id_funcionario_loop']."', '$data_lancamento', '$_POST[txt_hora_inicial]', '$_POST[txt_hora_final]', '$_POST[txt_qtde_horas]', '$descontar_hora_almoco', '$_POST[txt_observacao]', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        $valor = 2;
    }
    
    if(!empty($_POST['pop_up'])) {//Significa que essa Tela foi aberta como sendo Pop-UP ...
?>
    <Script Language = 'JavaScript'>
        alert('BANCO DE HORA(S) INCLUIDO COM SUCESSO !')
        parent.html5Lightbox.finish()
    </Script>
<?
    }else {//Significa que essa Tela foi aberta como sendo Pop-UP ...
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?passo=2&id_funcionario_loop=<?=$_POST['id_funcionario_loop'];?>&valor=<?=$valor;?>'
    </Script>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Consultar Funcionário(s) p/ Incluir Banco de Horas ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_nome.focus()'>
<form name='form' method='post' action="<?=$GLOBALS['PHP_SELF'].'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Funcionário(s) p/ Incluir Banco de Horas
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome
        </td>
        <td>
            <input type="text" name="txt_nome" title="Digite o Nome" size="45" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_nome.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>