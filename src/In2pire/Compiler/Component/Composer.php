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
use In2pire\Compiler\Component\Json\JsonFile;

/**
 * Composer Component.
 */
class Composer
{
    /**
     * Project Directory.
     *
     * @var string
     */
    protected $directory = null;

    /**
     * Composer config.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Constructor.
     *
     * @param string $directory
     *   Directory.
     */
    public function __construct($directory = null)
    {
        if (!static::isValidDirectory($directory)) {
            throw new \UnexpectedValueException('Directory ' . $directory . ' is not a composer project');
        }

        // Set directory.
        $this->directory = rtrim($directory, '/\\');

        // Read configuration.
        $configFile = new JsonFile($this->directory . '/composer.json');
        $this->config = $configFile->read();
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
     * Get composer command.
     *
     * @return string
     *   Command.
     */
    public function getCommand()
    {
        static $command = null;

        if (null !== $command) {
            return $command;
        }

        return $command;
    }

    /**
     * Get composer config.
     *
     * @return array
     *   Configurations.
     */
    public function getConfig()
    {
        return $this->config;
    }

    public function getVendorDirectory()
    {
        static $directory = null;

        if (null !== $directory) {
            return $directory;
        }

        if (empty($this->config['config']['vendor-dir'])) {
            $directory = 'vendor';
        } else {
            $directory = rtrim($this->config['config']['vendor-dir'], '/\\');
        }

        return $directory;
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
        $directory = rtrim($directory, '/');

        if (!is_readable($directory . '/composer.json')) {
            return false;
        }

        return true;
    }
}
