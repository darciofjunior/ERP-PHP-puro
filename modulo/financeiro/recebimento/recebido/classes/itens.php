<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/genericas.php');
session_start('funcionarios');
if($id_emp == 1) {//Albafer
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/albafer/index.php';
}else if($id_emp == 2) {//Tool Master
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/tool_master/index.php';
}else if($id_emp == 4) {//Grupo
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/grupo/index.php';
}else if($id_emp == 0) {//Todas Empresas
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/recebido/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../../');

//////////////////////// Tratamentos para não furar o SQL ///////////////////////////
if(!empty($cmb_representante))      $condicao_representante = " AND cr.`id_representante` LIKE '$cmb_representante' ";
if(empty($cmb_tipo_recebimento))    $cmb_tipo_recebimento = '%';
if(!empty($cmb_uf))                 $condicao_uf            = " AND c.`id_uf` LIKE '$cmb_uf' ";
if(empty($cmb_ano))                 $cmb_ano = '%';
if(!empty($cmb_banco))              $condicao_banco = " AND cr.`id_banco` LIKE '$cmb_banco' ";

$condicao_emp = ($id_emp == 0) ? '' : " AND cr.`id_empresa` = '$id_emp' ";

if(!empty($chkt_somente_exportacao)) $condicao_exportacao = " AND c.id_pais <> '31' ";

if(!empty($txt_data_emissao_inicial)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_emissao_final, 4, 1) != '-') {
        $txt_data_emissao_inicial   = data::datatodate($txt_data_emissao_inicial, '-');
        $txt_data_emissao_final     = data::datatodate($txt_data_emissao_final, '-');
    }
    //Aqui é para não dar erro de SQL
    $condicao1 = " AND cr.`data_emissao` BETWEEN '$txt_data_emissao_inicial' AND '$txt_data_emissao_final' ";
}

if(!empty($txt_data_vencimento_inicial)) {
    //Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_vencimento_final, 4, 1) != '-') {
        $txt_data_vencimento_inicial = data::datatodate($txt_data_vencimento_inicial, '-');
        $txt_data_vencimento_final = data::datatodate($txt_data_vencimento_final, '-');
    }
    //Aqui é para não dar erro de SQL
    $condicao2 = " AND cr.`data_vencimento_alterada` BETWEEN '$txt_data_vencimento_inicial' AND '$txt_data_vencimento_final' ";
}

if(!empty($txt_data_inicial)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_final, 4, 1) != '-') {
        $txt_data_inicial = data::datatodate($txt_data_inicial, '-');
        $txt_data_final = data::datatodate($txt_data_final, '-');
    }
    //Aqui é para não dar erro de SQL
    $condicao3 = " AND crq.data BETWEEN '$txt_data_inicial' AND '$txt_data_final' ";
}

if(!empty($txt_data_cadastro)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
    if(substr($txt_data_cadastro, 4, 1) != '-') $txt_data_cadastro = data::datatodate($txt_data_cadastro, '-');
}

//Essa adaptação só serve para o 1º SQL que não tem o relacional com a Tabela mesmo que não esteja preenchido o campo descrição da Conta ...
if(empty($txt_descricao_conta) && !empty($txt_cliente)) {
    $txt_descricao_conta_sql1 = $txt_cliente;
}else {
    $txt_descricao_conta_sql1 = $txt_descricao_conta;
}
			
