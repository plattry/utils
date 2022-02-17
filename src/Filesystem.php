<?php

declare(strict_types=1);

namespace Plattry\Utils;

/**
 * Class Filesystem
 * @package Plattry\Utils
 */
class Filesystem
{
    /**
     * Scan directory and fetch the filename.
     * @param string $dirname
     * @param bool $recursive
     * @param string|null $filter
     * @return array
     */
    public static function scanDir(string $dirname, bool $recursive = false, string $filter = null): array
    {
        !is_dir($dirname) &&
        throw new \InvalidArgumentException("$dirname is not a valid directory.");

        $files = [];
        foreach (scandir($dirname) as $filename) {
            if ('.' === $filename || '..' === $filename)
                continue;

            $fullName = $dirname . '/' . $filename;
            if (is_file($fullName) && (is_null($filter) || preg_match($filter, $fullName)))
                $files[] = $fullName;
            elseif (true === $recursive && is_dir($fullName))
                array_push($files, ...static::scanDir($fullName, $recursive, $filter));
        }

        return $files;
    }

    /**
     * Find class from file.
     * @param string $filename
     * @return string|null
     */
    public static function findClass(string $filename): string|null
    {
        $tokens = \token_get_all(\file_get_contents($filename) ?: '');
        (\count($tokens) === 1 || $tokens[0][0] === \T_INLINE_HTML) &&
        throw new \InvalidArgumentException(sprintf('The file "%s" does not contain PHP code', $filename));

        $namespace = $class = false;
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];
            if (!isset($token[1])) {
                continue;
            }

            switch ($token[0]) {
                case \T_NAMESPACE:
                    $namespace = true;

                    break;
                case \T_CLASS:
                    // Skip ::class constant and anonymous classes
                    $skipClassToken = false;
                    for ($j = $i - 1; $j > 0; --$j) {
                        if (!isset($tokens[$j][1])) {
                            if ('(' === $tokens[$j] || ',' === $tokens[$j]) {
                                $skipClassToken = true;
                            }
                            break;
                        }

                        if (\T_DOUBLE_COLON === $tokens[$j][0] || \T_NEW === $tokens[$j][0]) {
                            $skipClassToken = true;
                            break;
                        } elseif (!\in_array($tokens[$j][0], [\T_WHITESPACE, \T_DOC_COMMENT, \T_COMMENT])) {
                            break;
                        }
                    }

                    if (!$skipClassToken) {
                        $class = true;
                    }

                    break;
                case \T_NS_SEPARATOR:
                case \T_NAME_QUALIFIED:
                case \T_STRING:
                    if ($namespace === true) {
                        $namespace = $token[1];
                        while (isset($tokens[++$i][1], $nsTokens[$tokens[$i][0]])) {
                            $namespace .= $tokens[$i][1];
                        }
                    } elseif ($class === true) {
                        return "$namespace\\$token[1]";
                    }

                    break;
            }
        }

        return null;
    }
}
