<?php

$cachearray = array('pdnovelcategory');
foreach($cachearray as $cachename) {
	pdnovelcache($cachename, 'pdnovel');
}
cpmsg('cache_success', '', 'succeed');


?>