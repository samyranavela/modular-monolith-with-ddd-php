# Meetings

Meetings is the heart of the application: it is where members form communities around a place and meet. Members propose and join **meeting groups**, groups organize **meetings**, and members sign up, wait for a place, comment and owe the fee of a meeting.

Terms are defined in the [Meetings glossary](../../../src/Meetings/GLOSSARY.md).

## Who takes part

- A **member** is the person seen from Meetings. They exist here once their [account](../accounts/description.md) is confirmed with the Member role, and they are the same person as the [user](../accounts/description.md#accounts). An administrator is not a member: they cannot take part in a group or a meeting, but they read everything in Meetings, and they decide on proposals. They change nothing else.
- The **owner** of a meeting group is the member answerable for it and for its subscription. The member who proposed the group is its first owner.
- An **organizer** is a member who looks after a group below its owner, and stands in when the owner is not available.
- A **host** is a member responsible for holding one meeting, who takes no place in it. A host may change it, cancel it and look after its attendees. The owner and the organizers of the group may do the same on every meeting of the group.

## What the context does

- **Creating a meeting group**: a member proposes it, an administrator accepts or rejects the proposal, and an accepted proposal becomes a group. See [creating a meeting group](group-creation.md), its [conceptual model](group-creation-conceptual-model.md) and its [event storming](group-creation-event-storming.md).
- **Living in a meeting group**: members join and leave, a group can be archived and then no longer changes, the owner appoints and demotes organizers and can hand the group over to one of them, and the owner and the organizers edit the group. A group can organize new meetings only while its owner's subscription covers it, which Meetings needs to know from [Payments](../payments/description.md), or while it is the one group of an owner on the free plan. See [the life of a meeting group](group-life.md), its [conceptual model](group-life-conceptual-model.md) and its [event storming](group-life-event-storming.md).
- **Organizing meetings**: the owner and the organizers of a covered group create meetings, with a start date, a venue, an attendee limit, a guest limit and an optional fee. Members sign up during the RSVP term, or say they will not come, and, for a meeting without a fee, a waitlist takes the places that become free. Hosts, organizers and the owner change, cancel and look after a meeting until it starts. See [organizing meetings](organization.md), its [conceptual model](organization-conceptual-model.md) and its [event storming](organization-event-storming.md).
- **Finding and following**: members find groups and view them, list their own groups, find the meetings of a group, view a meeting with its attendees, and list their own meetings. See [the life of a group](group-life.md#reading) and [organizing meetings](organization.md#reading).
- **Commenting**: members of the group comment on a meeting, reply, edit and like, and the owner and the organizers moderate and can switch comments off for a meeting. See [commenting](commenting.md), its [conceptual model](commenting-conceptual-model.md) and its [event storming](commenting-event-storming.md).

The fee of a meeting is owed here, and its payment is made in Payments.

Meetings also has things to report to its members, such as a cancelled meeting or a decision on a proposal. It hands each one to [Notifications](../notifications/description.md), with its kind and its data, as [the catalogue](../notifications/catalogue.md#meetings-groups) lists.

## What Meetings needs from other contexts

This page and the following ones describe the information a context needs from another, and how exact or fresh it must be. They never say how the information is obtained: whether it is requested at the moment it is needed or kept up to date beforehand is a technical choice, made in the implementation.

| Information | From | Why | Exactness and freshness |
| :-- | :-- | :-- | :-- |
| That a user account was confirmed with the Member role | [Accounts](../accounts/registration.md#confirming) | To have a member | Each confirmation gives one member, never lost and never twice. A member must exist without noticeable delay, since they may join a group at once |
| The access of a member's user account: their role, their state (confirmed or locked), and until when their subscription is valid and whether they ever held one | [Accounts](../accounts/description.md#access) | To allow or refuse each action, to know whether a group is covered and whether the member is on the [free plan](group-life.md#the-free-plan), and to refuse a sign-up to a meeting with a fee while the owner of the group is locked | Exact at every action, read at that moment and never kept. The date itself decides coverage, so an expiry needs no delay. A renewal that Accounts does not know yet only shortens the coverage for a while |
| That the payment of a place is paid, or has expired | [Payments](../payments/meeting-fees.md) | To confirm or release the places | Without noticeable delay, and never lost. It may come after the start of the meeting |
| Whether a payout account is validated, and that it is deactivated or reactivated | [Payments](../payments/meeting-fees.md#the-payout-account) | To allow a group payout account to be chosen, and to deactivate and reactivate the ones that name it | Without noticeable delay and never lost, in the order it happened. A payout account that is deactivated in Payments lets nobody pay, even while Meetings does not know it yet, so a late fact never leaves a payment possible |
| That a member's user account becomes locked (a deletion is pending), and that it is deleted | [Accounts](../accounts/account-deletion.md) | To end a hand-over whose candidate is locked, and to take a deleted member out of the groups | Prompt: without noticeable delay, never lost |

## Who may do what

Meetings decides, for each of its actions, **which role may do it**, and whether it changes something. It reads the role and the state of the person from [Accounts](../accounts/description.md#access) at each action. A locked member is refused every action that changes something, and a sign-up to a meeting with a fee is refused while the owner of its group is locked. Being a host, an organizer or the owner is a role in one meeting or one group, which the aggregate checks.
