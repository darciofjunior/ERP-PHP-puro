<?
require('../../../../lib/segurancas.php');
require('../../../../lib/faturamentos.php');

switch($opcao) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    case 4://Significa que veio do Menu de Devolução 
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
    default://Significa que veio do Menu de Devolução ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
}

$calculo_peso_nf    = faturamentos::calculo_peso_nf($_GET['id_nf']);
//1) 
$qtde_volume        = $calculo_peso_nf['qtde_caixas'];
if(empty($qtde_volume)) $qtde_volume = 0;
?>
<html>
<head>
<title>.:: Detalhes Qtde de Volume ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho'>
        <td>
            <font color='yellow'>
                Qtde de Volume: 
            </font>
        </td>
        <td align='center'>
            <?=$qtde_volume;?>
        </td>
    </tr>
</table>
</body>
</html>