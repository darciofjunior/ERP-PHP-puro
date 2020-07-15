<?
require('../../../lib/segurancas.php');
require('../../../lib/estoque_acabado.php');
segurancas::geral('/erp/albafer/modulo/vendas/estoque_acabado/consultar.php', '../../../');

if($passo == 1) {
?>
<html>
<head>
<title>.:: Impressão de Pendência(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<form name='form' method='post' action=''>
<!--********************Controles de Tela********************-->
<input type='hidden' name='opt_opcao' value='<?=$_POST['opt_opcao'];?>'>
<input type='hidden' name='hdd_produto_acabado' value='<?=$_POST['hdd_produto_acabado'];?>'>
<input type='hidden' name='txt_qtde' value='<?=$_POST['txt_qtde'];?>'>
<!--*********************************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
    $dados = explode(';', $_POST['hdd_produto_acabado']);
    
    for($i = 0; $i < count($dados); $i++) {
        $contador = 0;
        $id_produto_acabado = ''; $urgencia = ''; $compra_producao = ''; $estoque_comprometido = '';
        $estoque_programado = ''; $mmv_corrigido = ''; $tipo_categoria = '';

        //Aqui eu vasculho cada caractér do Item da Lista ...
        for($j = 0; $j < strlen($dados[$i]); $j++) {
            if(substr($dados[$i], $j, 1) == '|') {
                $contador++;
            }else {
                if($contador == 0) {
                    $id_produto_acabado.= substr($dados[$i], $j, 1);
                }else if($contador == 1) {
                    $urgencia.= substr($dados[$i], $j, 1);
                }else if($contador == 2) {
                    $compra_producao.= substr($dados[$i], $j, 1);
                }else if($contador == 3) {
                    $estoque_comprometido.= substr($dados[$i], $j, 1);
                }else if($contador == 4) {
                    $estoque_programado.= substr($dados[$i], $j, 1);
                }else if($contador == 5) {
                    $mmv_corrigido.= substr($dados[$i], $j, 1);
                }else if($contador == 6) {
                    $tipo_categoria.= substr($dados[$i], $j, 1);
                }
            }
        }
        $vetor_produto_acabado[$i]      = $id_produto_acabado;
        $vetor_urgencia[$i]             = $urgencia;
        $vetor_compra_producao[$i]      = $compra_producao;
        $vetor_estoque_comprometido[$i] = $estoque_comprometido;
        $vetor_estoque_programado[$i] 	= $estoque_programado;
        $vetor_mmv_corrigido[$i]        = $mmv_corrigido;
        $vetor_tipo_categoria[$i]       = $tipo_categoria;
    }
    
    $vetor_qtde_compra  = explode(';', $_POST['txt_qtde']);
    $vetor_preco_total  = explode(';', $_POST['txt_preco_total']);
    
    if($_POST['opt_opcao'] == 1) {//Fornecedor ...
        $vetor_fornecedores = array();//Esse vetor de Fornecedores será utilizado mais abaixo ...

        for($i = 0; $i < count($vetor_produto_acabado); $i++) {
            if($vetor_qtde_compra[$i] > 0) {//Só é necessário mostrarmos essa linha quando a Qtde que foi digitada pelo usuário > 0 ...
                //Aqui eu busco todos os Fornecedores possíveis do PA se esse for um PIPA ...
                $sql = "SELECT DISTINCT(p.`id_fornecedor`) AS id_fornecedor 
                        FROM `produtos_acabados` pa 
                        INNER JOIN `itens_pedidos` ip ON ip.`id_produto_insumo` = pa.`id_produto_insumo` AND ip.`status` < '2' 
                        INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`status` = '1' AND ((p.`programado_descontabilizado` = 'S' AND p.`ativo` = '0') OR (p.`programado_descontabilizado` = 'N' AND p.`ativo` = '1')) 
                        WHERE pa.`id_produto_acabado` = '$vetor_produto_acabado[$i]' ";
                $campos_fornecedor = bancos::sql($sql);
                $linhas_fornecedor = count($campos_fornecedor);
                for($j = 0; $j < $linhas_fornecedor; $j++) if(!in_array($campos_fornecedor[$j]['id_fornecedor'], $vetor_fornecedores)) $vetor_fornecedores[] = $campos_fornecedor[$j]['id_fornecedor'];
            }
        }
        
        if(count($vetor_fornecedores) == 0) {//Não encontrou nenhum Fornecedor ...
?>
    <tr class='atencao' align='center'>
        <td>
            NÃO EXISTE(M) COMPRA(S) PRODUÇÃO(ÕES).
        </td>
    </tr>
    <tr class='atencao' align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'impressao_pendencias.php?hdd_produto_acabado[]=<?=$_POST['hdd_produto_acabado'];?>&txt_qtde[]=<?=$_POST['txt_qtde'];?>&txt_preco_total[]=<?=$_POST['txt_preco_total'];?>'" class='botao'>
        </td>
    </tr>
<?
            exit;
        }else {//Encontrou pelo menos 1 Fornecedor ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Impressão de Pendência(s) / Pending Printing
            <?
                /*Quando o checkbox "Mostrar Todos Fornecedores" está marcado então desabilito 
                a combo Fornecedor ...*/
                if(!empty($_POST['chkt_mostrar_todos_fornecedores'])) {
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                }else {//Checkbox desmarcado, então habilito a combo Fornecedor ...
                    $class      = 'combo';
                    $disabled   = '';
                }
            ?>
            <select name='cmb_fornecedor' title='Selecione um Fornecedor' onchange='document.form.submit()' class='<?=$class;?>' <?=$disabled;?>>
            <?
                $sql = "SELECT `id_fornecedor`, `razaosocial` 
                        FROM `fornecedores` 
                        WHERE `id_fornecedor` IN (".implode(',', $vetor_fornecedores).") ORDER BY `razaosocial` ";
                echo combos::combo($sql, $_POST['cmb_fornecedor']);
            ?>
            </select>
        </td>
    </tr>
<?
        }
    }else {//Compra / Produção
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Impressão de Pendência(s) / Pending Printing
        </td>
    </tr>
<?
    }

    if($_POST['opt_opcao'] == 1) {//Fornecedor ...
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Fornecedor
        </td>
        <td>
            Qtde
        </td>
        <td>
            Compra / Order<br/> Produção / Production
        </td>
        <td>
            Ref
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Pço. Total
        </td>
    </tr>
<?
        for($i = 0; $i < count($vetor_produto_acabado); $i++) {
            if($vetor_qtde_compra[$i] > 0) {//Só é necessário mostrarmos essa linha quando a Qtde que foi digitada pelo usuário > 0 ...
                /*Se essa combo for selecionada, então só mostro os PA(s) que são PIPA e todos os itens 
                de Pedido desse PI "PA" que estejam em Pendência < '2' do Fornecedor específico 
                selecionado pelo Usuário ...*/
                if(!empty($_POST['cmb_fornecedor'])) {
                    $inner_join = " INNER JOIN `itens_pedidos` ip ON ip.`id_produto_insumo` = pa.`id_produto_insumo` AND ip.`status` < '2' 
                                    INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`status` = '1' AND ((p.`programado_descontabilizado` = 'S' AND p.`ativo` = '0') OR (p.`programado_descontabilizado` = 'N' AND p.`ativo` = '1')) AND p.`id_fornecedor` = '$_POST[cmb_fornecedor]' ";
                }
                
                /*A princípio mostro Todos os PA(s) independente de o PA ser PIPA e 
                esse PI então estar em Pendência < '2' - Trago Tudo ...*/
                $sql = "SELECT pa.`id_produto_insumo`, pa.`operacao_custo`, pa.`operacao_custo_sub`, 
                        pa.`referencia`, pa.`discriminacao`, pa.`status_top` 
                        FROM `produtos_acabados` pa 
                        $inner_join 
                        WHERE pa.`id_produto_acabado` = '$vetor_produto_acabado[$i]' LIMIT 1 ";
                $campos     = bancos::sql($sql);

                $categoria  = ($vetor_tipo_categoria[$i] == 'Urgentíssimo') ? 'Urgentíssimos (Em Falta) / Most Urgent (Lacking)' : 'Urgentes (Estoque Baixo) / Urgent (Short Supply)';
//Se o Tipo de Categoria Atual for diferente da Anterior printa o Tipo de Categoria Atual que está sendo listado ...
                if($tipo_categoria_anterior != $vetor_tipo_categoria[$i]) {//Na primeira vez é claro que não existe anterior ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <?=$categoria;?>
        </td>
    </tr>
<?
                    $tipo_categoria_anterior = $vetor_tipo_categoria[$i];
                }
                
                /**************************************************************/
                if(count($campos) == 1) {//Retornou um Registro ...
                    /*A cada loop iniciado sempre crio um "Vetor Fornecedor" garantindo que essa variável 
                    nunca fique com resíduos ...*/
                    $vetor_fornecedores = array();
                    
                    /*Se esse PA do Loop for um PIPA, busco todos os itens de Pedido desse PI que estejam 
                    em Pendência < '2' ...*/
                    $sql = "SELECT p.`id_fornecedor`, ip.`id_item_pedido`, ip.`id_produto_insumo`, ip.`qtde` 
                            FROM `produtos_acabados` pa 
                            INNER JOIN `itens_pedidos` ip ON ip.`id_produto_insumo` = pa.`id_produto_insumo` AND ip.`status` < '2' 
                            INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`status` = '1' AND ((p.`programado_descontabilizado` = 'S' AND p.`ativo` = '0') OR (p.`programado_descontabilizado` = 'N' AND p.`ativo` = '1')) $condicao_fornecedor 
                            WHERE pa.`id_produto_acabado` = '$vetor_produto_acabado[$i]' ";
                    $campos_itens_pedido = bancos::sql($sql);
                    $linhas_itens_pedido = count($campos_itens_pedido);
                    for($j = 0; $j < $linhas_itens_pedido; $j++) {
                        if(!in_array($campos_itens_pedido[$j]['id_fornecedor'], $vetor_fornecedores)) $vetor_fornecedores[] = $campos_itens_pedido[$j]['id_fornecedor'];
                        
                        //Busca o Total entregue do Item do Pedido do Loop em diversas NF(s) ...
                        $sql = "SELECT SUM(`qtde_entregue`) AS total_entregue 
                                FROM `nfe_historicos` 
                                WHERE `id_item_pedido` = '".$campos_itens_pedido[$j]['id_item_pedido']."' ";
                        $campos_entregue = bancos::sql($sql);
                        
                        /*Abato da "Qtde Pedida", a "Qtde que foi entregue" em Nota Fiscal ...

                        O somatório de tudo encontrado "Qtde Pedida - a Qtde que foi entregue em Nota Fiscal" 
                        de todos os Loops é feito em cima do $vetor_total_restante "PA vs Fornecedor" ...*/
                        $vetor_total_restante[$vetor_produto_acabado[$i]][$campos_itens_pedido[$j]['id_fornecedor']]+= ($campos_itens_pedido[$j]['qtde'] - $campos_entregue[0]['total_entregue']);
                    }
                    
                    if(count($vetor_fornecedores) == 0) {//Não encontrou nenhum Fornecedor na respectiva linha ...
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            &nbsp;
        </td>
        <td>
            <?=$vetor_qtde_compra[$i];?>
        </td>
        <td>
            &nbsp;
        </td>
        <td>
            <?=$campos[0]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[0]['discriminacao'];?>
        </td>
        <td align='right'>
            <?//=number_format($vetor_preco_total[$i], 2, ',', '.');?>0,00
        </td>
    </tr>    
<?
                    }else {//Encontrou pelo menos 1 Fornecedor ...
                        //Aqui é feita uma apresentação do PA do Loop PIPA vs Fornecedor ...
                        for($j = 0; $j < count($vetor_fornecedores); $j++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
        <?
            //Busco a Razão Social do $vetor_fornecedores[$j] do Loop ...
            $sql = "SELECT `razaosocial` 
                    FROM `fornecedores` 
                    WHERE `id_fornecedor` = '$vetor_fornecedores[$j]' LIMIT 1 ";
            $campos_fornecedor = bancos::sql($sql);
            echo $campos_fornecedor[0]['razaosocial'];
        ?>
        </td>
        <td>
            <?=$vetor_qtde_compra[$i];?>
        </td>
        <td>
            <?=number_format($vetor_total_restante[$vetor_produto_acabado[$i]][$vetor_fornecedores[$j]], 2, ',', '.');?>
        </td>
        <td>
            <?=$campos[0]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[0]['discriminacao'];?>
        </td>
        <td align='right'>
            <?//=number_format($vetor_preco_total[$i], 2, ',', '.');?>0,00
        </td>
    </tr>
<?
                        }
                    }
                }
                /**************************************************************/
            }
        }
    }else if($_POST['opt_opcao'] == 2) {//Compra / Produção
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde
        </td>
        <td>
            Qtde<br/>Urg. Sug.
        </td>
        <td>
            <font title='Operação de Custo' style='cursor:help'>
                O.C.
            </font>
        </td>
        <td>
            Compra<br/> Produção
        </td>
        <td>
            <font title='Estoque Comprometido Total' style='cursor:help'>
                E.C.<br/>Total
            </font>
        </td>
        <td>
            Prog.<br/>Total
        </td>
        <td>
            <font title='MMV Corrigido' style='cursor:help'>
                MMV<br/>Cor.
            </font>
        </td>
        <td>
            Ref
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Pço. Total
        </td>
    </tr>
<?
        for($i = 0; $i < count($vetor_produto_acabado); $i++) {
            if($vetor_qtde_compra[$i] > 0) {//Só é necessário mostrarmos essa linha quando a Qtde que foi digitada pelo usuário > 0 ...
                //Faço a busca da Referência e Discriminação do PA corrente ...
                $sql = "SELECT operacao_custo, operacao_custo_sub, referencia, discriminacao, status_top 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$vetor_produto_acabado[$i]' LIMIT 1 ";
                $campos     = bancos::sql($sql);
                $categoria  = ($vetor_tipo_categoria[$i] == 'Urgentíssimo') ? 'Urgentíssimos (Em Falta) / Most Urgent (Lacking)' : 'Urgentes (Estoque Baixo) / Urgent (Short Supply)';
    //Se o Tipo de Categoria Atual for diferente da Anterior printa o Tipo de Categoria Atual que está sendo listado ...
                if($tipo_categoria_anterior != $vetor_tipo_categoria[$i]) {//Na primeira vez é claro que não existe anterior ...
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <?=$categoria;?>
        </td>
    </tr>
<?
                    $tipo_categoria_anterior = $vetor_tipo_categoria[$i];
                }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$vetor_qtde_compra[$i];?>
        </td>
        <td>
            <?=$vetor_urgencia[$i];?>
        </td>
        <td>
        <?
            if($campos[0]['status_top'] == 1) {
                echo  "<font color='red' style='cursor:help;' title='1º 50% dos PA´s TOP'>TopA</font> - ";
            }else if($campos[0]['status_top'] == 2) {
                echo  "<font color='red' style='cursor:help;' title='2º 50% dos PA´s TOP'>TopB</font> - ";
            }
            if($campos[0]['operacao_custo'] == 0) {
                echo 'I';
//Se a Operação de Custo for Industrial, então eu apresento a Sub-Operação de Custo do PA ...
                if($campos[0]['operacao_custo_sub'] == 0) {
                    echo '-I';
                }else if($campos[0]['operacao_custo_sub'] == 1) {
                    echo '-R';
                }else {
                    echo '-';
                }
            }else if($campos[0]['operacao_custo'] == 1) {
                echo 'R';
            }else {
                echo '-';
            }
        ?>
        </td>
        <td>
        <?
            echo $vetor_compra_producao[$i];
            $estoque_produto = estoque_acabado::qtde_estoque($vetor_produto_acabado[$i], 0);
            if($estoque_produto[11] > 0) echo '<br/><font color="purple"><b>(OE='.number_format($estoque_produto[11], 0, '', '.').')</b></font>';
        ?>
        </td>
        <td>
            <?=$vetor_estoque_comprometido[$i];?>
        </td>
        <td>
            <?=$vetor_estoque_programado[$i];?>
        </td>
        <td>
            <?=number_format($vetor_mmv_corrigido[$i], 1, ',', '.');?>
        </td>
        <td>
            <?=$campos[0]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[0]['discriminacao'];?>
        </td>
        <td align='right'>
            <?//=number_format($vetor_preco_total[$i], 2, ',', '.');?>0,00
        </td>
    </tr>
<?
            }
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else {
//Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $hdd_produto_acabado    = $_POST['hdd_produto_acabado'];
        $txt_qtde               = $_POST['txt_qtde'];
        $txt_preco_total        = $_POST['txt_preco_total'];
    }else {
        $hdd_produto_acabado    = $_GET['hdd_produto_acabado'];
        $txt_qtde               = $_GET['txt_qtde'];
        $txt_preco_total        = $_GET['txt_preco_total'];
    }
?>
<html>
<head>
<title>.:: Impressão de Pendência(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<!--************Controle de Tela************-->
<input type='hidden' name='hdd_produto_acabado' value='<?=implode(';', $hdd_produto_acabado);?>'>
<input type='hidden' name='txt_qtde' value='<?=implode(';', $txt_qtde);?>'>
<input type='hidden' name='txt_preco_total' value='<?=implode(';', $txt_preco_total);?>'>
<!--****************************************-->
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Impressão de Pendência(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' id='label' checked>
            <label for='label'>
                Fornecedor
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' id='label2'>
            <label for='label2'>
                Compra / Produção
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='submit' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='document.form.submit()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>