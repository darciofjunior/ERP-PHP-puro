<?
require('../../../lib/segurancas.php');
if(empty($pop_up))  require '../../../lib/menu/menu.php';//Significa que essa Tela foi aberta como sendo Pop-UP ...
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/intermodular.php');
require('../../classes/array_sistema/array_sistema.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

/************************Controle com o Novo Canhoto***********************/
//Significa que o Usuário optou por mudar o Novo Canhoto do id_nf q foi passado por parâmetro ...
if(isset($_GET['novo_canhoto_arquivado'])) {
    $sql = "UPDATE `nfs` SET `canhoto_arquivado` = '$_GET[novo_canhoto_arquivado]' WHERE `id_nf` = '$_GET[id_nf]' LIMIT 1 ";
    bancos::sql($sql);
}
/**************************************************************************/

if($passo == 1) {
//Tratamento com as variáveis que vem por parâmetro ...
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_numero_nf              = $_POST['txt_numero_nf'];
        $txt_cliente                = $_POST['txt_cliente'];
        $txt_transportadora         = $_POST['txt_transportadora'];
        $txt_numero_remessa         = $_POST['txt_numero_remessa'];
        $cmb_empresa                = $_POST['cmb_empresa'];
        $cmb_status_nf              = $_POST['cmb_status_nf'];
        $cmb_finalidade             = $_POST['cmb_finalidade'];
        $cmb_uf                     = $_POST['cmb_uf'];
        $cmb_confirmacao_documental = $_POST['cmb_confirmacao_documental'];
        $txt_referencia             = $_POST['txt_referencia'];
        $txt_discriminacao          = $_POST['txt_discriminacao'];
        $txt_data_emissao_inicial   = $_POST['txt_data_emissao_inicial'];
        $txt_data_emissao_final     = $_POST['txt_data_emissao_final'];
        $chkt_com_gnre              = $_POST['chkt_com_gnre'];
        $chkt_ultimos_30_dias       = $_POST['chkt_ultimos_30_dias'];
    }else {
        $txt_numero_nf              = $_GET['txt_numero_nf'];
        $txt_cliente                = $_GET['txt_cliente'];
        $txt_transportadora         = $_GET['txt_transportadora'];
        $txt_numero_remessa         = $_GET['txt_numero_remessa'];
        $cmb_empresa                = $_GET['cmb_empresa'];
        $cmb_status_nf              = $_GET['cmb_status_nf'];
        $cmb_finalidade             = $_GET['cmb_finalidade'];
        $cmb_uf                     = $_GET['cmb_uf'];
        $cmb_confirmacao_documental = $_GET['cmb_confirmacao_documental'];
        $txt_referencia             = $_GET['txt_referencia'];
        $txt_discriminacao          = $_GET['txt_discriminacao'];
        $txt_data_emissao_inicial   = $_GET['txt_data_emissao_inicial'];
        $txt_data_emissao_final     = $_GET['txt_data_emissao_final'];
        $chkt_com_gnre              = $_GET['chkt_com_gnre'];
        $chkt_ultimos_30_dias       = $_GET['chkt_ultimos_30_dias'];
    }
    //Esse tratamento "!is_numeric($cmb_status_nf)" é p/ evitar de cair aqui quando o usuario selecionar a opção "Em Aberto" que é = a Zero ...
    if(empty($cmb_status_nf) && !is_numeric($cmb_status_nf)) {
        $status_nf = " LIKE '%' "; 
        $status_nf_outras = " LIKE '%' ";
    }else if($cmb_status_nf == 5) {
        $status_nf = " = '$cmb_status_nf' ";
        $status_nf_outras =	" = '$cmb_status_nf' ";
    }else {
        $status_nf = " IN ($cmb_status_nf) AND nfs.snf_devolvida = '' ";
        $status_nf_outras =	" IN ($cmb_status_nf) ";
    }
    if(empty($cmb_empresa))         $cmb_empresa = '%';
    if(empty($cmb_finalidade))      $cmb_finalidade = '%';
    if(!empty($cmb_uf))             $condicao_uf = " AND c.`id_uf` LIKE '$cmb_uf' ";
/**************************************Confirmação Documental**************************************/
//Só existe apenas p/ as NFs de Saída  ...
    if($cmb_confirmacao_documental == '') {
        $cmb_confirmacao_documental = '%';
    }else {
        if($cmb_confirmacao_documental == 1) {
            $condicao_documental        = " AND nfs.`trading` <> '0' ";
            $condicao_documental_outras = " AND nfso.`id_nf_outra` = '0' ";//Nunca existirá Suframa/Trading p/ NF Outras ...
        }else if($cmb_confirmacao_documental == 2) {
            $condicao_documental        = " AND nfs.`suframa` > '0' ";
            $condicao_documental_outras = " AND nfso.`id_nf_outra` = '0' ";//Nunca existirá Suframa/Trading p/ NF Outras ...
        }
    }
    //Com GNRE ...
    if(!empty($chkt_com_gnre)) {
        $condicao_gnre              = " AND nfs.`gnre` <> '' ";
        $condicao_gnre_nfs_outras   = " AND nfso.`id_cliente` = '0' ";//Aqui é Macete Puro, pq não existe NF sem Cliente ...
    }
    //Apenas dos Últimos 30 dias ...
    if(!empty($chkt_ultimos_30_dias)) {
        $data_ultimos_30_dias                   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -30), '-');
        $condicao_ultimos_30_dias_nfs           = " AND nfs.`data_emissao` >= '".$data_ultimos_30_dias."' ";
        $condicao_ultimos_30_dias_nfs_outras    = " AND nfso.`data_emissao` >= '".$data_ultimos_30_dias."' ";
    }
