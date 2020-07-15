<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_new.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>DADO BAIXA COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT g.`referencia`, g.`nome`, pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`id_grupo` <> '9' AND g.`referencia` LIKE '%$txt_consultar%' 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
        case 2:
            $sql = "SELECT g.`referencia`, g.`nome`, pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`id_grupo` <> '9' 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
        case 3:
            $sql = "SELECT g.`referencia`, g.`nome`, pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`id_grupo` <> '9' 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`observacao` LIKE '%$txt_consultar%' 
                    AND pi.`ativo` = '1' ORDER BY pi.discriminacao ";
        break;
        case 4:
            /******************************1ª Etapa******************************/
            /*Busca os P.A(s) que estão atrelados ao custo da Primeira Etapa através desse Produto Insumo
            e claro a P.A(s) que estejam vinculados a OP(s) mais que estejam em aberto*/
            $sql = "SELECT DISTINCT(ppe.id_produto_insumo) 
                    FROM `pas_vs_pis_embs` ppe 
                    INNER JOIN ops ON ops.`id_produto_acabado` = ppe.`id_produto_acabado` AND ops.`status_finalizar` = '0' AND ops.`ativo` = '1' AND ops.`id_op` = '$txt_consultar' ";
            $campos1 = bancos::sql($sql);
            $linhas1 = count($campos1);
            for($i = 0; $i < $linhas1; $i++) $ids_produtos_insumos.= $campos1[$i]['id_produto_insumo'].', ';

            /******************************2ª Etapa******************************/
            /*Busca os P.A(s) que estão atrelados ao custo da Segunda Etapa através desse Produto Insumo e claro 
            a P.A(s) que estejam vinculados a OP(s) mais que estejam em aberto*/
            $sql = "SELECT DISTINCT(pac.`id_produto_insumo`) 
                    FROM `produtos_acabados_custos` pac 
                    INNER JOIN `ops` ON ops.`id_produto_acabado` = pac.`id_produto_acabado` AND ops.`status_finalizar` = '0' AND ops.`ativo` = '1' AND ops.`id_op` = '$txt_consultar' 
                    WHERE pac.`operacao_custo` = '0' 
                    AND pac.`id_produto_insumo` IS NOT NULL ";
            $campos2 = bancos::sql($sql);
            $linhas2 = count($campos2);
            for($i = 0; $i < $linhas2; $i++) $ids_produtos_insumos.= $campos2[$i]['id_produto_insumo'].', ';

            /******************************3ª Etapa******************************/
            /*Busca os P.A(s) que estão atrelados ao custo da Terceira Etapa através desse Produto Insumo
            e claro a P.A(s) que estejam vinculados a OP(s) mais que estejam em aberto*/
            $sql = "SELECT DISTINCT(pp.`id_produto_insumo`) 
                    FROM `pacs_vs_pis` pp 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                    INNER JOIN ops ON ops.`id_produto_acabado` = pac.`id_produto_acabado` AND ops.`status_finalizar` = '0' AND ops.`ativo` = '1' AND ops.`id_op` = '$txt_consultar' 
                    WHERE pac.`operacao_custo` = '0' ";
            $campos3 = bancos::sql($sql);
            $linhas3 = count($campos3);
            for($i = 0; $i < $linhas3; $i++) $ids_produtos_insumos.= $campos3[$i]['id_produto_insumo'].', ';

            if(isset($ids_produtos_insumos)) {
                $ids_produtos_insumos = substr($ids_produtos_insumos, 0, strlen($ids_produtos_insumos) - 2);
            }else {
                $ids_produtos_insumos = 0;//P/ não dar pau no SQL ...
            }

            //Aqui eu trago todos os PI´s coletados das Etapas anteriores do Custo ...
            $sql = "SELECT g.`referencia`, g.`nome`, pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`id_grupo` <> '9' 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`ativo` = '1' 
                    AND pi.`id_produto_insumo` IN ($ids_produtos_insumos) ORDER BY pi.`discriminacao` ";
        break;            
        default:
            $sql = "SELECT g.`referencia`, g.`nome`, pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`id_grupo` <> '9' 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                    WHERE pi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Dar Baixa no Estoque ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Dar Baixa no Estoque
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Grupo
        </td>
        <td>
            Referência
        </td>
        <td>
            Unidade
        </td>
        <td>
            Discriminação
        </td>
        <td>
            Qtde<br>Estoque
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
            if($campos[$i]['estocagem'] == 'S') {//Estocável ...
                $url = "incluir.php?passo=2&id_produto_insumo=".$campos[$i]['id_produto_insumo']."&inicio=".$inicio."&pagina=".$pagina."&txt_consultar=".$txt_consultar."&opt_opcao=".$opt_opcao;
            }else {//Não Estocável ...
                $url = "javascript:alert('ESTE PRODUTO NÃO PODE SER MANIPULADO, DEVIDO A SER NÃO ESTOCÁVEL !')";
            }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href="<?=$url;?>" class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='left'>
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
        <td align='right'>
        <?
