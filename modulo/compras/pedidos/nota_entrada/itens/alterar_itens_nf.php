<?
//O arquivo alterar.php que faz requisição a esse arquivo já tem dentro dele todas as libs embutidas ...
$mensagem[1] = "<font class='confirmacao'>ITEM(NS) ATUALIZADO(S) COM SUCESSO.</font>";

if(!empty($_POST['id_nfe_historico'])) {
//Se existir a opção de Tipo de Ajuste, então eu atualizo estes campos de Item de Nota Fiscal também ...
    if(!empty($_POST['cmb_tipo_ajuste'])) $atualizar_item_nf = ", `cod_tipo_ajuste` = '$_POST[cmb_tipo_ajuste]', `nf_obs_abatimento` = '$_POST[txt_num_nf]' ";
//Atualizando o Item da NF ...
    $sql = "UPDATE `nfe_historicos` SET `qtde_entregue` = '$_POST[txt_qtde]', `valor_entregue` = '$_POST[txt_preco_unitario]', `ipi_entregue` = '$_POST[txt_ipi]', `icms_entregue` = '$_POST[txt_icms]', `reducao` = '$_POST[txt_reducao]', `iva` = '$_POST[txt_iva]', `marca` = '$_POST[txt_marca_obs]', `data_sys` = '".date('Y-m-d H:i:s')."' $atualizar_item_nf WHERE `id_nfe_historico` = '$_POST[id_nfe_historico]' LIMIT 1 ";
    bancos::sql($sql);
//Busca do id_item_pedido através do $id_nfe_historico, porque utilizo na função abaixo pedido_status()
    $sql = "SELECT id_item_pedido 
            FROM `nfe_historicos` 
            WHERE `id_nfe_historico` = '$_POST[id_nfe_historico]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_item_pedido = $campos[0]['id_item_pedido'];
    compras_new::pedido_status($id_item_pedido);
//Chamo a função p/ fazer a divisão das parcelas pelo jeito de Vencimento ...
    compras_new::calculo_valor_financiamento($_POST['id_nfe']);
    $valor = 1;
}

//Procedimento quando carrega a Tela ...
$id_nfe = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_nfe'] : $_GET['id_nfe'];
if(empty($posicao)) $posicao = 1;

//Seleção da qtde de Item(ns) existente(s) na Nota Fiscal de Entrada
$sql = "SELECT COUNT(id_nfe) AS qtde_itens 
        FROM `nfe_historicos` 
        WHERE `id_nfe` = $id_nfe";
$campos     = bancos::sql($sql);
$qtde_itens = $campos[0]['qtde_itens'];

//Através do $id_nfe eu busco qual é o id_item_pedido
$sql = "SELECT CONCAT(tm.`simbolo`, ' ') AS moeda, nfe.id_fornecedor, nfe.num_nota, nfe.tipo AS tipo_nota, nfeh.* 
        FROM `nfe_historicos` nfeh 
        INNER JOIN `nfe` ON nfe.`id_nfe` = nfeh.`id_nfe` 
        INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = nfe.`id_tipo_moeda` 
        WHERE nfeh.`id_nfe` = '$id_nfe' ";
$campos 		= bancos::sql($sql, ($posicao - 1), $posicao);
$id_nfe_historico 	= $campos[0]['id_nfe_historico'];
$id_nfe		 	= $campos[0]['id_nfe'];
$moeda 			= $campos[0]['moeda'];
$tipo_nota 		= $campos[0]['tipo_nota'];
$num_nota 		= $campos[0]['num_nota'];
$id_pedido 		= $campos[0]['id_pedido'];
$id_item_pedido         = $campos[0]['id_item_pedido'];

//Seleção de Dados do Item de Nota Fiscal Corrente
$sql = "SELECT ip.id_produto_insumo, ip.preco_unitario, ip.marca, g.referencia, pi.discriminacao 
        FROM `itens_pedidos` ip 
        INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` 
        INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` 
        WHERE ip.`id_item_pedido` = '$id_item_pedido' LIMIT 1 ";
$campos_item_pedido = bancos::sql($sql);
/******************************************************************************************/
/*******************************Verificação de Ajuste em NF********************************/
/******************************************************************************************/
/*Se o Item de NF pra ser alterado, for um Ajuste então, o Sistema não permite 
a alteração do mesmo ...*/
if($campos_item_pedido[0]['id_produto_insumo'] == 1340 || $campos_item_pedido[0]['id_produto_insumo'] == 1426 || $campos_item_pedido[0]['id_produto_insumo'] == 1749) {
?>
    <Script Language = 'JavaScript'>
        alert('ESSE ITEM NÃO PODE SER ALTERADO, DEVIDO SER UM ITEM DE AJUSTE !')
        window.close()
    </Script>
<?
    exit;
}
/******************************************************************************************/
?>
<html>
<head>
<title>.:: Alterar Itens de Nota Fiscal ::.</title>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function calculo() {
    var qtde            = document.form.txt_qtde.value
    var preco_unitario  = document.form.txt_preco_unitario.value
    var caracteres  = '0123456789,.-'
    for (y = 0; y < document.form.txt_qtde.value.length; y ++) {
        if(caracteres.indexOf(document.form.txt_qtde.value.charAt(y), 0) == -1) {
            document.form.txt_qtde.value = document.form.txt_qtde.value.replace(document.form.txt_qtde.value.charAt(y), '')
            return false
        }
    }
    var preco_unitario  = strtofloat(preco_unitario)
    var qtde            = strtofloat(qtde)
    resultado           = eval(qtde) * eval(preco_unitario)
    if(document.form.txt_qtde.value == '') resultado = 0
    if(document.form.txt_preco_unitario.value == '') resultado = 0
    if(resultado == 0) {
        resultado = ''
        document.form.txt_valor_total.value = resultado
    }else {
        document.form.txt_valor_total.value = resultado
        document.form.txt_valor_total.value = '<?=$moeda;?>' + arred(document.form.txt_valor_total.value, 2, 1)
    }
}

