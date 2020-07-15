<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N�O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>VALE AVULSO / EMPR�STIMO INCLUIDO COM SUCESSO.</font>";

if($passo == 1) {
//Traz todos funcion�rios - menos do cargo AUTON�MO
    switch($opt_opcao) {
/*S� n�o exibo os funcion�rios Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes n�o s�o funcion�rios, simplesmente s� possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
        case 1:
            $sql = "SELECT f.`id_funcionario`, f.`id_funcionario_superior`, f.`nome`, f.`codigo_barra`, e.`nomefantasia`, c.`cargo`, d.`departamento` 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                    INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                    WHERE f.`nome` LIKE '%$txt_consultar%' 
                    AND f.`status` < '3' 
                    AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY f.`nome` ";
        break;
        default:
            $sql = "SELECT f.`id_funcionario`, f.`id_funcionario_superior`, f.`nome`, f.`codigo_barra`, e.`nomefantasia`, c.`cargo`, d.`departamento` 
                    FROM `funcionarios` f 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
                    INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                    INNER JOIN `departamentos` d ON d.`id_departamento` = f.`id_departamento` 
                    WHERE f.`status` < '3' 
                    AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY f.`nome` ";
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
<title>.:: Incluir Vale Avulso / Empr�stimo ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href='../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Consultar Funcion�rio(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            C�digo
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
//Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" da sess�o ...
            $url = 'incluir.php?passo=2&id_funcionario_loop='.$campos[$i]['id_funcionario'];
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'incluir.php?passo=2&id_funcionario_loop=<?=$campos[$i]['id_funcionario'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href='<?=$url;?>'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td align='center'>
            <a href='<?=$url;?>'>
                <?=$campos[$i]['codigo_barra'];?>
            </a>
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
            <input type='button' name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class='botao'>
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
}else if($passo == 2) {
//Aqui eu j� deixo carregada essa vari�vel porque vou estar utilizando essa nos c�lculos em PHP e JavaScript
    $taxa_aplicacao = genericas::variavel(31);
/***************************************************************************************************/
    $data_atual = date('Y-m-d');
/*S� listo nessa Combo as Datas de Holeriths que sejam > que a Data de Atual em que o dia do m�s seja entre dia 2 e 5 que � quando � 
realizado o Pagamento aqui da Empresa ...*/
    $sql = "SELECT `data`, DATE_FORMAT(`data`, '%d/%m/%Y') AS data_formatada 
            FROM `vales_datas` 
            WHERE `data` >= '$data_atual' 
            AND SUBSTRING(`data`, 9, 2) BETWEEN '02' AND '05' ORDER BY `data` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);

//Aqui eu busco a �ltima data de Holerith do Vale de Empr�stimo p/ este Funcion�rio ...
    $sql = "SELECT `data_debito` 
            FROM `vales_dps` 
            WHERE `id_funcionario` = '$id_funcionario_loop' 
            AND `tipo_vale` = '8' ORDER BY `id_vale_dp` DESC LIMIT 1 ";
    $campos_data_debito = bancos::sql($sql);
    if(count($campos_data_debito) == 1) {//Se existir uma �ltima data de d�bito ...
        $ultima_data_debito = $campos_data_debito[0]['data_debito'];
/*Aqui eu fa�o a compara��o dessa �ltima Data de D�bito do Funcion�rio com a Data Atual do Sistema ...
Eu jogo mais 30 dias em cima da Data de D�bito porque n�o posso estar se fazendo empr�stimo no mesmo m�s*/
        $ultima_data_debito_30 = data::datatodate(data::adicionar_data_hora(data::datetodata($ultima_data_debito, '/'), 30), '-');
        if($ultima_data_debito_30 > $data_atual) {
/*Se o Login que estiver realizando o Empr�stimo for do Roberto ou da Dona Sandra, ent�o eu ignoro essa 
verifica��o de �ltimo Empr�stimo ...*/
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 66) {
                $disabled_submit = '';
            }else {//Para os outros usu�rios, eu fa�o essa consist�ncia ...
                $disabled_submit = 'disabled';
            }
        }else {
            $disabled_submit = '';
        }
        $texto = data::datetodata($ultima_data_debito, '/');
    }else {
        //P/ � dar erro de Data apesar de o Funcion�rio nunca ter feito empr�stimos, o sistema sugere a Data Atual ...
        $ultima_data_debito = date('Y-m-d');
        $texto = '<font color="darkblue"><b>SEM CAR�NCIA</b></font>';
    }
