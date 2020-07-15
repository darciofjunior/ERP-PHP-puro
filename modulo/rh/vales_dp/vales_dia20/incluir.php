<?
require('../../../../lib/segurancas.php');
require('../../../../lib/data.php');
require('../../../../lib/depto_pessoal.php');
segurancas::geral('/erp/albafer/modulo/rh/vales_dp/itens/consultar.php', '../../../../');

$mensagem[1] = "<font class='atencao'>JÁ FORAM EMITIDO(S) VALE(S) DO DIA 20 P/ ESSA EMPRESA NESSA DATA DE HOLERITH.</font>";
$mensagem[2] = "<font class='atencao'>NÃO EXISTE NENHUM FUNCIONÁRIO CADASTRADO NESSA EMPRESA.</font>";
$mensagem[3] = "<font class='confirmacao'>VALE DO DIA 20 INCLUIDO COM SUCESSO.</font>";

$data_emissao   = date('Y-m-d');
$data_sys       = date('Y-m-d H:i:s');

if($passo == 1) {
//Aqui nesse loop eu disparo todos os funcionários da Empresa selecionada ...
    for($i = 0; $i < count($_POST['hdd_funcionario']); $i++) {
        //Tratamento com os salários ...
        if($_POST['txt_vlr_salario_pf'][$i] == 0) {//Se não existe Salário PF significa que o Funcionário é Registrado Full ...
            $vlr_acima_limite_pd = $_POST['txt_vlr_acima_limite'][$i];
            $vlr_acima_limite_pf = 0;
        }else {//Tem Flex ou não tem Registro ...
            $vlr_acima_limite_pd = 0;
            $vlr_acima_limite_pf = $_POST['txt_vlr_acima_limite'][$i];
        }
        
        if($_POST['txt_vlr_direito_pd'][$i] > 0) {//Só valores positivos ...
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `qtde_dias_trabalhados`, `valor_acima_limite`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '".$_POST['hdd_funcionario'][$i]."', '1', '".$_POST['txt_qtde_dias_trabalhados'][$i]."', '$vlr_acima_limite_pd', '".$_POST['txt_vlr_direito_pd'][$i]."', '$_POST[cmb_data_holerith]', '$data_emissao', 'PD', '$data_sys') ";
            bancos::sql($sql);
            $id_vales_dps.= bancos::id_registro().', ';
        }
        
        if($_POST['txt_vlr_direito_pf'][$i] > 0) {//Só valores positivos ...
            $sql = "INSERT INTO `vales_dps` (`id_vale_dp`, `id_funcionario`, `tipo_vale`, `qtde_dias_trabalhados`, `valor_acima_limite`, `valor`, `data_debito`, `data_emissao`, `descontar_pd_pf`, `data_sys`) VALUES (NULL, '".$_POST['hdd_funcionario'][$i]."', '1', '".$_POST['txt_qtde_dias_trabalhados'][$i]."', '$vlr_acima_limite_pf', '".$_POST['txt_vlr_direito_pf'][$i]."', '$_POST[cmb_data_holerith]', '$data_emissao', 'PF', '$data_sys') ";
            bancos::sql($sql);
            $id_vales_dps.= bancos::id_registro().', ';
        }
    }
    $id_vales_dps = substr($id_vales_dps, 0, strlen($id_vales_dps) - 2);
?>
    <Script Language = 'Javascript' Src = '../../../../js/nova_janela.js'></Script>
    <Script Language = 'Javascript'>
        window.location = 'incluir.php?cmb_data_holerith=<?=$_POST['cmb_data_holerith'];?>&valor=3'
        nova_janela('../itens/relatorios/relatorio_vale/relatorio.php?id_vales_dps=<?=$id_vales_dps;?>', 'CONSULTAR', 'F')
    </Script>
