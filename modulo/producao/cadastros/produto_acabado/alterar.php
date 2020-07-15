<?
require('../../../../lib/segurancas.php');
if(empty($pop_up))  require '../../../../lib/menu/menu.php';//Significa que essa Tela foi aberta como sendo Pop-UP ...
require('../../../../lib/custos.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/mda.php');
require('../../../../lib/producao.php');
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = '<font class="confirmacao">PRODUTO ACABADO ALTERADO COM SUCESSO.</font>';
$mensagem[2] = '<font class="erro">PRODUTO ACABADO J� EXISTENTE.</font>';
$mensagem[3] = '<font class="confirmacao">C�DIGO DE BARRA EXCLU�DO COM SUCESSO.</font>';

/**************************************************************************************************/
//Fun��o que atualiza a Sub-Opera��o de Custo do PA ...
if($alterar_operacao_custo_sub == 1) {
//Se a Sub-Opera��o de Custo � Industrial, ent�o esta passa a ser Revenda ...
    if($operacao_custo_sub == 0) {
        $nova_operacao_custo_sub = 1;
//Se a Sub-Opera��o de Custo � Revenda, ent�o esta passa a ser Industrial ...
    }else {
        $nova_operacao_custo_sub = 0;
    }
    $sql = "UPDATE `produtos_acabados` SET `operacao_custo_sub` = '$nova_operacao_custo_sub' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php<?=$parametro;?>'
    </Script>
<?
}
/**************************************************************************************************/

