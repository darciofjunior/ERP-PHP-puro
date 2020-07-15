/*Função Pop-Up

Objetivo: Abre Pop-Up com alinhamentos, efeito e controla as propriedades do Pop-Up
Data de Criação: 16/06/2003
Última Modificação: 25/06/2003
Observação: Conservar em local fresco

Parâmetros

endereço = Arquivo que vai ser aberto
nome = Nome do Pop-Up
fullscreen = Abre o Pop-Up em Tela Cheia

efeito = Abre o Pop-Up com Efeito
vel_altura = É a velocidade da altura, trabalha junto do parâmetro efeito
vel_larg = É a velocidade da largura, trabalha junto do parâmetro efeito

tamanhoaltura = Altura do Pop-Up
tamanholargura = Largura do Pop-Up
alinh_hor = Alinhamento Horizontal do Pop-Up: C - Centralizado, R - Direita, L - Esquerda
alinha_vert = Alinhamento Vertical do Pop-Up: C - Centralizado, U - Acima, D - Abaixo
espaco_hor = Deixa um Espaco entre o Pop-Up e a Margem Direita ou Esquerda
espaco_vert = Deixa um Espaco entre o Pop-Up e a Margem Acima ou Abaixo

propriedades = Neste parâmetro eu controlo as propriedades que desejo que apareça no Pop-Up
scrollbars = Habilita ou desabilita a barra de Rolagem, trabalha junto do parâmetro Propriedades
toolbar = Habilita ou desabilita a barra de Ferramentas, trabalha junto do parâmetro Propriedades
menubar = Habilita ou desabilita a barra de Menus, trabalha junto do parâmetro Propriedades
status = Habilita ou desabilita a barra de Status, trabalha junto do parâmetro Propriedades*/

