<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>DADO BAIXA COM SUCESSO.</font>";

if($passo == 1) {
    /*Somente p/ o "Roberto" 62 e "Dárcio" 98 porque programa, pode dar Baixa de um PA independente 
    de ser componente ou não ...*/
    $condicao_componente = ($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) ? '' : " AND gpa.`id_familia` IN (23, 24) ";
    
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT gpa.`nome`, pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, pa.`mmv`, 
                    pa.`observacao`, u.`sigla` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` $condicao_componente 
                    WHERE pa.`referencia` LIKE '%$txt_consultar%' 
                    AND pa.`ativo` = '1' ORDER BY pa.`discriminacao` ";
        break;
        case 2:
            $sql = "SELECT gpa.`nome`, pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, pa.`mmv`, 
                    pa.`observacao`, u.`sigla` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` $condicao_componente 
                    WHERE pa.`discriminacao` LIKE '%$txt_consultar%' 
                    AND pa.`ativo` = '1' ORDER BY pa.`discriminacao` ";
        break;
        case 3:
            $sql = "SELECT gpa.`nome`, pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, pa.`mmv`, 
                    pa.`observacao`, u.`sigla` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` $condicao_componente 
                    WHERE pa.`observacao` LIKE '%$txt_consultar%' 
                    AND pa.`ativo` = '1' ORDER BY pa.`discriminacao` ";
        break;
        default:
            $sql = "SELECT gpa.`nome`, pa.`id_produto_acabado`, pa.`operacao_custo`, pa.`referencia`, pa.`mmv`, 
                    pa.`observacao`, u.`sigla` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` $condicao_componente 
                    WHERE pa.`ativo` = '1' ORDER BY pa.`discriminacao` ";
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
<title>.:: Produto(s) Acabado(s) p/ Incluir Baixa no Estoque - PA Componente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function avancar(id_produto_acabado) {
    window.location = 'incluir.php?passo=2&id_produto_acabado='+id_produto_acabado
}
</Script>
</head>
<body>
<form name='form'>       
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Produto(s) Acabado(s) p/ Incluir Baixa no Estoque - PA Componente
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2' rowspan='2'>
            <font title='Referência / Discriminação' style='cursor:help'>
                Ref / Disc
            </font>
        </td>
        <td rowspan='2'>
            <font title='Operação de Custo' style='cursor:help'>
                O.C.
            </font>
        </td>
        <td rowspan='2'>
            <font title='Unidade' style='cursor:help'>
                Un.
            </font>
        </td>
        <td rowspan='2'>
            Compra<br> Produção
        </td>
        <td colspan='4'>
            Quantidade / Estoque
        </td>
        <td rowspan='2'>
            <font title='Média Mensal de Vendas' style='cursor:help'>
                M.M.V.
            </font>
        </td>
        <td rowspan='2'>
            Prazo de Entrega
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Real
        </td>
        <td>
            Disp.</td>
        <td>
            <font title='Estoque Pendente' style='cursor:help'>
                Pend.
            </font>
        </td>
        <td>
            <font title='Estoque Comprometido' style='cursor:help'>
                Comp.
            </font>
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
            $unidade                = $campos[$i]['sigla'];
            $operacao_custo         = $campos[$i]['operacao_custo'];
            $retorno                = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], 1);
            $quantidade_estoque     = $retorno[0];
            $qtde_pendente          = $retorno[7];
            $est_comprometido       = $retorno[8];
            $producao               = $retorno[2];
            $quantidade_disponivel  = $retorno[3];
//Aki verifica se o PA, possui prazo de Entrega
            $sql = "SELECT prazo_entrega 
                    FROM `estoques_acabados` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
            $campos_prazo_entrega = bancos::sql($sql);
            if(count($campos_prazo_entrega) == 1) {
                $prazo_entrega  = strtok($campos_prazo_entrega[0]['prazo_entrega'], '=');
                $responsavel    = strtok($campos_prazo_entrega[0]['prazo_entrega'], '|');
                $responsavel    = substr(strchr($responsavel, '> '), 1, strlen($responsavel));
                $data_hora      = strchr($campos_prazo_entrega[0]['prazo_entrega'], '|');
                $data_hora      = substr($data_hora, 2, strlen($data_hora));
                $data           = data::datetodata(substr($data_hora, 0, 10), '/');
                $hora           = substr($data_hora, 11, 8);
            }
