<?php

// dynamic genreation of style

require_once(dirname(__FILE__).'/../../../../wp-load.php');
require_once('../wps3.php');
$s3 = new wps3();
header('Content-type: text/css');

?>
/** Dynamic CSS Loader **/
<?php foreach($s3->galleries() as $gallery) : ?>
#s3slider-<?php echo $gallery->id; ?> {
   width: <?php echo $gallery->width; ?>px; /* important to be same as image width */
   height: <?php echo $gallery->height; ?>px; /* important to be same as image height */
   position: relative; /* important */
   overflow: hidden; /* important */
}

#s3slider-<?php echo $gallery->id; ?>Content {
   width: <?php echo $gallery->width; ?>px;
   position: absolute;
   top: 0;
   margin-left: 0;
}

.s3slider-<?php echo $gallery->id; ?>Image span {
   position: absolute; /* important */
   left: 0;
   font: 10px/15px Arial, Helvetica, sans-serif;
   padding: 10px 13px;
   width: <?php echo (int)$gallery->width - 26; ?>px;
   background-color: <?php echo $gallery->overlay_colour; ?>;
   filter: alpha(opacity=<?php echo round($gallery->opacity*100); ?>); /* here you can set the opacity of box with text */
   -moz-opacity: <?php echo number_format($gallery->opacity,1); ?>; /* here you can set the opacity of box with text */
   -khtml-opacity: <?php echo number_format($gallery->opacity,1); ?>; /* here you can set the opacity of box with text */
   opacity: <?php echo number_format($gallery->opacity,1); ?>; /* here you can set the opacity of box with text */
   color: <?php echo $gallery->text_colour; ?>;
   display: none; /* important */
}
.s3slider-<?php echo $gallery->id; ?>Image span.left {
   top:0;
   left:0;
   width:<?php echo round(0.25*$gallery->width); ?>px;
   height:<?php echo $gallery->height; ?>px;
}
.s3slider-<?php echo $gallery->id; ?>Image span.right {
   top:0;
   left:<?php echo ($gallery->width - round(0.25*$gallery->width)) - 26; ?>px;
   width:<?php echo round(0.25*$gallery->width); ?>px;
   height:<?php echo $gallery->height; ?>px;
}

.s3slider-<?php echo $gallery->id; ?>Image {float: left;position: relative;display: none;}

<?php endforeach; ?>


ul.nostyle, ul.nostyle li { margin:0; padding:0; list-style:none; }
.clear { clear: both; } 
.hidden { display:none; }
.top { top: 0; left: 0; }
.bottom { bottom: 0; }