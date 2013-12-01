<?php

?>
<!DOCTYPE html>
<html>
<head>
	<?php echo $this->Html->charset(); ?>
	<title>Nerve</title>
	<?php
		// echo $this->Html->meta('icon');
		
		// echo $this->Html->css('cake.generic');
		echo $this->Html->css('styles');
        
        echo $this->Html->script(array(
            'jquery.min.js',
            'arbor.js',
        ));
        
		echo $this->fetch('meta');
		echo $this->fetch('css');
		echo $this->fetch('script');
	?>
	<link href='http://fonts.googleapis.com/css?family=Roboto+Slab' rel='stylesheet' type='text/css'>
</head>
<body>
	<div id="container">
		<!-- <div id="header">
			<h1>Header</h1>
		</div> -->
		<?php echo $this->Session->flash(); ?>
        <?php echo $this->element('leftbar') ?>
        <?php echo $this->element('rightbar') ?>
		<div id="content">
		    <div id="content-inner">
		        <?php echo $this->fetch('content'); ?>
		    </div>
		</div>
		<!-- <div id="footer"></div> -->
	</div>
	<?php // echo $this->element('sql_dump'); ?>
</body>
</html>