//Faz esse tratamento para o caso de não encontrar o responsável
            $string_apresentar = (empty($responsavel)) ? '&nbsp;' : 'Responsável: '.$responsavel.' - '.$data.' '.$hora;
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="avancar('<?=$campos[$i]['id_produto_acabado'];?>')" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="avancar('<?=$campos[$i]['id_produto_acabado'];?>')" align='left'>
            <a href='#' class='link'>
            <?
                echo $campos[$i]['referencia'].' / '.intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
                if(!empty($campos[$i]['observacao'])) echo "&nbsp;-&nbsp;<img width='28' height='23' title='".$campos[$i]['observacao']."' src='../../../../imagem/olho.jpg'>";
            ?>
            </a>
        </td>
        <td>
        <?
            if($operacao_custo == 0) {
                echo 'I';
            }else {
                echo 'R';
            }
        ?>
        </td>
        <td>
            <?=$unidade;?>
        </td>
        <td align='right'>
            <?
//Aqui verifica se o PA tem relação com o PI, caso isso não acontece não apresenta o link
                    $sql = "SELECT id_produto_insumo 
                            FROM `produtos_acabados` 
                            WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                            AND `id_produto_insumo` > '0' 
                            AND `ativo` = '1' LIMIT 1 ";
                    $campos_pipa = bancos::sql($sql);
//Aqui o PI em relação com o PA e a OC. é do Tipo Revenda então mostra o link
                    if(count($campos_pipa) == 1 && $operacao_custo == 1) {
            ?>
            <a href="javascript:nova_janela('../../../classes/estoque/compra_producao.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
            <?
                        $compra = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
                        if($compra <> 0 && $producao <> 0) {
                            echo segurancas::number_format($compra, 2, '.').' / '.segurancas::number_format($producao, 2, '.');
                        }else {
                            echo segurancas::number_format($compra, 2, '.').segurancas::number_format($producao, 2, '.');
                        }
            ?>
            </a>
            <?
//Aqui o PI em relação com o PA e a OC. é do Tipo Industrial
                    }else if(count($campos_pipa) == 1 && $operacao_custo == 0) {//Não mostra o link
                        $compra = estoque_acabado::compra_producao($campos[$i]['id_produto_acabado']);
                        if($compra <> 0 && $producao <> 0) {
                            echo segurancas::number_format($compra, 2, '.').' / '.segurancas::number_format($producao, 2, '.');
                        }else {
                            echo segurancas::number_format($compra, 2, '.').segurancas::number_format($producao, 2, '.');
                        }
//Aqui o PA não tem relação com o PI
                    }else {
                        echo segurancas::number_format($producao,2,".");
                    }
            ?>
        </td>
        <td align='right'>
        <?
            if($quantidade_estoque > 0) echo segurancas::number_format($quantidade_estoque,2,".");
        ?>
        </td>
        <td align='right'>
        <?
            if($quantidade_disponivel < 0) {
                echo "<font color='red'>".segurancas::number_format($quantidade_disponivel,2,".")."</font>";
            }else if($quantidade_disponivel > 0) {
                echo segurancas::number_format($quantidade_disponivel,2, ".");
            }
        ?>
        </td>
        <td align='right'>
        <?
