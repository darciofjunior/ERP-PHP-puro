<?
require('../../../lib/segurancas.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
require('class_pdt.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

if($representante == '') $representante = '%';
$retorno    = pdt::funcao_geral_financeiros($tipo_retorno, $dias, $representante, $inicio, $pagina, $paginacao = 'sim');
$campos     = $retorno['campos'];
$linhas_financeiros = count($campos);
?>
<html>
<head>
<title>.:: Detalhe(s) Financeiro(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    if($linhas_financeiros == 0) {
?>
    <tr class='atencao' align='center'>
        <td>
            NÃO EXISTE(M) PEDIDO(S) PENDENTE(S) NESTA CONDIÇÃO
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Detalhe(s) Financeiro(s)
            <?
                if(!empty($dias)) echo ' nos últimos <font color="yellow">'.$dias.'</font> dias';
            ?>
        </td>
    </tr>
<?
        if($tipo_retorno == 0) {//Créditos Bloqueados ...
?>
<!--*********************************Créditos Bloqueados************************************-->
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Nome Fantasia
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
            for($i = 0; $i < $linhas_financeiros; $i++) {
                $url = "javascript:nova_janela('../../classes/cliente/alterar.php?passo=1&id_cliente=".$campos[$i]['id_cliente']."&ignorar_sessao=1', 'POP', '', '', '', '', '550', '950', 'c', 'c', '', '', 's', 's', '', '', '') ";
                $credito = financeiros::controle_credito($campos[$i]['id_cliente']);
?>
    <tr class='linhanormal'>
        <td width='10'>
            <a href="<?=$url;?>" class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='center'>
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
        <td align='center'>
            <font color='blue'>
                <?=$credito;?>
            </font>
        </td>
        <td align='center'>
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
        }else {//Contas Atrasadas ...
?>
<!--************************************Contas Atrasadas************************************-->
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.° Conta
        </td>
        <td>
            Cliente
        </td>
        <td>
            Representante
        </td>
        <td>
            Empresa
        </td>
        <td>
            Data de Vencimento
        </td>
        <td>
            Valor
        </td>
        <td>
            Valor Recebido
        </td>
        <td>
            Valores Extras
        </td>
        <td>
            Valor à Receber
        </td>
    </tr>
<?
            $data_atual_americano = date('Y-m-d');
            for ($i = 0; $i < $linhas_financeiros; $i++) {
                $url = "javascript:nova_janela('../../financeiro/recebimento/detalhes.php?id_conta_receber=".$campos[$i]['id_conta_receber']."', 'POP', '', '', '', '', '550', '950', 'c', 'c', '', '', 's', 's', '', '', '') ";
				
                $data_vencimento        = $campos[$i]['data_vencimento'];
                $calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
                $valor_reajustado       = $calculos_conta_receber['valor_reajustado'];
                $valores_extra          = $calculos_conta_receber['valores_extra'];
                //Contas Vencidas ou à Vencer ...
                $color                  = ($data_vencimento < $data_atual_americano) ? 'red' : '';
?>
    <tr class='linhanormal' align='center'>
        <td width='10'>
            <a href="<?=$url;?>" class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['num_conta'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td>
        <?
            //Aqui eu busco o Representante através do id_cliente ...
            $sql = "SELECT r.`nome_fantasia` 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                    WHERE cr.`id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['nome_fantasia'];
        ?>
        </td>
        <td>
            <font color='<?=$color;?>'>
                <?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
            </font>
        </td>
        <td>
            <font color='<?=$color;?>'>
                <?=data::datetodata($data_vencimento, '/');?>
            </font>
        </td>
        <td align='right'>
            <font color='<?=$color;?>'>
            <?
                if($campos[$i]['valor'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $campos[$i]['simbolo'].' '.number_format($campos[$i]['valor'], 2, ',', '.');
                }
            ?>
            </font>
        </td>
        <td align='right'>
            <font color='<?=$color;?>'>
            <?
                if($campos[$i]['valor_pago'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo $campos[$i]['simbolo'].' '.number_format($campos[$i]['valor_pago'], 2, ',', '.');
                }
            ?>
                </font>
        </td>
        <td align='right'>
            <font color='<?=$color;?>'>
                <?='R$ '.number_format($valores_extra, 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <font color='<?=$color;?>'>
                <?='R$ '.number_format($valor_reajustado, 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
            }
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            &nbsp;
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</body>
</html>