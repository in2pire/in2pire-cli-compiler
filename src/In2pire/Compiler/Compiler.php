<?php

/**
 * @file
 *
 * @package In2pire
 * @subpackage CliCompiler
 * @author Nhat Tran <nhat.tran@inspire.vn>
 */

namespace In2pire\Compiler;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use In2pire\Cli\Configuration;
use In2pire\Compiler\Logger\LoggerInterface;
use In2pire\Compiler\Logger\VoidLogger;
use In2pire\Compiler\Component\Composer;

/**
 * Cli Compiler.
 */
class Compiler
{
    /**
     * Compress files.
     */
    const FLAG_COMPRESS = 1;

    /**
     * Optimize class map.
     */
    const FLAG_OPTIMIZE = 2;

    /**
     * Add .phar extension.
     */
    const FLAG_PHAR = 4;

    /**
     * Make executable file.
     */
    const FLAG_EXECUTABLE = 8;

    /**
     * Cache configuration.
     */
    const FLAG_CACHE = 16;

    /**
     * Success code.
     */
    const RETURN_SUCCESS = 1;

    /**
     * Error code.
     */
    const RETURN_ERROR = 0;

    /**
     * Composer.
     *
     * @var \In2pire\Compiler\Component\Composer
     */
    protected $composer;

    /**
     * Logger.
     *
     * @var \In2pire\Compiler\Logger\LoggerInterface
     */
    protected $logger = null;

    /**
     * Project path.
     *
     * @var string
     */
    protected $projectPath = null;

    /**
     * App Path.
     *
     * @var string
     */
    protected $appPath = null;

    /**
     * App name.
     *
     * @var string
     */
    protected $appName = null;

    /**
     * Build version.
     *
     * @var string
     */
    protected $buildVersion = null;

    /**
     * Build date.
     *
     * @var string
     */
    protected $buildDate = null;

    /**
     * Compile flag.
     *
     * @var int
     */
    protected $flag = 0;

    /**
     * Error.
     *
     * @var string
     */
    protected $lastError = null;

    /**
     * Built file.
     *
     * @var string
     */
    protected $builtFile = null;

    /**
     * List of excluded files.
     *
     * @var array
     */
    protected $excludeFiles = [];

    /**
     * File counter.
     *
     * @var int
     */
    protected $fileCounter = 0;

    /**
     * Constructor.
     *
     * @param string $projectPath
     *   Project Path.
     * @param string $appPath
     *   App path.
     * @param string $appName
     *   App name.
     * @param string $configPath
     *   Config path.
     * @param string $buildVersion
     *   Build version.
     * @param string $buildDate
     *   Build date.
     */
    public function __construct($projectPath, $appPath, $appName, $configPath, $buildVersion, $buildDate)
    {
        $this->projectPath = $projectPath;
        $this->appPath = $appPath;
        $this->appName = $appName;
        $this->configPath = $configPath;
        $this->buildVersion = $buildVersion;
        $this->buildDate = $buildDate;
        $this->composer = new Composer($projectPath);
        $this->logger = new VoidLogger();
    }

    /**
     * Get composer.
     *
     * @return \In2pire\Compiler\Component\Composer
     *   Composer object.
     */
    public function getComposer()
    {
        return $this->composer;
    }

    /**
     * Set composer.
     *
     * @param \In2pire\Compiler\Component\Composer $composer
     *   Composer.
     *
     * @return \In2pire\Compiler\Compiler
     *   The called object.
     */
    public function setComposer(Composer $composer)
    {
        $this->composer = $composer;
        return $this;
    }

    /**
     * Get logger.
     *
     * @return \In2pire\Compiler\Logger\LoggerInterface
     *   Logger.
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set logger.
     *
     * @param \In2pire\Compiler\Logger\LoggerInterface $logger
     *   Logger.
     *
     * @return \In2pire\Compiler\Compiler
     *   The called object.
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * Set project path.
     *
     * @param string $projectPath
     *   Path.
     */
    public function setProjectPath($projectPath) {
        $this->projectPath = $projectPath;
    }

