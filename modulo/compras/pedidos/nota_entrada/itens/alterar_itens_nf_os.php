<?
//O arquivo alterar.php que faz requisição a esse arquivo já tem dentro dele todas as libs embutidas ...
$mensagem[1] = "<font class='confirmacao'>ITEM(NS) ATUALIZADO(S) COM SUCESSO.</font>";

if(!empty($_POST['id_nfe_historico'])) {
//1)*******************************************Nota Fiscal********************************************/
//Atualizando os Itens na Parte de Nota Fiscal, apenas o campo qtde e a data mesmo ...
    $sql = "UPDATE `nfe_historicos` SET `qtde_entregue` = '$_POST[txt_total_entrada]', `valor_entregue` = '$_POST[txt_preco_unitario]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_nfe_historico` = '$_POST[id_nfe_historico]' LIMIT 1 ";
    bancos::sql($sql);
//Busca do id_item_pedido através do $id_nfe_historico, porque utilizo na função abaixo pedido_status()
    $sql = "SELECT id_item_pedido 
            FROM `nfe_historicos` 
            WHERE `id_nfe_historico` = '$_POST[id_nfe_historico]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_item_pedido = $campos[0]['id_item_pedido'];
    //compras_new::pedido_status($id_item_pedido);
//Chamo a função p/ fazer a divisão das parcelas pelo jeito de Vencimento ...
    compras_new::calculo_valor_financiamento($_POST['id_nfe']);
//2)***********************************************OS*************************************************/
//Atualizando os Itens na Parte de OS ...
    $sql = "SELECT id_os_item, id_os, qtde_entrada, peso_total_entrada 
            FROM `oss_itens` 
            WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_os              = $campos[0]['id_os'];
    $id_os_item         = $campos[0]['id_os_item'];//Vou utilizar esse id na Função ...
    $qtde_entrada       = $campos[0]['qtde_entrada'];
    $peso_total_entrada = $campos[0]['peso_total_entrada'];
//Retirando o Total de Entrada na Tabela de Itens de OSS referente a Nota Corrente na qual eu estou ...
    $sql = "UPDATE `oss_itens` SET `qtde_entrada` = `qtde_entrada` - '$qtde_entrada', `peso_total_entrada` = `peso_total_entrada` - '$peso_total_entrada' WHERE `id_os_item` = '$id_os_item' LIMIT 1 ";
    bancos::sql($sql);
//Atualizando os dados de Entrada na parte de OS ...
    $sql = "UPDATE `oss_itens` SET `qtde_entrada` = `qtde_entrada` + '$_POST[txt_qtde_entrada]', `dureza_fornecedor` = '$_POST[txt_dureza_fornecedor]', `dureza_interna` = '$_POST[txt_dureza_interna]', `peso_total_entrada` = `peso_total_entrada` + '$_POST[txt_total_entrada]' WHERE `id_os_item` = '$id_os_item' LIMIT 1 ";
    bancos::sql($sql);
//Essa função serve tanto para o Incluir, como Alterar e Excluir Item da Nota Fiscal ...
    producao::atualizar_status_item_os($id_os_item);
/*****************Controle com o Status da OS*****************/
    producao::atualizar_status_os($id_os);
/*************************************************************/
    $valor = 1;
}

//Procedimento quando carrega a Tela ...
$id_nfe = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_nfe'] : $_GET['id_nfe'];
if(empty($posicao)) $posicao = 1;

//Seleção da qtde de Item(ns) existente(s) na Nota Fiscal de Entrada
$sql = "SELECT COUNT(id_nfe) AS qtde_itens 
        FROM `nfe_historicos` 
        WHERE `id_nfe` = $id_nfe";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

//Através do $id_nfe eu busco qual é o id_item_pedido
$sql = "SELECT CONCAT(tm.`simbolo`, ' ') AS moeda, nfe.`num_nota`, nfeh.`id_nfe_historico`, 
        nfeh.`id_item_pedido`, nfeh.`valor_entregue` 
        FROM `nfe_historicos` nfeh 
        INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = nfe.`id_tipo_moeda` 
        WHERE nfeh.`id_nfe` = '$id_nfe' ";
$campos 		= bancos::sql($sql, ($posicao - 1), $posicao);
$moeda 			= $campos[0]['moeda'];
$num_nota 		= $campos[0]['num_nota'];
$id_nfe_historico 	= $campos[0]['id_nfe_historico'];
$id_item_pedido 	= $campos[0]['id_item_pedido'];
$valor_entregue_nf      = $campos[0]['valor_entregue'];

