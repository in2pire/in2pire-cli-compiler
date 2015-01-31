<?php

/**
 * @file
 *
 * @package In2pire
 * @subpackage CliCompiler
 * @author Nhat Tran <nhat.tran@inspire.vn>
 */

namespace In2pire\Compiler\Component;

use Symfony\Component\Process\Process;

/**
 * Git Component.
 */
class Git
{
    /**
     * Project Directory.
     *
     * @var string
     */
    protected $directory = null;

    /**
     * Constructor.
     *
     * @param string $directory
     *   Directory.
     */
    public function __construct($directory = null)
    {
        if (!static::isValidDirectory($directory)) {
            throw new \UnexpectedValueException('Directory ' . $directory . ' is not a git repository');
        }

        // Set directory.
        $this->directory = rtrim($directory, '/\\');
    }

    /**
     * Set project directory.
     *
     * @param string $directory
     *   Project Directory.
     *
     * @return In2pire\Component\Composer
     *   The called object.
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * Get directory.
     *
     * @return string
     *   Project directory.
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Get repository version.
     *
     * @return string
     *   Version.
     */
    public function getVersion()
    {
        $process = new Process('git describe --tags HEAD', $this->directory);

        if ($process->run() == 0) {
            $version = trim($process->getOutput());
        } else {
            $process = new Process('git log --pretty="%H" -n1 HEAD', $this->directory);

            if ($process->run() != 0) {
                throw new \RuntimeException('Can\'t run git log. You must ensure that git binary is available.');
            }

            $version = 'rev-' . substr(trim($process->getOutput()), 0, 8);
        }

        return $version;
    }

    /**
     * Get last commit date in repository.
     *
     * @return string
     *   Date.
     */
    public function getLastCommitDate()
    {
        $process = new Process('git log -n1 --pretty=%ci HEAD', $this->directory);

        if ($process->run() != 0) {
            throw new \RuntimeException('Can\'t run git log. You must ensure that git binary is available.');
        }

        $date = new \DateTime(trim($process->getOutput()));
        $date->setTimezone(new \DateTimeZone('UTC'));

        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Find a git repository.
     *
     * @return mixed
     *   Git object or false.
     */
    public static function findGitRepository($directory)
    {
        $process = new Process('git rev-parse --show-toplevel', $directory);
        $process->run();

        if (!$process->isSuccessful()) {
            return false;
        }

        $rootPath = trim($process->getOutput());
        return new Git($rootPath);
    }

    /**
     * Check if directory is a project that uses composer.
     *
     * @param  string $directory
     *   Project directory.
     *
     * @return boolean
     *   True if directory is a composer project. Otherwise false.
     */
    public static function isValidDirectory($directory)
    {
        $process = new Process('git remote -v', $directory);
        $process->run();
        return $process->isSuccessful();
    }
}
