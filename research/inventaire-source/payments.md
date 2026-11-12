# Payments module

Source: `src/Modules/Payments/` (Application, Domain, Infrastructure, IntegrationEvents, Tests). Paths are relative to the .NET repository root unless noted. See [README.md](README.md) for conventions.

Role: price list, subscription purchase / renewal / expiry for members, meeting fee payments. It is the only module that is **event sourced** (SqlStreamStore on SQL Server) with projectors that maintain read tables; every other module uses EF Core state persistence.

## Persistence model (specific to this module)

- Aggregates derive from `Domain/SeedWork/AggregateRoot.cs` (`Version`, `Load(history)`, abstract `Apply(IDomainEvent)`); they are rebuilt from the event stream (`Infrastructure/AggregateStore/SqlStreamAggregateStore.cs`, `IAggregateStore`).
- Streams and messages live in `payments.Streams` and `payments.Messages` (SqlStreamStore schema).
- `Infrastructure/AggregateStore/SubscriptionsManager.cs` subscribes to the "all" stream from the stored checkpoint (`payments.SubscriptionCheckpoints`, code `All`, `SqlServerCheckpointStore`), deserializes each message with `DomainEventTypeMappings` and calls every `IProjector`. It is started by `PaymentsStartup.RunEventsProjectors()`.
- Projectors (all in `Application/`, discovered by name suffix `Projector` in `Infrastructure/Configuration/DataAccess/DataAccessModule.cs`):

| Projector | Source | Events handled | Target table |
|---|---|---|---|
| `PayerDetailsProjector` | `Payers/GetPayer/` | `PayerCreatedDomainEvent` | `payments.Payers` |
| `PriceListItemsProjector` | `PriceListItems/GetPriceListItem/` | `PriceListItemCreated`, `Activated`, `Deactivated`, `AttributesChanged` | `payments.PriceListItems` |
| `SubscriptionDetailsProjector` | `Subscriptions/GetSubscriptionDetails/` | `SubscriptionCreated`, `SubscriptionRenewed`, `SubscriptionExpired` | `payments.SubscriptionDetails` |
| `SubscriptionPaymentsProjector` | `Subscriptions/GetSubscriptionPayments/` | `SubscriptionCreated`, `SubscriptionPaymentCreated/Paid/Expired`, `SubscriptionRenewalPaymentCreated/Paid` | `payments.SubscriptionPayments` |
| `MeetingFeesProjector` | `MeetingFees/GetMeetingFees/` | `MeetingFeeCreated/Paid/Expired/Canceled` | `payments.MeetingFees` |

## Aggregates

All `: AggregateRoot` (event sourced), source `Modules/Payments/Domain/`.

| Aggregate | Folder | Value objects / helpers | Notes |
|---|---|---|---|
| `Payer` | `Payers/` | `PayerId` | created from `NewUserRegisteredIntegrationEvent`; `IPayerContext` |
| `PriceListItem` | `PriceListItems/` | `PriceListItemCategory` (`New`, `Renewal`), `PriceListItemData`, `PriceList` (VO), pricing strategies `DirectValuePricingStrategy`, `DirectValueFromPriceListPricingStrategy`, `DiscountedValueFromPriceListPricingStrategy` (`IPricingStrategy`) | activate / deactivate / change attributes |
| `SubscriptionPayment` | `SubscriptionPayments/` | `SubscriptionPaymentStatus` (`WaitingForPayment`, `Paid`, `Expired`), `SubscriptionPaymentSnapshot` | rules `PriceForSubscriptionMustBeDefinedRule`, `PriceOfferMustMatchPriceInPriceListRule` |
| `SubscriptionRenewalPayment` | `SubscriptionRenewalPayments/` | `SubscriptionRenewalPaymentStatus`, snapshot | rule `PriceOfferMustMatchPriceInPriceListRule` |
| `Subscription` | `Subscriptions/` | `SubscriptionStatus` (`Active`, `Expired`), `SubscriptionPeriod` (`Month`, `HalfYear`), `SubscriptionDateExpirationCalculator` (new: now + months; renewal: expiration + months if in the future, else now + months), `SubscriberId` | |
| `MeetingFee` | `MeetingFees/` | `MeetingFeeStatus` (`WaitingForPayment`, `Paid`, `Expired`, `Canceled`), `MeetingId`, snapshot | |
| `MeetingFeePayment` | `MeetingFeePayments/` | `MeetingFeePaymentStatus` (`WaitingForPayment`, `Paid`, `Expired`), snapshot | |

