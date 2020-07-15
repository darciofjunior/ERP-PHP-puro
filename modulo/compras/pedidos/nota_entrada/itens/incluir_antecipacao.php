<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');//Essa biblioteca é utilizada dentro da Biblioteca 'compras_new' ...
require('../../../../../lib/compras_new.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) ANTECIPAÇÃO(ÕES) PARA ESSA NOTA.</font>";
$mensagem[2] = "<font class='confirmacao'>ANTECIPAÇÃO LIBERADA COM SUCESSO.</font>";

if(!empty($_POST['chkt_antecipacao'])) {
    foreach($_POST['chkt_antecipacao'] as $id_antecipacao) {
        //Aki atrela a antecipação na tabela relacional de Nota Fiscal vs Antecipações
        $sql = "INSERT INTO `nfe_antecipacoes` (`id_nfe_antecipacao`, `id_nfe`, `id_antecipacao`, `data_sys`) VALUES (NULL, '$_POST[id_nfe]', '$id_antecipacao', '".date('Y-m-d')."') ";
        bancos::sql($sql);
    }
/*********************************************/
/*Essa função pega o valor da Nota Fiscal, e desconta desse valor, o valor total das antecipações e 
e divide o valor restante de acordo com a Qtde de Prazos*/
    compras_new::calculo_valor_financiamento($id_nfe);
/*********************************************/
    $valor = 2;
}

$id_nfe = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_nfe'] : $_GET['id_nfe'];

//Busca da Empresa, do número da Nota Fiscal e id_fornecedor através do id_nfe da NF de Entrada corrente
$sql = "SELECT `id_empresa`, `id_fornecedor`, `num_nota` 
        FROM `nfe` 
        WHERE `id_nfe` = '$id_nfe' ";
$campos             = bancos::sql($sql);
$id_empresa_nota    = $campos[0]['id_empresa'];
$id_fornecedor      = $campos[0]['id_fornecedor'];
$num_nota           = $campos[0]['num_nota'];

//Busca de todas as Antecipações do Fornecedor que estão importadas em Nota Fiscal ...
$sql = "SELECT nfea.`id_antecipacao` 
        FROM `nfe` 
        INNER JOIN `nfe_antecipacoes` nfea ON nfea.`id_nfe` = nfe.`id_nfe` 
        WHERE nfe.`id_fornecedor` = '$id_fornecedor' ";
$campos_antecipacoes = bancos::sql($sql);
$linhas_antecipacoes = count($campos_antecipacoes);

if($linhas_antecipacoes == 0) {
    $id_antecipacoes = 0;
}else {
    for($i = 0; $i < $linhas_antecipacoes; $i++) $id_antecipacoes.= $campos_antecipacoes[$i]['id_antecipacao'].', ';
    $id_antecipacoes = substr($id_antecipacoes, 0, strlen($id_antecipacoes) - 2);
}

//Busca de Todas as Antecipações do Pedido do Fornecedor que ainda não estão em Nota Fiscal ...
$sql = "SELECT a.*, p.`id_pedido`, p.`id_empresa`, p.`tp_moeda`, tp.`pagamento` 
        FROM `antecipacoes` a 
        INNER JOIN `pedidos` p ON p.`id_pedido` = a.`id_pedido` 
        INNER JOIN `tipos_pagamentos` tp ON tp.`id_tipo_pagamento` = a.`id_tipo_pagamento_recebimento` AND a.`id_antecipacao` NOT IN ($id_antecipacoes) 
        WHERE p.`id_fornecedor` = '$id_fornecedor' 
        AND p.`ativo` = '1' 
        AND a.`id_pedido` = p.`id_pedido` ORDER BY a.`data` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Antecipação(ões) Pendente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox' && elementos[i].name == 'chkt_antecipacao[]')  {
            if (elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        document.form.nao_atualizar.value = 1
        return true
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.top.opener.parent.itens.document.form.submit()
        window.top.opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
if($linhas == 0) {
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr class='atencao' align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title="Voltar" onclick="window.location = 'outras_opcoes.php?id_nfe=<?= $id_nfe; ?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?      
}else {
?>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Antecipação(ões) Pendentes da Nota Fiscal de Entrada N.º 
            <font color='yellow'>
                <?=$num_nota;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar Tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" id='chkt_tudo' class='checkbox'>
        </td>
        <td>
            <font title='N.º do Pedido / N.º da Antecipação' style='cursor:help'>
                N.º Ped / Ant
            </font>
        </td>
        <td>
            Pagamento
        </td>
        <td>
            Valor
        </td>
        <td>
            <font title='Data de Vencimento' style='cursor:help'>
                Data Venc
            </font>
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
        <?
//Se a Empresa do Pedido corrente é incompatível com a Empresa da Nota Fiscal, então eu não posso importar a antecipação ...
            if($campos[$i]['id_empresa'] != $id_empresa_nota) {
        ?>
            <input type='hidden' name='chkt_antecipacao[]' value='<?=$campos[$i]['id_antecipacao'];?>'>
                <font title='Não Compatível' color='green' style='cursor:help'>
                    <b>Ñ COMP.</b>
                </font> 
        <?
                }else {
        ?>
            <input type='checkbox' name='chkt_antecipacao[]' value='<?=$campos[$i]['id_antecipacao'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <?
                }
        ?>
        <td>
            <font color='red'>
                <b><?=$campos[$i]['id_pedido'];?></b>
            </font>
            <b><?=' / '.$campos[$i]['id_antecipacao'];?></b>
        </td>
        <td>
            <?=$campos[$i]['pagamento'];?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['tp_moeda'] == 1) {
                $moeda = 'R$ ';
            }else if($campos[$i]['tp_moeda'] == 2) {
                $moeda = 'U$ ';
            }else if($campos[$i]['tp_moeda'] == 3) {
                $moeda = '&euro; ';
            }
            echo $moeda.number_format($campos[$i]['valor'], 2, ',', '.');
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title="Voltar" onclick="window.location = 'outras_opcoes.php?id_nfe=<?=$id_nfe;?>'" class='botao'>
            <input type='submit' name='cmd_antecipar' value='Antecipar' title='Antecipar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_nfe' value='<?=$id_nfe;?>'>
<input type='hidden' name='nao_atualizar'>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
<b>* Ñ COMP</b> -> Significa que a Antecipação é do Tipo "Não Compatível". 

Ou seja está não pode ser importada p/ a Nota Fiscal, devido a incompatibilidade da Empresa do Pedido com a 
Empresa da Nota Fiscal.
</pre>
<?}?>