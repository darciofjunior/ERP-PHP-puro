<?
require('../../../lib/segurancas.php');
require('../../../lib/custos.php');
segurancas::geral('/erp/albafer/modulo/producao/programacao/estoque/gerenciar/consultar.php', '../../../');

$mensagem[1] = "<font class='atencao'>NÃO EXISTE(M) ITEM(NS) COM ENTRADA ANTECIPADA.</font>";
$mensagem[2] = "<font class='confirmacao'>RETORNO DE ENTRADA ANTECIPADA REALIZADO COM SUCESSO.</font>";

if(!empty($_POST['chkt_produto_acabado'])) {
    foreach($_POST['chkt_produto_acabado'] as $i => $id_produto_acabado) {
        $sql = "UPDATE `estoques_acabados` SET `entrada_antecipada` = `entrada_antecipada` - ".$_POST['txt_qtde_retornada'][$i]." WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
        bancos::sql($sql);
    }
    $valor = 2;
}

//Procedimento normal de quando se carrega a Tela ...
$id_produto_acabado = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado'] : $_GET['id_produto_acabado'];
?>
<html>
<head>
<title>.:: Retorno de Entrada Antecipada ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = 'tabela_itens_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos                   = document.form.elements
    //Significa que está tela foi carregada com apenas 1 linha ...
    var linhas                              = (typeof(elementos['chkt_produto_acabado[]'][0]) == 'undefined') ? 1 : elementos['chkt_produto_acabado[]'].length
    var total_produtos_acabados_marcados    = 0
    
    for(var i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_produto_acabado'+i).checked) {
            //Qtde Retornada ...
            if(document.getElementById('txt_qtde_retornada'+i).value == '') {
                alert('DIGITE A QUANTIDADE RETORNADA !')
                document.getElementById('txt_qtde_retornada'+i).focus()
                return false
            }
            
            var qtde_retornada      = eval(strtofloat(document.getElementById('txt_qtde_retornada'+i).value))
            var entrada_antecipada  = eval(strtofloat(document.getElementById('hdd_entrada_antecipada'+i).value))

            if(qtde_retornada > entrada_antecipada) {
                alert('QTDE RETORNADA INVÁLIDA !!!\n\nQTDE RETORNADA MAIOR DO QUE A ENTRADA ANTECIPADA !')
                document.getElementById('txt_qtde_retornada'+i).focus()
                document.getElementById('txt_qtde_retornada'+i).select()
                return false
            }
            total_produtos_acabados_marcados++
        }
    }
    
    if(total_produtos_acabados_marcados == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        //Preparo os campos p/ poder gravar no Banco de Dados ...
        for(var i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_produto_acabado'+i).checked) {
                document.getElementById('txt_qtde_retornada'+i).value = strtofloat(document.getElementById('txt_qtde_retornada'+i).value)
            }
        }
    }
}

function trazer_todos_produtos_acabados() {
    var trazer_todos_produtos_acabados = (document.form.chkt_trazer_todos_produtos_acabados.checked) ? 'S' : ''

    window.location = '<?=$PHP_SELF;?>?id_produto_acabado=<?=$_GET['id_produto_acabado'];?>&chkt_trazer_todos_produtos_acabados='+trazer_todos_produtos_acabados
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************************Controle de Tela*****************************-->
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<!--*************************************************************************-->
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
<?    
    if(!empty($_GET['chkt_trazer_todos_produtos_acabados'])) {
        $condicao           = '';//Deixo essa variável vazia afinal preciso estar trazendo todos os PA(s) do Sistema que estão com Entradas Antecipadas ...
        $checked            = 'checked';
    }else {
        $vetor_pa_atrelados = custos::pas_atrelados($_GET['id_produto_acabado']);
        $id_pas_atrelados   = implode(',', $vetor_pa_atrelados);
        //Trago somente os PA(s) que estão atrelados ao $id_produto_acabado passado por parâmetro ...
        $condicao           = " AND ea.`id_produto_acabado` IN ($id_pas_atrelados) ";
        $checked            = '';
    }

    //Essa query verifica se existe(m) Entradas Antecipadas ...
    $sql = "SELECT ea.`entrada_antecipada`, pa.`id_produto_acabado`, pa.`referencia`, pa.`discriminacao`, u.`sigla` 
            FROM `estoques_acabados` ea  
            INNER JOIN `produtos_acabados` pa ON  pa.`id_produto_acabado` = ea.`id_produto_acabado` 
            INNER JOIN `unidades` u ON u.`id_unidade` = pa.`id_unidade` 
            WHERE ea.`entrada_antecipada` > '0' 
            $condicao 
            ORDER BY pa.`referencia` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {//Não existe nenhuma Entrada Antecipada ...
?>
    <tr class='atencao' align='center'>
        <td colspan='5'>
            <?=$mensagem[1];?>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='5' bgcolor='#FFFFFF'>
            <input type='checkbox' name='chkt_trazer_todos_produtos_acabados' id='chkt_trazer_todos_produtos_acabados' value='S' title='Trazer todos os Produtos Acabados' onclick='trazer_todos_produtos_acabados()' class='checkbox' <?=$checked;?>>
            <label for='chkt_trazer_todos_produtos_acabados'>
                <font color='darkblue' size='2'>
                    <b>Trazer todos os Produtos Acabados</b>
                </font>
            </label>
        </td>
<?
    }else {//Existem Entradas Antecipadas ...
?>
    <tr class='atencao' align='center'>
        <td colspan='5'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            Retorno de Entrada Antecipada
            &nbsp;-
            <input type='checkbox' name='chkt_trazer_todos_produtos_acabados' id='chkt_trazer_todos_produtos_acabados' value='S' title='Trazer todos os Produtos Acabados' onclick='trazer_todos_produtos_acabados()' class='checkbox' <?=$checked;?>>
            <label for='chkt_trazer_todos_produtos_acabados'>
                Trazer todos os Produtos Acabados
            </label>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar_tudo('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' class='checkbox'>
        </td>
        <td>
            Qtde Retornada
        </td>
        <td>
            Qtde Antecipada
        </td>
        <td>
            Referência
        </td>
        <td>
            Discriminação
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_produto_acabado[]' id='chkt_produto_acabado<?=$i;?>' value='<?=$campos[$i]['id_produto_acabado'];?>' onclick="checkbox('form', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?
                //Só existe casas decimais quando a Unidade do PA = Kilo ...
                $onkeyup = ($campos[$i]['sigla'] == 'KG') ? "verifica(this, 'moeda_especial', '2', '0', event)" : "verifica(this, 'aceita', 'numeros', '', event)";
            ?>
            <input type='text' name='txt_qtde_retornada[]' id='txt_qtde_retornada<?=$i;?>' title='Digite a Qtde Retornada' onclick="checkbox('form', '<?=$i;?>', '#E8E8E8');return focos(this)" onkeyup="<?=$onkeyup;?>" size='12' class='textdisabled' disabled>
        </td>
        <td>
            <?=number_format($campos[$i]['entrada_antecipada'], 2, ',', '.');?>
            <!--**********************Controles de Tela**********************-->
            <input type='hidden' name='hdd_entrada_antecipada[]' id='hdd_entrada_antecipada<?=$i;?>' value='<?=number_format($campos[$i]['entrada_antecipada'], 2, ',', '.');?>' disabled>
            <!--*************************************************************-->
        </td>
        <td>
            <?=$campos[$i]['referencia'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['discriminacao'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
<?
    }
?>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</form>
</body>
</html>