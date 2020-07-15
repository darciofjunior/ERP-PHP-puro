<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');
require('../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/producao/os/incluir.php', '../../../../');

$mensagem[1] = "<font class='confirmacao'>O.S. ALTERADA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>EST� O.S. N�O PODE SER ALTERADA DEVIDO CONSTAR EM NOTA FISCAL.</font>";

if($passo == 1) {
/**********Aqui eu verifico se a OS est� importada na NF**********/
//Caso esta OS esteje importada em NF, eu n�o posso alterar nenhum item de Cabe�alho ...
    $sql = "SELECT `id_nfe` 
            FROM `nfe_historicos` 
            WHERE `id_pedido` = '$_POST[id_pedido]' 
            AND `id_pedido` <> '0' LIMIT 1 ";
    $campos_nfe = bancos::sql($sql);
    if(count($campos_nfe) == 0) {//Ainda n�o est� em Nota Fiscal ...
        $data_saida = ($_POST['txt_data_saida'] != '') ? data::datatodate($_POST['txt_data_saida'], '-') : '';
        
        $sql = "UPDATE `oss` SET `id_empresa` = '$_POST[cmb_empresa]', `nf_minimo_tt` = '$_POST[txt_nf_minimo_tt]', `nnf` = '$_POST[txt_nossa_nota_fiscal]', `data_saida` = '$data_saida', `qtde_caixas` = '$_POST[txt_qtde_caixas]', `peso_caixas` = '$_POST[txt_peso_total_caixas]', `peso_liq` = '$_POST[txt_peso_liquido]', `observacao` = '$_POST[txt_observacao]' WHERE `id_os` = '$_POST[id_os]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }else {//J� est� em Nota Fiscal, ent�o ...
        $valor = 2;
    }
/************************************/
?>
    <Script Language='JavaScript'>
        window.location = 'alterar_cabecalho.php?id_os=<?=$_POST['id_os'];?>&veio_outra_tela=<?=$veio_outra_tela;?>&valor=<?=$valor?>'
//S� atualiza os Frames quando esse cabe�alho for acessado de dentro da pr�pria OS ...
        var veio_outra_tela = eval('<?=$veio_outra_tela;?>')
        if(veio_outra_tela != 1) {//Significa que a tela foi acessada de dentro da pr�pria OS mesmo
            window.opener.parent.itens.document.form.submit()
            window.opener.parent.rodape.document.form.submit()
        }
    </Script>
<?
}else {
//Aqui traz os dados da OS
    $sql = "SELECT f.`razaosocial`, f.`nf_minimo_tt` AS nf_minimo_tt_cad, oss.* 
            FROM `oss` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` 
            WHERE oss.`id_os` = '$_GET[id_os]' 
            AND oss.`ativo` = '1' LIMIT 1 ";
    $campos = bancos::sql($sql);
    $fornecedor     = $campos[0]['razaosocial'];
    $id_pedido      = $campos[0]['id_pedido'];//Pedido de Compra ...
    $id_nf_outra    = $campos[0]['id_nf_outra'];//NF de Sa�da ...
/********************************Controle para apresenta��o dos Dados********************************/
    //Busco o Status da OS para saber de quais locais de qual local que eu vou buscar o lote m�nimo e a nf m�nima ...
    if($id_pedido == 0 && $id_nf_outra == 0) {//Essa OS ainda n�o foi importada, ent�o busco do Cadastro do Fornecedor ...
        $nf_minimo_tt               = $campos[0]['nf_minimo_tt_cad'];//Uso para comparar no JavaScript ...
        $class                      = 'caixadetexto';//Sempre dispon�vel ...
        $disabled                   = '';//Sempre dispon�vel ...
    }else {//Essa OS j� foi importada p/ Pedido ou NF sendo assim, eu busco os valores da pr�pria OS ...
        $nf_minimo_tt               = $campos[0]['nf_minimo_tt'];//Uso para comparar no JavaScript ...
/****************************************************************************************************/
//Busco o Valor Total da OS, eu trago esse valor para um controle que eu fa�o depois em JavaScript ...
        $sql = "SELECT SUM(`peso_total_saida` * `preco_pi`) AS valor_total_os 
                FROM `oss_itens` 
                WHERE `id_os` = '$_GET[id_os]' ";
        $campos_os      = bancos::sql($sql);
        $valor_total_os = round($campos_os[0]['valor_total_os'], 2);
//Verifico se o Pedido � do Tipo NF ou SGD, q vai me servir p/ controlar o cabe�alho de SGD na OS
        $sql = "SELECT `tipo_nota` 
                FROM `pedidos` 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos_pedido 	= bancos::sql($sql);
        $tipo_pedido 	= $campos_pedido[0]['tipo_nota'];
/**********Aqui eu verifico se a OS est� importada na NF**********/
//Caso esta OS esteje importada em NF, eu n�o posso alterar nenhum item de Cabe�alho ...
        $sql = "SELECT `id_nfe` 
                FROM `nfe_historicos` 
                WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
        $campos_pedido  = bancos::sql($sql);
        if(count($campos_pedido) == 0) {//Ainda n�o est� em Nota Fiscal ...
            $class      = 'caixadetexto';
            $disabled   = '';
        }else {//J� est� em Nota Fiscal, ent�o ...
            $class      = 'textdisabled';
            $disabled   = 'disabled';
        }
/************************************/
    }
?>
<html>
<head>
<title>.:: Alterar OS ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var tipo_pedido = eval('<?=$tipo_pedido;?>')
//Empresa
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE A EMPRESA !')) {
        return false
    }
//Data de Sa�da ...
    if(document.form.txt_data_saida.value != '') {
        if(!data('form', 'txt_data_saida', '4000', 'SA�DA')) {
            return false
        }
    }
//Nossa Nota Fiscal
    if(typeof(document.form.txt_nossa_nota_fiscal) == 'object' && document.form.txt_nossa_nota_fiscal.disabled == false) {
        if(!texto('form', 'txt_nossa_nota_fiscal', '1', '1234567890', 'NOSSA NOTA FISCAL N.�', '1')) {
            return false
        }
    }
//Qtde de Caixas
    if(!texto('form', 'txt_qtde_caixas', '1', '1234567890', 'QTDE DE CAIXAS', '1')) {
        return false
    }
//Peso das Caixas
    if(!texto('form', 'txt_peso_total_caixas', '1', '1234567890,.', 'PESO DAS CAIXAS', '2')) {
        return false
    }
//Peso L�quido
    if(!texto('form', 'txt_peso_liquido', '1', '1234567890,.', 'PESO L�QUIDO', '2')) {
        return false
    }
/**Tratamento com a Parte de Cabe�alho da OS com o Cabe�alho do Pedido**/
//OS j� est� importada em Pedido
    if(typeof(tipo_pedido != 'undefined')) {
        if(tipo_pedido == 1) {//Se o Pedido for NF
//Se tiver checado, ent�o significa que � uma OS do Tipo SGD ...
            if(document.form.chkt_sgd.checked == true) {
                alert('TIPO DE CABE�ALHO INV�LIDO !\nO PEDIDO DE COMPRAS � DO TIPO NF !!!')
                return false
            }
        }else if(tipo_pedido == 2) {//Se o Pedido for SGD
//Se n�o tiver checado, ent�o significa que � uma OS do Tipo NF ...
            if(document.form.chkt_sgd.checked == false) {
                alert('TIPO DE CABE�ALHO INV�LIDO !\nO PEDIDO DE COMPRAS � DO TIPO SGD !!!')
//Jogo o Layout de Desabilitado ...
                document.form.chkt_sgd.checked = true
                document.form.txt_nossa_nota_fiscal.className = 'textdisabled'
                document.form.txt_nossa_nota_fiscal.value = ''//Limpa a Caixa
                document.form.txt_nossa_nota_fiscal.disabled = true//Desabilita a Caixa
                return false
            }
        }
    }
/***********************************************************************/
//Parte de Compara��o da OS com o Valor de nf_minimo_tt
    var nf_minimo_tt = eval(strtofloat(document.form.txt_nf_minimo_tt.value))
    var valor_total_os = eval('<?=$valor_total_os;?>')

    if(valor_total_os <= nf_minimo_tt) {
        alert('ESTA O.S. N�O ATINGIU O VALOR DE NF M�NIMO !')
    }
//Desabilita para poder gravar no BD
    document.form.cmb_empresa.disabled = false
    document.form.txt_nf_minimo_tt.disabled = false
    return limpeza_moeda('form', 'txt_peso_total_caixas, txt_peso_liquido, txt_nf_minimo_tt, ')
}

function controlar_digitos(objeto) {
    if(objeto.value.length > 1) {//Se tiver pelo menos 2 d�gitos ...
        if(objeto.value.substr(0, 1) == '0') objeto.value = objeto.value.substr(1, 1)
    }
}

function calcular_peso_bruto() {
//Peso das Caixas
    var peso_caixas     = (document.form.txt_peso_total_caixas.value == '') ? 0 : eval(strtofloat(document.form.txt_peso_total_caixas.value))
//Peso L�quido
    var peso_liquido    = (document.form.txt_peso_liquido.value == '') ? 0 : eval(strtofloat(document.form.txt_peso_liquido.value))
//Aqui � o c�lculo do somat�rio desses 2 valores
    document.form.txt_peso_bruto.value = peso_caixas + peso_liquido
    document.form.txt_peso_bruto.value = arred(document.form.txt_peso_bruto.value, 3, 1)
}

//Simplesmente iguala o Peso L�quido da Caixa 2 para o Peso L�quido da Caixa 1
function igualar() {
    document.form.txt_peso_liquido.value = document.form.txt_peso_liquido2.value
}

function sgd() {
    var tipo_pedido = eval('<?=$tipo_pedido;?>')
//Se tiver checado, ent�o significa que � uma OS do Tipo SGD ...
    if(document.form.chkt_sgd.checked == true) {
//OS ainda n�o est� importada em Pedido
        if(typeof(tipo_pedido == 'undefined')) {
//Jogo o Layout de Desabilitado ...
            document.form.txt_nossa_nota_fiscal.className = 'textdisabled'
            document.form.txt_nossa_nota_fiscal.value = ''//Limpa a Caixa
            document.form.txt_nossa_nota_fiscal.disabled = true//Desabilita a Caixa
//OS j� est� Importada para Pedido
        }else {
//Significa que o Pedido � do Tipo NF, ent�o nosso posso colocar um cabe�alho de OS como sendo SGD
            if(tipo_pedido == 1) {
                alert('TIPO DE CABE�ALHO INV�LIDO !\nO PEDIDO DE COMPRAS � DO TIPO NF !!!')
                document.form.chkt_sgd.checked = false
                document.form.txt_nossa_nota_fiscal.focus()
            }else {//Significa que o Pedido � do Tipo SGD, ent�o posso colocar normalmente um cab. SGD p/ OS
//Jogo o Layout de Desabilitado ...
                document.form.txt_nossa_nota_fiscal.className = 'textdisabled'
                document.form.txt_nossa_nota_fiscal.value = ''//Limpa a Caixa
                document.form.txt_nossa_nota_fiscal.disabled = true//Desabilita a Caixa
            }
        }
//N�o est� checado, ent�o � uma OS com NF ...
    }else {
//OS ainda n�o est� importada em Pedido
        if(typeof(tipo_pedido == 'undefined')) {
//Jogo o Layout de Habilitado ...
            document.form.txt_nossa_nota_fiscal.className = 'caixadetexto'
            document.form.txt_nossa_nota_fiscal.value = '<?=$campos[0]['nnf'];?>'//Caixa c/ Valor = OS
            document.form.txt_nossa_nota_fiscal.disabled = false//Habilita a Caixa
            document.form.txt_nossa_nota_fiscal.focus()
//OS j� est� Importada para Pedido
        }else {
//Significa que o Pedido � do Tipo NF, ent�o posso colocar normalmente um cab. NF p/ OS
            if(tipo_pedido == 1) {
                //Jogo o Layout de Habilitado ...
                document.form.txt_nossa_nota_fiscal.className = 'caixadetexto'
                document.form.txt_nossa_nota_fiscal.value = '<?=$campos[0]['nnf'];?>'//Caixa c/ Valor = OS
                document.form.txt_nossa_nota_fiscal.disabled = false//Habilita a Caixa
                document.form.txt_nossa_nota_fiscal.focus()
            }else {//Significa que o Pedido � do Tipo SGD, ent�o n�o posso colocar um cab. NF p/ OS
                alert('TIPO DE CABE�ALHO INV�LIDO !\nO PEDIDO DE COMPRAS � DO TIPO SGD !!!')
                document.form.chkt_sgd.checked = true
//Jogo o Layout de Desabilitado ...
                document.form.txt_nossa_nota_fiscal.className = 'textdisabled'
                document.form.txt_nossa_nota_fiscal.value = ''//Limpa a Caixa
                document.form.txt_nossa_nota_fiscal.disabled = true//Desabilita a Caixa
            }
        }
    }
}
</Script>
<body onload='document.form.txt_data_saida.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='id_os' value='<?=$_GET['id_os'];?>'>
<!--Guardo esse campo aki para facilitar na hora de dar Update-->
<input type='hidden' name='id_pedido' value="<?=$id_pedido;?>">
<!--
***Controle de Tela***

Significa que esse Cabe�alho foi acessado de algum outro lugar que n�o seja desse m�dulo 
de Produ��o, como de Pedido de Compras, Nota Fiscal, etc ...-->
<input type='hidden' name='veio_outra_tela' value="<?=$veio_outra_tela;?>">
<table width='95%' border="0" cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar OS N.�&nbsp;
            <font color='yellow'>
                <?=$_GET['id_os'];?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Fornecedor:
        </td>
        <td>
            <?=$fornecedor;?>
        </td>
    </tr>
    <?
        //Se a OS tiver importada em Pedido, j� n�o � mais poss�vel estar alterando a Empresa da OS ...
        $class_empresa 		= ($id_pedido > 0) ? 'textdisabled' : 'caixadetexto';
        $disabled_empresa 	= ($id_pedido > 0) ? 'disabled' : '';
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' class='<?=$class_empresa;?>' <?=$disabled_empresa;?>>
            <?
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY `nomefantasia` ";
                //Essa op��o s� ir� servir pela 1� vez, quando n�o a OS ainda n�o tiver nenhuma empresa gravada ...
                $id_empresa_selected = (!empty($campos[0]['id_empresa'])) ? $campos[0]['id_empresa'] : intval(genericas::variavel(6));
                echo combos::combo($sql, $id_empresa_selected);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.� do Pedido:
        </td>
        <td>
        <?
            //Se j� estiver importado p/ Pedido ent�o eu exibo o N.� de Pedido ...
            if(empty($id_pedido)) {echo '-';}else {echo $id_pedido;}
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            NF M�nimo (Tratamento T�rmico) R$:
        </td>
        <td>
            <input type='text' name='txt_nf_minimo_tt' value='<?=number_format($nf_minimo_tt, 2, ',', '.');?>' title='NF M�nimo (Tratamento T�rmico) R$' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Sa�da:
        </td>
        <td>
            <?
                $data_saida = ($campos[0]['data_saida'] != '0000-00-00') ? data::datetodata($campos[0]['data_saida'], '/') : '';
            ?>
            <input type='text' name='txt_data_saida' value='<?=$data_saida;?>' title='Data de Sa�da' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_saida&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nossa Nota Fiscal N.�:</b>
        </td>
        <td>
        <?
//Verifico se essa OSS est� Importada em NF Outra(s) de Faturamento ...
            if($campos[0]['id_nf_outra'] > 0) {//Se sim, ent�o j� est� no processo automatizado ...
        ?>
                <a href="javascript:nova_janela('../../faturamento/outras_nfs/itens/detalhes_nota_fiscal.php?id_nf_outra=<?=$campos[0]['id_nf_outra'];?>&pop_up=1', 'DETALHES', '', '', '', '', 700, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Detalhes de Nota Fiscal" style="cursor:help" class="link">
                        <?=faturamentos::buscar_numero_nf($campos[0]['id_nf_outra'], 'O');?>
                </a>
        <?
            }else {//Aqui ainda � o Processo Manual aonde se Digita o N.� de NF Manualmente - Antigo ...
//Controle para habilitar ou travar o campo de SGD ...
/*Nesse caso eu criei outras vari�veis como $class_nossa_nf, $disabled_nossa_nf
para n�o dar conflito com as vari�veis $class, $disabled que eu j� estou utilizando para 
outro controle de OS com a Nota Fiscal de Compras*/
//Como o Nossa Nota Fiscal N.� � nulo ent�o, significa que essa OS � do Tipo SGD ...
                if($campos[0]['nnf'] == '') {//SGD, travo o campo de Nossa Nota Fiscal N.�
                    $class_nossa_nf     = 'textdisabled';
                    $disabled_nossa_nf  = 'disabled';
                    $checked            = 'checked';
                }else {//NF, habilito o campo de Nossa Nota Fiscal N.�
                    $class_nossa_nf     = $class;
                    $disabled_nossa_nf  = $disabled;
                    $checked            = '';
                }
        ?>
            <input type='text' name='txt_nossa_nota_fiscal' value='<?=$campos[0]['nnf'];?>' title='Digite a Nossa Nota Fiscal' size='12' maxlength='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='<?=$class_nossa_nf;?>' <?=$disabled_nossa_nf?>>
            &nbsp;<input type='checkbox' name='chkt_sgd' value='1' title='Selecione o SGD' id='label1' onclick='sgd()' class='checkbox' <?=$checked;?>>
            <label for='label1'>
                SGD
            </label>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Caixas:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_caixas' value='<?=$campos[0]['qtde_caixas'];?>' title='Digite a Qtde de Caixas' size='12' maxlength='10' onKeyUp="verifica(this, 'aceita', 'numeros', '', event);controlar_digitos(this)" class='<?=$class;?>' <?=$disabled?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso Total das Caixas:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_total_caixas' value='<?=number_format($campos[0]['peso_caixas'], 3, ',', '.');?>' title='Digite o Peso das Caixas' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '3', '', event);calcular_peso_bruto()" class='<?=$class;?>' <?=$disabled?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso L�quido:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_liquido' value='<?=number_format($campos[0]['peso_liq'], 4, ',', '.');?>' title='Digite o Peso L�quido' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '4', '', event);calcular_peso_bruto()" class='<?=$class;?>' <?=$disabled?>> KG
            &nbsp;-&nbsp;
            <?
//Aqui eu fa�o o Peso L�q. Total de Sa�da de Todos os Itens da OS, que seria uma sugest�o do Sistema ...
                $sql = "SELECT SUM(`peso_total_saida`) AS total_saida 
                        FROM `oss_itens` 
                        WHERE `id_os` = '$_GET[id_os]' ";
                $campos_peso_total_saida    = bancos::sql($sql);
                $total_saida                = $campos_peso_total_saida[0]['total_saida'];
            ?>
            <input type='text' name='txt_peso_liquido2' value='<?=number_format($total_saida, 4, ',', '.');?>' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;
            <a href='javascript:igualar();calcular_peso_bruto()' title='Igualar' alt='Igualar' style='cursor:help' class='link'>
                <b>Sugerido</b>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Peso Bruto:
        </td>
        <td>
            <?
                $peso_bruto = $campos[0]['peso_caixas'] + $campos[0]['peso_liq'];
            ?>
            <input type='text' name='txt_peso_bruto' value='<?=number_format($peso_bruto, 3, ',', '.');?>' title='Peso Bruto' size='12' maxlength='10' class='textdisabled' disabled> KG
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observa��o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='85' rows='3' maxlength='255' class='<?=$class;?>' <?=$disabled?>><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_nossa_nota_fiscal.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao' <?=$disabled;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>