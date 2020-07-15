<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
session_start('fucionarios');
//segurancas::geral($PHP_SELF, '../../../');
$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ATRASO / FALTA ALTERADO COM SUCESSO.</font>";

if($passo == 1) {
    if($_SERVER['REQUEST_METHOD'] == 'POST') {
        $txt_data_ocorrencia        = $_POST['txt_data_ocorrencia'];
        $cmb_motivo                 = $_POST['cmb_motivo'];
        $txt_observacao             = $_POST['txt_observacao'];
        $chkt_itens_ultimos_45_dias = $_POST['chkt_itens_ultimos_45_dias'];
    }else {
        $txt_data_ocorrencia        = $_GET['txt_data_ocorrencia'];
        $cmb_motivo                 = $_GET['cmb_motivo'];
        $txt_observacao             = $_GET['txt_observacao'];
        $chkt_itens_ultimos_45_dias = $_GET['chkt_itens_ultimos_45_dias'];
    }
//Data de Ocorrência ...
    if(!empty($txt_data_ocorrencia)) {
        $txt_data_ocorrencia = data::datatodate($txt_data_ocorrencia, '-');
        $condicao = " AND SUBSTRING(fa.`data_ocorrencia`, 1, 10) LIKE '$txt_data_ocorrencia%' "; 
    }
//Motivo ...
    if($cmb_motivo != '') $condicao2 = " AND fa.`motivo` = '$cmb_motivo' "; 
//Verifico se o usuário logado é Chefe, qual é o seu Departamento e se o mesmo tem login no ERP ...
    $sql = "SELECT DISTINCT(id_funcionario) 
            FROM `funcionarios` 
            WHERE `id_funcionario_superior` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_chefe = bancos::sql($sql);
//Verifico qual o Departamento do Funcionário logado ...
    $sql = "SELECT id_departamento 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_departamento = bancos::sql($sql);
//Verifico se o usuário logado tem Login ...
    $sql = "SELECT DISTINCT(id_login) 
            FROM `logins` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_login = bancos::sql($sql);
    if(count($campos_chefe) == 1 && count($campos_login) == 1) {//O Funcionário logado é chefe e tem login ...
/*Verifico todas as ocorrências dos funcionários "que ainda trabalham na Empresa" do usuário logado, no caso 'Chefe' que possuem 
pendência de Portaria, no estágio de Chefia Liberar ...*/
        $sql = "SELECT fa.`id_funcionario_acompanhamento` 
                FROM `funcionarios` f 
                INNER JOIN `funcionarios_acompanhamentos` fa ON fa.`id_funcionario_acompanhado` = f.`id_funcionario` AND fa.`registro_portaria` = 'S' AND fa.`status_andamento` = '0' 
                WHERE f.`id_funcionario_superior` = '$_SESSION[id_funcionario]' 
                AND f.`status` < '3' ";
        $campos_pendencia = bancos::sql($sql);
        $linhas_pendencia = count($campos_pendencia);//Existem pendências - estágio de Portaria ...
        for($i = 0; $i < $linhas_pendencia; $i++) $id_funcionario_acompanhamentos.= $campos_pendencia[$i]['id_funcionario_acompanhamento'].', ';
/*************************Chefes de Férias**************************/
/*Verifico se esse Chefe logado tem funcionários subordinados a ele e se algum desses funcionários subordinados 
está de Férias ...*/
        $sql = "SELECT DISTINCT(f.`id_funcionario`) 
                FROM `funcionarios` f 
                WHERE f.`id_funcionario_superior` = '$_SESSION[id_funcionario]' 
                AND f.`status` = '0' ";
        $campos_subordinados = bancos::sql($sql);
        $linhas_subordinados = count($campos_subordinados);
//Existem funcionários de Férias ...
        for($i = 0; $i < $linhas_subordinados; $i++) {
/*Verifico se algum desses funcionários subordinados que está de Férias, também são chefes 
de outros funcionários ... - No caso este seria um sub-chefe ...*/
            $sql = "SELECT DISTINCT(`id_funcionario`) 
                    FROM `funcionarios` 
                    WHERE `id_funcionario_superior` = ".$campos_subordinados[$i]['id_funcionario']." LIMIT 1 ";
            $campos_sub_chefe = bancos::sql($sql);
//Significa que este funcionário é um sub-chefe, ou seja subordinado ao Chefe Principal ...
            if(count($campos_sub_chefe) == 1) {
/*Verifico se os funcionários "que ainda trabalham na Empresa" que são subordinados aos Chefes que são subordinados aos seus Chefes (rs) 
possuem pendência no estágio de Chefia Liberar ...*/
                $sql = "SELECT fa.`id_funcionario_acompanhamento` 
                        FROM `funcionarios` f 
                        INNER JOIN `funcionarios_acompanhamentos` fa ON fa.`id_funcionario_acompanhado` = f.`id_funcionario` AND fa.`registro_portaria` = 'S' AND fa.`status_andamento` = '0' 
                        WHERE f.`id_funcionario_superior` = '".$campos_subordinados[$i]['id_funcionario']."' 
                        AND f.`status` < '3' ";
                $campos_pendencia = bancos::sql($sql);
                $linhas_pendencia = count($campos_pendencia);//Existem pendências - estágio de Portaria ...
                for($j = 0; $j < $linhas_pendencia; $j++) $id_funcionario_acompanhamentos.= $campos_pendencia[$j]['id_funcionario_acompanhamento'].', ';
            }
        }
    }
/****************************************************************************************************************/
/*********************************************Parte do Depto Pessoal*********************************************/
/****************************************************************************************************************/
/*O rh "Departamento Pessoal", enxerga somente as ocorrências na qual ele mesmo é chefe, ocorrências de funcionários que 
são chefe que não tem login no ERP exemplo Ademar e todas as Ocorrências que estão com estágio acima de "RH Liberar" ...*/

    //Verifico se id_funcionario logado trabalha no Departamento de RH ...
    $sql = "SELECT id_departamento 
            FROM `funcionarios` 
            WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
    $campos_depto = bancos::sql($sql);
    if($campos_depto[0]['id_departamento'] == 24) {//Recursos Humanos ...
        //Listagem de todos os funcionários que são Chefe e possuem logins no ERP ...
        $sql = "SELECT DISTINCT(f.`id_funcionario_superior`) AS id_funcionario_superior 
                FROM `funcionarios` f 
                INNER JOIN `logins` l ON l.`id_funcionario` = f.`id_funcionario_superior` 
                WHERE f.`id_funcionario_superior` <> '0' 
                /**********************Modificado no dia 22/06/2017**********************
                Não trago propositalmente os Diretores Roberto 62, Sandra 66, Wilson 68 p/ que a pessoa do RH possa liberar 
                a ocorrência desses 3 também - a variável 'id_func_acomp_ignorar' não carregar esses 3 valores aqui, consequentemente 
                vai tratar com os 3 diretores mais abaixo ...*/
                AND f.`id_funcionario_superior` NOT IN (62, 66, 68) ";
        $campos_chefe = bancos::sql($sql);
        $linhas_chefe = count($campos_chefe);
        for($i = 0; $i < $linhas_chefe; $i++) {
/*Busca de todas as ocorrências dos funcionários "que ainda trabalham na Empresa" que são subordinados a esses chefes que possuem login 
e que possuem pendência de Portaria no estágio de Chefia Liberar ...*/
            $sql = "SELECT fa.`id_funcionario_acompanhamento` 
                    FROM `funcionarios` f 
                    INNER JOIN `funcionarios_acompanhamentos` fa ON fa.`id_funcionario_acompanhado` = f.`id_funcionario` AND fa.`registro_portaria` = 'S' AND fa.`status_andamento` = '0' 
                    WHERE f.`id_funcionario_superior` = '".$campos_chefe[$i]['id_funcionario_superior']."' 
                    AND f.`status` < '3' ";
            $campos_subordinados = bancos::sql($sql);
            $linhas_subordinados = count($campos_subordinados);
            for($j = 0; $j < $linhas_subordinados; $j++) $id_func_acomp_ignorar.= $campos_subordinados[$j]['id_funcionario_acompanhamento'].', ';
        }
//Significa que não carregou essa variável no Loop ...
        if(strlen($id_func_acomp_ignorar) == 0) {
            $id_func_acomp_ignorar = 0;
        }else {
            $id_func_acomp_ignorar = substr($id_func_acomp_ignorar, 0, strlen($id_func_acomp_ignorar) - 2);
        }
/*Listagem de todas as ocorrências de todos os funcionários "que ainda trabalham na Empresa" subordinados que possuem pendência de Portaria, 
no estágio de Chefia Liberar, RH Chefia e seus chefes não possuem login no Sistema  ...*/
        $sql = "SELECT fa.`id_funcionario_acompanhamento` 
                FROM `funcionarios` f 
                /**********************Modificado no dia 22/06/2017**********************
                Como a própria pessoa do RH agora tem acesso para liberar as ocorrências dos diretores, só não exibo essa porque ela não pode
                liberar suas próprias ocorrências sem um parecer de seu superior ...*/
                INNER JOIN `funcionarios_acompanhamentos` fa ON fa.`id_funcionario_acompanhado` = f.`id_funcionario` AND fa.`registro_portaria` = 'S' AND ((fa.`status_andamento` = '0' AND fa.`id_funcionario_acompanhado` <> '111') OR fa.`status_andamento` = '1') AND fa.`id_funcionario_acompanhamento` NOT IN ($id_func_acomp_ignorar) 
                WHERE f.`id_funcionario_superior` <> '0' 
                AND f.`status` < '3' ";
        $campos_pendencia = bancos::sql($sql);
        $linhas_pendencia = count($campos_pendencia);//Existem pendências - estágio de Chefia e Portaria ...
        for($i = 0; $i < $linhas_pendencia; $i++) $id_funcionario_acompanhamentos.= $campos_pendencia[$i]['id_funcionario_acompanhamento'].', ';
    }
/****************************************************************************************************************/
    //Tratamento p/ não dar erro no SQL + abaixo ...
    if(strlen($id_funcionario_acompanhamentos) == 0) {
        $id_funcionario_acompanhamentos = 0;
    }else {
        $id_funcionario_acompanhamentos = substr($id_funcionario_acompanhamentos, 0, strlen($id_funcionario_acompanhamentos) - 2);
    }
    $condicao_func_acompanhamentos = " AND fa.`id_funcionario_acompanhamento` IN ($id_funcionario_acompanhamentos) ";
    $condicao_status_andamento = " AND fa.`status_andamento` IN (0, 1) ";
    
    if(!empty($chkt_itens_ultimos_45_dias)) {
        $data_ultimos_45_dias = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -45), '-');
        $condicao_itens_ultimos_45_dias = " AND SUBSTRING(fa.`data_ocorrencia`, 1, 10) > '$data_ultimos_45_dias' ";
    }
    
