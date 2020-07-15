<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/faturamentos.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/variaveis/intermodular.php');
require('../../../../../classes/array_sistema/array_sistema.php');
session_start('funcionarios');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ABATIMENTO / DIF. PRE�OS ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
//////////////////////// Tratamentos para n�o furar o SQL ///////////////////////////
    if(empty($cmb_representante))   $cmb_representante = '%';
    if($cmb_tipo_lancamento == '')  $cmb_tipo_lancamento = '%';
/*Aqui eu tive que fazer essa adapta��o, porque estava dando erro de par�metro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro*/
    if($hidden_tipo_lancamento == 1) {//Tipo de Lan�amento = 'DEVOLU��O DE CANCELAMENTO'
        $cmb_tipo_lancamento = 0;
    }else if($hidden_tipo_lancamento == 2) {//Tipo de Lan�amento = 'ATRASO DE PAGAMENTO'
        $cmb_tipo_lancamento = 1;
    }else if($hidden_tipo_lancamento == 3) {//Tipo de Lan�amento = 'ABATIMENTO / DIF. PRE�OS'
        $cmb_tipo_lancamento = 2;
    }else if($hidden_tipo_lancamento == 4) {//Tipo de Lan�amento = 'REEMBOLSO'
        $cmb_tipo_lancamento = 3;
    }else if($hidden_tipo_lancamento == 5) {//Tipo de Lan�amento = 'NF DE ENTRADA'
        $cmb_tipo_lancamento = 4;
    }

    if(!empty($txt_data_lancamento)) {
//Aqui verifica se a Data est� no formato Americano p/ n�o ter que fazer o Tratamento Novamente
        if(substr($txt_data_lancamento, 4, 1) != '-') $txt_data_lancamento = data::datatodate($txt_data_lancamento, '-');
    }
//S� Lista os Estornos de Comiss�o da Empresa Corrente -> $id_emp2 passado por par�metro ...
    $sql = "SELECT ce.*, DATE_FORMAT(cr.`data_vencimento_alterada`, '%d/%m/%Y') AS data_vencimento_alterada 
            FROM `comissoes_estornos` ce
            INNER JOIN `contas_receberes` cr ON cr.id_conta_receber = ce.id_conta_receber AND cr.`num_conta` LIKE '$txt_numero_conta%'
            INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente AND c.`razaosocial` LIKE '%$txt_cliente%'
            WHERE ce.`id_representante` LIKE '$cmb_representante'
            AND SUBSTRING(ce.`data_lancamento`, 1, 10) LIKE '%$txt_data_lancamento%'
            AND ce.tipo_lancamento LIKE '$cmb_tipo_lancamento'
            ORDER BY ce.id_comissao_estorno DESC ";
    $campos = bancos::sql($sql, $inicio, 25, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'alterar_devolucao.php?id_emp2=<?=$id_emp2;?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Alterar Abatimento / Dif. Pre�os ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' action="<?=$PHP_SELF.'?passo=2';?>" method='post'>
<table width='95%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Altera Abatimento / Dif. Pre�os 
            <?=genericas::nome_empresa($id_emp2);?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.� da <br/>NNF Baseada
        </td>
        <td>
            Tipo de Lan�amento
        </td>
        <td>
            Cliente
        </td>
        <td>
            Representante
        </td>
        <td>
            % Comiss�o
        </td>
        <td>
            N.� da <br/>SNF � Devolver
        </td>
        <td>
            Valor S/ IPI
        </td>
        <td>
            Data de <br/>Lan�amento
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
//Se o Tipo de Lan�amento for = DEVOLU��O, ATRASO DE PAGAMENTO ou REEMBOLSO, ent�o n�o se pode alterar a NF de Devolu��o
            if($campos[$i]['tipo_lancamento'] == 0 || $campos[$i]['tipo_lancamento'] == 1 || $campos[$i]['tipo_lancamento'] == 3) {
                $url = "alert('N�O PODE SER ALTERADO ESTE TIPO DE NOTA !') ";
            }else {
                $url = "window.location = 'alterar_devolucao.php?passo=2&id_emp2=".$id_emp2."&id_comissao_estorno=".$campos[$i]['id_comissao_estorno']."'";
            }
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');<?=$url;?>" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <a href='#'>
                <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td>
            <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_lancamento'] == 0) {
                echo 'DEVOLU��O DE CANCELAMENTO';
            }else if($campos[$i]['tipo_lancamento'] == 1) {
                echo 'ATRASO DE PAGAMENTO';
            }else if($campos[$i]['tipo_lancamento'] == 2) {
                echo 'ABATIMENTO / DIF. PRE�OS';
            }else if($campos[$i]['tipo_lancamento'] == 3) {
                echo 'REEMBOLSO';
            }else if($campos[$i]['tipo_lancamento'] == 4) {
                echo 'NF DE ENTRADA';
            }
        ?>
        </td>
        <td align='left'>
        <?
            //Busca do Nome do Cliente da Nota que est� sendo Baseada ...
            $sql = "SELECT IF(c.`nomefantasia` = '', c.`razaosocial`, c.`nomefantasia`) AS cliente 
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
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar_devolucao.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
//Vou utilizar essas datas p/ fazer algumas compara��es com a Data de Emiss�o ...
    $datas          = genericas::retornar_data_relatorio(1);
    $data_inicial   = data::datatodate($datas['data_inicial'], '-');
    $data_final     = data::datatodate($datas['data_final'], '-');

