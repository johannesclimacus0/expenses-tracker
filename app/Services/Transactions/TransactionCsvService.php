<?php

namespace App\Services\Transactions;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

final class TransactionCsvService
{
    private const HEADERS = [
        'occurred_at',
        'type',
        'amount',
        'category',
        'description',
    ];

    public function read(UploadedFile $file): array
    {
        $contents = file_get_contents($file->getRealPath());

        if ($contents === false || $contents === '') {
            $this->fail('CSV-файл пуст или не читается');
        }

        $contents = $this->toUtf8($contents);
        $contents = str_starts_with($contents, "\xEF\xBB\xBF")
            ? substr($contents, 3)
            : $contents;

        $handle = fopen('php://temp', 'w+b');
        fwrite($handle, $contents);
        rewind($handle);

        $firstLine = trim((string) fgets($handle));
        $hasSeparatorLine = str_starts_with(mb_strtolower($firstLine), 'sep=');
        $delimiter = $hasSeparatorLine
            ? mb_substr($firstLine, 4, 1)
            : (substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',');
        $headers = $hasSeparatorLine
            ? fgetcsv($handle, null, $delimiter, '"', '')
            : str_getcsv($firstLine, $delimiter, '"', '');

        $headers = array_map(
            fn ($header) => mb_strtolower(trim((string) $header)),
            $headers ?: [],
        );

        if ($headers !== self::HEADERS) {
            fclose($handle);
            $this->fail('Ожидаются колонки: '.implode(', ', self::HEADERS));
        }

        $rows = [];
        $line = $hasSeparatorLine ? 2 : 1;

        while (($values = fgetcsv($handle, null, $delimiter, '"', '')) !== false) {
            $line++;

            if (array_filter($values, fn ($value) => trim((string) $value) !== '') === []) {
                continue;
            }

            if (count($values) !== count(self::HEADERS)) {
                fclose($handle);
                $this->fail("Строка {$line}: неверное количество колонок");
            }

            if (count($rows) >= 10_000) {
                fclose($handle);
                $this->fail('За один раз можно импортировать не больше 10000 транзакций');
            }

            $values = array_map(function ($value): ?string {
                $value = trim((string) $value);

                return $value === '' ? null : $value;
            }, $values);

            $rows[] = [
                'line' => $line,
                'values' => array_combine(self::HEADERS, $values),
            ];
        }

        fclose($handle);

        if ($rows === []) {
            $this->fail('В CSV-файле нет транзакций');
        }

        return $rows;
    }

    public function write(iterable $transactions, mixed $stream): void
    {
        fwrite($stream, "\xFF\xFE");
        $filter = stream_filter_append($stream, 'convert.iconv.UTF-8/UTF-16LE', STREAM_FILTER_WRITE);

        fwrite($stream, "sep=;\r\n");
        fputcsv($stream, self::HEADERS, ';', '"', '', "\r\n");

        foreach ($transactions as $transaction) {
            fputcsv($stream, [
                $transaction->occurred_at->format('Y-m-d H:i:s'),
                $transaction->type->value,
                $transaction->amount,
                $transaction->category?->name,
                $transaction->description,
            ], ';', '"', '', "\r\n");
        }

        stream_filter_remove($filter);
    }

    private function toUtf8(string $contents): string
    {
        if (str_starts_with($contents, "\xFF\xFE")) {
            return mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16LE');
        }

        if (str_starts_with($contents, "\xFE\xFF")) {
            return mb_convert_encoding(substr($contents, 2), 'UTF-8', 'UTF-16BE');
        }

        return $contents;
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['csv' => $message]);
    }
}
