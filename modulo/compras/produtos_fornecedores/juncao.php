<?
require('../../../lib/segurancas.php');
require('../../../lib/biblioteca.php');
require('../../../lib/genericas.php');//Essa biblioteca é utilizada dentro da Biblioteca 'intermodular' ...
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/index.php', '../../../');

$mensagem[1] = 'PRODUTO(S) INSUMO(S) INCLUIDO(S) PARA O(S) FORNECEDOR(ES) COM SUCESSO !';
$mensagem[2] = 'PRODUTO(S) INSUMO(S) JÁ EXISTENTE(S) PARA ESTE(S) FORNECEDOR(ES) !';

$vetor_fornecedores = biblioteca::controle_itens($id_fornecedor, $id_fornecedor2, $acao);
$vetor_produtos     = biblioteca::controle_itens($id_produto, $id_produto2, $acao);

if($passo == 1) {
    foreach($_POST['cmb_fornecedor'] as $id_fornecedor) {
        foreach($_POST['cmb_produto'] as $id_produto_insumo) {
//Aqui é a função que atrela vários PI para um fornecedor
            $retorno = intermodular::incluir_varios_pi_fornecedor($id_fornecedor, $id_produto_insumo);
            if($retorno != 0) {//Inseriu PI para Fornecedor
                $valor = 1;
            }else {//PI já existente
                $valor = 2;
            }
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'juncao.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
//Matriz de fornecedores
    if(!empty($vetor_fornecedores)) {
        $sql = "SELECT id_fornecedor, razaosocial 
                FROM `fornecedores` 
                WHERE `id_fornecedor` IN ($vetor_fornecedores) ORDER BY razaosocial ";
        $campos_fornecedores = bancos::sql($sql);
        $linhas_fornecedores = count($campos_fornecedores);
    }
//Matriz de produtos
    if(!empty($vetor_produtos)) {
        $sql = "SELECT pi.id_produto_insumo, IF(pa.id_produto_acabado > 0, CONCAT(pa.referencia, ' - ', pi.discriminacao), pi.discriminacao) AS discriminacao 
                FROM `produtos_insumos` pi 
                INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                LEFT JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pa.ativo = '1' 
                WHERE pi.id_produto_insumo IN ($vetor_produtos) 
                AND pi.`ativo` = '1' ORDER BY discriminacao ";
        $campos_produtos = bancos::sql($sql);
        $linhas_produtos = count($campos_produtos);
    }
?>
<html>
<head>
<title>.:: Fornecedor vs Produto Insumo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function retirar_fornecedor() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
    var flag = 0, fornec_sel = ''
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            if(document.form.elements[i].value == '') {
                if(flag == 0) alert('SELECIONE PELO MENOS UM FORNECEDOR !')
                document.form.elements[i].focus()
                return false
            }else {
                for(j = 0; j < document.form.elements[i].length; j ++) {
                    if(document.form.elements[i][j].selected == true) fornec_sel = fornec_sel + document.form.elements[i][j].value + ','
                }
            }
            flag++
        }
        i = document.form.elements.length
    }
    fornec_sel = fornec_sel.substr(0, fornec_sel.length - 1)

    document.form.id_fornecedor2.value = fornec_sel
    document.form.acao.value = 1
    document.form.passo.value = 0
    document.form.submit()
}

function retirar_produto() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
    var flag = 0, prod_sel = ''
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            if(document.form.elements[i + 1].value == '') {
                if(flag == 0) alert('SELECIONE PELO MENOS UM PRODUTO INSUMO !')
                document.form.elements[i + 1].focus()
                return false
            }else {
                for(j = 0; j < document.form.elements[i + 1].length; j ++) {
                    if(document.form.elements[i + 1][j].selected == true) prod_sel = prod_sel + document.form.elements[i + 1][j].value + ','
                }
            }
            flag++
        }
    }
    prod_sel = prod_sel.substr(0, prod_sel.length - 1)

    document.form.id_produto2.value = prod_sel
    document.form.acao.value = 1
    document.form.passo.value = 0
    document.form.submit()
}

