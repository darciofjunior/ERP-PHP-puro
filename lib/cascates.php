<?
if(!class_exists('bancos')) require 'bancos.php';//CASO EXISTA EU DESVIO A CLASSE ...
class cascate {
    function consultar($campo_chave, $tabelas, $valor) {
        $tabela = explode(',', $tabelas);
        for($i = 0; $i < count($tabela); $i++) {
            $campos = bancos::sql("SELECT $campo_chave FROM $tabela[$i] WHERE $campo_chave = $valor LIMIT 1 ");
            if(count($campos) > 0) return 1;
        }
        return 0;
    }

    function incluir($tabelas, $id_empresas = '') {
        $vetor_tabelas = explode(',', $tabelas);
        for($i = 0; $i < count($vetor_tabelas); $i++) {
            if($id_empresas == '' or $id_empresas == 0) {
                $sql = "SELECT * FROM $vetor_tabelas[$i] WHERE `ativo` = '1' LIMIT 1 ";
            }else {
                $sql = "SELECT * FROM $vetor_tabelas[$i] WHERE `id_empresa` = '$id_empresas' AND `ativo` = '1' LIMIT 1 ";
            }
            $campos = bancos::sql($sql);
            if(count($campos) == 0) return 1;
        }
        return 0;
    }
}
?>