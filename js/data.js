//Retorna em dias
function diferenca_datas(campo1, campo2) {
	var meses = new Array('31','28','31','30','31','30','31','31','30','31','30','31')
//Significa que as Datas que foram passadas por Par�metro, estavam de objetos hiddens, texts, ...
	if(typeof(eval(campo1)) == 'object') {
		var data1 = eval(campo1)
		var data2 = eval(campo2)
		var dia1 = data1.value.substr(0,2)
		var dia2 = data2.value.substr(0,2)
		var mes1 = data1.value.substr(3,2)
		var mes2 = data2.value.substr(3,2)
		var ano1 = data1.value.substr(6,4)
		var ano2 = data2.value.substr(6,4)
//Significa que as Datas que foram passadas por Par�metro, foram passadas como simples Strings
	}else {
		var data1 = campo1
		var data2 = campo2
		var dia1 = data1.substr(0,2)
		var dia2 = data2.substr(0,2)
		var mes1 = data1.substr(3,2)
		var mes2 = data2.substr(3,2)
		var ano1 = data1.substr(6,4)
		var ano2 = data2.substr(6,4)
	}

	var dias = 0, inicio_for = 0, fim_for = 0
	var cont = 0, flag = 0

	/*if(parseInt(ano2) < parseInt(ano1)) {
		alert('A SEGUNDA DATA � MENOR QUE A PRIMEIRA !')
		return false
	}else if((parseInt(ano2) == parseInt(ano1)) && (parseInt(mes2) < parseInt(mes1))) {
		alert('A SEGUNDA DATA � MENOR QUE A PRIMEIRA !')
		return false
	}else if((parseInt(ano2) == parseInt(ano1)) && (parseInt(mes2) == parseInt(mes1)) && (dia2 < dia1)) {
		alert('A SEGUNDA DATA � MENOR QUE A PRIMEIRA !')
		return false
	}else {*/
//Aqui s� faz compara��o entre o m�s e o ano
		if(mes1 == mes2 && ano1 == ano2) {
			dif_dia = dia2 - dia1
			dias = dif_dia
		}else if(mes1 != mes2 && ano1 == ano2) {
			dif_dia = parseInt(dia2) - parseInt(dia1)
			dif_mes = parseInt(mes2) - parseInt(mes1)
//Aqui no la�o a vari�vel recebe o mes com valor - 1 devido ao array l� em cima
			for(i = (mes1 - 1); i <= (mes2 - 1); i++) {
//A vari�vel dias aqui vai recebendo o valor total de dias daquele m�s
//Verifica se o m�s est� come�ando em fevereiro
				if(i == 1) {                
//Verifica se o ano � bissexto
					if(ano1 % 4 == 0) {
//Verifica em qual m�s que est� come�ando na primeira data
						if(i == (mes1 - 1)) {
							dias = dias + ((eval(meses[i]) - dia1) + 1)//Somo + 1, devido ser ano Bissexto ...
//Verifica em qual m�s que est� terminando na segunda data
						}else if(i == (mes2 - 1)) {
							dias = dias + eval(dia2)
//Outros meses
						}else {
							dias = dias + eval(meses[i]) + 1
						}
//Aqui o ano n�o � bissexto
					}else {
//Verifica em qual m�s que est� come�ando na primeira data
						if(i == (mes1 - 1)) {
							dias = dias + ((eval(meses[i]) - dia1))
//Verifica em qual m�s que est� terminando na segunda data
						}else if(i == (mes2 - 1)) {
							dias = dias + eval(dia2)
//Outros meses
						}else {
							dias = dias + eval(meses[i])
						}
					}
//Verifica em qual m�s que est� come�ando na primeira data
				}else if(i == (mes1 - 1)) {
					dias = dias + (eval(meses[i]) - eval(dia1))
//Verifica em qual m�s que est� terminando na segunda data
				}else if(i == (mes2 - 1)) {
					dias = dias + eval(dia2)
//Outros meses
				}else {
					dias = dias + eval(meses[i])
				}
			}
		}else {
			dif_dia = parseInt(dia2) - parseInt(dia1)
			dif_mes = parseInt(mes2) - parseInt(mes1)
			dif_ano = parseInt(ano2) - parseInt(ano1)
			for(i = ano1; i <= eval(ano1) + dif_ano; i++) {
//Aqui ele verifica quais s�o os meses que ele ter� que percorrer no la�o
//Verifica se � o primeiro ano a ser percorrido no la�o
				if(i == ano1) {
					inicio_for = (mes1 - 1)
					fim_for = 12
					cont = 1
//Verifica se � o �ltimo ano a ser percorrido no la�o
				}else if(i == eval(ano1) + dif_ano) {
					inicio_for = 0
					fim_for = mes2
				}else {
					inicio_for = 0
					fim_for = 12
				}
				for(j = inicio_for; j < fim_for; j++) {
//A vari�vel dias aqui vai recebendo o valor total de dias daquele m�s
					if(j == 1) {
						if(i % 4 == 0) {
							if(i == eval(ano1) + dif_ano) {
								if(j + 1 == fim_for) {
									dias = dias + eval(dia2)
								}else {
									dias = dias + eval(meses[j]) + 1
								}
							}else {
								if(cont == 1) {
									dias = dias + (eval(meses[j]) - dia1)
								}else {
									dias = dias + eval(meses[j]) + 1
								}
							}
						}else {
							if(i == eval(ano1) + dif_ano) {
								if(j + 1 == fim_for) {
									dias = dias + eval(dia2)
								}else {
									dias = dias + eval(meses[j])
								}
							}else {
								if(cont == 1) {
									dias = dias + (eval(meses[j]) - dia1)
								}else {
									dias = dias + eval(meses[j])
								}
							}
						}
					}else {
						if(i == eval(ano1) + dif_ano) {
							if(j + 1 == fim_for) {
								dias = dias + eval(dia2)
							}else {
								dias = dias + eval(meses[j])
							}
						}else {
							if(cont == 1) {
								dias = dias + (eval(meses[j]) - dia1)
							}else {
								dias = dias + eval(meses[j])
							}
						}
					}
					cont = 0
				}
			}
		}
	//}
    if(dias > 0 && dias < 10)   dias = '0' + dias
    if(dias < 0)                dias = '0'
    return dias
}

