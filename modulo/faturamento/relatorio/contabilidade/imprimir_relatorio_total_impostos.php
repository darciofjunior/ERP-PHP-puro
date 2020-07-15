<?
require('../../../../lib/segurancas.php');
require('../../../../lib/calculos.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $txt_data_inicial   = $_POST['txt_data_inicial'];
    $txt_data_final     = $_POST['txt_data_final'];
    $cmb_empresa        = $_POST['cmb_empresa'];
    $cmd_consultar      = $_POST['cmd_consultar'];
}else {
    $txt_data_inicial   = $_GET['txt_data_inicial'];
    $txt_data_final     = $_GET['txt_data_final'];
    $cmb_empresa        = $_GET['cmb_empresa'];
    $cmd_consultar      = $_GET['cmd_consultar'];
}
?>
<html>
<head>
<title>.:: Relatório Total de Impostos ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial ...
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final ...
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
//Empresa ...
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
        return false
    }
//Controle com as Datas ...
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6, 4) + data_inicial.substr(3, 2) + data_inicial.substr(0, 2)
    data_final          = data_final.substr(6, 4) + data_final.substr(3, 2) + data_final.substr(0, 2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 1 ano. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 365) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
    alert('CLIQUE EM OK E AGUARDE ...')
}
</Script>
</head>
<body>
<pre>
    <b><font color='red'>Observação:</font></b>
    <pre>
    * Só exibe Notas Fiscais de Saída com Status = Despachadas, Canceladas ou Devolvidas.

    * Só exibe Notas Fiscais de Entrada que estejam Liberadas.
    </pre>
</pre>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Relatório Total de Impostos
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='7'> 
            Data Inicial:
            <?
//Sugestão de Período na Primeira vez em que carregar a Tela ...
                if(empty($txt_data_inicial)) {
                    $txt_data_inicial = '01'.date('/m/Y');
                    $txt_data_final = date('t/m/Y');
                }
            ?>
            <input type='text' name='txt_data_inicial' value='<?=$txt_data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp; Data Final:
            <input type='text' name='txt_data_final' value='<?=$txt_data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp; Empresa:
            <select name='cmb_empresa' title='Selecione uma Empresa' class='combo'>
            <?
                //Só posso listar as Empresas da qual o Faturamento é feito mediante a NF ...
                $sql = "SELECT id_empresa, nomefantasia 
                        FROM `empresas` 
                        WHERE `id_empresa` IN (1, 2) 
                        AND `ativo` = '1' ORDER BY nomefantasia ";
                echo combos::combo($sql, $cmb_empresa);
            ?>
            </select>
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'> 
        </td>
    </tr>