/**************************************NFs Saída/Devolução e NFs Outras*************************************/
//NFs Saída / Devolução que equivale ao Status 6 - nfs ...
//NFs Outras - nfs_outras ...
    if(!empty($txt_data_emissao_inicial)) {
//Aqui verifica se a Data está no formato Americano p/ não ter que fazer o Tratamento Novamente
        if(substr($txt_data_emissao_final, 4, 1) != '-') {
            $txt_data_emissao_inicial = data::datatodate($txt_data_emissao_inicial, '-');
            $txt_data_emissao_final = data::datatodate($txt_data_emissao_final, '-');
        }
//Aqui é para não dar erro de SQL
        $condicao_datas_nfs         = " AND nfs.`data_emissao` BETWEEN '$txt_data_emissao_inicial' AND '$txt_data_emissao_final' ";
        $condicao_datas_nfs_outras  = " AND nfso.`data_emissao` BETWEEN' $txt_data_emissao_inicial' AND '$txt_data_emissao_final' ";
    }

    if(!empty($txt_cliente) || !empty($cmb_uf)) {
        $inner_join_clientes_nfs    = " INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') AND c.`ativo` = '1' $condicao_uf ";
        $inner_join_clientes_nfso   = " INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` AND (c.`nomefantasia` LIKE '%$txt_cliente%' OR c.`razaosocial` LIKE '%$txt_cliente%') AND c.`ativo` = '1' $condicao_uf ";
    }

    if(!empty($txt_transportadora)) {
        $inner_join_transportadoras_nfs     = " INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfs.`id_transportadora` AND t.`nome` LIKE '%$txt_transportadora%' ";
        $inner_join_transportadoras_nfso    = " INNER JOIN `transportadoras` t ON t.`id_transportadora` = nfso.`id_transportadora` AND t.`nome` LIKE '%$txt_transportadora%' ";
    }
