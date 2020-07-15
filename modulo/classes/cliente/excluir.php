<?
require '../../../lib/menu/menu.php';
require '../../../lib/data.php';
require '../../../lib/financeiros.php';
require '../../../lib/genericas.php';
require '../../../lib/vendas.php';

$mensagem[1] = "<font class='confirmacao'>CLIENTE EXCLUÍDO COM SUCESSO.</font>";

if($passo == 1) {
    foreach ($_POST['chkt_cliente'] as $id_cliente) {
        //Deleta os contatos do cliente
        $sql = "UPDATE `clientes_contatos` SET `ativo` = '0' WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        bancos::sql($sql);

        //Exclusão do cliente, na realidade só oculta ...
        $sql = "UPDATE `clientes` SET `ativo` = '0' WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        bancos::sql($sql);
        genericas::atualizar_clientes_no_site_area_cliente($id_cliente);
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'excluir.php<?=$parametro?>&valor=1'
    </Script>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Excluir Clientes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name="form" method="POST" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar_checkbox('form','SELECIONE UMA OPÇÃO !')">
<table width='80%' border=0 align='center' cellspacing=1 cellpadding=1 onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='7'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='7'>
            Excluir Cliente(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td>
            <?=genericas::order_by('c.razaosocial', 'Razão Social', 'Razão Social', $order_by, '../../../');?>
        </td>
        <td>
            <?=genericas::order_by('c.nomefantasia', 'Nome Fantasia', 'Nome Fantasia', $order_by, '../../../');?>
        </td>
        <td>
            Tp
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Cr
        </td>
        <td>
            CNPJ / CPF
        </td>
        <td>
            <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $credito = financeiros::controle_credito($campos[$i]['id_cliente']);
?>
    <tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="nova_janela('../../vendas/cliente/relatorio.php?id_clientes=<?=$campos[$i]['id_cliente'];?>&pop_up=1', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')">
            <a href="javascript:nova_janela('../../vendas/cliente/relatorio.php?id_clientes=<?=$campos[$i]['id_cliente'];?>&pop_up=1', 'RELATORIO', '', '', '', '', 450, 900, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <?=$campos[$i]['cod_cliente'].' - '.$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align="center">
            <?=$campos[$i]['tipo'];?>
        </td>
        <td align="left">
        <?
            if(!empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax']))    echo $campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            if(!empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax']))     echo $campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].$campos[$i]['telfax'];
            if(empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax']))     echo $campos[$i]['ddi_fax'].$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            if(empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax']))      echo $campos[$i]['telfax'];
        ?>
        </td>
        <td align='center'>
            <font color='blue'>
                <?=$credito;?>
            </font>
        </td>
        <td align="center">
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
        <td align="center">
            <input type="checkbox" name="chkt_cliente[]" value="<?=$campos[$i]['id_cliente'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan="7">
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
            <input type="submit" name="cmd_excluir" value="Excluir" title="Excluir" class="botao">
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color='red'><b>Legenda dos Tipos de Cliente:</b></font>

 <font color="blue"><b>RA</b></font> -> Revenda Ativa
 <font color="blue"><b>RI</b></font> -> Revenda Inativa
 <font color="blue"><b>CO</b></font> -> Cooperado
 <font color="blue"><b>ID</b></font> -> Indústria
 <font color="blue"><b>AT</b></font> -> Atacadista
 <font color="blue"><b>DT</b></font> -> Distribuidor
 <font color="blue"><b>IT</b></font> -> Internacional
 <font color="blue"><b>FN</b></font> -> Fornecedor
 <font color="blue"><b>UC</b></font> -> Usina de Cana
</pre>
<?
    }
}
?>