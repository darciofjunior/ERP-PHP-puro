<?
/*Eu tenho esse desvio aki para não redeclarar as bibliotecas novamente, isso porque tem alguns arquivos 
q essa parte de estoque embutida e sendo assim já tem as bibliotecas declaradas logo no início*/
//class_exists()
if($nao_chamar_biblioteca != 1) {
    require('../../../lib/segurancas.php');
    require('../../../lib/biblioteca.php');
    require('../../../lib/custos.php');
    require('../../../lib/data.php');
    require('../../../lib/estoque_acabado.php');
    require('../../../lib/intermodular.php');
    require('../../../lib/vendas.php');
    $nivel_arquivo = '../../';
    $nivel_imagem = '../../../';
}else {
/*Tem esse outro desvio, pq dependendo do lugar em que eu chamo essa função, os níveis tanto de arquivo
como de imagem são iguais aos níveis menores como os acima ...*/
    if($nivel_reduzido == 1) {
        $nivel_arquivo = '../../';
        $nivel_imagem = '../../../';
    }else {
        $nivel_arquivo = '../../../';
        $nivel_imagem = '../../../../';
    }
}

if(!class_exists('custos'))     require('../../../lib/custos.php');// CASO EXISTA EU DESVIO A CLASSE
session_start('funcionarios');

//Significa que essa variável vai ser um array para auxiliar na apresentação de Dados do P.A. por Unidade
$vetor_unidade = array();

//Significa que veio da tela de Orçamentos
//Se não for passado o id_produto_acabado por parâmetro então eu preciso buscar c/ o id_orcamento_venda_item
if(!empty($id_orcamento_venda_item)) {
    $sql = "SELECT `id_produto_acabado` 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_produto_acabado = $campos[0]['id_produto_acabado'];
}

/*******************************************************************************************************************/
/****$_GET['trazer_somente_pa_componente'] -> Variável sempre será vazia na 1ª vez em que carregarmos a Tela ...****/
/*******************************************************************************************************************/

if(empty($_GET['trazer_somente_pa_componente'])) {//Significa que é p/ trazer todos os Produtos Acabados ...
    //Primeira verificação é ver se o "$id_produto_acabado" é um Componente ...
    $sql = "SELECT gpa.`id_grupo_pa` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_familia` IN (23, 24) 
            WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos_componente  = bancos::sql($sql);
    if(count($campos_componente) == 1) {//É componente e sendo assim trarei somente o mesmo em uma Tela Isolada ...
?>
        <center>
            <iframe src = '/erp/albafer/modulo/classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$id_produto_acabado;?>&trazer_somente_pa_componente=S' width='100%' height='280' frameborder='0'></iframe>
        </center>
<?
    }
}

if(empty($_GET['trazer_somente_pa_componente'])) {//Significa que é p/ trazer todos os Produtos Acabados ...
    $sql = "SELECT `explodir_view_estoque`, `discriminacao` 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos_explodir = bancos::sql($sql);
    if(strtoupper($campos_explodir[0]['explodir_view_estoque']) == 'S') {//se sim posso pegar todos os PA relacionados
        /*
        Observação: Essa variável "$id_pa_atrelados" esta como global pq tenho que pegar o id PA principal 
        e depois vejo os atrelados, ficando assim ordenado ...

        Coloquei esse nome "$GLOBALS['vetor_pa_atrelados'][]" porque a Biblioteca mais abaixo 
        vendas::calcular_preco_de_queima_pa( utiliza uma variável Global também com o nome de "$GLOBALS['id_pa_atrelados'][]"
        que acabava dando conflito nessa Tela e trazendo alguns Produtos que não tinham nada a ver ... 
        */
        $vetor_pa_atrelados                 = custos::pas_atrelados($id_produto_acabado);
        $linhas_produto_acabado             = count($vetor_pa_atrelados);
        //Se a Qtde de Produtos Acabados for maior do que 8, manda email para o Roberto ...
        if($linhas_produto_acabado > 10) {//Se for mandar e-mail dizendo que está atrapalhando a visualização ...
            $produto                        = $campos_explodir[0]['discriminacao'];
            if(!class_exists('comunicacao')) { require '../../../lib/comunicacao.php';} //CASO EXISTA EU DESVIO A CLASSE
            require('../../../lib/variaveis/intermodular.php');
            $destino                        = $visualizar_estoque;
            $assunto                        = 'ERP - Visualizar Estoque '.date('d-m-Y H:i:s');
            $mensagem                       = 'O Produto '.$produto.' possui mais de vinte atrelamento(s) relacionado(s).';
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', $assunto, $mensagem);
        }
    }else {
        $bloqueio                           = 'Este P.A. está marcado para não explodir os PA´s atrelados';
        
        $vetor_pa_atrelados[]               = $id_produto_acabado;
        $linhas_produto_acabado             = count($vetor_pa_atrelados);
    }
}else {//Significa que é p/ trazer somente o PA componente que foi passado por parâmetro ...
    $bloqueio                           = 'Este P.A. está marcado para não explodir os PA´s atrelados';
    
    $vetor_pa_atrelados[]               = $id_produto_acabado;
    $linhas_produto_acabado             = count($vetor_pa_atrelados);
}

