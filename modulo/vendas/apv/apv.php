<?
require('../../../lib/segurancas.php');
require('../../../lib/menu/menu.php');
require('../../../lib/data.php');
require('../../../lib/financeiros.php');
segurancas::geral($PHP_SELF, '../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>CLIENTE ALTERADO COM SUCESSO.</font>";
$mensagem[3] = "<font class='erro'>CLIENTE JÁ EXISTENTE.</font>";
$data_atual = date('Y-m-d');

if($passo == 1) {
?>
<html>
<head>
<title>.:: Relatório de Atendimento ao Cliente ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/tabela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
    var valor = false, elementos = document.form.elements
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].type == 'checkbox') {
            if(elementos[i].checked == true) valor = true
        }
    }
    if (valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        /*Já travo esse botão p/ que o usuário não fique clicando mais de 1 vez no mesmo e 
        enviando várias requisições p/ o Servidor ...*/
        document.form.cmd_avancar.disabled  = true
        document.form.cmd_avancar.className = 'textdisabled'
        document.form.action                = 'informacoes_apv.php'
    }
}

function imprimir_apv() {
    var valor = false, elementos = document.form.elements
    var id_clientes = ''
//Verifico se existe algum cliente selecionado ...
    for (var i = 0; i < elementos.length; i++) {
        if (elementos[i].type == 'checkbox' && elementos[i].name != 'chkt_tudo') {
            if (elementos[i].checked == true) {
                id_clientes+= elementos[i].value+', '
                valor = true
            }
        }
    }
//Se não estiver nenhuma opção selecionada, forço o usuário a preencher ...
    if(valor == false) {
        alert('SELECIONE UMA OPÇÃO !')
        return false
    }else {
        id_clientes = id_clientes.substr(0, id_clientes.length - 2)//Retira a última vírg. 
        var resposta = confirm('TEM CERTEZA DE QUE DESEJA IMPRIMIR ?')
        if(resposta == false) {
            return false
        }else {
            nova_janela('relatorio_pdf.php?id_clientes='+id_clientes, 'POP', '', '', '', '', 580, 980, 'c', 'c', '', '', 's', 's', '', '', '')
        }
    }
}

function carregando_tela() {
    var parametro_filtro = '<?=$parametro_filtro;?>'
    //Significa que essa Tela já foi submetida anteriormente ...
    if(parametro_filtro != '') window.location = '<?=$PHP_SELF;?>'+document.form.parametro_filtro.value
}
</Script>
</head>
<body onload='carregando_tela()'>
<form name='form' method='post' onsubmit='return validar()'>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center' onmouseover='total_linhas(this)'>
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
<?
//Tratamento com os Campos de "CNPJ ou CPF" ...
    $txt_cnpj_cpf   = str_replace('.', '', $txt_cnpj_cpf);
    $txt_cnpj_cpf   = str_replace('.', '', $txt_cnpj_cpf);
    $txt_cnpj_cpf   = str_replace('/', '', $txt_cnpj_cpf);
    $txt_cnpj_cpf   = str_replace('-', '', $txt_cnpj_cpf);

//Tratamento com as Combos agora ...
    if(!empty($cmb_uf))             $condicao_uf = " AND c.`id_uf` LIKE '$cmb_uf' ";
    if(!empty($cmb_representante))  $inner_join_clientes_representantes = "INNER JOIN `clientes_vs_representantes` cr ON cr.id_cliente = c.id_cliente AND cr.id_representante LIKE '$cmb_representante' ";
    if(!empty($cmb_novo_tipo_cliente)) {		
        foreach($cmb_novo_tipo_cliente as $id_novo_tipo_cliente) $valores_novo_tipo_cliente.= $id_novo_tipo_cliente.', ';
        $valores_novo_tipo_cliente      = substr($valores_novo_tipo_cliente, 0, strlen($valores_novo_tipo_cliente) - 2);
        $inner_join_novo_tipo_cliente   = " INNER JOIN `clientes_tipos` ct ON ct.`id_cliente_tipo` = c.`id_cliente_tipo` AND c.`id_cliente_tipo` IN ($valores_novo_tipo_cliente) ";
    }
