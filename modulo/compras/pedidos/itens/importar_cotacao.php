<?
require('../../../../lib/segurancas.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) COTAÇÃO(ÕES) PENDENTE(S) NO(S) ÚLTIMO(S) 7 DIA(S).</font>";

if($passo == 1) {
    if(!empty($_GET['new_tipo_compra'])) {//Aqui eu mudo o Tipo de Cotação, toda vez que o usuário alterar no Link ...
        $sql = "UPDATE `cotacoes` SET `tipo_compra` = '$_GET[new_tipo_compra]' WHERE `id_cotacao` = '$_GET[id_cotacao]' LIMIT 1 ";
        bancos::sql($sql);
    }
//Aqui eu busco o Fornecedor do Pedido pq o mesmo será utilizado mais abaixo ...
    $sql = "SELECT f.`id_pais`, p.`id_fornecedor` 
            FROM `pedidos` p 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
            WHERE p.`id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
    $campos_fornecedor 	= bancos::sql($sql);
    $id_pais            = $campos_fornecedor[0]['id_pais'];
    $id_fornecedor      = $campos_fornecedor[0]['id_fornecedor'];
//Aqui eu trago os Itens da Cotação q estão em aberto ou Parcial p/ serem Importados ...
    $sql = "SELECT ci.`id_cotacao_item`, ci.`id_produto_insumo`, ci.`qtde_pedida`, pi.`discriminacao` 
            FROM `cotacoes_itens` ci 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ci.`id_produto_insumo` 
            WHERE ci.`id_cotacao` = '$_GET[id_cotacao]' 
            AND ci.`status` < '2' ORDER BY pi.`discriminacao` ";
    $campos_itens = bancos::sql($sql);
    $linhas_itens = count($campos_itens);
?>
<html>
<head>
<title>.:: Importar Cotação(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if (elementos[i].checked == true) valor = true
        }
    }
//Se não tiver nenhuma opção marcada então ...
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {//Se tiver pelo menos 1 opção ...
        //Prepara a Tela p/ poder gravar no BD ...
        if(typeof(elementos['chkt_cotacao_item[]'][0]) == 'undefined') {
            var linhas = 1//Existe apenas 1 único elemento ...
        }else {
            var linhas = (elementos['chkt_cotacao_item[]'].length)
        }
        //Aqui eu verifico se tem algum Item em que o Preço Digitado está maior do que o Preço de Lista ...
        for(var i = 0; i < linhas; i++) {
            var qtde_digitada = eval(strtofloat(document.getElementById('txt_qtde_pedida'+i).value))
            var qtde_original = eval(strtofloat(document.getElementById('hdd_qtde_pedida'+i).value))//Esse valor está em um Hidden ...
            if(qtde_digitada > qtde_original) {
                alert('QTDE INVÁLIDA !!!\nQTDE DIGITADA MAIOR DO QUE O QTDE ORIGINAL !')
                document.getElementById('txt_qtde_pedida'+i).focus()
                document.getElementById('txt_qtde_pedida'+i).select()
                return false
            }
            var preco_pi_digitado = eval(strtofloat(document.getElementById('txt_preco_unitario'+i).value))
            var preco_pi_original = document.getElementById('hdd_preco_unitario'+i).value//Esse valor está em um Hidden ...
            if(preco_pi_digitado > preco_pi_original) {
                alert('PREÇO DE COMPRA INVÁLIDO !!!\nPREÇO DE COMPRA MAIOR DO QUE O PREÇO DE LISTA !')
                document.getElementById('txt_preco_unitario'+i).focus()
                document.getElementById('txt_preco_unitario'+i).select()
                return false
            }
        }
        //Prepara as caixinhas para gravar no BD ...
        for(var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_cotacao_item'+i).type == 'checkbox') {//Só irá tratar a linha quando for Checkbox mesmo ...
                document.getElementById('txt_qtde_pedida'+i).value      = strtofloat(document.getElementById('txt_qtde_pedida'+i).value)
                document.getElementById('txt_preco_unitario'+i).value   = strtofloat(document.getElementById('txt_preco_unitario'+i).value)
            }
        }
    }
}
</Script>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Importar Cotação N.º 
            <font color='yellow'>
                <?=$_GET['id_cotacao'];?>
            </font>
            <br/>
            <?
//Busca de alguns dados da Cotação ...
                $sql = "SELECT `fator_mmv`, `qtde_mes_comprar`, `tipo_compra`, `desconto_especial_porc` 
                        FROM `cotacoes` 
                        WHERE `id_cotacao` = '$_GET[id_cotacao]' LIMIT 1 ";
                $campos_cotacao = bancos::sql($sql);
            ?>
            Tipo: 
            <?
                if($campos_cotacao[0]['tipo_compra'] == 'N') {
                    $tipo_atual     = 'Nacional';
                    $new_tipo_compra= 'Export';
                }else {
                    $tipo_atual     = 'Export';
                    $new_tipo_compra= 'Nacional';
                }
            ?>
            <a href = 'importar_cotacao.php?passo=1&id_pedido=<?=$_GET['id_pedido'];?>&id_cotacao=<?=$_GET['id_cotacao'];?>&new_tipo_compra=<?=$new_tipo_compra;?>' title='Alterar Tipo de Compra da Cotação' style='cursor:help' class='link'>
                <font color='red' size='-1'>
                    <?=$tipo_atual;?>
                </font>
            </a>
            &nbsp;-&nbsp;
            Fator MMV: 
            <font color='yellow'>
                <?=number_format($campos_cotacao[0]['fator_mmv'], 1, ',', '.');?>
            </font>
            &nbsp;-&nbsp;
            Qtde de Mês: 
            <font color='yellow'>
                <?=number_format($campos_cotacao[0]['qtde_mes_comprar'], 1, ',', '.');?>
            </font>
            &nbsp;-&nbsp;
            Desconto: 
            <font color='yellow'>
                <?=number_format($campos_cotacao[0]['desconto_especial_porc'], 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Qtde<br/>Pedida
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Forma<br/>Pgto.
        </td>
        <td>
            Pço Compra<br/>Nac/Exp
        </td>
        <td>
            Valor Total
        </td>
        <td>
            Marca / <br/>Observação
        </td>
    </tr>
<?
    $vetor_forma_compra = array('', 'FAT/NF', 'FAT/SGD', 'AV/NF', 'AV/SGD');
    $existe_pi_blank = 0;
    for($i = 0; $i < $linhas_itens; $i++) {
        $qtde_importada = 0;//Sempre zero p/ não herdar valores do Loop Anterior ...
        //Verifico se o PI é do Grupo Blank ...
        $sql = "SELECT `id_grupo` 
                FROM `produtos_insumos` 
                WHERE `id_produto_insumo` = '".$campos_itens[$i]['id_produto_insumo']."' LIMIT 1 ";
        $campos_grupo = bancos::sql($sql);
        if($campos_grupo[0]['id_grupo'] == 22) $existe_pi_blank++;
//Aqui eu busco alguns dados da Lista de Preço do Fornecedor e Respectivo Produto ...
            $sql = "SELECT `preco`, `preco_exportacao`, `forma_compra`, `ipi`, `ipi_incluso`, `tp_moeda` 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_fornecedor` = '$id_fornecedor' 
                    AND `id_produto_insumo` = '".$campos_itens[$i]['id_produto_insumo']."' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_lista       = bancos::sql($sql);
            $type_object        = (count($campos_lista) == 1) ? 'checkbox' : 'hidden';//Se tiver pço desse PI na Lista mostra o checkbox ...
            $disabled_object    = (count($campos_lista) == 1) ? '' : 'disabled';//Se não tiver pço desse PI na Lista então desabilita o objeto ...
            $rotulo             = (count($campos_lista) == 1) ? '' : '<font color="red"><b>S/ PÇO DE LISTA</b></font>';//Se não tiver pço desse PI na Lista mostra essa mensagem ...
?>
    <tr class='linhanormal' onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='center'>
            <input type='<?=$type_object;?>' name='chkt_cotacao_item[]' id='chkt_cotacao_item<?=$i;?>' value="<?=$campos_itens[$i]['id_cotacao_item'];?>" onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8')" class='checkbox' <?=$disabled_object;?>>
            <?=$rotulo;?>
        </td>
        <td>
        <?
            //Aqui eu verifico tudo o que já foi importado em Pedidos deste "id_cotacao_item" do Loop ...
            $sql = "SELECT SUM(`qtde`) AS qtde_importada 
                    FROM `itens_pedidos` 
                    WHERE `id_cotacao_item` = '".$campos_itens[$i]['id_cotacao_item']."' ";
            $campos_qtde    = bancos::sql($sql);

            $qtde_restante  = $campos_itens[$i]['qtde_pedida'] - $campos_qtde[0]['qtde_importada'];
        ?>
            <input type='text' name='txt_qtde_pedida[]' id='txt_qtde_pedida<?=$i;?>' value="<?=number_format($qtde_restante, 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_total_geral('<?=$linhas_itens;?>')" onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8');return focos(this)" maxlength='9' size='11' class='textdisabled' disabled>
            <input type='hidden' name='hdd_qtde_pedida[]' id='hdd_qtde_pedida<?=$i;?>' value="<?=number_format($qtde_restante, 2, ',', '.');?>" disabled><!--P/ fazer comparação-->
        </td>
        <td align='left'>
            <?=$campos_itens[$i]['discriminacao'];?>
        </td>
        <td>
            <?=$vetor_forma_compra[$campos_lista[0]['forma_compra']];?>
        </td>
        <td align='right'>
        <?
            if($id_pais == 31) {//Se o País do Fornecedor for do Brasil, então verifica o Tipo de Cotação ...			
                $preco_lista    = ($campos_cotacao[0]['tipo_compra'] == 'E') ? $campos_lista[0]['preco_exportacao'] : $campos_lista[0]['preco'];
                $tp_moeda       = 'R$ ';
            }else {//Se Internacional então ...
                $preco_lista    = $campos_lista[0]['preco_exportacao'];
                $tp_moeda       = ($campos_lista[0]['tp_moeda'] == 1) ? 'U$' : '&euro; ';
            }
            if($campos_cotacao[0]['desconto_especial_porc'] > 0) {
                $preco_lista-= ($preco_lista * $campos_cotacao[0]['desconto_especial_porc'] / 100);
                $preco_lista = round($preco_lista, 2);
            }
            echo $tp_moeda;
        ?>
            <input type='text' name='txt_preco_unitario[]' id='txt_preco_unitario<?=$i;?>' value="<?=number_format($preco_lista, 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_total_geral()" onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8');return focos(this)" maxlength='9' size='11' class='textdisabled' disabled>
            <input type='hidden' name='hdd_preco_unitario[]' id='hdd_preco_unitario<?=$i;?>' value="<?=$preco_lista;?>" disabled><!--P/ fazer comparação-->
        </td>
        <td>
            <?$valor_total = $qtde_restante * $preco_lista;?>
            <input type='text' name='txt_valor_total[]' id='txt_valor_total<?=$i;?>' value="<?=number_format($valor_total, 2, ',', '.');?>" maxlength='9' size='11' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_marca_observacao[]' id='txt_marca_observacao<?=$i;?>' value="<?='Cot. '.$id_cotacao;?>" onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8');return focos(this)" class='textdisabled' disabled>
            <input type='hidden' name='hdd_ipi[]' id='hdd_ipi<?=$i;?>' value="<?=$campos_lista[0]['ipi'];?>" disabled>
            <input type='hidden' name='hdd_ipi_incluso[]' id='hdd_ipi_incluso<?=$i;?>' value="<?=$campos_lista[0]['ipi_incluso'];?>" disabled>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'importar_cotacao.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
        <td>
            <font color='yellow'>
                Total Geral => 
            </font>
        </td>
        <td align='center'>
            <input type='text' name='txt_total_geral' value='0,00' maxlength="9" size="11" class='textdisabled' disabled>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
<?
    if($existe_pi_blank > 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='7'>
            <font color='black' size='3'>
                <br/><blink><b/>PASSAR PARA EMITIR OP(S) DO(S) BLANK(S).</b></blink>
            </font>
        </td>
    </tr>
<?
    }
?>
</table>
<!--*******************Controle de Tela*******************-->
<input type='hidden' name='id_pedido' value='<?=$_GET['id_pedido'];?>'>
<input type='hidden' name='id_cotacao' value='<?=$_GET['id_cotacao'];?>'>
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<!--******************************************************-->
</form>
</body>
</html>
<?
//Parte de Inserção dos Itens
}else if($passo == 2) {
    foreach($_POST['chkt_cotacao_item'] as $i => $id_cotacao_item) {
        //Busco o "id_produto_insumo" através do $id_cotacao_item selecionado pelo usuário ...
        $sql = "SELECT `id_produto_insumo` 
                FROM `cotacoes_itens` 
                WHERE `id_cotacao_item` = '$id_cotacao_item' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $id_produto_insumo  = $campos[0]['id_produto_insumo'];

        //Insiro o "id_cotacao_item" na tabela de "id_item_pedido" ...
        $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `id_cotacao_item`, `preco_unitario`, `qtde`, `ipi`, `ipi_incluso`, `marca`) VALUES (NULL, '$_POST[id_pedido]', '$id_produto_insumo', '$id_cotacao_item', '".$_POST['txt_preco_unitario'][$i]."', '".$_POST['txt_qtde_pedida'][$i]."', '".$_POST['hdd_ipi'][$i]."', '".$_POST['hdd_ipi_incluso'][$i]."', '".$_POST['txt_marca_observacao'][$i]."') ";
        bancos::sql($sql);
/*****************************************E-mail*****************************************/
        /*Busca do Fornecedor Default atual até antes da troca e do Produto Insumo 
        p/ enviar por e-mail ...*/
        $sql = "SELECT f.`id_fornecedor`, f.`razaosocial`, pi.`discriminacao` 
                FROM `produtos_insumos` pi 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = pi.`id_fornecedor_default` 
                WHERE pi.`id_produto_insumo` = '$id_produto_insumo' 
                AND pi.`id_fornecedor_default` > '0' ";
        $campos_gerais              = bancos::sql($sql);
        $id_fornecedor_antigo       = $campos_gerais[0]['id_fornecedor'];
        $fornecedor_default_antigo  = $campos_gerais[0]['razaosocial'];
        $produto_insumo             = $campos_gerais[0]['discriminacao'];
        //Se o Fornecedor foi atual foi substítuido ...
        if($id_fornecedor_antigo != $_POST['id_fornecedor']) {
            //Busca do nome do Novo Fornecedor que será apresentado no e-mail ...
            $sql = "SELECT `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `id_fornecedor` = '$_POST[id_fornecedor]' LIMIT 1 ";
            $campos_fornecedor          = bancos::sql($sql);
            $fornecedor_default_novo    = $campos_fornecedor[0]['razaosocial'];
            //Busca do Nome do Funcionário que está fazendo a Substituição p/ o Novo Fornecedor ...
            $sql = "SELECT `login` 
                    FROM `logins` 
                    WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            $login_alterando = $campos_login[0]['login'];
            $mensagem_email = 'O Funcionário <b>'.ucfirst($login_alterando).'</b> fez substituição de Fornecedor Default.';
            $mensagem_email.= '<br><br><b>Fornecedor Default Antigo: </b>'.$fornecedor_default_antigo.' <br><b>Novo Fornecedor Default: </b>'.$fornecedor_default_novo.' <br><b>Produto Insumo: </b>'.$produto_insumo;
            //Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
            $mensagem_email.= '<br><br><b>Data e Hora: </b>'.date('d/m/Y H:i:s');
            //Aqui eu mando um e-mail informando quem está alterando o Fornecedor Default ...
            $destino = $substituicao_fornecedor_default;
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Substituição de Fornecedor Default', $mensagem_email);
/********************************Função que troca o Fornecedor por outro********************************/
            custos::setar_fornecedor_default($id_produto_insumo, $_POST['id_fornecedor'], 'S');
        }
        $observacao = 'Cotação N.º '.$id_cotacao. '. ';
        //Essa função serve tanto para o Incluir, como Alterar e Excluir Item de Pedido ...
        compras_new::atualizar_status_item_cotacao($_POST['chkt_cotacao_item'][$i]);
    }
