<?PHP
namespace router;
use nrns;


	class routeProvider extends nrns\Provider {

		public function __construct($nrns, $request, $injectionProvider) {
			$this->injection 	= $injectionProvider;
			$this->request 		= $request;
			$this->nrns			= $nrns;

			$this->checkForHtaccess();


			$this->router = new Router();


			$this->nrns->on('init', function(){
				$this->routes = $this->router->getRoutes();

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
			return call_user_func_array([$this->router, 'uses'], func_get_args());
		}

		public function get() {
			return call_user_func_array([$this->router, 'get'], func_get_args());
		}

		public function post() {
			return call_user_func_array([$this->router, 'post'], func_get_args());
		}

		public function put() {
			return call_user_func_array([$this->router, 'put'], func_get_args());
		}

		public function delete() {
			return call_user_func_array([$this->router, 'delete'], func_get_args());
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
