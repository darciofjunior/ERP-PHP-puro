<?
//Função que contém a Tela Principal de Filtro ...
function filtro($valor) {
    $mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
    $mensagem[2] = "<font class='confirmacao'>OP ALTERADA COM SUCESSO.</font>";
    $mensagem[3] = "<font class='confirmacao'>ENTRADA REGISTRADA COM SUCESSO.</font>";
    $mensagem[4] = "<font class='confirmacao'>OP FINALIZADA COM SUCESSO.</font>";
    $mensagem[5] = "<font class='confirmacao'>OP ABERTA COM SUCESSO.</font>";
    $mensagem[6] = "<font class='erro'>ESSA OP NÃO PODE SER FINALIZADA, É NECESSÁRIO DAR BAIXA(S) NA MATÉRIA PRIMA PRIMEIRO.</font>";
    $mensagem[7] = "<font class='erro'>OP ALTERADA COM SUCESSO, MAS NÃO PODE SER FINALIZADA, É NECESSÁRIO DAR BAIXA(S) NA MATÉRIA PRIMA PRIMEIRO.</font>";
    $mensagem[8] = "<font class='erro'>EXISTE(M) ITEM(NS) DE OS(S) EM ABERTO P/ ESTA OP !!!<br/>FINALIZE ESTE(S) ITEM(NS) DE OS.</font>";
    $mensagem[9] = "<font class='erro'>A QTDE DE ENTRADA ESTA COM DIFERENÇA ACIMA DE 2% DA QTDE DE SAÍDA DA OS !!!<br/>AVISAR P/ ROBERTO LIBERAR.</font>";
    $mensagem[10] = "<font class='erro'>PRODUTO ACABADO ESTÁ BLOQUEADO.</font>";
?>
<html>
<head>
<title>.:: Filtro de OP(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_numero_op.focus()'>
<form name='form' method='post' action=''>
<!--***************Controles de Tela***************-->
<input type='hidden' name='pop_up' value='<?=$pop_up;?>'>
<!--***********************************************-->
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de OP(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número da O.P.
        </td>
        <td>
            <input type='text' name='txt_numero_op' size='15' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discrimina&ccedil;&atilde;o
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' size='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ops_em_aberto' id='chkt_ops_em_aberto' value='1' title='Somente O.P(s) em Aberto' class='checkbox' checked>
            <label for='chkt_ops_em_aberto'>
                Somente OP(s) em Aberto
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ops_sem_baixa_pi' value='1' title='OP(s) s/ Baixa de PI' id='label1' class='checkbox'>
            <label for='label1'>
                OP(s) s/ Baixa de PI
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ops_sem_desenho' value='1' title='OP(s) s/ Desenho (Falta Imprimir)' id='label2' class='checkbox'>
            <label for='label2'>
                OP(s) s/ Desenho (Falta Imprimir)
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ops_nao_impressas' id='chkt_ops_nao_impressas' value='1' title='Somente O.P(s) não Impressas' class='checkbox'>
            <label for='chkt_ops_nao_impressas'>
                Somente O.P(s) não Impressas
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_numero_op.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>

* Nesse caso não trabalha com manipulação de Estoque.
</pre>
<?
}

//Significa que o usuário já fez pelo menos uma consulta ...
if(!empty($cmd_consultar)) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_numero_op          = $_POST['txt_numero_op'];
        $txt_referencia         = $_POST['txt_referencia'];
        $txt_discriminacao      = $_POST['txt_discriminacao'];
        $chkt_ops_em_aberto     = $_POST['chkt_ops_em_aberto'];
        $chkt_ops_sem_baixa_pi  = $_POST['chkt_ops_sem_baixa_pi'];
        $chkt_ops_sem_desenho   = $_POST['chkt_ops_sem_desenho'];
        $chkt_ops_nao_impressas = $_POST['chkt_ops_nao_impressas'];
        $id_op                  = $_POST['id_op'];
    }else {
        $txt_numero_op          = $_GET['txt_numero_op'];
        $txt_referencia         = $_GET['txt_referencia'];
        $txt_discriminacao      = $_GET['txt_discriminacao'];
        $chkt_ops_em_aberto     = $_GET['chkt_ops_em_aberto'];
        $chkt_ops_sem_baixa_pi  = $_GET['chkt_ops_sem_baixa_pi'];
        $chkt_ops_sem_desenho   = $_GET['chkt_ops_sem_desenho'];
        $chkt_ops_nao_impressas = $_GET['chkt_ops_nao_impressas'];
        $id_op                  = $_GET['id_op'];
    }
/********************************************************/
    if(!empty($chkt_ops_em_aberto)) $condicao_ops_em_aberto = " AND o.`status_finalizar` = '0' ";
    
    if(!empty($chkt_ops_sem_baixa_pi)) {
        //Busco todas as OP(s) que possuem pelo menos 1 Baixa de PI ...
        $sql = "SELECT DISTINCT(`id_op`) 
                FROM `baixas_ops_vs_pis` ";
        $campos_ops_baixas_pi = bancos::sql($sql);
        $linhas_ops_baixas_pi = count($campos_ops_baixas_pi);
        for($i = 0; $i < $linhas_ops_baixas_pi; $i++) $id_ops_com_baixa_pi.= $campos_ops_baixas_pi[$i]['id_op'].', ';
        $id_ops_com_baixa_pi        = substr($id_ops_com_baixa_pi, 0, strlen($id_ops_com_baixa_pi) - 2);
        $condicao_ops_sem_baixa_pi  = " AND o.`id_op` NOT IN ($id_ops_com_baixa_pi) ";
    }
    
    //Busca de todas as OP(s) que estão sem desenhos atrelados na família de Pinos 2 ou Cossinetes 8 ...
    if(!empty($chkt_ops_sem_desenho)) {
        $inner_join_ops_sem_desenho =  "INNER JOIN `gpas_vs_emps_divs` ged ON ged.id_gpa_vs_emp_div = pa.id_gpa_vs_emp_div 
                                        INNER JOIN `grupos_pas` gpa ON gpa.id_grupo_pa = ged.id_grupo_pa AND gpa.`id_familia` IN (2, 8) 
                                        AND (pa.`desenho_para_op` = '' OR pa.`desenho_para_op` IS NULL) ";
    }
    
    if(!empty($chkt_ops_nao_impressas)) $condicao_ops_nao_impressas = " AND o.`impresso` = 'N' ";
        
    if(!empty($id_produto_acabado)) $condicao_pa = " AND o.`id_produto_acabado` = '$id_produto_acabado' ";

    $sql = "SELECT o.*, pa.`referencia`, pa.`desenho_para_op`, u.`sigla` 
            FROM `ops` o 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = o.`id_produto_acabado` AND (pa.`operacao` < '9' AND pa.`operacao_custo` < '9') AND pa.`referencia` LIKE '%$txt_referencia%' AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
            INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
            $inner_join_ops_sem_desenho 
            WHERE o.`id_op` LIKE '%$txt_numero_op%' 
            AND o.`ativo` = '1' 
            $condicao_ops_em_aberto 
            $condicao_ops_sem_baixa_pi 
            $condicao_ops_nao_impressas 
            $condicao_pa 
            ORDER BY o.`id_op` DESC ";
    //Será utilizado abaixo do Loop ...
    $sql_todos_itens    = $sql;
    $campos             = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas             = count($campos);
    if($linhas == 0) {//Não retornou nenhum registro, então requisito a Tela de Filtro ...
        if(empty($_GET['pop_up'])) {//Se essa Tela foi aberta do modo Normal, faço um redirecionamento ...
?>
	<Script Language = 'JavaScript'>
            window.location = '<?=$PHP_SELF;?>?valor=1'
	</Script>
<?
        }else {//Tela aberta como Pop-UP então só exibo uma mensagem ...
?>
<html>
<head>
<title>.:: Filtro de OP(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center' class='atencao'>
        <td>
            SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.
        </td>
    </tr>
</table>
</head>
<?
        }
    }
}else {
//Quando esse arquivo é requisitado na primeira vez eu chamo a Tela de Filtro ...
    filtro($valor);
}
?>