<?
//Se foram digitadas as Datas acima, então realizo o SQL abaixo ...
if(!empty($cmd_consultar)) {
//Campos de Data ...
    $data_inicial = data::datatodate($txt_data_inicial, '-');
    $data_final = data::datatodate($txt_data_final, '-');
/******************************************************************************/
/*********************************NFS de Saída*********************************/
/******************************************************************************/
//Busca das NFs que estejam no Período digitado pelo Usuário e que não estejam Canceladas ...
    $sql = "SELECT c.`id_pais`, nfs.`id_nf`, nfs.`id_empresa`, nfs.`id_nf_num_nota`, nfs.`tipo_nfe_nfs`, DATE_FORMAT(nfs.`data_emissao`, '%d/%m/%Y') AS data_emissao 
            FROM `nfs` 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
            WHERE nfs.`id_empresa` = '$cmb_empresa' 
            AND nfs.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
            AND nfs.`status` >= '4' 
            UNION ALL 
            (SELECT c.`id_pais`, /*Esse Pipe é um Macete ...*/ CONCAT('|', nfso.`id_nf_outra`) AS id_nf, nfso.`id_empresa`, nfso.`id_nf_num_nota`, nfso.`tipo_nfe_nfs`, DATE_FORMAT(nfso.`data_emissao`, '%d/%m/%Y') AS data_emissao 
            FROM `nfs_outras` nfso 
            INNER JOIN `clientes` c ON c.`id_cliente` = nfso.`id_cliente` 
            WHERE nfso.`id_empresa` = '$cmb_empresa' 
            AND nfso.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
            AND nfso.`status` >= '4') 
            ORDER BY `id_empresa`, `data_emissao` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
        $id_empresa_anterior = '';
        for($i = 0; $i < $linhas; $i++) {
            /*Obs: O Union retorna o "id_nf_outra" e o "id_nf" como sendo um único campo, que no caso está sendo "id_nf", 
daí para distinguir de uma tabela com outra, eu joguei um "|" na Frente do campo id_nf_outra ...*/
//Verifico o Tipo de Nota Fiscal que está sendo listada dentro do Loop ...
            if(substr($campos[$i]['id_nf'], 0, 1) == '|') {//Significa que está sendo listada uma NF Outra(s) no Loop ...
                $id_nf_outra            = substr($campos[$i]['id_nf'], 1, strlen($campos[$i]['id_nf']));
                $caminho                = '../../outras_nfs/itens/detalhes_nota_fiscal.php?id_nf_outra='.$id_nf_outra.'&pop_up=1';
                $calculo_total_impostos = calculos::calculo_impostos(0, $id_nf_outra, 'NFO');
                $marcador               = '* ';
            }else {//Significa que está sendo acessada uma NF de Venda / Devolução no Loop ...
                $id_nf                  = $campos[$i]['id_nf'];
                $caminho                = '../../nota_saida/itens/detalhes_nota_fiscal.php?id_nf='.$id_nf.'&pop_up=1';
                $calculo_total_impostos = calculos::calculo_impostos(0, $id_nf, 'NF');
                $marcador               = '';
            }
/*Aqui eu verifico se a Empresa Anterior é Diferente da Empresa Atual que está sendo listada 
no loop, se for então eu atribuo o Empresa Atual p/ a Empresa Anterior ...*/
            if($id_empresa_anterior != $campos[$i]['id_empresa']) {
                $id_empresa_anterior = $campos[$i]['id_empresa'];
//Só não mostro essa linha quando acaba de Entrar no Loop ...
                if($i > 0) {
?>
    <tr class='linhadestaque' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total(is) por Empresa => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_nfs_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_nfs_st_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_ipi_nfs_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_produtos_nfs_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_nota_nfs_rs_empresa, 2, ',', '.');?>
        </td>
    </tr>
<?
                    //Zero essas variáveis p/ não acumular valores da Empresa Anterior ...
                    $valor_icms_nfs_rs_empresa              = 0;
                    $valor_icms_st_nfs_rs_empresa           = 0;
                    $valor_ipi_rs_nfs_empresa               = 0;
                    $valor_total_produtos_nfs_rs_empresa    = 0;
                    $valor_total_nota_nfs_rs_empresa        = 0;
                }
                
                if($i == 0) {//Antes da Exibição da Primeira Empresa ...
?>
    <tr class='iframe' align='center'>
        <td colspan='7'>
            NF(s) de Saída
        </td>
    </tr>
<?
                }
?>
    <tr class='linhacabecalho'>
        <td colspan='7'>
            <font color='yellow'>
                <b>Empresa: </b>
            </font>
            <?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º da NF
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Valor do ICMS
        </td>
        <td>
            Valor do ICMS ST
        </td>
        <td>
            Valor do IPI
        </td>
        <td>
            Valor Total dos Produtos
        </td>
        <td>
            Valor Total da Nota
        </td>
    </tr>
<?
            }
            
            if($campos[$i]['tipo_nfe_nfs'] == 'E') {//Se for NF de Entrada, então mostro na Cor vermelha ...
                $font_open              = "<font color='red'>";
                $font_close             = "</font>";
                $fator_entrada_saida    = -1;//Tem que abater o Valor dos Impostos ...
            }else {
                $font_open              = '';
                $font_close             = '';
                $fator_entrada_saida    = 1;//Tem que acrescentar o Valor dos Impostos ...
            }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href="<?=$caminho;?>" style='cursor:help' class='html5lightbox'>
            <?
/**************************************NF Outras*****************************************/
                if(substr($campos[$i]['id_nf'], 0, 1) == '|') {//Significa que está sendo listada uma NF Outra(s) no Loop ...
                    echo $marcador.'<font title="NF Outras" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($id_nf_outra, 'O').'</b></font>';
                }else {//Significa que está sendo acessada uma NF de Venda / Devolução no Loop ...
/**************************************Devolução*****************************************/
                    if($campos[$i]['status'] == 6) {//Está sendo acessada uma NF de Entrada ...
                        if(!empty($campos[$i]['snf_devolvida'])) {
                            echo $marcador.'<font color="red" title="NF de Devolução" style="cursor:help"><b>'.$campos[$i]['snf_devolvida'].'</font>';
                        }else {
                            echo $marcador.'<font color="red" title="NF de Devolução" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'D').'</font>';
                        }
                    }else {//Está sendo acessada uma NF normal ...
                        echo $marcador.'<font title="NF de Saída" style="cursor:help"><b>'.faturamentos::buscar_numero_nf($campos[$i]['id_nf'], 'S').'</font>';
                    }
                }
/****************************************************************************************/
            ?>
            </a>
        </td>
        <td>
            <?=$font_open.$campos[$i]['data_emissao'].$font_close;?>
        </td>
        <td align='right'>
            <?=$font_open.number_format($calculo_total_impostos['valor_icms'] * $fator_entrada_saida, 2, ',', '.').$font_close;?>
        </td>
        <td align='right'>
            <?=$font_open.number_format($calculo_total_impostos['valor_icms_st'] * $fator_entrada_saida, 2, ',', '.').$font_close;?>
        </td>
        <td align='right'>
            <?=$font_open.number_format($calculo_total_impostos['valor_ipi'] * $fator_entrada_saida, 2, ',', '.').$font_close;?>
        </td>
        <td align='right'>
            <?=$font_open.number_format($calculo_total_impostos['valor_total_produtos'] * $fator_entrada_saida, 2, ',', '.').$font_close;?>
        </td>
        <td align='right'>
            <?=$font_open.number_format($calculo_total_impostos['valor_total_nota'] * $fator_entrada_saida, 2, ',', '.').$font_close;?>
        </td>
    </tr>
<?
            $valor_icms_nfs_rs_empresa+=            ($calculo_total_impostos['valor_icms'] * $fator_entrada_saida);
            $valor_icms_st_nfs_rs_empresa+=         ($calculo_total_impostos['valor_icms_st'] * $fator_entrada_saida);
            $valor_ipi_rs_nfs_empresa+=             ($calculo_total_impostos['valor_ipi'] * $fator_entrada_saida);
            $valor_total_produtos_nfs_rs_empresa+=  ($calculo_total_impostos['valor_total_produtos'] * $fator_entrada_saida);
            $valor_total_nota_nfs_rs_empresa+=      ($calculo_total_impostos['valor_total_nota'] * $fator_entrada_saida);

            $valor_icms_nfs_rs_geral+=              ($calculo_total_impostos['valor_icms'] * $fator_entrada_saida);
            $valor_icms_st_nfs_rs_geral+=           ($calculo_total_impostos['valor_icms_st'] * $fator_entrada_saida);
            $valor_ipi_nfs_rs_geral+=               ($calculo_total_impostos['valor_ipi'] * $fator_entrada_saida);
            $valor_total_produtos_nfs_rs_geral+=    ($calculo_total_impostos['valor_total_produtos'] * $fator_entrada_saida);
            $valor_total_nota_nfs_rs_geral+=        ($calculo_total_impostos['valor_total_nota'] * $fator_entrada_saida);
        }