function validar(posicao, verificar) {
/*Aqui significa que estou submetendo o formulário através do botão submit, sendo
faz requisição das condições de validação*/
    if(typeof(verificar) != 'undefined') {
//Nessa tela, nem sempre vai existir essa combo de Tipo de Ajuste, por isso que tem esse controle ...
        if(typeof(document.form.cmb_tipo_ajuste) == 'object') {
//Tipo de Ajuste
            if(!combo('form', 'cmb_tipo_ajuste', '', 'SELECIONE O TIPO DE AJUSTE !')) {
                return false
            }
//Somente nessa opção "Abatimento de NF" que eu forço o preenchimento desse campo 'N.º NF'
            if(document.form.cmb_tipo_ajuste.value == 4) {//Habilitado ...
//N.º da NF
                if(!texto('form', 'txt_num_nf', '1', '1234567890', 'N.º DA NF', '2')) {
                    return false
                }
            }
        }
//Quantidade ...
        if(!texto('form', 'txt_qtde', '1', '1234567890,.-', 'QUANTIDADE', '1')) {
            return false
        }
//Preço Unitário ...
        if(!texto('form', 'txt_preco_unitario', '1', '1234567890.,-', 'PREÇO UNITÁRIO', '2')) {
            return false
        }
    }
    var preco_unitario  = eval(strtofloat(document.form.txt_preco_unitario.value))
    var preco_original  = eval(strtofloat(document.form.preco_original.value))
    preco_reajustado = (preco_original) * 1.1

//Quando o Preço for negativo, tem que transformar em Positivo p/ poder fazer comparação no if + abaixo
    if(preco_unitario < 0) {
        preco_unitario      = Math.abs(preco_unitario)
        preco_reajustado    = Math.abs(preco_reajustado)
    }
    if(preco_unitario > preco_reajustado) {
        alert('PREÇO UNITÁRIO INVÁLIDO !\n EXCEDIDO OS 10% PERMITIDO !')
        document.form.txt_preco_unitario.value = "<?=str_replace('.', ',', $campos[0]['valor_entregue']);?>"
        document.form.txt_preco_unitario.value = arred(document.form.txt_preco_unitario.value, 2, 1)
        document.form.txt_preco_unitario.focus()
        document.form.txt_preco_unitario.select()
        calculo()
        return false
    }
//Desabilito essas caixas p/ poder gravar no BD ...
    document.form.txt_ipi.disabled          = false
    document.form.txt_icms.disabled         = false
    document.form.txt_reducao.disabled      = false
    document.form.txt_iva.disabled          = false
    document.form.txt_marca_obs.disabled    = false
//Trata os campos p/ poder gravar no BD ...
    limpeza_moeda('form', 'txt_qtde, txt_preco_unitario, txt_ipi, txt_icms, txt_reducao, txt_iva, ')
//Recupera a posição corrente no hidden, para não dar erro de paginação
    document.form.posicao.value = posicao
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
//Submetendo o Formulário
    document.form.submit()
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}

function controlar_numero_nf() {
//Nessa tela, nem sempre vai existir essa combo de Tipo de Ajuste, por isso que tem esse controle ...
    if(typeof(document.form.cmb_tipo_ajuste) == 'object') {
//Somente nessa opção "Abatimento de NF" que eu habilito esse campo 'N.º NF'
        if(document.form.cmb_tipo_ajuste.value == 4) {//Habilitado ...
            document.form.txt_num_nf.className  = 'caixadetexto'
            document.form.txt_num_nf.disabled   = false
            document.form.txt_num_nf.value      = '<?=$campos[0]["nf_obs_abatimento"];?>'
            document.form.txt_num_nf.focus()
        }else {//Desabilitado ...
            document.form.txt_num_nf.className  = 'textdisabled'
            document.form.txt_num_nf.disabled   = true
            document.form.txt_num_nf.value      = ''
        }
    }
}
</Script>
</head>
<body onload='calculo();controlar_numero_nf();document.form.txt_preco_unitario.focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit="return validar('<?=$posicao;?>', 1)">
<!--Aqui é para quando for submeter-->
<input type='hidden' name='id_nfe' value="<?=$id_nfe;?>">
<input type='hidden' name='id_nfe_historico' value="<?=$id_nfe_historico;?>">
<!--Utiliza no auxilio de uma função de cálculo em JavaScript-->
<input type='hidden' name='preco_original' value="<?=str_replace('.', ',', $campos_item_pedido[0]['preco_unitario']);?>">
<!--Controle de Tela-->
<input type='hidden' name='posicao' value="<?=$posicao;?>">
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Itens de Nota Fiscal N.º 
            <font color='yellow'>
                <?=$num_nota;?>
            </font>
        </td>
    </tr>
