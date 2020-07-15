<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/faturamentos.php');
require('../../../../lib/genericas.php');
require('../../../../lib/vendas.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";

if($passo == 1) {
    $valor_dolar_nota 	= genericas::moeda_dia('dolar');
    $data_sys           = date('Y-m-d H:i:s');
    /*Verifico se existe pelo menos 1 NF de Devolução do Cliente que esteja sem Itens 
    e que não esteja com a Devolução Liberada ...*/
    $sql = "SELECT id_nf 
            FROM `nfs` 
            WHERE `id_cliente` = '$_GET[id_cliente]' 
            AND `status` = '6' 
            AND `devolucao_faturada` = 'N' 
            AND id_nf NOT IN 
            (SELECT DISTINCT(nfs.id_nf) 
            FROM nfs 
            INNER JOIN nfs_itens nfsi ON nfsi.id_nf = nfs.id_nf 
            WHERE nfs.id_cliente = '$_GET[id_cliente]') 
            ORDER BY id_nf LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 1) {//Significa que já existe uma NF em aberto sem Itens, sendo assim eu só reaproveita esta ...
        $id_nf = $campos[0]['id_nf'];
    }else {//Ainda não existe nenhuma NF sem Itens, sendo assim eu vou gerar uma NF ...
        vendas::transportadoras_padroes($_GET['id_cliente']);//Verifico se o cliente possui as transportadoras padrões cadastradas ...
        
        //Busca uma Transportadora qualquer do Cliente para gerar a NF ...
        $sql = "SELECT `id_transportadora` 
                FROM `clientes_vs_transportadoras` 
                WHERE `id_cliente` = '$_GET[id_cliente]' LIMIT 1 ";
        $campos_transportadoras = bancos::sql($sql);

        //Gerando NF de Devolução ...
        $sql = "INSERT INTO `nfs` (`id_nf`, `id_funcionario`, `id_cliente`, `id_empresa`, `id_transportadora`, `finalidade`, `frete_transporte`, `tipo_nfe_nfs`, `natureza_operacao`, `valor_dolar_dia`, `data_sys`, `status`) VALUES (NULL, '$_SESSION[id_funcionario]', '$_GET[id_cliente]', '4', '".$campos_transportadoras[0]['id_transportadora']."', 'R', '2', 'E', 'DEV', '$valor_dolar_nota', '$data_sys', '6') ";
        bancos::sql($sql);
        $id_nf                  = bancos::id_registro();
        $id_nf_num_nota_novo    = faturamentos::gerar_numero_nf(4);
        
        //Atualizo a NF de Devolução gerada com o Nº SGD que foi gerado acima ...
        $sql = "UPDATE `nfs` SET `id_nf_num_nota` = '$id_nf_num_nota_novo' WHERE `id_nf` = '$id_nf' LIMIT 1 ";
        bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>    
        alert('A EMPRESA GERADA PARA ESTA NF FOI COMO SENDO "GRUPO" - CERTIFIQUE-SE DE QUE ESTÁ ESTEJA CORRETA !')
    </Script>
<?
    }
?>
    <Script Language = 'JavaScript'>
        //Redireciona o usuário p/ a Tela de Itens no menu de Devolução p/ o usuário começar a Importar os Itens ...
        window.location = '../itens/index.php?id_nf=<?=$id_nf;?>&opcao=4'
    </Script>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
 requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../../../';
    $qtde_por_pagina = 50;
    //Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('../../../classes/cliente/tela_geral_filtro.php');
    if($linhas > 0) {//Se retornar pelo menos 1 registro
?>
<html>
<head>
<title>.:: Consultar Cliente(s) p/ Incluir NF de Devolução ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function prosseguir(id_cliente) {
    var resposta = confirm('DESEJA EMITIR UMA NOTA FISCAL DE DEVOLUÇÃO PARA ESSE CLIENTE ?')
    if(resposta == true) window.location = 'incluir.php?passo=1&id_cliente='+id_cliente
}
</Script>
</head>
<body>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover="total_linhas(this)">
    <tr></tr>
    <tr class="linhacabecalho" align="center">
        <td colspan='6'>
            Consultar Cliente(s) p/ Incluir NF de Devolução
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Razão Social
        </td>
        <td>
            Nome Fantasia
        </td>
        <td>
            Tp Cliente
        </td>
        <td>
            Duplicatas
        </td>
        <td>
            CNPJ / CPF
        </td>
    </tr>
<?
        for ($i = 0;  $i < $linhas; $i++) {
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td onclick="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>')" width="10">
            <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <a href="javascript:prosseguir('<?=$campos[$i]['id_cliente'];?>')" class="link">
                <?=$campos[$i]['razaosocial'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
        <td align="center">
            <?=$campos[$i]['tipo'];?>
        </td>
        <td align="center">
        <?
            $sql = "SELECT id_conta_receber 
                    FROM `contas_receberes` 
                    WHERE `id_cliente` = '".$campos[$i]['id_cliente']."' LIMIT 1 ";
            $campos_contas_receberes = bancos::sql($sql);
            if(count($campos_contas_receberes) == 1) {
                echo 'SIM';
            }else {
                echo 'NÃO';
            }
        ?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['cnpj_cpf'])) {//Campo está preenchido ...
                if(strlen($campos[$i]['cnpj_cpf']) == 11) {//CPF ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 3).'.'.substr($campos[$i]['cnpj_cpf'], 3, 3).'.'.substr($campos[$i]['cnpj_cpf'], 6, 3).'-'.substr($campos[$i]['cnpj_cpf'], 9, 2);
                }else {//CNPJ ...
                    echo substr($campos[$i]['cnpj_cpf'], 0, 2).'.'.substr($campos[$i]['cnpj_cpf'], 2, 3).'.'.substr($campos[$i]['cnpj_cpf'], 5, 3).'/'.substr($campos[$i]['cnpj_cpf'], 8, 4).'-'.substr($campos[$i]['cnpj_cpf'], 12, 2);
                }
            }
        ?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='6'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'incluir.php'" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<p class='piscar'>
    <font color='red'>
        NÃO SE ESQUEÇA DE VERIFICAR O CNPJ DO CLIENTE !!!
    </font>
</p>
&nbsp;
<!--Tenho q colocar a função depois, pq senão não é reconhecida a Tag "P" que foi criada antes usando o atributo Piscar ...-->
<Script Language = 'JavaScript'>
    function blink(selector) {
        $(selector).fadeOut('slow', function() {
            $(this).fadeIn('slow', function() {
                blink(this);
            });
        });
    }
    blink('.piscar');
</Script>
<?
    }
}
?>