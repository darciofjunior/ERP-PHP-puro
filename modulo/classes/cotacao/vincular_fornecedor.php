<?
require('../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/nivel_estoque/index.php', '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    //Procedimento normal de quando se carrega a Tela ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $opt_internacional  = $_POST['opt_internacional'];
        $txt_consultar      = $_POST['txt_consultar'];
        $opt_opcao          = $_POST['opt_opcao'];
        $id_cotacao         = $_POST['id_cotacao'];
    }else {
        $opt_internacional  = $_GET['opt_internacional'];
        $txt_consultar      = $_GET['txt_consultar'];
        $opt_opcao          = $_GET['opt_opcao'];
        $id_cotacao         = $_GET['id_cotacao'];
    }
    $condicao = ($opt_internacional == 1) ? " AND `id_pais` <> '31' " : " AND `id_pais` = '31' ";
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT `id_fornecedor`, `razaosocial`, `cnpj_cpf`, `produto` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` LIKE '%$txt_consultar%' 
                    AND `razaosocial` <> '' 
                    AND `ativo` = '1' 
                    $condicao 
                    ORDER BY `razaosocial` ";
        break;
        case 2:
            $consultar = str_replace('.', '', $consultar);
            $consultar = str_replace('.', '', $consultar);
            $consultar = str_replace('/', '', $consultar);
            $consultar = str_replace('-', '', $txt_consultar);
            
            $sql = "SELECT `id_fornecedor`, `razaosocial`, `cnpj_cpf`, `produto` 
                    FROM `fornecedores` 
                    WHERE `cnpj` LIKE '%$consultar%' 
                    AND `razaosocial` <> '' 
                    AND `ativo` = '1' 
                    $condicao 
                    ORDER BY `razaosocial` ";
        break;
        default:
            $sql = "SELECT `id_fornecedor`, `razaosocial`, `cnpj_cpf`, `produto` 
                    FROM `fornecedores` 
                    WHERE `razaosocial` <> '' 
                    AND `ativo` = '1' 
                    $condicao 
                    ORDER BY `razaosocial` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'vincular_fornecedor.php?id_cotacao=<?=$_POST['id_cotacao'];?>&valor=1'
    </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Fornecedor(es) p/ Vincular a Cotação ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'cores.js'></Script>
<Script Language = 'JavaScript'>
function fornecedores_geral() {
    var elementos   = document.form.elements
    var checar      = (elementos['chkt_tudo'].checked) ? true : false
    
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(checar == true) {
                document.form.id_fornecedores.value+= elementos[i].value + ', '
            }else {
                document.form.id_fornecedores.value = ''
                break
            }
        }
    }
}

function agregar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++)   {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        window.location = 'vincular_fornecedor.php<?=$parametro;?>&id_fornecedores='+document.form.id_fornecedores.value+', '
    }
}

function vincular_fornecedores(indice, id_fornecedor) {
    if(document.getElementById('chkt_fornecedor'+indice).checked == true) {
        //Verifico se o id_fornecedor já existe no hidden de Fornecedores, senão existir então eu adiciono o mesmo ...
        if(document.form.id_fornecedores.value.indexOf(document.getElementById('chkt_fornecedor'+indice).value + ', ') == -1) {//Não existe ...
            document.form.id_fornecedores.value+= document.getElementById('chkt_fornecedor'+indice).value + ', '
        }
    }else {
        //Verifico se o id_fornecedor já existe no hidden de Fornecedores, se existir então eu retiro o mesmo ...
        if(document.form.id_fornecedores.value.indexOf(id_fornecedor + ', ') != -1) {
            document.form.id_fornecedores.value = document.form.id_fornecedores.value.replace(id_fornecedor + ', ', '')
        }
    }
}

function validar() {
    if(document.form.id_fornecedores.value == '') {
        alert('AGREGUE PELO MENOS UM FORNECEDOR A ESTÁ COTAÇÃO !')
        return false
    }
    document.form.id_fornecedores.value = document.form.id_fornecedores.value.substr(0, document.form.id_fornecedores.value.length - 1)
    pergunta = confirm('VOCÊ ESTÁ PRESTES A VINCULAR TODOS OS ITENS DA COTAÇÃO N.º <?=$id_cotacao;?> PARA ESSES FORNECEDORES SELECIONADOS !\n\n DESEJA CONTINUAR ?')
    if(pergunta == true) {
        //Verifico se o Último Caractér do Hidden é Vírgula, se sim, retiro a mesma p/ não Ferrar na próxima Tela ...
        if(document.form.id_fornecedores.value.substr((document.form.id_fornecedores.value.length - 1), 1) == ',') {
            document.form.id_fornecedores.value = document.form.id_fornecedores.value.substr(0, document.form.id_fornecedores.value.length - 1)
        }
        return true
    }else {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='cotacao_vs_fornecedor.php' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Fornecedor(es) p/ Vincular a Cotação N.º 
            <font color='yellow'>
                <?=$id_cotacao;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Razão Social
        </td>
        <td>
            CNPJ / CPF
        </td>
        <td>
            Produto
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8');fornecedores_geral()" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
        //Aqui eu verifico todos os Fornecedores que já foram agregados ...
        $id_fornecedores        = substr($id_fornecedores, 0, strlen($id_fornecedores) - 1);
        $vetor_fornecedores     = explode(', ', $id_fornecedores);
        $total_checkado         = 0;

        for ($i = 0; $i < $linhas; $i++) {
            //Verifico se o id_fornecedor que está sendo listado no Loop está dentre a relação dos que foram agregados ...
            if(in_array($campos[$i]['id_fornecedor'], $vetor_fornecedores)) {
                $checked = 'checked';
                if($total_checkado != 10) $total_checkado++;
            }else {
                $checked = '';
            }
            if($checked == 'checked') {
                $class          = 'linhasub';
                $onclick        = "checkbox_2('form', 'chkt_tudo', '$i', '#C6E2FF', '#E8E8E8')";
                $onmouseover    = "sobre_celula_2(this, '#CCFFCC')";
            }else {
                $class          = 'linhanormal';
                $onclick        = "checkbox('form', 'chkt_tudo', '$i', '#E8E8E8')";
                $onmouseover    = "sobre_celula(this, '#CCFFCC')";
                $total_checkado--;
            }
?>
    <tr class='<?=$class;?>' onclick="<?=$onclick;?>;vincular_fornecedores('<?=$i;?>', '<?=$campos[$i]['id_fornecedor'];?>')" onmouseover="" onmouseout="fora_celula(this, '#E8E8E8')">
        <td>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td align='center'>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['produto'];?>
        </td>
        <td align='center'>
            <input type='checkbox' name='chkt_fornecedor[]' id='chkt_fornecedor<?=$i;?>' value="<?=$campos[$i]['id_fornecedor'];?>" onclick="<?=$onclick;?>;fornecedores('<?=$i;?>', '<?=$campos[$i]['id_fornecedor'];?>')" class='checkbox' <?=$checked;?>>
        </td>
    </tr>
<?
        }

        if($total_checkado == $i) {
?>
            <Script Language = 'JavaScript'>
                document.form.chkt_tudo.checked = true
            </Script>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='8'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'vincular_fornecedor.php?id_cotacao=<?=$id_cotacao;?>'" class='botao'>
            <input type='button' name='cmd_agregar' value='Agregar' title='Agregar' onclick='agregar()' class='botao'>
            <input type='submit' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_fornecedores' value='<?=$id_fornecedores;?>'>
<input type='hidden' name='id_cotacao' value='<?=$id_cotacao;?>'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Consultar Fornecedor(es) p/ Vincular a Cotação ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        for(i = 0; i < 2; i++) document.form.opt_opcao[i].disabled = false
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
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Fornecedor(es) p/ Vincular a Cotação N.º 
            <font color='yellow'>
                <?=$_GET['id_cotacao'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type="text" name="txt_consultar" size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar fornecedor por: Razão Social' onclick='desabilitar()' id='label' checked>
            <label for='label'>Razão Social</label>
        </td>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Consultar cliente por: CNPJ ou CPF' onclick='desabilitar()' id='label2'>
            <label for='label2'>CNPJ / CPF</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='opt_internacional' value='1' title='Consultar Fornecedores Internacionais' id='label3' class='checkbox'>
            <label for='label3'>Internacionais</label>
        </td>
        <td>
            <input type='checkbox' name='opcao' value='1' title='Consultar todos os Fornecedores' onclick='limpar()' id='label4' class='checkbox'>
            <label for='label4'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_cotacao' value='<?=$_GET['id_cotacao'];?>'>
</form>
</body>
</html>
<?}?>