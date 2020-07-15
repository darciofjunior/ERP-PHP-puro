<?
require('../../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/fornecedor/utilitarios/etiquetas/etiquetas.php', '../../../../../');

//Aqui eu apresento todos os Fornecedores que foram selecionados na Tela Anterior ...
foreach($_POST['cmb_fornecedores_selecionados'] as $id_fornecedor) $id_fornecedores.= $id_fornecedor.', ';
$id_fornecedores = substr($id_fornecedores, 0, strlen($id_fornecedores) - 2);

$sql = "SELECT `razaosocial` 
	FROM `fornecedores` 
	WHERE `id_fornecedor` IN ($id_fornecedores) 
	AND `ativo` = '1' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<title>.:: Imprimir Etiqueta(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../../css/layout.css'>
<Script Language = 'Javascript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var elementos = document.form.elements
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'select-one') {
            if(elementos[i].value == '') {
                alert('SELECIONE O DEPARTAMENTO !')
                elementos[i].focus()
                return false
            }
        }
    }
}

function perguntar() {
    if(!redefinir('document.form', 'REDEFINIR OS CAMPOS ')) {
        return false
    }
    document.form.elements[0].focus()
}
</script>
</head>
<body onLoad='document.form.elements[0].focus()'>
<form name='form' method='post' action='relatorio.php' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Imprimir Etiqueta(s)
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            Fornecedor:
        </td>
        <td>
            <?=$campos[$i]['razaosocial'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Depto.:</b>
        </td>
        <td>
            <select name="cmb_depto[]" title="Selecione o Departamento" class="combo">
            <?
                $sql = "Select departamento, departamento 
                        from departamentos 
                        where ativo = 1 order by departamento ";
                echo combos::combo($sql, 'COMPRAS');
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Contato:
        </td>
        <td>
            <input type='text' name='txt_contato[]' title='Digite o Contato' class='caixadetexto'>
        </td>
    </tr>
<?
            //Enquanto não chega no último Fornecedor, apresento uma linha de Separação entre cada Fornecedor ...
            if(($i + 1) != $linhas) {
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
<?
            }
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='return perguntar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_gerar' value='Gerar' title='Gerar' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='cmb_fornecedores_selecionados' value='<?=$id_fornecedores;?>'>
</form>
</body>
</html>