<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/calculos.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    //Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_nome_fantasia 	= $_POST['txt_nome_fantasia'];
        $txt_razao_social 	= $_POST['txt_razao_social'];
    }else {
        $txt_nome_fantasia 	= $_GET['txt_nome_fantasia'];
        $txt_razao_social 	= $_GET['txt_razao_social'];
    }
//Aqui eu listo todos os Clientes do Representante logado ...
    $sql = "SELECT DISTINCT(c.`id_cliente`), c.`cod_cliente`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, 
            c.`id_uf`, c.`endereco`, c.`cidade`, c.`ddi_com`, c.`ddd_com`, c.`telcom`, c.`cnpj_cpf`, ct.`tipo` 
            FROM `clientes` c 
            LEFT JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
            INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` 
            WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
            AND c.`razaosocial` LIKE '%$txt_razao_social%' 
            AND c.`ativo` = '1' ORDER BY c.`razaosocial` ";

    $sql_extra = "SELECT COUNT(DISTINCT(c.`id_cliente`)) AS total_registro 
            FROM `clientes` c 
            LEFT JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` 
            INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` 
            WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
            AND c.`razaosocial` LIKE '%$txt_razao_social%' 
            AND c.`ativo` = '1' ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = '<?=$PHP_SELF;?>?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Relatório de Preço(s) vs Imposto(s) por UF - Consultar Cliente(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
           Relatório de Preço(s) vs Imposto(s) por UF - Cliente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Cliente
        </td>
        <td>
            Tipo de Cliente
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Endereço
        </td>
        <td>
            Cidade / UF
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = "javascript:window.location = '".$PHP_SELF."?passo=2&id_cliente=".$campos[$i]['id_cliente']."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="<?=$url;?>" align='left'>
            <a href='#' class='link'>
                <?=$campos[$i]['cliente'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['endereco'];
            //Daí sim printa o complemento ...
            if(!empty($campos[$i]['endereco'])) echo ', '.$campos[$i]['num_complemento'];
        ?>
        </td>
        <td>
        <?
            $sql = "SELECT `sigla` 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
            $campos_uf 	= bancos::sql($sql);
            echo $campos[$i]['cidade'].' / '.$campos_uf[0]['sigla'];
        ?>
        </td>
        <td>
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
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = '<?=$PHP_SELF;?>'" class='botao'>
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
    //Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_cliente                             = $_POST['id_cliente'];
        $cmb_uf                                 = $_POST['cmb_uf'];
        $hdd_submetido                          = $_POST['hdd_submetido'];
        $cmb_gpa_vs_emp_div                     = $_POST['cmb_gpa_vs_emp_div'];
        $cmb_exibir_por                         = $_POST['cmb_exibir_por'];
        $chkt_somente_com_codigo_barra          = $_POST['chkt_somente_com_codigo_barra'];
        $chkt_somente_produtos_adquiridos       = $_POST['chkt_somente_produtos_adquiridos'];
        $txt_referencia_discriminacao           = $_POST['txt_referencia_discriminacao'];
    }else {
        $id_cliente                             = $_GET['id_cliente'];
        $cmb_uf                                 = $_GET['cmb_uf'];
        $hdd_submetido                          = $_GET['hdd_submetido'];
        $cmb_gpa_vs_emp_div                     = $_GET['cmb_gpa_vs_emp_div'];
        $cmb_exibir_por                         = $_GET['cmb_exibir_por'];
        $chkt_somente_com_codigo_barra          = $_GET['chkt_somente_com_codigo_barra'];
        $chkt_somente_produtos_adquiridos       = $_GET['chkt_somente_produtos_adquiridos'];
        $txt_referencia_discriminacao           = $_GET['txt_referencia_discriminacao'];
    }
?>
<html>
<head>
<title>.:: Relatório de Preço(s) vs Imposto(s) por UF ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function gerar_relatorio_excel() {
    document.form.action = 'relatorio_em_excel.php'
    document.form.target = 'gerar_relatorio_em_excel'
    nova_janela('relatorio_em_excel.php', 'gerar_relatorio_em_excel', '', '', '', '', 450, 700, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
    
}

function atualizar() {
    document.form.hdd_submetido.value = 1
    document.form.action = ''
    document.form.target = '_self'
    document.form.submit()
}
    
function controlar_objetos() {
    if(document.form.cmb_exibir_por.value != '') {//Se essa opção estiver habilitada, então não é possível digitar nenhuma Ref / Disc ...
        document.form.txt_referencia_discriminacao.disabled     = true
        document.form.txt_referencia_discriminacao.className    = 'textdisabled'
    }else {//Se essa opção estiver desabilitada, então é possível digitar nenhuma Ref / Disc ...
        document.form.txt_referencia_discriminacao.disabled     = false
        document.form.txt_referencia_discriminacao.className    = 'caixadetexto'
        document.form.txt_referencia_discriminacao.focus()
    }
    document.form.txt_referencia_discriminacao.value    = ''
}
</Script>
</head>
<body onload='controlar_objetos()'>
<form name='form' method='post' action=''>
<!--***********Controles de Tela***********-->
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='cmb_uf' value='<?=$cmb_uf;?>'>
<input type='hidden' name='hdd_submetido' value='0'>
<input type='hidden' name='passo' value='2'>
<!--***************************************-->
<table width='95%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td colspan='20'>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2' color='red'>
                <b>Observação de IVA:</b>
            </font>
            <font face='Verdana, Geneva, Arial, Helvetica, sans-serif' size='2'>
                <b><i>* No estado de SP só teremos IVA quando a OF do Produto Acabado = 'Industrial', 
                em outros estados teremos IVA independe da OF do Produto Acabado.</i></b>
            </font>
            <br/><br/>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='20'>
            Relatório de Preço(s) vs Imposto(s) por UF
            <br/>
            <font color='yellow' size='-1'>
            <?
                if($id_cliente > 0) {//Se o usuário consultou por Cliente ...
                    //Aqui eu busco alguns dados de Cliente ...
                    $sql = "SELECT c.`id_uf`, IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente, u.`sigla`, u.`convenio` 
                            FROM `clientes` c 
                            INNER JOIN `ufs` u ON u.id_uf = c.id_uf  
                            WHERE c.`id_cliente` = '$id_cliente' LIMIT 1 ";
                    $campos_cliente    = bancos::sql($sql);
                    if($campos_cliente[0]['sigla'] != 'SP') {//Essa mensagem só irá nos mostrar quando for Cliente de um outro Estado ...
                        $convenio = ($campos_cliente[0]['convenio'] != '') ? 'Convênio => ('.$campos_cliente[0]['convenio'].')' : ' (Não Existe Convênio c/ SP)';
                    }
                    $id_uf  = $campos_cliente[0]['id_uf'];
                    $uf     = $campos_cliente[0]['sigla'];
                    echo $campos_cliente[0]['cliente'].' - '.$uf.' '.$convenio;
                }else {//Se o usuário consultou por Estado ...
                    //Aqui dados da UF ...
                    $sql = "SELECT `sigla`, `convenio` 
                            FROM `ufs` 
                            WHERE `id_uf` = '$cmb_uf' LIMIT 1 ";
                    $campos_uf  = bancos::sql($sql);
                    if($campos_uf[0]['sigla'] != 'SP') {//Essa mensagem só irá nos mostrar quando for Cliente de um outro Estado ...
                        $convenio = ($campos_uf[0]['convenio'] != '') ? 'Convênio => ('.$campos_uf[0]['convenio'].')' : ' (Não Existe Convênio c/ SP)';
                    }
                    $id_uf  = $cmb_uf;
                    $uf     = $campos_uf[0]['sigla'];
                    echo $uf.' '.$convenio;
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='20'>
            <font size='2' color='black'>
                <b>Grupo vs Empresa Divisão:</b>
            </font>
            <select name='cmb_gpa_vs_emp_div' title='Selecione um Grupo vs Empresa Divisão' class='combo'>
            <?
                $sql = "SELECT ged.`id_gpa_vs_emp_div`, CONCAT(UPPER(gpa.`nome`), ' (', UPPER(ed.`razaosocial`), ')') AS rotulo 
                        FROM `gpas_vs_emps_divs` ged 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                        INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
                        ORDER BY `rotulo` ";
                echo combos::combo($sql, $cmb_gpa_vs_emp_div);
            ?>
            </select>
            &nbsp;
            <font size='2' color='black'>
                <b>Referência / Discriminação: </b>
            </font>
            <input type='text' name='txt_referencia_discriminacao' value='<?=$txt_referencia_discriminacao;?>' size='50' class='textdisabled' disabled>
            <br/>
            <?
                if(!empty($hdd_submetido)) {//Demais vezes ...
                    if($cmb_exibir_por == 'F') {
                        $selectedf  = 'selected';
                    }else if($cmb_exibir_por == 'G') {
                        $selectedg  = 'selected';
                    }
                    $checked_somente_com_codigo_barra       = ($chkt_somente_com_codigo_barra == 'S') ? 'checked' : '';
                    $checked_somente_produtos_adquiridos    = ($chkt_somente_produtos_adquiridos == 'S') ? 'checked' : '';
                }else {//Só irá entrar aqui na 1ª vez quando acabamos entramos na Tela ...
                    $selectedg                              = 'selected';
                    $checked_somente_com_codigo_barra       = 'checked';
                    $checked_somente_produtos_adquiridos    = '';
                }
            ?>
            Exibir por: 
            <select name='cmb_exibir_por' title='Selecione um Modo de Exibição' onchange='controlar_objetos()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='F' <?=$selectedf;?>>FAMÍLIA</option>
                <option value='G' <?=$selectedg;?>>GRUPO VS EMPRESA DIVISÃO</option>
            </select>
            <input type='checkbox' name='chkt_somente_com_codigo_barra' value='S' id='chkt_somente_com_codigo_barra' title='Somente c/ Código de Barra' class='checkbox' <?=$checked_somente_com_codigo_barra;?>>
            <label for='chkt_somente_com_codigo_barra'>
                Somente c/ Código de Barra
            </label>
            <input type='checkbox' name='chkt_somente_produtos_adquiridos' value='S' id='chkt_somente_produtos_adquiridos' title='Somente dos Produtos Adquiridos' class='checkbox' <?=$checked_somente_produtos_adquiridos;?>>
            <label for='chkt_somente_produtos_adquiridos'>
                Somente dos Produtos Adquiridos
            </label>
            &nbsp;
            <input type='button' name='cmd_gerar_relatorio_excel' value='Gerar Relatório em Excel' title='Gerar Relatório em Excel' onclick='gerar_relatorio_excel()' style='color:red' class='botao'>
            &nbsp;
            <input type='button' name='cmd_atualizar' value='Atualizar' title='Atualizar' onclick='atualizar()' class='botao'>
        </td>
    </tr>
<?
    if(!empty($hdd_submetido)) {
        if(empty($cmb_gpa_vs_emp_div)) $cmb_gpa_vs_emp_div = '%';
        //Se essa opção estiver marcada eu apresento os dados de Impostos agrupando por ...
        if($cmb_exibir_por == 'F') {//Família ...
            $group_by = ' GROUP BY f.`id_familia` ';
            $order_by = ' ed.`razaosocial`, gpa.`nome` ';
        }else if($cmb_exibir_por == 'G') {//Grupo PA vs Empresa Divisão ...
            $group_by = ' GROUP BY ged.`id_gpa_vs_emp_div` ';
            $order_by = ' ed.`razaosocial`, gpa.`nome` ';
        }else {
            $group_by = ' GROUP BY pa.`id_produto_acabado` ';
            $order_by = ' pa.`referencia`, pa.`discriminacao` ';
        }
        if($chkt_somente_com_codigo_barra == 'S') $condicao_codigo_barra = " AND pa.`codigo_barra` <> '' ";
        
        /*********************************************************************************************************/
        /************************Busca abaixo todos os PA(s) Adquiridos pelo nosso Cliente************************/
        /*********************************************************************************************************/
        if($_POST['chkt_somente_produtos_adquiridos'] == 'S') {
            $sql = "SELECT DISTINCT(pvi.`id_produto_acabado`) AS id_produto_acabado 
                    FROM `pedidos_vendas` pv 
                    INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda` = pv.`id_pedido_venda` 
                    WHERE pv.`id_cliente` = '$_POST[id_cliente]' ORDER BY pvi.`id_produto_acabado` ";
            $campos_produto_acabado = bancos::sql($sql);
            $linhas_produto_acabado = count($campos_produto_acabado);
            for($i = 0; $i < $linhas_produto_acabado; $i++) $vetor_produtos_acabados[] = $campos_produto_acabado[$i]['id_produto_acabado'];
            $condicao_produtos_acabados = " AND pa.`id_produto_acabado` IN (".implode($vetor_produtos_acabados, ',').") ";
        }
        /*********************************************************************************************************/
        
        //Fiz esse tratamento porque quando o Cliente é Estrangeiro não existe UF ...
        if(!empty($id_uf)) $condicao_uf = "AND i.`id_uf` = '$id_uf' ";
        
        //Busca de todos os PA(s) que são normais de Linha ...
        $sql = "SELECT cf.`classific_fiscal`, cf.`cest`, cf.`ipi`, ed.`razaosocial`, f.`nome` AS familia, gpa.`nome`, 
                ged.`id_empresa_divisao`, ged.`desc_base_a_nac`, ged.`desc_base_b_nac`, ged.`acrescimo_base_nac`, 
                pa.`id_produto_acabado`, pa.`operacao`, 
                pa.`referencia`, pa.`origem_mercadoria`, pa.`discriminacao`, 
                pa.`preco_unitario`, pa.`peso_unitario`, pa.`preco_export`, pa.`status_top`, 
                pa.`codigo_barra`, u.`sigla` 
                FROM `produtos_acabados` pa 
                INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_gpa_vs_emp_div` LIKE '$cmb_gpa_vs_emp_div' 
                INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
                INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa 
                INNER JOIN `familias` f ON f.id_familia = gpa.id_familia AND f.`ativo` = '1' AND f.`id_familia` NOT IN (23, 24)  
                INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` AND cf.`ativo` = '1' 
                INNER JOIN `icms` i ON i.`id_classific_fiscal` = cf.`id_classific_fiscal` $condicao_uf AND i.`ativo` = '1' 
                WHERE (pa.referencia LIKE '%$txt_referencia_discriminacao%' OR pa.discriminacao LIKE '%$txt_referencia_discriminacao%') 
                AND pa.`referencia` <> 'ESP' 
                AND pa.`ativo` = '1' 
                $condicao_codigo_barra 
                $condicao_produtos_acabados 
                $group_by ORDER BY $order_by ";
        $campos = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
        $linhas	= count($campos);
    }
    if($linhas == 0) {//Significa que não existe nenhuma Compra do Cliente p/ a Família selecionada ...
?>
<table width='95%' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '<?=$PHP_SELF.$parametro;?>'" style='color:red' class='botao'>
        </td>
    </tr>
</table>
<?
    }else {//Existe pelo menos uma Compra da Família ou Famílias ...
?>
    <tr class='linhacabecalho' align='center'>
        <td>Referência</td>
        <td>Discriminação</td>
        <td>Código de Barra</td>
        <td>Classif <br>Fiscal</td>
        <td>CEST</td>
        <td>O.F.</td>
        <td>
            <font title='Código de Situação Tributária' style='cursor:help'>
                CST
            </font>
        </td>
        <td>Padrão <br>Emb</td>
        <td>Peso <br>Unit.</td>
        <td>Lead <br>Time</td>
        <td>Vlr Unit <br>Bruto R$</td>
        <td>
            % ICMS
            <font color='yellow'>
                <br><?=$uf;?>
            </font>
        </td>
        <td>
            Dif % ICMS
            <font color='yellow'>
                <br><?=$uf;?> p/ SP
            </font>
        </td>
        <td>% Red BC</td>
        <td>% ICMS <br>c/ Red BC</td>
        <td>% ICMS <br>Intra Est</td>
        <td>
            IVA
        </td>
        <td>
            <font title='Substituição Tributária (% Aproximada)' style='cursor:help'>
                ST (% Aprox)
            </font>
        </td>
        <td>% IPI</td>
        <td>IPI R$</td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $dados_produto_uf_sp        = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], 1);
            $dados_produto_uf_cliente   = intermodular::dados_impostos_pa($campos[$i]['id_produto_acabado'], $id_uf);
