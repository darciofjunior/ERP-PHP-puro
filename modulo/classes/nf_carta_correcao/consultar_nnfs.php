<?
$campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
$linhas = count($campos);
if($linhas == 0) {
?>
    <Script Language = 'Javascript'>
        window.location = 'incluir.php?valor=1'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar Nota(s) Fiscal(is) de Saída ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function avancar(id_nf, id_nf_outra, id_carta_correcao) {
    if(id_carta_correcao > 0) {
        var resposta = confirm('JÁ FOI GERADA UMA CARTA DE CORREÇÃO P/ ESTA NF !\n DESEJA EDITAR ESTA CARTA ?')
        if(resposta == true) {
            window.location = 'itens/consultar.php?passo=1&id_carta_correcao='+id_carta_correcao
        }else {
            return false
        }
    }else {
        var resposta = confirm('DESEJA GERAR UMA CARTA DE CORREÇÃO P/ ESTA NF ?')
        if(resposta == true) window.location = 'incluir.php?passo=2&id_nf='+id_nf+'&id_nf_outra='+id_nf_outra
    }
}
</Script>
</head>
<body>
<table width='70%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr class="linhacabecalho" align="center">
        <td colspan='8'>
            Consultar Nota(s) Fiscal(is) de Saída
        </td>
    </tr>
    <tr class="linhadestaque" align="center">
        <td colspan='2'>
            N.º NF(s)
        </td>
        <td>
            Data Em.
        </td>
        <td>
            Cliente
        </td>
        <td>
            Transportadora
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
//Vetor para Auxiliar as Identificações de Follow-UP, que busca de outro arquivo
    $vetor = array_sistema::nota_fiscal();
    $tipo_despacho = array('', 'PORTARIA', 'TRANSPORTADORA', 'NOSSO CARRO', 'RETIRA', 'CORREIO/SEDEX', 'FINANCEIRO');
    for($i = 0;  $i < $linhas; $i++) {
        $id_carta_correcao = 0;//Sempre limpa essa variável p/ não herdar o valor do loop anterior ...
/*Obs: O Union retorna o "id_nf_outra" e o "id_nf" como sendo um único campo, que no caso está sendo "id_nf", 
daí para distinguir de uma tabela com outra, eu joguei um "|" na Frente do campo id_nf_outra ...*/
//Verifico o Tipo de Nota Fiscal que está sendo listada dentro do Loop ...
            if(substr($campos[$i]['id_nf'], 0, 1) == '|') {//Significa que está sendo listada uma NF Outra(s) no Loop ...
                $id_nf_outra = substr($campos[$i]['id_nf'], 1, strlen($campos[$i]['id_nf']));
//Aqui eu verifico se já foi feita alguma Carta de Correção p/ esta NF ...
                $sql = "SELECT id_carta_correcao 
                        FROM `cartas_correcoes` 
                        WHERE `id_nf_outra` = '$id_nf_outra' LIMIT 1 ";
                $campos_carta_correcao = bancos::sql($sql);
                if(count($campos_carta_correcao) == 1) $id_carta_correcao = $campos_carta_correcao[0]['id_carta_correcao'];
            }else {//Significa que está sendo acessada uma NF de Venda / Devolução no Loop ...
                $id_nf = $campos[$i]['id_nf'];
//Aqui eu verifico se já foi feita alguma Carta de Correção p/ esta NF ...
                $sql = "SELECT id_carta_correcao 
                        FROM `cartas_correcoes` 
                        WHERE `id_nf` = '$id_nf' LIMIT 1 ";
                $campos_carta_correcao = bancos::sql($sql);
                if(count($campos_carta_correcao) == 1) $id_carta_correcao = $campos_carta_correcao[0]['id_carta_correcao'];
            }
?>
    <tr class="linhanormal" onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align="center">
        <td onclick="javascript:avancar('<?=$id_nf;?>', '<?=$id_nf_outra;?>', '<?=$id_carta_correcao;?>')" width='10'>
            <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td onclick="javascript:avancar('<?=$id_nf;?>', '<?=$id_nf_outra;?>', '<?=$id_carta_correcao;?>')">
            <a href="javascript:avancar('<?=$id_nf;?>', '<?=$id_nf_outra;?>', '<?=$id_carta_correcao;?>')" class='link'>
            <?
/**************************************NF Outras*****************************************/
                if(substr($campos[$i]['id_nf'], 0, 1) == '|') {//Significa que está sendo listada uma NF Outra(s) no Loop ...
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
            if($campos[$i]['data_emissao'] != '0000-00-00') echo data::datetodata($campos[$i]['data_emissao'], '/');
        ?>
        </td>
        <td align="left">
            <font title="Nome Fantasia: <?=$campos[$i]['nomefantasia'];?>" style="cursor:help">
                <?=$campos[$i]['razaosocial'];?>
            </font>
        </td>
        <td>
            <?=$campos[$i]['transportadora'];?>
        </td>
        <td align="left">
        <?
            echo $vetor[$campos[$i]['status']];
            if($campos[$i]['status'] == 4) echo ' ('.$tipo_despacho[$campos[$i]['tipo_despacho']].')';
        ?>
        </td>
        <td align="left">
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
            if($campos[$i]['vencimento3'] > 0) $prazo_faturamento = '/'.$campos[$i]['vencimento3'].$prazo_faturamento;
            if($campos[$i]['vencimento2'] > 0) {
                $prazo_faturamento = $campos[$i]['vencimento1'].'/'.$campos[$i]['vencimento2'].$prazo_faturamento;
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
    <tr class="linhacabecalho" align='center'>
        <td colspan='8'>
            <input type="button" name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class="botao">
        </td>
    </tr>
</table>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?}?>