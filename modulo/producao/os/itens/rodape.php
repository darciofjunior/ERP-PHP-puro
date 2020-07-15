<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/producao/os/itens/consultar.php', '../../../../');

//Verifico se tem pelo menos um item de os, para poder habilitar os botões alterar e excluir
$sql = "SELECT `id_os_item` 
        FROM `oss_itens` 
        WHERE `id_os` = '$id_os' LIMIT 1 ";
$campos = bancos::sql($sql);
$linhas = count($campos);

/**********************************************************************************************/
//Esse controle eu vou utilizar um pouco mais abaixo para controle dos Botões do Rodapé
//Se esta OS já estiver importada para Pedido, então eu travo os Botões do Rodapé
$sql = "SELECT `id_pedido`, `id_nf_outra` 
        FROM `oss` 
        WHERE `id_os` = '$id_os' LIMIT 1 ";
$campos_oss     = bancos::sql($sql);
$id_pedido      = $campos_oss[0]['id_pedido'];
$id_nf_outra 	= $campos_oss[0]['id_nf_outra'];
/**********************************************************************************************/

$disabled_saida     = '';
$class_saida        = 'botao';
$disabled_entrada   = '';
$class_entrada      = 'botao';

//Aqui eu verifico se a OSS já foi importada em pedido, se sim desabilito o botão de alterar Entrada ...
$sql = "SELECT `id_os` 
        FROM `oss` 
        WHERE `id_os` = '$id_os' 
        AND id_pedido > '0' ";
