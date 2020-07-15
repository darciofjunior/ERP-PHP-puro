<?
require('../../../../../lib/segurancas.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/grupo/index.php';
}else if($id_emp == 0) {//Todos
    $endereco = '/erp/albafer/modulo/financeiro/pagamento/a_pagar/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');
?>
<html>
<head>
<title>.:: Rodapé de Itens de Contas a Pagar ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'Javascript'>
function selecionar(valor) {
    var id_contas_apagares = ''
//Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox    = 0
    var elementos   = parent.itens.document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true && elementos[i].name == 'chkt_conta_apagar[]') {
                id_contas_apagares+= elementos[i].value + ', '
                checkbox ++
            }
        }
    }
    id_contas_apagares = id_contas_apagares.substr(0, id_contas_apagares.length - 2)
    
    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
    }else {
        if(valor == 1) {
            if(checkbox > 1) {
                alert('SELECIONE SOMENTE UM ITEM !')
            }else {
                window.location = 'rodape.php?passo=1&id_conta_apagar='+id_contas_apagares+'&linhas=<?=$linhas;?>'
            }
        }else if(valor == 2) {
            var id_funcionario = eval('<?=$_SESSION['id_funcionario'];?>')
            /*A Dona Sandra 66 é a única que pode excluir + de uma Conta à Pagar "Em Lote" ou 
            Dárcio porque programa ...*/
            if(id_funcionario == 62 || id_funcionario == 66 || id_funcionario == 98) {
                nova_janela('excluir.php?id_conta_apagar='+id_contas_apagares, 'POP', '', '', '', '', 520, 950, 'c', 'c', '', '', 's', 's', '', '', '')
            }else {
                if(checkbox > 1) {
                    alert('SELECIONE SOMENTE UM ITEM !')
                }else {
                    nova_janela('excluir.php?id_conta_apagar='+id_contas_apagares, 'POP', '', '', '', '', 520, 950, 'c', 'c', '', '', 's', 's', '', '', '')
                }
            }
        }else if(valor == 3){
            nova_janela('relatorios/relatorio_a_pagar.php?id_emp=<?=$id_emp;?>&id_contas_apagares='+id_contas_apagares, 'RELATORIO', 'F');
        }else {
            if(parent.itens.document.form.bloquear_pagamento.value == 0) {
                nova_janela('controle_pagamento.php?id_conta_apagar='+id_contas_apagares, 'POP', '', '', '', '', 580, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
            }else {
                alert('ESTA(S) CONTA(S) NÃO PODE(M) SER QUITADA(S) ! \n\n EXISTE(M) SELECIONADA(S) CONTA(S) À PAGAR COMO PREVISÃO \n OU CONTA(S) À PAGAR JÁ QUITADA(S) COM CHEQUE !')
            }
        }
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.parent.location = 'consultar_contas.php?itens=1&id_emp2=<?=$id_emp;?>'" class='botao'>
            <input type='button' name='cmd_incluir_liberar' value='Incluir / Liberar' title='Incluir / Liberar' onclick="nova_janela('opcoes.php', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
    if($linhas != 0) {
?>
            <input type='button' name='cmd_alterar' value='Alterar' title='Alterar' onclick='return selecionar(1)' class='botao'>
            <input type='button' name='cmd_excluir' value='Excluir' title='Excluir' onclick='return selecionar(2)' class='botao'>
            <input type='button' name='cmd_controle_pagamento' value='Controle de Pagamento' title='Controle de Pagamento' onclick='return selecionar(4)' class='botao'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='selecionar(3)' class='botao'>
<?
    }

    //A princípio, esse botão só é exibido p/ os usuários Roberto e Dárcio ...
    if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
?>
            <input type='button' name='cmd_contas_sem_grupo' value='Contas sem Grupo' title='Contas sem Grupo' onclick="nova_janela('contas_sem_grupo_ultimos_3anos.php', 'SEM_GRUPO', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:red' class='botao'>
<?
    }
?>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
if($passo == 1) {
    //Verifica o status da conta p/ saber se está pode ser alterada ...
    $sql = "SELECT `id_pedido`, `id_antecipacao`, `id_nfe`, `id_representante`, `status` 
            FROM `contas_apagares` 
            WHERE `id_conta_apagar` = '$_GET[id_conta_apagar]' LIMIT 1 ";
    $campos_contas_apagar = bancos::sql($sql);
    if($campos_contas_apagar[0]['status'] == 2) {//A conta já foi quitada ...
?>
        <Script Language = 'JavaScript'>
            alert('ESTA CONTA NÃO PODE SER ALTERADA !')
        </Script>
<?
    }else {
?>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<?
        if($campos_contas_apagar[0]['id_representante'] > 0) {
?>
            <Script Language = 'JavaScript'>
                alert('NÃO EXISTE ALTERAR P/ ESTE TIPO DE CONTA !')
            </Script>
<?
        }else {
?>
            <Script Language = 'JavaScript'>
                nova_janela('../../alterar.php?id_conta_apagar=<?=$_GET[id_conta_apagar];?>', 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
            </Script>
<?
        }
    }
}
?>