<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/menu/menu.php');
require('../../../../../lib/biblioteca.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = '<font class=erro >SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>';

$clientes_e_contatos = biblioteca::controle_itens($id_cliente_e_contato, $id_cliente_e_contato2, $acao);

if($passo == 1) {
/*Esse controle é feito a partir do momento em q é adicionado um cliente para o combo de clientes selecionados*/
//Só exibe Clientes que contém Representante
    if($_POST['cmb_representante'] == '') $_POST['cmb_representante'] = '%';
    if($_POST['cmb_uf'] == '')  $_POST['cmb_uf'] = '%';
	
    if(!empty($_POST['cmb_novo_tipo_cliente']) || !empty($_POST['txt_novo_tipo_cliente'])) {
//Somente no Primeiro Filtro que caíra aqui ...
        if(!empty($_POST['cmb_novo_tipo_cliente'])) {//Se estiver preenchida a Combo ...
            foreach($_POST['cmb_novo_tipo_cliente'] as $id_novo_tipo_cliente) $id_clientes_tipos.= $id_novo_tipo_cliente.', ';
//Essa variável será utilizada em JavaScript ...
            $novo_tipo_cliente_string = implode(',', $_POST['cmb_novo_tipo_cliente']);
//Depois de outros filtros em diante cairá nesse Filtro ...
        }else {//Se o Valor estiver guardada em uma Caixa de Texto, então eu transformo esses valores em Array ...
            $vetor_novo_tipo_cliente = explode(',', $_POST['txt_novo_tipo_cliente']);
            foreach($vetor_novo_tipo_cliente as $id_novo_tipo_cliente) $id_clientes_tipos.= $id_novo_tipo_cliente.', ';
//Essa variável será utilizada em JavaScript ...
            $novo_tipo_cliente_string = implode(',', $_POST['txt_novo_tipo_cliente']);
        }
        $id_clientes_tipos = substr($id_clientes_tipos, 0, strlen($id_clientes_tipos) - 2);
        $condicao_tipos_clientes = " AND c.`id_cliente_tipo` IN ($id_clientes_tipos) ";
    }

    $sql = "SELECT DISTINCT(c.id_cliente), c.razaosocial, r.nome_fantasia AS representante 
            FROM `clientes` c 
            INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = c.`id_cliente` AND cr.`id_representante` LIKE '$_POST[cmb_representante]' 
            INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
            WHERE c.`nomefantasia` LIKE '%$_POST[txt_nome_fantasia]%' 
            AND c.`razaosocial` LIKE '%$_POST[txt_razao_social]%' 
            AND c.`ativo` = '1' 
            AND c.`bairro` LIKE '%$_POST[txt_bairro]%' 
            AND c.`cidade` LIKE '%$_POST[txt_cidade]%' 
            AND c.`id_uf` LIKE '$_POST[cmb_uf]' 
            $condicao_tipos_clientes 
            GROUP BY c.id_cliente ORDER BY c.razaosocial ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'emails.php?valor=1'
        </Script>
<?
        exit;
    }
}
?>
<html>
<head>
<title>.:: Consultar Cliente(s) p/ Gerar Lista de Email(s) ::.</title>
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
    var selecionados = 0, id_cliente_e_contato = ''
    var achou_combo = 0
    for (i = 0; i < elementos.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a segunda combo
            if(achou_combo == 2) {
                for(j = 1; j < document.form.elements[i].length; j++) {
                    if(document.form.elements[i][j].selected == true) {
                        selecionados ++
                        id_cliente_e_contato = id_cliente_e_contato + document.form.elements[i][j].value + ',';
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

    document.form.id_cliente_e_contato2.value   = id_cliente_e_contato.substr(0, id_cliente_e_contato.length - 1);
    document.form.txt_razao_social.value        = '<?=$_POST['txt_razao_social'];?>'
    document.form.txt_nome_fantasia.value       = '<?=$_POST['txt_nome_fantasia'];?>'
    document.form.txt_bairro.value              = '<?=$_POST['txt_bairro'];?>'
    document.form.txt_cidade.value              = '<?=$_POST['txt_cidade'];?>'
    document.form.txt_uf.value                  = '<?=$_POST['cmb_uf'];?>'
    document.form.txt_representante.value       = '<?=$_POST['cmb_representante'];?>'
    document.form.txt_novo_tipo_cliente.value   = '<?=$novo_tipo_cliente_string;?>'
    document.form.txt_tipo_filtro.value         = '<?=$_POST['cmb_tipo_filtro'];?>'
    document.form.action                        = 'emails.php'
    document.form.target                        = '_self'
    document.form.passo.value                   = 1
    document.form.exibir.value                  = 1
    document.form.submit()
}

function selecionar_todos() {
    var linhas = eval('<?=$linhas;?>')
/*Se a Qtde de Registros for maior do que 5.000, eu mando o usuário selecionar de forma manualmente p/ não 
travar o Navegador com o Script de Seleção da Combo em JavaScript ...*/
    if(linhas > 5000) {
        alert('SELECIONE O(S) CLIENTE(S) ATRAVÉS DO MOUSE E DA TECLA SHIFT AO MESMO TEMPO !')
        return false
    }else {
        var elementos = document.form.elements
        var achou_combo = 0
        for (var i = 0; i < elementos.length; i++) {
            if(document.form.elements[i].type == 'select-multiple') {
                achou_combo++
//Aqui tem esse macete porque é para controlar a segunda combo
                if(achou_combo == 2) {
                    for(var j = 1; j < document.form.elements[i].length; j++) document.form.elements[i][j].selected = true
                    i = elementos.length
                }
            }
        }
    }

}
/*************************************************************************/
/*Funções referentes a terceira tela depois da consulta - Exibir = 1*/
function retirar_cliente() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
    var flag = 0, cliente_e_contato_sel = ''
    var achou_combo = 0
    for(i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a terceira combo
            if(achou_combo == 3) {
                if(document.form.elements[i].value == '') {
                    if(flag == 0) alert('SELECIONE PELO MENOS UM CLIENTE !')
                    document.form.elements[i].focus()
                    return false
                }else {
                    for(j = 0; j < document.form.elements[i].length; j ++) {
                        if(document.form.elements[i][j].selected == true) cliente_e_contato_sel = cliente_e_contato_sel + document.form.elements[i][j].value + ','
                    }
                }
                flag++
            }
        }
    }
    cliente_e_contato_sel                       = cliente_e_contato_sel.substr(0, cliente_e_contato_sel.length - 1)
    document.form.id_cliente_e_contato2.value   = cliente_e_contato_sel
    document.form.txt_razao_social.value        = '<?=$_POST['txt_razao_social'];?>'
    document.form.txt_nome_fantasia.value       = '<?=$_POST['txt_nome_fantasia'];?>'
    document.form.txt_bairro.value              = '<?=$_POST['txt_bairro'];?>'
    document.form.txt_cidade.value              = '<?=$_POST['txt_cidade'];?>'
    document.form.txt_uf.value                  = '<?=$_POST['cmb_uf'];?>'
    document.form.txt_representante.value       = '<?=$_POST['cmb_representante'];?>'
    document.form.txt_novo_tipo_cliente.value   = '<?=$novo_tipo_cliente_string;?>'
    document.form.txt_tipo_filtro.value         = '<?=$_POST['cmb_tipo_filtro'];?>'
    document.form.acao.value                    = 1
    document.form.exibir.value                  = 1
    document.form.passo.value                   = 1
    document.form.action                        = 'emails.php'
    document.form.target                        = '_self'
    document.form.submit()
}

function selecionar_todos_clientes_contatos() {
    var elementos = document.form.elements
    var selecionados = ''
    var achou_combo = 0
    for (var i = 0; i < elementos.length; i ++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a terceira combo
            if(achou_combo == 3) {
                for(var j = 1; j < document.form.elements[i].length; j++) document.form.elements[i][j].selected = true
            }
        }
    }
}

