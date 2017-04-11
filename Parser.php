<?php

namespace steevanb\PhpYaml;

use steevanb\PhpYaml\Exception\FunctionIdNotFoundException;
use steevanb\PhpYaml\Exception\FunctionNotFoundException;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;

class Parser extends SymfonyYamlParser
{
    /** @var array */
    protected static $functions = [];

    /**
     * @param string $id
     * @param callable $callable
     */
    public static function registerFunction($id, $callable)
    {
        static::$functions[$id] = $callable;
    }

    /** @param string|null $path */
    public static function registerFileFunction($path = null)
    {
        static::registerFunction('file', function($fileName) use ($path) {
            $path = $path === null ? __DIR__ : rtrim($path, DIRECTORY_SEPARATOR);
            $filePath = $fileName[0] === DIRECTORY_SEPARATOR ? $fileName : $path . DIRECTORY_SEPARATOR . $fileName;
            if (is_readable($filePath) === false) {
                throw new \Exception('File "' . $filePath . '" not found.');
            }

            return file_get_contents($filePath);
        });
    }

    public static function registerDateFunction()
    {
        static::registerFunction('date', function($date = null) {
            return new \DateTime($date);
        });
    }

    /**
     * @param string $value
     * @param int $flags
     * @return array|null
     */
    public function parse($value, $flags = 0)
    {
        $return = parent::parse($value, $flags);

        if (is_array($return)) {
            $this->parseValues($return);
        }

        return $return;
    }

    /**
     * @param array $values
     * @return $this
     * @throws \Exception
     */
    protected function parseValues(array &$values)
    {
        foreach ($values as &$value) {
            if (is_array($value)) {
                $this->parseValues($value);
            } elseif (is_string($value) && substr($value, 0, 1) === '<' && substr($value, -1) === '>') {
                $functionId = null;
                $parameters = [];
                foreach (token_get_all('<?php ' . $value) as $token) {
                    if (is_array($token)) {
                        if ($token[0] === T_STRING && $functionId === null) {
                            $functionId = $token[1];
                        } elseif ($token[0] === T_STRING) {
                            $parameters[] = $token[1];
                        } elseif ($token[0] === T_CONSTANT_ENCAPSED_STRING) {
                            $parameters[] = substr($token[1], 1, -1);
                        } elseif ($token[0] === T_LNUMBER || $token[0] === T_DNUMBER) {
                            $parameters[] = $token[1];
                        }
                    }
                }

                if ($functionId === null) {
                    throw new FunctionIdNotFoundException('Function name cannont be found in ' . $value . '.');
                } elseif (isset(static::$functions[$functionId]) === false) {
                    throw new FunctionNotFoundException('Function "' . $functionId . '" not found.');
                }

                $value = call_user_func_array(static::$functions[$functionId], $parameters);
            }
        }

        return $this;
    }
}
