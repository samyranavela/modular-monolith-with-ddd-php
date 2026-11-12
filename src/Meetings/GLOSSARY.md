# Meetings

The core context: members propose and join meeting groups, groups organize meetings, members RSVP, wait for a place, comment, and pay event fees (the payment itself belongs to Payments).

## Language

**Member**:
A user who takes part in meeting groups and meetings. Known to this context from the registration of a user.
_Avoid_: User, participant

**Meeting Group Proposal**:
A request by a member to create a meeting group, waiting for an administrator's decision. Once accepted, it leads to the creation of the meeting group.
_Avoid_: Group request, group creation request

**Meeting Group**:
A community of members around a location, hosting meetings. Created from an accepted proposal.
_Avoid_: Club, community

**Meeting Group Member**:
A member who has joined a meeting group, as an Organizer or as a regular member. A member who left is no longer one.

**Organizer**:
The role of a meeting group member who manages the group. The proposer of the group is its first organizer.
_Avoid_: Admin, owner

**Group Payment Date**:
The date until which a meeting group is covered by an organizer's subscription. A meeting can only be organized by a group that is still covered.
_Avoid_: Group expiration, paid group

**Meeting**:
An event organized by a meeting group, with a term, a location, an optional attendee limit, a guest limit and an optional event fee.
_Avoid_: Event

**Meeting Attendee**:
A member who has declared they will attend a meeting, possibly with guests, as Attendee or Host.
_Avoid_: Participant

**Not Attendee**:
A member who has declared they will not attend a meeting. The decision can be reversed.

**Waitlist**:
The ordered queue of members who want to attend a meeting whose attendee limit is reached. The first active member is promoted to attendee when a place becomes free.
_Avoid_: Waiting list

**Host**:
The role of a meeting attendee who may edit the meeting and manage its attendees. A meeting always has at least one host.

**RSVP Term**:
The period during which members can sign up to a meeting.

**Event Fee**:
The amount each attendee owes for a meeting, guests included. Marked as paid once Payments confirms the payment.
_Avoid_: Ticket price

## Commenting

**Comment**:
A text a group member writes on a meeting. It can be edited by its author and removed by its author or an organizer, who may give a reason.

**Reply**:
A comment written in response to another comment.

**Like**:
A group member's appreciation of a comment, at most once per member.

**Meeting Commenting Configuration**:
The switch, created enabled with each meeting, that an organizer uses to allow or forbid comments on that meeting.
