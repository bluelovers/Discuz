<?php

/**
 *      [品牌空間] (C)2001-2010 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: batch.formhash.php 3779 2010-07-16 08:47:54Z yexinhao $
 */

include_once('./common.php');

$formhash = formhash();
echo <<<END
document.write('<input type="hidden" name="formhash" value="$formhash" />');
END;
?>