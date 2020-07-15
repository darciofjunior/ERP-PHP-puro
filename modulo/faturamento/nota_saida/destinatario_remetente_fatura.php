<?
require('../../../lib/segurancas.php');
require('../../../lib/calculos.php');
require('../../../lib/data.php');
require('../../../lib/faturamentos.php');
require('../../../lib/genericas.php');
require('../../../lib/intermodular.php');
require('../../classes/array_sistema/array_sistema.php');

/*Eu tenho esse desvio aki para não verificar a sessão desse arkivo, faço isso pq esse arquivo aki é um 
pop-up em outras partes do sistema e se eu não fizer esse desvio dá erro de permissão*/
if($nao_verificar_sessao != 1) {
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
}

/*Aqui nessa função eu faço a busca de todos os Pedidos que estão com o Prazo Médio irregular em 
comparação ao da Nota Fiscal*/
function peds_prazo_medio_irregular($id_nf, $prazo_medio_nf) {
    $diferenca_prazo_medio_maximo_entre_pedido_nf = genericas::variavel(78);
    
/*O prazo médio da NF não pode ser maior que o Prazo Médio do pedido + $diferenca_prazo_medio_maximo_entre_pedido_nf 

Exemplo: Pz na Nota Fiscal = 11 - Pz Médio no Pedido = 30 ...
$prazo_medio_nf > ($campos_itens[$i]['prazo_medio'] + $diferenca_prazo_medio_maximo_entre_pedido_nf) 

11 > (30 + 10) = 11 > 40 Se fosse não passaria porque significa que esses Prazos estão 
com muito divergência e não podemos faturar ...*/
    $sql = "SELECT DISTINCT (pv.`id_pedido_venda`), SUM(pvi.`qtde` * pvi.`preco_liq_final`) AS valor_pedido 
            FROM `nfs_itens` nfsi 
            INNER JOIN `pedidos_vendas_itens` pvi ON pvi.`id_pedido_venda_item` = nfsi.`id_pedido_venda_item` 
            INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` 
            WHERE nfsi.`id_nf` = '$id_nf' 
            /*AND (ABS(pv.`prazo_medio` - '$prazo_medio_nf') > $diferenca_prazo_medio_maximo_entre_pedido_nf)*/ 
            AND ('$prazo_medio_nf' > (pv.`prazo_medio` + '$diferenca_prazo_medio_maximo_entre_pedido_nf')) GROUP BY nfsi.`id_nf` ";
    $campos_prazo_irregular = bancos::sql($sql);
    $linhas = count($campos_prazo_irregular);
    if($linhas > 0) {
        $valor_nf_prazo_irregular = $campos_prazo_irregular[0]['valor_pedido'];
        $sql = "SELECT DISTINCT(pv.id_pedido_venda), SUM(pvi.qtde * pvi.preco_liq_final) AS valor_pedido 
                FROM `nfs_itens` nfsi 
                INNER JOIN `pedidos_vendas_itens` pvi ON pvi.id_pedido_venda_item = nfsi.id_pedido_venda_item 
                INNER JOIN `pedidos_vendas` pv ON pv.id_pedido_venda = pvi.id_pedido_venda 
                WHERE nfsi.id_nf = '$id_nf' GROUP BY nfsi.id_nf ";
        $campos_nf_volume           = bancos::sql($sql);
        $valor_nf_volume            = $campos_nf_volume[0]['valor_pedido'];
        $perc_nf_prazo_irregular    = round($valor_nf_prazo_irregular / $valor_nf_volume * 100, 2);
        if($perc_nf_prazo_irregular > 20) return 1;//retono 1 p/ barrar pois a nota não poderá serguir ...
        return 0;//normal pode liberar
    }else {
        return 0;//normal pode liberar
    }
}

//Procedimento normal de quando se carrega a Tela ...
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_nf      = $_POST['id_nf'];
    $opcao      = $_POST['opcao'];
    $acao       = $_POST['acao'];
}else {
    $id_nf      = $_GET['id_nf'];
    $opcao      = $_GET['opcao'];
    $acao       = $_GET['acao'];
}

if(!empty($_POST['hdd_atualizar_destinatario_remetente'])) {
//Garanti que a Nota escolhida anteriormente foi disponibilizada para um novo uso, mesmo que seje para a mesma Nota ...
    $sql = "UPDATE `nfs_num_notas` nnn 
            INNER JOIN `nfs` ON nfs.id_nf_num_nota = nnn.id_nf_num_nota 
            SET nnn.`nota_usado` = '0' WHERE nfs.`id_nf` = '$_POST[id_nf]' ";
    bancos::sql($sql);
//Com essa função eu atualizo o Prazo Médio do Pedido de Venda ...
    $prazo_medio            = intermodular::prazo_medio($_POST['txt_vencimento1'], $_POST['txt_vencimento2'], $_POST['txt_vencimento3'], $_POST['txt_vencimento4']);
    $prazo_medio_irregular  = peds_prazo_medio_irregular($_POST['id_nf'], $prazo_medio);
/*Se existir pelo menos 1 pedido que está com o Prazo Médio Irregular em Comparação ao da NF, o 
Sistema não permite alterar os dados de Cabeçalho da NF e retorna uma mensagem informando o usuário*/
    if($prazo_medio_irregular > 0) {
?>
    <Script Language = 'JavaScript'>
        alert('NÃO É POSSÍVEL ALTERAR O CABEÇALHO ! EXISTE(M) PEDIDO(S) EM QUE O PRAZO MÉDIO ESTÁ IRREGULAR EM COMPARAÇÃO AO DA NOTA FISCAL !')
        opener.parent.location = opener.parent.location.href
        window.close()
    </Script>
<?
    }else {
        $data_emissao = data::datatodate($_POST['txt_data_emissao'], '-');
/*********************************Controle com os Checkbox*********************************/
        
/************************************************************************************/
/**************************************Suframa***************************************/
/************************************************************************************/
//Só existirá essa opção para NF(s) com Suframa ...
        if(!empty($_POST['cmb_situacao_suframa'])) {
            $atualizar_suframa_nf   = ", `suframa_ativo` = '$_POST[cmb_situacao_suframa]', `id_funcionario_suframa` = '$_SESSION[id_funcionario]', `data_sys_suframa` = '".date('Y-m-d H:i:s')."' ";
/*Se o Suframa do Cliente estiver habilitado, então:
***Suframas 1 e 2 - Área de Livre / Comércio ou Zona Franca, então o Cliente se beneficia dos benefícios 
de PIS + Cofins e ICMS, mantendo como a CFOP como sendo 6.109 / 6.110 id_cfop 145 ...
***Suframa 3 - Amazônia Ocidental o CFOP é o normal de 6.101, como se fosse uma NF de Venda ...*/
            if($_POST['cmb_situacao_suframa'] == 'S') {
                //Busco o Tipo de Suframa que foi gravado na Nota Fiscal ...
                $sql = "SELECT `suframa` 
                        FROM `nfs` 
                        WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
                $campos     = bancos::sql($sql);
                $id_cfop    = ($campos[0]['suframa'] == 1 || $campos[0]['suframa'] == 2) ? 145 : 143;
//Se não estiver habilitado mudo a CFOP da NF como sendo uma NF de Venda 6.101 / 6.102 id_cfop 143 ...
            }else {
                $id_cfop = 143;
            }
            $atualizar_cfop = " `id_cfop` = '$id_cfop', ";
        }
        $sql = "UPDATE `nfs` SET `id_funcionario` = '$_SESSION[id_funcionario]', $atualizar_cfop `forma_pagamento`= '$_POST[cmb_forma_pagamento]', `data_emissao`= '$data_emissao', `vencimento1` = '$_POST[txt_vencimento1]', `vencimento2` = '$_POST[txt_vencimento2]', `vencimento3` = '$_POST[txt_vencimento3]', `vencimento4` = '$_POST[txt_vencimento4]', `prazo_medio` = '$prazo_medio', `data_sys` = '".date('Y-m-d H:i:s')."' $atualizar_suframa_nf WHERE `id_nf` = '$_POST[id_nf]' LIMIT 1 ";
        bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        alert('DADO(S) GERAL(IS) ATUALIZADO(S) COM SUCESSO !')
        opener.parent.location = opener.parent.location.href
        window.close()
    </Script>
<?
    }
}

//Aqui eu trago dados da "id_nf" passado por parâmetro ...
$sql = "SELECT c.`id_pais`, c.`id_uf`, c.`tipo_faturamento`, nfs.`id_cliente`, nfs.`id_empresa`, nfs.`tipo_nfe_nfs`, nfs.`forma_pagamento`, 
        nfs.`data_emissao`, nfs.`data_bl`, nfs.`vencimento1`, nfs.`vencimento2`, nfs.`vencimento3`, nfs.`vencimento4`, nfs.`valor_dolar_dia`, 
        nfs.`prazo_medio`, nfs.`trading`, nfs.`suframa`, nfs.`suframa_ativo`, nfs.`status`, nfs.`importado_financeiro` 
        FROM `nfs` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfs.`id_nf` = '$id_nf' LIMIT 1 ";
$campos                 = bancos::sql($sql);
$id_pais                = $campos[0]['id_pais'];
$id_uf                  = $campos[0]['id_uf'];
$forma_pagamento        = $campos[0]['forma_pagamento'];
$tipo_faturamento       = $campos[0]['tipo_faturamento'];
$id_cliente             = $campos[0]['id_cliente'];
$id_empresa_nf          = $campos[0]['id_empresa'];
$tipo_nfe_nfs           = $campos[0]['tipo_nfe_nfs'];

$data_emissao           = data::datetodata($campos[0]['data_emissao'], '/');
$data_bl                = data::datetodata($campos[0]['data_bl'], '/');

$vencimento1            = $campos[0]['vencimento1'];

/*Só existe este campo, p/ clientes Internacionais, caso esteja preenchido, os vencimentos
serão feitos em cima deste ...*/
if($campos[0]['data_bl'] != '0000-00-00') {
    $data_vencimento1 = data::adicionar_data_hora($data_bl, $vencimento1);
}else {
    if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento1 = data::adicionar_data_hora($data_emissao, $vencimento1);
}
$qtde_duplicatas = 1;//Sempre terá q ter pelo menos 1 duplicata ...

if($campos[0]['vencimento2'] == 0) {
    $vencimento2        = '';
    $data_vencimento2   = '';
}else {
    $vencimento2        = $campos[0]['vencimento2'];
    /*Só existe este campo, p/ clientes Internacionais, caso esteja preenchido, os vencimentos
    serão feitos em cima deste ...*/
    if($campos[0]['data_bl'] != '0000-00-00') {
        $data_vencimento2 = data::adicionar_data_hora($data_bl, $vencimento2);
    }else {
        if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento2 = data::adicionar_data_hora($data_emissao, $vencimento2);
    }
    $qtde_duplicatas++;
}

if($campos[0]['vencimento3'] == 0) {
    $vencimento3        = '';
    $data_vencimento3   = '';
}else {
    $vencimento3        = $campos[0]['vencimento3'];
    /*Só existe este campo, p/ clientes Internacionais, caso esteja preenchido, os vencimentos
    serão feitos em cima deste ...*/
    if($campos[0]['data_bl'] != '0000-00-00') {
        $data_vencimento3 = data::adicionar_data_hora($data_bl, $vencimento3);
    }else {
        if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento3 = data::adicionar_data_hora($data_emissao, $vencimento3);
    }
    $qtde_duplicatas++;
}

if($campos[0]['vencimento4'] == 0) {
    $vencimento4        = '';
    $data_vencimento4   = '';
}else {
    $vencimento4        = $campos[0]['vencimento4'];
    /*Só existe este campo, p/ clientes Internacionais, caso esteja preenchido, os vencimentos
    serão feitos em cima deste ...*/
    if($campos[0]['data_bl'] != '0000-00-00') {
        $data_vencimento4 = data::adicionar_data_hora($data_bl, $vencimento4);
    }else {
        if($campos[0]['data_emissao'] != '0000-00-00') $data_vencimento4 = data::adicionar_data_hora($data_emissao, $vencimento4);
    }
    $qtde_duplicatas++;
}

$prazo_medio            = $campos[0]['prazo_medio'];
$trading                = $campos[0]['trading'];
$suframa_nf             = $campos[0]['suframa'];
$suframa_ativo_nf       = $campos[0]['suframa_ativo'];
$status                 = $campos[0]['status'];
$importado_financeiro   = $campos[0]['importado_financeiro'];

if($campos[0]['data_saida_entrada'] != '0000-00-00') $data_saida_entrada = data::datetodata($campos[0]['data_saida_entrada'], '/');

//Aqui verifica o Tipo de Nota
if($id_empresa_nf == 1 || $id_empresa_nf == 2) {
    $nota_sgd   = 'N';//var surti efeito lá embaixo
    $tipo_nota  = ' (NF)';
}else {
    $nota_sgd   = 'S'; //var surti efeito lá embaixo
    $tipo_nota  = ' (SGD)';
}

/*Aqui verifica se a Nota Fiscal tem pelo menos 1 item cadastrado, se tiver não pode alterar 
a Empresa e o Tipo de Nota*/
$sql = "SELECT id_nfs_item 
        FROM `nfs_itens` 
        WHERE `id_nf` = '$id_nf' LIMIT 1 ";
$campos_qtde_itens  = bancos::sql($sql);
$qtde_itens_nf      = count($campos_qtde_itens);

if($acao == 'L') {//Significa que essa Tela foi aberta somente p/ Modo Leitura ...
    $class          = 'textdisabled';
    $class_combo    = 'textdisabled';
    $width          = '100%';
    $disabled       = 'disabled';
}else {//Significa que essa Tela foi aberta como Modo Gravação ...
    $class          = 'caixadetexto';
    $class_combo    = 'combo';
    $width          = '95%';
    $disabled       = '';
}

//Somente no status de "Em Aberto" 0 ou "NF de Devolução" 6 que os campos de Vencimento são editáveis e se a Tela foi aberta como Modo Gravação  ...
if(($status == 0 || $status == 6) && $acao == 'G') {
    $class_vencimentos      = 'caixadetexto';
    $disabled_vencimentos   = '';
}else {
    $class_vencimentos      = 'textdisabled';
    $disabled_vencimentos   = 'disabled';
}

//Observação: No ERP as Notas Fiscais começaram a funcionar a partir do dia 12 de Setembro de 2008 ...
$calculo_total_impostos = calculos::calculo_impostos(0, $id_nf, 'NF');
$valor_total_nota       = $calculo_total_impostos['valor_total_nota'];
$valor_total_produtos 	= $calculo_total_impostos['valor_total_produtos'];
?>
<html>
<head>
<title>.:: DESTINATÁRIO / REMETENTE / FATURA ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//CFOP
    var qtde_itens_nf                   = eval('<?=$qtde_itens_nf;?>')
    var id_empresa_nota                 = eval('<?=$id_empresa_nf;?>')
    var id_pais                         = eval('<?=$id_pais;?>')
    var id_cliente_tipo                 = eval('<?=$id_cliente_tipo;?>')
    var id_uf                           = eval('<?=$id_uf;?>')
    var linhas_classific_especificas    = '<?=$linhas_classific_especificas;?>'
    
/***************************************Chave de Acesso - NFe**************************/
//Data de Emissão ...
    if(document.form.txt_data_emissao.value != '') {
        if(!data('form', 'txt_data_emissao', '4000', 'EMISSÃO')) {
            return false
        }
    }
/************************************************************************************/
//Vencimento 1
    if(document.form.txt_vencimento1.value != '') {
        if(!texto('form', 'txt_vencimento1', '1', '0123456789', 'VENCIMENTO 1', '2')) {
            return false
        }
    }
//Vencimento 2
    if(document.form.txt_vencimento2.value != '') {
        if(!texto('form', 'txt_vencimento2', '1', '0123456789', 'VENCIMENTO 2', '2')) {
            return false
        }
    }
//Vencimento 3
    if(document.form.txt_vencimento3.value != '') {
        if(!texto('form', 'txt_vencimento3', '1', '0123456789', 'VENCIMENTO 3', '2')) {
            return false
        }
    }
//Vencimento 4
    if(document.form.txt_vencimento4.value != '') {
        if(!texto('form', 'txt_vencimento4', '1', '0123456789', 'VENCIMENTO 4', '2')) {
            return false
        }
    }
//Forma de Pagamento ...
    if(document.form.cmb_forma_pagamento.value == '' || document.form.cmb_forma_pagamento.value == 0) {
        alert('SELECIONE UMA FORMA DE PAGAMENTO !')
        document.form.cmb_forma_pagamento.focus()
        return false
    }
/****************Comparação dos Vencimentos**********************/
    if(qtde_itens_nf > 0) {//Só fará essa comparação caso venha existir pelo menos 1 item na NF
        var vencimento1 = eval(document.form.txt_vencimento1.value)
        var vencimento2 = eval(document.form.txt_vencimento2.value)
        var vencimento3 = eval(document.form.txt_vencimento3.value)
        var vencimento4 = eval(document.form.txt_vencimento4.value)
//Aqui força para não dar erro, quando o campo estiver em branco
        if(typeof(vencimento4) != 'undefined') {
            if(typeof(vencimento3) == 'undefined') vencimento3 = 0
            if(typeof(vencimento2) == 'undefined') vencimento2 = 0
            if(typeof(vencimento1) == 'undefined') vencimento1 = 0
        }
        if(typeof(vencimento3) != 'undefined') {
            if(typeof(vencimento2) == 'undefined') vencimento2 = 0
            if(typeof(vencimento1) == 'undefined') vencimento1 = 0
        }
        if(typeof(vencimento2) != 'undefined') {
            if(typeof(vencimento1) == 'undefined') vencimento1 = 0
        }
/*****************************************************************/
//Comparando o Vencimento 2
        if(vencimento2 <= vencimento1) {
            alert('VENCIMENTO 2 INVÁLIDO !!! \n VALOR DO VENCIMENTO 2 MENOR OU IGUAL AO VALOR DO VENCIMENTO 1 !')
            document.form.txt_vencimento2.focus()
            document.form.txt_vencimento2.select()
            return false
        }
//Comparando o Vencimento 3
        if(vencimento3 <= vencimento2 || vencimento3 <= vencimento1) {
            alert('VENCIMENTO 3 INVÁLIDO !!! \n VALOR DO VENCIMENTO 3 MENOR OU IGUAL AO VALOR DO VENCIMENTO 2 OU \n VALOR DO VENCIMENTO 3 MENOR OU IGUAL AO VALOR DO VENCIMENTO 1 !')
            document.form.txt_vencimento3.focus()
            document.form.txt_vencimento3.select()
            return false
        }
//Comparando o Vencimento 4
        if(vencimento4 <= vencimento3 || vencimento4 <= vencimento2 || vencimento4 <= vencimento1) {
            alert('VENCIMENTO 4 INVÁLIDO !!! \n VALOR DO VENCIMENTO 4 MENOR OU IGUAL AO VALOR DO VENCIMENTO 3 OU \n VALOR DO VENCIMENTO 4 MENOR OU IGUAL AO VALOR DO VENCIMENTO 2 OU \n VALOR DO VENCIMENTO 4 MENOR OU IGUAL AO VALOR DO VENCIMENTO 1 !')
            document.form.txt_vencimento4.focus()
            document.form.txt_vencimento4.select()
            return false
        }
    }
/***********************************************************/
//Desabilito esses campos p/ poder gravar na Base de Dados ...
    document.form.txt_vencimento1.disabled = false
    document.form.txt_vencimento2.disabled = false
    document.form.txt_vencimento3.disabled = false
    document.form.txt_vencimento4.disabled = false
    document.form.hdd_atualizar_destinatario_remetente.value = 'S'
}

function verificar(valor) {
    var id_pais         = eval('<?=$id_pais;?>')
    var tipo_nfe_nfs    = '<?=$tipo_nfe_nfs;?>'

    if(valor == 1) {//Vencimento 1
        if(document.form.txt_vencimento1.value == '') {
            document.form.txt_data_vencimento1.value = ''
        }else {
/*Se o Cliente for Internacional e a Nota Fiscal for uma Nota de Saída, então os prazos são 
acrescentados em cima da Data do B/L*/
            if(id_pais != 31 && tipo_nfe_nfs == 'S') {
                if(document.form.txt_data_bl.value != '') {
                    nova_data('document.form.txt_data_bl', 'document.form.txt_data_vencimento1', 'document.form.txt_vencimento1')
                }else {//Caso não esteje preenchido este campo, me baseio no da Data de Emissão ...
                    nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento1', 'document.form.txt_vencimento1')
                }
            }else {
                if(document.form.txt_data_emissao.value != '') nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento1', 'document.form.txt_vencimento1')
            }
        }
    }else if(valor == 2) {//Vencimento 2
        if(document.form.txt_vencimento2.value == '') {
            document.form.txt_data_vencimento2.value = ''
        }else {
/*Se o Cliente for Internacional e a Nota Fiscal for uma Nota de Saída, então os prazos são 
acrescentados em cima da Data do B/L*/
            if(id_pais != 31 && tipo_nfe_nfs == 'S') {
                if(document.form.txt_data_bl.value != '') {
                    nova_data('document.form.txt_data_bl', 'document.form.txt_data_vencimento2', 'document.form.txt_vencimento2')
                }else {//Caso não esteje preenchido este campo, me baseio no da Data de Emissão ...
                    nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento2', 'document.form.txt_vencimento2')
                }	
            }else {
                if(document.form.txt_data_emissao.value != '') nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento2', 'document.form.txt_vencimento2')
            }
        }
    }else if(valor == 3) {//Vencimento 3
        if(document.form.txt_vencimento3.value == '') {
            document.form.txt_data_vencimento3.value = ''
        }else {
/*Se o Cliente for Internacional e a Nota Fiscal for uma Nota de Saída, então os prazos são 
acrescentados em cima da Data do B/L*/
            if(id_pais != 31 && tipo_nfe_nfs == 'S') {
                if(document.form.txt_data_bl.value != '') {
                    nova_data('document.form.txt_data_bl', 'document.form.txt_data_vencimento3', 'document.form.txt_vencimento3')
                }else {//Caso não esteje preenchido este campo, me baseio no da Data de Emissão ...
                    nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento3', 'document.form.txt_vencimento3')
                }
            }else {
                if(document.form.txt_data_emissao.value != '') nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento3', 'document.form.txt_vencimento3')
            }
        }
    }else if(valor == 4) {//Vencimento 4
        if(document.form.txt_vencimento4.value == '') {
            document.form.txt_data_vencimento4.value = ''
        }else {
/*Se o Cliente for Internacional e a Nota Fiscal for uma Nota de Saída, então os prazos são 
acrescentados em cima da Data do B/L*/
            if(id_pais != 31 && tipo_nfe_nfs == 'S') {
                if(document.form.txt_data_bl.value != '') {
                    nova_data('document.form.txt_data_bl', 'document.form.txt_data_vencimento4', 'document.form.txt_vencimento4')
                }else {//Caso não esteje preenchido este campo, me baseio no da Data de Emissão ...
                    nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento4', 'document.form.txt_vencimento4')
                }
            }else {
                if(document.form.txt_data_emissao.value != '') nova_data('document.form.txt_data_emissao', 'document.form.txt_data_vencimento4', 'document.form.txt_vencimento4')
            }
        }
    }
}
</Script>
</head>
<?
if($importado_financeiro == 'N') {//Se a NF não tiver importada ...
    $functions = 'if(document.form.txt_data_emissao.disabled == false) {document.form.txt_data_emissao.focus()}';
}
?>
<body onload='<?=$functions;?>'>
<form name='form' method='post' action='' onsubmit='return validar()'>
<!--**********Controles de Tela**********-->
<input type='hidden' name='id_nf' value='<?=$id_nf;?>'>
<input type='hidden' name='opcao' value='<?=$opcao;?>'>
<input type='hidden' name='acao' value='<?=$acao;?>'>
<input type='hidden' name='id_cliente' value='<?=$id_cliente;?>'>
<!--Caixa que faz o controle de contatos inclusos deste Cliente nessa Nota Fiscal-->
<input type='hidden' name='controle' onclick='verificar(1);verificar(2);verificar(3);verificar(4)'>
<input type='hidden' name='passo' onclick='atualizar()'>
<input type='hidden' name='hdd_status' value='<?=$status;?>'>
<input type='hidden' name='hdd_atualizar_destinatario_remetente'>
<!--*************************************-->
<table width='<?=$width;?>' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            DESTINATÁRIO / REMETENTE / FATURA
            <?
                /*Significa que essa Tela foi aberta somente p/ Modo Leitura e que a mesma foi 
                acessada do Menu Em Aberto / Liberadas ou Devolução ...*/
                if($acao == 'L' && ($opcao == 1 || $opcao == 4)) {
            ?>
            <img src = '../../../imagem/menu/alterar.png' border='0' onclick="nova_janela('destinatario_remetente_fatura.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>&acao=G', 'DESTINATARIO_REMETENTE_FATURA', '', '', '', '', '320', '750', 'c', 'c', '', '', 's', 's', '', '', '')" title='Alterar Dados Gerais' alt='Alterar Dados Gerais'>
            <?
                }
            ?>
        </td>
    </tr>
<?
	$sql = "SELECT c.`razaosocial`, c.`forma_pagamento`, c.`cidade`, p.`pais` 
                FROM `clientes` c 
                INNER JOIN `paises` p ON p.`id_pais` = c.`id_pais` 
                WHERE c.`id_cliente` = '$id_cliente' LIMIT 1 ";
	$campos_cliente	= bancos::sql($sql);
	$razaosocial                = $campos_cliente[0]['razaosocial'];
        $forma_pagamento_cliente    = $campos_cliente[0]['forma_pagamento'];
        $cidade                     = $campos_cliente[0]['cidade'];
	$pais                       = $campos_cliente[0]['pais'];
?>
    <tr class='linhanormal'>
        <td>
            <b>Cliente:</b>
        </td>
        <td>
        <?
            echo $razaosocial;
            if($optante_simples_nacional == 'S') echo '<font color="red"><b> (OPTANTE SIMPLES NACIONAL)</b></font>';
            echo ' - <font color="darkred" size="2"><b> (CRÉDITO '.$campos[0]['credito'].')</b></font>';
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>País / UF / Cidade:</b>
        </td>
        <td>
        <?
            echo $pais;
//Aqui busca o estado do Cliente, faço esse sql à parte para não dar erro no banco
            if($id_uf != 0) {
                $sql = "SELECT `sigla` 
                        FROM `ufs` 
                        WHERE `id_uf` = '$id_uf' LIMIT 1 ";
                $campos_uf = bancos::sql($sql);
                echo ' / '.$campos_uf[0]['sigla'];
            }
            if(!empty($cidade)) echo ' / '.$cidade;
        ?>
        </td>
    </tr>
<?
    if($trading == 1) {
?>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <font color='blue'>
                <b>É COMERCIAL EXPORTADOR (TRADING).</b>
            </font>
        </td>
    </tr>
<?
	}
/*************************************************************************************/
/***************************************Suframa***************************************/
/*************************************************************************************/
	if($suframa_nf > 0) {
?>
    <tr class='linhanormal'>
        <td>
            <font color='blue'>
                <b>TIPO / CÓDIGO SUFRAMA: </b>
            </font>
        </td>
        <td>
        <?
            $tipo_suframa_vetor[1] = 'Área de Livre Comércio (ICMS/IPI) / ';
            $tipo_suframa_vetor[2] = 'Zona Franca de Manaus (ICMS/PIS/COFINS/IPI) / ';
            $tipo_suframa_vetor[3] = 'Amazônia Ocidental (IPI) / ';

            echo '<font color="blue">'.$tipo_suframa_vetor[$suframa_nf].$cod_suframa.'</font>';
//Se o Suframa for Ativo, então exibo essa Mensagem de Ativo ao lado ...
            if($suframa_ativo_nf == 'S') echo ' <font color="red"><b>(ATIVO)</b></font>';
/*********************************Controle com os Textos de Suframa*********************************/
            if($suframa_nf == 1 && $suframa_ativo_nf == 'S') {//Área de Livre e o Cliente possui o Suframa Ativo ...
        ?>
                <b>(Desconto de ICMS = <?=number_format(genericas::variavel(40), 2, ',', '.');?> %)</b>
        <?
            }else if($suframa_nf == 2 && $suframa_ativo_nf == 'S') {//Zona Franca de Man...
        ?>
                <b>(Desconto de PIS + Cofins = <?=number_format((genericas::variavel(20)+genericas::variavel(21)), 2, ',', '.');?> % e ICMS = <?=number_format(genericas::variavel(40), 2, ',', '.');?> %)</b>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Situação do Suframa:
        </td>
        <td>
        <?
/*Se ainda não foi feita nenhuma confirmação no que se refere a Habilitação do Suframa, então essa Combo 
de Situação do Suframa virá travada ...*/
            $disabled_situacao_suframa = ($id_funcionario_suframa_nf == 0) ? 'disabled' : '';
        ?>
        <select name='cmb_situacao_suframa' title='Selecione a Situação do Suframa' class='<?=$class_combo;?>' <?=$disabled;?>>
        <?
            if($suframa_ativo_nf == 'S') {
                $selecteds = 'selected';
            }else {
                $selectedn = 'selected';
            }
        ?>
            <option value='S' <?=$selecteds;?>>HABILITADO</option>
            <option value='N' <?=$selectedn;?>>NÃO HABILITADO</option>
        </select>
        &nbsp;
        <!--Após clicar no Site do Suframa, então habilita a Combo de Situação de Suframa, p/ gravar no BD-->
        <a href='#' onclick="if(document.form.cmb_situacao_suframa.disabled == false) {nova_janela('http://www.suframa.gov.br/asp/sintegra_cadastro.asp', 'SUFRAMA', '', '', '', '', 500, 1000, 'c', 'c', '', '', 's', 's', '', '', '');document.form.cmb_situacao_suframa.disabled = false}" title='Consultar Site do Suframa' id='lnk_site_suframa' class='link'>
            Consultar Suframa (Site)
        </a>
        <?
//Só irá exibir essa informações após a Primeira Verificação no Site do Suframa ...
            if($id_funcionario_suframa_nf > 0) {
//Busca do Responsável que fez as Conferências no Site do Suframa ...
                $sql = "SELECT `login` 
                        FROM `funcionarios` f 
                        INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario` 
                        WHERE f.`id_funcionario` = '$id_funcionario_suframa_nf' LIMIT 1 ";
                $campos_login = bancos::sql($sql);
        ?>
                &nbsp;|&nbsp;
                <font color='darkblue'><b>Login: </b><font color='red'><?=$campos_login[0]['login'];?> - 
                <font color='darkblue'><b>Data e Hora: </b><font color='red'><?=data::datetodata(substr($data_sys_suframa_nf, 0, 10), '/').' - '.substr($data_sys_suframa_nf, 11, 8);?>
        <?
            }
        ?>
        </td>
    </tr>
