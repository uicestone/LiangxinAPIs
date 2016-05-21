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

		section p{
			margin: 10px 0;
			font-size: 20px;
			word-wrap: break-word;
		}
		
		.title{
			font-weight: bold;
			font-size: 24px;
		}

		img{
			display: block;
			max-width: 100%;
		}
	</style>
</head>
<body>
	<header>
		<div class="title"><?=$post->title?></div>
	</header>
	<hr />
	<section>
		<?php foreach($post->images as $image){ ?>
		<img src="<?=$image->url?>">
		<?php } ?>
		<p><?=nl2br($post->content)?></p>
	</section>
</body>
</html>