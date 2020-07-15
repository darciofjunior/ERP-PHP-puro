<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/rh/hora_extra/opcoes_relacao_func_hora_extra.php', '../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE NENHUM FUNCIONÁRIO CADASTRADO NESSA EMPRESA.</font>";
$mensagem[2] = "<font class='confirmacao'>HORA(S) EXTRA(S) INCLUÍDA(S) / ALTERADA(S) COM SUCESSO.</font>";

if($passo == 1) {
//Antes de entrar no loop, eu deleto todos os Registros de funcionários na data de Hora Extra específica ...
    $sql = "DELETE FROM `funcionarios_horas_extras` WHERE `data_hora_extra` = '".$_POST['cmb_data_hora_extra']."' " ;
    bancos::sql($sql);
//Data Atual ...
    $data_sys = date('Y-m-d H:i:s');
//Disparando o Loop ...
    for($i = 0; $i < count($_POST['chkt_funcionario']); $i++) {
//Aqui eu verifico se o campo Horário está preenchido ...
/*******************************************************************************************/
//Raciocínio p/ calcular a Qtde de Horas Trabalhadas do Usuário ...
        $hora_inicial = strtok($_POST['txt_hora_inicial'][$i], '.');//Pega até o ponto
        $minuto_inicial = strchr($_POST['txt_hora_inicial'][$i], '.');//Pegar a partir do pt
        $minuto_inicial = substr($minuto_inicial, 1, strlen($minuto_inicial));

        $hora_final = strtok($_POST['txt_hora_final'][$i], '.');//Pega até o ponto
        $minuto_final = strchr($_POST['txt_hora_final'][$i], '.');//Pegar a partir do ponto
        $minuto_final = substr($minuto_final, 1, strlen($minuto_final));
//Se o Minuto Final for menor do que o Minuto Inicial ...
        if($minuto_final < $minuto_inicial) {
            $minuto_final+= 60;
            $descontar_hora = 1;
        }else {
            $descontar_hora = 0;
        }
        $diferenca_minutos  = $minuto_final - $minuto_inicial;
        $diferenca_horas    = ($hora_final - $hora_inicial) - $descontar_hora;
        $qtde_horas         = $diferenca_horas.'.'.$diferenca_minutos;
//Controle com o VT ...
        if(is_array($_POST['chkt_pagar_vt'])) {
            if(in_array($_POST['chkt_funcionario'][$i], $_POST['chkt_pagar_vt'])) {
                $pagar_vt = 'S';
            }else {
                $pagar_vt = 'N';
            }
        }else {
            $pagar_vt = 'N';
        }
//Controle com o VR ...
        if(is_array($_POST['chkt_pagar_vr'])) {
            if(in_array($_POST['chkt_funcionario'][$i], $_POST['chkt_pagar_vr'])) {
                $pagar_vr = 'S';
            }else {
                $pagar_vr = 'N';
            }
        }else {
            $pagar_vr = 'N';
        }
//Controle com o VR ...
        if(is_array($_POST['chkt_descontar_hora_almoco'])) {
            if(in_array($_POST['chkt_funcionario'][$i], $_POST['chkt_descontar_hora_almoco'])) {
                $descontar_hora_almoco = 'S';
            }else {
                $descontar_hora_almoco = 'N';
            }
        }else {
            $descontar_hora_almoco = 'N';
        }
        $insert_extendido.= " (NULL, '".$_POST['chkt_funcionario'][$i]."', '".$_POST['cmb_data_hora_extra']."', '".$_POST['txt_hora_inicial'][$i]."', '".$_POST['txt_hora_final'][$i]."', '$qtde_horas', '$pagar_vt', '$pagar_vr', '$descontar_hora_almoco', '$data_sys'), ";
    }
/*Se esta variável estiver carregada, significa que existe algum funcionário que precisa ser Inserido na
Base de Dados ...*/
    if(!empty($insert_extendido)) {
        $insert_extendido = substr($insert_extendido, 0, strlen($insert_extendido) - 2);
//Gravando as Horas Extras dos Funcionários ...
        $sql = "INSERT INTO `funcionarios_horas_extras` (`id_funcionario_hora_extra`, `id_funcionario`, `data_hora_extra`, `hora_inicial`, `hora_final`, `qtde_horas`, `pagar_vt`, `pagar_vr`, `descontar_hora_almoco`, `data_sys`) VALUES 
                $insert_extendido ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'relacao_func_hora_extra.php?cmb_data_hora_extra=<?=$_POST['cmb_data_hora_extra'];?>&valor=2'
    </Script>
<?
}else {
/*Só fará essa verificação quando eu estiver incluindo a Hora Extra, do contrário 
essa própria Tela, vira alterar*/
    if($incluir_data_hora_extra == 1) {
//Verifico se já foi incluida alguma Hora Extra na Data especificada pelo usuário ...
        $sql = "SELECT `id_funcionario_hora_extra` 
                FROM `funcionarios_horas_extras` 
                WHERE `data_hora_extra` = '".$_GET['cmb_data_hora_extra']."' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 1) {//Significa que já foi lançada hora Extra nessa Data, sendo assim ...
?>
            <Script Language = 'Javascript'>
                window.location = 'opcoes_relacao_func_hora_extra.php'
            </Script>
<?
        }
    }
/****************************************************************************************************/
/*Listagem de Todos os Funcionários que ainda estão trabalhando*/
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
    $sql = "SELECT c.`cargo`, d.`id_departamento`, d.`departamento`, e.`nomefantasia`, 
            f.`id_funcionario`, f.`nome` 
            FROM `funcionarios` f 
            INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
            INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento 
            INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
            WHERE f.`status` < '3' 
            AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY d.`departamento`, f.`nome` ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Não encontrou nenhum funcionário com essa marcação ...
?>
        <Script Language = 'Javascript'>
            window.location = 'opcoes_relacao_func_hora_extra.php?cmb_data_holerith=<?=$cmb_data_holerith;?>&valor=1'
        </Script>
<?
        exit;
    }
