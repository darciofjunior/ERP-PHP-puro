/*Parâmetros
numero - numero passado por parâmetro pelo usuário
num_casas - numero de casas que o usuário deseja retornar para o novo número
cima - significa que o usuário quer arredondar o número para cima
*/

function arred(numero, num_casas, cima) {
	numero = numero.replace(',', '.')
	if(num_casas == 0) {
		if(cima == 0) {
			numero = Math.floor(numero)
		}else {
			numero = Math.ceil(numero)
		}
	}else {	
		var divisor = 1, cont = 0
		var inteiro = '', decimal = ''
		var qtde_casas_decimais=''
		for(i=0;i<num_casas;i++) {
			divisor = divisor + '0'
      		qtde_casas_decimais=qtde_casas_decimais+'0'; // exemplo se for 3 ficara ,00
		}
		var novo_numero = numero * parseInt(divisor)

		if(novo_numero<0) { // se o valor for negativo multipica por - 1 e no final multiplica por -1 novamente
			novo_numero=String(novo_numero*-1);
			var valor_negativo=-1;
		} else {
			novo_numero=String(novo_numero);
			var valor_negativo=1;
		}
		for(i = 0; i < novo_numero.length; i ++) {
			if(novo_numero.charAt(i)=='.') {
				cont = 1
			}else {
				if(cont == 0) {//cont = 0 é a parte inteira
					inteiro = inteiro + novo_numero.charAt(i)
				}else {
					decimal = novo_numero.charAt(i)
					i=novo_numero.length
				}
			}
		}
		if(cima == 1) {
			if(decimal >= 5) {
				inteiro = parseInt(inteiro) + 1
			}
		}
		novo_numero=String(inteiro / divisor)
	}
	for(i = 0; i < novo_numero.length; i++) {
		if(novo_numero.charAt(i) == '.') {
			cont=1
			i=novo_numero.length
		} else {
			cont=0
		}
	}

	if(cont==0) {// quando terá virgula
		novo_numero = String(parseInt(novo_numero) * valor_negativo) + ',' + qtde_casas_decimais
	}else {
		novo_numero = String(parseFloat(novo_numero) * valor_negativo)
		digitos_apos_virgula = 0
//Aqui eu verifico quantas dígitos que estão preenchidos depois da vírgula ...
		for(i = (novo_numero.length - 1); i >= 0; i--) {
			if(novo_numero.charAt(i) == '.') {
				i = 0//P/ Sair do Loop
			}else {
				digitos_apos_virgula++
			}
		}
		qtde_casas_decimais = qtde_casas_decimais.substr(0, qtde_casas_decimais.length - digitos_apos_virgula)
		novo_numero+= qtde_casas_decimais
	}
	novo_numero = novo_numero.replace('.', ',')
	return novo_numero
}