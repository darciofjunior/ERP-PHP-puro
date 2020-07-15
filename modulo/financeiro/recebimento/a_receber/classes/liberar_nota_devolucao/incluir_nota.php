<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/data.php');
require('../../../../../../lib/calculos.php');
require('../../../../../../lib/faturamentos.php');
require('../../../../../../lib/financeiros.php');
require('../../../../../../lib/genericas.php');
require('../../../../../../lib/intermodular.php');//Essa biblioteca � utilizada dentro da Biblioteca 'faturamentos' ...
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>N�O EXISTE(M) NOTA(S) FISCAL(IS) NESSA CONDI��O.</font>";
$mensagem[3] = "<font class='confirmacao'>NOTA DE DEVOLU��O LIBERADA COM SUCESSO.</font>";

if($passo == 1) {
//Aqui eu busco os dados da Nota Fiscal Secund�ria "Devolvida" com o id_nf passado por par�metro ...
    $sql = "SELECT c.`id_pais`, c.`razaosocial`, nfs.`id_cliente`, nfs.`data_emissao`, nfs.`valor_dolar_dia`, nfs.`suframa` 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    //Coloquei esse nome na vari�vel porque na sess�o j� existe uma vari�vel com o nome de id_empresa
    $id_empresa_nota 	= $campos[0]['id_empresa'];
    $suframa            = $campos[0]['suframa'];
    $id_pais            = $campos[0]['id_pais'];
    $id_cliente         = $campos[0]['id_cliente'];
    $razaosocial        = $campos[0]['razaosocial'];
    $data_emissao       = data::datetodata($campos[0]['data_emissao'], '/');

    //Controle com o N.� da Nota Fiscal ...
    $numero_nf_devolucao = faturamentos::buscar_numero_nf($_GET['id_nf'], 'D');

    //Aqui verifica o Tipo de Nota
    if($id_empresa_nota == 1 || $id_empresa_nota == 2) {
        $nota_sgd = 'N';//var surti efeito l� embaixo
        $tipo_nota = ' (NF)';
    }else {
        $nota_sgd = 'S'; //var surti efeito l� embaixo
        $tipo_nota = ' (SGD)';
    }

    if($campos[0]['data_emissao'] != '0000-00-00') $data_emissao = data::datetodata($campos[0]['data_emissao'], '/');
    $valor_dolar_nota   = $campos[0]['valor_dolar_dia'];
//Aqui eu j� tenho o c�lculo para o valor das duplicatas
    $calculo_total_impostos = calculos::calculo_impostos(0, $_GET['id_nf'], 'NF');
    $simbolo_moeda          = ($id_pais == 31) ? 'R$ ' : 'U$ ';
?>
<html>
<head>
<title>.:: Liberar Nota de Devolu��o (Autom�tico) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calcular_valor_total_abatimentos() {
//Controle com os Valores da Nota Vendida ...
    var elementos = document.form.elements
    var total_valor_abatimento = 0
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_valor_abatimento[]') {
//S� ir� contabilizar esse Campo se o Valor estiver preenchido ...
            if(elementos[i].value != '') total_valor_abatimento+= eval(strtofloat(elementos[i].value))
        }
    }
    document.form.txt_valor_total_abatimentos.value = total_valor_abatimento
    document.form.txt_valor_total_abatimentos.value = arred(document.form.txt_valor_total_abatimentos.value, 2, 1)
}
</Script>
</head>
<body onload='calcular_valor_total_abatimentos()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<!--*******************Controle de Tela*******************-->
<input type='hidden' name='id_nf' value='<?=$_GET['id_nf'];?>'>
<input type='hidden' name='id_emp' value='<?=$id_emp;?>'>
<!--Esse hidden "hdd_cliente" ser� utilizado no pr�ximo passo ...-->
<input type='hidden' name='hdd_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='hdd_pais' value='<?=$id_pais;?>'>
<!--******************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Liberar Nota de 
            <font color='yellow'>
                Devolu��o 
            </font>
            <?
