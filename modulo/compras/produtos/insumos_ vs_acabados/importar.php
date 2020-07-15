<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PRODUTO ACABADO INCLUIDO PARA PRODUTO INSUMO COM SUCESSO.</font>";

if($passo == 1) {
    //Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $opt_opcao                  = $_POST['opt_opcao'];
        $txt_consultar              = $_POST['txt_consultar'];
        $chkt_mostrar_importados    = $_POST['chkt_mostrar_importados'];
    }else {
        $opt_opcao                  = $_GET['opt_opcao'];
        $txt_consultar              = $_GET['txt_consultar'];
        $chkt_mostrar_importados    = $_GET['chkt_mostrar_importados'];	
    }
    //Aqui significa que o usuário deseja visualizar somente os PA(s) que já foram importados como PIPA ...
    if(!empty($chkt_mostrar_importados)) {
        $condicao_importados    = " AND `id_produto_insumo` > '0' ";
    }else {
        //Aqui eu nessa condicao eu ignoro todos os PA's que sao PI's ...
        $condicao_nao_importados = " AND pa.`id_produto_acabado` NOT IN 
                                    (SELECT id_produto_acabado 
                                    FROM `produtos_acabados` 
                                    WHERE `id_produto_insumo` > '0') "; 
    }
    
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
                    ed.`razaosocial`, gpa.`nome` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                    WHERE pa.`referencia` LIKE '%$txt_consultar%' 
                    AND pa.`ativo` = '1' 
                    $condicao_importados 
                    $condicao_nao_importados ORDER BY pa.referencia, pa.discriminacao ";
        break;
        case 2:
            $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
                    ed.`razaosocial`, gpa.`nome` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                    WHERE pa.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pa.`ativo` = '1' 
                    $condicao_importados 
                    $condicao_nao_importados ORDER BY pa.referencia, pa.discriminacao ";
        break;
        case 3:
            $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
                    ed.`razaosocial`, gpa.`nome` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao AND (gpa.`nome` LIKE '%$txt_consultar%' OR ed.`razaosocial` LIKE '%$txt_consultar%') 
                    WHERE pa.`ativo` = '1' 
                    $condicao_importados 
                    $condicao_nao_importados ORDER BY pa.referencia, pa.discriminacao ";
        break;
        default:
            $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, 
                    ed.`razaosocial`, gpa.`nome` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                    WHERE pa.`ativo` = '1' 
                    $condicao_importados 
                    $condicao_nao_importados ORDER BY pa.referencia, pa.discriminacao ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 500, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'importar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Importar Produto(s) Acabado(s) para Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Importar Produto(s) Acabado(s) para Produto(s) Insumo(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Empresa Divisão
        </td>
        <td>
            <label for='todos'>Todos </label>
            <input type="checkbox" name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox' id='todos'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" class='linhanormal'>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td>
            <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
        </td>
        <td align='center'>
        <?
            //Aqui eu verifico se o PA do Loop já é um PIPA ...
            $sql = "SELECT id_produto_insumo 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `id_produto_insumo` > '0' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
            if(count($campos_pipa) == 0) {//Ainda não é PIPA ...
?>            
                <input type='checkbox' name='chkt_produto_acabado[]' value="<?=$campos[$i]['id_produto_acabado'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
<?
            }else {//É PIPA ...
?>
                <input type='hidden' name='chkt_produto_acabado[]' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>Importado
<?
            }
?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'importar.php'" class='botao'>
            <input type='submit' name='cmd_importar' value='Importar' title='Importar' class='botao'>
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
}else if($passo == 2) {
    foreach($_POST['chkt_produto_acabado'] as $id_produto_acabado) intermodular::importar_patopi($id_produto_acabado);//Aqui é a função que importa o PA para PI ...
?>
    <Script Language = 'Javascript'>
        window.location = 'importar.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Importar Produto(s) Acabado(s) para Produto(s) Insumo(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 3;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
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
<body onLoad='document.form.txt_consultar.focus()'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Importar Produto(s) Acabado(s) para Produto(s) Insumo(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength='45' class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type="radio" name="opt_opcao" value="1" onclick='document.form.txt_consultar.focus()' title="Consultar Produtos Acabados por: Referência" id='label'>
            <label for='label'>
                Referência PA
            </label>
        </td>
        <td width='20%'>
            <input type="radio" name="opt_opcao" value="2" onclick='document.form.txt_consultar.focus()' title="Consultar Produtos Acabados por: Discriminação" id='label2' checked>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
                <input type="radio" name="opt_opcao" value="3" onclick='document.form.txt_consultar.focus()' title="Consultar Produtos Acabados por: Grupo P.A. / Empresa Divisão" id='label3'>
                <label for='label3'>
                        Grupo P.A. / Empresa Divisão
                </labe>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os Produtos Acabados" onclick='limpar()' id='label4' class="checkbox">
            <label for='label4'>
                Todos os registros
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_mostrar_importados' value='1' title='Mostrar Importado(s)' id='label5' class="checkbox">
            <label for='label5'>
                Mostrar Importado(s)
            </labe>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar()" style="color:#ff9900;" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Só mostra PA(s) OC = Revenda.
</pre>
<?}?>