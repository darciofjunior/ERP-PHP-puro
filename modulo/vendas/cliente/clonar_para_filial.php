<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/vendas/cliente/vs_produtos_acabados.php', '../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) FILIAL(IS) PARA ESTE CLIENTE.</font>";
$mensagem[2] = "<font class='erro'>PA(S) JÁ EXISTENTE(S) P/ ESTE(S) CLIENTE(S).</font>";
$mensagem[3] = "<font class='confirmacao'>ALGUM(NS) PA(S) FORAM CLONADO(S), OUTRO(S) EXISTIA(M) P/ ESTE(S) CLIENTE(S) E FOI(RAM) SUBSTITUÍDO(S) O(S) CÓDIGO(S) DE PA(S).</font>";
$mensagem[4] = "<font class='erro'>PA(S) JÁ EXISTENTE(S) P/ ESTE(S) CLIENTE(S) MAS FOI(RAM) SUBSTITUÍDO(S) O(S) CÓDIGO(S) DE PA(S).</font>";

if(!empty($_POST['chkt_cliente'])) {
    //Busca de todos os PA(s) do Cliente Matriz que serão clonados ...
    $sql = "SELECT pcc.id_produto_acabado, pcc.cod_cliente 
            FROM `pas_cod_clientes` pcc 
            WHERE pcc.id_cliente = '$_POST[id_cliente]' ";
    $campos_pas_matriz = bancos::sql($sql);
    $linhas_pas_matriz = count($campos_pas_matriz);
    for($i = 0; $i < $linhas_pas_matriz; $i++) {
        for($j = 0; $j < count($_POST['chkt_cliente']); $j++) {
//Verifico se o PA que está sendo clonado já existe para o Cliente Filial em Questão ...
            $sql = "SELECT id_pa_cod_cliente 
                    FROM `pas_cod_clientes` 
                    WHERE `id_cliente` = '".$_POST['chkt_cliente'][$j]."' 
                    AND `id_produto_acabado` = '".$campos_pas_matriz[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {
                $insert_extend.= " ('".$campos_pas_matriz[$i]['id_produto_acabado']."', '".$_POST['chkt_cliente'][$j]."', '".$campos_pas_matriz[$i]['cod_cliente']."'), ";
                $incluidos++;
            }else {
                //Aqui eu atualizo os Códigos dos Produtos do Cliente Matriz para o Cliente Filial ...
                $sql = "UPDATE `pas_cod_clientes` SET `cod_cliente` = '".$campos_pas_matriz[$i]['cod_cliente']."' WHERE `id_cliente` = '".$_POST['chkt_cliente'][$j]."' AND `id_produto_acabado` = '".$campos_pas_matriz[$i]['id_produto_acabado']."' LIMIT 1 ";
                bancos::sql($sql);
                $nao_incluidos++;
            }
        }
    }

    if(!empty($insert_extend)) {
        $insert_extend = substr($insert_extend, 0, strlen($insert_extend) - 2).'; ';
        $sql = "INSERT INTO `pas_cod_clientes` (`id_produto_acabado`, `id_cliente`, `cod_cliente`) VALUES ".$insert_extend;
        bancos::sql($sql);
    }
/*********************Controle p/ Retornar a Mensagem*********************/	
//Significa que todo(s) o(s) PA(s) foram clonados com sucesso p/ o(s) Cliente(s) ...
    if($incluidos != 0 && $nao_incluidos == 0) {
        $valor = 2;
//Significa que alguns PA(s) foram clonados e outros não devido já existir o(s) mesmo(s) p/ o Cliente ...
    }else if($incluidos != 0 && $nao_incluidos != 0) {
        $valor = 3;
//Significa que nenhum PA foi clonado p/ nenhum Cliente devido estes já existirem ...
    }else if($incluidos == 0 && $nao_incluidos != 0) {
        $valor = 4;
    }
/*************************************************************************/
}

$id_cliente = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_cliente'] : $_GET['id_cliente'];

//Busca do Nome do Cliente Matriz ...
$sql = "SELECT if(nomefantasia = '', razaosocial, nomefantasia) as cliente 
        FROM `clientes` 
        WHERE `id_cliente_matriz` = '$id_cliente' LIMIT 1 ";
$campos_cliente_matriz = bancos::sql($sql);
if(count($campos_cliente_matriz) == 1) {//Significa que este Cliente realmente é o Cliente Matriz ...
    $cliente_matriz = $campos_cliente_matriz[0]['cliente'];
//Busca das Filiais do Cliente Matriz ...
    $sql = "SELECT id_cliente AS id_cliente_filial 
            FROM `clientes` 
            WHERE `id_cliente_matriz` = '$id_cliente' ";
    $campos_filiais = bancos::sql($sql);
    $linhas_filiais = count($campos_filiais);
    for($i = 0; $i < $linhas_filiais; $i++) $id_clientes_filiais.= $campos_filiais[$i]['id_cliente_filial'].', ';
    $id_clientes_filiais = substr($id_clientes_filiais, 0, strlen($id_clientes_filiais) - 2);
//Aqui eu busco todos os Clientes Filiais do Cliente Matriz ...
    $sql = "SELECT id_cliente, if(nomefantasia = '', razaosocial, nomefantasia) as cliente 
            FROM `clientes` 
            WHERE `id_cliente` IN ($id_clientes_filiais) ORDER BY cliente ";

    $sql_extra = "SELECT COUNT(DISTINCT(id_cliente)) AS total_registro 
                    FROM `clientes` 
                    WHERE `id_cliente` IN ($id_clientes_filiais) ";
    $campos = bancos::sql($sql, $inicio, 10, 'sim', $pagina);
    $linhas = count($campos);
}else {//Significa que o Cliente acessado não é o Matriz ...
    $linhas = 0;//Sendo assim não existem Filiais ...
}
?>
<html>
<head>
<title>.:: Clonar p/ Cliente(s) Filial(is) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
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
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    if($linhas == 0) {//Não encontrou nenhuma Filial p/ o Cliente passado por parâmetro ...
?>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
    }else {//Encontrou pelo menos 1 Filial ...
?>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Cliente Matriz: 
            <font color='yellow'>
                <?=$cliente_matriz;?>
            </font>
            <br/>Clonar p/ Cliente(s) Filial(is)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Todos' class='checkbox'>
        </td>
        <td>
            Cliente(s) Filial(is)
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_cliente[]' value='<?=$campos[$i]['id_cliente'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_atrelar' value='Atrelar' title='Atrelar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<!--Equivale o id do Cliente Matriz-->
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</body>
</html>