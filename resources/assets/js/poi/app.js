'use strict';

// Declare app level module which depends on views, and components
angular.module('liangxin-poi', [
	'ngRoute',
	'ui.bootstrap',
	'liangxin-poi.services',
	'liangxin-poi.controllers'
])
.config(['$routeProvider', '$httpProvider', '$locationProvider', '$sceProvider', function($routeProvider, $httpProvider, $locationProvider, $sceProvider) {
	$routeProvider
		.when('/', {
			controller: 'WelcomeController',
			templateUrl: '../assets/html/poi/welcome.html'
		})
		.when('/:category', {
			controller: 'PoiListController',
			templateUrl: '../assets/html/poi/list.html',
			resolve: {
				pois: ['$route', 'Poi', function($route, Poi) {
					return Poi.query({class_type: $route.current.params.category, per_page: 999}).$promise;
				}]
			}
		})
		.when('/:category/:id', {
			controller: 'PoiDetailController',
			templateUrl: '../assets/html/poi/detail.html',
			resolve: {
				poi: ['$route', 'Poi', function($route, Poi) {
					return Poi.get({id: $route.current.params.id}).$promise;
				}]
			}
		});

	$httpProvider.interceptors.push('HttpInterceptor');
	$locationProvider.html5Mode(true);
	$sceProvider.enabled(false);

}]);

angular.module('liangxin-poi.controllers', [])

.controller('WelcomeController', ['$scope', '$location', '$timeout', function($scope, $location, $timeout){

	$scope.zoom = 1;
	$scope.windowWidth = window.innerWidth;
	
	$scope.zoomIn = function () {
		$scope.zoomBefore = $scope.zoom;
		$scope.zoom = $scope.zoom * 1.5;
		$timeout(function () {
			$('.map-scroller').scrollLeft($('.map-scroller').scrollLeft() + ($scope.zoom - $scope.zoomBefore) * $scope.windowWidth / 2);
		});
	};

	$scope.zoomOut = function () {
		$scope.zoom = $scope.zoom / 1.5;
		$timeout(function () {
			// $('.map-scroller').scrollLeft($('.map-scroller').scrollLeft() - ($scope.zoomBefore - $scope.zoom) * $scope.windowWidth / 2);
		});
	};

}])

.controller('PoiListController', ['$scope', '$location', '$route', 'pois', function($scope, $location, $route, pois){

	$scope.pois = pois;

	$scope.category = $route.current.params.category;

	switch ($scope.category) {
		case 'service-center' : $scope.listTitle = '党建服务分中心分布情况'; break;
		case 'workshop' : $scope.listTitle = '党代表工作室分布情况'; break;
		case 'consultant' : $scope.listTitle = '党建顾问分布情况'; break;
	}

	$scope.showPoiDetail = function (poi) {
		$location.url($location.path() + '/' + poi.id);
	};

	$scope.back = function () {
		window.history.back();
	};

}])

.controller('PoiDetailController', ['$scope', '$location', 'poi', function($scope, $location, poi){

	$scope.poi = poi;

}])

.controller('AlertController', ['$scope', '$rootScope', 'Alert',
	function($scope, $rootScope, Alert){
		$scope.alerts = Alert.get();
		$scope.close = Alert.close;
		$scope.previous = function(){};
		$scope.next = function(){};

		$scope.toggleCloseButton = function(index){
			$scope.alerts[index].closeable = !$scope.alerts[index].closeable;
		};

		$rootScope.$on('$routeChangeSuccess', function(){
			Alert.clear();
		});
	}
]);
