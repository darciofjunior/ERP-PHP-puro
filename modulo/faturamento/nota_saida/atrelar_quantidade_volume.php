<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../classes/array_sistema/array_sistema.php');
session_start('funcionarios');

switch($opcao) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    case 4://Significa que veio do Menu de Devolu��o 
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
    default://Significa que veio do Menu de Devolu��o ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
}

$mensagem[1] = "<font class='atencao'>N�O EXISTE(M) CAIXA(S) COLETIVA(S) P/ NF.</font>";
$mensagem[2] = '<font class="confirmacao">CAIXA COLETIVA INCLUIDA COM SUCESSO.</font>';
$mensagem[3] = '<font class="confirmacao">CAIXA COLETIVA ALTERADA COM SUCESSO.</font>';
$mensagem[4] = '<font class="confirmacao">VIDE NOTA ATUALIZA COM SUCESSO P/ NF.</font>';
$mensagem[5] = '<font class="erro">HOUVE IRREGULARIDADE NO VIDE NOTA. APONTE OUTRO N.� DE VIDE NOTA.</font>';

//Tratamento com a vari�vel P/ o decorrer da Tela ...
$id_nf = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_nf'] : $_GET['id_nf'];

//Aki eu busco quem � o Cliente da NF atual e a Vide Nota desta tamb�m com a qual estou trabalhando
$sql = "SELECT id_cliente, id_nf_vide_nota, status 
        FROM `nfs` 
        WHERE `id_nf` = '$id_nf' LIMIT 1 ";
$campos             = bancos::sql($sql);
$id_cliente         = $campos[0]['id_cliente'];
$id_nf_vide_nota    = $campos[0]['id_nf_vide_nota'];
//caso a nota ja foi despachada, eu n�o posso deixar alterar os vide nota ent�o travo o bot�o SALVAR ...
if($campos[0]['status'] > 3) $disabled_submit = 'disabled';

if (empty($id_nf_vide_nota)) {
    $condicao = " AND nfs.`status` < '4' ";
}else {
    $condicao = " AND (nfs.`status` < '4' OR nfs.`id_nf` = '$id_nf_vide_nota') ";
}

