<?
function migrar_fornecedores($arquivo, $tabela, $campos, $campos2, $separacao) {
    $conectar = mysql_connect('localhost','root','albafer');
    mysql_select_db('erp_albafer');
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($x = 0; $x < count($linhas); $x ++) {
            $conteudo  = trim($linhas[$x]);
            $conteudo  = str_replace($separacao, "', '", $conteudo);
            if(!substr(strchr($conteudo,'@'),0,1)) {
                $vetor = explode(',', $conteudo);
                $vetor = str_replace('-','',list(,,,,,,$cep) = $vetor);
                $conteudo = implode(',' , $vetor);
                $sql = "insert into $tabela ($campos2) values ('$conteudo')";
            }else {
                $vetor = explode(',', $conteudo);
                $vetor = str_replace('-','',list(,,,,,,$cep) = $vetor);
                $conteudo = implode(',' , $vetor);
                $sql = "insert into $tabela ($campos) values ('$conteudo')";
            }
            if(!mysql_query($sql)) {
                echo $conteudo . "<br>";
            }
        }
        flush();
        $tamanho = filesize($arquivo);
        if ($tamanho >= '1073741824') {
            $tamanho = round($tamanho / 1073741824 * 100) / 100 . ' GB';
        }elseif ($tamanho >= '1048576') {
            $tamanho = round($tamanho / 1048576 * 100) / 100 . ' MB';
        }elseif ($tamanho >= '1024') {
            $tamanho = round($tamanho / 1024 * 100) / 100 . ' KB';
        }else {
                $tamanho = $tamanho . ' B';
        }
        echo '<font class="atencao">ARQUIVO MIGRADO COM SUCESSO '.basename($arquivo).' TAMANHO '.$tamanho.' TOTAL DE REGISTRO '.$x.'</font><br>';
    }else {
        echo '<font class="atencao">ERROR AO TENTAR ABRIR O ARQUIVO '.basename($arquivo).'</font>';
    }
    $sql = "Update fornecedores set id_empresa='1', ativo='1'";
    $executa = mysql_query($sql);
    //return migrar_produtos('produto01.txt', 'produtos_insumos', 'referencia, discriminacao', '|');
}

/************************************************************************************************/

/*Migração da Tabela Cliente*/
    return migrar_clientes('clientes.txt', 'clientes', 'razaosocial, endereco, cep, cidade, id_uf, id_pais, cnpj_cpf, insc_estadual, credito, telfax, email', '|');

function migrar_clientes($arquivo, $tabela, $campos, $separacao) {
    $conectar = mysql_connect('localhost','','');
    mysql_select_db('erp_albafer');
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($x = 0; $x < count($linhas); $x ++) {
            $conteudo  = trim($linhas[$x]);
            $conteudo  = str_replace($separacao, "', '", $conteudo);
            //if(!substr(strchr($conteudo,'@'),0,1)) {
            //$vetor = explode(',', $conteudo);
            //$vetor = str_replace('-','',list(,,,,,,$cep) = $vetor);
            //$conteudo = implode(',' , $vetor);
            $sql = "insert into $tabela ($campos) values ('$conteudo')";
            //}else {
                //$vetor = explode(',', $conteudo);
                //$vetor = str_replace('-','',list(,,,,,,$cep) = $vetor);
                //$conteudo = implode(',' , $vetor);
                //$sql = "insert into $tabela ($campos) values ('$conteudo')";
            //}
            if(!mysql_query($sql)) {
                echo $conteudo . "<br>";
            }
        }
        flush();
        $tamanho = filesize($arquivo);
        if ($tamanho >= '1073741824') {
            $tamanho = round($tamanho / 1073741824 * 100) / 100 . ' GB';
        }elseif ($tamanho >= '1048576') {
            $tamanho = round($tamanho / 1048576 * 100) / 100 . ' MB';
        }elseif ($tamanho >= '1024') {
            $tamanho = round($tamanho / 1024 * 100) / 100 . ' KB';
        }else {
            $tamanho = $tamanho . ' B';
        }
        echo '<font class="atencao">ARQUIVO MIGRADO COM SUCESSO '.basename($arquivo).' TAMANHO '.$tamanho.' TOTAL DE REGISTRO '.$x.'</font><br>';
    }else {
        echo '<font class="atencao">ERROR AO TENTAR ABRIR O ARQUIVO '.basename($arquivo).'</font>';
    }
    //return migrar_produtos('produto01.txt', 'produtos_insumos', 'referencia, discriminacao', '|');
}

