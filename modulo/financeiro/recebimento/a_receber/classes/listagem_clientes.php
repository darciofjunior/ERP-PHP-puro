<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
session_start('funcionarios');//Não posso arrancar essa Sessão porque o Menu depende dessa p/ saber qual o Path abaixo ...

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}else if($id_emp2 == 0) {//Todas Empresas
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');
$data_atual = date('Y-m-d');

/***************Listagem de Clientes com Crédito A e B***************/
$sql = "SELECT c.`id_cliente`, c.`razaosocial`, c.`nomefantasia`, c.`ddi_com`, c.`ddd_com`, c.`telcom`, c.`credito`, c.`endereco`, 
        c.`num_complemento`, c.`cnpj_cpf`, cr.`data_vencimento_alterada` 
        FROM `clientes` c 
        INNER JOIN `contas_receberes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`status` < '2' AND cr.`data_vencimento_alterada` < '$data_atual' 
        WHERE c.`credito` IN ('A', 'B') 
        AND c.`lembrete_credito` = 'S' 
        AND c.`ativo` = '1' 
        AND (DATEDIFF('$data_atual', SUBSTRING(c.`credito_data`, 1, 10)) > '5' OR SUBSTRING(c.`credito_data`, 1, 10) = '0000-00-00') 
        GROUP BY c.`id_cliente` ORDER BY c.`razaosocial` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Consultar Clientes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Cliente(s) com Crédito <font color='yellow'>A e B, </font>que possuem Débito(s) em Atraso <br> 
            e com Data de Atualização de Crédito alterada a mais que 5 dias - 
            <font color='yellow'>
                <?=$linhas;?>
            </font>
        </td>
    </tr>
<?
if($linhas > 0) {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Razão Social
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Cr
        </td>
        <td>
            Dias de Atraso
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Endereço
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $url = '../../../cadastro/credito_cliente/detalhes.php?id_cliente='.$campos[$i]['id_cliente'].'&pop_up=1&onunload=1';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='center'>
            <font color='blue'>
                <?=$campos[$i]['credito'];?>
            </font>
        </td>
        <td align='center'>
            <font color='red'>
            <?
//Se a Data de Vencimento for diferente de Vázia então cálcula a diferença em dias ...
                if($campos[$i]['data_vencimento_alterada'] != '0000-00-00') {
                    $dias = data::diferenca_data($campos[$i]['data_vencimento_alterada'], $data_atual);
                    echo $dias[0];
                }else {
                    echo '-';
                }
            ?>
            </font>
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
        <?
            echo $campos[$i]['endereco'];
            //Daí sim printa o complemento
            if(!empty($campos[$i]['endereco'])) echo ', '.$campos[$i]['num_complemento'];
        ?>
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
}
/***************Listagem de Clientes com Crédito C***************/
//Listagem de Clientes q possuem Contas à Receber em Aberto e q estão com a Data de Vencimento em Atraso em comparação à Data Atual ...
$sql = "SELECT c.`id_cliente`, c.`razaosocial`, c.`nomefantasia`, c.`ddi_com`, c.`ddd_com`, c.`telcom`, c.`credito`, c.`endereco`, 
        c.`num_complemento`, c.`cnpj_cpf`, cr.`data_vencimento_alterada` 
        FROM clientes c 
        INNER JOIN `contas_receberes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`status` < '2' AND cr.`data_vencimento_alterada` < '$data_atual' 
        WHERE c.`credito` = 'C' 
        AND c.`lembrete_credito` = 'S' 
        AND c.`ativo` = 1 
        AND (DATEDIFF('$data_atual', SUBSTRING(c.`credito_data`, 1, 10)) > '5' OR SUBSTRING(c.`credito_data`, 1, 10) = '0000-00-00') 
        GROUP BY c.`id_cliente` ORDER BY c.`razaosocial` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Cliente(s) com Crédito <font color='yellow'>C, </font>e que não possuem Débito(s) em Atraso <br> 
            e com Data de Atualização de Crédito alterada a mais que 5 dias - 
            <font color='yellow'>
                <?=$linhas;?>
            </font>
        </td>
    </tr>
<?
if($linhas > 0) {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Razão Social
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Endereço
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        $url = '../../../cadastro/credito_cliente/detalhes.php?id_cliente='.$campos[$i]['id_cliente'].'&pop_up=1&onunload=1';
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <a href='<?=$url;?>' class='html5lightbox'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td>
        <?
            echo $campos[$i]['endereco'];
            //Daí sim printa o complemento
            if(!empty($campos[$i]['endereco'])) echo ', '.$campos[$i]['num_complemento'];
        ?>
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
}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar_contas.php?itens=1&id_emp2=<?=$id_emp2;?>'" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>