<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/producao/cadastros/produto_acabado/grupo_pa/consultar.php', '../../../../../');

$sql = "SELECT * 
        FROM `grupos_pas` 
        WHERE `id_grupo_pa` = '$_GET[id_grupo_pa]' LIMIT 1 ";
$campos = bancos::sql($sql);
?>
<html>
<title>.:: Detalhes Grupo P.A. ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
<body>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Consultar Grupo P.A.
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Família:</b>
        <td>
            <select name='cmb_familia' title='Família' class='textdisabled' disabled>
            <?
                $sql = "SELECT id_familia, nome 
                        FROM `familias` 
                        WHERE `ativo` = '1' ORDER BY nome ";
                echo combos::combo($sql, $campos[0]['id_familia']);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo P.A.:</b>
        </td>
        <td>
            <input type='text' name='txt_grupo' value='<?=$campos[0]['nome'];?>' title='Grupo P.A.' maxlength='50' size='55' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo P.A. Inglês:</b>
        </td>
        <td>
            <input type='text' name='txt_grupo_ingles' value='<?=$campos[0]['nome_ing'];?>' title='Grupo P.A. Inglês' size='38' maxlength='50' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Grupo P.A. Espanhol:</b>
        </td>
        <td>
            <input type='text' name='txt_grupo_espanhol' value='<?=$campos[0]['nome_esp'];?>' title='Digite o Grupo P.A. Espanhol' size='38' maxlength='50' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Lote Mínimo Produção R$:</b>
        </td>
        <td>
            <input type='text' name='txt_lote_min_prod_reais' value='<?=number_format($campos[0]['lote_min_producao_reais'], 2, ',', '.');?>' title='Lote Mínímo Produção R$' size='14' maxlength='12' class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Tolerância:</b>
        </td>
        <td>
            <input type='text' name='txt_tolerancia' title='Digite a Tolerância' value='<?=$campos[0]['tolerancia'];?>' size='40' maxlength='5' class='textdisabled' disabled>
        </td>
    </tr>
<?
    if(!empty($campos[0]['desenho_para_conferencia'])) {//Se existe um Desenho no Grupo então ...
?>
    <tr class='linhanormal'>
        <td>
            Desenho p/ Conferência Atual:
        </td>
        <td>
            <img src = '../../../../../imagem/desenhos_grupos_pas/<?=$campos[0]['desenho_para_conferencia'];?>' width='400' height='100'>
            &nbsp;
            <input type='checkbox' name='chkt_excluir_desenho_para_conferencia' id='chkt_excluir_desenho_para_conferencia' value='S' title='Excluir Desenho p/ Conferência Atual' class='checkbox'>
            <label for='chkt_excluir_desenho_para_conferencia'>
                Excluir Desenho p/ Conferência Atual
            </label>
        </td>
    </tr>
<?
    }
//Aqui traz todas as empresas divisões que estão relacionado ao grupo pa ...
    $sql = "SELECT ged.*, ed.id_empresa_divisao, ed.razaosocial 
            FROM `gpas_vs_emps_divs` ged 
            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.id_empresa_divisao 
            WHERE ged.`id_grupo_pa` = '$_GET[id_grupo_pa]' ORDER BY ed.razaosocial ";
    $campos_ed = bancos::sql($sql);
    $linhas = count($campos_ed);
    if($linhas > 0) {
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhadestaque' align='center'>
        <td colspan='8'>
            Empresa(s) Divisão(ões) Atrelada(s)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td bgcolor='#CCCCCC'>
            <b><i>Empresa Divisão</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b>i>Desc. Base A Nac.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Desc. Base B Nac.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Acrésc. Base Nac.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Margem de Lucro Exp.</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Comissão Extra</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Data Limite</i></b>
        </td>
        <td bgcolor='#CCCCCC'>
            <b><i>Caminho do PDF do Site</i></b>
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='right'>
        <td align='left'>
            <?=$campos_ed[$i]['razaosocial'];?>
        </td>
        <td>
            <?=number_format($campos_ed[$i]['desc_base_a_nac'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos_ed[$i]['desc_base_b_nac'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos_ed[$i]['acrescimo_base_nac'], 2, ',', '.');?>
        </td>
        <td>
            <?=number_format($campos_ed[$i]['margem_lucro_exp'], 2, ',', '.');?>
        </td>
        <td>
            <?=segurancas::number_format($campos_ed[$i]['comissao_extra'], 1, '.');?>
        </td>
        <td align='center'>
        <?
            if($campos_ed[$i]['data_limite'] != '0000-00-00') echo data::datetodata($campos_ed[$i]['data_limite'], '/');
        ?>
        </td>
        <td align='left'>
            <?=$campos_ed[$i]['path_pdf'];?>
        </td>
    </tr>
<?
        }
?>
</table>
<?
    }
?>
<table width='90%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhanormal'>
        <td>
            Observação:
        </td>
        <td>
            <textarea name='txt_observacao' cols='85' rows='3' title='Observação' class='textdisabled' disabled><?=$campos[0]['observacao'];?></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>