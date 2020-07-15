<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/calculos.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/data.php');
session_start('funcionarios');

/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
$nivel_arquivo_principal = '../../../..';
//Aqui eu vou puxar a Tela única de Filtro de Notas Fiscais que serve para o Sistema Todo ...
require('tela_geral_filtro.php');

//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Consultar Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Consultar Nota(s) Fiscal(s) de Entrada
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Nota
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Valor Total <br/>dos Produtos
        </td>
        <td>
            Valor Total <br/>da Nota
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Data Ent.
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $url = 'itens/itens.php?id_nfe='.$campos[$i]['id_nfe'].'&pop_up=1';
//Cálculo da NF ...
        $calculo_total_impostos = calculos::calculo_impostos(0, $campos[$i]['id_nfe'], 'NFC');
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='<?=$url;?>' class='html5lightbox'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['num_nota'];?>
            </a>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['razaosocial'];
            //Caixa de Compras ...
            if($campos[$i]['pago_pelo_caixa_compras'] == 'S') echo '<font color="blue"><b> (CAIXA DE COMPRAS)</b></font>';
            //Só mostramos quando Ignorar Impostos no Financiamento ...
            if($campos[$i]['ignorar_impostos_financiamento'] == 'S') echo '<font color="darkred" title="Ignorar Impostos no Financiamento" style="cursor:help"><b> (IIF)</b></font>';
            //Nota já liberada ...
            if($campos[$i]['situacao'] == 2) echo '<font color="red"><b> (Liberada)</b></font>';
            //Nota Importada no Financeiro ...
            if($campos[$i]['importado_financeiro'] == 'S') echo '<font color="darkblue" title="Importado no Financeiro" style="cursor:help"><b> (Import. Financ.)</b></font>';
        ?>
        </td>
        <td align='right'>
            R$ <?=number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');?>
        </td>
        <?
            if(substr($campos[$i]['data_emissao'], 0, 10) == '0000-00-00') {
                $data_emissao = '';
            }else {
                $data_emissao = data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/');
            }
        ?>
        <td>
            <?=$data_emissao;?>
        </td>
        <?
            if(substr($campos[$i]['data_entrega'],0,10) == '0000-00-00') {
                $data_entrega = '';
            }else {
                $data_entrega = data::datetodata(substr($campos[$i]['data_entrega'], 0, 10), '/');
            }
        ?>
        <td>
            <?=$data_entrega;?>
        </td>
        <td align="left" >
            <?=$campos[$i]['nomefantasia'];?>
            (<?
                if($campos[$i]['tipo'] == 1) {
                    echo 'NF';
                }else {
                    echo 'SGD';
                }
            ?>)
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type="button" name="cmd_voltar" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'consultar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>