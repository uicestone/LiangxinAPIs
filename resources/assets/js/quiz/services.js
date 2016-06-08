'use strict';

var responseInterceptor = function(response){
	response.resource.$response = response;
	return response.resource;
};

angular.module('liangxin-quiz.services', ['ngResource'])

.service('Question', ['$resource', function($resource){
	var question = $resource('../api/v1/question/:id', {id: '@id'}, {
		query: {method: 'GET', isArray: true, interceptor: {response: responseInterceptor}},
		create: {method: 'POST'},
		update: {method: 'PUT'}
	});
	
	question.prototype.$save = function(a, b, c, d){
		if(this.id){
			return this.$update(a, b, c, d);
		}
		else{
			return this.$create(a, b, c, d);
		}
	};
	
	return question;
}])

.service('Quiz', ['$resource', function($resource){
	var quiz = $resource('../api/v1/quiz/:id', {id: '@id'}, {
		query: {method: 'GET', isArray: true, interceptor: {response: responseInterceptor}},
		create: {method: 'POST'},
		update: {method: 'PUT'},
		getResult: {method: 'GET', isArray: true, url: '../api/v1/quiz/result/:round', params: {round: '@round'}}
	});
	
	quiz.prototype.$save = function(a, b, c, d){
		if(this.id){
			return this.$update(a, b, c, d);
		}
		else{
			return this.$create(a, b, c, d);
		}
	};
	
	return quiz;
}])

.service('HttpInterceptor', ['$q', '$timeout', 'Alert', function($q, $timeout, Alert) {

	return {
		request: function(config) {

			if(config && config.cache === undefined){

				config.alert = {normal: {}, slow: {}};

				config.alert.normal.timeout = $timeout(function(){
					config.alert.normal.id = Alert.add('正在加载...');
				}, 200);

				config.alert.slow.timeout = $timeout(function(){
					Alert.close(config.alert.normal.id);
					config.alert.slow.id = Alert.add('仍在继续...');
				}, 5000);

				config.headers['Xinxin-Request-From'] = 'admin';
				config.headers['Authorization'] = token || (localStorage && localStorage.getItem('token'));

				return config;
			}

			return config || $q.when(config);
		},
		requestError: function(rejection) {
			return $q.reject(rejection);
		},
		response: function(response) {

			if(response && response.config.cache === undefined){
				$timeout.cancel(response.config.alert.normal.timeout);
				$timeout.cancel(response.config.alert.slow.timeout);
				Alert.close(response.config.alert.normal.id);
				Alert.close(response.config.alert.slow.id);
			}

			return response || $q.when(response);
		},
		responseError: function(rejection) {

			$timeout.cancel(rejection.config.alert.normal.timeout);
			$timeout.cancel(rejection.config.alert.slow.timeout);
			Alert.close(rejection.config.alert.normal.id);
			Alert.close(rejection.config.alert.slow.id);

			if(rejection.status === 401){
				alert(rejection.statusText);
			}

			if(rejection.data.message){
				Alert.add(rejection.data.message, 'danger', true);
			}
			else if(rejection.status > 0){
				Alert.add(rejection.statusText, 'danger', true);
			}

			return $q.reject(rejection);
		}
	};
}])

.service('Alert', [function(){
	
	var items = [];
		
	this.get = function(){
		return items;
	};

	this.add = function(message, type) {
		var id = new Date().getTime();
		items.push({id: id, msg: message, type: type === undefined ? 'warning' : type});
		return id;
	};

	this.close = function(id){
		if(id === undefined){
			return;
		}
		for(var index in items){
			if (items.hasOwnProperty(index) && items[index].id === id){
				break;
			}
		}
		items.splice(index, 1);
	};
	
	this.clear = function(){
		items.splice(0, items.length);
	}
	
}])
.service('Bridge', function() {
	return {
		_exec: function(method, params, callback){
			var callbackName = "LiangxinJSCallback_" + (+new Date()) + "_" + Math.floor(Math.random() * 50);
			var iframe = document.createElement('iframe');
			window[callbackName] = callback;
			iframe.src = "js://_?method=" + method + "&params=" + encodeURIComponent(JSON.stringify(params)) + "&callback=" + callbackName;
			console.log("iframe src", iframe.src);
			document.body.appendChild(iframe);
			iframe.style.display = "none";
			function removeNode(){
				iframe.onload = iframe.onerror = null;
				iframe.parentNode && iframe.parentNode.removeChild(iframe);
			}
			iframe.onload = iframe.onerror = removeNode;
			setTimeout(removeNode, 1000);
		},
		exec: function(method, params){
			var Bridge = this;
			return new Promise(function(resolve, reject){
				Bridge._exec(method, params, function(result){
					var error = result && result.error;
					var fail = params.fail;
					var success = params.success;
					if(error){
						error = new Error(error);
						fail && fail(error);
						reject(error);
						Bridge.onerror && Bridge.onerror(error);
					}else{
						success && success(result);
						resolve(result);
					}
				});
			});
		}
	};
})
.filter('choiceLabel', function(){
	var choiceLabel = ['A', 'B', 'C', 'D', 'E'];
	return function(choiceIndex){
		return  choiceLabel[choiceIndex];
	};
});