//Busca da Qtde em Estoque do Produto Insumo atual ...
            $sql = "SELECT `qtde` 
                    FROM `estoques_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
            $campos_estoque = bancos::sql($sql);
            if(count($campos_estoque) == 1 && $campos_estoque[0]['qtde'] > 0) echo number_format($campos_estoque[0]['qtde'], 2, ',', '.');
        ?>
        </td>
    </tr>
<?
            }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
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
?>
<html>
<head>
<title>.:: Dar Baixa no Estoque ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href='../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src='../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 4; i++) document.form.opt_opcao2[i].disabled = true
        document.form.txt_consultar2.disabled   = true
        document.form.txt_consultar2.value      = ''
    }else {
        for(i = 0; i < 4; i++) document.form.opt_opcao2[i].disabled = false
        document.form.txt_consultar2.disabled   = false
        document.form.txt_consultar2.value      = ''
        document.form.txt_consultar2.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar2.disabled == false) {
        if(document.form.txt_consultar2.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar2.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar2.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='id_produto_insumo' value='<?=$_GET['id_produto_insumo'];?>'>
<input type='hidden' name='passo' value='3'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Funcionário (Solicitador)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' title='Consultar Funcionário' name='txt_consultar2' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name="opt_opcao2" value="1" title="Consultar Funcionário por: Nome" onclick="document.form.txt_consultar2.focus()" id='label' checked>
            <label for="label">Nome</label>
        </td>
        <td width='20%'>
            <input type='radio' name="opt_opcao2" value="2" title="Consultar Funcionário por: Empresa" onclick="document.form.txt_consultar2.focus()" id='label2'>
            <label for="label2">Empresa</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name="opt_opcao2" value="3" title="Consultar Funcionário por: Cargo" onclick="document.form.txt_consultar2.focus()" id='label3'>
            <label for="label3">Cargo</label>
        </td>
        <td width='20%'>
            <input type='radio' name="opt_opcao2" value="4" title="Consultar Funcionário por: Departamento" onclick="document.form.txt_consultar2.focus()" id='label4'>
            <label for="label4">Departamento</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan="2">
            <input type='checkbox' name='opcao' title="Consultar todos os funcionários" onclick='limpar()' value='5' class="checkbox" id='label5'>
            <label for="label5">Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    //Tratamento com a variável que veio por parâmetro ...
    $id_produto_insumo = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_insumo'] : $_GET['id_produto_insumo'];
    
    switch($opt_opcao2) {
            case 1:
                $sql = "SELECT f.`id_funcionario`, f.`nome`, e.`nomefantasia`, c.`cargo`, d.`departamento` 
                        FROM `funcionarios` f 
                        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                        INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                        INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                        WHERE f.`nome` LIKE '%$txt_consultar2%' 
                        AND f.`status` < '3' ORDER BY f.`nome` ";
            break;
            case 2:
                $sql = "SELECT f.`id_funcionario`, f.`nome`, e.`nomefantasia`, c.`cargo`, d.`departamento` 
                        FROM `funcionarios` f 
                        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` AND e.`nomefantasia` LIKE '%$txt_consultar2%' 
                        INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                        INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                        WHERE f.`status` < '3' ORDER BY f.`nome` ";
            break;
            case 3:
                $sql = "SELECT f.`id_funcionario`, f.`nome`, e.`nomefantasia`, c.`cargo`, d.`departamento` 
                        FROM `funcionarios` f 
                        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                        INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                        INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` AND c.`cargo` LIKE '%$txt_consultar2%' 
                        WHERE f.`status` < '3' ORDER BY f.`nome` ";
            break;
            case 4:
                $sql = "SELECT f.`id_funcionario`, f.`nome`, e.`nomefantasia`, c.`cargo`, d.`departamento` 
                        FROM `funcionarios` f 
                        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                        INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` AND d.`departamento` LIKE '%$txt_consultar2%' 
                        INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                        WHERE f.`status` < '3' ORDER BY f.`nome` ";
            break;
            default:
                $sql = "SELECT f.`id_funcionario`, f.`nome`, e.`nomefantasia`, c.`cargo`, d.`departamento` 
                        FROM `funcionarios` f 
                        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                        INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                        INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                        WHERE f.`status` < '3' ORDER BY f.`nome` ";
            break;
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location= 'incluir.php?passo=2&id_produto_insumo=<?=$id_produto_insumo;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Dar Baixa no Estoque ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' src = '../../../../js/tabela.js'></Script>
</head>
<body onload="document.getElementById('lnk_funcionario0').focus()">
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Consultar Funcionário(s) - (Solicitador)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Nome
        </td>
        <td>
            Cargo
        </td>
        <td>
            Depto.
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'incluir.php?passo=4&id_produto_insumo='.$id_produto_insumo.'&id_funcionario_solicitador='.$campos[$i]['id_funcionario'].'&inicio='.$inicio.'&pagina='.$pagina.'&txt_consultar2='.$txt_consultar2.'&opt_opcao2='.$opt_opcao2;
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick = "window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <a href='<?=$url;?>' title='Visualizar Detalhes de <?=$campos[$i]['nome'];?>' id='lnk_funcionario<?=$i;?>' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
            <?=$campos[$i]['departamento'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_consultar' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php?passo=2&id_produto_insumo=<?=$id_produto_insumo;?>'" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
    }
}else if($passo == 4) {
    /*********************************************************************************************************************/
    if(empty($id_produto_insumo)) {//O sistema infelizmente às vezes cai, perdendo algum parâmetro tem que ser refeito ...
        exit('O SISTEMA PERDEU O PARÂMETRO DO PRODUTO INSUMO !!! POR FAVOR REFAÇA O FILTRO, ESSE CÓDIGO PRECISA SER REFEITO ');
    }
    /*********************************************************************************************************************/
    $valor_variavel = number_format(genericas::variavel(17), 2, ',', '.');

    $sql = "SELECT pi.id_produto_insumo, pi.unidade_conversao, pi.discriminacao, pi.durabilidade_minima, pi.qtde_estoque_pi, pi.observacao, u.sigla 
            FROM `produtos_insumos` pi 
            INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
            WHERE pi.id_produto_insumo = '$id_produto_insumo' LIMIT 1 ";
    $campos                     = bancos::sql($sql);
    $id_produto_insumo          = $campos[0]['id_produto_insumo'];
    $unidade_conversao          = $campos[0]['unidade_conversao'];
    $discriminacao              = $campos[0]['discriminacao'];
    $durabilidade_minima        = $campos[0]['durabilidade_minima'];
    $qtde_pi_usado              = number_format($campos[0]['qtde_estoque_pi'], 2, ',', '.');
    $observacao                 = $campos[0]['observacao'];
    $sigla                      = $campos[0]['sigla'];
//Seleção da Data da Última retirada do funcionário no estoque tabela baixas_manipulacoes ...
    $sql = "SELECT data_sys 
            FROM `baixas_manipulacoes` 
            WHERE `id_produto_insumo` = '$id_produto_insumo' 
            AND `id_funcionario_retirado` = '$id_funcionario_solicitador' 
            AND `acao` = 'B' ORDER BY id_baixa_manipulacao DESC LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {
            $data_retirada_usa = substr($campos[0]['data_sys'], 0, 10);
            $data_retirada = data::datetodata($data_retirada_usa, '/');
            $data_retirada = $data_retirada.' - '.substr($campos[0]['data_sys'], 11, 8);
    }else {
            $data_retirada = '&nbsp;';
    }
//Busca a Qtde em Estoque do Produto Insumo ...
    $sql = "SELECT qtde as qtde_estoque 
            FROM `estoques_insumos` 
            WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $qtde_estoque = (count($campos) == 0) ? 0 : number_format($campos[0]['qtde_estoque'], 2, ',', '.');
//Busco a Densidade do Aço, p/ auxiliar em alguns cálculos de PHP e JavaScript também ...
    $sql = "SELECT densidade_aco 
            FROM `produtos_insumos_vs_acos` 
            WHERE `id_produto_insumo` = '$id_produto_insumo' ";
    $campos3 = bancos::sql($sql);
    if(count($campos3) > 0) $densidade_aco = $campos3[0]['densidade_aco'];
//Busca o nome da pessoa q solicitou
    $sql = "SELECT nome 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$id_funcionario_solicitador' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) $solicitador = $campos[0]['nome'];

    if(!empty($data_retirada_usa)) {
        $diferenca_entre_datas = data::diferenca_data($data_retirada_usa, date('Y-m-d'));
        $dias_vencer = data::diferenca_data(date('Y-m-d'), data::datatodate(data::adicionar_data_hora(data::datetodata($data_retirada_usa, '-'), $durabilidade_minima), '-'));
        if($diferenca_entre_datas[0] >= $durabilidade_minima) {
            $msg_valor_devido = "<marquee><b><font color='blue'>Funcionário com prazo cumprindo deste Produto</font></b></marquee>";
        }else {
//Pego o valor do ultimo valor da nota fiscal
            $sql = "Select nh.valor_entregue, nfe.id_tipo_moeda 
                            from itens_pedidos ip 
                            inner join nfe_historicos nh on nh.id_item_pedido = ip.id_item_pedido 
                            inner join nfe on nfe.id_nfe = nh.id_nfe 
                            where ip.id_produto_insumo = '$id_produto_insumo' order by nfe.data_emissao desc limit 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 1) {
//Pego o valores das moedas estrangeiras
                $sql = "SELECT valor_dolar_dia, valor_euro_dia, data 
                        FROM `cambios` 
                        ORDER BY id_cambio DESC LIMIT 1 ";
                $campos_moeda = bancos::sql($sql);
                if(count($campos_moeda) == 1) {
                    $valor_dolar    = $campos_moeda[0]['valor_dolar_dia'];
                    $valor_euro     = $campos_moeda[0]['valor_euro_dia'];
                }else {
                    $valor_dolar    = 1;
                    $valor_euro     = 1;
                }
                $id_tipo_moeda = $campos[0]['id_tipo_moeda'];
                $ultimo_valor_compra_nf = $campos[0]['valor_entregue'];

                if($id_tipo_moeda == 2) { //dolar
                    $ultimo_valor_compra_nf*= $valor_dolar;
                }else if($id_tipo_moeda == 3) { //euro
                    $ultimo_valor_compra_nf*= $valor_euro;
                }
            }else {
                $ultimo_valor_compra_nf = 0.00;
            }
            $formula_valor_devido = number_format(($durabilidade_minima - $diferenca_entre_datas[0]) / $durabilidade_minima * $ultimo_valor_compra_nf, 2, ',', '.');

            if($formula_valor_devido < $valor_variavel) {
?>
            <Script Language = 'JavaScript'>
                alert('ESTE PRODUTO ESTÁ DENTRO DO PRAZO DE DURABILIDADE PARA ESTE FUNCIONÁRIO !')
            </Script>
<?
                $msg_valor_devido = "<marquee><b><font color='red'>O prazo está menor que a durabilidade mínima O valor a pagar é R$ 0,00 </font></b></marquee>";
                $gravar_msg = "O prazo está menor que a durabilidade mínima O valor a pagar é R$ 0,00 ";
            }else {
?>
            <Script Language = 'JavaScript'>
                alert('ESTE PRODUTO ESTÁ DENTRO DO PRAZO DE DURABILIDADE PARA ESTE FUNCIONÁRIO !')
            </Script>
<?
                $msg_valor_devido = "<marquee><b><font color='red'>O prazo está menor que a durabilidade mínima, então o valor a pagar é R$ ".$formula_valor_devido."</font></b></marquee>";
                $gravar_msg = "O prazo está menor que a durabilidade mínima, então o valor a pagar é R$ ".$formula_valor_devido ;
            }
        }
    }else {
        $msg_valor_devido = "<marquee><b><font color='blue'>Funcionário sem prazo para cumprir deste Produto</font></b></marquee>";
    }
?>
<html>
<title>.:: Dar Baixa no Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'Javascript' src = '../../../../js/arred.js'></Script>
<Script Language = 'Javascript' src = '../../../../js/geral.js'></Script>
<Script Language = 'Javascript' src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' src = '../../../../js/validar.js'></Script>
</head>
<body onload='calcular();travar_qtde();document.form.txt_retirado_por.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_insumo' value='<?=$id_produto_insumo;?>'>
<input type='hidden' name='id_funcionario_solicitador' value='<?=$id_funcionario_solicitador;?>'>
<input type='hidden' name='txt_gravar_msg' value='<?=$gravar_msg;?>'>
<input type='hidden' name='passo'>
<input type='hidden' name='controle'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
	<tr class="atencao" align='center'>
            <td colspan='2'>
                <b><?=$mensagem[$valor];?></b>
            </td>
	</tr>
	<tr class='linhacabecalho' align='center'>
            <td colspan="2">
                Dar Baixa no Estoque
            </td>
	</tr>
	<tr class='linhanormal'>
            <td width='50%'>
                <b>Produto Insumo:</b>
            </td>
            <td width='50%'>
                <b>Observação do Produto:</b>
            </td>
	</tr>
	<tr class='linhanormal'>
            <td>
                <a href="javascript:nova_janela('../detalhes.php?id_produto_insumo=<?=$id_produto_insumo;?>', 'pop', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Consultar Inventário" class='link'>
                    <?=$discriminacao;?>
                </a>
            </td>
            <td>
            <?
                if(!empty($observacao)) {
                    echo $observacao;
                }else {
                    echo '&nbsp;';
                }
            ?>
            </td>
	</tr>
<?
		if($densidade_aco != '') {//Somente para a primeira vez q cair na tela
?>
	<tr class='linhanormal'>
		<td colspan="2">
<?
//Somente para a primeira vez q cair na tela
                    if(empty($controle)) {
                        $checado = '';
                    }else {
                        $checado = (!empty($chkt_metros)) ? 'checked' : '';
                    }
?>
                    <input type="checkbox" <?=$checado;?> name="chkt_metros" value="1" onclick="calcular();submeter()" id="label1" class="checkbox">
                    <label for="label1">
                        <font color="darkblue">
                            <b>Dar baixa em Metros</b>
                        </font>
                    </label>
		</td>
	</tr>
<?
		}
?>
	<tr class='linhanormal'>
		<td>
			<b>Retirado por</b>
			<?
				$checado_mesmo = ($chkt_mesmo == 1) ? 'checked' : '';
			?>
			<input type="checkbox" name="chkt_mesmo" value="1" onclick="igualar()" id="label2" <?=$checado_mesmo;?> class="checkbox"><label for="label2"> O MESMO</label>
		</td>
		<td>
			<b>Solicitado por</b>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type='text' name="txt_retirado_por" value="<?=$txt_retirado_por;?>" title="Digite o nome da pessoa que esta retirando" size="35" class='caixadetexto'>
			&nbsp;
			<input type='button' name='cmd_baixas_manip' value='Baixas / Manipulações' title='Baixas / Manipulações' onclick="nova_janela('../detalhes_baixas_manipulacoes.php?id_produto_insumo=<?=$id_produto_insumo;?>&nao_exibir_voltar=1', 'CONSULTAR', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:brown; font-weight: bold' class='botao'>
		</td>
		<td>
			<?=$solicitador;?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Quantidade:</b>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			Quantidade em <?=$sigla;?>:
		</td>
		<td>
			<b>Qtde Calculada</b>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
		<?