?>
<html>
<head>
<title>.:: Relação Func. Hora Extra ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Tratamento com os objetos antes de gravar BD ...
    var elementos = document.form.elements
//Aqui eu verifico se existe pelo menos 1 Hora Extra selecionada p/ gravar no BD ...
    var selecionado = 0
//Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['chkt_funcionario[]'][0]) == 'undefined') {
        if(elementos['chkt_funcionario[]'].checked == true) selecionado = 1;
    }else {
        for(var i = 0; i < elementos.length; i++) {
            if(elementos['chkt_funcionario[]'][i] == '[object HTMLInputElement]' || elementos['chkt_funcionario[]'][i] == '[object]') {
                if(elementos['chkt_funcionario[]'][i].checked == true) {
                    selecionado = 1;
                    i = elementos.length//Para sair fora do Loop ...
                }
            }
        }
    }
//Se não tiver nenhum funcionário selecionado, então retorno uma Mensagem p/ o usuário ...
    if(selecionado == 0) {
        alert('SELECIONE PELO MENOS UM FUNCIONÁRIO P/ DIGITAR A HORA EXTRA !')
        return false
    }
//Todas as linhas que estiverem marcadas, então eu forço o usuário a digitar a linha o horário na linha ...
//Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['chkt_funcionario[]'][0]) == 'undefined') {
        if(elementos['chkt_funcionario[]'].checked == true) {
//Hora Inicial ...
            if(elementos['txt_hora_inicial[]'].value == '') {
                alert('DIGITE A HORA INICIAL !')
                elementos['txt_hora_inicial[]'].focus()
                return false
            }
//Hora Final ...
            if(elementos['txt_hora_final[]'].value == '') {
                alert('DIGITE A HORA FINAL !')
                elementos['txt_hora_inicial[]'].focus()
                return false
            }
        }
