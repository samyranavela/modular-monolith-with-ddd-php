# Project board

The work is tracked in the GitHub project **Modular monolith with DDD in PHP / Symfony** (owner `samyranavela`, number 2,
<https://github.com/users/samyranavela/projects/2>). The cards are the repository's issues; `docs/agents/issue-tracker.md`
holds the generic `gh` operations.

## Structure

| Level | Is | Lives in |
| :-- | :-- | :-- |
| Context (Accounts, Meetings, Payments, Notifications) | a bounded context | the `Contexte` field of the project |
| Topic (`accounts / registration`) | a topic folder of `docs/domain/` | a parent issue, the epic |
| Use case (`Confirm the account`) | one command or query, or one business rule of a big one | a sub-issue of the topic |

A ticket that must wait for another is blocked by it with GitHub's native link (`--blocked-by`), never by a sentence in
the body. A ticket is startable when it has no open blocker.

## Columns

`Status`: Backlog, Ready, In progress, In review, Done.

- A new issue lands in Backlog by itself. Backlog to Ready is the human's call.
- The agent moves a card to In progress when it starts the ticket.
- A pull request linked with `Closes #n` moves the card to In review (a project workflow). Closing an issue moves its card to Done; the workflow
  `Pull request merged` is on as well: check its target status the first time a PR merges.
- Set `Contexte` on every new card: the project does not do it.

## Moving a card

The token needs the `project` scope, which only the human can grant: `gh auth refresh -h github.com -s project`, run in
a terminal, with a browser authorization.

```sh
gh project item-list 2 --owner samyranavela --limit 100 --format json   # item ids, status, contexte
gh project field-list 2 --owner samyranavela --format json              # field ids and option ids
gh project view 2 --owner samyranavela --format json --jq .id           # project id
gh project item-edit --id <item> --project-id <project> --field-id <field> --single-select-option-id <option>
```

## A ticket

Title: the use case in the words of the domain, in English. Body, in French as the existing issues: **Ce qu'il faut
construire**, **Critères d'acceptation**, and **Questions ouvertes** when something is not decided. Labels: `needs-triage`
on creation, `needs-info` while a question stays open, removed once it is answered. `ready-for-agent` and
`ready-for-human` are not used: the Ready column says that a ticket may start.

- **Creating a ticket**: a question about how the ticket itself is cut or worded is asked of the human while creating it.
  No default value: what stays undecided goes into **Questions ouvertes**.
- **Starting a ticket**: move the card to In progress, read the ticket and the domain pages it relies on, ask its open
  questions one at a time (the human answers at that moment) and write the answers into the ticket. Then work the
  slice as `AGENTS.md` says: tests first, Domain then Application, a PR with `Closes #n`, and stop.
- Work a ticket with the `tdd` skill. `implement-spec` is not used: it built the technical foundation before the
  business was defined and asked nothing.

## Topic order

Fewest dependencies first (`docs/domain/README.md`, "How the contexts depend on one another"). The use cases of a topic
become sub-issues when the topic starts.

1. accounts / registration
2. accounts / authentication (to document first)
3. accounts / profile-management
4. payments / price-list
5. payments / subscriptions
6. meetings / group-creation
7. meetings / group-life
8. meetings / organization
9. meetings / commenting
10. payments / meeting-fees
11. notifications
12. accounts / account-deletion
