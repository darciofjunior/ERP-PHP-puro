<?
require('../../../lib/segurancas.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/ops/alterar.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_referencia     = $_POST['txt_referencia'];
        $txt_discriminacao  = $_POST['txt_discriminacao'];
        $id_pa_substituir   = $_POST['id_pa_substituir'];
        $id_op              = $_POST['id_op'];
    }else {
        $txt_referencia     = $_GET['txt_referencia'];
        $txt_discriminacao  = $_GET['txt_discriminacao'];
        $id_pa_substituir   = $_GET['id_pa_substituir'];
        $id_op              = $_GET['id_op'];
    }

    $sql = "SELECT ed.`razaosocial`, gpa.`nome`, pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, pa.`ativo` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
            WHERE pa.`referencia` LIKE '%$txt_referencia%' 
            AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
            AND (pa.`operacao` < '9' AND pa.`operacao_custo` < '9') ORDER BY pa.`discriminacao` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar_pa_op.php?id_pa_substituir=<?=$id_pa_substituir;?>&id_op=<?=$id_op;?>&valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Produto(s) Acabado(s) p/ Alterar PA da OP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function avancar(id_produto_acabado, operacao_custo, id_pa_substituir, id_op) {
    if(operacao_custo == 1) {//Quando a Operação de Custo o PA. Revenda ...
        alert('A OPERAÇÃO DE CUSTO DESSE PA É DO TIPO REVENDA !')
    }
    window.location = 'alterar_pa_op.php?passo=2&id_produto_acabado='+id_produto_acabado+'&id_pa_substituir='+id_pa_substituir+'&id_op='+id_op
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Produto(s) Acabado(s) p/ Alterar PA da OP
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Referência
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Grupo P.A. / Empresa Divisão
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = "javascript:avancar('".$campos[$i]['id_produto_acabado']."', '".$campos[$i]['operacao_custo']."', '".$id_pa_substituir."', '".$id_op."')";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$url;?>">
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['referencia'];?>
            </a>
        </td>
        <td>
        <?
            echo intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
//Se o PI já foi excluído, então o sistema mostra essa identificação
            if($campos[$i]['ativo'] == 0) echo '<font color="red"><b> (EXCLUÍDO) </b></font>';
        ?>
        </td>
        <td>
            <?=$campos[$i]['nome'].' / '.$campos[$i]['razaosocial'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar_pa_op.php?id_pa_substituir=<?=$id_pa_substituir;?>&id_op=<?=$id_op;?>'" class='botao'>
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
}else if($passo == 2) {
    /**************Procedimentos à serem feitos**************/
    /*1) Mudo o "id_produto_acabado" da(s) Baixa(s) Manipulação(ões) que foi(ram) feita(s) 
    anteriormente(s) para a OP independente da ação ...*/
    $sql = "SELECT bmp.`id_baixa_manipulacao_pa` 
            FROM `baixas_manipulacoes_pas` bmp 
            INNER JOIN `baixas_ops_vs_pas` bop ON bop.`id_baixa_manipulacao_pa` = bmp.`id_baixa_manipulacao_pa` AND bop.`id_op` = '$_GET[id_op]' ";
    $campos_baixas_manipulacoes = bancos::sql($sql);
    $linhas_baixas_manipulacoes = count($campos_baixas_manipulacoes);
    for($i = 0; $i < $linhas_baixas_manipulacoes; $i++) {
        //Procedimento normal para registro da Entrada ...
        $sql = "UPDATE `baixas_manipulacoes_pas` SET `id_produto_acabado` = '$_GET[id_produto_acabado]' WHERE `id_baixa_manipulacao_pa` = '".$campos_baixas_manipulacoes[$i]['id_baixa_manipulacao_pa']."' LIMIT 1 ";
        bancos::sql($sql);
        //************************Novo Controle com a Parte de OP(s)************************
        $sql = "UPDATE `baixas_ops_vs_pas` SET `id_produto_acabado` = '$_GET[id_produto_acabado]' WHERE `id_baixa_manipulacao_pa` = '".$campos_baixas_manipulacoes[$i]['id_baixa_manipulacao_pa']."' LIMIT 1 ";
        bancos::sql($sql);
    }
    //2) Mudo o "id_produto_acabado" da OP conforme o que foi solicitado pelo Usuário ...
    $sql = "UPDATE `ops` SET `id_produto_acabado` = '$_GET[id_produto_acabado]' WHERE `id_op` = '$_GET[id_op]' LIMIT 1 ";
    bancos::sql($sql);
    
    estoque_acabado::atualizar_producao($_GET['id_pa_substituir']);
    estoque_acabado::atualizar_producao($_GET['id_produto_acabado']);
    /********************************************************/
?>
    <Script Language = 'JavaScript'>
        parent.document.location = 'alterar.php?passo=1&id_op=<?=$_GET['id_op'];?>'
        parent.html5Lightbox.finish()
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Acabado(s) p/ Alterar PA da OP ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_referencia.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_pa_substituir' value='<?=$_GET['id_pa_substituir'];?>'>
<input type='hidden' name='id_op' value='<?=$_GET['id_op'];?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Acabado(s) p/ Alterar PA da OP
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' size='35' maxlength='300' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' size='50' maxlength='100' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_referencia.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>