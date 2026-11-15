# UserAccess

Knows who the users are, which roles they have and what those roles allow.

## Language

**User**:
Someone who can sign in with a login and a password. Created from a confirmed registration. 'Account' is the everyday word for a user seen by themselves ("delete my account", "locked account"); the term of the domain stays user.
_Avoid_: Profile

**Administrator**:
A user holding the Administrator role. Comes from an invitation, never from a self-registration, and is not a member of meeting groups.
_Avoid_: Admin, superuser

**User Role**:
A named set of permissions that a user holds. There are two, Member and Administrator, and a user holds exactly one.
_Avoid_: Profile, group

**Permission**:
The authorization to perform one action. An action is allowed when the user's role grants its permission. Each permission reads or changes something, and one that changes something is refused to a locked account.
_Avoid_: Right, privilege

**Account Deletion Request**:
A member's request to leave the application for good. It can be made only when nothing stands in the way: no group owned under hand-over, no last host of an upcoming meeting, no payment waiting, no group proposal pending. It waits for a grace period, during which the account is locked, and ends cancelled by the member, cancelled by the application when something stands in the way at the end, or completed when the deletion is carried out. An administrator makes none: another administrator deletes them.
_Avoid_: Account closure, unsubscribe

**Locked Account**:
The state of a user whose deletion request is pending. They can sign in and read everything an active member reads, cancel the request, export their data and mark their notifications as read, and change nothing else. UserAccess applies it by refusing them every permission that changes something.
_Avoid_: Suspended account, blocked user

**Open Item**:
A thing that stands in the way of a deletion request and must be settled first: a group owned under hand-over, a last host of an upcoming meeting, a payment waiting, a group proposal pending. Checked at the request and again when the deletion is carried out.
_Avoid_: Blocker, prerequisite

**Grace Period**:
The time, 14 days by default and configurable, between a deletion request and the carrying out of the deletion. The account is locked meanwhile, and the member can cancel the request.
_Avoid_: Cooling-off, delay

**Deleted User**:
A user whose login, email address, names and password were erased. Only the identifier remains, which names nobody. Final.
_Avoid_: Disabled user, archived user

**Data Export**:
A document gathering what each context holds about a member, which they ask for at any time. An administrator has none. It is ready after a while and expires after a limited time.
_Avoid_: Backup, download

**Deleted Administrator**:
An administrator deleted at once by another administrator, never by themselves. The decisions they took stay, attributed to the deleted administrator.
_Avoid_: Former administrator
