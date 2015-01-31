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
class VoidLogger extends AbstractLogger
{
    /**
     * @inheritdoc
     */
    public function log($message)
    {
        return $this;
    }
}
