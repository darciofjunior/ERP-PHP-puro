<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/nivel_estoque/index.php', '../../../');

if($passo == 1) {
    $vetor_fornecedores = explode(',', $_GET['id_fornecedores']);
    //Busca o nome do Funcionário que está gerando o Relatório de Cotação ...
    $sql = "SELECT nome 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_funcionario = bancos::sql($sql);
?>
<html>
<title>.:: Gerar Relatório de Cotação ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    if(typeof(elementos['txt_departamento[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_departamento[]'].length)
    }
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('txt_departamento'+i).value == '') {
            alert('DIGITE O DEPARTAMENTO !')
            document.getElementById('txt_departamento'+i).focus()
            return false
        }
        if(document.getElementById('txt_ac_cuidados'+i).value == '') {
            alert('DIGITE O A/C !')
            document.getElementById('txt_ac_cuidados'+i).focus()
            return false
        }
    }
    nova_janela('pdf/relatorio_cotacao.php', 'relatorio', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
}

function perguntar() {
    if(!redefinir('document.form', 'REDEFINIR OS CAMPOS ')) {
        return false
    }
    document.form.elements[1].focus()
}
</Script>
</head>
<body onload='document.form.elements[1].focus()'>
<form name='form' method='post' action='pdf/relatorio.php' onsubmit='return validar()' target='relatorio'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Gerar Relatório de Cotação N.º
            <font color='yellow'>
                <?=$_GET['id_cotacao'];?>
            </font>
        </td>
    </tr>
