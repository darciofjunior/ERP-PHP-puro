<?
require('../../../../lib/segurancas.php');

if(empty($_GET['pop_up']))  require '../../../../lib/menu/menu.php';//Só exibo esse botão se esta Tela foi aberta do modo Normal ...

require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='erro'>NÃO EXISTE ITEM(NS) DE ESTOQUE EXCEDENTE REGISTRADO(S) P/ ESTE PA.</font>";
$mensagem[3] = "<font class='confirmacao'>ESTOQUE EXCEDENTE ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
    //Busca desses dados a mais do PA ...
    $sql = "SELECT pa.`peso_unitario`, u.`sigla` 
            FROM `produtos_acabados` pa 
            INNER JOIN unidades u ON u.`id_unidade` = pa.`id_unidade` 
            WHERE pa.`id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
    $campos_pa = bancos::sql($sql);
    
//Aqui eu busco todos os Registros de Estoque Excedentes registrados do PA passado por parâmetro ...
    $sql = "SELECT * 
            FROM `estoques_excedentes` 
            WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' 
            AND `status` = '0' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Significa que não há registros ...
?>
        <Script Language = 'JavaScript'>
            window.location = 'alterar.php<?=$parametro;?>&valor=2'
        </Script>
<?
    }else {//Existe pelo menos 1 registro ...
        $retorno        = estoque_acabado::qtde_estoque($_GET['id_produto_acabado'], 1);
        $estoque_real 	= $retorno[0];
?>
<html>
<title>.:: Alterar Estoque Excedente de PA ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Forço o usuário a preencher pelo menos 1 Registro ...
    var valor = false
    for (var i = 0; i < document.form.elements.length; i++) {
        if (document.form.elements[i].type == 'checkbox')  {
            if (document.form.elements[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
    var qtde_estoque 	= eval('<?=$retorno[0];?>')
    var linhas 			= eval('<?=$linhas;?>')
//Valida de acordo com a Quantidade de Registros ...
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_estoque_excedente'+i).checked == true) {//Se algum Checkbox estiver habilitado ...
            /*if(document.getElementById('txt_qtde'+i).value == 0 || document.getElementById('txt_qtde'+i).value == '') {
                alert('DIGITE A QUANTIDADE !')
                document.getElementById('txt_qtde'+i).focus()
                return false
            }*/
            
            //Embalado ...
            if(document.getElementById('cmb_embalado'+i).value == '') {
                alert('SELECIONE UMA OPÇÃO P/ EMBALADO !')
                document.getElementById('cmb_embalado'+i).focus()
                return false
            }
            
            //Provavelmente o usuário só esta querendo editar uma observação e não mexeu na Qtde ...
            if(strtofloat(document.getElementById('txt_qtde'+i).value) == 0 || document.getElementById('txt_qtde'+i).value == '') {
                var resposta = confirm('QUANTIDADE = ZERO OU QUANTIDADE VAZIA !!!\n\nTEM CERTEZA DE QUE DESEJA CONTINUAR ?')
                document.getElementById('txt_qtde'+i).focus()
                document.getElementById('txt_qtde'+i).select()
                return false
            }

            /*****************************Controle com o Estoque Real*****************************/
            /*if(strtofloat(document.getElementById('txt_qtde'+i).value) < 0) {//Significa que o user está retirando do Estoque ...
                if(Math.abs(strtofloat(document.getElementById('txt_qtde'+i).value)) > strtofloat(document.getElementById('txt_total_excedente'+i).value)) {
                    alert('QUANTIDADE INVÁLIDA !!!\nQUANTIDADE MAIOR QUE A QUANTIDADE INICIAL ! ')
                    document.getElementById('txt_qtde'+i).focus()
                    document.getElementById('txt_qtde'+i).select()
                    return false
                }
            }*/
            /*************************************************************************************/
        }
    }
    if(document.getElementById('txt_saldo_total').value > qtde_estoque) {
        var resposta = confirm('ESTOQUE EXCEDENTE ESTÁ MAIOR QUE O ESTOQUE REAL, TEM QUE FAZER O INVENTÁRIO !!!\nDESEJA CONTINUAR ?')
        if(resposta == false) {
            return false
        }
    }
//Trata e desabilita o campo para poder gravar no Banco ...
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_estoque_excedente'+i).checked == true) {//Significa que está habilitado o checkbox da Linha ...
            document.getElementById('txt_qtde'+i).value = strtofloat(document.getElementById('txt_qtde'+i).value)
            document.getElementById('txt_saldo'+i).value = strtofloat(document.getElementById('txt_saldo'+i).value)
            document.getElementById('txt_saldo'+i).disabled = false
        }
    }
//Aqui eu desabilito o botão Salvar p/ não acontecer de o usuário clicar várias vezes ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
}

function calcular(indice) {
    var total_excedente = eval(strtofloat(document.getElementById('txt_total_excedente'+indice).value))
    if(document.getElementById('txt_qtde'+indice).value == 0) {//Volta a apresentar o Total Excedente ...
        document.getElementById('txt_saldo'+indice).value = total_excedente
    }else {
        document.getElementById('txt_saldo'+indice).value = total_excedente + eval(strtofloat(document.getElementById('txt_qtde'+indice).value))
    }
    var linhas 		= eval('<?=$linhas;?>')
    var saldo_total = 0
//Valida de acordo com a Quantidade de Registros ...
    for(var j = 0; j < linhas; j++) {
        saldo_total+= eval(document.getElementById('txt_saldo'+j).value)
    }
    document.getElementById('txt_saldo_total').value = saldo_total
}

function habilitar(indice) {
    var linhas = eval('<?=$linhas;?>')
    if(typeof(indice) == 'undefined') {//Significa que o Controle é com Checkbox Principal ...
//Valida de acordo com a Quantidade de Registros ...
        for(i = 0; i < linhas; i++) {
            if(document.form.chkt_tudo.checked == true) {//Significa que está habilitado o checkbox Principal ...
                document.getElementById('chkt_estoque_excedente'+i).checked = true
                //Muda o Layout dos objetos para Habilitado ...
                document.getElementById('txt_qtde'+i).className             = 'caixadetexto'
                document.getElementById('cmb_embalado'+i).className         = 'combo'
                document.getElementById('cmb_produto_acabado_faltante'+i).className = 'caixadetexto'
                document.getElementById('txt_observacao'+i).className       = 'caixadetexto'
                //Habilita os objetos ...
                document.getElementById('txt_qtde'+i).disabled              = false
                document.getElementById('cmb_embalado'+i).disabled          = false
                document.getElementById('cmb_produto_acabado_faltante'+i).disabled = false
                document.getElementById('txt_observacao'+i).disabled        = false
            }else {//Significa que está desabilitado o checkbox Principal ...
                document.getElementById('chkt_estoque_excedente'+i).checked = false
                document.getElementById('txt_qtde'+i).value = ''
                //Muda o Layout dos objetos para Desabilitado ...
                document.getElementById('txt_qtde'+i).className             = 'textdisabled'
                document.getElementById('cmb_embalado'+i).className         = 'textdisabled'
                document.getElementById('cmb_produto_acabado_faltante'+i).className = 'textdisabled'
                document.getElementById('txt_observacao'+i).className       = 'textdisabled'
                //Desabilita os objetos ...
                document.getElementById('txt_qtde'+i).disabled              = true
                document.getElementById('cmb_embalado'+i).disabled          = true
                document.getElementById('cmb_produto_acabado_faltante'+i).disabled = true
                document.getElementById('txt_observacao'+i).disabled        = true
                calcular(i)
            }
        }
    }else {//Significa que o Controle é com os Checkbox da Linha ...
        if(document.getElementById('chkt_estoque_excedente'+indice).checked == true) {//Significa que está habilitado o checkbox da Linha ...
            //Muda o Layout dos objetos para Habilitado ...
            document.getElementById('txt_qtde'+indice).className                        = 'caixadetexto'
            document.getElementById('cmb_embalado'+indice).className                    = 'combo'
            document.getElementById('cmb_produto_acabado_faltante'+indice).className    = 'combo'
            document.getElementById('txt_observacao'+indice).className                  = 'caixadetexto'
            //Habilita os objetos ...
            document.getElementById('txt_qtde'+indice).disabled             = false
            document.getElementById('cmb_embalado'+indice).disabled         = false
            document.getElementById('cmb_produto_acabado_faltante'+indice).disabled = false
            document.getElementById('txt_observacao'+indice).disabled       = false
            document.getElementById('txt_qtde'+indice).focus()
        }else {//Significa que está desabilitado o checkbox da Linha ...
            document.getElementById('txt_qtde'+indice).value                = ''
            //Muda o Layout dos objetos para Desabilitado ...
            document.getElementById('txt_qtde'+indice).className            = 'textdisabled'
            document.getElementById('cmb_embalado'+indice).className        = 'textdisabled'
            document.getElementById('cmb_produto_acabado_faltante'+indice).className = 'textdisabled'
            document.getElementById('txt_observacao'+indice).className      = 'textdisabled'
            //Desabilita os objetos ...
            document.getElementById('txt_qtde'+indice).disabled             = true
            document.getElementById('cmb_embalado'+indice).disabled         = true
            document.getElementById('cmb_produto_acabado_faltante'+indice).disabled = true
            document.getElementById('txt_observacao'+indice).disabled       = true
            calcular(indice)
        }
        var selecionados = 0
        //Verifico se todos os checkbox das Linhas estão selecionados ...
        for(i = 0; i < linhas; i++) if(document.getElementById('chkt_estoque_excedente'+i).checked == true) selecionados++
        if(linhas == selecionados) {//Significa que todas as linhas estão selecionadas ...
            document.form.chkt_tudo.checked = true
        }else {//Nem todas as linhas então ...
            document.form.chkt_tudo.checked = false
        }
    }
}

function retirar_material(indice) {
    document.getElementById('chkt_estoque_excedente'+indice).checked = true
    //Muda o Layout dos objetos para Habilitado ...
    document.getElementById('txt_qtde'+indice).className                        = 'caixadetexto'
    document.getElementById('cmb_produto_acabado_faltante'+indice).className    = 'combo'
    document.getElementById('txt_observacao'+indice).className                  = 'caixadetexto'
    //Habilita os objetos ...
    document.getElementById('txt_qtde'+indice).disabled                         = false
    document.getElementById('txt_qtde'+indice).value = '-'+eval(strtofloat(document.getElementById('txt_total_excedente'+indice).value))
    document.getElementById('cmb_produto_acabado_faltante'+indice).disabled     = false
    document.getElementById('txt_observacao'+indice).disabled                   = false
    document.getElementById('txt_qtde'+indice).focus()
    calcular(indice)
}
</Script>
</head>
<body onload="document.getElementById('txt_qtde0').focus()">
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit='return validar()'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Alterar Estoque Excedente de PA 
            <font color='yellow'>
                <br><?=intermodular::pa_discriminacao($_GET['id_produto_acabado']);?>
            </font>
            &nbsp;-&nbsp;
            <font color='yellow'>
                Peso Unitário: <?=number_format($campos_pa[0]['peso_unitario'], 8, ',', '.');?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick='habilitar()' title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Total Excedente
        </td>
        <td>
            Qtde
        </td>
        <td>
            Saldo
        </td>
        <td>
            Embalado
        </td>
        <td>
            Prateleira
        </td>
        <td>
            Bandeja
        </td>
        <td>
            Item Faltante (Opcional)
        </td>
        <td>
            Observação (Geral)
        </td>
        <td>
            Observação (Opcional)
        </td>
    </tr>
<?
        for($i = 0 ; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <input type='checkbox' name='chkt_estoque_excedente[]' id='chkt_estoque_excedente<?=$i;?>' value='<?=$campos[$i]['id_estoque_excedente'];?>' onclick="habilitar('<?=$i;?>')" class='checkbox'>
        </td>
        <td>
            <?
                //Verifico se o Item possui Estoque Excedente, mas somente do que está "Em aberto" ...
                $sql = "SELECT SUM(`qtde`) AS total_excedente 
                        FROM `estoques_excedentes` 
                        WHERE `id_estoque_excedente` = '".$campos[$i]['id_estoque_excedente']."' 
                        AND `status` = '0' ";
                $campos_excedente = bancos::sql($sql);
            ?>
            <input type='text' name="txt_total_excedente[]" id="txt_total_excedente<?=$i;?>" value="<?=number_format($campos_excedente[0]['total_excedente'], 2, ',', '.');?>" title="Qtde Estoque Real" size="8" maxlength="6" class='textdisabled' disabled>
            &nbsp;
            <img src = '../../../../imagem/maozinha.png' onclick="retirar_material('<?=$i;?>')" width="22" height="22" alt="Retirar do Estoque" title="Retirar do Estoque" style="cursor:help" border='0'>
        </td>
        <td>
            <input type='text' name='txt_qtde[]' id='txt_qtde<?=$i;?>' value='0' title='Digite a Qtde' size="8" maxlength="7" onkeyup="verifica(this, 'moeda_especial', '0', '1', event);calcular('<?=$i;?>')" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_saldo[]' id='txt_saldo<?=$i;?>' value='<?=$campos[$i]['qtde'];?>' title='Saldo' size='6' maxlength='4' class='textdisabled' disabled>
        </td>
        <td>
            <?
                if($campos[$i]['embalado'] == 'S') {
                    $selectedS = 'selected';
                }else if($campos[$i]['embalado'] == 'N') {
                    $selectedN = 'selected';
                }
            ?>
            <select name='cmb_embalado[]' id='cmb_embalado<?=$i;?>' title='Embalado' class='textdisabled' disabled>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='S' <?=$selectedS;?>>SIM</option>
                <option value='N' <?=$selectedN;?>>NÃO</option>
            </select>
        </td>
        <td>
            <input type='text' name='txt_prateleira[]' value='<?=$campos[$i]['prateleira'];?>' title='Prateleira' size='6' maxlength='3' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_bandeja[]' value='<?=$campos[$i]['bandeja'];?>' title='Bandeja' size='3' maxlength='1' class='textdisabled' disabled>
        </td>
        <td>
            <select name='cmb_produto_acabado_faltante[]' id='cmb_produto_acabado_faltante<?=$i;?>' title='Selecione o Produto Acabado Faltante' class='textdisabled' disabled>
            <?
                //Eu listo todos os PA(s) Padrões que já foram substituídos com o PA passado por parâmetro ...
                $sql = "SELECT IF(ps.`id_produto_acabado_1` = '$_GET[id_produto_acabado]', ps.`id_produto_acabado_2`, ps.`id_produto_acabado_1`) AS id_pa 
                        FROM `pas_substituires` ps 
                        WHERE (ps.`id_produto_acabado_1` = '$_GET[id_produto_acabado]') OR (ps.`id_produto_acabado_2` = '$_GET[id_produto_acabado]') ";
                $campos_pas = bancos::sql($sql);
                $linhas_pas = count($campos_pas);
                if($linhas_pas > 0) {//Se encontrar pelo menos 1 PA, então ...
                    for($j = 0; $j < $linhas_pas; $j++) $id_pas_exibir.= $campos_pas[$j]['id_pa'].', ';
                    $id_pas_exibir = substr($id_pas_exibir, 0, strlen($id_pas_exibir) - 2);
                }else {
                    $id_pas_exibir = 0;//Para não dar erro de SQL ...
                }
                //Trago todos os PA(s) que estão atrelados na tab. relacional ...
                $sql = "SELECT `id_produto_acabado`, CONCAT(`referencia`, ' * ', `discriminacao`) AS dados 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` IN ($id_pas_exibir) ";
                echo combos::combo($sql, $campos[$i]['id_produto_acabado_faltante']);
            ?>
            </select>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
        <td>
            <textarea name='txt_observacao[]' id='txt_observacao<?=$i;?>' rows='2' cols='25' class='textdisabled' disabled></textarea>
        </td>
    </tr>
