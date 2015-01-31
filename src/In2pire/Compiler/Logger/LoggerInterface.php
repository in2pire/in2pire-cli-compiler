<?php

/**
 * @file
 *
 * @package In2pire
 * @subpackage CliCompiler
 * @author Nhat Tran <nhat.tran@inspire.vn>
 */

namespace In2pire\Compiler\Logger;

/**
 * Logger.
 */
interface LoggerInterface
{
    /**
     * Log a message.
     *
     * @param string $message
     *   Message.
     *
     * @return In2pire\Compiler\Logger
     *   The called object.
     */
    function log($message);
}
