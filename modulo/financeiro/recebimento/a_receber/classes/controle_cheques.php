<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
session_start('funcionarios');
if($id_emp == 1) {
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) CHEQUE(S) PENDENTE(S) PARA ESTE CLIENTE.</font>";
$mensagem[2] = "<font class='confirmacao'>CHEQUE(S) EXCLUÍDO(S) COM SUCESSO.</font>";

//Aqui significa que teve um cheque que foi excluído
if(!empty($id_cheque_cliente)) {
    $sql = "DELETE FROM `cheques_clientes` WHERE `id_cheque_cliente` = '$id_cheque_cliente' LIMIT 1 ";
    bancos::sql($sql);
//Aqui eu transformo em vetor o string de cheques_selecionados, para poder remover
    $vetor_cheques_clientes = explode(',', $id_cheques_clientes);
//Removo o elemento do array o elemento que acabou de ser excluído do vetor
    array_pop($vetor_cheques_clientes);
//Aki eu volto o array de volta em string
    $id_cheques_clientes = implode(',', $vetor_cheques_clientes);
    $valor = 2;
}

//Aqui traz todos os cheques que estão em abertos, específicos daquele cliente e daquela empresa
$sql = "Select distinct(cc.num_cheque), cc.*, c.razaosocial 
	from clientes c, cheques_clientes cc 
	where cc.id_empresa = '$id_emp' 
	and cc.status_disponivel = 1 
	and cc.ativo = 1 
	and cc.id_cliente = c.id_cliente 
	and c.id_cliente = '$id_cliente' order by cc.data_vencimento asc ";

$sql_extra = "Select count(distinct(cc.num_cheque)) as total_registro 
		from clientes c, cheques_clientes cc 
		where cc.id_empresa = '$id_emp' 
		and cc.status_disponivel = 1 
		and cc.ativo = 1 
		and cc.id_cliente = c.id_cliente 
		and c.id_cliente = '$id_cliente' order by cc.data_vencimento ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
<html>
<head>
<title>.:: Controle de Cheque(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function incluir_novo_cheque() {
//Esse parâmetro controle_recebimento é para saber que esse arquivo está sendo puxado da tela de Recebimento de Contas
    window.location = '../../cheque_cliente/classes/manipular/incluir_cheques.php?passo=2&id_cliente=<?=$id_cliente;?>&id_emp2=<?=$id_emp;?>&pop_up=1'
}
</Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_incluir_novo_cheque' value='Incluir Novo Cheque' title='Incluir Novo Cheque' onclick='incluir_novo_cheque()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else {
//Transforma o String passado por parâmetro em vetor
    $vetor_cheques_clientes = explode(',', $id_cheques_clientes);
?>
<html>
<head>
<title>.:: Controle de Cheque(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<!--JS específico desse arquivo-->
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos           = document.form.elements
    var total_em_cheques    = 0//Total em Cheque
    var id_cheques_clientes = ''//Armazena os Cheques Clientes

    var mensagem = '', valor = false
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        if(typeof(elementos['chkt_cheque_cliente[]'][0]) == 'undefined') {
            var linhas = 1//Existe apenas 1 único elemento ...
        }else {
            var linhas = (elementos['chkt_cheque_cliente[]'].length)
        }
        for(i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_cheque_cliente'+i).checked == true) {
                total_em_cheques+= eval(document.getElementById('txt_valor_disponivel'+i).value)
                id_cheques_clientes+= document.getElementById('chkt_cheque_cliente'+i).value + ', '
            }
        }
        id_cheques_clientes = id_cheques_clientes.substr(0, id_cheques_clientes.length - 2)

        opener.document.form.txt_total_em_cheques.value = total_em_cheques
        opener.document.form.txt_total_em_cheques.value = arred(opener.document.form.txt_total_em_cheques.value, 2, 1)
        opener.document.form.txt_total_em_cheques.value = number_format(opener.document.form.txt_total_em_cheques.value)
        opener.document.form.hdd_cheques_clientes.value = id_cheques_clientes
//Aqui chama a função para debater o valor cheque nas contas à receber
        opener.debater_valor_cheque()
        window.close()
    }
}

function incluir_novo_cheque() {
//Esse parâmetro controle_recebimento é para saber que esse arquivo está sendo puxado da tela de Recebimento de Contas
//Objetivo evitar de trabalhar com vários parâmetros
    window.location = '../../cheque_cliente/classes/manipular/incluir_cheques.php?passo=2&id_cliente=<?=$id_cliente;?>&id_emp2=<?=$id_emp;?>&controle_recebimento=1'
}

