# Meetings module

Source: `src/Modules/Meetings/` (Application, Domain, Infrastructure, IntegrationEvents, Tests). Paths are relative to the .NET repository root unless noted. See [README.md](README.md) for conventions.

Role: core domain. Members propose meeting groups, groups host meetings, members join groups, RSVP to meetings (attend / not attend / waitlist), comment on meetings and like comments, and pay meeting fees (through Payments). State persistence with EF Core (write side) and Dapper over SQL views (read side).

## Aggregates

Source `Modules/Meetings/Domain/`. Aggregate roots are `Entity, IAggregateRoot`; child entities are `Entity` only (owned collections in EF).

| Aggregate root | Child entities | Value objects | Folder |
|---|---|---|---|
| `MeetingGroupProposal` | | `MeetingGroupProposalId`, `MeetingGroupProposalStatus` (`InVerification`, `Accepted`; internal statics in `MeetingGroupProposalStatus.cs`), `MeetingGroupLocation` | `MeetingGroupProposals/` |
| `MeetingGroup` | `MeetingGroupMember` (`_members`) | `MeetingGroupId`, `MeetingGroupLocation`, `MeetingGroupMemberRole` (`Organizer`, `Member`); policies `MeetingGroupExpirationDatePolicy`, `MeetingGroupMemberData` | `MeetingGroups/` |
| `Meeting` | `MeetingAttendee` (`_attendees`), `MeetingNotAttendee` (`_notAttendees`), `MeetingWaitlistMember` (`_waitlistMembers`) | `MeetingId`, `MeetingTerm`, `Term` (RSVP), `MeetingLocation`, `MeetingLimits`, `MoneyValue`, `MeetingAttendeeRole` (`Host`, `Attendee`) | `Meetings/` |
| `MeetingComment` | | `MeetingCommentId` | `MeetingComments/` |
| `MeetingMemberCommentLike` | | `MeetingMemberCommentLikeId` | `MeetingMemberCommentLikes/` |
| `MeetingCommentingConfiguration` | | `MeetingCommentingConfigurationId` | `MeetingCommentingConfigurations/` |
| `Member` | | `MemberId`, `MeetingGroupMemberData`, `IMemberContext` | `Members/` |
| `MemberSubscription` | | `MemberSubscriptionId` | `Members/MemberSubscriptions/` |

`Meeting` public behaviour (`Domain/Meetings/Meeting.cs`): `ChangeMainAttributes`, `AddAttendee`, `AddNotAttendee`, `ChangeNotAttendeeDecision`, `SignUpMemberToWaitlist`, `SignOffMemberFromWaitlist`, `SetHostRole`, `SetAttendeeRole`, `Cancel`, `RemoveAttendee`, `MarkAttendeeFeeAsPayed`, `AddComment`, `CreateCommentingConfiguration`. `Domain/SharedKernel/SystemClock.cs` provides the clock.

Business rules (`Domain/*/Rules/`, 37 classes):
- Commenting configuration: `MeetingCommentingCanBeDisabledOnlyByGroupOrganizerRule`, `MeetingCommentingCanBeEnabledOnlyByGroupOrganizerRule`
- Comments: `CommentCanBeAddedOnlyByMeetingGroupMemberRule`, `CommentCanBeCreatedOnlyIfCommentingForMeetingEnabledRule`, `CommentCanBeEditedOnlyIfCommentingForMeetingEnabledRule`, `CommentCanBeLikedOnlyByMeetingGroupMemberRule`, `CommentCannotBeLikedByTheSameMemberMoreThanOnceRule`, `CommentTextMustBeProvidedRule`, `MeetingCommentCanBeEditedOnlyByAuthorRule`, `MeetingCommentCanBeRemovedOnlyByAuthorOrGroupOrganizerRule`, `RemovingReasonCanBeProvidedOnlyByGroupOrganizerRule`
- Proposals: `MeetingGroupProposalCannotBeAcceptedMoreThanOnceRule`
- Groups: `MeetingCanBeOrganizedOnlyByPayedGroupRule`, `MeetingGroupMemberCannotBeAddedTwiceRule`, `MeetingHostMustBeAMeetingGroupMemberRule`, `NotActualGroupMemberCannotLeaveGroupRule`
- Meetings: `AttendeeCanBeAddedOnlyInRsvpTermRule`, `AttendeesLimitCannotBeChangedToSmallerThanActiveAttendeesRule`, `MeetingAttendeeMustBeAMemberOfGroupRule`, `MeetingAttendeesLimitCannotBeNegativeRule`, `MeetingAttendeesLimitMustBeGreaterThanGuestsLimitRule`, `MeetingAttendeesNumberIsAboveLimitRule`, `MeetingCannotBeChangedAfterStartRule`, `MeetingGuestsLimitCannotBeNegativeRule`, `MeetingGuestsNumberIsAboveLimitRule`, `MeetingMustHaveAtLeastOneHostRule`, `MemberCannotBeAnAttendeeOfMeetingMoreThanOnceRule`, `MemberCannotBeMoreThanOnceOnMeetingWaitlistRule`, `MemberCannotBeNotAttendeeTwiceRule`, `MemberCannotHaveSetAttendeeRoleMoreThanOnceRule`, `MemberOnWaitlistMustBeAMemberOfGroupRule`, `NotActiveMemberOfWaitlistCannotBeSignedOffRule`, `NotActiveNotAttendeeCannotChangeDecisionRule`, `OnlyActiveAttendeeCanBeRemovedFromMeetingRule`, `OnlyMeetingAttendeeCanHaveChangedRoleRule`, `OnlyMeetingOrGroupOrganizerCanSetMeetingMemberRolesRule`, `ReasonOfRemovingAttendeeFromMeetingMustBeProvidedRule`

