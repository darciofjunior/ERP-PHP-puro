<?
//Fun��o que cont�m a Tela Principal de Filtro ...
function filtro($nivel_arquivo_principal, $valor, $trazer_com_precos_concorrentes, $url_remetente) {
    $mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
    $mensagem[2] = "<font class='confirmacao'>PRODUTO(S) ACABADO(S) EXCLUIDO(S) COM SUCESSO.</font>";
    $mensagem[3] = "<font class='atencao'>ALGUNS PA(S) FORAM EXCLUIDO(S), OUTROS EST�O ATRELADO(S) A ETAPA 7.</font>";
    $mensagem[4] = "<font class='atencao'>NENHUM PA PODE SER EXCLU�DO, POIS EST�O ATRELADO(S) A ETAPA 7.</font>";
?>
<html>
<head>
<title>.:: Filtro de Produto(s) Acabado(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '<?=$nivel_arquivo_principal;?>/css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '<?=$nivel_arquivo_principal;?>/lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '<?=$nivel_arquivo_principal;?>/js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '<?=$nivel_arquivo_principal;?>/js/validar.js'></Script>
<Script Language = 'JavaScript'>
function iniciar() {
    document.form.cmb_operacao_custo_sub.className = 'textdisabled'
    document.form.cmb_operacao_custo_sub.disabled = true
    document.form.txt_referencia.focus()
}

//Controle com a Opera��o (Fat)
function controle_hidden_operacao() {
    var operacao = document.form.cmb_operacao[document.form.cmb_operacao.selectedIndex].text
//Se n�o estiver selecionada nenhuma Opera��o de Custo
    if(operacao == 'SELECIONE') {
        document.form.hidden_operacao.value = ''
    }else if(operacao == 'Industrializa��o (c/ IPI)') {
        document.form.hidden_operacao.value = 1
    }else if(operacao == 'Revenda (s/ IPI)') {
        document.form.hidden_operacao.value = 2
    }
}

//Controle com a Sub-Opera��o de Custo
function controle_hidden_operacao_custo_sub() {
    var operacao_custo_sub = document.form.cmb_operacao_custo_sub[document.form.cmb_operacao_custo_sub.selectedIndex].text
//Se n�o estiver selecionada nenhuma Sub-Opera��o de Custo
    if(operacao_custo_sub == 'SELECIONE') {
        document.form.hidden_operacao_custo_sub.value = ''
    }else if(operacao_custo_sub == 'Industrializa��o') {
        document.form.hidden_operacao_custo_sub.value = 1
    }else if(operacao_custo_sub == 'Revenda') {
        document.form.hidden_operacao_custo_sub.value = 2
    }
}

function controle_operacao_custo() {
    var operacao_custo = document.form.cmb_operacao_custo[document.form.cmb_operacao_custo.selectedIndex].text
    if(operacao_custo == 'Industrializa��o') {//Quando a Opera��o de Custo = Industrial, eu habilito a Sub-Opera��o de Custo ...
//Layout de Habilitado
        document.form.cmb_operacao_custo_sub.className = 'caixadetexto'
//Habilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value = ''
        document.form.cmb_operacao_custo_sub.disabled = false
//Controle do Hidden ...
        document.form.hidden_operacao_custo.value = 1
//Quando a Opera��o de Custo = Revenda, eu desabilito a Sub-Opera��o de Custo ...
    }else {
//Layout de Desabilitado
        document.form.cmb_operacao_custo_sub.className = 'textdisabled'
//Desabilita a Combo de Empresa
        document.form.cmb_operacao_custo_sub.value = ''
        document.form.cmb_operacao_custo_sub.disabled = true
//Controle do Hidden ...
        if(operacao_custo == 'Revenda') {
            document.form.hidden_operacao_custo.value = 2
        }else {//Se n�o tiver nenhuma Opera��o de Custo selecionada ...
            document.form.hidden_operacao_custo.value = ''
        }
    }
}

function esp_dt() {
    document.form.txt_referencia.value                  = 'ESP'
    document.form.chkt_depto_tecnico.checked            = true
    document.form.chkt_pas_com_orcamento.checked        = true
    document.form.chkt_so_custos_nao_liberados.checked  = true
    document.form.chkt_mostrar_esp.checked              = true
    document.form.cmd_consultar.click()
}
</Script>
</head>
<body onload='controle_operacao_custo();iniciar()'>
<form name='form' method='post' action='<?=$GLOBALS['PHP_SELF'];?>'>
<input type='hidden' name='nivel_arquivo_principal' value='<?=$nivel_arquivo_principal;?>'>
<!--**********************Gambiarra**********************
/*Aqui eu tive que fazer essa adapta��o, porque estava dando erro de par�metro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro l� no outro
passo da consulta*/
-->
<input type='hidden' name='hidden_operacao'>
<input type='hidden' name='hidden_operacao_custo'>
<input type='hidden' name='hidden_operacao_custo_sub'>
<input type='hidden' name='url_remetente'>
<!--Controle de Tela-->
<input type='hidden' name='trazer_com_precos_concorrentes' value='<?=$trazer_com_precos_concorrentes;?>'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Filtro de Produto(s) Acabado(s)
            <?
                /**********************************************************************/
                /***********************�rea de R�tulos Especiais**********************/
                /**********************************************************************/
                //�rea de R�tulos Especiais, que variam dependendo da onde esse arquivo � chamado ...
                if($url_remetente == 'EXCEDENTE') {
                    echo ' - Estoque Excedente';
                }else if($url_remetente == 'CUSTO') {
                    echo ' - Custo';
                }
                /**********************************************************************/
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            C�digo do Fornecedor
        </td>
        <td>
            <input type='text' name='txt_codigo_fornecedor' title='Digite o C�digo do Fornecedor' maxlength='15' size='16' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Refer�ncia
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Refer�ncia' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discrimina��o
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discrimina��o' size='30' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor
        </td>
        <td>
            <input type='text' name='txt_fornecedor' title='Digite o Fornecedor' size='35' class='caixadetexto'> <b>* Somente Produtos normais de Linha</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fam�lia
        </td>
        <td>
            <select name='cmb_familia' title='Selecione a Fam�lia' class='combo'>
            <?
                $sql = "SELECT `id_familia`, `nome` 
                        FROM `familias` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Grupo PA
        </td>
        <td>
            <select name='cmb_grupo_pa' title='Selecione o Grupo P.A.' class='combo'>
            <?
                $sql = "SELECT `id_grupo_pa`, `nome` 
                        FROM `grupos_pas` 
                        WHERE `ativo` = '1' ORDER BY `nome` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa Divis�o
        </td>
        <td>
            <select name='cmb_empresa_divisao' title='Selecione a Empresa Divis�o' class='combo'>
            <?
                $sql = "SELECT `id_empresa_divisao`, `razaosocial` 
                        FROM `empresas_divisoes` 
                        WHERE `ativo` = '1' ORDER BY `razaosocial` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Opera��o de Custo
        </td>
        <td>
            <select name='cmb_operacao_custo' title='Selecione a Opera��o de Custo' onchange='controle_operacao_custo()' class='combo'>
                <option value='' style='color:red' selected>SELECIONE</option>
                <option value='0'>Industrializa��o</option>
                <option value='1'>Revenda</option>
            </select>
            &nbsp;
            <select name='cmb_operacao_custo_sub' title='Selecione a Sub-Opera��o' onchange='controle_hidden_operacao_custo_sub()' class='textdisabled' disabled>
                <option value='' style='color:red' selected>SELECIONE</option>
                <option value='0'>Industrializa��o</option>
                <option value='1'>Revenda</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Opera��o (Fat)
        </td>
        <td>
            <select name='cmb_operacao' title='Selecione a Opera��o (Fat)' onchange='controle_hidden_operacao()' class='combo'>
                <option value='' style='color:red' selected>SELECIONE</option>
                <option value='0'>Industrializa��o (c/ IPI)</option>
                <option value='1'>Revenda (s/ IPI)</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Desenho p/ OP
        </td>
        <td>
            <select name='cmb_desenho_para_op' title='Selecione o Desenho p/ OP' class='combo'>
                <option value='' style='color:red' selected>SELECIONE</option>
                <option value='C'>C/ Desenho</option>
                <option value='S'>S/ Desenho</option>
            </select>
        </td>
    </tr>
    <?
        /**********************************************************************/
        /***********************�rea de Campos Especiais***********************/
        /**********************************************************************/
        //�rea de Campos Especiais, que variam dependendo da onde esse arquivo � chamado ...
        if($url_remetente == 'EXCEDENTE') {
    ?>
    <tr class='linhanormalescura'>
        <td>
            <b>Tem Embalado no Excedente ?</b>
        </td>
        <td>
            <select name='cmb_embalado' title='Selecione o Embalado' class='combo'>
                <!--"T" significa trazer todos os PA�s que est�o em Excedente independente de estar Embalado ou N�O-->
                <option value='T' style='color:red'>SELECIONE</option>
                <option value='S'>SIM</option>
                <option value='N'>N�O</option>
            </select>
        </td>
    </tr>
    <?
        }else if($url_remetente == 'CUSTO') {
    ?>
    <tr class='linhanormalescura'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_depto_tecnico' value='1' title='Consultar todos os DEPTO. T�CNICO' id='label_todos_depto_tecnico' class='checkbox'>
            <label for='label_todos_depto_tecnico'>
                Todos os DEPTO. T�CNICO
            </label>
        </td>
    </tr>
    <tr class='linhanormalescura'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_pas_com_orcamento' value='1' title='Consultar Somente PA(s) com Or�amento' id='label_somente_pas_com_orcamento' class='checkbox'>
            <label for='label_somente_pas_com_orcamento'>
                Somente PA(s) com Or�amento
            </label>
        </td>
    </tr>
    <?        
        }
        /**********************************************************************/
    ?>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_produtos_irregulares' value='1' title='Produtos Irregulares' id='label1' class='checkbox'>
            <label for='label1'>
                Todos Produtos Irregulares
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_so_custos_nao_liberados' value='1' title='S� Custos n�o Liberados' id='label2' class='checkbox'>
            <label for='label2'>
                S� Custos n�o Liberados
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_mostrar_componentes' value='1' title='Mostrar Componentes' id='label3' class='checkbox'>
            <label for='label3'>
                Mostrar Componentes
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_mostrar_esp' value='1' title='Mostrar ESP' id='label4' class='checkbox'>
            <label for='label4'>
                Mostrar ESP
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_mostrar_top' value='1' title='Mostrar TOP' id='label5' class='checkbox'>
            <label for='label5'>
                Mostrar TOP
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_sem_codigo_barra' value='1' title='Sem C�digo de Barra' id='label6' class='checkbox'>
            <label for='label6'>
                Sem C�digo de Barra
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td>
            <input type='checkbox' name='chkt_ajuste_mmv_maior_que_zero' value='1' title='Ajuste MMV > 0' id='label7' class='checkbox'>
            <label for='label7'>
                Ajuste MMV > 0
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='controle_operacao_custo();iniciar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_packing_list_limas' value='Packing List de Limas' title='Packing List de Limas' onclick="html5Lightbox.showLightbox(7, 'packing_list_limas.php')" style='color:green' class='botao'>
            <?
                /**********************************************************************/
                /***********************�rea de Bot�es Especiais***********************/
                /**********************************************************************/
                //�rea de Campos Especiais, que variam dependendo da onde esse arquivo � chamado ...
                if($url_remetente == 'CUSTO') {
            ?>
            <input type='button' name='cmd_esp_dt' value='ESP DT' title='ESP DT' onclick='esp_dt()' style='color:purple' class='botao'>
            <?
                }
                /**********************************************************************/
            ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observa��o:</font></b>
    <pre>
    * S� exibe para exclus�o os P.A(s) que estejam com os dados cadastrados corretamente.
    </pre>
</pre>
<?
}

//Significa que o usu�rio j� fez pelo menos uma consulta ...
if(!empty($cmd_consultar)) {
    if(!empty($trazer_com_precos_concorrentes)) {//Ir� listar somente os PA(s) que possuem Pre�o de Concorrente ...
        $inner_com_precos_concorrentes = "INNER JOIN `concorrentes_vs_prod_acabados` cpa ON cpa.`id_produto_acabado` = pa.`id_produto_acabado` ";
    }
    if(empty($order_by)) $order_by = " pa.`discriminacao` ";
//Tratamento com as vari�veis que vem por par�metro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_fornecedor                 = $_POST['txt_fornecedor'];
        $chkt_produtos_irregulares      = $_POST['chkt_produtos_irregulares'];
        $chkt_so_custos_nao_liberados   = $_POST['chkt_so_custos_nao_liberados'];
        $chkt_mostrar_componentes       = $_POST['chkt_mostrar_componentes'];
        $chkt_mostrar_esp               = $_POST['chkt_mostrar_esp'];
        $chkt_mostrar_top               = $_POST['chkt_mostrar_top'];
        $chkt_sem_codigo_barra          = $_POST['chkt_sem_codigo_barra'];
        $chkt_ajuste_mmv_maior_que_zero = $_POST['chkt_ajuste_mmv_maior_que_zero'];
        $cmb_familia                    = $_POST['cmb_familia'];
        $cmb_grupo_pa                   = $_POST['cmb_grupo_pa'];
        $cmb_empresa_divisao            = $_POST['cmb_empresa_divisao'];
        $cmb_operacao                   = $_POST['cmb_operacao'];
        $cmb_desenho_para_op            = $_POST['cmb_desenho_para_op'];
        $cmb_embalado                   = $_POST['cmb_embalado'];
        $chkt_depto_tecnico             = $_POST['chkt_depto_tecnico'];
        $chkt_pas_com_orcamento         = $_POST['chkt_pas_com_orcamento'];
        $hidden_operacao_custo          = $_POST['hidden_operacao_custo'];
        $hidden_operacao_custo_sub      = $_POST['hidden_operacao_custo_sub'];
        $url_remetente                  = $_POST['url_remetente'];
        $txt_codigo_fornecedor          = $_POST['txt_codigo_fornecedor'];
        $txt_referencia                 = $_POST['txt_referencia'];
        $txt_discriminacao              = $_POST['txt_discriminacao'];
    }else {
        $txt_fornecedor                 = $_GET['txt_fornecedor'];
        $chkt_produtos_irregulares      = $_GET['chkt_produtos_irregulares'];
        $chkt_so_custos_nao_liberados   = $_GET['chkt_so_custos_nao_liberados'];
        $chkt_mostrar_componentes       = $_GET['chkt_mostrar_componentes'];
        $chkt_mostrar_esp               = $_GET['chkt_mostrar_esp'];
        $chkt_mostrar_top               = $_GET['chkt_mostrar_top'];
        $chkt_sem_codigo_barra          = $_GET['chkt_sem_codigo_barra'];
        $chkt_ajuste_mmv_maior_que_zero = $_GET['chkt_ajuste_mmv_maior_que_zero'];
        $cmb_familia                    = $_GET['cmb_familia'];
        $cmb_grupo_pa                   = $_GET['cmb_grupo_pa'];
        $cmb_empresa_divisao            = $_GET['cmb_empresa_divisao'];
        $cmb_operacao                   = $_GET['cmb_operacao'];
        $cmb_desenho_para_op            = $_GET['cmb_desenho_para_op'];
        $cmb_embalado                   = $_GET['cmb_embalado'];
        $chkt_depto_tecnico             = $_GET['chkt_depto_tecnico'];
        $chkt_pas_com_orcamento         = $_GET['chkt_pas_com_orcamento'];
        $hidden_operacao_custo          = $_GET['hidden_operacao_custo'];
        $hidden_operacao_custo_sub      = $_GET['hidden_operacao_custo_sub'];
        $url_remetente                  = $_GET['url_remetente'];
        $txt_codigo_fornecedor          = $_GET['txt_codigo_fornecedor'];
        $txt_referencia                 = $_GET['txt_referencia'];
        $txt_discriminacao              = $_GET['txt_discriminacao'];
    }
/*Aqui eu tenho esse Tratamento devido com o % e |, devido o usu�rio utilizar o % 
como caracter ...*/
    $txt_discriminacao = str_replace('|', '%', $txt_discriminacao);
    //Se essa op��o estiver desmarcada, ent�o eu s� mostro os P.A(s) que s�o do Tipo normais de Linha ...
    if(empty($chkt_mostrar_esp))    $condicao_esp = " AND pa.`referencia` <> 'ESP' ";
//Se essa op��o estiver marcada, ent�o eu s� mostro os P.A(s) que s�o Top(s) ...
    if(!empty($chkt_mostrar_top))   $condicao_top = " AND pa.`status_top` = '1' ";
//Consulta por Fornecedor ...
    if(!empty($txt_fornecedor)) {
        $sql = "SELECT pa.id_produto_acabado 
                FROM `produtos_acabados` pa 
                INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.`id_produto_insumo` = pa.`id_produto_insumo` AND fpi.ativo = '1' 
                INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` AND f.`razaosocial` LIKE '%$txt_fornecedor%' 
                WHERE pa.`ativo` = '1' 
                AND pa.`referencia` <> 'ESP' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {//Disparo do Loop caso encontre pelo menos 1 item ...
            for($i = 0; $i < $linhas; $i++) $id_produto_acabados.= $campos[$i]['id_produto_acabado'].', ';
            //Se achar 1 item pelo menos, ent�o faz o tratamento necess�rio ...
            $id_produto_acabados = substr($id_produto_acabados, 0, strlen($id_produto_acabados) - 2);
        }else {
            //Se n�o achar nenhum PI, ent�o tem esse Macete ...
            $id_produto_acabados = 0;//para n�o dar erro de SQL
        }
        $condicao_fornecedor = ' AND pa.`id_produto_acabado` IN ('.$id_produto_acabados.') ';
    }
    
//Somente dar� efeito para a �ltima op��o, que � a sele��o de todos os produtos
    if(!empty($chkt_produtos_irregulares)) $condicao = " AND (pa.`operacao` = '9' OR pa.`operacao_custo` = '9') ";
//Este checkbox surte efeito em todas as op��es ...
    $condicao_status_custo = (!empty($chkt_so_custos_nao_liberados)) ? " AND pa.`status_custo` = '0' " : '';
//Se estiver habilitada essa ent�o mostra tamb�m os Produtos que s�o da Fam�lia de Componentes
    $condicao_mostrar_componentes = (!empty($chkt_mostrar_componentes)) ? '' : " AND gpa.`id_familia` <> 23 ";
//Se tiver habilitada essa op��o, ent�o mostra todos os P.A(s) que n�o possuem C�digo de Barra ...
    if(!empty($chkt_sem_codigo_barra)) $condicao_sem_codigo_barra = " AND pa.`codigo_barra` = '' ";
//Se estiver habilitada essa op��o, ent�o mostro todos os PA(s) em que seu MMV seja maior do que Zero ...
    if(!empty($chkt_ajuste_mmv_maior_que_zero)) $condicao_ajuste_mmv_maior_que_zero = " AND pa.`ajuste_mmv` > '0' ";
    
    if($cmb_familia == '')              $cmb_familia = '%';
    if($cmb_grupo_pa == '')             $cmb_grupo_pa = '%';
    if($cmb_empresa_divisao == '') 	$cmb_empresa_divisao = '%';
/*Aqui eu tive que fazer essa adapta��o, porque estava dando erro de par�metro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
//Primeira adapta��o
    if($hidden_operacao == 1) {//Opera��o (Fat) - Industrializa��o (c/ IPI)
        $cmb_operacao = 0;
    }else if($hidden_operacao == 2) {//Opera��o (Fat) - Revenda (s/ IPI)
        $cmb_operacao = 1;
    }else {//Independente da Opera��o (Fat)
        if($cmb_operacao == '') $cmb_operacao = '%';
    }
//Segunda adapta��o
    if($hidden_operacao_custo == 1) {//Opera��o de Custo = Industrial
        $cmb_operacao_custo = 0;
    }else if($hidden_operacao_custo == 2) {//Opera��o de Custo = Revenda
        $cmb_operacao_custo = 1;
    }else {//Independente da Opera��o de Custo
        if($cmb_operacao_custo == '') $cmb_operacao_custo = '%';
    }
//Terceira adapta��o
    if($hidden_operacao_custo_sub == 1) {//Sub-Opera��o de Custo = Industrial
        $cmb_operacao_custo_sub = 0;
    }else if($hidden_operacao_custo_sub == 2) {//Sub-Opera��o de Custo = Revenda
        $cmb_operacao_custo_sub = 1;
    }else {//Independente da Sub-Opera��o de Custo
        if($cmb_operacao_custo_sub == '') $cmb_operacao_custo_sub = '%';
    }
//Desenho p/ OP ...
    if($cmb_desenho_para_op == 'C') {
        $condicao_desenho_para_op = " AND pa.`desenho_para_op` <> '' ";
    }else if($cmb_desenho_para_op == 'S') {
        $condicao_desenho_para_op = " AND (pa.`desenho_para_op` = '' OR pa.`desenho_para_op` IS NULL) ";
    }else {
        $condicao_desenho_para_op = '';
    }

    if(!empty($cmb_embalado)) {
        //Verifico se o Item possui Estoque Excedente, mas somente do que est� "Em aberto" ...
        $inner_join_estoque_excedente = " INNER JOIN `estoques_excedentes` ee ON ee.`id_produto_acabado` = pa.`id_produto_acabado` AND `status` = '0' ";
        if($cmb_embalado == 'S' || $cmb_embalado == 'N') $inner_join_estoque_excedente.= " AND ee.`embalado` = '$cmb_embalado' ";
    }
    
    //Trago todos os PA(s) que est�o ativos de Refer�ncia "ESP" ...
    $sql = "SELECT pa.`id_produto_acabado` 
            FROM `produtos_acabados` pa 
            WHERE pa.`ativo` = '1' 
            AND pa.`referencia` = 'ESP' 
            $condicao_status_custo ORDER BY pa.`discriminacao` ";
    $campos_custo = bancos::sql($sql);
    $linhas_custo = count($campos_custo);
    //Guardo nessa vari�vel "$id_produtos_acabados" todos os PA(s) que foram encontrados nessa cl�usula acima ...
    for($i = 0; $i < $linhas_custo; $i++) $id_produtos_acabados.= $campos_custo[$i]['id_produto_acabado'].', ';
    $id_produtos_acabados = (!empty($id_produtos_acabados)) ? substr($id_produtos_acabados, 0, strlen($id_produtos_acabados) - 2) : 0;
    
    /*Trago todos os PA q estejam "sem Prazo" do Depto T�cnico ou sem Pre�o, situa��o = DEPTO T�CNICO, 
    isso s� acontece p/ PA�s do Tipo "ESP" ...*/
    if(!empty($chkt_depto_tecnico)) {//Habilitou a op��o de trazer os PA = DEPTO T�CNICO com refer�ncia "ESP" ...
        $sql = "SELECT DISTINCT(pa.`id_produto_acabado`) AS id_produto_acabado 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` AND pa.`referencia` = 'ESP' $condicao_status_custo 
                WHERE (ovi.`prazo_entrega_tecnico` = '' OR ovi.`preco_liq_fat_disc` = 'DEPTO T�CNICO') 
                ORDER BY pa.`id_produto_acabado` ";
        $campos_depto_tecnico = bancos::sql($sql);
        $linhas_depto_tecnico = count($campos_depto_tecnico);
        
        //Unifico os PA -> DEPTO T�CNICO com os encontrados da Cl�usula acima ...
        for($i = 0; $i < $linhas_depto_tecnico; $i++) $id_produtos_acabados.= $campos_depto_tecnico[$i]['id_produto_acabado'].', ';
        $id_produtos_acabados = (!empty($id_produtos_acabados)) ? substr($id_produtos_acabados, 0, strlen($id_produtos_acabados) - 2) : 0;
    }
    
    if(!empty($chkt_pas_com_orcamento)) {
        $tres_meses_atras = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -90), '-');
        
        /*Caso foi marcado o checkbox "$chkt_depto_tecnico", ent�o verifico se dos PA�s que foram 
        retornardos do SQL acima de Depto. T�cnico acima, existem aqueles q est�o vinculados a algum 
        or�amento N�o Congelado e dos �ltimos 90 dias, filtrando mais ainda, porque s� estes que 
        me interessam ...*/
        if(!empty($chkt_depto_tecnico)) $condicao_where = " WHERE ovi.`id_produto_acabado` IN ($id_produtos_acabados) ";
        
        $sql = "SELECT DISTINCT(pa.`id_produto_acabado`) AS id_produto_acabado 
                FROM `orcamentos_vendas_itens` ovi 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` $condicao_status_custo 
                INNER JOIN `orcamentos_vendas` ov ON ovi.`id_orcamento_venda` = ov.`id_orcamento_venda` AND ov.`congelar` = 'N' AND ov.`data_emissao` > '$tres_meses_atras' 
                $condicao_where ORDER BY pa.`id_produto_acabado` "; 
        $campos_pas_com_orcs = bancos::sql($sql);
        $linhas_pas_com_orcs = count($campos_pas_com_orcs);
        
        for($i = 0; $i < $linhas_pas_com_orcs; $i++) $id_pas_com_orcs.= $campos_pas_com_orcs[$i]['id_produto_acabado'].', ';
        $id_pas_com_orcs = (!empty($id_pas_com_orcs)) ? substr($id_pas_com_orcs, 0, strlen($id_pas_com_orcs) - 2) : 0;
        $condicao_pas = " AND pa.`id_produto_acabado` IN ($id_pas_com_orcs) ";
    }else {
        /*Tenho que colocar esse controle porque se eu acessar esse arquivo de uma Tela Normal, 
        ent�o se fura o Filtro ...*/
        if($url_remetente == 'CUSTO') $condicao_pas = " AND pa.`id_produto_acabado` IN ($id_produtos_acabados) ";
    }
    
    //Select Principal ...
    $sql = "SELECT DISTINCT(pa.`id_produto_acabado`), pa.*, ed.`razaosocial`, ged.`desc_base_a_nac`, ged.`desc_base_b_nac`, ged.`acrescimo_base_nac`, gpa.`nome`, gpa.`prazo_entrega`, u.`unidade`, u.`sigla` 
            FROM `produtos_acabados` pa 
            INNER JOIN `gpas_vs_emps_divs` ged ON pa.`id_gpa_vs_emp_div` = ged.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_grupo_pa` LIKE '$cmb_grupo_pa' AND gpa.`id_familia` LIKE '$cmb_familia' 
            INNER JOIN `empresas_divisoes` ed ON ged.`id_empresa_divisao` = ed.`id_empresa_divisao` AND ed.`id_empresa_divisao` LIKE '$cmb_empresa_divisao' 
            INNER JOIN `unidades` u ON pa.`id_unidade` = u.`id_unidade` 
            $inner_join_estoque_excedente 
            $inner_com_precos_concorrentes 
            WHERE pa.`codigo_fornecedor` LIKE '%$txt_codigo_fornecedor%' 
            AND pa.`referencia` LIKE '%$txt_referencia%' 
            AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
            AND pa.`operacao_custo` LIKE '$cmb_operacao_custo' 
            AND pa.`operacao_custo_sub` LIKE '$cmb_operacao_custo_sub' 
            AND pa.`operacao` LIKE '$cmb_operacao' 
            AND pa.`ativo` = '1' 
            $condicao 
            $condicao_mostrar_componentes 
            $condicao_status_custo 
            $condicao_sem_codigo_barra 
            $condicao_ajuste_mmv_maior_que_zero 
            $condicao_fornecedor 
            $condicao_top 
            $condicao_esp 
            $condicao_desenho_para_op 
            $condicao_pas 
            GROUP BY pa.`id_produto_acabado` ORDER BY $order_by ";

    $sql_extra = "SELECT COUNT(DISTINCT(pa.`id_produto_acabado`)) AS total_registro 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON pa.`id_gpa_vs_emp_div` = ged.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` AND gpa.`id_grupo_pa` LIKE '$cmb_grupo_pa' AND gpa.`id_familia` LIKE '$cmb_familia' 
                    INNER JOIN `empresas_divisoes` ed ON ged.`id_empresa_divisao` = ed.`id_empresa_divisao` AND ed.`id_empresa_divisao` LIKE '$cmb_empresa_divisao' 
                    INNER JOIN `unidades` u ON pa.`id_unidade` = u.`id_unidade` 
                    $inner_join_estoque_excedente 
                    $inner_com_precos_concorrentes 
                    WHERE pa.`codigo_fornecedor` LIKE '%$txt_codigo_fornecedor%' 
                    AND pa.`referencia` LIKE '%$txt_referencia%' 
                    AND pa.`discriminacao` LIKE '%$txt_discriminacao%' 
                    AND pa.`operacao_custo` LIKE '$cmb_operacao_custo' 
                    AND pa.`operacao_custo_sub` LIKE '$cmb_operacao_custo_sub' 
                    AND pa.`operacao` LIKE '$cmb_operacao' 
                    AND pa.`ativo` = '1' 
                    $condicao 
                    $condicao_mostrar_componentes 
                    $condicao_status_custo 
                    $condicao_sem_codigo_barra 
                    $condicao_ajuste_mmv_maior_que_zero 
                    $condicao_fornecedor 
                    $condicao_top 
                    $condicao_esp 
                    $condicao_desenho_para_op 
                    $condicao_pas 
                    GROUP BY pa.`id_produto_acabado` ORDER BY $order_by ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//N�o retornou nenhum registro, ent�o requisito a Tela de Filtro ...
