function showHide(nomedoIframe){
    div = "status" + nomedoIframe + " " //preciso deste espaco para poder retirar uma linha no codigo q chama esta funcao e n~ dê erro no mozilla 
	btn = "btn" + nomedoIframe
	//document.images[btn].src
	iframe = (document.getElementById)?document.getElementById(nomedoIframe):document.all(nomedoIframe);
    if(document.getElementById){
		if(iframe.style.display == "none"){
			iframe.style.display = "block";
			//document.images[btn].src = "imagens/PFisica/btn_ocultar.gif";
			//document.getElementById(div).innerHTML = "<font color='yellow'>Ocultar</font>";
		} else {
			iframe.style.display = "none";
			//document.images[btn].src = "imagens/PFisica/btn_mostrar.gif";
			//////////////////document.getElementById(div).innerHTML = "Exibir";
		}
	} else { // até então este passo nao existe
        // deixei alert como passo dois para descobrir em qual situação chama ela
        if(iframe.visibility == "hidden"){
			iframe.visibility = "visible";
			document.images[btn].src = "imagens/PFisica/btn_ocultar.gif";
		} else {
			iframe.visibility = "hidden";
			document.images[btn].src = "imagens/PFisica/btn_mostrar.gif";
		}
	}
}

function habilitar_desabilitar(formulario, campo, valores, campos, tipo) {
    var x, elemento      = eval('document.'+formulario+'.'+campo+'')  
    var y, objeto, index = eval('document.'+formulario+'.'+campo+'.selectedIndex')
    var matriz1 = new Array(), auxiliar1 = 0, indice1 = 0, posicao1 = 0
    var matriz2 = new Array(), auxiliar2 = 0, indice2 = 0, posicao2 = 0

	for (x = 0; x < valores.length; x ++) {		
		if (valores.substr(x, 1) == ',') {
			if (auxiliar1 == 1) {
					matriz1[indice1] = valores.substr(posicao1, auxiliar1)			
					auxiliar1 = 0
					indice1   ++						
			}else if (auxiliar1 > 1) {
					matriz1[indice1] = valores.substr(posicao1 - auxiliar1 + 1, auxiliar1)
					auxiliar1 = 0
					indice1   ++							
			}	
		}else {
			auxiliar1 ++ 
		}
		posicao1 = x
	}
		
	for (x = 0; x < campos.length; x ++) {		
		if (campos.substr(x, 1) == ',') {	
			if (auxiliar2 == 1) {
					matriz2[indice2] = campos.substr(posicao2, auxiliar2)			
					auxiliar2 = 0
					indice2   ++						
			}else if (auxiliar2 > 1) {
					matriz2[indice2] = campos.substr(posicao2 - auxiliar2 + 1, auxiliar2)			
					auxiliar2 = 0
					indice2   ++
			}	
		}else {
			auxiliar2 ++ 
		}
		posicao2 = x
	}
	
	for (x = 0; x < indice1; x ++) {
		if (tipo == '1') {
			if (matriz1[x]	== elemento.options[index].value) {
				for (y = 0; y < indice2; y ++) {
					objeto 			= eval('document.'+formulario+'.'+matriz2[y]+'')
					objeto.disabled = true
				}
				break
			}else {
				for (y = 0; y < indice2; y ++) {
					objeto 			= eval('document.'+formulario+'.'+matriz2[y]+'')
					objeto.disabled = false
				}
			}	
		}else {
			if (matriz1[x]	!= elemento.options[index].value) {
				for (y = 0; y < indice2; y ++) {
					objeto 			= eval('document.'+formulario+'.'+matriz2[y]+'')
					objeto.disabled = true
				}
				break
			}else {
				for (y = 0; y < indice2; y ++) {
					objeto 			= eval('document.'+formulario+'.'+matriz2[y]+'')
					objeto.disabled = false
				}		
			}			
		}
	}
}

function desabilitar(formulario) {
    var elementos = eval('document.'+formulario+'.elements')
    for(var i = 0; i < elementos.length; i++) {
        if(elementos[i].disabled == false) elementos[i].disabled = true
    }
}

function habilitar(formulario) {
    var elementos   = eval('document.'+formulario+'.elements')
    var conteudo    = eval('document.'+formulario+'')  
    for (var i = 0; i < elementos.length; i++) {
        if(elementos[i].disabled == true) elementos[i].disabled = false
    }
}

