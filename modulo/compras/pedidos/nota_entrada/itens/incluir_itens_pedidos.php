<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/calculos.php');//Essa biblioteca é utilizada dentro da Biblioteca 'compras_new' ...
require('../../../../../lib/compras_new.php');
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
segurancas::geral('/erp/albafer/modulo/compras/pedidos/nota_entrada/itens/consultar.php', '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ITEM DE PEDIDO INCLUIDO NA NOTA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>ITEM DE PEDIDO JÁ EXISTENTE NESTA NOTA.</font>";
$mensagem[4] = "<font class='erro'>EXISTEM DIVERGÊNCIA(S) P/ A IMPORTAÇÃO DESSE PEDIDO NA NOTA.</font>";

function verificar_pedidos_financiamento($id_nfe, $id_pedido, $id_item_pedido) {
//Aqui eu verifico se essa NF já importou algum Pedido do Tipo Financiamento ...
    $sql = "SELECT id_nfe 
            FROM `nfe_financiamentos` 
            WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
    $campos_nota_financ = bancos::sql($sql);
    $linhas_nota_financ = count($campos_nota_financ);
//Aqui eu verifico se o Item do Pedido que está sendo importado é do Tipo Financiamento ...
    $sql = "SELECT id_pedido_financiamento 
            FROM `pedidos_financiamentos` 
            WHERE `id_pedido` = '$id_pedido' LIMIT 1 ";
    $campos_pedido_financ = bancos::sql($sql);
    $linhas_pedido_financ = count($campos_pedido_financ);

    if($linhas_nota_financ == 1) {//Significa que esta NF é do Tipo Financiamento ...
        if($linhas_pedido_financ == 1) {//Este Pedido Corrente é do Tipo Financiamento ...
/*Se o Pedido da NF que foi Importado anteriormente for diferente do Pedido Corrente, então não 
posso importar porque senão ficará em divergência ... - Só o mesmo Pedido*/
            if($id_pedido != $campos_nota_financ[0]['id_pedido']) $retorno = 1;
        }else {//Se a NF é do Tipo Financiamento, não posso importar pedidos de outro Tipo
            $retorno = 1;
        }
    }else {//Nota Fiscal do Tipo Normal ...
//Aqui eu verifico se já existe algum Item de Pedido importado nessa NF ...
        $sql = "SELECT id_nfe_historico 
                FROM `nfe_historicos` 
                WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
        $campos_importado = bancos::sql($sql);
        $linhas_importado = count($campos_importado);
        if($linhas_importado == 1) {//Esta Nota Fiscal já possui 1 item do Tipo Normal 
            //Este Pedido Corrente é do Tipo Financiamento ...
            if($linhas_pedido_financ == 1) $retorno = 1;//Não posso importar porque a NF é do Tipo Normal ...
        }else {//Posso importar qualquer Tipo de Pedido até porque a NF ainda está sem Itens
            $retorno = 0;
        }
    }
    //return $retorno;
    return 0;//Pode ser que essa função está com problema, precisa ver com o Roberto ...
}

if($passo == 1) {
    $sql = "SELECT nfe.`id_empresa`, nfe.`id_fornecedor`, nfe.`id_tipo_moeda`, nfe.`tipo`, p.`id_pais` 
            FROM `nfe` 
            INNER JOIN `fornecedores` f ON f.id_fornecedor = nfe.id_fornecedor 
            INNER JOIN `paises` p ON p.id_pais = f.id_pais 
            WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
    $campos             = bancos::sql($sql);//Aki busca o id_empresa, id_fornecedor e o Tipo de Moeda da Fiscal c/ o id da NF
//Tem que renomear essa variável, pq já existe uma variável com o nome de $id_empresa na sessão ...
    $id_empresa_nf 	= $campos[0]['id_empresa'];
    $id_fornecedor 	= $campos[0]['id_fornecedor'];
    $id_tipo_moeda 	= $campos[0]['id_tipo_moeda'];
    $id_pais 		= $campos[0]['id_pais'];
    //Tratamento para o Tipo de Nota
    $tipo               = ($campos[0]['tipo'] == 1) ? 'NF' : 'SGD';
    //Primeira consistência a ser feita, é ver se a referência do PA que foi filtrado é um PI
    if(!empty($txt_ref_pa)) {
        $sql = "SELECT `id_produto_insumo` 
                FROM `produtos_acabados` 
                WHERE `referencia` LIKE '%$txt_ref_pa%' 
                AND `id_produto_insumo` > '0' 
                AND `ativo` = '1' ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
        if($linhas > 0) {//Se encontrar pelo menos 1 item, então disparo o loop
            for($i = 0; $i < $linhas; $i++) $id_produtos_insumos.= $campos[$i]['id_produto_insumo'].', ';
            $id_produtos_insumos = substr($id_produtos_insumos, 0, strlen($id_produtos_insumos) - 2);
            $condicao_pa = " AND pi.`id_produto_insumo` IN ($id_produtos_insumos)";
        }
    }
    
    //Trago todos os itens de Pedido de acordo com o Filtro feito pelo Usuário ...
    $sql = "SELECT ip.`id_item_pedido` 
            FROM `itens_pedidos` ip 
            INNER JOIN `oss_itens` oi ON oi.`id_item_pedido` = ip.`id_item_pedido` 
            INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`status` = '1' AND p.`id_fornecedor` = '$id_fornecedor' AND p.`id_tipo_moeda` = '$id_tipo_moeda' AND p.`id_empresa` = '$id_empresa_nf' AND p.`id_pedido` LIKE '%$txt_num_ped%' 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` AND pi.`discriminacao` LIKE '%$txt_discriminacao%' 
            INNER JOIN `grupos` g ON g.id_grupo = pi.id_grupo AND g.`referencia` LIKE '%$txt_ref_pi%' $condicao_pa 
            WHERE ip.`status` < '2' ";
    $campos_itens_pedidos = bancos::sql($sql);
    $linhas_itens_pedidos = count($campos_itens_pedidos);
    
    if($linhas_itens_pedidos == 0) {
        $id_itens_pedidos = 0;
    }else {
        for($i = 0; $i < $linhas_itens_pedidos; $i++) $id_itens_pedidos.= $campos_itens_pedidos[$i]['id_item_pedido'].', ';
        $id_itens_pedidos = substr($id_itens_pedidos, 0, strlen($id_itens_pedidos) - 2);
    }
    
    //Verifico o Tipo de Moeda do Pedido ...
    $sql = "SELECT `id_tipo_moeda` 
            FROM `pedidos` 
            WHERE `id_pedido` = '$txt_num_ped' LIMIT 1 ";
    $campos_tipo_moeda = bancos::sql($sql);

    /*Só listo Pedidos do Fornecedor em Aberto, que não sejam Programados / Contabilizados 
    e com Itens de Pedidos em Aberto ou Parcial que não estejam vinculados a OS(s) ...*/
    $sql = "SELECT ip.*, g.`referencia`, pi.`discriminacao`, pi.`id_produto_insumo` 
            FROM `itens_pedidos` ip 
            INNER JOIN `pedidos` p ON p.`id_pedido` = ip.`id_pedido` AND p.`id_pedido` LIKE '%$txt_num_ped%' AND p.`id_empresa` = '$id_empresa_nf' AND p.`id_tipo_moeda` = '$id_tipo_moeda' AND p.`id_fornecedor` = '$id_fornecedor' AND p.`programado_descontabilizado` = 'N' AND p.`status` = '1' 
            INNER JOIN `produtos_insumos` pi ON pi.`id_produto_insumo` = ip.`id_produto_insumo` AND pi.`discriminacao` LIKE '%$txt_discriminacao%' 
            INNER JOIN `grupos` g ON g.`id_grupo` = pi.`id_grupo` AND g.`referencia` LIKE '%$txt_ref_pi%' $condicao_pa 
            WHERE ip.`id_item_pedido` NOT IN ($id_itens_pedidos) 
            AND ip.`status` < '2' ORDER BY pi.`discriminacao`, ip.`id_pedido` ";
    $campos = bancos::sql($sql, $inicio, 50, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        var id_tipo_moeda_pedido    = eval('<?=$campos_tipo_moeda[0]['id_tipo_moeda'];?>')
        var id_tipo_moeda_nf        = eval('<?=$id_tipo_moeda;?>')
        var txt_num_ped             = '<?=$txt_num_ped;?>'
        
        if(txt_num_ped != '') {//Se existir um número de Pedido ...
            if(id_tipo_moeda_pedido != id_tipo_moeda_nf) {
                alert('O TIPO DE MOEDA DA NF DIFERE DO PEDIDO N.º "<?=$txt_num_ped;?>" À SER IMPORTADO !!!')
                window.close()
            }
        }else {
            window.location = 'incluir_itens_pedidos.php?id_nfe=<?=$id_nfe;?>&valor=1'
        }
    </Script>
<?	
    }else {
        //Seleciona o Tipo de moeda do fornecedor
        $sql = "SELECT CONCAT(tm.`simbolo`, ' ') AS moeda 
                FROM `nfe` 
                INNER JOIN `tipos_moedas` tm ON tm.id_tipo_moeda = nfe.id_tipo_moeda 
                WHERE nfe.`id_nfe` = '$id_nfe' LIMIT 1 ";
        $campos_moeda   = bancos::sql($sql);
        $moeda          = $campos_moeda[0]['moeda'];
        if($tipo_moeda == 1) {
            $moeda = 'R$ ';
        }else if($tipo_moeda == 2) {
            $moeda = 'U$ ';
        }else if($tipo_moeda == 3) {
            $moeda = '&euro; ';
        }
?>
<html>
<head>
<title>.:: Incluir Itens de Nota Fiscal ::.</title>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = 'tab_itens_ped_checkbox.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    if(!validar_checkbox('form', 'SELECIONE UMA OPÇÃO !')) {
        return false
    }
    var elementos = document.form.elements

//Significa que está tela foi carregada com apenas 1 linha ...
    if(typeof(elementos['txt_qtde[]'][0]) == 'undefined') {
        if(elementos['chkt_item_pedido[]'] == '[object HTMLInputElement]' || elementos['chkt_item_pedido[]'] == '[object]') {
            if(elementos['chkt_item_pedido[]'].checked == true) {//Se estiver checado o Shinebox (rsrs) ...
//Deixa no Formato em que o Banco de Dados vai reconhecer ...
                elementos['txt_qtde[]'].value = strtofloat(elementos['txt_qtde[]'].value)
                elementos['txt_preco[]'].value = strtofloat(elementos['txt_preco[]'].value)
                elementos['txt_ipi[]'].value = strtofloat(elementos['txt_ipi[]'].value)
                elementos['txt_icms[]'].value = strtofloat(elementos['txt_icms[]'].value)
                elementos['txt_reducao[]'].value = strtofloat(elementos['txt_reducao[]'].value)
                elementos['txt_iva[]'].value = strtofloat(elementos['txt_iva[]'].value)
//Desabilito p/ poder Gravar no Banco ...
                elementos['txt_ipi[]'].disabled         = false
                elementos['hdd_ipi_incluso[]'].disabled = false
                elementos['txt_icms[]'].disabled        = false
                elementos['txt_reducao[]'].disabled     = false
                elementos['txt_iva[]'].disabled         = false
                elementos['hdd_pedido[]'].disabled      = false
                elementos['hdd_produto_insumo[]'].disabled = false
            }
        }
    }else {
        for (i = 0; i < elementos.length; i++) {
            if(elementos['chkt_item_pedido[]'][i] == '[object HTMLInputElement]' || elementos['chkt_item_pedido[]'][i] == '[object]') {
                if(elementos['chkt_item_pedido[]'][i].checked == true) {//Se estiver checado o Shinebox (rsrs) ...
//Deixa no Formato em que o Banco de Dados vai reconhecer ...
                    elementos['txt_qtde[]'][i].value = strtofloat(elementos['txt_qtde[]'][i].value)
                    elementos['txt_preco[]'][i].value = strtofloat(elementos['txt_preco[]'][i].value)
                    elementos['txt_ipi[]'][i].value = strtofloat(elementos['txt_ipi[]'][i].value)
                    elementos['txt_icms[]'][i].value = strtofloat(elementos['txt_icms[]'][i].value)
                    elementos['txt_reducao[]'][i].value = strtofloat(elementos['txt_reducao[]'][i].value)
                    elementos['txt_iva[]'][i].value = strtofloat(elementos['txt_iva[]'][i].value)
//Desabilito p/ poder Gravar no Banco ...
                    elementos['txt_ipi[]'][i].disabled          = false
                    elementos['hdd_ipi_incluso[]'][i].disabled  = false
                    elementos['txt_icms[]'][i].disabled         = false
                    elementos['txt_reducao[]'][i].disabled      = false
                    elementos['txt_iva[]'][i].disabled          = false
                    elementos['hdd_pedido[]'][i].disabled       = false
                    elementos['hdd_produto_insumo[]'][i].disabled = false
                }
            }
        }
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=2';?>" onsubmit="return validar()">
<table width='98%' border='0' align='center' cellspacing='1' cellpadding='0' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            Incluir Itens de Nota Fiscal
            <font color='yellow'>
                <?=genericas::nome_empresa($id_empresa_nf);?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onClick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar todos' id='todos' class='checkbox'>
        </td>
        <td>
            Qtde
        </td>
        <td>
            Produto
        </td>
        <td>
            <font title='Preço Unitário <?=$moeda;?>' style='cursor:help'>
                P. Unit <?=$moeda;?>
            </font>
        </td>
        <td>
            <font title='Valor Total <?=$moeda;?>' style='cursor:help'>
                Val. Tot <?=$moeda;?>
            </font>
        </td>
        <td>
            IPI %
        </td>
        <td>
            ICMS %
        </td>
        <td>
            Red %
        </td>
        <td>
            IVA %
        </td>
        <td>
            <font title='Marca do Produto' style='cursor:help'>
                Marca
            </font>
        </td>
        <td>
            N.º Corrida
        </td>
        <td>
            <font title='N.º do Pedido / N.º da OS' style='cursor:help'>
                N.º Ped / OS
            </font>
        </td>
    </tr>
<?
//Utilizo essa variável para fazer o cálculo Total em Kilo ...
        $total_qtde = 0;
        $valor_total_ipi = 0;
        for ($i = 0;  $i < $linhas; $i++) {
            $id_pedido      = $campos[$i]['id_pedido'];
            $id_item_pedido = $campos[$i]['id_item_pedido'];
            $qtde_pedida    = $campos[$i]['qtde'];

            $sql = "SELECT SUM(qtde_entregue) AS total_entregue 
                    FROM `nfe_historicos` 
                    WHERE `id_item_pedido` = '$id_item_pedido' ";
            $campos_total_entregue  = bancos::sql($sql);
            $total_entregue         = $campos_total_entregue[0]['total_entregue'];
            $restante_entregar      = $qtde_pedida - $total_entregue;
?>
    <tr class='linhanormal' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_item_pedido[]' id='chkt_item_pedido<?=$i;?>' value="<?=$campos[$i]['id_item_pedido'];?>" onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td>
            <?$total_qtde+= $campos[$i]['qtde'];?>
<!--
Esse desvio é para não dar erro de JavaScript //if(this.value == '-0,0') {this.value = ''};
-->
            <input type='text' name='txt_qtde[]' id='txt_qtde<?=$i;?>' value="<?=number_format($restante_entregar, 2, ',', '.');?>" size='6' maxlength='7' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>', '#E8E8E8');return focos(this)" onkeyup="if(this.value == '-0,0') {this.value = ''};document.form.valor_atual.value = this.value; verifica('document.form.valor_atual','moeda_especial', '2', '1', event); this.value = document.form.valor_atual.value; calcular('<?=$i;?>', document.form.valor_corrente.value)" class='textdisabled' disabled>
        </td>
        <td align='left'>
        <?
            echo genericas::buscar_referencia($campos[$i]['id_produto_insumo'], $campos[$i]['referencia']).' * ';
            echo $campos[$i]['discriminacao'];
        ?>
        </td>
        <td align='right'>
            <input type='text' name='txt_preco[]' id='txt_preco<?=$i;?>' value="<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>" size='6' maxlength='7' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'moeda_especial', '2', '', event); if(this.value == '-') { this.value = ''}; calcular('<?=$i;?>', document.form.valor_corrente.value)" class='textdisabled' disabled>
        </td>
        <td align='right'>
        <?
            $total_preco = $restante_entregar * $campos[$i]['preco_unitario'];
        ?>
            <input type='text' name='txt_valor_total[]' id='txt_valor_total<?=$i;?>' size='9' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>', '#E8E8E8')" value="<?=$moeda.number_format($total_preco, 2, ',', '');?>" align='right' class='textdisabled' disabled>
        </td>
        <td>
            <?
                /*$ipi            = ($tipo == 'NF') ? $campos[$i]['ipi'] : 0;
                $valor_com_ipi 	= ($campos[$i]['valor_total'] * $ipi) / 100;
                $valor_total_ipi+= $valor_com_ipi;*/
                
                //Busco os campos de ICMS, Redução e IVA do "PI ativo e Fornecedor Default desse PI" diretamente na Lista de Preço ...
                $sql = "SELECT `ipi`, `ipi_incluso`, `icms`, `reducao`, `iva` 
                        FROM `fornecedores_x_prod_insumos` 
                        WHERE `id_fornecedor` = '$id_fornecedor' 
                        AND `id_produto_insumo` = '".$campos[$i]['id_produto_insumo']."' 
                        AND `ativo` = '1' LIMIT 1 ";
                $campos_lista_preco = bancos::sql($sql);

                //So ira existir ICMS para Notas do Tipo NF e Fornecedores Nacionais ...
                $ipi                = ($tipo == 'NF' && $id_pais == 31) ? $campos_lista_preco[0]['ipi'] : 0;
                $icms               = ($tipo == 'NF' && $id_pais == 31) ? $campos_lista_preco[0]['icms'] : 0;
                $reducao            = ($tipo == 'NF' && $id_pais == 31) ? $campos_lista_preco[0]['reducao'] : 0;
                $iva                = ($tipo == 'NF' && $id_pais == 31) ? $campos_lista_preco[0]['iva'] : 0;
                
                $valor_com_ipi 	= ($campos[$i]['valor_total'] * $ipi) / 100;
                $valor_total_ipi+= $valor_com_ipi;
                
                $valor_com_icms     = ($campos[$i]['valor_total'] * $icms) / 100;
                $valor_total_icms+= $valor_com_icms;
            ?>
            <input type='text' name='txt_ipi[]' id='txt_ipi<?=$i;?>' value='<?=number_format($ipi, 2, ',', '.');?>' size='4' maxlength='5' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='textdisabled' disabled>
            <?
                if($campos_lista_preco[0]['ipi_incluso'] == 'S') echo '<font color="red" title="IPI Incluso" style="cursor:help"><b>(Incl)</b></font>';
            ?>
            <input type='hidden' name='hdd_ipi_incluso[]' id='hdd_ipi_incluso<?=$i;?>' value='<?=$campos_lista_preco[0]['ipi_incluso'];?>' disabled>
        </td>
        <td align='right'>
            <input type='text' name='txt_icms[]' id='txt_icms<?=$i;?>' value='<?=number_format($icms, 2, ',', '.');?>' size='4' maxlength='5' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_reducao[]' id='txt_reducao<?=$i;?>' value='<?=number_format($reducao, 2, ',', '.');?>' size='4' maxlength='5' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_iva[]' id='txt_iva<?=$i;?>' value='<?=number_format($iva, 2, ',', '.');?>' size='4' maxlength='5' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>', '#E8E8E8');return focos(this)" onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_marca_obs[]' id='txt_marca_obs<?=$i;?>' value='<?=$campos[$i]['marca'];?>' size='12' maxlength='100' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>', '#E8E8E8')" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name='txt_num_corrida[]' id='txt_num_corrida<?=$i;?>' size='19' maxlength='15' onclick="checkbox_habilita('form', 'chkt_tudo', '<?=$i;?>', '<?=str_replace('.', ',', $campos[$i]['preco_unitario']);?>', '#E8E8E8');return focos(this)" class='textdisabled' disabled>
        </td>
        <td>
        <?
            echo $campos[$i]['id_pedido'];
//Verifico se este Pedido está atrelado em alguma OS ...
            $sql = "SELECT id_os 
                    FROM `oss` 
                    WHERE `id_pedido` = ".$campos[$i]['id_pedido']." LIMIT 1 ";
            $campos_os = bancos::sql($sql);
            //Encontrou uma OS nesse Pedido, então printo N. da OS ...
            if(count($campos_os) == 1) echo ' / '.$campos_os[0]['id_os'];
//Aqui eu verifico se foi feito algum Vencimento p/ Este Pedido ...
            $sql = "SELECT id_pedido_financiamento 
                    FROM `pedidos_financiamentos` 
                    WHERE `id_pedido` = ".$campos[$i]['id_pedido']." LIMIT 1 ";
            $campos_financiamento = bancos::sql($sql);
            //Encontrou um Venc. nesse Pedido, então printo N. da OS ...
            if(count($campos_financiamento) == 1) echo ' - <font color="red"><b>VENC(S)</b></font>';
        ?>
            <input type='hidden' name='hdd_pedido[]' id='hdd_pedido<?=$i;?>' value='<?=$id_pedido;?>' disabled>
            <input type='hidden' name='hdd_produto_insumo[]' id='hdd_produto_insumo<?=$i;?>' value='<?=$campos[$i]['id_produto_insumo'];?>' disabled>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhadestaque' align='center'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='text' name='txt_qtde_total' id='txt_qtde_total' size='6' maxlength='7' class='textdisabled' disabled>
        </td>
        <td colspan='10'>
            &nbsp;
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                FRETE QTD KGS P/ CÁLCULO TOTAL/FRETE ->
            </font>
        </td>
        <td colspan='7'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <?=number_format($total_qtde, 2, ',', '.');?> / KG-TOT
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='5'>
            <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                <b>DADOS BANCÁRIOS: <?=$bank;?><?=$agencia;?><?=$num_cc;?><?=$correntista;?></b>
            </font>
        </td>
        <td align='right'>
            <input type='text' name='txt_total_nf' value='0,00' size='8' class='textdisabled' disabled>
        </td>
        <td colspan='6'>
            &nbsp;IPI: <?=$moeda.number_format($valor_total_ipi, 2, ',', '.');?>
            &nbsp;ICMS: <?=$moeda.number_format($valor_total_icms, 2, ',', '.');?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='12'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'incluir_itens_pedidos.php?id_nfe=<?=$id_nfe;?>'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
        </td>
    </tr>
</table>
<input type='hidden' name='id_nfe' value="<?=$id_nfe;?>">
<input type='hidden' name='valor_atual'>
<input type='hidden' name='valor_corrente'>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
    $data_sys = date('Y-m-d H:i:s');//Data Atual
    foreach($_POST['chkt_item_pedido'] as $i => $id_item_pedido) {
        //$retorno = verificar_pedidos_financiamento($_POST['id_nfe'], $_POST['hdd_pedido'][$i], $id_item_pedido);
        //if($retorno == 1) {
            //$valor = 4;
        //}else {
            //Verifica se o Item já existente para a Nota Fiscal
            $sql = "SELECT id_item_pedido 
                    FROM `nfe_historicos` 
                    WHERE `id_pedido` = '".$_POST['hdd_pedido'][$i]."' 
                    AND `id_item_pedido` = '$id_item_pedido' 
                    AND `id_nfe` = '$_POST[id_nfe]' 
                    AND `status` = '0' ";
            $campos = bancos::sql($sql);
            if(count($campos) == 0) {
                $sql = "INSERT INTO `nfe_historicos` (`id_nfe_historico`, `id_item_pedido`, `id_produto_insumo`, `id_nfe`, `id_pedido`, `tipo`, `qtde_entregue`, `valor_entregue`, `ipi_entregue`, `ipi_incluso`, `icms_entregue`, `reducao`, `iva`, `marca`, `num_corrida`, `data_sys`) VALUES (NULL, '$id_item_pedido', '".$_POST['hdd_produto_insumo'][$i]."', '$id_nfe', '$hdd_pedido[$i]', 'E', '$txt_qtde[$i]', '$txt_preco[$i]', '".$_POST['txt_ipi'][$i]."', '".$_POST['hdd_ipi_incluso'][$i]."', '".$_POST['txt_icms'][$i]."', '".$_POST['txt_reducao'][$i]."', '".$_POST['txt_iva'][$i]."', '".$_POST['txt_marca_obs'][$i]."', '".$_POST['txt_num_corrida'][$i]."', '$data_sys') ";
                bancos::sql($sql);
                $valor = 2;
                compras_new::pedido_status($id_item_pedido);
            }else {
                $valor = 3;
            }
        //}
    }
//Aqui eu verifico a NF possui formas de Vencimento ...
    $sql = "SELECT id_nfe_financiamento 
            FROM `nfe_financiamentos` 
            WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
    $campos_financiamento = bancos::sql($sql);
//Se existir então chama a função, toda vez q excluir 1 item p/ recalcular as parcelas ...
    if(count($campos_financiamento) == 1) {
/*Toda vez que eu excluir os Itens eu garanto q o Sistema está zerando os Prazos de Vencimento do Modo 
Antigo p/ não dar conflitos com o JavaScript no cabeçalho da NF ...*/
        $sql = "UPDATE `nfe` SET `valor_a` = '0', `valor_b` = '0', `valor_c` = '0' WHERE `id_nfe` = '$_POST[id_nfe]' LIMIT 1 ";
        bancos::sql($sql);
/*********************************************/
/*Essa função pega o valor da Nota Fiscal, e desconta desse valor, o valor total das antecipações e 
e divide o valor restante de acordo com a Qtde de Prazos*/
        compras_new::calculo_valor_financiamento($_POST['id_nfe']);
    }
/*********************************************/
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir_itens_pedidos.php?id_nfe=<?=$_POST[id_nfe];?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
    //Aki busca a Empresa e a Importação c/ o id da NF
    $sql = "SELECT `id_empresa`, `id_importacao` 
            FROM `nfe`
            WHERE `id_nfe` = '$id_nfe' LIMIT 1 ";
    $campos         = bancos::sql($sql);
    //Tem que renomear essa variável, pq já existe uma variável com o nome de $id_empresa na sessão ...
    $id_empresa_nf  = $campos[0]['id_empresa'];
    /************************************************************************************************************/
    /*********************Automação p/ ir p/ a Tela Pós-Filtro quando tiver Importação na NF*********************/
    /************************************************************************************************************/
    if($campos[0]['id_importacao'] > 0) {
        /*Aqui eu busco o N.º do pedido através da Importação da NF p/ que esse vá por parâmetro diretamente p/ a Tela 
        Pós-Filtro, trazendo somente os PI(s) do Pedido da Importação de modo a facilitar a vida dos compradores 
        p/ que esses não precisem fazer o Filtro ...

        Obs: Só traz Pedidos quem estejam em Aberto de forma Total ou Parcial ...*/
        $sql = "SELECT `id_pedido` 
                FROM `pedidos` 
                WHERE `id_importacao` = '".$campos[0]['id_importacao']."' 
                AND `status` < '2' LIMIT 1 ";
        $campos_pedido = bancos::sql($sql);
        if(count($campos_pedido) == 1) {//Se encontrou um Pedido p/ essa Importação, redireciono a Tela ...
?>
        <Script Language = 'JavaScript'>
            window.location = 'incluir_itens_pedidos.php?passo=1&id_nfe=<?=$id_nfe;?>&txt_num_ped=<?=$campos_pedido[0]['id_pedido'];?>'
        </Script>
<?
        }
    }
    /************************************************************************************************************/
?>
<html>
<head>
<title>.:: Incluir Itens de Nota Fiscal ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript'>
//Atualiza a tela de baixo com a qual chamou o Pop-UP
function atualizar_abaixo() {
//Significa que só atualiza em baixo quando for pelo clique do X do Pop-Up
    if(document.form.nao_atualizar.value == 0) {
        if(typeof(opener.parent.itens.document.form) == 'object') {
            opener.parent.itens.document.form.submit()
            opener.parent.rodape.document.form.submit()
        }
    }
}
</Script>
</head>
<body onunload='atualizar_abaixo()' onload="document.form.txt_discriminacao.focus()">
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1'?>'>
<!--*****************Controles de Tela*****************-->
<input type='hidden' name='id_nfe' value='<?=$_GET['id_nfe'];?>'>
<input type='hidden' name='nao_atualizar'>
<input type='hidden' name='passo' value='1'>
<!--***************************************************-->
<table border='0' width='70%' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Incluir Itens de Nota Fiscal - Somente da Empresa 
            <font color='yellow'>
            <?
                $tp_empresa[1] = 'ALBAFER';
                $tp_empresa[2] = 'TOOL MASTER';
                $tp_empresa[4] = 'GRUPO';
                echo $tp_empresa[$id_empresa_nf];
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Discriminação
        </td>
        <td>
            <input type='text' name="txt_discriminacao" size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência P.I.
        </td>
        <td>
            <input type='text' name="txt_ref_pi" size='15' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Referência P.A.
        </td>
        <td>
            <input type='text' name="txt_ref_pa" size='15' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Número do Pedido
        </td>
        <td>
            <input type='text' name="txt_num_ped" size='15' maxlength='10' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<pre>
<b><font color='red'>Observação:</font></b>
    <pre>
    * Não permite Incluir item(ns) de Pedido(s) Programado(s) / Não Contabilizado(s).
    </pre>
</pre>
<?}?>