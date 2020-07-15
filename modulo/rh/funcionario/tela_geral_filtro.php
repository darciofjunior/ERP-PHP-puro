<?
//Função que contém a Tela Principal de Filtro ...
function filtro($nivel_arquivo_principal, $valor) {
    $mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
?>
<html>
<head>
<title>.:: Filtro de Funcionário(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body onLoad="document.form.txt_nome.focus()">
<form name='form' method='post' action="<?=$GLOBALS['PHP_SELF'];//antes não tinha o global se der pal é só tirar o global mas na minha maquina mostrava q está errado?>">
<input type='hidden' name='nivel_arquivo_principal' value='<?=$nivel_arquivo_principal;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de Funcionário(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome
        </td>
        <td>
            <input type='text' name='txt_nome' title='Digite o Nome' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa
        </td>
        <td>
            <input type='text' name='txt_empresa' title='Digite a Empresa' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cargo
        </td>
        <td>
            <input type='text' name='txt_cargo' title='Digite o Cargo' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Departamento
        </td>
        <td>
            <input type='text' name='txt_departamento' title='Digite o Departamento' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Chefe
        </td>
        <td>
            <input type='text' name='txt_chefe' title='Digite o Chefe' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_mostrar_demissionarios' title='Mostrar Demitidos' value='1' class='checkbox' id='label'>
            <label for='label'>Mostrar Demitidos</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_nome.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<font color='red'><b>Cuidado:</b></font>

* Ao demitir ou alterar valores de salários de um funcionário ele surtirá efeito no custo, 
caso o mesmo esteja sendo utilizado.
</pre>
<?
}

//Significa que o usuário já fez pelo menos uma consulta ...
if(!empty($cmd_consultar)) {
    if(empty($order_by)) $order_by = " f.`nome` ";
//Significa que também mostra os Funcionários Demitidos
    if($chkt_mostrar_demissionarios == 1) {
        $status = " AND f.`status` <= '3' ";
    }else {//Todos os com exceção dos demitidos
        $status = " AND f.`status` < '3' ";
    }
//Se estiver preenchido o Funcionário Superior busca qual é o id desse funcionário superior ...
    if(!empty($txt_chefe)) {
        $sql = "SELECT `id_funcionario` 
                FROM `funcionarios` 
                WHERE `nome` LIKE '$txt_chefe%' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 1) {
            $condicao_funcionario_superior = " AND f.`id_funcionario_superior` LIKE '".$campos[0]['id_funcionario']."' ";
        }else {//Mais de um funcionário ...
            for($i = 0; $i < $linhas; $i++) $id_funcionario_superior.= $campos[$i]['id_funcionario'].', ';
            $id_funcionario_superior = substr($id_funcionario_superior, 0, strlen($id_funcionario_superior) - 2);
            
            $condicao_funcionario_superior = " AND f.`id_funcionario_superior` IN ($id_funcionario_superior) ";
        }
    }
    
    if(!empty($txt_cargo)) {
        $inner_join_cargos = " INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` AND c.`id_cargo` <> '82' AND c.`cargo` LIKE '%$txt_cargo%' ";
    }else {
        $inner_join_cargos = " LEFT JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` AND c.`id_cargo` <> '82' ";
    }
    
    if(!empty($txt_departamento)) {
        $inner_join_departamentos = " INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` AND d.`departamento` LIKE '%$txt_departamento%' ";
    }else {
        $inner_join_departamentos = " LEFT JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` ";
    }
    
//Traz todos funcionários - menos do cargo AUTONÔMO
    $sql = "SELECT DISTINCT(f.`id_funcionario`), f.`id_funcionario_superior`, f.`codigo_barra`, f.`nome`, 
            f.`cpf`, f.`rg`, f.`ddd_residencial`, f.`telefone_residencial`, f.`ddd_celular`, f.`telefone_celular`, 
            e.`nomefantasia`, c.`cargo`, d.`departamento` 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` AND e.`nomefantasia` LIKE '%$txt_empresa%' 
            $inner_join_cargos 
            $inner_join_departamentos 
            WHERE f.`nome` LIKE '%$txt_nome%' 
            $condicao_funcionario_superior 
            $status 
            ORDER BY $order_by ";
    $campos = bancos::sql($sql, $inicio, 200, 'sim', $pagina);
    $linhas = count($campos);
//Não retornou nenhum registro, então requisito a Tela de Filtro ...
    if($linhas == 0) {
//Aqui eu chamo a Tela Principal de Filtro ...
        filtro($nivel_arquivo_principal, 1);
    }
}else {
//Quando esse arquivo é requisitado na primeira vez eu chamo a Tela de Filtro ...
    filtro($nivel_arquivo_principal, '');
}
?>