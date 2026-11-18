# Meetings

The core context: members propose and join meeting groups, groups organize meetings, members RSVP, wait for a place, comment, and pay meeting fees (the payment itself belongs to Payments).

## Language

**Member**:
A user who takes part in meeting groups and meetings. Known to this context once their account is confirmed with the Member role.
_Avoid_: User, participant

**Deleted Member**:
How a member whose account was deleted is shown in what they leave behind: their comments, likes, past meetings and decisions. Nothing identifies the person, and the others stay readable.
_Avoid_: Anonymous member, former member (a former member only left a group)

**Meeting Group Proposal**:
A request by a member to create a meeting group, waiting for an administrator's decision. It is pending until decided, then accepted or rejected. Once accepted, it leads to the creation of the meeting group.
_Avoid_: Group request, group creation request

**Proposal Decision**:
The administrator's answer to a meeting group proposal: accept or reject. It is taken once and is final. It keeps who decided and when.
_Avoid_: Verdict, approval

**Rejection Reason**:
The explanation an administrator must give when rejecting a proposal. The proposer is told of it.
_Avoid_: Comment, justification

**Meeting Group**:
A community of members around a place, a city and a country, hosting meetings. Created from an accepted proposal.
_Avoid_: Club, community

**Meeting Group Member**:
A member who has joined a meeting group, as its Owner, as an Organizer or as a regular member. A member who left is no longer one.

**Owner**:
The member answerable for a meeting group and for its subscription. There is exactly one per group, and the proposer is the first. The owner holds every right of an organizer, cannot leave, be demoted or be removed, and appoints and demotes the organizers.
_Avoid_: Creator, admin

**Organizer**:
A member who looks after a meeting group below its owner, and stands in when the owner is not available. There can be any number. An organizer can step down or leave the group, unless they are the last host who counts of a meeting that has not started.
_Avoid_: Admin, co-owner

**Ownership Transfer Request**:
The owner's proposal to hand the group over to one of its organizers, the candidate. It can be made only while no payment of the group's meetings waits, and it closes the sign-ups to the meetings with a fee until it ends. It is pending until the candidate confirms it, which completes it at once: the candidate needs an active subscription and, when a meeting with a fee has yet to start, a validated payout account chosen for the group. Before that, it can be declined by the candidate, cancelled by the owner, or cancelled by itself, with its reason, if the candidate leaves, is demoted or their user account is locked by a pending deletion (a locked member cannot be designated as a candidate). A completed transfer is final, and nobody can cancel a confirmed one.
_Avoid_: Ownership change

**Member Subscription**:
What Meetings needs to know of a member's subscription: until when it is valid, and whether they ever held one.
_Avoid_: Group payment date

**Member Payout Account**:
What Meetings needs to know of a payout account of a member: whether it is validated.
_Avoid_: Bank account, account (alone: it may mean the user account)

**Group Payout Account**:
The payout account that the owner of a group chose to receive the fees of its meetings: one at most for a group, active or deactivated. Only the owner of the group chooses it, which replaces the earlier one, and removes it, which needs that no meeting of the group with a fee has yet to start. It is deactivated when the holder deactivates its payout account in Payments, and reactivated when they reactivate it there, never by the owner of the group. A fee can be set, and a member can sign up to a meeting with a fee, only when it is active. A transfer of the group deletes the former owner's, whose payout account stays in Payments, and puts the candidate's in its place.
_Avoid_: Payout link, assignment

**Covered Group**:
A meeting group whose owner's subscription is still valid, or the one group of an owner on the free plan, who never held a subscription and cannot own another. Only a covered group can organize new meetings, and a group on the free plan has at most two a month; an uncovered group is not closed, and its planned meetings stay.
_Avoid_: Paid group, group expiration

**Archived Group**:
A group whose owner was deleted without handing it over. Its upcoming meetings are cancelled, and it is frozen and read-only: nobody can change it, join it, plan, change or cancel meetings, designate hosts, comment, reply, edit, like, remove a comment or moderate. Its members still read it, with its past, meetings and comments, and can leave it. Its owner stays the owner, shown as a deleted member. Final.
_Avoid_: Closed group, deleted group

**Venue**:
Where a meeting is held: a name, an address, a postal code and a city. Not to be confused with a place.
_Avoid_: Place (for the venue), location

**Place**:
Two things, told apart by what it is the place of. The place of a group is its city and its country. A place in a meeting is a seat, taken by an attendee and by each of their guests: a host takes none, and a place awaiting payment is taken. Neither is the venue of a meeting.
_Avoid_: Location, seat

**Meeting**:
An event organized by a covered meeting group, with a start date, a venue, an RSVP term, an optional attendee limit, a guest limit and an optional meeting fee. It only changes before it starts and while it is not cancelled.
_Avoid_: Event

**Meeting Attendee**:
A member who has declared they will attend a meeting, possibly with guests. An attendee takes a place and owes the meeting fee. For a meeting with a fee, the place is awaiting payment until Meetings knows from Payments that it is paid, then confirmed, or released if the payment expires. An attendee removed from a meeting cannot sign up to it again. A host is never an attendee.
_Avoid_: Participant

**Removal**:
The act of a host, the owner or an organizer taking an attendee out of a meeting, with a mandatory reason. The attendee loses their place and cannot sign up again to that meeting. It keeps who removed them, when and why.
_Avoid_: Ban, kick

**Hand-over**:
The handing of a group to a new owner: see Ownership Transfer Request.

**Guest**:
A person an attendee brings along, who takes a place and adds to the meeting fee, but has no account here.

**Attendee Limit**:
The maximum number of places of a meeting, an attendee and each of their guests taking one. Optional: with none, the meeting never fills up.

**Guest Limit**:
The maximum number of guests one attendee may bring. 0 means no guests.

**Not Attendee**:
A member who has declared they will not attend a meeting. The decision can be reversed.

**Waitlist**:
The ordered queue of members, without guests, who want to attend a meeting without a fee that is full. A meeting with a fee has none. Whenever places become free, the member waiting for the longest becomes an attendee, one place each, while there are places and members waiting.
_Avoid_: Waiting list

**Host**:
A member of the group responsible for holding one meeting, typically the person it takes place with. A host is not an attendee: they take no place, owe no fee and do not sign up. A host may change the meeting, remove attendees, designate and remove hosts and cancel it, as may the owner and the organizers of the group. A meeting needs at least one host to take place, so the last host who counts can neither be removed nor leave the group. A host whose user account is locked or deleted does not count, and a locked member cannot be designated host.

**RSVP Term**:
The period during which members can sign up to a meeting.

**Meeting Fee**:
The amount each attendee owes for a meeting, guests included. The place is confirmed once the payment is paid in Payments. A fee can be set only when the group has an active group payout account whose payout account is validated.
_Avoid_: Event fee, ticket price

## Commenting

**Comment**:
A text a group member writes on a meeting, before or after it, even cancelled. Its author can edit it and remove it; the owner and the organizers can remove it, and must then give a reason. A removed comment stays in the conversation as removed, with no text, and is final.

**Reply**:
A comment written in response to another comment. Replies stay on one level: replying to a reply attaches to the comment it answers.

**Like**:
A group member's appreciation of a comment, once per member, their own comment included. It can be taken back.

**Commenting Switch**:
What allows or forbids comments on one meeting. Created enabled with the meeting; the owner and the organizers turn it off and on. While it is off, nobody writes, replies, edits, likes or unlikes, but comments can still be removed.
_Avoid_: Meeting Commenting Configuration
