# Payments: event storming of the price list

Event storming lays out a process as a sequence of **commands** (what someone asks), **aggregates** (what decides), **events** (what happened) and **policies** (what the system does in reaction). The [conceptual model](price-list-conceptual-model.md) shows the structure; this page shows the behaviour. The rules are in [the price list](price-list.md).

```mermaid
flowchart LR
    classDef command fill:#54A0FF,stroke:#2e6fc0,color:#000
    classDef aggregate fill:#FFEAA7,stroke:#F39C12,color:#000
    classDef event fill:#FF9F43,stroke:#c26a12,color:#000
    classDef policy fill:#9B59B6,stroke:#6c3483,color:#000
    classDef actor fill:#FECA57,stroke:#c79a1c,color:#000
    classDef readmodel fill:#10AC84,stroke:#0b7a5e,color:#000
    classDef hotspot fill:#FF6B81,stroke:#c0394f,color:#000

    AD[Administrator]:::actor

    subgraph Keeping[Keeping the list]
        direction LR
        C1[Create a price list item]:::command --> P0{{Refuse if another item is active for the same country, period and kind}}:::policy --> I1((Price List Item)):::aggregate --> E1[Price list item created]:::event
        C2[Change the price]:::command --> I2((Price List Item)):::aggregate --> E2[Price of the item changed]:::event
        C3[Deactivate the item]:::command --> I3((Price List Item)):::aggregate --> E3[Price list item deactivated]:::event
        C4[Activate the item]:::command --> P5{{Refuse if another item is active for the same country, period and kind}}:::policy --> I4((Price List Item)):::aggregate --> E4[Price list item activated]:::event
        H1[Two administrators at the same instant]:::hotspot
    end

    subgraph Effects
        direction LR
        E2 --> P1{{Whenever the item is active and of kind Renewal}}:::policy --> P2{{Email the payers whose automatic renewal depends on it}}:::policy
    end

    subgraph Reading
        direction LR
        R1[Whole price list]:::readmodel
        R2[Price a payer would pay]:::readmodel
    end

    AD --> C1
    AD --> C2
    AD --> C3
    AD --> C4
    E1 --> R1
    E2 --> R1
    E3 --> R1
    E4 --> R1
    E1 --> R2
    E2 --> R2
    E3 --> R2
    E4 --> R2
```

Blue is a command, pale yellow an aggregate, orange an event, purple a policy, darker yellow someone who asks for a command, green a read model, red an open question.

## Reading it

1. **Creating.** The item is created active. A policy first checks that no other item is active for the same country, period and kind. The race of two administrators is a hotspot left to a technical choice.
2. **Changing the price.** Only the price changes, never the country, the period or the kind.
3. **Deactivating and activating.** Both do nothing when the item is already in that state. Activating is refused when another item is active for the same triple.
4. **The email.** It follows a change of price only when the item is active and of the kind *renewal*. It goes to the payers whose [automatic renewal](subscriptions-event-storming.md#automatic-renewal) has the same country and the same period. Nothing goes out for the other events.
5. **Reading.** The administrator reads the whole list. A payer reads only the price they would pay, which comes from the active item, and sees that no price is offered when there is none.
