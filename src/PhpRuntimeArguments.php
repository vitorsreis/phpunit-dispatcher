<?php

namespace PUD;

class PhpRuntimeArguments
{
    private $tokens;

    public function __construct($tokens = null)
    {
        $this->tokens = $tokens;
    }

    public function arguments()
    {
        return $this->extractDefinitions($this->tokens === null ? $this->currentProcessTokens() : $this->tokens);
    }

    private function extractDefinitions($tokens)
    {
        if (!is_array($tokens) || !$tokens) {
            return array();
        }

        array_shift($tokens);
        $arguments = array();
        $count = count($tokens);

        for ($index = 0; $index < $count; $index++) {
            $token = $tokens[$index];

            if ($token === '-d') {
                $value = isset($tokens[$index + 1]) ? $tokens[$index + 1] : null;
                if (is_string($value) && $value !== '') {
                    $arguments[] = '-d' . $value;
                    $index++;
                }
                continue;
            }

            if (strpos($token, '-d') === 0 && strlen($token) > 2) {
                $arguments[] = $token;
                continue;
            }

            if (strpos($token, '-') === 0) {
                continue;
            }

            break;
        }

        return $arguments;
    }

    private function currentProcessTokens()
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            return $this->windowsProcessTokens();
        }

        $cmdline = '/proc/' . getmypid() . '/cmdline';
        if (is_file($cmdline)) {
            $contents = @file_get_contents($cmdline);
            if (is_string($contents) && $contents !== '') {
                return array_values(array_filter(explode("\0", $contents), function ($token) {
                    return $token !== '';
                }));
            }
        }

        return array();
    }

    private function windowsProcessTokens()
    {
        $commandLine = $this->windowsCommandLine();
        return $commandLine === null ? array() : $this->tokenizeWindowsCommandLine($commandLine);
    }

    private function windowsCommandLine()
    {
        $query = '(Get-CimInstance Win32_Process -Filter "ProcessId=' . getmypid() . '").CommandLine';
        $command = 'powershell.exe -NoProfile -NonInteractive -Command ' . escapeshellarg($query);
        $output = @shell_exec($command);

        if (!is_string($output)) {
            return null;
        }

        $output = trim($output);
        return $output === '' ? null : $output;
    }

    private function tokenizeWindowsCommandLine($commandLine)
    {
        $tokens = array();
        $current = '';
        $inQuotes = false;
        $length = strlen($commandLine);

        for ($index = 0; $index < $length; $index++) {
            $char = $commandLine[$index];
            if ($char === '"') {
                $inQuotes = !$inQuotes;
                continue;
            }

            if (!$inQuotes && ctype_space($char)) {
                if ($current !== '') {
                    $tokens[] = $current;
                    $current = '';
                }
                continue;
            }

            $current .= $char;
        }

        if ($current !== '') {
            $tokens[] = $current;
        }

        return $tokens;
    }
}
