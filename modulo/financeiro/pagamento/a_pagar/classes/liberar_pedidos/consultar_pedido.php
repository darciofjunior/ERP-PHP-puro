<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
session_start('funcionarios');
if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

//Verificação de todos os pedidos que já foram importados em conta à pagar pelo financeiro ...
$sql = "SELECT DISTINCT(id_pedido) AS id_pedidos 
        FROM `contas_apagares` 
        WHERE `id_pedido` > 0 ";
$campos = bancos::sql($sql);
for($i = 0; $i < count($campos); $i++) $id_pedidos.= $campos[$i]['id_pedidos'].', ';
$id_pedidos = substr($id_pedidos, 0, strlen($id_pedidos) - 2);

//Aqui para o caso de ele não encontrar nenhum pedido relacionado
if(empty($id_pedidos)) $id_pedidos = 0;

if($id_emp == 4) {//Significa que a empresa é do tipo grupo ...
    $condicao = " AND p.tipo_nota = '2' ";
}else {
    $condicao = " AND p.tipo_nota = '1' AND p.`id_empresa` = '$id_emp' ";
}

$sql = "SELECT p.id_pedido, p.prazo_navio, p.tipo_nota, p.prazo_entrega, p.data_emissao, f.razaosocial, e.nomefantasia 
        FROM `pedidos` p 
        INNER JOIN `importacoes` i ON i.id_importacao = p.id_importacao 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor AND f.`id_pais` <> '31' 
        INNER JOIN `empresas` e ON e.id_empresa = p.id_empresa 
	WHERE p.`id_pedido` NOT IN ($id_pedidos) 
        AND p.status = '2' 
        AND p.`ativo` = '1' 
        $condicao ORDER BY p.data_emissao DESC ";
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas > 0) {//Se existe pelo menos 1 Importação ...
?>
<html>
<head>
<title>.:: Consultar Pedidos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Consultar Pedido(s) de Compras
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º Pedido
        </td>
        <td>
            Data Emissão
        </td>
        <td>
            Data Vencimento
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Tipo Import
        </td>
        <td>
            Tipo
        </td>
        <td>
            Empresa
        </td>
        <td>
            <img src = "../../../../../../imagem/propriedades.png" width='16' height='16' border='0'>
        </td>
    </tr>
<?
    $vetor_tipo_nota = array('', 'NF', 'SGD');//Esse vetorzinho, será utilizado + abaixo ...
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = 'incluir.php?id_pedido=<?=$campos[$i]['id_pedido'];?>'" width='10'>
            <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href='incluir.php?id_pedido=<?=$campos[$i]['id_pedido'];?>' class='link'>
                <?=$campos[$i]['id_pedido'];?>
            </a>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/');?>
        </td>
        <td>
        <?
            $data_emissao 	= substr($campos[$i]['data_emissao'], 0, 10);
            $data_entrega 	= $campos[$i]['prazo_entrega'];
            $prazo_entrega 	= data::diferenca_data($data_emissao, $data_entrega);
            $entrega 		= $prazo_entrega[0];
            $prazo_navio 	= $campos[$i]['prazo_navio'];
            //Aqui o valor do navio + o prazo de entrega
            $soma_prazo 	= (integer)$prazo_navio + (integer)$entrega;
            //Aqui adiciona os demais dias que é da data de embarque do navio
            $data_emissao 	= data::datetodata(substr($data_emissao, 0, 10), '/');
            echo data::adicionar_data_hora($data_emissao, $soma_prazo);
        ?>
        </td>
        <td>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
        <?
            $sql = "SELECT i.nome 
                    FROM `importacoes` i 
                    INNER JOIN `pedidos` p ON p.id_importacao = i.id_importacao 
                    WHERE p.id_pedido = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
            $campos_importacao = bancos::sql($sql);
            echo $campos_importacao[0]['nome'];
        ?>
        </td>
        <td>
            <?=$vetor_tipo_nota[$campos[$i]['tipo_nota']];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <a href="javascript:nova_janela('../../../../../compras/pedidos/itens/itens.php?id_pedido=<?=$campos[$i]['id_pedido'];?>&pop_up=1', 'CONSULTAR', 'F')">
                <img src="../../../../../../imagem/propriedades.png" width='16' height='16' border='0'>
            </a>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = '../opcoes.php'" class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
}else {//Se não existe nenhuma Importação ...
?>
    <Script Language = 'JavaScript'>
        alert('NO MOMENTO, NÃO EXISTE(M) IMPORTAÇÃO(ÕES) PARA SER(EM) INCLUÍDA(S) !')
        window.opener.parent.itens.document.location = '../itens.php?parametro='+window.opener.parent.itens.document.form.parametro.value
        window.close()
    </Script>
<?}?>