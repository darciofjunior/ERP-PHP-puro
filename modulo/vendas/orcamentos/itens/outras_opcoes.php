<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/intermodular.php');//Essa biblioteca � chamada aqui porque a mesma � utilizada dentro da Vendas ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

/********************************Comprar como Export********************************/
if(!empty($_GET['comprar_como_export'])) {
    //Essa fun��o posteriormente ir� p/ o Cabe�alho ...
    $sql = "UPDATE `orcamentos_vendas` SET `comprar_como_export` = '$_GET[comprar_como_export]' WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
    bancos::sql($sql);
    /********************************************************************************************************/
    //Busco todos os itens do $id_orcamento_venda passado por par�metro p/ poder rodar algumas fun��es abaixo ...
    $sql = "SELECT `id_orcamento_venda_item` 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
        vendas::calculo_preco_liq_final_item_orc($campos[$i]['id_orcamento_venda_item']);
        //Aqui eu atualizo a ML Est do Iem do Or�amento ...
        custos::margem_lucro_estimada($campos[$i]['id_orcamento_venda_item']);
/*************Rodo a fun��o de Comiss�o depois de ter gravado a ML Estimada*************/
        vendas::calculo_ml_comissao_item_orc($_GET['id_orcamento_venda'], $campos[$i]['id_orcamento_venda_item']);
    }
?>
    <Script Language = 'JavaScript'>
        alert('ATUALIZA��O DE COMPRAR COMO EXPORT REALIZADA COM SUCESSO !')
        window.parent.location = '/erp/albafer/modulo/vendas/orcamentos/itens/itens.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'
    </Script>
<?
}
/***********************************************************************************/
?>
<html>
<head>
<title>.:: Outras Op��es ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function avancar() {
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    if(document.form.opt_opcao[0].checked == true) {//Desconto Padr�o ...
        window.location = 'desconto_padrao.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'
    }else if(document.form.opt_opcao[1].checked == true) {
//Quando o par�metro acao = 0, significa q deseja Transportar os itens para o mesmo cliente
        window.location = 'transportar_outro_orcamento.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>&acao=0'
    }else if(document.form.opt_opcao[2].checked == true) {
//Quando o par�metro acao = 1, significa q deseja Clonar os itens para outro cliente
        window.location = 'transportar_outro_orcamento.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>&acao=1'
    }else if(document.form.opt_opcao[3].checked == true) {//Gerar Pedido ...
        window.location = 'gerar_pedido.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'
    }else if(document.form.opt_opcao[4].checked == true) {//Adaptar / Retirar Promo��o p/ todos os Itens do Or�amento ...
        window.location = 'adaptar_retirar_promocao_todos_itens.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'
    }else if(document.form.opt_opcao[5].checked == true) {//Incluir Atendimento Diario ...
        window.location = '../../atendimento_diario/incluir.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'
    }else if(document.form.opt_opcao[6].checked == true) {//E-mail p/ Depto. T�cnico Incluir Novos PA(s) ...
        window.location = 'email_depto_tecnico_incluir_novos_pas.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'	
    }else if(document.form.opt_opcao[7].checked == true) {//Importar OPC ...
        window.location = 'importar_opc.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'
    }else if(document.form.opt_opcao[8].checked == true) {//Retirar Promo��o de Excesso de Estoque ...
        var resposta = confirm('TEM CERTEZA DE QUE DESEJA RETIRAR A OP��O DE EXCESSO DE ESTOQUE DE TODOS OS ITEM(NS) DESSE OR�AMENTO ?')
        if(resposta == true) window.location = 'retirar_queima_todos_itens.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'
    }else if(document.form.opt_opcao[9].checked == true) {//Incluir / Alterar C�digo(s) Produto(s) do Cliente ...
        window.location = '../../cliente/vs_produtos_acabados.php?passo=1&id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'
    }else if(document.form.opt_opcao[10].checked == true) {//Atualizar Pre�os pela Lista de Compras JT ...
        var acrescimo_extra = prompt('DIGITE O ACR�SCIMO EXTRA: ', '0,00')
        //Se o usu�rio clicou no bot�o ok, ent�o o sistema avan�a para o arquivo "atualizar_precos_lista_compras_jt" ...
        if(acrescimo_extra != null) window.location = 'atualizar_precos_lista_compras_jt.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>&acrescimo_extra='+acrescimo_extra
    }else if(document.form.opt_opcao[11].checked == true) {//Ignorar Lote M�nimo do Grupo Faixa Or��vel ...
        window.location = 'ignorar_lote_minimo_do_grupo_faixa_orcavel.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>'	
    }else {
        alert('SELECIONE UMA OP��O !')
        return false
    }
}

