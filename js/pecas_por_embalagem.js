//Esses são os únicos 3 funcionários do Sistema que podem colocar qualquer valor no que se refere à "Peças por Embalagem" ...
var vetor_funcionarios_ignorar_pecas_por_embalagem = [62, 98, 136]//"62" Roberto, Dárcio 98 porque programa e Nishimura 136 ...

/*Observações dos returns

0, 3 - são mensagens que o próprio Script retorna sem a interação do Usuário "Alert" 0 ...
1, 2 - Houve interação do Usuário com clicks de botão do "Confirm" - 1 botão Cancelar, 2 botão OK ...

Existem 2 variáveis genéricas em PHP = '64' => PBS e 65 => Machos "Primeiro, Segundo, Terceiro"
Mas não utilizadas aqui porque aqui é um arquivo de extensão JS

//É uma das poucas funções em JS que é utilizada em poucos arquivos do sistema e que foi criada dentro dessa Biblioteca JS ...

Obs: esses últimos 3 parâmetros "preco_liquido_final, lote_min_producao_reais, posicao", são utilizados somente dentro do arquivo 
"alterar Item de Orçamento" ...*/
function pecas_por_embalagem(referencia, discriminacao, id_familia, qtde, pecas_por_embalagem, preco_liquido_final, lote_min_producao_reais, posicao) {
    var vetor_ref_embals_travadas   = ['HS-', 'TM-', 'UL-', 'PBS-']//Falta incluir o MC e o SU, mas daí é melhor mudar p/ a Família Bits e Bedame ...
    var pa_de_embalagem_travada     = 'N'
    var minimo_de_venda             = 0
    /****************************Controle por referências****************************/
    for(i = 0; i < vetor_ref_embals_travadas.length; i++) {
        //Verifico se a Referência do Produto Acabado está dentro de alguma dessas referências Proibidas ...
        var indice = referencia.indexOf(vetor_ref_embals_travadas[i])
        if(indice == 0) {//Significa que essa Referência está dentro de alguma dessas referências Proibidas ...
            pa_de_embalagem_travada = 'S'
            //Só nesse caso de PBS- que o sistema força a Qtde de Pçs por Embalagem ser o Mínimo de Venda ...
            if(vetor_ref_embals_travadas[i] == 'PBS-') minimo_de_venda = 5//É um PA que nós fizemos com Custo Baixo que tem de ser vendido em grande Qtde p/ Compensar ...
            break
        }
    }
    /***************************Controle por discriminações***************************/
    /*Sendo da Família de Machos, referência Normal de Linha e contendo alguma dessas Frases na Discriminação 
    do Produto, sugere-se essa Quantidade mínima abaixo ...*/
    if(id_familia == 9 && referencia != 'ESP' && discriminacao.indexOf('PRIMEIRO') == 0 || discriminacao.indexOf('SEGUNDO') == 0 || discriminacao.indexOf('TERCEIRO') == 0) {
        minimo_de_venda = 10//Mínimo de 10 peças porque o Fornecedor já não quer mais Produzir em Quantidades Pequenas ...
    }
    /*********************************************************************************/
    var resto_divisao = (qtde) % (pecas_por_embalagem)//Verifica o Mod (Resto da Divisão) ...    
    if(resto_divisao != 0 && !isNaN(resto_divisao)) {//Representa que não está compatível a Qtde de Peças / Embalagem ...
        var sugestao = (parseInt(qtde / pecas_por_embalagem) + 1) * pecas_por_embalagem
        /*Se a Família = 'PINOS' 
                ou Família = 'LIMAS' 
                ou Família = 'RISCADOR' 
                ou Família = 'SACA BUCHA' 
                ou Família = 'CHAVES PARA MANDRIL' 
                ou Família = 'FLUÍDOS, ÓLEOS, LUBRIFICANTES' 
                ou Família = 'CABO DE LIMA' 
                ou Família = 'BROCAS' 
                ou Família = 'ROSCA POSTIÇA' 
                ou a referência do PA está dentro do vetor de "vetor_ref_embals_travadas" 
                não dá opção p/ o usuário abrir a embalagem ...*/
        if(id_familia == 2 || id_familia == 3 || id_familia == 7 || id_familia == 17 || id_familia == 19 || id_familia == 26 || id_familia == 27 || id_familia == 29 || id_familia == 30 || pa_de_embalagem_travada == 'S') {
            alert('A QTDE DO     '+referencia+'     NÃO ESTÁ COMPATÍVEL COM A QTDE DE PÇS / EMBALAGEM ! \nALTERE A QUANTIDADE !!!        SUGESTÃO  =  '+sugestao+'  .')
            return 0//O Script teve que parar por si só, porque existe uma divergência ...
        }else {
            var pergunta = confirm('A QTDE DO     '+referencia+'     NÃO ESTÁ COMPATÍVEL COM A QTDE DE PÇS / EMBALAGEM ! \n DESEJA MANTER ESTÁ QUANTIDADE ?        SUGESTÃO  =  '+sugestao+'  .')
            if(pergunta == false) {//Não aceitou a qtde incompatível
                //Função foi chamada através do evento onblur ...
                if(typeof(posicao) == 'undefined') document.form.reset()
                return 1//Usuário clicou no botão Cancelar ...
            }else {
                return 2//Usuário clicou no botão OK ...
            }
        }
    }else if(minimo_de_venda > 0) {
        if((qtde * preco_liquido_final) < lote_min_producao_reais) {
            if(qtde < minimo_de_venda) {
                alert('A QTDE MÍNIMA DE VENDA DO     '+referencia+'     É  DE  =  '+minimo_de_venda+'  pçs.')
                return 0//O Script teve que parar por si só, porque existe uma divergência ...
            }
        }
    }else {
        return 3//O Script foi executado sem nenhum problema, pode continuar ...
    }
}