/**************************************************************************************************/
//Se o usuário consultar as NFs por número, então eu acrescento essa cláusula a mais no SQL ...
    if(!empty($txt_numero_nf)) {//Lembrando que QQ tipo de NF usa o mesmo Talonário nnn_num_notas ...
/**************************************NFs Saída/Devolução*****************************************/
//Essa Tabela de NFs está totalmente relacionada com as NFs de Saída que vem de Pedidos, Orçamentos ...
        $inner_join_nfs                 = " INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` AND nnn.`numero_nf` LIKE '$txt_numero_nf%' ";
//Aqui eu também busco as NFs que provavelmente são de Devolução com essa numeração ...
        $CAMPOS_NFS_DEVOLUCAO           = " UNION ALL (SELECT nfs.`id_nf`, nfs.`id_cliente`, nfs.`id_empresa`, nfs.`id_transportadora`, nfs.`id_nf_num_nota`, nfs.`finalidade`, nfs.`data_emissao`, nfs.`vencimento1`, nfs.`vencimento2`, nfs.`vencimento3`, nfs.`vencimento4`, nfs.`status`, nfs.`tipo_despacho`, nfs.`numero_remessa`, nfs.`data_saida_entrada` ";
        $CAMPOS_NFS_DEVOLUCAO_PAGINACAO = " UNION ALL (SELECT COUNT(DISTINCT(nfs.`id_nf`)) AS total_registro ";
        $UNION_NFS_DEVOLUCAO = "FROM `nfs` 
                                WHERE nfs.`snf_devolvida` LIKE '%$txt_numero_nf' 
                                AND nfs.`ativo` = '1' 
                                AND nfs.`id_empresa` LIKE '$cmb_empresa' 
                                AND nfs.`status` $status_nf 
                                AND nfs.`finalidade` LIKE '$cmb_finalidade' 
                                $condicao_gnre 
                                $condicao_datas_nfs 
                                $condicao_ultimos_30_dias_nfs 
                                $condicao_documental GROUP BY nfs.`id_nf`) ";
/********************************************NFs Outras********************************************/
//Essa Tabela de NFs Outras só está relacionada com as NFs de Saída ...
        $inner_join_nfso = "INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfso.`id_nf_num_nota` AND nnn.`numero_nf` LIKE '$txt_numero_nf%' ";
    }
/*Se o Filtro utilizou uma dessas opções abaixo, então eu faço um redirecionamento p/ um outro arquivo diferente 
por causa da Estrutura do SQL ...*/
    if(!empty($txt_referencia) || !empty($txt_discriminacao)) {
        require('consultar_itens.php');
        exit;
    }
