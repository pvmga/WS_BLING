<?php

function urlPadrao() {
  return 'http://localhost/projetos/WS_BLING/app';
}

function chaveKeyPadrao() {
  // TEUTOMAQ
  return "";
}

getPedidos();
function getPedidos() {
  $apikey = chaveKeyPadrao();
  $outputType = "json";
  $url = 'https://bling.com.br/Api/v2/pedidos/' . $outputType . '&filters=idSituacao[6]';
  $retorno = executarGetPedidos($url, $apikey);

  $pedidos = json_decode($retorno, true)['retorno']['pedidos'];

  foreach($pedidos as $pedido) {

    $codigoPedido = '';
    $codigoCliente = '';
    $atualizacaoStatus = '';
    // VERIFICAR CLIENTE
    $codigoCliente = getCliente($pedido['pedido']['cliente']);
    // INSERÇÃO PEDIDO
    if ($codigoCliente != '' && $codigoCliente != null) {
      $codigoPedido = getPedido($codigoCliente['CODIGO'], json_encode($pedido['pedido']));
    }
    // ATUALIZAR STATUS
    // 6 = Em aberto
    // 15 = Em andamento
    // 9 = Atendido
    if ($codigoPedido != '' && $codigoPedido != null) {
      $atualizacaoStatus = atualizarStatus($pedido['pedido']['numero'],'15');
    }

    echo '<pre>';
    //var_dump($pedido['pedido']['numero'], $codigoPedido);
    var_dump($codigoCliente, $codigoPedido, $atualizacaoStatus, $pedido['pedido']['situacao']);
    echo '</pre>';
  }
}

function getPedido($codigoCliente, $dadosVenda) {
  $URL_PADRAO = urlPadrao();

  $curl = curl_init();
  curl_setopt_array($curl, array(
    //CURLOPT_URL => "http://localhost/projetos/WS_BLING/app/getProdutos/?codigoProduto=417",
    CURLOPT_URL => $URL_PADRAO."/inserirVenda",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => array('codigoCliente' => $codigoCliente,'dadosVenda' => base64_encode($dadosVenda)),
  ));

  $response = curl_exec($curl);
  curl_close($curl);

  return json_decode($response, true);
}

function getCliente($dadosCliente) {
  //return $dadosCliente;
  $URL_PADRAO = urlPadrao();

  $cep = $dadosCliente['cep'];
  if (strlen($cep) == 8) {
    $cep = substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
  }

  $curl = curl_init();
  curl_setopt_array($curl, array(
    //CURLOPT_URL => "http://localhost/projetos/WS_BLING/app/getProdutos/?codigoProduto=417",
    CURLOPT_URL => $URL_PADRAO."/inserirCliente",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => array(
      'cnpj' => $dadosCliente['cnpj'],
      'rg' => $dadosCliente['rg'],
      'nome' => $dadosCliente['nome'],
      'endereco' => substr($dadosCliente['endereco'], 0, 40),
      'numero' => $dadosCliente['numero'],
      'complemento' => substr($dadosCliente['complemento'], 0, 20),
      'cidade' => substr($dadosCliente['cidade'], 0, 40),
      'bairro' => substr($dadosCliente['bairro'], 0, 40),
      'cep' => $cep,
      'uf' => $dadosCliente['uf'],
      'email' => $dadosCliente['email'],
      'celular' => $dadosCliente['celular'],
      'fone' => $dadosCliente['fone'],
    ),
  ));

  $response = curl_exec($curl);
  curl_close($curl);

  return json_decode($response, true);
}

function executarGetPedidos($url, $apikey){
  $curl_handle = curl_init();
  curl_setopt($curl_handle, CURLOPT_URL, $url . '&apikey=' . $apikey);
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
  $response = curl_exec($curl_handle);
  curl_close($curl_handle);
  return $response;
}

/* ATUALIZAR STATUS DOS PEDIDOS */
function atualizarStatus($numero_pedido, $status) {
  $apikey = chaveKeyPadrao();
  $url = 'https://bling.com.br/Api/v2/pedido/'.$numero_pedido.'/json';
  $xml = '<?xml version="1.0" encoding="UTF-8"?>
          <pedido>
              <idSituacao>'.$status.'</idSituacao>
          </pedido>';
  $posts = array (
      'apikey' => $apikey,
      'xml' => rawurlencode($xml)
  );

  $retorno = executarAtualizarStatus($url, $posts);
  return $retorno;
}

function executarAtualizarStatus($url, $data){
  $curl_handle = curl_init();
  curl_setopt($curl_handle, CURLOPT_URL, $url);
  curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($curl_handle, CURLOPT_POST, count($data));
  curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
  curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
  $response = curl_exec($curl_handle);
  curl_close($curl_handle);
  return $response;
}

/* /ATUALIZAR STATUS DOS PEDIDOS */