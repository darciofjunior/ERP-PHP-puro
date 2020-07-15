<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/custos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro da Vendas ...
require('../../../../lib/intermodular.php');//Esse arquivo ñ pode ser retirado, pq a biblioteca Vendas utiliza uma função deste ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>DESCONTO PADRÃO ALTERADO COM SUCESSO.</font>";

//Verifica se o Orçamento não foi congelado
$sql = "SELECT `id_cliente`, `congelar` 
        FROM `orcamentos_vendas` 
        WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
$campos     = bancos::sql($sql);
$id_cliente = $campos[0]['id_cliente'];
$congelar   = $campos[0]['congelar'];
if(strtoupper($congelar) == 'S') {
?>
<html>
<head>
<title>.:: Desconto Padrão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body>
<table width='60%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center' class='erro'>
        <td colspan='2'>
            ORÇAMENTO CONGELADO
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="window.close()" style="color:red" class='botao'>
        </td>
    </tr>
</table>
</body>
</html>
<?
    exit;
}

//Se essa opção NÃO estiver marcada, então eu só atribui o "Desconto Extra %" p/ os Itens que não estejam em Prom.
if(empty($_POST['hdd_desc_itens_promocionais'])) {
    $somente_itens_sem_promocao = " AND `promocao` = 'N' ";
}else {
    $atualizar_itens_para_sem_promocao = ", `promocao` = 'N' ";
}

