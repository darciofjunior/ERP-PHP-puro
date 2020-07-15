<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/custos.php');

//Significa que essa Tela foi acessada de forma normal pelo Menu e não por dentro do Custo ...
if(empty($_POST['id_produto_acabado_custo'])) {
    require('../../../../../lib/menu/menu.php');
    segurancas::geral($PHP_SELF, '../../../../../');
}else {
    session_start('funcionarios');
}

$mensagem[1] = "<font class='confirmacao'>MÁQUINA(S) EXCLUÍDA(S) PARA ESTE GRUPO vs EMPRESA DIVISÃO.</font>";
$mensagem[2] = "<font class='confirmacao'>DADO(S) DE MÁQUINA(S) ALTERADO(S) PARA ESTE GRUPO vs EMPRESA DIVISÃO COM SUCESSO.</font>";

if($passo == 1) {
    if(!empty($_GET['id_gpa_vs_emp_div_vs_maquina'])) {//Exclui a Máquina do "Grupo vs Empresa Divisão" ...
        $sql = "DELETE FROM `gpas_vs_emps_divs_vs_maquinas` WHERE `id_gpa_vs_emp_div_vs_maquina` = '$_GET[id_gpa_vs_emp_div_vs_maquina]' LIMIT 1 ";
        bancos::sql($sql);
        $valor = 1;
    }
    //Procedimento normal de quando se carrega a Tela ...
    $id_gpa_vs_emp_div = ($_SERVER['REQUEST_METHOD'] == 'POST') ? $_POST['id_gpa_vs_emp_div'] : $_GET['id_gpa_vs_emp_div'];
    
    /**********************************************************************/
    /**************************Grupo de Cossinetes*************************/
    /**********************************************************************/
    if($_POST['id_grupo_pa'] == 9) {//Cossinetes Manual ...
        $vetor_valores_pa   = custos::dados_pa_para_custo_padrao($id_produto_acabado_custo);
        $diametro_aco       = $vetor_valores_pa['bitola1_aco'];
    /**********************************************************************/
    /****************************Grupo de Pinos****************************/
    /**********************************************************************/
    }else if($_POST['id_grupo_pa'] == 39 || $_POST['id_grupo_pa'] == 45) {//Pinos DIN 1 ou Pinos 1:50 ou Pinos 1:48 ...
        $vetor_valores_pa   = custos::dados_pa_para_custo_padrao($id_produto_acabado_custo);
        $diametro_aco       = $vetor_valores_pa['bitola1_aco'];//Por enquanto é um código redundante ...
    }
    
    if(!empty($_POST['id_produto_acabado_custo'])) {//Significa que essa Tela foi acessada por dentro do Custo ...
        /*Aqui eu faço a busca do "diâmetro exato" que foi cadastrado na tabela de acordo com a variável $diametro_aco que 
        foi declarada acima, essa "diâmetro exato" encontrado será utilizado + abaixo no SQL principal dessa tela ...*/
        $sql = "SELECT diametro_aco_menor_igual 
                FROM `gpas_vs_emps_divs_vs_maquinas` 
                WHERE `id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' 
                AND `diametro_aco_menor_igual` >= '$diametro_aco'  LIMIT 1 ";
        $campos     = bancos::sql($sql);
        $condicao   = " AND gedm.`diametro_aco_menor_igual` = '".$campos[0]['diametro_aco_menor_igual']."' ";
        
        /*Se foi clicado no botão "Custo Padrão" então registro o Funcionário e a "Data + Hora" que 
        foram feitas essa última alteração no Custo ...*/
        $sql = "UPDATE `produtos_acabados_custos` SET `id_funcionario` = '$_SESSION[id_funcionario]', `data_sys` = '".date('Y-m-d H:i:s')."' WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' LIMIT 1 ";
        bancos::sql($sql);
    }

    //Aqui vasculha todas as Máquinas atreladas para esta Empresa Divisão passada por parâmetro ...
    $sql = "SELECT gedm.`id_gpa_vs_emp_div_vs_maquina`, gedm.`id_maquina`, gedm.`diametro_aco_menor_igual`, 
            gedm.`pecas_hora`, gedm.`observacao`, m.`nome` 
            FROM `gpas_vs_emps_divs_vs_maquinas` gedm 
            INNER JOIN `maquinas` m ON m.`id_maquina` = gedm.`id_maquina` 
            WHERE gedm.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' 
            $condicao ORDER BY gedm.diametro_aco_menor_igual, m.nome ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);

    if(!empty($_POST['id_produto_acabado_custo'])) {//Significa que essa Tela foi acessada por dentro do Custo ...
        if($linhas == 0) {
?>
    <Script Language = 'JavaScript'>
        alert('NÃO HÁ MÁQUINA(S) CADASTRADA(S) PARA ESTE GRUPO VS EMPRESA DIVISÃO !')
        parent.html5Lightbox.finish()
    </Script>
<?
            exit;
        }
    }
?>
<html>
<head>
<title>.:: Máquina(s) do Grupo(s) vs Empresa(s) Divisão(ões) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function atualizar_4_etapa_custo() {
    var elementos = document.form.elements
    if(typeof(elementos['hdd_gpa_vs_emp_div_vs_maquina[]'][0]) == 'undefined') {
        var linhas = 1//Existe apenas 1 único elemento ...
    }else {
        var linhas = (elementos['hdd_gpa_vs_emp_div_vs_maquina[]'].length)
    }
    //Prepara a Tela p/ poder gravar no BD ...
    for(var i = 0; i < linhas; i++) document.getElementById('txt_tempo_horas'+i).value = strtofloat(document.getElementById('txt_tempo_horas'+i).value)
    //Desabilito esse botão p/ que o usuário não fique submetendo várias vezes o formulário p / o Servidor ...
    document.form.cmd_atualizar_4_etapa_custo.disabled    = true
    document.form.cmd_atualizar_4_etapa_custo.className   = 'textdisabled'
    document.form.submit()
}
    
function atualizar() {
    window.location = 'vs_maquinas.php?passo=1&id_gpa_vs_emp_div=<?=$_GET['id_gpa_vs_emp_div'];?>'
}
    
function excluir_item(id_gpa_vs_emp_div_vs_maquina) {
    var mensagem = confirm('DESEJA REALMENTE EXCLUIR ESTE ITEM ?')
    if(mensagem == false) {
        return false
    }else {
        window.location = 'vs_maquinas.php?passo=1&id_gpa_vs_emp_div=<?=$_GET['id_gpa_vs_emp_div'];?>&id_gpa_vs_emp_div_vs_maquina='+id_gpa_vs_emp_div_vs_maquina
    }
}

/*Criei essa função p/ impedir que o usuário digite nas caixas de texto que estão com o layout de desabilitadas, 
não desabilitei as caixas porque retardava muito o servidor na hora de habilitar as caixas via JavaScript 
na hora enviar p/ o banco de dados*/
function cursor_pecas_hora(indice) {
    document.getElementById('txt_pecas_hora'+indice).focus()
}
</Script>
</head>
<body>
<form name='form' method='post' action='<?=$PHP_SELF.'?passo=2';?>'>
<!--*****************Controles de Tela*****************-->
<input type='hidden' name='id_gpa_vs_emp_div' value='<?=$id_gpa_vs_emp_div;?>'>
<input type='hidden' name='id_grupo_pa' value='<?=$_POST['id_grupo_pa'];?>'>
<input type='hidden' name='id_produto_acabado_custo' value='<?=$_POST['id_produto_acabado_custo'];?>'>
<!--***************************************************-->
<table width='90%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='7'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='7'>
            Máquina(s) do Grupo(s) vs Empresa(s) Divisão(ões):
            <font color='yellow'>
            <?
                $sql = "SELECT CONCAT(gpa.nome, ' (', ed.razaosocial, ')') AS grupo_vs_empresa_divisao 
                        FROM `gpas_vs_emps_divs` ged 
                        INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
                        INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.`id_empresa_divisao` 
                        WHERE ged.`id_gpa_vs_emp_div` = '$id_gpa_vs_emp_div' LIMIT 1 ";
                $campos_grupo_pa_empresa_divisao = bancos::sql($sql);
                echo $campos_grupo_pa_empresa_divisao[0]['grupo_vs_empresa_divisao'];
            ?>
            </font>
        </td>
    </tr>
<?
    if($linhas == 0) {
?>
    <tr class='atencao' align='center'>
        <td colspan='6'>
            NÃO HÁ MÁQUINA(S) CADASTRADA(S) PARA ESTE GRUPO VS EMPRESA DIVISÃO.
        </td>
    </tr>
<?
    }else {
?>
    <tr class='linhadestaque' align='center'>
        <td>
            Diâm. Aço <=
        </td>
        <td>
            Máquina
        </td>
        <td>
            Pçs / Hora
        </td>
        <td>
            Setup (Hs)
        </td>
        <td>
            Observação
        </td>
        <td>
            Horas
        </td>
        <td width='30'>
            &nbsp;
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="cor_clique_celula(this, '#C6E2FF')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <?=number_format($campos[$i]['diametro_aco_menor_igual'], 1, ',', '.');?>
        </td>
        <td align='left'>
            <?=$campos[$i]['nome'];?>
        </td>
        <td>
            <input type='text' name='txt_pecas_hora[]' id='txt_pecas_hora<?=$i;?>' value='<?=$campos[$i]['pecas_hora'];?>' title='Digite as Peças / Hora' size='7' maxlength='7' onkeyup="verifica(this, 'aceita', 'numeros', '', event)" class='caixadetexto'>
        </td>
        <td>
        <?
            $vetor_valores_maquina  = custos::dados_maquina_para_custo_padrao($id_gpa_vs_emp_div, $campos[$i]['id_maquina'], $diametro_aco);
            echo number_format($vetor_valores_maquina['setup'], 1, ',', '.');
        ?>
        </td>
        <td>
            <input type='text' name='txt_observacao[]' id='txt_observacao<?=$i;?>' value='<?=$campos[$i]['observacao'];?>' title='Digite a Observação' class='caixadetexto'>
        </td>
        <td>
        <?
            if(!empty($_POST['id_produto_acabado_custo'])) {//Significa que essa Tela foi acessada por dentro do Custo ...
                $tempo_horas = custos::calculo_horas($_POST['id_produto_acabado_custo'], $campos[$i]['id_maquina'], $diametro_aco, $_POST['txt_diametro_menor']);
                $tempo_horas = number_format($tempo_horas, 1, ',', '.');
            }else {//Significa que essa Tela foi acessada de forma normal pelo Menu e não por dentro do Custo ...
                $tempo_horas = '';
            }
        ?>
            <input type='text' name='txt_tempo_horas[]' id='txt_tempo_horas<?=$i;?>' value='<?=$tempo_horas;?>' size='10' onfocus="cursor_pecas_hora('<?=$i;?>')" class='textdisabled'>
        </td>
        <td>
        <?
            //Significa que essa Tela foi acessada de forma normal pelo Menu e não por dentro do Custo ...
            if(empty($_POST['id_produto_acabado_custo'])) {
        ?>
            <img src = '../../../../../imagem/menu/excluir.png' border='0' onclick="excluir_item('<?=$campos[$i]['id_gpa_vs_emp_div_vs_maquina'];?>')" alt='Excluir Máquina' title='Excluir Máquina'>
        <?
            }
        ?>
            <input type='hidden' name='hdd_gpa_vs_emp_div_vs_maquina[]' id='hdd_gpa_vs_emp_div_vs_maquina<?=$i;?>' value='<?=$campos[$i]['id_gpa_vs_emp_div_vs_maquina'];?>'>
        </td>
    </tr>
<?
        }
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td align='left'>
        <?
            //Significa que essa Tela foi acessada de forma normal pelo Menu e não por dentro do Custo ...
            if(empty($_POST['id_produto_acabado_custo'])) {
        ?>
            <a href = 'incluir_maquina.php?id_gpa_vs_emp_div=<?=$id_gpa_vs_emp_div;?>' class='html5lightbox'>
                <font color='#FFFF00'>
                    Incluir Máquina(s)
                </font>
            </a>
        <?
            }
        ?>
        </td>
        <td colspan='6'>
        <?
            if(!empty($_POST['id_produto_acabado_custo'])) {//Significa que essa Tela foi acessada por dentro do Custo ...
        ?>
            <input type='button' name='cmd_atualizar_4_etapa_custo' value='Atualizar 4ª Etapa do Custo' title='Atualizar 4ª Etapa do Custo' onclick='atualizar_4_etapa_custo()' class='botao'>
        <?
            }else {//Significa que essa Tela foi acessada de forma normal pelo Menu e não por dentro do Custo ...
        ?>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'vs_maquinas.php'" class='botao'>
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
}else if($passo == 2) {
    if(!empty($_POST['id_produto_acabado_custo'])) {//Significa que essa Tela foi acessada por dentro do Custo ...
        /*Excluo todas as Máquinas na 4ª etapa desse $_POST['id_produto_acabado_custo'] que foi submetido 
        da tela anterior ...*/
        $sql = "DELETE FROM `pacs_vs_maquinas` WHERE `id_produto_acabado_custo` = '$_POST[id_produto_acabado_custo]' ";
        bancos::sql($sql);

        foreach($_POST['hdd_gpa_vs_emp_div_vs_maquina'] as $i => $id_gpa_vs_emp_div_vs_maquina) {
            //Através desse "$id_gpa_vs_emp_div_vs_maquina" do Loop, verifico qual é o id_maquina vinculado ...
            $sql = "SELECT id_maquina 
                    FROM `gpas_vs_emps_divs_vs_maquinas` 
                    WHERE `id_gpa_vs_emp_div_vs_maquina` = '$id_gpa_vs_emp_div_vs_maquina' LIMIT 1 ";
            $campos_maquina = bancos::sql($sql);
            /*Aqui esse id_maquina que foi encontrado acima é insirido na 4ª Etapa do Custo desse 
            $_POST[id_produto_acabado_custo] foi submetido da outra tela com seu respectivo tempo ...*/
            $sql = "INSERT INTO `pacs_vs_maquinas` (`id_pac_maquina`, `id_produto_acabado_custo`, `id_maquina`, `tempo_hs`) VALUES (NULL, '$_POST[id_produto_acabado_custo]', '".$campos_maquina[0]['id_maquina']."', '".$_POST['txt_tempo_horas'][$i]."') ";
            bancos::sql($sql);
        }
?>
    <Script Language = 'JavaScript'>
        alert('4ª ETAPA DO CUSTO ATUALIZADA COM SUCESSO !')
        parent.location = parent.location.href//Atualiza a tela abaixo que chamou essa Pop-UP DIV ...
    </Script>
<?
    }else {//Significa que essa Tela foi acessada de forma normal pelo Menu e não por dentro do Custo ...
        foreach($_POST['hdd_gpa_vs_emp_div_vs_maquina'] as $i => $id_gpa_vs_emp_div_vs_maquina) {
            $sql = "UPDATE `gpas_vs_emps_divs_vs_maquinas` SET `pecas_hora` = '".$_POST['txt_pecas_hora'][$i]."', `observacao` = '".$_POST['txt_observacao'][$i]."' WHERE `id_gpa_vs_emp_div_vs_maquina` = '$id_gpa_vs_emp_div_vs_maquina' LIMIT 1 ";
            bancos::sql($sql);
        }
?>
    <Script Language = 'JavaScript'>
        window.location = 'vs_maquinas.php?passo=1&id_gpa_vs_emp_div=<?=$_POST['id_gpa_vs_emp_div'];?>&valor=2'
    </Script>
<?
    }
}else {
?>
<html>
<head>
<title>.:: Grupo(s) vs Empresa(s) Divisão(ões) para Gerenciar Máquina(s) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../../js/sessao.js'></Script>
</head>
<body>
<table width='60%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            Grupo(s) vs Empresa(s) Divisão(ões) para Gerenciar Máquina(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan='2'>
            Grupo P.A vs Empresa Divisão
        </td>
    </tr>
<?
    //Aqui eu faço uma listagem de todas as máquinas da Fábrica que estão cadastradas no ERP ...
    $sql = "SELECT ged.`id_gpa_vs_emp_div`, CONCAT(gpa.`nome`, ' (', ed.`razaosocial`, ')') AS grupo_vs_empresa_divisao 
            FROM `gpas_vs_emps_divs` ged 
            INNER JOIN `grupos_pas` gpa ON gpa.`id_grupo_pa` = ged.`id_grupo_pa` 
            INNER JOIN `empresas_divisoes` ed ON ed.id_empresa_divisao = ged.`id_empresa_divisao` 
            ORDER BY gpa.`nome`, ed.`razaosocial` ";
    $campos = bancos::sql($sql);
    $linhas = count($campos);
    for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' align='center'>
        <td onclick="window.location = 'vs_maquinas.php?passo=1&id_gpa_vs_emp_div=<?=$campos[$i]['id_gpa_vs_emp_div'];?>'" width='10'>
            <img src = '../../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
        </td>
        <td align='left'>
            <a href='vs_maquinas.php?passo=1&id_gpa_vs_emp_div=<?=$campos[$i]['id_gpa_vs_emp_div'];?>' class='link'>
                <?=$campos[$i]['grupo_vs_empresa_divisao'];?>
            </a>
        </td>
    </tr>
<?
    }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            &nbsp;
        </td>
    </tr>
</table>
</body>
</html>
<?}?>