function comprar_como_export() {
    alert('POR FAVOR AGUARDE UM MOMENTO !!!\n\nESSA ROTINA � BEM DEMORADA !')
    var comprar_como_export = (document.form.chkt_comprar_como_export.checked) ? 'S' : 'N'
    document.getElementById('lbl_mensagem').innerHTML = '<img src="../../../../css/little_loading.gif"> <font size="2" color="brown"><b>LOADING ...</b></font>'
    window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$_GET['id_orcamento_venda'];?>&comprar_como_export='+comprar_como_export
}
</Script>
</head>
<body>
<form name='form' method='post'>
<input type='hidden' name='nao_atualizar'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Outras Op��es
        </td>
    </tr>
<?
        //Trago alguns dados B�sicos do Or�amento passado por par�metro ...
        $sql = "SELECT c.`id_pais`, ov.`congelar`, ov.`comprar_como_export`, ov.`status` 
                FROM `orcamentos_vendas` ov 
                INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
                WHERE ov.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $id_pais                = $campos[0]['id_pais'];
        $congelar               = $campos[0]['congelar'];
        $comprar_como_export    = $campos[0]['comprar_como_export'];
        $status                 = $campos[0]['status'];

//Esse controle s� serve para travar os campos de Desconto Extra e Acr�scimo Extra, caso tenha virado o ano para 2100 ...
        if(date('Y-m-d') >= '2100-02-10')   $disabled_desconto_padrao = 'disabled';
?>
    <tr class='linhanormal'>
        <td width='20%'>
        <?
            //Se o Or�amento estiver congelado ou existir algum Item que est� em Queima de Estoque, travo o Cabe�alho ...
            $vetor_dados_gerais     = vendas::dados_gerais_orcamento($_GET[id_orcamento_venda]);
            $data_validade_orc      = $vetor_dados_gerais['data_validade_orc'];
            $dias_validade          = $vetor_dados_gerais['dias_validade'];
            $possui_queima_estoque  = $vetor_dados_gerais['possui_queima_estoque'];

            if($possui_queima_estoque == 'S') {
                $aviso              = '<font color="red"><b> EXISTE(M) ITEM(NS) EM EXCESSO DE ESTOQUE !!! S� � POSS�VEL DAR DESCONTO PADR�O SE DESMARCAR ESSE ITENS !</b></font>';
                $disabled_queima    = '';//Essa vari�vel influenciar� em um Option + abaixo desse arquivo ...
                $disabled_option1   = 'disabled';
                $checked_option1    = '';
            }else {
                $disabled_queima    = 'disabled';//Essa vari�vel influenciar� em um Option + abaixo desse arquivo ...
                $disabled_option1   = '';
                $checked_option1    = 'checked';
            }
        ?>
            <input type='radio' name='opt_opcao' value='1' title='Desconto Padr�o' id='label' <?=$disabled_option1;?> <?=$disabled_desconto_padrao;?> <?=$checked_option1;?>>
            <label for='label'>Desconto Padr�o</label>
            <?=$aviso;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='2' title='Transportar p/ outro Or�amento (Mesmo Cliente)' id='label2' <?=$disabled;?>>
            <label for='label2'>Transportar p/ outro Or�amento (Mesmo Cliente)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='3' title='Clonar Or�amento' id='label3'>
            <label for='label3'>Clonar Or�amento</label>
        </td>
    </tr>
    <?
        if(strtoupper($congelar) == 'S') $disabled = 'disabled';