Shared seed work (`Domain/SeedWork/`): `MoneyValue`, `IAggregateStore`, `SystemClock`; rules `MoneyMustHaveTheSameCurrencyRule`, `ValueOfMoneyMustNotBeNegativeRule`. `Domain/Users/IUserContext.cs`.

## Commands

Public commands derive from `CommandBase`; internal ones from `InternalCommandBase` (persisted in `payments.InternalCommands`).

| Command | Kind | Fields | Result | Source (`Application/`) |
|---|---|---|---|---|
| `CreatePriceListItemCommand` | public | `SubscriptionPeriodCode, CategoryCode, CountryCode, PriceValue, PriceCurrency` | `Guid` | `PriceListItems/CreatePriceListItem/` |
| `ActivatePriceListItemCommand` | public | `PriceListItemId` | none | `PriceListItems/ActivatePriceListItem/` |
| `DeactivatePriceListItemCommand` | public | `PriceListItemId` | none | `PriceListItems/DeactivatePriceListItem/` |
| `ChangePriceListItemAttributesCommand` | public | `PriceListItemId, CountryCode, SubscriptionPeriodCode, CategoryCode, PriceValue, PriceCurrency` | none | `PriceListItems/ChangePriceListItemAttributes/` |
| `BuySubscriptionCommand` | public | `SubscriptionTypeCode, CountryCode, Value, Currency` | `Guid` (payment id) | `Subscriptions/BuySubscription/` |
| `BuySubscriptionRenewalCommand` | public | `SubscriptionId, SubscriptionTypeCode, CountryCode, Value, Currency` | `Guid` | `Subscriptions/BuySubscriptionRenewal/` |
| `MarkSubscriptionPaymentAsPaidCommand` | public | `SubscriptionPaymentId` | none | `Subscriptions/MarkSubscriptionPaymentAsPaid/` |
| `MarkSubscriptionRenewalPaymentAsPaidCommand` | public | `SubscriptionRenewalPaymentId` | none | `Subscriptions/MarkSubscriptionRenewalPaymentAsPaid/` |
| `ExpireSubscriptionPaymentCommand` | public | `PaymentId` | none | `Subscriptions/ExpireSubscriptionPayment/` |
| `ExpireSubscriptionPaymentsCommand` | public, `IRecurringCommand` | none | none | `Subscriptions/ExpireSubscriptionPayments/` |
| `ExpireSubscriptionsCommand` | public, `IRecurringCommand` | none | none | `Subscriptions/ExpireSubscriptions/` |
| `CreateMeetingFeePaymentCommand` | public | `MeetingFeeId` | `Guid` | `MeetingFees/CreateMeetingFeePayment/` |
| `MarkMeetingFeePaymentAsPaidCommand` | public | `MeetingFeePaymentId` | none | `MeetingFees/MarkMeetingFeePaymentAsPaid/` |
| `CreatePayerCommand` | internal | `Id, UserId, Login, Email, FirstName, LastName, Name` | `Guid` | `Payers/CreatePayer/` |
| `CreateMeetingFeeCommand` | internal | `Id, PayerId, MeetingId, Value, Currency` | `Guid` | `MeetingFees/CreateMeetingFee/` |
| `MarkMeetingFeeAsPaidCommand` | internal | `MeetingFeeId` (base id = `Guid.Empty`) | none | `MeetingFees/MarkMeetingFeeAsPaid/` |
| `CreateSubscriptionCommand` | internal | `Id, SubscriptionPaymentId` | `Guid` | `Subscriptions/CreateSubscription/` |
| `RenewSubscriptionCommand` | internal | `Id, SubscriptionId, SubscriptionRenewalPaymentId` | none | `Subscriptions/RenewSubscription/` |
| `ExpireSubscriptionCommand` | internal | `Id, SubscriptionId` | none | `Subscriptions/ExpireSubscription/` |
| `SendSubscriptionCreationConfirmationEmailCommand` | internal | `Id, SubscriptionId, Email` | none | `Subscriptions/SendSubscriptionCreationConfirmationEmail/` |
| `SendSubscriptionRenewalConfirmationEmailCommand` | internal | `Id, SubscriptionId, Email` | none | `Subscriptions/SendSubscriptionRenewalConfirmationEmail/` |

Plumbing: `ProcessInboxCommand`, `ProcessOutboxCommand`, `ProcessInternalCommandsCommand`. No FluentValidation validator in this module.

## Queries

