# Mago as linter, formatter and architecture guard: parity with php-cs-fixer `@Symfony`

Research ticket: issue #3 (map: issue #1). Facts only; no decision is taken here.

- Mago version researched: **1.59.0** (latest GitHub release, published 2026-10-25) [S1].
- php-cs-fixer reference: `@Symfony` rule set as defined on `master` (`3.95.31-DEV`; latest tag `v3.95.30`) [S5].
- Project context: Symfony 8.1, PHP >= 8.5 (`composer.json`).
- Evidence tags: **[V]** = verified by running the Mago 1.59.0 `x86_64-unknown-linux-gnu` release binary on small fixtures (see "Method"); **[D]** = stated in Mago docs/source but not executed; **[N]** = no mention found in Mago docs/source (treated as not covered).

## 1. Method and sources

Primary sources only:

| Id | Source |
| :-- | :-- |
| S1 | Mago releases and repository, tag `1.59.0`: <https://github.com/carthage-software/mago/releases/tag/1.59.0> |
| S2 | Mago docs (Markdown sources inside the repo at the tag): `docs/content/en/tools/{formatter,linter,guard}/*.md`, `docs/content/en/guide/*.md` (published at <https://mago.carthage.software>) |
| S3 | Mago source: `crates/guard/src/{matcher.rs,path.rs,settings.rs}`, `crates/guard/src/perimeter/{checker.rs,mod.rs}`, `crates/formatter/src/presets.rs` |
| S4 | Mago linter rule reference: `docs/content/en/tools/linter/rules.md` (190 rules, 9 categories) and `mago lint --list-rules --json` |
| S5 | PHP-CS-Fixer repository (`master`): `src/RuleSet/Sets/{SymfonySet,SymfonyRiskySet,PERCS3x0Set,PERCS2x0Set,PERCS1x0Set,PSR12Set,PSR2Set,PSR1Set}.php`, `doc/ruleSets/Symfony.rst` |

Verification: the release binary was run on throwaway fixtures (outside the repo) for formatter output, lint output with `--only <rule>`, `--fix`, and guard output. No Mago code was run against this repo's `src/`, which only contains `Kernel.php`.

## 2. What Mago can do today (1.59.0)

Mago is a single Rust binary (also distributed through Composer as `carthage-software/mago`, a thin wrapper that downloads the matching binary on first run) [S2: `guide/installation.md`]. Relevant tools:

| Tool | Command | Facts |
| :-- | :-- | :-- |
| Formatter | `mago fmt` (alias of `mago format`) | Parse, discard formatting, reprint from CST (Prettier/rustfmt/Black approach). PER-CS by default. Flags: `--check`, `--dry-run`, `--stdin-input`, `--staged`. Pragmas `@mago-format-ignore`, `-ignore-next`, `-ignore-start/-end` [S2]. |
| Linter | `mago lint` | 190 rules in 9 categories; per-rule `enabled`, `level`, `exclude`, and rule-specific options; `--fix` with `--unsafe` / `--potentially-unsafe` / `--format-after-fix`; `--only <rule>`; baseline support; `-s` semantics-only mode (parser + semantic checker, replaces `php -l`) [S2, S4]. |
| Guard | `mago guard` | Perimeter guard (dependency edges between namespaces) and structural guard (naming, modifiers, supertypes, attributes). Self-described as covering "the same ground as deptrac and arkitect" [S2: `guard/overview.md`]. |
| Analyzer | `mago analyze` | Out of scope here (type-level analysis). |

Other facts:

- Config file: `mago.toml` (also yaml/yml/json), `extends` directive for shared configs (since 1.25), JSON schema published per version [S2: `guide/configuration.md`].
- Formatter presets in docs: `default` (PER-CS), `psr-12`, `laravel`, `drupal` [S2]. Source enum additionally contains `Pint`, `Tempest`, `Hack` [S3: `presets.rs`]. **There is no Symfony preset.**
- Linter integrations (opt-in rule packs): `symfony`, `phpunit`, `doctrine`, `psl`, and others [S2: `linter/integrations.md`]. The Symfony integration adds 4 rules: `final-controller`, `no-service-state-mutation`, `prefer-interface`, `prefer-pre-increment` [S4]. None of them is a formatting rule.
- Of the 190 rules, 118 are enabled by default with `integrations = ["symfony", "phpunit"]` [V: `--list-rules --json`]. Notable opt-in (disabled by default) rules used below: `yoda-conditions`, `prefer-pre-increment`, `no-redundant-else`, `no-null-property-init`, `no-short-bool-cast`, `no-alternative-syntax`, `switch-continue-to-break`, `no-unused-closure-capture`, `method-name` [V].
- `mago lint --pedantic` is documented as enabling every rule, but in the test it did not report `yoda-conditions`, `no-redundant-else`, `prefer-pre-increment`, `ambiguous-function-call` on code that violates them, while `--only <rule>` with the rule enabled in config did [V]. Do not rely on `--pedantic` as an "all rules" switch without re-checking.
- The formatter has **no PHPDoc handling**: docblocks are left untouched [V], and no option mentions docblocks [D]. The only docblock-related lint rules are `valid-docblock` (syntax errors in `{@see}`/`{@link}`) and `missing-docs` (presence) [S4].
- Formatter is deterministic by construction: it ignores the original layout (with a few `preserve-breaking-*` opt-ins) [S2]. php-cs-fixer, in contrast, is a set of independent token fixers that each touch one concern.

## 3. Parity table: `@Symfony` (php-cs-fixer) vs Mago

`@Symfony` = `@PER-CS3x0` + the rules in `SymfonySet.php`; `@PER-CS3x0` extends `@PER-CS2x0` -> `@PER-CS1x0` -> `@PSR12` -> `@PSR2` -> `@PSR1` [S5]. `@Symfony` is the **non-risky** set; `@Symfony:risky` is a separate set (section 3.7).

Status: **covered** = Mago produces/enforces the same result (by formatter output or an equivalent rule, possibly after a config option); **partial** = close but different semantics, scope, or opt-in only; **not covered** = no Mago equivalent found.

Where the column "Mago setting" names an option, it is a `[formatter]` option unless prefixed `lint:`. A formatter option "by reprint" means Mago always prints that way and has no switch.

### 3.1 Whitespace, layout, syntax (formatter territory)

| php-cs-fixer rule (`@Symfony` value) | Status | Mago setting / evidence |
| :-- | :-- | :-- |
| `@PER-CS3x0` base (indentation_type, line_ending, braces_position, control_structure_*, class_definition, no_trailing_whitespace, single_blank_line_at_eof, lowercase_cast, no_closing_tag, blank_line_after_opening_tag, etc.) | covered | Default formatter is PER-CS [D]. Defaults confirmed on fixtures: 4 spaces, LF, braces, `else` on same line as `}`, blank line after `<?php`, blank line between method members, `(INT)` -> `(int)` [V]. |
| `binary_operator_spaces` | covered | By reprint; `align-assignment-like = false` (default) means no alignment [V: `'b'=>3` -> `'b' => 3`]. |
| `concat_space` (default `spacing: none`) | covered | `space-around-concatenation-binary-operator = false` -> `'a'.$b.'c'` [V]. Mago default is the PER-CS `one` space. |
| `blank_line_before_statement` (`return`) | covered | `empty-line-before-return = true` [V: blank line inserted before `return $a;` after other statements; none when `return` is first in its block]. |
| `braces_position` (`allow_single_line_anonymous_functions`, `allow_single_line_empty_anonymous_classes`) | partial | `closure-brace-style`, `inline-empty-anonymous-class-braces` [D]; single-line anonymous functions are reflowed, not preserved [V: `function () use ($a) { return 1; }` expanded]. |
| `class_attributes_separation` (`method: one`) | covered | `empty-line-after-method = true`, `separate-class-like-members = true` (defaults) [V]. |
| `class_definition` (`single_line`) | covered | By reprint [V: `extends  Base` -> `extends Base`]. |
| `declare_parentheses`, `semicolon_after_instruction`, `no_singleline_whitespace_before_semicolons`, `no_empty_statement` (part), `space_after_semicolon`, `no_unneeded_braces`, `no_leading_namespace_whitespace`, `clean_namespace` | covered | By reprint [V for `for (;;)`, semicolons, namespace layout]. |
| `empty_loop_body` (`braces`) | partial | `inline-empty-control-braces = false` gives `for (;;) {\n}` [V]; but `while (true);` stayed as is [V]. |
| `empty_loop_condition` | partial | Closest is `lint: prefer-while-loop` (flags `for (;;)` and suggests `while`) [V]; different rewrite target than php-cs-fixer (`while (true)`), no fix tested. |
| `function_declaration` (`closure_fn_spacing: one`) | covered | `space-before-closure-parameter-list-parenthesis = true` (default) and `space-before-arrow-function-parameter-list-parenthesis = true` -> `fn ($q) => $q` [V]. Mago default for arrow functions is `fn($q)`. |
| `include` | covered | By reprint [V: `include('a.php')` -> `include 'a.php'`]. |
| `linebreak_after_opening_tag` | covered | `opening-tag-on-own-line`, `empty-line-after-opening-tag` (defaults) [V]. |
| `method_argument_space` (`on_multiline: ignore`, `after_heredoc`) | partial | Mago reflows arguments by width; `preserve-breaking-argument-list = true` keeps an author-written multi-line list [V]. Whether a single-line call is kept single-line depends on width, not on the author [V]. |
| `no_extra_blank_lines` (tokens list) | covered | By reprint (blank lines normalized) [D]. |
| `no_multiline_whitespace_around_double_arrow`, `no_spaces_around_offset`, `no_trailing_comma_in_singleline`, `no_whitespace_before_comma_in_array`, `whitespace_after_comma_in_array`, `trim_array_spaces`, `normalize_index_brace`, `object_operator_without_whitespace`, `type_declaration_spaces`, `ternary_operator_spaces`, `cast_spaces`, `attribute_block_no_spaces`, `array_indentation` | covered | By reprint [V: `$arr [0]` -> `$arr[0]`, `array( 'a' , 'b' )` -> `array('a', 'b')`, `$a ? : 1` -> `$a ?: 1`]. |
| `unary_operator_spaces` | covered | `space-after-*-unary-prefix-operator` options, defaults give `!$a` [V]. |
| `new_with_parentheses` | covered | `parentheses-in-new-expression = true` -> `new \stdClass()` [V]. |
| `no_unneeded_control_parentheses` | partial | Formatter removed parens in `include(...)`, `echo(...)` [V]; other statements (`return (...)`, `clone`, `yield`, `switch_case`) rely on `lint: no-redundant-parentheses` (enabled by default, has a fix) [D]; not tested per statement. |
| `operator_linebreak` (`only_booleans`) | partial | `line-before-binary-operator = true` applies to **all** binary operators when a binary expression breaks [D]; Symfony restricts it to booleans. |
| `single_quote` | covered | `single-quote = true` (default) [V]. |
| `single_line_throw` | covered | By reprint, width-driven [V: `throw new X(\n'msg'\n)` -> one line]. |
| `single_line_comment_style` (`hash`) | covered | Formatter converts `#` -> `//` [V]; `lint: no-hash-comment` (default on) also flags it [V list]. |
| `single_line_comment_spacing` | not covered | [N]. |
| `single_space_around_construct`, `statement_indentation`, `switch_case_*`, `elseif`, `control_structure_continuation_position` | covered | By reprint [D]. `elseif` vs `else if` normalization not documented [N]. |
| `single_import_per_statement`, `no_unneeded_import_alias` | covered | `expand-use-groups = true` (default) [V: `use Alpha\A, Alpha\B;` and `use Beta\{C, D};` split into single imports]. |
| `ordered_imports` (`class, function, const`; `alpha`) | partial | `sort-uses = "alphanumeric-ascending"` (default). Fixture output ordered classes, then `use function`, then `use const`, with a blank line between kinds [V] (`separate-use-types = true`, matches PSR-12 `blank_line_between_import_groups`). The exact comparison (php-cs-fixer `alpha`) versus Mago `alphanumeric` is not documented to be identical [N]. |
| `trailing_comma_in_multiline` (`arrays`, `array_destructuring`, `match`, `parameters`; **`arguments` excluded**) | partial | Mago has a single boolean `trailing-comma` [D]. With it enabled, multi-line **call arguments also get a trailing comma** [V: `foo(\n1,\n2\n)` -> `...2,\n)`]. Symfony explicitly excludes arguments. No per-construct switch exists. |
| `types_spaces`, `nullable_type_declaration`, `ordered_types` (`null_adjustment: always_last`, PER-CS3) | partial | `null-type-hint` has `question` (`?T`), `null_pipe`, `null_pipe_last` (`T\|null`, reorders unions) [D]. Sorting of non-null union members is not offered. |
| `single_class_element_per_statement` | not covered | `separate-trait-use` only covers trait `use` [D]; property/const multi-declarations [N]. |
| `modifier_keywords`, `ordered_class_elements` (`use_trait`), `lowercase_static_reference`, `short_scalar_cast`, `no_blank_lines_after_class_opening` | partial | `(INT)` lowercased [V]; modifier order and `(integer)` -> `(int)` not documented [N]; `empty-line-after-class-like-open = false` default removes the blank line after `{` [D]. `sort-class-methods` exists but orders methods, not modifiers [D]. |
| `no_break_comment` | not covered | [N]. |

### 3.2 Casing and normalization

| Rule | Status | Evidence |
| :-- | :-- | :-- |
| `constant_case` (`true/false/null` lowercase) | covered | `uppercase-literal-keyword = false` (default) [V: `TRUE` -> `true`]. |
| `lowercase_keywords` | covered | `lint: lowercase-keyword` (default on, fix available) [D]. |
| `native_type_declaration_casing` | covered | `lint: lowercase-type-hint` (default on) [D]. |
| `native_function_casing` | not covered | [V: `STRLEN($a)` unchanged by `fmt`, no lint issue]. |
| `integer_literal_case` | not covered | [V: `0XFF` unchanged]. |
| `magic_constant_casing` | not covered | [V: `__class__` unchanged]. |
| `magic_method_casing`, `class_reference_name_casing` | not covered | [N]. |

### 3.3 Language-level rewrites handled by lint rules

| php-cs-fixer rule | Status | Mago equivalent / evidence |
| :-- | :-- | :-- |
| `array_syntax` (short, PER-CS2) | covered | `lint: array-style` (default on) with fix [V: `array(1,2)` -> `[1, 2]`]. The formatter alone does **not** convert it [V]. |
| `backtick_to_shell_exec` | partial | `lint: no-shell-execute-string` (error, default on) flags it; autofix not documented [D]. |
| `no_alias_language_construct_call` (`die` -> `exit`) | not covered | [N]. |
| `no_alternative_syntax` | partial | `lint: no-alternative-syntax` exists but is disabled by default; it did not fire until enabled [V: not reported in default run]. |
| `no_binary_string` | covered | `lint: no-redundant-binary-string-prefix` (default on) [D]. |
| `no_empty_comment` | covered | `lint: no-empty-comment` (default on) [D]. |
| `no_mixed_echo_print` (`echo`) | not covered | [V: `print 'x';` unchanged, no lint issue]. |
| `no_null_property_initialization` | covered | `lint: no-null-property-init` (opt-in) with fix [V: `protected $x = null;` -> `protected $x;`]. Rule description limits it to untyped properties, as does the php-cs-fixer rule. |
| `no_short_bool_cast` | covered | `lint: no-short-bool-cast` (opt-in) [D]. |
| `no_unset_cast` | partial | `(unset)` is a **semantic error** under php-version 8.5 [V]; no autofix (removal changes behavior). |
| `no_unused_imports` | covered | `lint: no-redundant-use` (default on); `--fix` removes unused imports [V]. |
| `no_useless_concat_operator` | covered | `lint: no-redundant-string-concat` (default on) [V: flags `'x' . 'y'`]. |
| `no_useless_else` | covered | `lint: no-redundant-else` (opt-in) with fix [V]. `lint: no-else-clause` (default on) is stricter: flags every `else`/`elseif`. |
| `no_useless_nullsafe_operator` | covered | `lint: no-redundant-nullsafe` (default on) [D]. |
| `no_useless_return` | not covered | [V: trailing `return;` not reported]. |
| `lambda_not_used_import` | partial | `lint: no-unused-closure-capture` (opt-in) [D]. |
| `nullable_type_declaration_for_default_null_value` | covered | `lint: explicit-nullable-param` (default on) with fix [V: `string $b = null` -> `?string $b = null`]. |
| `increment_style` (`pre`) | covered | `lint: prefer-pre-increment` (opt-in; part of the Symfony integration) with fix [V]. |
| `standardize_increment` (`$i += 1` -> `++$i`) | not covered | `lint: use-compound-assignment` goes the opposite direction [D]. |
| `standardize_not_equals` (`<>` -> `!=`) | not covered | [V: `<>` unchanged by `fmt`]. `lint: identity-comparison` flags `<>` and suggests `!==`, a different rewrite [V]. |
| `switch_continue_to_break` | partial | `lint: switch-continue-to-break` exists, opt-in; did not fire on the fixture where `continue` sat in `default:` [V]. Not investigated further. |
| `yoda_style` | covered | `lint: yoda-conditions` (opt-in, default mode `require`) with fix [V: `$a == 1` -> `1 == $a`]. |
| `simple_to_complex_string_variable` (`${x}` -> `{$x}`) | not covered | [V: `braced-string-interpolation --fix` rewrote `${c}` to `{${c}}`, which is still the deprecated `${}` form]. |
| `protected_to_private` | partial | `lint: no-protected-in-final` (default on) covers `final` classes only [D]; php-cs-fixer also converts in non-final classes when safe. |
| `global_namespace_import` (all `false`) | n/a | Mago never auto-imports. Opposite-direction opt-in rules exist: `no-fully-qualified-global-class-like/-function/-constant`, `ambiguous-function-call/-constant-access` [D]. |
| `fully_qualified_strict_types` | not covered | [N]. |

### 3.4 PHPDoc rules (entirely outside Mago's formatter)

All of these are **not covered** [V: docblock with `@param integer`, `@return boolean`, `@inheritDocs` untouched by `fmt`, not reported by `lint`; D: no docblock option in formatter reference]:

`align_multiline_comment`, `general_phpdoc_tag_rename`, `no_blank_lines_after_phpdoc`, `no_empty_phpdoc`, `no_superfluous_phpdoc_tags`, `phpdoc_align`, `phpdoc_annotation_without_dot`, `phpdoc_indent`, `phpdoc_inline_tag_normalizer`, `phpdoc_no_access`, `phpdoc_no_alias_tag`, `phpdoc_no_package`, `phpdoc_no_useless_inheritdoc`, `phpdoc_order`, `phpdoc_return_self_reference`, `phpdoc_scalar`, `phpdoc_separation`, `phpdoc_single_line_var_spacing`, `phpdoc_summary`, `phpdoc_tag_type`, `phpdoc_to_comment`, `phpdoc_trim`, `phpdoc_trim_consecutive_blank_line_separation`, `phpdoc_types`, `phpdoc_types_order`, `phpdoc_var_annotation_correct_order`, `phpdoc_var_without_name`.

Adjacent Mago rules that are not equivalents: `valid-docblock` (syntax), `missing-docs` (presence, opt-in), `tagged-todo` / `tagged-fixme`.

### 3.5 PHPUnit

| Rule | Status | Evidence |
| :-- | :-- | :-- |
| `php_unit_fqcn_annotation` | not covered | [N]. |
| `php_unit_method_casing` (camelCase test methods) | partial | `lint: method-name` (opt-in, naming convention) [D]; not tested with PHPUnit specifics. |
| (not in `@Symfony`, FYI) | | `prefer-test-attribute`, `assertion-style`, `strict-assertions`, `use-specific-assertions` from the `phpunit` integration [S4]. |

### 3.6 Echo tags and misc

| Rule | Status | Evidence |
| :-- | :-- | :-- |
| `echo_tag_syntax` | not covered | [N] (the `no-inline` rule forbids inline HTML altogether; not an equivalent). |
| `encoding`, `full_opening_tag` (PSR-1) | partial | `lint: no-short-opening-tag`, `invalid-open-tag` (default on) [D]; file encoding not checked [N]. |

### 3.7 `@Symfony:risky` (separate set, FYI)

Not part of `@Symfony`, but projects usually run both. Notable members [S5: `SymfonyRiskySet.php`] and Mago counterparts (all [D] unless noted): `declare_strict_types` with `strategy: remove` is the **opposite** of `lint: strict-types` (default on, requires the declaration); `native_function_invocation` / `native_constant_invocation` ~ `ambiguous-function-call` / `ambiguous-constant-access` (opt-in; Mago also has an opposite `no-fully-qualified-global-*` family); `is_null` ~ `no-is-null`; `no_alias_functions` ~ `no-alias-function`; `modernize_strpos` ~ `str-contains` / `str-starts-with`; `static_lambda` ~ `prefer-static-closure` (default on, help level); `no_unneeded_final_method` ~ `no-redundant-final`; `error_suppression` ~ `no-error-control-operator`; `psr_autoloading` ~ `file-name` / `class-name` (naming only, not PSR-4 path mapping) [N for path mapping]; `ordered_traits`, `self_accessor`, `ternary_to_elvis_operator`, `void_return`, `get_class_to_class_keyword`, `implode_call`: no equivalent found [N].

### 3.8 Summary counts (qualitative)

- Formatter-by-reprint covers most of the PER-CS/PSR-12/Symfony whitespace and layout rules, with a few explicit options needed to match Symfony (section 4).
- Linter has a rule for roughly half of the non-PHPDoc "fixer" rules, mostly opt-in; many ship a fix.
- No coverage at all for the 27 PHPDoc rules, casing of native functions / magic constants / literals, `standardize_not_equals`, `no_useless_return`, `no_mixed_echo_print`, `simple_to_complex_string_variable`.

## 4. Draft `mago.toml` (closest to `@Symfony`)

Verified: the `[formatter]` block below was executed on fixtures (concat `.` without spaces, blank line before `return`, `fn ($q)`, preserved argument breaks, no table alignment) [V]. The `[linter]` block lists rules only by documented codes; names and defaults were checked with `--list-rules` and `--only`, behavior as per section 3 [V/D]. The `[guard]` block is verified in section 5.

```toml
#:schema https://mago.carthage.software/1.59.0/schema.json
version = "1"
php-version = "8.5"

[source]
paths = ["src", "tests"]

# ---------------------------------------------------------------- formatter
[formatter]
# preset omitted: "default" = PER-CS, the base of @Symfony
print-width = 120                    # @Symfony sets no line length; PER-CS soft limit
single-quote = true                  # single_quote
trailing-comma = true                # PARTIAL: also adds trailing commas to call arguments
space-around-concatenation-binary-operator = false   # concat_space (none)
empty-line-before-return = true      # blank_line_before_statement (return)
space-before-arrow-function-parameter-list-parenthesis = true  # function_declaration closure_fn_spacing: one
preserve-breaking-argument-list = true   # closest to method_argument_space on_multiline: ignore
array-table-style-alignment = false  # avoid column alignment (binary_operator_spaces single space)
null-type-hint = "question"          # nullable_type_declaration: ?T
sort-uses = "alphanumeric-ascending" # ordered_imports (alpha)
separate-use-types = true            # class / function / const groups
expand-use-groups = true             # single_import_per_statement
uppercase-literal-keyword = false    # constant_case

# ------------------------------------------------------------------- linter
[linter]
integrations = ["symfony", "phpunit"]   # symfony: final-controller, prefer-interface, no-service-state-mutation, prefer-pre-increment

[linter.rules]
# Opt-in rules that map to @Symfony fixers
yoda-conditions                = { enabled = true }   # yoda_style
prefer-pre-increment           = { enabled = true }   # increment_style
no-redundant-else              = { enabled = true }   # no_useless_else
no-null-property-init          = { enabled = true }   # no_null_property_initialization
no-short-bool-cast             = { enabled = true }   # no_short_bool_cast
no-alternative-syntax          = { enabled = true }   # no_alternative_syntax
switch-continue-to-break       = { enabled = true }   # switch_continue_to_break
no-unused-closure-capture      = { enabled = true }   # lambda_not_used_import
# Default-on rules that map to @Symfony fixers (kept on): array-style, no-redundant-use,
# no-redundant-string-concat, no-redundant-nullsafe, explicit-nullable-param,
# lowercase-keyword, lowercase-type-hint, no-hash-comment, no-empty-comment.
# Default-on rules stricter than or unrelated to @Symfony (review before adopting):
no-else-clause                 = { enabled = false }  # flags every else; stricter than no_useless_else
identity-comparison            = { enabled = false }  # forces ===, php-cs-fixer does not
literal-named-argument         = { enabled = false }
strict-types                   = { enabled = true }   # opposite of Symfony:risky declare_strict_types=remove

# -------------------------------------------------------------------- guard
[guard]
mode = "default"

[guard.perimeter.layers]
integration-events = ["App\\*\\IntegrationEvent\\**"]
framework = ["Symfony\\**", "Psr\\**"]

# Domain must not depend on Infrastructure, in every module (glob in deny-from)
[[guard.perimeter.restrictions]]
dependency = "App\\*\\Infrastructure\\**"
deny-from = ["App\\*\\Domain\\**"]
reason = "Domain must not depend on Infrastructure."

# One block per module (wildcards are NOT accepted in `namespace`)
[[guard.perimeter.rules]]
namespace = "App\\Billing\\Domain\\"
permit = ["@self", "@native", "App\\Billing\\Shared\\**"]
reason = "Billing Domain: own namespace and PHP built-ins only."

[[guard.perimeter.rules]]
namespace = "App\\Billing\\"
permit = ["@self", "@native", "@layer:framework", "@layer:integration-events"]
reason = "Modules may only depend on other modules' IntegrationEvent namespaces."

[[guard.perimeter.rules]]
namespace = "App\\Sales\\"
permit = ["@self", "@native", "@layer:framework", "@layer:integration-events"]
reason = "Modules may only depend on other modules' IntegrationEvent namespaces."

[[guard.structural.rules]]
on = "App\\*\\IntegrationEvent\\**"
target = "class"
must-be-final = true
must-be-readonly = true
must-be-named = "*Event"
reason = "Integration events are immutable public contracts."
```

Notes on the draft:

- `strict-types` and `no-else-clause` / `identity-comparison` lines are choices the project would need to make; they are shown because they are default-on and diverge from `@Symfony` behavior. This is not a recommendation.
- `[source].paths` should list the directories to scan; the repo currently has only `src/Kernel.php` and `src/Controller/`.

## 5. Guard: expressing the module boundaries

### 5.1 How the perimeter guard works (source-verified, [S3])

- Dependencies detected: `use` statements, `extends`, `implements`, trait `use`, attributes, property types, parameter types, return types (unions, intersections, nullable are recursed), `new`, static method calls, static property access, class constant access, function calls, constant access [S3: `perimeter/mod.rs`, `BreachVector`].
- **Not detected** (no walker found; also [V]): `catch (\Fqn $e)` types and `instanceof` with fully-qualified names, when the class is not imported via `use`; the fixture `catch (\App\Billing\Infrastructure\Boom $e)` and `$i instanceof \App\Billing\Infrastructure\DoctrineInvoiceRepository` produced no report. Generic/docblock types are also not walked [N].
- Evaluation order [S3: `checker.rs`, S2]: (1) `restrictions`; a match is an immediate breach; (2) the **single** `rules` entry whose `namespace` is the longest matching prefix of the source namespace is used (rules do **not** accumulate; a more specific rule shadows the general one) and its `permit` list is checked; (3) `layering` (if any); (4) if rules exist but none matches, breach `NoMatchingRule`.
- `[[guard.perimeter.rules]].namespace` must be `@global` or a plain namespace ending with `\`; **wildcards are rejected** [V: `namespace = "App\\*\\"` fails with `Invalid namespace: must be '@global' or a valid namespace ending with '\'`; S3: `path.rs`].
- Globs are accepted in `permit` paths, `restrictions.dependency`, `restrictions.allow-from` / `deny-from`, and layer aliases: `*` = one segment, `**` = zero or more, `{a,b}` braces, partial segments such as `*Repository` [S3: `matcher.rs`; V].
- There is **no capture/back-reference** between source and target (cannot say "target module must differ from source module").
- Output example and exit code: `error[disallowed-use]: Illegal dependency on ...` with the rule's `reason`; non-zero exit when any issue at or above `minimum-fail-level` (default `error`) [S2, V].

### 5.2 Boundary A: Domain must not depend on Infrastructure

Expressible generically with one restriction (see draft). Verified: `App\Billing\Domain\Invoice` importing and type-hinting `App\Billing\Infrastructure\DoctrineInvoiceRepository` produced two errors (`disallowed-use`, `disallowed-parameter-type`) with the configured reason [V]. Infrastructure -> Domain stays allowed [V: `DoctrineInvoiceRepository` referencing `Invoice` produced no issue].

Alternative using ordered layering (`[guard.perimeter] layering = [...]`) works on namespace prefixes without wildcards, so it fits a single-layer-tree (`App\Domain`, `App\Infrastructure`) rather than `App\<Module>\Domain` [D: `layering` doc and `get_layer_index` use prefix matching on explicit namespaces].

### 5.3 Boundary B: modules may only depend on other modules' IntegrationEvents

Expressible, with a per-module rule block (draft above). Verified on a two-module fixture (`Billing`, `Sales`):

| Case | Result [V] |
| :-- | :-- |
| `Sales\Application` uses `App\Billing\IntegrationEvent\InvoicePaid` | allowed |
| `Sales\Application` uses `App\Billing\Domain\Invoice` | 2 errors (`disallowed-use`, `disallowed-parameter-type`) |
| `Billing\Domain` uses `DateTimeImmutable` | allowed (`@native`) |
| `Billing\Domain` uses `App\Sales\IntegrationEvent\OrderPlaced` when a more specific `App\Billing\Domain\` rule exists without that permit | 2 errors (specific rule shadows the module rule) |
| Structural rule `must-be-final/readonly/named "*Event"` on `App\*\IntegrationEvent\**` | reports `must-be-named` on classes not ending in `Event` |

Limitations specific to this boundary:

1. One `rules` block per module (and per sub-layer where a stricter rule is needed); a new module requires a new block. A generic "any module except my own, only IntegrationEvent" rule cannot be written with wildcards in `namespace`.
2. Because the most specific rule wins and rules do not merge, a stricter `...\Domain\` rule must repeat every permit it wants (including framework/integration-event layers); this is what the draft's `@layer:` aliases are for.
3. Cross-module `catch`/`instanceof` on FQNs without `use` are not detected (5.1).
4. Restrictions can ban a **target** from selected source namespaces (e.g. `dependency = "App\\Billing\\**"`, `deny-from = ["App\\Sales\\**"]`) but cannot encode "except `IntegrationEvent`" in a single entry. The documented semantics are that restrictions are evaluated first and a permit cannot override them, and that `allow-from` only satisfies the restriction while ordinary rules must still allow the dependency [S2: configuration reference]. The tested solution therefore uses allow-lists (`rules`) for this boundary, not restrictions.
5. Symfony DI wiring (`config/services.yaml`, tags, `#[Autowire]` strings) and string class names are not analyzed.

## 6. Gaps to record in an ADR

Ordered by likely impact on a "replace php-cs-fixer" decision. Each is a fact, not a decision.

1. **No Symfony formatter preset.** Parity is achieved by option tuning on PER-CS; `trailing-comma` cannot exclude call arguments (Symfony excludes them), `operator_linebreak` is not limited to booleans, `ordered_types` cannot sort non-null union members.
2. **PHPDoc rules: 27 `@Symfony` rules have no counterpart** (section 3.4). Docblocks are never rewritten, only checked for syntax/presence.
3. **Casing normalizers missing**: `native_function_casing`, `integer_literal_case`, `magic_constant_casing`, `magic_method_casing`, `class_reference_name_casing`.
4. **Missing rewrites**: `standardize_not_equals`, `standardize_increment`, `no_useless_return`, `no_mixed_echo_print`, `no_alias_language_construct_call`, `simple_to_complex_string_variable` (Mago's fix produces `{${c}}`), `single_class_element_per_statement`, `echo_tag_syntax`, `single_line_comment_spacing`, `fully_qualified_strict_types`, `no_break_comment`, `php_unit_fqcn_annotation`.
5. **Many matching rules are lint rules, not formatter output, and several are opt-in** (`yoda-conditions`, `prefer-pre-increment`, `no-redundant-else`, `no-null-property-init`, `no-short-bool-cast`, `no-alternative-syntax`, `switch-continue-to-break`, `no-unused-closure-capture`). Correct code style then requires running both `mago fmt` and `mago lint --fix` (with `--format-after-fix`), unlike a single `php-cs-fixer fix`.
6. **Stricter default-on lint rules without `@Symfony` equivalent** (`no-else-clause`, `identity-comparison`, `strict-types`, `file-name`, `single-class-per-file` ...) will report issues on `@Symfony`-clean code. `@Symfony:risky` `declare_strict_types: remove` contradicts `strict-types`.
7. **Method argument handling differs by design**: deterministic reflow versus php-cs-fixer's `on_multiline: ignore`; only `preserve-breaking-*` opt-ins approximate it. Diffs on first run are expected.
8. **`--pedantic` did not surface opt-in rules** in the tested build (section 2); verify before using it as an audit mode.
9. **Guard**: wildcards not allowed in `rules[].namespace` (per-module blocks required); only the most specific rule applies (no accumulation); no source/target back-reference; `catch`/`instanceof` on non-imported FQNs, docblock types, DI configuration and string class names are not analyzed; auto-fix is "not currently meaningful" for guard [S2: `command-reference.md`].
10. **Tooling/ecosystem**: docs list editor recipes for VS Code, Zed, Helix; no PhpStorm integration page was found (only an `editor-url` template for `phpstorm://` links) [S2]. Distribution via Composer wrapper downloads a binary from GitHub on first run (network at install/first-run time) [S2: `guide/installation.md`]. The project is on 1.x with frequent minor releases (1.59.0 on 2026-10-25), and the docs reference options introduced in specific minors (e.g. `extends` since 1.25, per-rule glob `exclude` since 1.20, extension rule settings since 1.58); minimum version pinning matters.
11. **Docs/source discrepancy**: docs list formatter presets `default`, `psr-12`, `laravel`, `drupal`; the source enum also has `Pint`, `Tempest`, `Hack` [S2 vs S3].
12. **Scope of research**: Mago was run on fixtures, not on a real code base (this repo has no PHP beyond `Kernel.php`); rule behaviors on idiomatic Symfony code (attributes, readonly classes, property hooks) were not exercised beyond what the fixtures show.
