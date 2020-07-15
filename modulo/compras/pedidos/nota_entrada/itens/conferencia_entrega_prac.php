<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/compras_new.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

$mensagem[1] = "<font class='confirmacao'>CONFERÊNCIA DE ENTREGA DO PRAC REALIZADA COM SUCESSO.</font>";

if(!empty($_POST['id_nfe'])) {
    //Busco o login de que está fazendo a Alteração dos Itens da Nota
    $sql = "SELECT login 
            FROM logins 
            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $login                  = $campos[0]['login'];
    $responsavel_medidas    = 'Última alteração: '.$login.' - '.date('d/m/Y H:i:s');
    
    for($i = 0; $i < count($_POST['chkt_nfe_historico']); $i++) {
        $sql = "UPDATE `nfe_historicos` SET `qtde_prac_conf` = '".$_POST['txt_qtde_prac_conf'][$i]."', `responsavel_medidas` = '$responsavel_medidas' WHERE `id_nfe_historico` = '".$_POST['chkt_nfe_historico'][$i]."' LIMIT 1 ";
        bancos::sql($sql);
    }
//Atualizo a Observação de Conferência ...
    $sql = "UPDATE `nfe` SET `obs_conf_prac` = '$_POST[txt_obs_conf_prac]' WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
}

//Procedimento quando carrega a Tela ...
$id_nfe = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_nfe'] : $_GET['id_nfe'];

//Busca o Tipo de Moeda da NF
$sql = "SELECT CONCAT(tm.simbolo, ' ') AS moeda 
        FROM `nfe` 
        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = nfe.`id_tipo_moeda` 
        WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
$campos = bancos::sql($sql);
$moeda  = $campos[0]['moeda'];

//Busca o nome do Fornecedor com + detalhes alguns detalhes de dados da Nota Fiscal
$sql = "SELECT f.razaosocial, nfe.num_nota, nfe.tipo, nfe.obs_conf_prac, nfe.situacao 
        FROM `nfe` 
        INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
        WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
$campos         = bancos::sql($sql);
$razao_social   = $campos[0]['razaosocial'];
$num_nota       = $campos[0]['num_nota'];
if($campos[0]['tipo'] == 1) {//Tratamento para o Tipo de Nota
    $tipo = 'NF';
}else {
    $tipo = 'SGD';
}
$obs_conf_prac  = $campos[0]['obs_conf_prac'];
$situacao       = $campos[0]['situacao'];//Situação da Nota Fiscal
//Busca todos os Itens da NF em que os Itens são do Tipo PRAC para fazer a Conferência
$sql = "SELECT nfeh.id_nfe_historico, nfeh.id_item_pedido, nfeh.id_produto_insumo, nfeh.id_nfe, nfeh.qtde_entregue, nfeh.valor_entregue, nfeh.qtde_prac_conf, nfeh.responsavel_medidas 
        FROM `nfe_historicos` nfeh 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = nfeh.`id_produto_insumo` 
        WHERE nfeh.`id_nfe` = '$id_nfe' ";
$campos = bancos::sql($sql);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Conferência de Entrega do PRAC ::.</title>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'conferencia_entrega_prac.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false
    var elementos = document.form.elements
    for (i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            if (elementos[i].checked == true) valor = true
        }
    }

    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        if(typeof(elementos['chkt_nfe_historico[]'][0]) == 'undefined') {
            var linhas = 1//Existe apenas 1 único elemento ...
        }else {
            var linhas = (elementos['chkt_nfe_historico[]'].length)
        }
        for (var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_nfe_historico'+i).checked == true) {//Se tiver 1 checkbox selecionado
                //Se nenhum dos pesos estiverem preenchido, força o preenchimento
                if(document.getElementById('txt_qtde_prac_conf'+i).value == '') {
                    alert('DIGITE A QTDE !')
                    document.getElementById('txt_qtde_prac_conf'+i).focus()
                    return false
                }
            }
        }
        //Tratamento p/ gravar no BD ...
        for (var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_nfe_historico'+i).checked == true) {//Se tiver 1 checkbox selecionado
                document.getElementById('txt_qtde_prac_conf'+i).value = strtofloat(document.getElementById('txt_qtde_prac_conf'+i).value)
            }
        }
    }
}

