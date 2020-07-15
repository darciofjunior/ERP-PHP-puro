<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/comunicacao.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/faturamentos.php');
require('../../../../../../lib/genericas.php');

//Esses $id_emp, se referem a Empresa do Menu ...
if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

//Variáveis que seram utilizadas mais abaixo ...
$aliquota_imposto_renda = 1.5;
$valor_minimo_gare      = 10;//O Valor Mínimo p/ se Emitir uma Gare é a partir de R$ 10,00 ...
?>
<html>
<head>
<title>.:: E-mail ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
</head>
<body>
<?
$vetor_representantes 	= explode(',', $_GET['cmb_representante']);

foreach($vetor_representantes as $id_representante) {
    unset($texto);//Limpo o conteúdo da variável para não continuar herdando valores do Loop Anterior ... 
    //Aqui eu verifico se o Representante é um Funcionário ...
    $sql = "SELECT f.id_cargo, f.id_pais, r.nome_representante, r.nome_fantasia, r.descontar_ir, r.pgto_comissao_grupo, r.tipo_pessoa, r.email 
            FROM `representantes` r 
            INNER JOIN `representantes_vs_funcionarios` rf ON rf.id_representante = r.id_representante 
            INNER JOIN `funcionarios` f ON f.id_funcionario = rf.id_funcionario 
            WHERE r.id_representante = '$id_representante' LIMIT 1 ";
    $campos_rep_func = bancos::sql($sql);//certifico que o rep é funcionario
    if(count($campos_rep_func) > 0) {//Se o Representante for Funcionário tem DSR ...
        $id_pais 			= $campos_rep_func[0]['id_pais'];
        $nome_representante		= $campos_rep_func[0]['nome_representante'];
        $nome_fantasia			= $campos_rep_func[0]['nome_fantasia'];
        $descontar_ir			= $campos_rep_func[0]['descontar_ir'];
        $tipo_pessoa			= $campos_rep_func[0]['tipo_pessoa'];
        $email 				= $campos_rep_func[0]['email'];
        $pgto_comissao_grupo            = $campos_rep_func[0]['pgto_comissao_grupo'];
    }else {//Se o Representante não for Funcionário tem DSR e busca dados diretamente da Tabela de Representantes ...
        $sql = "SELECT id_pais, nome_representante, nome_fantasia, descontar_ir, pgto_comissao_grupo, tipo_pessoa, email 
                FROM `representantes` 
                WHERE `id_representante` = '$id_representante' LIMIT 1 ";
        $campos_rep_func		= bancos::sql($sql);
        $id_pais                        = $campos_rep_func[0]['id_pais'];
        $nome_representante		= $campos_rep_func[0]['nome_representante'];
        $nome_fantasia			= $campos_rep_func[0]['nome_fantasia'];
        $descontar_ir			= $campos_rep_func[0]['descontar_ir'];
        $tipo_pessoa			= $campos_rep_func[0]['tipo_pessoa'];
        $email                          = $campos_rep_func[0]['email'];
        $pgto_comissao_grupo            = $campos_rep_func[0]['pgto_comissao_grupo'];
    }
    //Quando for enviando e-mail p/ o(s) Representante(s) selecionado(s), serão enviados dados das 3 empresas ...
    $vetor_empresas     = array(1, 2, 4);
    $campo_valor 	= ($id_pais == 31) ? ' nfsi.valor_unitario ' : ' nfsi.valor_unitario_exp ';
    $moeda 		= ($id_pais == 31) ? 'R$ ' : 'U$ ';
    /**********************Envia E-mail ao Representante**********************/
    $texto = 'À<br><br>';
    $texto.= $nome_representante.' ('.$nome_fantasia.').<br><br>';
    $texto.= 'Prezado(s) Senhor(es), <br><br>';
    $texto.= 'Segue relação das comissões fechadas no mês: <br><br>';
	
    for($e = 0; $e < count($vetor_empresas); $e++) {
        //Aqui eu zero essas variáveis para não herdar valores do Loop Anterior ...
        $total_vendas_diretas_por_empresa           = 0;
        $total_comissoes_vendas_diretas_por_empresa = 0;
        $ir_por_empresa                             = 0;
        $total_devolucoes_reembolsos_por_empresa    = 0;
        $sql = "SELECT nfs.`id_nf`, nfs.`id_empresa`, nfs.`data_emissao`, nfs.`suframa`, nfs.`status`, nfs.`snf_devolvida`, 
                IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, c.`id_pais`, 
                IF(nfs.`status` = '6', (SUM(ROUND((nfsi.`qtde_devolvida` * $campo_valor), 2)) * (-1)), SUM(ROUND((nfsi.`qtde` * $campo_valor), 2))) AS tot_mercadoria, 
                IF(nfs.`status` = '6', (SUM(ROUND((((nfsi.`qtde_devolvida` * $campo_valor) * (nfsi.`comissao_new` + nfsi.`comissao_extra`)) / 100), 2)) * (-1)), SUM(ROUND((((nfsi.`qtde` * $campo_valor) * (nfsi.`comissao_new` + nfsi.`comissao_extra`)) / 100), 2))) AS valor_comissao 
                FROM `nfs_itens` nfsi 
                INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                WHERE nfs.`data_emissao` BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' AND pvi.`id_representante` = '$id_representante' AND nfs.`id_empresa` = '$vetor_empresas[$e]' 
                GROUP BY nfsi.`id_nf` ORDER BY nfs.`data_emissao` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {
            if($vetor_empresas[$e] == 1 || $vetor_empresas[$e] == 2) {//Só pode haver discriminação das NF´s p/ as Empresas Alba e Tool
                $texto.= '<table width="880" border="0" cellspacing="1" cellpadding="0" align="center">';
                //Linha Principal ...
                $texto.= '<tr class="linhacabecalho" align="center">';
                $texto.= '<td colspan="6" bgcolor="#BEBEBE"><font color="darkgreen" face="arial black">'.genericas::nome_empresa($vetor_empresas[$e]).'</font></td>';
                $texto.= '</tr>';
                //Linha Rótulos ...
                $texto.= '<tr class="linhadestaque" align="center">';
                $texto.= '<td bgcolor="#E8E8E8"><font face="courier new" color="black">Data Emissão(NF)</font></td>';
                $texto.= '<td bgcolor="#E8E8E8"><font face="courier new" color="black">Nº NF</font></td>';
                $texto.= '<td bgcolor="#E8E8E8"><font face="courier new" color="black">Cliente</font></td>';
                $texto.= '<td bgcolor="#E8E8E8"><font face="courier new" color="black">Vendas '.$moeda.'</font></td>';
                $texto.= '<td bgcolor="#E8E8E8"><font face="courier new" color="black">Comis. '.$moeda.'</font></td>';
                $texto.= '<td bgcolor="#E8E8E8"><font face="courier new" color="black">Comis. Média %</font></td>';
                $texto.= '</tr>';

                for($i = 0; $i < $linhas; $i++) {
                    $rotulo_devolucao = ($campos[$i]['status'] == 6) ? '<font color="red"> (DEVOLUÇÃO)</font>' : '';
                    //Linha de Dados ...
                    $texto.= '<tr class="linhanormal" align="center">';
                    $texto.= '<td bgcolor="#FFFFE0"><font color="brown" face="courier new">'.data::datetodata($campos[$i]['data_emissao'], '/').'</font></td>';
                    $texto.= '<td bgcolor="#FFFFE0"><font color="brown" face="courier new">'.faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S').'</font></td>';
                    $texto.= '<td bgcolor="#FFFFE0" align="left"><font color="brown" face="courier new">'.$campos[$i]['cliente'].$rotulo_devolucao.'</font></td>';
                    $texto.= '<td bgcolor="#FFFFE0" align="right"><font color="brown" face="courier new">'.number_format($campos[$i]['tot_mercadoria'], 2, ',', '.').'</font></td>';
                    $texto.= '<td bgcolor="#FFFFE0" align="right"><font color="brown" face="courier new">'.number_format($campos[$i]['valor_comissao'], 2, ',', '.').'</font></td>';
                    $texto.= '<td bgcolor="#FFFFE0" align="right"><font color="brown" face="courier new">'.number_format(($campos[$i]['valor_comissao'] / $campos[$i]['tot_mercadoria']) * 100, 2, ',', '.').'</font></td>';
                    $texto.= '</tr>';
                    $total_vendas_diretas_por_empresa+=             $campos[$i]['tot_mercadoria'];
                    $total_comissoes_vendas_diretas_por_empresa+=   $campos[$i]['valor_comissao'];
                }
                /******************************************************************************************************************/
                /***********************************************Estorno de Comissões***********************************************/
                /******************************************************************************************************************/
                //Estorno de Comissões ...
                $sql = "SELECT DATE_FORMAT(ce.data_lancamento, '%d/%m/%Y %h:%i:%s') AS data_lancamento, ce.num_nf_devolvida, ce.tipo_lancamento, ce.porc_devolucao, ce.valor_duplicata, 
                        IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente, nfs.id_nf, nfs.id_empresa 
                        FROM `comissoes_estornos` ce 
                        INNER JOIN `nfs` ON nfs.id_nf = ce.id_nf 
                        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                        WHERE SUBSTRING(ce.data_lancamento, 1, 10) BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' AND ce.id_representante = '$id_representante' 
                        AND nfs.id_empresa = '$vetor_empresas[$e]' ORDER BY ce.data_lancamento ";
                $campos_devolucao = bancos::sql($sql);
                $linhas_devolucao = count($campos_devolucao);
                if($linhas_devolucao > 0) {
                    $texto.= '</table><br>';
                    $texto.= '<table width="880" border="0" cellspacing="1" cellpadding="0" align="center">';
                    //Linha Principal ...
                    $texto.= '<tr class="linhacabecalho" align="center">';
                    $texto.= '<td colspan="7" bgcolor="#BEBEBE"><font color="#00008B" face="arial black">Devoluções '.genericas::nome_empresa($vetor_empresas[$e]).'</font></td>';
                    $texto.= '</tr>';
                    //Linha Rótulos ...
                    $texto.= '<tr class="linhadestaque" align="center">';
                    $texto.= '<td bgcolor="#E8E8E8"><font face="courier new">Data de Lançamento</font></td>';
                    $texto.= '<td bgcolor="#E8E8E8"><font face="courier new">Tipo de Lançamento</font></td>';
                    $texto.= '<td bgcolor="#E8E8E8"><font face="courier new">NF</font></td>';
                    $texto.= '<td bgcolor="#E8E8E8"><font face="courier new">NF Baseada</font></td>';
                    $texto.= '<td bgcolor="#E8E8E8"><font face="courier new">Cliente</font></td>';
                    $texto.= '<td bgcolor="#E8E8E8"><font face="courier new">Valor</font></td>';
                    $texto.= '<td bgcolor="#E8E8E8"><font face="courier new">Comissão</font></td>';
                    $texto.= '</tr>';
                    for($i = 0; $i < $linhas_devolucao; $i++) {
                        $texto.= '<tr class="linhanormal" align="center">';
                        $texto.= '<td bgcolor="#FFFFE0"><font color="brown" face="courier new">'.$campos_devolucao[$i]['data_lancamento'].'</font></td>';

                        if($campos_devolucao[$i]['tipo_lancamento'] == 0) {
                                $tipo_lancamento = 'DEVOLUÇÃO DE CANCELAMENTO';
                        }else if($campos_devolucao[$i]['tipo_lancamento'] == 1) {
                                $tipo_lancamento = 'ATRASO DE PAGAMENTO';
                        }else if($campos_devolucao[$i]['tipo_lancamento'] == 2) {
                                $tipo_lancamento = 'ABATIMENTO';
                        }else if($campos_devolucao[$i]['tipo_lancamento'] == 3) {
                                $tipo_lancamento = 'REEMBOLSO';
                        }

                        $texto.= '<td bgcolor="#FFFFE0"><font color="brown" face="courier new">'.$tipo_lancamento.'</font></td>';
                        $texto.= '<td bgcolor="#FFFFE0"><font color="brown" face="courier new">'.$campos_devolucao[$i]['num_nf_devolvida'].'</font></td>';
                        $texto.= '<td bgcolor="#FFFFE0"><font color="brown" face="courier new">'.faturamentos::buscar_numero_nf($campos_devolucao[$i]['id_nf'], 'D').'</font></td>';
                        $texto.= '<td bgcolor="#FFFFE0" align="left"><font color="brown" face="courier new">'.$campos_devolucao[$i]['cliente'].'</font></td>';
                        $texto.= '<td bgcolor="#FFFFE0" align="right"><font color="brown" face="courier new">'.$moeda.number_format($campos_devolucao[$i]['valor_duplicata'], 2, ',', '.').'</font></td>';

                        if($campos_devolucao[$i]['tipo_lancamento'] == 3) {//REEMBOLSO
                            $total_devolucoes_reembolsos_por_empresa+= ($campos_devolucao[$i]['valor_duplicata'] * $campos_devolucao[$i]['porc_devolucao']) / 100;
                        }else {//DEVOLUÇÃO DE CANCELAMENTO, ATRASO DE PAGAMENTO, ABATIMENTO
                            $total_devolucoes_reembolsos_por_empresa-= ($campos_devolucao[$i]['valor_duplicata'] * $campos_devolucao[$i]['porc_devolucao']) / 100;
                        }
                        $texto.= '<td bgcolor="#FFFFE0" align="right"><font color="brown" face="courier new">'.number_format(($campos_devolucao[$i]['valor_duplicata'] * $campos_devolucao[$i]['porc_devolucao']) / 100, 2, ',', '.').'</font></td>';
                        $texto.= '</tr>';
                    }
                    $texto.= '<tr class="linhanormal" align="right">';
                    $texto.= '<td colspan="7" bgcolor="#E8E8E8"><font color="#00008B" face="arial black">Sub-Total R$ '.number_format($total_devolucoes_reembolsos_por_empresa, 2, ',', '.').'</font></td>';
                    $texto.= '</tr>';
                    $texto.= '</table>';
                    $texto.= '<table width="880" border="0" cellspacing="1" cellpadding="0" align="center">';
                }//Fim das Devoluções Manuais ...
                /******************************************************************************************************************/
                //Cálculos Gerais das Notas Fiscais Automáticas e Devoluções Manuais caso existirem ...
                $texto.= '<tr class="linhanormal" align="right">';
                $texto.= '<td colspan="5" bgcolor="#E8E8E8"><font color="#00008B" face="arial black">Sub Total sobre Vendas Diretas R$ '.number_format($total_vendas_diretas_por_empresa, 2, ',', '.').'</font></td>';
                $texto.= '<td bgcolor="#E8E8E8"><font color="#00008B" face="arial black">'.number_format($total_comissoes_vendas_diretas_por_empresa, 2, ',', '.').'</font></td>';
                $texto.= '</tr>';
                $texto.= '<tr class="linhanormal" align="right">';
                $texto.= '<td colspan="5" bgcolor="#E8E8E8"><font color="#00008B" face="arial black">Sub Total das Devoluções / Reembolsos R$</font></td>';
                $texto.= '<td bgcolor="#E8E8E8"><font color="#00008B" face="arial black">'.number_format($total_devolucoes_reembolsos_por_empresa, 2, ',', '.').'</font></td>';
                $texto.= '</tr>';
                //O imposto de Renda só pode ser calculado p/ as empresas Albafer e Tool Master ...
                if($vetor_empresas[$e] != 4) {
                    /*Somente se existir a Marcação no Cadastro do Representante, se o mesmo for do Brasil
                    e se o mesmo for Pessoa Jurídica ...*/
                    if(strtoupper($descontar_ir) == 'S' && $id_pais == 31 && $tipo_pessoa == 'J') {
                        
                        /*O Roberto mudou esse IF em 14/07/2017, se ninguém falar mais nada, mais pra frente irei arrancar o código 
                        comentado ...*/
                        /*Nesse caso por ser positivo o "$total_devolucoes_reembolsos_por_empresa", então esse tem que compor a fórmula 
                        o IR já foi descontado quando houve alguma Devolução anterior e agora precisa ser recomposto por haver um 
                        Reembolso ...*/
                        //if($total_devolucoes_reembolsos_por_empresa > 0) {
                            $ir_por_empresa=- round((round($total_comissoes_vendas_diretas_por_empresa, 2) + round($total_devolucoes_reembolsos_por_empresa, 2)) * $aliquota_imposto_renda / 100, 2);
                        /*}else {
                            $ir_por_empresa=- round(round($total_comissoes_vendas_diretas_por_empresa, 2) * $aliquota_imposto_renda / 100, 2);
                        }*/
                        if(abs($ir_por_empresa) < $valor_minimo_gare) $ir_por_empresa = 0;//Ignoro o Valor Mínimo pois ele é muito baixo ...
                    }
                }
                $texto.= '<tr class="linhanormal" align="right">';
                $texto.= '<td colspan="5" bgcolor="#E8E8E8"><font color="#00008B" face="arial black">Imposto de Renda R$ </font></td>';
                $texto.= '<td bgcolor="#E8E8E8"><font color="#00008B" face="arial black">'.number_format($ir_por_empresa, 2, ',', '.').'</font></td>';
                $texto.= '</tr>';
                $texto.= '<tr class="linhanormal" align="right">';
                $texto.= '<td colspan="5" bgcolor="#E8E8E8"><font color="#00008B" face="arial black">Total Geral R$</font></td>';
                $texto.= '<td bgcolor="#E8E8E8"><font color="#00008B" face="arial black">'.number_format($total_comissoes_vendas_diretas_por_empresa + $total_devolucoes_reembolsos_por_empresa + $ir_por_empresa, 2, ',', '.').'</font></td>';
                $texto.= '</tr>';
                $texto.= '</table><br>';
            }else {//Somente Grupo ...
                $texto.= '<table width="880" border="0" cellspacing="1" cellpadding="0" align="center">';
                //Linha Principal ...
                $texto.= '<tr class="linhacabecalho" align="center">';
                $texto.= '<td colspan="6" bgcolor="#BEBEBE"><font color="#00008B" face="arial black">'.genericas::nome_empresa($vetor_empresas[$e]).'</font></td>';
                $texto.= '</tr>';
                for($i = 0; $i < $linhas; $i++) {
                    $total_vendas_diretas_por_empresa+=             $campos[$i]['tot_mercadoria'];
                    $total_comissoes_vendas_diretas_por_empresa+=   $campos[$i]['valor_comissao'];
                }
                /******************************************************************************************************************/
                /***********************************************Estorno de Comissões***********************************************/
                /******************************************************************************************************************/
                //Estorno de Comissões...
                $sql = "SELECT DATE_FORMAT(ce.data_lancamento, '%d/%m/%Y %h:%i:%s') AS data_lancamento, ce.num_nf_devolvida, ce.tipo_lancamento, ce.porc_devolucao, ce.valor_duplicata, 
                        IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente, nfs.id_nf, nfs.id_empresa 
                        FROM `comissoes_estornos` ce 
                        INNER JOIN `nfs` ON nfs.id_nf = ce.id_nf 
                        INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                        WHERE SUBSTRING(ce.data_lancamento, 1, 10) BETWEEN '$_GET[data_inicial]' AND '$_GET[data_final]' AND ce.id_representante = '$id_representante' 
                        AND nfs.id_empresa = '$vetor_empresas[$e]' ORDER BY ce.data_lancamento ";
                $campos_devolucao = bancos::sql($sql);
                $linhas_devolucao = count($campos_devolucao);
                if($linhas_devolucao > 0) {
                    for($i = 0; $i < $linhas_devolucao; $i++) {
                        if($campos_devolucao[$i]['tipo_lancamento'] == 3) {//REEMBOLSO
                            $total_devolucoes_reembolsos_por_empresa+= ($campos_devolucao[$i]['valor_duplicata'] * $campos_devolucao[$i]['porc_devolucao']) / 100;
                        }else {//DEVOLUÇÃO DE CANCELAMENTO, ATRASO DE PAGAMENTO, ABATIMENTO
                            $total_devolucoes_reembolsos_por_empresa-= ($campos_devolucao[$i]['valor_duplicata'] * $campos_devolucao[$i]['porc_devolucao']) / 100;
                        }
                    }
                }//Fim das Devoluções Manuais ...
                $texto.= '<tr class="linhanormal" align="right">';
                $texto.= '<td colspan="5" bgcolor="#E8E8E8"><font color="#00008B" face="arial black">Total Geral R$</font></td>';
                $texto.= '<td bgcolor="#E8E8E8"><font color="#00008B" face="arial black">'.number_format($total_comissoes_vendas_diretas_por_empresa + $total_devolucoes_reembolsos_por_empresa + $ir_por_empresa, 2, ',', '.').'</font></td>';
                $texto.= '</tr>';
                $texto.= '</table><br>';
            }
        }//Fim do IF das Empresas ...
    }//Fim do Loop das Empresas ...
    $texto.= '<table width="880" border="0" cellspacing="1" cellpadding="0" align="center">';
    $texto.= '</table><br>';
    $texto.= 'Solicitamos o envio da NF ou recibo <font color="darkgreen" face="arial black">(ALBAFER e TOOL MASTER)</font> correspondente para que seja efetuado o pagamento das comissões por empresa. A mesma poderá ser enviada a princípio por e-mail e posteriormente pelo correio.<br><br>';
    $texto.= 'Sem mais, <br><br>';
    $texto.= 'Depto. Financeiro<br><br><br>';
    $texto.= '<img src="http://192.168.1.253/erp/albafer/imagem/marcas/Logo_60_anos.jpg" height="100" width="500">';
    
    //Aqui eu busco o e-mail "Remetente" do usuário logado ...
    $sql = "SELECT `email_externo` 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_email_remetente = bancos::sql($sql);
    comunicacao::email($campos_email_remetente[0]['email_externo'], $email, '"'.$campos_email_remetente[0]['email_externo'].'"; darcio@grupoalbafer.com.br', 'Relatório de Comissão', $texto);
}//Fim do Loop dos Representantes ...
?>
</body>
</html>
<Script Language = 'JavaScript'>
    alert('E-MAIL(S) ENVIADO(S) COM SUCESSO !')
    window.close()
</Script>