/*Somente p/ os usuários do Gladys, Roberto, Rodrigo Almoxarife, Dárcio e Bruno Almoxarife do q poderá ter 
dígitos negativos ...*/
			if($_SESSION['id_login'] == 20 || $_SESSION['id_login'] == 22 || $_SESSION['id_login'] == 80 || $_SESSION['id_login'] == 92 || $_SESSION['id_login'] == 102) {//Dígitos negativos ...
				$onkeyup = "verifica(this, 'moeda_especial', 2, '1', event);calcular()";
			}else {//P/ os outros usuários, somente digítos positivos ...
				$onkeyup = "verifica(this, 'moeda_especial', 2, '', event);calcular()";
			}
		?>
			<input type='text' name="txt_quantidade" value="<?=$txt_quantidade;?>" title="Digite a quantidade" size="12" onkeyup="<?=$onkeyup;?>" class='caixadetexto'>
			<?
				if($densidade_aco != '') {
//Somente para a primeira vez q cair na tela
					if(empty($controle)) {
						echo $sigla;
					}else {
						if(!empty($chkt_metros)) {
							echo 'Metros';
						}else {
							echo $sigla;
						}
					}
				}
			?>
			&nbsp;
			<input type='text' name="txt_quantidade_convertida" value="<?=$txt_quantidade_convertida;?>" title="Quantidade em KG" size="12" class="textdisabled" disabled>
			<?=$sigla;?>
			<input type="checkbox" name="chkt_troca" value="S" id="label_troca" class="checkbox" <?=$checked_troca;?>>
			<label for="label_troca">
				Troca <b> (Não computar CMM)</b>
			</label>
		</td>
		<td>
			<input type='text' name="txt_quantidade_calculada_kg" size="10" class="textdisabled" disabled> KG 
			<input type='text' name="txt_quantidade_calculada_mt" size="10" class="textdisabled" disabled> Metros
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Quantidade em Estoque /</b>
			<font color='brown'>
				<b>Qtde PI USADO:</b>
			</font>
		</td>
		<td>
			Nova Qtde em Estoque
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<input type='text' name="txt_qtde_estoque" value="<?=$qtde_estoque?>" title="Quantidade em Estoque" class="textdisabled" disabled>
			<font color='brown'>
				&nbsp;/ <b><?=$qtde_pi_usado;?></b>
			</font>
		</td>
		<td>
			<input type='text' name="txt_nova_qtde_estoque" title="Nova Qtde em Estoque" size="20" class="textdisabled" disabled>
		</td>
	</tr>
</table>
<!--************************Novo Controle com a Parte de OP(s)************************-->
<?
	$indice_linha =  0;//Será utilizado + abaixo em JavaScript ...
/*********************Esse trecho de código é independente de qualquer Etapa*********************/
/*Aqui eu seleciono todas as OP(s) que estão relacionadas a esse Produto Insumo e que não 
foram finalizadas ainda ...*/
	$sql = "SELECT DISTINCT(bop.`id_op`) 
                FROM `baixas_ops_vs_pis` bop 
                INNER JOIN `ops` ON ops.`id_op` = bop.`id_op` AND ops.`status_finalizar` = '0' AND ops.`ativo` = '1' 
                WHERE `id_produto_insumo` = '$id_produto_insumo' ";
	$campos_op = bancos::sql($sql);
	$linhas_op = count($campos_op);
	for($i = 0; $i < $linhas_op; $i++) {
            /*Busca o último Status de Baixa da OP em relação ao Produto Insumo que está sendo solicitado pelo usuário 
            p/ se dar uma nova Baixa ...*/
            $sql = "SELECT `status` 
                    FROM `baixas_ops_vs_pis` 
                    WHERE `id_produto_insumo` = '$id_produto_insumo' 
                    AND `id_op` = '".$campos_op[$i]['id_op']."' ORDER BY `id_baixa_op_vs_pi` DESC LIMIT 1 ";
            $campos_status_baixa_op = bancos::sql($sql);
/*Se a última situação de PI = "baixa", então significa que eu posso estar contabilizando 
essa OP no processo de confecção, pois saiu PI(s) do Almoxarifado p/ a Produção de PA...*/
            if($campos_status_baixa_op[0]['status'] == 2) $id_ops[] = $campos_op[$i]['id_op'];
	}
//Arranjo Ténico
	if(count($id_ops) == 0) {
            $id_ops[]   = '0';
            $comparador = '<>';
	}else if(count($id_ops) == 1) {
            $comparador = '<>';
	}else {
            $comparador = 'NOT IN';
	}
	$vetor_ops = implode(',', $id_ops);
	$total_ops_abertas = 0;//Acumula o Total encontrado em todas as Etapas ...
/********************************************************************/

/******************************1ª Etapa******************************/
/*Busca os P.A(s) que estão atrelados ao custo da Primeira Etapa através desse Produto Insumo
e claro a P.A(s) que estejam vinculados a OP(s) mais que estejam em aberto*/
	$sql = "SELECT DISTINCT(ops.`id_op`), ppe.`id_produto_acabado`, ppe.`pecas_por_emb` 
                FROM `pas_vs_pis_embs` ppe 
                INNER JOIN `ops` ON ops.`id_produto_acabado` = ppe.`id_produto_acabado` AND ops.`status_finalizar` = '0' AND ops.`ativo` = '1' AND ops.`id_op` $comparador ($vetor_ops) 
                WHERE ppe.`id_produto_insumo` = '$id_produto_insumo' ";
	$campos1 = bancos::sql($sql);
	$linhas1 = count($campos1);
	$total_ops_abertas+= $linhas1;
/******************************2ª Etapa******************************/
/*Busca os P.A(s) que estão atrelados ao custo da Segunda Etapa através desse Produto Insumo e claro 
a P.A(s) que estejam vinculados a OP(s) mais que estejam em aberto*/
	$sql = "SELECT DISTINCT(ops.`id_op`), pac.`id_produto_acabado_custo`, pac.`id_produto_acabado`, pac.`qtde_lote`, pac.`peca_corte`, pac.`comprimento_1`, pac.`comprimento_2` 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `ops` ON ops.`id_produto_acabado` = pac.`id_produto_acabado` AND ops.`status_finalizar` = '0' AND ops.`ativo` = '1' AND ops.`id_op` $comparador ($vetor_ops) 
                WHERE pac.`id_produto_insumo` = '$id_produto_insumo' 
                AND pac.`operacao_custo` = '0' ";
	$campos2 = bancos::sql($sql);
	$linhas2 = count($campos2);
	$total_ops_abertas+= $linhas2;
/******************************3ª Etapa******************************/
/*Busca os P.A(s) que estão atrelados ao custo da Terceira Etapa através desse Produto Insumo
e claro a P.A(s) que estejam vinculados a OP(s) mais que estejam em aberto*/
	$sql = "SELECT DISTINCT(ops.id_op), pac.id_produto_acabado, pp.qtde 
                FROM `pacs_vs_pis` pp 
                INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                INNER JOIN `ops` ON ops.`id_produto_acabado` = pac.`id_produto_acabado` AND ops.`status_finalizar` = '0' AND ops.`ativo` = '1' AND ops.`id_op` $comparador ($vetor_ops) 
                WHERE pac.`operacao_custo` = '0' 
                AND pp.`id_produto_insumo` = '$id_produto_insumo' ";
	$campos3 = bancos::sql($sql);
	$linhas3 = count($campos3);
	$total_ops_abertas+= $linhas3;
	
