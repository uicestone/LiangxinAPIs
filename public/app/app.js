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
.config(['$routeProvider', '$httpProvider', function($routeProvider, $httpProvider) {
	$routeProvider
		.when('/user', {
			controller: 'UserController',
			templateUrl: 'app/user/list.html',
			resolve: {
				users: ['$route', 'User', function($route, User){
					return User.query(angular.extend({per_page: 20}, $route.current.params)).$promise;
				}]
			}
		})
		.when('/user/:id', {
			controller: 'UserEditController',
			templateUrl: 'app/user/edit.html',
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
			templateUrl: 'app/group/list.html',
			resolve: {
				groups: ['$route', 'Group', function($route, Group){
					return Group.query(angular.extend({per_page: 20}, $route.current.params)).$promise;
				}]
			}
		})
		.when('/group/:id', {
			controller: 'GroupEditController',
			templateUrl: 'app/group/edit.html',
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
			templateUrl: 'app/post/list.html',
			resolve: {
				posts: ['$route', 'Post', function($route, Post){
					return Post.query(angular.extend({per_page: 20}, $route.current.params)).$promise;
				}]
			}
		})
		.when('/post/:id', {
			controller: 'PostEditController',
			templateUrl: 'app/post/edit.html',
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

}])

.controller('AlertCtrl', ['$scope', 'Alert',
	function($scope, Alert){
		$scope.alerts = Alert.get();
		$scope.close = Alert.close;
		$scope.previous = function(){};
		$scope.next = function(){};
		
		$scope.toggleCloseButton = function(index){
			$scope.alerts[index].closeable = !$scope.alerts[index].closeable;
		};
	}
]);

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
			$location.url('group/' + group.id);
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
			$location.url('user/' + user.id);
		});
	}
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
			$location.url('post/' + post.id);
		});
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
	
	$scope.$watch('file', function () {
        $scope.upload($scope.file);
    });
	
	$scope.$watch('poster', function(){
		$scope.upload($scope.poster, 'poster');
	})
	
    $scope.upload = function (file, key) {
		if(!file) return;
		
		Upload.upload({
			url: 'api/v1/post/' + post.id,
			fields: {'_method': 'put'},
			file: file,
			fileFormDataName: key || 'file'
		})
//		.progress(function (evt) {
//			var progressPercentage = parseInt(100.0 * evt.loaded / evt.total);
//			console.log('progress: ' + progressPercentage + '% ' + evt.config.file.name);
//		})
		.success(function (data, status, headers, config) {
			Alert.add('文件上传成功', 'success');
			$scope.post.url = data.url;
			$scope.post.poster = data.poster;
		})
//		.error(function (data, status, headers, config) {
//			console.log('error status: ' + status);
//		})
    };
}]);

