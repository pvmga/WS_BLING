<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Mysoap_models extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function getUsuariosModels($usuario, $senha) {
        /* echo json_encode($senha);
          exit(); */
        $sql = "SELECT
					USR.CODIGO,
					USR.NOME,
					USR.APELIDO,
					USR.VENDEDOR,
					USR.SENHA,
					VEND.EMAIL,
					VEND.ESTADO,
					VEND.ENDERECO,
					VEND.BAIRRO,
					VEND.CIDADE,
					VEND.TELEFONE,
					VEND.CELULAR,
					VEND.NOME AS NOME_VENDEDOR
				FROM CAD_USUA AS USR
				INNER JOIN CAD_VEND AS VEND ON (USR.VENDEDOR = VEND.CODIGO)
				WHERE
					VEND.TIPO = ? AND
					USR.ATIVO = ? AND
					USR.APELIDO = ? AND USR.SENHA = ?";

        $query = $this->db->query($sql, array('E', 'S', $usuario, $senha));

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
    }

    public function getClientesModels($cnpjCpf) {
        /*$params = $this->getParametrosModels();
        $where = ($params['ONLINE_VENDER_CLIENTE_BLOQ'] == 'S') ? "" : " AND C.TIPO_CLIENTE NOT IN('P', 'D', 'I')";*/

        $where = " C.CPF = '{$cnpjCpf}'";
        

        $sql = "SELECT 
					C.CODIGO, 
					C.NATUREZA,
					C.CGC,
					C.INSCRICAO,
					C.CPF,
					C.RG,
					C.RAZAO_SOCIAL,
					C.NOME_FANTASIA,
					C.CEP,
					C.ENDERECO,
					C.NUM_END_PRINCIPAL,
					C.COMP_ENDERECO,
					C.BAIRRO,
					C.CIDADE,
					C.ESTADO,
					C.TELEFONE,
					C.CELULAR,
					C.CONTATO,
					C.TRANSPORTADORA,
					C.EMAIL,
					C.OBS_CADASTRO,
					C.CONSUMIDOR_FINAL,
					C.DISP_ST,
					C.CODIGO_VENDEDOR,
					C.VENDEDOR_EXTERNO,
					C.CONTRIBUINTE_ICMS,
					C.OPTANTE_SIMPLES,
					V.EMAIL AS EMAIL_VENDEDOR,
					V.NOME AS NOME_VENDEDOR
				FROM CAD_CLIE C
				LEFT JOIN CAD_VEND V ON(C.VENDEDOR_EXTERNO = V.CODIGO)
				WHERE
					$where";

        $query = $this->db->query($sql, array('S'));

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $clientes) {
                if ($clientes['CONSUMIDOR_FINAL'] == 'S' || $clientes['INSCRICAO'] == 'ISENTO' || $clientes['INSCRICAO'] == '' || $clientes['DISP_ST'] == 'S' || $clientes['CONTRIBUINTE_ICMS'] == 'N') {
                    $calcula_st = 'N';
                } else {
                    $calcula_st = 'S';
                }
                $data[] = [
                    'CODIGO' => $clientes['CODIGO'],
                    'NOME_FANTASIA' => $clientes['NOME_FANTASIA'],
                    'RAZAO_SOCIAL' => $clientes['RAZAO_SOCIAL'],
                    'SINCRONIZADO' => 1,
                    'NATUREZA' => $clientes['NATUREZA'],
                    'CGC' => $clientes['CGC'],
                    'INSCRICAO' => $clientes['INSCRICAO'],
                    'CPF' => $clientes['CPF'],
                    'RG' => $clientes['RG'],
                    'CEP' => $clientes['CEP'],
                    'ENDERECO' => $clientes['ENDERECO'],
                    'NUM_END_PRINCIPAL' => $clientes['NUM_END_PRINCIPAL'],
                    'COMP_ENDERECO' => $clientes['COMP_ENDERECO'],
                    'BAIRRO' => $clientes['BAIRRO'],
                    'CIDADE' => $clientes['CIDADE'],
                    'ESTADO' => $clientes['ESTADO'],
                    'TELEFONE' => $clientes['TELEFONE'],
                    'CELULAR' => $clientes['CELULAR'],
                    'CONTATO' => $clientes['CONTATO'],
                    'TRANSPORTADORA' => $clientes['TRANSPORTADORA'],
                    'EMAIL' => $clientes['EMAIL'],
                    'OBS_CADASTRO' => $clientes['OBS_CADASTRO'],
                    'CONSUMIDOR_FINAL' => $clientes['CONSUMIDOR_FINAL'],
                    'CALCULA_ST' => $calcula_st,
                    'CODIGO_VENDEDOR' => $clientes['CODIGO_VENDEDOR'],
                    'VENDEDOR_EXTERNO' => $clientes['VENDEDOR_EXTERNO'],
                    'CONTRIBUINTE_ICMS' => $clientes['CONTRIBUINTE_ICMS'],
                    'OPTANTE_SIMPLES' => $clientes['OPTANTE_SIMPLES'],
                    'EMAIL_VENDEDOR' => $clientes['EMAIL_VENDEDOR'],
                    'NOME_VENDEDOR' => $clientes['NOME_VENDEDOR'],
                ];
            }

            //return $query->result_array();
            return $data;
        } else {
            return false;
        }
    }

    public function getProdutosModels($codigoProduto) {

        $params = $this->getParametrosModels();
        $database_inc_mov = $params['DATABASE_INC_MOV'];
        if ($params['DATABASE_INC_MOV'] == '') {
            $database_inc_mov = CASHWIN;
        }
        $decimal = ($params['CASAS_DECIMAIS_VENDA'] == 'S') ? 2 : 3;

        $tipo_preco = $params['CASAS_DECIMAIS_VENDA'];
        $coluna_tipo_preco = 'PRECO_CUSTO';
        if ($tipo_preco == 'C') {
            $coluna_tipo_preco = 'CUSTO_BRUTO';
        }

        $where = ($params['ONLINE_ESTOQUE'] == 'N') ? " AND C2.ESTOQUEATUAL > 0" : "";

        if ($codigoProduto != 0) {
            $where .= " AND C.CODIGO = " . $codigoProduto;
        }

        $sql = "SELECT
                    C.CODIGO,
                    C.DESCRICAO,
                    C2.ESTOQUEATUAL,
                    C.PRECO_VENDA_A,
                    C.PESO_BRUTO,
                    C.LARGURA,
                    C.ALTURA,
                    C.COMPRIMENTO,
                    C.INF_TECNICAS,
                    C.ALIQUOTAIPIVENDA,
                    C.UNIDADE AS REF_UNIDADE,
                    U.UNIDADE AS DESCRICAO_UNIDADE,
                    C.PROMOCIONAL,
                    C." . $coluna_tipo_preco . " AS CUSTO_BRUTO,
                    C.GRUPO,
                    C.NBMIPI,
                    C.ST,
                    C.EMBALAGEM_VENDA,
                    C.COD_BARRAS,
                    (SELECT
                        TOP(1) PERC_DESC
                    FROM " . CASHWIN . ".DBO.CAD_IEST_DESC CD
                    WHERE
                        (GETDATE() BETWEEN CD.DATA_HORA_INICIAL AND CD.DATA_HORA_FINAL) AND
                        CD.COD_PROD = C.CODIGO) AS PERC_DESCONTO
                    FROM " . CASHWIN . ".DBO.CAD_IEST C
                    JOIN " . $database_inc_mov . ".DBO.CAD_IEST C2 ON(C.CODIGO = C2.CODIGO)
                    JOIN CAD_UNID U ON(C.UNIDADE = U.CODIGO)
                    WHERE
                        C.ECOMMERCE = ? AND C.ATIVO = ?
                        $where";

        $query = $this->db->query($sql, array('S', 'S'));

        $produtos = $query->result_array();
        foreach ($produtos as $produto) {

            $percentual = $params['DESCONTO_MAXIMO'];
            if ($params['ONLINE_DESC_MAX_VERIF'] == 'I') {
                $percentual = ($produto['PERC_DESCONTO'] != NULL) ? $produto['PERC_DESCONTO'] : 0;
            }

            /* $preco_venda = $produto['PRECO_VENDA_A'];
              // irá entrar quando deve considerar ipi no valor do item;
              if ($params['DESC_IPI_VENDA'] == 'N') {
              $preco_venda = round($preco_venda + ($preco_venda * $produto['ALIQUOTAIPIVENDA'] / 100), 2);
              } */

            $result[] = array(
                'CODIGO' => $produto['CODIGO'],
                'DESCRICAO' => $produto['DESCRICAO'],
                'ESTOQUEATUAL' => number_format($produto['ESTOQUEATUAL'], 0, ',', '.'),
                'PRECO_VENDA_A' => number_format($produto['PRECO_VENDA_A'], $decimal, ',', ''),
                'PRECO_VENDA_A_ORIGINAL' => $produto['PRECO_VENDA_A'],
                'PERCENTUAL_DESCONTO' => number_format($percentual, 2, ',', ''),
                'PERCENTUAL_DESCONTO_ORIGINAL' => number_format($percentual, 2),
                'ALIQUOTA_IPI' => ($params['DESC_IPI_VENDA'] == 'S') ? 0 : number_format($produto['ALIQUOTAIPIVENDA'], 2, ',', ''),
                'ALIQUOTA_IPI_ORIGINAL' => ($params['DESC_IPI_VENDA'] == 'S') ? 0 : number_format($produto['ALIQUOTAIPIVENDA'], 2),
                'REF_UNIDADE' => $produto['REF_UNIDADE'],
                'DESCRICAO_UNIDADE' => $produto['DESCRICAO_UNIDADE'],
                'PROMOCIONAL' => $produto['PROMOCIONAL'],
                'NBMIPI' => $produto['NBMIPI'],
                'ST' => $produto['ST'],
                'CUSTO_BRUTO' => round($produto['CUSTO_BRUTO'], $decimal),
                'GRUPO' => $produto['GRUPO'],
                'EMBALAGEM_VENDA' => number_format($produto['EMBALAGEM_VENDA'], 0, ',', '.'),
                'COD_BARRAS' => $produto['COD_BARRAS'],
                'PESO_BRUTO' => $produto['PESO_BRUTO'],
                'LARGURA' => $produto['LARGURA'],
                'ALTURA' => $produto['ALTURA'],
                'COMPRIMENTO' => $produto['COMPRIMENTO'],
                'INF_TECNICAS' => $produto['INF_TECNICAS'],
            );
        }
        if ($query->num_rows() > 0) {
            return $result;
        } else {
            return false;
        }
    }

    public function getDadosVendasModels($num_venda) {

        $params = $this->getParametrosModels();
        $decimal = ($params['CASAS_DECIMAIS_VENDA'] == 'S') ? 2 : 3;

        $sql = "SELECT
			V.COD_VENDA,
			V.COD_CLIENTE,
			V.TOTAL_VENDA,
			C.NOME_FANTASIA,
			V.DATA_VENDA,
			V.ENVIADO_CAIXA,
			V.NOTAFISCAL,
			V.DATA_FECHAMENTO,
			V.TIPO_PAGTO,
			T.DESCRICAO AS DESCRICAO_TIPO,
    		V.COD_PAGAMENTO,
			P.DESCRICAO AS DESCRICAO_PAG,
			V.IMPRIMIU
		FROM " . CASHWIN . ".DBO.VENDA V
		JOIN CAD_CLIE C ON (V.COD_CLIENTE = C.CODIGO)
		JOIN PUBLICO_NEW.DBO.CON_PGTO P ON(V.COD_PAGAMENTO = P.CODIGO)
		JOIN PUBLICO_NEW.DBO.CAD_TIPO T ON(V.TIPO_PAGTO = T.SIGLA)
		WHERE 
			V.COD_VENDA = ?";

        $query = $this->db->query($sql, array($num_venda));

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $vendas) {
                $enviado_caixa = 'NÃO';
                if ($vendas['ENVIADO_CAIXA'] == 'S') {
                    $enviado_caixa = 'SIM';
                }

                $nota_fiscal = 'NÃO';
                if ($vendas['NOTAFISCAL'] == 'S') {
                    $nota_fiscal = 'SIM';
                }

                $imprimiu = 'NÃO';
                if ($vendas['IMPRIMIU'] == 'S') {
                    $imprimiu = 'SIM';
                }

                $data[] = array(
                    'COD_VENDA' => $vendas['COD_VENDA'],
                    'COD_CLIENTE' => $vendas['COD_CLIENTE'],
                    'NOME_FANTASIA' => $vendas['NOME_FANTASIA'],
                    'TOTAL_VENDA' => number_format($vendas['TOTAL_VENDA'], $decimal, ',', '.'),
                    //'DATA_VENDA' => $vendas['DATA_VENDA'],
                    'ENVIADO_CAIXA' => $enviado_caixa,
                    'NOTAFISCAL' => $nota_fiscal,
                    //'DATA_FECHAMENTO' => $vendas['DATA_FECHAMENTO'],
                    'TIPO_PAGTO' => $vendas['TIPO_PAGTO'],
                    'DESCRICAO_TIPO' => $vendas['DESCRICAO_TIPO'],
                    'COD_PAGAMENTO' => $vendas['COD_PAGAMENTO'],
                    'DESCRICAO_PAG' => $vendas['DESCRICAO_PAG'],
                    'IMPRIMIU' => $imprimiu,
                );
            }

            return $data;
        } else {
            return false;
        }
    }

    public function getVendasModels($vendedor, $data_inicial, $data_final) {

        $params = $this->getParametrosModels();
        $decimal = ($params['CASAS_DECIMAIS_VENDA'] == 'S') ? 2 : 3;

        $sql = "SELECT
			TOP 50
			V.COD_VENDA,
			V.COD_CLIENTE,
			V.TOTAL_VENDA,
			C.NOME_FANTASIA
		FROM " . CASHWIN . ".DBO.VENDA V
		JOIN CAD_CLIE C ON (V.COD_CLIENTE = C.CODIGO)
		WHERE 
			V.COD_VENDEDOR_EXT = ? AND V.DATA_VENDA BETWEEN ? and ?
		ORDER BY V.COD_VENDA DESC";

        $query = $this->db->query($sql, array($vendedor, $data_inicial, $data_final));

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $vendas) {
                $data[] = array(
                    'COD_VENDA' => $vendas['COD_VENDA'],
                    'COD_CLIENTE' => $vendas['COD_CLIENTE'],
                    'NOME_FANTASIA' => $vendas['NOME_FANTASIA'],
                    'TOTAL_VENDA' => number_format($vendas['TOTAL_VENDA'], $decimal, ',', '.')
                );
            }

            return $data;
        } else {
            return false;
        }
    }

    public function getCondPagamentosModels() {

        $params = $this->getParametrosModels();
        $decimal = ($params['CASAS_DECIMAIS_VENDA'] == 'S') ? 2 : 3;

        $sql = "SELECT CODIGO, DESCRICAO, ACRESCIMO FROM CON_PGTO C WHERE C.ECOMMERCE = ?";

        $query = $this->db->query($sql, 'S');

        if ($query->num_rows() > 0) {
            foreach ($query->result_array() as $cond) {
                $data[] = array(
                    'CODIGO' => $cond['CODIGO'],
                    'DESCRICAO' => $cond['DESCRICAO'],
                    'ACRESCIMO' => number_format($cond['ACRESCIMO'], $decimal)
                );
            }

            return $data;
        } else {
            return false;
        }
    }

    public function getTipoPagamentosModels() {
        $sql = "SELECT C.CODIGO, C.DESCRICAO, C.SIGLA, C.PREFIXO FROM CAD_TIPO C WHERE C.ECOMMERCE = ?";

        $query = $this->db->query($sql, 'S');

        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $tipo) {
                $data[] = array(
                    'CODIGO' => $tipo['CODIGO'],
                    'DESCRICAO' => $tipo['DESCRICAO'],
                    'PREFIXO' => $tipo['PREFIXO'],
                    'SIGLA' => $tipo['SIGLA']
                );
            }

            return $data;
        } else {
            return false;
        }
    }

    public function getEstadosModels() {
        $sql = "SELECT * FROM " . CASHWIN . ".DBO.ESTADO_ST";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {

            foreach ($query->result_array() as $estado) {
                $data[] = array(
                    'SIGLA_ESTADO' => $estado['SIGLA_ESTADO'],
                    'DESC_ESTADO' => $estado['DESC_ESTADO'],
                    'OPTANTE_ST' => $estado['OPTANTE_ST'],
                    'ALIQUOTA_EXTERNA_ICMS' => $estado['ALIQUOTA_EXTERNA_ICMS'],
                    'ALIQUOTA_INTERNA_ICMS' => $estado['ALIQUOTA_INTERNA_ICMS'],
                    'IMP_ALIQUOTA_INTERNA_ICMS' => $estado['IMP_ALIQUOTA_INTERNA_ICMS'],
                    'IMP_ALIQUOTA_EXTERNA_ICMS' => $estado['IMP_ALIQUOTA_EXTERNA_ICMS']
                );
            }

            return $data;
        } else {
            return false;
        }
    }

    public function getNbmiModels() {
        $sql = "SELECT
                    CAD_NBMI.CODIGO,
                    CAD_NBMI.NCM,
                    CAD_NBMI.NBM,
                    CAD_NBMI.ST_AC,
                    CAD_NBMI.ST_AL,
                    CAD_NBMI.ST_AM,
                    CAD_NBMI.ST_AP,
                    CAD_NBMI.ST_BA,
                    CAD_NBMI.ST_CE,
                    CAD_NBMI.ST_DF,
                    CAD_NBMI.ST_ES,
                    CAD_NBMI.ST_EX,
                    CAD_NBMI.ST_GOI,
                    CAD_NBMI.ST_MA,
                    CAD_NBMI.ST_MG,
                    CAD_NBMI.ST_MS,
                    CAD_NBMI.ST_MT,
                    CAD_NBMI.ST_PA,
                    CAD_NBMI.ST_PB,
                    CAD_NBMI.ST_PE,
                    CAD_NBMI.ST_PI,
                    CAD_NBMI.ST_PR,
                    CAD_NBMI.ST_RJ,
                    CAD_NBMI.ST_RN,
                    CAD_NBMI.ST_RO,
                    CAD_NBMI.ST_RR,
                    CAD_NBMI.ST_RS,
                    CAD_NBMI.ST_SC,
                    CAD_NBMI.ST_SE,
                    CAD_NBMI.ST_SP,
                    CAD_NBMI.ST_TOC,
                    CAD_NBMI.ST_SN_AC,
                    CAD_NBMI.ST_SN_AL,
                    CAD_NBMI.ST_SN_AM,
                    CAD_NBMI.ST_SN_AP,
                    CAD_NBMI.ST_SN_BA,
                    CAD_NBMI.ST_SN_CE,
                    CAD_NBMI.ST_SN_DF,
                    CAD_NBMI.ST_SN_ES,
                    CAD_NBMI.ST_SN_GOI,
                    CAD_NBMI.ST_SN_MA,
                    CAD_NBMI.ST_SN_MG,
                    CAD_NBMI.ST_SN_MS,
                    CAD_NBMI.ST_SN_MT,
                    CAD_NBMI.ST_SN_PA,
                    CAD_NBMI.ST_SN_PB,
                    CAD_NBMI.ST_SN_PE,
                    CAD_NBMI.ST_SN_PI,
                    CAD_NBMI.ST_SN_PR,
                    CAD_NBMI.ST_SN_RJ,
                    CAD_NBMI.ST_SN_RN,
                    CAD_NBMI.ST_SN_RO,
                    CAD_NBMI.ST_SN_RR,
                    CAD_NBMI.ST_SN_RS,
                    CAD_NBMI.ST_SN_SC,
                    CAD_NBMI.ST_SN_SE,
                    CAD_NBMI.ST_SN_SP,
                    CAD_NBMI.ST_SN_TOC,
                    CAD_NBMI.PERC_RED_ST,
                    CAD_NBMI.ICMS
            FROM " . CASHWIN . ".DBO.CAD_NBMI
            WHERE
                ATIVO = 'S'";

        $query = $this->db->query($sql);

        if ($query->num_rows() > 0) {
            return $query->result_array();
        } else {
            return false;
        }
    }

    public function getParametrosModels() {
        $sql = "SELECT
                    PAR_CASH.CODIGO,
                    PAR_CASH.NOME_FANTASIA,
                    PAR_CASH.ONLINE_VENDER_CLIENTE_BLOQ,
                    PAR_CASH.ONLINE_PERM_DIG_DESCONTO,
                    PAR_CASH.ONLINE_PERM_ALT_PRECOS,
                    PAR_CASH.ONLINE_CAD_CLIENTES,
                    PAR_CASH.ONLINE_ESTOQUE,
                    PAR_CASH.DESC_IPI_VENDA,
                    PAR_CASH.DATABASE_INC_MOV,
                    PAR_CASH.ONLINE_DESC_MAX_VERIF,
                    PAR_CASH.DESCONTO_MAXIMO,
                    PAR_CASH.CASAS_DECIMAIS_VENDA,
                    PAR_CASH.CALC_IMPOSTOS_NF,
                    PAR_CASH.ESTADO,
                    PAR_CASH.COD_CONDPGTO_PADRAO,
                    PAR_CASH.TRANSP_PADRAO,
                    PAR_CASH.FRETE_PADRAO,
                    PAR_CASH.ONLINE_COD_VEND_INTERNO_PADRAO,
                    PAR_CASH.CALC_RENTABILIDADE,
                    CON_PGTO.ACRESCIMO,
                    PAR_CASH.COD_CONDPGTO_PADRAO,
                    PAR_CASH.ONLINE_SERVER_SMTP_USUARIO,
                    PAR_CASH.ONLINE_USER_SMTP,
                    PAR_CASH.ONLINE_SENHA_EMAIL_USUARIO,
                    PAR_CASH.ONLINE_PORT_SMTP,
                    PAR_CASH.ONLINE_NOME_EMAIL_USUARIO,
                    PAR_CASH.EMAIL AS EMAIL_RESPONSAVEL--,
                    --PAR_CASH.LOGOMARCA
                    
                FROM PAR_CASH
                LEFT JOIN CON_PGTO ON (PAR_CASH.COD_CONDPGTO_PADRAO = CON_PGTO.CODIGO)
                WHERE
                    PAR_CASH.DIRETORIO = ?";
        $query = $this->db->query($sql, array(CASHWIN));

        if ($query->num_rows() > 0) {
            return $query->row_array();
        }
    }

    public function geraCodigoVenda($generator_nome = 'SEQ_VENDA') {
        $update = "UPDATE G SET G.GEN_VALUE = G.GEN_VALUE +1
                FROM " . CASHWIN . ".DBO.GENERATOR G
                WHERE
                    G.GEN_CODIGO = ?";
        $this->db->query($update, array($generator_nome));

        $sql = "SELECT GEN_VALUE FROM " . CASHWIN . ".DBO.GENERATOR WHERE GEN_CODIGO = ?";

        $query = $this->db->query($sql, array($generator_nome));

        return $query->row_array();
    }

    public function inserirVendaItens($dados) {
        return $this->db->insert_batch(CASHWIN . '.DBO.ITENSVEN', $dados);
    }

    public function inserirVendaModels($dados) {
        return $this->db->insert(CASHWIN . '.DBO.VENDA', $dados);
    }

    public function verificaExistenciaVenda($cod_venda) {
        $sql = "SELECT
				V.COD_VENDA
			FROM " . CASHWIN . ".DBO.VENDA V
			WHERE 
				V.PEDIDO_ECM = ?";

        $query = $this->db->query($sql, array($cod_venda));

        if ($query->num_rows() > 0) {
            return 'true';
        } else {
            return 'false';
        }
    }

    public function gerarCodigoCliente() {
        $busca_id = $this->db->query("SELECT MAX(CODIGO)+1 CODIGO FROM CAD_CLIE");

        $seq = $busca_id->row();
        $codigo = $seq->CODIGO;

        return $codigo;
    }

    public function inserirCliente($dados) {
        return $this->db->insert('CAD_CLIE', $dados);
    }

    public function updateCliente($dados) {
        $this->db->where('CODIGO', $dados['CODIGO']);
        $query = $this->db->update('CAD_CLIE', $dados);
        return $query;
    }

}
