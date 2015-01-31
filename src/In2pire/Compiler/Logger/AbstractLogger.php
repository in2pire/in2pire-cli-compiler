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
abstract class AbstractLogger implements LoggerInterface
{
    /**
     * @inheritdoc
     */
    abstract public function log($message);
}
