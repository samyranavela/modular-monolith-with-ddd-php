# UserAccess: event storming of profile management

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](profile-management-conceptual-model.md) shows the structure; this page shows the behaviour. The rules are in [profile management](profile-management.md).

## Changing the address

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    US[User]:::actor --> C1
    PE[Person with the link]:::actor --> C2

    subgraph Requesting
        direction LR
        C1[Request an address change]:::command --> P1{{Refuse if the password is wrong, the account is locked or the address is the current one}}:::policy --> P2{{Whenever the address is free of users and waiting registrations, create the request}}:::policy --> A1((Address Change Request)):::aggregate --> E1[Address change requested]:::event
        P1 --> P3{{Notify the current address that a change was requested}}:::policy
        P2 -->|address of a user| P4{{Notify the owner of the address that an account exists}}:::policy
        E1 --> P5{{Send the link to the new address}}:::policy
        E1 --> P6{{Replace the pending request of the user}}:::policy
    end

    subgraph Confirming
        direction LR
        C2[Confirm the address change]:::command --> P7{{Refuse if expired, used or the address was taken meanwhile}}:::policy --> A2((Address Change Request)):::aggregate --> E2[Address change confirmed]:::event
        E2 --> C3[Give the new address to the user]:::command --> U1((User)):::aggregate --> E3[User address changed]:::event
        E2 --> P8{{Notify the old address of the change}}:::policy
    end

    subgraph Deletion[When a deletion is requested]
        direction LR
        E4[Deletion requested]:::event --> P9{{Cancel the pending address change}}:::policy --> A3((Address Change Request)):::aggregate --> E5[Address change cancelled]:::event
    end

    P3 --> EM[Notifications]:::external
    P4 --> EM
    P5 --> EM
    P8 --> EM
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink another context.

1. **Requesting.** The refusals come first. Then the same answer goes back whatever the address. When it is free of users and waiting registrations, the request is created and the link goes to the new address; when it belongs to a user, nothing is created and its owner is told that an account exists; when it belongs to a waiting registration, nothing more happens. In every case the current address is told that a change was requested.
2. **One at a time.** A new request replaces the pending one, whose link stops working.
3. **Confirming.** The link works once and before its deadline. The address is checked again: if it was taken meanwhile, the link is refused as expired or invalid. Then the user takes the new address, the old one is free, and the old address is told. Sessions go on.
4. **Deleting.** When the user requests the deletion of their account, a pending request is cancelled.

## Changing and resetting the password

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    US[User]:::actor --> C1
    AN[Anyone with an address]:::actor --> C2
    PE[Person with the link]:::actor --> C3

    subgraph Changing
        direction LR
        C1[Change the password]:::command --> P1{{Refuse if the current password is wrong or the new one breaks the rules}}:::policy --> U1((User)):::aggregate --> E1[Password changed]:::event
        E1 --> P2{{End the other sessions}}:::policy
        E1 --> P3{{Notify the current address}}:::policy
    end

    subgraph Asking[Asking for a reset]
        direction LR
        C2[Ask for a password reset]:::command --> P4{{Whenever the address belongs to a user, create the request}}:::policy --> A1((Password Reset Request)):::aggregate --> E2[Password reset requested]:::event
        E2 --> P5{{Send the link to the address}}:::policy
        E2 --> P6{{Replace the pending request of the user}}:::policy
        C2 --> P7{{Whenever there is no user and no waiting registration, notify that no account exists}}:::policy
    end

    subgraph Resetting
        direction LR
        C3[Reset the password]:::command --> P8{{Refuse if expired, used or the new password breaks the rules}}:::policy --> A2((Password Reset Request)):::aggregate --> E3[Password reset]:::event
        E3 --> C4[Set the new password]:::command --> U2((User)):::aggregate --> E4[Password changed]:::event
        E4 --> P9{{End every session of the user}}:::policy
        E4 --> P10{{Notify the address}}:::policy
    end

    P3 --> EM[Notifications]:::external
    P5 --> EM
    P7 --> EM
    P10 --> EM
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink another context.

1. **Changing.** The current password is checked, and the new one must follow the rules. The other sessions end, the current one goes on, and the current address is told. A locked account may do it.
2. **Asking.** The answer is the same sentence and takes the same time whatever the address. For a user, a request is created, replacing the pending one, and the link goes to the address; with no user and no waiting registration, a message tells the address that no account exists; for a waiting registration, nothing more happens.
3. **Resetting.** The link works once and for 1 hour. Following it, the person chooses a password that follows the rules. Every session of the user ends, and the address is told that the password changed. A locked account and an administrator can reset.
