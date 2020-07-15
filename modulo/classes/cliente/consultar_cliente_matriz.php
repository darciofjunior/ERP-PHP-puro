<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');
session_start('funcionarios');

$id_cliente = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_cliente'] : $_GET['id_cliente'];

if(!empty($_GET['id_cliente_matriz'])) {//Aqui foi passado o Cliente Matriz por parâmetro ...
/*Aqui o Sys atribui uma Matriz p/ o Cliente que eu estou fazendo a alteração ..., e nesse 
instante o mesmo que eu estou fazendo essa alteração passa a virar uma Filial ...*/
    $sql = "UPDATE `clientes` SET `id_cliente_matriz` = '$_GET[id_cliente_matriz]' WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'Javascript'>
        alert('MATRIZ ATRELADA COM SUCESSO P/ ESTE CLIENTE !')
        window.opener.document.form.id_cliente.onclick()
        window.close()
    </Script>
<?
}
/**********************************Tela de Filtro**********************************/
//Busca de Todas as Matrizes com exceção do Cliente que está sendo acessado ...
$trazer_clientes_matrizes = 1;
$nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
require('tela_geral_filtro.php');
if($linhas > 0) {//Se retornar pelo menos 1 registro
?>
<html>
<head>
<title>.:: Atrelar Cliente(s) Matriz ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function atrelar_cliente_matriz(id_cliente_matriz) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA ATRELAR ESSE CLIENTE COMO MATRIZ ?')
    if(resposta == true) window.location = 'consultar_cliente_matriz.php?id_cliente=<?=$id_cliente;?>&id_cliente_matriz='+id_cliente_matriz
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Atrelar Cliente(s) Matriz
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
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
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $credito = financeiros::controle_credito($campos[$i]['id_cliente']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')"  align='center'>
        <td width='10' onclick="atrelar_cliente_matriz('<?=$campos[$i]['id_cliente'];?>')">
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <a href="javascript:atrelar_cliente_matriz('<?=$campos[$i]['id_cliente'];?>')" class='link'>
                <?=$campos[$i]['cod_cliente'].' - '.$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td>
            <font color='blue'>
                <?=$credito;?>
            </font>
        </td>
        <td>
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
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar_cliente_matriz.php?id_cliente=<?=$id_cliente;?>'" class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>