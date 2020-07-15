<?
require('../../../../../lib/segurancas.php');
if(empty($_GET['pop_up'])) require('../../../../../lib/menu/menu.php');//Significa que essa Tela foi aberta de modo Normal, ent„o exibo o Menu ...
require('../../../../../lib/data.php');
require('../../../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA N√O RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>FAMÕLIA ALTERADA COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>FAMÕLIA J¡ EXISTENTE.</font>";

$meta_vendas_desejada = genericas::variavel(77);

if($passo == 1) {
    switch($opt_opcao) {
        case 1:
            $sql = "SELECT f.*, cf.`classific_fiscal`, l.`login` 
                    FROM `familias` f 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                    LEFT JOIN `logins` l ON l.`id_login` = f.`id_login_gerente` 
                    WHERE f.`nome` LIKE '%$txt_consultar%' 
                    AND f.`ativo` = '1' ORDER BY f.`nome` ";
        break;
        default:
            $sql = "SELECT f.*, cf.`classific_fiscal`, l.`login` 
                    FROM `familias` f 
                    INNER JOIN `classific_fiscais` cf ON cf.`id_classific_fiscal` = f.`id_classific_fiscal` 
                    LEFT JOIN `logins` l ON l.`id_login` = f.`id_login_gerente` 
                    WHERE f.`ativo` = '1' ORDER BY f.`nome` ";
        break;
    }
    $campos = bancos::sql($sql, $inicio, 30, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
	<Script Language = 'Javascript'>
		window.location = 'alterar.php?valor=1'
	</Script>
<?
    }else {
?>
<html>
<head>
<title>.:: FamÌlia(s) p/ Alterar::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
</head>
<body>
<table width='95%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            FamÌlia(s) p/ Alterar
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='4' align='left'>
            FamÌlia(s) p/ Alterar
            Total Meta de Vendas Desejada: 
            <font color='yellow'>
                R$ <?=number_format($meta_vendas_desejada, 2, ',', '.');?>
            </font>
        </td>
        <td align='right'>
            <font color='yellow'>
            <?
                //Aqui eu busco o Total de Meta Mensal de Vendas ...
                $sql = "SELECT SUM(`meta_mensal_vendas`) AS total_meta_mensal_vendas 
                        FROM `familias` 
                        WHERE `ativo` = '1' ";
                $campos_familia                             = bancos::sql($sql);
                $total_meta_mensal_vendas_todas_familias    = $campos_familia[0]['total_meta_mensal_vendas'];

                echo number_format($total_meta_mensal_vendas_todas_familias, 2, ',', '.');
            ?>
            </font>
        </td>
        <td align='right'>
            Fator Corr = 
            <font color='yellow'>
            <?
                $total_meta_mensal_vendas_todas_familias_corr = ($meta_vendas_desejada / $total_meta_mensal_vendas_todas_familias * 100);
                echo number_format($total_meta_mensal_vendas_todas_familias_corr, 1, ',', '.').' %';
            ?>
            </font>
        </td>
        <td>
            &nbsp;
        </td>
        <td colspan='4'>
            MÈdia de Vendas ⁄ltimos X meses
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            FamÌlia
        </td>
        <td>
            ClassificaÁ„o Fiscal
        </td>
        <td>
            Gerente da Linha
        </td>
        <td>
            <font title='Esta meta È a que imaginamos ser potencialmente possÌvel' style='cursor:help'>
                Meta Mensal <br/>de Vendas
            </font>
        </td>
        <td>
            Meta Mensal <br/>de Vendas Corr
        </td>
        <td>
            ObservaÁ„o
        </td>
        <td>
            36
        </td>
        <td>
            24
        </td>
        <td>
            12
        </td>
        <td>
            6
        </td>
    </tr>
<?
        $data_inicial_36meses   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -1095), '-');
        $data_inicial_24meses   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -730), '-');
        $data_inicial_12meses   = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -365), '-');
        $data_inicial_6meses    = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -183), '-');
        
        //Aqui eu busco tudo o que foi vendido nos ˙ltimos 36 meses ...
        $sql = "SELECT gpa.`id_familia`, pv.`data_emissao`, (pvi.`qtde` * pvi.`preco_liq_final`) AS total_item 
                FROM `pedidos_vendas_itens` pvi 
                INNER JOIN `produtos_acabados` pa ON pa.`id_produto_acabado` = pvi.`id_produto_acabado` 
                INNER JOIN `gpas_vs_emps_divs` ged ON ged.`id_gpa_vs_emp_div` = pa.`id_gpa_vs_emp_div` 
                INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                INNER JOIN `pedidos_vendas` pv ON pv.`id_pedido_venda` = pvi.`id_pedido_venda` AND pv.`data_emissao` >= '$data_inicial_36meses' 
                ORDER BY pv.`data_emissao` DESC ";
        $campos_pedidos_vendas = bancos::sql($sql);
        $linhas_pedidos_vendas = count($campos_pedidos_vendas);
        for($i = 0; $i < $linhas_pedidos_vendas; $i++) {//Dentro desse Total, eu vou fatiando por PerÌodo ...
            if($campos_pedidos_vendas[$i]['data_emissao'] >= $data_inicial_36meses) {
                $vetor_total_venda_rs_36meses[$campos_pedidos_vendas[$i]['id_familia']]+= $campos_pedidos_vendas[$i]['total_item'];
                $total_venda_rs_36meses+= $campos_pedidos_vendas[$i]['total_item'];
            }
            if($campos_pedidos_vendas[$i]['data_emissao'] >= $data_inicial_24meses) {
                $vetor_total_venda_rs_24meses[$campos_pedidos_vendas[$i]['id_familia']]+= $campos_pedidos_vendas[$i]['total_item'];
                $total_venda_rs_24meses+= $campos_pedidos_vendas[$i]['total_item'];
            }
            if($campos_pedidos_vendas[$i]['data_emissao'] >= $data_inicial_12meses) {
                $vetor_total_venda_rs_12meses[$campos_pedidos_vendas[$i]['id_familia']]+= $campos_pedidos_vendas[$i]['total_item'];
                $total_venda_rs_12meses+= $campos_pedidos_vendas[$i]['total_item'];
            }
            if($campos_pedidos_vendas[$i]['data_emissao'] >= $data_inicial_6meses) {
                $vetor_total_venda_rs_6meses[$campos_pedidos_vendas[$i]['id_familia']]+= $campos_pedidos_vendas[$i]['total_item'];
                $total_venda_rs_6meses+= $campos_pedidos_vendas[$i]['total_item'];
            }
        }
        $media_venda_rs_36meses = ($total_venda_rs_36meses / 36);
        $media_venda_rs_24meses = ($total_venda_rs_24meses / 24);
        $media_venda_rs_12meses = ($total_venda_rs_12meses / 12);
        $media_venda_rs_6meses = ($total_venda_rs_6meses / 6);

        $total_media_venda_rs_36meses+= $media_venda_rs_36meses;
        $total_media_venda_rs_24meses+= $media_venda_rs_24meses;
        $total_media_venda_rs_12meses+= $media_venda_rs_12meses;
        $total_media_venda_rs_6meses+= $media_venda_rs_6meses;

        for($i = 0;  $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF');window.location = 'alterar.php?passo=2&id_familia=<?=$campos[$i]['id_familia'];?>'" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td width='10'>
            <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <a href='alterar.php?passo=2&id_familia=<?=$campos[$i]['id_familia'];?>' class='link'>
                <?=$campos[$i]['nome'];?>
            </a>
        </td>
        <td>
            <?=$campos[$i]['classific_fiscal'];?>
        </td>
        <td>
            <font color='darkblue'>
                <b><?=strtoupper($campos[$i]['login']);?></b>
            </font>
        </td>
        <td align='right'>
            <?=number_format($campos[$i]['meta_mensal_vendas'], 2, ',', '.');?>
        </td>
        <td align='right'>
        <?
            $meta_mensal_vendas_corr = $meta_vendas_desejada / $total_meta_mensal_vendas_todas_familias * $campos[$i]['meta_mensal_vendas'];
            echo number_format($meta_mensal_vendas_corr, 2, ',', '.');
        ?>
        </td>
        <td>
        <?
            if(!empty($campos[$i]['observacao'])) {
        ?>
            <img width='28' height='23' title='<?=$campos[$i]['observacao'];?>' src = '../../../../../imagem/olho.jpg'>
        <?                
            }
        ?>
        </td>
        <td align='right'>
            R$ <?=number_format($vetor_total_venda_rs_36meses[$campos[$i]['id_familia']] / 36, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($vetor_total_venda_rs_24meses[$campos[$i]['id_familia']] / 24, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($vetor_total_venda_rs_12meses[$campos[$i]['id_familia']] / 12, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($vetor_total_venda_rs_6meses[$campos[$i]['id_familia']] / 6, 2, ',', '.');?>
        </td>
    </tr>
<?
            $total_meta_mensal_vendas_familias_filtradas+=      $campos[$i]['meta_mensal_vendas'];
            $total_meta_mensal_vendas_corr_familias_filtradas+= $meta_mensal_vendas_corr;
        }
?>
    <tr>
        
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='4'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
        </td>
        <td align='right'>
            R$ <?=number_format($total_meta_mensal_vendas_familias_filtradas, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($total_meta_mensal_vendas_corr_familias_filtradas, 2, ',', '.');?>
        </td>
        <td>
            &nbsp;
        </td>
        <td align='right'>
            R$ <?=number_format($total_media_venda_rs_36meses, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($total_media_venda_rs_24meses, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($total_media_venda_rs_12meses, 2, ',', '.');?>
        </td>
        <td align='right'>
            R$ <?=number_format($total_media_venda_rs_6meses, 2, ',', '.');?>
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
}else if($passo == 2) {
//Aqui eu trago dados da FamÌlia passada por par‚metro ...	
    $sql = "SELECT * 
            FROM `familias` 
            WHERE `id_familia` = '$_GET[id_familia]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<title>.:: Alterar FamÌlia ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//ClassificaÁ„o Fiscal
    if(!combo('form', 'cmb_classificacao_fiscal', '', 'SELECIONE A CLASSIFICA«√O FISCAL !')) {
        return false
    }
//Metal Mensal de Vendas ...
    if(document.form.cmb_login_gerente.value != '') {//Se tiver um Gerente selecionado, forÁa preencher a Meta ...
        if(!texto('form', 'txt_meta_mensal_vendas', '4', '0123456789,.', 'META MENSAL DE VENDAS', '1')) {
            return false
        }
    }
//FamÌlia
    if(!texto('form', 'txt_familia', '1', 'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿‚ÍÓÙ˚¬ Œ‘€ "1234567890/', 'FAMÕLIA', '1')) {
        return false
    }
//FamÌlia em InglÍs
    if(document.form.txt_familia_ingles.value != '') {
        if(!texto('form', 'txt_familia_ingles', '1', 'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿‚ÍÓÙ˚¬ Œ‘€ "1234567890/', 'FAMÕLIA EM INGL S', '1')) {
            return false
        }
    }
//FamÌlia em Espanhol
    if(document.form.txt_familia_espanhol.value != '') {
        if(!texto('form', 'txt_familia_espanhol', '1', 'abcdefghijkÁ«lmnopqrstuvwxyz ABCDEFGHIJKLMNOPQRSTUVWXYZ ¡…Õ”⁄·ÈÌÛ˙„ı√’‡¿‚ÍÓÙ˚¬ Œ‘€ "1234567890/', 'FAMÕLIA EM ESPANHOL', '1')) {
            return false
        }
    }
    return limpeza_moeda('form', 'txt_meta_mensal_vendas, ')
}
</Script>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit='return validar()'>
<input type='hidden' name='id_familia' value="<?=$_GET[id_familia];?>">
<table width='60%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar FamÌlia
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>ClassificaÁ„o Fiscal:</b>
        </td>
        <td>
            <select name='cmb_classificacao_fiscal' title='Selecione uma ClassificaÁ„o Fiscal' class='combo'>
            <?
                $sql = "SELECT `id_classific_fiscal`, `classific_fiscal` 
                        FROM `classific_fiscais` 
                        WHERE `ativo` = '1' ORDER BY `classific_fiscal` ";
                echo combos::combo($sql, $campos[0]['id_classific_fiscal']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Gerente da Linha:
        </td>
        <td>
            <select name='cmb_login_gerente' title='Selecione um Gerente da Linha' class='combo'>
            <?
                $sql = "SELECT id_login, login 
                        FROM `logins` 
                        ORDER BY login ";
                echo combos::combo($sql, $campos[0]['id_login_gerente']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Meta Mensal de Vendas:
        </td>
        <td>
            <input type='text' name='txt_meta_mensal_vendas' value="<?=number_format($campos[0]['meta_mensal_vendas'], 2, ',', '.');?>" title='Digite a Meta Mensal de Vendas' onkeyup="verifica(this, 'moeda_especial', '2', '', event)" size="15" maxlength="12" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>FamÌlia:</b>
        </td>
        <td>
            <input type='text' name="txt_familia" value="<?=$campos[0]['nome'];?>" title="Digite a FamÌlia" size="40" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            FamÌlia em InglÍs:
        </td>
        <td>
            <input type='text' name="txt_familia_ingles" value="<?=$campos[0]['nome_ing'];?>" title="Digite a FamÌlia em InglÍs" size="38" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            FamÌlia em Espanhol:
        </td>
        <td>
            <input type='text' name="txt_familia_espanhol" value="<?=$campos[0]['nome_esp'];?>" title="Digite a FamÌlia em Espanhol" size="38" maxlength="50" class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            ObservaÁ„o:
        </td>
        <td>
            <textarea name='txt_observacao' cols='85' rows='1' maxlength='85' title="Digite a ObservaÁ„o" class='caixadetexto'><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <?
                if(empty($_GET['pop_up'])) {//Significa que essa Tela foi aberta de modo Normal, ent„o exibo os Botıes abaixo ...
            ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
            <?
                }
            ?>
            &nbsp;
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 3) {
    //Verifico se j· foi cadastrada alguma famÌlia com o mesmo nome, mas diferente da famÌlia atual que estou alterando ...
    $sql = "SELECT id_familia 
            FROM `familias` 
            WHERE `nome` = '$_POST[txt_familia]' 
            AND `id_familia` <> '$_POST[id_familia]' 
            AND `ativo` = 1 LIMIT 1 ";
    $campos = bancos::sql($sql);
    if(count($campos) == 0) {
        $sql = "UPDATE `familias` SET `id_classific_fiscal` = '$_POST[cmb_classificacao_fiscal]', `id_login_gerente` = '$_POST[cmb_login_gerente]', `meta_mensal_vendas` = '$_POST[txt_meta_mensal_vendas]', `nome` = '$_POST[txt_familia]', `nome_ing` = '$_POST[txt_familia_ingles]', `nome_esp` = '$_POST[txt_familia_espanhol]', `observacao` = '$_POST[txt_observacao]' WHERE `id_familia` = '$_POST[id_familia]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 2;
    }else {
        $valor = 3;
    }
?>
    <Script Language = 'Javascript'>
        window.location = 'alterar.php<?=$parametro;?>&valor=<?=$valor;?>'
    </Script>
<?
}else {
?>
<html>
<head>
<title>.:: Consultar FamÌlia(s) p/ Alterar ::.</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'>
<meta http-equiv='cache-control' content='no-store'>
<meta http-equiv='pragma' content='no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function limpar() {
    document.form.txt_consultar.value = ''
    if(document.form.opcao.checked == true) {
        document.form.opt_opcao.disabled        = true
        document.form.txt_consultar.disabled    = true
        document.form.txt_consultar.className   = 'textdisabled'
    }else {
        document.form.opt_opcao.disabled        = false
        document.form.txt_consultar.disabled    = false
        document.form.txt_consultar.className   = 'caixadetexto'
        document.form.txt_consultar.focus()
    }
}

function validar() {
//Consultar
    if(document.form.txt_consultar.disabled == false) {
        if(document.form.txt_consultar.value == '') {
            alert('DIGITE O CAMPO CONSULTAR !')
            document.form.txt_consultar.focus()
            return false
        }
    }
}
</Script>
</head>
<body onload='document.form.txt_consultar.focus()'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=1';?>' onsubmit='return validar()'>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar FamÌlia(s) p/ Alterar
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td colspan='2'>
            Consultar <input type='text' name='txt_consultar' size='45' maxlength='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='20%'>
            <input type='radio' name='opt_opcao' value='1' onclick='document.form.txt_consultar.focus()' title='Consultar FamÌlia por: FamÌlia' id='label' checked>
            <label for='label'>FamÌlia</label>
        </td>
        <td width='20%'>
            <input type='checkbox' name='opcao' onclick='limpar()' value='1' title='Consultar todos as FamÌlias' class='checkbox' id='label2'>
            <label for='label2'>Todos os registros</label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.opcao.checked = false;limpar()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>