    /**
     * Get project path.
     *
     * @return string
     *   Path to project.
     */
    public function getProjectPath() {
        return $this->projectPath;
    }

    /**
     * Set app path.
     *
     * @param string $appPath
     *   Path.
     */
    public function setAppPath($appPath) {
        $this->appPath = $appPath;
    }

    /**
     * Get app path.
     *
     * @return string
     *   Path to app.
     */
    public function getAppPath() {
        return $this->appPath;
    }

    /**
     * Set app name.
     *
     * @param string $appName
     *   App name.
     */
    public function setAppName($appName) {
        $this->appName = $appName;
    }

    /**
     * Get app name.
     *
     * @return string
     *   App name.
     */
    public function getAppName() {
        return $this->appName;
    }

    /**
     * Set config path.
     *
     * @param string $configPath
     *   Config path.
     */
    public function setConfigPath($configPath) {
        $this->configPath = $configPath;
    }

    /**
     * Get config path.
     *
     * @return string
     *   Path.
     */
    public function getConfigPath() {
        return $this->configPath;
    }

    /**
     * Set build version.
     *
     * @param string $buildVersion
     *   Version.
     */
    public function setBuildVersion($buildVersion) {
        $this->buildVersion = $buildVersion;
    }

    /**
     * Get build version.
     *
     * @return string
     *   Version.
     */
    public function getBuildVersion() {
        return $this->buildVersion;
    }

    /**
     * Set build date.
     *
     * @param string $buildDate
     *   Date.
     */
    public function setBuildDate($buildDate) {
        $this->buildDate = $buildDate;
    }

    /**
     * Get build date.
     *
     * @return string
     *   Date.
     */
    public function getBuildDate() {
        return $this->buildDate;
    }

    /**
     * Set compile flag.
     *
     * @param int $flag
     *   Compile flag.
     */
    public function setFlag($flag)
    {
        $this->flag = $flag;
        return $this;
    }

    /**
     * Get compile flag.
     *
     * @return int
     *   Flag.
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * Get last error.
     *
     * @return string
     *   Error.
     */
    public function getLastError()
    {
        return $this->lastError;
    }

    /**
     * Get built file.
     *
     * @return string
     *   Path to built file.
     */
    public function getBuiltFile()
    {
        return $this->builtFile;
    }

    /**
     * Get max open files limit.
     *
     * @return int|boolean
     *   Number of max open files or false.
     */
    public function getMaxOpenFilesLimit()
    {
        static $limit = null;

        if (null === $limit) {
            $process = new Process('ulimit -n');
            $process->run();

            if ($process->isSuccessful()) {
                $limit = (int) trim($process->getOutput());
            }

            unset($process);
        }

        return $limit;
    }

    /**
     * Reset file counter.
     *
     * @return \In2pire\Compiler\Compiler
     *   The called object.
     */
    protected function resetFileCounter()
    {
        $this->fileCounter = 0;
        return $this;
    }

    /**
     * Increase file counter.
     *
     * @return \In2pire\Compiler\Compiler
     *   The called object.
     */
    protected function increaseFileCounter()
    {
        $this->fileCounter++;
        return $this;
    }

    /**
     * Get file counter.
     *
     * @return int
     *   Counter.
     */
    protected function getFileCounter()
    {
        return $this->fileCounter;
    }

    /**
     * Backup vendor/composer.
     */
    protected function backupComposer()
    {
        $composerDir = $this->composer->getVendorDirectory() . '/composer';
        $composerBak = $composerDir . '.bak';

        // Remove old backup folder.
        if (is_dir($composerBak)) {
            $process = new Process('rm -rf "' . $composerBak . '"');
            $process->run();
            $isSuccessful = $process->isSuccessful();

            unset($process);

            if (!$isSuccessful) {
                return false;
            }
        }

        // Backup
        $process = new Process('cp -R "' . $composerDir . '" "' . $composerBak . '"');
        $process->run();
        $isSuccessful = $process->isSuccessful();

        unset($process);

        return $isSuccessful;
    }

