<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/financeiros.php');
require('../../../../lib/genericas.php');
require('../../../../lib/data.php');
require('../../../classes/array_sistema/array_sistema.php');

switch($opcao) {
    case 1://Significa que veio do Menu Abertas / Liberadas ...
    case 2://Significa que veio do Menu de Liberadas / Faturadas ...
    case 3://Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
    case 4://Significa que veio do Menu de Devolução 
        segurancas::geral('/erp/albafer/modulo/faturamento/nota_saida/itens/devolucao.php', '../../../../');
    break;
    default://Significa que veio do Menu de Devolução ...
        segurancas::geral('/erp/albafer/modulo/faturamento/nfs_consultar/consultar.php', '../../../../');
    break;
}

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_cliente                        = $_POST['txt_cliente'];
        $txt_numero_nota_fiscal             = $_POST['txt_numero_nota_fiscal'];
        $txt_transportadora                 = $_POST['txt_transportadora'];
        $chkt_todos_despachados_portaria    = $_POST['chkt_todos_despachados_portaria'];
        $cmb_status                         = $_POST['cmb_status'];
        $chkt_ultimos_60_dias               = $_POST['chkt_ultimos_60_dias'];
        $opcao                              = $_POST['opcao'];
    }else {
        $txt_cliente                        = $_GET['txt_cliente'];
        $txt_numero_nota_fiscal             = $_GET['txt_numero_nota_fiscal'];
        $txt_transportadora                 = $_GET['txt_transportadora'];
        $chkt_todos_despachados_portaria    = $_GET['chkt_todos_despachados_portaria'];
        $cmb_status                         = $_GET['cmb_status'];
        $chkt_ultimos_60_dias               = $_GET['chkt_ultimos_60_dias'];
        $opcao                              = $_GET['opcao'];
    }
//Se essa opção tiver selecionada, então eu só exibo as notas fiscais que estão Despachadas e que já estão na portaria ...
    if(!empty($chkt_todos_despachados_portaria)) {
        $condicao = " nfs.`status` = '4' AND nfs.`tipo_despacho` = '1' ";
    }else {
        if($cmb_status == '') {
            if($opcao == 1) {//Significa que veio do Menu Abertas / Liberadas ...
                $condicao = " nfs.`status` IN (0, 1) ";
            }else if($opcao == 2) {//Significa que veio do Menu de Liberadas / Faturadas ...
                $condicao = " nfs.`status` IN (1, 2, 5) ";
            }else if($opcao == 3) {//Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
                $condicao = " nfs.`status` IN (2, 3, 4) ";
            }
        }else {
            $condicao = " nfs.`status` = '$cmb_status' ";
        }
    }
    //Apenas dos Últimos 60 dias ...
    if(!empty($chkt_ultimos_60_dias)) {
        $data_ultimos_60_dias           = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -60), '-');
        $condicao_ultimos_60_dias_nfs   = " AND nfs.`data_emissao` >= '".$data_ultimos_60_dias."' ";
    }
    
    if(!empty($txt_numero_nota_fiscal)) {
        $inner_join_nfs_num_notas   = "INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` AND nnn.`numero_nf` LIKE '$txt_numero_nota_fiscal%' ";
        $order_by_nfs_num_notas     = ", nnn.`numero_nf` DESC ";
    }
    
    if(!empty($txt_transportadora)) {
        $inner_join_transportadoras = "INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` AND t.`nome` LIKE '%$txt_transportadora%' ";
    }
    
    $sql = "SELECT nfs.`id_nf`, nfs.`id_empresa`, nfs.`id_transportadora`, nfs.`id_nf_vide_nota`, 
            nfs.`data_emissao`, nfs.`vencimento1`, nfs.`vencimento2`, nfs.`vencimento3`, nfs.`vencimento4`, 
            nfs.`peso_bruto_balanca`, nfs.`status`, nfs.`tipo_despacho`, c.`id_uf`, 
            c.`razaosocial`, c.`credito`, c.`cidade` 
            FROM `nfs` 
            $inner_join_nfs_num_notas 
            $inner_join_transportadoras 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') AND c.`ativo` = '1' 
            WHERE $condicao $condicao_ultimos_60_dias_nfs 
            ORDER BY nfs.`data_emissao` DESC $order_by_nfs_num_notas ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar_imprimir.php?opcao=<?=$opcao?>&valor=1'
    </Script>
