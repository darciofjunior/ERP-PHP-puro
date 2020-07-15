<?
require('../../../../lib/segurancas.php');
if(empty($_GET['id_nfe_historico'])) require('../../../../lib/menu/menu.php');//Se essa tela for aberta dentro de um Iframe não exibo Menu ...
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

//Verifica se tem uma nota em aberto daquele fornecedor selecionado
function notas_abertas($id_fornecedor) {
    $sql = "SELECT id_nfe 
            FROM `nfe` 
            WHERE `id_fornecedor` = '$id_fornecedor' 
            AND `situacao` < '2' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {
        return 1;
    }else {
        return 0;
    }
}

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' ORDER BY `razaosocial` ";
        break;
        default:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `ativo` = '1' 
                    AND `razaosocial` <> '' ORDER BY `razaosocial` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Fornecedor(es) para Incluir Nota Fiscal de Entrada :::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function avancar(valor, id_fornecedor) {
    if(valor == 1) {
        var pergunta = confirm('EXISTEM NOTAS EM ABERTO DESSE FORNECEDOR !\nDESEJA INCLUIR UMA NOTA NOVA ?')
        if(pergunta == true) window.location = 'incluir.php?passo=2&id_fornecedor='+id_fornecedor
    }else {
        window.location = 'incluir.php?passo=2&id_fornecedor='+id_fornecedor
    }
}
</Script>
</head>
<body onload="document.getElementById('lnk_fornecedor0').focus()">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Fornecedor(es) para Incluir Nota Fiscal de Entrada
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
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
            $notas_abertas  = notas_abertas($campos[$i]['id_fornecedor']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="avancar('<?=$notas_abertas?>', '<?=$campos[$i]['id_fornecedor']?>')">
            <a href='#' id='lnk_fornecedor<?=$i;?>' class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td align='center'>
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
        <td>
            <?=$campos[$i]['endereco'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
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
}else if($passo == 2) {//Incluir Nota Fiscal de Entrada ...
    /*Busco aqui o "id_pais" do "id_fornecedor" que foi passado por parâmetro para saber qual id_tipo_moeda 
    incluir na Nota Fiscal de Entrada abaixo ...*/
    $sql = "SELECT `id_pais` 
            FROM `fornecedores` 
            WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
    $campos_fornecedor  = bancos::sql($sql);
    $id_tipo_moeda      = ($campos_fornecedor[0]['id_pais'] == 31) ? 1 : 2;
    /*Esses parâmetros só existirão quando essa tela de "Incluir Nota Fiscal" for chamada dentro da opção 
    "Incluir Ajuste" através do botão "Outras Opções" que fica em Nota Fiscal de Entrada ...*/
    $num_nota           = (!empty($_GET['num_nota'])) ? $_GET['num_nota'] : '';

    /*Incluindo uma Nova Nota Fiscal de Entrada p/ o "id_fornecedor" passado por parâmetro ...

    Obs: o id_tipo_pagamento = '1' representa Dinheiro e não posso passar esse campo como sendo Nulo porque 
    senão não consigo dar INSERT na tabela ...*/
    $sql = "INSERT INTO `nfe` (`id_nfe`, `id_empresa`, `id_fornecedor`, `id_tipo_pagamento_recebimento`, `id_tipo_moeda`, `num_nota`, `tipo`) VALUES (NULL, '4', '$_GET[id_fornecedor]', '1', '$id_tipo_moeda', '$num_nota', '2') ";
    bancos::sql($sql);
    $id_nfe = bancos::id_registro();

/*Essa situação só ocorrerá quando essa tela de "Incluir Nota Fiscal" for chamada dentro da opção 
"Incluir Ajuste" através do botão "Outras Opções" que fica em Nota Fiscal de Entrada, ao invés de ser aberto 
como uma Tela normal ...*/
    if(!empty($_GET['id_nfe_historico'])) {
        /*Atrelo a Nova NF de Débito que acabou de ser Inclusa ao $_GET['id_nfe_historico'] de "Ajuste" 
        que acabou de ser passado por parâmetro ...*/
        $sql = "UPDATE `nfe_historicos` SET `id_nfe_debitar` = '$id_nfe' WHERE `id_nfe_historico` = '$_GET[id_nfe_historico]' LIMIT 1 ";
        bancos::sql($sql);
?>
        <Script Language = 'JavaScript'>
            //Já abro o cabeçalho dessa "Nova NF de Entrada à Debitar" que acabou de ser inclusa ...
            window.location = 'alterar_cabecalho.php?id_nfe=<?=$id_nfe;?>'
        </Script>
<?
//Significa que está tela foi aberta normalmente ...
    }else {
        /*Esse parâmetro -> $clique_automatico_incluir_itens
        Dispara um clique automático no botão de Incluir itens de NF, assim que acaba de ser gerado a Nova NF ...*/
?>
    <Script Language = 'JavaScript'>
        alert('A EMPRESA GERADA PARA ESTA NF FOI COMO SENDO "GRUPO" - CERTIFIQUE-SE DE QUE ESTÁ ESTEJA CORRETA !')
        window.location = 'itens/index.php?id_nfe=<?=$id_nfe;?>&clique_automatico_incluir_itens=S'
    </Script>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Consultar Fornecedor(es) para Incluir Nota Fiscal de Entrada :::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled 	= true
        document.form.txt_consultar.className 	= 'textdisabled'
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled 	= false
        document.form.txt_consultar.className 	= 'caixadetexto'
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onSubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Fornecedor(es) para Incluir Nota Fiscal de Entrada
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type="radio" name="opt_opcao" value='1' onclick="document.form.txt_consultar.focus()" title="Consultar fornecedor por: Razão Social" id='label' checked>
            <label for='label'>Razão Social</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os fornecedores" onclick='limpar()' class='checkbox' id='label2'>
            <label for='label2'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>