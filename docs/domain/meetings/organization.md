# Meetings: organizing meetings

A [covered meeting group](group-life.md#covering-the-group) organizes **meetings**. Members sign up, say they will not come, or wait for a place; hosts and organizers look after the meeting until it starts. This page follows a meeting from its creation to its start.

Terms are defined in the [Meetings glossary](../../../src/Meetings/GLOSSARY.md).

## Who does what

| Who | Can do |
| :-- | :-- |
| A member of the group | Sign up, say they will not come, take back that decision, join the waitlist and leave it. |
| A host of the meeting | Change the meeting, remove an attendee, designate and remove hosts, cancel it. A host does not sign up. |
| The owner or an organizer of the group | The same as a host, on every meeting of the group, and create meetings. |

Being a host, an organizer or the owner is a role in one meeting or one group, which the aggregate checks. The permissions behind these actions are defined by Meetings and granted to the Member role by [Accounts](../accounts/description.md#permissions).

A meeting only changes **before it starts**, and only while it is **not cancelled**, and never when its group is [archived](group-life.md#archived-groups): nobody signs up, changes, cancels or designates a host in a meeting of an archived group. Everything below holds under that condition, and the rest of the page does not repeat it. The one exception is the answer of Payments about a place awaiting payment, confirmed or released, which can arrive after the start, see [the fee](#the-fee).

## Hosts

A **host** is a member of the group who is responsible for holding the meeting, typically the person it takes place with. A host is **not an attendee**: they take no place, owe no fee, and do not sign up. Like the staff of a cinema, they are there for the meeting to happen.

A meeting needs **at least one host** to take place. An action that would leave none is refused, **always**: even when two actions happen at the same moment, such as the last two hosts leaving, only one succeeds and the other is refused.

- **Designating.** A host, or the owner or an organizer of the group, designates a member of the group as a host. If the member was an attendee, their attendance ends: their place and their guests' are freed, for the [waitlist](#the-waitlist) when the meeting has one, and a payment still awaited is cancelled. If they were waiting, they leave the waitlist.
- **Removing.** A host steps down, or another host, the owner or an organizer removes them, and they become a plain member again. This is refused for the last host: another one must be designated first.

A member designated or removed by somebody else is notified. Nothing is sent to a host who steps down, or who stops being one by leaving the group.

## Creating a meeting

The owner or an organizer creates a meeting in a group, and only a **covered** group can organize one. A group covered by the [free plan](group-life.md#the-free-plan) can have at most **two meetings a month**, configurable: meetings are counted by the month of their start date, a cancelled one does not count, and creating a third in a month, or moving one into a month that already holds two, is refused. A domain service checks it, reading what is stored; the rule is absolute: when two meetings are created at the same moment, the third of the month is refused.

A meeting has:

- a **title** and a **description**;
- a **start date**, in the future;
- a **venue**: a name, an address, a postal code and a city;
- an optional **attendee limit** and a **guest limit**, see [limits](#limits);
- an **RSVP term**, the period during which members can sign up. Its start and its end are both optional, and sign-ups never stay open past the start of the meeting;
- an optional **meeting fee**, an amount in euros, see [the fee](#the-fee). A fee can be set only when the owner of the group has a **validated payout account**, which Meetings knows from [Payments](../payments/description.md#the-payout-account).

Its [hosts](#hosts) are chosen among the members of the group. If none is chosen, the creator is the host.

### Limits

- The attendee limit, when it is set, is not negative and is strictly greater than the guest limit. With no attendee limit, the meeting never fills up.
- The guest limit is the number of guests one attendee may bring. It is not negative, and 0 means no guests.
- A **place** is taken by an attendee, and by each of their guests. Hosts take none. A place [awaiting payment](#the-fee) is taken. The meeting is **full** when no place is left.

## Changing a meeting

A host, or the owner or an organizer of the group, can change everything the meeting is created with. The meeting remembers who changed it and when.

- The attendee limit cannot go below the places already taken.
- A new guest limit applies to the sign-ups to come, not to the attendees already in.
- A new fee does not change what is already owed, nor what is already awaited. Setting a fee needs the owner's validated payout account, and is refused while members are on the waitlist, since a meeting with a fee has none.
- Raising the attendee limit frees places, which the [waitlist](#the-waitlist) takes when the meeting has one.

## Signing up

A member of the group signs up, with a number of guests. They need:

- the RSVP term to be open;
- not to be a host, nor an attendee already, nor to have been [removed](#removing-an-attendee) from this meeting;
- a number of guests within the guest limit;
- enough places for themselves and their guests;
- no payment already awaited from them for this meeting;
- for a meeting with a fee, the group not to be in a [hand-over](group-life.md#handing-the-group-over): sign-ups to its meetings with a fee are closed from the request until the hand-over ends;
- for a meeting with a fee, the owner of the group not to be [locked](../accounts/account-deletion.md#locked-account) by a pending deletion of their account: sign-ups are closed from the request until it ends.

A member who had said they would not come simply changes their mind.

For a meeting **without a fee**, the member is an attendee at once, and a notification tells them they are in. For a meeting **with a fee**, the places are kept **awaiting payment**, see [the fee](#the-fee).

## Saying you will not come

A member can say they will not come, whether or not they signed up. The decision can be reversed, by signing up or by taking it back, as many times as the member wants before the meeting starts.

An attendee who says they will not come loses their place, and their guests' places, which the waitlist then takes when the meeting has one. If the places were still awaiting payment, the payment is cancelled. A fee already paid is not refunded.

## The waitlist

A meeting **without a fee** has a waitlist. A meeting with a fee has none: a member who finds it full is refused, and the next place that frees up goes to whoever signs up first. A member who wants to come when a meeting without a fee is **full** joins the **waitlist**, without guests. They need the RSVP term to be open, to be a member of the group, to be neither a host, an attendee nor already waiting. They can leave the waitlist whenever they want.

Whenever places become free, because an attendee said they will not come, because an attendee was removed, because an attendee became a host, or because the attendee limit was raised, the waitlist takes them: the member waiting for the longest becomes an attendee, one place each, and so on while there are places left and members waiting. A member taken from the waitlist keeps their sign-up date and is notified that they were taken from the waitlist.

So while someone waits, no place stays free, and nobody can overtake the waitlist. A member who wants guests and finds too few places left is refused, and can still sign up without guests.

## Removing an attendee

A host, or the owner or an organizer of the group, removes an attendee, and must give a **reason**. The attendee loses their place and their guests', which the waitlist takes. They are notified, with the reason. A payment still awaited is cancelled, and a fee already paid is not refunded.

Removal is **final for this meeting**: the member cannot sign up again. They can still join other meetings of the group.

## Cancelling a meeting

A host, or the owner or an organizer of the group, cancels the meeting. The meeting remembers who and when. A cancelled meeting is frozen: nobody can sign up, wait or change anything, and the cancellation is final.

Every attendee, every member on the waitlist and every host but the one who cancels is notified. Every payment still awaited is cancelled. A fee already paid is not refunded: the owner decides, in their own payment system, see [Payments](../payments/meeting-fees.md#refunds).

## When a member leaves the group

A member who is the **last host** of a meeting that has not started cannot [leave the group](group-life.md#leaving): another host must be designated first. The refusal is absolute: when the last two hosts of a meeting leave the group at the same moment, only one succeeds and the other is refused, as if it had come second.

Otherwise, the member is taken out of the meetings of the group that have not started. On each one, an attendee, awaiting payment or not, is treated as if they said they will not come, a member on the waitlist leaves it, and a host stops being one. The places they free go to the waitlist like any other.

## The fee

A meeting **with a fee** takes a place in **two steps**. Each attendee owes **(1 + their guests) times the fee** of the meeting, fixed when they sign up. A host owes nothing.

1. **Awaiting payment.** When the member signs up, Meetings keeps the places for them, **awaiting payment**, and [Payments](../payments/meeting-fees.md#signing-up-to-a-meeting-with-a-fee) needs to know what they owe. Payments creates the payment and asks the payment system to collect it. The places are kept only while the payment waits, 30 minutes by default, and **the place is not guaranteed** until it is paid. A meeting without a fee has no such step.
2. **Confirmed or released.** When the payment is **paid**, the place is **confirmed**. When the payment **expired**, the places are **released**: the member is no longer an attendee, the places can be taken by the next one who signs up, and the member signs up again if they still want to come.

The result of the payment can be known after the start of the meeting, since a payment may still be awaited at that moment. It is then recorded all the same.

Payments needs to know when a place awaiting payment is lost before it is paid, because the attendee said they will not come, was removed, became a host or left the group, and when the meeting is cancelled. A fee already paid never comes back: the application does not refund.

[Payments](../payments/meeting-fees.md#the-payout-account) needs to know whether a meeting with a fee has yet to start in a group a member owns, or in a group whose [hand-over](group-life.md#handing-the-group-over) they have accepted and that is not yet complete, which decides whether they can remove their payout account.

During a [hand-over](group-life.md#handing-the-group-over) of the group, sign-ups to its meetings with a fee are closed, so that no payment is created while the receiver of the fees may change. The rule is absolute: when a sign-up to such a meeting and the hand-over request happen at the same moment, not both succeed. Either the sign-up is taken first and the request is refused because a payment waits, or the request is taken first and the sign-up is refused as closed.

The same holds while the owner of the group is [locked](../accounts/account-deletion.md#locked-account) by a pending deletion of their account: sign-ups to the meetings with a fee are closed until the request ends, and a sign-up and the deletion request at the same moment do not both succeed.

## Notifications

Each of these is a notification handed to [Notifications](../notifications/description.md): the member finds it in their inbox and receives it by email, as [the catalogue](../notifications/catalogue.md#meetings-meetings-and-comments) lists.

| When | Who is told |
| :-- | :-- |
| An attendee signs up to a meeting without a fee, or is taken from its waitlist | The attendee |
| A member signs up to a meeting with a fee | The member, with the places kept and until when they are kept |
| A place awaiting payment is confirmed | The attendee |
| A place awaiting payment is released | The member |
| An attendee is removed | The attendee, with the reason |
| The meeting is cancelled, or its group is archived | Every attendee, every member on the waitlist and every host, except the one who cancels |
| A member is designated host by somebody else, at the creation of the meeting or later | The member, with the place they give up if they were signed up |
| A host is removed by somebody else | The former host |

## Reading

Read-only views, kept up to date by the events of the meetings, let members find a meeting and follow it.

- **The meetings of a group**: its meetings that have not started first, then the past ones, a cancelled meeting being marked as such. Any member sees it, so that they can decide to join the group.
- **A meeting**: its title, description, start, venue, RSVP term, limits, places left, fee and hosts, whether it is cancelled, and for the reader whether they host it, attend it, wait for it, or said they will not come. A member on the waitlist also sees their rank.
- **The attendees of a meeting**: who attends, with their number of guests. The members of the group see it. For a meeting with a fee, the hosts, the owner and the organizers also see whether each place is awaiting payment or confirmed.
- **The waitlist of a meeting**: who waits, in order. The hosts, the owner and the organizers of the group see it.
- **My meetings**: the meetings where the member is a host, an attendee or waiting, with that status, those that have not started first.

An administrator sees all these views, whether or not they belong to the group, but takes no part in a meeting.

The members of the group can then [comment](commenting.md) on the meeting.