<?
	}
/***************************************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            Data de Emissão:
        </td>
        <td>
            <input type='text' name='txt_data_emissao' value='<?=$data_emissao;?>' title='Data de Emissão' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='<?=$class;?>' <?=$disabled;?>>
            &nbsp;<img src = '../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="if(document.form.txt_data_emissao.disabled == false) {nova_janela('../../../calendario/calendario.php?campo=txt_data_emissao&tipo_retorno=1&caixa_auxiliar=controle', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')}">&nbsp;Calend&aacute;rio
        </td>
    </tr>
<?
//Aqui eu já tenho o cálculo para o valor das duplicatas
        $valor_duplicata = faturamentos::valor_duplicata($id_nf, $suframa_nf, $nota_sgd, $id_pais);
        
//Verifica qual é o País para poder imprimir os símbolos corretos de R$
        if($id_pais != 31) {
            $tipo_moeda = 'U$';
/*Função que será utilizada somente quando o Cliente for estrangeiro, pois nesse caso, 
nós gravamos o valor da Duplicata em U$ na Base de Dados, daí passo o Valor da NF em
reais e lá dentro da Função ele divide pelo número de Vencimentos ...*/
            $valor_duplicata_rs = faturamentos::valor_duplicata_rs($valor_total_nota, $qtde_duplicatas);
        }else {
            $tipo_moeda = 'R$';
        }
