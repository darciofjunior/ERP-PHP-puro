//� uma das poucas fun��es em JS que � utilizada em poucos arquivos do sistema e que foi criada dentro dessa Biblioteca JS ...

/*Normalmente ...*/
function prazo_medio(valor_negociacao, id_funcionario) {
    if(typeof(document.form.txt_prazo_a) == 'object') {
        var prazo1 = (document.form.txt_prazo_a.value == '� vista' || document.form.txt_prazo_a.value == '') ? 0 : eval(document.form.txt_prazo_a.value)
        var prazo2 = eval(document.form.txt_prazo_b.value)
        var prazo3 = eval(document.form.txt_prazo_c.value)
        var prazo4 = eval(document.form.txt_prazo_d.value)
        var prazo1_foco = eval(document.form.txt_prazo_a)
    }else {
        var prazo1 = (document.form.txt_vencimento1.value == '� vista' || document.form.txt_vencimento1.value == '') ? 0 : eval(document.form.txt_vencimento1.value)
        var prazo2 = eval(document.form.txt_vencimento2.value)
        var prazo3 = eval(document.form.txt_vencimento3.value)
        var prazo4 = eval(document.form.txt_vencimento4.value)
        var prazo1_foco = eval(document.form.txt_vencimento1)
    }
    if(prazo4 > 0) {
        var prazo_medio = (prazo1 + prazo2 + prazo3 + prazo4) / 4
    }else if(prazo3 > 0) {
        var prazo_medio = (prazo1 + prazo2 + prazo3) / 3
    }else if(prazo2 > 0) {
        var prazo_medio = (prazo1 + prazo2) / 2
    }else {
        var prazo_medio = prazo1
    }
    /****Nova Regra de Prazo M�dio M�ximo Baseada no Volume de Vendas****/
    /*if(valor_negociacao < 200) {//Menos que R$ 200,00
        var prazo_medio_maximo = 0
    }else if(valor_negociacao < 400) {//Menos que R$ 400,00
        var prazo_medio_maximo = 7
    }else if(valor_negociacao < 600) {//Menos que R$ 600,00
        var prazo_medio_maximo = 14
    }else if(valor_negociacao < 800) {//Menos que R$ 800,00
        var prazo_medio_maximo = 21
    }else if(valor_negociacao < 1200) {//Menos que R$ 1200,00
        var prazo_medio_maximo = 28
    }else if(valor_negociacao < 2000) {//Menos que R$ 2000,00
        var prazo_medio_maximo = 35
    }else if(valor_negociacao < 3000) {//Menos que R$ 3000,00
        var prazo_medio_maximo = 42
    }else if(valor_negociacao < 5000) {//Menos que R$ 5.000,00
        var prazo_medio_maximo = 49
    }else if(valor_negociacao < 7500) {//Menos que R$ 7.500,00
        var prazo_medio_maximo = 56
    }else if(valor_negociacao < 10000) {//Menos que R$ 10.000,00
        var prazo_medio_maximo = 63
    }else {//Acima de R$ 10.000,00 em Vendas ...
        var prazo_medio_maximo = 75
    }*/
    
    /*Essas novas Regras come�aram a vigorar a partir do dia 20/08/2015 �s 13:30 devido 
    reclama��es de vendedores ...*/
    if(valor_negociacao < 800) {//Menos que R$ 800,00
        var prazo_medio_maximo = 28
    }else if(valor_negociacao < 2000) {//Menos que R$ 2.000,00
        var prazo_medio_maximo = 45
    }else {//Acima de R$ 2.000,00 em Vendas ...
        var prazo_medio_maximo = 75
    }
    /********************************************************************/
    //A m�dia dos Prazos da Negocia��o, n�o podem ser superior ao "Prazo M�dio M�ximo" definido acima ...
    if(prazo_medio > prazo_medio_maximo) {
        /*Se o funcion�rio logado for Roberto Chefe "62", Wilson Chefe "68", D�rcio "98" porque programa e 
        Wilson Nishimura "136", ent�o para estes 3 o sistema n�o ir� fazer Compara��o do Prazo M�dio do ORC ...*/
        if(id_funcionario == 62 || id_funcionario == 68 || id_funcionario == 98 || id_funcionario == 136) {
            //A m�dia dos Prazos da Negocia��o, n�o podem ser superior ao "Prazo M�dio M�ximo" definido acima ...
            var resposta = confirm('O PRAZO M�DIO EST� EM '+prazo_medio+' DIAS !!!\n\nO PRAZO M�DIO M�XIMO ACEIT�VEL � "'+prazo_medio_maximo+'" DIAS, PARA ESTE "VALOR TOTAL DOS PRODUTOS", CONFORME A CARTILHA !\n\nTEM CERTEZA DE QUE DESEJA MANTER ESSE PRAZO M�DIO ?')
            if(resposta == true) {//OK ...
                return {
                    'situacao_prazo' : 1,
                    'prazo_medio' : prazo_medio
                }
            }else {
                prazo1_foco.focus()
                prazo1_foco.select()
                return false
            }
        }else {//P/ qualquer outro Funcion�rio eu Impe�o de submeter a Tela ...
            alert('O PRAZO M�DIO EST� EM '+prazo_medio+' DIAS !!!\n\nO PRAZO M�DIO M�XIMO ACEIT�VEL � "'+prazo_medio_maximo+'" DIAS, PARA ESTE "VALOR TOTAL DOS PRODUTOS", CONFORME A CARTILHA !\n\nMODIFIQUE OS PRAZOS DESTA NEGOCIA��O !!!')
            prazo1_foco.focus()
            prazo1_foco.select()
            return false
        }
    }else {
        return {
            'situacao_prazo' : 1,
            'prazo_medio' : prazo_medio
        }
    }
}