<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>MENSALIDADE SINDICAL ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
//Tratamento com os campos p/ poder gravar no BD ...
    $data_emissao = date('Y-m-d');
//Primeiro apaga-se todos os vales do Tipo Mensalidade Sindical p/ poder gerar Novos Vales V�lidos ...
    $sql = "DELETE FROM `vales_dps` 
            WHERE `tipo_vale` = '10' 
            AND `data_debito` = '$cmb_data_holerith' ";
    bancos::sql($sql);
//Aqui nesse loop eu disparo todos os funcion�rios da Empresa selecionada ...
    for($i = 0; $i < count($hdd_funcionario); $i++) {
//Se o Valor do vale <> 0, ent�o eu gero vale para esse funcion�rio ...
        if($txt_vlr_fatura[$i] != 0.00) {
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '$hdd_funcionario[$i]', '10', '$txt_vlr_fatura[$i]', '$cmb_data_holerith', '$data_emissao', 'PD', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir_alterar.php?cmb_data_holerith=<?=$cmb_data_holerith;?>&valor=1'
    </Script>
<?
}else {
/****************************************************************************************************/
//Aqui eu j� deixo carregada essa vari�vel porque vou estar utilizando essa nos c�lculos em PHP e JavaScript
    $mensalidade_sindical = genericas::variavel(32);
/*Listagem de Funcion�rios que ainda est�o trabalhando e que est�o com a marca��o de Mensalidade Sindical ...
* S� n�o exibo os funcion�rios Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes n�o s�o funcion�rios, simplesmente s� possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...
Tamb�m n�o exibo nenhum funcion�rio que seje da Empresa Grupo -> id 4, at� porque esse tipo de Desconto
s� � feito em cima dos funcion�rios que sejam registrados, ou seja, s� existe desconto do PD*/
    $sql = "SELECT `id_funcionario`, `id_empresa`, `nome` 
            FROM `funcionarios` 
            WHERE `status` < '3' 
            AND `debitar_mensal_sindical` = 'S' 
            AND `id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
            AND `id_empresa` <> '4' ORDER BY `nome` ";
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//N�o encontrou nenhum funcion�rio com essa marca��o ...
?>
        <Script Language = 'Javascript'>
            window.location = '../itens/incluir.php?valor=1'
        </Script>
<?
        exit;
    }
?>
<html>
<head>
<title>.:: Incluir / Alterar Vale(s) - Mensalidade Sindical ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Tratamento com o restante dos Objetos ...
	var elementos = document.form.elements
	var objetos_inicio = 3//Qtde de objetos antes do loop
	var objetos_linha = 2//Qtde de objetos que eu tenho por linha
	var objetos_fim = 5//Qtde de objetos ap�s do loop
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
	document.form.nao_atualizar.value = 1
	document.form.passo.value = 1
	for (var i = objetos_inicio; i < (elementos.length) - objetos_fim; i+=objetos_linha) {
//Tratamento no objeto Vlr Fatura p/ gravar os objetos no BD ...
		elementos[i].value = strtofloat(elementos[i].value)
//Desabilita este campo p/ poder gravar no BD ...
		elementos[i].disabled = false
	}
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF;?>' onsubmit="return validar()">
<!--Esse hidden � um controle de Tela-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
	<tr align="center">
		<td colspan="3">
			<b><?=$mensagem[$valor];?></b>
		</td>
	</tr>
	<tr class='linhacabecalho' align='center'>
		<td colspan="3">
			<font color='#FFFFFF' size='-1'>
				Incluir / Alterar Vale(s) - Mensalidade Sindical
			</font>
		</td>
	</tr>
	<tr class="linhacabecalho">
		<td>
			<font color="yellow">
				Data de Holerith: 
			</font>
			<?=data::datetodata($cmb_data_holerith, '/');?>
		</td>
		<td colspan="2">
			<font color="yellow">
				Mensalidade Sindical: 
			</font>
			<?='R$ '.number_format($mensalidade_sindical, 2, ',', '.');?>
		</td>
	</tr>
	<tr class="linhadestaque" align="center">
		<td>Funcion�rio</td>
		<td>Empresa</td>
		<td>Vlr Fatura</td>
	</tr>
<?
		for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" da sess�o
			$id_funcionario_loop = $campos[$i]['id_funcionario'];
//C�lculos e controle com o Pop-Up ... 
			$url = "javascript:nova_janela('../../funcionario/detalhes.php?id_funcionario_loop=".$id_funcionario_loop."', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
			$vlr_fatura = $mensalidade_sindical;
?>
	<tr class="linhanormal" align="center">
		<td align="left">
			<a href="#" onclick="<?=$url;?>" title="Detalhes Funcion�rio" class="link">
				<?=$campos[$i]['nome'];?>
			</a>
		</td>
		<td>
			<?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
		</td>
		<td>
			R$ <input type="text" name="txt_vlr_fatura[]" value="<?=number_format($vlr_fatura, 2, ',', '.');?>" title="Valor da Fatura" size="10" class="textdisabled" disabled>
			&nbsp;
			<input type="hidden" name="hdd_funcionario[]" value="<?=$campos[$i]['id_funcionario'];?>">
		</td>
	</tr>
<?
//Essa vari�vel aqui eu apresento mais abaixo no fim do loop ...
		$total_vlr_fatura+= $vlr_fatura;
	}
?>
	<tr class="linhadestaque" align="center">
		<td colspan="2" align="right">
			Total Vlr Fatura R$:
		</td>
		<td>
			<input type="text" name="txt_total_vlr_fatura" value="<?=number_format($total_vlr_fatura, 2, ',', '.');?>" title="Total do Vlr Fatura" size="10" class="textdisabled" disabled>
		</td>
	</tr>
	<tr class="linhacabecalho" align="center">
		<td colspan="3">
			<input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../itens/incluir.php'" class="botao">
			<input type="button" name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="document.form.reset()" style="color:#ff9900;" class="botao">
			<input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class="botao">
			<input type="button" name="cmd_fechar" value="Fechar" title="Fechar" onclick="fechar(window)" style="color:red" class="botao">
		</td>
	</tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>