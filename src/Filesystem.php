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
        !file_exists($filename) &&
        throw new \InvalidArgumentException("$filename is not an exist file.");

        $content = file_get_contents($filename);
        $content === false &&
        throw new \RuntimeException("An error occur while getting $filename content.");

        $tokens = token_get_all($content);
        (count($tokens) < 1 || T_INLINE_HTML === $tokens[0][0]) &&
        throw new \InvalidArgumentException("$filename is not a file that contains valid PHP code.");

        $namespace = $class = false;
        foreach ($tokens as $token) {
            if (!is_array($token) || !isset($token[1]))
                continue;

            // found flag, and will be getting namespace or class name
            if (true === $namespace && T_NAME_QUALIFIED === $token[0])
                $namespace = $token[1];
            elseif (true === $class && T_STRING === $token[0])
                return "$namespace\\$token[1]";

            // check namespace or class name flag
            if (T_NAMESPACE === $token[0])
                $namespace = true;
            elseif (T_CLASS === $token[0])
                $class = true;
        }

        return null;
    }
}