/*Verifico todos os Clientes que contém Representante, independente do Funcionário logado 
e independente de ter Follow-Ups Registrados*/
    $sql = "SELECT DISTINCT(c.`id_cliente`), c.* 
            FROM `clientes` c 
            $inner_join_novo_tipo_cliente 
            $inner_join_clientes_representantes 
            WHERE c.`nomefantasia` LIKE '%$txt_nome_fantasia%' 
            AND c.`razaosocial` LIKE '%$txt_razao_social%' 
            AND c.`cnpj_cpf` LIKE '%$txt_cnpj_cpf%' 
            AND c.`ativo` = '1' 
            AND c.`bairro` LIKE '%$txt_bairro%' 
            AND c.`cidade` LIKE '%$txt_cidade%' 
            AND c.`credito` LIKE '$cmb_credito' 
            $condicao_uf 
            GROUP BY c.`id_cliente` ORDER BY c.`cidade`, c.`bairro`, c.`cep` ";
    $campos = bancos::sql($sql, $inicio, 20, 'sim', $pagina);
    $linhas = count($campos);
    if($linhas == 0) {
?>
        <Script Language = 'JavaScript'>
            window.location = 'apv.php?valor=1'
        </Script>
<?
    }else {
/*Essa linha só é gerada quando existe pelo menos 1 Follow-Up atrasado que está registrado,
porque senão dá erro de índice na hora em que eu clico na linha ...*/
        if(!empty($linhas_atrasados)) {
?>
    <tr align='center'>
        <td colspan='14'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            <font color='yellow'>
                APV (Atendimento Planejado de Vendas)
            </font>
            &nbsp;-&nbsp;Consultar Cliente(s)
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            <input type='checkbox' name='chkt_tudo' onclick="selecionar('form', 'chkt_tudo', totallinhas, '#E8E8E8')" title='Selecionar Tudo' class='checkbox'>
        </td>
        <td>
            Razão Social (Nome Fantasia)
        </td>
        <td>
            Tel Com
        </td>
        <td>
            Email
        </td>
        <td>
            Cr
        </td>
        <td>
            Tp
        </td>
        <td>
            Tp Fat.
        </td>		
        <td>
            Representante(s)
        </td>
        <td>
            Endereço
        </td>
        <td>
            Bairro
        </td>
        <td>
            Cidade
        </td>
        <td>
            UF
        </td>
        <td>
            País
        </td>
        <td>
            Cep
        </td>
    </tr>
<?
        for($i = 0; $i < $linhas; $i++) {
?>
    <tr class='linhanormal' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td>
            <input type='checkbox' name='chkt_cliente[]' value='<?=$campos[$i]['id_cliente'];?>' onclick="checkbox('form', 'chkt_tudo', '<?=$i;?>', '#E8E8E8')" class='checkbox'>
        </td>
        <td align='left'>
            <?=$campos[$i]['razaosocial'];?>
            <font color='brown'>
                <b>(<?=$campos[$i]['nomefantasia'];?>)</b>
            </font>
            &nbsp;
            <!--****************************Follow-UPs***************************-->
            <img src = '../../../imagem/menu/incluir.png' border='0' title='Registrar Follow-UP' alt='Registrar Follow-UP' onclick="nova_janela('../../classes/follow_ups/detalhes.php?id_cliente=<?=$campos[$i]['id_cliente'];?>&origem=6', 'OUTRAS', '', '', '', '', 600, 1000, 'c', 'c', '', '', 's', 's', '', '', '')">
            <!--*****************************************************************-->
            <?if($campos[$i]['matriz'] == 'S') echo "<font color='red'><b> (MATRIZ)</b></font>";?>
        </td>
        <td align='left'>
        <?
            if(!empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))    echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(!empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].' / '.$campos[$i]['ddd_com'].$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && !empty($campos[$i]['ddd_com']))     echo $campos[$i]['ddi_com'].$campos[$i]['ddd_com'].' / '.$campos[$i]['telcom'];
            if(empty($campos[$i]['ddi_com']) && empty($campos[$i]['ddd_com']))      echo $campos[$i]['telcom'];
        ?>
        </td>
        <td align='left'>
            <a href='mailto:<?=$campos[$i]['email'];?>'>
                <?=$campos[$i]['email'];?>
            </a>
        </td>
        <td>
            <font color='blue'>
                <?=financeiros::controle_credito($campos[$i]['id_cliente']);?>
            </font>
        </td>
        <td>
        <?
            $sql = "SELECT `tipo` 
                    FROM `clientes_tipos` 
                    WHERE `id_cliente_tipo` = '".$campos[$i]['id_cliente_tipo']."' LIMIT 1 ";
            $campos_cliente_tipo = bancos::sql($sql);
            echo $campos_cliente_tipo[0]['tipo'];
        ?>
        </td>
        <td>
        <?
            if($campos[$i]['tipo_faturamento'] == 'S') {
                echo 'Separadamente';
            }else if($campos[$i]['tipo_faturamento'] == 'Q') {
                echo 'Qualquer Empresa';
            }else if($campos[$i]['tipo_faturamento'] == 1) {
                echo 'Tudo Pela Albafer';
            }else if($campos[$i]['tipo_faturamento'] == 2) {
                echo 'Tudo Pela ToolMaster';
            }
        ?>
        </td>		
        <?
            $sql = "SELECT `sigla` 
                    FROM `ufs` 
                    WHERE `id_uf` = '".$campos[$i]['id_uf']."' LIMIT 1 ";
            $campos_uf = bancos::sql($sql);
        ?>
        <td align='left'>
        <?
            $sql = "SELECT r.`nome_fantasia` 
                    FROM `representantes` r 
                    INNER JOIN `clientes_vs_representantes` cr ON cr.`id_representante` = r.`id_representante` AND cr.`id_cliente` = '".$campos[$i]['id_cliente']."' 
                    GROUP BY r.`nome_fantasia` ";
            $campos_representantes = bancos::sql($sql);
            $linhas_representantes = count($campos_representantes);
            for($j = 0; $j < $linhas_representantes; $j++) {
                echo '* <b>'.$campos_representantes[$j]['nome_fantasia'].'</b>';
                if($j + 1 != $linhas_representantes) echo '<br/>';//Enquanto não chegar no último Representante, vou quebrando Linhas ...
            }
        ?>
        </td>
        <td align='left'>
        <?
            echo $campos[$i]['endereco'];
            //Daí sim printa o complemento
            if(!empty($campos[$i]['endereco'])) echo ', '.$campos[$i]['num_complemento'];
        ?>
        </td>
        <td align='left'>
            <?=$campos[$i]['bairro'];?>
        </td>
        <td align='left'>
            <?=$campos[$i]['cidade'];?>
        </td>
        <td>
            <?=$campos_uf[0]['sigla'];?>
        </td>
        <td>
        <?
            $sql = "SELECT pais 
                    FROM `paises` 
                    WHERE `id_pais` = '".$campos[$i]['id_pais']."' LIMIT 1 ";
            $campos_pais = bancos::sql($sql);
            echo $campos_pais[0]['pais'];
        ?>
        </td>
        <td>
            <?=$campos[$i]['cep'];?>
        </td>
    </tr>
<?
        }
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='14'>
            <input type='button' name='cmd_voltar' value='&lt;&lt; Voltar &lt;&lt;' title='Voltar' onclick="window.location = 'apv.php'" class='botao'>
            <input type='submit' name="cmd_avancar" value='&gt;&gt; Avançar &gt;&gt;' title='Avançar' class='botao'>
