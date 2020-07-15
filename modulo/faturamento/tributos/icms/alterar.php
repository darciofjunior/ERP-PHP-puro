<?
require('../../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require('../../../../lib/menu/menu.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>NÃO HÁ ICMS / IVA CADASTRADO PARA ESSA CLASSIFICAÇÃO FISCAL.</font>";
$mensagem[3] = "<font class='confirmacao'>ICMS / IVA ALTERADO COM SUCESSO.</font>";
$mensagem[4] = "<font class='atencao'>É NECESSÁRIO SELECIONAR UMA UF.</font>";

if($passo == 1) {
    //Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $cmb_uf                             = $_POST['cmb_uf'];
        $chkt_pa_comercializado_pelo_grupo  = $_POST['chkt_pa_comercializado_pelo_grupo'];
    }else {
        $cmb_uf                             = $_GET['cmb_uf'];
        $chkt_pa_comercializado_pelo_grupo  = $_GET['chkt_pa_comercializado_pelo_grupo'];
    }
    if(!empty($chkt_pa_comercializado_pelo_grupo)) $condicao = " AND cf.`pa_comercializado_pelo_grupo` = 'S' ";
    
    $sql = "SELECT cf.`id_classific_fiscal`, cf.`classific_fiscal`, cf.`ipi`, icms.`id_icms`, icms.`icms`, 
            icms.`reducao`, icms.`icms_intraestadual`, icms.`iva`, icms.`fecp` 
            FROM `classific_fiscais` cf 
            INNER JOIN `icms` ON icms.`id_classific_fiscal` = cf.`id_classific_fiscal` AND icms.`id_uf` = '$cmb_uf' 
            WHERE cf.`ativo` = '1' 
            $condicao ORDER BY cf.`classific_fiscal` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'alterar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar ICMS / IVA ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        //Preparo os objetos antes de gravar no BD ...
        if(elementos[i].type == 'text') elementos[i].value = strtofloat(elementos[i].value)
    }
//Aqui eu desabilito o botão Salvar p/ não acontecer de o usuário clicar várias vezes ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Alterar ICMS / IVA => 
            <font color='yellow'>
            <?
                $sql = "SELECT `sigla` 
                        FROM `ufs` 
                        WHERE id_uf = '$cmb_uf' LIMIT 1 ";
                $campos_uf = bancos::sql($sql);
                echo $campos_uf[0]['sigla'];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Id
        </td>
        <td>
            Classificação Fiscal
        </td>
        <td>
            IPI
        </td>
        <td>
            ICMS
        </td>
        <td>
            Red. BC
        </td>
        <td>
            ICMS IntraEstadual
        </td>
        <td>
            <font title='Fundo Estadual de Combate à Pobreza' style='cursor:help'>
                FECP
            </font>
        </td>
        <td>
            IVA
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
            $url = 'alterar.php?passo=2&id_classific_fiscal='.$campos[$i]['id_classific_fiscal'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10' onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" title='Alterar ICMS / IVA' style='cursor:help' class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" title='Alterar ICMS / IVA' style='cursor:help' class='link'>
                <?=$campos[$i]['id_classific_fiscal'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td>
            <a href = '../classif_fiscal/alterar.php?passo=2&id_classific_fiscal=<?=$campos[$i]['id_classific_fiscal'];?>&pop_up=1' class='html5lightbox'>
                <?=number_format($campos[$i]['ipi'], 2, ',', '.').' %';?>
            </a>
        </td>
        <td>
            <input type='text' name='txt_icms[]' value='<?=number_format($campos[$i]['icms'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='7' class='caixadetexto'> %
        </td>
        <td>
            <input type='text' name='txt_reducao[]' value='<?=number_format($campos[$i]['reducao'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='7' class='caixadetexto'> %
        </td>
        <td>
            <input type='text' name='txt_icms_intraestadual[]' value='<?=number_format($campos[$i]['icms_intraestadual'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='7' class='caixadetexto'> %
        </td>
        <td>
            <input type='text' name='txt_fecp[]' value='<?=number_format($campos[$i]['fecp'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='7' class='caixadetexto'> %
        </td>
        <td>
            <input type='text' name='txt_iva[]' value='<?=number_format($campos[$i]['iva'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='7' class='caixadetexto'> %
            <input type='hidden' name='hdd_icms[]' value='<?=$campos[$i]['id_icms']?>'>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    if($campos_uf[0]['sigla'] == 'SP') {//Somente no Estado de São Paulo que eu faço esse controle ...
?>
        <br/>
        <font color='red'>
            *** O CERTO ERA TERMOS TODAS AS ALÍQUOTAS INTRAESTADUAIS COMO SENDO 18%.
            
            <p/>* PORÉM NO ESTADO DE SÃO TANTO AS ALÍQUOTAS INTERESTADUAIS QUANTO AS ALÍQUOTAS INTRAESTADUAIS TEM DE SEREM IGUAIS, O QUE JUSTIFICA 
            ENTÃO DE TERMOS ALGUMAS NO VALOR DE 12%.
        </font>
<?
    }
?>
</body>
</html>
<?
    }
}else if($passo == 2) {
    //Procedimento normal de quando se carrega a Tela ...
    $id_classific_fiscal = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_classific_fiscal'] : $_GET['id_classific_fiscal'];

    //Busca dados da Classificação Fiscal Corrente ...
    $sql = "SELECT classific_fiscal, reducao_governo 
            FROM `classific_fiscais` 
            WHERE `id_classific_fiscal` = '$id_classific_fiscal' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $classific_fiscal       = $campos[0]['classific_fiscal'];
    $texto_reducao_governo  = $campos[0]['reducao_governo'];
    
    //Busca todos os Estados que estão atreladas p/ está classificação corrente ...
    $sql = "SELECT i.*, u.`sigla`, u.`convenio` 
            FROM `icms` i 
            INNER JOIN `ufs` u ON u.`id_uf` = i.`id_uf` 
            WHERE i.`id_classific_fiscal` = '$id_classific_fiscal' 
            AND i.`ativo` = '1' ORDER BY u.`sigla` ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php<?=$parametro;?>&passo=1&valor=2'
    </Script>
<?
        exit;
    }
