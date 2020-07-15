<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
require('../../../../lib/variaveis/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='confirmacao'>NOTA EXCLUIDA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>ESTA NOTA N�O PODE SER EXCLU�DA !!! ESTA POSSUI ITEM(S) LIBERADOS.</font>";

if(!empty($_POST['chkt_nfe'])) {
    foreach ($_POST['chkt_nfe'] as $id_nfe) {
        $sql = "SELECT `situacao` 
                FROM `nfe` 
                WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if($campos[0]['situacao'] == 0) {//Se a Nota estiver em aberto ...
//1)
/************************Busca de Dados************************/
//Aqui eu trago alguns dados de Nota Fiscal p/ passar por e-mail via par�metro ...
            $sql = "SELECT nfe.id_empresa, nfe.num_nota, nfe.tipo, f.razaosocial 
                    FROM `nfe` 
                    INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
                    WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
            $campos_nf      = bancos::sql($sql);
//Coloquei esse nome na vari�vel porque na sess�o j� existe uma vari�vel com o nome de id_empresa ...
            $id_empresa_nf  = $campos_nf[0]['id_empresa'];
            $empresa        = genericas::nome_empresa($id_empresa_nf);
            $num_nota       = $campos_nf[0]['num_nota'];
            $tipo           = $campos_nf[0]['tipo'];
            $tipo_nf        = ($tipo == 1) ? 'NF' : 'SGD';
            $fornecedor     = $campos_nf[0]['razaosocial'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
            $complemento_justificativa = '<br><b>Empresa: </b>'.$empresa.' ('.$tipo_nf.') <br><b>Fornecedor: </b>'.$fornecedor.' <br><b>N.� da Nota Fiscal: </b>'.$num_nota;
//2)
/************************E-mail************************/
/*
//-Se o Usu�rio estiver excluindo a Nota Fiscal de Compras, ent�o o Sistema dispara um e-mail informando 
qual a Nota Fiscal que est� sendo exclu�da ...
//-Aqui eu trago alguns dados de Nota Fiscal p/ passar por e-mail via par�metro ...
//-Aqui eu busco o login de quem est� excluindo a Nota Fiscal ...*/
            $sql = "SELECT login 
                    FROM `logins` 
                    WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
            $campos_login       = bancos::sql($sql);
            $login_excluindo    = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
            $justificativa      = $complemento_justificativa.'<br><b>Login: </b>'.$login_excluindo.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$_POST['txt_observacao_justificativa'];
//Aqui eu mando um e-mail informando quem e porque que exclui o Pedido ...
            $destino            = $excluir_nota_fiscal_compras;
            $assunto            = "Exclus�o de Nota Fiscal de Compras ".date('d/m/Y H:i:s');
            $mensagem           = $justificativa;
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', $assunto, $mensagem);
//3)
/************************Exclus�o************************/
//Verifica a Qtde de Itens existentes na Nota Fiscal ...
            $sql = "SELECT id_nfe_historico, id_item_pedido 
                    FROM `nfe_historicos` 
                    WHERE `id_nfe` = '$id_nfe' ";
            $campos_itens = bancos::sql($sql);
            $linhas_itens = count($campos_itens);
            for($i = 0; $i < $linhas_itens; $i++) {
                //Excluindo os Itens da Nota Fiscal ...
                $sql = "DELETE FROM `nfe_historicos` WHERE `id_nfe_historico` = '".$campos_itens[$i]['id_nfe_historico']."' LIMIT 1 ";
                bancos::sql($sql);
                //Atualizando os Itens de Pedido ...
                compras_new::pedido_status($campos_itens[$i]['id_item_pedido']);
            }
            //Verifica se a NF possui antecipa��es atreladas ...
            $sql = "SELECT id_antecipacao 
                    FROM `nfe_antecipacoes` 
                    WHERE `id_nfe` = '$id_nfe' ";
            $campos_antecipacao = bancos::sql($sql);
            $linhas_antecipacao = count($campos_antecipacao);
            if($linhas_antecipacao > 0) {//Significa que existe pelo menos 1 Antecipa��o ...
                for($i = 0; $i < $linhas_antecipacao; $i++) {
                    //Volta a antecipa��o p/ a Posi��o de Liberada, p/ que est� possa ser importada por uma outra NF ...
                    $sql = "UPDATE `antecipacoes` SET `status` = '1' WHERE `id_antecipacao` = '".$campos_antecipacao[$i]['id_antecipacao']."' LIMIT 1 ";
                    bancos::sql($sql);
                }
                //Delete as Antecipa��es que est�o vinculadas a NF ...
                $sql = "DELETE FROM `nfe_antecipacoes` WHERE `id_nfe` = '$id_nfe' ";
                bancos::sql($sql);
            }
//Excluindo o Finciamento da Nota Fiscal ...
            $sql = "DELETE FROM `nfe_financiamentos` WHERE `id_nfe` = '$id_nfe' ";
            bancos::sql($sql);
//Excluindo a Nota Fiscal ...
            $sql = "DELETE FROM `nfe` WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
            $campos = bancos::sql($sql);
            $valor = 1;
        }else {//A Nota j� est� em andamento ou liberada, sendo assim n�o pode ser apagada ...
            $valor = 2;
        }
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'excluir.php<?=$parametro?>&valor=<?=$valor;?>'
    </Script>
<?
}
        
/*Esse par�metro de n�vel vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisi��o desse arquivo Filtro*/
$nivel_arquivo_principal = '../../../..';
//Aqui eu vou puxar a Tela �nica de Filtro de Notas Fiscais que serve para o Sistema Todo ...
require('tela_geral_filtro.php');

//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Excluir Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox')  {
            if(elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OP��O !')
        return false
    }else {
//Observa��o / Justificativa ...
        if(document.form.txt_observacao_justificativa.value == '') {
            alert('DIGITE A OBSERVA��O / JUSTIFICATIVA !')
            document.form.txt_observacao_justificativa.focus()
            document.form.txt_observacao_justificativa.select()
            return false
        }
//Mensagem verificando se o Pedido realmente pode ser exclu�do ...
        var mensagem = confirm('CONFIRMA A EXCLUS�O ?')
        if(!mensagem == true) {
            return false
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit="return validar()">
<table width='70%' border="0" cellspacing="1" cellpadding="1" align='center' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Excluir Nota(s) Fiscal(s) de Entrada
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.&ordm; Nota
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Data Ent.
        </td>
        <td>
            Empresa
        </td>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <a href='itens/itens.php?pop_up=1&id_nfe=<?=$campos[$i]['id_nfe'];?>' class='html5lightbox'>
                <?=$campos[$i]['num_nota'];?>
            </a>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['razaosocial'];
            //Caixa de Compras ...
            if($campos[$i]['pago_pelo_caixa_compras'] == 'S') echo '<font color="blue"><b> (CAIXA DE COMPRAS)</b></font>';
            //Nota j� liberada ...
            if($campos[$i]['situacao'] == 2) echo '<font color="red"><b> (Liberada)</b></font>';
            //Nota Importada no Financeiro ...
            if($campos[$i]['status'] == 1) echo '<font color="darkblue" title="Importado no Financeiro" style="cursor:help"><b> (Import. Financ.)</b></font>';
        ?>
        </td>
        <?
            if(substr($campos[$i]['data_emissao'], 0, 10) == '0000-00-00') {
                $data_emissao = '';
            }else {
                $data_emissao = data::datetodata(substr($campos[$i]['data_emissao'], 0, 10),'/');
            }
        ?>
        <td align='center'>
            <?=$data_emissao;?>
        </td>
        <?
            if(substr($campos[$i]['data_entrega'], 0, 10) == '0000-00-00') {
                $data_entrega = '';
            }else {
                $data_entrega = data::datetodata(substr($campos[$i]['data_entrega'], 0, 10), '/');
            }
        ?>
        <td align='center'>
            <?=$data_entrega;?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nomefantasia'];?>
            (<?
                if($campos[$i]['tipo'] == 1) {
                    echo 'NF';
                }else {
                    echo 'SGD';
                }
            ?>)
        </td>
        <td align='center'>
            <input type='checkbox' name='chkt_nfe[]' value="<?=$campos[$i]['id_nfe'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
    </tr>
<?
    }
?>
    <tr class="linhanormal">
        <td colspan='2'>
            <b>Observa��o / Justificativa:</b>
        </td>
        <td colspan='4'>
            <textarea name='txt_observacao_justificativa' cols='84' rows='6' maxlength='500' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'excluir.php'" class="botao">
            <input type='submit' name='cmd_excluir' value='Excluir' title='Excluir' class='botao'>
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