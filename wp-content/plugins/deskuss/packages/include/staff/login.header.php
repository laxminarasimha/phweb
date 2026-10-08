<?php
defined('DSKADMININC') or die('Invalid path');
header("X-Frame-Options: SAMEORIGIN");

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="refresh" content="7200" />
    <title>Deskuss - <?php echo __('Agent Login'); ?></title>
	<link href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" rel="stylesheet" type="text/css">
    <!--<link type="text/css" rel="stylesheet" href="<?php echo DESKUSS_ROOT_PATH; ?>assets/css/font-awesome.min.css?035fd0a"/>-->
    <meta name="robots" content="noindex" />
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript" src="<?php echo includes_url('js/jquery/jquery.min.js'); ?>"></script>
    <script type="text/javascript" src="<?php echo includes_url('js/jquery/jquery-migrate.min.js'); ?>"></script>
    <script type="text/javascript">var $ = jQuery;</script>
	<link rel="stylesheet" href="<?php echo DESKUSS_MEDIA_URL ?>/css/bootstrap.min.css?<?php echo esc_attr(THIS_VERSION);?>" class="px-demo-stylesheet-core" media="all"/>
	<link rel="stylesheet" href="<?php echo esc_url(DESKUSS_MEDIA_URL); ?>/admin/css/login.css?<?php echo esc_attr(THIS_VERSION);?>" type="text/css" />

    <script type="text/javascript">
        $(document).ready(function() {
            $("input:not(.dp):visible:enabled:first").focus();
         });
    </script>
	
</head>
<body id="loginBody">

<?php

