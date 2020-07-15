<?
require('../../../lib/pdf/fpdf.php');
require('../../../lib/segurancas.php');
require('../../../lib/cascates.php');
require('../../../lib/custos.php');
require('../../../lib/data.php');
segurancas::geral('/erp/albafer/modulo/vendas/apv/apv.php', '../../../');

/******************************************************************************************/
/*****************************************Relatório****************************************/
/******************************************************************************************/
//Aqui eu abasteço na tabela os campos do formulario anterior...
$cliente    = ($_POST[chk_cliente] == '') ? 'S' : 'N';
$data_sys   = date('Y-m-d H:i:s');

//Busca do id_unidade_federal p/ poder gravar na Tabela de Informações Comerciais + abaixo ...
$sql = "SELECT `id_uf` 
        FROM `ufs` 
        WHERE `sigla` = '$_POST[txt_estado]' LIMIT 1 ";
$campos_uf = bancos::sql($sql);

//Inserindo a Informação Comercial na Base de Dados ...
$sql = "INSERT INTO `informacoes_comerciais` (`id_informacao_comercial`, `id_cliente_solicitado`, `id_cliente_solicitante`, `id_uf`, `razaosocial`, `contato`, `e_cliente`, `ramo_atividade`, `cep`, `endereco`, `num_complemento`, `bairro`, `cidade`, `email`, `data_sys`) 
        VALUES (NULL, '$_POST[hdd_cliente_solicitado]', '$_POST[hdd_cliente_solicitante]', '".$campos_uf[0]['id_uf']."', '$_POST[txt_razao_social]', '$_POST[txt_contato]', '$cliente', '$_POST[txt_ramo_atividade]', '$_POST[txt_cep]', '$_POST[txt_endereco]', '$_POST[txt_num_complemento]', '$_POST[txt_bairro]', '$_POST[txt_email]', '$_POST[txt_cidade]', '$data_sys')";
bancos::sql($sql);

define('FPDF_FONTPATH', 'font/');
$tipo_papel		= 'P';  // P=> Retrato L=>Paisagem
$unidade		= 'mm'; // pt=>point, mm=>millimeter, cm=>centimeter, in=>inch (A point equals 1/7)
$formato_papel          = 'A4'; // A3, A4, A5, Letter, Legal
$pdf			= new FPDF($tipo_papel, $unidade, $formato_papel);
$pdf->Open();
$pdf->SetLeftMargin(16);

global $pv,$ph; //valor baseado em mm do A4
if($formato_papel == 'A4') {
    if($tipo_papel == 'P') {
        $pv = 295 / 100;
        $ph = 205 / 100;
    }else {
        $pv = 205 / 100;
        $ph = 295 / 100;
    }
}else {
    echo 'Formato não definido';
}
$pdf->AddPage();

$pdf->Image('../../../imagem/logosistema.jpg', 15, 5, 24, 26, 'JPG');
$pdf->Image('../../../imagem/marcas/cabri.png', 44, 20, 10, 10, 'PNG');
$pdf->Image('../../../imagem/marcas/heinz.png', 61, 20, 20, 10, 'PNG');
$pdf->Image('../../../imagem/marcas/tool.png', 85, 20, 42, 10, 'PNG');
$pdf->Image('../../../imagem/marcas/nvo.png', 134, 20, 20, 10, 'PNG');
$pdf->Image('../../../imagem/marcas/warrior.jpg', 156, 20, 42, 10, 'JPG');
$pdf->Ln(12);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell($GLOBALS['ph'] * 75, 23, '________________________________________________________________________________________________', 0, 0, 'L');

$sql = "SELECT c.`razaosocial`, c.`cidade`, c.`cnpj_cpf`, c.`insc_estadual`, c.`credito`, 
        CONCAT(c.`endereco`, ', ', c.`num_complemento`) AS endereco_complemento, u.`sigla` 
        FROM `clientes` c 
        LEFT JOIN `ufs` u ON u.`id_uf` = c.`id_uf` 
        WHERE c.`id_cliente` = '$_POST[hdd_cliente_solicitado]' ";
$campos = bancos::sql($sql);

