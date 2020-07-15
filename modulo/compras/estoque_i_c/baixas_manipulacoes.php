<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/estoque_new.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT g.`nome`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`id_grupo` <> '9' AND g.`referencia` LIKE '%$txt_consultar%' 
                    ORDER BY pi.`discriminacao` ";
        break;
        case 2:
            $sql = "SELECT g.`nome`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`id_grupo` <> '9' 
                    WHERE pi.`discriminacao` LIKE '%$txt_consultar%' 
                    ORDER BY pi.`discriminacao` ";
        break;
        case 3:
            $verificar_apenas_epi = 1; 
            require('detalhes_baixas_manipulacoes.php');
            exit;
        break;
        default:
            $sql = "SELECT g.`nome`, g.`referencia`, pi.`id_produto_insumo`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`id_grupo` <> '9' 
                    ORDER BY pi.`discriminacao` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'baixas_manipulacoes.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Baixa(s) / Manipulação(ões) ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Consultar Baixa(s) / Manipulação(ões)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Grupo
        </td>
        <td>
            Unidade
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
/*Esse parâmetro nao_exibir_voltar = 1 é para não exibir o botão Voltar do Pop-Up que se abrirá 
quando clicar nesse link ...*/
            $url = "nova_janela('detalhes_baixas_manipulacoes.php?id_produto_insumo=".$campos[$i]['id_produto_insumo']."&nao_exibir_voltar=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '') ";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <a href="#" class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="<?=$url;?>" align='left'>
            <a href="#" title='Retiradas do Produto <?=$campos[$i]['discriminacao'];?>' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan = '5'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'baixas_manipulacoes.php'" class='botao'>
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
<title>.:: Consultar Baixa(s) / Manipulação(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 3;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
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
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Baixa(s) / Manipulação(ões)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='opt1' value='1' title='Consultar Baixas/Manipulações por: Referência' onclick='document.form.txt_consultar.focus()'>
            <label for='opt1'>Referência</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='opt2' value='2' title='Consultar Baixas/Manipulações por: Discriminação' onclick='document.form.txt_consultar.focus()' checked>
            <label for='opt2'>Discrimina&ccedil;&atilde;o</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' id='opt3' value='3' title='Consultar Baixas/Manipulações por: Solicitador por' onclick='document.form.txt_consultar.focus()'>
            <label for='opt3'>
                <b>EPI solicitado por</b>
            </label>
        </td>
        <td>
            <input type='checkbox' name='opcao' id='todos' value='1' title='Consultar todos os Produtos Insumos' onclick='limpar()' class='checkbox'>
            <label for='todos'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color='red'><b>Observação:</b></font>

<b>* Só não traz P.I(s) do Tipo PRAC</b>
</pre>