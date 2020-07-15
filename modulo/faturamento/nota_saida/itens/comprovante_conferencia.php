<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');

switch($opcao) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    case 4://Significa que veio do Menu de Devolução 
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
    default://Significa que veio do Menu de Devolução ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
}

//Aqui traz os dados da Nota Fiscal
$sql = "SELECT nfs.* 
        FROM `nfs` 
        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos                 = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
$id_empresa_nota        = $campos[0]['id_empresa'];
$id_cliente             = $campos[0]['id_cliente'];

//Aqui verifica o Tipo de Nota
if($id_empresa_nota == 1 || $id_empresa_nota == 2) {
    $nota_sgd = 'N';//var surti efeito lá embaixo
    $tipo_nota = ' (NF)';
}else {
    $nota_sgd = 'S'; //var surti efeito lá embaixo
    $tipo_nota = ' (SGD)';
}

if($campos[0]['data_emissao'] != '0000-00-00') $data_emissao = data::datetodata($campos[0]['data_emissao'], '/');

//Prazos
$vencimento1 = $campos[0]['vencimento1'];
if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento1 = data::adicionar_data_hora($data_emissao, $vencimento1);

if($campos[0]['vencimento2'] == 0) {
    $vencimento2 = '';
    $data_vencimento2 = '';
}else {
    $vencimento2 = $campos[0]['vencimento2'];
    if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento2 = data::adicionar_data_hora($data_emissao, $vencimento2);
}

if($campos[0]['vencimento3'] == 0) {
    $vencimento3 = '';
    $data_vencimento3 = '';
}else {
    $vencimento3 = $campos[0]['vencimento3'];
    if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento3 = data::adicionar_data_hora($data_emissao, $vencimento3);
}

if($campos[0]['vencimento4'] == 0) {
    $vencimento4 = '';
    $data_vencimento4 = '';
}else {
    $vencimento4 = $campos[0]['vencimento4'];
    if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento4 = data::adicionar_data_hora($data_emissao, $vencimento4);
}
$valor_dolar_nota = number_format($campos[0]['valor_dolar_dia'], 4, ',', '.');
	
if($campos[0]['data_saida_entrada'] != '0000-00-00') $data_saida_entrada = data::datetodata($campos[0]['data_saida_entrada'], '/');
$vide_nf            = $campos[0]['vide_nf'];
$peso_bruto_balanca = number_format($campos[0]['peso_bruto_balanca'], 2, ',', '.');
$observacao         = $campos[0]['observacao'];
$status             = $campos[0]['status'];

$sql = "SELECT c.`cidade`, c.`credito`, c.`razaosocial`, p.`pais` 
        FROM `clientes` c 
        INNER JOIN `paises` p ON p.`id_pais` = c.`id_pais` 
        WHERE c.`id_cliente` ='$id_cliente' LIMIT 1 ";
$campos         = bancos::sql($sql);
$cidade         = $campos[0]['cidade'];
$credito        = $campos[0]['credito'];
$razaosocial    = $campos[0]['razaosocial'];

/*Aqui verifica se a Nota Fiscal tem pelo menos 1 item cadastrado, se tiver não pode alterar 
a Empresa e o Tipo de Nota*/
$sql = "SELECT id_nfs_item 
        FROM `nfs_itens` 
        WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos_itens   = bancos::sql($sql);
$qtde_itens_nf  = count($campos_itens);
?>
<html>
<head>
<title>.:: Comprovante de Conferência de Material ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
document.oncontextmenu = function () { return false }
if(document.layers) {
    window.captureEvents(event.mousedown)
    window.onmousedown =
    function (e){
    if (e.target == document)
        return false
    }
}else {
    document.onmousedown = function (){ return false }
}

//Função que trava o teclado
function controle_teclas() {
    if(navigator.appName == 'Netscape') {//Mozilla, Netscape
        return false
    }else {//Controle para Internet Explorer
        var func1 = document.form.opt_funcionario[0].checked
        var func2 = document.form.opt_funcionario[1].checked
        var func3 = document.form.opt_funcionario[2].checked
        var func4 = document.form.opt_funcionario[3].checked
        if(func1 == false && func2 == false && func3 == false && func4 == false) {
            alert('SELECIONE UM FUNCIONÁRIO !')
            document.form.opt_funcionario[0].focus()
            return false
        }else {
            alert('UTILIZE O BOTÃO IMPRIMIR !')
            return false
        }
    }
}