$sql = "SELECT cr.id_conta_receber, cr.*, crq.data, c.razaosocial, c.credito, t.recebimento, t.imagem, concat(tm.simbolo, '&nbsp;') AS simbolo 
        FROM `contas_receberes` cr 
        INNER JOIN `contas_receberes_quitacoes` crq ON crq.id_conta_receber = cr.id_conta_receber 
        INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente AND (c.nomefantasia LIKE '%$txt_cliente%' OR c.razaosocial LIKE '%$txt_cliente%' OR cr.descricao_conta LIKE '%$txt_descricao_conta_sql1%') AND c.`ativo` = '1' AND c.bairro like '%$txt_bairro%' and c.cidade like '%$txt_cidade%' $condicao_uf $condicao_exportacao 
        INNER JOIN `tipos_recebimentos` t ON t.id_tipo_recebimento = cr.id_tipo_recebimento 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = cr.id_tipo_moeda 
        WHERE cr.ativo = '1' 
        AND cr.status >= '1' 
        AND cr.num_conta LIKE '%$txt_numero_conta%' 
        AND SUBSTRING(cr.`data_vencimento_alterada`, 1, 4) LIKE '$cmb_ano' 
        AND cr.semana LIKE '%$txt_semana%' 
        AND SUBSTRING(cr.data_sys, 1, 10) LIKE '%$txt_data_cadastro%' 
        AND cr.id_tipo_recebimento LIKE '$cmb_tipo_recebimento' 
        $condicao_representante 
        $condicao_banco 
        $condicao_emp 
        $condicao 
        $condicao1 
        $condicao2 
        $condicao3 
        GROUP BY cr.id_conta_receber ORDER BY cr.`data_vencimento_alterada` ";
$campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
$linhas = count($campos);
/*******************************************************************************************/
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        window.parent.location = 'index.php?itens=1&valor=1&id_emp2=<?=$id_emp;?>'
    </Script>
<?
    exit;
}
/*Aqui eu envio o parâmetros linhas para o frame debaixo p/ não ter que
refazer todo esse sql acima, com o objetivo de saber se é para aparecer os demais
botões no frame de baixo como alterar, excluir, ...*/
?>
	<Script Language = 'JavaScript'>
		window.parent.rodape.location = 'rodape.php?linhas=<?=$linhas;?>'
	</Script>