//Aqui eu pego o id_uf e 
$sql = "SELECT `sigla` 
        FROM `ufs` 
        WHERE `id_uf` = '$_POST[cmb_uf]' LIMIT 1 ";
$campos_ufs = bancos::sql($sql);
			
//Aqui eu pego a última Compra que não seja Livre de Débito claro ...
$sql = "SELECT nfs.`id_nf`, DATE_FORMAT(nfs.`data_emissao`, '%d/%m/%Y') AS data_emissao, 
        nfs.`vencimento1`, nfs.`vencimento2`, nfs.`vencimento3`, nfs.`vencimento4`, 
        IF(c.`id_pais` = 31, SUM(nfsi.`qtde` * nfsi.`valor_unitario`), SUM(nfsi.`qtde` * nfsi.`valor_unitario` / nfs.`valor_dolar_dia`)) AS valor_ultima_compra 
        FROM nfs 
        INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfs.`id_cliente` = '$_POST[hdd_cliente_solicitado]' 
        AND nfs.`livre_debito` = 'N' 
        GROUP BY nfs.`id_nf` ORDER BY nfs.`id_nf` DESC LIMIT 1 ";
$campos_ultima_compra = bancos::sql($sql);

//Aqui eu pego o maior valor em Compras que não seja Livre de Débito claro ...
$sql = "SELECT nfs.`id_nf`, nfs.`data_emissao` AS data_emissao, 
        nfs.`vencimento1`, nfs.`vencimento2`, nfs.`vencimento3`, nfs.`vencimento4`, 
        nnn.`numero_nf`, IF(c.`id_pais` = '31', SUM(nfsi.`qtde` * nfsi.`valor_unitario`), SUM(nfsi.`qtde` * nfsi.`valor_unitario` / nfs.`valor_dolar_dia`)) AS maior_valor_compra 
        FROM `nfs` 
        INNER JOIN `nfs_num_notas` nnn ON nnn.`id_nf_num_nota` = nfs.`id_nf_num_nota` 
        INNER JOIN `nfs_itens` nfsi ON nfsi.`id_nf` = nfs.`id_nf` 
        INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
        WHERE nfs.`id_cliente` = '$_POST[hdd_cliente_solicitado]' 
        AND nfs.`livre_debito` = 'N' 
        GROUP BY nfs.`id_nf` ORDER BY maior_valor_compra DESC ";
$campos_maior_compra = bancos::sql($sql);
$pdf->Ln(25);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($GLOBALS['ph'] * 90, 7, 'INFORMAÇÕES COMERCIAIS - DADOS DO SOLICITANTE', 1, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Razão Social:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $_POST[txt_razao_social], '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Endereço:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $_POST[txt_endereco].', '.$_POST[txt_num_complemento], '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Cidade:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $_POST[txt_cidade], '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'BAIRRO:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $_POST[txt_bairro], '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'UF:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $campos_ufs[0]['sigla'], '1', 1, 'L');	
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'CONTATO:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $_POST[txt_contato], '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'EMAIL:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $_POST[txt_email], '1', 1, 'L');

$pdf->Ln(15);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($GLOBALS['ph'] * 90, 7, 'INFORMAÇÕES COMERCIAIS - DADOS DO SOLICITADO', 1, 1, 'C');
$pdf->SetFont('Arial', '', 10);
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Razão Social:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $campos[0]['razaosocial'], '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Endereço:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $campos[0]['endereco_complemento'], '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Cidade:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $campos[0]['cidade'], '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'UF:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $campos[0]['sigla'], '1', 1, 'L');	
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'CNPJ:', '1', 0, 'L');

if(!empty($campos[0]['cnpj_cpf'])) {//Campo está preenchido ...
    if(strlen($campos[0]['cnpj_cpf']) == 11) {//CPF ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 3).'.'.substr($campos[0]['cnpj_cpf'], 3, 3).'.'.substr($campos[0]['cnpj_cpf'], 6, 3).'-'.substr($campos[0]['cnpj_cpf'], 9, 2);
    }else {//CNPJ ...
        $cnpj_cpf = substr($campos[0]['cnpj_cpf'], 0, 2).'.'.substr($campos[0]['cnpj_cpf'], 2, 3).'.'.substr($campos[0]['cnpj_cpf'], 5, 3).'/'.substr($campos[0]['cnpj_cpf'], 8, 4).'-'.substr($campos[0]['cnpj_cpf'], 12, 2);
    }
}

