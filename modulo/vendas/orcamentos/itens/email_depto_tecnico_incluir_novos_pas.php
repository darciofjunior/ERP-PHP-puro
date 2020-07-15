<?
require('../../../../lib/segurancas.php');
require('../../../../lib/comunicacao.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

if(!empty($_POST['id_orcamento_venda'])) {
    $sql = "UPDATE `orcamentos_vendas` SET `incluir_novos_pas` = '".str_replace(chr(13), '<br>', $_POST['txt_incluir_novos_pas'])."' WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
    bancos::sql($sql);
    //Busca o nome do Cliente atrav�s do Or�amento ...
    $sql = "SELECT ov.id_cliente, c.razaosocial AS cliente 
            FROM `orcamentos_vendas` ov 
            INNER JOIN `clientes` c ON c.id_cliente = ov.id_cliente 
            WHERE ov.`id_orcamento_venda` = '$_POST[id_orcamento_venda]' LIMIT 1 ";
    $campos_cliente = bancos::sql($sql);
    //Aqui eu busco os representantes do cliente ...
    $sql = "SELECT DISTINCT(id_representante)
            FROM `clientes_vs_representantes`
            WHERE `id_cliente` = '".$campos_cliente[0]['id_cliente']."' ";
    $campos_representantes_cliente = bancos::sql($sql);
    $linhas_representantes_cliente = count($campos_representantes_cliente);
    for($i = 0; $i < $linhas_representantes_cliente; $i++) {
        //Verifico se o Representante � funcion�rio ...
        $sql = "SELECT id_funcionario 
                FROM `representantes_vs_funcionarios` 
                WHERE `id_representante` = '".$campos_representantes_cliente[$i]['id_representante']."' LIMIT 1 ";
        $campos_funcionario_representante = bancos::sql($sql);
        if(count($campos_funcionario_representante) > 0) {//� funcion�rio ...
            //Busca o e-mail do Funcion�rio que � representante ...
            $sql = "SELECT email_externo 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos_funcionario_representante[0]['id_funcionario']."' LIMIT 1 ";
            $campos_email_representante = bancos::sql($sql);
        }else {//N�o � Funcion�rio, ent�o � auton�mo ...
            //Busco o Supervisor do Representante aut�nomo ...
            $sql = "SELECT rf.`id_funcionario` 
                    FROM `representantes_vs_supervisores` rs 
                    INNER JOIN `representantes_vs_funcionarios` rf ON rf.id_representante = rs.id_representante_supervisor 
                    WHERE rs.`id_representante` = '".$campos_representantes_cliente[$i]['id_representante']."' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            //Busca o e-mail do Funcion�rio que � Supervisor ...
            $sql = "SELECT email_externo 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos_funcionario[0]['id_funcionario']."' LIMIT 1 ";
            $campos_email_representante = bancos::sql($sql);
        }
        $email_representante_cliente = ';'.$campos_email_representante[0]['email_externo'];
    }
    //Aqui eu busco o e-mail do Funcion�rio logado ...
    $sql = "SELECT `email_externo` 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_email               = bancos::sql($sql);
    $email_funcionario_logado   = $campos_email[0]['email_externo'];
    /*Se o Funcion�rio logado for o mesmo que o Representante do Cliente, ent�o eu zero essa vari�vel 
    $email_representante_cliente para que n�o enviei 2 e-mails para a mesma pessoa que est� escrevendo 
    o e-mail ...*/
    if($email_representante_cliente == $email_funcionario_logado) $email_representante_cliente = '';

    $mensagem = 'Segue abaixo a rela��o de novos PA(s) ESP � serem inclu�dos no ERP: ';
    $mensagem.= '<br><br>'.str_replace(chr(13), '<br>', $_POST['txt_incluir_novos_pas']);
    comunicacao::email($email_funcionario_logado, 'gcusto@grupoalbafer.com.br;'.$email_funcionario_logado.$email_representante_cliente, '', 'Incluir Novos PA(s) ESP - ORC N.� '.$_POST['id_orcamento_venda'].' - Cliente '.$campos_cliente[0]['cliente'], $mensagem);
?>
    <Script Language = 'JavaScript'>
        alert('E-MAIL ENVIADO COM SUCESSO P/ DEPTO. T�CNICO !')
        parent.window.location = 'itens.php?id_orcamento_venda=<?=$_POST['id_orcamento_venda'];?>'
    </Script>
<?
}

//Aqui eu busco os novos PA(s) atrav�s do id_orcamento_venda p/ enviar e-mail ...
$sql = "SELECT incluir_novos_pas 
        FROM `orcamentos_vendas` 
        WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Enviar e-mail p/ Depto. T�cnico Incluir Novos PA(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.txt_incluir_novos_pas.value == '') {
        alert('DIGITE O(S) NOVO(S) PA(S) PARA ENVIAR E-MAIL !!!')
        document.form.txt_incluir_novos_pas.focus()
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_orcamento_venda' value='<?=$_GET['id_orcamento_venda'];?>'>
<pre>
* Edite a discrimina��o dos PA(s) com todos os dados necess�rios para facilitar o cadastramento 
e confec��o dos Custos.
<font color='red'>* Desenhos</font> devem ser enviados imediamente ao Depto. T�cnico.
</pre>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Enviar e-mail p/ Depto. T�cnico Incluir Novos PA(s)
        </td>
    </tr>
    <tr class="linhanormal">
        <td>
            Incluir Novo(s) PA(s):
        </td>
        <td>
            <textarea name='txt_incluir_novos_pas' cols='100' rows='20' maxlength='2000' class='caixadetexto'><?=str_replace('<br>', chr(13), $campos[0]['incluir_novos_pas']);?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'"class='botao'>
            <input type='submit' name='cmd_enviar_email' value='Enviar E-mail' title='Enviar E-mail' style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>