/*******************************************************************************************/
/*Se essa variável retornar 0, então retorno os Itens que estão com o Peso Unitário zerado 
e que estão influenciando no cálculo errado da NF - geralmente acontece com as NF(s) mais 
antigas, pois a partir de agora, o Sistema cerca com essa segurança no Incluir Itens ...*/
        if($calculo_total_impostos['peso_lote_total_kg'] == 0) echo faturamentos::itens_nf_peso_unitario_zerado($id_nf);
/*******************************************************************************************/
?>
    <tr class='linhanormal'>
        <td>
            Vencimento 1:
        </td>
        <td>
            <input type='text' name='txt_vencimento1' value='<?=$vencimento1;?>' title='Digite o Vencimento 1' size='5' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(1)" class='<?=$class_vencimentos;?>' <?=$disabled_vencimentos;?>>
            DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_vencimento1' value='<?=$data_vencimento1;?>' title='Data do Vencimento 1' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;<?=$tipo_moeda;?>&nbsp;<?=number_format($valor_duplicata[0], 2, ',', '.');?>
            <?
                if($id_pais != 31) echo '<b> / R$ '.number_format($valor_duplicata_rs[0], 2, ',', '.').'</b>';
                if($status == 0 || $status == 6) {//Somente no status de "Em Aberto" 0 ou "NF de Devolução" 6 que mostro essa frase abaixo ...
            ?>
            &nbsp;
            <font color='red'>
                <b>Salve p/ recalcular o Valor da(s) Duplicata(s)</b>
            </font>
            <?
                }
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Vencimento 2:
        </td>
        <td>
            <input type='text' name='txt_vencimento2' value='<?=$vencimento2;?>' title='Digite o Vencimento 2' size='5' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(2)" class='<?=$class_vencimentos;?>' <?=$disabled_vencimentos;?>>
            DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_vencimento2' value='<?=$data_vencimento2;?>' title='Data do Vencimento 2' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;<?=$tipo_moeda;?>&nbsp;<?=number_format($valor_duplicata[1], 2, ',', '.');?>
            <?
                if($id_pais != 31) echo '<b> / R$ '.number_format($valor_duplicata_rs[1], 2, ',', '.').'</b>';
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Vencimento 3:
        </td>
        <td>
            <input type='text' name='txt_vencimento3' value='<?=$vencimento3;?>' title='Digite o Vencimento 3' size='5' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(3)" class='<?=$class_vencimentos;?>' <?=$disabled_vencimentos;?>>
            DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_vencimento3' value='<?=$data_vencimento3;?>' title='Data do Vencimento 3' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;<?=$tipo_moeda;?>&nbsp;<?=number_format($valor_duplicata[2], 2, ',', '.');?>
            <?
                if($id_pais != 31) echo '<b> / R$ '.number_format($valor_duplicata_rs[2], 2, ',', '.').'</b>';
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Vencimento 4:
        </td>
        <td>
            <input type='text' name='txt_vencimento4' value='<?=$vencimento4;?>' title='Digite o Vencimento 4' size='5' maxlength='3' onkeyup="verifica(this, 'aceita', 'numeros', '', event);verificar(4)" class='<?=$class_vencimentos;?>' <?=$disabled_vencimentos;?>>
            DIAS &nbsp;&nbsp;
            <input type='text' name='txt_data_vencimento4' value='<?=$data_vencimento4;?>' title='Data do Vencimento 4' size='12' maxlength='10' class='textdisabled' disabled>
            &nbsp;<?=$tipo_moeda;?>&nbsp;<?=number_format($valor_duplicata[3], 2, ',', '.');?>
            <?
                if($id_pais != 31) echo '<b> / R$ '.number_format($valor_duplicata_rs[3], 2, ',', '.').'</b>';
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Prazo Médio:
        </td>
        <td>
            <?=$prazo_medio;?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Forma de Pagamento:</b>
        </td>
        <td>
            <select name='cmb_forma_pagamento' title='Selecione a Forma de Pagamento' class='<?=$class_combo;?>' <?=$disabled;?>>
                <?
                    if($forma_pagamento == 0) $selected0 = 'selected';
                ?>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0' style='color:red' <?=$selected0;?>>ANÁLISE FINANCEIRO</option>
                <?
                    //Significa que ainda não foi escolhida nenhuma Forma de Pagamento, então sugiro a que estão no cadastro do Cliente ...
                    if(empty($forma_pagamento)) $forma_pagamento = $forma_pagamento_cliente;
                    
                    $vetor_forma_pagamento  = array_sistema::forma_pagamento();
                    foreach($vetor_forma_pagamento as $indice => $rotulo) {
                        $selected = (!empty($forma_pagamento) && $forma_pagamento == $indice) ? 'selected' : '';
                        echo "<option value='$indice' $selected>".$indice.' - '.$rotulo."</option>";
                    }
                ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <?
                if($acao == 'G') {//Significa que essa Tela foi aberta como Modo Gravação ...
            ?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR');document.form.cmb_cliente_transportadora.focus()" class='botao'>
            <?
                    //Roberto 62 e Dárcio 98 porque programa ...
                    $vetor_usuarios_com_acesso = array(62, 98);
            
                    /*Este botão mostrará somente quando a Nota Fiscal estiver no status de "Liberada p/ Faturar" e 
                    p/ os seguintes usuários do array ...*/
                    if($status == 1 && in_array($_SESSION['id_funcionario'], $vetor_usuarios_com_acesso)) {
            ?>
            <input type='button' name='cmd_alterar_valores_duplicatas' value='Alterar Valores de Duplicatas' title='Alterar Valores de Duplicatas' style='color:blue' onclick="nova_janela('alterar_valores_duplicatas.php?id_nf=<?=$id_nf;?>&opcao=<?=$opcao;?>', 'ALTERAR_VALORES_DUPLICATAS', '', '', '', '', '250', '680', 'c', 'c', '', '', 's', 's', '', '', '')" class='botao'>
            <?
                    }
            ?>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='fechar(window)' style='color:red' class='botao'>
            <?
                }else {
                    echo '&nbsp;';
                }
            ?>
        </td>
    </tr>
</table>
</form>
</body>
</html>