## Commands

Public = `CommandBase` / `CommandBase<T>` (exposed through `IMeetingsModule`); internal = `InternalCommandBase` (persisted in `meetings.InternalCommands`). Source folders under `Application/`.

| Command | Kind | Fields | Result | Folder |
|---|---|---|---|---|
| `ProposeMeetingGroupCommand` | public | `Name, Description, LocationCity, LocationCountryCode` | `Guid` | `MeetingGroupProposals/ProposeMeetingGroup/` (validator) |
| `AcceptMeetingGroupProposalCommand` | internal | `Id, MeetingGroupProposalId` | none | `MeetingGroupProposals/AcceptMeetingGroupProposal/` (validator) |
| `CreateNewMeetingGroupCommand` | internal | `Id, MeetingGroupProposalId` | none | `MeetingGroups/CreateNewMeetingGroup/` |
| `EditMeetingGroupGeneralAttributesCommand` | public | `MeetingGroupId, Name, Description, LocationCity, LocationCountry` | none | `MeetingGroups/EditMeetingGroupGeneralAttributes/` |
| `JoinToGroupCommand` | public | `MeetingGroupId` | none | `MeetingGroups/JoinToGroup/` |
| `LeaveMeetingGroupCommand` | public | `MeetingGroupId` | none | `MeetingGroups/LeaveMeetingGroup/` |
| `SetMeetingGroupExpirationDateCommand` | internal | `Id, MeetingGroupId, DateTo` | none | `MeetingGroups/SetMeetingGroupExpirationDate/` |
| `SendMeetingGroupCreatedEmailCommand` | internal | `Id, MeetingGroupId, CreatorId` | none | `MeetingGroups/SendMeetingGroupCreatedEmail/` |
| `CreateMeetingCommand` | public | `MeetingGroupId, Title, TermStartDate, TermEndDate, Description, MeetingLocationName, MeetingLocationAddress, MeetingLocationPostalCode, MeetingLocationCity, AttendeesLimit?, GuestsLimit, RSVPTermStartDate?, RSVPTermEndDate?, EventFeeValue?, EventFeeCurrency, HostMemberIds` | `Guid` | `Meetings/CreateMeeting/` |
| `ChangeMeetingMainAttributesCommand` | public | same fields as create, with `MeetingId` instead of `MeetingGroupId`, no hosts | none | `Meetings/ChangeMeetingMainAttributes/` |
| `AddMeetingAttendeeCommand` | public | `MeetingId, GuestsNumber` | none | `Meetings/AddMeetingAttendee/` |
| `RemoveMeetingAttendeeCommand` | public | `MeetingId, AttendeeId, RemovingReason` | none | `Meetings/RemoveMeetingAttendee/` |
| `AddMeetingNotAttendeeCommand` | public | `MeetingId` | none | `Meetings/AddMeetingNotAttendee/` |
| `ChangeNotAttendeeDecisionCommand` | public | `MeetingId` | none | `Meetings/ChangeNotAttendeeDecision/` |
| `SignUpMemberToWaitlistCommand` | public | `MeetingId` | none | `Meetings/SignUpMemberToWaitlist/` |
| `SignOffMemberFromWaitlistCommand` | public | `MeetingId` | none | `Meetings/SignOffMemberFromWaitlist/` |
| `SetMeetingHostRoleCommand` | public | `MemberId, MeetingId` | none | `Meetings/SetMeetingHostRole/` |
| `SetMeetingAttendeeRoleCommand` | public | `MemberId, MeetingId` | none | `Meetings/SetMeetingAttendeeRole/` |
| `CancelMeetingCommand` | public | `MeetingId` | none | `Meetings/CancelMeeting/` |
| `MarkMeetingAttendeeFeeAsPayedCommand` | internal | `Id, MemberId, MeetingId` | none | `Meetings/` |
| `SendMeetingAttendeeAddedEmailCommand` | internal | `Id, AttendeeId, MeetingId` | none | `Meetings/SendMeetingAttendeeAddedEmail/` |
| `AddMeetingCommentCommand` | public | `MeetingId, Comment` | `Guid` | `MeetingComments/AddMeetingComment/` (validator) |
| `EditMeetingCommentCommand` | public | `MeetingCommentId, EditedComment` | none | `MeetingComments/EditMeetingComment/` (validator) |
| `RemoveMeetingCommentCommand` | public | `MeetingCommentId, Reason` | none | `MeetingComments/RemoveMeetingComment/` |
| `AddReplyToMeetingCommentCommand` | public | `InReplyToCommentId, Reply` | `Guid` | `MeetingComments/AddMeetingCommentReply/` |
| `AddMeetingCommentLikeCommand` | public | `MeetingCommentId` | none | `MeetingComments/AddMeetingCommentLike/` |
| `RemoveMeetingCommentLikeCommand` | public | `MeetingCommentId` | none | `MeetingComments/RemoveMeetingCommentLike/` |
| `EnableMeetingCommentingConfigurationCommand` | public | `MeetingId` | none | `MeetingCommentingConfigurations/EnableMeetingCommentingConfiguration/` |
| `DisableMeetingCommentingConfigurationCommand` | public | `MeetingId` | none | `MeetingCommentingConfigurations/DisableMeetingCommentingConfiguration/` |
| `CreateMemberCommand` | internal | `Id, MemberId, Login, Email, FirstName, LastName, Name` | none | `Members/CreateMember/` |
| `ChangeSubscriptionExpirationDateForMemberCommand` | internal | `Id, MemberId, ExpirationDate` | none | `MemberSubscriptions/ChangeSubscriptionExpirationDateForMember/` |

