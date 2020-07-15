<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/compras_new.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

//Controle p/ os botões do Rodapé
$sql = "SELECT `id_empresa`, `id_fornecedor`, `tipo`, `situacao` 
        FROM `nfe` 
        WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
$campos         = bancos::sql($sql);
$id_empresa_nf  = $campos[0]['id_empresa'];
$id_fornecedor  = $campos[0]['id_fornecedor'];
$tipo           = $campos[0]['tipo'];//Serve somente p/ controlar o Botão de Impressão ...

//Verifico se essa Nota Fiscal possui pelo menos 1 item ...
$sql = "SELECT `id_nfe_historico` 
        FROM `nfe_historicos` 
        WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
$campos_itens = bancos::sql($sql);
$linhas_itens   = count($campos_itens);
if($linhas_itens == 0) {
    $situacao = 0;//Serve para controlar os outros Botões ...
}else {
    $situacao = $campos[0]['situacao'];//Serve para controlar os outros Botões ...

    //Verifico se ao menos 1 item do que foi importado em Nota Fiscal está relacionado com OS ...
    $sql = "SELECT `id_os_item` 
            FROM `oss_itens` 
            WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
    $campos_os_itens    = bancos::sql($sql);
    $nf_com_itens_de_os = (count($campos_os_itens) == 1) ? 1 : 0;
}

/*Verifico se existe OS(s) em aberto que estão importadas em Pedido de Compras apenas 
e do mesmo Fornecedor que está em Nota Fiscal, e do mesmo tipo de Empresa do Cabeçalho da Nota Fiscal*/
$sql = "SELECT oi.`id_os_item` 
        FROM `oss_itens` oi 
        INNER JOIN `oss` ON oss.`id_os` = oi.`id_os` AND oss.`id_fornecedor` = '$id_fornecedor' AND oss.`id_pedido` <> '0' 
        INNER JOIN `pedidos` p ON oss.`id_pedido` = p.`id_pedido` AND p.`id_empresa` = '$id_empresa_nf' 
        INNER JOIN `ops` ON oi.`id_op` = ops.`id_op` 
        WHERE oi.`id_nfe` = '$id_nfe' 
        AND oi.`qtde_entrada` > '0' 
        AND oi.`status` < '2' LIMIT 1 ";
$campos_os = bancos::sql($sql);
$linhas_os = count($campos_os);
?>
<html>
<head>
<title>.:: Rodap&eacute; de Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'Javascript'>
function incluir_item(clique_automatico_incluir_itens) {
    var linhas_os           = eval('<?=$linhas_os;?>')
    var nf_com_itens_de_os  = eval('<?=$nf_com_itens_de_os;?>')
    
    if(linhas_os == 1) {//Existem OS(s) em aberto ...
        nova_janela('importar_os.php?id_nfe=<?=$id_nfe;?>', 'POP', '', '', '', '', 480, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    }else {//Não existem de OS(s), consequentemente segue o caminho normal ...
        /*Mas como já foi importado pelo menos 1 item de OS na Nota Fiscal não posso mais abrir a Tela 
        de Caminho Normal, porque senão ficaria mistureba sem sentindo e perderíamos o Controle 
        de Status de Nota Fiscal ...*/
        if(nf_com_itens_de_os == 1) {//Representa que está Nota Fiscal 
            alert('NÃO EXISTE(M) MAIS ITEM(NS) DE OS ATRELADO(S) A ESTA NOTA FISCAL !')
        }else {
            /*Esse parâmetro -> clique_automatico_incluir_itens 
            Representa que essa função foi chamada para ser executada de forma automática ...*/
            if(clique_automatico_incluir_itens == 'S') {
                nova_janela('incluir_itens_pedidos.php?passo=1&id_nfe=<?=$id_nfe;?>', 'POP', '', '', '', '', 480, 980, 'c', 'c', '', '', 's', 's', '', '', '')
            }else {//Caminho Padrão ...
                nova_janela('incluir_itens_pedidos.php?id_nfe=<?=$id_nfe;?>', 'POP', '', '', '', '', 480, 980, 'c', 'c', '', '', 's', 's', '', '', '')
            }
        }
    }
}
    
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
        nova_janela('alterar.php?id_nfe=<?=$id_nfe;?>&posicao='+posicao, 'POP', '', '', '', '', 450, 850, 'c', 'c')
    }
}

function ajuste() {
    nova_janela('ajuste.php?id_nfe=<?=$id_nfe;?>', 'POP', '', '', '', '', 580, 850, 'c', 'c', '', '', 's', 's', '', '', '')
}

