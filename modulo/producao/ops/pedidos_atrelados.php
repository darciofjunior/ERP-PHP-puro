<?
/*Eu tenho esse desvio aki para não redeclarar as bibliotecas novamente, isso porque tem alguns arquivos 
q essa parte de estoque embutida e sendo assim já tem as bibliotecas declaradas logo no início*/
if($nao_chamar_biblioteca != 1) {
    require('../../../lib/segurancas.php');
    require('../../../lib/data.php');
    require('../../../lib/vendas.php');
}
segurancas::geral('/erp/albafer/modulo/producao/ops/incluir.php', '../../../');
$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) PEDIDO(S) EM ABERTO QUE ESTÃO ATRELADO(S) PARA ESSE PRODUTO ACABADO.</font>";

//Listagem de todos os Pedidos que contém esse item atrelado (P.A) e que estejam em aberto ...
$sql = "SELECT ov.`id_funcionario`, ovi.`id_orcamento_venda`, ovi.`prazo_entrega_tecnico`, 
        pv.`data_emissao`, pvi.`id_pedido_venda`, pvi.`qtde`, pvi.`prazo_entrega` 
        FROM `pedidos_vendas_itens` pvi 
        INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`status` < '2' AND pvi.`status` < '2' 
        INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda_item` = pvi.`id_orcamento_venda_item` 
        INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` 
        WHERE pvi.`id_produto_acabado` = '$_GET[id_produto_acabado]' ORDER BY pv.`id_pedido_venda` DESC ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Pedido(s) Atrelado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<form name='form'>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
    if($linhas == 0) {
?>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Pedido(s) Atrelado(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde
        </td>
        <td>
            N.º Ped
        </td>
        <td>
            Data de <br/>Emissão
        </td>
        <td>
            Vendedor <br/>do Orc
        </td>
        <td>
            N.º Orc
        </td>
        <td>
            Prazo de Entrega
            <br/>do Pedido
        </td>
        <td>
            Prazo de Entrega sugerido<br/>
            pelo Depto. Técnico neste Orc
        </td>
    </tr>
<?
	$vetor_prazos_entrega = vendas::prazos_entrega();
//Disparo do Loop ...
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[$i]['id_pedido_venda'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
        <?
            $sql = "SELECT l.login 
                    FROM `logins` l 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = l.`id_funcionario` 
                    WHERE f.`id_funcionario` = ".$campos[$i]['id_funcionario']." LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            echo $campos_login[0]['login'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['id_orcamento_venda'];?>
        </td>
        <td>
        <?
            foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
                //Compara o valor do Banco com o valor do Vetor
                if($campos[$i]['prazo_entrega'] == $indice) echo $prazo_entrega;
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['prazo_entrega_tecnico'] == '0.0') {
                $prazo_entrega_apresentar = '<font color="red"><b>SEM PRAZO</b></font>';
    //Aqui é o Prazo de Ent. da Empresa Divisão, e verifica qual é o certo para poder carregar na caixa de texto
    /*Existe esse esquema de Int, porque o Campo -> 'prazo_entrega_tecnico' é do Tipo Float, foi feito
    esse esquema para não dar problema na hora de Atualizar o Custo*/
            }else {
                $prazo_entrega_apresentar = (int)$campos[$i]['prazo_entrega_tecnico'];
            }
            echo $prazo_entrega_apresentar;
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho'>
        <td colspan='7'>
            &nbsp;
        </td>
    </tr>
</table>
<!--Esse hidden me serve somente p/ a Tela de Incluir OP por enquanto,
ele guarda somente o primeiro prazo de entrega do Depto Técnico ...-->
<input type='hidden' name='primeiro_prazo_depto_tecnico' value='<?=(int)$campos[0]['prazo_entrega_tecnico'];?>'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
?>