function gerar_lista() {
//Aqui eu verifico todos os elementos que estão selecionados na combo múltipla
    var flag = 0, cliente_e_contato_sel = ''
    var achou_combo = 0, selecionados = 0
    var linhas = eval('<?=$linhas;?>')
/*Se a Qtde de Registros for menor do que 5.000, então o Sistema seleciona a Combo de Forma automática no 
JavaScript pois o Navegador não trava com o da Combo via JavaScript ...*/
    if(linhas < 5000) selecionar_todos_clientes_contatos()
    
    for(var i = 0; i < document.form.elements.length; i++) {
        if(document.form.elements[i].type == 'select-multiple') {
            achou_combo++
//Aqui tem esse macete porque é para controlar a terceira combo
            if(achou_combo == 3) {
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
    document.form.action = 'lista_emails.php'
    document.form.target = 'novajanela'
    nova_janela('lista_emails.php', 'novajanela', '', '', '', '', 450, 700, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_razao_social.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='passo'>
<input type='hidden' name='exibir'>
<input type='hidden' name='txt_uf'>
<input type='hidden' name='txt_representante'>
<input type='hidden' name='txt_novo_tipo_cliente'>
<input type='hidden' name='txt_tipo_filtro'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Cliente(s) p/ Gerar Lista de Email(s) - Somente clientes que possuem e-mail
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
                $sql = "SELECT id_cliente_tipo, tipo 
                        FROM `clientes_tipos` 
                        ORDER BY tipo ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Tipo de Filtro
        </td>
        <td>
            <select name='cmb_tipo_filtro' title='Selecione um Tipo de Filtro' class='combo'>
                <option value='' style='color:red' selected>SELECIONE</option>
                <option value='1'>SOMENTE CLIENTE(S)</option>
                <option value='2'>SOMENTE CONTATO(S)</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Departamento
        </td>
        <td>
            <select name="cmb_departamento" title="Selecione o Departamento" class='combo'>
            <?
                $sql = "SELECT id_departamento, departamento 
                        FROM `departamentos` 
                        WHERE `ativo` = '1' ORDER BY departamento ";
                echo combos::combo($sql, 'COMPRAS');
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
//Significa que eu quero exibir somente os Clientes ...
                if($_POST['cmb_tipo_filtro'] == 1) {
                    for($i = 0; $i < $linhas; $i++) {
?>
                    <option value="<?=$campos[$i]['id_cliente'];?>"><?=$campos[$i]['razaosocial'];?></option>
<?
                    }
//Significa que eu quero exibir somente os Contatos ...
                }else if($_POST['cmb_tipo_filtro'] == 2) {
                    for($i = 0; $i < $linhas; $i++) $id_clientes_loop.= $campos[$i]['id_cliente'].', ';
                    $id_clientes_loop = substr($id_clientes_loop, 0, strlen($id_clientes_loop) - 2);
//Se tiver algum Departamento de Contato selecionado então, trará somente contatos do Depto. selecionado ...
                    if(!empty($_POST['cmb_departamento'])) $condicao_depto = ' AND cc.`id_departamento` = '.$_POST['cmb_departamento'];
//Listagem de Todos os Contatos desses Clientes que foram selecionados
                    $sql = "SELECT cc.id_cliente_contato, CONCAT(cc.nome, ' (', c.razaosocial, ') ') AS dados 
                            FROM `clientes_contatos` cc 
                            INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                            WHERE cc.`id_cliente` IN ($id_clientes_loop) 
                            AND cc.`email` <> '' 
                            AND cc.`ativo` = '1' $condicao_depto ORDER BY nome ";
                    $campos_contatos = bancos::sql($sql);
                    $linhas_contatos = count($campos_contatos);
                    for($j = 0; $j < $linhas_contatos; $j++) {
?>
                    <option value="<?='C'.$campos_contatos[$j]['id_cliente_contato'];?>"><?=' * '.$campos_contatos[$j]['dados'];?></option>
<?
                    }
//Significa que eu quero exibir somente os Clientes e Contatos ...
                }else {
                    for($i = 0; $i < $linhas; $i++) {
?>
                    <option value="<?=$campos[$i]['id_cliente'];?>"><?=$campos[$i]['razaosocial'];?></option>
<?
                        $id_clientes_loop.= $campos[$i]['id_cliente'].', ';
                    }
                    $id_clientes_loop = substr($id_clientes_loop, 0, strlen($id_clientes_loop) - 2);
//Se tiver algum Departamento de Contato selecionado então, trará somente contatos do Depto. selecionado ...
                    if(!empty($_POST['cmb_departamento'])) $condicao_depto = ' AND cc.`id_departamento` = '.$_POST['cmb_departamento'];
//Listagem de Todos os Contatos desses Clientes que foram selecionados
                    $sql = "SELECT cc.id_cliente_contato, CONCAT(cc.nome, ' (', c.razaosocial, ') ') AS dados 
                            FROM `clientes_contatos` cc 
                            INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                            WHERE cc.`id_cliente` IN ($id_clientes_loop) 
                            AND cc.`email` <> '' 
                            AND cc.`ativo` = '1' $condicao_depto ORDER BY nome ";
                    $campos_contatos = bancos::sql($sql);
                    $linhas_contatos = count($campos_contatos);
                    for($j = 0; $j < $linhas_contatos; $j++) {
?>
                    <option value="<?='C'.$campos_contatos[$j]['id_cliente_contato'];?>"><?=' * '.$campos_contatos[$j]['dados'];?></option>
<?
                    }
                }
/***************************************************************************************************/
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
            <input type='button' name='cmd_selecionar' value='Selecionar Todos' title='Selecionar Todos' onclick='selecionar_todos()' class='botao'>
            <input type='button' name='cmd_adicionar' value='Adicionar Item(ns) Selecionado(s)' title='Adicionar Item(ns) Selecionado(s)' onclick='enviar()' class='botao'>
<?
        }
?>
        </td>
    </tr>
</table>
<?
/*Nessa parte é simplesmente para mostrar a segunda combo com os clientes ou contatos
selecionados da primeira combo*/
    if(!empty($clientes_e_contatos)) {
//Transformo em vetor, para fazer a separação do que é Cliente com o que é Contato
            $vetor_clientes_e_contatos = explode(',', $clientes_e_contatos);//Transforma em Vetor
//Disparo do Vetor
            for($i = 0; $i < count($vetor_clientes_e_contatos); $i++) {
                    if(substr($vetor_clientes_e_contatos[$i], 0, 1) == 'C') {//Significa que é Contato
                            $contatos_selecionados.= substr($vetor_clientes_e_contatos[$i], 1, strlen($vetor_clientes_e_contatos[$i])).', ';
                    }else {
                            $clientes_selecionados.= $vetor_clientes_e_contatos[$i].', ';
                    }
            }
?>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td>
            Cliente(s) e Contato(s) Selecionado(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <select name='cmb_cliente_e_contato_selecionado[]' class='combo' size='5' multiple>
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
//Cliente(s) Selecionado(s)
            if(strlen($clientes_selecionados) > 0) {
                $clientes_selecionados = substr($clientes_selecionados, 0, strlen($clientes_selecionados) - 2);
//Listagem de Cliente(s) Selecionado(s)
                $sql = "SELECT id_cliente, razaosocial 
                        FROM `clientes` 
                        WHERE `id_cliente` IN ($clientes_selecionados) ORDER BY razaosocial ";
                $campos = bancos::sql($sql);
                $linhas = count($campos);
                for($i = 0; $i < $linhas; $i++) {
        ?>
                    <option value='<?=$campos[$i]['id_cliente']?>'><?=$campos[$i]['razaosocial']?></option>
        <?
                }
            }
//Contato(s) Selecionado(s)
            if(strlen($contatos_selecionados) > 0) {
                $contatos_selecionados = substr($contatos_selecionados, 0, strlen($contatos_selecionados) - 2);
//Listagem de Contato(s) Selecionado(s)
//Listagem de Todos os Contatos desses Clientes que foram selecionados
                $sql = "SELECT cc.id_cliente_contato, CONCAT(cc.nome, ' (', c.razaosocial, ') ') AS dados 
                        FROM `clientes_contatos` cc 
                        INNER JOIN `clientes` c ON c.`id_cliente` = cc.`id_cliente` 
                        WHERE cc.`id_cliente_contato` IN ($contatos_selecionados) ORDER BY nome ";
                $campos_contatos = bancos::sql($sql);
                $linhas_contatos = count($campos_contatos);
                for($j = 0; $j < $linhas_contatos; $j++) {

    ?>
                    <option value="<?='C'.$campos_contatos[$j]['id_cliente_contato'];?>"><?=' * '.$campos_contatos[$j]['dados'];?></option>
    <?
                }
            }
        ?>
            </select>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='button' name='cmd_retirar2' value='Retirar' title='Retirar' onclick='retirar_cliente()' class='botao'>
            <input type='button' name='cmd_gerar_lista' value='Gerar Lista' title='Gerar Lista' onclick='gerar_lista()' class='botao'>
        </td>
    </tr>
</table>
<?
    }
?>
<input type='hidden' name='id_cliente_e_contato' value='<?=$clientes_e_contatos;?>'>
<input type='hidden' name='id_cliente_e_contato2'>
<input type='hidden' name='acao'>
</form>
</body>
<Script Language = 'JavaScript'>
function validar() {
    document.form.action = 'emails.php'
    document.form.target = '_self'
    document.form.exibir.value = 0
    document.form.passo.value = 1
}
</Script>
</html>