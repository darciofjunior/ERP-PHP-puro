<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/compras_new.php');
require('../../../../../lib/data.php');//Essa biblioteca é usada dentro da Intermodular por isso não posso arrancar ...
require('../../../../../lib/estoque_acabado.php');//Essa biblioteca é usada dentro da Intermodular por isso não posso arrancar ...
require('../../../../../lib/genericas.php');//Essa biblioteca é usada dentro da Intermodular por isso não posso arrancar ...
require('../../../../../lib/intermodular.php');
require('../../../../../lib/producao.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/pedidos_pendentes/pedidos_pendentes.php', '../../../../../');

//Aqui essas variáveis pq vou precisar delas nos cálculos lá + abaixo ...
$fator_importacao   = genericas::variaveis('fator_importacao');
$dolar_dia          = genericas::moeda_dia('dolar');
$euro_dia           = genericas::moeda_dia('euro');

$mensagem[1] = "<font class='confirmacao'>PEDIDO CONCLUÍDO COM SUCESSO.</font>";
$mensagem[2] = "<font class='confirmacao'>ITEM DE PEDIDO CONCLUÍDO COM SUCESSO.</font>";
$mensagem[3] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[4] = "<font class='atencao'>NÃO HÁ ITEM(NS) NESSE PEDIDO.</font>";

