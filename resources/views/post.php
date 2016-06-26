<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?=$post->title?></title>
	<meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
	<meta name="format-detection" content="telephone=no">
	<style>
		body{
			margin:0;
			padding: <?=$display === 'full-content' ? '0' : '24px'?>;
		}
		.title{
			font-size: 15px;
			margin-bottom: 5px;
		}
		.desc{
			font-size: 8px;
		}
		header{
			margin-bottom: 32px;
		}
		section{
			line-height: 1.5;
			text-indent: 2em;
			font-size: 10px
		}

		section p{
			margin: 0 0 10px;
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
	<?php if($display !== 'full-content'): ?>
	<header>
		<div class="title"><?=$post->title?></div>
	</header>
	<hr />
	<?php endif; ?>
	<section>
		<?php foreach($post->images as $image){ ?>
		<img src="<?=$image->url?>">
		<?php } ?>
		<p><?=nl2br($post->content)?></p>
	</section>
</body>
</html>