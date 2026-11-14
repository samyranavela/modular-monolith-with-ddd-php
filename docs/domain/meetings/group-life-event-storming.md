# Meetings: event storming of the life of a group

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](group-life-conceptual-model.md) shows the structure; this page shows the behaviour described in [the life of a meeting group](group-life.md). It has three parts: the members and their roles, the hand-over of the group, and the coverage by the subscription.

## Members and roles

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000

    MB[Member]:::actor --> C1
    MB --> C2
    OW[Owner]:::actor --> C3
    OW --> C4
    OR[Organizer]:::actor --> C5
    OW --> C6
    OR --> C6

    subgraph Meetings
        direction LR
        C1[Join the group]:::command --> A1((Meeting Group)):::aggregate --> E1[Member joined the group]:::event
        C2[Leave the group]:::command --> A2((Meeting Group)):::aggregate --> E2[Member left the group]:::event
        C3[Appoint an organizer]:::command --> A3((Meeting Group)):::aggregate --> E3[Organizer appointed]:::event
        C4[Demote an organizer]:::command --> A4((Meeting Group)):::aggregate --> E4[Organizer demoted]:::event
        C5[Step down]:::command --> A5((Meeting Group)):::aggregate --> E5[Organizer stepped down]:::event
        C6[Edit the group]:::command --> A6((Meeting Group)):::aggregate --> E6[Group edited]:::event
    end
```

Blue is a command, pale yellow an aggregate, orange an event, darker yellow someone who asks for a command.

1. **Joining.** A member joins, once. Joining again after leaving is a new membership.
2. **Leaving.** A member or an organizer leaves. The command is refused for the owner, who hands the group over first, and for the last host of a meeting that has not started, who has another host designated first. What leaving changes in the meetings is in the [event storming of organizing meetings](organization-event-storming.md#the-group-and-payments).
3. **Appointing.** Only the owner appoints, and only a member of the group who is not an organizer yet.
4. **Demoting and stepping down.** Only the owner demotes an organizer, and an organizer demotes only themselves. The owner can do neither to themselves.
5. **Editing.** The owner or an organizer edits the name, the description or the place.

## Handing the group over

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef hotspot fill:#FF6B81,stroke:#c4384f,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    OW[Owner]:::actor --> C1
    OW --> C4
    CA[Candidate]:::actor --> C2
    CA --> C3

    subgraph Meetings
        direction LR
        C1[Request a transfer to an organizer]:::command --> A1((Transfer Request)):::aggregate --> E1[Transfer requested]:::event
        C2[Accept the transfer]:::command --> A2((Transfer Request)):::aggregate --> E2[Transfer accepted by the candidate]:::event
        C3[Decline the transfer]:::command --> A3((Transfer Request)):::aggregate --> E3[Transfer declined]:::event
        C4[Cancel the transfer]:::command --> A4((Transfer Request)):::aggregate --> E4[Transfer cancelled]:::event

        X1[Member left the group]:::event --> P1{{Cancel the pending transfer if it concerns the candidate}}:::policy
        X2[Organizer demoted]:::event --> P1
        X3[Organizer stepped down]:::event --> P1
        P1 --> C5[Cancel the transfer by itself]:::command --> A5((Transfer Request)):::aggregate --> E5[Transfer cancelled by itself]:::event

        E2 --> P2{{Ask Payments to make sure the candidate has an active subscription}}:::policy

        V1[Subscription validity recorded]:::event --> P3{{Complete the transfer once the candidate is valid}}:::policy
        P3 --> C6[Complete the transfer]:::command --> A6((Meeting Group)):::aggregate --> E6[Group handed over]:::event

        E1 --> P4{{Email the parties concerned}}:::policy
        E2 --> P4
        E3 --> P4
        E4 --> P4
        E5 --> P4
        E6 --> P4
    end

    P2 --> H1[How Payments confirms or takes out the subscription: to specify with Payments]:::hotspot
    H1 --> V1
    P4 --> EM[Email service]:::external
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, red a point left open, pink an external system.

1. **The request.** The owner designates an organizer. One request is pending at most for a group, and it has no deadline.
2. **Accepting.** The candidate's acceptance does not complete the transfer: it asks Payments to make sure the candidate has an active subscription. The request stays pending.
3. **Completing.** The transfer completes only when Meetings records that the candidate's subscription is valid, as announced by Payments. The candidate becomes the owner, the former owner becomes an organizer, and the group follows the new owner's subscription.
4. **Ending.** The candidate can decline, the owner can cancel, and the request is cancelled by itself if the candidate leaves or is demoted. A completed transfer is final.
5. **Emails.** Every event of the request sends an email to each party concerned.
6. **The open point.** How Payments checks, or takes out, the subscription is specified in the Payments documentation. This page only needs its answer: the subscription of the candidate is valid.

## Covering the group

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    subgraph Payments
        direction LR
        Y1[Subscription validity changed]:::event
        Y2[Subscription about to expire]:::event
        Y3[Subscription expired]:::event
    end

    subgraph Meetings
        direction LR
        Y1 --> P1{{Record the validity}}:::policy --> C1[Record the subscription validity]:::command --> A1((Member Subscription)):::aggregate --> E1[Subscription validity recorded]:::event

        Y2 --> P2{{Remind the owner of the groups}}:::policy
        Y3 --> P3{{Tell the owner of the groups they are no longer covered}}:::policy
    end

    P2 --> EM[Email service]:::external
    P3 --> EM
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, pink an external system.

1. **Recording.** When Payments announces a new validity, Meetings records it for the member. This is the date that decides whether a meeting can be organized, and it is what completes a transfer.
2. **Reminding.** When Payments announces that a subscription is about to expire, an email reminds the owner of each group they own.
3. **Expiring.** When it has expired, an email tells the owner that their group is no longer covered. Nothing else changes: the group can no longer organize new meetings, and the meetings already planned stay.

## Reading

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5c,color:#000

    subgraph Meetings
        direction LR
        G1[Group created]:::event & G2[Group edited]:::event & G3[Member joined the group]:::event & G4[Member left the group]:::event --> R1(All the groups):::readmodel
        G1 & G2 & G3 & G4 & G5[Organizer appointed]:::event & G6[Organizer demoted]:::event & G7[Group handed over]:::event --> R2(A group):::readmodel
        G1 & G3 & G4 & G5 & G6 & G7 --> R3(The members of a group):::readmodel
        G1 & G3 & G4 & G5 & G6 & G7 --> R4(My groups):::readmodel

        M1[Meeting created]:::event & M2[Meeting changed]:::event & M3[Meeting cancelled]:::event & M4[Host designated]:::event & M5[Host removed]:::event --> R5(Upcoming meetings a member hosts):::readmodel
        R5 --> C1[Leave the group]:::command
    end
```

Blue is a command, orange an event, green a read model.

1. **All the groups, a group.** Each is kept up to date by the events of the group: its creation, its edits, the members who join and leave, the organizers appointed and demoted, and a hand-over. The number of members comes from the same events.
2. **The members of a group, my groups.** Both come from the same events as the roles: joining, leaving, appointing, demoting and handing over.
3. **The upcoming meetings a member hosts.** It is kept up to date by the events of the meetings: a meeting is created, changed or cancelled, and a host is designated or removed. The command to leave the group reads it, and is refused if the member is the last host of one of these meetings. Two operations can happen together, a host leaving and the other host being removed, so this rule cannot be made absolute by the group alone: how the application deals with that is a technical choice.

The rest of the events come from [Payments](../payments/description.md): Meetings only listens to them.
