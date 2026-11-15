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

A member or an organizer can leave a group, once they are not the last [host](organization.md#hosts) of a meeting that has not started: another host must be designated first. The group remembers when they joined and when they left, and they can join again later, as a regular member. Leaving takes the member out of the group's upcoming meetings, see [organizing meetings](organization.md#when-a-member-leaves-the-group).

The **owner cannot leave**, cannot be demoted and cannot be removed. They must first [hand the group over](#handing-the-group-over).

## Appointing and demoting organizers

Only the **owner** appoints and demotes organizers.

- **Appointing**: the person must be a regular member of the group, neither an organizer nor the owner. Nobody becomes an organizer any other way.
- **Demoting**: the organizer becomes a regular member again, and stays in the group.
- **Stepping down**: an organizer can demote themselves, which an organizer cannot do to anyone else.

An organizer can neither appoint nor demote another organizer.

## Handing the group over

The owner cannot leave, so the group changes hands in one way only: the owner **proposes a transfer** to one of the group's organizers, who becomes the new owner once they accept and have an active subscription. A candidate on the [free plan](#the-free-plan) can be chosen: they take a subscription out to complete the transfer.

- **The request.** The owner designates an organizer of the group. A **transfer request** is created, which is pending. There is one pending request at most for a group, and it never expires. The request is refused while a [payment](../payments/meeting-fees.md#a-hand-over-of-the-group) of one of the group's meetings still waits. From the request until the transfer ends, whichever way, **sign-ups to the group's meetings that have a fee are closed**, so that no payment is created while the receiver of the fees may change.
- **Accepting.** The candidate accepts. When a meeting of the group that has a fee has yet to start, the acceptance counts only if the candidate has a **validated payout account**: otherwise it is refused, and the candidate sets one up first. Accepting also means having an **active subscription**: a candidate who has none takes one out, or renews, in [Payments](../payments/subscriptions.md#a-hand-over-of-a-group), like any payer. The request stays pending until Meetings knows from Payments that the subscription is valid, and completes at once when it already is. Only then is the transfer complete.
- **Declining.** The candidate can decline, and the request ends. The sign-ups to the meetings with a fee reopen.
- **Cancelling.** The owner can cancel a pending request, and the request ends.
- **Ending by itself.** If the candidate leaves the group, or is demoted, while the request is pending, the request is cancelled.
- **Completing.** The candidate becomes the owner and the former owner becomes an organizer. The group is then covered by the new owner's subscription, and the sign-ups to its meetings with a fee reopen. The fees of the sign-ups to come go to the new owner's payout account, and those already paid stay with the former owner's.

Every one of these steps sends an **email to each party concerned**, the owner and the candidate: the request, the acceptance, the confirmation of the subscription, the refusal, the cancellation by the owner, the cancellation by itself and the completion.

A completed transfer is **final**. Going back means the new owner makes a new request, like anyone else.

What happens to the former owner's own subscription belongs to [Payments](../payments/description.md): for the group, it simply stops covering it.

## Covering the group

A group is covered by the subscription of **its owner**, and only a covered group can organize meetings. Meetings keeps, for each member, **until when** their subscription is valid, as announced by [Payments](../payments/description.md). The group is covered when its owner's subscription is still valid, from the moment the group is created, and the group follows the new owner's subscription after a hand-over.

There is no limit on the number of groups an owner's subscription covers.

### The free plan

An owner who has **never held a subscription**, a trial included, is on the [free plan](../payments/subscriptions.md#without-a-subscription-the-free-plan), and Meetings knows it because it holds no expiration date for them. Such an owner has **one group**, which is covered. The limit is configurable.

A member on the free plan **cannot own a second group**: they cannot [propose](group-creation.md#proposing) one while they own one, and a proposal of theirs cannot be accepted if they have become owner of a group in the meantime. They take a subscription or a trial first. Hence a group on the free plan is always covered, and an owner holding a subscription may own as many groups as they like.

A covered group on the free plan can have at most **two meetings a month**, see [creating a meeting](organization.md#creating-a-meeting). Its other rules are those of any covered group. A transfer of the group to a member on the free plan completes only once that member holds a valid subscription, see [handing the group over](#handing-the-group-over).

The free plan is left for good with the first subscription or trial. The group is then covered by the subscription as usual, and when it expires the group is uncovered, not back on the free plan.

### When the subscription ends

Payments announces when an owner's subscription **expires**, and also when it is **about to**. Two emails follow, to the owner: a reminder before the end, and a notice when the group is no longer covered.

An uncovered group is not closed down. Only one thing changes: it **cannot organize new meetings**.

- The meetings already planned stay as they are.
- Members can still join, leave and comment.
- The owner stays the owner: nothing is handed over by itself.

The group is covered again when the owner renews their subscription, which Payments announces, or when the group is [handed over](#handing-the-group-over) to an organizer who activates their own. A hand-over stays possible while the group is uncovered.

## Editing the group

The owner and the organizers can change the **name**, the **description** and the **place** of the group. A regular member cannot.

## Reading

Members find and follow groups through read-only views, kept up to date by the events of the groups.

- **All the groups**: the name, the description, the place and the number of members of every group, so that a member can find one to join.
- **A group**: its name, description, place, number of members, its owner and its organizers, and its meetings, see [organizing meetings](organization.md#reading).
- **The members of a group**: who belongs to it and with which role. The members of the group see it.
- **My groups**: the groups a member belongs to, with their role in each.
- **The upcoming meetings a member hosts**: for each group, the meetings that have not started and are not cancelled where the member is a host, with the number of hosts of each. It is not shown to anyone: it only lets the group refuse the departure of the last host of a meeting.

## What each role can do

| Who | Can do |
| :-- | :-- |
| A member | Join a group. Leave a group they belong to. Find groups, view a group, list their own groups. View the members of a group they belong to. |
| An organizer | Edit the group. Leave the group, or step down. |
| The owner | What an organizer does, except leaving or stepping down. Appoint and demote organizers. Hand the group over. |

An administrator is a user and not a member, so they take no part in the life of a group, but they see all the views of [reading](#reading), including the members of a group they do not belong to. The permissions behind this table are defined by Meetings and granted to the Member role by [UserAccess](../useraccess/description.md#permissions). Being an owner or an organizer is not a permission of a role: it is a role in one group, which the group itself checks.
