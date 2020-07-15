<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/estoque_new.php');

segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PRODUTO INSUMO DEVOLVIDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>PRODUTO INSUMO EMPRESTADO COM SUCESSO.</font>";
$mensagem[4] = "<font class='erro'>QUANTIDADE INVÁLIDA PARA ESTA SOLICITAÇÃO.</font>";

if($passo == 1) {
    if(!empty($chkt_qtde_estoque)) $condicao = " AND pi.`qtde_estoque_pi` > '0' ";
    
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, 
                    pi.`qtde_estoque_pi`, g.`nome`, g.`referencia`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`referencia` LIKE '%$txt_consultar%' 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`ativo` = '1' $condicao ORDER BY pi.`discriminacao` ";
        break;
        case 2:
            $sql = "SELECT pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, 
                    pi.`qtde_estoque_pi`, g.`nome`, g.`referencia`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' $condicao ORDER BY pi.`discriminacao` ";
        break;
        case 3:
            $sql = "SELECT pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, 
                    pi.`qtde_estoque_pi`, g.`nome`, g.`referencia`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`observacao` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' $condicao ORDER BY pi.`discriminacao` ";
        break;
        default:
            $sql = "SELECT pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, 
                    pi.`qtde_estoque_pi`, g.`nome`, g.`referencia`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`ativo` = '1' $condicao ORDER BY pi.`discriminacao` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'usado.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Produto(s) Insumo(s) p/ Empréstimo de PI Usado ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Produto(s) Insumo(s) p/ Empréstimo de PI Usado
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Grupo
        </td>
        <td>
            Referência
        </td>
        <td>
            Unidade
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Qtde Est
        </td>
    </tr>
<?
	for($i = 0;  $i < $linhas; $i++) {
            if($campos[$i]['estocagem'] == 'S') {//Estocável ...
                $url = "javascript:window.location = 'usado.php?passo=2&txt_consultar=".$txt_consultar."&opt_opcao=".$opt_opcao."&id_produto_insumo=".$campos[$i]['id_produto_insumo']."'";
            }else {//Não Estocável
                $url = "javascript:alert('ESTE PRODUTO NÃO PODE SER MANIPULADO, DEVIDO A SER NÃO ESTOCÁVEL !')";
            }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$url;?>" align='left'>
            <a style='cursor:pointer' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
        <?
            if($campos[$i]['qtde_estoque_pi'] != '0.00') echo number_format($campos[$i]['qtde_estoque_pi'], 2, ',', '.');
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'usado.php'" class="botao">
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
    $sql = "SELECT u.unidade, pi.discriminacao, pi.qtde_estoque_pi, pi.observacao 
            FROM `produtos_insumos` pi 
            INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
            WHERE pi.`id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $unidade            = $campos[0]['unidade'];
    $discriminacao      = $campos[0]['discriminacao'];
    $observacao         = $campos[0]['observacao'];
    $qtde_estoque_pi    = $campos[0]['qtde_estoque_pi'];
?>
<html>
<title>.:: Empréstimo de PI Usado ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Tipo de Solicitação
    if(document.form.opt_solicitacao[0].checked == false && document.form.opt_solicitacao[1].checked == false) {
        alert('SELECIONE UM TIPO DE SOLICITAÇÃO !')
        document.form.opt_solicitacao[0].focus()
        return false
    }
//Qtde
    if(!texto('form', 'txt_quantidade', '1', '1234567890.,', 'QUANTIDADE', '1')) {
        return false
    }
//Funcionário
    if(!combo('form', 'cmb_funcionario', '', 'SELECIONE O FUNCIONÁRIO !')) {
        return false
    }
//Quando a Operação Solicitada for do Tipo Saída, tem que fazer essa verificação
    if(document.form.opt_solicitacao[1].checked == true) {
        var quantidade_estoque = eval(strtofloat(document.form.txt_quantidade_estoque.value))
        var quantidade = eval(strtofloat(document.form.txt_quantidade.value))
        if(quantidade > quantidade_estoque) {
                alert('QUANTIDADE INVÁLIDA PARA ESTA SOLICITAÇÃO !')
                document.form.txt_quantidade.focus()
                document.form.txt_quantidade.select()
                return false
        }
    }
    return limpeza_moeda('form', 'txt_quantidade, ')
}

function desabilitar_qtde() {
    if(document.form.opt_solicitacao[0].checked == true || document.form.opt_solicitacao[1].checked == true) {
        document.form.txt_quantidade.disabled = false
        document.form.txt_quantidade.className = 'caixadetexto'
        document.form.txt_quantidade.focus()
    }
}
</Script>
</head>
<body>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=3';?>" onSubmit="return validar()">
<input type="hidden" name="id_produto_insumo" value="<?=$_GET['id_produto_insumo'];?>">
<table border='0' width='70%' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Empréstimo de PI Usado
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>PRODUTO INSUMO: </b> 
            </font>
            <?=$discriminacao;?>
        </td>
        <td>
            <font color='darkblue'>
                <b>QUANTIDADE EM ESTOQUE:</b>
            </font>
            <input type="text" name="txt_quantidade_estoque" value="<?=number_format($qtde_estoque_pi, 2, ',', '.')?>" title="Quantidade em Estoque" class="textdisabled" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='darkblue'>
                <b>OBSERVAÇÃO DO PRODUTO:</b>
            </font>
            <?
                if(!empty($observacao)) {
                    echo $observacao;
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>TIPO DE SOLICITAÇÃO</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan="2">
            <input type="radio" name="opt_solicitacao" value='E' id='label1' onclick='desabilitar_qtde()'>
            <label for="label1">Entrada</label>&nbsp;&nbsp;
            <input type="radio" name="opt_solicitacao" value='S' id='label2' onclick='desabilitar_qtde()'>
            <label for="label2">Saída</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Quantidade:</b>
        </td>
        <td>
            <b>Funcionário:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_quantidade' title='Digite a Qtde' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength="10" class='textdisabled' disabled>
        </td>
        <td>
            <select name='cmb_funcionario' title='Selecione o Funcionário' class="combo">
            <?
                /*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
                Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
                no Sistema p/ poder acessar algumas telas e menos do cargo AUTONÔMO*/
                $sql = "SELECT f.id_funcionario, f.nome 
                        FROM `funcionarios` f 
                        INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo AND c.`id_cargo` <> '82' 
                        WHERE f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
                        AND f.`status` < '3' ORDER BY f.nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Observação:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name="txt_observacao" rows='1' cols='85' maxlength='85' class="caixadetexto"></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'usado.php'" class="botao">
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="redefinir('document.form', 'LIMPAR')" class="botao">
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
        </td>
    </tr>
</table>
<?
    $sql = "SELECT * 
            FROM `estoques_insumos_usados` 
            WHERE `id_produto_insumo` = '$_GET[id_produto_insumo]' ORDER BY data_sys DESC ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas > 0) {
?>
<table border='0' width='70%' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Detalhes
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Data
        </td>
        <td>
            Tipo de Solicitação
        </td>
        <td>
            Responsável
        </td>
        <td>
            Funcionário
        </td>
        <td>
            Qtde
        </td>
        <td>
            Observação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td align='center'>
            <?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').' - '.substr($campos[$i]['data_sys'], 11, 8);?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['tipo_entrada_saida'] == 'E') {
                echo 'ENTRADA';
            }else {
                echo 'SAÍDA';
            }
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = ".$campos[$i]['id_funcionario_resp']." LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            echo $campos_funcionario[0]['nome'];
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = ".$campos[$i]['id_funcionario_sol']." LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            echo $campos_funcionario[0]['nome'];
        ?>
        </td>
        <td align='center'>
            <?=number_format($campos[$i]['qtde'], 2, ',','.');?>
        </td>
        <td>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            &nbsp;
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
/****************************************Código Antigo*******************************************/
//Quantidade que resta em Estoque, independente de todas as Saídas e Entradas no Estoque
    //$resta_em_estoque	= (float)(string)($qtde_estoque-$total_saida + $total_entrada);
    //$txt_quantidade		= (float)(string)$txt_quantidade;
/************************************************************************************************/
    $data_sys = date('Y-m-d H:i:s');

    $sql = "SELECT qtde_estoque_pi 
            FROM `produtos_insumos` 
            WHERE `id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $qtde_estoque_pi    = $campos[0]['qtde_estoque_pi'];
	
//Aqui verifica a operação que está sendo feita no Estoque, só faz controle para saída de Materiais
    if($_POST['opt_solicitacao'] == 'S') {//Está sendo feito um empréstimo de Materiais
//Quantidade em Estoque do PI Usado
        //Preciso desta condição por causa q existe um erro no PHP de conversão ...
        if($_POST['txt_quantidade'] > $qtde_estoque_pi) $desviar = 1;
    }
    
    if($desviar == 1) {
        $valor = 4;
    }else {
        if($_POST['opt_solicitacao'] == 'S') {
            $restante = $qtde_estoque_pi - $_POST['txt_quantidade'];
        }else {
            $restante = $qtde_estoque_pi + $_POST['txt_quantidade'];
        }
//Atualização da Qtde na Tabela de Estoques
        $sql = "UPDATE `produtos_insumos` SET `qtde_estoque_pi` = '$restante' WHERE `id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
        bancos::sql($sql);
//Aqui grava a manipulação que foi feita pelo usuário
        $sql = "INSERT INTO `estoques_insumos_usados` (`id_estoque_insumo_usado`, `id_produto_insumo`, `id_funcionario_resp`, `id_funcionario_sol`, `tipo_entrada_saida`, `qtde`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_produto_insumo]', '$_SESSION[id_funcionario]', '$_POST[cmb_funcionario]', '$_POST[opt_solicitacao]', '$_POST[txt_quantidade]', '$_POST[txt_observacao]', '$data_sys') ";
        bancos::sql($sql);
        if($_POST['opt_solicitacao'] == 'E') {//Se for entrada
            $valor = 2;
        }else {//Se for Saída
            $valor = 3;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'usado.php?passo=1&valor=<?=$valor;?>&<?=$parametro;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Insumo(s) p/ Empréstimo de PI Usado ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value       = ''
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 3;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'className'
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
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Insumo(s) p/ Empréstimo de PI Usado
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar Produtos Insumos por: Referência" onclick='document.form.txt_consultar.focus()' id='label'>
            <label for='label'>
                Referência
            </label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" title="Consultar Produtos Insumos por: Referência" onclick='document.form.txt_consultar.focus()'id='label2' checked>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="3" title="Consultar Produtos Insumos por: Observação" onclick='document.form.txt_consultar.focus()' id='label3'>
            <label for='label3'>Observação</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='chkt_qtde_estoque' value='1' title="Qtde de Estoque > 0" id='label4' class="checkbox">
            <label for='label4'>Qtde de Estoque > 0</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os Produtos Insumos" onClick='limpar()' id='label5' class="checkbox">
            <label for='label5'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
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