<?php

declare(strict_types=1);

namespace Plattry\Utils;

use DateTime;
use Throwable;

/**
 * Class Debug
 * @package Plattry\Debug
 */
class Debug
{
    /**
     * Get current time formatted by Y-m-d H:i:s.u.
     * @return string
     */
    protected static function getCurrTime(): string
    {
        try {
            return (new DateTime('now'))->format('Y-m-d H:i:s.u');
        } catch (Throwable) {
            return date('Y-m-d H:i:s.u');
        }
    }

    /**
     * Send a throwable content to STDERR.
     * @param Throwable $t
     * @return void
     */
    public static function handleThrow(Throwable $t): void
    {
        $content = sprintf(
            "\r\nAn %s thrown with %s(%d) in %s(%d) at %s\r\n",
            $t::class, $t->getMessage(), $t->getCode(), $t->getFile(), $t->getLine(), static::getCurrTime()
        );

        $flags = JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE;

        foreach ($t->getTrace() as $index => $trace) {
            if (isset($trace['file']))
                $content .= sprintf(
                    "from %d: %s(%d) on %s(%s)\r\n",
                    $index, $trace['file'], $trace['line'], $trace['function'], json_encode($trace['args'], $flags)
                );
            elseif (isset($trace['class']))
                $content .= sprintf(
                    "from %d: %s on %s(%s)\r\n",
                    $index, $trace['class'], $trace['function'], json_encode($trace['args'], $flags)
                );
            else
                $content .= sprintf(
                    "from %d: on %s(%s)\r\n",
                    $index, $trace['function'], json_encode($trace['args'], $flags)
                );
        }

        fwrite(STDERR, $content);
    }

    /**
     * Send a message content to STDOUT.
     * @param string $msg
     * @return void
     */
    public static function handleMessage(string $msg): void
    {
        $content = sprintf("\r\n%s\r\n", $msg);

        fwrite(STDOUT, $content);
    }
}
