<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/biblioteca.php');
segurancas::geral($PHP_SELF, '../../../../../');
$mensagem[1] = '<font class="erro">SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';

$ano1       = (int)date('Y');
$ano2       = $ano1-1;
$ano3       = $ano2-1;
$data_atual = date('Y-m-d');
$clientes   = biblioteca::controle_itens($id_cliente, $id_cliente2, $acao);
sleep(1);

if($passo == 1) {
//Esse controle é feito a partir do momento em q é adicionado um cliente para o combo de clientes selecionados ...
    if(!empty($_POST['txt_cnpj_cpf'])) {
        $txt_cnpj_cpf   = $_POST['txt_cnpj_cpf'];
        $txt_cnpj_cpf   = str_replace('.', '', $txt_cnpj_cpf);
        $txt_cnpj_cpf   = str_replace('-', '', $txt_cnpj_cpf);
        $txt_cnpj_cpf   = str_replace('/', '', $txt_cnpj_cpf);
    }

    if(!empty($_POST['cmb_compra_ultimos_meses'])) {
        if($_POST['cmb_compra_ultimos_meses'] == 6) {//6 Meses
            $dias = 180;
        }else if($_POST['cmb_compra_ultimos_meses'] == 12) {//12 Meses
            $dias = 365;
        }else if($_POST['cmb_compra_ultimos_meses'] == 18) {//18 Meses
            $dias = 545;
        }else if($_POST['cmb_compra_ultimos_meses'] == 24) {//24 Meses
            $dias = 730;
        }else if($_POST['cmb_compra_ultimos_meses'] == 30) {//30 Meses
            $dias = 910;
        }else if($_POST['cmb_compra_ultimos_meses'] == 36) {//36 Meses
            $dias = 1095;
        }
        $condicao_nfs = "INNER JOIN `nfs` ON nfs.`id_cliente` = c.`id_cliente` AND nfs.`data_emissao` BETWEEN (DATE_ADD('$data_atual', INTERVAL -$dias DAY)) AND '$data_atual' ";
    }

    if($cmb_uf == '') $cmb_uf = '%';
//Só exibe Clientes que contém Representante
    if($cmb_representante == '') $cmb_representante = '%';

    if(!empty($_POST['cmb_novo_tipo_cliente'])) {
        foreach($_POST['cmb_novo_tipo_cliente'] as $id_novo_tipo_cliente) $id_clientes_tipos.= $id_novo_tipo_cliente.', ';
        $id_clientes_tipos          = substr($id_clientes_tipos, 0, strlen($id_clientes_tipos) - 2);
        $condicao_tipos_clientes    = " AND c.`id_cliente_tipo` IN ($id_clientes_tipos) ";
    }

    $sql = "SELECT distinct(c.id_cliente), c.*, r.nome_fantasia as representante 
            FROM clientes c 
            $condicao_nfs 
            INNER JOIN clientes_vs_representantes cr ON c.id_cliente = cr.id_cliente AND cr.id_representante LIKE '$cmb_representante' 
            INNER JOIN representantes r ON r.id_representante = cr.id_representante 
            WHERE c.nomefantasia LIKE '%$_POST[txt_nome_fantasia]%' 
            AND c.razaosocial LIKE '%$_POST[txt_razao_social]%' 
            AND c.`cnpj_cpf` LIKE '%$txt_cnpj_cpf%' 
            AND c.ativo = '1' 
            AND c.bairro LIKE '%$_POST[txt_bairro]%' 
            AND c.cidade LIKE '%$_POST[txt_cidade]%' 
            AND c.id_uf LIKE '$cmb_uf' 
            $condicao_tipos_clientes 
            GROUP BY c.`id_cliente` ORDER BY c.`razaosocial` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
/*Aqui eu volto o parâmetro de clientes, para não perder os clientes q foram selecionados anteriormente e 
refazer a consulta novamente*/
            window.location = 'etiquetas.php?id_cliente=<?=$id_cliente;?>&valor=1'
        </Script>
<?
        exit;
    }
}
?>
<html>
<head>
<title>.:: Consultar Clientes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
/*************************************************************************/
/*Funções referentes a segunda tela depois da consulta - Passo = 1*/
function enviar() {
    var elementos = document.form.elements
    var selecionados = 0, id_cliente = ''
    var achou_combo = 0
    for (i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a segunda combo
            if(achou_combo == 2) {
                for(j = 1; j < document.form.elements[i].length; j++) {
                    if(document.form.elements[i][j].selected == true) {
                        selecionados ++
                        id_cliente = id_cliente + document.form.elements[i][j].value + ','
                    }
                }
                i = elementos.length
            }
        }
    }

    if(selecionados == 0) {
        alert('SELECIONE UM CLIENTE !')
        return false
    }

    document.form.id_cliente2.value = id_cliente.substr(0, id_cliente.length - 1)
    document.form.action = 'etiquetas.php'
    document.form.target = '_self'
    document.form.submit()
}