/**************************************NFs Saída/Devolução e NFs Outras*************************************/
/* "NFs Saída / Devolução" que é status 6 - equivale a tabela 'nfs' do sistema ...
/* NFs Outras - equivale a tabela 'nfs_outras' do sistema ...

Gambiarra: rsrs

Da tabela 'nfs' é necessário trazer o campo "tipo_despacho"; 
só que na tabela 'nfs_outras' não existe esse campo, sendo assim para não furar o Union All tive que trazer 
o campo "observacao" para substituí-lo mesmo não sendo utilizado para nada, p/ evitar o erro de SQL 
"different number of columns" ...*/
    $sql = "(SELECT nfs.`id_nf`, nfs.`id_cliente`, nfs.`id_empresa`, nfs.`id_transportadora`, 
            nfs.`id_nf_num_nota`, nfs.`finalidade`, nfs.`data_emissao`, nfs.`vencimento1`, 
            nfs.`vencimento2`, nfs.`vencimento3`, nfs.`vencimento4`, nfs.`status`, 
            nfs.`tipo_despacho`, nfs.`numero_remessa`, nfs.`data_saida_entrada` 
            FROM `nfs` 
            $inner_join_nfs 
            $inner_join_clientes_nfs 
            $inner_join_transportadoras_nfs 
            WHERE nfs.`ativo` = '1' 
            AND nfs.`id_empresa` LIKE '$cmb_empresa' 
            AND nfs.`status` $status_nf 
            AND nfs.`finalidade` LIKE '$cmb_finalidade' 
            AND nfs.`numero_remessa` LIKE '$txt_numero_remessa%' 
            $condicao_gnre 
            $condicao_datas_nfs 
            $condicao_ultimos_30_dias_nfs 
            $condicao_documental GROUP BY nfs.id_nf) 
            $CAMPOS_NFS_DEVOLUCAO 
            $UNION_NFS_DEVOLUCAO 
            UNION ALL 
            (SELECT /*Esse Pipe é um Macete ...*/ CONCAT('|', nfso.`id_nf_outra`), nfso.`id_cliente`, 
            nfso.`id_empresa`, nfso.`id_transportadora`, nfso.`id_nf_num_nota`, nfso.`finalidade`, 
            nfso.`data_emissao`, nfso.`vencimento1`, nfso.`vencimento2`, nfso.`vencimento3`, 
            nfso.`vencimento4`, nfso.`status`, nfso.`numero_remessa`, nfso.`observacao`, nfso.`data_saida_entrada` 
            FROM `nfs_outras` nfso 
            $inner_join_nfso 
            $inner_join_clientes_nfso  
            $inner_join_transportadoras_nfso 
            WHERE nfso.`ativo` = '1' 
            AND nfso.`id_empresa` LIKE '$cmb_empresa' 
            AND nfso.`status` $status_nf_outras 
            AND nfso.`finalidade` LIKE '$cmb_finalidade' 
            AND nfso.`observacao` LIKE '$txt_numero_remessa%' /*Outra Maracutaia ... rs*/
            $condicao_gnre_nfs_outras 
            $condicao_datas_nfs_outras 
            $condicao_ultimos_30_dias_nfs_outras 
            $condicao_documental_outras GROUP BY nfso.`id_nf_outra`) ORDER BY `data_emissao` DESC, `id_nf_num_nota` DESC ";
    $campos = bancos::sql($sql, $inicio, 25, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'Javascript'>
            window.location = 'consultar.php?pop_up=<?=$_POST['pop_up'];?>&valor=1'
        </Script>
<?
    }else {
?>
<html>
<head>
<title>.:: Consultar Nota(s) Fiscal(is) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function alterar_canhoto_arquivado(id_nf, novo_canhoto_arquivado) {
    window.location = '<?=$PHP_SELF.$parametro;?>&id_nf='+id_nf+'&novo_canhoto_arquivado='+novo_canhoto_arquivado
}
</Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='14'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            Consultar Nota(s) Fiscal(is)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            N.º NF(s)
        </td>
        <td>
            N.º NF de Devolução
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
            Finalidade
        </td>
        <td>
            Transportadora
        </td>
        <td>
            N.º de Remessa
        </td>
        <td>
            Canhoto Arquivado
        </td>
        <td>
            Status da NF
        </td>
        <td>
            <font title='Empresa / Tipo de Nota / Prazo de Pagamento' style='cursor:help'>
                Emp / Tp Nota <br/>/ Prazo Pgto
            </font>
        </td>
        <td>
            Data Saída
        </td>
    </tr>
<?
//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
        $vetor                  = array_sistema::nota_fiscal();
        $vetor_tipos_despacho   = faturamentos::tipos_despacho();
        for($i = 0;  $i < $linhas; $i++) {
//Zero as variáveis p/ não dar problema no próximo loop ...
            $id_nf_outra = 0; $id_nf = 0;
/*Obs: O Union retorna o "id_nf_outra" e o "id_nf" como sendo um único campo, que no caso está sendo "id_nf", 
daí para distinguir de uma tabela com outra, eu joguei um "|" na Frente do campo id_nf_outra ...*/
//Verifico o Tipo de Nota Fiscal que está sendo listada dentro do Loop ...
            if(substr($campos[$i]['id_nf'], 0, 1) == '|') {//Significa que está sendo listada uma NF Outra(s) no Loop ...
                $id_nf_outra    = substr($campos[$i]['id_nf'], 1, strlen($campos[$i]['id_nf']));
                $caminho        = '../outras_nfs/itens/detalhes_nota_fiscal.php?id_nf_outra='.$id_nf_outra;
            }else {//Significa que está sendo acessada uma NF de Venda / Devolução no Loop ...
                $id_nf          = $campos[$i]['id_nf'];
                $caminho        = '../nota_saida/itens/detalhes_nota_fiscal.php?id_nf='.$id_nf;
            }
            //Busca alguns dados do Cliente ...
            $sql = "SELECT `id_uf`, `nomefantasia`, `razaosocial`, `cidade` 
                    FROM `clientes` 
                    WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            $campos_clientes = bancos::sql($sql);
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="window.location = '<?=$caminho;?>'" width='10'>
            <a href="<?=$caminho;?>">
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="window.location = '<?=$caminho;?>'">
            <a href="<?=$caminho;?>" class='link'>
            <?
/**************************************NF Outras*****************************************/
                if($id_nf_outra > 0) {//Significa que está sendo listada uma NF Outra(s) no Loop ...
                    echo '<font title="NF Outras" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($id_nf_outra, 'O').'</b></font>';
                }else {//Significa que está sendo acessada uma NF de Venda / Devolução no Loop ...
/**************************************Devolução*****************************************/
                    if($campos[$i]['status'] == 6) {//Está sendo acessada uma NF de Devolução ...
                        echo '<font color="red" title="NF de Devolução" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($id_nf, 'D').'</b></font>';
                    }else {//Está sendo acessada uma NF normal ...
/**************************************NF Saída*****************************************/
                        echo '<font title="NF de Saída" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($id_nf, 'S').'</b></font>';
                    }
                }
/****************************************************************************************/
            ?>
            </a>
        </td>
        <td>
        <?
            //Somente pelo Caminho de NF de Saída que verifico se existe NF de Devolução ...
            if($id_nf > 0 && $campos[$i]['status'] <= 4) {
                //Verifico se está NF de Saída possui algum item que foi Devolvido ...
                $sql = "SELECT id_nfs_item 
                        FROM `nfs_itens` 
                        WHERE `id_nf` = '$id_nf' ";
                $campos_nfs = bancos::sql($sql);
                $linhas_nfs = count($campos_nfs);
                if($linhas_nfs > 0) {//Encontrou pelo menos 1 Item ...
                    for($j = 0; $j < $linhas_nfs; $j++) $vetor_nfs_item[] = $campos_nfs[$j]['id_nfs_item'];
                    
                    $sql = "SELECT `id_nf` 
                            FROM `nfs_itens` 
                            WHERE `id_nf_item_devolvida` IN (".implode(',', $vetor_nfs_item).") LIMIT 1 ";
                    $campos_nfs_devolvida = bancos::sql($sql);
                    if(count($campos_nfs_devolvida) == 1) echo '<font color="red"><b>'.faturamentos::buscar_numero_nf($campos_nfs_devolvida[0]['id_nf'], 'D').'</b></font>';
                }
            }
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[$i]['data_emissao'], '/');
        ?>
        </td>
        <td align='left'>
            <font title='Nome Fantasia: <?=$campos_clientes[0]['nomefantasia'];?>' style='cursor:help'>
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
                echo 'CONSUMO';
            }else if($campos[$i]['finalidade'] == 'I') {
                echo 'INDUSTRIALIZAÇÃO';
            }else {
                echo 'REVENDA';
            }
        ?>
        </td>
        <td>
        <?
            //Busca o nome da Transportadora ...
            $sql = "SELECT `nome` 
                    FROM `transportadoras` 
                    WHERE `id_transportadora` = '".$campos[$i]['id_transportadora']."' LIMIT 1 ";
            $campos_transportadora = bancos::sql($sql);
            echo $campos_transportadora[0]['nome'];
        ?>
        </td>
        <td>
        <?
            if(!empty($id_nf)) echo faturamentos::numero_remessa($id_nf);
        ?>
        </td>
        <td>
        <?