function verifica(objeto, tipo, checar, operador, event) {
    if (navigator.appName == 'Microsoft Internet Explorer') {
        if (event.keyCode == 16 || event.keyCode == 37 || event.keyCode == 38 || event.keyCode == 39 || event.keyCode == 40) {
            return false
        }
    }else {
        if(event.which == 16 || event.which == 37 || event.which == 38 || event.which == 39 || event.which == 40) {
            return false
        }
    }
    if(objeto == '[object]' || objeto == '[object HTMLInputElement]') {
        var elemento = objeto
    }else {
        var elemento = eval(objeto)
    }
    switch(tipo) {
        case 'moeda_especial': //moeda especial verifica(objeto, tipo, casas_decimais, operador, event);
            casa_decimal = eval(checar)
            if(typeof(casa_decimal) == 'undefined') casa_decimal = 2
            
            checar      = '1234567890'
            caracter1   = '.'
            caracter2   = ','
            
            var x, y, tamanho1 = elemento.value.length, tamanho2, auxiliar1, auxiliar2
            var valor   = 0
            if(operador == 1) {//Situção em que aceita números positivos e negativos ...
                var checar2 = '-+'
            }else if(operador == 2) {//Situção em que aceita somente números negativos ...
                var checar2 = '-'
            }else {//Situção em que aceita somente números positivos ...
                var checar2 = ''
            }
            for (x = 0; x < tamanho1; x ++)
                if ((elemento.value.charAt(x) == checar2.charAt(0)) && (elemento.value.charAt(x) != caracter2)) {
                    valor = 1
                    elemento.value  = elemento.value.replace('-', '')
                }else if ((elemento.value.charAt(x) == checar2.charAt(1)) && (elemento.value.charAt(x) != caracter2))
                    valor = 0
                    elemento.value  = elemento.value.replace('--', '-')
                    elemento.value  = elemento.value.replace('-', '')
                    elemento.value  = elemento.value.replace('+', '')
                    if (elemento.value != '0' && elemento.value.length>0 && elemento.value != '-' && elemento.value != '+') {
                            for (x = 0; x < tamanho1; x ++)
                                    if ((elemento.value.charAt(x) != '0') && (elemento.value.charAt(x) != caracter2))
                                            break
                                            auxiliar1 = ''
                                            for (; x<tamanho1; x++)
                                                    if (checar.indexOf(elemento.value.charAt(x)) != -1)
                                                            auxiliar1 += elemento.value.charAt(x)
                                                            auxiliar1 += ''
                                                            tamanho1 = auxiliar1.length
                                                    switch(casa_decimal){
                                                                    case 0:
                                                                        if (tamanho1 == 0)
                                                                                elemento.value = ''
                                                                        if (tamanho1 > 0)
                                                                                elemento.value = auxiliar1
                                                                                caracter2=''
                                                                    case 1:
                                                                            if (tamanho1 == 0)
                                                                                    elemento.value = ''
                                                                            if (tamanho1 == 1)
                                                                                    elemento.value = '0' + caracter2 +  auxiliar1
                                                                    break;
                                                                    case 2:
                                                                            if (tamanho1 == 0)
                                                                                    elemento.value = ''
                                                                            if (tamanho1 == 1)
                                                                                    elemento.value = '0' + caracter2 + '0' + auxiliar1
                                                                            if (tamanho1 == 2)
                                                                                    elemento.value = '0' + caracter2 +auxiliar1
                                                                    break;
                                                                    case 3:
                                                                            if (tamanho1 == 0)
                                                                                    elemento.value = ''
                                                                            if (tamanho1 == 1)
                                                                                    elemento.value = '0' + caracter2 + '00' + auxiliar1
                                                                            if (tamanho1 == 2)
                                                                                    elemento.value = '0' + caracter2 +'0'+auxiliar1
                                                                            if (tamanho1 == 3)
                                                                                    elemento.value = '0' + caracter2 +auxiliar1
                                                                    break;
                                                                    case 4:
                                                                            if (tamanho1 == 0)
                                                                                    elemento.value = ''
                                                                            if (tamanho1 == 1)
                                                                                    elemento.value = '0' + caracter2 + '000' + auxiliar1
                                                                            if (tamanho1 == 2)
                                                                                    elemento.value = '0' + caracter2 +'00'+auxiliar1
                                                                            if (tamanho1 == 3)
                                                                                    elemento.value = '0' + caracter2 +'0'+auxiliar1
                                                                            if (tamanho1 == 4)
                                                                                    elemento.value = '0' + caracter2 +auxiliar1
                                                                    break;
                                                            }
                                                                    if (tamanho1>casa_decimal) {
                                                                            auxiliar2 = ''
                                                                        val_aux=casa_decimal+1;
                                                                            for (y=0,x=tamanho1-val_aux; x>=0; x--) {
                                                                                    if (y == 3) {
                                                                                            auxiliar2+=caracter1
                                                                                            y=0
                                                                                    }
                                                                                    auxiliar2+=auxiliar1.charAt(x)
                                                                                    y ++
                                                                            }
                                                                            elemento.value = ''
                                                                            tamanho2 = auxiliar2.length
                                                                            for (x=tamanho2-1; x>=0;x--)
                                                                                    elemento.value += auxiliar2.charAt(x)
                                                                                    elemento.value += caracter2 + auxiliar1.substr(tamanho1 - casa_decimal, tamanho1)
                                                                    }
            }else if(elemento.value == 0) {
                switch(casa_decimal) {
                    case 1:
                        elemento.value = '0,0'
                    break;
                    case 2:
                        elemento.value = '0,00'
                    break;
                    case 3:
                        elemento.value = '0,000'
                    break;
                    case 4:
                        elemento.value = '0,0000'
                    break;
                }
            }
            //Sempre que o operador for = a 2, retornará o número como sendo negativo ...
            if(valor == 1 || operador == 2) {
                if(elemento.value != '') elemento.value = '-' + elemento.value
            }
        break;
            case 'moeda': //moeda
                    checar='1234567890';
                    caracter1='.'; caracter2=',';
                    var x, y, tamanho1 = elemento.value.length, tamanho2, auxiliar1, auxiliar2
                    var valor=0
                    if(operador==1) {
                            var checar2 = '-+'
                    }else {
                            var checar2 = ''
                    }
            ////////////////////////////////////////////////////
                    for (x = 0; x < tamanho1; x ++)
                            if ((elemento.value.charAt(x) == checar2.charAt(0)) && (elemento.value.charAt(x) != caracter2))
                                    valor=1
                            else if ((elemento.value.charAt(x) == checar2.charAt(1)) && (elemento.value.charAt(x) != caracter2))
                                    valor=0
                                    elemento.value  = elemento.value.replace('-', '')
                                    elemento.value  = elemento.value.replace('+', '')
                                    if (elemento.value != '0' && elemento.value.length>0 && elemento.value != '-' && elemento.value != '+') {
                                            for (x = 0; x < tamanho1; x ++)
                                                    if ((elemento.value.charAt(x) != '0') && (elemento.value.charAt(x) != caracter2))
                                                            break
                                                            auxiliar1 = ''
                                                            for (; x < tamanho1; x ++)
                                                                    if (checar.indexOf(elemento.value.charAt(x)) != -1)
                                                                            auxiliar1 += elemento.value.charAt(x)
                                                                            auxiliar1 += ''
                                                                            tamanho1 = auxiliar1.length
                                                                            if (tamanho1 == 0)
                                                                                    elemento.value = ''
                                                                                    if (tamanho1 == 1)
                                                                                            elemento.value = '0' + caracter2 + '0' + auxiliar1
                                                                                    if (tamanho1 == 2)
                                                                                            elemento.value = '0' + caracter2 + auxiliar1
                                                                                    if (tamanho1 > 2) {
                                                                                            auxiliar2 = ''
                                                                                            for (y = 0, x = tamanho1 - 3; x >= 0; x --) {
                                                                                                    if (y == 3) {
                                                                                                            auxiliar2 += caracter1
                                                                                                            y = 0
                                                                                            }
                                                                                            auxiliar2 += auxiliar1.charAt(x)
                                                                                            y ++
                                                                                    }
                                                                                    elemento.value = ''
                                                                                    tamanho2 = auxiliar2.length
                                                                                    for (x = tamanho2 - 1; x >= 0; x --)
                                                                                            elemento.value += auxiliar2.charAt(x)
                                                                                            elemento.value += caracter2 + auxiliar1.substr(tamanho1 - 2, tamanho1)
                                    }
                            }else if(elemento.value == '0'){
                    elemento.value = '0,00'
            }
            if(valor==1){
                    elemento.value ="-"+elemento.value
            }
        break;
        case 'data':
            if (navigator.appName == 'Microsoft Internet Explorer') {
                if (event.keyCode==8 || event.keyCode==46) {
                    return false;
                }
            }else {
                if (event.which==8 || event.which==46) {
                    return false;
                }
            }
			    checar='1234567890';
			    caracter1='/';
			var x, y, tamanho1 = elemento.value.length, tamanho2, auxiliar1, auxiliar2
				if (elemento.value.length>0) {
					for (x = 0; x < tamanho1; x ++)
							break
							auxiliar1 = ''
							for (; x < tamanho1; x ++)
								if (checar.indexOf(elemento.value.charAt(x)) != -1)
									auxiliar1 += elemento.value.charAt(x)+''
									tamanho1 = auxiliar1.length
										if (tamanho1 >= 0) {
											auxiliar2 = ''
											for (y = 0, x = tamanho1 - 3; x >= 0; x --) {
											    auxiliar2 += auxiliar1.charAt(x)
											   // y ++
										    }
        										elemento.value='';
	    									tamanho2 = auxiliar2.length
		    								for (x = tamanho2; x >= 0; x --)
											elemento.value+=auxiliar2.charAt(x)
											elemento.value+=auxiliar1.substr(tamanho1 - 2, tamanho1)
				                    	}
				  }
for(x=0;x<elemento.value.length;x++) {
    elemento.value=elemento.value.replace('/','');
}
//alert(event.keyCode)
var tamanho_data=elemento.value;
elemento.value='';
for(x=0;x<tamanho_data.length;x++) {
elemento.value=elemento.value + tamanho_data.charAt(x);
	if (navigator.appName == 'Microsoft Internet Explorer') {
//       if((event.keyCode==111 || event.keyCode==193) && elemento.value.substr(elemento.value.length-1, 1)=='/'){
  //          elemento.value=elemento.value.substr(0, elemento.value.length-1);
    //   }
   //     if((elemento.value.length==2 || elemento.value.length==5) &&(event.keyCode==111 || event.keyCode==193)){
     //       elemento.value=elemento.value+'/';
       // }
        switch(elemento.value.length) {
    		case 1:
    		//    if (event.keyCode != 8 && event.keyCode != 111) {
                        if(elemento.value>3) {
                        elemento.value=''
                    }
              //  }
            break;
    		case 2:
    //		    if (event.keyCode != 8 && event.keyCode != 111) {
                    if(elemento.value>31 || elemento.value<1 ) {
                        elemento.value=elemento.value.substr(0, 1)
                    }else {
                        elemento.value=elemento.value.substr(0, 2)+'/';
                    }
      //          }
            break;

    		case 3:
 //   		    if (event.keyCode != 8 && event.keyCode != 111) {
                    if(elemento.value.substr(0, 2)<32 && elemento.value.substr(0, 2)>0) {
                        if(elemento.value.substr(2, 1)!='/') {
                            elemento.value=elemento.value.substr(0, 2);
                        }else {
                            elemento.value=elemento.value.substr(0, 2)+'/';
                        }
                    }else {
                        elemento.value='';
                    }
   //             }
            break;
    		case 4:
    	//	    if (event.keyCode != 8 && event.keyCode != 111) {
                    if(elemento.value.substr(3, 1)>1) {
                        elemento.value=elemento.value.substr(0, 3)
                    }else {
                        elemento.value=elemento.value.substr(0, 4)
                    }
          //      }
            break;
    		case 5:
    //		    if (event.keyCode != 8 && event.keyCode != 111) {
                    if(elemento.value.substr(3, 2)>12 || elemento.value.substr(3, 2)<1) {
                        elemento.value=elemento.value.substr(0, 4);
                    }else {
                        elemento.value=elemento.value.substr(0, 5)+'/';
                    }
      //          }

            break;
    		case 6:
//    		    if (event.keyCode != 8 && event.keyCode != 111) {
                    if(elemento.value.substr(3, 2)<13 && elemento.value>00) {
                        if(elemento.value.substr(5, 1)!='/') {
                            elemento.value=elemento.value.substr(0, 5);
                        }else {
                            elemento.value=elemento.value.substr(0, 5)+'/';
                        }
                    }else {
                        elemento.value=elemento.value.substr(0, 6);
                    }
  //              }
            break;
            case 7:
   // 		    if (event.keyCode != 8 && event.keyCode != 111) {
                    if(elemento.value.substr(6, 1)==0) {
                        elemento.value=elemento.value.substr(0, elemento.value.length-1) ;
                    }
     //           }
            break;
            case 10:
    	//	    if (event.keyCode != 8 && event.keyCode != 111) {
                    if(elemento.value.substr(6, 4)<1900) {
                        elemento.value=elemento.value.substr(0, elemento.value.length-4) ;
                    }
         //       }
            break;
        }
	}else {
  //      if((event.which==111 || event.which==193) && elemento.value.substr(elemento.value.length-1, 1)=='/'){
    //        elemento.value=elemento.value.substr(0, elemento.value.length-1);
      // }
//        if((elemento.value.length==2 || elemento.value.length==5) &&(event.which==111 || event.which==193)){
  //          elemento.value=elemento.value+'/';
    //    }

        switch(elemento.value.length) {
    		case 1:
   // 		    if (event.which != 8 && event.which != 111) {
                        if(elemento.value>3) {
                        elemento.value=''
                    }
     //           }
            break;
    		case 2:
    //		    if (event.which != 8 && event.which != 111) {
                    if(elemento.value>31 || elemento.value<1 ) {
                        elemento.value=elemento.value.substr(0, 1)
                    }else {
                        elemento.value=elemento.value.substr(0, 2)+'/';
                    }
    //            }
            break;

    		case 3:
  //  		    if (event.which != 8 && event.which != 111) {
                    if(elemento.value.substr(0, 2)<32 && elemento.value.substr(0, 2)>0) {
                        if(elemento.value.substr(2, 1)!='/') {
                            elemento.value=elemento.value.substr(0, 2);
                        }else {
                            elemento.value=elemento.value.substr(0, 2)+'/';
                        }
                    }else {
                        elemento.value='';
                    }
   //             }
            break;
    		case 4:
   // 		    if (event.which != 8 && event.which != 111) {
                    if(elemento.value.substr(3, 1)>1) {
                        elemento.value=elemento.value.substr(0, 3)
                    }else {
                        elemento.value=elemento.value.substr(0, 4)
                    }
   //             }
            break;
    		case 5:
    //		    if (event.which != 8 && event.which != 111) {
                    if(elemento.value.substr(3, 2)>12 || elemento.value.substr(3, 2)<1) {
                        elemento.value=elemento.value.substr(0, 4);
                    }else {
                        elemento.value=elemento.value.substr(0, 5)+'/';
                    }
  //              }

            break;
    		case 6:
  //  		    if (event.which != 8 && event.which != 111) {
                    if(elemento.value.substr(3, 2)<13 && elemento.value>00) {
                        if(elemento.value.substr(5, 1)!='/') {
                            elemento.value=elemento.value.substr(0, 5);
                        }else {
                            elemento.value=elemento.value.substr(0, 5)+'/';
                        }
                    }else {
                        elemento.value=elemento.value.substr(0, 6);
                    }
  //              }
            break;
            case 7:
 //   		    if (event.which != 8 && event.which != 111) {
                    if(elemento.value.substr(6, 1)==0) {
                        elemento.value=elemento.value.substr(0, elemento.value.length-1) ;
                    }
  //              }
            break;
            case 10:
  //  		    if (event.which != 8 && event.which != 111) {
                    if(elemento.value.substr(6, 4)<1900) {
                        elemento.value=elemento.value.substr(0, elemento.value.length-4) ;
                    }
  //              }
            break;
        }
	}
}
////***** aqui verifica a data total se esta correta******////
if(elemento.value.length>=10) {
	dia = elemento.value.substring(0, 2)
	mes = elemento.value.substring(3, 5) - 1
	ano = elemento.value.substring(6, 10)
	data_dig = new Date(ano, mes, dia)

	if (data_dig.getUTCDate() != dia) {
		window.alert('DIA NÃO COERENTE COM O MÊS E ANO !')
		elemento.focus()
		elemento.select()
		return false
	}else if (data_dig.getMonth() != mes) {
		window.alert('MÊS INVÁLIDO !')
		elemento.focus()
		elemento.select()
		return false
	}else if (data_dig.getUTCFullYear() != ano) {
	    window.alert('ANO INVÁLIDO !')
	    elemento.focus()
	    elemento.select()
	    return false
	}
}
////////////////////////
        break;

        case "cep":
           if (navigator.appName == 'Microsoft Internet Explorer') {
                if (event.keyCode==8 || event.keyCode==46) {
                    return false;
                }
            } else {
                if (event.which==8 || event.which==46) {
                    return false;
                }
            }
			checar='1234567890-';
			var x, tamanho1 = elemento.value.length, auxiliar=''
			for (x = 0; x <= tamanho1; x ++) { // tratamento do tracinho
				elemento.value  = elemento.value.replace('-', '')
			}
			tamanho1=elemento.value.length
			for (x = 0; x < tamanho1; x ++) {
				if (checar.indexOf(elemento.value.charAt(x)) != -1) {
					auxiliar=auxiliar+elemento.value.charAt(x) //caracteres q foram aceitos
				}
			}
			if(auxiliar.length==5) {
				auxiliar=auxiliar.substr(0, 5)+"-"
			} else if(auxiliar.length>5) {
				auxiliar=auxiliar.substr(0, 5)+"-"+auxiliar.substr(5, 3)
			}
			elemento.value=auxiliar
		break;

        case "aceita": //aceita o que foi passado como parametro
               switch(checar) {
                case "numeros":
                    checar="0123456789";
					break;
                case "numeros_inteiros":
					 var retirar_zero_esquerda=1
                    checar="0123456789";
					break;
                case "letras":
                    checar="abcdeABCD";
                break;
                case "caracteres":
                    checar="0123456789abcABC";
                break;
                case "emails":
                    checar="0123456789abc";
                break;
                case "telefones":
                    checar="0123456789abc";
                break;
                case "ceps":
                    checar="0123456789abc";
                break;

                case "nomes":
                    checar="0123456789abc";
                break;

                case "sites":
                    checar="0123456789abc";
                break;

                case "bla":
                    checar="0123456789abc";
                break;
                }
				if(retirar_zero_esquerda==1) {
					elemento.value = parseInt(elemento.value,10);
				}
               caracter1='.'; caracter2=',';
                var x, y, tamanho1 = elemento.value.length, tamanho2, auxiliar1, auxiliar2
                var valor=0
                if(operador==1) {
                    var checar2 = '-+'
                }else {
                    var checar2 = ''
                }

              	for (x = 0; x < tamanho1; x ++)
            		if ((elemento.value.charAt(x) != '00') && (elemento.value.charAt(x) != caracter2))
            			break
            			auxiliar1 = ''
            	for (; x < tamanho1; x ++)
            		if (checar.indexOf(elemento.value.charAt(x)) != -1)
            			auxiliar1 += elemento.value.charAt(x)
            			auxiliar1 += ''
            			tamanho1 = auxiliar1.length
            		if (tamanho1 == 0)
            			elemento.value = ''
            		if (tamanho1 == 1)
            			elemento.value=auxiliar1
            		if (tamanho1 == 2)
            			elemento.value=auxiliar1
            		if (tamanho1 > 2) {
            			auxiliar2 = ''
            		for (y = 0, x = tamanho1 - 3; x >= 0; x --) {
            				auxiliar2 += auxiliar1.charAt(x)
            				y ++
            		}
            			elemento.value = ''
            			tamanho2 = auxiliar2.length
            		for (x = tamanho2 - 1; x >= 0; x --)
            			elemento.value += auxiliar2.charAt(x)
            			elemento.value += auxiliar1.substr(tamanho1 - 2, tamanho1)
            		}
        break;
//////////////////////////////////////////////
		case "hora": //hora
			checar='1234567890';
			caracter1=':'; caracter2=':';
			var x, y, tamanho1 = elemento.value.length, tamanho2, auxiliar1, auxiliar2
			var valor=0
			if(operador==1) {
				var checar2 = '-+'
			}else {
				var checar2 = ''
			}
                ////////////////////////////////////////////////////
			for (x = 0; x < tamanho1; x ++)
				if ((elemento.value.charAt(x) == checar2.charAt(0)) && (elemento.value.charAt(x) != caracter2))
					valor=1
				else if ((elemento.value.charAt(x) == checar2.charAt(1)) && (elemento.value.charAt(x) != caracter2))
					valor=0
					elemento.value  = elemento.value.replace('-', '')
					elemento.value  = elemento.value.replace('+', '')                                 						
					if (elemento.value != '0' && elemento.value.length>0 && elemento.value != '-' && elemento.value != '+') {
						for (x = 0; x < tamanho1; x ++)
							if ((elemento.value.charAt(x) != '0') && (elemento.value.charAt(x) != caracter2))
								break
								auxiliar1 = ''
								for (; x < tamanho1; x ++)
									if (checar.indexOf(elemento.value.charAt(x)) != -1) 
										auxiliar1 += elemento.value.charAt(x)
										auxiliar1 += ''
										tamanho1 = auxiliar1.length
										if (tamanho1 == 0)
											elemento.value = ''
											if (tamanho1 == 1) 
												elemento.value = '0' + caracter2 + '0' + auxiliar1
											if (tamanho1 == 2) 
												elemento.value = '0' + caracter2 + auxiliar1
											if (tamanho1 > 2) {
												auxiliar2 = ''
												for (y = 0, x = tamanho1 - 3; x >= 0; x --) {
												//	if (y == 3) {
												//	auxiliar2 += caracter1
												//    y = 0
												//}
												auxiliar2 += auxiliar1.charAt(x)
												y ++
											}
											elemento.value = ''
											tamanho2 = auxiliar2.length
											for (x = tamanho2 - 1; x >= 0; x --)
												elemento.value += auxiliar2.charAt(x)
												elemento.value += caracter2 + auxiliar1.substr(tamanho1 - 2, tamanho1)
					}                        
				}else if(elemento.value == '0'){
            		elemento.value = '0:00'
            	}
            	if(valor==1){
            		elemento.value ="-"+elemento.value
            	}
        break;
    }
}

