<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>CUSTO ATUALIZADO COM SUCESSO.</font>";

if(!empty($_POST['id_pac_pi_trat'])) {
    //Verifico se o usu�rio, marcou o checkbox de de Peso de A�o Manual ...
    $peso_aco_manual = (!empty($_POST['chkt_peso_aco_manual'])) ? 1 : 0;
    
    $sql = "UPDATE `pacs_vs_pis_trat` SET `fator` = '$_POST[txt_fator_tt5]', `peso_aco` = '$_POST[txt_peso_aco5]', `peso_aco_manual` = '$peso_aco_manual', `lote_minimo_fornecedor` = '$_POST[chkt_lote_minimo]' WHERE `id_pac_pi_trat` = '$_POST[id_pac_pi_trat]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
    //Atualiza��o do Funcion�rio que alterou os dados no custo ...
    $sql = "UPDATE `produtos_acabados_custos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
    bancos::sql($sql);
    
    if($_POST['hdd_adicionar_novo'] == 'S') {
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_tratamento_termico.php?id_produto_acabado_custo=<?=$_POST[id_produto_acabado_custo];?>'
    </Script>
<?
    }
}

$id_produto_acabado_custo   = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado_custo'] : $_GET['id_produto_acabado_custo'];
$fator_custo5               = genericas::variavel(10);

/*Nessa parte o sistema j� deixa preparado o valor do peso a�o da etapa 2, caso o
usu�rio venha tirar do modo manual e desejar colocar do modo autom�tico*/
$sql = "SELECT `id_produto_acabado`, `id_produto_insumo`, `comprimento_1`, `comprimento_2`, `qtde_lote` 
        FROM `produtos_acabados_custos` 
        WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
$campos_etapa2          = bancos::sql($sql);
$id_produto_acabado	= $campos_etapa2[0]['id_produto_acabado'];
$id_produto_insumo	= $campos_etapa2[0]['id_produto_insumo'];
$comprimento_a		= $campos_etapa2[0]['comprimento_1'];
$qtde_lote		= $campos_etapa2[0]['qtde_lote'];
$comprimento_b		= $campos_etapa2[0]['comprimento_2'];
$comprimento_total	= ($comprimento_a + $comprimento_b) / 1000;

//Vou utilizar essa vari�vel + abaixo no JavaScript ...
$produto_etapa_2        = $id_produto_insumo;

//Aqui eu verifico se o "PA Principal" do Custo que foi acessado � do Tipo ESP ...
$sql = "SELECT `referencia` 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos_pa      = bancos::sql($sql);
$referencia_pa  = $campos_pa[0]['referencia'];

$sql = "SELECT pia.`densidade_aco` 
        FROM `produtos_insumos` pi 
        INNER JOIN `produtos_insumos_vs_acos` pia ON pia.`id_produto_insumo` = pi.`id_produto_insumo` 
        WHERE pi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
$campos_aco     = bancos::sql($sql);
$densidade      = (count($campos_aco) == 1) ? $campos_aco[0]['densidade_aco'] : 1;
$peso_aco_kg    = $densidade * $comprimento_total;

//Seleciona a qtde de itens que existe do produto acabado na etapa 5
$sql = "SELECT COUNT(ppt.`id_pac_pi_trat`) AS qtde_itens 
        FROM `pacs_vs_pis_trat` ppt 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppt.`id_produto_insumo` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

/*Aqui traz todos os produtos insumos que est�o relacionados ao produto acabado 
passado por par�metro*/
$sql = "SELECT pi.`id_produto_insumo`, pi.`discriminacao`, ppt.`id_pac_pi_trat`, ppt.`fator`, ppt.`peso_aco`, ppt.`peso_aco_manual`, 
        ppt.`lote_minimo_fornecedor`, u.`sigla` 
        FROM `pacs_vs_pis_trat` ppt 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ppt.`id_produto_insumo` 
        INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
        WHERE ppt.`id_produto_acabado_custo` = '$id_produto_acabado_custo' ORDER BY ppt.`id_pac_pi_trat` ";
