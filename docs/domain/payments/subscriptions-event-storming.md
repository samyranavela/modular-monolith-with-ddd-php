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

    E3 --> P8{{Notify the payer}}:::policy
    E7 --> P8
    P8 --> NT[Notifications]:::external
    E3 -.->|new expiration date| MT[Meetings]:::external
    E7 -.->|new expiration date| MT
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

### Reading it

1. **Buying.** A payer without a subscription buys one. The price must be the active price of the list, and the payer must have no payment waiting. The command creates a subscription payment, which holds the period and the price.
2. **Collecting.** Creating the payment is not enough: a policy asks the [payment system](description.md#the-payment-system) to collect the price. Payments never takes the money itself.
3. **Paid.** The payment system's answer is the only thing that marks the payment as paid. That event, and not the command of the payer, is what creates the subscription, active, with an expiration date one period after the moment of payment.
4. **Expired.** When time passes without an answer, nobody asked: the payment expires, nothing is created, and the payer may start again.
5. **Renewing.** A payer with a subscription, active or expired, renews it by the same path, with a renewal payment and the same active price of the list. The paid event renews the subscription: the period adds up to the date when the subscription was still active, and starts at the payment otherwise.
6. **The expiration date.** Both a creation and a renewal bring a new expiration date, which [Meetings](../meetings/group-life-event-storming.md) needs to know (dotted arrow). The payer is also notified.

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
        PS3[Payment system]:::external --> M1[Record a period paid]:::command --> S3((Subscription)):::aggregate --> E3[Subscription renewed]:::event --> P7{{Notify the payer}}:::policy
        PS4[Payment system]:::external --> M2[Record that the payment system gave up]:::command --> S4((Subscription)):::aggregate --> E4[Automatic renewal turned off]:::event --> P6{{Notify the payer}}:::policy
    end

    subgraph Price
        direction LR
        E5[Price of the item changed]:::event --> P4{{Whenever the item is active, and an automatic renewal has the same period}}:::policy
        P4 --> P3{{Notify the payers whose automatic renewal depends on it}}:::policy
        P4 --> P5{{Take the new price for their next payment}}:::policy
    end

    PA --> O1
    PA --> O2
    P1 --> PS1[Payment system]:::external
    P2 --> PS2[Payment system]:::external
    P7 --> NT[Notifications]:::external
    P6 --> NT
    P3 --> NT
    E3 -.->|new expiration date| MT[Meetings]:::external
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

### Reading it

1. **Turning it on.** The payer chooses a period and agrees to be charged again at the end of each period. The subscription keeps the period and the price, and the payment system is asked to repeat the payment.
2. **Turning it off.** The payer turns it off whenever they want, and the payment system is asked to stop. The subscription runs until its date.
3. **A period paid.** Nobody in Payments asks for the renewal: the payment system confirms a period paid, and the subscription is renewed exactly as by a manual renewal, with the same new expiration date and the same notification.
4. **Given up.** After its own retries, the payment system reports that it gives up, and the option is turned off, and a notification tells the payer. Between a first failure and that moment, Payments does nothing.
5. **The price.** When the price of an active item changes, every automatic renewal that depends on it, with the same period, takes the new price for its next payment, and a notification tells the payer. A trial not yet paid depends on the same item as any other automatic renewal. The payer who refuses turns the option off. A deactivation changes nothing.

## Trying

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c25fb3,color:#000

    PA[Payer]:::actor

    subgraph Start
        direction LR
        C1[Start a trial]:::command --> S1((Subscription)):::aggregate --> E1[Trial started]:::event
        E1 --> P1{{Ask the payment system to keep the payment method and repeat the payment}}:::policy
        E1 --> P2{{Notify the payer}}:::policy
    end

    subgraph TE["End of the trial"]
        direction LR
        T1[Reminder date reached, 2 days before the end by default]:::event --> P3{{Notify the payer that the first payment is near}}:::policy
        PS1[Payment system]:::external --> M1[Record a period paid]:::command --> S2((Subscription)):::aggregate --> E2[Subscription renewed]:::event --> P6{{Notify the payer}}:::policy
        PS2[Payment system]:::external --> M2[Record that the payment system gave up]:::command --> S3((Subscription)):::aggregate --> E3[Automatic renewal turned off]:::event --> P5{{Notify the payer}}:::policy
    end

    PA --> C1
    P1 --> PS3[Payment system]:::external
    P2 --> NT[Notifications]:::external
    P3 --> NT
    P6 --> NT
    P5 --> NT
    E1 -.->|new expiration date| MT[Meetings]:::external
    E2 -.->|new expiration date| MT
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

### Reading it

1. **Starting.** A payer who never held a subscription starts a trial. The command creates the subscription at once, active, expiring after the length of the trial, with automatic renewal on for the chosen period. No payment is created: nothing is charged yet. The payer saw the active price and agrees to be charged the price active at the end, being notified if it changes.
2. **The expiration date.** The creation brings an expiration date, which [Meetings](../meetings/group-life-event-storming.md) needs to know like any other, so the groups are covered during the trial.
3. **The end.** Nobody in Payments asks for the first payment: the payment system confirms a period paid, at the active price at that moment, and the subscription is renewed as by any automatic renewal.
4. **Not continuing.** The payer turns automatic renewal off, as in [automatic renewal](#automatic-renewal), and the subscription ends with the trial.

## Changing the period

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef external fill:#FF9FF3,stroke:#c25fb3,color:#000

    PA[Payer]:::actor
    C1[Change the period of the automatic renewal]:::command --> S1((Subscription)):::aggregate --> E1[Period of the automatic renewal changed]:::event
    E1 --> P1{{Ask the payment system to charge the new price at the end of the period}}:::policy
    E1 --> P2{{Notify the payer}}:::policy
    PS1[Payment system]:::external --> M1[Record a period paid]:::command --> S2((Subscription)):::aggregate --> E2[Subscription renewed]:::event --> P3{{Notify the payer}}:::policy
    PA --> C1
    P1 --> PS2[Payment system]:::external
    P2 --> NT[Notifications]:::external
    P3 --> NT
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, pink an external system.

### Reading it

1. **Asking.** The subscription must be active. It changes the period of the next payment and nothing else: its expiration date does not move.
2. **At the end of the period.** The payment system takes the new price and confirms a period paid. The subscription is renewed for the new period, which starts exactly at the old expiration date.
3. **No credit, no refund.** Neither is computed: the old period is not shortened and the new one is not started early.

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
        T1[Reminder date reached, 30 days before the end of a year or 7 days before the end of a month by default]:::event --> P1{{Remind, whichever way applies}}:::policy
        P1 -->|automatic renewal off| A1[Note the subscription as about to expire]:::command --> S1((Subscription)):::aggregate --> E1[Subscription about to expire]:::event
        P1 -->|automatic renewal on| EM1{{Notify the payer that it is about to renew}}:::policy
        E2[Automatic renewal turned off after that date]:::event --> A1
    end

    subgraph Expiration
        direction LR
        T2[Expiration date passed]:::event --> X1[Expire the subscription]:::command --> S2((Subscription)):::aggregate --> E3[Subscription expired]:::event
    end

    E1 --> P2{{Notify the payer that it is about to expire}}:::policy
    E3 --> P3{{Notify the payer that it has expired}}:::policy
    EM1 --> NT[Notifications]:::external
    P2 --> NT
    P3 --> NT
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, pink an external system.

### Reading it

1. **The reminder date.** Time brings it, a configurable delay before the expiration date, which depends on the period in progress: 30 days for a year and 7 days for a month by default. Two cases: with automatic renewal off, the subscription is noted as about to expire, once for each expiration date, and the payer is notified; with it on, only a notification to the payer goes out, with the date and the price, and the subscription is not about to expire.
2. **Turned off late.** When automatic renewal is turned off after the reminder date has passed, it is noted as about to expire at once, since its moment is already behind.
3. **Expiring.** When the expiration date passes, the subscription expires, whatever the option, and the payer can renew it. This is the case when an automatic payment did not arrive in time.
4. **Notifying.** Payments notifies the payer of both facts, whether or not they own a group. Meetings needs neither: it decides from the expiration date whether a group is [covered](../meetings/group-life.md#covering-the-group).
