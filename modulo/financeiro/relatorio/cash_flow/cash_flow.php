<?
require('../../../../lib/segurancas.php');
require('../../../../lib/menu/menu.php');
require('../../../../lib/data.php');
require('../../../../lib/financeiros.php');
require('../../../../lib/genericas.php');
segurancas::geral($PHP_SELF, '../../../../');

function contas_apagar_e_receber($situacao, $data_inicial) {
    //Regras ...
    if($situacao == 1) {//Contas à Receber atrasadas à + de 60 dias ...
        $data_inicial           = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -365), '-');
        $data_final             = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -31), '-');
        $rotulo                 = '<font color="red">Ct. Receb. de '.data::datetodata($data_inicial, '/').' à '.data::datetodata($data_final, '/').')</font>';
        $condicao               = " BETWEEN '$data_inicial' AND '$data_final' ";
        $class                  = 'linhanormaldestaque';
        $align                  = 'left';
    }else if($situacao == 2) {//Contas à Receber atrasadas à + de 30 dias ...
        $data_inicial           = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -30), '-');
        $data_final             = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -16), '-');
        $rotulo                 = '<font color="red">Ct. Receb. de '.data::datetodata($data_inicial, '/').' à '.data::datetodata($data_final, '/').')</font>';
        $condicao               = " BETWEEN '$data_inicial' AND '$data_final' ";
        $class                  = 'linhanormaldestaque';
        $align                  = 'left';
    }else if($situacao == 3) {//Contas à Receber atrasadas à + de 15 dias ...
        $data_inicial           = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -15), '-');
        $data_final             = data::datatodate(data::adicionar_data_hora(date('d/m/Y'), -1), '-');
        $rotulo                 = '<font color="red">Ct. Receb. de '.data::datetodata($data_inicial, '/').' à '.data::datetodata($data_final, '/').')</font>';
        $condicao               = " BETWEEN '$data_inicial' AND '$data_final' ";
        $class                  = 'linhanormaldestaque';
        $align                  = 'left';
    }else if($situacao == 4) {//Contas à Pagar atrasadas Não Urgentes ...
        $rotulo                 = '<font color="red">Ct. Pagar atrasadas não Urgentes</font>';
        $condicao               = " <= '$data_inicial' AND `urgente` = 'N' ";
        $class                  = 'linhanormaldestaque';
        $align                  = 'left';
    }else if($situacao == 5) {//Contas à Pagar atrasadas Urgentes ...
        $rotulo                 = '<font color="red">Ct. Pagar atrasadas Urgentes</font>';
        $condicao               = " <= '$data_inicial' AND `urgente` = 'S' ";
        $class                  = 'linhanormaldestaque';
        $align                  = 'left';
    }else if($situacao == 6) {//Contas do dia que foi passado no Loop - DIÁRIA ...
        $rotulo                 = data::datetodata($data_inicial, '/');
        $condicao               = " = '$data_inicial' ";
        $class                  = 'linhanormal';
        $align                  = 'center';
    }
    
    /*Infelizmente em alguns trechos abaixo, eu trabalho com variáveis $GLOBAIS porque preciso desses valores fora dessa função e seriam muitas 
    variáveis para retornar através dessa, então desse modo acaba sendo mais fácil apesar de utilizar mais memória com esses tipos de variáveis ...*/
    
    /**************************************************************************/
    /******************************Contas à Pagar******************************/
    /**************************************************************************/
    if($situacao == 4 || $situacao == 5 || $situacao == 6) {
        //Busco o valor que temos à Pagar da $data_inicial passada por parâmetro nos status de "Em Aberto / Parcial" ...
        $sql = "SELECT `id_conta_apagar`, `id_empresa` 
                FROM `contas_apagares` 
                WHERE `data_vencimento_alterada` $condicao 
                AND `ativo` = '1' 
                AND `urgente` = 'S' 
                AND `status` IN (0, 1) ORDER BY `id_empresa` ";
        $campos_contas_apagar = bancos::sql($sql);
        $linhas_contas_apagar = count($campos_contas_apagar);
        for($i = 0; $i < $linhas_contas_apagar; $i++) {
            $calculos_conta_pagar = financeiros::calculos_conta_pagar($campos_contas_apagar[$i]['id_conta_apagar']);
            switch((int)$campos_contas_apagar[$i]['id_empresa']) {
                case 1://Empresa Albafer ...
                    $contas_apagar_albafer+= $calculos_conta_pagar['valor_reajustado'];
                break;
                case 2://Empresa Tool Master ...
                    $contas_apagar_tool_master+= $calculos_conta_pagar['valor_reajustado'];
                break;
                case 4://Empresa Grupo ...
                    $contas_apagar_grupo+= $calculos_conta_pagar['valor_reajustado'];
                break;
            }
        }
    }
    
    /**************************************************************************/
    /*****************************Contas à Receber*****************************/
    /**************************************************************************/
    if($situacao == 1 || $situacao == 2 || $situacao == 3 || $situacao == 6) {
        /*Busco o valor que temos à Receber da $data_inicial passada por parâmetro nos status de "Em Aberto / Parcial" 

        Obs: Não levo em conta as Contas à receber em que o "Tipo de Recebimento" está como "Protestada" 7 ou em "Cartório" 9, 
        essas só Deus sabe quando vamos receber ...*/
        $sql = "SELECT `id_conta_receber`, `id_empresa` 
                FROM `contas_receberes` 
                WHERE `id_tipo_recebimento` NOT IN (7, 9) 
                AND `data_recebimento` = '$data_inicial' 
                AND `ativo` = '1' 
                AND `status` IN (0, 1) ORDER BY `id_empresa` ";
        $campos_contas_receber  = bancos::sql($sql);
        $linhas_contas_receber = count($campos_contas_receber);
        for($i = 0; $i < $linhas_contas_receber; $i++) {
            $calculos_conta_receber = financeiros::calculos_conta_receber($campos_contas_receber[$i]['id_conta_receber']);
            switch((int)$campos_contas_receber[$i]['id_empresa']) {
                case 1://Empresa Albafer ...
                    $contas_receber_albafer+= $calculos_conta_receber['valor_reajustado'];
                break;
                case 2://Empresa Tool Master ...
                    $contas_receber_tool_master+= $calculos_conta_receber['valor_reajustado'];
                break;
                case 4://Empresa Grupo ...
                    $contas_receber_grupo+= $calculos_conta_receber['valor_reajustado'];
                break;
            }
        }

        /*Busco o valor que temos à Receber em Caução da $data_inicial passada por parâmetro nos status de "Em Aberto / Parcial" ...
        Tipos de Recebimentos = 11 Cobrança Caucionada, 12 Desconto e 15 Acerto a Pagar vs Receber (Troca de Ferramentas)*/
        $sql = "SELECT `id_conta_receber`, `id_empresa` 
                FROM `contas_receberes` 
                WHERE `id_tipo_recebimento` IN (11, 12, 15) 
                AND `data_recebimento` = '$data_inicial' 
                AND `ativo` = '1' 
                AND `status` IN (0, 1) ORDER BY `id_empresa` ";
        $campos_contas_receber  = bancos::sql($sql);
        $linhas_contas_receber = count($campos_contas_receber);
        for($i = 0; $i < $linhas_contas_receber; $i++) {
            $calculos_conta_receber = financeiros::calculos_conta_receber($campos_contas_receber[$i]['id_conta_receber']);
            switch((int)$campos_contas_receber[$i]['id_empresa']) {
                case 1://Empresa Albafer ...
                    $contas_receber_albafer_caucao+= $calculos_conta_receber['valor_reajustado'];
                break;
                case 2://Empresa Tool Master ...
                    $contas_receber_tool_master_caucao+= $calculos_conta_receber['valor_reajustado'];
                break;
                case 4://Empresa Grupo ...
                    $contas_receber_grupo_caucao+= $calculos_conta_receber['valor_reajustado'];
                break;
            }
        }

        //Busco o valor que temos à Receber de contas já recebidas com cheques "à Compensar" na $data_inicial passada por parâmetro ...
        $sql = "SELECT SUM(crq.`valor`) AS valores, cr.`id_empresa` 
                FROM `contas_receberes` cr 
                INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_conta_receber` = cr.`id_conta_receber` 
                INNER JOIN `cheques_clientes` cc ON cc.`id_cheque_cliente` = crq.`id_cheque_cliente` AND cc.`status` = '1' AND cc.`data_vencimento` = '$data_inicial' 
                ORDER BY cr.`id_empresa` ";
        $campos_contas_receber  = bancos::sql($sql);
        $linhas_contas_receber = count($campos_contas_receber);
        for($i = 0; $i < $linhas_contas_receber; $i++) {
            $exibir = 1;//para exibir na tela
            switch((int)$campos_contas_receber[$i]['id_empresa']) {
                case 1://Empresa Albafer
                    $contas_receber_albafer_cheque+= $campos_contas_receber[$i]['valores'];
                break;
                case 2://Empresa Tool Master
                    $contas_receber_tool_master_cheque+= $campos_contas_receber[$i]['valores'];
                break;
                case 4://Empresa Grupo
                    $contas_receber_grupo_cheque+= $campos_contas_receber[$i]['valores'];
                break;
            }
        }
    }
?>
    <tr class='<?=$class;?>' align='right'>
        <td align='<?=$align;?>'>
            <a href = '' class='html5lightbox'>
                <?=$rotulo;?>
            </a>
        </td>
        <td>
            <font color='red'>
            <?
                echo segurancas::number_format($contas_apagar_albafer, 2, '.');
                $GLOBALS['total_contas_apagar_albafer']+= $contas_apagar_albafer;
            ?>
            </font>
        </td>
        <td>
        <?
            echo segurancas::number_format($contas_receber_albafer, 2, '.');
            $GLOBALS['total_contas_receber_albafer']+= $contas_receber_albafer;
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($contas_receber_albafer_caucao, 2, '.');
            $GLOBALS['total_contas_receber_albafer_caucao']+= $contas_receber_albafer_caucao;
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($contas_receber_albafer_cheque, 2, '.');
            $GLOBALS['total_contas_receber_albafer_cheque']+= $contas_receber_albafer_cheque;
        ?>
        </td>
        <td>
        <?
            $contas_apagar_receber_albafer = ($contas_receber_albafer + $contas_receber_albafer_cheque) - $contas_apagar_albafer;
            
            //Valores Negativos mostro em Vermelho ...
            $color = ($contas_apagar_receber_albafer < 0) ? 'red' : '';
        ?>
            <font color='<?=$color;?>'>
                <?=segurancas::number_format($contas_apagar_receber_albafer, 2, '.');?>
            </font>
        <?
            $GLOBALS['total_contas_apagar_receber_albafer']+= $contas_apagar_receber_albafer;
        ?>
        </td>
        <td>
            <font color='red'>
            <?
                echo segurancas::number_format($contas_apagar_tool_master, 2, '.');
                $GLOBALS['total_contas_apagar_tool_master']+= $contas_apagar_tool_master;
            ?>
            </font>
        </td>
        <td>
        <?
            echo segurancas::number_format($contas_receber_tool_master, 2, '.');
            $GLOBALS['total_contas_receber_tool_master']+= $contas_receber_tool_master;
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($contas_receber_tool_master_caucao, 2, '.');
            $GLOBALS['total_contas_receber_tool_master_caucao']+= $contas_receber_tool_master_caucao;
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($contas_receber_tool_master_cheque, 2, '.');
            $GLOBALS['total_contas_receber_tool_master_cheque']+= $contas_receber_tool_master_cheque;
        ?>
        </td>
        <td>
        <?
            $contas_apagar_receber_tool_master = ($contas_receber_tool_master + $contas_receber_tool_master_cheque) - $contas_apagar_tool_master;
            
            //Valores Negativos mostro em Vermelho ...
            $color = ($contas_apagar_receber_tool_master < 0) ? 'red' : '';
        ?>
            <font color='<?=$color;?>'>
                <?=segurancas::number_format($contas_apagar_receber_tool_master, 2, '.');?>
            </font>
        <?
            $GLOBALS['total_contas_apagar_receber_tool_master']+= $contas_apagar_receber_tool_master;
        ?>
        </td>
        <td>
            <font color='red'>
            <?
                echo segurancas::number_format($contas_apagar_grupo, 2, '.');
                $GLOBALS['total_contas_apagar_grupo']+= $contas_apagar_grupo;
            ?>
            </font>
        </td>
        <td>
        <?
            echo segurancas::number_format($contas_receber_grupo, 2, '.');
            $GLOBALS['total_contas_receber_grupo']+= $contas_receber_grupo;
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($contas_receber_grupo_caucao, 2, '.');
            $GLOBALS['total_contas_receber_grupo_caucao']+= $contas_receber_grupo_caucao;
        ?>
        </td>
        <td>
        <?
            echo segurancas::number_format($contas_receber_grupo_cheque, 2, '.');
            $GLOBALS['total_contas_receber_grupo_cheque']+= $contas_receber_grupo_cheque;
        ?>
        </td>
        <td>
        <?
            $contas_apagar_receber_grupo = ($contas_receber_grupo + $contas_receber_grupo_cheque) - $contas_apagar_grupo;
            
            //Valores Negativos mostro em Vermelho ...
            $color = ($contas_apagar_receber_grupo < 0) ? 'red' : '';
        ?>
            <font color='<?=$color;?>'>
                <?=segurancas::number_format($contas_apagar_receber_grupo, 2, '.');?>
            </font>
        <?
            $GLOBALS['total_contas_apagar_receber_grupo']+= $contas_apagar_receber_grupo;
        ?>
        </td>
        <td>
        <?
            $contas_apagar_receber_todas_empresas = ($contas_apagar_receber_albafer + $contas_apagar_receber_tool_master + $contas_apagar_receber_grupo);
            
//Valores Negativos mostro em Vermelho ...
            $color = ($contas_apagar_receber_todas_empresas < 0) ? 'red' : '';
        ?>
            <font color='<?=$color;?>'>
                <?=segurancas::number_format($contas_apagar_receber_todas_empresas, 2, '.');?>
            </font>
        <?
            $GLOBALS['total_contas_apagar_receber_todas_empresas']+= $contas_apagar_receber_todas_empresas;
        ?>
        </td>
    </tr>
<?
}
?>
<html>
<head>
<title>.:: CashFlow ::.</title>
<meta http-equiv = 'Content-Type' content = 'text/html; charset=iso-8859-1'>
<meta http-equiv = 'cache-control' content = 'no-store'>
<meta http-equiv = 'pragma' content = 'no-cache'>
<link href = '../../../../css/layout.css' type = 'text/css' rel = 'stylesheet'>
<Script Language = 'JavaScript' Src = '../../../../js/data.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/geral.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/nova_janela.js'></Script>
<Script Language = 'JavaScript' Src = '../../../../js/validar.js'></Script>
<Script Language = 'JavaScript'>
function validar() {
//Data Inicial
    if(!data('form', 'txt_data_inicial', '4000', 'INÍCIO')) {
        return false
    }
//Data Final
    if(!data('form', 'txt_data_final', '4000', 'FIM')) {
        return false
    }
//Empresa
    if(!combo('form', 'cmb_empresa', '', 'SELECIONE UMA EMPRESA !')) {
        return false
    }
    var data_inicial    = document.form.txt_data_inicial.value
    var data_final      = document.form.txt_data_final.value
    data_inicial        = data_inicial.substr(6, 4) + data_inicial.substr(3, 2) + data_inicial.substr(0, 2)
    data_final          = data_final.substr(6, 4) + data_final.substr(3, 2) + data_final.substr(0, 2)
    data_inicial        = eval(data_inicial)
    data_final          = eval(data_final)

    if(data_final < data_inicial) {
        alert('DATA FINAL INVÁLIDA !!!\n DATA FINAL MENOR DO QUE A DATA INICIAL !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
/**Verifico se o intervalo entre Datas é > do que 1 ano. Faço essa verificação porque se o usuário 
colocar um intervalo de datas muito distantes, então acaba sobrecarregando o Banco de Dados**/
    var dias = diferenca_datas(document.form.txt_data_inicial, document.form.txt_data_final)
    if(dias > 365) {
        alert('INTERVALO DE DATAS INVÁLIDO !!!\n INTERVALO DE DATAS SUPERIOR A HUM ANO !')
        document.form.txt_data_final.focus()
        document.form.txt_data_final.select()
        return false
    }
}
</Script>
</head>
<body>
<form name='form' method='post' action='' onsubmit='return validar()'>
<table width='98%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr align='center'>
        <td colspan='2'>
            <?=$mensagem[$valor];?>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td colspan='2'>
            CashFlow
        </td>
    </tr>
    <tr class='linhadestaque'>
        <td colspan='2'>
            <?
                if(empty($_POST['txt_data_inicial'])) {
                    $data_inicial 	= date('d/m/Y');
                    $data_final 	= data::adicionar_data_hora(date('d/m/Y'), 30);
                }else {
                    $data_inicial 	= $_POST['txt_data_inicial'];
                    $data_final 	= $_POST['txt_data_final'];
                }
            ?>
            <p>Data Inicial: 
            <input type='text' name='txt_data_inicial' value='<?=$data_inicial;?>' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_inicial&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            Data Final:
            <input type='text' name='txt_data_final' value='<?=$data_final;?>' onkeyup="verifica(this, 'data', '', '', event)" size='12' maxlength='10' class='caixadetexto'>
            <img src = '../../../../imagem/calendario.gif' width='12' height='12' border='0' alt='Calend&aacute;rio Normal' style='cursor:hand' onclick="nova_janela('../../../../calendario/calendario.php?campo=txt_data_final&tipo_retorno=1', 'CALENDÁRIO', '', '', '', '', 270, 240, 'c', 'c')">
            &nbsp;
            <input type='submit' name='cmd_consultar' value='Consultar' title='Consultar' class='botao'>
        </td>
    </tr>
<?
if(!empty($_POST['txt_data_inicial'])) {
?>
</table>
<table width='98%' border='0' cellspacing ='1' cellpadding='1' align='center'>
    <tr class='linhacabecalho' align='center'>
        <td rowspan='3'>
            Data
        </td>
        <td colspan='5'>
            Albafer
        </td>
        <td colspan='5'>
            Tool Master
        </td>
        <td colspan='5'>
            Grupo
        </td>
        <td rowspan='3'>
            Total
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td rowspan='2'>
            Contas à Pagar
            <img src = '../../../../imagem/bloco_negro.gif' title='Contas à Pagar em Aberto / Parcialmente Pagas' width='8' height='8' style='cursor:help'>
            <font color='yellow'>
                <br/>(A)
            </font>
        </td>
        <td colspan='3'>
            Contas à Receber
        </td>
        <td rowspan='2'>
            Saldo
            <font color='yellow'>
                <br/>(B+D-A)
            </font>
        </td>
        <td rowspan='2'>
            Contas à Pagar
            <img src = '../../../../imagem/bloco_negro.gif' title='Contas à Pagar em Aberto / Parcialmente Pagas' width='8' height='8' style='cursor:help'>
            <font color='yellow'>
                <br/>(A)
            </font>
        </td>
        <td colspan='3'>
            Contas à Receber
        </td>
        <td rowspan='2'>
            Saldo
            <font color='yellow'>
                <br/>(B+D-A)
            </font>
        </td>
        <td rowspan='2'>
            Contas à Pagar
            <img src = '../../../../imagem/bloco_negro.gif' title='Contas à Pagar em Aberto / Parcialmente Pagas' width='8' height='8' style='cursor:help'>
            <font color='yellow'>
                <br/>(A)
            </font>
        </td>
        <td colspan='3'>
            Contas à Receber
        </td>
        <td rowspan='2'>
            Saldo
            <font color='yellow'>
                <br/>(B+D-A)
            </font>
        </td>
    </tr>
    <tr class='linhacabecalho' align='center'>
        <td>
            Contas
            <font color='yellow'>
                (B)
            </font>
        </td>
        <td>
            Caução
            <font color='yellow'>
                (C)
            </font>
        </td>
        <td>
            Cheques
            <font color='yellow'>
                (D)
            </font>
        </td>
        <td>
            Contas
            <font color='yellow'>
                (B)
            </font>
        </td>
        <td>
            Caução
            <font color='yellow'>
                (C)
            </font>
        </td>
        <td>
            Cheques
            <font color='yellow'>
                (D)
            </font>
        </td>
        <td>
            Contas
            <font color='yellow'>
                (B)
            </font>
        </td>
        <td>
            Caução
            <font color='yellow'>
                (C)
            </font>
        </td>
        <td>
            Cheques
            <font color='yellow'>
                (D)
            </font>
        </td>
    </tr>
<?
    //Tratamento com as Datas no Formato em que o BD reconhece ...
    $data_inicial   = data::datatodate($data_inicial, '-');
    $data_final     = data::datatodate($data_final, '-');

    contas_apagar_e_receber(1, $data_inicial);
    contas_apagar_e_receber(2, $data_inicial);
    contas_apagar_e_receber(3, $data_inicial);
    contas_apagar_e_receber(4, $data_inicial);
    contas_apagar_e_receber(5, $data_inicial);

    while($data_inicial <= $data_final) {
        contas_apagar_e_receber(6, $data_inicial);

        $data_inicial   = data::adicionar_data_hora(data::datetodata($data_inicial, '/'), 1);
        $data_inicial   = data::datatodate($data_inicial, '-');
    }
?>
    </tr>
    <tr class='linhadestaque' align='right'>
        <td>
            &nbsp;
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_apagar_albafer'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_receber_albafer'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_receber_albafer_caucao'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_receber_albafer_cheque'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_apagar_receber_albafer'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_apagar_tool_master'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_receber_tool_master'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_receber_tool_master_caucao'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_receber_tool_master_cheque'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_apagar_receber_tool_master'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_apagar_grupo'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_receber_grupo'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_receber_grupo_caucao'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_receber_grupo_cheque'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_apagar_receber_grupo'], 2, '.');?>
        </td>
        <td>
            <?=segurancas::number_format($GLOBALS['total_contas_apagar_receber_todas_empresas'], 2, '.');?>
        </td>
    </tr>
</table>
</body>
<pre>
    Conta à Receber:

        <b>Contas (B)</b> => Não leva em conta as Contas à receber em que o "Tipo de Recebimento" está como "Protestada" ou "Cartório", 
        essas só Deus sabe quando vamos receber.
</pre>
</html>
<?}?>