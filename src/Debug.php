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
            "\r\n%s\r\nTHROWN: %s\r\nCODE: %d\r\nPOSITION: %s(%d)\r\nMESSAGE: %s\r\n%s\r\n",
            static::getCurrTime(), get_class($t), $t->getCode(), $t->getFile(),
            $t->getLine(), $t->getMessage(), $t->getTraceAsString()
        );

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