if($passo == 1) {
/*Pelo q consegui entender a variável $num_opcao serve para não perder a refêrencia de Consulta, e 
poder realizar o SQL do mesmo jeito em q o usuário fez a consulta na primeira vez*/
    if(isset($num_opcao)) {
        if($num_opcao <= 9) {
            $opt_opcao = $num_opcao;
        }else {
            $opcao = $num_opcao;
        }
    }
    switch($opt_opcao) {
        case 1:
            $sql_principal = "Select f.razaosocial, e.nomefantasia, p.* 
                    from pedidos p 
                    inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
                    inner join empresas e on e.id_empresa = p.id_empresa and e.nomefantasia like '%$txt_consultar%' 
                    where p.status = 1 
                    and p.ativo = 1 order by e.nomefantasia, p.id_pedido desc ";
            $condicao = " and e.nomefantasia like '%$txt_consultar%' order by e.nomefantasia, p.id_pedido desc ";
            $tela = 1;
        break;
        case 2:
            $sql_principal = "Select u.sigla, g.referencia, pi.discriminacao, ip.*, f.razaosocial, e.nomefantasia, p.* 
                    from pedidos p 
                    inner join empresas e on e.id_empresa = p.id_empresa 
                    inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
                    inner join itens_pedidos ip on ip.id_pedido = p.id_pedido and ip.status < 2 
                    inner join produtos_insumos pi on pi.id_produto_insumo = ip.id_produto_insumo 
                    inner join unidades u on u.id_unidade = pi.id_unidade 
                    inner join grupos g on g.id_grupo = pi.id_grupo and g.referencia like '%$txt_consultar%' 
                    where p.status = '1' 
                    and p.ativo = 1 order by g.referencia, pi.discriminacao ";
            $condicao = " and g.referencia like '%$txt_consultar%' order by g.referencia, pi.discriminacao ";
            $tela = 2;
        break;
        case 3:
            $sql_principal = "Select f.razaosocial, e.nomefantasia, p.* 
                    from pedidos p 
                    inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
                    inner join empresas e on e.id_empresa = p.id_empresa 
                    where f.razaosocial like '%$txt_consultar%' 
                    and p.status = 1 
                    and p.ativo = 1 order by f.razaosocial, p.id_pedido desc ";
            $condicao = " and f.razaosocial like '%$txt_consultar%' order by f.razaosocial, p.id_pedido desc ";
            $tela = 1;
        break;
        case 4:
            $sql_principal = "Select ui.sigla, g.referencia, pa.referencia as referencia_pa, pi.discriminacao, ip.*, f.razaosocial, e.nomefantasia, p.* 
                    from pedidos p 
                    inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
                    inner join empresas e on e.id_empresa = p.id_empresa 
                    inner join itens_pedidos ip on ip.id_pedido = p.id_pedido and ip.status < 2 
                    inner join produtos_insumos pi on pi.id_produto_insumo = ip.id_produto_insumo 
                    inner join unidades ui on ui.id_unidade = pi.id_unidade 
                    inner join grupos g on g.id_grupo = pi.id_grupo 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` AND pa.`referencia` LIKE '%$txt_consultar%' 
                    where p.status = '1' 
                    and p.ativo = 1 order by g.referencia, pi.discriminacao ";
            $condicao = " and pa.referencia like '%$txt_consultar%' order by g.referencia, pi.discriminacao ";
            $tela = 2;
        break;
        case 5:
            $sql_principal = "Select f.razaosocial, e.nomefantasia, p.* 
                    from pedidos p 
                    inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
                    inner join empresas e on e.id_empresa = p.id_empresa 
                    where p.id_pedido like '$txt_consultar%' 
                    and p.status = 1 
                    and p.ativo = 1 order by id_pedido desc ";
            $condicao = " and p.id_pedido like '$txt_consultar%' order by id_pedido desc ";
            $tela = 1;
        break;
        case 6:
            $sql_principal = "SELECT u.sigla, g.referencia, pi.discriminacao, ip.*, f.razaosocial, e.nomefantasia, p.* 
                    FROM `pedidos` p 
                    INNER JOIN `empresas` e ON e.id_empresa = p.id_empresa 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
                    INNER JOIN `itens_pedidos` ip ON ip.id_pedido = p.id_pedido AND ip.status < 2 
                    INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo AND pi.`discriminacao` LIKE '%$txt_consultar%' 
                    INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                    WHERE p.`status` = '1' 
                    AND p.`ativo` = '1' ORDER BY pi.discriminacao, g.referencia ";
            $condicao = " AND pi.`discriminacao` LIKE '%$txt_consultar%' ORDER BY pi.discriminacao, g.referencia ";
            $tela = 2;
        break;
        case 7:
            $sql_principal = "SELECT u.sigla, g.referencia, pi.discriminacao, ip.*, f.razaosocial, e.nomefantasia, p.* 
                    FROM `pedidos` p 
                    INNER JOIN `empresas` e ON e.id_empresa = p.id_empresa 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = p.id_fornecedor 
                    INNER JOIN `itens_pedidos` ip ON ip.id_pedido = p.id_pedido AND ip.status < 2 
                    INNER JOIN `produtos_insumos` pi ON pi.id_produto_insumo = ip.id_produto_insumo AND ip.`marca` LIKE '%$txt_consultar%' 
                    INNER JOIN `unidades` u ON u.id_unidade = pi.id_unidade 
                    INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
                    WHERE p.`status` = '1' 
                    AND p.`ativo` = '1' ORDER BY pi.discriminacao, g.referencia ";
            $condicao = " AND ip.`marca` LIKE '%$txt_consultar%' ORDER BY pi.discriminacao, g.referencia ";
            $tela = 2;
        break;
        case 8:
            $sql_principal = "Select u.sigla, g.referencia, pi.discriminacao, ip.*, f.razaosocial, e.nomefantasia, p.* 
                    from pedidos p 
                    inner join empresas e on e.id_empresa = p.id_empresa 
                    inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
                    inner join itens_pedidos ip on ip.id_pedido = p.id_pedido and ip.status < 2 
                    inner join produtos_insumos pi on pi.id_produto_insumo = ip.id_produto_insumo 
                    inner join unidades u on u.id_unidade = pi.id_unidade 
                    inner join grupos g on g.id_grupo = pi.id_grupo 
                    where p.id_pedido like '$txt_consultar%' 
                    and p.status = '1' 
                    and p.ativo = 1 order by p.id_pedido desc ";
            $condicao = " and p.id_pedido like '$txt_consultar%' order by p.id_pedido desc ";
            $tela = 2;
        break;
        case 9:
            $sql_principal = "Select u.sigla, g.referencia, pi.discriminacao, ip.*, f.razaosocial, e.nomefantasia, p.* 
                    from pedidos p 
                    inner join empresas e on e.id_empresa = p.id_empresa 
                    inner join fornecedores f on f.id_fornecedor = p.id_fornecedor and f.razaosocial like '%$txt_consultar%' 
                    inner join itens_pedidos ip on ip.id_pedido = p.id_pedido and ip.status < 2 
                    inner join produtos_insumos pi on pi.id_produto_insumo = ip.id_produto_insumo 
                    inner join unidades u on u.id_unidade = pi.id_unidade 
                    inner join grupos g on g.id_grupo = pi.id_grupo 
                    where p.status = '1' 
                    and p.ativo = 1 order by pi.discriminacao asc, p.id_pedido ";
            $condicao = " and f.razaosocial like '%$txt_consultar%' order by pi.discriminacao asc, p.id_pedido ";
            $tela = 2;
        break;
        default:
            if($opcao == 10) {
                $sql_principal = "Select f.razaosocial, e.nomefantasia, p.* 
                        from pedidos p 
                        inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
                        inner join empresas e on e.id_empresa = p.id_empresa 
                        where p.status = 1 
                        and p.ativo = 1 order by p.data_emissao desc ";
                $condicao = " order by p.data_emissao desc ";
                $tela = 1;
            }else {
                $sql_principal = "Select u.sigla, g.referencia, pi.discriminacao, ip.*, f.razaosocial, e.nomefantasia, p.* 
                        from pedidos p 
                        inner join empresas e on e.id_empresa = p.id_empresa 
                        inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
                        inner join itens_pedidos ip on ip.id_pedido = p.id_pedido and ip.status < 2 
                        inner join produtos_insumos pi on pi.id_produto_insumo = ip.id_produto_insumo 
                        inner join unidades u on u.id_unidade = pi.id_unidade 
                        inner join grupos g on g.id_grupo = pi.id_grupo 
                        where p.status = '1' 
                        and p.ativo = 1 order by p.data_emissao desc ";
                $condicao = " order by p.data_emissao desc ";
                $tela = 2;
            }
        break;
    }
    if(!empty($opt_opcao)) {
        $campos = bancos::sql($sql_principal, $inicio, 200, 'sim', $pagina);
    }else {
        $campos = bancos::sql($sql_principal, $inicio, 20, 'sim', $pagina);
    }
    $linhas = count($campos);
//Acho q aki é para devolver o parâmetro e guardar no hidden
    if(isset($opt_opcao)) {
        $num_opcao = $opt_opcao;
    }else {
        $num_opcao = $opcao;
    }
//Significa que o Usuário escolheu um Relatório por Pedido(s)
    if($tela == 1) {
        if($linhas == 0) {
?>
	<Script Language = 'JavaScript'>
		window.location = 'pedidos_pendentes.php?valor=3'
	</Script>
<?
        }else {
?>
<html>
<head>
<title>.:: Pedidos Pendentes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {//Concluir Pedido
    if(!option('form', 'opt_pedido', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'radio') {
            if(document.form.elements[i].checked == true) id_pedido = document.form.elements[i].value
        }
    }
    var txt_consultar = document.form.txt_consultar.value
    var num_opcao = document.form.num_opcao.value
    window.location = 'pedidos_pendentes.php?passo=5&id_pedido='+id_pedido+'&txt_consultar='+txt_consultar+'&num_opcao='+num_opcao
}
</Script>
</head>
<body>
<form name='form' action='<?=$PHP_SELF.'?passo=2';?>' method='post' onsubmit='return validar()'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Pedido(s) Pendente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N. &ordm; Pedido
        </td>
        <td>
            Emiss&atilde;o
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Pend&ecirc;ncia
        </td>
        <td>
            Empresa
        </td>
        <td>
            Selecionar
        </td>
    </tr>
<?
			for ($i = 0; $i < $linhas;  $i++) {
				$id_pedido = $campos[$i]['id_pedido'];
				$url = "javascript:nova_janela('../itens.php?id_pedido=".$id_pedido."&pop_up=1', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')";
?>
    <tr class='linhanormal' onclick="options('form', 'opt_pedido', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
		<td onclick="<?=$url;?>" width='10'>
                    <a href="#" class='link'>
                        <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
                    </a>
		</td>
		<td onclick="<?=$url;?>">
			<a href="#" class="link">
				<?=$campos[$i]['id_pedido'];?>
			</a>
		</td>
		<td>
			<?=data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/');?>
		</td>
		<td align="left">
			<?=$campos[$i]['razaosocial'];?>
		</td>
		<td>
<?
				$sql = "Select id_item_pedido 
						from itens_pedidos 
						where id_pedido=".$campos[$i]['id_pedido']." 
						and status > 0 limit 1 ";
				$campos2 = bancos::sql($sql);
				if(count($campos2) == 0) {
					echo '<font color="FF0000">Total</font>';
				}else {
					echo '<font color="0000FF">Parcial</font>';
				}
?>
		</td>
		<td>
		<?
			$tp_nota[1] = 'NF';
			$tp_nota[2] = 'SGD';
			echo $campos[$i]['nomefantasia'].' ('.$tp_nota[$campos[$i]['tipo_nota']].') ';
			if($campos[$i]['tipo_export'] == 'E') {
				echo '<font color="red"><b> (Exp)</b></font>';
			}else if($campos[$i]['tipo_export'] == 'I') {
				echo '<font color="red"><b> (Imp)</b></font>';
			}else if($campos[$i]['tipo_export'] == 'N') {
				echo '<font color="red"><b> (Nac)</b></font>';
			}
		?>
		</td>
		<td>
			<input type='radio' name='opt_pedido'  value='<?=$campos[$i]['id_pedido'];?>' onclick="options('form', 'opt_pedido','<?=$i;?>', '#E8E8E8')">
		</td>
	</tr>
<?
			}
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan='8'>
			<input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'pedidos_pendentes.php?passo=0'" class='botao'>
			<input type='submit' name='cmd_detalhes_nf' value='&gt;&gt; Detalhes NF &gt;&gt;' title='Detalhes NF' class='botao'>
			<input type='button' name='cmd_concluir' value='Concluir Pedido' title='Concluir Pedido' onclick='return validar()' class='botao'>
		</td>
	</tr>
</table>
<input type='hidden' name='passo' value='2'>
<input type='hidden' name='tela' value='<?=$tela;?>'>
<input type='hidden' name='txt_consultar' value='<?=$txt_consultar;?>'>
<input type='hidden' name='num_opcao' value='<?=$num_opcao;?>'>
</form>
<center>
	<?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
        }
    }else {
//Significa que o Usuário escolheu um Relatório por Itens de Pedido(s)
//Esse desvio existe, porque quando consulto por Referência do PA, ele trabalha com duas tabelas a +, q não tem nas outras opções
        if($opt_opcao == 4) {
                $sql = "Select count(p.id_pedido) as total_registro 
                                from pedidos p 
                                inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
                                inner join empresas e on e.id_empresa = p.id_empresa 
                                inner join itens_pedidos ip on ip.id_pedido = p.id_pedido and ip.status < 2 
                                inner join produtos_insumos pi on pi.id_produto_insumo = ip.id_produto_insumo 
                                inner join unidades ui on ui.id_unidade = pi.id_unidade 
                                inner join grupos g on g.id_grupo = pi.id_grupo 
                                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_insumo` = pi.`id_produto_insumo` 
                                where p.status = '1' 
                                and p.ativo = 1 $condicao ";
        }else {
                $sql = "Select count(p.id_pedido) as total_registro 
                                from pedidos p 
                                inner join empresas e on e.id_empresa = p.id_empresa 
                                inner join fornecedores f on f.id_fornecedor = p.id_fornecedor 
                                inner join itens_pedidos ip on ip.id_pedido = p.id_pedido and ip.status < 2 
                                inner join produtos_insumos pi on pi.id_produto_insumo = ip.id_produto_insumo 
                                inner join unidades u on u.id_unidade = pi.id_unidade 
                                inner join grupos g on g.id_grupo = pi.id_grupo 
                                where p.status = '1' 
                                and p.ativo = 1 
                                $condicao ";
        }
        $campos2 = bancos::sql($sql);
        if($linhas == 0) {
?>
	<Script Language = 'JavaScript'>
            window.location = 'pedidos_pendentes.php?valor=3'
	</Script>
<?
        }else {
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function igualar(indice) {
    var controle = 0, existe = 0, liberado = '', codigo = '', cont = 0
    var elemento = '', objeto = ''
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'radio') cont ++
    }

    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'hidden' && document.form.elements[i].name == 'opt_item_pedido') existe ++
    }
    
    if(cont > 1) {
        elemento    = document.form.opt_item_pedido[indice].value
        objeto      = document.form.opt_item_pedido[indice]
    }else {
        if(existe == 0) {
            elemento    = document.form.opt_item_pedido.value
            objeto      = document.form.opt_item_pedido
        }else {
            elemento    = document.form.opt_item_pedido[indice].value
            objeto      = document.form.opt_item_pedido[indice]
        }
    }
    if(objeto.type == 'radio') {
        for(i = 0; i < elemento.length; i ++) {
            if(elemento.charAt(i) == '|') {
                controle++
            }else {
                if(controle == 1) {
                    liberado = liberado + elemento.charAt(i)
                }else {
                    codigo = codigo + elemento.charAt(i)
                }
            }
        }
        document.form.id_item_pedido.value = codigo
    }else {
        limpar_radio()
    }
}

