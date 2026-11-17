# Deleting an account

A person can ask to leave the application for good. What that means touches every context: [Accounts](description.md) holds who they are, [Meetings](../meetings/description.md) what they did with others, [Payments](../payments/description.md) what they paid. This page describes the whole, from the request to the day nothing identifies the person any more.

Terms are defined in the glossaries of [Accounts](../../../src/Accounts/GLOSSARY.md), [Meetings](../../../src/Meetings/GLOSSARY.md) and [Payments](../../../src/Payments/GLOSSARY.md).

## The principle

The application keeps **no personal data** of someone who left, and keeps **what others rely on**.

- What identifies the person is **erased** and the account is **deleted for good**: login, email address, names, password.
- What the person did among others is **kept and made anonymous**: their comments, their likes, the groups and meetings they looked after, the payments they made. It is shown as coming from a **deleted member**.
- What others cannot do without is **settled first**: a group under hand-over, a meeting that would be left without host, a payment that is still waiting. The member decides, before anything is lost.

Only the person asks for the deletion of their own account, and only for their own. The one exception, an administrator, has [its own section](#deleting-an-administrator).

## The steps

1. **Seeing what stands in the way.** The member asks what deleting their account would change. The answer lists the [open items](#what-stands-in-the-way), each with what to do about it. Nothing changes yet, and it can be asked again at any time.
2. **Requesting.** Once nothing stands in the way, the member requests the deletion and confirms that they have read what will happen. The request is **refused** when something stands in the way at that moment. Otherwise the account is **locked**.
3. **Waiting.** The account stays locked for a **grace period**, 14 days by default and configurable. It keeps the day on which the deletion will be carried out.
4. **Ending, one of two ways.** The member **cancels** the request during the grace period, and the account goes back to confirmed, as it was. Or the grace period ends and the deletion is **carried out**: the account is deleted for good.

An account is locked or it is not: there is one request at a time. A cancelled request is not reopened: the member makes a new one.

## What stands in the way

Four things must be settled before a request is accepted. They are the **open items**.

| Open item | Where it comes from | How the member settles it |
| :-- | :-- | :-- |
| A group the member owns that is **under hand-over** | [Meetings](../meetings/group-life.md#roles-in-a-group) | Wait until the hand-over ends: it completes and the group is no longer theirs, or it ends otherwise and the group is back to being theirs, and not under hand-over. |
| An upcoming meeting where the member is the **last host** of a group that goes on | [Meetings](../meetings/organization.md#hosts) | Designate another host, or cancel the meeting. |
| A **payment that waits**, for the member's own places or for a meeting of a group they own | [Payments](../payments/meeting-fees.md) | Wait: it ends by itself, in 30 minutes by default. It cannot come back once the account is locked. |
| A **meeting group proposal** that is pending | [Meetings](../meetings/group-creation.md) | Wait for an administrator to decide. |

Some remarks:

- **Owning a group is not a blocker, a group under hand-over is.** The member who does not hand the group over accepts that it is archived, and says so by confirming. Handing it over, before the request, is the only way to keep the group alive, so the view of what deleting the account would change lists the owned groups first. A hand-over that is pending counts as open until it ends.
- **The last host of a group the member owns** is not listed: if the group is archived, its upcoming meetings are cancelled anyway, and if it is handed over, the new owner's group goes on with its hosts.
- **A subscription is not an open item.** It is not a thing to settle, it simply ends with the account, see [what happens in Payments](#what-happens-in-payments).
- **A hand-over proposed to the member**, with the member as candidate, is not an open item either: it ends as **cancelled by itself** when the account is locked, and the owner is notified, as for any [transfer cancelled by itself](../notifications/catalogue.md#meetings-groups). If the deletion request and the hand-over request arrive at the same moment, the first to arrive wins: when the hand-over request is first, the deletion request is accepted and cancels it; when the deletion request is first, the hand-over request is refused, since a locked member cannot be named.

Nothing is refused to an administrator for these reasons: an administrator is neither a member nor a payer, and has none of them.

## Locked account

From the request until it ends, the account is **locked**. The member can still sign in and **reads everything a member with a confirmed account reads**: their groups, their meetings, the comments, their subscription, their payments and their inbox. They **change nothing**, with four exceptions: cancelling the request, [exporting their data](#exporting-the-data), marking their notifications as read, and [changing their password](profile-management.md#changing-the-password).

Each context applies the lock to its own actions: it reads the [access](description.md#access) of the account from Accounts at every action, and refuses a locked account every action that changes something, apart from these four, which belong to Accounts and Notifications.

A locked member takes part in nothing: no sign-up to a meeting, no comment, no like, no payment, no group proposal, no hand-over. Hence the open items cannot come back through the member's own doing, and a hand-over cannot name them as candidate.

What was set up before the request goes on by itself: an automatic renewal that falls during the grace period is charged as usual.

While the owner of a group is locked, **sign-ups to its meetings that have a fee are closed**, as during a [hand-over](../meetings/group-life.md#handing-the-group-over), so that no payment is created for an owner who may be gone. They reopen if the request is cancelled. Meetings reads the state of the owner at each sign-up, so the sign-ups are closed **from the moment the lock is committed**. A sign-up that is read just before can still go through, even if the request is committed right after: the payment then waits 30 minutes at most and ends by itself, paid or expired, long before the end of the grace period. The [final check](#carrying-out-the-deletion) finds no payment that waits, and refuses the deletion otherwise. Hence no payment can wait when the deletion is carried out.

What others do can still change the picture. A host who was not the last may find themselves the last when the other host leaves. That is checked when the deletion is carried out, see below.

## Carrying out the deletion

When the grace period is over, the application checks the open items again. The check and the carrying out are **one decision**: nothing can stand in the way between the two, whatever other members do at the same moment, and only one of the two outcomes happens. The deletion is **carried out only if nothing stands in the way**, as at the request. If something does at that moment, the deletion is cancelled with the reason, the account goes back to confirmed, and the member is notified: they settle it and make a new request if they still want to.

Otherwise the account is **deleted for good** at once, and each of the other contexts does its part on its own. The member is not asked anything, and cannot cancel any more.

### What happens in Accounts

The account is deleted for good: its **login, email address, names and password are erased**, nothing of it remains, and nobody can sign in with it any more. The login and the email address are **free again**, like those of a pending account that was removed at its deadline: somebody else can use them at once.

The other contexts keep the identifier of the account, which names nobody: it is how they know that a comment or a payment came from someone who is gone. The role of the account ends with it.

### What happens in Meetings

The member **leaves every group they belong to except the groups they own**, as if they left it themselves, see [leaving](../meetings/group-life.md#leaving) and [when a member leaves the group](../meetings/organization.md#when-a-member-leaves-the-group): they are taken out of the upcoming meetings, an attendee loses their place and their guests', the places go to the waitlist, a waiting member leaves it, and a host stops being one. An owner cannot leave: the groups they own are archived, below. A hand-over that names them as candidate has already ended as cancelled by itself.

- **A group the member owns is archived**, unless it was handed over before. The owner stays the owner of an archived group. Its upcoming meetings are **cancelled**, with the usual notification to attendees, the waitlist and hosts. The group is then **frozen**: nobody can change it, join it, plan meetings in it, comment or moderate, and nothing is added to it. Its members still see it and can still leave it, since it keeps its past and its comments. Its owner is shown as a **deleted member**.
- **What the member wrote stays, anonymously.** Their comments stay in the conversation with their text and their date, and the author is shown as a **deleted member**: the replies stay readable. Their likes stay counted, and a like of a deleted member shows no one. Nobody can edit the comments of a deleted member, and only the owner and the organizers can remove them, as for any [former member](../meetings/commenting.md#when-a-member-leaves-the-group).
- **What is decided stays.** A group proposal the member made and that was decided, a meeting they hosted, a group they once belonged to: each keeps the deleted member in place of the person.
- **Their place in past meetings** counts in the numbers of attendees and is no longer attached to anyone.

The members of an archived group are notified.

### What happens in Payments

- The payer is **anonymised**: Payments knew nothing of the person but their identifier, so nothing more has to be erased there.
- Their **subscription ends** with the account: an automatic renewal is stopped and no new period is bought. There is **no refund**, not even of a renewal charged during the grace period: the application never refunds, see [the end of a subscription](../payments/subscriptions.md#when-a-subscription-ends).
- Their **payout account is removed**. This removal is **not refused** by the rule that refuses it while a meeting with a fee has yet to start, see [the payout account](../payments/meeting-fees.md): the meetings of the groups concerned are cancelled or the groups handed over. The fees of the meetings of a group handed over go to the new owner's account, and the meetings of an archived group are cancelled, so no fee waits for the removed account.
- Their **payments are kept**, the subscriptions as well as the fees of the meetings, **without an identity**. They are the accounting record, kept for **10 years**, the period the law requires for accounting records, which is configurable, and then deleted. A fee already paid stays paid: the owner of the group decides on a refund in their own payment system.

### What happens in Notifications

The member's **inbox is deleted**, with its deliveries, and no notification is created for them afterwards. The message that confirms the deletion is a [required message](../notifications/description.md#required-messages) sent to the email address, since there is no member any more to notify.

## Exporting the data

A member can **export their data** at any time, with or without a request made, whether the account is locked or not. The export gathers, in one readable document, **what each context holds about the person**:

- **Accounts**: the login, the email address, the names, the role.
- **Meetings**: the groups the member belongs to or belonged to, with their role and the dates; the meetings they hosted or attended, with their guests; their comments and likes; the group proposals they made, and the decision on each.
- **Payments**: their subscriptions and the dates of their periods, their payments with their amounts and dates, and whether they have a payout account.
- **Notifications**: their notifications, with their date and whether they are read.

An administrator has no export: they are neither a member nor a payer, and hold nothing but what Accounts knows of them.

The export is prepared, not instantaneous, and is available to the member for **7 days**, configurable, then it expires. The member is notified when it is ready. A member exports as many times as they want, and each export is the state of the data at that moment.

## Deleting an administrator

An **administrator is a particular account**: nobody deletes their own. An administrator is deleted **by another administrator** only.

- There is no open item, no grace period and no lock: the administrator is neither a member nor a payer, and the other administrator decides, not the person. The deletion is **carried out at once**.
- The account is deleted for good: its login, email address, names and password are erased, and the login and the email address are free again. What the administrator decided stays, attributed to a **deleted administrator**: the group proposals they accepted or rejected keep who, when and why, without the person.
- There is always **at least one administrator**. The one who deletes is an administrator at that moment, so the last one is never deleted, since nobody can delete themselves. The rule matters when two administrators delete **each other at the same moment**: only one deletion succeeds, the other is refused.

## Notifications

Each of these is a notification handed to [Notifications](../notifications/description.md): the member finds it in their inbox and receives it by email, as [the catalogue](../notifications/catalogue.md#accounts) lists. The last one, the deletion carried out, is a [required message](../notifications/description.md#required-messages): an email only, to the email address that the deletion frees, since there is no member any more.

| When | Who is told |
| :-- | :-- |
| The deletion is requested | The member, with the date it will be carried out and what will happen, among them the groups to be archived |
| The request is cancelled by the member | The member |
| The deletion is cancelled because something stands in the way when the grace period is over | The member, with the open items |
| The deletion is carried out | The member, at the email address that is erased by it |
| A group is archived | Its members |
| The meetings of an archived group are cancelled | Their attendees, waitlist and hosts, as for any [cancelled meeting](../meetings/organization.md#cancelling-a-meeting) |
| An export is ready | The member |

## Reading

- **What deleting my account would change.** The open items of the member and the groups that would be archived. Only the member reads it.
- **My deletion request.** Whether the account is locked, since when, and when the deletion will be carried out. Only the member reads it.
- **My exports.** The exports that are ready and until when. Only the member reads them.

A locked member reads these three, like everything else a member with a confirmed account reads. An administrator who is deleted cannot read anything afterwards.

## What stands outside this page

- **A person deleting themselves for someone else**, or an administrator deleting a member's account on their behalf, is not in scope.
- **A reversal after the deletion is carried out** does not exist: the deletion is final, and the member who comes back registers again as a new person.
- **Technical copies**, such as backups and logs, are not described here: they follow the same rule, erased within the retention of the infrastructure, when the implementation is written.

## What each context needs from the others

| Who needs it | Information | From | Exactness and freshness |
| :-- | :-- | :-- | :-- |
| Accounts | The open items of a member: the groups they own that are under hand-over, the meetings where they are the last host, the proposals pending. And the groups they own, which would be archived | [Meetings](../meetings/description.md) | Exact at the request, and again when the deletion is carried out |
| Accounts | Whether a payment waits for the member, as an attendee or as the owner of a group | [Payments](../payments/description.md) | Exact at the request, and again when the deletion is carried out |
| Meetings | The state of a member's account: whether it is locked | Accounts | Exact at every action, read at that moment: a hand-over to a locked member must be refused, and a sign-up to a meeting with a fee is closed while the owner of the group is locked, see [access](description.md#access) |
| Meetings | That a member's account becomes locked, and that it is deleted | Accounts | Prompt, to end a hand-over whose candidate is locked, and never lost for the deletion |
| Payments | That a payer's account is deleted | Accounts | Never lost |
| Each context | What it holds about a person, for the export | Itself | As of the day of the export |

See the [conceptual model](account-deletion-conceptual-model.md) and the [event storming](account-deletion-event-storming.md).
