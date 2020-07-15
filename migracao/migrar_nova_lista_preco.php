<?
$data_hora = date('H:i:s Y-m-d');
/*Migração na Tabela Produtos Acabados que também armazena os Preços da Lista ...*/
return migrar_nova_lista_preco('lista_promocional_machos_heinz.txt');

function migrar_nova_lista_preco($arquivo) {
	$conectar = mysql_connect('localhost','root','albafer');
	mysql_select_db('erp_albafer');
	if (file_exists($arquivo) && is_readable($arquivo)) {
		$linhas = file($arquivo);
		for ($x = 0; $x < count($linhas); $x++) {
			$conteudo  	= trim($linhas[$x]);
			$vetor  	= explode('|', $conteudo);
			echo $sql = "UPDATE `produtos_acabados` SET `qtde_promocional_simulativa` = '1', `preco_promocional_simulativa` = '".$vetor[1]."', `qtde_promocional_simulativa_b` = '1', `preco_promocional_simulativa_b` = '".$vetor[2]."' WHERE `id_produto_acabado` = '".$vetor[0]."' LIMIT 1; ";
			echo '<br>';
			/*if(!mysql_query($sql)) {
				echo $conteudo . "<br>";
			}*/
		}
		flush();
		$tamanho = filesize($arquivo);
		if ($tamanho >= '1073741824') {
			$tamanho = round($tamanho / 1073741824 * 100) / 100 . ' GB';
		}elseif ($tamanho >= '1048576') {
			$tamanho = round($tamanho / 1048576 * 100) / 100 . ' MB';
		}elseif ($tamanho >= '1024') {
			$tamanho = round($tamanho / 1024 * 100) / 100 . ' KB';
		}else {
			$tamanho = $tamanho . ' B';
		}
		echo '<font class="atencao">ARQUIVO MIGRADO COM SUCESSO '.basename($arquivo).' TAMANHO '.$tamanho.' TOTAL DE REGISTRO '.$x.'</font><br>';
	}else {
		echo '<font class="atencao">ERROR AO TENTAR ABRIR O ARQUIVO '.basename($arquivo).'</font>';
	}
}
//Aqui e um script quer retira os espacos e separa o credito da observacao
/*$sql= "select * ";
$sql.="from clientes ";
$sql.="where ativo=1 order by razaosocial";
$campos = $bancos->sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    $id_cliente = $campos[$i]['id_cliente'];
    $credito = trim($campos[$i]['credito']);
    $endereco = trim($campos[$i]['endereco']);
    $cep = trim($campos[$i]['cep']);
    $cidade = trim($campos[$i]['cidade']);
    $telcom = trim($campos[$i]['telcom']);
    $telfax = trim($campos[$i]['telfax']);
    $email = trim($campos[$i]['email']);

    $credito_novo = substr($credito, 0, 1);
    $observacao = trim(substr($credito, 1, strlen($credito)));

    $sql = "Update clientes set credito = '$credito_novo', endereco = '$endereco', cep = '$cep', cidade = '$cidade', telcom = '$telcom', telfax = '$telfax', email = '$email', observacao = '$observacao' where id_cliente = '$id_cliente'";
    $bancos->sql($sql);
}*/
?>