/*Aki eu busco todas as NF desse Cliente, que estejam na Situa��o de Cancelada, Em Aberto, Liberada p/ Faturar, 
Faturada, Empacotada, e que a Vide Nota seja = 0 - 'N�o tenha Vide Nota'*/
$sql_combo = "SELECT nfs.id_nf, concat(nnn.numero_nf, ' (', e.nomefantasia, ')') AS dados 
                FROM `nfs` 
                INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` 
                INNER JOIN `empresas` e ON e.`id_empresa` = nfs.`id_empresa` 
                WHERE nfs.`id_cliente` = '$id_cliente' 
                AND nfs.`id_nf` <> '$id_nf' 
                $condicao 
                AND nfs.`id_nf` IN 
                (SELECT id_nf 
                FROM `nfs` 
                WHERE `id_nf_vide_nota` IS NULL) ORDER BY nfs.numero_nf ";

if(!empty($_POST['id_nf'])) {
    if(!empty($_POST['cmb_vide_nota'])) {//Significa que foi preenchida a parte de vide nota
/*Aki eu busco todas as NF desse Cliente, e que Vide seja = 0, fa�o isso para ter um controle maior de seguran�a, ex:
devido o sistema ser multiusu�rio, ou ter sido aberto em outras inst�ncias, ...*/
        $campos = bancos::sql($sql_combo);
        $linhas = count($campos);
        for($i = 0; $i < $linhas; $i++) $vetor_nfs[] = $campos[$i]['id_nf'];
        if(in_array($_POST['cmb_vide_nota'], $vetor_nfs) == 1) {
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem n�o tiver preenchidos  ...
/*******************************************************************************/
            $cmb_vide_nota  = (!empty($_POST[cmb_vide_nota])) ? "'".$_POST[cmb_vide_nota]."'" : 'NULL';
//Atualiza na NF a parte referente ao Vide Nota
            $sql = "UPDATE `nfs` SET `id_nf_vide_nota` = $cmb_vide_nota WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
            bancos::sql($sql);
//Deleta dessa NF todas as Caixa(s) Coletiva(s)
            $sql = "DELETE FROM `nfs_vs_pi_embalagens` WHERE `id_nf` = '$_POST[id_nf]' AND `id_produto_insumo` NOT IN (0) ";
            bancos::sql($sql);
            $valor = 4;
        }else {
            $valor = 5;
        }
    }else {//Significa que foi preenchida a parte de Caixa(s) Coletiva(s)
//Primeira coisa a fazer � desatrelar o Vide Nota da NF que estou trabalhando
        $sql = "UPDATE `nfs` SET `id_nf_vide_nota` = NULL, `peso_bruto_balanca` = '$_POST[txt_peso_bruto_balanca]' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        bancos::sql($sql);
        //Aqui � a parte da inser��o de itens de Caixa(s) Coletiva(s) para NF ...
        for($i = 0; $i < count($_POST['chkt_produto_insumo']); $i++) $id_produto_insumos.= $_POST['chkt_produto_insumo'][$i].', ';
        //P/ o caso de o usu�rio n�o ter selecionado nenhuma Caixa(s) Coletiva(s) tem esse macete (rsrs)
        if(empty($id_produto_insumos)) $id_produto_insumos = '0, ';
        $id_produto_insumos = substr($id_produto_insumos, 0, strlen($id_produto_insumos) - 2);
	
//Deleta dessa NF todas as Caixa(s) Coletiva(s) que n�o foram selecionadas ...
        $sql = "DELETE FROM `nfs_vs_pi_embalagens` WHERE `id_nf` = '$id_nf' AND `id_produto_insumo` NOT IN ($id_produto_insumos) ";
        bancos::sql($sql);

        for($i = 0; $i < count($_POST['chkt_produto_insumo']); $i++) {
            //Verifica se j� foi incluida a Caixa(s) Coletiva(s) na NF
            $sql = "SELECT id_nf_pi_embalagem 
                    FROM `nfs_vs_pi_embalagens` 
                    WHERE `id_produto_insumo` = '".  $_POST['chkt_produto_insumo'][$i]."' 
                    AND `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {//Item n�o existente ...
                $sql = "INSERT INTO `nfs_vs_pi_embalagens` (`id_nf_pi_embalagem`, `id_nf`, `id_produto_insumo`, `qtde`) VALUES (NULL, '$_POST[id_nf]', '".$_POST['chkt_produto_insumo'][$i]."', '".$_POST['txt_quantidade'][$i]."') ";
                $valor = 2;
            }else {//Item j� existente
                $sql = "UPDATE `nfs_vs_pi_embalagens` SET `qtde` = '".$_POST['txt_quantidade'][$i]."' WHERE `id_nf_pi_embalagem` = '".$campos[0]['id_nf_pi_embalagem']."' LIMIT 1 ";
                $valor = 3;
            }
            bancos::sql($sql);
        }
        //Significa que o Usu�rio desmarcou a(s) Caixa(s) Coletiva(s) que estava(m) selecionada(s
        if(empty($valor)) $valor = 3;
    }
/**********************************************************************************/
?>
    <Script Language = 'JavaScript'>
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    </Script>
<?
}

//Aqui � para buscar o Peso do ERP
$peso_nf = faturamentos::calculo_peso_nf($id_nf);
$peso_erp = number_format($peso_nf['peso_liq_total_nf'], 4, '.', '');

//Aqui traz todos os PIs Caixa(s) Coletiva(s) que podem estar sendo utilizados com a Caixa Coletiva para NFS
$sql = "SELECT g.referencia, pi.id_produto_insumo, pi.discriminacao, pi.peso 
        FROM `produtos_insumos` pi 
        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
        WHERE pi.`ativo` = '1' 
        AND pi.`caixa_coletiva_nfs` = '1' ORDER BY pi.discriminacao ";
