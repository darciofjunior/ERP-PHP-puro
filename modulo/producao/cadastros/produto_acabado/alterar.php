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
$mensagem[2] = '<font class="erro">PRODUTO ACABADO JÁ EXISTENTE.</font>';
$mensagem[3] = '<font class="confirmacao">CÓDIGO DE BARRA EXCLUÍDO COM SUCESSO.</font>';

/**************************************************************************************************/
//Função que atualiza a Sub-Operação de Custo do PA ...
if($alterar_operacao_custo_sub == 1) {
//Se a Sub-Operação de Custo é Industrial, então esta passa a ser Revenda ...
    if($operacao_custo_sub == 0) {
        $nova_operacao_custo_sub = 1;
//Se a Sub-Operação de Custo é Revenda, então esta passa a ser Industrial ...
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
    /********************Roteiro p/ excluir Código de Barras*******************/
    if($_GET['excluir_codigo_barras'] == 'S') {
        //Aqui eu busco o atual "Código de Barra" do PA passado por parâmetro ...
        $sql = "SELECT `codigo_barra` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
        $campos_codigo_barra    = bancos::sql($sql);
        $codigo_barra           = $campos_codigo_barra[0]['codigo_barra'];
        
        //Desaloco o Código de Barra do PA ...
        $sql = "UPDATE `produtos_acabados` SET `codigo_barra` = '' WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
        bancos::sql($sql);
                
        //Libero o Código de Barra para que o mesmo possa ser utilizado novamente ...
        $sql = "UPDATE `codigos_barras` SET `usado` = 'N' WHERE `codigo_barra` = '$codigo_barra' LIMIT 1 ";
        bancos::sql($sql);
        
        $valor = 3;
    }
    /**************************************************************************/
    
//Verifico se o $_GET[id_produto_acabado] que foi passado por parâmetro é um PIPA ...
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

    //Busco dados do Produto Acabado que o usuário deseja clonar ...
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
//Grupo P.A. vs Empresa Divisão
    if(!combo('form', 'cmb_gpas_vs_emps_divs', '', 'SELECIONE UM GRUPO P.A. (EMPRESA DIVISÃO) !')) {
        return false
    }
//Operação de Custo
    if(!combo('form', 'cmb_operacao_custo', '', 'SELECIONE UMA OPERAÇÃO DE CUSTO !')) {
        return false
    }
//Sub-Operação de Custo
/*Se a Operação de Custo selecionada foi Industrial, então eu forço o usuário a preencher uma 
Sub-Operação de Custo*/
    if(document.form.cmb_operacao_custo.value == 0) {//Industrial
        if(!combo('form', 'cmb_operacao_custo_sub', '', 'SELECIONE UMA SUB-OPERAÇÃO DE CUSTO !')) {
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
//Operação FAT
    if(!combo('form', 'cmb_operacao', '', 'SELECIONE UMA OPERAÇÃO !')) {
        return false
    }
//Código do Fornecedor ...
    if(document.form.txt_codigo_fornecedor.value != '') {
        if(!texto('form', 'txt_codigo_fornecedor', '1', '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ._-', 'CÓDIGO DO FORNECEDOR', '2')) {
            return false
        }
    }
//Referência ...
    if(!texto('form','txt_referencia', '3', "-=!@¹²³£¢¬{} 1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJHGFDSAZXCVBNM,.'ÜüáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãõÃÕ.,%&*$()@#<>ªº°:;\/", 'REFERÊNCIA', '1')) {
        return false
    }
//Discriminação
    if(!texto('form', 'txt_discriminacao', '3', "+-=!@¹²³£¢¬{} 1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJHGFDSAZXCVBNMÜüáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãõÃÕ.%&*$()@#<>ªº°Ø:;\/", 'DISCRIMINAÇÃO', '1')) {
        return false
    }
/*Peças por Jogo - sempre será obrigatório ser preenchido se a Unidade for Jogo ou a Família 
do Produto = Machos ...*/
    var id_familia = eval('<?=$id_familia;?>')
    if(document.form.cmb_unidade.value == 12 || id_familia == 9) {
        if(!texto('form', 'txt_pecas_por_jogo', '1', '1234567890', 'PEÇAS POR JOGO', '1')) {
            return false
        }
        //Nunca este campo "Peças por Jogo" pode ser igual = Zero ...
        if(document.form.txt_pecas_por_jogo.value == 0) {
            alert('PEÇAS POR JOGO INVÁLIDO !!!\n\nPEÇAS POR JOGO NÃO PODE SER IGUAL A ZERO !')
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
/*Se a Origem da Mercadoria for uma dessas 3 abaixo ao qual se referem a Importação de PA(s), então 
forço o preenchimento desses campos de FCI que terão de estar presentes na Nota Fiscal ...*/
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
/*Se a Referência do PA for diferente de 'ESP' e o PA estiver com a Marcação de "Não Produzido Temp" o Sistema 
retorna uma mensagem de Erro ...*/
    /*if(document.form.txt_referencia.value != 'ESP' && document.form.chkt_status_nao_produzido_temp.checked == true) {
        alert('ESSA MARCAÇÃO "NÃO PRODUZIDO TEMPORARIAMENTE", SÓ SERVE PARA REFERÊNCIA ESP !!!')
        document.form.chkt_status_nao_produzido_temp.checked = false
        return false
    }*/
//Habilita a caixa de texto referencia para submeter valor
    document.form.txt_referencia.disabled = false
    document.form.action = 'alterar.php?passo=2'
}

function excluir_codigo_barras(id_produto_acabado) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR O CÓDIGO DE BARRA DESTE PA ?')
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
    if(operacao_custo == 0) {//Quando a Operação de Custo = Industrial, eu habilito a Sub-Operação de Custo ...
//Layout de Habilitado
        document.form.cmb_operacao_custo_sub.className  = 'combo'
//Habilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value      = '<?=$operacao_custo_sub;?>'
        document.form.cmb_operacao_custo_sub.disabled   = false
//Quando a Operação de Custo = Revenda, eu desabilito a Sub-Operação de Custo ...
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
<!--Essa variável serve para saber se essa tela é uma tela simples ou é um Pop-Up
Se for igual 1, então significa q essa tela é um Pop-Up, da onde ela é chamada na Parte de Custo 
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
            <b>Grupo P.A. (Empresa Divisão):</b>
        </td>
        <td>
            <b>Operação de Custo / Sub-Operação de Custo:</b>
        </td>
        <td>
            <b>Unidade:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_gpas_vs_emps_divs' title='Selecione o Grupo P.A. (Empresa Divisão)' class='combo'>
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
            <select name="cmb_operacao_custo" title="Selecione a Operação de Custo" onchange="controle_operacao_custo()" class='combo'>
                <?
                    if($operacao_custo == 0) {
                        $selectedi = 'selected';
                    }else if($operacao_custo == 1) {
                        $selectedr = 'selected';
                    }
                ?>
                <option value='' style="color:red">SELECIONE</option>
                <option value='0' <?=$selectedi;?>>Industrialização</option>
                <option value='1' <?=$selectedr;?>>Revenda</option>
            </select>
            &nbsp;
            <select name="cmb_operacao_custo_sub" title="Selecione a Sub-Operação" class='combo'>
            <?
                if($operacao_custo_sub == 0) {
                    $selectedii = 'selected';
                }else if($operacao_custo_sub == 1) {
                    $selectedir = 'selected';
                }
            ?>
                <option value='' style="color:red">SELECIONE</option>
                <option value='0' <?=$selectedii;?>>Industrialização</option>
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
                <b>Operação (Fat):</b>
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
            <select name='cmb_operacao' title='Selecione a Operação' class='combo'>
                <?
                    if($operacao == 0) {
                        $selected_i = 'selected';
                    }else if($operacao == 1) {
                        $selected_r = 'selected';
                    }
                ?>
                <option value='' style="color:red">SELECIONE</option>
                <option value='0' <?=$selected_i;?>>Industrialização (c/ IPI)</option>
                <option value='1' <?=$selected_r;?>>Revenda (s/ IPI)</option>
            </select>
        </td>
    </tr>
<?
/*Aqui traz todas as embalagens que estão relacionado ao produto acabado*/
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
            <b><i>Ref. Emb - Discriminação</i></b>
        </td>
        <td>
            <b><i>E.P.</i></b>
        </td>
        <td>
            <b><i>Pçs / Emb</i></b>
        </td>
        <td>
            <b><i>Preço Unitário R$</i></b>
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
                echo $campos_embs[$i]['pecas_por_emb'].' / <font color="red" title="Sem Conversão">S. C.</font>';
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
            Código do Fornecedor:
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <b>Referência:</b>
        </td>
        <td>
            <b>Discriminação:</b>
        </td>
        <td>
            Código de Barra:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_codigo_fornecedor' value='<?=$codigo_fornecedor;?>' title='Digite o Código do Fornecedor' maxlength='15' size='16' class='caixadetexto'>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='text' name='txt_referencia' value='<?=$referencia;?>' title='Digite a Referência' size='20' maxlength='30' class='caixadetexto'>
            <?
//Verifica se os produtos acabados são do tipo especial ...
                $checked = ($referencia == 'ESP') ? 'checked' : '';
            ?>
            <input type='checkbox' name='chk_referencia' onclick='check_referencia()' id='label' class='checkbox' <?=$checked;?>>
            <label for='label'>ESP</label>
        </td>
        <td>
            <input type='text' name='txt_discriminacao' value='<?=$discriminacao;?>' title='Digite a Discriminação' maxlength='100' size='60' class='caixadetexto'>
            &nbsp;&nbsp;
            <a href="javascript:nova_janela('../../../../help/visualizar.php?id_help=1', 'CONSULTAR', '', '', '', '', '450', '780', 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Ajuda" class='link'>
                <img src = '../../../../imagem/help.jpg' border='0' width='20' height='20'>
            </a>
        <?
            //Aqui eu verifico se o PA já teve algum Orçamento na vida ...
            $sql = "SELECT `id_orcamento_venda` 
                    FROM `orcamentos_vendas_itens` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_orcs = bancos::sql($sql);
            $linhas_orcs = count($campos_orcs);
            if($linhas_orcs == 1) {
        ?>
            &nbsp;&nbsp;
            <a href = '../../../classes/orcamento/orcamentos.php?id_produto_acabado=<?=$id_produto_acabado;?>' class='html5lightbox'>
                Orçamento(s)
            </a>
        <?
            }
        ?>
        </td>
        <td>
        <?
//Verifico se já existe Código de Barra p/ esse Produto ...
            if(!empty($codigo_barra)) {
        ?>
                <input type='text' name='txt_codigo_barra' maxlength='13' size='14' title='Código de Barra' value='<?=$codigo_barra;?>' class='textdisabled' disabled>
        <?
                //Esse botão de Excluir Código de Barras só mostro p/ Roberto 62 e Dárcio 98 porque programa ...
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
        ?>
                &nbsp;
                <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir Código de Barras' alt='Excluir Código de Barras' onclick="excluir_codigo_barras('<?=$id_produto_acabado?>')">
        <?
                }
            }else {
                echo '<font color="red"><b>NÃO EXISTE CÓDIGO DE BARRA <br>P/ ESTE TIPO DE PRODUTO</b></font>';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
<!--Esses parâmetros tela1 serve para o pop-up fazer a atualização na tela de baixo-->
            <a href="javascript:nova_janela('../../../classes/produtos_acabados/alterar_peso_unitario.php?id_produto_acabado=<?=$id_produto_acabado;?>&tela1=window.opener', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Atualizar Peso do Produto' class='link'>
                Peso Unitário (Kg): 
            </a>
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            Peças por Jogo:
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
            <input type='checkbox' name='chkt_status_nao_produzido_temp' value='1' title='Não Produzido Temporariamente' id='label2' class='checkbox' <?=$checked;?>>
            <label for='label2'>Não Produzido Temporariamente</label>
        </td>
        <td>
            <input type='text' name='txt_pecas_por_jogo' value='<?=$pecas_por_jogo;?>' title='Digite o Peças por Jogo' size='4' maxlenght='2' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
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
            <!--Este hidden será utilizado mais abaixo no passo 2 ...-->
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
            <!--Este hidden será utilizado mais abaixo no passo 2 ...-->
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
            Desenho p/ Conferência:
        </td>
        <td colspan='2'>
            <input type='file' name='txt_desenho_para_conferencia' title='Digite ou selecione o Caminho do Desenho para Conferência' size='80' class='caixadetexto'>
            <!--Este hidden será utilizado mais abaixo no passo 2 ...-->
            <input type='hidden' name='hdd_desenho_para_conferencia' value='<?=$campos[0]['desenho_para_conferencia'];?>'>
        </td>
    </tr>
<?
/******************************************************************************/
        if(!empty($campos[0]['desenho_para_conferencia'])) {//Se existe um Desenho no Grupo então ...
?>
    <tr class='linhanormal'>
        <td>
            Desenho p/ Conferência Atual:
        </td>
        <td colspan='2'>
            <img src = '../../../../imagem/fotos_produtos_acabados/<?=$campos[0]['desenho_para_conferencia'];?>' width='180' height='120'>
            &nbsp;
            <input type='checkbox' name='chkt_excluir_desenho_para_conferencia' id='chkt_excluir_desenho_para_conferencia' value='S' title='Excluir Desenho p/ Conferência Atual' class='checkbox'>
            <label for='chkt_excluir_desenho_para_conferencia'>
                Excluir Desenho p/ Conferência Atual
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
            Observação:
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
                <font size='2' color='blue'><b>ESSE PRODUTO ACABADO ESTÁ RELACIONADO COM
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
//Significa que é uma tela normal, sendo assim pode exibir o botão de Voltar
            if($pop_up == 0) {
        ?>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
        <?
            }
        ?>
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR')" style="color:#ff9900;" class='botao'>
            <input type='submit' name="cmd_alterar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        <?
//Significa que essa tela é um Pop-UP, sendo assim exibir o botão de Fechar
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
/*Atualização do P.A.*/
    $sql = "SELECT `id_produto_acabado` 
            FROM `produtos_acabados` 
            WHERE ((`discriminacao` = '$_POST[txt_discriminacao]') OR (`referencia` = '$_POST[txt_referencia]' AND `referencia` <> 'ESP')) 
            AND `ativo` = '1' 
            AND `id_produto_acabado` <> '$_POST[id_produto_acabado]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
//Aqui busca a operação de custo do P.A. antes da alteração dos campos
        $sql = "SELECT `operacao_custo`, `status_custo`, `desenho_para_op`, `codigo_barra` 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $operacao_custo         = $campos[0]['operacao_custo'];
        $status_custo           = $campos[0]['status_custo'];
        $desenho_para_op        = $campos[0]['desenho_para_op'];
        $codigo_barra           = $campos[0]['codigo_barra'];
//Verifica se não foi alterada a última operação de custo
        if($operacao_custo != $_POST['cmb_operacao_custo']) {//Alterou
            if($status_custo == 1) {//Significa q está liberado
/*Aqui eu verifico qual é o id_produto_acabado_custo do P.A. pela Operação de Custo gravada
no BD, antes da substituição pela Nova selecionada pelo usuário ...*/
                $sql = "SELECT `id_produto_acabado_custo` 
                        FROM `produtos_acabados_custos` 
                        WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' 
                        AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
                $campos_custo = bancos::sql($sql);
                $id_produto_acabado_custo = $campos_custo[0]['id_produto_acabado_custo'];
/*Aqui eu travo o Custo de Todos os PA(s) da 7ª Etapa que estão relacionados ao P.A. 
Corrente em que eu estou mudando a Operação de Custo ...*/
                custos::liberar_desliberar_custo($id_produto_acabado_custo, 'NAO');
                $status_custo = 0;//Volta a bloquear o custo - Irá gravar no BD de Dados ...
            }
        }
        $referencia = strtoupper($_POST['txt_referencia']);
/****************Controle com a Lista de Preço****************/
        if($referencia == 'ESP') {//Se a Referência desejada do usuário for ESP ...
//Verifico se a referência atual do PA é ESP ...
            $sql = "SELECT `referencia` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $referencia = $campos[0]['referencia'];
/*Se a Referência atual do PA é Normal de linha e este então vai virar ESP, então ... 
A partir do momento q o usuário está trocando essa referência, eu zero os Preços preco_promocional e 
preco_unitario desse PA na Lista de Preços p/ não dar problema*/
            if($referencia != 'ESP') {//Até aki a Referência era de um PA normal de linha ...
                $sql = "UPDATE `produtos_acabados` SET `preco_promocional` = '0.00', `preco_unitario` = '0.00' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
/*************************************************************/
/*Se o Usuário habilitou a opção de excluir a Imagem ou então ele está fazendo a substituição de uma
Imagem por outra, então eu excluo a imagem atual do servidor ...*/
        if(!empty($_POST['chkt_excluir_desenho_para_op'])) {
            $endereco_imagem = '../../../../imagem/fotos_produtos_acabados/'.$desenho_para_op;
            unlink($endereco_imagem);//Exclui a Imagem do Servidor ...
            $campo_desenho_para_op = " `desenho_para_op` = '', ";
        }
        
        if($_FILES['txt_desenho_para_op']['error'] == 1) {//Tratamento p/ Desenhos muito grandes ...
?>
    <Script Language = 'Javascript'>
        alert('ESSE DESENHO DE OP NÃO SERÁ UPADO !!!\n\nDESENHO DE OP MUITO PESADO P/ SUBIR NO SERVIDOR !')
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
                        //echo "Não é possivel copiar a imagem";
                    break;
                }
                $campo_desenho_para_op = " `desenho_para_op` = '$desenho_para_op', ";
            }
        }
        
/*Se o Usuário habilitou a opção de excluir a Imagem ou então ele está fazendo a substituição de uma
Imagem por outra, então eu excluo a imagem atual do servidor ...*/
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
                    //echo "Não é possivel copiar a imagem";
                break;
            }
            $campo_desenho_para_etiqueta = " `desenho_para_etiqueta` = '$desenho_para_etiqueta', ";
        }
        
/*Se o Usuário habilitou a opção de excluir a Imagem ou então ele está fazendo a substituição de uma
Imagem por outra, então eu excluo a imagem atual do servidor ...*/
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
                    //echo "Não é possivel copiar a imagem";
                break;
            }
            $campo_desenho_para_conferencia = " `desenho_para_conferencia` = '$desenho_para_conferencia', ";
        }
/*************************************************************/
        /*Se o PA: 

        * Não possui Código de Barra;
        * Normal de Linha;
        * Famílias Diferentes de Componente / Mão Obra;
        * SEM Blank na Discriminação;
         * 

        Então existirá Código de Barras ...*/
        if($codigo_barra == '' && $_POST['txt_referencia'] != 'ESP' && ($_POST['id_familia'] != 23 && $_POST['id_familia'] != 24 && $_POST['id_familia'] != 25) && strpos($_POST['txt_discriminacao'], 'BLANK') === false) {
            //Busco o Primeiro N.º de Código de Barras que esteja disponível na Tabela "codigos_barras" ...
            $sql = "SELECT `codigo_barra` 
                    FROM `codigos_barras` 
                    WHERE `usado` = 'N' LIMIT 1 ";
            $campos_codigo_barra = bancos::sql($sql);
            
            if(count($campos_codigo_barra) == 1) {//Significa que existe 1 código de Barra disponível ...
                $codigo_barra = $campos_codigo_barra[0]['codigo_barra'];
            }else {//Significa que já não temos mais códigos disponíveis ...
                echo 'CHEGAMOS AO LIMITE DE 9.999 CÓDIGO DE BARRA(S).';
                exit;
                //Teria que gerar o 10000 ???
                
                /*$codigo_barra = producao::gerador_codigo_barra($id_produto_acabado);
                /*Essa é uma garantia de que estou trabalhando exatamente com 13 dígitos, às vezes o sistema pode
                gerar com 14 dígitos o código devido estourar o limite de códigos que ainda hoje é de 9999
                o que seria um erro ...
                $codigo_barra = substr($codigo_barra, 0, 13);*/
            }
            
            /*Verifico se esse Código de Barras que acabou de ser gerado acima, já foi utilizado 
            por algum PA no sistema ...*/
            $sql = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `codigo_barra` = '$codigo_barra' LIMIT 1 ";
            $campos_codigo_barra = bancos::sql($sql);
            if(count($campos_codigo_barra) == 0) {//Atualizo os Dados do PA com o Novo Código de Barras ...
                $sql = "UPDATE `produtos_acabados` SET `codigo_barra` = '$codigo_barra' WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                bancos::sql($sql);
                
                /*Atualizo a Tabela "codigos_barras" marcando o campo "usado" como sendo = 'S' p/ que este 
                N.º não seja sugerido futuramente ...*/
                $sql = "UPDATE `codigos_barras` SET `usado` = 'S' WHERE `codigo_barra` = '$codigo_barra' LIMIT 1 ";
                bancos::sql($sql);
            }else {
?>
                <Script Language = 'JavaScript'>
                    alert('CÓDIGO DE BARRA(S) JÁ EXISTENTE !')
                </Script>
<?
            }
        }
//Atualizando Dados do PA ...
        $sql = "UPDATE `produtos_acabados` SET `id_gpa_vs_emp_div` = '$_POST[cmb_gpas_vs_emps_divs]', `id_unidade` = '$_POST[cmb_unidade]' , `id_funcionario` = '$_SESSION[id_funcionario]', `operacao` = '$_POST[cmb_operacao]', `operacao_custo` = '$_POST[cmb_operacao_custo]', `operacao_custo_sub` = '$_POST[cmb_operacao_custo_sub]', `origem_mercadoria` = '$_POST[cmb_origem_mercadoria]', `id_nivel` = '', `codigo_fornecedor` = '$_POST[txt_codigo_fornecedor]', `referencia` = '$_POST[txt_referencia]', `discriminacao` = '$_POST[txt_discriminacao]', `altura` = '$_POST[txt_altura]', `pecas_por_jogo` = '$_POST[txt_pecas_por_jogo]', `largura` = '$_POST[txt_largura]', `comprimento` = '$_POST[txt_comprimento]', $campo_desenho_para_op $campo_desenho_para_etiqueta $campo_desenho_para_conferencia `fci_albafer` = '$_POST[txt_fci_albafer]', `fci_tool_master` = '$_POST[txt_fci_tool_master]', `observacao` = '$_POST[txt_observacao_produto]', `status_custo` = '$status_custo', `explodir_view_estoque` = '$_POST[chkt_explodir_view_estoque]', `status_nao_produzir` = '$_POST[chkt_status_nao_produzido_temp]' $campo_desenho WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
        bancos::sql($sql);
        genericas::atualizar_pas_no_site_area_cliente($_POST['id_produto_acabado']);
        
//Essa variável vem herdada do passo anterior, se existe = 1, significa que o Pa tem relação com o PI
        if($existe == 1) {
            $data_sys = date('Y-m-d H:i:s');
            $sql = "UPDATE `produtos_insumos` SET `id_unidade` = '$_POST[cmb_unidade]', `discriminacao` = '$_POST[txt_discriminacao]', `altura` = '$_POST[txt_altura]', `largura` = '$_POST[txt_largura]', `comprimento` = '$_POST[txt_comprimento]', `observacao` = '$_POST[txt_observacao_produto]', `data_sys` = '$data_sys' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            bancos::sql($sql);
        }
        $valor = 1;
    }else {
        $valor = 2;
    }
//Significa que essa tela é um Pop-UP, então volta para a parte de alteração do produto novamente
    if($pop_up == 1) {
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php?passo=1&id_produto_acabado=<?=$_POST[id_produto_acabado];?>&pop_up=<?=$pop_up;?>&valor=<?=$valor;?>'
    </Script>
<?
//Significa que é uma tela normal, então volta para a parte de Filtro para poder consultar novamente
    }else {
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&id_produto_acabado=<?=$_POST['id_produto_acabado'];?>&valor=<?=$valor;?>'
    </Script>
<?
    }
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
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
            <?=genericas::order_by('ed.razaosocial', ' / (E.D.)', 'Empresa Divisão', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.referencia', 'Ref.', 'Referência', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.discriminacao', 'Discriminação', '', $order_by, '../../../../');?>
        </td>
        <td>
            Top
        </td>
        <td>
            <?=genericas::order_by('pa.operacao_custo', 'O. C.', 'Operação de Custo', $order_by, '../../../../');?>
        </td>
        <td>
            <?=genericas::order_by('pa.operacao', 'O. F.', 'Operação (Fat)', $order_by, '../../../../');?>
        </td>
        <td>
            Origem - ST
        </td>
        <td>
            <p title='Classificação Fiscal'>C. F.</p>
        </td>
        <td>
            Código<br>Barra
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
            <font title='Peças por Embalagem' style='cursor:help'>
                P.E.
            </font>
        </td>
        <td>
            M.M.V
        </td>
        <td>
            <?=genericas::order_by('pa.peso_unitario', 'P. U.', 'Peso Unitário', $order_by, '../../../../');?>
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
            <p title='Dimensões da Embalagem'>
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
            Observação
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
//Aki é a Marcação de PA Migrado
            if($campos[$i]['pa_migrado'] == 1) echo '<font color="red" title="PA MIGRADO" style="cursor:help"><b>MIG</b></font>';
        ?>
        <td align='right'>
        <?
            if($campos[$i]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
            }else if($campos[$i]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
            }
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
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
                <p title="Industrialização (c/ IPI)">I - C</p>
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
//Busca a Classificação do P.A.
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
            //Faz a Verificação no Custo do PA <- Etapa 1 - busco qual é a embalagem principal do PA ...
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