?>
<!--Apresenta fora do Loop o Total Geral da última Empresa-->
    <tr class='linhadestaque' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total(is) por Empresa => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_nfs_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_st_nfs_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_ipi_nfs_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_produtos_nfs_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_nota_nfs_rs_empresa, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total(is) Geral(is) => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_nfs_rs_geral, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_st_nfs_rs_geral, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_ipi_nfs_rs_geral, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_produtos_nfs_rs_geral, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_nota_nfs_rs_geral, 2, ',', '.');?>
        </td>
    </tr>
<?
    }
/******************************************************************************/
/********************************NFS de Entrada********************************/
/******************************************************************************/
    //Busca das NFs de Compras que estejam no Período digitado pelo Usuário e somente Liberadas ...
    $sql = "SELECT f.`id_pais`, nfe.`id_nfe`, nfe.`id_empresa`, nfe.`num_nota`, 
            nfe.`valor_icms_oculto_creditar`, 
            DATE_FORMAT(nfe.`data_entrega`, '%d/%m/%Y') AS data_entrega 
            FROM `nfe` 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
            WHERE nfe.`id_empresa` = '$cmb_empresa' 
            AND nfe.`data_entrega` BETWEEN '$data_inicial' AND '$data_final' 
            AND nfe.`situacao` = '2' 
            ORDER BY nfe.`id_empresa`, nfe.`data_entrega` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
        $id_empresa_anterior = '';
        for($i = 0; $i < $linhas; $i++) {
            $calculo_total_impostos = calculos::calculo_impostos(0, $campos[$i]['id_nfe'], 'NFC');
/*Aqui eu verifico se a Empresa Anterior é Diferente da Empresa Atual que está sendo listada 
no loop, se for então eu atribuo o Empresa Atual p/ a Empresa Anterior ...*/
            if($id_empresa_anterior != $campos[$i]['id_empresa']) {
                $id_empresa_anterior = $campos[$i]['id_empresa'];
//Só não mostro essa linha quando acaba de Entrar no Loop ...
                if($i > 0) {
?>
    <tr class='linhadestaque' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total(is) por Empresa => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_nfe_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_st_nfe_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_ipi_nfe_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_produtos_nfe_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_nota_nfe_rs_empresa, 2, ',', '.');?>
        </td>
    </tr>
<?
                    //Zero essas variáveis p/ não acumular valores da Empresa Anterior ...
                    $valor_icms_nfe_rs_empresa              = 0;
                    $valor_icms_st_nfe_rs_empresa           = 0;
                    $valor_ipi_nfe_rs_empresa               = 0;
                    $valor_total_produtos_nfe_rs_empresa    = 0;
                    $valor_total_nota_nfe_rs_empresa        = 0;
                }

                if($i == 0) {//Antes da Exibição da Primeira Empresa ...
?>
    <tr class='iframe' align='center'>
        <td colspan='7'>
            NF(s) de Entrada
        </td>
    </tr>
<?
                }
?>
    <tr class='linhacabecalho'>
        <td colspan='7'>
            <font color='yellow'>
                <b>Empresa: </b>
            </font>
            <?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º da NF
        </td>
        <td>
            Data de Entrega
        </td>
        <td>
            Valor do ICMS
        </td>
        <td>
            Valor do ICMS ST
        </td>
        <td>
            IPI Incluso
            <br/>Valor do IPI
        </td>
        <td>
            Valor Total dos Produtos
        </td>
        <td>
            Valor Total da Nota
        </td>
    </tr>
<?
            }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href = '../../../compras/pedidos/nota_entrada/itens/itens.php?id_nfe=<?=$campos[$i]['id_nfe'];?>&pop_up=1' style='cursor:help' class='html5lightbox'>
                <?='<font title="NF de Entrada" style="cursor:help"><b>'.$campos[$i]['num_nota'].'</font>';?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_entrega'];?>
        </td>
        <td align='right'>
        <?
            if($campos[$i]['valor_icms_oculto_creditar'] > 0) {
                $valor_icms     = $campos[$i]['valor_icms_oculto_creditar'];
                $rotulo_icms    = ' <font color="red"><b>(ICMS Oculto)</b></font>';
            }else {
                $valor_icms     = $calculo_total_impostos['valor_icms'];
                $rotulo_icms    = '';
            }
            echo number_format($valor_icms, 2, ',', '.').$rotulo_icms;
        ?>
        </td>
        <td align='right'>
            <?=number_format($calculo_total_impostos['valor_icms_st'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            if($calculo_total_impostos['valor_ipi_incluso'] > 0) {
        ?>
            <font color='darkblue'>
                <b><?=number_format($calculo_total_impostos['valor_ipi_incluso'], 2, ',', '.');?></b>&nbsp;&nbsp;/&nbsp;&nbsp;
            </font>
        <?
            }
            echo number_format($calculo_total_impostos['valor_ipi'], 2, ',', '.');
        ?>
        </td>
        <td align='right'>
            <?=number_format($calculo_total_impostos['valor_total_produtos'], 2, ',', '.');?>
        </td>
        <td align='right'>
            <?=number_format($calculo_total_impostos['valor_total_nota'], 2, ',', '.');?>
        </td>
    </tr>
<?
            $valor_icms_nfe_rs_empresa+=            $valor_icms;
            $valor_icms_st_nfe_rs_empresa+=         $calculo_total_impostos['valor_icms_st'];
            $valor_ipi_incluso_nfe_rs_empresa+=     $calculo_total_impostos['valor_ipi_incluso'];
            $valor_ipi_nfe_rs_empresa+=             $calculo_total_impostos['valor_ipi'];
            $valor_total_produtos_nfe_rs_empresa+=  $calculo_total_impostos['valor_total_produtos'];
            $valor_total_nota_nfe_rs_empresa+=      $calculo_total_impostos['valor_total_nota'];

            $valor_icms_nfe_rs_geral+=              $valor_icms;
            $valor_icms_st_nfe_rs_geral+=           $calculo_total_impostos['valor_icms_st'];
            $valor_ipi_incluso_nfe_rs_geral+=       $calculo_total_impostos['valor_ipi_incluso'];
            $valor_ipi_nfe_rs_geral+=               $calculo_total_impostos['valor_ipi'];
            $valor_total_produtos_nfe_rs_geral+=    $calculo_total_impostos['valor_total_produtos'];
            $valor_total_nota_nfe_rs_geral+=        $calculo_total_impostos['valor_total_nota'];
        }
?>
<!--Apresenta fora do Loop o Total Geral da última Empresa-->
    <tr class='linhadestaque' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total(is) por Empresa => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_nfe_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_st_nfe_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
        <?
            if($valor_ipi_incluso_nfe_rs_empresa > 0) echo 'R$ '.number_format($valor_ipi_incluso_nfe_rs_empresa, 2, ',', '.').'  /  ';
            echo 'R$ '.number_format($valor_ipi_nfe_rs_empresa, 2, ',', '.');
        ?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_produtos_nfe_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_nota_nfe_rs_empresa, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total(is) Geral(is) => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_nfe_rs_geral, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_st_nfe_rs_geral, 2, ',', '.');?>
        </td>
        <td>
        <?
            if($valor_ipi_incluso_nfe_rs_geral > 0) echo 'R$ '.number_format($valor_ipi_incluso_nfe_rs_geral, 2, ',', '.').'  /  ';
            echo 'R$ '.number_format($valor_ipi_nfe_rs_geral, 2, ',', '.');
        ?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_produtos_nfe_rs_geral, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_nota_nfe_rs_geral, 2, ',', '.');?>
        </td>
    </tr>
