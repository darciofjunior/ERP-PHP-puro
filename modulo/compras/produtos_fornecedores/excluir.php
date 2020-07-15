<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PRODUTO(S) DO FORNECEDOR EXCLUIDO(S) COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ESSE PRODUTO NÃO PODE SER EXCLUÍDO, DEVIDO ESTE SER O FORNECEDOR DEFAULT P/ ESTE PRODUTO INSUMO.</font>";

if($passo == 1) {
    $condicao = ($opt_internacional == 1) ? " AND `id_pais` <> '31' " : " AND `id_pais` = '31' ";
    
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
        case 2:
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('.', '', $txt_consultar);
            $txt_consultar = str_replace('/', '', $txt_consultar);
            $txt_consultar = str_replace('-', '', $txt_consultar);
            
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `cnpj_cpf` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' 
                    $condicao_pais ORDER BY `razaosocial` ";
        break;
        default:
            $sql = "SELECT `id_fornecedor`, `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `ativo` = '1'                  
                    AND `razaosocial` <> '' 
                    $condicao ORDER BY `razaosocial` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'excluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Produto(s) de Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='60%' border="0" align='center' cellspacing="1" cellpadding="1" onmouseover="total_linhas(this)">
    <tr align='center'>
        <td>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td>
            Excluir Produto(s) de Fornecedor(es)
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td>
            <font color='yellow'>
            <?
                if($opt_internacional == 1) {
                    echo 'Internacional(s)';
                }else {
                    echo 'Nacional(s)';
                }
            ?>
            </font>
        </td>
    </tr>
<?
//Disparo do Loop de Fornecedores ...
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <a href='excluir.php?passo=2&id_fornecedor=<?=$campos[$i]['id_fornecedor'];?>&razaosocial=<?=$campos[$i]['razaosocial'];?>&opt_internacional=<?=$opt_internacional;?>&txt_consultar=<?=$txt_consultar;?>&opt_opcao=<?=$opt_opcao;?>' class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
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
}elseif($passo == 2) {
?>
<html>
<head>
<title>.:: Excluir Produtos do Fornecedor <?=$_GET['razaosocial'];?> ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name="form" method="POST" action="<?=$PHP_SELF.'?passo=3';?>" onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='80%' border="0" align='center' cellspacing="1" cellpadding="1" onmouseover="total_linhas(this)">
<?
    $sql = "SELECT pi.id_produto_insumo, pi.discriminacao, fpi.id_fornecedor_prod_insumo, g.referencia 
            FROM produtos_insumos pi 
            INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.id_produto_insumo = pi.id_produto_insumo AND fpi.`ativo` = '1' 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = fpi.id_fornecedor and f.id_fornecedor = '$id_fornecedor' 
            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
            WHERE pi.`ativo` = '1' ORDER BY pi.discriminacao ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'excluir.php?valor=1'
        </Script>
<?
    }else {
?>
    <tr align='center'>
        <td colspan='3'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Excluir Produto(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Produtos do Fornecedor 
            <font color='yellow'>
                <?=$razaosocial;?>
            </font>
        </td>
        <td>
            <input type="checkbox" name="chkt" title="Selecionar Todos" onclick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
//Disparo do Loop de Produtos Insumos do Fornecedor Corrente ...
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" class="linhanormal">
        <td>
            <b>Referencia:</b>&nbsp;<?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <b>Discriminação:</b>&nbsp;<?=$campos[$i]['discriminacao'];?>
        </td>
        <td align="center">
            <input type="checkbox" name="chkt_fornecedor_prod_insumo[]" value="<?=$campos[$i]['id_fornecedor_prod_insumo'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'excluir.php?passo=1&id_fornecedor=<?=$id_fornecedor;?>&razaosocial=<?=$razaosocial;?>&opt_internacional=<?=$opt_internacional;?>&txt_consultar=<?=$txt_consultar;?>&opt_opcao=<?=$opt_opcao;?>'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?
    }
}else if($passo == 3) {
    foreach($_POST['chkt_fornecedor_prod_insumo'] as $id_fornecedor_prod_insumo) {
//Aqui eu busco o PI e o Fornecedor p/ fazer uma verificação antes de Excluir ...
        $sql = "SELECT id_fornecedor, id_produto_insumo 
                FROM `fornecedores_x_prod_insumos` 
                WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $id_fornecedor          = $campos[0]['id_fornecedor'];
        $id_produto_insumo_loop = $campos[0]['id_produto_insumo'];
/*Aqui eu verifico quem é o Fornecedor da Última Compra deste Produto Insumo ...
Vou utilizar esse id + abaixo p/ fazer algumas comparações ...*/
        $sql = "SELECT id_fornecedor_default 
                FROM `produtos_insumos` 
                WHERE `id_produto_insumo` = '$id_produto_insumo_loop' 
                AND `id_fornecedor_default` > '0' 
                AND `ativo` = '1' ";
        $campos_default         = bancos::sql($sql);
        $id_fornecedor_default  = $campos_default[0]['id_fornecedor_default'];
/*Significa que este Fornecedor do Loop é o Fornecedor Default, e sendo assim eu não posso estar excluindo
este fornecedor*/
        if($id_fornecedor_default == $id_fornecedor) {//Não pode excluir ...
//Aqui eu verifico se esse P.I. é do Tipo P.A e se neste a OC = 'Industrial' ...
            $sql = "SELECT operacao_custo 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo_loop' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
            if(count($campos_pipa) == 1) {//Significa que esse PI é um PA ...
                if($campos_pipa[0]['operacao_custo'] == 0) {//Ind, pode desat. normalm
                    intermodular::excluir_varios_pi_fornecedor($id_fornecedor_prod_insumo);
                    $valor = 2;
                }else {//Revenda não pode desatrelar ...
                    $valor = 3;
                }
            }else {//É simplesmente um PI, então não posso desatrelar esse Fornec ...
                $valor = 3;
            }
        }else {//Posso excluir normalmente ...
            intermodular::excluir_varios_pi_fornecedor($id_fornecedor_prod_insumo);
            $valor = 2;
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'excluir.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Produto(s) do Fornecedor ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled = false
        document.form.txt_consultar.value = ''
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
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir Produto(s) do Fornecedor
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar fornecedor por: Razão Social" onclick="document.form.txt_consultar.focus()" id='label' checked>
            <label for="label">Razão Social</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" title="Consultar fornecedor por: CNPJ ou CPF" onclick="document.form.txt_consultar.focus()" id='label2'>
            <label for="label2">CNPJ / CPF</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type='checkbox' name='opt_internacional' value='1' title="Consultar fornecedores internacionais" id='label3' class="checkbox">
            <label for="label3">Internacionais</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os fornecedores" onclick='limpar()' id='label4' class="checkbox">
            <label for="label4">Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>