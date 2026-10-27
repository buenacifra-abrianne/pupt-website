<?php

namespace App\Support;

use RuntimeException;

/** Reads CMS values from mysqldump output. It never executes backup SQL. */
class CmsBackupSqlReader
{
    public function read($stream): array
    {
        $columns = [];
        $rows = [];
        $inSchema = false;
        $inCmsData = false;
        $foundSchema = false;
        $bytes = 0;
        $limit = (int) config('database_backups.history_max_sql_bytes', 536870912);

        while (($line = fgets($stream, 16777217)) !== false) {
            $bytes += strlen($line);
            if ($bytes > $limit || (strlen($line) === 16777216 && ! str_ends_with($line, "\n"))) {
                throw new RuntimeException('The database dump exceeds the CMS history read limit.');
            }
            if (preg_match('/^CREATE TABLE(?: IF NOT EXISTS)? `cms_contents`\s*\(/i', $line)) {
                $inSchema = true;
                $inCmsData = true;
                $foundSchema = true;
                $columns = [];

                continue;
            }
            if ($inSchema) {
                if (preg_match('/^\s*`([^`]+)`\s/', $line, $match)) {
                    $columns[] = $match[1];
                } elseif (preg_match('/^\)\s*(?:ENGINE|;)/i', $line)) {
                    $inSchema = false;
                }

                continue;
            }
            if (preg_match('/^CREATE TABLE|^DELIMITER\s|^UNLOCK TABLES;|^-- Dumping (?:routines|events|triggers)/i', $line)) {
                $inCmsData = false;
            }
            if (preg_match('/^-- Dumping data for table `([^`]+)`/', $line, $table)) {
                $inCmsData = $table[1] === 'cms_contents';
            }
            if (! $inCmsData) {
                continue;
            }
            if (! preg_match('/^INSERT INTO `cms_contents`\s*(\([^)]*\))?\s*VALUES\s*/i', $line, $match)) {
                continue;
            }
            $insertColumns = $columns;
            if (! empty($match[1])) {
                preg_match_all('/`([^`]+)`/', $match[1], $names);
                $insertColumns = $names[1];
            }
            if (array_diff(['tab_key', 'title', 'content'], $insertColumns) !== []) {
                throw new RuntimeException('The backup CMS schema is not supported.');
            }
            // mysqldump escapes newlines inside strings and writes each INSERT on one line.
            $values = substr($line, strlen($match[0]));
            $offset = 0;
            $length = strlen($values);
            $terminated = false;
            while ($offset < $length) {
                $this->whitespace($values, $offset);
                if (($values[$offset] ?? '') !== '(') {
                    throw new RuntimeException('The backup contains an incomplete CMS INSERT.');
                }
                $offset++;
                $tuple = [];
                do {
                    $tuple[] = $this->value($values, $offset);
                    $this->whitespace($values, $offset);
                    $separator = $values[$offset++] ?? '';
                } while ($separator === ',');
                if ($separator !== ')' || count($tuple) !== count($insertColumns)) {
                    throw new RuntimeException('The backup contains an invalid CMS row.');
                }
                $row = array_combine($insertColumns, $tuple);
                $tab = $row['tab_key'];
                if (! is_string($tab) || isset($rows[$tab])) {
                    throw new RuntimeException('The backup contains duplicate or invalid CMS pages.');
                }
                $rows[$tab] = (object) $row;
                $this->whitespace($values, $offset);
                $separator = $values[$offset++] ?? '';
                if ($separator === ';' && trim(substr($values, $offset)) === '') {
                    $terminated = true;
                    break;
                }
                if ($separator !== ',') {
                    throw new RuntimeException('The backup contains an incomplete CMS INSERT.');
                }
            }
            if (! $terminated) {
                throw new RuntimeException('The backup contains an incomplete CMS INSERT.');
            }
        }
        if (! $foundSchema || $inSchema || array_diff(['tab_key', 'title', 'content'], $columns) !== []) {
            throw new RuntimeException('This database backup does not contain a supported CMS contents table.');
        }

        return $rows;
    }

    private function whitespace(string $sql, int &$offset): void
    {
        while (isset($sql[$offset]) && ctype_space($sql[$offset])) {
            $offset++;
        }
    }

    private function value(string $sql, int &$offset): ?string
    {
        $this->whitespace($sql, $offset);
        if (($sql[$offset] ?? '') !== "'") {
            $start = $offset;
            while (isset($sql[$offset]) && ! in_array($sql[$offset], [',', ')'], true)) {
                $offset++;
            }
            $value = trim(substr($sql, $start, $offset - $start));
            if (strtoupper($value) === 'NULL') {
                return null;
            }
            if (preg_match('/^-?\d+(?:\.\d+)?$/D', $value)) {
                return $value;
            }
            throw new RuntimeException('The backup contains an unsupported CMS value.');
        }
        $offset++;
        $result = '';
        while (isset($sql[$offset])) {
            $char = $sql[$offset++];
            if ($char === "'") {
                if (($sql[$offset] ?? '') === "'") {
                    $result .= "'";
                    $offset++;

                    continue;
                }

                return $result;
            }
            if ($char === '\\') {
                if (! isset($sql[$offset])) {
                    break;
                }
                $escaped = $sql[$offset++];
                $result .= match ($escaped) {
                    '0' => "\0", 'b' => "\x08", 'n' => "\n", 'r' => "\r", 't' => "\t", 'Z' => "\x1a",
                    '%', '_' => '\\'.$escaped,
                    default => $escaped,
                };
            } else {
                $result .= $char;
            }
        }
        throw new RuntimeException('The backup contains an unterminated CMS string.');
    }
}
