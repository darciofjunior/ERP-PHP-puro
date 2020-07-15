<?
class carta_correcao extends bancos {
    function dados_nfs($id_carta_correcao) {
//Busca do N.º da NF em uma das Três Situações ...
        $sql = "SELECT id_nfe, id_nf, id_nf_outra 
                FROM `cartas_correcoes` 
                WHERE `id_carta_correcao` = '$id_carta_correcao' LIMIT 1 ";
        $campos = bancos::sql($sql);
        if($campos[0]['id_nfe'] > 0) {//Foi feita uma Carta p/ uma NF de Entrada - Compras ...
            $sql = "SELECT nfe.id_empresa, nfe.num_nota, DATE_FORMAT(SUBSTRING(nfe.data_emissao, 1, 10), '%d/%m/%Y') AS data_emissao, f.id_fornecedor, f.razaosocial AS negociador 
                    FROM `nfe` 
                    INNER JOIN `fornecedores` f ON f.`id_fornecedor` = nfe.`id_fornecedor` 
                    WHERE nfe.`id_nfe` = '".$campos[0]['id_nfe']."' LIMIT 1 ";
            $campos_nota 	= bancos::sql($sql);
            $id_nota		= $campos[0]['id_nfe'];
            $tipo_nota		= 'NFE';
            $id_empresa_nf	= $campos_nota[0]['id_empresa'];
            $numero_nf 		= $campos_nota[0]['num_nota'];
            $data_emissao	= $campos_nota[0]['data_emissao'];
            $id_negociador	= $campos_nota[0]['id_fornecedor'];
            $negociador 	= $campos_nota[0]['negociador'];
        }else if($campos[0]['id_nf'] > 0) {//Foi feita uma Carta p/ uma NF de Saída - Vendas ...
            $sql = "SELECT nfs.id_empresa, nfs.id_nf_num_nota, nfs.snf_devolvida, DATE_FORMAT(nfs.data_emissao, '%d/%m/%Y') AS data_emissao, nfs.status, c.id_cliente, c.razaosocial AS negociador 
                    FROM `nfs` 
                    INNER JOIN `clientes` c ON c.`id_cliente` = nfs.`id_cliente` 
                    WHERE nfs.`id_nf` = '".$campos[0]['id_nf']."' LIMIT 1 ";
            $campos_nota 	= bancos::sql($sql);
            $id_nota		= $campos[0]['id_nf'];
            $tipo_nota		= 'NFS';
            $id_empresa_nf	= $campos_nota[0]['id_empresa'];
            $numero_nf 		= faturamentos::buscar_numero_nf($campos[0]['id_nf'], 'S');
            $data_emissao	= $campos_nota[0]['data_emissao'];
            $id_negociador	= $campos_nota[0]['id_cliente'];
            $negociador 	= $campos_nota[0]['negociador'];
        }else if($campos[0]['id_nf_outra'] > 0) {//Foi feita uma Carta p/ uma NF de Saída - Outras ...
            $sql = "SELECT nfso.id_empresa, nfso.id_nf_num_nota, DATE_FORMAT(nfso.data_emissao, '%d/%m/%Y') AS data_emissao, c.id_cliente, c.razaosocial AS negociador 
                    FROM `nfs_outras` nfso 
                    INNER JOIN `clientes` c ON c.id_cliente = nfso.id_cliente 
                    WHERE nfso.`id_nf_outra` = '".$campos[0]['id_nf_outra']."' LIMIT 1 ";
            $campos_nota 	= bancos::sql($sql);
            $id_nota		= $campos[0]['id_nf_outra'];
            $tipo_nota		= 'NFSO';
            $id_empresa_nf	= $campos_nota[0]['id_empresa'];
            $numero_nf 		= faturamentos::buscar_numero_nf($campos[0]['id_nf_outra'], 'O');
            $data_emissao	= $campos_nota[0]['data_emissao'];
            $id_negociador	= $campos_nota[0]['id_cliente'];
            $negociador 	= $campos_nota[0]['negociador'];
        }
        return array('id_nota'=>$id_nota, 'tipo_nota'=>$tipo_nota, 'id_empresa_nf'=>$id_empresa_nf, 'numero_nf'=>$numero_nf, 'data_emissao'=>$data_emissao, 'id_negociador'=>$id_negociador, 'negociador'=>$negociador);
    }
}
?>