function nova_janela(endereco, nome, fullscreen, efeito, vel_altura, vel_larg, tamanhoaltura, tamanholargura, alinh_hor, alinh_vert, espaco_hor, espaco_vert, propriedades, scrollbars, toolbar, menubar, status) {
	if((fullscreen == 'f') || (fullscreen == 'F')) {
		tamanhoaltura = screen.height
		tamanholargura = screen.width
	}else {
		if(typeof(tamanhoaltura) == 'undefined' || (tamanhoaltura) == '') {
			alert('PREENCHA O TAMANHO ALTURA !')
			return false
		}
		if(tamanhoaltura < 0) {
			alert('TAMANHO ALTURA INVÁLIDA !')
			return false
		}
		if(typeof(tamanholargura) == 'undefined' || (tamanholargura) == '') {
			alert('PREENCHA O TAMANHO LARGURA !')
			return false
		}
		if(tamanholargura < 0) {
			alert('TAMANHO LARGURA INVÁLIDA !')
			return false
		}
		if(typeof(alinh_hor) == 'undefined' || (alinh_hor) == '') {
			alert('PREENCHA O ALINHAMENTO HORIZONTAL !')
			return false
		}
		if(typeof(alinh_vert) == 'undefined' || (alinh_vert) == '') {
			alert('PREENCHA O ALINHAMENTO VERTICAL !')
			return false
		}
		switch (alinh_hor) {
			case 'C':
			case 'c':
				var largura = (screen.width / 2)
				var inicio_left = (largura - (tamanholargura / 2))
			break;
			case 'L':
			case 'l':
				if(typeof(espaco_hor) != 'undefined') {
					switch (espaco_hor) {
						case 'S':
						case 's':
							var largura = (screen.width / 32)
							var inicio_left = largura
						break;
						case 'N':
						case 'n':
						case '':
							var inicio_left = 0
						break;
						default:
							alert('ESPAÇAMENTO HORIZONTAL INVÁLIDO !')
						break;
					}
				}
			break;
			case 'R':
			case 'r':
				if(typeof(espaco_hor) != 'undefined') {
					switch (espaco_hor) {
						case 'S':
						case 's':
							var largura = (screen.width / 32)
							var inicio_left = largura
							var margem_direita = inicio_left + eval(tamanholargura)
							if(tamanholargura < 100) {
								margem_direita = screen.width - 132
								inicio_left = margem_direita
							}else {
								margem_direita = screen.width - margem_direita
								inicio_left = margem_direita
							}
						break;
						case 'N':
						case 'n':
						case '':
							var margem_direita = tamanholargura
							margem_direita = screen.width - margem_direita
							inicio_left = margem_direita
						break;
						default:
							alert('ESPAÇAMENTO HORIZONTAL INVÁLIDO !')
						break;
					}
				}
			break;
			case '':
			break;
			default:
				alert('ALINHAMENTO HORIZONTAL INVÁLIDO !')
			break;
		}
		switch(alinh_vert) {
			case 'C':
			case 'c':
				var altura = (screen.height / 2)
				var inicio_top = (altura - (tamanhoaltura / 2))
			break;
			case 'U':
			case 'u':
				if(typeof(espaco_vert) != 'undefined') {
					switch (espaco_vert) {
						case 'S':
						case 's':
							var altura = (screen.height / 24)
							var inicio_top = altura
						break;
						case 'N':
						case 'n':
						case '':
							var inicio_top = 0
						break;
						default:
							alert('ESPAÇAMENTO VERTICAL INVÁLIDO !')
						break;
					}
				}
			break;
			case 'D':
			case 'd':
				if(typeof(espaco_vert) != 'undefined') {
					switch (espaco_vert) {
						case 'S':
						case 's':
							var altura = (screen.height / 24)
							var inicio_top = altura
							var margem_inferior = inicio_top + eval(tamanhoaltura)
							margem_inferior = screen.height - margem_inferior
							inicio_top = margem_inferior
						break;
						case 'N':
						case 'n':
						case '':
							var margem_inferior = tamanhoaltura
							margem_inferior = screen.height - margem_inferior
							inicio_top = margem_inferior
						break;
						default:
							alert('ESPAÇAMENTO VERTICAL INVÁLIDO !')
						break;
					}
				}
			break;
			case '':
			break;
			default:
				alert('ALINHAMENTO VERTICAL INVÁLIDO !')
			break;
		}
	}
	if((efeito) == 'S' || (efeito) == 's') {
		if(typeof(vel_altura) == 'undefined' || (vel_altura) == '') {
			alert('PREENCHA A VELOCIDADE ALTURA !')
			return false
		}
		if(typeof(vel_larg) == 'undefined' || (vel_larg) == '') {
			alert('PREENCHA A VELOCIDADE LARGURA !')
			return false
		}
	}
	if((propriedades == 'S') || (propriedades == 's')) {
		if(typeof(scrollbars) == 'undefined') {
			alert('PREENCHA O SCROLLBARS !')
			return false
		}
		if(typeof(toolbar) == 'undefined') {
			alert('PREENCHA O TOOLBAR !')
			return false
		}
		if(typeof(menubar) == 'undefined') {
			alert('PREENCHA O MENUBAR !')
			return false
		}
		if(typeof(status) == 'undefined') {
			alert('PREENCHA O STATUS !')
			return false
		}
		switch(scrollbars) {
			case 'S':
			case 's':
				var barra = 'yes'
			break;
			case 'N':
			case 'n':
			case '':
				var barra = 'no'
			break;
			case 'A':
			case 'a':
				var barra = 'auto'
			break;
			default:
				alert('SCROLLBARS INVÁLIDO !')
				return false
			break;
		}
		switch(toolbar) {
			case 'S':
			case 's':
				var ferramenta = 'yes'
			break;
			case 'N':
			case 'n':
			case '':
				var ferramenta = 'no'
			break;
			default:
				alert('TOOLBAR INVÁLIDO !')
				return false
			break;
		}
		switch(menubar) {
			case 'S':
			case 's':
				var menu = 'yes'
			break;
			case 'N':
			case 'n':
			case '':
				var menu = 'no'
			break;
			default:
				alert('MENU INVÁLIDO !')
				return false
			break;
		}
		switch(status) {
			case 'S':
			case 's':
				var stat = 'yes'
			break;
			case 'N':
			case 'n':
			case '':
				var stat = 'no'
			break;
			default:
				alert('STATUS INVÁLIDO !')
				return false
			break;
		}
		janela = window.open(endereco, nome, 'top='+inicio_top+',left='+inicio_left+', width='+tamanholargura+',height='+tamanhoaltura+',scrollbars='+barra+', toolbar='+ferramenta+', menubar='+menu+', status='+stat)
	}else {
		janela = window.open(endereco, nome, 'top='+inicio_top+',left='+inicio_left+', width='+tamanholargura+',height='+tamanhoaltura+',scrollbars=auto, toolbar=no, menubar=no, status=no')
	}
	if(typeof(efeito) != 'undefined') {
		switch (efeito) {
			case 'S':
			case 's':
				for (var tamanho_altura = 1; tamanho_altura < tamanhoaltura; tamanho_altura += eval(vel_altura)) {
					janela.resizeTo('1', tamanho_altura)
				}
				for (var tamanho_largura = 0; tamanho_largura < tamanholargura; tamanho_largura += eval(vel_larg)) {
					janela.resizeTo(tamanho_largura, tamanho_altura)
				}
			break;
			case 'N':
			case 'n':
			case '':
			break;
			default:
				alert('EFEITO INVÁLIDO !')
			break;
		}
	}
}