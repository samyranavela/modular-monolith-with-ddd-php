# Payments: event storming of meeting fees

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](meeting-fees-conceptual-model.md) shows the structure; this page shows the behaviour. The rules are in [meeting fees](meeting-fees.md).

## Paying for a place

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef external fill:#FF9FF3,stroke:#c25fb3,color:#000

    MT1[Meetings]:::external

    subgraph Creating
        direction LR
        E1[Attendee awaiting payment]:::event --> P1{{Create the payment}}:::policy --> C1[Create the meeting fee payment]:::command --> Y1((Meeting Fee Payment)):::aggregate --> E2[Meeting fee payment created]:::event
        E2 --> P2{{Ask the payment system to collect the amount for the owner's payout account}}:::policy
    end

    subgraph Paid
        direction LR
        PS1[Payment system]:::external --> C2[Mark the meeting fee payment as paid]:::command --> Y2((Meeting Fee Payment)):::aggregate --> E3[Meeting fee payment paid]:::event
    end

    subgraph RT["Running out of time"]
        direction LR
        T1[Wait limit passed]:::event --> P3{{Expire the payment}}:::policy --> C3[Expire the meeting fee payment]:::command --> Y3((Meeting Fee Payment)):::aggregate --> E4[Meeting fee payment expired]:::event
    end

    MT1 --> E1
    P2 --> PS1
    E3 -.->|payment paid| MT2[Meetings]:::external
    E4 -.->|payment expired| MT2
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, pink an external system.

### Reading it

1. **Creating.** When Meetings says that an attendee is awaiting payment, with the amount and the owner who receives it, Payments creates the payment, waiting, with a limit 30 minutes later by default. A policy asks the [payment system](description.md#the-payment-system) to collect the amount for the owner's payout account, and the attendee pays on its page. Payments never takes the money itself.
2. **Paid.** The answer of the payment system marks the payment as paid. Meetings confirms the place once it knows the payment is paid, and not before.
3. **Running out of time.** Nobody asked: time did. The payment expires, and Meetings, which needs to know it, releases the places. The member signs up again if they still want to come.

## Cancelling

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef external fill:#FF9FF3,stroke:#c25fb3,color:#000

    subgraph BP["Before payment"]
        direction LR
        MT1[Meetings]:::external --> E1[Attendee lost the place before paying]:::event --> P1{{Cancel the payment if it waits}}:::policy --> C1[Cancel the meeting fee payment]:::command --> Y1((Meeting Fee Payment)):::aggregate --> E2[Meeting fee payment cancelled]:::event
        MT2[Meetings]:::external --> E3[Meeting cancelled]:::event --> P2{{Cancel every payment of the meeting that waits}}:::policy --> C1
        E2 --> P3{{Ask the payment system to close the payment}}:::policy
    end

    subgraph AE["After the end"]
        direction LR
        PS1[Payment system]:::external --> C2[Mark the meeting fee payment as paid]:::command --> Y2((Meeting Fee Payment)):::aggregate --> E4[Payment recorded after the end of the meeting fee payment]:::event --> OP[The owner refunds in their payment system]:::external
    end
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, pink an external system.

### Reading it

1. **Before paying.** Whatever the reason, Payments needs to know it and the payment is cancelled if it still waits, and the payment system is asked to close it. A meeting cancelled cancels every payment of the meeting that waits, by the same command.
2. **After the end.** A payment can reach Payments after its end, because it was already with the payment system. The payment records it, its status and the place do not change, and a refund, if any, is the owner's, in their own payment system.
3. **Already paid.** A paid payment is left as it is when its attendee later loses the place or the meeting is cancelled. The application never refunds.

## The payout account

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c25fb3,color:#000

    OW[Owner]:::actor

    subgraph SU[Setting up]
        direction LR
        C1[Set up a payout account]:::command --> A1((Payout Account)):::aggregate --> E1[Payout account set up]:::event --> P1{{Ask the payment system to validate it}}:::policy
        PS1[Payment system]:::external --> C2[Validate the payout account]:::command --> A2((Payout Account)):::aggregate --> E2[Payout account validated]:::event
    end

    subgraph Removing
        direction LR
        C3[Remove the payout account]:::command --> G3{{Refuse if Meetings says a meeting with a fee has yet to start}}:::policy --> A3((Payout Account)):::aggregate --> E3[Payout account removed]:::event
        G3 -.-> MT2[Meetings]:::external
    end

    OW --> C1
    OW --> C3
    P1 --> PS1
    E2 -.->|account validated| MT1[Meetings]:::external
    E3 -.->|account removed| MT1
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

### Reading it

1. **Setting up.** The owner starts the setup, and the account is pending. A policy asks the payment system to validate it, which the owner completes with it.
2. **Validated.** The answer of the payment system validates the account, which Meetings needs to know. That is what lets the groups of the owner give a meeting a fee. When it replaces an earlier account, the new one takes over at that moment.
3. **Removing.** The owner removes the account. Payments needs to know whether a meeting that has a fee, of the owner's groups, has yet to start, and refuses if there is one. Meetings needs to know the removal, so that it stops accepting a fee for them.
