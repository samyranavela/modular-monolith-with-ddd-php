# Meetings: event storming of commenting

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](commenting-conceptual-model.md) shows the structure; this page shows the behaviour described in [commenting](commenting.md). It has four parts: the switch, writing and editing, removing, and likes, then the reading.

## The switch

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000

    OO[Owner or organizer]:::actor --> C1
    OO --> C2

    subgraph Meetings
        direction LR
        M1[Meeting created]:::event --> P1{{Create the commenting switch, enabled}}:::policy
        P1 --> C0[Create the switch]:::command --> A0((Commenting Switch)):::aggregate --> E0[Commenting switch created]:::event
        C1[Disable commenting]:::command --> A1((Commenting Switch)):::aggregate --> E1[Commenting disabled]:::event
        C2[Enable commenting]:::command --> A2((Commenting Switch)):::aggregate --> E2[Commenting enabled]:::event
    end
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command.

1. **Creating.** Every meeting gets its switch when it is created, enabled.
2. **Turning it off or on.** Only the owner and the organizers of the group can. Asking for the state the switch is already in is accepted and raises no event.
3. **What it blocks.** The writing, the replying, the editing and the likes in the next parts check the switch. Removing does not.

## Writing, replying and editing

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000

    MB[Member of the group]:::actor --> C1
    MB --> C2
    AU[Author of the comment]:::actor --> C3

    subgraph Meetings
        direction LR
        C1[Write a comment]:::command --> A1((Comment)):::aggregate --> E1[Comment added]:::event
        C2[Reply to a comment]:::command --> A2((Comment)):::aggregate --> E2[Reply added]:::event
        C3[Edit the comment]:::command --> A3((Comment)):::aggregate --> E3[Comment edited]:::event
    end

```

Blue is a command, pale yellow an aggregate, orange an event, darker yellow someone who asks for a command.

1. **Writing.** The comment is refused unless the author is a member of the group, commenting is enabled and the text is not empty and has at most 2000 characters. The meeting's cancellation or end does not matter.
2. **Replying.** The same checks apply, and the comment answered must not be removed. A reply to a reply attaches to the comment it answers, so a reply never has replies of its own.
3. **Editing.** Only the author, with a text that is not empty and has at most 2000 characters, while commenting is enabled and the comment is not removed. The comment remembers when it was edited.
4. **Membership.** The writer is checked against the members of the group at that moment. A member who left cannot write, but what they wrote stays.

## Removing

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    AU[Author of the comment]:::actor --> C1
    OO[Owner or organizer]:::actor --> C2

    subgraph Meetings
        direction LR
        C1[Remove my comment]:::command --> A1((Comment)):::aggregate --> E1[Comment removed by its author]:::event
        C2[Remove a comment with a reason]:::command --> A2((Comment)):::aggregate --> E2[Comment removed by a moderator]:::event
        E2 --> P1{{Notify the author, with the reason}}:::policy
    end

    P1 --> EM[Notifications]:::external
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

1. **By the author.** No reason is asked. The author may be a former member of the group, who then cannot: only the owner and the organizers remove their comment.
2. **By the owner or an organizer.** The reason is mandatory. The comment stays in the conversation as removed, with the person who removed it and the reason.
3. **After the removal.** The comment can no longer be edited, replied to or liked, and its likes are no longer shown. Removal is final, and works whether commenting is enabled or not.
4. **Notification.** Only a removal by somebody else tells the author, with the reason. Nothing is sent when somebody replies or likes.

## Likes

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000

    MB[Member of the group]:::actor --> C1
    MB --> C2

    subgraph Meetings
        direction LR
        C1[Like a comment]:::command --> A1((Comment)):::aggregate --> E1[Comment liked]:::event
        C2[Take back my like]:::command --> A2((Comment)):::aggregate --> E2[Comment unliked]:::event
    end
```

Blue is a command, pale yellow an aggregate, orange an event, darker yellow someone who asks for a command.

1. **Liking.** The member must belong to the group, commenting must be enabled and the comment must not be removed. A member likes a comment once, their own included.
2. **Taking back.** The same conditions apply, and the member must have liked the comment.

## Reading

```mermaid
flowchart LR
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5c,color:#000

    subgraph Meetings
        direction LR
        C1[Comment added]:::event & C2[Reply added]:::event & C3[Comment edited]:::event & C4[Comment removed by its author]:::event & C5[Comment removed by a moderator]:::event & L1[Comment liked]:::event & L2[Comment unliked]:::event --> R1(The comments of a meeting):::readmodel
        L1 & L2 --> R2(The likers of a comment):::readmodel
        S0[Commenting switch created]:::event & S1[Commenting enabled]:::event & S2[Commenting disabled]:::event --> R3(The commenting state of a meeting):::readmodel
    end
```

Orange is an event, green a read model.

1. **The comments of a meeting.** It follows every event of the comments: their writing, their edits, their removal, and the likes that give each comment its count.
2. **The likers of a comment.** It follows the likes and the likes taken back.
3. **The commenting state.** It follows the switch.
4. **Who sees what.** The views do not change anything, so they raise no event of their own. Who is allowed to see each one is in [commenting](commenting.md#reading).
