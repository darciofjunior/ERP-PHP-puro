<?
require('../../../lib/segurancas.php');
require('../../../lib/cascates.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) FORNECEDOR(ES) ATRELADO(S) PARA ESTE PRODUTO.</font>";
$mensagem[2] = 'FORNECEDOR DEFAULT ALTERADO COM SUCESSO PARA ESTE PI !!!';

if($passo == 1) {
//Aqui eu estou trocando o fornecedor Default desse PI ...
    if(!empty($opt_fornecedor)) {
//Atualização do Novo Fornecedor Default na tabela de PI ...
        $sql = "UPDATE `produtos_insumos` SET `id_fornecedor_default` = '$opt_fornecedor' WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
/*****************Aqui eu atualizo as "telas, Pop-Ups" que contém esse arquivo dentro delas
Faço isso porque esse arquivo aki está como iframe dentro das outras telas*****************/	
        if(typeof(window.top.document.form) == 'object') window.top.location.href = top.location
/*************************************************************************************************/
//Redirecionando p/ o arquivo normal
        alert('<?=$mensagem[2];?>')
        window.location = 'marcar_fornecedor_default.php?id_produto_insumo=<?=$id_produto_insumo;?>'
    </Script>
<?
}else {
    $sql = "SELECT fpi.*, f.`razaosocial`, g.`referencia`, pi.`discriminacao` 
            FROM `fornecedores_x_prod_insumos` fpi 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` AND f.`ativo` = '1' AND f.`razaosocial` <> '' 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
            WHERE fpi.`id_produto_insumo` = '$id_produto_insumo' 
            AND fpi.`ativo` = '1' ORDER BY f.`razaosocial` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
//Não existe nenhum Fornecedor para este PI ...
    if($linhas == 0) {
?>
<html>
<title>.:: Marcar Fornecedor Default ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<body>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_atrelar_fornecedor' value='Atrelar Fornecedor' title='Atrelar Fornecedor' onclick="nova_janela('../../compras/produtos_fornecedores/atrelar_fornecedor_em_pi.php?id_produto_insumo=<?=$id_produto_insumo;?>', 'CONSULTAR', '', '', '', '', 480, 880, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
//Existe pelo menos 1 fornecedor, então eu listo estes para o Usuário ...
    }else {
?>
<html>
<head>
<title>.:: Marcar Fornecedor Default ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Verifico se existe pelo menos 1 option para poder chamar a função de Options 
    var elementos = document.form.elements
    var achou_option = 0
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'opt_fornecedor') {
            achou_option = 1
            i = elementos.length//Para sair fora do loop
        }
    }
//Como eu tenho pelo menos 1 option chamo a função forçando o usuário a selecionar pelo menos 1 option ...
    if(achou_option == 1) {
        return option('form', 'opt_fornecedor', 'SELECIONE UMA OPÇÃO !')
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='17'>
            Fornecedor(es) do PI => 
            <font color='yellow'>
                Ref: 
            </font>
            <?=$campos[0]['referencia'];?> - 
            <font color='yellow'>
                Disc: 
            </font>
            <?=$campos[0]['discriminacao'];?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Item
        </td>
        <td rowspan='2'>
            Fornecedor
        </td>
        <td rowspan='2'>
            IPI %
        </td>
        <td rowspan='2'>
            IPI Incl  %
        </td>
        <td rowspan='2'>
            ICMS %
        </td>
        <td rowspan='2'>
            Red.BC %
        </td>
        <td rowspan='2'>
            IVA %
        </td>
        <td colspan='4'>
            Pre&ccedil;o (Nacional)
        </td>
        <td colspan='6'>
            Pre&ccedil;o (Internacional)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font title='Tipo da Moeda' style='cursor:help'>
                $
            </font>
        </td>
        <td>
            Fat.
        </td>
        <td>
            <font title='Adicional Custo Int. PI R$' style='cursor:help'>
                Adic.<br/> Custo  PI
            </font>
        </td>
        <td>
            Fat. Custo c/ Adic.
        </td>
        <td>
            <font title='Tipo da Moeda' style='cursor:help'>
                $
            </font>
        </td>
        <td>
            Fat. 
        </td>
        <td>
            <font title='Adicional Custo Int. PI R$' style='cursor:help'>
                Adic.<br/> Custo  PI
            </font>
        </td>
        <td>
            Fat. Custo c/ Adic.
        </td>
        <td>
            Moeda<br/>p/ Compra
        </td>
        <td>
            Fat. Custo c/ Adic.
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="options('form', 'opt_fornecedor', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
        <?
            //Aqui eu verifico se esse Fornecedor do Loop é o Default ...
            $sql = "SELECT `id_fornecedor_default` 
                    FROM `produtos_insumos` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' 
                    AND `id_fornecedor_default` = '".$campos[$i]['id_fornecedor']."' 
                    AND `id_fornecedor_default` > '0' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_default = bancos::sql($sql);
            if(count($campos_default) == 1) {//Este é o Default, então só printo na Tela ...
                echo '<font color="blue"><b>DEFAULT</b></font>';
//Coloco esse hidden para não perder o controle de índice ...
        ?>
                <input type='hidden'>
        <?
            }else {//Este não é o Default, então exibo o Option p/ poder trocar por Outro ...
        ?>
                <input type='radio' name='opt_fornecedor' value="<?=$campos[$i]['id_fornecedor'];?>" onclick="options('form', 'opt_fornecedor', '<?=$i;?>', '#E8E8E8')">
        <?
            }
        ?>
        </td>	
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
            &nbsp;
            <font color='black' size='-2' title='Data da Última Atualização' style='cursor:help'>
                <b>
                    (<?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/');
            
                        //Aqui eu busco o nome do Funcionário que fez a última modificação na Lista de Preço ...
                        if(!empty($campos[$i]['id_funcionario'])) {
                            $sql = "SELECT SUBSTRING_INDEX(`nome`, ' ', 1) AS nome 
                                    FROM `funcionarios` 
                                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
                            $campos_funcionario = bancos::sql($sql);
                            echo ' - '.$campos_funcionario[0]['nome'];
                        }
                    ?>)
                </b>
            </font>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['ipi'], 2, ',', '.');?>
        </td>
        <td>
        <?
            if($campos[$i]['ipi_incluso'] == 'S') echo '<font color="red" title="IPI Incluso de '.number_format($campos[$i]['ipi'], 2, ',', '.').' %" style="cursor:help"><b>(Incl)</b></font>';
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['icms'], 2, ',', '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['reducao'], 2, ',', '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['iva'], 2, ',', '.');?>
        </td>
        <td>
            R$ 
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['preco_faturado'], 2, ',', '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['preco_faturado_adicional'], 2, ',', '.');?>
        </td>
        <td>
            <?
                $fat_custo_c_adic_nac           = $campos[$i]['preco_faturado'] + $campos[$i]['preco_faturado_adicional'];
                $icms_c_red                     = $campos[$i]['icms'] * (1 - $campos[$i]['reducao'] / 100);
                $fat_custo_c_adic_nac_sem_icms  = $fat_custo_c_adic_nac * (1 - $icms_c_red / 100);
            ?>
            <font title='S/ ICMS = <?=number_format($fat_custo_c_adic_nac_sem_icms, 2, ',', '.');?>' style='cursor:help'>
                <?=segurancas::number_format($campos[$i]['preco_faturado'] + $campos[$i]['preco_faturado_adicional'], 2, ',', '.');?>
            </font>
        </td>
        <td>
        <?
//Aqui o Controle de Moedas é um pouco diferente, porque puxa do Esq. da Lista de Preço ...
            if($campos[$i]['tp_moeda'] == 1) {
                echo 'U$';
            }else if($campos[$i]['tp_moeda'] == 2) {
                echo '&euro;';
            }else {
                echo 'R$';
            }
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['preco_faturado_export'], 2, ',', '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos[$i]['preco_faturado_export_adicional'], 2, ',', '.');?>
        </td>
        <td>
        <?
            $fat_custo_c_adic_inter                 = $campos[$i]['preco_faturado_export'] + $campos[$i]['preco_faturado_export_adicional'];
            $fat_custo_c_adic_inter_em_rs           = $fat_custo_c_adic_inter * $campos[$i]['valor_moeda_compra'];
            $fat_custo_c_adic_inter_em_rs_sem_icms  = $fat_custo_c_adic_inter_em_rs * (1 - $icms_c_red / 100);
            echo segurancas::number_format($fat_custo_c_adic_inter, 2, ',', '.');
        ?>
        </td>
        <td>
            R$ <?=segurancas::number_format($campos[$i]['valor_moeda_compra'], 2, ',', '.');?>
        </td>
        <td>
            <font title='S/ ICMS = <?=number_format($fat_custo_c_adic_inter_em_rs_sem_icms, 2, ',', '.');?>' style='cursor:help'>
                R$ <?=segurancas::number_format($fat_custo_c_adic_inter_em_rs, 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal' align='left'>
        <td colspan='17'>
            Moedas custos:
            U$ <?=segurancas::number_format(genericas::variavel(7), 4, '.');?> &nbsp;&nbsp; - &nbsp;&nbsp; &euro;$ <?=segurancas::number_format(genericas::variavel(8), 4, '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='17'>
            <input type='button' name='cmd_atrelar_fornecedor' value='Atrelar Fornecedor' title='Atrelar Fornecedor' onclick="nova_janela('../../compras/produtos_fornecedores/atrelar_fornecedor_em_pi.php?id_produto_insumo=<?=$id_produto_insumo;?>', 'CONSULTAR', '', '', '', '', 480, 880, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:green' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='parent.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_produto_insumo' value='<?=$id_produto_insumo;?>'>
</form>
</body>
</html>
<?
    }
}
?>