/*Esse trecho de tela foi feito em um arquivo à parte, p/ evitar de recarregar toda a tela do 
Estoque Acabado que daí seria muito lento, achamos mais fácil e mais rápido recarregar apenas
o Iframe que é exatamente esse arquivo na hora em que o usuário altera o Prazo de Entrega ...*/
$data_atual_menos_sete = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), '-7'), '-');
?>
<html>
<head>
<title>.:: Visualizar Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='7'>
            <fieldset>
                <legend>
                    <b>CONSULTAR ESTOQUE</b>
                </legend>
<?
for($i = 0; $i < $linhas_produto_acabado; $i++) {//Lista os PA(s) Atrelado(s) ...
    if($i > 0) {//P/ cada novo PA que for apresentado, crio uma nova Tabela na intenção de criar uma Separação ...
?>
                <table border='0'>
                    <tr>
                        <td height='1'></td>
                    </tr>
                </table>
<?
        $titulo = 'Produto Atrelado';
    }else {
        $titulo = 'Produtos';
    }
    
    $sql = "SELECT ged.`path_pdf`, gpa.`id_familia`, pa.`operacao_custo`, pa.`referencia`, pa.`pecas_por_jogo`, 
            pa.`mmv`, pa.`observacao`, pa.`qtde_queima_estoque`, u.`sigla` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
            WHERE pa.`id_produto_acabado` = '".$vetor_pa_atrelados[$i]."' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $path_pdf           = $campos[0]['path_pdf'];
    $id_familia         = $campos[0]['id_familia'];
    $operacao_custo     = $campos[0]['operacao_custo'];
    $referencia         = $campos[0]['referencia'];
    $pecas_por_jogo     = $campos[0]['pecas_por_jogo'];
    $mmv                = $campos[0]['mmv'];
    $observacao         = $campos[0]['observacao'];
    $sigla              = $campos[0]['sigla'];

    /*Aqui eu verifico a qtde disponível desse item em Estoque e a qtde dele em Produção*/
    $estoque_produto                = estoque_acabado::qtde_estoque($vetor_pa_atrelados[$i]);
    $quantidade_estoque             = $estoque_produto[0];
    $producao                       = $estoque_produto[2];
    $qtde_disponivel                = $estoque_produto[3];
    $racionado                      = $estoque_produto[5];
    $qtde_pendente                  = $estoque_produto[7];
    $estoque_comprometido           = $estoque_produto[8];
    $qtde_pa_possui_item_faltante   = $estoque_produto[9];
    $qtde_oe_em_aberto              = $estoque_produto[11];
    $qtde_fornecedor                = $estoque_produto[12];
    $qtde_porto                     = $estoque_produto[13];
    $entrada_antecipada             = $estoque_produto[15];
    $compra                         = estoque_acabado::compra_producao($vetor_pa_atrelados[$i]);

    if($id_familia == 9) {//Família de Machos o raciocínio é um pouquinho diferente ...
        $total_qtde_disponivel_blank+= ($qtde_disponivel * $pecas_por_jogo);
        $total_quantidade_estoque_blank+= ($quantidade_estoque * $pecas_por_jogo);
        $total_compra_producao_blank+= (($producao + $compra) * $pecas_por_jogo);
        $total_estoque_comprometido_blank+= ($estoque_comprometido * $pecas_por_jogo);
        $total_mmv_blank+= ($mmv * $pecas_por_jogo);
    }else {//Outras Famílias ...
        if(!in_array($sigla, $vetor_unidade)) $vetor_unidade[] = $sigla;
        
        //A idéia dessa rotina é armazenar o valor do Total do P.A. só que por Unidade ...
        //Acumulo os valores nas variáveis arrays para Apresentar no fim da Tela ...
        $total_qtde_disponivel[$sigla]+= $qtde_disponivel;
        $total_quantidade_estoque[$sigla]+= $quantidade_estoque;
        $total_compra_producao[$sigla]+= ($producao + $compra);
        $total_estoque_comprometido[$sigla]+= $estoque_comprometido;
        $total_mmv[$sigla]+= $mmv;
    }

    //Se retornar nulo do banco
    if($qtde_disponivel == '') 	$qtde_disponivel = 0;
    if($qtde_producao == '') 	$qtde_producao = 0;
    if($racionado == '')        $racionado = 0;
    if($qtde_fornecedor == '')  $qtde_fornecedor = 0;
    if($qtde_porto == '')       $qtde_porto = 0;
    
//Busca os dados do grupo_pa e da empresa divisão através do id_produto_acabado
    $sql = "SELECT ed.`id_empresa_divisao`, ed.`razaosocial`, gpa.`id_grupo_pa`, gpa.`nome`, gpa.`prazo_entrega` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `empresas_divisoes` ed ON ed.`id_empresa_divisao` = ged.`id_empresa_divisao` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pa.`id_produto_acabado` = '".$vetor_pa_atrelados[$i]."' LIMIT 1 ";
    $campos_grupo_pa    = bancos::sql($sql);
    if(count($campos_grupo_pa) == 1) {
        $razaosocial    = $campos_grupo_pa[0]['razaosocial'];
        $id_grupo_pa    = $campos_grupo_pa[0]['id_grupo_pa'];
        $nome           = $campos_grupo_pa[0]['nome'];
    }else {
        $razaosocial    = '';
        $id_grupo_pa    = 0;
        $nome           = '';
    }
//Aqui já se aproveita o busca também o IPI da Class. Fiscal. q é utilizado + abaixo
    $sql = "SELECT cf.`classific_fiscal`, cf.`ipi` 
            FROM `grupos_pas` gpa 
            INNER JOIN `familias` f ON f.`id_familia` = gpa.`id_familia` 
            INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
            WHERE gpa.`id_grupo_pa` = '$id_grupo_pa' ";
    $campos_class_fiscal = bancos::sql($sql);
    if(count($campos_class_fiscal) == 1) {
        if($operacao_custo == 1) {//Revenda
            $ipi_classific_fiscal   = 'S/IPI'; //então é zero de IPI
            $classific_fiscal       = $campos_class_fiscal[0]['classific_fiscal'];
        }else {
            $classific_fiscal       = $campos_class_fiscal[0]['classific_fiscal'];
            $ipi_classific_fiscal   = number_format($campos_class_fiscal[0]['ipi'], 1, ',', '.');
        }
    }else {
        $classific_fiscal = '';
        $ipi_classific_fiscal = 0;
    }
?>
<table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque'>
        <td colspan='7'>
            <font color='#CCCCCC' title='Grupo:<?=$nome;?> - ED:<?=$razaosocial;?>' style='cursor:help'>
                <?=$titulo;?>:
            </font>
            <?
                echo intermodular::pa_discriminacao($vetor_pa_atrelados[$i]);
                //Somente na Família de Macho que estarei fazendo essa adaptação p/ que sejam acertado erros de cadastros ...
                if($id_familia == 9) echo ' - '.$pecas_por_jogo.' pç/jg';
            ?>
            <?
                if($operacao_custo == 0) {//Industrial
            ?>
                <a href="javascript:nova_janela('/erp/albafer/modulo/producao/custo/industrial/custo_industrial.php?id_produto_acabado=<?=$vetor_pa_atrelados[$i];?>&tela=2&pop_up=1', 'DETALHES_CUSTO', '', '', '', '', 500, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Industrial" style="cursor:help" class='link'>
            <?
                }else {
            ?>
                <a href="javascript:nova_janela('/erp/albafer/modulo/producao/custo/revenda/custo_revenda.php?id_produto_acabado=<?=$vetor_pa_atrelados[$i];?>', 'DETALHES_CUSTO', '', '', '', '', 400, 800, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Custo Revenda" style="cursor:help" class='link'>
            <?
                }
            ?>
                    <img src = '../../../imagem/menu/alterar.png' title='Visualizar Custo' alt='Visualizar Custo' border='0'>
                </a>
            &nbsp;-&nbsp;
            <font color='#CCCCCC' title='Classificação Fiscal' style='cursor:help'>
                CF:
            </font>
            <font size='1' color='white'>
                <?=$classific_fiscal;?>
            </font>
        </td>
    </tr>
<?
/******************************************************************************************/
                /****Comentário da Queima****/
                /*A partir do dia 01/08/2014 o Roberto pediu p/ comentar a função 
                de queima, porque a ML Estimada e a Taxa de Estocagem substitui
                essa função ...
                if($campos[0]['qtde_queima_estoque'] > 0) {
                    if($_GET[controle] != 1) {
                        //Através do id_orcamento_venda_item passado por parâmetro, eu busco qualé o Orc ...
                        $sql = "SELECT id_orcamento_venda 
                                FROM `orcamentos_vendas_itens` 
                                WHERE `id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' LIMIT 1 ";
                        $campos_orcamento_venda = bancos::sql($sql);
?>
                    <tr class='linhanormal'>
                        <td colspan='7'>
                            <img src="/erp/albafer/imagem/queima_estoque.png" title="Excesso de Estoque (Todos PAs Atrelados)" alt="Excesso de Estoque (Todos PAs Atrelados)" style='cursor:help' border='0'>
                            <font color='darkblue'>
                                <b>EXCESSO DE ESTOQUE => </b>
                            </font>
                            <?=number_format($campos[0]['qtde_queima_estoque'], 2, ',', '.');?>
                            <font color='darkblue'>
                                <b>&nbsp;/&nbsp;PREÇO DE EXCESSO => </b>
                            </font>
                            <?
                                $preco_venda_para_queima_cond_orc = vendas::calcular_preco_de_queima_pa($vetor_pa_atrelados[$i], $campos_orcamento_venda[0]['id_orcamento_venda']);
                                echo 'R$ '.number_format($preco_venda_para_queima_cond_orc, 2, ',', '.');
                            ?>
                        </td>
                    </tr>
<?
                        }else {
?>
                    <tr class='linhanormal'>
                        <td colspan='7'>
                            <img src="../../../imagem/queima_estoque.png" title="Excesso de Estoque (Todos PAs Atrelados)" alt="Excesso de Estoque (Todos PAs Atrelados)" style='cursor:help' border='0'>
                            <font color='darkblue'>
                                <b>EXCESSO DE ESTOQUE => </b>
                            </font>
                            <?=number_format($campos[0]['qtde_queima_estoque'], 2, ',', '.');?>        
                        </td>
                    </tr>
<?
                        }
                    }*/
/******************************************************************************************/
?>
                    <tr class='linhanormal'>
                        <td>
                            <font face='Verdana, Arial, Helvetica, sans-serif' color='#990000' size='-5'>
                                <b>ED:</b>
                            </font>
                            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
<?
                                $qtde_producao 	= number_format($qtde_producao, 2, ',', '.');
                                if($racionado == 1) {
                                    $type           = 'hidden';
                                    $msg_racionado  = "<font color='red' size='-3'><b>Racionado</b></font>";
                                }else {
                                    $type           = 'text';
                                    $msg_racionado  = '';
                                }
                                echo $msg_racionado;
?>
                                <input type='<?=$type;?>' name='txt_estoque_disponivel' value='<?=number_format($qtde_disponivel, 2, ',', '.');?>' title='Quantidade Disponível' size='10' maxlength='10' class='caixadetexto2' style='color:#000000' disabled>
                            </font>
                        </td>
                        <td>
                            <font face='Verdana, Arial, Helvetica, sans-serif' color='#990000' size='-5'>
                                <b>Entrada Antecipada:</b>
                            </font>
                            <?=number_format($entrada_antecipada, 2, ',', '.');?> 
                            <font color='green'>
                                <b>(Disponível = <?=number_format($qtde_disponivel - $entrada_antecipada, 2, ',', '.');?>)</b>
                            </font>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <font face='Verdana, Arial, Helvetica, sans-serif' color='#990000' size='-5'>
                                <b>ER:</b>
                            </font>
                            <?
                                //Verifico se o Item possui Estoque Excedente, mas somente do que está "Em aberto" ...
                                $sql = "SELECT `qtde` 
                                        FROM `estoques_excedentes` 
                                        WHERE `id_produto_acabado` = '$vetor_pa_atrelados[$i]' 
                                        AND `status` = '0' LIMIT 1 ";
                                $campos_excedente = bancos::sql($sql);
                                if($campos_excedente[0]['qtde'] > 0) {//Se existir Estoque Excedente, exibo um link p/ ver Detalhes
                            ?>
                            <a href = '../../vendas/estoque_acabado/excedente/alterar.php?passo=1&id_produto_acabado=<?=$vetor_pa_atrelados[$i];?>&pop_up=1' class='html5lightbox'>
                            <?
                                }
                                echo number_format($quantidade_estoque, 2, ',', '.');
                                
                                if($qtde_pa_possui_item_faltante > 0) echo '<br/><font color="red" title="Produto Incompleto (Faltando Item)" style="cursor:help"><b>'.$qtde_pa_possui_item_faltante.' F.I</b></font>';
                            ?>
                        </td>
                        <td>
                            <img src = '../../../imagem/carrinho_compras.png' border='0' title='Compra + Produção' alt='Compra + Produção' width='25' height='16' onclick="html5Lightbox.showLightbox(7, '../producao/visualizar_compra_producao.php?id_produto_acabado=<?=$vetor_pa_atrelados[$i];?>')">
                            <?
                                //Aqui eu busco o PI do PA do Loop ...
                                $sql = "SELECT `id_produto_insumo` 
                                        FROM `produtos_acabados` 
                                        WHERE `id_produto_acabado` = '".$vetor_pa_atrelados[$i]."' LIMIT 1 ";
                                $campos_pi = bancos::sql($sql);
                                echo number_format($compra, 2, ',', '.');
                            ?>
                            +
                            <?
                                echo number_format($producao, 2, ',', '.');
                                if($qtde_oe_em_aberto > 0) echo '<br/><font color="purple"><b>(OE='.number_format($qtde_oe_em_aberto, 0, '', '.').')</b></font>';
                            ?>
                        </td>
                        <td>
                            <font face='Verdana, Arial, Helvetica, sans-serif' color='#990000' size='-5'>
                                <b>Pend.:</b>
                            </font>
                            <?
                                echo $msg_racionado;
                                echo number_format($qtde_pendente, 2, ',', '.');
                            ?>
                        </td>
                        <td>
                            <font face='Verdana, Arial, Helvetica, sans-serif' color='#990000' size='-5'>
                                <b>EC:</b>
                            </font>
                            <?
                                $font = ($estoque_comprometido < 0) ? "<font color='red'>" : '';
                                echo $font.number_format($estoque_comprometido, 2, ',', '.');
                            ?>
                        </td>
                        <td>
                            <font face='Verdana, Arial, Helvetica, sans-serif' color='#990000' size='-5'>
                                <b>Prog. >= 30:</b>
                            </font>
                            <?=number_format(estoque_acabado::qtde_programada($vetor_pa_atrelados[$i]), 2, ',', '.');?>
                        </td>
                        <td>
                            <font face='Verdana, Arial, Helvetica, sans-serif' color='#990000' size='-5'>
                                <b>MMV:</b>
                            </font>
                            <?=number_format($campos[0]['mmv'], 2, ',', '.');?>
                        </td>
                    </tr>
                    <tr class='linhanormalescura'>
                        <td>
                            <font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue' size='-5'>
                                <b>Estoque Fornecedor (P. Ent = 3 dias úteis):</b>
                                &nbsp;
                                <?=number_format($qtde_fornecedor, 2, ',', '.');?>
                            </font>
                        </td>
                        <td colspan='7'>
                            <font face='Verdana, Arial, Helvetica, sans-serif' color='darkblue' size='-5'>
                                <b>Estoque Fornecedor Porto (P. Ent = 30 dias úteis):</b>
                                &nbsp;
                                <?=number_format($qtde_porto, 2, ',', '.');?>
                            </font>
                        </td>
                    </tr>
                    <tr class='linhanormal'>
                        <td>
                            <font face='Verdana, Arial, Helvetica, sans-serif' color='#990000' size='-5'>
                                <b>Obs. do Produto:</b>
                            </font>
                        </td>
                        <td colspan='6'>
                            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                                <?=$observacao;?>
                            </font>
                        </td>
                    </tr>
                    <tr class='linhanormal'>
<?
//Quando o PA for ESP, eu busco o lote mínimo
                        if($referencia == 'ESP') {
                            $colspan = 6;
?>
                        <td>
                            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                                <font color='red'>
                                    <b>Lote M&iacute;nimo em R$: </b>
                                </font>
                            </font>
<?
                            $sql = "SELECT gpa.lote_min_producao_reais 
                                    FROM `produtos_acabados` pa 
                                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                                    WHERE pa.`id_produto_acabado` = '".$vetor_pa_atrelados[$i]."' LIMIT 1 ";
                            $campos = bancos::sql($sql);
                            $lote_min_producao_reais = (count($campos) > 0) ? $campos[0]['lote_min_producao_reais'] : 0;
                            echo segurancas::number_format($lote_min_producao_reais, 2, '.');
?>
                            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                                &nbsp;
                            </font>
                        </td>
<?
                        }else {
                            $colspan = 7;
                        }
?>
                        <td colspan='<?=$colspan;?>' align='center'>
                            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                                <a href="javascript:nova_janela('<?=$path_pdf;?>', 'SITE', '', '', '', '', 700, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Clique aqui para Visualizar as Medidas deste Produto' class='link'>
                                    Clique aqui para Visualizar o Cat&aacute;logo deste Produto
                                </a>
                            </font>
                            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                                &nbsp;
                            </font>
                        </td>
                    </tr>
                    <tr class='linhacabecalho' align='center'>
                        <td colspan='7'>
                            Prazo(s) de Entrega do Produto
                        </td>
                    </tr>
<?
/*___________________________________________________________________________________________________________*/
//Só vai exibir esse trecho de Código quando essa tela for acessada pela Tela de Alterar Itens de Orçamento ...
                        if(!empty($id_orcamento_venda_item)) {
                            $sql = "SELECT `prazo_entrega_tecnico` 
                                    FROM `orcamentos_vendas_itens` 
                                    WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
                            $campos_prazo_entrega   = bancos::sql($sql);
                            $prazo_entrega_tecnico  = $campos_prazo_entrega[0]['prazo_entrega_tecnico'];
?>
                    <tr class='linhanormal'>
                        <td colspan='7'>
<?
                            if($referencia == 'ESP') {//Produtos Especiais
?>
                            <b>Sugerido pelo Depto. T&eacute;cnico: </b>
<?
                                if($prazo_entrega_tecnico == '0.0') {
                                    $prazo_entrega_apresentar = '<font color="red"><b>SEM PRAZO</b></font>';
//Aqui é o Prazo de Ent. da Empresa Divisão, e verifica qual é o certo para poder carregar na caixa de texto
/*Existe esse esquema de Int, porque o Campo -> 'prazo_entrega_tecnico' é do Tipo Float, foi feito
esse esquema para não dar problema na hora de Atualizar o Custo*/
                                }else {
                                    $prazo_entrega_apresentar = (int)$prazo_entrega_tecnico;
                                }
                                echo $prazo_entrega_apresentar;
                            }else {//Normais de Linha
?>
                                <b>Sugerido pelo (Grupo): </b>
<?
                                $sql = "SELECT gpa.`prazo_entrega` 
                                        FROM `produtos_acabados` pa 
                                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                                        WHERE pa.`id_produto_acabado` = '".$vetor_pa_atrelados[$i]."' LIMIT 1 ";
                                $campos_prazo_entrega = bancos::sql($sql);
                                echo $campos_prazo_entrega[0]['prazo_entrega'].' Dia(s)';
                            }
?>
                        </td>
                    </tr>
                    <tr class='linhanormal'>
                        <td colspan='7'>
                            <b>Definido pelo Vendedor neste Item de Or&ccedil;amento: </b>
<?
                            $vetor_prazos_entrega   = vendas::prazos_entrega();

                            $sql = "SELECT `prazo_entrega` 
                                    FROM `orcamentos_vendas_itens` 
                                    WHERE `id_orcamento_venda_item` = '$id_orcamento_venda_item' LIMIT 1 ";
                            $campos_prazo_entrega   = bancos::sql($sql);

                            foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
                                //Compara o valor do Banco com o valor do Vetor ...
                                if($campos_prazo_entrega[0]['prazo_entrega'] == $indice) echo $prazo_entrega;
                            }
?>
                        </td>
                    </tr>
<?
                        }
