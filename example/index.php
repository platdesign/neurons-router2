<?php


	require 'vendor/neurons/neurons.php';
	require '../module.php';

	nrns::devMode();


	$app = nrns::module('app', ['router']);

	$app->config(function($routeProvider){

		$router = new router\Router();


			$router->get('/', function(){ echo 'LEER'; });
			$router->get('/a', function(){ echo 'A'; });
			$router->get('/b', function(){ echo 'B'; });

		$routeProvider->uses('/huhu', $router);




		$router = new router\Router();

			$router->get('/c', function(){ echo 'C'; });
			$router->get('/d', function(){ echo 'D'; });




		$subrouter = new router\Router();
		$subrouter->get('/sub', function(){ echo 'sub'; });

		$router->uses('/subrouter', $subrouter);

		$routeProvider->uses($router);

		$routeProvider->get('/test', function(){
			echo 'test';
		});






	});



 ?>