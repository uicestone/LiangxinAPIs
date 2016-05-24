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
		.when('/welcome', {
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
		});

	$httpProvider.interceptors.push('HttpInterceptor');
	$locationProvider.html5Mode(true);

}]);

angular.module('liangxin-quiz.controllers', [])

.controller('WelcomeController', ['$scope', '$location', function($scope, $location){
	
	$scope.showingRules = false;
	
	$scope.showRule = function() {
		document.body.scrollTop = 0;
		$scope.showingRules = true;
	};
	$scope.startQuiz = function() {
		$location.path('questions');
	}
}])

.controller('QuestionsController', ['$scope', '$location', '$interval', '$timeout', '$anchorScroll', 'quiz', function($scope, $location, $interval, $timeout, $anchorScroll, quiz){

	document.body.scrollTop = 0;
	
	$scope.quiz = quiz;
	$scope.showingOutline = false;
	$scope.currentQuestion = 0;
	$scope.question = $scope.quiz.questions[0];

	var countdown = $interval(function() {
		$scope.timeLeft = new Date(quiz.timeout_at) - new Date();
	}, 1000);

	$scope.toggleOutline = function() {
		$scope.showingOutline = !$scope.showingOutline;
	};

	$scope.goToQuestion = function(index) {
		$scope.currentQuestion = index;
		$scope.question = $scope.quiz.questions[index];
	};

	$scope.submit = function() {
		$scope.submitting = true;
		$scope.quiz.$save({
			question_id: $scope.currentQuestion,
			user_answer: $scope.quiz.questions[$scope.currentQuestion].user_answer,
			finish: $scope.currentQuestion === $scope.quiz.questions.length - 1
		}, function() {
			$scope.submitting = false;
			$scope.question = $scope.quiz.questions[$scope.currentQuestion];
			
			// 不是最后一题
			if($scope.currentQuestion < $scope.quiz.questions.length - 1) {
				// $scope.goToQuestion($scope.currentQuestion + 1);
			}
			else {
				// $scope.currentQuestion = null;
				$interval.cancel(countdown);
			}
		}, function(err) {
			if(user.id == 1) {
				alert(err.data.message);
			}
			$scope.submitting = false;
		});
	};
	
	$scope.scrollToScore = function() {
		$anchorScroll.yOffset = 95;
		$anchorScroll('score-sb');
	}

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