if(!empty($_POST['txt_aliquota_percentagem'])) {//Atualizando o Acréscimo Extra Porc
    /*Como esse processamento pode ser muito pesado, deixo o servidor operar excepcionalmente em até 
    20 minutos para essa tela ...*/
    set_time_limit(1200);
    
    //Busco todos os itens do Orçamento passado por parâmetro, p/ rodar a função de "preco_liq_final_item_orc" ...
    $sql = "SELECT `id_orcamento_venda_item`, `acrescimo_extra` 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda` = '$_POST[id_orcamento_venda]' ";
    $campos_itens = bancos::sql($sql);
    $linhas_itens = count($campos_itens);
    
    if($_POST['opt_opcao'] == 1) {//Desconto Extra % ...
        for($i = 0; $i < $linhas_itens; $i++) {
            //Sobrepõe o valor de Desconto Extra e zera o Acréscimo Extra do item do Orçamento ...
            $sql = "UPDATE `orcamentos_vendas_itens` SET `desc_extra` = '$_POST[txt_aliquota_percentagem]', `acrescimo_extra` = '0.00' $atualizar_itens_para_sem_promocao WHERE `id_orcamento_venda_item` = '".$campos_itens[$i]['id_orcamento_venda_item']."' $somente_itens_sem_promocao ";
            bancos::sql($sql);
            /*******************************************************************************************************/
            vendas::calculo_preco_liq_final_item_orc($campos_itens[$i]['id_orcamento_venda_item']);
//Aqui eu atualizo a ML Est do Iem do Orçamento ...
            custos::margem_lucro_estimada($campos_itens[$i]['id_orcamento_venda_item']);
/*************Rodo a função de Comissão depois de ter gravado a ML Estimada*************/
            vendas::calculo_ml_comissao_item_orc($_POST['id_orcamento_venda'], $campos_itens[$i]['id_orcamento_venda_item']);
        }
    }else if($_POST['opt_opcao'] == 2) {//Acréscimo Extra % ...
        for($i = 0; $i < $linhas_itens; $i++) {
            //Zera o valor de Desconto Extra e sobrepõe o Acréscimo Extra do item do Orçamento ...
            $sql = "UPDATE `orcamentos_vendas_itens` SET `desc_extra` = '0.00', `acrescimo_extra` = '$_POST[txt_aliquota_percentagem]' $atualizar_itens_para_sem_promocao WHERE `id_orcamento_venda_item` = '".$campos_itens[$i]['id_orcamento_venda_item']."' $somente_itens_sem_promocao ";
            bancos::sql($sql);
            /*******************************************************************************************************/
            vendas::calculo_preco_liq_final_item_orc($campos_itens[$i]['id_orcamento_venda_item']);
//Aqui eu atualizo a ML Est do Iem do Orçamento ...
            custos::margem_lucro_estimada($campos_itens[$i]['id_orcamento_venda_item']);
/*************Rodo a função de Comissão depois de ter gravado a ML Estimada*************/
            vendas::calculo_ml_comissao_item_orc($_POST['id_orcamento_venda'], $campos_itens[$i]['id_orcamento_venda_item']);
        }
    }else if($_POST['opt_opcao'] == 3) {//Desconto à Vista %, o valor digitado "tem q ser transformado em negativo" ...
        for($i = 0; $i < $linhas_itens; $i++) {
            /*Se esse checkbox estiver marcado, então o sistema pega o Valor digitado pelo Usuário e joga na coluna de 
            Acréscimo com o Valor Invertido "Negativo", sobrepondo os Valores existentes nesse campo ...*/
            if($_POST['opt_acrescimo_existente'] == 1) {//Ignorar Acréscimos Existentes ...
                $novo_acrescimo_extra = ($_POST['txt_aliquota_percentagem'] * -1);
            }else {//Manter Acréscimos Existentes ...
                /*Nesse caso nós não zeramos ou sobrepomos o Acréscimo Extra como fazemos nas outras opções de 
                Desconto Padrão, apenas Descontamos do Acréscimo extra existente o Desconto à Vista ...*/
                $novo_acrescimo_extra = ((1 + $campos_itens[$i]['acrescimo_extra'] / 100) * (1 - $_POST['txt_aliquota_percentagem'] / 100) - 1) * 100;
            }
            //Zero a ML Estimada porque mexe com o Pço de Venda e consequentemente a ML ...
            $sql = "UPDATE `orcamentos_vendas_itens` SET `acrescimo_extra` = '$novo_acrescimo_extra' WHERE `id_orcamento_venda_item` = '".$campos_itens[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
            bancos::sql($sql);
/*******************************************************************************************************/
            vendas::calculo_preco_liq_final_item_orc($campos_itens[$i]['id_orcamento_venda_item']);
//Aqui eu atualizo a ML Est do Iem do Orçamento ...
            custos::margem_lucro_estimada($campos_itens[$i]['id_orcamento_venda_item']);
/*************Rodo a função de Comissão depois de ter gravado a ML Estimada*************/
            vendas::calculo_ml_comissao_item_orc($_POST['id_orcamento_venda'], $campos_itens[$i]['id_orcamento_venda_item']);
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = '/erp/albafer/modulo/vendas/orcamentos/itens/desconto_padrao.php?id_orcamento_venda=<?=$_POST['id_orcamento_venda'];?>&valor=1'
    </Script>
<?
    exit;
}

$taxa_financeira_vendas = genericas::variavel(16);

$vetor_valores          = vendas::preco_minimo_venda(0, $_GET['id_orcamento_venda']);
$desconto_maximo_venda  = $vetor_valores['desconto_maximo_venda'];

//Através do $id_cliente busco o primeiro Desconto do Cliente ...
$sql = "SELECT c.`id_cliente_tipo`, cr.`desconto_cliente` 
        FROM `clientes_vs_representantes` cr 
        INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
        WHERE cr.`id_cliente` = '$id_cliente' LIMIT 1 ";
$campos             = bancos::sql($sql);
$id_cliente_tipo    = $campos[0]['id_cliente_tipo'];
$desconto_cliente   = $campos[0]['desconto_cliente'];
?>
<html>
<head>
<title>.:: Desconto Padrão ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Alíquota % ...
    if(!texto('form', 'txt_aliquota_percentagem', '1', '1234567890,.', 'ALÍQUOTA %', '1')) {
        return false
    }
    var aliquota_percentagem    = eval(strtofloat(document.form.txt_aliquota_percentagem.value))
    var desconto_maximo_venda   = eval('<?=$desconto_maximo_venda;?>')
    var id_funcionario          = eval('<?=$_SESSION['id_funcionario'];?>')
    
    if(document.form.opt_opcao[0].checked == true) {//Desconto Extra % ...
        var desconto_cliente    = eval('<?=$desconto_cliente;?>')
        var desconto_total      = eval((1 - (1 - desconto_cliente / 100) * (1 - aliquota_percentagem / 100)) * 100)
        
        //Nunca a Alíquota digitada poderá ser maior do que o Desconto Máximo de Venda ...
        if(desconto_total > desconto_maximo_venda) {
            //Gerência de Vendas tem autorização -> Roberto 62, Wilson Chefe 68, "Dárcio 98 só pq programa" e Nishimura 136 ...
            if(id_funcionario == 62 || id_funcionario == 68 || id_funcionario == 98 || id_funcionario == 136) {
                var resposta = confirm('O DESCONTO TOTAL ESTA ACIMA DO MÁXIMO PERMITIDO !!!\n\n DESEJA CONTINUAR !')
                if(resposta == false) {
                    document.form.txt_aliquota_percentagem.focus()
                    document.form.txt_aliquota_percentagem.select()
                    return false
                }
            }else {
                alert('O DESCONTO TOTAL ESTA ACIMA DO MÁXIMO PERMITIDO !!!\n\n CONTATE A GERÊNCIA !')
                document.form.txt_aliquota_percentagem.focus()
                document.form.txt_aliquota_percentagem.select()
                return false
            }
        }
    }else if(document.form.opt_opcao[2].checked == true) {//Desconto à Vista % ...
        /*Se a opção "Desconto à Vista" estiver selecionada, então o sistema força o usuário a selecionar 
        uma opção de "Acréscimo Existente" ...*/
        if(document.form.opt_acrescimo_existente[0].checked == false && document.form.opt_acrescimo_existente[1].checked == false) {
            alert('SELECIONE UMA OPÇÃO DE "ACRÉSCIMO EXISTENTE" !')
            document.form.opt_acrescimo_existente[0].focus()
            return false
        }
        var taxa_financeira_vendas  = eval('<?=$taxa_financeira_vendas;?>')
        //Nunca a Alíquota digitada poderá ser maior do que a Taxa Financeira de Vendas ...
        if(aliquota_percentagem > taxa_financeira_vendas) {
            alert('O DESCONTO À VISTA ESTÁ ACIMA DA TAXA FINANCEIRA DE VENDAS !')
            document.form.txt_aliquota_percentagem.focus()
            document.form.txt_aliquota_percentagem.select()
            return false
        }
    }

    if(document.form.opt_opcao[0].checked == true) {//Desconto Extra % ...
        alert('O ACRÉSCIMO EXTRA / DESCONTO À VISTA SERÃO ZERADOS !')
    }else if(document.form.opt_opcao[1].checked == true) {//Acréscimo Extra % ...
        alert('O DESCONTO EXTRA / DESCONTO À VISTA SERÃO ZERADOS !')
    }

    resposta = confirm('TAMBÉM DESEJA ATRIBUIR DESCONTO / ACRÉSCIMO EXTRA PARA O(S) ITEM(NS) EM PROMOÇÃO ?\n\nSE SIM O SISTEMA DESMARCARÁ O(S) ITENS PROMOCIONAI(S) !')
    if(resposta == true) document.form.hdd_desc_itens_promocionais.value = 1
    //Aqui é para não atualizar a Tela abaixo desse Pop-UP Div ...
    document.form.nao_atualizar.value       = 1
    //Travo o botão para que o usuário não fique submetendo mais de uma vez ...
    document.form.cmd_atualizar.disabled    = true
    document.form.cmd_atualizar.className   = 'textdisabled'
    limpeza_moeda('form', 'txt_aliquota_percentagem, ')
}

function controlar() {
    var id_funcionario  = eval('<?=$_SESSION['id_funcionario'];?>')
    var id_cliente_tipo = eval('<?=$id_cliente_tipo;?>')
    /*Esses são os únicos funcionários que podem mudar o desconto 
    Extra: Roberto 62, Wilson 68, Dárcio 98, Nishimura 136 ...*/
    var vetor_funcionarios_podem_mudar_desconto_extra = [62, 68, 98, 136]
    
    if(document.form.opt_opcao[0].checked == true) {//Se o usuário selecionou a opção Desconto Extra % ...
        //Em qualquer tipo de Cliente é permitido dar desconto, com exceção de Indústria que só podem os funcionários descritos no Vetor acima ...
        if(id_cliente_tipo != 4 || (id_cliente_tipo == 4 && vetor_funcionarios_podem_mudar_desconto_extra.indexOf(id_funcionario) > -1)) {
            document.form.txt_aliquota_percentagem.className    = 'caixadetexto'
            document.form.txt_aliquota_percentagem.disabled     = false
        }else {
            document.form.txt_aliquota_percentagem.className    = 'textdisabled'
            document.form.txt_aliquota_percentagem.disabled     = true
        }
    }else if(document.form.opt_opcao[1].checked == true) {//Se o usuário selecionou a opção Acréscimo Extra % ...
        document.form.txt_aliquota_percentagem.className    = 'caixadetexto'
        document.form.txt_aliquota_percentagem.disabled     = false
        
        //Desabilito as opções de Acréscimo ...
        document.form.opt_acrescimo_existente[0].disabled   = true
        document.form.opt_acrescimo_existente[1].disabled   = true
    }else {//Se o usuário selecionou a opção de Desconto à Vista % ...
        //Se o Cliente é Indústria, nunca podemos dar Desconto Extra, a não ser estes ...
        if(id_cliente_tipo == 4 && vetor_funcionarios_podem_mudar_desconto_extra.indexOf(id_funcionario) > -1) {
            //Habilito as opções de Acréscimo ...
            document.form.opt_acrescimo_existente[0].disabled   = false
            document.form.opt_acrescimo_existente[1].disabled   = false

            document.form.txt_aliquota_percentagem.className    = 'caixadetexto'
            document.form.txt_aliquota_percentagem.disabled     = false
        }else {
            //Desabilito as opções de Acréscimo ...
            document.form.opt_acrescimo_existente[0].disabled   = true
            document.form.opt_acrescimo_existente[1].disabled   = true
            
            document.form.txt_aliquota_percentagem.className    = 'textdisabled'
            document.form.txt_aliquota_percentagem.disabled     = true
        }
    }
    if(document.form.txt_aliquota_percentagem.disabled == false) document.form.txt_aliquota_percentagem.focus()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        parent.ativar_loading()
        parent.html5Lightbox.finish()
    }
}
</Script>
</head>
<body onload='controlar()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_orcamento_venda' value='<?=$id_orcamento_venda;?>'>
<!--Controle de Tela-->
<input type='hidden' name='hdd_desc_itens_promocionais'>
<input type='hidden' name='nao_atualizar'>
<!--****************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Desconto Padrão
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <input type='radio' name='opt_opcao' id='opt_opcao1' value='1' onclick='controlar()' checked>
            <label for='opt_opcao1'>
                Desconto Extra %
            </label>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <input type='radio' name='opt_opcao' id='opt_opcao2' value='2' onclick='controlar()'>
            <label for='opt_opcao2'>
                Acréscimo Extra %
            </label>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <input type='radio' name='opt_opcao' id='opt_opcao3' value='3' onclick='controlar()'>
            <label for='opt_opcao3'>
                Desconto à Vista %
            </label>
            <font color='yellow'>
                (Escolha uma das 2 opções abaixo)
            </font>
            <?
                $pz_medio_estipulado = 7;
                
                $sql = "SELECT `prazo_medio` 
                        FROM `orcamentos_vendas` 
                        WHERE `id_orcamento_venda` = '$_GET[id_orcamento_venda]' LIMIT 1 ";
                $campos_prazo_medio = bancos::sql($sql);
                //Coloquei esta claúsula pq consideramos como pagto. à Vista até no máximo 7 dias ...
                $disabled = ($campos_prazo_medio[0]['prazo_medio'] <= $pz_medio_estipulado) ? '' : 'disabled';
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_acrescimo_existente' id='opt_acrescimo_existente1' value='1' disabled>
            <label for='opt_acrescimo_existente1'>
                <font color='red'>
                    <b>"Ignorar Acréscimos Existentes" (Apenas esse Novo Desconto à Vista será considerado)</b>
                </font>
            </label>
            <br/>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_acrescimo_existente' id='opt_acrescimo_existente2' value='2' disabled>
            <label for='opt_acrescimo_existente2'>
                <font color='red'>
                    <b>"Manter Acréscimos Existentes" e Adicionar o Novo Desconto à Vista</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='15%'>
            Alíquota %: 
        </td>
        <td width='85%'>
            <input type='text' name='txt_aliquota_percentagem' title='Digite a Alíquota %' size='15' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="document.form.nao_atualizar.value = 1;window.location = 'outras_opcoes.php?id_orcamento_venda=<?=$id_orcamento_venda;?>'" class='botao'>
            <input type='submit' name='cmd_atualizar' value='Atualizar' title='Atualizar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Desconto à Vista apenas p/ Prazo Médio <= 7 ddl.

* Taxa Financeira de Vendas p/ 30 ddl = <font color='darkblue'><b><?=number_format($taxa_financeira_vendas, 2, ',', '.');?> %.</b></font>
</pre>