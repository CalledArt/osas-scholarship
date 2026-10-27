<?php

namespace App\Support;

use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\Process;
use RuntimeException;

/**
 * The qpdf command-line tool, which the applications report uses to put
 * students' own PDFs into it page for page, at their original page size.
 *
 * dompdf can draw a page but cannot include another PDF, so the report is
 * assembled from separate PDFs here. qpdf is installed in both Docker images.
 * It exits 3 when it repaired something and carried on, which counts as success.
 */
class Qpdf
{
    /**
     * Join the given PDFs, in order, into one file.
     *
     * @param  array<int, string>  $inputs
     */
    public function merge(array $inputs, string $output): void
    {
        $result = Process::timeout(240)->run(['qpdf', '--empty', '--pages', ...$inputs, '--', $output]);

        if (! $this->succeeded($result) || ! is_file($output)) {
            throw new RuntimeException('qpdf could not merge the report: '.trim($result->errorOutput()));
        }
    }

    /**
     * Rewrite an uploaded PDF without its encryption, repairing what qpdf can.
     * False when the file cannot be read, such as one locked with a password.
     */
    public function normalize(string $input, string $output): bool
    {
        $result = Process::timeout(60)->run(['qpdf', '--decrypt', $input, $output]);

        return $this->succeeded($result) && is_file($output);
    }

    private function succeeded(ProcessResult $result): bool
    {
        return in_array($result->exitCode(), [0, 3], true);
    }
}