$campos = bancos::sql($sql, $inicio, 1000, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Consultar Caixa(s) Coletiva(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<!--JS espec�fico para esse arquivo-->
<Script Language = 'JavaScript' Src = 'atrelar_quantidade_volume.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos = document.form.elements
    
    if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['chkt_produto_insumo[]'].length)
    }

    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_produto_insumo'+i).checked == true) {
            if(document.getElementById('txt_quantidade'+i).value == '') {
                alert('DIGITE A QUANTIDADE !')
                document.getElementById('txt_quantidade'+i).focus()
                return  false
            }

            if(document.getElementById('txt_quantidade'+i).value == 0) {
                alert('QUANTIDADE INV�LIDA !')
                document.getElementById('txt_quantidade'+i).focus()
                document.getElementById('txt_quantidade'+i).select()
                return  false
            }
        }
    }
    
    var valor = false
    //Verifica se tem hum checkbox selecionado pelo menos ...
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo')  {
            if (elementos[i].checked == true) {
                valor = true
            }
        }
    }

    if(valor == true && document.form.cmb_vide_nota.value != '') {
        alert('N�O � PERMITIDO MARCAR CAIXA(S) COLETIVA(S) E VIDE NOTA AO MESMO TEMPO !')
        document.form.cmb_vide_nota.focus()
        document.form.cmb_vide_nota.select()
        return false
    }

//Verifica��o do Peso Bruto da Balan�a digitado pelo usu�rio com o valor que vem por par�metro do Erp
    var peso_bruto_balanca = eval(strtofloat(document.form.txt_peso_bruto_balanca.value))
    var peso_total_das_caixas = eval(strtofloat(arred(document.form.txt_peso_total.value, 2, 1)))
    var peso_bruto_erp = eval('<?=$peso_erp;?>') + peso_total_das_caixas
    if((peso_bruto_balanca / peso_bruto_erp > 0.99) && (peso_bruto_balanca / peso_bruto_erp < 1.01)) {
        alert('OK !')
    }else {
        var diferenca_peso = peso_bruto_balanca - peso_bruto_erp
//Aqui tem esse cambalacho para poder conseguir fazer o arredondamento na hora da Apresenta��o do alert
        peso_bruto_balanca = String(peso_bruto_balanca)
        peso_bruto_erp = String(peso_bruto_erp)
        diferenca_peso = String(diferenca_peso)
/*****************************************************************************************************/
        if(peso_bruto_balanca > peso_bruto_erp) {
            alert('BALAN�A MAIS PESADA DO QUE O ERP !\n\nPESO DA BALAN�A = '+arred(peso_bruto_balanca, 4, 1)+' KG(s) !\nPESO DO ERP = '+arred(peso_bruto_erp, 4, 1)+' KG(s) !\n\nDIFEREN�A DE '+arred(diferenca_peso, 4, 1)+' KG(s) !')
        }else {
            alert('ERP MAIS PESADA DO QUE A BALAN�A !\n\nPESO DA BALAN�A = '+arred(peso_bruto_balanca, 4, 1)+' KG(s) !\nPESO DO ERP = '+arred(peso_bruto_erp, 4, 1)+' KG(s) !\n\nDIFEREN�A DE '+arred(diferenca_peso, 4, 1)+' KG(s) !')
        }
    }
    limpeza_moeda('form', 'txt_peso_bruto_balanca,')
//Para n�o atualizar o Pop-up de baixo
    document.form.nao_atualizar.value = 1
    return true
}

function redefinir() {
    var resposta = confirm('DESEJA REDEFINIR ?')
    if(resposta == true) {
        window.location = 'atrelar_quantidade_volume.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>'
    }else {
        return false
    }
}

