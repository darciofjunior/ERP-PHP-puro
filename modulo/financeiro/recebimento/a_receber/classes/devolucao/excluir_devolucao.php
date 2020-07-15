<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/comunicacao.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/faturamentos.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/variaveis/intermodular.php');
session_start('funcionarios');

if($id_emp2 == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp2 == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp2 == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>DEVOLUÇÃO EXCLUÍDA COM SUCESSO.</font>";

if($passo == 1) {
//////////////////////// Tratamentos para não furar o SQL ///////////////////////////
    if(empty($cmb_representante))   $cmb_representante = '%'; 
    if($cmb_tipo_lancamento == '')  $cmb_tipo_lancamento = '%';
/*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
    if($hidden_tipo_lancamento == 1) {//Tipo de Lançamento = 'DEVOLUÇÃO DE CANCELAMENTO'
        $cmb_tipo_lancamento = 0;
    }else if($hidden_tipo_lancamento == 2) {//Tipo de Lançamento = 'ATRASO DE PAGAMENTO'
        $cmb_tipo_lancamento = 1;
    }else if($hidden_tipo_lancamento == 3) {//Tipo de Lançamento = 'ABATIMENTO / DIF. PREÇOS'
        $cmb_tipo_lancamento = 2;
    }else if($hidden_tipo_lancamento == 4) {//Tipo de Lançamento = 'REEMBOLSO'
        $cmb_tipo_lancamento = 3;
    }else if($hidden_tipo_lancamento == 5) {//Tipo de Lançamento = 'NF DE ENTRADA'
        $cmb_tipo_lancamento = 4;
    }else {//Independente da Operação de Custo
        if($cmb_tipo_lancamento == '')  $cmb_tipo_lancamento = '%';
    }

    if(!empty($txt_data_lancamento)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
        if(substr($txt_data_lancamento, 4, 1) != '-') $txt_data_lancamento = data::datatodate($txt_data_lancamento, '-');
    }
//Só Lista as NFs de Devolução da Empresa Corrente -> $id_emp2 passado por parâmetro ...
    $sql = "SELECT ce.* 
            FROM `comissoes_estornos` ce 
            INNER JOIN `nfs` ON nfs.`id_nf` = ce.`id_nf` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND c.`razaosocial` LIKE '%$txt_cliente%' 
            INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` AND nnn.`numero_nf` LIKE '%$txt_nnf_devolvida%' 
            WHERE nfs.`id_empresa` = '$id_emp2' 
            AND ce.`id_representante` LIKE '$cmb_representante' 
            AND SUBSTRING(ce.`data_lancamento`, 1, 10) LIKE '%$txt_data_lancamento%' 
            AND ce.`tipo_lancamento` LIKE '$cmb_tipo_lancamento' 
            ORDER BY ce.data_lancamento DESC ";
    $campos = bancos::sql($sql, $inicio, 25, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'excluir_devolucao.php?id_emp2=<?=$id_emp2;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Excluir Abatimento / Dif. Preços ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
//Observação / Justificativa ...
        if(document.form.txt_observacao_justificativa.value == '') {
            alert('DIGITE A OBSERVAÇÃO / JUSTIFICATIVA !')
            document.form.txt_observacao_justificativa.focus()
            document.form.txt_observacao_justificativa.select()
            return false
        }
//Mensagem verificando se o Pedido realmente pode ser excluído ...
        var mensagem = confirm('CONFIRMA A EXCLUSÃO ?')
        if(!mensagem == true) return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<table width='98%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Excluir Abatimento(s) / Dif. Preço(s)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'> 
        <td>
            N.º da <br/>NNF Devolvida
        </td>
        <td>
            Tipo de Lançamento
        </td>
        <td>
            Cliente
        </td>
        <td>
            Representante
        </td>
        <td>
            % Comissão
        </td>
        <td>
            N.º da <br/>SNF à Devolver
        </td>
        <td>
            Valor S/ IPI
        </td>
        <td>
            Data de <br/>Lançamento
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_lancamento'] == 0) {
                echo 'DEVOLUÇÃO DE CANCELAMENTO';
            }else if($campos[$i]['tipo_lancamento'] == 1) {
                echo 'ATRASO DE PAGAMENTO';
            }else if($campos[$i]['tipo_lancamento'] == 2) {
                echo 'ABATIMENTO / DIF. PREÇOS';
            }else if($campos[$i]['tipo_lancamento'] == 3) {
                echo 'REEMBOLSO';
            }else if($campos[$i]['tipo_lancamento'] == 4) {
                echo 'NF DE ENTRADA';
            }
        ?>
        </td>
        <td align='left'>
        <?
            //Busca do Nome do Cliente da Nota que está sendo Devolvida ...
            $sql = "SELECT IF(c.nomefantasia = '', c.razaosocial, c.nomefantasia) AS cliente 
                    FROM `nfs` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                    WHERE nfs.`id_nf` = '".$campos[$i]['id_nf']."' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            echo $campos_cliente[0]['cliente'];
        ?>
        </td>
        <td>
        <?
            //Busca do Nome do Representante ...
            $sql = "SELECT nome_fantasia 
                    FROM `representantes` 
                    WHERE `id_representante` = '".$campos[$i]['id_representante']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            echo $campos_representante[0]['nome_fantasia'];
        ?>
        </td>
        <td>
            <?=number_format($campos[$i]['porc_devolucao'], 2, ',', '.');?>
        </td>
        <td>
        <?
            if($campos[$i]['num_nf_devolvida'] == 0) {
                echo ' - ';
            }else {
                echo $campos[$i]['num_nf_devolvida'];
            }
        ?>
        </td>
        <td align='right'>
            <?='R$ '.segurancas::number_format($campos[$i]['valor_duplicata'], 2, '.');?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_lancamento'], 0, 10), '/').' '.substr($campos[$i]['data_lancamento'], 11, 8);?>
        </td>
        <td>
        <?
/*Se o Tipo de Lançamento for = DEVOLUÇÃO, ATRASO DE PAGAMENTO ou REEMBOLSO, então não se traz o checkbox 
p/ excluir a Nota de Devolução*/
            if($campos[$i]['tipo_lancamento'] == 0 || $campos[$i]['tipo_lancamento'] == 1 || $campos[$i]['tipo_lancamento'] == 3) {
        ?>
                <input type='hidden' name='hdd_comissao_estorno[]' value='<?=$campos[$i]['id_comissao_estorno'];?>'>
        <?
            }else {
        ?>
                <input type='checkbox' name='chkt_comissao_estorno[]' value='<?=$campos[$i]['id_comissao_estorno'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        <?
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Observação / Justificativa:</b>
        </td>
        <td colspan='7'>
            <textarea name='txt_observacao_justificativa' cols='60' rows='2' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'opcoes_devolucao.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    foreach($_POST['chkt_comissao_estorno'] as $id_comissao_estorno) {
//1)
/************************Busca de Dados************************/
//Busca de alguns dados p/ passar por e-mail mais abaixo ...
        $sql = "SELECT nfs.id_empresa, c.razaosocial 
                FROM `nfs` 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                INNER JOIN `comissoes_estornos` ce ON ce.`id_nf` = nfs.`id_nf` 
                WHERE ce.`id_comissao_estorno` = '$id_comissao_estorno' LIMIT 1 ";
        $campos_nf          = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa ...
        $id_empresa_nota    = $campos_nf[0]['id_empresa'];
        $empresa            = genericas::nome_empresa($id_empresa_nota);
        $cliente            = $campos_nf[0]['razaosocial'];
        $numero_nf          = faturamentos::buscar_numero_nf($campos_nf[0]['id_empresa'], 'S');
/**********************************Excluindo os Campos na Base de Dados**********************************/
        $sql = "DELETE FROM `comissoes_estornos` WHERE `id_comissao_estorno` = '$id_comissao_estorno' LIMIT 1 ";
        bancos::sql($sql);
/**********************************************************************************************************/
//Dados p/ enviar por e-mail ...
        $complemento_justificativa.= '<b>Empresa: </b>'.$empresa.' / <b>Cliente: </b>'.$cliente.' / <b>N.º da Conta: </b>'.$numero_nf.'<br>';
    }
//2)
/************************E-mail************************/
/*
//-Se o Usuário estiver excluindo a Nota Fiscal de Compras, então o Sistema dispara um e-mail informando 
qual a Nota Fiscal que está sendo excluída ...
//-Aqui eu trago alguns dados de Nota Fiscal p/ passar por e-mail via parâmetro ...
//-Aqui eu busco o login de quem está excluindo a Nota Fiscal ...*/
    $sql = "SELECT login 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos_login       = bancos::sql($sql);
    $login_excluindo    = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
    $justificativa      = $complemento_justificativa.' <br><b>Login: </b>'.$login_excluindo.' - <b>Data e Hora de Exclusão: </b> '.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$_POST['txt_observacao_justificativa'].'<br>'.$PHP_SELF;
/***********************************E-mail***********************************/
//Aqui eu mando um e-mail informando quem e porque que exclui a Conta à Receber ...
    $destino = $excluir_contas_devolucao;
    $assunto = 'Exclusão de NF Devolução '.date('d/m/Y H:i:s');
    $mensagem = $justificativa;
    comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', $assunto, $mensagem);
?>
    <Script Language = 'JavaScript'>
        window.location = 'excluir_devolucao.php<?=$parametro;?>&id_emp2=<?=$id_emp2;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Excluir Devolução ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
//Controle com o Tipo de Lançamento
function controle_tipo_lancamento() {
    var tipo_lancamento = document.form.cmb_tipo_lancamento[document.form.cmb_tipo_lancamento.selectedIndex].text
//Se não estiver selecionada nenhum Tipo de Lançamento
    if(tipo_lancamento == 'SELECIONE') {
        document.form.hidden_tipo_lancamento.value = ''
    }else if(tipo_lancamento == 'DEVOLUÇÃO DE CANCELAMENTO') {
        document.form.hidden_tipo_lancamento.value = 1
    }else if(tipo_lancamento == 'ATRASO DE PAGAMENTO') {
        document.form.hidden_tipo_lancamento.value = 2
    }else if(tipo_lancamento == 'ABATIMENTO / DIF. PREÇOS') {
        document.form.hidden_tipo_lancamento.value = 3
    }else if(tipo_lancamento == 'REEMBOLSO') {
        document.form.hidden_tipo_lancamento.value = 4
    }else if(tipo_lancamento == 'NF DE ENTRADA') {
        document.form.hidden_tipo_lancamento.value = 5
    }
}
</Script>
</head>
<body onload='document.form.txt_nnf_devolvida.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<!--**********************Gambiarra**********************
/*Aqui eu tive que fazer essa adaptação, porque estava dando erro de parâmetro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro lá no outro
passo da consulta*/
-->
<input type='hidden' name='hidden_tipo_lancamento'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Excluir Devolução
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º da NNF Devolvida
        </td>
        <td>
            <input type='text' name="txt_nnf_devolvida" title="Digite o N.º da NNF Devolvida" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type='text' name="txt_cliente" title="Digite o Cliente" size="40" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Lançamento
        </td>
        <td>
            <input type='text' name='txt_data_lancamento' title='Digite a Data de Lançamento' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_lancamento&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> Calendário
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
            Tipo de Lançamento
        </td>
        <td>
            <select name='cmb_tipo_lancamento' title='Selecione o Tipo de Lançamento' onchange='controle_tipo_lancamento()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0'>DEVOLUÇÃO DE CANCELAMENTO</option>
                <option value='1'>ATRASO DE PAGAMENTO</option>
                <option value='2'>ABATIMENTO / DIF. PREÇOS</option>
                <option value='3'>REEMBOLSO</option>
                <option value='4'>NF DE ENTRADA</option>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'opcoes_devolucao.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_nnf_devolvida.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<b><font color="red">Observação:</font></b>
<pre>
* Não podem ser excluída(s) a(s) Devolução(ões) do Tipo "ATRASO DE PAGAMENTO" ou "REEMBOLSO".
</pre>