function selecionar_todos_fornecedores() {
    var i, elementos = document.form.elements
    var selecionados = ''
    for (i = 0; i < elementos.length; i ++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(j = 1; j < document.form.elements[i].length; j++) document.form.elements[i][j].selected = true
        }
        i = elementos.length
    }
}

function selecionar_todos_produtos() {
    var elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            for(var j = 1; j < document.form.elements[i+1].length; j++) document.form.elements[i + 1][j].selected = true
        }
    }
}

function validar() {
    var cont = 0, flag = 0
    var perguntou = document.form.perguntou.value
    if(perguntou == 1) resposta = false
    if(perguntou == 0) var resposta = confirm('DESEJA INCLUIR TODOS OS FORNECEDORES PARA TODOS OS PRODUTOS INSUMOS ? ')
    
    if(resposta == true) {
        selecionar_todos_fornecedores()
        selecionar_todos_produtos()
        document.form.perguntou.value = 1
    }else {
        for(i = 0; i < document.form.elements.length; i++) {
            if(document.form.elements[i].type == 'select-multiple') {
                if(document.form.elements[i].value == '') {
                    if(flag == 0) {
                        alert('SELECIONE PELO MENOS UM FORNECEDOR !')
                    }else {
                        alert('SELECIONE PELO MENOS UM PRODUTO INSUMO !')
                    }
                    document.form.elements[i].focus()
                    document.form.perguntou.value = 1
                    return false
                }
                flag++
            }
        }
    }
    document.form.passo.value = 1
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar()">
<?
	if(!empty($vetor_fornecedores) || !empty($vetor_produtos)) {
?>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Fornecedor(es)
        </td>
        <td>
            Produto(s) Insumo(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <select name='cmb_fornecedor[]' size='15' class='combo' multiple>
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
            for($i = 0; $i < $linhas_fornecedores; $i++) {
        ?>
                <option value='<?=$campos_fornecedores[$i]['id_fornecedor']?>'><?=$campos_fornecedores[$i]['razaosocial']?></option>
        <?
            }
        ?>
            </select>
        </td>
        <td>
            <select name='cmb_produto[]' size='15' class='combo' multiple>
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
                for($i = 0; $i < $linhas_produtos; $i++) {
        ?>
                    <option value='<?=$campos_produtos[$i]['id_produto_insumo']?>'><?=$campos_produtos[$i]['discriminacao']?></option>
        <?
                }
        ?>
            </select>
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td>
        <?
            if(!empty($vetor_fornecedores)) {
        ?>
                <input type="button" name="cmd_selecionar" value="Selecionar Todos" title="Selecionar Todos" onclick="selecionar_todos_fornecedores()" class='botao'>
                <input type="button" name="cmd_retirar" value="Retirar" title="Retirar" onclick="retirar_fornecedor()" class='botao'>
        <?
            }
        ?>
        </td>
        <td>
        <?
            if(!empty($vetor_produtos)) {
        ?>
                <input type="button" name="cmd_selecionar2" value="Selecionar Todos" title="Selecionar Todos" onclick="selecionar_todos_produtos()" class='botao'>
                <input type="button" name="cmd_retirar2" value="Retirar" title="Retirar" onclick="retirar_produto()" class='botao'>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name="cmd_incluir" value='Incluir' title='Incluir' class='botao'>
        </td>
    </tr>
</table>
<?
	}
?>
<input type="hidden" name="id_fornecedor" value="<?=$vetor_fornecedores;?>">
<input type="hidden" name="id_fornecedor2" value="">
<input type="hidden" name="id_produto" value="<?=$vetor_produtos;?>">
<input type="hidden" name="id_produto2" value="">
<input type="hidden" name="passo">
<input type="hidden" name="perguntou">
<input type="hidden" name="acao">
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
?>