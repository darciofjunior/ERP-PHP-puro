<?
require('../../../../../../lib/segurancas.php');
require('../../../../../../lib/calculos.php');//Essa biblioteca È chamada aqui porque a mesma È utilizada dentro da Faturamentos ...
require('../../../../../../lib/data.php');
require('../../../../../../lib/faturamentos.php');
require('../../../../../../lib/financeiros.php');
require('../../../../../../lib/genericas.php');
require('../../../../../classes/array_sistema/array_sistema.php');
session_start('funcionarios');

if($id_emp == 1) {
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/albafer/index.php';
}else if($id_emp == 2) {
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/tool_master/index.php';
}else if($id_emp == 4) {
	$endereco = '/erp/albafer/modulo/financeiro/recebimento/a_receber/grupo/index.php';
}
segurancas::geral($endereco, '../../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='atencao'>N√O EXISTE(M) NOTA(S) FISCAL(IS) NESSA CONDI«√O.</font>";
$mensagem[3] = "<font class='confirmacao'>CONTA ¿ RECEBER INCLUIDA COM SUCESSO.</font>";

//Essa funÁ„o ser· utilizada mais abaixo ...
function calc_semana($data_emissao, $prazo) {
    $data_emissao_br    = data::datetodata($data_emissao, '-');
    $data_vencimento    = data::adicionar_data_hora($data_emissao_br, $prazo);
    $dia                = substr($data_vencimento, 0, 2);
    $mes                = substr($data_vencimento, 3, 2);
    $ano                = substr($data_vencimento, 6, 4);
    $retorno[]          = $data_vencimento;
    $retorno[]          = data::numero_semana($dia, $mes, $ano);
    return $retorno;
}

if($passo == 1) {
//Aqui eu busco os dados da Nota Fiscal com o id_nf passado por par‚metro
//Aqui traz os dados da Nota Fiscal
    $sql = "SELECT nfso.*, c.`id_pais`, c.`razaosocial` 
            FROM `nfs_outras` nfso 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
            WHERE nfso.`id_nf_outra` = '$_GET[id_nf_outra]' LIMIT 1 ";
    $campos                 = bancos::sql($sql);
//Coloquei esse nome na vari·vel porque na sess„o j· existe uma vari·vel com o nome de id_empresa
    $id_empresa_nota        = $campos[0]['id_empresa'];
    $id_pais                = $campos[0]['id_pais'];
    $id_cliente             = $campos[0]['id_cliente'];
    $razaosocial            = $campos[0]['razaosocial'];
    $numero_nf              = faturamentos::buscar_numero_nf($_GET['id_nf_outra'], 'O');
    $data_emissao           = data::datetodata($campos[0]['data_emissao'], '/');
	
//Aqui verifica o Tipo de Nota
    if($id_empresa_nota == 1 || $id_empresa_nota == 2) {
        $nota_sgd   = 'N';//var surti efeito l· embaixo
        $tipo_nota  = ' (NF)';
    }else {
        $nota_sgd   = 'S'; //var surti efeito l· embaixo
        $tipo_nota  = ' (SGD)';
    }

    if($campos[0]['data_emissao'] != '0000-00-00') $data_emissao = data::datetodata($campos[0]['data_emissao'], '/');
//Prazos
    $vencimento1        = $campos[0]['vencimento1'];
    $vencimento2        = ($campos[0]['vencimento2'] == 0) ? '' : $campos[0]['vencimento2'];
    $vencimento3        = ($campos[0]['vencimento3'] == 0) ? '' : $campos[0]['vencimento3'];
    $vencimento4        = ($campos[0]['vencimento4'] == 0) ? '' : $campos[0]['vencimento4'];
    $valor_dolar_nota   = $campos[0]['valor_dolar_dia'];
    $observacao         = $campos[0]['observacao'];
//Aqui eu j· tenho o c·lculo para o valor das duplicatas
    $valor_duplicata    = faturamentos::valor_duplicata_outras_nfs($_GET['id_nf_outra'], $nota_sgd, $id_pais);
?>
<html>
<head>
<title>.:: Liberar NF Outras (Autom·tico) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Tipo de Recebimento
    if(!combo('form', 'cmb_tipo_recebimento', '', 'SELECIONE UM TIPO DE RECEBIMENTO !')) {
        return false
    }
<?
/*P/ as empresas Albafer e Tool Master, traz a caixa de texto de descriÁ„o
da conta*/
    if($id_emp != 4) {
?>
        if(document.form.txt_descricao_conta.value != '') {
            if(!texto('form','txt_descricao_conta', '2', '-=!@π≤≥£¢¨{}1234567890qwertyuiopÁlkjhgfdsazxcvbnmQWERTYUIOPLK«J.|HGFDSAZXCVBNM,.‹¸·ÈßÌÛ˙¡…Õ¿‡∫”⁄‚ÍÓÙ˚¬ Œ‘€„ı√’{[]}.,%&*$()@#<>™∫∞:;\/ ','DESCRI«√O DA CONTA','1')) {
                return false
            }
        }
<?
    }else {
?>
        if(!combo('form', 'cmb_descricao_conta', '', 'SELECIONE A DESCRI«√O DA CONTA !')) {
            return false
        }
<?
    }
?>
//Taxa de Juros
    if(!texto('form', 'txt_taxa_juros', '2', '0123456789,.', 'TAXA DE JUROS', '1')) {
        return false
    }
//Aqui desabilita os campos travados para poder gravar no BD
    for(i = 0; i < document.form.elements.length; i++) document.form.elements[i].disabled = false
//Desabilito o Bot„o para o usu·rio n„o ficar incluindo v·rias vezes a mesma Nota no BD
    document.form.cmd_salvar.disabled = true
    limpeza_moeda('form', 'txt_taxa_juros, ')
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit='return validar()'>
<input type='hidden' name='id_nf_outra' value='<?=$_GET['id_nf_outra'];?>'>
<input type='hidden' name='id_emp' value='<?=$_GET['id_emp'];?>'>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            Liberar NF Outras (Autom·tico)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Cliente:</b>
        </td>
        <td colspan='2'>
            <b>N.∫ da Conta / Nota:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font size='-2'>
                <?=$razaosocial;?>
            </font>
        </td>
        <td colspan='2'>
            <a href="javascript:nova_janela('../../../../../faturamento/nfs_consultar/cabecalho_nfs_outras.php?id_nf_outra=<?=$_GET['id_nf_outra'];?>', 'DETALHES', '', '', '', '', 700, 850, 'c', 'c', '', '', 's', 's', '', '', '')" title='Visualizar Faturamento' class='link'>
                <?=$numero_nf;?>
            </a>
            <input type='hidden' name='txt_numero_conta' value="<?=$numero_nf;?>">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Tipo de Recebimento:</b>
        </td>
        <td colspan='2'>
            <b>Banco:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <select name='cmb_tipo_recebimento' title='Tipo de Recebimento' class='textdisabled' disabled>
            <?
                $sql = "SELECT `id_tipo_recebimento`, `recebimento` 
                        FROM `tipos_recebimentos` 
                        WHERE `ativo` = '1' ";
//Se a Empresa for = GRUPO, traz Default como CobranÁa Simples
                if($id_emp == 4) {
                    echo combos::combo($sql, 2);//Traz a OpÁ„o de Carteira Selecionada
                    $id_tipo_recebimento = 2;//Vari·vel utilizada mais abaixo
//Se a Empresa for = ALBAFER ou TOOL MASTER traz Default como Carteira
                }else {
                    echo combos::combo($sql, 2);
//Vari·vel utilizada mais abaixo
                    $id_tipo_recebimento = 2;
                }
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
        <?
            if($id_emp != 4) {
                echo 'DescriÁ„o da Conta:';
            }else {
                echo '<b>DescriÁ„o da Conta:<b>';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
        <?
//P/ as empresas Albafer e Tool Master, traz a caixa de texto de descriÁ„o da conta ...
            if($id_emp != 4) {
        ?>
            <input type='text' name='txt_descricao_conta' value='' size='20' title='Digite a DescriÁ„o da Conta' class='caixadetexto'>
        <?
            }else {
        ?>
            <select name='cmb_descricao_conta' title='DescriÁ„o da Conta' class='textdisabled' disabled>
                <option value='' style="color:red">SELECIONE</option>
                <option value="NE">NE</option>
                <?
//Quando for Carteira, traz est· opÁ„o marcada
                    if($id_tipo_recebimento == 2) {
                ?>
                        <option value='PED S/ BOLETO' selected>PED S/ BOLETO</option>
                <?
//Quando for CobranÁa Simples, traz est· opÁ„o marcada
                    }else if($id_tipo_recebimento == 3) {
                ?>
                        <option value='PED C/ BOLETO' selected>PED C/ BOLETO</option>
                <?
                    }
                ?>
                <option value='CHEQUE DEVOLVIDO'>CHEQUE DEVOLVIDO</option>
            </select>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <b>Taxa Juros:</b>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <?
//Retorna a Taxa de Juros cadastrada pelo pessoal do Financeiro ...
                $taxa_juros_financeiro = number_format(genericas::variavel(39), 2, ',', '.');
            ?>
            <input type='text' name='txt_taxa_juros' value='<?=$taxa_juros_financeiro;?>' title='Taxa Juros' size='8' maxlength='5' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <b>Data de Emiss„o:</b>
            <?=$data_emissao;?>
        </td>
        <td colspan='2'>
            <font color='blue'>
                Valor DÛlar da Nota:
            </font>
            <?='R$ '.number_format($valor_dolar_nota, 4, ',', '.');?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <b>N.&ordm; da Duplicata</b>
        </td>
        <td>
            <b> Valor em 
            <?
                $sql = "SELECT simbolo 
                        FROM `tipos_moedas` 
                        WHERE `ativo` = '1' ";
                $campos_moeda = bancos::sql($sql);
                if($id_pais == 31) {//Quando for brasil, È R$
                    $simbolo_moeda = $campos_moeda[0]['simbolo'];
                }else {//Quando for paÌs Internacional, U$
                    $simbolo_moeda = $campos_moeda[1]['simbolo'];
                }
                echo $simbolo_moeda;
            ?>
            </b>
        </td>
        <td>
            <b>Dias</b>
        </td>
        <td>
            <b>Data de Vencimento</b>
        </td>
    </tr>
<?
//Aki verifica se a Duplicatas, j· foram importadas do Faturamento para o Financeiro
    $sql = "SELECT cr.num_conta 
            FROM `contas_receberes` cr 
            INNER JOIN `nfs_outras` nfso ON nfso.`id_nf_outra` = cr.`id_nf_outra` AND nfso.`id_cliente` = '$id_cliente' 
            WHERE cr.`id_empresa` = '$id_empresa_nota' 
            AND (cr.`num_conta` LIKE '".$numero_nf."_' OR cr.`num_conta` LIKE '$numero_nf') ORDER BY cr.num_conta LIMIT 4 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
        for($i = 0;$i < $linhas; $i++) {
            if($campos[$i]['num_conta'] == $numero_nf.'A' || $campos[$i]['num_conta'] == $numero_nf) {
                $a = 1;//N„o mostra a duplicata A
            }else if($campos[$i]['num_conta'] == $numero_nf.'B') {
                $b = 1;//N„o mostra a duplicata B
            }else if($campos[$i]['num_conta'] == $numero_nf.'C') {
                $c = 1;//N„o mostra a duplicata C
            }else if($campos[$i]['num_conta'] == $numero_nf.'D') {
                $d = 1;//N„o mostra a duplicata D
            }
        }
    }
    if(!isset($a)) {
?>
    <tr class='linhanormal' align='center'>
        <td>
        <?
            if($vencimento2 != 0) {// pois se sÛ tiver uma duplicata, nao precisa de letra
                echo $numero_nf.'A';
            }else {
                echo $numero_nf;
            }
        ?>
        </td>
        <td align='right'>
            <?=number_format($valor_duplicata[0], 2, ',', '.');?>
        </td>
        <td>
            <?=$vencimento1;?>
        </td>
        <td>
            <?=data::adicionar_data_hora($data_emissao, $vencimento1);?>
        </td>
    </tr>
<?
    }
    if($vencimento2 != 0 && !isset($b)) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$numero_nf;?>B
        </td>
        <td align='right'>
            <?=number_format($valor_duplicata[1], 2, ',', '.');?>
        </td>
        <td>
            <?=$vencimento2;?>
        </td>
        <td>
            <?=data::adicionar_data_hora($data_emissao, $vencimento2);?>
        </td>
    </tr>
<?
    }
    if($vencimento3 != 0 && !isset($c)) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$numero_nf;?>C
        </td>
        <td align='right'>
            <?=number_format($valor_duplicata[2], 2, ',', '.');?>
        </td>
        <td>
            <?=$vencimento3;?>
        <td>
            <?=data::adicionar_data_hora($data_emissao, $vencimento3);?>
        </td>
    </tr>
<?
    }
    if($vencimento4 != 0 && !isset($d)) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$numero_nf;?>D
        </td>
        <td align='right'>
            <?=number_format($valor_duplicata[3], 2, ',', '.');?>
        </td>
        <td>
            <?=$vencimento4;?>
        </td>
        <td>
            <?=data::adicionar_data_hora($data_emissao, $vencimento4);?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhanormal'>
        <td colspan='4'>
            <b>ObservaÁ„o da NF:</b>
            <?=$observacao;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            ObservaÁ„o:
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='4'>
            <textarea name='txt_observacao' title='Digite a ObservaÁ„o' rows='5' cols='100' maxlength='500' class='caixadetexto'><?=$txt_observacao;?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir_nfs_outras.php?id_emp=<?=$id_emp;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' style='color:red' onclick="fechar(window)" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 2) {
/*Aqui È sÛ quando a empresa for do tipo grupo, eu faÁo esse macete porque n„o existe caixa de texto para 
essa empresa, e sim o que existe È uma combo no lugar*/
    $descricao_conta    = ($id_emp == 4) ? $_POST['cmb_descricao_conta'] : $_POST['txt_descricao_conta'];
    $data_sys           = date('Y-m-d H:i:s');
    $data_atual         = date('Y-m-d');
    
    $sql = "SELECT c.`id_cliente`, c.`id_pais`, nnn.`numero_nf`, nfso.`id_nf_comp`, nfso.`vencimento1`, 
            nfso.`vencimento2`, nfso.`vencimento3`, nfso.`vencimento4`, nfso.`valor1`, nfso.`valor2`, nfso.`valor3`, 
            nfso.`valor4`, nfso.`data_emissao` 
            FROM `nfs_outras` nfso 
            INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfso.`id_nf_num_nota` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
            WHERE nfso.`id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    $id_cliente     = $campos[0]['id_cliente'];
    $id_pais        = $campos[0]['id_pais'];
//Quando for do Brasil ser· em Reais ...
    $id_tipo_moeda  = ($id_pais == 31) ? 1 : 2;
    $numero_nf      = $campos[0]['numero_nf'];
    $id_nf_comp     = $campos[0]['id_nf_comp'];
    $vencimento1    = $campos[0]['vencimento1'];
    $vencimento2    = $campos[0]['vencimento2'];
    $vencimento3    = $campos[0]['vencimento3'];
    $vencimento4    = $campos[0]['vencimento4'];
    $valor1         = $campos[0]['valor1'];
    $valor2         = $campos[0]['valor2'];
    $valor3         = $campos[0]['valor3'];
    $valor4         = $campos[0]['valor4'];
    $data_emissao   = $campos[0]['data_emissao'];
/**************************************************************************************************/
//Se existir NF complementar ...
    if($id_nf_comp > 0) {//Busco o Representante da NF de SaÌda p/ gravar na Duplicata e evitar pau de Banco de Dados
//Verifico qual foi o Representante que teve a maior venda em Nota Fiscal 
//Aqui eu coloco esse comando (sum) para me retornar o representante que teve a maior venda na NF ...
        $sql = "SELECT SUM(valor_unitario) AS valor_unitario, id_representante AS id_rep_melhor_desempenho 
                FROM `nfs_itens` 
                WHERE `id_nf` = '$id_nf_comp' GROUP BY id_representante ORDER BY valor_unitario DESC LIMIT 1 ";
        $campos_rep         = bancos::sql($sql);
        $id_representante   = $campos_rep[0]['id_rep_melhor_desempenho'];
    }
//Aki verifica se a Duplicatas, j· foram importadas do Faturamento para o Financeiro
    $sql = "SELECT cr.num_conta 
            FROM `contas_receberes` cr 
            WHERE (cr.num_conta LIKE '".$numero_nf."_' OR cr.num_conta LIKE '$numero_nf') 
            AND `id_nf_outra` > '0' ORDER BY cr.num_conta LIMIT 4 ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
        for($i = 0; $i < $linhas; $i++) {
            if($campos[$i]['num_conta'] == $numero_nf.'A' || $campos[$i]['num_conta'] == $numero_nf) {
                $a = 1;//N„o mostra a duplicata A
            }else if($campos[$i]['num_conta']==$numero_nf.'B') {
                $b = 1;//N„o mostra a duplicata B
            }else if($campos[$i]['num_conta']==$numero_nf.'C') {
                $c = 1;//N„o mostra a duplicata C
            }else if($campos[$i]['num_conta']==$numero_nf.'D') {
                $d = 1;//N„o mostra a duplicata D
            }
        }
    }

    if(!isset($a)) {
        //Pois se sÛ tiver uma duplicata, nao precisa de letra ...
        $num_conta      = ($vencimento2 != 0) ? $_POST['txt_numero_conta'].'A' : $_POST['txt_numero_conta'];
        $retorno        = calc_semana($data_emissao, $vencimento1);
        $vencimento1    = data::datatodate($retorno[0], '-');
        $semana         = $retorno[1];
        $sql = "INSERT INTO `contas_receberes` (`id_conta_receber`, `id_empresa`, `id_tipo_recebimento`, `id_funcionario`, `id_cliente`, `id_tipo_moeda`, `num_conta`, `descricao_conta`, `semana`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `data_recebimento`, `valor`, `taxa_juros`, `data_sys`, `status`, `ativo`) VALUES (NULL, '$_POST[id_emp]', '$_POST[cmb_tipo_recebimento]', '$_SESSION[id_funcionario]', '$id_cliente', '$id_tipo_moeda', '$num_conta', '$descricao_conta', '$semana', '$data_emissao', '$vencimento1', '$vencimento1', '$vencimento1', '$valor1', '$_POST[txt_taxa_juros]', '$data_sys', '0', '1') ";
        bancos::sql($sql);
        $id_conta_receber = bancos::id_registro();
        //Se existir representante, ent„o vinculo o seu id na tabela de Contas ‡ Receber ...
        if(!empty($id_representante)) {
            $sql = "UPDATE `contas_receberes` SET `id_representante` = '$id_representante' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Registrando Follow-UP(s) ...
        if(!empty($_POST['txt_observacao'])) {
            if(empty($id_representante)) $id_representante = 'NULL';
            
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', $id_representante, '$_SESSION[id_funcionario]', '$id_conta_receber', '4', '".$_POST['txt_observacao']."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        $sql = "UPDATE `contas_receberes` SET `id_cliente` = '$id_cliente', `id_nf_outra` = '$_POST[id_nf_outra]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
        bancos::sql($sql);
        
        financeiros::atualizar_data_alterada($id_conta_receber, 'R');
    }
	
    if($vencimento2 != 0 && !isset($b)) {
        $num_conta      = $_POST['txt_numero_conta'].'B';
        $retorno        = calc_semana($data_emissao, $vencimento2);
        $vencimento2    = data::datatodate($retorno[0], '-');
        $semana         = $retorno[1];
        $sql = "INSERT INTO `contas_receberes` (`id_conta_receber`, `id_empresa`, `id_tipo_recebimento`, `id_funcionario`, `id_cliente`, `id_tipo_moeda`, `num_conta`, `descricao_conta`, `semana`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `data_recebimento`, `valor`, `taxa_juros`, `data_sys`, `status`, `ativo`) VALUES (NULL, '$_POST[id_emp]', '$_POST[cmb_tipo_recebimento]', '$_SESSION[id_login]', '$id_cliente', '$id_tipo_moeda', '$num_conta', '$descricao_conta', '$semana', '$data_emissao', '$vencimento2', '$vencimento2', '$vencimento2', '$valor2', '$_POST[txt_taxa_juros]', '$data_sys', '0', '1') ";
        bancos::sql($sql);
        $id_conta_receber = bancos::id_registro();
        //Se existir representante, ent„o vinculo o seu id na tabela de Contas ‡ Receber ...
        if(!empty($id_representante)) {
            $sql = "UPDATE `contas_receberes` SET `id_representante` = '$id_representante' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Registrando Follow-UP(s) ...
        if(!empty($_POST['txt_observacao'])) {
            if(empty($id_representante)) $id_representante = 'NULL';
            
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', $id_representante, '$_SESSION[id_funcionario]', '$id_conta_receber', '4', '".$_POST['txt_observacao']."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        $sql = "UPDATE `contas_receberes` SET `id_cliente` = '$id_cliente', `id_nf_outra` = '$_POST[id_nf_outra]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
        bancos::sql($sql);
        
        financeiros::atualizar_data_alterada($id_conta_receber, 'R');
    }
	
    if($vencimento3 != 0 && !isset($c)) {
        $num_conta      = $_POST['txt_numero_conta'].'C';
        $retorno        = calc_semana($data_emissao, $vencimento3);
        $vencimento3    = data::datatodate($retorno[0], '-');
        $semana         = $retorno[1];
        $sql = "INSERT INTO `contas_receberes` (`id_conta_receber`, `id_empresa`, `id_tipo_recebimento`, `id_funcionario`, `id_cliente`, `id_tipo_moeda`, `num_conta`, `descricao_conta`, `semana`, `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `data_recebimento`, `valor`, `taxa_juros`, `data_sys`, `status`, `ativo`) VALUES (NULL, '$_POST[id_emp]', '$_POST[cmb_tipo_recebimento]', '$_SESSION[id_login]', '$id_cliente', '$id_tipo_moeda', '$num_conta', '$descricao_conta', '$semana', '$data_emissao', '$vencimento3', '$vencimento3', '$vencimento3', '$valor3', '$_POST[txt_taxa_juros]', '$data_sys', '0', '1') ";
        bancos::sql($sql);
        $id_conta_receber = bancos::id_registro();
        //Se existir representante, ent„o vinculo o seu id na tabela de Contas ‡ Receber ...
        if(!empty($id_representante)) {
            $sql = "UPDATE `contas_receberes` SET `id_representante` = '$id_representante' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Registrando Follow-UP(s) ...
        if(!empty($_POST['txt_observacao'])) {
            if(empty($id_representante)) $id_representante = 'NULL';
            
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', $id_representante, '$_SESSION[id_funcionario]', '$id_conta_receber', '4', '".$_POST['txt_observacao']."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        $sql = "UPDATE `contas_receberes` SET `id_cliente` = '$id_cliente', `id_nf_outra` = '$_POST[id_nf_outra]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
        bancos::sql($sql);
        
        financeiros::atualizar_data_alterada($id_conta_receber, 'R');
    }

    if($vencimento4 != 0 && !isset($d)) {
        $num_conta      = $_POST['txt_numero_conta'].'D';
        $retorno        = calc_semana($data_emissao, $vencimento4);
        $vencimento4    = data::datatodate($retorno[0], '-');
        $semana         = $retorno[1];
        $sql = "INSERT INTO `contas_receberes` (`id_conta_receber`, `id_empresa`, `id_tipo_recebimento`, `id_funcionario`, `id_cliente`, `id_tipo_moeda`, `num_conta`, `descricao_conta`, `semana` , `data_emissao`, `data_vencimento`, `data_vencimento_alterada`, `data_recebimento`, `valor`, `taxa_juros`, `data_sys`, `status` , `ativo`) VALUES (NULL, '$_POST[id_emp]', '$_POST[cmb_tipo_recebimento]', '$_SESSION[id_login]', '$id_cliente', '$id_tipo_moeda', '$num_conta', '$descricao_conta', '$semana', '$data_emissao', '$vencimento4', '$vencimento4', '$vencimento4', '$valor4', '$_POST[txt_taxa_juros]', '$data_sys', '0', '1') ";
        bancos::sql($sql);
        $id_conta_receber = bancos::id_registro();
        //Se existir representante, ent„o vinculo o seu id na tabela de Contas ‡ Receber ...
        if(!empty($id_representante)) {
            $sql = "UPDATE `contas_receberes` SET `id_representante` = '$id_representante' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
            bancos::sql($sql);
        }
        //Registrando Follow-UP(s) ...
        if(!empty($_POST['txt_observacao'])) {
            if(empty($id_representante)) $id_representante = 'NULL';
            
            $sql = "INSERT INTO `follow_ups` (`id_follow_up`, `id_cliente`, `id_representante`, `id_funcionario`, `identificacao`, `origem`, `observacao`, `data_sys`) VALUES (NULL, '$id_cliente', $id_representante, '$_SESSION[id_funcionario]', '$id_conta_receber', '4', '".$_POST['txt_observacao']."', '".date('Y-m-d H:i:s')."') ";
            bancos::sql($sql);
        }
        $sql = "UPDATE `contas_receberes` SET `id_cliente` = '$id_cliente', `id_nf_outra` = '$_POST[id_nf_outra]' WHERE `id_conta_receber` = '$id_conta_receber' LIMIT 1 ";
        bancos::sql($sql);
        
        financeiros::atualizar_data_alterada($id_conta_receber, 'R');
    }
    //Muda o Flag da NF Outra p/ "Importada" ...
    $sql = "UPDATE `nfs_outras` SET `importado_financeiro` = 'S' WHERE `id_nf_outra` = '$_POST[id_nf_outra]' LIMIT 1 ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        opener.parent.itens.document.form.recarregar.value = 1
        window.location = 'incluir_nfs_outras.php?id_emp=<?=$_POST['id_emp'];?>&valor=3'
    </Script>
<?
}else {
/*Aqui todas as Notas Fiscais que j· podem ser liberadas da mesma empresa do Menu,
nas condiÁıes de Liberada / Empacotada ou Despachada, sÛ exibe Notas a partir do MÍs de Maio
e que tenham valor de Faturamento > R$ 0,00*/
    $sql = "(SELECT c.`razaosocial`, nfso.`id_nf_outra`, nfso.`id_empresa`, nfso.`data_emissao`, 
            nfso.`vencimento1`, nfso.`vencimento2`, nfso.`vencimento3`, nfso.`vencimento4`, nfso.`status`, 
            t.`nome` AS transportadora 
            FROM `nfs_outras` nfso 
            INNER JOIN `nfs_num_notas` nnn ON nnn.id_nf_num_nota = nfso.id_nf_num_nota 
            INNER JOIN `transportadoras` t ON t.id_transportadora = nfso.id_transportadora 
            INNER JOIN `clientes` c ON c.id_cliente = nfso.id_cliente 
            WHERE nfso.gerar_duplicatas = 'S' 
            AND nfso.`id_empresa` = '$id_emp' 
            AND nfso.`status` IN (2, 3, 4) 
            AND nfso.`importado_financeiro` = 'N' 
            AND nfso.`valor1` <> '0' ORDER BY nnn.`numero_nf`) ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);  