//Traz todos funcionários que possuem pendências na Portaria com exceção das concluídas ...
    $sql = "SELECT c.`cargo`, e.`id_empresa`, e.`nomefantasia`, f.`id_funcionario`, f.`id_funcionario_superior`, 
            f.`nome`, f.`rg`, f.`codigo_barra`, f.`ddd_residencial`, f.`telefone_residencial`, fa.* 
            FROM `funcionarios` f 
            INNER JOIN `empresas` e ON e.`id_empresa` = f.`id_empresa` 
            INNER JOIN `cargos` c ON c.`id_cargo` = f.`id_cargo` 
            INNER JOIN `funcionarios_acompanhamentos` fa ON fa.`id_funcionario_acompanhado` = f.`id_funcionario` AND fa.`observacao` LIKE '%$txt_observacao%' $condicao $condicao2 AND fa.`registro_portaria` = 'S' $condicao_func_acompanhamentos $condicao_status_andamento $condicao_itens_ultimos_45_dias 
            WHERE f.`nome` LIKE '%$txt_nome%' 
            ORDER BY e.`id_empresa`, f.`nome`, fa.`data_ocorrencia` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
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
<title>.:: Alterar Atraso / Falta / Saída ::.</title>
<meta http-equiv = 'content-type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var elementos           = document.form.elements
    var linhas_selecionadas = 0

    if(typeof(elementos['chkt_funcionario_acompanhamento[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_funcionario_acompanhamento[]'].length)
    }
//Verifico se temos pelo menos 1 opção selecionada ...
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_funcionario_acompanhamento'+i).checked == true) {//Linha habilitada ...
            linhas_selecionadas++
            break;
        }
    }
    
    if(linhas_selecionadas == 0) {
        alert('SELECIONE UMA OPÇÃO !')
        document.getElementById('chkt_funcionario_acompanhamento0').focus()
        return false
    }else {
        for(i = 0; i < linhas; i++) {
            if(document.getElementById('chkt_funcionario_acompanhamento'+i).checked == true) {//Linha habilitada ...
                //Verifico se será abonado ou não ...
                if(document.getElementById('cmb_abonar'+i).value == '') {
                    alert('SELECIONE UMA OPÇÃO P/ ABONAR !')
                    document.getElementById('cmb_abonar'+i).focus()
                    return false
                }
            }
        }
    }
    
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('chkt_funcionario_acompanhamento'+i).checked == true) {//Linha habilitada ...
            //Verifico se será abonado ou não ...
            if(document.getElementById('cmb_abonar'+i).value == '') {
                alert('SELECIONE UMA OPÇÃO P/ ABONAR !')
                document.getElementById('cmb_abonar'+i).focus()
                return false
            }
        }
    }
}

