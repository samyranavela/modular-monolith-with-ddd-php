<?php

declare(strict_types=1);

namespace App\Tools\Docs;

/**
 * Checks an ADR against the MADR template of docs/adr/adr-template.md: the file name, the metadata that is given
 * and the mandatory sections. The optional metadata and sections may be left out.
 */
final readonly class AdrLinter
{
    private const array MANDATORY_SECTIONS = [
        '## Context and Problem Statement',
        '## Considered Options',
        '## Decision Outcome',
    ];

    /**
     * @return list<string>
     */
    public function lint(string $fileName, string $content): array
    {
        if (1 !== preg_match('/^\d{4}-[a-z0-9]+(-[a-z0-9]+)*\.md$/', $fileName)) {
            return [$fileName.': the file name must be NNNN-title-with-dashes.md'];
        }

        $problems = [];

        $status = $this->metadata($content, 'status');
        if (null !== $status && 1 !== preg_match('/^(proposed|rejected|accepted|deprecated|superseded by ADR-\d{4})$/', $status)) {
            $problems[] = 'the status must be proposed, rejected, accepted, deprecated or superseded by ADR-NNNN';
        }

        $date = $this->metadata($content, 'date');
        if (null !== $date && 1 !== preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $problems[] = 'the date must be YYYY-MM-DD';
        }

        foreach (self::MANDATORY_SECTIONS as $section) {
            if (1 !== preg_match('/^'.preg_quote($section, '/').'$/m', $content)) {
                $problems[] = \sprintf('the section "%s" is missing', $section);
            }
        }

        return array_map(static fn (string $problem): string => $fileName.': '.$problem, $problems);
    }

    /**
     * @param list<string> $fileNames the names of the ADRs, the template excluded
     *
     * @return list<string>
     */
    public function lintNumbering(array $fileNames): array
    {
        $namesByNumber = [];
        foreach ($fileNames as $fileName) {
            $namesByNumber[substr($fileName, 0, 4)][] = $fileName;
        }

        $problems = [];
        foreach ($namesByNumber as $number => $names) {
            if (\count($names) > 1) {
                $problems[] = \sprintf('the number %s is used by %s', $number, implode(' and ', $names));
            }
        }

        $numbers = array_map(intval(...), array_keys($namesByNumber));
        for ($expected = 1; $expected < ($numbers ? max($numbers) : 0); ++$expected) {
            if (!\in_array($expected, $numbers, true)) {
                $problems[] = \sprintf(
                    'the numbers of the ADRs must be consecutive from 0001: %04d is missing',
                    $expected,
                );
            }
        }

        return $problems;
    }

    private function metadata(string $content, string $key): ?string
    {
        if (1 !== preg_match('/\A---\R(.*?)\R---\R/s', $content, $frontMatter)) {
            return null;
        }

        if (1 !== preg_match('/^'.$key.':\s*"?(.*?)"?\s*$/m', $frontMatter[1], $value)) {
            return null;
        }

        return $value[1];
    }
}