//Retorna uma nova data
function nova_data(campo, campo_retorno, dias) {
//Essa vari�vel tem a fun��o de armazenar a string campo passada por par�metro
	var campo_antigo = campo
	campo_retorno = eval(campo_retorno)

	if(campo == '') {
		var nova_data = ''
	}else {
		var data = eval(campo)
		if(typeof(data) == 'object') {
			data = data.value
		}else {
			data = campo_antigo
		}
//Verifica de que tipo que � o parametro dias
		if(dias == '[object]' || dias == '[object HTMLInputElement]') {
			var elemento = dias.value
		}else {
			if(typeof(dias) != 'number') {
				var elemento = eval(dias+'.value')
			}else {
				var elemento = eval(dias)
			}
		}
//A vari�vel elemento foi a que passou a assumir a qtde de dias, passada por param.
		if(typeof(campo) == 'undefined' || data == '') {
			var nova_data = ''
		}else if(elemento == 0) {
			var nova_data = data
		}else {
			var meses = new Array('31','28','31','30','31','30','31','31','30','31','30','31')
			var flag = 0

			var dia = data.substr(0,2)
			var mes = data.substr(3,2)
			var ano = data.substr(6,4)

			var conta_dias = elemento
			var desvio = 0
//Significa que eu quero somar mais dias na data passada por par�metro
			if(elemento > 0) {
				while(conta_dias > 0) {
					if(mes == 13) {
						mes = 1
						ano = eval(ano) + 1
					}
					if(mes == 2) {
						if(ano % 4 == 0) {
//Controla quantos dias ainda restam referentes ao do m�s digitado na caixa de data
							if(desvio == 0) {
								diferenca = eval((meses[mes - 1]) - dia) + 1
								desvio = 1
								bissexto = 0
							}else {
								diferenca = eval(meses[mes - 1]) + 1
								bissexto = 0
							}
						}else {
							if(desvio == 0) {
								diferenca = eval(meses[mes - 1]) - dia
								desvio = 1
								bissexto = 1
							}else {
								diferenca = eval(meses[mes - 1])
								bissexto = 1
							}
						}
					}else {
						if(desvio == 0) {
							diferenca = eval(meses[mes - 1]) - dia
							desvio = 1
							bissexto = 1
						}else {
							diferenca = eval(meses[mes - 1])
							bissexto = 1
						}
					}
//Aqui a vari�vel vai receber o novo dia, da nova data se o c�lculo for negativo
					if(eval(conta_dias - diferenca) <= 0) {
						if(flag == 1) {
							resto = conta_dias
						}else {
//Aqui faz a soma dos dias daquele m�s pr�prio m�s digitado
							resto = eval(dia) + eval(elemento)
						}
					}
					conta_dias = conta_dias - diferenca
					mes ++
//Flag s� � utiz. para saber que o valor do m�s foi trocado
					flag = 1
				}
				mes = mes - 1
//Nessa parte se subtrai um do m�s, porque no la�o ele termina com um a mais
				if(mes < 10) {
					mes = '0' + mes
				}
				if(conta_dias <= 0) {
					conta_dias = resto
				}
				if(conta_dias < 10) {
					conta_dias = '0' + conta_dias
				}
//Significa que eu quero retirar dias da data passada por par�metro
			}else {
				while(conta_dias <= 0) {
					if(mes == 0) {
						mes = 12
						ano = eval(ano) - 1
					}
					if(mes == 2) {
						if(ano % 4 == 0) {
//Somente na primeira vez, sem trocar o m�s
							if(desvio == 0) {
								diferenca = eval(dia)
								desvio = 1
								bissexto = 0
//Demais vezes, j� trocou o m�s
							}else {
								diferenca = eval(meses[mes - 1]) + 1
								bissexto = 0
							}
						}else {
//Somente na primeira vez, sem trocar o m�s
							if(desvio == 0) {
								diferenca = eval(dia)
								desvio = 1
								bissexto = 1
//Demais vezes, j� trocou o m�s
							}else {
								diferenca = eval(meses[mes - 1])
								bissexto = 1
							}
						}
					}else {
//Somente na primeira vez, sem trocar o m�s
						if(desvio == 0) {
							diferenca = eval(dia)
							desvio = 1
							bissexto = 1
//Demais vezes, j� trocou o m�s
						}else {
							diferenca = eval(meses[mes - 1])
							bissexto = 1
						}
					}
//Aqui a vari�vel vai receber o novo dia, da nova data se o c�lculo for negativo
					if(eval(conta_dias) + eval(diferenca) >= 0) {
//Significa que j� trocou de m�s pelo menos uma vez
						if(flag == 1) {
//Subtrai o total de dias do m�s corrente pela qtde de dias restante (-20, -30 , ...)
							resto = eval(meses[mes - 1]) + conta_dias
//Aqui � um c�lculo para se acontecer no mesmo m�s, n�o chegou a virar o m�s ainda
						}else {
//Aqui faz a retirada dos dias daquele m�s pr�prio m�s digitado
//conta_dias = qtde de dias q se deseja retirar
							resto = eval(conta_dias) + eval(diferenca)
						}
					}
					conta_dias = eval(conta_dias) + eval(diferenca)
					mes --
//Flag s� � utiz. para saber que o valor do m�s foi trocado
					flag = 1
				}
				mes = mes + 1
//Nessa parte se subtrai um do m�s, porque no la�o ele termina com um a mais
				if(mes < 10) {
					mes = '0' + mes
				}
				if(conta_dias >= 0) {
					conta_dias = resto
				}
				if(conta_dias < 10) {
					conta_dias = '0' + conta_dias
				}
			}
			nova_data = conta_dias + '/' + mes + '/' + ano
		}
	}
	campo_retorno.value = nova_data
}