<?
    }else {
/***************************Script p/ Excluir as Notas Fiscais***************************/
        //Aqui eu excluo as Notas Fiscais q não tiverem itens e q Empresa desta for Albafer ou Tool Master ...
        if(!empty($_GET['id_nf']) && empty($_GET['sair'])) {
            //Deleto esse id_nf das tabelas relacionais que usam esse "id_" primeiro ...
            $sql = "DELETE FROM `nfs_vs_pi_embalagens` WHERE `id_nf` = '$_GET[id_nf]' ";
            bancos::sql($sql);
            
            /*Caso foi escolhido um N.º de Nota Fiscal de Talonário nessa NF que está sendo excluída, 
            então eu reabro o mesmo garantindo que este seja disponibilizado para um novo uso futuramente ...*/
            $sql = "UPDATE `nfs_num_notas` nnn 
                    INNER JOIN `nfs` ON nfs.`id_nf_num_nota` = nnn.`id_nf_num_nota` 
                    SET nnn.`nota_usado` = '0' WHERE nfs.`id_nf` = '$_GET[id_nf]' ";
            bancos::sql($sql);
            
            //Deleto a própria Nota Fiscal que foi solicitada p/ ser deletada ...
            $sql = "DELETE FROM `nfs` WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
            bancos::sql($sql);
?>
            <Script Language = 'JavaScript'>
                alert('NOTA FISCAL EXCLUIDA COM SUCESSO !')
                window.location = 'alterar_imprimir.php<?=$parametro?>&sair=1'
            </Script>
<?
        }
/****************************************************************************************/
?>
<html>
<head>
<title>.:: Alterar / Imprimir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_nf, credito) {
    if(credito == 'C' || credito == 'D') alert('CLIENTE COM CRÉDITO '+credito+' !\n POR FAVOR CONTATAR O DEPTO. FINANCEIRO !')
    //Significa que veio do menu faturadas / empacotadas / despachadas
    window.location = 'index.php?id_nf='+id_nf+'&opcao=<?=$opcao;?>'
}

function excluir_nota_fiscal(id_nf) {
    var resposta = confirm('VOCÊ TEM CERTEZA DE QUE DESEJA EXCLUIR ESSA NOTA FISCAL ?')
    if(resposta == true) {
        //Essa variável é uma jogadinha que eu faço p/ não ficar dando reload umas 500 vezes na Tela ...
        document.location = 'alterar_imprimir.php<?=$parametro;?>&id_nf='+id_nf+'&sair=0'
    }else {
        return false
    }
}
</Script>
</head>
<body>
<form name='form' action='<?=$PHP_SELF.'?passo=1';?>' method='post'>
<!--*********************Controles de Tela*********************-->
<!--Estes controles foram criados p/ não dar erro aqui nessa Tela quando o Usuário abrir o Cabeçalho 
de Nota Fiscal através do Link que fica na Coluna Status da NF que fica aqui nessa tela mesmo ...-->
<input type='hidden' name='cmb_status' value='<?=$cmb_status;?>'>
<input type='hidden' name='parametro' value='<?=$parametro;?>'>
<input type='hidden' name='opcao' value='<?=$opcao;?>'>
<!--***********************************************************-->
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Consultar Notas Fiscais de Saida
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.&ordm; Nota Fiscal
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Cidade
        </td>
        <td>
            UF
        </td>
        <td>
            Transportadora
        </td>
        <td>
            Qtde Vol/ Peso Bruto Vol
        </td>
        <td>
            Status da NF
        </td>
        <td>
            Emp / Tp Nota <br>/ Prazo Pgto
        </td>
        <td>
            <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir Nota Fiscal' alt='Excluir Nota Fiscal'>
        </td>
    </tr>
<?
//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
        $vetor                  = array_sistema::nota_fiscal();
        $vetor_tipos_despacho   = faturamentos::tipos_despacho();
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_nf'];?>', '<?=$campos[$i]['credito'];?>')" width='10'>
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_nf'];?>', '<?=$campos[$i]['credito']?>')">
            <a href="javascript:prosseguir('<?=$campos[$i]['id_nf'];?>', '<?=$campos[$i]['credito'];?>')" class='link'>
                <?=faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S');?>
            </a>
        </td>
        <td>
        <?
            if($campos[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[$i]['data_emissao'], '/');
        ?>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['razaosocial'];
            //Aqui verifica se esta NF do Loop contém pelo menos 1 item ...
            $sql = "SELECT `id_nfs_item` 
                    FROM `nfs_itens` 
                    WHERE `id_nf` = '".$campos[$i]['id_nf']."' LIMIT 1 ";
            $campos_item 	= bancos::sql($sql);
            $qtde_itens_nf 	= count($campos_item);
            if($qtde_itens_nf == 0) echo ' <font color="red">(S/ ITENS)</font>';
        ?>
        </td>
        <td>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
        <?
//Se existir UF para o Cliente ...
            if($campos[$i]['id_uf'] > 0) {
                $sql = "SELECT `sigla` 
                        FROM `ufs` 
                        WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
                $campos_ufs = bancos::sql($sql);
                echo $campos_ufs[0]['sigla'];
            }
        ?>
        </td>
        <td>
        <?
            //Busco o nome da Transportadora ...
            $sql = "SELECT `nome` 
                    FROM `transportadoras` 
                    WHERE `id_transportadora` = '".$campos[$i]['id_transportadora']."' LIMIT 1 ";
            $campos_transportadora = bancos::sql($sql);
            echo $campos_transportadora[0]['nome'];
        ?>
        </td>
        <td>
            <img src = '../../../../imagem/propriedades.png' title='Detalhes Qtde / Peso Bruto de Volume' alt='Detalhes Qtde de Volume' onclick="html5Lightbox.showLightbox(7, 'detalhes_qtde_volume.php?id_nf=<?=$campos[$i]['id_nf'];?>')" border='0'>
            / <?=number_format($campos[$i]['peso_bruto_balanca'], 2, ',', '.');?>
        </td>
        <td align='left'>
        <?
/*Se a NF do Loop que estou trabalhando não for Vide Nota, então significa que esta é a NF principal, 
sendo assim eu posso exibir o link, p/ alteração dos Dados*/
            if($campos[$i]['id_nf_vide_nota'] == 0) {
        ?>
            <a href="javascript:nova_janela('../alterar_cabecalho.php?id_nf=<?=$campos[$i]['id_nf'];?>&opcao=<?=$opcao;?>', 'POP', '', '', '', '', 720, 850, 'c', 'c', '', '', 's', 's', '', '', '')" class='link'>
        <?
                echo $vetor[$campos[$i]['status']];
                if($campos[$i]['status'] == 4) echo ' ('.$vetor_tipos_despacho[$campos[$i]['tipo_despacho']].')';
            }else {
                echo '<b>Vide Nota => </b>'.faturamentos::buscar_numero_nf($campos[$i]['id_nf_vide_nota'], 'S');
            }
        ?>
            </a>
        </td>
        <td align='left'>
        <?
//Busca da Empresa da NF ...			
            $sql = "SELECT nomefantasia 
                    FROM `empresas` 
                    WHERE `id_empresa` = '".$campos[$i]['id_empresa']."' LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);

            $apresentar = $campos_empresa[0]['nomefantasia'];
            $apresentar.= ($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2) ? ' (NF)' : ' (SGD)';
