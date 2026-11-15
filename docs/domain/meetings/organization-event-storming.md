# Meetings: event storming of organizing meetings

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](organization-conceptual-model.md) shows the structure; this page shows the behaviour described in [organizing meetings](organization.md). It has four parts: creating and changing a meeting, attending it and the waitlist, looking after it, and the links with the group and with Payments.

## Creating and changing a meeting

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    OO[Owner or organizer]:::actor --> C1
    HO[Host, owner or organizer]:::actor --> C2

    subgraph Meetings
        direction LR
        C1[Create a meeting]:::command --> P1{{Refuse a third meeting a month on the free plan}}:::policy --> A1((Meeting Group)):::aggregate --> E1[Meeting created]:::event
        E1 --> P3{{Notify the hosts chosen, other than the creator}}:::policy
        C2[Change the meeting]:::command --> P2{{Refuse moving it into a month with two on the free plan}}:::policy --> A2((Meeting)):::aggregate --> E2[Meeting changed]:::event
    end

    P3 --> EM[Notifications]:::external
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

1. **Creating.** The command goes through the meeting group, which refuses it unless the group is covered and not archived and the hosts are members of the group. With no host chosen, the creator is the host. The hosts are recorded as the meeting is created, and those chosen by the creator, other than themselves, are notified. For a group on the [free plan](group-life.md#the-free-plan), a policy also refuses a meeting when two others, not cancelled, already start in the same month.
2. **Changing.** Any of the meeting's attributes can change, as long as the attendee limit does not drop below the places taken. On the free plan, moving the start date into a month that already holds two meetings is refused. The meeting remembers who changed it and when. A change can free places, which is handled in the next part.

## Attending and the waitlist

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    MB[Member]:::actor --> C1
    MB --> C2
    MB --> C3
    MB --> C4
    MB --> C5

    subgraph Meetings
        direction LR
        C1[Sign up with guests]:::command --> G1{{Refuse a meeting with a fee during a hand-over or while the owner is locked}}:::policy --> A1((Meeting)):::aggregate
        A1 --> E1[Attendee signed up, no fee]:::event
        A1 --> E8[Places kept awaiting payment, fee]:::event
        C2[Say I will not come]:::command --> A2((Meeting)):::aggregate --> E2[Member said they will not come]:::event
        A2 --> E3[Attendee gave up their place]:::event
        C3[Take back my decision]:::command --> A3((Meeting)):::aggregate --> E4[Decision taken back]:::event
        C4[Join the waitlist, meeting without a fee]:::command --> A4((Meeting)):::aggregate --> E5[Member joined the waitlist]:::event
        C5[Leave the waitlist]:::command --> A5((Meeting)):::aggregate --> E6[Member left the waitlist]:::event

        E3 --> P1{{Fill the free places from the waitlist}}:::policy
        X1[Attendee removed]:::event --> P1
        X2[Meeting changed]:::event --> P1
        X3[Host designated, if the member was an attendee]:::event --> P1
        P1 --> C6[Take the next member of the waitlist]:::command --> A6((Meeting)):::aggregate --> E7[Waitlist member became attendee]:::event

        E1 --> P2{{Notify the attendee}}:::policy
        E7 --> P2
        E8 --> P2
        E8 --> P3{{Have the payment created, for the amount owed}}:::policy

        E3 --> P6{{Cancel the payment if the place was awaiting payment}}:::policy
        X1 --> P6
        X3 --> P6

        Y1[Meeting fee payment paid]:::event --> P4{{Confirm the place}}:::policy --> C7[Confirm the place]:::command --> A7((Meeting)):::aggregate --> E9[Place confirmed]:::event
        Y2[Meeting fee payment expired]:::event --> P5{{Release the places}}:::policy --> C8[Release the places]:::command --> A8((Meeting)):::aggregate --> E10[Places released]:::event
        E9 --> P2
        E10 --> P2
    end

    P2 --> EM[Notifications]:::external
    P3 --> PAY[Payments]:::external
    P6 --> PAY
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

1. **Signing up.** The meeting refuses unless the RSVP term is open, the member is in the group, is neither a host nor an attendee and was not removed, the guests are within the limit, enough places are left and no payment is awaited from them for this meeting. For a meeting with a fee, a policy also refuses it while the group is in a hand-over, or while its owner is locked by a pending deletion of their account. The rule is absolute: when a sign-up and the hand-over request, or the owner's deletion request, happen at the same moment, not both succeed, see [handing the group over](group-life-event-storming.md#handing-the-group-over). A member who had said they will not come changes their mind.
2. **Without a fee.** The member becomes an attendee at once and is notified.
3. **With a fee.** The places are kept, awaiting payment. A notification tells the member the places and until when they are kept, and the payment is created for the amount owed.
4. **Paid or expired.** When the payment is paid, the place is confirmed and the attendee is told. When it has expired, the places are released and the member is told. A released meeting with a fee has no waitlist to fill, so nothing else follows: the next member to sign up takes the places.
5. **Saying they will not come.** The decision is recorded. If the member was an attendee, awaiting payment or not, they also give up their place and their guests'. A payment still awaited is cancelled.
6. **Taking back.** The member reverses their decision, and is neither attending nor not attending.
7. **The waitlist.** Only a meeting without a fee has one. A member joins it only when the meeting is full, without guests, and leaves it whenever they want.
8. **Filling the free places.** Places become free when an attendee gives up their place, is removed, becomes a host, or the attendee limit is raised. For a meeting with a waitlist, the policy takes the member waiting for the longest, one place each, and repeats while there are places and members waiting. The taken member keeps their sign-up date.
9. **Losing the place before paying.** When an attendee gives up the place, is removed or becomes a host while the payment is awaited, the payment is cancelled.

## Looking after the meeting

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    HO[Host, owner or organizer]:::actor --> C1
    HO --> C2
    HO --> C3
    HO --> C4

    subgraph Meetings
        direction LR
        C1[Designate a host]:::command --> A1((Meeting)):::aggregate --> E1[Host designated]:::event
        A1 --> E5[Attendee gave up their place, if the member was an attendee]:::event
        C2[Remove a host]:::command --> A2((Meeting)):::aggregate --> E2[Host removed]:::event
        C3[Remove an attendee with a reason]:::command --> A3((Meeting)):::aggregate --> E3[Attendee removed]:::event
        C4[Cancel the meeting]:::command --> A4((Meeting)):::aggregate --> E4[Meeting cancelled]:::event

        E3 --> P1{{Notify the removed attendee with the reason}}:::policy
        E4 --> P2{{Notify the attendees, the waitlist and the other hosts}}:::policy
        E1 --> P5{{Notify the host designated by somebody else}}:::policy
        E2 --> P6{{Notify the host removed by somebody else}}:::policy
        E4 --> P3{{Cancel the payments still awaited}}:::policy
        E3 --> P4{{Cancel the payment if the place was awaiting payment}}:::policy
    end

    P1 --> EM[Notifications]:::external
    P2 --> EM
    P5 --> EM
    P6 --> EM
    P3 --> PAY[Payments]:::external
    P4 --> PAY
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

1. **Hosts.** Any member of the group can be designated. A member who was an attendee stops being one, which frees their places for the waitlist, and a member who was waiting leaves the waitlist. Removing the last host is refused: another one is designated first.
2. **Removing an attendee.** The reason is mandatory, the places are freed for the waitlist, and the removed member cannot sign up again.
3. **Cancelling.** The meeting remembers who cancelled and when, and is then frozen. The cancellation is final. Every payment of the meeting that still waits is cancelled. A fee already paid is not refunded.
4. **Notifications.** The removed attendee is told with the reason. The attendees, the waitlist and the hosts, except the one who cancels, are told of a cancellation. A member designated host, or a host removed, by somebody else is told; a host who steps down is not.

## The group and Payments

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    subgraph Payments
        direction LR
        Y1[Payout account validated]:::event
        Y2[Payout account removed]:::event
    end

    subgraph Meetings
        direction LR
        G1[Member left the group]:::event --> P1{{Take the member out of the upcoming meetings of the group}}:::policy
        P1 --> C1[Say I will not come, for the member]:::command --> A1((Meeting)):::aggregate --> E1[Member said they will not come]:::event
        P1 --> C2[Leave the waitlist, for the member]:::command --> A2((Meeting)):::aggregate --> E2[Member left the waitlist]:::event
        P1 --> C4[Remove the host, for the member]:::command --> A4((Meeting)):::aggregate --> E4[Host removed]:::event
        A1 --> E5[Attendee gave up their place, if the member was an attendee]:::event
        E5 --> P2{{Cancel the payment if the place was awaiting payment}}:::policy

        K1(Whether the owner's payout account is validated):::readmodel
    end

    Y1 -.->|needed| K1
    Y2 -.->|needed| K1
    P2 --> Payments
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, green the information Meetings needs. A dotted arrow means that Meetings needs the fact.

1. **Leaving the group.** The group's event is the trigger. On each meeting of the group that has not started, the member is treated as if they said they will not come, or they leave the waitlist, or they stop being a host. A member who was an attendee gives up their place, as in any other case. The freed places go to the waitlist of a meeting that has one, as in [attending](#attending-and-the-waitlist), and a payment still awaited is cancelled.
2. **The last host.** The group refuses the departure of a member who is the last host of a meeting that has not started, so this policy never leaves a meeting without a host. Another host must be designated first. The refusal is absolute: when the last two hosts leave at the same moment, only one succeeds.
3. **The payout account.** Meetings needs to know whether the owner's payout account is validated, and a fee can be set on a meeting only when it is. The paid and expired payments are handled in [attending](#attending-and-the-waitlist).

## Reading

One diagram for each view, with the events that keep it up to date.

**The meetings of a group**

```mermaid
flowchart LR
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5c,color:#000

    subgraph Meetings
        direction LR
        M1[Meeting created]:::event & M2[Meeting changed]:::event & M3[Meeting cancelled]:::event --> R1(The meetings of a group):::readmodel
    end
```

Orange is an event, green a read model.

**A meeting**

```mermaid
flowchart LR
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5c,color:#000

    subgraph Meetings
        direction LR
        M1[Meeting created]:::event & M2[Meeting changed]:::event & M3[Meeting cancelled]:::event & M4[Host designated]:::event & M5[Host removed]:::event --> R2(A meeting):::readmodel
        A1[Attendee signed up]:::event & A5[Places kept awaiting payment]:::event & A6[Place confirmed]:::event & A7[Places released]:::event --> R2
        A2[Attendee gave up their place]:::event & A3[Attendee removed]:::event & A4[Waitlist member became attendee]:::event --> R2
        N1[Member said they will not come]:::event & N2[Decision taken back]:::event --> R2
    end
```

Orange is an event, green a read model.

**The attendees of a meeting**

```mermaid
flowchart LR
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5c,color:#000

    subgraph Meetings
        direction LR
        A1[Attendee signed up]:::event & A5[Places kept awaiting payment]:::event & A6[Place confirmed]:::event & A7[Places released]:::event --> R3(The attendees of a meeting):::readmodel
        A2[Attendee gave up their place]:::event & A3[Attendee removed]:::event & A4[Waitlist member became attendee]:::event & M4[Host designated]:::event --> R3
    end
```

Orange is an event, green a read model.

**The waitlist of a meeting**

```mermaid
flowchart LR
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5c,color:#000

    subgraph Meetings
        direction LR
        W1[Member joined the waitlist]:::event & W2[Member left the waitlist]:::event & A4[Waitlist member became attendee]:::event & M4[Host designated]:::event --> R4(The waitlist of a meeting):::readmodel
    end
```

Orange is an event, green a read model.

**My meetings**

```mermaid
flowchart LR
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5c,color:#000

    subgraph Meetings
        direction LR
        M1[Meeting created]:::event & M2[Meeting changed]:::event & M3[Meeting cancelled]:::event & M4[Host designated]:::event & M5[Host removed]:::event --> R5(My meetings):::readmodel
        A1[Attendee signed up]:::event & A5[Places kept awaiting payment]:::event & A6[Place confirmed]:::event & A7[Places released]:::event --> R5
        A2[Attendee gave up their place]:::event & A3[Attendee removed]:::event & A4[Waitlist member became attendee]:::event --> R5
        W1[Member joined the waitlist]:::event & W2[Member left the waitlist]:::event & N1[Member said they will not come]:::event & N2[Decision taken back]:::event --> R5
    end
```

Orange is an event, green a read model.

1. **The meetings of a group, a meeting.** Both follow the meeting's life: its creation, its changes, its cancellation, its hosts, and the attendees who sign up, wait for payment, are confirmed or released, give up their place, are removed or are taken from the waitlist. The places left come from the same events.
2. **The attendees, the waitlist.** The attendees follow the same events, and show for a meeting with a fee whether each place is awaiting payment or confirmed. The waitlist follows the members who join, leave, are taken, or become hosts.
3. **My meetings.** It gathers, for each member, the meetings where they host, attend or wait, and follows the changes of a meeting, its start date included. The decisions to not come, and their reversal, feed **a meeting** too, so that the reader sees that they said they will not come.
4. **Who sees what.** The views do not change the meeting, so they raise no event of their own. Who is allowed to see each one is in [organizing meetings](organization.md#reading).

The facts that come from [Payments](../payments/description.md) are the information Meetings needs, listed in [its description](description.md#what-meetings-needs-from-other-contexts); the others come from [the life of the group](group-life-event-storming.md).
