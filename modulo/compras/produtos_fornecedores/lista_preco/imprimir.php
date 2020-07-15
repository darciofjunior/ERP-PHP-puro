<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/lista_preco/lista_precos.php', '../../../../');

//Aqui eu busco qual é o País porque utilizares mais abaixo no JavaScript ...
$sql = "SELECT id_pais 
        FROM `fornecedores` 
        WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
$campos     = bancos::sql($sql);
$id_pais    = $campos[0]['id_pais'];
?>
<html>
<head>
<title>.:: Impressão(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    if(document.form.opt_opcao[0].checked == true) {//Impressão da Lista de Preço Nacional ...
        return imprimir(1)
    }else if(document.form.opt_opcao[1].checked == true) {//Impressão da Lista de Preço Export ...
        return imprimir(2)
    }else if(document.form.opt_opcao[2].checked == true) {//Gera Cotação somente em cima dos Produtos Filtrados do Fornecedor da Lista de Preço ...
        nova_janela('gerar_cotacao.php?id_fornecedor=<?=$_GET['id_fornecedor'];?>&id_produtos_insumos=<?=$_GET['id_produtos_insumos'];?>', 'CONSULTAR', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
        window.close()
    }else if(document.form.opt_opcao[3].checked == true) {//Gera Cotação p/ todos os Produtos do Fornecedor da Lista de Preço ...
        nova_janela('gerar_cotacao.php?id_fornecedor=<?=$_GET['id_fornecedor'];?>', 'CONSULTAR', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
        window.close()
    }else if(document.form.opt_opcao[4].checked == true) {
        nova_janela('../../../classes/cotacao/consultar.php?id_fornecedor=<?=$_GET[id_fornecedor];?>&id_produtos_insumos=<?=$_GET['id_produtos_insumos'];?>', 'CONSULTAR', '', '', '', '', 350, 780, 'c', 'c', '', '', 's', 's', '', '', '')
        window.close()
    }
}

function validar() {
    if(document.form.opt_opcao[0].checked == false && document.form.opt_opcao[1].checked == false) {
        alert('SELECIONE NACIONAL OU IMPORT / EXPORT');
        return false
    }
    return true
}

function imprimir(id_export) {
    var id_pais     = '<?=$id_pais;?>'
    var toda_lista  = (document.form.chkt_todos.checked == true) ? true : false
    if(id_pais == 31) {//Brasil ...
        if(id_export == 2) {//Exportação ...
            nova_janela('pdf/relatorio.php?id_fornecedor=<?=$_GET[id_fornecedor];?>&id_produtos_insumos=<?=$_GET['id_produtos_insumos'];?>&valor=1&toda_lista='+toda_lista, 'CONSULTAR', 'F')
        }else {
            nova_janela('pdf/relatorio.php?id_fornecedor=<?=$_GET[id_fornecedor];?>&id_produtos_insumos=<?=$_GET['id_produtos_insumos'];?>&valor=0&toda_lista='+toda_lista, 'CONSULTAR', 'F')
        }
    }else {//Exportação ...
        nova_janela('pdf/relatorio.php?id_fornecedor=<?=$_GET[id_fornecedor];?>&id_produtos_insumos=<?=$_GET['id_produtos_insumos'];?>&valor=2&toda_lista='+toda_lista, 'CONSULTAR', 'F')
    }
    window.close()
}
</Script>
</head>
<body>
<form name='form'>
<table border='0' width='90%' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Impressão(ões)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Lista de Preço
        </td>
    </tr>
<?
//Controle para imprimir Nac e Export ...
    if(!empty($_GET[excel])) {
        if($_GET[excel] == 1) {//Lista de Preço Nacional ...
            $checked    = 'checked';
            $checked2   = '';
        }else {//Lista de Preço Import / Export ...
            $checked    = '';
            $checked2   = 'checked';
        }
    }
?>
    <tr class='linhanormal'>
        <td width="20%">
            <input type='radio' name='opt_opcao' value='1' title='Nacional' id='label' onclick="window.location = 'imprimir.php?id_fornecedor=<?=$_GET['id_fornecedor'];?>&id_produtos_insumos=<?=$_GET['id_produtos_insumos'];?>&excel=1'" <?=$checked;?>>
            <label for='label'>Nacional</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='2' title='Import / Export' id='label2' OnClick="window.location = 'imprimir.php?id_fornecedor=<?=$_GET['id_fornecedor'];?>&id_produtos_insumos=<?=$_GET['id_produtos_insumos'];?>&excel=2'" <?=$checked2;?>>
            <label for='label2'>Import / Export</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='chkt_todos' value='true' title='Imprimir toda a Lista' id='todos' class='checkbox'>
            <label for='todos'>Imprimir toda a Lista</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = "javascript:nova_janela('gerar_toda_lista_em_excel.php?id_fornecedor=<?=$_GET['id_fornecedor'];?>&excel=<?=$_GET[excel];?>', 'CONSULTAR', '', '', '', '', 350, 780, 'c', 'c', '', '', 's', 's', '', '', '')" onclick='return validar()' class='link'>
                <font color='red'>==> Gerar Toda Lista em Excel</font>
            </a>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Cotações
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Gerar Cotação p/ Itens selecionados da Lista de Preço do Fornecedor' id='label3'>
            <label for='label3'>Gerar Cotação p/ Itens selecionados da Lista de Preço do Fornecedor</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='4' title='Gerar Cotação p/ Toda Lista de Preço do Fornecedor' id='label4'>
            <label for='label4'>Gerar Cotação p/ Toda Lista de Preço do Fornecedor</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='5' title='Consultar Cotação' id='label5'>
            <label for='label5'>Consultar Cotação</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>