//Busca de Dados da NF de Devolu��o Corrente ...
    $sql = "SELECT ce.*, nfs.`data_emissao` 
            FROM `comissoes_estornos` ce 
            INNER JOIN `nfs` ON nfs.`id_nf` = ce.`id_nf` 
            WHERE ce.`id_comissao_estorno` = '$id_comissao_estorno' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $data_emissao   = $campos[0]['data_emissao'];
    $data_icms      = date('Y-m-').'01';//Sempre � o dia 1 do M�s corrente ...
?>
<html>
<head>
<title>.:: Alterar Abatimento / Dif. Pre�os ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var id_emp2         = eval('<?=$id_emp2;?>')
    var data_emissao    = '<?=$data_emissao;?>'
    var data_final      = '<?=$data_final;?>'
    var data_icms       = '<?=$data_icms;?>'

    data_emissao        = data_emissao.substr(0,4)+data_emissao.substr(5,2)+data_emissao.substr(8,2)
    data_final          = data_final.substr(0,4)+data_final.substr(5,2)+data_final.substr(8,2)
    data_icms           = data_icms.substr(0,4)+data_icms.substr(5,2)+data_icms.substr(8,2)

    data_emissao        = eval(data_emissao)
    data_final          = eval(data_final)
    data_icms           = eval(data_icms)

//Representante
    if(!combo('form', 'cmb_representante', '', 'SELECIONE UM REPRESENTANTE !')) {
        return false
    }
//Tipo de Lan�amento
    if(!combo('form', 'cmb_tipo_lancamento', '', 'SELECIONE UM TIPO DE LAN�AMENTO !')) {
        return false
    }
//Se a Empresa for Albafer ou Tool Master, eu for�o o preenhcimento do campo de N.� de SNF � Devolver ...
    if(id_emp2 == 1 || id_emp2 == 2) {
//N.� da SNF � Devolver
        if(!texto('form', 'txt_num_nf_devolver', '1', '1234567890', 'N.� DA SNF � DEVOLVER', '2')) {
            return false
        }
    }
//Valor S/ IPI
    if(!texto('form', 'txt_valor_sem_ipi', '1', '1234567890,.', 'VALOR SEM IPI', '2')) {
        return false
    }