function limpar_radio() {
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'radio') document.form.elements[i].checked = false
    }
}

function validar(valor) {
    var cont = 0
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'radio') {
            if(document.form.elements[i].checked == true) cont ++
        }
    }
    selecionado = document.form.id_item_pedido.value
    if(cont == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
    if(valor == 2) {
        var mensagem = confirm('DESEJA REALMENTE CONCLUIR ESSE ITEM DE PEDIDO ?')
        if(mensagem == true) {
            var txt_consultar = document.form.txt_consultar.value
            var num_opcao = document.form.num_opcao.value
            window.location = 'pedidos_pendentes.php?passo=7&id_item_pedido='+selecionado+'&txt_consultar='+txt_consultar+'&num_opcao='+num_opcao
        }else {
            return false
        }
    }
}
</Script>
</head>
<body>
<form name='form' action='<?=$PHP_SELF.'?passo=3';?>' method='post' onsubmit='return validar(1)'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='14'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            Itens de Pedido
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde <br/>Solicitada
        </td>
        <td>
            Qtde <br/>Recebido
        </td>
        <td>
            Qtde <br/>Restante
        </td>
        <td>
            <font title='Estoque do Fornecedor' size='-2' style='cursor:help'>
                E Forn
            </font>
        </td>
        <td>
            <font title='Estoque do Porto' size='-2' style='cursor:help'>
                E Porto
            </font>
        </td>
        <td>
            Un
        </td>
        <td>
            Produto
        </td>
        <td>
            Preço <br/>Unitário
        </td>
        <td>
            Valor <br/>Pendente
        </td>
        <td>
            N.º Ped
        </td>
        <td>
            Data Emissão
        </td>        
        <td>
            Pzo Entr <br/>/ Emb
        </td>
        <td>
            Marca <br/>/ Obs
        </td>
        <td>
            Item
        </td>
    </tr>