if(empty($posicao)) 	$posicao = $qtde_itens;
$campos = bancos::sql($sql, ($posicao - 1), $posicao);
$id_produto_insumo = $campos[0]['id_produto_insumo'];//Aqui eu j� me refiro ao PI da etapa 5 mesmo ...
?>
<html>
<head>
<title>.:: Alterar Custo de Trat. T�rmico / Galvanoplastia ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calcular(desmarcar_peso_aco_manual) {
//1) Peso do A�o ...
    var produto_etapa_2 = eval('<?=$produto_etapa_2;?>')
    if(produto_etapa_2 == 0) {//Significa que n�o existe Produto Insumo atrelado na Etapa 2 ...
        if(desmarcar_peso_aco_manual == 1) {//Caso o usu�rio tenha desmarcado o checkbox ...
            if(document.form.chkt_peso_aco_manual.checked == false) {
                alert('ESSA OP��O N�O PODE SER DESMARCADA, DEVIDO N�O EXISTIR ITEM NA 2� ETAPA !')
            }
        }
        document.form.txt_fator_tt5.disabled        = true
        document.form.txt_fator_tt5.value           = '0,00'
        document.form.txt_fator_tt5.className       = 'textdisabled'
        document.form.chkt_peso_aco_manual.checked  = true
        document.form.txt_peso_aco5.disabled        = false
        document.form.txt_peso_aco5.className       = 'caixadetexto'
        document.form.txt_peso_aco5.focus()
    }else {//Quando existir algum PI, ent�o ...
        //Verifico qual � a unidade do Produto Insumo ...
        var unidade_insumo = "<?=$campos[0]['sigla'];?>"
        //Quando for Diferente de Unidade Realiza o C�lculo Normalmente ...
        if(unidade_insumo != 'UN') {//Se for Kg, ...
            if(document.form.chkt_peso_aco_manual.checked == true) {//Checkbox Habilitado
                document.form.txt_fator_tt5.disabled    = true
                document.form.txt_fator_tt5.value       = '0,00'
                document.form.txt_fator_tt5.className   = 'textdisabled'
                document.form.txt_peso_aco5.disabled    = false
                document.form.txt_peso_aco5.className   = 'caixadetexto'
                document.form.txt_peso_aco5.focus()
            }else {//Checkbox Desabilitado
                document.form.txt_peso_aco5.disabled    = true
                if(desmarcar_peso_aco_manual == 1) {//Caso o usu�rio tenha desmarcado o checkbox ...
                    document.form.txt_fator_tt5.value   = '0,00'
                }else {
                    //Iguala com o Peso da 2� Etapa ...
                    document.form.txt_peso_aco5.value   = "<?=number_format($peso_aco_kg, 3, ',', '.');?>"
                }
                document.form.txt_peso_aco5.className   = 'textdisabled'
                document.form.txt_fator_tt5.disabled    = false
                document.form.txt_fator_tt5.className   = 'caixadetexto'
                document.form.txt_fator_tt5.focus()
            }
        }else {//Quando for unidade, tem essa particulariza��o p/ alguns produtos, o sistema sugere 1 no Peso do A�o ...
            if(document.form.chkt_peso_aco_manual.checked == true) {//Checkbox Habilitado
                document.form.txt_fator_tt5.disabled    = true
                document.form.txt_fator_tt5.value       = '0,00'
                document.form.txt_fator_tt5.className   = 'textdisabled'
                document.form.txt_peso_aco5.disabled    = false
                document.form.txt_peso_aco5.value       = arred(document.form.txt_peso_aco5.value, 3, 1)
                document.form.txt_peso_aco5.className   = 'caixadetexto'
                document.form.txt_peso_aco5.focus()
            }else {//Checkbox Desabilitado
                document.form.txt_peso_aco5.disabled = true
                if(desmarcar_peso_aco_manual == 1) {//Caso o usu�rio tenha desmarcado o checkbox ...
                    document.form.txt_fator_tt5.value   = '0,00'
                }else {//Iguala com o Peso da 2� Etapa ...
                    document.form.txt_peso_aco5.value   = "<?=number_format($peso_aco_kg, 3, ',', '.');?>"	
                }
                document.form.txt_peso_aco5.className   = 'textdisabled'
                document.form.txt_fator_tt5.className   = 'caixadetexto'
                document.form.txt_fator_tt5.disabled    = false
                document.form.txt_fator_tt5.focus()
            }
        }
    }
//2) Peso / Pe�a corrigido pelo Fator ...
    var fator_custo = eval('<?=$fator_custo5;?>')
    var preco_kg_rs = eval(strtofloat(document.form.txt_preco_unitario_kg_rs5.value))
    var peso_aco    = (document.form.txt_peso_aco5.value != '') ? eval(strtofloat(document.form.txt_peso_aco5.value)) : 0
    var fator_tt    = (document.form.txt_fator_tt5.value != '') ? eval(strtofloat(document.form.txt_fator_tt5.value)) : 0

    if(document.form.chkt_peso_aco_manual.checked == true) {//S� iguala os campos ...
        document.form.txt_peso_peca_corrigido.value = peso_aco
    }else {//C�lculo p/ o novo campo que apareceu Peso de Pe�a corrigido ...
        document.form.txt_peso_peca_corrigido.value = fator_tt * peso_aco
    }
    document.form.txt_peso_peca_corrigido.value = arred(document.form.txt_peso_peca_corrigido.value, 3, 1)
//3) Total em R$ ...
    if(document.form.chkt_lote_minimo.checked == true) {//Lote M�nimo marcado, ent�o aciono esse c�lculo abaixo de Lote M�nimo por Fornecedor Default por pedido ...
        var lote_minimo_fornecedor  = eval(strtofloat(document.form.txt_lote_minimo.value))
        var lote_custo              = eval('<?=$qtde_lote;?>')
        var preco_peca_corte        = lote_minimo_fornecedor / lote_custo
        var total_pecas_s_fator     = eval(strtofloat(document.form.txt_total5.value)) / fator_custo

        if(total_pecas_s_fator < preco_peca_corte) {
            document.form.txt_total5.value = preco_peca_corte * fator_custo
        }else {
            calcular_total_rs(preco_kg_rs, peso_aco, fator_custo, fator_tt)
        }
    }else {//Lote m�nimo desmarcado ...
        calcular_total_rs(preco_kg_rs, peso_aco, fator_custo, fator_tt)
    }
//Tratamento p/ apresentar o Total em R$ ...
    document.form.txt_total5.value = (isNaN(document.form.txt_total5.value)) ? '' : arred(document.form.txt_total5.value, 2, 1)
}

