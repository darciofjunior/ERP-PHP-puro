<?
//Não chamar todas as libs porque esse arquivo já é puxado dentro de outro arquivo ...
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');//Essa biblioteca é utilizada dentro da Biblioteca 'compras_new' ...
require('../../../../../lib/compras_new.php');
require('../../../../../lib/data.php');//Essa biblioteca é utilizada dentro da Biblioteca 'compras_new' ...
require('../../../../../lib/intermodular.php');
require('../../../../../lib/producao.php');

$mensagem[1] = "<font class='confirmacao'>ITEM(NS) DE OS IMPORTADO(S) COM SUCESSO.</font>";

//Somente essa Tela que tem um controle um porquinho (rsrs) diferente com relação as libs ...
if(!empty($_POST['chkt_os_item'])) {//Incluir
    $data       = date('Y-m-d H:i:s');//Data Atual
    $vetor_os   = array();//Esse vetor será utilizado mais abaixo ...
    
    foreach($_POST['chkt_os_item'] as $i => $id_os_item) {
        //1)*******************************************Nota Fiscal********************************************/
//Inserção dos Itens de Pedido na Parte de Nota Fiscal
//Busca de alguns dados do Pedido e Item de Pedido, com o ID do Item do Pedido ...
        $sql = "SELECT p.`id_fornecedor`, p.`tipo_nota`, ip.`id_pedido`, ip.`id_produto_insumo`, ip.`ipi` 
                FROM `itens_pedidos` ip 
                INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` 
                WHERE ip.`id_item_pedido` = '".$_POST['hdd_item_pedido'][$i]."' LIMIT 1 ";
        $campos_pedido      = bancos::sql($sql);
        $id_fornecedor      = $campos_pedido[0]['id_fornecedor'];
//Tratamento para o Tipo de Nota, 0 = NF e 1 = SGD ...
        $ipi                = ($campos_pedido[0]['tipo_nota'] == 1) ? 0 : $campos_pedido[$i]['ipi'];
        $id_pedido          = $campos_pedido[0]['id_pedido'];
        $id_produto_insumo  = $campos_pedido[0]['id_produto_insumo'];

//O ICMS, Redução e IVA são campos que eu puxo diretamente da Lista de Preço ...
        $sql = "SELECT `icms`, `reducao`, `iva` 
                FROM `fornecedores_x_prod_insumos` 
                WHERE `id_fornecedor` = '$id_fornecedor' 
                AND `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos_lista   = bancos::sql($sql);
        if($campos_pedido[0]['tipo_nota'] == 1) {
            $icms       = $campos_lista[0]['icms'];
            $reducao    = $campos_lista[0]['reducao'];
            $iva        = $campos_lista[0]['iva'];
        }else {
            $icms       = 0;
            $reducao    = 0;
            $iva        = 0;
        }
        
        //Se foi marcada a opção de Lote Minimo da OS e existir esse Valor, gravo esse no Campo Marca / Obs do Item da NF ...
        $sql = "SELECT `id_os_item` 
                FROM `oss_itens` 
                WHERE `id_item_pedido` = '".$_POST['hdd_item_pedido'][$i]."' 
                AND `cobrar_lote_minimo` = 'S' 
                AND `lote_minimo_custo_tt` > '0' LIMIT 1 ";
        $campos_cobra_lote_minimo   = bancos::sql($sql);
        $marca                      = (count($campos_cobra_lote_minimo) == 1) ? ' <font color="brown" title="Lote Mínimo" style="cursor:help"><b> (L. Mín)</b></font>' : '';

        //Gravo o item de Entrada da OS na Nota Fiscal ...
        $sql = "INSERT INTO `nfe_historicos` (`id_nfe_historico`, `id_item_pedido`, `id_produto_insumo`, `id_nfe`, `id_pedido`, `tipo`, `qtde_entregue`, `valor_entregue`, `ipi_entregue`, `icms_entregue`, `reducao`, `iva`, `marca`, `data_sys`) VALUES (NULL, '".$_POST['hdd_item_pedido'][$i]."', '$id_produto_insumo', '$_POST[id_nfe]', '$id_pedido', 'E', '".$_POST['hdd_peso_qtde_total_utilizar'][$i]."', '".$_POST['hdd_preco_pi'][$i]."', '$ipi', '$icms', '$reducao', '$iva', '$marca', '$data') ";
        bancos::sql($sql);
        $id_nfe_historico = bancos::id_registro();
        compras_new::pedido_status($_POST['hdd_item_pedido'][$i]);
        
        //Atrelo no item de Entrada da OS o id_nfe_historico que foi gerado acima ...
        $sql = "UPDATE `oss_itens` SET `id_nfe_historico` = '$id_nfe_historico' WHERE `id_os_item` = '$id_os_item' LIMIT 1 ";
        bancos::sql($sql);
        
        //2)***********************************************OS*************************************************/
        $sql = "SELECT `id_os` 
                FROM `oss_itens` 
                WHERE `id_os_item` = '$id_os_item' LIMIT 1 ";
        $campos = bancos::sql($sql);

        //Verifico se esse id_os está dentro do $vetor_os ...
        if(!in_array($campos[0]['id_os'], $vetor_os)) $vetor_os[] = $campos[0]['id_os'];

        //Essa função serve tanto para o Incluir, como Alterar e Excluir Item da Nota Fiscal ...
        producao::atualizar_status_item_os($id_os_item);//Aqui atualiza o status do Item de Entrada ...*/
/*****************************************************************************************************/
    }

/*****************Controle com o Status da OS*****************/
    for($i = 0; $i < count($vetor_os); $i++) producao::atualizar_status_os($vetor_os[$i]);
/*************************************************************/
//Aqui eu verifico a NF possui formas de Vencimento ...
    $sql = "SELECT `id_nfe_financiamento` 
            FROM `nfe_financiamentos` 
            WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
    $campos_financiamento = bancos::sql($sql);
//Se existir então chama a função, toda vez q excluir 1 item p/ recalcular as parcelas ...
    if(count($campos_financiamento) == 1) {
/*Toda vez que eu excluir os Itens eu garanto q o Sistema está zerando os Prazos de Vencimento do Modo 
Antigo p/ não dar conflitos com o JavaScript no cabeçalho da NF ...*/
        $sql = "UPDATE `nfe` SET `valor_a` = '0', `valor_b` = '0', `valor_c` = '0' WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
        bancos::sql($sql);
/*********************************************/
/*Essa função pega o valor da Nota Fiscal, e desconta desse valor, o valor total das antecipações e 
e divide o valor restante de acordo com a Qtde de Prazos*/
        compras_new::calculo_valor_financiamento($_POST['id_nfe']);
    }
/*********************************************/
    $valor = 1;
}
       
