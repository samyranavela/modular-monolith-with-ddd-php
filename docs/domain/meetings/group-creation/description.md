# Meetings: creating a meeting group

A meeting group does not appear on its own: a member **proposes** it, an administrator **decides**, and only an accepted proposal becomes a group. This page follows that path, from the proposal to the first owner.

Terms are defined in the [Meetings glossary](../../../../src/Meetings/GLOSSARY.md). The members and administrators come from [Accounts](../../accounts/description.md).

## Proposing

A member proposes a group by giving a **name**, a **city**, a **country** and, optionally, a **description**. The name, the city and the country are required.

This creates a **Meeting Group Proposal**, which is **pending**. The group does not exist yet, and nothing can be organized in it.

Nothing limits the proposals of a member who holds a subscription: they can have several waiting at once, and two proposals can carry the same name. A member on the [free plan](../group-life/description.md#the-free-plan) who already owns a group cannot propose another: they take a subscription or a trial first.

## Deciding

Administrators see **every proposal, whatever its status**, and can filter them by status. They can open one to read it in full.

An administrator decides in one of two ways:

- **Accept** the proposal.
- **Reject** it. A rejection must give a **reason**.

Accepting is refused when the proposer is on the free plan and has become owner of a group since they proposed: the administrator then rejects the proposal, with that reason. The rule is enforced **at the acceptance**, not at the creation of the group, which is a reaction to it. It is absolute: when two proposals of the same free-plan member are accepted at the same moment, only one is accepted and the other is refused.

A proposal is decided **once**. The decision is final: it is neither changed nor undone. The proposal keeps who decided, when, and the reason of a rejection.

## After the decision

- **Accepted**: the meeting group is created from the proposal, with the same name, description and place. The proposer becomes its **owner**, and a member of the group. A [notification](../../notifications/catalogue.md#meetings-groups) tells the proposer that the group exists.
- **Rejected**: a notification tells the proposer that the proposal was rejected, and gives the reason. The proposal stays in their list, with the reason. A member who still wants the group makes a new proposal, which has no link to the rejected one.

## What each role sees

| Who | Can do |
| :-- | :-- |
| A member | Propose a group. List their own proposals, with their status and, for a rejected one, the reason. |
| An administrator | List all the proposals and filter them by status. View a proposal. Accept or reject it. |

An administrator is a user, not a member: they cannot propose a group, and they are not in any group. Meetings decides which role may do each of these actions, and reads the role of the person from [Accounts](../../accounts/description.md#access).
