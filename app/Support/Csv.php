<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class Csv
{
    /**
     * Read every row of the given CSV file, stripping a UTF-8 BOM if
     * present so the first column isn't corrupted (common with Excel
     * exports).
     *
     * @return array<int, array<int, string|null>>
     */
    public static function readRows(UploadedFile $file): array
    {
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false) {
            throw ValidationException::withMessages(['file' => 'The uploaded file could not be read.']);
        }

        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $contents);
        rewind($stream);

        $rows = [];

        while (($cells = fgetcsv($stream)) !== false) {
            $rows[] = $cells;
        }

        fclose($stream);

        return $rows;
    }
}
