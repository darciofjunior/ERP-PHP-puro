<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');

if($_POST['irregularidade'] == 'S') {//Significa que o usuário ainda não preencheu de maneira Correta a Data Final ...
    echo 'DATA(S) INV&Aacute;LIDA(S) !';
}else {
    /*Busca a próxima Data do Holerith, maior do que a Data Final digitada pelo usuário 
    no Filtro e que tenha o campo 'qtde_dias_uteis_mes' preenchida ...*/
    $sql = "SELECT `total_faturamento` 
            FROM `vales_datas` 
            WHERE `data` > '".data::datatodate($_POST['txt_data_final'], '-')."' 
            AND `qtde_dias_uteis_mes` > '0' LIMIT 1 ";
    $campos_data        = bancos::sql($sql);
    $total_faturamento  = (count($campos_data) == 0) ? 0 : $campos_data[0]['total_faturamento'];

    if($total_faturamento == 0) {//Não fechou o Mês, por isso que ainda não foi preenchido o total de Faturamento ...
        echo 'Proje&ccedil;&atilde;o de Faturamento: ';
        
        /*Ainda não foi digitado um valor de "Projeção de Faturamento" pelo usuário, sendo assim então o sistema 
        tenta sugerir no mais aproximado possível o Total que foi Faturado no mês ...*/
        $sql = "SELECT IF(nfs.`status` = '6', (SUM(ROUND((nfsi.`qtde_devolvida` * IF(c.`id_pais` = '31', nfsi.`valor_unitario`, nfsi.`valor_unitario_exp`)), 2)) * (-1)), SUM(ROUND((nfsi.`qtde` * IF(c.`id_pais` = '31', nfsi.`valor_unitario`, nfsi.`valor_unitario_exp`)), 2))) AS total_faturado 
                FROM `nfs_itens` nfsi 
                INNER JOIN `nfs` ON nfs.`id_nf` = nfsi.`id_nf` 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                WHERE nfs.`status` IN (2, 3, 4, 6) 
                AND nfs.`data_emissao` BETWEEN '".data::datatodate($_POST['txt_data_inicial'], '-')."'  AND '".data::datatodate($_POST['txt_data_final'], '-')."' ";
        $campos_total_faturado  = bancos::sql($sql);
        $projecao_faturamento   = number_format($campos_total_faturado[0]['total_faturado'], 2, ',', '.');
?>
        <input type='text' name='txt_projecao_faturamento' value='<?=$projecao_faturamento;?>' title='Digite a Proje&ccedil;&atilde;o de Faturamento' size='9' maxlength='11' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'/>
<?
    }else {
        echo 'Faturamento: '.number_format($total_faturamento, 2, ',', '.');
    }
}
?>
&nbsp;
<input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'/>