/*Se existir pelo menos 1 PA atrelado para o PI corrente em algumas dessas Etapas, eu exibindo essa 
parte normalmente ...*/
	if($total_ops_abertas > 0) {
?>
<table border="0" width='70%' align='center' cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
		<td colspan="6">
			OP(s) em Aberto
		</td>
	</tr>
	<tr class="linhadestaque" align='center'>
		<td>
			Qtde <?=$sigla;?>
		</td>
		<td>
			N.º OP
		</td>
		<td>
			Produto
		</td>
		<td>
			<font title="Quantidade à Produzir" style='cursor:help'>
				Qtde Prod
			</font>
		</td>
		<td>
			<font title="Quantidade do Produto Insumo" style='cursor:help'>
				Qtde PI
			</font>
		</td>
		<td>
			<font title="Necessidade Atual" style='cursor:help'>
				Nec. Atual
			</font>
		</td>
	</tr>
<?
/**************************************Listando os PA(s) da 1ª Etapa**************************************/
		for($i = 0; $i < $linhas1; $i++) {
?>
	<tr class='linhanormal' align='center'>
		<td>
		<?
//Se existir a Unidade de Conversão, então eu também faço a divisão com esta na fórmula também ...
                    if($unidade_conversao != 0) {
                        $qtde = (1 / $campos1[$i]['pecas_por_emb']) * (1 / $unidade_conversao);
                        $num_casas = 8;
/*Caso não exista a Unidade de Conversão, então eu não aplico esta na Fórmula p/ que não de erro
de Divisão por Zero ...*/
                    }else {
                        $qtde = (1 / $campos1[$i]['pecas_por_emb']);
                        $num_casas = 2;
                    }
		?>
                    <input type='text' name='txt_qtde[]' size="10" maxlength="8" onkeyup="verifica(this, 'moeda_especial', 2, '', event);quantidade_calculada()" class='caixadetexto'>
		</td>
		<td>
                    <a href="javascript:copiar_qtde('<?=$indice_linha;?>')" title="Copiar Necessidade Atual" style='cursor:help' class='link'>
                        <?=$campos1[$i]['id_op'];?>
                    </a>
                    <input type='hidden' name='id_op[]' value="<?=$campos1[$i]['id_op'];?>">
		</td>
		<td align='left'>
                    <a href = '../../../producao/ops/alterar.php?passo=2&id_op=<?=$campos1[$i]['id_op']?>&pop_up=1' title='Detalhes de OP' style='cursor:help' class='html5lightbox'>
                        <?=intermodular::pa_discriminacao($campos1[$i]['id_produto_acabado'], 0, 0, 0, 0, 1);?>
                    </a>
		</td>
		<td align='right'>
		<?
//Busco a Quantidade a Produzir da OP corrente ...
				$sql = "SELECT `qtde_produzir` 
                                        FROM `ops` 
                                        WHERE `id_op` = '".$campos1[$i]['id_op']."' LIMIT 1 ";
				$campos_quantidade  = bancos::sql($sql);
				$qtde_produzir      = $campos_quantidade[0]['qtde_produzir'];
/*Aqui nesse SQL, eu busco tudo o que foi produzido daquela OP que está em aberto para 
aquele PI que está sendo acessado ...*/
				/*$sql = "Select sum(bm.qtde) qtde_produzido 
						from baixas_manipulacoes bm 
						inner join baixas_ops_vs_pis bop on bop.id_baixa_manipulacao = bm.id_baixa_manipulacao and bop.id_produto_insumo = '$id_produto_insumo' 
						inner join ops on ops.id_op = bop.id_op and ops.status_finalizar = '0' and ops.ativo = '1' 
						where bop.id_op = '".$campos1[$i]['id_op']."' ";
				$campos_producao = bancos::sql($sql);//pego tudo q foi produzido até agora deste produto
				if(count($campos_producao) > 0) {
					$qtde_produzido = $campos_producao[0]['qtde_produzido'];
				}else {
					$qtde_produzido = 0;
				}
				
				Não estamos levando em conta as Entradas Parciais desta OP ...
				
				*/
				$producao = $qtde_produzir - $qtde_produzido;
				////////////////////// Se for ESP e Familia Pino aumentar 10% na qtde_produção ////////////////
				$sql = "Select pa.referencia 
						from produtos_acabados pa
						inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div=pa.id_gpa_vs_emp_div
						inner join grupos_pas gp on gp.id_grupo_pa=ged.id_grupo_pa 
						where pa.referencia='ESP' 
						and gp.id_familia=2 
						and id_produto_acabado=".$campos1[$i]['id_produto_acabado'];
				$campos_porcentagem = bancos::sql($sql,0,1);// se for ESP e Familia pino
				if(count($campos_porcentagem)>0) {// se for maior significa que é ESP e Family Pinos
					$cmb_porcentagem=10;
				} else {
					$cmb_porcentagem=0;
				}
				////////////////////////////////////////////////////////////////////////////////////////////
				//Aqui eu acrescento a % selecionada pelo Usuário em acima da Produção ...
				$producao+= $producao * ($cmb_porcentagem / 100);
				echo number_format($producao, 2, ',', '.');
		?>
		</td>
		<td align='right'>
			<?=number_format($qtde, $num_casas, ',', '.');?>
		</td>
		<td align='right'>
		<?
			$nec_atual1 = $qtde * $producao;
			if($nec_atual1 != 0) {
				echo segurancas::number_format($nec_atual1, 2, '.').' '.$campos[0]['sigla'];
			}
		?>
			<input type='hidden' name="txt_nec_atual[]" value="<?=number_format($nec_atual1, 2, ',', '.');?>">
		</td>
	</tr>
<?
			$indice_linha++;
		}
/**************************************Listando os PA(s) da 2ª Etapa**************************************/
		for($i = 0; $i < $linhas2; $i++) {
?>
	<tr class='linhanormal' align='center'>
		<td>
		<?
//Tenho que fazer esse Tratamento para que não de erro de Divisão por Zero ...
			if($campos2[$i]['peca_corte'] == 0) {
				$pecas_corte = 1;
			}else {
				$pecas_corte = $campos2[$i]['peca_corte'];
			}
			$comprimento_total = ($campos2[$i]['comprimento_1'] + $campos2[$i]['comprimento_2']) / 1000;
			$peso_aco_kg = $densidade_aco * $comprimento_total * 1.05;
			$peso_aco_kg/= $pecas_corte;
			$peso_aco_kg = round($peso_aco_kg, 5);
		?>
			<input type='text' name='txt_qtde[]' size="10" maxlength="8" onkeyup="verifica(this, 'moeda_especial', 2, '', event);quantidade_calculada()" class='caixadetexto'>
		</td>
		<td>
                    <a href="javascript:copiar_qtde('<?=$indice_linha;?>')" title='Copiar Necessidade Atual' style='cursor:help' class='link'>
                        <?=$campos2[$i]['id_op'];?>
                    </a>
                    <input type='hidden' name='id_op[]' value='<?=$campos2[$i]['id_op'];?>'>
		</td>
		<td align='left'>
                    <a href = '../../../producao/ops/alterar.php?passo=2&id_op=<?=$campos2[$i]['id_op']?>&pop_up=1' title='Detalhes de OP' style='cursor:help' class='html5lightbox'>
                        <?=intermodular::pa_discriminacao($campos2[$i]['id_produto_acabado'], 0, 0, 0, 0, 1);?>
                    </a>
		</td>
		<td align='right'>
		<?
//Busco a Quantidade a Produzir da OP corrente ...
				$sql = "Select qtde_produzir 
						from ops 
						where id_op = '".$campos2[$i]['id_op']."' limit 1 ";
				$campos_quantidade = bancos::sql($sql);
				$qtde_produzir = $campos_quantidade[0]['qtde_produzir'];
/*Aqui nesse SQL, eu busco tudo o que foi produzido daquela OP que está em aberto para 
aquele PI que está sendo acessado ...*/
				/*$sql = "Select sum(bm.qtde) qtde_produzido 
						from baixas_manipulacoes bm 
						inner join baixas_ops_vs_pis bop on bop.id_baixa_manipulacao = bm.id_baixa_manipulacao and bop.id_produto_insumo = '$id_produto_insumo' 
						inner join ops on ops.id_op = bop.id_op and ops.status_finalizar = '0' and ops.ativo = '1' 
						where bop.id_op = '".$campos2[$i]['id_op']."' ";
				$campos_producao = bancos::sql($sql);//pego tudo q foi produzido até agora deste produto
				if(count($campos_producao) > 0) {
					$qtde_produzido = $campos_producao[0]['qtde_produzido'];
				}else {
					$qtde_produzido = 0;
				}*/
				$producao=$qtde_produzir-$qtde_produzido;
				////////////////////// Se for ESP e Familia Pino aumentar 10% na qtde_produção ////////////////
				$sql = "Select pa.referencia 
						from produtos_acabados pa
						inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div=pa.id_gpa_vs_emp_div
						inner join grupos_pas gp on gp.id_grupo_pa=ged.id_grupo_pa 
						where pa.referencia='ESP' 
						and gp.id_familia=2 
						and id_produto_acabado=".$campos2[$i]['id_produto_acabado'];
				$campos_porcentagem = bancos::sql($sql,0,1);// se for ESP e Familia pino
				if(count($campos_porcentagem)>0) {// se for maior significa que é ESP e Family Pinos
					$cmb_porcentagem=10;
				} else {
					$cmb_porcentagem=0;
				}
				////////////////////////////////////////////////////////////////////////////////////////////
				//Aqui eu acrescento a % selecionada pelo Usuário em acima da Produção ...
				$producao+= $producao * ($cmb_porcentagem / 100);
				echo number_format($producao, 2, ',', '.');
		?>
		</td>
		<td align='right'>
			<?=number_format($peso_aco_kg, 5, ',', '.');?>
		</td>
		<td align='right'>
		<?
/*Aqui eu tive que fazer esse "trambique" porque estava dando erro de arredondamento com diferença
de 0,01 em comparado com o da função - vai entender ??? - Dárcio*/
			$nec_atual2 = round(round($producao * $peso_aco_kg, 3), 2);
			if($nec_atual2 != 0) {
				echo segurancas::number_format($nec_atual2, 2, '.');
			}
		?>
			<input type='hidden' name="txt_nec_atual[]" value="<?=number_format($nec_atual2, 2, ',', '.');?>">
		</td>
	</tr>
<?
			$indice_linha++;
		}
/**************************************Listando os PA(s) da 3ª Etapa**************************************/
		for($i = 0; $i < $linhas3; $i++) {
?>
	<tr class='linhanormal' align='center'>
		<td>
                    <input type='text' name='txt_qtde[]' size="10" maxlength="8" onkeyup="verifica(this, 'moeda_especial', 2, '', event);quantidade_calculada()" class='caixadetexto'>
		</td>
		<td>
                    <a href="javascript:copiar_qtde('<?=$indice_linha;?>')" title="Copiar Necessidade Atual" style='cursor:help' class='link'>
                        <?=$campos3[$i]['id_op'];?>
                    </a>
                    <input type='hidden' name='id_op[]' value='<?=$campos3[$i]['id_op'];?>'>
		</td>
		<td align='left'>
                    <a href = '../../../producao/ops/alterar.php?passo=2&id_op=<?=$campos3[$i]['id_op']?>&pop_up=1' title='Detalhes de OP' style='cursor:help' class='html5lightbox'>
                        <?=intermodular::pa_discriminacao($campos3[$i]['id_produto_acabado'], 0, 0, 0, 0, 1);?>
                    </a>
		</td>
		<td align='right'>
		<?
//Busco a Quantidade a Produzir da OP corrente ...
				$sql = "Select qtde_produzir 
						from ops 
						where id_op = '".$campos3[$i]['id_op']."' limit 1 ";
				$campos_quantidade = bancos::sql($sql);
				$qtde_produzir = $campos_quantidade[0]['qtde_produzir'];
/*Aqui nesse SQL, eu busco tudo o que foi produzido daquela OP que está em aberto para 
aquele PI que está sendo acessado ...*/
				/*$sql = "Select sum(bm.qtde) qtde_produzido 
						from baixas_manipulacoes bm 
						inner join baixas_ops_vs_pis bop on bop.id_baixa_manipulacao = bm.id_baixa_manipulacao and bop.id_produto_insumo = '$id_produto_insumo' 
						inner join ops on ops.id_op = bop.id_op and ops.status_finalizar = '0' and ops.ativo = '1' 
						where bop.id_op = '".$campos3[$i]['id_op']."' ";
				$campos_producao = bancos::sql($sql);//pego tudo q foi produzido até agora deste produto
				if(count($campos_producao) > 0) {
					$qtde_produzido = $campos_producao[0]['qtde_produzido'];
				}else {
					$qtde_produzido = 0;
				}*/
				$producao = $qtde_produzir - $qtde_produzido;
				////////////////////// Se for ESP e Familia Pino aumentar 10% na qtde_produção ////////////////
				$sql = "SELECT pa.referencia 
                                        FROM `produtos_acabados` pa 
                                        inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div=pa.id_gpa_vs_emp_div 
                                        inner join grupos_pas gp on gp.id_grupo_pa=ged.id_grupo_pa 
                                        where pa.referencia = 'ESP' 
                                        and gp.id_familia = '2' 
                                        and id_produto_acabado=".$campos3[$i]['id_produto_acabado']." LIMIT 1 ";
				$campos_porcentagem = bancos::sql($sql);// se for ESP e Familia pino
				if(count($campos_porcentagem)>0) {// se for maior significa que é ESP e Family Pinos
					$cmb_porcentagem = 10;
				} else {
					$cmb_porcentagem=0;
				}
				////////////////////////////////////////////////////////////////////////////////////////////

//Aqui eu acrescento a % selecionada pelo Usuário em acima da Produção ...
				$producao+= $producao * ($cmb_porcentagem / 100);
				echo number_format($producao, 2, ',', '.');
		?>
		</td>
		<td align='right'>
			<?=number_format($campos3[$i]['qtde'], 2, ',', '.');?>
		</td>
		<td align='right'>
		<?
			$nec_atual3 = $campos3[$i]['qtde'] * $producao;
			if($nec_atual3 != 0) {
				echo segurancas::number_format($nec_atual3, 2, '.');
			}
		?>
			<input type='hidden' name="txt_nec_atual[]" value="<?=number_format($nec_atual3, 2, ',', '.');?>">
		</td>
	</tr>
<?
			$indice_linha++;
		}
	}
