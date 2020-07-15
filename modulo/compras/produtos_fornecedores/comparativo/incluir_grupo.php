<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/comparativo/index.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT g.nome, g.referencia, pi.id_produto_insumo, pi.discriminacao 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo AND g.referencia LIKE '%$txt_consultar%' 
                    WHERE pi.`ativo` = '1' ORDER BY g.referencia ";
        break;
        case 2:
            $sql = "SELECT g.nome, g.referencia, pi.id_produto_insumo, pi.discriminacao 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                    WHERE pi.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' ORDER BY pi.discriminacao ";
        break;
        default:
            $sql = "SELECT g.nome, g.referencia, pi.id_produto_insumo, pi.discriminacao 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                    WHERE pi.`ativo` = '1' ORDER BY g.referencia ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir_grupo.php?id_prods_insumos=<?=$id_prods_insumos;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Grupo(s) p/ Incluir no Comparativo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function voltar(id_produto_insumo) {
    var numero = '', id_prods_insumos = '', flag = 0
    var vetor = '<?=$id_prods_insumos;?>' + ','
    for(i = 0; i < vetor.length; i++) {
        if(vetor.charAt(i) == ',') {
            numero = eval(numero)
            if(numero != id_produto_insumo) {
                id_prods_insumos = vetor + id_produto_insumo
            }else {
                flag = 1
            }
            numero = ''
        }else {
            numero = numero + vetor.charAt(i)
        }
    }

    if(flag == 1) {
        alert('ESTE GRUPO JÁ FOI INCLUIDO !')
        return false
    }else {
    <?
        if($id_prods_insumos == '') {
    ?>
            id_prods_insumos = id_produto_insumo
    <?
        } else {
    ?>
            id_prods_insumos = '<?=$id_prods_insumos;?>,' + id_produto_insumo
    <?
        }
    ?>
//Atualizando os Frames ...
        window.opener.parent.itens.document.location = 'itens.php?id_prods_insumos='+id_prods_insumos
        window.opener.parent.rodape.document.location = 'rodape.php?id_prods_insumos='+id_prods_insumos
        window.close()
    }
}

function atrelar_grupo() {
    var valor = false, elementos = document.form.elements
    var numero = '', id_prods_insumos = '', lista = '', flag = 0
    vetor = '<?=$id_prods_insumos;?>'

    for (var x = 0; x < elementos.length; x++) {
        if (elementos[x].type == 'checkbox' && elementos[x].name != 'chkt_tudo') {
            if (elementos[x].checked == true) valor = true
        }
    }

    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        for (var x = 0; x < elementos.length; x ++) {
            if (elementos[x].type == 'checkbox' && elementos[x].name != 'chkt_tudo') {
                if (elementos[x].checked == true) {
                    numero_selecionado = elementos[x].value
                    if(vetor.length > 0) {
                        for(i = 0; i < vetor.length; i++) {
                            if(vetor.charAt(i) == ',') {
                                numero = eval(numero)
                                numero_selecionado = eval(numero_selecionado)
                                if(numero != numero_selecionado && flag != 1) {
                                    flag = 2
                                }else {
                                    flag = 1
                                    id_prods_insumos = vetor
                                }
                                numero = ''
                            }else {
                                numero = numero + vetor.charAt(i)
                            }
                        }
                        if(flag == 0 || flag == 2) {
                            lista = lista + numero_selecionado + ','
                            var existe_lista = 0
                        }
                        flag = 0
                    }else {
                        id_prods_insumos = id_prods_insumos + numero_selecionado
                        id_prods_insumos = id_prods_insumos + ','
                    }
                }
            }
        }
        if(existe_lista == 0) id_prods_insumos = vetor + lista
//Atualizando os Frames ...
        window.opener.parent.itens.document.location = 'itens.php?id_prods_insumos='+id_prods_insumos
        window.opener.parent.rodape.document.location = 'rodape.php?id_prods_insumos='+id_prods_insumos
        window.close()
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Grupo(s) p/ Incluir no Comparativo
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td colspan='2'>
            Grupo
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
	$pular = 0;
        for ($i = 0;  $i < $linhas; $i++) {
            //Verifico se esse PI tem Preço e está vinculado a algum fornecedor na Lista de Preço ...           
            $sql = "SELECT f.razaosocial 
                    FROM `fornecedores_x_prod_insumos` fpi 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = fpi.id_fornecedor 
                    WHERE fpi.`id_produto_insumo` = '519' LIMIT 1 ";
            $campos_lista = bancos::sql($sql);
            if(count($campos_lista) > 0) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$pular;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_grupo[]' value='<?=$campos[$i]['id_produto_insumo'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$pular;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
                $pular++;
            }
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir_grupo.php?id_prods_insumos=<?=$id_prods_insumos;?>'" class='botao'>
            <input type='button' name='cmd_atrelar' value='Atrelar' title='Atrelar' onclick='atrelar_grupo()' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Consultar Grupo(s) p/ Incluir no Comparativo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
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
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<!--//Armazena todos os Produtos Insumos q foram atrelados p/ a comparação de Preço ...-->
<input type='hidden' name='id_prods_insumos' value='<?=$_GET['id_prods_insumos'];?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Grupo(s) p/ Incluir no Comparativo
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1'  onclick="document.form.txt_consultar.focus()" title="Consultar Produtos Insumos por: Referência" id='label'>
            <label for='label'>
                Referência
            </label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' onClick="document.form.txt_consultar.focus()" title="Consultar Produtos Insumos por: Referência" id='label2' checked>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title="Consultar todos os Produtos Insumos" class='checkbox' id='label3'>
            <label for='label3'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style='color:#ff9900' onclick="document.form.opcao.checked = false;limpar()" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>