/*Aqui eu busco os dados da OS que esta atrelado ao Pedido que está atrelado a Nota Fiscal através do id_item_pedido
Nessa tela 90% dos dados que são exibidos, são dados diretos da OS ...*/
$sql = "SELECT oi.* 
        FROM `oss_itens` oi 
        WHERE `id_item_pedido` = '$id_item_pedido' 
        ORDER BY oi.id_os_item ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Itens de Nota Fiscal ::.</title>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Achei interessante comentar esse parâmetro peso_aco
/*É o peso do Aço do PI do Item de OS que eu passo para calcular o total de entrada em R$ 
que não é nada + que a qtde_entrada * peso_aco ...*/
function calcular_total_entrada_rs() {
//Qtde de Entrada
    var qtde_entrada    = (document.form.txt_qtde_entrada.value == '') ? 0 : eval(document.form.txt_qtde_entrada.value)
    var peso_aco        = eval('<?=$campos[0]["peso_unit_saida"]?>')
    var resultado       = String(qtde_entrada * peso_aco)//Gambiarra (rsrs)
    document.form.txt_peso_total_entrada.value = arred(resultado, 2, 1)
}

function controlar_cores_qtdes() {
//Qtde de Saída
    var qtde_saida      = eval('<?=$campos[0]["qtde_saida"];?>')
//Qtde de Entrada
    var qtde_entrada    = (document.form.txt_qtde_entrada.value == '') ? 0 : eval(document.form.txt_qtde_entrada.value)
//Se a Caixa de Qtde de Entrada estiver vazia então ...
    if(document.form.txt_qtde_entrada.value == '') {//Deixa a caixa na cor branca normalmente ...
        document.form.txt_qtde_entrada.style.background         = 'white'
        document.form.txt_qtde_entrada.style.color              = 'black'
//Se a Caixa de Qtde de Entrada estiver preenchida, vai para a parte de cálculo p/ controlar as cores ...
    }else {
        if(((qtde_entrada / qtde_saida) > 1.01) || ((qtde_entrada / qtde_saida) < 0.99)) {
            document.form.txt_qtde_entrada.style.background     = 'red'
            document.form.txt_qtde_entrada.style.color          = 'white'
        }else {
            document.form.txt_qtde_entrada.style.background     = 'white'
            document.form.txt_qtde_entrada.style.color          = 'black'
        }
    }
}

function controlar_cores_totais() {
//Total de Saída
    var total_saida     = eval('<?=$campos[0]["peso_total_saida"];?>')
//Total de Entrada
    var total_entrada   = (document.form.txt_total_entrada.value == '') ? 0 : eval(strtofloat(document.form.txt_total_entrada.value))
//Se a Caixa de Total de Entrada estiver vazia então ...
    if(document.form.txt_total_entrada.value == '') {//Deixa a caixa na cor branca normalmente ...
        document.form.txt_total_entrada.style.background        = 'white'
        document.form.txt_total_entrada.style.color             = 'black'
//Se a Caixa de Total de Entrada estiver preenchida, vai para a parte de cálculo p/ controlar as cores ...
    }else {
        if(((total_entrada / total_saida) > 1.01) || ((total_entrada / total_saida) < 0.99)) {
            document.form.txt_total_entrada.style.background    = 'red'
            document.form.txt_total_entrada.style.color         = 'white'
        }else {
            document.form.txt_total_entrada.style.background    = 'white'
            document.form.txt_total_entrada.style.color         = 'black'
        }
    }
}