function validar() {
    var func1 = document.form.opt_funcionario[0].checked
    var func2 = document.form.opt_funcionario[1].checked
    var func3 = document.form.opt_funcionario[2].checked
    var func4 = document.form.opt_funcionario[3].checked
    if(func1 == false && func2 == false && func3 == false && func4 == false) {
        alert('SELECIONE UM FUNCIONÁRIO !')
        document.form.opt_funcionario[0].focus()
        return false
    }else {
        document.form.submit()
        window.print()
    }
}
</Script>
</head>
<body topmargin='15' onkeypress='return controle_teclas()' onkeydown='return controle_teclas()'>
<form name='form' method='post' action='atualizar_comprovante.php' target='atualizar_comprovante'>
<input type='hidden' name='id_nf' value="<?=$id_nf;?>">
<table width='90%' cellspacing='0' cellpadding='1' border="1" align='center'>
    <tr class='linhanormal' align="center" valign="center">
        <td width="96" rowspan="2" bgcolor="#FFFFFF">
            <img src="../../../../imagem/logosistema.jpg" width="90" height="104">
        </td>
        <td colspan="2" bgcolor="#FFFFFF">
            &nbsp;&nbsp;&nbsp;&nbsp; <img src="../../../../imagem/marcas/cabri.png">
            &nbsp;&nbsp;&nbsp;&nbsp; <img src="../../../../imagem/marcas/heinz.png" width="111" height="34">
            &nbsp;&nbsp;&nbsp;&nbsp; <img src="../../../../imagem/marcas/tool.png" width="137" height="28">
            &nbsp;&nbsp;&nbsp;&nbsp; <img src="../../../../imagem/marcas/nvo.png" width="81" height="42">
            &nbsp;&nbsp;&nbsp;&nbsp; <img src="../../../../imagem/marcas/warrior.jpg" width="164" height="36">
        </td>
    </tr>
    <tr class='linhacabecalho' align='center' valign='center'>
        <td colspan="2" bgcolor="#FFFFFF">
            <font size='4'>
                <b><i>COMPROVANTE DE CONFERÊNCIA DE MATERIAL</i></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3' align='left' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Empresa: </b><?=genericas::nome_empresa($id_empresa_nota);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3' align='left' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Cliente: </b><?=$razaosocial;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2' align='left' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Nota Fiscal N.&ordm;:</b>&nbsp;<?=faturamentos::buscar_numero_nf($_GET['id_nf'], 'S');?>
            </font>
        </td>
        <td bgcolor="#FFFFFF">
            <font size='2'>
                <b>Data de Emiss&atilde;o:</b><?=$data_emissao;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2' align='left' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>Quantidade de caixas:</b>
                <?
                    if($qtde_itens_nf > 0) {
                        $peso_nf = faturamentos::calculo_peso_nf($id_nf);
                        echo $peso_nf['qtde_caixas'];
                    }
                ?>
            </font>
        </td>
        <td bgcolor='#FFFFFF'>
            <font size='2'><b>Peso Bruto da Nota:</b>
            <?=$peso_bruto_balanca.' Kg';