/*Se o Status do Orc for igual a 2, significa que o Or�amento j� foi totalmente importado para pedido,
sendo assim n�o posso gerar pedido deste*/
        if($status == 2) {
            $disabled   = 'disabled';
            $aviso      = '<font color="red"><b> (ESTE OR�AMENTO J� FOI TOTALMENTE IMPORTADO) </b></font>';
        }else {
            /*
            1) O Orc tem que possuir itens ...
            2) Se o Orc Possui algum Item na Situa��o de OR�AR ou DEP. T�CNICO ...
            3) Com Custo Bloqueado ...
            4) Com algum Item 'ESP' e que esteje sem Pzo. T�cnico ...*/
            $sql = "SELECT ovi.`id_orcamento_venda_item` 
                    FROM `orcamentos_vendas_itens` ovi 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
                    WHERE ovi.`id_orcamento_venda` = '$_GET[id_orcamento_venda]' 
                    AND (ovi.`preco_liq_fat_disc` <> '' OR pa.`status_custo` = '0' OR (pa.`referencia` = 'ESP' AND ovi.`prazo_entrega_tecnico` = '0.0')) LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 1) {
                $disabled   = 'disabled';
                $aviso      = '<font color="red"><b> (EXISTEM ITENS SEM CUSTO / BLOQUEADO / SEM PRAZO DE DEPTO. T�CNICO) </b></font>';
            }else {
                if($data_validade_orc >= date('Y-m-d')) {
                    /*Esses s�o os �nicos funcion�rios que podem mudar a gera��o de Pedido com itens de Or�amento acima de 200% de Margem de Lucro
                    Roberto 62, D�rcio 98 porque programa, Nishimura 136 ...*/
                    $vetor_funcionarios_podem_gerar_pedido_com_ml_acima_200 = array(62, 68, 98, 136);

                    //Qualquer outro funcion�rio ter� que fazer essa verifica��o ...
                    if(!in_array($_SESSION['id_funcionario'], $vetor_funcionarios_podem_gerar_pedido_com_ml_acima_200)) {
                        //Verifico se existe algum item de Or�amento em que a ML seja >= 200% na gravada "margem_lucro" ou "margem_lucro_estimada" ...
                        $sql = "SELECT `id_orcamento_venda_item` 
                                FROM `orcamentos_vendas_itens` 
                                WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' 
                                AND (`margem_lucro` >= '200' OR `margem_lucro_estimada` >= '200') LIMIT 1 ";
                        $campos = bancos::sql($sql);
                        if(count($campos) == 1) {
                            $disabled   = 'disabled';
                            $aviso      = '<font color="darkred" size="2"><b> (FALAR COM A GER�NCIA !!! EXISTEM ITENS CUJA MARGEM DE LUCRO OU MARGEM DE LUCRO ESTIMADA S�O >= 200%) </b></font>';
                        }else {
                            $disabled   = '';
                            $aviso      = '';
                        }
                    }else {
                        $disabled   = '';
                        $aviso      = '';
                    }
                }else {
                    /*Se o Or�amento estiver dentro do Prazo de Validade ou mesmo que esse Prazo de Validade j� 
                    tenha passado, se os Funcion�rios Logados forem 62 - Roberto, D�rcio 98 ou Nishimura 136 estes 
                    podem estar gerando Pedido independente da Situa��o ...*/
                    $disabled   = ($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136) ? '' : 'disabled';
                    $aviso      = '<font color="red"><b> (OR�AMENTO FORA DA DATA DE VALIDADE) - '.(int)$dias_validade.' DIAS</b></font>';
                }
            }
        }
?>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='4' title='Gerar Pedido' id='label4' <?=$disabled;?>>
            <label for='label4'>Gerar Pedido</label><?=$aviso;?>
        </td>
    </tr>
<?
/*Aqui � uma verifica��o para habilitar essa op��o, se o or�amento corrente estiver congelado ou o Cliente for Estrangeiro, 
ent�o n�o posso adaptar ou retirar Promo��o para nenhum Item ... */
    $disabled_adaptar_retirar_promocao = (strtoupper($congelar) == 'S' || $id_pais != 31) ? 'disabled' : '';
