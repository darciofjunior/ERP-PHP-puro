<?
if(strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {
    $sql = "SELECT f.id_funcionario, f.nome 
            FROM `funcionarios` f 
            INNER JOIN `logs_apvs` la ON la.`id_funcionario` = f.`id_funcionario` AND SUBSTRING(`data_ocorrencia`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' 
            GROUP BY f.id_funcionario ORDER BY f.nome ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas == 0) {//Se não existir nenhum APV Registrado, então ...
?>
    <tr>
        <td></td>
    </tr>
    <tr class='atencao' align='center'>
        <td>
            NÃO EXISTE(M) APV(S) REGISTRADO(S) NESSE INTERVALO DE DATA(S).
        </td>
    </tr>
<?
    }else {//Se existir então printa normalmente o Relatório ...
?>
    <tr class='linhacabecalho' align='center'>
        <td>
            Funcionário(s)
        </td>
        <td>
            APV(s) em Aberto
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href = 'detalhes_apv.php?id_funcionario_loop=<?=$campos[$i]['id_funcionario'];?>&data_inicial=<?=$data_inicial;?>&data_final=<?=$data_final;?>' class='html5lightbox'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
        <?
//Busca da Qtde de APV(s) em Aberto do Funcionário dentro do Período Especificado
            $sql = "SELECT COUNT(id_log_apv) AS total_apvs_aberto 
                    FROM `logs_apvs` 
                    WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                    AND SUBSTRING(`data_ocorrencia`, 1, 10) BETWEEN '$data_inicial' AND '$data_final' ";
            $campos_total_apvs_aberto = bancos::sql($sql);
            echo $campos_total_apvs_aberto[0]['total_apvs_aberto'];
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
<?
    }
}
?>
</body>
</html>