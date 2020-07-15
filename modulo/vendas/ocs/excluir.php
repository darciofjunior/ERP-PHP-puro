<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>OC EXCLUÍDA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ESTA OC NÃO PODE SER EXCLUÍDA.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT DISTINCT(o.`id_oc`), DATE_FORMAT(o.`data_emissao`, '%d/%m/%Y') AS data_emissao, o.`observacao`, o.`nf_entrada`, c.razaosocial, c.`id_uf`, c.`cidade` 
                    FROM `ocs` o  
                    INNER JOIN `clientes` c ON c.`id_cliente` = o.`id_cliente` 
                    WHERE o.`id_oc` LIKE '$txt_consultar%'
                    AND o.`status` = '0' ORDER BY o.`id_oc` DESC ";
        break;
        case 2:
            $sql = "SELECT DISTINCT(o.`id_oc`), DATE_FORMAT(o.`data_emissao`, '%d/%m/%Y') AS data_emissao, o.`observacao`, o.`nf_entrada`, c.razaosocial, c.`id_uf`, c.`cidade` 
                    FROM `ocs` o  
                    INNER JOIN `clientes` c ON c.`id_cliente` = o.`id_cliente` 
                    WHERE c.`razaosocial` LIKE '%$txt_consultar%' 
                    AND o.`status` = '0' ORDER BY o.`id_oc` DESC ";
        break;
        case 3:
            $sql = "SELECT DISTINCT(o.`id_oc`), DATE_FORMAT(o.`data_emissao`, '%d/%m/%Y') AS data_emissao, o.`observacao`, o.`nf_entrada`, c.razaosocial, c.`id_uf`, c.`cidade` 
                    FROM `ocs` o 
                    INNER JOIN `clientes` c ON c.`id_cliente` = o.`id_cliente` 
                    WHERE o.`observacao` LIKE '%$txt_consultar%'
                    AND o.`status` = '0' ORDER BY o.`id_oc` DESC ";
        break;
        default:
            $sql = "SELECT DISTINCT(o.`id_oc`), DATE_FORMAT(o.`data_emissao`, '%d/%m/%Y') AS data_emissao, o.`observacao`, o.`nf_entrada`, c.razaosocial, c.`id_uf`, c.`cidade` 
                    FROM `ocs` o 
                    INNER JOIN `clientes` c ON c.`id_cliente` = o.`id_cliente`
                    AND o.`status` = '0' ORDER BY o.`id_oc` DESC ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'excluir.php?valor=1'
        </Script>
<?
    }else {
//Variável que será utilizada mais abaixo ...
        $prazo_validade_orc_dias = (int)genericas::variavel(38);
?>
<html>
<head>
<title>.:: Excluir OC(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr>
        <td colspan='10'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Excluir OC(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.&ordm; OC.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Cidade / Estado
        </td>
        <td>
            Data Em.
        </td>
        <td>
            NF de Entrada
        </td>
        <td>
            Observação
        </td>								
        <td>
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' title='Selecionar Tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['id_oc'];?>
        </td>
        <td align="left">
            <font title="Nome Fantasia: <?=$campos[$i]['nomefantasia'];?>" style="cursor:help">
                    <?=$campos[$i]['razaosocial'];?>
            </font>
            <?
                //Aqui verifica se a OC contém pelo menos 1 item ...
                $sql = "SELECT `id_oc_item` 
                        FROM `ocs_itens` 
                        WHERE `id_oc` = '".$campos[$i]['id_oc']."' LIMIT 1 ";
                $campos_itens_ocs 	= bancos::sql($sql);
                $qtde_itens_ocs 	= count($campos_itens_ocs);
                if($qtde_itens_ocs == 0) echo ' <font color="red">(S/ ITENS)</font>';
            ?>
        </td>
        <td>
        <?
            $sql = "SELECT `sigla` 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
            $campos_uf = bancos::sql($sql);
            echo $campos[$i]['cidade'].' / '.$campos_uf[0]['sigla'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>		
        <td>
            <?=$campos[$i]['nf_entrada'];?>
        </td>
        <td>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td>
            <input type='checkbox' name='chkt_oc[]' value="<?=$campos[$i]['id_oc'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'excluir.php'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}elseif ($passo == 2) {
    foreach($_POST['chkt_oc'] as $id_oc) {
        //Verifico se a OC possui pelo menos 1 item que já está em andamento ...
        $sql = "SELECT oi.`id_oc_item` 
                FROM `ocs_itens` oi 
                INNER JOIN `ocs` o ON o.`id_oc` = oi.`id_oc` AND o.`status` = '1' 
                WHERE o.`id_oc` = '$id_oc' 
                AND oi.`status` > '0' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {
            //Aqui eu busco todos os Itens da OC que vou excluir ...
            $sql = "SELECT `id_oc_item` 
                    FROM `ocs_itens` 
                    WHERE `id_oc` = '$id_oc' ";
            $campos_ocs_itens = bancos::sql($sql);
            $linhas_ocs_itens = count($campos_ocs_itens);
            for($i = 0; $i < $linhas_ocs_itens; $i++) {
                //Deleto os Follow-Up(s) do Item da OC ...
                $sql = "DELETE FROM `ocs_itens_follow_ups` WHERE `id_oc_item` = '".$campos_ocs_itens[$i]['id_oc_item']."' ";
                bancos::sql($sql);
                //Deleto o Item da OC ...
                $sql = "DELETE FROM `ocs_itens` WHERE `id_oc_item` = '".$campos_ocs_itens[$i]['id_oc_item']."' LIMIT 1 ";
                bancos::sql($sql);
            }
            //Por último deleto a OC em questão ...
            $sql = "DELETE FROM `ocs` WHERE `id_oc` = '$id_oc' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 2;
        }else {
            $valor = 3;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'excluir.php?valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir OC(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    if(document.form.opcao.checked == true) {
        for(var i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(var i = 0; i < 3; i++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar ...
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
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir OC(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' title='Consultar OC' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar O por: Número do OC' onclick='document.form.txt_consultar.focus()' id='label'>
            <label for='label'>Número do OC</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar OC por: Cliente' onclick='document.form.txt_consultar.focus()' id='label2' checked>
            <label for='label2'>Cliente</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='3' title='Consultar OC por: Observação' onclick='document.form.txt_consultar.focus()' id='label3'>
            <label for='label3'>Observação</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title='Selecionar Todos os Registros' id='label4' class='checkbox'>
            <label for="label4">Todos os registros</label>
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