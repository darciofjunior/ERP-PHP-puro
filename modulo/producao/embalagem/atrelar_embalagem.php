<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/embalagem/embalagem.php', '../../../');

$mensagem[1] = '<font class="erro">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">EMBALAGEM(NS) INCLUÍDA(S) COM SUCESSO PARA P.A.</font>';
$mensagem[3] = '<font class="erro">EMBALAGEM(NS) JÁ EXISTENTE(S) ESTE PARA P.A.</font>';

if($passo == 1) {
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_referencia     = $_POST['txt_referencia'];
        $txt_discriminacao  = $_POST['txt_discriminacao'];
    }else {
        $txt_referencia     = $_GET['txt_referencia'];
        $txt_discriminacao  = $_GET['txt_discriminacao'];
    }
/*Aqui eu tenho esse Tratamento devido com o % e |, devido o usuário utilizar o % 
como caracter ...*/
    $txt_discriminacao = str_replace('|', '%', $txt_discriminacao);

    $sql = "SELECT g.referencia, pi.id_produto_insumo, pi.discriminacao 
            FROM `produtos_insumos` pi 
            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo AND g.`referencia` LIKE '%$txt_referencia%' 
            WHERE pi.`discriminacao` LIKE '%$txt_discriminacao%' 
            AND pi.`ativo` = '1' ORDER BY g.referencia, pi.discriminacao ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'atrelar_embalagem.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Atrelar Embalagem(ns) ::.</title>
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
        return true
    }
//P/ não atualizar a Tela de Baixo ...
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.recarregar_tela()
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Atrelar Embalagem(ns)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar todos' onclick="selecionar_especial('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_produto_insumo[]' value="<?=$campos[$i]['id_produto_insumo'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class="linhacabecalho" align="center">
        <td colspan='3'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'atrelar_embalagem.php?id_produto_acabado=<?=$id_produto_acabado;?>'" class='botao'>
            <input type='submit' name='cmd_atrelar' value='Atrelar' title='Atrelar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='nao_atualizar'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {//Inserção das Embalagens vs Produtos Acabados
//Aqui eu verifico a Operação de Custo do PA ...
    $sql = "SELECT operacao_custo 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
    $campos_operacao_custo  = bancos::sql($sql);
    $operacao_custo         = $campos_operacao_custo[0]['operacao_custo'];
/******************************************************************************************/
/******************************************Custo*******************************************/
/******************************************************************************************/
//Aqui eu verifico se já existe Custo p/ esse PA ...
    $sql = "SELECT id_produto_acabado_custo 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' 
            AND `operacao_custo` = '$operacao_custo' LIMIT 1 ";
    $campos_custo = bancos::sql($sql);
    if(count($campos_custo) == 0) {//Se não existe Custo p/ o PA, crio um de acordo com a sua Operação de Custo ...
        $sql = "INSERT INTO `produtos_acabados_custos` (`id_produto_acabado_custo`, `id_produto_acabado`, `id_funcionario`, `operacao_custo`, `data_sys`) VALUES (NULL, '$_POST[id_produto_acabado]', '$_SESSION[id_login]', '$operacao_custo', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
/******************************************************************************************/
//Verifica se já existe pelo menos uma embalagem atrelada p/ este Produto Acabado ...
    $sql = "SELECT COUNT(id_pa_pi_emb) AS total_embalagens 
            FROM `pas_vs_pis_embs` 
            WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' ";
    $campos_embalagens  = bancos::sql($sql);
    $total_embalagens   = $campos_embalagens[0]['total_embalagens'];
//Atrelando as Embalagens p/ o Produto Acabado ...
    foreach($_POST['chkt_produto_insumo'] as $i => $id_produto_insumo) {
//Verifico se já essa Embalagem já esta atrelada p/ esse PA ...
        $sql = "SELECT id_pa_pi_emb 
                FROM `pas_vs_pis_embs` 
                WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' 
                AND `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {
            //Significa que não existe nenhuma Embalagem Default p/ esse PA ...
            if($i == 0 && $total_embalagens == 0) {
                $embalagem_default = 1;
            }else {//Demais embalagens q estão sendo inseridas
                $embalagem_default = 0;
            }
            $sql = "INSERT INTO `pas_vs_pis_embs` (`id_pa_pi_emb`, `id_produto_acabado`, `id_produto_insumo`, `embalagem_default`) VALUES (NULL, '$_POST[id_produto_acabado]', '$id_produto_insumo', '$embalagem_default') ";
            bancos::sql($sql);
            $valor = 2;
        }else {
            $valor = 3;
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'atrelar_embalagem.php?id_produto_acabado=<?=$id_produto_acabado;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Atrelar Embalagem(ns) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.recarregar_tela()
}
</Script>
</head>
<body onload='document.form.txt_referencia.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='id_produto_acabado' value='<?=$_GET['id_produto_acabado'];?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Atrelar Embalagem(ns)
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