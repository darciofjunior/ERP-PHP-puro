<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/compras_new.php');
require('../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='erro'>PREENCHIMENTO INCORRETO P/ OS PRAZOS DO VENCIMENTO.</font>";
$mensagem[2] = "<font class='confirmacao'>VENCIMENTO ALTERADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='confirmacao'>PARCELA DE VENCIMENTO EXCLUÍDA COM SUCESSO.</font>";
$mensagem[4] = "<font class='confirmacao'>PARCELA DE VENCIMENTO INCLUÍDA COM SUCESSO.</font>";

if(!empty($_POST['hdd_incluir_financiamento']) || !empty($_POST['hdd_nfe_financiamento'])) {
//Incluindo uma Parcela de Vencimento a + da NF ...
    if(!empty($_POST['hdd_incluir_financiamento'])) {
//1) Busca a Data de Emissão da NFE ...
        $sql = "SELECT DATE_FORMAT(SUBSTRING(data_emissao, 1, 10), '%d/%m/%Y') AS data_emissao 
                FROM `nfe` 
                WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
        $campos         = bancos::sql($sql);
        $data_emissao   = $campos[0]['data_emissao'];
//Aqui eu busco o Prazo de Vencimento p/ poder gerar a Próxima Parcela subseqüente ...
        $sql = "SELECT dias 
                FROM `nfe_financiamentos` 
                WHERE `id_nfe` = '$_POST[id_nfe]' ORDER BY dias DESC LIMIT 1 ";
        $campos = bancos::sql($sql);
        $dias   = $campos[0]['dias'] + 1;//Somo + 1, p/ poder jogar na próxima Parcela ...
        $data   = data::datatodate(data::adicionar_data_hora($data_emissao, $dias), '-');
//Gravando os Pedidos de Vencimentos ...
        $sql = "INSERT INTO `nfe_financiamentos` (`id_nfe_financiamento`, `id_nfe`, `dias`, `data`, `valor_parcela_nf`) VALUES (NULL, '$_POST[id_nfe]', '$dias', '$data', '0') ";
        bancos::sql($sql);
//Chamo a função p/ fazer a divisão das parcelas pelo jeito de Vencimento ...
        compras_new::calculo_valor_financiamento($_POST[id_nfe]);
        $valor = 4;
//Alterando os Dados já existentes das Parcelas de Vencimento ...
    }else {
//Aqui eu verifico se foi preenchida a Qtde de dias referente as Parcelas ...
        for($i = 0; $i < count($_POST['hdd_nfe_financiamento']); $i++) {
/*Se o a parcela anterior a próxima, tiver seu valor maior, então o Sistema tem que dar erro de 
inconsistência de Dados*/
//Enquanto não chegar na última parcela, eu vou fazendo essa comparação ...
            if(($i + 1) < count($_POST['hdd_nfe_financiamento'])) {
                if($_POST['txt_dias'][$i] > $_POST['txt_dias'][$i + 1]) $valor = 1;
            }
        }

        if($valor != 1) {//Significa que a parte de Dias dos Prazos está corretamente preenchida
//Disparando o Loop ...
            for($i = 0; $i < count($_POST['hdd_nfe_financiamento']); $i++) {
                $dias = $_POST['txt_dias'][$i];
                $data = data::datatodate($_POST['txt_data'][$i], '-');
                $valor_parcela_nf = $_POST['txt_valor'][$i];

//Alterando os dados da Tabela de Pedidos de Financiamentos ...
                $sql = "UPDATE `nfe_financiamentos` SET `dias` = '$dias', `data` = '$data', `valor_parcela_nf` = '$valor_parcela_nf' WHERE `id_nfe_financiamento` = '".$_POST['hdd_nfe_financiamento'][$i]."' LIMIT 1 ";
                bancos::sql($sql);
            }
//Por garantia atualizo a Data de Emissão na tabela de NF(s) que foi passado por parâmetro ...
            $txt_data_emissao = data::datatodate($_POST['txt_data_emissao'], '-').' '.date('H:i:s');
            $sql = "UPDATE `nfe` SET `data_emissao` = '$txt_data_emissao' WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
            bancos::sql($sql);
            $valor = 7;
        }
    }
?>
    <Script Language = 'Javascript'>
        var valor = '<?=$valor;?>'
        if(valor == 7) {//Significa que foi possível fazer o Vencimento ...
            window.location = 'alterar_cabecalho.php?id_nfe=<?=$_POST['id_nfe'];?>&valor=<?=$valor;?>'
            window.opener.parent.itens.document.form.submit()
            window.opener.parent.rodape.document.form.submit()
        }else {
            window.location = 'alterar_finame.php?id_nfe=<?=$_POST['id_nfe'];?>&txt_data_emissao=<?=$_POST['txt_data_emissao'];?>&valor=<?=$valor;?>'
        }
    </Script>
<?
}

//Procedimento quando acaba de ser carregada a tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_nfe = $_POST['id_nfe'];
}else {
    $id_nfe = $_GET['id_nfe'];
}