$pdf->Cell($GLOBALS['ph'] * 55, 7, $cnpj_cpf, '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'INSC.EST.:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $campos[0]['insc_estadual'], '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Cliente desde:', '1', 0, 'L');

/*Como o sistema não grava a Data de Nascimento do Cliente, eu busco então a Data do primeiro Orçamento 
como sendo "sua Data de Origem" ...*/
$sql = "SELECT DATE_FORMAT(`data_emissao`, '%d/%m/%Y') AS data_emissao 
        FROM `orcamentos_vendas` 
        WHERE id_cliente = '$_POST[hdd_cliente_solicitado]' LIMIT 1 ";
$campos_orcamento = bancos::sql($sql);
$pdf->Cell($GLOBALS['ph'] * 55, 7, $campos_orcamento[0]['data_emissao'], '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 90, 7, '', '1', 1, 'L');

/**************Dados da Última Compra**************/
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Data Última Compra:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $campos_ultima_compra[0]['data_emissao'], '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Valor Ultima Compra:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, 'R$ '.number_format($campos_ultima_compra[0]['valor_ultima_compra'], '2', ',', '.'), '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Forma de Pagamento:', '1', 0, 'L');

if($campos_ultima_compra[0]['vencimento4'] > 0) $pzo_fat_ultima_compra = '/'.$campos_ultima_compra[0]['vencimento4'];
if($campos_ultima_compra[0]['vencimento3'] > 0) $pzo_fat_ultima_compra = '/'.$campos_ultima_compra[0]['vencimento3'].$pzo_fat_ultima_compra;
if($campos_ultima_compra[0]['vencimento2'] > 0) {
    $pzo_fat_ultima_compra = $campos_ultima_compra[0]['vencimento1'].'/'.$campos_ultima_compra[0]['vencimento2'].$pzo_fat_ultima_compra;
}else {
    $pzo_fat_ultima_compra = ($campos_ultima_compra[0]['vencimento1'] == 0) ? 'À vista' : $campos_ultima_compra[0]['vencimento1'];
}
$pdf->Cell($GLOBALS['ph'] * 55, 7, $pzo_fat_ultima_compra, '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Pagamento da Ultima Compra:', '1', 0, 'L');

//Aqui eu busco a última Duplicata da NF, podemos ter A, B, C e D ...
$sql = "SELECT `id_conta_receber`, `id_tipo_recebimento`, `data_vencimento`, `status` 
        FROM `contas_receberes` 
        WHERE `id_nf` = '".$campos_ultima_compra[0]['id_nf']."' ORDER BY `id_conta_receber` DESC LIMIT 1 ";
$campos_duplicata = bancos::sql($sql);
if(count($campos_duplicata) == 1) {//Significa que esta NF já foi importada pelo Financeiro ...
    if($campos_duplicata[0]['status'] == 1 || $campos_duplicata[0]['status'] == 2) {//Já foi recebido algo ou tudo da Duplicata ...
        //Busca a última Data de Recebimento da Conta ...
        $sql = "SELECT `data` 
                FROM `contas_receberes_quitacoes` 
                WHERE `id_conta_receber` = '".$campos_duplicata[0]['id_conta_receber']."' ORDER BY `id_conta_receber_quitacao` DESC LIMIT 1 ";
        $campos_recebimento = bancos::sql($sql);
        //Verifico em qual Dia da Semana que caiu a Data de Vencimento da Duplicata ...
        $dia_semana         = data::dia_semana(data::datetodata($campos_duplicata[0]['data_vencimento'], '/'));
        //Verifico quantos dias se passaram do Vencimento da Duplicata até quando foi pago ...
        $retorno            = data::diferenca_data($campos_duplicata[0]['data_vencimento'], $campos_recebimento[0]['data']);
        $qtde_dias_pago_em_atraso_ultima_compra = $retorno[0];
    }else {//Por enquanto o Cliente não pagou nada para a Albafer, está devendo na casa ...
        //Verifico quantos dias se passaram do Vencimento da Duplicata até a Data Atual ...
        $retorno            = data::diferenca_data($campos_duplicata[0]['data_vencimento'], date('Y-m-d'));
        $qtde_dias_pago_em_atraso_ultima_compra = $retorno[0];
    }
}else {//Ainda não foi importada pelo Financeiro, não existe Duplicata, sendo assim eu pego a Data de Vencimento da NF ...
    $sql = "SELECT `id_nf`, `data_emissao`, `vencimento1`, `vencimento2`, `vencimento3`, `vencimento4` 
            FROM `nfs` 
            WHERE `id_nf` = '".$campos_ultima_compra[0]['id_nf']."' ORDER BY `id_nf` DESC LIMIT 1 ";
    $campos_duplicata = bancos::sql($sql);
    if($campos_duplicata[0]['vencimento4'] > 0) {
        $data_vencimento	= data::adicionar_data_hora(data::datetodata($campos_duplicata[0]['data_emissao'], '/'), $campos_duplicata[0]['vencimento4']);
    }else if($campos_duplicata[0]['vencimento3'] > 0) {
        $data_vencimento	= data::adicionar_data_hora(data::datetodata($campos_duplicata[0]['data_emissao'], '/'), $campos_duplicata[0]['vencimento3']);
    }else if($campos_duplicata[0]['vencimento2'] > 0) {
        $data_vencimento	= data::adicionar_data_hora(data::datetodata($campos_duplicata[0]['data_emissao'], '/'), $campos_duplicata[0]['vencimento2']);
    }else {
        $data_vencimento	= data::adicionar_data_hora(data::datetodata($campos_duplicata[0]['data_emissao'], '/'), $campos_duplicata[0]['vencimento1']);
    }
    $retorno                    = data::diferenca_data(data::datatodate($data_vencimento, '-'), date('Y-m-d'));
    $qtde_dias_pago_em_atraso_ultima_compra = $retorno[0];
}

if($dia_semana == 6) {//Se a Data de Vencimento caiu no Sábado, o cliente pode pagar na segunda-feira ...
    $qtde_dias_pago_em_atraso_ultima_compra-= 2;//Desconto 2 dias do Atraso ...
}else if($dia_semana == 0) {//Se a Data de Vencimento caiu no Domingo, o Cliente pode pagar também na segunda-feira
    $qtde_dias_pago_em_atraso_ultima_compra-= 1;//Desconto 1 dias do Atraso ...
}

if($qtde_dias_pago_em_atraso_ultima_compra < 0) {
    $rotulo_dias_pago_em_atraso_ultima_compra = 'PGTO ANTECIPADO';
}else {
    $rotulo_dias_pago_em_atraso_ultima_compra = ($qtde_dias_pago_em_atraso_ultima_compra == 0) ? 'PONTUAL' : 'ATRASADO - '.$qtde_dias_pago_em_atraso_ultima_compra.' DIAS';
}

if($campos_duplicata[0]['id_tipo_recebimento'] == 7) {
    $tipo_recebimento_ultima_compra = ' (PROTESTADO)';
}else if($campos_duplicata[0]['id_tipo_recebimento'] == 9) {
    $tipo_recebimento_ultima_compra = ' (CARTÓRIO)';
}

$pdf->Cell($GLOBALS['ph'] * 55, 7, $rotulo_dias_pago_em_atraso_ultima_compra.$tipo_recebimento_ultima_compra, '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 90, 7, '', '1', 1, 'L');
/**************Dados do Maior Valor Comprado**************/
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Data do Maior Valor Comprado:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, data::datetodata($campos_maior_compra[0]['data_emissao'], '/'), '1', 1, 'L');								
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Maior Valor Comprado:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, 'R$ '.number_format($campos_maior_compra[0]['maior_valor_compra'], '2', ',', '.'), '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Forma de Pagamento:', '1', 0, 'L');

