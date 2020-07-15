<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');
require('../../../../../lib/data.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/genericas.php');//Essa biblioteca é requerida dentro da Intermodular ...
require('../../../../../lib/intermodular.php');//Essa biblioteca é utilizada dentro da Biblioteca 'faturamentos' ...
require('../../../../classes/array_sistema/array_sistema.php');
session_start('funcionarios');

if($id_emp == 1) {//Albafer
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {//Tool Master
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {//Grupo
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}else if($id_emp == 0) {//Todas Empresas
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/todas_empresas/index.php';
}
segurancas::geral($endereco, '../../../../../');

$mensagem[1] = "<font class='confirmacao'>VENCIMENTO(S) ALTERADO(S) COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>NÃO É POSSÍVEL ALTERAR OS VENCIMENTOS(S) !!! ALGUMA DAS DUPLICATAS ESTÁ COM RECEBIMENTO !</font>";

$id_conta_receber = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_conta_receber'] : $_GET['id_conta_receber'];

if(!empty($_POST['txt_vencimento1'])) {
    //1) Antes de apagar as duplicatas o sistema verifica se existe alguma que já está com Recebimento ...
    $sql = "SELECT id_conta_receber 
            FROM `contas_receberes` 
            WHERE `id_nf` = '$_POST[hdd_nf]' 
            AND `status` > '0' LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Não é possível apagar as duplicatas devido alguma delas estar com recebimento ...
        $valor = 2;
    }else {//As duplicatas estão sem recebimento, sendo assim podemos alterar os vencimentos e duplicatas ...
        $prazo_medio = intermodular::prazo_medio($_POST['txt_vencimento1'], $_POST['txt_vencimento2'], $_POST['txt_vencimento3'], $_POST['txt_vencimento4']);
        /*******************************************************************************/
        /*O sistema altera os Vencimentos da NF "Faturamento" e muda o Status de NF p/ em Aberto p/ que essa possa 
        ser reimportada novamente ...*/
        $sql = "UPDATE `nfs` SET `vencimento1` = '$_POST[txt_vencimento1]', `vencimento2` = '$_POST[txt_vencimento2]', `vencimento3` = '$_POST[txt_vencimento3]', vencimento4 = '$_POST[txt_vencimento4]', valor1 = '$_POST[txt_valor1]', valor2 = '$_POST[txt_valor2]', `valor3` = '$_POST[txt_valor3]', `valor4` = '$_POST[txt_valor4]', `prazo_medio` = '$prazo_medio', `importado_financeiro` = 'N' 
                WHERE `id_nf` = '$_POST[hdd_nf]' LIMIT 1 ";
        bancos::sql($sql);
        /************************************************************************************/
        /*************************Controle com a parte de Duplicatas*************************/
        /************************************************************************************/
        //Busco o "id_conta_receber" (Duplicata) através do id_nf ...
        $sql = "SELECT id_conta_receber 
                FROM `contas_receberes` 
                WHERE `id_nf` = '$_POST[hdd_nf]' LIMIT 1 ";
        $campos_contas_receber = bancos::sql($sql);
        if(count($campos_contas_receber) == 1) {
            //Verifico se existe alguma Duplicata de Devolução vinculada à Duplicata de Saída ...
            $sql = "SELECT id_conta_receber_nf_devolucao, id_nf_devolucao 
                    FROM `contas_receberes_vs_nfs_devolucoes` 
                    WHERE `id_conta_receber` = '".$campos_contas_receber[0]['id_conta_receber']."' ";
            $campos_contas_receber_devolucao = bancos::sql($sql);
            $linhas_contas_receber_devolucao = count($campos_contas_receber_devolucao);
            if($linhas_contas_receber_devolucao > 0) {//Sim, existe ...
                for($i = 0; $i < $linhas_contas_receber_devolucao; $i++) {
                    //O sistema apaga a Duplicata de Devolução primeiro ...
                    $sql = "DELETE FROM `contas_receberes_vs_nfs_devolucoes` WHERE `id_conta_receber_nf_devolucao` = '".$campos_contas_receber_devolucao[$i]['id_conta_receber_nf_devolucao']."' LIMIT 1 ";
                    bancos::sql($sql);
                    //************************************************************//
                    //Mudo o Status da Nota de Devolução p/ Hum para que esta possa ser Importada novamente no Futuro ...
                    //************************************************************//
                    $sql = "UPDATE `nfs` SET `importado_financeiro`= 'N' WHERE `id_nf` = '".$campos_contas_receber_devolucao[$i]['id_nf_devolucao']."' LIMIT 1 ";
                    bancos::sql($sql);
                }
?>
    <Script Language = 'JavaScript'>
        //Dou esse informativo p/ que o Usuário fique a par do que aconteceu com as Duplicatas de Devolução ...
        alert('EXISTIA(M) DUPLICATA(S) DE DEVOLUÇÃO ATRELADA(S) A ESSA(S) DUPLICATA(S) DE SAÍDA QUE VOCÊ ALTEROU O(S) VENCIMENTO(S) !!!\n\nESSA(S) DUPLICATA(S) DE DEVOLUÇÃO FOI(RAM) AUTOMATICAMENTE DO SISTEMA E PRECISA(M) SER REIMPORTADA(S) NOVAMENTE !')
    </Script>
<?
            }
            /*Por fim o sistema apaga as duplicatas que estavam importadas "Financeiro", pois ficaram erradas 
            devido a alteração dos vencimentos, "faço isso p/ agilizar o trampo do povo" ...*/
            $sql = "DELETE FROM `contas_receberes` WHERE `id_nf` = '$_POST[hdd_nf]' ";
            bancos::sql($sql);
        }
        /************************************************************************************/
?>
    <Script Language = 'JavaScript'>
        //Atualiza a Tela de Baixo de modo que a Conta errada desapareça ...
        opener.parent.itens.recarregar_tela()
    </Script>
<?

        $valor = 1;
    }
}
?>
<html>
<head>
<title>.:: Alterar Vencimentos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var vencimento1 = eval(document.form.txt_vencimento1.value)
    var vencimento2 = eval(document.form.txt_vencimento2.value)
    var vencimento3 = eval(document.form.txt_vencimento3.value)
    var vencimento4 = eval(document.form.txt_vencimento4.value)
    
    if(vencimento1 == 0 || vencimento1 == '') {
        alert('DIGITE O VENCIMENTO 1 !')
        return false
    }
    if(vencimento2 != '') {
        if(vencimento2 <= vencimento1) {
            alert('VENCIMENTO 2 MENOR QUE O VENCIMENTO 1 !')
            document.form.txt_vencimento2.focus()
            return false
        }
    }    
    if(vencimento3 != '') {
        if(vencimento3 <= vencimento2) {
            alert('VENCIMENTO 3 MENOR QUE O VENCIMENTO 2 !')
            document.form.txt_vencimento3.focus()
            return false
        }
        if(vencimento3 <= vencimento1) {
            alert('VENCIMENTO 3 MENOR QUE O VENCIMENTO 1 !')
            document.form.txt_vencimento3.focus()
            return false
        } 
    }
    if(vencimento4 != '') {
        if(vencimento4 <= vencimento3) {
            alert('VENCIMENTO 4 MENOR QUE O VENCIMENTO 3 !')
            document.form.txt_vencimento4.focus()
            return false
        } 
        if(vencimento4 <= vencimento2) {
            alert('VENCIMENTO 4 MENOR QUE O VENCIMENTO 2 !')
            document.form.txt_vencimento4.focus()
            return false
        }   
        if(vencimento4 <= vencimento1) {
            alert('VENCIMENTO 4 MENOR QUE O VENCIMENTO 1 !')
            document.form.txt_vencimento4.focus()
            return false
        }
    }
    //Habilito os inputs para submeter ...
    document.form.txt_valor1.disabled = false
    document.form.txt_valor2.disabled = false
    document.form.txt_valor3.disabled = false
    document.form.txt_valor4.disabled = false
    document.form.txt_vencimento1.disabled = false
    document.form.txt_vencimento2.disabled = false
    document.form.txt_vencimento3.disabled = false
    document.form.txt_vencimento4.disabled = false
    return limpeza_moeda('form', 'txt_valor1, txt_valor2, txt_valor3, txt_valor4, ')
}