/*Jogo o SQL mais acima para verificar por causa de um desvio que não mostrar os valores comprometidos <=0*/
            if($qtde_pendente > 0) echo segurancas::number_format($qtde_pendente, 2, '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($est_comprometido < 0) {
                echo "<font color='red'>".segurancas::number_format($est_comprometido, 2, '.')."</font>";
            }else if ($est_comprometido > 0) {
                echo segurancas::number_format($est_comprometido, 2, '.');
            }
        ?>
        </td>
        <td align='right'>
            <?=  segurancas::number_format($campos[$i]['mmv'], 2, '.');?>
        </td>
        <td title="<?=$string_apresentar;?>" alt="<?=$string_apresentar;?>" align='right'>
            <?=$prazo_entrega;?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class='botao'>
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
?>
<html>
<head>
<title>.:: Consultar Funcionário (Solicitador) p/ Incluir Baixa no Estoque - PA Componente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 4; i++) document.form.opt_opcao2[i].disabled = true
        document.form.txt_consultar2.disabled   = true
        document.form.txt_consultar2.value      = ''
    }else {
        for(i = 0; i < 4 ;i++) document.form.opt_opcao2[i].disabled = false
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
<body onLoad='document.form.txt_consultar2.focus()'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=3';?>" onSubmit="return validar()">
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='passo' value='3'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr class="atencao">
        <td colspan='2' align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
                <b><?=$mensagem[$valor];?></b>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Funcionário (Solicitador) p/ Incluir Baixa no Estoque - PA Componente
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" title="Consultar Funcionário" name="txt_consultar2" size="45" maxlength="45" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao2" value="1" title="Consultar Funcionário por: Nome" onclick='document.form.txt_consultar2.focus()' id='label' checked>
            <label for="label">Nome</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao2" value="2" title="Consultar Funcionário por: Empresa" onclick='document.form.txt_consultar2.focus()' id='label2'>
            <label for="label2">Empresa</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao2" value="3" title="Consultar Funcionário por: Cargo" onclick='document.form.txt_consultar2.focus()' id='label3'>
            <label for="label3">Cargo</label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao2" value="4" title="Consultar Funcionário por: Departamento" onclick='document.form.txt_consultar2.focus()' id='label4'>
            <label for="label4">Departamento</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' title="Consultar todos os funcionários" onclick='limpar()' value='5' class="checkbox" id='label5'>
            <label for="label5">Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false; limpar()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    switch($opt_opcao2) {
        case 1:
            $sql = "SELECT f.id_funcionario, f.nome, e.nomefantasia, c.cargo, d.departamento 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
                    INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento 
                    INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
                    WHERE f.`nome` LIKE '%$txt_consultar2%' 
                    AND f.`status` < '3' ORDER BY f.nome ";
        break;
        case 2:
            $sql = "SELECT f.id_funcionario, f.nome, e.nomefantasia, c.cargo, d.departamento 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa AND e.nomefantasia LIKE '%$txt_consultar2%' 
                    INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento 
                    INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
                    WHERE f.`status` < '3' ORDER BY f.nome ";
        break;
        case 3:
            $sql = "SELECT f.id_funcionario, f.nome, e.nomefantasia, c.cargo, d.departamento 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
                    INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento 
                    INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo AND c.cargo LIKE '%$txt_consultar2%' 
                    WHERE f.`status` < '3' ORDER BY f.nome ";
        break;
        case 4:
            $sql = "SELECT f.id_funcionario, f.nome, e.nomefantasia, c.cargo, d.departamento 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
                    INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento AND d.departamento LIKE '%$txt_consultar2%' 
                    INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
                    WHERE f.`status` < '3' ORDER BY f.nome ";
        break;
        default:
            $sql = "SELECT f.id_funcionario, f.nome, e.nomefantasia, c.cargo, d.departamento 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
                    INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento 
                    INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
                    WHERE f.`status` < '3' ORDER BY f.nome ";
        break;
    }
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir.php?passo=2&id_produto_acabado=<?=$id_produto_acabado;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Funcionário (Solicitador) p/ Incluir Baixa no Estoque - PA Componente ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Funcionário (Solicitador) p/ Incluir Baixa no Estoque - PA Componente
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
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
            $url = "javascript:window.location = 'incluir.php?passo=4&id_produto_acabado=".$id_produto_acabado."&id_func_solic=".$campos[$i]['id_funcionario']."&inicio=".$inicio."&pagina=".$pagina."&txt_consultar2=".$txt_consultar2."&opt_opcao2=".$opt_opcao2."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <a href="<?=$url;?>" title='Visualizar Detalhes de <?=$campos[$i]['nome'];?>' class='link'>
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
        <td colspan='4'>
            <input type='button' name='cmd_consultar' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php?passo=2&id_produto_acabado=<?=$id_produto_acabado;?>'" class='botao'>
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
}else if($passo == 4) {
    //Busco dados do PA passado por parâmetro ...
    $sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.operacao_custo, pa.observacao, u.sigla 
            FROM `produtos_acabados` pa 
            INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
            WHERE pa.`id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
    $campos 		= bancos::sql($sql);
    $referencia 	= $campos[0]['referencia'];
    $discriminacao 	= $campos[0]['discriminacao'];
    $operacao_custo     = $campos[0]['operacao_custo'];
    $observacao_produto = $campos[0]['observacao'];
    $sigla              = $campos[0]['sigla'];
    //Busco dados de Estoque do PA ...
    $sql = "SELECT qtde, qtde_producao, prazo_entrega 
            FROM `estoques_acabados` 
            WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
    $campos_estoque_pa = bancos::sql($sql);
    if(count($campos_estoque_pa) == 0) {
        $estoque_real_inicial   = number_format(0, 2, ',', '.');
        $producao               = 0;
        $string_apresentar      = '&nbsp;';
    }else {
        $estoque_real_inicial   = number_format($campos_estoque_pa[0]['qtde'], 2, ',', '.');
        $producao               = $campos_estoque_pa[0]['qtde_producao'];
        $prazo_entrega          = strtok($campos_estoque_pa[0]['prazo_entrega'], '=');
//Se isso acontecer significa que não tem prazo de entrega e no BD ele grava um espaço para não dar erro
        if(strlen($prazo_entrega) == 1) $prazo_entrega = trim($prazo_entrega);

        $responsavel            = strtok($campos_estoque_pa[0]['prazo_entrega'], '|');
        $responsavel            = substr(strchr($responsavel, '> '), 1, strlen($responsavel));

        $data_hora              = strchr($campos_estoque_pa[0]['prazo_entrega'], '|');
        $data_hora              = substr($data_hora, 2, strlen($data_hora));
        $data                   = data::datetodata(substr($data_hora, 0, 10), '/');
        $hora                   = substr($data_hora, 11, 8);

//Faz esse tratamento para o caso de não encontrar o responsável
        if(empty($responsavel)) {
            $string_apresentar = '&nbsp;';
        }else {
            $string_apresentar = $responsavel.' - '.$data.' '.$hora;
        }
    }
//Aki faz Controle no Rótulo
    $rotulo_oc  = ($operacao_custo == 1) ? 'Revenda' : 'Industrial';
/*Aqui eu verifico a quantidade desse item em Estoque e já trago o status do Estoque para saber se este
pode ser manipulado pelo Estoquista*/
    $vetor              = estoque_acabado::qtde_estoque($id_produto_acabado, 1);
    $qtde_estoque_real	= number_format($vetor[0], 2, ',', '.');
	
    //Busca o nome da pessoa q solicitou ...
    $sql = "SELECT nome 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_GET[id_func_solic]' LIMIT 1 ";
    $campos_funcionario = bancos::sql($sql);
    $solicitador        = $campos_funcionario[0]['nome'];
?>
<html>
<title>.:: Incluir Baixa no Estoque - PA Componente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body onload="calcular();travar_qtde();document.form.txt_retirado_por.focus()">
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=5';?>" onSubmit="return validar()">
<input type='hidden' name='hdd_produto_acabado' value='<?=$_GET['id_produto_acabado'];?>'>
<input type='hidden' name='hdd_func_solic' value='<?=$_GET['id_func_solic'];?>'>
<input type='hidden' name='passo'>
<input type='hidden' name='controle'>
<table border='0' width='60%' align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Baixa no Estoque - PA Componente
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Ref: </b><?=$referencia;?> - <b>Discriminação: </b><?=$discriminacao;?>
        </td>
        <td>
            <font color='blue'>
                <b>O.C:</b>
            </font>
            <?=$rotulo_oc;?>
        </td>
    </tr>
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
            <input type="text" name="txt_retirado_por" value="<?=$txt_retirado_por;?>" title="Digite o nome da pessoa que esta retirando" size="35" class="caixadetexto">
            &nbsp;
            <input type='button' name='cmd_baixas_manip' value='Baixas / Manipulações' title='Baixas / Manipulações' onclick="nova_janela('../manipular_estoque/consultar.php?passo=1&opt_opcao=1&txt_referencia=<?=$referencia;?>&chkt_mostrar_componentes=1&pop_up=1', 'CONSULTAR', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:brown; font-weight: bold' class='botao'>
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
            <input type="text" name="txt_quantidade" value="<?=$txt_quantidade;?>" title="Digite a quantidade" size="12" onkeyup="verifica(this, 'moeda_especial', 2, '', event);calcular()" class="caixadetexto">
            &nbsp;
            <input type="text" name="txt_qtde_sigla" value="<?=$txt_qtde_sigla;?>" title="Quantidade em <?=$sigla;?>" size="12" class="textdisabled" disabled>
            <?=$sigla;?>
        </td>
        <td>
            <input type="text" name="txt_qtde_calculada" size="10" class="textdisabled" disabled> <?=$sigla;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Quantidade em Estoque:
        </td>
        <td>
            Nova Qtde em Estoque
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type="text" name="txt_qtde_estoque" value="<?=$qtde_estoque_real;?>" title="Quantidade em Estoque" class="textdisabled" disabled>
        </td>
        <td>
            <input type="text" name="txt_nova_qtde_estoque" title="Nova Qtde em Estoque" size="20" class="textdisabled" disabled>
        </td>
    </tr>
</table>
<!--************************Novo Controle com a Parte de OP(s)************************-->
<?
    $indice_linha =  0;//Será utilizado + abaixo em JavaScript ...
/*********************Esse trecho de código é independente de qualquer Etapa*********************/
/*Aqui eu seleciono todas as OP(s) que estão relacionadas a esse Produto Acabado ...*/
    $sql = "SELECT DISTINCT(id_op) 
            FROM `baixas_ops_vs_pas` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' ";
    $campos_op = bancos::sql($sql);
    $linhas_op = count($campos_op);
    for($i = 0; $i < $linhas_op; $i++) {
//Busca do último Status de Baixa referente a OP ...
        $sql = "SELECT status 
                FROM `baixas_ops_vs_pas` 
                WHERE `id_op` = '".$campos_op[$i]['id_op']."' ORDER BY id_baixa_op_vs_pa DESC LIMIT 1 ";
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
    $total_registros = 0;//Acumula o Total encontrado em todas as Etapas ...
/********************************************************************/
/******************************7ª Etapa******************************/
/*Busca os P.A(s) que estão atrelados ao custo da Sétima Etapa através desse Produto Acabado
e claro a P.A(s) que estejam vinculados a OP(s) mais que estejam em aberto*/
    $sql = "SELECT DISTINCT(ops.id_op), pac.id_produto_acabado, pp.qtde 
            FROM `pacs_vs_pas` pp 
            INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
            INNER JOIN `ops` ON ops.`id_produto_acabado` = pac.`id_produto_acabado` AND ops.`status_finalizar` = '0' AND ops.`ativo` = '1' AND ops.`id_op` $comparador ($vetor_ops) 
            WHERE pp.`id_produto_acabado` = '$id_produto_acabado' ORDER BY pp.id_pac_pa ";
    $campos7 = bancos::sql($sql);
    $linhas7 = count($campos7);
    $total_registros+= $linhas7;
    if($total_registros > 0) {
?>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            OP(s) em Aberto
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
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
            <font title='Quantidade à Produzir' style="cursor:help">
                Qtde Prod
            </font>
        </td>
        <td>
            <font title='Quantidade do Produto Acabado' style="cursor:help">
                Qtde PA
            </font>
        </td>
        <td>
            <font title='Necessidade Atual' style="cursor:help">
                Nec. Atual
            </font>
        </td>
    </tr>
<?
/**************************************Listando os PA(s) da 7ª Etapa**************************************/
        for($i = 0; $i < $linhas7; $i++) {
?>
	<tr class='linhanormal' align='center'>
            <td>
                <input type="text" name="txt_qtde[]" size="10" maxlength="8" onkeyup="verifica(this, 'moeda_especial', 2, '', event);quantidade_calculada()" class="caixadetexto">
            </td>
            <td>
                <a href="javascript:copiar_qtde('<?=$indice_linha;?>')" title="Copiar Necessidade Atual" style="cursor:help" class='link'>
                    <?=$campos7[$i]['id_op'];?>
                </a>
                <input type="hidden" name="hdd_op[]" value="<?=$campos7[$i]['id_op'];?>">
            </td>
            <td align='left'>
                <a href="javascript:nova_janela('../../../producao/ops/detalhes.php?id_op=<?=$campos7[$i]['id_op']?>', 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c')" title="Detalhes de OP" style="cursor:help" class='link'>
                    <?=intermodular::pa_discriminacao($campos7[$i]['id_produto_acabado'], 0, 0, 0, 0, 1);?>
                </a>
            </td>
            <td align='right'>
            <?
//Busco a Quantidade a Produzir da OP corrente ...
                $sql = "SELECT qtde_produzir 
                        FROM `ops` 
                        WHERE `id_op` = '".$campos7[$i]['id_op']."' LIMIT 1 ";
                $campos_quantidade  = bancos::sql($sql);
                $qtde_produzir      = $campos_quantidade[0]['qtde_produzir'];
/*Aqui nesse SQL, eu busco tudo o que foi produzido daquela OP que está em aberto para 
aquele PA que está sendo acessado ...*/
                $sql = "SELECT SUM(bmp.qtde) AS qtde_produzido 
                        FROM `baixas_manipulacoes_pas` bmp 
                        INNER JOIN `baixas_ops_vs_pas` bop ON bop.id_baixa_manipulacao_pa = bmp.id_baixa_manipulacao_pa 
                        INNER JOIN `ops` ON ops.id_op = bop.id_op AND ops.status_finalizar = '0' AND ops.ativo = '1' AND ops.id_produto_acabado = '$id_produto_acabado' 
                        WHERE bop.`id_op` = '".$campos7[$i]['id_op']."' ";
                $campos_producao = bancos::sql($sql);//pego tudo q foi produzido até agora deste produto
                if(count($campos_producao) > 0) {
                    $qtde_baixada = $campos_producao[0]['qtde_produzido'];
                }else {
                    $qtde_baixada = 0;
                }
                $producao = $qtde_produzir + $qtde_baixada;
                echo number_format($producao, 2, ',', '.');
            ?>
            </td>
            <td align='right'>
                <?=number_format($campos7[$i]['qtde'], $num_casas, ',', '.');?>
            </td>
            <td align='right'>
            <?
                $nec_atual7 = $campos7[$i]['qtde'] * $producao;
                if($nec_atual7 != 0) echo segurancas::number_format($nec_atual7, 2, '.').' '.$campos[0]['sigla'];
            ?>
                <input type="hidden" name="txt_nec_atual[]" value="<?=number_format($nec_atual7, 2, ',', '.');?>">
            </td>
	</tr>
<?
            $indice_linha++;
        }
?>
</table>
<?
    }
?>
<!--**********************************************************************************-->
<table border="0" width='60%' align='center' cellspacing ='1' cellpadding='1'>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Observação:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name='txt_observacao' rows='3' cols='85' maxlength='255' class="caixadetexto"></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Observação do Produto:</b>
            <?=$observacao_produto;?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php?passo=3&inicio=<?=$inicio;?>&pagina=<?=$pagina;?>&txt_consultar2=<?=$txt_consultar2;?>&opt_opcao2=<?=$opt_opcao2;?>&id_produto_acabado=<?=$id_produto_acabado;?>'" class='botao'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');calcular();travar_qtde();document.form.txt_retirado_por.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
<pre>
<font color="darkblue">
* Na exibição da(s) OP(s) em Aberto, só está levando em conta o(s) PA(s) que estão atrelado(s) à 7ª Etapa do Custo.
</font>
</pre>
</body>
<Script Language = 'Javascript'>
function igualar() {
    if(document.form.chkt_mesmo.checked == true) {
        document.form.txt_retirado_por.value = '<?=trim($solicitador);?>'
    }else {
        document.form.txt_retirado_por.value = ''
    }
}

function copiar_qtde(indice) {
    var elementos = document.form.elements
//Somente em alguns P.A.(s) que exibirá esse objeto que é o checkbox ...
    var objetos_inicio  = 12//Qtde de Objetos antes do Loop
    var objetos_linha   = 3//Qtde de Objetos por Loop
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

function calcular() {
    if(document.form.txt_quantidade.value == '-') document.form.txt_quantidade.value = ''

    var qtde_estoque    = eval(strtofloat(document.form.txt_qtde_estoque.value))
    var quantidade      = eval(strtofloat(document.form.txt_quantidade.value))

    if(typeof(quantidade) == 'undefined') {
        document.form.txt_quantidade.value          = ''
        document.form.txt_nova_qtde_estoque.value   = ''
    }else {
        document.form.txt_qtde_sigla.value          = document.form.txt_quantidade.value
        document.form.txt_nova_qtde_estoque.value   = qtde_estoque - quantidade
        document.form.txt_nova_qtde_estoque.value   = arred(document.form.txt_nova_qtde_estoque.value, 2, 1)
    }
}

//Função que é solicitada quando carrega a Tela ...
function travar_qtde() {
    qtde_ops = eval('<?=$total_registros;?>')
//Caso exista alguma OP na Tela de Baixa de Estoque, então eu travo a caixa de Qtde ...

//No momento está desativada devido o roberto não ter uma lógica definitiva ainda ... 	
    /*if(qtde_ops > 0) {
        document.form.txt_quantidade.className = 'textdisabled'
        document.form.txt_quantidade.disabled = true
    }*/
}

//Nessa função eu faço um somatório de todas as Qtdes dadas em OP (Baixas) ...
function quantidade_calculada() {
    var elementos               = document.form.elements
    var quantidade_calculada    = 0
//Faço assim porque essa variável é utilizada em uma divisão e assim não dá erro de Divisão por Zero ...
    if(typeof(densidade_aco) == 'undefined') densidade_aco = 1
//Disparo do Loop ...
    for(var i = 0; i < elementos.length; i++) {
//Controle com os Qtde(s) da Primeira, Segunda, Terceira e Sétima Etapa p/ poder gravar no BD ...
        if(elementos[i].name == 'txt_qtde[]' && elementos[i].value != '') quantidade_calculada+= eval(strtofloat(elementos[i].value))
    }
    document.form.txt_qtde_calculada.value = quantidade_calculada
    document.form.txt_qtde_calculada.value = arred(document.form.txt_qtde_calculada.value, 2, 1)
}

function submeter() {
    document.form.controle.value    = 1
    document.form.passo.value       = 4
    document.form.submit()
}

function validar() {
/***************************************************************************/
//Variáveis que serão utilizadas mais abaixo ...
    var elementos   = document.form.elements
    var qtde_ops    = eval('<?=$total_registros;?>')
/***************************************************************************/
//Retirado por
    if(!texto('form', 'txt_retirado_por', '1', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'RETIRADO POR', '2')) {
        return false
    }
//Quantidade ...
    if(!texto('form', 'txt_quantidade', '1', '1234567890.,', 'QUANTIDADE', '1')) {
        return false
    }
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
        if(document.form.txt_qtde_calculada.value != '' && document.form.txt_qtde_calculada.value != '0,00') ops_preenchidas++
//Verificação referente ao preenchimento dos campos de baixa ...
        if(ops_preenchidas == 0 && document.form.txt_quantidade.value == '') {
            alert('DIGITE UMA QUANTIDADE OU PREENCHA ALGUM CAMPO DE OP !')
            document.form.txt_quantidade.focus()
            return false
        }
    }
/**********************************Controles*****************************************/
//Comparação da Quantidade Digitada pelo Usuário com a Qtde em Kilos ou em Metros ...
    var quantidade              = eval(strtofloat(document.form.txt_quantidade.value))
    var quantidade_calculada    = eval(strtofloat(document.form.txt_qtde_calculada.value))

    if(quantidade != quantidade_calculada) {
        var resposta1 = confirm('A QTDE DIGITADA ESTÁ INCOMPATÍVEL COM A QUANTIDADE CALCULADA !!!\nDESEJA CONTINUAR ?')
        if(resposta1 == false) return false
    }
/************************************************************************************/
//Comparação da Quantidade Solicitada com o Saldo Disponível em Estoque ...
    var qtde_estoque = eval(strtofloat('<?=$qtde_estoque_real;?>'))
    var quantidade = eval(strtofloat(document.form.txt_quantidade.value))
    if(quantidade > qtde_estoque) {
        alert('A QUANTIDADE SOLICITADA É MAIOR DO QUE O SALDO EM ESTOQUE ! ')
        document.form.txt_quantidade.focus()
        document.form.txt_quantidade.select()
        return false
    }
/*************************Novo Controle com a Parte de OP(s)*************************/
	
//Caso exista alguma OP na Tela de Baixa de Estoque ...
//Não trava mais temporariamente, então eu travo a caixa de Qtde, não definiu a lógica ainda ...
    if(qtde_ops > 0) {
        //document.form.txt_quantidade.value = ''//Limpo a caixa Qtde por garantia, macete ...
        for(var i = 0; i < elementos.length; i++) {
//Tratamento com as Qtde(s) da 7ª Etapa p/ poder gravar no BD ...
            if(elementos[i].name == 'txt_qtde[]') elementos[i].value = strtofloat(elementos[i].value)
        }
/*
//Comentado temporariamente devido ter mudado a lógica ...
//Caso exista alguma OP na Tela de Baixa de Estoque ...
        if(qtde_ops > 0) {
//Se o campo Qtde estiver vazio, então igualo esse campo ao campo de Qtde Calculada ...
            if(document.form.txt_quantidade.value == '' || document.form.txt_quantidade.value == '0,00') {
                document.form.txt_quantidade.value = document.form.txt_qtde_calculada.value
            }
        }
*/
    }
/************************************************************************************/		
//Desabilita p/ poder gravar no BD ...
    document.form.txt_quantidade.disabled = false
//Para o passo q vai seguir ...
    document.form.passo.value = 5
//Aqui eu desabilito o botão Salvar p/ não acontecer de o usuário clicar várias vezes ...
    document.form.cmd_salvar.disabled = true
//Tratamento p/ poder gravar esses 2 campos no Banco ...
    return limpeza_moeda('form', 'txt_quantidade, ')
}
</Script>
</html>
<?
}else if($passo == 5) {
/************************************************************************************/
//Verifico se a Sessão não caiu ...
    if (!(session_is_registered('id_funcionario'))) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../../html/index.php?valor=1'
    </Script>
<?
        exit;
    }
/************************************************************************************/
//Procedimento normal
    $data_sys       = date('Y-m-d H:i:s');
    $qtde_gravar    = $_POST['txt_quantidade'] * (-1);
//Inserindo os Dados no BD ...
    $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `retirado_por`, `qtde`, `observacao`, `acao`, `status`, `data_sys`) values (NULL, '$_POST[hdd_produto_acabado]', '$_SESSION[id_funcionario]', '$_POST[hdd_func_solic]', '$_POST[txt_retirado_por]', '$qtde_gravar', '$_POST[txt_observacao]', 'B', '1', '$data_sys') ";
    bancos::sql($sql);
    $id_baixa_manipulacao_pa = bancos::id_registro();
    estoque_acabado::manipular($_POST[hdd_produto_acabado], $qtde_gravar);
    estoque_acabado::qtde_estoque($_POST[hdd_produto_acabado], 1);
//************************Novo Controle com a Parte de OP(s)************************
    foreach($_POST['txt_qtde'] as $i => $qtde) {
        //Se a qtde estiver preenchida ...
        if($qtde != '' && $qtde != '0.00') {
            $sql = "INSERT INTO `baixas_ops_vs_pas` (`id_baixa_op_vs_pa`, `id_produto_acabado`, `id_op`, `id_baixa_manipulacao_pa`, `qtde_baixa`, `data_sys`, `status`) VALUES (NULL, '$_POST[hdd_produto_acabado]', '".$_POST['hdd_op'][$i]."', '$id_baixa_manipulacao_pa', '$qtde', '$data_sys', '2') ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php<?=$parametro;?>&passo=1&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Acabado(s) p/ Incluir Baixa no Estoque - PA Componente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.className   = 'textdisabled'
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
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
            Consultar Produto(s) Acabado(s) p/ Incluir Baixa no Estoque - PA Componente
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size='45' maxlength='45' class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar Produtos Insumos por: Referência" onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>
                Referência
            </label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" title="Consultar Produtos Insumos por: Discriminação" onclick='document.form.txt_consultar.focus()' id='label2'>
            <label for='label2'>
                Discriminação
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="3" title="Consultar Produtos Insumos por: Observação" onclick='document.form.txt_consultar.focus()' id='label3'>
            <label for='label3'>Observação</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os Produtos Insumos" id="todos" onClick='limpar()' class="checkbox">
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>