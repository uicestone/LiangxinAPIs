<?php

function wholeurlencode($url)
{
	return preg_replace_callback("{[^0-9a-z_.!~*'();,/?:@&=+$#-]}i", function ($m) {
		return sprintf('%%%02X', ord($m[0]));
	}, $url);
}

?>
