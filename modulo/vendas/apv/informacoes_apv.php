<?
require('../../../lib/segurancas.php');
//pop_up = 1, significa que essa Tela foi aberta como sendo Pop-UP e por isso não exibo o menu no início da Página ...
if(empty($pop_up)) require('../../../lib/menu/menu.php');
require('../../../lib/calculos.php');
require('../../../lib/estoque_acabado.php');
require('../../../lib/faturamentos.php');
require('../../../lib/financeiros.php');
require('../../../lib/intermodular.php');
require('../../../lib/genericas.php');
require('../../../lib/data.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral('/erp/albafer/modulo/vendas/apv/apv.php', '../../../');
?>
<html>
<head>
<title>.:: Relatório APV ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function imprimir() {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA IMPRIMIR ?')
    if(resposta == true) document.form.action = 'relatorio_pdf.php'
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return imprimir()'>
<!--********************************Macete para quando entrar nessa Tela********************************-->
<?
/*Significa que o Cliente selecionado na Tela anterior foi através de um link e não através
de um checkbox - Transformo em Vetor p/ não furar a lógica abaixo ...*/
if(!empty($id_clientes)) $chkt_cliente = explode(',', $id_clientes);

//Essa rotina só vai acontecer na primeira vez em que cair nessa Tela
if(empty($cliente_selecionados)) {
    foreach($chkt_cliente as $id_cliente) $cliente_selecionados.= $id_cliente.', ';
    //Marcador de Impressão
    $cliente_selecionados = substr($cliente_selecionados, 0, strlen($cliente_selecionados) - 2);
}
?>
<!--****************************************************************************************************-->
<!--Eu guardo esse valor de id_cliente aqui nesse hidden, por causa do(s) Pop-Up(s) que são abertos por cima 
dessa tela, quando eu fecho esses Pop-Up(s), ele submete esse arquivo, e se eu não tiver esse id_cliente 
guardado, então dá problema de loop do foreach, por causa de parâmetro-->
<input type='hidden' name='cliente_selecionados' value='<?=$cliente_selecionados;?>'>
<!--Significa que essa tela foi aberta como sendo um Pop-UP-->
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<!--Parâmetro que vem da Tela de TeleMarketing ...-->
<input type='hidden' name='telemarketing' value='<?=$telemarketing;?>'>
<!--Quem controla esse hidden controle é o arquivo alterar_contatos ...-->
<input type='hidden' name='controle'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <font color='yellow'>
                <b>APV (Atendimento Planejado de Vendas)</b>
            </font>
        </td>
    </tr>
</table>
<!--************************Macete para as demais vezes que estiver nessa tela************************-->
<?
//Significa que essa tela já foi submetida pelo menos 1 vez - Transforma em Array a Determinada String ...
if(!empty($cliente_selecionados)) $chkt_cliente = explode(',', $cliente_selecionados);

//Aqui eu continuo a rotina normalmente
//Disparo de loop para os Clientes Selecionados
foreach($chkt_cliente as $id_cliente) { //Marcador de Impressão
    //Aki é a busca dos Dados do Cliente ...
    $sql = "SELECT * 
            FROM `clientes` 
            WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    //Aqui tem q ser id_func na variável para não dar conflito com a variável da sessão
    $id_cliente_tipo    = $campos[0]['id_cliente_tipo'];
    $nome_fantasia      = $campos[0]['nomefantasia'];
    $razao_social       = $campos[0]['razaosocial'];
    $credito            = $campos[0]['credito'];
    $limite_credito     = $campos[0]['limite_credito'];
    $credito_data       = $campos[0]['credito_data'];
    $observacao_credito = $campos[0]['credito_observacao'];
    $suframa            = $campos[0]['cod_suframa'];
    //Dados de Endereço
    $id_pais            = $campos[0]['id_pais'];
    //Significa que o Cliente é do Tipo Internacional
    $tipo_moeda         = ($id_pais != 31) ? 'U$ ' : 'R$ ';
    $cep                = $campos[0]['cep'];
    $numero_complemento = $campos[0]['num_complemento'];
    $endereco           = $campos[0]['endereco'];
    $bairro             = $campos[0]['bairro'];
    $cidade             = $campos[0]['cidade'];
    $id_uf_cliente      = $campos[0]['id_uf'];

    $sql = "SELECT `sigla` 
            FROM `ufs` 
            WHERE `id_uf` = '$id_uf_cliente' LIMIT 1 ";
    $campos_uf          = bancos::sql($sql);
    $estado             = $campos_uf[0]['sigla'];

    $sql = "SELECT `pais` 
            FROM `paises` 
            WHERE `id_pais` = '$id_pais' LIMIT 1 ";
    $campos_pais        = bancos::sql($sql);
    $pais               = $campos_pais[0]['pais'];

    $ddi_com 		= $campos[0]['ddi_com'];
    $ddd_com 		= $campos[0]['ddd_com'];
    $telcom 		= $campos[0]['telcom'];
    $telefone_com       = '('.$ddi_com.'-'.$ddd_com.') '.$telcom;
    $ddi_fax 		= $campos[0]['ddi_fax'];
    $ddd_fax 		= $campos[0]['ddd_fax'];
    $telfax 		= $campos[0]['telfax'];
    $telefone_fax       = '('.$ddi_fax.'-'.$ddd_fax.') '.$telfax;
    $email              = $campos[0]['email'];
    $email_nfe 		= $campos[0]['email_nfe'];
    $pagina_web         = $campos[0]['pagweb'];
    
    $inscricao_estadual     = ($campos[0]['insc_estadual'] == 0) ? '' : substr($campos[0]['insc_estadual'], 0, 3).'.'.substr($campos[0]['insc_estadual'], 3, 3).'.'.substr($campos[0]['insc_estadual'], 6, 3).'.'.substr($campos[0]['insc_estadual'], 9, 3);
    $inscricao_municipal    = ($campos[0]['insc_municipal'] == 0) ? '' : $campos[0]['insc_municipal'];
    
    if(!empty($campos[0]['cnpj_cpf'])) {
        if(strlen($campos[0]['cnpj_cpf']) == 11) {//CPF ...
            $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
        }else {//CNPJ ...
            $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
        }
    }else {
        $cnpj_cpf = '';
    }
    
    $ccm                    = ($campos[0]['ccm'] == 0) ? '' : $campos[0]['ccm'];
    $rg                     = $campos[0]['rg'];
    $orgao                  = $campos[0]['orgao'];
?>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhadestaque'>
        <td colspan='3'>
            <font color='yellow'>
                <b>RAZÃO SOCIAL: </b>
            </font>
            <a href = '../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$id_cliente;?>&nao_exibir_menu=1' class='html5lightbox'>
                <font color='#000000'>
                    <?=$razao_social;?>
                </font>
                &nbsp;<img src = "../../../imagem/menu/alterar.png" border='0' title="Alterar Cliente" alt="Alterar Cliente">
            </a>
        </td>
        <td colspan='3'>
            <font color='yellow'>
                <b>N. FANTASIA: </b>
            </font>
            <font color='#000000'>
                <?=$nome_fantasia;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>CEP: </b>
            <?=$cep;?>
        </td>
        <td>
            <b>ENDEREÇO: </b>
            <?=$endereco.', '.$numero_complemento;?>
        </td>
        <td>
            <b>BAIRRO: </b>
            <?=$bairro;?>
        </td>
        <td>
            <b>CIDADE: </b>
            <?=$cidade;?>
        </td>
        <td>
            <b>ESTADO: </b>
            <?=$estado;?>
        </td>
        <td>
            <b>PAÍS: </b>
            <?=$pais;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>DDI/ DDD/ TEL. COMERCIAL: </b>
            <?=$telefone_com;?>
        </td>
        <td colspan='2'>
            <b>DDI/ DDD/ TEL. FAX: </b>
            <?=$telefone_fax;?>
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>E-MAIL: </b>
            <?=$email;?>
        </td>
        <td>
            <font color='red'>
                <b>E-MAIL NFe: </b>
            </font>
            <?=$email_nfe;?>
        </td>
        <td>
            <b>PÁGINA WEB: </b>
            <?=$pagina_web;?>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='6'>
            <b>TP CLIENTE:</b>
            <font color='darkblue' size='2'>
                <b>
                <?
                    $sql = "SELECT `tipo` 
                            FROM `clientes_tipos` 
                            WHERE `id_cliente_tipo` = '$id_cliente_tipo' LIMIT 1 ";
                    $campos_tipo = bancos::sql($sql);
                    echo $campos_tipo[0]['tipo'];
                ?>
            </b></font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>IE: </b>
            <?=$inscricao_estadual;?>
        </td>
        <td>
            <b>IM: </b>
            <?=$inscricao_municipal;?>
        </td>
        <td colspan='2'>
            <b>CNPJ / CPF: </b>
            <?=$cnpj_cpf;?>
        </td>
        <td>
            <b>CCM: </b>
            <?=$ccm;?>
        </td>
        <td>
            <b>RG/ORG: </b>
            <?=$rg;?> / <?=$orgao;?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='6'>
            Financeiro
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>LIMITE DE CRÉDITO: </b>
            <?=number_format($limite_credito, 2, ',', '.');?>
        </td>
        <td>
            <b>CRÉDITO: </b>
            <?=$credito;?>
        </td>
        <td colspan='2'>
            <b>ÚLTIMO CRÉDITO ALTERADO POR: </b>
            <?=strtok($campos2[0]['nome'], ' ');?>
        </td>
        <td colspan='2'>
            <b>EM: </b>
            <?=data::datetodata(substr($credito_data, 0, 10), '/').substr($credito_data, 10, 9);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='6'>
            <b>OBS. DE CRÉDITO: </b>
            <?=$observacao_credito;?>
        </td>
    </tr>
</table>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='iframe' onclick="showHide('contatos'); return false" style='cursor:pointer'>
        <td height='22' align='left'>
            <font color='yellow' size='2'>
                Contato(s)
            </font>
        </td>
    </tr>
    <tr>
        <td>
            <iframe src = '../../classes/cliente/contatos.php?id_cliente=<?=$id_cliente;?>' name='contatos' id='contatos' marginwidth='0' marginheight='0' style='display: visible' frameborder='0' height='100%' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='7'>
            <!--*********Passo o parâmetro cmb_origem=15 para que no início só carregue nessa parte 
            de Follow-Ups dados que são pertinentes a parte de cadastro*********-->
            <iframe name='detalhes' id='detalhes' src = '../../classes/follow_ups/detalhes.php?id_cliente=<?=$id_cliente;?>&origem=7&cmb_origem=15' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
</table>
<?
/********************************Débitos do Cliente******************************/
    //Verifico se existe pelo menos 1 Débito do Cliente do Loop ...
    $retorno    = financeiros::contas_em_aberto($id_cliente, 1, '', 2);
    $linhas     = count($retorno['id_contas']);
    if($linhas > 0) {
?>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onclick="showHide('detalhes2'); return false">
        <td colspan='2'>
            <font color='yellow' size='2'>
                &nbsp;Débito(s) à Receber: 
            </font>
            <font color='#FFFFFF' size='2'>
                <?=$linhas;?>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Passo o id_cliente por parâmetro porque utilizo dentro da Função de Receber-->
            <iframe src = '../../classes/cliente/debitos_receber.php?id_cliente=<?=$id_cliente;?>&id_emp=<?=$id_emp;?>&ignorar_sessao=1' name='detalhes2' id='detalhes2' marginwidth='0' marginheight='0' style='display: none' frameborder='0' height='126' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<?
    }
//Marcador de Impressão
/********************************Pendências******************************/
    $sql = "SELECT pv.`id_pedido_venda`, pv.`id_empresa`, pv.`finalidade`, pv.`vencimento1`, 
            pv.`vencimento2`, pv.`vencimento3`, pv.`vencimento4`, pv.`faturar_em`, 
            pvi.`id_pedido_venda_item`, pvi.`id_produto_acabado`, pvi.`qtde`, pvi.`vale`, 
            pvi.`qtde_pendente`, pvi.`qtde_faturada`, pvi.`preco_liq_final`, 
            pvi.`status` AS status_item 
            FROM `pedidos_vendas` pv 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`status` < '2' 
            WHERE pv.`id_cliente` = '$id_cliente' ORDER BY pv.`id_empresa`, pv.`id_pedido_venda` ";
    $campos_pendencias = bancos::sql($sql);
    $linhas_pendencias = count($campos_pendencias);
    if($linhas_pendencias > 0) {
        $total_pendencias = 0;//Zero a variável para não herdar valores do looping anterior ...
?>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='13'>
            PENDÊNCIA(S) - Estoque
        </td>
    </tr>
    <tr class='linhanormaldestaque' align='center'>
        <td>INI</td>
        <td>FAT</td>
        <td>SEP</td>
        <td>PEND</td>
        <td>VALE</td>
        <td>E.D.</td>
        <td>PRODUTO</td>
        <td>P. L. FINAL <?=$tipo_moeda;?></td>
        <td>IPI %</td>
        <td>TOTAL <?=$tipo_moeda;?></td>
        <td>EMP/ TP/ PZO PGTO</td>
        <td>FATURAR EM</td>
        <td>N.º PED</td>
    </tr>
<?
//Listagem de Pendência
        for($i = 0; $i < $linhas_pendencias; $i++) {
            $vetor = estoque_acabado::qtde_estoque($campos_pendencias[$i]['id_produto_acabado']);
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=number_format($campos_pendencias[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos_pendencias[$i]['qtde_faturada'], 2, '.');?>
        </td>
            <?$separado = $campos_pendencias[$i]['qtde'] - $campos_pendencias[$i]['qtde_pendente'] - $campos_pendencias[$i]['vale'] - $campos_pendencias[$i]['qtde_faturada'];?>
        <td>
            <?=segurancas::number_format($separado, 0, '.', 1);?>
        </td>
        <td>
        <?
            if($campos_pendencias[$i]['qtde_pendente'] > $vetor[3]) {
                echo '<font color="red"><b>'.segurancas::number_format($campos_pendencias[$i]['qtde_pendente'], 0, '.').'</b></font>';
            }else {
                echo segurancas::number_format($campos_pendencias[$i]['qtde_pendente'], 0, '.');
            }
        ?>
        </td>
        <td>
            <?=segurancas::number_format($campos_pendencias[$i]['vale'], 0, '.', 1);?>
        </td>
        <td>
            <?=segurancas::number_format($vetor[3], 0, '.');?>
        </td>
        <td align='left'>
            <?=intermodular::pa_discriminacao($campos_pendencias[$i]['id_produto_acabado']);?>
        </td>
        <td align='right'>
            <?=segurancas::number_format($campos_pendencias[$i]['preco_liq_final'], 2, '.');?>
        </td>
        <td>
        <?
            $dados_produto  = intermodular::dados_impostos_pa($campos_pendencias[$i]['id_produto_acabado'], $id_uf_cliente, $id_cliente, $campos_pendencias[$i]['id_empresa'], $campos_pendencias[$i]['finalidade']);
            if($dados_produto['ipi'] > 0) echo number_format($dados_produto['ipi'], 2, ',', '.');
        ?>
        </td>
        <?
            $preco_total_lote = $campos_pendencias[$i]['preco_liq_final'] * ($campos_pendencias[$i]['qtde'] - $campos_pendencias[$i]['qtde_faturada']);
            $total_pendencias+= $preco_total_lote;
        ?>
        <td align='right'>
            <?=segurancas::number_format($preco_total_lote, 2, '.');?>
        </td>
        <?
            if($campos_pendencias[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos_pendencias[$i]['vencimento4'];
            if($campos_pendencias[$i]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos_pendencias[$i]['vencimento3'].$prazo_faturamento;
            if($campos_pendencias[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos_pendencias[$i]['vencimento1'].'/'.$campos_pendencias[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos_pendencias[$i]['vencimento1'] == 0) ? 'À vista' : $campos_pendencias[$i]['vencimento1'];
            }
            if($campos_pendencias[$i]['id_empresa'] == 1) {
                $nomefantasia = 'ALBA - NF';
                $total_empresa+= $preco_total_lote;
        ?>
        <td>
            <?='(A - NF) / '.$prazo_faturamento;?>
        </td>
        <?
            }else if($campos_pendencias[$i]['id_empresa'] == 2) {
                $nomefantasia = 'TOOL - NF';
                $total_empresa+= $preco_total_lote;
        ?>
        <td>
            <?='(T - NF) / '.$prazo_faturamento;?>
        </td>
        <?
            }else if($campos_pendencias[$i]['id_empresa'] == 4) {
                $nomefantasia = 'GRUPO - SGD';
                $total_empresa+= $preco_total_lote;
        ?>
        <td>
            <?='(G - SGD) / '.$prazo_faturamento;?>
        </td>
        <?
            }
            $prazo_faturamento = '';//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop ...
        ?>
        <td>
            <?=data::datetodata($campos_pendencias[$i]['faturar_em'], '/');?>
        </td>
        <td>
            <a href='../../faturamento/nota_saida/itens/detalhes_pedido.php?id_pedido_venda=<?=$campos_pendencias[$i]['id_pedido_venda'];?>' class='html5lightbox'>
                <?=$campos_pendencias[$i]['id_pedido_venda'];?>
            </a>
        </td>
    </tr>
<?
        }
//Apresentação do Total de Pendências ...
?>
    <tr class='linhanormal' align='right'>
        <td colspan='9'>
            <b>TOTAL PENDÊNCIA(S):</b>
        </td>
        <td>
            <b><?=$tipo_moeda.segurancas::number_format($total_pendencias, 2, '.');?></b>
        </td>
        <td colspan='3'>
            &nbsp;
        </td>
    </tr>
</table>
<?
    }
    unset($vetor_faturamento);//Aqui eu Destruo a variável para não herdar valores do Loop Anterior ...
/****************************Volume de Compra**************************/
?>
<table width='80%' cellspacing ='1' cellpadding='1' border='0' align='center'>
    <tr class='iframe' onclick="showHide('volume_de_compras'); return false" style='cursor:pointer'>
        <td height='22' align='left'>
            <font color='yellow' size='2'>
                &nbsp;Volume de Compra(s) - Pedidos
            </font>
        </td>
    </tr>
    <tr>
        <td>
            <iframe src = '../../classes/cliente/volume_de_compras.php?id_cliente=<?=$id_cliente;?>' name='volume_de_compras' id='volume_de_compras' marginwidth='0' marginheight='0' style='display: visible' frameborder='0' height='100%' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_relatorio_vendas_referencia' value='Relatório de Vendas por Referência vs Ano' title='Relatório de Vendas por Referência vs Ano' onclick="html5Lightbox.showLightbox(7, 'relatorio_vendas_referencia_ano.php?id_cliente=<?=$id_cliente;?>')" style='color:red' class='botao'>
            <input type='button' name='cmd_pedidos_livres_debito' value='Pedidos Livre de Débito' title='Pedidos Livre de Débito' onclick="html5Lightbox.showLightbox(7, 'pedidos_livre_debito.php?passo=2&id_cliente=<?=$id_cliente?>')" style='color:black' class='botao'>
            <input type='button' name='cmd_projetar_opc_pas_adquiridos' value='Projetar OPC - PA(s) Adquirido(s)' title='Projetar OPC - PA(s) Adquirido(s)' onclick="nova_janela('../relatorio/projetar_opc/projetar_opc.php?passo=2&id_cliente=<?=$id_cliente?>&opt_tipo_opc=1&pop_up=1', 'OPC', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:blue' class='botao'>
            <?
                $sql = "SELECT id_representante 
                        FROM `clientes_vs_representantes` 
                        WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
            ?>
            <input type='button' name='cmd_imprimir_espelho_produtos' value='Imprimir Espelho de Produtos' title='Imprimir Espelho de Produtos' onclick="html5Lightbox.showLightbox(7, '../relatorio/projetar_espelho_produtos/relatorio.php?cmb_tipo_relatorio=familia&cmb_representante=<?=$campos_representante[0]['id_representante'];?>&cmb_cliente=<?=$id_cliente;?>&pop_up=1')" style='color: #D55C21' class='botao'>
            <input type='button' name='cmd_projetar_opc_curva_abc' value='Projetar OPC - Curva ABC' title='Projetar OPC - Curva ABC' onClick="nova_janela('../relatorio/projetar_opc/projetar_opc.php?passo=2&id_cliente=<?=$id_cliente;?>&opt_tipo_opc=2&pop_up=1', 'OPC', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:peru' class='botao'>
            <input type='button' name='cmd_atendimento_diario' value='Incluir Atendimento Diário' title='Incluir Atendimento Diário' onclick="html5Lightbox.showLightbox(7, '../atendimento_diario/incluir.php?pop_up=1')" style='color:black' class='botao'>
        </td>
    </tr>
</table>
<?
/********************************Representantes******************************/
//Aqui busca todos os representantes que estão atrelados a esse Cliente
$sql = "SELECT `id_empresa_divisao`, `razaosocial` 
        FROM `empresas_divisoes` 
        WHERE `ativo` = '1' ORDER BY `razaosocial` ";
$campos_empresas_divisoes = bancos::sql($sql);
$linhas_empresas_divisoes = count($campos_empresas_divisoes);
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?//Essa primeira linha e primeira coluna é fixa?>
    <tr class='linhanormaldestaque' align='center'>
        <td width="<?=$width - 4;//-4 é um trambique do Gomes ...?>">
            &nbsp;
        </td>
<?
//Printagem das outras colunas de Divisão
    for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
?>
        <td width="<?=$width;?>">
            <?=$campos_empresas_divisoes[$i]['razaosocial'];?>
        </td>
<?
    }
?>
<!--//Essa coluna aqui é p/ as divisões alinhadas com as Divisões de Cima-->
        <td width="<?=$width;?>">
            &nbsp;
        </td>
    </tr>
<?//Essa segunda linha e primeira coluna é fixa?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <b>REPRESENTANTE(S)</b>
        </td>
<?
//Printagem das outras colunas de Divisão
    for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
//Verifica se a empresa divisão atual do loop está atrelada ao cliente
        $sql = "SELECT r.`id_representante`, r.`nome_fantasia` 
                FROM `clientes_vs_representantes` cr 
                INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                WHERE cr.`id_cliente` = '$id_cliente' 
                AND cr.`id_empresa_divisao` = '".$campos_empresas_divisoes[$i]['id_empresa_divisao']."' LIMIT 1 ";
        $campos_representante = bancos::sql($sql);
        if(count($campos_representante) > 0) {
//Verifico quem é o Supervisor desse Representante ...
            $sql = "SELECT r.`nome_fantasia` AS supervisor 
                    FROM `representantes_vs_supervisores` rs 
                    INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante_supervisor` 
                    WHERE rs.`id_representante` = '".$campos_representante[0]['id_representante']."' LIMIT 1 ";
            $campos_supervisor = bancos::sql($sql);
            if(count($campos_supervisor) == 1) {//Se encontre o Supervisor, apresento este ao lado do Representante ...
                $supervisor = '<br><font color="darkblue"><b>('.$campos_supervisor[0]['supervisor'].')</b></font>';
            }else {//Se não só apresenta o Vendedor que no caso seria o próprio representante ...
                $supervisor = '';
            }
        }
?>
        <td>
<?
//Esse link de alterar Representante do Cliente, aparecerá apenas p/ o usuário Roberto 62 Diretor e Dárcio 98 porque programa ...
        if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
//O parâmetro de pop_up = 1, significa que essa Tela foi aberta como sendo Pop-UP ...
?>
            <a href = '../../classes/cliente/clientes_vs_representantes.php?passo=1&id_cliente=<?=$id_cliente;?>&pop_up=1' class='html5lightbox'>
<?
        }
?>
                <?=$campos_representante[0]['nome_fantasia'];?>
            </a>
            <?=$supervisor;?>
        </td>
<?
    }
?>
<!--//Essa coluna aqui é p/ as divisões alinhadas com as Divisões de Cima-->
        <td align='left'>
            &nbsp;
        </td>
    </tr>
<?//Essa terceira linha e primeira coluna é fixa?>
    <tr class='linhanormal'>
        <td>
            <b>DESC.(S) ATUAL</b>
        </td>
<?
//Printagem das outras colunas de Desconto do Cliente ...
    for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
//Verifica se a empresa divisão atual do loop está atrelada ao cliente
        $sql = "SELECT cr.`desconto_cliente` 
                FROM `clientes_vs_representantes` cr 
                INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                WHERE cr.`id_cliente` = '$id_cliente' 
                AND cr.`id_empresa_divisao` = '".$campos_empresas_divisoes[$i]['id_empresa_divisao']."' LIMIT 1 ";
        $campos_desconto_atual = bancos::sql($sql);
?>
        <td align='center'>
            <?=number_format($campos_desconto_atual[0]['desconto_cliente'], 2, ',', '.').' %';?>
        </td>
<?
    }
?>
<!--//Essa coluna aqui é p/ as divisões alinhadas com as Divisões de Cima-->
        <td align='left'>
            &nbsp;
        </td>
    </tr>
<?//Essa quarta linha e primeira coluna é fixa?>
    <tr class='linhanormal'>
        <td>
            <b>DESC.(S) ANTERIOR</b>
        </td>
<?
//Printagem das outras colunas de Desconto do Cliente
    for($i = 0; $i < $linhas_empresas_divisoes; $i++) {
//Verifica se a empresa divisão atual do loop está atrelada ao cliente
        $sql = "SELECT cr.`desconto_cliente_old` 
                FROM `clientes_vs_representantes` cr 
                INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
                WHERE cr.`id_cliente` = '$id_cliente' 
                AND cr.`id_empresa_divisao` = ".$campos_empresas_divisoes[$i]['id_empresa_divisao']." LIMIT 1 ";
        $campos_desconto_antigo = bancos::sql($sql);
?>
        <td align='center'>
            <?=number_format($campos_desconto_antigo[0]['desconto_cliente_old'], 2, ',', '.').' %';?>
        </td>
<?
    }
?>
<!--//Essa coluna aqui é p/ as divisões alinhadas com as Divisões de Cima-->
        <td align='left'>
            &nbsp;
        </td>
    </tr>
</table>
<?
/********************************Análises*******************************/
/********************************Análise 1******************************/
//Aqui vasculha todas as Faixas de Desconto do Cliente
    $sql = "SELECT * 
            FROM `descontos_clientes` 
            WHERE `tabela_analise` = '0' ORDER BY `valor_semestral` ";
    $campos_descontos = bancos::sql($sql);
    $linhas_descontos = count($campos_descontos);
    if($linhas_descontos > 0) {
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?//Essa primeira linha é um Cabeçalho?>
    <tr class='linhanormaldestaque' align='center'>
        <td colspan="<?=$linhas_descontos + 1;?>">
            ANÁLISE 1 - (VOLUME VENDAS DO GRUPO)
        </td>
    </tr>
<?//Essa segunda linha e primeira coluna é fixa?>
    <tr class='linhanormal' align='center'>
        <td width='160' align='left'>
            <b>PORCENTAGEM(NS)</b>
        </td>
<?
//Printagem das outras colunas de Desconto do Cliente
        for($i = 0; $i < $linhas_descontos; $i++) {
?>
        <td align='right'>
            <?=number_format($campos_descontos[$i]['desconto_cliente'], 2, ',', '.').' %';?>
        </td>
<?
        }
?>
    </tr>
<?//Essa terceira linha e primeira coluna é fixa?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <b>VALOR(ES)</b>
        </td>
<?
//Printagem das outras colunas de Valor Semestral
        for($i = 0; $i < $linhas_descontos; $i++) {
?>
        <td align='right'>
            <?='< '.number_format($campos_descontos[$i]['valor_semestral'], 2, ',', '.');?>
        </td>
<?
        }
?>
    </tr>
<?
    }
/********************************Análise 2******************************/
//Aqui vasculha todas as Faixas de Desconto do Cliente
    $sql = "SELECT * 
            FROM `descontos_clientes` 
            WHERE `tabela_analise` = '1' ORDER BY `valor_semestral` ";
    $campos_descontos = bancos::sql($sql);
    $linhas_descontos = count($campos_descontos);
    if($linhas_descontos > 0) {
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?//Essa primeira linha é um Cabeçalho?>
    <tr class='linhanormaldestaque' align='center'>
        <td colspan="<?=$linhas_descontos + 1;?>">
            ANÁLISE 2 - (VOLUME VENDAS POR LINHA)
        </td>
    </tr>
<?//Essa segunda linha e primeira coluna é fixa?>
    <tr class='linhanormal' align='center'>
        <td width='160' align='left'>
            <b>PORCENTAGEM(NS)</b>
        </td>
<?
//Printagem das outras colunas de Desconto do Cliente
        for($i = 0; $i < $linhas_descontos; $i++) {
?>
        <td align='right'>
            <?=number_format($campos_descontos[$i]['desconto_cliente'], 2, ',', '.').' %';?>
        </td>
<?
        }
?>
    </tr>
<?//Essa terceira linha e primeira coluna é fixa?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <b>VALOR(ES)</b>
        </td>
<?
//Printagem das outras colunas de Valor Semestral
        for($i = 0; $i < $linhas_descontos; $i++) {
?>
        <td align='right'>
            <?='< '.number_format($campos_descontos[$i]['valor_semestral'], 2, ',', '.');?>
        </td>
<?
        }
?>
</tr>
<?
    }
/********************************Última Compra******************************/
    $sql = "SELECT nfs.*, DATE_FORMAT(nfs.`data_emissao`, '%d/%m/%Y') AS data_emissao 
            FROM `nfs` 
            INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            WHERE nfs.`id_cliente` = '$id_cliente' 
            GROUP BY nfs.`id_nf` ORDER BY nfs.`data_emissao` DESC, nfs.`id_nf` DESC ";
    $campos_nfs = bancos::sql($sql);
    $linhas_nfs = count($campos_nfs);
    if($linhas_nfs > 0) {
        $valor_ultima_compra    = $campos_nfs[0]['valor_ultima_compra'];
        $data_ultima_compra     = $campos_nfs[0]['data_emissao'];
        $numero_nf              = faturamentos::buscar_numero_nf($campos_nfs[0]['id_nf'], 'S');

        if($campos_nfs[0]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos_nfs[0]['vencimento4'];
        if($campos_nfs[0]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos_nfs[0]['vencimento3'].$prazo_faturamento;
        if($campos_nfs[0]['vencimento2'] > 0) {
            $prazo_faturamento = $campos_nfs[0]['vencimento1'].'/'.$campos_nfs[0]['vencimento2'].$prazo_faturamento;
        }else {
            $prazo_faturamento = ($campos_nfs[0]['vencimento1'] == 0) ? 'À vista' : $campos_nfs[0]['vencimento1'];
        }

        //Aqui verifica o Tipo de Nota ...
        if($campos_nfs[0]['id_empresa'] == 1 || $campos_nfs[0]['id_empresa'] == 2) {
            $nota_sgd   = 'N';//var surti efeito lá embaixo
            $tipo_nota  = ' (NF)';
        }else {
            $nota_sgd   = 'S'; //var surti efeito lá embaixo
            $tipo_nota  = ' (SGD)';
        }

        //Aqui é a verifica se esta Nota é de Saída ou Entrada ...
        $tipo_nfe_nfs   = ($campos_nfs[0]['tipo_nfe_nfs'] == 'S') ? ' - Saída' : ' - Entrada';
        $prazo_faturamento.= $tipo_nota.$tipo_nfe_nfs;

        //Se o Cliente for Estrangeiro ou Nacional ...
        $tipo_moeda     = ($id_pais != 31) ? 'U$ ' : 'R$ ';
    }
?>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhanormaldestaque' align='center'>
        <td colspan='6'>
            ÚLTIMA COMPRA (FAT)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>NF: </b>
        </td>
        <td>
            N. º 
            <a href='../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos_nfs[0]['id_nf'];?>&pop_up=1' class='html5lightbox'>
                <?=$numero_nf;?>
            </a>
            <?=' - '.$prazo_faturamento;?>
        </td>
        <td>
            <b>VALOR: </b>
        </td>
        <td>
        <?
            $calculo_total_impostos = calculos::calculo_impostos(0, $campos_nfs[0]['id_nf'], 'NF');
            echo $tipo_moeda.number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');
        ?>
        </td>
        <td>
            <b>DATA DE EMISSÃO: </b>
        </td>
        <td>
            <?=$data_ultima_compra;?>
        </td>
    </tr>
</table>
<?
/********************************Produtos Vendidos para o Cliente******************************/
	$mes_atual = (int)date('m');//Mês atual
        $ano_atual = (int)date('Y');//Ano atual
        if($mes_atual > 6) {//Significa está na segunda parte do Semestre
//Então ele mostra dados do Último Semestre Passado - Primeiro
            $mes_atual-= 6;
        }else {//Significa que está na primeira parte do Semestre
//Então ele mostra dados do Último Semestre Passado - Segundo
            $mes_atual = 12 + ($mes_atual - 6);
            $ano_atual--; 
        }
//Listagem de Todos os Produtos Vendidos para o Cliente
	$sql = "SELECT SUM(nfsi.`qtde` * nfsi.`valor_unitario`) AS total, ed.`id_empresa_divisao`, ed.`razaosocial`, 
                CONCAT(ed.`razaosocial`, ' - ', f.`nome`) AS vendas 
                FROM `nfs` 
                INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = nfsi.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
                WHERE MONTH(nfs.`data_emissao`) >= '$mes_atual' 
                AND SUBSTRING(nfs.`data_emissao`, 1, 4) >= '$ano_atual' 
                AND nfs.`id_cliente` = '$id_cliente' 
                GROUP BY f.`id_familia`, ed.`id_empresa_divisao` ORDER BY ed.`id_empresa_divisao`, f.`nome` ";
	$campos_fat = bancos::sql($sql);
	$linhas     = count($campos_fat);
	if($linhas > 0) {
?>
<table border="0" width="80%" align='center' cellspacing ='1' cellpadding='1'>
	<tr class='linhadestaque' align='center'>
		<td colspan="2">
			COMPRA DO(S) ÚLTIMO(S) 6 MES(ES)- Vendas
		</td>
	</tr>
	<tr class='linhanormaldestaque' align='center'>
		<td>DIVISÃO / FAMÍLIA</td>
		<td>VALOR R$</td>
	</tr>
<?
//Listagem de Débitos do Cliente
		$id_empresa_divisao_current = $campos_fat[0]['id_empresa_divisao'];
		for($i = 0; $i < $linhas; $i++) {
//Pergunta se a Empresa Divisão atual ainda é a mesma divisão em Relação a Próxima do Loop
?>
	<tr class='linhanormal'>
		<td align='left'><?=$campos_fat[$i]['vendas'];?></td>
		<td align='right'><?=segurancas::number_format($campos_fat[$i]['total'], 2, '.');?></td>
	</tr>
<?
			$sub_total_divisao+= $campos_fat[$i]['total'];
			if($id_empresa_divisao_current != $campos_fat[$i+1]['id_empresa_divisao']) { //Trocou a Divisão
?>
	<tr class='linhanormal' align='right'>
		<td colspan="2">
			<b><?='TOTAL DA '.$campos_fat[$i]['razaosocial'].' => R$ '.number_format($sub_total_divisao, 2, ',', '.');?></b>
		</td>
	</tr>
<?
				$sub_total_divisao = 0;
				$id_empresa_divisao_current = $campos_fat[$i + 1]['id_empresa_divisao'];
			}
			$total_ultimos_6_meses+= $campos_fat[$i]['total'];
		}
?>
</table>
<?
	}
?>
<table border="0" width="80%" align='center' cellspacing ='1' cellpadding='1'>
	<tr class='linhanormaldestaque' align='center'>
            <td>
                <?
/*Se o Cliente em que irá ser feito o Orçamento for Albafer = 2276 ou Tool Master = 2688, então os únicos que poderão
orçar para estes Clientes é o Roberto, Dárcio ou Nishimura ...*/
                    if($id_cliente == 2276 || $id_cliente == 2688) {
                        if($_SESSION['id_funcionario'] != 62 && $_SESSION['id_funcionario'] != 98 && $_SESSION['id_funcionario'] != 136) {
                            $onclick = "javascript:alert('ESTE CLIENTE ESTÁ INDISPONÍVEL PARA ORÇAMENTO !')";
                        }else {
                            $onclick = "javascript:window.location = '../orcamentos/incluir.php?passo=1&id_cliente=".$id_cliente."'";
                        }
                    }else {
                        $onclick = "javascript:window.location = '../orcamentos/incluir.php?passo=1&id_cliente=".$id_cliente."'";
                    }
                ?>
                    <input type='button' name="cmd_incluir_novo_orcamento" value="Incluir Novo Orçamento" title="Incluir Novo Orçamento" onClick="<?=$onclick;?>" style="color:green" class='botao'>
                <?//Terá Permissão Roberto, Darcio, Nishimura, Netto, Patricia e Eunice. 
                    if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 147 || $_SESSION['id_funcionario'] == 106 || $_SESSION['id_funcionario'] == 183) {
                ?>
                    <input type='button' name="cmd_informacoes_comerciais" value="Informações Comerciais" title="Informações Comerciais" onClick="html5Lightbox.showLightbox(7, 'informacoes_comerciais.php?id_cliente=<?=$id_cliente?>')" style='color:red' class='botao'>
                <?
                    }
                ?>
                    <input type='button' name='cmd_imprimir_apv' value='Imprimir APV' title='Imprimir APV' onclick='window.print()' class='botao'>
		</td>
	</tr>
</table>
<?
//Aki eu acumulo esses valores p/ guardar no hidden e utilizar no arquivo de Impressão em PDF
    $id_clientes.= $id_cliente.', ';
}

$id_clientes = substr($id_clientes, 0, strlen($id_clientes) - 2);
?>
<table border="0" width="80%" align='center' cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
		<td>
<?
//Significa que essa tela foi aberta de forma normal ...
			if($pop_up != 1) {
                            /*Esse valor é guardado no Hidden mais abaixo, porque alguns Pop-Ups que são abertos por esse 
                            arquivo também possuem paginação e sendo assim acaba dando conflito com o parâmetro daqui ...*/
                            if(!empty($parametro_velho)) $parametro = $parametro_velho;
?>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'apv.php<?=$parametro;?>&telemarketing=<?=$telemarketing;?>'" class='botao'>
<?
			}
//Significa que essa tela foi aberta como sendo um Pop-UP
			if($pop_up == 1) {
?>
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class='botao'>
<?
			}
?>
		</td>
	</tr>
</table>
<input type='hidden' name='id_clientes' value='<?=$id_clientes;?>'>
<input type='hidden' name='parametro_velho' value='<?=$parametro;?>'>
</form>
</body>
</html>