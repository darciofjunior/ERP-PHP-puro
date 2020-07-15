<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/estoque_acabado.php');
require('../../../../lib/intermodular.php');
segurancas::geral($PHP_SELF, '../../../../');

$mensagem[1] = "<font class='atencao'>SUA CONSULTA NÃO RETORNOU NENHUM RESULTADO.</font>";
$mensagem[2] = "<font class='confirmacao'>ESTOQUE EXCEDENTE REGISTRADO COM SUCESSO.</font>";

if($passo == 1) {
    $retorno = estoque_acabado::qtde_estoque($_GET['id_produto_acabado'], 1);
    //Busca desses dados a mais do PA ...
    $sql = "SELECT peso_unitario 
            FROM `produtos_acabados` 
            WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' LIMIT 1 ";
    $campos_pa = bancos::sql($sql);
?>
<html>
<title>.:: Incluir Estoque Excedente de PA ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/arred.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Quantidade ...
    if(!texto('form', 'txt_qtde', '1', '0123456789', 'QUANTIDADE', '1')) {
        return false
    }
//Embalado ...
    if(!combo('form', 'cmb_embalado', '', 'SELECIONE UMA OPÇÃO P/ EMBALADO !')) {
        return false
    }
//Prateleira ...
    if(!texto('form', 'txt_prateleira', '3', '0123456789', 'PRATELEIRA', '1')) {
        return false
    }
//Bandeja ...
    if(!texto('form', 'txt_bandeja', '1', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 'BANDEJA', '1')) {
        return false
    }
//Aqui eu verifico se a Qtde Digitada + Total Excedente é > que o Estoque Real ...
    var total_excedente = eval(strtofloat(document.form.txt_total_excedente.value))
    if((eval(document.form.txt_qtde.value) + total_excedente) > eval(strtofloat(document.form.txt_qtde_real.value))) {
        alert('ESTOQUE EXCEDENTE ESTÁ MAIOR QUE O ESTOQUE REAL !!!\nFAÇA O INVENTÁRIO !')
        document.form.txt_qtde.focus()
        document.form.txt_qtde.select()
    }
//Aqui eu desabilito o botão Salvar p/ não acontecer de o usuário clicar várias vezes ...
    document.form.cmd_salvar.disabled   = true
    document.form.cmd_salvar.className  = 'textdisabled'
}