function calcular_total_rs(preco_kg_rs, peso_aco, fator_custo, fator_tt) {
    /*Ignora a multiplica��o pelo Fator_TT, pois j� temos o Peso Real da Pe�a que aqui chamamos erroneamente 
    de "Peso A�o Manual" ...*/
    if(document.form.chkt_peso_aco_manual.checked == true) {
        document.form.txt_total5.value  = (preco_kg_rs * peso_aco * fator_custo)
    }else {
        document.form.txt_total5.value  = (preco_kg_rs * peso_aco * fator_custo * fator_tt)
    }
}

function validar(posicao, verificar) {
//Fator T.T.
    if(document.form.txt_fator_tt5.disabled == false) {
        var fator_tt = eval(strtofloat(document.form.txt_fator_tt5.value))
        if(fator_tt == 0 || typeof(fator_tt) == 'undefined') {
            alert('FATOR T.T. INV�LIDO ! \nVALOR IGUAL A ZERO OU EST� V�ZIO !')
            document.form.txt_fator_tt5.focus()
            document.form.txt_fator_tt5.select()
            return false
        }
//Se o Peso do A�o Manual estiver desmarcado, ent�o ...
        if(document.form.chkt_peso_aco_manual.disabled == false) {
            if(fator_tt > 1) {
                alert('FATOR T.T. INV�LIDO !\nVALOR MAIOR DO QUE 1 !')
                document.form.txt_fator_tt5.focus()
                document.form.txt_fator_tt5.select()
                return false
            }
        }
    }
//Peso p/ TT ou Peso do A�o ...
    if(document.form.txt_peso_aco5.disabled == false) {
        var rotulo = document.form.txt_rotulo.value.toUpperCase()
        var peso_aco5 = eval(strtofloat(document.form.txt_peso_aco5.value))
        if(peso_aco5 == 0 || typeof(peso_aco5) == 'undefined') {
            alert(rotulo+' INV�LIDO ! \nVALOR IGUAL A ZERO OU EST� V�ZIO !')
            document.form.txt_peso_aco5.focus()
            document.form.txt_peso_aco5.select()
            return false
        }
    }
//Desabilita os campos p/ poder gravar no BD ...
    document.form.txt_fator_tt5.disabled = false
    document.form.txt_peso_aco5.disabled = false
    limpeza_moeda('form', 'txt_fator_tt5, txt_peso_aco5, ')
//Recupera a posi��o corrente no hidden, para n�o dar erro de pagina��o
    document.form.posicao.value = posicao
//Aqui � para n�o atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
//Submetendo o Formul�rio ...
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que s� atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) parent.document.form.submit()
}