/**************************************NF Outras*****************************************/
            if($id_nf_outra > 0) {//Significa que está sendo listada uma NF Outra(s) no Loop ...
                echo '-';
/********************************NF Saída ou Devolução***********************************/
            }else {//Significa que está sendo acessada uma NF de Venda / Devolução no Loop ...
                $sql = "SELECT `canhoto_arquivado` 
                        FROM `nfs` 
                        WHERE `id_nf` = '$id_nf' LIMIT 1 ";
                $campos_nfs             = bancos::sql($sql);
                $canhoto_arquivado      = ($campos_nfs[0]['canhoto_arquivado'] == 'S') ? 'SIM' : 'NÃO';
                //Aqui eu preparo a variável abaixo na situação de como é que ficaria a Nota Fiscal caso o usuário clicasse no link ...
                $novo_canhoto_arquivado = ($campos_nfs[0]['canhoto_arquivado'] == 'S') ? 'N' : 'S';
            ?>
            <a href="javascript:alterar_canhoto_arquivado('<?=$id_nf;?>', '<?=$novo_canhoto_arquivado;?>')" class='link'>
                <font color='black'>
                    <b><?=$canhoto_arquivado;?></b>
                </font>
            </a>
        <?
            }
        ?>
        </td>
        <td align='left'>
        <?
            if(!empty($id_nf)) {
                $sql = "SELECT `id_nf_vide_nota` 
                        FROM `nfs` 
                        WHERE `id_nf` = '$id_nf' LIMIT 1 ";
                $campos_vide_nota   = bancos::sql($sql);
                $id_nf_vide_nota    = $campos_vide_nota[0]['id_nf_vide_nota'];
            }else {
                $id_nf_vide_nota    = 0;
            }

