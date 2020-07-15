<?
require('../../lib/segurancas.php');

$sql = "SELECT id_produto_acabado, referencia 
        FROM `produtos_acabados` 
        WHERE `id_produto_acabado` IN (15416, 24553, 24555, 24559, 24560, 24561, 24565, 24566, 24567, 3125, 3127, 
            3128, 3129, 3153, 3154, 3132, 3152, 3130, 3131, 3155, 3126, 3133, 3135, 3136, 3137, 3138, 3139, 3140, 
            3134, 3141, 3142, 3143, 3144, 3145, 3146, 3147, 3148, 3149, 3150, 3151, 3172, 3173, 3174, 3041, 3042, 
            3043, 3044, 3045, 3046, 3081, 3082, 3083, 3084, 3085, 3086, 3087, 3053, 3054, 3055, 3056, 3057, 3058, 
            3059, 3067, 3068, 3069, 3070, 3071, 3072, 3073, 3156, 3157, 3158, 3159, 3160, 3161, 3162, 3095, 3096, 
            3097, 3098, 3099, 3100, 3101, 3109, 3110, 3111, 3112, 3113, 3114, 3115, 3116, 24515, 3175, 3176, 3177, 
            3170, 3171, 18933, 3060, 3061, 3062, 3063, 3064, 3065, 3066, 3088, 3089, 3090, 3091, 3092, 3093, 3094, 
            3178, 3179, 3180, 3074, 3075, 3076, 3077, 3078, 3079, 3080, 3163, 3164, 3165, 3166, 3167, 3168, 3169, 
            3102, 3103, 3104, 3105, 3106, 3107, 3108, 3117, 3118, 3119, 3120, 3121, 3122, 3123, 3124, 8508, 3047, 
            3048, 3049, 3050, 3051, 3052) ";
$campos = bancos::sql($sql);
$linhas = count($campos);
for($i = 0; $i < $linhas; $i++) {
    //Verifico se o PA do Loop possui Custo Industrial ...
    $sql = "SELECT id_produto_acabado_custo 
            FROM `produtos_acabados_custos` 
            WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' 
            AND `operacao_custo` = '0' LIMIT 1 ";
    $campos_custo = bancos::sql($sql);
    if(count($campos_custo) == 0) {//Não possui Custo Industrial ...
        $sql = "INSERT INTO `produtos_acabados_custos` (`id_produto_acabado_custo`, `id_produto_acabado`, `qtde_lote`, `comprimento_2`, `operacao_custo`, `data_sys`) VALUES (NULL, '".$campos[$i]['id_produto_acabado']."', '1', '6.0', '0', '".date('Y-m-d H:i:s')."') ";
        bancos::sql($sql);
        $id_produto_acabado_custo = bancos::id_registro();
    }else {//Já possui Custo Industrial ...
        $id_produto_acabado_custo = $campos_custo[0]['id_produto_acabado_custo'];
        //Atualizo a Qtde do Lote do Custo p/ 1 porque sempre um PA da 7ª Etapa tem 1 item com Qtde = 1 ...
        $sql = "UPDATE `produtos_acabados_custos` SET `qtde_lote` = '1' WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
        bancos::sql($sql);
    }
    $referencia_sem_nl = str_replace('NL', '', $campos[$i]['referencia']);
    
    $sql = "SELECT id_produto_acabado 
            FROM `produtos_acabados` 
            WHERE `referencia` = '$referencia_sem_nl' LIMIT 1 ";
    $campos_pa_sem_nl = bancos::sql($sql);
    
    //Verifico se existe algum item na 7ª Etapa desse Custo ...
    $sql = "SELECT id_pac_pa 
            FROM `pacs_vs_pas` 
            WHERE `id_produto_acabado_custo` = '$id_produto_acabado_custo' LIMIT 1 ";
    $campos_etapa7 = bancos::sql($sql);
    if(count($campos_etapa7) == 0) {//Não existe nenhum item na 7ª Etapa desse Custo, sendo assim vou incluir o 1º ...
        $sql = "INSERT INTO `pacs_vs_pas` (`id_pac_pa`, `id_produto_acabado_custo`, `id_produto_acabado`, `qtde`) VALUES (NULL, '$id_produto_acabado_custo', '".$campos_pa_sem_nl[0]['id_produto_acabado']."', '1') ";
        bancos::sql($sql);
    }

    //Libera o Custo do PA ...
    $sql = "UPDATE `produtos_acabados` SET `status_custo` = '1' WHERE `id_produto_acabado` = '".$campos[$i]['id_produto_acabado']."' LIMIT 1 ";
    bancos::sql($sql);
    
    echo 'PA -> '.$campos[$i]['id_produto_acabado'].'<br/>';
}
echo 'FIM';
?>