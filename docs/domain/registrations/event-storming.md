# Registrations: event storming

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](conceptual-model.md) shows the structure; this page shows the behaviour.

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000

    subgraph Registrations
        direction LR
        R1[Register new user]:::command --> RA1((User Registration)):::aggregate --> E1[New user registered]:::event
        I1[Invite an administrator]:::command --> RA4((User Registration)):::aggregate --> E7[Administrator invited]:::event
        R2[Register again, same email]:::command --> RA2((User Registration)):::aggregate --> E2[Confirmation link sent again]:::event

        E1 --> P1{{Send the confirmation link}}:::policy
        E7 --> P1
        E2 --> P1

        C1[Confirm registration]:::command --> RA3((User Registration)):::aggregate --> E3[Registration confirmed]:::event

        X1[48 hours passed]:::event --> P2{{Delete the registration}}:::policy
    end

    OP[Operator or administrator]:::actor --> I1

    subgraph UserAccess
        direction LR
        U1[Create user]:::command --> UA((User)):::aggregate --> E4[User created]:::event
    end

    subgraph Meetings
        M1[Create member]:::command --> MA((Member)):::aggregate --> E5[Member created]:::event
    end

    subgraph Payments
        Y1[Create payer]:::command --> YA((Payer)):::aggregate --> E6[Payer created]:::event
    end

    E3 --> U1
    E3 -->|role Member| M1
    E3 -->|role Member| Y1
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command.

## Reading it

1. **Registering.** The person registers. A registration is created for the Member role, and the confirmation link goes out as a reaction to the event, not as part of the command.
2. **Inviting.** The operator or an administrator invites someone. The registration is created for the Administrator role, and the same policy sends the link.
3. **Registering again.** Same aggregate, same registration: a new link is sent and the deadline starts again. When the address belongs to an existing user, nothing here happens: an email says so instead, and no registration is touched.
4. **Confirming.** The registration is confirmed, if it is still waiting and before its deadline. For an invitation, this is where the person gives their login, password and names. This is the only event the rest of the system listens to.
5. **After the confirmation.** The rest of the system creates what it needs, in reaction to the same event: UserAccess always creates a user, with the role of the registration. Meetings and Payments create a member and a payer only for the Member role, and they do not wait for each other.
6. **Expiring.** When 48 hours pass without confirmation, the registration is deleted. Nobody asked for it: it is time that triggers it.
