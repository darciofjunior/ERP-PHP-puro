<?
//Se essa tela foi aberta como sendo Pop-UP então faço um Require da Biblioteca de Bancos ...
if($pop_up == 1) require('../../../lib/segurancas.php');
session_start('funcionarios');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Salva os dados na tabela de clientes quando fecha o pop-up
//Seleção dos Dados do Cliente
    $sql = "SELECT `nomefantasia`, `razaosocial` 
            FROM `clientes` 
            WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Cliente(s) vs Representante(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link rel = 'stylesheet' type = 'text/css' href = '../../../css/layout.css'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos                       = document.form.elements
//Controle p/ selecionar todos os Representantes de todas as Empresas Divisões ...
    var representantes_nao_selecionados = 0

    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'select-one') {
            if(elementos[i].value == '') {//Se encontrou uma Empresa Divisão sem Representante selecionado ...
                representantes_nao_selecionados++
                break;//Sai fora do Loop ...
            }
        }
    }
    if(representantes_nao_selecionados == 1) {
        alert('EXISTE(M) EMPRESA(S) DIVISÃO(ÕES) SEM REPRESENTANTE(S) SELECIONADO(S) !!!\n\nSELECIONE UM REPRESENTANTE PARA CADA EMPRESA DIVISÃO !')
        return false
    }    
//Nessa parte o sistema trata os campos de Desconto do Cliente p/ gravar no Banco de Dados ...
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text') {
/*Verifica se no nome tem um símbolo de colchete para saber se já são os texts
de arrays de desconto de clientes*/
            if(elementos[i].name.indexOf('[') != -1) {
//Significa que estou na prim. caixa e que vou armazenar o valor deste p/ as outras
                elementos[i].value = strtofloat(elementos[i].value)
            }
        }
    }
/*************************************Controles com as Empresas Divisões*************************************/
    var cabri = elementos['cmb_representante[]'][0].value
    var heinz = elementos['cmb_representante[]'][1].value
    var warrior = elementos['cmb_representante[]'][2].value
    var tool = elementos['cmb_representante[]'][3].value
    var nvo = elementos['cmb_representante[]'][4].value
//Sempre o Sistema força o Representante da Divisão Tool e NVO sempre devem ser o mesmo ...
    if(tool != nvo) {
        alert('O REPRESENTANTE DA DIVISÃO TOOL É DIFERENTE DO REPRESENTANTE DA DIVISÃO NVO !\n\nPOR FAVOR COLOQUE O MESMO REPRESENTANTE PARA ESSA(S) DIVISÃO(ÕES) !!!')
        return false
    }
//Sempre o Sistema aconselha q o melhor seria q os Rep(s) da Divisão Cabri, Heinz e Warrior fossem os mesmos ...
    if((cabri != heinz) || (cabri != warrior) || (heinz != warrior)) {
        alert('SERIA MELHOR SE O(S) REPRESENTANTE(S) DA(S) DIVISÃO(ÕES):\n\nCABRI, HEINZ E WARRIOR FOSSE(M) O(S) MESMO(S) !!!')
    }
/************************************************************************************************************/
//Aqui serve para não submeter
    if(document.form.controle.value == 0) return false
//Aqui é para não atualizar a tela abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
//Desabilito o botão de Salvar para que o Usuário não fique submetendo várias vezes essa Tela ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
    atualizar_abaixo()
    document.form.submit()
}

function atualizar_combos() {
    var elementos = document.form.elements
    var contador = 0
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'select-one') {
/*Verifica se no nome tem um símbolo de colchete para saber se já são os combos
de arrays de representantes*/
            if(elementos[i].name.indexOf('[') != -1) {
//Significa que estou no prim. combo e que vou armazenar o valor deste p/ os outros
                if(contador == 0) {
                    valor_combo = elementos[i].value
                    contador++
                }else {
                    elementos[i].value = valor_combo
                }
            }
        }
    }
}

