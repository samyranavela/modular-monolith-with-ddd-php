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
- **Living in a meeting group**: members join and leave, the owner appoints and demotes organizers and can hand the group over to one of them, and the owner and the organizers edit the group. A group can organize new meetings only while its owner's subscription covers it, which [Payments](../payments/description.md) tells Meetings. See [the life of a meeting group](group-life.md), its [conceptual model](group-life-conceptual-model.md) and its [event storming](group-life-event-storming.md).
- **Organizing meetings**: the owner and the organizers of a covered group create meetings, with a start date, a place, an attendee limit, a guest limit and an optional fee. Members sign up during the RSVP term, or say they will not come, and a waitlist takes the places that become free. Hosts, organizers and the owner change, cancel and look after a meeting until it starts. See [organizing meetings](organization.md), its [conceptual model](organization-conceptual-model.md) and its [event storming](organization-event-storming.md).
- **Finding and following**: members find groups and view them, list their own groups, find the meetings of a group, view a meeting with its attendees, and list their own meetings. See [the life of a group](group-life.md#reading) and [organizing meetings](organization.md#reading).
- **Commenting**: members of the group comment on a meeting, reply and like, and organizers moderate.

The fee of a meeting is owed here, and its payment is made in Payments.

## Permissions

Every action of Meetings is protected by a permission that Meetings defines. [UserAccess](../useraccess/description.md#permissions) holds the roles and which permissions each one grants, and Meetings never names a role.