| Query | Fields | Result | Source (`Application/`) |
|---|---|---|---|
| `GetPriceListItemQuery` | `CountryCode, CategoryCode, PeriodTypeCode` | `PriceListItemMoneyValueDto` | `PriceListItems/GetPriceListItem/` |
| `GetPriceListItemsQuery` | none | `List<PriceListItemDto>` | `PriceListItems/GetPriceListItems/` |
| `GetAuthenticatedPayerSubscriptionQuery` | none | `SubscriptionDetailsDto` | `Subscriptions/GetPayerSubscription/` |
| `GetSubscriptionDetailsQuery` | `SubscriptionId` | `SubscriptionDetailsDto` | `Subscriptions/GetSubscriptionDetails/` |
| `GetSubscriptionPaymentsQuery` | `PayerId` | `List<SubscriptionPaymentDto>` | `Subscriptions/GetSubscriptionPayments/` |
| `GetMeetingFeesQuery` | `MeetingId` | `List<MeetingFeeDto>` | `MeetingFees/GetMeetingFees/` |
| `GetPayerQuery` | `PayerId` | `PayerDto` | `Payers/GetPayer/` |

REST-exposed: `GetPriceListItemQuery`, `GetAuthenticatedPayerSubscriptionQuery`. The other five have no endpoint (checked against `API/.../Modules/Payments/`).

## Domain events

All derive from `DomainEventBase`; they double as the event-store events (`Infrastructure/AggregateStore/DomainEventTypeMappings.cs`). Source `Modules/Payments/Domain/*/Events/`.

| Aggregate | Event | Payload |
|---|---|---|
| Payer | `PayerCreatedDomainEvent` | `PayerId, Login, FirstName, LastName, Name, Email` |
| PriceListItem | `PriceListItemCreatedDomainEvent` | `PriceListItemId, CountryCode, SubscriptionPeriodCode, CategoryCode, Price, Currency, IsActive` |
| | `PriceListItemAttributesChangedDomainEvent` | `PriceListItemId, CountryCode, SubscriptionPeriodCode, CategoryCode, Price, Currency` |
| | `PriceListItemActivatedDomainEvent`, `PriceListItemDeactivatedDomainEvent` | `PriceListItemId` |
| SubscriptionPayment | `SubscriptionPaymentCreatedDomainEvent` | `SubscriptionPaymentId, PayerId, SubscriptionPeriodCode, CountryCode, Status, Value, Currency` |
| | `SubscriptionPaymentPaidDomainEvent`, `SubscriptionPaymentExpiredDomainEvent` | `SubscriptionPaymentId, Status` |
| SubscriptionRenewalPayment | `SubscriptionRenewalPaymentCreatedDomainEvent` | `SubscriptionRenewalPaymentId, PayerId, SubscriptionId, SubscriptionPeriodCode, CountryCode, Status, Value, Currency` |
| | `SubscriptionRenewalPaymentPaidDomainEvent` | `SubscriptionRenewalPaymentId, SubscriptionId, Status` |
| Subscription | `SubscriptionCreatedDomainEvent` | `SubscriptionPaymentId, SubscriptionId, PayerId, SubscriptionPeriodCode, CountryCode, ExpirationDate, Status` |
| | `SubscriptionRenewedDomainEvent` | `SubscriptionId, ExpirationDate, PayerId, SubscriptionPeriodCode, Status` |
| | `SubscriptionExpiredDomainEvent` | `SubscriptionId, Status` |
| MeetingFee | `MeetingFeeCreatedDomainEvent` | `MeetingFeeId, PayerId, MeetingId, FeeValue, FeeCurrency, Status` |
| | `MeetingFeePaidDomainEvent`, `MeetingFeeExpiredDomainEvent`, `MeetingFeeCanceledDomainEvent` | `MeetingFeeId, Status` |
| MeetingFeePayment | `MeetingFeePaymentCreatedDomainEvent` | `MeetingFeePaymentId, MeetingFeeId, Status` |
| | `MeetingFeePaymentPaidDomainEvent`, `MeetingFeePaymentExpiredDomainEvent` | `MeetingFeePaymentId, Status` |

Notifications (`DomainNotificationBase<T>`): `MeetingFeePaidNotification`, `MeetingFeePaymentPaidNotification`, `SubscriptionCreatedNotification`, `SubscriptionPaymentPaidNotification`, `SubscriptionRenewalPaymentPaidNotification`, `SubscriptionRenewedNotification`.

## Integration events

