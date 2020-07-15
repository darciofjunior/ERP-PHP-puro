<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');

$mensagem[1] = '<font class="atencao">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">USINAGEM INCLUÍDA COM SUCESSO PARA P.A.</font>';
$mensagem[3] = '<font class="erro">USINAGEM JÁ EXISTENTE ESTE PARA P.A.</font>';

//Inserção dos produtos acabados vs produtos insumos
if($passo == 1) {
    if($_POST['inserir'] == 1) {
        foreach($_POST['cmb_produto_insumo'] as $id_produto_insumo) {
            $sql = "SELECT id_pac_pi_usi 
                    FROM `pacs_vs_pis_usis` 
                    WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' 
                    AND `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {
                $sql = "INSERT INTO `pacs_vs_pis_usis` (`id_pac_pi_usi`, `id_produto_acabado_custo`, `id_produto_insumo`, `qtde`) VALUES (NULL, '$_POST[id_produto_acabado_custo]', '$id_produto_insumo', '1') ";
                bancos::sql($sql);
                $valor = 2;
            }else {
                $valor = 3;
            }
        }
?>
    <Script Language = 'JavaScript'>
        var valor = eval('<?=$valor;?>')
        if(valor == 2) window.location = 'alterar_etapa6.php?id_produto_acabado_custo=<?=$_POST['id_produto_acabado_custo'];?>'
    </Script>
<?
    }
}
//Fim da Inserção

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT pi.id_produto_insumo, pi.discriminacao 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo AND g.`referencia` LIKE '%$txt_consultar%' 
                    WHERE pi.`id_grupo` = '17' 
                    AND pi.`ativo` = '1' ORDER BY pi.discriminacao ";
        break;
        case 2:
            $sql = "SELECT id_produto_insumo, discriminacao 
                    FROM `produtos_insumos` 
                    WHERE `discriminacao` LIKE '%$txt_consultar%' 
                    AND `id_grupo` = '17' 
                    AND `ativo` = '1' ORDER BY discriminacao ";
        break;
        default:
            $sql = "SELECT id_produto_insumo, discriminacao 
                    FROM `produtos_insumos` 
                    WHERE `id_grupo` = '17' 
                    AND `ativo` = '1' ORDER BY discriminacao ";
        break;
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_usinagem.php?id_produto_acabado_custo=<?=$id_produto_acabado_custo;?>&valor=1'
    </Script>
<?
        exit;
    }
}
?>
<html>
<head>
<title>.:: Consultar Produto(s) Insumo(s) de Usinagem(ns) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
/*Funções referentes a primeira tela antes de fazer a consulta*/
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
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
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
}
    
function atrelar() {
    var i, elementos = document.form.elements
    var selecionados = 0
    for (i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1; j < document.form.elements[i].length; j++) {
                if(document.form.elements[i][j].selected == true) selecionados ++
            }
        }
    }

    if(selecionados == 0) {
        alert('SELECIONE UM PRODUTO INSUMO !')
        return false
    }else if(selecionados > 10) {
        alert('EXCEDIDO O NÚMERO DE PRODUTO(S) INSUMO(S) SELECIONADO(S) !\n\nPERMITIDO NO MÁXIMO 10 REGISTROS POR VEZ !')
        return false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.inserir.value = 1
    document.form.submit()
}

function selecionar_todos() {
    var i, elementos = document.form.elements
    for (i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1; j < document.form.elements[i].length; j++) document.form.elements[i][j].selected = true
        }
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.document.form.submit()
}
</Script>
</head>
<body onLoad='document.form.txt_consultar.focus()' onunload="atualizar_abaixo()">
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onSubmit='return validar()'>
<!--********************************Controle de Tela********************************-->
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='inserir'>
<?
    $consultar  = (!empty($txt_consultar)) ? $txt_consultar : $txt_consultar2;
    $opcao      = (!empty($opt_opcao)) ? $opt_opcao : $opt_opcao2;
?>
<input type='hidden' name='txt_consultar2' value="<?=$consultar;?>">
<input type='hidden' name='opt_opcao2' value="<?=$opcao;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<input type='hidden' name='nao_atualizar'>
<!--********************************************************************************-->
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Insumo(s) de Usinagem(ns)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title="Consultar Produtos Insumos por: Referência" id='label'>
            <label for='label'>Referência</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' onClick='document.form.txt_consultar.focus()' title='Consultar Produtos Insumos por: Discriminação' id='label2' checked>
            <label for='label2'>Discrimina&ccedil;&atilde;o</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' onClick='limpar()' value='1' title='Consultar todos os Produtos Insumos' id='label3' class='checkbox'>
            <label for='label3'>Todos os registros</label>
        </td>
    </tr>
<?
    if($passo == 1) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <select name="cmb_produto_insumo[]" class="combo" size="5" multiple>
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
                <option value="<?=$campos[$i]['id_produto_insumo'];?>"><?=$campos[$i]['discriminacao'];?></option>
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
            <input type='reset' name='cmd_limpar' style="color:#ff9900;" value="Limpar" onclick="document.form.opcao.checked = false;limpar();" title='Limpar' class='botao'>
<?
    if($passo == 1) {
?>
            <input type='button' name='cmd_selecionar' value='Selecionar Todos' title='Selecionar Todos' onclick='selecionar_todos()' class='botao'>
            <input type='button' name='cmd_atrelar' value='Atrelar' title='Atrelar' onclick='atrelar()' class='botao'>
<?
    }
?>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>