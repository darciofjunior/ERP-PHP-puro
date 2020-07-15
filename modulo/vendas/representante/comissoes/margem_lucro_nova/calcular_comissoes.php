<?
require('../../../../../lib/segurancas.php');
require('../../../../../lib/vendas.php');

if($_POST['base_comissao_vendedor_sp'] > 0) {
    $vetor_calcular_comissao = vendas::calcular_comissoes($_POST['base_comissao_vendedor_sp']);

    echo '<p/><b>Comiss&atilde;o Vendedor Fora de SP</b> => '.number_format($vetor_calcular_comissao['comis_vend_fora_sp'], 2, ',', '.');
    echo '<br/><b>Comiss&atilde;o Vendedor Interior de SP</b> => '.number_format($vetor_calcular_comissao['comis_vend_interior_sp'], 2, ',', '.');
    echo '<br/><b>Comiss&atilde;o Aut&ocirc;nomo</b> => '.number_format($vetor_calcular_comissao['comis_autonomo'], 2, ',', '.');
    echo '<br/><b>Comiss&atilde;o Vendedor / Supervisor Interno</b> => '.number_format($vetor_calcular_comissao['comis_vend_sup_interno'], 2, ',', '.');
    echo '<br/><b>Comiss&atilde;o Exporta&ccedil;&atilde;o</b> => '.number_format($vetor_calcular_comissao['comis_export'], 2, ',', '.');
    echo '<br/><b>Comiss&atilde;o Supervisor Outras UF</b> => '.number_format($vetor_calcular_comissao['comis_sup_outras_ufs'], 2, ',', '.');
    echo '<br/><b>Comiss&atilde;o Supervisor Aut&ocirc;nomo</b> => '.number_format($vetor_calcular_comissao['comis_sup_autonomo'], 2, ',', '.');
}
?>