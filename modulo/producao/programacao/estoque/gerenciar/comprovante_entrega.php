<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');//Essa biblioteca é utilizada dentro da Biblioteca de Faturamentos ...
require('../../../../../lib/comunicacao.php');
require('../../../../../lib/data.php');
require('../../../../../lib/estoque_acabado.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/variaveis/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../../../');
?>
<html>
<head>
<title>.:: Comprovante de Entrega de Material ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
document.oncontextmenu = function () { return false }
if(document.layers) {
    window.captureEvents(event.mousedown)
    window.onmousedown =
    function (e){
    if (e.target == document)
            return false
    }
}else {
    document.onmousedown = function () { return false }
}

//Função que trava o teclado
function controle_teclas() {
    if(navigator.appName == 'Netscape') {//Mozilla, Netscape
        return false
    }else {//Controle para Internet Explorer
        alert('UTILIZE O BOTÃO IMPRIMIR !')
        return false
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
    if(window.opener.parent.itens != null) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }else {
        window.opener.document.form.submit()
    }
}

function controle_sedex(id_cliente_contato) {
    var resposta = confirm('DESEJA IMPRIMIR O ENDEREÇO DE COBRANÇA ?')
    if(resposta == true) {//Se quiser imprimir o Endereço de Cobrança ...
        var imp_endereco_cobranca = 1
    }else {//Se não quiser imprimir o Endereço de Cobrança ...
        var imp_endereco_cobranca = 0
    }
//Abrindo o Pop-Up do Sedex ...
/*Parâmetros

- Endereço de Cobrança - se o usuário não deseje imprimir o Endereço de Cobrança, será impresso o Normal ...
- O remetente no caso o Grupo, porque geralmente é tudo sem NF ...
- O id_cliente_contato - é o Contato responsável da NF, com este eu já pego o Cliente, Departamento*/
    nova_janela('../../../relatorio/controle_sedex/imprimir.php?imp_endereco_cobranca='+imp_endereco_cobranca+'&remetente_emp=4&id_cliente_contato='+id_cliente_contato, 'CONSULTAR', '', '', '', '', '450', '800', 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body topmargin='15' onkeypress='return controle_teclas()' onkeydown='return controle_teclas()' onunload='atualizar_abaixo()'>
<form name='form'>
<?
//Busca de alguns dados através do $_GET['id_pedido_venda'], que serão apresentados mais abaixo ...
$sql = "SELECT CONCAT(c.`razaosocial`, ' (', c.`nomefantasia`, ')') AS cliente, 
        pv.`id_cliente_contato`, pv.`id_empresa` 
        FROM `pedidos_vendas` pv 
        INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` 
        WHERE pv.`id_pedido_venda` = '$_GET[id_pedido_venda]' LIMIT 1 ";
$campos_pedida_venda    = bancos::sql($sql);

$mensagem_email         = '';
$vias                   = 3;
$vetor_vias             = array('1ª VIA GRUPO ALBAFER', '2ª VIA GRUPO ALBAFER', 'VIA CLIENTE');

for($v = 0; $v < $vias; $v++) {
    $total_geral_rs = 0;//Toda vez que se inicia uma nova via, zero p/ não herdar valores do Loop anterior ...
?>
<table width='880' cellspacing='0' cellpadding='1' border='1' align='center'>
    <tr class='linhanormal' valign='center'>
        <td colspan='6' bgcolor='#FFFFFF'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1'>
                <b><i>COMPROVANTE DE ENTREGA DE MATERIAL P/ CLIENTE - <?=genericas::nome_empresa($campos_pedida_venda[0]['id_empresa']);?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <font face='Verdana, Arial, Helvetica, sans-serif' size='2'>
                    VL N.º <?=$_GET['id_vale_venda'];?>
                </i></b>
            </font>
        </td>
        <td colspan='2' bgcolor='#FFFFFF' align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1'>
                <b><i>* <?=$vetor_vias[$v]?> *</i></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='left'>
        <td colspan='6' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Cliente:</b> <?=$campos_pedida_venda[0]['cliente'];?>
            </font>
        </td>
        <td colspan='2' bgcolor='#FFFFFF' align='right'>
            <font size='2'>
                <?=date('d/m/Y H:i').' HS';?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#FFFFFF'>
            <font title='Quantidade' size='2'>
                <b>Qtde</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font title='Referência * Discriminação' size='2'>
                <b>Ref. * Discriminação</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font title='Preço Unitário R$' size='2'>
                <b>Pço Unit. R$</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font title='Preço Total R$' size='2'>
                <b>Pço Total R$</b>
            </font>
        </td>	
        <td bgcolor='#FFFFFF'>
            <font title='Nosso N.º de Pedido' size='2'>
                <b>N/ N.º Ped</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font title='Seu N.º de Pedido' size='2'>
                <b>S/ N.º Ped</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font title='Peso Unitário' size='2'>
                <b>Peso Unit.</b>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font title='Peso Total' size='2'>
                <b>Peso Total</b>
            </font>
        </td>
    </tr>
<?
        $total_linhas   = 8;//Total de linhas por pág. p/ printagem

        /*Através do $_GET[id_vale_venda] que foi passado por parâmetro eu verifico quais itens que foram 
        enviados naquele Lote ...*/
        $sql = "SELECT vv.*, vvi.`id_pedido_venda_item`, vvi.`qtde` 
                FROM `vales_vendas` vv 
                INNER JOIN `vales_vendas_itens` vvi ON vvi.`id_vale_venda` = vv.`id_vale_venda` 
                WHERE vv.`id_vale_venda` = '$_GET[id_vale_venda]' ";
        $campos_vale_venda  = bancos::sql($sql);
        $linhas_vale_venda  = count($campos_vale_venda);

//Essas aki é a diferença de linhas que falta printar para somar no Total de linhas = 11
	$printar_linhas     = $total_linhas - $linhas_vale_venda;
	$peso_liquido_total = 0;
        
	for($i = 0; $i < $linhas_vale_venda; $i++) {
/*Seleção somente de Itens de Pedidos que foram selecionados pelo usuário na tela Principal de Itens 
<- 'pvi.status < 2' e pedidos já liberados*/
            $sql = "SELECT pv.`id_pedido_venda`, pv.`num_seu_pedido`, pvi.`preco_liq_final`, 
                    pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, pa.`peso_unitario` 
                    FROM `pedidos_vendas_itens` pvi 
                    INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`status` < '2' 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                    WHERE pvi.`id_pedido_venda_item` = '".$campos_vale_venda[$i]['id_pedido_venda_item']."' 
                    AND pvi.`status` < '2' ORDER BY pv.`id_empresa`, pv.`id_pedido_venda`, pvi.`id_pedido_venda_item` "; //nao pode tirar o pvi.id_pedido_venda_item, pois da erro de indexação
            $campos = bancos::sql($sql);
//Enquanto Imprime o sistema também faz a separação das qtdes de vale, mas somente na Via 1, senão fará 3 vezes ...
/*************************************Roteiros*************************************/
            if($campos_vale_venda[$i]['qtde'] != 0 && $v == 0) {
                //Aqui eu busco a Discriminação do Produto Acabado ...
                $sql = "SELECT CONCAT(`referencia`, ' - ', `discriminacao`) AS produto_acabado 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '".$campos[0]['id_produto_acabado']."' LIMIT 1 ";
                $campos_pa = bancos::sql($sql);
                
                $rotulo_complementar = ($campos_vale_venda[$i]['qtde'] < 0) ? " <font color='red'><b>(Cancelado)</b></font>" : '';
                $mensagem_email.= $campos_vale_venda[$i]['qtde'].' - '.$campos_pa[0]['produto_acabado'].' - Pedido: '.$campos[0]['id_pedido_venda'].$rotulo_complementar.'<br/>';
            }
/**********************************************************************************/
?>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#FFFFFF'>
            <font size='2'>
            <?
                if(!empty($campos_vale_venda[$i]['qtde'])) {
                    echo number_format($campos_vale_venda[$i]['qtde'], 2, ',', '.');
                }else {
                    echo 0;
                }
            ?>
            </font>
        </td>
        <td bgcolor='#FFFFFF' align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='1'>
                <?=$campos[0]['referencia'].' * '.$campos[0]['discriminacao'];?>
            </font>
        </td>
        <td bgcolor='#FFFFFF' align='right'>
            <font size='2'>
                <?=number_format($campos[0]['preco_liq_final'], 2, ',', '.');?>
            </font>
        </td>	
        <td bgcolor='#FFFFFF' align='right'>
            <font size='2'>
            <?
                $preco_final 	= $campos_vale_venda[$i]['qtde'] * $campos[0]['preco_liq_final'];
                $total_geral_rs+= $preco_final;//Será utilizado no final da página ...

                echo number_format($preco_final, 2, ',', '.');
            ?>
            </font>
        </td>			
        <td bgcolor='#FFFFFF'>
            <font size='2'>
                <?=$campos[0]['id_pedido_venda'];?>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font size='2'>
                <?=$campos[0]['num_seu_pedido'];?>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font size='2'>
            <?
                if($campos[0]['peso_unitario'] != '0.0000') {
                    echo number_format($campos[0]['peso_unitario'], 4, ',', '.');
                }else {
                    echo '&nbsp;';
                }
            ?>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font size='2'>
            <?
                $peso_total = $campos_vale_venda[$i]['qtde'] * $campos[0]['peso_unitario'];
                $peso_liquido_total+= $peso_total;
                echo number_format($peso_total, 4, ',', '.');
            ?>
            </font>
        </td>
    </tr>
<?
        }
//Printagem das demais linhas para completar 11 linhas
	for($i = 0; $i < $printar_linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td bgcolor='#FFFFFF'>
            &nbsp;
        </td>
        <td bgcolor='#FFFFFF'>
            &nbsp;
        </td>
        <td bgcolor='#FFFFFF'>
            &nbsp;
        </td>
        <td bgcolor='#FFFFFF'>
            &nbsp;
        </td>
        <td bgcolor='#FFFFFF'>
            &nbsp;
        </td>
        <td bgcolor='#FFFFFF'>
            &nbsp;
        </td>
        <td bgcolor='#FFFFFF'>
            &nbsp;
        </td>
        <td bgcolor='#FFFFFF'>
            &nbsp;
        </td>
    </tr>
<?
	}
?>
    <tr class='linhanormal' align='right'>
        <td colspan='3' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Total Geral => </b> 
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font size='2'>
                <b>R$ <?=number_format($total_geral_rs, 2, ',', '.');?></b>
            </font>
        </td>
        <td colspan='4' bgcolor='#FFFFFF'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='6' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Transportadora:</b> 
                <?
                    $sql = "SELECT `nome` 
                            FROM `transportadoras` 
                            WHERE `id_transportadora` = '".$campos_vale_venda[0]['id_transportadora']."' LIMIT 1 ";
                    $campos_transportadora = bancos::sql($sql);
                    echo $campos_transportadora[0]['nome'];
                ?>
            </font>
        </td>
        <td colspan='2' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Vlr Frete R$</b> <?=number_format($campos_vale_venda[0]['valor_frete'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='6' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Entregue por:</b> <?=$campos_vale_venda[0]['entregue_por'];?>
            </font>
        </td>
        <td colspan='2' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Retirado por:</b> <?=$campos_vale_venda[0]['retirado_por'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='6' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Quantidade de Caixas:</b> <?=$campos_vale_venda[0]['qtde_caixas'];?>
            </font>
            &nbsp;&nbsp;-&nbsp;&nbsp;
            <font size='2'>
                <b>Peso Líq. Total:</b> <?=number_format($peso_liquido_total, 4, ',', '.').' KGS';?>
            </font>
        </td>
        <td colspan='2' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Peso Bruto:</b> <?=number_format($campos_vale_venda[0]['peso_bruto'], 2, ',', '.').' KGS';?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='8' bgcolor='#FFFFFF'>
            <font size='3'>
                <i>Favor complementar o pedido com urgência, pois o preço está sujeito a reajuste na data do faturamento.</i>
            </font>
        </td>
    </tr>
<?
    if($campos_pedida_venda[0]['id_empresa'] == 1 || $campos_pedida_venda[0]['id_empresa'] == 2) {//Somente para NFs do Tipo "NF" ...
?>
    <tr class='linhanormal'>
        <td colspan='8' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>O Frete será acrescido de Impostos no Faturamento.</b>
            </font>
        </td>
    </tr>
<?
    }
?>
</table>
<br/><br/>
<?
}

/******************************************************************/
/*******************Email de Segurança com Vales*******************/
/******************************************************************/
$mensagem_email.= '<br/><b>Entregue por: </b> '.$campos_vale_venda[0]['entregue_por'];
$mensagem_email.= '<br/><b>Retirado por: </b> '.$campos_vale_venda[0]['retirado_por'];
$mensagem_email.= '<br/><b>Data Atual = </b>'.date('d/m/Y H:i:s');
$mensagem_email.= '<p/>'.$PHP_SELF;

//Envio um email ...
$destino    = $enviar_vale_gerenciar_est;
$assunto    = 'Envio de Vale N.º '.$_GET[id_vale_venda].' - '.$campos_pedida_venda[0]['cliente'];

if($campos_vale_venda[0]['email_enviado'] == 'N') {//Ainda ñ foi enviado nenhum e-mail referente aos Vales ...
    /*Aqui eu mudo o valor do campo "email_enviado" como sendo = 'S' p/ que numa próxima vez em que essa 
    tela for acessada o Sistema não venha mandar outros e-mails com o mesmo conteúdo novamente ...*/
    $sql = "UPDATE `vales_vendas` SET `email_enviado` = 'S' WHERE `id_vale_venda` = '$_GET[id_vale_venda]' LIMIT 1 ";
    bancos::sql($sql);
    
    comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', $assunto, $mensagem_email);
}
/******************************************************************/
?>
<br/>
<center>
    <input type='button' name='cmd_imprimir_comprovante' value='Imprimir Comprovante' title='Imprimir Comprovante' onclick='window.print()' class='botao'>
    <input type='button' name='cmd_controle_sedex' value='Controle de Sedex' title='Controle de Sedex' onclick="controle_sedex('<?=$campos_pedida_venda[0]['id_cliente_contato'];?>')" style='color:black' class='botao'>
    <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window);atualizar_abaixo()' style='color:red' class='botao'>
</center>
</table>
</form>
</body>
</html>