//Excluindo os Vencimentos da NF ...    
if(!empty($_POST['id_nfe_financiamento'])) {
//Aqui eu apago a Parcela do Financiamento ...
    $sql = "DELETE FROM `nfe_financiamentos` WHERE `id_nfe_financiamento` = '$_POST[id_nfe_financiamento]' LIMIT 1 ";
    bancos::sql($sql);
//Chamo a função p/ fazer a divisão das parcelas pelo jeito de Vencimento ...
    compras_new::calculo_valor_financiamento($id_nfe);
    $valor = 3;
}
/******************************************/
//Busca dos Dados de Cabeçalho desta NFE ...
$sql = "SELECT f.razaosocial, f.id_pais, nfe.*, tm.simbolo, CONCAT(tm.simbolo, ' - ', tm.moeda) AS moeda 
        FROM `nfe` 
        INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
        INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = nfe.id_tipo_moeda 
        WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
$campos     = bancos::sql($sql);
$id_pais    = $campos[0]['id_pais'];
$razaosocial = $campos[0]['razaosocial'];
$simbolo    = $campos[0]['simbolo'];
$moeda      = $campos[0]['moeda'];

$calculo_total_impostos = calculos::calculo_impostos(0, $id_nfe, 'NFC');
$valor_total_nota       = $calculo_total_impostos['valor_total_nota'];

//Dados Referentes as Antecipações da Nota Fiscal ...
$retorno_antecipacoes       = compras_new::calculo_valor_antecipacao($id_nfe);
$valor_total_antecipacoes   = $retorno_antecipacoes['valor_total_antecipacoes'];
/***********************Busca dos Vencimentos Gerados p/ esta NF***********************/
$sql = "SELECT * 
        FROM `nfe_financiamentos` 
        WHERE `id_nfe` = '$id_nfe' ORDER BY dias ";
$campos_financiamento = bancos::sql($sql);
$linhas_financiamento = count($campos_financiamento);
?>
<html>
<title>.:: Alterar Vencimento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Continuação ...
    var elementos = document.form.elements
//Verifico se as Demais caixas do Iframe estão preenchidas ...
    for(var i = 0; i < elementos.length; i++) {
//Dias ...
        if(elementos[i].name == 'txt_dias[]') {
            if(elementos[i].value == '') {
                alert('PREENCHA O N.º DE DIAS DO VENCIMENTO !')
                elementos[i].focus()
                return false
            }
        }
//Valores ...
        if(elementos[i].name == 'txt_valor[]') {
            if(elementos[i].value == '') {
                alert('PREENCHA O VALOR DO VENCIMENTO !')
                elementos[i].focus()
                return false
            }
        }
    }
    var valor_anterior = 0//Utilizado mais abaixo ...
//Aqui eu verifico se foi preenchida a Qtde de dias referente as Parcelas ...
    for(var i = 0; i < elementos.length; i++) {
/*Se o a parcela anterior a próxima, tiver seu valor maior, então o Sistema tem que dar erro de 
inconsistência de Dados*/
        if(elementos[i].name == 'txt_dias[]') {
//Enquanto não chegar na última parcela, eu vou fazendo essa comparação ...
            if(valor_anterior != 0) {//Se está variável estiver preenchida ...
                if(eval(valor_anterior) > eval(elementos[i].value)) {
                    alert('PREENCHIMENTO INCORRETO P/ OS PRAZOS DO VENCIMENTO !')
                    elementos[i].focus()
                    elementos[i].select()
                    return false
                }
            }
            valor_anterior = elementos[i].value
        }
    }
/*Se o Somatório das Parcelas do Vencimento não condizer com o Valor da NFE, então o Sistema 
retorna uma mensagem de erro*/
    var valor_total_vencimento = 0
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_valor[]') {
            if(elementos[i].value != '') {
                valor_total_vencimento+= eval(strtofloat(elementos[i].value))
                //Faço essa adaptação p/ poder arredondar de forma exata esse valor ...
                valor_total_vencimento = String(valor_total_vencimento)
                valor_total_vencimento = arred(valor_total_vencimento, 2, 1)
                //Transformo essa variável em número novamente p/ poder fazer o somatório nos próximos Loops ...
                valor_total_vencimento = eval(strtofloat(valor_total_vencimento))
            }
        }
    }
