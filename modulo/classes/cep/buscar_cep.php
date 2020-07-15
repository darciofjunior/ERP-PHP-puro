<?
require('../../../lib/segurancas.php');
require('../../../lib/genericas.php');

if(!empty($_GET['txt_cep'])) {
    $retorno = genericas::buscar_cep($_GET['txt_cep']);
    if($retorno != 0) {//Encontrou o Cep ...
?>
    <Script Language = 'JavaScript'>
        if(typeof(parent.document.form.txt_endereco) != 'undefined') {
            parent.document.form.txt_endereco.value         = '<?=$retorno['logradouro'];?>'
            parent.document.form.txt_bairro.value           = '<?=$retorno['bairro'];?>'
            parent.document.form.txt_cidade.value           = '<?=$retorno['cidade'];?>'
            parent.document.form.txt_estado.value           = '<?=$retorno['uf'];?>'
            parent.document.form.txt_ddd_comercial.value    = '<?=$retorno['ddd'];?>'
            //Aqui é um controle para saber se o usuário terá que digitar a rua e o bairro, caso o cep não retornar os mesmos ...
            var endereco    = '<?=$retorno['logradouro'];?>'
            var bairro      = '<?=$retorno['bairro'];?>'
            if(endereco == '' || bairro == '') {
                //Trocando o Layout p/ Habilitado ...
                parent.document.form.txt_endereco.className = 'caixadetexto'
                parent.document.form.txt_bairro.className   = 'caixadetexto'
                //Habilitando os objetos ...
                parent.document.form.txt_endereco.disabled  = false
                parent.document.form.txt_bairro.disabled    = false
                parent.document.form.txt_endereco.focus()
            }else {
                //Trocando o Layout p/ Desabilitado ...
                parent.document.form.txt_endereco.className = 'textdisabled'
                parent.document.form.txt_bairro.className   = 'textdisabled'
            }
        }else if(typeof(parent.corpo.tela.document.form.txt_endereco) != 'undefined') {
            parent.corpo.tela.document.form.txt_endereco.value      = '<?=$retorno['logradouro'];?>'
            parent.corpo.tela.document.form.txt_bairro.value        = '<?=$retorno['bairro'];?>'
            parent.corpo.tela.document.form.txt_cidade.value        = '<?=$retorno['cidade'];?>'
            parent.corpo.tela.document.form.txt_estado.value        = '<?=$retorno['uf'];?>'
            parent.corpo.tela.document.form.txt_ddd_comercial.value = '<?=$retorno['ddd'];?>'
            //Aqui é um controle para saber se o usuário terá que digitar a rua e o bairro, caso o cep não retornar os mesmos ...
            var endereco    = '<?=$retorno['logradouro'];?>'
            var bairro      = '<?=$retorno['bairro'];?>'
            if(endereco == '' || bairro == '') {
                //Trocando a Cor da Letra para Habilitado ...
                parent.corpo.tela.document.form.txt_endereco.className   = 'caixadetexto'
                parent.corpo.tela.document.form.txt_bairro.className     = 'caixadetexto'
                //Habilitando os objetos ...
                parent.corpo.tela.document.form.txt_endereco.disabled    = false
                parent.corpo.tela.document.form.txt_bairro.disabled      = false
                parent.corpo.tela.document.form.txt_endereco.focus()
            }else {
                //Trocando o Layout p/ Desabilitado ...
                parent.corpo.tela.document.form.txt_endereco.className   = 'textdisabled'
                parent.corpo.tela.document.form.txt_bairro.className     = 'textdisabled'
            }
        }
    </Script>
<?
    }else {//Se não encontrar abre uma Janela para o Usuário consultar o Cep no Site dos Correios ...
?>
    <Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
    <Script Language = 'JavaScript'>
        //nova_janela('cep_index.php?cep=<?=$_GET['txt_cep'];?>', 'POP', '', '', '', '', 550, 780, 'c', 'c')
        alert('CEP NÃO EXISTENTE !')
    </Script>
<?
    }
}

