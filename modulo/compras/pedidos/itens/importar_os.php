<?
require('../../../../lib/segurancas.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/custos.php');
require('../../../../lib/data.php');
require('../../../../lib/producao.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>N�O EXISTE(M) O.S.(S) PENDENTE(S) PARA ESSE FORNECEDOR.</font>";

//Parte de Inser��o do Cabe�alho
if($passo == 1) {
//Aqui traz os dados da OS
    $sql = "SELECT f.`razaosocial`, f.`nf_minimo_tt` AS nf_minimo_tt_cad, oss.* 
            FROM `oss` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` 
            WHERE oss.`id_os` = '$_GET[id_os]' 
            AND oss.`ativo` = '1' LIMIT 1 ";
    $campos     = bancos::sql($sql);
    $fornecedor = $campos[0]['razaosocial'];
/********************************Controle para apresenta��o dos Dados********************************/
/*Busco o Status da OS para saber de quais locais de qual local que eu vou buscar 
o lote m�nimo e a nf m�nima*/
    if($campos[0]['id_pedido'] == 0) {//Essa OS ainda n�o foi importada, ent�o busco do Cadastro do Fornecedor
        $nf_minimo_tt = $campos[0]['nf_minimo_tt_cad'];//Uso para comparar no JavaScript ...
    }else {//Essa OS j� foi importada sendo assim, eu busco os valores da pr�pria OS
        $nf_minimo_tt = $campos[0]['nf_minimo_tt'];//Uso para comparar no JavaScript ...
    }
/****************************************************************************************************/
//Busco o Valor Total da OS, eu trago esse valor para um controle que eu fa�o depois em JavaScript ...
    $sql = "SELECT SUM(`peso_total_saida` * `preco_pi`) AS valor_total_os 
            FROM `oss_itens` 
            WHERE `id_os` = '$_GET[id_os]' ";
    $campos_total   = bancos::sql($sql);
    $valor_total_os = round($campos_total[0]['valor_total_os'], 2);
//Verifico se o Pedido � do Tipo NF ou SGD, q vai me servir p/ controlar o cabe�alho de SGD na OS
    $sql = "SELECT `tipo_nota` 
            FROM `pedidos` 
            WHERE `id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
    $campos_tipo_ped 	= bancos::sql($sql);
    $tipo_pedido        = $campos_tipo_ped[0]['tipo_nota'];
?>
<html>
<head>
<title>.:: Importar OS(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var tipo_pedido = eval('<?=$tipo_pedido;?>')
//Nossa Nota Fiscal
    if(document.form.txt_nossa_nota_fiscal.disabled == false) {
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
                document.form.txt_nossa_nota_fiscal.className   = 'textdisabled'
                document.form.txt_nossa_nota_fiscal.value       = ''//Limpa a Caixa
                document.form.txt_nossa_nota_fiscal.disabled    = true//Desabilita a Caixa
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
    document.form.txt_nf_minimo_tt.disabled = false
    return limpeza_moeda('form', 'txt_peso_total_caixas, txt_peso_liquido, txt_nf_minimo_tt, ')
}

function controlar_digitos(objeto) {
    if(objeto.value.length > 1) {//Se tiver pelo menos 2 d�gitos ...
        if(objeto.value.substr(0, 1) == '0') {
            objeto.value = objeto.value.substr(1, 1)
        }
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
            document.form.txt_nossa_nota_fiscal.className   = 'textdisabled'
            document.form.txt_nossa_nota_fiscal.value       = ''//Limpa a Caixa
            document.form.txt_nossa_nota_fiscal.disabled    = true//Desabilita a Caixa
//OS j� est� Importada para Pedido
        }else {
//Significa que o Pedido � do Tipo NF, ent�o nosso posso colocar um cabe�alho de OS como sendo SGD
            if(tipo_pedido == 1) {
                alert('TIPO DE CABE�ALHO INV�LIDO !\nO PEDIDO DE COMPRAS � DO TIPO NF !!!')
                document.form.chkt_sgd.checked = false
                document.form.txt_nossa_nota_fiscal.focus()
            }else {//Significa que o Pedido � do Tipo SGD, ent�o posso colocar normalmente um cab. SGD p/ OS
//Jogo o Layout de Desabilitado ...
                document.form.txt_nossa_nota_fiscal.className   = 'textdisabled'
                document.form.txt_nossa_nota_fiscal.value       = ''//Limpa a Caixa
                document.form.txt_nossa_nota_fiscal.disabled    = true//Desabilita a Caixa
            }
        }
//N�o est� checado, ent�o � uma OS com NF ...
    }else {
//OS ainda n�o est� importada em Pedido
        if(typeof(tipo_pedido == 'undefined')) {
//Jogo o Layout de Habilitado ...
            document.form.txt_nossa_nota_fiscal.className       = 'caixadetexto'
            document.form.txt_nossa_nota_fiscal.value           = '<?=$campos[0]["nnf"];?>'//Caixa c/ Valor = OS
            document.form.txt_nossa_nota_fiscal.disabled        = false//Habilita a Caixa
            document.form.txt_nossa_nota_fiscal.focus()
//OS j� est� Importada para Pedido
        }else {
//Significa que o Pedido � do Tipo NF, ent�o posso colocar normalmente um cab. NF p/ OS
            if(tipo_pedido == 1) {
                //Jogo o Layout de Habilitado ...
                document.form.txt_nossa_nota_fiscal.className   = 'caixadetexto'
                document.form.txt_nossa_nota_fiscal.value       = '<?=$campos[0]["nnf"];?>'//Caixa c/ Valor = OS
                document.form.txt_nossa_nota_fiscal.disabled    = false//Habilita a Caixa
                document.form.txt_nossa_nota_fiscal.focus()
            }else {//Significa que o Pedido � do Tipo SGD, ent�o n�o posso colocar um cab. NF p/ OS
                alert('TIPO DE CABE�ALHO INV�LIDO !\nO PEDIDO DE COMPRAS � DO TIPO SGD !!!')
                document.form.chkt_sgd.checked = true
//Jogo o Layout de Desabilitado ...
                document.form.txt_nossa_nota_fiscal.className   = 'textdisabled'
                document.form.txt_nossa_nota_fiscal.value       = ''//Limpa a Caixa
                document.form.txt_nossa_nota_fiscal.disabled    = true//Desabilita a Caixa
            }
        }
    }
}
</Script>
<body onload='document.form.txt_nossa_nota_fiscal.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>' onsubmit='return validar()'>
<input type='hidden' name='id_pedido' value='<?=$_GET['id_pedido'];?>'>
<input type='hidden' name='id_os' value='<?=$_GET['id_os'];?>'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Importar OS N.�&nbsp;
            <font color='yellow'>
                <?=$id_os;?>
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
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' class='textdisabled' disabled>
            <?
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY `nomefantasia` ";
                echo combos::combo($sql, $campos[0]['id_empresa']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            NF M�nimo (Tratamento T�rmico) R$:
        </td>
        <td>
            <input type='text' name='txt_nf_minimo_tt' value='<?=number_format($nf_minimo_tt, 2, ',', '.');?>' title='NF M�nimo (Tratamento T�rmico) R$' size="12" maxlength='10' class='textdisabled' disabled>
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
            <input type='text' name='txt_data_saida' value='<?=$data_saida;?>' title='Data de Sa�da' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <?
//Se o Tipo de Pedido for SGD, ent�o o Sys trava esse campo Nossa Nota Fiscal N.�
        if($tipo_pedido == 2) {//Pedido do Tipo SGD
            $class      = 'textdisabled';
            $disabled   = 'disabled';
            $checked    = 'checked';
        }else {//Pedido do Tipo NF, ent�o deixo habilitado normalmente este campo
            $class      = 'caixadetexto';
            $disabled   = '';
            $checked    = '';
        }
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Nossa Nota Fiscal N.�:</b>
        </td>
        <td>
            <input type='text' name='txt_nossa_nota_fiscal' value='<?=$campos[0]['nnf'];?>' title='Digite a Nossa Nota Fiscal' size='12' maxlength='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='<?=$class;?>' <?=$disabled;?>>
            &nbsp;<input type='checkbox' name='chkt_sgd' value='1' title='Selecione o SGD' id='label1' onclick='sgd()' class='checkbox' <?=$checked;?>>
            <label for='label1'>
                SGD
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde de Caixas:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_caixas' value='<?=$campos[0]['qtde_caixas'];?>' title='Digite a Qtde de Caixas' size='12' maxlength='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);controlar_digitos(this)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso Total das Caixas:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_total_caixas' value='<?=number_format($campos[0]['peso_caixas'], 3, ',', '.');?>' title='Digite o Peso das Caixas' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '3', '', event);calcular_peso_bruto()" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso L�quido:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_liquido' value='<?=number_format($campos[0]['peso_liq'], 4, ',', '.');?>' title='Digite o Peso L�quido' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '4', '', event);calcular_peso_bruto()" class='caixadetexto'> KG
            &nbsp;-&nbsp;
            <?
//Aqui eu fa�o o Peso L�q. Total de Sa�da de Todos os Itens da OS, que seria uma sugest�o do Sistema ...
                $sql = "SELECT SUM(`peso_total_saida`) AS total_saida 
                        FROM `oss_itens` 
                        WHERE `id_os` = '$_GET[id_os]' ";
                $campos_total_saida = bancos::sql($sql);
                $total_saida        = $campos_total_saida[0]['total_saida'];
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
            <textarea name='txt_observacao' cols='85' rows='3' maxlength='255' class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'importar_os.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR');document.form.txt_nossa_nota_fiscal.focus()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
