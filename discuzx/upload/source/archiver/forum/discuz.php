<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
include loadarchiver('common/header');
?>
<div id="nav">
	<a href="forum.php?archiver=1"><strong><?php echo $_G['setting']['bbname']; ?></strong></a>
</div>
<div id="content">
	<?php foreach($catlist as $key => $cat): ?>
	<h3><?php echo $cat['name']; ?></h3>
	<?php if(!empty($cat['forums'])): ?>
	<ul>
		<?php foreach($cat['forums'] as $fid): ?>
		<li><a href="forum.php?mod=forumdisplay&fid=<?php echo $fid; ?>&archiver=1"><?php echo $forumlist[$fid]['name']; ?></a></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
	<?php endforeach; ?>
</div>
<div id="footer">
	<?php echo lang('forum/archiver', 'full_version'); ?>:
	<a href="forum.php" target="_blank"><strong><?php echo $_G['setting']['bbname']; ?></strong></a>
</div>
<?php include loadarchiver('common/footer'); ?>