//Aki eu busco o id_pedido_venda_item com o id_nf da Nota Fiscal para poder ver os detalhes da NF
                $sql = "SELECT nfsi.`id_pedido_venda_item` 
                        FROM `nfs` 
                        INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
                        WHERE nfs.`id_nf` = '$_GET[id_nf]' LIMIT 1 ";
                $campos_nfs_item = bancos::sql($sql);
                if(count($campos_nfs_item) == 1) {//Quando tiver pelo menos 1 item de pedido na NF, tem link
                    $id_pedido_venda_item = $campos_nfs_item[0]['id_pedido_venda_item'];
            ?>
                    <a href="javascript:nova_janela('../../../../../classes/faturamento/faturado.php?id_pedido_venda_item=<?=$id_pedido_venda_item;?>', 'FATURADO', '', '', '', '', 350, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Faturamento" class='link'>
                        <font color='darkgreen'>
            <?
                        if(!empty($numero_nf_devolucao)) {
                            echo $numero_nf_devolucao;
                        }else {
                            echo '&nbsp;-&nbsp;';
                        }
            ?>
                        </font>
                    </a>
            <?
                }else {//N�o tem nenhum item, ent�o n�o tem como ter link para ver os detalhes
                    if(!empty($numero_nf_devolucao)) {
                        echo $numero_nf_devolucao;
                    }else {
                        echo '&nbsp;-&nbsp;';
                    }
                }
                echo '&nbsp;'.genericas::nome_empresa($id_emp);
            ?>
            (Autom�tico)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='40%'>
            <b>Cliente:</b>
        </td>
        <td>
            <?=$razaosocial;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Representante:</b>
        </td>
        <td>
        <?
