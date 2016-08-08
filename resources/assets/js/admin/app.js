'use strict';

// Declare app level module which depends on views, and components
angular.module('liangxin', [
	'ngRoute',
	'ui.bootstrap',
	'liangxin.services',
	'liangxin.posts',
	'liangxin.groups',
	'liangxin.users'
])
.config(['$routeProvider', '$httpProvider', '$locationProvider', function($routeProvider, $httpProvider, $locationProvider) {
	$routeProvider
		.when('/user', {
			controller: 'UserController',
			templateUrl: '../assets/html/admin/user/list.html',
			resolve: {
				users: ['$route', 'User', function($route, User){
					return User.query(angular.extend({per_page: 20}, $route.current.params)).$promise;
				}]
			}
		})
		.when('/user/:id', {
			controller: 'UserEditController',
			templateUrl: '../assets/html/admin/user/edit.html',
			resolve: {
				user: ['$route', 'User', function($route, User){
					if($route.current.params.id === 'new'){
						return new User();
					}
					return User.get({id: $route.current.params.id}).$promise;
				}]
			}
		})
		.when('/group', {
			controller: 'GroupController',
			templateUrl: '../assets/html/admin/group/list.html',
			resolve: {
				groups: ['$route', 'Group', function($route, Group){
					return Group.query(angular.extend({per_page: 20}, $route.current.params)).$promise;
				}]
			}
		})
		.when('/group/:id', {
			controller: 'GroupEditController',
			templateUrl: '../assets/html/admin/group/edit.html',
			resolve: {
				group: ['$route', 'Group', function($route, Group){
					if($route.current.params.id === 'new'){
						return new Group();
					}
					return Group.get({id: $route.current.params.id}).$promise;
				}]
			}
		})
		.when('/post', {
			controller: 'PostController',
			templateUrl: '../assets/html/admin/post/list.html',
			resolve: {
				posts: ['$route', 'Post', function($route, Post){
					return Post.query(angular.extend({per_page: 20}, $route.current.params)).$promise;
				}]
			}
		})
		.when('/post/:id', {
			controller: 'PostEditController',
			templateUrl: '../assets/html/admin/post/edit.html',
			resolve: {
				post: ['$route', 'Post', function($route, Post){
					if($route.current.params.id === 'new'){
						return new Post();
					}
					return Post.get({id: $route.current.params.id}).$promise;
				}]
			}
		})
		.otherwise({redirectTo: '/group'});

	$httpProvider.interceptors.push('HttpInterceptor');
	
	$locationProvider.html5Mode(true);

}])

.controller('AlertCtrl', ['$scope', '$rootScope', 'Alert',
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
])

angular.module('liangxin.groups', [])
.controller('GroupController', ['$scope', '$location', 'groups', function($scope, $location, groups){
	$scope.groups = groups;
	$scope.currentPage = $location.search().page || 1;
	
	// get pagination argument from headers
	var headers = $scope.groups.$response.headers();
	$scope.itemsTotal = Number(headers['items-total']);
	$scope.itemsStart = Number(headers['items-start']);
	$scope.itemsEnd = Number(headers['items-end']);
	
	$scope.nextPage = function(){
		$location.search('page', ++$scope.currentPage);
	}

	$scope.previousPage = function(){
		$scope.currentPage--;
		$location.search('page', $scope.currentPage === 1 ? null : $scope.currentPage);
	}
	
	$scope.editGroup = function(group){
		$location.url('group/' + group.id);
	}
}])
.controller('GroupEditController', ['$scope', '$location', 'group', 'Alert', 'Group', function($scope, $location, group, Alert, Group){
	$scope.group = group;
	$scope.save = function(group){
		group.$save({}, function(){
			Alert.add('群组已保存', 'success');
			$location.replace().url('group/' + group.id);
		});
	}
	$scope.searchGroup = function(name){
		return Group.query({keyword: name}).$promise;
	}
}]);