//Mais de 1 linha ...
    }else {
        for(var i = 0; i < elementos.length; i++) {
            if(elementos['chkt_funcionario[]'][i] == '[object HTMLInputElement]' || elementos['chkt_funcionario[]'][i] == '[object]') {
                if(elementos['chkt_funcionario[]'][i].checked == true) {
//Hora Inicial ...
                    if(elementos['txt_hora_inicial[]'][i].value == '') {
                        alert('DIGITE A HORA INICIAL !')
                        elementos['txt_hora_inicial[]'][i].focus()
                        return false
                    }
//Hora Final ...
                    if(elementos['txt_hora_final[]'][i].value == '') {
                        alert('DIGITE A HORA FINAL !')
                        elementos['txt_hora_inicial[]'][i].focus()
                        return false
                    }
                }
            }
        }
    }
/*Aqui eu verifico se existe pelo menos 1 Hora Irregular que foi lançada, exemplos: Hora Inicial maior do que 
a Hora Final ...*/
    hora_irregular = 0
    if(typeof(elementos['txt_hora_inicial[]'][0]) == 'undefined') {
        if(elementos['txt_hora_inicial[]'] == '[object HTMLInputElement]' || elementos['txt_hora_inicial[]'] == '[object]') {
            hora_inicial    = elementos['txt_hora_inicial[]'].value
            hora_final      = elementos['txt_hora_final[]'].value
        }
//Mais de 1 linha ...
    }else {
        for (i = 0; i < elementos.length; i++) {
            if(elementos['txt_hora_inicial[]'][i] == '[object HTMLInputElement]' || elementos['txt_hora_inicial[]'][i] == '[object]') {
//Consistência somente nos campos de Hora que estiverem preenchidos ...
                if(elementos['txt_hora_inicial[]'][i].value != '') {
                    hora_inicial = eval(strtofloat(elementos['txt_hora_inicial[]'][i].value.replace(':', '.')))
                    hora_final = eval(strtofloat(elementos['txt_hora_final[]'][i].value.replace(':', '.')))
//Se a Hora Final for menor do que a Hora Inicial ...
                    if(hora_final < hora_inicial) {
                        alert('HORA FINAL INVÁLIDA !')
                        elementos['txt_hora_final[]'][i].focus()
                        //elementos['txt_hora_final[]'][i].select()
                        return false
                    }
                }
            }
        }
    }
//Prepara a Tela p/ poder gravar no BD ...
//Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['txt_hora_inicial[]'][0]) == 'undefined') {
        if(elementos['txt_hora_inicial[]'] == '[object HTMLInputElement]' || elementos['txt_hora_inicial[]'] == '[object]') {
            elementos['txt_hora_inicial[]'].value = elementos['txt_hora_inicial[]'].value.replace(':', '.')
            elementos['txt_hora_final[]'].value = elementos['txt_hora_final[]'].value.replace(':', '.')
        }
//Mais de 1 linha ...
    }else {
        for (i = 0; i < elementos.length; i++) {
            if(elementos['txt_hora_inicial[]'][i] == '[object HTMLInputElement]' || elementos['txt_hora_inicial[]'][i] == '[object]') {
                elementos['txt_hora_inicial[]'][i].value = elementos['txt_hora_inicial[]'][i].value.replace(':', '.')
                elementos['txt_hora_final[]'][i].value = elementos['txt_hora_final[]'][i].value.replace(':', '.')
/*
//Macete com os Checkboxs ...
                if(elementos['chkt_pagar_vr[]'][i].disabled == false && elementos['chkt_pagar_vr[]'][i].checked == false) {
                        elementos['chkt_pagar_vr[]'][i].value = 'N'
                        elementos['chkt_pagar_vr[]'][i].disabled = true
                }
//Macete com os Checkboxs ...
                if(elementos['chkt_pagar_vt[]'][i].disabled == false && elementos['chkt_pagar_vt[]'][i].checked == false) {
                        elementos['chkt_pagar_vt[]'][i].value = 'N'
                        elementos['chkt_pagar_vt[]'][i].disabled = true
                }*/
            }
        }
    }
}

function copiar_todos_horarios() {
    var elementos = document.form.elements
    if(typeof(elementos['chkt_funcionario[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_funcionario[]'].length)
    }
//Chama a função de acordo com a qtde de funcionários ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_pagar_vt'+i).disabled == false) {
            document.getElementById('txt_hora_inicial'+i).value = document.form.txt_hora_inicial.value
            document.getElementById('txt_hora_final'+i).value   = document.form.txt_hora_final.value
        }
    }
}