?>
<html>
<head>
<title>.:: Incluir Vale Avulso / Empr�stimo ::.</title>
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
//Tipo de Vale
    if(document.form.opt_tipo_vale[0].checked == false && document.form.opt_tipo_vale[1].checked == false) {
        alert('SELECIONE UM TIPO DE VALE !')
        document.form.opt_tipo_vale[0].focus()
        return false
    }
//Tipo de Desconto ...
    if(!combo('form', 'cmb_descontar_pd_pf', '', 'SELECIONE O TIPO DE DESCONTO "PD ou PF" !')) {
        return false
    }    
//Data de Emiss�o
    if(!data('form', 'txt_data_emissao', '4000', 'EMISS�O')) {
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
/*Aqui eu verifico a Data de Emiss�o que est� sendo colocada pelo usu�rio no caso de ele tentar colocar
uma Data de Emiss�o que a Data Atual ou colocar uma Data de Emiss�o muito acima da Data Atual*/
    var data_atual = eval('<?=date(Ymd);?>')
    var data_emissao = document.form.txt_data_emissao.value
    data_emissao = data_emissao.substr(6,4) + data_emissao.substr(3,2) + data_emissao.substr(0,2)
    data_emissao = eval(data_emissao)

    if((data_emissao < data_atual) || (data_emissao > (data_atual + 5))) {
        alert('DATA DE EMISS�O INV�LIDA !!!\nDATA DE EMISS�O MENOR QUE A DATA ATUAL OU MUITO SUPERIOR QUE A DATA ATUAL !')
        document.form.txt_data_emissao.focus()
        document.form.txt_data_emissao.select()
        return false
    }
    calcular_valor_parcela()
    document.form.passo.value = 3
//Desabilito estes campos p/ poder gravar no BD ...
    document.form.txt_taxa_aplicacao.disabled = false
    document.form.txt_data_emissao.disabled = false
//Aqui eu trato este campo p/ poder gravar no BD ...
    limpeza_moeda('form', 'txt_taxa_aplicacao, ')
/*********************Controle p/ Desabilitar as caixas e gravar no BD*********************/
    var qtde_parcelas = eval(strtofloat(document.form.txt_qtde_parcelas.value))
    for(var i = 1; i <= qtde_parcelas; i++) {
        //Desabilitando as Caixas p/ gravar no BD ...
        document.getElementById('txt_data_holerith'+i).disabled = false
        document.getElementById('txt_vencimento'+i).disabled    = false
        document.getElementById('txt_valor_parcela'+i).disabled = false
        //Deixando as Caixas num formato em que eu consiga gravar no BD ...
        document.getElementById('txt_valor_parcela'+i).value    = strtofloat(document.getElementById('txt_valor_parcela'+i).value)
    }
/******************************************************************************************/
    //Desabilito o bot�o Salvar para que o usu�rio n�o clique 2 ou + vezes, submetendo v�rias vezes a mesma informa��o ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
}