?>
<html>
<head>
<title>.:: Liberar NF Outras (Autom·tico) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP, tem um controle um pouquinho diferente
function atualizar_abaixo() {
//Significa que sÛ atualiza em baixo quando for pelo clique do X do Pop-Up
//Vari·vel referente ao Frame de Baixo
    var recarregar = opener.parent.itens.document.form.recarregar.value
    if(recarregar == 1 && document.form.ignorar.value == 0) {
        if(typeof(opener.parent.itens.document.form) == 'object') opener.parent.itens.recarregar_tela()
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>">
<input type='hidden' name='passo' value='1'>
<!--Controle de Tela-->
<input type='hidden' name='ignorar'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
<?
    if($linhas == 0) {
?>
    <tr align='center'>
        <td>
            <?=$mensagem[2];?>
        </td>
    </tr>
    <tr align='center'>
        <td>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../opcoes_incluir.php'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?        
    }else {
?>
    <tr align='center'>
        <td colspan='6'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            Liberar NF Outras (Autom·tico)
            <font color='yellow'>
                <?=genericas::nome_empresa($id_emp);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; NF
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Status da NF
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Emp / Tp Nota <br>/ Prazo Pgto
            </font>
        </td>
    </tr>
<?
//Vetor para Auxiliar as IdentificaÁıes de Follow-UP, que busca de outro arquivo
    $vetor = array_sistema::nota_fiscal();
    for($i = 0;  $i < $linhas; $i++) {
        $url = "javascript:document.form.ignorar.value = 1;window.location = 'incluir_nfs_outras.php?passo=1&id_nf_outra=".$campos[$i]['id_nf_outra']."&id_emp=".$campos[$i]['id_empresa']."'";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <img src = '../../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td>
            <a href="<?=$url;?>" title='Liberar Outras NF de SaÌda' class='link'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf_outra'], 'O');?>
            </a>
        </td>
        <td>
        <?
            if($campos[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[$i]['data_emissao'], '/');
        ?>
        </td>
        <td align='left'>
            <font title='Nome Fantasia: <?=$campos[$i]['nomefantasia'];?>' style='cursor:help'>
                <?=$campos[$i]['razaosocial'];?>
            </font>
        </td>
        <td align='left'>
            <?=$vetor[$campos[$i]['status']];?>
        </td>
        <td align='left'>
        <?
            //Busca da Raz„o Social da NF Outra ...
            $sql = "SELECT nomefantasia 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $apresentar     = $campos_empresa[0]['nomefantasia'];
            $apresentar.= ($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) ? ' (NF)' : ' (SGD)';
//Vencimentos da NF ...
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? '¿ vista' : $campos[$i]['vencimento1'];
            }
            echo $apresentar.' / '.$prazo_faturamento;
//Aki eu limpo essa vari·vel para n„o dar problema quando voltar no prÛximo loop
            $prazo_faturamento = '';
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = '../opcoes_incluir.php'" class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
<?
/*Aki È simplesmente o contador, n„o tem paginaÁ„o para n„o dar conflito com a da Tela de Itens que est· 
no Frame Debaixo*/
?>
    <tr>
        <td colspan='9'>
            &nbsp;
        </td>
    </tr>
    <tr class='confirmacao' align='center'>
        <td colspan='9'>
            Total de Registro(s): <?=$linhas;?>
        </td>
    </tr>
<?
    }
?>
</table>
</form>
</body>
<pre>
O sistema n„o exibe:

<b>- Valores zerados
- Notas que estejam em Aberto / Liberada / Canceladas</b>
</pre>
</html>
<?}?>