function alterar_cheque(id_cheque_cliente) {
//Limpa a caixa de Total em Cheques na tela de baixo, para não dar erro de cálculo e limpa os ids também
    opener.document.form.txt_total_em_cheques.value = ''
    opener.document.form.hdd_cheques_clientes.value = ''
//Esse parâmetro controle_recebimento é para saber que esse arquivo está sendo puxado da tela de Recebimento de Contas
//Objetivo evitar de trabalhar com vários parâmetros
    window.location = '../../cheque_cliente/classes/manipular/alterar_cheques.php?passo=2&id_cheque_cliente='+id_cheque_cliente+'&id_emp2=<?=$id_emp;?>&controle_recebimento=1'
}

function excluir_cheque(id_cheque_cliente) {
    var mensagem = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE CHEQUE ?')
    if(mensagem == true) {
//Limpa a caixa de Total em Cheques na tela de baixo, para não dar erro de cálculo e limpa os ids também
        opener.document.form.txt_total_em_cheques.value = ''
        opener.document.form.hdd_cheques_clientes.value = ''
//Aqui eu devolvo * o Cliente da Tela abaixo de recebimento, * os cheques_selecionados anteriormente, * passo o id_cheque_cliente que eu desejo excluir
        window.location = 'controle_cheques.php?id_cliente=<?=$id_cliente;?>&id_cheques_clientes=<?=$id_cheques_clientes;?>&id_cheque_cliente='+id_cheque_cliente
    }else {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='10'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Controle de Cheque(s) <?=genericas::nome_empresa($id_emp);?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type = 'checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            N.º Cheque
        </td>
        <td>
            Banco
        </td>
        <td>
            Cliente
        </td>
        <td>
            Correntista
        </td>
        <td>
            Valor do Cheque
        </td>
        <td>
            Valor Disponível
        </td>
        <td>
            Data de Venc.
        </td>
        <td>
            <img src = '../../../../../imagem/menu/alterar.png' border='0' title='Alterar' alt='Alterar'>
        </td>
        <td>
            <img src = '../../../../../imagem/menu/excluir.png' border='0' title='Excluir' alt='Excluir'>
        </td>
    </tr>
<?
	for($i = 0; $i < $linhas; $i++) {
//Verifica se o Cheque Corrente do Cliente que está sendo listado é igual aos selecionados anteriormente
            $checked = (in_array($campos[$i]['id_cheque_cliente'], $vetor_cheques_clientes)) ? 'checked' : '';
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_cheque_cliente[]' id='chkt_cheque_cliente<?=$i;?>' value="<?=$campos[$i]['id_cheque_cliente'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox' <?=$checked;?>>
        </td>
        <td>
            <?=$campos[$i]['num_cheque'];?>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['correntista'];?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?='R$ '.number_format($campos[$i]['valor_disponivel'], 2, ',', '.');?>
            <!--Caixa que serve de controle para guardar os valores disponiveis das contas-->
            <input type='hidden' name='txt_valor_disponivel[]' id='txt_valor_disponivel<?=$i;?>' value='<?=$campos[$i]['valor_disponivel'];?>'>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento'], '/');?>
        </td>
        <td>
        <?
//Se o cheque ainda não foi utilizado para pagar ou receber nenhuma conta, então eu posso estar fazendo alterações
            if($campos[$i]['valor'] == $campos[$i]['valor_disponivel']) {
        ?>
            <img src = '../../../../../imagem/menu/alterar.png' border='0' title='Alterar Cheque' alt='Alterar Cheque' onclick="alterar_cheque('<?=$campos[$i]['id_cheque_cliente'];?>')" style='cursor:pointer'>
        <?
            }
        ?>
        </td>
        <td>
        <?
//Se o cheque ainda não foi utilizado para pagar ou receber nenhuma conta, então eu posso estar excluindo esse cheque
            if($campos[$i]['valor'] == $campos[$i]['valor_disponivel']) {
        ?>
            <img src = '../../../../../imagem/menu/excluir.png' border='0' title='Excluir Cheque' alt='Excluir Cheque' onclick="excluir_cheque('<?=$campos[$i]['id_cheque_cliente'];?>')" style='cursor:pointer'>
        <?
            }
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='submit' name='cmd_atrelar_conta' value='Atrelar à Conta' title='Atrelar à Conta' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
            <input type='button' name='cmd_incluir_novo_cheque' value='Incluir Novo Cheque' title='Incluir Novo Cheque' onclick='incluir_novo_cheque()' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>