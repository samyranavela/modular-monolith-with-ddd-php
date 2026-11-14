# UserAccess

UserAccess knows who may do what. It holds the **users** of the application, the **roles** they have and the **permissions** those roles grant.

Terms are defined in the [UserAccess glossary](../../../src/UserAccess/GLOSSARY.md).

## Users

A **User** is someone who can sign in with a login and a password. A user comes from a [confirmed registration](../registrations/description.md): the login, the password, the email address and the names given during it become the user's own, and the user receives the role the registration was created for. UserAccess creates no user any other way.

The user is the same person that other contexts know under other names: a member in Meetings, a payer in Payments, an administrator in Administration. UserAccess is the context that says who the person is; the others decide what that person can be.

## Roles

A user has **one or more roles**. A role is a named set of permissions. The application has two:

- **Member**: the ordinary user. A confirmed registration always gives this role.
- **Administrator**: the user who handles what ordinary users cannot, such as deciding on meeting group proposals.

An administrator is never the result of someone registering themselves: they come from an [invitation](../registrations/description.md#inviting-an-administrator), which an existing administrator, or whoever operates the application, sends. An administrator is a user only: not a member of the meeting groups, not a payer.

There is no way to turn an existing user into an administrator.

## Permissions

A **Permission** authorizes one action, such as accepting a meeting group proposal. A role holds one or more permissions. The permissions belong to the contexts whose actions they guard, so the Administration context defines what an administrator may do there, and UserAccess only stores the link between roles and permissions.

The rule is the same for every action: it is allowed when at least one of the user's roles grants its permission, and refused otherwise.

See the [conceptual model](conceptual-model.md). The creation of a user, member or administrator, is shown in the [event storming of the registration](../registrations/event-storming.md).
