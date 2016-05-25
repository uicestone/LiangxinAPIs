<?php

function wholeurlencode($url)
{
	return preg_replace_callback("{[^0-9a-z_.!~*'();,/?:@&=+$#-]}i", function ($m) {
		return sprintf('%%%02X', ord($m[0]));
	}, $url);
}

function resource_url($path)
{
	if(env('APP_ENV') === 'local' || !env('CDN_PREFIX'))
	{
		return url($path);
	}
	else
	{
		return (env('CDN_PREFIX_SSL') ? env('CDN_PREFIX_SSL') : env('CDN_PREFIX')) . preg_replace('/^\//', '', $path);
	}
}

?>