function controlar_item(indice, peso_item) {
/************Controle para a Troca de Cores************/
//Comparação de Valores
    var qtde_nota       = eval(strtofloat(document.getElementById('txt_qtde_nota'+indice).value))
    var qtde_digitada   = eval(strtofloat(document.getElementById('txt_qtde_prac_conf'+indice).value))
//Enquanto a qtde Digitada for Diferente do Valor da Nota, está mantém a caixa na cor Vermelha
    if(qtde_digitada != qtde_nota) {
        document.getElementById('txt_qtde_nota'+indice).style.background    = 'red'
        document.getElementById('txt_qtde_nota'+indice).style.color         = 'white'
    }else {//Se = ou maior então ...
        document.getElementById('txt_qtde_nota'+indice).style.background    = '#FFFFE1'
        document.getElementById('txt_qtde_nota'+indice).style.color         = 'gray'
    }
    
    document.getElementById('txt_peso_total_kg'+indice).value = qtde_digitada * peso_item
    document.getElementById('txt_peso_total_kg'+indice).value = arred(document.getElementById('txt_peso_total_kg'+indice).value, 2, 1)
/******************************************************/
    calcular_peso_total_geral_kg()
}

function calcular_peso_total_geral_kg() {
    var elementos           = document.form.elements
    var peso_total_geral_kg = 0
    
    if(typeof(elementos['chkt_nfe_historico[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_nfe_historico[]'].length)
    }
    
    for (var i = 0; i < linhas; i++) {
        if(eval(strtofloat(document.getElementById('txt_peso_total_kg'+i).value)) > 0) peso_total_geral_kg+= eval(strtofloat(document.getElementById('txt_peso_total_kg'+i).value))
    }
    document.form.txt_peso_total_geral_kg.value = peso_total_geral_kg
    document.form.txt_peso_total_geral_kg.value = arred(document.form.txt_peso_total_geral_kg.value, 2, 1)
}

