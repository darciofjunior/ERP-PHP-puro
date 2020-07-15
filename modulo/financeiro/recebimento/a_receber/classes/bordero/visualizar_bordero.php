<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/genericas.php');

session_start('funcionarios');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}else if($id_emp2 == 0) {//Todas Empresas
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../../');

if($passo == 1) {
    //Traz Dados Específicos do Bordero com o id_bordero
    $sql = "SELECT bc.`banco`, SUBSTRING(b.`data`, 1, 10) AS data, cc.`id_contacorrente`, tr.`id_tipo_recebimento`, tr.`recebimento` 
            FROM `borderos` b 
            INNER JOIN `tipos_recebimentos` tr ON tr.`id_tipo_recebimento` = b.`id_tipo_recebimento` 
            INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = b.`id_contacorrente` 
            INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
            INNER JOIN `bancos` bc ON bc.`id_banco` = a.`id_banco` 
            WHERE b.`id_bordero` = '$_GET[id_bordero]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
//Variáveis que utiliza para fazer o SQL abaixo ...
    $data               = $campos[0]['data'];
    $id_conta_corrente  = $campos[0]['id_contacorrente'];
    $id_tipo_recebimento = $campos[0]['id_tipo_recebimento'];
//Variáveis que utiliza para exibição na Tela na Barra de Título
    $recebimento        = $campos[0]['recebimento'];
    $banco              = $campos[0]['banco'];
/*Busca todas as contas à Receber com a mesma data do bordero, do mesmo tipo de Conta Corrente e 
do mesmo Tipo de Recebimento*/
    $sql = "SELECT cr.`id_conta_receber` 
            FROM `borderos` b 
            INNER JOIN `contas_receberes` cr ON cr.`id_bordero` = b.`id_bordero` 
            WHERE b.`data` = '$data' 
            AND b.`id_contacorrente` = '$id_conta_corrente' 
            AND b.`id_tipo_recebimento` = '$id_tipo_recebimento' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($l = 0; $l < $linhas; $l++) $id_contas[] = $campos[$l]['id_conta_receber'];
//Arranjo Ténico
    if($id_contas == '') $id_contas[] = '0';
    $vetor_contas = implode(',', $id_contas);
//Busca de alguns dados das Contas à Receber p/ apresentação na Tela ...
    $sql = "SELECT c.`razaosocial`, cr.`id_conta_receber`, cr.`num_conta`, cr.`data_emissao`, cr.`valor`, tm.`simbolo` 
            FROM `contas_receberes` cr 
            INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = cr.`id_tipo_moeda` 
            WHERE cr.`id_conta_receber` IN ($vetor_contas) ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        alert('BORDERO NÃO POSSUI NENHUMA CONTA ATRELADA !')
        window.location = 'opcoes_bordero.php'
    </Script>
<?        
    }else {//Aqui tem pelo menos 1 conta atrelada ao bordero ...
?>
<html>
<head>
<title>.:: Visualizar Bordero(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Visualizar Bordero(s)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            <font color='yellow'>
                Bordero: <?=data::datetodata($data, '/');?> - <?=$recebimento;?> - <?=$banco;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Conta(s)
        </td>
        <td>
            Cliente
        </td>
        <td>
            Emiss&atilde;o
        </td>
        <td>
            Valor
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td> 
            <?=$campos[$i]['num_conta'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td align='right'>
            <?=$campos[$i]['simbolo'].' '.str_replace('.', ',', $campos[$i]['valor']);?>
        </td>
    </tr>
<? 
        }
?>
    <tr class='linhacabecalho' align='center'> 
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'visualizar_bordero.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick="nova_janela('pdf/relatorio_bordero.php?id_bordero=<?=$_GET['id_bordero'];?>&id_emp2=<?=$id_emp2;?>', 'RELATORIO_BORDERO', 'F')" class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else {
    //Listo todos os borderos que estão cadastrados p/ a determinada Empresa do Menu ...
    $sql = "SELECT b.`id_bordero`, b.`data`, bc.`banco`, tr.`recebimento` 
            FROM `borderos` b 
            INNER JOIN `tipos_recebimentos` tr ON tr.`id_tipo_recebimento` = b.`id_tipo_recebimento` 
            INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = b.`id_contacorrente` AND cc.`id_empresa` = '$id_emp2' 
            INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
            INNER JOIN `bancos` bc ON bc.`id_banco` = a.`id_banco` 
            GROUP BY b.`data`, b.`id_contacorrente`, b.`id_tipo_recebimento` ORDER BY b.`data` DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        alert('NÃO EXISTEM BORDERO(S) CADASTRADO(S) !')
        window.location = 'opcoes_bordero.php'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Visualizar Bordero(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Visualizar Bordero(s)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'> 
        <td colspan='2'>
            Data do Bordero
        </td>
        <td>
            Tipo Recebimento
        </td>
        <td>
            Banco
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = 'visualizar_bordero.php?passo=1&id_emp2='.$id_emp2.'&id_bordero='.$campos[$i]['id_bordero'];
?>
    <tr class='linhanormal' align='center'>
        <td width='10'>
            <a href="<?=$url;?>">
                <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <a href="<?=$url;?>" class='link'>
                <?=data::datetodata($campos[$i]['data'], '/');?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['recebimento'];?>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'opcoes_bordero.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>