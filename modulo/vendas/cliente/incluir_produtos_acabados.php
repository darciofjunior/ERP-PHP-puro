<?
require('../../../lib/segurancas.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/vs_produtos_acabados.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PA(S) INCLUÍDO(S) COM SUCESSO P/ ESTE CLIENTE.</font>";
$mensagem[3] = "<font class='confirmacao'>ALGUM(NS) PA(S) FORAM INCLUÍDO(S) E OUTRO(S) JÁ EXISTIA(M) P/ ESTE CLIENTE.</font>";
$mensagem[4] = "<font class='erro'>PA(S) JÁ EXISTENTE(S) P/ ESTE CLIENTE.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_cliente             = $_POST['id_cliente'];
        $chkt_mostrar_especiais = $_POST['chkt_mostrar_especiais'];
        $txt_referencia         = $_POST['txt_referencia'];
        $txt_discriminacao      = $_POST['txt_discriminacao'];
    }else {
        $id_cliente             = $_GET['id_cliente'];
        $chkt_mostrar_especiais = $_GET['chkt_mostrar_especiais'];
        $txt_referencia         = $_GET['txt_referencia'];
        $txt_discriminacao      = $_GET['txt_discriminacao'];
    }
/*Aqui eu tenho esse Tratamento devido com o % e |, devido o usuário utilizar o % 
como caracter ...*/
    $txt_discriminacao = str_replace('|', '%', $txt_discriminacao);
//Se essa opção estiver desmarcada, então eu só mostro os P.A(s) que são do Tipo normais de Linha ...
    if(empty($chkt_mostrar_especiais)) $condicao_esp = " AND pa.`referencia` <> 'ESP' ";

    $sql = "SELECT pa.id_produto_acabado 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
            INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
            WHERE pa.`referencia` LIKE '%$txt_referencia%' 
            AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
            AND gpa.`id_familia` <> '23' 
            AND pa.`ativo` = '1' 
            $condicao_esp ORDER BY pa.referencia ";
    $campos = bancos::sql($sql, $inicio, 15, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir_produtos_acabados.php?id_cliente=<?=$id_cliente;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Produto(s) Acabado(s) p/ Incluir p/ Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        document.form.nao_atualizar.value = 1
        return true
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Produto(s) Acabado(s) p/ Incluir p/ Cliente
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Todos' class='checkbox'>
        </td>
        <td>
            Produto Acabado
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_produto_acabado[]' value="<?=$campos[$i]['id_produto_acabado'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos[$i]['id_produto_acabado'], 0, '', '');?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir_produtos_acabados.php?id_cliente=<?=$id_cliente;?>'" class='botao'>
            <input type="submit" name="cmd_incluir" value="Incluir" title="Incluir" style="color:green" class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='nao_atualizar'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
	}
}else if($passo == 2) {
    for($i = 0; $i < count($_POST['chkt_produto_acabado']); $i++) {
//Verifico se o Cliente selecionado já está atrelado p/ o Produto Acabado em questão ...
        $sql = "SELECT id_pa_cod_cliente 
                FROM `pas_cod_clientes` 
                WHERE `id_cliente` = '$_POST[id_cliente]' 
                AND `id_produto_acabado` = '".$_POST['chkt_produto_acabado'][$i]."' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {
            $insert_extend.= " ('".$_POST['chkt_produto_acabado'][$i]."', '$_POST[id_cliente]'), ";
            $incluidos++;
        }else {
            $nao_incluidos++;
        }
    }

    if(!empty($insert_extend)) {
        $insert_extend = substr($insert_extend, 0, strlen($insert_extend) - 2).';';
        $sql = "INSERT INTO `pas_cod_clientes` (`id_produto_acabado`, `id_cliente`) VALUES ".$insert_extend;
        bancos::sql($sql);
    }
/*********************Controle p/ Retornar a Mensagem*********************/	
//Significa que todo(s) o(s) PA(s) foram incluidos com sucesso p/ o Cliente	...
    if($incluidos != 0 && $nao_incluidos == 0) {
        $valor = 2;
//Significa que alguns PA(s) foram incluídos e outros não devido já existir o(s) mesmo(s) p/ o Cliente ...
    }else if($incluidos != 0 && $nao_incluidos != 0) {
        $valor = 3;
//Significa que nenhum PA foi incluído p/ o Cliente devido estes já existirem ...
    }else if($incluidos == 0 && $nao_incluidos != 0) {
        $valor = 4;
    }
/*************************************************************************/
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir_produtos_acabados.php?id_cliente=<?=$_POST['id_cliente'];?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Acabado(s) p/ Incluir p/ Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_referencia.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_cliente' value='<?=$_GET['id_cliente'];?>'>
<input type='hidden' name='nao_atualizar'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Acabado(s) p/ Incluir p/ Cliente
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' size='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_mostrar_especiais' value='1' title="Mostrar Especiais" id='label1' class='checkbox'>
            <label for='label1'>
                Mostrar Especiais
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_referencia.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' onclick='document.form.nao_atualizar.value = 1' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>