<?
/*Esse botão só aparece para os Vendedores Externos e + alguns usuários específicos ...
Verificação para ver se o usuário logado é vendedor Externo '27', Supervisor de Vendas '25', Vendedor Interno '47' 
ou 'Supervisor Interno' de Vendas 109 ...*/
        $sql = "SELECT id_funcionario 
                FROM `funcionarios` 
                WHERE (`id_cargo` = '27' OR `id_cargo` = '25' OR `id_cargo` = '47' OR `id_cargo` = '109') 
                AND `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if(count($campos) == 1) {//É vendedor externo, então aparece o botão
            $exibir_botao = 1;
//Não é vendedor externo, mas ...
        }else {
//Dárcio, Roberto, Wilson Chefe e Noronha
            if($_SESSION['id_login'] == 92 || $_SESSION['id_login'] == 22 || $_SESSION['id_login'] == 32 || $_SESSION['id_login'] == 43) $exibir_botao = 1;
        }
//Se exibir botão = 1 ...
        if($exibir_botao == 1) {
?>
            <input type='button' name="cmd_imprimir_apv" value="Imprimir APV" title="Imprimir APV" onclick="imprimir_apv()" class='botao'>
<?
        }
?>
        </td>
    </tr>
</table>
<!--Parâmetro que vem da Tela de TeleMarketing ...-->
<input type='hidden' name='telemarketing' value='<?=$telemarketing;?>'>
<?
//Essa mudança de variável é feita devido, o Sistema se perder com a Variável $parametro do Pop-Up de Follow-UP ...
    if(empty($parametro_filtro)) $parametro_filtro = $parametro;
?>
<!--Controle de Tela-->
<input type='hidden' name='parametro_filtro' value='<?=$parametro_filtro;?>'>
</form>
<table width='80%' border='0' cellspacing='1' cellpadding='1' align='center'>
    <tr>
        <td>&nbsp;</td>
    </tr>
    <tr align='center'>
        <td>
            <?=paginacao::print_paginacao('sim');?>
        </td>
    </tr>
</table>
</body>
</html>
<?
    }
}else {
?>
<html>
<head>
<title>.:: APV (Atendimento Planejado de Vendas) ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'Javascript' src = '../../../js/nova_janela.js'></Script>
<Script Language = 'Javascript' src = '../../../js/sessao.js'></Script>
<Script Language = 'JavaScript'>
function habilitar_relatorio() {
//Fará esse controle de habilitar e desabilitar, somente quando existir esse botão de Impressão ...
//Este aparece somente para o Gomes, Wilson ...
    if(typeof(document.form.cmd_relatorio) == 'object') {
        //Se o Representante estiver selecionado ...
        if(document.form.cmb_representante.value != '') {//Habilita o Botão de Impressão
            document.form.cmd_relatorio.disabled    = false
            document.form.cmd_relatorio.className   = 'caixadetexto'
        }else {//Desabilita o Botão de Impressão
            document.form.cmd_relatorio.disabled    = true
            document.form.cmd_relatorio.className   = 'textdisabled'
        }
    }
}

function controlar_somente_inativos() {
    var somente_inativos = (document.form.chkt_somente_inativos.checked == true) ? 'S' : 'N'
    window.location = '<?=$PHP_SELF;?>?somente_inativos='+somente_inativos
}

function relatorio() {
    document.form.action = 'rel_imp_por_representante.php'
    document.form.target = 'nova_janela'
    nova_janela('rel_imp_por_representante.php', 'nova_janela', '', '', '', '', '580', '980', 'c', 'c', '', '', 's', 's', '', '', '')
    document.form.submit()
}
</Script>
</head>
<body onload='document.form.txt_razao_social.focus()'>
<form name='form' method='post' action="<?=$PHP_SELF.'?passo=1';?>" target=''>
<input type='hidden' name='passo' value='1'>
<!--Parâmetro que vem da Tela de TeleMarketing ...-->
<input type='hidden' name='telemarketing' value='<?=$telemarketing;?>'>
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <font color='yellow'>
                APV (Atendimento Planejado de Vendas)
            </font>
            &nbsp;-&nbsp;Consultar Cliente(s)
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Razão Social
        </td>
        <td>
            <input type='text' name='txt_razao_social' title='Digite a Razão Social' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Nome Fantasia
        </td>
        <td>
            <input type='text' name='txt_nome_fantasia' title='Digite o Nome Fantasia' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            CNPJ / CPF
        </td>
        <td>
            <input type='text' name='txt_cnpj_cpf' title='Digite o CNPJ ou CPF' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Bairro
        </td>
        <td>
            <input type='text' name='txt_bairro' title='Digite o Bairro' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Cidade
        </td>
        <td>
            <input type='text' name='txt_cidade' title='Digite a Cidade' class='caixadetexto'>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Crédito
        </td>
        <td>
            <select name='cmb_credito' title='Selecione o Crédito' class='combo'>
                <option value='%' style='color:red'>SELECIONE</option>
                <option value='A'>A</option>
                <option value='B'>B</option>
                <option value='C'>C</option>
                <option value='D'>D</option>
            </select>
        </td>
    </tr>        
    <tr class='linhanormal'>
        <td>
            Estado
        </td>
        <td>
            <select name='cmb_uf' title='Selecione o Estado' class='combo'>
            <?
                $sql = "SELECT `id_uf`, `sigla` 
                        FROM `ufs` 
                        WHERE `ativo` = '1' ORDER BY `sigla` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Representante
        </td>
        <td>
        <select name='cmb_representante' title='Selecione o Representante' onchange='habilitar_relatorio()' class='combo'>
        <?
            //Esse parâmetro só é abastecido nessa tela quando o usuário clica no Checkbox Somente Inativos ...
            if($_GET['somente_inativos'] == 'S') {
                $ativo = 0;//A intenção principal é de visualizar somente os Representantes Inativos que possuem algum Cliente vinculado ...
                $inner_join_clientes = 'INNER JOIN `clientes_vs_representantes` cr ON cr.`id_representante` = r.`id_representante` ';
            }else {
                $ativo = 1;
            }

            $sql = "SELECT DISTINCT(r.`id_representante`), r.`nome_fantasia` 
                    FROM `representantes` r 
                    $inner_join_clientes 
                    WHERE r.`ativo` = '$ativo' 
                    ORDER BY r.`nome_fantasia` ";
            echo combos::combo($sql);
        ?>
        </select>
        <?
            //Só exibe esse botão para os funcionários => Roberto 66, Wilson Rodrigues 68, Wilson Nishimura 136 e Dárcio 98 / Netto 147 porque programam ...
            if($_SESSION['id_funcionario'] == 62 || $_SESSION['id_funcionario'] == 68 || $_SESSION['id_funcionario'] == 136 || $_SESSION['id_funcionario'] == 98 || $_SESSION['id_funcionario'] == 147) {
                $checked = ($_GET['somente_inativos'] == 'S') ? 'checked' : '';
        ?>
        <input type='checkbox' name='chkt_somente_inativos' value='S' title='Consultar todos os Representantes Inativos' onclick='controlar_somente_inativos()' class='checkbox' id='label1' <?=$checked;?>>
        <label for='label1'>
            <font color='darkblue'>
                <b>Somente Inativos c/ Clientes</b>
            </font>
        </label>
        -
        <input type='button' name='cmd_relatorio' value='Relatório de Impressão por Representante' title='Relatório de Impressão por Representante' onclick='relatorio()' class='textdisabled' disabled>
        <?
            }
        ?>
        </td>
    </tr>
    <tr class='linhanormal'>
        <td>
            Novo Tipo de Cliente
        </td>
        <td>
            <select name='cmb_novo_tipo_cliente[]' title='Selecione o Novo Tipo de Cliente' class='combo' size='5' multiple>
            <?
                $sql = "SELECT `id_cliente_tipo`, `tipo` 
                        FROM `clientes_tipos` 
                        ORDER BY `tipo` ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            <input type='reset' name='cmd_redefinir' value='Redefinir' title='Redefinir' onclick='document.form.txt_razao_social.focus()' style='color:#ff9900' class='botao'>
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?}?>
<pre>
<b><font color='red'>Observação:</font></b>
<pre>
* Só exibe Cliente(s) que contém representante(s).
</pre>