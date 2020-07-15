<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/intermodular.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../');

function verificar_vide_notas($id_nf, $id_cliente, $id_empresa_nota, $numero_nf_ac = '') {
    //Aqui vai acumulando todos os Núms. de Nota
    $numero_nf_ac.= faturamentos::buscar_numero_nf($id_nf, 'S').' <- ';

    $sql = "SELECT `id_nf` 
            FROM `nfs` 
            WHERE `id_cliente` = '$id_cliente' 
            AND `id_nf_vide_nota` = '$id_nf' ORDER BY numero_nf ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($j = 0; $j < $linhas; $j++) $numero_nf_ac = verificar_vide_notas($campos[$j]['id_nf'], $id_cliente, $id_empresa_nota, $numero_nf_ac);
    return $numero_nf_ac;
}
?>
<html>
<head>
<title>.:: Controle de Entrega ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(document.form.cmb_motorista.value == '') {
        alert('SELECIONE O MOTORISTA !');
        return false;
    }
    var elementos        = document.form.elements
    var nfs_selecionadas = 0
    
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox' && elementos[i].name == 'chkt_nf[]' && elementos[i].checked) nfs_selecionadas = 1
    }
    /*if(nfs_selecionadas == 0) {
        alert('SELECIONE UMA NF PARA EFETUAR O CONTROLE DE ENTREGA !')
        return false
    }*/
    document.form.action    = 'imprimir_controle_entrega.php'
    document.form.target    = 'CONTROLE'
    nova_janela('imprimir_controle_entrega.php', 'CONTROLE', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}

function excluir_fornecedor(id_fornecedor) {
    document.form.action    = ''
    document.form.target    = ''
    document.form.hdd_fornecedores_atrelados.value = document.form.hdd_fornecedores_atrelados.value.replace(id_fornecedor+',', '');
    document.form.submit()
}

function controle_geral(indice) {
    if(document.getElementById('txt_qtde_volume'+indice).value == '' && document.getElementById('txt_peso_bruto'+indice).value == '') {
        document.getElementById('hdd_indice'+indice).value = ''
    }else {
        document.getElementById('hdd_indice'+indice).value = indice
    }
}
</Script>
</head>
<body>
<form name='form' action='' method='post' >
<!--*****************************************************************-->
<table width='80%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover='total_linhas(this)'>
<?
/*********************************************************************************************************************/
/**************************************A partir daqui é toda a Parte de Clientes**************************************/
/*********************************************************************************************************************/

//Aqui eu pego a vide notas ...
$sql_vide = "SELECT id_nf AS id_nfs_com_vide_nota, id_nf_vide_nota
            FROM `nfs` 
            WHERE ativo = '1'
            AND id_nf_vide_nota > '0' 
            AND (status = '3' OR (status = '4' AND data_saida_entrada = '".date('Y-m-d')."')) 
            ORDER BY status asc, data_emissao DESC ";
$campos_vide = bancos::sql($sql_vide);
$linhas_vide = count($campos_vide);

if($linhas_vide > 0) {
    for($i = 0; $i < $linhas_vide; $i++) $id_nf_vide_notas.= $campos_vide[$i]['id_nfs_com_vide_nota'].',';
    $id_nf_vide_notas = substr($id_nf_vide_notas, 0, strlen($id_nf_vide_notas) - 1);
}
//Esse controle é p/ não dar erro no SQL mais abaixo ...
if(!isset($id_nf_vide_notas)) $id_nf_vide_notas = 0;

//Lista as Notas empacotadas e (despachadas com data atual) em que a transp seja diferente de retira.
$sql = "SELECT `id_nf`, `id_cliente`, `id_empresa`, `id_transportadora`, `finalidade`, `data_emissao`, 
        `tipo_despacho`, `vencimento1`, `vencimento2`, `vencimento3`, `vencimento4`, `status`, `tipo_despacho` 
        FROM `nfs` 
        WHERE `ativo` = '1' 
        AND `id_nf` NOT IN ($id_nf_vide_notas) 
        AND (`status` = '3' OR (`status` = '4' AND `data_saida_entrada` = '".date('Y-m-d')."')) 
        ORDER BY `status`, `data_emissao` DESC "; //AND id_transportadora <> '796' 
