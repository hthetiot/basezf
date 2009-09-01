<?php
/**
 * Mail class in /BazeZF/Service
 *
 * @category   BazeZF
 * @package    BazeZF_Service
 * @copyright  Copyright (c) 2008 BazeZF
 * @author     Harold Thetiot (hthetiot)
 */

class BaseZF_Service_Mail
{
    const DEFAULT_HELO = 'microsoft.com';         // I hate them
    const DEFAULT_FROM = 'support@microsoft.com'; // I hate them too

    /**
     * SMTP Response protocol code value
     */
    const RESPONSE_NON_STANDARD         = 200; // A non-standard response reference RFC876
    const RESPONSE_SYSTEM_STATUS        = 211; // A system status message.
    const RESPONSE_HELP_MESSAGE         = 214; // A help message for a human reader follows.
    const RESPONSE_SERVICE_READY        = 220; // SMTP Service ready.
    const RESPONSE_SERVICE_CLOSED       = 221; // Service closing.
    const RESPONSE_COMPLETED            = 250; // Requested action taken and completed. The best message of them all.
    const RESPONSE_RECIPIENT_NOT_EXIST  = 251; // The recipient is not local to the server, but the server will accept and forward the message.
    const RESPONSE_RECIPIENT_FORWARDED  = 252; // The recipient cannot be VRFYed, but the server accepts the message and attempts delivery.
    const RESPONSE_MSG_NODE_STARTED     = 253; // Messages for "node" have started.

    const RESPONSE_START_MESSAGE        = 354; // Start message input and end with <CRLF>.<CRLF>. This indicates that the server is ready to accept the message itself (after you have told it who it is from and where you want to to go).

    const RESPONSE_SERVICE_UNAVAILABLE  = 421; // The service is not available and the connection will be closed.
    const RESPONSE_PASSWORD_REQUIRE     = 432; // A password transition is needed.
    const RESPONSE_MAILBOX_UNAVAILABLE  = 450; // The requested command failed because the user's mailbox was unavailable (for example because it was locked). Try again later.
    const RESPONSE_SERVER_ERROR         = 451; // The command has been aborted due to a server error. Not your fault. Maybe let the admin know.
    const RESPONSE_SERVER_EXCEEDED      = 452; // The command has been aborted because the server has insufficient system storage.
    const RESPONSE_EMPTY_MAILBOX        = 453; // You have no mail
    const RESPONSE_ENCRYP_UNAVAILABLE   = 454; // TLS not available due to temporary reason: Encryption required.
    const RESPONSE_UNABLE_TO_QUEUE      = 458; // Unable to queue messages for node.
    const RESPONSE_NODE_NOT_ALLOWED     = 459; // Node not allowed: "reason"

    const RESPONSE_SYNTAX_ERROR         = 500; // The server could not recognize the command due to a syntax error.
    const RESPONSE_ARGS_SYNTAX_ERROR    = 501; // A syntax error was encountered in command arguments.
    const RESPONSE_NOT_IMPLEMENTED      = 502; // This command is not implemented.
    const RESPONSE_BAD_SEQUENCE         = 503; // The server has encountered a bad sequence of commands.
    const RESPONSE_ARG_NOT_IMPLEMENTED  = 504; // A command parameter is not implemented.
    const RESPONSE_AUTH_REQUIRED        = 505; // Authentication required.
    const RESPONSE_NOT_ACCEPT_MAIL      = 521; // Machine does not accept mail.
    const RESPONSE_ENCRYP_REQUIRED      = 530; // Must issue STARTTLS command first: Encryption required.
    const RESPONSE_AUTH_TOO_WEAK        = 534; // Authentication mechanism too weak.
    const RESPONSE_AUTH_ENCRYP_REQUIRED = 538; // Encryption required for requested authentication.
    const RESPONSE_COMMAND_FAILED       = 550; // The requested command failed because the user's mailbox was unavailable (for example because it was not found, or because the command was rejected for policy reasons).
    const RESPONSE_MAILBOX_NOT_LOCALE   = 551; // The recipient is not local to the server. The server then gives a forward address to try.
    const RESPONSE_MAILBOX_EXCEEDED     = 552; // The action was aborted due to exceeded storage allocation.
    const RESPONSE_MAILBOX_INVALID      = 553; // The command was aborted because the mailbox name is invalid.
    const RESPONSE_TRANSACTION_FAILED   = 554; // The transaction failed.
    const RESPONSE_NO_EXTERNAL_ALLOWED  = 571; // No external routing allowed.
    const RESPONSE_BOUNCE_BLOCKED       = 572; // Bounces are blocked.

