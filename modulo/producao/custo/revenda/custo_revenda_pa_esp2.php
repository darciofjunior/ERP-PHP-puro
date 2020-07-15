<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/producao/custo/revenda/custo_revenda_pa_esp.php', '../../../../');
$mensagem[1] = '<font class="confirmacao">FORNECEDOR DESATRELADO COM SUCESSO PARA ESTE P.A.</font>';

//Aqui significa que está desatrelando um fornecedor do P.A.
if(!empty($_POST['id_fornecedor_prod_insumo'])) {
    intermodular::excluir_varios_pi_fornecedor($_POST['id_fornecedor_prod_insumo']);
    $valor = 1;
}

//Procedimento quando carrega a Tela ...
$id_produto_acabado = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado'] : $_GET['id_produto_acabado'];

//Verifico se existem Fornecedores atrelados p/ o PA "PIPA" passado por parâmetro ...
$sql = "SELECT f.`id_fornecedor`, f.`razaosocial`, fpi.`id_fornecedor_prod_insumo`, fpi.`fator_margem_lucro_pa` 
        FROM `produtos_acabados` pa 
        INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pa.`id_produto_insumo` AND fpi.`ativo` = '1' 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` 
        WHERE pa.`id_produto_acabado` = '$id_produto_acabado' 
        AND pa.`id_produto_insumo` > '0' 
        AND pa.`ativo` = '1' ORDER BY f.`razaosocial` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Custo Revenda - (PA Especial) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function desatrelar_item(id_fornecedor_prod_insumo) {
    var mensagem = confirm('DESEJA REALMENTE DESATRELAR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_fornecedor_prod_insumo.value = id_fornecedor_prod_insumo
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <font color='#00FF00' size='2'>
                <b>CUSTO REVENDA - (PA Especial)</b>
            </font>
        </td>
    </tr>
<?
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            NÃO HÁ FORNECEDOR(ES) ATRELADO(S) P/ ESSE PRODUTO ACABADO.
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Fornecedor(es) Atrelado(s) p/ o Produto => 
            <font color='yellow'>
            <?
                $sql = "SELECT `discriminacao` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                $campos_produto_acabado = bancos::sql($sql);
                echo $campos_produto_acabado[0]['discriminacao'];
            ?>
            </font>
        </td>
    </tr>
<?
        //Busca somente o id_fornecedor_default p/ saber de qual fornecedor q estou pegando p/ calcular o custo do PA revenda ...
	$id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1);
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="javascript:nova_janela('custo_revenda.php?id_fornecedor_prod_insumo=<?=$campos[$i]['id_fornecedor_prod_insumo'];?>', 'CUSTO_REVENDA', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')" title='Custo Revenda' class='link'>
            <?
                if($campos[$i]['id_fornecedor'] == $id_fornecedor_setado) {
                    echo "<b>".$campos[$i]['razaosocial']."<a/> <= DEFAULT</b>";
                }else {
                    echo $campos[$i]['razaosocial']."</a>";
                }
            ?>
            </a>
        </td>
        <td width='10' align='center'>
            <img src = '../../../../imagem/filtro.jpg' border='0' title='Filtro por Fornecedor' onclick="window.location = 'consultar_produtos.php?id_fornecedor=<?=$campos[$i]['id_fornecedor']?>&razaosocial=<?=$campos[$i]['razaosocial']?>&id_produto_acabado=<?=$id_produto_acabado;?>&tipo=ESP'">
        </td>
        <td width='10' align='center'>
            <img src = '../../../../imagem/menu/excluir.png' border='0' title="Desatrelar Item" onclick="desatrelar_item('<?=$campos[$i]['id_fornecedor_prod_insumo'];?>')">
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'custo_revenda_pa_esp.php<?=$parametro;?>'" class='botao'>
            <?
                //Verifico se o P.A. já tem relação com o PI ...
                $sql = "SELECT `id_produto_insumo` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                        AND `id_produto_insumo` > '0' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos = bancos::sql($sql);
                if(count($campos) == 1) {//Significa que encontrou
                    $id_produto_insumo = $campos[0]['id_produto_insumo'];
                }else {//Não encontrou, chama a função de importar P.A. p/ P.I.
                    $id_produto_insumo = intermodular::importar_patopi($id_produto_acabado);
                }
            ?>
            <input type='button' name='cmd_atrelar_fornecedor' value='Atrelar Fornecedor' title='Atrelar Fornecedor' onclick="nova_janela('../../../compras/produtos_fornecedores/atrelar_fornecedor_em_pi.php?id_produto_insumo=<?=$id_produto_insumo;?>', 'CONSULTAR', '', '', '', '', 480, 880, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:green' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_fornecedor_prod_insumo'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
</form>
</body>
</html>