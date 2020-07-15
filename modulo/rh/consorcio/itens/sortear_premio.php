<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/consorcio/itens/consultar.php', '../../../../');
$mensagem[1] = 'FUNCIONÁRIO CONTEMPLADO COM SUCESSO !';

if(!empty($_GET['id_consorcio_vs_funcionario'])) {
//Busco o id_consorcio p/ passar por parâmetro ...
    $sql = "SELECT id_consorcio 
            FROM `consorcios_vs_funcionarios` 
            WHERE `id_consorcio_vs_funcionario` = '$_GET[id_consorcio_vs_funcionario]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_consorcio   = $campos[0]['id_consorcio'];
//Atualizo o Funcionário que foi contemplado ...
    $data_contemplado = date('Y-m-d H:i:s');
    $sql = "UPDATE `consorcios_vs_funcionarios` SET `contemplado` = '1', `data_contemplado` = '$data_contemplado' WHERE `id_consorcio_vs_funcionario` = '$_GET[id_consorcio_vs_funcionario]' LIMIT 1 ";
    bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
    window.opener.parent.itens.document.form.submit()
    alert('<?=$mensagem[1];?>')
    var resposta = confirm('DESEJA SORTEAR OUTRO FUNCIONÁRIO ?')
    if(resposta == true) {
        window.location = 'sortear_premio.php?id_consorcio=<?=$_GET['id_consorcio'];?>'
    }else {
        window.close()
    }
</Script>
<?
}

/*Aqui eu busco a "Qtde" de Funcionários que ainda não foram contemplados e que estão envolvidos no 
Consórcio p/ realizar o sorteio ...*/
$sql = "SELECT COUNT(id_funcionario) AS total_funcionarios 
        FROM `consorcios_vs_funcionarios` 
        WHERE `id_consorcio` = '$_GET[id_consorcio]' 
        AND `contemplado` = '0' ORDER BY id_funcionario ";
$campos             = bancos::sql($sql);
$total_funcionarios = $campos[0]['total_funcionarios'];
$ordem_contemplado  = rand(1, $total_funcionarios);
/*Aqui eu busco os "id_s" dos Funcionários que ainda não foram contemplados e que estão envolvidos no 
Consórcio p/ realizar o sorteio ...*/
$sql = "SELECT id_consorcio_vs_funcionario, id_funcionario 
        FROM `consorcios_vs_funcionarios` 
        WHERE `id_consorcio` = '$_GET[id_consorcio]' 
        AND `contemplado` = '0' ORDER BY id_funcionario ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('NÃO HÁ FUNCIONÁRIO(S) À SER(EM) CONTEMPLADO(S) !')
        window.close()
    </Script>
<?
}else {
    for($i = 0; $i < $linhas; $i++) {
/*Quando o índice do Loop dos Funcionário(s) for igual ao índice do funcionário sorteado, 
então eu guardo este id de Funcionário que é o que vai ser contemplado*/
        if(($i + 1) == $ordem_contemplado) {
            $id_consorcio_vs_funcionario = $campos[$i]['id_consorcio_vs_funcionario'];
            $id_funcionario_sorteado = $campos[$i]['id_funcionario'];
        }
    }
//Busca a nome do Funcionário para exibir no JavaScript ...
    $sql = "SELECT nome 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$id_funcionario_sorteado' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
    var resposta = confirm('O FUNCIONÁRIO SORTEADO FOI "<?=$campos[0]['nome'];?>" !\nDESEJA CONTEMPLAR ESTE FUNCIONÁRIO ?')
    if(resposta == true) {
        window.location = 'sortear_premio.php?id_consorcio_vs_funcionario=<?=$id_consorcio_vs_funcionario;?>'
    }else {
        var resposta2 = confirm('DESEJA SORTEAR OUTRO FUNCIONÁRIO DESSE MESMO CONSÓRCIO ?')
        if(resposta2 == true) {
            window.location = "sortear_premio.php?id_consorcio=<?=$_GET['id_consorcio'];?>"
        }else {
            window.close()
        }
    }
</Script>
<?}?>