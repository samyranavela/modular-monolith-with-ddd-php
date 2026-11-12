# Architecture tests

Source architecture rules mapped to their mechanism here. The module-boundary rules live in `deptrac.yaml`,
one block per module; immutability is checked by PHPStan max; a reflection test is used only when neither can
express the rule (naming, structure).

| Rule | Mechanism |
| :-- | :-- |
| Domain depends on no Infrastructure | Deptrac |
| Domain: own namespace + `SharedKernel\Domain` only (Payments: + Patchlevel) | Deptrac (per module) |
| Application: own Domain, SharedKernel, other modules' `Contract` | Deptrac (per module) |
| Ui: own Application only, never Domain or Infrastructure | Deptrac (per module) |
| Modules talk to each other only through `Contract` | Deptrac |
| `Contract` classes are `final readonly` | PHPStan custom rule (`tools/PHPStan/ContractRule.php`) |
| _remaining source rules (22 repeated per module)_ | _mapped slice by slice_ |
