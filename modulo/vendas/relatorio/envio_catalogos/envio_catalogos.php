<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1]    = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2]    = "<font class='confirmacao'>DADO(S) ATUALIZADO(S) COM SUCESSO.</font>";

/**************************************************************************/
/*****************Atualização de Dados dos Catálogos do Cliente*****************/
/**************************************************************************/
if(!empty($_POST['hdd_atualizar_catalogos'])) {
    foreach($_POST['hdd_cliente'] as $i => $id_cliente) {
        //Verifico a última situação do Catálogo Enviado para esse Cliente ...
        $sql = "SELECT `catalogo_enviado`
                FROM `clientes`
                WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        $campos_enviado = bancos::sql($sql);
        if($campos_enviado[0]['catalogo_enviado'] == 'S') {//Significa já ouve envio de Catálogo anteriormente p/ esse Cliente ...
            //Atualizando dados de Catálogos da Copa do Cliente do Loop ...
            $sql = "UPDATE `clientes` SET `observacao_catalogo` = '".$_POST['txt_observacao_catalogo'][$i]."' WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        }else {//Ainda não houve envio de Catálogo p/ esse Cliente ...
            if(in_array($id_cliente, $_POST['chkt_catalogo_enviado'])) {
                $catalogo_enviado       = 'S';
                $data_envio_catalogo    = date('Y-m-d');
            }else {
                $catalogo_enviado       = 'N';
                $data_envio_catalogo    = '0000-00-00';
            }
            //Atualizando dados de Catálogos da Copa do Cliente do Loop ...
            $sql = "UPDATE `clientes` SET `catalogo_enviado` = '$catalogo_enviado', `observacao_catalogo` = '".$_POST['txt_observacao_catalogo'][$i]."', `data_envio_catalogo` = '$data_envio_catalogo' WHERE `id_cliente` = '$id_cliente' LIMIT 1 ";
        }
        bancos::sql($sql);
    }
    $valor = 2;
}
/**************************************************************************/

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial       = $_POST['txt_data_inicial'];
    $txt_data_final         = $_POST['txt_data_final'];
    $cmb_representante      = $_POST['cmb_representante'];
    $cmb_opcao_catalogos    = $_POST['cmb_opcao_catalogos'];
    $cmb_opcao_clientes     = $_POST['cmb_opcao_clientes'];
    $txt_cliente            = $_POST['txt_cliente'];
}else {
    $txt_data_inicial       = $_GET['txt_data_inicial'];
    $txt_data_final         = $_GET['txt_data_final'];
    $cmb_representante      = $_GET['cmb_representante'];
    $cmb_opcao_catalogos    = $_GET['cmb_opcao_catalogos'];
    $txt_cliente            = $_GET['txt_cliente'];
}
?>
<html>
<head>
<title>.:: Relatório de Envio de Catálogo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function selecionar_tudo() {
    var elementos   = document.form.elements
    var checado     = (document.form.chkt_tudo.checked) ? true : false
    
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['hdd_cliente[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_cliente[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
        //Esse tratamento só será válido em cima dos objetos Habilitados ...
        if(document.getElementById('chkt_catalogo_enviado'+i).disabled == false) document.getElementById('chkt_catalogo_enviado'+i).checked = checado
    }
}
    
function validar() {
//Data Inicial ...
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final ...
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6, 4) + data_inicial.substr(3, 2) + data_inicial.substr(0, 2)
    data_final          = data_final.substr(6, 4) + data_final.substr(3, 2) + data_final.substr(0, 2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 5 anos. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 1825) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A CINCO ANOS !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    document.form.submit()
}

