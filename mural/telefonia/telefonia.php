<?
require('../../lib/segurancas.php');
session_start('fucionarios');
//segurancas::geral($PHP_SELF, '../../');
$mensagem[1] = "<font class='confirmacao'>TELEFONE EXCLUÍDO COM SUCESSO.</font>";

if(!empty($_POST['id_telefone'])) {//Exclusão dos Telefones ...
    $sql = "DELETE FROM `telefones` WHERE `id_telefone` = '$_POST[id_telefone]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

//Busca de Dados da tabela Telefone ...
$sql = "SELECT * 
	FROM `telefones` 
	ORDER BY departamento, nome, ramal ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Telefonia ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function excluir_item(id_telefone) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_telefone.value = id_telefone
        document.form.submit()
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<input type='hidden' name='id_telefone'>
<table width='70%' border='1' cellspacing='0' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Telefonia (Ramais) -
            <a href="javascript:nova_janela('regras.php', 'REGRAS', '', '', '', '', '480', '850', 'c', 'c')" title="Dicas de uso telefônico" class='link'>
               <font color='yellow' size='-1' style='cursor:help'>
                    Regras
                </font>
            </a>
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td>
            Nome
        </td>
        <td>
            Ramal
        </td>
        <?
            //Só mostrará esse botão p/ os usuários Roberto, Eunice, Anderson, Dárcio e Netto ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 183 || $_SESSION['id_funcionario'] == 135 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
        ?>
        <td>
            &nbsp;
        </td>
        <td>
            &nbsp;
        </td>
        <?
            }
        ?>
    </tr>
<?
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='4'>
            NÃO HÁ TELEFONE(S) CADASTRADO(S).
        </td>
    </tr>
<?
    }else {
        $departamento_anterior = '';
//Da a cor para os departamentos...
        $cor ['direção']    = "#FF0000";
        $cor ['cpd']        = "#0000FF";
        $cor ['compras']    = "#008000";
        $cor ['fabrica']    = "#000000";
        $cor ['financeiro'] = "#FF80FF";
        $cor ['vendas']     = "#AA0000";
            
        for($i = 0; $i < $linhas; $i++) {
/*Se o Depto. anterior for diferente do Departamento atual, então eu igualo essa variável 
ao Depto. atual ...*/
            if($departamento_anterior != $campos[$i]['departamento']) {
?>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            <font color='yellow'>
                  Departamento:
            </font>
            <?=$campos[$i]['departamento']?>
        </td>
    </tr>
<?
                $departamento_anterior = $campos[$i]['departamento'];
            }
?>
    <tr class="linhanormal">
        <td>
            <font color='<?=$cor[strtolower($campos[$i]['departamento'])];?>'>
                  <?=$campos[$i]['nome'];?>
            </font>
        </td>
        <td align='center'>
            <font color='<?=$cor[strtolower($campos[$i]['departamento'])];?>'>
                <?=$campos[$i]['ramal'];?>
            </font>
        </td>
        <?
            //Só mostrará esse botão p/ os usuários Roberto, Eunice, Anderson, Dárcio e Netto ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 183 || $_SESSION['id_funcionario'] == 135 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
        ?>
        <td align='center'>
            <img src="../../imagem/menu/alterar.png" border='0' onClick="window.location = 'alterar.php?id_telefone=<?=$campos[$i]['id_telefone'];?>'" alt="Alterar Telefone" title="Alterar Telefone">
        </td>
        <td align='center'>
            <img src="../../imagem/menu/excluir.png" border='0' onClick="excluir_item('<?=$campos[$i]['id_telefone'];?>')" alt="Excluir Telefone" title="Excluir Telefone">
        </td>
        <?
            }
        ?>
	</tr>
<?
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
        <?
            //Só mostrará esse botão p/ os usuários Roberto, Eunice, Anderson, Dárcio e Netto ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 183 || $_SESSION['id_funcionario'] == 135 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
        ?>
            <input type='button' name='cmd_incluir_telefone' value='Incluir Telefone' title='Incluir Telefone' onclick="window.location = 'incluir.php'" class='botao'>
        <?
            }
        ?>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' style='color:black' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>