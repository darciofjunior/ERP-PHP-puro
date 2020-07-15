<?
require('../../../../lib/segurancas.php');
require('../../../../lib/faturamentos.php');

switch($opcao) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    case 4://Significa que veio do Menu de Devolução 
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
    default://Significa que veio do Menu de Devolução ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
}

//Busca alguns dados da NF p/ alguns controles + abaixo ...
$sql = "SELECT status, devolucao_faturada 
        FROM `nfs` 
        WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
$campos = bancos::sql($sql);

if($opcao == 4) {//Significa que veio do Menu de Devolução
    if($campos[0]['devolucao_faturada'] == 'S') {//Nesse caso não e possivel a Exlusão ...
        $disabled           = 'disabled';
        $checked            = '';
        $checked_imprimir   = 'checked';
    }else {//Se a NF de Devolução não estiver faturada, posso excluir todos os Itens normalmente ...
        $checked            = 'checked';
        $disabled_imprimir  = 'disabled';
        $checked_imprimir   = '';
    }
}else {//Se for de Outros Menus ...
//Função q verifica se a Nota está liberada_para_faturar, faturada, empacotada, despachada, cancelada
//caso sim, então o usuário não pode + incluir, alterar ou excluir nenhum item
    if($campos[0]['status'] >= 1) {//Está liberado, então ñ posso excluir nada
//Verifica se a NF possui Suframa ...
        $sql = "SELECT suframa 
                FROM `nfs` 
                WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
        $campos_suframa = bancos::sql($sql);
/*Se a NF possuir Suframa, então ainda não se posso travar os Options para que o Usuário possa Excluir os Itens 
caso seja necessário, porque estes influenciam muito nos cálculos de NF ...*/
        if($campos_suframa[0]['suframa'] > 0) {//Existe Suframa, então não pode travar os Options ...
            $checked            = 'checked';
            $disabled_imprimir  = 'disabled';
            $checked_imprimir   = '';
        }else {//Não existe Suframa, trava os Options normalmente ...
            $disabled           = 'disabled';
            $checked            = '';
            $checked_imprimir   = 'checked';
        }
    }else {
        /*Se a NF possuir GNRE, então o usuário não tem como excluir os Itens da NF de forma a não 
        permitir que a NF seja cancelada ...*/
        $sql = "SELECT gnre 
                FROM `nfs` 
                WHERE `id_nf` = '$_GET[id_nf]' 
                AND gnre <> '' LIMIT 1 ";
        $campos_gnre = bancos::sql($sql);
        if(count($campos_gnre) == 1) {//Se existe GNRE ...
            $disabled 		= 'disabled';
            $checked 	 	= '';
            $rotulo_gnre 	= '<font color="darkblue"><b>(NÃO É POSSÍVEL EXCLUIR PORQUE JÁ EXISTE GNRE)</b></font>';
        }else {
            $checked            = 'checked';
        }
        $disabled_imprimir      = 'disabled';
        $checked_imprimir       = '';
    }
}
?>
<html>
<head>
<title>.:: Outras Opções ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post'>
<input type='hidden' name='nao_atualizar'>
<!--Controle de Tela-->
<input type='hidden' name='hdd_justificativa'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Outras Opções
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='1' title='Comprovante de Conferência de Material' id='label1' <?=$disabled_imprimir;?> <?=$checked_imprimir;?>>
            <label for='label1'>Comprovante de Conferência de Material</label>
        </td>
    </tr>
    <?
//Se for de Outros Menus ...
        //Exibirá essa opção desde que seje acessado por um outro Menu diferente do de 'Devolução'
        if($opcao != 4) {
            //Só aparecerá essas opções de Cancelamento da Nota p/ os usuários do Agueda 32, Roberto 62 e Darcio 98 ...
            if($_SESSION['id_funcionario'] == 32 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
                $pago_comissao_pode_excluir = faturamentos::pago_comissao_pode_excluir($_GET['id_nf']);
                if($pago_comissao_pode_excluir == 0) {//Não pode excluir a comissão
                    $mensagem = " <font color='red' size='2'><b> (JÁ FOI PAGO A COMISSÃO DESSA NOTA FISCAL) </b></font> ";
                    $disabled = "disabled";
                }else {//Se ainda não foi pago, então ...
                    //Aqui eu verifico se a Nota Fiscal já foi importada p/ o Financeiro ...
                    $sql = "SELECT `id_conta_receber` 
                            FROM `contas_receberes` 
                            WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
                    $campos = bancos::sql($sql);
                    //Se está já estiver importada p/ o Financeiro, então eu não posso mais cancelar esta Nota ...
                    if(count($campos) == 1) {
                        $mensagem = " <font color='red' size='2'><b> (NOTA FISCAL JÁ IMPORTADA PELO DEPTO. FINANCEIRO) </b></font> ";
                        $disabled = "disabled";
                    }else {
                        //Verifico se algum item dessa Nota Fiscal de Saída consta em NF de Devolução ...
                        $sql = "SELECT `id_nfs_item` 
                                FROM `nfs_itens` 
                                WHERE `id_nf` = '$_GET[id_nf]' ";
                        $campos_itens_saida = bancos::sql($sql);
                        $linhas_itens_saida = count($campos_itens_saida);
                        for($i = 0; $i < $linhas_itens_saida; $i++) $itens_saida.= $campos_itens_saida[$i]['id_nfs_item'].', ';
                        $itens_saida = substr($itens_saida, 0, strlen($itens_saida) - 2);
                        
                        $sql = "SELECT id_nfs_item 
                                FROM `nfs_itens` 
                                WHERE `id_nf_item_devolvida` IN ($itens_saida) LIMIT 1 ";
                        $campos = bancos::sql($sql);
                        if(count($campos) == 1) {
                            $mensagem = " <font color='red' size='2'><b> (NOTA FISCAL POSSUI ITEM(NS) DE DEVOLUÇÃO) </b></font> ";
                            $disabled = "disabled";
                        }else {
                            $disabled = '';
                        }
                    }
                }
	?>
    <tr class='linhanormal'>
        <td>
            <input type='radio' name='opt_opcao' value='2' title='Cancelar Nota Fiscal' id='label2' <?=$disabled;?>>
            <label for='label2'>Cancelar Nota Fiscal</label>
            <?=$mensagem;?>
        </td>
    </tr>
<?
            }
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avancar' value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' onclick="avancar()" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
<Script Language = 'JavaScript'>
function avancar() {
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1

    if(typeof(document.form.opt_opcao[0]) == 'object') {//Significa que existe mais de 1 option ...
        if(document.form.opt_opcao[0].checked == true) {//Comprovante de Conferência de Material ...
            window.close()
            nova_janela('comprovante_conferencia.php?id_nf=<?=$_GET['id_nf'];?>', 'CONSULTAR', 'F')
        }else if(document.form.opt_opcao[1].checked == true) {//Cancelar Nota ...
            window.location = 'cancelar_nota.php?id_nf=<?=$_GET['id_nf'];?>'
        }else {//Se não estiver nenhum opção selecionada, então ...
            alert('SELECIONE UMA OPÇÃO !')
            return false
        }
    }else {//Só existe 1 único option ...
        window.close()
        nova_janela('comprovante_conferencia.php?id_nf=<?=$_GET['id_nf'];?>', 'CONSULTAR', 'F')
    }
}
</Script>
</html>