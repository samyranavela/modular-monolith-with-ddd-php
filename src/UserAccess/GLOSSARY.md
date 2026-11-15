# UserAccess

Knows who the users are, which roles they have and what those roles allow.

## Language

**User**:
Someone who can sign in with a login and a password. Created from a confirmed registration.
_Avoid_: Account, profile

**Administrator**:
A user holding the Administrator role. Comes from an invitation, never from a self-registration, and is not a member of meeting groups.
_Avoid_: Admin, superuser

**User Role**:
A named set of permissions that a user holds. There are two: Member and Administrator.
_Avoid_: Profile, group

**Permission**:
The authorization to perform one action. An action is allowed when one of the user's roles grants its permission.
_Avoid_: Right, privilege

**Account Deletion Request**:
A member's request to leave the application for good. It can be made only when nothing stands in the way: no group owned under hand-over, no last host of an upcoming meeting, no payment waiting, no group proposal pending. It waits for a grace period, during which the account is locked, and ends cancelled by the member, cancelled by the application when something stands in the way at the end, or completed when the deletion is carried out. An administrator makes none: another administrator deletes them.
_Avoid_: Account closure, unsubscribe

**Locked Account**:
The state of a user whose deletion request is pending. They can sign in to cancel the request or export their data, and do nothing else.
_Avoid_: Suspended account, blocked user

**Deleted User**:
A user whose login, email address, names and password were erased. Only the identifier remains, which names nobody. Final.
_Avoid_: Disabled user, archived user

**Data Export**:
A document gathering what each context holds about a user, which they ask for at any time. It is ready after a while and expires after a limited time.
_Avoid_: Backup, download
