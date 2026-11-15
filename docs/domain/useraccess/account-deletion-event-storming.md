# Deleting an account: event storming

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](account-deletion-conceptual-model.md) shows the structure; this page shows the behaviour described in [deleting an account](account-deletion.md). It has five parts: requesting, cancelling, carrying out, exporting, and deleting an administrator, then the reading.

## Requesting

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    MB[Member]:::actor --> C1

    subgraph UserAccess
        direction LR
        C1[Request the deletion of my account]:::command --> A1((Account Deletion Request)):::aggregate --> E1[Deletion requested]:::event
        E1 --> P1{{Lock the account}}:::policy --> A2((User)):::aggregate --> E2[Account locked]:::event
        E1 --> P2{{Tell the member}}:::policy
    end

    subgraph Meetings
        direction LR
        E2 --> P3{{Decline the hand-overs proposed to the member}}:::policy
    end

    P2 --> EM[Email service]:::external
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

1. **Refusals.** The request is refused when the user is an administrator, when a request of theirs is already pending, or when an [open item](account-deletion.md#what-stands-in-the-way) stands in the way: a group they own that is under hand-over, a last host of an upcoming meeting, a payment that waits, a proposal that is pending. The check and the request are one decision: if something stands in the way at the same moment, only one of the two succeeds.
2. **The request** remembers when it was made and the day it will be carried out, the end of the grace period.
3. **Locking.** The account is locked as soon as the request exists. Meetings needs to know it, to decline a hand-over that was proposed to the member, which ends it as for a candidate that left.
4. **Email.** The member is told the date and what will happen.

## Cancelling

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    MB[Locked member]:::actor --> C1

    subgraph UserAccess
        direction LR
        C1[Cancel my deletion request]:::command --> A1((Account Deletion Request)):::aggregate --> E1[Deletion cancelled by the member]:::event
        E1 --> P1{{Unlock the account}}:::policy --> A2((User)):::aggregate --> E2[Account unlocked]:::event
        E1 --> P2{{Tell the member}}:::policy
    end

    P2 --> EM[Email service]:::external
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

1. **Only while pending.** Nothing can be cancelled after the grace period is over and the deletion is carried out.
2. **Unlocking.** The account is as it was, except for the hand-overs that were declined: they are not restored, the owner proposes again.

## Carrying out

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    subgraph UserAccess
        direction LR
        T1[Grace period over]:::event --> P1{{Check the open items again}}:::policy
        P1 -->|something stands in the way| C1[Cancel the request, with the reason]:::command --> A1((Account Deletion Request)):::aggregate --> E1[Deletion cancelled by the application]:::event
        E1 --> P2{{Unlock the account, tell the member}}:::policy
        P1 -->|nothing| C2[Carry out the deletion]:::command --> A2((Account Deletion Request)):::aggregate --> E2[Deletion carried out]:::event
        E2 --> P3{{Tell the member, before the erasure}}:::policy
        E2 --> C3[Erase the user]:::command --> A3((User)):::aggregate --> E3[User erased]:::event
    end

    subgraph Registrations
        direction LR
        E3 --> P4{{Delete the confirmed registration}}:::policy
    end

    subgraph Meetings
        direction LR
        E3 --> P5{{Take the member out of every group}}:::policy
        E3 --> P6{{Archive the groups still owned}}:::policy
        P6 --> E4[Group archived]:::event
        E4 --> P7{{Cancel the upcoming meetings, tell the members}}:::policy
    end

    subgraph Payments
        direction LR
        E3 --> P8{{End the subscription, stop the renewal, remove the payout account}}:::policy
        E3 --> P9{{Anonymise the payer}}:::policy
    end

    P3 --> EM[Email service]:::external
    P2 --> EM
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, pink an external system.

1. **The check, again.** The same rules as at the request, on the open items at that moment. If they do not hold, nothing is erased, the request is cancelled with the reason, and the account is unlocked.
2. **Erasing.** The login, the email address, the names and the password are erased, and the user is deleted. The member is told first, at the address that the erasure then takes away.
3. **Registrations.** The confirmed registration of the user is deleted, which frees the login and the email address.
4. **Meetings.** The member leaves every group as if they left it themselves. A group they still own is archived, with its upcoming meetings cancelled. The comments, likes and decisions of the member stay, with the deleted member in place of the person.
5. **Payments.** The subscription ends with no refund and no further renewal, the payout account is removed, and the payer is anonymised. The payments are kept without an identity.
6. **Independent parts.** The three contexts act on their own, each when it knows. None waits for the others, and none can undo what it did.

## Exporting

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    US[User]:::actor --> C1

    subgraph UserAccess
        direction LR
        C1[Export my data]:::command --> A1((Data Export)):::aggregate --> E1[Export requested]:::event
        E1 --> P1{{Gather what each context holds}}:::policy
        P1 --> E2[Export ready]:::event
        E2 --> P2{{Tell the user}}:::policy
        T1[Time passed]:::event --> P3{{Expire the export}}:::policy --> E3[Export expired]:::event
    end

    P2 --> EM[Email service]:::external
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

1. **Who.** Any user, locked or not, an administrator included: their export holds what UserAccess knows of them.
2. **Gathering.** Each context gives what it holds about the person. The export is ready when all have.
3. **Expiring.** After a limited time the export is no longer available, and the user asks again if they need it.

## Deleting an administrator

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    AD[Administrator]:::actor --> C1

    subgraph UserAccess
        direction LR
        C1[Delete another administrator]:::command --> A1((User)):::aggregate --> E1[Administrator deleted]:::event
        E1 --> P1{{Tell the administrator, before the erasure}}:::policy
        E1 --> P2{{Erase the user}}:::policy
    end

    subgraph Meetings
        direction LR
        E1 --> P3{{Show the deleted administrator on past decisions}}:::policy
    end

    P1 --> EM[Email service]:::external
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

1. **Refusals.** Nobody deletes themselves, and the target must be an administrator. The command is refused when it would leave no administrator, whichever way two deletions overlap.
2. **At once.** There is no request, no grace period and no lock: the administrator is neither a member nor a payer.
3. **Past decisions.** The proposals the administrator accepted or rejected keep who, when and why, with the deleted administrator in place of the person.

## Reading

```mermaid
flowchart LR
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5c,color:#000

    subgraph UserAccess
        direction LR
        E1[Deletion requested]:::event & E2[Deletion cancelled by the member]:::event & E3[Deletion cancelled by the application]:::event & E4[Deletion carried out]:::event --> R1(My deletion request):::readmodel
        X1[Export requested]:::event & X2[Export ready]:::event & X3[Export expired]:::event --> R2(My exports):::readmodel
    end

    subgraph Meetings
        direction LR
        G1[Member joined, left or was designated]:::event & G2[Group archived]:::event --> R3(What deleting my account would change):::readmodel
    end

    subgraph Payments
        direction LR
        Y1[Payment created, paid or expired]:::event --> R3
    end
```

Orange is an event, green a read model.

1. **My deletion request.** It follows the request until it ends.
2. **My exports.** It follows the exports of the user.
3. **What deleting my account would change.** It brings together what Meetings and Payments hold about the member: the groups they own, the meetings where they are the last host, the proposals pending and the payments that wait. It is read on demand, and checked again by the commands, never trusted from the view alone.
4. **Who sees what.** Only the member reads these views, as listed in [deleting an account](account-deletion.md#reading).