<?
    }
/******************************************************************************/
/********************************Contas à Pagar********************************/
/******************************************************************************/
    //Busca das Contas à Pagar que estejam no Período digitado pelo Usuário ...
    $sql = "SELECT f.`id_pais`, ca.`id_conta_apagar`, ca.`id_empresa`, ca.`numero_conta`, 
            DATE_FORMAT(ca.`data_emissao`, '%d/%m/%Y') AS data_emissao, ca.`valor_icms` 
            FROM `contas_apagares` ca 
            INNER JOIN `fornecedores` f ON f.`id_fornecedor` = ca.`id_fornecedor` 
            WHERE ca.`valor_icms` > '0' 
            AND ca.`id_empresa` = '$cmb_empresa' 
            AND ca.`data_emissao` BETWEEN '$data_inicial' AND '$data_final' 
            ORDER BY ca.`id_empresa`, ca.`data_emissao` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    if($linhas > 0) {//Se encontrou pelo menos 1 Registro ...
        $id_empresa_anterior = '';
        for($i = 0; $i < $linhas; $i++) {
            $calculo_total_impostos = calculos::calculo_impostos(0, $campos[$i]['id_nfe'], 'NFC');
/*Aqui eu verifico se a Empresa Anterior é Diferente da Empresa Atual que está sendo listada 
no loop, se for então eu atribuo o Empresa Atual p/ a Empresa Anterior ...*/
            if($id_empresa_anterior != $campos[$i]['id_empresa']) {
                $id_empresa_anterior = $campos[$i]['id_empresa'];
//Só não mostro essa linha quando acaba de Entrar no Loop ...
                if($i > 0) {
?>
    <tr class='linhadestaque' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total(is) por Empresa => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_contas_a_pagar_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_st_contas_a_pagar_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_ipi_contas_a_pagar_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_produtos_contas_a_pagar_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_nota_contas_a_pagar_rs_empresa, 2, ',', '.');?>
        </td>
    </tr>
<?
                    //Zero essas variáveis p/ não acumular valores da Empresa Anterior ...
                    $valor_icms_contas_a_pagar_rs_empresa              = 0;
                }

                if($i == 0) {//Antes da Exibição da Primeira Empresa ...
?>
    <tr class='iframe' align='center'>
        <td colspan='7'>
            Conta(s) à Pagar
        </td>
    </tr>
<?
                }
?>
    <tr class='linhacabecalho'>
        <td colspan='7'>
            <font color='yellow'>
                <b>Empresa: </b>
            </font>
            <?=genericas::nome_empresa($campos[$i]['id_empresa']);?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            N.º da NF
        </td>
        <td>
            Data de Emissão
        </td>
        <td>
            Valor do ICMS
        </td>
        <td>
            Valor do ICMS ST
        </td>
        <td>
            Valor do IPI
        </td>
        <td>
            Valor Total dos Produtos
        </td>
        <td>
            Valor Total da Nota
        </td>
    </tr>
<?
            }
?>
    <tr class='linhanormal' align='center'>
        <td>
            <a href = '../../../financeiro/pagamento/a_apagar/alterar.php?id_conta_apagar=<?=$campos[$i]['id_conta_apagar'];?>&pop_up=1' style='cursor:help' class='html5lightbox'>
                <?='<font title="NF de Entrada" style="cursor:help"><b>'.$campos[$i]['numero_conta'].'</font>';?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['data_emissao'];?>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['valor_icms'], 2, ',', '.');?>
        </td>
        <td align='right'>
            
        </td>
        <td align='right'>
            
        </td>
        <td align='right'>
            
        </td>
        <td align='right'>
            
        </td>
    </tr>