function validar(posicao, verificar) {
/*Aqui significa que estou submetendo o formulário através do botão submit, sendo
faz requisição das condições de validação*/
    if(typeof(verificar) != 'undefined') {
//Quantidade de Entrada
        if(!texto('form', 'txt_qtde_entrada', '1', '1234567890', 'QUANTIDADE DE ENTRADA', '1')) {
            return false
        }
        if(document.form.txt_qtde_entrada.value == 0) {
            alert('QUANTIDADE DE ENTRADA INVÁLIDA !')
            document.form.txt_qtde_entrada.focus()
            document.form.txt_qtde_entrada.select()
            return false
        }
//Total de Entrada
        if(!texto('form', 'txt_total_entrada', '1', '1234567890,.', 'TOTAL DE ENTRADA', '2')) {
            return false
        }
        if(document.form.txt_total_entrada.value == 0) {
            alert('TOTAL DE ENTRADA INVÁLIDA !')
            document.form.txt_total_entrada.focus()
            document.form.txt_total_entrada.select()
            return false
        }
//Preço Unitário ...
        if(!texto('form', 'txt_preco_unitario', '1', '1234567890.,-', 'PREÇO UNITÁRIO', '2')) {
            return false
        }
        var preco_unitario_na_nf    = eval(strtofloat(document.form.txt_preco_unitario.value))
        var preco_unitario_na_os    = eval('<?=$campos[0]['preco_pi'];?>')
        //Nunca que o Preço Unitário da Nota Fiscal poderá ser maior do que o Preo Unitário da OS ...
        if(preco_unitario_na_nf > preco_unitario_na_os) {
            alert('PREÇO UNITÁRIO INVÁLIDO !!!')
            document.form.txt_preco_unitario.focus()
            document.form.txt_preco_unitario.select()
            return false
        }
//Dureza Interna
        if(document.form.txt_dureza_interna.value == '') {
            alert('DIGITE A DUREZA INTERNA !')
            document.form.txt_dureza_interna.focus()
            return false
        }
    }
/****************************Controle com os Alerts****************************/
//Variáveis que vão servir para o controle do alert mais abaixo
    var qtdes_invalidas     = 0
    var totais_invalidos    = 0
    //Qtde de Saída
    var qtde_saida          = eval('<?=$campos[0]["qtde_saida"];?>')
    //Qtde de Entrada
    var qtde_entrada        = (document.form.txt_qtde_entrada.value == '') ? 0 : eval(document.form.txt_qtde_entrada.value)
//Cálculo p/ controlar de Qtdes Inválidas ...
    if(((qtde_entrada / qtde_saida) > 1.01) || ((qtde_entrada / qtde_saida) < 0.99)) qtdes_invalidas++
/**********************************Controle com a Parte de Total**********************************/
    //Total de Saída
    var total_saida         = eval('<?=$campos[0]["peso_total_saida"];?>')
    //Total de Entrada
    var total_entrada       = (document.form.txt_total_entrada.value == '') ? 0 : eval(strtofloat(document.form.txt_total_entrada.value))
//Cálculo p/ controlar de Totais Inválidos ...
    if(((total_entrada / total_saida) > 1.01) || ((total_entrada / total_saida) < 0.99)) totais_invalidos++
//1)Se existir apenas Qtdes Inválidas ...
    if(qtdes_invalidas > 0 && totais_invalidos == 0) alert('A QTDE DE ENTRADA ESTÁ INVÁLIDA !')
//2)Se existir apenas Totais Inválidos ...
    if(qtdes_invalidas == 0 && totais_invalidos > 0) alert('O TOTAL DE ENTRADA ESTÁ INVÁLIDO !')
//3)Se existir Qtdes e Totais Inválidos ...
    if(qtdes_invalidas > 0 && totais_invalidos > 0) alert('A QTDE E TOTAL DE ENTRADA ESTÃO INVÁLIDO(S) !')
//Desabilito para poder Gravar no BD
    document.form.txt_peso_total_entrada.disabled = false
//Deixa no Formato em que o Banco de Dados vai reconhecer ...
    limpeza_moeda('form', 'txt_total_entrada, txt_preco_unitario, txt_peso_total_entrada, ')
    //Recupera a posição corrente no hidden, para não dar erro de paginação
    document.form.posicao.value = posicao
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
//Submetendo o Formulário
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        opener.parent.itens.document.form.submit()
        opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onload='document.form.txt_qtde_entrada.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>', 1)">
