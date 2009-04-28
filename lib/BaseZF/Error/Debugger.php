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
        ?>
		<style>
            pre.debug {
                max-height: 100px;
                overflow: auto;
                background: #CCC;
                padding: 5px;
            }
		</style>
        <h1>An error occurred</h1>
        <h2><?php echo $this->_exception->getMessage(); ?></h2>

        <h3>Exception information: </h3>

		<table>
			<tr>
				<th>Name:</th>
				<td><?php echo get_class($this->_exception); ?></td>
			</tr>
			<tr>
				<th>Code:</th>
				<td><?php echo $this->_exception->getCode(); ?></td>
			</tr>
			<tr>
				<th>Source:</th>
				<td><?php echo $this->_exception->getFile(); ?> Line <?php echo $this->_exception->getLine() ?></td>
			</tr>
		</table>

		<div>
			<h3>Debugger:</h3>
			<pre class="debug"><?php

                if ($source = $this->getExceptionSourceDetails()) {
                } else {
                    echo 'Unable to get Debugger.';
                }

            ?></pre>
		</div>

		<div>
			<h3>Context Variables Values:</h3>
			<pre class="debug"><?php

                $context = $this->getExceptionContext();

                if ( $context = $this->getExceptionContext()) {
                    var_dump($context);
                } else{
                    echo 'Unable to get Context Variables Values.';
                }

            ?></pre>
		</div>

		<div>
        	<h3>Stack trace:</h3>
			<pre class="debug"><?php echo $this->_exception->getTraceAsString() ?></pre>
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
			<pre class="debug"><?php

                if(isset($_SESSION)) {
                    var_dump($_SESSION);
                } else {
                    echo 'No Session initialized or empty.';
                }

            ?></pre>
		</div>
        <?php

        exit();
    }
}
