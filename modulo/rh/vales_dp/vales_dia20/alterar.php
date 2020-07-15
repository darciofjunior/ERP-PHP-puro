<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>VALE DO DIA 20 ALTERADO COM SUCESSO.</font>";

$id_vale_dp = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_vale_dp'] : $_GET['id_vale_dp'];

if(!empty($_POST['id_vale_dp'])) {
//Alterando o Vale na Tabela ...
    $sql = "UPDATE `vales_dps` SET `valor_acima_limite` = '$_POST[txt_vlr_acima_limite]', `valor` = '$_POST[txt_vlr_liberado]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_vale_dp` = '$_POST[id_vale_dp]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

//Busca dados de vale através do id_vale_dp passado por parâmetro ...
$sql = "SELECT * 
        FROM `vales_dps` 
        WHERE `id_vale_dp` = '$id_vale_dp' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Vale Dia 20 ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Valor Pedido
    if(!texto('form', 'txt_vlr_acima_limite', '1', '1234567890,.', 'VALOR ACIMA LIMITE', '2')) {
        return false
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
    atualizar_abaixo()
//Habilito a caixa p/ poder gravar no Banco ...
    document.form.txt_vlr_liberado.disabled = false
    return limpeza_moeda('form', 'txt_vlr_acima_limite, txt_vlr_liberado, ')
}

function cop_vlr_acima_limite() {
//Igualando as variáveis ...
    vlr_direito = eval(strtofloat(document.form.txt_vlr_direito.value))
    vlr_pedido  = eval(strtofloat(document.form.txt_vlr_acima_limite.value))

    if(vlr_pedido <= (vlr_direito + 50)) {
//O campo vlr_liberado eu igualo ao campo vlr_pedido
        elementos[indice + 5].value = vlr_pedido
        elementos[indice + 5].value = arred(elementos[indice + 5].value, 2, 1)
    }else {
        elementos[indice + 5].value = ''
    }
}

function atualizar() {
    document.form.passo.value = 0
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.recarregar_tela()
}
</Script>
</head>
<body onload='document.form.txt_vlr_liberado.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Aqui eu renomeio essa variável $id_funcionario para $id_funcionario_loop para não dar conflito com 
a variável da Sessão "$id_funcionario"-->
<input type='hidden' name='id_funcionario_loop' value='<?=$campos[0]['id_funcionario'];?>'>
<input type='hidden' name='id_vale_dp' value='<?=$id_vale_dp;?>'>
<input type='hidden' name='nao_atualizar'>
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='passo' onclick="atualizar()">
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Vale Dia 20
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Funcionário:</b>
        </td>
        <td>
        <?
            $sql = "SELECT id_empresa, tipo_salario, salario_pd, salario_pf, salario_premio, nome 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '".$campos[0]['id_funcionario']."' LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            echo $campos_funcionario[0]['nome'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <?=genericas::nome_empresa($campos_funcionario[0]['id_empresa']);?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Emissão:</b>
        </td>
        <td>
            <?=data::datetodata($campos[0]['data_emissao'], '/');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Descontar:</b>
        </td>
        <td>
            <?=$campos[0]['descontar_pd_pf'];?>
        </td>
    </tr>
    <?
/***********************************************/
//Aqui é uma lógica p/ formar Data de Holerith do próximo mês ...
        $proximo_mes = date('m') + 1;
        $ano = date('Y');
        if($proximo_mes < 10) {
            $proximo_mes = '0'.$proximo_mes;
        }else if($proximo_mes == 13) {
            $proximo_mes = '01';
            $ano = date('Y') + 1;
        }
        $data_holerith = '05/'.$proximo_mes.'/'.$ano;
//Vou utilizar essa variável numa consulta de SQL mais abaixo ...
        $data_holerith_sql = data::datatodate($data_holerith, '-');
/***********************************************/
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Data de Holerith:</b>
        </td>
        <td>
            <?=data::datetodata($campos[0]['data_debito'], '/');?>
        </td>
    </tr>
    <?
        $id_vale_datas = '';//Valor Inicial ...

/*Aqui eu busco as 2 últimas Datas de Holerith p/ calcular a Média de Comissão do Vendedor, assim definindo
o valor de seu salário para o Vale do Dia 20 ...*/
        $sql = "SELECT `id_vale_data` 
                FROM `vales_datas` 
                WHERE `data` < '".$campos[0]['data_emissao']."' ORDER BY `id_vale_data` DESC LIMIT 2 ";
        $campos_data = bancos::sql($sql);
        $media_campos_data = count($campos_data);//Serve também p/ calcular a Média da Com. + abaixo no sql
        for($i = 0; $i < $media_campos_data; $i++) $id_vale_datas.= $campos_data[$i]['id_vale_data'].',';
        
//Caso encontrou pelo menos uma Data de Vale, então eu retiro o último caractér ',' ...
        if($media_campos_data > 0) $id_vale_datas = substr($id_vale_datas, 0, strlen($id_vale_datas) - 1);
        
//Aqui eu busco as comissões do Funcionário caso esse seja um Vendedor ...
        $sql = "SELECT (SUM(`comissao_alba`) / $media_campos_data) AS total_comissao_alba, 
                (SUM(`comissao_tool`) / $media_campos_data) AS total_comissao_tool, 
                (SUM(`comissao_grupo`) / $media_campos_data) AS total_comissao_grupo 
                FROM `funcionarios_vs_holeriths` 
                WHERE `id_funcionario` = '".$campos[0]['id_funcionario']."' 
                AND `id_vale_data` IN ($id_vale_datas) 
                GROUP BY id_funcionario ";
        $campos_comissao        = bancos::sql($sql);
        $total_comissao_alba    = $campos_comissao[0]['total_comissao_alba'];
        $total_comissao_tool    = $campos_comissao[0]['total_comissao_tool'];
        $total_comissao_grupo   = $campos_comissao[0]['total_comissao_grupo'];
        
/*Faço um somatório do Total de Vales em Empréstimo c/ a Data de Holerith anterior ao dia 5 do Próximo 
Mês que não estejam descontados do Funcionário do Loop ...*/
        $sql = "SELECT SUM(`valor`) AS total_vale_emprestimo 
                FROM `vales_dps` 
                WHERE `tipo_vale` = '8' 
                AND `id_funcionario` = '".$campos[0]['id_funcionario']."' 
                AND `data_debito` <= '$data_holerith_sql' 
                AND `descontar_pd_pf` = '".$campos[0]['descontar_pd_pf']."' 
                AND `descontado` = 'N' ";
        $campos_emprestimo  = bancos::sql($sql);
        
/*Faço um somatório do Total de Vales em Avulso c/ a Data de Holerith anterior ao dia 5 do Próximo Mês 
que não estejam descontados do Funcionário do Loop ...*/
        $sql = "SELECT SUM(valor) AS total_vale_avulso 
                FROM `vales_dps` 
                WHERE `tipo_vale` = '2' 
                AND `id_funcionario` = '".$campos[0]['id_funcionario']."' 
                AND `data_debito` <= '$data_holerith_sql' 
                AND `descontar_pd_pf` = '".$campos[0]['descontar_pd_pf']."' 
                AND `descontado` = 'N' ";
        $campos_avulso  = bancos::sql($sql);
        
        //Se o Funcionário está pelo 'Grupo' s/ Registro e o Tipo de Desconto do Vale = 'PD' ...
        if($campos_funcionario[0]['id_empresa'] == 4 && $campos[0]['descontar_pd_pf'] == 'PD') {
            $vlr_salario            = 0;//Isso não existe, porque o funcionário não tem Registro ...
            $total_comissoes        = 0;//Isso não existe ...
            $total_vale_emprestimo  = 0;//Isso não existe ...
            $total_vale_avulso      = 0;//Isso não existe ...
        //Se o Funcionário está pelo 'Grupo' s/ Registro e o Tipo de Desconto do Vale = 'PF' ...
        }else if($campos_funcionario[0]['id_empresa'] == 4 && $campos[0]['descontar_pd_pf'] == 'PF') {
            //Cálculo do Salário ...
            if($campos_funcionario[0]['tipo_salario'] == 1) {//Horista
                $vlr_salario = 220 * ($campos_funcionario[0]['salario_pd'] + $campos_funcionario[0]['salario_pf'] + $campos_funcionario[0]['salario_premio']);
            }else {//Mensalista
                $vlr_salario = $campos_funcionario[0]['salario_pd'] + $campos_funcionario[0]['salario_pf'] + $campos_funcionario[0]['salario_premio'];
            }
            $total_comissoes        = $total_comissao_alba + $total_comissao_tool + $total_comissao_grupo;
            $total_vale_emprestimo  = $campos_emprestimo[0]['total_vale_emprestimo'];
            $total_vale_avulso      = $campos_emprestimo[0]['total_vale_avulso'];
        //Se o Funcionário está pela 'Albafer ou Tool Master' c/ Registro e o Tipo de Desconto do Vale = 'PD' ...
        }else if($campos_funcionario[0]['id_empresa'] <> 4 && $campos[0]['descontar_pd_pf'] == 'PD') {
            //Cálculo do Salário ...
            if($campos_funcionario[0]['tipo_salario'] == 1) {//Horista
                $vlr_salario = 220 * ($campos_funcionario[0]['salario_pd']);
            }else {//Mensalista
                $vlr_salario = $campos_funcionario[0]['salario_pd'];
            }
            if($campos_funcionario[0]['id_empresa'] == 1) {//Funcionário da Empresa "Albafer" ...
                $total_comissoes = $total_comissao_alba;
            }else {//Funcionário da Empresa "Tool Master" ...
                $total_comissoes = $total_comissao_tool;
            }
            $total_vale_emprestimo  = $campos_emprestimo[0]['total_vale_emprestimo'];
            $total_vale_avulso      = $campos_emprestimo[0]['total_vale_avulso'];
        //Se o Funcionário está pela 'Albafer ou Tool Master' c/ Registro e o Tipo de Desconto do Vale = 'PF' ...
        }else if($campos_funcionario[0]['id_empresa'] <> 4 && $campos[0]['descontar_pd_pf'] == 'PF') {
            //Cálculo do Salário ...
            if($campos_funcionario[0]['tipo_salario'] == 1) {//Horista
                $vlr_salario = 220 * ($campos_funcionario[0]['salario_pf'] + $campos_funcionario[0]['salario_premio']);
            }else {//Mensalista
                $vlr_salario = $campos_funcionario[0]['salario_pf'] + $campos_funcionario[0]['salario_premio'];
            }
            $total_comissoes        = $total_comissao_grupo;
            $total_vale_emprestimo  = $campos_emprestimo[0]['total_vale_emprestimo'];
            $total_vale_avulso      = $campos_emprestimo[0]['total_vale_avulso'];
        }
//Junto do Salário eu ainda acrescento o Valor Total das Comissões ...
        $vlr_salario+= $total_comissoes;
//40 % do Salário ...
        $quarenta_perc_salario = 0.4 * $vlr_salario;
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Vlr Salário:</b>
        </td>
        <td>
            <input type='text' name='txt_vlr_salario' value="<?=number_format($vlr_salario, 2, ',', '.');?>" title='Valor do Salário' size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>40% Salário:</b>
        </td>
        <td>
            <input type='text' name='txt_40_perc_salario' value="<?=number_format($quarenta_perc_salario, 2, ',', '.');?>" title='40% do Salário' size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Vlr Empréstimo:</b>
        </td>
        <td>
            <input type='text' name='txt_vlr_emprestimo' value="<?=number_format($total_vale_emprestimo, 2, ',', '.');?>" title='Valor Empréstimo' size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Vlr Avulso:</b>
        </td>
        <td>
            <input type='text' name='txt_vlr_avulso' value="<?=number_format($total_vale_avulso, 2, ',', '.');?>" title='Valor Avulso' size="12" maxlength="10" class='textdisabled' disabled>
        </td>
    </tr>
    <?
        $vlr_direito = $quarenta_perc_salario - $total_vale_avulso;
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Vlr Direito:</b>
        </td>
        <td>
            <input type='text' name='txt_vlr_direito' value="<?=number_format($vlr_direito, 2, ',', '.');?>" title='Valor Direito' size="12" maxlength="10" class='textdisabled' disabled>
            <font color='red'>
                <b>(Não leva em conta o Vlr Empréstimo)</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Vlr Acima Limite:</b>
        </td>
        <td>
            <input type='text' name='txt_vlr_acima_limite' value="<?=number_format($campos[0]['valor_acima_limite'], 2, ',', '.');?>" title='Digite o Valor do Pedido' size="12" maxlength="10" onkeyup="verifica(this, 'moeda_especial', '2', '', event);cop_vlr_acima_limite()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Vlr Liberado:</b>
        </td>
        <td>
            <input type='text' name='txt_vlr_liberado' value="<?=number_format($campos[0]['valor'], 2, ',', '.');?>" title='Valor Liberado' size="12" maxlength="10" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_vlr_liberado.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* O valor do Salário = Salário + Prêmio + Média Comissão dos últimos 2 meses.
* Se Empresa = 'GRUPO' e Descontar = 'PD' o Vlr Direito tem de vir zerado.
* Se Empresa = 'GRUPO' e Descontar = 'PF' traz todos os Valores PD + PF.
* Se Empresa <> 'GRUPO' e Descontar = 'PD' traz somente os Valores PD.
* Se Empresa <> 'GRUPO' e Descontar = 'PF' traz somente os Valores PF.

Faltou analisarmos se na opção de Gerar "Vale do Dia 20" os cálculos estão desta forma.
</pre>