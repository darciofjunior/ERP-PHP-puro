<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/lista_preco/lista_precos.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>ATUALIZADO COM SUCESSO.</font>";

//Vou utilizar essas variáveis para o cálculo em JavaScript ...
$taxa_financeiro    = genericas::variavel(4);
$desconto_snf       = genericas::variavel(5);

$sql = "SELECT `id_lista_preco_permissao` 
        FROM `listas_precos_permissoes` 
        WHERE `id_fornecedor` = '$id_fornecedor' 
        AND `id_funcionario` = '".$_SESSION['id_funcionario']."' LIMIT 1 ";
$campos_permissao = bancos::sql($sql);
if(count($campos_permissao) == 1) {
?>
    <Script Language = 'JavaScript'>
        alert('ESSE USUÁRIO NÃO TEM PERMISSÃO PARA ALTERAR A LISTA DE PREÇO !')
    </Script>
<?
}

if($passo == 1) {
    foreach($_POST['hdd_fornecedor_prod_insumo'] as $i => $id_fornecedor_prod_insumo) {
/*Aqui eu faço a nusca desses campos na Lista de Preço p/ fazer um controle de 
Data de Atualização por Item da Lista ...*/
        $sql = "SELECT `id_produto_insumo`, `preco_faturado`, `preco_faturado_export`, `valor_moeda_compra` 
                FROM `fornecedores_x_prod_insumos` 
                WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
        $campos                 = bancos::sql($sql);
        $id_produto_insumo      = $campos[0]['id_produto_insumo'];
        $preco_faturado         = $campos[0]['preco_faturado'];
        $preco_faturado_export  = $campos[0]['preco_faturado_export'];
        $valor_moeda_compra     = $campos[0]['valor_moeda_compra'];
        
        /*Macete caso essas 2 caixas abaixo estejam vazias p/ não cair no UPDATE abaixo que faz com que 
        atualiza o Funcionário e a Data Sys de maneira desnecessária - 18/02/2016 ...*/
        if(empty($_POST['txt_preco_fat_exp'][$i]))      $_POST['txt_preco_fat_exp'][$i] = '0.00';
        if(empty($_POST['txt_valor_moeda_compra'][$i])) $_POST['txt_valor_moeda_compra'][$i] = '0.0000';
/*Aqui eu verifico se Preço Fat. Nac R$ do BD está diferente do Digitado, se o Preço 
Fat. Moeda Est do BD está diferente do Digitado e se o Valor Moeda p/ Compra do BD 
está diferente do Digitado*/
        if(($preco_faturado != $_POST['txt_preco_faturado'][$i]) || ($preco_faturado_export != $_POST['txt_preco_fat_exp'][$i]) || ($valor_moeda_compra != $_POST['txt_valor_moeda_compra'][$i])) {
            $sql = "UPDATE `fornecedores_x_prod_insumos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."', `custo_pi_bloqueado` = 'N' WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
            bancos::sql($sql);
        }
        /*Aqui eu zero os adicionais do produto aqui na Lista de Preço porque esse preço só era calculado 
        em Produção Custo PI ...*/
        $sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado_adicional` = '0', `preco_faturado_export_adicional` = '0' WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
        bancos::sql($sql);
        
        $ipi_incluso = (in_array($id_fornecedor_prod_insumo, $_POST['chkt_ipi_incluso'])) ? 'S' : 'N';
/*Atualizando dados da Lista de Preço menos a Data que é controlada somente por aqueles 3 campos 
 _ O campo "fator_margem_lucro_pa" dessa tabela sempre é controlado por essa variável -> genericas::variavel(22) ...*/
        $sql = "UPDATE `fornecedores_x_prod_insumos` SET `preco_faturado` = '".$_POST['txt_preco_faturado'][$i]."', `prazo_pgto_ddl` = '".$_POST['txt_prazo_pgto_ddl'][$i]."', `desc_vista` = '".$_POST['txt_desc_vista'][$i]."', `desc_sgd` = '".$_POST['txt_desc_sgd'][$i]."', `ipi` = '".$_POST['txt_ipi'][$i]."', `ipi_incluso` = '$ipi_incluso', `icms` = '".$_POST['txt_icms'][$i]."', `reducao` = '".$_POST['txt_reducao'][$i]."', `iva` = '".$_POST['txt_iva'][$i]."', `lote_minimo_reais` = '".$_POST['txt_lote_minimo_reais'][$i]."', `forma_compra` = '".$_POST['cmb_forma_compra'][$i]."', `preco` = '".$_POST['txt_preco_compra_nac'][$i]."', `tp_moeda` = '".$_POST['cmb_tipo_moeda'][$i]."', `preco_faturado_export` = '".$_POST['txt_preco_fat_exp'][$i]."', `valor_moeda_compra` = '".$_POST['txt_valor_moeda_compra'][$i]."', `preco_exportacao` = '".$_POST['txt_preco_compra_internac'][$i]."', `fator_margem_lucro_pa` = '".genericas::variavel(22)."', `valor_moeda_custo` = '".$_POST['txt_valor_moeda_custo'][$i]."' WHERE `id_fornecedor_prod_insumo` = '$id_fornecedor_prod_insumo' LIMIT 1 ";
        bancos::sql($sql);
/********************************************************************************************************/
/**************************************Controle com o Custo Revenda**************************************/
/********************************************************************************************************/        
//Verifico se esse PI que foi atualizado é um PA "PIPA" ...
        $sql = "SELECT id_produto_acabado 
                FROM `produtos_acabados` 
                WHERE `id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
        $campos_pa = bancos::sql($sql);
        if(count($campos_pa) == 1) {//É um PIPA, então sendo assim eu Libero o Custo desse PA "Revenda" ...
            //Se um desses campos abaixo estiverem preenchidos, posso liberar o Custo normalmente ...
            if(!empty($_POST['txt_preco_faturado'][$i]) || !empty($_POST['txt_preco_fat_exp'][$i])) {
                $status_custo = 1;
            }else {//Se não eu bloqueio o Custo ...
                $status_custo = 0;
            }
            $sql = "UPDATE `produtos_acabados` SET `status_custo` = '$status_custo' WHERE `id_produto_acabado` = '".$campos_pa[0]['id_produto_acabado']."' LIMIT 1 ";
            bancos::sql($sql);
        }
/********************************************************************************************************/
    }
?>
    <Script language = 'JavaScript'>
        window.location = 'itens.php?id_fornecedor=<?=$_POST['id_fornecedor'];?>&id_produtos_insumos=<?=$_POST['id_produtos_insumos'];?>&valor=1'
    </Script>
<?
}else {
    //Variáveis de Controle ...
    $cont               = 0;
    $linhas_pracs_icms  = '';//Aqui nessa variável eu guardo todas as linhas de P.A(s) q são do Tipo PRAC
    
    //Busca o país do Fornecedor p/ utilizar nos cálculos mais abaixo em JavaScript ...
    $sql = "SELECT `id_pais` 
            FROM `fornecedores` 
            WHERE `id_fornecedor` = '$id_fornecedor' LIMIT 1 ";
    $campos_fornecedor  = bancos::sql($sql);
    $id_pais            = $campos_fornecedor[0]['id_pais'];
    
    //Buscando os Dados da Lista de Preço ...
    $sql = "SELECT g.`referencia`, pi.`discriminacao`, pi.`credito_icms`, fpi.*, u.`sigla` 
            FROM `fornecedores_x_prod_insumos` fpi 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = fpi.`id_produto_insumo` AND pi.`ativo` = '1' 
            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = fpi.`id_fornecedor` AND f.`id_fornecedor` = '$_GET[id_fornecedor]' 
            WHERE fpi.`id_produto_insumo` IN ($_GET[id_produtos_insumos]) 
            AND fpi.`ativo` = '1' ORDER BY pi.`discriminacao` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Lista de Preço(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/datatable.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
$(document).ready(function() {
    $('#example').dataTable( {
        'order': [[2, 'asc']],
        'paging': false,
        'scrollX': '10%',
        'scrollY': '54%',
    });
});
    
function redefinir() {
    var resposta = confirm('DESEJA RETORNAR OS VALORES ALTERADOS ?')
    if(resposta == false) {
        return false
    }else {
//Destrava o Botão de Indexação da Tela de Baixo p/ o usuário poder Indexar novamente caso desejar ...
        document.form.cmd_indexar.disabled = false
//Atualiza a Layer Limpando essa novamente do mesmo modo quando se carregou a Tela ...
        document.getElementById('texto_indexar_lista').innerHTML = ''
        document.form.reset()
    }
}

function calcular(indice, limite) {
    if(limite != '') {//Significa que essa função tem que ser rodada em cima de todos os itens da lista ...
        var inicio = 0
        var linhas = limite
    }else {//Aqui é para rodar função em cima de um única linha especificada ...
        var inicio = indice
        var linhas = eval(indice + 1)
    }
//Atribuições das caixas de texto para as variáveis
    var taxa_financeiro = '<?=$taxa_financeiro;?>'
    var desconto_snf    = '<?=$desconto_snf;?>'
    
    for(var i = inicio; i < linhas; i++) {
        var preco_faturado      = (document.getElementById('txt_preco_faturado'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_preco_faturado'+i).value))
        var prazo_pgto_dias     = (document.getElementById('txt_prazo_pgto_ddl'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_prazo_pgto_ddl'+i).value))
        var desconto_vista      = (document.getElementById('txt_desc_vista'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_desc_vista'+i).value))
        var desconto_sgd        = (document.getElementById('txt_desc_sgd'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_desc_sgd'+i).value))
        var icms                = (document.getElementById('txt_icms'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_icms'+i).value))
        var reducao             = (document.getElementById('txt_reducao'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_reducao'+i).value))
        var icms_com_reducao    =  icms * (1 - reducao / 100)

        //Verifica se a combo forma de compra está selecionada
        if(document.getElementById('cmb_forma_compra'+i).value == '') {
            document.getElementById('txt_preco_compra_nac'+i).value = ''
        }else {//Parte onde começa a calcular
            precoavnf       = (preco_faturado * (100 - desconto_vista) / 100)
            precominimo     = (preco_faturado * (100 - (prazo_pgto_dias / 30) * taxa_financeiro) / 100)
            precofatsgd     = (preco_faturado * (100 - desconto_sgd) / 100)
            precominimo2    = (preco_faturado * (100 - (icms_com_reducao - desconto_snf)) / 100)
            precoavsgd      = (precofatsgd * (100 - desconto_vista) / 100)
            precominimo3    = (precominimo * (100 - (icms_com_reducao - desconto_snf)) / 100)
            resposta1       = (Math.round((precoavnf * 100))) / 100
            resposta2       = (Math.round((precofatsgd * 100))) / 100
            resposta3       = (Math.round((precoavsgd * 100))) / 100
            precominimo     = (Math.round((precominimo * 100))) / 100
            precominimo2    = (Math.round((precominimo2 * 100))) / 100
            precominimo3    = (Math.round((precominimo3 * 100))) / 100

            //FAT/NF  ...
            if(document.getElementById('cmb_forma_compra'+i).value == 1) document.getElementById('txt_preco_compra_nac'+i).value = preco_faturado
            //FAT/SGD ...
            if(document.getElementById('cmb_forma_compra'+i).value == 2) document.getElementById('txt_preco_compra_nac'+i).value = resposta2
            //AV/NF ...
            if(document.getElementById('cmb_forma_compra'+i).value == 3) document.getElementById('txt_preco_compra_nac'+i).value = resposta1
            //AV/SGD ...
            if(document.getElementById('cmb_forma_compra'+i).value == 4) document.getElementById('txt_preco_compra_nac'+i).value = resposta3
            document.getElementById('txt_preco_compra_nac'+i).value = arred(document.getElementById('txt_preco_compra_nac'+i).value, 2, 1)
        }
    }
}

function calcular2(indice, limite) {
    var elementos       = document.form.elements
    if(limite != '') {//Significa que essa função tem que ser rodada em cima de todos os itens da lista ...
        var inicio = 0
        var linhas = limite
    }else {//Aqui é para rodar função em cima de um única linha especificada ...
        var inicio = indice
        var linhas = eval(indice + 1)
    }
    var id_pais         = '<?=$id_pais;?>'
    //Atribuições das caixas de texto para as variáveis
    var taxa_financeiro = '<?=$taxa_financeiro;?>'
    var desconto_snf    = '<?=$desconto_snf;?>'

    for(var i = inicio; i < linhas; i++) {
        var prazo_pgto_dias     = (document.getElementById('txt_prazo_pgto_ddl'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_prazo_pgto_ddl'+i).value))
        var desconto_vista      = (document.getElementById('txt_desc_vista'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_desc_vista'+i).value))
        var desconto_sgd        = (document.getElementById('txt_desc_sgd'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_desc_sgd'+i).value))
        var icms                = (document.getElementById('txt_icms'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_icms'+i).value))
        var reducao             = (document.getElementById('txt_reducao'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_reducao'+i).value))
        var preco_faturado_exp  = (document.getElementById('txt_preco_fat_exp'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_preco_fat_exp'+i).value))
        var icms_com_reducao    =  icms * (1 - reducao / 100)

        //Verifica se a combo forma de compra está selecionada
        if(document.getElementById('cmb_forma_compra'+i).value == '') {
            document.getElementById('txt_preco_compra_nac'+i).value = ''
        }else {
            //Aqui trata com o campo Valor Moeda p/ Compra
            if(id_pais != 31) {//País Internacional ...
                if(document.getElementById('txt_valor_moeda_compra'+i).value == '' || document.getElementById('txt_valor_moeda_compra'+i).value == '0,0000') {
                    //Igualo a 1 p/ facilitar p/ o Roberto, mas somente quando País = Estrangeiro, por causa do Dólar, Euro ...
                    var valor_moeda_compra  = '1.0000'
                }else {
                    var valor_moeda_compra  = eval(strtofloat(document.getElementById('txt_valor_moeda_compra'+i).value))
                }
            }else {//País Nacional ...
                var valor_moeda_compra      = (document.getElementById('txt_valor_moeda_compra'+i).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_valor_moeda_compra'+i).value))
            }            
            preco_faturado_exp*= valor_moeda_compra
            //Parte onde começa a calcular
            var preco_av_nf_exp     = (preco_faturado_exp * (100 - desconto_vista) / 100)
            var preco_minimo_exp    = (preco_faturado_exp * (100 - (prazo_pgto_dias / 30) * taxa_financeiro) / 100)
            var preco_fat_sgd_exp   = (preco_faturado_exp * (100 - desconto_sgd) / 100)
            var preco_minimo2_exp   = (preco_faturado_exp * (100 - (icms_com_reducao - desconto_snf)) / 100)
            var preco_av_sgd_exp    = (preco_fat_sgd_exp * (100 - desconto_vista) / 100)
            var preco_minimo3_exp   = (preco_minimo_exp * (100 - (icms_com_reducao - desconto_snf)) / 100)

            var resposta1_exp       = (Math.round((preco_av_nf_exp * 100))) / 100
            var resposta2_exp       = (Math.round((preco_fat_sgd_exp * 100))) / 100
            var resposta3_exp       = (Math.round((preco_av_sgd_exp * 100))) / 100			

            //FAT/NF ...
            if(document.getElementById('cmb_forma_compra'+i).value == 1) document.getElementById('txt_preco_compra_internac'+i).value = preco_faturado_exp
            //FAT/SGD ...
            if(document.getElementById('cmb_forma_compra'+i).value == 2) document.getElementById('txt_preco_compra_internac'+i).value = resposta2_exp
            //AV/NF ...
            if(document.getElementById('cmb_forma_compra'+i).value == 3) document.getElementById('txt_preco_compra_internac'+i).value = resposta1_exp
            //AV/SGD ...
            if(document.getElementById('cmb_forma_compra'+i).value == 4) document.getElementById('txt_preco_compra_internac'+i).value = resposta3_exp
            document.getElementById('txt_preco_compra_internac'+i).value = arred(document.getElementById('txt_preco_compra_internac'+i).value, 2, 1)
        }
    }
}

//Passa o índíce da coluna
function atualizar_coluna(id_campo) {
    var elementos = document.form.elements//Objetos do Formulário
    if(typeof(elementos['hdd_fornecedor_prod_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_fornecedor_prod_insumo[]'].length)
    }
    if(!confirm('DESEJA ATUALIZAR REALMENTE ?')) {
        return false
    }else {
        //Aqui eu igualo os valores da 2ª Coluna em diante com os valores da 1ª Coluna passada por parâmetro ...
        for(var i = 1; i < linhas; i++) {
            if(id_campo == 'chkt_ipi_incluso') {
                document.getElementById(String(id_campo+i)).checked = eval(document.getElementById(String(id_campo+'0'))).checked
            }else {
                document.getElementById(String(id_campo+i)).value   = eval(document.getElementById(String(id_campo+'0'))).value
            }
        }
        document.form.cmd_recalcular.onclick()
    }
}

//Aqui eu forço o usuário a Salvar a Lista de Preços 1º antes mesmo de Imprimir ...
function precisa_salvar_lista() {
    document.form.hdd_precisa_salvar_lista.value = 1
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" onsubmit='return validar()'>
<table width='98%' id='example' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <thead>
        <tr class='linhacabecalho' align='center'>
            <th colspan='23'>
                Lista de Preço(s) do Fornecedor => 
                <font color='yellow'>
                <?
                    //Busca da Razão Social do Fornecedor Corrente ...
                    $sql = "SELECT razaosocial 
                            FROM `fornecedores` 
                            WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
                    $campos_razao = bancos::sql($sql);
                    echo $campos_razao[0]['razaosocial'];
                ?>
                </font>
            </th>
        </tr>
        <tr class='linhadestaque' align='center'>
            <th>
                Ref.
            </th>
            <th>
                Un
            </th>
            <th>
                Discriminação
            </th>
            <th>
                Data Últ. <br/>Atual
            </th>
            <th>
                Preço Fat. R$
            </th>
            <th>
                Prazo Fat
            </th>
            <th>
                Desc. <br/>A/V %
            </th>
            <th>
                Desc.<br/> SGD %
            </th>
            <th>
                IPI %
            </th>
            <th>
                IPI<br/>Incl
            </th>
            <th>
                ICMS %
            </th>
            <th>
                Red.BC ICMS %
            </th>
            <th>
                IVA %
            </th>
            <th>
                Lote Min R$
            </th>
            <th>
                Forma de Compra
            </th>
            <th>
                Preço de Compra Nac. R$
            </th>
            <th>
                Adic. Custo Nac. R$
            </th>
            <th>
                Tipo Moeda
            </th>
            <th>
                Pr. Fat. Moeda Estrang.
            </th>
            <th>
                Valor Moeda p/ Compra
            </th>
            <th>
                Preço de Compra Inter. R$
            </th>
            <th>
                Adic. Custo Inter. R$
            </th>
            <th>
                Cond. Padrão
            </th>
        </tr>
    </thead>
    <tbody>
<?
    for($i = 0; $i < $linhas; $i++) {
        if(!empty($txt_preco_faturado)) {
            $preco_faturado = $txt_preco_faturado;
        }else {
            $preco_faturado = ($campos[$i]['preco_faturado'] == '0.00') ? '' : number_format($campos[$i]['preco_faturado'], 2, ',', '.');
        }

        if(!empty($txt_preco_faturado_adicional)) {
            $preco_faturado_adicional = $txt_preco_faturado_adicional;
        }else {
            $preco_faturado_adicional = ($campos[$i]['preco_faturado_adicional'] == '0.00') ? '' : number_format($campos[$i]['preco_faturado_adicional'], 2, ',', '.');
        }

        if(!empty($txt_prazo_pgto_ddl)) {
            $prazo_pgto_ddl = $txt_prazo_pgto_ddl;
        }else {
            $prazo_pgto_ddl = ($campos[$i]['prazo_pgto_ddl'] == '0.0') ? '' : number_format($campos[$i]['prazo_pgto_ddl'], 1, ',', '.');
        }

        if(!empty($txt_desc_vista)) {
            $desc_vista = $txt_desc_vista;
        }else {
            $desc_vista = ($campos[$i]['desc_vista'] == '0.0') ? '' : number_format($campos[$i]['desc_vista'], 1, ',', '.');
        }

        if(!empty($txt_desc_sgd)) {
            $desc_sgd = $txt_desc_sgd;
        }else {
            $desc_sgd = ($campos[$i]['desc_sgd'] == '0.0') ? '' : number_format($campos[$i]['desc_sgd'], 1, ',', '.');
        }

        $ipi            = (!empty($txt_ipi)) ? $txt_ipi : $campos[$i]['ipi'];

        $ipi_incluso    = (!empty($chkt_ipi_incluso)) ? $chkt_ipi_incluso : $campos[$i]['ipi_incluso'];

        if(!empty($txt_icms)) {
            $icms = $txt_icms;
        }else {
            $icms = ($campos[$i]['icms'] == '0.00') ? '' : number_format($campos[$i]['icms'], 2, ',', '');
        }

        if(!empty($txt_reducao)) {
            $reducao = $txt_reducao;
        }else {
            $reducao = ($campos[$i]['reducao'] == '0.00') ? '' : number_format($campos[$i]['reducao'], 2, ',', '');
        }

        if(!empty($txt_iva)) {
            $iva = $txt_iva;
        }else {
            $iva = ($campos[$i]['iva'] == '0.00') ? '' : number_format($campos[$i]['iva'], 2, ',', '');
        }

        if(!empty($txt_lote_minimo_reais)) {
            $lote_minimo_reais = $txt_lote_minimo_reais;
        }else {
            $lote_minimo_reais = ($campos[$i]['lote_minimo_reais'] == '0.00') ? '' : number_format($campos[$i]['lote_minimo_reais'], 2, ',', '');
        }

        $preco_compra_nac = ($campos[$i]['preco'] == '0.00' || $campos[$i]['preco'] == '') ? '' : number_format($campos[$i]['preco'], 2, ',', '.');

        if(!empty($txt_valor_moeda_compra)) {
            $valor_moeda_compra = $txt_valor_moeda_compra;
        }else {
            $valor_moeda_compra = ($campos[$i]['valor_moeda_compra'] == '0.00') ? '' : number_format($campos[$i]['valor_moeda_compra'], 4, ',', '.');
        }

        if(!empty($txt_preco_fat_exp)) {
            $preco_fat_exp = $txt_preco_fat_exp;
        }else {
            $preco_fat_exp = ($campos[$i]['preco_faturado_export'] == '0.00') ? '' : number_format($campos[$i]['preco_faturado_export'], 2, ',', '.');
        }

        if(!empty($txt_preco_faturado_export_adicional)) {
            $preco_faturado_export_adicional = $txt_preco_faturado_export_adicional;
        }else {
            $preco_faturado_export_adicional = ($campos[$i]['preco_faturado_export_adicional'] == '0.00') ? '' : number_format($campos[$i]['preco_faturado_export_adicional'], 2, ',', '.');
        }

        $preco_compra_internac  = number_format($campos[$i]['preco_exportacao'], 2, ',', '.');
        $valor_moeda_custo      = ($campos[$i]['valor_moeda_custo'] == '0.00') ? '' : number_format($campos[$i]['valor_moeda_custo'], 2, ',', '.');
    //Se for Prac, então eu acumulo o índice de linha do PA nessa variável ...
        if($campos[$i]['referencia'] == 'PRAC') $linhas_pracs_icms.= $i.',';
?>
        <tr class='linhanormal' title="<?=$campos[$i]['referencia'].' | '.$campos[$i]['discriminacao'];?>" align='center'>
            <td onclick="nova_janela('../alterar_lista_preco.php?id_fornecedor_prod_insumo=<?=$campos[$i]['id_fornecedor_prod_insumo'];?>&veio_lista_preco=S', 'POP', '', '', '', '', 530, 650, 'c', 'c', '', '', 's', 's', '', '', '')"  align='left'>
                <a href='#' class='link'>
                    <?=genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia']);?>
                </a>
                <input type='hidden' name='hdd_referencia[]' id='hdd_referencia<?=$i;?>' value='<?=$campos[$i]['referencia'];?>'>
            </td>
            <td onclick="nova_janela('../alterar_lista_preco.php?id_fornecedor_prod_insumo=<?=$campos[$i]['id_fornecedor_prod_insumo'];?>&veio_lista_preco=S', 'POP', '', '', '', '', 530, 650, 'c', 'c', '', '', 's', 's', '', '', '')"  align='left'>
                <?=$campos[$i]['sigla'];?>
            </td>
            <td align='left'>
                <a href="javascript:nova_janela('../alterar_lista_preco.php?id_fornecedor_prod_insumo=<?=$campos[$i]['id_fornecedor_prod_insumo'];?>&veio_lista_preco=S', 'POP', '', '', '', '', 530, 650, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
                    <?=$campos[$i]['discriminacao'];?>
                </a>
                &nbsp;
                <a href="javascript:nova_janela('../../estoque_i_c/detalhes_compras.php?id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>', 'POP', '', '', '', '', '600', '1000', 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes da Última Compra" class="link">
                    <img src="../../../../imagem/visualizar_detalhes.png" title="Detalhes da Última Compra" alt="Detalhes da Última Compra" border="0">
                </a>
            </td>
            <td>
                <b>
                <?
                    echo substr($campos[$i]['data_sys'], 8, 2).'/'.substr($campos[$i]['data_sys'], 5, 2).'/'.substr($campos[$i]['data_sys'], 2, 2);
                    
                    //Aqui eu busco o nome do Funcionário que fez a última modificação na Lista de Preço ...
                    if(!empty($campos[$i]['id_funcionario'])) {
                        $sql = "SELECT SUBSTRING_INDEX(`nome`, ' ', 1) AS nome 
                                FROM `funcionarios` 
                                WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
                        $campos_funcionario = bancos::sql($sql);
                        echo ' - '.$campos_funcionario[0]['nome'];
                    }
                ?>
                </b>
            </td>
            <td>
<?
	if($i == 0) {
?>
            <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_preco_faturado')">
            <br/>
<?
	}
?>
                <input type='text' name='txt_preco_faturado[]' id='txt_preco_faturado<?=$i;?>' value="<?=$preco_faturado;?>" id='txt_preco_faturado<?=$i;?>' size='7' maxlength='9' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular('<?=$i;?>', '');calcular2('<?=$i;?>', '');precisa_salvar_lista()" tabindex="<?='100'.$cont;?>" class='caixadetexto'>
            </td>
            <td>
<?
	if($i == 0) {
?>
                <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_prazo_pgto_ddl')">
                <br/>
<?
	}
?>
                <input type='text' name='txt_prazo_pgto_ddl[]' id='txt_prazo_pgto_ddl<?=$i;?>' value="<?=$prazo_pgto_ddl;?>" size='3' maxlength='4' onkeyup="verifica(this, 'moeda_especial', '1', '', event);calcular('<?=$i;?>', '');calcular2('<?=$i;?>', '');precisa_salvar_lista()" tabindex="<?='200'.$cont;?>" class='caixadetexto'>
            </td>
            <td>
<?
	if($i == 0) {
?>
                <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_desc_vista')">
                <br/>
<?
	}
?>
                <input type='text' name='txt_desc_vista[]' id='txt_desc_vista<?=$i;?>' value="<?=$desc_vista;?>" size='3' maxlength='4' onkeyup="verifica(this, 'moeda_especial', '1', '', event);calcular('<?=$i;?>', '');calcular2('<?=$i;?>', '');precisa_salvar_lista()" tabindex="<?='300'.$cont;?>" class='caixadetexto'>
            </td>
            <td>
<?
	if($i == 0) {
?>
                <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_desc_sgd')">
                <br/>
<?
	}
?>
                <input type='text' name='txt_desc_sgd[]' id='txt_desc_sgd<?=$i;?>' value="<?=$desc_sgd;?>" size='3' maxlength='4' onkeyup="verifica(this, 'moeda_especial', '1', '', event);calcular('<?=$i;?>', '');calcular2('<?=$i;?>', '');precisa_salvar_lista()" tabindex="<?='400'.$cont;?>" class='caixadetexto'>
            </td>
            <td>
<?
	if($i == 0) {
?>
            <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_ipi')">
            <br/>
<?
	}
?>
                <input type='text' name='txt_ipi[]' id='txt_ipi<?=$i;?>' value="<?=$ipi;?>" size='1' maxlength='2' onkeyup="verifica(this, 'aceita', 'numeros', '', event);precisa_salvar_lista()" tabindex="<?='500'.$cont;?>" class='caixadetexto'>
            </td>
            <td>
<?
	if($i == 0) {
?>
                <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('chkt_ipi_incluso')">
                <br/>
<?
	}
        $checked = ($ipi_incluso == 'S') ? 'checked' : '';
?>
                <input type='checkbox' name='chkt_ipi_incluso[]' id='chkt_ipi_incluso<?=$i;?>' value='<?=$campos[$i]['id_fornecedor_prod_insumo'];?>' tabindex="<?='600'.$cont;?>" <?=$checked;?>>
            </td>
            <td>
<?
        if($campos[$i]['credito_icms'] == 0) echo 'S / C';
        if($i == 0) {
?>
            <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_icms')">
<?
            }
?>
                <input type='text' name='txt_icms[]' id='txt_icms<?=$i;?>' value="<?=$icms;?>" size='4' maxlength='5' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular('<?=$i;?>', '');calcular2('<?=$i;?>', '');precisa_salvar_lista()" tabindex="<?='700'.$cont;?>" class='caixadetexto'>
<?

            if($id_pais == 31) {//Busca o ICMS do Fornecedor, somente no caso deste ser do Brasil ...
                //Me certifico de que o PI é um PA ...
                $sql = "SELECT id_produto_acabado 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos_pipa = bancos::sql($sql);
                //Se o PI é um PA, então eu busco o ICMS desse PA apenas do Estado de São Paulo ...
                if(count($campos_pipa) == 1) {
                    $sql = "SELECT IF(icms.`reducao` = '', icms.`icms`, (icms.`icms` - (icms.`icms` * icms.`reducao`) / 100)) AS icms_pipa 
                            FROM `produtos_acabados` pa 
                            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                            INNER JOIN `familias` f ON gpa.`id_familia` = f.`id_familia` 
                            INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                            INNER JOIN `icms` ON icms.`id_classific_fiscal` = cf.`id_classific_fiscal` AND icms.`id_uf` = '1' 
                            WHERE pa.`id_produto_acabado` = '".$campos_pipa[0]['id_produto_acabado']."' LIMIT 1 ";
                    $campos_icms_pipa = bancos::sql($sql);
                    echo '<font color="darkblue"><b>'.number_format($campos_icms_pipa[0]['icms_pipa'], 2, ',', '.').'</b></font>';
                }
            }
?>
                <input type='hidden' name='hdd_icms_pipa[]' id='hdd_icms_pipa<?=$i;?>' value='<?=number_format($campos_icms_pipa[0]['icms_pipa'], 1, ',', '.');?>'>
            </td>
            <td>
<?
	if($i == 0) {
?>
                <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_reducao')">
                <br/>
<?
	}
?>
                <input type='text' name='txt_reducao[]' id='txt_reducao<?=$i;?>' value="<?=$reducao;?>" size='4' maxlength='5' onkeyup="verifica(this, 'moeda_especial', '2', '', event);precisa_salvar_lista()" tabindex="<?='800'.$cont;?>" class='caixadetexto'>
            </td>
            <td>
<?
	if($i == 0) {
?>
                <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_iva')">
                <br/>
<?
	}
?>
                <input type='text' name='txt_iva[]' id='txt_iva<?=$i;?>' value="<?=$iva;?>" size='4' maxlength='5' onkeyup="verifica(this, 'moeda_especial', '2', '', event);precisa_salvar_lista()" tabindex="<?='900'.$cont;?>" class='caixadetexto'>
            </td>
            <td>
<?
	if($i == 0) {
?>
                <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_lote_minimo_reais')">
                <br/>
<?
	}
?>
                <input type='text' name='txt_lote_minimo_reais[]' id='txt_lote_minimo_reais<?=$i;?>' value="<?=$lote_minimo_reais;?>" size='6' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event);precisa_salvar_lista()" tabindex="<?='1000'.$cont;?>" class='caixadetexto'>
            </td>
            <td>
<?
	if($i == 0) {
?>
                <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('cmb_forma_compra')">
                <br/>
<?
	}
?>
                <select name='cmb_forma_compra[]' id='cmb_forma_compra<?=$i?>' onchange="calcular(<?=$i;?>, '');calcular2('<?=$i;?>', '');precisa_salvar_lista()" tabindex="<?='1100'.$cont;?>" class='combo'>
                <?
                    if(!empty($cmb_forma_compra)) {
                        if($cmb_forma_compra == 1) {
                            $selected1 = 'selected';
                        }else if($cmb_forma_compra == 2) {
                            $selected2 = 'selected';
                        }else if($cmb_forma_compra == 3) {
                            $selected3 = 'selected';
                        }else if($cmb_forma_compra == 4) {
                            $selected4 = 'selected';
                        }else {
                            $selected0 = 'selected';
                        }
                    }else {
                        if($campos[$i]['forma_compra'] == 1) {
                            $selected1 = 'selected';
                        }else if($campos[$i]['forma_compra'] == 2) {
                            $selected2 = 'selected';
                        }else if($campos[$i]['forma_compra'] == 3) {
                            $selected3 = 'selected';
                        }else if($campos[$i]['forma_compra'] == 4) {
                            $selected4 = 'selected';
                        }else {
                            $selected0 = 'selected';
                        }
                    }
                ?>
                    <option value='' style='color:red' <?=$selected0;?>>-</option>
                    <option value='1' <?=$selected1;?>>FAT/NF</option>
                    <option value='2' <?=$selected2;?>>FAT/SGD</option>
                    <option value='3' <?=$selected3;?>>AV/NF</option>
                    <option value='4' <?=$selected4;?>>AV/SGD</option>
                </select>
            </td>
            <td align='left'>
                <input type='text' name='txt_preco_compra_nac[]' id='txt_preco_compra_nac<?=$i;?>' size='6' maxlength='7' tabindex="<?='1200'.$cont;?>" class='textdisabled' disabled>
                <br/><font color='darkblue'><b>BD -> <?=$preco_compra_nac;?></b></font>
            </td>
            <td>
                <input type='text' name='txt_preco_faturado_adicional[]' id='txt_preco_faturado_adicional<?=$i;?>' value="<?=$preco_faturado_adicional;?>" size='4' maxlength='6' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular('<?=$i;?>', '');calcular2('<?=$i;?>', '');precisa_salvar_lista()" tabindex="<?='1300'.$cont;?>" class='textdisabled' disabled>
            </td>
            <td>
<?
	if($i == 0) {
?>
                <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('cmb_tipo_moeda')">
                <br/>
<?
	}
?>
                <select name='cmb_tipo_moeda[]' id='cmb_tipo_moeda<?=$i;?>' onchange="precisa_salvar_lista()" tabindex="<?='1400'.$cont;?>" class='combo'>
            <?
                if(!empty($cmb_tipo_moeda)) {
                    if($cmb_tipo_moeda == 1) {
                        $selected_estrangeira1 = 'selected';
                    }else if($cmb_tipo_moeda == 2) {
                        $selected_estrangeira2 = 'selected';
                    }else {
                        $selected_estrangeira0 = 'selected';
                    }
                }else {
                    if($campos[$i]['tp_moeda'] == 1) {
                        $selected_estrangeira1 = 'selected';
                    }else if($campos[$i]['tp_moeda'] == 2) {
                        $selected_estrangeira2 = 'selected';
                    }else {
                        $selected_estrangeira0 = 'selected';
                    }
                }
            ?>
                    <option value='' style='color:red' <?=$selected_estrangeira0;?>>-</option>
                    <option value='1' <?=$selected_estrangeira1;?>>U$</option>
                    <option value='2' <?=$selected_estrangeira2;?>>&euro;</option>
                </select>
            </td>
            <td>
<?
	if($i == 0) {
?>
                <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_preco_fat_exp')">
                <br/>
<?
	}
?>
                <input type='text' name='txt_preco_fat_exp[]' id='txt_preco_fat_exp<?=$i;?>' value="<?=$preco_fat_exp;?>" size='6' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular2('<?=$i;?>', '');precisa_salvar_lista()" tabindex="<?='1500'.$cont;?>" class='caixadetexto'>
            </td>
            <td>
<?
	if($i == 0) {
?>
                <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick="atualizar_coluna('txt_valor_moeda_compra')">
                <br/>
<?
	}
?>
                <input type='text' name='txt_valor_moeda_compra[]' id='txt_valor_moeda_compra<?=$i;?>' value="<?=$valor_moeda_compra;?>" size='6' maxlength='7' onkeyup="verifica(this, 'moeda_especial', '4', '', event);calcular2('<?=$i;?>', '');precisa_salvar_lista()" tabindex="<?='1600'.$cont;?>" class='caixadetexto'>
            </td>
            <td align='left'>
                <input type='text' name='txt_preco_compra_internac[]' id='txt_preco_compra_internac<?=$i;?>' size='6' maxlength='8' tabindex="<?='1700'.$cont;?>" class='textdisabled' disabled>
                <br/>
                <font color='darkblue'>
                    <b>BD -> <?=$preco_compra_internac;?></b>
                </font>
            </td>
            <td>
                <input type='text' name='txt_preco_faturado_export_adicional[]' id='txt_preco_faturado_export_adicional<?=$i;?>' value="<?=$preco_faturado_export_adicional;?>" size='4' maxlength='6' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular('<?=$i;?>', '');calcular2('<?=$i;?>', '');precisa_salvar_lista()" tabindex="<?='1800'.$cont;?>" class='textdisabled' disabled>
            </td>
            <td align='left'>
                <iframe style='backgroud:#ccff00' name='<?='condicao_padrao'.$i;?>' id='condicao_padrao' frameborder='0' vspace='0' hspace='0' marginheight='0' marginwidth='0' scrolling='no' title='Condição Padrão' width='50' height='22' src='condicao_padrao.php?id_fornecedor=<?=$id_fornecedor;?>&id_produto_insumo=<?=$campos[$i]['id_produto_insumo'];?>&qtde_registro=<?=$linhas;?>'></iframe>
                <input type='hidden' name='nao_apagar_por_causa_dos_index'>
                <input type='hidden' name='hdd_fornecedor_prod_insumo[]' id='hdd_fornecedor_prod_insumo<?=$i;?>' value='<?=$campos[$i]['id_fornecedor_prod_insumo'];?>'>
            </td>
        </tr>
<?
	$cont++;
    }
?>
    </tbody>
    <tr align='center'>
        <td class='linhacabecalho' colspan='23'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="parent.location = 'consultar_produtos.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_calculo_desconto' value='Cálculo de Desconto' title='Cálculo de Desconto' onclick="nova_janela('calculo_desconto.php', 'CALCULO_DESCONTO', '', '', '', '', 140, 700, 'c', 'c')" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir();calcular('', '<?=$linhas;?>');calcular2('', '<?=$linhas;?>')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_indexar' value='Indexar Lista' title='Indexar Lista' onclick="if(this.disabled == true) {alert('JÁ FOI FEITA UMA INDEXAÇÃO DA LISTA !')}else {nova_janela('indexar_lista.php', 'INDEXAR_LISTA', '', '', '', '', 140, 700, 'c', 'c')}" class='botao'>
            <input type='button' name='cmd_adicional_liga' value='Adicional de Liga' title='Adicional de Liga vs Fornecedor' onclick="nova_janela('liga_fornecedores.php?id_fornecedor=<?=$id_fornecedor;?>', 'ADICIONAL_LIGA', '', '', '', '', 280, 880, 'c', 'c')" class='botao'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick="nova_janela('imprimir.php?id_fornecedor=<?=$id_fornecedor;?>&id_produtos_insumos=<?=$_GET[id_produtos_insumos];?>', 'IMPRIMIR', '', '', '', '', 280, 880, 'c', 'c')" class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='23'>
<?
    if($valor == 1) {
        echo $mensagem[$valor];
    }else {
?>
        <font class='erro'>
            ESTÁ PAGINA AINDA NÃO FOI ATUALIZADA.
        </font>
<?        
    }
?>
        </td>
    </tr>
    <tr align='left'>
        <td colspan='23'>
            <div id='texto_indexar_lista' style='background-color: #FFFFFF; position:relative; left:0px; top:3px; height:42px; width:600px; border-width:0px;border-style:solid;border-color:#000000; color:darkblue; font:bold 16px verdana'></div>
        </td>
    </tr>
</table>
<!--****************Controle de Tela****************-->
<input type='hidden' name='cmd_recalcular' value='Recalcular' onclick="calcular('', '<?=$linhas;?>');calcular2('', '<?=$linhas;?>')">
<input type='hidden' name='cmd_recarregar' onclick="window.location='itens.php?id_fornecedor=<?=$id_fornecedor;?>&id_produtos_insumos=<?=$_GET[id_produtos_insumos];?>'">
<input type='hidden' name='id_fornecedor' value='<?=$id_fornecedor;?>'>
<input type='hidden' name='id_produtos_insumos' value='<?=$_GET[id_produtos_insumos];?>'>
<input type='hidden' name='hdd_precisa_salvar_lista' value='0'>
<!--************************************************-->
</form>
</body>
<?
//Tratamento com a variável p/ não dar problema ...
    if($linhas_pracs_icms != '') {//Se existir pelo menos um PI q é do Tipo PRAC então ...
        $linhas_pracs_icms = substr($linhas_pracs_icms, 0, strlen($linhas_pracs_icms) - 1);
        $linhas_pracs_icms = explode(',', $linhas_pracs_icms);//Transformando em vetor, vou utilizar + embaixo ...
        $existe_vetor = 1;//P/ evitar de JavaScript ...
    }
?>
<Script Language = 'JavaScript'>
function validar() {
    var elementos   = document.form.elements
    if(typeof(elementos['hdd_fornecedor_prod_insumo[]'][0]) == 'undefined') {
        var linhas  = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas  = (elementos['hdd_fornecedor_prod_insumo[]'].length)
    }
<?
//Aqui eu verifico se o funcionário tem permissão na lista de preço
    $sql = "SELECT `id_lista_preco_permissao` 
            FROM `listas_precos_permissoes` 
            WHERE `id_fornecedor` = '$id_fornecedor' 
            AND `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_permissao = bancos::sql($sql);
    if(count($campos_permissao) == 0) {
//Aqui eu verifico se é do Depto. de Compras, só esse departamento tem permissão para manipular a lista
        $sql = "SELECT id_departamento 
                FROM `funcionarios` 
                WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
        $campos_depto = bancos::sql($sql);
//Se for do Depto. de Compras, "Roberto" 62, "Fábio" 64 ou o "Dárcio" 98 porque programa então tem acesso ...
        if($campos_depto[0]['id_departamento'] == 4 || $_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 64 || $_SESSION['id_funcionario'] == 98) {
?>
//Variáveis de Controle ...
            var existe_vetor = '<?=$existe_vetor;?>'
            var elementos = document.form.elements//Objetos do Formulário

            if(existe_vetor == 1) {//P/ evitar de dar erro no JavaScript caso não existir o Vetor
                //Controle com os campos de ICMS no caso dos PA(s) ...
                var linhas_pracs_icms_array = new Array('<?=count($linhas_pracs_icms);?>')
/*************************************************************************/
//Guardo no vetor em JavaScript os valores do Vetor em PHP
<?
                for($i = 0; $i < count($linhas_pracs_icms); $i++) {
?>
                    linhas_pracs_icms_array['<?=$i;?>'] = eval('<?=$linhas_pracs_icms[$i];?>')
<?
                }
/*************************************************************************/
?>
			
//Se existir algum PI que é PA, então eu verifico se neste estão preenchidos todos os campos da coluna ICMS
                if(linhas_pracs_icms_array.length > 0) {
                    for(i = 0; i < linhas_pracs_icms_array.length; i++) {
                        //var posicao_objeto_icms = (linhas_pracs_icms_array[i] * objetos_linha) + 5
//Se estiver vazio o campo ICMS ou zerado desse PI que é PA, então eu forço o preenchimento desse campo de qualquer jeito ...
                        /*if(elementos[posicao_objeto_icms].value == '' || elementos[posicao_objeto_icms].value == '0,0') {
                            //ICMS %
                            alert('DIGITE O ICMS % !')
                            elementos[posicao_objeto_icms].focus()
                            elementos[posicao_objeto_icms].select()
                            return false
                        }*/
                    }
                }
            }
/********************************Validações********************************/
            for(i = 0; i < linhas; i++) {
//Controle com o "Desc.SGD %" em comparado ao ICMS "azul, abaixo da caixinha" quando for PRAC ...
                if(document.getElementById('hdd_referencia'+i).value == 'PRAC') {
                    var desc_sgd    = (document.getElementById('txt_desc_sgd'+i).value != '') ? eval(strtofloat(document.getElementById('txt_desc_sgd'+i).value)) : 0
                    var icms_pipa   = eval(strtofloat(document.getElementById('hdd_icms_pipa'+i).value))
                    
                    if(desc_sgd != icms_pipa) {
                        alert('DESCONTO SGD INVÁLIDO !!!\n\nO DESCONTO SGD TEM QUE SER IGUAL AO ICMS % "EM AZUL" !')
                        document.getElementById('txt_desc_sgd'+i).focus()
                        document.getElementById('txt_desc_sgd'+i).select()
                        return false
                    }
                }
//IPI Incluso ...
                if(document.getElementById('chkt_ipi_incluso'+i).checked == true) {
                    var ipi = (document.getElementById('txt_ipi'+i).value == '') ? 0 : eval(document.getElementById('txt_ipi'+i).value)
                    //IPI ...
                    if(ipi == 0) {
                        alert('IPI INVÁLIDO !!!\n\nIPI IGUAL A ZERO !')
                        document.getElementById('txt_ipi'+i).focus()
                        document.getElementById('txt_ipi'+i).select()
                        return false
                    }
                }
/*Controle com o Tipo de Moeda Estrangeira U$ e Euro ...

Se o Campo "Preço Fat. Moeda Estrangeira" estiver preenchido, então eu forço o Tipo de Moeda caso 
esteja vazio ...*/
                if(document.getElementById('txt_preco_fat_exp'+i).value != '' && document.getElementById('cmb_tipo_moeda'+i).value == '') {
                    alert('SELECIONE O TIPO DE MOEDA !')
                    document.getElementById('cmb_tipo_moeda'+i).focus()
                    return false
                }
            }
/**************************************************************************/
//Continuando a Rotina normalmente ...
            var resposta = confirm('DESEJA SALVAR OS VALORES ?')
            if(resposta == false) {
                return false
            }else {
/*Aqui trava o botão para não ter perigo de o usuário clicar novamente no botão e dar complicação
na caixa com os valores por causa da função de tratamento do JavaScript*/
                document.form.cmd_salvar.disabled = true
                alert('AGUARDE ESTA ROTINA PODE DEMORAR ALGUNS MINUTOS !!!\n\nSEU NAVEGADOR PODE NÃO RESPONDER DURANTE ALGUM TEMPO !')
                //Trato todas as caixas de texto p/ serem gravadas no BD ...
                for(i = 0; i < elementos.length; i++) {
                    if(elementos[i].type == 'text') elementos[i].value = strtofloat(elementos[i].value)
                }
                //Desabilito essas caixas em específico p/ serem gravadas no BD ...
                for(i = 0; i < linhas; i++) {
                    document.getElementById('txt_preco_compra_nac'+i).disabled      = false
                    document.getElementById('txt_preco_compra_internac'+i).disabled = false
                }
            }
<?
//Não tem acesso, não é do departamento de Compras
        }else {
?>
            alert('ESSE USUÁRIO NÃO TEM PERMISSÃO PARA ALTERAR A LISTA DE PREÇO !')
            return false
<?
//Não tem permissão para manipular a lista de preços
        }
    }else {
?>
        alert('ESSE USUÁRIO NÃO TEM PERMISSÃO PARA ALTERAR A LISTA DE PREÇO !')
        return false
<?
    }
?>
}

function imprimir(id_export) {
    var id_pais = '<?=$id_pais;?>'
    if(id_pais == 31) {//Se for Brasil ...
        if(id_export == 2) {
            nova_janela('pdf/relatorio.php?id_fornecedor=<?=$id_fornecedor;?>&id_produtos_insumos=<?=$_GET[id_produtos_insumos];?>&valor=1', 'CONSULTAR', 'F')
        }else {
            nova_janela('pdf/relatorio.php?id_fornecedor=<?=$id_fornecedor;?>&id_produtos_insumos=<?=$_GET[id_produtos_insumos];?>&valor=0', 'CONSULTAR', 'F')
        }
    }else {//Estrangeiro ...
        nova_janela('pdf/relatorio.php?id_fornecedor=<?=$id_fornecedor;?>&id_produtos_insumos=<?=$_GET[id_produtos_insumos];?>&valor=2', 'CONSULTAR', 'F')
    }
}
document.form.cmd_recalcular.onclick()
</Script>
</html>
<?}?>