<?
            $pular = 0;
            for($i = 0;  $i < $linhas; $i++) {
                //Aki vejo se o PI tem relação com o PA ...
                $sql = "SELECT `id_produto_acabado` 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                $campos_pipa        = bancos::sql($sql);
                
                if($campos_pipa[0]['id_produto_acabado'] > 0) {
                    $estoque_produto    = estoque_acabado::qtde_estoque($campos_pipa[0]['id_produto_acabado'], 0);
                    $est_fornecedor     = $estoque_produto[12];
                    $est_porto          = $estoque_produto[13];
                }else {
                    $est_fornecedor     = 0;
                    $est_porto          = 0;
                }

//Verifico em Nota Fiscal, a Qtde Entregue do Item de Pedido Corrente ...
                $sql = "SELECT SUM(nfeh.`qtde_entregue`) AS total_entregue 
                        FROM `itens_pedidos` ip 
                        INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` 
                        INNER JOIN `nfe_historicos` nfeh ON nfeh.`id_item_pedido` = ip.`id_item_pedido` 
                        WHERE ip.`id_item_pedido` = '".$campos[$i]['id_item_pedido']."' ";
                $campos_qtde_entregue   = bancos::sql($sql);
                $total_entregue         = $campos_qtde_entregue[0]['total_entregue'];
//Nessa variável eu verifico o quanto que ainda resta para Entregar daquele Item ...
                $total_restante = $campos[$i]['qtde'] - $total_entregue;
                if($total_restante != 0) {
?>
    <tr class='linhanormal' onclick="options('form', 'opt_item_pedido', '<?=$pular;?>', '#E8E8E8');igualar('<?=$pular;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($total_entregue, 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($total_restante, 2, ',', '.');?>
        </td>
        <td>
            <?=segurancas::number_format($est_fornecedor, 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($est_porto, 2, '.');?>
        </td>
        <td>
            <?=$campos[$i]['sigla'];?>
        </td>
        <td align='left'>
        <?
            if(count($campos_pipa) == 1) {//Se existir passa o id do PA
//Vira link ...
        ?>
                <a href="javascript:nova_janela('../../alterar_prazo_entrega.php?id_produto_acabado=<?=$campos_pipa[0]['id_produto_acabado'];?>', 'CONSULTAR', '', '', '', '', '300', '750', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Prazo de Entrega' class='link'>
        <?
            }
            echo genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia']).' * '.$campos[$i]['discriminacao'];
        ?>
                </a>
        </td>
        <td align='right'>
        <?
            $tipo_moeda = $campos[$i]['tp_moeda'];
            if($tipo_moeda == 1) {
                $tipo_moeda = 'R$ ';
                $total_real+= $total_restante * $campos[$i]['preco_unitario'];
            }else if($tipo_moeda == 2) {
                $tipo_moeda = 'U$ ';
                $total_dolar+= $total_restante * $campos[$i]['preco_unitario'];
            }else {
                $tipo_moeda = '&euro; ';
                $total_euro+= $total_restante * $campos[$i]['preco_unitario'];
            }
            echo $tipo_moeda.number_format($campos[$i]['preco_unitario'], 2, ',', '.');
        ?>
        </td>
        <td align='right'>
        <?
//Imprime o Cálculo da Linha Corrente
            echo $tipo_moeda.number_format($total_restante * $campos[$i]['preco_unitario'], 2, ',', '.');
        ?>
        </td>
        <td onclick="nova_janela('../itens.php?id_pedido=<?=$campos[$i]['id_pedido'];?>&pop_up=1', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')">
            <a href="#" title="Detalhes de Pedido" class="link">
                <?=$campos[$i]['id_pedido'];?>
            </a>
        <?
            if($campos[$i]['tipo_export'] == 'E') {
                echo '<font color="red"><b>(Exp)</b></font>';
            }else if($campos[$i]['tipo_export'] == 'I') {
                echo '<font color="red"><b>(Imp)</b></font>';
            }else if($campos[$i]['tipo_export'] == 'N') {
                echo '<font color="red"><b>(Nac)</b></font>';
            }
            //Aqui eu verifico se existe Importação atrelada com o Pedido ...
            $sql = "SELECT `nome` 
                    FROM `importacoes` i 
                    INNER JOIN `pedidos` p ON p.`id_importacao` = i.`id_importacao` 
                    WHERE p.`id_pedido` = '".$campos[$i]['id_pedido']."' LIMIT 1 ";
            $campos_importacao = bancos::sql($sql);
            if(count($campos_importacao) == 1) {//Se existir importação, printa o nome
                echo ' / <font color="red"><b>'.$campos_importacao[0]['nome'].'</b></font>';
            }
        ?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>                
        <td>
            <?=data::datetodata($campos[$i]['prazo_entrega'], '/');?>
        </td>
        <td>
            <?=$campos[$i]['marca'];?>
        </td>
        <td>
        <?
            if($total_entregue > 0) {
        ?>
                <input type='radio' name='opt_item_pedido' value='<?=$campos[$i]['id_item_pedido'];?>' onclick="options('form', 'opt_item_pedido','<?=$pular;?>', '#E8E8E8');igualar('<?=$pular;?>')">
        <?
            }else {
        ?>
                <input type='hidden' name='opt_item_pedido'>
        <?
            }
        ?>
        </td>
    </tr>
<?
					$pular++;
                }
            }
//Cálculo do Total Geral de Pendência em R$ independente da Moeda ...
            $valor_total = $total_real + ($total_dolar * $fator_importacao * $dolar_dia) + ($total_euro * $fator_importacao * $dolar_dia);
?>
    <tr align='right'>
        <td class='linhacabecalho' colspan="4">
            U$ do Dia: 
            <font color="yellow">
                <?=number_format($dolar_dia, 4, ',', '.');?>
            </font>
        </td>
        <td class='linhacabecalho' colspan="4">
            &euro;$ do Dia: 
            <font color="yellow">
                <?=number_format($euro_dia, 4, ',', '.');?>
            </font>
        </td>
        <td class='linhacabecalho' colspan="6">
            Fator de Importação: 
            <font color="yellow">
                <?=number_format($fator_importacao, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr align='right'>
        <td class='linhadestaque' colspan='8'>
            Total R$ 
            <font color="yellow">
                <?=number_format($total_real, 2, ',', '.');?>
            </font>&nbsp;&nbsp;&nbsp;&nbsp;
            -&nbsp;&nbsp;&nbsp;&nbsp;Total U$ 
            <font color="yellow">
                <?=number_format($total_dolar, 2, ',', '.');?>
            </font>&nbsp;&nbsp;&nbsp;&nbsp;
            -&nbsp;&nbsp;&nbsp;&nbsp;Total &euro;$ 
            <font color="yellow">
                <?=number_format($total_euro, 2, ',', '.');?>
            </font>
        </td>
        <td class='linhadestaque' colspan='6'>
            Valor Total R$ 
            <font color="yellow">
                <?=number_format($valor_total, 2, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'pedidos_pendentes.php'" class='botao'>
            <input type='submit' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
            <input type='button' name='cmd_concluir_item' value='Concluir Item' title='Concluir Item' onclick='return validar(2)' class='botao'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' class='botao'>
            <input type='button' name='cmd_toda_pendencia_em_excel' value='Gerar Toda Pendência em Excel' title='Gerar Toda Pendência em Excel' onclick="nova_janela('gerar_toda_pendencia_em_excel.php?id_fornecedor=<?=$campos[0]['id_fornecedor'];?>&sql_principal='+document.form.sql_principal.value, 'GERAR_PENDENCIA_PEDIDOS_EXCEL', '', '', '', '', 350, 780, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:red' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='tela' value='<?=$tela;?>'>
<input type='hidden' name='txt_consultar' value='<?=$txt_consultar;?>'>
<input type='hidden' name='num_opcao' value='<?=$num_opcao;?>'>
<input type='hidden' name='id_item_pedido'>
<!--*****Esse Hidden só terá utilidade quando tentarmos gerar a Pendência do Fornecedor em Excel*****-->
<?
/*Trato esta variável "$sql_principal" p/ tentar passá-la por parâmetro quando gerarmos a 
Pendência do Fornecedor em Excel ...*/
    $sql_principal = str_replace(' ', '|', $sql_principal);
    $sql_principal = str_replace("'", '!', $sql_principal);
    $sql_principal = str_replace('%', ':', $sql_principal);
?>
<input type='hidden' name='sql_principal' value='<?=$sql_principal;?>'>
<!--*************************************************************************************************-->
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
        }
    }
}else if($passo == 2) {
////////////////////////////TELA SOMENTE DE ITENS DE PEDIDOS/////////////////////
    $sql = "Select ip.*, u.sigla, g.referencia, pi.discriminacao, p.* 
            from itens_pedidos ip 
            inner join pedidos p on p.id_pedido = ip.id_pedido 
            inner join produtos_insumos pi on pi.id_produto_insumo = ip.id_produto_insumo 
            inner join unidades u on u.id_unidade = pi.id_unidade 
            inner join grupos g on g.id_grupo = pi.id_grupo 
            where ip.id_pedido = '$opt_pedido' 
            and ip.status < 2 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function igualar(indice) {
	var controle = 0, existe = 0, liberado = '', codigo = '', cont = 0
	var elemento = '', objeto = ''
	for(i = 0; i < document.form.elements.length; i++) {
		if(document.form.elements[i].type == 'radio') {
			cont ++
		}
	}
	for(i = 0; i < document.form.elements.length; i++) {
		if(document.form.elements[i].type == 'hidden' && document.form.elements[i].name == 'opt_item_pedido') {
			existe ++
		}
	}
	if(cont > 1) {
		elemento = document.form.opt_item_pedido[indice].value
		objeto = document.form.opt_item_pedido[indice]
	}else {
		if(existe == 0) {
			elemento = document.form.opt_item_pedido.value
			objeto = document.form.opt_item_pedido
		}else {
			elemento = document.form.opt_item_pedido[indice].value
			objeto = document.form.opt_item_pedido[indice]
		}
	}
	if(objeto.type == 'radio') {
		for(i = 0; i < elemento.length; i ++) {
			if(elemento.charAt(i) == '|') {
				controle ++
			}else {
				if(controle == 1) {
					liberado = liberado + elemento.charAt(i)
				}else {
					codigo = codigo + elemento.charAt(i)
				}
			}
		}
		document.form.id_item_pedido.value = codigo
	}else {
		limpar_radio()
	}
}

function limpar_radio() {
	for(i = 0; i < document.form.elements.length; i++) {
		if(document.form.elements[i].type == 'radio') {
			document.form.elements[i].checked = false
		}
	}
}

function validar() {
	var cont = 0
	for(i = 0; i < document.form.elements.length; i++) {
		if(document.form.elements[i].type == 'radio') {
			if(document.form.elements[i].checked == true) {
				cont ++
			}
		}
	}
	if(cont == 0) {
		alert('SELECIONE UMA OPÇÃO !')
		return false
	}
}
</Script>
</head>
<body>
<?
	if($linhas == 0) {
?>
<table width='90%' border='0' align='center' cellspacing="1" cellpadding="1">
    <tr align='center'>
        <td>
            <?=$mensagem[4];?>
        </td>
    </tr>
    <tr>
        <td>
            &nbsp;
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'pedidos_pendentes.php?passo=1&txt_consultar=<?=$txt_consultar;?>&num_opcao=<?=$num_opcao;?>&tela=<?=$tela;?>'" class='botao'>
        </td>
    </tr>
</table>
<?
	}else {
?>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=3';?>" onsubmit="return validar()">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
                Itens do Pedido N.º 
                <font color='yellow'>
                    <?=$opt_pedido;?>
                </font>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qtde Sol
        </td>
        <td>
            Qtde Rec
        </td>
        <td>
            Qtde Rest
        </td>
        <td>
            Un
        </td>
        <td>
            Produto
        </td>
        <td>
            Preço
        </td>
        <td>
            Total
        </td>
        <td>
            Marca
        </td>
        <td>
            Selecionar
        </td>
    </tr>
<?
		for($i = 0; $i < $linhas; $i++) {
//Verifico em Nota Fiscal, a Qtde Entregue do Item de Pedido Corrente ...
			$sql = "SELECT sum(nfeh.qtde_entregue) as total_entregue 
					from itens_pedidos ip 
					inner join pedidos p on p.id_pedido = ip.id_pedido 
					inner join nfe_historicos nfeh on nfeh.id_item_pedido = ip.id_item_pedido 
					where ip.id_item_pedido = '".$campos[$i]['id_item_pedido']."' ";
			$campos2 = bancos::sql($sql);
			$total_entregue = $campos2[0]['total_entregue'];
//Nessa variável eu verifico o quanto que ainda resta para Entregar daquele Item ...
			$total_restante = $campos[$i]['qtde'] - $total_entregue;
?>
	<tr class='linhanormal' onclick="options('form', 'opt_item_pedido', '<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=number_format($total_entregue, 2, ',', '.');?>
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=number_format($total_restante, 2, ',', '.');?>
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
				<?=$campos[$i]['sigla'];?>
			</font>
		</td>
		<td align='left'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
			<?
				echo genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia']).' * ';
				echo $campos[$i]['discriminacao'];
			?>
			</font>
		</td>
		<td align='right'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
			<?
				$tipo_moeda = $campos[$i]['tp_moeda'];
				if($tipo_moeda == 1) {
					$tipo_moeda = 'R$ ';
				}else if($tipo_moeda == 2) {
					$tipo_moeda = 'U$ ';
				}else {
					$tipo_moeda = "&euro;";
				}
				echo $tipo_moeda.number_format($campos[$i]['preco_unitario'], 2, ',', '.');
			?>
			</font>
		</td>
		<td align='right'>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
			<?
//Imprime o Cálculo da Linha Corrente
				echo $tipo_moeda.number_format($total_restante * $campos[$i]['preco_unitario'], 2, ',', '.');
			?>
			</font>
		</td>
		<td>
			<font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
			<?
				if($campos[$i]['marca'] != '') {
					echo $campos[$i]['marca'];
				}else {
			?>
					&nbsp;
			<?
				}
			?>
			</font>
		</td>
		<td align='center'>
		<?
			if($total_entregue > 0) {
		?>
				<input type='radio' name='opt_item_pedido' value="<?=$campos[$i]['id_item_pedido'];?>" onclick="options('form', 'opt_item_pedido','<?=$i;?>', '#E8E8E8');igualar('<?=$i;?>')">
		<?
			}else {
		?>
				<input type='hidden' name='opt_item_pedido'>
		<?
			}
		?>
		</td>
	</tr>
<?
		}
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan='10'>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location ='pedidos_pendentes.php?passo=1&txt_consultar=<?=$txt_consultar;?>&num_opcao=<?=$num_opcao;?>&tela=<?=$tela;?>'" class='botao'>
			<input type='submit' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
		</td>
	</tr>
</table>
<input type='hidden' name='tela' value='<?=$tela;?>'>
<input type='hidden' name='txt_consultar' value='<?=$txt_consultar;?>'>
<input type='hidden' name='num_opcao' value='<?=$num_opcao;?>'>
<input type='hidden' name='id_item_pedido'>
<input type='hidden' name='opt_pedido' value='<?=$opt_pedido;?>'>
</form>
<?
		if($limite != '') {
?>
	<center>
		<?=paginacao::print_paginacao('sim');?>
	</center>
<?
		}
	}
?>
</body>
</html>
<?
}else if($passo == 3) {
//TELA SOMENTE DE NOTAS FISCAIS
	$sql = "Select nfe.*, f.razaosocial 
			from nfe_historicos nfeh 
			inner join nfe on nfe.id_nfe = nfeh.id_nfe 
			inner join fornecedores f on f.id_fornecedor = nfe.id_fornecedor 
			where nfeh.id_item_pedido = '$id_item_pedido' ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
?>
<html>
<head>
<title>.:: Liberar Nota ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
	if(!option('form', 'opt_nfe', 'SELECIONE UMA OPÇÃO !')) {
		return false
	}
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=4';?>" onsubmit="return validar()">
<table width='90%' border="0" align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Nota(s) Fiscal(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Fornecedor
        </td>
        <td>
            N.&ordm; Nota
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Data de Entrega
        </td>
        <td>
            Selecionar
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
?>
	<tr class='linhanormal' onclick="options('form', 'opt_nfe', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')"  align='center'>
		<td align="left">
			<?=$campos[$i]['razaosocial'];?>
		</td>
		<td>
			<?=$campos[$i]['num_nota'];?>
		</td>
		<?
			if(substr($campos[$i]['data_emissao'], 0, 10) == '0000-00-00') {
				$data_emissao = '';
			}else {
				$data_emissao = data::datetodata(substr($campos[$i]['data_emissao'], 0, 10), '/');
			}
		?>
		<td>
			<?=$data_emissao;?>
		</td>
		<?
			if(substr($campos[$i]['data_entrega'], 0, 10) == '0000-00-00') {
				$data_entrega = '';
			}else {
				$data_entrega = data::datetodata(substr($campos[$i]['data_entrega'], 0, 10), '/');
			}
		?>
		<td>
			<?=$data_entrega;?>
		</td>
		<td>
			<input type='radio' name="opt_nfe" value="<?=$campos[$i]['id_nfe'];?>" onclick="options('form', 'opt_nfe','<?=$i;?>', '#E8E8E8')">
		</td>
	</tr>
<?
	}
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan='6'>
		<?
			if($tela == 1) {
		?>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location ='pedidos_pendentes.php?passo=2&txt_consultar=<?=$txt_consultar;?>&num_opcao=<?=$num_opcao;?>&opt_pedido=<?=$opt_pedido;?>&tela=<?=$tela;?>'" class='botao'>
		<?
			}else {
		?>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location ='pedidos_pendentes.php?passo=1&txt_consultar=<?=$txt_consultar;?>&num_opcao=<?=$num_opcao;?>&tela=<?=$tela;?>'" class='botao'>
		<?
			}
		?>
			<input type='submit' name='cmd_avançar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
		</td>
	</tr>
</table>
<input type='hidden' name='tela' value='<?=$tela;?>'>
<input type='hidden' name='txt_consultar' value='<?=$txt_consultar;?>'>
<input type='hidden' name='num_opcao' value='<?=$num_opcao;?>'>
<input type='hidden' name="id_item_pedido" value="<?=$id_item_pedido;?>">
<input type='hidden' name="opt_pedido" value="<?=$opt_pedido;?>">
</form>
<?
	if($limite != '') {
?>
	<center>
		<?=paginacao::print_paginacao('sim');?>
	</center>
<?
	}
?>
</body>
</html>
<?
}else if($passo == 4) {
	$sql = "Select nfe.num_nota, nfeh.*, p.* 
			from nfe_historicos nfeh 
			inner join nfe on nfe.id_nfe = nfeh.id_nfe 
			inner join pedidos p on p.id_pedido = nfeh.id_pedido 
			where nfeh.id_nfe = '$opt_nfe' ";
	$campos = bancos::sql($sql);
	$linhas = count($campos);
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)' align='center'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Itens da Nota Fiscal N.º 
            <font color='yellow'>
                <?=$campos[0]['num_nota'];?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N/E
        </td>
        <td>
            Qtde
        </td>
        <td>
            Produto
        </td>
        <td>
            Pre&ccedil;o Unit.
        </td>
        <td>
            Valor Total
        </td>
        <td>
            IPI
        </td>
        <td>
            N.º Ped.
        </td>
    </tr>
<?
		$valor_total = 0;
		for($i = 0; $i < $linhas; $i++) {
?>
	<tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
		<td>
			<?=$campos[$i]['num_nota'];?>
		</td>
		<td>
			<?=number_format($campos[$i]['qtde_entregue'], 2, ',', '.');?>
		</td>
		<?
			$id_item_do_pedido = $campos[$i]['id_item_pedido'];
			$sql = "Select g.referencia, pi.id_produto_insumo, pi.discriminacao 
					from itens_pedidos ip 
					inner join produtos_insumos pi on pi.id_produto_insumo = ip.id_produto_insumo 
					inner join grupos g on g.id_grupo = pi.id_grupo 
					where ip.id_item_pedido = '$id_item_do_pedido' limit 1 ";
			$campos2 = bancos::sql($sql);
			$tipo_moeda = $campos[$i]['tp_moeda'];
			if($tipo_moeda == 1) {
				$tipo_moeda = 'R$ ';
			}else if($tipo_moeda == 2) {
				$tipo_moeda = 'U$ ';
			}else {
				$tipo_moeda = '&euro; ';
			}
		?>
		<td align="left">
		<?
			echo genericas::buscar_referencia($campos2[0]['id_produto_insumo'], $campos2[0]['referencia']).' * ';
			echo $campos2[0]['discriminacao'];
		?>
		</td>
		<td align='right'>
			<?=$tipo_moeda.number_format($campos[$i]['valor_entregue'], '2', ',', '.');?>
		</td>
		<?
			$qtde = $campos[$i]['qtde_entregue'];
			$valor = $campos[$i]['valor_entregue'];
			$total = $qtde * $valor;
			$valor_total+= $total;
		?>
		<td align='right'>
			<?=$tipo_moeda.number_format($total, '2', ',', '.');?>
		</td>
		<td>
			<?=$campos[$i]['ipi_entregue'];?>
		</td>
		<td onclick="nova_janela('../itens.php?id_pedido=<?=$campos[$i]['id_pedido'];?>&pop_up=1', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')">
			<a href="#" title="Detalhes de Pedido" class="link">
				<?=$campos[$i]['id_pedido'];?>
			</a>
		</td>
	</tr>
<?
	}
?>
	<tr class='linhacabecalho' align='center'>
		<td colspan='7'>
			<input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'pedidos_pendentes.php?passo=3&txt_consultar=<?=$txt_consultar;?>&num_opcao=<?=$num_opcao;?>&id_item_pedido=<?=$id_item_pedido;?>&opt_pedido=<?=$opt_pedido;?>&tela=<?=$tela;?>'" class='botao'>
		</td>
	</tr>
</table>
</body>
</html>
<?
}else if($passo == 5) {//TELA PARA CONCLUIR PEDIDO
//Verifica se o Pedido está importado em NF ...
    $sql = "SELECT nfe.num_nota, nfeh.status 
            FROM `nfe_historicos` nfeh 
            INNER JOIN `nfe` ON nfe.id_nfe = nfeh.id_nfe 
            WHERE nfeh.`id_pedido` = '$id_pedido' 
            AND nfeh.`status` = '0' LIMIT 1 ";
    $campos_nf = bancos::sql($sql);
    if(count($campos_nf) == 1) {
?>
    <Script Language = 'JavaScript'>
        alert('ESTE PEDIDO NÃO PODE SER CONCLUÍDO, PORQUE ESTA NA NOTA <?=$campos_nf[0]['num_nota'];?> !')
        window.location = 'pedidos_pendentes.php?passo=1&txt_consultar=<?=$txt_consultar;?>&num_opcao=<?=$num_opcao;?>&tela=<?=$tela;?>'
    </Script>
<?
        exit;
    }
//Verifica se o Pedido possui Antecipações à Liberar ou Liberada - que é quando não foi importada p/ NF ...
    $sql = "SELECT id_antecipacao 
            FROM `antecipacoes` 
            WHERE `id_pedido` = '$id_pedido' 
            AND `status` IN (0, 1) LIMIT 1 ";
    $campos_antecipacao = bancos::sql($sql);
    if(count($campos_antecipacao) == 1) {
?>
    <Script Language = 'JavaScript'>
        alert('ESTE PEDIDO NÃO PODE SER CONCLUÍDO, PORQUE POSSUI A ANTECIPAÇÃO <?=$campos_antecipacao[0]['id_antecipacao'];?> EM PENDÊNCIA OU LIBERADA !')
        window.location = 'pedidos_pendentes.php?passo=1&txt_consultar=<?=$txt_consultar;?>&num_opcao=<?=$num_opcao;?>&tela=<?=$tela;?>'
    </Script>
<?
        exit;
    }
/***********************************************************************/
/************************Numerário de Importação************************/
/***********************************************************************/
    /*Vejo se esse Pedido é um Numerário que está importado no Financeiro e se esse já foi pago de forma total - se esse ainda 
    não foi pago de forma total - não posso concluir o pedido - lembrando que todo numerário possui uma Importação ...*/
    $sql = "SELECT `id_conta_apagar` 
            FROM `contas_apagares` 
            WHERE `id_pedido` = '$id_pedido' 
            AND `id_importacao` > '0' 
            AND `status` < '2' LIMIT 1 ";
    $campos_contas_apagares = bancos::sql($sql);
    if(count($campos_contas_apagares) == 1) {//Significa que já foi importado p/ o Financeiro ...
?>
    <Script Language = 'JavaScript'>
        alert('ESTE PEDIDO NÃO PODE SER CONCLUÍDO PORQUE ESTE É UM NUMERÁRIO IMPORTADO PELO FINANCEIRO !\n\nCOMO ESTE AINDA NÃO FOI PAGO DE FORMA TOTAL, NÃO É POSSÍVEL A CONCLUSÃO DO MESMO !!!')
        window.location = 'pedidos_pendentes.php?passo=1&txt_consultar=<?=$txt_consultar;?>&num_opcao=<?=$num_opcao;?>&tela=<?=$tela;?>'
    </Script>
<?	
        exit;
    }
    //Busco o "id_fornecedor" atráves do "id_pedido" porque o mesmo será utilizado na Próxima Tela ...
    $sql = "SELECT `id_fornecedor` 
            FROM `pedidos` 
            WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
    $campos_fornecedor = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Pedidos Pendentes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var mensagem = confirm('DESEJA REALMENTE CONCLUIR ESSE PEDIDO ?')
    if(mensagem == true) {
        return true
    }else {
        document.form.txt_observacao.focus()
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_observacao.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=6';?>' onsubmit='return validar()'>
<!--****************Controles de Tela****************-->
<input type='hidden' name='hdd_fornecedor' value='<?=$campos_fornecedor[0]['id_fornecedor'];?>'>
<input type='hidden' name='hdd_pedido' value='<?=$id_pedido;?>'>
<input type='hidden' name='tela' value='<?=$tela;?>'>
<!--*************************************************-->
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Concluir Pedido N.&ordm; 
            <font color='yellow'>
                <?=$id_pedido;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.&ordm; Pedido:
        </td>
        <td>
            <?=$id_pedido;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observa&ccedil;&atilde;o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='50' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'pedidos_pendentes.php?passo=1&txt_consultar=<?=$txt_consultar;?>&num_opcao=<?=$num_opcao;?>&tela=<?=$tela;?>'" class='botao'>
            <input type='submit' name='cmd_concluir' value='Concluir Pedido' title='Concluir Pedido' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 6) {
//Registrando um Follow-UP para o Pedido que está sendo concluido ...
    $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `data_entrega_embarque`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[hdd_fornecedor]', '$_SESSION[id_funcionario]', '$_POST[hdd_pedido]', '16', '".date('Y-m-d')."', '".strtolower($_POST['txt_observacao'])."', '".date('Y-m-d H:i:s')."') ";
    bancos::sql($sql);
    
//Vasculho todos os Itens do Pedido "Em Aberto" e "Parcial" p/ poder Concluir ...
    $sql = "SELECT id_item_pedido, id_produto_insumo 
            FROM `itens_pedidos` 
            WHERE `id_pedido` = '$_POST[hdd_pedido]' 
            AND `status` < '2' ";
    $campos_itens = bancos::sql($sql);
    $linhas_itens = count($campos_itens);
    for($i = 0; $i < $linhas_itens; $i++) {
        //Concluindo o Item do Pedido do Loop ...
        $sql = "UPDATE `itens_pedidos` SET `status` = '2' WHERE `id_item_pedido` = '".$campos_itens[$i]['id_item_pedido']."' LIMIT 1 ";
	bancos::sql($sql);
        //Aqui verifico se o PI é um PA "PIPA" para poder executar a função abaixo ...
        $sql = "SELECT id_produto_acabado 
                FROM `produtos_acabados` 
                WHERE `id_produto_insumo` = '".$campos_itens[$i]['id_produto_insumo']."' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_pipa = bancos::sql($sql);
        if(count($campos_pipa) == 1) intermodular::gravar_campos_para_calcular_margem_lucro_estimada($campos_itens[$i]['id_produto_insumo']);
    }
//Concluindo o Pedido e os Itens de Pedido ...
    $sql = "UPDATE `pedidos` SET status = '2' WHERE `id_pedido` = '$_POST[hdd_pedido]' LIMIT 1 ";
    bancos::sql($sql);
//Aki eu verifico se o Pedido tem alguma OS atrelada a este ...
    $sql = "SELECT id_os 
            FROM `oss` 
            WHERE `id_pedido` = '$_POST[hdd_pedido]' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $id_os  = $campos[0]['id_os'];
//Caso exista OS, então eu atualizo o status da OS como sendo encerrado ...
    if($id_os != 0) {
//Matando os Itens da OS ...
        $sql = "UPDATE `oss_itens` SET `status` = '2' WHERE `id_os` = '$id_os' ";
        bancos::sql($sql);
//No restante essa função se encarrega de tudo p/ saber se a OS continua em aberto ou não ...
/*****************Controle com o Status da OS*****************/
        producao::atualizar_status_os($id_os);
/*************************************************************/
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'pedidos_pendentes.php?valor=1'
    </Script>
<?
}else if($passo == 7) {
//Concluindo apenas o Item de Pedido passado por parâmetro ...
    $sql = "SELECT nfe.num_nota, nfeh.status 
            FROM `nfe_historicos` nfeh 
            INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
            WHERE nfeh.`id_item_pedido` = '$id_item_pedido' 
            AND nfeh.`status` = '0' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {
?>
	<Script Language = 'Javascript'>
            alert('ESTE ITEM DE PEDIDO NÃO PODE SER CONCLUÍDO, PORQUE ESTA NA NOTA <?=$campos[0]['num_nota'];?> !')
            window.location = 'pedidos_pendentes.php<?=$parametro;?>'
	</Script>
<?
    }else {
//Verifico qual é o Pedido do item de Pedido, vou utiliza esse id_pedido depois ...
        $sql = "SELECT id_pedido, id_produto_insumo 
                FROM `itens_pedidos` 
                WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $id_pedido          = $campos[0]['id_pedido'];
        $id_produto_insumo  = $campos[0]['id_produto_insumo'];
//Concluo com item de Pedido passado por parâmetro pelo usuário ...
        $sql = "UPDATE `itens_pedidos` SET `status` = '2' WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
        bancos::sql($sql);
//Aqui verifico se o PI é um PA "PIPA" para poder executar a função abaixo ...
        $sql = "SELECT id_produto_acabado 
                FROM `produtos_acabados` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' 
                AND `ativo` = '1' LIMIT 1 ";
        $campos_pipa = bancos::sql($sql);
        if(count($campos_pipa) == 1) intermodular::gravar_campos_para_calcular_margem_lucro_estimada($id_produto_insumo);
//Essa verificação consiste em saber se eu devo concluir o Pedido também ou Não ...
        $sql = "SELECT id_item_pedido 
                FROM `itens_pedidos` 
                WHERE `id_pedido` = '$id_pedido' 
                AND `status` < '2' LIMIT 1 ";
        $campos = bancos::sql($sql);
//Significa que não há + nenhum Item de Pedido Pendente ...
        if(count($campos) == 0) {
//Verifica se o Pedido possui Antecipações em Pendência ou Liberada - que é quando não foi importada p/ NF ...
            $sql = "SELECT id_antecipacao 
                    FROM `antecipacoes` 
                    WHERE `id_pedido` = '$id_pedido' 
                    AND `status` IN (0, 1) LIMIT 1 ";
            $campos_antecipacao = bancos::sql($sql);
            if(count($campos_antecipacao) == 1) {
/*Atualizando o status do Pedido para parcial, porque mesmo tendo fechado todos os Itens, 
existem Antecipações em Pendência ...*/
                $sql = "UPDATE `pedidos` SET `status` = '1' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
                bancos::sql($sql);
?>
                <Script Language = 'JavaScript'>
                    alert('ESTE PEDIDO NÃO PODE SER CONCLUÍDO, PORQUE POSSUI A ANTECIPAÇÃO <?=$campos_antecipacao[0]['id_antecipacao'];?> EM PENDÊNCIA OU LIBERADA !')
                </Script>
<?
                }else {
//Atualizando o status do Pedido para concluido, significa que todos os Itens de Pedido estão concluidos ...
                    $sql = "UPDATE `pedidos` SET `status` = '2' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
                    bancos::sql($sql);
                }
//Significa que ainda existem Itens de Pedido Pendentes ...
        }else {
//Atualizando o status do Pedido para parcial, significa que ainda existem Itens de Pedido em aberto ...
            $sql = "UPDATE `pedidos` SET `status` = '1' WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
            bancos::sql($sql);
        }
/***********************************************OS*************************************************/
//Aki eu verifico se o Pedido tem alguma OS atrelada a este ...
        $sql = "SELECT id_os 
                FROM `oss` 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $id_os  = $campos[0]['id_os'];
//Caso exista OS, então eu atualizo o status da OS como sendo encerrado ...
        if($id_os != 0) {
/***********************************************OS*************************************************/
//Aqui eu mato o Item da OS primeiramente ...
            $sql = "UPDATE `oss_itens` SET `status` = '2' WHERE `id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
            bancos::sql($sql);
//No restante essa função se encarrega de tudo p/ saber se a OS continua em aberto ou não ...
/*****************Controle com o Status da OS*****************/
            producao::atualizar_status_os($id_os);
/*************************************************************/
        }