function outras_opcoes() {
    nova_janela('outras_opcoes.php?id_nfe=<?=$id_nfe;?>', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
}

function imprimir() {
    nova_janela('relatorio_pdf/relatorio.php?id_nfe=<?=$id_nfe;?>', 'CONSULTAR', 'F')
}

function clique_automatico_incluir_itens() {
    var clique_automatico_incluir_itens = '<?=$_GET['clique_automatico_incluir_itens'];?>'
    if(clique_automatico_incluir_itens == 'S') document.form.cmd_incluir.onclick()
}
</Script>
</head>
<?
/*Esse parâmetro -> $clique_automatico_incluir_itens
Dispara um clique automático no botão de Incluir itens de NF, assim que acaba de ser gerado a Nova NF ...*/

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
<body onload='clique_automatico_incluir_itens()'>
<form name='form'>
<input type='hidden' name='parametro_velho' value='<?=$parametro_velho2;?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <td align='center'>
<?
    //Se existir esse parâmetro, então não volto p/ o Filtro que corresponde ao Alterar / Imprimir ...
    $url = (!empty($_GET['pop_up'])) ? '../consultar.php' : 'consultar.php';//Tela de Consultar senão Tela de Alterar / Imprimir ...
?>
        <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="parent.location = '<?=$url.$parametro_velho;?>'" class='botao'>
<?
//Se essa tela foi aberta como sendo Pop-UP, não exibo esses botões ...
    if(empty($_GET['pop_up'])) {
?>
        <input type='button' name='cmd_cabecalho' value='Cabe&ccedil;alho' title='Cabe&ccedil;alho' onclick="nova_janela('../alterar_cabecalho.php?id_nfe=<?=$id_nfe;?>', 'CABECALHO', '', '', '', '', 550, 850, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
//Se a Nota Fiscal estiver em Aberto de forma Total ou Parcial eu ainda posso estar incluindo + itens
        if($situacao == 0 || $situacao == 1) {
            $controle_botao = "class='botao' ";
        }else {
            $controle_botao = "class='disabled' onclick='alert(".'"NOTA CONCLUÍDA / FECHADA !"'.")'";
        }
?>
        <input type='button' name='cmd_incluir' value='Incluir Item' title='Incluir' <?=$controle_botao;?> onclick="incluir_item('<?=$_GET['clique_automatico_incluir_itens'];?>')">
<?
        if($linhas_itens > 0) {
?>
        <input type='button' name='cmd_alterar' value='Alterar Item' title='Alterar' <?=$controle_botao;?> onclick='alterar_item()'>
        <input type='button' name='cmd_excluir' value='Excluir Item(ns)' title='Excluir Item(ns)' <?=$controle_botao;?> onclick="nova_janela('excluir_itens.php?id_nfe=<?=$id_nfe;?>', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')">
<?
        }
?>
        <input type='button' name='cmd_ajuste' value='Ajuste' title='Ajuste' <?=$controle_botao;?> onclick='ajuste()'>
        <input type='button' name='cmd_outras_opcoes' value='Outras Opções' title='Outras Opções' onclick='outras_opcoes()' class='botao'>
<?
        if($tipo == 2) {//Só existe impressão de PDF, para Nota que é do Tipo SGD
//Só exibe, esse botão em NF, quando está tiver pelo menos 1 Item ...
            if($linhas_itens > 0) {
//Aqui eu verifico se existe pelo menos 1 parcelamento de Vencimento feito p/ esta NF ... 
                $sql = "SELECT id_nfe_financiamento 
                        FROM `nfe_financiamentos` 
                        WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
                $campos_financiamento = bancos::sql($sql);
                if(count($campos_financiamento) == 0) {
                    $controle_imprimir = "class='disabled' onclick='alert(".'"PRECISA GERAR AS PARCELAS DE FINANCIAMENTO PRIMEIRO ANTES DE IMPRIMIR !"'.")' ";
                }else {
                    $controle_imprimir = "class='botao' onclick='imprimir()' ";
                }
?>
        <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' <?=$controle_imprimir;?>>
<?
            }
        }
//Somente p/ os usuários Gladys 14, Roberto 62, Fabio Petroni 64, Dárcio 98 e Netto 147 porque programam ...
        if($_SESSION['id_funcionario'] == 14 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
            if($situacao == 2) {//Esse botão só pode ser exibido quando a Nota Fiscal estiver Liberada e não estiver importada no Financeiro ...
?>
        <input type='button' name='cmd_estornar_nota_fiscal' value='Estornar Nota Fiscal' title='Estornar Nota Fiscal' onclick="nova_janela('estornar_nota_fiscal.php?id_nfe=<?=$id_nfe;?>', 'POP', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
            }
        }
    }
?>
    </td>
</table>
<input type='hidden' name='id_nfe' value='<?=$id_nfe;?>'>
</form>
</body>
</html>