<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>LISTA DE FUNCIONÁRIO(S) BLOQUEADO(S) ATUALIZADA COM SUCESSO.</font>";

if($passo == 1) {
?>
<html>
<head>
<title>.:: Bloquear Lista de Preço por (Fornecedores vs Funcionários) ::.</title>
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='2'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Bloquear Lista de Preço por 
            <font color='yellow'>
                (Fornecedores vs Funcionários)
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar fornecedores por: Razão Social' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Razão Social</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar fornecedores por: CNPJ ou CPF' onclick='document.form.txt_consultar.focus()' id='label2'>
            <label for='label2'>CNPJ / CPF</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='opt_internacional' value='1' title='Consultar fornecedores internacionais' class='checkbox' id='label3'>
            <label for='label3'>Internacionais</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os fornecedores' onclick='limpar()' class='checkbox' id='label4'>
            <label for='label4'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'bloquear.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick='document.form.opcao.checked = false;limpar()' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 2) {
    $condicao = ($opt_internacional == 1) ? "AND `id_pais` <> '31' " : "AND `id_pais` = '31' ";
    
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
                    $condicao ORDER BY `razaosocial` ";
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
            window.location = 'bloquear.php?passo=1&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Bloquear Lista de Preço por (Fornecedores vs Funcionários) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Bloquear Lista de Preço por 
            <font color='yellow'>
                (Fornecedores vs Funcionários)
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
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
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href="bloquear.php?passo=3&id_fornecedor=<?=$campos[$i]['id_fornecedor'];?>&opt_internacional=<?=$opt_internacional;?>" class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location  = 'bloquear.php?passo=1'" class='botao'>
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
}else if($passo == 3) {
    //Aqui só lista funcionários do Departamento de Compras ...
    $sql = "SELECT f.`id_funcionario`, f.`nome`, f.`cnpj_cpf`, c.`cargo`, d.`departamento`, e.`nomefantasia` 
            FROM `funcionarios` f 
            INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
            INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` AND d.`id_departamento` = '4' 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
            WHERE f.`status` = '1' ORDER BY f.`nome` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Não existe nenhum Funcionário desse Departamento cadastrado ...
?>
        <Script Language = 'Javascript'>
            window.location = '../../../../html/index.php?valor=20'
        </Script>
<?
    }else {//Existe pelo menos 1 funcionário de Compras cadastrado ...
?>
<html>
<head>
<title>.:: Bloquear Lista de Preço por (Fornecedores vs Funcionários) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src='cores.js'></Script>
</head>
<body>
<form name='form' action="<?=$PHP_SELF.'?passo=4';?>" method='post'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Bloquear Lista de Preço por 
            <font color='yellow'>
                (Fornecedores vs Funcionários)
            </font>
            <br/>
            Bloquear Funcionário(s) do Fornecedor 
            <?
                $sql = "SELECT `razaosocial` 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
                $campos_fornecedor = bancos::sql($sql);
            ?>
            <font color='yellow'>
                <?=$campos_fornecedor[0]['razaosocial'];?>
            </font>    
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Nome
        </td>
        <td>
            CPF
        </td>
        <td>
            Cargo
        </td>
        <td>
            Departamento
        </td>
        <td>
            Empresa
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        $cont = 0;
        for ($i = 0;  $i < $linhas; $i++) {
            //Aqui eu verifico se o funcionário tem permissão na lista de preço ...
            $sql = "SELECT `id_lista_preco_permissao` 
                    FROM `listas_precos_permissoes` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                    AND `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
            $campos_permissao = bancos::sql($sql);
            if(count($campos_permissao) == 0) {//Tem permissão para manipular a lista de preços ...
                $cont++;
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo','<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['departamento'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_funcionario[]' value="<?=$campos[$i]['id_funcionario'];?>" title='Tem permissão' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
//Não tem acesso, não é do departamento de Compras
            }else {//Não tem permissão para manipular a lista de preços
?>
    <tr class='linhasub' onclick="checkbox_2('form', 'chkt_tudo', '<?=$i;?>', '#C6E2FF', '#E8E8E8')" onmouseover="sobre_celula_2(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['departamento'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_funcionario[]' class='checkbox' value="<?=$campos[$i]['id_funcionario'];?>" onclick="checkbox_2('form', 'chkt_tudo', '<?=$i;?>', '#C6E2FF', '#E8E8E8')"  title="Está bloqueado" checked>
        </td>
    </tr>
<?
            }
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'bloquear.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR')" class='botao'>
            <input type='submit' name='cmd_bloquear' value='Bloquear' title='Bloquear' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_fornecedor' value='<?=$_GET['id_fornecedor'];?>'>
</form>
<?
    if($cont == 0) {
?>
    <Script Language = 'JavaScript'>
        document.form.chkt_tudo.checked = true
    </Script>
<?
    }
?>
</body>
</html>
<?
	}
}else if ($passo == 4) {
    /*Aqui eu verifico todos os Funcionários que estão bloqueados na Lista de Preço p/ o determinado fornecedor 
    passado por parâmetro ...*/
    $sql = "SELECT id_lista_preco_permissao 
            FROM `listas_precos_permissoes` 
            WHERE `id_fornecedor` = '$_POST[id_fornecedor]' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        //Aqui eu libero "Temporariamente" todos os funcionários que estavam bloqueados p/ o Tal Fornecedor ...
        $sql = "DELETE FROM `listas_precos_permissoes` WHERE `id_lista_preco_permissao` = '".$campos[$i]['id_lista_preco_permissao']."' LIMIT 1 ";
        bancos::sql($sql);
    }
    if(count($_POST['chkt_funcionario']) > 0) {
        foreach($_POST['chkt_funcionario'] as $id_funcionario_loop) {
            //Aqui eu bloqueio todos os funcionários que foram selecionados na Tela Anterior ...
            $sql = "INSERT INTO `listas_precos_permissoes` (`id_lista_preco_permissao`, `id_fornecedor`, `id_funcionario`, `data_sys`) VALUES (NULL, '$_POST[id_fornecedor]', '$id_funcionario_loop', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'bloquear.php?passo=1&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Bloquear Lista de Preço ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
    if(document.form.opt_opcao[0].checked == true) {
        window.location = 'bloquear.php?passo=1'
    }else if(document.form.opt_opcao[1].checked == true) {
        window.location = 'bloquear_funcionario.php'
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Bloquear Lista de Preço
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='1' title='Fornecedores vs Funcionários' id='label' checked>
            <label for='label'>Fornecedores vs Funcionários</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='2' title='Funcionário' id='label2'>
            <label for='label2'>Funcionário</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick='avancar()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>