    /**
     * Restore vendor/composer.
     */
    protected function restoreComposer()
    {
        $composerDir = $this->composer->getVendorDirectory() . '/composer';
        $composerBak = $composerDir . '.bak';

        // Restore class loaders.
        if (!is_dir($composerBak)) {
            return false;
        }

        // Remove current one.
        $process = new Process('rm -rf "' . $composerDir . '"');
        $process->run();
        $isSuccessful = $process->isSuccessful();

        unset($process);

        if (!$isSuccessful) {
            return false;
        }

        // Restore
        $process = new Process('mv "' . $composerBak . '" "' . $composerDir . '"');
        $process->run();
        $isSuccessful = $process->isSuccessful();

        unset($process);

        return $isSuccessful;
    }

    /**
     * Get composer command.
     *
     * @return mixed
     *   Command on success or false.
     */
    protected function getComposerCommand()
    {
        static $command = null;

        if (null !== $command) {
            return $command;
        }

        // Detect local composer.phar.
        $pharFile = $this->projectPath . '/composer.phar';

        if (file_exists($pharFile)) {
            return $command = '/usr/bin/env php -d allow_url_fopen=On -d detect_unicode=Off "' . $pharFile . '"';
        }

        // Detect local composer command
        $composerFile = $this->projectPath . '/composer';

        if (file_exists($composerFile)) {
            return $command = './composer';
        }

        // Detect global composer command.
        $process = new Process('hash composer');
        $process->run();
        $isSuccessful = $process->isSuccessful();

        unset($process);

        if ($isSuccessful) {
            return $command = 'composer';
        }

        return $command;
    }

    /**
     * Generate optimized classmap.
     */
    protected function generateOptimizedClassmap()
    {
        $composerCommand = $this->getComposerCommand();

        if (!$composerCommand) {
            $this->logger->log('<error>Could not find composer command</error>');
            return false;
        }

        $process = new Process($composerCommand . ' dump-autoload --optimize', $this->projectPath);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->log('<error>Could not optimize class map</error>');
        }

        $this->logger->log('<info>Optimized class map</info>');
        unset($process);