/*Fun��o que tem por objetivo, verificar se a Data passado por par�metro � v�lida ...

Exemplo: 30/02/???? - Data Inv�lida porque n�o existe ...*/
function validar_data(dia, mes, ano) {
    if(ano > 1900) {
        //For�o essa vari�vel mes ser String porque: case '11' <- � String e n�o n�mero por estar entre '' ...
        mes = String(mes)

        switch(mes) {//Verifico qual � o m�s do ano que foi passado p/ saber quantos dias este m�s pode ter ...
            case '01'://Janeiro ...
            case '03'://Mar�o ...
            case '05'://Maio ...
            case '07'://Julho ...
            case '08'://Agosto ...
            case '10'://Outubro ...
            case '12'://Dezembro ...
                if(dia <= 31) return 1
            break;
            case '04'://Abril ...
            case '06'://Junho ...
            case '09'://Setembro ...
            case '11'://Novembro ...
                if(dia <= 30) return 1
            break;
            case '02'://Fevereiro ...
                var bissexto;
                //Validando ano Bissexto / fevereiro / dia ...
                if((ano % 4 == 0) || (ano % 100 == 0) || (ano % 400 == 0)) bissexto = 1
                if((bissexto == 1) && (dia <= 29)) return 1//Ano Bissexto ...
                if((bissexto != 1) && (dia <= 28)) return 1//Ano Normal ...
            break;                        
        }
    }
    return 0
}