function calcular_peso_total_pa() {
    var peso_unitario = strtofloat(document.form.txt_peso_unitario.value)
    document.form.txt_peso_total_pa.value = document.form.txt_qtde.value * peso_unitario
    document.form.txt_peso_total_pa.value = arred(document.form.txt_peso_total_pa.value, 8, 1)
}
</Script>
</head>
<body onload="document.form.txt_qtde.focus()">
<form name="form" method="post" action="<?=$PHP_SELF.'?passo=2';?>" onSubmit="return validar()">
<input type="hidden" name="id_produto_acabado" value="<?=$_GET['id_produto_acabado'];?>">
<table width='70%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            Incluir Estoque Excedente de PA 
            <font color='yellow'>
                <br/><?=intermodular::pa_discriminacao($_GET['id_produto_acabado']);?>
            </font>
            &nbsp;
            <a href = 'alterar.php?passo=1&id_produto_acabado=<?=$_GET['id_produto_acabado'];?>&pop_up=1' style='cursor:help' class='html5lightbox'>
                <img src = '../../../../imagem/propriedades.png' title='Relatório de Estoque Excedente' alt='Relatório de Estoque Excedente' border='0'>
            </a>
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Estoque Real
        </td>
        <td>
            Total Excedente
        </td>
        <td>
            Qtde
        </td>
        <td>
            Embalado
        </td>
        <td>
            Peso Unitário
        </td>
        <td>
            Peso Total PA
        </td>
        <td>
            Prateleira
        </td>
        <td>
            Bandeja
        </td>
        <td>
            Item Faltante (Opcional)
        </td>
        <td>
            Observação (Opcional)
        </td>
    </tr>
    <tr class='linhanormal' align='center'>
        <td>
            <input type='text' name="txt_qtde_real" value="<?=number_format($retorno[0], 2, ',', '.');?>" title="Qtde Estoque Real" size="8" maxlength="6" class='textdisabled' disabled>
        </td>
        <td>
            <?
                //Verifico se o Item possui Estoque Excedente, mas somente do que está "Em aberto" ...
                $sql = "SELECT SUM(qtde) AS total_excedente 
                        FROM `estoques_excedentes` 
                        WHERE `id_produto_acabado` = '$_GET[id_produto_acabado]' 
                        AND `status` = '0' ";
                $campos_excedente = bancos::sql($sql);
            ?>
            <input type='text' name="txt_total_excedente" value="<?=number_format($campos_excedente[0]['total_excedente'], 2, ',', '.');?>" title="Qtde Estoque Real" size="8" maxlength="6" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_qtde" title="Digite a Qtde" size="6" maxlength="4" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};calcular_peso_total_pa()" class='caixadetexto'>
        </td>
        <td>
            <select name='cmb_embalado' title='Selecione o Embalado' class='combo'>
                <option value='' style='color:red'>SELECIONE</option>
                <option value='S'>SIM</option>
                <option value='N'>NÃO</option>
            </select>
        </td>
        <td>
            <input type='text' name="txt_peso_unitario" value="<?=number_format($campos_pa[0]['peso_unitario'], 8, ',', '.')?>" size="15" maxlength="13" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_peso_total_pa" size="18" maxlength="15" class='textdisabled' disabled>
        </td>
        <td>
            <input type='text' name="txt_prateleira" title="Digite a Prateleira" size="6" maxlength="3" onkeyup="verifica(this, 'aceita', 'numeros', '', event);if(this.value == 0) {this.value = ''};if(this.value.length == 3) {document.form.txt_bandeja.focus()}" class='caixadetexto'>
        </td>
        <td>
            <input type='text' name="txt_bandeja" title="Digite a Bandeja" size="3" maxlength="1" onkeyup="if(this.value.length == 1) {document.form.txt_observacao.focus()}" class='caixadetexto'>
        </td>
        <td>
            <select name="cmb_produto_acabado_faltante" title="Selecione o Produto Acabado Faltante" class="combo">
            <?
                //Eu listo todos os PA(s) Padrões que já foram substituídos com o PA passado por parâmetro ...
                $sql = "SELECT IF(ps.id_produto_acabado_1 = '$_GET[id_produto_acabado]', ps.id_produto_acabado_2, ps.id_produto_acabado_1) AS id_pa 
                        FROM `pas_substituires` ps 
                        WHERE (ps.id_produto_acabado_1 = '$_GET[id_produto_acabado]') OR (ps.id_produto_acabado_2 = '$_GET[id_produto_acabado]') ";
                $campos_pas = bancos::sql($sql);
                $linhas_pas = count($campos_pas);
                    if($linhas_pas > 0) {//Se encontrar pelo menos 1 PA, então ...
                    for($j = 0; $j < $linhas_pas; $j++) $id_pas_exibir.= $campos_pas[$j]['id_pa'].', ';
                    $id_pas_exibir = substr($id_pas_exibir, 0, strlen($id_pas_exibir) - 2);
                }else {
                    $id_pas_exibir = 0;//Para não dar erro de SQL ...
                }
                //Trago todos os PA(s) que estão atrelados na tab. relacional ...
                $sql = "SELECT id_produto_acabado, CONCAT(referencia, ' * ', discriminacao) AS dados 
                        FROM `produtos_acabados` 
                        WHERE `id_produto_acabado` IN ($id_pas_exibir) ";
                echo combos::combo($sql);
            ?>
            </select>
        </td>
        <td>
            <textarea name='txt_observacao' rows='2' cols='50' maxlength='1000' class='caixadetexto'></textarea>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='10'>
            <input type='button' name="cmd_voltar" value="&lt;&lt; Voltar &lt;&lt;" title="Voltar" onclick="window.location = 'incluir.php<?=$parametro;?>'" class='botao'>
            <input type='button' name="cmd_limpar" value="Limpar" title="Limpar" onclick="redefinir('document.form', 'LIMPAR');document.form.txt_qtde.focus()" style="color:#ff9900" class='botao'>
            <input type="submit" name="cmd_salvar" value="Salvar" title="Salvar" style="color:green" class='botao'>
        </td>
    </tr>