//Se o Valor da Nota Fiscal, já descontando o Valor das Antecipações for diferente do Valor Total de Venc ...
    if(valor_total_vencimento != '<?=round(round($valor_total_nota - $valor_total_antecipacoes, 3), 2);?>') {
        alert('VALOR TOTAL DE VENCIMENTO(S) INVÁLIDO !')
//Significa que está tela foi carregada com apenas 1 linha ...
        if(typeof(elementos['txt_valor[]'][0]) == 'undefined') {
            document.form.elements['txt_valor[]'].focus()
            document.form.elements['txt_valor[]'].select()
//Mais de 1 linha ...
        }else {
            document.form.elements['txt_valor[]'][0].focus()
            document.form.elements['txt_valor[]'][0].select()
        }
        return false
    }
//Tratando os Elementos antes p/ enviar p/ o BD ...
    for(var i = 0; i < elementos.length; i++) {
//Se o Tipo de Objeto for caixa de Texto ...
        if(elementos[i].type == 'text') {
            elementos[i].value = strtofloat(elementos[i].value)
            elementos[i].disabled = false
        }
    }
//P/ não atualizar o frames abaixo desse Pop-UP ...
    document.form.nao_atualizar.value = 1
    atualizar_abaixo()
}

function calcular_novo_prazo(data, dias) {
    if(document.getElementById(dias).value != '') {//Se a Qtde de Dias estiver preenchida ...
        nova_data('<?=$data_emissao;?>', "document.getElementById('"+data+"')", "document.getElementById('"+dias+"')")
    }else {//Limpa a caixa ...
        document.getElementById(data).value = ''
    }
}

function copiar_valores() {
    var elementos = document.form.elements
    var cont = 0, primeiro_valor = ''
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_valor[]') {
            if(cont == 0) {//Significa que eu estou passando pelo 1º Valor ...
                primeiro_valor = elementos[i].value
                cont = 1
            }else {
                elementos[i].value = primeiro_valor
            }
        }
    }
}

function valor_total_vencimento() {
    var elementos               = document.form.elements
    var valor_total_vencimento  = 0
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].name == 'txt_valor[]') {
            if(elementos[i].value != '') {
                valor_total_vencimento+= eval(strtofloat(elementos[i].value))
                //Faço essa adaptação p/ poder arredondar de forma exata esse valor ...
                valor_total_vencimento = String(valor_total_vencimento)
                valor_total_vencimento = arred(valor_total_vencimento, 2, 1)
                //Transformo essa variável em número novamente p/ poder fazer o somatório nos próximos Loops ...
                valor_total_vencimento = eval(strtofloat(valor_total_vencimento))
            }
        }
    }
    document.form.txt_valor_total_vencimento.value = valor_total_vencimento
    document.form.txt_valor_total_vencimento.value = arred(document.form.txt_valor_total_vencimento.value, 2, 1)
}

//Inclusão de Parcelas no Vencimento
function incluir_parcela() {
    var mensagem = confirm('DESEJA REALMENTE INCLUIR UMA PARCELA ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.hdd_incluir_financiamento.value = 1
        document.form.submit()
    }
}

//Exclusão de Parcelas do Vencimento ...
function excluir_parcela(id_nfe_financiamento) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTA PARCELA ?')
    if(mensagem == false) {
        return false
    }else {
        document.form.id_nfe_financiamento.value = id_nfe_financiamento
        document.form.submit()
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        window.opener.parent.itens.document.form.submit()
        window.opener.parent.rodape.document.form.submit()
    }
}

function calcular_todos_prazos() {
    var linhas_financiamentos = eval('<?=$linhas_financiamento;?>')
    for(i = 1; i <= linhas_financiamentos; i++) {
/*Chama a função em JavaScript p/ atualizar os Vencimentos assim que entrar na Tela 
logo de cara*/
        calcular_novo_prazo('txt_data'+i, 'txt_dias'+i)
    }
}

function calcular_novo_prazo(data, dias) {
    if(document.getElementById(dias).value != '') {//Se a Qtde de Dias estiver preenchida ...
        nova_data('<?=$txt_data_emissao;?>', "document.getElementById('"+data+"')", "document.getElementById('"+dias+"')")
    }else {//Limpa a caixa ...
        document.getElementById(data).value = ''
    }
}