/*___________________________________________________________________________________________________________*/
//Vai exibir esse trecho de código em qualquer tela, menos na Tela de Orçamento de Vendas
//Verifica se o PA tem prazo de Entrega
                        $sql = "SELECT `prazo_entrega` 
                                FROM `estoques_acabados` 
                                WHERE `id_produto_acabado` = '".$vetor_pa_atrelados[$i]."' LIMIT 1 ";
                        $campos_prazo_entrega = bancos::sql($sql);
                        if(count($campos_prazo_entrega) == 0) {
                            $string_apresentar = '&nbsp;';
                            $prazo_entrega = '';
                        }else {
                            $prazo_entrega = strtok($campos_prazo_entrega[0]['prazo_entrega'], '=');
//Se não encontrar o Prazo de Entrega, ele dá um tratamento nesse String para ficar melhor na apresentação da tela ...
                            if($prazo_entrega == '' || $prazo_entrega == ' ') {
                                $prazo_entrega = 'PRAZO INDEFINIDO';
                            }else {
                                $responsavel = stristr(strtok($campos_prazo_entrega[0]['prazo_entrega'], '|'), '=>');
                                $responsavel = substr($responsavel, 3, strlen($responsavel));
                                $data_hora = data::datetodata(substr(stristr($campos_prazo_entrega[0]['prazo_entrega'], '|'),2,10), '/').' às '.substr(stristr($campos2[0]['prazo_entrega'], '|'), 13, 8);
                            }
//Faz esse tratamento para o caso de não encontrar o responsável ...
                            if(empty($responsavel)) {
                                $string_apresentar = '&nbsp;';
                            }else {
                                $string_apresentar = ' - <b>Responsável pela Atualização:</b> '.$responsavel.' - '.$data_hora;
                            }
                        }
                        
                        if($operacao_custo == 0) {//Industrial
                            $prazo_entrega_pa_revenda = '';
                        }else {//Revenda
                            $prazo_entrega_pa_revenda = $prazo_entrega.$string_apresentar;
                        }
