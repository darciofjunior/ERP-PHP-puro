<?
//Função que contém a Tela Principal de Filtro ...
function filtro($valor, $nivel_path) {
    $mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
    $mensagem[2] = "<font class='confirmacao'>CARTA DE CORREÇÃO EXCLUIDA COM SUCESSO.</font>";
?>
<html>
<head>
<title>.:: Filtro de Carta(s) de Correção(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '<?=$nivel_path;?>/css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '<?=$nivel_path;?>/js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '<?=$nivel_path;?>/js/nova_janela.js'></Script>
</head>
<body onload="document.form.txt_numero_nf.focus()">
<form name='form' method='post' action="<?=$GLOBALS['PHP_SELF'];?>">
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de Carta(s) de Correção(ões)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º da Carta
        </td>
        <td>
            <input type="text" name="txt_numero_carta" title="Digite o N.º da Carta" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º da NF
        </td>
        <td>
            <input type="text" name="txt_numero_nf" title="Digite o N.º da NF" size="30" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type="text" name="txt_fornecedor" title="Digite o Fornecedor" size="35" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type="text" name="txt_cliente" title="Digite o Cliente" size="35" class="caixadetexto">
        </td>
    </tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='2'>
            <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" style="color:#ff9900;" onclick="document.form.txt_numero_nf.focus()" class="botao">
            <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class="botao">
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
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
            $txt_numero_carta 	= $_POST['txt_numero_carta'];
            $txt_numero_nf      = $_POST['txt_numero_nf'];
            $txt_fornecedor     = $_POST['txt_fornecedor'];
            $txt_cliente        = $_POST['txt_cliente'];
    }else {
            $txt_numero_carta 	= $_GET['txt_numero_carta'];
            $txt_numero_nf      = $_GET['txt_numero_nf'];
            $txt_fornecedor     = $_GET['txt_fornecedor'];
            $txt_cliente        = $_GET['txt_cliente'];
    }
    $filtrar_numeracao = 0;//Variável de Controle ...
//Consulta por Fornecedor ...
    if(!empty($txt_fornecedor) || !empty($txt_numero_nf)) {
/********************************************NFs Entrada********************************************/
//Essa Tabela de NFEs está totalmente relacionada com as NFs de Entrada que vem de Pedidos de Compras ...
        $sql = "SELECT DISTINCT(nfe.id_nfe) 
                FROM `nfe 
                INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor AND f.razaosocial LIKE '%$txt_fornecedor%' 
                WHERE nfe.`num_nota` LIKE '%$txt_numero_nf%' ORDER BY nfe.id_nfe DESC ";
        $campos_nfes = bancos::sql($sql);
        $linhas_nfes = count($campos_nfes);
        for($l = 0; $l < $linhas_nfes; $l++) $id_nfes[] = $campos_nfes[$l]['id_nfe'];
        //Arranjo Ténico
        if($linhas_nfes > 0) {
            $vetor_nfes     = implode(',', $id_nfes);
            $condicao_nfes  = " id_nfe IN ($vetor_nfes) ";
        }
        $filtrar_numeracao = 1;
    }
//Consulta por Cliente ...
    if(!empty($txt_cliente) || !empty($txt_numero_nf)) {
/**************************************NFs Saída/Devolução*****************************************/
//Essa Tabela de NFs está totalmente relacionada com as NFs de Saída que vem de Pedidos, Orçamentos ...
            $sql = "(SELECT distinct(nfs.id_nf) 
                    FROM `nfs` 
                    INNER JOIN `nfs_num_notas` nnn ON nnn.id_nf_num_nota = nfs.id_nf_num_nota AND nnn.numero_nf LIKE '%$txt_numero_nf%' 
                    INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente AND c.razaosocial LIKE '%$txt_cliente%') 
                    UNION 
                    (SELECT DISTINCT(nfs.id_nf) 
                    FROM `nfs` 
                    INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente AND c.razaosocial LIKE '%$txt_cliente%' 
                    WHERE nfs.`snf_devolvida` LIKE '%$txt_numero_nf%') ORDER BY id_nf DESC ";
            $campos_nfs = bancos::sql($sql);
            $linhas_nfs = count($campos_nfs);
            for($l = 0; $l < $linhas_nfs; $l++) $id_nfs[] = $campos_nfs[$l]['id_nf'];
            //Arranjo Ténico
            if($linhas_nfs > 0) {
                $vetor_nfs = implode(',', $id_nfs);
                $condicao_nfs = " id_nf IN ($vetor_nfs) ";
            }
/********************************************NFs Outras********************************************/
//Essa Tabela de NFs Outras só está relacionada com as NFs de Saída ...
            $sql = "SELECT DISTINCT(nfso.id_nf_outra) 
                    FROM `nfs_outras` nfso 
                    INNER JOIN `nfs_num_notas` nnn ON nnn.id_nf_num_nota = nfso.id_nf_num_nota AND nnn.numero_nf LIKE '%$txt_numero_nf%' 
                    INNER JOIN `clientes` c ON c.id_cliente = nfso.id_cliente AND c.razaosocial LIKE '%$txt_cliente%' 
                    ORDER BY nfso.id_nf_outra DESC ";
            $campos_nfs_outras = bancos::sql($sql);
            $linhas_nfs_outras = count($campos_nfs_outras);
            for($l = 0; $l < $linhas_nfs_outras; $l++) $id_nfs_outras[] = $campos_nfs_outras[$l]['id_nf_outra'];
            //Arranjo Ténico
            if($linhas_nfs_outras > 0) {
                $vetor_nfs_outras       = implode(',', $id_nfs_outras);
                $condicao_nfs_outras    = " id_nf_outra IN ($vetor_nfs_outras) ";
            }
            $filtrar_numeracao = 1;
    }

    if($filtrar_numeracao == 1) {
//1) Aqui eu verifico as cartas nos N.ºs das NFEs ...
        if(!empty($condicao_nfes)) {
            $sql = "SELECT DISTINCT(id_carta_correcao) 
                    FROM `cartas_correcoes` 
                    WHERE $condicao_nfes ";
            $campos_cartas = bancos::sql($sql);
            $linhas_cartas = count($campos_cartas);
            for($l = 0; $l < $linhas_cartas; $l++) $id_cartas_correcoes[] = $campos_cartas[$l]['id_carta_correcao'];
        }
//2) Aqui eu verifico as cartas nos N.ºs das NFSs ...
        if(!empty($condicao_nfs)) {
            $sql = "SELECT DISTINCT(id_carta_correcao) 
                    FROM `cartas_correcoes` 
                    WHERE $condicao_nfs ";
            $campos_cartas = bancos::sql($sql);
            $linhas_cartas = count($campos_cartas);
            for($l = 0; $l < $linhas_cartas; $l++) $id_cartas_correcoes[] = $campos_cartas[$l]['id_carta_correcao'];
        }
//3) Aqui eu verifico as cartas nos N.ºs das NFsOutras ... 
        if(!empty($condicao_nfs_outras)) {
            $sql = "SELECT DISTINCT(id_carta_correcao) 
                    FROM `cartas_correcoes` 
                    WHERE $condicao_nfs_outras ";
            $campos_cartas = bancos::sql($sql);
            $linhas_cartas = count($campos_cartas);
            for($l = 0; $l < $linhas_cartas; $l++) $id_cartas_correcoes[] = $campos_cartas[$l]['id_carta_correcao'];
        }
//Arranjo Ténico
        if(count($id_cartas_correcoes) == 0) {$id_cartas_correcoes[]='0';}
        $vetor_cartas_correcoes = implode(',', $id_cartas_correcoes);
        $condicao_cartas_correcoes = " AND `id_carta_correcao` IN ($vetor_cartas_correcoes) ";
    }

    $sql = "SELECT DISTINCT(id_carta_correcao), id_nfe, id_nf, id_nf_outra, CONCAT(DATE_FORMAT(SUBSTRING(data_sys, 1, 10), '%d/%m/%Y'), ' ', SUBSTRING(data_sys, 12, 8)) AS data_sys 
            FROM `cartas_correcoes` 
            WHERE `id_carta_correcao` LIKE '%$txt_numero_carta%' 
            $condicao_cartas_correcoes ORDER BY id_carta_correcao DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Não retornou nenhum registro, então requisito a Tela de Filtro ...
?>
    <Script Language = 'JavaScript'>
        window.location = '<?=$PHP_SELF;?>?valor=1'
    </Script>
<?
    }
}else {
//Quando esse arquivo é requisitado na primeira vez eu chamo a Tela de Filtro ...
    filtro($valor, $nivel_path);
}
?>