function strtofloat(valor) {
    var qtde_caracteres = valor.length
    for(var i = 0; i < qtde_caracteres; i++) valor = valor.replace('.', '')
    return valor.replace(',', '.')
}

function floattostr(valor) {
	var qtde = valor.length
	var achei_virgula = 0
	var casas = 0
	var novo_valor = ''
	valor = valor.replace('.', ',')
	for (x = qtde; x >= 0; x--) {
		if(valor.substr(x, 1) == ',') {
			novo_valor = ',' + novo_valor
			achei_virgula = 1
		}else {
   			if(achei_virgula == 1) {

				if(casas == 3) {
					novo_valor = valor.substr(x, 1) + '.' +novo_valor
					casas = 0
				} else {
					novo_valor = valor.substr(x, 1) + novo_valor
				}
				casas++
			}else {
				novo_valor = valor.substr(x, 1) + novo_valor
			}
		}
	}
	return novo_valor
}

function limpeza_moeda(formulario, campos) {
var x, y, elemento, objeto1, objeto2, indice = 0, auxiliar = 0, posicao = 0, matriz = new Array()
for (x = 0; x < campos.length; x ++) {
    if (campos.substr(x, 1) == ',') {
			if (auxiliar == 1) {
					matriz[indice] = campos.substr(posicao, auxiliar)
					auxiliar = 0
					indice   ++
			}else if (auxiliar > 1) {
					matriz[indice] = campos.substr(posicao - auxiliar + 1, auxiliar)
					auxiliar = 0
					indice   ++
			}
		}else {
			auxiliar ++
		}
		posicao = x
	}
	for (x = 0; x < indice; x ++) {
		elemento = eval('document.'+formulario+'.'+matriz[x]+'')
		objeto1  = eval('document.'+formulario+'.'+matriz[x]+'.value')
		objeto2  =  objeto1
			for (y = 0; y < objeto1.length; y ++) {
				objeto1  = objeto2.replace('.', '')
				objeto2  = objeto1
			}
			objeto1  = objeto2.replace(',', '.')
			objeto2  = objeto1
	elemento.value = objeto1
	}
}

