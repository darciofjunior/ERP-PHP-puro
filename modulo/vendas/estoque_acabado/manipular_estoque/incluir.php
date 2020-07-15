<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>MANIPULAÇÃO INCLUIDA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>PRODUTO BLOQUEADO !!! ESTÁ SENDO MANIPULADO PELO ESTOQUISTA !.</font>";
$mensagem[4] = "<font class='erro'>ESTOQUE DISPONÍVEL FINAL NÃO PODE SER MENOR DO QUE ZERO !.</font>";

/*Significa q está sendo acessado do Mód. de Compras, então só mostra P.A. do Tipo Componentes*/
if($veio_compras == 1) {
    $condicao = " AND fm.`id_familia` = '23' ";
}else {//Aqui está sendo acessado do Mód. de Vendas, então pode mostrar todos os P.A.
    if(empty($chkt_mostrar_componentes)) $condicao = " AND gpa.`id_familia` <> '23' ";
}
if($cmb_opcao_entrada != '') $condicao_entrada = " AND `acao` = '$cmb_opcao_entrada' ";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $cmb_tipo_manipulacao = $_POST['cmb_tipo_manipulacao'];
    }else {
        $cmb_tipo_manipulacao = $_GET['cmb_tipo_manipulacao'];
    }
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.operacao_custo, pa.mmv, pa.observacao, u.sigla 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa $condicao 
                    WHERE pa.referencia LIKE '%$txt_consultar%' AND pa.ativo = '1' 
                    $condicao_entrada ORDER BY pa.discriminacao ";
        break;
        case 2:
            $sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.operacao_custo, pa.mmv, pa.observacao, u.sigla 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa $condicao 
                    WHERE pa.discriminacao LIKE '%$txt_consultar%' AND pa.ativo = '1' 
                    $condicao_entrada ORDER BY pa.discriminacao ";
        break;
        case 3:
            $sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.operacao_custo, pa.mmv, pa.observacao, u.sigla 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.nome LIKE '%$txt_consultar%' $condicao 
                    WHERE pa.ativo = '1' 
                    $condicao_entrada ORDER BY pa.discriminacao ";
        break;
        default:
            $sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.operacao_custo, pa.mmv, pa.observacao, u.sigla 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                    INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa $condicao 
                    WHERE pa.ativo = '1' 
                    $condicao_entrada ORDER BY pa.discriminacao ";
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
<title>.:: Incluir Manipulação de Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function avancar(id_produto_acabado, status_estoque, tipo_manipulacao) {
    if(status_estoque == 0) {//Produto Acabado Liberado ...
        /*Se o Tipo de Manipulação for de Uso p/ Fábrica, então antes será necessário selecionar um solicitador para 
        depois ser feita a Manipulação ...*/
        if(tipo_manipulacao == 'U') {//Uso p/ Fábrica
            window.location = 'incluir.php?passo=2&id_produto_acabado='+id_produto_acabado+'&tipo_manipulacao='+tipo_manipulacao
        }else {//OC ...
            window.location = 'incluir.php?passo=4&id_produto_acabado='+id_produto_acabado+'&tipo_manipulacao='+tipo_manipulacao
        }
    }else {//Produto Acabado Bloqueado ...
        alert('ESTE PRODUTO ACABADO ESTÁ BLOQUEADO !!!')
    }
}
</Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Incluir Manipulação no Estoque
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
            Compra<br/> Produção
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
            Disp.
        </td>
        <td>
            <font title='Pendência' style='cursor:help'>
                Pend.
            </font>
        </td>
        <td>
            <font title='Comprometido' style='cursor:help'>
                Comp.
            </font>
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
            $retorno                = estoque_acabado::qtde_estoque($campos[$i]['id_produto_acabado'], 1);
            $quantidade_estoque     = $retorno[0];
            $status_estoque         = $retorno[1];
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
                $prazo_entrega 	= strtok($campos_prazo_entrega[0]['prazo_entrega'], '=');
                $responsavel 	= strtok($campos_prazo_entrega[0]['prazo_entrega'], '|');
                $responsavel 	= substr(strchr($responsavel, '> '), 1, strlen($responsavel));
                $data_hora      = strchr($campos_prazo_entrega[0]['prazo_entrega'], '|');
                $data_hora      = substr($data_hora, 2, strlen($data_hora));
                $data           = data::datetodata(substr($data_hora, 0, 10), '/');
                $hora           = substr($data_hora, 11, 8);
            }
            //Faz esse tratamento para o caso de não encontrar o responsável ...
            $string_apresentar = (empty($responsavel)) ? '&nbsp;' : 'Responsável: '.$responsavel.' - '.$data.' '.$hora;
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="avancar('<?=$campos[$i]['id_produto_acabado'];?>', '<?=$status_estoque;?>', '<?=$cmb_tipo_manipulacao;?>')" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <a href="javascript:avancar('<?=$campos[$i]['id_produto_acabado'];?>', '<?=$status_estoque;?>', '<?=$cmb_tipo_manipulacao;?>')" class='link'>
            <?
                echo $campos[$i]['referencia'].' / '.intermodular::pa_discriminacao($campos[$i]['id_produto_acabado']);
                if(!empty($campos[$i]['observacao'])) echo "&nbsp;-&nbsp;<img width='28' height='23' title='".$campos[$i]['observacao']."' src='../../../imagem/olho.jpg'>";
            ?>
            </a>
        </td>
        <td>
        <?
            if($campos[$i]['operacao_custo'] == 0) {
                echo 'I';
            }else {
                echo 'R';
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align='right'>
        <?
            //Aqui verifica se o PA tem relação com o PI, caso isso não acontece não apresenta o link
            $sql = "SELECT `id_produto_insumo` 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
                    AND `id_produto_insumo` > '0' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
                //Aqui o PI em relação com o PA e a OC. é do Tipo Revenda então mostra o link
            if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 1) {
        ?>
        <a href="javascript:nova_janela('../../../classes/estoque/compra_producao.php?id_produto_acabado=<?=$campos[$i]['id_produto_acabado'];?>', 'pop', '', '', '', '', '580', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Compra Produção' class='link'>
        <?
/****************Compra****************/
            if($font_compra == "<font color='black'>") $font_compra = "<font color='#6473D4'>";//Se link, exibe em Azul ...
                echo $font_compra.number_format($compra, 2, ',', '.');
/****************Produção****************/					
                if(!empty($producao) && $producao != 0) {
                    if($font_producao == "<font color='black'>") $font_producao = "<font color='#6473D4'>";//Se link, exibe em Azul ...
                    echo ' / '.$font_producao.number_format($producao, 2, ',', '.');
                }
        ?>
        </a>
<?
//Aqui o PI em relação com o PA e a OC. é do Tipo Industrial
            }else if(count($campos_pipa) == 1 && $campos[$i]['operacao_custo'] == 0) {//Não mostra o link
/****************Compra****************/					
                echo $font_compra.number_format($compra, 2, ',', '.');
/****************Produção****************/
                if(!empty($producao) && $producao != 0) echo ' / '.$font_producao.number_format($producao, 2, ',', '.');
            }else {//Aqui o PA não tem relação com o PI
/****************Produção****************/
                echo $font_producao.number_format($producao, 2, ',', '.');
            }
            //$retorno_pas_atrelados          = intermodular::calculo_producao_mmv_estoque_pas_atrelados($campos[$i]['id_produto_acabado']);
            $font_compra_producao_atrelado  = ($retorno_pas_atrelados['total_compra_producao_pas_atrelados'] < -$retorno_pas_atrelados['total_ec_pas_atrelados']) ? 'red' : 'black';

            echo '<br><font color="'.$font_compra_producao_atrelado.'" title="Somatória dos PAs Atrelados" style="cursor:help">Atrel = '.number_format($retorno_pas_atrelados['total_compra_producao_pas_atrelados'], 0, '', '.').'</font>';
            echo $qtde_oe_em_aberto;
        ?>
        </td>
        <td align='right'>
        <?
            if($quantidade_estoque > 0) echo segurancas::number_format($quantidade_estoque, 2, '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($quantidade_disponivel < 0) {
                echo "<font color='red'>".segurancas::number_format($quantidade_disponivel, 2, '.')."</font>";
            }else if($quantidade_disponivel > 0) {
                echo segurancas::number_format($quantidade_disponivel, 2, '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
            //Jogo o SQL mais acima para verificar por causa de um desvio que não mostrar os valores comprometidos <=0 ...
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
            <?=number_format($campos[$i]['mmv'], 2, ',', '.');?>
        </td>
        <td align='right' title="<?=$string_apresentar;?>" alt="<?=$string_apresentar;?>">
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
<title>.:: Incluir Manipulação de Estoque ::.</title>
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
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=3';?>" onSubmit="return validar()">
<input type='hidden' name='id_produto_acabado' value='<?=$_GET['id_produto_acabado'];?>'>
<input type='hidden' name='tipo_manipulacao' value='<?=$_GET['tipo_manipulacao'];?>'>
<input type='hidden' name='passo' value='3'>
<table border='0' width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr class="atencao">
        <td colspan='2' align='center'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-1'>
                <b><?=$mensagem[$valor];?></b>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Funcionário (Solicitador)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' title="Consultar Funcionário" name="txt_consultar2" size="45" maxlength="45" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type='radio' name="opt_opcao2" value="1" title="Consultar Funcionário por: Nome" onclick="document.form.txt_consultar.focus()" id='label' checked>
            <label for="label">Nome</label>
        </td>
        <td width="20%">
            <input type='radio' name="opt_opcao2" value="2" title="Consultar Funcionário por: Empresa" onclick="document.form.txt_consultar.focus()" id='label2'>
            <label for="label2">Empresa</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type='radio' name="opt_opcao2" value="3" title="Consultar Funcionário por: Cargo" onclick="document.form.txt_consultar.focus()" id='label3'>
            <label for="label3">Cargo</label>
        </td>
        <td width="20%">
            <input type='radio' name="opt_opcao2" value="4" title="Consultar Funcionário por: Departamento" onclick="document.form.txt_consultar.focus()" id='label4'>
            <label for="label4">Departamento</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' title="Consultar todos os funcionários" onclick='limpar()' value='5' class='checkbox' id='label5'>
            <label for="label5">Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
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
    switch($opt_opcao2) {
        case 1:
            $sql = "SELECT f.id_funcionario, f.nome, f.rg, f.codigo_barra, f.ddd_residencial, f.telefone_residencial, e.nomefantasia, c.cargo, d.departamento 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
                    INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento 
                    INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
                    WHERE f.`nome` LIKE '%$txt_consultar2%' 
                    AND f.`status` < '3' ORDER BY f.nome ";
        break;
        case 2:
            $sql = "SELECT f.id_funcionario, f.nome, f.rg, f.codigo_barra, f.ddd_residencial, f.telefone_residencial, e.nomefantasia, c.cargo, d.departamento 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa AND e.nomefantasia LIKE '%$txt_consultar2%' 
                    INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento 
                    INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
                    WHERE f.`status` < '3' ORDER BY f.nome ";
        break;
        case 3:
            $sql = "SELECT f.id_funcionario, f.nome, f.rg, f.codigo_barra, f.ddd_residencial, f.telefone_residencial, e.nomefantasia, c.cargo, d.departamento 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
                    INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento 
                    INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo AND c.`cargo` LIKE '%$txt_consultar2%' 
                    WHERE f.`status` < '3' ORDER BY f.nome ";
        break;
        case 4:
            $sql = "SELECT f.id_funcionario, f.nome, f.rg, f.codigo_barra, f.ddd_residencial, f.telefone_residencial, e.nomefantasia, c.cargo, d.departamento 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
                    INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento AND d.`departamento` LIKE '%$txt_consultar2%' 
                    INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
                    WHERE f.`status` < '3' ORDER BY f.nome ";
        break;
        default:
            $sql = "SELECT f.id_funcionario, f.nome, f.rg, f.codigo_barra, f.ddd_residencial, f.telefone_residencial, e.nomefantasia, c.cargo, d.departamento 
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
            window.location = 'incluir.php?passo=2&id_produto_acabado=<?=$id_produto_acabado;?>&tipo_manipulacao=<?=$tipo_manipulacao?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Incluir Manipulação de Estoque ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' src = '../../../../js/tabela.js'></Script>
</head>
<body>
<input type='hidden' name='id_produto_acabado' value='<?=$_POST['id_produto_acabado'];?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Consultar Funcionário(s) - (Solicitador)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Cód.
        </td>
        <td>
            Nome
        </td>
        <td>
            RG
        </td>
        <td>
            Telefone
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
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=$campos[$i]['codigo_barra'];?>
        </td>
        <td align='left'>
            <a href="incluir.php?passo=4&id_produto_acabado=<?=$_POST['id_produto_acabado'];?>&tipo_manipulacao=<?=$_POST['tipo_manipulacao'];?>&id_func_solic=<?=$campos[$i]['id_funcionario'];?>&inicio=<?=$inicio;?>&pagina=<?=$pagina;?>&txt_consultar2=<?=$txt_consultar2;?>&opt_opcao2=<?=$opt_opcao2;?>" class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['rg'];?>
        </td>
        <td>
            <?=$campos[$i]['ddd_residencial'].' '.$campos[$i]['telefone_residencial'];?>
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
        <td colspan='7'>
            <input type='button' name="cmd_consultar" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php?passo=2&id_produto_acabado=<?=$_POST['id_produto_acabado'];?>&tipo_manipulacao=<?=$_POST['tipo_manipulacao']?>'" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
	}
}else if($passo == 4) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $id_produto_acabado = $_POST['id_produto_acabado'];
        $tipo_manipulacao   = $_POST['tipo_manipulacao'];
        $id_func_solic      = $_POST['id_func_solic'];
    }else {
        $id_produto_acabado = $_GET['id_produto_acabado'];
        $tipo_manipulacao   = $_GET['tipo_manipulacao'];
        $id_func_solic      = $_GET['id_func_solic'];
    }
