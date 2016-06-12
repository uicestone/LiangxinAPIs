<!DOCTYPE html>
<html ng-app="liangxin-quiz">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<meta name="format-detection" content="telephone=no">
		<title>新城党建 达人挑战赛</title>
		
		<base href="<?=url('quizzes')?>/">

		<!-- Google font -->
		<link href='http://fonts.useso.com/css?family=Lato:300,400,700' rel='stylesheet' type='text/css'>
		<link href='http://fonts.useso.com/css?family=Raleway:300,400,700,900' rel='stylesheet' type='text/css'>
		<!-- Css -->
		<link rel="stylesheet" type="text/css" href="<?=resource_url(elixir('assets/css/quiz.css'))?>">
		<script type="text/javascript" src="<?=resource_url(elixir('assets/js/quiz.js'))?>"></script>

		<script type="text/javascript">
			var user = <?=json_encode($user)?>;
			var token = '<?=$token?>';
			var round = Number(<?=$round?>);
			var userAgent = '<?=app()->user_agent?>';
			
			if(user.id == 1) {
				window.onerror = function(err){alert(JSON.stringify(err))};
			}
			
			if(user && localStorage) {
				localStorage.setItem('user', '<?=$user?>');
				localStorage.setItem('token', '<?=$token?>');
			}

		</script>

	</head>

	<body>
		<div class="alert-container" ng-controller="AlertController">
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