/***OP(s) Finalizada(s) que estão sem Baixa no PI de 01/04/2007 em diante***/
//Código Temporário
//Busca de Todas as OP(s) que possuem Matéria Prima, independente de ser Finalizada ou não ...
	$sql = "SELECT DISTINCT(ops.id_op) 
                FROM `ops` 
                INNER JOIN `baixas_ops_vs_pis` bop on bop.id_op = ops.id_op 
                where ops.data_emissao >= '2008-04-01' 
                and ops.ativo = '1' ";
	$campos_op = bancos::sql($sql);
	$linhas_op = count($campos_op);
	for($i = 0; $i < $linhas_op; $i++) $id_ops_baixadas[] = $campos_op[$i]['id_op'];
//Arranjo Ténico
	if(count($id_ops_baixadas) == 0) {
            $id_ops_baixadas[] = '0';
            $comparador = '<>';
	}else if(count($id_ops_baixadas) == 1) {
            $comparador = '<>';
	}else {
            $comparador = 'NOT IN';
	}
	$vetor_ops_baixadas = implode(',', $id_ops_baixadas);
	$total_ops_finalizadas = 0;//Acumula o Total encontrado em todas as Etapas ...
/********************************************************************/

/******************************1ª Etapa******************************/
/*Busca todos P.A(s) que estão atrelados ao custo da Primeira Etapa através 
desse Produto Insumo e claro a P.A(s) que estejam vinculados a OP(s) que estejam fechadas*/
	$sql = "SELECT distinct(ops.id_op), ppe.id_produto_acabado, ppe.pecas_por_emb 
                FROM `pas_vs_pis_embs` ppe 
                INNER JOIN `ops` ON ops.id_produto_acabado = ppe.id_produto_acabado AND ops.`data_emissao` >= '2008-04-01' AND ops.`status_finalizar` = '1' AND ops.`ativo` = '1' AND ops.`id_op` $comparador ($vetor_ops_baixadas) 
                WHERE ppe.`id_produto_insumo` = '$id_produto_insumo' ";
	$campos1 = bancos::sql($sql);
	$linhas1 = count($campos1);
	$total_ops_finalizadas+= $linhas1;
/******************************2ª Etapa******************************/
/*Busca todos P.A(s) que estão atrelados ao custo da Segunda Etapa através 
desse Produto Insumo e claro a P.A(s) que estejam vinculados a OP(s) que estejam fechadas*/
	$sql = "SELECT distinct(ops.id_op), pac.id_produto_acabado_custo, pac.id_produto_acabado, pac.qtde_lote, pac.peca_corte, pac.comprimento_1, pac.comprimento_2 
                FROM `produtos_acabados_custos` pac 
                INNER JOIN `ops` ON ops.id_produto_acabado = pac.id_produto_acabado AND ops.`data_emissao` >= '2008-04-01' AND ops.`status_finalizar` = '1' AND ops.`ativo` = '1' AND ops.`id_op` $comparador ($vetor_ops_baixadas) 
                WHERE pac.`id_produto_insumo` = '$id_produto_insumo' AND pac.`operacao_custo` = 0 ";
	$campos2 = bancos::sql($sql);
	$linhas2 = count($campos2);
	$total_ops_finalizadas+= $linhas2;
/******************************3ª Etapa******************************/
/*Busca todos P.A(s) que estão atrelados ao custo da Terceira Etapa através 
desse Produto Insumo e claro a P.A(s) que estejam vinculados a OP(s) que estejam fechadas*/
	$sql = "SELECT DISTINCT(ops.id_op), pac.id_produto_acabado, pp.qtde 
                FROM `pacs_vs_pis` pp 
                INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
                INNER JOIN `ops` ON ops.`id_produto_acabado` = pac.`id_produto_acabado` AND ops.`data_emissao` >= '2008-04-01' AND ops.`status_finalizar` = '1' AND ops.`ativo` = '1' AND ops.`id_op` $comparador ($vetor_ops_baixadas) 
                WHERE pac.`operacao_custo` = '0' 
                AND pp.`id_produto_insumo` = '$id_produto_insumo' ";
	$campos3 = bancos::sql($sql);
	$linhas3 = count($campos3);
	$total_ops_finalizadas+= $linhas3;