| Direction | Event | Payload | Source / handler |
|---|---|---|---|
| Published | `MeetingFeePaidIntegrationEvent` | `Id, OccurredOn, PayerId, MeetingId` | `Modules/Payments/IntegrationEvents/MeetingFeePaidIntegrationEvent.cs`; published by `MeetingFeePaidNotificationHandler` (`Application/MeetingFees/MarkMeetingFeeAsPaid/`) |
| Published | `SubscriptionExpirationDateChangedIntegrationEvent` | `Id, OccurredOn, PayerId, ExpirationDate` | `Modules/Payments/IntegrationEvents/...`; published by `SubscriptionCreatedNotificationHandler` and `SubscriptionRenewedNotificationHandler` |
| Consumed | `NewUserRegisteredIntegrationEvent` (Registrations) | | `Payers/CreatePayer/NewUserRegisteredIntegrationEventHandler.cs` -> `CreatePayerCommand` |
| Consumed | `MeetingAttendeeAddedIntegrationEvent` (Meetings) | | `MeetingFees/MeetingAttendeeAddedIntegrationEventHandler.cs` -> `CreateMeetingFeeCommand` |
| Declared, no handler | `MeetingGroupProposalAcceptedIntegrationEvent` (Administration) | | subscribed in `Infrastructure/Configuration/EventsBus/EventsBusStartup.cs:22`; no handler for it exists under `Modules/Payments/` |

## Event flows (choreography; no process manager class exists)

1. Meeting fee: Meetings `MeetingAttendeeAddedIntegrationEvent` -> `CreateMeetingFeeCommand` -> `MeetingFee` (WaitingForPayment). `POST meetingFeePayments` -> `MeetingFeePayment`. `PUT meetingFeePayments/{id}/purchased` -> `MeetingFeePaymentPaid` -> `MeetingFeePaymentPaidNotificationHandler` enqueues `MarkMeetingFeeAsPaidCommand` -> `MeetingFeePaid` -> `MeetingFeePaidNotificationHandler` publishes `MeetingFeePaidIntegrationEvent` (consumed by Meetings).
2. Subscription purchase: `BuySubscriptionCommand` -> `SubscriptionPayment`. `POST subscriptionPayments` -> `MarkSubscriptionPaymentAsPaidCommand` -> `SubscriptionPaymentPaid` -> `SubscriptionPaymentPaidNotificationHandler` enqueues `CreateSubscriptionCommand` -> `SubscriptionCreated` -> `SubscriptionCreatedNotificationHandler` publishes `SubscriptionExpirationDateChangedIntegrationEvent`; `SubscriptionCreatedEnqueueEmailConfirmationHandler` enqueues `SendSubscriptionCreationConfirmationEmailCommand`.
3. Subscription renewal: `BuySubscriptionRenewalCommand` -> `SubscriptionRenewalPayment`. `POST subscriptionRenewals` -> `MarkSubscriptionRenewalPaymentAsPaidCommand` -> `SubscriptionRenewalPaymentAsPaidNotificationHandler` enqueues `RenewSubscriptionCommand` -> `SubscriptionRenewed` -> publishes `SubscriptionExpirationDateChangedIntegrationEvent` and enqueues `SendSubscriptionRenewalConfirmationEmailCommand`.
4. Expiry (Quartz): `ExpireSubscriptionPaymentsCommand` selects waiting payments and enqueues one `ExpireSubscriptionPaymentCommand` each; `ExpireSubscriptionsCommand` selects `SubscriptionDetails` with `ExpirationDate < now` and enqueues one `ExpireSubscriptionCommand` each.

Observed detail, recorded as found: `ExpireSubscriptionPaymentsCommandHandler.cs` computes `date = SystemClock.Now + 20 minutes` and selects payments with `Date > @Date` and `Status = WaitingForPayment`.

## REST endpoints

Controllers in `API/CompanyName.MyMeetings.API/Modules/Payments/`; permission constants in `PaymentsPermissions.cs`.

