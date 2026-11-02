<?php

namespace Tests\Feature;

use App\Support\CmsBackupSqlReader;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class CmsBackupSqlReaderTest extends TestCase
{
    private function parse(string $values): array
    {
        $sql = "CREATE TABLE `cms_contents` (\n  `tab_key` varchar(80),\n  `title` varchar(255),\n  `content` longtext\n) ENGINE=InnoDB;\n".$values;
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, $sql);
        rewind($stream);
        try {
            return (new CmsBackupSqlReader)->read($stream);
        } finally {
            fclose($stream);
        }
    }

    public function test_mysql_escaped_values_and_multiple_rows_are_preserved_without_executing_sql(): void
    {
        $rows = $this->parse(<<<'SQL'
INSERT INTO `cms_contents` VALUES ('home','It\'s a title','First\nSecond\r\t\\path\0\Z, (parentheses); DROP TABLE users;'),('about',NULL,'it''s Unicode: Philippines 🇵🇭');
SQL);
        $this->assertSame("It's a title", $rows['home']->title);
        $this->assertSame("First\nSecond\r\t\\path\0\x1a, (parentheses); DROP TABLE users;", $rows['home']->content);
        $this->assertNull($rows['about']->title);
        $this->assertSame("it's Unicode: Philippines 🇵🇭", $rows['about']->content);
    }

    public function test_complete_inserts_can_use_a_different_column_order(): void
    {
        $rows = $this->parse("INSERT INTO `cms_contents` (`content`, `title`, `tab_key`) VALUES ('Body','Title','home');\n");
        $this->assertSame('Body', $rows['home']->content);
        $this->assertSame('Title', $rows['home']->title);
    }

    public function test_cms_insert_text_in_other_table_or_routine_sections_is_not_treated_as_page_data(): void
    {
        $rows = $this->parse(<<<'SQL'
INSERT INTO `cms_contents` VALUES ('home','Actual page','Actual content');
UNLOCK TABLES;
DELIMITER ;;
INSERT INTO `cms_contents` VALUES ('about','Routine text','Must not become a historical page');
SQL);
        $this->assertCount(1, $rows);
        $this->assertSame('Actual content', $rows['home']->content);
    }

    public function test_oversized_dumps_are_rejected(): void
    {
        config(['database_backups.history_max_sql_bytes' => 20]);
        $this->expectException(RuntimeException::class);
        $this->parse("INSERT INTO `cms_contents` VALUES ('home',NULL,'Body');\n");
    }

    public static function invalidInserts(): array
    {
        return [
            ['INSERT INTO `cms_contents` VALUES (\'home\',\'Title\',\'Unterminated);'],
            ['INSERT INTO `cms_contents` VALUES (\'home\',\'Title\',\'Body\'),'],
            ['INSERT INTO `cms_contents` VALUES (\'home\',\'Title\',\'Body\')'],
            ['INSERT INTO `cms_contents` VALUES (\'home\',\'Title\');'],
            ['INSERT INTO `cms_contents` VALUES (\'home\',\'Title\',CONCAT(\'bad\'));'],
            ['INSERT INTO `cms_contents` VALUES (\'home\',NULL,\'One\'),(\'home\',NULL,\'Two\');'],
        ];
    }

    #[DataProvider('invalidInserts')]
    public function test_unsupported_or_incomplete_sql_is_rejected(string $insert): void
    {
        $this->expectException(RuntimeException::class);
        $this->parse($insert);
    }
}
