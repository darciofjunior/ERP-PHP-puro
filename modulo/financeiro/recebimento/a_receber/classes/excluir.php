<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/comunicacao.php');
require('../../../../../lib/data.php');
require('../../../../../lib/financeiros.php');
require('../../../../../lib/faturamentos.php');
require('../../../../../lib/genericas.php');
require('../../../../../lib/variaveis/intermodular.php');
session_start('funcionarios');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../');

$mensagem[1] = 'ESTA CONTA N�O PODE SER EXCLU�DA ! \nELA FOI INCLUIDA DE MANEIRA MANUAL PARA O FINANCEIRO !';
$mensagem[2] = 'CONTA � RECEBER (DUPLICATA) EXCLUIDA COM SUCESSO !';
$mensagem[3] = 'ESSA(S) CONTA(S) N�O PODE(M) SER EXCLU�DA(S) ! \nELA(S) J� POSSUE(M) RECEBIMENTO(S) !';

/*Fun��o que serve exclusivamente para esse arquivo ...
Objetivo: Se for exclu�do uma duplicata, ent�o tem que ser exclu�do do sistema todas as demais vias ...*/

function verifica_e_exclui_todas_duplicatas($id_conta_receber, $id_cliente) {
//1)
/*****************************************Busca de Dados*****************************************/
//Busca do id_empresa e do num_conta da Conta � Receber selecionada ...
    $sql = "SELECT `id_empresa`, `num_conta`, `data_emissao` 
            FROM `contas_receberes` 
            WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
    $campos             = bancos::sql($sql);
//Tem que renomear essa vari�vel pq na sess�o j� existe uma com esse nome ...
    $id_empresa_conta 	= $campos[0]['id_empresa'];
    $num_conta          = $campos[0]['num_conta'];
    $ano_emissao        = substr($campos[0]['data_emissao'], 0, 4);
/********************************************************************************************/
//Busca do �ltimo digito da Conta ...
    $ultimo_digito = substr($num_conta, strlen($num_conta) - 1, 1);
/******Se o �ltimo d�gito for uma letra, ent�o eu retiro essa letra p/ que fique apenas c/ o num. puro******/
    if($ultimo_digito == 'A' || $ultimo_digito == 'B' || $ultimo_digito == 'C' || $ultimo_digito == 'D') {
//Aqui � o N.� da Conta puro, sem a letra no fim ...
        $num_conta = substr($campos[0]['num_conta'], 0, strlen($campos[0]['num_conta']) - 1);
    }
//Tratamento apenas para garantir as Contas que eu estou tentando excluir realmente ...
/********************************************************************************************/
    $clausula = "'".$num_conta."', '".$num_conta."A', '".$num_conta."B', '".$num_conta."C', '".$num_conta."D'";
/*Listo todas as contas � Receber que possuem o mesmo n�mero, da mesma Empresa, que estejam em v�rias Duplicatas 
e que sejam importadas do Faturamento para o Financeiro ...

//Os dados da "Conta � Receber" e "Cliente" que forem encontrados tamb�m ser�o passados por e-mail + abaixo ...*/
    $sql = "SELECT cr.*, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, tp.`recebimento` 
            FROM `contas_receberes` cr 
            INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
            INNER JOIN `tipos_recebimentos` tp ON tp.`id_tipo_recebimento` = cr.`id_tipo_recebimento` 
            WHERE cr.`id_cliente` = '$id_cliente' 
            AND cr.`num_conta` IN ($clausula) 
            AND cr.`id_empresa` = '$id_empresa_conta' 
            AND SUBSTRING(cr.`data_emissao`, 1, 4) = '$ano_emissao' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {//Disparo das contas ...
        $data_vencimento_alterada   = $campos[$i]['data_vencimento_alterada'];
        //Coloquei esse nome na vari�vel porque na sess�o j� existe uma vari�vel com o nome de id_empresa ...
        $id_empresa_conta_receber   = $campos[$i]['id_empresa'];
        $empresa                    = genericas::nome_empresa($id_empresa_conta_receber);
        $num_conta                  = $campos[$i]['num_conta'];
        $cliente                    = $campos[$i]['cliente'];
        $tipo_recebimento           = $campos[$i]['recebimento'];
        //Dados p/ enviar por e-mail ...
        $complemento_justificativa.= '<br><b>Empresa: </b>'.$empresa.' <br><b>Cliente: </b>'.$cliente.' <br><b>N.� da Conta: </b>'.$num_conta.' <br><b>Data de Vencimento: </b>'.data::datetodata($data_vencimento_alterada, '/').' <br><b>Tipo de Recebimento: </b>'.$tipo_recebimento;
        $id_contas_receberes.= $campos[$i]['id_conta_receber'].', ';//Vari�vel que vai ser utilizada + abaixo ...
    }
    $id_contas_receberes = substr($id_contas_receberes, 0, strlen($id_contas_receberes) - 2);

//Aqui eu verifico se essas contas j� possuem pelo menos uma parcela recebida ...
    $sql = "SELECT `id_conta_receber_quitacao` 
            FROM `contas_receberes_quitacoes` 
            WHERE `id_conta_receber` IN ($id_contas_receberes) LIMIT 1 ";
    $campos = bancos::sql($sql);
//2)
/*****************************************Verifica��o p/ Excluir*****************************************/
//Significa que essa conta j� tem pelo menos uma parcela recebida ...
    if(count($campos) == 1) {
        return 3;
//Conta inclu�da de forma autom�tica, sendo assim posso excluir
    }else {
//Aqui fa�o alguns desatrelamentos ...
        $sql = "UPDATE `contas_receberes` SET `id_banco` = NULL, `id_bordero` = NULL, `id_representante` = NULL WHERE `id_conta_receber` IN ($id_contas_receberes) ";
        bancos::sql($sql);
/***********************NF de Sa�da - (Vendas / Faturamento) Modo Novo - Autom�tico***********************/
        $sql = "SELECT `id_nf` 
                FROM `contas_receberes` 
                WHERE `id_conta_receber` IN ($id_contas_receberes) 
                AND `id_nf` > '0' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {
            $sql = "UPDATE `nfs` SET `importado_financeiro` = 'N' WHERE `id_nf` = '".$campos[0]['id_nf']."' LIMIT 1 ";
            bancos::sql($sql);
        }
/**************************************NF Outras - Modo Autom�tico**************************************/
        $sql = "SELECT `id_nf_outra` 
                FROM `contas_receberes` 
                WHERE `id_conta_receber` IN ($id_contas_receberes) 
                AND `id_nf_outra` > '0' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {
            $sql = "UPDATE `nfs_outras` SET `importado_financeiro` = 'N' WHERE `id_nf_outra` = '".$campos[0]['id_nf_outra']."' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Deleto todos os $id_contas_receberes encontrados acima ...
        $sql = "DELETE FROM `contas_receberes` WHERE `id_conta_receber` IN ($id_contas_receberes) ";
        bancos::sql($sql);
//3)
/*****************************************E-mail*****************************************/
//Aqui eu mando um e-mail informando quem e porque que exclui a Conta � Receber ...
        $sql = "SELECT `login` 
                FROM `logins` 
                WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
        $campos_login       = bancos::sql($sql);
        $login_excluindo    = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
        $txt_justificativa.= $complemento_justificativa.'<br/><b>Login: </b>'.$login_excluindo.'<br/>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$GLOBALS['txt_justificativa'];
//Aqui eu mando um e-mail informando quem e porque que exclui a Conta � Receber ...
        $destino = $excluir_contas_receber;
        $mensagem_email = $txt_justificativa;
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Exclus�o de Duplicata(s)', $mensagem_email);
/****************************************************************************/
        return 2;
    }
}

if(!empty($_POST['id_conta_receber'])) {
//1)
/*****************************************Disparo das Contas*****************************************/
/*Aqui faz uma verifica��o, porque antigamente as contas eram inclusas de forma manual, verifico se a 
Conta � Receber est� relacionada com a Tabela de Clientes ...*/
    $sql = "SELECT `id_cliente`, `valor`, `status` 
            FROM `contas_receberes` 
            WHERE `id_conta_receber` = '$_POST[id_conta_receber]' 
            AND `id_cliente` > '0' 
            AND `id_nf` IS NULL 
            AND `id_nf_outra` IS NULL LIMIT 1 ";
    $campos_conta_receber_manual = bancos::sql($sql);
    if(count($campos_conta_receber_manual) == 1) {//Significa que a conta foi incluida de forma manual, ent�o ...
        if($campos_conta_receber_manual[0]['status'] == 0) {//Conta � Receber "Em Aberto" ...
            if($campos_conta_receber_manual[0]['valor'] > 0) {//D�bito (CQ DEVOLVIDO DEP. EM C/C PELO CLIENTE) ...
                $valor = verifica_e_exclui_todas_duplicatas($_POST['id_conta_receber'], $campos_conta_receber_manual[0]['id_cliente']);//Fun��o que exclui a Conta ...
            }else {//Cr�dito (DUPL. CEDIDA, ANTECIPA��O) ...
                /*Numa situ��o dessa somente o Roberto 62 e Dona Sandra 66, podem excluir esse Tipo de Conta 
                porque � um Recebimento avulso, n�o temos como fazer Rastreamento no Sistema atrav�s de 
                Nota Fiscal que tem as suas Duplicatas ...*/
                if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 66) {
                    $valor = verifica_e_exclui_todas_duplicatas($_POST['id_conta_receber'], $campos_conta_receber_manual[0]['id_cliente']);//Fun��o que exclui a Conta ...
                }else {//Demais funcion�rios n�o podem excluir esse Tipo de Conta � Receber ...
                    $valor = 1;
                }
            }
        }else {//Conta � Receber "Parcial", j� teve pelo menos um Recebimento, n�o posso Excluir ...
            $valor = 1;
        }
    }else {//Conta inclu�da de forma autom�tica "Nota Fiscal / Duplicata", sendo assim posso excluir ...
        //Verifica se � uma NF de Sa�da - (Vendas / Faturamento) Modo Novo - Autom�tico ...
        $sql = "SELECT `id_cliente` 
                FROM `contas_receberes` 
                WHERE `id_conta_receber` = '$_POST[id_conta_receber]' 
                AND `id_nf` > '0' LIMIT 1 ";
        $campos_nf = bancos::sql($sql);
        if(count($campos_nf) == 1) {//� uma NF de Sa�da ...
//Fun��o que se encarrega de Tudo ...
            $valor = verifica_e_exclui_todas_duplicatas($_POST['id_conta_receber'], $campos_nf[0]['id_cliente']);
        }else {
            //Verifica se � uma NF Outras - Modo Autom�tico ...
            $sql = "SELECT `id_cliente` 
                    FROM `contas_receberes` 
                    WHERE `id_conta_receber` = '$_POST[id_conta_receber]' 
                    AND `id_nf_outra` > '0' LIMIT 1 ";
            $campos_nf_outra = bancos::sql($sql);
            if(count($campos_nf_outra) == 1) {//� uma importa��o
                $valor = verifica_e_exclui_todas_duplicatas($_POST['id_conta_receber'], $campos_nf_outra[0]['id_cliente']);
            }else {//Nem l� e nem c� ent�o, excluo normalmente aquela conta ...
//Aqui eu verifico se essa conta j� n�o foi recebida antes, pelo menos uma parcela
                $sql = "SELECT `id_conta_receber_quitacao` 
                        FROM `contas_receberes_quitacoes` 
                        WHERE `id_conta_receber` = '$_POST[id_conta_receber]' LIMIT 1 ";
                $campos_quitacao = bancos::sql($sql);
                if(count($campos_quitacao) == 0) {
//Busca dos Dados da Conta � Receber e do Cliente p/ poder passar por e-mail ...
                    $sql = "SELECT cr.*, tp.`recebimento` 
                            FROM  contas_receberes` cr 
                            INNER JOIN `tipos_recebimentos` tp ON tp.`id_tipo_recebimento` = cr.`id_tipo_recebimento` 
                            WHERE cr.`id_conta_receber` = '$_POST[id_conta_receber]' LIMIT 1 ";
                    $campos                     = bancos::sql($sql);
                    $data_vencimento_alterada   = $campos[0]['data_vencimento_alterada'];
//Coloquei esse nome na vari�vel porque na sess�o j� existe uma vari�vel com o nome de id_empresa ...
                    $id_empresa_conta_receber   = $campos[0]['id_empresa'];
                    $empresa                    = genericas::nome_empresa($id_empresa_conta_receber);
                    $num_conta                  = $campos[0]['num_conta'];
                    $descricao_conta            = $campos[0]['descricao_conta'];
                    $tipo_recebimento           = $campos[0]['recebimento'];
//Dados p/ enviar por e-mail ...
                    $complemento_justificativa.= '<br><b>Empresa: </b>'.$empresa.' <br><b>Descri��o da Conta: </b>'.$descricao_conta.' <br><b>N.� da Conta: </b>'.$num_conta.' <br><b>Data de Vencimento: </b>'.data::datetodata($data_vencimento_alterada, '/').' <br><b>Tipo de Recebimento: </b>'.$tipo_recebimento;
//2)
/****************************************Excluindo todas as Contas****************************************/
                    //Aqui fa�o alguns desatrelamentos ...
                    $sql = "UPDATE `contas_receberes` SET `id_banco` = NULL, `id_bordero` = NULL, `id_representante` = NULL WHERE `id_conta_receber` = '$_POST[id_conta_receber]' LIMIT 1 ";
                    bancos::sql($sql);
//Deleta as Contas � Receber
                    $sql = "DELETE FROM `contas_receberes` WHERE `id_conta_receber` = '$_POST[id_conta_receber]' LIMIT 1 ";
                    bancos::sql($sql);
//3)
/*****************************************E-mail*****************************************/
//Aqui eu mando um e-mail informando quem e porque que exclui a Conta � Receber ...
                    $sql = "SELECT `login` 
                            FROM `logins` 
                            WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
                    $campos_login       = bancos::sql($sql);
                    $login_excluindo    = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
                    $txt_justificativa.= $complemento_justificativa.'<br><b>Login: </b>'.$login_excluindo.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$txt_justificativa;
//Aqui eu mando um e-mail informando quem e porque que exclui a Conta � Receber ...
                    $destino            = $excluir_contas_receber;
                    $assunto            = 'Exclus�o de Conta(s) � Receber - Duplicata(s)';
                    $mensagem_email     = $txt_justificativa;
                    comunicacao::email("ERP - GRUPO ALBAFER", $destino, $assunto, $mensagem_email);
/****************************************************************************/
                    $valor = 2;
                }else {
                    $valor = 3;
                }
            }
        }
    }
?>
    <Script Language = 'Javascript'>
        alert('<?=$mensagem[$valor];?>')
        opener.parent.itens.recarregar_tela()
        window.close()
    </Script>
<?
}

//Sele��o dos dados de contas � receber - aqui � gen�rico para os 3 tipos de casos
$sql = "SELECT cr.*, c.`razaosocial`, c.`cnpj_cpf`, c.`cidade`, c.`id_pais`, c.`id_uf`, c.`telcom`, c.`telfax` 
        FROM `contas_receberes` cr 
        INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
        WHERE cr.`id_conta_receber` = '$_GET[id_conta_receber]' LIMIT 1 ";
$campos                 = bancos::sql($sql);
//Tem que renomear essa vari�vel pq na sess�o j� existe uma com esse nome ...
$id_empresa_conta       = $campos[0]['id_empresa'];
$id_tipo_recebimento 	= $campos[0]['id_tipo_recebimento'];
$id_tipo_moeda          = $campos[0]['id_tipo_moeda'];
$valor_conta            = $campos[0]['valor'];
$valor_abatimento       = $campos[0]['valor_abatimento'];

$calculos_conta_receber = financeiros::calculos_conta_receber($_GET['id_conta_receber']);
$valor_reajustado       = $calculos_conta_receber['valor_reajustado'];

$descricao_conta        = $campos[0]['descricao_conta'];
$numero_conta           = $campos[0]['num_conta'];
$ultimo_digito          = substr($numero_conta, strlen($numero_conta) - 1, 1);
//Aqui faz um tratamento do N�mero da Nota para depois poder puxar as demais vias de duplicatas
if($ultimo_digito == 'A' || $ultimo_digito == 'B' || $ultimo_digito == 'C' || $ultimo_digito == 'D') $numero_conta = substr($numero_conta, 0, strlen($numero_conta) - 1);
$semana                     = $campos[0]['semana'];
$data_emissao               = $campos[0]['data_emissao'];
$ano_emissao                = substr($campos[0]['data_emissao'], 0, 4);
$data_vencimento_alterada   = $campos[0]['data_vencimento_alterada'];

$comissao_estornada 	= $campos[0]['comissao_estornada'];
$id_cliente             = $campos[0]['id_cliente'];
$cliente                = $campos[0]['razaosocial'];
$cidade                 = $campos[0]['cidade'];
$telcom                 = $campos[0]['telcom'];
$telfax                 = $campos[0]['telfax'];
$id_pais                = $campos[0]['id_pais'];
$id_uf                  = $campos[0]['id_uf'];

$sql = "SELECT `pais` 
        FROM `paises` 
        WHERE `id_pais` = '$id_pais' LIMIT 1 ";
$campos_pais    = bancos::sql($sql);
$pais           = $campos_pais[0]['pais'];

$sql = "SELECT `sigla` 
        FROM `ufs` 
        WHERE `id_uf` = '$id_uf' LIMIT 1 ";
$campos_uf  = bancos::sql($sql);
$uf         = $campos_uf[0]['sigla'];

//Verifica o representante na tabela relacional de conta � receber ...
$sql = "SELECT r.`nome_fantasia` 
        FROM `contas_receberes` cr 
        INNER JOIN `representantes` r ON r.`id_representante` = cr.`id_representante` 
        WHERE cr.`id_conta_receber` = '$_GET[id_conta_receber]' LIMIT 1 ";
$campos_representantes = bancos::sql($sql);
if(count($campos_representantes) == 1) $representante = $campos_representantes[0]['nome_fantasia'];

//Verifica o banco na tabela relacional de conta � receber
$sql = "SELECT b.`banco` 
        FROM `contas_receberes` cr 
        INNER JOIN `bancos` b ON b.`id_banco` = cr.`id_banco` 
        WHERE cr.`id_conta_receber` = '$_GET[id_conta_receber]' LIMIT 1 ";
$campos_banco = bancos::sql($sql);
if(count($campos_banco) == 1) $nome_banco = $campos_banco[0]['banco'];
?>
<html>
<head>
<title>.:: Excluir Conta � Receber (Duplicata) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_conta_receber' value='<?=$_GET['id_conta_receber'];?>'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Excluir Conta � Receber (Duplicata)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4'>
            Detalhes
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' >
        <td colspan='3'>
            <font size='2' color='#6473D4'>
                <b>Cliente / Descri&ccedil;&atilde;o:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>N.� / Conta:</b>
            </font>
            <font size='2'>
                &nbsp;
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2'>
                <?=$cliente;?> / <?=$descricao_conta;?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$numero_conta;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2' color='#6473D4'>
                <b>Cidade / Estado / Pa�s:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>CNPJ:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='3'>
            <font size='2'>
                <?=$cidade.' / '.$uf.' / '.$pais;?>
            </font>
        </td>
        <td>
            <font size='2'>
            <?
                if(!empty($campos[0]['cnpj_cpf'])) {//Campo est� preenchido ...
                    if(strlen($campos[0]['cnpj_cpf']) == 11) {//CPF ...
                        echo substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
                    }else {//CNPJ ...
                        echo substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
                    }
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='2' color='#6473D4'>
                <b>Fone / Fax.:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Representante:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Tipo de Recebimento:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Banco:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' >
        <td>
            <font size='2'>
                <?=$telcom.' / '.$telfax;?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$representante;?>
            </font>
        </td>
        <td>
            <font size='2'>
            <?
                $sql = "SELECT recebimento 
                        FROM `tipos_recebimentos` 
                        WHERE `id_tipo_recebimento` = '$id_tipo_recebimento' LIMIT 1 ";
                $campos_tipo_recebimento = bancos::sql($sql);
                echo $campos_tipo_recebimento[0]['recebimento'];
            ?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$nome_banco;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal' >
        <td>
            <font size='2' color='#6473D4'>
                <b>Tipo da Moeda:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Valor Nacional / Estrangeiro:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Valor Reajustado:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Valor Abatimento:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='red' size='2'>
            <?
                $sql = "SELECT simbolo, CONCAT(simbolo, ' - ', moeda) AS moeda 
                        FROM `tipos_moedas` 
                        WHERE `id_tipo_moeda` = '$id_tipo_moeda' LIMIT 1 ";
                $campos_moeda   = bancos::sql($sql);
                $simbolo_moeda  = $campos_moeda[0]['simbolo'];
                echo $campos_moeda[0]['moeda'];
            ?>
            </font>
        </td>
        <td>
            <font size='2' color='red'>
                <?=number_format($valor_conta, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font size='2' color='red'>
                <?=number_format($valor_reajustado, 2, ',', '.');?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=number_format($valor_abatimento, 2, ',', '.');?>
            </font>
        </td>
    </tr>
<?
/*******************************************************************************************/
//Verifico se existe(m) NF(s) de Devolu��es p/ essa Duplicata ...
    $sql = "SELECT * 
            FROM `contas_receberes_vs_nfs_devolucoes` 
            WHERE `id_conta_receber` = '$_GET[id_conta_receber]' ";
    $campos_nota    = bancos::sql($sql);
    $linhas_nota    = count($campos_nota);
    if($linhas_nota > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            NF(s) Devolvida(s) p/ essa Duplicata
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.� da NF de Devolu��o
        </td>
        <td colspan='2'>
            Valor da Devolu��o
        </td>
    </tr>
    <?
        $total_devolucao = 0;
        for($i = 0; $i < $linhas_nota; $i++) {
    ?>
    <tr class='linhanormal'>
        <td colspan='2' align='center'>
            <a href="javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos_nota[$i]['id_nf_devolucao'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes da NF de Devolu��o' style='cursor:help' class='link'>
                <?=faturamentos::buscar_numero_nf($campos_nota[$i]['id_nf_devolucao'], 'D');?>
            </a>
        </td>
        <td colspan='2' align='right'>
            R$ <?=number_format($campos_nota[$i]['valor_devolucao'], 2, ',', '.');?>
        </td>
    </tr>
<?
            $total_devolucao+= $campos_nota[$i]['valor_devolucao'];
        }
?>
    <tr class='linhadestaque' align='right'>
        <td colspan='2'>
            Total Devolvido => 
        </td>
        <td colspan='2'>
            R$ <?=number_format($total_devolucao, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            &nbsp;
        </td>
    </tr>
<?
    }
/*******************************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            <font size='2' color='#6473D4'>
                <b>Semana:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Data da Conta:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Data de Vencimento:</b>
            </font>
        </td>
        <td>
            <font size='2' color='#6473D4'>
                <b>Comiss�o Estornada:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='2'>
                <?=$semana;?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=data::datetodata($data_emissao, '/');?>
            </font>
        </td>
        <td>
            <font size='2' >
                <?=data::datetodata($data_vencimento_alterada, '/');?>
            </font>
        </td>
        <td>
            <font size='2'>
                <?=$comissao_estornada;?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <font size='2' color='#6473D4'>
                <b>Observa��o:</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <font size='2'>
            <?
                $sql = "SELECT `observacao` 
                        FROM `follow_ups` 
                        WHERE `origem` = '4' 
                        AND `identificacao` = '$_GET[id_conta_receber]' ";
                $campos_follow_ups = bancos::sql($sql);
                $linhas_follow_ups = count($campos_follow_ups);
                for($f = 0; $f < $linhas_follow_ups; $f++) {
                    echo $campos_follow_ups[$f]['observacao'];
                    if($f + 1 != $linhas_follow_ups) echo '<br/>';//Enquanto n�o chegar no �ltimo registro ...
                }
            ?>
            </font>
        </td>
    </tr>
<?
//Tratamento apenas para garantir as Contas que eu estou tentando excluir realmente ...
    $clausula = "'".$numero_conta."', '".$numero_conta."A', '".$numero_conta."B', '".$numero_conta."C', '".$numero_conta."D'";
/*Aki lista todas as contas que possui o mesmo n�mero, da mesma Empresa e do mesmo Cliente que estejam em varias as 
Duplicatas e que sejam importadas do Faturamento para o Financeiro ...*/
    $sql = "SELECT * 
            FROM `contas_receberes` 
            WHERE `id_cliente` = '$id_cliente' 
            AND `num_conta` IN ($clausula) 
            AND `id_empresa` = '$id_empresa_conta' 
            AND SUBSTRING(`data_emissao`, 1, 4) = '$ano_emissao' ORDER BY num_conta LIMIT 4 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td>
            N.&ordm; da Duplicata
        </td>
        <td>
            Valor em 
            <?
                $sql = "SELECT simbolo 
                        FROM `tipos_moedas` 
                        WHERE `id_tipo_moeda` = '$id_tipo_moeda' LIMIT 1 ";
                $campos_moeda = bancos::sql($sql);
                echo $campos_moeda[0]['simbolo'];
            ?>
        </td>
        <td>
            Data de Vencimento
        </td>
        <td>
            Tipo de Recebimento
        </td>
        <td>
            Banco
        </td>
        <td>
            Valor Desconto
        </td>
        <td>
            Valor Abatimento
        </td>
        <td>
            Valor Despesa
        </td>
        <td>
            Valor Reajustado
        </td>
    </tr>
<?
/**********************/
//Essas vari�veis s�o utilizadas mais abaixo, para controle dos Avisos
//Vetor de Status
        $status = array('');
//Vetor de Borderor
        $borderor = array('');
/**********************/
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
<?
        if($campos[$i]['status'] > 0) {//Quer dizer que j� foi recebido algo dessa conta
            $status[$i] = 1;
?>
            <a href="javascript:nova_janela('../../detalhes.php?id_conta_receber=<?=$campos[$i]['id_conta_receber'];?>', 'DETALHES', '', '', '', '', 550, 950, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Conta � Receber' class='link'>
                <?=$campos[$i]['num_conta'];?>
            </a>
<?
        }else {
            echo $campos[$i]['num_conta'];
        }
?>
        </td>
        <td align='right'>
            <?=$simbolo_moeda.' '.number_format($campos[$i]['valor'], 2, ',', '.');?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_vencimento_alterada'], '/');?>
        </td>
        <td>
        <?
//Busca o Tipo de Recebimento da Conta
            $sql = "SELECT `recebimento` 
                    FROM `tipos_recebimentos` 
                    WHERE `id_tipo_recebimento` = '".$campos[$i]['id_tipo_recebimento']."' LIMIT 1 ";
            $campos_tipo_recebimento = bancos::sql($sql);
            echo $campos_tipo_recebimento[0]['recebimento'];
        ?>
        </td>
        <td>
        <?
            //Verifica se existe Banco para esta da Conta
            $sql = "SELECT b.banco 
                    FROM `contas_receberes` cr 
                    INNER JOIN `bancos` b ON b.`id_banco` = cr.`id_banco` 
                    WHERE cr.`id_conta_receber` = '".$campos[$i]['id_conta_receber']."' LIMIT 1 ";
            $campos_banco = bancos::sql($sql);
            if(count($campos_banco) == 1) $nome_banco = $campos_banco[0]['banco'];
        ?>
        </td>
        <td>
            <?=$simbolo_moeda.' '.number_format($campos[$i]['valor_desconto'], 2, ',', '.');?>
        </td>
        <td>
            <?=$simbolo_moeda.' '.number_format($campos[$i]['valor_abatimento'], 2, ',', '.');?>
        </td>
        <td>
            <?=$simbolo_moeda.' '.number_format($campos[$i]['valor_despesas'], 2, ',', '.');?>
        </td>
        <td>
            <?=$simbolo_moeda.' '.number_format($campos[$i]['valor_reajustado'], 2, ',', '.');?>
        </td>
    </tr>
<?
            //Aqui verifica se essa via tamb�m est� atrelada a bordero ...
            $sql = "SELECT id_bordero 
                    FROM `contas_receberes` 
                    WHERE `id_conta_receber` = ".$campos[$i]['id_conta_receber']." 
                    AND `id_bordero` > '0' LIMIT 1 ";
            $campos_borderos = bancos::sql($sql);
            if(count($campos_borderos) == 1) $borderor[$i] = 1;
        }
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
    }
//Vou utilizar essas datas p/ fazer algumas compara��es com a Data de Emiss�o ...
    $datas          = genericas::retornar_data_relatorio(1);
    $data_final     = data::datatodate($datas['data_final'], '-');
    $data_icms      = date('Y-m-').'01';//Sempre � o dia 1 do M�s corrente ...
//Aqui nessa parte eu verifico se a Data de Emiss�o � Menor do que a Data Final de Comiss�o
//Data de Emiss�o � Menor, sendo assim eu verifico se j� foi feito um abatimento dakela Nota Fiscal ...
    if($data_emissao <= $data_final || $data_emissao < $data_icms) {
/******************Nova L�gica de Devolu��o******************/
//Verifica se essa conta � receber tem alguma Conta � Receber de Devolu��o atrelada ...
        $sql = "SELECT id_conta_receber 
                FROM `contas_receberes_vs_nfs_devolucoes` 
                WHERE `id_conta_receber` = '$_GET[id_conta_receber]' LIMIT 1 ";
        $campos_devolucao   = bancos::sql($sql);
        if(count($campos_devolucao) == 1) $devolucao = 1;
    }
//Aqui eu verifico se existe algum motivo o do porque n�o posso excluir alguma Duplicata
    for($i = 0; $i < $linhas; $i++) {
//Significa que possui alguma duplicata paga ou consta em borderor
        if($status[$i] == 1 || $borderor[$i] == 1) $atencao = 1;
    }
//Significa que existem motivos para n�o estar excluindo a Conta � Receber
    if($atencao == 1 || $devolucao == 1) {
?>
    <tr class='linhacabecalho'>
        <td colspan='4'>
            Duplicata desta Nota Fiscal n�o pode ser exclu�da. Motivos:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <font color='red'><b>
<?
//Devolu��o for = 1 ...
        if($devolucao == 1) {
                echo '-> O sistema detectou que j� foi pago a Comiss�o ao Representante ou precisa ser Creditado o ICMS da Nota, por favor criar devolu��o dessa comiss�o / Cr�dito ICMS. 
                <br><b>Data de Emiss�o da Conta:</b> '.data::datetodata($data_emissao, '/').' <= que a <b>Data da �ltima Comiss�o Paga:</b> '.data::datetodata($data_final, '/').' ou Data de Emiss�o da Conta < que a Data de Cr�dito ICMS '.data::datetodata($data_icms, '/');
        }
//Motivos o do porque n�o posso estar excluindo a Conta
        for($i = 0; $i < $linhas; $i++) {
            if($status[$i] == 1) echo '-> '.$campos[$i]['num_conta'].' possui parcela(s) recebida(s).';
            if($borderor[$i] == 1) echo '<br>-> '.$campos[$i]['num_conta'].' consta em borderor.';
        }
?>
            </b></font>
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhanormal'>
        <td>
            <b>Justificativa:</b>
        </td>
        <td colspan='3'>
            <textarea name='txt_justificativa' cols='85' rows='3' maxlength='255' class='caixadetexto'></textarea>
        </td>
    </tr>
<?
    }
//Significa que existem motivos o do porque n�o posso excluir a Duplicata
    if($atencao == 1 || $devolucao == 1) {
        $disabled   = 'disabled';
        $class      = 'disabled';
    }else {
        $class      = 'botao';
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='<?=$class;?>' <?=$disabled;?>>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='window.close()' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<!--Joguei essa function aqui em baixo, por causa da vari�vel em PHP $linhas que s� carreguei mais abaixo do <head> ...-->
<Script Language = 'JavaScript'>
function validar() {
//Se existir o Campo Justificativa, ent�o eu for�o o usu�rio a preencher este campo ...
    if(typeof(document.form.txt_justificativa) == 'object') {
//Justificativa se estiver vazia
        if(document.form.txt_justificativa.value == '') {
            alert('DIGITE A JUSTIFICATIVA !')
            document.form.txt_justificativa.focus()
            return false
        }
    }
    var linhas = eval('<?=$linhas;?>')
    if(linhas == 1) {//Nota Fiscal que possui apenas 1 via
        var resposta = confirm('VOC� TEM CERTEZA DE QUE DESEJA EXCLUIR ESSA DUPLICATA ?')
    }else {//Nota Fiscal que possui mais de 1 via
        var resposta = confirm('TODAS AS DUPLICATAS DESSA NOTA FISCAL SER�O EXCLU�DAS.\nVOC� TEM CERTEZA DE QUE DESEJA EXCLUIR ?')
    }
//Se o usu�rio desejar excluir realmente a Conta ent�o ...
    if(resposta == true) {
        return true
    }else {
        return false
    }
}
</Script>