//Para n�o dar problema no in�cio da tela, na hora em q carregar o body
function retornar_foco() {
    if(document.form.txt_fator_tt5.disabled == false) document.form.txt_fator_tt5.focus()
}

function verificar_pa() {
    referencia_pa = '<?=$referencia_pa;?>'
//Se o Produto for normal de Linha, ent�o eu n�o posso fazer o c�lculo por Lote M�nimo ...
    if(referencia_pa != 'ESP') {
        if(document.form.chkt_lote_minimo.checked == true) {
            alert('ESSE PRODUTO � NORMAL DE LINHA !!!\nPORTANTO ESSA OP��O N�O PODE SER MARCADA ! ')
            document.form.chkt_lote_minimo.checked = false
            return false
        }
    }
}

function controlar_rotulo() {
    if(document.form.chkt_peso_aco_manual.checked == true) {//Checkbox Habilitado
        document.form.txt_rotulo.value = 'Peso p/ TT: '
    }else {
        document.form.txt_rotulo.value = 'Peso do A�o: '
    }
}
</Script>
</head>
<body onload="controlar_rotulo();retornar_foco();calcular()" onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>', 1)">
<!--********************************Controle de Tela********************************-->
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='id_produto_acabado_custo' value="<?=$id_produto_acabado_custo;?>">
<input type='hidden' name='id_pac_pi_trat' value="<?=$campos[0]['id_pac_pi_trat'];?>">
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='hdd_adicionar_novo'>
<!--********************************************************************************-->
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr onClick="showHide('calculo_fator_tt'); return false">
        <td align='center'>
            &nbsp;
        </td>
        <td align='right'>
            &nbsp;
            <span id='statuscalculo_fator_tt'></span>
            <span id='statuscalculo_fator_tt'></span>
        </td>
    </tr>
    <tr onClick="showHide('calculo_fator_tt'); return false">
        <td colspan='2'>
            <iframe src='calculo_fator_tt.php?tela=<?=$tela;?>' name='calculo_fator_tt' id='calculo_fator_tt' marginwidth='0' marginheight='0' style='display:none' frameborder='0' height='260' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            5&ordf; Etapa - Alterar Custo de Trat. T�rmico / Galvanoplastia
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <font color='#FFFF00'>Ref.:</font>
            Trat - <font color='#FFFF00'>Und.:</font>
            <?=$campos[0]['sigla'];?> - 
            <font color='#FFFF00'>Discrim.:</font>
            <?=$campos[0]['discriminacao'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fator T.T.:
        </td>
        <td>
            <input type='text' name='txt_fator_tt5' value="<?=number_format($campos[0]['fator'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular()" size="8" class='caixadetexto'>
            <img src='../../../../imagem/icones/calculadora3.jpg' width='16' height='17' onclick="showHide('calculo_fator_tt'); return false" title='C�lculo p/ Fator de Tratamento T�rmico' alt='C�lculo p/ Fator de Tratamento T�rmico'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor Default:
        </td>
        <td>
        <?
            $id_fornecedor_default = custos::preco_custo_pi($campos[0]['id_produto_insumo'], 0, 1);
            //Aqui eu pego o Lote M�nimo do Fornecedor Default encontrado atrav�s do PI na lista de Pre�o de Compras ...
            $sql = "SELECT f.razaosocial, fpi.lote_minimo_reais 
                    FROM `fornecedores` f 
                    INNER JOIN `fornecedores_x_prod_insumos` fpi ON fpi.id_fornecedor = f.id_fornecedor AND fpi.`id_produto_insumo` = '".$campos[0]['id_produto_insumo']."' 
                    WHERE f.`id_fornecedor` = '$id_fornecedor_default' LIMIT 1 ";
            $campos_forncec = bancos::sql($sql);
            echo $campos_forncec[0]['razaosocial'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor do Lote M&iacute;nimo R$:
        </td>
        <td>
            <input type='text' name='txt_lote_minimo' value="<?=number_format($campos_forncec[0]['lote_minimo_reais'], 2, ',', '.');?>" size="12" id="txt_lote_minimo" class='disabled' disabled>
            <?
                $checked_lote_minimo = ($campos[0]['lote_minimo_fornecedor'] == 1) ? 'checked' : '';
//Se o Produto for normal de Linha, ent�o eu n�o posso fazer o c�lculo por Lote M�nimo ...
                if($referencia_pa != 'ESP') {
//Se o checkbox estava desmarcado, ent�o j� desabilito essa op��o p/ evitar futuros erros ...
                    if($checked_lote_minimo == '') $disabled = 'disabled';
                }
            ?>
            <input type='checkbox' name='chkt_lote_minimo' id='chkt_lote_minimo' value='1' onclick='verificar_pa();calcular()' <?=$checked_lote_minimo;?> class='checkbox' <?=$disabled;?>>
            <label for='chkt_lote_minimo'>C&aacute;lculo por Lote M�nimo</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            P. Unit&aacute;rio sem ICMS R$:
        </td>
        <td>
            <input type='text' name='txt_preco_unitario_kg_rs5' value='<?=number_format(custos::preco_custo_pi($campos[0]['id_produto_insumo']), 2, ',', '.');?>' size='12' class='disabled' disabled>
            &nbsp;
            <font color='darkblue'>
                <b><?=$campos[0]['sigla'];?></b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_rotulo' style='color:#000000' class='caixadetexto2' disabled>
        </td>
        <td>
            <?
                /*Sempre que a sigla da Unidade = UN a op��o de Fator T.T. ser� igual a 0,0 e a op��o Alterar - 
                Peso / Pe�a ficar� selecionada, agora o porque eu n�o me lembro e nem o Roberto ... rsrs*/
                if($campos[0]['sigla'] == 'UN') {//Quando for = a Unidade
                    $checked = 'checked';
                }else {//Quando for != Unidade ...
                    $checked = ($campos[0]['peso_aco_manual'] == 1) ? 'checked' : '';
                }
            ?>
            <input type='text' name='txt_peso_aco5' value='<?=number_format($campos[0]['peso_aco'], 3, ',', '.');?>' onkeyup="verifica(this, 'moeda_especial', '3', '', event);calcular()" size='7' class='disabled' disabled>
            &nbsp;
            <input type='checkbox' name='chkt_peso_aco_manual' id='chkt_peso_aco_manual' value='1' onclick="calcular(1);controlar_rotulo()" <?=$checked;?> class='checkbox'>
            <label for='chkt_peso_aco_manual'>Usar Peso Real da Pe�a</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Peso / Pe�a corrigido pelo Fator:
        </td>
        <td>
            
            <input type='text' name='txt_peso_peca_corrigido' id='txt_peso_peca_corrigido' size='7' class='disabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Total R$:
        </td>
        <?
            if($campos[0]['peso_aco_manual'] == 1) {//Ignora a multiplica��o pelo fator_tt ...
                $total = $preco_pi * $campos[0]['peso_aco'] * $fator_custo5;
            }else {
                $total = $campos[0]['fator'] * $preco_pi * $campos[0]['peso_aco'] * $fator_custo5;
            }
        ?>
        <td>
            <input type='text' name='txt_total5' id='txt_total5' value='<?=number_format($total, 2, ',', '.');?>' size='12' class='disabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_adicionar_novo' value='Adicionar Novo' title='Adicionar Novo' onclick="document.form.hdd_adicionar_novo.value = 'S';validar('<?=$posicao;?>')" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');calcular();retornar_foco()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_alterar_fornecedores' value='Alterar Fornecedores' title='Alterar Fornecedores' onClick="showHide('alterar_fornecedores'); return false" style='color:black' class='botao'>
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
    <tr align='center'>
        <td colspan='2'> 
        <?
/////////////////////////////// PAGINACAO CASO ESPECIFICA PARA ESTA TELA ///////////////////////////////////////
            if($posicao > 1) echo "<b><a href='#' onclick='validar(($posicao-1))' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>&lt;&lt; Anterior &lt;&lt; </font></a>&nbsp;</b>&nbsp;&nbsp;";
            for($i = 1; $i <= $qtde_itens; $i++) {
                if($i == $posicao) {
                    echo "<b><font size='2' color='red' face='verdana, arial, helvetica, sans-serif'>$i</font>&nbsp;</b>";
                }else {
                    echo "<b><a href='#' onclick='validar($i)' class='link'><font size='2' color='#6473D4' face='verdana, arial, helvetica, sans-serif'>$i</font></a>&nbsp;</b>";
                }
            }
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='#' onclick='validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Pr�xima &gt;&gt; </font></a>&nbsp;</b>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
<pre>
* Sempre que a sigla da Unidade = <font color='darkblue'><b>'UN'</b></font> a op��o de Fator T.T. ser� igual a 0,0 e a op��o <b>Alterar - 
Peso / Pe�a</b> ficar� selecionada.
</pre>
<!--Agora sempre ir� mostrar esse Iframe-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td align='center'>
            &nbsp;
        </td>
        <td align="right">
            &nbsp;
            <span id='statusalterar_fornecedores'></span>
            <span id='statusalterar_fornecedores'></span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
            <iframe src='../../../classes/produtos_insumos/marcar_fornecedor_default.php?id_produto_insumo=<?=$id_produto_insumo;?>&posicao=<?=$posicao;?>' name="alterar_fornecedores" id="alterar_fornecedores" marginwidth="0" marginheight="0" style="display: none;" frameborder="0" height="260" width="100%"></iframe>
        </td>
    </tr>
</table>
<!--Controle para saber se vai estar mostrando este Iframe para o Usu�rio-->
<?
//Verifico se esse PI corrente est� em algum Pedido de Compras ...
    $sql = "SELECT id_item_pedido 
            FROM `itens_pedidos` 
            WHERE `id_produto_insumo = '$id_produto_insumo' limit 1 ";
    $campos_pi = bancos::sql($sql);
    if(count($campos_pi) == 0) {//Como n�o est�, exibo essa Tela com Todos os Fornecedores desse PI ...
?>
<Script Language = 'JavaScript'>
/*Id�ia de Onload

Na primeira vez em que carregar essa Tela, caso venha existir algum Pedido de Compras para esse PI, ent�o 
eu disparo por meio do JavaScript essa fun��o para que j� venha mostrar esse iframe ...*/
    showHide('alterar_fornecedores')
</Script>
<?
    }
?>
</form>
</body>
</html>