function habilitar_text() {
    if(document.form.txt_vencimento2.value != '') {
        document.form.txt_vencimento3.className = 'caixadetexto'
        document.form.txt_vencimento3.disabled  = false
    }
    if(document.form.txt_vencimento3.value != '') {
        document.form.txt_vencimento4.className = 'caixadetexto'
        document.form.txt_vencimento4.disabled = false
    }
}

function travar_caixinha(event) {
    if(document.all) { 
        tecla_digitada = event.keyCode
    }else {
        tecla_digitada = event.which
    }
    if(document.form.txt_vencimento2.value.length == '1') { 
        document.form.txt_vencimento3.value = ''
        document.form.txt_vencimento3.className = 'textdisabled'
        document.form.txt_vencimento3.disabled = true 
        document.form.txt_vencimento4.value = ''
        document.form.txt_vencimento4.className = 'textdisabled'
        document.form.txt_vencimento4.disabled = true         
    }
    if(document.form.txt_vencimento3.value.length == '1' && tecla_digitada == '8') { 
        document.form.txt_vencimento4.value = ''
        document.form.txt_vencimento4.className = 'textdisabled'
        document.form.txt_vencimento4.disabled = true
        document.form.txt_vencimento3.value = ''
        document.form.txt_vencimento3.className = 'textdisabled'
        document.form.txt_vencimento3.disabled = true
        document.form.txt_vencimento2.focus()
    }
    if(document.form.txt_vencimento4.value.length == '1' && tecla_digitada == '8') { 
        document.form.txt_vencimento4.value = ''
        document.form.txt_vencimento4.className = 'textdisabled'
        document.form.txt_vencimento4.disabled = true
        document.form.txt_vencimento3.focus()
    }
}

