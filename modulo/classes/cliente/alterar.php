<?
if(!class_exists('segurancas'))                                 require '../../../lib/segurancas.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(empty($_GET['pop_up']) && empty($_GET['nao_exibir_menu']))   require '../../../lib/menu/menu.php';//Significa que essa Tela foi aberta como sendo Pop-UP ...
if(!class_exists('data'))                                       require '../../../lib/data.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(!class_exists('financeiros'))                                require '../../../lib/financeiros.php';//CASO EXISTA EU DESVIO A CLASSE ...
if(!class_exists('genericas'))                                  require '../../../lib/genericas.php';//CASO EXISTA EU DESVIO A CLASSE ...
session_start('funcionarios');

if($passo == 1) {
?>
<html>
<head>
<title>.:: Alterar Cliente(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function gerenciar_telas(tela) {
    if(tela == 1) {//Dados Básicos ...
        window.tela.location = 'alterar_dados_basicos.php?id_cliente='+document.form.id_cliente.value+'&pop_up=<?=$_GET['pop_up'];?>&nao_exibir_menu=<?=$_GET['nao_exibir_menu'];?>'
    }else if(tela == 2) {//Dados Comerciais ...
        window.tela.location = 'alterar_dados_comerciais.php?id_cliente='+document.form.id_cliente.value+'&pop_up=<?=$_GET['pop_up'];?>&nao_exibir_menu=<?=$_GET['nao_exibir_menu'];?>'
    }else if(tela == 3) {//Históricos ...
        window.tela.location = 'alterar_dados_historicos.php?id_cliente='+document.form.id_cliente.value+'&pop_up=<?=$_GET['pop_up'];?>&nao_exibir_menu=<?=$_GET['nao_exibir_menu'];?>'
    }
}
</Script>
</head>
<body onload='gerenciar_telas(1)'>
<form name='form' method='post'>
<input type='hidden' name='id_cliente' value="<?=$_GET['id_cliente'];?>">
<table width='880' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td id='aba0' onclick='gerenciar_telas(1);aba(this, 3, 650)' width='33%' class='aba_ativa'>
            Dados Básicos
        </td>
        <td id='aba1' onclick='gerenciar_telas(2);aba(this, 3, 650)' width='33%' class='aba_inativa'>
            Dados Comerciais
        </td>
        <td id='aba2' onclick='gerenciar_telas(3);aba(this, 3, 650)' width='33%' class='aba_inativa'>
            Histórico
        </td>
    </tr>
</table>
<table border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <iframe name='tela' src='' marginwidth='0' marginheight='0' frameborder='0' height='1050' width='900'></iframe>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Alterar Clientes ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='15'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            Alterar Cliente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <?=genericas::order_by('c.razaosocial', 'Razão Social', 'Razão Social', $order_by, '../../../');?>
        </td>
        <td>
            <?=genericas::order_by('c.nomefantasia', 'Nome Fantasia', 'Nome Fantasia', $order_by, '../../../');?>
        </td>
        <td>
            Tp
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Tel Fax
        </td>
        <td>
            Cr
        </td>
        <td>
            E-mail
        </td>
        <td>
            Últ Visita
        </td>
        <td>
            Endereço
        </td>
        <td>
            Cidade
        </td>
        <td>
            Cep
        </td>
        <td>
            País
        </td>
        <td>
            UF
        </td>
        <td>
            CPF / CNPJ
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = '../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$campos[$i]['id_cliente'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <?=$campos[$i]['cod_cliente'].' - '.$campos[$i]['razaosocial'];?>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['tipo'];?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax']))    echo $campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            if(!empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax']))     echo $campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].$campos[$i]['telfax'];
            if(empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax']))     echo $campos[$i]['ddi_fax'].$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            if(empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax']))      echo $campos[$i]['telfax'];
        ?>
        </td>
        <td align='center'>
            <font color='blue'>
                <?=financeiros::controle_credito($campos[$i]['id_cliente']);?>
            </font>
        </td>
        <td>
            <?=$campos[$i]['email'];?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['data_ultima_visita'] != '0000-00-00') echo data::datetodata($campos[$i]['data_ultima_visita'], '/');
        ?>
        </td>
        <td>
        <?
            echo $campos[$i]['endereco'];
            //Daí sim printa o complemento
            if(!empty($campos[$i]['endereco'])) echo ', '.$campos[$i]['num_complemento'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td align='center'>
            <?=$campos[$i]['cep'];?>
        </td>
        <td align='center'>
        <?
            $sql = "SELECT `pais` 
                    FROM `paises` 
                    WHERE `id_pais` = '".$campos[$i]['id_pais']."' LIMIT 1 ";
            $campos_pais = bancos::sql($sql);
            echo $campos_pais[0]['pais'];
        ?>
        </td>
        <td align='center'>
            <?=$campos_uf[0]['sigla'];?>
        </td>
        <td align='center'>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='15'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
        </td>
    </tr>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
<tr>
    <td>&nbsp;</td>
</tr>
<tr align='center'>
    <td>
        <?=paginacao::print_paginacao('sim');?>
    </td>
</tr>
</table>
</body>
</html>
<?
    }
}
?>