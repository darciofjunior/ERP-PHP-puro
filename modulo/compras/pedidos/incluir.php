<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>PEDIDO N.º '.$id_pedido.' INCLUIDO COM SUCESSO.</font>";

//Verifica se tem um Pedido em aberto daquele fornecedor selecionado
function pedidos_abertos($id_fornecedor) {
/******************Todo esse procedimento é para ver se existe algum pedido em Branco, caso existir
eu vou reaproveitar esse pedido mesmo ao invés de Gerar um Pedido******************/
//Aki verifico todos os pedidos do Fornecedor que contém pelo menos 1 item ...
    $sql = "SELECT DISTINCT(p.`id_pedido`) 
            FROM `pedidos` p 
            INNER JOIN `itens_pedidos` ip ON ip.`id_pedido` = p.`id_pedido` 
            WHERE p.`id_fornecedor` = '$id_fornecedor' 
            AND p.`status` < '2' 
            AND p.`ativo` = '1' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
//Dispara outro For
    $id_pedidos = '';
    for($i = 0; $i < $linhas; $i++) $id_pedidos.= $campos[$i]['id_pedido'].',';
//Esse macete é para forçar a entrar no sql da linha 46
    if(strlen($id_pedidos) == 0) $id_pedidos = '0,';
    $id_pedidos = substr($id_pedidos, 0, strlen($id_pedidos) - 1);
//Se foi encontrado mais de 2 pedidos, caso isso vai refletir na condição do Sql mais abaixo
    if(strpos($id_pedidos, ',') > 0) {//Se existir vírgula, então significa q tem no mínimo 2 notas
        $tipo_comparacao = ' NOT IN ('.$id_pedidos.') ';
    }else {
        $tipo_comparacao = ' <> '.$id_pedidos;
    }
//Aki verifico todos os pedidos do Fornecedor que estão em aberto e q não possuem nenhum item ...
    $sql = "SELECT id_pedido 
            FROM `pedidos` 
            WHERE `id_fornecedor` = '$id_fornecedor' 
            AND `id_pedido` $tipo_comparacao 
            AND `status` < '2' 
            AND `ativo` = '1' ORDER BY id_pedido LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Retorno o Primeiro pedido que está em aberto e que foi encontrado ...
/*Como esse pedido vai ser reaproveitado, aqui eu já atualizo a Data de Emissão desse Pedido antigo p/ 
uma Data mais recente, a Data Atual do dia ...*/
        $data_sys = date('Y-m-d H:i:s');
        $sql = "UPDATE `pedidos` SET `data_emissao` = '$data_sys' WHERE `id_pedido` = ".$campos[0]['id_pedido']." LIMIT 1 ";
        bancos::sql($sql);
        return $campos[0]['id_pedido'];
    }else {
        return 0;
    }
}

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` LIKE '%$txt_consultar%' 
                    AND `ativo` = '1' 
                    AND `razaosocial` <> '' ORDER BY `razaosocial` ";
        break;
        default:
            $sql = "SELECT `id_fornecedor`, `cnpj_cpf`, `razaosocial`, `bairro`, `cep`, `cidade`, `endereco` 
                    FROM `fornecedores` 
                    WHERE `ativo` = '1' 
                    AND `razaosocial` <> '' ORDER BY `razaosocial` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language= 'Javascript'>
            window.location = 'incluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Incluir Pedido - Fornecedor(es) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function avancar(id_pedido_existente, id_fornecedor) {
//Se existir algum Pedido em Aberto, então o Sistema informa ao usuário p/ que ele utilize esse Pedido que está em aberto ...
    if(id_pedido_existente != 0) {
        var pergunta = confirm('O PEDIDO N.º '+id_pedido_existente+' DESSE FORNECEDOR ESTÁ EM ABERTO E NÃO POSSUI ITEM(NS) !!!\nDESEJA IR PARA ESTE PEDIDO ?')
        if(pergunta == true) {
            window.location = 'itens/index.php?id_pedido='+id_pedido_existente
        }else {
            return false
        }
    }else {
        var pergunta = confirm('TEM CERTEZA DE QUE DESEJA GERAR UM NOVO PEDIDO ?')
        if(pergunta == true) {
            window.location = 'incluir.php?passo=2&id_fornecedor='+id_fornecedor
        }else {
            return false
        }
    }
}
</Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)" align='center'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Incluir Pedido - Fornecedor(es)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Razão Social
        </td>
        <td>
            CNPJ / CPF
        </td>
        <td>
            Endereço
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
//Tratamento com o CNPJ ...
            $cnpj = $campos[$i]['cnpj'];
            $cnpj = substr($cnpj, 0, 2).'.'.substr($cnpj, 2, 3).'-'.substr($cnpj, 5, 3).'/'.substr($cnpj, 8, 4).'-'.substr($cnpj, 12, 2);
            if($cnpj == '00.000-000/0000-00') $cnpj = '';
            $id_pedido_existente = pedidos_abertos($campos[$i]['id_fornecedor']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td onclick="avancar('<?=$id_pedido_existente;?>', '<?=$campos[$i]['id_fornecedor'];?>')">
            <a style='cursor:pointer' class='link'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                <?=$campos[$i]['razaosocial'];?>
            </a>
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
        <td>
        <?
            if(!empty($endereco)) echo $campos[$i]['endereco'];
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
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
//Busca id_pais do Fornecedor ...
    $sql = "SELECT `id_pais` 
            FROM `fornecedores` 
            WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
    $campos 	= bancos::sql($sql);
    $id_pais 	= $campos[0]['id_pais'];
//Aqui eu busco nas variáveis qual é a Preferência de Compra p/ este Fornecedor ...
    $sql = "SELECT `valor` 
            FROM `variaveis` 
            WHERE `id_variavel` = '6' LIMIT 1 ";
    $campos = bancos::sql($sql);
    //A empresa também é a própria Preferência de Compra ...
    $empresa = intval($campos[0]['valor']);
    if($empresa == 1 || $empresa == 2) {//Se a Empresa for Alba ou Tool ...
        $tipo_pedido = 1;
    }else {//Se a Empresa for Grupo ...
        $tipo_pedido = 2;
    }
    $tipo_nota_porc = '100%';//Porcentagem da NFE ...
//Se o Fornecedor for do Brasil ...
    if($id_pais == 31) {
        $tipo_exportacao = 'N';//Nacional
        $tipo_moeda = 1;//Real
//Estrangeiro ...
    }else {
        $tipo_exportacao = 'I';//Importação
        $tipo_moeda = 2;//Dólar
    }
    //O Sistema busca qual foi o vendedor do último pedido deste Fornecedor ...
    $sql = "SELECT `vendedor` 
            FROM `pedidos` 
            WHERE `id_fornecedor` = '$_GET[id_fornecedor]' ORDER BY `id_pedido` DESC LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) $vendedor = $campos[0]['vendedor'];
    $prazo_entrega 	= data::datatodate(data::adicionar_data_hora(date('d-m-Y'), 0), '-');
    //Se o Fornecedor for Estrangeiro ...
    if($id_pais != 31) $prazo_viagem_navio = 0;
    $data_sys = date('Y-m-d H:i:s');
    $sql = "INSERT INTO `pedidos` (`id_pedido`, `id_empresa`, `id_tipo_moeda`, `tipo_nota_porc`, `vendedor` ,`tp_moeda`, `desc_ddl`, `desconto_especial_porc`, `prazo_pgto_a`, `prazo_pgto_b`, `prazo_pgto_c` , `prazo_entrega`, `prazo_navio`, `tipo_nota`, `tipo_export`, `id_funcionario_cotado` , `id_fornecedor`, `data_emissao`, `ativo`, `status`) VALUES (NULL, '$empresa', '$tipo_moeda', '$tipo_nota_porc', '$vendedor', '$tipo_moeda', '', '', '','','', '$prazo_entrega', '$prazo_viagem_navio', '$tipo_pedido', '$tipo_exportacao', '$_SESSION[id_funcionario]', '$_GET[id_fornecedor]', '$data_sys', '1', '1') ";
    bancos::sql($sql);
    $id_pedido = bancos::id_registro();
?>
    <Script Language = 'JavaScript'>
        alert('NOVO PEDIDO N.º '+<?=$id_pedido;?>+' GERADO COM SUCESSO !')
        window.location = 'itens/index.php?id_pedido=<?=$id_pedido;?>&clique_automatico_cabecalho=1'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Fornecedor(es) p/ Incluir Pedido :::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled 	= true
        document.form.txt_consultar.className 	= 'textdisabled'
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled 	= false
        document.form.txt_consultar.className 	= 'caixadetexto'
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
<body onLoad="document.form.txt_consultar.focus()">
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
            Consultar Fornecedor(es) p/ Incluir Pedido
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size="45" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" onclick="document.form.txt_consultar.focus()" title="Consultar fornecedor por: Razão Social" id='label' checked>
            <label for='label'>Razão Social</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os fornecedores" onclick='limpar()' class="checkbox" id='label2'>
            <label for='label2'>Todos os registros</label>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title='Limpar' onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>