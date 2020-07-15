<?
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $representante          = $_POST['representante'];
    $cmb_representante      = $_POST['cmb_representante'];
    $cmb_novo_tipo_cliente  = $_POST['cmb_novo_tipo_cliente'];
    $cmb_credito            = $_POST['cmb_credito'];
    $txt_dias_vencidos      = $_POST['txt_dias_vencidos'];
    $cmd_consultar          = $_POST['cmd_consultar'];
}else {
    $representante          = $_GET['representante'];
    $cmb_representante      = $_GET['cmb_representante'];
    $cmb_novo_tipo_cliente  = $_GET['cmb_novo_tipo_cliente'];
    $cmb_credito            = $_GET['cmb_credito'];
    $txt_dias_vencidos      = $_GET['txt_dias_vencidos'];
    $cmd_consultar          = $_GET['cmd_consultar'];
}

require('../../../lib/segurancas.php');
if(empty($pop_up)) require('../../../lib/menu/menu.php');

require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/vendas/pdt/pdt.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Relatório de Análise de Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'Javascript'>
function validar() {
//Dias Vencidos ...
    if(document.form.txt_dias_vencidos.value == '') {
        alert('DIGITE A QTDE DE DIA(S) VENCIDO(S) !')
        document.form.txt_dias_vencidos.focus()
        return false
    }
//Dias Vencidos ...
    if(document.form.txt_dias_vencidos.value == 0) {
        alert('QTDE DE DIA(S) VENCIDO(S) INVÁLIDO !!!')
        document.form.txt_dias_vencidos.focus()
        document.form.txt_dias_vencidos.select()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_dias_vencidos.focus()'>
<form name='form' method='POST' action='' onsubmit='return validar()'>
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='16'>
            Relatório de Análise de Cliente(s) <br>
            <font color='yellow'>
                <b>Vendedor: </b>
            </font>
            <?
//Verifico se o Vendedor foi passado por Parâmetro ...
            if(!empty($representante)) {
                $sql = "SELECT nome_fantasia 
                        FROM `representantes` 
                        WHERE `id_representante` = '$representante' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                echo $campos_representante[0]['nome_fantasia'];
    ?>
            <input type='hidden' name='representante' value='<?=$representante;?>'>
    <?
//Se não foi passado nenhum Representante por parâmetro, então eu apresento a combo abaixo ...
            }else {
    ?>
                <select name='cmb_representante' title='Selecione o Representante' class='combo'>
    <?
            $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                    FROM `representantes` 
                    WHERE `ativo` = '1' ORDER BY nome_fantasia ";
            echo combos::combo($sql, $cmb_representante);
    ?>
                </select>
    <?
            }
    ?>
            &nbsp;-&nbsp;
            <font color='yellow'>
                <b>Novo Tipo de Cliente:</b>
            </font>
            <select name='cmb_novo_tipo_cliente' title='Selecione o Novo Tipo de Cliente' class='combo'>
            <?
                $sql = "SELECT id_cliente_tipo, tipo 
                        FROM `clientes_tipos` ";
                echo combos::combo($sql, $cmb_novo_tipo_cliente);
            ?>
            </select>
            <br>
            &nbsp;&nbsp;
            <font color='yellow'>
                <b>Crédito:</b>
            </font>
            <select name='cmb_credito' title='Selecione o Crédito' class='combo'>
                <?
                    if($cmb_credito == 'A') {
                        $selecteda = 'selected';
                    }else if($cmb_credito == 'B') {
                        $selectedb = 'selected';
                    }else if($cmb_credito == 'C') {
                        $selectedc = 'selected';
                    }else if($cmb_credito == 'D') {
                        $selectedd = 'selected';
                    }
                ?>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='A' <?=$selecteda;?>>A</option>
                <option value='B' <?=$selectedb;?>>B</option>
                <option value='C' <?=$selectedc;?>>C</option>
                <option value='D' <?=$selectedd;?>>D</option>
            </select>
            &nbsp;-&nbsp;
            <input type='text' name='txt_dias_vencidos' value='<?=$txt_dias_vencidos;?>' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == '0' || this.value == '00' || this.value == '000') {this.value = ''}" maxlength='3' size='5' class='caixadetexto'>
            <font color='yellow'>
                <b>dia(s) vencido(s)</b>
            </font>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
        </td>
    </tr>
<?
    $data_atual = date('Y-m-d');
    if(!empty($cmd_consultar)) {//Se a Tela foi submetida, então eu realizo a pesquisa ...
        $data_atual_menos_x_dias    = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -$_POST['txt_dias_vencidos']), '-');
        //Aqui eu busco todos os Clientes daquele representante selecionado "Carteira" ... 
        if(!empty($representante) || !empty($cmb_representante)) {
            if(!empty($representante)) {
                $sql = "SELECT DISTINCT(id_cliente) 
                        FROM `clientes_vs_representantes` 
                        WHERE `id_representante` = '$representante' ";
            }else {
                $sql = "SELECT DISTINCT(id_cliente) 
                        FROM `clientes_vs_representantes` 
                        WHERE `id_representante` = '$cmb_representante' ";
            }
            $campos_clientes = bancos::sql($sql);
            $linhas_clientes = count($campos_clientes);
            for($i = 0; $i < $linhas_clientes; $i++) $id_clientes.= $campos_clientes[$i]['id_cliente'].', ';
            $id_clientes        = substr($id_clientes, 0, strlen($id_clientes) - 2);
            $condicao_clientes  = " AND c.id_cliente IN ($id_clientes) ";
        }
        if(!empty($cmb_novo_tipo_cliente))  $condicao_novo_tipo_cliente = " AND c.id_cliente_tipo = '$cmb_novo_tipo_cliente' ";
        if(!empty($cmb_credito))            $condicao_credito = " AND c.`credito` LIKE '$cmb_credito' ";
        //Busca de Todos os Clientes do Representante que possuem Vendas da Maior Venda p/ a Menor ...
        $sql = "SELECT c.id_cliente, c.id_pais, c.id_uf, c.id_cliente_tipo, 
                IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) AS cliente, c.cidade, c.credito, 
                DATE_FORMAT(c.data_ultimo_espelho_produtos, '%d/%m/%Y') AS data_ultimo_espelho_produtos, ct.tipo, 
                SUM(pv.valor_ped) AS total_valor_pedido 
                FROM `clientes` c 
                LEFT JOIN `pedidos_vendas` pv ON pv.id_cliente = c.id_cliente 
                INNER JOIN `clientes_tipos` ct ON ct.id_cliente_tipo = c.id_cliente_tipo $condicao_credito
                WHERE c.`ativo` = '1' $condicao_clientes $condicao_novo_tipo_cliente $condicao_credito 
                GROUP BY c.id_cliente ORDER BY total_valor_pedido DESC ";

        $sql_extra = "SELECT COUNT(c.id_cliente) AS total_registro 
                    FROM `clientes` c 
                    LEFT JOIN `pedidos_vendas` pv ON pv.id_cliente = c.id_cliente $condicao_clientes 
                    INNER JOIN `clientes_tipos` ct ON ct.id_cliente_tipo = c.id_cliente_tipo 
                    WHERE c.`ativo` = '1' $condicao_clientes $condicao_novo_tipo_cliente $condicao_credito 
                    GROUP BY c.id_cliente ";
        $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
        $linhas = count($campos);
        if($linhas == 0) {//Não encontrou nenhum Registro ...
?>
    <tr align='center'>
        <td colspan='16'>
            <?=$mensagem[1];?>
        </td>
    </tr>
<?
        }else {//Encontrou pelo menos 1 Registro ...
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Cliente
        </td>
        <td>
            Cidade
        </td>
        <td>
            <font title='Tipo de Cliente' style='cursor:help'>
                TC
            </font>
        </td>
        <td>
            UF
        </td>
        <td>
            <font title='Crédito' style='cursor:help'>
                Cr
            </font>
        </td>
        <td>
            Valor Reajustado<br/>Devido R$ 
        </td>        
        <td>
            Representante
        </td>
        <td>
            N.° OPC(s)
        </td>
        <td>
            Data Último Espelho
        </td>
        <td>
            Último Orc
        </td>
        <td>
            Data Último Orc
        </td>
        <td>
            Qtde Dias
        </td>
        <td>
            Último Ped
        </td>
        <td>
            Data Último Ped
        </td>
        <td>
            Qtde Dias
        </td>
        <td>
            Total Vendido
        </td>
    </tr>
<?
            //Esse vetor será utilizado mais abaixo ...
            $vetor_tipos = array('', 'RA', 'RI', 'C', 'I', 'A', 'D', 'I', 'F', 'UC', 'NC', 'TM', 'TMI');
            for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
<!--O nome desse parâmetro tem que ser id_clientes, porque existe uma outra tela no Sistema 
que leva como parâmetro vários clientes, daí por isso que eu acabei mantendo esse nome ...-->
            <a href = '../apv/informacoes_apv.php?id_clientes=<?=$campos[$i]['id_cliente'];?>&pop_up=1' style='cursor:help' class='html5lightbox'>
                <?=$campos[$i]['cliente'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
            <?='<font title="'.$campos[$i]['tipo'].'" style="cursor:help">'.$vetor_tipos[$campos[$i]['id_cliente_tipo']].'</font>';?>
        </td>
        <td>
        <?
            $sql = "SELECT sigla 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
            $campos_ufs = bancos::sql($sql);
            echo $campos_ufs[0]['sigla'];
        ?>
        </td>
        <td>
        <?
//Tratamento com as cores de Crédito
            if($campos[$i]['credito'] == 'C' || $campos[$i]['credito'] == 'D') {//Se for D, Cliente caloteiro, vermelho ...
                $font = "<font color='red'>";
            }else {//Se não está ok, azul ...
                $font = "<font color='blue'>";
            }
            echo $font.$campos[$i]['credito'];
        ?>
        </td>
        <td align='right'>
            <?
                $sql = "SELECT id_conta_receber
                        FROM `contas_receberes`
                        WHERE `id_cliente` = '".$campos[$i]['id_cliente']."'
                        AND `status` < '2'
                        AND `data_vencimento` < '".date('Y-m-d')."' ";
                $campos_duplicatas = bancos::sql($sql);
                $linhas_duplicatas = count($campos_duplicatas);                  
                for($j = 0; $j < $linhas_duplicatas; $j++) {
                    /*Aqui eu verifico se está Duplicata do Loop realmente possui Pendência "Dívida Conosco" ...

                    Obs: Existem Duplicatas que estão no Valor Negativo o que representa que o Cliente possui Crédito 
                    conosco e não pendência e essas não podem ser Contabilizadas p/ mudar o Crédito do Cliente p/ C ...*/
                    $calculos_conta_receber = financeiros::calculos_conta_receber($campos_duplicatas[$j]['id_conta_receber']);

                    /*Se esse "Valor Reajustado" for maior do que Zero, então isso representa "Dívida" e sendo assim tenho 
                    que contabilizar o "id_cliente" dessa Duplicata p/ poder mudar o Crédito do Cliente p/ "C" ...*/
                    if($calculos_conta_receber['valor_reajustado'] > 0) $valor_total_devido_por_cliente+= $calculos_conta_receber['valor_reajustado'];
                }
                echo number_format($valor_total_devido_por_cliente, 2, ',', '.');
            ?>
        </td>        
        <td>
        <?
            if(!empty($representante) || !empty($cmb_representante)) {
                if(!empty($representante)) {
                    $sql = "SELECT nome_fantasia AS representante 
                            FROM `representantes` 
                            WHERE `id_representante` = '$representante' LIMIT 1 ";
                }else {
                    $sql = "SELECT nome_fantasia AS representante 
                            FROM `representantes` 
                            WHERE `id_representante` = '$cmb_representante' LIMIT 1 ";
                }
            }else {//Se não foi selecionada nenhuma opção, traz o primeiro representante que encontrar ...
                //Aqui eu busco o Representante daquele Cliente ...
                $sql = "SELECT DISTINCT(r.nome_fantasia) AS representante 
                        FROM `representantes` r 
                        INNER JOIN `clientes_vs_representantes` cr ON cr.id_representante = r.id_representante AND cr.id_cliente = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            }
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['representante'];
        ?>
        </td>
        <td>
        <?
            //Aqui eu trago a Quantidade de OPc(s) projetado(s) p/ o Cliente ...
            $sql = "SELECT COUNT(id_opc) AS qtde_opcs 
                    FROM `opcs` 
                    WHERE id_cliente = '".$campos[$i]['id_cliente']."' ";
            $campos_total_opc = bancos::sql($sql);
            if($campos_total_opc[0]['qtde_opcs'] > 0) {
                echo $campos_total_opc[0]['qtde_opcs'];
                //Aqui eu busco a Data do último OPC que foi Projetado ...
                $sql = "SELECT DATE_FORMAT(SUBSTRING(data_sys, 1, 10), '%d/%m/%Y') AS data 
                        FROM `opcs` 
                        WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' ORDER BY data_sys DESC LIMIT 1 ";
                $campos_data_ultimo_opc = bancos::sql($sql);
                echo ' - '.$campos_data_ultimo_opc[0]['data'];
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['data_ultimo_espelho_produtos'] != '00/00/0000') echo $campos[$i]['data_ultimo_espelho_produtos'];
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT id_orcamento_venda, DATE_FORMAT(data_emissao, '%d/%m/%Y') AS data_emissao, valor_orc 
                    FROM `orcamentos_vendas` 
                    WHERE id_cliente = '".$campos[$i]['id_cliente']."' ORDER BY id_orcamento_venda DESC LIMIT 1 ";
            $campos_ultimo_orcamento = bancos::sql($sql);
            if(!empty($campos_ultimo_orcamento[0]['data_emissao'])) {
        ?>
                <a href="javascript:nova_janela('../../vendas/pedidos/itens/detalhes_orcamento.php?veio_faturamento=1&id_orcamento_venda=<?=$campos_ultimo_orcamento[0]['id_orcamento_venda'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class="link">
                    <?=$campos_ultimo_orcamento[0]['id_orcamento_venda'];?>
                </a>
        <?
            }
        ?>
        </td>
        <td>
        <?
            if(!empty($campos_ultimo_orcamento[0]['data_emissao'])) echo $campos_ultimo_orcamento[0]['data_emissao'].' - '.number_format($campos_ultimo_orcamento[0]['valor_orc'], 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            if(!empty($campos_ultimo_orcamento[0]['data_emissao'])) {
                $retorno = data::diferenca_data(data::datatodate($campos_ultimo_orcamento[0]['data_emissao'], '-'), date('Y-m-d'));
                echo $retorno[0];
            }
        ?>
        </td>
        <td bgcolor='#CECECE'>
        <?
            $sql = "SELECT id_pedido_venda, DATE_FORMAT(data_emissao, '%d/%m/%Y') AS data_emissao, valor_ped 
                    FROM `pedidos_vendas` 
                    WHERE id_cliente = '".$campos[$i]['id_cliente']."' ORDER BY id_pedido_venda DESC LIMIT 1 ";
            $campos_ultimo_pedido = bancos::sql($sql);
            if(!empty($campos_ultimo_pedido[0]['data_emissao'])) {
        ?>
                <a href="javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_pedido.php?veio_faturamento=1&id_pedido_venda=<?=$campos_ultimo_pedido[0]['id_pedido_venda'];?>', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes" class="link">
                    <?=$campos_ultimo_pedido[0]['id_pedido_venda'];?>
                </a>
        <?
            }
        ?>
        </td>
        <td bgcolor='#CECECE'>
        <?
            if(!empty($campos_ultimo_pedido[0]['data_emissao'])) echo $campos_ultimo_pedido[0]['data_emissao'].' - '.number_format($campos_ultimo_pedido[0]['valor_ped'], 2, ',', '.');
        ?>
        </td>
        <td bgcolor='#CECECE'>
        <?
            if(!empty($campos_ultimo_pedido[0]['data_emissao'])) {
                $retorno = data::diferenca_data(data::datatodate($campos_ultimo_pedido[0]['data_emissao'], '-'), date('Y-m-d'));
                echo $retorno[0];
            }
        ?>
        </td>
        <td align='right'>
            <b><?=number_format($campos[$i]['total_valor_pedido'], 2, ',', '.');?></b>
        </td>                
    </tr>
<?
            //Zero essa variável "$valor_total_devido_por_cliente" p/ não herdar valores do Loop Anterior ...
            $valor_total_devido_por_cliente = 0;
        }
        /*****************************************************************************************/
        /*Aqui eu busco o Valor Total Devido Geral de todos os Clientes e de todos os Clientes por Representante 
        se foi selecionado algum ...*/
        $sql = "SELECT cr.`id_conta_receber`, cr.`id_representante`, cr.`data_vencimento` 
                FROM `contas_receberes` cr 
                INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` $condicao_credito 
                WHERE cr.`status` < '2' 
                AND cr.`data_vencimento` < '".date('Y-m-d')."' ";
        $campos_total_duplicatas = bancos::sql($sql);
        $linhas_total_duplicatas = count($campos_total_duplicatas);
        for($i = 0; $i < $linhas_total_duplicatas; $i++) {
            /*Aqui eu verifico se está Duplicata do Loop realmente possui Pendência "Dívida Conosco" ...

            Obs: Existem Duplicatas que estão no Valor Negativo o que representa que o Cliente possui Crédito 
            conosco e não pendência e essas não podem ser Contabilizadas p/ mudar o Crédito do Cliente p/ C ...*/
            $calculos_conta_receber = financeiros::calculos_conta_receber($campos_total_duplicatas[$i]['id_conta_receber']);

            /*Se esse "Valor Reajustado" for maior do que Zero, então isso representa "Dívida" e sendo assim tenho 
            que contabilizar o "id_cliente" dessa Duplicata p/ poder mudar o Crédito do Cliente p/ "C" ...*/
            if($calculos_conta_receber['valor_reajustado'] > 0) {
                //Valor Total Devido de Todos os Clientes por Representante, se for selecionado algum é Claro ...
                if(!empty($cmb_representante) && $cmb_representante == $campos_total_duplicatas[$i]['id_representante']) {
                    $valor_total_devido_todos_clientes_por_representante+= $calculos_conta_receber['valor_reajustado'];

                    if($campos_total_duplicatas[$i]['data_vencimento'] >= $data_atual_menos_x_dias) {
                        $valor_total_devido_todos_clientes_por_representante_vencidos_x_dias+= $calculos_conta_receber['valor_reajustado'];
                    }
                }
                //Valor Total Devido de Todos os Clientes ...
                $valor_total_devido_todos_clientes+= $calculos_conta_receber['valor_reajustado'];
                
                if($campos_total_duplicatas[$i]['data_vencimento'] >= $data_atual_menos_x_dias) {
                    $valor_total_devido_todos_clientes_vencidos_x_dias+= $calculos_conta_receber['valor_reajustado'];
                }
            }
        }
        /*****************************************************************************************/
        if(!empty($cmb_representante)) {
?>
    <tr class='linhadestaque'>
        <td colspan='8'>
            <font color='yellow'>
                Valor Total Reajustado Devido de Todos os Clientes por Representante => 
            </font>
            R$ <?=number_format($valor_total_devido_todos_clientes_por_representante, 2, ',', '.');?>
        </td>
        <td colspan='8'>
            <font color='yellow'>
                Vencidos <= <?=$_POST['txt_dias_vencidos'];?> dias (À partir de <?=data::datetodata($data_atual_menos_x_dias, '/');?>) => 
            </font>
            R$ <?=number_format($valor_total_devido_todos_clientes_por_representante_vencidos_x_dias, 2, ',', '.');?>
        </td>
    </tr>
    <?
        }
    ?>
    <tr class='linhadestaque'>
        <td colspan='8'>
            <font color='yellow'>
                Valor Total Reajustado Devido de Todos os Clientes => 
            </font>
            <a href='detalhes_valor_total_devido_por_cliente.php?cmb_credito=<?=$cmb_credito;?>' title='Detalhes do Valor Total Devido por Cliente' style='cursor:help' class='html5lightbox'>
                <font color='#FFFFFF' size='2'>
                    R$ <?=number_format($valor_total_devido_todos_clientes, 2, ',', '.');?>
                </font>
            </a>
        </td>
        <td colspan='8'>
            <font color='yellow'>
                Vencidos <= <?=$_POST['txt_dias_vencidos'];?> dias (À partir de <?=data::datetodata($data_atual_menos_x_dias, '/');?>) => 
            </font>
            R$ <?=number_format($valor_total_devido_todos_clientes_vencidos_x_dias, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='16'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' style='color:red' onclick="window.location = 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
        }
    }else {//Se não foi passado nenhum representante por parâmetro ...
?>
    <tr></tr>
    <tr class='atencao' align='center'>
        <td colspan='16'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' style='color:red' onclick="window.location = 'estrategia_vendas.php?representante=<?=$representante;?>&pop_up=<?=$pop_up;?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<?
    }
?>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Só são exibido(s) Cliente(s) que possuem pelo meno(s) um Pedido efetuado.
</pre>