$campos = bancos::sql($sql, $inicio, 200, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='10'>
            <?=$mensagem[1]?>
        </td>
    </tr>
<?
}else {
?>
    <!--Controle de Tela-->
    <tr></tr>
    <tr></tr>
    <!--****************-->
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Controle de Entrega - Motorista: 
            <select name='cmb_motorista' title='Selecione um Motorista' class='combo'>
                <option value = '' style='color:red' selected>SELECIONE</option>
                <option value = 'Kalifa'>Kalifa</option>
                <option value = 'Michael'>Michael</option>
            </select>
        </td>
    </tr>
    <tr align='center'>
        <td class='linhadestaque'>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td class='linhadestaque'>
            N.º NF(s)
        </td>
        <td class='linhadestaque'>
            Data Em.
        </td>
        <td class='linhadestaque'>
            Cliente
        </td>
        <td class='linhadestaque'>
            Cidade
        </td>
        <td class='linhadestaque'>
            UF
        </td>
        <td class='linhadestaque'>
            <font title='Consumo / Revenda' style='cursor:help'>
                Cons / Rev
            </font>
        </td>
        <td class='linhadestaque'>
            Transportadora
        </td>
        <td class='linhadestaque'>
            Status
        </td>                        
        <td class='linhadestaque'>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Emp / Tp Nota <br>/ Prazo Pgto
            </font>
        </td>
    </tr>
<?
    $vetor          = array_sistema::nota_fiscal();
    $tipo_despacho  = array('', 'PORTARIA', 'TRANSPORTADORA', 'NOSSO CARRO', 'RETIRA', 'CORREIO/SEDEX', 'TAM');
    for($i = 0; $i < $linhas; $i++) {
        //Busca alguns dados do Cliente ...
        $sql = "SELECT id_uf, nomefantasia, razaosocial, cidade 
                FROM `clientes` 
                WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
        $campos_clientes = bancos::sql($sql);
        //O "($i + 1)" que coloco dentro da função chkt_tudo é pq temos uma Combo de Motorista antes do Checkbox Principal ...
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=($i + 1);?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?
                if(!empty($_POST['chkt_nf'])) {
                    $checked = (in_array($campos[$i]['id_nf'], $_POST['chkt_nf'])) ? 'checked' : '';
                }
            ?>
            <input type='checkbox' name='chkt_nf[]' id='chkt_nf<?=$i;?>' value='<?=$campos[$i]['id_nf'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=($i + 1);?>', '#E8E8E8')" class='checkbox' <?=$checked;?>>
        </td>             
        <td>
            <a href="javascript:nova_janela('../../faturamento/nota_saida/itens/detalhes_nota_fiscal.php?id_nf=<?=$campos[$i]['id_nf'];?>&nao_verificar_sessao=1', 'DETALHES', '', '', '', '', 580, 1010, 'c', 'c', '', '', 's', 's', '', '', '')" title="Visualizar Detalhes" class="link">
            <?
                $vide_notas = verificar_vide_notas($campos[$i]['id_nf'], $campos[$i]['id_cliente'], $campos[$i]['id_empresa']);
                $vide_notas = substr($vide_notas, 0, strlen($vide_notas) - 4);
                echo $vide_notas;
            ?>
            </a>
        </td>
        <td>
        <?
            if($campos[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[$i]['data_emissao'], '/');
        ?>
        </td>
        <td align='left'>
            <font title="Nome Fantasia: <?=$campos_clientes[0]['nomefantasia'];?>" style='cursor:help'>
                <?=$campos_clientes[0]['razaosocial'];?>
            </font>
        </td>
        <td>
            <?=$campos_clientes[0]['cidade'];?>
        </td>
        <td>
        <?
//Se existir UF para o Cliente ...
            if($campos_clientes[0]['id_uf'] > 0) {
                $sql = "SELECT sigla 
                        FROM `ufs` 
                        WHERE `id_uf` = '".$campos_clientes[0]['id_uf']."' LIMIT 1 ";
                $campos_ufs = bancos::sql($sql);
                echo $campos_ufs[0]['sigla'];
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['finalidade'] == 'C') {
                echo 'Consumo';
            }else if($campos[$i]['finalidade'] == 'I') {
                echo 'Industrialização';
            }else {
                echo 'Revenda';
            }
        ?>
        </td>
        <td>
        <?
            //Busca o nome da Transportadora ...
            $sql = "SELECT id_transportadora, nome 
                    FROM `transportadoras` 
                    WHERE `id_transportadora` = '".$campos[$i]['id_transportadora']."' LIMIT 1 ";
            $campos_transportadora = bancos::sql($sql);
            if($campos_transportadora[0]['id_transportadora'] != 795) {
                echo '<font color="gray">'.$campos_transportadora[0]['nome'].'</font>';
            }else {
                echo $campos_transportadora[0]['nome'];
            }
        ?>
        </td>              
        <td align='left'>
        <?
            $sql = "SELECT id_nf_vide_nota 
                    FROM `nfs` 
                    WHERE id_nf = '".$campos[$i]['id_nf']."' LIMIT 1 ";
            $campos_vide_nota 	= bancos::sql($sql);
/*Se a NF do Loop que estou trabalhando não for Vide Nota, então significa que esta é a NF principal, 
sendo assim eu posso exibir o link, p/ alteração dos Dados*/
            if($campos_vide_nota[$i]['id_nf_vide_nota'] == 0) {
        ?>
                <a href="javascript:nova_janela('../nota_saida/alterar_cabecalho_instancia3.php?id_nf=<?=$campos[$i]['id_nf'];?>', 'POP', '', '', '', '', 720, 850, 'c', 'c', '', '', 's', 's', '', '', '')"  class="link">
        <?
                echo $vetor[$campos[$i]['status']];
                if($campos[$i]['status'] == 4) echo ' ('.$tipo_despacho[$campos[$i]['tipo_despacho']].')';
            }else {
                echo '<b>Vide Nota => </b>'.faturamentos::buscar_numero_nf($campos_vide_nota[$i]['id_nf_vide_nota'], 'S');
            }
        ?>
                </a>
        </td>           
        <td align='left'>
        <?
//Busca da Empresa da NF ...
            $sql = "SELECT nomefantasia 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $apresentar = $campos_empresa[0]['nomefantasia'];
            $apresentar.= ($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) ? ' (NF)' : ' (SGD)';
//Vencimentos da NF ...
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento= $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista' : $campos[$i]['vencimento1'];
            }
            echo $apresentar.' / '.$prazo_faturamento;
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name="cmd_controle_entrega" value="Imprimir Controle de Entrega" title="Imprimir Controle de Entrega" onclick="return validar()" class='botao'>
            <input type='button' name="cmd_tracar_rota" value="Traçar Rota" title="Traçar Rota" onclick="nova_janela('https://maps.google.com.br/?ll=-22.553147,-48.636475&spn=6.733826,13.227539&t=h&z=7', 'ROTA', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
<hr>
<?
}
/*********************************************************************************************************************/
/************************************A partir daqui é toda a Parte de Fornecedores************************************/
/*********************************************************************************************************************/
?>
<!--Esse objeto será utilizado apenas quando submeter esse formulário-->
<input type='hidden' name='hdd_fornecedores_atrelados' value='<?=$_POST['hdd_fornecedores_atrelados'];?>'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
<?    
if(!empty($_POST['hdd_fornecedores_atrelados'])) {//Se existe pelo menos 1 Fornecedor atrelado ...
    $id_fornecedores_atrelados      = substr($_POST['hdd_fornecedores_atrelados'], 0, strlen($_POST['hdd_fornecedores_atrelados']) - 1);
    $vetor_fornecedores_atrelados   = explode(',', $id_fornecedores_atrelados);
?>    
        <tr class='linhacabecalho' align='center'>
            <td colspan='9'>
                Fornecedores Atrelados
            </td>
        </tr>
        <tr class='linhadestaque' align='center'>
            <td>Fornecedor</td>
            <td>Endereço</td>
            <td>Bairro</td>
            <td>Cidade</td>
            <td>Cep</td>
            <td>UF</td>
            <td>Qtde Volume</td>
            <td>Peso Bruto</td>
            <td>&nbsp;</td>
        </tr>
<?
    foreach($vetor_fornecedores_atrelados as $i => $id_fornecedor) {
        $sql = "SELECT CONCAT(f.`razaosocial`, '(', f.`nomefantasia`, ')') AS fornecedor, CONCAT(f.`endereco`, ', ', f.`num_complemento`) AS logradouro, f.`bairro`, f.`cidade`, f.`cep`, ufs.`sigla` 
                FROM `fornecedores` f 
                INNER JOIN `ufs` ON ufs.`id_uf` = f.`id_uf` 
                WHERE f.`id_fornecedor` = '$id_fornecedor' ";
        $campos = bancos::sql($sql);
    ?>
         <tr class='linhanormal' align='center'>
            <td>
                <?=$campos[0]['fornecedor'];?>
                <input type='hidden' name='hdd_indice[]' id='hdd_indice<?=$i;?>'>
            </td>
            <td><?=$campos[0]['logradouro'];?></td>
            <td><?=$campos[0]['bairro'];?></td>
            <td><?=$campos[0]['cidade'];?></td>
            <td><?=$campos[0]['cep'];?></td>
            <td><?=$campos[0]['sigla'];?></td>
            <td>
                <input type='text' name="txt_qtde_volume[]" id="txt_qtde_volume<?=$i;?>" maxlength="2" size="5" onkeyup="verifica(this, 'aceita', 'numeros', '', event);controle_geral('<?=$i;?>')" value="<?=$_POST['txt_qtde_volume'][$i];?>" class='caixadetexto'>
            </td>
            <td>
                <input type='text' name="txt_peso_bruto[]" id="txt_peso_bruto<?=$i;?>" maxlength="8" size="10" onkeyup="verifica(this, 'moeda_especial', '2', '', event);controle_geral('<?=$i;?>')" value="<?=$_POST['txt_peso_bruto'][$i];?>" class='caixadetexto'>
            </td>
            <td>
                <img src = "../../../imagem/menu/excluir.png" border='0' title="Excluir Fornecedor" alt="Excluir Fornecedor" onClick="excluir_fornecedor(<?=$id_fornecedor;?>)">
            </td>
        </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='9'>&nbsp;</td>
    </tr>
<?
}else {//Se ainda não existe Fornecedor mostra a msn abaixo ...
?>
    <tr class='atencao' align='center'>
        <td colspan='9'>
            N&Atilde;O EXISTE(M) FORNECEDOR(ES) ATRELADO(S).
        </td>
    </tr>
<?
}
?>
    <tr class='atencao' align='center'>
        <td colspan='9'>
            <input type='button' name='cmd_atrelar_fornecedor' value='Atrelar Fornecedor' onclick="nova_janela('atrelar_fornecedor.php', 'FORNECEDORES', '', '', '', '', 580, 980, 'c', 'c')" class='botao'>
        </td>
    </tr>   
</table>
</form>
</body>
</html>
<?/*********************************************************************************************************************/?>