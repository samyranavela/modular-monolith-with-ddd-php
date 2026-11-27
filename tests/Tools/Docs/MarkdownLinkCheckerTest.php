<?php

declare(strict_types=1);

namespace App\Tests\Tools\Docs;

use App\Tools\Docs\MarkdownLinkChecker;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class MarkdownLinkCheckerTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/markdown-links-'.bin2hex(random_bytes(4));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->directory);
    }

    #[Test]
    public function a_link_to_a_missing_file_is_reported(): void
    {
        $page = $this->directory.'/page.md';

        $problems = new MarkdownLinkChecker()->check($page, 'See [the glossary](missing.md).');

        self::assertSame([$page.': the link target missing.md does not exist'], $problems);
    }

    #[Test]
    public function a_link_to_an_existing_file_is_not_reported_whatever_its_fragment(): void
    {
        touch($this->directory.'/glossary.md');

        $problems = new MarkdownLinkChecker()->check(
            $this->directory.'/page.md',
            'See [the glossary](glossary.md) and [a term](glossary.md#term).',
        );

        self::assertSame([], $problems);
    }

    #[Test]
    #[TestWith(['[site](https://example.com/page)'])]
    #[TestWith(['[mail](mailto:someone@example.com)'])]
    #[TestWith(['[section](#a-section)'])]
    public function a_link_that_is_not_a_relative_file_is_not_checked(string $markdown): void
    {
        self::assertSame([], new MarkdownLinkChecker()->check($this->directory.'/page.md', $markdown));
    }

    #[Test]
    public function a_link_in_code_is_not_checked(): void
    {
        $markdown = "```md\n[a link](nowhere.md)\n```\n\nInline `[a link](nowhere.md)` too.";

        self::assertSame([], new MarkdownLinkChecker()->check($this->directory.'/page.md', $markdown));
    }
}
