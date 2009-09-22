<?php
    session_start();
?>
<h2>Server</h2>
<pre>
<?php var_dump($_SERVER); ?>
</pre>

<h2>Session</h2>
<pre>
<?php var_dump($_SESSION); ?>
</pre>

<h2>Cookies</h2>
<pre>
<?php var_dump($_COOKIE); ?>
</pre>
