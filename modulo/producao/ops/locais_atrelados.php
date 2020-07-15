<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/ops/excluir.php', '../../../');

//Busco dados da OP passada por parâmetro ...
$sql = "SELECT ops.*, pa.referencia, pa.discriminacao 
        FROM `ops` 
        INNER JOIN `produtos_acabados` pa ON pa.id_produto_acabado = ops.id_produto_acabado 
        WHERE `id_op` = '$_GET[id_op]' LIMIT 1 ";
$campos             = bancos::sql($sql);
$referencia         = $campos[0]['referencia'];
$discriminacao      = $campos[0]['discriminacao'];
$qtde_produzir      = $campos[0]['qtde_produzir'];
$data_emissao       = data::datetodata($campos[0]['data_emissao'], '/');
$prazo_entrega      = data::datetodata($campos[0]['prazo_entrega'], '/');

//Verifico o último Status no Sistema relacionada a esta OP e a este PI ...
$sql = "SELECT status 
        FROM `baixas_ops_vs_pis` 
        WHERE `id_op` = '$_GET[id_op]' ORDER BY id_baixa_op_vs_pi DESC LIMIT 1 ";
$campos_baixa_op = bancos::sql($sql);
if(count($campos_baixa_op) == 1) {//Estornando Baixa ...
/*Se existir o último Status for baixa, então eu exibo essa ferramenta p/ poder estornar a 
Baixa dessa OP e desse PI no Banco de Dados ...*/
    if($campos_baixa_op[0]['status'] == 2) $linhas1 = 1;
}

//Aqui eu verifico se a OP está atrelada a alguma OSS ...
$sql = "SELECT id_os 
        FROM `oss_itens` 
        WHERE `id_op` = '$_GET[id_op]' ";
$campos_os = bancos::sql($sql);
$linhas_os = count($campos_os);

//Aqui eu verifico se a OP possui alguma Entrada ...
$sql = "SELECT bmp.id_baixa_manipulacao_pa 
        FROM `baixas_ops_vs_pas` bop 
        INNER JOIN `baixas_manipulacoes_pas` bmp ON bmp.id_baixa_manipulacao_pa = bop.id_baixa_manipulacao_pa 
        WHERE bop.`id_op` = '$_GET[id_op]' ";
$campos_entrada = bancos::sql($sql);
$linhas_entrada = count($campos_entrada);
?>
<html>
<title>.:: Locais Atrelados ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<body>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho'>
        <td>
            <font color='yellow'>
                <b>OP N.º: </b>
            </font>
            <?=$_GET['id_op'];?>
        </td>
        <td colspan='2'>
            <font color='yellow'>
                <b>Produto: </b>
            </font>
            <?=$referencia.' - '.$discriminacao;?>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td>
            <font color='yellow'>
                <b>Qtde à Produzir: </b>
            </font>
            <?=$qtde_produzir;?>
        </td>
        <td>
            <font color='yellow'>
                <b>Data de Emissão: </b>
            </font>
            <?=$data_emissao;?>
        </td>
        <td>
            <font color='yellow'>
                <b>Pzo. Entrega: </b>
            </font>
            <?=$prazo_entrega;?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'>
            Locais Atrelados
        </td>
    </tr>
<?
    if($linhas1 == 1) {
?>
    <tr class='linhanormal'>
        <td colspan='3'>
            <b>* Essa O.P. possui baixa de PI</b>
        </td>
    </tr>
<?
    }
    if($linhas_os > 0) {
        for($i = 0; $i < $linhas_os; $i++) $id_oss.= $campos_os[$i]['id_os'].', ';
        $id_oss = substr($id_oss, 0, strlen($id_oss) - 2);
?>
    <tr class='linhanormal'>
        <td colspan='3'>
            <b>* Essa O.P. está atrelada p/ a(s) OSS(s)  N.º -> <?=$id_oss;?></b>
        </td>
    </tr>
<?
    }
    if($linhas_entrada == 1) {
?>
    <tr class='linhanormal'>
        <td colspan='3'>
            <b>* Essa O.P. possui Entrada(s)</b>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>