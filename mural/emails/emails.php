<?
require('../../lib/segurancas.php');
require('../../modulo/classes/array_sistema/array_sistema.php');
session_start('funcionarios');

$mensagem[1] = "<font class='confirmacao'>FUNCIONÁRIO DESATRELADO COM SUCESSO DO GRUPO DE E-MAIL.</font>";

//Aqui eu desatrelo o Funcionário de um Grupo de E-mail ...
if(!empty($_GET['id_grupo_email_funcionario'])) {
//Aqui eu busco o id_funcionario do Grupo que está sendo excluído ...
    $sql = "SELECT id_funcionario 
            FROM `grupos_emails_vs_funcionarios` 
            WHERE `id_grupo_email_funcionario` = '$_GET[id_grupo_email_funcionario]' ";
    $campos_funcionario = bancos::sql($sql);
//Verifico se o Funcionário está em mais de um Grupo cadastrado ...
    $sql = "SELECT id_grupo_email_funcionario 
            FROM `grupos_emails_vs_funcionarios` 
            WHERE `id_grupo_email_funcionario` <> '$_GET[id_grupo_email_funcionario]' ";
    $campos_grupos = bancos::sql($sql);
    if(count($campos_grupos) == 0) {//Significa que o Funcionário só estava cadastrado em um único e-mail ...
//Atualiza o E-mail da pessoa para Vazio ...
        $sql = "UPDATE `funcionarios` SET `email_externo` = '' WHERE `id_funcionario` = '$_GET[id_funcionario]' LIMIT 1 ";
        bancos::sql($sql);
    }
//Deleta o Funcionário do Grupo requisitado inicialmente ...
    $sql = "DELETE FROM `grupos_emails_vs_funcionarios` where `id_grupo_email_funcionario` = '$_GET[id_grupo_email_funcionario]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}
?>
<html>
<head>
<title>.:: Listagem de E-mail(s) Albafer ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' src = '../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' src = '../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function desatrelar_funcionario(id_grupo_email_funcionario) {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA DESATRELAR ESSE FUNCIONÁRIO DESSE GRUPO DE E-MAIL ?')
    if(resposta == true) window.location = 'emails.php?id_grupo_email_funcionario='+id_grupo_email_funcionario
}
</Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
<?
//Vetor que armazena todos os grupos de e-mails criados, que está em outro arquivo ...
    $grupos_emails = array_sistema::grupos_emails();
    //sort($grupos_emails);//Ordena em Ordem Alfabética os Grupos de E-mails ...
    $linhas_grupo_email = count($grupos_emails);
    if($linhas_grupo_email > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Listagem do(s)
            <font color='yellow'>
                Grupo(s) de E-mail(s)
            </font>
            e 
            <font color='yellow'>
                Funcionário(s)
            </font>
            que possuem alguma Conta de E-mail
            <?
//Só mostra essa opção Roberto 62 e Dárcio 98 porque programa ...
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
            ?>
            &nbsp;<img src = '../../imagem/menu/adicao.jpeg' onclick="html5Lightbox.showLightbox(7, 'incluir_emails.php')" border='0' title='Incluir Conta(s) de E-mail' alt='Incluir Conta(s) de E-mail' width='16' height='16' style='cursor:help'>
            <?
                }
            ?>
        </td>
    </tr>
<?
//Comecei o For no 1, porque não existe índice zero p/ o meu vetor
        for($id_grupo_email = 1; $id_grupo_email <= $linhas_grupo_email; $id_grupo_email++) {
            if(!empty($grupos_emails[$id_grupo_email])) {
//Aqui eu verifico se existe @ nessa String ...
                $posicao_arroba = strpos($grupos_emails[$id_grupo_email], '@');
                
                if($posicao_arroba == '') {//Não existe e-mail nesse Grupo de E-mail ...
?>
    <tr onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='left'>
        <td class='linhacabecalho' colspan='2'>
            <font color='yellow' size='1'>
                <?=strtoupper(strtok($grupos_emails[$id_grupo_email], '@'));?>
            </font>
            <?
//Só mostra essa opção p/ Darcio, Roberto, Anderson e Netto ...
                    if($_SESSION['id_login'] == 92 || $_SESSION['id_login'] == 22 || $_SESSION['id_login'] == 88 || $_SESSION['id_login'] == 104) {
            ?>
            &nbsp;<img src = '../../imagem/menu/incluir.png' border='0' title='Atrelar Funcionário(s) nesse Grupo de E-mail' alt='Atrelar Funcionário(s) nesse Grupo de E-mail' onClick="javascript:nova_janela('atrelar_grupos_emails_funcs.php?id_grupo_email=<?=$id_grupo_email;?>', 'CONSULTAR', '', '', '', '', '360', '780', 'c', 'c', '', '', 's', 's', '', '', '')">
            <?
                    }
            ?>
        </td>
    </tr>
<?
                }else {//Significa que existe um Grupo ...
?>
    <tr align='left'>
        <td class='linhacabecalho'>
            <font color='yellow' size='1'>
                <?=strtoupper(strtok($grupos_emails[$id_grupo_email], '@'));?>
            </font>
        </td>
        <td class='linhacabecalho'>
            Grupo de E-mail: 
            <a href="mailto:<?=$grupos_emails[$id_grupo_email];?>" title='Enviar E-mail' class='link'>
                <font color='yellow' size='1'>
                    <?=$grupos_emails[$id_grupo_email];?>
                </font>
            </a>
            <?
//Só mostra essa opção p/ Darcio, Roberto, Anderson e Netto ...
                    if($_SESSION['id_login'] == 92 || $_SESSION['id_login'] == 22 || $_SESSION['id_login'] == 88 || $_SESSION['id_login'] == 104) {
            ?>
            &nbsp;<img src = "../../imagem/menu/incluir.png" border='0' title="Atrelar Funcionário(s) nesse Grupo de E-mail" alt="Atrelar Funcionário(s) nesse Grupo de E-mail" onClick="javascript:nova_janela('atrelar_grupos_emails_funcs.php?id_grupo_email=<?=$id_grupo_email;?>', 'CONSULTAR', '', '', '', '', '360', '780', 'c', 'c', '', '', 's', 's', '', '', '')">
            <?
                    }
            ?>
            </a>
        </td>
    </tr>
<?				
                }
            }
/*Aqui eu listo todos os Funcionários que estão atrelados ao Grupo de E-mail Corrente, desde que não foram 
demitidos ...*/
            $sql = "SELECT f.`id_funcionario`, f.`nome`, f.`email_externo`, f.`status`, 
                    gef.`id_grupo_email_funcionario` 
                    FROM `funcionarios` f 
                    INNER JOIN `grupos_emails_vs_funcionarios` gef ON gef.`id_funcionario` = f.`id_funcionario` AND gef.`id_grupo_email` = '$id_grupo_email' 
                    WHERE f.`status` < '3' 
                    ORDER BY f.`nome` ";
            $campos_funcionario = bancos::sql($sql);
            $linhas_funcionario = count($campos_funcionario);
//Se existir pelo menos 1 funcionário atrelado ao Grupo de E-mail ...
            if($linhas_funcionario > 0) {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário
        </td>
        <td>
            E-mail
        </td>
    </tr>
<?
                for($j = 0; $j < $linhas_funcionario; $j++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
        <?
                    echo $campos_funcionario[$j]['nome'];
//Só mostra essa opção p/ Luis, Roberto e p/ o Anderson ...
                    if($_SESSION['id_login'] == 92 || $_SESSION['id_login'] == 22 || $_SESSION['id_login'] == 88 || $_SESSION['id_login'] == 104) {
        ?>
        <img src = '../../imagem/menu/alterar.png' onclick="nova_janela('alterar_emails.php?id_funcionario_current=<?=$campos_funcionario[$j]['id_funcionario'];?>', 'ALTERAR_EMAILS', '', '', '', '', 175, 700, 'c', 'c')" border='0' title="Alterar Conta(s) de E-mail(s)" alt="Alterar Conta(s) de E-mail(s)" style='cursor:help'>
        &nbsp;
        <img src = '../../imagem/menu/excluir.png' onclick="desatrelar_funcionario('<?=$campos_funcionario[$j]['id_grupo_email_funcionario'];?>')" border='0' title='Desatrelar Funcionário desse Grupo de E-mail' alt='Desatrelar Funcionário do Grupo de E-mail' style='cursor:help'>
        <?
                    }
        ?>
        </td>
        <td align='center'>
            <a href='mailto:<?=$campos_funcionario[$j]['email_externo'];?>' title='Enviar E-mail' class='link'>
                <?=$campos_funcionario[$j]['email_externo'];?>
            </a>
        </td>
    </tr>
<?
/*Aqui eu guardo os Id´s de todos os funcionários que possuem uma conta de e-mail e que 
estão atrelado a algum grupo, vou utilizar mais abaixo ...*/
                    $id_funcs_em_grupo_email.= $campos_funcionario[$j]['id_funcionario'].', ';
                }
            }
        }
    }
