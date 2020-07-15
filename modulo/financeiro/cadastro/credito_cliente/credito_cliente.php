<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/financeiros.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CRÉDITO ALTERADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>CRÉDITO DE CLIENTE ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_cliente            = $_POST['txt_cliente'];
        $txt_cnpj_cpf           = $_POST['txt_cnpj_cpf'];
        $txt_cidade             = $_POST['txt_cidade'];
        $cmb_uf                 = $_POST['cmb_uf'];
        $cmb_email_financeiro 	= $_POST['cmb_email_financeiro'];
    }else {
        $txt_cliente            = $_GET['txt_cliente'];
        $txt_cnpj_cpf           = $_GET['txt_cnpj_cpf'];
        $txt_cidade             = $_GET['txt_cidade'];
        $cmb_uf                 = $_GET['cmb_uf'];
        $cmb_email_financeiro 	= $_GET['cmb_email_financeiro'];
    }

//Tratamento com os Campos de "CNPJ ou CPF" ...
    $txt_cnpj_cpf   = str_replace('.', '', $txt_cnpj_cpf);
    $txt_cnpj_cpf   = str_replace('.', '', $txt_cnpj_cpf);
    $txt_cnpj_cpf   = str_replace('/', '', $txt_cnpj_cpf);
    $txt_cnpj_cpf   = str_replace('-', '', $txt_cnpj_cpf);
    
    if(!empty($cmb_uf)) $condicao_uf = " AND c.`id_uf` LIKE '$cmb_uf' ";

    $sql = "SELECT DISTINCT(c.`id_cliente`), c.`nomefantasia`, c.`razaosocial`, c.`cidade`, c.`ddi_com`, 
            c.`ddd_com`, c.`telcom`, c.`cnpj_cpf`, ct.`tipo`, ufs.`sigla` 
            FROM `clientes` c 
            LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
            LEFT JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
            WHERE (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') 
            AND c.`cnpj_cpf` LIKE '%$txt_cnpj_cpf%' 
            AND c.`cidade` LIKE '%$txt_cidade%' 
            $condicao_uf 
            $cmb_email_financeiro 
            AND c.`ativo` = '1' 
            ORDER BY c.`razaosocial` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'credito_cliente.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Cliente(s) p/ Alterar Crédito ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Cliente(s) p/ Alterar Crédito
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Cliente
        </td>
        <td>
            Tipo de Cliente
        </td>
        <td>
            Cidade
        </td>
        <td>
            UF
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Cr
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $credito = financeiros::controle_credito($campos[$i]['id_cliente']);
            $url = 'detalhes.php?id_cliente='.$campos[$i]['id_cliente'];
?>
    <tr class='linhanormal'>
        <td onclick="window.location='<?=$url;?>'" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="window.location='<?=$url;?>'">
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td align='center'>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com'])) echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com'])) echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com'])) echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com'])) echo $campos[$i]['telcom'];
        ?>
        </td>
        <td align='center'>
            <font color='blue'>
                <?=$credito;?>
            </font>
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
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'credito_cliente.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<pre>
<font color='red'><b>Legenda dos Tipos de Cliente:</b></font>

 <font color='blue'><b>RA</b></font> -> Revenda Ativa
 <font color='blue'><b>RI</b></font> -> Revenda Inativa
 <font color='blue'><b>CO</b></font> -> Cooperado
 <font color='blue'><b>ID</b></font> -> Indústria
 <font color='blue'><b>AT</b></font> -> Atacadista
 <font color='blue'><b>DT</b></font> -> Distribuidor
 <font color='blue'><b>IT</b></font> -> Internacional
 <font color='blue'><b>FN</b></font> -> Fornecedor
 <font color='blue'><b>UC</b></font> -> Usina de Cana
</pre>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Consultar Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_cliente.focus()'>
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
            Consultar Cliente(s) p/ Alterar Crédito
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type='text' name='txt_cliente' title='Digite o Cliente' size='55' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CNPJ ou CPF
        </td>
        <td>
            <input type='text' name='txt_cnpj_cpf' title='Digite o CNPJ ou CPF' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade
        </td>
        <td>
            <input type='text' name='txt_cidade' title='Digite a Cidade' size='32' maxlength='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            UF
        </b>
        <td>
            <select name='cmb_uf' title='Selecione a UF' class='combo'>
            <?
                $sql = "SELECT `id_uf`, `sigla` 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY `sigla` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            E-Mail Financeiro
        </td>
        <td>
            <select name='cmb_email_financeiro' title='Selecione o E-mail Financeiro' class='combo'>
                <option value=''>SELECIONE</option>
                <option value=" AND c.`email_financeiro` <> ''">Clientes com E-Mail</option>
                <option value=" AND c.`email_financeiro` = ''">Clientes sem E-Mail</option>
            </select>
        </td>
    </tr>	
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick='document.form.txt_cliente.focus()' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>