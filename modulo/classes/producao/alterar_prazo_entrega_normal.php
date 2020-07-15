<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
session_start('funcionarios');

if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo/prod_acabado_componente/pa_componente_todos.php', '../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo/prod_acabado_componente/pa_componente_esp.php', '../../../');
}
$mensagem[1] = "<font class='confirmacao'>PRAZO DE ENTREGA ALTERADO COM SUCESSO.</font>";

if(!empty($_POST['txt_prazo_entrega'])) {
    $data_sys = date('Y-m-d H:i:s');
//Verifica quem é o responsável pela alteração do prazo de entrega
    $sql = "SELECT login 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $login  = $campos[0]['login'];
//Junta o que o usuário digitou com o login do responsável que está fazendo a manipulação
    if(!empty($_POST['txt_prazo_entrega'])) {//Se tiver preenchido
        $prazo_entrega = $_POST['txt_prazo_entrega'].' => '.$login.' | '.$data_sys;
    }else {
        $prazo_entrega = ' => '.$login.' | '.$data_sys;
    }
//Verifica se o PA já existe na tabela relacional de Estoque Acabados para atualizar o Prazo de Entrega
    $sql = "SELECT id_estoque_acabado 
            FROM `estoques_acabados` 
            WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Já existe ...
        //Aki atualiza o Prazo de Entrega ...
        $sql = "UPDATE `estoques_acabados` SET `prazo_entrega` = '$prazo_entrega' WHERE `id_estoque_acabado` = '".$campos[0]['id_estoque_acabado']."' LIMIT 1 ";
    }else {//Não existe ...
        $sql = "INSERT INTO `estoques_acabados` (id_estoque_acabado, `id_produto_acabado`, `prazo_entrega`) VALUES (NULL, '$_POST[id_produto_acabado]', '$prazo_entrega') ";
    }
    bancos::sql($sql);
    $valor = 1;
}

$id_produto_acabado = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado'] : $_GET['id_produto_acabado'];

//Aqui se faz busca de dados do Produto Acabado passado por parâmetro ...
$sql = "SELECT referencia, discriminacao 
        from produtos_acabados 
        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar Prazo de Entrega ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_prazo_entrega.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Prazo de Entrega
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td width='40%'>
            <font color='yellow'>
                <b>Ref: </b>
            </font>
            <?=$campos[0]['referencia'];?>
        </td>
        <td width='60%'>
            <font color='yellow'>
                <b>Discriminação: </b>
            </font>
            <?=$campos[0]['discriminacao'];?>
        </td>
    </tr>
    <?
//Verifica se o PA tem prazo de Entrega
        $sql = "SELECT prazo_entrega 
                FROM `estoques_acabados` 
                WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        $campos_prazo_entrega = bancos::sql($sql);
        if(count($campos_prazo_entrega) == 0) {
            $string_apresentar  = '&nbsp;';
            $prazo_entrega      = '';
        }else {
            $prazo_entrega      = strtok($campos_prazo_entrega[0]['prazo_entrega'], '=');
//Se isso acontecer significa que não tem prazo de entrega e no BD ele grava um espaço para não dar erro
            if(strlen($prazo_entrega) == 1) $prazo_entrega = trim($prazo_entrega);
            $responsavel    = stristr(strtok($campos_prazo_entrega[0]['prazo_entrega'], '|'),'=>');
            $responsavel    = substr($responsavel, 3, strlen($responsavel));
            $data_hora      = data::datetodata(substr(stristr($campos_prazo_entrega[0]['prazo_entrega'], '|'), 2, 10), '/').' às '.substr(stristr($campos_prazo_entrega[0]['prazo_entrega'], '|'), 13, 8);
//Faz esse tratamento para o caso de não encontrar o responsável
            if(empty($responsavel)) {
                $string_apresentar = '&nbsp;';
            }else {
                $string_apresentar = $responsavel.' - '.$data_hora;
            }
        }
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Prazo de Entrega:</b>
        </td>
        <td>
            <input type='text' name='txt_prazo_entrega' value='<?=$prazo_entrega;?>' title='Digite o Prazo de Entrega' size='60' class='caixadetexto'>
            &nbsp;&nbsp;
            <a href="javascript:nova_janela('../../../modulo/classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'POP', '', '', '', '', 300, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Estoque' class='link'>
                <img src='../../../imagem/propriedades.png' title='Visualizar Estoque' alt='Visualizar Estoque' border='0'>
                &nbsp;Visualizar Estoque
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Responsável pelo Prazo de Ent:</b>
        </td>
        <td>
            <?=$string_apresentar;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_prazo_entrega.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>