function visualizar_desenho_para_conferencia(desenho_para_conferencia) {
    nova_janela(desenho_para_conferencia, 'CONSULTAR', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_nfe' value='<?=$id_nfe;?>'>
<table width='95%' border='0' cellspacing='1' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='9'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            Conferência de Entrega do PRAC - 
            <font color='yellow'>N.º </font><?=$num_nota.' / '.$tipo;?><br>
            <font color='yellow'>Fornecedor: </font><?=$razao_social;?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            Qtde NF
        </td>
        <td>
            Qtde Conf
        </td>
        <td>
            Produto
        </td>
        <td>
            <font title='Responsável pela Última Alteração' style='cursor:help'>
                Resp
            </font>
        </td>
        <td>
            <font title='Preço Unitário' style='cursor:help'>
                Preço <br/>Unit.
            </font>
        </td>
        <td>
            <font title='Peso do PA Kg' style='cursor:help'>
                Peso do <br/>PA Kg
            </font>
        </td>
        <td>
            Desenho <br/>Conferência
        </td>
        <td>
            Peso Total <br/>Kg
        </td>
    </tr>
<?
    $cont = 0;

    for($i = 0; $i < $linhas; $i++) {
//Busca do Peso do PA p/ se o PI do Loop realmente for um "PIPA", esse será utilizado mais abaixo ...
        $sql = "SELECT pa.`id_produto_acabado`, pa.`peso_unitario`, 
                pa.`desenho_para_conferencia`, gpa.`desenho_para_conferencia` AS desenho_para_conferencia_grupo 
                FROM `produtos_acabados` pa 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                WHERE pa.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
        $campos_dados_gerais = bancos::sql($sql);
        if(count($campos_dados_gerais) == 1) {//Isso representa que é um PI puro mesmo, então trago o Peso do PI ...
            $id_produto_acabado         = $campos_dados_gerais[0]['id_produto_acabado'];
            $peso_unitario              = $campos_dados_gerais[0]['peso_unitario'];
            
            if(!empty($campos_dados_gerais[0]['desenho_para_conferencia'])) {
                $desenho_para_conferencia   = '../../../../../imagem/fotos_produtos_acabados/'.$campos_dados_gerais[0]['desenho_para_conferencia'];
            }else {
                $desenho_para_conferencia   = '../../../../../imagem/desenhos_grupos_pas/'.$campos_dados_gerais[0]['desenho_para_conferencia_grupo'];
            }
        }else {//Isso representa que é um PI puro mesmo, então trago o Peso do PI ...
            $sql = "SELECT pi.`peso` AS `peso_unitario`, 
                    pi.`desenho_para_conferencia`, g.`desenho_para_conferencia` AS desenho_para_conferencia_grupo 
                    FROM `produtos_insumos` pi 
                    INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_produto_insumo` 
                    WHERE pi.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
            $campos_dados_gerais        = bancos::sql($sql);
            $id_produto_acabado         = $campos_dados_gerais[0]['id_produto_acabado'];
            $peso_unitario              = $campos_dados_gerais[0]['peso_unitario'];
            
            if(!empty($campos_dados_gerais[0]['desenho_para_conferencia'])) {
                $desenho_para_conferencia   = '../../../../../imagem/fotos_produtos_insumos/'.$campos_dados_gerais[0]['desenho_para_conferencia'];
            }else {
                $desenho_para_conferencia   = '../../../../../imagem/desenhos_grupos_pis/'.$campos_dados_gerais[0]['desenho_para_conferencia_grupo'];
            }
        }
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8', '<?=$campos_dados_gerais[0]['id_produto_acabado'];?>', '<?=number_format($peso_unitario, 4, ',', '.');?>')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_nfe_historico[]' id='chkt_nfe_historico<?=$i;?>' value='<?=$campos[$i]['id_nfe_historico'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8', '<?=$campos_dados_gerais[0]['id_produto_acabado'];?>', '<?=number_format($peso_unitario, 4, ',', '.');?>')" class='checkbox'>
        </td>
        <?
/************Controle para a Troca de Cores************/
//Comparação de Valores
//Enquanto a qtde Digitada for Diferente do Valor da Nota, está mantém a caixa na cor Vermelha
            if($campos[$i]['qtde_prac_conf'] != $campos[$i]['qtde_entregue']) {
                $backcolor  = 'background:red';
                $color      = 'color:white';
            }else {//Se = ou maior então ...
                $backcolor  = 'background:#FFFFE1';
                $color      = 'color:gray';
            }
/******************************************************/
        ?>
        <td>
            <input type='text' name='txt_qtde_nota[]' id='txt_qtde_nota<?=$i;?>' value='<?=number_format($campos[$i]['qtde_entregue'], 2, ',', '.');?>' size='8' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8', '<?=$campos_dados_gerais[0]['id_produto_acabado'];?>', '<?=number_format($peso_unitario, 4, ',', '.');?>');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '1', event); if(this.value == '-') { this.value = ''}" align='right' style="<?=$backcolor.';'.$color;?>" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_qtde_prac_conf[]' id='txt_qtde_prac_conf<?=$i;?>' value="<?=number_format($campos[$i]['qtde_prac_conf'], 2, ',', '.');?>" size='8' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8', '<?=$campos_dados_gerais[0]['id_produto_acabado'];?>', '<?=number_format($peso_unitario, 4, ',', '.');?>');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '1', event); if(this.value == '-') { this.value = ''};controlar_item('<?=$i;?>', '<?=number_format($peso_unitario, 4, '.', '');?>')" align='right' tabindex="<?="1".$cont;?>" class='textdisabled' disabled>
        </td>
        <td align='left'>
            <a href="javascript:nova_janela('calculos_conferencia_entrega_prac.php?id_nfe_historico=<?=$campos[$i]['id_nfe_historico'];?>', 'CALCULOS', '', '', '', '', 300, 800, 'c', 'c')" title='Cálculos de Conferência de Entrega do PRAC' class='link'>
            <?
                $sql = "SELECT g.referencia, pi.discriminacao 
                        FROM `produtos_insumos` pi 
                        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
                        WHERE pi.`id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' LIMIT 1 ";
                $campos_pi = bancos::sql($sql);
                echo genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos_pi[0]['referencia']).' * '.$campos_pi[0]['discriminacao'];
        ?>
            </a>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['responsavel_medidas'])) echo "<img width='28' height='23' title='".$campos[$i]['responsavel_medidas']."' src='../../../../../imagem/olho.jpg'>";
        ?>
        </td>
        <td align='right'>
            <?=$moeda.number_format($campos[$i]['valor_entregue'], '2', ',', '.');?>
        </td>
        <td>
        <?
            if($peso_unitario == '0.00000000') {//Se o peso for Zero, vermelho ...
                $font_color = '<font color="red"><b>';
            }else {//Se o peso for maior que Zero então mostra em preto ...
                $font_color = '<font color="darkblue"><b>';
            }
            echo $font_color.number_format($peso_unitario, 4, ',', '.');
            $total_peso_unitario+= $peso_unitario;
        ?>
        </td>
        <td>
        <?
            if(!empty($desenho_para_conferencia)) {
        ?>
                <img src = '../../../../../imagem/impressora.gif' border='0' title='Visualizar Desenho para Conferência' alt='Visualizar Desenho para Conferência' onclick="visualizar_desenho_para_conferencia('<?=$desenho_para_conferencia;?>')" style='cursor:pointer'>
        <?
            }
        ?>
        </td>
        <td>
            <input type='text' name='txt_peso_total_kg[]' id='txt_peso_total_kg<?=$i;?>' value="<?=number_format($campos[$i]['qtde_prac_conf'] * $peso_unitario, 2, ',', '.');?>" size='8' align='right' class='textdisabled' disabled>
        </td>
    </tr>
<?
        $cont++;
        $peso_total_geral_kg+= $campos[$i]['qtde_prac_conf'] * $peso_unitario;
    }
