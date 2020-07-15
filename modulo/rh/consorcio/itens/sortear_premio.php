<?
require('../../../../lib/segurancas.php');
segurancas::geral('/erp/albafer/modulo/rh/consorcio/itens/consultar.php', '../../../../');
$mensagem[1] = 'FUNCION�RIO CONTEMPLADO COM SUCESSO !';

if(!empty($_GET['id_consorcio_vs_funcionario'])) {
//Busco o id_consorcio p/ passar por par�metro ...
    $sql = "SELECT id_consorcio 
            FROM `consorcios_vs_funcionarios` 
            WHERE `id_consorcio_vs_funcionario` = '$_GET[id_consorcio_vs_funcionario]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_consorcio   = $campos[0]['id_consorcio'];
//Atualizo o Funcion�rio que foi contemplado ...
    $data_contemplado = date('Y-m-d H:i:s');
    $sql = "UPDATE `consorcios_vs_funcionarios` SET `contemplado` = '1', `data_contemplado` = '$data_contemplado' WHERE `id_consorcio_vs_funcionario` = '$_GET[id_consorcio_vs_funcionario]' LIMIT 1 ";
    bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
    window.opener.parent.itens.document.form.submit()
    alert('<?=$mensagem[1];?>')
    var resposta = confirm('DESEJA SORTEAR OUTRO FUNCION�RIO ?')
    if(resposta == true) {
        window.location = 'sortear_premio.php?id_consorcio=<?=$_GET['id_consorcio'];?>'
    }else {
        window.close()
    }
</Script>
<?
}

/*Aqui eu busco a "Qtde" de Funcion�rios que ainda n�o foram contemplados e que est�o envolvidos no 
Cons�rcio p/ realizar o sorteio ...*/
$sql = "SELECT COUNT(id_funcionario) AS total_funcionarios 
        FROM `consorcios_vs_funcionarios` 
        WHERE `id_consorcio` = '$_GET[id_consorcio]' 
        AND `contemplado` = '0' ORDER BY id_funcionario ";
$campos             = bancos::sql($sql);
$total_funcionarios = $campos[0]['total_funcionarios'];
$ordem_contemplado  = rand(1, $total_funcionarios);
/*Aqui eu busco os "id_s" dos Funcion�rios que ainda n�o foram contemplados e que est�o envolvidos no 
Cons�rcio p/ realizar o sorteio ...*/
$sql = "SELECT id_consorcio_vs_funcionario, id_funcionario 
        FROM `consorcios_vs_funcionarios` 
        WHERE `id_consorcio` = '$_GET[id_consorcio]' 
        AND `contemplado` = '0' ORDER BY id_funcionario ";
$campos = bancos::sql($sql);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('N�O H� FUNCION�RIO(S) � SER(EM) CONTEMPLADO(S) !')
        window.close()
    </Script>
<?
}else {
    for($i = 0; $i < $linhas; $i++) {
/*Quando o �ndice do Loop dos Funcion�rio(s) for igual ao �ndice do funcion�rio sorteado, 
ent�o eu guardo este id de Funcion�rio que � o que vai ser contemplado*/
        if(($i + 1) == $ordem_contemplado) {
            $id_consorcio_vs_funcionario = $campos[$i]['id_consorcio_vs_funcionario'];
            $id_funcionario_sorteado = $campos[$i]['id_funcionario'];
        }
    }
//Busca a nome do Funcion�rio para exibir no JavaScript ...
    $sql = "SELECT nome 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$id_funcionario_sorteado' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<Script Language = 'JavaScript'>
    var resposta = confirm('O FUNCION�RIO SORTEADO FOI "<?=$campos[0]['nome'];?>" !\nDESEJA CONTEMPLAR ESTE FUNCION�RIO ?')
    if(resposta == true) {
        window.location = 'sortear_premio.php?id_consorcio_vs_funcionario=<?=$id_consorcio_vs_funcionario;?>'
    }else {
        var resposta2 = confirm('DESEJA SORTEAR OUTRO FUNCION�RIO DESSE MESMO CONS�RCIO ?')
        if(resposta2 == true) {
            window.location = "sortear_premio.php?id_consorcio=<?=$_GET['id_consorcio'];?>"
        }else {
            window.close()
        }
    }
</Script>
<?}?>