/*
function digito_numero() {
	if (navigator.appName == 'Microsoft Internet Explorer') {	
		if (!(event.keyCode > 47 && event.keyCode < 58)) {
			event.keyCode = 0
		}		
	}
}
*/
function atualizar(formulario, campo, valor)  {
	var elemento = eval('window.opener.parent.document.'+formulario+'.'+campo+'')
	elemento.value = elemento.value + valor
}

function janela_efeito(endereco, nome, topo, esquerda, altura, largura, velocidade_altura, velocidade_largura) {
var janela, propriedades, tamanho_altura, tamanho_largura
	propriedades = ('top = '+topo+', left = '+esquerda+', height = 10, width = 10, resizable = no, scrollbars = yes, toolbar = no, location = no, directories = no, status = no, menubar = no, fullscreen = no')
	janela     = window.open(endereco, nome, propriedades)	
			
	for (tamanho_altura = 1; tamanho_altura < altura; tamanho_altura += velocidade_altura) {
		janela.resizeTo('1', tamanho_altura)
	}			
	for (tamanho_largura = 0; tamanho_largura < largura; tamanho_largura += velocidade_largura) {
		janela.resizeTo(tamanho_largura, tamanho_altura)
	}
}

function janela(endereco, nome, topo, esquerda, altura, largura) {
var propriedades
	propriedades = ('top = '+topo+', left = '+esquerda+', height = '+altura+', width = '+largura+', resizable = no, scrollbars = yes, toolbar = no, location = no, directories = no, status = no, menubar = no, fullscreen = no')
	window.open(endereco, nome, propriedades)
}

