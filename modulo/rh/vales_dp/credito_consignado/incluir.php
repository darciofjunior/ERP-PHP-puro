<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>VALE CRÉDITO CONSIGNADO INCLUIDO COM SUCESSO.</font>";

if($passo == 1) {
//Traz todos funcionários - menos do cargo AUTONÔMO
    switch($opt_opcao) {
//Listagem de Funcionários que ainda estão trabalhando ...
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
        case 1:
            $sql = "SELECT f.id_funcionario, f.id_funcionario_superior, f.nome, f.codigo_barra, e.nomefantasia, c.cargo, d.departamento 
                    FROM funcionarios f 
                    INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
                    INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
                    INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento 
                    WHERE f.nome LIKE '%$txt_consultar%' 
                    AND f.status < '3' 
                    AND f.id_funcionario NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY f.nome ";
        break;
        default:
            $sql = "SELECT f.id_funcionario, f.id_funcionario_superior, f.nome, f.codigo_barra, e.nomefantasia, c.cargo, d.departamento 
                    FROM funcionarios f 
                    INNER JOIN `empresas` e ON e.id_empresa = f.id_empresa 
                    INNER JOIN `cargos` c ON c.id_cargo = f.id_cargo 
                    INNER JOIN `departamentos` d ON d.id_departamento = f.id_departamento 
                    AND f.status < 3 
                    AND f.id_funcionario NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY f.nome ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'incluir.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Incluir Vale Crédito Consignado ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i ++) {
        if (elementos[i].type == 'checkbox')  {
            if (elementos[i].checked == true) valor = true
        }
    }

    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        return true
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='6'>
            <b><?=$mensagem[$valor];?></b>
        </td>
    </tr>
    <tr class="linhacabecalho" align='center'>
        <td colspan='6'>
            Consultar Funcionário(s)
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td colspan="2">
            Código
        </td>
        <td>
            Nome
        </td>
        <td>
            Depto.
        </td>
        <td>
            Cargo
        </td>
        <td>
            Empresa
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'incluir.php?passo=2&id_funcionario_loop=<?=$campos[$i]['id_funcionario'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href = 'incluir.php?passo=2&id_funcionario_loop=<?=$campos[$i]['id_funcionario'];?>'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align="center">
            <?=$campos[$i]['codigo_barra'];?>
        </td>
        <td>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <?=$campos[$i]['departamento'];?>
        </td>
        <td>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class='botao'>
            <input type="button" name="cmd_fechar" value="Fechar" title="Fechar" style="color:red" onclick="window.close()" class='botao'>
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
    }
}else if($passo == 2) {
    $data_atual = date('Y-m-d');
    $id_funcionario_loop = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_funcionario_loop'] : $_GET['id_funcionario_loop'];

    //Esse campo que trago do cadastro do Funcionário será utilizado nos controles em JavaScript p/ os options mais abaixo ...
    $sql = "SELECT `mensalidade_metalcred` 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
    $campos_mensalidade     = bancos::sql($sql);
    $mensalidade_metalcred  = $campos_mensalidade[0]['mensalidade_metalcred'];
    
/*Só listo nessa Combo as Datas de Holeriths que sejam > que a Data de Atual e que o Dia do Vencimento seja até 
no máximo dia 5 que é a Data Convencional de Pagamento Salarial, dias superiores a Dia 5 geralmente são p/ 
pagamento de Abono, Boticário, etc e não têm nada a ver com o que precisa ser feito aqui ...*/
    $sql = "SELECT DATE_FORMAT(data, '%d/%m/%Y') AS data_formatada 
            FROM `vales_datas` 
            WHERE `data` > '$data_atual' 
            AND DAY(`data`) <= '5' ORDER BY data ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
?>
<html>
<head>
<title>.:: Incluir Vale Crédito Consignado ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Financeira
    if(document.getElementById('opt_financeira1').checked == false && document.getElementById('opt_financeira2').checked == false && document.getElementById('opt_financeira3').checked == false) {
        alert('SELECIONE UMA FINANCEIRA !')
        return false
    }
//Data de Emissão
    if(!data('form', 'txt_data_emissao', '4000', 'EMISSÃO')) {
        return false
    }
//Valor
    if(!texto('form', 'txt_valor', '1', '1234567890,.', 'VALOR', '2')) {
        return false
    }
//Qtde de Parcelas
    if(!texto('form', 'txt_qtde_parcelas', '1', '1234567890', 'QTDE DE PARCELA(S)', '1')) {
        return false
    }
//Data do Holerith
    if(!combo('form', 'cmb_data_holerith', '', 'SELECIONE A DATA DE HOLERITH !')) {
        return false
    }
/*Aqui eu verifico a Data de Emissão que está sendo colocada pelo usuário no caso de ele tentar colocar
uma Data de Emissão que a Data Atual ou colocar uma Data de Emissão muito acima da Data Atual*/
    var data_atual = eval('<?=date(Ymd);?>')
    var data_emissao = document.form.txt_data_emissao.value
    data_emissao = data_emissao.substr(6,4) + data_emissao.substr(3,2) + data_emissao.substr(0,2)
    data_emissao = eval(data_emissao)

    if((data_emissao < data_atual) || (data_emissao > (data_atual + 5))) {
        alert('DATA DE EMISSÃO INVÁLIDA !!!\nDATA DE EMISSÃO MENOR QUE A DATA ATUAL OU MUITO SUPERIOR QUE A DATA ATUAL !')
        document.form.txt_data_emissao.focus()
        document.form.txt_data_emissao.select()
        return false
    }
    calcular_valor_parcela()
    document.form.passo.value = 3
//Desabilito estes campos p/ poder gravar no BD ...
    document.form.txt_data_emissao.disabled = false
/*********************Controle p/ Desabilitar as caixas e gravar no BD*********************/
    var elementos  = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['txt_data_holerith[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_data_holerith[]'].length)
    }
    for(var i = 0; i < linhas; i++) {
//Desabilitando as Caixas p/ gravar no BD ...
        document.getElementById('txt_data_holerith'+i).disabled = false
        document.getElementById('txt_vencimento'+i).disabled    = false
        document.getElementById('txt_valor_parcela'+i).disabled = false
//Deixando as Caixas num formato em que eu consiga gravar no BD ...
        document.getElementById('txt_valor_parcela'+i).value    = strtofloat(document.getElementById('txt_valor_parcela'+i).value)
    }