function atualizar_caixas() {
    var elementos = document.form.elements
    var contador = 0
    for(i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'text') {
/*Verifica se no nome tem um símbolo de colchete para saber se já são os texts
de arrays de desconto de clientes*/
            if(elementos[i].name.indexOf('[') != -1) {
//Significa que estou na prim. caixa e que vou armazenar o valor deste p/ as outras
                if(contador == 0) {
                    valor_caixa = elementos[i].value
                    contador++
                }else {
                    elementos[i].value = valor_caixa
                }
            }
        }
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.top.opener.document.form.submit()
}
</Script>
</head>
<?
//Só irá executar essa Função se essa Tela foi aberta como sendo Pop-UP ...
if($pop_up == 1) {$onunload = 'onunload="atualizar_abaixo()"';}
?>
<body onload='document.form.elements[0].focus()' <?=$onunload;?>>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit='return validar()'>
<!--**********************************Controles de Tela**********************************-->
<input type='hidden' name='id_cliente' value='<?=$_GET['id_cliente'];?>'>
<input type='hidden' name='nao_atualizar'>
<!--Se Pop-Up = 1, então significa que essa Tela foi aberta como sendo um Pop-UP-->
<input type='hidden' name='pop_up' value="<?=$_GET['pop_up'];?>">
<!--Caixa que faz controle p/ submeter a tela de Cliente-->
<input type='hidden' name='controle' value='1'>
<!--*************************************************************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='atencao' align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Cliente(s) vs Representante(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Razão Social:</b>
        </td>
        <td colspan='2'>
            <b>Nome Fantasia:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$campos[0]['razaosocial'];?>
        </td>
        <td colspan='2'>
            <?=$campos[0]['nomefantasia'];?>
        </td>
    </tr>
</table>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
<?
//Aqui faz a verificação das Empresas Divisões cadastradas no Sistema ...
    $sql = "SELECT id_empresa_divisao, razaosocial 
            FROM `empresas_divisoes` 
            WHERE `ativo` = '1' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
    <tr class='linhadestaque'>
        <td colspan='3'>
            <font color='#FFFF00'>
                <b><i>Representante(s)</i></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Divisão(ões)</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Representante(s)</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Desconto do Cliente</i></b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal'>
        <td>
            <?=$campos[$i]['razaosocial'];?>
            <input type='hidden' name='hdd_empresa_divisao[]' value='<?=$campos[$i]['id_empresa_divisao']?>'>
        </td>
        <td>
            <select name='cmb_representante[]' title='Selecione um Representante' class='combo'>
            <?
                //Verifica se a empresa divisão atual do loop está atrelada ao cliente
                $sql = "SELECT id_representante, desconto_cliente 
                        FROM `clientes_vs_representantes` 
                        WHERE `id_cliente` = '$_GET[id_cliente]' 
                        AND `id_empresa_divisao` = ".$campos[$i]['id_empresa_divisao']." LIMIT 1 ";
                $campos_rep = bancos::sql($sql);
                //Busca os representanes do Cadastro ...
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql, $campos_rep[0]['id_representante']);
            ?>
            </select>
<?
//Somente para a primeira linha
            if($i == 0) {
?>
                &nbsp;<img src="../../../imagem/seta_abaixo.gif" width="12" height="12" title="Copiar Representante" alt="Copiar Representante" onclick="atualizar_combos()">
<?
            }
?>
        </td>
        <td>
<?
                if(count($campos_rep) == 1) {
                    $desconto_cliente = number_format($campos_rep[0]['desconto_cliente'], 2, ',', '.');
                }else {
                    $desconto_cliente = number_format(0, 2, ',', '.');
                }
?>
            <input type="text" name="txt_desconto_cliente[]" value="<?=$desconto_cliente;?>" title="Selecione um Desconto Cliente" onkeyup="verifica(this, 'moeda_especial', '2', '1', event)" size="12" maxlength="10" class="caixadetexto">
<?
//Somente para a primeira linha
            if($i == 0) {
?>
                &nbsp;<img src="../../../imagem/seta_abaixo.gif" width="12" height="12" title="Copiar Representante" alt="Copiar Representante" onclick="atualizar_caixas()">
<?
            }
?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
        <?
            if($pop_up != 1) {//Só irá mostrar o Botão de Voltar quando essa Tela for aberta como sendo normal ...
        ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'clientes_vs_representantes.php<?=$parametro;?>'" class='botao'>
        <?
            }
        ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.elements[0].focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_repassar_pedidos' value='Repassar Pedidos' title='Repassar Pedidos' onclick="html5Lightbox.showLightbox(7, '../../classes/cliente/repassar_pedidos.php?id_cliente=<?=$_GET['id_cliente'];?>&cmb_representante_atual='+document.form.elements['cmb_representante[]'][0].value)" style='color:black' class='botao'>
        <?
            if($pop_up == 1) {//Só irá mostrar o Botão de Fechar se essa Tela foi aberta como sendo Pop-UP ...
        ?>	
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        <?
            }
        ?>
        </td>
    </tr>
    <tr>
        <td colspan='3'>
            <font color='red'>
                <br/>
                &nbsp;*** <b><i><u>INDEPENDENTE</u></i></b> de alterar o Representante ou Desconto do Cliente na(s) Empresa(s) Divisão(ões) acima, 
                ao clicar no botão Salvar o sistema atualizará todo(s) o(s) Orçamento(s) <b>DESCONGELADOS e que estejam com sua Data de Emissão dentro 
                do Prazo de Validade</b> desse Cliente com a(s) informação(ões) selecionada(s) / digitada(s).
                <p class='piscar'>
                    <font color='darkblue'>
                        Obs: Só afetará os item(ns) Normal(is) de Linha ...
                    </font>
                </p>
            </font>
            &nbsp;****** Certifique-se de descongelar algum Orçamento antes se realmente necessário aplicar essas mudança(s).
            <!--Tenho q colocar a função depois, pq senão não é reconhecida a Tag "P" que foi criada antes usando o atributo Piscar ...-->
            <Script Language = 'JavaScript'>  
                function blink(selector) {
                    $(selector).fadeOut('slow', function() {
                        $(this).fadeIn('slow', function() {
                            blink(this);
                        });
                    });
                }
                blink('.piscar');
            </Script>
        </td>
    </tr>
