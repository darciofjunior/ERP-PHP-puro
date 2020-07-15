<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');
?>
<html>
<body>
<form name='form' action='' method="post">
<input type='hidden' name='txt_cep' value='<?=$txt_cep?>'>
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
        window.parent.corpo.tela.document.form.txt_endereco.value = "<?=$logradouro;?>"
        window.parent.corpo.tela.document.form.txt_bairro.value = "<?=$bairro;?>"
        window.parent.corpo.tela.document.form.txt_cidade.value = "<?=$cidade;?>"
        window.parent.corpo.tela.document.form.txt_estado.value = "<?=$estado;?>"
/*Aqui é um controle para saber se o usuário terá que digitar a rua e o bairro,
caso o cep não retornar os mesmos*/
        var endereco = "<?=$logradouro;?>"
        var bairro = "<?=$bairro;?>"
        if(endereco == '' || bairro == '') {
            window.parent.corpo.tela.document.form.txt_endereco.disabled    = false
            window.parent.corpo.tela.document.form.txt_bairro.disabled      = false
            //Trocando a Cor da Letra para Habilitado ...
            window.parent.corpo.tela.document.form.txt_endereco.className   = 'caixadetexto'
            window.parent.corpo.tela.document.form.txt_bairro.className     = 'caixadetexto'
            window.parent.corpo.tela.document.form.txt_endereco.focus()
        }else {
            window.parent.corpo.tela.document.form.txt_endereco.disabled    = true
            window.parent.corpo.tela.document.form.txt_bairro.disabled      = true
            //Trocando o Layout p/ Desabilitado ...
            window.parent.corpo.tela.document.form.txt_endereco.className   = 'textdisabled'
            window.parent.corpo.tela.document.form.txt_bairro.className     = 'textdisabled'
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
?>