?>
    <tr class='linhanormal'>
        <td colspan='8' align='right'>
            <font color='darkblue' size='-1'>
                Peso Total em KG =>
            </font>
        </td>
        <td align='center'>
            <input type='text' name='txt_peso_total_geral_kg' value='<?=number_format($peso_total_geral_kg, 2, ',', '.');?>' size='8' align='right' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td bgcolor='#E8E8E8'>
            Observação:
        </td>
        <td colspan='8' bgcolor='#E8E8E8'>
            <textarea name='txt_obs_conf_prac' cols='107' rows='3' maxlength='255' class='caixadetexto'><?=$obs_conf_prac;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_nfe=<?=$_GET['id_nfe'];?>'" class='botao'>
            <?
//Se a Nota Fiscal estiver fechada, então não posso + fazer modificações referentes as Conferências de Entr.
                if($situacao == 2) {
                    $disabled = 'disabled';
                    $aviso = 1;
                }
            ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style="color:green" class='botao' <?=$disabled;?>>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
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
if($situacao == 2) {//Se a NF estiver liberada, o ERP mostra essa mensagem comunicando ao usuário ...
?>
<pre>
<b><font color="red">Observação:</font></b>
<pre>

* ESTÁ NOTA FISCAL ESTÁ LIBERADA ! PORTANTO NÃO SE PODE MAIS ALTERAR AS CONFERÊNCIAS DE ENTREGA !!!
</pre>
<?}?>