</table>
</body>
</html>	
<?
}else if($passo == 2) {
    /*Se existirem muitos itens de Orçamento a serem atualizados devido as mudanças abaixo de Representante 
    ou Desconto do Cliente, isso faz com que o sistema fique muito pesado e trave a tela não concluindo
    toda a Rotina, então sendo assim aumentei o timer em específico p/ essa Rotina = 300 segundos ...*/
    set_time_limit(300);
/*******************************************************************************/
//Tratamento com o Representante em toda(s) as Empresa(s) Divisão(ões) ...
    foreach ($_POST['cmb_representante'] as $i => $id_representante) {
        $atualizar_representante_desconto = 'N';//Valor Inicial ...
        
        /*Verifico o Cliente e Desconto atual que estão cadastrados na respectiva Empresa Divisão 
        p/ saber se houve alguma mudança ...*/
        $sql = "SELECT `id_cliente_representante`, `id_representante`, `desconto_cliente` 
                FROM `clientes_vs_representantes` 
                WHERE `id_cliente` = '$_POST[id_cliente]' 
                AND `id_empresa_divisao` = '".$_POST['hdd_empresa_divisao'][$i]."' LIMIT 1 ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas == 0) {
            /*Não existe esta Empresa Divisão do Loop p/ este Cliente, sendo assim vou atrelá-la 
            com o Representante ...*/
            $sql = "INSERT INTO `clientes_vs_representantes` (`id_cliente_representante`, `id_cliente`, `id_representante`, `id_empresa_divisao`, `desconto_cliente`) VALUES (NULL, '$_POST[id_cliente]', '$id_representante', '".$_POST['hdd_empresa_divisao'][$i]."', '".$_POST['txt_desconto_cliente'][$i]."') ";
            bancos::sql($sql);
        }else {//Já existe esta Empresa Divisão do Loop p/ este Cliente, só vou alterar o Representante ...
            //Representa que realmente houve uma alteração ou de Representante ou de Desconto do Cliente ...
            if(($campos[0]['id_representante'] != $id_representante) || ($campos[0]['desconto_cliente'] != $_POST['txt_desconto_cliente'][$i])) {
                $sql = "UPDATE `clientes_vs_representantes` SET `id_representante` = '$id_representante', `desconto_cliente` = '".$_POST['txt_desconto_cliente'][$i]."' WHERE `id_cliente_representante` = '".$campos[0]['id_cliente_representante']."' LIMIT 1 ";
                bancos::sql($sql);
                $atualizar_representante_desconto = 'S';
            }
        }
        /************************************************************************************************************/
        if($atualizar_representante_desconto == 'S') {
            /*O sistema atualiza o Representante e Desconto de todos os Orçamentos descongelados do Cliente 
            e das Divisões que foram submetidas, OBS: Somente Produtos Normais de Linha ...*/
            $sql = "SELECT ov.`id_orcamento_venda`, ovi.`id_orcamento_venda_item` 
                    FROM `orcamentos_vendas` ov 
                    INNER JOIN `orcamentos_vendas_itens` ovi ON ovi.`id_orcamento_venda` = ov.`id_orcamento_venda` AND ov.congelar = 'N' 
                    INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` AND pa.`referencia` <> 'ESP' 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` AND ged.`id_empresa_divisao` = '".$_POST['hdd_empresa_divisao'][$i]."' 
                    WHERE ov.`id_cliente` = '$_POST[id_cliente]' ORDER BY ov.data_emissao DESC ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
            for($j = 0; $j < $linhas; $j++) {
                //Atualizo o Item de Orçamento com o "id_representante" e "desconto_cliente" submetido na Divisão atual do Loop ...
                $sql = "UPDATE `orcamentos_vendas_itens` SET `id_representante` = '$id_representante', `desc_cliente` = '".$_POST['txt_desconto_cliente'][$i]."' WHERE `id_orcamento_venda_item` = '".$campos[$j]['id_orcamento_venda_item']."' LIMIT 1 ";
                bancos::sql($sql);

                /*Se o Orçamento estiver com sua Data de Emissão dentro do Prazo de Validade 
                atualizo os Custos dos Itens "PAs" desse Orçamento ...*/
                $vetor_dados_gerais     = vendas::dados_gerais_orcamento($campos[$j]['id_orcamento_venda']);
                $data_validade_orc      = $vetor_dados_gerais['data_validade_orc'];

                if($data_validade_orc >= date('Y-m-d')) {
                    /*******************************************************************************************************/
                    /*Função pesadíssima que verifica o Custo do Produto Acabado, Comissão do Representante p/ o determinado 
                    Item de Orçamento, sendo executada desse jeito por item, a mesma já fica um pouco mais leve ...

                    Obs Importantíssima: Eu passo nessa função abaixo esse 3º parâmetro 
                    $mudou_tipo_cliente = 'S' p/ que o Sistema entre de "maneira forçada" no caminho de 
                    Desconto mesmo que por aqui nós não possamos mudar o Tipo de Cliente ...*/
                    vendas::calculo_preco_liq_final_item_orc($campos[$j]['id_orcamento_venda_item'], 'S', 'S');
                    //Aqui eu atualizo a ML Est do Iem do Orçamento ...
                    custos::margem_lucro_estimada($campos[$j]['id_orcamento_venda_item']);
                    /*************Rodo a função de Comissão depois de ter gravado a ML Estimada*************/
                    vendas::calculo_ml_comissao_item_orc($campos[$j]['id_orcamento_venda'], $campos[$j]['id_orcamento_venda_item']);
                }
            }
        }
        /************************************************************************************************************/
    }
