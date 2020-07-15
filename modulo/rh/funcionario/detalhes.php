<?
require('../../../lib/segurancas.php');
require('../../../lib/data.php');
require('../../../lib/variaveis/dp.php');
segurancas::geral('/erp/albafer/modulo/rh/funcionario/consultar.php', '../../../');

//Busca dos Dados do Funcionário com o id_funcionario passado por parâmetro ...
$sql = "SELECT f.*, ufs.id_uf, ufs.sigla 
        FROM `funcionarios` f 
        INNER JOIN `ufs` ON ufs.id_uf = f.id_uf 
        WHERE `id_funcionario` = '$_GET[id_funcionario_loop]' LIMIT 1 ";
$campos         = bancos::sql($sql);
$cod_civil      = $campos[0]['cod_civil'];
$cod_academico  = $campos[0]['cod_academico'];
$cod_sangue     = $campos[0]['cod_sangue'];
$cod_categoria  = $campos[0]['cod_categoria'];
$cod_banco      = $campos[0]['cod_banco'];
?>
<html>
<head>
<title>.:: Detalhes Funcionário(s) ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
</head>
<body>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Detalhes Funcionário(s)
        </td>
    </tr>
    <tr class='linhadestaque' align="center">
        <td colspan='2'>
            Dado(s) Pessoal(is)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nome:</b> <?=$campos[0]['nome'];?>
        </td>
        <td>
        <?