if(!empty($_GET['txt_cep_cobranca'])) {
    $retorno = genericas::buscar_cep($_GET['txt_cep_cobranca']);
    if($retorno != 0) {//Encontrou o Cep ...
?>
    <Script Language = 'JavaScript'>
        if(typeof(parent.document.form.txt_endereco_cobranca) != 'undefined') {
            parent.document.form.txt_endereco_cobranca.value    = '<?=$retorno['logradouro'];?>'
            parent.document.form.txt_bairro_cobranca.value      = '<?=$retorno['bairro'];?>'
            parent.document.form.txt_cidade_cobranca.value      = '<?=$retorno['cidade'];?>'
            parent.document.form.txt_estado_cobranca.value      = '<?=$retorno['uf'];?>'
            //Aqui é um controle para saber se o usuário terá que digitar a rua e o bairro, caso o cep não retornar os mesmos ...
            var endereco_cobranca   = '<?=$retorno['logradouro'];?>'
            var bairro_cobranca     = '<?=$retorno['bairro'];?>'
            if(endereco_cobranca == '' || bairro_cobranca == '') {
                //Trocando o Layout p/ Habilitado ...
                parent.document.form.txt_endereco_cobranca.className = 'caixadetexto'
                parent.document.form.txt_bairro_cobranca.className   = 'caixadetexto'
                //Habilitando os objetos ...
                parent.document.form.txt_endereco_cobranca.disabled  = false
                parent.document.form.txt_bairro_cobranca.disabled    = false
                parent.document.form.txt_endereco_cobranca.focus()
            }else {
                //Trocando o Layout p/ Desabilitado ...
                parent.document.form.txt_endereco_cobranca.className = 'textdisabled'
                parent.document.form.txt_bairro_cobranca.className   = 'textdisabled'
            }
        }else if(typeof(parent.corpo.tela.document.form.txt_endereco_cobranca) != 'undefined') {
            parent.corpo.tela.document.form.txt_endereco_cobranca.value = '<?=$retorno['logradouro'];?>'
            parent.corpo.tela.document.form.txt_bairro_cobranca.value   = '<?=$retorno['bairro'];?>'
            parent.corpo.tela.document.form.txt_cidade_cobranca.value   = '<?=$retorno['cidade'];?>'
            parent.corpo.tela.document.form.txt_estado_cobranca.value   = '<?=$retorno['uf'];?>'
            //Aqui é um controle para saber se o usuário terá que digitar a rua e o bairro, caso o cep não retornar os mesmos ...
            var endereco_cobranca   = '<?=$retorno['logradouro'];?>'
            var bairro_cobranca     = '<?=$retorno['bairro'];?>'
            if(endereco_cobranca == '' || bairro_cobranca == '') {
                //Trocando a Cor da Letra para Habilitado ...
                parent.corpo.tela.document.form.txt_endereco_cobranca.className   = 'caixadetexto'
                parent.corpo.tela.document.form.txt_bairro_cobranca.className     = 'caixadetexto'
                //Habilitando os objetos ...
                parent.corpo.tela.document.form.txt_endereco_cobranca.disabled    = false
                parent.corpo.tela.document.form.txt_bairro_cobranca.disabled      = false
                parent.corpo.tela.document.form.txt_endereco_cobranca.focus()
            }else {
                //Trocando o Layout p/ Desabilitado ...
                parent.corpo.tela.document.form.txt_endereco_cobranca.className   = 'textdisabled'
                parent.corpo.tela.document.form.txt_bairro_cobranca.className     = 'textdisabled'
            }
        }
    </Script>
<?
    }else {//Se não encontrar abre uma Janela para o Usuário consultar o Cep no Site dos Correios ...
?>
    <Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
    <Script Language = 'JavaScript'>
        //nova_janela('cep_index.php?cep=<?=$_GET['txt_cep_cobranca'];?>', 'POP', '', '', '', '', 550, 780, 'c', 'c')
        alert('CEP NÃO EXISTENTE !')
    </Script>
<?
    }
}
?>