<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../');

if($passo == 1) {
    foreach($_POST['chkt_representante'] as $id_representante) {
//Aqui eu verifico se o Representante possui algum Cliente que está ativo em Carteira ...
        $sql = "SELECT cr.`id_cliente` 
                FROM `clientes_vs_representantes` cr 
                INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` AND c.`ativo` = '1' 
                WHERE cr.`id_representante` = '$id_representante' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 0) {
            //Inativo esse Representante para que nunca esse possa ser utilizado no Sistema ...
            $sql = "UPDATE `representantes` SET `ativo` = '0' WHERE `id_representante` = '$id_representante' LIMIT 1 ";
            bancos::sql($sql);

            //Verifico se esse Representante que está sendo Inativado é um Funcionário ...
            $sql = "SELECT `id_representante_funcionario`, `id_funcionario` 
                    FROM `representantes_vs_funcionarios` 
                    WHERE `id_representante` = '$id_representante' LIMIT 1 ";
            $campos_representante_funcionario = bancos::sql($sql);
            if(count($campos_representante_funcionario) == 1) {
                /*Limpo esses campos de seu cadastro como funcionário p/ não gerar sujeira de Banco de Dados e dar problemas em alguns relatórios 
                como de Salário por exemplo, também não tem sentido guardar essas infos afinal o mesmo já não estará mais trabalhando conosco ...*/
                $sql = "UPDATE `funcionarios` SET `comissao_ultimos3meses_pd` = '0.00', `comissao_ultimos3meses_pf` = '0.00' WHERE `id_representante` = '$id_representante' LIMIT 1 ";
                bancos::sql($sql);
                //Deleto o Representante da tabela Relacional "representantes_vs_funcionarios" ...
                $sql = "DELETE FROM `representantes_vs_funcionarios` WHERE `id_representante` = '$id_representante' LIMIT 1 ";
                bancos::sql($sql);
            }else {
                genericas::atualizar_representantes_no_site_portal($id_representante);
            }
            $valor = 4;
        }else {
            $valor = 5;
        }
    }
?>
	<Script Language = 'JavaScript'>
		window.location = 'excluir.php?valor=<?=$valor;?>'
	</Script>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
	$nivel_arquivo_principal = '../../../';
//Aqui eu vou puxar a Tela única de Filtro de Notas Fiscais que serve para o Sistema Todo ...
	require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
	if($linhas > 0) {
?>
<html>
<head>
<title>.:: Excluir Representante ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '../../../js/validar.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/geral.js'></Script>
<Script Language = 'Javascript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit="return validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')">
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
	<tr align='center'>
		<td colspan='9'>
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan='9'>
                    Excluir Representante(s)
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>
                    Cód Rep
		</td>
		<td>
                    Nome do Representante
		</td>
		<td>
                    Nome Fantasia
		</td>
		<td>
                    Cargo
		</td>
		<td>
                    Tel Com
		</td>
		<td>
                    Tel Cel / Fax
		</td>
		<td>
                    Zona de Atuação
		</td>
		<td>
                    E-mail
		</td>
		<td>
                    <label for='todos'>Todos </label>
                    <input type="checkbox" name="chkt" onClick="selecionar('form', 'chkt', totallinhas, '#E8E8E8')" title='Selecionar todos' id='todos' class="checkbox">
		</td>
	</tr>
<?
		for ($i = 0;  $i < $linhas; $i++) {
?>
	<tr class="linhanormal" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
		<td align='center'>
			<?=$campos[$i]['id_representante'];?>
		</td>
		<td>
			<?=$campos[$i]['nome_representante'];?>
		</td>
		<td>
			<?=$campos[$i]['nome_fantasia'];?>
		</td>
		<td>
		<?
//Aqui eu verifico se o repres. também é um funcionário, se for retorna o cargo do Funcionário ...
			$sql = "Select c.cargo 
                                from representantes_vs_funcionarios rf 
                                inner join funcionarios f on f.id_funcionario = rf.id_funcionario 
                                inner join cargos c on c.id_cargo = f.id_cargo 
                                where rf.id_representante = ".$campos[$i]['id_representante']." limit 1 ";
			$campos_cargo = bancos::sql($sql);
			if(count($campos_cargo) == 1) {//Significa que é funcionário ...
                            echo $campos_cargo[0]['cargo'];
			}
		?>
		</td>
		<td>
			<?=$campos[$i]['fone'];?>
		</td>
		<td>
			<?=$campos[$i]['fax'];?>
		</td>
		<td>
			<?=$campos[$i]['zona_atuacao'];?>
		</td>
		<td>
			<?=$campos[$i]['email'];?>
		</td>
		<td align='center'>
			<input type="checkbox" name="chkt_representante[]" value="<?=$campos[$i]['id_representante'];?>" onclick="checkbox('form', 'chkt', '<?=$i;?>', '#E8E8E8')" class="checkbox">
		</td>
	</tr>
<?
		}
?>
	<tr class="linhacabecalho" align="center">
            <td colspan='9'>
                <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
                <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
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
}
?>