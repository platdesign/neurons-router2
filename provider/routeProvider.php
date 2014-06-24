<?PHP

	namespace router;
	use nrns;

	class middleware {
		public function __construct($method, $routeString, $handler) {

			$this->method = strtoupper($method);
			$this->routeString = $routeString;
			$this->handler = $handler;

		}


		public function matches($method, $routeString) {

			if($method === $this->method || $this->method === 'USES' || $this->method === 'ALL') {
				$paramValues = (object) [];

				$regex = preg_replace_callback(
					'#:([\w]+)(:([^/\(\)]*))?#',
					function($matches) use ($paramValues) {
						if(isset($matches[3])) {
							return '(?P<'.$matches[1].'>'.$matches[3].')';
						}

						$paramValues->{$matches[1]} = null;
						return '(?P<'.$matches[1].'>[^/\?]+)';
					},
					str_replace([')', '*'], [')?', '(.*)'], $this->routeString)
				);


				if(preg_match('#^'.$regex.'(?:\?.*)?$#i', $routeString, $matches)) {

					foreach($paramValues as $k => $v) {
						$this->params->{$k} = (array_key_exists($k, $matches)) ? urldecode($matches[$k]) : null;
					}
					return true;
				}
			}

		}


		public function execute() {
			$this->_executeHandler($this->handler);
		}

		private function _executeHandler($handler) {
			if( is_array($handler) ) {
				foreach($handler as $h) {
					if( $this->_executeHandler($h) !== NULL) {
						$this->stop();
					}
					if($this->stopped) {
						break;
					}
				}
			}

			if( is_callable($handler) ) {
				return nrns::$injection->invoke($handler, ['route'=>$this]);
			}
		}

		public function stop() {
			$this->stopped=true;
		}

		public function __toString() {
			return $this->routeString;
		}
	}













	class routeProvider extends nrns\Provider {

		public function __construct($nrns, $request, $injectionProvider) {
			$this->injection 	= $injectionProvider;
			$this->request 		= $request;
			$this->nrns			= $nrns;

			$this->checkForHtaccess();


			$this->nrns->on('init', function(){
				$this->activeRoutes = $this->findRoutes();
			});

			// Add event to the app which executes the active route on app-start
			$this->nrns->on('run', function(){

				if($this->activeRoutes) {

					foreach($this->activeRoutes as $route) {
						$route->execute();
						if($route->stopped) {
							break;
						}
					}

				} else {
					nrns::$injection->invoke($this->otherwise, ['route'=>null]);
				}

			});
		}





		public function getActiveRoute() {
			return $this->activeRoute;
		}

		public function otherwise($closure) {
			$this->otherwise = $closure;
			return $this;
		}




		public function uses() {
			return $this->_addRoute(array_merge(['uses'], func_get_args()));
		}

		public function get() {
			return $this->_addRoute(array_merge(['get'], func_get_args()));
		}

		public function post() {
			return $this->_addRoute(array_merge(['post'], func_get_args()));
		}

		public function put() {
			return $this->_addRoute(array_merge(['put'], func_get_args()));
		}

		public function delete() {
			return $this->_addRoute(array_merge(['delete'], func_get_args()));
		}



		private function _addRoute($args) {
			return call_user_func_array([$this, 'addRoute'], $args);
		}

		public function addRoute($method) {
			$route = str_replace("//", "/", $route);

			$args = array_splice(func_get_args(), 1);

			if( is_string($args[0]) ) {
				$routeString = $args[0];
				$handler = array_splice($args, 1);
			} else {
				$routeString = '/*';
				$handler = $args;
			}


			$this->routes[] = $this->injection->invoke('router\middleware', [
				'method'		=>	$method,
				'routeString' 	=>	$routeString,
				'handler' 		=>	$handler
			]);

			return $this;
		}





		private function findRoutes() {
			$method = $this->request->getMethod();
			$routeString = $this->request->getRoute();


			$matchingRoutes = [];

			foreach($this->routes as $route) {
				if($route->matches($method, $routeString)) {
					$matchingRoutes[] = $route;
				}
			}
			return $matchingRoutes;
		}







    	public function getService() {
    		return $this->activeRoute;
    	}











		private function checkForHtaccess() {

			$htaccessFile = __SCRIPT__."/.htaccess";


			if(!file_exists($htaccessFile)) {

				$htaccessContent = str_replace("\t", "", 'RewriteEngine On

					RewriteBase '.dirname($_SERVER['PHP_SELF']).'

					# Remove double slashes in whole URL
					RewriteCond %{REQUEST_URI} ^(.*)//(.*)$
					RewriteRule . %1/%2 [R=301,L]

					# Send each Request to index.php
					RewriteCond %{REQUEST_FILENAME} !-f
					RewriteCond %{REQUEST_FILENAME} !-d
					RewriteRule ^ index.php [QSA,L]
				');

				if( is_writable($htaccessFile) ) {
					file_put_contents($htaccessFile, $htaccessContent);
				} else {
					throw nrns::Exception("Create .htaccess-File at <br><pre>".$htaccessFile."</pre> with following Content<hr><pre>".$htaccessContent."</pre>");
				}



			}

		}



	}



?>