    static private $_responseCodeDescription = array(

        200 => 'A non-standard response reference RFC876',
        211 => 'A system status message.',
        214 => 'A help message for a human reader follows.',
        220 => 'SMTP Service ready.',
        221 => 'Service closing.',
        250 => 'Requested action taken and completed. The best message of them all.',
        251 => 'The recipient is not local to the server, but the server will accept and forward the message.',
        252 => 'The recipient cannot be VRFYed, but the server accepts the message and attempts delivery.',
        253 => 'Messages for "node" have started.',

        354 => 'Start message input and end with <CRLF>.<CRLF>. This indicates that the server is ready to accept the message itself (after you have told it who it is from and where you want to to go).',

        421 => 'The service is not available and the connection will be closed.',
        432 => 'A password transition is needed.',
        450 => "The requested command failed because the user's mailbox was unavailable (for example because it was locked). Try again later.",
        451 => 'The command has been aborted due to a server error. Not your fault. Maybe let the admin know.',
        452 => 'The command has been aborted because the server has insufficient system storage.',
        453 => 'You have no mail',
        454 => 'TLS not available due to temporary reason: Encryption required.',
        458 => 'Unable to queue messages for node.',
        459 => 'Node not allowed: "reason"',

        500 => 'The server could not recognize the command due to a syntax error.',
        501 => 'A syntax error was encountered in command arguments.',
        502 => 'This command is not implemented.',
        503 => 'The server has encountered a bad sequence of commands.',
        504 => 'A command parameter is not implemented.',
        505 => 'Authentication required.',
        521 => 'Machine does not accept mail.',
        530 => 'Must issue STARTTLS command first: Encryption required.',
        534 => 'Authentication mechanism too weak.',
        538 => 'Encryption required for requested authentication.',
        550 => "The requested command failed because the user's mailbox was unavailable (for example because it was not found, or because the command was rejected for policy reasons).",
        551 => 'The recipient is not local to the server. The server then gives a forward address to try.',
        552 => 'The action was aborted due to exceeded storage allocation.',
        553 => 'The command was aborted because the mailbox name is invalid.',
        554 => 'The transaction failed.',
        571 => 'No external routing allowed.',
        572 => 'Bounces are blocked.',
    );

    /**
     * Get response SMTP code description
     *
     * @param int $code a valide reponse code
     * @return string a code description
     */
    static public function getResponseCodeDescription($code)
    {
        if (!isset(self::$_responseCodeDescription[$code])) {
            throw new BaseZF_Exception('Unknow reponse code for value "' . $code . '"');
        }

        return self::$_responseCodeDescription[$code];
    }

    /**
     * Send content to a socket and retrieve response
     *
     * @param void a fsockopen ressource
     * @param string content
     * @return string ressouce response
     */
    static private function _sendCommand($fp, $out)
    {
        fwrite($fp, $out . "\r\n");
        stream_set_timeout($fp, 2);

        $s = '';
        for($i = 0; $i < 2; $i++) {
            $s .= fgets($fp, 1024);
        }

        return $s;
    }

    /**
     * Validate mail directly on domain server of email adress using SMTP protocole
     *
     * @param string a email adress
     * @return int a reponse code
     */
    static public function validateEmailAddress($email)
    {
        // handle multiple validation
        if (is_array($email)) {

            $response = array();
            foreach ($email as $value) {
                $response[$value] = self::validateEmailAddress($value);
            }

            return $response;
        }

        $code = false;
        $mxHostnames = array();
        $mxHostnamesWeigth = array();
        $hostname = array_pop(explode('@', $emails));

        // try to get mx
        if(!getmxrr($hostname, $mxHostnames, $mxHostnamesWeigth)) {
            return $code;
        }

        $mxHostname = array_shift($mxHostnames);

        // test email server
        $fp = @fsockopen($mxHostname, 25, $errno, $errstr, 2);
        if ($fp)    {
            self::_sendCommand($fp, 'HELO ' . self::DEFAULT_HELO);
            self::_sendCommand($fp, 'MAIL FROM:<' . self::DEFAULT_FROM . '>');
            $erg = self::_sendCommand($fp, 'RCPT TO:<' . $emails . '>');
            fclose($fp);
            $code = intval(substr($erg, 0, 3));
        }

        return $code;
    }

    /**
     * Parse /var/log/mail.log file
     *
     * @param string $logFilePath log file path
     * @return array data of log
     */
    static public function parseMailLog($logFilePath)
    {
        $collection = array();

        $fp = @fopen($logFilePath, "r");
        if ($fp) {

            while (!feof($fp)) {

                $buffer = fgets($fp, 1024);
                $matches = array();

                // parse me : Feb 26 06:27:49 ns354369 sm-mta[17678]: n1OHMvSn028483: to=<my@example.com>, ctladdr=<www-data@example.com> (33/33), delay=1+12:04:52, xdelay=00:00:00, mailer=esmtp, pri=19566779, relay=example.com., dsn=4.0.0, stat=Deferred: Connection timed out with example.com
                preg_match_all("/([a-z-A-Z]{3} [0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}) ([a-z-A-Z-0-9]*) ([a-z-A-Z-0-9]*)\[([0-9]*)\]: ([a-z-A-Z-0-9]*): (.*)/", $buffer, $matches);

                // add parsed data
                $item = array(
                    'creation'      => $matches[1][0],
                    'hostname'      => $matches[2][0],
                    'service'       => $matches[3][0],
                    'pid'           => $matches[4][0],
                    'mailqueue_id'  => $matches[5][0],
                );

                // add other data
                $tmpA = array_map('trim', explode(',', $matches[6][0]));
                foreach ($tmp as $k => $v) {
                    $tmpB = explode('=', $v);

                    // clean mail addr
                    if (in_array($tmpB[0], array('to', 'from')) !== false) {
                         $tmpB[1] == substr($tmpB[1], 1, -1);
                    }

                    $item[$tmpB[0]] = $tmpB[1];
                }

                $collection[] = $item;
            }

            fclose($fp);
        }

        return $collection;
    }
}