function selecionar_todos() {
    var elementos   = document.form.elements
    var achou_combo = 0
    for (var i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
            for(j = 1; j < document.form.elements[i].length; j++) {
//Aqui tem esse macete porque é para controlar a segunda combo
                if(achou_combo == 2) document.form.elements[i][j].selected = true
            }
        }
    }
}

/*************************************************************************/
/*Funções referentes a terceira tela depois da consulta - !empty($clientes)*/
function retirar_cliente() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
    var flag = 0, cliente_sel = ''
    var achou_combo = 0
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a segunda combo
            if(achou_combo == 2) {
                if(document.form.elements[i].value == '') {
                    if(flag == 0) alert('SELECIONE PELO MENOS UM CLIENTE !')
                    document.form.elements[i].focus()
                    return false
                }else {
                    for(j = 0; j < document.form.elements[i].length; j ++) {
                        if(document.form.elements[i][j].selected == true) cliente_sel = cliente_sel + document.form.elements[i][j].value + ','
                    }
                }
                flag++
            }
        }
    }
    cliente_sel = cliente_sel.substr(0, cliente_sel.length - 1)
    document.form.id_cliente2.value = cliente_sel
    document.form.acao.value = 1
    document.form.action = 'etiquetas.php'
    document.form.target = '_self'
    document.form.submit()
}

function selecionar_todos_clientes() {
    var i, elementos = document.form.elements
    var selecionados = ''
    var achou_combo = 0
    for (i = 0; i < elementos.length; i ++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a terceira combo
            if(achou_combo == 2) {
                for(j = 1; j < document.form.elements[i].length; j++) document.form.elements[i][j].selected = true
            }
        }
    }
}

function imprimir_etiquetas() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
    var flag = 0
    var achou_combo = 0, selecionados = 0
    selecionar_todos_clientes()
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a terceira combo
            if(achou_combo == 2) {
                if(document.form.elements[i].value == '') {
                    if(flag == 0) alert('SELECIONE PELO MENOS UM CLIENTE !')
                    document.form.elements[i].focus()
                    return false
                }else {
                    for(j = 0; j < document.form.elements[i].length; j ++) {
                        if(document.form.elements[i][j].selected == true) selecionados ++
                    }
                }
                flag++
            }
        }
    }
    if(selecionados == 0) {
        alert('SELECIONE UM CLIENTE !')
        return false
    }
/*Se não estiver selecionado a opção de Checkbox Preencher ou Ignorar Depto, então eu forço o usuário
a preencher o Departamento*/
    if(document.form.chkt_preencher_ignorar_depto.checked == false) {
        if(!combo('form', 'cmb_departamento', '', 'SELECIONE UM DEPARTAMENTO !')) {
            document.form.cmb_departamento.focus()
            return false
        }
    }
//Se essa opção estiver habilitada, então eu forço o Preenchimento do Contato ...
    if(document.form.chkt_preencher_contato.checked == true) {
        if(!texto('form', 'txt_contato', '3', "-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,'.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ", 'CONTATO', '2')) {
            return false
        }
    }
//Destino
    document.form.action = 'imprimir_etiquetas.php'
    document.form.target = 'novajanela'
    nova_janela('imprimir_etiquetas.php', 'novajanela', 'F')
    document.form.submit()
}

function preencher_ignorar_depto() {
    if(document.form.chkt_preencher_ignorar_depto.checked == true) {//Habilitado
        document.form.txt_departamento.disabled     = false
        document.form.txt_departamento.className    = 'caixadetexto'
        document.form.txt_departamento.focus()
    }else {//Desabilitado
        document.form.txt_departamento.disabled     = true
        document.form.txt_departamento.className    = 'textdisabled'
        document.form.txt_departamento.value = ''
    }
}

