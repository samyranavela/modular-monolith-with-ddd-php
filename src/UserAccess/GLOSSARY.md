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
