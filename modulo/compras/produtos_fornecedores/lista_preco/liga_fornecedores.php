<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/compras/produtos_fornecedores/lista_preco/lista_precos.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>LIGA X FORNECEDOR ADICIONADA COM SUCESSO.</font>";

if(!empty($_POST['hdd_qualidade_aco'])) {
    //Aqui eu verifico se foi alterado o Valor de Pedágio do Fornecedor ...
    if($_POST['txt_pedagio_digitado'] != $_POST['hdd_pedagio_atual']) {
        $sql = "UPDATE `fornecedores` set `pedagio` = '$_POST[txt_pedagio_digitado]' WHERE `id_fornecedor` = '$_POST[id_fornecedor]' LIMIT 1 ";
        bancos::sql($sql);
    }
    //Listando os Itens ...
    foreach($_POST['hdd_qualidade_aco'] as $i => $id_qualidade_aco) {
        //Para aliviar processamento, so rodo as Querys abaixo quando houve alguma alteracao ...
        if(!empty($_POST['txt_nac_novo'][$i]) || !empty($_POST['txt_est_novo'][$i])) {
            //Verifico se existe Adicional de Liga para esse Fornecedor nessa Qualidade de Aço ...
            $sql = "SELECT id_fornecedor_liga 
                    FROM `fornecedores_ligas` 
                    WHERE `id_fornecedor` = '$_POST[id_fornecedor]' 
                    AND `id_qualidade_aco` = '$id_qualidade_aco' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Não existe sendo assim crio o Adicional ...
                $sql = "INSERT INTO `fornecedores_ligas` (`id_fornecedor_liga`, `id_fornecedor`, `id_qualidade_aco`, `valor_nac_ant`, `valor_nac`, `valor_est_ant`, `valor_est`) VALUES (NULL, '$_POST[id_fornecedor]', '$id_qualidade_aco', '".$_POST['txt_nac_antigo'][$i]."', '".$_POST['txt_nac_novo'][$i]."', '".$_POST['txt_est_antigo'][$i]."', '".$_POST['txt_est_novo'][$i]."') ";
                bancos::sql($sql);
            }else {//Existe, sendo assim só altera os valores ...
                if(!empty($_POST['txt_nac_novo'][$i]) && !empty($_POST['txt_est_novo'][$i])) {
                    $sql = "UPDATE `fornecedores_ligas` SET `valor_nac` = '".$_POST['txt_nac_novo'][$i]."', `valor_est` = '".$_POST['txt_est_novo'][$i]."' WHERE `id_fornecedor` = '$_POST[id_fornecedor]' AND `id_qualidade_aco` = '$id_qualidade_aco' ";
                }else if(!empty($_POST['txt_nac_novo'][$i]) && empty($_POST['txt_est_novo'][$i])) {
                    $sql = "UPDATE `fornecedores_ligas` SET `valor_nac` = '".$_POST['txt_nac_novo'][$i]."' WHERE `id_fornecedor` = '$_POST[id_fornecedor]' AND `id_qualidade_aco` = '$id_qualidade_aco' ";
                }else if(empty($_POST['txt_nac_novo'][$i]) && !empty($_POST['txt_est_novo'][$i])) {
                    $sql = "UPDATE `fornecedores_ligas` SET `valor_est` = '".$_POST['txt_est_novo'][$i]."' WHERE `id_fornecedor` = '$_POST[id_fornecedor]' AND `id_qualidade_aco` = '$id_qualidade_aco' ";
                }
                bancos::sql($sql);
            }
            /*Aqui eu atualizo somente os PI(s) que atendem ao aco do Loop do Fornecedor ...
             * 
             * Liga: Sao os elementos quimicos adicionados ao aco ...
             * 
             */
            $sql = "SELECT fpi.id_fornecedor_prod_insumo, fpi.preco_faturado, fpi.preco_faturado_export 
                    FROM `fornecedores_x_prod_insumos` fpi 
                    INNER JOIN `produtos_insumos_vs_acos` pia ON pia.id_produto_insumo = fpi.id_produto_insumo 
                    WHERE fpi.`id_fornecedor` = '$_POST[id_fornecedor]' 
                    AND pia.`id_qualidade_aco` = '$id_qualidade_aco' ";
            $campos_lista = bancos::sql($sql);
            $linhas_lista = count($campos_lista);
            for($j = 0; $j < $linhas_lista; $j++) {
                if(empty($_POST['txt_nac_novo'][$i])) $_POST['txt_nac_novo'][$i] = $_POST['txt_nac_antigo'][$i];
                $novo_reajuste_nac = round(round($campos_lista[$j]['preco_faturado'] + $_POST['txt_nac_novo'][$i] - $_POST['txt_nac_antigo'][$i], 3), 2);
                $sql = "UPDATE `fornecedores_x_prod_insumos` 
                        SET `preco_faturado` = '$novo_reajuste_nac' 
                        WHERE `id_fornecedor_prod_insumo` = '".$campos_lista[$j]['id_fornecedor_prod_insumo']."' LIMIT 1 ";
                bancos::sql($sql);

                if(empty($_POST['txt_est_novo'][$i])) $_POST['txt_est_novo'][$i] = $_POST['txt_est_antigo'][$i];
                $novo_reajuste_inter = round(round($campos_lista[$j]['preco_faturado_export'] + $_POST['txt_est_novo'][$i] - $_POST['txt_est_antigo'][$i] + $_POST['txt_pedagio_digitado'] - $_POST['hdd_pedagio_atual'], 3), 2);
                $sql = "UPDATE `fornecedores_x_prod_insumos` 
                        SET `preco_faturado_export` = '$novo_reajuste_inter' 
                        WHERE `id_fornecedor_prod_insumo` = '".$campos_lista[$j]['id_fornecedor_prod_insumo']."' LIMIT 1 ";
                bancos::sql($sql);
            }
        }
    }//Fim do Foreach ...
    $valor = 1;
