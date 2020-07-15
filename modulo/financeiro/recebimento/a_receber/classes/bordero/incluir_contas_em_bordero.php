<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');
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

$mensagem[1] = "<font class='atencao'>NÃO HÁ CONTA(S) A SEREM INCLUIDA(S).</font>";
$mensagem[2] = "<font class='confirmacao'>CONTA(S) INCLUIDA(S) COM SUCESSO.</font>";

if($passo == 1) {
    //Traz Dados Específicos do Bordero com o id_bordero
    $sql = "SELECT bc.`banco`, SUBSTRING(b.`data`, 1, 10) AS data, tr.`recebimento` 
            FROM `borderos` b 
            INNER JOIN `tipos_recebimentos` tr ON tr.`id_tipo_recebimento` = b.`id_tipo_recebimento` 
            INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = b.`id_contacorrente` 
            INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
            INNER JOIN `bancos` bc ON bc.`id_banco` = a.`id_banco` 
            WHERE b.`id_bordero` = '$_GET[id_bordero]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
//Variáveis que utiliza para fazer o SQL abaixo ...
    $data               = $campos[0]['data'];
//Variáveis que utiliza para exibição na Tela na Barra de Título
    $recebimento        = $campos[0]['recebimento'];
    $banco              = $campos[0]['banco'];
    
    /*Aqui eu trago todas as contas que são do Tipo Livre de Débito, que estão Totalmente 
    em Aberto e que são do Tipo "Carteira" ...*/
    $sql = "SELECT cr.`id_conta_receber` 
            FROM `contas_receberes` cr 
            INNER JOIN `nfs` ON nfs.`id_nf` = cr.`id_nf` AND nfs.`livre_debito` = 'S' 
            WHERE cr.`ativo` = '1' 
            AND cr.`id_empresa` = '$id_emp2' 
            AND cr.`status` = '0' 
            AND cr.`id_tipo_recebimento` = '2' 
            ORDER BY `id_conta_receber` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($l = 0; $l < $linhas; $l++) $id_contas[] = $campos[$l]['id_conta_receber'];
    //Arranjo Ténico
    if(count($id_contas) == 0) $id_contas[] = '0';
    $vetor_contas = implode(',', $id_contas);
	
    /*Traz somente do Tipo Carteiro e do Tipo da Empresa do Menu menos as do Tipo Livre de Débito, 
    que estão totalmente em Aberta na ordem das mais Recentes ...*/
    $sql = "SELECT c.`razaosocial`, cr.`id_conta_receber`, cr.`id_tipo_moeda`, cr.`num_conta`, cr.`data_vencimento_alterada`, cr.`valor` 
            FROM `contas_receberes` cr 
            INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente 
            WHERE cr.`id_conta_receber` NOT IN ($vetor_contas) 
            AND cr.`ativo` = '1' 
            AND cr.`id_empresa` = '$id_emp2' 
            AND cr.`status` = '0' 
            AND cr.`id_tipo_recebimento` = '2' ORDER BY cr.`data_vencimento_alterada` DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>    
    <Script Language = 'JavaScript'>
        window.location = 'incluir_contas_em_bordero.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Incluir Conta(s) ao Bordero ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            if (elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
}
</Script>
</head>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'> 
        <td colspan='5'>
            Incluir Conta(s) ao Bordero 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?> - 
            </font>
            <br/>
            <font color='yellow'>
                Data <?=data::datetodata($data, '/');?> - <?=$recebimento;?> - <?=$banco;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar Tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            Conta(s)
        </td>
        <td>
            Cliente
        </td>
        <td>
            Data de Venc.
        </td>
        <td>
            Valor
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_conta_receber[]' value='<?=$campos[$i]['id_conta_receber'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=$campos[$i]['num_conta'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td> 
            <?=data::datetodata($campos[$i]['data_vencimento_alterada'], '/');?>
        </td>
        <td> 
        <?
            $sql = "SELECT simbolo 
                    FROM `tipos_moedas` 
                    WHERE `id_tipo_moeda` = '".$campos[$i]['id_tipo_moeda']."' LIMIT 1 ";
            $campos_moeda   = bancos::sql($sql);
            $simbolo        = (count($campos_moeda) == 1) ? $campos_moeda[0]['simbolo'] : '';
            echo $simbolo.' '.str_replace('.', ',', $campos[$i]['valor']);
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir_contas_em_bordero.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<input type='hidden' name='id_bordero' value='<?=$_GET['id_bordero'];?>'>
</form>
</body>
</html>
<?
    }
}else if($passo == 2) {
    foreach($_POST['chkt_conta_receber'] as $id_conta_receber) {
        //Gera bordero na Tabela de Contas à Receber ...
        $sql = "UPDATE `contas_receberes` SET `id_bordero` = '$_POST[id_bordero]' WHERE id_conta_receber = '$id_conta_receber' LIMIT 1 ";
        bancos::sql($sql);
        
        //Busca o Tipo de Recebimento e o Banco referente à Conta Corrente do Bordero ...
        $sql = "SELECT b.id_tipo_recebimento, bc.id_banco 
                FROM `borderos` b 
                INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = b.`id_contacorrente` 
                INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                INNER JOIN `bancos` bc ON bc.`id_banco` = a.`id_banco` 
                WHERE b.`id_bordero` = '$_POST[id_bordero]' ";
        $campos                 = bancos::sql($sql);
        $id_tipo_recebimento    = $campos[0]['id_tipo_recebimento'];
        $id_banco               = $campos[0]['id_banco'];

        //Atualiza o Tipo de recebimento e o Banco na Tabela de Contas à Receberes ...
        $sql = "UPDATE `contas_receberes` SET `id_tipo_recebimento` = '$id_tipo_recebimento', `id_banco` = '$id_banco' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_contas_em_bordero.php<?=$parametro;?>&id_emp2=<?=$id_emp2;?>&valor=2'
    </Script>
<?
}else {
    //Listo todos os borderos que estão cadastrados p/ a determinada Empresa do Menu ...
    $sql = "SELECT b.id_bordero, b.data, bc.banco, tr.recebimento 
            FROM `borderos` b 
            INNER JOIN `tipos_recebimentos` tr ON tr.`id_tipo_recebimento` = b.`id_tipo_recebimento` 
            INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = b.`id_contacorrente` AND cc.`id_empresa` = '$id_emp2' 
            INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
            INNER JOIN `bancos` bc ON bc.`id_banco` = a.`id_banco` 
            GROUP BY b.data, b.id_contacorrente, b.id_tipo_recebimento order by b.data DESC ";
    $campos = bancos::sql($sql, $inicio, 25, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        alert('NÃO EXISTEM BORDERO(S) CADASTRADO(S) !')
        window.location = 'opcoes_bordero.php'
    </Script>
<?        
    }else {//Aqui tem pelo menos 1 bordero Cadastrado ...
?>
<html>
<head>
<title>.:: Incluir Conta(s) em Bordero já existente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Incluir Conta(s) em Bordero já existente
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
            $url = 'incluir_contas_em_bordero.php?passo=1&id_emp2='.$id_emp2.'&id_bordero='.$campos[$i]['id_bordero'];
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
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>