function calcular() {
    var elementos           = document.form.elements
    var quantidade_total    = 0
    var peso_total          = 0
    
    if(typeof(elementos['chkt_produto_insumo[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 �nico elemento ...
    }else {
        var linhas = (elementos['chkt_produto_insumo[]'].length)
    }
    
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_produto_insumo'+i).checked == true) {
            quantidade_corrente = eval(document.getElementById('txt_quantidade'+i).value)
//Caso o usu�rio venha passar a qtde como n�o sendo n�mero na c�lula, ent�o eu transformo em 0, p/ n�o dar erro de c�lculo
            if(isNaN(quantidade_corrente)) quantidade_corrente = 0
//A coluna peso total � igual ao peso do produto X quantidade digitada pelo usu�rio
            document.getElementById('txt_peso_total'+i).value = document.getElementById('hdd_peso'+i).value * quantidade_corrente
            document.getElementById('txt_peso_total'+i).value = arred(document.getElementById('txt_peso_total'+i).value, 4, 1)
//Totais
            quantidade_total+= quantidade_corrente//Total da Coluna Qtde
            peso_total+= eval(strtofloat(document.getElementById('txt_peso_total'+i).value))//Total da Coluna Qtde Total
        }
    }
    document.form.txt_quantidade_total.value    = quantidade_total
    document.form.txt_peso_total.value          = peso_total
    document.form.txt_peso_total.value          = arred(document.form.txt_peso_total.value, 4, 1)
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) window.opener.document.form.submit()
}
</Script>
</head>
<body onload='calcular()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
<?
    if($linhas == 0) {//N�o existem PIs Caixa(s) Coletiva(s) ...
?>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
<?
        exit;
    }