/*Se existir pelo menos 1 PA atrelado para o PI corrente em algumas dessas Etapas, eu exibindo essa 
parte normalmente ...*/
	if($total_ops_finalizadas > 0) {
?>
<table border='0' width='70%' align='center' cellspacing ='1' cellpadding='1'>
	<tr class='linhacabecalho' align='center'>
		<td colspan="6">
			<font color='yellow'>
				OP(s) Finalizada(s) que estão sem Baixa no PI de 01/04/2007 em diante
			</font>
		</td>
	</tr>
	<tr class="linhadestaque" align='center'>
		<td>
			Qtde <?=$sigla;?>
		</td>
		<td>
			N.º OP
		</td>
		<td>
			Produto
		</td>
		<td>
			<font title="Quantidade à Produzir" style='cursor:help'>
				Qtde Prod
			</font>
		</td>
		<td>
			<font title="Quantidade do Produto Insumo" style='cursor:help'>
				Qtde PI
			</font>
		</td>
		<td>
			<font title="Necessidade Atual" style='cursor:help'>
				Nec. Atual
			</font>
		</td>
	</tr>
<?
/**************************************Listando os PA(s) da 1ª Etapa**************************************/
		for($i = 0; $i < $linhas1; $i++) {
?>
	<tr class='linhanormal' align='center'>
		<td>
		<?
//Se existir a Unidade de Conversão, então eu também faço a divisão com esta na fórmula também ...
			if($unidade_conversao != 0) {
				$qtde = (1 / $campos1[$i]['pecas_por_emb']) * (1 / $unidade_conversao);
				$num_casas = 8;
/*Caso não exista a Unidade de Conversão, então eu não aplico esta na Fórmula p/ que não de erro
de Divisão por Zero ...*/
			}else {
				$qtde = (1 / $campos1[$i]['pecas_por_emb']);
				$num_casas = 2;
			}
		?>
                    <input type='text' name='txt_qtde[]' size="10" maxlength="8" onkeyup="verifica(this, 'moeda_especial', 2, '', event);quantidade_calculada()" class='caixadetexto'>
		</td>
		<td>
                    <a href="javascript:copiar_qtde('<?=$indice_linha;?>')" title="Copiar Necessidade Atual" style='cursor:help' class='link'>
                        <?=$campos1[$i]['id_op'];?>
                    </a>
                    <input type='hidden' name='id_op[]' value='<?=$campos1[$i]['id_op'];?>'>
		</td>
		<td align='left'>
                    <a href = '../../../producao/ops/alterar.php?passo=2&id_op=<?=$campos1[$i]['id_op']?>&pop_up=1' title='Detalhes de OP' style='cursor:help' class='html5lightbox'>
                        <?=intermodular::pa_discriminacao($campos1[$i]['id_produto_acabado'], 0, 0, 0, 0, 1);?>
                    </a>
		</td>
		<td align='right'>
		<?
//Busco a Quantidade a Produzir da OP corrente ...
				$sql = "Select qtde_produzir 
						from ops 
						where id_op = '".$campos1[$i]['id_op']."' limit 1 ";
				$campos_quantidade = bancos::sql($sql);
				$qtde_produzir = $campos_quantidade[0]['qtde_produzir'];
/*Aqui nesse SQL, eu busco tudo o que foi produzido daquela OP que está em aberto para 
aquele PI que está sendo acessado ...*/
				/*$sql = "Select sum(bm.qtde) qtde_produzido 
						from baixas_manipulacoes bm 
						inner join baixas_ops_vs_pis bop on bop.id_baixa_manipulacao = bm.id_baixa_manipulacao and bop.id_produto_insumo = '$id_produto_insumo' 
						inner join ops on ops.id_op = bop.id_op and ops.status_finalizar = '0' and ops.ativo = '1' 
						where bop.id_op = '".$campos1[$i]['id_op']."' ";
				if(count($campos_producao) > 0) {
					$qtde_produzido = $campos_producao[0]['qtde_produzido'];
				}else {
					$qtde_produzido = 0;
				}*/
				$producao = $qtde_produzir - $qtde_produzido;
				////////////////////// Se for ESP e Familia Pino aumentar 10% na qtde_produção ////////////////
				$sql = "Select pa.referencia 
						from produtos_acabados pa
						inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div=pa.id_gpa_vs_emp_div
						inner join grupos_pas gp on gp.id_grupo_pa=ged.id_grupo_pa 
						where pa.referencia='ESP' 
						and gp.id_familia=2 
						and id_produto_acabado=".$campos1[$i]['id_produto_acabado'];
				$campos_porcentagem = bancos::sql($sql,0,1);// se for ESP e Familia pino
				if(count($campos_porcentagem)>0) {// se for maior significa que é ESP e Family Pinos
					$cmb_porcentagem=10;
				} else {
					$cmb_porcentagem=0;
				}
				////////////////////////////////////////////////////////////////////////////////////////////
				//Aqui eu acrescento a % selecionada pelo Usuário em acima da Produção ...
				$producao+= $producao * ($cmb_porcentagem / 100);
				echo number_format($producao, 2, ',', '.');
		?>
		</td>
		<td align='right'>
			<?=number_format($qtde, $num_casas, ',', '.');?>
		</td>
		<td align='right'>
		<?
			$nec_atual1 = $qtde * $producao;
			if($nec_atual1 != 0) {
				echo segurancas::number_format($nec_atual1, 2, '.').' '.$campos[0]['sigla'];
			}
		?>
			<input type='hidden' name="txt_nec_atual[]" value="<?=number_format($nec_atual1, 2, ',', '.');?>">
		</td>
	</tr>
<?
			$indice_linha++;
		}
/**************************************Listando os PA(s) da 2ª Etapa**************************************/
		for($i = 0; $i < $linhas2; $i++) {
?>
	<tr class='linhanormal' align='center'>
		<td>
		<?
//Tenho que fazer esse Tratamento para que não de erro de Divisão por Zero ...
			if($campos2[$i]['peca_corte'] == 0) {
				$pecas_corte = 1;
			}else {
				$pecas_corte = $campos2[$i]['peca_corte'];
			}
			$comprimento_total = ($campos2[$i]['comprimento_1'] + $campos2[$i]['comprimento_2']) / 1000;
			$peso_aco_kg = $densidade_aco * $comprimento_total * 1.05;
			$peso_aco_kg/= $pecas_corte;
			$peso_aco_kg = round($peso_aco_kg, 3);
		?>
                    <input type='text' name='txt_qtde[]' size="10" maxlength="8" onkeyup="verifica(this, 'moeda_especial', 2, '', event);quantidade_calculada()" class='caixadetexto'>
		</td>
		<td>
                    <a href="javascript:copiar_qtde('<?=$indice_linha;?>')" title="Copiar Necessidade Atual" style='cursor:help' class='link'>
                        <?=$campos2[$i]['id_op'];?>
                    </a>
                    <input type='hidden' name='id_op[]' value='<?=$campos2[$i]['id_op'];?>'>
		</td>
		<td align='left'>
                    <a href = '../../../producao/ops/alterar.php?passo=2&id_op=<?=$campos2[$i]['id_op']?>&pop_up=1' title='Detalhes de OP' style='cursor:help' class='html5lightbox'>
                        <?=intermodular::pa_discriminacao($campos2[$i]['id_produto_acabado'], 0, 0, 0, 0, 1);?>
                    </a>
		</td>
		<td align='right'>
		<?
//Busco a Quantidade a Produzir da OP corrente ...
				$sql = "Select qtde_produzir 
						from ops 
						where id_op = '".$campos2[$i]['id_op']."' limit 1 ";
				$campos_quantidade = bancos::sql($sql);
				$qtde_produzir = $campos_quantidade[0]['qtde_produzir'];
/*Aqui nesse SQL, eu busco tudo o que foi produzido daquela OP que está em aberto para 
aquele PI que está sendo acessado ...*/
				/*$sql = "Select sum(bm.qtde) qtde_produzido 
						from baixas_manipulacoes bm 
						inner join baixas_ops_vs_pis bop on bop.id_baixa_manipulacao = bm.id_baixa_manipulacao and bop.id_produto_insumo = '$id_produto_insumo' 
						inner join ops on ops.id_op = bop.id_op and ops.status_finalizar = '0' and ops.ativo = '1' 
						where bop.id_op = '".$campos2[$i]['id_op']."' ";
				$campos_producao = bancos::sql($sql);//pego tudo q foi produzido até agora deste produto
				if(count($campos_producao) > 0) {
					$qtde_produzido = $campos_producao[0]['qtde_produzido'];
				}else {
					$qtde_produzido = 0;
				}*/
				$producao=$qtde_produzir-$qtde_produzido;
				////////////////////// Se for ESP e Familia Pino aumentar 10% na qtde_produção ////////////////
				$sql = "Select pa.referencia 
						from produtos_acabados pa
						inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div=pa.id_gpa_vs_emp_div
						inner join grupos_pas gp on gp.id_grupo_pa=ged.id_grupo_pa 
						where pa.referencia='ESP' 
						and gp.id_familia=2 
						and id_produto_acabado=".$campos2[$i]['id_produto_acabado'];
				$campos_porcentagem = bancos::sql($sql,0,1);// se for ESP e Familia pino
				if(count($campos_porcentagem)>0) {// se for maior significa que é ESP e Family Pinos
					$cmb_porcentagem=10;
				} else {
					$cmb_porcentagem=0;
				}
				////////////////////////////////////////////////////////////////////////////////////////////
				//Aqui eu acrescento a % selecionada pelo Usuário em acima da Produção ...
				$producao+= $producao * ($cmb_porcentagem / 100);
				echo number_format($producao, 2, ',', '.');
		?>
		</td>
		<td align='right'>
			<?=number_format($peso_aco_kg, 3, ',', '.');?>
		</td>
		<td align='right'>
		<?
/*Aqui eu tive que fazer esse "trambique" porque estava dando erro de arredondamento com diferença
de 0,01 em comparado com o da função - vai entender ??? - Dárcio*/
			$nec_atual2 = round(round($producao * $peso_aco_kg, 3), 2);
			if($nec_atual2 != 0) {
				echo segurancas::number_format($nec_atual2, 2, '.');
			}
		?>
			<input type='hidden' name="txt_nec_atual[]" value="<?=number_format($nec_atual2, 2, ',', '.');?>">
		</td>
	</tr>
<?
			$indice_linha++;
		}
/**************************************Listando os PA(s) da 3ª Etapa**************************************/
		for($i = 0; $i < $linhas3; $i++) {
?>
	<tr class='linhanormal' align='center'>
		<td>
                    <input type='text' name='txt_qtde[]' size="10" maxlength="8" onkeyup="verifica(this, 'moeda_especial', 2, '', event);quantidade_calculada()" class='caixadetexto'>
		</td>
		<td>
                    <a href="javascript:copiar_qtde('<?=$indice_linha;?>')" title="Copiar Necessidade Atual" style='cursor:help' class='link'>
                        <?=$campos3[$i]['id_op'];?>
                    </a>
                    <input type='hidden' name='id_op[]' value='<?=$campos3[$i]['id_op'];?>'>
		</td>
		<td align='left'>
                    <a href = '../../../producao/ops/alterar.php?passo=2&id_op=<?=$campos3[$i]['id_op']?>&pop_up=1' title='Detalhes de OP' style='cursor:help' class='html5lightbox'>
                        <?=intermodular::pa_discriminacao($campos3[$i]['id_produto_acabado'], 0, 0, 0, 0, 1);?>
                    </a>
		</td>
		<td align='right'>
		<?
//Busco a Quantidade a Produzir da OP corrente ...
				$sql = "Select qtde_produzir 
						from ops 
						where id_op = '".$campos3[$i]['id_op']."' limit 1 ";
				$campos_quantidade = bancos::sql($sql);
				$qtde_produzir = $campos_quantidade[0]['qtde_produzir'];		
/*Aqui nesse SQL, eu busco tudo o que foi produzido daquela OP que está em aberto para 
aquele PI que está sendo acessado ...*/
				/*$sql = "Select sum(bm.qtde) qtde_produzido 
						from baixas_manipulacoes bm 
						inner join baixas_ops_vs_pis bop on bop.id_baixa_manipulacao = bm.id_baixa_manipulacao and bop.id_produto_insumo = '$id_produto_insumo' 
						inner join ops on ops.id_op = bop.id_op and ops.status_finalizar = '0' and ops.ativo = '1' 
						where bop.id_op = '".$campos3[$i]['id_op']."' ";
				$campos_producao = bancos::sql($sql);//pego tudo q foi produzido até agora deste produto
				if(count($campos_producao) > 0) {
					$qtde_produzido = $campos_producao[0]['qtde_produzido'];
				}else {
					$qtde_produzido = 0;
				}*/
				$producao = $qtde_produzir - $qtde_produzido;
				////////////////////// Se for ESP e Familia Pino aumentar 10% na qtde_produção ////////////////
				$sql = "Select pa.referencia 
						from produtos_acabados pa
						inner join gpas_vs_emps_divs ged on ged.id_gpa_vs_emp_div=pa.id_gpa_vs_emp_div
						inner join grupos_pas gp on gp.id_grupo_pa=ged.id_grupo_pa 
						where pa.referencia='ESP' 
						and gp.id_familia=2 
						and id_produto_acabado=".$campos3[$i]['id_produto_acabado'];
				$campos_porcentagem = bancos::sql($sql,0,1);// se for ESP e Familia pino
				if(count($campos_porcentagem) > 0) {// se for maior significa que é ESP e Family Pinos
					$cmb_porcentagem = 10;
				}else {
					$cmb_porcentagem = 0;
				}
				////////////////////////////////////////////////////////////////////////////////////////////

//Aqui eu acrescento a % selecionada pelo Usuário em acima da Produção ...
				$producao+= $producao * ($cmb_porcentagem / 100);
				echo number_format($producao, 2, ',', '.');
		?>
		</td>
		<td align='right'>
			<?=number_format($campos3[$i]['qtde'], 2, ',', '.');?>
		</td>
		<td align='right'>
		<?
			$nec_atual3 = $campos3[$i]['qtde'] * $producao;
			if($nec_atual3 != 0) {
				echo segurancas::number_format($nec_atual3, 2, '.');
			}
		?>
			<input type='hidden' name="txt_nec_atual[]" value="<?=number_format($nec_atual3, 2, ',', '.');?>">
		</td>
	</tr>
<?
			$indice_linha++;
		}
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan="6">
			&nbsp;
		</td>
	</tr>
</table>
<?
	}
	$qtde_ops = $total_ops_abertas + $total_ops_finalizadas;//Variável utilizada mais abaixo ...