if($dsk->getConfig()->getStaffLoginBackdrop()){
	echo '<div id="brickwall"></div>';
}else{
	echo '
<canvas id="world" style="position: absolute;"></canvas>
<script type="text/javascript" src="'.DESKUSS_MEDIA_URL.'/js/bootstrap.min.js?'.esc_attr(THIS_VERSION).'"></script>
<script type="text/javascript">
var SCREEN_WIDTH = window.innerWidth;
var height2 = window.innerHeight;
var element = document.getElementById("loginBody");
var height1 = element.offsetHeight;
if(height1 > height2){
	var SCREEN_HEIGHT = height1;
}else{
	var SCREEN_HEIGHT = height2;
}

var RADIUS = 70;

var RADIUS_SCALE = 1;
var RADIUS_SCALE_MIN = 1;
var RADIUS_SCALE_MAX = 1.5;

var QUANTITY = 	8;

var canvas;
var context;
var particles;

var mouseX = SCREEN_WIDTH * 0.5;
var mouseY = SCREEN_HEIGHT * 0.5;
var mouseIsDown = false;

function deskuss_init() {

  canvas = document.getElementById( "world" );
  
  if (canvas && canvas.getContext) {
		context = canvas.getContext("2d");
		
		// Register event listeners
		window.addEventListener("mousemove", documentMouseMoveHandler, false);
		window.addEventListener("mousedown", documentMouseDownHandler, false);
		window.addEventListener("mouseup", documentMouseUpHandler, false);
		document.addEventListener("touchstart", documentTouchStartHandler, false);
		document.addEventListener("touchmove", documentTouchMoveHandler, false);
		window.addEventListener("resize", windowResizeHandler, false);
		
		createParticles();
		
		windowResizeHandler();
		
		setInterval( loop, 1000 / 60 );
	}
}

function createParticles() {
	particles = [];
	var use_colors = ["#20336e","#406a1b","#e86d30","#992929","#20336e","#406a1b","#e86d30","#992929"];
	var use_colors = ["#F17613","#224F78","#406a1b","#FFFFFF","#F17613","#224F78","#406a1b","#FFFFFF"];
	
	for (var i = 0; i < QUANTITY; i++) {
		var particle = {
			size: 1,
			position: { x: mouseX, y: mouseY },
			offset: { x: 0, y: 0 },
			shift: { x: mouseX, y: mouseY },
			speed: 0.01+Math.random()*0.04,
			targetSize: 1,
			fillColor: use_colors[i],
			orbit: RADIUS*.5 + (RADIUS * .5 * Math.random())
		};
		
		particles.push( particle );
	}
}

function documentMouseMoveHandler(event) {
	mouseX = event.clientX - (window.innerWidth - SCREEN_WIDTH) * .5;
	mouseY = event.clientY - (window.innerHeight - SCREEN_HEIGHT) * .5;
}

function documentMouseDownHandler(event) {
	mouseIsDown = true;
}

function documentMouseUpHandler(event) {
	mouseIsDown = false;
}

function documentTouchStartHandler(event) {
	if(event.touches.length == 1) {
		event.preventDefault();

		mouseX = event.touches[0].pageX - (window.innerWidth - SCREEN_WIDTH) * .5;;
		mouseY = event.touches[0].pageY - (window.innerHeight - SCREEN_HEIGHT) * .5;
	}
}

function documentTouchMoveHandler(event) {
	if(event.touches.length == 1) {
		event.preventDefault();

		mouseX = event.touches[0].pageX - (window.innerWidth - SCREEN_WIDTH) * .5;;
		mouseY = event.touches[0].pageY - (window.innerHeight - SCREEN_HEIGHT) * .5;
	}
}

function windowResizeHandler() {
	SCREEN_WIDTH = window.innerWidth;
	height2 = window.innerHeight;
	 element = document.getElementById("loginBody");
	 height1 = element.offsetHeight;
	if(height1 > height2){
		 SCREEN_HEIGHT = height1;
	}else{
		 SCREEN_HEIGHT = height2;
	}
	canvas.width = SCREEN_WIDTH;
	canvas.height = SCREEN_HEIGHT;
}

function loop() {
	
	if( mouseIsDown ) {
		RADIUS_SCALE += ( RADIUS_SCALE_MAX - RADIUS_SCALE ) * (0.02);
	}
	else {
		RADIUS_SCALE -= ( RADIUS_SCALE - RADIUS_SCALE_MIN ) * (0.02);
	}
	
	RADIUS_SCALE = Math.min( RADIUS_SCALE, RADIUS_SCALE_MAX );
	
	context.fillStyle = "rgba(30,30,30,0.05)";
	context.fillRect(0, 0, context.canvas.width, context.canvas.height);
	
	for (i = 0, len = particles.length; i < len; i++) {
		var particle = particles[i];
		
		var lp = { x: particle.position.x, y: particle.position.y };
		
		// Rotation
		particle.offset.x += particle.speed;
		particle.offset.y += particle.speed;
		
		// Follow mouse with some lag
		particle.shift.x += ( mouseX - particle.shift.x) * (particle.speed);
		particle.shift.y += ( mouseY - particle.shift.y) * (particle.speed);
		
		// Apply position
		particle.position.x = particle.shift.x + Math.cos(i + particle.offset.x) * (particle.orbit*RADIUS_SCALE);
		particle.position.y = particle.shift.y + Math.sin(i + particle.offset.y) * (particle.orbit*RADIUS_SCALE);
		
		// Limit to screen bounds
		particle.position.x = Math.max( Math.min( particle.position.x, SCREEN_WIDTH ), 0 );
		particle.position.y = Math.max( Math.min( particle.position.y, SCREEN_HEIGHT ), 0 );
		
		particle.size += ( particle.targetSize - particle.size ) * 0.05;
		
		if( Math.round( particle.size ) == Math.round( particle.targetSize ) ) {
			particle.targetSize = 1 + Math.random() * 7;
		}
		
		context.beginPath();
		context.fillStyle = particle.fillColor;
		context.strokeStyle = particle.fillColor;
		context.lineWidth = particle.size;
		context.moveTo(lp.x, lp.y);
		context.lineTo(particle.position.x, particle.position.y);
		context.stroke();
		context.arc(particle.position.x, particle.position.y, particle.size/2, 0, Math.PI*2, true);
		context.fill();
	}
}

window.onload = init;
</script>';
}

?>