?>
    <tr align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Consultar Caixa(s) Coletiva(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar_especial('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class="checkbox">
        </td>
        <td>
            Qtde
        </td>
        <td>
            Refer�ncia * Discrimina��o
        </td>
        <td>
            Peso
        </td>
        <td>
            Peso Total
        </td>
    </tr>
<?
    for($i = 0; $i < $linhas; $i++) {
        //Aqui eu verifico se a Caixa(s) Coletiva(s) corrente, est� atrelada a NF ...
        $sql = "SELECT qtde 
                FROM `nfs_vs_pi_embalagens` 
                WHERE `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                AND `id_nf` = '$id_nf' LIMIT 1 ";
        $campos_nfs_embalagem = bancos::sql($sql);
        if(count($campos_nfs_embalagem) == 1) {//Est� atrelada
            $checked    = 'checked';
            $disabled   = '';
            $class      = 'caixadetexto';
            $quantidade = $campos_nfs_embalagem[0]['qtde'];
        }else {//N�o est� atrelada
            $checked    = '';
            $disabled   = 'disabled';
            $class      = 'textdisabled';
            $quantidade = '';
        }
/****************************************************************************************************/
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_produto_insumo[]' id='chkt_produto_insumo<?=$i;?>' value='<?=$campos[$i]['id_produto_insumo'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox' <?=$checked;?>>
        </td>
        <td>
            <input type='text' name='txt_quantidade[]' id='txt_quantidade<?=$i;?>' value='<?=$quantidade;?>' title='Digite a Quantidade' maxlength="8" size="8" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyUp="verifica(this, 'aceita', 'numeros', '', event);if(this.value == '0') {this.value = ''};calcular()" class="<?=$class;?>" <?=$disabled;?>>
        </td>
        <td align='left'>
            <?=$campos[$i]['referencia'].' * '.$campos[$i]['discriminacao'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['peso'], 4, ',', '.');?>
            <input type='hidden' name='hdd_peso[]' id='hdd_peso<?=$i;?>' value='<?=$campos[$i]['peso'];?>'>
        </td>
        <td>
            <input type='text' name='txt_peso_total[]' id='txt_peso_total<?=$i;?>' title='Peso Total' maxlength='12' size='13' class='textdisabled' disabled>
        </td>
    </tr>
<?
    }
?>
    <tr align='right'>
        <td class='linhadestaque' >
            Qtde Caixas:
        </td>
        <td class='linhadestaque' align='center'>
            <input type='text' name='txt_quantidade_total' maxlength='8' size='8' class='textdisabled' disabled>
        </td>
        <td class='linhadestaque' colspan='2'>
            Peso Total:
        </td>
        <td class='linhadestaque' align='center'>
            <input type='text' name='txt_peso_total' maxlength='12' size='13' class='textdisabled' disabled>
        </td>
    </tr>
<?
//Aqui busca a vide NF e o Peso Bruto da Balan�a da NF com o id_nf
    $sql = "SELECT peso_bruto_balanca 
            FROM `nfs` 
            WHERE `id_nf` = '$id_nf' LIMIT 1 ";
    $campos             = bancos::sql($sql);
    $peso_bruto_balanca = $campos[0]['peso_bruto_balanca'];
?>
    <tr class='linhacabecalho'>
        <td colspan='5'>
            Vide Nota:
            <select name="cmb_vide_nota" title="Selecione a Vide Nota" class='combo'>
                <?=combos::combo($sql_combo, $id_nf_vide_nota);?>
            </select>
            &nbsp;-&nbsp;
            Peso Bruto da Balan�a:
            <input type='text' name='txt_peso_bruto_balanca' value="<?=number_format($peso_bruto_balanca, 3, ',', '.');?>" title="Digite o Peso Bruto da Balan�a" maxlength="12" size="13" onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='caixadetexto'>
            Esp�cie: 
            <?
                $calculo_peso_nf = faturamentos::calculo_peso_nf($id_nf);
                echo $calculo_peso_nf['especie'];
            ?>
        </td>
    </tr>
<?
//Aqui eu fa�o uma verifica��o de todas as Notas Fiscais que est�o em aberto daquele Cliente
    $sql = "SELECT nfs.id_nf, nfs.id_empresa, nfs.id_nf_num_nota, nfs.data_emissao, nfs.vencimento1, nfs.vencimento2, nfs.vencimento3, nfs.vencimento4, nfs.status, nfs.tipo_despacho, c.razaosocial, c.credito, t.nome AS transportadora 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.id_cliente = nfs.id_cliente 
            INNER JOIN `transportadoras` t ON t.id_transportadora = nfs.id_transportadora 
            WHERE nfs.id_cliente = '$id_cliente' 
            AND nfs.`status` < '4' ORDER BY nfs.data_emissao DESC, nfs.numero_nf DESC ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='5' bgcolor='red'>
            Nota(s) Fiscal(is) em Aberto
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            N.&ordm; Nota Fiscal
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Transportadora
        </td>
        <td>
            Status da NF
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Emp / Tp Nota <br>/ Prazo Pgto
            </font>
        </td>
    </tr>
<?
        $vetor          = array_sistema::nota_fiscal();
        $tipo_despacho  = array('', 'PORTARIA', 'TRANSPORTADORA', 'NOSSO CARRO', 'RETIRA', 'CORREIO/SEDEX', 'TAM');
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
        </td>
        <td>
        <?
            if($campos[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[$i]['data_emissao'], '/');
        ?>
        </td>
        <td>
            <?=$campos[$i]['transportadora'];?>
        </td>
        <td align='left'>
        <?
            echo $vetor[$campos[$i]['status']];
            if($campos[$i]['status'] == 4) echo ' ('.$tipo_despacho[$campos[$i]['tipo_despacho']].')';
        ?>
        </td>
        <td align='left'>
        <?
            //Aqui eu busco o nome da Empresa da Nota Fiscal ...
            $sql = "SELECT nomefantasia 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $nomefantasia   = $campos_empresa[0]['nomefantasia'];
            $apresentar     = ($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) ? $nomefantasia.' (NF)' : $nomefantasia.' (SGD)';

            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? '� vista' : $campos[$i]['vencimento1'];
            }
            echo $apresentar.' / '.$prazo_faturamento;
//Aki eu limpo essa vari�vel para n�o dar problema quando voltar no pr�ximo loop
            $prazo_faturamento = '';
        ?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick='redefinir()' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao' <?=$disabled_submit;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='opener.document.form.submit();window.close()' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_nf' value="<?=$id_nf;?>">
<!--Para n�o dar erro de permiss�o, � referente aos Menus da Sess�o-->
<input type='hidden' name='opcao' value="<?=$opcao;?>">
<input type='hidden' name='nao_atualizar'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>