function incluir_data_holerith() {
    nova_janela('../class_data_holerith/incluir.php', 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
}

function alterar_data_holerith() {
    if(document.form.cmb_data_holerith.value == '') {
        alert('SELECIONE A DATA DE HOLERITH !')
        document.form.cmb_data_holerith.focus()
        return false
    }else {
        nova_janela('../class_data_holerith/alterar.php?data='+document.form.cmb_data_holerith.value, 'CONSULTAR', '', '', '', '', '200', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function atualizar() {
    document.form.passo.value = 2
    document.form.submit()
}

function calcular_valor_parcela() {
    if(document.form.txt_taxa_aplicacao.value != '') {//Se tiver valor digitado ...
        var taxa_aplicacao = eval(strtofloat(document.form.txt_taxa_aplicacao.value))
    }else {//Se for igual a V�zio ...
        var taxa_aplicacao = 0
    }
//Por seguran�a, para que n�o falcatruem o Sistema sempre limpo todas as caixas abaixo ...
    for(var i = 1; i <= 12; i++) {
        //Limpando as Caixas ...
        document.getElementById('txt_data_holerith'+i).value        = ''
        document.getElementById('txt_vencimento'+i).value           = ''
        document.getElementById('txt_valor_parcela'+i).value        = ''
    }
//Se todas essas vari�veis estiverem preenchidas, ent�o fa�o o c�lculo das Parcelas e Valores em R$ do Empr�stimo ou Vale ...
    if(document.form.txt_qtde_parcelas.value != '' && document.form.txt_data_emissao.value.length == 10 && document.form.txt_valor.value != '') {
        var valor_emprestimo = eval(strtofloat(document.form.txt_valor.value))//Valor do Empr�stimo
/****************************************************************************************************/
//1) Rotina para armazenar as Datas de Holerith do Loop em PHP nesse vetor de JavaScript ...
        var qtde_parcelas = eval(strtofloat(document.form.txt_qtde_parcelas.value))
        var data_holerith_vetor = new Array()
<?
        for($i = 0; $i < $linhas; $i++) {
?>
//Essa vari�vel � o que o usu�rio tem pra receber da Conta com todos os juros, acr�scimos, ...
            data_holerith_vetor['<?=$i?>'] = '<?=$campos[$i]["data_formatada"]?>'
<?
        }
?>
/****************************************************************************************************/
/*2) Controle p/ ver quais Datas de Holerith que eu irei estar utilizando no Sistema em rela��o a Data 
de Holerith selecionada pelo usu�rio na Combo ...*/
        var data_holerith_principal = document.form.cmb_data_holerith.value//Selecionada pelo usu�rio ...
//Aqui eu retiro os tra�os da Data de Holerith Principal p/ poder transformar em n�mero ...
        data_holerith_principal = data_holerith_principal.replace('-', '')
        data_holerith_principal = data_holerith_principal.replace('-', '')
        data_holerith_principal = eval(data_holerith_principal)
        var j = 0//Vari�vel q vai servir como �ndice p/ alimentar com Dados o Novo Vetor ...
//S� ir� ficar nesse vetor as Datas de Holerith acima da combo de Data de Holerith selecionada pelo usu�rio ...
        var novo_data_holerith_vetor = new Array()
/*Aqui eu comparo a Data de Holerith Principal com as Datas do Vetor em JavaScript p/ ver qual � que eu
vou estar utilizando ...*/
        for(var i = 0; i < data_holerith_vetor.length; i++) {
//Transformando a Data de Holerith em N�mero p/ poder comparar com a Data de Holerith Principal ...
            data_holerith_loop = eval(data_holerith_vetor[i].substr(6,4) + data_holerith_vetor[i].substr(3,2) + data_holerith_vetor[i].substr(0,2))
//Aqui eu verifico qual vai ser o tamanho desse novo vetor ...
            if(data_holerith_loop >= data_holerith_principal) {
                novo_data_holerith_vetor[j] = data_holerith_vetor[i]
                j++
            }
        }
/****************************************************************************************************/
//C�lculo p/ a vari�vel Fator Di�rio q vai estar sendo utilizada + abaixo nas parcelas ...
        var fator_diario = (Math.pow((1 + taxa_aplicacao / 100), 1 / 30))
/*Aqui eu fa�o uma jogadinha transformando o fator di�rio em String p/ poder arredondar o n�mero 
retornado do fator di�rio p/ 5 casas ...*/
        //fator_diario = String(fator_diario)
        //fator_diario = fator_diario.replace('.', ',')
        //fator_diario = arred(fator_diario, 5, 1)
//Aqui eu transformo o fator_di�rio em n�mero novamente p/ poder seguir nos c�lculos ...
        //fator_diario = eval(strtofloat(fator_diario))
/****************************************************************************************************/
/*3) Verifico se o N�mero de Parcelas desejado pelo usu�rio � maior do que a Qtde de Datas de Holeriths 
dispon�veis*/
        if(qtde_parcelas > j) {
            alert('QTDE DE PARCELAS INV�LIDA !!!\nQTDE DE PARCELAS MAIOR QUE A QTDE DE DATA(S) DE HOLERITH CADASTRADA(S) !')
            document.form.txt_qtde_parcelas.value = ''
//Limpando as Caixas p/ a tela n�o ficar com res�duo ...
            for(var i = 1; i <= qtde_parcelas; i++) {
                document.getElementById('txt_data_holerith'+i).value    = ''
                document.getElementById('txt_vencimento'+i).value       = ''
                document.getElementById('txt_valor_parcela'+i).value    = ''
            }
            return false
        }
/****************************************************************************************************/
//4) Calculando o Valor da Parcela ...
        var valor_parcela = valor_emprestimo / qtde_parcelas
        for(var i = 1; i <= qtde_parcelas; i++) {
//Data de Holerith
            document.getElementById('txt_data_holerith'+i).value = novo_data_holerith_vetor[i - 1]//A 1� Data de Holerith est� na posi��o Zero ...
/*Vencimento, retorna a diferen�a em dias da Data Atual "Hoje" at� a pr�xima Data de Holerith 
cadastrada no Sys ...*/
            document.getElementById('txt_vencimento'+i).value = diferenca_datas(document.form.txt_data_emissao.value, novo_data_holerith_vetor[i - 1])//A 1� Data de Holerith est� na posi��o Zero ...
//Valor da Parcela ...
            document.getElementById('txt_valor_parcela'+i).value = valor_parcela * Math.pow(fator_diario, document.getElementById('txt_vencimento'+i).value)
            document.getElementById('txt_valor_parcela'+i).value = arred(document.getElementById('txt_valor_parcela'+i).value, 2, 1)
        }
    }
}

function controlar_tipo_vale(tipo_vale) {
    if(tipo_vale == 2) {//Vale Avulso ...
        document.form.txt_taxa_aplicacao.disabled   = true
        document.form.txt_taxa_aplicacao.className  = 'textdisabled'
        document.form.txt_taxa_aplicacao.value      = '0,00'
    }else if(tipo_vale == 8) {//Vale Empr�stimo
        var id_funcionario      = eval('<?=$_SESSION[id_funcionario];?>')
        var disabled_submit     = '<?=$disabled_submit;?>'
        //Se os usu�rios logados for Roberto 62, Dona Sandra 66 ou D�rcio 98 porque programa, ent�o sempre habilito o bot�o p/ Salvar o Empr�stimo ...
        if(id_funcionario == 62 || id_funcionario == 66 || id_funcionario == 98) {
            document.form.txt_taxa_aplicacao.disabled   = false
            document.form.txt_taxa_aplicacao.className  = 'caixadetexto'
        }else {
            document.form.cmd_salvar.disabled   = disabled_submit
            if(disabled_submit == '') {//Significa que o bot�o est� habilitado ...
                document.form.cmd_salvar.className = 'botao'
            }else {//Significa que o bot�o est� desabilitado ...
                document.form.cmd_salvar.className = 'textdisabled'
            }
        }
        document.form.txt_taxa_aplicacao.value      = '<?=number_format($taxa_aplicacao, 2, ',', '.');?>'
        aviso_regra_emprestimo()
    }
    calcular_valor_parcela()
}

function aviso_regra_emprestimo() {
    var id_funcionario      = eval('<?=$_SESSION[id_funcionario];?>')
    var ultima_data_debito  = '<?=data::datetodata($ultima_data_debito, '/');?>'
    var data_liberacao      = '<?=data::adicionar_data_hora(data::datetodata($ultima_data_debito, '/'), 30);?>'
    
    //Se os usu�rios logados for Roberto 62, Dona Sandra 66 ou D�rcio 98 porque programa, ent�o o sistema sempre pergunta antes de exibir a Mensagem ...
    if(id_funcionario == 62 || id_funcionario == 66 || id_funcionario == 98) {
        var resposta = confirm('O VENCIMENTO DO �LTIMO EMPR�STIMO � '+ultima_data_debito+' !\nO PR�XIMO SER� LIBERADO APENAS AP�S '+data_liberacao+' !')
        if(resposta == false) {
            parent.fechar_pop_up_div()
        }
    }else {
        alert('O VENCIMENTO DO �LTIMO EMPR�STIMO � '+ultima_data_debito+' !\nO PR�XIMO SER� LIBERADO APENAS AP�S '+data_liberacao+' !')
        parent.fechar_pop_up_div()
    }
}
</Script>
</head>
<body onload='document.form.txt_valor.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--Aqui eu renomeio essa vari�vel $id_funcionario para $id_funcionario_loop para n�o dar conflito com 
a vari�vel da Sess�o "$id_funcionario"-->
<input type='hidden' name='id_funcionario_loop' value='<?=$id_funcionario_loop;?>'>
<!--Esse hidden � um controle de Tela-->
<input type='hidden' name='passo' onclick='atualizar()'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='4'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Incluir Vale Avulso / Empr�stimo
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Funcion�rio:</b>
        </td>
        <td colspan='3'>
        <?
            $sql = "SELECT `id_empresa`, `nome`, `salario_pd`, `salario_pf`, `salario_premio`, 
                    `comissao_ultimos3meses_pd`, `comissao_ultimos3meses_pf` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = '$id_funcionario_loop' LIMIT 1 ";
            $campos = bancos::sql($sql);
/*Coloquei esse nome de $id_funcionario_loop, p/ n�o dar conflito com a vari�vel "id_funcion�rio" da sess�o
e o par�metro pop_up significa que est� tela est� sendo aberta como pop_up e sendo assim � para n�o exibir
o bot�o de Voltar que existe nessa tela*/
            $url = "javascript:nova_janela('../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=".$id_funcionario_loop."&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
    ?>
            <a href="#" onclick="<?=$url;?>" title='Detalhes Funcion�rio' class='link'>
                <?=$campos[0]['nome'];?>
            </a>
            (<?=genericas::nome_empresa($campos[0]['id_empresa']);?>)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Vale:</b>
        </td>
        <td colspan='3'>
            <input type='radio' name='opt_tipo_vale' value='2' id='opt_tipo_vale2' onclick='controlar_tipo_vale(this.value)'>
            <label for='opt_tipo_vale2'>
                Avulso
            </label>
            <input type='radio' name='opt_tipo_vale' value='8' id='opt_tipo_vale8' onclick='controlar_tipo_vale(this.value)'>
            <label for='opt_tipo_vale8'>
                Empr�stimo
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Descontar PD / PF:</b>
        </td>
        <td colspan='3'>
            <?
                if($campos[0]['id_empresa'] == 4) {//Se a empresa do Funcion�rio = 'GRUPO' ent�o o sal�rio sempre ser� PF ...
                    $option_pf = '<option value="PF">PF</option>';
                }else {//ALBAFER ou TOOL MASTER ...
                    /*Mediante a forma de pagamento do sal�rio do Funcion�rio, o sistema ir� sugerir na combo abaixo 
                    maneiras p/ Descontar do Funcion�rio o vale requisitado ...*/
                    if($campos[0]['salario_pd'] > 0 && ($campos[0]['salario_pf'] + $campos[0]['salario_premio']) == 0) {
                        $option_pd = '<option value="PD">PD</option>';
                        //Se o usu�rio tem Comiss�o PF, ent�o exibo essa op��o ...
                        if($campos[0]['comissao_ultimos3meses_pf'] > 0) $option_pf = '<option value="PF">PF</option>';
                    }else if($campos[0]['salario_pd'] == 0 && ($campos[0]['salario_pf'] + $campos[0]['salario_premio']) > 0) {
                        $option_pf = '<option value="PF">PF</option>';
                        //Se o usu�rio tem Comiss�o PD, ent�o exibo essa op��o ...
                        if($campos[0]['comissao_ultimos3meses_pd'] > 0) $option_pd = '<option value="PD">PD</option>';
                    }else if($campos[0]['salario_pd'] > 0 && ($campos[0]['salario_pf'] + $campos[0]['salario_premio']) > 0) {
                        $option_pd = '<option value="PD">PD</option>';
                        $option_pf = '<option value="PF">PF</option>';
                    }
                }
            ?>

            <select name='cmb_descontar_pd_pf' title='Selecione o Descontar PD / PF' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?=$option_pd;?>
                <?=$option_pf;?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>�ltima Data de Empr�stimo:</b>
        </td>
        <td colspan='3'>
            <?=$texto;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <?
            //Somente na primeira vez em q carrega a Tela � q vem preenchido com a Data do Dia como sugestiva ...
            if(empty($txt_data_emissao)) $txt_data_emissao = date('d/m/Y');
        ?>
        <td>
            <b>Data de Emiss�o:</b>
        </td>
        <td colspan='3'>
            <input type='text' name='txt_data_emissao' value='<?=$txt_data_emissao;?>' title='Digite a Data de Emiss�o' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" onblur='calcular_valor_parcela()' class='caixadetexto'>
            &nbsp; <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1', 'CALEND�RIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor do Empr�stimo:</b>
        </td>
        <td>
            <input type='text' name='txt_valor' value='<?=$txt_valor;?>' title='Digite o Valor' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_parcela()" class='caixadetexto'>
        </td>
        <td>
            <b>Qtde de Parcelas:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_parcelas' value='<?=$txt_qtde_parcelas;?>' title='Digite a Qtde de Parcelas' size='12' maxlength='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0 || this.value > 12) {this.value = ''};calcular_valor_parcela()" class='caixadetexto'>
        </td>
    </tr>
    <?
/***********************************************/
        $data_emissao = date('Y-m-d');
//Aqui � uma l�gica p/ verificar qual que seria a pr�xima Data de Holerith acima da Data de Emiss�o ...
        $sql = "SELECT `data` 
                FROM `vales_datas` 
                WHERE data >= '$data_emissao' ORDER BY `data` LIMIT 1 ";
        $campos_data_holerith = bancos::sql($sql);
//Vou utilizar essa vari�vel p/ trazer carregada na combo e numa consulta de SQL mais abaixo ...
        $data_holerith_sql = $campos_data_holerith[0]['data'];
/***********************************************/
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Data de Holerith:</b>
        </td>
        <td>
            <select name='cmb_data_holerith' title='Selecione a Data de Holerith' onchange='calcular_valor_parcela()' class='combo'>
            <?
                $data_atual_menos_60 = data::adicionar_data_hora(date('d/m/Y'), -60);
                $data_atual_menos_60 = data::datatodate($data_atual_menos_60, '-');
/*S� listo nessa Combo as Datas de Holeriths que sejam > que a Data de Atual em que o dia do m�s seja entre dia 2 e 5 que � quando � 
realizado o Pagamento aqui da Empresa ...*/
                $sql = "SELECT `data`, DATE_FORMAT(`data`, '%d/%m/%Y') AS data_formatada 
                        FROM `vales_datas` 
                        WHERE `data` >= '$data_atual_menos_60' 
                        AND SUBSTRING(`data`, 9, 2) BETWEEN '02' AND '05' ORDER BY `data` ";
                echo combos::combo($sql, $data_holerith_sql);
            ?>
            </select>
            &nbsp;&nbsp; <img src = '../../../../imagem/menu/incluir.png' border='0' title='Incluir Data de Holerith' alt='Incluir Data de Holerith' onclick='incluir_data_holerith()'>
            &nbsp;&nbsp; <img src = '../../../../imagem/menu/alterar.png' border='0' title='Alterar Data de Holerith' alt='Alterar Data de Holerith' onclick='alterar_data_holerith()'>
        </td>
        <td>
            <b>Taxa de Aplic.:</b>
        </td>
        <td>
        <?
/*Se os usu�rios logados que estiverem fazendo o empr�stimo for o Roberto ou a Dona Sandra, ent�o esta 
caixa vem habilitada ...*/
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 66) {//Habilitada ...
                $class = 'caixadetexto';
                $disabled = '';
            }else {//Desabilitada ...
                $class = 'textdisabled';
                $disabled = 'disabled';
            }
        ?>
            <input type='text' name='txt_taxa_aplicacao' value='0,00' size='12' maxlength='10' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valor_parcela()" class='<?=$class;?>' <?=$disabled;?>> %
        </td>
    </tr>
</table>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td width='33%'>
            Data(s) Vencimento(s)
        </td>
        <td width='33%'>
            Vencimento(s)
        </td>
        <td width='33%'>
            Valor da Parcela
        </td>
    </tr>
<?
//Aqui printo 12 linhas relacionadas ao Vencimento, Data de Vencimento e valor da Parcela ...
    for($i = 1; $i <= 12; $i++) {
?>
    <tr id='linha_venc<?=$i;?>' class='linhanormal' align='center'>
        <td>
            <input type='text' name='txt_data_holerith[]' id='txt_data_holerith<?=$i;?>' size='12' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_vencimento[]' id='txt_vencimento<?=$i;?>' size='12' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_valor_parcela[]' id='txt_valor_parcela<?=$i;?>' size='12' class='textdisabled' disabled>
        </td>
    </tr>
<?
	}
?>
</table>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhanormal'>
        <td>
            Observa��o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='55' rows='2' maxlength='110' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title="Limpar" style='color:#ff9900' onclick="redefinir('document.form', 'LIMPAR');document.form.txt_valor.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
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
    $meses              = count($_POST['txt_data_holerith']);
//Disparo do Loop ...
    for($i = 0; $i < $meses; $i++) {
//Se o Campo Data de Holerith estiver preenchido, ent�o eu gero vale para esse funcion�rio ...
        if(!empty($_POST['txt_data_holerith'][$i])) {
/****************************Preparando as vari�veis p/ gravar no Banco****************************/
            $parcelamento = ($i + 1).'/'.$meses;
            $_POST['txt_data_holerith'][$i] = data::datatodate($_POST['txt_data_holerith'][$i], '-');
/**************************************************************************************************/
//Inserindo o Vale na Tabela ...
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `parcelamento`, `taxa_emprestimo`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `observacao`, `data_sys`) VALUES (NULL, '$id_funcionario_loop', '$_POST[opt_tipo_vale]', '$parcelamento', '$_POST[txt_taxa_aplicacao]', '$txt_valor_parcela[$i]', '".$_POST['txt_data_holerith'][$i]."', '$txt_data_emissao', '$_POST[cmb_descontar_pd_pf]', '$_POST[txt_observacao]', '$data_sys') ";
            bancos::sql($sql);
            $id_vales_dps.= bancos::id_registro().', ';
        }
    }
    $id_vales_dps = substr($id_vales_dps, 0, strlen($id_vales_dps) - 2);
?>
    <Script Language = 'Javascript' Src = '../../../../js/nova_janela.js'></Script>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php?valor=2'
        nova_janela('../itens/relatorios/relatorio_vale/relatorio.php?id_vales_dps=<?=$id_vales_dps;?>', 'CONSULTAR', 'F')
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Incluir Vale Avulso / Empr�stimo ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
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
//Aqui � para n�o atualizar os frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<!--Esse hidden � um controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Funcion�rio(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' title='Consultar Funcion�rio' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' title='Consultar Funcion�rio por: Nome' onclick='document.form.txt_consultar.focus()' id='label' checked>
            <label for='label'>Nome</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' value='2' title='Consultar todos os funcion�rios' onclick='limpar()' id='label2' class='checkbox'>
            <label for='label2'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan="2">
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../itens/incluir.php'" class='botao'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick="document.form.opcao.checked = false;limpar()" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>