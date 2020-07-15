<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/financeiros.php');
require('../../../../lib/genericas.php');
require('../../../classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>NOTA FISCAL CANCELADA COM SUCESSO.</font>";

if($passo == 1) {
    //Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_cliente                        = $_POST['txt_cliente'];
        $txt_numero_nota_fiscal             = $_POST['txt_numero_nota_fiscal'];
        $cmb_status                         = $_POST['cmb_status'];
        $chkt_ultimos_60_dias               = $_POST['chkt_ultimos_60_dias'];
    }else {
        $txt_cliente                        = $_GET['txt_cliente'];
        $txt_numero_nota_fiscal             = $_GET['txt_numero_nota_fiscal'];
        $cmb_status                         = $_GET['cmb_status'];
        $chkt_ultimos_60_dias               = $_GET['chkt_ultimos_60_dias'];
    }
    
//Se o usuário consultar as NFs por número, então eu acrescento essa cláusula a mais no SQL ...
    if(!empty($txt_numero_nota_fiscal)) $inner_nfs_num_notas = "INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfso.`id_nf_num_nota` AND nnn.`numero_nf` LIKE '%$txt_numero_nota_fiscal%' ";
    
    if(empty($cmb_status)) $cmb_status = '%';
    
    //Apenas dos Últimos 60 dias ...
    if(!empty($chkt_ultimos_60_dias)) {
        $data_ultimos_60_dias           = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -60), '-');
        $condicao_ultimos_60_dias_nfs   = " AND (nfso.`data_emissao` >= '".$data_ultimos_60_dias."' OR (nfso.`data_emissao` = '0000-00-00')) ";
    }

    $sql = "SELECT nfso.`id_nf_outra`, nfso.`id_empresa`, nfso.`id_nf_num_nota`, nfso.`data_emissao`, nfso.`vencimento1`, 
            nfso.`vencimento2`, nfso.`vencimento3`, nfso.`vencimento4`, nfso.`status`, c.`razaosocial` 
            FROM `nfs_outras` nfso 
            $inner_nfs_num_notas 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') AND c.`ativo` = '1' 
            WHERE nfso.`status` LIKE '$cmb_status' 
            $condicao_ultimos_60_dias_nfs 
            ORDER BY nfso.`id_nf_outra` DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'alterar_imprimir.php?valor=1'
        </Script>
<?
    }else {
/***************************Script p/ Excluir as Notas Fiscais***************************/
//Aqui eu excluo as Notas Fiscais q não tiverem itens e q Empresa desta for Albafer ou Tool Master ...
        if(!empty($_GET['id_nf_outra']) && empty($sair)) {
            $sql = "DELETE FROM `nfs_outras` WHERE `id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
            bancos::sql($sql);
?>
            <Script Language = 'JavaScript'>
                alert('NOTA FISCAL EXCLUIDA COM SUCESSO !')
                window.location = 'alterar_imprimir.php<?=$parametro?>&sair=1'
            </Script>
<?
        }
/****************************************************************************************/
?>
<html>
<head>
<title>.:: Consultar Nota(s) Fiscal(is) Outra(s) p/ Alterar Imprimir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_nf_outra) {
    window.location = 'index.php?id_nf_outra='+id_nf_outra
}

function excluir_nota_fiscal(id_nf_outra) {
    var resposta = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA EXCLUIR ESSA NOTA FISCAL ?')
    if(resposta == true) {
//Essa variável é uma jogadinha que eu faço p/ não ficar dando reload umas 500 vezes na Tela ...
        document.location = 'alterar_imprimir.php<?=$parametro;?>&id_nf_outra='+id_nf_outra+'&sair=0'
    }else {
        return false
    }
}
</Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Nota(s) Fiscal(is) Outra(s) p/ Alterar Imprimir
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Nota Fiscal
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Status da NF
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Emp / Tp Nota <br>/ Prazo Pgto
            </font>
        </td>
        <td>
            <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir Nota Fiscal' alt='Excluir Nota Fiscal'>
        </td>
    </tr>
<?
//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
        $vetor = array_sistema::nota_fiscal();
        for($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_nf_outra'];?>')" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_nf_outra'];?>')" class='link'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf_outra'], 'O');?>
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
            $sql = "SELECT id_nf_outra_item 
                    FROM `nfs_outras_itens` 
                    WHERE `id_nf_outra` = '".$campos[$i]['id_nf_outra']."' LIMIT 1 ";
            $campos_itens = bancos::sql($sql);
            $qtde_itens_nf = count($campos_itens);
            if($qtde_itens_nf == 0) echo ' <font color="red">(S/ ITENS)</font>';
        ?>
        </td>
        <td>
            <?=$vetor[$campos[$i]['status']];?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT nomefantasia 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $nomefantasia   = $campos_empresa[0]['nomefantasia'];
            
            $vetor_nota_sgd = genericas::nota_sgd($campos[$i]['id_empresa']);
            $apresentar     = $nomefantasia.$vetor_nota_sgd['tipo_nota'];
            

            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }
            echo $apresentar.' / '.$prazo_faturamento;

//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
        <td>
        <?
/*Só irá exibir esse link quando a Nota Fiscal não tiver nenhum Item e a Empresa desta 
for Albafer ou Tool Master ...*/
            if($qtde_itens_nf == 0 && ($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2)) {
        ?>
                <img src = "../../../../imagem/menu/excluir.png" border='0' title="Excluir Nota Fiscal" alt="Excluir Nota Fiscal" onClick="excluir_nota_fiscal('<?=$campos[$i]['id_nf_outra'];?>')">
        <?
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar_imprimir.php'" class='botao'>
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
<title>.:: Consultar Nota(s) Fiscal(is) Outra(s) p/ Alterar Imprimir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body onload='document.form.txt_cliente.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Nota(s) Fiscal(is) Outra(s) p/ Alterar Imprimir
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
    <tr class='linhanormal'>
        <td>
            N.º da Nota Fiscal
        </td>
        <td>
            <input type='text' name='txt_numero_nota_fiscal' title='Digite o N.º da Nota Fiscal' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Status
        </td>
        <td>
            <select name='cmb_status' title='Selecione o Status Nota Fiscal' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0'>EM ABERTO</option>
                <option value='1'>LIBERADA P/ FATURAR</option>
                <option value='2'>FATURADA</option>
                <option value='3'>EMPACOTADA</option>
                <option value='4'>DESPACHADA</option>
                <option value='5'>CANCELADA</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ultimos_60_dias' value='1' title='Últimos 60 dias' id='chkt_ultimos_60_dias' class='checkbox' checked>
            <label for='chkt_ultimos_60_dias'>
                <font color='red'>
                    <b>Últimos 60 dias</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_cliente.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>