<?
//Função que contém a Tela Principal de Filtro ...
function filtro($valor, $sem_entrada) {
    $mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
    $mensagem[2] = "<font class='confirmacao'>OE ALTERADA COM SUCESSO.</font>";
    $mensagem[3] = "<font class='erro'>ESTOQUE DISPONÍVEL FINAL NÃO PODE SER MENOR DO QUE ZERO E/OU PRODUTO ACABADO ESTÁ BLOQUEADO !!!</font>";
    $mensagem[4] = "<font class='confirmacao'>OE(S) EXCLUÍDA(S) COM SUCESSO.</font>";
?>
<html>
<head>
<title>.:: Filtro de OE(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function habilita_desabilita_objetos() {
    if(document.form.chkt_habilita_desabilita_dados_saida.checked) {
        //Habilitando os campos de Saída ...
        document.form.txt_referencia_saida.disabled         = false
        document.form.txt_discriminacao_saida.disabled      = false
        document.form.txt_referencia_saida.className        = 'caixadetexto'
        document.form.txt_discriminacao_saida.className     = 'caixadetexto'
        document.form.txt_referencia_saida.focus()
        
        //Desabilitando os campos de Entrada ...
        document.form.txt_referencia_entrada.disabled       = true
        document.form.txt_discriminacao_entrada.disabled    = true
        document.form.txt_referencia_entrada.className      = 'textdisabled'
        document.form.txt_discriminacao_entrada.className   = 'textdisabled'
        
        //Limpando os campos de Entrada ...
        document.form.txt_referencia_entrada.value          = ''
        document.form.txt_discriminacao_entrada.value       = ''
    }else {
        //Desabilitando os campos de Saída ...
        document.form.txt_referencia_saida.disabled         = true
        document.form.txt_discriminacao_saida.disabled      = true
        document.form.txt_referencia_saida.className        = 'textdisabled'
        document.form.txt_discriminacao_saida.className     = 'textdisabled'
        
        //Limpando os campos de Saída ...
        document.form.txt_referencia_saida.value            = ''
        document.form.txt_discriminacao_saida.value         = ''
    }
    
    if(document.form.chkt_habilita_desabilita_dados_entrada.checked) {
        //Desabilitando os campos de Saída ...
        document.form.txt_referencia_saida.disabled         = true
        document.form.txt_discriminacao_saida.disabled      = true
        document.form.txt_referencia_saida.className        = 'textdisabled'
        document.form.txt_discriminacao_saida.className     = 'textdisabled'
        
        //Limpando os campos de Saída ...
        document.form.txt_referencia_saida.value            = ''
        document.form.txt_discriminacao_saida.value         = ''
        
        //Habilitando os campos de Entrada ...
        document.form.txt_referencia_entrada.disabled       = false
        document.form.txt_discriminacao_entrada.disabled    = false
        document.form.txt_referencia_entrada.className      = 'caixadetexto'
        document.form.txt_discriminacao_entrada.className   = 'caixadetexto'
        document.form.txt_referencia_entrada.focus()
    }else {
        //Desabilitando os campos de Entrada ...
        document.form.txt_referencia_entrada.disabled       = true
        document.form.txt_discriminacao_entrada.disabled    = true
        document.form.txt_referencia_entrada.className      = 'textdisabled'
        document.form.txt_discriminacao_entrada.className   = 'textdisabled'
        
        //Limpando os campos de Entrada ...
        document.form.txt_referencia_entrada.value          = ''
        document.form.txt_discriminacao_entrada.value       = ''
    }
}

function validar() {
    //Checkbox de Dados de Saída habilitado ...
    if(document.form.chkt_habilita_desabilita_dados_saida.checked) {
        if(document.form.txt_referencia_saida.value == '' && document.form.txt_discriminacao_saida.value == '') {
            alert('DIGITE UMA REFERÊNCIA OU DISCRIMINAÇÃO DE SAÍDA !')
            document.form.txt_referencia_saida.focus()
            return false
        }
    }
    
    //Checkbox de Dados de Entrada habilitado ...
    if(document.form.chkt_habilita_desabilita_dados_entrada.checked) {
        if(document.form.txt_referencia_entrada.value == '' && document.form.txt_discriminacao_entrada.value == '') {
            alert('DIGITE UMA REFERÊNCIA OU DISCRIMINAÇÃO DE ENTRADA !')
            document.form.txt_referencia_entrada.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_numero_oe.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--*******************Controle de Tela*******************-->
<input type='hidden' name='sem_entrada' value='<?=$sem_entrada;?>'>
<!--******************************************************-->
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de OE(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número da O.E.
        </td>
        <td>
            <input type='text' name='txt_numero_oe' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Dados de Saída <input type='checkbox' name='chkt_habilita_desabilita_dados_saida' value='S' title='Habilita / Desabilita Dados de Saída' onclick='if(this.checked) {document.form.chkt_habilita_desabilita_dados_entrada.checked = false};habilita_desabilita_objetos()'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia_saida' title='Digite a Referência de Saída' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discrimina&ccedil;&atilde;o
        </td>
        <td>
            <input type='text' name='txt_discriminacao_saida' title='Digite a Discriminação de Saída' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Dados de Entrada <input type='checkbox' name='chkt_habilita_desabilita_dados_entrada' value='E' title='Habilita / Desabilita Dados de Entrada' onclick='if(this.checked) {document.form.chkt_habilita_desabilita_dados_saida.checked = false};habilita_desabilita_objetos()'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência
        </td>
        <td>
            <input type='text' name='txt_referencia_entrada' title='Digite a Referência de Entrada' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discrimina&ccedil;&atilde;o
        </td>
        <td>
            <input type='text' name='txt_discriminacao_entrada' title='Digite a Discriminação de Entrada' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_oes_em_aberto' id='chkt_oes_em_aberto' value='1' title='Somente O.E(s) em Aberto' class='checkbox' checked>
            <label for='chkt_oes_em_aberto'>
                Somente O.E(s) em Aberto
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_oes_nao_impressas' id='chkt_oes_nao_impressas' value='1' title='Somente O.E(s) não Impressas' class='checkbox'>
            <label for='chkt_oes_nao_impressas'>
                Somente O.E(s) não Impressas
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_numero_oe.focus()' style='color:#ff9900' class='botao'>
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
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_referencia_saida       = $_POST['txt_referencia_saida'];
        $txt_discriminacao_saida    = $_POST['txt_discriminacao_saida'];
        $txt_referencia_entrada     = $_POST['txt_referencia_entrada'];
        $txt_discriminacao_entrada  = $_POST['txt_discriminacao_entrada'];
        $txt_numero_oe              = $_POST['txt_numero_oe'];
        $chkt_oes_em_aberto         = $_POST['chkt_oes_em_aberto'];
        $chkt_oes_nao_impressas     = $_POST['chkt_oes_nao_impressas'];
        $sem_entrada                = $_POST['sem_entrada'];
    }else {
        $txt_referencia_saida       = $_GET['txt_referencia_saida'];
        $txt_discriminacao_saida    = $_GET['txt_discriminacao_saida'];
        $txt_referencia_entrada     = $_GET['txt_referencia_entrada'];
        $txt_discriminacao_entrada  = $_GET['txt_discriminacao_entrada'];
        $txt_numero_oe              = $_GET['txt_numero_oe'];
        $chkt_oes_em_aberto         = $_GET['chkt_oes_em_aberto'];
        $chkt_oes_nao_impressas     = $_GET['chkt_oes_nao_impressas'];
        $sem_entrada                = $_GET['sem_entrada'];
    }
/*Se esse parâmetro não estiver vazio, então eu exibo esse Botão porque significa que essa tela foi acessada 
de outro lugar ao invés de ter sido acessada de algum Menu ...*/	
    if(!empty($id_produto_acabado)) {
        $sql = "SELECT o.*, pa.`referencia`, pa.`discriminacao` 
                FROM `oes` o 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = o.`id_produto_acabado_e` AND o.`id_produto_acabado_e` = '$id_produto_acabado' 
                WHERE o.`status_finalizar` = '0' ORDER BY o.`id_oe` DESC ";
    }else {
        if(!empty($txt_referencia_saida) || !empty($txt_discriminacao_saida)) {
            $inner_join_produtos_acabados = "INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = o.`id_produto_acabado_s` AND pa.`referencia` LIKE '%$txt_referencia_saida%' AND pa.`discriminacao` LIKE '%$txt_discriminacao_saida%' ";
        }else if(!empty($txt_referencia_entrada) || !empty($txt_discriminacao_entrada)) {
            $inner_join_produtos_acabados = "INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = o.`id_produto_acabado_e` AND pa.`referencia` LIKE '%$txt_referencia_entrada%' AND pa.`discriminacao` LIKE '%$txt_discriminacao_entrada%' ";
        }else {
            $inner_join_produtos_acabados = "INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = o.`id_produto_acabado_s` ";
        }
        if(!empty($chkt_oes_em_aberto))     $condicao_status_finalizar  = " AND o.`status_finalizar` = '0' ";
        if(!empty($chkt_oes_nao_impressas)) $condicao_oes_nao_impressas = " AND o.`impresso` = 'N' ";
        if(!empty($sem_entrada))            $condicao_sem_entrada       = " AND o.`qtde_e` = '0' ";
        
//Só posso listar as O.E.(s) em cima dos Produtos que estão para retornar ...
        $sql = "SELECT o.*, pa.`referencia`, pa.`discriminacao` 
                FROM `oes` o 
                $inner_join_produtos_acabados 
                WHERE o.`id_oe` LIKE '$txt_numero_oe%' 
                $condicao_status_finalizar 
                $condicao_oes_nao_impressas 
                $condicao_sem_entrada 
                ORDER BY o.`id_oe` DESC ";
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
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
<title>.:: Filtro de OE(s) ::.</title>
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
    filtro($valor, $sem_entrada);
}
?>