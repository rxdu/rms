<?php
/**
 * A basic interface to display 1 or more MJPEG streams and basic keyboard
 * teleoperation control.
 *
 * @author     Russell Toris <rctoris@wpi.edu>
 * @copyright  2013 Russell Toris, Worcester Polytechnic Institute
 * @license    BSD -- see LICENSE file
 * @version    April, 15 2013
 * @package    api.robot_environments.interfaces.basic
 * @link       http://ros.org/wiki/rms
 */

/**
 * A static class to contain the interface generate function.
 *
 * @author     Russell Toris <rctoris@wpi.edu>
 * @copyright  2013 Russell Toris, Worcester Polytechnic Institute
 * @license    BSD -- see LICENSE file
 * @version    April, 15 2013
 * @package    api.robot_environments.interfaces.basic
 */
#class basic
class fall_risk
{
    /**
     * Generate the HTML for the interface. All HTML is echoed.
     * @param robot_environment $re The associated robot_environment object for
     *     this interface
     */
    static function generate($re)
    {
        // lets begin by checking if we have an MJPEG keyboard at the very least
        if (!$streams = $re->get_widgets_by_name('MJPEG Stream')) {
            robot_environments::create_error_page(
                'No MJPEG streams found.', 
                $re->get_user_account()
            );
        } else if (!$teleop = $re->get_widgets_by_name('Keyboard Teleop')) {
            robot_environments::create_error_page(
                'No Keyboard Teloperation settings found.', 
                $re->get_user_account()
            );
        } else if (!$nav = $re->get_widgets_by_name('2D Navigation')) {
            robot_environments::create_error_page(
                'No 2D Navaigation settings found.',
                $re->get_user_account()
            );
        } else if (!$re->authorized()) {
            robot_environments::create_error_page(
                'Invalid experiment for the current user.', 
                $re->get_user_account()
            );
        } else { 
            // lets create a string array of MJPEG streams
            $topics = '[';
            $labels = '[';
            foreach ($streams as $s) {
                $topics .= "'".$s['topic']."', ";
                $labels .= "'".$s['label']."', ";
            }
            $topics = substr($topics, 0, strlen($topics) - 2).']';
            $labels = substr($labels, 0, strlen($topics) - 2).']';

            // we will also need the map
            $widget = widgets::get_widget_by_table('maps');
            $map = widgets::get_widget_instance_by_widgetid_and_id(
                $widget['widgetid'], $nav[0]['mapid']
            );


            // here we can spit out the HTML for our interface ?>
<!doctype html>
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!-->
<html lang="en">
<!--<![endif]-->
<head>
<meta charset="utf-8">
<link href="../api/robot_environments/interfaces/fall_risk/style.css" rel="stylesheet" type="text/css">
<link rel="stylesheet" type="text/css" href="../css/jquery-ui-1.8.22.custom.css" />
<script type="text/javascript" src="../js/rms/common.js"></script>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="http://code.jquery.com/ui/1.8.22/jquery-ui.min.js"></script>
<script type="text/javascript" src="../js/rms/study.js"></script> 
<!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
  <![endif]-->
<!--<?php $re->create_head() // grab the header information ?> -->
<script type="text/javascript"
    src="http://cdn.robotwebtools.org/EventEmitter2/0.4.11/eventemitter2.js">
</script>
<script type="text/javascript"
    src="http://cdn.robotwebtools.org/roslibjs/r5/roslib.min.js"></script>
<script type="text/javascript"
    src="http://cdn.robotwebtools.org/mjpegcanvasjs/r1/mjpegcanvas.min.js">
</script>
<script type="text/javascript"
  src="http://cdn.robotwebtools.org/keyboardteleopjs/r1/keyboardteleop.min.js">
</script>
<script type="text/javascript" src="http://cdn.robotwebtools.org/EaselJS/current/easeljs.min.js"></script>
<script type="text/javascript" src="http://cdn.robotwebtools.org/ros2djs/current/ros2d.min.js"></script>
<script type="text/javascript" src="http://cdn.robotwebtools.org/nav2djs/current/nav2d.min.js"></script>


<title>Basic Teleop Interface</title>
<script type="text/javascript">
  //connect to ROS
  var ros = new ROSLIB.Ros({
      url : '<?php echo $re->rosbridge_url()?>'
  });
  
  ros.on('error', function() {
        alert('Lost communication with ROS.');
    });

  /**
   * Load everything on start.
   */
  
function start() {

  
//Initializing viewer for 2D map 
var mapViewer = new ROS2D.Viewer({
      divID : 'nav2d',
      width : 650,
      height : 475
    });

    // Setup the nav client.
     var nav = NAV2D.OccupancyGridClientNav({
      ros : ros,
      rootObject : mapViewer.scene,
      viewer : mapViewer,
      serverName : '<?php echo $nav[0]['actionserver']?>',
      actionName : '<?php echo $nav[0]['action']?>',
      topic : '<?php echo $map['topic']?>',
      continuous : '<?php echo ($map['continuous'] === 0) ? 'true' : 'false'?>'
  });

	//Shift the canvas to display entire map
	mapViewer.shift(0,18.65);

  } 
</script>
</head>

<body onload="start();">
<div class="container">
  <header class="sixteen columns">
   <table style="width:1050px;"><tr> <td style="vertical-align:middle;"><div id="logo" >
      <h1>In-home environment screening for Fall Risk</h1>
      <h2>using turtlebot</h2>
    </div></td><td style="vertical-align:middle;width:150px">
    <img src="../img/logoModified.png" width="170" height="73" alt=""/>
</td></tr></table>
    <hr/>
  </header>

    <div id="overview" class="sixteen columns">
    <table>
      <tr><td><h4 align="center">2D Navigation Map</h4>
<div id="nav2d" align=center> </div>       
</td>
        </tr>
    </table>
  </div>

  <!-- Footer begins ========================================================================== -->
  <footer class="sixteen columns">
    <hr />
    <ul id="footerLinks">
      <li>&copy; 2014 <a href="http://robot.wpi.edu/">RIVeR Lab</a>, WPI</li>
      <li>Powered by <a href="http://www.ros.org/wiki/rms/">Robot Management System</a></li>
    </ul>
  </footer>
</div>
</body>
</html>
<?php
        }
    }
}