?>
    <Script Language = 'Javascript'>
        window.location = 'pedidos_pendentes.php<?=$parametro;?>&valor=2'
    </Script>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Pedidos Pendentes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar(valor) {
    if(valor == 1) {
        if(document.form.opcao[0].checked == true) document.form.opcao[1].checked = false
    }else if(valor == 2) {
        if(document.form.opcao[1].checked == true) document.form.opcao[0].checked = false
    }else {
        document.form.opcao[0].checked = false
        document.form.opcao[1].checked = false
    }
    
    document.form.txt_consultar.value   = ''
    
    if(document.form.opcao[0].checked == true || document.form.opcao[1].checked == true) {
        for(i = 0; i < 8; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        for(i = 0; i < 8; i++) document.form.opt_opcao[i].disabled = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function iniciar() {
    if(document.form.txt_consultar.disabled == false) document.form.txt_consultar.focus()
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
<body onload='iniciar()'>
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Pedidos Pendentes
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name="txt_consultar" title="Consultar Pedido" size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td width='20%'>
            <font color='blue' size='-1'>
                <b>Relatório por Pedido(s)</b>
            </font>
        </td>
        <td width='20%'>
            <font color='blue' size='-1'>
                <b>Relatório por Itens de Pedido(s)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title="Consultar por: Empresa" onclick='iniciar()' id='label'>
            <label for="label">Empresa</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title="Consultar por: Referência" onclick='iniciar()' id='label2'>
            <label for="label2">Referência do PI</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='3' title="Consultar por: Fornecedor" onclick='iniciar()' id='label3'>
            <label for="label3">Fornecedor</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='4' title="Consultar por: Referência do PA" onclick='iniciar()' id='label4'>
            <label for="label4">Referência do PA</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='5' title="Consultar por: N.º Pedido" onclick='iniciar()' id='label5'>
            <label for="label5">N.º Pedido / <b>(Concluir)</b></label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='6' title="Consultar por: Discriminação" onclick='iniciar()' id='label6' checked>
            <label for="label6">Discriminação</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            &nbsp;
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='7' title='Marca / Obs' onclick='iniciar()' id='label7'>
            <label for='label7'>Marca / Obs</b></label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            &nbsp;
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='8' title='Consultar por: N.º Pedido' onclick='iniciar()' id='label8'>
            <label for='label8'>N.º Pedido / <b>(Concluir Itens)</b></label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            &nbsp;
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='9' title='Consultar por: Fornecedor' onclick='iniciar()' id='label9'>
            <label for='label9'>Fornecedor</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='10' title='Consultar todos os pedidos' onclick='limpar(1)' class='checkbox' id='label10'>
            <label for='label10'>Todos os pedidos</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='11' title='Consultar todos os itens de pedido' onclick='limpar(2)' class='checkbox' id='label11'>
            <label for='label11'>Todos os itens de pedido</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar(3)' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
<?}?>