<?
//Função que contém a Tela Principal de Filtro ...
function filtro($nivel_arquivo_principal, $valor) {
    $mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
    $mensagem[2] = "<font class='confirmacao'>REPRESENTANTE ALTERADO COM SUCESSO.</font>";
    $mensagem[3] = "<font class='atencao'>REPRESENTANTE JÁ EXISTENTE.</font>";
    $mensagem[4] = "<font class='confirmacao'>REPRESENTANTE(S) EXCLUIDO(S) COM SUCESSO.</font>";
    $mensagem[5] = "<font class='erro'>ESSE REPRESENTANTE(S) NÃO PODE SER EXCLUIDO, DEVIDO O MESMO POSSUIR CLIENTE(S) EM CARTEIRA.</font>";
?>
<html>
<head>
<title>.:: Filtro de Representante(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '<?=$nivel_arquivo_principal;?>css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' Src = '<?=$nivel_arquivo_principal;?>js/geral.js'></Script>
<Script Language = 'Javascript' Src = '<?=$nivel_arquivo_principal;?>js/validar.js'></Script>
</head>
<body onLoad="document.form.txt_codigo_representante.focus()">
<form name='form' method='post' action=''>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
            <td colspan='2'>
                    <b><?=$mensagem[$valor];?></b>
            </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
            <td colspan="2">
                Filtro de Representante(s)
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Código do Representante
            </td>
            <td>
                    <input type="text" name="txt_codigo_representante" title="Digite o Código do Representante" class="caixadetexto">
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Nome do Representante
            </td>
            <td>
                    <input type="text" name="txt_nome_representante" title="Digite o Nome do Representante" class="caixadetexto">
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Nome Fantasia
            </td>
            <td>
                    <input type="text" name="txt_nome_fantasia" title="Digite o Nome Fantasia" class="caixadetexto">
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Zona de Atuação
            </td>
            <td>
                    <input type="text" name="txt_zona_atuacao" title="Digite a Zona de Atuação" class="caixadetexto">
            </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    Tipo de Representante
            </td>
            <td>
                    <select name="cmb_tipo_representante" title="Selecione o Tipo de Representante" class="combo">
                            <option value="" style="color:red" selected>SELECIONE</option>
                            <option value="S">SUPERVISOR</option>
                            <option value="A">AUTÔNOMO</option>
                            <option value="E">VENDEDOR EXTERNO</option>
                            <option value="I">VENDEDOR INTERNO</option>
                    </select>
            </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Supervisor
        </td>
        <td>
            <select name='cmb_supervisor' title='Selecione o Supervisor' class='combo'>
            <?
                //Só seleciona funcionários que são Representantes, mas que são do Tipo Supervisores
                $sql = "SELECT r.id_representante, f.nome 
                        FROM `representantes_vs_funcionarios` rf 
                        INNER JOIN `representantes` r ON r.`id_representante` = rf.`id_representante` 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = rf.`id_funcionario` AND f.`id_cargo` IN (25, 109) 
                        ORDER BY f.nome ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
            <td>
                    &nbsp;
            </td>
            <td>
                    <input type='checkbox' name='chkt_nao_mostrar_excluidos' value="1" title="Não Mostrar Excluídos" id='label' class="checkbox" checked>
                    <label for='label'>Não Mostrar Excluídos</label>
            </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan="2">
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="document.form.txt_codigo_representante.focus()" class='botao'>
            <input type="submit" name="cmd_consultar" value="Consultar" title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}

//Significa que o usuário já fez pelo menos uma consulta ...
if(!empty($cmd_consultar)) {
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$txt_codigo_representante   = $_POST['txt_codigo_representante'];
		$txt_nome_representante     = $_POST['txt_nome_representante'];
		$txt_nome_fantasia          = $_POST['txt_nome_fantasia'];
		$txt_zona_atuacao           = $_POST['txt_zona_atuacao'];
		$cmb_tipo_representante     = $_POST['cmb_tipo_representante'];
		$cmb_supervisor             = $_POST['cmb_supervisor'];
		$chkt_nao_mostrar_excluidos = $_POST['chkt_nao_mostrar_excluidos'];
	}else {
		$txt_codigo_representante   = $_GET['txt_codigo_representante'];
		$txt_nome_representante     = $_GET['txt_nome_representante'];
		$txt_nome_fantasia          = $_GET['txt_nome_fantasia'];
		$txt_zona_atuacao           = $_GET['txt_zona_atuacao'];
		$cmb_tipo_representante     = $_GET['cmb_tipo_representante'];
		$cmb_supervisor             = $_GET['cmb_supervisor'];
		$chkt_nao_mostrar_excluidos = $_GET['chkt_nao_mostrar_excluidos'];
	}
//Eu tratei essa estrutura assim desse jeito para não dar erro no default do switch ...
	$condicao = (!empty($chkt_nao_mostrar_excluidos)) ? ' AND r.`ativo` = 1 ' : ' AND r.ativo IN (0, 1) ';

//Se o Tipo de Representante selecionado foi Supervisor, então ...
	if($cmb_tipo_representante == 'S') {
		$inner_join_rep_funcs = "inner join representantes_vs_funcionarios rf on rf.id_representante = r.id_representante 
                                        inner join funcionarios f on f.id_funcionario = rf.id_funcionario and f.id_cargo = '25' ";
//Se o Tipo de Representante selecionado foi Autônomo, então ...
	}else if($cmb_tipo_representante == 'A') {
/*Aqui eu faço a busca de todos os representantes que são funcionários p/ que mais abaixo eu não liste esses 
representantes ...*/
		$sql = "SELECT id_representante 
                        FROM `representantes_vs_funcionarios` ";
		$campos_rep_func = bancos::sql($sql);
		$linhas_rep_func = count($campos_rep_func);
		for($l = 0; $l < $linhas_rep_func; $l++) {$id_rep_funcs[] = $campos_rep_func[$l]['id_representante'];}
//Arranjo Ténico
		if(count($id_rep_funcs) == 0) {$id_rep_funcs[]='0';}
		$vetor_rep_funcs = implode(',', $id_rep_funcs);
		$condicao_rep_funcs = " AND `id_representante` NOT IN ($vetor_rep_funcs) ";
//Se o Tipo de Representante selecionado foi Vendedor Externo, então ...
	}else if($cmb_tipo_representante == 'E') {
		$inner_join_rep_funcs = "INNER JOIN `representantes_vs_funcionarios` rf ON rf.id_representante = r.id_representante 
                                        INNER JOIN `funcionarios` f ON f.id_funcionario = rf.id_funcionario AND f.id_cargo = '27' ";
//Se o Tipo de Representante selecionado foi Vendedor Interno, então ...
	}else if($cmb_tipo_representante == 'I') {
		$inner_join_rep_funcs = "INNER JOIN `representantes_vs_funcionarios` rf ON rf.id_representante = r.id_representante 
                                        INNER JOIN `funcionarios` f ON f.id_funcionario = rf.id_funcionario AND f.id_cargo = '47' ";
	}
//Aqui eu listo todos os Representantes do Representante Superior selecionado ...
	if(!empty($cmb_supervisor)) {
		$inner_join_rep_superiores = "INNER JOIN `representantes_vs_supervisores` rs ON rs.id_representante = r.id_representante AND rs.id_representante_supervisor = '$cmb_supervisor' ";
	}
	
	$sql = "SELECT r.* 
                FROM `representantes` r 
                $inner_join_rep_funcs 
                $inner_join_rep_superiores 
                WHERE r.`id_representante` LIKE '%$txt_codigo_representante%' 
                AND r.`nome_representante` LIKE '%$txt_nome_representante%' 
                AND r.`nome_fantasia` LIKE '%$txt_nome_fantasia%' 
                AND r.`zona_atuacao` LIKE '%$txt_zona_atuacao%' 
                $condicao $condicao_rep_funcs 
                ORDER BY r.nome_fantasia ";
	$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
	$linhas = count($campos);
//Não retornou nenhum registro, então requisito a Tela de Filtro ...
	if($linhas == 0) {
            filtro($nivel_arquivo_principal, 1);
	}
}else {
//Quando esse arquivo é requisitado na primeira vez eu chamo a Tela de Filtro ...
    filtro($nivel_arquivo_principal, $valor);
}
?>