function preencher_contato() {
    if(document.form.chkt_preencher_contato.checked == true) {//Habilitado
        document.form.txt_contato.disabled  = false
        document.form.txt_contato.className = 'caixadetexto'
        document.form.txt_contato.focus()
    }else {//Desabilitado
        document.form.txt_contato.disabled  = true
        document.form.txt_contato.className = 'textdisabled'
        document.form.txt_contato.value     = ''
    }
}

function alterar_departamento() {
    if(document.form.cmb_departamento.value != '') {
        if(document.form.txt_contato.disabled == false) {
//Departamento
            document.form.chkt_preencher_ignorar_depto.checked = false
            document.form.txt_departamento.disabled     = true
            document.form.txt_departamento.className    = 'textdisabled'
            document.form.txt_departamento.value        = ''
//Contato
            document.form.chkt_preencher_contato.checked = false
            document.form.txt_contato.disabled          = true
            document.form.txt_contato.className         = 'textdisabled'
            document.form.txt_contato.value             = ''
        }
    }
}

function clientes_selecionados() {
    //Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
    var flag = 0
    var achou_combo = 0, selecionados = 0
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a terceira combo
            if(achou_combo == 2) {
                if(document.form.elements[i].value == '') {
                    if(flag == 0) alert('SELECIONE PELO MENOS UM CLIENTE !')
                    document.form.elements[i].focus()
                    return false
                }else {
                    for(j = 0; j < document.form.elements[i].length; j ++) {
                        if(document.form.elements[i][j].selected == true) selecionados ++
                    }
                }
                flag++
            }
        }
    }
