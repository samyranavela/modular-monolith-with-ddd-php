# Accounts

Accounts knows who the people of the application are and what each may do. It holds the **account** of every person who can sign in, from the moment they ask to open it until the day it is deleted, with the **role** it has. Every other context reads from it the **access** of an account, to decide what the person may do.

Terms are defined in the [Accounts glossary](../../../src/Accounts/GLOSSARY.md).

## Accounts

An **Account** is what the application holds for a person who can sign in: a login, a password, an email address, a first and a last name, a role, and the requests that are in progress, such as a change of email address, a reset of the password or a deletion. The person who holds it is the **user**.

An account keeps the **same identity for its whole life**, whatever happens to its email address. This identity is how the other contexts refer to the person, and it names nobody once the account is deleted.

An account is always in one **state**, and the state says what can be done with it:

- **Pending**: the account was just created by a registration or an invitation, and the email address is not proven yet. It can do nothing, not even sign in.
- **Confirmed**: the link was followed. The account can do what its role grants.
- **Locked**: its holder asked to leave, and the grace period is running. It can read, and change almost nothing, see [locked account](account-deletion/description.md#locked-account).

An account ends when it is deleted: the deletion is final, and nothing of the account remains here, see [deleting an account](account-deletion/description.md). Two accounts never share a login or an email address, whatever their state.

The same person is known in other contexts under other names: a member in Meetings, a payer in Payments. An administrator has an account and nothing else. Accounts is the context that says who the person is; the others decide what that person can be.

[Notifications](../notifications/description.md) needs the email address of an account to write to it, and to know when an account is deleted, see [deleting an account](account-deletion/description.md).

## Creating an account

A person gets an account in one of two ways, and both create a pending account that knows only an email address, and waits until a link proves that the email address is theirs:

- A person **registers** themselves and receives the Member role.
- An administrator, or whoever operates the application, **invites** someone, who receives the Administrator role.

The person then follows the link and gives their login, names and password: confirming the account is what makes it real. Until then it can do nothing, and no other context knows it: they create what they need for the person when the account is confirmed. An account that is not confirmed in time is removed. See [registration](registration/description.md).

## Roles

An account has **exactly one role**. A role says what kind of account it is, and each context decides what a role may do. The application has two:

- **Member**: the ordinary role. Someone who registers themselves always receives it.
- **Administrator**: the role of whoever handles what ordinary users cannot, such as deciding on meeting group proposals.

An administrator is never the result of someone registering themselves: they come from an [invitation](registration/description.md#inviting-an-administrator). An administrator is not a member of the meeting groups and not a payer. There is no way to turn a member into an administrator.

## Access

Accounts is the **referent of who a person is and what they may be**. The **access** of an account is made of three things:

- its **role**, Member or Administrator;
- its **state**, confirmed or [locked](account-deletion/description.md#locked-account);
- its **subscription**: until when it is valid, and whether the account ever held one.

At every action, the context that guards it **reads the access of the account from Accounts, as it stands at that moment**, and keeps no copy. An identity or a right that is out of date is a risk, whereas a short delay is not: a role that is withdrawn, a lock or a deletion must count at once.

Each context decides, for each of its own actions, **which role may do it**, and whether it **changes** something. Accounts knows none of these actions, and a new action of another context changes nothing here. A [locked account](account-deletion/description.md#locked-account) is refused by each context every action that changes something, apart from four exceptions: cancelling the deletion request and exporting the data, which belong to Accounts, changing the password, see [profile management](profile-management/description.md), and marking the notifications as read, which belongs to Notifications.

### The subscription

The subscription is not a thing of Accounts: it belongs to [Payments](../payments/subscriptions/description.md). Accounts keeps only what the access needs, **until when** the subscription is valid and **whether the account ever held one**, from the fact that Payments gives each time a subscription is started, bought or renewed. It is a value, replaced by the next one. This is enough for Meetings to know whether a group is covered by its owner's subscription, and whether the owner is on the free plan.

Whatever is late can only remove a right, never grant one more:

- **A renewal not yet known**: the date read is the earlier one, so the owner is covered for a shorter time until the fact arrives.
- **A subscription cancelled before its end**: nothing changes, since the rights run until the date, and that date has not moved.
- **A locked account**: only the state counts, the date no longer matters.

## Profile

A user changes the email address and the password of their account, and gets back in after forgetting the password, see [profile management](profile-management/description.md).

## Leaving

A user can ask to leave the application: a member requests the deletion of their account, and an administrator is deleted by another one. The data is erased or made anonymous in every context, see [deleting an account](account-deletion/description.md).

## What Accounts needs from other contexts

| Information | From | Why | Exactness and freshness |
| :-- | :-- | :-- | :-- |
| That a subscription was started, bought or renewed, and until when it is valid | [Payments](../payments/subscriptions/description.md) | To give the access of the account its subscription | Without noticeable delay and never lost. When it is late, only a right is missing for a while, see [the subscription](#the-subscription) |
| The open items of a member: the groups they own that are under hand-over, the meetings where they are the last host, the proposals pending. And the groups they own, which would be archived | [Meetings](../meetings/description.md) | To allow a deletion request, and to carry it out | Exact at the request, and again when the deletion is carried out |
| Whether a payment waits for the member, as an attendee or as the owner of a group | [Payments](../payments/description.md) | To allow a deletion request, and to carry it out | Exact at the request, and again when the deletion is carried out |

## What other contexts need from Accounts

| Who needs it | Information | Why | Exactness and freshness |
| :-- | :-- | :-- | :-- |
| Meetings, Payments | That an account was confirmed with the Member role | To have a member, to have a payer | Each confirmation gives one member and one payer, never lost and never twice |
| Meetings | That an account becomes locked, and that it is deleted | To end a hand-over whose candidate is locked, and to deal with an erased member | Prompt, and never lost |
| Payments | That an account is deleted | To end the subscription, deactivate the payout accounts and make the payer anonymous | Never lost |
| Notifications | The email address of an account, and that an account is deleted | To write to the person, and to send nothing more to a deleted member | Exact when a message is sent. The deletion is never lost |
| Each context | The access of an account: its role, its state and its subscription | To allow or refuse each action: the role may do it, a locked account changes nothing, and for Meetings, a group is covered by the subscription of its owner and its sign-ups to meetings with a fee are closed while its owner is locked | Exact at each action, read from Accounts at that moment and never kept by the other context |

See the [conceptual model](conceptual-model.md). The creation and the confirmation of an account, with the member and the payer that follow, are shown in the [event storming of the registration](registration/event-storming.md).