function selecionar_todos_pagar_vt() {
//Se o Checkbox Principal estiver checado ...
    if(document.form.chkt_pagar_principal_vt.checked == true) {//Marca todos os checkboxs de VT
        var habilitar = true
    }else {//Desmarca todos os checkboxs de VT ...
        var habilitar = false
    }
    var elementos = document.form.elements
    if(typeof(elementos['chkt_funcionario[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_funcionario[]'].length)
    }
//Chama a função de acordo com a qtde de funcionários ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_pagar_vt'+i).disabled == false) document.getElementById('chkt_pagar_vt'+i).checked = habilitar
    }
}

function selecionar_todos_pagar_vr() {
//Se o Checkbox Principal estiver checado ...
    if(document.form.chkt_pagar_principal_vr.checked == true) {//Marca todos os checkboxs de VT
        var habilitar = true
    }else {//Desmarca todos os checkboxs de VT ...
        var habilitar = false
    }
    var elementos = document.form.elements
    if(typeof(elementos['chkt_funcionario[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_funcionario[]'].length)
    }
//Chama a função de acordo com a qtde de funcionários ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_pagar_vr'+i).disabled == false) {
            document.getElementById('chkt_pagar_vr'+i).checked              = habilitar
            document.getElementById('chkt_descontar_hora_almoco'+i).checked = habilitar
        }
    }
}

function selecionar_departamento(indice_departamento, indice_funcionario, id_departamento) {
    var elementos = document.form.elements
    var procedimento = ''
//Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['chkt_departamento[]'][0]) == 'undefined') {
        if(elementos['chkt_departamento[]'] == '[object HTMLInputElement]' || elementos['chkt_departamento[]'] == '[object]') {
/*Se o checkbox Principal do Departamento estiver selecionado, terá que selecionar todos os departamentos
daquele grupo ...*/
            if(elementos['chkt_departamento[]'].checked == true) {
                elementos['chkt_funcionario[]'].checked = true
/*Se o checkbox Principal do Departamento estiver desmarcado, irá desmarcar todos os departamentos
daquele grupo ...*/
            }else {
                elementos['chkt_funcionario[]'].checked = false
            }
        }
//Mais de 1 linha ...
    }else {
        if(elementos['chkt_departamento[]'][indice_departamento] == '[object HTMLInputElement]' || elementos['chkt_departamento[]'][indice_departamento] == '[object]') {
/*Se o checkbox Principal do Departamento estiver selecionado, terá que selecionar todos os departamentos
daquele grupo ...*/
            if(elementos['chkt_departamento[]'][indice_departamento].checked == true) {
                procedimento = true
//Layout de Habilitado
                cor_fonte = 'Brown'
                cor_fundo = '#FFFFFF'
                situacao = false
/*Se o checkbox Principal do Departamento estiver desmarcado, irá desmarcar todos os departamentos
daquele grupo ...*/
            }else {
                procedimento = false
//Layout de Desabilitado
                cor_fonte = 'gray'
                cor_fundo = '#FFFFE1'
                situacao = true
            }

            for(var i = indice_funcionario; i < elementos.length; i++) {
/*Enquanto o Departamento do Loop for igual ao Departamento Corrente do Hidden que eu passei por parâmetro, 
então eu vou marcando os funcionários ...*/
                if(elementos['hdd_departamento[]'][i] == '[object HTMLInputElement]' || elementos['hdd_departamento[]'][i] == '[object]') {
                    if(elementos['hdd_departamento[]'][i].value == id_departamento) {
                        elementos['chkt_funcionario[]'][i].checked              = procedimento
                        elementos['txt_hora_inicial[]'][i].style.color          = cor_fonte
                        elementos['txt_hora_inicial[]'][i].style.background     = cor_fundo
                        elementos['txt_hora_final[]'][i].style.color            = cor_fonte
                        elementos['txt_hora_final[]'][i].style.background       = cor_fundo
                        
                        elementos['txt_hora_inicial[]'][i].disabled             = situacao
                        elementos['txt_hora_final[]'][i].disabled               = situacao
                        elementos['chkt_pagar_vt[]'][i].disabled                = situacao
                        elementos['chkt_pagar_vr[]'][i].disabled                = situacao
                        elementos['chkt_descontar_hora_almoco[]'][i].disabled   = situacao
                    }
                }
            }
        }
    }
}

