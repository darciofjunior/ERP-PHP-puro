<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/biblioteca.php');
segurancas::geral($PHP_SELF, '../../../../../');
$mensagem[1] = '<font class="erro">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';

$representantes = biblioteca::controle_itens($id_representante, $id_representante2, $acao);

/**********************Essas variáveis são controles de hiddens*************************/
if(!empty($opcao_selecionada))          $opt_opcao = $opcao_selecionada;
if(!empty($apenas_autonomos_nacionais)) $chkt_apenas_autonomos_nacionais = $apenas_autonomos_nacionais;
/****************************************************************************************/
if($passo == 1) {
    if(!empty($chkt_apenas_autonomos_nacionais)) {
//Aqui eu verifico todos os representantes que são funcionários
        $sql = "SELECT r.id_representante 
                FROM `representantes` r 
                INNER JOIN `representantes_vs_funcionarios` rf ON rf.id_representante = r.id_representante ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) $id_representantes.= $campos[$i]['id_representante'].', ';
        $id_representantes = substr($id_representantes, 0, strlen($id_representantes) - 2);
        //Eu passei zero também, não sei, eu acho que deve estar com erro de cadastro ...
        $condicao = " AND `id_pais` IN (0, 31) AND `id_representante` NOT IN ($id_representantes) ";
    }

    switch($opt_opcao) {
        case 1:
            $sql = "SELECT id_representante, nome_fantasia 
                    FROM `representantes` 
                    WHERE `id_representante` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' $condicao ORDER BY nome_fantasia ";
        break;
        case 2:
            $sql = "SELECT id_representante, nome_fantasia 
                    FROM `representantes` 
                    WHERE `nome_representante` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' $condicao ORDER BY nome_fantasia ";
        break;
        case 3:
            $sql = "SELECT id_representante, nome_fantasia 
                    FROM `representantes` 
                    WHERE `nome_fantasia` LIKE '$txt_consultar%' 
                    AND `ativo` = '1' $condicao ORDER BY nome_fantasia ";
        break;
        default:
            $sql = "SELECT id_representante, nome_fantasia 
                    FROM `representantes` 
                    WHERE `ativo` = '1' $condicao ORDER BY nome_fantasia ";
        break;
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'etiquetas.php?valor=1'
        </Script>
<?
        exit;
    }
}
?>
<html>
<head>
<title>.:: Consultar Representante(s) p/ Imprimir Etiqueta(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
/*************************************************************************/
/*Funções referentes a segunda tela depois da consulta - Passo = 1*/
function enviar() {
    var elementos = document.form.elements
    var selecionados = 0, id_representante = ''
    for (i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1; j < document.form.elements[i].length; j++) {
                if(document.form.elements[i][j].selected == true) {
                    selecionados ++
                    id_representante = id_representante + document.form.elements[i][j].value + ',';
                }
            }
            i = elementos.length
        }
    }

    if(selecionados == 0) {
        alert('SELECIONE UM REPRESENTANTE !')
        return false
    }
    document.form.id_representante2.value = id_representante.substr(0, id_representante.length - 1);
    document.form.txt_consultar.value = '<?=$txt_consultar;?>'
    document.form.opcao_selecionada.value = '<?=$opt_opcao;?>'
    document.form.apenas_autonomos_nacionais.value = '<?=$chkt_apenas_autonomos_nacionais;?>'
    document.form.action = 'etiquetas.php'
    document.form.target = '_self'
    document.form.passo.value = 1
    document.form.exibir.value = 1
    document.form.submit()
}

function selecionar_todos() {
    var i, elementos = document.form.elements
    for (i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1; j < document.form.elements[i].length; j++) document.form.elements[i][j].selected = true
            return false
        }
    }
}
/*************************************************************************/
/*Funções referentes a terceira tela depois da consulta - !empty($representantes)*/
function retirar_representante() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
    var flag = 0, representante_sel = ''
    var achou_combo = 0
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a segunda combo
            if(achou_combo == 2) {
                if(document.form.elements[i].value == '') {
                    if(flag == 0) alert('SELECIONE PELO MENOS UM REPRESENTANTE !')
                    document.form.elements[i].focus()
                    return false
                }else {
                    for(j = 0; j < document.form.elements[i].length; j ++) {
                        if(document.form.elements[i][j].selected == true) representante_sel = representante_sel + document.form.elements[i][j].value + ','
                    }
                }
                flag++
            }
        }
    }
    representante_sel                               = representante_sel.substr(0, representante_sel.length - 1)
    document.form.id_representante2.value           = representante_sel
    document.form.txt_consultar.value               = '<?=$txt_consultar;?>'
    document.form.opcao_selecionada.value           = '<?=$opt_opcao;?>'
    document.form.apenas_autonomos_nacionais.value  = '<?=$chkt_apenas_autonomos_nacionais;?>'
    document.form.acao.value        = 1
    document.form.exibir.value      = 1
    document.form.passo.value       = 1
    document.form.action            = 'etiquetas.php'
    document.form.target            = '_self'
    document.form.submit()
}

function selecionar_todos_representantes() {
    var elementos = document.form.elements
    var selecionados = ''
    var achou_combo = 0
    for (var i = 0; i < elementos.length; i ++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a segunda combo
            if(achou_combo == 2) {
                for(j = 1; j < document.form.elements[i].length; j++) document.form.elements[i][j].selected = true
            }
        }
    }
}