if($campos_maior_compra[0]['vencimento4'] > 0) $pzo_fat_maior_compra = '/'.$campos_maior_compra[0]['vencimento4'];
if($campos_maior_compra[0]['vencimento3'] > 0) $pzo_fat_maior_compra = '/'.$campos_maior_compra[0]['vencimento3'].$pzo_fat_maior_compra;
if($campos_maior_compra[0]['vencimento2'] > 0) {
    $pzo_fat_maior_compra = $campos_maior_compra[0]['vencimento1'].'/'.$campos_maior_compra[0]['vencimento2'].$pzo_fat_maior_compra;
}else {
    $pzo_fat_maior_compra = ($campos_maior_compra[0]['vencimento1'] == 0) ? 'À vista' : $campos_maior_compra[0]['vencimento1'];
}
$pdf->Cell($GLOBALS['ph'] * 55, 7, $pzo_fat_maior_compra, '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Pagamento da Maior Compra:', '1', 0, 'L');

//Aqui eu busco a última Duplicata da NF, podemos ter A, B, C e D ...
$sql = "SELECT `id_conta_receber`, `data_vencimento`, `status` 
        FROM `contas_receberes` 
        WHERE `id_nf` = '".$campos_maior_compra[0]['id_nf']."' ORDER BY id_conta_receber DESC LIMIT 1 ";