//Busco dados do Produto Acabado passado por parâmetro ...
    $sql = "SELECT pa.id_produto_acabado, pa.referencia, pa.discriminacao, pa.operacao_custo, pa.observacao, u.unidade 
            FROM `produtos_acabados` pa 
            INNER JOIN `unidades` u ON u.id_unidade = pa.id_unidade 
            WHERE pa.`id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $unidade            = $campos[0]['unidade'];
    $referencia         = $campos[0]['referencia'];
    $discriminacao      = $campos[0]['discriminacao'];
    $operacao_custo     = $campos[0]['operacao_custo'];
    $observacao_produto = $campos[0]['observacao'];
    //Busco dados de Estoque do Produto Acabado passado por parâmetro ...
    $sql = "SELECT qtde, qtde_producao, prazo_entrega 
            FROM `estoques_acabados` 
            WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
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
        $string_apresentar = (empty($responsavel)) ? '&nbsp;' : $responsavel.' - '.$data.' '.$hora;
    }
    $compra             = estoque_acabado::compra_producao($id_produto_acabado);
    $compra_producao    = number_format($producao + $compra, 2, ',', '.');

//Aki faz Controle no Rótulo
    if($operacao_custo == 1) {//Revenda
        $rotulo_oc = 'Revenda';
    }else {//Industrial
        $rotulo_oc = 'Industrial';
    }
//Aqui eu verifico a quantidade desse item em Estoque e já trago o status do Estoque para saber se este pode ser manipulado pelo Estoquista
    $vetor                  = estoque_acabado::qtde_estoque($_GET['id_produto_acabado'], 1);
    $qtde_estoque_real      = $vetor[0];
    $status_estoque         = $vetor[1];
    $qtde_producao          = $vetor[2];
    $estoque_disponivel     = $vetor[3];
    $total_separado         = $vetor[4] - $vetor[6];
    $racionado              = $vetor[5];
    $estoque_comprometido   = $vetor[8];
?>
<html>
<title>.:: Incluir Manipulação de Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function submeter() {
    document.form.controle.value    = 1
    document.form.passo.value       = 4
    document.form.submit()
}

function calcular() {
    if(document.form.txt_lancamento_estoque.value == '-')   document.form.txt_lancamento_estoque.value = ''
    if(document.form.txt_lancamento_producao.value == '-')  document.form.txt_lancamento_producao.value = ''

    var lancamento_estoque  = eval(strtofloat(document.form.txt_lancamento_estoque.value))
    var lancamento_producao = eval(strtofloat(document.form.txt_lancamento_producao.value))

    var estoque_real_inicial = eval(strtofloat(document.form.txt_estoque_real_inicial.value))
    var producao_inicial    = eval(strtofloat(document.form.txt_producao_inicial.value))
//Parte de Estoque
    if(typeof(lancamento_estoque) == 'undefined') {
        document.form.txt_estoque_real_final.value = '<?=$estoque_real_inicial;?>'
    }else {
        document.form.txt_estoque_real_final.value = estoque_real_inicial + lancamento_estoque
        document.form.txt_estoque_real_final.value = arred(document.form.txt_estoque_real_final.value, 2, 1)
    }
//Parte de Produção
    if(typeof(lancamento_producao) == 'undefined') {
        document.form.txt_producao_final.value = '<?=$compra_producao;?>'
    }else {
        document.form.txt_producao_final.value = producao_inicial + lancamento_producao
        document.form.txt_producao_final.value = arred(document.form.txt_producao_final.value, 2, 1)
    }
}

function validar() {
/**************************************************************************************************/
/******************************Somente quando estou fazendo a ação de OC***************************/
/**************************************************************************************************/
    if(typeof(document.form.txt_numero_oc) == 'object') {
//Número da OC ...
        if(!texto('form', 'txt_numero_oc', '1', '1234567890', 'NÚMERO DA OC', '2')) {
            return false
        }
//Cliente ...	
        if(!texto('form', 'txt_cliente', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'CLIENTE', '2')) {
            return false
        }
    }
/**************************************************************************************************/
//Lançamento em Estoque ...
    if(!texto('form', 'txt_lancamento_estoque', '1', '1234567890,.-', 'LANÇAMENTO EM ESTOQUE', '2')) {
        return false
    }
//Observação ...
    if(document.form.txt_observacao.value == '') {
        alert('DIGITE A OBSERVAÇÃO !')
        document.form.txt_observacao.focus()
        return false
    }
    if(document.form.txt_observacao.value.length < 7) {
        alert('OBSERVAÇÃO INCOMPLETA !')
        document.form.txt_observacao.focus()
        return false
    }
    var lancamento_estoque      = eval(strtofloat(document.form.txt_lancamento_estoque.value))
    var estoque_real_inicial    = eval(strtofloat('<?=$estoque_real_inicial;?>'))
    var estoque_real_final      = eval(strtofloat(document.form.txt_estoque_real_final.value))
    var total_separado          = eval(strtofloat(document.form.txt_total_separado.value))

    if(estoque_real_final < total_separado) {
        alert('ESTOQUE REAL FINAL NÃO PODE SER MENOR DO QUE O TOTAL SEPARADO !')
        document.form.txt_lancamento_estoque.focus()
        document.form.txt_lancamento_estoque.select()
        return false
    }

    if(estoque_real_inicial + lancamento_estoque < 0) {
        alert('O LANÇAMENTO EM ESTOQUE É MAIOR DO QUE O ESTOQUE INICIAL !')
        document.form.txt_lancamento_estoque.focus()
        document.form.txt_lancamento_estoque.select()
        return false
    }
//Desabilito o botão de Salvar, p/ evitar de o usuário enviar as informações + de 1 vez p/ o Servidor
    document.form.cmd_salvar.disabled = true
    document.form.passo.value = 5//Passo aonde grava os dados
    return limpeza_moeda('form', 'txt_lancamento_estoque, txt_lancamento_producao, ')
}

function controlar_foco() {
    if(document.form.txt_lancamento_estoque.disabled == false) {
        document.form.txt_lancamento_estoque.focus()
    }else {
        document.form.txt_lancamento_producao.focus()
    }
}
</Script>
</head>
<body onload='controlar_foco();calcular()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=5';?>' onsubmit='return validar()'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<input type='hidden' name='tipo_manipulacao' value='<?=$tipo_manipulacao;?>'>
<input type='hidden' name='id_func_solic' value='<?=$id_func_solic;?>'>
<input type='hidden' name='id_cliente'>
<input type='hidden' name='passo'>
<input type='hidden' name='controle'>
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Incluir Manipulação no Estoque - 
            <font color="yellow">
            <?
                if($tipo_manipulacao == 'M') {
                    echo 'Manipular';
                }else if($tipo_manipulacao == 'O') {
                    echo 'OC';
                }else if($tipo_manipulacao == 'R') {
                    echo 'Refugo';
                }else if($tipo_manipulacao == 'U') {
                    echo 'Uso p/ Fábrica';
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Ref: </b><?=$referencia;?> - <b>Discriminação: </b><?=$discriminacao;?>
        </td>
        <td>
            <font color='blue'>
                <b>O.C:</b>
            </font>
            <?=$rotulo_oc;?>
        </td>
    </tr>
<?
/**********************************************************************************************/
/*************************************TIPO DE MANIPULAÇÃO**************************************/
/**********************************************************************************************/
//Se foi selecionado algum Tipo de Manipulação ... 
    if(!empty($tipo_manipulacao)) {
?>
    <tr class='linhanormal'>
<?
        if($tipo_manipulacao == 'U') {//Uso p/ Fábrica ...
?>
        <td colspan='3'>
            <font color='darkblue'>
                <b>Solicitador: </b>
            </font>
            <?
//Busca o nome da pessoa q solicitou ...
                $sql = "SELECT `nome` 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = '$id_func_solic' LIMIT 1 ";
                $campos_solicitador = bancos::sql($sql);
                echo $campos_solicitador[0]['nome'];
            ?>
        </td>
<?
        }else if($tipo_manipulacao == 'O') {//OC ...
?>
        <td>
            <b>N.º OC: </b>
            <input type='text' name='txt_numero_oc' title="Digite o N.º da OC" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''}" class='caixadetexto'>
        </td>
        <td colspan="2">
            <input type='button' name='cmd_consultar_cliente' value='Consultar Cliente' title='Consultar Cliente' onclick="html5Lightbox.showLightbox(7, 'consultar_clientes.php')" class='botao'>
            &nbsp;<input type='text' name='txt_cliente' title='Cliente' size='60' class="textdisabled" disabled>
        </td>
<?		
        }
?>
    </tr>
<?
    }
/**********************************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            Lançamento Estoque:
        </td>
        <td>
            Estoque Real Inicial:
        </td>
        <td>
            Estoque Real Final:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_lancamento_estoque' title='Lançamento em Estoque' onkeyup="verifica(this, 'moeda_especial', '2', '1', event);calcular()" size='15' maxlength='12' class='caixadetexto'>
            &nbsp;
            <?
                $url = 'consultar.php?passo=1';
                /*Mudança feita em 17/05/2016 - Antigamente os detalhes da consulta só eram feitos pela 
                referência independente de ser normal de Linha, eu supus que fosse assim porque temos PA(s) 
                que são similares em seu cadastro na parte de referência, por exemplo ML: 
                ML-001, ML-001A, ML-001AS, ML-001D, ML-001S, ML-001T, ML-001U, mas para ESP fica inviável 
                vindo todos os ESP´s do Sistema e trazendo informações que não tinham nada haver ...*/
                if($referencia == 'ESP') {//Aqui quero ver detalhes do PA ESP em específico ...
                    $url.= '&id_produto_acabado='.$id_produto_acabado.'&pop_up=1';
                }else {//PA normal de Linha, quero ver detalhes de todos os PA(s) semelhantes a este da Referência ...
                    $url.= '&txt_referencia='.$referencia.'&pop_up=1';
                }
            ?>
            <input type='button' name='cmd_baixas_manip' value='Baixas / Manipulações' title='Baixas / Manipulações' onclick="html5Lightbox.showLightbox(7, '<?=$url;?>')" style='color:brown; font-weight: bold' class='botao'>
        </td>
        <td>
            <input type='text' name="txt_estoque_real_inicial" value="<?=$estoque_real_inicial;?>" title="Estoque Real Inicial" size='20' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_estoque_real_final" title="Estoque Real Final" size='20' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>Lançamento Produção:</td>
        <td>
        <?
            //Aqui verifica se o PA tem relação com o PI, caso isso não acontece não apresenta o link
            $sql = "SELECT id_produto_insumo 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `id_produto_insumo` > '0' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_pipa = bancos::sql($sql);
            if(count($campos_pipa) == 1) {//Tem relação, então mostra o link
        ?>
            <a href="javascript:nova_janela('../../../classes/estoque/compra_producao.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" title="Compra Produção" class="link">
                Produção Inicial:
            </a>
        <?
            }else {
                echo 'Produção Inicial:';
            }
        ?>
        </td>
        <td>
            Produção Final:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_lancamento_producao" title="Lançamento em Produção" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event);calcular()" size="20" maxlength="20" class="textdisabled" disabled>
        </td>
        <td>
            <input type='text' name="txt_producao_inicial" value="<?=$compra_producao;?>" title="Produção Inicial" size="20" class="textdisabled" disabled>
        </td>
        <td>
            <input type='text' name="txt_producao_final" title="Produção Final" size="20" class="textdisabled" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <a href = '../../../classes/estoque/visualizar_estoque.php?id_produto_acabado=<?=$id_produto_acabado;?>' class='html5lightbox'>
                Prazo de Entrega
            </a>
        </td>
        <td colspan='2'>
            <font color='blue'>
                <b>Responsável:</b>
            </font>
            <?=$string_apresentar;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Total Separado:</b>
            <input type='text' name="txt_total_separado" value="<?=number_format($total_separado, 2, ',', '.');?>" title='Total Separado' size='10' class='caixadetexto2' disabled>
        </td>
        <td>
            <b>Total em Pedido:</b>
            <?
                //Aki pego o Total em Pedido
                /*$sql = "SELECT SUM(pvi.qtde) AS total_pedido 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_orcamento_venda_item = ovi.id_orcamento_venda_item 
                        WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ";
                $campos_pedido = bancos::sql($sql);*/
            ?>
            ???<input type='text' name='txt_total_pedido' value='<?=number_format($campos_pedido[0]['total_pedido'], 2, ',', '.');?>' title='Total em Pedido' size='10' class='caixadetexto2' disabled>
        </td>
        <td>
            <b>Total de Vale:</b>
            <?
                //Aki pego o Total de Vale
                /*$sql = "SELECT SUM(pvi.vale) AS total_vale 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_orcamento_venda_item = ovi.id_orcamento_venda_item 
                        WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ";
                $campos_pedido = bancos::sql($sql);*/
            ?>
            ???<input type='text' name="txt_total_vale" value="<?=number_format($campos_pedido[0]['total_vale'], 2, ',', '.');?>" title='Total de Vale' size='10' class='caixadetexto2' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Total Faturado:</b>
            <?
                //Aki pego o Total Faturado
                /*$sql = "SELECT SUM(nfsi.qtde) total_faturado 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_orcamento_venda_item = ovi.id_orcamento_venda_item 
                        INNER JOIN `nfs_itens` nfsi ON nfsi.id_pedido_venda_item = pvi.id_pedido_venda_item 
                        WHERE ovi.id_produto_acabado = '$id_produto_acabado' ";
                $campos_pedido = bancos::sql($sql);*/
            ?>
            ???<input type='text' name="txt_total_faturado" value="<?=number_format($campos_pedido[0]['total_faturado'], 2, ',', '.');?>" title="Total Faturado" size="10" class="caixadetexto2" disabled>
        </td>
        <td colspan='2'>
            <b>Total de Pendência:</b>
            <?
//Aki pego o Total de Pendência
                /*$sql = "SELECT SUM(pvi.qtde_pendente) AS total_pendente 
                        FROM `orcamentos_vendas_itens` ovi 
                        INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_orcamento_venda_item = ovi.id_orcamento_venda_item 
                        WHERE ovi.id_produto_acabado = '$id_produto_acabado' ";
                $campos_pedido = bancos::sql($sql);*/
            ?>
            ???<input type='text' name="txt_total_pendencia" value="<?=number_format($campos_pedido[0]['total_pendente'], 2, ',', '.');?>" title="Total de Pendência" size="10" class="caixadetexto2" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Estoque Disponível:</b>
            <input type='text' name="txt_estoque_disponivel" value="<?=number_format($estoque_disponivel, 2, ',', '.');?>" title="Estoque Disponível" size="10" class="caixadetexto2" disabled>
        </td>
        <td colspan='2'>
            <b>Estoque Comprometido:</b>
            <input type='text' name="txt_estoque_comprometido" value="<?=number_format($estoque_comprometido, 2, ',', '.');?>" title="Estoque Comprometido" size="10" class="caixadetexto2" disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <b>ATENÇÃO:</b>
            <marquee>
                O ESTOQUE REAL FINAL NÃO PODE SER MENOR DO QUE O TOTAL SEPARADO !! GERENCIE O ESTOQUE DO ITEM, SE NECESSÁRIO !!!
            </marquee>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <b>Observação:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <textarea name='txt_observacao' rows='3' cols='85' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <b>Observação do Produto:</b>
            <?=$observacao_produto;?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='3'>
<?
//$status_estoque => para saber se o estoquista esta manpulando o  produto 0-free  1-locked
//$status_estoque_item => é para saber se o item poder ser manipulado ou liberado para manipular 0-free 1-lock
        if($status_estoque == 0 && $racionado == 0) {
            if($status_estoque_item == 0) {
                $botao_submit = 1;//Quer dizer q pode mostrar o botão de submit
                echo '<font color="blue"><b>PRODUTO LIBERADO PARA USO !</b></font>';
            }else {
                $botao_submit = 1;//Quer dizer q pode mostrar o botão de submit
                echo '<font color="red"><b>PRODUTO BLOQUEADO !!! ESTE PRODUTO JÁ FOI MANIPULADO PELO ESTOQUISTA !</b></font>';
            }
        }else if($status_estoque == 1) {//tive q retirara a clausula racionado deste if
            $botao_submit = 0;//Quer dizer q pode não mostrar o botão de submit
            echo '<font color="red"><b>PRODUTO BLOQUEADO !!! ESTÁ SENDO MANIPULADO PELO ESTOQUISTA !</b></font>';
        }else {
            $botao_submit = 1;//Quer dizer q pode mostrar o botão de submit
            echo '<font color="red"><b>PRODUTO RACIONADO !</b></font>';
        }
?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');" style="color:#ff9900;" class='botao'>
            <?
                if($botao_submit == 1) {//Então pode mostrar o botão
            ?>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
            <?
                }
            ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 5) {
    $resultado = estoque_acabado::verificar_manipulacao_estoque($_POST['id_produto_acabado'], $_POST['txt_lancamento_estoque']);
    if($resultado['retorno'] == 'executar') {
//Aki registra a Data e Hora em q foi feita a alteração
        $data_sys = date('Y-m-d H:i:s');
        $status_tipus = $acao - 1;//Aki ele atribui a ação do Menu p/ Gravar no Banco
/**********************************************************************************/
//Tenho que chamar essa função para Setar o P.A., para o Paçoquinha saber que ele poder liberar os Pedidos...
        estoque_acabado::seta_nova_entrada_pa_op_compras($_POST['id_produto_acabado']);
//Procedimento normal para registro da Entrada ...
        $sql = "INSERT INTO `baixas_manipulacoes_pas` (`id_baixa_manipulacao_pa`, `id_produto_acabado`, `id_funcionario`, `id_funcionario_retirado`, `id_cliente`, `numero_oc`, `qtde`, `observacao`, `acao`, `data_sys`) VALUES (NULL, '$_POST[id_produto_acabado]', '$_SESSION[id_funcionario]', '$_POST[id_func_solic]', '$_POST[id_cliente]', '$_POST[txt_numero_oc]', '$_POST[txt_lancamento_estoque]', '$_POST[txt_observacao]', '$_POST[tipo_manipulacao]', '$data_sys') ";
        bancos::sql($sql);
        sleep(2);
        estoque_acabado::atualizar($_POST['id_produto_acabado']);
//Verifica quem é o responsável pela alteração do prazo de entrega
        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $login = $campos[0]['login'];
//Essa verificação é para ver se o Estoque Comprometido do PA é maior do que Zero
        $vetor = estoque_acabado::qtde_estoque($_POST['id_produto_acabado'], 1);
        $estoque_comprometido = $vetor[8];
//Quando o Estoque Comprometido for >=0, então limpa o Prazo de Entrega
        if($estoque_comprometido >= 0) {
//Aki eu Limpo o Prazo de Entrega, fica branquinho, ...
            $sql = "UPDATE `estoques_acabados` SET `prazo_entrega` = '' WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Aqui verifico se o PA é um PI "PIPA" para poder executar a função abaixo ...
        $sql = "SELECT id_produto_insumo 
                FROM `produtos_acabados` 
                WHERE `id_produto_acabado` = '$_POST[id_produto_acabado]' 
                AND `id_produto_insumo` > '0' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_pipa = bancos::sql($sql);
        if(count($campos_pipa) == 1) intermodular::gravar_campos_para_calcular_margem_lucro_estimada($campos_pipa[0]['id_produto_insumo']);
        $valor = $resultado['valor_msg'];
    }else {
        $valor = $resultado['valor_msg'];
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php<?=$parametro;?>&passo=1&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Manipulação de Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = false
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
//Tipo de Manipulação ...
    if(!combo('form', 'cmb_tipo_manipulacao', '', 'SELECIONE UM TIPO DE MANIPULAÇÃO !')) {
        return false
    }
}
</Script>
</head>
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border='0' width='70%' align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Manipulação de Estoque
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name="txt_consultar" size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="1" title="Consultar Produtos Insumos por: Referência" onclick="document.form.txt_consultar.focus()" id='label' checked>
            <label for='label'>
                Referência
            </label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value="2" title="Consultar Produtos Insumos por: Discriminação" onclick="document.form.txt_consultar.focus()" id='label2'>
            <label for='label2'>
                Discriminação
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value="3" title="Consultar Produtos Insumos por: Observação" onclick="document.form.txt_consultar.focus()" id='label3'>
            <label for='label3'>Observação</label>
        </td>
        <td>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Tipo de Manipulação 
            <select name="cmb_tipo_manipulacao" title="Selecione o Tipo de Manipulação" class='combo'>
                <option value="" style='color:red'>SELECIONE</option>
                <option value="M">MANIPULAR</option>
                <option value="O">OC</option>
                <option value="R">REFUGO</option>
                <option value="U">USO P/ FÁBRICA</option>
            </select>
        </td>
    </tr>
<?
/*Significa q está sendo acessado do Mód. de Compras, então não mostrar o checkbox de Mostrar Componentes*/
    if($veio_compras == 1) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os Produtos Insumos" id="todos" onClick='limpar()' class='checkbox'>
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
<?
//Aqui está sendo acessado do Mód. de Vendas, então pode mostrar o checkbox de Mostrar Componentes*/
    }else {
?>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='chkt_mostrar_componentes' value='1' title="Mostrar Componentes" id="mostrar_componentes" class='checkbox'>
            <label for="mostrar_componentes">Mostrar Componentes</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os Produtos Insumos" id="todos" onClick='limpar()' class='checkbox'>
            <label for="todos">Todos os registros</label>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false; limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>