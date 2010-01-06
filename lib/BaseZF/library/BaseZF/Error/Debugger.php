<?php
/**
 * Handler class in /BazeZF/Error
 *
 * @category  BazeZF
 * @package   BaseZF_Error
 * @author    Harold Thetiot <hthetiot@gmail.com>
 * @copyright 2006-2009 The Authors
 * @license   http://github.com/hthetiot/basezf/blob/master/lib/BaseZF/COPYING Custom License
 * @link      http://github.com/hthetiot/basezf
 */

class BaseZF_Error_Debugger extends BaseZF_Error_Debugger_Abstract
{
    protected function _render()
    {
        // Server error
        header('HTTP/1.1 500 Internal Server Error');

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
<title>An error occurred</title>
<meta name="robots" content="noindex,follow,nocache,noarchive" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-Language" content="en" />
<style>
    pre.debug {
        max-height: 100px;
        overflow: auto;
        background: #CCC;
        padding: 5px;
    }
</style>
</head>
    <body>
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
                    echo $source;
                } else {
                    echo 'Unable to get Debugger.';
                }

            ?></pre>
        </div>

        <div>
            <h3>Context Variables Values:</h3>
            <pre class="debug"><?php

                if ($context = $this->getExceptionContext()) {
                    var_dump($context);
                } else {
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
            <pre class="debug"><?php echo var_dump($this->getServerParams()); ?></pre>
        </div>

        <div>
            <h3>POST Parameters:</h3>
            <pre class="debug"><?php echo var_dump($this->getPostParams()); ?></pre>
        </div>

        <div>
            <h3>GET Parameters:</h3>
            <pre class="debug"><?php echo var_dump($this->getGetParams()); ?></pre>
        </div>

        <div>
            <h3>COOKIES Parameters:</h3>
            <pre class="debug"><?php

                if ($cookies = $this->getCookiesParams()) {
                    var_dump($cookies);
                } else {
                    echo 'No Cookies initialized or empty.';
                }
            ?>
            </pre>
        </div>

        <div>
            <h3>SESSION Parameters:</h3>
            <pre class="debug"><?php

                if ($session = $this->getSessionParams()) {
                    var_dump($session);
                } else {
                    echo 'No Session initialized or empty.';
                }
            ?>
            </pre>
        </div>
    </body>
</html>
<?php

    }
}