?>
    <Script Language = 'Javascript'>
        alert('ENTRAR EM TODAS AS PÁGINAS DE ITENS DESTE FORNECEDOR E SALVAR !!!\n\nÉ NECESSÁRIO FAZERMOS ISSO P/ ATUALIZARMOS OS CAMPOS PREÇO DE COMPRA NAC / INTER. R$ !')
        opener.location.reload()
    </Script>
<?
}

//Busca todas as Qualidades cadastradas que tiverem RAP ou SINTER na sua Qualidade ...
$sql = "SELECT id_qualidade_aco, nome, valor_perc 
        FROM `qualidades_acos` 
        WHERE (`nome` LIKE 'RAP%' OR `nome` LIKE 'SIN%') 
        AND `ativo` = '1' ORDER BY nome ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Liga Fornecedores ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['txt_nac_antigo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_nac_antigo[]'].length)
    }

    for(var i = 0; i < linhas; i++) {
        if(elementos[i].type == 'text' && elementos[i].value == '0,000') {
            mensagem = confirm('EXISTEM VALORES ZERADOS, DESEJA CONTINUAR ?\n\n OBSERVAÇÃO: ESTES VALORES IRÃO SOBREPOR OS VALORES ANTIGOS !')
            if(mensagem == true) {
                break
            }else {
                return false
            }
        }
    }
    //Prepara os objetos para gravar no BD ...
    for(var i = 0; i < linhas; i++) {
        document.getElementById('txt_nac_antigo'+i).disabled    = false
        document.getElementById('txt_nac_novo'+i).disabled      = false
        document.getElementById('txt_est_antigo'+i).disabled    = false
        document.getElementById('txt_est_novo'+i).disabled      = false

        document.getElementById('txt_nac_antigo'+i).value       = strtofloat(document.getElementById('txt_nac_antigo'+i).value)
        document.getElementById('txt_nac_novo'+i).value         = strtofloat(document.getElementById('txt_nac_novo'+i).value)
        document.getElementById('txt_est_antigo'+i).value       = strtofloat(document.getElementById('txt_est_antigo'+i).value)
        document.getElementById('txt_est_novo'+i).value         = strtofloat(document.getElementById('txt_est_novo'+i).value)
    }
    limpeza_moeda('form', 'txt_pedagio_digitado, hdd_pedagio_atual, ')
}
</Script>
</head>
<body onload="alert('TODOS OS PREÇOS FAT. NACIONAL E ESTRANGEIRO TEM DE ESTAR ATUALIZADOS !')">
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr align='center'>
        <td colspan='6'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Adicional de Liga x Fornecedor
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Qualidade Aço
        </td>
        <td>
            Nac Antigo
        </td>
        <td>
            Nac Novo
        </td>
        <td>
            Intern. Antigo
        </td>
        <td>
            Intern. Novo
        </td>
        <td>
            % Acr&eacute;scimo <br>na Densidade
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        //Busco tudo o que é de liga do Fornecedor passado por parâmetro e da Qualidade do Aço Corrente do Loop ...
        $sql = "SELECT * 
                FROM `fornecedores_ligas` 
                WHERE `id_fornecedor` = '$_GET[id_fornecedor]' 
                AND `id_qualidade_aco` = '".$campos[$i]['id_qualidade_aco']."' LIMIT 1 ";
        $campos_liga = bancos::sql($sql);
        if(count($campos_liga) == 0) {
            $nacional_antigo    = '0,000';
            $estrangeiro_antigo = '0,000';
        }else {
            $nacional_antigo    = number_format($campos_liga[0]['valor_nac'], 3, ',', '.');
            $estrangeiro_antigo = number_format($campos_liga[0]['valor_est'], 3, ',', '.');
        }
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <input type='text' name='txt_nac_antigo[]' id='txt_nac_antigo<?=$i;?>' value="<?=$nacional_antigo;?>" maxlength="10" size="12" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_nac_novo[]' id='txt_nac_novo<?=$i?>' maxlength="10" size="12" onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class="caixadetexto">
        </td>
        <td>
            <input type='text' name='txt_est_antigo[]' id='txt_est_antigo<?=$i;?>' value="<?=$estrangeiro_antigo;?>" maxlength="10" size="12" class="textdisabled" disabled>
        </td>
        <td>
            <input type='text' name='txt_est_novo[]' id='txt_est_novo<?=$i;?>' maxlength="10" size="12" onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'>
            <input type='hidden' name='hdd_qualidade_aco[]' id='hdd_qualidade_aco<?=$i;?>' value="<?=$campos[$i]['id_qualidade_aco'];?>">
        </td>
        <td>
            <?=number_format($campos[$i]['valor_perc'], 3, ',', '.').' % ';?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhadestaque'>
        <td colspan='6'>
        <?
            //Busca do Pedágio do Fornecedor ...
            $sql = "SELECT pedagio 
                    FROM `fornecedores` 
                    WHERE `id_fornecedor` = '$_GET[id_fornecedor]' LIMIT 1 ";
            $campos_fornecedor = bancos::sql($sql);
        ?>
            Pedágio Internacional: <input type='text' name='txt_pedagio_digitado' value="<?=number_format($campos_fornecedor[0]['pedagio'], 3, ',', '.')?>" title="Digite o Pedágio" maxlength="12" size="16" onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class="botao">
        </td>
    </tr>
    <tr class='atencao'>
        <td colspan='6'>
            <font color='blue' size='-1'>
                <marquee>
                    <b>Quando o valor novo n&atilde;o for preenchido o sistema n&atilde;o atualizar&aacute; o item.</b>
                </marquee>
            </font>
        </td>
    </tr>
</table>
<input type='hidden' name='id_fornecedor' value='<?=$_GET['id_fornecedor'];?>'>
<!--Esse objeto serve para comparar com o Pedágio Atual que está visível para o Usuário-->
<input type='hidden' name='hdd_pedagio_atual' value="<?=number_format($campos_fornecedor[0]['pedagio'], 3, ',', '.')?>">
<!--***********************************************************************************-->
</form>
</body>
</html>