//Só mostra essa opção p/ Darcio, Roberto, Anderson e Netto ...
    if($_SESSION['id_login'] == 92 || $_SESSION['id_login'] == 22 || $_SESSION['id_login'] == 88 || $_SESSION['id_login'] == 104) {
        if(!empty($id_funcs_em_grupo_email)) {
            $id_funcs_em_grupo_email = substr($id_funcs_em_grupo_email, 0, strlen($id_funcs_em_grupo_email) - 2);
        }else {
            $id_funcs_em_grupo_email = 0;
        }
/*Aqui eu listo todos os Funcionários que possuem contas de e-mail, e que não estão atrelados a nenhum 
Grupo de E-mail ...*/
        $sql = "SELECT `id_funcionario`, `nome`, `email_externo`, `status` 
                FROM `funcionarios` 
                WHERE `id_funcionario` NOT IN (1, 2, 91, 114, $id_funcs_em_grupo_email) 
                AND (`status` < '3' AND `email_externo` <> '') ORDER BY `nome` ";
        $campos_funcionario = bancos::sql($sql);
        $linhas_funcionario = count($campos_funcionario);
//Se existir pelo menos 1 funcionário atrelado ao Grupo de E-mail ...
        if($linhas_funcionario > 0) {
?>
    <tr>
        <td></td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            ALBAFER
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário
        </td>
        <td>
            E-mail
        </td>
    </tr>
<?
            for($j = 0; $j < $linhas_funcionario; $j++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos_funcionario[$j]['nome'];?>
            <img src = "../../imagem/menu/alterar.png" onclick="nova_janela('alterar_emails.php?id_funcionario_current=<?=$campos_funcionario[$j]['id_funcionario'];?>', 'ALTERAR_EMAILS', '', '', '', '', 175, 700, 'c', 'c')" border='0' title="Alterar Conta(s) de E-mail(s)" alt="Alterar Conta(s) de E-mail(s)" style='cursor:help'>
            &nbsp;
            <img src = "../../imagem/menu/excluir.png" onclick="desatrelar_funcionario('<?=$campos_funcionario[$j]['id_funcionario'];?>')" border='0' title="Desatrelar Funcionário desse Grupo de E-mail" alt="Desatrelar Funcionário do Grupo de E-mail" style='cursor:help'>
        </td>
        <td align='center'>
            <a href="mailto:<?=$campos_funcionario[$j]['email_externo'];?>" title="Enviar E-mail" class="link">
                <?=$campos_funcionario[$j]['email_externo'];?>
            </a>
        </td>
    </tr>
<?
            }
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            <font size='-1'>
                Servidores
            </font>
        </td>
        <td>
            <font size='-1'>
                IPs
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            Pop = pop.grupoalbafer.com.br
        </td>
        <td>
            200.234.205.130
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            Smtp = smtp.grupoalbafer.com.br
        </td>
        <td>
            200.234.205.130
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            Pop = pop.grupoalbafer.com.br (Externo)
        </td>
        <td>
            192.168.1.254
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            Smtp = smtp.grupoalbafer.com.br (Externo)
        </td>
        <td>
            192.168.1.254
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>