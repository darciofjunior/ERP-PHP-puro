<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../../lib/custos.php');
require('../../../../lib/data.php');//Essa biblioteca é requerida dentro do Custo ...
require('../../../../lib/intermodular.php');//Essa biblioteca é requerida dentro da Venda ...
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/orcamentos/itens/consultar.php');

/* Busco dados atuais que estão gravados no "id_orcamento_venda_item" porque estes serão reatribuidos 
novamente + abaixo ...*/
$sql = "SELECT ovi.`id_orcamento_venda`, ovi.`qtde`, ovi.`preco_liq_fat_disc`, ovi.`preco_liq_fat`, ovi.`comissao_new`, 
        ovi.`comissao_extra`, ovi.`preco_liq_final`, ovi.`margem_lucro`, ovi.`margem_lucro_estimada`, pa.`referencia` 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = ovi.`id_produto_acabado` 
        WHERE ovi.`id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' LIMIT 1 ";
$campos                         = bancos::sql($sql);
$id_orcamento_venda             = $campos[0]['id_orcamento_venda'];
$qtde_atual                     = $campos[0]['qtde'];
$preco_liq_fat_disc_atual       = $campos[0]['preco_liq_fat_disc'];
$preco_liq_fat_atual            = $campos[0]['preco_liq_fat'];
$comissao_new_atual             = $campos[0]['comissao_new'];
$comissao_extra_atual           = $campos[0]['comissao_extra'];
$preco_liq_final_atual          = $campos[0]['preco_liq_final'];
$margem_lucro_atual             = $campos[0]['margem_lucro'];
$margem_lucro_estimada_atual    = $campos[0]['margem_lucro_estimada'];
$referencia                     = $campos[0]['referencia'];

/**************************Observação Importantíssima**************************/
/*Eu guardo a nova Quantidade ou o novo Preço digitados no item de Orçamento 
para que possamos calcular a comissão do vendedor de maneira instantânea na 
Tela, sem ter a necessidade de salvarmos primeiro para depois visualizar 
esse valor ...*/
/******************************************************************************/

/*1) Guardo no "id_orcamento_venda_item" passado por parâmetro apenas a "Qtde do Item" ..., faço isso porque os PA´s que são ESP 
tem o seu preço calculado baseado na Qtde do Lote e posteriormente p/ que calculemos a Margem de Lucro Estimada de forma correta, 
conforme função abaixo ...*/
$sql = "UPDATE `orcamentos_vendas_itens` SET `qtde` = '$_GET[qtde]', `preco_liq_final` = '$_GET[preco_liquido_final]' WHERE `id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' LIMIT 1 ";
bancos::sql($sql);


/*Só chamo essa função meio "pesada" quando o PA for ESP e houve mudança na Qtde do Orc porque isso interfere no 
Preço Líq Fat em R$ por Peça ...*/
if($referencia == 'ESP') {
    //Teve um caso que não precisou chamar a função exemplo -> BITS REDONDO MASTERCUT 8 X 200 10%CO - Dia 03/05/2018, preciso ficar atento Dárcio ...

    //vendas::calculo_preco_liq_final_item_orc($_GET[id_orcamento_venda_item], 'S');
    
    //Busco o novo Preço Líquido Final do Item do Orçamento que foi calculado p/ um item ESP através da função acima ...
    $sql = "SELECT `preco_liq_final` 
            FROM `orcamentos_vendas_itens` 
            WHERE `id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $preco_liq_final_esp    = $campos[0]['preco_liq_final'];
}

//2) Gravo a Margem de Lucro Estimada Correta no "id_orcamento_venda_item" passado por parâmetro ...
custos::margem_lucro_estimada($_GET['id_orcamento_venda_item']);

/*3) Essa função além de calcular a Margem de Lucro no "id_orcamento_venda_item", também retorna a Nova comissão 
do Representante ...*/
$comissao_new   = vendas::calculo_ml_comissao_item_orc($id_orcamento_venda, $_GET['id_orcamento_venda_item'], 'S');

/*Aqui eu busco a "Nova Comissão Extra" do Item porque a função acima "calculo_ml_comissao_item_orc" 
chama por sua vez a função "nova_comissao_representante" e dentro desta segunda é gravada a Nova Comissão 
Extra de acordo com o caminho que foi seguido lá dentro "Exemplo: Representante Autônomo, 
Externo, Interno", etc ...*/
$sql = "SELECT `comissao_extra` 
        FROM `orcamentos_vendas_itens` 
        WHERE `id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' LIMIT 1 ";
$campos_comissao_extra_new  = bancos::sql($sql);
$comissao_extra_new         = $campos_comissao_extra_new[0]['comissao_extra'];

//4) Aqui é o cálculo da Comissão em R$ ...
$comissao_item  = vendas::comissao_representante_reais($_GET['preco_total_lote'], $comissao_new + $comissao_extra_new);//% em cima daquele Item ...*/
?>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
    var referencia                                                  = '<?=$referencia;?>'
    var preco_liq_final_esp                                         = '<?=number_format($preco_liq_final_esp, 2, ',', '.');?>'
    
    if(referencia == 'ESP') {
        if(eval(strtofloat(preco_liq_final_esp)) > 0) {
            parent.document.form.txt_preco_liquido_final_rs.value       = preco_liq_final_esp
        }else {
            parent.document.form.txt_preco_liquido_final_rs.value       = ''
        }
    }

//Aqui eu retorno os valores para o Formulário Principal do Alterar Itens ...
    parent.document.form.txt_porc_comissao.value                    = '<?=number_format($comissao_new + $comissao_extra_new, 2, ',', '.');?>'
    parent.document.form.txt_porc_comissao_rs.value                 = '<?=number_format($comissao_item, 2, ',', '.');?>'
    parent.document.getElementById('div_comissao').style.visibility = 'visible'
    parent.document.getElementById('div_loading').style.visibility  = 'hidden'
</Script>
<?
/*5) Volto no "id_orcamento_venda_item" passado por parâmetro os valores carregados anteriormente que são os 
corretos, se o usuário deseja gravar essa Quantidade, Desconto ou Preço, então ele que clique no Botão Salvar ...*/
$sql = "UPDATE `orcamentos_vendas_itens` SET `qtde` = '$qtde_atual', `preco_liq_fat_disc` = '$preco_liq_fat_disc_atual', `preco_liq_fat` = '$preco_liq_fat_atual', `comissao_new` = '$comissao_new_atual', `comissao_extra` = '$comissao_extra_atual', `preco_liq_final` = '$preco_liq_final_atual', `margem_lucro` = '$margem_lucro_atual', `margem_lucro_estimada` = '$margem_lucro_estimada_atual' WHERE `id_orcamento_venda_item` = '$_GET[id_orcamento_venda_item]' LIMIT 1 ";
bancos::sql($sql);
?>