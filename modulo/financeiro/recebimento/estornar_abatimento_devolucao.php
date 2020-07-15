<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');//Essa biblioteca é requerida dentro da Intermodular ...
require('../../../lib/intermodular.php');//Essa biblioteca é utilizada dentro da Biblioteca 'faturamentos' ...

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../');

if(!empty($_POST['hdd_conta_receber_nf_devolucao'])) {
    //Busca de alguns dados antes de Excluir à NF de Devolução da Conta à Receber ...
    $sql = "SELECT * 
            FROM `contas_receberes_vs_nfs_devolucoes` 
            WHERE `id_conta_receber_nf_devolucao` = '$_POST[hdd_conta_receber_nf_devolucao]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    //Retirando o Abatimento de Devolução da Duplicatas à Receber ...
    $sql = "DELETE FROM `contas_receberes_vs_nfs_devolucoes` WHERE `id_conta_receber_nf_devolucao` = '$_POST[hdd_conta_receber_nf_devolucao]' LIMIT 1 ";
    bancos::sql($sql);
//************************************************************//
//Mudo o Status da Nota de Devolução p/ Hum para que esta possa ser Importada novamente no Futuro ...
//************************************************************//
    $sql = "UPDATE `nfs` SET `importado_financeiro`= 'N' WHERE `id_nf` = '".$campos[0]['id_nf_devolucao']."' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.opener.parent.itens.document.form.recarregar.value = 1
        window.location = 'alterar.php?id_conta_receber=<?=$campos[0]['id_conta_receber'];?>&valor=4'
    </Script>
<?
}else {
//Busca dados da Duplicata e NF de Devolução com o $id_conta_receber_nf_devolucao passado por parâmetro ...
    $sql = "SELECT nfs.`id_nf`, nfs.`id_empresa`, nfs.`data_emissao`, nfs.`valor_dolar_dia`, 
            nfs.`suframa`, c.`id_pais`, c.`razaosocial`, crnd.`id_nf_devolucao` 
            FROM `contas_receberes_vs_nfs_devolucoes` crnd 
            INNER JOIN `contas_receberes` cr ON cr.`id_conta_receber` = crnd.`id_conta_receber` 
            INNER JOIN `nfs` ON nfs.`id_nf` = cr.`id_nf` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            WHERE crnd.`id_conta_receber_nf_devolucao` = '$_GET[id_conta_receber_nf_devolucao]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
    $id_nf              = $campos[0]['id_nf'];
    $id_empresa_nota 	= $campos[0]['id_empresa'];
    $suframa            = $campos[0]['suframa'];
    $id_pais            = $campos[0]['id_pais'];
    $razaosocial        = $campos[0]['razaosocial'];
    $numero_saida       = faturamentos::buscar_numero_nf($campos[0]['id_nf'], 'S');
    $numero_devolucao   = faturamentos::buscar_numero_nf($campos[0]['id_nf_devolucao'], 'D');

//Aqui verifica o Tipo de Nota ...
    if($id_empresa_nota == 1 || $id_empresa_nota == 2) {
        $nota_sgd = 'N';//var surti efeito lá embaixo
        $tipo_nota = ' (NF)';
    }else {
        $nota_sgd = 'S'; //var surti efeito lá embaixo
        $tipo_nota = ' (SGD)';
    }

    if($campos[0]['data_emissao'] != '0000-00-00') $data_emissao = data::datetodata($campos[0]['data_emissao'], '/');

    $valor_dolar_nota       = $campos[0]['valor_dolar_dia'];
    $observacao             = $campos[0]['observacao'];
    $calculo_total_impostos = calculos::calculo_impostos(0, $campos[0]['id_nf_devolucao'], 'NF');
    $simbolo_moeda          = ($id_pais == 31) ? 'R$ ' : 'U$ ';
?>
<html>
<head>
<title>.:: Estornar Abatimento de Devolução ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar(estornar) {
    if(estornar == 'S') {//Significa que é possível estornar a NF de Devolução ...
        var resposta = confirm('TEM CERTEZA DE QUE DESEJA ESTORNAR ESSE ABATIMENTO DE DEVOLUÇÃO NF N.º <?=$numero_devolucao;?> DESSA DUPLICATA ?')
        if(resposta == true) {
            //Faço esse controle p/ que o usuário não fique submetendo essa devolução + de uma vez ...
            document.form.cmd_salvar.disabled = true
            document.form.submit()
        }
    }else {//Não é possível estornar a NF de Devolução devido existir(em) uma ou mais duplicatas Quitadas ...
        alert('NÃO É POSSÍVEL ESTORNAR ESSE ABATIMENTO DE DEVOLUÇÃO DEVIDO EXISTIR(EM) DUPLICATA(S) QUITADA(S) !!!')
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<!--********************************************Controles de Tela********************************************-->
<input type='hidden' name='hdd_conta_receber_nf_devolucao' value='<?=$_GET['id_conta_receber_nf_devolucao'];?>'>
<!--Esse parâmetro de $tela_menu igual a 1 identifica que esse arquivo está sendo acessado do Menu Principal
de Estornar Devolução (Automática)-->
<input type='hidden' name='tela_menu' value="<?=$tela_menu;?>">
<input type='hidden' name='id_emp2' value="<?=$id_emp2;?>">
<!--*********************************************************************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Estornar Abatimento de Devolução NF N.º 
            <a href="javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[0]['id_nf_devolucao'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 1010, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Faturamento" class='link'>
                <font color='yellow'>
                    <?=$numero_devolucao;?> 
                </font>
            </a>
            da <br>NF de Saída N.º 
            <a href="javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[0]['id_nf'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 1010, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Faturamento" class='link'>
                <font color='yellow'>
                    <?=$numero_saida;?>
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='30%'>
            <b>Cliente:</b>
        </td>
        <td>
            <?=$razaosocial;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Representante:</b>
        </td>
        <td>
        <?
//Verifico qual foi o Representante que teve a maior venda em Nota Fiscal 
//Aqui eu coloco esse comando (SUM) para me retornar o representante que teve a maior venda na NF ...
            $sql = "SELECT SUM(nfsi.valor_unitario) AS valor_unitario, r.nome_fantasia 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `representantes` r ON r.id_representante = nfsi.id_representante 
                    WHERE nfsi.`id_nf` = '$_GET[id_conta_receber_nf_devolucao]' GROUP BY nfsi.id_representante ORDER BY nfsi.valor_unitario DESC LIMIT 1 ";
            $campos_nfs_item = bancos::sql($sql);
            echo $campos_nfs_item[0]['nome_fantasia'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Emissão:</b>
        </td>
        <td>
            <?=$data_emissao;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>        
            <b>Valor Total da Nota: </b>
        </td>
        <td>
        <?
            //Quando o Cliente é "Estrangeiro", trabalho com a Nota Fiscal no valor em U$ ...
            $valor_total_nota_devolucao = ($id_pais != 31) ? $calculo_total_impostos['valor_total_nota_us'] : $calculo_total_impostos['valor_total_nota'];
            echo $simbolo_moeda.number_format($valor_total_nota_devolucao, 2, ',', '.');
        ?>
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='2'>
            <iframe name='detalhes' id='detalhes' src = '../../classes/follow_ups/detalhes.php?identificacao=<?=$id_nf;?>&origem=5' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
    <tr class='linhanormal'>
        <td>
            <b>Valor Dólar da Nota: </b>
        </td>
        <td>
            <?='R$ '.number_format($valor_dolar_nota, 4, ',', '.');?>
        </td>
    </tr>
<?
    /*Nessa parte o sistema busca todas as Duplicatas de NF(s) de Saída que tiveram abatimento através 
    da NF de Devolução acima ...*/
    $sql = "SELECT cr.`id_conta_receber`, cr.`num_conta`, cr.`data_emissao`, cr.`data_vencimento_alterada`, 
            cr.`valor`, cr.`status`, crnd.`valor_devolucao` 
            FROM `contas_receberes_vs_nfs_devolucoes` crnd 
            INNER JOIN `contas_receberes` cr ON cr.`id_conta_receber` = crnd.`id_conta_receber` 
            WHERE crnd.`id_conta_receber_nf_devolucao` = '$_GET[id_conta_receber_nf_devolucao]' ";
    $campos_duplicatas = bancos::sql($sql);
    $linhas_duplicatas = count($campos_duplicatas);
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='iframe' align='center'>
        <td colspan='5'>
            Duplicata(s) atrelada(s) à esta NF de Devolução
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Duplicata (N.&ordm; NF Saída)
        </td>
        <td>
            Valor Duplicata <?=$simbolo_moeda;?>
        </td>
        <td>
            Valor Abatimento <br>de Devolução <?=$simbolo_moeda;?>
        </td>
        <td>
            Data de Vencimento
        </td>
    </tr>
<?
        $estornar = 'S';
/******************************************************************************************************/
        for($i = 0; $i < $linhas_duplicatas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <!--Para que a tela seja aberta como Pop-UP ...-->
            <a href="javascript:nova_janela('alterar.php?id_conta_receber=<?=$campos_duplicatas[$i]['id_conta_receber'];?>&pop_up=1', 'POP', '', '', '', '', 550, 950, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                <?=$campos_duplicatas[$i]['num_conta'];?>
            </a>
            <?
                if($campos_duplicatas[$i]['status'] == 2) {
                    echo '<font color="red"><b>(TOTALMENTE QUITADA)</b></font>';
                    /*O sistema irá impedir que outras duplicatas em aberto sejam desvinculadas da NF de Devolução que está 
                    sendo estornada por causa dessa(s) em específico que já foi(ram) quitada(s) 100% anteriormente ...*/
                    $estornar = 'N';
                }
            ?>
        </td>
        <td align='right'>
            <?=number_format($campos_duplicatas[$i]['valor'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos_duplicatas[$i]['valor_devolucao'], 2, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata($campos_duplicatas[$i]['data_vencimento_alterada'], '/');?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php?id_conta_receber=<?=$campos_duplicatas[0]['id_conta_receber'];?>&id_emp=<?=$id_emp2;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900;' onclick="redefinir('document.form', 'REDEFINIR')" class='botao'>
            <input type='button' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' onclick="return validar('<?=$estornar;?>')" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>