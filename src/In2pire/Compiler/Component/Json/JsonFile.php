<?php

/**
 * @file
 *
 * @package In2pire
 * @subpackage CliCompiler
 * @author Nhat Tran <nhat.tran@inspire.vn>
 */

namespace In2pire\Compiler\Component\Json;

use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

/**
 * Reads json files.
 */
class JsonFile
{
    const LAX_SCHEMA = 1;
    const STRICT_SCHEMA = 2;

    const JSON_UNESCAPED_SLASHES = 64;
    const JSON_PRETTY_PRINT = 128;
    const JSON_UNESCAPED_UNICODE = 256;

    private $path;

    /**
     * Initializes json file reader/parser.
     *
     * @param string $path
     *   Path to json file.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Get file path.
     *
     * @return string
     *   Path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Checks whether json file exists.
     *
     * @return bool
     *   True if file exists. Otherwise false.
     */
    public function exists()
    {
        return is_readable($this->path);
    }

    /**
     * Reads json file.
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function read()
    {
        $json = file_get_contents($this->path);
        return static::parseJson($json, $this->path);
    }


    /**
     * Parses json string and returns hash.
     *
     * @param string $json
     *   Json string.
     *
     * @param string $file
     *   The json file.
     *
     * @return mixed
     */
    public static function parseJson($json, $file = null)
    {
        $data = json_decode($json, true);

        if (null === $data && JSON_ERROR_NONE !== json_last_error()) {
            self::validateSyntax($json, $file);
        }

        return $data;
    }

    /**
     * Validates the syntax of a JSON string.
     *
     * @param string $json
     *   Json string.
     *
     * @param string $file
     *   The json file.
     *
     * @return bool
     *   True on success. Otherwise false.
     *
     * @throws \UnexpectedValueException
     * @throws ParsingException
     */
    protected static function validateSyntax($json, $file = null)
    {
        $parser = new JsonParser();
        $result = $parser->lint($json);

        if (null === $result) {
            if (defined('JSON_ERROR_UTF8') && JSON_ERROR_UTF8 === json_last_error()) {
                throw new \UnexpectedValueException('"' . $file . '" is not UTF-8, could not parse as JSON');
            }

            return true;
        }

        throw new ParsingException('"' . $file . '" does not contain valid JSON');
    }
}
