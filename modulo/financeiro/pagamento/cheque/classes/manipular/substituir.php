<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/menu/menu.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/genericas.php');
session_start('funcionarios');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/cheque/albafer/index.php';
    $endereco_volta = 'albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/cheque/tool_master/index.php';
    $endereco_volta = 'tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/cheque/grupo/index.php';
    $endereco_volta = 'grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = '<font class="atencao">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';
$mensagem[2] = '<font class="confirmacao">CHEQUE SUBSTITUÍDO COM SUCESSO.</font>';

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT c.*, t.num_inicial as num_inicial , t.num_final as num_final, t.id_talao as cod_talao, cc.conta_corrente as conta_corrente, a.nome_agencia as nome_agencia, a.cod_agencia as cod_agencia, b.banco as banco, a.id_agencia as id_agencia, cc.id_contacorrente AS id_contacorrente 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.id_talao = c.id_talao AND t.ativo = '1' 
                    INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente AND cc.`id_empresa` = '$id_emp2' 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE c.`num_cheque` LIKE '$txt_consultar%' 
                    AND c.`status` = '2' 
                    AND c.`ativo` = '1' ORDER BY c.num_cheque DESC ";
        break;
        default:
            $sql = "SELECT c.*, t.num_inicial as num_inicial , t.num_final as num_final, t.id_talao as cod_talao, cc.conta_corrente as conta_corrente, a.nome_agencia as nome_agencia, a.cod_agencia as cod_agencia, b.banco as banco, a.id_agencia as id_agencia, cc.id_contacorrente AS id_contacorrente 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.id_talao = c.id_talao AND t.ativo = '1' 
                    INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente AND cc.`id_empresa` = '$id_emp2' 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE c.`status` = '2' 
                    AND c.`ativo` = '1' ORDER BY c.num_cheque DESC ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {	
?>
    <Script Language = 'JavaScript'>
        window.location = 'substituir.php?valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Cheque(s) p/ Substituir ::.</title>
<meta http-equiv = 'Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content='no-store'>
<meta http-equiv = 'pragma' content='no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Cheque(s) p/ Substituir
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º Cheque
        </td>
        <td>
            Número Inicial
        </td>
        <td>
            Conta Corrente
        </td>
        <td>
            Agência
        </td>
        <td>
            Banco
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = 'substituir.php?passo=2&id_cheque_antigo='.$campos[$i]['id_cheque'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="<?=$url;?>" title='Cheques do tal&atilde;o <?=$campos[$i]['num_inicial'];?>/<?=$campos[$i]['num_final'];?>' class='link'>
                <?=$campos[$i]['num_cheque'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['num_inicial'];?>
        </td>
        <td>
            <?=$campos[$i]['conta_corrente'];?>
        </td>
        <td>
            <?=$campos[$i]['cod_agencia'];?>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' onclick="window.location = 'substituir.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
?>
<html>
<head>
<title>.:: Cheque(s) p/ Substituir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='3'>
<input type='hidden' name='id_cheque_antigo' value='<?=$_GET[id_cheque_antigo];?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cheque(s) p/ Substituir o Cheque N.º 
            <font color='yellow'>
            <?
                //Aqui seleciona o número cheque do antigo ...
                $sql = "SELECT num_cheque 
                        FROM `cheques` 
                        WHERE `id_cheque` = '$_GET[id_cheque_antigo]' LIMIT 1 ";
                $campos_cheque_antigo = bancos::sql($sql);
                echo $campos_cheque_antigo[0]['num_cheque'].' - '.genericas::nome_empresa($id_emp2);
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name="opt_opcao" id='opt1' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar Cheque por Número do Cheque' checked>
            <label for='opt1'>Número do Cheque</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' id='opcao' value='1' title="Consultar todos os Cheques" onclick='limpar()' class='checkbox'>
            <label for='opcao'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'substituir.php<?=$parametro;?>&passo=1'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    //Procedimento quando carrega a Tela ...
    $id_cheque_antigo = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_cheque_antigo'] : $_GET['id_cheque_antigo'];

    //Seleciono a empresa do cheque antigo ...
    $sql = "SELECT cc.id_empresa AS id_empresa_cheque 
            FROM `cheques` c 
            INNER JOIN `taloes` t ON t.id_talao = c.id_talao 
            INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente 
            WHERE c.`id_cheque` = '$id_cheque_antigo' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $id_empresa_cheque  = $campos[0]['id_empresa_cheque'];

//Trago todos cheques que ainda estão em abertos independente do Banco, mas da empresa do cheque antigo ...
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT c.*, t.num_inicial as num_inicial , t.num_final as num_final, t.id_talao as cod_talao, cc.conta_corrente as conta_corrente, a.nome_agencia as nome_agencia, a.cod_agencia as cod_agencia, b.banco as banco, a.id_agencia as id_agencia, cc.id_contacorrente AS id_contacorrente 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.id_talao = c.id_talao AND t.ativo = '1' 
                    INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente AND cc.`id_empresa` = '$id_empresa_cheque' 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE c.`num_cheque` LIKE '$txt_consultar%' 
                    AND c.`id_cheque` <> '$id_cheque_antigo' 
                    AND c.`status` <= '2' 
                    AND c.`ativo` = '1' ORDER BY b.banco, c.num_cheque ";
        break;
        default:
            $sql = "SELECT c.*, t.num_inicial as num_inicial , t.num_final as num_final, t.id_talao as cod_talao, cc.conta_corrente as conta_corrente, a.nome_agencia as nome_agencia, a.cod_agencia as cod_agencia, b.banco as banco, a.id_agencia as id_agencia, cc.id_contacorrente AS id_contacorrente 
                    FROM `cheques` c 
                    INNER JOIN `taloes` t ON t.id_talao = c.id_talao AND t.ativo = '1' 
                    INNER JOIN `contas_correntes` cc ON cc.id_contacorrente = t.id_contacorrente AND cc.`id_empresa` = '$id_empresa_cheque' 
                    INNER JOIN `agencias` a ON a.id_agencia = cc.id_agencia 
                    INNER JOIN `bancos` b ON b.id_banco = a.id_banco 
                    WHERE c.`id_cheque` <> '$id_cheque_antigo' 
                    AND c.`status` <= '2' 
                    AND c.`ativo` = '1' ORDER BY b.banco, c.num_cheque ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'substituir.php<?=$parametro;?>&passo=2&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Substituir Cheque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src='../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src='../../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar Cheque(s) p/ Substituir o Cheque N.º 
            <font color='yellow'>
            <?
                //Aqui seleciona o número cheque do antigo ...
                $sql = "SELECT num_cheque 
                        FROM `cheques` 
                        WHERE `id_cheque` = '$id_cheque_antigo' LIMIT 1 ";
                $campos_cheque_antigo = bancos::sql($sql);
                echo $campos_cheque_antigo[0]['num_cheque'].' - '.genericas::nome_empresa($id_emp2);
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º Cheque
        </td>
        <td>
            Número Inicial
        </td>
        <td>
            Conta Corrente
        </td>
        <td>
            Agência
        </td>
        <td>
            Banco
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
            $url = 'substituir.php?passo=4&id_cheque_antigo='.$id_cheque_antigo.'&id_cheque_novo='.$campos[$i]['id_cheque'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="<?=$url;?>" title='Cheques do tal&atilde;o <?=$campos[$i]['num_inicial'];?>/<?=$campos[$i]['num_final'];?>' class='link'>
                <?=$campos[$i]['num_cheque'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['num_inicial'];?>
        </td>
        <td>
            <?=$campos[$i]['conta_corrente'];?>
        </td>
        <td>
            <?=$campos[$i]['cod_agencia'];?>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' onclick="window.location = 'substituir.php<?=$parametro;?>&passo=2'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 4) {
    //Seleciona todos os dados do cheque Antigo ...
    $sql = "SELECT * 
            FROM `cheques` 
            WHERE `id_cheque` = '$_GET[id_cheque_antigo]' LIMIT 1 ";
    $campos_cheque_antigo = bancos::sql($sql);

    //Seleciona todos os dados do cheque Novo ...
    $sql = "SELECT * 
            FROM `cheques` 
            WHERE `id_cheque` = '$_GET[id_cheque_novo]' LIMIT 1 ";
    $campos_cheque_novo = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Substituir Cheque ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var resposta = confirm('VOCÊ ESTÁ PRESTES A SUBSTITUIR O CHEQUE N.º <?=$campos_cheque_antigo[0]["num_cheque"];?> PELO CHEQUE N.º <?=$campos_cheque_novo[0]["num_cheque"];?> !\n DESEJA SUBSTITUIR REALMENTE ?')
    if(resposta == true) {
        return true
    }else {
        return false
    }
}
</script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=5'?>" onsubmit='return validar()'>
<!--************************Controle de Tela************************-->
<input type='hidden' name='id_cheque_antigo' value='<?=$_GET['id_cheque_antigo'];?>'>
<input type='hidden' name='id_cheque_novo' value='<?=$_GET['id_cheque_novo'];?>'>
<!--****************************************************************-->
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Substituir Cheque N.º <?=$campos_cheque_antigo[0]['num_cheque'];?> pelo Cheque N.º <?=$campos_cheque_novo[0]['num_cheque'];?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º Cheque
        </td>
        <td>
            Histórico
        </td>
        <td>
            Valor
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos_cheque_antigo[0]['num_cheque'];?>
        </td>
        <td>
            <?=$campos_cheque_antigo[0]['historico'];?>
        </td>
        <td>
        <?
            echo 'R$ '.number_format($campos_cheque_antigo[0]['valor'], 2, ',', '.');
            //Essa variável armazena o valor do cheque antigo
            $valor = $campos_cheque_antigo[0]['valor'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos_cheque_novo[0]['num_cheque'];?>
        </td>
        <td>
            <?=$campos_cheque_novo[0]['historico'];?>
        </td>
        <td>
        <?
            echo 'R$ '.number_format($campos_cheque_novo[0]['valor'], 2, ',', '.');
//Aqui ela já possui o valor do cheque antigo e acrescenta com o do cheque novo
            $valor = $valor + $campos_cheque_novo[0]['valor'];
        ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='3'>
            Dados do Cheque N.º 
            <font color='yellow'>
                <?=$campos_cheque_novo[0]['num_cheque'];?> 
            </font>
            após as alterações
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos_cheque_novo[0]['num_cheque'];?>
        </td>
        <td>
            <?=$campos_cheque_antigo[0]['historico'];?>
        </td>
        <td>
            <?='R$ '.number_format($valor, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' onclick="window.location = 'substituir.php<?=$parametro;?>'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 5) {
    // Busca o valor do cheque antigo ...
    $sql = "SELECT historico, valor, predatado 
            FROM `cheques` 
            WHERE `id_cheque` = '$_POST[id_cheque_antigo]' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $historico  = $campos[0]['historico'];
    $valor      = $campos[0]['valor'];
    $predatado  = $campos[0]['predatado'];
    //Substitui todas as contas que estão utilizando o cheque antigo pelo cheque novo ...
    $sql = "UPDATE `contas_apagares_quitacoes` SET `id_cheque` = '$_POST[id_cheque_novo]' WHERE `id_cheque` = '$_POST[id_cheque_antigo]' ";
    bancos::sql($sql);

    //Cancela o cheque antigo ...
    $sql = "UPDATE `cheques` SET `status` = '4' WHERE `id_cheque` = '$_POST[id_cheque_antigo]' LIMIT 1 ";
    bancos::sql($sql);

//Atualizo o valor p/ o cheque novo e mudo o status p/ 2 constando que esse já foi emitido ...
    $sql = "UPDATE `cheques` SET `historico` = '$historico' , valor = valor + '$valor', predatado = '$predatado', status = 2 WHERE `id_cheque` = '$_POST[id_cheque_novo]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'substituir.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Cheque(s) p/ Substituir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type='text/css' rel='stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method="post" action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cheque(s) p/ Substituir
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name="opt_opcao" id='opt1' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar Cheque por Número do Cheque' checked>
            <label for='opt1'>Número do Cheque</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' id='opcao' value='1' title="Consultar todos os Cheques" onclick='limpar()' class='checkbox'>
            <label for='opcao'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../../<?=$endereco_volta;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>