function calcular_duplicatas(data_emissao, valor_nota_fiscal) {
    var qtde_parcelas   = 1
    //Parcela 1 ...
    if(document.form.txt_vencimento1.value != '') {
        nova_data(data_emissao, 'document.form.txt_data_vencimento1', 'document.form.txt_vencimento1')
        document.form.txt_valor1.value = valor_nota_fiscal
        document.form.txt_valor1.value = arred(document.form.txt_valor1.value, 2, 1)
    }else {
        document.form.txt_data_vencimento1.value    = ''
        document.form.txt_valor1.value              = ''
    }
    //Parcela 2 ...
    if(document.form.txt_vencimento2.value != '') {
        nova_data(data_emissao, 'document.form.txt_data_vencimento2', 'document.form.txt_vencimento2')
        qtde_parcelas++
        document.form.txt_valor1.value = valor_nota_fiscal / qtde_parcelas
        document.form.txt_valor2.value = valor_nota_fiscal / qtde_parcelas
        document.form.txt_valor1.value = arred(document.form.txt_valor1.value, 2, 1)
        document.form.txt_valor2.value = arred(document.form.txt_valor2.value, 2, 1)
    }else {
        document.form.txt_data_vencimento2.value = ''
        document.form.txt_valor2.value = ''
    }
    //Parcela 3 ...
    if(document.form.txt_vencimento3.value != '') {
        nova_data(data_emissao, 'document.form.txt_data_vencimento3', 'document.form.txt_vencimento3')
        qtde_parcelas++
        document.form.txt_valor1.value = valor_nota_fiscal / qtde_parcelas
        document.form.txt_valor2.value = valor_nota_fiscal / qtde_parcelas
        document.form.txt_valor3.value = valor_nota_fiscal / qtde_parcelas
        document.form.txt_valor1.value = arred(document.form.txt_valor1.value, 2, 1)
        document.form.txt_valor2.value = arred(document.form.txt_valor2.value, 2, 1)
        document.form.txt_valor3.value = arred(document.form.txt_valor3.value, 2, 1)
    }else {
        document.form.txt_data_vencimento3.value = ''
        document.form.txt_valor3.value = ''
    }
    //Parcela 4 ...
    if(document.form.txt_vencimento4.value != '') {
        nova_data(data_emissao, 'document.form.txt_data_vencimento4', 'document.form.txt_vencimento4')
        qtde_parcelas++
        document.form.txt_valor1.value = valor_nota_fiscal / qtde_parcelas
        document.form.txt_valor2.value = valor_nota_fiscal / qtde_parcelas
        document.form.txt_valor3.value = valor_nota_fiscal / qtde_parcelas
        document.form.txt_valor4.value = valor_nota_fiscal / qtde_parcelas
        document.form.txt_valor1.value = arred(document.form.txt_valor1.value, 2, 1)
        document.form.txt_valor2.value = arred(document.form.txt_valor2.value, 2, 1)
        document.form.txt_valor3.value = arred(document.form.txt_valor3.value, 2, 1)   
        document.form.txt_valor4.value = arred(document.form.txt_valor4.value, 2, 1)   
    }else {
        document.form.txt_data_vencimento4.value = ''
        document.form.txt_valor4.value = ''
    }
    
    var valor1 = (document.form.txt_valor1.value != '') ? eval(strtofloat(document.form.txt_valor1.value)) : 0
    var valor2 = (document.form.txt_valor2.value != '') ? eval(strtofloat(document.form.txt_valor2.value)) : 0
    var valor3 = (document.form.txt_valor3.value != '') ? eval(strtofloat(document.form.txt_valor3.value)) : 0
    var valor4 = (document.form.txt_valor4.value != '') ? eval(strtofloat(document.form.txt_valor4.value)) : 0

    var diferenca_centavos = valor_nota_fiscal - (valor1 + valor2 + valor3 + valor4)
    if(diferenca_centavos != 0) {
        document.form.txt_valor1.value = eval(strtofloat(document.form.txt_valor1.value)) + diferenca_centavos
        document.form.txt_valor1.value = arred(document.form.txt_valor1.value, 2, 1)
    }
}

