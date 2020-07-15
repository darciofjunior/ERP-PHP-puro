<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/bloquear/bloquear.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>FUNCIONÁRIO ".strtoupper($nome)." BLOQUEADO(A) COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT f.`id_funcionario`, f.`nome`, f.`codigo_barra`, e.`nomefantasia`, c.`cargo`, d.`departamento` 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                    INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                    WHERE f.`nome` LIKE '$txt_consultar%' 
                    AND f.`status` < '3' ORDER BY f.`nome` ";
        break;
        default:
            $sql = "SELECT f.`id_funcionario`, f.`nome`, f.`codigo_barra`, e.`nomefantasia`, c.`cargo`, d.`departamento` 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                    INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                    WHERE f.`status` < '3' ORDER BY f.`nome` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'bloquear_funcionario.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Bloquear Lista de Preço por (Funcionário) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Bloquear Lista de Preço por 
            <font color='yellow'>
                (Funcionário)
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Cód.
        </td>
        <td>
            Nome
        </td>
        <td>
            Cargo
        </td>
        <td>
            Depto.
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'bloquear_funcionario.php?passo=2&id_funcionario_loop='.$campos[$i]['id_funcionario'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['codigo_barra'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
            <?=$campos[$i]['departamento'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'bloquear_funcionario.php'" class='botao'>
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
}else if($passo == 2) {
    //Aqui eu trago todos os Fornecedores cadastrados no Sistema ... 
    $sql = "SELECT `id_fornecedor`, `razaosocial`, `endereco`, `cnpj_cpf` 
            FROM `fornecedores` 
            WHERE `ativo` = '1' 
            AND `razaosocial` <> '' 
            ORDER BY razaosocial ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = '../../../../html/index.php?valor=20'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Bloquear Lista de Preço por (Funcionário) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'cores.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Funcionário
    if(!combo('form', 'cmb_funcionario', '', 'SELECIONE UM FUNCIONÁRIO !')) {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' action='<?=$PHP_SELF.'?passo=3';?>' method='post' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Fornecedor(es) Bloqueado(s) para o Funcionário 
            <?
                $sql = "SELECT `nome` 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = '$_GET[id_funcionario_loop]' LIMIT 1 ";
                $campos_fornecedor = bancos::sql($sql);
            ?>
            <font color='yellow'>
                <?=$campos_fornecedor[0]['nome'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Razão Social
        </td>
        <td>
            CNPJ / CPF
        </td>
        <td>
            Endereço
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Todos' class='checkbox'>
        </td>
    </tr>
<?
        $bloqueados = 0;
        for($i = 0;  $i < $linhas; $i++) {
            //Aqui eu verifico se o Fornecedor do Loop está Bloqueado p/ o Funcionário que foi passado por parâmetro ...
            $sql = "SELECT `id_fornecedor` 
                    FROM `listas_precos_permissoes` 
                    WHERE `id_fornecedor` ='".$campos[$i]['id_fornecedor']."' 
                    AND `id_funcionario` = '$_GET[id_funcionario_loop]' LIMIT 1 ";
            $campos_lista_preco = bancos::sql($sql);
            if(count($campos_lista_preco) == 0) {//Fornecedor não está bloqueado ...
                $checked    = '';
                $class      = 'linhanormal';
            }else {//Fornecedor está bloqueado ...
                $checked    = 'checked';
                $class      = 'linhasub';
                $bloqueados++;
            }
?>
    <tr class='<?=$class;?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
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
        <?
            if($campos[$i]['endereco'] != '') echo $campos[$i]['endereco'];
        ?>
        </td>
        <td>
            <input type='checkbox' name='chkt_fornecedor[]' value='<?=$campos[$i]['id_fornecedor'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" title="Bloquear / Desbloquear: <?=$campos[$i]['razaosocial'];?>" class='checkbox' <?=$checked;?>>
        </td>
    </tr>
<?
        }
?>
</table>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário(s): 
        </td>
        <td>
            <select name='cmb_funcionario' title='Selecione um funcionário' class='combo'>
            <?
                $sql = "SELECT `id_funcionario`, `nome` 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` <> '$_GET[id_funcionario_loop]' 
                        AND `ativo` = '1' 
                        AND `status` < '3' ORDER BY `nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'bloquear_funcionario.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_bloquear' value='Bloquear' title='Bloquear' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_funcionario_loop' value='<?=$_GET[id_funcionario_loop];?>'>
</form>
<?
        //Se todos os fornecedores estiverem bloqueados então o sistema seleciona o Checkbox Principal ...
        if($bloqueados == $linhas) {
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
}else if ($passo == 3) {
    //Busca o nome do Funcionário que foi selecionado na Combo na Tela anterior ...
    $sql = "SELECT `nome` 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_POST[cmb_funcionario]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $nome   = $campos[0]['nome'];//Será passado por parâmetro para ser exibida na MSN ...
    /*Aqui eu verifico todos os Fornecedores que estão bloqueados na Lista de Preço p/ o determinado funcionário 
    passado por parâmetro ...*/
    $sql = "SELECT `id_lista_preco_permissao` 
            FROM `listas_precos_permissoes` 
            WHERE `id_funcionario` = '$_POST[cmb_funcionario]' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        //Aqui eu libero "Temporariamente" todos os fornecedores que estavam bloqueados p/ o Tal Funcionário ...
        $sql = "DELETE FROM `listas_precos_permissoes` WHERE `id_lista_preco_permissao` = '".$campos[$i]['id_lista_preco_permissao']."' LIMIT 1 ";
        bancos::sql($sql);
    }
    if(count($_POST['chkt_fornecedor']) > 0) {
        foreach($_POST['chkt_fornecedor'] as $id_fornecedor) {
            //Aqui eu bloqueio todos os fornecedores que foram selecionados na Tela Anterior ...
            $sql = "INSERT INTO `listas_precos_permissoes` (`id_lista_preco_permissao`, `id_fornecedor`, `id_funcionario`, `data_sys`) VALUES (NULL, '$id_fornecedor', '$_POST[cmb_funcionario]', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'bloquear_funcionario.php?id_funcionario_loop=<?=$_POST['cmb_funcionario'];?>&passo=2&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Bloquear Lista de Preço por (Funcionário) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
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
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Bloquear Lista de Preço por 
            <font color='yellow'>
                (Funcionário)
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' title='Consultar Funcionário' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Funcionário por: Nome' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Nome</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os funcionários' onclick='limpar()' id='label2' class='checkbox'>
            <label for='label2'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'bloquear.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>