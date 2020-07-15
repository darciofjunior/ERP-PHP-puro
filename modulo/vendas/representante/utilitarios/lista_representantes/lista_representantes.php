<?
require('../../../../../lib/segurancas.php');
//Só irá exibir o menu e fazer a Segurança de Permissão quando esta Tela for aberta de forma normal ...
if(empty($_GET['pop_up'])) {
    require('../../../../../lib/menu/menu.php');
    segurancas::geral($PHP_SELF, '../../../../../');
}
//Aqui eu listo todos os Representantes que não são "funcionários" ...
$sql = "SELECT * 
        FROM `representantes` 
        WHERE `ativo` = '1' 
        AND `id_representante` NOT IN (
        SELECT r.id_representante 
        FROM `representantes` r 
        INNER JOIN `representantes_vs_funcionarios` rf ON rf.id_representante = r.id_representante 
        WHERE r.`ativo` = '1' 
        AND r.`id_representante`) 
        ORDER BY uf, nome_fantasia ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Listagem de Representante(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../js/sessao.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Listagem de Representante(s)
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <font size='-1'>
                UF: <?=$campos[$i]['uf'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='-1'>
                <b>Nome Fantasia: </b>
            </font>
        </td>
        <td>
            <font size='-1'>
                <?=$campos[$i]['nome_fantasia'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='-1'>
                <b>Contato: </b>
            </font>
        </td>
        <td>
            <font size='-1'>
                <?=$campos[$i]['contato'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormaldestaque'>
        <td>
            <font size='-1'>
                <b>Banco / Agência / Conta Corrente: </b>
            </font>
        </td>
        <td>
            <font size='-1'>
                <b><?=$campos[$i]['banco'].' / '.$campos[$i]['agencia'].' / '.$campos[$i]['conta_corrente'];?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormaldestaque'>
        <td>
            <font size='-1'>
                <b>Correntista: </b>
            </font>
        </td>
        <td>
            <font size='-1'>
                <b><?=$campos[$i]['correntista'];?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='-1'>
                <b>Endereço: </b>
            </font>
        </td>
        <td>
            <font size='-1'>
            <?
                if(!empty($campos[$i]['bairro'])) {//Escreve o Bairro e Concat. com o -
                    $bairro = ' - '.$campos[$i]['bairro'];
                }else {//Bairro Vázio
                    $bairro = '';
                }
                echo $campos[$i]['endereco'].', '.$campos[$i]['num_comp'].$bairro;
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='-1'>
                <b>Cep: </b>
            </font>
        </td>
        <td>
            <font size='-1'>
                <?=$campos[$i]['cep'].' - '.$campos[$i]['cidade'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='-1'>
                <b>Fone: </b>
            </font>
        </td>
        <td>
            <font size='-1'>
                <?=$campos[$i]['fone'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='-1'>
                <b>Cel / Fax:</b>
            </font>
        </td>
        <td>
            <font size='-1'>
                <?=$campos[$i]['fax'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='-1'>
                <b>E-mail: </b>
            </font>
        </td>
        <td>
            <font size='-1'>
                <?=$campos[$i]['email'];?>
            </font>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>