<?
//Quando o Produto for do Tipo Ajuste, então eu carrego nessa tela, p/ que este possa ser alterado ...
	if($campos_item_pedido[0]['id_produto_insumo'] == 1340 || $campos_item_pedido[0]['id_produto_insumo'] == 1426 || $campos_item_pedido[0]['id_produto_insumo'] == 1749) {//Produto Ajuste
?>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Ajuste:</b>
        </td>
        <td>
            <select name='cmb_tipo_ajuste' title='Selecione o Tipo de Ajuste' onchange="controlar_numero_nf()" class='combo'>
                <?=combos::combo_array($tipos_ajustes, $campos[0]['cod_tipo_ajuste']);?>
            </select>
            &nbsp;&nbsp;<b>N.º da NF:</b>&nbsp;
            <input type="text" name="txt_num_nf" value="<?=$campos[0]['nf_obs_abatimento'];?>" title="Digite o N.º da NF" size="12" maxlength="10" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class="textdisabled" disabled>
        </td>
    </tr>
<?
	}else {//Quando outro Produto, eu só mostro os rótulos normalmente ...
?>
    <tr class='linhanormal'>
        <td>
            <b>Produto:</b>
        </td>
        <td>
        <?
            echo genericas::buscar_referencia($campos_item_pedido[0]['id_produto_insumo'], $campos_item_pedido[0]['referencia']).' * ';
            echo $campos_item_pedido[0]['discriminacao'];
        ?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhanormal'>
        <td>
            <b>Marca do Produto:</b>
        </td>
        <td>
            <?=$campos_item_pedido[0]['marca'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde' value="<?=number_format($campos[0]['qtde_entregue'], 2, ',', '.');?>" onKeyUp="verifica(this, 'moeda_especial', '2', '1', event);calculo()" size='12' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Pre&ccedil;o Unit&aacute;rio <?=$moeda;?>:</b>
        </td>
        <td>
            <input type='text' name='txt_preco_unitario' value="<?=str_replace('.', ',', $campos[0]['valor_entregue']);?>" onKeyUp="verifica(this, 'moeda_especial', '2', '', event); if(this.value == '-') {this.value = ''};calculo()" size='12' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Total:
        </td>
        <td>
            <input type='text' name='txt_valor_total' size='15' class='textdisabled' disabled>
        </td>
    </tr>
    <?
        //Essas caixas de Impostos sempre estarão liberadas somente p/ o usuário Roberto 62 e Dárcio porque programa ...
        if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 98) {
            $class      = 'caixadetexto';
            $disabled   = '';
        }else {
            if($campos[0]['tipo'] == 1) {//NF
                $tipo = 'NF';
    //Se o Produto Insumo for um Ajuste, então as caixas de IPI e ICMS vem desabilitadas ...
                if($campos_item_pedido[0]['id_produto_insumo'] == 1340) {
                    $class      = 'caixadetexto';
                    $disabled   = '';
                }else {//Se um outro produto normal, as caixas de IPI e ICMS vem travadas ..
                    $class      = 'textdisabled';
                    $disabled   = 'disabled';
                }
            }else {//SGD
                /*Mesmo que o PI seja do Tipo Ajuste, se a Nota for do Tipo SGD então as caixas de IPI e ICMS vem 
                sempre desabilitadas ...*/
                $class      = 'textdisabled';
                $disabled   = 'disabled';
            }
        }
    ?>
    <tr class='linhanormal'>
        <td>
            IPI %:
        </td>
        <td>
            <input type='text' name='txt_ipi' value="<?=number_format($campos[0]['ipi_entregue'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ICMS %:
        </td>
        <td>
            <input type='text' name='txt_icms' value="<?=number_format($campos[0]['icms_entregue'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Redução %:
        </td>
        <td>
            <input type='text' name='txt_reducao' value="<?=number_format($campos[0]['reducao'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            IVA %:
        </td>
        <td>
            <input type='text' name='txt_iva' value="<?=number_format($campos[0]['iva'], 2, ',', '.');?>" onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='12' class='<?=$class;?>' <?=$disabled;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Marca / Obs:
        </td>
        <td>
            <textarea name='txt_marca_obs' rows='2' cols='50' maxlength='100' class='<?=$class;?>' <?=$disabled;?>><?=$campos[0]['marca'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');calculo();document.form.txt_preco_unitario.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
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
            if($posicao < $qtde_itens) echo "&nbsp;&nbsp;<b><a href='#' onclick='validar(($posicao+1))' class='link'><font size='2' face='verdana, arial, helvetica, sans-serif'> &gt;&gt; Próxima &gt;&gt; </font></a>&nbsp;</b>";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>