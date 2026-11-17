# Accounts

Accounts knows who the people of the application are and what each may do. It holds the **account** of every person who can sign in, from the moment they ask to open it until the day it is deleted, with the **role** it has and the **permissions** that role grants.

Terms are defined in the [Accounts glossary](../../../src/Accounts/GLOSSARY.md).

## Accounts

An **Account** is what the application holds for a person who can sign in: a login, a password, an email address, a first and a last name, a role, and the requests that are in progress, such as a change of email address, a reset of the password or a deletion. The person who holds it is the **user**.

An account keeps the **same identity for its whole life**, whatever happens to its email address. This identity is how the other contexts refer to the person, and it names nobody once the account is deleted.

An account is always in one **state**, and the state says what can be done with it:

- **Pending**: the account was just created by a registration or an invitation, and the email address is not proven yet. It can do nothing, not even sign in.
- **Confirmed**: the link was followed. The account can do what its role grants.
- **Locked**: its holder asked to leave, and the grace period is running. It can read, and change almost nothing, see [locked account](account-deletion.md#locked-account).

An account ends when it is deleted: the deletion is final, and nothing of the account remains here, see [deleting an account](account-deletion.md). Two accounts never share a login or an email address, whatever their state.

The same person is known in other contexts under other names: a member in Meetings, a payer in Payments. An administrator has an account and nothing else. Accounts is the context that says who the person is; the others decide what that person can be.

[Notifications](../notifications/description.md) needs the email address of an account to write to it, and to know when an account is deleted, see [deleting an account](account-deletion.md).

## Creating an account

A person gets an account in one of two ways, and both create a pending account that knows only an email address, and waits until a link proves that the email address is theirs:

- A person **registers** themselves and receives the Member role.
- An administrator, or whoever operates the application, **invites** someone, who receives the Administrator role.

The person then follows the link and gives their login, names and password: confirming the account is what makes it real. Until then it can do nothing, and no other context knows it: they create what they need for the person when the account is confirmed. An account that is not confirmed in time is removed. See [registration](registration.md).

## Roles

An account has **exactly one role**. A role is a named set of permissions. The application has two:

- **Member**: the ordinary role. Someone who registers themselves always receives it.
- **Administrator**: the role of whoever handles what ordinary users cannot, such as deciding on meeting group proposals.

An administrator is never the result of someone registering themselves: they come from an [invitation](registration.md#inviting-an-administrator). An administrator is not a member of the meeting groups and not a payer. There is no way to turn a member into an administrator.

## Permissions

A **Permission** authorizes one action, such as accepting a meeting group proposal. A role holds one or more permissions. The permissions belong to the contexts whose actions they guard, so Meetings defines what an administrator may do with meeting group proposals, and Accounts only stores the link between roles and permissions.

The rule is the same for every action: it is allowed when the role of the account grants its permission, and refused otherwise.

The context that defines a permission also says whether its action **reads** or **changes** something. That is what a [locked account](account-deletion.md#locked-account) rests on: Accounts refuses a locked account every permission that changes something, with four exceptions, cancelling the deletion request, exporting the data, marking the notifications as read and changing the password, see [profile management](profile-management.md). The lock is thus applied in one place, and no context has to know it to refuse an action.

## Profile

A user changes the email address and the password of their account, and gets back in after forgetting the password, see [profile management](profile-management.md).

## Leaving

A user can ask to leave the application: a member requests the deletion of their account, and an administrator is deleted by another one. The data is erased or made anonymous in every context, see [deleting an account](account-deletion.md).

## What Accounts needs from other contexts

| Information | From | Why | Exactness and freshness |
| :-- | :-- | :-- | :-- |
| The open items of a member: the groups they own that are under hand-over, the meetings where they are the last host, the proposals pending. And the groups they own, which would be archived | [Meetings](../meetings/description.md) | To allow a deletion request, and to carry it out | Exact at the request, and again when the deletion is carried out |
| Whether a payment waits for the member, as an attendee or as the owner of a group | [Payments](../payments/description.md) | To allow a deletion request, and to carry it out | Exact at the request, and again when the deletion is carried out |

## What other contexts need from Accounts

| Who needs it | Information | Why | Exactness and freshness |
| :-- | :-- | :-- | :-- |
| Meetings, Payments | That an account was confirmed with the Member role | To have a member, to have a payer | Each confirmation gives one member and one payer, never lost and never twice |
| Meetings | That an account is locked, and that it is deleted | To refuse a locked member as the candidate of a hand-over, to close the sign-ups to meetings with a fee of a group whose owner is locked, and to deal with an erased member | Exact at those moments. Prompt otherwise, and never lost for the erasure |
| Payments | That an account is deleted | To end the subscription, remove the payout account and make the payer anonymous | Never lost |
| Notifications | The email address of an account, and that an account is deleted | To write to the person, and to send nothing more to a deleted member | Exact when a message is sent. The deletion is never lost |
| Each context | Whether the role of an account grants a permission | To allow or refuse each action | Exact at each action |

See the [conceptual model](conceptual-model.md). The creation and the confirmation of an account, with the member and the payer that follow, are shown in the [event storming of the registration](registration-event-storming.md).
