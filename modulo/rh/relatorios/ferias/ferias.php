<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../../');
?>
<html>
<head>
<title>.:: Relatório de Férias ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<html xmlns="http://www.w3.org/1999/xhtml">
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<form name='form' method='post'>
<table width='70%' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <?
                if($cmb_empresa == 1) {
                    $empresa = ' - ALBAFER';
                }else if($cmb_empresa == 2) {
                    $empresa = ' - TOOL MASTER';
                }else if($cmb_empresa == 4) {
                    $empresa = ' - GRUPO';
                }else if(strtoupper($cmb_empresa) == 'T') {
                    $empresa = ' - TODAS EMPRESAS';
                }
            ?>
            Relatório de Férias
            <font color='yellow'>
                <?=$empresa;?>
            </font>
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='5'>
            Empresa: 
            <select name='cmb_empresa' title='Selecione a Empresa' class='combo'>
                <?
                    if($cmb_empresa == 1) {
                        $selecteda = 'selected';
                    }else if($cmb_empresa == 2) {
                        $selectedt = 'selected';
                    }
                ?>
                <option value='t'>TODAS EMPRESAS</option>
                <option value='1' <?=$selecteda;?>>ALBAFER</option>
                <option value='2' <?=$selectedt;?>>TOOL MASTER</option>
            </select>
            &nbsp;
            <?
                if(!empty($chkt_ferias_proximos_30_dias)) {
                    $data_ferias_proximos_30_dias           = data::adicionar_data_hora(date('d/m/Y'), 30);
                    $condicao_data_ferias_proximos_30_dias  = " AND f.`data_max_ferias` BETWEEN '".date('Y-m-d')."' AND '".data::datatodate($data_ferias_proximos_30_dias, '-')."'";
                    $checked                                = 'checked';
                }
            ?>
            <input type='checkbox' name='chkt_ferias_proximos_30_dias' id='chkt_ferias_proximos_30_dias' value='S' class='checkbox' <?=$checked;?>>
            <label for='chkt_ferias_proximos_30_dias'>
                Férias nos Próximos 30 dias
            </label>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
//Nunca listo nenhum Funcionário que é da Empresa Grupo, porque este não contém registro em carteira ...
//Significa que o usuário já selecionou alguma empresa no Relatório ...
if(!empty($cmb_empresa)) {
    if(strtoupper($cmb_empresa) == 'T') {//Selecionei para listar os Funcionários de Todas as Empresas
        $empresas = array(1, 2);
    }else {
        $empresas[] = $cmb_empresa;//empresa selecionada pela combo
    }

//Listo as Empresas de acordo com o que foi selecionado pelo usuário no Sistema ...
    $linhas_empresas = count($empresas);
//Disparo de Loop das Empresas
    for($h = 0; $h < $linhas_empresas; $h++) {
?>
    <tr class='linhacabecalho'>
        <td colspan='5'>
            <font color='yellow'>
                Empresa: 
            </font>
            <?=genericas::nome_empresa($empresas[$h]);?>
        </td>
    </tr>
<?
/*Listagem de Funcionários independente da Empresa, que ainda estão trabalhando
/*Só não exibo os funcionários Default (1,2), ADAMO 91 e DIRETO BR 114 e os diretores Roberto 62, 
Dona Sandra 66 e Wilson 68 porque estes não são funcionários, simplesmente só possuem cadastrado 
no Sistema p/ poder acessar algumas telas ...*/
        $sql = "SELECT c.`cargo`, f.`id_funcionario`, f.`nome`, f.`data_admissao`, f.`data_prox_ferias`, 
                f.`data_max_ferias` 
                FROM `funcionarios` f 
                INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
                WHERE f.`id_empresa` = '".$empresas[$h]."' 
                AND f.`status` < '3' 
                AND f.`id_funcionario` NOT IN (1, 2, 62, 66, 68, 91, 114) 
                $condicao_data_ferias_proximos_30_dias ORDER BY f.`nome` ";
        $campos = bancos::sql($sql);
        $linhas = count($campos);
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Funcionário
        </td>
        <td>
            Cargo
        </td>
        <td>
            Data de <br/>Admissão
        </td>
        <td>
            Venc. <br/>a Gozar
        </td>
        <td>
            Venc. Max. <br/>a Gozar
        </td>
    </tr>
<?
//Listando os Funcionário(s) ...
        for($i = 0; $i < $linhas; $i++) {
/*Coloquei esse nome de $id_funcionario_loop, p/ não dar conflito com a variável "id_funcionário" da sessão
e o parâmetro pop_up significa que está tela está sendo aberta como pop_up e sendo assim é para não exibir
o botão de Voltar que existe nessa tela*/
            $url = "javascript:html5Lightbox.showLightbox(7, '../../funcionario/alterar_dados_profissionais.php?id_funcionario_loop=".$campos[$i]['id_funcionario']."&pop_up=1') ";
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td align='left'>
            <a href="<?=$url;?>" title='Visualizar Detalhes' class='link'>
                <font face='Verdana, Arial, Helvetica, sans-serif' size='-5'>
                    <?=$campos[$i]['nome'];?>
                </font>
            </a>
        </td>
        <td align='left'>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
            <?=data::datetodata($campos[$i]['data_admissao'], '/');?>
        </td>
        <td>
        <?
/*Se a Data Atual já for maior do que o Vencimento das Próximas férias, então eu printo esta linha em 
vermelho para dizer que estas férias já venceu ...*/
            if(date('Y-m-d') > $campos[$i]['data_prox_ferias']) echo '<font color="red">';
            echo data::datetodata($campos[$i]['data_prox_ferias'], '/');
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['data_max_ferias'] != '0000-00-00') echo data::datetodata($campos[$i]['data_max_ferias'], '/');
        ?>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='5'>
            <input type='submit' name='cmd_atualizar' value='Atualizar' title='Atualizar' class='botao'>
            <input type='button' name='cmd_imprimir' value='Imprimir' title='Imprimir' onclick='window.print()' style='color:black' class='botao'>
        </td>
    </tr>
</table>
<?}?>
</form>
</body>
</html>