angular.module('liangxin.users', []).controller('UserController', ['$scope', '$location', 'users', function($scope, $location, users){
	$scope.users = users;
	$scope.currentPage = $location.search().page || 1;
	
	// get pagination argument from headers
	var headers = $scope.users.$response.headers();
	$scope.itemsTotal = Number(headers['items-total']);
	$scope.itemsStart = Number(headers['items-start']);
	$scope.itemsEnd = Number(headers['items-end']);
	
	$scope.search = function(keyword){
		keyword = keyword || null;
		$location.search('keyword', keyword);
	};
	
	$scope.queryArgs = $location.search();
	
	$scope.nextPage = function(){
		$location.search('page', ++$scope.currentPage);
	}

	$scope.previousPage = function(){
		$scope.currentPage--;
		$location.search('page', $scope.currentPage === 1 ? null : $scope.currentPage);
	}
	
	$scope.editUser = function(user){
		$location.url('user/' + user.id);
	}
}])
.controller('UserEditController', ['$scope', '$location', 'user', 'Alert', 'Group', function($scope, $location, user, Alert, Group){
	$scope.user = user;
	$scope.save = function(user){
		user.$save({}, function(){
			Alert.add('用户已保存', 'success');
			$location.replace().url('user/' + user.id);
		});
	};
	$scope.delete = function(user){
		user.$delete(function(){
			history.back();
		});
	};
	$scope.searchGroup = function(name){
		return Group.query({keyword: name}).$promise;
	}
}]);

angular.module('liangxin.posts', ['ngFileUpload']).controller('PostController', ['$scope', '$location', 'posts', function($scope, $location, posts){
	$scope.posts = posts;
	$scope.currentPage = $location.search().page || 1;
	
	// get pagination argument from headers
	var headers = $scope.posts.$response.headers();
	$scope.itemsTotal = Number(headers['items-total']);
	$scope.itemsStart = Number(headers['items-start']);
	$scope.itemsEnd = Number(headers['items-end']);
	
	$scope.search = $location.search();
	
	$scope.nextPage = function(){
		$location.search('page', ++$scope.currentPage);
	}

	$scope.previousPage = function(){
		$scope.currentPage--;
		$location.search('page', $scope.currentPage === 1 ? null : $scope.currentPage);
	}
	
	$scope.editPost = function(post){
		$location.url('post/' + post.id);
	}
}])
.controller('PostEditController', ['$scope', '$location', 'post', 'Alert', 'Group', 'User', 'Post', 'Upload', function($scope, $location, post, Alert, Group, User, Post, Upload){
	$scope.post = post;
	$scope.save = function(post){
		post.$save({}, function(post){
			Alert.add('文章已保存' , 'success');
			$location.replace().url('post/' + post.id);
		});
	}
	
	$scope.search = $location.search();
	
	if($scope.search.type){
		$scope.post.type = $scope.search.type;
	}
	
	$scope.searchGroup = function(name){
		return Group.query({keyword: name}).$promise;
	}
	$scope.searchUser = function(name){
		return User.query({keyword: name, with_group: true}).$promise;
	}
	$scope.searchPost = function(name){
		return Post.query({keyword: name}).$promise;
	}
	
	$scope.remove = function(post){
		post.$remove({}, function(){
			$location.replace().url('post?type=' + $scope.post.type);
		});
	}
	
	$scope.$watch('file', function () {
        $scope.upload($scope.file);
    });
	
	$scope.$watch('poster', function(){
		$scope.upload($scope.poster, {type: '封面'}, function(post){
			$scope.post.poster = post;
		});
	})
	
    $scope.upload = function (file, fields, callback) {
		
		if(!file) return;

		Upload.upload({
			url: '../api/v1/post',
			file: file,
			fileFormDataName: 'file',
			fields: fields
		})
//		.progress(function (evt) {
//			var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
//			console.log('progress: ' + progressPercentage + '% ' + evt.config.file.name);
//		})
		.success(function (post, status, headers, config) {
			Alert.add('文件上传成功', 'success');
			$scope.post.poster = post;
		})
//		.error(function (data, status, headers, config) {
//			console.log('error status: ' + status);
//		})
    };
}]);

