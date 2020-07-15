<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
?>
<html>
<body>
<form name='form' action='' method='post'>
<input type='hidden' name='txt_cep' value='<?=$txt_cep?>'>
<input type='hidden' name='txt_cep_corresp' value='<?=$txt_cep_corresp?>'>
</form>
</body>
</html>
<?
if(!empty($txt_cep)) {
    $retorno = genericas::buscar_cep($txt_cep);
    if($retorno != 0) {//Encontrou o Cep
        $logradouro = $retorno['logradouro'];
        $bairro = $retorno['bairro'];
        $cidade = $retorno['cidade'];
        $estado = $retorno['uf'];
?>
    <Script Language = 'JavaScript'>
        window.parent.corpo.document.form.txt_endereco.value = '<?=$logradouro;?>'
        window.parent.corpo.document.form.txt_bairro.value = '<?=$bairro;?>'
        window.parent.corpo.document.form.txt_cidade.value = '<?=$cidade;?>'
        window.parent.corpo.document.form.txt_estado.value = '<?=$estado;?>'
/*Aqui é um controle para saber se o usuário terá que digitar a rua e o bairro,
caso o cep não retornar os mesmos*/
        var endereco = '<?=$logradouro;?>'
        var bairro = '<?=$bairro;?>'
        if(endereco == '' || bairro == '') {
            parent.corpo.document.form.txt_endereco.disabled     = false
            parent.corpo.document.form.txt_bairro.disabled       = false
            parent.corpo.document.form.txt_endereco.className    = 'caixadetexto'
            parent.corpo.document.form.txt_bairro.className      = 'caixadetexto'
            parent.corpo.document.form.txt_endereco.focus()
        }else {
            parent.corpo.document.form.txt_endereco.disabled     = true
            parent.corpo.document.form.txt_bairro.disabled       = true
            parent.corpo.document.form.txt_endereco.className    = 'textdisabled'
            parent.corpo.document.form.txt_bairro.className      = 'textdisabled'
        }
    </Script>
<?
    }else {
?>
    <Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
    <Script Language = 'JavaScript'>
        nova_janela('../../classes/cep/cep_index.php?cep=<?=$txt_cep;?>', 'POP', '', '', '', '', 550, 780, 'c', 'c')
    </Script>
<?
    }
}

if(!empty($txt_cep_corresp)) {
    $retorno = genericas::buscar_cep($txt_cep_corresp);
    if($retorno != 0) {//Encontrou o Cep
        $logradouro_corresp = $retorno['logradouro'];
        $bairro_corresp = $retorno['bairro'];
        $cidade_corresp = $retorno['cidade'];
        $estado_corresp = $retorno['uf'];
?>
    <Script Language = 'JavaScript'>
        window.parent.corpo.document.form.txt_endereco_corresp.value = '<?=$logradouro_corresp;?>'
        window.parent.corpo.document.form.txt_bairro_corresp.value = '<?=$bairro_corresp;?>'
        window.parent.corpo.document.form.txt_cidade_corresp.value = '<?=$cidade_corresp;?>'
        window.parent.corpo.document.form.txt_estado_corresp.value = '<?=$estado_corresp;?>'
        /*Aqui é um controle para saber se o usuário terá que digitar a rua e o bairro,
caso o cep não retornar os mesmos*/
        var endereco_corresp = '<?=$logradouro_corresp;?>'
        var bairro_corresp = '<?=$bairro_corresp;?>'
        if(endereco_corresp == '' || bairro_corresp == '') {
            parent.corpo.document.form.txt_endereco_corresp.disabled    = false
            parent.corpo.document.form.txt_bairro_corresp.disabled      = false
            parent.corpo.document.form.txt_endereco_corresp.className   = 'caixadetexto'
            parent.corpo.document.form.txt_bairro_corresp.className     = 'caixadetexto'
            parent.corpo.document.form.txt_endereco_corresp.focus()
        }else {
            parent.corpo.document.form.txt_endereco_corresp.disabled    = true
            parent.corpo.document.form.txt_bairro_corresp.disabled      = true
            parent.corpo.document.form.txt_endereco_corresp.className   = 'textdisabled'
            parent.corpo.document.form.txt_bairro_corresp.className     = 'textdisabled'
        }
    </Script>
<?
    }else {
?>
    <Script Language = 'JavaScript' Src='../../../js/nova_janela.js'></Script>
    <Script Language = 'JavaScript'>
        nova_janela('../../classes/cep/cep_index.php?cep=<?=$txt_cep_corresp;?>', 'POP', '', '', '', '', 550, 780, 'c', 'c')
    </Script>
<?
    }
}
?>