?>
<!--**********************************************************************************-->
<table border="0" width='70%' align='center' cellspacing ='1' cellpadding='1'>
	<tr class='linhanormal'>
		<td width='50%'>
                    <b>Durabilidade Mínima</b>
		</td>
		<td width='50%'>
                    <b>Data da Última Retirada do Funcionário</b>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<?=$durabilidade_minima;?>
		</td>
		<td>
			<?=$data_retirada;?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<b>Valor do EPI</b>
		</td>
		<td>
			<b>Dias para vencer o prazo</b>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td>
			<?='Min. R$ '.$valor_variavel.' - Max. R$ '.number_format($ultimo_valor_compra_nf, 2, ',', '.');?>
		</td>
		<td>
			<?=$dias_vencer[0];?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<?=$msg_valor_devido;?>
		</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">Observação</td>
	</tr>
	<tr class='linhanormal'>
		<td colspan="2">
			<textarea name="txt_observacao" cols="60" rows="3" title="Digite a observação" class='caixadetexto'><?=$txt_observacao;?></textarea>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="2">
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php?passo=3&inicio=<?=$inicio;?>&pagina=<?=$pagina;?>&txt_consultar2=<?=$txt_consultar2;?>&opt_opcao2=<?=$opt_opcao2;?>&id_produto_insumo=<?=$id_produto_insumo;?>'" class='botao'>
			<input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form','LIMPAR');calcular();travar_qtde();document.form.txt_retirado_por.focus()" style="color:#ff9900;" class='botao'>
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
		</td>
	</tr>
</table>
</form>
<pre>
<font color="darkblue">
<b>* Na exibição da(s) OP(s) em Aberto, só está levando em conta o(s) PA(s) que estão atrelado(s) à 
  1ª, 2ª e 3ª Etapa do Custo.</b>
</font>
<font color="blue"><b>Calculo devido da durabilidade do produto por funcionário</b></font>
Se ((data_atual-data_retirada) < durabilidade_mínima)
<b>formula_valor_devido</b> = (durabilidade_mínima-(data_atual - data_retirada)) / durabilidade_mínima * último valor comprado em NF
Se fórmula_valor_devido < valor_variavel
	exibo uma mensagem com valor o R$ 0,00
Se não
	exibo uma mensagem com valor o R$ (fórmula com o custo devido)
</pre>
</body>
<Script Language = 'Javascript'>
function copiar_qtde(indice) {
	var elementos = document.form.elements
//Somente em alguns P.A.(s) que exibirá esse objeto que é o checkbox ...
	if(typeof(document.form.chkt_metros) == 'object') {
		var objetos_inicio = 16//Qtde de Objetos antes do Loop
	}else {
		var objetos_inicio = 15//Qtde de Objetos antes do Loop
	}
	var objetos_linha = 3//Qtde de Objetos por Loop
/*Significa que o usuário está clicando da segunda linha em diante, aqui se realiza
esse macete porque se tem 3 objetos por linha contando o checkbox também*/
	if(indice != 0) {
		var cont = (indice * objetos_linha) + objetos_inicio
	}else {
		var cont = objetos_inicio
	}
//Aqui eu igualo a Necessidade Atual no campo de Quantidade p/ facilitar a vida do Usuário ...
	elementos[cont].value = elementos[cont + 2].value
//Aqui eu chamo essa função que faz um somatório de todas as Qtdes dadas em OP (Baixas) ...
	quantidade_calculada()
}

//Nessa função eu faço um somatório de todas as Qtdes dadas em OP (Baixas) ...
function quantidade_calculada() {
	var elementos = document.form.elements
	var quantidade_calculada = 0
	var densidade_aco = eval('<?=$densidade_aco;?>')
//Faço assim porque essa variável é utilizada em uma divisão e assim não dá erro de Divisão por Zero ...
	if(typeof(densidade_aco) == 'undefined') {
		densidade_aco = 1
	}
//Disparo do Loop ...
	for(var i = 0; i < elementos.length; i++) {
//Controle com os Qtde(s) da Primeira, Segunda, Terceira e Sétima Etapa p/ poder gravar no BD ...
		if(elementos[i].name == 'txt_qtde[]' && elementos[i].value != '') {
			quantidade_calculada+= eval(strtofloat(elementos[i].value))
		}
	}
	document.form.txt_quantidade_calculada_kg.value = quantidade_calculada
	document.form.txt_quantidade_calculada_mt.value = quantidade_calculada / densidade_aco
	document.form.txt_quantidade_calculada_kg.value = arred(document.form.txt_quantidade_calculada_kg.value, 2, 1)
	document.form.txt_quantidade_calculada_mt.value = arred(document.form.txt_quantidade_calculada_mt.value, 2, 1)
}

function submeter() {
	document.form.controle.value = 1
	document.form.passo.value = 4
	document.form.submit()
}

function igualar() {
	if(document.form.chkt_mesmo.checked == true) {
		document.form.txt_retirado_por.value = '<?=trim($solicitador);?>'
	}else {
		document.form.txt_retirado_por.value = ''
	}
}

function calcular() {
	if(document.form.txt_quantidade.value == '-') {
		document.form.txt_quantidade.value = ''
	}

	var qtde_estoque = eval(strtofloat(document.form.txt_qtde_estoque.value))
	var quantidade = eval(strtofloat(document.form.txt_quantidade.value))
<?
//Produtos do Tipo Aço
	if($densidade_aco != '') {
/*Significa que está sendo feito o cálculo de Kilos para Metros pois está checkado o objeto 
"Dar Baixa em Metros"*/
		if(!empty($chkt_metros)) {
?>
			var densidade_aco = eval('<?=$densidade_aco;?>')
			if(typeof(quantidade) == 'undefined') {
				document.form.txt_quantidade_convertida.value = ''
				document.form.txt_nova_qtde_estoque.value = ''
//Aqui é a conversão de Metros para Kilos
			}else {
				document.form.txt_quantidade_convertida.value = densidade_aco * quantidade
				document.form.txt_quantidade_convertida.value = arred(document.form.txt_quantidade_convertida.value, 2, 1)

				var quantidade_kg = eval(strtofloat(document.form.txt_quantidade_convertida.value))
				document.form.txt_nova_qtde_estoque.value = qtde_estoque - quantidade_kg
				document.form.txt_nova_qtde_estoque.value = arred(document.form.txt_nova_qtde_estoque.value, 2, 1)
			}
<?
/*Significa que está sendo feito o cálculo de Metros para Kilos porque não está checkado o objeto 
"Dar Baixa em Metros"*/
		}else {
?>
			if(typeof(quantidade) == 'undefined') {
				document.form.txt_quantidade_convertida.value = ''
				document.form.txt_nova_qtde_estoque.value = ''
			}else {
				document.form.txt_quantidade_convertida.value = document.form.txt_quantidade.value
				document.form.txt_nova_qtde_estoque.value = qtde_estoque - quantidade
				document.form.txt_nova_qtde_estoque.value = arred(document.form.txt_nova_qtde_estoque.value, 2, 1)
			}
<?
		}
//Produtos Normais
	}else {
?>
		if(typeof(quantidade) == 'undefined') {
			document.form.txt_quantidade_convertida.value = ''
			document.form.txt_nova_qtde_estoque.value = ''
		}else {
			document.form.txt_quantidade_convertida.value = document.form.txt_quantidade.value
			document.form.txt_nova_qtde_estoque.value = qtde_estoque - quantidade
			document.form.txt_nova_qtde_estoque.value = arred(document.form.txt_nova_qtde_estoque.value, 2, 1)
		}
<?
	}
?>
}

//Função que é solicitada quando carrega a Tela ...
function travar_qtde() {
	qtde_ops = eval('<?=$qtde_ops;?>')
//Caso exista alguma OP na Tela de Baixa de Estoque, então eu travo a caixa de Qtde ...

//No momento está desativada devido o roberto não ter uma lógica definitiva ainda ... 	
	/*if(qtde_ops > 0) {
		document.form.txt_quantidade.style.color = 'gray'
		document.form.txt_quantidade.style.background = '#FFFFE1'
		document.form.txt_quantidade.disabled = true
	}*/
}