| Verb | Route | Permission | Dispatches | Body / params |
|---|---|---|---|---|
| GET | `/api/payments/priceListItems` | `GetPriceListItem` | `GetPriceListItemQuery` | query string `CountryCode, CategoryCode, PeriodTypeCode` |
| POST | `/api/payments/priceListItems` | `CreatePriceListItem` | `CreatePriceListItemCommand` | `CountryCode, SubscriptionPeriodCode, CategoryCode, PriceValue, PriceCurrency` |
| PATCH | `/api/payments/priceListItems/{priceListItemId}/activate` | `ActivatePriceListItem` | `ActivatePriceListItemCommand` | |
| PATCH | `/api/payments/priceListItems/{priceListItemId}/deactivate` | `DeactivatePriceListItem` | `DeactivatePriceListItemCommand` | |
| PUT | `/api/payments/priceListItems` | `ChangePriceListItemAttributes` | `ChangePriceListItemAttributesCommand` | `PriceListItemId, CountryCode, SubscriptionPeriodCode, CategoryCode, PriceValue, PriceCurrency` |
| GET | `/api/payments/payers/authenticated/subscription` | `GetAuthenticatedPayerSubscription` | `GetAuthenticatedPayerSubscriptionQuery` | |
| POST | `/api/payments/subscriptions` | `BuySubscription` | `BuySubscriptionCommand` | `SubscriptionTypeCode, CountryCode, Value, Currency` |
| POST | `/api/payments/subscriptions/{subscriptionId}/renewals` | `RenewSubscription` | `BuySubscriptionRenewalCommand` | `SubscriptionTypeCode, CountryCode, Value, Currency` |
| POST | `/api/payments/subscriptionPayments` | `RegisterPayment` | `MarkSubscriptionPaymentAsPaidCommand` | `PaymentId` |
| POST | `/api/payments/subscriptionRenewals` | `RegisterPayment` | `MarkSubscriptionRenewalPaymentAsPaidCommand` | `PaymentId` |
| POST | `/api/payments/meetingFeePayments` | `RegisterPayment` | `CreateMeetingFeePaymentCommand` | `MeetingFeeId` |
| PUT | `/api/payments/meetingFeePayments/{meetingFeePaymentId}/purchased` | `RegisterPayment` | `MarkMeetingFeePaymentAsPaidCommand` | |

Role grants (`SeedDatabase.sql`): `Member` has `RegisterPayment, BuySubscription, RenewSubscription, GetAuthenticatedPayerSubscription, GetPriceListItem`; `Administrator` has `CreatePriceListItem, ActivatePriceListItem, DeactivatePriceListItem, ChangePriceListItemAttributes, GetPriceListItem`.

## Database (schema `payments`)

Defined in `Database/CompanyName.MyMeetings.Database/Structure/payments/` (migrations under `Scripts/Migrations/1_0_0_0/`, e.g. `0005_add_payer_id_to_subcription_details_view.sql`).

| Table / type | Columns (type) | Role |
|---|---|---|
| `payments.Streams` | `Id` char(42), `IdOriginal` nvarchar(1000), `IdInternal` int identity PK, `Version` int default -1, `Position` bigint default -1; unique index on `Id` | event store (SqlStreamStore) |
| `payments.Messages` | `StreamIdInternal` int FK, `StreamVersion` int, `Position` bigint identity, `Id` GUID, `Created` datetime, `Type` nvarchar(128), `JsonData` nvarchar(max), `JsonMetadata` nvarchar(max); unique indexes on `Position`, `(StreamIdInternal, Id)`, `(StreamIdInternal, StreamVersion)`; index on `(StreamIdInternal, Created)` | event store |
| `payments.SubscriptionCheckpoints` | `Code` varchar(50), `Position` bigint | projector checkpoint |
| user-defined type `payments.NewStreamMessages` | (`Structure/payments/Types/NewStreamMessages.sql`) | SqlStreamStore append |
| `payments.Payers` | `Id` GUID PK, `Login`, `Email`, `FirstName`, `LastName`, `Name` | projection |
| `payments.PriceListItems` | `Id` GUID, `SubscriptionPeriodCode` varchar(50), `CategoryCode` varchar(50), `CountryCode` varchar(50), `MoneyValue` decimal(18,2), `MoneyCurrency` varchar(50), `IsActive` bit (no PK declared) | projection |
| `payments.SubscriptionDetails` | `Id` GUID PK, `PayerId` GUID, `Period` varchar(50), `Status` varchar(50), `CountryCode` varchar(50), `ExpirationDate` datetime | projection |
| `payments.SubscriptionPayments` | `PaymentId` GUID, `PayerId` GUID, `Type` varchar(50), `Status` varchar(50), `Period` varchar(50), `Date` datetime, `SubscriptionId` GUID null, `MoneyValue` decimal(18,2), `MoneyCurrency` varchar(50) (no PK declared) | projection (holds both payment and renewal-payment rows) |
| `payments.MeetingFees` | `MeetingFeeId` GUID PK, `PayerId` GUID, `MeetingId` GUID, `FeeValue` decimal(18,2), `FeeCurrency` varchar(50), `Status` varchar(50) | projection |
| `payments.InboxMessages`, `OutboxMessages`, `InternalCommands` | same shape as in every module (see [administration.md](administration.md)) | messaging |

