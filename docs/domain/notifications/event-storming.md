# Notifications: event storming

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](conceptual-model.md) shows the structure; this page shows the behaviour. The rules are in [Notifications](description.md).

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5e,color:#000

    CX[Another context]:::actor
    ME[Member]:::actor

    subgraph Notifying[Notifying a member]
        direction LR
        C1[Notify a member]:::command --> P1{{Refuse if the kind is unknown or its data incomplete}}:::policy --> P2{{Refuse if the member's account is deleted}}:::policy --> N1((Notification)):::aggregate --> E1[Notification created]:::event
        E1 --> P3{{Whenever a notification is created, deliver it through every channel of its kind other than the inbox}}:::policy --> C2[Send the email]:::command --> D1((Delivery)):::aggregate
        D1 --> E2[Email sent]:::event
        D1 --> E3[Email failed]:::event
        E3 --> P4{{Whenever an email failed, try again within the limits}}:::policy --> C2
        RM1(Email email address of the member):::readmodel --> C2
    end

    subgraph Required[Sending a required message]
        direction LR
        C3[Send a required message]:::command --> P5{{Refuse if the kind is unknown or its data incomplete}}:::policy --> R1((Required Message)):::aggregate --> E4[Required message created]:::event
        E4 --> P6{{Whenever a required message is created, send it}}:::policy --> C4[Send the email to the email address]:::command --> R2((Required Message)):::aggregate
        R2 --> E5[Required message sent]:::event
        R2 --> E6[Required message failed]:::event
        E6 --> P7{{Whenever a required message failed, try again within the limits}}:::policy --> C4
    end

    subgraph Reading[Reading the inbox]
        direction LR
        C5[Mark a notification as read]:::command --> N2((Notification)):::aggregate --> E7[Notification read]:::event
        C6[Mark all as read]:::command --> N3((Notification)):::aggregate --> E8[All notifications read]:::event
        V1(My notifications):::readmodel
        V2(My unread count):::readmodel
    end

    subgraph Erasing[When a member's account is deleted]
        direction LR
        E9[Account deleted]:::event --> P8{{Whenever a member's account is deleted, delete their notifications and deliveries}}:::policy --> C7[Delete the inbox of the member]:::command --> N4((Notification)):::aggregate --> E10[Inbox deleted]:::event
    end

    CX --> C1
    CX --> C3
    ME --> C5
    ME --> C6
    E1 --> V1
    E1 --> V2
    E7 --> V1
    E7 --> V2
    E8 --> V1
    E8 --> V2
    E10 --> V1
    E10 --> V2
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, green a read model.

## Reading it

1. **Notifying.** Another context hands Notifications a fact for one member: its kind, its identity and its data. A policy first refuses a kind that is not in the [catalogue](catalogue.md) or data that is incomplete, and refuses a member whose account is deleted. A fact already handed for the same member changes nothing: there is still one notification. A context with several members to notify hands one fact for each.
2. **Delivering.** Once the notification exists, a policy sends it through each channel its kind uses besides the inbox, which today means the email. The email goes to the current email address of the member, which Notifications needs exact at that moment, as Accounts knows it. A failure is repeated within limits left to the implementation, and never removes the notification, which stays in the inbox.
3. **Required messages.** They follow the same path, without a member: the email address is given with the fact, nothing is stored in an inbox, and the only thing that can happen is that the email is sent or fails.
4. **Reading.** The member lists their notifications, most recent first, and counts the unread ones. Marking one as read, or all, does nothing when they are already read. Nobody but the member reads or marks their own.
5. **Erasing.** When a member's account is deleted, their notifications and their deliveries are deleted at once, and a notification arriving after that is refused. The required message that confirms the deletion goes to the email address, not to a member, and is not concerned.