//Busca de Alguns Dados da Nota Fiscal
$sql = "SELECT `id_empresa`, `id_fornecedor`, `id_tipo_moeda` 
        FROM `nfe` 
        WHERE `id_nfe` = '$_GET[id_nfe]' LIMIT 1 ";
$campos         = bancos::sql($sql);//Aki busca o id_empresa, id_fornecedor e o Tipo de Moeda da Fiscal c/ o id da NF
//Tem que renomear essa variável, pq já existe uma variável com o nome de $id_empresa na sessão ...
$id_empresa_nf  = $campos[0]['id_empresa'];
$id_fornecedor  = $campos[0]['id_fornecedor'];
$id_tipo_moeda  = $campos[0]['id_tipo_moeda'];

/*Aqui eu busco somente as OS em que seus "Itens de Saída" estão importadas em Pedido de Compras, que estejam em Aberto apenas 
e do mesmo Fornecedor que está em Nota Fiscal, e do mesmo tipo de Empresa do Cabeçalho da Nota Fiscal ...*/
$sql = "SELECT oi.* 
        FROM `oss_itens` oi 
        INNER JOIN `oss` ON oss.`id_os` = oi.`id_os` AND oss.`id_fornecedor` = '$id_fornecedor' AND oss.`id_pedido` <> '0' 
        INNER JOIN `pedidos` p ON oss.`id_pedido` = p.`id_pedido` AND p.`id_empresa` = '$id_empresa_nf' 
        INNER JOIN `ops` ON oi.`id_op` = ops.`id_op` 
        WHERE oi.`id_nfe` = '$_GET[id_nfe]' 
        AND oi.`qtde_entrada` > '0' /*Somente os itens de Entrada da OS*/
        AND oi.`status` < '2' 
        ORDER BY oi.`id_os_item` ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
<Script Language = 'JavaScript'>
    alert('NÃO EXISTE(M) ITEM(NS) DE OS PARA SER(EM) IMPORTADO(S) NESSA NF !')
    window.opener.parent.itens.document.form.submit()
    window.opener.parent.rodape.document.form.submit()
    window.close()