/*Aqui eu fa�o essa verifica��o p/ ver se realmente vai ser necess�rio estar preenchendo o Valor de 
Porcentagem da Comiss�o - Ser� necess�rio o preenchimento sempre que a Data de Emiss�o for menor que 
a Data Final de Comiss�o que geralmente � sempre � o dia vai at� o dia 25 de cada m�s, ou seja o �ltimo
dia p/ fechamento da Folha*/
    if(data_emissao <= data_final) {
//Se o Tipo de Devolu��o for Parcial, ent�o eu for�o o preenchimento do campo de Porcentagem da Comiss�o ...
        //if(document.form.cmb_tipo_devolucao.value == 0) {
//Porcentagem da Comiss�o
            if(!texto('form', 'txt_porc_comissao', '1', '1234567890,.', 'VALOR DE PORCENTAGEM DA COMISS�O', '2')) {
                return false
            }
//Se a Porcentagem da Comiss�o = 0, ent�o tem que obrigar a colocar outro valor ...
            if(document.form.txt_porc_comissao.value == '0,00') {
                alert('PORCENTAGEM DA COMISS�O INV�LIDA !')
                document.form.txt_porc_comissao.focus()
                document.form.txt_porc_comissao.select()
                return false
            }
        //}
    }
//Observa��o / Justificativa ...
    if(document.form.txt_observacao_justificativa.value == '') {
        alert('DIGITE A OBSERVA��O / JUSTIFICATIVA !')
        document.form.txt_observacao_justificativa.focus()
        document.form.txt_observacao_justificativa.select()
        return false
    }
/*Aqui eu destravo esse campo para poder no BD - isso vai servir para o caso de Grupo em que esse campo
vem travado*/
    document.form.txt_num_nf_devolver.disabled = false
//Prepara no formato em que eu posso ler no banco ...
    return limpeza_moeda('form', 'txt_valor_sem_ipi, txt_porc_comissao, ')
}

function calcular() {
//Valor Sem IPI
    var valor_sem_ipi = (document.form.txt_valor_sem_ipi.value == '') ? 0 : eval(strtofloat(document.form.txt_valor_sem_ipi.value))
//Porcentagem Comiss�o ...
    var porc_comissao = (document.form.txt_porc_comissao.value == '') ? 0 : eval(strtofloat(document.form.txt_porc_comissao.value))
//Valor de Devolu��o de Comiss�o ...
    document.form.txt_valor_devolucao_comissao.value    = (valor_sem_ipi * porc_comissao) / 100
    document.form.txt_valor_devolucao_comissao.value    = arred(document.form.txt_valor_devolucao_comissao.value, 2, 1)
}

function iniciar() {
    var id_emp2 = eval('<?=$id_emp2;?>')
    if(id_emp2 == 1 || id_emp2 == 2) {//Se a Empresa for Albafer ou Tool Master, o campo de N.� de NF � Devolver vem destravado ...
        document.form.txt_num_nf_devolver.focus()
    }else {//Quando a Empresa for Grupo, o campo de N.� de NF � Devolver vem travado ...
        document.form.txt_valor_sem_ipi.focus()
    }
}

