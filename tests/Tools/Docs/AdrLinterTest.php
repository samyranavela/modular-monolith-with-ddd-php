<?php

declare(strict_types=1);

namespace App\Tests\Tools\Docs;

use App\Tools\Docs\AdrLinter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

final class AdrLinterTest extends TestCase
{
    #[Test]
    public function a_well_formed_adr_has_no_problem(): void
    {
        self::assertSame([], new AdrLinter()->lint('0001-use-a-signed-link.md', $this->adr()));
    }

    #[Test]
    #[TestWith(['use-a-signed-link.md'])]
    #[TestWith(['1-use-a-signed-link.md'])]
    #[TestWith(['0001-Use A Signed Link.md'])]
    #[TestWith(['0001-use-a-signed-link.txt'])]
    public function the_file_name_is_a_number_then_a_title_with_dashes(string $fileName): void
    {
        self::assertSame(
            [$fileName.': the file name must be NNNN-title-with-dashes.md'],
            new AdrLinter()->lint($fileName, $this->adr()),
        );
    }

    #[Test]
    #[TestWith(['status: draft', 'the status must be proposed, rejected, accepted, deprecated or superseded by ADR-NNNN'])]
    #[TestWith(['status: superseded', 'the status must be proposed, rejected, accepted, deprecated or superseded by ADR-NNNN'])]
    public function the_status_is_one_of_the_madr_values(string $status, string $problem): void
    {
        $adr = str_replace('status: accepted', $status, $this->adr());

        self::assertSame(['0001-use-a-signed-link.md: '.$problem], new AdrLinter()->lint('0001-use-a-signed-link.md', $adr));
    }

    #[Test]
    public function a_status_that_supersedes_an_adr_is_accepted(): void
    {
        $adr = str_replace('status: accepted', 'status: superseded by ADR-0007', $this->adr());

        self::assertSame([], new AdrLinter()->lint('0001-use-a-signed-link.md', $adr));
    }

    #[Test]
    public function the_date_is_written_year_month_day(): void
    {
        $adr = str_replace('date: 2026-11-27', 'date: 27/11/2026', $this->adr());

        self::assertSame(
            ['0001-use-a-signed-link.md: the date must be YYYY-MM-DD'],
            new AdrLinter()->lint('0001-use-a-signed-link.md', $adr),
        );
    }

    #[Test]
    #[TestWith(['## Context and Problem Statement'])]
    #[TestWith(['## Considered Options'])]
    #[TestWith(['## Decision Outcome'])]
    public function the_mandatory_sections_of_madr_are_present(string $section): void
    {
        $adr = str_replace($section, '## Something else', $this->adr());

        self::assertSame(
            ['0001-use-a-signed-link.md: the section "'.$section.'" is missing'],
            new AdrLinter()->lint('0001-use-a-signed-link.md', $adr),
        );
    }

    #[Test]
    public function consecutive_numbers_have_no_problem(): void
    {
        self::assertSame([], new AdrLinter()->lintNumbering(['0001-a.md', '0002-b.md', '0003-c.md']));
    }

    #[Test]
    public function a_missing_number_is_reported(): void
    {
        self::assertSame(
            ['the numbers of the ADRs must be consecutive from 0001: 0002 is missing'],
            new AdrLinter()->lintNumbering(['0001-a.md', '0003-c.md']),
        );
    }

    #[Test]
    public function a_number_used_twice_is_reported(): void
    {
        self::assertSame(
            ['the number 0002 is used by 0002-b.md and 0002-c.md'],
            new AdrLinter()->lintNumbering(['0001-a.md', '0002-b.md', '0002-c.md']),
        );
    }

    private function adr(): string
    {
        return <<<'MD'
            ---
            status: accepted
            date: 2026-11-27
            ---

            # Use a signed link

            ## Context and Problem Statement

            A problem.

            ## Considered Options

            * One option

            ## Decision Outcome

            Chosen option: "One option", because it is the only one.
            MD;
    }
}
