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
        E1[Places kept awaiting payment, in Meetings]:::event --> P1{{Create the payment}}:::policy --> C1[Create the meeting fee payment]:::command --> Y1((Meeting Fee Payment)):::aggregate --> E2[Meeting fee payment created]:::event
        E2 --> P2{{Ask the payment system to collect the amount for the group's payout account}}:::policy
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

1. **Creating.** Payments needs to know that an attendee is awaiting payment, with the amount and the group payout account where it goes. It refuses when that group payout account is deactivated or its payout account is not validated. Otherwise it creates the payment, waiting, with a limit 30 minutes later by default. A policy asks the [payment system](description.md#the-payment-system) to collect the amount for the payout account of the group, and the attendee pays on its page. Payments never takes the money itself.
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
        MT1[Meetings]:::external --> E1[Attendee gave up their place or was removed, in Meetings]:::event --> P1{{Cancel the payment if it waits}}:::policy --> C1[Cancel the meeting fee payment]:::command --> Y1((Meeting Fee Payment)):::aggregate --> E2[Meeting fee payment cancelled]:::event
        MT2[Meetings]:::external --> E3[Meeting cancelled, in Meetings]:::event --> P2{{Cancel every payment of the meeting that waits}}:::policy --> C1
        E2 --> P3{{Ask the payment system to close the payment}}:::policy
    end

    subgraph AE["After the end"]
        direction LR
        PS1[Payment system]:::external --> C2[Mark the meeting fee payment as paid]:::command --> Y2((Meeting Fee Payment)):::aggregate --> E4[Payment recorded after the end of the meeting fee payment]:::event --> OP[The owner refunds in their payment system]:::external
    end
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, pink an external system.

### Reading it

1. **Before paying.** An attendee loses the place by giving it up (they said they will not come, became a host or left the group) or by being removed. Whatever the reason, Payments needs to know it and the payment is cancelled if it still waits, and the payment system is asked to close it. A meeting cancelled cancels every payment of the meeting that waits, by the same command.
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

    PA[Payer]:::actor

    subgraph SU[Setting up]
        direction LR
        C1[Set up a payout account]:::command --> A1((Payout Account)):::aggregate --> E1[Payout account set up]:::event --> P1{{Ask the payment system to validate it}}:::policy
        PS1[Payment system]:::external --> C2[Validate the payout account]:::command --> A2((Payout Account)):::aggregate --> E2[Payout account validated]:::event
    end

    subgraph DE[Deactivating and reactivating]
        direction LR
        C3[Deactivate the payout account]:::command --> A3((Payout Account)):::aggregate --> E3[Payout account deactivated]:::event --> P3{{Cancel the payments waiting on it}}:::policy
        C4[Reactivate the payout account]:::command --> A4((Payout Account)):::aggregate --> E4[Payout account reactivated]:::event
    end

    PA --> C1
    PA --> C3
    PA --> C4
    P1 --> PS1
    E2 -.->|account validated| MT1[Meetings]:::external
    E3 -.->|account deactivated| MT1
    E4 -.->|account reactivated| MT1
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

### Reading it

1. **Setting up.** The payer starts the setup, and the account is pending. A policy asks the payment system to validate it, which the payer completes with it.
2. **Validated.** The answer of the payment system validates the account, which Meetings needs to know. That is what lets an owner choose it for a group.
3. **Deactivating.** The payer deactivates the account whenever they want. The payments waiting on it are cancelled, and no payment is created for it afterwards. Meetings needs to know it, and deactivates the group payout accounts that name it, so that the sign-ups to the meetings with a fee of those groups stop. Payments never asks whether such a meeting exists: the holder decides, and the consequence is that nobody can sign up until they reactivate.
4. **Reactivating.** The holder reactivates it, the payments can be created again, and Meetings reactivates the group payout accounts that name it.

## The group payout account

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef external fill:#FF9FF3,stroke:#c25fb3,color:#000

    MT1[Meetings]:::external --> E1[Group payout account chosen, in Meetings]:::event --> P1{{Keep or activate the group payout account}}:::policy --> C1[Activate the group payout account]:::command --> A1((Group Payout Account)):::aggregate --> E2[Group payout account activated]:::event
    MT2[Meetings]:::external --> E3[Group payout account removed or replaced, in Meetings]:::event --> P2{{Deactivate the group payout account}}:::policy --> C2[Deactivate the group payout account]:::command --> A1
    A1 --> E4[Group payout account deactivated]:::event
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, pink an external system.

### Reading it

1. **Chosen.** The owner chooses a validated payout account for the group in [Meetings](../meetings/group-life.md#the-payout-account-of-the-group), or the candidate does it at the confirmation of a hand-over. Payments needs to know it, and keeps the pair of the payout account and the group as an active group payout account. A pair already known is activated again.
2. **Removed or replaced.** The owner removes it, or a new choice or a transfer replaces it, in Meetings, which deletes it there. Payments needs to know it and keeps the pair as deactivated, with the payments made for it, which are never deleted. From then on, no payment is created for it.
