<?php

/**
 * @file
 *
 * @package In2pire
 * @subpackage CliCompiler
 * @author Nhat Tran <nhat.tran@inspire.vn>
 */

namespace In2pire\Compiler\Cli\Task;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use In2pire\Compiler\Compiler;
use In2pire\Compiler\Logger\ConsoleLogger;
use In2pire\Compiler\Logger\VoidLogger;

/**
 * Compile Application.
 */
class Compile extends \In2pire\Cli\Task\CliTask
{
    /**
     * @inheritdoc
     */
    protected $id = 'compile';

    /**
     * @inheritdoc
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $projectPath = $input->getOption('project-path');
        $appPath = $input->getArgument('app');
        $appName = $this->data['app-name'];
        $configPath = $input->getOption('config-path');
        $buildVersion = $input->getOption('build-version');
        $buildDate = $input->getOption('build-date');
        $compileFlag = 0;

        // Flags
        if ($input->getOption('compress')) {
            $compileFlag |= Compiler::FLAG_COMPRESS;
        }

        if ($input->getOption('optimize')) {
            $compileFlag |= Compiler::FLAG_OPTIMIZE;
        }

        if ($input->getOption('phar')) {
            $compileFlag |= Compiler::FLAG_PHAR;
        }

        if ($input->getOption('executable')) {
            $compileFlag |= Compiler::FLAG_EXECUTABLE;
        }

        if ($output->getVerbosity() > OutputInterface::VERBOSITY_NORMAL) {
            $logger = new ConsoleLogger($output);
        } else {
            $logger = new VoidLogger();
        }

        $compiler = new Compiler($projectPath, $appPath, $appName, $configPath, $buildVersion, $buildDate);
        $compiler
            ->setFlag($compileFlag)
            ->setLogger($logger)
            ->run();

        if ($compiler->isSuccessful()) {
            $output->writeln('<comment>Built file</comment> ' . $compiler->getBuiltFile());
        } else {
            $output->writeln('<error>' . $compiler->getLastError() . '</error>');
            return static::RETURN_ERROR;
        }

        return static::RETURN_SUCCESS;
    }
}
