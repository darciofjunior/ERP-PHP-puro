<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos/insumos_ vs_acabados/consultar.php', '../../../../');

//Busco o id_PI do PA "PIPA" passado por parâmetro ...
$sql = "SELECT id_produto_insumo 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
$campos             = bancos::sql($sql);
$id_produto_insumo  = $campos[0]['id_produto_insumo'];
?>
<html>
<head>
<title>.:: Detalhes Produto Insumo Vs Produtos Acabados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table border="0" width='90%' align="center" cellspacing ='1' cellpadding='1'>
    <tr>
        <td>
            <!--*********************Aqui eu exibo dados do PI equivalente ao PA encontrado no SQL acima*********************-->
            <iframe name='detalhes_pi' src='../detalhes.php?id_produto_insumo=<?=$id_produto_insumo;?>' width='100%' height='240'></iframe>
        </td>
    </tr>
    <tr>
        <td>
            <br/>
        </td>
    </tr>
    <tr>
        <td>
            <!--*********************Aqui eu exibo dados do PA passado por parâmetro*********************-->
            <iframe name='detalhes_pa' src='/erp/albafer/modulo/producao/cadastros/produto_acabado/detalhes.php?id_produto_acabado=<?=$_GET['id_produto_acabado'];?>' width='100%' height='240'></iframe>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
                &nbsp;
        </td>
    </tr>
</table>
</body>
</html>