?>
                    <tr class='linhanormal'>
                        <td colspan='2'>
                            <b>Produto Industrial:</b>
                            <?
/*Faço uma verificação de Toda(s) as OP(s) que estão em aberto e que ainda não foram excluídas 
do Sistema referente ao PA atrelado ...*/
                                $sql = "SELECT `id_op` 
                                        FROM `ops` 
                                        WHERE `id_produto_acabado` = '".$vetor_pa_atrelados[$i]."' 
                                        AND `status_finalizar` = '0' 
                                        AND `ativo` = '1' 
                                        ORDER BY `prazo_entrega` ";
                                $campos_op = bancos::sql($sql);
                                $linhas_op = count($campos_op);
                                if($linhas_op == 0) {
                                    echo '<font color="red"><b>S/ OP!</b></font>';
                                }else {
                                    echo "<font color='blue'><b>".$linhas_op." OP(s)</b></font>";
                                }
                            ?>
                        </td>
                        <td colspan='2'>
                            <b>Produto Revenda:</b>
                            <?=$prazo_entrega_pa_revenda;?>
                        </td>
                        <td colspan='3'>
                            <b>Sugerido (Grupo):</b>
                            <?
                                $sql = "SELECT gpa.`prazo_entrega` 
                                        FROM `produtos_acabados` pa 
                                        INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                                        WHERE pa.`id_produto_acabado` = '".$vetor_pa_atrelados[$i]."' ";
                                $campos_pe = bancos::sql($sql);
                                echo $campos_pe[0]['prazo_entrega'].' Dia(s)';
                            ?>
                        </td>
                    </tr>
                </table>
