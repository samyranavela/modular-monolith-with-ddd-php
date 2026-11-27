<?php

declare(strict_types=1);

namespace App\Tools\Git;

/**
 * The rules of a commit message that a tool can check: a body under the subject, a type on the first commit of a
 * branch, and English (as far as accented letters tell).
 */
final readonly class CommitMessageChecker
{
    /**
     * @return list<string>
     */
    public function check(string $message, bool $firstOfBranch): array
    {
        $lines = array_values(array_filter(
            explode("\n", trim($message)),
            static fn (string $line): bool => !str_starts_with($line, '#'),
        ));
        $subject = $lines[0] ?? '';

        if (str_starts_with($subject, 'Merge ')) {
            return [];
        }

        $problems = [];

        if ('' === trim(implode("\n", \array_slice($lines, 1)))) {
            $problems[] = 'the message needs a body under its subject';
        }

        if ($firstOfBranch && 1 !== preg_match('/^\[[A-Za-z]+\] \S/', $subject)) {
            $problems[] = 'the subject of the first commit of a branch starts with its type, as [Feat] Description';
        }

        if (1 === preg_match('/[àâäçéèêëîïôöûùüÿœæ]/iu', implode("\n", $lines))) {
            $problems[] = 'the message has accented letters: write it in English';
        }

        return $problems;
    }
}
