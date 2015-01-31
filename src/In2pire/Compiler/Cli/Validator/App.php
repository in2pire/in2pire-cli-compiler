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

/**
 * App Validator.
 */
class App extends \In2pire\Cli\Validator\CliValidator
{
    /**
     * @inheritdoc
     */
    public function validate(InputInterface $input)
    {
        $projectPath = $input->getOption('project-path');
        $app = $input->getArgument('app');

        if (strpos($app, '/') !== 0) {
            $app = $projectPath . '/' . $app;
        }

        $app = realpath($app);

        if (!$app || !is_file($app) || strpos($app, $projectPath) !== 0) {
            throw new \UnexpectedValueException('App is not in project path');
        }

        $appName = pathinfo($app, PATHINFO_FILENAME);
        $app = str_replace($projectPath . '/', '', $app);
        $input->setArgument('app', $app);

        // Validate config path.
        $configPath = $input->getOption('config-path');

        if (empty($configPath)) {
            $dirs = ['config', 'conf', 'settings'];

            foreach ($dirs as $dir) {
                $dir = $projectPath . '/' . $dir . '/' . $appName;
                $configPath = realpath($dir);

                if ($configPath) {
                    $configPath = str_replace($projectPath . '/', '', $configPath);
                    break;
                }
            }

            if (empty($configPath)) {
                throw new \UnexpectedValueException('Could not find configuration path. Please specify using --config-path');
            }
        } else {
            if (strpos($configPath, '/') !== 0) {
                $configPath = $projectPath . '/' . $configPath;
            }

            if (!is_dir($configPath)) {
                throw new \UnexpectedValueException($configPath . ' is not a directory');
            }

            $configPath = realpath($configPath);

            if (strpos($configPath, $projectPath) !== 0) {
                throw new \UnexpectedValueException('Config path is not in project path');
            }

            $configPath = str_replace($projectPath . '/', '', $configPath);
        }

        $input->setOption('config-path', $configPath);

        return [
            'app' => $app,
            'app-name' => $appName,
            'config-path' => $configPath,
        ];
    }
}