        return true;
    }

    /**
     * Exclude files.
     *
     * @param array $excludeFiles
     *   List of files.
     *
     * @return \In2pire\Compiler\Compiler
     *   The called object.
     */
    public function excludeFiles(array $excludeFiles)
    {
        $this->excludeFiles = $excludeFiles;
        return $this;
    }

    /**
     * Add file to phar package.
     *
     * @param \Phar $phar
     *   Phar object.
     * @param string $file
     *   File path.
     * @param boolean $strip
     *   Strip whitespace.
     */
    protected function addFile($phar, $file, $strip = true)
    {
        $path = strtr(str_replace($this->projectPath, '', $file->getRealPath()), '\\', '/');
        $content = file_get_contents($file);

        $this->logger->log('<comment>Adding file</comment> ' . $path);

        switch (true) {
            case ('LICENSE' === basename($file)):
                $content = "\n" . $content . "\n";
                break;

            case preg_match('#/In2pire/Cli/Configuration/PhpConfiguration.php$#', $path):
                if ($this->flag & STATIC::FLAG_CACHE) {
                    $configuration = $this->getAllConfiguration();
                    $content = str_replace('// [CONFIGURATION]', '$this->configuration = ' . var_export($configuration, 1) . ';', $content);
                    $content = str_replace(['@version', '@build'], [$this->buildVersion, $this->buildDate], $content);
                }
                break;

            case (pathinfo($file, PATHINFO_EXTENSION) == 'yml'):
                $content = str_replace(['@version', '@build'], [$this->buildVersion, $this->buildDate], $content);
                break;

            case $strip:
                $content = $this->stripWhitespace($content);
                break;
        }

        $phar->addFromString($path, $content);
        $this->increaseFileCounter();
    }

    /**
     * Add bin file.
     *
     * @param \Phar $phar
     *   Phar object.
     * @param string $binFile
     *   Path to bin file.
     */
    protected function addBinFile($phar, $binFile)
    {
        $relativeBinFile = str_replace($this->projectPath, '', $binFile);
        $this->logger->log('<comment>Adding file</comment> ' . $relativeBinFile);

        $content = file_get_contents($binFile);
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);

        $phar->addFromString($relativeBinFile, $content);
        $this->increaseFileCounter();
    }

    /**
     * Get finder.
     *
     * @return \Symfony\Component\Finder\Finder
     *   Finder.
     */
    protected function findFiles()
    {
        // Add php files.
        $finder = new Finder();
        $finder
            ->files()
            // Ignore version control system folder.
            ->ignoreVCS(true)
            // Do not add tests folders.
            ->exclude('Tests')
            ->exclude('tests')
            // Do not add composer backup folder.
            ->exclude('composer.bak');

        if (!empty($this->excludeFiles)) {
            foreach ($this->excludeFiles as $file) {
                $finder->notPath($file);
            }
        }

        return $finder;
    }

    /**
     * Removes whitespace from a PHP source string while preserving line
     * numbers.
     *
     * @param string $source
     *   A PHP string.
     *
     * @return string
     *   The PHP string with the whitespace removed.
     */
    protected function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';

        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    protected function getStub($binFile, $pharFileName)
    {
        $relativeBinFile = str_replace($this->projectPath, '', $binFile);

        $stub = <<<EOF
#!/usr/bin/env php
<?php
/*
 * This file is compiled by IN2PIRE CLI Compiler (https://github.com/in2pire/in2pire-cli-compiler)
 *
 * For the full copyright and license information, please view the license that
 * is located at the bottom of this file.
 */

Phar::mapPhar('$pharFileName');

EOF;

        return $stub . <<<EOF
require 'phar://$pharFileName/$relativeBinFile';

__HALT_COMPILER();
EOF;
    }

    protected function getAllConfiguration()
    {
        $rootPath = $this->projectPath . '/' . $this->configPath;
        $files = $this->findFiles()
            // Only add yml file.
            ->name('*.yml')
            ->in($rootPath);

        $results = [];
        $config = new Configuration();
        $config->init($rootPath, false);

        foreach ($files as $file) {
            $path = strtr(str_replace($rootPath . '/', '', $file->getRealPath()), '\\', '/');
            $namespace = str_replace(['.yml', '/'], ['', '.'], $path);
            $results[$namespace] = $config->getAll($namespace);
        }

        return $results;
    }

    /**
     * Compile application.
     */
    protected function compile()
    {
        $binFile = $this->projectPath . '/' . $this->appPath;
        $pharFileName = $this->appName . '.phar';
        $pharFile = $this->projectPath . '/' . $pharFileName;

        if (file_exists($pharFile)) {
            unlink($pharFile);

            if (file_exists($pharFile)) {
                $this->lastError = 'Could not remove ' . $pharFileName;
                return static::RETURN_ERROR;
            }
        }

        // Create phar file.
        $phar = new \Phar($pharFile, 0, $pharFileName);

        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->startBuffering();
        $this->resetFileCounter();

        // Add php files.
        $files = $this->findFiles()
            // Only add php file.
            ->name('*.php')
            // Search in project folder.
            ->in($this->projectPath);

        foreach ($files as $file) {
            $this->addFile($phar, $file);
        }

        // Add resource files
        $files = $this->findFiles()
            // Only add files in /Resources/
            ->path('/Resources/')
            ->in($this->projectPath);

        foreach ($files as $file) {
            $this->addFile($phar, $file, false);
        }

        // Add config files.
        if (~$this->flag & STATIC::FLAG_CACHE) {
            $files = $this->findFiles()
                // Only add yml file.
                ->name('*.yml')
                ->in($this->configPath);

            foreach ($files as $file) {
                $this->addFile($phar, $file, false);
            }
        }

        // Add the cli application.
        $this->addBinFile($phar, $binFile);

        // Stubs
        $phar->setStub($this->getStub($binFile, $pharFileName));

        // Stop buffering.
        $phar->stopBuffering();

        // Try to compress files.
        if ($this->flag & static::FLAG_COMPRESS) {
            if ($this->getFileCounter() > $this->getMaxOpenFilesLimit()) {
                $this->lastError = 'Could not compress ' . $this->getFileCounter() . ' files, max open files is ' . $this->getMaxOpenFilesLimit() . PHP_EOL .
                    'Please use --no-compress or increase the limit using `ulimit -n NUMBER` (NUMBER > ' . $this->getFileCounter() . ')' . PHP_EOL .
                    'If you are using Mac OSX, try http://superuser.com/a/514049';
                return static::RETURN_ERROR;
            } elseif (extension_loaded('zlib') || extension_loaded('bzip2')) {
                $this->logger->log('<info>Compressed files</info>');
                $phar->compressFiles(\Phar::GZ);
            } else {
                $this->logger->log('<error>Could not find zlib or bzip2 library</error>');
            }
        }

        // Add license file if exists.
        $licenseFile = $this->projectPath . '/LICENSE';

        if (file_exists($licenseFile)) {
            $this->addFile($phar, new \SplFileInfo($licenseFile), false);
        }

        unset($phar);

        if ($this->flag & STATIC::FLAG_EXECUTABLE) {
            $process = new Process('chmod +x "' . $pharFile . '"');
            $process->run();
            $isSuccessful = $process->isSuccessful();

            unset($process);

            if (!$isSuccessful) {
                $this->lastError = 'An error occured while chmod phar file';
                return static::RETURN_ERROR;
            }
        }

        if (~$this->flag & STATIC::FLAG_PHAR) {
            $noPharFile = substr($pharFile, 0, -5);

            if (file_exists($noPharFile)) {
                unlink($noPharFile);

                if (file_exists($noPharFile)) {
                    $this->lastError = 'Could not remove ' . $noPharFile;
                    return static::RETURN_ERROR;
                }
            }

            $process = new Process('mv "' . $pharFile . '" "' . $noPharFile . '"');
            $process->run();
            $isSuccessful = $process->isSuccessful();

            unset($process);

            if (!$isSuccessful) {
                $this->lastError = 'Could not remove .phar extension';
                return static::RETURN_ERROR;
            }

            $pharFile = $noPharFile;
        }

        $this->builtFile = $pharFile;

        return static::RETURN_SUCCESS;
    }

    /**
     * Check wether code is success.
     *
     * @return boolean
     *   True on success. Otherwise fail.
     */
    public function isSuccessful()
    {
        return $this->returnCode == static::RETURN_SUCCESS;
    }

    /**
     * Run compiler.
     */
    public function run()
    {
        $this->returnCode = static::RETURN_SUCCESS;
        $this->lastError = null;
        $this->builtFile = null;

        if (!$this->backupComposer()) {
            $this->lastError = 'Could not backup composer directory';
            return $this->returnCode = static::RETURN_ERROR;
        }

        try {
            if ($this->flag & static::FLAG_OPTIMIZE) {
                $this->generateOptimizedClassmap();
            }

            $this->returnCode = $this->compile();
        } catch(\Exception $e) {
            $this->lastError = get_class($e) . ': ' . $e->getMessage();
            $this->returnCode = static::RETURN_ERROR;
        }

        if (!$this->restoreComposer()) {
            $this->lastError = 'Could not restore composer directory';
            return $this->returnCode = static::RETURN_ERROR;
        }

        return $this->returnCode;
    }
}