$campos_duplicata = bancos::sql($sql);
if(count($campos_duplicata) == 1) {//Significa que esta NF já foi importada pelo Financeiro ...
    if($campos_duplicata[0]['status'] == 1 || $campos_duplicata[0]['status'] == 2) {//Já foi recebido algo ou tudo da Duplicata ...
        //Busca a última Data de Recebimento da Conta ...
        $sql = "SELECT `data` 
                FROM `contas_receberes_quitacoes` 
                WHERE `id_conta_receber` = '".$campos_duplicata[0]['id_conta_receber']."' ORDER BY `id_conta_receber_quitacao` DESC LIMIT 1 ";
        $campos_recebimento     = bancos::sql($sql);
        //Verifico em qual Dia da Semana que caiu a Data de Vencimento da Duplicata ...
        $dia_semana 		= data::dia_semana(data::datetodata($campos_duplicata[0]['data_vencimento'], '/'));
        //Verifico quantos dias se passaram do Vencimento da Duplicata até quando foi pago ...
        $retorno 		= data::diferenca_data($campos_duplicata[0]['data_vencimento'], $campos_recebimento[0]['data']);
        $qtde_dias_pago_em_atraso_maior_compra = $retorno[0];
    }else {//Por enquanto o Cliente não pagou nada para a Albafer, está devendo na casa ...
        //Verifico quantos dias se passaram do Vencimento da Duplicata até a Data Atual ...
        $retorno 		= data::diferenca_data($campos_duplicata[0]['data_vencimento'], date('Y-m-d'));
        $qtde_dias_pago_em_atraso_maior_compra = $retorno[0];
    }
}else {//Ainda não foi importada pelo Financeiro, não existe Duplicata ...
    if($campos_maior_compra[0]['data_emissao'] < '2006-05-02') {//Antes dessa data, não existia o Sistema de Importar a NF direto no Financeiro ...
        //Aqui eu busco a última Duplicata, podemos ter A, B, C e D ...
        $sql = "SELECT `id_conta_receber`, `data_vencimento`, `status` 
                FROM `contas_receberes` 
                WHERE `num_conta` = '".$campos_maior_compra[0]['numero_nf']."' 
                AND `data_emissao` = '".$campos_maior_compra[0]['data_emissao']."' ORDER BY `id_conta_receber` DESC LIMIT 1 ";
        $campos_duplicata = bancos::sql($sql);
        if($campos_duplicata[0]['status'] == 1 || $campos_duplicata[0]['status'] == 2) {//Já foi recebido algo ou tudo da Duplicata ...
            //Busca a última Data de Recebimento da Conta ...
            $sql = "SELECT `data` 
                    FROM `contas_receberes_quitacoes` 
                    WHERE `id_conta_receber` = '".$campos_duplicata[0]['id_conta_receber']."' ORDER BY `id_conta_receber_quitacao` DESC LIMIT 1 ";
            $campos_recebimento     = bancos::sql($sql);
            //Verifico em qual Dia da Semana que caiu a Data de Vencimento da Duplicata ...
            $dia_semana 		= data::dia_semana(data::datetodata($campos_duplicata[0]['data_vencimento'], '/'));
            //Verifico quantos dias se passaram do Vencimento da Duplicata até quando foi pago ...
            $retorno 		= data::diferenca_data($campos_duplicata[0]['data_vencimento'], $campos_recebimento[0]['data']);
        }else {//Por enquanto o Cliente não pagou nada para a Albafer, está devendo na casa ...
            //Verifico quantos dias se passaram do Vencimento da Duplicata até a Data Atual ...
            $retorno 		= data::diferenca_data($campos_duplicata[0]['data_vencimento'], date('Y-m-d'));
        }
    }else {//A partir de 02/05/2006 foi que as NFs começaram a ser Importadas, sendo assim eu pego a Data de Vencimento da NF ...
        $sql = "SELECT `data_emissao`, `vencimento1`, `vencimento2`, `vencimento3`, `vencimento4` 
                FROM `nfs` 
                WHERE `id_nf` = '".$campos_maior_compra[0]['id_nf']."' ORDER BY `id_nf` DESC LIMIT 1 ";
        $campos_duplicata = bancos::sql($sql);
        if($campos_duplicata[0]['vencimento4'] > 0) {
            $data_vencimento	= data::adicionar_data_hora(data::datetodata($campos_duplicata[0]['data_emissao'], '/'), $campos_duplicata[0]['vencimento4']);
        }else if($campos_duplicata[0]['vencimento3'] > 0) {
            $data_vencimento	= data::adicionar_data_hora(data::datetodata($campos_duplicata[0]['data_emissao'], '/'), $campos_duplicata[0]['vencimento3']);
        }else if($campos_duplicata[0]['vencimento2'] > 0) {
            $data_vencimento	= data::adicionar_data_hora(data::datetodata($campos_duplicata[0]['data_emissao'], '/'), $campos_duplicata[0]['vencimento2']);
        }else {
            $data_vencimento	= data::adicionar_data_hora(data::datetodata($campos_duplicata[0]['data_emissao'], '/'), $campos_duplicata[0]['vencimento1']);
        }
        $retorno 			= data::diferenca_data(data::datatodate($data_vencimento, '-'), date('Y-m-d'));
    }
    $qtde_dias_pago_em_atraso_maior_compra = $retorno[0];
}

