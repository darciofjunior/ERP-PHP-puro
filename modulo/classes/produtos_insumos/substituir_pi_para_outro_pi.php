<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');//Essas bibliotecas não são chamadas aqui diretamente e sim dentro da de Produção na linha 28 ...
require('../../../lib/custos.php');//Essas bibliotecas não são chamadas aqui diretamente e sim dentro da de Produção na linha 28 ...
require('../../../lib/estoque_acabado.php');//Essas bibliotecas não são chamadas aqui diretamente e sim dentro da de Produção na linha 28 ...
require('../../../lib/estoque_new.php');//Essas bibliotecas não são chamadas aqui diretamente e sim dentro da de Produção na linha 28 ...
require('../../../lib/intermodular.php');
require('../../../lib/producao.php');
segurancas::geral('/erp/albafer/modulo/compras/estoque_i_c/inventario.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>PA(S) DA(S) ETAPA(S) SUBSTITUÍDO(S) COM SUCESSO DO PI PRINCIPAL P/ O OUTRO PI.</font>";

if($passo == 1) {
//Trocando os PA(s) do PI principal pelo outro PI de Substituição ...
//1ª Etapa
    if(count($_POST['chkt_produto_acabado_etapa1']) > 0) {
        foreach($_POST['chkt_produto_acabado_etapa1'] as $id_produto_acabado_etapa1) {
            $sql = "UPDATE `pas_vs_pis_embs` SET `id_produto_insumo` = '$_POST[cmb_produto_insumo_substituicao]' 
                    WHERE `id_produto_insumo` = '$_POST[id_produto_insumo]' 
                    AND `id_produto_acabado` = '$id_produto_acabado_etapa1' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
//2ª Etapa
    if(count($_POST['chkt_produto_acabado_etapa2']) > 0) {
        foreach($_POST['chkt_produto_acabado_etapa2'] as $id_produto_acabado_etapa2) {
            //Verifico se houve alguma alteração do Produto Insumo "Aço" ...
            if($_POST['id_produto_insumo'] != $_POST['cmb_produto_insumo_substituicao']) {//Se houve alteração então chamo a Função ...
                producao::verificar_ops_com_baixa_nao_finalizadas($id_produto_acabado_etapa2, $_POST['id_produto_insumo'], $_POST['cmb_produto_insumo_substituicao'], 2);
            }
            $sql = "UPDATE `produtos_acabados_custos` SET `id_produto_insumo` = '$_POST[cmb_produto_insumo_substituicao]' 
                    WHERE `id_produto_insumo` = '$_POST[id_produto_insumo]' 
                    AND `id_produto_acabado` = '$id_produto_acabado_etapa2' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
//3ª Etapa
    if(count($_POST['chkt_produto_acabado_etapa3']) > 0) {
        foreach($_POST['chkt_produto_acabado_etapa3'] as $id_produto_acabado_etapa3) {
            $sql = "UPDATE `pacs_vs_pis` pp 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` AND pac.`id_produto_acabado` = '$id_produto_acabado_etapa3' 
                    SET pp.`id_produto_insumo` = '$_POST[cmb_produto_insumo_substituicao]' 
                    WHERE pp.`id_produto_insumo` = '$_POST[id_produto_insumo]' ";
            bancos::sql($sql);
        }
    }
//5ª Etapa
    if(count($_POST['chkt_produto_acabado_etapa5']) > 0) {
        foreach($_POST['chkt_produto_acabado_etapa5'] as $id_produto_acabado_etapa5) {
            $sql = "UPDATE `pacs_vs_pis_trat` ppt 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = ppt.`id_produto_acabado_custo` AND pac.`id_produto_acabado` = '$id_produto_acabado_etapa5' 
                    SET ppt.`id_produto_insumo` = '$_POST[cmb_produto_insumo_substituicao]' 
                    WHERE ppt.`id_produto_insumo` = '$_POST[id_produto_insumo]' ";
            bancos::sql($sql);
        }
    }
//6ª Etapa
    if(count($_POST['chkt_produto_acabado_etapa6']) > 0) {
        foreach($_POST['chkt_produto_acabado_etapa6'] as $id_produto_acabado_etapa6) {
            $sql = "UPDATE `pacs_vs_pis_usis` ppu 
                    INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = ppu.`id_produto_acabado_custo` AND pac.`id_produto_acabado` = '$id_produto_acabado_etapa6' 
                    SET ppu.`id_produto_insumo` = '$_POST[cmb_produto_insumo_substituicao]' 
                    WHERE ppu.`id_produto_insumo` = '$_POST[id_produto_insumo]' ";
            bancos::sql($sql);
        }
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'substituir_pi_para_outro_pi.php?id_produto_insumo=<?=$_POST['id_produto_insumo'];?>&valor=1'
    </Script>
<?
}else {
    $sql = "SELECT id_pedido 
            FROM `itens_pedidos` 
            WHERE `id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
    $campos1 = bancos::sql($sql);
    $linhas1 = count($campos1);

    $sql = "SELECT qtde 
            FROM `estoques_insumos` 
            WHERE `id_produto_insumo` = '$_GET[id_produto_insumo]' LIMIT 1 ";
    $campos2 = bancos::sql($sql);
    $linhas2 = count($campos2);

    $sql = "SELECT g.referencia, pi.discriminacao 
            FROM `produtos_insumos` pi 
            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo 
            WHERE pi.id_produto_insumo = '$_GET[id_produto_insumo]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $referencia     = $campos[0]['referencia'];
    $discriminacao  = $campos[0]['discriminacao'];
?>
<html>
<head>
<title>.:: Substituir PI por outro PI ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox') {
            if (elementos[i].checked == true) valor = true
        }
    }
//Se o Valor = False, forço o usuário a selecionar algum P.A. de alguma Etapa ...
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }
//Mando selecionar o PI no qual vai receber os PA(s) ...
    if(!combo('form', 'cmb_produto_insumo_substituicao', '', 'SELECIONE UM PRODUTO INSUMO P/ SUBSTITUIÇÃO !')) {
        return false
    }
}

function selecionar_todos_checkboxs_etapa(etapa) {
    var elementos = document.form.elements
    
    if(etapa == 1) {
        var checar = (elementos['chkt_selec_todos_checkboxs_etapa1'].checked) ? true : false
    }else if(etapa == 2) {
        var checar = (elementos['chkt_selec_todos_checkboxs_etapa2'].checked) ? true : false
    }else if(etapa == 3) {
        var checar = (elementos['chkt_selec_todos_checkboxs_etapa3'].checked) ? true : false
    }else if(etapa == 5) {
        var checar = (elementos['chkt_selec_todos_checkboxs_etapa5'].checked) ? true : false
    }else if(etapa == 6) {
        var checar = (elementos['chkt_selec_todos_checkboxs_etapa6'].checked) ? true : false
    }

    for(var i = 0; i < elementos.length; i++) elementos[i].checked = checar
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1'?>" onsubmit='return validar()'>
<input type='hidden' name='id_produto_insumo' value='<?=$_GET['id_produto_insumo'];?>'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <font color="yellow">Produto: </font>
                <?=$referencia.' - '.$discriminacao;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Locais Atrelados
        </td>
    </tr>
<?
    if($linhas1 > 0) {
        for($i = 0; $i < $linhas1; $i++) $id_pedidos.= $campos1[$i]['id_pedido'].', ';
        $id_pedidos = substr($id_pedidos, 0, strlen($id_pedidos) - 2);
?>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <b>* Esse produto está atrelado ao(s) Pedido(s) de Compra(s) N.º -> <?=$id_pedidos;?></b>
        </td>
    </tr>
<?
    }
    
    if($campos2[0]['qtde'] > 0) {
?>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <b>* Esse produto está atrelado ao Estoque com Quantidade: <?=number_format($campos2[0]['qtde'], 2, ',', '.');?></b>
        </td>
    </tr>
<?
    }
//Aqui verifica em quais etapas do custo que o PI está sendo utilizado
//1.a Etapa
    $sql = "SELECT pa.`id_produto_acabado`, gpa.`nome` 
            FROM `pas_vs_pis_embs` ppe 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ppe.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE ppe.id_produto_insumo = '$id_produto_insumo' ORDER BY pa.`discriminacao` ";
    $campos_etapas = bancos::sql($sql);
    $linhas_etapas = count($campos_etapas);
    if($linhas_etapas > 0) {
?>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_selec_todos_checkboxs_etapa1' onclick='selecionar_todos_checkboxs_etapa(1)' title='Selecionar todos os Checkbox da Etapa 1' id='checkboxs_etapa1' class='checkbox'>
            <label for='checkboxs_etapa1'>
                Esse produto está atrelado na 1&ordf; Etapa do Custo p/ o(s) seguinte(s) PA(s)
            </label>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas_etapas; $i++) {
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <input type='checkbox' name='chkt_produto_acabado_etapa1[]' value="<?=$campos_etapas[$i]['id_produto_acabado'];?>" id="produto<?=$i;?>" class='checkbox'>
        </td>
        <td>
            <label for='produto<?=$i;?>'>
            <?
                echo intermodular::pa_discriminacao($campos_etapas[$i]['id_produto_acabado'], 0);
                echo '<font color="black"><b> - Grupo PA: </b>'.$campos_etapas[$i]['nome'].'</font>';
            ?>
            </label>
        </td>
    </tr>
<?
        }
    }
//2.a Etapa
    $sql = "SELECT pa.`id_produto_acabado`, gpa.`nome` 
            FROM `produtos_acabados_custos` pac 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pac.`id_produto_insumo` = '$id_produto_insumo' ORDER BY pa.`discriminacao` ";
    $campos_etapas = bancos::sql($sql);
    $linhas_etapas = count($campos_etapas);
    if($linhas_etapas > 0) {
?>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_selec_todos_checkboxs_etapa2' onclick='selecionar_todos_checkboxs_etapa(2)' title='Selecionar todos os Checkbox da Etapa 2' id='checkboxs_etapa2' class='checkbox'>
            <label for='checkboxs_etapa2'>
                Esse produto está atrelado na 2&ordf; Etapa do Custo p/ o(s) seguinte(s) PA(s) 
            </label>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas_etapas; $i++) {
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <input type='checkbox' name='chkt_produto_acabado_etapa2[]' value="<?=$campos_etapas[$i]['id_produto_acabado'];?>" id='produto<?=$i;?>' class='checkbox'>
        </td>
        <td>
            <label for='produto<?=$i;?>'>
            <?
                echo intermodular::pa_discriminacao($campos_etapas[$i]['id_produto_acabado'], 0);
                echo '<font color="black"><b> - Grupo PA: </b>'.$campos_etapas[$i]['nome'].'</font>';
            ?>
            </label>
        </td>
    </tr>
<?
        }
    }
//3.a Etapa
    $sql = "SELECT pa.`id_produto_acabado`, gpa.`nome` 
            FROM `pacs_vs_pis` pp 
            INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = pp.`id_produto_acabado_custo` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE pp.id_produto_insumo = '$id_produto_insumo' ORDER BY pa.`discriminacao` ";
    $campos_etapas = bancos::sql($sql);
    $linhas_etapas = count($campos_etapas);
    if($linhas_etapas > 0) {
?>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_selec_todos_checkboxs_etapa3' onclick='selecionar_todos_checkboxs_etapa(3)' title="Selecionar todos os Checkbox da Etapa 3" id='checkboxs_etapa3' class='checkbox'>
            <label for='checkboxs_etapa3'>
                Esse produto está atrelado na 3&ordf; Etapa do Custo p/ o(s) seguinte(s) PA(s) 
            </label>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas_etapas; $i++) {
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <input type='checkbox' name='chkt_produto_acabado_etapa3[]' value="<?=$campos_etapas[$i]['id_produto_acabado'];?>" id='produto<?=$i;?>' class='checkbox'>
        </td>
        <td>
            <label for='produto<?=$i;?>'>
            <?
                echo intermodular::pa_discriminacao($campos_etapas[$i]['id_produto_acabado'], 0);
                echo '<font color="black"><b> - Grupo PA: </b>'.$campos_etapas[$i]['nome'].'</font>';
            ?>
            </label>
        </td>
    </tr>
<?
        }
    }
//5.a Etapa
    $sql = "SELECT pa.`id_produto_acabado`, gpa.`nome` 
            FROM `pacs_vs_pis_trat` ppt 
            INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = ppt.`id_produto_acabado_custo` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE ppt.id_produto_insumo = '$id_produto_insumo' ORDER BY pa.`discriminacao` ";
    $campos_etapas = bancos::sql($sql);
    $linhas_etapas = count($campos_etapas);
    if($linhas_etapas > 0) {
?>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_selec_todos_checkboxs_etapa5' onclick='selecionar_todos_checkboxs_etapa(5)' title='Selecionar todos os Checkbox da Etapa 5' id='checkboxs_etapa5' class='checkbox'>
            <label for='checkboxs_etapa5'>
                Esse produto está atrelado na 5&ordf; Etapa do Custo p/ o(s) seguinte(s) PA(s) 
            </label>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas_etapas; $i++) {
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <input type='checkbox' name='chkt_produto_acabado_etapa5[]' value="<?=$campos_etapas[$i]['id_produto_acabado'];?>" id='produto<?=$i;?>' class='checkbox'>
        </td>
        <td>
            <label for='produto<?=$i;?>'>
            <?
                echo intermodular::pa_discriminacao($campos_etapas[$i]['id_produto_acabado'], 0);
                echo '<font color="black"><b> - Grupo PA: </b>'.$campos_etapas[$i]['nome'].'</font>';
            ?>
            </label>
        </td>
    </tr>
<?
        }
    }
//6.a Etapa
    $sql = "SELECT pa.`id_produto_acabado`, gpa.`nome` 
            FROM `pacs_vs_pis_usis` ppu 
            INNER JOIN `produtos_acabados_custos` pac ON pac.`id_produto_acabado_custo` = ppu.`id_produto_acabado_custo` 
            INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pac.`id_produto_acabado` 
            INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            WHERE ppu.id_produto_insumo = '$id_produto_insumo' ORDER BY pa.`discriminacao` ";
    $campos_etapas = bancos::sql($sql);
    $linhas_etapas = count($campos_etapas);
    if($linhas_etapas > 0) {
?>
    <tr class='linhacabecalho'>
        <td colspan='2'>
            <input type='checkbox' name='chkt_selec_todos_checkboxs_etapa6' onclick='selecionar_todos_checkboxs_etapa(6)' title='Selecionar todos os Checkbox da Etapa 6' id='checkboxs_etapa6' class='checkbox'>
            <label for='checkboxs_etapa6'>
                Esse produto está atrelado na 6&ordf; Etapa do Custo p/ o(s) seguinte(s) PA(s) 
            </label>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas_etapas; $i++) {
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td align='center'>
            <input type='checkbox' name='chkt_produto_acabado_etapa6[]' value='<?=$campos_etapas[$i]['id_produto_acabado'];?>' id='produto<?=$i;?>' class='checkbox'>
        </td>
        <td>
            <label for='produto<?=$i;?>'>
            <?
                echo intermodular::pa_discriminacao($campos_etapas[$i]['id_produto_acabado'], 0);
                echo '<font color="black"><b> - Grupo PA: </b>'.$campos_etapas[$i]['nome'].'</font>';
            ?>
            </label>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhadestaque'>
        <td colspan='2'>
            Substituir por esse PI:&nbsp; 
            <select name='cmb_produto_insumo_substituicao' title='Selecione o Produto Insumo de Substituição' class='combo'>
            <?
                $sql = "SELECT id_produto_insumo, discriminacao 
                        FROM `produtos_insumos` 
                        WHERE `ativo` = '1' 
                        AND `discriminacao` <> '' ORDER BY discriminacao ";
                echo combos::combo($sql);
            ?>
            </select>
            &nbsp;
            <input type='button' name='cmd_consultar_produto_insumo' value='Consultar Produto Insumo' title='Consultar Produto Insumo' onclick="nova_janela('consultar_produto_insumo.php?id_produto_insumo=<?=$id_produto_insumo;?>', 'CONSULTAR_PI', '', '', '', '', 350, 780, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR')" class='botao'>
            <input type='submit' name='cmd_substituir_pi' value='Substituir PI' title='Substituir PI' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>