/*Se a NF do Loop que estou trabalhando não for Vide Nota, então significa que esta é a NF principal, 
sendo assim eu posso exibir o link, p/ alteração dos Dados*/
            if($id_nf_vide_nota == 0) {
                echo $vetor[$campos[$i]['status']];
                if($campos[$i]['status'] == 4) echo ' ('.$vetor_tipos_despacho[$campos[$i]['tipo_despacho']].')';
            }else {
                echo '<b>Vide Nota => </b>'.faturamentos::buscar_numero_nf($id_nf_vide_nota, 'S');
            }
        ?>
        </td>
        <td align='left'>
        <?
//Busca da Empresa da NF ...
            $sql = "SELECT `nomefantasia` 
                    FROM `empresas` 
                    WHERE `id_empresa` = ".$campos[$i]['id_empresa']." LIMIT 1 ";
            $campos_empresa = bancos::sql($sql);
            $apresentar     = $campos_empresa[0]['nomefantasia'];
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
        <td>
        <?
            if($campos[$i]['data_saida_entrada'] != '0000-00-00') echo data::datetodata($campos[$i]['data_saida_entrada'], '/');
        ?>
        </td>                       
    </tr>
<?
            //Destruo esse Vetor p/ não acumular valores dos Loops anteriores ...
            unset($vetor_nfs_item);
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'consultar.php?pop_up=<?=$pop_up;?>'" class='botao'>
        </td>
    </tr>
