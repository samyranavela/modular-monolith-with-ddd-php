# Meetings

Meetings is the heart of the application: it is where members form communities around a place and meet. Members propose and join **meeting groups**, groups organize **meetings**, and members sign up, wait for a place, comment and owe the fee of a meeting.

Terms are defined in the [Meetings glossary](../../../src/Meetings/GLOSSARY.md).

## Who takes part

- A **member** is the person seen from Meetings. They exist here once their [registration](../registrations/description.md) is confirmed with the Member role, and they are the same person as the [user](../useraccess/description.md). An administrator is not a member: they only decide on proposals.
- The **owner** of a meeting group is the member answerable for it and for its subscription. The member who proposed the group is its first owner.
- An **organizer** is a member who looks after a group below its owner, and stands in when the owner is not available.
- A **host** is a member who manages one meeting, and may edit it and its attendees.

## What the context does

- **Creating a meeting group**: a member proposes it, an administrator accepts or rejects the proposal, and an accepted proposal becomes a group. See [creating a meeting group](group-creation.md), its [conceptual model](group-creation-conceptual-model.md) and its [event storming](group-creation-event-storming.md).
- **Living in a meeting group**: members join and leave, the owner appoints and demotes organizers and can hand the group over to one of them, and the owner and the organizers edit the group. A group can organize new meetings only while its owner's subscription covers it, which [Payments](../payments/description.md) tells Meetings. See [the life of a meeting group](group-life.md), its [conceptual model](group-life-conceptual-model.md) and its [event storming](group-life-event-storming.md).
- **Organizing meetings**: a group organizes meetings with a term, a place, an attendee limit, a guest limit and an optional fee. Members sign up during the RSVP term, or say they will not come, and a waitlist takes over when the limit is reached.
- **Commenting**: members of the group comment on a meeting, reply and like, and organizers moderate.

The fee of a meeting is owed here, and its payment is made in Payments.

## Permissions

Every action of Meetings is protected by a permission that Meetings defines. [UserAccess](../useraccess/description.md#permissions) holds the roles and which permissions each one grants, and Meetings never names a role.
