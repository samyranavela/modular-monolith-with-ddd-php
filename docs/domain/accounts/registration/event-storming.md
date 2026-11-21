# Registration: event storming

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](conceptual-model.md) shows the structure; this page shows the behaviour.

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c36bb8,color:#000

    subgraph Accounts
        direction LR
        R1[Register]:::command --> P5{{Whenever the email address is free, create the pending account}}:::policy --> RA1((Account)):::aggregate --> E1[Account registered]:::event
        I1[Invite an administrator]:::command --> RA4((Account)):::aggregate --> E7[Administrator invited]:::event
        R1 --> P6{{Whenever a pending account holds the email address, send a new link}}:::policy --> RA2((Account)):::aggregate --> E2[Confirmation link sent again]:::event

        E1 --> P1{{Send the confirmation link}}:::policy
        E7 --> P1
        E2 --> P1

        C1[Confirm the account]:::command --> P7{{Refuse if expired, used, the login is taken or the password breaks the rules}}:::policy --> RA3((Account)):::aggregate --> E3[Account confirmed]:::event

        X1[48 hours passed]:::event --> P2{{Remove the pending account}}:::policy
    end

    OP[Operator or administrator]:::actor --> I1
    PE[Person with the link]:::actor --> C1

    subgraph Meetings
        direction LR
        M1[Create member]:::command --> MA((Member)):::aggregate --> E5[Member created]:::event
    end

    subgraph Payments
        direction LR
        Y1[Create payer]:::command --> YA((Payer)):::aggregate --> E6[Payer created]:::event
    end

    P1 --> EM[Notifications]:::external

    E3 -->|role Member| M1
    E3 -->|role Member| Y1
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

## Reading it

1. **Registering.** The person gives an email address. When it is free, a pending account is created for the Member role, and the confirmation link goes out as a reaction to the event, not as part of the command. When a confirmed account holds it, nothing happens. The answer is the same in every case.
2. **Inviting.** The operator or an administrator invites someone. A pending account is created for the Administrator role, with only the email address, and the same policy sends the link.
3. **Registering again.** When a pending account holds the email address, no account is created: a new link is sent and the deadline starts again, even past the previous deadline, as long as the account was not removed.
4. **Confirming.** The person follows the link, fills in the form and validates it. The account goes from pending to confirmed, if it is still pending and before its deadline, and if the login is free. This is the only fact of the registration that other contexts need.
5. **After the confirmation.** Meetings and Payments create a member and a payer, only for the Member role, in reaction to the same event, and they do not wait for each other. Accounts creates nothing: the account is the same one, now confirmed.
6. **Expiring.** When 48 hours pass without confirmation, the pending account is removed. Nobody asked for it: it is time that triggers it.