//Parte de Inser��o dos Itens
}else if($passo == 2) {
/******Aqui � todo o Controle para Atualiza��o dos Pre�os de Itens da OS e Cabe�alho da OS******/
//Nessa fun��o eu busco quais os PI(s) de OS que est�o com os Pre�os em rela��o a Lista de Pre�o
    $retorno                = producao::conferir_precos_os($id_os);
    $id_produtos_insumos    = count($retorno['id_produtos_insumos']);
//N�o retornou nenhum item da confer�ncia de Pre�os, ent�o j� retorno essa logo mensagem ...
    if($id_produtos_insumos == 0) {//Se n�o encontrar nenhum PI, ent�o h� nada a ser feito 
        $mensagem = 'N�O H� ITEM(NS) DE O.S. P/ SER(EM) ATUALIZADO(S) !';
//Se retornar algum item da confer�ncia de Pre�os, ent�o chamo a fun��o para atualiza��o dos Pre�os ...
    }else {//Pego os PI(s) e passo por Par�metro estes que precisam ser atualizados
        $id_produtos_insumos = $retorno['id_produtos_insumos'];
        $retorno = producao::atualizar_precos_os($id_os, $id_produtos_insumos);
        if($retorno == 1) {//Significa que atualizou algum Item
            $mensagem = 'TODO(S) O(S) ITEM(NS) DE O.S. QUE ESTAVA(M) COM O(S) PRE�O(S) INCOMPAT�VEL(IS) COM O DA LISTA DE PRE�O, FORAM ATUALIZADO(S) COM SUCESSO !';
//Informo p/ o usu�rio na mesma hora que foi atualizado os Pre�os de Itens de O(s) com o da Lista de Pre�o ..
?>
            <Script Language = 'JavaScript'>
                alert('<?=$mensagem;?>')
            </Script>
<?
        }else {//N�o atualizou nenhum Item, porque a O.S. j� foi importada
            $mensagem = 'NENHUM ITEM DE O.S. PODE SER ATUALIZADO !\nEST� O.S. J� FOI IMPORTADA P/ PEDIDO !';
        }
    }
/******Atualiza��o de Dados do Cabe�alho da OS******/
//Obs: J� atrelo o id do pedido na OS que est� sendo importado ...
    $sql = "UPDATE `oss` SET `id_pedido` = '$_POST[id_pedido]', `nf_minimo_tt` = '$_POST[txt_nf_minimo_tt]', `nnf` = '$_POST[txt_nossa_nota_fiscal]', `qtde_caixas` = '$_POST[txt_qtde_caixas]', `peso_caixas` = '$_POST[txt_peso_total_caixas]', `peso_liq` = '$_POST[txt_peso_liquido]', `observacao` = '$_POST[txt_observacao]' WHERE `id_os` = '$_POST[id_os]' LIMIT 1 ";
    bancos::sql($sql);
/***********************************************************************************************/
//1)Vou utilizar esse id_fornecedor da OS p/ poder marcar o Fornecedor Default dos PI(s) itens de OS ...
    $sql = "SELECT `id_fornecedor` 
            FROM `oss` 
            WHERE `id_os` = '$_POST[id_os]' LIMIT 1 ";
    $campos_fornecedor  = bancos::sql($sql);
    $id_fornecedor      = $campos_fornecedor[0]['id_fornecedor'];

//2)Busco todos os Itens da OS "Sa�da" para jogar na tabela de itens_pedido ...
    $sql = "SELECT * 
            FROM `oss_itens` 
            WHERE `id_os` = '$_POST[id_os]' 
            AND `qtde_entrada` = '0' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0 ; $i < $linhas; $i++) {
        $id_os_item             = $campos[$i]['id_os_item'];
        $id_produto_insumo_ctt  = $campos[$i]['id_produto_insumo_ctt'];
        $qtde_saida             = $campos[$i]['qtde_saida'];
        $peso_total_saida       = $campos[$i]['peso_total_saida'];
        $preco_pi               = $campos[$i]['preco_pi'];
        $cobrar_lote_minimo     = $campos[$i]['cobrar_lote_minimo'];
        $lote_minimo_custo_tt   = $campos[$i]['lote_minimo_custo_tt'];
        
        //Busca da Unidade que ser� utilizada logo abaixo ...
        $sql = "SELECT u.`sigla` 
                FROM `produtos_insumos` pi 
                INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
                WHERE pi.`id_produto_insumo` = '$id_produto_insumo_ctt' LIMIT 1 ";
        $campos_dados = bancos::sql($sql);
        
        if($campos_dados[0]['sigla'] == 'UN') {//Se a unidade do CTT = "Unidade", ent�o utilizo o campo Qtde ... 
            $peso_qtde_total_utilizar = $qtde_saida;
        }else {//Se a unidade do CTT <> "Unidade", ent�o utilizo o campo Peso Total  ... 
            $peso_qtde_total_utilizar = $peso_total_saida;
        }
//Se no Item da OS possuir essa marca��o, o Novo Pre�o do PI fica sendo ...
        if($cobrar_lote_minimo == 'S') {
            //Aqui n�s recalculamos o Pre�o Unit�rio para que o Total do Item do Pedido n�o d� errado ...
            if($peso_qtde_total_utilizar * $preco_pi < $lote_minimo_custo_tt) $preco_pi = round($lote_minimo_custo_tt / $peso_qtde_total_utilizar, 2);
        }
        $marca = 'OP N.� '.$campos[$i]['id_op'];
//3)Insere os dados de Itens de OS na tabela de Itens de Pedido ...
        $sql = "INSERT INTO `itens_pedidos` (`id_item_pedido`, `id_pedido`, `id_produto_insumo`, `preco_unitario`, `qtde`, `marca`) VALUES (NULL, '$_POST[id_pedido]', '$id_produto_insumo_ctt', '$preco_pi', '$peso_qtde_total_utilizar', '$marca') ";
        bancos::sql($sql);
        $id_item_pedido = bancos::id_registro();
        
//4)Atrelo o id_item_pedido na tabela de 'oss_itens' da OS que est� sendo importada ...
        $sql = "UPDATE `oss_itens` SET `id_item_pedido` = '$id_item_pedido' WHERE `id_os_item` = '$id_os_item' LIMIT 1 ";
        bancos::sql($sql);
//Agora eu marco esse Fornecedor da OSS como sendo o Fornecedor default desse PI -> $id_produto_insumo_ctt
        custos::setar_fornecedor_default($id_produto_insumo_ctt, $id_fornecedor, 'S');
    }