function validar() {
/***************************************************************************/
//Variáveis que serão utilizadas mais abaixo ...
    var elementos = document.form.elements
    var qtde_ops = eval('<?=$qtde_ops;?>')
/***************************************************************************/
//Retirado por
    if(!texto('form', 'txt_retirado_por', '1', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'RETIRADO POR', '2')) {
        return false
    }
/*Somente p/ os usuários do Roberto, Gladys, Luís e Rodrigo do Almoxarifado q poderá ter 
dígitos negativos ...*/
<?
    if($_SESSION['id_login'] == 20 || $_SESSION['id_login'] == 22 || $_SESSION['id_login'] == 80 || $_SESSION['id_login'] == 98 || $_SESSION['id_login'] == 102) {//Dígitos negativos ...
?>
        if(!texto('form', 'txt_quantidade', '1', '1234567890.,-', 'QUANTIDADE', '1')) {
            return false
        }
<?
    }else {
?>
        if(!texto('form', 'txt_quantidade', '1', '1234567890.,', 'QUANTIDADE', '1')) {
            return false
        }
<?
    }
?>
//Se a Qtde for Igual a Zero ...
    if(document.form.txt_quantidade.value == '0,00') {
        alert('QUANTIDADE INVÁLIDA !')
        document.form.txt_quantidade.focus()
        document.form.txt_quantidade.select()
        return false
    }
//Se existir 1 OP, então forço o preenchimento do campo Qtde ou pelo menos de 1 OP ...
    if(qtde_ops > 0) {
        var ops_preenchidas = 0
//Primeiro verifico se foi preenchido algum valor p/ alguma OP ...
        if(document.form.txt_quantidade_calculada_kg.value != '' && document.form.txt_quantidade_calculada_kg.value != '0,00') {
            ops_preenchidas++
        }
//Verificação referente ao preenchimento dos campos de baixa ...
        if(ops_preenchidas == 0 && document.form.txt_quantidade.value == '') {
            alert('DIGITE UMA QUANTIDADE OU PREENCHA ALGUM CAMPO DE OP !')
            document.form.txt_quantidade.focus()
            return false
        }
    }
/**********************************Controles*****************************************/
//Comparação da Quantidade Digitada pelo Usuário com a Qtde em Kilos ou em Metros ...
    var quantidade = eval(strtofloat(document.form.txt_quantidade.value))
    var quantidade_calculada_kg = eval(strtofloat(document.form.txt_quantidade_calculada_kg.value))
    var quantidade_calculada_mt = eval(strtofloat(document.form.txt_quantidade_calculada_mt.value))

//Essa comparação eu faço antes de submeter para o Banco de Dados ...
    if(typeof(document.form.chkt_metros) == 'object') {
        if(document.form.chkt_metros.checked == true) {//Se for em Metros, eu comparo a Qtde c/ a Qtde em Mts
            if(quantidade != quantidade_calculada_mt) {
                var resposta1 = confirm('A QTDE DIGITADA ESTÁ INCOMPATÍVEL COM A QUANTIDADE CALCULADA EM METROS !!!\nDESEJA CONTINUAR ?')
                if(resposta1 == false) return false
            }
        }else {//Senão eu comparo a Qtde c/ a Qtde em Kgs
            if(quantidade != quantidade_calculada_kg) {
                var resposta1 = confirm('A QTDE DIGITADA ESTÁ INCOMPATÍVEL COM A QUANTIDADE CALCULADA EM KILOS !!!\nDESEJA CONTINUAR ?')
                if(resposta1 == false) return false
            }
        }
    }
/*************************Novo Controle com a Parte de OP(s)*************************/
//Tolerância de ate 10% a + ou a - ....
    if(qtde_ops > 0) {
        var qtde_digitada       = (document.form.txt_quantidade.value != '') ? eval(strtofloat(document.form.txt_quantidade.value)) : 0
        var qtde_calculada_kg   = (document.form.txt_quantidade_calculada_kg.value != '') ? eval(strtofloat(document.form.txt_quantidade_calculada_kg.value)) : 0

        if((qtde_digitada / qtde_calculada_kg > 1.1) || (qtde_digitada / qtde_calculada_kg < 0.9)) {
            var resposta = confirm('A QTDE DE BAIXA ESTA COM DIFERENÇA MAIOR QUE 10% DA QTDE CALCULADA (SOMATÓRIA DA QTDE DAS OPS !!!)\n\nCLIQUE EM CANCELAR P/ ACEITAR A QUANTIDADE DIGITADA ! ')
            if(resposta == true) {
                document.form.txt_quantidade.focus()
                document.form.txt_quantidade.select()
                return false
            }
        }
//Caso exista alguma OP na Tela de Baixa de Estoque ...
//Não trava mais temporariamente, então eu travo a caixa de Qtde, não definiu a lógica ainda ...
    
        for(var i = 0; i < elementos.length; i++) {
//Tratamento com as Qtde(s) da 1ª, 2ª, 3ª e 7ª Etapa p/ poder gravar no BD ...
            if(elementos[i].name == 'txt_qtde[]') elementos[i].value = strtofloat(elementos[i].value)
        }
    }
/************************************************************************************/		
//Desabilita p/ poder gravar no BD ...
    document.form.txt_quantidade.disabled               = false
    document.form.txt_quantidade_convertida.disabled    = false
//Para o passo q vai seguir ...
    document.form.passo.value                           = 5
//Aqui eu desabilito o botão Salvar p/ não acontecer de o usuário clicar várias vezes ...
    document.form.cmd_salvar.disabled                   = true
    document.form.cmd_salvar.className                  = 'textdisabled'
//Tratamento p/ poder gravar esses 2 campos no Banco ...
    return limpeza_moeda('form', 'txt_quantidade, txt_quantidade_convertida, ')
}
</Script>
</html>
<?
}else if($passo == 5) {
/************************************************************************************/
//Verifico se a Sessão não caiu ...
    if(!(session_is_registered('id_funcionario'))) {
?>
        <Script Language = 'JavaScript'>
            window.location = '../../../../html/index.php?valor=1'
        </Script>
<?
        exit;
    }
/************************************************************************************/
    //Busca a Qtde em Estoque do Produto Insumo ...
    $sql = "SELECT `qtde` 
            FROM `estoques_insumos` 
            WHERE `id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $qtde_estoque 	= (count($campos) == 0) ? 0 : $campos[0]['qtde'];
    $estoque_final 	= $qtde_estoque - $_POST['txt_quantidade_convertida'];
        
    if($estoque_final < 0) {//Nunca podemos deixar o Estoque Negativo ...
?>
	<Script Language = 'JavaScript'>
            alert('A QUANTIDADE SOLICITADA É MAIOR DO QUE O SALDO EM ESTOQUE !')
            window.location = 'incluir.php?passo=4&id_produto_insumo=<?=$_POST['id_produto_insumo'];?>'
        </Script>
<?
    }else {//Procedimento normal
        $data_sys = date('Y-m-d H:i:s');
        if(!empty($txt_gravar_msg) && !empty($txt_observacao)) { 
            $txt_observacao = $txt_gravar_msg.'|'.strtolower($txt_observacao);
        }else if(empty($txt_gravar_msg) && !empty($txt_observacao)) {
            $txt_observacao = strtolower($txt_observacao);
        }else if(!empty($txt_gravar_msg) && empty($txt_observacao)) {
            $txt_observacao = $txt_gravar_msg;
        }
        $quantidade = ($_POST['chkt_metros'] == 1) ?  $_POST['txt_quantidade_convertida'] : $_POST['txt_quantidade'];
//Aqui eu inverto o Sinal da Quantidade, p/ não dar erro de CMM ...
        $quantidade*= -1;
//Controle com a Parte de Troca ...
        $troca = (!empty($_POST['chkt_troca'])) ? 'S' : 'N';
//Inserindo os Dados no BD ...
        $sql = "INSERT INTO `baixas_manipulacoes` (`id_baixa_manipulacao`, `id_produto_insumo`, `id_funcionario`, `id_funcionario_retirado`, `qtde`, `retirado_por`, `estoque_final`, `observacao`, `acao`, `troca`, `data_sys`) VALUES (NULL, '$_POST[id_produto_insumo]', '$_SESSION[id_funcionario]', '$_POST[id_funcionario_solicitador]', '$quantidade', '$_POST[txt_retirado_por]', '$estoque_final', '$txt_observacao', 'B', '$troca', '$data_sys') ";
        bancos::sql($sql);
        $id_baixa_manipulacao = bancos::id_registro();
        estoque_ic::atualizar($id_produto_insumo, 0);
//************************Novo Controle com a Parte de OP(s)************************
        for($i = 0; $i < count($txt_qtde); $i++) {
//Se a qtde estiver preenchida ...
            if($txt_qtde[$i] != '' && $txt_qtde[$i] != '0.00') {
                $sql = "INSERT INTO `baixas_ops_vs_pis` (`id_baixa_op_vs_pi`, `id_produto_insumo`, `id_op`, `id_baixa_manipulacao`, `qtde_baixa`, `data_sys`, `status`) VALUES (NULL, '$id_produto_insumo', '$id_op[$i]', '$id_baixa_manipulacao', '$txt_qtde[$i]', '$data_sys', '2') ";
                bancos::sql($sql);
            }
        }
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir.php<?=$parametro;?>&valor=2'
        </Script>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Insumo(s) p/ Dar Baixa no Estoque do PI ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 4; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 4; i ++) document.form.opt_opcao[i].disabled = false
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
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Insumo(s) p/ Dar Baixa no Estoque do PI
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'> 
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar Produtos Insumos por: Referência' id='label'>
            <label for='label'>
                Referência
            </label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' onclick='document.form.txt_consultar.focus()' title='Consultar Produtos Insumos por: Discrimina&ccedil;&atilde;o' id='label2' checked>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='3' onclick='document.form.txt_consultar.focus()' title='Consultar Produtos Insumos por: Observa&ccedil;&atilde;o' id='label3'>
            <label for='label3'>
                Observa&ccedil;&atilde;o
            </label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='4' onclick='document.form.txt_consultar.focus()' title='Consultar Produtos Insumos por: OP' id='label4'>
            <label for='label4'>
                N° OP
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan="2">
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os Produtos Insumos' onclick='limpar()' class='checkbox' id='label5'>
            <label for='label5'>
                Todos os registros
            </label>
        </td>                
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<font color='red'><b>Observação:</b></font>

<b>* Só não traz P.I(s) do Tipo PRAC</b>
</pre>