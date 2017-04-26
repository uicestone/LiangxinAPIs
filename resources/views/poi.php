<!DOCTYPE html>
<html ng-app="liangxin-poi">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta name="format-detection" content="telephone=no">
		<title>嘉定新城（马陆镇）党建地图</title>
		
		<base href="<?=url('pois')?>/">

		<!-- Google font -->
		<!-- <link href='http://fonts.useso.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'> -->
		<!-- <link href='http://fonts.useso.com/css?family=Raleway:300,400,700,900' rel='stylesheet' type='text/css'> -->
		<!-- Css -->
		<link rel="stylesheet" type="text/css" href="<?=resource_url(elixir('assets/css/poi.css'))?>">
		<script type="text/javascript" src="<?=resource_url(elixir('assets/js/poi.js'))?>"></script>

		<script type="text/javascript">
			var user = <?=json_encode($user)?>;
			var token = '<?=$token?>';
			var userAgent = '<?=app()->user_agent?>';
			
			if(user && localStorage) {
				localStorage.setItem('user', '<?=$user?>');
				localStorage.setItem('token', '<?=$token?>');
			}

		</script>

	</head>

	<body>
		<div class="alert-container ng-cloak" ng-controller="AlertController">
			<alert ng-repeat="alert in alerts" type="{{alert.type}}" ng-mouseenter="toggleCloseButton($index)" ng-mouseleave="toggleCloseButton($index)">
				<button ng-show="alert.closeable" type="button" class="close" ng-click="close(alert.id)">
					<span aria-hidden="true">×</span>
					<span class="sr-only">Close</span>
				</button>
				{{alert.msg}}
			</alert>
		</div>
		<div ng-view></div>
	</body>

</html>
