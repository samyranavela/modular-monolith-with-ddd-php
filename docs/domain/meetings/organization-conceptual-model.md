# Meetings: conceptual model of organizing meetings

The things of the domain and how they relate, before any technical choice. The rules are described in [organizing meetings](organization.md); the behaviour is in the [event storming](organization-event-storming.md). The group that organizes the meetings is in the [conceptual model of its life](group-life-conceptual-model.md).

```mermaid
classDiagram
    direction LR

    class Meeting {
        title
        description
        start date
        attendee limit
        guest limit
        event fee
        created at
        changed at
        cancelled at
    }
    class Place {
        name
        address
        postal code
        city
    }
    class RsvpTerm {
        opens at
        closes at
    }
    class MeetingHost {
        designated at
        designated by
    }
    class MeetingAttendee {
        guests
        fee owed
        signed up at
        status
    }
    class AttendeeStatus {
        <<enumeration>>
        Awaiting payment
        Attending
        Released
        Gave up the place
        Removed
    }
    class Removal {
        removed at
        reason
    }
    class NotAttendee {
        decided at
        taken back at
    }
    class WaitlistMember {
        signed up at
        status
    }
    class WaitlistStatus {
        <<enumeration>>
        Waiting
        Left
        Taken
    }
    class MemberPayoutAccount {
        validated
    }
    class MeetingGroup
    class Member

    Meeting --> Place : takes place at
    Meeting --> RsvpTerm : is open to sign-ups during
    Meeting "*" --> "1" MeetingGroup : is organized by
    Meeting "1" --> "1..*" MeetingHost : has
    Meeting "1" --> "*" MeetingAttendee : has
    Meeting "1" --> "*" NotAttendee : has
    Meeting "1" --> "*" WaitlistMember : has
    Meeting "*" --> "1" Member : created by
    Meeting "*" --> "0..1" Member : cancelled by
    MeetingHost "*" --> "1" Member : is
    MeetingAttendee --> AttendeeStatus : is
    MeetingAttendee "0..1" --> "1" Removal : may have
    MeetingAttendee "*" --> "1" Member : is
    NotAttendee "*" --> "1" Member : is
    WaitlistMember "*" --> "1" Member : is
    WaitlistMember --> WaitlistStatus : is
    Removal "*" --> "1" Member : by
    MemberPayoutAccount "0..1" --> "1" Member : of
    Meeting ..> MemberPayoutAccount : a fee needs the one of the owner, validated
```

## Reading it

- **Meeting** is the aggregate at the centre. It holds its hosts, its attendees, the members who said they will not come, and its waitlist, so that its rules always hold: the places taken against the limit, the order of the waitlist, at least one host. A meeting is created through its [meeting group](group-life-conceptual-model.md), which checks that it is covered and that the hosts are members. The meeting then reads the group's members and organizers to check who signs up and who may act.
- **Place** and **RSVP Term** are values of the meeting. The RSVP term has an optional opening and an optional closing, and never closes after the start.
- **Meeting Host** links a member to a meeting they are responsible for holding, with the date they were designated and by whom. A host is not an attendee: they take no place and owe no fee. A meeting always has at least one host, and a member is never a host and an attendee of the same meeting.
- **Meeting Attendee** links a member to a meeting, with a number of guests, the fee owed and the date of signing up. Its status says whether the member is **awaiting payment**, **attending** (confirmed, or with nothing to pay), **released** because the payment expired, gave up the place by saying they will not come, or was removed. A meeting without a fee has no awaiting or released status: the member is attending at once. A removed attendee keeps the date, the reason and who removed them, and cannot sign up again. The places taken by a meeting are those of its attending and awaiting members and their guests.
- **Not Attendee** is a member's decision not to come. It is taken back when the member signs up or reverses it.
- **Waitlist Member** is a member waiting for a place, without guests. Only a meeting without a fee has a waitlist. The longest-waiting member is the first to be taken. Leaving the waitlist or being taken ends the wait.
- **Meeting** also remembers when it was changed and by whom, and when and by whom it was cancelled. A cancelled meeting is frozen.
- **Member Payout Account** is what Meetings knows of the payout account of an owner: only whether it is validated, as announced by [Payments](../payments/description.md). A fee can be set on a meeting only when the owner of its group has a validated one. Meetings holds no payment: it keeps the amount owed and the status of the place, and Payments keeps the payment.

## Rules that cross aggregates

- **Two meetings a month on the free plan.** A group on the [free plan](group-life.md#the-free-plan) can have at most two meetings, not cancelled, starting in the same month. The rule involves the group and its meetings, so a domain service checks it when a meeting is created or its start date changes, reading what is stored; the rare race is left to a technical choice.
- **A fee needs a validated payout account.** Setting a fee reads the owner's account as Meetings knows it. A meeting with a fee also has no waitlist, so setting a fee is refused while members wait.

## What is not in the model

- No end date for a meeting.
- No history of a member's changes of decision, or of a host who stepped down, beyond the current state.
- No link between a removed attendee and the waitlist: a removal only frees places.
- No payment, no payment system and no refund: those are Payments'.
- Comments, replies and likes, which are in [commenting](commenting.md).