</table>
</form>
</body>
</html>
<?
}else if($passo == 2) {
/************************************************************************************/
//Verifico se a Sessão não caiu ...
    if (!(session_is_registered('id_funcionario'))) {
?>
    <Script Language = 'JavaScript'>
        window.location = '../../../../html/index.php?valor=1'
    </Script>
<?
        exit;
    }
/************************************************************************************/
//Gerando a Observação Automática ...
    if(!empty($_POST['txt_observacao'])) $observacao_extra = ' - <b>Observação: </b> '.$_POST['txt_observacao'];
    $observacao = '<b>Ação <font color="darkblue">(Entrada de '.$_POST['txt_qtde'].' pçs)</font></b> - <b>Login:</b> '.$_SESSION['login'].' - <b>Data:</b> '.date('d/m/Y H:i:s').$observacao_extra;

/*******************************************************************************/
//Tratamento com os campos que tem que ficar NULL sem não tiver preenchidos  ...
/*******************************************************************************/
    $cmb_produto_acabado_faltante = (!empty($_POST[cmb_produto_acabado_faltante])) ? "'".$_POST[cmb_produto_acabado_faltante]."'" : 'NULL';

//Inserindo os Dados no BD ...
    $sql = "INSERT INTO `estoques_excedentes` (`id_estoque_excedente`, `id_produto_acabado`, `id_produto_acabado_faltante`, `qtde`, `embalado`, `prateleira`, `bandeja`, `observacao`) VALUES (NULL, '$_POST[id_produto_acabado]', $cmb_produto_acabado_faltante, '$_POST[txt_qtde]', '$_POST[cmb_embalado]', '$_POST[txt_prateleira]', '".strtoupper($_POST['txt_bandeja'])."', '$observacao') ";
    bancos::sql($sql);
?>
    <Script Language = 'JavaScript'>
        window.location = 'incluir.php<?=$parametro;?>&valor=2'
    </Script>
<?
}else {
/*Esse parâmetro de nível vai auxiliar na hora de retornar os valores para essa Tela Principal que fez a 
requisição desse arquivo Filtro*/
    $nivel_arquivo_principal = '../../../..';
//Aqui eu vou puxar a Tela única de Filtro de Produtos Acabados que serve para o Sistema Todo ...
    require('../../../classes/produtos_acabados/tela_geral_filtro.php');
//Se retornar pelo menos 1 registro
    if($linhas > 0) {
?>
<html>
<head>
<title>.:: Incluir Estoque Excedente de PA ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../lightbox/html5lightbox.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/sessao.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/tabela.js'></Script>
</head>
<body>
<table width='90%' border='0' align='center' cellspacing='1' cellpadding='1' onmouseover="total_linhas(this)">
    <tr align='center'>
        <td colspan='11'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            Incluir Estoque Excedente de PA
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td colspan="2" rowspan="2">
            <font title='Referência / Discriminação' style='cursor:help'>
                Ref / Disc
            </font>
        </td>
        <td rowspan="2">
            <font title="Operação de Custo" style='cursor:help'>
                O.C.
            </font>
        </td>
        <td rowspan="2">
            <font title='Unidade' style='cursor:help'>
                Un.
            </font>
        </td>
        <td rowspan="2">
            Compra<br> Produção
        </td>
        <td colspan='4'>
            Quantidade / Estoque
        </td>
        <td rowspan='2'>
            <font title='Média Mensal de Vendas' style='cursor:help'>
                M.M.V.
            </font>
        </td>
        <td rowspan='2'>
            Prazo de Entrega
        </td>
    </tr>
    <tr class='linhadestaque' align='center'>
        <td>
            Real
        </td>
        <td>
            Disp.
        </td>
        <td>
            <font title='Pendência' style='cursor:help'>
                Pend.
            </font>
        </td>
        <td>
            <font title='Comprometido' style='cursor:help'>
                Comp.
            </font>
        </td>
    </tr>
<?
        for ($i = 0; $i < $linhas; $i++) {
            $id_produto_acabado    	= $campos[$i]['id_produto_acabado'];
            $referencia 	       	= $campos[$i]['referencia'];
            $unidade                = $campos[$i]['sigla'];
            $operacao_custo	       	= $campos[$i]['operacao_custo'];
            $retorno                = estoque_acabado::qtde_estoque($id_produto_acabado, 1);                       
            $quantidade_estoque   	= $retorno[0];
            $qtde_pendente          = $retorno[7];
            $est_comprometido       = $retorno[8];
            $producao               = $retorno[2];
            $quantidade_disponivel  = $retorno[3];
//Aki verifica se o PA, possui prazo de Entrega
            $sql = "SELECT `prazo_entrega` 
                    FROM `estoques_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_prazo_entrega = bancos::sql($sql);
            if(count($campos_prazo_entrega) == 1) {
                $prazo_entrega  = strtok($campos_prazo_entrega[0]['prazo_entrega'], '=');
                $responsavel    = strtok($campos_prazo_entrega[0]['prazo_entrega'], '|');
                $responsavel    = substr(strchr($responsavel, '> '), 1, strlen($responsavel));
                $data_hora      = strchr($campos_prazo_entrega[0]['prazo_entrega'], '|');
                $data_hora      = substr($data_hora, 2, strlen($data_hora));
                $data           = data::datetodata(substr($data_hora, 0, 10), '/');
                $hora           = substr($data_hora, 11, 8);
            }
//Faz esse tratamento para o caso de não encontrar o responsável
            if(empty($responsavel)) {
                $string_apresentar = '&nbsp;';
            }else {
                $string_apresentar = 'Responsável: '.$responsavel.' - '.$data.' '.$hora;
            }
            $url = "window.location = 'incluir.php?passo=1&id_produto_acabado=".$id_produto_acabado."' ";
?>
    <tr class='linhanormal' onmouseover="sobre_celula(this, '#CCFFCC')" onmouseout="fora_celula(this, '#E8E8E8')" align='center'>
        <td onclick="<?=$url;?>" width='10'>
            <a href="#" class='link'>
                <img src = '../../../../imagem/seta_direita.gif' width='12' height='12' border='0'>
            </a>
        </td>
        <td onclick="<?=$url;?>" align="left">
            <a href="#" class="link">
            <?
                echo $referencia.' / '.intermodular::pa_discriminacao($id_produto_acabado);
                if(!empty($campos[$i]['observacao_pa'])) {
                    echo "&nbsp;-&nbsp;<img width='28' height='23' title='".$campos[$i]['observacao_pa']."' src='../../../imagem/olho.jpg'>";
                }
            ?>
            </a>
        </td>
        <td>
        <?
            if($operacao_custo == 0) {
                echo 'I';
            }else {
                echo 'R';
            }
        ?>
        </td>
        <td>
            <?=$unidade;?>
        </td>
        <td align="right">
                <?
                        //Aqui verifica se o PA tem relação com o PI ...
                        $sql = "SELECT id_produto_insumo 
                                FROM `produtos_acabados` 
                                WHERE `id_produto_acabado` = '$id_produto_acabado' 
                                AND `id_produto_insumo` > '0' 
                                AND `ativo` = '1' LIMIT 1 ";
                        $campos_pipa = bancos::sql($sql);
//Aqui o PI em relação com o PA e a OC. é do Tipo Revenda então mostra o link
                        if(count($campos_pipa) == 1 && $operacao_custo == 1) {
                ?>
                <a href="javascript:nova_janela('../../../classes/estoque/compra_producao.php?id_produto_acabado=<?=$id_produto_acabado;?>', 'pop', '', '', '', '', '500', '850', 'c', 'c', '', '', 's', 's', '', '', '')" class="link">
                        <?
                                $compra = estoque_acabado::compra_producao($id_produto_acabado);
                                //echo segurancas::number_format($compra, 2, '.').' / '.segurancas::number_format($producao, 2, '.');
                                if($compra<>0 && $producao<>0) {
                                        echo segurancas::number_format($compra, 2, '.').' / '.segurancas::number_format($producao, 2, '.');
                                } else {
                                        echo segurancas::number_format($compra, 2, '.').segurancas::number_format($producao, 2, '.');
                                }
                ?>
                </a>
                <?
//Aqui o PI em relação com o PA e a OC. é do Tipo Industrial
                        } else if(count($campos_pipa) == 1 && $operacao_custo == 0) {//Não mostra o link
                                $compra = estoque_acabado::compra_producao($id_produto_acabado);
                                //echo segurancas::number_format($compra, 2, '.').' / '.segurancas::number_format($producao, 2, '.');
                                if($compra<>0 && $producao<>0) {
                                        echo segurancas::number_format($compra, 2, '.').' / '.segurancas::number_format($producao, 2, '.');
                                } else {
                                        echo segurancas::number_format($compra, 2, '.').segurancas::number_format($producao, 2, '.');
                                }
//Aqui o PA não tem relação com o PI
                        }else {
                                echo segurancas::number_format($producao, 2, '.');
                        }
                ?>
        </td>
        <td align="right">
        <?
                //Verifico se o Item possui Estoque Excedente, mas somente do que está "Em aberto" ...
                $sql = "SELECT qtde 
                        FROM `estoques_excedentes` 
                        WHERE `id_produto_acabado` = '$id_produto_acabado' 
                        AND `status` = '0' LIMIT 1 ";
                $campos_excedente = bancos::sql($sql);

                if($campos_excedente[0]['qtde'] > 0) {//Se existir Estoque Excedente, exibo um link p/ ver Detalhes
        ?>
                <a href = 'alterar.php?passo=1&id_produto_acabado=<?=$id_produto_acabado;?>&pop_up=1' style='cursor:help' class='html5lightbox'>
        <?
                }
                echo number_format($quantidade_estoque, 2, ',', '.');
        ?>
                </a>
        </td>
        <td align="right">
        <?
            if($quantidade_disponivel < 0) {
                echo "<font color='red'>".segurancas::number_format($quantidade_disponivel, 2, '.')."</font>";
            }else if($quantidade_disponivel > 0) {
                echo segurancas::number_format($quantidade_disponivel,2, ".");
            }
        ?>
        </td>
        <td align="right">
        <?
/*Jogo o SQL mais acima para verificar por causa de um desvio que não mostrar os valores comprometidos <=0*/
            if($qtde_pendente > 0) echo segurancas::number_format($qtde_pendente, 2, '.');
        ?>
        </td>
        <td align="right">
        <?
            if($est_comprometido < 0) {
                echo "<font color='red'>".segurancas::number_format($est_comprometido,2,".")."</font>";
            }else if ($est_comprometido > 0) {
                echo segurancas::number_format($est_comprometido,2,".");
            }
        ?>
        </td>
        <td align="right">
        <?
            //Aki eu busco a média mensal de vendas do PA
            $sql = "SELECT mmv 
                    FROM `produtos_acabados` 
                    WHERE `id_produto_acabado` = '$id_produto_acabado' LIMIT 1 ";
            $campos_mmv = bancos::sql($sql);
            if($campos_mmv[0]['mmv'] > 0) echo number_format($campos2[0]['mmv'], 2, ',', '.');
        ?>
        </td>
        <td align="right" title="<?=$string_apresentar;?>" alt="<?=$string_apresentar;?>">
            <?=$prazo_entrega;?>
        </td>
    </tr>
<?
		}
?>
    <tr class='linhacabecalho' align='center'>
        <td colspan='11'>
            <input type='button' name="cmd_consultar_novamente" value="Consultar Novamente" title="Consultar Novamente" onclick="window.location = 'incluir.php'" class='botao'>
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
}
?>