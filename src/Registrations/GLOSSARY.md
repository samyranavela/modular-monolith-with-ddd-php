# Registrations

Turns a person who wants to join into a user, by checking that the address they gave is theirs. Ends when the registration is confirmed or deleted.

## Language

**User Registration**:
A person's request to become a user, made of an email address, a login, a password and a first and last name. It waits for confirmation, has a deadline, and carries the role the user will receive.
_Avoid_: Sign-up, account request

**Administrator Invitation**:
A user registration started for someone else's email address by an administrator or the operator, which will give the Administrator role. Only the email address is known at first; the invited person fills in the rest when confirming.
_Avoid_: Admin account creation

**Waiting For Confirmation**:
The status of a registration whose confirmation link has not been followed yet.

**Confirmed**:
The status of a registration whose link was followed in time. It leads to a user.

**Confirmation Link**:
The link sent to the registration's email address. Following it confirms the registration.
_Avoid_: Activation link, verification link

**Confirmation Deadline**:
The moment, 48 hours after the latest confirmation link was sent, when a registration can no longer be confirmed. After it, the registration is deleted, and its login and email address become free again.
_Avoid_: Expiration status, timeout