<?
/*___________________________________________________________________________________________________________*/
                if($linhas_op > 0) {//Se tiver pelo menos 1 OP, então ...
?>
                <table width='100%' border='0' cellspacing='1' cellpadding='1' bgcolor='black' align='center'>
                    <tr class='linhadestaque' align='center'>
                        <td colspan='2'>
                            N.&#176 OP
                        </td>
                        <td>
                            Data de Emiss&atilde;o
                        </td>
                        <td>
                            Qtde Ini
                        </td>
                        <td>
                            Qtde Ent
                        </td>
                        <td>
                            Qtde Saldo
                        </td>
                        <td>
                            <a href="javascript:nova_janela('alterar_prazo_entrega_industrial.php?id_produto_acabado=<?=$id_produto_acabado_estoque;?>&operacao_custo=0', 'PRAZO_ENTREGA', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Prazo de Entrega' class='link'>
                                <font color='#CCCCCC' size='-1'>
                                    <b>Prazo Entrega OP</b>
                                </font>
                            </a>
                        </td>
                    </tr>
<?
                    for($j = 0; $j < $linhas_op; $j++) {
                        $url = "javascript:nova_janela('".$nivel_arquivo."producao/ops/alterar.php?passo=1&id_op=".$campos_op[$j]['id_op']."&pop_up=1', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')";
                        $vetor_dados_op = intermodular::dados_op($campos_op[$j]['id_op']);
?>
                    <tr class='linhanormal' align='center'>
                        <td onclick="<?=$url;?>" width='10'>
                            <img src='<?=$nivel_imagem;?>imagem/seta_direita.gif' width='12' height='12' border='0'>
                        </td>
                        <td onclick="<?=$url;?>" align='center'>
                            <a href='#' title='Detalhes de OP' alt='Detalhes de OP' class='link'>
                                <?=$campos_op[$j]['id_op'].$vetor_dados_op['posicao_op'];?>
                            </a>
                        </td>
                        <td>
                            <?=$vetor_dados_op['data_emissao'];?>
                        </td>
                        <td>
                            <?=$vetor_dados_op['qtde_produzir'];?>
                        </td>
                        <td>
                            <?=$vetor_dados_op['total_baixa_op_para_pa'];?>
                        </td>
                        <td>
                            <?=$vetor_dados_op['qtde_saldo'];?>
                        </td>
                        <td>
                            <?
                                $cor_link = (substr($vetor_dados_op['data_ocorrencia'], 0, 10) >= $data_atual_menos_sete) ? 'green' : '';
                            ?>
                            <a href='' class='link'>
                                <font color='<?=$cor_link;?>' size='-2'>
                                    <?=$vetor_dados_op['prazo_entrega'].'-'.$vetor_dados_op['situacao'];?>
                                </font>
                            </a>
                        </td>
                    </tr>
<?
                    }
?>
                </table>
<?
                }
}
?>
            </fieldset>
        </td>
    </tr>