function selecionar_funcionario(indice) {
    if(document.getElementById('chkt_funcionario'+indice).checked == true) {//Se checado, então desmarca ...
        document.getElementById('chkt_funcionario'+indice).checked   = false
//Layout de Desabilitado ...
        document.getElementById('txt_hora_inicial'+indice).className = 'textdisabled'
        document.getElementById('txt_hora_final'+indice).className   = 'textdisabled'
//Desabilitando os Objetos ...
        document.getElementById('txt_hora_inicial'+indice).disabled             = true
        document.getElementById('txt_hora_final'+indice).disabled               = true
        document.getElementById('chkt_pagar_vt'+indice).disabled                = true
        document.getElementById('chkt_pagar_vr'+indice).disabled                = true
        document.getElementById('chkt_descontar_hora_almoco'+indice).disabled   = true
    }else {//Se não estiver checado então eu marco ...
        document.getElementById('chkt_funcionario'+indice).checked   = true
//Layout de Habilitado ...
        document.getElementById('txt_hora_inicial'+indice).className = 'caixadetexto'
        document.getElementById('txt_hora_final'+indice).className   = 'caixadetexto'
//Habilitando os Objetos ...
        document.getElementById('txt_hora_inicial'+indice).disabled             = false
        document.getElementById('txt_hora_final'+indice).disabled               = false
        document.getElementById('chkt_pagar_vt'+indice).disabled                = false
        document.getElementById('chkt_pagar_vr'+indice).disabled                = false
        document.getElementById('chkt_descontar_hora_almoco'+indice).disabled   = false
    }
}

function selecionar_vr(indice) {
    if(document.getElementById('chkt_pagar_vr'+indice).checked) {
        document.getElementById('chkt_descontar_hora_almoco'+indice).checked    = true
    }else {
        document.getElementById('chkt_descontar_hora_almoco'+indice).checked    = false
    }
}

function focos(objeto) {
    objeto.disabled = false
    objeto.focus()
    return false
}
</Script>
</head>
<body onload='document.form.txt_hora_inicial.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Relação Func. Hora Extra - 
            <font color='yellow'>
                Data Atual: 
            </font>
            <?=data::datetodata($_GET['cmb_data_hora_extra'], '/');?>

            <input type='hidden' name='cmb_data_hora_extra' value='<?=$_GET['cmb_data_hora_extra'];?>'>
            - 
            <br/>
            <font color='yellow'>
                Hora Inicial: 
            </font>
            <input type='text' name='txt_hora_inicial' title='Digite a Hora Inicial' onkeyup="verifica(this, 'hora', '', '', event)" maxlength='5' size='6' class='caixadetexto'>
            &nbsp;
            <font color='yellow'>
                Hora Final: 
            </font>
            <input type='text' name='txt_hora_final' title='Digite a Hora Final' onkeyup="verifica(this, 'hora', '', '', event)" maxlength='5' size='6' class='caixadetexto'>
            <img src = '../../../imagem/seta_abaixo.gif' title='Copiar Horário(s) p/ Funcionário(s)' width='12' height='12' onclick='copiar_todos_horarios()'>
            &nbsp;
            <label for='chkt_pagar_principal_vt'>
                <b>Pagar VT</b>
            </label>
            <input type='checkbox' name='chkt_pagar_principal_vt' id='chkt_pagar_principal_vt' title='Selecionar Todos Pagar VT' onclick='selecionar_todos_pagar_vt()' class='checkbox'>
            &nbsp;
            <label for='chkt_pagar_principal_vr'>
                <b>Pagar VR</b>
            </label>
            <input type='checkbox' name='chkt_pagar_principal_vr' id='chkt_pagar_principal_vr' title='Selecionar Todos Pagar VR' onclick='selecionar_todos_pagar_vr()' class='checkbox'>
        </td>
    </tr>
