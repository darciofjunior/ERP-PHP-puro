<?
require('../../../lib/segurancas.php');
require('../../../lib/compras_new.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>PEDIDO ALTERADO COM SUCESSO.</font>";
$mensagem[2] = "<font class='confirmacao'>VENCIMENTO REALIZADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>VENCIMENTO ALTERADO COM SUCESSO.</font>";
$mensagem[4] = "<font class='confirmacao'>VENCIMENTO EXCLUÍDO COM SUCESSO.</font>";

if(!empty($_POST['id_pedido'])) {
//Aqui eu verifico o último desconto_especial_porc do Pedido para ver se este sofreu alterações
    $sql = "SELECT id_fornecedor, desconto_especial_porc, tipo_export 
            FROM `pedidos` 
            WHERE `id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $desconto_especial_velho    = $campos[0]['desconto_especial_porc'];
    $id_fornecedor              = $campos[0]['id_fornecedor'];
    $tipo_export                = $campos[0]['tipo_export'];
//Significa que o usuário alterou o Desconto
    if($desconto_especial_velho != $_POST['txt_desc_esp_ped']) {//Aqui só trago os itens de Pedido que possuem Desconto, para depois recalcular novamente
        $sql = "SELECT id_item_pedido, id_produto_insumo, qtde 
                FROM `itens_pedidos` 
                WHERE `id_pedido` = '$_POST[id_pedido]' 
                AND `desconto_especial` = 'S' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
//Aqui fará a modificação de Preço em todos os Itens de Pedidos
        for($i = 0; $i < $linhas; $i++) {//Aqui volta o Preço Antigo do Produto, exatamente o da Lista de Preço 
            $sql = "SELECT preco, preco_exportacao, preco_faturado_export 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    AND `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
            $campos_lista = bancos::sql($sql);
            if($tipo_export == 'N') {
                $preco_lista = $campos_lista[0]['preco'];
            }else if ($tipo_export == 'E') {
                $preco_lista = $campos_lista[0]['preco_exportacao'];
            }else if ($tipo_export == 'I') {
                $preco_lista = $campos_lista[0]['preco_faturado_export'];
            }
            //Aqui já joga o novo desconto que foi modificado pelo usuário*/
            $novo_preco = $preco_lista * ((100 - $_POST['txt_desc_esp_ped']) / 100);
            $novo_preco = round(round($novo_preco, 3), 2);
            //Aqui atualiza na Base de Dados o Novo Desconto
            $sql = "UPDATE `itens_pedidos` SET `preco_unitario` = '$novo_preco' WHERE `id_item_pedido` = ".$campos[$i]['id_item_pedido']." LIMIT 1 ";
            bancos::sql($sql);
        }
    }
/**********************************Controle com os Checkbox**********************************/
    $programado_descontabilizado        = (!empty($_POST['chkt_programado_descontabilizado'])) ? 'S' : 'N';
    $material_retirado_nosso_estoque    = (!empty($_POST['chkt_material_retirado_nosso_estoque'])) ? 'S' : 'N';
    /*O sistema coloca o Pedido como sendo Excluído p/ não Contabilizar no Compra Produção devido ser 
    pedidos Programados a longo Prazo ...*/
    $ativo                              = (!empty($_POST['chkt_programado_descontabilizado'])) ? 0 : 1;
    //Só existe esse procedimento quando o Fornecedor de quem estivermos comprando for Nacional daqui do Brasil ...
    if(isset($_POST['txt_data_entrega_atual'])) {
        $txt_data_entrega_atual 	= data::datatodate($txt_data_entrega_atual, '-');
        $condicao_prazo_entrega     = " `prazo_entrega` = '$txt_data_entrega_atual', ";
    }
    $cmb_porc   = substr($cmb_porc, 0, strlen($cmb_porc) - 1);
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL se não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_importacao = (!empty($_POST[cmb_importacao])) ? "'".$_POST[cmb_importacao]."'" : 'NULL';