function selecionar_tudo(indice_checkbox_principal, id_funcionario) {
    var elementos   = document.form.elements
    //Controle com o Checkbox Principal ...
    var checkado    = (document.getElementById('chkt_tudo'+indice_checkbox_principal).checked == true) ? true : false
    
    if(typeof(elementos['chkt_funcionario_acompanhamento[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['chkt_funcionario_acompanhamento[]'].length)
    }

//Verifico quantas linhas eu preciso selecionar ...
    for(i = 0; i < linhas; i++) {
        if(document.getElementById('hdd_funcionario'+i).value == id_funcionario) {//Só seleciono linhas do funcionário passado por parâmetro ...
            document.getElementById('chkt_funcionario_acompanhamento'+i).checked = checkado
            habilitar_desabilitar_linha(i)
        }
    }
}
    
function habilitar_desabilitar_linha(indice) {
    if(document.getElementById('chkt_funcionario_acompanhamento'+indice).checked == true) {//Linha habilitada ...
        //Habilita os objetos ...
        document.getElementById('hdd_status_andamento'+indice).disabled     = false
        document.getElementById('cmb_abonar'+indice).disabled               = false
        document.getElementById('txt_observacao_chefe'+indice).disabled     = false
        //Muda o Layout dos objetos p/ Habilitado ...
        document.getElementById('cmb_abonar'+indice).className              = 'combo'
        document.getElementById('txt_observacao_chefe'+indice).className    = 'caixadetexto'
    }else {
        //Desabilita os objetos ...
        document.getElementById('hdd_status_andamento'+indice).disabled     = true
        document.getElementById('cmb_abonar'+indice).disabled               = true
        document.getElementById('txt_observacao_chefe'+indice).disabled     = true
        //Desmarca e Limpa os objetos ...
        document.getElementById('cmb_abonar'+indice).value                  = ''
        document.getElementById('txt_observacao_chefe'+indice).value        = ''
        //Muda o Layout dos objetos p/ Desabilitado ...
        document.getElementById('cmb_abonar'+indice).className              = 'textdisabled'
        document.getElementById('txt_observacao_chefe'+indice).className    = 'textdisabled'
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=3';?>" onsubmit='return validar()'>
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Alterar Atraso / Falta / Saída
        </td>
    </tr>
<?
//Criei esse array p/ facilitar na Visualização mais abaixo ...
        $vetor_motivos                      = array('Entrada', 'Saída', 'Falta');
        $vetor_status_andamento             = array('<font color="red">Chefia Liberar</font>', '<font color="darkblue">RH Liberar</font>', '<font color="darkgreen">RH Liberado</font>');
        $vetor_dia_semana                   = array('DOMINGO', 'SEGUNDA-FEIRA', 'TERÇA-FEIRA', 'QUARTA-FEIRA', 'QUINTA-FEIRA', 'SEXTA-FEIRA', 'SÁBADO');
        
//Essa variável será utilizada mais abaixo ...
        $id_empresa_anterior                = '';
        $id_funcionario_anterior            = '';
        $id_funcionario_superior_anterior   = '';
        
        for($i = 0; $i < $linhas; $i++) {
/*Aqui eu verifico se a Empresa Anterior é Diferente da Empresa Atual que está sendo listado
no loop, se for então eu atribuo o Empresa Atual p/ o Empresa Anterior ...*/
/**************************Empresa**************************/
            if($id_empresa_anterior != $campos[$i]['id_empresa']) {
                $id_empresa_anterior = $campos[$i]['id_empresa'];
?>
    <tr class='linhacabecalho'>
        <td colspan='7'>
            <font color='yellow'>
                <b>Empresa: </b>
            </font>
            <?=$campos[$i]['nomefantasia'];?>
        </td>
    </tr>
<?
            }
/*Aqui eu verifico se o Funcionário Anterior é Diferente do Funcionário Atual que está 
sendo listado no loop, se for então eu atribuo o Funcionário Atual p/ o Funcionário 
Anterior ...*/
/***********************************************************/
/**************************Funcionário**************************/
            if($id_funcionario_anterior != $campos[$i]['id_funcionario']) {
                $id_funcionario_anterior = $campos[$i]['id_funcionario'];
?>
    <tr class='linhadestaque'>
        <td colspan='4'>
            <font color='yellow'>
                <b>Funcionário: </b>
            </font>
            <?=$campos[$i]['nome'];?>
        </td>
        <td colspan='2'>
            <font color='yellow'>
                <b>Cargo: </b>
            </font>
            <?=$campos[$i]['cargo'];?>
        </td>
        <td>
            <font color='yellow'>
                <b>Chefe: </b>
            </font>
        <?	
//Busca do Nome e Status do Chefe do Funcionário ...
            $sql = "SELECT `nome`, `status` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = ".$campos[$i]['id_funcionario_superior']." LIMIT 1 ";
            $campos_chefe = bancos::sql($sql);
            echo $campos_chefe[0]['nome'];
            if($campos_chefe[0]['status'] == 0) echo '<font color="red"><b> (Férias)</b></font>';
        ?>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo[]' id='chkt_tudo<?=$indice_checkbox_principal;?>' onclick="selecionar_tudo('<?=$indice_checkbox_principal;?>', '<?=$campos[$i]['id_funcionario'];?>')" class='checkbox'>
            Opções&nbsp;<img src = '../../../imagem/bloco_negro.gif' title='A seta serve p/ liberar a Ocorrência de forma Individual, o Checkbox serve p/ liberar a Ocorrência em Lote.' style='cursor:help' width='6' height='6'>
        </td>
        <td>
            Data
        </td>
        <td>
            Hora
        </td>
        <td>
            Motivo - Login
        </td>
        <td>
            Status
        </td>
        <td>
            Abonar
        </td>
        <td>
            Observação Chefe
        </td>
    </tr>
<?
                $indice_checkbox_principal++;
            }
/***************************************************************/
/**************************Rotina Normal**************************/
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')">
        <td width='10'>
            <a href = 'alterar.php?passo=2&id_funcionario_acompanhamento=<?=$campos[$i]['id_funcionario_acompanhamento'];?>'>
                <img src = '../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
            &nbsp;/
            <input type='checkbox' name='chkt_funcionario_acompanhamento[]' id='chkt_funcionario_acompanhamento<?=$i;?>' value='<?=$campos[$i]['id_funcionario_acompanhamento'];?>' onclick="habilitar_desabilitar_linha('<?=$i;?>')" class='checkbox'>
        </td>
        <td align='center'>
        <?
            echo data::datetodata(substr($campos[$i]['data_ocorrencia'], 0, 10), '/');
            $dia_semana = data::dia_semana(data::datetodata(substr($campos[$i]['data_ocorrencia'], 0, 10), '/'));
            echo '<br/><font color="red"><b> ('.$vetor_dia_semana[$dia_semana].') </b></font>';
        ?>
        </td>
        <td align='center'>
        <?
            if($campos[$i]['motivo'] == 2) {//Falta
                echo '-';
            }else {//Em alguma outra situação ...
                echo substr($campos[$i]['data_ocorrencia'], 11, 8);
            }
        ?>
        </td>
        <td>
        <?
            echo '<font color="darkblue"><b>'.$vetor_motivos[$campos[$i]['motivo']].'</b></font>';
            if($campos[$i]['sem_cracha'] == 'S') echo '<font color="red"><b> (Sem Crachá)</b></font>';
            echo ' - '.$campos[$i]['observacao'];
        ?>
        </td>
        <td align='center'>
            <b><?=$vetor_status_andamento[$campos[$i]['status_andamento']];?></b>
            <input type='hidden' name='hdd_status_andamento[]' id='hdd_status_andamento<?=$i;?>' value='<?=$campos[$i]['status_andamento'];?>' disabled>
        </td>
        <td align='center'>
            <?
                if($campos[$i]['abonar'] == 'S') {
                    $selected_abonar_sim = 'selected';
                }else if($campos[$i]['abonar'] == 'N') {
                    $selected_abonar_nao = 'selected';
                }else {
                    $selected_abonar_sim = '';
                    $selected_abonar_nao = '';
                }
            ?>
            <select name='cmb_abonar[]' id='cmb_abonar<?=$i;?>' title='Selecione uma opção p/ Abonar' class='textdisabled' disabled>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='S' <?=$selected_abonar_sim;?>>SIM</option>
                <option value='N' <?=$selected_abonar_nao;?>>NÃO</option>
            </select>
        </td>
        <td align='center'>
            <textarea name='txt_observacao_chefe[]' id='txt_observacao_chefe<?=$i;?>' title='Digite a Observação do Chefe' maxlength='100' cols='35' rows='1' class='textdisabled' disabled></textarea>
            <input type='hidden' name='hdd_funcionario[]' id='hdd_funcionario<?=$i;?>' value='<?=$campos[$i]['id_funcionario'];?>' disabled>
        </td>
    </tr>
<?
        }
/*****************************************************************/
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            <input type='button' name='cmd_consultar_novamente' value='Consultar Novamente' title='Consultar Novamente' onclick="window.location = 'alterar.php'" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
        </td>
    </tr>
</table>
</form>
<center>
    <?=paginacao::print_paginacao('sim');?>
</center>
</body>
</html>
<?
    }
}else if($passo == 2) {
//Trago dados de Acompanhamento do "id_funcionario_acompanhamento" passado por parâmetro ...
    $sql = "SELECT f.`id_funcionario_superior`, fa.* 
            FROM `funcionarios_acompanhamentos` fa 
            INNER JOIN `funcionarios` f ON f.`id_funcionario` = fa.`id_funcionario_acompanhado` 
            WHERE fa.`id_funcionario_acompanhamento` = '$_GET[id_funcionario_acompanhamento]' LIMIT 1 ";
    $campos = bancos::sql($sql);
?>
<html>
<head>
<title>.:: Alterar Atraso / Falta / Saída ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    status_andamento = eval('<?=$campos[0]['status_andamento'];?>')
//Estado de Chefia ...
    if(status_andamento == 0) {//Somente no Estado de Chefia Liberar, força o preench ...
//Verifico se será abonado ou não ...
        if(document.form.cmb_abonar.value == '') {
            alert('SELECIONE UMA OPÇÃO P/ ABONAR !')
            document.form.cmb_abonar.focus()
            return false
        }
//Controla p/ ver se a Observação foi preenchida corretamente ...
        return observacao()
//Somente no Estado de RH Liberar ou RH Liberado, força o preench ...
    }else if(status_andamento == 1 || status_andamento == 2) {
/***************************Descontar PLR***************************/
        if(document.form.chkt_descontar_plr.checked == true) {//Se estiver checado ...
//Hora Inicial ...
            if(!texto('form', 'txt_hora_inicial_plr', '1', '1234567890:', 'HORA INICIAL DESCONTAR PLR', '1')) {
                return false
            }
//Hora Final ...
            if(!texto('form', 'txt_hora_final_plr', '1', '1234567890:', 'HORA FINAL DESCONTAR PLR', '1')) {
                return false
            }
//Consistência somente nos campos de Hora ...
            hora_inicial = eval(strtofloat(document.form.txt_hora_inicial_plr.value.replace(':', '.')))
            hora_final = eval(strtofloat(document.form.txt_hora_final_plr.value.replace(':', '.')))
//Se a Hora Final for menor do que a Hora Inicial ...
            if(hora_final < hora_inicial) {
                alert('HORA FINAL DESCONTAR PLR INVÁLIDA !')
                document.form.txt_hora_final_plr.focus()
                document.form.txt_hora_final_plr.select()
                return false
            }
        }
/***************************Atestado***************************/
        if(document.form.chkt_atestado.checked == true) {//Se estiver checada ...
//Hora Inicial ...
            if(!texto('form', 'txt_hora_inicial_atestado', '1', '1234567890:', 'HORA INICIAL DO ATESTADO', '1')) {
                return false
            }
//Hora Final ...
            if(!texto('form', 'txt_hora_final_atestado', '1', '1234567890:', 'HORA FINAL DO ATESTADO', '1')) {
                return false
            }
//Consistência somente nos campos de Hora ...
            hora_inicial = eval(strtofloat(document.form.txt_hora_inicial_atestado.value.replace(':', '.')))
            hora_final = eval(strtofloat(document.form.txt_hora_final_atestado.value.replace(':', '.')))
//Se a Hora Final for menor do que a Hora Inicial ...
            if(hora_final < hora_inicial) {
                alert('HORA FINAL DO ATESTADO INVÁLIDA !')
                document.form.txt_hora_final_atestado.focus()
                document.form.txt_hora_final_atestado.select()
                return false
            }
        }
/***************************Descontar***************************/
        if(document.form.chkt_descontar.checked == true) {//Se estiver checada ...
//Hora Inicial ...
            if(!texto('form', 'txt_hora_inicial_descontar', '1', '1234567890:', 'HORA INICIAL DESCONTAR', '1')) {
                return false
            }
//Hora Final ...
            if(!texto('form', 'txt_hora_final_descontar', '1', '1234567890:', 'HORA FINAL DESCONTAR', '1')) {
                return false
            }
//Consistência somente nos campos de Hora ...
            hora_inicial = eval(strtofloat(document.form.txt_hora_inicial_descontar.value.replace(':', '.')))
            hora_final = eval(strtofloat(document.form.txt_hora_final_descontar.value.replace(':', '.')))
//Se a Hora Final for menor do que a Hora Inicial ...
            if(hora_final < hora_inicial) {
                alert('HORA FINAL INVÁLIDA !')
                document.form.txt_hora_final_descontar.focus()
                document.form.txt_hora_final_descontar.select()
                return false
            }
        }
//Controla p/ ver se a Observação foi preenchida corretamente ...
        return observacao(status_andamento)
    }
}

function observacao(valor) {
//Somente no Estado de RH Liberar que eu forço o preenchimento da Observação ...
    if(valor == 1) {//RH Liberar ...
//Observação ...
        if(document.form.txt_observacao_complementar.value != '') {
//Verifica se a Observação está incompleta ...
            if(document.form.txt_observacao_complementar.value.length < 5) {
                alert('OBSERVAÇÃO INCOMPLETA !')
                document.form.txt_observacao_complementar.focus()
                return false
            }
        }
/****************************************************************************/
        var resposta = confirm('TEM CERTEZA DE QUE DESEJA LIBERAR ?')
        if(resposta == true) {
            desabilitar_objetos()
        }else {
            return false
        }
    }else {//RH Liberado ...
        desabilitar_objetos()
    }
}

function desabilitar_objetos() {
//Prepara os objetos p/ gravar na Base de Dados ...
    document.form.cmb_abonar.disabled = false
//Caso existir esses objetos na Tela ...
/***************************Descontar PLR***************************/
    if(typeof(document.form.txt_hora_inicial_plr) == 'object') {
        document.form.txt_hora_inicial_plr.value = document.form.txt_hora_inicial_plr.value.replace(':', '.')
        document.form.txt_hora_final_plr.value = document.form.txt_hora_final_plr.value.replace(':', '.')
    }
/***************************Atestado***************************/
    if(typeof(document.form.txt_hora_inicial_atestado) == 'object') {
        document.form.txt_hora_inicial_atestado.value = document.form.txt_hora_inicial_atestado.value.replace(':', '.')
        document.form.txt_hora_final_atestado.value = document.form.txt_hora_final_atestado.value.replace(':', '.')
    }
/***************************Descontar***************************/
    if(typeof(document.form.txt_hora_inicial_descontar) == 'object') {
        document.form.txt_hora_inicial_descontar.value = document.form.txt_hora_inicial_descontar.value.replace(':', '.')
        document.form.txt_hora_final_descontar.value = document.form.txt_hora_final_descontar.value.replace(':', '.')
    }	
}

function controlar_objetos(valor) {
    if(valor == 1) {//Descontar no PLR ...
        if(document.form.chkt_descontar_plr.checked == true) {//Se estiver checado ...
//Habilita as caixinhas ...
            document.form.txt_hora_inicial_plr.disabled = false
            document.form.txt_hora_final_plr.disabled   = false
//Layout de Habilitado ...
            document.form.txt_hora_inicial_plr.className = 'caixadetexto'
            document.form.txt_hora_final_plr.className  = 'caixadetexto'
            document.form.txt_hora_inicial_plr.focus()
        }else {//Se desmarcado ...
//Desabilita as caixinhas ...
            document.form.txt_hora_inicial_plr.disabled = true
            document.form.txt_hora_final_plr.disabled   = true
//Limpa as caixinhas ...
            document.form.txt_hora_inicial_plr.value    = ''
            document.form.txt_hora_final_plr.value      = ''
//Layout de Desabilitado ...
            document.form.txt_hora_inicial_plr.className = 'textdisabled'
            document.form.txt_hora_final_plr.className  = 'textdisabled'
        }
    }else if(valor == 2) {//Atestado vistado pelo chefe ...
        if(document.form.chkt_atestado.checked == true) {//Se estiver checado ...
//Habilita as caixinhas ...
            document.form.txt_hora_inicial_atestado.disabled = false
            document.form.txt_hora_final_atestado.disabled = false
//Layout de Habilitado ...
            document.form.txt_hora_inicial_atestado.className = 'caixadetexto'
            document.form.txt_hora_final_atestado.className = 'caixadetexto'
            document.form.txt_hora_inicial_atestado.focus()
        }else {//Se desmarcado ...
//Desabilita as caixinhas ...
            document.form.txt_hora_inicial_atestado.disabled = true
            document.form.txt_hora_final_atestado.disabled = true
//Limpa as caixinhas ...
            document.form.txt_hora_inicial_atestado.value = ''
            document.form.txt_hora_final_atestado.value = ''
//Layout de Desabilitado ...
            document.form.txt_hora_inicial_atestado.className = 'textdisabled'
            document.form.txt_hora_final_atestado.className = 'textdisabled'
        }
    }else if(valor == 3) {//Descontar ...
        if(document.form.chkt_descontar.checked == true) {//Se estiver checado ...
//Habilita as caixinhas ...
            document.form.txt_hora_inicial_descontar.disabled = false
            document.form.txt_hora_final_descontar.disabled = false
//Layout de Habilitado ...
            document.form.txt_hora_inicial_descontar.className = 'caixadetexto'
            document.form.txt_hora_final_descontar.className = 'caixadetexto'
            document.form.txt_hora_inicial_descontar.focus()
        }else {//Se desmarcado ...
//Desabilita as caixinhas ...
            document.form.txt_hora_inicial_descontar.disabled = true
            document.form.txt_hora_final_descontar.disabled = true
//Limpa as caixinhas ...
            document.form.txt_hora_inicial_descontar.value = ''
            document.form.txt_hora_final_descontar.value = ''
//Layout de Desabilitado ...
            document.form.txt_hora_inicial_descontar.className = 'textdisabled'
            document.form.txt_hora_final_descontar.className = 'textdisabled'
        }
    }
}
</Script>
</head>
<?
/*Quando essa 'Tela de Alterar' for acessada de Dentro do Relatório, então vou ter alguns controles diferentes 
no que se refere a JavaScript e Tratamento de Dados pelo PHP ...*/
    if($_GET['veio_tela_relatorio'] == 1) {
        $onload = 'controlar_objetos(1);controlar_objetos(2);controlar_objetos(3);document.form.txt_data_ocorrencia.focus()';
//Variáveis que serão utilizadas mais abaixo ...
        $hora_inicial_plr   = str_replace('.', ':', $campos[0]['hora_inicial_plr']);
        $hora_final_plr     = str_replace('.', ':', $campos[0]['hora_final_plr']);

        $hora_inicial_atestado = str_replace('.', ':', $campos[0]['hora_inicial_atestado']);
        $hora_final_atestado = str_replace('.', ':', $campos[0]['hora_final_atestado']);

        $hora_inicial_descontar = str_replace('.', ':', $campos[0]['hora_inicial_descontar']);
        $hora_final_descontar = str_replace('.', ':', $campos[0]['hora_final_descontar']);
    }else {
        $onload = 'document.form.txt_data_ocorrencia.focus()';
    }
?>
<body onload='<?=$onload;?>'>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=3';?>' onsubmit='return validar()'>
<input type='hidden' name='id_funcionario_acompanhamento' value='<?=$_GET['id_funcionario_acompanhamento'];?>'>
<input type='hidden' name='hdd_status_andamento' value='<?=$campos[0]['status_andamento'];?>'>
<!--Esse hidden tem por objetivo guardar a variável "parâmetro" que veio da Tela Pós-Filtro ... Faço isso porque nessa mesma tela aqui um 
pouco mais abaixo dentro de um Iframe é carregado um relatório de Atraso / Falta / Saída com dados do Funcionário e este é paginado, 
daí a variável "parâmetro" que fica na memória passa a ser substituda pela variável "parâmetro" deste relatório ...-->
<input type='hidden' name='hdd_parametro_pos_filtro' value='<?=$parametro;?>'>
<!--Variável que veio por parâmetro da Tela de Relatório e que vai me auxiliar em alguns controles 
especiais nessa Tela-->
<input type='hidden' name='veio_tela_relatorio' value='<?=$_GET['veio_tela_relatorio'];?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='3'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
            Alterar Atraso / Falta / Saída - 
            <font color='yellow'>
            <?
                if($campos[0]['status_andamento'] == 0) {
                    echo 'Chefia Liberar';
                }else if($campos[0]['status_andamento'] == 1) {
                    echo 'RH Liberar';
                }else if($campos[0]['status_andamento'] == 2) {
                    echo 'RH Liberado';
                }
            ?>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td width='15%'>
            <b>Funcionário:</b>
        </td>
        <td colspan='2'>
        <?
            $sql = "SELECT `nome` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = ".$campos[0]['id_funcionario_acompanhado']." LIMIT 1 ";
            $campos_funcionario = bancos::sql($sql);
            echo $campos_funcionario[0]['nome'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Chefe:</b>
        </td>
        <td colspan='2'>
        <?
            $sql = "SELECT `nome` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = ".$campos[0]['id_funcionario_superior']." LIMIT 1 ";
            $campos_chefe = bancos::sql($sql);
            echo $campos_chefe[0]['nome'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <font color='darkblue'>
                <b>Porteiro / Emissor:</b>
            </font>
        </td>
        <td colspan='2'>
        <?
            $sql = "SELECT `nome` 
                    FROM `funcionarios` 
                    WHERE `id_funcionario` = ".$campos[0]['id_funcionario_registrou']." LIMIT 1 ";
            $campos_porteiro_emissor = bancos::sql($sql);
            echo $campos_porteiro_emissor[0]['nome'];
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Data de Ocorrência:</b>
        </td>
        <td colspan='2'>
            <input type='text' name="txt_data_ocorrencia" value="<?=data::datetodata(substr($campos[0]['data_ocorrencia'], 0, 10), '/');?>" title="Digite a Data de Comparecimento" size="12" maxlength="10" class='textdisabled' disabled>
            <?
                $dia_semana         = data::dia_semana(data::datetodata(substr($campos[0]['data_ocorrencia'], 0, 10), '/'));
                $vetor_dia_semana   = array('DOMINGO', 'SEGUNDA-FEIRA', 'TERÇA-FEIRA', 'QUARTA-FEIRA', 'QUINTA-FEIRA', 'SEXTA-FEIRA', 'SÁBADO');
                echo ' <font color="red"><b> ('.$vetor_dia_semana[$dia_semana].') </b></font>';
            ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <b>Horário da Ocorrência:</b>
        </td>
        <td colspan='2'>
        <?
            if($campos[0]['motivo'] == 2) {//Falta
                $horario = '';
            }else {//Em alguma outra situação ...
                $horario = substr($campos[0]['data_ocorrencia'], 11, 5);
            }
        ?>
        <input type='text' name='txt_horario_ocorrencia' value='<?=$horario;?>' title='Digite o Horário da Ocorrência' size='8' maxlength='5' class='textdisabled' disabled>
        &nbsp;-
        <?
            if($campos[0]['sem_cracha'] == 'S') {
                $checked_sem_cracha = 'checked';
            }else {
                $checked_sem_cracha = '';
            }
        ?>
        <input type='checkbox' name='chkt_sem_cracha' value='S' title='Sem Crachá' id="sem_cracha" class='checkbox' <?=$checked_sem_cracha;?> disabled>
        <label for='sem_cracha'>
            Sem Crachá
        </label>
        <?
            $data_30_dias_atras = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -30), '-');
            /*Verifico quantas vezes que o Funcionário no qual está sendo feito a Ocorrência veio sem Crachá 
            nos últimos 30 dias ...*/
            $sql = "SELECT `id_funcionario_acompanhamento` 
                    FROM `funcionarios_acompanhamentos` 
                    WHERE `id_funcionario_acompanhado` = '".$campos[0]['id_funcionario_acompanhado']."' 
                    AND SUBSTRING(`data_ocorrencia`, 1, 10) >= '$data_30_dias_atras' 
                    AND `sem_cracha` = 'S' 
                    GROUP BY SUBSTRING(`data_ocorrencia`, 1, 10) ";
            $campos_dias_sem_cracha = bancos::sql($sql);
            if(count($campos_dias_sem_cracha) > 0) echo ' <font color="red"><b> - '.count($campos_dias_sem_cracha).' DIA(S) SEM CRACHÁ NOS ÚLTIMOS 30 DIAS</b></font>';
        ?>
        </td>
    </tr>
    <?
    	if($campos[0]['motivo'] == 0) {//Entrada
            $checked1 = 'checked';
    	}else if($campos[0]['motivo'] == 1) {//Saída
            $checked2 = 'checked';
        }else if($campos[0]['motivo'] == 2) {//Falta
            $checked3 = 'checked';
        }
    ?>
    <tr class='linhanormal'>
        <td>
            <b>Motivo:</b>
        </td>
        <td colspan='2'>
            <input type='radio' name='opt_motivo' id='label1' value='0' <?=$checked1;?> disabled>
            <label for='label1'>Entrada</label>
            &nbsp;
            <input type='radio' name='opt_motivo' id='label2' value='1' <?=$checked2;?> disabled>
            <label for='label2'>Saída</label>
            &nbsp;
            <input type='radio' name='opt_motivo' id='label3' value='2' <?=$checked3;?> disabled>
            <label for='label3'>Falta</label>
        </td>
    </tr>
    <tr class='linhanormal'>
    	<td>
            <b>Abonar:</b>
        </td>
        <?
//Controle com o campo Abonar ...
            if($campos[0]['abonar'] == 'S') {
                $selecteds = 'selected';
            }else if($campos[0]['abonar'] == 'N') {
                $selectedn = 'selected';
            }
//Controle de Status da Tela ...
            if($campos[0]['status_andamento'] == 0) {//Status de Chefia Liberar ...
                $rotulo             = 'Observação Chefia';
                $class_abonar       = 'combo';
                $disabled_abonar    = '';//Será habilitado
                $colspan = 'colspan="2"';
            }else {//Status de RH Liberar ...
                $rotulo = 'Observação Rh';
                $class_abonar       = 'textdisabled';
                $disabled_abonar    = 'disabled';//Sempre será desabilitado
            }
        ?>
        <td>
            <select name='cmb_abonar' title='Selecione uma opção p/ Abonar' class='<?=$class_abonar;?>' <?=$disabled_abonar;?>>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='S' <?=$selecteds;?>>SIM</option>
                <option value='N' <?=$selectedn;?>>NÃO</option>
            </select>
        </td>
    </tr>
<?
//Status de RH Liberar ou RH Liberado ...
        if($campos[0]['status_andamento'] == 1 || $campos[0]['status_andamento'] == 2) {
/***************************Descontar PLR***************************/
?>
    <tr class='linhanormal'>
        <td></td>
        <td colspan='2'>
            <?
                if($campos[0]['descontar_plr'] == 'S') $checked_descontar_plr = 'checked';
            ?>
            <input type='checkbox' name='chkt_descontar_plr' id='descontar_plr' value='S' title='Descontar no PLR' onclick='controlar_objetos(1)' class='checkbox' <?=$checked_descontar_plr;?>>
            <label for='descontar_plr'>
                Descontar no PLR -
            </label>
            <b>Hora Inicial: </b><input type='text' name="txt_hora_inicial_plr" value="<?=$hora_inicial_plr;?>" title="Digite a Hora Inicial do PLR" onkeyup="verifica(this, 'hora', '', '', event)" maxlength="5" size="6" class='textdisabled' disabled> -
            <b>Hora Final: </b><input type='text' name="txt_hora_final_plr" value="<?=$hora_final_plr;?>" title="Digite a Hora Final do PLR" onkeyup="verifica(this, 'hora', '', '', event)" maxlength="5" size="6" class='textdisabled' disabled>
        </td>
    </tr>
    <tr class='linhanormal'>
    	<td></td>
        <td colspan='2'>
        <?
/***************************Atestado***************************/
            if($campos[0]['atestado'] == 'S') $checked_atestado = 'checked';
        ?>
            <label for='atestado_vistado_chefe'>
                <input type='checkbox' name='chkt_atestado' id='atestado_vistado_chefe' value='S' title='Atestado vistado pelo Chefe' onclick='controlar_objetos(2)' class='checkbox' <?=$checked_atestado;?>>
                Atestado vistado pelo Chefe -
            </label>
            <b>Hora Inicial: </b><input type='text' name="txt_hora_inicial_atestado" value="<?=$hora_inicial_atestado;?>" title="Digite a Hora Inicial" maxlength="5" size="6" onkeyup="verifica(this, 'hora', '', '', event)" class='textdisabled' disabled> -
            <b>Hora Final: </b><input type='text' name="txt_hora_final_atestado" value="<?=$hora_final_atestado;?>" title="Digite a Hora Final" maxlength="5" size="6" onkeyup="verifica(this, 'hora', '', '', event)" class='textdisabled' disabled>
            <font color='darkblue'>
                <br/><b>* Funcionário deve entregar ao DP após Chefe Vistar</b>
            </font>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td></td>
        <td colspan='2'>
        <?
/***************************Descontar***************************/
            if($campos[0]['descontar'] == 'S') $checked_descontar = 'checked';
        ?>
            <input type='checkbox' name='chkt_descontar' id='descontar' value='S' title='Descontar' onclick='controlar_objetos(3)' class='checkbox' <?=$checked_descontar;?>>
            <label for='descontar'>
                Descontar -
            </label>
            <b>Hora Inicial: </b><input type='text' name="txt_hora_inicial_descontar" value="<?=$hora_inicial_descontar;?>" title="Digite a Hora Inicial do Descontar" maxlength='5' size='6' onkeyup="verifica(this, 'hora', '', '', event)" class='textdisabled' disabled> -
            <b>Hora Final: </b><input type='text' name="txt_hora_final_descontar" value="<?=$hora_final_descontar;?>" title="Digite a Hora Final do Descontar" maxlength='5' size='6' onkeyup="verifica(this, 'hora', '', '', event)" class='textdisabled' disabled>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhanormal'>
        <td>
            Observação Geral:
        </td>
        <td colspan='2'>
            <?=$campos[0]['observacao'];?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            <?=$rotulo;?>:
        </td>
        <td colspan='2'>
            <textarea name='txt_observacao_complementar' title='Digite a <?=$rotulo;?>' cols='80' rows='3' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='3'>
<?
/*******************************************************************************************************/
/*Quando essa 'Tela de Alterar' for acessada de Dentro do Relatório, então eu não posso mostrar esse botão de Voltar 
pois o intuito principal é fazer uma alteração apenas desse Atraso / Falta / Saída do Funcionário em espec.*/
        if($_GET['veio_tela_relatorio'] != 1) {
?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'alterar.php<?=$parametro;?>'" class='botao'>
<?
	}
?>
            <input type='button' name='cmd_redefinir' value='Redefinir' title='Redefinir' style='color:#ff9900' onclick="redefinir('document.form', 'REDEFINIR')" class='botao'>
            <input type='submit' name='cmd_salvar' value='Salvar' title='Salvar' style='color:green' class='botao'>
<?
        //Só quando a Ocorrência do Func estiver nos status de "RH Liberar" ou "RH Liberado" que exibo o botão abaixo ...
        if($campos[0]['status_andamento'] == 1 || $campos[0]['status_andamento'] == 2) {
?>
            <input type='button' name='cmd_incluir_banco_horas' value='Incluir Banco de Horas' title='Incluir Banco de Horas' onclick="html5Lightbox.showLightbox(7, '../banco_horas/incluir.php?pop_up=1&passo=2&id_funcionario_loop=<?=$campos[0]['id_funcionario_acompanhado'];?>&data_ocorrencia=<?=data::datetodata(substr($campos[0]['data_ocorrencia'], 0, 10), '/');?>')" style='color:red' class='botao'>
<?
        }
/*Quando essa 'Tela de Alterar' for acessada de Dentro do Relatório, então eu não posso mostrar esse botão 
de Fechar caso foi aberto o Registro errado ...*/
	if($_GET['veio_tela_relatorio'] == 1) {
?>
            <input type='button' name='cmd_fechar' value='Fechar' title='Fechar' onclick='window.close()' style='color:red' class='botao'>
<?
	}
?>
        </td>
    </tr>
</table>
<!--Relatório de Atraso / Falta / Saída do Funcionário-->
<?
/*******************************************************************************************************/
/*Quando essa 'Tela de Alterar' for acessada de Dentro do Relatório, então eu não posso mostrar esse Iframe abaixo 
pois nele eu já carrego o Relatório de Atraso / Falta / Saída do Funcionário ...
*/
    if($_GET['veio_tela_relatorio'] != 1) {
?>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr>
        <td>&nbsp;<td>
    </tr>
    <tr class='atencao' align='center'>
        <td>
            <iframe style='backgroud:#ccff00' name='relatorio_funcionario' id='relatorio_funcionario' frameborder="0" vspace="0" hspace="0" marginheight="0" marginwidth="0" scrolling="auto" title="Relatório Funcionário" width='980' height='320' src='relatorio.php?passo=1&id_funcionario_current=<?=$campos[0]['id_funcionario_acompanhado'];?>'></iframe>
        </td>
    </tr>
<?
    }
/*******************************************************************************************************/
?>
</table>
</form>
</body>
</html>
<?	
}else if($passo == 3) {
    if(isset($_POST['chkt_funcionario_acompanhamento'])) {//Significa que o usuário alterou as Ocorrências em Lote ...
        foreach($_POST['chkt_funcionario_acompanhamento'] as $i => $id_funcionario_acompanhamento) {
            //Controle com o Status do Andamento do Funcionário ...
            if($_POST['hdd_status_andamento'][$i] == 0) {//Saiu do Estágio de Portaria / Emissor - Chefia
                $status_andamento = 1;//Muda p/ o Estágio "Rh Liberar" ...
                //Saiu do Estágio de RH - Finaliza o Processo ou se já estiver no Estágio de RH Liberado ...
            }else if($_POST['hdd_status_andamento'][$i] == 1 || $_POST['hdd_status_andamento'][$i] == 2) {
                $status_andamento   = 2;//Muda p/ o Estágio "Rh Liberado" ...
            }
            $observacao = " `observacao` = CONCAT(`observacao`, '<br><b>Chefia: </b>".$_POST['txt_observacao_chefe'][$i]."') ";
            
            //Atualizando os dados no acompanhamento do Funcionário ...
            $sql = "UPDATE `funcionarios_acompanhamentos` SET $observacao, `status_andamento` = '$status_andamento', `abonar` = '".$_POST['cmb_abonar'][$i]."' WHERE `id_funcionario_acompanhamento` = '$id_funcionario_acompanhamento' LIMIT 1 ";
            bancos::sql($sql);
        }
    }else {//Significa que o usuário alterou as Ocorrências de modo Individual ...
        //Controle com o Status do Andamento do Funcionário ...
        if($_POST['hdd_status_andamento'] == 0) {//Saiu do Estágio de Portaria / Emissor - Chefia
            $status_andamento = 1;//Muda p/ o Estágio "Rh Liberar" ...
            //Saiu do Estágio de RH - Finaliza o Processo ou se já estiver no Estágio de RH Liberado ...
        }else if($_POST['hdd_status_andamento'] == 1 || $_POST['hdd_status_andamento'] == 2) {
            $chkt_descontar_plr = (!empty($_POST['chkt_descontar_plr']))    ? 'S' : 'N';
            $chkt_atestado      = (!empty($_POST['chkt_atestado']))         ? 'S' : 'N';
            $chkt_descontar     = (!empty($_POST['chkt_descontar']))        ? 'S' : 'N';
            $status_andamento   = 2;//Muda p/ o Estágio "Rh Liberado" ...
        }
        $observacao = " `observacao` = CONCAT(`observacao`, '<br><b>Chefia: </b>".$_POST['txt_observacao_complementar']."') ";
        
        //Atualizando os dados no acompanhamento do Funcionário ...
        $sql = "UPDATE `funcionarios_acompanhamentos` SET $observacao, `status_andamento` = '$status_andamento', `abonar` = '".$_POST['cmb_abonar']."', `descontar_plr` = '$chkt_descontar_plr', `hora_inicial_plr` = '".$_POST['txt_hora_inicial_plr']."', `hora_final_plr` = '".$_POST['txt_hora_final_plr']."', `atestado` = '$chkt_atestado', `hora_inicial_atestado` = '".$_POST['txt_hora_inicial_atestado']."', `hora_final_atestado` = '".$_POST['txt_hora_final_atestado']."', `descontar` = '$chkt_descontar', `hora_inicial_descontar` = '".$_POST['txt_hora_inicial_descontar']."', `hora_final_descontar` = '".$_POST['txt_hora_final_descontar']."' WHERE `id_funcionario_acompanhamento` = '".$_POST['id_funcionario_acompanhamento']."' LIMIT 1 ";
        bancos::sql($sql);
    }
/*Quando essa 'Tela de Alterar' for acessada de Dentro do Relatório, então eu não simplesmente fecho essa Tela 
e atualizo a Tela de Baixo do Relatório que chamou essa Tela*/
    if($_POST['veio_tela_relatorio'] == 1) {
?>
	<Script Language = 'JavaScript'>
            window.opener.document.location = 'relatorio.php<?=$parametro;?>'
            window.close()
	</Script>
<?
    }else {
        if(isset($_POST['chkt_funcionario_acompanhamento'])) {//Significa que o usuário alterou as Ocorrências em Lote ...
?>
	<Script Language = 'JavaScript'>
            window.location = 'alterar.php<?=$parametro;?>&valor=2'
	</Script>
<?
        }else {//Significa que o usuário alterou as Ocorrências de modo Individual ...
?>
	<Script Language = 'JavaScript'>
            window.location = 'alterar.php?parametro=<?=$_POST['hdd_parametro_pos_filtro'];?>&valor=2'
	</Script>
<?
        }
    }
}else {
?>
<html>
<head>
<title>.:: Alterar Atraso / Falta / Saída ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
</head>
<body onload='document.form.txt_nome.focus()'>
<form name='form' method='post' action=''>
<input type='hidden' name='passo' value='1'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Alterar Atraso / Falta / Saída
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome
        </td>
        <td>
            <input type='text' name='txt_nome' title='Digite o Nome' size='45' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Data da Ocorrência
        </td>
        <td>
            <input type='text' name='txt_data_ocorrencia' title='Digite a Data da Ocorrência' size='12' maxlength='10' onkeyup="verifica(this, 'data', '', '', event)" class='caixadetexto'>
            <img src = '../../../imagem/calendario.gif' width='12' height="12" border="0" alt="Calend&aacute;rio Normal" style='cursor:hand' onclick="nova_janela('../../../calendario/calendario.php?campo=txt_data_ocorrencia&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')"> Calendário
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Motivo
        </td>
        <td>
            <select name='cmb_motivo' title='Selecione o Motivo' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='0'>Entrada</option>
                <option value='1'>Saída</option>
                <option value='2'>Falta</option>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Observação
        </td>
        <td>
            <input type='text' name='txt_observacao' title='Digite a Observação' size='35' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            &nbsp;
        </td>
        <td>
            <input type='checkbox' name='chkt_itens_ultimos_45_dias' id='chkt_itens_ultimos_45_dias' value='1' title='Mostrar itens dos últimos 45 dias' class='checkbox'>
            <label for='chkt_itens_ultimos_45_dias'>
                Mostrar itens dos últimos 45 dias
            </label>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_limpar' value='Limpar' title='Limpar' onclick='document.form.txt_nome.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>