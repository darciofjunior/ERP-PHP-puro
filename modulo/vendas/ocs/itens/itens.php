<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/vendas.php');
segurancas::geral('/erp/albafer/modulo/vendas/ocs/itens/consultar.php', '../../../../');

//Busca de alguns dados da OC ...
$sql = "SELECT ocs.`id_cliente`, ocs.`id_cliente_contato`, DATE_FORMAT(ocs.`data_emissao`, '%d/%m/%Y') AS data_emissao, 
        DATE_FORMAT(ocs.`data_conclusao`, '%d/%m/%Y') AS data_conclusao, ocs.`nf_entrada`, ocs.`observacao`, 
        c.`cod_cliente`, c.`id_uf`, IF(c.`razaosocial` = '', c.`nomefantasia`, c.`razaosocial`) AS cliente, 
        c.`credito`, c.`endereco`, c.`num_complemento`, c.`bairro`, c.`cep`, c.`cidade`, 
        ocs.`id_representante`, ocs.`status`, cc.`nome` 
        FROM `ocs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = ocs.`id_cliente` 
        LEFT JOIN `clientes_contatos` cc ON cc.`id_cliente_contato` = ocs.`id_cliente_contato` 
        WHERE ocs.`id_oc` = '$_GET[id_oc]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Itens ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet' media = 'screen'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/ajax.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function ativar_loading() {
    document.getElementById('listar_itens').innerHTML = "<img src='/erp/albafer/css/new_loading.gif'>"
    listar_itens()
}

function listar_itens() {
    var credito = '<?=$campos[0]['credito'];?>'
    //Só mostro essa Mensagem como Lembrete p/ os Clientes que possuem Crédito C ou D ...
    if(credito == 'C' || credito == 'D') alert('CLIENTE COM CRÉDITO "'+credito+'" !')
    ajax('/erp/albafer/modulo/vendas/ocs/itens/listar_itens.php?id_oc=<?=$_GET['id_oc'];?>', 'listar_itens')
}

function incluir_defeito(id_oc_item) {
    html5Lightbox.showLightbox(7, 'incluir_defeito.php?id_oc_item='+id_oc_item)
}

function alterar_item(posicao) {
    html5Lightbox.showLightbox(7, 'alterar.php?id_oc=<?=$_GET['id_oc'];?>&posicao='+posicao)
}

function excluir_item(id_oc_item) {
    if (confirm('TEM CERTEZA QUE DESEJA EXCLUIR ESSE ITEM ?')) {
        ajax('listar_itens.php?id_oc=<?=$_GET['id_oc'];?>&id_oc_item='+id_oc_item, 'listar_itens')
    }
}
</Script>
</head>
<body onload='listar_itens()'>
<form name='form'>
<input type='hidden' name='id_oc' value='<?=$_GET['id_oc']?>'>
<table width='95%' border='0' cellpadding='0' cellspacing='0' align='center'>
    <tr>
        <td>
            <fieldset>
                <legend class='legend_contorno'>
                    OC Nº: 
                    <font color='darkblue'>
                        <?=$_GET['id_oc'];?>
                    </font>
                </legend>
                <table width='95%' border='0' cellpadding='0' cellspacing='0' align='center'>
                    <tr align='left'>
                        <td colspan='2'>
                            <fieldset>
                                <legend>
                                    <span style='cursor: pointer'>
                                        <b>DADOS DO CLIENTE</b>
                                        <a href = '../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$campos[0]['id_cliente'];?>&nao_exibir_menu=1' class='html5lightbox'>
                                            <img src = '../../../../imagem/propriedades.png' title='Detalhes de Cliente' alt='Detalhes de Cliente' style='cursor:pointer' border='0'>
                                        </a>
                                    </span>
                                </legend>
                                <table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
                                    <tr class='linhanormal'>
                                        <td bgcolor='#CECECE'>
                                            <a href = '../../../classes/cliente/alterar.php?passo=1&id_cliente=<?=$campos[0]['id_cliente'];?>&nao_exibir_menu=1' class='html5lightbox'>
                                                <font color='#000000'>
                                                    <?=$campos[0]['cod_cliente'].' - '.$campos[0]['cliente'];?>
                                                </font>
                                                <img src = '../../../../imagem/propriedades.png' title='Detalhes de Cliente' alt='Detalhes de Cliente' style='cursor:pointer' border='0'>
                                            </a>
                                            / <b>CONTATO:</b>
                                            <?=$campos[0]['nome'];?>
                                            / <b>END:</b>
                                            <?
                                                echo $campos[0]['endereco'].', '.$campos[0]['num_complemento'].' - '.$campos[0]['bairro'].' - '.$campos[0]['cidade'];

                                                if($campos[0]['id_uf'] > 0) {
                                                    $sql = "SELECT `sigla` 
                                                            FROM `ufs` 
                                                            WHERE `id_uf` = '".$campos[0]['id_uf']."' LIMIT 1 ";
                                                    $campos_uf = bancos::sql($sql);
                                                    echo ' - '.$campos_uf[0]['sigla'];
                                                }
                                            ?>
                                            - <b>CEP:</b>
                                            <?=$campos[0]['cep'];?>
                                        </td>
                                    </tr>
                                </table>
                            </fieldset>
                        </td>
                    </tr>
                    <tr align='left' height='5'>
                        <td colspan='2'>
                            <fieldset>
				<legend>
                                    <b>DADOS DE OC</b>
                                    <img id='img_cabecalho' title='Alterar Dados de OC' style='cursor:pointer' src='/erp/albafer/imagem/menu/alterar.png' width='17' height='15' border="0" onclick="html5Lightbox.showLightbox(7, '/erp/albafer/modulo/vendas/ocs/alterar_cabecalho.php?id_oc=<?=$_GET['id_oc'];?>')"/>
				</legend>
				<table width='100%' border='0' cellspacing='1' cellpadding='1' align='center'>
                                    <tr class='linhanormal'>
                                        <td width='500'>
                                            <b>REPRESENTANTE: </b>
                                            <?
                                                //Busco o "Representante" da OC ...
                                                $sql = "SELECT `nome_fantasia` 
                                                        FROM `representantes` 
                                                        WHERE `id_representante` = '".$campos[0]['id_representante']."' LIMIT 1 ";
                                                $campos_representante = bancos::sql($sql);
                                                if(count($campos_representante) == 1) {//Tem Representante ...
                                                    //Verifico quem é o Supervisor desse Representante ...
                                                    $sql = "SELECT r.`nome_fantasia` AS supervisor 
                                                            FROM `representantes_vs_supervisores` rs 
                                                            INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante_supervisor` 
                                                            WHERE rs.`id_representante` = '".$campos[0]['id_representante']."' LIMIT 1 ";
                                                    $campos_supervisor = bancos::sql($sql);
                                                    if(count($campos_supervisor) == 1) {//Se encontre o Supervisor, apresento este ao lado do Representante ...
                                                        $supervisor = ' ('.$campos_supervisor[0]['supervisor'].') ';
                                                    }else {//Se não só apresenta o Vendedor que no caso seria o próprio representante ...
                                                        $supervisor = '';
                                                    }
                                                    echo $campos_representante[0]['nome_fantasia'].$supervisor;
                                                }
                                            ?>
                                        </td>
                                        <td width='500'>
                                            <b>N.º NF DE ENTRADA: </b><?=$campos[0]['nf_entrada'];?>
                                        </td>
                                    </tr>
                                    <tr class='linhanormal'>
                                        <td>
                                            <b>DATA DE EMISSÃO: </b><?=$campos[0]['data_emissao'];?>
                                            <?
                                                $dias = data::diferenca_data(data::datatodate($campos[0]['data_emissao'], '-'), date('Y-m-d'));
                                                if($dias[0] > 8) echo '<font color="red"><b> - '.$dias[0].' DIA(S) - FORA DE PRAZO</b></font>';
                                            ?>
                                        </td>
                                        <td>
                                            <b>DATA DE CONCLUSÃO: </b><?if($campos[0]['data_conclusao'] != '00/00/0000') echo $campos[0]['data_conclusao'];?>
                                        </td>
                                    </tr>
				</table>
                            </fieldset>
			</td>
                    </tr>
                    <tr align='left' height='5'>
                        <td colspan='2'>
                            <fieldset>
				<legend>
                                    <b>OBSERVAÇÃO</b>
				</legend>
				<table width="100%" border='0' cellspacing='1' cellpadding='1' align='center'>
                                    <tr class='linhanormal'>
                                        <td bgcolor='#CECECE' colspan='2'>
                                            <img src = '../../../../imagem/exclamacao.gif' height='30' border='0'>
                                            <font color='red' size='3'>
                                                <b><?=$campos[0]['observacao'];?></b>
                                            </font>
                                            <img src = '../../../../imagem/exclamacao.gif' height='30' border='0'>
                                        </td>
                                    </tr>
				</table>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <font size='1px'>
                                &nbsp;
                            </font>
                        </td>
                    </tr>
                    <tr class='linha_e' valign='top'>
			<td colspan='6'>
                            <fieldset>
                                <legend>
                                    <b>ITENS</b>
                                    &nbsp;-&nbsp;
                                    <?
                                        //Se a OC foi finalizada, já não posso mais incluir ou excluir nenhum Item ...
                                        if($campos[0]['status'] == 1) {
                                            $disabled   = 'disabled';
                                            $class      = 'textdisabled';
                                        }else {//Está em aberta ...
                                            $disabled   = '';
                                            $class      = 'botao';
                                        }
                                    ?>
                                    <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'consultar.php<?=$parametro;?>'" class='botao'>
                                    <input type='button' name='cmd_incluir' value='Incluir' title='Incluir' onclick="html5Lightbox.showLightbox(7, 'incluir.php?id_oc=<?=$_GET['id_oc'];?>')" class='<?=$class;?>' <?=$disabled;?>>
                                    <input type='button' name='cmd_outras' value='Outras Op&ccedil;&otilde;es' title='Outras Op&ccedil;&otilde;es' onclick="html5Lightbox.showLightbox(7, 'outras_opcoes.php?id_oc=<?=$_GET['id_oc'];?>')" class='<?=$class;?>' <?=$disabled;?>>
                                    <input type='button' name="cmd_imprimir" value='Imprimir' title='Imprimir' class='botao' onclick='window.print()'>
                                </legend>
                                <div id='listar_itens' align='center'>
                                    <img src='/erp/albafer/css/new_loading.gif'>
                                </div>
                            </fieldset>
			</td>
                    </tr>
                </table>
            </fieldset>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
/*Se ainda não foram preenchidos os campos Contato e Representante da OC, então abro o Cabeçalho até 
que essas informações sejam preenchidas ...*/
if($campos[0]['id_cliente_contato'] == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('PREENCHA OS DADOS DE CABEÇALHO DA OC !')
        /*Coloquei um Timeout aqui, porque o Browser leva alguns Milésimos de Segundo 
        p/ carregar a Biblioteca "lightbox" na memória ...*/
        setTimeout("document.getElementById('img_cabecalho').onclick()", 400)
    </Script>
<?
}
?>