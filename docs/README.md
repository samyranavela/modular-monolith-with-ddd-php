# Documentation

| Page | Content |
| :-- | :-- |
| [slices.md](slices.md) | How the port is cut into build slices (S0a, S0b, then per module) and the definition of done |
| [test-traceability.md](test-traceability.md) | Source test → target test, one line per source test |
| [architecture-tests.md](architecture-tests.md) | Architecture rules → Deptrac, PHPStan or reflection test |
| [api-mapping.md](api-mapping.md) | Source REST endpoint → target API Platform route, filled slice by slice |
| `adr/` | Architecture decision records (added as decisions are made) |

A **slice** is a vertical cut (domain → application → API → HTTP test → docs) of one aggregate or group of use cases,
sized for one PR. It is a concept of this PoC, not a domain term.