<!--Aqui é para quando for submeter-->
<input type='hidden' name='id_nfe' value="<?=$id_nfe;?>">
<input type='hidden' name='id_nfe_historico' value="<?=$id_nfe_historico;?>">
<!--Controle de Tela-->
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Itens de Nota Fiscal N.º
            <font color='yellow'>
                <?=$num_nota;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>N.º OP:</b>
        </td>
        <td>
            <?=$campos[0]['id_op'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Saída:</b>
        </td>
        <td>
            <?=$campos[0]['qtde_saida'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Qtde de Entrada:</b>
            </font>
        </td>
        <?
        //Cálculo p/ controlar o CSS da caixinha
            if((($campos[0]['qtde_entrada'] / $campos[0]['qtde_saida']) > 1.01) || (($campos[0]['qtde_entrada'] / $campos[0]['qtde_saida']) < 0.99)) {
                $backcolor  = 'background:red';//Aqui é quando o cálculo está irregular
                $color      = 'color:white';
            }else {//Aqui é quando o cálculo está Ok ...
                $backcolor  = 'background:white';
                $color      = 'color:brown';
            }
        ?>
        <td>
            <input type='text' name='txt_qtde_entrada' value='<?=$campos[0]['qtde_entrada'];?>' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};calcular_total_entrada_rs();controlar_cores_qtdes()" maxlength="10" size="12" style="<?=$backcolor.';'.$color;?>" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Dif. Qtde:</b>
        </td>
        <td>
        <?
//Comparação entre as 2 Quantidades - Faço controle de Cores ...
            if((($campos[0]['qtde_entrada'] / $campos[0]['qtde_saida']) > 1.01) || (($campos[0]['qtde_entrada'] / $campos[0]['qtde_saida']) < 0.99)) {
                $color = 'red';
            }else {
                $color = 'blue';
            }
            $resultado = $campos[0]['qtde_entrada'] - $campos[0]['qtde_saida'];
            echo "<font color=$color>".$resultado."</font>";
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Produto:</b>
        </td>
        <td>
        <?
            //Busca dos Produtos da OP agora através do id_op que está na OS ...
            $sql = "SELECT pa.id_produto_acabado, pa.referencia 
                    FROM `ops` 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
                    WHERE ops.`id_op` = '".$campos[0]['id_op']."' LIMIT 1 ";
            $campos_referencia = bancos::sql($sql);
            echo intermodular::pa_discriminacao($campos_referencia[0]['id_produto_acabado']);
//Aki eu printo se é Retrabalho na Frente da Discriminação ...
            if($campos[0]['retrabalho'] == 1) echo ' <font color="red"><b>RETRABALHO</b></font>';
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Total de Saída:</b>
        </td>
        <td>
            <?=number_format($campos[0]['peso_total_saida'], 3, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Total de Entrada:</b>
            </font>
        </td>
        <?
//Cálculo p/ controlar o CSS da caixinha
            if((($campos[0]['peso_total_entrada'] / $campos[0]['peso_total_saida']) > 1.01) || (($campos[0]['peso_total_entrada'] / $campos[0]['peso_total_saida']) < 0.99)) {
                $backcolor  = 'background:red';//Aqui é quando o cálculo está irregular
                $color      = 'color:white';
            }else {//Aqui é quando o cálculo está Ok ...
                $backcolor  = 'background:white';
                $color      = 'color:brown';
            }
        ?>
        <td>
            <!--Este aqui é o único campo em que eu leio da Tabela de Nota Fiscal-->
            <input type='text' name='txt_total_entrada' value="<?=number_format($campos[0]['peso_total_entrada'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event);controlar_cores_totais()" style="<?=$backcolor.';'.$color;?>" maxlength='10' size='12' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>CTT:</b>
        </td>
        <td>
        <?
            $sql = "SELECT CONCAT(u.sigla, ' - ', pi.discriminacao) AS dados 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
                    WHERE pi.`id_produto_insumo` = ".$campos[0]['id_produto_insumo_ctt']." LIMIT 1 ";
            $campos_produto_insumo = bancos::sql($sql);
            if(!empty($campos_produto_insumo[0]['dados'])) {
                echo $campos_produto_insumo[0]['dados'];
            }else {
                echo '&nbsp;';
            }
//Verifico se esse PI tem algum CTT, atrelado ...
            $sql = "SELECT ctts.id_ctt, ctts.codigo AS dados_ctt 
                    FROM `ctts` 
                    INNER JOIN `produtos_insumos` pi ON pi.id_ctt = ctts.id_ctt 
                    WHERE pi.`id_produto_insumo` = '".$campos[0]['id_produto_insumo_ctt']."' ";
            $campos_ctts = bancos::sql($sql);
            //Se encontrar CTT atrelado ao PI, então eu printo este ...
            if(count($campos_ctts) == 1) echo ' / <font color="darkblue">'.$campos_ctts[0]['dados_ctt'].'</font>';
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Preço Unit. R$:</b>
        </td>
        <td>
            <input type='text' name='txt_preco_unitario' value="<?=number_format($valor_entregue_nf, 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' class='caixadetexto'>
            &nbsp;
            <font color='brown'>
                <b>(Preço Unit. na OS R$ <?=number_format($campos[0]['preco_pi'], 2, ',', '.');?>)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Total Entrada R$:</b>
        </td>
        <td>
        <?
            $peso_total_entrada = $campos[0]['qtde_entrada'] * $campos[0]['peso_unit_saida'];
        ?>
            <input type='text' name='txt_peso_total_entrada' value="<?=number_format($peso_total_entrada, 2, ',', '.');?>" size='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Dureza Fornecedor:</b>
        </td>
        <td>
            <input type='text' name='txt_dureza_fornecedor' value="<?=$campos[0]['dureza_fornecedor'];?>" size='16' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Dureza Interna:</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_dureza_interna' value="<?=$campos[0]['dureza_interna'];?>" size='16' maxlength='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>N.º Ped / OS</b>
        </td>
        <td>
        <?
//Verifico se esta OS está atrelada em algum Pedido ...
            $sql = "SELECT id_pedido 
                    FROM `oss` 
                    WHERE `id_os` = ".$campos[0]['id_os']." ";
            $campos_os = bancos::sql($sql);
            //Encontrou a OS em um Pedido, então eu printo o N. do Ped
            if(count($campos_os) == 1) echo $campos_os[0]['id_pedido'].' / '.$campos[0]['id_os'];
        ?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');calculo();document.form.txt_preco.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'>
        <?
/////////////////////////////// PAGINACAO CASO ESPECIFICA PARA ESTA TELA ///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='#' onclick='validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
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