<?php

/**
 * @file
 *
 * @package In2pire
 * @subpackage CliCompiler
 * @author Nhat Tran <nhat.tran@inspire.vn>
 */

namespace In2pire\Compiler\Logger;

use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Logger.
 */
class ConsoleLogger extends AbstractLogger
{
    /**
     * Console output.
     *
     * @var Symfony\Component\Console\Output\ConsoleOutput
     */
    protected $output = null;

    /**
     * Constructor
     *
     * @param Symfony\Component\Console\Output\ConsoleOutput $output
     *   Console Output.
     */
    public function __construct(ConsoleOutput $output)
    {
        $this->output = $output;
    }

    /**
     * @inheritdoc
     */
    public function log($message)
    {
        $this->output->writeln($message);
        return $this;
    }
}
