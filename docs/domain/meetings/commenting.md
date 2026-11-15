# Meetings: commenting

The members of a group talk about its meetings in **comments**: before, to ask a question, and after, to share what they thought. Members reply to comments and like them, and the organizers of the group keep the conversation in order. This page follows a comment from its writing to its removal.

Terms are defined in the [Meetings glossary](../../../src/Meetings/GLOSSARY.md).

## Who does what

| Who | Can do |
| :-- | :-- |
| A member of the group | Comment on a meeting of the group, reply, edit and remove their own comments, like and unlike. |
| The owner or an organizer of the group | The same, and remove any comment, and allow or forbid comments on a meeting. |

Being a member, the owner or an organizer is a role in the group, which the aggregates check. A member does not need to attend the meeting to comment on it. The permissions behind these actions are defined by Meetings and granted to the Member role by [UserAccess](../useraccess/description.md#permissions).

Commenting does not depend on the life of the meeting: members can comment before it starts, after it ends, and even when it is cancelled, which is a place to explain why. The only thing that stops comments is the switch below.

## The commenting switch

Each meeting has a **commenting switch**, created **enabled** with the meeting. The owner or an organizer of the group turns it off to forbid comments on that meeting, and back on to allow them again. Turning it to the state it is already in changes nothing.

While commenting is disabled, nobody can write a comment or a reply, edit a comment, like or unlike. What is already there stays visible. The owner and the organizers can still **remove** comments: moderating is the reason to turn the switch off, so it must stay possible.

## Writing a comment

A member of the group writes a comment on a meeting. It needs:

- commenting to be enabled for the meeting;
- a text, which is not empty and has at most **2000 characters**, a limit that is configurable.

The comment remembers its author and when it was written. The same limit holds for a reply and for an edit.

## Replying

A member replies to a comment, under the same conditions as writing one. Replies stay on **one level**: replying to a reply attaches the new reply to the comment it answers, so a comment has replies and a reply has none.

A removed comment cannot be replied to.

## Editing

Only the **author** edits a comment, a reply included, under the same conditions as writing one. The comment remembers when it was edited, and readers see that it was. Nobody else can edit it, not even the organizers: they can only remove it.

A removed comment cannot be edited.

## Removing

The author removes their comment, and the owner or an organizer removes any comment, a reply included.

- When the author removes their own comment, they give no reason.
- When the owner or an organizer removes a comment of somebody else, they must give a **reason**, which the author is told, see [emails](#emails).

A removed comment is not erased: it stays in the conversation as a **removed** comment, with no text, so that the replies under it remain readable. Members of the group see who removed it and the reason, if there is one. A removed comment can no longer be edited, replied to or liked, and its likes are no longer shown. Removal is final.

Removing is possible whether or not commenting is enabled.

## Likes

A member of the group likes a comment, a reply included, **once**, and takes their like back whenever they want. They can like their own comment. Liking and unliking need commenting to be enabled, and the comment not to be removed.

Every member of the group sees how many likes a comment has and who liked it.

## When a member leaves the group

The comments and the likes of a member who leaves the group **stay**. The former member can no longer write, reply, edit, remove or like, since they are no longer a member, but the owner and the organizers can still remove their comments.

What happens to a member's content when their account is deleted concerns every context, and is described on its own page, [deleting an account](../useraccess/account-deletion.md). A comment whose author is gone stays readable.

## Emails

| When | Who is told |
| :-- | :-- |
| The owner or an organizer removes the comment of somebody else | The author, with the reason |

Nothing is sent when somebody replies or likes: members read them in the conversation.

## Reading

Read-only views, kept up to date by the events of the comments, let members follow the conversation.

- **The comments of a meeting**: the comments with their replies below, oldest first, each with its author, its text, when it was written and edited, and its number of likes. A removed comment shows as removed, with who removed it and the reason. The members of the group see it.
- **The likers of a comment**: the members who liked it. The members of the group see it.
- **The commenting state of a meeting**: whether comments are allowed, so that a member knows if they can write. The members of the group see it.

An administrator sees all these views, whether or not they belong to the group, but writes nothing.

There is no view of all the comments one member has written: such a view crosses the modules and belongs to a context that aggregates them.
