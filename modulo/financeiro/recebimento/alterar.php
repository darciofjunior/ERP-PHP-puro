<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/financeiros.php');
require('../../../lib/genericas.php');
require('../../../lib/variaveis/intermodular.php');

if($id_emp == 1) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
    $endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../');

$mensagem[1] = "<font class='confirmacao'>CONSULTA À RECEBER ALTERADA COM SUCESSO.</font>";

if(!empty($_POST['id_conta_receber'])) {
//Se tiver a Data de Vencimento for alterada, então precisa ser modificada a Justificativa ...
    if(!empty($_POST['hdd_justificativa'])) {
//1)
/************************Busca de Dados************************/
        $data_ocorrencia = date('Y-m-d H:i:s');
        $txt_justificativa = '<font color="blue">Follow-Up Registrado automaticamente (E-mail) </font>';
//Aqui eu trago alguns dados de Conta à Receber p/ passar por e-mail via parâmetro ...
        $sql = "SELECT cr.`id_cliente`, IF(c.razaosocial = '', c.nomefantasia, c.razaosocial) AS cliente, 
                DATE_FORMAT(cr.`data_vencimento_alterada`, '%d/%m/%Y') AS data_vencimento_alterada, 
                cr.`id_empresa`, cr.`id_nf`, cr.`id_representante`, cr.`num_conta`, cr.`valor_abatimento`, 
                nfs.`comissao_media` 
                FROM `contas_receberes` cr 
                INNER JOIN `nfs` ON nfs.id_nf = cr.id_nf 
                INNER JOIN `clientes` c ON c.id_cliente = cr.id_cliente 
                WHERE cr.`id_conta_receber` = '$_POST[id_conta_receber]' LIMIT 1 ";
        $campos                     = bancos::sql($sql);
        $id_cliente                 = $campos[0]['id_cliente'];
        $cliente                    = $campos[0]['cliente'];
        $data_vencimento_alterada   = $campos[0]['data_vencimento_alterada'];
        //Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa ...
        $id_empresa_cr              = $campos[0]['id_empresa'];
        $id_nf                      = $campos[0]['id_nf'];
        $id_representante           = $campos[0]['id_representante'];
        $empresa                    = genericas::nome_empresa($id_empresa_cr);
        $num_conta                  = $campos[0]['num_conta'];
        $valor_abatimento           = $campos[0]['valor_abatimento'];
        $comissao_media             = $campos[0]['comissao_media'];
        
        //Dados p/ enviar por e-mail - Controle com as Mensagens de Alteração ...
        $dados_alterados = '';
        if($data_vencimento_alterada != $_POST['txt_data_vencimento_alterada']) $dados_alterados.= '<br><b>Data de Vencimento Alterada de: </b>'.$data_vencimento_alterada.' <b>para </b>'.$_POST['txt_data_vencimento_alterada'];
        
        /*******************************************************************************************************/
        /*******************************Controle referente à Estornos de Comissão*******************************/
        /*******************************************************************************************************/
        if($id_nf > 0) {
            if($valor_abatimento != $_POST['txt_valor_abatimento']) {
                //Verifico se já foi gerado algum registro de Abatimento na Tabela de Estorno de Comissões ...
                $sql = "SELECT `id_comissao_estorno` 
                        FROM `comissoes_estornos` 
                        WHERE `id_conta_receber` = '$_POST[id_conta_receber]' LIMIT 1 ";
                $campos_estorno = bancos::sql($sql);
                if(count($campos_estorno) == 0) {//Não existia registro de Abatimento, sendo assim gero um ...
                    //Esse registro que eu gero na tabela de Abatimento é utilizado no relatorio de Estorno de Comissoes ...
                    $sql = "INSERT INTO `comissoes_estornos` (`id_comissao_estorno`, `id_nf`, `id_conta_receber`, `id_representante`, `num_nf_devolvida`, `data_lancamento`, `tipo_lancamento`, `porc_devolucao`, `valor_duplicata`) 
                            VALUES (NULL, '$id_nf', '$_POST[id_conta_receber]', '$id_representante', '', '".date('Y-m-d')."', '2', '$comissao_media', '$_POST[txt_valor_abatimento]') ";
                    bancos::sql($sql);
                }else {//Já existia abatimento, sendo assim só atualizo ...
                    if($_POST['txt_valor_abatimento'] == 0) {//Como o funcionário resolveu tirar o Abatimento da Duplicata, deleto o Registro ...
                        //Esse registro que eu gero na tabela de Abatimento é utilizado no relatorio de Estorno de Comissoes ...
                        $sql = "DELETE FROM `comissoes_estornos` WHERE `id_comissao_estorno` = '".$campos_estorno[0]['id_comissao_estorno']."' LIMIT 1 ";
                        bancos::sql($sql);
                    }else {//Ainda tem algum valor de Abatimento, sendo assim atualizo o Registro de Abatimento ...
                        //Esse registro que eu gero na tabela de Abatimento é utilizado no relatorio de Estorno de Comissoes ...
                        $sql = "UPDATE `comissoes_estornos` SET `data_lancamento` = '".date('Y-m-d')."', `valor_duplicata` = '$_POST[txt_valor_abatimento]' WHERE `id_comissao_estorno` = '".$campos_estorno[0]['id_comissao_estorno']."' LIMIT 1 ";
                        bancos::sql($sql);
                    }
                }
                $dados_alterados.= '<br><b>Valor Abatimento Alterado de: </b>'.number_format($valor_abatimento, 2, ',', '.').' <b>para </b>'.number_format($_POST['txt_valor_abatimento'], 2, ',', '.');
            }
            $observacao_follow_up = $txt_justificativa.' - '.$dados_alterados.' - <b>N.º da Conta: </b>'.$num_conta.' - <b>Justificativa: </b>'.$hdd_justificativa;
        
//Registrando Follow-UP(s) ...
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', '$id_representante', '$_SESSION[id_funcionario]', '$id_conta_receber_loop', '4', '$observacao_follow_up', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        /*******************************************************************************************************/

//Só não irá enviar esse e-mail quando for a própria da Dona Sandra que estiver fazendo essa ação ...
        if($_SESSION['id_funcionario'] != 66) {
//2)
/************************E-mail************************/
/*
//-Se o Usuário estiver alterando a Conta à Receber do Financeiro, então o Sistema dispara um e-mail 
informando qual a Conta à Receber que está sendo alterada ...
//-Aqui eu trago alguns dados de Conta à Receber p/ passar por e-mail via parâmetro ...
//-Aqui eu busco o login de quem está alterando a Conta à Receber ...*/
            $sql = "SELECT login 
                    FROM `logins` 
                    WHERE `id_login` = '$_SESSION[id_login]' LIMIT 1 ";
            $campos_login       = bancos::sql($sql);
            $login_alterando    = $campos_login[0]['login'];
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
            $complemento_justificativa = '<br><b>Empresa: </b>'.$empresa.' <br><b>Cliente: </b>'.$cliente.' <br><b>N.º da Conta: </b>'.$num_conta.' <br><b>Login: </b>'.$login_alterando;
            $txt_justificativa.= $complemento_justificativa.$dados_alterados.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$hdd_justificativa.'<br>'.$PHP_SELF;
//Os e-mails estão especificados dentro da biblioteca intermodular na pasta variáveis ...
            $destino = $abatimento_financeiro;
            comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Alteração de Dados da Conta à Receber', $txt_justificativa);
        }
    }
//3)
/********************Controle de Datas********************/
/*Essas variáveis estarei utilizando mais abaixo na linha 156, são de extrema importância porque irão controlar a chamada da função 
financeiros::atualizar_data_alterada ...*/
    $sql = "SELECT DATE_FORMAT(`data_vencimento`, '%d/%m/%Y') AS data_vencimento, 
            DATE_FORMAT(`data_vencimento_alterada`, '%d/%m/%Y') AS data_vencimento_alterada 
            FROM `contas_receberes` 
            WHERE `id_conta_receber` = '$_POST[id_conta_receber]' LIMIT 1 ";
    $campos                     = bancos::sql($sql);
    $data_vencimento            = $campos[0]['data_vencimento'];
    $data_vencimento_alterada   = $campos[0]['data_vencimento_alterada'];
//4)
/************************Alteração************************/
/*Aqui é só quando a empresa for do Tipo Grupo, eu faço esse macete porque não existe caixa de texto 
para essa empresa, e sim o que existe é uma combo no lugar*/
    $descricao_conta    = (isset($_POST['cmb_descricao_conta'])) ? $_POST['cmb_descricao_conta'] : $_POST['txt_descricao_conta'];
    $dia                = substr($_POST['txt_data_vencimento_alterada'], 0, 2);
    $mes                = substr($_POST['txt_data_vencimento_alterada'], 3, 2);
    $ano                = substr($_POST['txt_data_vencimento_alterada'], 6, 4);
    $semana             = data::numero_semana($dia, $mes, $ano);
    $manual             = ($_POST['chkt_habilitar_juros'] == 1) ? 1 : 0;
    $data_sys           = date('Y-m-d H:i:s');//Aqui serve para registrar a última alteração da Conta ...
    
    if(empty($_POST['chkt_previsao'])) $_POST['chkt_previsao'] = 0;   
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_banco          = (!empty($_POST[cmb_banco])) ? "'".$_POST[cmb_banco]."'" : 'NULL';

//Pode alterar todos os campos, porque não foi paga nenhuma parcela daquela conta
    $sql = "UPDATE `contas_receberes` SET `id_tipo_recebimento` = '$_POST[id_tipo_recebimento]', `id_banco` = $cmb_banco, `id_funcionario` = '$_SESSION[id_funcionario]', `descricao_conta` = '$descricao_conta', `semana` = '$semana', `previsao` = '$_POST[chkt_previsao]', `data_vencimento_alterada` = '".data::datatodate($_POST['txt_data_vencimento_alterada'], '-')."', `valor` = '$_POST[txt_valor]', `valor_abatimento` = '$_POST[txt_valor_abatimento]', `taxa_juros` = '$_POST[txt_taxa_juros]', `valor_juros` = '$_POST[txt_valor_juros]', `valor_despesas` = '$_POST[txt_valor_despesas]', `comissao_estornada` = '$_POST[txt_comissao_estornada]', `manual` = '$manual', `data_sys` = '$data_sys' WHERE `id_conta_receber` = '$_POST[id_conta_receber]' LIMIT 1 ";
    bancos::sql($sql);
    
    /*Se a "Data de Vencimento Original" = "Data de Vencimento Alterada", isso acontece quando a Conta à Receber acaba de ser inclusa 
    ou houve modificação na "Data de Vencimento Alterada" por parte do Usuário ...*/
    if(($data_vencimento == $data_vencimento_alterada) || ($data_vencimento_alterada != $_POST['txt_data_vencimento_alterada'])) financeiros::atualizar_data_alterada($_POST[id_conta_receber], 'R');
    $valor = 1;
}

$id_conta_receber = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_conta_receber'] : $_GET['id_conta_receber'];

//Seleção dos dados de contas à receber
$sql = "SELECT cr.*, c.`id_pais`, c.`razaosocial`, tr.`status` AS status_recebimento 
        FROM `contas_receberes` cr 
        INNER JOIN `clientes` c ON c.`id_cliente` = cr.`id_cliente` 
        INNER JOIN `tipos_recebimentos` tr ON tr.`id_tipo_recebimento` = cr.`id_tipo_recebimento` 
        WHERE cr.`id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
$campos                         = bancos::sql($sql);
$id_pais                        = $campos[0]['id_pais'];
$cliente                        = $campos[0]['razaosocial'];
$id_tipo_recebimento            = $campos[0]['id_tipo_recebimento'];
$id_cliente                     = $campos[0]['id_cliente'];
$id_tipo_moeda 			= $campos[0]['id_tipo_moeda'];
$id_banco 			= $campos[0]['id_banco'];
$id_nf                          = $campos[0]['id_nf'];
$id_nf_outra                    = $campos[0]['id_nf_outra'];
$id_representante               = $campos[0]['id_representante'];
$num_conta                      = $campos[0]['num_conta'];
$descricao_conta 		= $campos[0]['descricao_conta'];
$data_emissao 			= data::datetodata($campos[0]['data_emissao'], '/');
$data_vencimento                = data::datetodata($campos[0]['data_vencimento'], '/');
$data_vencimento_alterada       = data::datetodata($campos[0]['data_vencimento_alterada'], '/');
$data_recebimento               = data::datetodata($campos[0]['data_recebimento'], '/');

if($campos[0]['data_bl'] != '0000-00-00') $data_bl = data::datetodata($campos[0]['data_bl'], '/');

$valor_conta 			= $campos[0]['valor'];
$valor_desconto 		= ($campos[0]['valor_desconto'] != '0.00' && $campos[0]['valor_desconto'] != '') ? number_format($campos[0]['valor_desconto'], '2', ',', '') : '';
$valor_abatimento 		= number_format($campos[0]['valor_abatimento'], '2', ',', '');
$taxa_juros 			= ($campos[0]['taxa_juros'] != '0.00' && $campos[0]['taxa_juros'] != '') ? number_format($campos[0]['taxa_juros'], '2', ',', '') : '';
$valor_juros 			= ($campos[0]['valor_juros'] != '0.00' && $campos[0]['valor_juros'] != '') ? number_format($campos[0]['valor_juros'], '2', ',', '') : '';
$valor_despesas 		= ($campos[0]['valor_despesas'] != '0.00' && $campos[0]['valor_despesas'] != '') ? number_format($campos[0]['valor_despesas'], '2', ',', '') : '';
$manual                         = $campos[0]['manual'];
$status                         = $campos[0]['status_recebimento'];

$id_tipo_recebimento_status     = $id_tipo_recebimento.'|'.$status;

$simbolo_moeda                  = ($id_pais == 31) ? 'R$ ' : 'U$ ';

//Puxa o último valor de Dólar e Euro cadastrado ...
$valor_dolar 	= genericas::moeda_dia('dolar');
$valor_euro 	= genericas::moeda_dia('euro');

/********************************************************************************************/
//Verifico se tenho alguma NF de Devolução importada p/ essa Duplicata em Questão ...
$sql = "SELECT SUM(valor_devolucao) AS total_devolucao_importada 
        FROM `contas_receberes_vs_nfs_devolucoes` 
        WHERE `id_conta_receber` = '$id_conta_receber' ";
$campos_devolucao_importada = bancos::sql($sql);
$valor_abat_devolucao       = number_format($campos_devolucao_importada[0]['total_devolucao_importada'], 2, ',', '');
/********************************************************************************************/
?>
<html>
<head>
<title>.:: Alterar Conta à Receber ::.</title>
<meta http-equiv ='Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv ='pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function taxa() {
    if(document.form.cmb_descricao_conta.value == 'PD SEM BOLETO') {
        document.form.txt_taxa_juros.value = '5,00'
    }else {
        document.form.txt_taxa_juros.value = '<?=$taxa_juros;?>'
    }
}

function calcular(linha) {
    //Vem por parâmetro do Pop-UP Div ...
    var get_abatimento = '<?=$_GET['valor_abatimento'];?>'
    if(get_abatimento != '') {
        document.form.txt_valor_abatimento.value = get_abatimento
    }else {    
        var tipo_moeda = document.form.cmb_tipo_moeda.value
//Aqui é a rotina para calcular quando eu vou digitando nas caixas de texto mesmo
	if(typeof(linha) != 'undefined') {
//Significa que a Data foi digitada de modo completo
            if(document.form.txt_data_vencimento_alterada.value.length == '10') {
                var valor = (document.form.txt_valor.value != '') ? eval(strtofloat(document.form.txt_valor.value)) : 0
                if(tipo_moeda == 2) {
                    valor*= eval('<?=$valor_dolar;?>')
                }else if(tipo_moeda == 3) {
                    valor*= eval('<?=$valor_euro;?>')
                }else {
                    valor*= 1
                }
//Desconto
                var desconto = (document.form.txt_valor_desconto.value != '') ? eval(strtofloat(document.form.txt_valor_desconto.value)) : 0
                if(tipo_moeda == 2) {
                    desconto*= eval('<?=$valor_dolar;?>')
                }else if(tipo_moeda == 3) {
                    desconto*= eval('<?=$valor_euro;?>')
                }else {
                    desconto*= 1
                }
//Abatimento
                var abatimento = (document.form.txt_valor_abatimento.value != '') ? eval(strtofloat(document.form.txt_valor_abatimento.value)) : 0
                if(tipo_moeda == 2) {
                    abatimento*= eval('<?=$valor_dolar;?>')
                }else if(tipo_moeda == 3) {
                    abatimento*= eval('<?=$valor_euro;?>')
                }else {
                    abatimento*= 1
                }
//Abatimento Devolvido ...
                var valor_abat_devolucao = '<?=$valor_abat_devolucao;?>'
                //Se o Valor for Nulo ...
                var valor_abat_devolucao = (typeof(valor_abat_devolucao) == 'undefined') ? 0 : eval(strtofloat('<?=$valor_abat_devolucao;?>'))
                if(tipo_moeda == 2) {
                    valor_abat_devolucao*= eval('<?=$valor_dolar;?>')
                }else if(tipo_moeda == 3) {
                    valor_abat_devolucao*= eval('<?=$valor_euro;?>')
                }else {
                    valor_abat_devolucao*= 1
                }
//Taxa Juros
                var taxa_juros = (document.form.txt_taxa_juros.value != '') ? eval(strtofloat(document.form.txt_taxa_juros.value)) : 0
                if(taxa_juros > 0) {
//A variável dias equivale a data atual até a data de vecimento
                    dias = diferenca_datas('document.form.txt_data_vencimento_alterada', 'document.form.txt_data_atual')
                    taxa_juros_dias_venc = (taxa_juros / 100) / 30 * dias
                }else {
                    taxa_juros_dias_venc = 0
                }
//Valor Despesas
                var valor_despesas = (document.form.txt_valor_despesas.value != '') ? eval(strtofloat(document.form.txt_valor_despesas.value)) : 0
                if(tipo_moeda == 2) {
                    valor_despesas*= eval('<?=$valor_dolar;?>')
                }else if(tipo_moeda == 3) {
                    valor_despesas*= eval('<?=$valor_euro;?>')
                }else {
                    valor_despesas*= 1
                }
                var valores = eval(valor) - desconto - abatimento - valor_abat_devolucao
//Valor Juros estiver desabilitado
                if(document.form.txt_valor_juros.disabled == false) {
                    var valor_juros = (document.form.txt_valor_juros.value != '') ? eval(strtofloat(document.form.txt_valor_juros.value)) : 0
                    if(tipo_moeda == 2) {
                        valor_juros*= eval('<?=$valor_dolar;?>')
                    }else if(tipo_moeda == 3) {
                        valor_juros*= eval('<?=$valor_euro;?>')
                    }else {
                        valor_juros*= 1
                    }
                    document.form.txt_valor_reajustado.value = (valores) + eval(valor_juros) + eval(valor_despesas)
                }else {
//Valor Juros
//Se o usuário preencher a taxa de juros, então aparece o valor de juros
                    if(document.form.txt_taxa_juros.value != '') {
                        /*Nunca o Juros pode ser negativo porque significa que nós daqui da Albafer ainda 
                        estamos devendo juros p/ o Cliente ...*/
                        if(valores < 0) valores = 0
                        document.form.txt_valor_juros.value = valores * taxa_juros_dias_venc
                        document.form.txt_valor_juros.value = arred(document.form.txt_valor_juros.value, 2, 1)
                    }
                    //As despesas é o único campo que não pode levar em conta os Juros ...
                    document.form.txt_valor_reajustado.value = (valores) * (taxa_juros_dias_venc + 1) + eval(valor_despesas)
                }
                document.form.txt_valor_reajustado.value = arred(document.form.txt_valor_reajustado.value, 2, 1)
            }
	}else {
//Aqui é a rotina para calcular quando eu mudo o tipo de moeda na combo
            if(document.form.txt_valor.value != '') {
//Valor
                var valor = (document.form.txt_valor.value != '') ? eval(strtofloat(document.form.txt_valor.value)) : 0
                if(tipo_moeda == 2) {
                    valor*= eval('<?=$valor_dolar;?>')
                }else if(tipo_moeda == 3) {
                    valor*= eval('<?=$valor_euro;?>')
                }else {
                    valor*= 1
                }
//Desconto
                var desconto = (document.form.txt_valor_desconto.value != '') ? eval(strtofloat(document.form.txt_valor_desconto.value)) : 0
                if(tipo_moeda == 2) {
                    desconto*= eval('<?=$valor_dolar;?>')
                }else if(tipo_moeda == 3) {
                    desconto*= eval('<?=$valor_euro;?>')
                }else {
                    desconto*= 1
                }
//Abatimento
                var abatimento = (document.form.txt_valor_abatimento.value != '') ? eval(strtofloat(document.form.txt_valor_abatimento.value)) : 0
                if(tipo_moeda == 2) {
                    abatimento*= eval('<?=$valor_dolar;?>')
                }else if(tipo_moeda == 3) {
                    abatimento*= eval('<?=$valor_euro;?>')
                }else {
                    abatimento*= 1
                }
//Abatimento Devolvido ...
                var valor_abat_devolucao = '<?=$valor_abat_devolucao;?>'
                //Se o Valor for Nulo ...
                var valor_abat_devolucao = (typeof(valor_abat_devolucao) == 'undefined') ? 0 : eval(strtofloat('<?=$valor_abat_devolucao;?>'))
                if(tipo_moeda == 2) {
                    valor_abat_devolucao*= eval('<?=$valor_dolar;?>')
                }else if(tipo_moeda == 3) {
                    valor_abat_devolucao*= eval('<?=$valor_euro;?>')
                }else {
                    valor_abat_devolucao*= 1
                }
//Taxa Juros
                var taxa_juros = (document.form.txt_taxa_juros.value != '') ? eval(strtofloat(document.form.txt_taxa_juros.value)) : 0

                if(taxa_juros > 0) {
//A variável dias equivale a data atual até a data de vecimento
                    dias = diferenca_datas('document.form.txt_data_vencimento_alterada', 'document.form.txt_data_atual')
                    taxa_juros_dias_venc = (taxa_juros / 100) / 30 * dias
                }else {
                    taxa_juros_dias_venc = 0
                }
//Valor Despesas
                var valor_despesas = (document.form.txt_valor_despesas.value != '') ? eval(strtofloat(document.form.txt_valor_despesas.value)) : 0
                if(tipo_moeda == 2) {
                    valor_despesas*= eval('<?=$valor_dolar;?>')
                }else if(tipo_moeda == 3) {
                    valor_despesas*= eval('<?=$valor_euro;?>')
                }else {
                    valor_despesas*= 1
                }
                var valores = eval(valor) - desconto - abatimento - valor_abat_devolucao
//Valor Juros estiver desabilitado
                if(document.form.txt_valor_juros.disabled == false) {
                    var valor_juros = (document.form.txt_valor_juros.value != '') ? eval(strtofloat(document.form.txt_valor_juros.value)) : 0
                    if(tipo_moeda == 2) {
                        valor_juros*= eval('<?=$valor_dolar;?>')
                    }else if(tipo_moeda == 3) {
                        valor_juros*= eval('<?=$valor_euro;?>')
                    }else {
                        valor_juros*= 1
                    }
                    //As despesas é o único campo que não pode levar em conta os Juros ...
                    document.form.txt_valor_reajustado.value = (valores) + eval(valor_juros) + eval(valor_despesas)
                }else {
//Valor Juros
//Se o usuário preencher a taxa de juros, então aparece o valor de juros
                    if(document.form.txt_taxa_juros.value != '') {
                        /*Nunca o Juros pode ser negativo porque significa que nós daqui da Albafer ainda 
                        estamos devendo juros p/ o Cliente ...*/
                        if(valores < 0) valores = 0
                        document.form.txt_valor_juros.value = valores * taxa_juros_dias_venc
                        document.form.txt_valor_juros.value = arred(document.form.txt_valor_juros.value, 2, 1)
                    }
                    document.form.txt_valor_reajustado.value = (valores) * (taxa_juros_dias_venc + 1) + eval(valor_despesas)
                }
                document.form.txt_valor_reajustado.value = arred(document.form.txt_valor_reajustado.value, 3, 1)
                document.form.txt_valor_reajustado.value = arred(document.form.txt_valor_reajustado.value, 2, 1)
            }
	}
    }
}

function calcular_credito_debito() {
    var valor = eval('<?=abs($valor_conta);?>')//Sempre pego o "Valor Positivo" do Valor q foi gravado na Base ...
    //var valor_reajutado = eval('<?=$valor_reajustado;?>')
        
    //Crédito ou Débito ...
    document.form.txt_valor.value = (document.form.opt_credito_debito[0].checked == true) ? (valor * -1) : valor
    document.form.txt_valor.value = arred(document.form.txt_valor.value, 2, 1)
    calcular()
}

function validar() {
//Tipo de Recebimento
    if(!combo('form', 'cmb_tipo_recebimento', '', 'SELECIONE UM TIPO DE RECEBIMENTO !')) {
        return false
    }
//Força o Banco
    if(document.form.status.value == 1) {
        if(document.form.cmb_banco.value == '') {
            alert('SELECIONE O BANCO !')
            document.form.cmb_banco.focus()
            return false
        }
    }
//Tipo de Moeda
    if(!combo('form', 'cmb_tipo_moeda', '', 'SELECIONE O TIPO DA MOEDA !')) {
        return false
    }
<?
//Significa que está Conta à Receber é do Tipo Manual "Crédito(s) / Débito(s) Financeiro(s)" ...
    if(is_null($id_nf) && is_null($id_nf_outra)) {
?>
        if(document.form.txt_descricao_conta.value != '') {
            if(!texto('form', 'txt_descricao_conta', '2', '-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ', 'DESCRIÇÃO DA CONTA', '1')) {
                return false
            }
        }
<?
    }else {//Duplicatas de NF ou NF Outra(s) ...
        //P/ as empresas Albafer e Tool Master, traz a caixa de texto de descrição da conta ...
        if($id_emp != 4) {
?>
            if(document.form.txt_descricao_conta.value != '') {
                if(!texto('form', 'txt_descricao_conta', '2', '-=!@¹²³£¢¬{}1234567890qwertyuiopçlkjhgfdsazxcvbnmQWERTYUIOPLKÇJ.|HGFDSAZXCVBNM,.Üüáé§íóúÁÉÍÀàºÓÚâêîôûÂÊÎÔÛãõÃÕ{[]}.,%&*$()@#<>ªº°:;\/ ', 'DESCRIÇÃO DA CONTA', '1')) {
                    return false
                }
            }
<?
        }else {
?>
            if(!combo('form', 'cmb_descricao_conta', '', 'SELECIONE A DESCRIÇÃO DA CONTA !')) {
                return false
            }
<?
        }
    }
?>
//Data de Vencimento Alterada ...
    if(!data('form', 'txt_data_vencimento_alterada', '4000', 'VENCIMENTO ALTERADA')) {
        return false
    }
/******************Controle com algum Dado q foi alterado pelo usuário******************/
//Aqui eu verifico se foi alterado algum desses dados pelo usuário ...
    var data_vencimento                 = '<?=$data_vencimento;?>'//Q carrega do BD diretamente ...
    var data_vencimento_alterada_bd     = '<?=$data_vencimento_alterada;?>'//Q carrega do BD diretamente ...
    var valor_conta_bd                  = '<?=$valor_conta;?>'
    var valor_abatimento_bd             = '<?=$valor_abatimento;?>'
    var id_nf                           = '<?=$id_nf;?>'
    var id_nf_outra                     = '<?=$id_nf_outra;?>'

    /*Se não existir NF ou NF Outra, representa que essa Conta à Receber foi feita de Forma Manual pela 
    opção "Incluir Crédito(s) / Débito(s) Financeiro(s)" ...*/
    var conta_forma_manual              = (id_nf == '' && id_nf_outra == '') ? 'S' : 'N'

    var data_vencimento_alterada_dg     = document.form.txt_data_vencimento_alterada.value//Digitado pelo usuário ...
    var valor_conta_dg                  = document.form.txt_valor.value
    var valor_abatimento_dg             = document.form.txt_valor_abatimento.value
    
//Primeira verificação ...
    data_vencimento_usa                 = data_vencimento.substr(6, 4) + data_vencimento.substr(3, 2) + data_vencimento.substr(0, 2)
    data_vencimento_alterada_usa        = data_vencimento_alterada_dg.substr(6, 4) + data_vencimento_alterada_dg.substr(3, 2) + data_vencimento_alterada_dg.substr(0, 2)

    //Nunca a "Data de Vencimento Original" pode ser maior do que a "Data de Vencimento Alterada" ...
    if(data_vencimento_usa > data_vencimento_alterada_usa) {
        alert('DATA DE VENCIMENTO ALTERADA INVÁLIDA !!!\n\n"DATA DE VENCIMENTO ALTERADA" TEM QUE SER MAIOR OU IGUAL A "DATA DE VENCIMENTO ORIGINAL" !')
        document.form.txt_data_vencimento_alterada.focus()
        document.form.txt_data_vencimento_alterada.select()
        return false
    }

//Segunda Verificação - verifico se a "Data de Vencimento" ou "Valor da Conta" ou "Valor de Abatimento" foram alterados pelo usuário ...
    if((data_vencimento_alterada_bd != data_vencimento_alterada_dg) || (valor_conta_bd != valor_conta_dg && conta_forma_manual == 'S') || (valor_abatimento_bd != valor_abatimento_dg)) {
//Verifico se a Data de Vencimento foi alterada pelo usuário ...
        var justificativa = prompt('DIGITE UMA JUSTIFICATIVA P/ ALTERAÇÃO DE DADO(S): ')
        document.form.hdd_justificativa.value = justificativa
//Controle com a Justificativa ...
        if(document.form.hdd_justificativa.value == '' || document.form.hdd_justificativa.value == 'null' || document.form.hdd_justificativa.value == 'undefined') {
            alert('JUSTIFICATIVA INVÁLIDA !!!\nDIGITE UMA JUSTIFICATIVA P/ ALTERAÇÃO DE DADO(S) !')
            return false
        }
    }
/*********************************************************************/
//Aqui desabilita os campos para poder gravar no BD
    document.form.txt_valor.disabled        = false
    document.form.txt_valor_juros.disabled  = false
//Aqui é para não atualizar o frame de Itens abaixo desse Pop-UP
    document.form.nao_atualizar.value           = 1
    return limpeza_moeda('form', 'txt_valor_abatimento, txt_taxa_juros, txt_valor, txt_valor_juros, txt_valor_despesas, ')
}

function separar() {
    var tipo_recebimento = document.form.cmb_tipo_recebimento.value
    var achou = 0, id_tipo_recebimento = '', status = ''
    for(i = 0; i < tipo_recebimento.length; i++) {
        if(tipo_recebimento.charAt(i) == '|') {
            achou = 1
        }else {
            if(achou == 0) {
                id_tipo_recebimento = id_tipo_recebimento + tipo_recebimento.charAt(i)
            }else {
                status = status + tipo_recebimento.charAt(i)
            }
        }
    }
    document.form.id_tipo_recebimento.value = id_tipo_recebimento
    document.form.status.value = status
    if(document.form.status.value == 0) {
        document.form.cmb_banco.disabled    = true
        document.form.cmb_banco.className   = 'textdisabled'
        document.form.cmb_banco.value = ''
    }else {
        document.form.cmb_banco.disabled    = false
        document.form.cmb_banco.className   = 'combo'
    }
}

function habilitar_valor_juros() {
    if(document.form.chkt_habilitar_juros.checked == true) {//Designer de Desabilitado
        document.form.txt_taxa_juros.value      = ''
        document.form.txt_taxa_juros.disabled   = true
        document.form.txt_taxa_juros.className  = 'textdisabled'
        //Designer de Habilitado
        document.form.txt_valor_juros.disabled  = false
        document.form.txt_valor_juros.className = 'caixadetexto'
        document.form.txt_valor_juros.focus()
    }else {//Designer de Habilitado
        document.form.txt_taxa_juros.value      = '<?=$taxa_juros;?>'
        document.form.txt_taxa_juros.disabled   = false
        document.form.txt_taxa_juros.className  = 'caixadetexto'
//Designer de Desabilitado
        document.form.txt_valor_juros.disabled  = true
        document.form.txt_valor_juros.className = 'textdisabled'
        document.form.txt_taxa_juros.focus()
        calcular()
    }
}

//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        opener.parent.itens.recarregar_tela()
    }
}
</Script>
</head>
<body onload='separar();habilitar_valor_juros();calcular()' onunload='atualizar_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--**********************************************-->
<input type='hidden' name='id_tipo_recebimento' value='<?=$id_tipo_recebimento;?>'>
<input type='hidden' name='status' value='<?=$status;?>'>
<input type='hidden' name='id_conta_receber' value='<?=$id_conta_receber;?>'>
<input type='hidden' name='txt_data_atual' value='<?=date('d/m/Y');?>'>
<!--Controle de Tela-->
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='hdd_justificativa'>
<!--**********************************************-->
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1'>
    <tr class='atencao' align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Conta à Receber
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <?
        if($id_cliente > 0 && is_null($id_nf) && is_null($id_nf_outra)) {
            if($valor_conta < 0) {
                $opt_credito = 'checked';
            }else {
                $opt_debito = 'checked';
            }
    ?>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            <input type='radio' name='opt_credito_debito' id='opt_credito' value='C' onclick='calcular_credito_debito()' <?=$opt_credito;?>>
            <label for='opt_credito'>
                Crédito
            </label>
            <font color='yellow'>
                <b>(DUPL. CEDIDA, ANTECIPAÇÃO)</b>
            </font>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type='radio' name='opt_credito_debito' id='opt_debito' value='D' onclick='calcular_credito_debito()' <?=$opt_debito;?>>
            <label for='opt_debito'>
                Débito
            </label>
            <font color='yellow'>
                <b>(CQ DEVOLVIDO DEP. EM C/C PELO CLIENTE)</b>
            </font>
        </td>
    </tr>
    <?
        }
    ?>
    <tr class='linhanormal'>
        <td>
            Descrição da Conta:
        </td>
        <td>
            <a href="javascript:nova_janela('a_receber/classes/liberar_nota/alterar_data_bl.php?id_conta_receber=<?=$id_conta_receber;?>', 'ALTERAR_BL', '', '', '', '', 300, 800, 'c', 'c')" title='Alterar Data do B/L' class='link'>
                <font color='red' size='-1'>
                    Alterar Data do B/L
                </font>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            //Significa que está Conta à Receber é do Tipo Manual "Crédito(s) / Débito(s) Financeiro(s)" ...
            if(is_null($id_nf) && is_null($id_nf_outra)) {
        ?>
                <input type='text' name='txt_descricao_conta' value='<?=$campos[0]['descricao_conta'];?>' title='Digite a Descrição da Conta' maxlength='50' size='55' class='caixadetexto'>
        <?
            }else {//Duplicatas de NF ou NF Outra(s) ...
                //P/ as empresas Albafer e Tool Master, traz a caixa de texto de descrição da conta ...
                if($id_emp != 4) {
        ?>
                <input type='text' name='txt_descricao_conta' value='<?=$descricao_conta;?>' title='Digite a Descrição da Conta' maxlength='50' size='55' class='caixadetexto'>
        <?
                }else {
        ?>
                <select name='cmb_descricao_conta' title='Selecione a Descrição da Conta' onchange='taxa()' class='combo'>
                    <option value='' style='color:red'>SELECIONE</option>
                    <?
                        if($descricao_conta == 'NE') {
                            $selected0 = 'selected';
                        }else if($descricao_conta == 'PED S/ BOLETO') {
                            $selected1 = 'selected';
                        }else if($descricao_conta == 'PED C/ BOLETO') {
                            $selected2 = 'selected';
                        }else if($descricao_conta == 'CHEQUE DEVOLVIDO') {
                            $selected3 = 'selected';
                        }
                    ?>
                    <option value='NE' <?=$selected0;?>>NE</option>
                    <option value='PED S/ BOLETO' <?=$selected1;?>>PED S/ BOLETO</option>
                    <option value='PED C/ BOLETO' <?=$selected2;?>>PED C/ BOLETO</option>
                    <option value='CHEQUE DEVOLVIDO' <?=$selected3;?>>CHEQUE DEVOLVIDO</option>
                </select>
        <?
                }
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cliente</b>
        </td>
        <td>
            <b>N.º da Conta / Nota</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font size='-2'>
                <?=$cliente;?>
            </font>
        </td>
        <td>
            <a href="javascript:html5Lightbox.showLightbox(7, '../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$id_nf;?>&pop_up=1')" class='link'>
                <?=$num_conta;?>
            </a>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Recebimento</b>
        </td>
        <td>
            Banco
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_recebimento' title='Selecione o Tipo de Recebimento' onchange='separar()' class='combo'>
            <?
                $sql = "SELECT CONCAT(`id_tipo_recebimento`, '|', `status`) AS dados, `recebimento` 
                        FROM `tipos_recebimentos` 
                        WHERE `ativo` = '1' ORDER BY `recebimento` ";
                echo combos::combo($sql, $id_tipo_recebimento_status);
            ?>
            </select>
            <?
                if($campos[0]['previsao'] == 1) $checked_previsao = 'checked';
            ?>
            &nbsp;<input type='checkbox' name='chkt_previsao' value='1' id='label' class='checkbox' <?=$checked_previsao;?>>
            <label for='label'>Previsão</label>
        </td>
        <td>
            <select name='cmb_banco' title='Selecione o Banco' class='combo'>
            <?
                $sql = "SELECT `id_banco`, `banco` 
                        FROM `bancos` 
                        WHERE `ativo` = '1' 
                        ORDER BY `banco` ";
                echo combos::combo($sql, $id_banco);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo da Moeda</b>
        </td>
        <td>
            Comissão Estornada:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <select name='cmb_tipo_moeda' title='Tipo de Moeda' class='textdisabled' disabled>
            <?
                $sql = "SELECT id_tipo_moeda, CONCAT(simbolo, ' - ', moeda) AS moeda 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' ";
                echo combos::combo($sql, $id_tipo_moeda);
            ?>
            </select>
        </td>
        <td>
            <input type='text' name='txt_comissao_estornada' value='<?=$campos[0]['comissao_estornada'];?>' title='Digite a Comissão Estornada' size='3' maxlength='2' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            Representante
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <select name='cmb_representante' title='Representante' class='textdisabled' disabled>
            <?
                $sql = "SELECT id_representante, CONCAT(nome_fantasia, ' / ', zona_atuacao) AS dados 
                        FROM `representantes` 
                        WHERE `ativo` = '1' ORDER BY nome_fantasia ";
                echo combos::combo($sql, $id_representante);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='blue'>
                Valor Dólar:
            </font>
            <?='R$ '.number_format($valor_dolar, 4, ',', '.');?>
        </td>
        <td>
            <font color="blue">
                Valor Euro:
            </font>
            <?='R$ '.number_format($valor_euro, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Valor Nacional / Estrangeiro</b>
        </td>
        <td>
            Valor Desconto Duplicata
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_valor' value='<?=number_format($valor_conta, '2', ',', '');?>' size='20' maxlength='15' title='Valor' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_valor_desconto' value='<?=$valor_desconto;?>' size='20' maxlength='15' title='Valor Desconto Duplicata' class='textdisabled' disabled>
        </td>
    </tr>
<?
/*******************************************************************************************/
//Verifico se existe(m) NF(s) de Devoluções p/ essa Duplicata ...
	$sql = "SELECT * 
                FROM `contas_receberes_vs_nfs_devolucoes` 
                WHERE `id_conta_receber` = '$id_conta_receber' ";
        $campos_nota    = bancos::sql($sql);
        $linhas_nota    = count($campos_nota);
        if($linhas_nota > 0) {
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            NF(s) Devolvida(s) p/ essa Duplicata
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º da NF de Devolução
        </td>
        <td>
            Valor da Devolução
        </td>
    </tr>
	<?
            $total_devolucao = 0;
            for($i = 0; $i < $linhas_nota; $i++) {
	?>
    <tr class='linhanormal'>
        <td align='center'>
            <a href = 'estornar_abatimento_devolucao.php?id_conta_receber_nf_devolucao=<?=$campos_nota[$i]['id_conta_receber_nf_devolucao'];?>&id_emp=<?=$id_emp;?>' title='Estornar Abatimento de Devolução' style='cursor:help' class='link'>
                <img src = '../../../imagem/seta_acima.gif' width='12' height='12' border='0' alt='Estornar NF de Devolução' style='cursor:hand'>
                <font color='red'>
                    ESTORNAR
                </font>
            </a>
            &nbsp;|&nbsp;
            <a href="javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos_nota[$i]['id_nf_devolucao'];?>&pop_up=1', 'DETALHES', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes da NF de Devolução' style='cursor:help' class='link'>
                <?=faturamentos::buscar_numero_nf($campos_nota[$i]['id_nf_devolucao'], 'D');?>
            </a>
        </td>
        <td align='right'>
            <?=$simbolo_moeda.number_format($campos_nota[$i]['valor_devolucao'], 2, ',', '.');?>
        </td>
    </tr>
<?
                $total_devolucao+= $campos_nota[$i]['valor_devolucao'];
            }
?>
    <tr class='linhadestaque' align='right'>
        <td>
            Total Devolvido => 
        </td>
        <td>
            <?=$simbolo_moeda.number_format($total_devolucao, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
<?
	}
/*******************************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            Valor Abatimento
        </td>
        <td>
            Taxa Juros
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_valor_abatimento' value='<?=$valor_abatimento;?>' title='Digite o Valor Abatimento' size='20' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular(1)" class='caixadetexto'>
            <?
                //Esse botão só aparecerá se a Data de Vencimento Alterada da Duplicata for maior que a Data de Emissão ...
                if(data::datatodate($data_vencimento_alterada, '-') >= date('Y-m-d')) {
            ?>
            &nbsp;<input type='button' name='cmd_calcular_abatimento' value='Calcular Abatimento' title='Calcular Abatimento' onclick="nova_janela('calcular_abatimento.php?valor_reajustado=<?=number_format($valor_conta, '2', ',', '');?>&data_vencimento_alterada=<?=$data_vencimento_alterada;?>&id_conta_receber=<?=$id_conta_receber;?>', 'CALCULAR ABATIMENTO', '', '', '', '', 200, 550, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
            <?
                }
            ?>
        </td>
        <td>
            <input type='text' name='txt_taxa_juros' value='<?=$taxa_juros;?>' title='Digite a Taxa Juros' size='20' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular(1)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Juros R$
        </td>
        <td>
            Valor Despesas
        </td>
    </tr>
	<?
            if($manual == 1) $checked_habilitar_juros = 'checked';
	?>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_valor_juros' value="<?=$valor_juros;?>" title='Valor Juros' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size='20' maxlength='15' class='textdisabled' disabled>
            &nbsp;<input type='checkbox' name='chkt_habilitar_juros' value='1' onclick='habilitar_valor_juros()' id='habilitar' class='checkbox' <?=$checked_habilitar_juros;?>>
            <label for='habilitar'>Valor Juros Manual</label>
        </td>
        <td>
            <input type='text' name="txt_valor_despesas" value="<?=$valor_despesas;?>" title="Valor Despesas" size="20" maxlength="15" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular(1);" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Valor Reajustado R$ 
        </td>
        <td>
            Data de Emissão
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_valor_reajustado' title='Digite o Valor Reajustado' size='20' maxlength='15' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_data_emissao' value='<?=$data_emissao;?>' title='Data de Emissão' size='20' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Vencimento
        </td>
        <td>
            Data de Vencimento Alterada
            &nbsp;-&nbsp;
            <font color='red'>
                <b>Data de Recebimento</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <input type='text' name='txt_data_vencimento' value='<?=$data_vencimento;?>' title='Data de Vencimento' size='20' maxlength='10' class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_data_vencimento_alterada' value='<?=$data_vencimento_alterada;?>' title='Data de Vencimento Alterada' size='20' maxlength='10' onkeyup="verifica(this, 'data', '', '', event);calcular(1)" class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="if(document.form.txt_data_vencimento_alterada.disabled == false) {nova_janela('../../../calendario/calendario.php?campo=txt_data_vencimento_alterada&tipo_retorno=1&chamar_funcao=2&caixa_auxiliar=executar', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio
            &nbsp;-&nbsp;
            <font color='red'>
                <b><?=$data_recebimento;?></b>
            </font>
        </td>
    </tr>
<?
/*******************************************Controle********************************************/
//Quando for o País do Cliente for diferente do Brasil, vai estar exibindo este campo Data do B/L
        if($id_pais != 31) {
?>
    <tr class='linhanormal'>
        <td colspan='2'>Data do B/L</td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <input type='text' name='txt_data_bl' value="<?=$data_bl;?>" title='Data do B/L' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;-&nbsp;
            <a href="javascript:nova_janela('alterar_data_bl.php?id_conta_receber=<?=$id_conta_receber;?>', 'POP', '', '', '', '', 300, 800, 'c', 'c')" title='Alterar Data do B/L' class='link'>
                Alterar Data do B/L
            </a>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
            /******************************************************************/
            /*Significa que essa Tela foi aberta de modo normal e sendo assim exibo normalmente 
            os botões abaixo p/ manipulação de Dados do Formulário ...*/
            if($_GET['pop_up'] != 1) {
        ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');separar();habilitar_valor_juros();calcular()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        <?
            }
            /******************************************************************/
        ?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick='fechar(window)' class='botao'>
        </td>
    </tr>
    <!--****************************Follow-UPs***************************-->
    <tr align='center'>
        <td colspan='2'>
            <br/>
            <iframe name='detalhes' id='detalhes' src = '/erp/albafer/modulo/classes/follow_ups/detalhes.php?identificacao=<?=$id_conta_receber;?>&origem=4' marginwidth='0' marginheight='0' frameborder='0' height='150' width='100%'></iframe>
        </td>
    </tr>
    <!--*****************************************************************-->
</table>
<?
//Aqui retorno todas às quitações da Conta à Receber "Duplicata" passada por parâmetro ...
    $sql = "SELECT * 
            FROM `contas_receberes_quitacoes` 
            WHERE `id_conta_receber` = '$id_conta_receber' ORDER BY `id_conta_receber_quitacao` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Detalhes de Conta à Receber Quitada
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            Tipo de Recebimento
        </td>
        <td rowspan='2'>
            N.° Cheque
        </td>
        <td rowspan='2'>
            Banco
        </td>
        <td rowspan='2'>
            Correntista
        </td>
        <td rowspan='2'>
            Cobrança
        </td>
        <td rowspan='2'>
            Conta<br/>Corrente
        </td>
        <td colspan='3'>
            Valor
        </td>
        <td rowspan='2'>
            Data
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Parcela
        </td>
        <td>
            Total<br/>Recebido
        </td>
        <td>
            Restante
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
            //Aqui eu busco o N.º de Cheque na tabela relacional de contas à receber ...
            $sql = "SELECT cc.* 
                    FROM `contas_receberes_quitacoes` crq 
                    INNER JOIN `cheques_clientes` cc ON cc.`id_cheque_cliente` = crq.`id_cheque_cliente` 
                    WHERE crq.`id_conta_receber_quitacao` = '".$campos[$i]['id_conta_receber_quitacao']."' LIMIT 1 ";
            $campos_cheque_cliente = bancos::sql($sql);
            if(count($campos_cheque_cliente) == 1) {
                $texto              = array('Cancelado', 'À compensar', 'Concluído / Compensado', 'Devolvido');
                $situacao           = $texto[$status];

                $id_cheque_cliente  = $campos_cheque_cliente[0]['id_cheque_cliente'];
                $num_cheque         = $campos_cheque_cliente[0]['num_cheque'].' <b>('.$situacao.')</b>';
                $bank               = $campos_cheque_cliente[0]['banco'];
                $correntista        = $campos_cheque_cliente[0]['correntista'];
                $cobranca           = $campos_cheque_cliente[0]['tipo_cobranca'];
                
                if($cobranca == 0) {
                    $cobranca = 'Carteira';
                }else {
                    $cobranca = 'Cobrança Bancária';
                }
                
                $status             = $campos_cheque_cliente[0]['status'];
            }else {
                $num_cheque = '';
            }
            //Aqui eu verifico a conta_corrente, agência e banco na tabela relacional de contas à receber ...
            $sql = "SELECT a.`nome_agencia`, b.`banco`, cc.`conta_corrente` 
                    FROM `contas_receberes_quitacoes` crc 
                    INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = crc.`id_contacorrente` 
                    INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
                    INNER JOIN `bancos` b ON b.`id_banco` = a.`id_banco` 
                    WHERE crc.`id_conta_receber_quitacao` = '".$campos[$i]['id_conta_receber_quitacao']."' LIMIT 1 ";
            $campos_conta_corrente  = bancos::sql($sql);
            $dados                  = $campos_conta_corrente[0]['conta_corrente'];
            $title_dados            = 'Agência: '.$campos_conta_corrente[0]['nome_agencia'].' / Banco: '.$campos_conta_corrente[0]['banco'];
/***********************************************************************/
            $valor_parcela_recebida = $campos[$i]['valor'];
            $valor_total_recebido+= $campos[$i]['valor'];
            $valor_a_receber        = ($valor_conta + $valores_extra) - $valor_total_recebido;
?>
    <tr class='linhanormal'>
        <td>
        <?
            //Busco o Tipo de Recebimento ...
            $sql = "SELECT `recebimento` 
                    FROM `tipos_recebimentos` 
                    WHERE `id_tipo_recebimento` = '".$campos[$i]['id_tipo_recebimento']."' LIMIT 1 ";
            $campos_tipo_recebimento = bancos::sql($sql);
            echo $campos_tipo_recebimento[0]['recebimento'];
        ?>
        </td>
        <td align='center'>
        <?
//Se existir cheque, então mostra em quais contas que este cheque está sendo utilizado
            if(!empty($num_cheque)) {
//Se o Cheque for Devolvido, então tem que apresentar o número do cheque em Vermelho
                if($status == 3) {
                    $color = 'red';
                }else {//Apresentação Normal
                    $color = '';
                }
        ?>
                <a href="javascript:nova_janela('cheque_cliente/classes/manipular/detalhes.php?id_cheque_cliente=<?=$id_cheque_cliente;?>', 'DETALHES_CHEQUES', '', '', '', '', 500, 900, 'c', 'c', '', '', 's', 's', '', '', '')" title='Detalhes de Cheques' class='link'>
                    <font color='<?=$color;?>'>
                        <?=$num_cheque;?>
                    </font>
                </a>
        <?
            }
        ?>
        </td>
        <td align='center'>
            <?=$bank;?>
        </td>
        <td align='center'>
            <?=$correntista;?>
        </td>
        <td align='center'>
            <?=$cobranca;?>
        </td>
        <td style='cursor:help'>
        <?
            if($dados != ' /  / ') echo '<font color="blue" title = "'.$title_dados.'">'.$dados.'</font>';
        ?>
        </td>
        <td align='right'>
            <?=number_format($valor_parcela_recebida, '2', ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($valor_total_recebido, '2', ',', '.');?>
        </td>
        <td align='right'>
        <?
            if(number_format($valor_a_receber, 2, ',', '.') == '-0,00') $valor_a_receber = '0,00';
            echo number_format($valor_a_receber, '2', ',', '.');
        ?>
        </td>
        <td align='center'>
        <?
            echo data::datetodata($campos[$i]['data'], '/').'<br>';
            echo '<font color="darkblue">'.$campos[$i]['data_sys'].'</font>';
        ?>
        </td>
    </tr>
<?
        }
?>
</table>
<?    
    }
//Aqui retorna todos os borderos da conta à receber 
    $sql = "SELECT DISTINCT (b.`data`), cc.`id_contacorrente`, b.`data`, bnk.`banco`, 
            cr.`id_conta_receber`, tr.`id_tipo_recebimento`, tr.`recebimento` 
            FROM `contas_receberes` cr 
            INNER JOIN `borderos` b ON b.`id_bordero` = cr.`id_bordero` 
            INNER JOIN `tipos_recebimentos` tr ON tr.`id_tipo_recebimento` = b.`id_tipo_recebimento` 
            INNER JOIN `contas_correntes` cc ON cc.`id_contacorrente` = b.`id_contacorrente` 
            INNER JOIN `agencias` a ON a.`id_agencia` = cc.`id_agencia` 
            INNER JOIN `bancos` bnk ON bnk.`id_banco` = a.`id_banco` 
            WHERE cr.`id_conta_receber` = '$id_conta_receber' 
            GROUP BY `recebimento`, bnk.`banco` 
            ORDER BY b.`data` DESC ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Detalhes de Borderor
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Data do Bordero
        </td>
        <td>
            Tipo Recebimento
        </td>
        <td>
            Banco
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=data::datetodata($campos[$i]['data'], '/');?>
        </td>
        <td>
            <?=$campos[$i]['recebimento'];?>
        </td>
        <td>
            <?=$campos[$i]['banco'];?>
        </td>
    </tr>
<?
        }
?>
</table>
<?
    }
/************************Visualização das Contas à Receber************************/
    //Visualizando as Contas à Receber
    $retorno    = financeiros::contas_em_aberto($id_cliente, 1, '', 2);
    $linhas     = count($retorno['id_contas']);
    if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onclick="showHide('detalhes2'); return false">
        <td colspan='2'>
            <font color='yellow' size='2'>
                &nbsp;Débito(s) à Receber: 
            </font>
            <font color='#FFFFFF' size='2'>
                <?=$linhas;?>
            </font>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Passo o id_cliente por parâmetro porque utilizo dentro da Função de Receber-->
            <iframe src = '../../classes/cliente/debitos_receber.php?id_cliente=<?=$id_cliente;?>&id_emp=<?=$id_emp;?>&ignorar_sessao=1' name='detalhes2' id='detalhes2' marginwidth='0' marginheight='0' style='display: none' frameborder='0' height='126' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<?
    }
/*********************************************************************************/
/************************Visualização das Contas à Pagar************************/
//Aqui eu zero a variável para não dar conflito com a variável lá de cima
    $valor_pagar = 0;

    $sql = "SELECT `id_fornecedor` 
            FROM `fornecedores` 
            WHERE `cnpj_cpf` '$cnpj_cpf' LIMIT 1 ";
    $campos_fornecedor = bancos::sql($sql);
//Visualizando as Contas à Pagar
    $retorno = financeiros::contas_em_aberto($campos_fornecedor[0]['id_fornecedor'], 2, '', 1);
    $linhas = count($retorno['id_contas']);
//Se encontrou uma Conta à Pagar pelo menos
    if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td></td>
    </tr>
    <tr class='iframe' onClick="showHide('detalhes1'); return false">
        <td colspan='2'>
            <font color='yellow' size='2'>(<?=$linhas;?>) </font>
                Contas à Pagar do Fornecedor:
            <font color='#FFFFFF' size='2'><?=$cliente;?></font>
            <font color='yellow' size='2'> - Valor Total:</font>
            <?
                for($i = 0; $i < $linhas; $i++) {
                    $sql = "SELECT ca.*, CONCAT(tm.`simbolo`, '&nbsp;') AS simbolo 
                            FROM `contas_apagares` ca 
                            INNER JOIN `tipos_moedas` tm ON tm.`id_tipo_moeda` = ca.`id_tipo_moeda` 
                            WHERE ca.`id_conta_apagar` = '".$retorno['id_contas'][$i]."' LIMIT 1 ";
                    $campos = bancos::sql($sql);
//Essa variável iguala o tipo de moeda da conta à pagar
                    $moeda = $campos[0]['simbolo'];
                    $valor_pagar = $campos[0]['valor'] - $campos[0]['valor_pago'];
                    if($campos[0]['predatado'] == 1) {
/*Está parte é o script q exibirá o valor da conta quando o cheque for pré-datado */
                        $sql = "SELECT SUM(caq.valor) AS valor 
                                FROM `contas_apagares` ca 
                                INNER JOIN `contas_apagares_quitacoes` caq ON caq.`id_conta_apagar` = ca.`id_conta_apagar` 
                                INNER JOIN `cheques` c ON c.`id_cheque` = caq.`id_cheque` AND c.`status` IN (1, 2) AND c.`predatado` = '1' 
                                WHERE ca.`id_conta_apagar` = '$id_conta_apagar' ";
                        $campos_pagamento = bancos::sql($sql);
                        $valor_conta = $campos_pagamento[0]['valor'];
                        $valor_pagar+= $valor_conta;
                    }
                    if($campos[0]['id_tipo_moeda'] == 2) {//Dólar
                        $valor_pagar*= $valor_dolar;
                    }else if($campos[0]['id_tipo_moeda'] == 3) {//Euro
                        $valor_pagar*= $valor_euro;
                    }
                    $valor_pagar_total+= $valor_pagar;
                }
            ?>
            <font color='#FFFFFF' size='2'>
                <?=number_format($valor_pagar_total, 2, ',', '.');?>
            </font>
            &nbsp;
            <span id='statusdados_fornecedor'>&nbsp;</span>
            <span id='statusdados_fornecedor'>&nbsp;</span>
        </td>
    </tr>
    <tr>
        <td colspan='2'>
<!--Passo o id_fornecedor por parâmetro porque utilizo dentro da Função de Apagar-->
            <iframe src = '../../classes/cliente/debitos_pagar.php?id_fornecedor=<?=$campos_fornecedor[0]['id_fornecedor'];?>&id_emp=<?=$id_emp;?>&ignorar_sessao=1' name='detalhes1' id='detalhes1' marginwidth='0' marginheight='0' style='display: none' frameborder='0' height='126' width='100%' scrolling='auto'></iframe>
        </td>
    </tr>
</table>
<?
    }
/*******************************************************************************/
?>
<input type='hidden' name='executar' onclick='calcular()'>
</form>
</body>
</html>