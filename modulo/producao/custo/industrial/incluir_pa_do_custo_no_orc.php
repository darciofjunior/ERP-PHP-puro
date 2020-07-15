<?
require('../../../../lib/segurancas.php');
require('../../../../lib/custos.php');
require('../../../../lib/intermodular.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../../');

$mensagem[1] = '<font class="erro">ESTE PRODUTO ACABADO JÁ EXISTENTE PARA ESTE ORÇAMENTO.</font>';
$mensagem[2] = '<font class="confirmacao">PRODUTO ACABADO INCLUÍDO COM SUCESSO PARA O ORC.</font>';
$mensagem[3] = '<font class="erro">ESTE ORÇAMENTO NÃO EXISTE OU ESTÁ CONGELADO.</font>';

if(!empty($_POST['txt_numero_orcamento'])) {
    //Aqui verifico se este Orçamento digitado pelo usuário existe no Sistema e se o mesmo não está Congelado ...
    $sql = "SELECT `id_orcamento_venda` 
            FROM `orcamentos_vendas` 
            WHERE id_orcamento_venda = '$_POST[txt_numero_orcamento]' 
            AND `congelar` = 'N' LIMIT 1 ";
    $campos_orcamento = bancos::sql($sql);
    $linhas_orcamento = count($campos_orcamento);
    if($linhas_orcamento == 0) {//Não existe esse Orçamento ...
        $valor = 3;
    }else {
        //Por aqui significa que esse Orçamento existe e sendo assim verifico se esse PA do Custo já existe nesse ORC ...
        $sql = "SELECT id_orcamento_venda_item 
                FROM `orcamentos_vendas_itens`
                WHERE id_orcamento_venda = '$_POST[txt_numero_orcamento]' 
                AND id_produto_acabado = '$_POST[id_produto_acabado]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Item já existente, não posso incluir novamente ...
            $valor = 1;
        }else {//Esse item não existe, sendo assim busco alguns dados abaixo p/ Incluí-lo ...
            //Busca de alguns dados do PA que serão utilizados + abaixo, independente do caso ...
            $sql = "SELECT gpa.prazo_entrega, ged.id_empresa_divisao, pa.referencia 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    WHERE pa.`id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
            $campos_dados_gerais = bancos::sql($sql);
             //Aqui eu busco o $id_cliente do Orçamento através do $id_orcamento_venda ...
            $sql = "SELECT id_cliente 
                    FROM `orcamentos_vendas` 
                    WHERE `id_orcamento_venda` = '$_POST[txt_numero_orcamento]' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            //Aqui eu busco o Representante do Cliente no Orçamento na Respectiva Empresa Divisão do PA ...
            $sql = "SELECT id_representante 
                    FROM `clientes_vs_representantes` 
                    WHERE `id_cliente` = '".$campos_cliente[0]['id_cliente']."' 
                    AND `id_empresa_divisao` = '".$campos_dados_gerais[0]['id_empresa_divisao']."' LIMIT 1 ";
            $campos_representante = bancos::sql($sql);
            if(count($campos_representante) == 0) {//Não encontrou nenhum Representante na Query acima ...
                exit('REPRESENTANTE NÃO ENCONTRADO, VERIFIQUE SE O REPRESENTANTE ESTE CLIENTE PARA ESTA DIVISÃO !');
            }
            //Gerando item de Orçamento com o PA do Custo passado por parâmetro ...
            $sql = "INSERT INTO `orcamentos_vendas_itens` (`id_orcamento_venda_item`, `id_orcamento_venda`, `id_produto_acabado`, `id_representante`, `qtde`, `desc_cliente`, `prazo_entrega`, `data_sys`) 
                    VALUES (NULL, '$_POST[txt_numero_orcamento]', '$_POST[id_produto_acabado]', '".$campos_representante[0]['id_representante']."',  '$_POST[txt_qtde_pecas]', '0', '".$campos_dados_gerais[0]['prazo_entrega']."', '".date('Y-m-d')."') ";
            bancos::sql($sql);
            $id_orcamento_venda_item = bancos::id_registro();
/*******************************************************************************************************/
            vendas::calculo_preco_liq_final_item_orc($id_orcamento_venda_item, 'S');
            $valor = 2;
        }
    }
}

$id_produto_acabado = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_produto_acabado'] : $_GET['id_produto_acabado'];

$sql = "SELECT referencia, discriminacao 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Incluir Produto Acabado do Custo no Orçamento ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//N.º do Orçamento ...
    if(!texto('form', 'txt_numero_orcamento', '1', '0123456789', 'N.º DO ORÇAMENTO', '2')) {
        return false
    }
//Quantidade de Peças ...
    if(!texto('form', 'txt_qtde_pecas', '1', '0123456789', 'QUANTIDADE DE PEÇAS', '1')) {
        return false
    }
}
</Script>
</head>
<body onload='document.form.txt_numero_orcamento.focus()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Produto Acabado<br/>
            <font color='yellow'>
                "<?=$campos[0]['referencia'].' - '.$campos[0]['discriminacao'];?>"
            </font>
            do Custo no Orçamento
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>N° do Orçamento:</b>
        </td>
        <td>
            <input type='text' name='txt_numero_orcamento' title='Digite o N.º do Orçamento' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_pecas' title='Digite a Qtde de Peças' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_numero_orcamento.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>