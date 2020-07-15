<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/compras_new.php');
require('../../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/conversoes/consultar.php', '../../../../../');

$mensagem[1] = 'ITEM DE CONVERSÃO INCLUIDO COM SUCESSO !';
$mensagem[2] = 'ITEM DE CONVERSÃO JÁ EXISTENTE !';
$mensagem[3] = 'ITEM DE CONVERSÃO ALTERADO COM SUCESSO !';
$mensagem[4] = 'ITEM DE CONVERSÃO EXCLUIDO COM SUCESSO !';
$mensagem[5] = 'NÃO HÁ ITENS PARA ESSA CONVERSÃO';

//Exclusão dos Itens $id_conversoes_temps ...
if($passo == 1) {
    //Busca do $id_conversoes_temps p/ passar por parâmetro ...
    $sql = "SELECT `id_conversoes_temps` 
            FROM `itens_conversoes_temps` 
            WHERE `id_item_conversoes_temps` = '$id_item_conversoes_temps' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $id_conversoes_temps    = $campos[0]['id_conversoes_temps'];

    $sql = "DELETE FROM `itens_conversoes_temps` WHERE `id_item_conversoes_temps` = '$id_item_conversoes_temps' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'Javascript'>
        parent.itens.document.location = 'itens.php?id_conversoes_temps=<?=$id_conversoes_temps;?>&valor=4'
        parent.rodape.document.form.submit()
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Itens de Convers&otilde;es ::.</title>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function igualar(indice) {
    var controle = 0, existe = 0, liberado = '', codigo = '', cont = 0
    var elemento = '', objeto = ''
    for(i = 0; i < parent.itens.document.form.elements.length; i++) {
        if(parent.itens.document.form.elements[i].type == 'radio') cont ++
    }
    
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'hidden' && document.form.elements[i].name == 'opt_item') existe ++
    }
    
    if(cont > 1) {
        elemento = parent.itens.document.form.opt_item[indice].value
        objeto = parent.itens.document.form.opt_item[indice]
    }else {
        if(existe == 0) {
            elemento = parent.itens.document.form.opt_item.value
            objeto = parent.itens.document.form.opt_item
        }else {
            elemento = parent.itens.document.form.opt_item[indice].value
            objeto = parent.itens.document.form.opt_item[indice]
        }
    }

    if(objeto.type == 'radio') {
        for(i = 0; i < elemento.length; i ++) {
            if(elemento.charAt(i) == '|') {
                controle ++
            }else {
                if(controle == 1) {
                    liberado = liberado + elemento.charAt(i)
                }else {
                    codigo = codigo + elemento.charAt(i)
                }
            }
        }
        parent.itens.document.form.opt_item_principal.value = codigo
    }else {
        limpar_radio()
    }
}

function limpar_radio() {
    for(i = 0; i < parent.itens.document.form.elements.length; i++) {
        if(parent.itens.document.form.elements[i].type == 'radio') {
            parent.itens.document.form.elements[i].checked = false
        }
    }
}
</Script>
</head>
<body>
<form name='form'>
<?
    //Busca dos Itens de Conversão passado por parâmetro ...
    $sql = "SELECT ict.*, qa.`nome`, ga.`nome` AS geometria_aco 
            FROM `itens_conversoes_temps` ict 
            INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = ict.`id_produto_insumo` 
            INNER JOIN `qualidades_acos` qa ON qa.`id_qualidade_aco` = pia.`id_qualidade_aco` 
            INNER JOIN `geometrias_acos` ga ON ga.`id_geometria_aco` = pia.`id_geometria_aco` 
            WHERE ict.`id_conversoes_temps` = '$id_conversoes_temps' ORDER BY ict.`id_item_conversoes_temps` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
<table width='90%' border='0' cellspacing='1' cellpadding='0' align='center'>
    <tr class='erro'>
        <td align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size="-1" color="#FF0000">
                <b>Conversão
                    <font face='Verdana, Arial, Helvetica, sans-serif' size="-1" color="blue">
                        <?=$id_conversoes_temps;?>
                    </font>
                    n&atilde;o cont&eacute;m itens cadastrado.
                </b>
            </font>
        </td>
    </tr>
</table>
<?
    }else {
?>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            <font size='3'>
                Conversão N.º
                <font color='yellow'>
                    <?=$id_conversoes_temps;?>
                </font>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Itens
        </td>
        <td>
            Produto Insumo
        </td>
        <td>
            A&ccedil;o
        </td>
        <td>
            Geometria do A&ccedil;o
        </td>
        <td>
            Medida 1 MM
        </td>
        <td>
            Medida 2 MM
        </td>
        <td>
            Qtde Mts.
        </td>
        <td>
            Dens Kg/M
        </td>
        <td>
            Peso Cor. Kg
        </td>
        <td>
            Peso + 2% Adm. Kg
        </td>
        <td>
            Preço/Kg
        </td>
        <td>
            Preço/M
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $id_produto_insumo  = $campos[$i]['id_produto_insumo'];
?>
    <tr class='linhanormal' onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='radio' name='opt_item' onclick="options('form', 'opt_item', '<?=$i;?>', '#E8E8E8')" value="<?=$campos[$i]['id_item_conversoes_temps'];?>">
        </td>
        <?
            $sql = "SELECT g.`referencia`, pi.`discriminacao` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    WHERE pi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            $campos_discriminacao = bancos::sql($sql);
        ?>
        <td align='left'>
        <?
            echo genericas::buscar_referencia($id_produto_insumo, $campos_discriminacao[0]['referencia']).' * ';
            echo $campos_discriminacao[0]['discriminacao'];
        ?>
        </td>
        <?
            $densidade_kg_por_m = compras_new::calcular_densidade('', $campos[$i]['id_item_conversoes_temps']);

            $qtde_metros 	= $campos[$i]['qtde'];
            $peso_correto 	= $qtde_metros * $densidade_kg_por_m;
            $peso_2 		= $peso_correto * 1.02;
        ?>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['geometria_aco'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['medida1'], 2, ',', '.');?>
        </td>
        <td>
        <?
            if($campos[$i]['medida2'] == '0.00') {
                echo '&nbsp;';
            }else {
                echo number_format($campos[$i]['medida2'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
            <?=number_format($qtde_metros, 3, ',', '.');?>
        </td>
        <td>
            <?=number_format($densidade_kg_por_m, 3, ',', '.');?>
        </td>
        <td>
            <?=number_format($peso_correto, 3, ',', '.');?>
        </td>
        <td>
            <?=number_format($peso_2, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['preco_kg'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['preco_m'], 2, ',', '.');?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            &nbsp;
        </td>
    </tr>
</table>
<?
    }
?>
<!--Não me lembro desses hiddens aki (rsrs)-->
<input type='hidden' name='opt_item'>
<input type='hidden' name='opt_item_principal'>
<!-- ******************************************** -->
<input type='hidden' name='id_conversoes_temps' value='<?=$id_conversoes_temps;?>'>
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