?>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='5' title='Adaptar / Retirar Promo��o p/ todos os Itens do Or�amento' id='label5' <?=$disabled_adaptar_retirar_promocao;?>>
            <label for='label5'>
                <font color='red'>
                    <b>Adaptar / Retirar Promo��o p/ todos os Itens do Or�amento</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='6' title='Incluir Atendimento Di�rio' id='label6'>
            <label for='label6'>Incluir Atendimento Di�rio</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='7' title='Enviar e-mail p/ Depto. T�cnico Incluir Novos PA(s)' id='label7'>
            <label for='label7'>Enviar e-mail p/ Depto. T�cnico Incluir Novos PA(s)</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <?
                $sql = "SELECT `id_orcamento_venda_item` 
                        FROM `orcamentos_vendas_itens` 
                        WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' 
                        AND `id_opc_item` = '0' LIMIT 1 ";
                $campos_itens_sem_opc   = bancos::sql($sql);
                $qtde_itens_sem_opc     = count($campos_itens_sem_opc);
                //N�o � poss�vel importar uma OPC, caso exista algum item de Or�amento que foi gerado sem OPC(s) ...
                $disabled_opc = ($qtde_itens_sem_opc > 0) ? 'disabled' : '';
            ?>
            <input type='radio' name='opt_opcao' value='8' title='Importar OPC' id='label8' <?=$disabled_opc;?>>
            <label for='label8'>Importar OPC</label>&nbsp;<b>(Apenas em Orc(s) s/ Itens)</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='9' title='Retirar Excesso de Estoque de todos Itens de Or�amento' id='label9' <?=$disabled_queima;?>>
            <label for='label9'>Retirar <b>EXCESSO DE ESTOQUE</b> de todos Itens de Or�amento</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='10' title='Incluir / Alterar C�digo Produto do Cliente' id='label10'>
            <label for='label10'>Incluir / Alterar C�digo(s) Produto(s) do Cliente</label>
        </td>
    </tr>
<?
/*S� mostra esse bot�o e checkbox p/ os usu�rios Rivaldo 27, Rodrigo Soares 54, Roberto 62, Fabio Petroni 64, 
D�rcio 98 'pq programa' e Nishimura 136 ...*/
        if($_SESSION['id_funcionario'] == 27 || $_SESSION['id_funcionario'] == 54 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 136) {
?>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='11' title='Atualizar Pre�os pela Lista de Compras JT' id='label11'>
            <label for='label11'>Atualizar Pre�os pela Lista de Compras JT</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <?
                //Se o Or�amento estiver congelado ou a sua Data de Validade tiver expirado travo o Option ...
                if(strtoupper($congelar) == 'S' || (date('Y-m-d') > $data_validade_orc)) $disabled_ignorar_lote_minimo_do_grupo_faixa_orcavel = 'disabled';
            ?>
            <input type='radio' name='opt_opcao' value='12' title='Ignorar Lote M�nimo do Grupo Faixa Or��vel' id='label12' <?=$disabled_ignorar_lote_minimo_do_grupo_faixa_orcavel;?>>
            <label for='label12'>Ignorar Lote M�nimo do Grupo Faixa Or��vel</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <?
                $checked = ($comprar_como_export == 'S') ? 'checked': '';
            ?>
            <input type='checkbox' name='chkt_comprar_como_export' id='chkt_comprar_como_export' value='S' title='Comprar como Export' onclick='comprar_como_export()' class='checkbox' <?=$checked;?> <?=$disabled;?>>
            <label for='chkt_comprar_como_export'>
                <font color='darkblue'>
                    <b>COMPRAR COMO EXPORT</b>
                </font>
            </label>
            <label id='lbl_mensagem'></label>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_avan�ar' value='&gt;&gt; Avan�ar &gt;&gt;' title='Avan�ar' onclick='avancar()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observa��o:</font></b>
<pre>
* Or�amentos com algum Item em Excesso de Estoque, tem validade m�xima de 3 dias.
</pre>