<?php
/**
 * rounded.php in /public/tools
 *
 * @category  BazeZF
 * @package   BazeZF_Service
 * @copyright Copyright (c) 2008 BazeZF
 * @author    Harold Thetiot (hthetiot)
 *
 * Script arguments:
 * Corner Name :              cn (one of: tl, bl, tr, br)
 * Height      :              h (height for elliptical arc)
 * Width       :              w (width for elliptical arc)
 * Size        :              s (alternative parameter for circular arc)
 * Border      :              b (thickness of optional border line)
 * Border color:              cb (ignored unless b!=0)
 * Inner color :              ci
 * Outer color :              co
 *
 * Usage:
 * rounded.php?cn=tl&s=10&ci=ffffff&co=000077 (top left 10x10 image, white inside, light blue outside)
 * rounded.php?cn=tr&h=20&w=20&ci=ffffff&co=000077 (top right 10x20 elliptical corner image, white inside, light blue outside)
 *
 */

$myCorner = BaseZF_Service_CornerGenerator::factoryFromRequest();
$myCorner->display();


