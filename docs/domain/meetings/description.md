# Meetings

Meetings is the heart of the application: it is where members form communities around a place and meet. Members propose and join **meeting groups**, groups organize **meetings**, and members sign up, wait for a place, comment and owe the fee of a meeting.

Terms are defined in the [Meetings glossary](../../../src/Meetings/GLOSSARY.md).

## Who takes part

- A **member** is the person seen from Meetings. They exist here once their [registration](../registrations/description.md) is confirmed with the Member role, and they are the same person as the [user](../useraccess/description.md). An administrator is not a member: they cannot take part in a group or a meeting, but they read everything in Meetings, and they decide on proposals. They change nothing else.
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
| That a registration was confirmed with the Member role | [Registrations](../registrations/description.md) | To have a member | Each confirmation gives one member, never lost and never twice. A member must exist without noticeable delay, since they may join a group at once |
| Until when a member's subscription is valid, and whether they ever held one | [Payments](../payments/subscriptions.md) | To know whether a group is covered, whether a transfer completes, and whether the member is on the [free plan](group-life.md#the-free-plan) | The date itself decides coverage, so an expiry needs no delay. A renewal must count without noticeable delay, since the owner expects to organize at once. Whether a member ever held a subscription must be exact when a group is proposed or accepted |
| That the payment of a place is paid, or has expired | [Payments](../payments/meeting-fees.md) | To confirm or release the places | Without noticeable delay, and never lost. It may come after the start of the meeting |
| Whether an owner's payout account is validated | [Payments](../payments/meeting-fees.md#the-payout-account) | To allow a fee on a meeting | Exact at the moment a fee is set. A validated account cannot be removed while a meeting with a fee has yet to start, in a group its holder owns or has accepted the hand-over of, so it never disappears under a fee |
| That a member's account is locked (a deletion is pending), and that it is deleted | [UserAccess](../useraccess/account-deletion.md) | To refuse a locked member as the candidate of a hand-over, to end a hand-over whose candidate is locked, to close the sign-ups to the meetings with a fee of a group whose owner is locked, and to take a deleted member out of the groups | Exact at a designation of a candidate and at a sign-up to a meeting with a fee. Prompt otherwise: without noticeable delay, never lost |
| Whether a user holds a role that grants a permission | [UserAccess](../useraccess/description.md#permissions) | To allow or refuse each action. A locked member is refused every action that changes something, which UserAccess decides | Exact at every action |

## Permissions

Every action of Meetings is protected by a permission that Meetings defines. [UserAccess](../useraccess/description.md#permissions) holds the roles and which permissions each one grants, and Meetings never names a role.
