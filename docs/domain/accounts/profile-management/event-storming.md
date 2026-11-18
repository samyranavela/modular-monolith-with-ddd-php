# Profile management: event storming

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](conceptual-model.md) shows the structure; this page shows the behaviour. The rules are in [profile management](description.md).

## Changing the email address

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
        C1[Request an email address change]:::command --> P1{{Refuse if the password is wrong, the account is locked or the email address is the current one}}:::policy --> P2{{Whenever the email address is free, create the request}}:::policy --> A1((Account)):::aggregate --> E1[Email address change requested]:::event
        P1 --> P3{{Notify the current email address that a change was requested}}:::policy
        E1 --> P5{{Send the link to the new email address}}:::policy
        E1 --> P6{{Replace the previous request of the account}}:::policy
    end

    subgraph Confirming
        direction LR
        C2[Confirm the email address change]:::command --> P7{{Refuse if expired, used or the email address was taken meanwhile}}:::policy --> A2((Account)):::aggregate --> E4[Email address changed]:::event
        E4 --> P8{{Notify the old email address of the change}}:::policy
    end

    subgraph Deletion[When a deletion is requested]
        direction LR
        E5[Account locked]:::event --> P9{{Drop the email address change request in progress}}:::policy --> A3((Account)):::aggregate --> E6[Email address change request dropped]:::event
    end

    P3 --> EM[Notifications]:::external
    P5 --> EM
    P8 --> EM
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink another context.

1. **Requesting.** The refusals come first. Then the same answer goes back whatever the email address. When it is free, the request is created and the link goes to the new email address; when it is held by an account, confirmed or pending, nothing is created and nothing is sent to the holder. In every case the current email address is told that a change was requested.
2. **One at a time.** A new request replaces the previous one, whose link stops working.
3. **Confirming.** The link works once and before its deadline. The email address is checked again: if another account took it meanwhile, the link is refused as expired or invalid. Then the account takes the new email address, the old one is free, and the old one is told. Sessions go on.
4. **Deleting.** When the user requests the deletion of their account, the account is locked and the request in progress, if any, is dropped.

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
    AN[Anyone with an email address]:::actor --> C2
    PE[Person with the link]:::actor --> C3

    subgraph Changing
        direction LR
        C1[Change the password]:::command --> P1{{Refuse if the current password is wrong or the new one breaks the rules}}:::policy --> U1((Account)):::aggregate --> E1[Password changed]:::event
        E1 --> P2{{End the other sessions}}:::policy
        E1 --> P3{{Notify the current email address}}:::policy
    end

    subgraph Asking[Asking for a reset]
        direction LR
        C2[Ask for a password reset]:::command --> P4{{Whenever a confirmed account holds the email address, create the request}}:::policy --> A1((Account)):::aggregate --> E2[Password reset requested]:::event
        E2 --> P5{{Send the link to the email address}}:::policy
        E2 --> P6{{Replace the previous request of the account}}:::policy
    end

    subgraph Resetting
        direction LR
        C3[Reset the password]:::command --> P8{{Refuse if expired, used or the new password breaks the rules}}:::policy --> U2((Account)):::aggregate --> E4[Password changed]:::event
        E4 --> P9{{End every session of the user}}:::policy
        E4 --> P10{{Notify the email address}}:::policy
    end

    P3 --> EM[Notifications]:::external
    P5 --> EM
    P10 --> EM
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink another context.

1. **Changing.** The current password is checked, and the new one must follow the rules. The other sessions end, the current one goes on, and the current email address is told. A locked account may do it.
2. **Asking.** The answer is the same sentence and takes the same time whatever the email address. For a confirmed account, a request is created, replacing the previous one, and the link goes to the email address; when no account holds it, or only a pending one, nothing happens and nothing is sent.
3. **Resetting.** The link works once and for 1 hour. Following it, the person chooses a password that follows the rules. Every session of the user ends, and the email address is told that the password changed. A locked account and an administrator can reset.
