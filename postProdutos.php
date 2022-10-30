<?php

//PPO
//$URL_PADRAO = "http://localhost:8080/app/";

//LOCALHOST
$URL_PADRAO = "";

postProdutoBling();
function postProdutoBling() {
	$URL_PADRAO = "http://localhost/projetos/WS_BLING/app/";
	$curl = curl_init();

	curl_setopt_array($curl, array(
		//CURLOPT_URL => "http://localhost/projetos/WS_BLING/app/getProdutos/?codigoProduto=417",
		CURLOPT_URL => $URL_PADRAO."getProdutos",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
		"Content-Type: application/json"
		),
	));

	$produtos = curl_exec($curl);
	curl_close($curl);

	//<vlr_unit>'.$produto['PRECO_VENDA_A'].'</vlr_unit>
	foreach(json_decode($produtos, true) as $produto) {
		$url = 'https://bling.com.br/Api/v2/produto/json/';
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
				<produto>
				<codigo>'.$produto['CODIGO'].'</codigo>
				<descricao>'.$produto['DESCRICAO'].'</descricao>
				<situacao>Ativo</situacao>
				<descricaoCurta></descricaoCurta>
				<descricaoComplementar></descricaoComplementar>
				<un>UN</un>
				<peso_bruto>'.$produto['PESO_BRUTO'].'</peso_bruto>
				<peso_liq>'.$produto['PESO_BRUTO'].'</peso_liq>
				<estoque>'.$produto['ESTOQUEATUAL'].'</estoque>
				<largura>'.$produto['LARGURA'].'</largura>
				<altura>'.$produto['ALTURA'].'</altura>
				<profundidade>'.$produto['COMPRIMENTO'].'</profundidade>
				<observacoes>'.$produto['INF_TECNICAS'].'</observacoes>
				</produto>';

		$posts = array (
			"apikey" => "926789ccf983e70a7e726dadf16696319530f798366d8edb03208d9a22a76490597617ed",
			"xml" => rawurlencode($xml)
		);
		$retorno = executeInsertProduct($url, $posts);
		$res = json_decode($retorno, true);

		// print inserção.
		//var_dump(array('sku' => $res['retorno']['produtos'][0][0]['produto']['codigo'], 'codigo_bling' => $res['retorno']['produtos'][0][0]['produto']['id'], 'estoque' => $produto['ESTOQUEATUAL']));
	}
}


function executeInsertProduct($url, $data) {
	$curl_handle = curl_init();
	curl_setopt($curl_handle, CURLOPT_URL, $url);
	curl_setopt($curl_handle, CURLOPT_POST, count($data));
	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
	$response = curl_exec($curl_handle);
	curl_close($curl_handle);
	return $response;
}