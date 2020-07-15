<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/comunicacao.php');
require('../../../../lib/data.php');
require('../../../../lib/depto_pessoal.php');
require('../../../../lib/genericas.php');
require('../../../../lib/variaveis/intermodular.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = 'VALE EXCLUIDO COM SUCESSO !';
$mensagem[2] = 'VALE(S) DESCONTADO(S) COM SUCESSO !';
$mensagem[3] = 'ESTE VALE NÃO PODE SER ALTERADO !\nESTE VALE JÁ FOI DESCONTADO !!!';
$mensagem[4] = 'ESTE VALE NÃO PODE SER EXCLUÍDO !\nESTE VALE JÁ FOI DESCONTADO !!!';

$vetor_tipos_vale = depto_pessoal::tipos_vale();

if($passo == 1) {
    $vetor_vales_dps = explode(',', $_GET['id_vales_dps']);
    $data_ocorrencia = date('Y-m-d H:i:s');
    
//Só não irá enviar esse e-mail quando for a própria Dona Sandra 66 que estiver excluindo o(s) Vale(s) ...
    if($_SESSION['id_funcionario'] != 66) {
//Se tiver a Data de Vencimento for alterada, então precisa ser modificada a Justificativa ...
        if(!empty($_GET['hdd_justificativa'])) {
//1)
/************************Busca de Dados************************/
            foreach($vetor_vales_dps as $id_vale_dp) {
//Aqui eu trago alguns dados de Vale p/ passar por e-mail via parâmetro ...
                $sql = "SELECT e.nomefantasia, f.nome, vd.* 
                        FROM `vales_dps` vd 
                        INNER JOIN `funcionarios` f ON f.`id_funcionario` = vd.`id_funcionario` 
                        INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` AND e.`id_empresa` 
                        WHERE vd.`id_vale_dp` = '$id_vale_dp' LIMIT 1 ";
                $campos         = bancos::sql($sql);
                $empresa        = $campos[0]['nomefantasia'];
                $funcionario 	= $campos[0]['nome'];
                $tipo_vale      = $campos[0]['tipo_vale'];
                $valor          = $campos[0]['valor'];
                $data_holerith 	= data::datetodata($campos[0]['data_debito'], '/');
                $data_emissao 	= data::datetodata($campos[0]['data_emissao'], '/');

                $conteudo_email.= '<br/><b>Empresa: </b>'.$empresa.' <br/><b>Funcionário: </b>'.$funcionario.' <br/><b>Tipo de Vale: </b>'.$vetor_tipos_vale[$tipo_vale].'<br/><b>Data de Holerith: </b>'.$data_holerith.'<br/><b>Data de Emissão: </b>'.$data_emissao.'<br/><b>Valor do Vale: </b>'.number_format($valor, 2, ',', '.').'<br/>';
            }
//2)
/************************E-mail************************/
/*
//-Se o Usuário estiver excluindo o Vale do RH, então o Sistema dispara um e-mail informando qual o Vale 
que está sendo excluído ...*/
            
//Também busco o login de quem está excluindo o Vale ...
            $sql = "SELECT `login` 
                    FROM `logins` 
                    WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
            $campos_login       = bancos::sql($sql);
            $login_excluindo    = $campos_login[0]['login'];

            //Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
            $destino            = $excluir_vales;
            $assunto            = 'Exclusão de Vale(s)';

            $conteudo_email.= '<br/><b>Justificativa: </b>'.$_GET['hdd_justificativa'].'<br/><b>Excluído por: </b>'.$login_excluindo.' em '.date('d/m/Y').' às '.date('H:i:s');
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', $assunto, $conteudo_email);
        }
    }
//3)
/************************Exclusão************************/
    foreach($vetor_vales_dps as $id_vale_dp) {
        //Excluindo o Vale ...
        $sql = "UPDATE `vales_dps` SET `ativo` = '0' WHERE `id_vale_dp` = '$id_vale_dp' LIMIT 1 ";
        bancos::sql($sql);
    }
?>
    <Script Language = 'JavaScript'>
        window.location = 'itens.php<?=$parametro;?>&valor=1'
    </Script>
<?
//Aqui nesse passo eu faço uma marcação de todos os Vale(s)
}else if($passo == 2) {
//Só não irá enviar esse e-mail quando for o Roberto 62 e Sandra 66 que estiver(em) descontado o(s) Vale(s) ...
    if($_SESSION['id_funcionario'] != 62 || $_SESSION['id_funcionario'] != 66) {
//Se tiver a Data de Vencimento for alterada, então precisa ser modificada a Justificativa ...
        if(!empty($_GET['hdd_justificativa'])) {
//1)
/************************Busca de Dados************************/
            $data_ocorrencia = date('Y-m-d H:i:s');
//Aqui eu trago alguns dados de Vale p/ passar por e-mail via parâmetro ...
            $sql = "SELECT e.`nomefantasia`, f.`nome`, vd.* 
                    FROM `vales_dps` vd 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = vd.`id_funcionario` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` AND e.`id_empresa` 
                    WHERE vd.`id_vale_dp` IN ($id_vales_dps) ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
//Disparo do Loop ...
            for($i = 0; $i < $linhas; $i++) {
                $empresa        = $campos[$i]['nomefantasia'];
                $funcionario    = $campos[$i]['nome'];
                $tipo_vale      = $campos[$i]['tipo_vale'];
                $valor          = $campos[$i]['valor'];
                $data_holerith  = data::datetodata($campos[$i]['data_debito'], '/');
                $data_emissao   = data::datetodata($campos[$i]['data_emissao'], '/');
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
                $complemento_justificativa.= '<br/><b>Empresa: </b>'.$empresa.' <br/><b>Funcionário: </b>'.$funcionario.' <br/><b>Tipo de Vale: </b>'.$vetor_tipos_vale[$tipo_vale].'<br/><b>Data de Holerith: </b>'.$data_holerith.'<br/><b>Data de Emissão: </b>'.$data_emissao.'<br/><b>Valor do Vale: </b>'.number_format($valor, 2, ',', '.').'<br/>';
            }
//2)
/************************E-mail************************/
/*
//-Se o Usuário estiver descontando o Vale do RH, então o Sistema dispara um e-mail 
informando qual o Vale que está sendo excluído ...
//-Aqui eu trago alguns dados do Vale p/ passar por e-mail via parâmetro ...
//-Aqui eu busco o login de quem está descontando o Vale ...*/
            $sql = "SELECT `login` 
                    FROM `logins` 
                    WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
            $campos_login       = bancos::sql($sql);
            $login_descontando  = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
            $conteudo_email = '<font color="blue">Vale(s) que estão sendo descontados antes que a Data do Holerith: </font><br/>';
            $conteudo_email.= $complemento_justificativa.'<br/><b>Login: </b>'.$login_descontando.' - '.date('d/m/Y H:i:s').'<br/><b>Justificativa: </b>'.$hdd_justificativa;
//Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
            $destino    = $vales;
            $copia      = $vales_copia;
            $assunto    = 'Vale(s) Descontado(s) antes da Data do Holerith';
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, $assunto, $conteudo_email, $copia);
        }
    }
//3)
/************************Descontando************************/
//Descontando o Vale ...
    $sql = "UPDATE `vales_dps` SET `descontado` = 'S' WHERE `id_vale_dp` IN ($id_vales_dps) ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'itens.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
    /*******************Controle de Link que altera o Vale em PD ou PF*******************/
//Significa que o usuário resolveu alterar a forma de Desconto do Vale ...
    if(!empty($_GET['descontar_pd_pf_futuro'])) {
        $sql = "UPDATE `vales_dps` SET `descontar_pd_pf`= '$_GET[descontar_pd_pf_futuro]' WHERE `id_vale_dp` = '$_GET[id_vale_dp]' LIMIT 1 ";
        bancos::sql($sql);
    }
    /************************************************************************************/
/*Somente na primeira vez em que eu entrar nessa tela, irá executar essa rotina até o usuário 
não recarregar "submeter" a Tela ...*/
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_funcionario                = $_POST['txt_funcionario'];
        $cmb_data_holerith              = $_POST['cmb_data_holerith'];
        $cmb_empresa                    = $_POST['cmb_empresa'];
        $cmb_tipo_vale                  = $_POST['cmb_tipo_vale'];
        $txt_financeira                 = $_POST['txt_financeira'];
        $cmb_descontar_pd_pf            = $_POST['cmb_descontar_pd_pf'];
        $txt_observacao                 = $_POST['txt_observacao'];
        $chkt_somente_nao_descontado    = $_POST['chkt_somente_nao_descontado'];
        $chkt_somente_vales_zerados     = $_POST['chkt_somente_vales_zerados'];
        $chkt_data_holerith_vencida     = $_POST['chkt_data_holerith_vencida'];
        $chkt_mostrar_vales_excluidos   = $_POST['chkt_mostrar_vales_excluidos'];
    }else {
        $txt_funcionario                = $_GET['txt_funcionario'];
        $cmb_data_holerith              = $_GET['cmb_data_holerith'];
        $cmb_empresa                    = $_GET['cmb_empresa'];
        $cmb_tipo_vale                  = $_GET['cmb_tipo_vale'];
        $txt_financeira                 = $_GET['txt_financeira'];
        $cmb_descontar_pd_pf            = $_GET['cmb_descontar_pd_pf'];
        $txt_observacao                 = $_GET['txt_observacao'];
        $chkt_somente_nao_descontado    = $_GET['chkt_somente_nao_descontado'];
        $chkt_somente_vales_zerados     = $_GET['chkt_somente_vales_zerados'];
        $chkt_data_holerith_vencida     = $_GET['chkt_data_holerith_vencida'];
        $chkt_mostrar_vales_excluidos   = $_GET['chkt_mostrar_vales_excluidos'];
    }
    
    $condicao_vales_excluidos = (!empty($chkt_mostrar_vales_excluidos)) ? " AND vd.`ativo` IN (0, 1) " : " AND vd.`ativo` = '1' ";
    
//Aqui eu exibo somente os Vales que não foram descontados e que a Data de Holerith seja menor que a Data Atual ...
    if(!empty($chkt_data_holerith_vencida)) {
        $data_atual = date('Y-m-d');
        $sql = "SELECT vd.* 
                FROM `vales_dps` vd 
                INNER JOIN `funcionarios` f ON f.`id_funcionario` = vd.`id_funcionario` 
                WHERE vd.`descontado` = 'N' 
                AND vd.`data_debito` < '$data_atual' 
                $condicao_vales_excluidos ORDER BY vd.`data_debito`, f.`nome`, vd.`tipo_vale` ";
    }else {
        if(empty($cmb_data_holerith))   $cmb_data_holerith = '%';
        if(empty($cmb_empresa))         $cmb_empresa = '%';
        if(empty($cmb_tipo_vale))       $cmb_tipo_vale = '%';
        if(empty($cmb_descontar_pd_pf)) $cmb_descontar_pd_pf = '%';
//Exibirá somente os Vales que ainda não foram Descontados ...
        if(!empty($chkt_somente_nao_descontado))    $condicao = " AND `descontado` = 'N' ";
//Exibirá somente os Vales Liberados em que o Valor for igual a 0
        if(!empty($chkt_somente_vales_zerados))     $condicao2 = " AND `valor` = '0.00' ";
//Aqui eu exibo os Vales ...
        $sql = "SELECT vd.* 
                FROM `vales_dps` vd 
                INNER JOIN `funcionarios` f ON f.`id_funcionario` = vd.`id_funcionario` AND f.`nome` LIKE '%$txt_funcionario%' 
                INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` AND e.`id_empresa` LIKE '$cmb_empresa' 
                WHERE vd.`data_debito` LIKE '$cmb_data_holerith' 
                AND vd.`tipo_vale` LIKE '$cmb_tipo_vale' 
                AND vd.`financeira` LIKE '%$txt_financeira%' 
                AND vd.`descontar_pd_pf` LIKE '$cmb_descontar_pd_pf' 
                AND vd.`observacao` LIKE '%$txt_observacao%' $condicao $condicao2 
                $condicao_vales_excluidos ORDER BY vd.`data_debito`, f.`nome`, vd.`tipo_vale` ";
    }
//Nas demais vezes em que o usuário recarregou "submeteu" a Tela ...
    $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
    $linhas = count($campos);
    /*********************************************************************/
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'consultar.php?valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Gerenciar Vales ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function alterar_vale(tipo_vale, id_vale_dp, cmb_data_holerith) { 
    if(tipo_vale == 4 || tipo_vale == 14) {//Não existe alterar Vale quando este é do Tipo Consórcio ou Crédito Consignado ...
        alert('OPÇÃO INVÁLIDA !!!\nNÃO EXISTE ALTERAR P/ ESTE TIPO DE VALE !')
    }else {
        if(tipo_vale == 12) {
            var mes = cmb_data_holerith.substr(5, 2)
//Se o Mês = Abril, então eu direciono p/ estar colhendo o Imposto Sindical de Todos os Funcionários ...
            if(mes == 4 || mes == '04') {
                url_imposto_sindical = '../imposto_sindical/incluir_alterar.php'
            }else {
//Colhe o Imposto Sindical de forma Unitária, ou seja de apenas 1 func
                url_imposto_sindical = '../imposto_sindical/alterar_unitario.php'
            }
        }else {
            url_imposto_sindical = ''//Para não dar pau de JavaScript ...
        }
//Criei esse vetor de telas p/ facilitar na hora de alterar o vale ...
        var urls = new Array('', '../vales_dia20/alterar.php', '../avulso/alterar.php', '../combustivel/incluir_alterar.php', '', '../convenio_medico/incluir_alterar.php', '../convenio_odonto/incluir_alterar.php', '../transporte/alterar.php', '../emprestimo/alterar.php', '../celular/incluir_alterar.php', '../mensalidade_sindical/incluir_alterar.php', '../contribuicao_confederativa/incluir_alterar.php', url_imposto_sindical, '../contribuicao_assistencial/incluir_alterar.php', '', '../mensalidade_metalcred/incluir_alterar.php')
        nova_janela(urls[tipo_vale]+'?id_vale_dp='+id_vale_dp+'&cmb_data_holerith='+cmb_data_holerith, 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function descontar_vale() {
    var id_funcionario  = '<?=$_SESSION['id_funcionario'];?>'//Login do Usuário q está logado no Sistema ...
    var id_vales_dps    = ''
//Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox  = 0
    var elementos = document.form.elements
    var data_atual_maior_q_holerith = '<?=$data_atual_maior_q_holerith;?>'
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(elementos[i].checked == true) {
                id_vales_dps+= elementos[i].value + ', '
                checkbox ++
            }
        }
    }
    id_vales_dps = id_vales_dps.substr(0, id_vales_dps.length - 2)

    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        var resposta = confirm('VOCÊ JÁ GEROU VALE(S) NEGATIVO(S) DO MÊS ANTERIOR ?')
        if(resposta == false) {
            return false
        }else {
            var resposta2 = confirm('TEM CERTEZA DE QUE DESEJA DESCONTAR ESSE(S) VALE(S) ?')
            if(resposta2 == false) {
                return false
            }else {//Se sim, então ...
//Só não irá enviar esse e-mail quando for o Roberto e a Dona Sandra que estiver descontando o Vale ...
                if(id_funcionario != 62 && id_funcionario != 66) {
/*Se existir algum vale em que a Data Atual é Menor que a Data do Holerith, então eu forço o usuário
a preencher uma Justificativa p/ mandar por e-mail ...*/
                    if(data_atual_maior_q_holerith > 0) {
                        var justificativa = prompt('EXISTE(M) VALE(S) QUE ESTÃO SENDO DESCONTADO(S) ANTES QUE A DATA DO HOLERITH !\nDIGITE UMA JUSTIFICATIVA P/ DESCONTAR ESSE(S) VALE(S): ')
                        document.form.hdd_justificativa.value = justificativa
//Controle com a Justificativa ...
                        if(document.form.hdd_justificativa.value == '' || document.form.hdd_justificativa.value == 'null' || document.form.hdd_justificativa.value == 'undefined') {
                            alert('JUSTIFICATIVA INVÁLIDA !!!\nDIGITE UMA JUSTIFICATIVA P/ DESCONTAR O VALE !')
                            return false
                        }
                        var hdd_justificativa = document.form.hdd_justificativa.value
                        window.location = 'itens.php?passo=2&hdd_justificativa='+hdd_justificativa+'&id_vales_dps='+id_vales_dps
                    }else {
                        window.location = 'itens.php?passo=2&id_vales_dps='+id_vales_dps
                    }
                }else {
                    window.location = 'itens.php?passo=2&id_vales_dps='+id_vales_dps
                }
            }
        }
    }
}

function excluir_vale() {
    var id_vales_dps = ''
//Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox  = 0
    var elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(elementos[i].checked == true) {
                id_vales_dps+= elementos[i].value + ', '
                checkbox ++
            }
        }
    }
    id_vales_dps = id_vales_dps.substr(0, id_vales_dps.length - 2)
    
    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        var id_funcionario = '<?=$_SESSION['id_funcionario'];?>'//Login do Usuário q está logado no Sistema ...
        
        if(checkbox == 1) {//Foi selecionado um único checkbox ...
            var resposta = confirm('CONFIRMA A EXCLUSÃO ?')
        }else {//Foram selecionados 2 ou mais checkboxs ...
            var resposta = confirm('VOCÊ ESTÁ EXCLUINDO MAIS DE UM VALE !!!\n\nCONFIRMA A EXCLUSÃO PARA MAIS DE UM VALE ?')
        }
        
        if(resposta == true) {
//Só não irá enviar esse e-mail quando for o Roberto e a Dona Sandra que estiver excluindo o Vale ...
            if(id_funcionario != 62 && id_funcionario != 66) {
//Verifico se a Data de Vencimento foi alterada pelo usuário ...
                var justificativa = prompt('DIGITE UMA JUSTIFICATIVA P/ EXCLUIR O VALE: ')
                document.form.hdd_justificativa.value = justificativa
//Controle com a Justificativa ...
                if(document.form.hdd_justificativa.value == '' || document.form.hdd_justificativa.value == 'null' || document.form.hdd_justificativa.value == 'undefined') {
                    alert('JUSTIFICATIVA INVÁLIDA !!!\nDIGITE UMA JUSTIFICATIVA P/ EXCLUIR O(S) VALE(S) !')
                    return false
                }
                var hdd_justificativa = document.form.hdd_justificativa.value
                window.location = 'itens.php?passo=1&hdd_justificativa='+hdd_justificativa+'&id_vales_dps='+id_vales_dps
            }else {
                window.location = 'itens.php?passo=1&id_vales_dps='+id_vales_dps
            }
        }else {
            return false
        }
    }
}

function relatorio_vale() {
//Pop-UP = 1, significa que esta tela está sendo aberta como Pop-Up e por que eu passo esse parâmetro ...
    nova_janela('relatorios/relatorio_func/consultar.php?pop_up=1', 'POP', '', '', '', '', 180, 700, 'c', 'c', '', '', 's', 's', '', '', '')
}

function imprimir_vale() {
    var id_vales_dps = ''
//Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox  = 0
    var elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(elementos[i].checked == true) {
                id_vales_dps+= elementos[i].value + ', '
                checkbox ++
            }
        }
    }
    id_vales_dps = id_vales_dps.substr(0, id_vales_dps.length - 2)
    
    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        nova_janela('relatorios/relatorio_vale/relatorio.php?id_vales_dps='+id_vales_dps, 'CONSULTAR', 'F')
    }
}

//Aqui eu já passo por parâmetro a futura forma de Desconto do Vale ...
function alterar_forma_desconto(descontar_pd_pf_futuro, id_vale_dp) {
    var resposta = confirm('QUER MUDAR O TIPO DE VALE ?')
    if(resposta == true) window.location = '<?=$PHP_SELF.$parametro;?>&descontar_pd_pf_futuro='+descontar_pd_pf_futuro+'&id_vale_dp='+id_vale_dp
}

//Toda vez que fechar as Telas, chama essa função p/ não perder os parâmetros de Filtro ...
function recarregar_tela() {
    window.location = '<?=$PHP_SELF.$parametro;?>'
}
</Script>
</head>
<body>
<form name='form'>
<table width='90%' border='1' cellspacing='0' cellpadding='0' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Gerenciar Vale(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CECECE'>
            <input type='checkbox' name='chkt_tudo' title='Marcar Tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td bgcolor='#CECECE'>
            <b>Funcionário</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Empresa</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Tipo de Vale</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Observação</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Vlr</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Data de Holerith</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Data de Emissão</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Descontar</b>
        </td>
        <td bgcolor='#CECECE'>
            <b>Descontado</b>
        </td>
        <td bgcolor='#CECECE' colspan='2'>
            -
        </td>
    </tr>
<?
//Variável q vai servir p/ controlar se existe alguma Data Atual Menor que a Data de Holerith ...
		$data_atual_maior_q_holerith = 0;
//Listando os vales ...
                for ($i = 0; $i < $linhas; $i++) {
/*Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
e o parâmetro pop_up significa que está tela está sendo aberta como pop_up e sendo assim é para não exibir
o botão de Voltar que existe nessa tela*/
			$url = "javascript:nova_janela('../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=".$campos[$i]['id_funcionario']."&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
/*******************Controle de Cores*******************/
//Aqui eu verifico se o Valor Liberado = 0, caso seje então eu printo a linha em vermelho ...
			if($campos[$i]['valor'] == 0) {
                            $color          = "color='#FF0000'";
                            $color_link     = 'red';
			}else {
//Se o Valor acima do Limite for Maior do que Zero, mostrar em Vermelho também ... 
                            if($campos[$i]['valor_acima_limite'] > 0) {
                                $color      = "color='#FF0000'";
                                $color_link = 'red';
                            }else {
                                $color      = '';
                                $color_link = '';
                            }
			}
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');document.form.chkt_tudo.checked = false" onmouseover="return sobre_celula(this, '#CCFFCC')" onmouseout="return fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?
                if($campos[$i]['ativo'] == 1) {//Vale Ativo ...
                    if($campos[$i]['descontado'] == 'S') {//Vale Descontado, esse não pode ser excluído ...
            ?>
                <font onclick="javascript:alert('<?=$mensagem[3];?>')" style='cursor:help'>
                    <b>&nbsp;-&nbsp;</b>
                </font>
            <?
                    }else {//Vale pode ser excluído normalmente ...
            ?>
            <input type='checkbox' name='chkt_vale_dp[]' value="<?=$campos[$i]['id_vale_dp'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');document.form.chkt_tudo.checked = false" class='checkbox'>
            <?
                    }
                }else {//Vale Excluído ...
                    echo '<font color="red" title="Vale Excluído" style="cursor:help"><b>EXCL</b></font>';
                }
            ?>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                $sql = "SELECT id_empresa, nome 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
                $campos_dados = bancos::sql($sql);
            ?>
                <a href="#" onclick="<?=$url;?>" title='Detalhes Funcionário' class='link'>
                    <font color="<?=$color_link;?>">
                        <?=$campos_dados[0]['nome'];?>
                    </font>
                </a>
            </font>
        </td>
        <td align='left'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=genericas::nome_empresa($campos_dados[0]['id_empresa']);?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=$vetor_tipos_vale[$campos[$i]['tipo_vale']];?>
            </font>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['financeira'])) echo $campos[$i]['financeira'].' - ';
            if(!empty($campos[$i]['parcelamento'])) echo 'Parcela '.$campos[$i]['parcelamento'].'. ';
            echo $campos[$i]['observacao'];
        ?>
            &nbsp;
        </td>
        <td align='right' title="Valor Acima do Limite <?=number_format($campos[$i]['valor_acima_limite'], 2, ',', '.');?>" style='cursor:help'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?='R$ '.number_format($campos[$i]['valor'], 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=data::datetodata($campos[$i]['data_debito'], '/');?>
            </font>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
            </font>
        </td>
        <td>
        <?
            $descontar_pd_pf_futuro = ($campos[$i]['descontar_pd_pf'] == 'PF') ? 'PD' : 'PF';
        ?>
            <a href="javascript:alterar_forma_desconto('<?=$descontar_pd_pf_futuro;?>', '<?=$campos[$i]['id_vale_dp'];?>')" title='Alterar a Forma de Desconto' class='link'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
                    <?=$campos[$i]['descontar_pd_pf'];?>
                </font>
            </a>
        </td>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5' <?=$color;?>>
            <?
                if($campos[$i]['descontado'] == 'N') {
                    echo 'Não';
                }else {
                    echo 'Sim';
                }
            ?>
            </font>
        </td>
        <td>
        <?
            if($campos[$i]['ativo'] == 1) {//Vale Ativo ...
//Só tem permissão p/ Alteração de Vales os usuários do Roberto, Dona Sandra, Dárcio ou Netto porque desenvolvem o Sistema ...
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 66 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
                    if($campos[$i]['descontado'] == 'S') {//Vale Descontado, esse não pode ser alterado ...
        ?>
                <font onclick="javascript:alert('<?=$mensagem[3];?>')" style='cursor:help'>
                    <b>&nbsp;-&nbsp;</b>
                </font>
        <?
                    }else {//Vale pode ser alterado normalmente ...
                        $onclick = "javascript:alterar_vale('".$campos[$i]['tipo_vale']."', '".$campos[$i]['id_vale_dp']."', '".$campos[$i]['data_debito']."')";
        ?>
                <img src = "../../../../imagem/menu/alterar.png" border='0' title="Alterar Vale" alt="Alterar Vale" onclick="<?=$onclick;?>">
        <?
                    }
                }else {
//Se for o Usuário da Graziella, o único Tipo de Vale q pode estar sendo alterado é o do Dia 20 ...
                    if($_SESSION['id_funcionario'] == 111 || $campos[$i]['tipo_vale'] == 1) {
                        if($campos[$i]['descontado'] == 'S') {//Vale Descontado, esse não pode ser alterado ...
        ?>
                <font onclick="javascript:alert('<?=$mensagem[3];?>')" style='cursor:help'>
                    <b>&nbsp;-&nbsp;</b>
                </font>
        <?
                        }else {//Vale pode ser alterado normalmente ...
                            $onclick = "javascript:alterar_vale('".$campos[$i]['tipo_vale']."', '".$campos[$i]['id_vale_dp']."', '".$campos[$i]['data_debito']."')";
        ?>
                        <img src = "../../../../imagem/menu/alterar.png" border='0' title="Alterar Vale" alt="Alterar Vale" onclick="<?=$onclick;?>">
        <?
                        }
                    }else {
        ?>
                    <img src = "../../../../imagem/menu/alterar.png" border='0' title="Alterar Vale" alt="Alterar Vale" onclick="javascript:alert('USUÁRIO SEM PERMISSÃO P/ ALTERAR VALE !')">
        <?
                    }
                }
            }else {//Vale Excluído ...
                echo '<font color="red" title="Vale Excluído" style="cursor:help"><b>EXCL</b></font>';
            }
        ?>
        </td>
    </tr>
<?
                    $total_vales+= $campos[$i]['valor'];
			
/*Aqui eu verifico se existe algum Vale em que a Data de Holerith é maior do que a Data Atual, faço esse 
controle pra na hora de descontar os Vales*/
                    $data_holerith_current = substr($campos[$i]['data_debito'], 0, 4).substr($campos[$i]['data_debito'], 5, 2).substr($campos[$i]['data_debito'], 8, 2);
                    $data_atual = date('Ymd');
/*Se existir alguma Data Atual Menor que a Data de Holerith então o Sistema força o Usuário a preencher
uma justificativa explicando o motivo pelo qual este está fazendo esse procedimento ...*/
                    if($data_atual < $data_holerith_current) $data_atual_maior_q_holerith++;
                }
?>
    <tr>
        <td colspan='4' class='linhadestaque' align='right'>
            Total de Vale(s):
        </td>
        <td colspan='2' class='linhadestaque' align='right'>
            R$ <?=number_format($total_vales, 2, ',', '.');?>
        </td>
        <td colspan='6' class='linhadestaque'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar.php'" class='botao'>
<?
            if($linhas > 0) {
?>
                <input type='button' name='cmd_descontar_vale' value='Descontar Vale' title='Descontar Vale' onclick="descontar_vale()" class='botao'>
                <input type='button' name='cmd_excluir_vale' value='Excluir Vale(s)' title='Excluir Vale(s)' onclick='excluir_vale()' class='botao'>
                <input type='button' name='cmd_relatorio_vale' value='Relatório de Vale(s)' title='Relatório de Vale(s)' onclick='relatorio_vale()' class='botao'>
                <input type='button' name='cmd_imprimir_vale' value='Imprimir Vale(s)' title='Imprimir Vale(s)' onclick='imprimir_vale()' class='botao'>
<?
            }
?>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<!--Controle de Tela p/ não perder a paginação e a ordenação dos Itens da Página-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='hdd_justificativa'>
<!-- ******************************************** -->
</form>
</body>
</html>
<?
    }
}

if(!empty($valor)) {
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem[$valor];?>')
    </Script>
<?
}
?>