<?
            $valor_icms_contas_a_pagar_rs_empresa+= $campos[$i]['valor_icms'];
            $valor_icms_contas_a_pagar_rs_geral+=   $campos[$i]['valor_icms'];
        }
?>
<!--Apresenta fora do Loop o Total Geral da última Empresa-->
    <tr class='linhadestaque' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total(is) por Empresa => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_contas_a_pagar_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            
        </td>
        <td>
            
        </td>
        <td>
            
        </td>
        <td>
            
        </td>
    </tr>
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Total(is) Geral(is) => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_contas_a_pagar_rs_geral, 2, ',', '.');?>
        </td>
        <td>

        </td>
        <td>

        </td>
        <td>

        </td>
        <td>

        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='right'>
        <td colspan='2'>
            <font color='yellow' size='-1'>
                Saldo à Pagar => 
            </font>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_nfs_rs_geral - $valor_icms_nfe_rs_geral - $valor_icms_contas_a_pagar_rs_empresa, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_icms_st_nfs_rs_geral - $valor_icms_st_nfe_rs_geral, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_ipi_nfs_rs_geral - $valor_ipi_nfe_rs_geral - $valor_ipi_incluso_nfe_rs_geral, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_produtos_nfs_rs_geral - $valor_total_produtos_nfe_rs_geral, 2, ',', '.');?>
        </td>
        <td>
            <?='R$ '.number_format($valor_total_nota_nfs_rs_geral - $valor_total_nota_nfe_rs_geral, 2, ',', '.');?>
        </td>
    </tr>
<?
    //}
}
?>
</table>
</form>
</body>
</html>
<!--Apresento o Botão de Imprimir p/ que o Usuário Imprima a Listagem caso desejar ...-->
<Script Language = 'JavaScript'>
    parent.document.getElementById('linha_imprimir').style.visibility = 'visible'
</Script>