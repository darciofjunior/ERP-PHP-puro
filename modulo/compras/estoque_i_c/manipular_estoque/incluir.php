<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_new.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>MANIPULAÇÃO INCLUIDA COM SUCESSO.</font>";

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT g.`referencia`, g.`nome`, pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g on g.id_grupo = pi.id_grupo and g.id_grupo <> '9' AND g.referencia LIKE '%$txt_consultar%' 
                    INNER JOIN `unidades` u on u.id_unidade = pi.id_unidade 
                    WHERE pi.ativo = '1' ORDER BY pi.discriminacao ";
        break;
        case 2:
            $sql = "SELECT g.`referencia`, g.`nome`, pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g on g.id_grupo = pi.id_grupo and g.id_grupo <> '9' 
                    INNER JOIN `unidades` u on u.id_unidade = pi.id_unidade 
                    WHERE pi.discriminacao LIKE '%$txt_consultar%' 
                    AND pi.ativo = '1' ORDER BY pi.discriminacao ";
        break;
        case 3:
            $sql = "SELECT g.`referencia`, g.`nome`, pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g on g.id_grupo = pi.id_grupo and g.id_grupo <> '9' 
                    INNER JOIN `unidades` u on u.id_unidade = pi.id_unidade 
                    WHERE pi.observacao LIKE '%$txt_consultar%' 
                    AND pi.ativo = '1' ORDER BY pi.discriminacao ";
        break;
        default:
            $sql = "SELECT g.`referencia`, g.`nome`, pi.`id_produto_insumo`, pi.`estocagem`, pi.`discriminacao`, u.`sigla` 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g on g.id_grupo = pi.id_grupo and g.id_grupo <> '9' 
                    INNER JOIN `unidades` u on u.id_unidade = pi.id_unidade 
                    WHERE pi.ativo = '1' ORDER BY pi.discriminacao ";
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
<title>.:: Produto(s) Insumo(s) p/ Incluir Manipulação de Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Produto(s) Insumo(s) p/ Incluir Manipulação de Estoque
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
                $url = 'incluir.php?passo=2&id_produto_insumo='.$campos[$i]['id_produto_insumo'];
            }else {//Não Estocável ...
                $url = "javascript:alert('ESTE PRODUTO NÃO PODE SER MANIPULADO, DEVIDO A SER NÃO ESTOCÁVEL !')";
            }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
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
        <td>
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
            $sql = "SELECT qtde 
                    FROM `estoques_insumos` 
                    WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
            $campos_estoque = bancos::sql($sql);
            if(count($campos_estoque) == 1) {
                //Se a qtde em Estoque é menor do que Zero, então exibo essa coluna em Vermelho ...
                $color = ($campos_estoque[0]['qtde'] < 0) ? 'red' : 'black';
                echo "<font color=$color>".number_format($campos_estoque[0]['qtde'], 2, ',', '.')."</font>";
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class='botao'>
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
    $id_produto_insumo 	= ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_insumo'] : $_GET['id_produto_insumo'];
    
    $sql = "SELECT pi.id_produto_insumo, pi.discriminacao, pi.qtde_estoque_pi, pi.observacao, u.sigla 
            FROM `produtos_insumos` pi 
            INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
            WHERE pi.id_produto_insumo = '$id_produto_insumo' LIMIT 1 ";
    $campos 		= bancos::sql($sql);
    $sigla              = $campos[0]['sigla'];
    $unidade 		= $campos[0]['unidade'];
    $discriminacao 	= $campos[0]['discriminacao'];
    $qtde_pi_usado	= number_format($campos[0]['qtde_estoque_pi'], 2, ',', '.');
    $observacao = $campos[0]['observacao'];
//Busca a Qtde em Estoque do Produto Insumo ...
    $sql = "SELECT qtde 
            FROM estoques_insumos 
            WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $qtde_estoque = (count($campos) == 0) ? 0 : number_format($campos[0]['qtde'], 2, ',', '.');
//Busco a Densidade do Aço, p/ auxiliar em alguns cálculos de PHP e JavaScript também ...
    $sql = "SELECT densidade_aco 
            FROM `produtos_insumos_vs_acos` 
            WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
    $campos_aco 	= bancos::sql($sql);
    $densidade_aco 	= (count($campos_aco) > 0) ? $campos_aco[0]['densidade_aco'] : 0;
?>
<html>
<title>.:: Incluir Manipulação no Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
</head>
<body onload='calcular();document.form.txt_qtde_manipular_baixa.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_insumo' value="<?=$id_produto_insumo;?>">
<input type='hidden' name='passo'>
<input type='hidden' name='controle'>
<table border='0' width='60%' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Manipulação no Estoque
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>PRODUTO INSUMO: </b> 
            </font>
            <?=$discriminacao;?>
        </td>
        <td>
            <font color='darkblue'>
                <b>OBSERVAÇÃO DO PRODUTO:</b>
            </font>
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
    if($densidade_aco != '') {//Quando o PI for Tipo Aço ...
?>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
//Somente para a primeira vez q cair na tela
            if(empty($controle)) {
                $checado = '';
            }else {
                if(!empty($chkt_metros)) {
                    $checado = 'checked';
                }else {
                    $checado  = '';
                }
            }
        ?>
            <input type='checkbox' <?=$checado;?> name='chkt_metros' value='1' onclick='calcular();submeter()' id='label1' class='checkbox'>
            <label for='label1'>
                <font color='darkblue'>
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
            <b>Qtde à Manipular / Dar Baixa:</b>
        </td>
        <td>
            <b>Qtde em <?=$sigla;?>:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type="text" name="txt_qtde_manipular_baixa" value="<?=$txt_qtde_manipular_baixa;?>" title="Digite a quantidade" size="20" onkeyup="verifica(this, 'moeda_especial', '2', '1', event);calcular()" class="caixadetexto">
            <?
                $checked_troca = (!empty($_POST['chkt_troca'])) ? 'checked' : '';
            ?>
            <input type="checkbox" name="chkt_troca" value="S" id="label_troca" class="checkbox" <?=$checked_troca;?>>
            <label for="label_troca">
                    Troca <b> (Não computar CMM)</b>
            </label>
        </td>
        <td>
            <input type='text' name='txt_qtde_kg' value='<?=$txt_qtde_kg;?>' title='Quantidade em KG' size='20' class='textdisabled' disabled>
            &nbsp;<?=$sigla;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde Inicial Estoque:</b>
        </td>
        <td>
            <b>Qtde Final Estoque /</b>
            <font color='brown'>
                <b>Qtde PI USADO:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name="txt_qtde_inicial_estoque" value="<?=$qtde_estoque;?>" title="Quantidade Inicial em Estoque" class="textdisabled" size="15" disabled>
            &nbsp;
            <input type='button' name='cmd_baixas_manip' value='Baixas / Manipulações' title='Baixas / Manipulações' onclick="html5Lightbox.showLightbox(7, '../detalhes_baixas_manipulacoes.php?id_produto_insumo=<?=$id_produto_insumo;?>&nao_exibir_voltar=1')" style='color:brown; font-weight: bold' class='botao'>
        </td>
        <td>
            <input type="text" name="txt_qtde_final_estoque" value="<?=$txt_qtde_final_estoque;?>" class="textdisabled" size="15" disabled>
            <font color='brown'>
                    &nbsp;/ <b><?=$qtde_pi_usado;?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Justificativa:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <textarea name="txt_justificativa" rows='3' cols='85' maxlength='255' class="caixadetexto"><?=$txt_justificativa;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type="button" name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');calcular();document.form.txt_qtde_manipular_baixa.focus()" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
<Script Language = 'Javascript'>
function submeter() {
    document.form.controle.value = 1
    document.form.passo.value = 2
    document.form.submit()
}

function calcular() {
    if(document.form.txt_qtde_manipular_baixa.value == '-') document.form.txt_qtde_manipular_baixa.value = ''

    var qtde_estoque = eval(strtofloat(document.form.txt_qtde_inicial_estoque.value))
    var quantidade = eval(strtofloat(document.form.txt_qtde_manipular_baixa.value))
<?
//Produtos do Tipo Aço
    if($densidade_aco != '') {
/*Significa que está sendo feito o cálculo de Kilos para Metros pois está checkado o objeto 
"Dar Baixa em Metros"*/
        if(!empty($chkt_metros)) {
?>
            var densidade_aco = eval('<?=$densidade_aco;?>')
            if(typeof(quantidade) == 'undefined') {
                document.form.txt_qtde_kg.value = ''
                document.form.txt_qtde_final_estoque.value = ''
//Aqui é a conversão de Metros para Kilos
            }else {
                document.form.txt_qtde_kg.value = densidade_aco * quantidade
                document.form.txt_qtde_kg.value = arred(document.form.txt_qtde_kg.value, 2, 1)

                var quantidade_kg = eval(strtofloat(document.form.txt_qtde_kg.value))
                document.form.txt_qtde_final_estoque.value = qtde_estoque + quantidade_kg
                document.form.txt_qtde_final_estoque.value = arred(document.form.txt_qtde_final_estoque.value, 2, 1)
            }
<?
/*Significa que está sendo feito o cálculo de Metros para Kilos porque não está checkado o objeto 
"Dar Baixa em Metros"*/
        }else {
?>
            if(typeof(quantidade) == 'undefined') {
                document.form.txt_qtde_kg.value = ''
                document.form.txt_qtde_final_estoque.value = ''
            }else {
                document.form.txt_qtde_kg.value = document.form.txt_qtde_manipular_baixa.value
                document.form.txt_qtde_final_estoque.value = qtde_estoque + quantidade
                document.form.txt_qtde_final_estoque.value = arred(document.form.txt_qtde_final_estoque.value, 2, 1)
            }
<?
        }
//Produtos Normais
    }else {
?>
        if(typeof(quantidade) == 'undefined') {
            document.form.txt_qtde_kg.value = ''
            document.form.txt_qtde_final_estoque.value = ''
        }else {
            document.form.txt_qtde_kg.value = document.form.txt_qtde_manipular_baixa.value
            document.form.txt_qtde_final_estoque.value = qtde_estoque + quantidade
            document.form.txt_qtde_final_estoque.value = arred(document.form.txt_qtde_final_estoque.value, 2, 1)
        }
<?
    }
?>
}

function validar() {
//Quantidade
    if(!texto('form', 'txt_qtde_manipular_baixa', '1', '1234567890,.-', 'QUANTIDADE', '1')) {
        return false
    }
    document.form.txt_qtde_kg.disabled 	= false
    document.form.passo.value           = 3
    return limpeza_moeda('form', 'txt_qtde_manipular_baixa, txt_qtde_kg, ')
}
</Script>
</html>
<?
}else if($passo == 3) {
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
    $sql = "SELECT qtde 
            FROM `estoques_insumos` 
            WHERE `id_produto_insumo` = '$_POST[id_produto_insumo]' LIMIT 1 ";
    $campos 		= bancos::sql($sql);
    $qtde_estoque 	= (count($campos) == 0) ? 0 : $campos[0]['qtde'];
    $estoque_final 	= $qtde_estoque + $_POST['txt_qtde_manipular_baixa'];	
    if($estoque_final < 0) {//Nunca podemos deixar o Estoque Negativo ...
?>
	<Script Language = 'JavaScript'>
            alert('A QUANTIDADE SOLICITADA É MAIOR DO QUE O SALDO EM ESTOQUE !')
            window.location = 'incluir.php?passo=2&id_produto_insumo=<?=$_POST['id_produto_insumo'];?>'
	</Script>
<?
    }else {//Procedimento normal
        $data_sys = date('Y-m-d H:i:s');
        if($_POST['chkt_metros'] == 1) $_POST['txt_qtde_manipular_baixa'] = $_POST['txt_qtde_kg'];
//Controle com a Parte de Troca ...
        $troca = (!empty($_POST['chkt_troca'])) ? 'S' : 'N';
//Gravando a Manipulação ...
        $sql = "INSERT INTO `baixas_manipulacoes` (`id_baixa_manipulacao`, `id_produto_insumo`, `id_funcionario`, `id_funcionario_retirado`, `retirado_por`, `qtde`, `estoque_final`, `observacao`, `acao`, `troca`, `data_sys`) VALUES (NULL, '$_POST[id_produto_insumo]', '$_SESSION[id_funcionario]', '$_SESSION[id_funcionario]', '', '$_POST[txt_qtde_manipular_baixa]', '$estoque_final', '$_POST[txt_justificativa]', 'M', '$troca', '$data_sys') ";
        bancos::sql($sql);
        estoque_ic::atualizar($_POST['id_produto_insumo'], 0);
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir.php<?=$parametro;?>&passo=1&valor=2'
        </Script>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Consultar Produto(s) Insumo(s) p/ Incluir Manipulação de Estoque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 3; i ++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 3;i ++) document.form.opt_opcao[i].disabled = false
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
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table border="0" width="70%" align='center' cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Produto(s) Insumo(s) p/ Incluir Manipulação de Estoque
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'> 
            Consultar <input type="text" name='txt_consultar' size='45' maxlength='45' class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="1" title="Consultar Produtos Insumos por: Referência" onclick="document.form.txt_consultar.focus()" id='label'>
            <label for='label'>
                Referência
            </label>
        </td>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="2" title="Consultar Produtos Insumos por: Referência" onClick="document.form.txt_consultar.focus()" id='label2' checked>
            <label for='label2'>
                Discrimina&ccedil;&atilde;o
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width="20%">
            <input type="radio" name="opt_opcao" value="3" title="Consultar Produtos Insumos por: Observação" onClick='document.form.txt_consultar.focus()' id='label3'>
            <label for='label3'>Observação</label>
        </td>
        <td width="20%">
            <input type='checkbox' name='opcao' value='1' title="Consultar todos os Produtos Insumos" onClick='limpar()' class="checkbox" id='label4'>
            <label for='label4'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
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