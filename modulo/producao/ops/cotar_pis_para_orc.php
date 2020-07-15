<?
require('../../../lib/segurancas.php');
require('../../../lib/comunicacao.php');
require('../../../lib/intermodular.php');
segurancas::geral('/erp/albafer/modulo/producao/custo_unificado/custo_unificado.php', '../../../');

/********************************************************************************************************/
/********************************************Links para Custo********************************************/
/********************************************************************************************************/
$texto.= "Custo: <a href='http://192.168.1.253/erp/albafer/modulo/producao/custo/industrial/custo_industrial.php?id_produto_acabado=".$_GET['id_produto_acabado']."&tela=2&pop_up=1'>Interno</a> - ";

/**Busca do IP Externo que está cadastrado em alguma Empresa aqui do Sistema ...**/
$sql = "SELECT ip_externo 
        FROM `empresas` 
        WHERE `ip_externo` <> '' LIMIT 1 ";
$campos_empresa = bancos::sql($sql);
/*Se encontrar um IP Externo cadastrado, o conteúdo do e-mail apontará p/ esse IP "que é a preferência", 
do contrário o IP será da onde o usuário está acessando o ERP $_SERVER['HTTP_HOST'] ...*/
$ip_externo     = (count($campos_empresa) == 1) ? $campos_empresa[0]['ip_externo'] : $_SERVER['HTTP_HOST'];

$texto.= "<a href='http://".$ip_externo."/erp/albafer/modulo/producao/custo/industrial/custo_industrial.php?id_produto_acabado=".$_GET['id_produto_acabado']."&tela=2&pop_up=1'>Externo</a><br/><br/>";
/********************************************************************************************************/

$texto.= '<b>A/C DEPTO. COMPRAS</b>';
$texto.= '<br/>Cotar PI(s) p/ Orçamento - '.intermodular::pa_discriminacao($_GET['id_produto_acabado'], 0, 0, 0, 0, 1).'<br/>';

$vetor_valores = explode(',', $_GET['valores']);

for($i = 0; $i < count($vetor_valores); $i++) {
    //Em cada linha dessa variável -> $vetor_valores[$i], sempre trago 3 valores que representam: N.º da Etapa, Qtde e PI ...
    $valores_loop = explode('|', $vetor_valores[$i]);
    $etapa              = $valores_loop[0];
    $qtde               = number_format($valores_loop[1], 1, ',', '.');
    $id_produto_insumo  = $valores_loop[2];
    
    //Busca da Discriminação do Produto Insumo ...
    $sql = "SELECT pi.discriminacao, u.sigla 
            FROM `produtos_insumos` pi 
            INNER JOIN `unidades` u ON u.`id_unidade` = pi.`id_unidade` 
            WHERE pi.`id_produto_insumo` = '$id_produto_insumo' LIMIT 1 ";
    $campos = bancos::sql($sql);
    
    if($etapa == 2) {//Essa Etapa é a única que apresenta alguns dados a mais junto da discriminação ...
        //Busca do Comprimento do Custo do PA ...
        $sql = "SELECT (comprimento_1 + 1) AS comprimento_mais1 
                FROM `produtos_acabados_custos` 
                WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' 
                AND `operacao_custo` = '0' LIMIT 1 ";
        $campos_custo = bancos::sql($sql);
        $texto.= '<br/>'.$qtde.' '.$campos[0]['sigla'].' - '.$campos[0]['discriminacao'].' ('.$_GET['qtde_lote'].' pçs c/ '.$campos_custo[0]['comprimento_mais1'].'  mm, se pçs cortadas) ';
    }else {
        $texto.= '<br/>'.$qtde.' '.$campos[0]['sigla'].' - '.$campos[0]['discriminacao'];
    }
}

if($_GET['observacao_depto_compras'] != null) $texto.= '<br/><br/><font color="red"><b>OBSERVAÇÃO DO DEPTO. TÉCNICO: </b><br/>'.$_GET['observacao_depto_compras'].'</font><br/><br/>';

/*Busco o e-mail do funcionário que está logado p/ saber no Remetente quem solicitou 
a requisição que chegará via e-mail p/ Compras e Depto. Técnico ...*/
$sql = "SELECT `email_externo` 
        FROM `funcionarios` 
        WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
$campos = bancos::sql($sql);
comunicacao::email($campos[0]['email_externo'], 'gcompras@grupoalbafer.com.br; gcusto@grupoalbafer.com.br', '', 'Cotar PI(s) p/ Orçamento - '.intermodular::pa_discriminacao($_GET['id_produto_acabado'], 0, 0, 0, 0, 1), $texto);
?>
<Script Language = 'JavaScript'>
    alert('E-MAIL ENVIADO COM SUCESSO !')
    window.close()
</Script>