<?
    for($i = 0; $i < count($vetor_fornecedores); $i ++) {
        //Busca dados do Fornecedor atual do Loop ...
        $sql = "SELECT razaosocial, fone1, fax 
                FROM `fornecedores` 
                WHERE `id_fornecedor` = '$vetor_fornecedores[$i]' LIMIT 1 ";
        $campos     = bancos::sql($sql);
?>
    <tr class='linhanormal'>
        <td>
            <b>Fornecedor:</b>
        </td>
        <td>
            <?=$campos[0]['razaosocial'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Departamento:</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_departamento[]' id='txt_departamento<?=$i;?>' value='VENDAS' title='Digite o Departemento' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color="darkblue">
                <b>A/C:</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_ac_cuidados[]' id='txt_ac_cuidados<?=$i;?>' title='Digite o Departemento' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_lista_fornecedor' id='chkt_lista_fornecedor<?=$i;?>' value='S' class='checkbox'>
            <label for='chkt_lista_fornecedor<?=$i;?>'>
                <b>Preencher Dados da Lista do Fornecedor:</b>
            </label>
        </td>
    </tr>	
    <tr class='linhanormal'>
        <td>
            <b>Fone: </b><?=$campos[0]['fone1'];?>
        </td>
        <td>
            <b>Fax: </b><?=$campos[0]['fax'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Comprador:</b>
        </td>
        <td>
            <?=$campos_funcionario[0]['nome'];?>
        </td>
    </tr>
<?
        if(($i + 1) != count($vetor_fornecedores)) {//Quando for o último Fornecedor, não exibo essa Linha ...
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="return perguntar()" style="color:#ff9900" class='botao'>
            <input type='submit' name="cmd_avancar" value="&gt;&gt; Avançar &gt;&gt; " title="Avançar" class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_cotacao' value='<?=$_GET['id_cotacao'];?>'>
<input type='hidden' name='id_fornecedores' value='<?=$_GET['id_fornecedores'];?>'>
<input type='hidden' name='funcionario' value='<?=$campos_funcionario[0]['nome'];?>'>
</form>
</body>
</html>
<?
}else {
    $total_vinculado        = 0;
    $fornecedor_vinculado   = 'N';
    $frase                  = '';
    
    $vetor_fornecedores     = explode(',', $_POST['id_fornecedores']);
    $linhas_fornecedores    = count($vetor_fornecedores);

    //Aqui eu busco todos os PI(s) gravados da Cotação passada por parâmetro ...
    $sql = "SELECT DISTINCT(id_produto_insumo) AS id_produto_insumo 
            FROM `cotacoes_itens` 
            WHERE `id_cotacao` = '$_POST[id_cotacao]' ";
    $campos_itens_cotacao   = bancos::sql($sql);
    $linhas_itens_cotacao   = count($campos_itens_cotacao);
       
    for($i = 0; $i < $linhas_fornecedores; $i++) {//Dispara o Loop dos Fornecedores ...
        for($j = 0; $j < $linhas_itens_cotacao; $j++) {//Dispara o Loop dos Produtos Insumos ...
            //Verifica se esse "Fornecedor" atual do Loop tem em sua Lista de Preço esse "Produto Insumo" atual do Loop ...
            $sql = "SELECT id_fornecedor_prod_insumo 
                    FROM `fornecedores_x_prod_insumos` 
                    WHERE `id_fornecedor` = '$vetor_fornecedores[$i]' 
                    AND `id_produto_insumo` = '".$campos_itens_cotacao[$j]['id_produto_insumo']."' LIMIT 1 ";
            echo $sql;
            $campos_lista_preco = bancos::sql($sql);
            if(count($campos_lista_preco) == 0) {//Produto Insumo não existente p/ o Fornecedor ...
                if($fornecedor_vinculado == 'N') {
                    //Busca da Razão Social p/ Mostrar ao Usuário qual Fornecedor teve PI vinculado na Lista de Preço ...
                    $sql = "SELECT razaosocial 
                            FROM `fornecedores` 
                            WHERE `id_fornecedor` = '$vetor_fornecedores[$i]' LIMIT 1 ";
                    $campos = bancos::sql($sql);
                    $frase.= 'PRODUTOS VINCULADOS AO FORNECEDOR '.$campos[0]['razaosocial'].': '.'\n';
                    $fornecedor_vinculado = 'S';//Controle p/ não concatenar + de uma vez o Nome do Fornecedor na Frase
                }
                //Insere o PI na Lista de Preço do Fornecedor do Looop ...
                $sql = "INSERT INTO `fornecedores_x_prod_insumos` (`id_fornecedor_prod_insumo`, `id_fornecedor`, `id_produto_insumo`) VALUES (NULL, '$vetor_fornecedores[$i]', '".$campos_itens_cotacao[$j]['id_produto_insumo']."') ";
                bancos::sql($sql);
                //Busca da Discriminação p/ Mostrar ao Usuário qual Produto Insumo foi vinculado a Fornecedor na Lista de Preço ...
                $sql = "SELECT discriminacao 
                        FROM `produtos_insumos` 
                        WHERE `id_produto_insumo` = '".$campos_itens_cotacao[$j]['id_produto_insumo']."' LIMIT 1 ";
                $campos_pi = bancos::sql($sql);
                $produtos.= '* '.$campos_pi[0]['discriminacao'].', \n';
                $total_vinculado++;
            }
        }
        $produtos               = substr($produtos, 0, strlen($produtos) - 2);
        $fornecedor_vinculado   = 'N';//Por ser fim do Loop irá mudar o Fornecedor, daí normal voltar a variável p/ N ...
        $frase.= $produtos.' \n\n';
        unset($produtos);//P/ não herdar Valores do Loop anterior ...
    }
    if($total_vinculado > 0) {//Significa que existiu pelo menos 1 vínculo de PI p/ Fornecedor em Lista de Preço ...
?>
    <Script Language = 'JavaScript'>
        alert('<?=$frase;?>')
    </Script>
<?
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'cotacao_vs_fornecedor.php?passo=1&id_cotacao=<?=$_POST['id_cotacao'];?>&id_fornecedores=<?=$_POST['id_fornecedores'];?>'
    </Script>
<?}?>