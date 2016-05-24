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
		<link rel="stylesheet" type="text/css" href="<?=url('packages/bootstrap/dist/css/bootstrap.min.css')?>">
		<link rel="stylesheet" type="text/css" href="<?=url('packages/font-awesome/css/font-awesome.min.css')?>">
		<link rel="stylesheet" type="text/css" href="<?=url('css/md-font.css')?>">
		<link rel="stylesheet" type="text/css" href="<?=url('css/quiz.css')?>">

		<script type="text/javascript" src="<?=url('packages/angular/angular.js')?>"></script>
		<script type="text/javascript" src="<?=url('packages/angular-route/angular-route.min.js')?>"></script>
		<script type="text/javascript" src="<?=url('packages/angular-resource/angular-resource.min.js')?>"></script>
		<script type="text/javascript" src="<?=url('packages/angular-bootstrap/ui-bootstrap-tpls.min.js')?>"></script>
		<script type="text/javascript" src="<?=url('packages/ng-file-upload/ng-file-upload.min.js')?>"></script>
		
		<script type="text/javascript" src="<?=url('quiz/services.js')?>"></script>
		<script type="text/javascript" src="<?=url('quiz/app.js')?>"></script>

		<script type="text/javascript">
			var user = <?=json_encode($user)?>;

			if(user) {
				localStorage.setItem('user', '<?=$user?>');
				localStorage.setItem('token', '<?=$token?>');
			}

		</script>

	</head>

	<body ng-view></body>

</html>