?>
    <Script Language = 'Javascript'>
        alert('REPRESENTANTE(S) ALTERADO(S) COM SUCESSO !')
        window.location = '/erp/albafer/modulo/vendas/cliente/clientes_vs_representantes.php'
    </Script>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Cliente(s) vs Representante(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='1700' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='14'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            Cliente(s) vs Representante(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Tp
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Tel Fax
        </td>
        <td>
            Cr
        </td>
        <td>
            E-mail
        </td>
        <td>
            Endereço
        </td>
        <td>
            Cidade
        </td>
        <td>
            Cep
        </td>
        <td>
            País
        </td>
        <td>
            UF
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $credito = financeiros::controle_credito($campos[$i]['id_cliente']);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'clientes_vs_representantes.php?passo=1&id_cliente=<?=$campos[$i]['id_cliente'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com'])) echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com'])) echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com'])) echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com'])) echo $campos[$i]['telcom'];
        ?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax'])) echo $campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            if(!empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax'])) echo $campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].$campos[$i]['telfax'];
            if(empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax'])) echo $campos[$i]['ddi_fax'].$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            if(empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax'])) echo $campos[$i]['telfax'];
        ?>
        </td>
        <td align='center'>
            <font color='blue'>
                <?=$credito;?>
            </font>
        </td>
        <td>
            <?=$campos[$i]['email'];?>
        </td>
        <td>
        <?
            echo $campos[$i]['endereco'];
            //Daí sim printa o complemento
            if(!empty($campos[$i]['endereco'])) echo ', '.$campos[$i]['num_complemento'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['cep'];?>
        </td>
        <td align='center'>
        <?
            $sql = "SELECT `pais` 
                    FROM `paises` 
                    WHERE `id_pais` = ".$campos[$i]['id_pais']." ";
            $campos_pais = bancos::sql($sql);
            echo $campos_pais[0]['pais'];
        ?>
        </td>
        <td align='center'>
        <?
            $sql = "SELECT `sigla` 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
            $campos_uf = bancos::sql($sql);
            echo $campos_uf[0]['sigla'];
        ?>
        </td>
        <td align='center'>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'clientes_vs_representantes.php'" class='botao'>
        </td>
    </tr>
</table>
<table width='1700' border='0' align='center' cellspacing='1' cellpadding='1'>
<tr>
    <td>&nbsp;</td>
</tr>
<tr align='center'>
    <td>
        <?=paginacao::print_paginacao('sim');?>
    </td>
</tr>
</table>
</body>
</html>
<pre>
<font color='red'><b>Legenda dos Tipos de Cliente:</b></font>

 <font color='blue'><b>RA</b></font> -> Revenda Ativa
 <font color='blue'><b>RI</b></font> -> Revenda Inativa
 <font color='blue'><b>CO</b></font> -> Cooperado
 <font color='blue'><b>ID</b></font> -> Indústria
 <font color='blue'><b>AT</b></font> -> Atacadista
 <font color='blue'><b>DT</b></font> -> Distribuidor
 <font color='blue'><b>IT</b></font> -> Internacional
 <font color='blue'><b>FN</b></font> -> Fornecedor
</pre>
<?
    }
}
?>