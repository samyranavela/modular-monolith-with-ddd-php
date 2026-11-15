# Meetings

The core context: members propose and join meeting groups, groups organize meetings, members RSVP, wait for a place, comment, and pay event fees (the payment itself belongs to Payments).

## Language

**Member**:
A user who takes part in meeting groups and meetings. Known to this context from the registration of a user.
_Avoid_: User, participant

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
A community of members around a location, hosting meetings. Created from an accepted proposal.
_Avoid_: Club, community

**Meeting Group Member**:
A member who has joined a meeting group, as its Owner, as an Organizer or as a regular member. A member who left is no longer one.

**Owner**:
The member answerable for a meeting group and for its subscription. There is exactly one per group, and the proposer is the first. The owner holds every right of an organizer, cannot leave, be demoted or be removed, and appoints and demotes the organizers.
_Avoid_: Creator, admin

**Organizer**:
A member who looks after a meeting group below its owner, and stands in when the owner is not available. There can be any number. An organizer can step down or leave at any time.
_Avoid_: Admin, co-owner

**Ownership Transfer Request**:
The owner's proposal to hand the group over to one of its organizers, the candidate. It can be made only while no payment of the group's meetings waits, and it closes the sign-ups to the meetings with a fee until it ends. It is pending until the candidate accepts, with a validated payout account when a meeting with a fee has yet to start, and Meetings knows from Payments that their subscription is valid; it can be declined by the candidate, cancelled by the owner, or cancelled by itself if the candidate leaves or is demoted. A completed transfer is final.
_Avoid_: Ownership change

**Member Subscription**:
What Meetings knows of a member's subscription: until when it is valid, as announced by Payments.
_Avoid_: Group payment date

**Member Payout Account**:
What Meetings knows of the payout account of a member: whether it is validated, as announced by Payments. A fee can be set on a meeting only when the owner of its group has a validated one.
_Avoid_: Bank account

**Covered Group**:
A meeting group whose owner's subscription is still valid, or the one group of an owner on the free plan, who never held a subscription and cannot own another. Only a covered group can organize new meetings, and a group on the free plan has at most two a month; an uncovered group is not closed, and its planned meetings stay.
_Avoid_: Paid group, group expiration

**Meeting**:
An event organized by a covered meeting group, with a start date, a place, an RSVP term, an optional attendee limit, a guest limit and an optional event fee. It only changes before it starts and while it is not cancelled.
_Avoid_: Event

**Meeting Attendee**:
A member who has declared they will attend a meeting, possibly with guests. An attendee takes a place and owes the event fee. For a meeting with a fee, the place is awaiting payment until Payments says it is paid, then confirmed, or released if the payment expires. An attendee removed from a meeting cannot sign up to it again. A host is never an attendee.
_Avoid_: Participant

**Guest**:
A person an attendee brings along, who takes a place and adds to the event fee, but has no account here.

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
A member of the group responsible for holding one meeting, typically the person it takes place with. A host is not an attendee: they take no place, owe no fee and do not sign up. A host may change the meeting, remove attendees, designate and remove hosts and cancel it, as may the owner and the organizers of the group. A meeting needs at least one host to take place, so the last host can neither be removed nor leave the group.

**RSVP Term**:
The period during which members can sign up to a meeting.

**Event Fee**:
The amount each attendee owes for a meeting, guests included. The place is confirmed once Payments confirms the payment. A fee can be set only when the owner of the group has a validated payout account.
_Avoid_: Ticket price

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
