# Payments: event storming of subscriptions

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](subscriptions-conceptual-model.md) shows the structure; this page shows the behaviour. The rules are in [subscriptions](subscriptions.md).

## Buying and renewing

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c25fb3,color:#000

    PA[Payer]:::actor

    subgraph Buying
        direction LR
        B1[Buy a subscription]:::command --> SP1((Subscription Payment)):::aggregate --> E1[Subscription payment created]:::event
        E1 --> P1{{Ask the payment system to collect the price}}:::policy
        PS1[Payment system]:::external --> M1[Mark the subscription payment as paid]:::command --> SP2((Subscription Payment)):::aggregate --> E2[Subscription payment paid]:::event
        E2 --> P2{{Create the subscription}}:::policy --> C1[Create the subscription]:::command --> S1((Subscription)):::aggregate --> E3[Subscription created]:::event
        T1[Time to pay passed]:::event --> P3{{Expire the payment}}:::policy --> X1[Expire the subscription payment]:::command --> SP3((Subscription Payment)):::aggregate --> E4[Subscription payment expired]:::event
    end

    subgraph Renewing
        direction LR
        B2[Renew the subscription]:::command --> RP1((Subscription Renewal Payment)):::aggregate --> E5[Subscription renewal payment created]:::event
        E5 --> P4{{Ask the payment system to collect the price}}:::policy
        PS2[Payment system]:::external --> M2[Mark the renewal payment as paid]:::command --> RP2((Subscription Renewal Payment)):::aggregate --> E6[Subscription renewal payment paid]:::event
        E6 --> P5{{Renew the subscription}}:::policy --> C2[Renew the subscription]:::command --> S2((Subscription)):::aggregate --> E7[Subscription renewed]:::event
        T2[Time to pay passed]:::event --> P6{{Expire the payment}}:::policy --> X2[Expire the renewal payment]:::command --> RP3((Subscription Renewal Payment)):::aggregate --> E8[Subscription renewal payment expired]:::event
    end

    PA --> B1
    PA --> B2
    P1 --> PS1
    P4 --> PS2

    E3 --> P7{{Announce the new expiration date}}:::policy
    E7 --> P7
    E3 --> P8{{Email the payer}}:::policy
    E7 --> P8
    P7 --> MT[Meetings]:::external
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

### Reading it

1. **Buying.** A payer without a subscription buys one. The price must be the active price of the list, and the payer must have no payment waiting. The command creates a subscription payment, which holds the period, the country and the price.
2. **Collecting.** Creating the payment is not enough: a policy asks the [payment system](description.md#the-payment-system) to collect the price. Payments never takes the money itself.
3. **Paid.** The payment system's answer is the only thing that marks the payment as paid. That event, and not the command of the payer, is what creates the subscription, active, with an expiration date one period after the moment of payment.
4. **Expired.** When time passes without an answer, nobody asked: the payment expires, nothing is created, and the payer may start again.
5. **Renewing.** A payer with a subscription, active or expired, renews it by the same path, with a renewal payment and the price of the renewal kind. The paid event renews the subscription: the period adds up to the date when the subscription was still active, and starts at the payment otherwise.
6. **The announcement.** Both a creation and a renewal bring a new expiration date, which Payments announces to the rest of the application. [Meetings](../meetings/group-life-event-storming.md) listens and records it. The payer also gets an email.

## Automatic renewal

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c25fb3,color:#000

    PA[Payer]:::actor

    subgraph Option
        direction LR
        O1[Turn automatic renewal on]:::command --> S1((Subscription)):::aggregate --> E1[Automatic renewal turned on]:::event
        E1 --> P1{{Ask the payment system to repeat the payment}}:::policy
        O2[Turn automatic renewal off]:::command --> S2((Subscription)):::aggregate --> E2[Automatic renewal turned off]:::event
        E2 --> P2{{Ask the payment system to stop}}:::policy
    end

    subgraph EP[Each period]
        direction LR
        PS3[Payment system]:::external --> M1[Record a period paid]:::command --> S3((Subscription)):::aggregate --> E3[Subscription renewed]:::event
        PS4[Payment system]:::external --> M2[Record that the payment system gave up]:::command --> S4((Subscription)):::aggregate --> E4[Automatic renewal turned off]:::event
    end

    subgraph Price
        direction LR
        E5[Price of a list item changed]:::event --> P3{{Email the payers whose automatic renewal is concerned}}:::policy
    end

    PA --> O1
    PA --> O2
    P1 --> PS1[Payment system]:::external
    P2 --> PS2[Payment system]:::external
    E3 --> P4{{Announce the new expiration date}}:::policy --> MT[Meetings]:::external
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

### Reading it

1. **Turning it on.** The payer chooses a period and agrees to be charged again at the end of each period. The subscription keeps the period, the country and the price, and the payment system is asked to repeat the payment.
2. **Turning it off.** The payer turns it off whenever they want, and the payment system is asked to stop. The subscription runs until its date.
3. **A period paid.** Nobody in Payments asks for the renewal: the payment system announces a period paid, and the subscription is renewed exactly as by a manual renewal, with the same announcement and the same email.
4. **Given up.** After its own retries, the payment system announces that it gives up, and the option is turned off. Between a first failure and that moment, Payments does nothing.
5. **The price.** When a price of the list changes, an email goes to every payer whose automatic renewal depends on that price. The renewal keeps its fixed price until the next period, where the new one applies. The payer who refuses turns the option off.

## When a subscription ends

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c25fb3,color:#000

    subgraph Reminder
        direction LR
        T1[Reminder date reached, 30 days before by default]:::event --> P1{{Remind, whichever way applies}}:::policy
        P1 -->|automatic renewal off| A1[Announce that the subscription is about to expire]:::command --> S1((Subscription)):::aggregate --> E1[Subscription about to expire]:::event
        P1 -->|automatic renewal on| EM1[Email the payer that it is about to renew]:::command
        E2[Automatic renewal turned off after that date]:::event --> A1
    end

    subgraph Expiration
        direction LR
        T2[Expiration date passed]:::event --> X1[Expire the subscription]:::command --> S2((Subscription)):::aggregate --> E3[Subscription expired]:::event
    end

    E1 --> MT[Meetings]:::external
    E3 --> MT
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, pink an external system.

### Reading it

1. **The reminder date.** Time brings it, a configurable delay before the expiration date. Two cases: with automatic renewal off, the subscription announces that it is about to expire, once for each expiration date; with it on, only an email to the payer goes out, with the date and the price, and nothing is announced.
2. **Turned off late.** When automatic renewal is turned off after the reminder date has passed, the announcement is made at once, since its moment is already behind.
3. **Expiring.** When the expiration date passes, the subscription expires, whatever the option, and the payer can renew it. This is the case when an automatic payment did not arrive in time.
4. **What Meetings does.** Meetings listens to both announcements. It sends the reminder and the notice to the owner, and it decides from the date, not from these announcements, whether a group is [covered](../meetings/group-life.md#covering-the-group).