/******************************************************************************************/
}

function incluir_data_holerith() {
    nova_janela('../../class_data_holerith/incluir.php', 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
}

function alterar_data_holerith() {
    if(document.form.cmb_data_holerith.value == '') {
        alert('SELECIONE A DATA DE HOLERITH !')
        document.form.cmb_data_holerith.focus()
        return false
    }else {
        nova_janela('../../class_data_holerith/alterar.php?data='+document.form.cmb_data_holerith.value, 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function atualizar() {
    document.form.passo.value = 2
    document.form.submit()
}

function calcular_valor_parcela() {
//Controle com os objetos - Array(s) mais abaixo ...
    var elementos = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['txt_data_holerith[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['txt_data_holerith[]'].length)
    }
//Em Primeiro Lugar - Sempre que chamar esta função, eu limpo todas as caixas ...
    for(var i = 0; i < linhas; i++) {
        document.getElementById('txt_data_holerith'+i).value    = ''
        document.getElementById('txt_vencimento'+i).value       = ''
        document.getElementById('txt_valor_parcela'+i).value    = ''
    }
//Qtde da Parcelas ...
    if(document.form.txt_qtde_parcelas.value != '' && document.form.txt_data_emissao.value.length == 10 && document.form.txt_valor.value != '') {
//Valor do Empréstimo
        var valor_emprestimo = eval(strtofloat(document.form.txt_valor.value))
/****************************************************************************************************/
//1) Rotina para armazenar as Datas de Holerith do Loop em PHP nesse vetor de JavaScript ...
        var qtde_parcelas = eval(strtofloat(document.form.txt_qtde_parcelas.value))
        var data_holerith_vetor = new Array()
<?
        for($i = 0; $i < $linhas; $i++) {
?>
//Essa variável é o que o usuário tem pra receber da Conta com todos os juros, acréscimos, ...
            data_holerith_vetor['<?=$i?>'] = '<?=$campos[$i]["data_formatada"]?>'
<?
        }
?>
/****************************************************************************************************/
/*2) Controle p/ ver quais Datas de Holerith que eu irei estar utilizando no Sistema em relação a Data 
de Holerith selecionada pelo usuário na Combo ...*/
        var data_holerith_principal = document.form.cmb_data_holerith.value//Selecionada pelo usuário ...
//Aqui eu retiro os traços da Data de Holerith Principal p/ poder transformar em número ...
        data_holerith_principal = data_holerith_principal.replace('-', '')
        data_holerith_principal = data_holerith_principal.replace('-', '')
        data_holerith_principal = eval(data_holerith_principal)
        var j = 0//Variável q vai servir como índice p/ alimentar com Dados o Novo Vetor ...
//Só irá ficar nesse vetor as Datas de Holerith acima da combo de Data de Holerith selecionada pelo usuário ...
        var novo_data_holerith_vetor = new Array()
/*Aqui eu comparo a Data de Holerith Principal com as Datas do Vetor em JavaScript p/ ver qual é que eu
vou estar utilizando ...*/
        for(var i = 0; i < data_holerith_vetor.length; i++) {
//Transformando a Data de Holerith em Número p/ poder comparar com a Data de Holerith Principal ...
            data_holerith_loop = eval(data_holerith_vetor[i].substr(6,4) + data_holerith_vetor[i].substr(3,2) + data_holerith_vetor[i].substr(0,2))
//Aqui eu verifico qual vai ser o tamanho desse novo vetor ...
            if(data_holerith_loop >= data_holerith_principal) {
                novo_data_holerith_vetor[j] = data_holerith_vetor[i]
                j++
            }
        }
/*3) Verifico se o Número de Parcelas desejado pelo usuário é maior do que a Qtde de Datas de Holeriths 
disponíveis*/
        if(qtde_parcelas > j) {
            alert('QTDE DE PARCELAS INVÁLIDA !!!\nQTDE DE PARCELAS MAIOR QUE A QTDE DE DATA(S) DE HOLERITH CADASTRADA(S) !')
            document.form.txt_qtde_parcelas.value = ''
//Limpando as Caixas p/ a tela não ficar com resíduo ...
            for(var i = 0; i < linhas; i++) {
                document.getElementById('txt_data_holerith'+i).value    = ''
                document.getElementById('txt_vencimento'+i).value       = ''
                document.getElementById('txt_valor_parcela'+i).value    = ''
            }
            return false
        }
/****************************************************************************************************/
//4) Calculando o Valor da Parcela ...
        var valor_parcela   = valor_emprestimo / qtde_parcelas
        var resto           = 0
        /*__________________________________________________________________________________*/
        //Controle p/ saber se a somatória das parcelas, dá diferença com o total do empréstimo ...
        valor_parcela = eval(strtofloat(arred(String(valor_parcela), 2, 1)))//Arredondo o valor_parcela p/ 2 casas decimais p/ poder fazer a Continha ...
        var somatoria_parcelas = (valor_parcela * qtde_parcelas)
        //Significa que existe uma divergência de Valores e sendo assim o resto será debitado somente dá última parcela ...
        if(somatoria_parcelas != valor_emprestimo) {
            resto   = (somatoria_parcelas - valor_emprestimo)
            resto   = eval(strtofloat(arred(String(resto), 2, 1)))
        }
        /*__________________________________________________________________________________*/
        var j = 0//Variável q vai servir p/ preencher as caixas somente pela qtde de parcela
        for(var i = 0; i < linhas; i++) {
            if(j == qtde_parcelas) {//Quando já preencheu as caixas, sai fora do loop
                i = linhas
            }else {
//Data de Holerith
                document.getElementById('txt_data_holerith'+i).value = novo_data_holerith_vetor[j]
/*Vencimento, retorna a diferença em dias da Data Atual "Hoje" até a próxima Data de Holerith 
cadastrada no Sys ...*/
                document.getElementById('txt_vencimento'+i).value = diferenca_datas(document.form.txt_data_emissao.value, novo_data_holerith_vetor[j])
//Valor da Parcela ...
                if((j + 1) == qtde_parcelas) {//Representa que o sistema está preenchendo o Valor da última Parcela ...
                    document.getElementById('txt_valor_parcela'+i).value = (valor_parcela - resto)
                }else {//Demais Parcelas ...
                    document.getElementById('txt_valor_parcela'+i).value = valor_parcela
                }
                document.getElementById('txt_valor_parcela'+i).value = arred(document.getElementById('txt_valor_parcela'+i).value, 2, 1)
            }
            j++
        }
    }
}

function controlar_financeira(opcao_financeira) {
    var mensalidade_metalcred = '<?=$mensalidade_metalcred;?>'
    /*Se o usuário selecionou a opção MetalCred e o funcionário não tem cadastro, é necessário cadastrar o mesmo p/ 
    poder utilizar essa opção ...*/
    if(opcao_financeira == 'MetalCred' && mensalidade_metalcred == 'N') {
        alert('FUNCIONÁRIO NÃO TEM CADASTRO NA METALCRED !')
        document.getElementById('opt_financeira1').checked = false
    }
}
</Script>
</head>
<body onload='document.form.txt_valor.focus();<?=$function;?>'>
<form name='form' method='post' action='' onsubmit="return validar()">
<!--Aqui eu renomeio essa variável $id_funcionario para $id_funcionario_loop para não dar conflito com 
a variável da Sessão "$id_funcionario"-->
<input type='hidden' name='id_funcionario_loop' value="<?=$id_funcionario_loop;?>">
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='passo' onclick='atualizar()'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Incluir Vale Crédito Consignado
        </td>
    </tr>
    <tr class="linhanormal">
            <td><b>Funcionário:</b>
            <td colspan="3">
            <?
                    $sql = "SELECT id_empresa, nome 
                            FROM `funcionarios` 
                            WHERE `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
                    $campos = bancos::sql($sql);
/*Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
e o parâmetro pop_up significa que está tela está sendo aberta como pop_up e sendo assim é para não exibir
o botão de Voltar que existe nessa tela*/
                    $url = "javascript:nova_janela('../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=".$id_funcionario_loop."&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
            ?>
                    <a href="#" onclick="<?=$url;?>" title="Detalhes Funcionário" class="link">
                            <?=$campos[0]['nome'];?>
                    </a>
                    (<?=genericas::nome_empresa($campos[0]['id_empresa']);?>)
            </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Financeira:</b>
        </td>
        <td colspan='4'>
            <input type='radio' name='opt_financeira' id='opt_financeira1' value='MetalCred' onclick='controlar_financeira(this.value)'>
            <label for='opt_financeira1'>
                MetalCred
            </label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_financeira' id='opt_financeira2' value='Itaú' onclick='controlar_financeira(this.value)'>
            <label for='opt_financeira2'>
                Itaú
            </label>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_financeira' id='opt_financeira3' value='Bradesco' onclick='controlar_financeira(this.value)'>
            <label for='opt_financeira3'>
                Bradesco 
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <?
//Somente na primeira vez em q carrega a Tela é q vem preenchido com a Data do Dia como sugestiva ...
            if(empty($txt_data_emissao)) $txt_data_emissao = date('d/m/Y');
        ?>
        <td width='25%'>
            <b>Data de Emissão:</b>
        </td>
        <td width='25%'>
            <input type='text' name='txt_data_emissao' value='<?=$txt_data_emissao;?>' title='Digite a Data de Emissão' size="12" maxlength="10" onkeyup="verifica(this, 'data', '', '', event)" onblur="calcular_valor_parcela()" class="caixadetexto">
            &nbsp; <img src="../../../../imagem/calendario.gif" width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="javascript:nova_janela('../../../../calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
        <td width='25%'>
            <b>Valor do Empréstimo:</b>
        </td>
        <td width='25%'>
            <input type='text' name='txt_valor' value='<?=$txt_valor;?>' title='Digite o Valor' size="12" maxlength="10" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_parcela()" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Holerith:</b>
        </td>
        <td>
            <select name='cmb_data_holerith' title='Selecione a Data de Holerith' onchange='calcular_valor_parcela()' class='combo'>
            <?
                /***********************************************/
                $data_emissao = date('Y-m-d');
//Aqui é uma lógica p/ verificar qual que seria a próxima Data de Holerith acima da Data de Emissão ...
                $sql = "SELECT data 
                        FROM `vales_datas` 
                        WHERE `data` >= '$data_emissao' ORDER BY data LIMIT 1 ";
                $campos_data_holerith   = bancos::sql($sql);
//Vou utilizar essa variável p/ trazer carregada na combo e numa consulta de SQL mais abaixo ...
                $data_holerith_sql      = $campos_data_holerith[0]['data'];
/***********************************************/
                $data_atual = date('Y-m-d');
/*Só listo nessa Combo as Datas de Holeriths que sejam > que a Data de Atual e que o Dia do Vencimento seja até 
no máximo dia 5 que é a Data Convencional de Pagamento Salarial, dias superiores a Dia 5 geralmente são p/ 
pagamento de Abono, Boticário, etc e não têm nada a ver com o que precisa ser feito aqui ...*/
                $sql = "SELECT data, DATE_FORMAT(data, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        WHERE `data` > '$data_atual' 
                        AND DAY(`data`) <= '5' ORDER BY data ";
                echo combos::combo($sql, $data_holerith_sql);
            ?>
            </select>
            &nbsp;&nbsp; <img src = "../../../../imagem/menu/incluir.png" border='0' title="Incluir Data de Holerith" alt="Incluir Data de Holerith" onClick="incluir_data_holerith()">
            &nbsp;&nbsp; <img src = "../../../../imagem/menu/alterar.png" border='0' title="Alterar Data de Holerith" alt="Alterar Data de Holerith" onClick="alterar_data_holerith()">
        </td>
        <td>
            <b>Qtde de Parcelas:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_parcelas' value='<?=$txt_qtde_parcelas;?>' title='Digite a Qtde de Parcelas' size="12" maxlength="10" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0 || this.value > 48) {this.value = ''};calcular_valor_parcela()" class="caixadetexto">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td colspan='3'>
            <textarea name='txt_observacao' cols='55' rows='2' maxlength='110' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_valor.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
</table>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Data(s) Vencimento(s)
        </td>
        <td>
            Vencimento(s)
        </td>
        <td>
            Valor da Parcela
        </td>
    </tr>
<?
        //Aqui printo 48 linhas relacionadas ao Vencimento, Data de Vencimento e valor da Parcela ...
	for($i = 0; $i < 48; $i++) {
?>
    <tr class='linhanormal' id="linha_venc<?=$i;?>" align='center'>
        <td>
            <b><?=($i + 1);?></b>
        </td>
        <td>
            <input type='text' name="txt_data_holerith[]" id="txt_data_holerith<?=$i;?>" size="12" class="textdisabled" disabled>
        </td>
        <td>
            <input type='text' name="txt_vencimento[]" id="txt_vencimento<?=$i;?>" size="12" class="textdisabled" disabled>
        </td>
        <td>
            <input type='text' name="txt_valor_parcela[]" id="txt_valor_parcela<?=$i;?>" size="12" class="textdisabled" disabled>
        </td>
    </tr>
<?
	}
?>
</table>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_valor.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
//Tratamento com os campos p/ poder gravar no BD ...
    $txt_data_emissao 	= data::datatodate($txt_data_emissao, '-');
    $data_sys           = date('Y-m-d H:i:s');
//Disparo do Loop ...
    for($i = 0; $i < $_POST['txt_qtde_parcelas']; $i++) {
//Se o Campo Data de Holerith estiver preenchido, então eu gero vale para esse funcionário ...
        if(!empty($_POST['txt_data_holerith'][$i])) {
/****************************Preparando as variáveis p/ gravar no Banco****************************/
            $parcelamento = ($i + 1).'/'.$_POST['txt_qtde_parcelas'];
            $_POST['txt_data_holerith'][$i] = data::datatodate($_POST['txt_data_holerith'][$i], '-');
/**************************************************************************************************/
//Inserindo o Vale na Tabela ...
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `financeira`, `parcelamento`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `observacao`, `data_sys`) VALUES (NULL, '$_POST[id_funcionario_loop]', '14', '$_POST[opt_financeira]', '$parcelamento', '".$_POST['txt_valor_parcela'][$i]."', '".$_POST['txt_data_holerith'][$i]."', '$txt_data_emissao', 'PD', '$_POST[txt_observacao]', '$data_sys') ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?valor=2'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Vale Crédito Consignado ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.value       = ''
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.value       = ''
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
//Aqui é para não atualizar os frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
}
</Script>
</head>
<body onLoad="document.form.txt_consultar.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=1';?>" onSubmit="return validar()">
<input type='hidden' name='passo' value='1'>
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table border="0" width="70%" align="center" cellspacing ='1' cellpadding='1'>
	<tr align='center'>
            <td colspan='2'>
                <b><?=$mensagem[$valor];?></b>
            </td>
	</tr>
	<tr class='linhacabecalho' align='center'>
            <td colspan='2'>
                Consultar Funcionário(s)
            </td>
	</tr>
	<tr class='linhanormal' align='center'>
            <td colspan='2'>
                Consultar <input type='text' name="txt_consultar" title="Consultar Funcionário" size="45" maxlength="45" class="caixadetexto">
            </td>
	</tr>
	<tr class='linhanormal'>
            <td width="20%"><input type="radio" name="opt_opcao" value="1" title="Consultar Funcionário por: Nome" onclick="document.form.txt_consultar.focus()" id='label' checked>
                <label for="label">Nome</label>
            </td>
            <td width="20%">
                <input type='checkbox' name='opcao' value='2' title="Consultar todos os funcionários" onclick='limpar()' id='label2' class="checkbox">
                <label for="label2">Todos os registros</label>
            </td>
	</tr>
	<tr class="linhacabecalho" align="center">
            <td colspan="2">
                <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../itens/incluir.php'" class='botao'>
                <input type="reset" name="cmd_limpar" value="Limpar" title="Limpar" onclick="document.form.opcao.checked = false;limpar();" style="color:#ff9900;" class='botao'>
                <input type="submit" name="cmd_consultar" value="Consultar" title="Consultar" class='botao'>
            </td>
	</tr>
</table>
</form>
</body>
</html>
<?}?>