function habilitar_comissao() {
    if(document.form.cmb_tipo_lancamento.value == '') {//Deixa desabilitado ...
        document.form.txt_porc_comissao.className   = 'textdisabled'
        document.form.txt_porc_comissao.disabled    = true
        document.form.txt_porc_comissao.value       = ''
    }else {//Habilita sempre que tiver algum Tipo de Lan�amento selecionado ...
        document.form.txt_porc_comissao.className   = 'caixadetexto'
        document.form.txt_porc_comissao.disabled    = false
        document.form.txt_porc_comissao.value       = '<?=number_format($campos[0]['porc_devolucao'], 2, ',', '.');?>'
        document.form.txt_porc_comissao.focus()
    }
}
</Script>
</head>
<body onload='habilitar_comissao();iniciar()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<input type='hidden' name='id_comissao_estorno' value='<?=$id_comissao_estorno;?>'>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Abatimento / Dif. Pre�os para <?=genericas::nome_empresa($id_emp2);?>
        </td>
    </tr>
    <tr class='linhanormal'> 
        <td width='30%'>
            <b>N.� da NNF Baseada:</b>
        </td>
        <td>
            <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Emiss�o da NNF Baseada:</b>
        </td>
        <td>
            <?=data::datetodata($data_emissao, '/');?>
        </td>
    </tr>
    <tr class='linhanormal'> 
        <td>
            <b>Cliente:</b>
        </td>
        <td>
        <?
            //Busca do Nome do Cliente da Nota que est� sendo Baseada ...
            $sql = "SELECT c.id_cliente, c.razaosocial 
                    FROM `nfs` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                    WHERE nfs.`id_nf` = '".$campos[0]['id_nf']."' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            $id_cliente     = $campos_cliente[0]['id_cliente'];//Vou utilizar essa vari�vel + abaixo ...
            echo $campos_cliente[0]['razaosocial'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'> 
        <td>
            <font color='darkblue'>
                <b>Representante:</b>
            </font>
        </td>
        <td>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?						
                //Aqui eu busco todos representantes diretamente da Nota Fiscal ...
                $sql = "SELECT DISTINCT(r.`id_representante`), CONCAT(r.`nome_fantasia`, ' / ', r.`zona_atuacao`) AS dados 
                        FROM `representantes` r 
                        INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = '".$campos[0]['id_nf']."' AND nfsi.`id_representante` = r.`id_representante` 
                        WHERE r.`ativo` = '1' ORDER BY r.`nome_fantasia` ";
                $campos_representante = bancos::sql($sql);
                //Caso eu n�o encontre algum, ent�o eu busco todos os representantes daquele Cliente ...
                if(count($campos_representante) == 0) {
                    $sql = "SELECT DISTINCT(r.`id_representante`), CONCAT(r.`nome_fantasia`, ' / ', r.`zona_atuacao`) AS dados 
                            FROM `representantes` r 
                            INNER JOIN `clientes_vs_representantes` cr ON cr.`id_cliente` = '$id_cliente' AND cr.`id_representante` = r.`id_representante` 
                            WHERE r.`ativo` = '1' ORDER BY r.`nome_fantasia` ";
                }
                echo combos::combo($sql, $campos[0]['id_representante']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'> 
        <td>
            <font color='darkblue'>
                <b>Tipo de Lan�amento:</b>
            </font>
        </td>
        <td>
            <select name='cmb_tipo_lancamento' title='Selecione o Tipo de Lan�amento' onchange='habilitar_comissao()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($campos[0]['tipo_lancamento'] == 0) {
                        $selected0 = 'selected';
                    }else if($campos[0]['tipo_lancamento'] == 1) {
                        $selected1 = 'selected';
                    }else if($campos[0]['tipo_lancamento'] == 2) {
                        $selected2 = 'selected';
                    }else if($campos[0]['tipo_lancamento'] == 3) {
                        $selected3 = 'selected';
                    }else if($campos[0]['tipo_lancamento'] == 4) {
                        $selected4 = 'selected';
                    }
                ?>
                <option value='0' <?=$selected0;?>>DEVOLU��O DE CANCELAMENTO</option>
                <!--<option value='1' <?=$selected1;?>>ATRASO DE PAGAMENTO</option>-->
                <option value='2' <?=$selected2;?>>ABATIMENTO / DIF. PRE�OS</option>
                <!--<option value='3' <?=$selected3;?>>REEMBOLSO</option>-->
                <!--<option value='4' <?=$selected4;?>>NF DE ENTRADA</option>-->
            </select>
        </td>
    </tr>
    <!--<tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Tipo de Devolu��o:</b>
            </font>
        </td>
        <td>
            <select name='cmb_tipo_devolucao' title='Selecione o Tipo de Devolu��o' onchange='habilitar_comissao()' class='textdisabled' disabled>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($campos[0]['porc_devolucao'] != 0) {
                ?>
                <option value='0' selected>PARCIAL</option>
                <option value='1'>TOTAL</option>
                <?
                    }else {
                ?>
                <option value='0'>PARCIAL</option>
                <option value='1' selected>TOTAL</option>
                <?
                    }
                ?>
            </select>
        </td>
    </tr>-->
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>N.� da SNF � Devolver:</b>
            </font>
        </td>
        <?
            //Se a Empresa for Albafer ou Tool Master, o campo de N.� de SNF � Devolver vem destravado ...
            if($id_emp2 == 1 || $id_emp2 == 2) {
                $class              = 'caixadetexto';
                $disabled           = '';
                $num_nf_devolver    = $campos[0]['num_nf_devolvida'];
            }else {//Quando a Empresa for Grupo, o campo de N.� de NF � Devolver vem travado ...
                $class              = 'disabled';
                $disabled           = 'disabled';
                $num_nf_devolver    = '';
            }
        ?>
        <td>
            <input type='text' name='txt_num_nf_devolver' value='<?=$num_nf_devolver;?>' title='Digite o N.� de NF � Devolver' size='12' maxlength="10" onKeyUp="verifica(this, 'aceita', 'numeros', '', event)" class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Valor S/ IPI:</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_valor_sem_ipi' value='<?=number_format($campos[0]['valor_duplicata'], 2, ',', '.');?>' title='Digite o Valor Sem IPI' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                % Comiss�o: <font color='green'>*</font>
            </font>
        </td>
        <td>
            <input type='text' name='txt_porc_comissao' value='<?=number_format($campos[0]['porc_devolucao'], 2, ',', '.');?>' title='Digite a % Comiss�o' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Valor do Abatimento / NF Entrada:</b>
            </font>
        </td>
        <td>
        <?
            $valor_devolucao_comissao = ($campos[0]['valor_duplicata'] * $campos[0]['porc_devolucao']) / 100;
        ?>
            <input type='text' name='txt_valor_devolucao_comissao' value='<?=number_format($valor_devolucao_comissao, 2, ',', '.');?>' title='Valor de Devolu��o da Comiss�o' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observa��o / Justificativa:</b>
        </td>
        <td>
            <textarea name='txt_observacao_justificativa' cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar_devolucao.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');iniciar()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='return fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observa��o:</font></b>
<pre>
<font color='green'>* </font>Ser� necess�rio o preenchimento do campo "% Comiss�o", sempre que a Data de Emiss�o for menor que a Data 
Final de Comiss�o. Geralmente esta data vai at� o dia 25 de cada m�s, ou seja o �ltimo dia p/ fechamento da Folha

<font color='green'>** </font>Ser� necess�rio o preenchimento do campo "ICMS � Creditar", sempre que a Data de Emiss�o for menor 
que o dia 1 do M�s Corrente
</pre>
<?
}else if($passo == 3) {
//1)
/************************Busca de Dados************************/
//Aqui eu trago alguns dados de Nota Fiscal p/ passar por e-mail via par�metro ...
    $sql = "SELECT nfs.id_nf, nfs.id_empresa, c.razaosocial 
            FROM `comissoes_estornos` ce 
            INNER JOIN `nfs` ON nfs.`id_nf` = ce.`id_nf` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            WHERE ce.`id_comissao_estorno` = '$_POST[id_comissao_estorno]' LIMIT 1 ";
    $campos_nf          = bancos::sql($sql);
//Coloquei esse nome na vari�vel porque na sess�o j� existe uma vari�vel com o nome de id_empresa ...
    $id_empresa_nota    = $campos_nf[0]['id_empresa'];
    $empresa            = genericas::nome_empresa($id_empresa_nota);
    $cliente            = $campos_nf[0]['razaosocial'];
    $numero_nf          = faturamentos::buscar_numero_nf($campos_nf[0]['id_nf'], 'S');
//2)
/************************E-mail************************/
/*
//-Se o Usu�rio estiver alterando a Nota Fiscal de Compras, ent�o o Sistema dispara um e-mail informando 
qual a Nota Fiscal que est� sendo exclu�da ...
//-Aqui eu trago alguns dados de Nota Fiscal p/ passar por e-mail via par�metro ...
//-Aqui eu busco o login de quem est� alterando a Nota Fiscal ...*/
    $sql = "SELECT login 
            FROM `logins` 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos_login       = bancos::sql($sql);
    $login_alterando    = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
    $complemento_justificativa = '<br><b>Empresa: </b>'.$empresa.' <br><b>Cliente: </b>'.$cliente.' <br><b>N.� da Conta: </b>'.$numero_nf.'<br>';
    $txt_justificativa = $complemento_justificativa.' <br><b>Login: </b>'.$login_alterando.' - <b>Data e Hora de Altera��o: </b> '.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$txt_observacao_justificativa.'<br>'.$PHP_SELF;
/***********************************E-mail***********************************/
//Aqui eu mando um e-mail informando quem e porque que exclui a Conta � Receber ...
    $destino    = $alterar_contas_devolucao;
    $assunto    = 'Altera��o de NF Abatimento / Dif. Pre�os '.date('d/m/Y H:i:s');
    $mensagem   = $txt_justificativa;
    $data_sys   = date('Y-m-d H:i:s');
//Alterando a Nota de Devolu��o ...
    $sql = "UPDATE `comissoes_estornos` SET `id_representante` = '$cmb_representante', `num_nf_devolvida` = '$txt_num_nf_devolver', `data_lancamento` = '$data_sys', `tipo_lancamento` = '$cmb_tipo_lancamento', `porc_devolucao` = '$txt_porc_comissao', `valor_duplicata` = '$txt_valor_sem_ipi' WHERE `id_comissao_estorno` = '$_POST[id_comissao_estorno]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar_devolucao.php<?=$parametro;?>&id_emp2=<?=$id_emp2;?>&valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Alterar Abatimento / Dif. Pre�os ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
//Controle com o Tipo de Lan�amento
function controle_tipo_lancamento() {
    var tipo_lancamento = document.form.cmb_tipo_lancamento[document.form.cmb_tipo_lancamento.selectedIndex].text
//Se n�o estiver selecionada nenhum Tipo de Lan�amento
    if(tipo_lancamento == 'SELECIONE') {
        document.form.hidden_tipo_lancamento.value = ''
    }else if(tipo_lancamento == 'DEVOLU��O DE CANCELAMENTO') {
        document.form.hidden_tipo_lancamento.value = 1
    }else if(tipo_lancamento == 'ATRASO DE PAGAMENTO') {
        document.form.hidden_tipo_lancamento.value = 2
    }else if(tipo_lancamento == 'ABATIMENTO / DIF. PRE�OS') {
        document.form.hidden_tipo_lancamento.value = 3
    }else if(tipo_lancamento == 'REEMBOLSO') {
        document.form.hidden_tipo_lancamento.value = 4
    }else if(tipo_lancamento == 'NF DE ENTRADA') {
        document.form.hidden_tipo_lancamento.value = 5
    }
}
</Script>
</head>
<body onload='document.form.txt_nnf_baseada.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='id_emp2' value='<?=$id_emp2;?>'>
<!--**********************Gambiarra**********************
/*Aqui eu tive que fazer essa adapta��o, porque estava dando erro de par�metro por causa que a Combo
armazena um dos valores como sendo zero, e devido a isso, eu estava perdendo todo o Filtro l� no outro
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
            Alterar Devolu��o(�es)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp2);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.� da NNF Baseada
        </td>
        <td>
            <input type='text' name='txt_nnf_baseada' title='Digite o N.� da NNF Baseada' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type='text' name='txt_cliente' title='Digite o Cliente' size='40' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Lan�amento
        </td>
        <td>
            <input type='text' name='txt_data_lancamento' title='Digite a Data de Lan�amento' size='12' maxlength='10' onKeyUp="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            &nbsp;<img src = '../../../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../../../calendario/calendario.php?campo=txt_data_lancamento&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')"> Calend�rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Representante
        </td>
        <td>
            <select name='cmb_representante' title='Selecione o Representante' class='combo'>
            <?
                $sql = "SELECT `id_representante`, `nome_fantasia` 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY `nome_fantasia` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Tipo de Lan�amento
        </td>
        <td>
            <select name='cmb_tipo_lancamento' title='Selecione o Tipo de Lan�amento' onchange='controle_tipo_lancamento()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0'>DEVOLU��O DE CANCELAMENTO</option>
                <option value='1'>ATRASO DE PAGAMENTO</option>
                <option value='2'>ABATIMENTO / DIF. PRE�OS</option>
                <option value='3'>REEMBOLSO</option>
                <option value='4'>NF DE ENTRADA</option>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'opcoes_devolucao.php?id_emp2=<?=$id_emp2;?>'" class='botao'>
            <input type='reset' name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.txt_nnf_baseada.focus()" style="color:#ff9900" class='botao'>
            <input type='submit' name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<b><font color="red">Observa��o:</font></b>
<pre>
* N�o podem ser alterada(s) a(s) Devolu��o(�es) do Tipo "ATRASO DE PAGAMENTO" ou "REEMBOLSO".
</pre>