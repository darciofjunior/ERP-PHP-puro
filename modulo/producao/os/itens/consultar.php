<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
/**********Concluindo a OS**********/
//Aqui eu concluo a OS ...
    if(!empty($_GET['id_os'])) {
        $sql = "UPDATE `oss` SET `status_nf` = '2' WHERE `id_os` = '$_GET[id_os]' LIMIT 1 ";
        bancos::sql($sql);
    }
/********************************************************/   
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_numero_os              = $_POST['txt_numero_os'];
        $txt_fornecedor             = $_POST['txt_fornecedor'];
        $chkt_os_em_aberto          = $_POST['chkt_os_em_aberto'];
        $chkt_ops_nao_finalizadas   = $_POST['chkt_ops_nao_finalizadas'];
    }else {
        $txt_numero_os              = $_GET['txt_numero_os'];
        $txt_fornecedor             = $_GET['txt_fornecedor'];
        $chkt_os_em_aberto          = $_GET['chkt_os_em_aberto'];
        $chkt_ops_nao_finalizadas   = $_GET['chkt_ops_nao_finalizadas'];
    }
    
    if(!empty($txt_numero_nf)) {
        require('consultar_dados_de_nfe.php');
        exit;
    }else if(!empty($txt_referencia) || !empty($txt_discriminacao) || !empty($txt_numero_op)) {
        require('consultar_dados_de_op.php');
        exit;
    }
    
    if(!empty($chkt_os_em_aberto))          $condicao_oss_em_aberto = " AND oss.`status_nf` < '2' ";
    if(!empty($chkt_ops_nao_finalizadas)) {
        $condicao_ops_nao_finalizadas = "
                INNER JOIN `oss_itens` oi ON oi.`id_os` = oss.`id_os` 
                INNER JOIN `ops` ON ops.`id_op` = oi.`id_op` AND ops.`status_finalizar` = '0' ";
    }
    
    $sql = "SELECT oss.*, f.`razaosocial` 
            FROM `oss` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` AND f.`razaosocial` LIKE '$txt_fornecedor%' 
            $condicao_ops_nao_finalizadas 
            WHERE oss.`id_os` LIKE '$txt_numero_os%' 
            AND oss.`ativo` = '1' 
            $condicao_oss_em_aberto 
            GROUP BY oss.`id_os` ORDER BY oss.`id_os` DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'consultar.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar OS(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function concluir_os(id_os) {
    var resposta = confirm('DESEJA REALMENTE CONCLUIR ESSA OS ?')
    if(resposta == true) {
        window.location = 'consultar.php<?=$parametro;?>&id_os='+id_os
    }
}
</Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar OS(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º OS
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Data de Saída
        </td>
        <td>
            Observação
        </td>
        <td>
            Concluir OS
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
            $url = "javascript:window.location = 'index.php?id_os=".$campos[$i]['id_os']."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width="10">
            <a href="#">
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="<?=$url;?>">
            <a href='#' class='link'>
                <?=$campos[$i]['id_os'];?>
            </a>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['razaosocial'];
//Aqui verifica se a O.S. contém pelo menos 1 item
            $sql = "SELECT `id_os_item` 
                    FROM `oss_itens` 
                    WHERE `id_os` = ".$campos[$i]['id_os']." LIMIT 1 ";
            $campos_oss = bancos::sql($sql);
            if(count($campos_oss) == 0) echo ' <font color="red">(S/ ITENS)</font>';
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['data_saida'] != '0000-00-00') echo data::datetodata($campos[$i]['data_saida'], '/');
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td>
        <?
            //Somente p/ os Logins Roberto, Dárcio e Netto porque programam ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
                //Se a OS ainda não foi concluída, então exibo essa figura p/ q o usuário venha concluir a mesma caso desejar ...
                if($campos[$i]['status'] < 2) {
        ?>
            <img src = '../../../../imagem/estornar.jpeg' title='Concluir OS' alt='Concluir OS' onclick="concluir_os('<?=$campos[$i]['id_os'];?>')" style='cursor:help' border='0'>
        <?
                }
            }
        ?>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php'" class='botao'>
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
<title>.:: Consultar OS(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function dar_entrada() {
//Número da OP ...
    if(!texto('form', 'txt_numero_op', '1', '0123456789', 'NÚMERO DA OP', '2')) {
        return false
    }
    html5Lightbox.showLightbox(7, 'dar_entrada.php?txt_numero_op='+document.form.txt_numero_op.value)
}

function controlar_checkboxs(objeto) {//Função que faz os checkbox funcionarem como options ...
    if(objeto.name == 'chkt_os_em_aberto') {
        document.form.chkt_os_em_aberto.checked = (document.form.chkt_os_em_aberto.checked == true) ? true : false
        document.form.chkt_ops_nao_finalizadas.checked  = false
    }else if(objeto.name == 'chkt_ops_nao_finalizadas') {
        document.form.chkt_ops_nao_finalizadas.checked = (document.form.chkt_ops_nao_finalizadas.checked == true) ? true : false
        document.form.chkt_os_em_aberto.checked         = false
    }
}
</Script>
</head>
<body onload='document.form.txt_numero_os.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar / Imprimir OS
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.° OS
        </td>
        <td>
            <input type='text' name='txt_numero_os' title='Digite o N.° da OS' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type='text' name='txt_fornecedor' size='60' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>N.° NF de Entrada</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_numero_nf' class='caixadetexto'>
        </td>
    </tr>    
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>N.° OP</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_numero_op' title='Digite o N.° da OP' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>Referência</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' size='35' maxlength='300' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red'>
                <b>Discriminação</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' size='50' maxlength='100' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_os_em_aberto' value='1' title='Só OS(s) em Aberto' id='label1' class='checkbox' onclick='controlar_checkboxs(this)' checked>
            <label for='label1'>Só OS(s) em Aberto</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ops_nao_finalizadas' value='1' title='OP(s) não Finalizadas' id='label2' onclick='controlar_checkboxs(this)' class='checkbox'>
            <label for='label2'>OP(s) não Finalizadas</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_numero_os.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_dar_entrada' value='Dar Entrada' title='Dar Entrada' style='color:purple' onclick='dar_entrada()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<font color='darkblue'>
<b>* O campo que está na cor azul apresenta uma Tela Pós-Filtro diferencial.</b>
</font><font color='red'>
<b>* Os campos que estão na cor vermelha apresentam uma Tela Pós-Filtro diferencial.</b>
</font>
</pre>
<?}?>