$campos_travar  = bancos::sql($sql);
$linhas_travar  = count($campos_travar);
if($linhas_travar > 0) {
    $disabled_saida = 'disabled';
    $class_saida    = 'textdisabled';
}
?>
<html>
<head>
<title>.:: Rodapé de Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function selecionar(valor) {
    var option  = 0
    if(typeof(parent.itens.document.form) == 'undefined') {
        return false
    }else {
        var elementos = parent.itens.document.form
    }
    if(elementos.checked == true && elementos.type == 'radio') {
        return true
    }
    if(elementos.checked == false) {
        alert('SELECIONE UM ITEM DE SAÍDA !')
        return false
    }
    for(var i = 0; i < elementos.length; i ++) {
        if(elementos[i].checked == true && elementos[i].type == 'radio') option ++
    }
//Outra Opções - É um botão para o Controle do Orçamento
/*Na realidade foi feito uma adaptação dos "Outra Opções" nesse Script de selecionar, fiz isso por causa da última opção
que é o registro do Follow_up para produto acabado e fazendo essa adaptação eu consigo garantir o id_os_item*/
    if(valor == 4) {
        if(option == 0) {
            nova_janela('outras_opcoes.php?id_os=<?=$id_os;?>', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
        }else {
            for(var i = 0; i < elementos.length; i++) {
                if(elementos[i].checked == true && elementos[i].type == 'radio') {
                    var id_os_item  = elementos[i].value
                    var posicao     = (i + 1)
                    break;
                }
            }
            nova_janela('outras_opcoes.php?id_os=<?=$id_os;?>', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
        }
//Botões para controle dos itens de Pedido
    }else {
        if(option == 0) {
            alert('SELECIONE UM ITEM DE SAÍDA !')
            return false
        }else {
            for(var i = 0; i < elementos.length; i++) {
                if(elementos[i].checked == true && elementos[i].type == 'radio') {
                    var id_os_item  = elementos[i].value
                    var posicao     = (i + 1)
                    break;
                }
            }
            if(valor == 1) {//Alterar ...
                nova_janela('alterar_saida.php?id_os=<?=$id_os;?>&posicao='+posicao, 'POP', '', '', '', '', 550, 850, 'c', 'c', '', '', 's', 's', '', '', '')
            }else if(valor == 2) {//Incluir Entrada ...
                nova_janela('incluir_entrada.php?id_os=<?=$id_os;?>&posicao='+posicao, 'POP', '', '', '', '', 550, 850, 'c', 'c', '', '', 's', 's', '', '', '')
            }else if(valor == 3) {//Excluir ...
                var resposta = confirm('TEM CERTEZA DE QUE DESEJA EXCLUIR ESSE ITEM (SAÍDA DE OS) ?')
                if(resposta == true) parent.itens.document.location = 'itens.php?passo=1&id_os=<?=$id_os;?>&id_os_item='+id_os_item
            }
        }
    }
}

function clique_automatico_cabecalho() {
    var clique_automatico_cabecalho = '<?=$clique_automatico_cabecalho;?>'
    if(clique_automatico_cabecalho == 1) document.form.cmd_cabecalho.onclick()
}
</Script>
</head>
<?
//Vai entrar aqui somente na primeira em que carregar a tela
if(empty($parametro_velho)) {
//Controle para o botão
    $parametro_velho = $parametro;
//Controle para o hidden
    $parametro_velho2 = $parametro;
}else {
/*Controle para o hidden, aqui tem q ter a urlencode, para não dar erro após q submeter, só q se tiver
isso diretamente no botão já da erro*/
    //$parametro_velho2 = urlencode($parametro_velho);
    $parametro_velho2 = $parametro_velho;
}
?>
<body onload="clique_automatico_cabecalho()">
<form name='form'>
<input type='hidden' name='parametro_velho' value="<?=$parametro_velho2;?>">
<table width='90%' border='0' cellspacing='1' cellpadding='1' align="center">
    <td align='center'>
        <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="parent.location = 'consultar.php<?=$parametro_velho;?>'" class='botao'>
        <input type='button' name='cmd_cabecalho' value='Cabe&ccedil;alho / Observa&ccedil;&atilde;o' title='Cabe&ccedil;alho / Observa&ccedil;&atilde;o' onclick="nova_janela('../alterar_cabecalho.php?id_os=<?=$id_os;?>', 'POP', '', '', '', '', 550, 850, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
/****************************Controle para Travamento dos Botões****************************/
	if($id_pedido > 0) {//Se a OS estiver Importada em Pedido então não posso manipular os Itens da mesma ...
            $controle_botao = "class='disabled' onclick='JavaScript:alert(".'"ESTA O.S. JÁ FOI IMPORTADA PARA PEDIDO !"'.")'";
	}else if($id_nf_outra > 0) {//Se a OS estiver Importada em NF então não posso manipular os Itens da mesma ...
            $controle_botao = "class='disabled' onclick='JavaScript:alert(".'"ESTA O.S. JÁ FOI IMPORTADA PARA NF DE SAÍDA !"'.")'";
	}else {//Aqui posso manipular de forma normal ...
            $controle_botao = "class='botao' ";
	}
/*******************************************************************************************/
?>
        <input type='button' name='cmd_incluir' value='Incluir Itens / Saída' title='Incluir Itens / Saída' <?=$controle_botao;?> onclick="nova_janela('incluir.php?id_os=<?=$id_os;?>', 'POP', '', '', '', '', 550, 1000, 'c', 'c', '', '', 's', 's', '', '', '')">
<?
	if($linhas > 0) {
?>
        <input type='button' name='cmd_alterar_saida' value='Alterar Saída' title='Alterar Saída' onclick='selecionar(1)' class='<?=$class_saida;?>' <?=$disabled_saida;?>>
        <input type='button' name='cmd_incluir_entrada' value='Incluir Entrada(s)' title='Incluir Entrada(s)' onclick='selecionar(2)' class='botao'>
        <input type='button' name='cmd_excluir' value='Excluir Itens' title='Excluir Itens' <?=$controle_botao;?> onclick='selecionar(3)'>
        <input type='button' name='cmd_outras' value='Outras Opções' title='Outras Opções' onclick='selecionar(4)' class='botao'>
<?
	}
?>
    </td>
</table>
<input type='hidden' name='id_os' value='<?=$id_os;?>'>
</form>
</body>
</html>