?>
	<Script Language = 'JavaScript'>
            var nao_perguntar_novamente = eval('<?=$nao_perguntar_novamente;?>')
/*Significa que j� foi feita uma pergunta referente ao Filtro anteriormente e sendo assim
s� ir� redirecionar p/ a Tela de Filtro novamente ...*/
            if(nao_perguntar_novamente == 1) {
                window.location = '<?=$PHP_SELF;?>?valor=1'
            }else {
/*Se n�o foi encontrado nenhum P.A. pelo filtro normal, ent�o o Sistema pergunta p/ o usu�rio 
se ele deseja visualizar os ESP(s) de acordo com o Filtro que ele fez ...*/
                var resposta = confirm('DESEJA CONSULTAR OS ESPECIAIS ?')
                if(resposta == true) {//Ir� manter o Filtro do Usu�rio, acrescentando apenas a op��o de Especiais ...
                <?
//Aqui eu tenho esse Tratamento devido com o % e |, devido o usu�rio utilizar o % como caracter ...
                    $txt_discriminacao = str_replace('%', '|', $txt_discriminacao);
                ?>
                    window.location = '<?=$PHP_SELF;?>?cmd_consultar=<?=$cmd_consultar;?>&txt_fornecedor=<?=$txt_fornecedor;?>&chkt_produtos_irregulares=<?=$chkt_produtos_irregulares;?>&chkt_so_custos_nao_liberados=<?=$chkt_so_custos_nao_liberados;?>&chkt_mostrar_componentes=<?=$chkt_mostrar_componentes;?>&chkt_mostrar_esp=1&cmb_familia=<?=$cmb_familia;?>&cmb_grupo_pa=<?=$cmb_grupo_pa;?>&cmb_empresa_divisao=<?=$cmb_empresa_divisao;?>&hidden_operacao=<?=$hidden_operacao;?>&hidden_operacao_custo=<?=$hidden_operacao_custo;?>&hidden_operacao_custo_sub=<?=$hidden_operacao_custo_sub;?>&chkt_depto_tecnico=<?=$chkt_depto_tecnico;?>&chkt_pas_com_orcamento=<?=$chkt_pas_com_orcamento;?>&txt_referencia=<?=$txt_referencia;?>&txt_discriminacao=<?=$txt_discriminacao;?>&nao_perguntar_novamente=1'
                }else {
                    window.location = '<?=$PHP_SELF;?>?valor=1'
                }
            }
	</Script>
<?
    }
}else {
//Quando esse arquivo � requisitado na primeira vez eu chamo a Tela de Filtro ...
    filtro($nivel_arquivo_principal, $valor, $trazer_com_precos_concorrentes, $url_remetente);
}
?>