Plumbing: `ProcessInboxCommand`, `ProcessOutboxCommand`, `ProcessInternalCommandsCommand`. FluentValidation validators: `ProposeMeetingGroupCommandValidator`, `AcceptMeetingGroupProposalCommandValidator`, `AddMeetingCommentCommandValidator`, `EditMeetingCommentCommandValidator`.

## Queries

| Query | Fields | Result | Folder (`Application/`) |
|---|---|---|---|
| `GetAllCountriesQuery` | none | `List<CountryDto>` | `Countries/` |
| `GetMeetingGroupProposalQuery` | `MeetingGroupProposalId` | `MeetingGroupProposalDto` | `MeetingGroupProposals/GetMeetingGroupProposal/` |
| `GetMemberMeetingGroupProposalsQuery` | none | `List<MeetingGroupProposalDto>` | `MeetingGroupProposals/GetMemberMeetingGroupProposals/` |
| `GetAllMeetingGroupProposalsQuery` | `Page?, PerPage?` (`IPagedQuery`) | `List<MeetingGroupProposalDto>` | `MeetingGroupProposals/GetAllMeetingGroupProposals/` |
| `GetAllMeetingGroupsQuery` | none | `List<MeetingGroupDto>` | `MeetingGroups/GetAllMeetingGroups/` |
| `GetAuthenticationMemberMeetingGroupsQuery` | none | `List<MemberMeetingGroupDto>` | `MeetingGroups/GetAuthenticationMemberMeetingGroups/` |
| `GetMeetingGroupDetailsQuery` | `MeetingGroupId` | `MeetingGroupDetailsDto` | `MeetingGroups/GetMeetingGroupDetails/` |
| `GetAuthenticatedMemberMeetingsQuery` | none | `List<MemberMeetingDto>` | `Meetings/GetAuthenticatedMemberMeetings/` |
| `GetMeetingDetailsQuery` | `MeetingId` | `MeetingDetailsDto` | `Meetings/GetMeetingDetails/` |
| `GetMeetingAttendeesQuery` | `MeetingId` | `List<MeetingAttendeeDto>` | `Meetings/GetMeetingAttendees/` |
| `GetMeetingCommentsQuery` | `MeetingId` | `List<MeetingCommentDto>` | `MeetingComments/GetMeetingComments/` |
| `GetMeetingCommentLikersQuery` | `MeetingCommentId` | `List<MeetingCommentLikerDto>` | `MeetingComments/GetMeetingCommentLikers/` |
| `GetMeetingCommentingConfigurationQuery` | `MeetingId` | `MeetingCommentingConfigurationDto` | `MeetingCommentingConfigurations/GetMeetingCommentingConfiguration/` |

No REST endpoint for: `GetMeetingGroupProposalQuery`, `GetMeetingCommentsQuery`, `GetMeetingCommentLikersQuery` (checked by searching `API/.../Modules/` for `new <Query>`). `GetMeetingCommentingConfigurationQuery` has no endpoint either and is only referenced from the module integration tests (`Tests/IntegrationTests/MeetingCommentingConfigurations/`). Helpers: `MeetingsQueryHelper`, `MembersQueryHelper`.

## Domain events

All derive from `DomainEventBase`. Source `Modules/Meetings/Domain/`.

