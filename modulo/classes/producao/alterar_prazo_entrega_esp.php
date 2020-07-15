<?
require('../../../lib/segurancas.php');
require('../../../lib/custos.php');
require('../../../lib/data.php');
require('../../../lib/intermodular.php');//Essa biblioteca é chamada aqui porque a mesma é utilizada dentro do Custos ...
require('../../../lib/vendas.php');
session_start('funcionarios');

if($tela == 1) {//Veio da tela de Todos os P.A.
    segurancas::geral('/erp/albafer/modulo/producao/custo/prod_acabado_componente/pa_componente_todos.php', '../../../');
}else if($tela == 2) {//Veio da tela dos P.A. do Tipo Esp.
    segurancas::geral('/erp/albafer/modulo/producao/custo/prod_acabado_componente/pa_componente_esp.php', '../../../');
}

$mensagem[1] = 'NOVO PRAZO DE ENTREGA (TÉCNICO) ALTERADO COM SUCESSO !';
$mensagem[2] = 'NÃO EXISTE(M) ORÇAMENTO(S) PARA SER(EM) ALTERADO(S) O PRAZO DE ENTREGA (TÉCNICO) !';
$mensagem[3] = "<font class='atencao'>NÃO HÁ ORÇAMENTO(S) DESCONGELADO(S) QUE CONTÉM ESSE PA ATRELADO.</font>";

//Busco a Referência e a Operação de Custo do P.A. ...
$sql = "SELECT referencia, operacao_custo 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
$campos         = bancos::sql($sql);
$referencia     = $campos[0]['referencia'];
$operacao_custo = $campos[0]['operacao_custo'];
/*Se o P.A. é a 'ESP' e a Operação de Custo = 'Revenda' eu preciso buscar qual é o id_fornecedor_setado 
porque vou utilizar mais abaixo p/ gravar no Banco*/
if($referencia == 'ESP' && $operacao_custo == 1) {
    $id_fornecedor_setado = custos::procurar_fornecedor_default_revenda($id_produto_acabado, '', 1);
}

