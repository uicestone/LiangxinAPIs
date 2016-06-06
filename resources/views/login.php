<!DOCTYPE html>
<html lang="en">

	<head>

		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="">

		<title>登录 - 新城e家 - 管理后台</title>

		<link rel="stylesheet" type="text/css" href="<?=resource_url(elixir('assets/css/admin.css'))?>">
		<script type="text/javascript" src="<?=resource_url(elixir('assets/js/admin.js'))?>"></script>

	</head>

	<body>

		<div class="container">
			<div class="row">
				<div class="col-md-4 col-md-offset-4">
					<div class="login-panel panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title">请登录</h3>
						</div>
						<div class="panel-body">
							<form role="form" method="post">
								<fieldset>
									<div class="form-group">
										<input class="form-control" placeholder="用户名" name="username" type="text" autofocus>
									</div>
									<div class="form-group">
										<input class="form-control" placeholder="密码" name="password" type="password" value="">
									</div>
									<div class="checkbox">
										<label>
											<input name="remember" type="checkbox" value="Remember Me">记住
										</label>
									</div>
									<!-- Change this to a button or input when using this as a form -->
									<button type="submit" class="btn btn-lg btn-success btn-block">登录</button>
								</fieldset>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>

	</body>

</html>