| Folder | Event | Payload |
|---|---|---|
| `MeetingGroupProposals/Events/` | `MeetingGroupProposedDomainEvent` | `MeetingGroupProposalId, Name, Description, ProposalUserId, ProposalDate, LocationCity, LocationCountryCode` |
| | `MeetingGroupProposalAcceptedDomainEvent` | `MeetingGroupProposalId` |
| `MeetingGroups/Events/` | `MeetingGroupCreatedDomainEvent` | `MeetingGroupId, CreatorId` |
| | `MeetingGroupGeneralAttributesEditedDomainEvent` | `NewName, NewDescription, NewLocation` |
| | `NewMeetingGroupMemberJoinedDomainEvent` | `MeetingGroupId, MemberId, Role` |
| | `MeetingGroupMemberLeftGroupDomainEvent` | `MeetingGroupId, MemberId` |
| | `MeetingGroupPaymentInfoUpdatedDomainEvent` | `MeetingGroupId, PaymentDateTo` |
| | `MeetingAttendeeChangedDecisionDomainEvent` | `MemberId, MeetingId` |
| | `MeetingNotAttendeeChangedDecisionDomainEvent` | `MemberId, MeetingId` |
| `Meetings/Events/` | `MeetingCreatedDomainEvent` | `MeetingId` |
| | `MeetingMainAttributesChangedDomainEvent` | `MeetingId` |
| | `MeetingEditedDomainEvent` | `Guid MeetingId` |
| | `MeetingCanceledDomainEvent` | `MeetingId, CancelMemberId, CancelDate` |
| | `MeetingAttendeeAddedDomainEvent` | `MeetingId, AttendeeId, RSVPDate, Role, GuestsNumber, FeeValue?, FeeCurrency` |
| | `MeetingAttendeeRemovedDomainEvent` | `MemberId, MeetingId, Reason` |
| | `MeetingAttendeeFeePaidDomainEvent` | `MeetingId, AttendeeId` |
| | `MeetingNotAttendeeAddedDomainEvent` | `MeetingId, MemberId` |
| | `MeetingWaitlistMemberAddedDomainEvent` | `MeetingId, MemberId` |
| | `MemberSignedOffFromMeetingWaitlistDomainEvent` | `MeetingId, MemberId` |
| | `MemberSetAsAttendeeDomainEvent` | `MeetingId, HostId` |
| | `NewMeetingHostSetDomainEvent` | `MeetingId, HostId` |
| `MeetingComments/Events/` | `MeetingCommentAddedDomainEvent` | `MeetingCommentId, MeetingId, Comment` |
| | `MeetingCommentEditedDomainEvent` | `MeetingCommentId, EditedComment` |
| | `MeetingCommentRemovedDomainEvent` | `MeetingCommentId` |
| | `ReplyToMeetingCommentAddedDomainEvent` | `MeetingCommentId, InReplyToCommentId, Reply` |
| `MeetingMemberCommentLikes/Events/` | `MeetingCommentLikedDomainEvent`, `MeetingCommentUnlikedDomainEvent` | `MeetingCommentId, LikerId` |
| `MeetingCommentingConfigurations/Events/` | `MeetingCommentingConfigurationCreatedDomainEvent` | `MeetingId, IsEnabled` |
| | `MeetingCommentingEnabledDomainEvent`, `MeetingCommentingDisabledDomainEvent` | `MeetingId` |
| `Members/Events/` | `MemberCreatedDomainEvent` | `MemberId` |
| `Members/MemberSubscriptions/Events/` | `MemberSubscriptionExpirationDateChangedDomainEvent` | `MemberId, ExpirationDate` |

Domain events with a handler (`Application/`): `MeetingCreatedDomainEvent` (direct `INotificationHandler<MeetingCreatedDomainEvent>` in `MeetingCommentingConfigurations/MeetingCreatedEventHandler.cs`: loads the meeting, calls `CreateCommentingConfiguration()`, persists it) and those wrapped by `DomainNotificationBase<T>` notifications below. Dispatch mechanism (`BuildingBlocks/Infrastructure/DomainEventsDispatching/DomainEventsDispatcher.cs`): every domain event is published in-process through MediatR; additionally, an event that has a registered `IDomainEventNotification<T>` is serialized into the outbox. The other events have no in-module handler.

Notifications and handlers:

| Notification (wraps) | Handler(s) | Effect |
|---|---|---|
| `MeetingGroupProposedNotification` (`MeetingGroupProposedDomainEvent`) | `MeetingGroupProposedNotificationHandler` | publishes `MeetingGroupProposedIntegrationEvent` |
| `MeetingGroupProposalAcceptedNotification` (`MeetingGroupProposalAcceptedDomainEvent`) | `MeetingGroupProposalAcceptedNotificationHandler` | enqueues `CreateNewMeetingGroupCommand` |
| `MeetingGroupCreatedNotification` (`MeetingGroupCreatedDomainEvent`) | `MeetingGroupCreatedSendEmailHandler` | enqueues `SendMeetingGroupCreatedEmailCommand` |
| `MeetingAttendeeAddedNotification` (`MeetingAttendeeAddedDomainEvent`) | `MeetingAttendeeAddedNotificationHandler` (enqueues `SendMeetingAttendeeAddedEmailCommand`) and `MeetingAttendeeAddedPublishEventNotificationHandler` (publishes `MeetingAttendeeAddedIntegrationEvent`) | |
| `MeetingCommentLikedNotification` / `MeetingCommentUnlikedNotification` | `MeetingCommentLikedNotificationHandler` / `MeetingCommentUnlikeNotificationHandler` | SQL `UPDATE meetings.MeetingComments SET LikesCount = (count of meetings.MeetingMemberCommentLikes)` |
| `MemberCreatedNotification` (declared as wrapping `MeetingCreatedDomainEvent`, not `MemberCreatedDomainEvent`) | `MemberCratedNotificationHandler` | empty handler (`return Task.CompletedTask`) |
| `MemberSubscriptionExpirationDateChangedNotification` | `MemberSubscriptionExpirationDateChangedNotificationHandler` | loads the member's groups from `meetings.v_MeetingGroupMembers`, applies `MeetingGroupExpirationDatePolicy.GetMeetingGroupsCoveredByMemberSubscription`, enqueues one `SetMeetingGroupExpirationDateCommand` per covered group |

