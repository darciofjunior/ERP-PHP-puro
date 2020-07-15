//Esses s�o os �nicos 3 funcion�rios do Sistema que podem colocar qualquer valor no que se refere � "Pe�as por Embalagem" ...
var vetor_funcionarios_ignorar_pecas_por_embalagem = [62, 98, 136]//"62" Roberto, D�rcio 98 porque programa e Nishimura 136 ...

/*Observa��es dos returns

0, 3 - s�o mensagens que o pr�prio Script retorna sem a intera��o do Usu�rio "Alert" 0 ...
1, 2 - Houve intera��o do Usu�rio com clicks de bot�o do "Confirm" - 1 bot�o Cancelar, 2 bot�o OK ...

Existem 2 vari�veis gen�ricas em PHP = '64' => PBS e 65 => Machos "Primeiro, Segundo, Terceiro"
Mas n�o utilizadas aqui porque aqui � um arquivo de extens�o JS

//� uma das poucas fun��es em JS que � utilizada em poucos arquivos do sistema e que foi criada dentro dessa Biblioteca JS ...

Obs: esses �ltimos 3 par�metros "preco_liquido_final, lote_min_producao_reais, posicao", s�o utilizados somente dentro do arquivo 
"alterar Item de Or�amento" ...*/
function pecas_por_embalagem(referencia, discriminacao, id_familia, qtde, pecas_por_embalagem, preco_liquido_final, lote_min_producao_reais, posicao) {
    var vetor_ref_embals_travadas   = ['HS-', 'TM-', 'UL-', 'PBS-']//Falta incluir o MC e o SU, mas da� � melhor mudar p/ a Fam�lia Bits e Bedame ...
    var pa_de_embalagem_travada     = 'N'
    var minimo_de_venda             = 0
    /****************************Controle por refer�ncias****************************/
    for(i = 0; i < vetor_ref_embals_travadas.length; i++) {
        //Verifico se a Refer�ncia do Produto Acabado est� dentro de alguma dessas refer�ncias Proibidas ...
        var indice = referencia.indexOf(vetor_ref_embals_travadas[i])
        if(indice == 0) {//Significa que essa Refer�ncia est� dentro de alguma dessas refer�ncias Proibidas ...
            pa_de_embalagem_travada = 'S'
            //S� nesse caso de PBS- que o sistema for�a a Qtde de P�s por Embalagem ser o M�nimo de Venda ...
            if(vetor_ref_embals_travadas[i] == 'PBS-') minimo_de_venda = 5//� um PA que n�s fizemos com Custo Baixo que tem de ser vendido em grande Qtde p/ Compensar ...
            break
        }
    }
    /***************************Controle por discrimina��es***************************/
    /*Sendo da Fam�lia de Machos, refer�ncia Normal de Linha e contendo alguma dessas Frases na Discrimina��o 
    do Produto, sugere-se essa Quantidade m�nima abaixo ...*/
    if(id_familia == 9 && referencia != 'ESP' && discriminacao.indexOf('PRIMEIRO') == 0 || discriminacao.indexOf('SEGUNDO') == 0 || discriminacao.indexOf('TERCEIRO') == 0) {
        minimo_de_venda = 10//M�nimo de 10 pe�as porque o Fornecedor j� n�o quer mais Produzir em Quantidades Pequenas ...
    }
    /*********************************************************************************/
    var resto_divisao = (qtde) % (pecas_por_embalagem)//Verifica o Mod (Resto da Divis�o) ...    
    if(resto_divisao != 0 && !isNaN(resto_divisao)) {//Representa que n�o est� compat�vel a Qtde de Pe�as / Embalagem ...
        var sugestao = (parseInt(qtde / pecas_por_embalagem) + 1) * pecas_por_embalagem
        /*Se a Fam�lia = 'PINOS' 
                ou Fam�lia = 'LIMAS' 
                ou Fam�lia = 'RISCADOR' 
                ou Fam�lia = 'SACA BUCHA' 
                ou Fam�lia = 'CHAVES PARA MANDRIL' 
                ou Fam�lia = 'FLU�DOS, �LEOS, LUBRIFICANTES' 
                ou Fam�lia = 'CABO DE LIMA' 
                ou Fam�lia = 'BROCAS' 
                ou Fam�lia = 'ROSCA POSTI�A' 
                ou a refer�ncia do PA est� dentro do vetor de "vetor_ref_embals_travadas" 
                n�o d� op��o p/ o usu�rio abrir a embalagem ...*/
        if(id_familia == 2 || id_familia == 3 || id_familia == 7 || id_familia == 17 || id_familia == 19 || id_familia == 26 || id_familia == 27 || id_familia == 29 || id_familia == 30 || pa_de_embalagem_travada == 'S') {
            alert('A QTDE DO     '+referencia+'     N�O EST� COMPAT�VEL COM A QTDE DE P�S / EMBALAGEM ! \nALTERE A QUANTIDADE !!!        SUGEST�O  =  '+sugestao+'  .')
            return 0//O Script teve que parar por si s�, porque existe uma diverg�ncia ...
        }else {
            var pergunta = confirm('A QTDE DO     '+referencia+'     N�O EST� COMPAT�VEL COM A QTDE DE P�S / EMBALAGEM ! \n DESEJA MANTER EST� QUANTIDADE ?        SUGEST�O  =  '+sugestao+'  .')
            if(pergunta == false) {//N�o aceitou a qtde incompat�vel
                //Fun��o foi chamada atrav�s do evento onblur ...
                if(typeof(posicao) == 'undefined') document.form.reset()
                return 1//Usu�rio clicou no bot�o Cancelar ...
            }else {
                return 2//Usu�rio clicou no bot�o OK ...
            }
        }
    }else if(minimo_de_venda > 0) {
        if((qtde * preco_liquido_final) < lote_min_producao_reais) {
            if(qtde < minimo_de_venda) {
                alert('A QTDE M�NIMA DE VENDA DO     '+referencia+'     �  DE  =  '+minimo_de_venda+'  p�s.')
                return 0//O Script teve que parar por si s�, porque existe uma diverg�ncia ...
            }
        }
    }else {
        return 3//O Script foi executado sem nenhum problema, pode continuar ...
    }
}