?>
    <tr class='linhanormal' align='center'>
        <td>
        <?
            //Se essa opção estiver marcada não apresento a Referência ...
            if(!empty($cmb_exibir_por)) {
                echo '-';
            }else {
                echo $campos[$i]['referencia'];
            }
        ?>
        </td>
        <td align='left'>
        <?
            //Se essa opção estiver marcada não apresento a Discriminação, só a Família ...
            if(!empty($cmb_exibir_por)) {
                if($cmb_exibir_por == 'F') {
                    echo strtoupper($campos[$i]['familia']);
                }else if($cmb_exibir_por == 'G') {
                    echo strtoupper($campos[$i]['razaosocial']).' - '.strtoupper($campos[$i]['nome']);
                }
            }else {
                echo $campos[$i]['discriminacao'];
            }
        ?>
        </td>
        <td>
        <?
            //Se essa opção estiver marcada não apresento o Código de Barra ...
            if(!empty($cmb_exibir_por)) {
                echo '-';
            }else {
                echo $campos[$i]['codigo_barra'];
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td>
            <?=$campos[$i]['cest'];?>
        </td>
        <td>
        <?
            //Se essa opção estiver marcada não apresento a Operação de Custo ...
            if(!empty($cmb_exibir_por)) {
                echo '-';
            }else {
                if($campos[$i]['operacao'] == 0) {
                    echo '<font title="Industrializado" style="cursor:help"><b>Ind</b></font>';
                }else {
                    echo '<font title="Revenda" style="cursor:help"><b>Rev</b></font>';
                }
            }
        ?>
        </td>
        <td>
        <?
            //Se essa opção estiver marcada não apresento a Situação Tributária ...
            if(!empty($cmb_exibir_por)) {
                echo '-';
            }else {
                echo $dados_produto_uf_cliente['cst'];
            }
        ?>
        </td>
        <td>
        <?
            //Se essa opção estiver marcada não apresento a Peças por Embalagem ...
            if(!empty($cmb_exibir_por)) {
                echo '-';
            }else {
                $sql = "SELECT pecas_por_emb 
                        FROM `pas_vs_pis_embs` 
                        WHERE id_produto_acabado = '".$campos[$i]['id_produto_acabado']."' 
                        AND embalagem_default = '1' ";
                $campos_pecas = bancos::sql($sql);
                echo number_format($campos_pecas[0]['pecas_por_emb'], 3, ',', '.').' '.$campos[$i]['sigla'];
            }
        ?>
        </td>
        <td>
        <?
            //Se essa opção estiver marcada não apresento o Peso Unitário ...
            if(!empty($cmb_exibir_por)) {
                echo '-';
            }else {
                echo number_format($campos[$i]['peso_unitario'], 3, ',', '.').' g';
            }
        ?>
        </td>
        <td>
            Imediato
        </td>
        <td align='right'>
        <?
            //Se essa opção estiver marcada não apresento o Preço Unitário ...
            if(!empty($cmb_exibir_por)) {
                echo '-';
            }else {
                echo number_format($campos[$i]['preco_unitario'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
            if($dados_produto_uf_cliente['icms'] > 0) {
                echo number_format($dados_produto_uf_cliente['icms'], 2, ',', '.');
            }else {
                echo 'S/ICMS';
            }
        ?>
        </td>
        <td>
        <?
            if($dados_produto_uf_sp['icms'] - $dados_produto_uf_cliente['icms'] > 0) {
                echo number_format($dados_produto_uf_sp['icms'] - $dados_produto_uf_cliente['icms'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
            if($dados_produto_uf_cliente['reducao'] > 0) {
                echo number_format($dados_produto_uf_cliente['reducao'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
            if($dados_produto_uf_cliente['icms'] > 0) {
                echo number_format(($dados_produto_uf_cliente['icms'] - ($dados_produto_uf_cliente['icms'] * $dados_produto_uf_cliente['reducao']) / 100), 2, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
            if($dados_produto_uf_cliente['icms_intraestadual'] > 0) {
                echo number_format($dados_produto_uf_cliente['icms_intraestadual'], 2, ',', '.');
            }
        ?>
        </td>
        <td>
        <?
            if($uf == 'SP') {
                //Em SP só existirá IVA quando a OF do PA for Industrial ...
                if($campos[$i]['operacao'] == 0) {
                    if($dados_produto_uf_sp['iva'] > 0) {
                        echo number_format($dados_produto_uf_sp['iva'], 2, ',', '.');
                    }else {
                        echo 'S/IVA';
                    }
                }else {
                    echo 'S/IVA';
                }
            }else {//Em qualquer outro Estado independente da OF do PA ser Industrial ou Revenda, existirá IVA ...
                if($dados_produto_uf_cliente['iva'] > 0) {
                    echo number_format($dados_produto_uf_cliente['iva'], 2, ',', '.');
                }else {
                    echo 'S/IVA';
                }
            }
        ?>
        </td>
        <td>
        <?
        //Se essa opção estiver marcada não apresento o Preço Unitário ...
            if(!empty($cmb_exibir_por)) {
                $valor_total = 10;//Pelo fato de não existir um único PA em específico, coloco como se o Preço Médio de cada um fosse R$ 10,00 ...
            }else {
                $valor_total = 1 * $campos[$i]['preco_unitario'];
            }
            $valor_icms                             = ($valor_total * $dados_produto_uf_cliente['icms'] / 100);
            $vetor_dados_substituicao_tributaria    = calculos::calculos_substituicao_tributaria($dados_produto_uf_cliente['ipi'], $dados_produto_uf_cliente['icms'], $dados_produto_uf_cliente['icms_intraestadual'], $dados_produto_uf_cliente['iva'], $valor_total, $valor_icms);
            
            echo number_format(($vetor_dados_substituicao_tributaria['valor_icms_st_item_current_rs'] * 100) / $valor_total, 2, ',', '.').'% ';
        ?>
        </td>
        <td>
        <?
            if($dados_produto_uf_cliente['ipi'] > 0) {
                echo number_format($dados_produto_uf_cliente['ipi'], 2, ',', '.');
            }else {
                echo 'S/IPI';
            }
        ?>
        </td>
        <td align='right'>
        <?
            //Se essa opção estiver marcada não apresento o Código de Barra ...
            if(!empty($cmb_exibir_por)) {
                echo '-';
            }else {
                echo number_format(($campos[$i]['preco_unitario'] * $dados_produto_uf_cliente['ipi'] / 100), 2, ',', '.');
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='20'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '<?=$PHP_SELF;?>'" style='color:red' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</form>
</body>
</html>
<?
}else {
?>
<html>
<head>
<title>.:: Relatório de Preço(s) vs Imposto(s) por UF ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function controle_de_objetos() {
    if(document.form.cmb_uf.value == '' && document.form.txt_razao_social.value == '' && document.form.txt_nome_fantasia.value == '') {
        //Cor dos Objetos ...
        document.form.txt_razao_social.className    = 'caixadetexto'
        document.form.txt_nome_fantasia.className   = 'caixadetexto'
        document.form.cmb_uf.className              = 'combo'
        //Habilitação dos Objetos ...
        document.form.txt_razao_social.disabled     = false
        document.form.txt_nome_fantasia.disabled    = false
        document.form.cmb_uf.disabled               = false
    }else if(document.form.cmb_uf.value == '' && (document.form.txt_razao_social.value != '' || document.form.txt_nome_fantasia.value != '')) {
        //Cor dos Objetos ...
        document.form.txt_razao_social.className    = 'caixadetexto'
        document.form.txt_nome_fantasia.className   = 'caixadetexto'
        document.form.cmb_uf.className              = 'textdisabled'
        //Habilitação dos Objetos ...
        document.form.txt_razao_social.disabled     = false
        document.form.txt_nome_fantasia.disabled    = false
        document.form.cmb_uf.disabled               = true
    }else {
        //Cor dos Objetos ...
        document.form.txt_razao_social.className    = 'textdisabled'
        document.form.txt_nome_fantasia.className   = 'textdisabled'
        document.form.cmb_uf.className              = 'combo'
        //Habilitação dos Objetos ...
        document.form.txt_razao_social.disabled     = true
        document.form.txt_nome_fantasia.disabled    = true
        document.form.cmb_uf.disabled               = false
    }
}
    
function validar() {
//O usuário sempre terá que fazer algum Filtro antes de acessar o Relatório ...
    if(document.form.cmb_uf.value == '' && document.form.txt_razao_social.value == '' && document.form.txt_nome_fantasia.value == '') {
        alert('DIGITE ALGUM CLIENTE OU SELECIONE ALGUM ESTADO !')
        document.form.txt_razao_social.focus()
        return false
    }
//Se o Estado foi preenchido ...
    document.form.passo.value = (document.form.cmb_uf.value > 0) ? 2 : 1
}
</Script>
</head>
<body onload='document.form.txt_razao_social.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF;?>' onsubmit='return validar()'>
<input type='hidden' name='passo'>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Relatório de Preço(s) vs Imposto(s) por UF - Consultar Cliente(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Razão Social:
        </td>
        <td>
            <input type='text' name='txt_razao_social' title='Digite a Razão Social' maxlength='50' size='60' onkeyup='controle_de_objetos()' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome Fantasia:
        </td>
        <td>
            <input type='text' name='txt_nome_fantasia' title='Digite a Nome Fantasia' maxlength='40' size='50' onkeyup='controle_de_objetos()' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Estado:
        </td>
        <td>
            <select name='cmb_uf' title='Selecione o Estado' onchange='controle_de_objetos()' class='combo'>
            <?
                $sql = "SELECT `id_uf`, `sigla` 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY `sigla` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.reset()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>