## Integration events

| Direction | Event | Payload | Source / handler |
|---|---|---|---|
| Published | `MeetingGroupProposedIntegrationEvent` | `Id, OccurredOn, MeetingGroupProposalId, Name, Description, LocationCity, LocationCountryCode, ProposalUserId, ProposalDate` | `Modules/Meetings/IntegrationEvents/`; published by `MeetingGroupProposedNotificationHandler` |
| Published | `MeetingAttendeeAddedIntegrationEvent` | `Id, OccurredOn, MeetingId, AttendeeId, FeeValue?, FeeCurrency` | same folder; published by `MeetingAttendeeAddedPublishEventNotificationHandler` |
| Defined, never published | `MemberCreatedIntegrationEvent` | `Id, OccurredOn, MemberId` | same folder; no `Publish` call for it exists in `src/`; subscribed to by UserAccess with no handler (see [useraccess.md](useraccess.md)) |
| Consumed | `MeetingGroupProposalAcceptedIntegrationEvent` (Administration) | | `MeetingGroupProposals/MeetingGroupProposalAcceptedIntegrationEventHandler.cs` -> `AcceptMeetingGroupProposalCommand` |
| Consumed | `SubscriptionExpirationDateChangedIntegrationEvent` (Payments) | | `MemberSubscriptions/SubscriptionExpirationDateChangedIntegrationEventHandler.cs` -> `ChangeSubscriptionExpirationDateForMemberCommand` |
| Consumed | `NewUserRegisteredIntegrationEvent` (Registrations) | | `Members/CreateMember/NewUserRegisteredIntegrationEventHandler.cs` -> `CreateMemberCommand` |
| Consumed | `MeetingFeePaidIntegrationEvent` (Payments) | | `Meetings/MeetingFeePaidIntegrationEventHandler.cs` -> `MarkMeetingAttendeeFeeAsPayedCommand` |

Subscriptions registered in `Infrastructure/Configuration/EventsBus/EventsBusStartup.cs` (4 events).

## Event flows (choreography; no process manager class exists)

1. Group creation: `ProposeMeetingGroupCommand` -> `MeetingGroupProposedDomainEvent` -> publishes `MeetingGroupProposedIntegrationEvent` -> (Administration verifies/accepts) -> `MeetingGroupProposalAcceptedIntegrationEvent` -> internal `AcceptMeetingGroupProposalCommand` -> `MeetingGroupProposalAcceptedDomainEvent` -> `CreateNewMeetingGroupCommand` -> `MeetingGroup` created -> `MeetingGroupCreatedDomainEvent` -> `SendMeetingGroupCreatedEmailCommand`.
2. Meeting creation: `CreateMeetingCommand` -> `MeetingCreatedDomainEvent` -> `MeetingCreatedEventHandler` creates the commenting configuration (enabled by default per `MeetingCommentingConfigurationCreatedDomainEvent.IsEnabled`).
3. Attendance with fee: `AddMeetingAttendeeCommand` -> `MeetingAttendeeAddedDomainEvent` -> email command + `MeetingAttendeeAddedIntegrationEvent` (Payments creates the fee). Payments later publishes `MeetingFeePaidIntegrationEvent` -> `MarkMeetingAttendeeFeeAsPayedCommand` -> `Meeting.MarkAttendeeFeeAsPayed`.
4. Subscription: Payments `SubscriptionExpirationDateChangedIntegrationEvent` -> `ChangeSubscriptionExpirationDateForMemberCommand` -> `MemberSubscriptionExpirationDateChangedDomainEvent` -> `SetMeetingGroupExpirationDateCommand` per covered group -> `MeetingGroup` payment date (`PaymentDateTo`) updated (rule `MeetingCanBeOrganizedOnlyByPayedGroupRule` uses it).
5. New user: Registrations `NewUserRegisteredIntegrationEvent` -> `CreateMemberCommand` -> `Member`.

## REST endpoints

Controllers in `API/CompanyName.MyMeetings.API/Modules/Meetings/`; permission constants in `MeetingsPermissions.cs`. ASP.NET `[controller]` token resolves to the controller class name minus suffix (route matching is case-insensitive).