function imprimir_etiquetas() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
    var flag = 0
    var achou_combo = 0, selecionados = 0
    selecionar_todos_representantes()
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a terceira combo
            if(achou_combo == 2) {
                if(document.form.elements[i].value == '') {
                    if(flag == 0) alert('SELECIONE PELO MENOS UM REPRESENTANTE !')
                    document.form.elements[i].focus()
                    return false
                }else {
                    for(j = 0; j < document.form.elements[i].length; j ++) {
                        if(document.form.elements[i][j].selected == true) selecionados ++
                    }
                }
                flag++
            }
        }
    }
    if(selecionados == 0) {
        alert('SELECIONE UM CLIENTE !')
        return false
    }
//Força o Preenchimento de um Departamento ...
    if(!combo('form', 'cmb_departamento', '', 'SELECIONE UM DEPARTAMENTO !')) {
        document.form.cmb_departamento.focus()
        return false
    }
//Destino
    document.form.action = 'imprimir_etiquetas.php'
    document.form.target = 'novajanela'
    nova_janela('imprimir_etiquetas.php', 'novajanela', 'F')
    document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo'>
<input type='hidden' name='exibir'>
<input type='hidden' name='opcao_selecionada'>
<input type='hidden' name='apenas_autonomos_nacionais'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Representante(s) p/ Imprimir Etiqueta(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar representante por: Código do Representante' onclick='document.form.txt_consultar.focus()' id='label1'>
            <label for='label1'>Código do Representante</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar representante por: Nome do Representante' onclick='document.form.txt_consultar.focus()' id='label2' checked>
            <label for='label2'>Nome do Representante</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Consultar representante por: Nome Fantasia' onclick='document.form.txt_consultar.focus()' id='label3'>
            <label for='label3'>Nome Fantasia</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os Representantes' onclick='limpar()' id='label4' class='checkbox'>
            <label for='label4'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_apenas_autonomos_nacionais' value='1' title='Apenas Autônomos Nacionais' id='label5' onclick='limpar()' class='checkbox'>
            <label for='label5'>Apenas Autônomos Nacionais</label>
        </td>
    </tr>
<?
    if($passo == 1) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <select name='cmb_representante[]' class='combo' size='5' multiple>
                <option value='' style='color:red'>
                SELECIONE
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </option>
<?
                for($i = 0; $i < $linhas; $i ++) {
?>
                    <option value="<?=$campos[$i]['id_representante'];?>"><?=$campos[$i]['nome_fantasia'];?></option>
<?
                }
?>
            </select>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Redefinir Consulta' title='Redefinir Consulta' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
<?
        if($passo == 1) {
?>
            <input type='button' name='cmd_selecionar' value='Selecionar Todos Itens' title='Selecionar Todos Itens' onclick='selecionar_todos()' class='botao'>
            <input type='button' name='cmd_adicionar' value='Adicionar Item(ns) Selecionado(s)' title='Adicionar' onclick='enviar()' class='botao'>
<?
        }
?>
        </td>
    </tr>
</table>
<?
/*Nessa parte é simplesmente para mostrar a segunda combo com os representantes
selecionados da primeira combo*/
    if(!empty($representantes)) {
        $sql = "SELECT id_representante, nome_fantasia 
                FROM `representantes` 
                WHERE `id_representante` IN ($representantes) ORDER BY nome_fantasia " ;
        $campos = bancos::sql($sql);
        $linhas = count($campos);
?>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Representante(s) Selecionado(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <select name='cmb_representante_selecionado[]' class='combo' size='5' multiple>
                <option value='' style='color:red'>
                SELECIONE
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </option>
        <?
            for($i = 0; $i < $linhas; $i++) {
        ?>
                <option value='<?=$campos[$i]['id_representante']?>'><?=$campos[$i]['nome_fantasia']?></option>
        <?
            }
        ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Departamento: 
            <select name='cmb_departamento' title='Selecione o Departamento' class='combo'>
            <?
                    $sql = "SELECT id_departamento, departamento 
                            FROM `departamentos` 
                            WHERE `ativo` = '1' ORDER BY departamento ";
                    echo combos::combo($sql, 'COMPRAS');
            ?>
            </select>
            - Quantidade de Representantes Selecionados para o Envio de Etiquetas:<font color="red"> <?=$linhas;?></font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='button' name='cmd_retirar2' value='Retirar Item(ns) Selecionado(s)' title='Retirar Item(ns) Selecionado(s)' onclick='retirar_representante()' style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_imprimir_etiqueta' value='Imprimir Etiqueta(s)' title="Imprimir Etiqueta(s)" onclick='return imprimir_etiquetas()' style='color:black' class='botao'>
        </td>
    </tr>
</table>
<?
    }
?>
<input type='hidden' name='id_representante' value='<?=$representantes;?>'>
<input type='hidden' name='id_representante2'>
<input type='hidden' name='acao'>
</form>
</body>
<Script Language = 'JavaScript'>
/*Funções referentes a primeira tela antes de fazer a consulta*/
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = false
        document.form.opt_opcao[1].checked      = true
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
    document.form.action = 'etiquetas.php'
    document.form.target = '_self'
    document.form.exibir.value = 0
    document.form.passo.value = 1
}

function desabilitar() {
    if(document.form.opt_opcao[4].checked == true) {
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}
</Script>
</html>