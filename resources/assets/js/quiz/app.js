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
			templateUrl: '../assets/html/quiz/welcome.html'
		})
		.when('/questions/:quizId?', {
			controller: 'QuestionsController',
			templateUrl: '../assets/html/quiz/questions.html',
			resolve: {
				quiz: ['$route', 'Quiz', function($route, Quiz) {
					if($route.current.params.quizId) {
						return Quiz.get({id: $route.current.params.quizId}).$promise;
					} else {
						var quiz = new Quiz();
						return quiz.$save();
					}
				}],
				quizzes: ['Quiz', function(Quiz) {
					return Quiz.query().$promise;
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

.controller('QuestionsController', ['$scope', '$location', '$route', '$interval', '$timeout', '$anchorScroll', 'quiz', 'quizzes', 'Bridge', function($scope, $location, $route, $interval, $timeout, $anchorScroll, quiz, quizzes, Bridge){

	$scope.quiz = quiz;
	$scope.showingOutline = false;
	$scope.currentQuestion = 0;
	$scope.question = $scope.quiz.questions[0];
	$scope.shouldShowCloseButton = userAgent === 'iOS app';
	$scope.finishedAll = quizzes.length === quiz.attempts_limit;
	
	$scope.quizzes = quizzes;

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
	
	$scope.continue = function() {
		if($location.path() === '/questions') {
			$route.reload();
		}

		$location.path('questions');
	};
	
	$scope.scrollToScore = function() {
		$timeout(function() {
			$anchorScroll.yOffset = 95;
			$anchorScroll('score-sb');
		});
	};

	$scope.close = function() {
		Bridge.exec('closeWindow');
		$timeout(function() {
			alert('本次结果已经保存, 如果页面没有关闭, 请手动退出');
		}, 3000);
	};

	$scope.showQuiz = function(id) {
		$location.path('questions/' + id);
	};

	if($scope.quiz.score === null) {
		document.body.scrollTop = 0;
	} else {
		$scope.scrollToScore();
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
