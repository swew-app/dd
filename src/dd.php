<?php

declare(strict_types=1);


if (!function_exists('__valueToString')) {
    function __valueToString(mixed $value): string
    {
        if (\is_string($value)) {
            return '"' . $value . '"';
        }

        if (is_null($value)) {
            return 'null';
        }

        if (true === $value) {
            return 'true';
        }

        if (false === $value) {
            return 'false';
        }

        if (\is_array($value)) {
            return var_export($value, true) ?? 'array';
        }

        if (\is_object($value)) {
            if (\method_exists($value, '__toString')) {
                return \get_class($value) . ': ' . __valueToString($value->__toString());
            }

            if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable) {
                return \get_class($value) . ': ' . __valueToString($value->format('c'));
            }

            return \get_class($value);
        }

        if (\is_resource($value)) {
            return 'resource';
        }

        return (string) $value;
    }
}

if (!function_exists('__dump')) {
    function __dump(): void
    {
        $str = '';
        try {
            foreach (func_get_args() as $x) {
                $str = $str . "\n" . var_export($x, true);
            }
        } catch (Exception) {
            ob_start();
            foreach (func_get_args() as $x) {
                echo "\n";
                var_dump($x);
            }
            $str = ob_get_clean();
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 2);
        $line = $backtrace[1]['line'];
        $file = $backtrace[1]['file'];

        if (defined('STDIN')) {
            echo "\n 🔍\033[0;33m ⮕ \033[0;36m$file\033[90m:$line\033[0m\n";
            echo $str . "\n";
            return;
        }

        $str = preg_replace(
            "/\[([^\]]+)\] =>/",
            '<b style="color:#888">[</b><span style="color:#17661c">$1</span><b style="color:#888">]</b> <span style="color:#999">=></span>',
            $str
        );
        echo "<pre> 🔍 >>> $file:$line \n";
        echo $str . "</pre>";
    }
}

if (!function_exists('__dump_trace')) {
    function __dump_trace(): void
    {
        $backtrace = array_slice(debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT), 2);
        $formattedTrace = [''];

        foreach ($backtrace as $v) {
            if (!isset($v['file'])) {
                continue;
            }

            $msg = '➫' . $v['file'] . ':' . $v['line'] . "\n";
            if (!empty($v['class'])) {
                $msg .= $v['class'];
                $msg .= $v['type'] ?? '';
            }

            if (!empty($v['function'])) {
                $msg .= $v['function'];
            }

            if (isset($v['args'])) {
                if (count($v['args']) === 0) {
                    $msg .= '()';
                } else {
                    $arr = array_map('__valueToString', $v['args']);
                    $msg .= '(' . implode(', ', $arr) . ')';
                }
            }

            $formattedTrace[] = $msg;
        }

        if (defined('STDIN')) {
            echo implode("\n\n", $formattedTrace);
            return;
        }

        echo '<pre>' . implode("\n\n", $formattedTrace) . '</pre>';

    }
}

if (!function_exists('d')) {
    function d(): void
    {
        $args = func_get_args();
        __dump(...$args);
    }
}

if (!function_exists('dd')) {
    function dd(): void
    {
        $args = func_get_args();
        __dump(...$args);
        die(0);
    }
}

if (!function_exists('dds')) {
    function dds(): void
    {
        $args = func_get_args();
        __dump(...$args);
        __dump_trace();
        die(0);
    }
}
