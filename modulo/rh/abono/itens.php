<?
require('../../../lib/segurancas.php');
require('../../../lib/compras_new.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
require('../../../lib/menu/menu.php');
segurancas::geral('/erp/albafer/modulo/rh/abono/consultar.php', '../../../');

$mensagem[1] = 'ABONO EXCLUÍDO COM SUCESSO !';
$mensagem[2] = 'ABONO(S) DESCONTADO(S) COM SUCESSO !';
$mensagem[3] = 'ESTE ABONO NÃO PODE SER ALTERADO !\nESTE ABONO JÁ FOI DESCONTADO !!!';
$mensagem[4] = 'ESTE ABONO NÃO PODE SER EXCLUÍDO !\nESTE ABONO JÁ FOI DESCONTADO !!!';

if($passo == 1) {
//Só não irá enviar esse e-mail quando for o Roberto e a Dona Sandra que estiver excluindo o Abono ...
    if($_SESSION['id_login'] != 22 || $_SESSION['id_login'] != 29) {
//Se tiver a Data de Vencimento for alterada, então precisa ser modificada a Justificativa ...
        if(!empty($hdd_justificativa)) {
//1)
/************************Busca de Dados************************/
            $data_ocorrencia = date('Y-m-d H:i:s');
//Aqui eu trago alguns dados de Abono p/ passar por e-mail via parâmetro ...
            $sql = "SELECT e.nomefantasia, f.nome, a.*, vd.data 
                    FROM `abonos` a 
                    INNER JOIN `vales_datas` vd ON vd.`id_vale_data` = a.`id_vale_data` 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = a.`id_funcionario` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` AND e.`id_empresa` 
                    WHERE a.`id_abono` = '$id_abono' LIMIT 1 ";
            $campos         = bancos::sql($sql);
            $empresa        = $campos[0]['nomefantasia'];
            $funcionario    = $campos[0]['nome'];
            $valor          = $campos[0]['valor'];
            $data_holerith  = data::datetodata($campos[0]['data'], '/');
            $data_emissao   = data::datetodata($campos[0]['data_emissao'], '/');
//2)
/************************E-mail************************/
/*
//-Se o Usuário estiver excluindo o Abono do RH, então o Sistema dispara um e-mail 
informando qual o Abono que está sendo excluído ...
//-Aqui eu trago alguns dados do Abono p/ passar por e-mail via parâmetro ...
//-Aqui eu busco o login de quem está excluindo o Abono ...*/
            $sql = "SELECT login 
                    FROM `logins` 
                    WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
            $campos_login 	= bancos::sql($sql);
            $login_excluindo    = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
            $complemento_justificativa = '<br><b>Empresa: </b>'.$empresa.' <br><b>Funcionário: </b>'.$funcionario.' <br><b>Login: </b>'.$login_excluindo;
            $txt_justificativa  = $complemento_justificativa.'<br><b>Data de Holerith: </b>'.$data_holerith.'<br><b>Data de Emissão: </b>'.$data_emissao.'<br><b>Valor do Abono: </b>'.number_format($valor, 2, ',', '.').'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$hdd_justificativa;
//Aqui eu mando um e-mail informando quem e porque que excluiu o Abono ...
            $destino = 'direcao@grupoalbafer.com.br';
            $mensagem = $txt_justificativa;
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Exclusão de Abono(s)', $mensagem);
        }
    }
//3)
/************************Exclusão************************/
//Excluindo o Abono ...
    $sql = "DELETE 
            FROM `abonos` 
            WHERE `id_abono` = '$id_abono' LIMIT 1 ";
    bancos::sql($sql);
    $valor = 1;
//Esse comando serve p/ retirar as \ invertidas antes dos espaços em branco ...
    $sql2 = stripcslashes($sql2);
//Macete (rsrs) p/ não perder o parâmetro quando exclui os Abonos ...
?>
<html>
<body>
<form name='form'>
<!--Controle de Tela p/ não perder a paginação e a ordenação dos Itens da Página-->
<input type='hidden' name='sql2' value='<?=$sql2;?>'>
<!-- ******************************************** -->
</form>
</body>
</html>
<Script Language = 'JavaScript'>
    var sql2 = document.form.sql2.value
    window.location = 'itens.php?sql2='+sql2+'&valor=<?=$valor;?>'
</Script>
<?
//Aqui nesse passo eu faço uma marcação de todos os Abono(s)
}else if($passo == 2) {
//Só não irá enviar esse e-mail quando for o Roberto e a Dona Sandra que estiver descontando o Abono ...
    if($_SESSION['id_login'] != 22 || $_SESSION['id_login'] != 29) {
//Se tiver a Data de Vencimento for alterada, então precisa ser modificada a Justificativa ...
        if(!empty($hdd_justificativa)) {
            $txt_justificativa = '<font color="blue">Abono(s) que estão sendo descontados antes que a Data do Holerith: </font><br>';
//1)
/************************Busca de Dados************************/
            $data_ocorrencia = date('Y-m-d H:i:s');
//Aqui eu trago alguns dados de Abono p/ passar por e-mail via parâmetro ...
            $sql = "SELECT e.nomefantasia, f.nome, a.*, vd.data 
                    FROM `abonos` a 
                    INNER JOIN `vales_datas` vd ON vd.`id_vale_data` = a.`id_vale_data` 
                    INNER JOIN `funcionarios` f ON f.`id_funcionario` = a.`id_funcionario` 
                    INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` AND e.`id_empresa` 
                    WHERE a.`id_abono` IN ($id_abonos) ";
            $campos = bancos::sql($sql);
            $linhas = count($campos);
//Disparo do Loop ...
            for($i = 0; $i < $linhas; $i++) {
                $empresa = $campos[$i]['nomefantasia'];
                $funcionario = $campos[$i]['nome'];
                $valor = $campos[$i]['valor'];
                $data_holerith = data::datetodata($campos[$i]['data'], '/');
                $data_emissao = data::datetodata($campos[$i]['data_emissao'], '/');
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
                $complemento_justificativa.= '<br><b>Empresa: </b>'.$empresa.' <br><b>Funcionário: </b>'.$funcionario.'<br><b>Data de Holerith: </b>'.$data_holerith.'<br><b>Data de Emissão: </b>'.$data_emissao.'<br><b>Valor do Abono: </b>'.number_format($valor, 2, ',', '.').'<br>';
            }
//2)
/************************E-mail************************/
/*
//-Se o Usuário estiver descontando o Abono do RH, então o Sistema dispara um e-mail 
informando qual o Abono que está sendo excluído ...
//-Aqui eu trago alguns dados do Abono p/ passar por e-mail via parâmetro ...
//-Aqui eu busco o login de quem está descontando o Abono ...*/
            $sql = "SELECT login 
                    FROM logins 
                    WHERE id_login = '$_SESSION[id_login]' LIMIT 1 ";
            $campos_login 		= bancos::sql($sql);
            $login_descontando 	= $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
            $txt_justificativa.= $complemento_justificativa.'<br><b>Login: </b>'.$login_descontando.' - '.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$hdd_justificativa;
//Aqui eu mando um e-mail informando quem e porque que excluiu o Abono ...
            $destino    = 'direcao@grupoalbafer.com.br';
            $mensagem   = $txt_justificativa;
            comunicacao::email("ERP - GRUPO ALBAFER", $destino, 'Abono(s) Descontado(s) antes da Data do Holerith', $mensagem);
        }
    }
//3)
/************************Descontando************************/
//Descontando o Abono ...
    $sql = "UPDATE `abonos` SET `descontado` = 'S' WHERE `id_abono` IN ($id_abonos) ";
    bancos::sql($sql);
//Esse comando serve p/ retirar as \ invertidas antes dos espaços em branco ...
    $sql2 = stripcslashes($sql2);
//Macete (rsrs) p/ não perder o parâmetro quando exclui os Abonos ...
?>
<html>
<body>
<form name='form'>
<!--Controle de Tela p/ não perder a paginação e a ordenação dos Itens da Página-->
<input type='hidden' name='sql2' value='<?=$sql2;?>'>
<!-- ******************************************** -->
</form>
</body>
</html>
<Script Language = 'JavaScript'>
    var sql2 = document.form.sql2.value
    window.location = 'itens.php?sql2='+sql2+'&valor=2'
</Script>
<?
}else {
/*Somente na primeira vez em que eu entrar nessa tela, irá executar essa rotina até o usuário 
não recarregar "submeter" a Tela ...*/
    if(empty($sql2)) {
        if(empty($cmb_data_holerith))               $cmb_data_holerith = '%';
        if(empty($cmb_empresa))                     $cmb_empresa = '%';
        if(empty($cmb_descontar_pd_pf))             $cmb_descontar_pd_pf = '%';
//Exibirá somente os Abonos que ainda não foram Descontados ...
        if(!empty($chkt_somente_nao_descontado))    $condicao = " AND a.`descontado` = 'N' ";
//Aqui eu exibo os Abonos ...
        $sql_principal = "SELECT a.*, e.`nomefantasia`, f.`nome`, vd.`data` 
                            FROM `abonos` a 
                            INNER JOIN `vales_datas` vd ON vd.`id_vale_data` = a.`id_vale_data` AND vd.`id_vale_data` LIKE '$cmb_data_holerith' 
                            INNER JOIN `funcionarios` f ON f.`id_funcionario` = a.`id_funcionario` AND f.`nome` LIKE '%$txt_funcionario%' 
                            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` AND e.`id_empresa` LIKE '$cmb_empresa' 
                            WHERE a.`descontar_pd_pf` LIKE '$cmb_descontar_pd_pf' $condicao ORDER BY a.id_vale_data, f.nome ";
//Nas demais vezes em que o usuário recarregou "submeteu" a Tela ...
    }else {
//Esse comando serve p/ retirar as \ invertidas antes dos espaços em branco ...
        $sql_principal = stripcslashes(str_replace('|', '%', $sql2));
    }
    $campos = bancos::sql($sql_principal, $inicio, 100, 'sim', $pagina);
/*********************************************************************/
    $linhas = count($campos);
//Verifica se tem pelo menos um Abono Cadastrado ...
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function imprimir_abono() {
    var id_abonos = ''
//Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox  = 0
    var elementos = document.form.elements
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(elementos[i].checked == true) {
                id_abonos+= elementos[i].value + ','
                checkbox ++
            }
        }
    }
    id_abonos = id_abonos.substr(0, id_abonos.length - 1)

    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        nova_janela('relatorios/relatorio.php?id_abonos='+id_abonos, 'CONSULTAR', 'F')
    }
}
</Script>
</head>
<body>
<form name='form'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Abono(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' title='Selecionar Tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            Funcionário
        </td>
        <td>
            Empresa
        </td>
        <td>
            Valor
        </td>
        <td>
            Data de Holerith
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Abono
        </td>
        <td>
            Descontado
        </td>
        <td colspan='2'>
            <img src = '../../../imagem/menu/incluir.png' border='0' title='Incluir Abono' alt='Incluir Abono' onclick="nova_janela('incluir.php', 'POP', '', '', '', '', 580, 950, 'c', 'c', '', '', 's', 's', '', '', '')">
        </td>
    </tr>
<?
        //Variável q vai servir p/ controlar se existe alguma Data Atual Menor que a Data de Holerith ...
        $data_atual_maior_q_holerith = 0;
        //Listando os Abonos ...
        for ($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');document.form.chkt_tudo.checked = false" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_abono[]' value="<?=$campos[$i]['id_abono'];?>" onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8');document.form.chkt_tudo.checked = false" class='checkbox'>
        </td>
        <td align='left'>
            <?
                /*Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" 
                da sessão e o parâmetro pop_up significa que está tela está sendo aberta como pop_up e sendo 
                assim é para não exibir o botão de Voltar que existe nessa tela ...*/
            ?>
            <a href="javascript:nova_janela('../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=<?=$campos[$i]['id_funcionario'];?>&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title="Detalhes Funcionário" class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='right'>
            R$ <?=number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_emissao'], '/');?>
        </td>
        <td>
            <?=$campos[$i]['descontar_pd_pf'];?>
        </td>
        <td>
        <?
            if($campos[$i]['descontado'] == 'N') {
                echo 'Não';
            }else {
                echo 'Sim';
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['descontado'] == 'S') {//O abono não pode ser exclúido ...
        ?>
                <font onclick="javascript:alert('<?=$mensagem[4];?>')" style="cursor:help">
                    <b>&nbsp;-&nbsp;</b>
                </font>
        <?
            }else {
        ?>
                <img src = "../../../imagem/menu/alterar.png" border='0' title="Alterar Abono" alt="Alterar Abono" onclick="nova_janela('alterar.php?id_abono=<?=$campos[$i]['id_abono'];?>', 'POP', '', '', '', '', 280, 800, 'c', 'c', '', '', 's', 's', '', '', '')">
        <?
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['descontado'] == 'S') {//O abono não pode ser exclúido ...
        ?>
                <font onclick="javascript:alert('<?=$mensagem[4];?>')" style="cursor:help">
                    <b>&nbsp;-&nbsp;</b>
                </font>
        <?
            }else {
                $onclick = "javascript:excluir_abono('".$campos[$i]['id_abono']."')";
        ?>
                <img src = "../../../imagem/menu/excluir.png" border='0' title="Excluir Abono" alt="Excluir Abono" onclick="<?=$onclick;?>">
        <?
            }
        ?>
        </td>
    </tr>
<?
            $total_abonos+= $campos[$i]['valor'];

/*Aqui eu verifico se existe algum Abono em que a Data de Holerith é maior do que a Data Atual, faço esse 
controle pra na hora de descontar os Abonos*/
            $data_holerith_current = substr($campos[$i]['data'], 0, 4).substr($campos[$i]['data'], 5, 2).substr($campos[$i]['data'], 8, 2);
            $data_atual = date('Ymd');
/*Se existir alguma Data Atual Menor que a Data de Holerith então o Sistema força o Usuário a preencher
uma justificativa explicando o motivo pelo qual este está fazendo esse procedimento ...*/
            if($data_atual < $data_holerith_current) $data_atual_maior_q_holerith++;
        }
?>
    <tr class='linhadestaque'>
        <td colspan='3' align='right'>
            Total de Abono(s):
        </td>
        <td align='right'>
            <font color='yellow'>
                R$ <?=number_format($total_abonos, 2, ',', '.');?>
            </font>
        </td>
        <td colspan='6'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar.php'" class='botao'>
            <input type='button' name='cmd_incluir_abono' value='Incluir Abono' title='Incluir Abono' onclick="nova_janela('incluir.php', 'POP', '', '', '', '', 580, 950, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
<?
        if($linhas > 0) {
?>
            <input type='button' name='cmd_descontar_abono' value='Descontar Abono' title='Descontar Abono' onclick="descontar_abono()" class='botao'>
            <input type='button' name='cmd_imprimir_abono' value='Imprimir Abono(s)' title='Imprimir Abono(s)' onclick='imprimir_abono()' class='botao'>
<?
        }
?>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
/****************Macete p/ controlar a Tela e não perder mais o(s) Parâmetros (rsrs) ****************/
//Vai entrar aqui somente na primeira em que carregar a tela
if(empty($sql2)) {
//Controle para o hidden
    $sql2 = stripcslashes(str_replace('%', '|', $sql_principal));
}else {
//Esse comando serve p/ retirar as \ invertidas antes dos espaços em branco ...
    $sql2 = stripcslashes(str_replace('%', '|', $sql2));
}
/****************************************************************************************************/
?>
<!--Não me lembro desses hiddens aki (rsrs)-->
<input type='hidden' name='opt_item'>
<input type='hidden' name='opt_item_principal'>
<!--Controle de Tela p/ não perder a paginação e a ordenação dos Itens da Página-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='sql2' value="<?=$sql2;?>">
<input type='hidden' name='hdd_justificativa'>
<!-- ******************************************** -->
</form>
</body>
</html>
<Script Language = 'JavaScript'>
//Coloquei essas funções aqui em baixo por causa da variável sql2 ...
function descontar_abono() {
    var id_login = '<?=$_SESSION['id_login'];?>'//Login do Usuário q está logado no Sistema ...
    var id_abonos = ''
//Serve para verificar quantos checkbox eu tenho selecionado no frame de cima
    var checkbox  = 0
    var elementos = document.form.elements
    var data_atual_maior_q_holerith = '<?=$data_atual_maior_q_holerith;?>'
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if(elementos[i].checked == true) {
                id_abonos+= elementos[i].value + ','
                checkbox ++
            }
        }
    }
    id_abonos = id_abonos.substr(0, id_abonos.length - 1)

    if(checkbox == 0) {
        alert('SELECIONE UM ITEM !')
        return false
    }else {
        var resposta = confirm('TEM CERTEZA DE QUE DESEJA DESCONTAR ESSE(S) ABONO(S) ?')
        if(resposta == false) {
            return false
        }else {//Se sim, então ...
//Só não irá enviar esse e-mail quando for o Roberto e a Dona Sandra que estiver descontando o Abono ...
            if(id_login != 22 && id_login != 29) {
/*Se existir algum abono em que a Data Atual é Menor que a Data do Holerith, então eu forço o usuário
a preencher uma Justificativa p/ mandar por e-mail ...*/
                if(data_atual_maior_q_holerith > 0) {
                    var justificativa = prompt('EXISTE(M) ABONO(S) QUE ESTÃO SENDO DESCONTADO(S) ANTES QUE A DATA DO HOLERITH !\nDIGITE UMA JUSTIFICATIVA P/ DESCONTAR ESSE(S) ABONO(S): ')
                    document.form.hdd_justificativa.value = justificativa
//Controle com a Justificativa ...
                    if(document.form.hdd_justificativa.value == '' || document.form.hdd_justificativa.value == 'null' || document.form.hdd_justificativa.value == 'undefined') {
                        alert('JUSTIFICATIVA INVÁLIDA !!!\nDIGITE UMA JUSTIFICATIVA P/ DESCONTAR O ABONO !')
                        return false
                    }
                    var sql2 = document.form.sql2.value
                    var hdd_justificativa = document.form.hdd_justificativa.value
                    window.location = 'itens.php?passo=2&hdd_justificativa='+hdd_justificativa+'&sql2='+sql2+'&id_abonos='+id_abonos
                }else {
                    var sql2 = document.form.sql2.value
                    window.location = 'itens.php?passo=2&sql2='+sql2+'&id_abonos='+id_abonos
                }
            }else {
                var sql2 = document.form.sql2.value
                window.location = 'itens.php?passo=2&sql2='+sql2+'&id_abonos='+id_abonos
            }
        }
    }
}

function excluir_abono(id_abono) {
    var id_login = '<?=$_SESSION['id_login'];?>'//Login do Usuário q está logado no Sistema ...
    var resposta = confirm('CONFIRMA A EXCLUSÃO ?')
    if(resposta == true) {
//Só não irá enviar esse e-mail quando for o Roberto e a Dona Sandra que estiver excluindo o Abono ...
        if(id_login != 22 && id_login != 29) {
//Verifico se a Data de Vencimento foi alterada pelo usuário ...
            var justificativa = prompt('DIGITE UMA JUSTIFICATIVA P/ EXCLUIR O ABONO: ')
            document.form.hdd_justificativa.value = justificativa
//Controle com a Justificativa ...
            if(document.form.hdd_justificativa.value == '' || document.form.hdd_justificativa.value == 'null' || document.form.hdd_justificativa.value == 'undefined') {
                alert('JUSTIFICATIVA INVÁLIDA !!!\nDIGITE UMA JUSTIFICATIVA P/ EXCLUIR O ABONO !')
                return false
            }
            var sql2 = document.form.sql2.value
            var hdd_justificativa = document.form.hdd_justificativa.value
            window.location = 'itens.php?passo=1&hdd_justificativa='+hdd_justificativa+'&sql2='+sql2+'&id_abono='+id_abono
        }else {
            var sql2 = document.form.sql2.value
            window.location = 'itens.php?passo=1&sql2='+sql2+'&id_abono='+id_abono
        }
    }else {
        return false
    }
}
</Script>
<?
        if(!empty($valor)) {
?>
            <Script Language = 'Javascript'>
                alert('<?=$mensagem[$valor];?>')
            </Script>
<?
        }
    }else {
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<body>
<form name='form'>
<table width='80%' border='0' cellspacing='0' cellpadding='0' align='center'>
    <tr class='atencao' align='center'>
        <td>
            <font face='Verdana, Arial, Helvetica, sans-serif' size="-1" color="#FF0000">
                <b>Não há abono(s) cadastrado(s)
            </font>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="javascript:window.location = 'consultar.php'" class='botao'>
            <input type='button' name='cmd_incluir_abono' value='Incluir Abono' title='Incluir Abono' onclick="javascript:nova_janela('incluir.php', 'POP', '', '', '', '', 580, 950, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        </td>
    </tr>
</table>
<?
/****************Macete p/ controlar a Tela e não perder mais o(s) Parâmetros (rsrs) ****************/
//Vai entrar aqui somente na primeira em que carregar a tela
if(empty($sql2)) {
//Controle para o hidden
    $sql2 = stripcslashes(str_replace('%', '|', $sql_principal));
}else {
//Esse comando serve p/ retirar as \ invertidas antes dos espaços em branco ...
    $sql2 = stripcslashes(str_replace('%', '|', $sql2));
}
/****************************************************************************************************/
?>
<!--Controle de Tela p/ não perder a paginação e a ordenação dos Itens da Página-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='sql2' value="<?=$sql2;?>">
<!-- ******************************************** -->
</form>
</body>
</html>
<?
        if(!empty($valor)) {
?>
            <Script Language = 'JavaScript'>
                alert('<?=$mensagem[$valor];?>')
            </Script>
<?
        }
    }
}
?>