</table>
<?
/******************************************************************************************************************/
/******************************************************Resumo******************************************************/
/******************************************************************************************************************/
//Depois de ter apresentado o último PA, apresento essa linha a mais que seria um resumo geral de tudo ...
?>
<table width='100%' border='5' bgcolor='red' cellspacing='1' cellpadding='1' align='center'>
<?
    if(!empty($bloqueio)) {
?>
        <tr class='linhanormal' align='center'>
            <td colspan='7'>
                <?=$bloqueio;?>
            </td>
        </tr>
<?
    }
                    
    if($id_familia == 9) {//Família de Machos o raciocínio é um pouquinho diferente ...
?>
    <tr class='linhadestaque'>
        <td align='center'>
            <font color='#CCCCCC'>
                BLANK
            </font>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total de Estoque Disponível'>
                Total ED:
            </font>
            <?=number_format($total_qtde_disponivel_blank, 2, ',', '.');?>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total de Estoque Real'>
                Total ER:
            </font>
            <?=number_format($total_quantidade_estoque_blank, 2, ',', '.');?>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total de Compra / Produ&ccedil;&atilde;o'>
                Total Comp / Prod:
            </font>
            <?=number_format($total_compra_producao_blank, 2, ',', '.');?>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total de Estoque Comprometido'>
                Total EC.:
            </font>
            <?
                if($total_estoque_comprometido_blank < 0) {
                    echo "<font color='red'>".number_format($total_estoque_comprometido_blank, 2, ',', '.')."</font>";
                }else {
                    echo number_format($total_estoque_comprometido_blank, 2, ',', '.');
                }
            ?>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total Programado dos PAs Atrelados'>
                Total Prog.:
                <font color='#FFFFFF'>
                <?
                    $calculo_programado_pas_atrelados   = intermodular::calculo_programado_pas_atrelados($id_produto_acabado);
                    $font_programado_atrelado           = ($calculo_programado_pas_atrelados['total_programado_pas_atrelados'] < 0) ? 'red' : '';
                    echo '<font color="'.$font_programado_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help">'.number_format($calculo_programado_pas_atrelados['total_programado_pas_atrelados'], 2, ',', '.').'</font>';
                ?>
                </font>
            </font>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total de MMV'>
                Total MMV:
            </font>
            <?=number_format($total_mmv_blank, 2, ',', '.');?>
        </td>
        <td colspan='2'>
            <font color='#CCCCCC' style='cursor:help' title='Total de Estoque Comprometido'>
                Est. p/ 
                <font color='#FFFFFF'>
                <?
                    //Se não existir MMV, não faz pq dá derro de Divisão por Zero ...
                    if($total_mmv_blank == 0) {
                        echo '<b>S/ MMV</b>';
                    }else {
                        if($total_estoque_comprometido_blank / $total_mmv_blank < 0) {
                            echo '0';
                        }else {
                            echo number_format($total_estoque_comprometido_blank / $total_mmv_blank, 1, ',', '.');
                        }
                    }
                ?> 
                </font>
                meses: 
            </font>
        </td>
    </tr>
<?
    }else {//Outras Famílias ...
        //Listo os valores nas variáveis "arrays" referentes ao valor do Total do P.A. só que por Unidade ...
        for($j = 0; $j < count($vetor_unidade); $j++) {
?>
    <tr class='linhadestaque'>
        <td align='center'>
            <font color='#CCCCCC'>
                <?=$vetor_unidade[$j];?>
            </font>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total de Estoque Disponível'>
                Total ED:
            </font>
            <?=number_format($total_qtde_disponivel[$vetor_unidade[$j]], 2, ',', '.');?>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total de Estoque Real'>
                Total ER:
            </font>
            <?=number_format($total_quantidade_estoque[$vetor_unidade[$j]], 2, ',', '.');?>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total de Compra / Produ&ccedil;&atilde;o'>
                Total Comp / Prod:
            </font>
            <?=number_format($total_compra_producao[$vetor_unidade[$j]], 2, ',', '.');?>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total de Estoque Comprometido'>
                Total EC.:
            </font>
            <?
                if($total_estoque_comprometido[$vetor_unidade[$j]] < 0) {
                    echo "<font color='#990000'>".number_format($total_estoque_comprometido[$vetor_unidade[$j]], 2, ',', '.')."</font>";
                }else {
                    echo number_format($total_estoque_comprometido[$vetor_unidade[$j]], 2, ',', '.');
                }
            ?>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total Programado dos PAs Atrelados'>
                Total Prog.:
                <font color='#FFFFFF'>
                <?
                    $calculo_programado_pas_atrelados   = intermodular::calculo_programado_pas_atrelados($id_produto_acabado);
                    $font_programado_atrelado           = ($calculo_programado_pas_atrelados['total_programado_pas_atrelados'] < 0) ? 'red' : '';
                    echo '<font color="'.$font_programado_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help">'.number_format($calculo_programado_pas_atrelados['total_programado_pas_atrelados'], 2, ',', '.').'</font>';
                ?>
                </font>
            </font>
        </td>
        <td>
            <font color='#CCCCCC' style='cursor:help' title='Total de MMV'>
                Total MMV:
            </font>
            <?=number_format($total_mmv[$vetor_unidade[$j]], 2, ',', '.');?>
        </td>
        <td colspan='2'>
            <font color='#CCCCCC' style='cursor:help' title='Total de Estoque Comprometido'>
                Est. p/ 
                <font color='#FFFFFF'>
                <?
                    //Se não existir MMV, não faz pq dá derro de Divisão por Zero ...
                    if($total_mmv[$vetor_unidade[$j]] == 0) {
                        echo '<b>S/ MMV</b>';
                    }else {
                        if($total_estoque_comprometido[$vetor_unidade[$j]] / $total_mmv[$vetor_unidade[$j]] < 0) {
                            echo '0';
                        }else {
                            echo number_format($total_estoque_comprometido[$vetor_unidade[$j]] / $total_mmv[$vetor_unidade[$j]], 1, ',', '.');
                        }
                    }
                ?> 
                </font>
                meses: 
            </font>
        </td>
    </tr>
<?                        
        }
    }
?>
</table>
</body>
</html>