<!DOCTYPE html>
<html ng-app="liangxin">

	<head>

		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="author" content="Uice Lu">
		<meta name="renderer" content="webkit">

		<title>新城e家 - 管理后台</title>
		
		<base href="<?=url('admin')?>/">

		<!-- Bootstrap Core CSS -->
		<link href="<?=url('packages')?>/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">

		<!-- MetisMenu CSS -->
		<link href="<?=url('packages')?>/metisMenu/dist/metisMenu.min.css" rel="stylesheet">

		<!-- Custom CSS -->
		<link href="<?=url('css')?>/sb-admin-2.css" rel="stylesheet">

		<!-- Custom Fonts -->
		<link href="<?=url('packages')?>/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
		
		<script type="text/javascript" src="<?=url('packages')?>/angular/angular.js"></script>
		<script type="text/javascript" src="<?=url('packages')?>/angular-route/angular-route.min.js"></script>
		<script type="text/javascript" src="<?=url('packages')?>/angular-resource/angular-resource.min.js"></script>
		<script type="text/javascript" src="<?=url('packages')?>/angular-bootstrap/ui-bootstrap-tpls.min.js"></script>
		<!--[if lt IE 9]><script type="text/javascript" src="<?=url('packages')?>/ng-file-upload/ng-file-upload-shim.min.js"></script><![endif]-->
		<script type="text/javascript" src="<?=url('packages')?>/ng-file-upload/ng-file-upload.min.js"></script>
		
		<script type="text/javascript" src="<?=url('app')?>/services.js"></script>
		<script type="text/javascript" src="<?=url('app')?>/app.js"></script>

	</head>

	<body>

		<div id="wrapper">

			<!-- Navigation -->
			<nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">导航</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<span class="navbar-brand">新城e家 - 管理后台</span>
				</div>
				<!-- /.navbar-header -->

				<ul class="nav navbar-top-links navbar-right">
					<li class="dropdown" dropdown>
						<a href="" class="dropdown-toggle" dropdown-toggle>
							<i class="fa fa-user fa-fw"></i> <?=app()->user->name?> <i class="fa fa-caret-down"></i>
						</a>
						<ul class="dropdown-menu dropdown-user" dropdown-menu>
							<li><a href="<?=url('logout')?>"><i class="fa fa-sign-out fa-fw"></i> 登出</a>
							</li>
						</ul>
						<!-- /.dropdown-user -->
					</li>
					<!-- /.dropdown -->
				</ul>
				<!-- /.navbar-top-links -->

				<div class="navbar-default sidebar" role="navigation">
					<div class="sidebar-nav navbar-collapse">
						<ul class="nav" id="side-menu">
							<li class="sidebar-search">
								<div class="input-group custom-search-form">
									<input type="text" class="form-control" placeholder="搜索...">
									<span class="input-group-btn">
										<button class="btn btn-default" type="button">
											<i class="fa fa-search"></i>
										</button>
									</span>
								</div>
								<!-- /input-group -->
							</li>
							<li>
								<a href="post?type=公告"><i class="fa fa-volume-off fa-fw"></i> 公告</a>
							</li>
							<li>
								<a href="post?type=文章"><i class="fa fa-edit fa-fw"></i> 文章</a>
							</li>
							<li>
								<a href="post?type=课堂"><i class="fa fa-book fa-fw"></i> 课堂</a>
							</li>
							<li>
								<a href="post?type=活动"><i class="fa fa-calendar fa-fw"></i> 活动</a>
							</li>
							<li>
								<a href="post?type=图片"><i class="fa fa-image fa-fw"></i> 图片</a>
							</li>
							<li>
								<a href="post?type=视频"><i class="fa fa-video-camera fa-fw"></i> 视频</a>
							</li>
							<li>
								<a href="post?type=附件"><i class="fa fa-paperclip fa-fw"></i> 附件</a>
							</li>
							<li>
								<a href="post?type=服务"><i class="fa fa-glass fa-fw"></i> 服务</a>
							</li>
							<li>
								<a href="post?type=横幅"><i class="fa fa-bookmark fa-fw"></i> 横幅</a>
							</li>
							<li>
								<a href="group"><i class="fa fa-sitemap fa-fw"></i> 群组</a>
							</li>
							<li>
								<a href="user"><i class="fa fa-user fa-fw"></i> 用户</a>
							</li>
						</ul>
					</div>
					<!-- /.sidebar-collapse -->
				</div>
				<!-- /.navbar-static-side -->
			</nav>

			<div id="page-wrapper" ng-view></div>

		</div>
		<!-- /#wrapper -->

		<!-- jQuery -->
		<script src="<?=url('packages')?>/jquery/dist/jquery.min.js"></script>

		<!-- Metis Menu Plugin JavaScript -->
		<script src="<?=url('packages')?>/metisMenu/dist/metisMenu.min.js"></script>

		<!-- Custom Theme JavaScript -->
		<script src="<?=url('js')?>/sb-admin-2.js"></script>

		<div class="alert-container" ng-controller="AlertCtrl">
			<alert ng-repeat="alert in alerts" type="{{alert.type}}" ng-mouseenter="toggleCloseButton($index)" ng-mouseleave="toggleCloseButton($index)">
				<button ng-show="alert.closeable" type="button" class="close" ng-click="close(alert.id)">
					<span aria-hidden="true">×</span>
					<span class="sr-only">Close</span>
				</button>
				{{alert.msg}}
			</alert>
		</div>
		
	</body>

</html>