| Verb | Route | Permission | Dispatches | Body / params |
|---|---|---|---|---|
| GET | `/api/meetings/countries` | `GetMeetingGroupProposals` | `GetAllCountriesQuery` | `page?`, `perPage?` accepted, not forwarded to the query |
| GET | `/api/meetings/meetingGroupProposals` | `GetMeetingGroupProposals` | `GetMemberMeetingGroupProposalsQuery` | |
| GET | `/api/meetings/meetingGroupProposals/all` | `GetMeetingGroupProposals` | `GetAllMeetingGroupProposalsQuery` | `page?`, `perPage?` |
| POST | `/api/meetings/meetingGroupProposals` | `ProposeMeetingGroup` | `ProposeMeetingGroupCommand` | `Name, Description, LocationCity, LocationCountryCode` |
| GET | `/api/meetings/meetingGroups` | `GetAuthenticatedMemberMeetingGroups` | `GetAuthenticationMemberMeetingGroupsQuery` | |
| GET | `/api/meetings/meetingGroups/{meetingGroupId}` | `GetMeetingGroupDetails` | `GetMeetingGroupDetailsQuery` | |
| GET | `/api/meetings/meetingGroups/all` | `GetAllMeetingGroups` | `GetAllMeetingGroupsQuery` | |
| PUT | `/api/meetings/meetingGroups/{meetingGroupId}` | `EditMeetingGroupGeneralAttributes` | `EditMeetingGroupGeneralAttributesCommand` | `Name, Description, LocationCity, LocationCountry` |
| POST | `/api/meetings/meetingGroups/{meetingGroupId}/members` | `JoinToGroup` | `JoinToGroupCommand` | |
| DELETE | `/api/meetings/meetingGroups/{meetingGroupId}/members` | `LeaveMeetingGroup` | `LeaveMeetingGroupCommand` | |
| GET | `/api/meetings/meetings` | `GetAuthenticatedMemberMeetings` | `GetAuthenticatedMemberMeetingsQuery` | |
| GET | `/api/meetings/meetings/{meetingId}` | `GetMeetingDetails` | `GetMeetingDetailsQuery` | |
| POST | `/api/meetings/meetings` | `CreateNewMeeting` | `CreateMeetingCommand` | `CreateMeetingRequest` (fields as the command) |
| PUT | `/api/meetings/meetings/{meetingId}` | `EditMeeting` | `ChangeMeetingMainAttributesCommand` | `ChangeMeetingMainAttributesRequest` |
| GET | `/api/meetings/meetings/{meetingId}/attendees` | `GetMeetingAttendees` | `GetMeetingAttendeesQuery` | |
| POST | `/api/meetings/meetings/{meetingId}/attendees` | `AddMeetingAttendee` | `AddMeetingAttendeeCommand` | `GuestsNumber` |
| DELETE | `/api/meetings/meetings/{meetingId}/attendees/{attendeeId}` | `RemoveMeetingAttendee` | `RemoveMeetingAttendeeCommand` | `RemovingReason` |
| POST | `/api/meetings/meetings/{meetingId}/notAttendees` | `AddNotAttendee` | `AddMeetingNotAttendeeCommand` | |
| DELETE | `/api/meetings/meetings/{meetingId}/notAttendees` | `ChangeNotAttendeeDecision` | `ChangeNotAttendeeDecisionCommand` | |
| POST | `/api/meetings/meetings/{meetingId}/waitlistMembers` | `SignUpMemberToWaitlist` | `SignUpMemberToWaitlistCommand` | |
| DELETE | `/api/meetings/meetings/{meetingId}/waitlistMembers` | `SignOffMemberFromWaitlist` | `SignOffMemberFromWaitlistCommand` | |
| POST | `/api/meetings/meetings/{meetingId}/hosts` | `SetMeetingHostRole` | `SetMeetingHostRoleCommand` | `SetMeetingHostRequest` (`AttendeeId`) |
| POST | `/api/meetings/meetings/{meetingId}/attendees/attendeeRole` | `SetMeetingAttendeeRole` | `SetMeetingAttendeeRoleCommand` | `SetMeetingHostRequest` (`AttendeeId`) |
| PATCH | `/api/meetings/meetings/{meetingId}/cancel` | `CancelMeeting` | `CancelMeetingCommand` | |
| PATCH | `/api/meetings/meetings/{meetingId}/configuration/commenting/disable` | `DisableMeetingCommenting` | `DisableMeetingCommentingConfigurationCommand` | |
| PATCH | `/api/meetings/meetings/{meetingId}/configuration/commenting/enable` | `EnableMeetingCommenting` | `EnableMeetingCommentingConfigurationCommand` | |
| POST | `/api/meetings/meetingComments` | `AddMeetingComment` | `AddMeetingCommentCommand` | `MeetingId, Comment` |
| PUT | `/api/meetings/meetingComments/{meetingCommentId}` | `EditMeetingComment` | `EditMeetingCommentCommand` | `EditedComment` |
| DELETE | `/api/meetings/meetingComments/{meetingCommentId}?reason=` | `RemoveMeetingComment` | `RemoveMeetingCommentCommand` | query `reason` |
| POST | `/api/meetings/meetingComments/{meetingCommentId}/replies` | `AddMeetingCommentReply` | `AddReplyToMeetingCommentCommand` | raw string body |
| POST | `/api/meetings/meetingComments/{meetingCommentId}/likes` | `LikeMeetingComment` | `AddMeetingCommentLikeCommand` | |
| DELETE | `/api/meetings/meetingComments/{meetingCommentId}/likes` | `UnlikeMeetingComment` | `RemoveMeetingCommentLikeCommand` | |

Also present in the API project: `CreateNewMeetingGroupRequest.cs` (request DTO not used by any controller action). Role grants: see [useraccess.md](useraccess.md); `EnableMeetingCommenting` / `DisableMeetingCommenting` are in `users.Permissions` but not granted to `Member` or `Administrator` by `SeedDatabase.sql`.

## Database (schema `meetings`)

Defined in `Database/CompanyName.MyMeetings.Database/Structure/meetings/`. Migrations: `Scripts/Migrations/1_0_0_0/0001..0014_*.sql` (`0001_initial_structure.sql` plus later tables/views: countries, commenting configurations, comment likes, likes count, views, mock emails table, registrations tables).