/*****************Controle com o Status da Cotação*****************/
    compras_new::atualizar_status_cotacao($_POST['id_cotacao']);
/*****************Atualização de Dados da Cotação******************/
/*Antes de registrar um Follow-UP para este Pedido referente a Cotação que está sendo importada, verifico 
se não existe algum Follow-UP registrado anteriormente para este Pedido, independente da ação ...*/
    $sql = "SELECT `id_follow_up` 
            FROM `follow_ups` 
            WHERE `identificacao` = '$_POST[id_pedido]' 
            AND `origem` = '16' LIMIT 1 ";
    $campos_follow_up   = bancos::sql($sql);
    /*Significa que ainda será o 1º Registro de Follow-UP que será incluso, então a Sugestão Inicial p/ esse 
    campo $exibir_no_pdf = 'S' ...*/
    $exibir_no_pdf      = (count($campos_follow_up) == 0) ? 'S' : 'N';
//Registro um Follow UP p/ saber a qual Cotação que este Pedido pertence ...
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `exibir_no_pdf`, `data_sys`) VALUES (NULL, '$_POST[id_fornecedor]', '$_SESSION[id_funcionario]', '$_POST[id_pedido]', '16', '$observacao', '$exibir_no_pdf', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('ITEM DE COTAÇÃO(ÕES) IMPORTADO(S) COM SUCESSO !')
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
        window.location = 'importar_cotacao.php?id_pedido=<?=$_POST[id_pedido];?>'
    </Script>
<?
}else {
/********************Processo de verificação p/ que a Cotação possa ser importada com segurança********************/
//Aqui eu busco o Desconto Especial do Pedido caso exista ...
    $sql = "SELECT `desconto_especial_porc` AS desconto_esp_porc_ped, `tipo_export`, `material_retirado_nosso_estoque` 
            FROM `pedidos` 
            WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
    $campos                             = bancos::sql($sql);
    $tipo_compra                        = $campos[0]['tipo_export'];
    if($tipo_compra == 'I')             $tipo_compra = 'E';//Se o Tipo de Pedido = 'Importação' então o Sys sugere as Cotações do Tipo Export ...
    $desconto_esp_porc_ped              = $campos[0]['desconto_esp_porc_ped'];
    $material_retirado_nosso_estoque    = $campos[0]['material_retirado_nosso_estoque'];
    if($material_retirado_nosso_estoque == 'S') {
?>
    <Script Language = 'JavaScript'>
        alert('ESSA COTAÇÃO NÃO PODE SER IMPORTADA NESTE PEDIDO !!!\n\nDESMARQUE DO CABEÇALHO A MARCAÇÃO "MATERIAL RETIRADO DO NOSSO ESTOQUE" !')
        window.close()
    </Script>
<?
        exit;
    }
/******************************************************************************************************************/
    //Se esse checkbox estiver marcado então exibe todas as Cotações desde o início do Sistema ...
    $condicao_dias = (!empty($_GET['chkt_visualizar_todas_cotacoes'])) ? '' : " AND SUBSTRING(c.`data_sys`, 1, 10) > DATE_ADD('".date('Y-m-d')."', INTERVAL -7 DAY) ";
    //Aqui lista todas as Cotações que estão em Aberto ou Parcial do mesmo Tipo de Compra do Cabeçalho do Pedido ...
    $sql = "SELECT c.`id_cotacao`, c.`fator_mmv`, c.`qtde_mes_comprar`, c.`tipo_compra`, c.`desconto_especial_porc`, 
            DATE_FORMAT(SUBSTRING(c.`data_sys`, 1, 10), '%d/%m/%Y') AS data_emissao, f.`nome` 
            FROM `cotacoes` c 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = c.`id_funcionario` 
            WHERE c.`status` < '2' 
            AND c.`tipo_compra` = '$tipo_compra' 
            $condicao_dias 
            ORDER BY c.`id_cotacao` DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Importar Cotação(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_cotacao, desconto_esp_porc_cotacao) {
    var id_pedido               = '<?=$_GET['id_pedido'];?>'
    var desconto_esp_porc_ped 	= '<?=$desconto_esp_porc_ped;?>'

    if(desconto_esp_porc_ped != desconto_esp_porc_cotacao) {
        alert('ESSA COTAÇÃO NÃO PODE SER IMPORTADA DEVIDO POSSUIR UM DESC DIFERENTE DO PEDIDO !\n\nACERTE O DESC DESTA COTAÇÃO P/ QUE A MESMA POSSA SER IMPORTADA NESSE PEDIDO !!!')
        return false
    }
    window.location = 'importar_cotacao.php?passo=1&id_pedido='+id_pedido+'&id_cotacao='+id_cotacao
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    if($linhas == 0) {//Não encontrou nenhuma Cotação ...
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
            <p/>
            <input type='checkbox' name='chkt_visualizar_todas_cotacoes' id='chkt_visualizar_todas_cotacoes' value='1' onclick="window.location = 'importar_cotacao.php?id_pedido=<?=$_GET['id_pedido'];?>&chkt_visualizar_todas_cotacoes=1'" title='Visualizar Todas Cotações' class='checkbox'>
            <label for='chkt_visualizar_todas_cotacoes' class='combo'>
                <b>VISUALIZAR TODAS COTAÇÕES</b>
            </label>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_pedido=<?=$_GET['id_pedido'];?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<? 
    }else {//Encontrou pelo menos uma Cotação ...
?>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Importar Cotação(ões) - 
            <font color='yellow'>
                    Tipo de Compra: 
            </font> 
            <?
                if($tipo_compra == 'N') {
                    echo 'Nacional';
                }else {
                    echo 'Export';
                }
            ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º Cotação
        </td>
        <td>
            Emissor
        </td>
        <td>
            Fator MMV
        </td>
        <td>
            Qtde de Mês
        </td>
        <td>
            Desconto
        </td>
        <td>
            Data de Emissão
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="prosseguir('<?=$campos[$i]['id_cotacao'];?>', '<?=$campos[$i]['desconto_especial_porc'];?>')" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="prosseguir('<?=$campos[$i]['id_cotacao'];?>', '<?=$campos[$i]['desconto_especial_porc'];?>')">
            <a href="#" class='link'>
                <?=$campos[$i]['id_cotacao'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['fator_mmv'], 1, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde_mes_comprar'], 1, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos[$i]['desconto_especial_porc'], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_pedido=<?=$_GET['id_pedido'];?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?
    }
?>
</table>
<?
    if($linhas > 0) {//Só irá exibir a Paginação se encontrou pelo menos 1 Cotação ...
?>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
<b>* O sistema só exibe as Cotações dos <font color='red'>Últimos 7 Dias</font> e de <font color='red'>mesmo Tipo de Compra</font> 
do Cabeçalho do Pedido.</b>
</pre>
<?}?>