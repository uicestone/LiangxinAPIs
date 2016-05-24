'use strict';

// Declare app level module which depends on views, and components
angular.module('liangxin-quiz', [
	'ngRoute',
	'ui.bootstrap',
	'liangxin-quiz.services',
	'liangxin-quiz.controllers'
])
.config(['$routeProvider', '$httpProvider', '$locationProvider', function($routeProvider, $httpProvider, $locationProvider) {
	$routeProvider
		.when('/', {
			controller: 'WelcomeController',
			templateUrl: '../quiz/welcome.html'
		})
		.when('/questions', {
			controller: 'QuestionsController',
			templateUrl: '../quiz/questions.html',
			resolve: {
				quiz: ['Quiz', function(Quiz){
					var quiz = new Quiz();
					return quiz.$save();
				}]
			}
		})
		.when('/result', {
			controller: 'ResultController',
			templateUrl: '../quiz/result'
		})
		.otherwise({redirectTo: '/'});

	$httpProvider.interceptors.push('HttpInterceptor');
	$locationProvider.html5Mode(true);

}]);

angular.module('liangxin-quiz.controllers', [])

.controller('WelcomeController', ['$scope', '$location', function($scope, $location){
	
	$scope.showingRules = false;
	
	$scope.showRule = function() {
		$scope.showingRules = true;
	};
	$scope.startQuiz = function() {
		$location.path('questions');
	}
}])

.controller('QuestionsController', ['$scope', '$location', '$interval', 'quiz', function($scope, $location, $interval, quiz){

	$scope.quiz = quiz;
	$scope.showingOutline = false;
	$scope.currentQuestion = 0;

	$interval(function() {
		$scope.timeLeft = new Date(quiz.timeout_at) - new Date();
	}, 1000);

	$scope.toggleOutline = function() {
		$scope.showingOutline = !$scope.showingOutline;
	};

	$scope.goToQuestion = function(index) {
		$scope.currentQuestion = index;
	};

	$scope.submit = function() {
		$scope.submitting = true;
		$scope.quiz.$save({
			question_id: $scope.currentQuestion,
			user_answer: $scope.quiz.questions[$scope.currentQuestion].user_answer,
			finish: $scope.currentQuestion === $scope.quiz.questions.length - 1
		}, function() {
			$scope.submitting = false;
			if($scope.currentQuestion < $scope.quiz.questions.length - 1) {
				// $scope.goToQuestion($scope.currentQuestion + 1);
			}
			else {
				$scope.currentQuestion = null;
			}
		}, function(err) {
			alert(err.data.message);
			$scope.submitting = false;
		});
	};

	$scope.finish = function() {
		$window.close();
	};
	
}])

.controller('ResultController', ['$scope', '$location', function($scope, $location){

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