if($passo == 1) {
    /********************Roteiro p/ excluir C�digo de Barras*******************/
    if($_GET['excluir_codigo_barras'] == 'S') {
        //Aqui eu busco o atual "C�digo de Barra" do PA passado por par�metro ...
        $sql = "SELECT `codigo_barra` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
        $campos_codigo_barra    = bancos::sql($sql);
        $codigo_barra           = $campos_codigo_barra[0]['codigo_barra'];
        
        //Desaloco o C�digo de Barra do PA ...
        $sql = "UPDATE `produtos_acabados` SET `codigo_barra` = '' WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
        bancos::sql($sql);
                
        //Libero o C�digo de Barra para que o mesmo possa ser utilizado novamente ...
        $sql = "UPDATE `codigos_barras` SET `usado` = 'N' WHERE `codigo_barra` = '$codigo_barra' LIMIT 1 ";
        bancos::sql($sql);
        
        $valor = 3;
    }
    /**************************************************************************/
    
//Verifico se o $_GET[id_produto_acabado] que foi passado por par�metro � um PIPA ...
    $sql = "SELECT `id_produto_insumo` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' 
            AND `id_produto_insumo` > '0' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {
        $existe = 1;
        $id_produto_insumo = $campos[0]['id_produto_insumo'];
    }

    //Busco dados do Produto Acabado que o usu�rio deseja clonar ...
    $sql = "SELECT gp.`id_familia`, pa.* 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gp ON gp.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
    $campos 			= bancos::sql($sql);
    $id_familia                 = $campos[0]['id_familia'];
    $id_produto_insumo          = $campos[0]['id_produto_insumo'];
    $id_gpa_vs_emp_div          = $campos[0]['id_gpa_vs_emp_div'];
    $id_unidade 		= $campos[0]['id_unidade'];
    $operacao 			= $campos[0]['operacao'];
    $operacao_custo             = $campos[0]['operacao_custo'];
    $operacao_custo_sub         = $campos[0]['operacao_custo_sub'];
    $origem_mercadoria          = $campos[0]['origem_mercadoria'];
    $codigo_fornecedor 		= $campos[0]['codigo_fornecedor'];
    $referencia 		= $campos[0]['referencia'];
    $discriminacao 		= $campos[0]['discriminacao'];
    $peso_unitario 		= number_format($campos[0]['peso_unitario'], 4, ',', '.');
    $pecas_por_jogo             = $campos[0]['pecas_por_jogo'];
    $altura 			= $campos[0]['altura'];
    $largura 			= $campos[0]['largura'];
    $comprimento 		= $campos[0]['comprimento'];
    $fci_albafer 		= $campos[0]['fci_albafer'];
    $fci_tool_master            = $campos[0]['fci_tool_master'];
    $observacao 		= $campos[0]['observacao'];
    $explodir_view_estoque      = $campos[0]['explodir_view_estoque'];
    $status_nao_produzir        = $campos[0]['status_nao_produzir'];
    $codigo_barra 		= $campos[0]['codigo_barra'];
?>
<html>
<head>
<title>.:: Alterar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Grupo P.A. vs Empresa Divis�o
    if(!combo('form', 'cmb_gpas_vs_emps_divs', '', 'SELECIONE UM GRUPO P.A. (EMPRESA DIVIS�O) !')) {
        return false
    }
//Opera��o de Custo
    if(!combo('form', 'cmb_operacao_custo', '', 'SELECIONE UMA OPERA��O DE CUSTO !')) {
        return false
    }
//Sub-Opera��o de Custo
/*Se a Opera��o de Custo selecionada foi Industrial, ent�o eu for�o o usu�rio a preencher uma 
Sub-Opera��o de Custo*/
    if(document.form.cmb_operacao_custo.value == 0) {//Industrial
        if(!combo('form', 'cmb_operacao_custo_sub', '', 'SELECIONE UMA SUB-OPERA��O DE CUSTO !')) {
            return false
        }
    }
//Unidade
    if(!combo('form', 'cmb_unidade', '', 'SELECIONE UMA UNIDADE !')) {
        return false
    }
//Origem da Mercadoria ...
    if(!combo('form', 'cmb_origem_mercadoria', '', 'SELECIONE A ORIGEM DA MERCADORIA !')) {
        return false
    }
//Opera��o FAT
    if(!combo('form', 'cmb_operacao', '', 'SELECIONE UMA OPERA��O !')) {
        return false
    }
//C�digo do Fornecedor ...
    if(document.form.txt_codigo_fornecedor.value != '') {
        if(!texto('form', 'txt_codigo_fornecedor', '1', '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ._-', 'C�DIGO DO FORNECEDOR', '2')) {
            return false
        }
    }
//Refer�ncia ...
    if(!texto('form','txt_referencia', '3', "-=!@������{} 1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�JHGFDSAZXCVBNM,.'��������������������������.,%&*$()@#<>���:;\/", 'REFER�NCIA', '1')) {
        return false
    }
//Discrimina��o
    if(!texto('form', 'txt_discriminacao', '3', "+-=!@������{} 1234567890qwertyuiop�lkjhgfdsazxcvbnmQWERTYUIOPLK�JHGFDSAZXCVBNM��������������������������.%&*$()@#<>����:;\/", 'DISCRIMINA��O', '1')) {
        return false
    }
/*Pe�as por Jogo - sempre ser� obrigat�rio ser preenchido se a Unidade for Jogo ou a Fam�lia 
do Produto = Machos ...*/
    var id_familia = eval('<?=$id_familia;?>')
    if(document.form.cmb_unidade.value == 12 || id_familia == 9) {
        if(!texto('form', 'txt_pecas_por_jogo', '1', '1234567890', 'PE�AS POR JOGO', '1')) {
            return false
        }
        //Nunca este campo "Pe�as por Jogo" pode ser igual = Zero ...
        if(document.form.txt_pecas_por_jogo.value == 0) {
            alert('PE�AS POR JOGO INV�LIDO !!!\n\nPE�AS POR JOGO N�O PODE SER IGUAL A ZERO !')
            document.form.txt_pecas_por_jogo.focus()
            document.form.txt_pecas_por_jogo.select()
            return false
        }
    }
//Altura
    if(document.form.txt_altura.value != '') {
        if(!texto('form', 'txt_altura', '1', '1234567890', 'ALTURA', '1')) {
            return false
        }
    }
//Largura
    if(document.form.txt_largura.value != '') {
        if(!texto('form', 'txt_largura', '1', '1234567890', 'LARGURA', '1')) {
            return false
        }
    }
//Comprimento
    if(document.form.txt_comprimento.value != '') {
        if(!texto('form', 'txt_comprimento', '1', '1234567890', 'COMPRIMENTO', '2')) {
            return false
        }
    }
/*************************Controle com os campos de FCI************************/
/*Se a Origem da Mercadoria for uma dessas 3 abaixo ao qual se referem a Importa��o de PA(s), ent�o 
for�o o preenchimento desses campos de FCI que ter�o de estar presentes na Nota Fiscal ...*/
    if(document.form.cmb_origem_mercadoria.value == 3 || document.form.cmb_origem_mercadoria.value == 5 || document.form.cmb_origem_mercadoria.value == 8) {
        //FCI Albafer ...
        if(!texto('form', 'txt_fci_albafer', '36', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890-', 'FCI DA ALBAFER', '2')) {
            return false
        }
        //FCI Tool Master ...
        if(!texto('form', 'txt_fci_tool_master', '36', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890-', 'FCI DA TOOL MASTER', '2')) {
            return false
        }
    }
/******************************************************************************/
/*Se a Refer�ncia do PA for diferente de 'ESP' e o PA estiver com a Marca��o de "N�o Produzido Temp" o Sistema 
retorna uma mensagem de Erro ...*/
    /*if(document.form.txt_referencia.value != 'ESP' && document.form.chkt_status_nao_produzido_temp.checked == true) {
        alert('ESSA MARCA��O "N�O PRODUZIDO TEMPORARIAMENTE", S� SERVE PARA REFER�NCIA ESP !!!')
        document.form.chkt_status_nao_produzido_temp.checked = false
        return false
    }*/
//Habilita a caixa de texto referencia para submeter valor
    document.form.txt_referencia.disabled = false
    document.form.action = 'alterar.php?passo=2'
}

function excluir_codigo_barras(id_produto_acabado) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR O C�DIGO DE BARRA DESTE PA ?')
    if(resposta == true) window.location = 'alterar.php<?=$parametro;?>&passo=1&excluir_codigo_barras=S&id_produto_acabado='+id_produto_acabado
}

function check_referencia() {
    if(document.form.chk_referencia.checked == true) {
        document.form.txt_referencia.disabled   = true
        document.form.txt_referencia.value      = 'ESP'
    }else {
        document.form.txt_referencia.value      = '<?=$referencia;?>'
        document.form.txt_referencia.disabled   = false
        document.form.txt_referencia.focus()
    }
}

function fechar_atualizar() {
    var resposta = confirm('DESEJA REALMENTE FECHAR ESTA JANELA ?')
    if(resposta == true) {
        window.opener.document.form.submit()
        window.close()
    }else {
        return false
    }
}

function controle_operacao_custo() {
    var operacao_custo = eval(document.form.cmb_operacao_custo.value)
    if(operacao_custo == 0) {//Quando a Opera��o de Custo = Industrial, eu habilito a Sub-Opera��o de Custo ...
//Layout de Habilitado
        document.form.cmb_operacao_custo_sub.className  = 'combo'
//Habilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value      = '<?=$operacao_custo_sub;?>'
        document.form.cmb_operacao_custo_sub.disabled   = false
//Quando a Opera��o de Custo = Revenda, eu desabilito a Sub-Opera��o de Custo ...
    }else {
//Layout de Desabilitado
        document.form.cmb_operacao_custo_sub.className  = 'textdisabled'
//Desabilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value      = ''
        document.form.cmb_operacao_custo_sub.disabled   = true
    }
}
</Script>
</head>
<body onload='check_referencia();controle_operacao_custo()'>
<form name='form' method='post' onsubmit='return validar()' enctype='multipart/form-data'>
<input type='hidden' name='id_produto_insumo' value='<?=$id_produto_insumo;?>'>
<input type='hidden' name='id_produto_acabado' value='<?=$_GET['id_produto_acabado'];?>'>
<input type='hidden' name='existe' value='<?=$existe;?>'>
<input type='hidden' name='hdd_gerar_codigo'>
<!--Essa vari�vel serve para saber se essa tela � uma tela simples ou � um Pop-Up
Se for igual 1, ent�o significa q essa tela � um Pop-Up, da onde ela � chamada na Parte de Custo 
de P.A. Industrial-->
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar Produto Acabado
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo P.A. (Empresa Divis�o):</b>
        </td>
        <td>
            <b>Opera��o de Custo / Sub-Opera��o de Custo:</b>
        </td>
        <td>
            <b>Unidade:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_gpas_vs_emps_divs' title='Selecione o Grupo P.A. (Empresa Divis�o)' class='combo'>
            <?
                $sql = "SELECT ged.`id_gpa_vs_emp_div`, CONCAT(gpa.`nome`, ' (', ed.razaosocial, ') ') AS rotulo 
                        FROM `gpas_vs_emps_divs` ged 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                        WHERE (gpa.`ativo` = '1' OR (gpa.`ativo` = '0' AND ged.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div')) 
                        AND gpa.`ativo` = '1' ORDER BY rotulo ";
                echo combos::combo($sql, $id_gpa_vs_emp_div);
            ?>
            </select>
        </td>
        <td>
            <select name="cmb_operacao_custo" title="Selecione a Opera��o de Custo" onchange="controle_operacao_custo()" class='combo'>
                <?
                    if($operacao_custo == 0) {
                        $selectedi = 'selected';
                    }else if($operacao_custo == 1) {
                        $selectedr = 'selected';
                    }
                ?>
                <option value='' style="color:red">SELECIONE</option>
                <option value='0' <?=$selectedi;?>>Industrializa��o</option>
                <option value='1' <?=$selectedr;?>>Revenda</option>
            </select>
            &nbsp;
            <select name="cmb_operacao_custo_sub" title="Selecione a Sub-Opera��o" class='combo'>
            <?
                if($operacao_custo_sub == 0) {
                    $selectedii = 'selected';
                }else if($operacao_custo_sub == 1) {
                    $selectedir = 'selected';
                }
            ?>
                <option value='' style="color:red">SELECIONE</option>
                <option value='0' <?=$selectedii;?>>Industrializa��o</option>
                <option value='1' <?=$selectedir;?>>Revenda</option>
            </select>
        </td>
        <td>
            <select name='cmb_unidade' title='Selecione a Unidade' class='combo'>
            <?
                $sql = "SELECT `id_unidade`, `unidade` 
                        FROM `unidades` 
                        WHERE `ativo` = '1' ORDER BY `unidade` ";
                echo combos::combo($sql, $id_unidade);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='#0000FF'>
                <b>Origem da Mercadoria:</b>
            </font>
        </td>
        <td colspan='2'>
            <font color='#0000FF'>
                <b>Opera��o (Fat):</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_origem_mercadoria' title='Selecione a Origem da Mercadoria' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    $vetor_origem_mercadoria  = array_sistema::origem_mercadoria();
                    foreach($vetor_origem_mercadoria as $indice => $id_origem_mercadoria) {
                        $selected = ($origem_mercadoria == $indice) ? 'selected' : '';
                        echo "<option value='$indice' $selected>".$indice.' - '.$id_origem_mercadoria."</option>";
                    }
                ?>
            </select>
        </td>
        <td colspan='2'>
            <select name='cmb_operacao' title='Selecione a Opera��o' class='combo'>
                <?
                    if($operacao == 0) {
                        $selected_i = 'selected';
                    }else if($operacao == 1) {
                        $selected_r = 'selected';
                    }
                ?>
                <option value='' style="color:red">SELECIONE</option>
                <option value='0' <?=$selected_i;?>>Industrializa��o (c/ IPI)</option>
                <option value='1' <?=$selected_r;?>>Revenda (s/ IPI)</option>
            </select>
        </td>
    </tr>
<?
/*Aqui traz todas as embalagens que est�o relacionado ao produto acabado*/
$sql = "SELECT ppe.`id_pa_pi_emb`, ppe.`pecas_por_emb`, ppe.`embalagem_default`, pi.`id_produto_insumo`, 
        pi.`discriminacao`, pi.`unidade_conversao`, u.`sigla` 
        FROM `pas_vs_pis_embs` ppe 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppe.`id_produto_insumo` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        WHERE ppe.`id_produto_acabado` = '$id_produto_acabado' ORDER BY ppe.`id_pa_pi_emb` ";
$campos_embs = bancos::sql($sql);
$linhas_embs = count($campos_embs);
if($linhas_embs > 0) {
?>
</table>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='5'>
            <b>Embalagem(ns) Atrelada(s)</b>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <b><i>Ref. Emb - Discrimina��o</i></b>
        </td>
        <td>
            <b><i>E.P.</i></b>
        </td>
        <td>
            <b><i>P�s / Emb</i></b>
        </td>
        <td>
            <b><i>Pre�o Unit�rio R$</i></b>
        </td>
        <td>
            <b><i>Total R$ </i></b>
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas_embs; $i++) {
?>
    <tr class='linhanormal'>
        <td align='left'>
            <?=$campos_embs[$i]['sigla'].' - '.$campos_embs[$i]['discriminacao'];?>
        </td>
        <td align='center'>
        <?
            if($campos_embs[$i]['embalagem_default'] == 1) {//Principal
                echo '<img src="../../../../imagem/certo.gif">';
            }else {
                echo '&nbsp;';
            }
        ?>
        </td>
        <td align='center'>
        <?
            if($campos_embs[$i]['unidade_conversao'] > 0) {
                echo $campos_embs[$i]['pecas_por_emb'].' / '.number_format($campos_embs[$i]['unidade_conversao'], 2, ',', '.').' ('.number_format($campos_embs[$i]['pecas_por_emb'] / $campos_embs[$i]['unidade_conversao'], 2, ',', '.').') ';
            }else {
                echo $campos_embs[$i]['pecas_por_emb'].' / <font color="red" title="Sem Convers�o">S. C.</font>';
            }
        ?>
        </td>
        <td align='right'>
        <?
            $dados_pi = custos::preco_custo_pi($campos_embs[$i]['id_produto_insumo']);
            echo number_format($dados_pi['preco_comum'], 2, ',', '.');
        ?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
    }
?>
</table>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?}?>
    <tr class='linhanormal'>
        <td>
            C�digo do Fornecedor:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>Refer�ncia:</b>
        </td>
        <td>
            <b>Discrimina��o:</b>
        </td>
        <td>
            C�digo de Barra:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_codigo_fornecedor' value='<?=$codigo_fornecedor;?>' title='Digite o C�digo do Fornecedor' maxlength='15' size='16' class='caixadetexto'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='text' name='txt_referencia' value='<?=$referencia;?>' title='Digite a Refer�ncia' size='20' maxlength='30' class='caixadetexto'>
            <?
//Verifica se os produtos acabados s�o do tipo especial ...
                $checked = ($referencia == 'ESP') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chk_referencia' onclick='check_referencia()' id='label' class='checkbox' <?=$checked;?>>
            <label for='label'>ESP</label>
        </td>
        <td>
            <input type='text' name='txt_discriminacao' value='<?=$discriminacao;?>' title='Digite a Discrimina��o' maxlength='100' size='60' class='caixadetexto'>
            &nbsp;&nbsp;
            <a href="javascript:nova_janela('../../../../help/visualizar.php?id_help=1', 'CONSULTAR', '', '', '', '', '450', '780', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Ajuda" class='link'>
                <img src = '../../../../imagem/help.jpg' border='0' width='20' height='20'>
            </a>
        <?
            //Aqui eu verifico se o PA j� teve algum Or�amento na vida ...
            $sql = "SELECT `id_orcamento_venda` 
                    FROM `orcamentos_vendas_itens` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_orcs = bancos::sql($sql);
            $linhas_orcs = count($campos_orcs);
            if($linhas_orcs == 1) {
        ?>
            &nbsp;&nbsp;
            <a href = '../../../classes/orcamento/orcamentos.php?id_produto_acabado=<?=$id_produto_acabado;?>' class='html5lightbox'>
                Or�amento(s)
            </a>
        <?
            }
        ?>
        </td>
        <td>
        <?
//Verifico se j� existe C�digo de Barra p/ esse Produto ...
            if(!empty($codigo_barra)) {
        ?>
                <input type='text' name='txt_codigo_barra' maxlength='13' size='14' title='C�digo de Barra' value='<?=$codigo_barra;?>' class='textdisabled' disabled>
        <?
                //Esse bot�o de Excluir C�digo de Barras s� mostro p/ Roberto 62 e D�rcio 98 porque programa ...
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
        ?>
                &nbsp;
                <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir C�digo de Barras' alt='Excluir C�digo de Barras' onclick="excluir_codigo_barras('<?=$id_produto_acabado?>')">
        <?
                }
            }else {
                echo '<font color="red"><b>N�O EXISTE C�DIGO DE BARRA <br>P/ ESTE TIPO DE PRODUTO</b></font>';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
<!--Esses par�metros tela1 serve para o pop-up fazer a atualiza��o na tela de baixo-->
            <a href="javascript:nova_janela('../../../classes/produtos_acabados/alterar_peso_unitario.php?id_produto_acabado=<?=$id_produto_acabado;?>&tela1=window.opener', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Atualizar Peso do Produto' class='link'>
                Peso Unit�rio (Kg): 
            </a>
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            Pe�as por Jogo:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$peso_unitario;?>
        </td>
        <td>
            <?
                $checked = ($explodir_view_estoque == 'S') ? 'checked' : '';			
            ?>
            <input type='checkbox' name='chkt_explodir_view_estoque' value='S' title='Explodir Visualizar Estoque' id='label1' class='checkbox' <?=$checked;?>>
            <label for='label1'>Explodir Visualizar Estoque</label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            -
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <?
                $checked = ($status_nao_produzir == 1) ? 'checked' : '';
            ?>
            <input type='checkbox' name='chkt_status_nao_produzido_temp' value='1' title='N�o Produzido Temporariamente' id='label2' class='checkbox' <?=$checked;?>>
            <label for='label2'>N�o Produzido Temporariamente</label>
        </td>
        <td>
            <input type='text' name='txt_pecas_por_jogo' value='<?=$pecas_por_jogo;?>' title='Digite o Pe�as por Jogo' size='4' maxlenght='2' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Altura:
        </td>
        <td>
            Largura:
        </td>
        <td>
            Comprimento:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_altura' value='<?=$altura;?>' title='Digite a Altura' size='12' maxlenght='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
        <td>
            <input type='text' name='txt_largura' value='<?=$largura;?>' title='Digite a Largura' size='12' maxlenght='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
        <td>
            <input type='text' name='txt_comprimento' value='<?=$comprimento;?>' title='Digite o Comprimento' size='12' maxlenght="10" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'> mm
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Desenho p/ OP:
        </td>
        <td colspan='2'>
            <input type='file' name='txt_desenho_para_op' title='Digite ou selecione o Caminho do Desenho para OP' size='80' class='caixadetexto'>
            <!--Este hidden ser� utilizado mais abaixo no passo 2 ...-->
            <input type='hidden' name='hdd_desenho_para_op' value='<?=$campos[0]['desenho_para_op'];?>'>
        </td>
    </tr>
    <?
        if(!empty($campos[0]['desenho_para_op'])) {
    ?>
    <tr class='linhanormal'>
        <td>
            Desenho p/ OP Atual:
        </td>
        <td colspan='2'>
            <img src = '<?='../../../../imagem/fotos_produtos_acabados/'.$campos[0]['desenho_para_op'];?>' width='180' height='120'>
            &nbsp;
            <input type='checkbox' name='chkt_excluir_desenho_para_op' id='chkt_excluir_desenho_para_op' value='S' title='Excluir Desenho p/ OP' class='checkbox'>
            <label for='chkt_excluir_desenho_para_op'>
                Excluir Desenho p/ OP
            </label>
        </td>
    </tr>
    <?
        }
    ?>
    <tr class='linhanormal'>
        <td>
            Desenho p/ Etiqueta:
        </td>
        <td colspan='2'>
            <input type='file' name='txt_desenho_para_etiqueta' title='Digite ou selecione o Caminho do Desenho para Etiqueta' size='80' class='caixadetexto'>
            <!--Este hidden ser� utilizado mais abaixo no passo 2 ...-->
            <input type='hidden' name='hdd_desenho_para_etiqueta' value='<?=$campos[0]['desenho_para_etiqueta'];?>'>
        </td>
    </tr>
    <?
        if(!empty($campos[0]['desenho_para_etiqueta'])) {
    ?>
    <tr class='linhanormal'>
        <td>
            Desenho p/ Etiqueta Atual:
        </td>
        <td colspan='2'>
            <img src = '<?='../../../../imagem/desenhos_grupos_pas/'.$campos[0]['desenho_para_etiqueta'];?>' width='180' height='120'>
            &nbsp;
            <input type='checkbox' name='chkt_excluir_desenho_para_etiqueta' id='chkt_excluir_desenho_para_etiqueta' value='S' title='Excluir Desenho p/ Etiqueta Atual' class='checkbox'>
            <label for='chkt_excluir_desenho_para_etiqueta'>
                Excluir Desenho p/ Etiqueta Atual
            </label>
        </td>
    </tr>
    <?
        }
    ?>
    <tr class='linhanormal'>
        <td>
            Desenho p/ Confer�ncia:
        </td>
        <td colspan='2'>
            <input type='file' name='txt_desenho_para_conferencia' title='Digite ou selecione o Caminho do Desenho para Confer�ncia' size='80' class='caixadetexto'>
            <!--Este hidden ser� utilizado mais abaixo no passo 2 ...-->
            <input type='hidden' name='hdd_desenho_para_conferencia' value='<?=$campos[0]['desenho_para_conferencia'];?>'>
        </td>
    </tr>
<?
/******************************************************************************/
        if(!empty($campos[0]['desenho_para_conferencia'])) {//Se existe um Desenho no Grupo ent�o ...
?>
    <tr class='linhanormal'>
        <td>
            Desenho p/ Confer�ncia Atual:
        </td>
        <td colspan='2'>
            <img src = '../../../../imagem/fotos_produtos_acabados/<?=$campos[0]['desenho_para_conferencia'];?>' width='180' height='120'>
            &nbsp;
            <input type='checkbox' name='chkt_excluir_desenho_para_conferencia' id='chkt_excluir_desenho_para_conferencia' value='S' title='Excluir Desenho p/ Confer�ncia Atual' class='checkbox'>
            <label for='chkt_excluir_desenho_para_conferencia'>
                Excluir Desenho p/ Confer�ncia Atual
            </label>
        </td>
    </tr>
<?
        }
/******************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            FCI da Albafer:
        </td>
        <td colspan='2'>
            <input type='text' name='txt_fci_albafer' value='<?=$fci_albafer;?>' title='Digite a FCI da Albafer' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" maxlength='36' size='38' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            FCI da Tool Master:
        </td>
        <td colspan='2'>
            <input type='text' name='txt_fci_tool_master' value='<?=$fci_tool_master;?>' title='Digite a FCI da Tool Master' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" maxlength='36' size='38' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            Observa��o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <textarea name='txt_observacao_produto' rows='3' cols='85' maxlength='255' title='Digite a Observa&ccedil;&atilde;o' class='caixadetexto'><?=$observacao;?></textarea>
        </td>
    </tr>
<?
        if($existe == 1) {
?>
    <tr class='linhanormal'>
        <td colspan='3'>
            <marquee loop='100' scrollamount='5'>
                <font size='2' color='blue'><b>ESSE PRODUTO ACABADO EST� RELACIONADO COM
                    O PRODUTO INSUMO !</b>
                </font>
            </marquee>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
        <?
//Significa que � uma tela normal, sendo assim pode exibir o bot�o de Voltar
            if($pop_up == 0) {
        ?>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
        <?
            }
        ?>
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class='botao'>
            <input type='submit' name="cmd_alterar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        <?
//Significa que essa tela � um Pop-UP, sendo assim exibir o bot�o de Fechar
            if($pop_up == 1) {
        ?>
            <input type='button' name="cmd_fechar_atualizar" value="Fechar e Atualizar" title="Fechar e Atualizar" onclick="fechar_atualizar()" style="color:red" class='botao'>
        <?
            }
        ?>
        </td>
    </tr>
</table>
<br>
</form>
</body>
</html>
<?
}else if($passo == 2) {
/*Atualiza��o do P.A.*/
    $sql = "SELECT `id_produto_acabado` 
            FROM `produtos_acabados` 
            WHERE ((`discriminacao` = '$_POST[txt_discriminacao]') OR (`referencia` = '$_POST[txt_referencia]' AND `referencia` <> 'ESP')) 
            AND `ativo` = '1' 
            AND `id_produto_acabado` <> '$_POST[id_produto_acabado]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
//Aqui busca a opera��o de custo do P.A. antes da altera��o dos campos
        $sql = "SELECT `operacao_custo`, `status_custo`, `desenho_para_op`, `codigo_barra` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $operacao_custo         = $campos[0]['operacao_custo'];
        $status_custo           = $campos[0]['status_custo'];
        $desenho_para_op        = $campos[0]['desenho_para_op'];
        $codigo_barra           = $campos[0]['codigo_barra'];
//Verifica se n�o foi alterada a �ltima opera��o de custo
        if($operacao_custo != $_POST['cmb_operacao_custo']) {//Alterou
            if($status_custo == 1) {//Significa q est� liberado
/*Aqui eu verifico qual � o id_produto_acabado_custo do P.A. pela Opera��o de Custo gravada
no BD, antes da substitui��o pela Nova selecionada pelo usu�rio ...*/
                $sql = "SELECT `id_produto_acabado_custo` 
                        FROM `produtos_acabados_custos` 
                        WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' 
                        AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
                $campos_custo = bancos::sql($sql);
                $id_produto_acabado_custo = $campos_custo[0]['id_produto_acabado_custo'];
/*Aqui eu travo o Custo de Todos os PA(s) da 7� Etapa que est�o relacionados ao P.A. 
Corrente em que eu estou mudando a Opera��o de Custo ...*/
                custos::liberar_desliberar_custo($id_produto_acabado_custo, 'NAO');
                $status_custo = 0;//Volta a bloquear o custo - Ir� gravar no BD de Dados ...
            }
        }
        $referencia = strtoupper($_POST['txt_referencia']);
/****************Controle com a Lista de Pre�o****************/
        if($referencia == 'ESP') {//Se a Refer�ncia desejada do usu�rio for ESP ...
//Verifico se a refer�ncia atual do PA � ESP ...
            $sql = "SELECT `referencia` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $referencia = $campos[0]['referencia'];
/*Se a Refer�ncia atual do PA � Normal de linha e este ent�o vai virar ESP, ent�o ... 
A partir do momento q o usu�rio est� trocando essa refer�ncia, eu zero os Pre�os preco_promocional e 
preco_unitario desse PA na Lista de Pre�os p/ n�o dar problema*/
            if($referencia != 'ESP') {//At� aki a Refer�ncia era de um PA normal de linha ...
                $sql = "UPDATE `produtos_acabados` SET `preco_promocional` = '0.00', `preco_unitario` = '0.00' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
/*************************************************************/
/*Se o Usu�rio habilitou a op��o de excluir a Imagem ou ent�o ele est� fazendo a substitui��o de uma
Imagem por outra, ent�o eu excluo a imagem atual do servidor ...*/
        if(!empty($_POST['chkt_excluir_desenho_para_op'])) {
            $endereco_imagem = '../../../../imagem/fotos_produtos_acabados/'.$desenho_para_op;
            unlink($endereco_imagem);//Exclui a Imagem do Servidor ...
            $campo_desenho_para_op = " `desenho_para_op` = '', ";
        }
        
        if($_FILES['txt_desenho_para_op']['error'] == 1) {//Tratamento p/ Desenhos muito grandes ...
?>
    <Script Language = 'Javascript'>
        alert('ESSE DESENHO DE OP N�O SER� UPADO !!!\n\nDESENHO DE OP MUITO PESADO P/ SUBIR NO SERVIDOR !')
    </Script>
<?
        }else {
//Fazendo Upload da Imagem para o Servidor ...
            if(!empty($_FILES['txt_desenho_para_op']['type'])) {
                //Tratamento com a Imagem 
                switch ($_FILES['txt_desenho_para_op']['type']) {
                    case 'image/gif':
                    case 'image/pjpeg':
                    case 'image/jpeg':
                    case 'image/x-png':
                    case 'image/png':
                    case 'image/bmp':
                        $desenho_para_op = copiar::copiar_arquivo('../../../../imagem/fotos_produtos_acabados/', $_FILES['txt_desenho_para_op']['tmp_name'], $_FILES['txt_desenho_para_op']['name'], $_FILES['txt_desenho_para_op']['size'], $_FILES['txt_desenho_para_op']['type'], '2');
                    break;
                    default:
                        //echo "N�o � possivel copiar a imagem";
                    break;
                }
                $campo_desenho_para_op = " `desenho_para_op` = '$desenho_para_op', ";
            }
        }
        
/*Se o Usu�rio habilitou a op��o de excluir a Imagem ou ent�o ele est� fazendo a substitui��o de uma
Imagem por outra, ent�o eu excluo a imagem atual do servidor ...*/
        if(!empty($_POST['chkt_excluir_desenho_para_etiqueta'])) {
            if(file_exists('../../../../imagem/desenhos_grupos_pas/'.$_POST['hdd_desenho_para_etiqueta'])) {
                unlink('../../../../imagem/desenhos_grupos_pas/'.$_POST['hdd_desenho_para_etiqueta']);
            }
        }

//Fazendo Upload da Imagem para o Servidor ...
        if(!empty($_FILES['txt_desenho_para_etiqueta']['type'])) {
            //Tratamento com a Imagem ...
            switch ($_FILES['txt_desenho_para_etiqueta']['type']) {
                case 'image/gif':
                case 'image/pjpeg':
                case 'image/jpeg':
                case 'image/x-png':
                case 'image/bmp':
                    $desenho_para_etiqueta = copiar::copiar_arquivo('../../../../imagem/desenhos_grupos_pas/', $_FILES['txt_desenho_para_etiqueta']['tmp_name'], $_FILES['txt_desenho_para_etiqueta']['name'], $_FILES['txt_desenho_para_etiqueta']['size'], $_FILES['txt_desenho_para_etiqueta']['type'], '2');
                break;
                default:
                    //echo "N�o � possivel copiar a imagem";
                break;
            }
            $campo_desenho_para_etiqueta = " `desenho_para_etiqueta` = '$desenho_para_etiqueta', ";
        }
        
/*Se o Usu�rio habilitou a op��o de excluir a Imagem ou ent�o ele est� fazendo a substitui��o de uma
Imagem por outra, ent�o eu excluo a imagem atual do servidor ...*/
        if(!empty($_POST['chkt_excluir_desenho_para_conferencia'])) {
            if(file_exists('../../../../imagem/fotos_produtos_acabados/'.$_POST['hdd_desenho_para_conferencia'])) {
                unlink('../../../../imagem/fotos_produtos_acabados/'.$_POST['hdd_desenho_para_conferencia']);
            }
        }

//Fazendo Upload da Imagem para o Servidor ...
        if(!empty($_FILES['txt_desenho_para_conferencia']['type'])) {
            //Tratamento com a Imagem ...
            switch ($_FILES['txt_desenho_para_conferencia']['type']) {
                case 'image/gif':
                case 'image/pjpeg':
                case 'image/jpeg':
                case 'image/x-png':
                case 'image/bmp':
                    $desenho_para_conferencia = copiar::copiar_arquivo('../../../../imagem/desenhos_grupos_pas/', $_FILES['txt_desenho_para_conferencia']['tmp_name'], $_FILES['txt_desenho_para_conferencia']['name'], $_FILES['txt_desenho_para_conferencia']['size'], $_FILES['txt_desenho_para_conferencia']['type'], '2');
                break;
                default:
                    //echo "N�o � possivel copiar a imagem";
                break;
            }
            $campo_desenho_para_conferencia = " `desenho_para_conferencia` = '$desenho_para_conferencia', ";
        }
/*************************************************************/
        /*Se o PA: 

        * N�o possui C�digo de Barra;
        * Normal de Linha;
        * Fam�lias Diferentes de Componente / M�o Obra;
        * SEM Blank na Discrimina��o;
         * 

        Ent�o existir� C�digo de Barras ...*/
        if($codigo_barra == '' && $_POST['txt_referencia'] != 'ESP' && ($_POST['id_familia'] != 23 && $_POST['id_familia'] != 24 && $_POST['id_familia'] != 25) && strpos($_POST['txt_discriminacao'], 'BLANK') === false) {
            //Busco o Primeiro N.� de C�digo de Barras que esteja dispon�vel na Tabela "codigos_barras" ...
            $sql = "SELECT `codigo_barra` 
                    FROM `codigos_barras` 
                    WHERE `usado` = 'N' LIMIT 1 ";
            $campos_codigo_barra = bancos::sql($sql);
            
            if(count($campos_codigo_barra) == 1) {//Significa que existe 1 c�digo de Barra dispon�vel ...
                $codigo_barra = $campos_codigo_barra[0]['codigo_barra'];
            }else {//Significa que j� n�o temos mais c�digos dispon�veis ...
                echo 'CHEGAMOS AO LIMITE DE 9.999 C�DIGO DE BARRA(S).';
                exit;
                //Teria que gerar o 10000 ???
                
                /*$codigo_barra = producao::gerador_codigo_barra($id_produto_acabado);
                /*Essa � uma garantia de que estou trabalhando exatamente com 13 d�gitos, �s vezes o sistema pode
                gerar com 14 d�gitos o c�digo devido estourar o limite de c�digos que ainda hoje � de 9999
                o que seria um erro ...
                $codigo_barra = substr($codigo_barra, 0, 13);*/
            }
            
            /*Verifico se esse C�digo de Barras que acabou de ser gerado acima, j� foi utilizado 
            por algum PA no sistema ...*/
            $sql = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `codigo_barra` = '$codigo_barra' LIMIT 1 ";
            $campos_codigo_barra = bancos::sql($sql);
            if(count($campos_codigo_barra) == 0) {//Atualizo os Dados do PA com o Novo C�digo de Barras ...
                $sql = "UPDATE `produtos_acabados` SET `codigo_barra` = '$codigo_barra' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                bancos::sql($sql);
                
                /*Atualizo a Tabela "codigos_barras" marcando o campo "usado" como sendo = 'S' p/ que este 
                N.� n�o seja sugerido futuramente ...*/
                $sql = "UPDATE `codigos_barras` SET `usado` = 'S' WHERE `codigo_barra` = '$codigo_barra' LIMIT 1 ";
                bancos::sql($sql);
            }else {
?>
                <Script Language = 'JavaScript'>
                    alert('C�DIGO DE BARRA(S) J� EXISTENTE !')
                </Script>
<?
            }
        }
//Atualizando Dados do PA ...
        $sql = "UPDATE `produtos_acabados` SET `id_gpa_vs_emp_div` = '$_POST[cmb_gpas_vs_emps_divs]', `id_unidade` = '$_POST[cmb_unidade]' , `id_funcionario` = '$_SESSION[id_funcionario]', `operacao` = '$_POST[cmb_operacao]', `operacao_custo` = '$_POST[cmb_operacao_custo]', `operacao_custo_sub` = '$_POST[cmb_operacao_custo_sub]', `origem_mercadoria` = '$_POST[cmb_origem_mercadoria]', `id_nivel` = '', `codigo_fornecedor` = '$_POST[txt_codigo_fornecedor]', `referencia` = '$_POST[txt_referencia]', `discriminacao` = '$_POST[txt_discriminacao]', `altura` = '$_POST[txt_altura]', `pecas_por_jogo` = '$_POST[txt_pecas_por_jogo]', `largura` = '$_POST[txt_largura]', `comprimento` = '$_POST[txt_comprimento]', $campo_desenho_para_op $campo_desenho_para_etiqueta $campo_desenho_para_conferencia `fci_albafer` = '$_POST[txt_fci_albafer]', `fci_tool_master` = '$_POST[txt_fci_tool_master]', `observacao` = '$_POST[txt_observacao_produto]', `status_custo` = '$status_custo', `explodir_view_estoque` = '$_POST[chkt_explodir_view_estoque]', `status_nao_produzir` = '$_POST[chkt_status_nao_produzido_temp]' $campo_desenho WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
        bancos::sql($sql);
        genericas::atualizar_pas_no_site_area_cliente($_POST['id_produto_acabado']);
        
//Essa vari�vel vem herdada do passo anterior, se existe = 1, significa que o Pa tem rela��o com o PI
        if($existe == 1) {
            $data_sys = date('Y-m-d H:i:s');
            $sql = "UPDATE `produtos_insumos` SET `id_unidade` = '$_POST[cmb_unidade]', `discriminacao` = '$_POST[txt_discriminacao]', `altura` = '$_POST[txt_altura]', `largura` = '$_POST[txt_largura]', `comprimento` = '$_POST[txt_comprimento]', `observacao` = '$_POST[txt_observacao_produto]', `data_sys` = '$data_sys' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            bancos::sql($sql);
        }
        $valor = 1;
    }else {
        $valor = 2;
    }
//Significa que essa tela � um Pop-UP, ent�o volta para a parte de altera��o do produto novamente
    if($pop_up == 1) {
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php?passo=1&id_produto_acabado=<?=$_POST[id_produto_acabado];?>&pop_up=<?=$pop_up;?>&valor=<?=$valor;?>'
    </Script>
<?
//Significa que � uma tela normal, ent�o volta para a parte de Filtro para poder consultar novamente
    }else {
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&id_produto_acabado=<?=$_POST['id_produto_acabado'];?>&valor=<?=$valor;?>'
    </Script>
<?
    }
}else {
/*Esse par�metro de n�vel vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisi��o desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../../..';
//Aqui eu vou puxar a Tela �nica de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('../../../classes/produtos_acabados/tela_geral_filtro.php');
    
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Alterar Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function alterar_operacao_custo_sub(id_produto_acabado, operacao_custo_sub) {
    window.location = 'alterar.php<?=$parametro;?>&id_produto_acabado='+id_produto_acabado+'&operacao_custo_sub='+operacao_custo_sub+'&alterar_operacao_custo_sub=1'
}
</Script>
</head>
<body>
<table width='120%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='25'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='25'>
            Alterar Produto(s) Acabado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <?=genericas::order_by('gpa.nome', 'Grupo P.A.', 'Grupo P.A.', $order_by, '../../../../');?>
            <?=genericas::order_by('ed.razaosocial', ' / (E.D.)', 'Empresa Divis�o', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.referencia', 'Ref.', 'Refer�ncia', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.discriminacao', 'Discrimina��o', '', $order_by, '../../../../');?>
        </td>
        <td>
            Top
        </td>
        <td>
            <?=genericas::order_by('pa.operacao_custo', 'O. C.', 'Opera��o de Custo', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.operacao', 'O. F.', 'Opera��o (Fat)', $order_by, '../../../../');?>
        </td>
        <td>
            Origem - ST
        </td>
        <td>
            <p title='Classifica��o Fiscal'>C. F.</p>
        </td>
        <td>
            C�digo<br>Barra
        </td>
        <td>
            Des.<br/>OP
        </td>
        <td>
            Des.<br/>Etiq.
        </td>
        <td>
            Des.<br/>Conf.
        </td>
        <td>
            <font title='Pe�as por Embalagem' style='cursor:help'>
                P.E.
            </font>
        </td>
        <td>
            M.M.V
        </td>
        <td>
            <?=genericas::order_by('pa.peso_unitario', 'P. U.', 'Peso Unit�rio', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.altura', 'Alt', 'Altura', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.largura', 'Larg', 'Largura', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.comprimento', 'Comp', 'Comprimento', $order_by, '../../../../');?>
        </td>
        <td>
            <p title='Dimens�es da Embalagem'>
                Dim. Embals.
            </p>
        </td>
        <td>
            P.U.
        </td>
        <td>
            <p title='Altura'>Alt</p>
        </td>
        <td>
            <p title='Largura'>Larg</p>
        </td>
        <td>
            <p title='Comprimento'>Comp</p>
        </td>
        <td>
            Observa��o
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $dados_produto = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado']);
                    
            $url = 'alterar.php?passo=1&id_produto_acabado='.$campos[$i]['id_produto_acabado'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="window.location = '<?=$url;?>'">
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
                <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);?>
            <?
//Aki � a Marca��o de PA Migrado
            if($campos[$i]['pa_migrado'] == 1) echo '<font color="red" title="PA MIGRADO" style="cursor:help"><b>MIG</b></font>';
        ?>
        <td align='right'>
        <?
            if($campos[$i]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1� 50% dos PA�s TOP'>TopA</font> - ";
            }else if($campos[$i]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2� 50% dos PA�s TOP'>TopB</font> - ";
            }
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['operacao_custo'] == 0) {
                echo 'I';
//Se a Opera��o de Custo for Industrial, ent�o eu apresento a Sub-Opera��o de Custo do PA ...
                if($campos[$i]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos[$i]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos[$i]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['operacao'] == 0) {
        ?>
                <p title="Industrializa��o (c/ IPI)">I - C</p>
        <?
            }else if($campos[$i]['operacao'] == 1) {
        ?>
                <p title="Revenda (s/ IPI)">R - S</p>
        <?
            }else {
                echo '-';
            }
        ?>
        </td>
        <td align='center'>
            <?=$campos[$i]['origem_mercadoria'].$dados_produto['situacao_tributaria'];?>
        </td>
        <td align='center'>
        <?
//Busca a Classifica��o do P.A.
            $sql = "SELECT cf.`classific_fiscal` 
                    FROM `gpas_vs_emps_divs` ged 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                    WHERE ged.`id_gpa_vs_emp_div` = ".$campos[$i]['id_gpa_vs_emp_div']." LIMIT 1 ";
            $campos_classif_fiscal = bancos::sql($sql);
            echo $campos_classif_fiscal[0]['classific_fiscal'];
        ?>
        <td align='center'>
        <?
            if($campos[$i]['codigo_barra'] != '') echo $campos[$i]['codigo_barra'];
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['desenho_para_op'] != '') echo 'Sim';
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['desenho_para_etiqueta'] != '') {
        ?>
                <img src="<?='../../../../imagem/desenhos_grupos_pas/'.$campos[$i]['desenho_para_etiqueta'];?>" width='40' height='12'>
        <?
            }
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['desenho_para_conferencia'] != '') {
        ?>
                <img src="<?='../../../../imagem/fotos_produtos_acabados/'.$campos[$i]['desenho_para_conferencia'];?>" width='40' height='12'>
        <?
            }
        ?>
        </td>
        <td align='center'>
        <?
            //Faz a Verifica��o no Custo do PA <- Etapa 1 - busco qual � a embalagem principal do PA ...
            $sql = "SELECT pi.id_produto_insumo, pi.discriminacao, pi.peso, pi.altura, pi.largura, pi.comprimento, ppe.pecas_por_emb 
                    FROM `pas_vs_pis_embs` ppe 
                    INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ppe.id_produto_insumo 
                    WHERE ppe.`id_produto_acabado` = ".$campos[$i]['id_produto_acabado']." 
                    AND ppe.`embalagem_default` = '1' ORDER BY ppe.id_pa_pi_emb ";
            $campos_pecas_emb = bancos::sql($sql);
            if(count($campos_pecas_emb) == 1) echo intval($campos_pecas_emb[0]['pecas_por_emb']);
        ?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos[$i]['mmv'], 1, '.');?>
        </td>
        <td align='center'>
            <?=number_format($campos[$i]['peso_unitario'], 4, ',', '.');?>
        </td>
        <td align='center'>
            <?=segurancas::number_format($campos[$i]['altura'], 0);?>
        </td>
        <td align='center'>
            <?=segurancas::number_format($campos[$i]['largura'], 0);?>
        </td>
        <td align='center'>
            <?=segurancas::number_format($campos[$i]['comprimento'], 0);?>
        </td>
        <td align='left'>
            <?=$campos_pecas_emb[0]['discriminacao'];?>
        </td>
        <td align='center'>
            <?=segurancas::number_format($campos_pecas_emb[0]['peso'],4, '.');?>
        </td>
        <td align='center'>
            <?=segurancas::number_format($campos_pecas_emb[0]['altura'], 0);?>
        </td>
        <td align='center'>
            <?=segurancas::number_format($campos_pecas_emb[0]['largura'], 0);?>
        </td>
        <td align='center'>
            <?=segurancas::number_format($campos_pecas_emb[0]['comprimento'], 0);?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='25'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
        </td>
    </tr>
</table>
<table width='120%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td>&nbsp;</td>
    </tr>
    <tr align='center'>
        <td>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
</table>
</body>
</html>
<?
    }
}
?>