//Verifico qual foi o Representante que teve a maior venda em Nota Fiscal 
//Aqui eu coloco esse comando (sum) para me retornar o representante que teve a maior venda na NF ...
            $sql = "SELECT SUM(nfsi.`valor_unitario`) AS valor_unitario, r.`nome_fantasia` 
                    FROM `nfs_itens` nfsi 
                    INNER JOIN `representantes` r ON r.`id_representante` = nfsi.`id_representante` 
                    WHERE nfsi.`id_nf` = '$_GET[id_nf]' GROUP BY nfsi.`id_representante` ORDER BY nfsi.`valor_unitario` DESC LIMIT 1 ";
            $campos_nfs_item = bancos::sql($sql);
            echo $campos_nfs_item[0]['nome_fantasia'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Emiss�o:</b>
        </td>
        <td>
            <?=$data_emissao;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>        
            <b>Valor Total da Nota: </b>
        </td>
        <td>
        <?
            //Quando o Cliente � "Estrangeiro", trabalho com a Nota Fiscal no valor em U$ ...
            $valor_total_nota_devolucao = ($id_pais != 31) ? $calculo_total_impostos['valor_total_nota_us'] : $calculo_total_impostos['valor_total_nota'];
            echo $simbolo_moeda.number_format($valor_total_nota_devolucao, 2, ',', '.');
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='blue'>
                <b>Saldo Restante p/ Importar do "Valor Total da Nota": </b>
            </font>
        </td>
        <td>
        <?
            /********************************************************************************************/
            //Verifico se tenho alguma coisa dessa NF de Devolu��o importada em alguma outra Duplicata ...
            $sql = "SELECT SUM(`valor_devolucao`) AS total_devolucao_importada 
                    FROM `contas_receberes_vs_nfs_devolucoes` 
                    WHERE `id_nf_devolucao` = '$_GET[id_nf]' ";
            $campos_devolucao_importada = bancos::sql($sql);
            //Do valor Total da NF de Devolu��o, abato o Valor do que j� foi importado antes em outras Duplicatas ...
            $valor_total_nota_devolucao-= $campos_devolucao_importada[0]['total_devolucao_importada'];
            /********************************************************************************************/
            echo $simbolo_moeda.number_format($valor_total_nota_devolucao, 2, ',', '.');
            //Essa vari�vel ser� utilizada mais abaixo em JavaScript ...
            $valor_total_nota_devolucao_inicial = $valor_total_nota_devolucao;
            
            //Aqui eu mostro p/ o usu�rio logado um lembrete do Valor de NF que j� foi importado em outras duplicatas ...
            if($campos_devolucao_importada[0]['total_devolucao_importada'] > 0) {
                echo ' - <font color="blue"><b>(Valor Importado em Outras Duplicatas => R$ '.number_format($campos_devolucao_importada[0]['total_devolucao_importada'], 2, ',', '.').')</b></font>';
            }
        ?>
            <!--Esse hidden ser� utilizado no pr�ximo passo ...-->
            <input type='hidden' name='valor_total_nota_devolucao_inicial' value='<?=number_format($valor_total_nota_devolucao_inicial, 2, ',', '.');?>'>
            Talvez esse campo n�o seja mais necess�rio ...
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Observa��o:</b>
        </td>
        <td>
        <?
            //Aqui eu busco as observa��es de Follow-UP(s) atrav�s do id_nf passado por par�metro ...
            $sql = "SELECT `observacao` 
                    FROM `follow_ups` 
                    WHERE `identificacao` = '$_GET[id_nf]' 
                    AND `origem` = '5' ";
            $campos_follow_up = bancos::sql($sql);
            $linhas_follow_up = count($campos_follow_up);
            
            for($i = 0; $i < $linhas_follow_up; $i++) echo '<br/>'.$campos_follow_up[$i]['observacao'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor D�lar da Nota: </b>
        </td>
        <td>
            <?='R$ '.number_format($valor_dolar_nota, 4, ',', '.');?>
        </td>
    </tr>
</table>
<?
    /*Essa vari�vel � de extrema import�ncia porque � ela quem controla os �ndices das Caixinhas 
    "Texts e Hiddens" que est�o em array mais abaixo ...*/
    $indice = 0;
/*************************Duplicatas das NF(s) de Sa�da atreladas a esta NF de Devolu��o*************************/
/*Aqui trago todos os "id_itens_nfs_saida" atrav�s dos "id_itens_nfs_devolucao" atrav�s do id_nf de Devolu��o 
passado por par�metro ...*/
    $sql = "SELECT `id_nf_item_devolvida` 
            FROM `nfs_itens` 
            WHERE `id_nf` = '$_GET[id_nf]' ";
    $campos_itens_devolucoes = bancos::sql($sql);
    $linhas_itens_devolucoes = count($campos_itens_devolucoes);
    for($i = 0; $i < $linhas_itens_devolucoes; $i++) $itens_devolucoes.= $campos_itens_devolucoes[$i]['id_nf_item_devolvida'].', ';
    $itens_devolucoes = substr($itens_devolucoes, 0, strlen($itens_devolucoes) - 2);

    /*Aqui eu trago todas as Duplicatas que n�o estejam finalizadas da(s) NF(s) de Sa�da atrav�s dos 
    id_itens_nfs_saida encontrados no SQL anteriormente ...*/
    $sql = "SELECT cr.`id_conta_receber`, cr.`num_conta`, cr.`data_emissao`, cr.`data_vencimento_alterada`, 
            cr.`valor`, cr.`valor_pago`, cr.`status` 
            FROM `nfs_itens` nfsi 
            INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
            INNER JOIN `contas_receberes` cr ON cr.id_nf = nfs.id_nf AND cr.status < '2' 
            WHERE nfsi.`id_nfs_item` IN ($itens_devolucoes) GROUP BY cr.`id_conta_receber` ";
    $campos_nfs = bancos::sql($sql);
    $linhas_nfs = count($campos_nfs);
    if($linhas_nfs > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='iframe' align='center'>
        <td colspan='4'>
            Duplicatas das NF(s) de Sa�da atreladas a esta NF de Devolu��o
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.&ordm; da Duplicata
        </td>
        <td>
            Valor Reajustado <?=$simbolo_moeda;?>
        </td>
        <td>
            Valor Abatimento <?=$simbolo_moeda;?>
        </td>
        <td>
            Data de Vencimento
        </td>
    </tr>
<?
        for($i = 0 ; $i < $linhas_nfs; $i++) {
            $calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
            if($calculos_conta_receber['valor_reajustado'] > 0) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <!--Para que a tela seja aberta como Pop-UP ...-->
            <a href="javascript:nova_janela('../../../alterar.php?id_conta_receber=<?=$campos[$i]['id_conta_receber'];?>&pop_up=1', 'POP', '', '', '', '', 520, 920, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Conta � Receber' class='link'>
                <?=$campos_nfs[$i]['num_conta'];?>
            </a>
        </td>
        <td align='right'>
            <?=number_format($campos_nfs[$i]['valor'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($campos_nfs[$i]['valor_pago'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $valor_a_receber = $calculos_conta_receber['valor_reajustado'];
            echo number_format($valor_a_receber, 2, ',', '.');
        ?>
            <input type='hidden' name='hdd_valor_receber[]' id='hdd_valor_receber<?=$indice;?>' value='<?=number_format($valor_a_receber, 2, ',', '.');?>'>
        </td>
        <td align='right'>
            <?
                //Se o valor da Duplicata e maior do que o Total da NF de Devolucao ...
                if($valor_a_receber > $valor_total_nota_devolucao) {
                    $valor_abatimento = $valor_total_nota_devolucao;
                }else {//Valor da Duplicata e menor do que o Total da NF de Devolucao ...
                    $valor_abatimento = $valor_a_receber;
                }
                //Do valor da NF de Devolucao, vou descontando os valores de abatimento ...
                $valor_total_nota_devolucao-= $valor_abatimento;
            ?>
            <input type='text' name='txt_valor_abatimento[]' id='txt_valor_abatimento<?=$indice;?>' value='<?=number_format($valor_abatimento, 2, ',', '.');?>' title='Digite o Valor de Abatimento' maxlength='10' size='12' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_total_abatimentos()" class='caixadetexto'>
        </td>
        <td>
            <?=data::datetodata($campos_nfs[$i]['data_vencimento_alterada'], '/');?>
            <input type='hidden' name='hdd_conta_receber[]' value='<?=$campos_nfs[$i]['id_conta_receber'];?>'>
        </td>
    </tr>
<?
                /*Aqui nessa variavel vou acumulando todas as Duplicatas referentes a(s) NF de Saida(s) 
                que estao vinculadas a NF de Devolu��o selecionada pelo Usu�rio ...*/
                $id_contas_receberes.= $campos_nfs[$i]['id_conta_receber'].', ';
                $indice++;
            }
        }
    }
    //Tratamento com a variavel abaixo p/ n�o furar o proximo SQL ...
    $id_contas_receberes = (isset($id_contas_receberes)) ? substr($id_contas_receberes, 0, strlen($id_contas_receberes) - 2) : 0;
/**********************************Demais Duplicata(s) em aberto desse Cliente***********************************/
/*Aqui eu trago todas as Duplicatas da empresa do Menu do Financeiro, do Cliente, que estejam em aberto, e que n�o 
sejam as Duplicatas de NF de Saida exibidas acima, porque estas se enquadrariam perfeitamente no SQL abaixo 
e seriam exibidas novamente ...*/
    $sql = "SELECT `id_conta_receber`, `num_conta`, `data_emissao`, `data_vencimento_alterada`, `valor`, `valor_pago`, `status` 
            FROM `contas_receberes` 
            WHERE `id_conta_receber` NOT IN ($id_contas_receberes) 
            AND `id_empresa` = '$id_emp' 
            AND `id_cliente` = '$id_cliente' 
            AND `status` < '2' 
            AND (`valor` > `valor_pago`) ORDER BY `data_vencimento_alterada` ";//Esse controle (`valor` > `valor_pago`) � para o Sistema n�o trazer as Duplicatas em que o Cliente tem Cr�dito "Valor Negativo" ...
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='iframe' align='center'>
        <td colspan='4'>
            Demais Duplicata(s) em aberto desse Cliente
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.&ordm; da Duplicata
        </td>
        <td>
            Valor Reajustado <?=$simbolo_moeda;?>
        </td>
        <td>
            Valor Abatimento <?=$simbolo_moeda;?>
        </td>
        <td>
            Data de Vencimento
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $calculos_conta_receber = financeiros::calculos_conta_receber($campos[$i]['id_conta_receber']);
            if($calculos_conta_receber['valor_reajustado'] > 0) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <!--Para que a tela seja aberta como Pop-UP ...-->
            <a href="javascript:nova_janela('../../../alterar.php?id_conta_receber=<?=$campos[$i]['id_conta_receber'];?>&pop_up=1', 'POP', '', '', '', '', 520, 920, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Conta � Receber' class='link'>
                <?=$campos[$i]['num_conta'];?>
            </a>
        </td>
        <td align='right'>
        <?
            $valor_a_receber = $calculos_conta_receber['valor_reajustado'];
            echo number_format($valor_a_receber, 2, ',', '.');
        ?>
            <input type='hidden' name='hdd_valor_receber[]' id='hdd_valor_receber<?=$indice;?>' value='<?=number_format($valor_a_receber, 2, ',', '.');?>'>
        </td>
        <td align='right'>
            <?
                //Se o valor da Duplicata e maior do que o Total da NF de Devolucao ...
                if($valor_a_receber > $valor_total_nota_devolucao) {
                    $valor_abatimento = $valor_total_nota_devolucao;
                }else {//Valor da Duplicata e menor do que o Total da NF de Devolucao ...
                    $valor_abatimento = $valor_a_receber;
                }
                //Do valor da NF de Devolucao, vou descontando os valores de abatimento ...
                $valor_total_nota_devolucao-= $valor_abatimento;
            ?>
            <input type='text' name='txt_valor_abatimento[]' id='txt_valor_abatimento<?=$indice;?>' value='<?=number_format($valor_abatimento, 2, ',', '.');?>' title='Digite o Valor' maxlength='10' size='12' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_total_abatimentos()" class='caixadetexto'>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento_alterada'], '/');?>
            <input type='hidden' name='hdd_conta_receber[]' value='<?=$campos[$i]['id_conta_receber'];?>'>
        </td>
    </tr>
<?
                $indice++;
            }
        }
    }
/*******************************************Gerar um Cr�dito Financeiro******************************************/
    /*Se o sistema n�o encontrou nenhuma Duplicata de Sa�da atrelada � NF de Devolu��o passada por 
    par�metro e tamb�m n�o encontrou nenhuma outra duplicata em aberto desse Cliente p/ sugerir, ent�o 
    o sistema pergunta p/ o Usu�rio se o mesmo deseja gerar um cr�dito Financeiro p/ poder ent�o 
    atribu�-lo a essa Devolu��o ...*/
    if($linhas_nfs == 0 && $linhas == 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td colspan='4'>
            <!--Tenho que ter essas caixas p/ n�o furar a Valida��o de Formul�rio "validar()" ...-->
            <input type='hidden' name='hdd_valor_receber[]' id='hdd_valor_receber<?=$indice;?>' value='<?=number_format($valor_total_nota_devolucao, 2, ',', '.');?>'>
            <input type='hidden' name='txt_valor_abatimento[]' id='txt_valor_abatimento<?=$indice;?>' value='0,00'>
        </td>
    </tr>
    <Script Language = 'JavaScript'>
        //Preciso colocar um "Timeout" p/ dar tempo de carregar todo o Formul�rio ...
        setTimeout('document.form.cmd_salvar.click()', 500)
    </Script>
<?
    }
?>
    <tr class='linhadestaque'>
        <td colspan='2'>
            Valor Total dos Abatimentos: 
        </td>
        <td align='right'>
            <input type='text' name='txt_valor_total_abatimentos' size='12' maxlength='15' class='textdisabled' disabled>
        </td>
        <td>
            &nbsp;
        </td>
    </tr>
</table>
<?
/****************************************************************************************************************/
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>    
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir_nota.php?id_emp=<?=$id_emp;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao' <?=$disabled_submit;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
</table>
</form>
<!--*************************************************************************-->    
<Script Language = 'JavaScript'>
/*Adaptei essa fun��o aqui abaixo, porque eu precisava de algumas vari�veis em PHP que foram carregadas 
com o decorrer do Script ...*/
function validar() {
/*Nessa parte da fun��o verifica se o Valor Total dos Abatimentos digitados da NF Vendida � exatamente 
igual ao Somat�rio de Valores em R$ da Nota de Devolu��o*/
//Controle com os Valores da Nota de Devolu��o ...   
    var total_valor_nota_devolucao  = eval(strtofloat('<?=number_format($valor_total_nota_devolucao_inicial, 2, ',', '.');?>'))
//Controle com os Valores da Nota Vendida ...
    var elementos               = document.form.elements
    var total_valor_abatimento  = 0
//Verifico o N.� de Linhas de abatimentos que existem nesse Formul�rio ...
    if(typeof(elementos['txt_valor_abatimento[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['txt_valor_abatimento[]'].length)
    }
    
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('txt_valor_abatimento'+i).value != '') {
            if(eval(strtofloat(document.getElementById('txt_valor_abatimento'+i).value)) > eval(strtofloat(document.getElementById('hdd_valor_receber'+i).value))) {
                alert('O "VALOR ABATIMENTO R$" N�O PODE SER MAIOR DO QUE O "VALOR � RECEBER R$" !')
                document.getElementById('txt_valor_abatimento'+i).focus()
                document.getElementById('txt_valor_abatimento'+i).select()
                return false
            }
        }
    }
//Verifica��o do "Valor Total da Nota de Devolu��o" com o "Valor Total de Abatimento" ...
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('txt_valor_abatimento'+i).value != '') {
//S� ir� contabilizar esse Campo se o Valor estiver preenchido ...
            total_valor_abatimento+= eval(strtofloat(document.getElementById('txt_valor_abatimento'+i).value))
        }
    }
    total_valor_abatimento = arred(String(total_valor_abatimento), 2, 1)
    total_valor_abatimento = eval(strtofloat(total_valor_abatimento))
    
/*Quando o Valor Total Nota de Devolu��o for maior que o Valor Total dos Abatimentos, sugiro para o usu�rio 
se o mesmo deseja gerar um "Cr�dito Financeiro" ...*/
    if(total_valor_nota_devolucao > total_valor_abatimento) {
        var resposta = confirm('O TOTAL DA NOTA FISCAL DE DEVOLU��O � MAIOR DO QUE O SOMAT�RIO DOS ABATIMENTO(S) DA(S) NOTA(S) FISCAL(IS) VENDIDA(S) !!!\n\nDESEJA GERAR UM CR�DITO FINANCEIRO ?')
        if(resposta == false) return false
    }else {
/*Fazendo a Compara��o com os Valores p/ ver se o Valor Total dos Abatimentos e o Valor Total Nota de 
Devolu��o s�o exatamente iguais ...*/
        if(total_valor_nota_devolucao != total_valor_abatimento) {
            alert('O SOMAT�RIO DOS ABATIMENTO(S) DA NOTA FISCAL VENDIDA N�O EQUIVALE AO TOTAL DA NOTA FISCAL DE DEVOLU��O !')
            return false
        }
    }
//Aqui desabilita os campos travados para poder gravar no BD
    for(var i = 0; i < document.form.elements.length; i++) document.form.elements[i].value = strtofloat(document.form.elements[i].value)
//Desabilito o Bot�o para o usu�rio n�o ficar incluindo v�rias vezes a mesma Nota no BD
    document.form.cmd_salvar.disabled = true
}
</Script>
<!--*************************************************************************-->
</body>
</html>
<pre>
<b><font color='red'>Observa��o:</font></b>
<pre>
* Caso o Valor Total dos Abatimentos seja inferior ao Valor Total da Nota de Devolu��o ser� gerada 
uma Conta � Receber (-) como Cr�dito p/ o Cliente.
</pre>
<?
}else if($passo == 2) {
    //Essa vari�vel ser� de extrema import�ncia p/ saber se ser� necess�rio gerar um Cr�dito p/ o Cliente ...
    $valor_total_nota_devolucao = $_POST['valor_total_nota_devolucao_inicial'];
    $vinculou_id_devolucao      = 'N';
    
    //Simplesmente vinculo o N.� Nota de Devolu��o em cima do N.� da Nota Vendida ...
    foreach($_POST['hdd_conta_receber'] as $i => $id_conta_receber) {
        //Esse Registro de Devolu��o s� ser� feito quando o Valor de Abatimento estiver preenchido ...
        if($_POST['txt_valor_abatimento'][$i] > 0) {
            $sql = "INSERT INTO `contas_receberes_vs_nfs_devolucoes` (`id_conta_receber_nf_devolucao`, `id_conta_receber`, `id_nf_devolucao`, `valor_devolucao`) VALUES (NULL, '$id_conta_receber', '$_POST[id_nf]', '".$_POST['txt_valor_abatimento'][$i]."') ";
            bancos::sql($sql);
            $valor_total_nota_devolucao-= $_POST['txt_valor_abatimento'][$i];
            $vinculou_id_devolucao      = 'S';//Controle p/ n�o vincular a "Duplicata Corrente" na tabela de "Duplicata de Devolu��o" ...
        }
    }
    
    //Como sobrou saldo nessa vari�vel, ent�o significa que devo gerar um Cr�dito Financeiro p/ o Cliente ...
    if($valor_total_nota_devolucao > 0) {
        $semana         = data::numero_semana(date('d'), date('m'), date('Y'));
        $id_tipo_moeda  = ($_POST['hdd_pais'] == 31) ? 1 : 2;//Se o pa�s do Cliente for do Brasil a moeda fica sendo em R$, sen�o � U$ ...
        $observacao     = 'Cr�dito referente a NF de Devolu��o N.� '.faturamentos::buscar_numero_nf($_POST['id_nf'], 'D');
        //Somente a op��o de Cr�dito inverte o Sinal, gerando uma Conta Negativa ...
        $valor_total_nota_devolucao*= (-1);

        $sql = "INSERT INTO `contas_receberes` (`id_conta_receber`, `id_empresa`, `id_tipo_recebimento`, `id_funcionario`, `id_cliente`, `id_tipo_moeda`, `num_conta`, `semana`, `previsao`, `data_emissao`, `data_vencimento_alterada`, `valor`, `valor_desconto`, `comissao_estornada`, `data_sys`, `fase_implant`, `status`, `ativo`) VALUES (NULL, '$id_emp', '15', '$_SESSION[id_funcionario]', '$_POST[hdd_cliente]', '$id_tipo_moeda', '".date('dmY')."', '$semana', '0', '".date('Y-m-d')."', '".date('Y-m-d')."', '$valor_total_nota_devolucao', '0', '0', '".date('Y-m-d H:i:s')."', '3', '0', '1') ";
        bancos::sql($sql);
        $id_conta_receber_novo = bancos::id_registro();
        
        //Registrando Follow-UP(s) ...
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[hdd_cliente]', '$_SESSION[id_funcionario]', '$id_conta_receber_novo', '4', '$observacao', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        
        if($vinculou_id_devolucao == 'N') {//Se n�o vinculou a "Duplicata Corrente" na tabela de "Duplicata de Devolu��o" ...
            $sql = "INSERT INTO `contas_receberes_vs_nfs_devolucoes` (`id_conta_receber_nf_devolucao`, `id_conta_receber`, `id_nf_devolucao`, `valor_devolucao`) VALUES (NULL, '$id_conta_receber_novo', '$_POST[id_nf]', '0.00') ";
            bancos::sql($sql);
        }
    }
    /*Significa que essa duplicata q estava quitada e foi a situa��o da mesma concedendo Cr�dito p/ o Cliente, ent�o 
    atualizo o seu status p/ "1" - Parcial ...*/
    //****************************** ******************************//
    //Fun��o que muda a Situa��o da NF quando est� for importada no sistema do Faturamento para o Financ.
    //controla o status de importa��o de nfs para financeiro -> intermodular::verifica_status_importar_financeiro($id_nf);
    //****************************** ******************************//
    $sql = "UPDATE `nfs` SET `importado_financeiro` = 'S' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.opener.parent.itens.document.form.recarregar.value = 1
        window.location = 'incluir_nota.php?id_emp=<?=$id_emp;?>&valor=3'
    </Script>
<?
}else {
/*Aqui o sistema exibe todas as NF(s) que s�o do Tipo Devolu��o, que estejam Liberadas, que 
tenham valor de Faturamento > R$ 0,00 e que possuem algum N.� de NF de Devolu��o - independente 
de ser Nosso N.� ou N.� do Cliente ...*/
    $sql = "SELECT nfs.id_nf, nfs.id_empresa, nfs.data_emissao, nfs.vencimento1, nfs.vencimento2, nfs.vencimento3, nfs.vencimento4, nfs.status, c.razaosocial, c.credito, t.nome AS transportadora 
            FROM `nfs` 
            INNER JOIN `transportadoras` t ON t.id_transportadora = nfs.id_transportadora 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            WHERE nfs.`id_empresa` = '$id_emp' 
            AND nfs.`valor1` <> '0' 
            AND nfs.`status` = '6' 
            AND nfs.`importado_financeiro` = 'N' 
            AND nfs.`devolucao_faturada` = 'S' 
            AND (nfs.`id_nf_num_nota` <> '0' OR nfs.`snf_devolvida` <> '') ORDER BY nfs.id_nf DESC ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Consultar Notas Fiscais ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP, tem um controle um pouquinho diferente
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
//Vari�vel referente ao Frame de Baixo
    var recarregar = window.opener.parent.itens.document.form.recarregar.value
    if(recarregar == 1 && document.form.ignorar.value == 0) {
        if(typeof(window.opener.parent.itens.document.form) == 'object') {
            window.opener.parent.itens.document.location = '../itens.php<?=$parametro;?>'
        }
    }
}
</Script>
</head>
<?
    if($linhas == 0) {//Nao existe nenhuma NF de Devolu��o da Empresa do Menu p/ importar ...
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td>
            <?=$mensagem[2];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../opcoes_incluir.php'" class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" onclick="window.close()" style="color:red" class='botao'>
        </td>
    </tr>
</table>
<?
    }else {//Existe pelo menos uma NF de Devolu��o da Empresa do Menu p/ importar ...
?>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>'>
<input type='hidden' name='passo' value='1'>
<!--Controle de Tela-->
<input type='hidden' name='ignorar'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='5'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Consultar Nota(s) Fiscal(is) de 
            <font color='yellow'>
                Devolu��o 
            </font>
            <?=genericas::nome_empresa($id_emp);?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Nota Fiscal
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Emp / Tp Nota <br>/ Prazo Pgto
            </font>
        </td>
    </tr>
<?
        for($i = 0;  $i < $linhas; $i++) {
            $url = "javascript:document.form.ignorar.value = 1;window.location = 'incluir_nota.php?passo=1&id_nf=".$campos[$i]['id_nf']."&id_emp=".$id_emp."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="<?=$url;?>" title='Liberar Nota de Sa�da de Devolu��o' class='link'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'D');?>
            </a>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['nomefantasia'])) {
                echo $campos[$i]['nomefantasia'];
            }else {
                echo $campos[$i]['razaosocial'];
            }
        ?>
        </td>
        <td align='left'>
        <?
            $sql = "SELECT `nomefantasia` 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $nomefantasia   = $campos_empresa[0]['nomefantasia'];

            if($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) {
                $apresentar = $nomefantasia.' (NF)';
            }else {
                $apresentar = $nomefantasia.' (SGD)';
            }

            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ?  '� vista' : $campos[$i]['vencimento1'];
            }
            echo $apresentar.' / '.$prazo_faturamento;
//Aki eu limpo essa vari�vel para n�o dar problema quando voltar no pr�ximo loop
            $prazo_faturamento = '';
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../opcoes_incluir.php'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
<?
/*Aki � simplesmente o contador, n�o tem pagina��o para n�o dar conflito com a da Tela de Itens que est� 
no Frame Debaixo*/
?>
    <tr>
        <td colspan='5'>
            &nbsp;
        </td>
    </tr>
    <tr class='confirmacao' align='center'>
        <td colspan='5'>
            Total de Registro(s): <?=$linhas;?>
        </td>
    </tr>
</table>
</form>
</body>
<pre>
<b><font color='red'>Observa��o:</font></b>
<pre>
<b>O sistema n�o exibe: </b>

 * Valores zerados
 * Notas Fiscais de Devolu��o s/ N.�
</pre>
</html>
<?
    }
}
?>