<?
            $total_estoque_excedente+= $campos[$i]['qtde'];
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3' align='right'>
            <font color='yellow'>
                Saldo Total: 
            </font>
        </td>
        <td>
            <input type='text' name='txt_saldo_total' id='txt_saldo_total' value="<?=$total_estoque_excedente;?>" size="6" maxlength="4" class='textdisabled' disabled>
        </td>
        <td colspan='4' align='left'>
            <font color='yellow'>
                Estoque Real: 
            </font>	
            <?=number_format($estoque_real, 2, ',', '.').' '.$campos_pa[0]['sigla'];?>
            &nbsp;-&nbsp;
            <font color='yellow'>
                Estoque na Prateleira: 
            </font>
            <?=number_format($estoque_real - $total_estoque_excedente, 2, ',', '.').' '.$campos_pa[0]['sigla'];?>
        </td>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');habilitar()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_produto_acabado' value="<?=$_GET['id_produto_acabado'];?>">
</form>
</body>
</html>
<?
    }
}else if($passo == 2) {
/************************************************************************************/
//Verifico se a Sessão não caiu ...
    if (!(session_is_registered('id_funcionario'))) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../../html/index.php?valor=1'
    </Script>
<?
        exit;
    }
/************************************************************************************/
    foreach($_POST['chkt_estoque_excedente'] as $i => $id_estoque_excedente) {
        //Gerando a Observação Automática ...
        $acao 		= ($_POST['txt_qtde'][$i] >= 0) ? '<font color="darkblue">(Entrada de ' : '<font color="red">(Saída de ';
        if(!empty($_POST['txt_observacao'][$i])) $observacao_extra = ' - <b>Observação: </b> '.$_POST['txt_observacao'][$i];
        $observacao = '<br><b>Ação '.$acao.abs($_POST['txt_qtde'][$i]).' pçs)</font></b> - <b>Login:</b> '.$_SESSION['login'].' - <b>Data:</b> '.date('d/m/Y H:i:s').$observacao_extra;
        //Se foi zerado todo o Estoque Excedente, então eu zero o status do Registro como Concluído ...
        $campos_status = ($_POST['txt_saldo'][$i] == 0) ? " , status = '1' " : '';

/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        $id_produto_acabado_faltante = (!empty($_POST['cmb_produto_acabado_faltante'][$i])) ? "'".$_POST['cmb_produto_acabado_faltante'][$i]."'" : 'NULL';

//Alterando os Dados no BD ...
        $sql = "UPDATE `estoques_excedentes` SET `id_produto_acabado_faltante` = $id_produto_acabado_faltante, `qtde` = `qtde` + '".$_POST['txt_qtde'][$i]."', `embalado` = '".$_POST['cmb_embalado'][$i]."', `observacao` = CONCAT(`observacao`, '$observacao') $campos_status WHERE `id_estoque_excedente` = '$id_estoque_excedente' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=3'
    </Script>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal    = '../../../..';
    $url_remetente              = 'EXCEDENTE';

//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('../../../classes/produtos_acabados/tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Alterar Estoque Excedente de PA ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Alterar Estoque Excedente de PA
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2' rowspan='2'>
            <font title="Referência / Discriminação" style='cursor:help'>
                Ref / Disc
            </font>
        </td>
        <td rowspan="2">
            <font title="Operação de Custo" style='cursor:help'>
                O.C.
            </font>
        </td>
        <td rowspan='2'>
            <font title="Unidade" style='cursor:help'>
                Un.
            </font>
        </td>
        <td rowspan='2'>
            Compra<br> Produção
        </td>
        <td colspan='4'>
            Quantidade / Estoque
        </td>
        <td rowspan='2'>
            <font title='Média Mensal de Vendas' style='cursor:help'>
                M.M.V.
            </font>
        </td>
        <td rowspan='2'>
            Prazo de Entrega
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Real
        </td>
        <td>
            Disp.
        </td>
        <td>
            <font title='Pendência' style='cursor:help'>
                Pend.
            </font>
        </td>
        <td>
            <font title='Comprometido' style='cursor:help'>
                Comp.
            </font>
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
            $id_estoque_acabado    	= $campos[$i]['id_estoque_acabado'];
            $id_produto_acabado    	= $campos[$i]['id_produto_acabado'];
            $referencia 	       	= $campos[$i]['referencia'];
            $unidade                    = $campos[$i]['sigla'];
            $operacao_custo	       	= $campos[$i]['operacao_custo'];
            $retorno                    = estoque_acabado::qtde_estoque($id_produto_acabado, 1);
            $quantidade_estoque   	= $retorno[0];
            $qtde_pendente              = $retorno[7];
            $est_comprometido		= $retorno[8];
            $producao 			= $retorno[2];
            $quantidade_disponivel      = $retorno[3];
//Aki verifica se o PA, possui prazo de Entrega
            $sql = "SELECT prazo_entrega 
                    FROM `estoques_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_prazo_entrega = bancos::sql($sql);
            if(count($campos_prazo_entrega) == 1) {
                $prazo_entrega  = strtok($campos_prazo_entrega[0]['prazo_entrega'], '=');
                $responsavel    = strtok($campos_prazo_entrega[0]['prazo_entrega'], '|');
                $responsavel    = substr(strchr($responsavel, '> '), 1, strlen($responsavel));
                $data_hora      = strchr($campos_prazo_entrega[0]['prazo_entrega'], '|');
                $data_hora      = substr($data_hora, 2, strlen($data_hora));
                $data           = data::datetodata(substr($data_hora, 0, 10), '/');
                $hora           = substr($data_hora, 11, 8);
            }
//Faz esse tratamento para o caso de não encontrar o responsável
            if(empty($responsavel)) {
                $string_apresentar = '&nbsp;';
            }else {
                $string_apresentar = 'Responsável: '.$responsavel.' - '.$data.' '.$hora;
            }
            $url = "alterar.php?passo=1&id_produto_acabado=".$id_produto_acabado;
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width='10'>
            <a href="<?=$url;?>" class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
            <td onclick="window.location = '<?=$url;?>'" align='left'>
                <a href="<?=$url;?>" class='link'>
                <?
                    echo $referencia.' / '.intermodular::pa_discriminacao($id_produto_acabado);
                    if(!empty($campos[$i]['observacao_pa'])) echo "&nbsp;-&nbsp;<img width='28' height='23' title='".$campos[$i]['observacao_pa']."' src='../../../imagem/olho.jpg'>";
                ?>
                </a>
            </td>
        <td>
        <?
            if($operacao_custo == 0) {
                echo 'I';
            }else {
                echo 'R';
            }
        ?>
        </td>
        <td>
            <?=$unidade;?>
        </td>
        <td align='right'>
        <?
                //Aqui verifica se o PA tem relação com o PI ...
                $sql = "SELECT id_produto_insumo 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                        AND `id_produto_insumo` > '0' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos_pipa = bancos::sql($sql);
    //Aqui o PI em relação com o PA e a OC. é do Tipo Revenda então mostra o link
                if(count($campos_pipa) == 1 && $operacao_custo == 1) {
        ?>
        <a href = '../../../classes/estoque/compra_producao.php?id_produto_acabado=<?=$id_produto_acabado;?>' class='html5lightbox'>
                <?
                        $compra = estoque_acabado::compra_producao($id_produto_acabado);
                        //echo segurancas::number_format($compra, 2, '.').' / '.segurancas::number_format($producao, 2, '.');
                        if($compra<>0 && $producao<>0) {
                                echo segurancas::number_format($compra, 2, '.').' / '.segurancas::number_format($producao, 2, '.');
                        } else {
                                echo segurancas::number_format($compra, 2, '.').segurancas::number_format($producao, 2, '.');
                        }
        ?>
        </a>
        <?
    //Aqui o PI em relação com o PA e a OC. é do Tipo Industrial
                } else if(count($campos_pipa) == 1 && $operacao_custo == 0) {//Não mostra o link
                        $compra = estoque_acabado::compra_producao($id_produto_acabado);
                        //echo segurancas::number_format($compra, 2, '.').' / '.segurancas::number_format($producao, 2, '.');
                        if($compra<>0 && $producao<>0) {
                                echo segurancas::number_format($compra, 2, '.').' / '.segurancas::number_format($producao, 2, '.');
                        } else {
                                echo segurancas::number_format($compra, 2, '.').segurancas::number_format($producao, 2, '.');
                        }
    //Aqui o PA não tem relação com o PI
                }else {
                        echo segurancas::number_format($producao, 2, '.');
                }
        ?>
        </td>
        <td align='right'>
        <?
                //Verifico se o Item possui Qtde Excedente ...
                $sql = "SELECT qtde 
                        FROM `estoques_excedentes` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                        AND `status` = '0' LIMIT 1 ";
                $campos_excedente = bancos::sql($sql);

                if($campos_excedente[0]['qtde'] > 0) {//Se existir Estoque Excedente, exibo um link p/ ver Detalhes
        ?>
                <a href = 'alterar.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&pop_up=1' class='html5lightbox'>
        <?
                }
                echo number_format($quantidade_estoque, 2, ',', '.');
        ?>
                </a>
        </td>
        <td align='right'>
        <?
            if($quantidade_disponivel < 0) {
                echo "<font color='red'>".segurancas::number_format($quantidade_disponivel, 2, '.')."</font>";
            }else if($quantidade_disponivel > 0) {
                echo segurancas::number_format($quantidade_disponivel, 2, '.');
            }
        ?>
        </td>
        <td align='right'>
        <?
/*Jogo o SQL mais acima para verificar por causa de um desvio que não mostrar os valores comprometidos <=0*/
            if($qtde_pendente > 0) echo segurancas::number_format($qtde_pendente, 2, '.');
        ?>
        </td>
        <td align='right'>
        <?
            if($est_comprometido < 0) {
                echo "<font color='red'>".segurancas::number_format($est_comprometido,2,".")."</font>";
            }else if ($est_comprometido > 0) {
                echo segurancas::number_format($est_comprometido,2,".");
            }
        ?>
        </td>
        <td align='right'>
        <?
            //Aki eu busco a média mensal de vendas do PA
            $sql = "SELECT mmv 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_mmv = bancos::sql($sql);
            if($campos_mmv[0]['mmv'] > 0) echo number_format($campos2[0]['mmv'], 2, ',', '.');
        ?>
        </td>
        <td align='right' title="<?=$string_apresentar;?>" alt="<?=$string_apresentar;?>">
            <?=$prazo_entrega;?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'alterar.php'" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}
?>