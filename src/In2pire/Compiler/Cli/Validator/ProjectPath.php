<?php

/**
 * @file
 *
 * @package In2pire
 * @subpackage CliCompiler
 * @author Nhat Tran <nhat.tran@inspire.vn>
 */

namespace In2pire\Compiler\Cli\Validator;

use Symfony\Component\Console\Input\InputInterface;
use In2pire\Compiler\Component\Git;
use In2pire\Compiler\Component\Composer;

/**
 * Project Path Validator.
 */
class ProjectPath extends \In2pire\Cli\Validator\CliValidator
{
    /**
     * @inheritdoc
     */
    public function validate(InputInterface $input)
    {
        $projectPath = $input->getOption('project-path');
        $git = null;
        $composer = null;

        // Try to detect project path from git.
        if (empty($projectPath)) {
            $git = Git::findGitRepository(APP_PATH . '/../');

            // If project is a git repository.
            if ($git) {
                $projectPath = $git->getDirectory();
            }
        }

        // Try to detect project path from project structure.
        if (empty($projectPath)) {
            $maxDepth = 6;

            for ($i = $maxDepth; $i > 0; --$i) {
                $dir = APP_PATH . str_repeat('/..', $i);
                $file = $dir . '/composer.json';

                if (file_exists($file)) {
                    $projectPath = $dir;
                    break;
                }
            }
        }

        // Could not find path to project.
        if (empty($projectPath)) {
            throw new \RuntimeException('Could not find path to project');
        }

        // Clean project path.
        $projectPath = rtrim(realpath($projectPath), '/\\');
        $input->setOption('project-path', $projectPath);

        // Detect version and date.
        $version = $input->getOption('build-version');
        $date = $input->getOption('build-date');

        if (empty($version) || empty($date)) {
            // Initiate git.
            if (empty($git)) {
                $git = Git::findGitRepository($projectPath);

                if (empty($git)) {
                    throw new \RuntimeException('Could not detect build version and date. Please specify using --build-version and --build-date');
                }
            }

            if (empty($version)) {
                $version = $git->getVersion();
                $input->setOption('build-version', $version);
            }

            if (empty($date)) {
                $date = $git->getLastCommitDate();
                $input->setOption('build-date', $date);
            }
        }

        // Initiate composer.
        $composer = new Composer($projectPath);
        $vendorDirectory = $composer->getVendorDirectory();

        if (strpos($vendorDirectory, '/') !== 0) {
            $vendorDirectory = $projectPath . '/' . $vendorDirectory;
        }

        $vendorDirectory = realpath($vendorDirectory);

        if (strpos($vendorDirectory, $projectPath) !== 0) {
            throw new \UnexpectedValueException('Vendor directory is not in project path');
        }

        unset($composer);

        return [
            'version' => $version,
            'date' => $date,
        ];
    }
}