//Se eu tiver mais de um Cliente selecionado, não faz sentido deixar habilitado 
    if(selecionados > 1) {
//Desabilita a caixa de departamento - preencher_ignorar_depto() ...
        document.form.chkt_preencher_ignorar_depto.checked  = false
        document.form.txt_departamento.disabled             = true
        document.form.txt_departamento.className            = 'textdisabled'
        document.form.txt_departamento.value                = ''
//Desabilita a caixa de contato - preencher_contato() ...
        document.form.chkt_preencher_contato.checked        = false
        document.form.txt_contato.disabled                  = true
        document.form.txt_contato.className                 = 'textdisabled'
        document.form.txt_contato.value                     = ''
    }
}
</Script>
</head>
<body onload='document.form.txt_razao_social.focus()'>
<form name='form' method='post' action='' onSubmit='return validar()'>
<input type='hidden' name='passo'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cliente(s) - Imprimir Etiqueta(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Razão Social
        </td>
        <td>
            <input type='text' name='txt_razao_social' title='Digite a Razão Social' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome Fantasia
        </td>
        <td>
            <input type='text' name='txt_nome_fantasia' title='Digite o Nome Fantasia' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CNPJ / CPF
        </td>
        <td>
            <input type='text' name='txt_cnpj_cpf' title='Digite o CNPJ ou CPF' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Bairro
        </td>
        <td>
            <input type='text' name='txt_bairro' title='Digite o Bairro' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade
        </td>
        <td>
            <input type='text' name='txt_cidade' title='Digite a Cidade' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Compras dos últimos
        </td>
        <td>
            <select name='cmb_compra_ultimos_meses' title='Selecione as Últimas Compras' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='6'>6 meses</option>
                <option value='12'>12 meses</option>
                <option value='18'>18 meses</option>
                <option value='24'>24 meses</option>
                <option value='30'>30 meses</option>
                <option value='36'>36 meses</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Estado
        </td>
        <td>
            <select name='cmb_uf' title='Selecione o Estado' class='combo'>
            <?
                $sql = "SELECT id_uf, sigla 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY sigla ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Representante
        </td>
        <td>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Novo Tipo de Cliente
        </td>
        <td>
            <select name='cmb_novo_tipo_cliente[]' title='Selecione o Novo Tipo de Cliente' class='combo' size='5' multiple>
            <?
                //Aqui só não aparece o Grupo de TMKT ...
                $sql = "SELECT id_cliente_tipo, tipo 
                        FROM `clientes_tipos` 
                        WHERE `id_cliente_tipo` NOT IN (11, 12) ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
<?
    if($passo == 1) {
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            <select name='cmb_cliente[]' class='combo' size='5' multiple>
                <option value='' style='color:red'>
                SELECIONE
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </option>
<?
                for($i = 0; $i < $linhas; $i ++) {
?>
                <option value="<?=$campos[$i]['id_cliente'];?>"><?=$campos[$i]['razaosocial'];?></option>
<?
                }
?>
            </select>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir_consulta' value='Redefinir Consulta' title='Redefinir Consulta' onclick='document.form.txt_razao_social.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
<?
        if($passo == 1) {
?>
            <input type='button' name='cmd_selecionar' value='Selecionar Todos Itens' title='Selecionar Todos Itens' onclick='selecionar_todos()' class='botao'>
            <input type='button' name='cmd_adicionar' value='Adicionar Item(ns) Selecionado(s)' title='Adicionar' onclick='enviar()' class='botao'>
<?
        }
?>
        </td>
    </tr>
</table>
<?
/*Nessa parte é simplesmente para mostrar a segunda combo com os clientes selecionados 
da primeira combo que mostrou anteriormente

Obs: Lembrando que ao selecionar os clientes, a combo Principal com todos os clientes 
desaparece ...*/
    if(!empty($clientes)) {
        $sql = "SELECT id_cliente, razaosocial 
                FROM `clientes` 
                WHERE `id_cliente` IN ($clientes) ORDER BY razaosocial ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
?>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Cliente(s) Selecionado(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <select name='cmb_clientes_selecionados[]' onclick='clientes_selecionados()' class='combo' size='5' multiple>
                <option value='' style='color:red'>
                SELECIONE
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                </option>
    <?
            for($i = 0; $i < $linhas; $i++) {
    ?>
                <option value='<?=$campos[$i]['id_cliente']?>'><?=$campos[$i]['razaosocial']?></option>
    <?
            }
    ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='checkbox' name='chkt_preencher_ignorar_depto' value='1' title='Preencher ou Ignorar Depto' onclick='preencher_ignorar_depto()' id='lbl_preencher_ignorar_depto' class='checkbox'>
            <label for='lbl_preencher_ignorar_depto'>
                Preencher ou Ignorar Depto: 
            </label>
            &nbsp;
            <input type='text' name='txt_departamento' title='Digite o Departamento' class='textdisabled' disabled>
            &nbsp;
            <input type='checkbox' name='chkt_preencher_contato' value='1' title='Preencher Contato' onclick='preencher_contato()' id='lbl_preencher_contato' class='checkbox'>
            <label for='lbl_preencher_contato'>
                Preencher Contato:
            </label>
            &nbsp;
            <input type='text' name='txt_contato' title='Digite o Contato' class='textdisabled' disabled>
            &nbsp;
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Departamento: 
            <select name='cmb_departamento' title='Selecione o Departamento' onchange='alterar_departamento()' class='combo'>
            <?
                $sql = "SELECT id_departamento, departamento 
                        FROM `departamentos` 
                        WHERE `ativo` = '1' ORDER BY departamento ";
                echo combos::combo($sql, 'COMPRAS');
            ?>
            </select>
            - Quantidade de Clientes Selecionados para o Envio de Etiquetas:
            <font color='red'><?=$linhas;?></font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='button' name='cmd_retirar2' value='Retirar Item(ns) Selecionado(s)' title='Retirar Item(ns) Selecionado(s)' onclick='retirar_cliente()' style='color:#ff9900' class='botao'>
            <input type='button' name='cmd_imprimir_etiqueta' value='Imprimir Etiqueta(s)' title='Imprimir Etiqueta(s)' onclick='return imprimir_etiquetas()' style='color:black' class='botao'>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
</table>
<?
    }
?>
<input type='hidden' name='id_cliente' value='<?=$clientes;?>'>
<input type='hidden' name='id_cliente2'>
<input type='hidden' name='acao'>
</form>
</body>
<Script Language = 'JavaScript'>
//Função referentes a primeira tela antes de fazer a consulta ...
function validar() {
    document.form.action = 'etiquetas.php'
    document.form.target = '_self'
    document.form.passo.value = 1
}
</Script>
</html>