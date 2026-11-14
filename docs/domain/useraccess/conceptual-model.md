# UserAccess: conceptual model

The things of the domain and how they relate. The user's creation is shown in the [event storming of the registration](../registrations/event-storming.md), since UserAccess has no process of its own to draw.

```mermaid
classDiagram
    direction LR

    class User {
        login
        email
        first name
        last name
    }
    class UserRole {
        name
    }
    class Permission

    User "1" --> "1..*" UserRole : has
    UserRole "1..*" --> "1..*" Permission : grants

    namespace Registrations {
        class UserRegistration
    }
    namespace Administration {
        class Administrator
    }
    namespace Meetings {
        class Member
    }
    namespace Payments {
        class Payer
    }

    UserRegistration ..> User : once confirmed, leads to
    Administrator ..> User : is a
    Member ..> User : is a
    Payer ..> User : is a
```

## Reading it

- **User** carries what the person gave at sign-up. It always has at least one role.
- **User Role** is a named group of permissions. There are two, Member and Administrator.
- **Permission** is the smallest unit: one action that can be allowed.
- The dotted arrows are the same person seen from other contexts. UserAccess does not know them: they know the user.
