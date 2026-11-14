# Meetings: event storming of organizing meetings

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](organization-conceptual-model.md) shows the structure; this page shows the behaviour described in [organizing meetings](organization.md). It has four parts: creating and changing a meeting, attending it and the waitlist, looking after it, and the links with the group and with Payments.

## Creating and changing a meeting

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000

    OO[Owner or organizer]:::actor --> C1
    HO[Host, owner or organizer]:::actor --> C2

    subgraph Meetings
        direction LR
        C1[Create a meeting]:::command --> A1((Meeting Group)):::aggregate --> E1[Meeting created]:::event
        C2[Change the meeting]:::command --> A2((Meeting)):::aggregate --> E2[Meeting changed]:::event
    end
```

Blue is a command, pale yellow an aggregate, orange an event, darker yellow someone who asks for a command.

1. **Creating.** The command goes through the meeting group, which refuses it unless the group is covered and the hosts are members of the group. With no host chosen, the creator is the host. The hosts are recorded as the meeting is created.
2. **Changing.** Any of the meeting's attributes can change, as long as the attendee limit does not drop below the places taken. The meeting remembers who changed it and when. A change can free places, which is handled in the next part.

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
        C1[Sign up with guests]:::command --> A1((Meeting)):::aggregate --> E1[Attendee signed up]:::event
        C2[Say I will not come]:::command --> A2((Meeting)):::aggregate --> E2[Member said they will not come]:::event
        A2 --> E3[Attendee gave up their place]:::event
        C3[Take back my decision]:::command --> A3((Meeting)):::aggregate --> E4[Decision taken back]:::event
        C4[Join the waitlist]:::command --> A4((Meeting)):::aggregate --> E5[Member joined the waitlist]:::event
        C5[Leave the waitlist]:::command --> A5((Meeting)):::aggregate --> E6[Member left the waitlist]:::event

        E3 --> P1{{Fill the free places from the waitlist}}:::policy
        X1[Attendee removed]:::event --> P1
        X2[Meeting changed]:::event --> P1
        X3[Host designated, if the member was an attendee]:::event --> P1
        P1 --> C6[Take the next member of the waitlist]:::command --> A6((Meeting)):::aggregate --> E7[Waitlist member became attendee]:::event

        E1 --> P2{{Email the attendee}}:::policy
        E7 --> P2
        E1 --> P3{{Ask Payments to create the attendee's debt}}:::policy
        E7 --> P3
    end

    P2 --> EM[Email service]:::external
    P3 --> PAY[Payments]:::external
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

1. **Signing up.** The meeting refuses unless the RSVP term is open, the member is in the group, is neither a host nor an attendee and was not removed, the guests are within the limit and enough places are left. A member who had said they will not come changes their mind.
2. **Saying they will not come.** The decision is recorded. If the member was an attendee, they also give up their place and their guests'.
3. **Taking back.** The member reverses their decision, and is neither attending nor not attending.
4. **The waitlist.** A member joins it only when the meeting is full, without guests, and leaves it whenever they want.
5. **Filling the free places.** Places become free when an attendee gives up their place, is removed, becomes a host, or the attendee limit is raised. The policy takes the member waiting for the longest, one place each, and repeats while there are places and members waiting. The taken member keeps their sign-up date.
6. **Telling the attendee.** The attendee gets an email when they sign up or are taken from the waitlist.
7. **The fee.** For each of these two events, Payments is asked to create the attendee's debt, if the meeting has a fee. How it does so is specified in the Payments documentation.

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

        E3 --> P1{{Email the removed attendee with the reason}}:::policy
        E4 --> P2{{Email the attendees and the waitlist}}:::policy
    end

    P1 --> EM[Email service]:::external
    P2 --> EM
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

1. **Hosts.** Any member of the group can be designated. A member who was an attendee stops being one, which frees their places for the waitlist, and a member who was waiting leaves the waitlist. Removing the last host is refused: another one is designated first.
2. **Removing an attendee.** The reason is mandatory, the places are freed for the waitlist, and the removed member cannot sign up again.
3. **Cancelling.** The meeting remembers who cancelled and when, and is then frozen. The cancellation is final.
4. **Emails.** The removed attendee is told with the reason, and the attendees and the waitlist are told of a cancellation.

## The group and Payments

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef hotspot fill:#FF6B81,stroke:#c4384f,color:#000

    subgraph Payments
        direction LR
        Y1[Attendee fee paid]:::event
    end

    subgraph Meetings
        direction LR
        G1[Member left the group]:::event --> P1{{Take the member out of the upcoming meetings of the group}}:::policy
        P1 --> C1[Say I will not come, for the member]:::command --> A1((Meeting)):::aggregate --> E1[Member said they will not come]:::event
        P1 --> C2[Leave the waitlist, for the member]:::command --> A2((Meeting)):::aggregate --> E2[Member left the waitlist]:::event
        P1 --> C4[Remove the host, for the member]:::command --> A4((Meeting)):::aggregate --> E4[Host removed]:::event

        Y1 --> P2{{Mark the fee as paid}}:::policy --> C3[Mark the attendee's fee as paid]:::command --> A3((Meeting)):::aggregate --> E3[Attendee fee marked as paid]:::event
    end

    E3 --> H2[What becomes of a fee when the place is lost or the meeting cancelled: to specify with Payments]:::hotspot
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, red a point left open.

1. **Leaving the group.** The group's event is the trigger. On each meeting of the group that has not started, the member is treated as if they said they will not come, or they leave the waitlist, or they stop being a host. The freed places go to the waitlist, as in [attending](#attending-and-the-waitlist).
2. **The last host.** The group refuses the departure of a member who is the last host of a meeting that has not started, so this policy never leaves a meeting without a host. Another host must be designated first.
3. **The fee paid.** Meetings only marks the fee as paid when Payments announces it. It does not change the place.
4. **The other open point.** What Payments does with a fee when its attendee loses their place, or the meeting is cancelled, is specified in the Payments documentation.

## Reading

```mermaid
flowchart LR
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5c,color:#000

    subgraph Meetings
        direction LR
        M1[Meeting created]:::event & M2[Meeting changed]:::event & M3[Meeting cancelled]:::event --> R1(The meetings of a group):::readmodel
        M1 & M2 & M3 & M4[Host designated]:::event & M5[Host removed]:::event & A1[Attendee signed up]:::event & A2[Attendee gave up their place]:::event & A3[Attendee removed]:::event & A4[Waitlist member became attendee]:::event --> R2(A meeting):::readmodel
        A1 & A2 & A3 & A4 & M4 --> R3(The attendees of a meeting):::readmodel
        W1[Member joined the waitlist]:::event & W2[Member left the waitlist]:::event & A4 & M4 --> R4(The waitlist of a meeting):::readmodel
        M1 & M3 & M4 & M5 & A1 & A2 & A3 & A4 & W1 & W2 --> R5(My meetings):::readmodel
    end
```

Orange is an event, green a read model.

1. **The meetings of a group, a meeting.** Both follow the meeting's life: its creation, its changes, its cancellation, its hosts, and the attendees who sign up, give up their place, are removed or are taken from the waitlist. The places left come from the same events.
2. **The attendees, the waitlist.** The attendees follow the same events. The waitlist follows the members who join, leave, are taken, or become hosts.
3. **My meetings.** It gathers, for each member, the meetings where they host, attend or wait.
4. **Who sees what.** The views do not change the meeting, so they raise no event of their own. Who is allowed to see each one is in [organizing meetings](organization.md#reading).

The rest of the events come from [Payments](../payments/description.md) and from [the life of the group](group-life-event-storming.md): Meetings only listens to them.
