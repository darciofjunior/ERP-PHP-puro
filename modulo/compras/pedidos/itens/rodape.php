<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/consultar.php', '../../../../');

//Verifica a situação do Pedido de Compras, para poder travar os botões
$sql = "SELECT status 
        FROM `pedidos` 
        WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
$campos = bancos::sql($sql);
$status = $campos[0]['status'];

//Verifica se existe pelo menos 1 item de Pedido de Compra(s) para fazer a exibição dos botões de rodapé
$sql = "SELECT id_item_pedido 
        FROM `itens_pedidos` 
        WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
$campos = bancos::sql($sql);
$linhas = count($campos);

/**********************************************************************************************/
//Esse controle eu vou utilizar um pouco mais abaixo para controle dos Botões do Rodapé
//Se este Pedido estiver atrelado a uma OS, então eu travo os Botões do Rodapé
$sql = "SELECT id_os 
        FROM `oss` 
        WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
$campos_os = bancos::sql($sql);
if(count($campos_os) == 1) {//Está importado p/ OS
    $tem_os_importada = 1;
}else {//Ainda não está importado p/ OS
    $tem_os_importada = 0;
}
/**********************************************************************************************/
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
function alterar_item() {
    var option  = 0
    if(typeof(parent.itens.document.form) == 'undefined') {
        return false
    }else {
        elemento = parent.itens.document.form
    }
    if(elemento.checked == true && elemento.type == 'radio') return true
    if(elemento.checked == false) {
        alert('SELECIONE UM ITEM !')
        return false
    }
    for(var i = 0; i < elemento.length; i++) {
        if(elemento[i].checked == true && elemento[i].type == 'radio') option ++
    }
    if(option == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        for(var i = 0; i < elemento.length; i++) {
            if (elemento[i].checked == true && elemento[i].type == 'radio') {
                var posicao = (i + 1)
                break;
            }
        }
        nova_janela('alterar.php?id_pedido=<?=$id_pedido;?>&posicao='+posicao, 'POP', '', '', '', '', 450, 980, 'c', 'c')
    }
}

function imprimir() {
    nova_janela('imprimir.php?id_pedido=<?=$id_pedido;?>', 'CONSULTAR', 'F')
}

function gerar_nfe_entrada() {
    var resposta = confirm('TEM CERTEZA DE QUE DESEJA GERAR UMA NFE DE ENTRADA ?')
    if(resposta == true) nova_janela('gerar_nfe_entrada.php?id_pedido=<?=$id_pedido;?>', 'POP', '', '', '', '', 450, 980, 'c', 'c')
}

function clique_automatico_cabecalho() {
    var clique_automatico_cabecalho = '<?=$clique_automatico_cabecalho;?>'
    if(clique_automatico_cabecalho == 1) {
        document.form.cmd_cabecalho.onclick()
    }
}
</Script>
</head>
<?
//Apenas na 1ª vez que esse parâmetro será vazio ...
if(!empty($parametro_velho)) $parametro = $parametro_velho;
?>
<body onload='clique_automatico_cabecalho()'>
<form name='form'>
<input type='hidden' name='parametro_velho' value='<?=$parametro;?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align="center">
    <td align='center'>
    <?
        //Se existir esse parâmetro, então não volto p/ o Filtro que corresponde ao Alterar / Imprimir ...
        $url = (!empty($_GET['pop_up'])) ? '../consultar.php' : 'consultar.php';//Tela de Consultar senão Tela de Alterar / Imprimir ...
    ?>
        <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="parent.location = '<?=$url.$parametro;?>'" class='botao'>
<?
//Se essa tela foi aberta como sendo Pop-UP, não exibo esses botões ...
if(empty($_GET['pop_up'])) {
?>
        <input type='button' name='cmd_cabecalho' value='Cabe&ccedil;alho' title='Cabe&ccedil;alho' onclick="nova_janela('../alterar_cabecalho.php?id_pedido=<?=$id_pedido;?>', 'CABECALHO', '', '', '', '', 580, 900, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
/****************************Controle para Travamento dos Botões****************************/
//Se o Pedido tiver uma OS Importada, de jeito maneira que eu posso manipular os Itens do Pedido
    if($tem_os_importada == 1) {
        $controle_botao     = "class='disabled' onclick='alert(".'"ESTE PEDIDO POSSUI UMA O.S. IMPORTADA !"'.")' ";
    }else {
        if($status == 1 || $status == 0) {//Pedido em Aberto ou Parcial, posso incluir + itens
            $controle_botao = "class='botao' ";
        }else {//Significa que esse Pedido está concluído ...
            $controle_botao = "class='disabled' onclick='alert(".'"PEDIDO CONCLUÍDO !"'.")'";
        }
    }
/*******************************************************************************************/
?>
        <input type='button' name='cmd_incluir_item' value='Incluir Item' title='Incluir Item' <?=$controle_botao;?> onclick="nova_janela('frame_incluir_itens.php?id_pedido=<?=$id_pedido;?>', 'POP', '', '', '', '', 450, 980, 'c', 'c')">
<?
    if($linhas > 0) {
?>
        <input type='button' name='cmd_alterar_item' value='Alterar Item' title='Alterar Item' <?=$controle_botao;?> onclick='alterar_item()'>
        <input type='button' name='cmd_excluir_item' value='Excluir Item(ns)' title='Excluir Item(ns)' onclick="nova_janela('excluir_itens.php?id_pedido=<?=$id_pedido;?>', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
    }
?>
        <input type='button' name='cmd_outras_opcoes' value='Outras Opções' title='Outras Opções' onclick="nova_janela('outras_opcoes.php?id_pedido=<?=$id_pedido;?>', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
    if($linhas > 0) {
//Aqui eu verifico se existe pelo menos 1 parcelamento de Vencimento feito p/ este pedido ... 
        $sql = "SELECT id_pedido_financiamento 
                FROM `pedidos_financiamentos` 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos_financiamento = bancos::sql($sql);
        if(count($campos_financiamento) == 0) {
            $controle_imprimir = "class='disabled' onclick='alert(".'"PRECISA GERAR AS PARCELAS DE FINANCIAMENTO PRIMEIRO ANTES DE IMPRIMIR !"'.")' ";
        }else {
            $controle_imprimir = "class='botao' onclick='imprimir()' ";
        }
?>
        <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' <?=$controle_imprimir;?>>
        <input type='button' name='cmd_gerar_nfe_entrada' value='Gerar NFe de Entrada' title='Gerar NFe de Entrada' <?=$controle_botao;?> onclick="gerar_nfe_entrada()" style='color:red'>
<?
    }
}
?>
    </td>
</table>
<input type='hidden' name='id_pedido' value='<?=$id_pedido;?>'>
</form>
</body>
</html>