<?
}else {
//Se tiver uma Empresa selecionada, então listo todos os funcionários daquela Empresa ...
    if(!empty($cmb_empresa)) {
/*Verifico se já existe no Sistema pelo menos 1 funcionário com Vale do Dia20 nessa Data de Holerith e
Empresa selecionada pelo usuário ...*/
        $sql = "SELECT vd.`id_vale_dp` 
                FROM `vales_dps` vd 
                INNER JOIN `funcionarios` f ON f.`id_funcionario` = vd.`id_funcionario` AND f.`id_empresa` = '$cmb_empresa' 
                WHERE vd.`tipo_vale` = '1' 
                AND vd.`data_debito` = '$cmb_data_holerith' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//Já encontrou pelo menos um funcionário ...
/* Então eu listo somente os funcionários da Empresa no qual ainda não foi gerado nenhum Vale ...
* Faça essa nova verificação porque a cada certo período, existem funcionários que são mudados de uma 
Empresa para outra ... */
            $sql = "SELECT `id_funcionario`, `nome`, `tipo_salario`, `salario_pd`, `salario_pf`, `salario_premio`, 
                    `comissao_ultimos3meses_pd`, `comissao_ultimos3meses_pf`, `perc_vale_pd` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` NOT IN (
                            SELECT `id_funcionario` 
                            FROM `vales_dps` 
                            WHERE `tipo_vale` = '1' 
                            AND `data_debito` = '$cmb_data_holerith') 
                    AND `id_empresa` = '$cmb_empresa' 
                    AND `status` < '3' 
                    AND `retirar_vale_dia_20` = 'S' 
                    AND `id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY `nome` ";
            $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
            $linhas = count($campos);
            if($linhas == 0) {//Não encontrou nenhum funcionário com essa marcação ...
?>
                <Script Language = 'Javascript'>
                    window.location = 'incluir.php?cmb_data_holerith=<?=$cmb_data_holerith;?>&valor=2'
                </Script>
<?
                exit;
            }
        }else {
/****************************************************************************************************/
/*Listagem de Funcionários referente a Empresa selecionada, que ainda estão trabalhando 
e que estão com a marcação de Retirar Vale no dia 20 ...*/
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
            $sql = "SELECT id_funcionario, nome, tipo_salario, salario_pd, salario_pf, salario_premio, comissao_ultimos3meses_pd, comissao_ultimos3meses_pf, perc_vale_pd 
                    FROM `funcionarios` 
                    WHERE `id_empresa` = '$cmb_empresa' 
                    AND `status` < '3' 
                    AND `retirar_vale_dia_20` = 'S' 
                    AND `id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) ORDER BY nome ";
            $campos = bancos::sql($sql, $inicio, 100, 'sim', $pagina);
            $linhas = count($campos);
            if($linhas == 0) {//Não encontrou nenhum funcionário com essa marcação ...
?>
                <Script Language = 'Javascript'>
                        window.location = 'incluir.php?cmb_data_holerith=<?=$cmb_data_holerith;?>&valor=2'
                </Script>
<?
                exit;
            }
        }
    }
/*Aqui eu verifico se existe algum Vale em Avulso "Ativo" c/ a Data de Holerith anterior ao dia 5 
do Próximo Mês que não esteja descontado independente da Empresa ...*/
    $sql = "SELECT `id_vale_dp` 
            FROM `vales_dps` 
            WHERE `tipo_vale` = '2' 
            AND `valor` > '0' 
            AND `data_debito` < '$cmb_data_holerith' 
            AND `descontado` = 'N' 
            AND `ativo` = '1' LIMIT 1 ";
    $campos_vale_dp = bancos::sql($sql);
    $id_vale_dp     = $campos_vale_dp[0]['id_vale_dp'];
?>
<html>
<head>
<title>.:: Incluir Vale(s) - Vale do Dia 20 ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var id_vale_dp = eval('<?=$id_vale_dp;?>')
//Significa que existe algum Vale em Avulso c/ a Data de Holerith anterior ao dia 5 do Próximo Mês q não está descontado ...
    if(id_vale_dp > 0) {
        alert('EXISTE(M) VALE(S) AVULSO(S) DE ALGUMA EMPRESA COM DATA DE DÉBITO ANTERIOR À DATA DE HOLERITH QUE NÃO ESTÁ(ÃO) DESCONTADO(S) !!!')
        return false//Não pode gerar ...
    }
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.passo.value = 1
    //Tratamento com o restante dos Objetos ...
    var elementos       = document.form.elements
    //Prepara a Tela p/ poder gravar no BD ...
    if(typeof(elementos['hdd_funcionario[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_funcionario[]'].length)
    }
    //Tratamento nos objetos Vlr PD, PF e Vlr Liberado p/ gravar os objetos no BD ...
    for (var i = 0; i < linhas; i++) {
        document.getElementById('txt_vlr_salario_pd'+i).value   = strtofloat(document.getElementById('txt_vlr_salario_pd'+i).value)
        document.getElementById('txt_vlr_direito_pd'+i).value   = strtofloat(document.getElementById('txt_vlr_direito_pd'+i).value)
        document.getElementById('txt_vlr_salario_pf'+i).value   = strtofloat(document.getElementById('txt_vlr_salario_pf'+i).value)
        document.getElementById('txt_vlr_direito_pf'+i).value   = strtofloat(document.getElementById('txt_vlr_direito_pf'+i).value)
        document.getElementById('txt_vlr_acima_limite'+i).value = strtofloat(document.getElementById('txt_vlr_acima_limite'+i).value)
        //Habilito as caixas p/ poder gravar no Banco ...
        document.getElementById('txt_vlr_salario_pd'+i).disabled    = false
        document.getElementById('txt_vlr_direito_pd'+i).disabled    = false
        document.getElementById('txt_vlr_salario_pf'+i).disabled    = false
        document.getElementById('txt_vlr_direito_pf'+i).disabled    = false
        document.getElementById('txt_vlr_acima_limite'+i).disabled  = false
    }
    document.form.submit()
}

function recarregar_objetos() {
//Aqui é para não atualizar o frames abaixo desse Pop-UP
    document.form.nao_atualizar.value = 1
    document.form.submit()
}

function calcular_valores(indice, vlr_liberado_inicial) {
    var elementos = document.form.elements
//Tratamento com as variáveis que vem por parâmetro ...
    vlr_direito_pd_inicial          = eval(strtofloat(document.getElementById('txt_40_perc_salario_pd'+indice).value))
    vlr_salario_pf                  = eval(strtofloat(document.getElementById('txt_vlr_salario_pf'+indice).value))
    vlr_comissao_ultimos3meses_pf   = eval(strtofloat(document.getElementById('txt_comissao_ultimos3meses_pf'+indice).value))
    vlr_direito_pf_inicial          = eval(strtofloat(document.getElementById('txt_40_perc_salario_pf'+indice).value))
    vlr_liberado_inicial            = eval(strtofloat(vlr_liberado_inicial))
//Igualando as variáveis ...
    var vlr_direito_pd              = eval(strtofloat(document.getElementById('txt_vlr_direito_pd'+indice).value))
    var vlr_pedido                  = (document.getElementById('txt_vlr_pedido'+indice).value == '') ? 0 : eval(strtofloat(document.getElementById('txt_vlr_pedido'+indice).value))
        
    /*Se o funcionário pediu menos do que 40% do salário PD "Valor de Direito - Valor Avulso" no vale, o 
    Valor de Direito fica sendo atribuido no valor a menor desejado ...*/
    if(vlr_pedido <= vlr_direito_pd_inicial || (vlr_salario_pf + vlr_comissao_ultimos3meses_pf == 0)) {//Essa regra so sera valida se o Func nao tiver nada de beneficio PF ...
        document.getElementById('txt_vlr_direito_pd'+indice).value = (vlr_pedido == 0) ? 0 : vlr_pedido
    }else {
        document.getElementById('txt_vlr_direito_pd'+indice).value = vlr_direito_pd_inicial
    }
    document.getElementById('txt_vlr_direito_pd'+indice).value = arred(document.getElementById('txt_vlr_direito_pd'+indice).value, 2, 1)
//Aqui eu reassumo essa variável vlr_direito_pd pq o if acima faz com que essa sofra mudança de valores ...
    var vlr_direito_pd  = eval(strtofloat(document.getElementById('txt_vlr_direito_pd'+indice).value))

    //O PF é a sobra do que o funcionário pediu menos o que já levou de PD ...
    var vlr_direito_pf = (vlr_pedido - vlr_direito_pd)
    if(vlr_direito_pf == 0) {//Não faço a conta de arredondamento de Notas, p/ o sistema não ficar sempre jogando R$ 10,00 acima ...
        document.getElementById('txt_vlr_direito_pf'+indice).value      = vlr_direito_pf
    }else {
        //Aqui eu faco um arrendodamento p/ Notas de R$ 10,00 p/ facilitar troco ...
        document.getElementById('txt_vlr_direito_pf'+indice).value      = (parseInt(vlr_direito_pf / 10) + 1) * 10
    }

    document.getElementById('txt_vlr_direito_pf'+indice).value          = arred(document.getElementById('txt_vlr_direito_pf'+indice).value, 2, 1)
    document.getElementById('txt_vlr_acima_limite'+indice).value        = vlr_pedido - vlr_liberado_inicial
   
    //Nunca podemos ter um valor de Pedido maior do que o Valor Liberado ...
    if(document.getElementById('txt_vlr_acima_limite'+indice).value > 0) {
        document.getElementById('txt_vlr_acima_limite'+indice).style.background = 'red'
        document.getElementById('txt_vlr_acima_limite'+indice).style.color      = 'white'
    }else {
        document.getElementById('txt_vlr_acima_limite'+indice).style.background = '#FFFFE1'
        document.getElementById('txt_vlr_acima_limite'+indice).style.color      = 'black'
        document.getElementById('txt_vlr_acima_limite'+indice).value            = 0
    }
    document.getElementById('txt_vlr_acima_limite'+indice).value = arred(document.getElementById('txt_vlr_acima_limite'+indice).value, 2, 1)
}

function atualizar() {
    document.form.passo.value = 0
    document.form.submit()
}
</Script>
</head>
<body>
<form name='form' method='post' action=''>
<!--Esse hidden é um controle de Tela-->
<input type='hidden' name='cmb_data_holerith' value='<?=$cmb_data_holerith;?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo' onclick="atualizar()">
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='16'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='16'>
            Incluir Vale(s) - Vale do Dia 20 - 
            <input type="button" name="cmd_listagem_contabilidade" value="Listagem p/ Contabilidade" title="Listagem p/ Contabilidade" onclick="nova_janela('listagem_contabilidade.php?cmb_empresa='+document.form.cmb_empresa.value+'&cmb_data_holerith=<?=$cmb_data_holerith;?>', 'LISTAGEM_CONTABILIDADE', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')" style='color:orchid' class='botao'>
        </td>
    </tr>
    <tr class='linhacabecalho'>
        <td> Empresa:
            <select name='cmb_empresa' onchange='recarregar_objetos()' class='combo'>
            <?
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql, $cmb_empresa);
            ?>
            </select>
        </td>
        <td colspan='15'>
            <font color='yellow'>
                Data de Holerith: 
            </font>
            <?=data::datetodata($cmb_data_holerith, '/');?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <font color='yellow'>
                Período: 
            </font>
            <?
                //Busca o Período da Folha ...
                $datas_folha    = depto_pessoal::periodo_folha(data::datetodata($cmb_data_holerith, '/'));
                echo $datas_folha['data_inicial_folha'].' à '.$datas_folha['data_final_folha'];
            ?>
        </td>
    </tr>
<?
//Se não tiver nenhuma Empresa selecionada, então eu exibo esse botão de Voltar p/ a Tela Principal de Vales
	if(empty($cmb_empresa)) {
?>
    <tr>
        <td></td>
    </tr>
    <tr align='center'>
        <td colspan='16'>
            <input type="button" name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../itens/incluir.php'" class='botao'>
        </td>
    </tr>
<?
//Se tiver uma Empresa selecionada, então listo todos os funcionários daquela Empresa ...
	}else {
?>
    <tr class='linhadestaque' align='center'>
        <td>Funcionário</td>
        <td>Qtde <br>Dias</td>
        <td>% Vale <br>PD</td>
        <td>Vlr Sal.PD</td>
        <td>Comis.PD</td>
        <td>Vlr Avulso PD</td>
        <td>X% Sal. - <br>Avulso PD</td>
        <td>Vlr Direito PD</td>
        <td>Vlr Sal.PF</td>
        <td>Comis.PF</td>
        <td>Vlr Avulso PF</td>
         <td>40% Sal. - <br>Avulso PF</td>
        <td>Vlr Direito PF</td>
        <td>Vlr Pedido</td>
        <td>Vlr Acima <br>Limite</td>
        <td>Vlr Credito <br>Consignado</td>
    </tr>
<?
        $cont = 0;
        for($i = 0; $i < $linhas; $i++) {
//Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
            $url = "javascript:nova_janela('../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=".$campos[$i]['id_funcionario']."&pop_up=1', 'DETALHES', '', '', '', '', 550, 900, 'c', 'c', '', '', 's', 's', '', '', '') ";
//Cálculo do Salário ...
            if($campos[$i]['tipo_salario'] == 1) {//Horista
                $vlr_salario_pd = 220 * $campos[$i]['salario_pd'];
                $vlr_salario_pf = 220 * ($campos[$i]['salario_pf'] + $campos[$i]['salario_premio']);
            }else {//Mensalista
                $vlr_salario_pd = $campos[$i]['salario_pd'];
                $vlr_salario_pf = $campos[$i]['salario_pf'] + $campos[$i]['salario_premio'];
            }

            if($cmb_empresa == 4) {//Se a Empresa for Grupo então, não existe PD, afinal não existe Registro ...
                $vlr_salario_pf+= $vlr_salario_pd;
                $vlr_salario_pd = 0;
            }
            
            /*Faço um somatório do Total de Vales em Avulso PD c/ a Data de Holerith anterior ao dia 5 do Próximo Mês 
            que não estejam descontados do Funcionário do Loop ...*/
            $sql = "SELECT SUM(`valor`) AS total_vale_avulso 
                    FROM `vales_dps` 
                    WHERE `tipo_vale` = '2' 
                    AND `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                    AND `data_debito` <= '$cmb_data_holerith' 
                    AND `descontar_pd_pf` = 'PD' 
                    AND `descontado` = 'N' ";
            $campos_avulso          = bancos::sql($sql);
            $vlr_avulso_pd          = $campos_avulso[0]['total_vale_avulso'];
            
            //Verifico quantos dias que o funcionário trabalhou no mês vigente da Folha ...
            $dias_trabalhados       = depto_pessoal::dias_trabalhados($campos[$i]['id_funcionario'], data::datetodata($cmb_data_holerith, '/'));
            
            //40 % do Salário PD ...
            $quarenta_perc_salario_pd   = (($vlr_salario_pd + $campos[$i]['comissao_ultimos3meses_pd']) - $vlr_avulso_pd) * ($dias_trabalhados / 30) * ($campos[$i]['perc_vale_pd'] / 100);
            $vlr_direito_pd             = $quarenta_perc_salario_pd;

            /*Faço um somatório do Total de Vales "Ativos" em Avulso PF c/ a Data de Holerith anterior ao dia 
            5 do Próximo Mês que não estejam descontados do Funcionário do Loop ...*/
            $sql = "SELECT SUM(`valor`) AS total_vale_avulso 
                    FROM `vales_dps` 
                    WHERE `tipo_vale` = '2' 
                    AND `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                    AND `data_debito` <= '$cmb_data_holerith' 
                    AND `descontar_pd_pf` = 'PF' 
                    AND `descontado` = 'N' 
                    AND `ativo` = '1' ";
            $campos_avulso  = bancos::sql($sql);
            $vlr_avulso_pf  = $campos_avulso[0]['total_vale_avulso'];
            
            //40 % do Salário PF ...
            $quarenta_perc_salario_pf   = (0.4 * ($vlr_salario_pf + $campos[$i]['comissao_ultimos3meses_pf']) - $vlr_avulso_pf) * ($dias_trabalhados / 30);
            $vlr_direito_pf             = $quarenta_perc_salario_pf;
            //Aqui eu faco um arrendodamento p/ Notas de R$ 10,00 p/ facilitar troco ...
            if($vlr_direito_pf > 0) $vlr_direito_pf = (intval($vlr_direito_pf / 10) + 1) * 10;
            
            //Estamos arredondando p/ limpar a tela sem poluir com centavos ...
            $vlr_pedido         = round($vlr_direito_pd + $vlr_direito_pf, 0);
            $vlr_liberado       = $vlr_pedido;
?>
    <tr class='linhanormal' align='center'>
        <td align='left'>
            <a href="<?=$url;?>" title='Detalhes Funcionário' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_qtde_dias_trabalhados[]' id='txt_qtde_dias_trabalhados<?=$i;?>' value='<?=$dias_trabalhados;?>' title='Digite a Quantidade de Dias Trabalhados' size='3' maxlength='2' style='color:black' class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_perc_vale_pd[]' id='txt_perc_vale_pd<?=$i;?>' value="<?=$campos[$i]['perc_vale_pd'];?>" title='% Vale PD' size='3' style='color:black' class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_vlr_salario_pd[]' id='txt_vlr_salario_pd<?=$i;?>' value="<?=number_format($vlr_salario_pd, 2, ',', '.');?>" title="Valor do Salário PD" size="10" style="color:black" class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_comissao_ultimos3meses_pd[]' id='txt_comissao_ultimos3meses_pd<?=$i;?>' value="<?=number_format($campos[$i]['comissao_ultimos3meses_pd'], 2, ',', '.');?>" title="Comissao Ultimos 3 Meses PD" size="10" style="color:black" class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_vlr_avulso_pd[]' id='txt_vlr_avulso_pd<?=$i;?>' value="<?=number_format($vlr_avulso_pd, 2, ',', '.');?>" title="Valor Avulso PD" size="10" style="color:black" class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_40_perc_salario_pd[]' id='txt_40_perc_salario_pd<?=$i;?>' value="<?=number_format($quarenta_perc_salario_pd, 2, ',', '.');?>" title="40% do Salário PD" size="10" style="color:black" class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_vlr_direito_pd[]' id='txt_vlr_direito_pd<?=$i;?>' value="<?=number_format($vlr_direito_pd, 2, ',', '.');?>" title="Valor de Direito PD" size="10" style="color:black" class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_vlr_salario_pf[]' id='txt_vlr_salario_pf<?=$i;?>' value="<?=number_format($vlr_salario_pf, 2, ',', '.');?>" title="Valor do Salário PF" size="10" style="color:black" class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_comissao_ultimos3meses_pf[]' id='txt_comissao_ultimos3meses_pf<?=$i;?>' value="<?=number_format($campos[$i]['comissao_ultimos3meses_pf'], 2, ',', '.');?>" title="Comissao Ultimos 3 Meses PF" size="10" style="color:black" class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_vlr_avulso_pf[]' id='txt_vlr_avulso_pf<?=$i;?>' value="<?=number_format($vlr_avulso_pf, 2, ',', '.');?>" title="Valor Avulso PF" size="10" style="color:black" class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_40_perc_salario_pf[]' id='txt_40_perc_salario_pf<?=$i;?>' value="<?=number_format($quarenta_perc_salario_pf, 2, ',', '.');?>" title="40% do Salário PF" size="10" style="color:black" class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_vlr_direito_pf[]' id='txt_vlr_direito_pf<?=$i;?>' value="<?=number_format($vlr_direito_pf, 2, ',', '.');?>" title="Valor de Direito PF" size="10" style="color:black" class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_vlr_pedido[]' id='txt_vlr_pedido<?=$i;?>' value="<?=number_format($vlr_pedido, 2, ',', '.');?>" title="Digite o Valor do Pedido" size="10" onkeyup="verifica(this, 'moeda_especial', '2', '', event);calcular_valores('<?=$i;?>', '<?=number_format($vlr_liberado, 2, ',', '.');?>')" tabindex="<?='1'.$cont;?>" class="caixadetexto">
        </td>
        <td bgcolor='#C8C8C8'>
            <input type='text' name='txt_vlr_acima_limite[]' id='txt_vlr_acima_limite<?=$i;?>' value="0,00" title="Valor Acima Limite" size="10" style="color:black" class='textdisabled' disabled>
        </td>
        <td bgcolor='#C8C8C8'>
        <?
            /*Faço um somatório do Total de Vales Credito Consignado c/ a Data de Holerith anterior ao dia 5 do Próximo Mês 
            que não estejam descontados do Funcionário do Loop ...*/
            $sql = "SELECT SUM(`valor`) AS total_credito_consignado 
                    FROM `vales_dps` 
                    WHERE `tipo_vale` = '14' 
                    AND `id_funcionario` = '".$campos[$i]['id_funcionario']."' 
                    AND `data_debito` <= '$cmb_data_holerith' 
                    AND `descontado` = 'N' ";
            $campos_credito_consignado  = bancos::sql($sql);
            $vlr_credito_consignado     = $campos_credito_consignado[0]['total_credito_consignado'];
        ?>
            <input type='text' name='txt_vlr_credito_consignado[]' id='txt_vlr_credito_consignado<?=$i;?>' value="<?=number_format($vlr_credito_consignado, 2, ',', '.');?>" title="Valor Credito Consignado" size="10" style="color:black" class='textdisabled' disabled>
            &nbsp;
            <input type='hidden' name='hdd_funcionario[]' id='hdd_funcionario<?=$i;?>' value="<?=$campos[$i]['id_funcionario'];?>" size="10">
        </td>
    </tr>
<?
            $cont++;
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='16'>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = '../itens/incluir.php'" class='botao'>
            <input type='button' name="cmd_redefinir" value="Redefinir" title="Redefinir" onclick="document.form.reset()" style="color:#ff9900;" class='botao'>
            <input type='button' name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" onclick="return validar()" class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<?
	}
?>
</body>
</html>
<pre>
<b><font color="red">Observação:</font></b>
<pre><font color="darkblue">
* Os funcionários que estão de férias não aparecem neste <b>TIPO DE VALE.</b>
</font>
</pre>
<?}?>