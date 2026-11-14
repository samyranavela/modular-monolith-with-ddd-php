# Meetings: the life of a meeting group

Once a [meeting group is created](group-creation.md), it lives through its members: they join and leave, and the people who look after it change over time. This page follows that life, from the first member to the change of owner.

Terms are defined in the [Meetings glossary](../../../src/Meetings/GLOSSARY.md).

## Roles in a group

A meeting group has **members**, and each one holds one of three roles in it:

| Role | Who | Count |
| :-- | :-- | :-- |
| **Owner** | The member answerable for the group and for its subscription. The proposer is the first owner. | Exactly one |
| **Organizer** | A member who looks after the group, and stands in when the owner is not available. | Any number |
| **Member** | A member who takes part in the group. | Any number |

The owner has every right of an organizer, plus the ones that commit the group: appointing and demoting organizers, handing the group over, and carrying its subscription.

## Joining

Any member can join any meeting group, as a regular member. There is no request and no approval.

A member cannot join a group they already belong to.

## Leaving

A member or an organizer can leave a group at any time. The group remembers when they joined and when they left, and they can join again later, as a regular member.

The **owner cannot leave**, cannot be demoted and cannot be removed. They must first [hand the group over](#handing-the-group-over).

## Appointing and demoting organizers

Only the **owner** appoints and demotes organizers.

- **Appointing**: the person must be a regular member of the group, neither an organizer nor the owner. Nobody becomes an organizer any other way.
- **Demoting**: the organizer becomes a regular member again, and stays in the group.
- **Stepping down**: an organizer can demote themselves, which an organizer cannot do to anyone else.

An organizer can neither appoint nor demote another organizer.

## Handing the group over

The owner cannot leave, so the group changes hands in one way only: the owner **proposes a transfer** to one of the group's organizers, who becomes the new owner once they accept and have an active subscription.

- **The request.** The owner designates an organizer of the group. A **transfer request** is created, which is pending. There is one pending request at most for a group, and it never expires.
- **Accepting.** The candidate accepts, and accepting includes having an **active subscription**: if they have none, they take one out as part of accepting. The request stays pending until [Payments](../payments/description.md) confirms that the subscription is in effect and valid. Only then is the transfer complete.
- **Declining.** The candidate can decline, and the request ends.
- **Cancelling.** The owner can cancel a pending request, and the request ends.
- **Ending by itself.** If the candidate leaves the group, or is demoted, while the request is pending, the request is cancelled.
- **Completing.** The candidate becomes the owner and the former owner becomes an organizer. The group is then covered by the new owner's subscription.

Every one of these steps sends an **email to each party concerned**, the owner and the candidate: the request, the acceptance, the confirmation of the subscription, the refusal, the cancellation by the owner, the cancellation by itself and the completion.

A completed transfer is **final**. Going back means the new owner makes a new request, like anyone else.

What happens to the former owner's own subscription belongs to [Payments](../payments/description.md): for the group, it simply stops covering it.

## Covering the group

A group is covered by the subscription of **its owner**, and only a covered group can organize meetings. Meetings keeps, for each member, **until when** their subscription is valid, as announced by [Payments](../payments/description.md). The group is covered when its owner's subscription is still valid, from the moment the group is created, and the group follows the new owner's subscription after a hand-over.

There is no limit on the number of groups an owner's subscription covers.

### When the subscription ends

Payments announces when an owner's subscription **expires**, and also when it is **about to**. Two emails follow, to the owner: a reminder before the end, and a notice when the group is no longer covered.

An uncovered group is not closed down. Only one thing changes: it **cannot organize new meetings**.

- The meetings already planned stay as they are.
- Members can still join, leave and comment.
- The owner stays the owner: nothing is handed over by itself.

The group is covered again when the owner renews their subscription, which Payments announces, or when the group is [handed over](#handing-the-group-over) to an organizer who activates their own. A hand-over stays possible while the group is uncovered.

## Editing the group

The owner and the organizers can change the **name**, the **description** and the **place** of the group. A regular member cannot.

## What each role can do

| Who | Can do |
| :-- | :-- |
| A member | Join a group. Leave a group they belong to. |
| An organizer | Edit the group. Leave the group, or step down. |
| The owner | What an organizer does, except leaving or stepping down. Appoint and demote organizers. Hand the group over. |

An administrator is a user and not a member, so they take no part in the life of a group. The permissions behind this table are defined by Meetings and granted to the Member role by [UserAccess](../useraccess/description.md#permissions). Being an owner or an organizer is not a permission of a role: it is a role in one group, which the group itself checks.
