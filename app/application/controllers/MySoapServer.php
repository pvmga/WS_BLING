<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class MySoapServer extends MY_Controller {

    function __construct() {
        parent::__construct();
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Credentials: false');
    }

    public function index() {
        
    }

    public function getClientes($cpfCliente) {
        $this->load->model('mysoap_models');

        $cnpjCpf = $cpfCliente;
        $clientes = $this->mysoap_models->getClientesModels($cnpjCpf);
        return json_encode($clientes);
    }

    public function getProdutos() {
        $this->load->model('mysoap_models');
        $codigoProduto = isset($_GET['codigoProduto']) ? $_GET['codigoProduto'] : 0;

        $produtos = $this->mysoap_models->getProdutosModels($codigoProduto);
        echo json_encode($produtos);
    }

    public function getDadosVendas() {
        $this->load->model('mysoap_models');

        $num_venda = $_GET['num_venda'];

        $vendas = $this->mysoap_models->getDadosVendasModels($num_venda);
        echo json_encode($vendas);
    }

    public function getParametros() {
        $this->load->model('mysoap_models');

        $parametros = $this->mysoap_models->getParametrosModels();
        echo json_encode($parametros);
    }

    public function inserirVenda() {
        $this->load->model('mysoap_models');
        $dadosVenda = json_decode(base64_decode($this->input->post('dadosVenda')), true);

        $venda = $this->mysoap_models->verificaExistenciaVenda($dadosVenda['numero']);
        
        //$dadosVenda = $dadosVenda;
        $codigoCliente = $this->input->post('codigoCliente');

        //$cliente = $this->mysoap_models->getClientesModels();

        $parametros = $this->mysoap_models->getParametrosModels();

        $numero_venda = $this->mysoap_models->geraCodigoVenda();
        $codigo_venda = $numero_venda['GEN_VALUE'];

        $valor_total_itens = 0;
        $valor_total_ipi = 0;
        $valor_st = 0;
        $x = 0;

        foreach($dadosVenda['itens'] as $dados) {
            $x += 1;
            $valor_unitario = $dados['item']['valorunidade'];
            $valor_total = $dados['item']['valorunidade'];

            $dadosItens = array(
                'COD_VENDA' => $codigo_venda,
                'COD_PROD' => $dados['item']['codigo'],
                'SEQUENCIA' => $x,
                'UNIDADE' => $dados['item']['un'],
                'QUANTIDADE' => $dados['item']['quantidade'],
                'QTDE_RESERVADA' => $dados['item']['quantidade'],
                'DESCONTO' => $dados['item']['descontoItem'],
                'FABRICAR' => 'N',
                'LARGURA' => 0,
                'GARANTIA' => 'N',
                'RETIRAR' => 'N',
                'VP' => 'N',
                'VALOR_UNIT' => $valor_unitario,
                'VALOR_CUSTO' => $valor_unitario,
            );

            $dadosItens2[] = $dadosItens;
            $valor_total_itens += round($valor_total, 2);
        }
        
        $dados_itens[] = $this->mysoap_models->inserirVendaItens($dadosItens2);

        $dados = array(
            'COD_VENDA' => $codigo_venda,
            'COD_CLIENTE' => $codigoCliente,
            'COD_PAGAMENTO' => empty($dadosVenda['condicao']) ? $parametros['COD_CONDPGTO_PADRAO'] : $dadosVenda['condicao'],
            'TIPO_PAGTO' => 'BOLETO',
            'FRETE' => $dadosVenda['valorfrete'],
            'DATA_VENDA' => date('Ymd'),
            'PEDIDO_ECM' => $dadosVenda['numero'],
            'COD_TRANSP' => empty($dadosVenda['transportadora']) ? $parametros['TRANSP_PADRAO'] : $dadosVenda['transportadora'],
            'CLASSIFICACAO' => 'I',
            'SITUACAO' => 'V',
            'DATA_HORA_VENDA' => date('Ymd h:i:s'),
            'ENVIADO_CAIXA' => 'N',
            'CONCLUIDA' => 'N',
            'PED_WEB' => 'S',
            'CONTATO' => $dadosVenda['tipoIntegracao'],
            'NOTAFISCAL' => 'N',
            'USUARIO' => 'INTEGRACAO',
            //'COD_VENDEDOR_EXT' => ,
            'PAR_EMPRESA' => $parametros['CODIGO'],
            'TRANSPORTE' => $parametros['FRETE_PADRAO'],
            'COD_VENDEDOR' => $parametros['ONLINE_COD_VEND_INTERNO_PADRAO'],
            'OBS_COMP' => addslashes($dadosVenda['observacoes']),
            'IMPRIMIU' => 'N',
            'VALOR_PAGO' => $valor_total_itens,
            'TOTAL_VENDA' => $valor_total_itens,
            'VALOR_DESC' => $valor_total_itens,
            'VALOR_OUTROS' => round($valor_st - $valor_total_ipi, 2), // EM TESTE
            'VALOR_IPI' => round($valor_total_ipi, 2), // EM TESTE
        );

        $dados_venda = $this->mysoap_models->inserirVendaModels($dados);

        echo json_encode(array(
            'cod_venda' => $codigo_venda,
        ));
    }

    public function inserirCliente() {
        $cnpj = $this->input->post('cnpj');
        $rg = $this->input->post('rg');
        $nome = $this->input->post('nome');
        $endereco = $this->input->post('endereco');
        $numero = $this->input->post('numero');
        $complemento = $this->input->post('complemento');
        $cidade = $this->input->post('cidade');
        $bairro = $this->input->post('bairro');
        $cep = $this->input->post('cep');
        $uf = $this->input->post('uf');
        $email = $this->input->post('email');
        $celular = $this->input->post('celular');
        $fone = $this->input->post('fone');

        if ($rg == '') {
            $rg = 'ND';
        }

        if (strlen($cnpj) > 14) {
            $CGC = $cnpj;
            $CPF = NULL;
            $RG = NULL;
            $INSCRICAO = $rg;
            $natureza = 'J';
            $consumidor = 'N';
            $contribuinte = 'S';
        } else {
            $CGC = '00.000.000/0000-00';
            $CPF = $cnpj;
            $RG = $rg;
            $INSCRICAO = NULL;
            $natureza = 'F';
            $consumidor = 'S';
            $contribuinte = 'N';
        }

        $res = json_decode($this->getClientes($CPF), true);

        $this->load->model('mysoap_models');

        // Verifica a existência do codigo interno, caso exista será considerado alteração de registro.
        if ($res[0]['CODIGO'] == false) {
            $CODIGO = $this->mysoap_models->gerarCodigoCliente();
        } else {
            $CODIGO = $res[0]['CODIGO'];
        }

        $parametros = $this->mysoap_models->getParametrosModels();

        $dados = array(
            'CODIGO' => $CODIGO,
            'NATUREZA' => $natureza,
            'CGC' => $CGC,
            'INSCRICAO' => $INSCRICAO,
            'CPF' => $CPF,
            'RG' => $RG,
            'RAZAO_SOCIAL' => ($this->uppercasebr(addslashes($nome))),
            'NOME_FANTASIA' => ($this->uppercasebr($nome)),
            'CEP' => $cep,
            'ENDERECO' => ($this->uppercasebr($this->formataTextoAspas(addslashes($endereco)))),
            'NUM_END_PRINCIPAL' => $numero,
            'COMP_ENDERECO' => ($this->uppercasebr(addslashes($complemento))),
            'BAIRRO' => ($this->uppercasebr(addslashes($bairro))),
            'CIDADE' => ($this->uppercasebr(addslashes($cidade))),
            'ESTADO' => ($this->uppercasebr(addslashes($uf))),
            'TELEFONE' => $fone,
            'CELULAR' => $celular,
            'TRANSPORTADORA' => $parametros['TRANSP_PADRAO'],
            'EMAIL' => '',
            'OBS_CADASTRO' => '',
            'TIPO_CLIENTE' => 'A',
            'TIPO' => 'A',
            'DATA_CADASTRO' => date('Ymd'),
            'USUARIO_CADASTRO' => 'WS_BLING',
            'ECOMMERCE' => 'S',
            'CODIGO_VENDEDOR' => $parametros['ONLINE_COD_VEND_INTERNO_PADRAO'],
            'COD_PAIS' => 1058,
            'ACEITAR_DESCONTO' => 'S',
            'CONSUMIDOR_FINAL' => $consumidor,
            'CONTRIBUINTE_ICMS' => $contribuinte,
        );

        if ($res[0]['CODIGO'] == false) {
            $this->mysoap_models->inserirCliente($dados);
            $tipo_insert = 'insert';
        } else {
            $this->mysoap_models->updateCliente($dados);
            $tipo_insert = 'update';
        }
        echo json_encode(array('CODIGO' => $CODIGO, 'TIPO' => $tipo_insert));
    }

    public function uppercasebr($str) {

        return strtoupper(strtr($str, "áéíóúâêôãõàèìòùç", "ÁÉÍÓÚÂÊÔÃÕÀÈÌÒÙÇ"));
    }

    public function formataTextoAspas($string) {
        $search = array("'", "‘", "’");
        $replace = array(" ", " ", " ");
        $string = str_replace($search, $replace, $string);
        return $string;
    }

}