//return migrar_produtos('garra.txt', 'produtos_insumos', 'referencia, discriminacao', '|');

/*Migração da Tabela Produto*/
function migrar_produtos($arquivo, $tabela, $campos, $separacao) {
    $conectar = mysql_connect('localhost','root','albafer');
    mysql_select_db('erp_albafer');
    if (file_exists($arquivo) && is_readable($arquivo)) {
        $linhas = file($arquivo);
        for ($x = 0; $x < count($linhas); $x ++) {
            $conteudo  = trim($linhas[$x]);
            $conteudo  = str_replace($separacao, "', '", $conteudo);
            $sql = "insert into $tabela ($campos) values ('$conteudo')";
            if(!mysql_query($sql)) {
                echo $conteudo . "<br>";
            }
        }
        flush();
        $tamanho = filesize($arquivo);
        if ($tamanho >= '1073741824') {
            $tamanho = round($tamanho / 1073741824 * 100) / 100 . ' GB';
        }elseif ($tamanho >= '1048576') {
            $tamanho = round($tamanho / 1048576 * 100) / 100 . ' MB';
        }elseif ($tamanho >= '1024') {
            $tamanho = round($tamanho / 1024 * 100) / 100 . ' KB';
        }else {
            $tamanho = $tamanho . ' B';
        }
        echo '<font class="atencao">ARQUIVO MIGRADO COM SUCESSO '.basename($arquivo).' TAMANHO '.$tamanho.' TOTAL DE REGISTRO '.$x.'</font><br>';
    }else {
        echo '<font class="atencao">ERROR AO TENTAR ABRIR O ARQUIVO '.basename($arquivo).'</font>';
    }

    $data = date("Y-m-d H:i:s");
    //$sql = "Update produtos_insumos set data_sys='$data', id_grupo='1'";
    $executa = mysql_query($sql);
    //return produtos_X_fornecedores($id_fornecedor, $total);
}
/*************************************************************************************************/

/*Migração Produtos_X_Fornecedores*/
function produtos_X_fornecedores($id_fornecedor, $total) {
    $data = date("Y-m-d H:m:i");

    if(empty($id_fornecedor)) $id_fornecedor = 1;

    $conectar = mysql_connect("localhost","root","albafer");
    mysql_select_db("erp_albafer");

    $sql = "Select id_fornecedor from fornecedores";
    $num_fornec = mysql_num_rows(mysql_query($sql));

    for($x = 0; $x < 10; $x++) {
        $sql = "select * from produtos_insumos";
        $executa2 = mysql_query($sql);
        $linhas = mysql_num_rows($executa2);
        $id_produto = 1;
        for($y = 0; $y < $linhas; $y++) {
            $sql = "insert into fornecedores_X_prod_insumos(id_fornecedor,id_produto_insumo, data_sys) values('$id_fornecedor','$id_produto', '$data')";
            $executa3 = mysql_query($sql);
            $id_produto++;
            $total++;
        }
        $id_fornecedor++;
        //mysql_close($conectar);
        if($id_fornecedor > $num_fornec) $x = 10;
    }

    if($x == 10 and ($id_fornecedor <= $num_fornec)) {
?>
        <script language="javascript">
            window.location='migrar_tabelas_geral.php?passo=1&id_fornecedor='+"<?echo $id_fornecedor;?>"+'&total='+"<?echo $total;?>"
        </script>
<?
    }else {
        echo '<font class="atencao">ARQUIVO MIGRADO COM SUCESSO TOTAL DE REGISTRO '.$total.'</font><br>';
    }
}
?>
