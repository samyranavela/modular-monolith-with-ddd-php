<?php

declare(strict_types=1);

// The rule test's input (autoload-dev classmap, for PHPStan's reflection): one violation next to the shapes that pass.

namespace App\Fixture\Contract {
    interface Published {}

    final readonly class MemberCreated {}

    final class Mutable {}

    readonly class Extensible {}
}

namespace App\Fixture\Application {
    class NotAContract {}
}