if($dia_semana == 6) {//Se a Data de Vencimento caiu no Sábado, o cliente pode pagar na segunda-feira ...
    $qtde_dias_pago_em_atraso_maior_compra-= 2;//Desconto 2 dias do Atraso ...
}else if($dia_semana == 0) {//Se a Data de Vencimento caiu no Domingo, o Cliente pode pagar também na segunda-feira
    $qtde_dias_pago_em_atraso_maior_compra-= 1;//Desconto 1 dias do Atraso ...
}

if($qtde_dias_pago_em_atraso_maior_compra < 0) {
    $rotulo_dias_pago_em_atraso_maior_compra = 'PGTO ANTECIPADO';
}else {
    $rotulo_dias_pago_em_atraso_maior_compra = ($qtde_dias_pago_em_atraso_maior_compra == 0) ? 'PONTUAL' : 'ATRASADO - '.$qtde_dias_pago_em_atraso_maior_compra.' DIAS';
}

if($campos_duplicata[0]['id_tipo_recebimento'] == 7) {
    $tipo_recebimento_maior_compra = ' (PROTESTADO)';
}else if($campos_duplicata[0]['id_tipo_recebimento'] == 9) {
    $tipo_recebimento_maior_compra = ' (CARTÓRIO)';
}
$pdf->Cell($GLOBALS['ph'] * 55, 7, $rotulo_dias_pago_em_atraso_maior_compra.$tipo_recebimento_maior_compra, '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 90, 7, '', '1', 1, 'L');
/*********************************************************/
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Limite de Crédito:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, 'INDEFINIDO', '1', 1, 'L');
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Conceito:', '1', 0, 'L');
/*********************************************************/
/*******Recebimento do Cliente nos últimos 6 meses********/
/*********************************************************/
//Aqui eu verifico tudo o que foi recebido o Cliente nos últimos 6 meses ...
$sql = "SELECT DISTINCT(cr.`id_conta_receber`), cr.`data_vencimento` 
        FROM `contas_receberes` cr 
        INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_conta_receber` = cr.`id_conta_receber` 
        AND crq.data > DATE_ADD('".date('Y-m-d')."', INTERVAL -180 DAY) 
        WHERE cr.`id_cliente` = '$_POST[hdd_cliente_solicitado]' 
        AND cr.`ativo` = '1' 
        AND cr.`status` >= '1' ";
$campos_recebidas = bancos::sql($sql);
$linhas_recebidas = count($campos_recebidas);
$total_contas	  = $linhas_recebidas;//Essa variável será utilizada para calcular a $media_atraso abaixo ...
if($total_contas > 0) {
    for($i = 0; $i < $linhas_recebidas; $i++) {
        //Busca da última Data de Recebimento da Conta à Receber ...
        $sql = "SELECT `data` AS data_recebida 
                FROM `contas_receberes_quitacoes` 
                WHERE `id_conta_receber` = '".$campos_recebidas[$i]['id_conta_receber']."' 
                ORDER BY `data_recebida` DESC LIMIT 1 ";
        $campos_data_recebimento    = bancos::sql($sql);
        $retorno                    = data::diferenca_data($campos_recebidas[$i]['data_vencimento'], $campos_data_recebimento[0]['data_recebida']);
        //Só contabilizo o atraso dos dias em que a Diferença da variável $diferença "é Maior do que Zero" ...
        if($retorno[0] > 0) $qtde_dias_pago_em_atraso_geral+= $retorno[0];
    }
    $periodo_em_meses = 6;
}else {
    $retorno = data::diferenca_data(data::datatodate($campos_ultima_compra[0]['data_emissao'], '-'), date('Y-m-d'));
    //Às vezes o Cliente não tem recebimento $$ nos últimos 3 meses - 1 ano e nunca efetuou compra anterior na vida aqui na Albafér ...
    if($retorno[0] > 90 && count($campos_ultima_compra) == 0)  {
        $conceito = 'SEM AVALIAÇÃO - CLIENTE HÁ POUCO TEMPO';//A princípio será isso porque não tem compra nos últimos 3 meses ...
        $periodo_em_meses = 0;
    }else {
        /*********************************************************/
        /*******Recebimento do Cliente nos últimos 12 meses*******/
        /*********************************************************/
        //Aqui eu verifico tudo o que foi recebido o Cliente nos últimos 12 meses  - 1 ano ...
        $sql = "SELECT DISTINCT(cr.`id_conta_receber`), cr.`data_vencimento` 
                FROM `contas_receberes` cr 
                INNER JOIN `contas_receberes_quitacoes` crq ON crq.`id_conta_receber` = cr.`id_conta_receber` 
                AND crq.`data` > DATE_ADD( '".date('Y-m-d')."', INTERVAL -365 DAY) 
                WHERE cr.`id_cliente` = '$_POST[hdd_cliente_solicitado]' 
                AND cr.`ativo` = '1' 
                AND cr.`status` >= '1' ";
        $campos_recebidas   = bancos::sql($sql);
        $linhas_recebidas   = count($campos_recebidas);
        $total_contas       = $linhas_recebidas;//Essa variável será utilizada para calcular a $media_atraso abaixo ...
        if($total_contas > 0) {
            for($i = 0; $i < $linhas_recebidas; $i++) {
                //Busca da última Data de Recebimento da Conta à Receber ...
                $sql = "SELECT `data` AS data_recebida 
                        FROM `contas_receberes_quitacoes` 
                        WHERE `id_conta_receber` = '".$campos_recebidas[$i]['id_conta_receber']."' 
                        ORDER BY `data_recebida` DESC LIMIT 1 ";
                $campos_data_recebimento    = bancos::sql($sql);
                $retorno                    = data::diferenca_data($campos_recebidas[$i]['data_vencimento'], $campos_data_recebimento[0]['data_recebida']);
                //Só contabilizo o atraso dos dias em que a Diferença da variável $diferença "é Maior do que Zero" ...
                if($retorno[0] > 0) $qtde_dias_pago_em_atraso_geral+= $retorno[0];
            }
            $periodo_em_meses = 12;
        }else {//Às vezes o Cliente não tem recebimento nos últimos 12 meses - 1 ano, mas pode ter comprado alguma coisa no último 1 ano NF ...
            $retorno = data::diferenca_data(data::datatodate($campos_ultima_compra[0]['data_emissao'], '-'), date('Y-m-d'));
            if($retorno[0] > 365) {
                $conceito = 'SEM AVALIAÇÃO - CLIENTE INATIVO';//O cliente já está há mais de 1 ano sem comprar nada, então ...
            }else {
                /*O cliente faz muito tempo que não paga nada, e voltou a comprar agora sendo assim fica difícil reavaliá-lo, 
                sendo assim fixo "REGULAR" ...*/
                $conceito = 'REGULAR';
            }
        }
    }
}

if($total_contas > 0) {
    $media_atraso = intval($qtde_dias_pago_em_atraso_geral / $total_contas);
    if($media_atraso <= 3) {
        $conceito = 'ÓTIMO';
    }else if($media_atraso <= 7) {
        $conceito = 'BOM';
    }else if($media_atraso <= 14) {
        $conceito = 'REGULAR';
    }else {
        $conceito = 'RUIM';
    }
}
if($periodo_em_meses > 0) $conceito.= ' (Nos últimos '.$periodo_em_meses.' meses)';
$pdf->Cell($GLOBALS['ph'] * 55, 7, $conceito, '1', 1, 'L');
/*********************************************************/

$sql = "SELECT `nome` 
        FROM `funcionarios` 
        WHERE `id_funcionario` = '$_SESSION[id_funcionario]' LIMIT 1 ";
$campos_funcionario = bancos::sql($sql);
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Responsavel pela informação:', '1', 0, 'L');
$pdf->Cell($GLOBALS['ph'] * 55, 7, $campos_funcionario[0]['nome'], '1', 1, 'L');

$pdf->Ln(12);
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Wilson: ____________________________________________________________________', '0', 1, 'L');

//Aqui eu busco o Representante do Cliente passado por parâmetro ...
$sql = "SELECT r.id_representante, r.nome_fantasia 
        FROM `representantes` r 
        INNER JOIN `clientes_vs_representantes` cr ON cr.id_representante = r.id_representante AND cr.id_cliente = '$_POST[hdd_cliente_solicitado]' 
        WHERE r.`ativo` = '1' ORDER BY r.nome_fantasia LIMIT 1 ";
$campos = bancos::sql($sql);
//Aqui eu confirmo se o Representante que foi retornado do Cliente é um funcionário ...
$sql = "SELECT `id_representante` 
        FROM `representantes_vs_funcionarios` 
        WHERE `id_representante` = '".$campos[0]['id_representante']."' LIMIT 1 ";
$campos_funcionario = bancos::sql($sql);
//Se não for funcionário e for diferente de Direto e PME, busco o Supervisor desse que é funcionário ...
if(count($campos_funcionario) == 0 && ($campos[0]['id_representante'] != 1 && $campos[0]['id_representante'] != 71)) {
    $sql = "SELECT r.`nome_fantasia` 
            FROM `representantes_vs_supervisores` rs 
            INNER JOIN `representantes` r ON r.`id_representante` = rs.`id_representante` 
            WHERE rs.`id_representante` = '".$campos[0]['id_representante']."' LIMIT 1 ";
    $campos_representante = bancos::sql($sql);
    $representante = $campos_representante[0]['nome_fantasia'];
}else {
    $representante = $campos[0]['nome_fantasia'];
}
$pdf->Cell($GLOBALS['ph'] * 35, 7, 'Representante ('.$representante.'): ___________________________________________________', '0', 1, 'L');
$pdf->Ln(15);
$pdf->Cell($GLOBALS['ph'] * 90, 7, 'E-mail: grupoalbafer@grupoalbafer.com.br - Site: www.grupoalbafer.com.br', 'T', 0, 'C');

chdir('../../../pdf');
$file='../../../pdf/'.basename(tempnam(str_replace(trim("/"),'/',getcwd()),'rel')).'.pdf';//Determine a temporary file name in the current directory
chdir(dirname(__FILE__));
$pdf->Output($file);//Save PDF to file
echo "<HTML><body></body><SCRIPT language='JavaScript'>document.location='$file';</SCRIPT></HTML>";//JavaScript redirection
?>