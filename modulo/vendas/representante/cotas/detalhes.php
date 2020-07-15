<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/representante/cotas/cotas.php', '../../../../');
?>
<html>
<title>.:: Detalhes de Cota(s) do Representante ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function atualizar_abaixo() {
    //Controle p/ não recarregar a Tela de baixo ...
    if(document.form.nao_atualizar.value == 0) parent.location = parent.location.href
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post'>
<!--************Controle de Tela************-->
<input type='hidden' name='nao_atualizar' value='0'>
<!--****************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Detalhes de Cota(s) do Representante
            <font color='yellow'>
            <?
                $sql = "SELECT nome_fantasia 
                        FROM `representantes` 
                        WHERE `id_representante` = '$id_representante' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                echo $campos_representante[0]['nome_fantasia'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Cota Mensal
        </td>
        <td>
            Data Inicial da Vigência
        </td>
        <td>
            Data Final da Vigência
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
    /*Aqui eu trago dados de Cota Mensal do id_representante passado por parâmetro ordernando pela 
    última Data de Vigência que está em vigor ...*/
    $sql = "SELECT id_representante_cota, SUM(cota_mensal) AS total_cota_mensal, data_inicial_vigencia, data_final_vigencia 
            FROM `representantes_vs_cotas` 
            WHERE `id_representante` = '$_GET[id_representante]' 
            GROUP BY `data_inicial_vigencia` ORDER BY data_inicial_vigencia DESC ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['total_cota_mensal'], 2, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_inicial_vigencia'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_final_vigencia'], '/');?>
        </td>
        <td>
        <?
            if($i == 0) {//Só posso estar alterando a última cota que está vigência ...
        ?>
            <img src= '../../../../imagem/menu/alterar.png' border='0' onclick="document.form.nao_atualizar.value = 1;window.location = 'alterar.php?id_representante_cota=<?=$campos[$i]['id_representante_cota'];?>'" alt='Alterar Cota do Representante' title='Alterar Cota do Representante'>
        <?
            }
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>