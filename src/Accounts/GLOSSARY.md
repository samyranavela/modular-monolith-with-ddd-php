# Accounts

Knows who the people of the application are, in which state their account is, which role they hold and what that role allows.

## Language

**Account**:
What the application holds for a person who can sign in: a login, a password, an email address, a first and a last name, a role, and the requests in progress. It is always in one state, pending, confirmed or locked, until it is deleted for good. Its identity never changes, whatever happens to its email address, and two accounts never share a login or an email address. 'Account' is also the everyday word ("delete my account").
_Avoid_: Profile

**User**:
The person who holds an account. The word stays where the person acts ("the user confirms the link"). The same person is a member in Meetings and a payer in Payments.

**Registration**:
The act of asking for an account by giving an email address: someone registers themselves, or an administrator invites them. It creates a pending account and sends a confirmation link. The answer is the same whether or not the email address is already held. It is not a thing that lives on its own.
_Avoid_: Sign-up, account request

**Administrator Invitation**:
A registration started for someone else's email address by an administrator or the operator, which creates a pending account with the Administrator role. Only the email address is known at first, as for any registration; the invited person fills in the rest when confirming.
_Avoid_: Admin account creation

**Pending Account**:
The state of an account created by a registration or an invitation, until its holder has followed the link and validated the form. It knows only an email address, can do nothing, not even sign in, and no other context knows it. Without a confirmation in time, it is removed.
_Avoid_: Provisional account, unconfirmed account

**Confirmed Account**:
The state of an account whose holder followed the link and validated the form, in time: login, names and password are given. It can do what its role grants. Confirming it is what lets the other contexts create a member and a payer.
_Avoid_: Active account, verified account

**Locked Account**:
The state of an account whose holder asked to leave, for as long as the grace period runs. Its holder can sign in and read everything a member with a confirmed account reads, cancel the request, export their data, mark their notifications as read and change their password, and change nothing else. Each context applies it to its own actions, by refusing the account every action that changes something.
_Avoid_: Suspended account, blocked account

**Confirmation Link**:
The link sent to the email address of a pending account. Following it shows the form that confirms the account. It works until the deadline, until the account is confirmed, or until a new link replaces it.
_Avoid_: Activation link, verification link

**Confirmation Deadline**:
The moment, 48 hours after the latest confirmation link was sent, when a pending account can no longer be confirmed. After it, the account is removed, and its login and email address become free again.
_Avoid_: Expiration status, timeout

**Email Address**:
The address that receives the emails for an account. A link proves that it belongs to the person, and no two accounts share one. It can change while the account stays the same. Always written 'email address' in full: a postal address is not a notion of the application.
_Avoid_: Address, mail

**Administrator**:
An account holding the Administrator role. Comes from an invitation, never from a self-registration, and is not a member of meeting groups.
_Avoid_: Admin, superuser

**Account Role**:
The kind of account, which each context uses to decide who may do what. There are two, Member and Administrator, and an account holds exactly one.
_Avoid_: Profile, group

**Access**:
What Accounts says of an account to the other contexts: its role, its state (confirmed or locked), and its subscription as Accounts knows it. A context reads it at every action and never keeps it.
_Avoid_: Permission, right, privilege

**Subscription (in Accounts)**:
Until when the subscription of an account is valid, and whether the account ever held one. A value that Accounts keeps from the fact given by Payments, which owns the subscription.

**Account Deletion Request**:
A member's request to leave the application for good. It can be made only when nothing stands in the way: no group owned under hand-over, no last host who counts of an upcoming meeting, no payment waiting, no group proposal pending. It locks the account for a grace period. The request has no status of its own: the account goes back to confirmed when the member cancels, or when the application cancels because something stands in the way at the end, and it is erased when the deletion is carried out. An administrator makes none: another administrator deletes them.
_Avoid_: Account closure, unsubscribe

**Open Item**:
A thing that stands in the way of a deletion request and must be settled first: a group owned under hand-over, a last host who counts of an upcoming meeting, a payment waiting, a group proposal pending. Checked at the request and again when the deletion is carried out.
_Avoid_: Blocker, prerequisite

**Grace Period**:
The time, 14 days by default and configurable, between a deletion request and the carrying out of the deletion. The account is locked meanwhile, and the member can cancel the request.
_Avoid_: Cooling-off, delay

**Email Address Change Request**:
A request by the holder of an account to give it a new email address, made when the new email address is free. It has a deadline, 48 hours later by default, and a link sent to the new email address confirms it, once and before the deadline. The request is then gone and the new email address is the account's. Until then the current one stays the account's. An account has one at a time: a new request replaces the previous one.
_Avoid_: Email update, address update

**Password Reset Request**:
What anyone with the email address of an account creates when they forgot the password: a link, sent to the email address, valid for one hour and for one use. An account has one at a time: a new request replaces the previous one.
_Avoid_: Recovery, password recovery

**Data Export**:
A document gathering what each context holds about a member, which they ask for at any time. An administrator has none. It is ready after a while and expires after a limited time.
_Avoid_: Backup, download

**Deleted Administrator**:
An administrator deleted at once by another administrator, never by themselves. The decisions they took stay, attributed to the deleted administrator.
_Avoid_: Former administrator
