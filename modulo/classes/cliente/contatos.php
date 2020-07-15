<?
require('../../../lib/segurancas.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>CLIENTE(S) CONTATO(S) EXCLUÍDO(S) COM SUCESSO.</font>";

if(!empty($_POST['chkt_cliente_contato'])) {
    foreach($_POST['chkt_cliente_contato'] as $id_cliente_contato) {
        $sql = "UPDATE `clientes_contatos` SET `ativo` = '0' WHERE `id_cliente_contato` = '$id_cliente_contato' LIMIT 1 ";
        bancos::sql($sql);
        
        $sql = "UPDATE `clientes` SET `data_atualizacao_emails_contatos` = '".date('Y-m-d')."' WHERE `id_cliente` = '$_POST[hdd_cliente]' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Contato(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
}

function alterar_contato(id_cliente_contato) {
    nova_janela('../../classes/cliente/alterar_contatos.php?id_cliente_contato='+id_cliente_contato, 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='100%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
//Aqui traz todos os contatos ATIVOS que estão relacionado ao cliente ...
$sql = "SELECT d.`departamento`, cc.* 
        FROM `clientes_contatos` cc 
        INNER JOIN `departamentos` d ON d.`id_departamento` = cc.`id_departamento` 
        WHERE cc.`id_cliente` = '$id_cliente' 
        and cc.`ativo` = '1' 
        ORDER BY cc.`nome` ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas > 0) {
?>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Contato(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Departamento
        </td>
        <td>
            Nome
        </td>
        <td>
            DDI / DDD / Telefone
        </td>
        <td>
            Ramal
        </td>
        <td>
            E-mail
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
    $emails_invalidos = 'N';

    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['departamento'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['ddi'].' / '.$campos[$i]['ddd'].' / '.$campos[$i]['telefone'];?>
        </td>
        <td>
            <?=$campos[$i]['ramal'];?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['email'])) {
                echo $campos[$i]['email'];
                
                /*Segurança para o vendedor não fazer trambicagem, porque nunca podemos ter albafer no endereço de e-mail, 
                afinal albafer é e-mail daqui da empresa ...*/
                if(strpos($campos[$i]['email'], 'grupoalbafer') > 0) {
                    $emails_invalidos = 'S';
                    $emails_contatos_preenchidos--;
                }
                $emails_contatos_preenchidos++;
            }
        ?>
        </td>
        <td>
            <img src = '../../../imagem/menu/alterar.png' border='0' title='Alterar' alt='Alterar' onclick="alterar_contato('<?=$campos[$i]['id_cliente_contato'];?>')">
        </td>
        <td>
            <input type='checkbox' name='chkt_cliente_contato[]' value='<?=$campos[$i]['id_cliente_contato'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_incluir_contato' value='Incluir Contato' title='Incluir Contato' onclick="nova_janela('../../classes/cliente/incluir_contatos.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')" style='color:red' class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
        </td>
    </tr>
<?
    if($emails_invalidos == 'S') {
?>
    <tr class='erro' align='center'>
        <td colspan='7'>
            EXISTE(M) ENDEREÇO(S) DE E-MAIL(S) INVÁLIDO(S) !!! <p/>E-MAIL(S) COM DADOS DAQUI DA EMPRESA !
        </td>
    </tr>
<?    
    }

    //Significa que ainda existe(m) contato(s) de cliente(s) que ficaram sem email(s) preenchidos ...
    if($emails_contatos_preenchidos != $linhas) {
        //Faço isso de propósito justamente para forçar o usuário a preencher cada e-mail de seus contatos cadastrados ...
        $sql = "UPDATE `clientes` SET `data_atualizacao_emails_contatos` = '0000-00-00' WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        bancos::sql($sql);
    }
}else {
?>
    <tr class='atencao' align='center'>
        <td>
            NÃO EXISTE(M) CONTATO(S) CADASTRADO(S).
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_incluir_contato' value='Incluir Contato' title='Incluir Contato' onclick="nova_janela('../../classes/cliente/incluir_contatos.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '300', '600', 'c', 'c', '', '', 's', 's', '', '', '')" style='color:red' class='botao'>
        </td>
    </tr>
<?
}
?>
<input type='hidden' name='hdd_cliente' value='<?=$id_cliente;?>'>
</form>
</table>
</body>
</html>