Tables:

| Table | Columns (type) | Aggregate mapping |
|---|---|---|
| `meetings.Meetings` | `Id` PK, `MeetingGroupId`, `CreatorId`, `CreateDate` datetime2, `Title` nvarchar(200), `Description` nvarchar(4000), `TermStartDate`, `TermEndDate`, `LocationName` nvarchar(200), `LocationAddress` nvarchar(200), `LocationPostalCode` nvarchar(200) null, `LocationCity` nvarchar(50), `AttendeesLimit` int null, `GuestsLimit` int, `RSVPTermStartDate` null, `RSVPTermEndDate` null, `EventFeeValue` decimal(5,0) null, `EventFeeCurrency` varchar(3) null, `ChangeDate` null, `ChangeMemberId` null, `CancelDate` null, `CancelMemberId` null, `IsCanceled` bit | `Meeting` (owned VOs `_term`, `_rsvpTerm`, `_eventFee`, `_location`, `_meetingLimits`) |
| `meetings.MeetingAttendees` | `MeetingId, AttendeeId, DecisionDate` (composite PK), `RoleCode` varchar(50) null, `GuestsNumber` int null, `DecisionChanged` bit, `DecisionChangeDate` null, `IsRemoved` bit, `RemovingMemberId` null, `RemovingReason` nvarchar(500) null, `RemovedDate` null, `BecameNotAttendeeDate` null, `FeeValue` decimal(5,0) null, `FeeCurrency` varchar(3) null, `IsFeePaid` bit | `MeetingAttendee` (owned collection) |
| `meetings.MeetingNotAttendees` | `MeetingId, MemberId, DecisionDate` (PK), `DecisionChanged` bit, `DecisionChangeDate` null | `MeetingNotAttendee` |
| `meetings.MeetingWaitlistMembers` | `MeetingId, MemberId, SignUpDate` (PK), `IsSignedOff` bit, `SignOffDate` null, `IsMovedToAttendees` bit, `MovedToAttendeesDate` null | `MeetingWaitlistMember` |
| `meetings.MeetingGroups` | `Id` PK, `Name` nvarchar(255), `Description` varchar(200) null, `LocationCity` nvarchar(50), `LocationCountryCode` nvarchar(3), `CreatorId`, `CreateDate` datetime, `PaymentDateTo` date null | `MeetingGroup` |
| `meetings.MeetingGroupMembers` | `MeetingGroupId, MemberId, JoinedDate` (PK), `RoleCode` varchar(50), `IsActive` bit, `LeaveDate` null | `MeetingGroupMember` |
| `meetings.MeetingGroupProposals` | `Id` PK, `Name`, `Description` varchar(200) null, `LocationCity`, `LocationCountryCode`, `ProposalUserId`, `ProposalDate`, `StatusCode` nvarchar(50) | `MeetingGroupProposal` |
| `meetings.MeetingComments` | `Id` PK, `MeetingId`, `AuthorId`, `InReplyToCommentId` null, `Comment` varchar(300) null, `IsRemoved` bit, `RemovedByReason` varchar(300) null, `CreateDate` datetime, `EditDate` null, `LikesCount` int | `MeetingComment` |
| `meetings.MeetingMemberCommentLikes` | `Id` PK, `MemberId` FK `meetings.Members`, `MeetingCommentId` FK `meetings.MeetingComments` | `MeetingMemberCommentLike` |
| `meetings.MeetingCommentingConfigurations` | `Id` PK, `MeetingId` FK `meetings.Meetings`, `IsCommentingEnabled` bit | `MeetingCommentingConfiguration` |
| `meetings.Members` | `Id` PK, `Login`, `Email`, `FirstName`, `LastName`, `Name` | `Member` |
| `meetings.MemberSubscriptions` | `Id` PK, `ExpirationDate` datetime | `MemberSubscription` |
| `meetings.Countries` | `Code` char(2) PK, `Name` nvarchar(50) | reference data, seeded by `Scripts/Seeds/0001_SeedCountries.sql` (244 lines) |
| `meetings.InboxMessages`, `OutboxMessages`, `InternalCommands` | same shape as in every module (see [administration.md](administration.md)) | messaging |

Views (read models used by Dapper queries): `v_Countries`, `v_MeetingAttendees`, `v_MeetingComments`, `v_MeetingDetails`, `v_MeetingGroupMembers`, `v_MeetingGroupProposals`, `v_MeetingGroups`, `v_Meetings`, `v_MemberMeetingGroups`, `v_MemberMeetings`, `v_Members`.

EF mappings: `Infrastructure/Domain/*/…EntityTypeConfiguration.cs`.

## Quartz jobs

`Infrastructure/Configuration/Quartz/QuartzStartup.cs`. The three generic jobs only: `ProcessOutboxJob`, `ProcessInboxJob`, `ProcessInternalCommandsJob`, each with cron `0/2 * * ? * *` or a fixed millisecond interval when supplied. No business job (the group expiration is event driven, see flow 4).

## Process managers

None found by name. Multi-step processes are the choreographed flows above.

## Existing tests

Folder `Modules/Meetings/Tests/`.

