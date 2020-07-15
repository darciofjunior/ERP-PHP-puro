<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CONTA(S) ESTORNADA(S) COM SUCESSO.</font>";

if($passo == 1) {
//Traz Todas as Contas da Empresa do Menu que estão atreladas a Borderor
    switch($opt_opcao) {
        case 1://N.º da Conta
            $sql = "SELECT c.razaosocial, cr.id_conta_receber, cr.num_conta, cr.data_emissao, cr.valor, tm.simbolo 
                    FROM `contas_receberes` cr 
                    INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda 
                    INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente 
                    WHERE cr.num_conta LIKE '%$txt_consultar%' 
                    AND cr.id_empresa = '$id_emp2' 
                    AND cr.`ativo` = '1' 
                    AND cr.`status` < '2' ";
        break;
        default://Todos os Registros
            $sql = "SELECT c.razaosocial, cr.id_conta_receber, cr.num_conta, cr.data_emissao, cr.valor, tm.simbolo 
                    FROM `contas_receberes` cr 
                    INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda 
                    INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente 
                    WHERE cr.id_empresa = '$id_emp2' 
                    AND cr.`ativo` = '1' 
                    AND cr.`status` < '2' ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);

    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'estornar_contas_bordero.php?id_emp2=<?=$id_emp2;?>&valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Estornar Contas de Bordero ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if (elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        return true
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Estornar Contas de Bordero 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Dados da Conta
        </td>
        <td colspan='4'>
            Dados do Bordero
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>Conta(s)</td>
        <td>Cliente</td>
        <td>Emiss&atilde;o</td>
        <td>Valor</td>
        <td>Data</td>
        <td>Tipo de Rec</td>
        <td>Banco</td>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar Tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td> 
            <?=$campos[$i]['num_conta'];?>
        </td>
        <td align="left"> 
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td align="right"> 
            <?=$campos[$i]['simbolo'].' '.str_replace('.', ',', $campos[$i]['valor']);?>
        </td>
        <?
            //Busca alguns dados de bordero com o $id_conta_receber corrente ...
            $sql = "SELECT bc.banco, DATE_FORMAT(SUBSTRING(b.data, 1, 10), '%d/%m/%Y') AS data, tr.recebimento 
                    FROM `contas_receberes` cr 
                    INNER JOIN `borderos` b ON b.id_bordero = cr.id_bordero 
                    INNER JOIN `tipos_recebimentos` tr ON tr.id_tipo_recebimento = b.id_tipo_recebimento 
                    INNER JOIN `bancos` bc ON bc.id_banco = cr.id_banco 
                    WHERE cr.id_conta_receber = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
            $campos_bordero = bancos::sql($sql);
        ?>
        <td>
            <?=$campos_bordero[0]['data'];?>
        </td>
        <td>
            <?=$campos_bordero[0]['recebimento'];?>
        </td>
        <td>
            <?=$campos_bordero[0]['banco'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_conta_receber[]' value="<?=$campos[$i]['id_conta_receber'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<? 
        }
?>
    <tr class='linhacabecalho' align='center'> 
        <td colspan='8'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'estornar_contas_bordero.php?id_emp2=<?=$id_emp2;?>'" class="botao">
            <input type='submit' name='cmd_estornar' value='Estornar' title='Estornar' class='botao'>
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
}else if($passo == 2) {
    foreach($_POST['chkt_conta_receber'] as $id_conta_receber) {
//Aki volta a conta para o Tipo de Recebimento como Carteira ...
        $sql = "UPDATE `contas_receberes` SET `id_tipo_recebimento` = '2' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
        bancos::sql($sql);
        //Desatrela alguns campos da Tabela de Conta à Receber ...
        $sql = "UPDATE `contas_receberes` SET `id_banco` = NULL, `id_bordero` = NULL WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'estornar_contas_bordero.php?id_emp2=<?=$id_emp2;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Estornar Contas de Bordero ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}

function iniciar() {
    if(document.form.txt_consultar.disabled == false) document.form.txt_consultar.focus()
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onLoad="iniciar()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Estornar Contas de Bordero 
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" title="Consultar Contas à Receber" size='45' maxlength='45' class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type="radio" name="opt_opcao" value="1" title="Consultar Contas à Receber por: N.º da Conta" onclick="iniciar()" id='label' checked>
            <label for="label">N.º da Conta</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value="1" title="Consultar todas as Contas à Receber" onclick="limpar()" class="checkbox" id='label2'>
            <label for="label2">Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'opcoes_bordero.php?id_emp2=<?=$id_emp2;?>'" class="botao">
            <input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');iniciar()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>