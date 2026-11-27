<?php

declare(strict_types=1);

namespace App\Tests\Tools\Git;

use App\Tools\Git\CommitMessageChecker;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CommitMessageCheckerTest extends TestCase
{
    #[Test]
    public function a_message_with_a_subject_and_a_body_has_no_problem(): void
    {
        $message = "Register an account\n\nA free email address registers a pending member.\n";

        self::assertSame([], new CommitMessageChecker()->check($message, firstOfBranch: false));
    }

    #[Test]
    public function a_message_without_a_body_is_reported(): void
    {
        self::assertSame(
            ['the message needs a body under its subject'],
            new CommitMessageChecker()->check("Register an account\n", firstOfBranch: false),
        );
    }

    #[Test]
    public function the_first_commit_of_a_branch_starts_with_its_type(): void
    {
        $message = "Register an account\n\nA body.\n";

        self::assertSame(
            ['the subject of the first commit of a branch starts with its type, as [Feat] Description'],
            new CommitMessageChecker()->check($message, firstOfBranch: true),
        );
        self::assertSame([], new CommitMessageChecker()->check("[Feat] Register an account\n\nA body.\n", firstOfBranch: true));
    }

    #[Test]
    public function a_merge_commit_needs_neither_a_type_nor_a_body(): void
    {
        self::assertSame([], new CommitMessageChecker()->check("Merge branch 'main' into feat/x\n", firstOfBranch: true));
    }

    #[Test]
    public function a_message_with_accented_letters_is_reported_as_not_english(): void
    {
        $message = "Créer un compte\n\nUne adresse libre crée un compte.\n";

        self::assertSame(
            ['the message has accented letters: write it in English'],
            new CommitMessageChecker()->check($message, firstOfBranch: false),
        );
    }

    #[Test]
    public function the_comment_lines_of_the_editor_are_ignored(): void
    {
        $message = "Register an account\n\nA body.\n\n# Please enter the commit message, é\n";

        self::assertSame([], new CommitMessageChecker()->check($message, firstOfBranch: false));
    }
}