//Rotina Normal para atualização do Pedido ...
    $sql = "UPDATE `pedidos` SET `id_empresa` = '$cmb_empresa', `id_tipo_moeda` = '$cmb_tipo_moeda', `id_importacao` = $cmb_importacao, `vendedor` = '$txt_vendedor', `desconto_especial_porc` = '$_POST[txt_desc_esp_ped]', $condicao_prazo_entrega `tipo_nota` = '$cmb_tipo_pedido', `tipo_nota_porc` = '$cmb_porc', `tipo_export` = '$cmb_tipo', `tp_moeda` = '$cmb_tipo_moeda', `programado_descontabilizado` = '$programado_descontabilizado', `material_retirado_nosso_estoque` = '$material_retirado_nosso_estoque', `ativo` = '$ativo' WHERE `id_pedido` = '$_POST[id_pedido]' LIMIT 1 ";
    bancos::sql($sql);
/**********************Atualizando as Datas dos Vencimentos do Pedido**********************/
/*Busca do Valor e os Dias das Parcelas do Pedido para poder montar uma Nova Data 
de Vencimento com a Data de Entrega digitada pelo usuário ...*/
    $sql = "SELECT id_pedido_financiamento, dias 
            FROM `pedidos_financiamentos` 
            WHERE `id_pedido` = '$_POST[id_pedido]' ORDER BY dias ";
    $campos_financiamento = bancos::sql($sql);
    $linhas_financiamento = count($campos_financiamento);
    if($linhas_financiamento > 0) {
        $txt_data_entrega_atual = data::datetodata($txt_data_entrega_atual, '/');
//Disparando o Loop ...
        for($i = 0; $i < $linhas_financiamento; $i++) {
//Aqui eu atualizo a Data de Venc. das Parcelas somando da Data de Ent. digitada pelo usuário ...
            $data_gravar = data::datatodate(data::adicionar_data_hora($txt_data_entrega_atual, $campos_financiamento[$i]['dias']), '-');
//Atualizando a Data de Vencimento de cada parcela ...
            $sql = "UPDATE `pedidos_financiamentos` SET `data` = '$data_gravar' WHERE `id_pedido_financiamento` = '".$campos_financiamento[$i]['id_pedido_financiamento']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    $valor = 1;
/******************************************************************************************/
?>
    <Script Language = 'Javascript'>
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    </Script>
<?
}
/******************************************/

//Excluindo os Vencimentos do Pedido ...
if($excluir_vencimento == 1) {
    $sql = "SELECT * 
            FROM `pedidos_financiamentos` 
            WHERE `id_pedido` = '$id_pedido' ";
    $campos_financiamento = bancos::sql($sql);
    $linhas_financiamento = count($campos_financiamento);
    if($linhas_financiamento > 0) {//Se foi encontrado pelo menos 1 Financiamento ...
        for($i = 0; $i < $linhas_financiamento; $i++) {
            $sql = "DELETE FROM `pedidos_financiamentos` WHERE `id_pedido_financiamento` = '".$campos_financiamento[$i]['id_pedido_financiamento']."' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
    $valor = 4;
?>
    <Script Language = 'Javascript'>
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    </Script>
<?
}
/******************************************/

//Procedimento normal de quando se carrega a Tela ...
$id_pedido = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_pedido'] : $_GET['id_pedido'];

//Verifico se tem algum Item de Pedido já importado em Nota Fiscal ...
$sql = "SELECT ip.id_item_pedido 
        FROM `itens_pedidos` ip 
        INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_item_pedido` = ip.`id_item_pedido` 
        WHERE ip.`id_pedido` = '$id_pedido' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$linhas_item_importado  = count($campos);

//Seleciona a qtde de itens que existe no Pedido
$sql = "SELECT COUNT(id_item_pedido) AS qtde_itens 
        FROM `itens_pedidos` 
        WHERE `id_pedido` = '$id_pedido' ";
$campos = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

//Busca dos Dados de Cabeçalho deste Pedido ...
$sql = "SELECT p.*, f.razaosocial, f.id_pais 
        FROM `pedidos` p 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
        WHERE p.id_pedido = '$id_pedido' LIMIT 1 ";
$campos         = bancos::sql($sql);
$id_pais        = $campos[0]['id_pais'];
$tipo_export    = $campos[0]['tipo_export'];
$data_emissao   = data::datetodata(substr($campos[0]['data_emissao'], 0, 10), '/');

//Verifica somente para países que são internacionais
if($id_pais != 31) {
    $sql = "SELECT id_importacao 
            FROM `pedidos` 
            WHERE `id_pedido` = '$id_pedido' 
            AND `id_importacao` > '0' LIMIT 1 ";
    $campos_importacao = bancos::sql($sql);
    if(count($campos_importacao) == 1) $id_importacao = $campos_importacao[0]['id_importacao'];
}
?>
<html>
<title>.:: Alterar Pedido ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Empresa
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE A EMPRESA !')) {
        return false
    }
//Tipo de Pedido
    if(!combo('form', 'cmb_tipo_pedido', '', 'SELECIONE O TIPO DE PEDIDO !')) {
        return false
    }
//Tipo de Exportação
    if(!combo('form', 'cmb_tipo', '', 'SELECIONE O TIPO DE EXPORTAÇÃO !')) {
        return false
    }
//Tipo de Moeda
    if(!combo('form', 'cmb_tipo_moeda', '', 'SELECIONE O TIPO DE MOEDA !')) {
        return false
    }
//Vendedor
    if(!texto('form', 'txt_vendedor', '3', "-/qwe'rtyuiopçlkjhgfdsazxcvbnm QWERTYUIOPÇLKJHGFDSAZXCVBNMÁÉÍÓÚáéíóúàÀüÜâêîôûÂÊÎÔÛÃãõÕ", 'VENDEDOR', '2')) {
        return false
    }
//Importação ...
    if(typeof(document.form.cmb_importacao) == 'object') document.form.cmb_importacao.disabled = false
//Prazo de Entrega
    if(typeof(document.form.txt_dias_prazo_entrega) == 'object') {
        if(!texto('form', 'txt_dias_prazo_entrega', '1', '/1234567890', 'PRAZO DE ENTREGA', '2')) {
            return false
        }
    }
//Tratamento com os objetos ...
    if(typeof(document.form.txt_data_entrega_atual) == 'object') {//Nacional ...
        document.form.txt_data_entrega_atual.disabled = false
    }
//Desabilito p/ poder gravar no BD ...
    if(typeof(document.form.chkt_material_retirado_nosso_estoque) == 'object') {
        document.form.chkt_material_retirado_nosso_estoque.disabled = false
    }
    document.form.cmb_empresa.disabled      = false
    document.form.cmb_tipo_pedido.disabled  = false
    limpeza_moeda('form', 'txt_desc_esp_ped, ')
}

function recalcular_datas() {
    if(typeof(document.form.txt_data_entrega_atual) == 'object') {//Nacional ...
        nova_data('<?=$data_emissao;?>', 'document.form.txt_data_entrega_atual', 'document.form.txt_dias_prazo_entrega')
    }
}

function alterar_tipo_pedido() {
    if(document.form.cmb_empresa.value == 1 || document.form.cmb_empresa.value == 2) {//Albafer ou Tool
        document.form.cmb_tipo_pedido.value = 1//NF
    }else if(document.form.cmb_empresa.value == 4) {//Se for Grupo
        document.form.cmb_tipo_pedido.value = 2//SGD
    }else {//Se não tiver nada selecionado, então eu zero o Tipo de Pedido
        document.form.cmb_tipo_pedido.value = ''
    }
}

//Excluir Vencimento
function excluir_vencimento(linhas_item_importado) {
/*Significa que esse Pedido possui pelo menos 1 item em Nota Fiscal, então eu não posso mais manipular 
os botões de Cabeçalho desse Pedido*/
    if(linhas_item_importado == 1) {
        alert('ESSE PEDIDO NÃO PODE SER MODIFICADO DEVIDO POSSUIR ITEM(NS) IMPORTADO(S) EM NF !')
    }else {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE VENCIMENTO ?')
        if(mensagem == true) window.location = 'alterar_cabecalho.php?id_pedido=<?=$id_pedido;?>&excluir_vencimento=1'
    }
}

function foco_onload() {
//Prazo de Entrega
    if(typeof(document.form.txt_dias_prazo_entrega) == 'object') {
        if(!texto('form', 'txt_dias_prazo_entrega', '1', '/1234567890', 'PRAZO DE ENTREGA', '2')) {
            return false
        }
    }
}

function controle_finame(acao) {
    var id_pedido           = '<?=$id_pedido;?>'
    var data_entrega_atual  = document.form.txt_data_entrega_atual.value

    if(acao == 'GERAR') {
        window.location = 'finame.php?id_pedido='+id_pedido+'&txt_data_entrega_atual='+data_entrega_atual
    }else if(acao == 'ALTERAR') {
        window.location = 'alterar_finame.php?id_pedido='+id_pedido+'&txt_data_entrega_atual='+data_entrega_atual
    }
}
</Script>
<body onload='recalcular_datas();alterar_tipo_pedido();foco_onload()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_pedido' value='<?=$id_pedido;?>'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Pedido
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='25%'>
            Fornecedor:
        </td>
        <td>
            <?=$campos[0]['razaosocial'];?>
        </td>
    </tr>
    <?
//Retorna a Qtde Antecipação(ões) existente(s) em Pedido 
        $sql = "SELECT COUNT(id_antecipacao) as total_antecipacoes 
                FROM antecipacoes 
                WHERE `id_pedido` = '$id_pedido' ";
        $campos_antecipacoes 	= bancos::sql($sql);
        $total_antecipacoes 	= $campos_antecipacoes[0]['total_antecipacoes'];
/*Se existir pelo menos 1 item de Pedido ou o Pedido tiver com pelo menos 1 antecipação, então eu já não 
posso + alterar a Empresa deste Pedido ...*/
        if($qtde_itens > 0 || $total_antecipacoes > 0) {
            $disabled   = 'disabled';
            $class      = 'textdisabled';
        }else {
            $disabled   = '';
            $class      = 'caixadetexto';
        }
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' onchange='alterar_tipo_pedido()' class="<?=$class;?>" <?=$disabled;?>>
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE ativo = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql, $campos[0]['id_empresa']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Tipo de Pedido:
        </td>
        <td>
            <select name='cmb_tipo_pedido' title='Tipo de Pedido' class='textdisabled' disabled>
                <option value = '' style='color:red'>SELECIONE</option>
<?
        if($campos[0]['tipo_nota'] == 1) {
?>
                <option value='1' selected>NF</option>
                <option value='2'>SGD</option>
<?
        }else {
?>
                <option value='1'>NF</option>
                <option value='2' selected>SGD</option>
<?
        }
?>
            </select>
            &nbsp;
            <select name='cmb_porc' title='Selecione o Tipo de Percentagem' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($campos[0]['tipo_nota_porc'] == 25) {
                        $selected25 = 'selected';
                    }else if($campos[0]['tipo_nota_porc'] == 50) {
                        $selected50 = 'selected';
                    }else if($campos[0]['tipo_nota_porc'] == 75) {
                        $selected75 = 'selected';
                    }else if($campos[0]['tipo_nota_porc'] == 100) {
                        $selected100 = 'selected';
                    }
                ?>
                <option value='25%' <?=$selected25;?>>25%</option>
                <option value='50%' <?=$selected50;?>>50%</option>
                <option value='75%' <?=$selected75;?>>75%</option>
                <option value='100%' <?=$selected100;?>>100%</option>
            </select>
            &nbsp;
            <select name='cmb_tipo' title='Selecione o tipo' class='combo'>
<?
        if($id_pais == 31) {
?>
                <option value='' style='color:red'>SELECIONE</option>
<?
            if($tipo_export == 'N') {
?>
                <option value='N' selected>Nacional</option>
                <option value='E'>Exportação</option>
<?
            }else {
?>
                <option value='N'>Nacional</option>
                <option value='E' selected>Exportação</option>
<?
            }
        }else {
?>
            <option value='I' selected>Importação</option>
<?
        }
?>
            </select>
        </td>
    </tr>
<?
/*Essa linha de importação só mostra para países internacionais, e traz somente
as importações que ainda não foram utilizadas em nenhum pedido*/
    if($id_pais != 31) {
/*Significa q esse pedido não tem importação atrelada, então jogo uma valor
para a variável*/
            if(empty($id_importacao)) $id_importacao = 0;
            //Aqui só trago somente as importações atreladas ...
            $sql = "SELECT DISTINCT(id_importacao) 
                    FROM `pedidos` 
                    WHERE `id_importacao` NOT IN (0, $id_importacao) ORDER BY id_importacao ";
            $campos_importacao = bancos::sql($sql);
            $linhas_importacao = count($campos_importacao);
            for($i = 0; $i < $linhas_importacao; $i++) $id_importacoes.= $campos_importacao[$i]['id_importacao'].',';
            $id_importacoes = substr($id_importacoes, 0, strlen($id_importacoes) - 1);

            //Vejo esse Pedido está importado no Financeiro ...
            $sql = "SELECT id_conta_apagar 
                    FROM `contas_apagares` 
                    WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
            $campos_contas_apagares = bancos::sql($sql);
            if(count($campos_contas_apagares) == 0) {//Se não está no Financeiro, então posso mexer na Combo de Importação normalmente ...
                $disabled_importacao    = '';
                $class                  = 'combo';
            }else {//Já está importado no financeiro, então não posso desatrelar a Importação atribuída ...
                $disabled_importacao    = 'disabled';
                $class                  = 'textdisabled';
            }
?>
    <tr class='linhanormal'>
        <td>
            Importação:
        </td>
        <td>
            <select name='cmb_importacao' title='Selecione a Importação' class='<?=$class;?>' <?=$disabled_importacao;?>>
            <?
                $sql = "SELECT id_importacao, nome 
                        FROM `importacoes` 
                        WHERE `ativo` = '1' 
                        AND `id_importacao` NOT IN ($id_importacoes) ORDER BY id_importacao DESC ";
                echo combos::combo($sql, $id_importacao);
            ?>
            </select>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td>
            <b>Tipo da Moeda:</b>
        </td>
        <td>
            <select name='cmb_tipo_moeda' title='Selecione o tipo da moeda' class='combo'>
            <?
                $simbolo = ($id_pais == 31) ? ' = ' : ' <> ';
                $sql = "SELECT id_tipo_moeda, concat(simbolo, ' - ', moeda) 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' 
                        AND `id_tipo_moeda` $simbolo 1 ";
                echo combos::combo($sql, $campos[0]['id_tipo_moeda']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <?
                if($campos[0]['programado_descontabilizado'] == 'S') $checked = 'checked';
            ?>
            <input type='checkbox' name='chkt_programado_descontabilizado' id='programado_descontabilizado' value='S' class='checkbox' <?=$checked;?>>
            <label for='programado_descontabilizado'>
                <b>Programado Descontabilizado</b>
            </label>
        </td>
    </tr>
    <?
        /*************************************************************************************************************/
        //A princípio essa opção só é mostrada p/ os Fornecedores Hispania - 146 e K2 - 697 conforme ordens do Roberto.
        if($campos[0]['id_fornecedor'] == 146 || $campos[0]['id_fornecedor'] == 697) {
    ?>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <?
                $disabled = ($qtde_itens > 0) ? 'disabled' : '';//Se o Pedido tiver pelo menos 1 item, travo esse Checkbox ...
                if($campos[0]['material_retirado_nosso_estoque'] == 'S') $checked = 'checked';
            ?>
            <input type='checkbox' name='chkt_material_retirado_nosso_estoque' id='material_retirado_nosso_estoque' value='S' class='checkbox' <?=$checked;?> <?=$disabled;?>>
            <label for='material_retirado_nosso_estoque'>
                Material retirado do nosso Estoque
            </label>
        </td>
    </tr>
<?
        }
        /*************************************************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            <b>Funcionário Cotador:</b>
        </td>
        <td>
        <?
            $sql = "SELECT `nome` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos[0]['id_funcionario_cotado']."' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            echo $campos_funcionario[0]['nome'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
            <td><b>Vendedor:</b>
            <td><input type='text' name="txt_vendedor" value="<?=$campos[0]['vendedor'];?>" title="Vendedor" size="26" maxlength="50" class='caixadetexto'></td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Desconto Especial P/ este pedido:
        </td>
        <td>
            <?
                //Somente os usuários Roberto 62 e Dárcio 98, que podem dar Desconto Negativo ... "Acréscimo"
                $onkeyup = ($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) ? "verifica(this, 'moeda_especial', '2', '1', event)" : "verifica(this, 'moeda_especial', '2', '', event)";
            ?>
            <input type='text' name='txt_desc_esp_ped' value="<?=number_format($campos[0]['desconto_especial_porc'], 2, ',', '.')?>" title='Desconto Especial p/ Pedido' onkeyup="<?=$onkeyup;?>" size='5' maxlength='6' class='caixadetexto'>&nbsp;%&nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data Emissão:</b>
        </td>
        <td>
            <?=$data_emissao;?>
        </td>
    </tr>
<?
    /***************************Países Nacionais*********************/
    //if($id_pais == 31) {//Esse código foi comentado no dia 24/02/2017 porque não víamos lógica travar p/ Estrangeiros ...
        $data_entrega_atual = $campos[0]['prazo_entrega'];
        $retorno            = data::diferenca_data(data::datatodate($data_emissao, '-'), $data_entrega_atual);
        $dias_prazo_entrega = $retorno[0];
//Aqui eu passo a Data p/ o Formato normal anti-heróis (rs) ...
        $data_entrega_atual = data::datetodata($data_entrega_atual, '/');
        if($dias_prazo_entrega < 0) {
            $dias_prazo_entrega = 0;
            $data_entrega_atual = '';
        }
?>
    <tr class='linhanormal'>
        <td>
            <font color='darkgreen'>
                <b>Prazo de Entrega:</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_dias_prazo_entrega' value='<?=$dias_prazo_entrega;?>' title='Prazo de Entrega' size='8' maxlength='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);recalcular_datas()" class='caixadetexto'> DIAS
            <input type='text' name='txt_data_entrega_atual' value='<?=$data_entrega_atual;?>' size='12' class='textdisabled' disabled>
        </td>
    </tr>
<?
    //}
/****************************************************************************************************/
/***************************************** Vencimento ***********************************************/
/****************************************************************************************************/
//Aqui eu busco todas as Parcelas do Vencimento que foi feitas p/ o Pedido ...
            $sql = "SELECT pf.*, tm.simbolo 
                    FROM `pedidos_financiamentos` pf 
                    INNER JOIN pedidos p ON p.id_pedido = pf.id_pedido 
                    INNER JOIN tipos_moedas tm ON tm.id_tipo_moeda = p.id_tipo_moeda 
                    WHERE pf.id_pedido = '$id_pedido' ORDER BY pf.dias ";
            $campos_financiamento = bancos::sql($sql);
            $linhas_financiamento = count($campos_financiamento);
            if($linhas_financiamento > 0) {//Se foi encontrado pelo menos 1 Financiamento ...
?>
<table width='90%' cellspacing='0' cellpadding='1' border='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'>
            <font color='#FFFDCA'>
                <b>VENCIMENTO E PRAZO(S)</b>
                &nbsp; <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Vencimento' alt='Excluir Vencimento' onclick="excluir_vencimento('<?=$linhas_item_importado;?>')">
            </font>
        </td>
    </tr>
    <?
                for($i = 0; $i < $linhas_financiamento; $i++) {
    ?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Parcela N.º <?=$i + 1;?>:</b>
            </font>
        </td>
        <td>
            <font color='darkblue'>Dias: </font><?=$campos_financiamento[$i]['dias'];?>
        </td>
        <td>
            <font color='darkblue'>Data: </font><?=data::datetodata($campos_financiamento[$i]['data'], '/');?>
        </td>
    </tr>
    <?
                    if($i == 0) {//Se eu estiver na Primeira parcela
                        $primeira_parcela = $campos_financiamento[$i]['dias'];
                    }else if($i + 1 == $linhas_financiamento) {//Última Parcela
                        $ultima_parcela = $campos_financiamento[$i]['dias'];
                    }
                }
                $exibir_nota = ($campos[0]['tipo_nota'] == 1) ? 'NF' : 'SGD';
                $descricao_parcelas_ddl = $linhas_financiamento.' parc. ('.$primeira_parcela.' à '.$ultima_parcela.' DDL) '.$exibir_nota.' '.$campos[0]['tipo_nota_porc'].'%';
    ?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'><b>Descrição DDL: </b></font>
        </td>
        <td colspan='2'>
            <input type='text' name='txt_descricao_parcelas_ddl' value='<?=$descricao_parcelas_ddl;?>' title='Descrição Parcelas DDL' size='50' class='textdisabled' disabled>
        </td>
    </tr>
</table>
<?
            }
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
<?
/*Se esse Pedido possuir pelo menos 1 item em Nota Fiscal, então eu não posso mais manipular os botões 
de Cabeçalho desse Pedido*/
                if($linhas_item_importado == 1) {
                    $disabled   = 'disabled';
                    $class      = 'textdisabled';
                }else {
                    $disabled   = '';
                    $class      = 'botao';
                }
?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');recalcular_datas();foco_onload()" style='color:#ff9900' class="<?=$class;?>" <?=$disabled;?>>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class="<?=$class;?>" <?=$disabled;?>>
<?
/*Esse parâmetro txt_data_entrega_atual, eu levo porque é fundamental p/ o cálculo dos Prazos 
na Tela de Financiamento ...*/
                if($linhas_financiamento > 0) {
?>
                    <input type='button' name="cmd_alterar_vencimento" value="Alterar Vencimento" title="Alterar Vencimento" onclick="controle_finame('ALTERAR')" style="color:darkblue" class="<?=$class;?>" <?=$disabled;?>>
<?
                }else {//Ainda não existe Vencimento ...
?>
                    <input type='button' name='cmd_vencimento' value='Vencimento' title='Vencimento' onclick="controle_finame('GERAR')" style='color:darkblue' class="<?=$class;?>" <?=$disabled;?>>
<?
                }
?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick="fechar(window)" class='botao'>
<?
//Verifico se esta OS está atrelada em algum Pedido ...
                $sql = "SELECT id_os 
                        FROM `oss` 
                        WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
                $campos_os = bancos::sql($sql);
//Como encontrou OS atrelada, então eu exibo esse botão p/ mostrar os dados de Cabeçalho da OS ...
/*Eu tenho que levar esse parâmetro veio_outra_tela = 1, porque esse cabeçalho não está sendo acessado
diretamente de lá da tela de OS em produção, daí levando esse parâmetro eu evito que os Sys atualize 
a tela de itens e de rodapé que está abaixo do cabeçalho da OS*/
                if(count($campos_os) == 1) {
?>
            <input type='button' name='cmb_cabecalho_os' value='Cabeçalho da OS' title='Cabeçalho da OS' style='color:black' onclick="nova_janela('../../producao/os/alterar_cabecalho.php?id_os=<?=$campos_os[0]['id_os'];?>&veio_outra_tela=1', 'OS', '', '', '', '', 550, 850, 'c', 'c', '', '', 's', 's', '', '', '')" class="<?=$class;?>" <?=$disabled;?>>
<?
                }
?>
        </td>
    </tr>
</table>
<input type='hidden' name='data_emissao' value='<?=$data_emissao;?>'>
</form>
</body>
</html>