<?
	//Verifico em que Período a Data Atual passada por parâmetro se enquadra "Data Inicial e Data Final" -> usado + abaixo ...
	$sql = "SELECT `data_inicial`, `data_final` 
                FROM `funcionarios_hes_rel` 
                WHERE `data_final` >= '$_GET[cmb_data_hora_extra]' ORDER BY `id_funcionario_he_rel` LIMIT 1 ";
	$campos_datas = bancos::sql($sql);
	if(count($campos_datas) == 1) {
            $data_inicial 	= $campos_datas[0]['data_inicial'];
            $data_final 	= $campos_datas[0]['data_final'];
	}else {
            $data_inicial 	= '0000-00-00';
            $data_final 	= '0000-00-00';
	}

	$departamento_anterior = '';
	$d = 0;
	for ($i = 0; $i < $linhas; $i++) {
/*Aqui eu verifico se o Departamento Anterior é Diferente do Departamento Atual que está sendo listado
no loop, se for então eu atribuo o Departamento Atual p/ o Departamento Anterior ...*/
            if($departamento_anterior != $campos[$i]['departamento']) {
                $departamento_anterior = $campos[$i]['departamento'];
?>
    <tr class='linhadestaque'>
        <td colspan='9'>
            <font color='yellow'>
                <b>Departamento: </b>
            </font>
            <?=$campos[$i]['departamento'];?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <label for='departamento<?=$i;?>'><b>Depto </b></label>
            <input type='checkbox' name='chkt_departamento[]' value="<?=$campos[$i]['id_departamento'];?>" title='Selecionar todos' onClick="selecionar_departamento('<?=$d;?>', '<?=$i;?>', '<?=$campos[$i]['id_departamento'];?>')" id="departamento<?=$i;?>" class='checkbox'>
        </td>
        <td bgcolor='#CECECE'>
            <b>Nome</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Cargo</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Empresa</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Hora Inicial</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Hora Final</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Pagar VT</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Pagar VR</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Descontar Hora de Almoço</b>
        </td>
    </tr>
<?
                $d++;
            }
//Busca de Dados dos Funcionários na Tabela de Horas Extras ...
            $sql = "SELECT `hora_inicial`, `hora_final`, `pagar_vt`, `pagar_vr`, `descontar_hora_almoco` 
                    FROM `funcionarios_horas_extras` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                    AND `data_hora_extra` = '".$_GET['cmb_data_hora_extra']."' LIMIT 1 ";
            $campos_horas_extras = bancos::sql($sql);
            if(count($campos_horas_extras) == 1) {//Achou na Base ...
                $hora_inicial   = number_format($campos_horas_extras[0]['hora_inicial'], 2, ':', '');
                $hora_final     = number_format($campos_horas_extras[0]['hora_final'], 2, ':', '');
                $checked        = 'checked';
                $class          = 'caixadetexto';
                $disabled       = '';
            }else {//Não encontrou ...
                $hora_inicial   = '';
                $hora_final     = '';
                $checked        = '';
                $class          = 'textdisabled';
                $disabled       = 'disabled';
            }
?>
    <tr class='linhanormal' onclick="selecionar_funcionario('<?=$i;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_funcionario[]' id='chkt_funcionario<?=$i;?>' value='<?=$campos[$i]['id_funcionario'];?>' title='Digite a Hora Inicial' onclick="selecionar_funcionario('<?=$i;?>')" maxlength='5' size='6' class='caixadetexto' <?=$checked;?>>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
            &nbsp;
            <a href="javascript:nova_janela('descritivo_historico.php?id_funcionario_loop=<?=$campos[$i]['id_funcionario'];?>&txt_data_inicial=<?=$data_inicial;?>&txt_data_final=<?=$data_final;?>&ignorar_seguranca=1', 'DESCRITIVO_HISTORICO', '', '', '', '', '400', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title='Descritivo Histórico' class='link'>
                <img src = '../../../imagem/visualizar_detalhes.png' title = 'Descritivo Histórico' alt = 'Descritivo Histórico' border='0'>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <input type='text' name='txt_hora_inicial[]' id='txt_hora_inicial<?=$i;?>' value='<?=$hora_inicial;?>' title='Digite a Hora Inicial' onclick="selecionar_funcionario('<?=$i;?>');return focos(this)" onkeyup="verifica(this, 'hora', '', '', event)" maxlength='5' size='6' class='<?=$class;?>' <?=$disabled;?>>
        </td>
        <td>
            <input type='text' name='txt_hora_final[]' id='txt_hora_final<?=$i;?>' value='<?=$hora_final;?>' title='Digite a Hora Final' onclick="selecionar_funcionario('<?=$i;?>');return focos(this)" onkeyup="verifica(this, 'hora', '', '', event)" maxlength='5' size='6' class='<?=$class;?>' <?=$disabled;?>>
        </td>
        <td>
        <?
            $checked_vt = ($campos_horas_extras[0]['pagar_vt'] == 'S') ? 'checked' : '';
        ?>
            <input type='checkbox' name='chkt_pagar_vt[]' id='chkt_pagar_vt<?=$i;?>' value='<?=$campos[$i]['id_funcionario'];?>' title='Selecione o Pagar VT' onclick="selecionar_funcionario('<?=$i;?>')" class='checkbox' <?=$checked_vt;?> <?=$disabled;?>>
        </td>
        <?
            $checked_vr = ($campos_horas_extras[0]['pagar_vr'] == 'S') ? 'checked' : '';
        ?>
        <td>
            <input type='checkbox' name='chkt_pagar_vr[]' id='chkt_pagar_vr<?=$i;?>' value='<?=$campos[$i]['id_funcionario'];?>' title='Selecione o Pagar VR' onclick="selecionar_funcionario('<?=$i;?>');selecionar_vr('<?=$i;?>')" class='checkbox' <?=$checked_vr;?> <?=$disabled;?>>
        </td>
        <?
            $checked_descontar_hora_almoco = ($campos_horas_extras[0]['descontar_hora_almoco'] == 'S') ? 'checked' : '';
        ?>
        <td>
            <input type='checkbox' name='chkt_descontar_hora_almoco[]' id='chkt_descontar_hora_almoco<?=$i;?>' value='<?=$campos[$i]['id_funcionario'];?>' title="Selecione o Pagar VR" onclick="selecionar_funcionario('<?=$i;?>')" class='checkbox' <?=$checked_descontar_hora_almoco;?> <?=$disabled;?>>
            <!--Utilizado p/ ajudar nos Controles com o JavaScript-->
            <input type='hidden' name='hdd_departamento[]' id='hdd_departamento<?=$i;?>' value='<?=$campos[$i]['id_departamento'];?>'>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'opcoes_relacao_func_hora_extra.php'" class='botao'>
        <?
/*Aqui eu busco a próxima Data de Pagamento na Tabela de Relatórios de Horas Extras referente a Data de 
Lançamento Corrente */
            $sql = "SELECT `data_pagamento` 
                    FROM `funcionarios_hes_rel` 
                    WHERE `data_inicial` >= '$_GET[cmb_data_hora_extra]' LIMIT 1 ";
            $campos = bancos::sql($sql);
//Se não achar, o botão Salvar fica habilitado p/ q seje possível estar fazendo alterações ...
            if(count($campos) == 0) {
                $class      = 'botao';
                $disabled   = '';
            }else {//Se achar então ...
                $data_atual = date('Y-m-d');
//Se a Data de Pagamento for maior que a Data Atual, então eu não posso mais alterar os dados ...
                if($campos[0]['data_pagamento'] >= $data_atual) {//Pode Alterar
                    $class      = 'botao';
                    $disabled   = '';
                }else {//Pode estar fazendo alterações concernente aos dados
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                }
            }
//Aqui eu verifico se já foi pago o Valor de hora Extra p/ essa Data de Hora Extra 
        ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='<?=$class;?>' <?=$disabled;?>>
        <?
            if($disabled == 'disabled') {//Significa que já aconteceu a Data de Pagamento ...
                echo ' <font color="darkred">JÁ FOI REALIZADO O PAGAMENTO</font>';
            }
        ?>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>