function tipo_negociacao() {
    document.form.txt_qtde_dias.value = ''
    if(document.form.opt_tipo_negociacao[0].checked == true) {//Dia Fixo ...
        document.form.txt_qtde_dias.disabled    = true
        document.form.txt_qtde_dias.className   = 'textdisabled'
    }else {//Intervalo Fixo ...
        document.form.txt_qtde_dias.disabled    = false
        document.form.txt_qtde_dias.className   = 'caixadetexto'
        document.form.txt_qtde_dias.focus()
    }
}

function atualizar_vencimentos() {
//Tipo de Negociação ...
    if(document.form.opt_tipo_negociacao[0].checked == false && document.form.opt_tipo_negociacao[1].checked == false) {
        alert('SELECIONE UM TIPO DE NEGOCIAÇÃO !')
        document.form.opt_tipo_negociacao[0].focus()
        return false
    }
//Se a opção selecionada foi Intervalo Fixo ...
    if(document.form.opt_tipo_negociacao[1].checked == true) {
//Qtde de Dias ...
        if(!texto('form', 'txt_qtde_dias', '1', '1234567890', 'QTDE DE DIAS', '1')) {
            return false
        }
    }
//Cálculos para atualizar Vencimentos ...
    var qtde_parcelas = eval('<?=$linhas_financiamento;?>')
    for(var i = 1; i <= qtde_parcelas; i++) {
        if(i == 1) {//Primeira parcela só herda o valor digitado pelo Usuário ...
            dias_loop   = eval(document.getElementById('txt_dias'+i).value)
            data_loop   = document.getElementById('txt_data'+i).value
        }else {//A partir da 2ª parcela tem controles ...
            if(document.form.opt_tipo_negociacao[0].checked == true) {//Dia Fixo ...
                data_antes_da_nova_data = data_loop//Data atual até o momento, antes de gerar a Nova Data ...
                //Gerando a Nova Data ...
                /************************/
                var dia_data = data_loop.substr(0, 2)
                var mes_data = data_loop.substr(3, 2)
                var ano_data = data_loop.substr(6, 4)
                mes_data++
                if(mes_data == 13) {
                    mes_data = 1
                    ano_data++
                }
                if(mes_data < 10) mes_data = '0'+mes_data
                data_loop       = dia_data+'/'+mes_data+'/'+ano_data
                diferenca_dias  = diferenca_datas(data_antes_da_nova_data, data_loop)
                dias_loop+= diferenca_dias
                
                document.getElementById('txt_dias'+i).value = dias_loop
                document.getElementById('txt_data'+i).value = data_loop
            }else {//Intervalo Fixo ...
                dias_loop+= eval(document.getElementById('txt_qtde_dias').value)
                document.getElementById('txt_dias'+i).value = dias_loop
                calcular_novo_prazo('txt_data'+i, 'txt_dias'+i)
            }
        }
    }
}
</Script>
<body onload='calcular_todos_prazos();document.form.elements[1].focus()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_nfe' value="<?=$id_nfe;?>">
<input type='hidden' name='hdd_incluir_financiamento'>
<input type='hidden' name='id_nfe_financiamento'>
<input type='hidden' name='txt_data_emissao' value="<?=$txt_data_emissao;?>">
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Alterar Vencimento
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Fornecedor:</b>
        </td>
        <td colspan='3'>
            <?=$razaosocial;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo da Moeda:</b>
        </td>
        <td colspan='3'>
            <?=$moeda;?>
        </td>
    </tr>
<!--
/***************************************************************************************/
Esse parâmetro txt_data_emissao, veio de outra Tela porque é fundamental p/ o cálculo 
dos Prazos de Financiamento aqui nessa parte ...-->
    <tr class='linhanormal'>
        <td>
            <font color='darkgreen'>
                <b>Data Emissão:</b>
            </font>
        </td>
        <td colspan='3'>
            <?=$txt_data_emissao;?>
        </td>
    </tr>