?>
<html>
<title>.:: Alterar ICMS / IVA ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        //Preparo os objetos antes de gravar no BD ...
        if(elementos[i].type == 'text') elementos[i].value = strtofloat(elementos[i].value)
    }
//Aqui eu desabilito o botão Salvar p/ não acontecer de o usuário clicar várias vezes ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
}
</Script>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit='return validar()'>
<table width='95%' cellspacing='1' cellpadding='1' border='0' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='8'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            Alterar ICMS / IVA - 
            <font color='yellow'>
                ID: 
            </font>
            <?=$_GET['id_classific_fiscal'];?>
            - 
            <font color='yellow'>
                Classificação Fiscal:
            </font>
            <?=$classific_fiscal;?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            UF
        </td>
        <td>
            <font title='Alíq. Icms Interestadual' style='cursor:help'>
                Alíq. Icms Inter
            </font>
        </td>
        <td>
            <font title='Redução de Base de Cálculo' style='cursor:help'>
                Red. BC
            </font>
        </td>
        <td>
            <font title='Alíq. Icms Intraestadual' style='cursor:help'>
                Alíq. Icms Intra
            </font>
        </td>
        <td>
            <font title='Fundo Estadual de Combate à Pobreza' style='cursor:help'>
                FECP
            </font>
        </td>
        <td>
            IVA
        </td>
        <td>
            <font title='Texto da Nota na Classificação Fiscal' style='cursor:help'>
                Texto da Nota na CF
            </font>
        </td>
        <td>
            Convênio
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td>
            <input type='text' name='txt_icms[]' value='<?=number_format($campos[$i]['icms'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='7' class='caixadetexto'> %
        </td>
        <td>
            <input type='text' name='txt_reducao[]' value='<?=number_format($campos[$i]['reducao'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='7' class='caixadetexto'> %
        </td>
        <td>
            <input type='text' name='txt_icms_intraestadual[]' value='<?=number_format($campos[$i]['icms_intraestadual'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='7' class='caixadetexto'> %
        </td>
        <td>
            <input type='text' name='txt_fecp[]' value='<?=number_format($campos[$i]['fecp'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='7' class='caixadetexto'> %
        </td>
        <td>
            <input type='text' name='txt_iva[]' value='<?=number_format($campos[$i]['iva'], 2, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" maxlength='6' size='7' class='caixadetexto'> %
        </td>
        <td align='left'>
            <?=str_replace('?', number_format($campos[$i]['reducao'], 2, ',', '.'), $texto_reducao_governo);?>
        </td>
        <td>
            <a href = '../ufs_convenios/alterar.php?id_uf=<?=$campos[$i]['id_uf'];?>&pop_up=1' class='html5lightbox'>
                <?=$campos[$i]['convenio'];?>
            </a>
            <input type='hidden' name='hdd_icms[]' value='<?=$campos[$i]['id_icms']?>'>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
        <?
            //Às vezes essa tela é aberta como sendo Pop-Up, por isso que tenho esse controle ...
            if(empty($_GET['pop_up'])) {
        ?>  
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>&passo=1'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        <?
            }
        ?>
            &nbsp;
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<br/>
<font color='red'>
    *** O CERTO ERA TERMOS TODAS AS ALÍQUOTAS INTRAESTADUAIS COMO SENDO 18%.

    <p/>* PORÉM NO ESTADO DE SÃO TANTO AS ALÍQUOTAS INTERESTADUAIS QUANTO AS ALÍQUOTAS INTRAESTADUAIS TEM DE SEREM IGUAIS, O QUE JUSTIFICA 
    ENTÃO DE TERMOS ALGUMAS NO VALOR DE 12%.
