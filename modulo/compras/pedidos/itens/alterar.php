<?
require('../../../../lib/segurancas.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/data.php');//Essa biblioteca é usada dentro da Intermodular por isso não posso arrancar ...
require('../../../../lib/estoque_acabado.php');//Essa biblioteca é usada dentro da Intermodular por isso não posso arrancar ...
require('../../../../lib/genericas.php');//Essa biblioteca é usada dentro da Intermodular por isso não posso arrancar ...
require('../../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>ITEM(NS) ATUALIZADO(S) COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>ITEM(NS) JÁ EXISTENTE(S).</font>";

if(!empty($_POST['id_item_pedido'])) {
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_fornecedor             = (!empty($_POST[cmb_fornecedor])) ? "'".$_POST[cmb_fornecedor]."'" : 'NULL';
    $cmb_fornecedor_terceiro    = (!empty($_POST[cmb_fornecedor_terceiro])) ? "'".$_POST[cmb_fornecedor_terceiro]."'" : 'NULL';

//Aqui eu sempre altero o "$_POST[id_item_pedido]" passado por parâmetro ...
    $sql = "UPDATE `itens_pedidos` SET `id_fornecedor` = $cmb_fornecedor, `id_fornecedor_terceiro` = $cmb_fornecedor_terceiro, `preco_unitario` = '$_POST[txt_preco_unitario]', qtde = '$_POST[txt_qtde]', marca = '$_POST[txt_marca]', estocar = '$_POST[chkt_estocar]' WHERE `id_item_pedido` = '$_POST[id_item_pedido]' LIMIT 1 ";
    bancos::sql($sql);
    //Essa função atualiza o Status do Item do Pedido e do próprio Pedido em Questão ...
    compras_new::pedido_status($_POST['id_item_pedido']);
    //Aqui verifico se o PI é um PA "PIPA" para poder executar a função abaixo ...
    $sql = "SELECT id_produto_acabado 
            FROM `produtos_acabados` 
            WHERE `id_produto_insumo` = '$_POST[hdd_produto_insumo]' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_pipa = bancos::sql($sql);
    if(count($campos_pipa) == 1) intermodular::gravar_campos_para_calcular_margem_lucro_estimada($_POST[hdd_produto_insumo]);
    $valor = 1;
}

if(empty($posicao)) $posicao = 1;

//Seleção da qtde de Item(ns) existente(s) no Pedido de Compras
$sql = "SELECT COUNT(`id_pedido`) AS qtde_itens 
        FROM `itens_pedidos` 
        WHERE `id_pedido` = '$id_pedido' ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

//Seleção de Dados do Item de Pedido Corrente 
$sql = "SELECT g.`referencia`, ip.*, pi.`estocagem`, pi.`discriminacao`, pi.`observacao`, u.`sigla` 
        FROM `itens_pedidos` ip 
        INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo 
        INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
        INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
        WHERE ip.`id_pedido` = '$id_pedido' ORDER BY pi.discriminacao, ip.id_item_pedido ";
$campos 		= bancos::sql($sql, ($posicao - 1), $posicao);
$id_item_pedido		= $campos[0]['id_item_pedido'];
$id_produto_insumo	= $campos[0]['id_produto_insumo'];
$id_fornecedor_item	= $campos[0]['id_fornecedor'];
$id_fornecedor_terceiro	= $campos[0]['id_fornecedor_terceiro'];
$referencia 		= $campos[0]['referencia'];
$estocagem 		= $campos[0]['estocagem'];
$discriminacao 		= $campos[0]['discriminacao'];
$observacao 		= $campos[0]['observacao'];
$unidade                = $campos[0]['sigla'];
$valor_total 		= $campos[0]['valor_total'];
$qtde                   = number_format($campos[0]['qtde'], 2, ',', '.');
$marca                  = $campos[0]['marca'];
$ipi                    = $campos[0]['ipi'];
$preco                  = number_format($campos[0]['preco_unitario'], 2, ',', '.');
$desconto_especial 	= $campos[0]['desconto_especial'];
$estocar                = $campos[0]['estocar'];
$status_item            = $campos[0]['status'];

/******************Dados de Cabeçalho do Pedido******************/
//Aqui eu busco alguns Dados de Cabeçalho do Pedido ...
$sql = "SELECT f.id_fornecedor, f.razaosocial, concat(tm.simbolo, ' - ', tm.moeda) AS moeda, p.prazo_pgto_a, p.prazo_pgto_b, p.prazo_pgto_c, p.desconto_especial_porc, p.tipo_nota, 
        p.tipo_export, p.material_retirado_nosso_estoque 
        FROM `pedidos` p 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor AND f.ativo = '1' 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = p.id_tipo_moeda 
        WHERE p.`id_pedido` = '$id_pedido' LIMIT 1 ";
$campos_cabecalho               = bancos::sql($sql);
$prazo_pgto_a                   = $campos_cabecalho[0]['prazo_pgto_a'];
$prazo_pgto_b                   = $campos_cabecalho[0]['prazo_pgto_b'];
$prazo_pgto_c                   = $campos_cabecalho[0]['prazo_pgto_c'];
$desconto_especial_porc         = number_format($campos_cabecalho[0]['desconto_especial_porc'], 2, ',', '.');
$material_retirado_nosso_estoque = $campos_cabecalho[0]['material_retirado_nosso_estoque'];

$total_prazos_cabecalho = $prazo_pgto_a + $prazo_pgto_b + $prazo_pgto_c;//Utilizo essa variável p/ retornar Alert
$id_fornecedor          = $campos_cabecalho[0]['id_fornecedor'];
$razaosocial            = $campos_cabecalho[0]['razaosocial'];
$moeda                  = $campos_cabecalho[0]['moeda'];

//Tratamento com o Tipo de Cabeçalho ...
if($campos_cabecalho[0]['tipo_nota'] == 1) {
    $tipo_cabecalho = 'NF';//Utilizo essa variável p/ retornar Alert
}else {
    $tipo_cabecalho = 'SGD';//Utilizo essa variável p/ retornar Alert
}
/****************************************************************/

//Busca de + alguns do Produto mas daquele fornecedor escolhido na Lista de Preços
$sql = "SELECT * 
        FROM `fornecedores_x_prod_insumos` 
        WHERE `id_fornecedor` = '$id_fornecedor' 
        AND `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
$campos_lista_preco = bancos::sql($sql);
if(count($campos_lista_preco) == 1) {
    $prazo_ddl      = str_replace('.', ',', $campos_lista_preco[0]['prazo_pgto_ddl']);
    $forma_compra   = $campos_lista_preco[0]['forma_compra'];
    if($forma_compra == 1) {
        $compra = 'FAT/NF';
        $tipo_item = 'NF';//Utilizo essa variável p/ retornar Alert
        $compra_item = 'FAT';//Utilizo essa variável p/ retornar Alert
    }else if($forma_compra == 2) {
        $compra = 'FAT/SGD';
        $tipo_item = 'SGD';//Utilizo essa variável p/ retornar Alert
        $compra_item = 'FAT';//Utilizo essa variável p/ retornar Alert
    }else if($forma_compra == 3) {
        $compra = 'AV/NF';
        $tipo_item = 'NF';//Utilizo essa variável p/ retornar Alert
        $compra_item = 'AV';//Utilizo essa variável p/ retornar Alert
    }else if($forma_compra == 4) {
        $compra = 'AV/SGD';
        $tipo_item = 'SGD';//Utilizo essa variável p/ retornar Alert
        $compra_item = 'AV';//Utilizo essa variável p/ retornar Alert
    }
    //Será utilizado mais abaixo nos cálculos em JavaScript ...
    if($campos_cabecalho[0]['tipo_export'] == 'N') {
        $preco_aux = $campos_lista_preco[0]['preco'];
    }else if($campos_cabecalho[0]['tipo_export'] == 'E') {
        $preco_aux = $campos_lista_preco[0]['preco_exportacao'];
    }else if($campos_cabecalho[0]['tipo_export'] == 'I') {
        $preco_aux = $campos_lista_preco[0]['preco_faturado_export'];
    }
}

//Verifico se o PI é do Tipo PA, vai me auxiliar para a função em JavaScript desabilitar_combo()
$sql = "SELECT id_produto_acabado 
        FROM `produtos_acabados` 
        WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
$campos_pipa = bancos::sql($sql);
if(count($campos_pipa) == 1) {//Significa que é PRAC
    $is_prac = 1;
}else {//Não é PRAC
    $is_prac = 0;
}

//Aqui eu busco a Qtde que já foi importada desse item de Pedido em Nota Fiscal ...
$sql = "SELECT SUM(qtde_entregue) AS qtde_importada 
        FROM `nfe_historicos` 
        WHERE `id_item_pedido` = '$id_item_pedido' ";
$campos_nfe_item    = bancos::sql($sql);
$qtde_entregue_nf   = intval($campos_nfe_item[0]['qtde_importada']);
?>
<html>
<head>
<title>.:: Alterar Itens do Pedido N.º&nbsp;<?=$id_pedido;?> ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar(posicao, verificar) {
/*Aqui significa que estou submetendo o formulário através do botão submit, sendo
faz requisição das condições de validação*/
    if(typeof(verificar) != 'undefined') {
//IPI
        if(document.form.txt_ipi.value != '') {
            if(!texto('form', 'txt_ipi', '1', '0123456789', 'IPI', '2')) {
                return false
            }
        }
//Quantidade
        if(!texto('form', 'txt_qtde', '1', '1234567890,.-', 'QUANTIDADE', '1')) {
            return false
        }
//Controle com Quantidade = Zero ...
        var qtde = eval(strtofloat(document.form.txt_qtde.value))
        if(qtde == 0) {
            alert('QUANTIDADE INVÁLIDA !')
            document.form.txt_qtde.focus()
            document.form.txt_qtde.select()
            return false
        }
/*Nessa parte o Sistema verifica se a Qtde que está sendo digitada pelo Usuário é menor do que a Qtde 
que foi Importada em NF ...*/
        var qtde_entregue_nf    = eval('<?=$qtde_entregue_nf;?>')
        var qtde                = eval(strtofloat(document.form.txt_qtde.value))
        if(qtde >= 0) {//O sistema só fará essa verificação quando o usuário digitar valores positivos ...
            if(qtde < qtde_entregue_nf) {
                alert('QUANTIDADE INVÁLIDA !!!\n\nQUANTIDADE DIGITADA MENOR DO QUE A QUANTIDADE JÁ RECEBIDA EM NF !')
                document.form.txt_qtde.focus()
                document.form.txt_qtde.select()
                return false
            }
        }
//Se essa opção estiver marcada, significa que esta mercadoria varia para um Fornecedor Terceiro
        if(document.form.chkt_estocar.checked == false && document.form.chkt_estocar.disabled == false) {
            if(document.form.cmb_fornecedor_terceiro.value == '') {
                alert('SELECIONE O FORNECEDOR TERCEIRO !')
                document.form.cmb_fornecedor_terceiro.focus()
                return false
            }
        }
    }
    var tipo_cabecalho          = '<?=$tipo_cabecalho;?>'
    var tipo_item               = '<?=$tipo_item;?>'
    var compra_item             = '<?=$compra_item;?>'
    var total_prazos_cabecalho  = eval('<?=$total_prazos_cabecalho;?>')
    var erro_prazos             = 0
//Se a Compra do Item é a Vista e Existir Prazos no Cabeçalho do Pedido de Compras ...
    if(compra_item == 'AV' && total_prazos_cabecalho > 0) {//Está incoerente ...
        erro_prazos = 1
    }
/*Se a Forma de Compra do Cabeçalho do Pedido for Diferente da Forma de Compra do Item do Pedido, 
e a Forma de Pagamento do Cabeçalho do Pedido estiver incompatível coma Forma de Pagamento
do Item do Pedido então o Sistema retorna essa mensagem apenas informando o Usuário, + prossegue 
a rotina normalmente ...*/
    if((tipo_cabecalho != tipo_item) && erro_prazos == 1) {
        alert('A CONDIÇÃO DE COMPRA DO CABEÇALHO ESTÁ INCOMPATÍVEL COM A CONDIÇÃO DE COMPRA DA LISTA DE PREÇO DESTE FORNECEDOR !!!')
    }
/************************************************************************************************/
//Aqui desabilita esses campos para poder gravar no BD
    document.form.txt_preco_unitario.disabled = false
    limpeza_moeda('form', 'txt_qtde, txt_preco_unitario, ')
//Recupera a posição corrente no hidden, para não dar erro de paginação
    document.form.posicao.value = posicao
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
//Submetendo o Formulário
    document.form.submit()
}

function calcular() {
    var qtde = eval(strtofloat(document.form.txt_qtde.value))
    var preco = eval(strtofloat(document.form.txt_preco_unitario.value))
    var ipi = document.form.txt_ipi.value
//Se a Qtde estiver preenchida, ....
    if(qtde != '') {
//Se não existir Preço
        if(document.form.txt_preco_unitario.value == '') {
            document.form.txt_valor_total.value = ''
//Se existir Preço, ....
        }else {
            document.form.txt_valor_total.value = preco * qtde
            if(document.form.txt_valor_total.value == 'NaN') {
                document.form.txt_valor_total.value = ''
            }else {
                document.form.txt_valor_total.value = 'R$ '+arred(document.form.txt_valor_total.value, 2, 1)
            }
        }
//Se a Qtde não estiver digitada
    }else {
        document.form.txt_valor_total.value = ''
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}

function desabilitar_combo(is_prac) {
    if(is_prac == 1) {
        alert('ESSE PRODUTO É DO TIPO PRAC !\nDEVIDO A ISSO, ESTE É DO TIPO ESTOCÁVEL !!!')
        document.form.chkt_estocar.checked = true
        return false
    }else {
//Quando o Produto for estocável, significa que este vai ser p/ a Própria Empresa
        if(document.form.chkt_estocar.checked == true && document.form.chkt_estocar.disabled == false) {
            document.form.cmb_fornecedor_terceiro.disabled  = true
            document.form.cmb_fornecedor_terceiro.value     = ''
//Aki nesse caso, significa que estamos comprando o Produto para outro Fornecedor
        }else {
            document.form.cmb_fornecedor_terceiro.disabled  = false
            document.form.cmb_fornecedor_terceiro.value     = '<?=$id_fornecedor_terceiro;?>'
        }
    }
}

function calcular_desconto() {
    var desconto_perc                       = (document.form.txt_desconto_perc.value != 0) ? eval(strtofloat(document.form.txt_desconto_perc.value)) : 0
    var preco                               = eval('<?=$preco_aux;?>')
    document.form.txt_preco_unitario.value  = preco * (1 - desconto_perc / 100)
    document.form.txt_preco_unitario.value  = arred(document.form.txt_preco_unitario.value, 2, 1)
}
</Script>
</head>
<body onload='calcular();desabilitar_combo();document.form.txt_marca.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>', 1)">
<!--Aqui é para quando for submeter-->
<input type='hidden' name='id_pedido' value='<?=$id_pedido;?>'>
<input type='hidden' name='id_item_pedido' value='<?=$id_item_pedido;?>'>
<input type='hidden' name='hdd_produto_insumo' value='<?=$id_produto_insumo;?>'>
<!--Controle de Tela-->
<input type='hidden' name='posicao' value='<?=$posicao;?>'>
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellpadding='1' cellspacing ='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar Item(ns) do Pedido N.º 
            <font color='yellow'>
                <?=$id_pedido;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Fornecedor:</b>
        </td>
        <td>
            <?=$razaosocial;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Produto Insumo:</b>
        </td>
        <td>
        <?
            $referencia_funcao = genericas::buscar_referencia($id_produto_insumo, $referencia, 0);
            echo $referencia_funcao.' * '.$discriminacao;
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Desconto %:</b>
        </td>
        <td>
            <?
                //Com o Desconto Especial, não podemos dar % de Desconto manual, pois ja é concedido um pelo Cabeçalho do Pedido ...
                if($desconto_especial == 'S') {
                    $class_desconto_perc    = 'textdisabled';
                    $disabled_desconto_perc = 'disabled';
                }else {
                    $class_desconto_perc = 'caixadetexto';
                }
            ?>
            <input type='text' name='txt_desconto_perc' size='5' maxlength='4' onkeyup="verifica(this, 'moeda_especial', '1', '', event);calcular_desconto()"  class='<?=$class_desconto_perc;?>' <?=$disabled_desconto_perc?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Preço:</b>
        </td>
        <td align='right'>
            <?=$moeda;?>
        </td>
        <td>
            <input type='text' name="txt_preco_unitario" value="<?=$preco;?>" size="12" maxlength="10" class='textdisabled' disabled>
            <?
                if(!empty($unidade)) echo '&nbsp;/&nbsp;'.$unidade;
                if($desconto_especial == 'S') {//Foi dado o Desconto
            ?>
                    <font color="blue">
                    - Com Desconto Especial de <b><?=$desconto_especial_porc;?></b> %.
                    </font>
            <?
                }
                    //Somente aparece para os Logins do Roberto e Dárcio porque programa ...
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
            ?>
            <img src="../../../../imagem/cifrao.png" title="Alterar Preço" alt="Alterar Preço" width="20" heigth="20" onclick="nova_janela('alterar_preco.php?id_item_pedido=<?=$id_item_pedido;?>', 'ALTERAR_PRECO', '', '', '', '', 160, 600, 'c', 'c', '', '', 's', 's', '', '', '')" border="0">
            <?
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Prazo de Pagto:</b>
        </td>
        <td>
            <input type='text' name="txt_prazo_entrega" value="<?=$prazo_ddl;?>" size="12" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Forma de Compra:</b>
        </td>
        <td>
            <input type='text' name='txt_forma_compra' size='12' value='<?=$compra;?>' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>IPI:</b>
        </td>
        <td>
            <input type='text' name='txt_ipi' value='<?=$ipi;?>' size='12' maxlength='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Quantidade:</b>
        </td>
        <td>
        <?
            //Quando o Material for retirado do nosso Estoque, esse campo só aceitará números negativos ...
            $operador = ($material_retirado_nosso_estoque == 'S') ? 2 : 1;
        ?>
            <input type='text' name='txt_qtde' value='<?=$qtde;?>' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '<?=$operador;?>', event);calcular()" onblur='document.form.txt_marca.focus()' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Valor Total:</b>
        </td>
        <td>
            <input type='text' name="txt_valor_total" size="20" maxlength="15" value="<?='R$ '.str_replace('.', ',', $valor_total);?>" class='textdisabled' disabled><?=$moeda;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Marca / Obs:</b>
        </td>
        <td>
            <input type='text' name='txt_marca' value='<?=$marca;?>' size='52' maxlength='50' class='caixadetexto'>
        </td>
    </tr>
    <?
            if($estocagem == 'N') {//PI do Tipo "NÃO Estocável" ...
                $disabled = 'disabled';
            }else {//Se for Estocável
                $disabled = '';
            }
    ?>
    <tr class='linhanormal'>
            <td colspan='2'>Estocagem:</td>
            <td>
            <?
                if($estocar == 1) $checked = 'checked';
            ?>
                    <input type="checkbox" name="chkt_estocar" value="1" title="Estocar" id="label" onclick="desabilitar_combo('<?=$is_prac;?>')" class="checkbox" <?=$checked;?> <?=$disabled;?>>
                    <label for="label">
                    <?
                        if($estocagem == 'N') {//PI do Tipo "NÃO Estocável" ...
                            echo 'ESSE PI É DO TIPO NÃO ESTOCÁVEL';
                        }else {//Se for Estocável
                            echo 'ESTOCAR ESSE PRODUTO';
                        }
                    ?>
                    </label>
                    -&nbsp;<b>Enviar para:</b>
                    <select name="cmb_fornecedor_terceiro" title="Selecione o Fornecedor Terceiro" class='combo' disabled>
                    <?
                            $sql = "Select id_fornecedor, razaosocial 
                                            from fornecedores 
                                            where material_ha_debitar = 1 
                                            and ativo = 1 order by razaosocial ";
                            echo combos::combo($sql, $id_fornecedor_terceiro);
                    ?>
                    </select>
            </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>Debitar do Fornecedor:</td>
        <td>
                <select name="cmb_fornecedor" title="Selecione o Fornecedor" class='combo'>
                <?
                        $sql = "Select id_fornecedor, razaosocial 
                                        from fornecedores 
                                        where material_ha_debitar = 1 
                                        and ativo = 1 order by razaosocial ";
                        echo combos::combo($sql, $id_fornecedor_item);
                ?>
                </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Obs do Produto:</b>
        </td>
        <td>
            <textarea name="txt_observacao" title="Observação" cols="50" rows="3" class='textdisabled' disabled><?=$observacao;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_redefinir' value='Redefinir' onclick="redefinir('document.form','REDEFINIR');calcular();desabilitar_combo();document.form.txt_marca.focus()" title="Redefinir" style='color:#ff9900' class='botao'>
            <?
                //Se o Item estiver "Em Aberto" ou Parcialmente Importado em Nota Fiscal, ainda posso alterar dados ...
                if($status_item < 2) {
                    $class      = 'botao';
                    $disabled   = '';
                }else {//Já não posso mais alterar nada, item Totalmente Importado em Pedido ...
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                }
            ?>
            <input type='submit' name='cmd_salvar' value='Salvar' style='color:green' class='<?=$class;?>' <?=$disabled;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="return fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='3'>
        <?
/////////////////////////////// PAGINACAO CASO ESPECIFICA PARA ESTA TELA ///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='#' onclick='validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
                if($i % 40 == 0) echo '<br>';//Quebro a linha porque não estoura o limite da Tela ...

                if($i == $posicao) {
                    echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                }else {
                    echo "<b><a href='#' onclick='validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                }
            }
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='#' onclick='validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Próxima &gt;&gt; </font></a>&nbsp;</b>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>