function janela_total(endereco, nome) {
var propriedades
	propriedades = ('top = 0, left = 0, height = '+(screen.height - 55)+', width = '+(screen.width - 10)+', resizable = no, scrollbars = yes, toolbar = no, location = no, directories = no, status = no, menubar = no, fullscreen = no')
	window.open(endereco, nome, propriedades)
}
function fechar(instancia) {
	valor = confirm("DESEJA REALMENTE FECHAR ESTA JANELA ?")
	if(valor == true) {
		instancia.close()
	}
}

function redefinir(instancia, mensagem) {
    valor = confirm('DESEJA '+mensagem+' ?')
    if(valor == true) eval(instancia).reset()
}

function number_format(numero) {
    var verificar_virgula = ','
    var achou_virgula = 0, passou_pela_virgula = 0
    var separador_milhar = 0
    var novo_numero = ''

    for(i = 0; i < numero.length; i ++) {
//Aqui eu verifico se o número que foi passado por parâmetro contêm vírgula
//Eu faço isso porque daí fica mais fácil para fazer os tratamentos no número nos loops mais abaixo
        if(verificar_virgula.indexOf(numero.charAt(i)) != -1) achou_virgula = 1
    }
//Significa que o número passado por parâmetro contêm vírgula
    if(achou_virgula == 1) {
//Aqui eu tenho que ler o número de forma inversa, do fim para o início
        for(i = (numero.length - 1); i >= 0; i--) {
            if(numero.charAt(i) == ',') {//Verifica se é vírgula
                novo_numero = numero.charAt(i) + novo_numero
                passou_pela_virgula = 1//Encontrou a vírgula do número
            }else {
                if(passou_pela_virgula == 0) {//Aqui ele está varrendo as casas dec. do núm
                    novo_numero = numero.charAt(i) + novo_numero
                }else {//Aqui ele já está varrendo a parte de milhar do número
                    if(separador_milhar == 2) {
/*Aqui eu verifico se já estou no último dígito do número para não colocar . na frente do número
/Ex: .397.333*/
                        if(i == 0) {//Significa que já está no último dígito, então não precisa por .
                            novo_numero = numero.charAt(i) + novo_numero
                        }else {//Ainda, tem mais dígitos a ser lidos
                            novo_numero = '.' + numero.charAt(i) + novo_numero
                        }
                        separador_milhar = 0
                    }else {
                        novo_numero = numero.charAt(i) + novo_numero
                        separador_milhar++
                    }
                }
            }
        }
//Significa que o número passado por parâmetro Não contêm vírgula
    }else {
//Aqui eu tenho que ler o número de forma inversa, do fim para o início
        for(i = (numero.length - 1); i >= 0; i--) {
            if(separador_milhar == 2) {
/*Aqui eu verifico se já estou no último dígito do número para não colocar . na frente do número
/Ex: .397.333*/
                if(i == 0) {//Significa que já está no último dígito, então não precisa por .
                    novo_numero = numero.charAt(i) + novo_numero
                }else {//Ainda, tem mais dígitos a ser lidos
                    novo_numero = '.' + numero.charAt(i) + novo_numero
                }
                separador_milhar = 0
            }else {
                novo_numero = numero.charAt(i) + novo_numero
                separador_milhar++
            }
        }
    }
    return novo_numero
}

/*
window.captureEvents(Event.CHANGE|Event.KEYUP) //esta parte significa quais eventos q eu quero q aciona a função
function capturar_evento(e){ //esta função serve para identificar se houve alteração na pagina se houver ele habilita a submit
	if(e.type){
		for(r=0;r<document.forms.length;r++) {
			var w=0; //tem q ter esta variavel
			var elementos=eval('document.'+document.forms[r].name+'.elements');
			while(elementos[w++].type!='submit');
			elementos[--w].disabled=false;
		}
	}
}
window.onchange=capturar_evento;
window.onkeyup=capturar_evento;
*/
///////////////////////////////////////////////////////
/*
document.oncontextmenu =
function () {
	return false
}
if (document.layers) {
	window.captureEvents(event.mousedown)
	window.onmousedown =
function (e){
	if (e.target == document)
		return false
	}}else {
    	document.onmousedown =
function (){
	return false
	}
}*/