function salvar() {
    var elementos   = document.form.elements

//Habilito todos os Checkbox antes de gravar no BD, senão perco o histórico de tudo o que foi gravado anteriormente ...
    if(typeof(elementos['hdd_cliente[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_cliente[]'].length)
    }
    for(var i = 0; i < linhas; i++) document.getElementById('chkt_catalogo_enviado'+i).disabled = false

    document.form.hdd_atualizar_catalogos.value = 1
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<!--******************Controle de Tela******************-->
<input type='hidden' name='hdd_atualizar_catalogos'>
<!--****************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Relatório de Envio de Catálogo
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='9'>
            <p/>Data Inicial: 
            <?
                $datas = genericas::retornar_data_relatorio();
                //Nas demais vezes em que já submetou para o Banco de Dados ...
                if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($pagina)) {//Só faço os calculos se ele submeter para evitar processamento indevido ao clicar no link sem querer
                    $data_inicial   = $txt_data_inicial;
                    $data_final     = $txt_data_final;
                }else {//Aqui é somente na Primeira vez em que carregar a Tela ...
                    $data_inicial   = $datas['data_inicial'];
                    $data_final     = $datas['data_final'];
                }
            ?>
            <input type='text' name='txt_data_inicial' value='<?=$data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            &nbsp; <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> &nbsp; Data Final:
            <input type='text' name='txt_data_final' value='<?=$data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='11' maxlength='10' class='caixadetexto'>
            &nbsp; <img src='../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style="cursor:hand" onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;&nbsp;&nbsp;
            Representante: 
            <select name='cmb_representante' title='Selecione o Representante' onchange='return validar()' class='combo'>
            <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql, $cmb_representante);
            ?>
            </select>
            <p/>
            Opção de Catálogos: 
            <?
                if($cmb_opcao_catalogos == 1) {
                    $selected_catalogos1 = 'selected';
                }else if($cmb_opcao_catalogos == 2) {
                    $selected_catalogos2 = 'selected';
                }
            ?>
            <select name='cmb_opcao_catalogos' onchange='return validar()' title='Selecione uma Opção de Catálogos' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1' <?=$selected_catalogos1;?>>SOMENTE CATÁLOGOS ENVIADOS</option>
                <option value='2' <?=$selected_catalogos2;?>>SOMENTE CATÁLOGOS NÃO ENVIADOS</option>
            </select>
            &nbsp;-&nbsp;
            Opção de Clientes: 
            <?
                if($cmb_opcao_clientes == 1) {
                    $selected_clientes1 = 'selected';
                }else if($cmb_opcao_clientes == 2) {
                    $selected_clientes2 = 'selected';
                }
            ?>
            <select name='cmb_opcao_clientes' onchange='return validar()' title='Selecione uma Opção de Clientes' class='combo'>
                <option value='1' <?=$selected_clientes1;?>>SOMENTE COM PEDIDOS</option>
                <option value='2' <?=$selected_clientes2;?>>TODOS OS CLIENTES</option>
            </select>
            &nbsp;-&nbsp;
            Cliente:
            <input type='text' name='txt_cliente' value='<?=$txt_cliente;?>' title='Digite o Cliente' class='caixadetexto'>
            &nbsp;
            <input type='button' name='cmd_consultar' value='Consultar' title='Consultar' onclick="return validar()" class='botao'>
        </td>
    </tr>