</Script>
<?
    exit;
}else {
?>
<html>
<head>
<title>.:: Incluir Itens de Nota Fiscal ::.</title>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tab_itens_oss_checkbox.js'></Script>
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
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='98%' border='0' align='center' cellspacing='1' cellpadding='0' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='14'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            Incluir Itens de Nota Fiscal - Somente da Empresa 
            <font color='yellow'>
            <?
                $tp_empresa[1] = 'ALBAFER';
                $tp_empresa[2] = 'TOOL MASTER';
                $tp_empresa[4] = 'GRUPO';

                echo $tp_empresa[$id_empresa_nf];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar(totallinhas, '#E8E8E8')" class="checkbox">
        </td>
        <td>
            N.º OP
        </td>
        <td>
            Qtde de Saída
        </td>
        <td>
            Qtde de Entrada
        </td>
        <td>
            Dif. Qtde
        </td>
        <td>
            Produto
        </td>
        <td>
            Total de Saída
        </td>
        <td>
            Total de Entrada
        </td>
        <td>
            CTT
        </td>
        <td>
            Preço<br/> Unit. R$
        </td>
        <td>
            Total<br/> Entrada R$
        </td>
        <td>
            Dureza Fornecedor
        </td>
        <td>
            Dureza Interna
        </td>
        <td>
            <font title='N.º do Pedido / N.º da OS'>
                N.º Ped / OS
            </font>
        </td>
    </tr>
<?
//Utilizo essa variável para fazer o cálculo Total em Kilo ...
            $total_qtde = 0;
            for($i = 0; $i < $linhas; $i++) {
                //Busca de Dados e da Unidade que será utilizada logo abaixo ...
                $sql = "SELECT pi.`discriminacao`, u.`sigla` 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                        WHERE pi.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo_ctt']."' LIMIT 1 ";
                $campos_dados = bancos::sql($sql);

                if($campos_dados[0]['sigla'] == 'UN') {//Se a unidade do PI do CTT = "Unidade", então utilizo o campo Qtde ... 
                    $peso_qtde_total_utilizar = $campos[$i]['qtde_entrada'];
                }else {//Se a unidade do PI do CTT <> "Unidade", então utilizo o campo Peso Total ...
                    $peso_qtde_total_utilizar = $campos[$i]['peso_total_entrada'];
                }

                //Busco dados de Saída do id_os_item que é Entrada nesse Loop ...
                $sql = "SELECT * 
                        FROM `oss_itens` 
                        WHERE `id_os_item` = '".$campos[$i]['id_os_item_saida']."' LIMIT 1 ";
                $campos_os_item_saida = bancos::sql($sql);
?>
    <tr class='linhanormal' onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_os_item[]' id='chkt_os_item<?=$i;?>' value='<?=$campos[$i]['id_os_item'];?>' onclick="checkbox_habilita('<?=$i;?>', '#E8E8E8')" class='checkbox'>
            &nbsp;
            <!--********Esse controle será utilizado na próxima tela*********-->
            <input type='hidden' name='hdd_item_pedido[]' id='hdd_item_pedido<?=$i;?>' value='<?=$campos_os_item_saida[0]['id_item_pedido'];?>'>
        </td>
        <td>
            <?=$campos[$i]['id_op'];?>
        </td>
        <td>
            <?=$campos_os_item_saida[0]['qtde_saida'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde_entrada'];?>
        </td>
        <td>
        <?
//Comparação entre as 2 Quantidades - Faço controle de Cores ...
            if((($campos[$i]['qtde_entrada'] / $campos_os_item_saida[0]['qtde_saida']) > 1.01) || (($campos[$i]['qtde_entrada'] / $campos_os_item_saida[0]['qtde_saida']) < 0.99)) {
                $color = 'red';
            }else {
                $color = 'blue';
            }
            $resultado = $campos[$i]['qtde_entrada'] - $campos_os_item_saida[0]['qtde_saida'];
            echo "<font color=$color>".$resultado."</font>";
        ?>
        </td>
        <td align='left'>
        <?
                //Busca dos Produtos da OP agora através do id_op que está na OS
                $sql = "SELECT pa.`id_produto_acabado`, pa.`referencia` 
                        FROM `ops` 
                        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ops.`id_produto_acabado` 
                        WHERE ops.`id_op` = ".$campos[$i]['id_op']." LIMIT 1 ";
                $campos_pa = bancos::sql($sql);
                echo intermodular::pa_discriminacao($campos_pa[0]['id_produto_acabado']);
//Aki eu printo se é Retrabalho na Frente da Discriminação ...
                if($campos[$i]['retrabalho'] == 1) echo ' <font color="red"><b>RETRABALHO</b></font>';
            ?>
            </td>
            <td>
            <?
                $total_qtde+= $campos_os_item_saida[0]['peso_total_saida'];
                echo number_format($campos_os_item_saida[0]['peso_total_saida'], 3, ',', '.');
//Verifico o quanto que ainda falta para ser devolvido da Peça ...
                $diferenca = $campos_os_item_saida[0]['peso_total_saida'] - $campos[$i]['peso_total_entrada'];
                if($diferenca > 0) {
                    echo '<br><font color="red" title="Qtde restante p/ Entrega" style="cursor:help">';
                    echo number_format($diferenca, 3, ',', '.');
                }else {
                    echo '<br><font color="blue" title="Qtde que sobrou da Entrega" style="cursor:help">';
                    echo number_format(abs($diferenca), 3, ',', '.');
                }
                echo '</font>';
            ?>
            </td>
            <td>
            <?
                if($campos[$i]['peso_total_entrada'] > 0) echo number_format($campos[$i]['peso_total_entrada'], 3, ',', '.');
            ?>
                <!--Eu guardo esse Preço em um Hidden porque daí fica + fácil na hora em que eu submeto-->
                <input type='hidden' name='hdd_peso_qtde_total_utilizar[]' id='hdd_peso_qtde_total_utilizar<?=$i;?>' value="<?=$peso_qtde_total_utilizar;?>" disabled>
            </td>
            <td align='left'>
            <?
                if(!empty($campos_dados[0]['discriminacao'])) {
                    echo $campos_dados[0]['sigla'].' - '.$campos_dados[0]['discriminacao'];
                }else {
                    echo '&nbsp;';
                }

//Verifico se esse PI tem algum CTT, atrelado ...
                $sql = "SELECT ctts.`id_ctt`, ctts.`codigo` AS dados_ctt 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `ctts` ON ctts.`id_ctt` = pi.`id_ctt` 
                        WHERE pi.`id_produto_insumo` = ".$campos[$i]['id_produto_insumo_ctt']." ";
                $campos_ctts = bancos::sql($sql);
                if(count($campos_ctts) == 1) {//Se encontrar CTT atrelado ao PI, então eu printo este ...
                    echo ' / <font color="darkblue">'.$campos_ctts[0]['dados_ctt'].'</font>';
                }
            ?>
            </td>
            <td align='right'>
            <?
                //Se no Item da OS possuir essa marcação, o Novo Preço do PI fica sendo ...
                if($campos_os_item_saida[0]['cobrar_lote_minimo'] == 'S') {
                    //Se o Lote Mínimo desse PI na Lista de Preço for zerado não sigo esse caminho 
                    if($campos_os_item_saida[0]['lote_minimo_custo_tt'] == 0) {
                        $preco_pi   = $campos_os_item_saida[0]['preco_pi'];
                    }else {
                        //Aqui nós recalculamos o Preço Unitário para que o Total do Item do Pedido não dê errado ...
                        if($peso_qtde_total_utilizar * $campos_os_item_saida[0]['preco_pi'] < $campos_os_item_saida[0]['lote_minimo_custo_tt']) {
                            $preco_pi = round($campos_os_item_saida[0]['lote_minimo_custo_tt'] / $peso_qtde_total_utilizar, 2);
                        }else {
                            $preco_pi   = $campos_os_item_saida[0]['preco_pi'];
                        }
                    }
                }else {
                    $preco_pi   = $campos_os_item_saida[0]['preco_pi'];
                }
                echo number_format($preco_pi, 2, ',', '.');
            ?>
                <!--Eu guardo esse Preço em um Hidden porque daí fica + fácil na hora em que eu submeto-->
                <input type='hidden' name='hdd_preco_pi[]' id='hdd_preco_pi<?=$i;?>' value='<?=$preco_pi;?>' disabled>
            </td>
            <td align='right'>
            <?
                echo number_format($peso_qtde_total_utilizar * $preco_pi, 2, ',', '.');
                if($campos_os_item_saida[0]['cobrar_lote_minimo'] == 'S') echo '<br><font color="brown" title="Lote Mínimo" style="cursor:help"><b> (L. Mín)</b></font>';
            ?>
            </td>
            <td>
                <?=$campos[$i]['dureza_fornecedor'];?>
            </td>
            <td>
                <?=$campos_os_item_saida[0]['dureza_interna'];?>
            </td>
            <td>
            <?
                //Aqui eu trago o N.º do Pedido no qual a OS está vinculada ...
                $sql = "SELECT `id_pedido` 
                        FROM `oss` 
                        WHERE `id_os` = '".$campos[$i]['id_os']."' LIMIT 1 ";
                $campos_pedido = bancos::sql($sql);
                if(count($campos_pedido) == 1) {//Encontrou a OS em um Pedido, então eu printo o N. do Ped
                    echo $campos_pedido[0]['id_pedido'].' / '."<font color='red'>".$campos[$i]['id_os']."</font>";
                }
            ?>
            </td>
    </tr>
<?
            }
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='9'>
            <b>FRETE QTD KGS P/ CÁLCULO TOTAL/FRETE -></b>
        </td>
        <td colspan='5'>
            <b><?=number_format($total_qtde, 2, ',', '.');?> / KG-TOT</b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<!--Controle de Tela-->
<input type='hidden' name='id_nfe' value='<?=$_GET['id_nfe'];?>'>
<input type='hidden' name='nao_atualizar'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>