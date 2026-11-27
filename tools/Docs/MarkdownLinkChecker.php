<?php

declare(strict_types=1);

namespace App\Tools\Docs;

/**
 * Reports the relative links of a Markdown page whose target file does not exist.
 */
final readonly class MarkdownLinkChecker
{
    /**
     * @return list<string>
     */
    public function check(string $path, string $markdown): array
    {
        preg_match_all('/\]\(([^)\s]+)\)/', $this->withoutCode($markdown), $matches);

        $problems = [];
        foreach ($matches[1] as $link) {
            $target = preg_replace('/[#?].*$/', '', $link) ?? $link;

            if ('' === $target || 1 === preg_match('/^[a-z][a-z0-9+.-]*:/i', $target)) {
                continue;
            }

            if (!file_exists(\dirname($path).'/'.$target)) {
                $problems[] = \sprintf('%s: the link target %s does not exist', $path, $target);
            }
        }

        return $problems;
    }

    private function withoutCode(string $markdown): string
    {
        $withoutBlocks = preg_replace('/^(```|~~~).*?^\1/ms', '', $markdown) ?? $markdown;

        return preg_replace('/`[^`\n]*`/', '', $withoutBlocks) ?? $withoutBlocks;
    }
}