No relational table exists for the aggregates themselves; they live only in `payments.Streams` / `payments.Messages` (the EF mapping in `Infrastructure/InternalCommands/` only covers `InternalCommands`).

## Quartz jobs

`Infrastructure/Configuration/Quartz/QuartzStartup.cs`. Scheduler instance name is `"Meetings"` (string in the file). Five jobs:

| Job | Class | Trigger | Action |
|---|---|---|---|
| Expire subscription payments | `ExpireSubscriptionPaymentsJob` (`Quartz/Jobs/`) | cron `0/59 * * ? * *` | runs `ExpireSubscriptionPaymentsCommand` |
| Expire subscriptions | `ExpireSubscriptionsJob` (`Quartz/Jobs/`) | cron `0/59 * * ? * *` | runs `ExpireSubscriptionsCommand` |
| Internal commands | `ProcessInternalCommandsJob` | cron `0/2 * * ? * *` or fixed interval | executes `payments.InternalCommands` |
| Inbox | `ProcessInboxJob` | cron `0/2 * * ? * *` or fixed interval | dispatches inbox messages |
| Outbox | `ProcessOutboxJob` | cron `0/2 * * ? * *` or fixed interval | publishes outbox messages |

## Process managers

None found by name (no `ProcessManager` / `Saga` type in `src/`). The multi-step payment flows above are implemented as chains of notification handlers and internal commands; the projectors plus `SubscriptionsManager` are the only long-running subscription in the module.

## Existing tests

Folder `Modules/Payments/Tests/`.

Unit (`UnitTests/`):
- `Payers/PayerTests.cs`: `CreatePayer_IsSuccessful`
- `PriceListItems/PriceListItemTests.cs`: `CreatePriceListItem_IsSuccessful`, `ActivatePriceListItem_IsSuccessful`, `ActivatePriceListItem_WhenItemIsActive_ThenActivationIgnored`, `DeactivatePriceListItem_IsSuccessful`, `DeactivatePriceListItem_WhenItemNotActive_ThenDeactivationIgnored`, `ChangePriceListItem_IsSuccessful`
- `SubscriptionPayments/SubscriptionPaymentTests.cs`: `BuySubscription_IsSuccessful`, `BuySubscriptionRenewal_WhenPriceDoesNotExist_IsNotPossible`, `PaySubscription_IsSuccessful`, `ExpireSubscription_IsSuccessful`
- `SubscriptionRenewalPayments/SubscriptionRenewalPaymentTests.cs`: `BuySubscriptionRenewal_IsSuccessful`, `BuySubscriptionRenewal_WhenPriceDoesNotExist_IsNotPossible`, `PaySubscriptionRenewal_IsSuccessful`
- `Subscriptions/SubscriptionDateExpirationCalculatorTests.cs`: `CalculateForNew_WhenPeriodMonthIsSelected_Test`, `CalculateForNew_WhenPeriodHalfYearIsSelected_Test`, `CalculateForRenewal_WhenPeriodMonthIsSelected_AndExpireDateIsInTheFuture_ThenMonthsAreAddedToExpireDate`, `CalculateForRenewal_WhenPeriodMonthIsSelected_AndExpireDatePassed_ThenMonthsAreAddedToNow`
- `Subscriptions/SubscriptionTests.cs`: `CreateSubscription_IsSuccessful`, `RenewSubscription_IsSuccessful`, `ExpireSubscription_IsSuccessful`

Integration (`IntegrationTests/`, real SQL Server):
- `MeetingFees/MeetingFeesTests.cs`: `Create_Then_Pay_MeetingFee_Test`
- `Payers/PayerTests.cs`: `CreatePayer_Test`
- `Subscriptions/BuySubscriptionTests.cs`: `BuySubscription_Test`
- `Subscriptions/SubscriptionPaymentsTests.cs`: `SubscriptionPayment_Expiration_Test`
- `Subscriptions/SubscriptionLifecycleTests.cs`: `Subscription_Buy_ThenRenew_ThenExpire_Test` (class-level `[Ignore("Sometimes fails, to check why")]` in source)

Architecture: same 10 + 9 + 3 rules as the other modules (see [administration.md](administration.md)).

Not covered by any test in the module: price list integration (no integration test), meeting fee payment, subscription renewal endpoint paths.