<!--*************************************************************************************-->
    <tr class='linhanormal'>
        <td>
            <b>Valor da Nota Fiscal:</b>
        </td>
        <td colspan='3'>
            <?=$simbolo.' '.number_format($valor_total_nota, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Negociação:</b>
        </td>
        <td colspan='3'>
            <input type='radio' name='opt_tipo_negociacao' id='opt_tipo_negociacao1' value='1' title='Dia Fixo' onclick='tipo_negociacao()'>
            <label for='opt_tipo_negociacao1'>
                Dia Fixo
            </label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_tipo_negociacao' id='opt_tipo_negociacao2' value='2' title='Intervalo Fixo' onclick='tipo_negociacao()'>
            <label for='opt_tipo_negociacao2'>
                Intervalo Fixo
            </label>
            &nbsp;-&nbsp;
            Qtde de Dias: <input type='text' name='txt_qtde_dias' id='txt_qtde_dias' title='Digite a Qtde de Dias' onkeyup="verifica(this, 'moeda_especial', '0', '', event)" size='5' maxlength='3' class='caixadetexto'>
        </td>
    </tr>
<?
/*********************Listagem dos Vencimentos Gerados p/ esta NF*********************/
//Gera a Qtde de Parcelas de acordo com a Qtde que foi passada por parâmetro pelo usuário ...
    $cont_tab = 0;
    for($i = 0; $i < $linhas_financiamento; $i++) {
?>
    <tr class='linhanormal'>
        <td width='150'>
            <b>Parcela N.º <?=$i + 1;?>:</b>
        </td>
        <td>
            Dias: <input type='text' name='txt_dias[]' value="<?=$campos_financiamento[$i]['dias'];?>" id="txt_dias<?=$i + 1;?>" title="Digite o N.º de Dias" size='6' maxlength='5' onkeyup="verifica(this, 'aceita', 'numeros', '', event);calcular_novo_prazo('txt_data<?=$i + 1;?>', 'txt_dias<?=$i + 1;?>')" tabIndex="<?='10'.$cont_tab;?>" class='caixadetexto'>
<?
        if($i == 0) {//Só irá exibir essa seta p/ o Primeiro Registro ...
?>            
            <input type='button' name='cmd_atualizar_vencimentos' value='Atualizar Ventos' title='Atualizar Vencimentos' onclick='atualizar_vencimentos()' class='botao'>
<?
        }else {
            echo '&nbsp;&nbsp;&nbsp;&nbsp;';
        }
?>
        </td>
        <td>
            Data: <input type='text' name='txt_data[]' value="<?=data::datetodata($campos_financiamento[$i]['data'], '/');?>" id="txt_data<?=$i + 1;?>" title="Data" size="12" class='textdisabled' disabled>&nbsp;&nbsp;
        </td>
        <td>
            Valor <?=$simbolo?>: <input type='text' name='txt_valor[]' value="<?=number_format($campos_financiamento[$i]['valor_parcela_nf'], 2, ',', '.');?>" id='txt_valor<?=$i + 1;?>' title='Digite o Valor' size='13' maxlength='11' onkeyup="verifica(this, 'moeda_especial', '2', '1', event);valor_total_vencimento()" class='caixadetexto'>
<?
        if($i == 0) {//Só irá exibir essa seta p/ o Primeiro Registro ...
?>
            <img src = '../../../../imagem/seta_abaixo.gif' width='12' height='12' onclick='copiar_valores();valor_total_vencimento()'>
<?
        }else {
            echo '&nbsp;&nbsp;&nbsp;&nbsp;';
        }
?>
            <input type='hidden' name='hdd_nfe_financiamento[]' value='<?=$campos_financiamento[$i]['id_nfe_financiamento'];?>'>
<?
//Quando for a última Parcela eu coloco essas marcação p/ excluir ou incluir + parcelas nesse Vencimento ...
        if(($i + 1) == $linhas_financiamento) {
?>
        &nbsp; <img src = '../../../../imagem/menu/adicao.jpeg' border='0' title='Incluir Parcela' alt='Incluir Parcela' width='16' height='16' onclick='incluir_parcela()'>
        &nbsp; <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir Parcela' alt='Excluir Parcela' onclick="excluir_parcela('<?=$campos_financiamento[$i]['id_nfe_financiamento']?>')">
<?
        }
?>
        </td>
    </tr>
<?
        $valor_total_vencimento+= $campos_financiamento[$i]['valor_parcela_nf'];
        $cont_tab++;
    }
/********************************************************************************************************/
?>
    <tr class='linhadestaque'>
        <td colspan='3'>
            &nbsp;
        </td>
        <td>
            Total <?=$simbolo?>: 
            <input type='text' name="txt_valor_total_vencimento" value="<?=number_format($valor_total_vencimento, 2, ',', '.');?>" size="13" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar_cabecalho' value='&lt;&lt; Voltar p/ Cabeçalho &lt;&lt;' title='Voltar p/ Cabeçalho' onclick="window.location = 'alterar_cabecalho.php?id_nfe=<?=$id_nfe;?>'" class='botao'>
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="redefinir('document.form', 'REDEFINIR');document.form.elements[1].focus()" style="color:#ff9900" class='botao'>
            <input type='submit' name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
            <input type='button' name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>