if(!empty($_POST['cmd_salvar'])) {
//Atualizando os Orçamentos que contém esse PA "Somente Descongelados" ...
    $sql = "SELECT ovi.`id_orcamento_venda_item`, ovi.`id_orcamento_venda` 
            FROM `orcamentos_vendas_itens` ovi 
            INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`congelar` = 'N' 
            WHERE ovi.`id_produto_acabado` = '$_POST[id_produto_acabado]' ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {
        for($i = 0; $i < $linhas; $i++) {
            $id_orcamento_venda = $campos[$i]['id_orcamento_venda'];
//Se o P.A. é do Tipo = 'ESP' e a Operação de Custo = 'Revenda' agora eu tenho que atualizar essa tab. relacional ...
            if($referencia == 'ESP' && $operacao_custo == 1) {
//Verifico se já existe algum Prazo pra esse Fornecedor, Orçamento e PA ...
                $sql = "SELECT `id_prazo_revenda_esp` 
                        FROM `prazos_revendas_esps` 
                        WHERE `id_fornecedor` = '$id_fornecedor_setado' 
                        AND `id_orcamento_venda` = '$id_orcamento_venda' 
                        AND `id_produto_acabado` = '$_POST[id_produto_acabado]' LIMIT 1 ";
                $campos_prazo_entrega = bancos::sql($sql);
                if(count($campos_prazo_entrega) == 1) {
                    $sql = "UPDATE `prazos_revendas_esps` SET `prazo` = '$_POST[cmb_prazo_entrega]' WHERE `id_prazo_revenda_esp` = '".$campos_prazo_entrega[0]['id_prazo_revenda_esp']."' LIMIT 1 ";
                }else {
                    $sql = "INSERT INTO `prazos_revendas_esps` (`id_prazo_revenda_esp`, `id_fornecedor`, `id_orcamento_venda`, `id_produto_acabado`, `prazo`) VALUES (NULL, '$id_fornecedor_setado', '$id_orcamento_venda', '$_POST[id_produto_acabado]', '$_POST[cmb_prazo_entrega]') ";
                }
                bancos::sql($sql);
            }
            //Atualizo a tabela de Itens de Orçamento normalmente ...
            $sql = "UPDATE `orcamentos_vendas_itens` SET `prazo_entrega` = '$_POST[cmb_prazo_entrega]', `prazo_entrega_tecnico` = '$_POST[cmb_prazo_entrega]' WHERE `id_orcamento_venda_item` = '".$campos[$i]['id_orcamento_venda_item']."' LIMIT 1 ";
            bancos::sql($sql);
        }
        $valor = 1;
    }else {
        $valor = 2;
    }
?>
    <Script Language = 'JavaScript'>
        alert('<?=$mensagem[$valor];?>')
        parent.document.form.submit()
    </Script>
<?
}
?>
<tr>
<title>.:: Alterar Prazo de Entrega (Técnico) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript'>
function validar() {
    var pergunta = confirm('TEM CERTEZA DE QUE DESEJA ALTERAR O PRAZO DE ENTREGA (TÉCNICO) ?')
    if(pergunta == false) {
        return false
    }else {
        return true
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<input type='hidden' name='id_produto_acabado' value='<?=$id_produto_acabado;?>'>
<table width='80%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Prazo de Entrega Sugerido pelo Depto. Técnico
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='40%'>
            <b>Prazo de Entrega Padrão:</b>
        </td>
        <td width='60%'>
        <?
//Aqui eu faço a Busca do Prazo de Entrega do Grupo
            $sql = "SELECT gpa.`prazo_entrega` 
                    FROM `produtos_acabados` pa 
                    INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                    INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                    WHERE pa.`id_produto_acabado` = '$id_produto_acabado' ";
            $campos_prazo_entrega       = bancos::sql($sql);
            echo $prazo_entrega_tecnico = $campos_prazo_entrega[0]['prazo_entrega'];
        ?>
        </td>
    </tr>
<?
    $vetor_prazos_entrega = vendas::prazos_entrega();
?>
    <tr class='linhanormal'>
        <td>
            <b>Prazo de Entrega Sugerido pelo Depto. Técnico no Último Orc.:</b>
        </td>
        <td>
        <?
/*Busco o último "Prazo de Entrega do Depto. Técnico" que foi preenchido no último Orçamento do 
$id_produto_acabado do Custo que foi passado por parâmetro ...*/
            $sql = "SELECT `id_orcamento_venda`, `prazo_entrega_tecnico` 
                    FROM `orcamentos_vendas_itens` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' 
                    AND `prazo_entrega_tecnico` <> '' 
                    ORDER BY `id_orcamento_venda` DESC LIMIT 1 ";
            $campos_prazo_entrega = bancos::sql($sql);
            if(count($campos_prazo_entrega) == 1) {
                if($campos_prazo_entrega[0]['prazo_entrega_tecnico'] == '') {
                    echo '<font color="red"><b>SEM PRAZO</b></font>';
/*Existe esse esquema de Int, porque o Campo -> 'prazo_entrega_tecnico' é do Tipo Float, foi feito
esse esquema para não dar problema na hora de Atualizar o Custo*/
                }else if((int)$campos_prazo_entrega[0]['prazo_entrega_tecnico'] == 0) {
                    echo '<font color="blue"><b>IMEDIATO</b></font>';
                }else {
                    echo '<font color="blue"><b>'.(int)$campos_prazo_entrega[0]['prazo_entrega_tecnico'].'</b></font>';
                }
                echo ' - <b>Orc. N.º: </b>'.$campos_prazo_entrega[0]['id_orcamento_venda'];
            }else {
                echo '<font color="red"><b>SEM ORÇAMENTO ANTERIOR.</b></font>';
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Prazo de Entrega Sugerido pelo Depto. Técnico:</b>
        </td>
        <td>
            <select name='cmb_prazo_entrega' title='Selecione o Prazo de Entrega' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    foreach($vetor_prazos_entrega as $indice => $prazo_entrega) {
//Compara o valor do Banco com o valor do Vetor
                        if($prazo_entrega_tecnico == $indice) {//Se igual seleciona esse valor
                ?>
                <option value='<?=$indice;?>' selected><?=$prazo_entrega;?></option>
                <?
                        }else {
                ?>
                <option value='<?=$indice;?>'><?=$prazo_entrega;?></option>
                <?
                        }
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick="redefinir('document.form', 'REDEFINIR')" style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
<br/><br/><br/><br/><br/>
<?
/*******************************************************************************/
//Controle para ver se o usuário tem permissão no menu de "Follow-Up do Cliente"
$endereco = '/modulo/producao/custo/follow_up_cliente/follow_up_cliente.php';

$sql = "SELECT `id_menu_item` 
        FROM `menus_itens` 
        WHERE `endereco` LIKE '%$endereco%'";
$campos         = bancos::sql($sql);
$id_menu_item   = $campos[0]['id_menu_item'];

/*Aqui eu verifico se o usuário tem permissão no menu p/ disponibilizar um link p/ ele poder registrar
o follow_Up dessa tela mesmo*/
$sql = "SELECT `id_tipo_acesso` 
        FROM `tipos_acessos` 
        WHERE `id_login` = '$_SESSION[id_login]' 
        AND `id_menu_item` = '$id_menu_item' ";
$campos = bancos::sql($sql);
if(count($campos) == 1) $exibir_link_follow_up = 1;
/*******************************************************************************/

//Listagem de Todos os Orçamento(s) Descongelados que contém esse PA atrelado
$sql = "SELECT c.`id_cliente`, c.`nomefantasia`, c.`razaosocial`, c.`ddi_com`, c.`ddd_com`, c.`telcom`, 
        c.`ddi_fax`, c.`ddd_fax`, c.`telfax`, ov.`id_orcamento_venda`, ov.`id_funcionario`, ov.`data_sys`, 
        ov.`congelar`, DATE_FORMAT(ov.`data_emissao`, '%d/%m/%Y') AS data_emissao, ovi.`qtde`, 
        ovi.`prazo_entrega`, ovi.`prazo_entrega_tecnico` 
        FROM `orcamentos_vendas_itens` ovi 
        INNER JOIN `orcamentos_vendas` ov ON ov.`id_orcamento_venda` = ovi.`id_orcamento_venda` AND ov.`congelar` = 'N' 
        INNER JOIN `clientes` c ON c.`id_cliente` = ov.`id_cliente` 
        WHERE ovi.`id_produto_acabado` = '$id_produto_acabado' ";
$campos = bancos::sql($sql, $inicio, 5, 'sim', $pagina);
$linhas = count($campos);
?>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?
    if($linhas == 0) {//Não existem Orçamentos Descongelados que contém esse PA Atrelado)
?>
    <tr align='center'>
        <td>
            <?=$mensagem[3];?>
        </td>
    </tr>
<?        
    }else {//Existem Orçamentos Descongelados que contém esse PA Atrelado
?>
    <tr align='center'>
        <td></td>
    </tr>
    <tr class='linhacabecalho' align="center"> 
        <td colspan='8'>
            Orçamento(s) Descongelado(s) que contém esse PA atrelado
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td rowspan='2'>
            <font title='N.º do Orçamento' style='cursor:help'>
                N.º Orc.
            </font>
        </td>
        <td rowspan='2'>
            <font title='Data de Emissão' style='cursor:help'>
                Data<br/>Emissão
            </font>
        </td>
        <td rowspan='2'>
            Qtde
        </td>
        <td colspan='2'>
            <font title='Prazo de Entrega Técnico' style='cursor:help'>
                Prazo de Entrega
            </font>
        </td>
        <td rowspan='2'>
            Cliente
        </td>
        <td rowspan='2'>
            Vendedor
        </td>
        <td rowspan='2'>
            <font title='Última Atualização' style='cursor:help'>
                Atualizado
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <font title='Prazo de Entrega' style='cursor:help'>
                T&eacute;cnico
            </font>
        </td>
        <td>
            <font title='Prazo de Entrega Técnico' style='cursor:help'>
                Vendedor
            </font>
        </td>
    </tr>
<?
//Disparo do Loop
	for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td>
            <?=$campos[$i]['id_orcamento_venda'];?>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td>
            <?=number_format($campos[$i]['qtde'], 2, ',', '.');?>
        </td>
        <td>
        <?
/************************Tratamento Novo com Relação ao Prazo de Entrega************************/
/*Se o P.A. é do Tipo = 'ESP' e a O.C. = 'Revenda', então eu ignoro o prazo_entrega_tecnico da Tabela
de Orçamento e leio o "Prazo de Entrega" da tabela relacional de 'prazos_revendas_esps' ...*/
            if($referencia == 'ESP' && $operacao_custo == 1) {
                $sql = "SELECT `prazo` 
                        FROM `prazos_revendas_esps` 
                        WHERE `id_fornecedor` = '$id_fornecedor_setado' 
                        AND `id_orcamento_venda` = '".$campos[$i]['id_orcamento_venda']."' 
                        AND `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
                $campos_prazo_entrega = bancos::sql($sql);
//Se encontrar algum Prazo de Entrega p/ esta condição ...
                if(count($campos_prazo_entrega) == 1) {
                    $prazo_entrega_tecnico = $campos_prazo_entrega[0]['prazo'];
                    if($prazo_entrega_tecnico == 0) {
                        echo 'IMEDIATO';
                    }else {
                        echo $prazo_entrega_tecnico;
                    }
                }else {//Se não encontrar ...
                    echo '<font color="red"><b>SEM PRAZO</b></font>';
                }
            }else {
                if($campos[$i]['prazo_entrega_tecnico'] == '0.0') {
                    echo '<font color="red"><b>SEM PRAZO</b></font>';
/*Existe esse esquema de Int, porque o Campo -> 'prazo_entrega_tecnico' é do Tipo Float, foi feito
esse esquema para não dar problema na hora de Atualizar o Custo*/
                }else if((int)$campos[$i]['prazo_entrega_tecnico'] == 0) {
                    echo 'IMEDIATO';
                }else {
                    echo (int)$campos[$i]['prazo_entrega_tecnico'];
                }
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['prazo_entrega'] == 0) {
                echo 'IMEDIATO';
            }else {
                echo $campos[$i]['prazo_entrega'];
            }
        ?>
        </td>
        <td align='left'>
        <?
/**********************Telefone Fone**********************/
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com'])) {
                $title = "Tel Com: ".$campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            }
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com'])) {
                $title = "Tel Com: ".$campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            }
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com'])) {
                $title = "Tel Com: ".$campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            }
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com'])) {
                $title = "Tel Com: ".$campos[$i]['telcom'];
            }
/**********************Telefone Fax**********************/
            if(!empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax'])) {
                $title.= " - Tel Fax: ".$campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            }
            if(!empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax'])) {
                $title.= " - Tel Fax: ".$campos[$i]['ddi_fax'].' / '.$campos[$i]['ddd_fax'].$campos[$i]['telfax'];
            }
            if(empty($campos[$i]['ddi_fax']) && !empty($campos[$i]['ddd_fax'])) {
                $title.= " - Tel Fax: ".$campos[$i]['ddi_fax'].$campos[$i]['ddd_fax'].' / '.$campos[$i]['telfax'];
            }
            if(empty($campos[$i]['ddi_fax']) && empty($campos[$i]['ddd_fax'])) {
                $title.= " - Tel Fax: ".$campos[$i]['telfax'];
            }
/********************************************************/
//Exibe o link do Follow-UP
            if($exibir_link_follow_up == 1) {
        ?>
                <a href="javascript:nova_janela('../cliente/follow_up.php?identificacao=<?=$campos[$i]['id_cliente'];?>&origem=8', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" title='Registrar Follow-UP' class='link'>
        <?
            }

            if(!empty($campos[$i]['nomefantasia'])) {
                echo $campos[$i]['nomefantasia'];
            }else {
                echo $campos[$i]['razaosocial'];
            }
/********************************************************/
        ?>
        </td>
        <td>
        <?
            //Busca o login do funcionário que fez o Orçamento ...
            $sql = "SELECT l.`login` 
                    FROM `funcionarios` f 
                    INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                    WHERE f.`id_funcionario` = '".$campos[$i]['id_funcionario']."' LIMIT 1 ";
            $campos_login = bancos::sql($sql);
            echo $campos_login[0]['login'];
        ?>
        </td>
        <td>
            <?=data::datetodata(substr($campos[$i]['data_sys'], 0, 10), '/').'<br>'.substr($campos[$i]['data_sys'], 11, 8);?>
        </td>
    </tr>
<?
	}
?>
    <tr class='linhacabecalho'>
        <td colspan='8'> 
            &nbsp;
        </td>
    </tr>
    <tr align='center'> 
        <td colspan='8'> 
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
<?
    }
?>
</table>
</body>
</html>