<?
if($linhas > 0) {
    $dia = date('d');
    $mes = date('m');
    $ano = date('Y');
    $data_hoje = $ano.$mes.$dia;
?>
<html>
<head>
<title>.:: Consultar Conta(s) Recebida(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function atualizar_rodape() {
    window.parent.rodape.document.location = 'rodape.php?existe=1'
}

/*Toda vez que fechar o Pop-UP de Follow-UP, chama essa função p/ não perder 
o parâmetro de Filtro ...*/
function recarregar_tela() {
    window.parent.itens.document.location = '../../financeiro/recebimento/recebido/classes/itens.php<?=$parametro;?>'
}
</Script>
</head>
<body onload="atualizar_rodape()">
<form name='form'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='13'>
            Contas Recebida(s) 
            <font color='yellow'>
            <?
                if($id_emp != 0) {//Diferente de Todas Empresas
                    echo genericas::nome_empresa($id_emp);
                }else {
                    echo 'TODAS EMPRESAS';
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Item<input type='hidden' name='chkt'>
        </td>
        <td>
            <font title='Semana' style='cursor:help'>
                Sm.
            </font>
        </td>
        <td>
            N.º / Conta
        </td>
        <td>
            Cliente / Descrição da Conta
        </td>
        <td>
            <font title='Empresa' style='cursor:help'>
                E
            </font>
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            <font title='Data de Vencimento' style='cursor:help'>
                Data de <br/>Venc.
            </font>
        </td>
        <td align='center'>
            <font title='Data de Recebimento' style='cursor:help'>
                Data <br/>Rec.
            </font>    
        </td>
        <td align='center'>
            <font title='Tipo de Recebimento' style='cursor:help'>
                Tipo Rec.
            </font>
        </td>
        <td>
            <font title='Praça de Recebimento' style='cursor:help'>
                Praça Rec.
            </font>
        </td>
        <td>
            Valor
        </td>
        <td>
            Valor Recebido
        </td>
        <td>
            Obs. Conta à Receber / Recebida
        </td>
    </tr>
<?
        //Aqui eu busco todas observações Follow-UPs dos ids_conta_receberes encontrados acima ...
        for($i = 0; $i < $linhas; $i++) $vetor_contas_receberes[] = $campos[$i]['id_conta_receber'];
        
        $sql = "SELECT `identificacao`, `observacao` 
                FROM `follow_ups` 
                WHERE `origem` = '4' 
                AND `identificacao` IN (".implode(',', $vetor_contas_receberes).") ";
        $campos_follow_ups = bancos::sql($sql);
        $linhas_follow_ups = count($campos_follow_ups);
        for($i = 0; $i < $linhas_follow_ups; $i++) $vetor_observacao_follow_up[$campos_follow_ups[$i]['identificacao']] = $campos_follow_ups[$i]['observacao'];

	$total_contas = 0;
	for ($i = 0; $i < $linhas; $i++) {
//Essa variável iguala o tipo de moeda da conta à receber
            $moeda = $campos[$i]['simbolo'];
//Aqui eu limpo as variáveis para no caso de não encontrar o cliente no if abaixo e não manter os valores antigos
            $virar_link = 1;//Valor Default - Significa que é p/ virar link ...

            $id_cliente = $campos[$i]['id_cliente'];
            $cliente 	= $campos[$i]['razaosocial'];
            $credito 	= $campos[$i]['credito'];
            if(empty($credito)) $credito = ' ';
/***************************************************************************/
            $data_vencimento_alterada   = substr($campos[$i]['data_vencimento_alterada'], 0, 4).substr($campos[$i]['data_vencimento_alterada'], 5, 2).substr($campos[$i]['data_vencimento_alterada'], 8, 2);
            $calculos_conta_receber     = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
?>
    <tr class='linhanormal' onclick="options('form', 'opt_conta_receber', '<?=$i+1;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='radio' name='opt_conta_receber' onclick="options('form', 'opt_conta_receber', '<?=$i + 1;?>', '#E8E8E8')" value="<?=$campos[$i]['id_conta_receber'];?>">
        </td>
        <td>
            <?=$campos[$i]['semana'];?>
        </td>
        <td>
        <?
            if($virar_link == 1) {
                if($campos[$i]['id_nf'] > 0) {//Exibir Dados de NFs de Saída ...
                    //Aki eu busco o id_pedido_venda_item p/ visualizar os Detalhes da NF de Saída ...
                    $sql = "SELECT nfsi.id_pedido_venda_item 
                            FROM `nfs` 
                            INNER JOIN `nfs_itens` nfsi ON nfsi.id_nf = nfs.id_nf 
                            WHERE nfs.id_nf = '".$campos[$i]['id_nf']."' LIMIT 1 ";
                    $campos_pedido_venda 	= bancos::sql($sql);
                    //Exibo aqui detalhes da NF de Vendas de Saída ...
                        $a_href = "javascript:nova_janela('../../../../classes/faturamento/faturado.php?id_pedido_venda_item=".$campos_pedido_venda[0]['id_pedido_venda_item']."&nao_verificar_sessao=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '') ";
                }else if($campos[$i]['id_nf_outra'] > 0) {//Exibir Dados de NFs de Saída Outras ...
                    $a_href = "javascript:nova_janela('../../../../faturamento/nfs_consultar/cabecalho_nfs_outras.php?id_nf_outra=".$campos[$i]['id_nf_outra']."', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '') ";
                }
        ?>
            <a href="<?=$a_href;?>" title='Visualizar Faturamento' class='link'>
        <?
            }
        ?>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
            <?
                if($campos[$i]['num_conta'] == '') {
                    echo '&nbsp;';
                }else {
                    echo $campos[$i]['num_conta'];
                }
            ?>
            </font>
        <?
            if($virar_link == 1) {
        ?>
                </a>
        <?
            }
        ?>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('../../../../classes/follow_ups/detalhes.php?identificacao=<?=$campos[$i]['id_conta_receber'];?>&origem=4', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title="Registrar Follow-Up do Cliente" class="link">
            <?
                if(!empty($cliente) && $cliente != '&nbsp;') echo $cliente.' / ';
                
                if($campos[$i]['descricao_conta'] == '') {
                    echo '&nbsp;';
                }else {
                    echo $campos[$i]['descricao_conta'];
                }
            ?>
            </a>
        </td>
        <td>
        <?
            $empresa_conta = genericas::nome_empresa($campos[$i]['id_empresa']);
            if($empresa_conta == 'ALBAFER') {
                echo '<font title="ALBAFER" style="cursor:help">A</font>';
            }else if($empresa_conta == 'TOOL MASTER') {
                echo '<font title="TOOL MASTER" style="cursor:help">T</font>';
            }else if($empresa_conta == 'GRUPO') {
                echo '<font title="GRUPO" style="cursor:help">G</font>';
            }
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'],'/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento_alterada'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td>
            <?
                $url = '../../../../../imagem/financeiro/tipos_pag_rec/'.$campos[$i]['imagem'];
            ?>
            <img src='<?=$url;?>' width='33' height='20' border='0' title='<?=$campos[$i]['pagamento_recebimento'];?>'>
        </td>
        <td>
        <?
            $sql = "SELECT b.banco 
                    FROM `contas_receberes` cr 
                    INNER JOIN `bancos` b on cr.id_banco = b.id_banco 
                    WHERE cr.id_conta_receber = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
            $campos_bancos = bancos::sql($sql);
            if(count($campos_bancos) > 0) {
                echo $campos_bancos[0]['banco'];
            }else {
                echo '&nbsp';
            }
        ?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['valor'] == '0.00') {
                echo '&nbsp;';
            }else {
                echo $moeda.number_format($campos[$i]['valor'], 2, ',', '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            //Se o Cliente for Estrangeiro Então ...
            if($campos[$i]['simbolo'] != 'R$') {
                $sql = "SELECT SUM(valor * valor_moeda_dia) AS total_recebido 
                        FROM `contas_receberes_quitacoes` 
                        WHERE `id_conta_receber` = '".$campos[$i]['id_conta_receber']."' ";
                $campos_recebido = bancos::sql($sql);
                echo 'R$ '.number_format($campos_recebido[0]['total_recebido'], 2, ',', '.');
                $valor_recebido_total+= $campos_recebido[0]['total_recebido'];
            }else {
                if($campos[$i]['valor_pago'] == 0) {
                    echo '&nbsp;';
                }else {
                    echo 'R$ '.number_format($campos[$i]['valor_pago'], 2, ',', '.');
                }
                $valor_recebido_total+= $campos[$i]['valor_pago'];
            }
        ?>
        </td>
        <td align='left'>
            <?=$vetor_observacao_follow_up[$campos[$i]['id_conta_receber']];?>
        </td>
    </tr>
<?
            $total_contas++;
	}
?>
    <tr class='linhacabecalho'>
        <td colspan='5'>
            <b>Total de Contas: </b><?=$total_contas;?>
        </td>
        <td colspan='5'>
        <?
            $semana = data::numero_semana(date('d'),date('m'),date('Y'));
            $ano = date('Y');
            $sql = "SELECT dia_inicio, dia_fim 
                    FROM `semanas` 
                    WHERE `semana` = '$semana' 
                    AND SUBSTRING(`dia_inicio`, 1, 4) = '$ano' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) != 0) {
                $dia_inicio = data::datetodata($campos[0]['dia_inicio'],'/');
                $dia_inicio = substr($dia_inicio, 0, 6).substr($dia_inicio, 8, 2);
                $dia_fim    = data::datetodata($campos[0]['dia_fim'],'/');
                $dia_fim    = substr($dia_fim, 0, 6).substr($dia_fim, 8, 2);
        ?>							
                Semana: <?=$semana;?>
                - Período: <?=$dia_inicio.' a '.$dia_fim;?>
        <?
            }else {
        ?>
                Semana: <?=$semana;?>
        <?
            }
        ?>
        </td>
        <td colspan='2' align='right'>
            Total: <?='R$ '.number_format($valor_recebido_total, 2, ',', '.');?>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
</table>
<input type='hidden' name='id_emp' value='<?=$id_emp;?>'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>