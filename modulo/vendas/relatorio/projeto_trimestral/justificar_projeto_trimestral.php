<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
session_start('funcionarios');

//Tratamento com os objetos após ter submetido a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_projecao_trimestral = $_POST['id_projecao_trimestral'];
}else {
    $id_projecao_trimestral = $_GET['id_projecao_trimestral'];
}

/************************************************************************************/
//Se o funcionario estiver logado ou não foi passado parâmetro de Projeção Trimestral então ...
if (!(session_is_registered('id_funcionario')) && empty($id_projecao_trimestral)) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../../html/index.php?valor=1'
    </Script>
<?
    exit;
}
/************************************************************************************/
if(!empty($_POST['chkt_projecao_trimestral'])) {
    foreach($_POST['chkt_projecao_trimestral'] as $i => $id_projecao_trimestral) {
        //Registra na Projeção Trimestral, a justificativa que o Vendedor deu para matar a Projeção ...
        $sql = "UPDATE `projecoes_trimestrais` SET `justificativa` = '".$_POST['txt_justificativa'][$i]."' WHERE `id_projecao_trimestral` = '$id_projecao_trimestral' LIMIT 1 ";
        bancos::sql($sql);

        //Aqui eu gero um Follow-Up para o Cliente ...
        $sql = "SELECT cc.id_cliente_contato, pt.id_cliente 
                FROM `projecoes_trimestrais` pt 
                INNER JOIN `clientes_contatos` cc ON cc.id_cliente = pt.id_cliente AND cc.ativo = '1' 
                WHERE `id_projecao_trimestral` = '$id_projecao_trimestral' LIMIT 1 ";
        $campos_contato     = bancos::sql($sql);
        $id_cliente_contato = $campos_contato[0]['id_cliente_contato'];
        $id_cliente         = $campos_contato[0]['id_cliente'];
        
        $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_cliente_contato`, `id_funcionario`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', '$id_cliente_contato', '$_SESSION[id_funcionario]', '13', '".$_POST['txt_justificativa'][$i]."', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        alert('JUSTIFICATIVA REGISTRADA COM SUCESSO !')
        window.location = '../../../../mural/mural.php'
    </Script>
<?
}

//Aqui eu busco todas as Projeções passadas por parâmetro ...
$sql = "SELECT IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) as cliente, f.nome, pt.* 
        FROM `projecoes_trimestrais` pt 
        INNER JOIN `clientes` c ON c.id_cliente = pt.id_cliente 
        INNER JOIN `funcionarios` f ON f.id_funcionario = pt.id_funcionario 
        WHERE pt.id_projecao_trimestral IN ($id_projecao_trimestral) ORDER BY pt.id_projecao_trimestral DESC ";
$campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
$linhas = count($campos);
?>
<html>
<head>
<title>.:: Justificar Projeto Trimestral ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = 'controle.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    var cont_checkbox_selecionados = 0, total_linhas = 0
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            if(elementos[i].name == 'chkt_projecao_trimestral[]') {//Só vasculho os checkbox de Produtos ...
                if(elementos[i].checked) {
                    valor = true
                    cont_checkbox_selecionados++
                }
                total_linhas++
            }
        }
    }
    //Se não tiver nenhuma opção selecionada, força o Preenchimento ...
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {//Se tiver pelo menos 1 opção selecionada ...
        for(var i = 0; i < total_linhas; i++) {
//Força o Preenchimento do Campo Quantidade ...
            if(document.getElementById('chkt_projecao_trimestral'+i).checked == true) {
                //Força preencher a Justificativa ...
                if(document.getElementById('txt_justificativa'+i).value == '') {
                    alert('DIGITE A JUSTIFICATIVA !')
                    document.getElementById('txt_justificativa'+i).focus()
                    return false
                }
            }
        }
    }
}
</Script>
</head>
<body>
<form name='form' action='' method='post' onsubmit='return validar()'>
<table width='80%' border='1' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Justificar Projeções Realizadas
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' id='chkt_tudo' onClick="selecionar_tudo_incluir(totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            Cliente
        </td>
        <td>
            Funcionário
        </td>
        <td>
            Tipo de Projeção
        </td>
        <td>
            Tipo de Produto
        </td>
        <td>
            Qtde de Produtos
        </td>
        <td>
            Qtde de Meses
        </td>
        <td>
            Perc.
        </td>
        <td>
            Valor
        </td>
        <td>
            Data / Hora
        </td>
        <td>
            Justificativa
        </td>
    </tr>
<?
    for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_projecao_trimestral[]' id='chkt_projecao_trimestral<?=$i;?>' value="<?=$campos[$i]['id_projecao_trimestral'];?>" onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='left'>
            <?=$campos[$i]['cliente'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_projecao'] == 'C') {
                echo 'Cliente Compra';
            }else {
                echo 'Cliente não Compra';
            }
        ?>
        </td>
        <td>
            <?=$campos[$i]['tipo_produto'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde_produtos'];?>
        </td>
        <td>
            <?=$campos[$i]['qtde_meses'];?>
        </td>
        <td>
            <?=$campos[$i]['percentagem'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor_projecao'], 2, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').' '.substr($campos[$i]['data_sys'], 11, 8);?>
        </td>
        <td>
            <textarea name='txt_justificativa[]' id='txt_justificativa<?=$i;?>' title='Digite a Justificativa' cols='85' rows='3' maxlength='255' onclick="checkbox_incluir('<?=$i;?>', '#E8E8E8');focos(this)" class='textdisabled' disabled></textarea>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<input type='hidden' name='id_projecao_trimestral' value='<?=$id_projecao_trimestral;?>'>
</form>
</body>
</html>