//Vencimentos da NF ...
            if($campos[$i]['vencimento4'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento4'];
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento= '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento= $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
            }else {
                $prazo_faturamento = ($campos[$i]['vencimento1'] == 0) ? 'À vista': $campos[$i]['vencimento1'];
            }
            echo $apresentar.' / '.$prazo_faturamento;
//Aki eu limpo essa variável para não dar problema quando voltar no próximo loop
            $prazo_faturamento = '';
        ?>
        </td>
        <td>
        <?
/*Só irá exibir esse link quando a Nota Fiscal não tiver nenhum Item e a Empresa desta 
for Albafer ou Tool Master ...*/
            if($qtde_itens_nf == 0 && $campos[$i]['status'] == 0 && ($campos[$i]['id_empresa'] == 1 || $campos[$i]['id_empresa'] == 2)) {
        ?>
                <img src = '../../../../imagem/menu/excluir.png' border='0' title='Excluir Nota Fiscal' alt='Excluir Nota Fiscal' onclick="excluir_nota_fiscal('<?=$campos[$i]['id_nf'];?>')">
        <?
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar_imprimir.php?opcao=<?=$opcao;?>'" class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Alterar / Imprimir ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
</head>
<body onload='document.form.txt_cliente.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1'?>'>
<!--*******************Controles de Tela*******************-->
<input type='hidden' name='passo' value='1'>
<!--Esse parâmetro opcao vem do Menu-->
<input type='hidden' name='opcao' value='<?=$opcao;?>'>
<!--*******************************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar / Imprimir
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cliente
        </td>
        <td>
            <input type='text' name='txt_cliente' title='Digite o Cliente' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º da Nota Fiscal
        </td>
        <td>
            <input type='text' name='txt_numero_nota_fiscal' title='Digite o N.º da Nota Fiscal' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Transportadora
        </td>
        <td>
            <input type='text' name="txt_transportadora" title="Digite a Transportadora" class='caixadetexto'>
            (FAT / EMPAC / DESP na Portaria)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Status
        </td>
        <td>
            <select name='cmb_status' title='Selecione o Status Nota Fiscal' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <?
                    if($opcao == 1) {//Significa que veio do Menu Abertas / Liberadas ...
                ?>
                <option value='0'>EM ABERTO</option>
                <option value='1'>LIBERADA P/ FATURAR</option>
                <?
                    }else if($opcao == 2) {//Significa que veio do Menu de Liberadas / Faturadas ...
                ?>
                <option value='1'>LIBERADA P/ FATURAR</option>
                <option value='2'>FATURADA</option>
                <option value='5'>CANCELADA</option>
                <?
                    }else if($opcao == 3) {//Significa que veio do Menu de Faturadas / Empacotadas / Despachadas ...
                ?>
                <option value='2'>FATURADA</option>
                <option value='3'>EMPACOTADA</option>
                <option value='4'>DESPACHADA</option>
                <?
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_todos_despachados_portaria' value='1' title="Todos Despachados na Portaria" class='checkbox' id='label'>
            <label for='label'>Todos Despachados na Portaria</label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ultimos_60_dias' value='1' title='Últimos 60 dias' id='chkt_ultimos_60_dias' class='checkbox' checked>
            <label for='chkt_ultimos_60_dias'>
                <font color='red'>
                    <b>Últimos 60 dias</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_cliente.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>