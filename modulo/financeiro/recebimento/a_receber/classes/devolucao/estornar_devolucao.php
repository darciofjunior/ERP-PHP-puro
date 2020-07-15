<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/faturamentos.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/variaveis/intermodular.php');
require('../../../../../classes/array_sistema/array_sistema.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>DEVOLUÇÃO DE CANCELAMENTO REALIZADA COM SUCESSO.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_numero_nf_vendida  = $_POST['txt_numero_nf_vendida'];
        $txt_cliente            = $_POST['txt_cliente'];
    }else {
        $txt_numero_nf_vendida  = $_GET['txt_numero_nf_vendida'];
        $txt_cliente            = $_GET['txt_cliente'];
    }
//Se o usuário consultar as NFs por número, então eu acrescento essa cláusula a mais no SQL ...
    if(!empty($txt_numero_nf_vendida)) {
        $txt_numero_nf_vendida = str_replace('A', '', $txt_numero_nf_vendida);
        $txt_numero_nf_vendida = str_replace('B', '', $txt_numero_nf_vendida);
        $txt_numero_nf_vendida = str_replace('C', '', $txt_numero_nf_vendida);
        $txt_numero_nf_vendida = str_replace('D', '', $txt_numero_nf_vendida);
        $inner_nfs_num_notas = "INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` AND nnn.`numero_nf` = '$txt_numero_nf_vendida' ";
    }
    //Exibo todas as Notas "Despachadas", que possuem "Duplicatas" importadas no Financeiro e que possuem valor de Devolução ...
    $sql = "SELECT c.`razaosocial`, crnd.`id_conta_receber_nf_devolucao`, nfs.`id_nf`, nfs. id_empresa`, 
            nfs.`data_emissao`, nfs.`vencimento1`, nfs.`vencimento2`, nfs.`vencimento3`, nfs.`vencimento4`, 
            t.`nome` AS transportadora 
            FROM `nfs` 
            INNER JOIN `contas_receberes` cr ON cr.id_nf = nfs.id_nf 
            INNER JOIN `contas_receberes_vs_nfs_devolucoes` crnd ON crnd.`id_conta_receber` = cr.`id_conta_receber` 
            $inner_nfs_num_notas 
            INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND c.`razaosocial` LIKE '%$txt_cliente%' 
            WHERE nfs.`status` = '4' 
            AND nfs.`id_empresa` = '$id_emp2' 
            AND nfs.`importado_financeiro` = 'S' ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'estornar_devolucao.php?id_emp2=<?=$id_emp2;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Estornar Devolução (Automática) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_conta_receber_nf_devolucao) {
/*Esse parâmetro de $tela_menu = 1, identifica que esse arquivo está sendo acessado do Menu Principal de 
Estornar Devolução (Automática)*/
    window.location = '../estornar_abatimento_devolucao.php?id_emp2=<?=$id_emp2;?>&id_conta_receber_nf_devolucao='+id_conta_receber_nf_devolucao+'&tela_menu=1'
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Estornar Devolução (Automática)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; da <br>NF Vendida
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Transportadora
        </td>
        <td>
            <font title='Prazo de Pagamento' style='cursor:help'>
                Prazo Pgto
            </font>
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_conta_receber_nf_devolucao'];?>')" width='10'>
            <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_conta_receber_nf_devolucao'];?>')" class='link'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
            </a>
        </td>
        <td>
        <?
            if($campos[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[$i]['data_emissao'], '/');
        ?>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['razaosocial'];
//Aqui verifica se a NF contém pelo menos 1 item
            $sql = "SELECT id_nfs_item 
                    FROM `nfs_itens` 
                    WHERE `id_nf` = '".$campos[$i]['id_nf']."' LIMIT 1 ";
            $campos_nfs_item = bancos::sql($sql);
            if(count($campos_nfs_item) == 0) echo ' <font color="red">(S/ ITENS)</font>';
        ?>
        </td>
        <td>
            <?=$campos[$i]['transportadora'];?>
        </td>
        <td>
        <?
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento= $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }
            echo $prazo_faturamento;
            //Aki eu limpo essa variável para não dar problema quando voltar no próximo loop ...
            $prazo_faturamento = '';
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'estornar_devolucao.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
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
}else {
?>
<html>
<head>
<title>.:: Estornar Devolução (Automática) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_numero_nf_vendida.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
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
            Estornar Devolução (Automática)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='40%'>
            N.&ordm; da Nota Vendida (Duplicata)
        </td>
        <td width='60%'>
            <input type='text' name='txt_numero_nf_vendida' title='Digite o N.º da Nota Vendida' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type='text' name='txt_cliente' title='Digite o Cliente' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'opcoes_devolucao.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_numero_nf_vendida.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Só exibe NF(s) do tipo Despachada
</pre>
<?}?>