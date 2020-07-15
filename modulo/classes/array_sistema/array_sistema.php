<?
class array_sistema extends bancos {
    function follow_ups() {
        //Esse vetor tem por objetivo rotular de melhor modo a origem onde foi gerado o Follow-UP ...
        $vetor[0] = 'Vazio';
        $vetor[1] = 'Or�amento de Vendas';
        $vetor[2] = 'Pedido de Vendas';
        $vetor[3] = 'Gerenciar Estoque';
        $vetor[4] = 'Contas � Receber';
        $vetor[5] = 'NF Sa�da';
        $vetor[6] = 'APV';
        $vetor[7] = 'Atend. Interno';
        $vetor[8] = 'Depto. T�cnico';
        $vetor[9] = 'Pend�ncias';
        $vetor[10] = 'TeleMarketing';
        $vetor[11] = 'Acompanhamento';
        $vetor[12] = 'Simples Relato';
        $vetor[13] = 'Proje��o Trimestral';
        $vetor[14] = 'APV Projetado';
        $vetor[15] = 'Cadastro';
        $vetor[16] = 'Pedido de Compras';
        $vetor[17] = 'NF Entrada';
        $vetor[18] = 'Contas � Pagar';
        $vetor[19] = 'Produto Acabado';
        $vetor[20] = 'Produto Insumo';
        return $vetor;
    }

    function nota_fiscal() {
        //Vetor para Auxiliar as Identifica��es
        $vetor[0] = 'EM ABERTO';
        $vetor[1] = 'LIBERADA P/ FATURAR';
        $vetor[2] = 'FATURADA';
        $vetor[3] = 'EMPACOTADA';
        $vetor[4] = 'DESPACHADA';
        $vetor[5] = 'CANCELADA';
        $vetor[6] = '<font color="red"><b>DEVOLU��O</b></font>';
        return $vetor;
    }

    function grupos_emails() {
        //Vetor para Auxiliar as Identifica��es
        $vetor[1] = 'almoxarifado';
        $vetor[2] = 'atendimento@grupoalbafer.com.br';
        $vetor[3] = 'depto.pessoal';
        $vetor[4] = 'direcao';
        $vetor[5] = 'fabrica';
        $vetor[6] = 'estoque';
        $vetor[7] = 'faturamento';
        $vetor[8] = 'marketing';
        $vetor[9] = 'portaria';
        $vetor[10] = 'telefonista';
        $vetor[11] = 'gfinanceiro@grupoalbafer.com.br';
        $vetor[12] = 'gtodos@grupoalbafer.com.br';
        $vetor[13] = 'gvendas@grupoalbafer.com.br';
        return $vetor;
    }

    function condicao_faturamento() {
        //Vetor para Auxiliar as Identifica��es
        $vetor[1] = '<font color="green">D� vlr NF+Imed</font>';
        $vetor[2] = '<font color="red">D� vlr NF+em prod</font>';
        $vetor[3] = '<font color="red">S/vlr NF+Imed</font>';
        $vetor[4] = '<font color="red">S/vlr NF+em prod</font>';
        return $vetor;
    }

    function situacao_tributaria() {
        //Vetor para Auxiliar as Identifica��es
        $vetor['00'] = 'Tributada integralmente';
        $vetor['10'] = 'Tributada e com cobran�a do ICMS por substitui��o tribut�ria';
        $vetor['20'] = 'Com redu��o de base de c�lculo';
        $vetor['30'] = 'Isenta ou n�o tributada e com cobran�a do ICMS por substitui��o tribut�ria';
        $vetor['40'] = 'Isenta';
        $vetor['41'] = 'N�o tributada';
        $vetor['50'] = 'Suspens�o';
        $vetor['51'] = 'Diferimento';
        $vetor['60'] = 'ICMS cobrado anteriormente por substitui��o tribut�ria';
        $vetor['70'] = 'Com redu��o de base de c�lculo e cobran�a do ICMS por substitui��o tribut�ria';
        $vetor['90'] = 'Outras';
        return $vetor;
    }
    
    function origem_mercadoria() {
        //Vetor para Auxiliar as Identifica��es
        $vetor[0] = 'Nacional';
        $vetor[1] = 'Estrangeira - Importa��o direta';
        $vetor[2] = 'Estrangeira - Adquirida do mercado interno';
        $vetor[3] = 'Nacional, mercadoria ou bem com Conte�do de Importa��o superior a 40%';
        $vetor[4] = 'Nacional, cuja produ��o tenha sido feita em conformidade com os processos produtivos b�sicos';
        $vetor[5] = 'Nacional, mercadoria ou bem com Conte�do de Importa��o inferior ou igual a 40%';
        $vetor[6] = 'Estrangeira - Importa��o direta, sem similar nacional, constante em lista de Resolu��o Camex';
        $vetor[7] = 'Estrangeira - Adquirida no mercado interno, s/ similar nacional, constante em lista da Camex';
        $vetor[8] = 'Nacional, mercadoria ou bem com Conte�do de Importa��o superior a 70%';
        return $vetor;
    }
    
    function emails_corporativos() {
        //Vetor para Auxiliar as Identifica��es
        $vetor[0] = 'analise.venda@grupoalbafer.com.br';
        $vetor[1] = 'gcusto@grupoalbafer.com.br';
        $vetor[2] = 'gdirecao@grupoalbafer.com.br';
        $vetor[3] = 'gestoque@grupoalbafer.com.br';
        $vetor[4] = 'gfinanceiro@grupoalbafer.com.br';
        return $vetor;
    }
    
    function forma_pagamento() {
        //Vetor para Auxiliar as Identifica��es
        $vetor[1] = 'Dupl. em carteira';
        $vetor[2] = 'Dupl. apenas Banco';
        $vetor[3] = 'Dupl. FDIC / Banco';
        $vetor[4] = 'Dupl. � definir';
        $vetor[5] = 'Pagto adiantado';
        $vetor[6] = 'Deposito cheque em cc';
        $vetor[7] = 'Deposito dinheiro em cc';
        return $vetor;
    }
}
?>