//Zera a variável porque na função de cálculo de peso nf, essa variável é declarada como GLOBAL
                //$GLOBALS['peso_total_vide_nota'] = 0;
                //$peso_nf = faturamentos::calculo_peso_nf($id_nf);
                //$peso_bruto = $peso_nf['peso_total_nf_current'] + $peso_nf['peso_total_emb_nf_current'] + $peso_nf['peso_total_vide_nota'];
                //echo number_format($peso_bruto, 4, ',', '.');
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3' align='left' bgcolor='#FFFFFF'>
            <font size='2'>
                <b>O MATERIAL CONSTANTE NESTA NOTA FISCAL FOI RECONFERIDO PELO FUNCIONÁRIO:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' height='50'>
        <td colspan='3' align='left' bgcolor='#FFFFFF'>
            <font size='2'>
                (<input type="radio" name="opt_funcionario" value="ivair" id="label1">) <label for="label1">Ivair</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                (<input type="radio" name="opt_funcionario" value="marcio" id="label2">) <label for="label2">Marcio</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                (<input type="radio" name="opt_funcionario" value="agueda" id="label3">) <label for="label3">Agueda</label>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                (<input type="radio" name="opt_funcionario" value="rivaldo" id="label4">) <label for="label4">Rivaldo</label> 
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3' align='left' bgcolor='#FFFFFF'>
            <font size='2'><b>Data de Conferência:</b>
                <?=date('d/m/Y');?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center' valign='left' bgcolor='#FFFFFF'>
        <td colspan='3' align='left' bgcolor="#FFFFFF">
            <font size='2'>
                <u><b>IMPORTANTE:</b></u><br>
                <ul>
                    <li>Caso seja encontrada alguma divergência, contatar-nos imediatamente (no ato do recebimento).<br>
                    <li>Em caso de devolução deste material, incluir este comprovante <b>explicando o motivo</b><br>
                    <li>Não aceitaremos reclamações posteriores
                </ul>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3' align='left' bgcolor='#FFFFFF'>
            <font size='2'>
                <u><b>Central de Atendimento:</b></u>
                <br>
                <ul>
                <?
/******************************************Tool Master******************************************/
//Busca de Dados da Empresa Tool Master ...
                    $sql = "SELECT razaosocial 
                            FROM `empresas` 
                            WHERE `id_empresa` = '2' LIMIT 1 ";
                    $campos_fone        = bancos::sql($sql);
                    $razao_social_tool  = $campos_fone[0]['razaosocial'];
    /******************************************Albafér******************************************/
    //Busca de Dados da Empresa Albafer ...
                    $sql = "SELECT * 
                            FROM `empresas` 
                            WHERE `id_empresa` = '1' LIMIT 1 ";
                    $campos_fone        = bancos::sql($sql);
                    $razao_social_alba  = $campos_fone[0]['razaosocial'];
                    $endereco           = $campos_fone[0]['endereco'];
                    $numero             = $campos_fone[0]['numero'];
                    $bairro             = strtoupper($campos_fone[0]['bairro']);
                    $cidade             = strtoupper($campos_fone[0]['cidade']);
                    $telefone_comercial = $campos_fone[0]['telefone_comercial'];
                    $home               = $campos_fone[0]['home'];
                    $cep                = $campos_fone[0]['cep'];
                    $site               = $campos_fone[0]['home'];
                    $email              = $campos_fone[0]['email'];
                    $ddd_comercial      = $campos_fone[0]['ddd_comercial'];

                    echo $razao_social_alba.' '.$razao_social_tool.'<br>';
                    echo $endereco.', N.º '.$numero.' - '.$bairro.' - '.$cidade.' - CEP: '.$cep.'<br>';
                    echo '<b>FONE/FAX: (55-'.$ddd_comercial.') '.$telefone_comercial.'</b> # E-MAIL: '.$email.' - Site: '.$site;
                ?>
                </ul>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center' valign='center'>
        <td height='17' bgcolor='#FFFFFF'>&nbsp;</td>
        <td width='320' bgcolor='#FFFFFF'>&nbsp;</td>
        <td bgcolor='#FFFFFF'>&nbsp;</td>
    </tr>
</table>
<center>
<?
//Traço para Destacar
for($i = 0; $i < 168; $i++) echo '-';
?>
<br>
    <input type="button" name="cmd_imprimir" value="Imprimir" title="Imprimir" onclick="return validar()" class="botao">
    <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class="botao">
</center>
<!--Aqui nesse iframe eu atualizo quem foi o responsável pela conferência da Nota, porque é muito importante-->
<iframe name="atualizar_comprovante" src="atualizar_comprovante.php" height="0" width="0" frameborder="0">
</iframe>
</form>
</body>
</html>