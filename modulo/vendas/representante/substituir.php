<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) CLIENTE(S) PARA ESSE REPRESENTANTE.</font>";
$mensagem[2] = "<font class='confirmacao'>REPRESENTANTE SUBSTITUÍDO COM SUCESSO PARA ESTE(S) CLIENTE(S) SELECIONADO(S).</font>";

if(!empty($_POST['chkt_cliente'])) {
//Aqui dispara um loop para todos os clientes que foram selecionados ...
    for($i = 0; $i < count($_POST['chkt_cliente']); $i++) {
        //Aqui disparo um loop p/ todas as combos de Empresa Divisão com o Representante 
        for($j = 0; $j < count($_POST['cmb_divisao_representante']); $j++) {
            //Mas só vai estar fazendo o Tratamento apenas p/ as combos selecionadas e daí já faço a Separação
            if($_POST['cmb_divisao_representante'][$j] != '') {
                //Aqui eu busco o String do Início até o String |
                $id_empresa_divisao_loop = strtok($_POST['cmb_divisao_representante'][$j], '|');
                //Aqui eu busco o String do Fim até o String |
                $id_representante_loop = substr(strrchr($_POST['cmb_divisao_representante'][$j], '|'), 1);
                //Aqui eu verifico se existe algum Representante p/ o determinado Cliente e Respectiva Divisão ...
                $sql = "SELECT id_representante 
                        FROM `clientes_vs_representantes` 
                        WHERE `id_cliente` = '".$_POST['chkt_cliente'][$i]."' 
                        AND `id_empresa_divisao` = '$id_empresa_divisao_loop' LIMIT 1 ";
                $campos_representante = bancos::sql($sql);
                if(count($campos_representante) == 0) {//Não existe nada ainda, então Insere um Registro ...
                    $sql = "INSERT INTO `clientes_vs_representantes` (`id_cliente_representante`, `id_cliente`, `id_representante`, `id_empresa_divisao`) VALUES (NULL, '".$_POST['chkt_cliente'][$i]."', '$id_representante_loop',  '$id_empresa_divisao_loop') ";
                }else {//Como já existe, substituo o Representante da Divisão atual do Cliente por outro q foi escolhido na Combo ...
                    $sql = "UPDATE `clientes_vs_representantes` SET `id_representante` = '$id_representante_loop' WHERE `id_cliente` = '".$_POST['chkt_cliente'][$i]."' AND `id_empresa_divisao` = '$id_empresa_divisao_loop' LIMIT 1 ";
                }
                bancos::sql($sql);
            }
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'substituir.php<?=$parametro;?>&valor=2'
    </Script>
<?
}

/****************************************************/
//Procedimento normal de quando se carrega a Tela ...
$cmb_representante = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['cmb_representante'] : $_GET['cmb_representante'];
//Data Atual
$data_atual = date('Y-m-d');
//Data Atual
$data_um_ano_atras = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -365), '-');
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
$nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
require('../../classes/cliente/tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
if($linhas > 0) {
?>
<html>
<head>
<title>.:: Substituir Cliente(s) do Representante ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++)   {
        if (elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if(valor == false) {
        alert('SELECIONE UM CLIENTE !')
        return false
    }else {
        var elementos = document.form.elements
        var combos_selecionadas = 0, total_combos = 0
        //Aqui eu verifico se tem pelo menos um Representante selecionado ...
        for(i = 0; i < elementos.length; i++) {
            if(elementos[i].type == 'select-one') {
                if(elementos[i].value != 0) combos_selecionadas++
                total_combos++
            }
        }
        if(combos_selecionadas == 0) {
            alert('SELECIONE UM REPRESENTANTE DA EMPRESA DIVISÃO QUE DESEJA SUBSTITUIR !')
            elementos[1].focus()
            return false
        }
        alert('O IDEAL É QUE O(S) REPRESENTANTE(S) DA(S) DIVISÃO(ÕES):\n\nCABRI, HEINZ E WARRIOR FOSSE(M) O(S) MESMO(S); \n\nTOOL-MASTER E NVO TAMBÉM !!!')
    }
}

function controlar_redefinir() {
    redefinir('document.form', 'REDEFINIR')
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<?
/*A intenção é estar fazendo um esquema de dinâmica com as colunas de divisão, sendo assim eu verifico a 
Qtde de Divisões que tenho cadastradas no sistema*/
        $sql = "SELECT id_empresa_divisao, razaosocial 
                FROM `empresas_divisoes` 
                WHERE `ativo` = '1' ";
        $campos_divisoes = bancos::sql($sql);
        $linhas_divisoes = count($campos_divisoes);
        $colspan = 6 + ($linhas_divisoes);//Para ficar um esquema dinâmico com relação as colunas ...
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='<?=$colspan;?>'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='<?=$colspan;?>'>
            <font color='yellow'>
                <b>Volume de Compras dos Último(s) 12 meses</b>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            Cliente
        </td>
        <td>
            Endereço
        </td>
        <td>
            Bairro
        </td>
        <td>
            Cidade
        </td>
        <td>
            UF
        </td>
<?
//Printa as outras colunas de Divisão ...
        for($i = 0; $i < $linhas_divisoes; $i++) {
?>
        <td>
            <?=strtok($campos_divisoes[$i]['razaosocial'], ' ');?> - Desc
            <select name="cmb_divisao_representante[]" title="Selecione o Representante" class="caixadetexto">
            <?
                $sql = "SELECT id_representante, nome_fantasia 
                        FROM `representantes`
                        WHERE ativo = '1' 
                        ORDER BY nome_fantasia ";
                $campos_representante = bancos::sql($sql);
                $linhas_representante = count($campos_representante);
            ?>
                    <option value='' style='color:red'>SELECIONE</option>
            <?
//Listagem ...
                    for($j = 0; $j < $linhas_representante; $j++) {
            ?>
                    <option value="<?=$campos_divisoes[$i]['id_empresa_divisao'].'|'.$campos_representante[$j]['id_representante'];?>"><?=$campos_representante[$j]['nome_fantasia'];?></option>
            <?
                    }
            ?>
            </select>
        </td>
<?
        }
?>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <input type='checkbox' name='chkt_cliente[]' id='chkt_cliente<?=$i;?>' value='<?=$campos[$i]['id_cliente'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['nomefantasia'])) {
                echo $campos[$i]['nomefantasia'];
            }else {
                echo $campos[$i]['razaosocial'];
            }
        ?>
        </td>
        <td>
        <?
            echo $campos[$i]['endereco'];
            //Daí sim printa o complemento ...
            if(!empty($campos[$i]['endereco'])) echo ', '.$campos[$i]['num_complemento'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['bairro'];?>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['sigla'];?>
        </td>
    <?
    //Printa as outras para o controle de Representantes por Divisão
            for($j = 0; $j < $linhas_divisoes; $j++) {
    //Aki eu verifico o "Representante" e "Desconto Cliente" nessa Divisão do loop
                $sql = "SELECT id_representante, desconto_cliente 
                        FROM `clientes_vs_representantes` 
                        WHERE `id_cliente` = ".$campos[$i]['id_cliente']." 
                        AND `id_empresa_divisao` = ".$campos_divisoes[$j]['id_empresa_divisao']." LIMIT 1 ";
                $campos_desconto = bancos::sql($sql);
    ?>
        <td align='center'>
        <?
                //Busca do nome do Representante
                if(count($campos_desconto) == 1) {
                    $sql = "SELECT nome_fantasia 
                            FROM `representantes` 
                            WHERE `id_representante` = ".$campos_desconto[0]['id_representante']." ORDER BY nome_fantasia ";
                    $campos_representante = bancos::sql($sql);
//Quando o Representante da Divisão do Loop for igual ao Principal, coloca uma cor diferente
                    if($cmb_representante == $campos_desconto[0]['id_representante']) {//É igual
                        echo '<font color="blue">'.$campos_representante[0]['nome_fantasia'].'</font> - <font color="green" title="Desconto do Cliente" style="cursor:help">'.segurancas::number_format($campos_desconto[0]['desconto_cliente'], 2, '.').'</font>';
                    }else {//É Diferente
                        echo $campos_representante[0]['nome_fantasia'].' - <font color="green" title="Desconto do Cliente" style="cursor:help">'.segurancas::number_format($campos_desconto[0]['desconto_cliente'], 2, '.').'</font>';
                    }
/***********************************************/
                }
        ?>
        </td>
<?
            }
?>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan="<?=$colspan;?>">
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'substituir.php'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='controlar_redefinir()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>
<?}?>