//Se o Funcionário não tiver foto, então eu exibo o logotipo da Albafer p/ não ficar feio na Tela ...
            if(empty($campos[0]['path_foto'])) {
        ?>
                <img src="../../../imagem/logosistema.jpg" width="110" height="120">
        <?
//Aqui já é a foto do usuário que vai estar sendo exibida normalmente no Sistema ...
            }else {
        ?>
                <img src="../../../imagem/fotos_funcionarios/<?=$campos[0]['path_foto'];?>" title="Clique aqui para ampliar" width="180" height="120">
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Sexo:</b> 
            <?
                if($campos[0]['sexo'] == 'M') {
                    echo 'Masculino';
                }else {
                    echo 'Feminino';
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Nacionalidade:</b>
        <?
                $sql = "SELECT nacionalidade 
                        FROM `nacionalidades` 
                        WHERE `id_nacionalidade` = '".$campos[0]['id_nacionalidade']."' LIMIT 1 ";
                $campos_nacionalidade = bancos::sql($sql);
                echo $campos_nacionalidade[0]['nacionalidade'];
        ?>
        </td>
        <td>
            <b>Estado Civil:</b>
        <?
            if(empty($estado_civil[$cod_civil][0])) {//Certifico que ele não foi deletado, Luis ..
                echo $estado_civil[$cod_civil][1];
            }else {
                echo $estado_civil[$cod_civil][0];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Naturalidade:</b> <?=$campos[0]['naturalidade'];?>
        </td>
        <td>
            <b>Nível Acadêmico:</b>
            <?
                if(empty($nivel_academico[$cod_academico][0])) {//Certifico que ele não foi deletado, Luis ..
                    echo $nivel_academico[$cod_academico][1];
                }else {
                    echo $nivel_academico[$cod_academico][0];
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tipo de Sangue:</b>
        <?
            if(empty($tipo_sangue[$cod_sangue][0])) {//Certifico que ele não foi deletado, Luis ..
                echo $tipo_sangue[$cod_sangue][1];
            }else {
                echo $tipo_sangue[$cod_sangue][0];
            }
        ?>
        </td>
        <td>
            <b>Data de Nascimento:</b> 
            <?=data::datetodata($campos[0]['data_nascimento'], '/');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>RG:</b> <?=$campos[0]['rg'];?>
        </td>
        <td>
            <b>Org&atilde;o Expedidor:</b> <?=$campos[0]['orgao_expedidor'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Emissão:</b>
        <?
            if($campos[0]['data_emissao'] == '0000-00-00') {
                echo '&nbsp;';
            }else {
                echo data::datetodata($campos[0]['data_emissao'], '/');
            }
        ?>
        </td>
        <td>
            <b>Número da Carteira:</b> <?=$campos[0]['carteira_profissional'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Série:</b> <?=$campos[0]['serie_profissional'];?>
        </td>
        <td>
            <b>PIS:</b> <?=$campos[0]['pis'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Título de Eleitor:</b> <?=$campos[0]['titulo_eleitor'];?>
        </td>
        <td>
            <b>CPF:</b> <?=$campos[0]['cpf'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Carteira de Habilitação:</b>
        <?
            if($campos[0]['habilitacao'] == '') {
                echo '&nbsp;';
            }else {
                echo $campos[0]['habilitacao'];
            }
        ?>
        </td>
        <td>
            <b>Categoria da Carteira de Habilitação:</b>
        <?
            if(empty($carteira_habilitacao[$cod_categoria][0])) {//Certifico que ele não foi deletado, Luis ..
                echo $carteira_habilitacao[$cod_categoria][1];
            }else {
                echo $carteira_habilitacao[$cod_categoria][0];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>País:</b>
        <?
            $sql = "SELECT pais 
                    FROM `paises` 
                    WHERE `id_pais` = '".$campos[0]['id_pais']."' LIMIT 1 ";
            $campos_pais = bancos::sql($sql);
            echo $campos_pais[0]['pais'];
        ?>
        </td>
        <td>
            <b>CEP:</b> <?=$campos[0]['cep'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Endereço / N.º / Comp.:</b>
            <?
                echo $campos[0]['endereco'].', '.$campos[0]['numero'];
                //Se existir complemento
                if(!empty($campos[0]['complemento']))                         echo ' / '.$campos[0]['complemento'];
            ?>
        </td>
        <td>
            <b>Bairro:</b> <?=$campos[0]['bairro'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Cidade:</b> <?=$campos[0]['cidade'];?>
        </td>
        <td>
            <b>Estado:</b>
        <?
            $sql = "SELECT sigla 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos[0]['id_uf']."' LIMIT 1 ";
            $campos_uf = bancos::sql($sql);
            echo $campos_uf[0]['sigla'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>DDD Residencial:</b> <?=$campos[0]['ddd_residencial'];?>
        </td>
        <td>
            <b>Telefone Residencial:</b> <?=$campos[0]['telefone_residencial'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>DDD Celular:</b>
            <?
                if($campos[0]['ddd_celular'] == 0) {
                    echo '&nbsp;';
                }else {
                    echo $campos[0]['ddd_celular'];
                }
            ?>
        </td>
        <td>
            <b>Telefone Celular:</b>
        <?
            if($campos[0]['telefone_celular'] == 0) {
                echo '&nbsp;';
            }else {
                echo $campos[0]['telefone_celular'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Email Particular:</b>
        <?
            if($campos[0]['email_particular'] == 0) {
                echo '&nbsp;';
            }else {
                echo $campos[0]['email_particular'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhadestaque' align="center">
        <td colspan='2'>
            Dado(s) Profissional(is)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Código do Funcionário:</b> <?=$campos[0]['codigo_barra'];?>
        </td>
        <td>
            <b>Empresa:</b>
        <?
            $sql = "SELECT nomefantasia 
                    FROM `empresas` 
                    WHERE `id_empresa` = '".$campos[0]['id_empresa']."' 
                    AND `ativo` = '1' LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            echo $campos_empresa[0]['nomefantasia'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Email Interno:</b> <?=$campos[0]['email_interno'];?>
        </td>
        <td>
            <b>Email Externo:</b> <?=$campos[0]['email_externo'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Funcionário Superior:</b>
        <?
//Aqui eu listo o(s) Funcionário(s) que são Superior(es) ...
            if($campos[0]['id_funcionario_superior'] != 0) {
                $sql = "SELECT nome 
                        FROM `funcionarios` 
                        WHERE `id_funcionario` = '".$campos[0]['id_funcionario_superior']."' LIMIT 1 ";
                $campos_supervisor = bancos::sql($sql);
                echo $campos_supervisor[0]['nome'];
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Departamento:</b>
            <?
                $sql = "SELECT departamento 
                        FROM `departamentos` 
                        WHERE `id_departamento` = '".$campos[0]['id_departamento']."' LIMIT 1 ";
                $campos_departamento = bancos::sql($sql);
                echo $campos_departamento[0]['departamento'];
            ?>
        </td>
        <td>
            <b>Cargo:</b>
            <?
                $sql = "SELECT cargo 
                        FROM `cargos` 
                        WHERE `id_cargo` = '".$campos[0]['id_cargo']."' LIMIT 1 ";
                $campos_cargo = bancos::sql($sql);
                echo $campos_cargo[0]['cargo'];
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Este funcionário é Superior: </b>
            <?
                if($campos[0]['status_superior'] == 1) {
                    echo 'SIM';
                }else {
                    echo 'NÃO';
                }
            ?>
        </td>
        <td>
            <b>Tipo de Salário:</b>
            <?
                if($campos[0]['tipo_salario'] == 1) {
                    echo 'HORISTA';
                }else if($campos[0]['tipo_salario'] == 2) {
                    echo 'MENSALISTA';
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Sálario PD:</b>
            <?
                if($campos[0]['salario_pd'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo number_format($campos[0]['salario_pd'], 2, ',', '.');
                }
            ?>
        </td>
        <td>
            <b>Sálario PF:</b>
            <?
                if($campos[0]['salario_pf'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo number_format($campos[0]['salario_pf'], 2, ',', '.');
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Prêmio:</b>
            <?
                if($campos[0]['salario_premio'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo number_format($campos[0]['salario_premio'], 2, ',', '.');
                }
            ?>
        </td>
        <td>
            <b>Garantia Salarial:</b>
            <?
                if($campos[0]['garantia_salarial'] == '0.00') {
                    echo '&nbsp;';
                }else {
                    echo number_format($campos[0]['garantia_salarial'], 2, ',', '.');
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Admissão:</b>
            <?
                if($campos[0]['data_admissao'] == '0000-00-00') {
                    echo '&nbsp;';
                }else {
                    echo data::datetodata($campos[0]['data_admissao'], '/');
                }
            ?>
        </td>
        <td>
            <b>Data de Demissão:</b>
            <?
                if($campos[0]['data_demissao'] == '0000-00-00') {
                    echo '&nbsp;';
                }else {
                    echo data::datetodata($campos[0]['data_demissao'], '/');
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Últimas Férias:</b>
            <?=data::datetodata($campos[0]['ultimas_ferias_data_inicial'], '/').' à '.data::datetodata($campos[0]['ultimas_ferias_data_final'], '/');?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data da Próxima Férias:</b>
            <?
                if($campos[0]['data_prox_ferias'] == '0000-00-00') {
                    echo '&nbsp;';
                }else {
                    echo data::datetodata($campos[0]['data_prox_ferias'], '/');
                }
            ?>
        </td>
        <td>
            <b>Data Máxima de Férias (Venc. Máx. a Gozar):</b>
            <?
                if($campos[0]['data_max_ferias'] == '0000-00-00') {
                    echo '&nbsp;';
                }else {
                    echo data::datetodata($campos[0]['data_max_ferias'], '/');
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?
                $programacao_ferias = $campos[0]['programacao_ferias'];
                $mes_programacao_ferias = substr($programacao_ferias, 5, 2);
//Criei esse vetor aqui porque achei + facil, pra listagem dos Meses no Banco de Dados ...
                $vetor_meses = array('', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro');
                $ano_programacao_ferias = substr($programacao_ferias, 0, 4);
//Se tiver com o campo Programação de Férias preenchido ...
                if($programacao_ferias != '0000-00-00') {
                    $apresentar = $vetor_meses[$mes_programacao_ferias].' de '.$ano_programacao_ferias;
                }else {
                    $apresentar = '&nbsp;';
                }
            ?>
            <b>Programação de Férias:</b> <?=$apresentar;?>
        </td>
        <td>
            <b>Status:</b> 
            <?
//Criei esse vetor aqui porque achei + facil, pra na hora de comparar com o valor q retorna do Banco
                $vetor_status = array('Férias', 'Ativo', 'Afastado', 'Demitido');
                echo $vetor_status[$campos[0]['status']];
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Descrição:</b> <?=$campos[0]['descricao'];?>
        </td>
    </tr>
    <tr class='linhadestaque' align="center">
        <td colspan='2'>
            Vale(s) Transportes
        </td>
    </tr>
<?
    if(!empty($_GET['id_funcionario_loop'])) {
//Aqui traz todos os Vales Transportes que estão relacionados ao Funcionário
        $sql = "SELECT vt.tipo_vt, vt.valor_unitario, fvt.* 
                FROM `funcionarios_vs_vales_transportes` fvt 
                INNER JOIN `vales_transportes` vt ON vt.id_vale_transporte = fvt.id_vale_transporte 
                WHERE fvt.`id_funcionario` = '$_GET[id_funcionario_loop]' ";
        $campos_vt_por_mes = bancos::sql($sql);
        $linhas_vt_por_mes = count($campos_vt_por_mes);
        if($linhas_vt_por_mes > 0) {
?>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center' bgcolor="#CCCCCC" class="linhanormal">
        <td bgcolor="#CCCCCC">
            <b><i>Tipo de Vale Transporte</i></b>
        </td>
        <td bgcolor="#CCCCCC">
            <b><i>Valor Unitário</i></b>
        </td>
        <td bgcolor="#CCCCCC">
            <b><i>Qtde de Vale</i></b>
        </td>
        <td bgcolor="#CCCCCC">
            <b><i>Valor Total</i></b>
        </td>
    </tr>
<?
            for($i = 0; $i < $linhas_vt_por_mes; $i++) {
?>
    <tr class="linhanormal" align="center">
        <td>
            <?=$campos_vt_por_mes[$i]['tipo_vt'];?>
        </td>
        <td align="right">
            <?='R$ '.number_format($campos_vt_por_mes[$i]['valor_unitario'], 2, ',', '.');?>
        </td>
        <td align="right">
            <?=$campos_vt_por_mes[$i]['qtde_vale'];?>
        </td>
        <td align="right">
            <?='R$ '.number_format($campos_vt_por_mes[$i]['valor_unitario'] * $campos_vt_por_mes[$i]['qtde_vale'], 2, ',', '.');?>
        </td>
    </tr>
<?
            }
        }
    }
?>
</table>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Controle(s) Extra(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Pensão Alimentícia:</b> <?=$campos[0]['pensao_alimenticia'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            $imprimir = ($campos[0]['debitar_conv_medico'] == 'S') ? 'SIM' : 'NÃO';
        ?>
            <b>Debitar Convênio Médico:</b> <?=$imprimir;?> - 
            <b>Qtde de Dependentes:</b> <?=$campos[0]['dependentes_conv_medico'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            $imprimir = ($campos[0]['debitar_conv_odonto'] == 'S') ? 'SIM' : 'NÃO';
        ?>
            <b>Debitar Convênio Odontológico:</b> <?=$imprimir;?> - 
            <b>Qtde de Plano(s):</b> <?=$campos[0]['qtde_plano_odonto'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            $imprimir = ($campos[0]['debitar_combustivel'] == 'S') ? 'SIM' : 'NÃO';
        ?>
            <b>Debitar Combustível:</b> <?=$imprimir;?> - 
            <b>Qtde de Litro(s):</b> <?=$campos[0]['qtde_litros_combustivel'];?> - 
        <?
            $imprimir = ($campos[0]['reembolso_combustivel'] == 'S') ? 'SIM' : 'NÃO';
        ?>
            <b>Reembolso</b> <?=$imprimir;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            $imprimir = ($campos[0]['debitar_celular'] == 'S') ? 'SIM' : 'NÃO';
        ?>
            <b>Debitar Celular:</b> <?=$imprimir;?> - 
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            $imprimir = ($campos[0]['retirar_vale_dia_20'] == 'S') ? 'SIM' : 'NÃO';
        ?>
            <b>Retirar Vale do Dia 20:</b> <?=$imprimir;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            $imprimir = ($campos[0]['debitar_mensal_sindical'] == 'S') ? 'SIM' : 'NÃO';
        ?>
            <b>Debitar Mensalidade Sindical:</b> <?=$imprimir;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            $imprimir = ($campos[0]['debitar_contrib_federativa'] == 'S') ? 'SIM' : 'NÃO';
        ?>
            <b>Debitar Contribuição Federativa:</b> <?=$imprimir;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            $imprimir = ($campos[0]['retira_vale_transporte'] == 'S') ? 'SIM' : 'NÃO';
        ?>
            <b>Retirar Vale Transporte:</b> <?=$imprimir;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            $imprimir = ($campos[0]['conducao_propria'] == 'S') ? 'SIM' : 'NÃO';
        ?>
            <b>Condução Própria:</b> <?=$imprimir;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
        <?
            $imprimir = ($campos[0]['sindicalizado'] == 'S') ? 'SIM' : 'NÃO';
        ?>
            <b>Sindicalizado:</b> <?=$imprimir;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Código do Banco:</b> 
            <?
                if(empty($cadastro_banco[$cod_banco][0])) {//Certifico que ele não foi deletado, Luis ..
                    echo $cadastro_banco[$cod_banco][1];
                }else {
                    echo $cadastro_banco[$cod_banco][0];
                }
            ?>
            &nbsp;-&nbsp;<b>Forma de Pagamento:</b> 
            <?
                if($campos[0]['cheque_dinheiro'] == 'N') {
                    echo 'NENHUM';
                }else if($campos[0]['cheque_dinheiro'] == 'C') {
                    echo 'CHEQUE';
                }else if($campos[0]['cheque_dinheiro'] == 'D') {
                    echo 'DINHEIRO';
                }
            ?>
        </td>
        <td>
            <b>Agência:</b> <?=$campos[0]['agencia'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Conta Corrente:</b> <?=$campos[0]['conta_corrente'];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>