//5)Registro um Follow-UP com uma Observa��o do Pedido eu falo a qual OSS que este Pertence ...
    $observacao = 'OS N.� '.$id_os;

    $sql = "SELECT `id_follow_up` 
            FROM `follow_ups` 
            WHERE `identificacao` = '$_POST[id_pedido]' 
            AND `origem` = '16' 
            AND `observacao` = '$observacao' ";
    $campos_follow_up = bancos::sql($sql);
    if(count($campos_follow_up) == 0) {//Ainda n�o existe Follow-UP registrado nesse sentido ...
        //Registrando Follow-UP ...
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_fornecedor`, `id_funcionario`, `identificacao`, `origem`, `data_entrega_embarque`, `observacao`, `data_sys`) VALUES (NULL, '$id_fornecedor', '$_SESSION[id_funcionario]', '$_POST[id_pedido]', '16', '".date('Y-m-d')."', '$observacao', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        alert('O.S. IMPORTADA COM SUCESSO !')
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
        window.close()
    </Script>
<?
}else {
//Com o id_pedido, eu busco qual � a Empresa, Fornecedor e a raz�osocial ...
    $sql = "SELECT p.`id_empresa`, p.`material_retirado_nosso_estoque`, p.`id_fornecedor`, f.`razaosocial` 
            FROM `pedidos` p 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = p.`id_fornecedor` 
            WHERE p.`id_pedido` = '$_GET[id_pedido]' LIMIT 1 ";
    $campos                             = bancos::sql($sql);
    $id_empresa_ped                     = $campos[0]['id_empresa'];//Renomeio a vari�vel p/ n�o dar conflito com o id_empresa da Sess�o ...
    $material_retirado_nosso_estoque    = $campos[0]['material_retirado_nosso_estoque'];
    $id_fornecedor                      = $campos[0]['id_fornecedor'];
    $razaosocial                        = $campos[0]['razaosocial'];
    if($material_retirado_nosso_estoque == 'S') {
?>
    <Script Language = 'JavaScript'>
        alert('ESSA OS N�O PODE SER IMPORTADA NESTE PEDIDO !!!\n\nDESMARQUE DO CABE�ALHO A MARCA��O "MATERIAL RETIRADO DO NOSSO ESTOQUE" !')
        window.close()
    </Script>
<?
        exit;
    }
/******************************************************************************************************************/
    //Aqui lista todas as OS que est�o em aberto da Empresa do Pedido e do mesmo Fornecedor ...
    $sql = "SELECT oss.`id_os`, DATE_FORMAT(oss.`data_saida`, '%d/%m/%Y') AS data_saida, oss.`observacao`, f.`razaosocial` 
            FROM `oss` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = oss.`id_fornecedor` 
            WHERE oss.`id_empresa` = '$id_empresa_ped' 
            AND oss.`id_fornecedor` = '$id_fornecedor' 
            AND oss.`ativo` = '1' 
            AND oss.`id_pedido` IS NULL 
            ORDER BY oss.`id_os` DESC ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Importar OS(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    if($linhas == 0) {
?>
    <tr align='center'>
        <td>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?
    }else {
?>
    <tr align='center'>
        <td colspan='5'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Importar OS(s) do Fornecedor 
            <font color='yellow'>
                <?=$razaosocial;?>
            </font>
        </td>
    </tr>
    <tr class="linhadestaque" align='center'>
        <td colspan='2'>
            N.� OS
        </td>
        <td>
            Fornecedor
        </td>
        <td>
            Data de Sa�da
        </td>
        <td>
            Observa��o
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            $url = 'importar_os.php?passo=1&id_pedido='.$_GET['id_pedido'].'&id_os='.$campos[$i]['id_os'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$url;?>'" width="10">
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="window.location = '<?=$url;?>'">
            <a href="<?=$url;?>" class='link'>
                <?=$campos[$i]['id_os'];?>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=$campos[$i]['data_saida'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['observacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'outras_opcoes.php?id_pedido=<?=$_GET['id_pedido'];?>'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
    }
?>
</body>
</html>
<?}?>