</table>
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
<title>.:: Consultar Nota(s) Fiscal(is) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Se a Data de Emissão estiver preenchida, então eu forço o usuário a preencher as 2 Datas ...
    if(document.form.txt_data_emissao_inicial.value != '') {
//Data de Emissão Inicial
        if(!data('form', 'txt_data_emissao_inicial', '4000', 'EMISSÃO INICIAL')) {
            return false
        }
//Data de Emissão Final
        if(!data('form', 'txt_data_emissao_final', '4000', 'EMISSÃO FINAL')) {
            return false
        }
//Comparação com as Datas ...
        var data_emissao_inicial = document.form.txt_data_emissao_inicial.value
        var data_emissao_final = document.form.txt_data_emissao_final.value
        data_emissao_inicial = data_emissao_inicial.substr(6, 4) + data_emissao_inicial.substr(3, 2) + data_emissao_inicial.substr(0, 2)
        data_emissao_final = data_emissao_final.substr(6, 4) + data_emissao_final.substr(3, 2) + data_emissao_final.substr(0, 2)
        data_emissao_inicial = eval(data_emissao_inicial)
        data_emissao_final = eval(data_emissao_final)

        if(data_emissao_final < data_emissao_inicial) {
            alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
            document.form.txt_data_emissao_final.focus()
            document.form.txt_data_emissao_final.select()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_numero_nf.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<!--**********Controle de Tela**********-->
<input type='hidden' name='passo' value='1'>
<input type='hidden' name='pop_up' value='<?=$_GET[pop_up];?>'>
<!--************************************-->
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Nota(s) Fiscal(is)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º da NF
        </td>
        <td>
            <input type='text' name='txt_numero_nf' title='Digite o N.º da NF' class='caixadetexto'>
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
            Transportadora
        </td>
        <td>
            <input type='text' name='txt_transportadora' title='Digite a Transportadora' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            N.º de Remessa
        </td>
        <td>
            <input type='text' name='txt_numero_remessa' title='Digite o N.º de Remessa' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Empresa
        </b>
        <td>
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
            <?
                $sql = "SELECT `id_empresa`, `nomefantasia` 
                        FROM `empresas` 
                        WHERE `ativo` = '1' ORDER BY `nomefantasia` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Status da NF
        </b>
        <td>
            <select name='cmb_status_nf' title='Selecione o Status da NF' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0' style='color:darkblue'>EM ABERTO</option>
                <option value='1' style='color:darkblue'>LIBERADA P/ FATURAR</option>
                <option value='2' style='color:darkblue'>FATURADA</option>
                <option value='3' style='color:darkblue'>EMPACOTADA</option>
                <option value='4' style='color:darkblue'>DESPACHADA</option>
                <option value='5' style='color:red'>CANCELADA</option>
                <option value='6' style='color:red'>DEVOLUÇÃO</option>
                <option value='2 , 3, 4, 6'>FAT / EMP / DESP / DEV</option>
                <option value='2, 3, 4, 5, 6'>FAT / EMP / DESP / CANC / DEV</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Finalidade
        </b>
        <td>
            <select name='cmb_finalidade' title='Selecione a Finalidade' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='C'>CONSUMO</option>
                <option value='I'>INDUSTRIALIZAÇÃO</option>
                <option value='R'>REVENDA</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Confirmação Documental
        </td>
        <td>
            <select name='cmb_confirmacao_documental' title='Selecione a Confirmação Documental' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='1'>TRADING</option>
                <option value='2'>SUFRAMA</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            UF
        </b>
        <td>
            <select name='cmb_uf' title='Selecione a UF' class='combo'>
            <?
                $sql = "SELECT id_uf, sigla 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY sigla ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data de Emissão
        </td>
        <td>
            <input type='text' name='txt_data_emissao_inicial' title='Digite a Data de Emissão Inicial' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> até&nbsp;
            <input type='text' name='txt_data_emissao_final' title='Digite a Data de Emissão Final' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'> 
            <img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>* Referência</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_referencia' title='Digite a Referência' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>* Discriminação</b>
            </font>
        </td>
        <td>
            <input type='text' name='txt_discriminacao' title='Digite a Discriminação' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td colspan='2'>
            <font color='darkblue'>
                * Se algum desses campos estiverem preenchido, a Tela Pós-Filtro será uma outra diferente.
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_com_gnre' value='1' title='Com GNRE' id='chkt_com_gnre' class='checkbox'>
            <label for='chkt_com_gnre'>
                C/ GNRE
            </label>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_ultimos_30_dias' value='1' title='Últimos 30 dias' id='chkt_ultimos_30_dias' class='checkbox' checked>
            <label for='chkt_ultimos_30_dias'>
                <font color='red'>
                    <b>Últimos 30 dias</b>
                </font>
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_numero_nf.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>