</font>
</body>
</html>
<?
}else if($passo == 3) {
    foreach($_POST['hdd_icms'] as $i => $id_icms) {
        $sql = "UPDATE `icms` SET  `icms` = '".$_POST['txt_icms'][$i]."', `reducao` = '".$_POST['txt_reducao'][$i]."', `icms_intraestadual` = '".$_POST['txt_icms_intraestadual'][$i]."', `fecp` = '".$_POST['txt_fecp'][$i]."', `iva` = '".$_POST['txt_iva'][$i]."' WHERE `id_icms` = '$id_icms' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=3'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar ICMS / IVA ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function controlar_objetos() {
    if(document.form.opt_opcao[0].checked == true) {//Classificação Fiscal ...
        //Habilitando objetos ...
        document.form.cmb_classific_fiscal.className                = 'combo'
        document.form.cmb_classific_fiscal.disabled                 = false
        //Desabilitando objetos ...
        document.form.cmb_uf.className                              = 'textdisabled'
        document.form.cmb_uf.value                                  = ''
        document.form.cmb_uf.disabled                               = true
        document.form.chkt_pa_comercializado_pelo_grupo.disabled    = true
    }else {//UF ...
        //Habilitando objetos ...
        document.form.cmb_uf.className                              = 'combo'
        document.form.cmb_uf.disabled                               = false
        document.form.chkt_pa_comercializado_pelo_grupo.disabled    = false
        //Desabilitando objetos ...
        document.form.cmb_classific_fiscal.className                = 'textdisabled'
        document.form.cmb_classific_fiscal.value                    = ''
        document.form.cmb_classific_fiscal.disabled                 = true
    }
}

function validar() {
    if(document.form.opt_opcao[0].checked == true) {//Classificação Fiscal ...
        if(!combo('form', 'cmb_classific_fiscal', '', 'SELECIONE UMA CLASSIFICAÇÃO FISCAL !')) {
            return false
        }
        document.form.id_classific_fiscal.value = document.form.cmb_classific_fiscal.value
        document.form.passo.value               = 2
        document.form.action                    = '<?=$PHP_SELF.'?passo=2';?>'
    }else {//UF ...
        if(!combo('form', 'cmb_uf', '', 'SELECIONE UMA UF !')) {
            return false
        }
        document.form.passo.value               = 1
        document.form.action                    = '<?=$PHP_SELF.'?passo=1';?>'
    }
}
</Script>
</head>
<body>
<form name='form' method='post' onsubmit='return validar()'>
<!--*******************Controle de Tela*******************-->
<!--Eu criei esse hidden para que no Passo 2, chegue a variável "id_classific_fiscal", poderia passar a combo direto, 
mas no passo 1 se clicar em cima do link também leva um parâmetro de "id_classific_fiscal" p/ o passo 2 ...-->
<input type='hidden' name='id_classific_fiscal'>
<input type='hidden' name='passo'>
<!--******************************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar ICMS / IVA
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='opt_opcao1' value='1' title='Selecione uma Opção' onclick='controlar_objetos()' checked>
            <label for='opt_opcao1'>
                Classificação Fiscal
            </label>
            &nbsp;
            <select name='cmb_classific_fiscal' title='Selecione uma Classificação Fiscal' class='combo'>
            <?
                $sql = "SELECT id_classific_fiscal, classific_fiscal 
                        FROM `classific_fiscais` 
                        WHERE `ativo` = '1' ORDER BY classific_fiscal ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' id='opt_opcao2' value='2' title='Selecione uma Opção' onclick='controlar_objetos()'>
            <label for='opt_opcao2'>
                UF
            </label>
            &nbsp;
            <select name='cmb_uf' title='Selecione uma UF' class='textdisabled' disabled>
            <?
                $sql = "SELECT id_uf, sigla 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY sigla ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            <input type='checkbox' name='chkt_pa_comercializado_pelo_grupo' value='S' id='pa_comercializado_pelo_grupo' class='checkbox' checked disabled>
            <label for='pa_comercializado_pelo_grupo'>
                <font color='red'>
                    <b>PA(s) comercializado(s) pelo Grupo</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='limpar();document.form.txt_classificacao_fiscal.focus() ' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>