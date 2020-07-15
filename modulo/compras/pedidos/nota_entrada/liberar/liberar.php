<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/calculos.php');
require('../../../../../lib/data.php');
require('../../../../../lib/estoque_new.php');
require('../../../../../lib/estoque_acabado.php');
require('../../../../../lib/compras_new.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/intermodular.php');
require('../../../../../lib/variaveis/compras.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>TODOS OS ITENS FORAM LIBERADOS, MAS A NOTA PERMANECEU EM ABERTA.</font>";
$mensagem[3] = "<font class='confirmacao'>NOTA LIBERADA COM SUCESSO.</font>";
$mensagem[4] = "<font class='confirmacao'>ITEM LIBERADO COM SUCESSO.</font>";
$mensagem[5] = "<font class='erro'>ITEM LIBERADO COM SUCESSO, MAS EXISTEM ITENS EM ABERTO, POR ISTO A NOTA NÃO PODE SER CONCLUÍDA.</font>";
$mensagem[6] = "<font class='erro'>EXISTEM ITENS EM ABERTO, POR ISTO A NOTA NÃO PODE SER CONCLUÍDA.</font>";
$mensagem[7] = "<font class='erro'>ESSA NOTA FISCAL NÃO PODE SER LIBERADA !!! EXISTE(M) ITEM(NS) COM A MARCAÇÃO DE <font color='green'>(DEB)</font>, QUE AINDA NÃO FORAM ATRELADO(S) À OUTRA(S) NOTA(S) FISCAL(IS).</font>";
$mensagem[8] = "<font class='erro'>ESSA NOTA FISCAL NÃO PODE SER LIBERADA !!! EXISTE(M) NOTA(S) FISCAL(IS) ATRELADA(S) A ESTÁ QUE PRECISA(M) SER LIBERADA(S) PRIMEIRO.</font>";
$mensagem[9] = "<font class='erro'>EXISTE(M) PRODUTO(S) ACABADO(S) QUE NÃO POSSUE(M) ESTOQUE REAL OU DISPONÍVEL.</font>";
$mensagem[10] = "<font class='erro'>ESSA NOTA FISCAL NÃO PODE SER LIBERADA !!! ESTA NÃO FOI FEITA PELO MODO DE FINANCIAMENTO.</font>";
$mensagem[11] = "<font class='erro'>EXISTE(M) ITEM(NS) FALTANDO O CQ / CORRIDA.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT DISTINCT(nfe.`id_nfe`), nfe.`num_nota`, nfe.`tipo`, nfe.`pago_pelo_caixa_compras`, 
                    nfe.`ignorar_impostos_financiamento`, nfe.`data_emissao`, nfe.`data_entrega`, nfe.`situacao`, 
                    f.`razaosocial`, e.`nomefantasia` 
                    FROM nfe 
                    INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_nfe` = nfe.`id_nfe` 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` AND f.`razaosocial` LIKE '%$txt_consultar%' 
                    INNER JOIN `empresas` e ON e.`id_empresa` = nfe.`id_empresa` 
                    WHERE nfe.`situacao` < '2' 
                    ORDER BY nfe.`data_emissao` DESC ";
        break;
        case 2:
            $sql = "SELECT DISTINCT(nfe.`id_nfe`), nfe.`num_nota`, nfe.`tipo`, nfe.`pago_pelo_caixa_compras`, 
                    nfe.`ignorar_impostos_financiamento`, nfe.`data_emissao`, nfe.`data_entrega`, nfe.`situacao`, 
                    f.`razaosocial`, e.`nomefantasia` 
                    FROM nfe 
                    INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_nfe` = nfe.`id_nfe` 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = nfe.`id_empresa` 
                    WHERE nfe.`situacao` < '2' 
                    AND nfe.`num_nota` LIKE '%$txt_consultar%' 
                    ORDER BY nfe.`data_emissao` DESC ";
        break;
        case 3:
            $txt_consultar = data::datatodate($txt_consultar,'-');
            
            $sql = "SELECT DISTINCT(nfe.`id_nfe`), nfe.`num_nota`, nfe.`tipo`, nfe.`pago_pelo_caixa_compras`, 
                    nfe.`ignorar_impostos_financiamento`, nfe.`data_emissao`, nfe.`data_entrega`, nfe.`situacao`, 
                    f.`razaosocial`, e.`nomefantasia` 
                    FROM nfe 
                    INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_nfe` = nfe.`id_nfe` 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = nfe.`id_empresa` 
                    WHERE nfe.situacao < '2' 
                    AND SUBSTRING(nfe.`data_emissao`, 1, 10) LIKE '%$txt_consultar%' 
                    ORDER BY nfe.`data_emissao` DESC ";
        break;
        case 4:
            $txt_consultar = data::datatodate($txt_consultar,'-');
            
            $sql = "SELECT DISTINCT(nfe.`id_nfe`), nfe.`num_nota`, nfe.`tipo`, nfe.`pago_pelo_caixa_compras`, 
                    nfe.`ignorar_impostos_financiamento`, nfe.`data_emissao`, nfe.`data_entrega`, nfe.`situacao`, 
                    f.`razaosocial`, e.`nomefantasia` 
                    FROM nfe 
                    INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_nfe` = nfe.`id_nfe` 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = nfe.`id_empresa` 
                    WHERE nfe.`situacao` < '2' 
                    AND SUBSTRING(nfe.`data_entrega`, 1, 10) LIKE '%$txt_consultar%' 
                    ORDER BY nfe.`data_entrega` DESC ";
        break;
        default:
            $sql = "SELECT DISTINCT(nfe.`id_nfe`), nfe.`num_nota`, nfe.`tipo`, nfe.`pago_pelo_caixa_compras`, 
                    nfe.`ignorar_impostos_financiamento`, nfe.`data_emissao`, nfe.`data_entrega`, nfe.`situacao`, 
                    f.`razaosocial`, e.`nomefantasia` 
                    FROM nfe 
                    INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_nfe` = nfe.`id_nfe` 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = nfe.`id_empresa` 
                    WHERE nfe.`situacao` < '2' 
                    ORDER BY nfe.`data_entrega` DESC ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'liberar.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Nota(s) Fiscal(s) de Entrada para Liberar Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Consultar Nota(s) Fiscal(is) de Entrada para Liberar Estoque
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Nota
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            <font title='Data de Emissão' style='cursor:help'>
                Data Em.
            </font>
        </td>
        <td>
            <font title='Data de Entrega' style='cursor:help'>
                Data Ent.
            </font>
        </td>
        <td>
            Empresa
        </td>
        <td>
            Pendência
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = "liberar.php?passo=2&id_nfe=".$campos[$i]['id_nfe'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = '<?=$url;?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <?=$campos[$i]['num_nota'];?>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['razaosocial'];
            //Caixa de Compras ...
            if($campos[$i]['pago_pelo_caixa_compras'] == 'S') echo '<font color="blue"><b> (CAIXA DE COMPRAS)</b></font>';
            //Só mostramos quando Ignorar Impostos no Financiamento ...
            if($campos[$i]['ignorar_impostos_financiamento'] == 'S') echo '<font color="darkred" title="Ignorar Impostos no Financiamento" style="cursor:help"><b> (IIF)</b></font>';
        ?>
        </td>
        <td>
        <?
            $data_emissao = (substr($campos[$i]['data_emissao'], 0, 10) == '0000-00-00') ? '' : data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/');
            echo $data_emissao;
        ?>
        </td>
        <td>
        <?
            $data_entrega = (substr($campos[$i]['data_entrega'], 0, 10) == '0000-00-00') ? '' : data::datetodata(substr($campos[$i]['data_entrega'], 0, 10), '/');
            echo $data_entrega;
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?> (<?
            if($campos[$i]['tipo'] == 1) {
                echo 'NFE';
            }else {
                echo 'SGD';
            }
        ?>)
        </td>
        <td>
        <?
            if($campos[$i]['situacao'] == 0) {
                echo '<font color="FF0000">TOTAL</font>';
            }else {
                echo '<font color="0000FF">PARCIAL</font>';
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'liberar.php'" class='botao'>
            <input type='button' name='cmd_antecipacoes_liberadas' value='Antecipações Liberadas' title='Antecipações Liberadas' onclick="nova_janela('../../liberadas/liberadas.php', 'POP', '', '', '', '', 500, 980, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
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
//Busca de Alguns Dados da Nota Fiscal Principal, tais como Número da NF, Tipo de Moeda, etc ...
    $sql = "SELECT nfe.`id_fornecedor`, nfe.`num_nota`, nfe.`tipo`, CONCAT(tm.`simbolo`, ' ') AS moeda 
            FROM `nfe` 
            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = nfe.`id_tipo_moeda` 
            WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_fornecedor  = $campos[0]['id_fornecedor'];
    $num_nota       = $campos[0]['num_nota'];
    $moeda          = $campos[0]['moeda'];
    
    //Tratamento para o Tipo de Nota
    if($campos[0]['tipo'] == 1) {
        $tipo = 'NF';
        $tipo_nf = 1;
    }else {
        $tipo = 'SGD';
        $tipo_nf = 2;
    }
//Busca os Item(ns) da Nota Fiscal
    $sql = "SELECT * 
            FROM `nfe_historicos` 
            WHERE `id_nfe` = '$id_nfe' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Consultar Nota(s) Fiscal(is) para Liberar Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function copiar_qtde(indice) {
    if(document.getElementById('chkt_nfe_item'+indice).checked) {//Linha está marcada ...
        document.getElementById('txt_entrada_antecipada'+indice).value = document.getElementById('hdd_entrada_antecipada'+indice).value
    }else {//Linha está desmarcada ...
        document.getElementById('txt_entrada_antecipada'+indice).value = ''
    }
}
</Script>
</head>
<body>
<form name='form' action="<?=$PHP_SELF.'?passo=3';?>" method='post' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            Itens da Nota Fiscal de Entrada
            <a href = '../itens/index.php?id_nfe=<?=$id_nfe;?>&pop_up=1' class='html5lightbox'>
                <font color='yellow'>
                    <?=$num_nota;?>
                </font> 
                <img src = '../../../../../imagem/propriedades.png' title='Detalhes da Nota Fiscal' alt='Detalhes da Nota Fiscal' border='0'>
            </a>
            p/ Liberar Estoque
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type = 'checkbox' name='chkt_tudo' title='Selecionar Tudo' onclick="selecionar_tudo('form', 'chkt_tudo', totallinhas, '#E8E8E8');document.form.chkt_aberto.checked = false" class='checkbox'>
        </td>
        <td>
            Qtde NF
        </td>
        <td>
            Ent Antecipada
        </td>
        <td>
            Qtde Conf
        </td>
        <td>
            Qtde EC Atrel
        </td>
        <td>
            Un
        </td>
        <td>
            Produto
        </td>
        <td>
            Pre&ccedil;o Unit.
        </td>
        <td>
            Valor Total
        </td>
        <td>
            IPI %
        </td>
        <td>
            ICMS %
        </td>
        <td>
            IVA
        </td>
        <td>
            Marca / Obs
        </td>
        <td>
            N.º Ped / OS
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            //Esse SQL busca alguns dados de Produto Insumo que serão utilizados no decorrer desse Loop ...
            $sql = "SELECT g.`referencia`, ip.*, pi.`discriminacao`, pi.`credito_icms`, u.`sigla` 
                    FROM `itens_pedidos` ip 
                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE ip.id_item_pedido = '".$campos[$i]['id_item_pedido']."' LIMIT 1 ";
            $campos_itens = bancos::sql($sql);
            
            if($campos[$i]['status'] == 0) {//Se o Item de NF ainda não foi liberado para Estoque ...
                $indice = (!isset($indice)) ? 0 : ($indice + 1);
            }
?>
    <tr class='linhanormal' onclick="checkbox('form', '<?=$i;?>', '<?=$indice;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
        <?
            if($campos[$i]['status'] == 0) {//Significa que o Item de NF ainda não foi liberado para Estoque
        ?>
                <input type='checkbox' name='chkt_nfe_item[]' id='chkt_nfe_item<?=$indice;?>' value="<?=$campos[$i]['id_nfe_historico'];?>" onclick="checkbox('form', '<?=$i;?>', '<?=$indice;?>', '#E8E8E8')" class='checkbox'>
        <?
            }else {//Esse hidden é colocado aqui, justamente para não dar erro de índice ...
                echo "<font color = 'blue'>LIBERADO</font>";
        ?>
                <input type='hidden'>
        <?
            }
        ?>
        </td>
        <td align='right'>
            <a href="javascript:copiar_qtde('<?=$indice;?>')" title='Copiar "Quantidade NF" p/ "Ent Antecipada"' style='cursor:help' class='link'>
                <?=number_format($campos[$i]['qtde_entregue'], 2, ',', '.');?>
            </a>
        </td>
        <td>
            <?
                //Só existe casas decimais quando a Unidade do PA = Kilo ...
                $onkeyup = ($campos_itens[0]['sigla'] == 'KG') ? "verifica(this, 'moeda_especial', '2', '0', event)" : "verifica(this, 'aceita', 'numeros', '', event)";
            ?>
            <input type='text' name='txt_entrada_antecipada[]' id='txt_entrada_antecipada<?=$indice;?>' title='Digite a Entrada Antecipada' onclick="checkbox('form', '<?=$i;?>', '<?=$indice;?>', '#E8E8E8');return focos(this)" onkeyup="<?=$onkeyup;?>" size='12' class='textdisabled' disabled>
        </td>
        <td align='right'>
        <?
/*****Comparação para ver qual campo de Conferência da NF que eu vou exibir e fazer a Comparação*****/
//Aqui eu verifico se este item é PRAC, caso não seje o campo que eu vou apresentar é do Tipo Aço ...
            $sql = "SELECT `id_produto_acabado` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_prac = bancos::sql($sql);
            if(count($campos_prac) == 1) {//Esse Item é um PRAC ...
                //Enquanto a qtde Digitada for Diferente do Valor da Nota, está mantém a fonte na cor Vermelha ...
                $font = ($campos[$i]['qtde_prac_conf'] != $campos[$i]['qtde_entregue']) ?  '<font color="red"><b>' : '';
                
                echo $font.number_format($campos[$i]['qtde_prac_conf'], 2, ',', '.');
                //Se for um PA, eu já aproveito p/ buscar o EC desse PA ...
                $retorno            = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos_prac[0]['id_produto_acabado'], 0);
                $est_comprometido   = $retorno['total_ec_pas_atrelados'];
                
                $tipo_de_produto    = 'PA';
            }else {//Esse Item é um PI ...
                //Verifico se o PI é um Blank ...
                if($campos_itens[0]['referencia'] == 'BLANK') {
                    /*Se sim verifico se esse PI está sendo utilizado em algum Custo de algum PA que seja 
                    normal de Linha em sua 3ª Etapa ...*/
                    $sql = "SELECT pac.`id_produto_acabado` 
                            FROM `produtos_acabados_custos` pac 
                            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` AND pa.`referencia` <> 'ESP' 
                            INNER JOIN `pacs_vs_pis` pp ON pp.`id_produto_acabado_custo` = pac.`id_produto_acabado_custo` AND pp.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                            LIMIT 1 ";
                    $campos_etapa3 = bancos::sql($sql);
                    if(count($campos_etapa3[0]['id_produto_acabado']) == 1) {
                        //Se for um PA, eu já aproveito p/ buscar o EC desse PA ...
                        $retorno            = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos_etapa3[0]['id_produto_acabado'], 0);
                        $est_comprometido   = $retorno['total_ec_pas_atrelados'];
                    }
                }
                //Verifico se o PI que está no item da NF é do Tipo Aço ...
                $sql = "SELECT `id_produto_insumo_vs_aco` 
                        FROM `produtos_insumos_vs_acos` 
                        WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                $campos_aco = bancos::sql($sql);
                //Se o PI for do Tipo Aço, então exibo um link para que se possa enxergar toda a Conferência do PI ...
                if(count($campos_aco) == 1) {
/*Tenho que passar esse parâmetro "nao_exibir_voltar" para que não exiba o botão de << Voltar << nessa tela 
que eu estou chamando e assim também evitar o erro*/
?>
                    <a href="javascript:nova_janela('../itens/conferencia_entrega_pi.php?id_nfe=<?=$id_nfe;?>&nao_exibir_voltar=1', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Conferência de Entrega do PI (Aço)' style='cursor:help'>
<?
                }
//Enquanto o Peso + 2% Adm. Kg For Menor do Valor da Nota, está mantém a caixa na cor Vermelha
                if($campos[$i]['peso_2_porc'] < $campos[$i]['qtde_entregue']) {
                    $font = '<font color="red"><b>';
                }else {
                    $font = '<font><b>';
                }
                echo $font.number_format($campos[$i]['peso_2_porc'], 2, ',', '.');
                
                $tipo_de_produto    = 'PI';
            }
/*****************************************************************************************************/
        ?>
        </td>
        <td align='right'>
        <?
            if($est_comprometido < 0) echo '<font color="red"><b>';
            echo number_format($est_comprometido, 0, ',', '.');
        ?>
        </td>
        <td align='left'>
            <?=$campos_itens[0]['sigla'];?>
        </td>
        <td align='left'>
        <?
            echo genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos_itens[0]['referencia']).' * ';
            echo $campos_itens[0]['discriminacao'];
//Impressão do Tipo de Ajuste na Tela ...
            if(!empty($campos[$i]['cod_tipo_ajuste'])) {//Certifico que ele não foi deletado, Luis ..
                echo ' - <b>'.$tipos_ajustes[$campos[$i]['cod_tipo_ajuste']][1];
//Se o Tipo de Ajuste = 'Abatimento de NF' então eu exibo o N.º da NF ...
                if($campos[$i]['cod_tipo_ajuste'] == 4) echo ' => '.$campos[$i]['nf_obs_abatimento'];
            }
/**********************OP**********************/
//Verifico se esse Item está atrelado a alguma OP ...
            $sql = "SELECT `id_op` 
                    FROM `oss_itens` 
                    WHERE `id_item_pedido` = '".$campos[$i]['id_item_pedido']."' LIMIT 1 ";
            $campos_ops = bancos::sql($sql);
            if(count($campos_ops) == 1) echo ' / <b>OP N.º</b> '.$campos_ops[0]['id_op'];
/**********************************************/
//Significa que é um Produto do Tipo não Estocável
            if($campos_itens[0]['estocar'] == 0) {
//Se eu não vou estocar, esse Produto, então significa que este vai para alguém, então busco p/ qual fornec
                if($campos_itens[0]['id_fornecedor_terceiro'] != 0) {
//Busca o nome do Fornecedor que deve ser cobrado
                    $sql = "SELECT `razaosocial` 
                            FROM `fornecedores` 
                            WHERE `id_fornecedor` = '".$campos_itens[0]['id_fornecedor_terceiro']."' LIMIT 1 ";
                    $campos_fornecedor = bancos::sql($sql);
                }
                echo "<font color='red' title='Não Estocar - Enviar p/: ".$campos_fornecedor[0]['razaosocial']."' style='cursor:help'><b> (N.E) </b></font>";
            }
//Significa que esse Produto tem débito com Fornecedor
            if($campos_itens[0]['id_fornecedor'] != 0) {
//Busca o nome do Fornecedor que deve ser cobrado
                $sql = "SELECT `razaosocial` 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` = '".$campos_itens[0]['id_fornecedor']."' LIMIT 1 ";
                $campos_fornecedor 	= bancos::sql($sql);
//Também busco o N.º da Nota Fiscal deste Fornecedor de qual vai ser cobrado
                $sql = "SELECT DISTINCT(nfe.`num_nota`) 
                        FROM `nfe_historicos` nh 
                        INNER JOIN `nfe` ON nh.`id_nfe_debitar` = nfe.`id_nfe` 
                        WHERE nh.id_nfe_debitar = '".$campos[$i]['id_nfe_debitar']."' LIMIT 1 ";
                $campos_nfe = bancos::sql($sql);
                if(count($campos_nfe) == 1) {//Se já estiver atrelado a outra Nota Fiscal ...
                    $num_nota_fiscal = $campos_nfe[0]['num_nota'];
                    echo "<font color='red' title='Debitar do(a): ".$campos_fornecedor[0]['razaosocial']." - Nota Fiscal N.º $num_nota_fiscal' style='cursor:help'><b> (DEB) </b></font>";
                }else {//Se ainda não estiver atrelada a nenhuma outra Nota, então ...
                    echo "<font color='red' title='Debitar do(a): ".$campos_fornecedor[0]['razaosocial']." - S/ N.º de Nota Fiscal atrelada' style='cursor:help'><b> (DEB) </b></font>";
                }
            }
            //Só se a referência = 'ACO' que o sistema irá verificar o Controle de Qualidade ...
            if(genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos_itens[0]['referencia']) == 'ACO') {
                if(empty($campos[$i]['num_corrida'])) echo "<font color='red' title='Falta Controle de Qualidade' style='cursor:help'> <b>(Falta CQ)</b></font>";
            }
        ?>
        </td>
        <td align='right'>
            <?=$moeda.number_format($campos[$i]['valor_entregue'], '2', ',', '.');?>
        </td>
        <td align='right'>
        <?
            $total = $campos[$i]['qtde_entregue'] * $campos[$i]['valor_entregue'];
            echo $moeda.number_format($total, '2', ',', '.');
        ?>
        </td>
        <td>
        <?
            if(($campos[$i]['ipi_entregue'] == '0.00') or ($tipo == 'SGD')) {//SGD
                echo '&nbsp;';
            }else {//NF
                echo number_format($campos[$i]['ipi_entregue'], 2, ',', '.');
            }
//Cálculo só da Parte do IPI
            if($tipo == 'SGD') {//SGD
                $ipi = 0;
            }else {//NF
                $ipi = $campos[$i]['ipi_entregue'];
            }
        ?>
        </td>
        <td>
        <?
            if(($campos[$i]['icms_entregue'] == '0.00') or ($tipo == 'SGD')) {//SGD
                echo '&nbsp;';
            }else {//NF
                echo number_format($campos[$i]['icms_entregue'], 2, ',', '.');
//Aqui eu verifico o Crédito de ICMS diretamente do PI ...
                if($campos_itens[0]['credito_icms'] == 0) echo '<font color="red" title="Sem Crédito ICMS" style="cursor:help"><b> (S.C)</b></font>';
            }
//Cálculo só da Parte do ICMS
            if($tipo == 'SGD') {//SGD
                $icms = 0;
            }else {//NF
                $icms = $campos[$i]['icms_entregue'];
            }
        ?>
        </td>
        <td>
        <?
            if(($campos[$i]['iva'] == '0.00') or ($tipo == 'SGD')) {//SGD
                echo '&nbsp;';
            }else {
                echo number_format($campos[$i]['iva'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['marca'];?>
        </td>
        <td>
        <?
            echo $campos[$i]['id_pedido'];
//Verifico se esta OS está atrelada em algum Pedido ...
            $sql = "SELECT `id_os` 
                    FROM `oss` 
                    WHERE `id_pedido` = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
            $campos_os = bancos::sql($sql);
            //Encontrou a OS em um Pedido, então eu printo o N. da OS e Ped
            if(count($campos_os) == 1) echo ' / '."<font color='red'>".$campos_os[0]['id_os']."</font>";
        ?>
            <!--**********************Controles de Tela**********************-->
            <input type='hidden' name='hdd_entrada_antecipada[]' id='hdd_entrada_antecipada<?=$indice;?>' value='<?=number_format($campos[$i]['qtde_entregue'], 2, ',', '.');?>' disabled>
            <input type='hidden' name='hdd_tipo_de_produto[]' id='hdd_tipo_de_produto<?=$indice;?>' value='<?=$tipo_de_produto;?>' disabled>
            <!--*************************************************************-->
        </td>
    </tr>
<?
            $est_comprometido = '';//Limpo essa variável p/ não herdar valores do Loop anterior ...
            
            /*Sempre deleto essa variável para que a mesma não acumule valor dos Loops anteriores, ela não se 
            encontra aqui nessa tela, mais é reconhecida aqui porque foi declarada de forma global dentro 
            da Biblioteca de Custos, na função pas_atrelados ...*/
            unset($vetor_pas_atrelados);
        }
        $calculo_total_impostos = calculos::calculo_impostos(0, $id_nfe, 'NFC');
?>
    <tr class='linhadestaque'>
        <td colspan='14'>
            <input type='checkbox' name='chkt_aberto' id='chkt_aberto' value='1' class='checkbox'>
            <label for='chkt_aberto'>Clique aqui para concluir ou fechar a Nota</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'liberar.php<?=$parametro;?>'" class='botao'>
            <input type='submit' name='cmd_liberar' value='Liberar' title='Liberar' class='botao'>
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='14'>
            <iframe name='detalhes' id='detalhes' src = '/erp/albafer/modulo/classes/follow_ups/detalhes.php?identificacao=<?=$id_nfe;?>&origem=17' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
</table>
<!--Aqui no hidden eu sempre guardo o id_nfe da Primeira NF de Entrada que é a Principal p/ não sobrepor 
com o id_nfe logo abaixo casos esse exista que aí dá caca se eu guardar aqui ...-->
<input type='hidden' name='id_nfe' value='<?=$id_nfe;?>'>
<?
/*****************************************************************************************/
/*Aqui eu faço a listagem dos Itens que foram atrelados a essa Nota, através de outra Nota 
como valores que tem que ser debitados do fornecedor - "Nota Fiscal Debitar"*/
    $sql = "SELECT `id_nfe` 
            FROM `nfe_historicos` 
            WHERE `id_nfe_debitar` = '$id_nfe' LIMIT 1 ";
    $campos_itens_atrelados = bancos::sql($sql);
    $linhas_itens_atrelados = count($campos_itens_atrelados);
    if($linhas_itens_atrelados == 1) {
?>
<pre>

* Listagem de Item(ns) que foram atrelados a essa Nota, mas por meio de outra(s) Nota(s)
* Para excluir um item atrelado errado, vá a NF de origem e exclua e inclua o item novamente

<font face='arial' color='red' size='3'><b><center>ITENS P/ DEBITAR DESTA NOTA FISCAL</center></b></font>
</pre>
<iframe src="../itens/itens.php?id_nfe=<?=$campos_itens_atrelados[0]['id_nfe'];?>&pop_up=1" marginwidth="0" marginheight="0" frameborder="0" height="580" width="100%" scrolling="auto"></iframe>
<?
    }
    echo compras_new::verificar_irregularidades_nfe($id_nfe);
?>
<!--*********************************************************************************************-->
<!--Joguei essa function aki em baixo, devido algumas variáveis q são carregadas + acima pelo PHP-->
<!--*********************************************************************************************-->
<Script Language = 'Javascript'>
function validar() {
    var elementos                   = document.form.elements
    //Significa que está tela foi carregada com apenas 1 linha ...
    var linhas                      = (typeof(elementos['chkt_nfe_item[]'][0]) == 'undefined') ? 1 : elementos['chkt_nfe_item[]'].length
    var total_itens_nfe_marcados    = 0
    
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_nfe_item'+i).checked) {
            if(document.getElementById('txt_entrada_antecipada'+i).value != '') {//Esse controle só será feito se esse campo estiver preenchido ...
                var txt_entrada_antecipada  = eval(strtofloat(document.getElementById('txt_entrada_antecipada'+i).value))
                var hdd_entrada_antecipada  = eval(strtofloat(document.getElementById('hdd_entrada_antecipada'+i).value))

                if(txt_entrada_antecipada > hdd_entrada_antecipada) {
                    alert('ENTRADA ANTECIPADA INVÁLIDA !!!\n\nENTRADA ANTECIPADA MAIOR DO QUE A QUANTIDADE NF !')
                    document.getElementById('txt_entrada_antecipada'+i).focus()
                    document.getElementById('txt_entrada_antecipada'+i).select()
                    return false
                }
            }
            total_itens_nfe_marcados++
        }
    }
    
    if(total_itens_nfe_marcados == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
/*******************************************************************************************************/
        var valor_total_nota_principal  = eval('<?=$calculo_total_impostos['valor_total_nota'];?>')

        /*if(document.form.chkt_aberto.checked == true) {//Se o checkbox Concluir ou Fechar a Nota estiver marcado ...
            var id_funcionario  = eval('<?=$_SESSION[id_funcionario];?>')
            //O funcionário 64 "Fábio Petroni", só podem liberar NF(s) de Entrada que sejam < que R$ 1.000,00 ...
            if(id_funcionario == 64) {
                if(valor_total_nota_principal > 1000) {
                    alert('SOMENTE A DIRETORIA QUE PODE LIBERAR NOTAS FISCAIS COM VALOR TOTAL ACIMA DE R$ 1.000,00 !')
                    document.form.chkt_aberto.checked = false
                    return false
                }
            }
        }*/
/*******************************************************************************************************/
/*Qtde de Itens que foram atrelados a essa Nota, através de outra Nota como valores que tem que ser debitados 
do fornecedor - "Nota Fiscal Debitar" ...*/
        var linhas_itens_atrelados      = eval('<?=$linhas_itens_atrelados;?>')
//Se existir pelo menos 1 item q foi atrelado a essa Nota, então essa Nota terá que ser de valor Negativo ...
        if(linhas_itens_atrelados > 0) {
//Se a Nota não tiver Valor Negativo então essa nota será inválida ...
            if(valor_total_nota_principal >= 0) {
                alert('VALOR DE NOTA FISCAL INVÁLIDO !!!\nESSA NOTA FISCAL TEM QUE SER DE VALOR NEGATIVO !')
                return false
            }
        }
//Conferência p/ a liberação dos Itens de Nota no Estoque ...
        var mensagem = confirm('DESEJA LIBERAR ESTE(S) ITEN(S) DE NOTA NO ESTOQUE ?')
        if(!mensagem == true) {
            return false
        }else {
            //Preparo os campos p/ poder gravar no Banco de Dados ...
            for(var i = 0; i < linhas; i++) {
                if(document.getElementById('chkt_nfe_item'+i).checked) {
                    if(document.getElementById('txt_entrada_antecipada'+i).value != '') {
                        document.getElementById('txt_entrada_antecipada'+i).value = strtofloat(document.getElementById('txt_entrada_antecipada'+i).value)
                    }
                    total_itens_nfe_marcados++
                }
            }
        }
    }
}
</Script>
<!--*********************************************************************************************-->
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
}else if($passo == 3) {
    $data_sys       = date('Y-m-d H:i:s');
    $contador       = 0;
    $linhas         = count($_POST['chkt_nfe_item']);
    
    for($i = 0; $i < $linhas; $i++) {
        //Busca do PI da Nota Fiscal ...
        $sql = "SELECT `id_produto_insumo` 
                FROM `nfe_historicos` 
                WHERE `id_nfe_historico` = '".$_POST['chkt_nfe_item'][$i]."' LIMIT 1 ";
        $campos_pi          = bancos::sql($sql);
        $id_produto_insumo  = $campos_pi[0]['id_produto_insumo'];
        //Aqui eu verifico se o PI é um PA ...
        $sql = "SELECT `id_produto_acabado` 
                FROM `produtos_acabados` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' 
                AND `id_produto_insumo` > '0' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_pipa = bancos::sql($sql);
        if(count($campos_pipa) == 0) {//Não é PA ...
            //Busca a Qtde em Estoque do Produto Insumo ...
            $sql = "SELECT `qtde` 
                    FROM `estoques_insumos` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
            $campos_estoque = bancos::sql($sql);
            if(count($campos_estoque) == 0) {//Se nao tem nada nesta tabela ela cria um para referencia somente ...
                $sql = "INSERT INTO `estoques_insumos` (`id_estoque_insumo`, `id_produto_insumo`, `qtde`, `data_atualizacao`) VALUES (NULL, '$id_produto_insumo', '0', '$data_sys') ";
                bancos::sql($sql);
            }else {
                $qtde_estoque 	= (count($campos_estoque) == 0) ? 0 : $campos_estoque[0]['qtde'];
            }
            //Aqui eu verifico se o status do Item = 0 e a NF está com situacao = 0 ou 1 ...
            $sql = "SELECT ip.`id_item_pedido`, ip.`estocar`, ip.`id_fornecedor_terceiro`, nfeh.`qtde_entregue`, nfe.`num_nota` 
                    FROM `nfe_historicos` nfeh 
                    INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` AND nfe.`situacao` IN (0, 1) 
                    INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nfeh.`id_item_pedido` 
                    WHERE nfeh.`id_nfe_historico` = '".$_POST['chkt_nfe_item'][$i]."' 
                    AND nfeh.`status` = '0' LIMIT 1 ";
            $campos         = bancos::sql($sql);
            $estoque_final  = $qtde_estoque + $campos[0]['qtde_entregue'];
            
            /*************Liberando item de Nota Fiscal PI*************/
            //Mudo o status do Item da Nota Fiscal p/ Liberado, nesse caso é um PI ...
            $sql = "UPDATE `nfe_historicos` SET `status` = '1' WHERE `id_nfe_historico` = '".$_POST['chkt_nfe_item'][$i]."' LIMIT 1 ";
            bancos::sql($sql);
            /**********************************************************/
            
            if($campos[0]['estocar'] == 1) {//Atualiza o Estoque ...
                //Inserindo os Dados no BD ...						
                $sql = "INSERT INTO `baixas_manipulacoes` (`id_baixa_manipulacao`, `id_produto_insumo`, `id_funcionario`, `id_funcionario_retirado`, `retirado_por`, `qtde`, `estoque_final`, `observacao`, `acao`, `status`, `troca`, `data_sys`) 
                        VALUES (NULL , '$id_produto_insumo', '$_SESSION[id_funcionario]', '$_SESSION[id_funcionario]', '', '".$campos[0]['qtde_entregue']."', '$estoque_final', 'Nota N.º ".$campos[0]['num_nota']." liberada.', 'L', '0', 'S', '$data_sys') ";
                bancos::sql($sql);
            }else {
                if($campos[0]['id_fornecedor_terceiro'] != 0 && !empty($campos[0]['id_fornecedor_terceiro'])) {
                    $sql = "SELECT `razaosocial` 
                            FROM `fornecedores` 
                            WHERE `id_fornecedor` = ".$campos[0]['id_fornecedor_terceiro']." LIMIT 1 ";
                    $campos_fornec = bancos::sql($sql);
                    $observacao = "Material enviado para ".$campos_fornec[0]['razaosocial']." com a nota N.º ".$campos[0]['num_nota'];
                    $sql = "INSERT INTO `baixas_manipulacoes` (`id_baixa_manipulacao`, `id_produto_insumo`, `id_funcionario`, `id_funcionario_retirado`, `retirado_por`, `qtde`, `estoque_final`, `observacao`, `acao`, `status`, `troca`, `data_sys`) 
                            VALUES (NULL , '$id_produto_insumo', '$_SESSION[id_funcionario]', '$_SESSION[id_funcionario]', '', '-".$campos[0]['qtde_entregue']."', '$estoque_final', '$observacao', 'M', '1', 'S', '$data_sys') ";
                    bancos::sql($sql);
                }
            }
        }
        $retorno = estoque_ic::atualizar(0, $_POST['id_nfe'], $_POST['chkt_nfe_item'][$i]);
        //Este é um PI que é PA "PIPA" ...
        if(count($campos_pipa) == 1) {
            intermodular::gravar_campos_para_calcular_margem_lucro_estimada($id_produto_insumo);
            
            if($_POST['txt_entrada_antecipada'][$i] > 0) {//Significa que este campo foi preenchido pelo Usuário ...
                //Atualizo o Item da Nota Fiscal com a Entrada Antecipada que foi digitada pelo usuário ...
                $sql = "UPDATE `nfe_historicos` SET `entrada_antecipada` = '".$_POST['txt_entrada_antecipada'][$i]."' WHERE `id_nfe_historico` = '".$_POST['chkt_nfe_item'][$i]."' LIMIT 1 ";
                bancos::sql($sql);
                
                //Como sendo a última ação do PA, atualizo o campo Entrada Antecipada do PA na tabela de "estoques_acabados" ...
                $sql = "UPDATE `estoques_acabados` SET `entrada_antecipada` = `entrada_antecipada` + ".$_POST['txt_entrada_antecipada'][$i]." WHERE `id_produto_acabado` = '".$campos_pipa[0]['id_produto_acabado']."' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
        $contador = 1;
    }
//Significa que não conseguiu liberar o Estoque do PA ...
    if($retorno == 1) {
        $valor = 9;
    }else {//Liberado com sucesso ...
        //Verifico se essa NF foi feita pelo modo de Financiamento ...
        $sql = "SELECT `id_nfe_financiamento` 
                FROM `nfe_financiamentos` 
                WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
        $campos_financiamento = bancos::sql($sql);
        if(count($campos_financiamento) == 0) {//Se não tem pelo menos 1 Parcela de Vencimento então não posso a NF ...
            $valor = 10;
        }else {
            //Verifico se existe algum item em aberto na Nota Fiscal
            $sql = "SELECT `id_nfe_historico` 
                    FROM nfe_historicos 
                    WHERE `id_nfe` = '$_POST[id_nfe]' 
                    AND `status` = '0' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Não tem nenhum item em aberto para a Nota, então posso concluir Total
/*Verifico se a Nota Fiscal corrente possui algum item com a marcação de DEB e se esse item já 
está atrelado à alguma Nota Fiscal*/
                $sql = "SELECT nh.`id_nfe_historico` 
                        FROM `nfe_historicos` nh 
                        INNER JOIN `itens_pedidos` ip ON ip.`id_item_pedido` = nh.`id_item_pedido` AND ip.`id_fornecedor` <> '0' 
                        WHERE nh.`id_nfe` = '$_POST[id_nfe]' 
                        AND nh.`id_nfe_debitar` IS NULL LIMIT 1 ";
                $campos_itens_deb = bancos::sql($sql);
                $linhas_itens_deb = count($campos_itens_deb);
/*Se existir pelo menos 1 com a Marcação de DEB, e esse ainda não estiver atrelado a uma NF, então o que 
deve ser feito primeiro é atrelar esse Item a uma Nota Fiscal*/
                if($linhas_itens_deb == 1) {
                    $valor = 7;
//Todos os Itens DEB(s) estão atrelados a uma Nota, sendo assim ...
                }else {
/*Verifico se a Nota Fiscal corrente possui NFS atrelada(s) a ela, e se esta(s) Nota(s) Fiscal(is) 
já foram liberadas*/
                    $sql = "SELECT nh.`id_nfe_debitar` 
                            FROM `nfe_historicos` nh 
                            INNER JOIN `nfe` ON nfe.`id_nfe` = nh.`id_nfe_debitar` AND nfe.`situacao` < '2' 
                            WHERE nh.`id_nfe` = '$_POST[id_nfe]' 
                            AND nh.`id_nfe_debitar` > '0' LIMIT 1 ";
                    $campos_itens_atrelados = bancos::sql($sql);
                    $linhas_itens_atrelados = count($campos_itens_atrelados);
/*Se existir pelo menos 1 Nota Fiscal em aberto, eu ainda não posso concluir essa Nota NF, o que tem 
que ser feito primeiro é concluir essas Notas atreladas p/ depois se concluir a Nota Principal*/
                    if($linhas_itens_atrelados == 1) {
                        $valor = 8;
                    }else {
                        if($_POST['chkt_aberto'] == 1) {//Quis concluir a Nota Fiscal
                            //Verifico se temos algum item de referência aço que esteja sem CQ / Corrida preenchida ...
                            $sql = "SELECT nfeh.`id_nfe_historico` 
                                    FROM `nfe_historicos` nfeh 
                                    INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = nfeh.`id_produto_insumo` AND pi.`id_grupo` = '5' 
                                    WHERE nfeh.`id_nfe` = '$_POST[id_nfe]' 
                                    AND nfeh.`num_corrida` = '' LIMIT 1 ";
                            $campos_sem_corrida = bancos::sql($sql);
                            if(count($campos_sem_corrida) == 1) {//Existe algum item que está sem CQ / Corrida Preenchida, sendo assim não posso Liberar a NF ...
                                $valor = 11;
                            }else {//Todos os itens estão com CQ / Corrida Preenchida, sendo assim posso Liberar a NF ...
                                $sql = "UPDATE `nfe` SET `situacao` = '2' WHERE `id_nfe`= '$_POST[id_nfe]' LIMIT 1 ";
                                $valor = 3;
                            }
                        }else {//Quis permanecer com a Nota Fiscal em aberto
                            $sql = "UPDATE `nfe` SET `situacao` = '1' WHERE `id_nfe`= '$_POST[id_nfe]' LIMIT 1 ";
                            $valor = 2;
                        }
                    }
                    bancos::sql($sql);
                }
            }else {//Ainda existem itens em aberto
                if($contador > 0) {//A Nota Fiscal continua em aberto
                    $sql = "UPDATE `nfe` SET `situacao` = '1' WHERE `id_nfe`= '$_POST[id_nfe]' limit 1";
                    bancos::sql($sql);
                    $valor = 4;
                }
                if($_POST['chkt_aberto'] == 1) {//Um Fecha a Nota ...
                    $valor = ($contador > 0) ? 5 : 6;
                }
            }
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'liberar.php?passo=1&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Nota(s) Fiscal(is) para Liberar Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 4; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 4;i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
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
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Nota(s) Fiscal(is) para Liberar Estoque
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' title='Consultar Nota Fiscal de Entrada' size='45' maxlength='45' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="1" title="Consultar notas fiscais de entrada por: Fornecedor" onclick='document.form.txt_consultar.focus()' id='label' disabled checked>
            <label for="label">Fornecedor</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="2" title="Consultar notas fiscais de entrada por: Número da Nota" onclick='document.form.txt_consultar.focus()' id='label2' disabled>
            <label for="label2">Número da Nota</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="3" title="Consultar notas fiscais de entrada por: Data de Emissão" onclick='document.form.txt_consultar.focus()' id='label3' disabled>
            <label for="label3">Data de Emissão</label>
        </td>
        <td>
            <input type='radio' name='opt_opcao' value="4" title="Consultar notas fiscais de entrada por: Data de Entrega" onclick='document.form.txt_consultar.focus()' id='label4' disabled>
            <label for="label4">Data de Entrega</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='1' title='Consultar todas as Notas Fiscais' onclick='limpar()' class='checkbox' id='label5' checked>
            <label for='label5'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false; limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>