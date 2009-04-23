<?php
/**
 * Handler class in /BazeZF/Error
 *
 * @category   BazeZF_Core
 * @package    BazeZF
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thétiot (hthetiot)
 */

class BaseZF_Error_Debugger extends BaseZF_Error_Debugger_Abstract
{
    protected function _render()
    {

    }

    static public function printExceptionSourceDetails(BaseZF_Error_Exception $e)
    {
    	echo highlight_string($e->getSource());
    }

    static public function printExceptionContext(BaseZF_Error_Exception $e)
    {
    	echo highlight_string($e->getContext);
    }


    static public function debugException(Exception $e)
    {
        ?>
		<style>
		pre.debug {
			max-height: 100px;
			overflow: auto;
			background: grey;
			padding: 5px;
		}
		</style>
        <h1>An error occurred</h1>
        <h2><?php echo $e->getMessage(); ?></h2>

        <h3>Exception information: </h3>

		<table>
			<tr>
				<th>Name:</th>
				<td><?php echo get_class($e); ?></td>
			</tr>
			<tr>
				<th>Code:</th>
				<td><?php echo $e->getCode(); ?></td>
			</tr>
			<tr>
				<th>Source:</th>
				<td><?php echo $e->getFile(); ?> Line <?php echo $e->getLine() ?></td>
			</tr>
		</table>

		<div>
			<h3>Debugger:</h3>
			<pre class="debug"><?php
				if ($e instanceof BaseZF_Error_Exception) {
					self::printExceptionSourceDetails($e);
				} else {
					echo 'Unable to use debuger.';
				}
			?></pre>
		</div>

		<div>
			<h3>Context Variables Values:</h3>
			<pre class="debug"><?php
				if ($e instanceof BaseZF_Error_Exception) {
					self::printExceptionContext($e);
				} else {
					echo 'Unable to use debuger.';
				}
			?></pre>
		</div>

		<div>
        	<h3>Stack trace:</h3>
			<pre class="debug"><?php echo $e->getTraceAsString() ?></pre>
		</div>

		<div>
        	<h3>Server Parameters:</h3>
			<pre class="debug"><?php echo var_dump($_SERVER); ?></pre>
		</div>

		<div>
			<h3>POST Parameters:</h3>
			<pre class="debug"><?php echo var_dump($_POST); ?></pre>
		</div>

		<div>
			<h3>GET Parameters:</h3>
			<pre class="debug"><?php echo var_dump($_GET); ?></pre>
		</div>

		<div>
			<h3>SESSION Parameters:</h3>
			<pre class="debug"><?php echo isset($_SESSION) ?  var_dump($_SESSION) : 'No Session initialized.'; ?></pre>
		</div>
        <?php

        exit();
    }
	*/