Unit tests (`UnitTests/`):
- `MeetingGroupProposals/MeetingGroupProposalTests.cs`: `ProposeNewMeetingGroup_IsSuccessful`, `AcceptProposal_WhenIsNotAccepted_IsSuccessful`, `AcceptProposal_WhenIsAlreadyAccepted_BreaksProposalCannotBeAcceptedMoreThanOnceRule`, `CreateMeetingGroup_IsSuccessful_And_CreatorIsAHost`
- `MeetingGroups/MeetingGroupTests.cs`: `EditGeneralAttributes_IsSuccessful`, `JoinToGroup_WhenMemberHasNotJoinedYet_IsSuccessful`, `JoinToGroup_WhenMemberHasAlreadyJoined_BreaksMeetingGroupMemberCannotBeAddedTwiceRule`, `LeaveGroup_WhenMemberIsActiveMemberOfGroup_IsSuccessful`, `LeaveGroup_WhenMemberIsNotActiveMemberOfGroup_BreaksNotActualGroupMemberCannotLeaveGroupRule`, `UpdatePaymentDateTo_IsSuccessful`, `CreateMeeting_WhenGroupIsNotPayed_IsNotPossible`, `CreateMeeting_WhenCreatorIsMemberOfGroupAndHostsAreNotDefined_IsSuccessful`, `CreateMeeting_WhenHostsAreDefinedAndTheyAreGroupMembers_DefinedHostsAreHostsOfMeeting`, `CreateMeeting_WhenHostsAreDefinedAndTheyAreNotGroupMembers_BreaksMeetingHostMustBeAMeetingGroupMemberRule`, `CreateMeeting_WhenCreatorIsNotMemberOfGroup_BreaksMeetingHostMustBeAMeetingGroupMemberRule`
- `Meetings/MeetingAddAttendeeTests.cs` (8): meeting started / RSVP term ended / not group member / already attendee / guests above limit / attendee limit reached (each "IsNotPossible"), all-conditions success, not-attendee-changed-decision success
- `Meetings/MeetingAddNotAttendeeTests.cs` (7): `AddNotAttendee_*` (meeting started, already not-attendee, success, attendee changed decision) and `ChangeNotAttendeeDecision_*` (meeting started, not active not-attendee, success)
- `Meetings/MeetingCommentTests.cs` (19, 3 with `[TestCase]`): add / edit / remove comment, add reply, add / remove like, each with the rule-breaking variants (non-member author, empty text, commenting disabled, non-author edit, non-author-non-organizer removal, reason by non-organizer, like by non-member, double like)
- `Meetings/MeetingCommentingConfigurationTests.cs` (7): create, disable (success, not organizer, already disabled ignored), enable (same three)
- `Meetings/MeetingLimitsTests.cs` (6): attendees/guests limit combinations and negative values
- `Meetings/MeetingRolesTests.cs` (9): `SetHostRole_*` (4) and `SetAttendeeRole_*` (5, including `SetAttendeeRole_ForLastOrganizer_BreaksMeetingMustHaveAtLeastOneHostRule`)
- `Meetings/MeetingTests.cs` (6): `CancelMeeting_*` (2), `RemoveAttendee_*` (4)
- `Meetings/MeetingWaitlistTests.cs` (8): `SignUpMemberToWaitList_*` (5), `SignOffMemberFromWaitList_*` (3)
- `Members/MemberTests.cs`: `CreateMember_IsSuccessful`

Integration tests (`IntegrationTests/`, real SQL Server, probes poll for async results):
- `Countries/GetCountriesTests.cs`: `GetCountriesTest`
- `MeetingCommentingConfigurations/`: `CreateMeetingCommentingCofiguration_WhenDataIsValid_IsSuccessful`; Enable and Disable each `..._WhenDataIsValid_IsSuccess` and `..._WhenConfigurationNotExist_ThrowsInvalidCommandException`
- `MeetingCommentLikes/`: Add (`WhenDataIsValid_IsSuccess`, `WhenCommentNotExists_ThrowsInvalidCommandException`), Remove (`UnlikeMeetingComment_*`, same two)
- `MeetingComments/`: `AddMeetingComment_*` (2), `AddReply_*` (2), `EditMeetingComment_*` (2), `RemoveMeetingComment_ByAuthor_WhenDataIsValid_IsSuccessful`, `RemoveMeetingComment_WhenItIsNonexistent_ThrowsInvalidCommandException`, `GetMeetingComments_Test`
- `MeetingGroupProposals/`: `GetMeetingGroupProposals_Test`, `ProposeAndAcceptMeetingGroup_WhenDataIsValid_IsSuccessful`, `ProposeMeetingGroup_WhenNoLocationProvided_ThrowsInvalidCommandException`
- `MeetingGroups/CreateNewMeetingGroupTests.cs`: `CreateNewMeetingGroup_Test`
- `Meetings/MeetingCreateTests.cs`: `CreateMeeting_Test`

Architecture tests: same 10 + 9 + 3 rules as the other modules (the internal-command rule is named `InternalCommand_Should_Have_Constructor_With_JsonConstructorAttribute` here); see [administration.md](administration.md).

Not covered by a module integration test: join/leave group, attendee/not-attendee/waitlist commands, host/attendee role commands, cancel meeting, edit meeting, fee-paid flow, member subscription expiration flow (these are covered only by unit tests on the aggregates, if at all).