<?
    //Somente os Pedidos Liberados Independente do Representante Selecionado ...
    if($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($pagina)) {//Só faço os calculos se ele submeter para evitar processamento indevido ao clicar no link sem querer
        if($cmb_opcao_catalogos == 1) {
            $condicao_opcao_catalogos   = " AND c.`catalogo_enviado` = 'S' ";
        }else if($cmb_opcao_catalogos == 2) {
            $condicao_opcao_catalogos   = " AND c.`catalogo_enviado` = 'N' ";
        }
        /**********************************************************************/
        /*************************SOMENTE COM PEDIDOS**************************/
        /**********************************************************************/
        if($cmb_opcao_clientes == 1) {//SOMENTE COM PEDIDOS ...
            if(empty($cmb_representante))   $cmb_representante = '%';
            
            $txt_data_faturado_mes 	= data::datatodate($txt_data_inicial, '-');
            $txt_data_faturavel_mes     = data::datatodate($txt_data_final, '-');
            
            $sql = "SELECT c.`id_cliente`, IF(c.`id_pais` = '31', SUM(pvi.`qtde` * pvi.`preco_liq_final`), 
                    SUM((pvi.`qtde` * pvi.`preco_liq_final`) * pv.`valor_dolar`)) AS total, 
                    IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, c.`cidade`, 
                    c.`catalogo_enviado`, c.`observacao_catalogo`, 
                    DATE_FORMAT(c.`data_envio_catalogo`, '%d/%m/%Y') AS data_envio_catalogo, ufs.`sigla` 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` AND pvi.`id_representante` LIKE '$cmb_representante' 
                    INNER JOIN `clientes` c ON c.`id_cliente` = pv.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') 
                    LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                    WHERE pv.data_emissao BETWEEN '$txt_data_faturado_mes' AND '$txt_data_faturavel_mes' 
                    AND pv.liberado = '1' 
                    $condicao_opcao_catalogos 
                    GROUP BY c.`id_cliente` ORDER BY IF(c.`id_pais` = '31', SUM(pvi.`qtde` * pvi.`preco_liq_final`), SUM((pvi.`qtde` * pvi.`preco_liq_final`) * pv.`valor_dolar`)) DESC ";
        /**********************************************************************/
        /**************************TODOS OS CLIENTES***************************/
        /**********************************************************************/
        }else {//TODOS OS CLIENTES ...
            if(!empty($cmb_representante)) {
                /*Busco todos os Clientes de acordo com Representante Filtrado feito pelo Usuário, 
                independente do Tipo de Cliente ...*/
                $sql = "SELECT `id_cliente` 
                        FROM `clientes_vs_representantes` 
                        WHERE `id_representante` = '$cmb_representante' 
                        GROUP BY id_cliente ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                if($linhas == 0) {//Não encontrou nenhum Cliente ...
                    $vetor_cliente[] = 0;
                }else {//Encontrou pelo menos 1 Cliente ...
                    for($i = 0; $i < $linhas; $i++) $vetor_cliente[] = $campos[$i]['id_cliente'];
                }
                $condicao_clientes          = " AND c.`id_cliente` IN (".implode($vetor_cliente, ',').") ";
            }else {
                /*Nesse caso eu trago todos os Clientes que são do Tipo: "Revenda Ativa 1, Indústria 4, 
                Atacadista 5, Distribuidor 6" ...*/
                $condicao_clientes_tipos    = " AND c.`id_cliente_tipo` IN (1, 4, 5, 6) ";
            }

            $sql = "SELECT c.`id_cliente`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, 
                    c.`cidade`, c.`catalogo_enviado`, c.`observacao_catalogo`, 
                    DATE_FORMAT(c.`data_envio_catalogo`, '%d/%m/%Y') AS data_envio_catalogo, ufs.`sigla` 
                    FROM `clientes` c 
                    LEFT JOIN `ufs` ON ufs.`id_uf` = c.`id_uf` 
                    WHERE (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') 
                    $condicao_clientes_tipos 
                    $condicao_clientes 
                    $condicao_opcao_catalogos ORDER BY IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) ";
        }
        $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
        $linhas = count($campos);
        if($linhas == 0) {//Não encontrou nenhuma venda do Representante selecionado ...
?>
    <tr class='atencao' align='center'>
        <td colspan='9'>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
        }else {
            if($cmb_opcao_clientes == 1) {//SOMENTE COM PEDIDOS ...
                //Busco o "Total de Pedido de Vendas" dentro do Período de Datas filtrado pelo Usuário ...
                $sql = "SELECT SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS total 
                        FROM `pedidos_vendas` pv 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda = pv.id_pedido_venda 
                        WHERE pv.data_emissao BETWEEN '$txt_data_faturado_mes' AND '$txt_data_faturavel_mes' 
                        AND pv.liberado = '1' ";
                $campos_fat_total_nac   = bancos::sql($sql);
                $total_geral            = $campos_fat_total_nac[0]['total'];
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='9'>
            <font color='yellow'>
                TOTAL GERAL (TODOS VENDEDORES) => 
                <?=segurancas::number_format($total_geral, 2, '.');?>
            </font>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Cidade
        </td>
        <td>
            UF
        </td>
        <td>
            Representante
        </td>
        <td>
            Total <br/>Pedido<font color='red'>**</font>
            &nbsp;R$
        </td>
        <td>
            %
        </td>
        <td>
            Catálogo(s) <br/>Enviado(s)
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' title='Selecionar Tudo' style='cursor:help' onclick='selecionar_tudo()' class='checkbox'>
        </td>
        <td>
            Observação <br/>de Catálogo
        </td>
        <td>
            Data de Envio <br/>do Catálogo
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas; $i++) {
                //Controle p/ linha Zebrada ...
                $class_linha = (($i % 2) == 0) ? 'linhanormal' : 'linhanormalescura';
?>
    <tr class='<?=$class_linha;?>' align='center'>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td>
        <?
            $sql = "SELECT DISTINCT(nome_fantasia) AS representante 
                    FROM `clientes_vs_representantes` cr 
                    INNER JOIN `representantes` r ON r.id_representante = cr.id_representante 
                    WHERE cr.id_cliente = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['representante'];
        ?>
        </td>
        <td align='right'>
            <?
                if($cmb_opcao_clientes == 1) {//SOMENTE COM PEDIDOS ...
            ?>
            <a href = 'detalhes_divisao.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&data_inicial=<?=$txt_data_faturado_mes;?>&data_final=<?=$txt_data_faturavel_mes;?>' class='html5lightbox'>
                <font color='blue' size='2'>
                <?
                    $sub_total+= $campos[$i]['total'];
                    echo number_format($campos[$i]['total'], 2, ',', '.');
                ?>
                </font>
            </a>
            <?
                }
            ?>
        </td>
        <td align='right'>
        <?
            if($cmb_opcao_clientes == 1) {//SOMENTE COM PEDIDOS ...
                echo number_format($campos[$i]['total'] / $total_geral * 100, 2, ',', '.').' %';
            }
        ?>
        </td>
        <td>
            <?
                if($campos[$i]['catalogo_enviado'] == 'S') {
                    $checked    = 'checked';
                    $disabled   = 'disabled';
                }else {
                    $checked    = '';
                    $disabled   = '';
                }
            ?>
            <input type='checkbox' name='chkt_catalogo_enviado[]' id='chkt_catalogo_enviado<?=$i;?>' value='<?=$campos[$i]['id_cliente'];?>' class='checkbox' <?=$checked;?> <?=$disabled;?>>
        </td>
        <td>
            <textarea name='txt_observacao_catalogo[]' id='txt_observacao_catalogo<?=$i;?>' cols='15' rows='1' maxlength='85' title='Digite a Observação de Catálogo' class='caixadetexto'><?=$campos[$i]['observacao_catalogo'];?></textarea>
        </td>
        <td>
        <?
            if($campos[$i]['data_envio_catalogo'] != '00/00/0000') echo $campos[$i]['data_envio_catalogo'];
        ?>
        </td>
        <!--***********Esse hidden me serve de controle de Tela quando o usuário salvar os campos de Catálogos***********-->
        <input type='hidden' name='hdd_cliente[]' id='hdd_cliente<?=$i;?>' value='<?=$campos[$i]['id_cliente'];?>'>
    </tr>
<?
            }
            
            if($cmb_opcao_clientes == 1) {//SOMENTE COM PEDIDOS ...
?>
    <tr class='linhanormal' align='right'>
        <td colspan='5'>
            <font color='red' size='3'>
                <b>Sub Total: <?=segurancas::number_format($sub_total, 2, '.');?></b>
            </font>
        </td>
        <td>
            <b><?=segurancas::number_format($sub_total / $total_geral * 100, 2, '.');?> %</b>
        </td>
        <td colspan='3'>
            <font color='blue' size='4'>
                <b>Valor Dólar do dia => R$ <?=number_format(genericas::moeda_dia('dolar'), 4, ',', '.');?></b>
            </font>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' onclick='salvar()' class='botao'>
            <input type='button' name='cmd_imprimir_pdf' value='Imprimir PDF' title='Imprimir PDF' onclick="html5Lightbox.showLightbox(7, 'imprimir_pdf.php')" style='color:black' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<pre>
<font color='red'>** Neste Relatório não consta os seguintes cálculos:</font>
<font color='blue'>
 - Notas Fiscais sem Data de Emissão
 - Frete / IPI
 - Despesas Acessórias
 - Desconto de PIS + Cofins e ICMS = 7%
</font>
</pre>
<?
        }
    }else {
?>
    <tr class='erro' align='center'>
        <td colspan='9'>
            CLIQUE EM CONSULTAR PARA GERAR O RELATÓRIO.
        </td>
    </tr>
</table>
</form>
<?
    }
?>
</body>
</html>