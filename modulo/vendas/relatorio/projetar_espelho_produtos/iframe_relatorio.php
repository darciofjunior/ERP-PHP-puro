<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/relatorio/projetar_espelho_produtos/relatorio.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cmd_consultar      = $_POST['cmd_consultar'];
    $cmb_tipo_relatorio = $_POST['cmb_tipo_relatorio'];
    $cmb_cliente	= $_POST['cmb_cliente'];
}else {
    $cmd_consultar      = $_GET['cmd_consultar'];
    $cmb_tipo_relatorio	= $_GET['cmb_tipo_relatorio'];
    $cmb_cliente	= $_GET['cmb_cliente'];
}
?>
<html>
<head>
<title>.:: Relat�rio de Pedido(s) Fam�lia / Grupo dos �ltimos (6 anos) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function controlar_combos() {
    //Somente nessa op��o de Fam�lia Selecionada que eu apresento a Combo de Representante e de Cliente p/ o usu�rio ...
    if(document.form.cmb_tipo_relatorio.value == 'familia') {
        document.getElementById('div_filtros_extras').style.visibility  = 'visible'
    }else {
        document.getElementById('div_filtros_extras').style.visibility  = 'hidden'
        document.form.cmb_representante.value                           = ''
        document.form.cmb_cliente.value                                 = ''
    }
}

function visualizar_detalhes_por_grupo(indice, id_cliente, id_familia) {
    //Se Div Oculta, ent�o apresento dados do Grupo na DIV atrav�s do id_familia passado por par�metro ...
    if(document.getElementById('linha_detalhes_por_grupo'+indice).style.visibility == 'hidden') {
        document.getElementById('linha_detalhes_por_grupo'+indice).style.visibility = 'visible'
        ajax('visualizar_detalhes_por_grupo.php?id_cliente='+id_cliente+'&id_familia='+id_familia, 'div_detalhes_por_grupo'+indice, '', 'SIM')
    }else {//Se Div Vis�vel, ent�o oculto dados da DIV ...
        document.getElementById('linha_detalhes_por_grupo'+indice).style.visibility = 'hidden'
        //Muita sacanagem, tive que criar um arquivo em branco porque o meu Ajax se perde, quando uso o comando innerHTML que limpa a DIV ...
        ajax('branco.php', 'div_detalhes_por_grupo'+indice)
    }
}
    
function validar() {
//Tipo de Relat�rio ...
    if(!combo('form', 'cmb_tipo_relatorio', '', 'SELECIONE O TIPO DE RELAT�RIO !')) {
        return false
    }
    //Somente quando esta Div estiver aparencendo p/ o usu�rio � que vou for�ar o preenchimento dessas combos ...
    if(document.getElementById('div_filtros_extras').style.visibility == 'visible') {
//Representante ...
        if(!combo('form', 'cmb_representante', '', 'SELECIONE O REPRESENTANTE !')) {
            return false
        }
//Cliente ...
        if(!combo('form', 'cmb_cliente', '', 'SELECIONE O CLIENTE !')) {
            return false
        }
        //Dependendo do usu�rio logado essa Combo vem travada, ent�o sempre destravo a mesma p/ n�o furar as consultas abaixo ...
        document.form.cmb_representante.disabled = false
    }
}

function carregar_clientes() {
    var id_cliente_selecionado  = eval('<?=$cmb_cliente;?>')
    ajax('carregar_clientes.php', 'cmb_cliente', id_cliente_selecionado)
}

function carregar_endereco_cliente(id_cliente) {
    ajax('carregar_endereco_cliente.php?id_cliente='+id_cliente, 'cmb_endereco_cliente')
    /*Coloco um temporizador para carregar o endere�o do Cliente na Combo, porque o ajax retarda um pouco p/ 
    o fazer do Processamento ...*/
    setTimeout('document.form.cmb_endereco_cliente.value="'+id_cliente+'"', '1100')
    //Se o usu�rio n�o selecionou nenhum Cliente ent�o oculto o Label, do contr�rio exibo o Label ...
    document.getElementById('lbl_endereco_cliente').style.visibility = (id_cliente == '') ? 'hidden' : 'visible'
}
</Script>
</head>
<body onload="controlar_combos();carregar_clientes();carregar_endereco_cliente('<?=$cmb_cliente;?>')">
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='1' cellspacing='0' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='16'>
            Relat�rio de Pedido(s) Fam�lia / Grupo dos �ltimos (6 anos)
            &nbsp;-&nbsp;
            Tipo de Relat�rio: 
            <?
                if($cmb_tipo_relatorio == 'familia') {
                    $selected1 = 'selected';
                }else if($cmb_tipo_relatorio == 'grupo_pa') {
                    $selected2 = 'selected';
                }else if($cmb_tipo_relatorio == 'grupo_pa_ed') {
                    $selected3 = 'selected';
                }else if($cmb_tipo_relatorio == 'produto_acabado') {
                    $selected4 = 'selected';
                }
            ?>
            <select name='cmb_tipo_relatorio' title='Selecione o Tipo de Relat�rio' onchange='controlar_combos()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='familia' <?=$selected1;?>>Por Fam�lia</option>
                <option value='grupo_pa' <?=$selected2;?>>Por Grupo do PA</option>
                <option value='grupo_pa_ed' <?=$selected3;?>>Por Grupo vs Empresa Divis�o</option>
                <option value='produto_acabado' <?=$selected4;?>>Por Produto Acabado</option>
            </select>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <div id='div_filtros_extras' style='visibility: hidden'>
                Representante: 
                <select name='cmb_representante' title='Selecione o Representante' onchange="ajax('carregar_clientes.php', 'cmb_cliente')" class='combo'>
                <?
                    $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                            FROM `representantes` 
                            WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                    echo combos::combo($sql, $cmb_representante);
                ?>
                </select>
                <br/>
                Cliente: 
                <select name='cmb_cliente' id='cmb_cliente' title='Selecione o Cliente' onchange="carregar_endereco_cliente(this.value)" class='combo'>
                    <option value=''>LOADING ...</option>
                </select>
                <label id='lbl_endereco_cliente' style='visibility:hidden'>
                    <br/>
                    Logradouro: 
                    <select name='cmb_endereco_cliente' id='cmb_endereco_cliente' title='Endere�o do Cliente' class='textdisabled' disabled>
                        <option value=''>LOADING ...</option>
                    </select>
                </label>
            </div>
        </td>
    </tr>
<?
    if(!empty($cmd_consultar)) {
        if($cmb_tipo_relatorio == 'familia') {
            require('relatorio_por_familia.php');
        }else if($cmb_tipo_relatorio == 'grupo_pa') {
            require('relatorio_por_grupo_pa.php');
        }else if($cmb_tipo_relatorio == 'grupo_pa_ed') {
            require('relatorio_por_grupo_pa_ed.php');
        }else if($cmb_tipo_relatorio == 'produto_acabado') {
            require('relatorio_por_produto_acabado.php');
        }
    }
?>