function imprimir(nota_sgd) {
    if(nota_sgd == 'N') {//Nota Fiscal com Nota ... rsrs
        nova_janela('../../../../faturamento/nota_saida/itens/relatorio/imprimir_copia_duplicata.php?id_nf=<?=$_POST['hdd_nf'];?>', 'CONSULTAR', 'F')
    }else {//Nota Fiscal ... sem papel .. SGD
        alert('COMUNICAR AO FATURAMENTO P/ QUE OS BOLETOS SEJAM REFEITOS !')
    }
}
</Script>
</head>
<body topmargin='30'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
<?
/*Como foi possível alterar os vencimentos, exibo o botão p/ que o usuário possa importar as duplicatas novamente 
com o novo vencimento ...*/
    if($valor == 1) {
        //Busca-se o id_empresa p/ que o sistema consiga identificar se é possível Imprimir as Novas duplicatas ...
        $sql = "SELECT nfs.id_empresa 
                FROM `nfs` 
                WHERE `id_nf` = '$_POST[hdd_nf]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if($campos[0]['id_empresa'] == 1 || $campos[0]['id_empresa'] == 2) {
            $nota_sgd = 'N';
        }else {
            $nota_sgd = 'S';
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Clique no Botão abaixo p/ incluir novamente a(s) Duplicata(s) com o Vencimento correto
        </td>
    </tr>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_incluir_duplicatas' value='Incluir Duplicata(s)' title='Incluir Duplicata(s)' onclick="nova_janela('liberar_nota/incluir_nfs_saida.php?passo=1&id_nf=<?=$_POST['hdd_nf'];?>&id_emp=<?=$id_emp;?>', 'IMPORTAR_DUPLICATA', '', '', '', '', 450, 950, 'c', 'c', '', '', 's', 's', '', '', '')" style='width:180px; height: 40px; color: darkblue; font-size: 13px; font-weight: bold' class='botao'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_imprimir' value='Imprimir Cópia da Duplicata' title='Imprimir Cópia da Duplicata' onclick="imprimir('<?=$nota_sgd;?>')" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
<?
    }else {
        $sql = "SELECT nfs.*, c.id_pais 
                FROM `contas_receberes` cr 
                INNER JOIN `nfs` ON nfs.id_nf = cr.id_nf 
                INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
                WHERE cr.id_conta_receber = '$id_conta_receber' LIMIT 1 ";
        $campos     = bancos::sql($sql);
        $id_pais    = $campos[0]['id_pais'];
        if($campos[0]['valor2'] != 0 && $campos[0]['valor2'] != '') {
            $class3     = 'caixadetexto';
            $disabled3  = '';
        }else {
            $class3     = 'textdisabled';
            $disabled3  = 'disabled';
        }

        if($campos[0]['valor3'] != 0 && $campos[0]['valor3'] != '') {
            $class4     = 'caixadetexto';
            $disabled4  = '';
        }else {
            $class4     = 'textdisabled';
            $disabled4  = 'disabled';
        }
        //Se o Cliente for Estrangeiro o Tipo de Moeda é em U$ ...
        $tipo_moeda = ($id_pais != 31) ? 'U$' : 'R$';
        $nota_sgd   = ($campos[0]['id_empresa'] == 1 || $campos[0]['id_empresa'] == 2) ? 'N' : 'S';
        
        $calculo_total_impostos = calculos::calculo_impostos(0, $campos[0]['id_nf'], 'NF');
        $valor1                 = number_format($campos[0]['valor1'], 2, ',', '.');

        $data_vencimento1 = data::adicionar_data_hora(data::datetodata($campos[0]['data_emissao'], '/'), $campos[0]['vencimento1']);
        if($campos[0]['vencimento2'] != 0) {
            $vencimento2        = $campos[0]['vencimento2'];
            $data_vencimento2   = data::adicionar_data_hora(data::datetodata($campos[0]['data_emissao'], '/'), $vencimento2);
            $valor2             = number_format($campos[0]['valor2'], 2, ',', '.');
        }
        if($campos[0]['vencimento3'] != 0) {
            $vencimento3        = $campos[0]['vencimento3'];
            $data_vencimento3   = data::adicionar_data_hora(data::datetodata($campos[0]['data_emissao'], '/'), $vencimento3);
            $valor3             = number_format($campos[0]['valor3'], 2, ',', '.');
        }
        if($campos[0]['vencimento4'] != 0) {
            $vencimento4        = $campos[0]['vencimento4'];
            $data_vencimento4   = data::adicionar_data_hora(data::datetodata($campos[0]['data_emissao'], '/'), $vencimento4);
            $valor4             = number_format($campos[0]['valor4'], 2, ',', '.');
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Vencimento(s) - Nota Fiscal 
            <font color='yellow'>
                <?=faturamentos::buscar_numero_nf($campos[0]['id_nf'], 'S');?>				
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <font color='yellow'>
                Valor total da Nota 
            </font>
            R$ <?=number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');?>
            <font color='yellow'>
                - Data Emissão:
            </font>
            <?=data::datetodata($campos[0]['data_emissao'], '/');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Vencimento 1:
        </td>
        <td>
            <input type='text' name='txt_vencimento1' value='<?=$campos[0]['vencimento1'];?>' title='Digite o Vencimento 1' size='5' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular_duplicatas('<?=data::datetodata($campos[0]['data_emissao'], '/');?>', '<?=number_format($calculo_total_impostos['valor_total_nota'], 2, '.', '');?>')" class='caixadetexto'>
            DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_vencimento1' value='<?=$data_vencimento1;?>' title='Data do Vencimento 1' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$tipo_moeda;?>
            <input type='text' name='txt_valor1' value='<?=$valor1;?>' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Vencimento 2:
        </td>
        <td>
            <input type='text' name='txt_vencimento2' value='<?=$vencimento2;?>' title='Digite o Vencimento 2' size='5' maxlength='3' onkeydown='travar_caixinha(event)' onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular_duplicatas('<?=data::datetodata($campos[0]['data_emissao'], '/');?>', '<?=number_format($calculo_total_impostos['valor_total_nota'], 2, '.', '');?>');habilitar_text()" class='caixadetexto'>
            DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_vencimento2' value='<?=$data_vencimento2;?>' title='Data do Vencimento 2' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$tipo_moeda;?>
            <input type='text' name='txt_valor2' value='<?=$valor2;?>' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Vencimento 3:
        </td>
        <td>
            <input type='text' name='txt_vencimento3' value='<?=$vencimento3;?>' title='Digite o Vencimento 3' size='5' maxlength='3' onkeydown='travar_caixinha(event)' onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular_duplicatas('<?=data::datetodata($campos[0]['data_emissao'], '/');?>', '<?=number_format($calculo_total_impostos['valor_total_nota'], 2, '.', '');?>');habilitar_text()"  class='<?=$class3;?>' <?=$disabled3;?>>
            DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_vencimento3' value='<?=$data_vencimento3;?>' title='Data do Vencimento 3' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$tipo_moeda;?>
            <input type='text' name="txt_valor3" value="<?=$valor3;?>" size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Vencimento 4:
        </td>
        <td>
            <input type='text' name='txt_vencimento4' value='<?=$vencimento4;?>' title='Digite o Vencimento 4' size='5' maxlength='3' onkeydown='travar_caixinha(event)' onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular_duplicatas('<?=data::datetodata($campos[0]['data_emissao'], '/');?>', '<?=number_format($calculo_total_impostos['valor_total_nota'], 2, '.', '');?>');habilitar_text()"  class='<?=$class4;?>' <?=$disabled4;?>>
            DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_vencimento4' value='<?=$data_vencimento4;?>' title='Data do Vencimento 4' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=$tipo_moeda;?>
            <input type='text' name='txt_valor4' value='<?=$valor4;?>' size='12' maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
<?
    }
?>
</table>
<!--Coloquei os hiddens aqui porque algumas dessas variáveis variáveis são abastecidas no decorrer dessa Tela-->
<input type='hidden' name='hdd_nf' value='<?=$campos[0]['id_nf'];?>'>
<input type='hidden' name='id_conta_receber' value='<?=$id_conta_receber;?>'>
<!--****************************************************************************-->
</form>
</body>
</html>
<?
/*Essa função foi colocada aqui em Baixo p/ esse caso específico e não dentro do Header, porque esta depende 
de algumas variáveis que só vão sendo carregadas durante o decorrer desta Tela ...*/
/***************Segurança de Notas Fiscais e Duplicatas***************/
//Aqui eu verifico se o Somatório das Duplicatas da NF estão diferente do Valor Total da NF ...
    $valor_total_nota   = round($calculo_total_impostos['valor_total_nota'], 2);
    $total_duplicatas   = round($campos[0]['valor1'] + $campos[0]['valor2'] + $campos[0]['valor3'] + $campos[0]['valor4'], 2);
/*********************************************************************/
?>
<Script Language = 'JavaScript'>
/***************Segurança de Notas Fiscais e Duplicatas***************/
//Aqui eu verifico se o Somatório das Duplicatas da NF estão diferente do Valor Total da NF ...
    var id_pais             = eval('<?=$id_pais;?>')
    var valor_total_nota    = eval('<?=$valor_total_nota;?>')
    var total_duplicatas    = eval('<?=$total_duplicatas;?>')
    //Nunca poderemos ter um Valor Total de NF diferente do Valor Total das Duplicatas ...
    if(id_pais == 31) {//A princípio estamos fazendo esse tratamento apenas em cima dos Clientes daqui do Brasil ...
        if(valor_total_nota != total_duplicatas) {
            var resposta = confirm('O TOTAL DA(S) DUPLICATA(S) ESTA INCOERENTE COM O VALOR TOTAL DA NOTA FISCAL !!!\n\nDESEJA RECALCULAR ?')
            if(resposta == true) calcular_duplicatas('<?=data::datetodata($campos[0]['data_emissao'], '/');?>', '<?=number_format($calculo_total_impostos['valor_total_nota'], 2, '.', '');?>')
        }
    }
/*********************************************************************/
</Script>