<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/comunicacao.php');
require('../../../lib/data.php');
require('../../../lib/genericas.php');
require('../../../lib/faturamentos.php');
require('../../../lib/variaveis/intermodular.php');
segurancas::geral('/erp/albafer/modulo/faturamento/outras_nfs/itens/alterar_imprimir.php', '../../../');

$mensagem[1] = "<font class='confirmacao'>NOTA FISCAL ALTERADA COM SUCESSO.</font>";
$mensagem[2] = "<font class='erro'>NÚMERO DE NOTA FISCAL JÁ EXISTENTE PARA ESSA EMPRESA.</font>";

$id_nf_outra = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_nf_outra'] : $_GET['id_nf_outra'];

if($passo == 1) {
//Garanti que a Nota escolhida anteriormente foi disponibilizada para um novo uso, mesmo que seje para a mesma Nota ...
    $sql = "UPDATE `nfs_num_notas` nnn 
            INNER JOIN `nfs_outras` nfso ON nfso.`id_nf_num_nota` = nnn.`id_nf_num_nota` 
            SET nnn.`nota_usado` = '0' 
            WHERE nfso.`id_nf_outra` = '$_POST[id_nf_outra]' ";
    bancos::sql($sql);
/********************************************************************************************/
    $txt_data_emissao       = data::datatodate($txt_data_emissao, '-');
    $txt_data_saida_entrada = data::datatodate($txt_data_saida_entrada, '-');
    $data_sys               = date('Y-m-d H:i:s');
//Aqui verifica a situação da NF, para saber se está já foi importada parcial ou totalmente ...
    $importado_financeiro   = faturamentos::importado_financeiro_outras_nfs($_POST['id_nf_outra']);
/********************************Toda Parte p/ Núm NF************************************/
//Verifica a empresa e o número antigo atual da NF
    $sql = "SELECT id_empresa, id_nf_num_nota, id_cfop 
            FROM `nfs_outras` 
            WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
    $id_empresa_nf          = $campos[0]['id_empresa'];
    $id_nf_num_nota_antigo  = $campos[0]['id_nf_num_nota'];
    $id_cfop_antigo         = $campos[0]['id_cfop'];
//Se a Empresa da Nota for Grupo, então segue o critério da numeração sequencial ...
    if($cmb_empresa == 4) {
        $id_nf_num_nota_novo = faturamentos::gerar_numero_nf($cmb_empresa, $cmb_num_nota_fiscal);
//Essa variável vai estar sendo utilizada lá embaixo no Update ...
        $campo_nf_num_nota_novo = " `id_nf_num_nota` = '$id_nf_num_nota_novo', ";
    }else {//Se a Mudança de Núm. for para a Empresa Alba ou Tool, então ...
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
        if(empty($cmb_num_nota_fiscal)) {
            $id_nf_num_nota_novo = 'NULL';
        }else {
            $id_nf_num_nota_novo = faturamentos::gerar_numero_nf($cmb_empresa, $cmb_num_nota_fiscal);
            if($id_nf_num_nota_novo == 0 || $id_nf_num_nota_novo == 'NULL') $id_nf_num_nota_novo = $id_nf_num_nota_antigo;
        }
//Essa variável vai estar sendo utilizada lá embaixo no Update ...
        $campo_nf_num_nota_novo = " `id_nf_num_nota` = $id_nf_num_nota_novo, ";
    }
/*************************************************************************************************/
/*****************************************NF Complementar*****************************************/
/*************************************************************************************************/		
    if(!empty($_POST['cmb_dado_escolhido'])) {//Se foi escolhida uma NF então ...
        //Verifico se está é p/ uma NF de Saída ou p/ uma NF Outra ...
        if(substr($_POST['cmb_dado_escolhido'], 0, 3) == '777') {//Significa que é para uma NF Outra ...
            $id_nf_outra_comp = substr($_POST['cmb_dado_escolhido'], 3, strlen($_POST['cmb_dado_escolhido']));
            //Preciso Buscar a CFOP da NF de Outra ...
            $sql = "SELECT id_cfop 
                    FROM `nfs_outras` 
                    WHERE `id_nf_outra` = '$id_nf_outra_comp' LIMIT 1 ";
            $campos_cfop = bancos::sql($sql);
            $id_campo_nf_escolhida = " `id_nf_outra_comp` = '$id_nf_outra_comp', ";
        }else {
            //Preciso Buscar a CFOP da NF de Saída ...
            $sql = "SELECT id_cfop 
                    FROM `nfs` 
                    WHERE `id_nf` = '$_POST[cmb_dado_escolhido]' LIMIT 1 ";
            $campos_cfop = bancos::sql($sql);
            $id_campo_nf_escolhida = " `id_nf_comp` = '$_POST[cmb_dado_escolhido]', ";
        }
        $id_cfop 	 = $campos_cfop[0]['id_cfop'];
    }else {//Significa que já não existe mais nenhuma NF Complementar ...
/*Se o usuário resolver desmarcar a opção de Nota Fiscal Comp, então é preciso zerar os campos de NF Complementar e 
tirar a CFOP que foi herdada da NF de Saída ...*/
        $sql = "UPDATE `nfs_outras` SET `id_cfop` = NULL, `id_nf_comp` = NULL, `id_nf_outra_comp` = NULL, `base_calculo_icms_comp` = '0.00', `valor_icms_comp` = '0.00', `base_calculo_icms_st_comp` = '0.00', `valor_icms_st_comp` = '0.00', `valor_total_produtos_comp` = '0.00', `valor_frete_comp` = '0.00', `valor_seguro_comp` = '0.00', `outras_despesas_acessorias_comp` = '0.00', `valor_ipi_comp` = '0.00', `valor_total_nota_comp` = '0.00', `gerar_duplicatas` = 'N' WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
        bancos::sql($sql);
    }
/*************************************************************************************************/
/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $id_cfop = (!empty($_POST['cmb_cfop'])) ? $_POST['cmb_cfop'] : 'NULL';
    
    if($importado_financeiro == 'S') {
        $sql = "UPDATE `nfs_outras` SET `id_funcionario` = '$_SESSION[id_funcionario]', `finalidade` = '$cmb_finalidade', `id_empresa` = '$cmb_empresa', `id_transportadora` = '$cmb_cliente_transportadora', `frete_transporte`= '$cmb_frete_transporte', `tipo_nfe_nfs` = '$opt_nota', `valor_frete` = '$txt_valor_frete', `data_saida_entrada` = '$txt_data_saida_entrada', `observacao` = '$txt_observacao_justificativa', `data_sys` = '$data_sys', `numero_remessa` = '$_POST[txt_numero_remessa]' WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    }else {
        $sql = "UPDATE `nfs_outras` SET `id_funcionario` = '$_SESSION[id_funcionario]', `finalidade` = '$cmb_finalidade', `id_empresa` = '$cmb_empresa', `id_transportadora` = '$cmb_cliente_transportadora', `id_cfop` = $id_cfop, $id_campo_nf_escolhida $campo_nf_num_nota_novo `frete_transporte`= '$cmb_frete_transporte', `tipo_nfe_nfs` = '$opt_nota', `valor_frete` = '$txt_valor_frete', `data_emissao` = '$txt_data_emissao', `chave_acesso` = '$_POST[txt_chave_acesso]', `vencimento1` = '$txt_vencimento1', `vencimento2` = '$txt_vencimento2', `vencimento3` = '$txt_vencimento3', `vencimento4` = '$txt_vencimento4', `data_saida_entrada` = '$txt_data_saida_entrada', `qtde_volume` = '$_POST[txt_qtde_volume]', `especie_volume` = '$_POST[txt_especie_volume]', `peso_bruto_volume` = '$_POST[txt_peso_bruto_volume]', `peso_liquido_volume` = '$_POST[txt_peso_liquido_volume]', `observacao` = '$txt_observacao_justificativa', `data_sys` = '$data_sys', `status` = '$cmb_status_nota_fiscal', `numero_remessa` = '$_POST[txt_numero_remessa]', `gerar_duplicatas` = '$_POST[opt_gerar_duplicatas]' WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    }
    bancos::sql($sql);
    /*************************************Texto da Nota**************************************/
    /*Sempre que o usuário trocar a CFOP, então eu zero o campo texto da NF p/ que o Usuário digite 
    o Novo Texto coerente com o que foi selecionado na combo ...*/
    if($id_cfop != $id_cfop_antigo) {
        $sql = "UPDATE `nfs` SET `texto_nf` = '' WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        bancos::sql($sql);
    }
    /*************************************Packings Lists*************************************/
//Se a NF foi faturada então eu chamo a função p/ recalcular o Faturamento do Cliente no ano Corrente ...
    if($cmb_status_nota_fiscal == 2) {
/************************Novo Controle com a Parte de NF-e************************/
//Para Tool Master que se iniciou em 01/04/2010 ...
//Para Albafér que se iniciou em 01/07/2010 ...
//Aqui eu trago alguns dados de Nota Fiscal p/ passar por e-mail via parâmetro ...
        if($id_empresa_nf == 1 || $id_empresa_nf == 2) {
            $sql = "SELECT IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, c.`cnpj_cpf`, c.`email`, nfso.`id_nf_num_nota` 
                    FROM `nfs_outras` nfso 
                    INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
                    WHERE nfso.`id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
            $campos_cliente = bancos::sql($sql);
            $cliente        = $campos_cliente[0]['cliente'];
            $cnpj_cpf       = $campos_cliente[0]['cnpj_cpf'];
            $email          = $campos_cliente[0]['email'];
            $id_nf_num_nota = $campos_cliente[0]['id_nf_num_nota'];
//Busca um E-mail preenchido de Contato do Cliente do Departamento de Compras ...
            $sql = "SELECT email 
                    FROM `clientes_contatos` 
                    WHERE `id_cliente` = '$id_cliente' 
                    AND `id_departamento` = '4' 
                    AND `email` <> '' LIMIT 1 ";
            $campos_contato = bancos::sql($sql);
            $destino_nfe    = (count($campos_contato) == 1) ? $campos_contato[0]['email'] : $email;
//Busca do Número da NF ...
            $sql = "SELECT numero_nf 
                    FROM `nfs_num_notas` 
                    WHERE `id_nf_num_nota` = '$id_nf_num_nota' LIMIT 1 ";
            $campos_nf  = bancos::sql($sql);
            $numero_nf  = $campos_nf[0]['numero_nf'];
//Aqui eu disparo um e-mail ao Cliente informando o N.º de sua Chave de Acesso ...
            $mensagem_nfe = 'Não responder este email. Qualquer dúvida entrar em contato através de giacosa@grupoalbafer.com.br ou pelo fone (11) 2972-5655 com Sr. Giacosa. <br><br>';
            $mensagem_nfe.= 'Esta mensagem refere-se a Nota Fiscal Eletrônica Nacional de serie/número [1/'.$numero_nf.'] emitida para: <br><br>';
            $mensagem_nfe.= 'Razão Social: '.$cliente.'<br>';
            $mensagem_nfe.= 'CNPJ / CPF: '.$cnpj_cpf.'<br><br>';
            $mensagem_nfe.= 'Para verificar a autorização da SEFAZ referente à nota acima mencionada, acesse o <br>';
            $mensagem_nfe.= 'www.nfe.fazenda.gov.br/portal/FormularioDePesquisa.aspx?tipoconsulta=completa<br><br>';
            $mensagem_nfe.= 'Chave de acesso: ['.$_POST['txt_chave_acesso'].']<br><br>';
            $mensagem_nfe.= 'Este e-mail foi enviado automaticamente pelo Sistema de Nota Fiscal Eletrônica (NF-e) da TOOL ..............';
            //comunicacao::email('erp@grupoalbafer.com.br', $destino_nfe, 'nfemitida@grupoalbafer.com.br; darcio@grupoalbafer.com.br', 'Envio de NF-e Tool Master', $mensagem_nfe);
/*********************************************************************************/
        }
    }
/*****************************************E-mail*****************************************/
/*Se o Usuário estiver cancelando a Nota Fiscal, então o Sistema dispara um e-mail informando qual a 
Nota que está sendo cancelada*/
    if($cmb_status_nota_fiscal == 5) {
        $data_atual     = date('Y-m-d');
        $justificativa  = '<font color="blue">Follow-Up Registrado automaticamente (E-mail) </font>';
//Aqui eu trago alguns dados de Nota Fiscal p/ passar por e-mail via parâmetro ...
        $sql = "SELECT nfso.`id_cliente`, nfso.`id_nf_num_nota`, c.`razaosocial` 
                FROM `nfs_outras` nfso 
                INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
                WHERE nfso.`id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
        $campos             = bancos::sql($sql);
        $empresa            = genericas::nome_empresa($id_empresa_nf);
        $id_cliente         = $campos[0]['id_cliente'];
        $id_nf_num_nota     = $campos[0]['id_nf_num_nota'];
        $cliente            = $campos[0]['razaosocial'];
        
//Busca do Número da NF ...
        $sql = "SELECT `numero_nf` 
                FROM `nfs_num_notas` 
                WHERE `id_nf_num_nota` = '$id_nf_num_nota' LIMIT 1 ";
        $campos     = bancos::sql($sql);
        $numero_nf  = $campos[0]['numero_nf'];

//Aqui eu busco o login de quem está excluindo a Conta ...
        $sql = "SELECT login 
                FROM `logins` 
                WHERE `id_login` = '$_SESSON[id_login]' LIMIT 1 ";
        $campos_login       = bancos::sql($sql);
        $login_cancelando   = $campos_login[0]['login'];
        
//Eu concateno esses d+ dados p/ enviar por e-mail na Justificativa ...
        $complemento_justificativa = '<br><b>Empresa: </b>'.$empresa.' <br><b>Cliente: </b>'.$cliente.' <br><b>N.º da Conta: </b>'.$numero_nf.' <br><b>Login: </b>'.$login_cancelando;
        $justificativa.= $complemento_justificativa.'<br>'.date('d/m/Y H:i:s').'<br><b>Justificativa: </b>'.$txt_observacao_justificativa;
//Aqui eu mando um e-mail informando quem e porque que exclui a Conta à Receber ...
/*****************************************E-mail*****************************************/
        $destino = $cancelar_nota_fiscal;
        $mensagem = $justificativa;
        comunicacao::email('ERP - GRUPO ALBAFER', $destino, '', 'Cancelamento de Nota', $mensagem);
/****************************************************************************************/
//Observação: existe esse mesmo trecho de código na Tela de Excluir todos os Itens de NF ...
    }
/****************************************************************************************/
    if($follow_up == 1) {
?>
    <Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
    <Script Language = 'JavaScript'>
        window.close()
        nova_janela('../../classes/cliente/follow_up.php?identificacao=<?=$id_nf_outra;?>&origem=5', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    </Script>
<?
    }else {
?>
    <Script Language = 'JavaScript'>
        window.location = 'alterar_cabecalho.php?id_nf_outra=<?=$id_nf_outra;?>&valor=1'
    </Script>
<?
    }
}else {
//Exclusão de Transportadoras
    if(!empty($id_transportadora_excluir)) {
//Se a Transportadora for N/Carro ou Retira, então não pode ser excluido do Cliente
        if($id_transportadora_excluir != 795 && $id_transportadora_excluir != 796) {
            $sql = "DELETE FROM `clientes_vs_transportadoras` WHERE `id_cliente` = '$id_cliente' AND `id_transportadora` = '$id_transportadora_excluir' LIMIT 1 ";
            bancos::sql($sql);
        }
    }
//Aqui traz os dados da Nota Fiscal
    $sql = "SELECT nfso.*, c.`id_uf`, c.`id_pais`, c.`id_cliente_tipo`, c.`razaosocial`, c.`optante_simples_nacional`, cfops.`natureza_operacao` 
            FROM `nfs_outras` nfso 
            LEFT JOIN `cfops` on cfops.`id_cfop` = nfso.`id_cfop` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
            WHERE nfso.`id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
    $campos                     = bancos::sql($sql);
//Coloquei esse nome na variável porque na sessão já existe uma variável com o nome de id_empresa
    $id_empresa_nf              = $campos[0]['id_empresa'];
    $id_uf                      = $campos[0]['id_uf'];
    $id_pais                    = $campos[0]['id_pais'];
    $id_cliente_tipo            = $campos[0]['id_cliente_tipo'];
    $id_cliente                 = $campos[0]['id_cliente'];
    $id_transportadora          = $campos[0]['id_transportadora'];
    $id_nf_num_nota             = $campos[0]['id_nf_num_nota'];
    $id_cfop                    = $campos[0]['id_cfop'];
    $id_nf_comp                 = $campos[0]['id_nf_comp'];
    $id_nf_outra_comp           = $campos[0]['id_nf_outra_comp'];
    $natureza_operacao          = $campos[0]['natureza_operacao'];

//Esse $id_campo_nf_escolhida será utilizado + abaixo p/ os demais controles em JavaScript ...
    if($id_nf_comp > 0) {
        $id_campo_nf_escolhida = $id_nf_comp;
    }else if($id_nf_outra_comp > 0) {
        $id_campo_nf_escolhida = '777'.$id_nf_outra_comp;//Esse 777 é um Macete p/ eu poder passar o parâmetro em JS
    }
    $valor_frete                = number_format($campos[0]['valor_frete'], 2, ',', '.');

//Aqui serve para tratar com os options do Tipo de Nota de Entrada ou Saída que estão a partir da linha 950
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
    if(empty($opt_nota))        $opt_nota = $campos[0]['tipo_nfe_nfs'];
//Data de Emissão
    if($campos[0]['data_emissao'] != '0000-00-00') $data_emissao = data::datetodata($campos[0]['data_emissao'], '/');
//Prazos
    $vencimento1 = $campos[0]['vencimento1'];

    if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento1 = data::adicionar_data_hora($data_emissao, $vencimento1);
    $qtde_duplicatas = 1;//Sempre terá q ter pelo menos 1 duplicata ...

    if($campos[0]['vencimento2'] == 0) {
        $vencimento2        = '';
        $data_vencimento2   = '';
    }else {
        $vencimento2 = $campos[0]['vencimento2'];
        if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento2 = data::adicionar_data_hora($data_emissao, $vencimento2);
        $qtde_duplicatas++;
    }

    if($campos[0]['vencimento3'] == 0) {
        $vencimento3 = '';
        $data_vencimento3 = '';
    }else {
        $vencimento3 = $campos[0]['vencimento3'];
        if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento3 = data::adicionar_data_hora($data_emissao, $vencimento3);
        $qtde_duplicatas++;
    }

    if($campos[0]['vencimento4'] == 0) {
        $vencimento4 = '';
        $data_vencimento4 = '';
    }else {
        $vencimento4 = $campos[0]['vencimento4'];
        if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento4 = data::adicionar_data_hora($data_emissao, $vencimento4);
        $qtde_duplicatas++;
    }
    $total_icms     = number_format($campos[0]['total_icms'], 2, ',', '.');

    if($campos[0]['data_saida_entrada'] != '0000-00-00') $data_saida_entrada = data::datetodata($campos[0]['data_saida_entrada'], '/');
    $qtde_volume 		= $campos[0]['qtde_volume'];
    $especie_volume             = $campos[0]['especie_volume'];
    $peso_bruto_volume          = number_format($campos[0]['peso_bruto_volume'], 3, ',', '.');
    $peso_liquido_volume        = number_format($campos[0]['peso_liquido_volume'], 3, ',', '.');
    $observacao 		= $campos[0]['observacao'];
    $status                     = $campos[0]['status'];
    $importado_financeiro       = $campos[0]['importado_financeiro'];

/*Aqui verifica se a Nota Fiscal tem pelo menos 1 item cadastrado, se tiver não pode alterar 
a Empresa e o Tipo de Nota*/
    $sql = "SELECT `id_nf_outra_item` 
            FROM `nfs_outras_itens` 
            WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
    $campos_qtde_itens      = bancos::sql($sql);
    $qtde_itens_nf          = count($campos_qtde_itens);
//Já deixo esse valor carregado em variável, porque eu uso para fazer validação de JavaScript + pra baixo
    $valor_minimo_nf        = genericas::variavel(23);
/****************************************************************************/
//Função para o cálculo do Valor Total da NF - tem q ter todos os calculos da NF, pois o valor contém frete+impostos e etc.
    $calculo_total_impostos = calculos::calculo_impostos(0, $id_nf_outra, 'NFO');
/****************************************************************************/
//Controle com as Datas de Emissão do N.º de NF selecionado ...
    if(!empty($cmb_num_nota_fiscal) || !empty($id_nf_num_nota)) {
//Somente p/ a primeira vez em que carrega a Tela ...
        if(empty($cmb_num_nota_fiscal)) $cmb_num_nota_fiscal = $id_nf_num_nota;
//Aqui eu chamo a função de Talonário que controla tudo referente à parte de NF(s) ...
        $talonario = faturamentos::buscar_numero_ant_post_talonario($cmb_num_nota_fiscal);
        $data_emissao_anterior 	= $talonario['data_emissao_anterior'];
        $numero_nf_anterior 	= $talonario['numero_nf_anterior'];
        $data_emissao_posterior = $talonario['data_emissao_posterior'];
        $numero_nf_posterior 	= $talonario['numero_nf_posterior'];
    }
    //Essa variável será utilizada + abaixo ...
    $vetor_nota_sgd = genericas::nota_sgd($id_empresa_nf);
?>
<html>
<head>
<title>.:: Alterar Cabeçalho de NF Outras ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var id_campo_nf_escolhida = eval('<?=$id_campo_nf_escolhida;?>')
//CFOP
    if(typeof(document.form.cmb_cfop) == 'object') {
        if(document.form.cmb_cfop.value == '' && id_campo_nf_escolhida == 0 && document.getElementById('opt_outras_opcoes1').checked == false && document.getElementById('opt_outras_opcoes2').checked == false) {
            alert('SELECIONE A CFOP !')
            document.form.cmb_cfop.focus()
            return false
        }
    }
	
    var qtde_itens_nf   = eval('<?=$qtde_itens_nf;?>')
    var id_empresa_nota = eval('<?=$id_empresa_nf;?>')

    if(qtde_itens_nf == 0) {//Não tem itens cadastrados
//Empresa ...
        if(!combo('form', 'cmb_empresa','','SELECIONE A EMPRESA !')) {
            return false
        }
    }
//Transportadora ...
    if(document.form.cmb_cliente_transportadora.value == '') {
        alert('SELECIONE A TRANSPORTADORA DO CLIENTE !')
        document.form.cmb_cliente_transportadora.focus()
        return false
    }
//Finalidade ...
    if(document.form.cmb_finalidade.value == '') {
        alert('SELECIONE A FINALIDADE !')
        document.form.cmb_finalidade.focus()
        return false
    }
//Frete Transporte ...
    if(!combo('form', 'cmb_frete_transporte', '', 'SELECIONE O FRETE TRANSPORTE !')) {
        return false
    }
//Data de Emissão ...
    if(document.form.txt_data_emissao.value != '') {
        if(!data('form', 'txt_data_emissao', "4000", 'EMISSÃO')) {
            return false
        }
    }
/************************************************************************************/
//Vencimento 1
    if(document.form.txt_vencimento1.value != '') {
        if(!texto('form', 'txt_vencimento1', '1', '0123456789', 'VENCIMENTO 1', '2')) {
            return false
        }
    }
//Vencimento 2
    if(document.form.txt_vencimento2.value != '') {
        if(!texto('form', 'txt_vencimento2', '1', '0123456789', 'VENCIMENTO 2', '2')) {
            return false
        }
    }
//Vencimento 3
    if(document.form.txt_vencimento3.value != '') {
        if(!texto('form', 'txt_vencimento3', '1', '0123456789', 'VENCIMENTO 3', '2')) {
            return false
        }
    }
//Vencimento 4
    if(document.form.txt_vencimento4.value != '') {
        if(!texto('form', 'txt_vencimento4', '1', '0123456789', 'VENCIMENTO 4', '2')) {
            return false
        }
    }
/****************Comparação dos Vencimentos**********************/
    if(qtde_itens_nf > 0) {//Só fará essa comparação caso venha existir pelo menos 1 item na NF
        var vencimento1 = eval(document.form.txt_vencimento1.value)
        var vencimento2 = eval(document.form.txt_vencimento2.value)
        var vencimento3 = eval(document.form.txt_vencimento3.value)
        var vencimento4 = eval(document.form.txt_vencimento4.value)
//Aqui força para não dar erro, quando o campo estiver em branco
        if(typeof(vencimento4) != 'undefined') {
            if(typeof(vencimento3) == 'undefined') vencimento3 = 0
            if(typeof(vencimento2) == 'undefined') vencimento2 = 0
            if(typeof(vencimento1) == 'undefined') vencimento1 = 0
        }
        if(typeof(vencimento3) != 'undefined') {
            if(typeof(vencimento2) == 'undefined') vencimento2 = 0
            if(typeof(vencimento1) == 'undefined') vencimento1 = 0
        }
        if(typeof(vencimento2) != 'undefined') {
            if(typeof(vencimento1) == 'undefined') vencimento1 = 0
        }
/*****************************************************************/
//Comparando o Vencimento 2
        if(vencimento2 <= vencimento1) {
            alert('VENCIMENTO 2 INVÁLIDO !!! \n VALOR DO VENCIMENTO 2 MENOR OU IGUAL AO VALOR DO VENCIMENTO 1 !')
            document.form.txt_vencimento2.focus()
            document.form.txt_vencimento2.select()
            return false
        }
//Comparando o Vencimento 3
        if(vencimento3 <= vencimento2 || vencimento3 <= vencimento1) {
            alert('VENCIMENTO 3 INVÁLIDO !!! \n VALOR DO VENCIMENTO 3 MENOR OU IGUAL AO VALOR DO VENCIMENTO 2 OU \n VALOR DO VENCIMENTO 3 MENOR OU IGUAL AO VALOR DO VENCIMENTO 1 !')
            document.form.txt_vencimento3.focus()
            document.form.txt_vencimento3.select()
            return false
        }
//Comparando o Vencimento 4
        if(vencimento4 <= vencimento3 || vencimento4 <= vencimento2 || vencimento4 <= vencimento1) {
            alert('VENCIMENTO 4 INVÁLIDO !!! \n VALOR DO VENCIMENTO 4 MENOR OU IGUAL AO VALOR DO VENCIMENTO 3 OU \n VALOR DO VENCIMENTO 4 MENOR OU IGUAL AO VALOR DO VENCIMENTO 2 OU \n VALOR DO VENCIMENTO 4 MENOR OU IGUAL AO VALOR DO VENCIMENTO 1 !')
            document.form.txt_vencimento4.focus()
            document.form.txt_vencimento4.select()
            return false
        }
    }
/***********************************************************/
/*Somente quando for FOB - Frete - Se a Transportadora for 797 - Sedex, 1050 - Correio Encomenda P.A.C., 
1092 - Sedex 10, 1093 - Motoboy, e Valor do Frete = 0, então forço a calcular ...*/
    var id_transportadora = document.form.cmb_cliente_transportadora.value
    if(document.form.cmb_frete_transporte.value == 2 && document.form.txt_valor_frete.value == '0,00' && (id_transportadora == 797 || id_transportadora == 1050 || id_transportadora == 1092 || id_transportadora == 1093)) {
        alert('VALOR DO FRETE INVÁLIDO !!!\nCALCULE O VALOR DO FRETE PARA ESSA TRANSPORTADORA ! ')
        document.form.cmd_calcular_frete.focus()
        document.form.cmd_calcular_frete.select()
        return false
    }
//Status da Nota Fiscal
    if(document.form.cmb_status_nota_fiscal.value == '') {
        alert('SELECIONE O STATUS DA NOTA FISCAL !')
        document.form.cmb_status_nota_fiscal.focus()
        return false
    }
/********************************Chave de Acesso - NFe**********************************/
    if(typeof(document.form.txt_chave_acesso) == 'object') {
//Se a Nota Fiscal for para uma situação de Faturada, então ...
        if(document.form.cmb_status_nota_fiscal.value >= 2) {//Se a NF estiver Faturada ...
            if(id_empresa_nota != 4) {//Só existirá Chave de Acesso p/ Alba e Tool aonde é obrigatório ter NFe ...
//Chave de Acesso ...
                if(!texto('form', 'txt_chave_acesso', '44', '0123456789 ', 'CHAVE DE ACESSO', '1')) {
                    return false
                }
            }
        }
    }
/**************************************************************************************/
//Gerar Duplicatas ...
    if(document.form.opt_gerar_duplicatas[0].checked == false && document.form.opt_gerar_duplicatas[1].checked == false) {
        alert('SELECIONE UMA OPÇÃO P/ "O GERAR DUPLICATA(S)" !')
        return false
    }
//Data de Saída / Entrada
    if(document.form.txt_data_saida_entrada.value != '') {
        if(!data('form', 'txt_data_saida_entrada', "4000", 'SAÍDA / ENTRADA')) {
            return false
        }
    }
/**********************************************************************************************************/
/**********************************************Dados de Volume*********************************************/
/**********************************************************************************************************/    
    /*O sistema exigirá o preenchimento de Dados de Volume quando a NF for <> Complementar "NF Normal" 
    e o status >= Liberada p/ Faturar ...*/
    if(document.getElementById('opt_outras_opcoes1').checked == false && document.getElementById('opt_outras_opcoes2').checked == false && document.form.cmb_status_nota_fiscal.value > 0) {
//Quantidade de Volume ...
        if(!texto('form', 'txt_qtde_volume', '1', '0123456789 ', 'QUANTIDADE DE VOLUME', '1')) {
            return false
        }
//Tratamento com a Quantidade de Volume Inválida ...
        if(document.form.txt_qtde_volume.value == 0) {
            alert('QUANTIDADE DE VOLUME INVÁLIDA !!!\nDIGITE A QUANTIDADE DE VOLUME !')
            document.form.txt_qtde_volume.focus()
            document.form.txt_qtde_volume.select()
            return false
        }
//Espécie de Volume ...
        if(!texto('form', 'txt_especie_volume', '1', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZáéíóúÁÉÍÓÚâêîôûÂÊÎÔÛãõÃÕçÇ()[]{} ', 'ESPÉCIE DE VOLUME', '1')) {
            return false
        }
//Peso Bruto de Volume ...
        if(!texto('form', 'txt_peso_bruto_volume', '4', '0123456789.,', 'PESO BRUTO DE VOLUME', '2')) {
            return false
        }
//Tratamento com o Peso Bruto de Volume Inválido ...
        if(document.form.txt_peso_bruto_volume.value == '0,000') {
            alert('PESO BRUTO DE VOLUME INVÁLIDO !!!\nDIGITE O PESO BRUTO DE VOLUME !')
            document.form.txt_peso_bruto_volume.focus()
            document.form.txt_peso_bruto_volume.select()
            return false
        }
//Peso Líquido de Volume ...
        if(!texto('form', 'txt_peso_liquido_volume', '4', '0123456789.,', 'PESO LÍQUIDO DE VOLUME', '2')) {
            return false
        }
//Tratamento com o Peso Líquido de Volume Inválido ...
        if(document.form.txt_peso_liquido_volume.value == '0,000') {
            alert('PESO LÍQUIDO DE VOLUME INVÁLIDO !!!\nDIGITE O PESO LÍQUIDO DE VOLUME !')
            document.form.txt_peso_liquido_volume.focus()
            document.form.txt_peso_liquido_volume.select()
            return false
        }
    }
/**********************************************************************************************************/
//Se existir esse objeto
    if(typeof(document.form.chkt_liberar_cfop_sem_itens) == 'object') {
//Esse objeto serve para ignorar a validação das CFOPS em JavaScript
//Se esse objeto não tiver checado, então eu sigo normalmente as regras para validar o CFOP
        if(document.form.chkt_liberar_cfop_sem_itens.checked == false) {
/*Tem essa verificação antes das demais, eu não posso colocar essa Nota em Situação de Liberada para
Faturar, se ela não tiver itens e pertencer as classificações fiscais 101, 102, 109, 501*/
//Está na Situação de Liberada para Faturar
            if(document.form.cmb_status_nota_fiscal.value == 1) {
                if(qtde_itens_nf == 0) {//A NF não contém nenhum IF
                    var indice_combo = document.form.cmb_cfop.selectedIndex
                    var tamanho_combo = document.form.cmb_cfop[indice_combo].text
                    var cfop = ''
                    var num_cfop = ''
                    var achou_ponto = 0
//Todo esse controle é para ver qual Classificação Fiscal que foi selecionada pelo usuário na Combo
                    for(i = 0; i < tamanho_combo.length; i++) {
                        if(tamanho_combo.charAt(i) == '.') {
                            achou_ponto = 1
                        }else {
//Aki eu busco o Número da CFOP
                            if(achou_ponto == 1) {
                                if(tamanho_combo.charAt(i) == ' ') {//Sai fora do Loop
                                    i = tamanho_combo.length
                                }else {//Vai armazenando a Classificação Fiscal
                                    num_cfop+= tamanho_combo.charAt(i)
                                }
//Nessa eu Busco a CFOP
                            }else {
                                cfop+= tamanho_combo.charAt(i)
                            }
                        }
                    }
//Aqui começam as Particularizações
                    if(cfop != 3 && num_cfop == '101') {//Por enquanto a CFOP 3 é uma exceção ...
                        alert('ESSA NOTA NÃO PODE SER LIBERADA P/ FATURAR !\nALÉM DE NÃO CONTÉR ITEM(NS), O CFOP SELECIONADO É DO TIPO VENDA / REVENDA !')
                        return false
                    }
//Aqui eu já analiso direto pelas terminações 102, 109 ou 501
                    if(num_cfop == '102' || num_cfop == '109' || num_cfop == '501') {
                        alert('ESSA NOTA NÃO PODE SER LIBERADA P/ FATURAR !\nALÉM DE NÃO CONTÉR ITEM(NS), O CFOP SELECIONADO É DO TIPO VENDA / REVENDA !')
                        return false
                    }
                }
            }
        }
    }
//Todas as opções à partir de Faturada seleciona 
    if(document.form.cmb_status_nota_fiscal.value >= 2) {
//Núm. da Nota Fiscal
        if(typeof(document.form.cmb_num_nota_fiscal) == 'object') {
            if(document.form.cmb_num_nota_fiscal.value == '') {
                alert('SELECIONE UM NÚMERO DE NOTA FISCAL !')
                document.form.cmb_num_nota_fiscal.focus()
                return false
            }
        }
//Data de Emissão
        if(document.form.txt_data_emissao.disabled == false) {
            if(!data('form', 'txt_data_emissao', '4000', 'EMISSÃO')) {
                return false
            }
        }
    }
//As Opções LIBERADA P/ FATURAR, FATURADA tem que fazer essa verificação
    if(document.form.cmb_status_nota_fiscal.value == 1 || document.form.cmb_status_nota_fiscal.value == 2) {
        if(!texto('form', 'txt_vencimento1', '1', '0123456789', 'VENCIMENTO 1', '2')) {
            return false
        }
    }
//A Opção CANCELADA tem que fazer essa verificação
    if(document.form.cmb_status_nota_fiscal.value == 5) {
//Observação / Justificativa ...
        if(document.form.txt_observacao_justificativa.value == '') {
            alert('DIGITE A OBSERVAÇÃO / JUSTIFICATIVA !')
            document.form.txt_observacao_justificativa.focus()
            document.form.txt_observacao_justificativa.select()
            return false
        }
    }
/***************************Controle com a Data de Emissão em ao N.º de Nota Fiscal selecionado 
pelo usuáriorelação ************************************************************************************/
    if(id_empresa_nota != 4) {//Somente para as Empresas Albafer e Tool Master, que tem esse Controle  
        if(document.form.txt_data_emissao.value != '') {
            var data_emissao = document.form.txt_data_emissao.value
            var data_emissao_anterior = '<?=$data_emissao_anterior;?>'
            var data_emissao_posterior = '<?=$data_emissao_posterior;?>'

            data_emissao = data_emissao.substr(6,4)+data_emissao.substr(3,2)+data_emissao.substr(0,2)
            data_emissao_anterior = data_emissao_anterior.substr(0,4)+data_emissao_anterior.substr(5,2)+data_emissao_anterior.substr(8,2)
//Aqui eu verifico se a Data de Emissão é Menor em relação ao do N.º de NF selecionado pelo usuário ...
            if(data_emissao < data_emissao_anterior) {
                alert('DATA DE EMISSÃO INVÁLIDA !!!\nDATA DE EMISSÃO MENOR DO QUE A DATA DE EMISSÃO DO ÚLTIMO N.º DE NF USADO !')
                document.form.txt_data_emissao.focus()
                document.form.txt_data_emissao.select()
                return false
            }
//Data de Emissão Posterior ...
            if(data_emissao_posterior != '') {
                data_emissao_posterior = data_emissao_posterior.substr(0,4)+data_emissao_posterior.substr(5,2)+data_emissao_posterior.substr(8,2)
//Aqui eu verifico se a Data de Emissão é Maior em relação ao do N.º de NF selecionado pelo usuário ...
                if(data_emissao > data_emissao_posterior) {
                    alert('DATA DE EMISSÃO INVÁLIDA !!!\nDATA DE EMISSÃO MAIOR DO QUE A DATA DE EMISSÃO DO ÚLTIMO N.º DE NF USADO !')
                    document.form.txt_data_emissao.focus()
                    document.form.txt_data_emissao.select()
                    return false
                }
            }
        }
    }
    desabilitar_objetos()
    document.form.passo.value           = 1
    //Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value   = 1
    atualizar_frames_abaixo()
//Travo o Botão de Salvar para que o usuário não fique submetendo várias vezes o Cabeçalho da Nota Fiscal ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
    return true
}

//Exclusão das Transportadoras
function excluir_transportadora() {
    if(document.form.cmb_cliente_transportadora.value == '') {
        alert('SELECIONE A TRANSPORTADORA DO CLIENTE !')
        document.form.cmb_cliente_transportadora.focus()
        return false
    }else {
        var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
        if(mensagem == false) {
            return false
        }else {
            document.form.passo.value = 0
            document.form.id_transportadora_excluir.value = document.form.cmb_cliente_transportadora.value
            document.form.submit()
        }
    }
}

function verificar(valor) {
    if(valor == 1) {//Vencimento 1
        if(document.form.txt_vencimento1.value == '') {
            document.form.txt_data_vencimento1.value = ''
        }else {
            if(document.form.txt_data_emissao.value != '') nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento1', 'document.form.txt_vencimento1')
        }
    }else if(valor == 2) {//Vencimento 2
        if(document.form.txt_vencimento2.value == '') {
            document.form.txt_data_vencimento2.value = ''
        }else {
            if(document.form.txt_data_emissao.value != '') nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento2', 'document.form.txt_vencimento2')
        }
    }else if(valor == 3) {//Vencimento 3
        if(document.form.txt_vencimento3.value == '') {
            document.form.txt_data_vencimento3.value = ''
        }else {
            if(document.form.txt_data_emissao.value != '') nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento3', 'document.form.txt_vencimento3')
        }
    }else if(valor == 4) {//Vencimento 4
        if(document.form.txt_vencimento4.value == '') {
            document.form.txt_data_vencimento4.value = ''
        }else {
            if(document.form.txt_data_emissao.value != '') nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento4', 'document.form.txt_vencimento4')
        }
    }
}

function valor_nota() {
    var valor_total = eval('<?=$calculo_total_impostos['valor_total_nota'];?>')
//Caso retornar nulo do PHP, então jogo zero para a variável em JavaScript para não dar erro de arredond.
    if(typeof(valor_total) == 'undefined') valor_total = 0
    if(document.form.txt_vencimento4.value != '') {
        var valor_duplicata = valor_total / 4
        valor_duplicata = String(valor_duplicata)//Transforma em String, p/ poder arredondar
        valor_duplicata = strtofloat(arred(valor_duplicata, 2, 1))
        valor_duplicata = eval(valor_duplicata)//Volta a ser número para poder efetuar o cálculo
        valor1 = valor_duplicata
        valor2 = valor_duplicata
        valor3 = valor_duplicata
        valor4 = valor_total - (valor1 + valor2 + valor3)
    }else if(document.form.txt_vencimento3.value != '') {
        valor_duplicata = valor_total / 3//round(round((valor_total / 3),3),2)
        valor_duplicata = String(valor_duplicata)//Transforma em String, p/ poder arredondar
        valor_duplicata = strtofloat(arred(valor_duplicata, 2, 1))
        valor_duplicata = eval(valor_duplicata)//Volta a ser número para poder efetuar o cálculo
        valor1 = valor_duplicata
        valor2 = valor_duplicata
        valor3 = valor_total - (valor1 + valor2)
        valor4 = 0
    }else if(document.form.txt_vencimento2.value != '') {
        valor_duplicata = valor_total / 2//round(round((valor_total/2),3),2)
        valor_duplicata = String(valor_duplicata)//Transforma em String, p/ poder arredondar
        valor_duplicata = strtofloat(arred(valor_duplicata, 2, 1))
        valor_duplicata = eval(valor_duplicata)//Volta a ser número para poder efetuar o cálculo
        valor1 = valor_duplicata
        valor2 = valor_total - valor1
        valor3 = 0
        valor4 = 0
    }else {//então só existe um prazo o valor da duplicata é total
        valor1 = valor_total
        valor2 = 0
        valor3 = 0
        valor4 = 0
    }

    document.form.txt_valor1.value = valor1
    document.form.txt_valor2.value = valor2
    document.form.txt_valor3.value = valor3
    document.form.txt_valor4.value = valor4

    document.form.txt_valor1.value = arred(document.form.txt_valor1.value, 2, 1)
    document.form.txt_valor2.value = arred(document.form.txt_valor2.value, 2, 1)
    document.form.txt_valor3.value = arred(document.form.txt_valor3.value, 2, 1)
    document.form.txt_valor4.value = arred(document.form.txt_valor4.value, 2, 1)
//Chama a função p/
    arredondamento_especial()
}

function arredondamento_especial() {
    var valor_total = eval('<?=$calculo_total_impostos['valor_total_nota'];?>')
    var valor1 = eval(strtofloat(document.form.txt_valor1.value))
    var valor2 = eval(strtofloat(document.form.txt_valor2.value))
    var valor3 = eval(strtofloat(document.form.txt_valor3.value))
    var valor4 = eval(strtofloat(document.form.txt_valor4.value))
//Quando tiver a via A, B, C, D estão preenchidas
    if(valor1 != 0.00 && valor2 != 0.00 && valor3 != 0.00 && valor4 != 0.00) {
        total_vias = valor1 + valor2 + valor3 + valor4
/*Tem q fazer todo esse Macete, porque as vezes dá problema de arredondamento nessa variável total_vias
na hora em q faz o somatório*/
        document.form.controle.value = total_vias
        document.form.controle.value = strtofloat(arred(document.form.controle.value, 2, 1))
        total_vias = eval(document.form.controle.value)
/*******************************************************************************************/
//Verifica se o valor Total das Vias é > do que o valor da Nota, para poder ajustar com a diferença de cent.
        if(total_vias > valor_total) valor4-= 0.01
        if(total_vias < valor_total) valor4+= 0.01
        document.form.txt_valor4.value = valor4
        document.form.txt_valor4.value = arred(document.form.txt_valor4.value, 2, 1)
    }
//Quando tiver a via A, B, C estão preenchidas
    if(valor1 != 0.00 && valor2 != 0.00 && valor3 != 0.00 && valor4 == 0.00) {
        total_vias = valor1 + valor2 + valor3
/*Tem q fazer todo esse Macete, porque as vezes dá problema de arredondamento nessa variável total_vias
na hora em q faz o somatório*/
        document.form.controle.value = total_vias
        document.form.controle.value = strtofloat(arred(document.form.controle.value, 2, 1))
        total_vias = eval(document.form.controle.value)
/*******************************************************************************************/
//Verifica se o valor Total das Vias é > do que o valor da Nota, para poder ajustar com a diferença de cent.
        if(total_vias > valor_total) valor3-=0.01
        if(total_vias < valor_total) valor3+=0.01
        document.form.txt_valor3.value = valor3
        document.form.txt_valor3.value = arred(document.form.txt_valor3.value, 2, 1)
    }
//Quando tiver a via A, B estão preenchidas
    if(valor1 != 0.00 && valor2 != 0.00 && valor3 == 0.00 && valor4 == 0.00) {
        total_vias = valor1 + valor2
/*Tem q fazer todo esse Macete, porque as vezes dá problema de arredondamento nessa variável total_vias
na hora em q faz o somatório*/
        document.form.controle.value = total_vias
        document.form.controle.value = strtofloat(arred(document.form.controle.value, 2, 1))
        total_vias = eval(document.form.controle.value)
/*******************************************************************************************/
//Verifica se o valor Total das Vias é > do que o valor da Nota, para poder ajustar com a diferença de cent.
        if(total_vias > valor_total) valor2-=0.01
        if(total_vias < valor_total) valor2+=0.01
        document.form.txt_valor2.value = valor2
        document.form.txt_valor2.value = arred(document.form.txt_valor2.value, 2, 1)
    }
}

function recarregar_notas_fiscais() {
/*Sempre que trocar a Empresa, não posso manter gravado o N.º do Talonário que foi escolhido anteriormente 
p/ não dar problema ...*/
    if(typeof(document.form.cmb_num_nota_fiscal) == 'object') document.form.cmb_num_nota_fiscal.value = ''

    desabilitar_objetos()
    document.form.passo.value = 1
    document.form.submit()
}

function buscar_dt_emis_num_nf_selec() {
//Todas as Opções FATURADA ou CANCELADA tem que fazer essa verificação
    if(document.form.cmb_status_nota_fiscal.value == 2 || document.form.cmb_status_nota_fiscal.value == 5) {
//Faz a validação de Formulário também quando estou trocando o N.º da NF ...
        validar_formulario = validar()
        if(validar_formulario == false) {
            document.form.reset()
            return false
        }
    }
    desabilitar_objetos()
    document.form.passo.value = 1
    document.form.submit()
}

//Função que habilita os campos para poder gravar no BD ...
function desabilitar_objetos() {
    if(typeof(document.form.cmb_cfop) == 'object') document.form.cmb_cfop.disabled = false
    document.form.cmb_finalidade.disabled           = false
    document.form.cmb_status_nota_fiscal.disabled   = false
    document.form.opt_nota[0].disabled              = false
    document.form.opt_nota[1].disabled              = false
    
    document.form.cmb_status_nota_fiscal.disabled   = false
    if(typeof(document.form.cmb_num_nota_fiscal) == 'object') document.form.cmb_num_nota_fiscal.disabled = false
    
    document.form.txt_data_emissao.disabled         = false
    document.form.txt_vencimento1.disabled          = false
    document.form.txt_vencimento2.disabled          = false
    document.form.txt_vencimento3.disabled          = false
    document.form.txt_vencimento4.disabled          = false
    document.form.txt_valor_frete.disabled          = false
    document.form.txt_qtde_volume.disabled          = false
    document.form.txt_especie_volume.disabled       = false
    document.form.txt_peso_bruto_volume.disabled    = false
    document.form.txt_peso_liquido_volume.disabled  = false
    limpeza_moeda('form', 'txt_valor_frete, txt_peso_bruto_volume, txt_peso_liquido_volume, ')
}

/*Esse parâmetro id_campo_nf_escolhida, eu só passo ele quando acabo de carregar a Tela, do contrário ao clicar 
no checkbox esse parâmetro sempre será nulo ...*/
function carregar_dados(id_campo_nf_escolhida) {
//Verifica se existe NF Complementar ...
    if(id_campo_nf_escolhida > 0) document.getElementById('opt_outras_opcoes1').checked = true//Significa que existe uma NF Complementar
    
    if(document.getElementById('opt_outras_opcoes1').checked == true) {//Gerar NF Complementar ...
        ajax('carregar_dados.php?id_cliente=<?=$id_cliente;?>&id_empresa_nota=<?=$id_empresa_nf;?>&opt_outras_opcoes=1', 'cmb_dado_escolhido', id_campo_nf_escolhida)
        if(typeof(id_campo_nf_escolhida) == 'undefined') {
            document.getElementById('img_campos_complementares').style.visibility                       = 'hidden'
            document.getElementById('lbl_campos_complementares').style.visibility                       = 'hidden'
            document.getElementById('cmd_gerar_nota_fiscal_venda_para_entrega_futura').style.visibility = 'hidden'
        }else if(id_campo_nf_escolhida > 0 || document.form.cmb_dado_escolhido.value > 0) {
            document.getElementById('img_campos_complementares').style.visibility                       = 'visible'
            document.getElementById('lbl_campos_complementares').style.visibility                       = 'visible'
            document.getElementById('cmd_gerar_nota_fiscal_venda_para_entrega_futura').style.visibility = 'hidden'

            var qtde_itens_nf = eval('<?=$qtde_itens_nf;?>')
            if(qtde_itens_nf == 0) {
                var resposta = confirm('*********************OBS: LEMBRANDO QUE É OBRIGATÓRIO*********************\n\nNÃO EXISTE(M) ITEM(NS) NESSA NOTA FISCAL !!!\n\n\nDESEJA INCLUIR UM ITEM AGORA COMO SENDO "COMPLEMENTO DE NF" ?')
                if(resposta == true) {
                    nova_janela('itens/incluir_manual.php?id_nf_outra=<?=$id_nf_outra;?>', 'INCLUIR_ITENS', '', '', '', '', 480, 750, 'c', 'c', '', '', 's', 's', '', '', '')
                    window.close()
                }
            }else {
                document.getElementById('img_campos_complementares').focus()
            }
        }
        
        //Habilitando normal o Botão Salvar ...
        document.form.cmd_salvar.disabled   = false
        document.form.cmd_salvar.className  = 'botao'
        
        var controle = 'visible'
    }else if(document.getElementById('opt_outras_opcoes2').checked == true) {//Remessa originada de Entrega Futura ...
        ajax('carregar_dados.php?id_cliente=<?=$id_cliente;?>&id_empresa_nota=<?=$id_empresa_nf;?>&opt_outras_opcoes=2', 'cmb_dado_escolhido', id_campo_nf_escolhida)
        
        if(typeof(id_campo_nf_escolhida) == 'undefined') {
            document.getElementById('img_campos_complementares').style.visibility                       = 'hidden'
            document.getElementById('lbl_campos_complementares').style.visibility                       = 'hidden'
            document.getElementById('cmd_gerar_nota_fiscal_venda_para_entrega_futura').style.visibility = 'hidden'
        }else if(id_campo_nf_escolhida > 0 || document.form.cmb_dado_escolhido.value > 0) {
            document.getElementById('img_campos_complementares').style.visibility                       = 'hidden'
            document.getElementById('lbl_campos_complementares').style.visibility                       = 'hidden'
            document.getElementById('cmd_gerar_nota_fiscal_venda_para_entrega_futura').style.visibility = 'visible'
        }

        /*Travo o Botão Salvar para que o usuário não clique neste e o Sistema venha interpretar que o mesmo deseja 
        gerar uma Nota Fiscal Complementar, coisa que só pode acontecer na Primeira Opção ...*/
        document.form.cmd_salvar.disabled   = true
        document.form.cmd_salvar.className  = 'textdisabled'
        
        var controle = 'visible'
    }else {
        document.form.cmb_dado_escolhido.value                                                          = ''//Para perder o Valor da Combo ...
        document.getElementById('img_campos_complementares').style.visibility                           = 'hidden'
        document.getElementById('lbl_campos_complementares').style.visibility                           = 'hidden'
        document.getElementById('cmd_gerar_nota_fiscal_venda_para_entrega_futura').style.visibility     = 'hidden'
        
        //Habilitando normal o Botão Salvar ...
        document.form.cmd_salvar.disabled   = false
        document.form.cmd_salvar.className  = 'botao'
        
        var controle = 'hidden' 
    }
    document.form.cmb_dado_escolhido.style.visibility                                                   = controle
}

function trocar_nfs() {
    if(document.form.cmb_dado_escolhido.value != '') {//Significa que foi escolhida alguma NF na Combo ...
        if(document.getElementById('opt_outras_opcoes1').checked == true) {//Gerar NF Complementar ...
            alert('PREENCHA UM CAMPO A SER COMPLEMENTADO NA FIGURA AO LADO !')
            document.getElementById('img_campos_complementares').style.visibility                       = 'visible'
            document.getElementById('lbl_campos_complementares').style.visibility                       = 'visible'
            document.getElementById('img_campos_complementares').focus()
        }else if(document.getElementById('opt_outras_opcoes2').checked == true) {//Remessa originada de Entrega Futura ...
            document.getElementById('cmd_gerar_nota_fiscal_venda_para_entrega_futura').style.visibility = 'visible'
        }
    }else {//Não foi escolhida nenhuma NF na Combo ...
        document.getElementById('img_campos_complementares').style.visibility                           = 'hidden'
        document.getElementById('lbl_campos_complementares').style.visibility                           = 'hidden'
        document.getElementById('cmd_gerar_nota_fiscal_venda_para_entrega_futura').style.visibility     = 'hidden'
    }
}

function gerar_nota_fiscal_venda_para_entrega_futura() {
    var resposta = confirm('DESEJA GERAR UMA "NOTA FISCAL DE VENDA PARA ENTREGA FUTURA" P/ O PEDIDO DE VENDA QUE FOI ESCOLHIDO ?')
    if(resposta == true) {
        //Aqui é para não atualizar o frames abaixo desse Pop-UP
        document.form.nao_atualizar.value = 1
        atualizar_frames_abaixo()
        html5Lightbox.showLightbox(7, 'gerar_nota_fiscal_venda_para_entrega_futura.php?id_nf_outra=<?=$id_nf_outra;?>&id_pedido_venda='+document.form.cmb_dado_escolhido.value)
    }
}

function calcular_frete() {
    if(document.form.cmb_modo_envio.value == 'CORREIO') {
        nova_janela('../../classes/cliente/calcular_frete_correio.php?id_nf_outra=<?=$id_nf_outra;?>', 'CALCULAR_FRETE', '', '', '', '', '150', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }else {
        nova_janela('../../classes/cliente/calcular_frete_tam.php?id_nf_outra=<?=$id_nf_outra;?>&valor_total_produtos=<?=$calculo_total_impostos['valor_total_produtos'];?>', 'CALCULAR_FRETE', '', '', '', '', '250', '600', 'c', 'c', '', '', 's', 's', '', '', '')
    }
}

function atualizar_frames_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        if(typeof(opener.parent.itens) == 'object') {
            opener.parent.itens.document.form.submit()
            opener.parent.rodape.document.form.submit()
        }else {
            //opener.location.href = 'itens/alterar_imprimir.php<?=$parametro;?>'
            opener.document.form.submit()
        }
    }
}
</Script>
<?
if($importado_financeiro == 'S') {//Se a NF já tiver importada, não pode desabilitar o N. da Nota p/ digitar
    $functions = 'valor_nota()';
}else {
    $functions = 'if(document.form.txt_data_emissao.disabled == false) {document.form.txt_data_emissao.focus()};carregar_dados('.$id_campo_nf_escolhida.');valor_nota()';
}
?>
<body onload="<?=$functions;?>" onunload='atualizar_frames_abaixo()'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--****************Haja controle (rsrsrs)****************-->
<input type='hidden' name='passo' onclick='atualizar()'>
<input type='hidden' name='id_nf_outra' value='<?=$id_nf_outra;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<input type='hidden' name='id_transportadora_atrelar'>
<input type='hidden' name='id_transportadora_excluir'>
<input type='hidden' name='qtde_itens_nf' value="<?=$qtde_itens_nf;?>">
<input type='hidden' name='follow_up'>
<!--Caixa que faz o controle de contatos inclusos deste Cliente nessa Nota Fiscal-->
<input type='hidden' name='controle' onclick="verificar(1);verificar(2);verificar(3);verificar(4)">
<!--Caixa para controle dos Combos-->
<input type='hidden' name='ja_submeteu' value='1'>
<input type='hidden' name='nao_atualizar'>
<!--******************************************************-->
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
<?
/*Se a NF já tiver sido importado pelo Financeiro, o usuário já não pode + mudar as CFOPS 
da Combo e a Situação da NF nem para LIBERADA P/ FATURAR e nem para  CANCELADA*/
        if($importado_financeiro == 'S') {
            $disabled_special   = 'disabled';
            $class_special      = 'textdisabled';
        }else {
            $disabled_special   = '';
            $class_special      = 'caixadetexto';
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Cabeçalho de NF Outras
        </td>
    </tr>
<?
	$sql = "SELECT c.`cidade`, c.`credito`, c.`razaosocial`, p.`pais` 
                FROM `clientes` c 
                INNER JOIN `paises` p ON p.`id_pais` = c.`id_pais` 
                WHERE c.`id_cliente` = '$id_cliente' LIMIT 1 ";
	$campos_cliente = bancos::sql($sql);
	$cidade         = $campos_cliente[0]['cidade'];
	$credito        = $campos_cliente[0]['credito'];
	$razaosocial 	= $campos_cliente[0]['razaosocial'];
	$pais           = $campos_cliente[0]['pais'];
?>
    <tr class='linhanormal'>
        <td>
            <b>Cliente:</b>
        </td>
        <td>
        <?
            echo $razaosocial;
            if($campos[0]['optante_simples_nacional'] == 'S') echo '<font color="red"><b> (OPTANTE SIMPLES NACIONAL)</b></font>';
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>País / UF / Cidade:</b>
        </td>
        <td>
        <?
            echo $pais;
//Aqui busca o estado do Cliente, faço esse sql à parte para não dar erro no banco
            if($id_uf != 0) {
                $sql = "SELECT `sigla` 
                        FROM `ufs` 
                        WHERE `id_uf` = '$id_uf' LIMIT 1 ";
                $campos_uf = bancos::sql($sql);
                echo ' / '.$campos_uf[0]['sigla'];
            }
            if(!empty($cidade)) echo ' / '.$cidade;
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Nota:</b>
        </td>
        <td>
            <?
                if($opt_nota == 'S') {
                    $checkeds = 'checked';
                }else {
                    $checkede = 'checked';
                }
            ?>
            <input type='radio' name='opt_nota' value='E' id='E' onclick='trocar_cfops()' <?=$checkede;?> <?=$disabled_special;?>><label for='E'>Entrada</label>
            <input type='radio' name='opt_nota' value='S' id='S' onclick='trocar_cfops()' <?=$checkeds;?> <?=$disabled_special;?>><label for='S'>Saída</label>
        </td>
    </tr>
<?
	$descritivo_cfop = 'Iniciais: 1,2,3 p/ NF de Entrada<br>Iniciais: 5,6,7 p/ NF de Nota Saída<br>Inicias 1,5 para UF = SP<br>Inicias 2,6 para UF <> SP<br>Inicias 3,7 para País <> Brasil<br>Final 1 para Produto Industrializado<br>Final 2 para Produto Revendido';
//Só existe CFOP p/ NF(s) do Tipo NF ...
	if($vetor_nota_sgd['nota_sgd'] == 'N') {
//Mesmo que a NF esteja aberta, só pode haver trocar as CFOPS quando a mesma não conter Itens e não for Complementar ...
            if($qtde_itens_nf == 0 && $id_campo_nf_escolhida == 0) {
                $disabled_cfop  = $disabled_special;
                $class_cfop     = $class_special;
            }else {//Como existe(m) item(ns) na NF, então eu não posso trocar p/ outra CFOP ...
                $disabled_cfop  = 'disabled';
                $class_cfop     = 'textdisabled';
            }
            $cfop_combo = faturamentos::cfop_combo_outras_nfs($id_nf_outra);
?>
    <tr class='linhanormal'>
        <td>
            <b>CFOP:</b>
        </td>
        <td>
            <select name='cmb_cfop' title='<?=$descritivo_cfop;?>' class='<?=$class_cfop;?>' <?=$disabled_cfop;?>>
            <?
//Função que controla todo o retorno de CFOPs ...
                $cfop_combo         = faturamentos::cfop_combo_outras_nfs($id_nf_outra);
                $sql_saida          = $cfop_combo['sql_saida'];
                $sql_entrada        = $cfop_combo['sql_entrada'];
/*********Essas variáveis vou estar utilizando na function trocar_cfops() em JS*********/
//Aqui eu carrego todas as CFOP's do Tipo Saída
                $campos_saida       = bancos::sql($sql_saida);
                $linhas_saida       = count($campos_saida);
//Aqui eu carrego todas as CFOP's do Tipo Entrada
                $campos_entrada     = bancos::sql($sql_entrada);
                $linhas_entrada     = count($campos_entrada);

                if($opt_nota == 'S') {//Se a NF for de Saída
                    echo combos::combo($sql_saida, $id_cfop);
                }else {//Se a NF for de Entrada
//Significa que já tinha uma cfop cadastrada na NF anteriormente
                    echo combos::combo($sql_entrada, $id_cfop);
                }
/***************************************************************************************/
            ?>
            </select>
            &nbsp;&nbsp;
            <a href="#" onclick="if(document.form.cmb_cfop.value != '') {javascript:nova_janela('../tributos/cfop/consultar2.php?id_cfop='+document.form.cmb_cfop.value, 'CFOP', '', '', '', '', 450, 750, 'c', 'c')}" title='Visualizar Detalhes' class='link'>
                <img src = '../../../imagem/help.jpg' border='0' width='20' height='20'>
            </a>
            &nbsp;&nbsp;
<!--Esse objetos serve para ignorar a validação das CFOPS em JavaScript-->
            <input type='checkbox' name='chkt_liberar_cfop_sem_itens' value='1' title='Liberar CFOP S/ Itens' id='liberar_cfop_sem_itens' class='checkbox'>
            <label for='liberar_cfop_sem_itens'>
                <b>Liberar CFOP S/ Itens</b>
            </label>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhanormal'>
        <td>
            <b>Empresa:</b>
        </td>
        <td>
<?
        if($status == 0) {//Se a NF estiver em Aberto ...
//Se o Cliente for Estrangeiro, então independente de ter itens ou não, eu posso estar mudando a Empresa
            if($id_pais != 31) {
//Aqui busca as empresas
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ";
                $campos_empresa = bancos::sql($sql);
                $linhas_empresa = count($campos_empresa);
?>
            <select name='cmb_empresa' title='Selecione a Empresa' onchange="return recarregar_notas_fiscais()" class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
<?
                    for($i = 0; $i < $linhas_empresa; $i++) {
                        $vetor_nota_sgd_loop = genericas::nota_sgd($campos_empresa[$i]['id_empresa']);
//Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP
                        if(!empty($cmb_empresa)) {
                            if($cmb_empresa == $campos_empresa[$i]['id_empresa']) {
?>
                    <option value="<?=$campos_empresa[$i]['id_empresa'];?>" selected><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                            }else {
?>
                    <option value="<?=$campos_empresa[$i]['id_empresa'];?>"><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                            }

//Até então não foi feito nenhuma manipulação referente a transportadora ou algum contato no Pop-UP
                        }else {//Só lista
                            if($id_empresa_nf == $campos_empresa[$i]['id_empresa']) {
?>
                    <option value="<?=$campos_empresa[$i]['id_empresa'];?>" selected><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                            }else {
?>
                    <option value="<?=$campos_empresa[$i]['id_empresa'];?>"><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                            }
                        }
                    }
?>
            </select>
<?
//Se o Cliente = Nacional, então eu só posso mudar a Empresa quando está não tiver nenhum Item cadastrado
            }else {
                if($qtde_itens_nf == 0) {//Não tem itens cadastrados
//Aqui busca as empresas
                    $sql = "SELECT `id_empresa`, `nomefantasia` 
                            FROM `empresas` 
                            WHERE `ativo` = '1' ";
                    $campos_empresa = bancos::sql($sql);
                    $linhas_empresa = count($campos_empresa);
?>
            <select name='cmb_empresa' title='Selecione a Empresa' onchange="return recarregar_notas_fiscais()" class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
<?
                    for($i = 0; $i < $linhas_empresa; $i++) {
                        $vetor_nota_sgd_loop = genericas::nota_sgd($campos_empresa[$i]['id_empresa']);
//Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP
                        if(!empty($cmb_empresa)) {
                            if($cmb_empresa == $campos_empresa[$i]['id_empresa']) {
?>
                <option value="<?=$campos_empresa[$i]['id_empresa'];?>" selected><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                            }else {
?>
                <option value="<?=$campos_empresa[$i]['id_empresa'];?>"><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                            }

//Até então não foi feito nenhuma manipulação referente a transportadora ou algum contato no Pop-UP
                        }else {//Só lista
                            if($id_empresa_nf == $campos_empresa[$i]['id_empresa']) {
?>
                <option value="<?=$campos_empresa[$i]['id_empresa'];?>" selected><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                            }else {
?>
                <option value="<?=$campos_empresa[$i]['id_empresa'];?>"><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                            }
                        }
                    }
?>
            </select>
<?
                }else {//Tem 1 item cadastrado
                    $sql = "SELECT `nomefantasia` 
                            FROM `empresas` 
                            WHERE `id_empresa` = '$id_empresa_nf' LIMIT 1 ";
                    $campos_empresa = bancos::sql($sql);
                    echo $campos_empresa[0]['nomefantasia'].$vetor_nota_sgd['tipo_nota'];
//Aqui eu coloco esse objeto para não dar erro de programação no PHP ...
?>
                    <input type='hidden' name='cmb_empresa' value='<?=$id_empresa_nf;?>'>
<?
                }
            }
        }else {//Se a NF estiver em algum outro Status ...
            if($qtde_itens_nf == 0) {//Não tem itens cadastrados ...
//Aqui busca as empresas
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ";
                $campos_empresa = bancos::sql($sql);
                $linhas_empresa = count($campos_empresa);
?>
            <select name='cmb_empresa' title='Selecione a Empresa' onchange='return recarregar_notas_fiscais()' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
<?
                for($i = 0; $i < $linhas_empresa; $i++) {
                    $vetor_nota_sgd_loop = genericas::nota_sgd($campos_empresa[$i]['id_empresa']);
//Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP
                    if(!empty($cmb_empresa)) {
                        if($cmb_empresa == $campos_empresa[$i]['id_empresa']) {
?>
                <option value="<?=$campos_empresa[$i]['id_empresa'];?>" selected><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                        }else {
?>
                <option value="<?=$campos_empresa[$i]['id_empresa'];?>"><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                        }
//Até então não foi feito nenhuma manipulação referente a transportadora ou algum contato no Pop-UP
                    }else {//Só lista
                        if($id_empresa_nf == $campos_empresa[$i]['id_empresa']) {
?>
                <option value="<?=$campos_empresa[$i]['id_empresa'];?>" selected><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                        }else {
?>
                <option value="<?=$campos_empresa[$i]['id_empresa'];?>"><?=$campos_empresa[$i]['nomefantasia'].$vetor_nota_sgd_loop['tipo_nota'];?></option>
<?
                        }
                    }
                }
?>
                </select>
<?
            }else {//Tem 1 item cadastrado
                $sql = "SELECT `nomefantasia` 
                        FROM `empresas` 
                        WHERE `id_empresa` = '$id_empresa_nf' LIMIT 1 ";
                $campos_empresa = bancos::sql($sql);
                echo $campos_empresa[0]['nomefantasia'].$vetor_nota_sgd['tipo_nota'];
//Aqui eu coloco esse objeto para não dar erro de programação no PHP ...
?>
                <input type='hidden' name='cmb_empresa' value="<?=$id_empresa_nf;?>">
<?
            }
        }
?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Transportadora:</b>
        </td>
        <td>
            <select name='cmb_cliente_transportadora' title='Selecione a Transportadora' class='combo'>
            <?
                $sql = "SELECT t.`id_transportadora`, t.`nome` 
                        FROM `clientes_vs_transportadoras` ct 
                        INNER JOIN `transportadoras` t ON t.`id_transportadora` = ct.`id_transportadora` AND t.`ativo` = '1' 
                        WHERE ct.`id_cliente` = '$id_cliente' ORDER BY t.`nome` ";
//Significa que o usuário atrelou uma transportadora no Pop-UP de Transportadoras
                if(!empty($id_transportadora_atrelar)) {
                    echo combos::combo($sql, $id_transportadora_atrelar);
                }else {//Aqui carrega a transportadora já escolhida em Nota Fiscal
//Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP
                    if(!empty($cmb_cliente_transportadora)) {
                        echo combos::combo($sql, $cmb_cliente_transportadora);
//Até então não foi feito nenhuma manipulação referente a transportadora ou algum contato no Pop-UP
                    }else {//Aqui carrega a transportadora já escolhida em Nota Fiscal
                        echo combos::combo($sql, $id_transportadora);
                    }
                }
            ?>
            </select>
            &nbsp;&nbsp;
            <img src = '../../../imagem/menu/incluir.png' border='0' title='Atrelar Transportadora' alt='Atrelar Transportadora' onClick="nova_janela('../../classes/cliente/atrelar_transportadoras.php?id_cliente=<?=$id_cliente;?>', 'CONSULTAR', '', '', '', '', '350', '750', 'c', 'c', '', '', 's', 's', '', '', '')">
            &nbsp;&nbsp;
            <img src = '../../../imagem/menu/excluir.png' border='0' title='Excluir Transportadora' alt='Excluir Transportadora' onclick='excluir_transportadora()'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Finalidade:</b>
        </td>
        <td>
        <?
//Se a Qtde de Itens da NF for igual a Zero ...
            if($qtde_itens_nf == 0) {
                $disabled_finalidade    = $disabled_special;
                $class_finalidade       = $class_special;
            }else {//Como existe(m) item(ns) na NF, então eu não posso trocar p/ outra CFOP ...
                $disabled_finalidade    = 'disabled';
                $class_finalidade       = 'textdisabled';
            }
        ?>
            <select name='cmb_finalidade' title='Selecione a Finalidade' class='<?=$class_finalidade;?>' <?=$disabled_finalidade;?>>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    //Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP
                    if(!empty($cmb_finalidade)) {
                        if($cmb_finalidade == 'C') {
                            $selected_consumo           = 'selected';
                        }else if($cmb_finalidade == 'I') {
                            $selected_industrializacao  = 'selected';
                        }else {
                            $selected_revenda           = 'selected';
                        }
                    }else {
                        if($campos[0]['finalidade'] == 'C') {
                            $selected_consumo           = 'selected';
                        }else if($campos[0]['finalidade'] == 'I') {
                            $selected_industrializacao  = 'selected';
                        }else {
                            $selected_revenda           = 'selected';
                        }
                    }
                ?>
                <option value='C' <?=$selected_consumo;?>>CONSUMO</option>
                <option value='I' <?=$selected_industrializacao;?>>INDUSTRIALIZAÇÃO</option>
                <option value='R' <?=$selected_revenda;?>>REVENDA</option>
            </select>
        <?
//Significa que o Cliente é do Tipo Internacional ...
            if($id_pais != 31) echo 'Exportação';
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Frete Transporte:</b>
        </td>
        <td>
            <select name='cmb_frete_transporte' title='Selecione o Frete Transporte' onchange="if(this.value == '1') {document.getElementById('lbl_frete_transporte').style.visibility = 'visible'}else {document.getElementById('lbl_frete_transporte').style.visibility = 'hidden'}" class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
<?
//Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP
                if(!empty($cmb_frete_transporte)) {
                    if($cmb_frete_transporte == '1') {
                        $selected_remetente = 'selected';
                    }else {
                        $selected_destinatario = 'selected';
                    }
//Até então não foi feito nenhuma manipulação referente a transportadora ou algum contato no Pop-UP
                }else {		
                    if($campos[0]['frete_transporte'] == '1') {
                        $selected_remetente = 'selected';
                    }else {
                        $selected_destinatario = 'selected';
                    }
                }
            ?>
                <option value='1' <?=$selected_remetente;?>>CIF (POR NOSSA CONTA - REMETENTE)</option>
                <option value='2' <?=$selected_destinatario;?>>FOB (POR CONTA DO CLIENTE - DESTINATÁRIO)</option>
            </select>
            <label id='lbl_frete_transporte' style='visibility: hidden'>
                <font color='red'>
                    &nbsp;<b>NÃO FAZ PARTE DOS CÁLCULOS DA NF</b>
                </font>
            </label>
        <?
            if($id_pais != 31) echo 'Exportação';//Significa que o Cliente é do Tipo Internacional ...
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Status da NF:</b>
        </td>
        <td>
            <select name='cmb_status_nota_fiscal' title='Status Nota Fiscal' class='<?=$class_special;?>' <?=$disabled_special;?>>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if(!empty($cmb_status_nota_fiscal)) {
                        if($cmb_status_nota_fiscal == 0) {
                            $selected0 = 'selected';
                        }else if($cmb_status_nota_fiscal == 1) {
                            $selected1 = 'selected';
                        }else if($cmb_status_nota_fiscal == 2) {
                            $selected2 = 'selected';
                        }else if($cmb_status_nota_fiscal == 3) {
                            $selected3 = 'selected';
                        }else if($cmb_status_nota_fiscal == 4) {
                            $selected4 = 'selected';
                        }else if($cmb_status_nota_fiscal == 5) {
                            $selected5 = 'selected';
                        }
//Até então não foi feito nenhuma manipulação referente a transportadora ou algum contato no Pop-UP
                    }else {//Aqui e quando carrega a tela de primeira
                        if($status == 0) {
                            $selected0 = 'selected';
                        }else if($status == 1) {
                            $selected1 = 'selected';
                        }else if($status == 2) {
                            $selected2 = 'selected';
                        }else if($status == 3) {
                            $selected3 = 'selected';
                        }else if($status == 4) {
                            $selected4 = 'selected';
                        }else if($status == 5) {
                            $selected5 = 'selected';
                        }
                    }
                ?>
                <option value='0' <?=$selected0;?>>EM ABERTO</option>
                <option value='1' <?=$selected1;?>>LIBERADA P/ FATURAR</option>
                <option value='2' <?=$selected2;?>>FATURADA</option>
                <option value='3' <?=$selected3;?>>EMPACOTADA</option>
                <option value='4' <?=$selected4;?>>DESPACHADA</option>
                <option value='5' <?=$selected5;?>>CANCELADA</option>
            </select>
            &nbsp;
            N.º de Remessa: <input type='text' name='txt_numero_remessa' value='<?=$campos[0]['numero_remessa'];?>' title='Digite o N.º de Remessa' maxlength='13' size='15' class='<?=$class_special;?>' <?=$disabled_special;?>>
        </td>
    </tr>
<?
/**********************************************************************************/
//Se a NF já foi importada pelo Financeiro, mostro essa mensagem na linha abaixo ...
    if($importado_financeiro == 'S') {
?>
    <tr class='linhanormal'>
        <td>
             &nbsp;
        </td>
        <td>
            <font color='red' size='2'>
                <b>(NOTA FISCAL JÁ IMPORTADA PELO DEPTO. FINANCEIRO)</b>
            </font>
        </td>
    </tr>
<?
    }
/**********************************************************************************/
/*Esse campo será obrigado ser preenchido nesse estágio p/ q o Sistema dispare um e-mail para o Cliente 
informando a chave de acesso de sua NF-e no Site da Receita Federal ...*/
    if($vetor_nota_sgd['nota_sgd'] == 'N') {//Só irá aparecer esse campo chave de acesso p/ NF(s) com Nota ...
?>
    <tr class='linhanormal'>
        <td>
            <b>Chave de Acesso:</b>
        </td>
        <td>
            <input type='text' name='txt_chave_acesso' value='<?=$campos[0]['chave_acesso'];?>' title='Digite a Chave de Acesso' size='75' maxlength='54' class='caixadetexto'>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>N.º da NF Outra: </b>
            </font>
        </td>
        <td>
        <?
/***************************************************************************************************/
/*A primeira coisa a fazer é verificar se realmente não existe uma outra NF além da atual que 
possui o mesmo número na mesma empresa*/
            $sql = "SELECT nfso.`id_nf_outra` 
                    FROM `nfs_outras` nfso 
                    INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfso.`id_nf_num_nota` 
                    WHERE nfso.`id_nf_num_nota` = '$id_nf_num_nota' 
                    AND nfso.`id_nf_outra` <> '$id_nf_outra' LIMIT 1 ";
            $campos_mesmo_numero = bancos::sql($sql);
//Se existir, então a NF atual passará a herdar outro N.º de Nota Fiscal
            if(count($campos_mesmo_numero) == 1) {//Busca de um novo N.º p/ a Nota Fiscal ...
                $id_nf_num_nota_novo = faturamentos::gerar_numero_nf($id_empresa_nf);
//Atualizando a NF corrente com o novo número gerado ...
                $sql = "UPDATE `nfs_outras` SET `id_nf_num_nota` = '$id_nf_num_nota_novo' WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
                bancos::sql($sql);
//Agora a variável $id_nf_num_nota, passa a ser o Novo N.º de NF que foi gerado ...
                $id_nf_num_nota = $id_nf_num_nota_novo;
            }
/********************************************Controle********************************************/
            if($status > 0) {//Se a NF estiver no estágio Mínimo de Liberada p/ Faturar ...
//Aqui significa que o Usuário trocou pelo menos 1 vez o N.º de Nota Fiscal na combo ...
                if(!empty($cmb_num_nota_fiscal)) $id_nf_num_nota = $cmb_num_nota_fiscal;
/************************************************************************************************/
//Se a Empresa for diferente de Grupo então segue ...
                if($id_empresa_nf != 4) {
                    if($natureza_operacao == 'Prestação de Serviço') {//Nesse Tipo de Nota Fiscal utilizamos o N.º do Governo ...
                        faturamentos::gerar_numero_nf($id_empresa_nf, 0, 'S');
                        //Busca de todos os N.ºs de Prestação de Serviço que estejam em aberto ...
                        //Busca de todos os N.ºs de Prestação de Serviço que estejam em aberto ...
                        $sql = "SELECT id_nf_num_nota, numero_nf 
                                FROM `nfs_num_notas` 
                                WHERE (`nota_usado` = '0' OR `id_nf_num_nota` = '$id_nf_num_nota') 
                                AND `id_empresa` = '$id_empresa_nf' 
                                AND `prestacao_servico` = 'S' ORDER BY numero_nf ";
                    }else {//Em outro Tipo de NF utilizamos nosso número ...
                        faturamentos::gerar_numero_nf($id_empresa_nf);
                        //Busca de todos os N.ºs que estejam em aberto da Empresa selecionada pelo usuário ...
                        $sql = "SELECT id_nf_num_nota, numero_nf 
                                FROM `nfs_num_notas` 
                                WHERE (`nota_usado` = '0' OR `id_nf_num_nota` = '$id_nf_num_nota') 
                                AND `id_empresa` = '$id_empresa_nf' 
                                AND `prestacao_servico` = 'N' ORDER BY numero_nf ";
                    }
                ?>
                <select name='cmb_num_nota_fiscal' title='Selecione o Número da Nota Fiscal' onchange='buscar_dt_emis_num_nf_selec()' class='<?=$class_special;?>' <?=$disabled_special;?>>
                    <?=combos::combo($sql, $id_nf_num_nota);?>
                </select>
<?
/*Se eu tiver algum N.º de Nota selecionado na combo, então eu apresento o N.º Anterior e N.º Posterior 
da que está selecionada na combo*/
                    if(!empty($numero_nf_anterior) || !empty($id_nf_num_nota)) {
?>
                &nbsp;
                <font title='N.º Anterior de Nota Fiscal' style='cursor:help'>
                    <b>N.º Ant: </b>
                </font>
                <?=$numero_nf_anterior;?>
                -&nbsp;<?=data::datetodata($data_emissao_anterior, '/');?>
<?
//Se tiver N.º Posterior, então eu exibo este também ...
                        if(!empty($numero_nf_posterior)) {
?>
                &nbsp;|&nbsp;

                <font title='N.º Posterior de Nota Fiscal' style='cursor:help'>
                    <b>N.º Post: </b>
                </font>
                <?=$numero_nf_posterior;?>
                -&nbsp;<?=data::datetodata($data_emissao_posterior, '/');?>
<?
                        }
                    }
                }else {
                    echo faturamentos::buscar_numero_nf($id_nf_outra, 'O');
//Aqui eu coloco esse objeto para não dar erro de programação no PHP
?>
                <input type='hidden' name='cmb_num_nota_fiscal' value='<?=$id_nf_num_nota;?>'>
<?
                }
            }
        ?>
        </td>
    </tr>
<!--*********************************************Outras Opções*********************************************-->
    <tr class='linhanormal' align='center'>
        <td colspan='2' bgcolor='#CECECE'>
            <font color='red'>
                <b>OUTRAS OPÇÕES</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
                if($qtde_itens_nf > 0) $disabled_opt_outras_opcoes = 'disabled';
            ?>
            <input type='radio' name='opt_outras_opcoes' id='opt_outras_opcoes1' value='1' title='Gerar NF Complementar' onclick='carregar_dados()' class='checkbox' <?=$disabled_opt_outras_opcoes;?>/>
            <label for='opt_outras_opcoes1'>
                Gerar NF Complementar
            </label>
            <br/>
            <input type='radio' name='opt_outras_opcoes' id='opt_outras_opcoes2' value='2' title='Venda para Entrega Futura' onclick='carregar_dados()' class='checkbox' <?=$disabled_opt_outras_opcoes;?>/>
            <label for='opt_outras_opcoes2'>
                Venda para Entrega Futura
            </label>
        </td>
        <td>
            <select name='cmb_dado_escolhido' title='Selecione um Dado' style='visibility:hidden' onchange='trocar_nfs()' class='combo'>
            </select>
            &nbsp;
            <img src = '../../../imagem/lista.jpg' width='20' height='20' border='0' alt='Campos Complementares' id='img_campos_complementares' style='visibility:hidden; cursor:hand' onclick="if(document.form.cmb_dado_escolhido.disabled == false) {nova_janela('campos_complementares.php?id_nf_outra=<?=$id_nf_outra;?>', 'CONSULTAR', '', '', '', '', 350, 800, 'c', 'c')}"/>
            <label id='lbl_campos_complementares' style='visibility:hidden'>
                <font color='darkblue'><b>&nbsp; <= (CAMPOS COMPLEMENTARES)</b></font>
            </label>
            <p/><input type='button' name='cmd_gerar_nota_fiscal_venda_para_entrega_futura' id='cmd_gerar_nota_fiscal_venda_para_entrega_futura' value='Gerar Nota Fiscal de Venda para Entrega Futura' title='Gerar Nota Fiscal de Venda para Entrega Futura' style='visibility:hidden; cursor:hand' onclick='gerar_nota_fiscal_venda_para_entrega_futura()' class='botao'/>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2' bgcolor='#CECECE'>
            &nbsp;	
        </td>
    </tr>
<!--*********************************************************************************************************-->
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
    if(empty($txt_data_emissao)) $txt_data_emissao = $data_emissao;
?>
    <tr class='linhanormal'>
        <td>
            Data de Emissão:
        </td>
        <td>
            <input type='text' name='txt_data_emissao' value='<?=$txt_data_emissao;?>' title='Data de Emissão' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='<?=$class_special;?>' <?=$disabled_special;?>>
            &nbsp;<img src = '../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="if(document.form.txt_data_emissao.disabled == false) {nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1&caixa_auxiliar=controle', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Total ICMS:
        </td>
        <td>
            <input type='text' name='txt_total_icms' value='<?=$total_icms;?>' title='Total ICMS' size='12' maxlength='10' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Gerar Duplicatas:</b>
        </td>
        <td>
            <?
                if($campos[0]['gerar_duplicatas'] == 'S') {
                    $checked_gerar_duplicatas_sim = 'checked';
                }else if($campos[0]['gerar_duplicatas'] == 'N') {
                    $checked_gerar_duplicatas_nao = 'checked';
                }
            ?>
            <input type='radio' name='opt_gerar_duplicatas' value='S' title='Gerar Duplicatas' id='opt_gerar_duplicatas_sim' class='checkbox' <?=$checked_gerar_duplicatas_sim;?>>
            <label id='opt_gerar_duplicatas_sim' for='opt_gerar_duplicatas_sim'>
                Sim
            </label>
            &nbsp;
            <input type='radio' name='opt_gerar_duplicatas' value='N' title='Gerar Duplicatas' id='opt_gerar_duplicatas_nao' class='checkbox' <?=$checked_gerar_duplicatas_nao;?>>
            <label id='opt_gerar_duplicatas_nao' for='opt_gerar_duplicatas_nao'>
                Não
            </label>
        </td>
    </tr>
<?
/***********************Controle que serve para desabilitar os vencimentos***********************/
    if($importado_financeiro == 'S') {//Se estiver importado no Financeiro, travo as caixas mais abaixo ...
        $class      = $class_special ;
        $disabled   = $disabled_special;
    }else {
        $class      = 'caixadetexto';
        $disabled   = '';
    }
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
    if(empty($ja_submeteu)) {//Na primeira vez
        $txt_vencimento1        = $vencimento1;
        $txt_data_vencimento1   = $data_vencimento1;
/*Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP, então uso essa caixa
de empresa para fazer esse macete*/
    }else {
        if(!empty($txt_vencimento1)) {
            $txt_data_vencimento1 = data::adicionar_data_hora($txt_data_emissao, $txt_vencimento1);
        }else {
            $txt_vencimento1 = $vencimento1;
        }
    }
/*******************************************************************************************/
//Aqui eu já tenho o cálculo para o valor das duplicatas
    $valor_duplicata = faturamentos::valor_duplicata_outras_nfs($id_nf_outra, $vetor_nota_sgd['nota_sgd'], $id_pais);
/*******************************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            Vencimento 1:
        </td>
        <td>
            <input type='text' name="txt_vencimento1" value="<?=$txt_vencimento1;?>" title="Digite o Vencimento 1" size="5" maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(1);valor_nota()" class="<?=$class;?>" <?=$disabled;?>>
            DIAS &nbsp;&nbsp;
            <input type='text' name="txt_data_vencimento1" value="<?=$txt_data_vencimento1;?>" title="Data do Vencimento 1" size="12" maxlength="10" class='textdisabled' disabled>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;R$
            <input type='text' name="txt_valor1" size="12" maxlength="10" class='textdisabled' disabled>
            &nbsp;R$&nbsp;<?=number_format($valor_duplicata[0], 2, ',', '.');?>
        </td>
    </tr>
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
    if(empty($ja_submeteu)) {//Na primeira vez
        $txt_vencimento2 = $vencimento2;
        $txt_data_vencimento2 = $data_vencimento2;
/*Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP, então uso essa caixa
de empresa para fazer esse macete*/
    }else {
        if(!empty($txt_vencimento2)) {
            $txt_data_vencimento2 = data::adicionar_data_hora($txt_data_emissao, $txt_vencimento2);
        }else {
            $txt_vencimento2 = $vencimento2;
        }
    }
?>
    <tr class='linhanormal'>
        <td>
            Vencimento 2:
        </td>
        <td>
            <input type='text' name="txt_vencimento2" value="<?=$txt_vencimento2;?>" title="Digite o Vencimento 2" size="5" maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(2);valor_nota()" class="<?=$class;?>" <?=$disabled;?>>
            DIAS &nbsp;&nbsp;
            <input type='text' name="txt_data_vencimento2" value="<?=$txt_data_vencimento2;?>" title="Data do Vencimento 2" size="12" maxlength="10" class='textdisabled' disabled>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;R$
            <input type='text' name="txt_valor2" size="12" maxlength="10" class='textdisabled' disabled>
            &nbsp;R$&nbsp;<?=number_format($valor_duplicata[1], 2, ',', '.');?>
        </td>
    </tr>
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
    if(empty($ja_submeteu)) {//Na primeira vez
        $txt_vencimento3 = $vencimento3;
        $txt_data_vencimento3 = $data_vencimento3;
/*Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP, então uso essa caixa
de empresa para fazer esse macete*/
    }else {
        if(!empty($txt_vencimento3)) {
            $txt_data_vencimento3 = data::adicionar_data_hora($txt_data_emissao, $txt_vencimento3);
        }else {
            $txt_vencimento3 = $vencimento3;
        }
    }
?>
    <tr class='linhanormal'>
        <td>
            Vencimento 3:
        </td>
        <td>
            <input type='text' name="txt_vencimento3" value="<?=$txt_vencimento3;?>" title="Digite o Vencimento 3" size="5" maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(3);valor_nota()"  class="<?=$class;?>" <?=$disabled;?>>
            DIAS &nbsp;&nbsp;
            <input type='text' name="txt_data_vencimento3" value="<?=$txt_data_vencimento3;?>" title="Data do Vencimento 3" size="12" maxlength="10" class='textdisabled' disabled>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;R$
            <input type='text' name="txt_valor3" size="12" maxlength="10" class='textdisabled' disabled>
            &nbsp;R$&nbsp;<?=number_format($valor_duplicata[2], 2, ',', '.');?>
        </td>
    </tr>
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
    if(empty($ja_submeteu)) {//Na primeira vez
        $txt_vencimento4 = $vencimento4;
        $txt_data_vencimento4 = $data_vencimento4;
/*Significa que o usuário manipulou uma transportadora ou algum contato no Pop-UP, então uso essa caixa
de empresa para fazer esse macete*/
    }else {
        if(!empty($txt_vencimento4)) {
            $txt_data_vencimento4 = data::adicionar_data_hora($txt_data_emissao, $txt_vencimento4);
        }else {
            $txt_vencimento4 = $vencimento4;
        }
    }
?>
    <tr class='linhanormal'>
        <td>
            Vencimento 4:
        </td>
        <td>
            <input type='text' name='txt_vencimento4' value='<?=$txt_vencimento4;?>' title="Digite o Vencimento 4" size="5" maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(4);valor_nota()" class="<?=$class;?>" <?=$disabled;?>>
            DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_vencimento4' value="<?=$txt_data_vencimento4;?>" title="Data do Vencimento 4" size="12" maxlength="10" class='textdisabled' disabled>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;R$
            <input type='text' name='txt_valor4' size="12" maxlength="10" class='textdisabled' disabled>
            &nbsp;R$&nbsp;<?=number_format($valor_duplicata[3], 2, ',', '.');?>
        </td>
    </tr>
<?
/************************************************************************************************/
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
    if(empty($txt_valor_frete)) $txt_valor_frete = $valor_frete;
?>
    <tr class='linhanormal'>
        <td>
            Valor do Frete:
        </td>
        <td>
            <input type='text' name='txt_valor_frete' value='<?=$txt_valor_frete;?>' title='Valor do Frete' size='11' maxlength='9' class='textdisabled' disabled>
            &nbsp;
            -
            &nbsp;
            <select name='cmb_modo_envio' title='Modo de Envio' class='combo'>
                <option value='CORREIO'>CORREIO</option>
                <option value='TAM'>TAM</option>
            </select>
            &nbsp;
            <input type='button' name='cmd_calcular_frete' value='Calcular Frete' title='Calcular Frete' onclick='calcular_frete()' class='botao'>
            &nbsp;
            <a href="javascript:nova_janela('http://www2.correios.com.br/sistemas/precosPrazos/', 'CORREIOS', '', '', '', '', 500, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Consultar Sedex (Correios)' class='link'>
                Consultar Sedex (Correios)
            </a>
        </td>
    </tr>
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
    if(empty($txt_data_saida_entrada)) $txt_data_saida_entrada = $data_saida_entrada;
?>
    <tr class='linhanormal'>
        <td>
            Data de Saída / Entrada:
        </td>
        <td>
            <input type='text' name='txt_data_saida_entrada' value='<?=$txt_data_saida_entrada;?>' title="Digite a Data de Saída Entrada" onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            &nbsp;<img src = '../../../imagem/calendario.gif' width="12" height="12" border="0" alt="Calend&aacute;rio Normal" style="cursor:hand" onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_saida_entrada&tipo_retorno=1&caixa_auxiliar=controle', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">&nbsp;Calend&aacute;rio
        </td>
    </tr>
<!--*********************************************Dados de Volume*********************************************-->
<?
    if($status == 0) {//Em Aberto, pode-se manipular todos os dados de Volume ...
        $disabled_volume    = '';
        $class_volume       = 'caixadetexto';
    }else {//Em outros status, sempre tem de se manter travada essas Caixas ...
        $disabled_volume    = 'disabled';
        $class_volume       = 'textdisabled';
    }
?>
    <tr class='linhanormal' align='center'>
        <td colspan='2' bgcolor='#CECECE'>
            <font color='red'>
                <b>DADOS DE VOLUME</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Qtde:</b>
        </td>
        <td>
            <input type='text' name='txt_qtde_volume' value='<?=$qtde_volume;?>' title='Digite a Qtde de Volume' size='10' maxlength='10' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='<?=$class_volume;?>' <?=$disabled_volume;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Espécie:</b>
        </td>
        <td>
            <input type='text' name='txt_especie_volume' value='<?=$especie_volume;?>' title='Digite a Espécie de Volume' size='25' maxlength='25' class='<?=$class_volume;?>' <?=$disabled_volume;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso Bruto de Volume:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_bruto_volume' value='<?=$peso_bruto_volume;?>' title='Digite o Peso Bruto de Volume' size='13' maxlength='13' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='<?=$class_volume;?>' <?=$disabled_volume;?>>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Peso Líquido de Volume:</b>
        </td>
        <td>
            <input type='text' name='txt_peso_liquido_volume' value='<?=$peso_liquido_volume;?>' title='Digite o Peso Líquido de Volume' size='13' maxlength='13' onkeyup="verifica(this, 'moeda_especial', '3', '', event)" class='<?=$class_volume;?>' <?=$disabled_volume;?>>
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2' bgcolor='#CECECE'>
            &nbsp;
        </td>
    </tr>
<!--*********************************************************************************************************-->
<?
//Significa que o usuário ainda não manipulou uma transportadora ou algum contato no Pop-UP
    if(empty($txt_observacao_justificativa)) $txt_observacao_justificativa = $observacao;
?>
    <tr class='linhanormal'>
        <td>
            Observação / Justificativa:
        </td>
        <td>
            <textarea name='txt_observacao_justificativa' cols='63' rows='4' maxlength='255' class='caixadetexto'><?=$txt_observacao_justificativa;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
        <?
            if(empty($_GET['pop_up'])) {//Tela foi aberta de forma normal ...
        ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');trocar_cfops();carregar_dados();trocar_nfs();valor_nota();document.form.txt_data_emissao.focus()" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        <?
            }
        ?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick="fechar(window)" style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<Script Language = 'JavaScript'>
/*Eu tive que desenvolver esse JS aqui em baixo, porque eu carrego as variáveis de CFOP através do PHP e esses códigos 
só estão abaixo do head, a partir da linha 890 ...*/
function trocar_cfops() {
    if(typeof(document.form.cmb_cfop) == 'object') {
//Variáveis para saber as qtdes de CFOP's de Entrada e Saída
        var total_linhas_entrada = eval('<?=$linhas_entrada;?>')
        var total_linhas_saida = eval('<?=$linhas_saida;?>')
//Variáveis para carregar os ids e rótulos das combos, são todas variáveis do Tipo Array
        var ids_entrada = new Array('<?=$linhas_entrada;?>')
        var rotulo_entrada = new Array('<?=$linhas_entrada;?>')
        var ids_saida = new Array('<?=$linhas_saida;?>')
        var rotulo_saida = new Array('<?=$linhas_saida;?>')
//Significa que o usuário selecionou no Option a opção de Nota de Entrada
        if(document.form.opt_nota[0].checked == true) {
<?
//Atribui os valores do PHP no vetorzinho em JS
            for($i = 0; $i < $linhas_entrada; $i++) {
?>
                ids_entrada['<?=$i?>'] = '<?=$campos_entrada[$i]["id_cfops"];?>'
                rotulo_entrada['<?=$i?>'] = '<?=$campos_entrada[$i]["cfop"];?>'
<?
            }
?>
            if(total_linhas_entrada > 0) {//Se existir daí então é q se vai disparar o loop
                document.form.cmb_cfop.length = total_linhas_entrada + 1
                document.form.cmb_cfop[0].value = ''//Índice Zero da Combo
                document.form.cmb_cfop[0].text = 'SELECIONE'//Texto da Combo
//Carrega na Combo os valores que foram guardados no Array
                for(i = 0; i < total_linhas_entrada; i++) {
                    document.form.cmb_cfop[i + 1].value = ids_entrada[i]
                    document.form.cmb_cfop[i + 1].text = rotulo_entrada[i]
                }
            }else {
                document.form.cmb_cfop.length = 1
                document.form.cmb_cfop.value = ''//Índice Zero da Combo
                document.form.cmb_cfop.text = 'SELECIONE'//Texto da Combo
            }
//Significa que o usuário selecionou no Option a opção de Nota de Saída
        }else {
<?
//Atribui os valores do PHP no vetorzinho em JS
            for($i = 0; $i < $linhas_saida; $i++) {
?>
                ids_saida['<?=$i?>'] = '<?=$campos_saida[$i]['id_cfops'];?>'
                rotulo_saida['<?=$i?>'] = '<?=$campos_saida[$i]['cfop'];?>'
<?
            }
?>
            if(total_linhas_saida > 0) {//Se existir daí então é q se vai disparar o loop
                document.form.cmb_cfop.length = total_linhas_saida + 1
                document.form.cmb_cfop[0].value = ''//Índice Zero da Combo
                document.form.cmb_cfop[0].text = 'SELECIONE'//Texto da Combo
//Carrega na Combo os valores que foram guardados no Array
                for(i = 0; i < total_linhas_saida; i++) {
                    document.form.cmb_cfop[i + 1].value = ids_saida[i]
                    document.form.cmb_cfop[i + 1].text = rotulo_saida[i]
                }
            }else {
                document.form.cmb_cfop.length = 1
                document.form.cmb_cfop.value = ''//Índice Zero da Combo
                document.form.cmb_cfop.text = 'SELECIONE'//Texto da Combo
            }
        }
    }
/*Aqui é para reajustar os prazos de Vencimento em cima da Data de Emissão ou da Data do B/L, dependendo
do Tipo de Nota que foi escolhido*/
    document.form.controle.onclick()
}
</Script>
<?}?>