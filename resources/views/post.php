<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?=$post->title?></title>
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta name="format-detection" content="telephone=no">
	<style>
		body{
			padding: 24px;
		}
		.title{
			font-size: 15px;
			margin-bottom: 5px;
		}
		.desc{
			font-size: 8px;
		}
		section{
			margin-top: 32px;
			line-height: 1.5;
			text-indent: 2em;
			font-size: 10px
		}
	</style>
</head>
<body>
	<head>
		<div class="title"><?=$post->title?></div>
		<div class="desc"><?=$post->updated_at?> <?=$post->author->name?></div>
	</head>
	<section>
		<?=$post->content?>
	</section>
</body>
</html>