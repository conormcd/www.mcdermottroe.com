+++
title = "A stand-up that works"
template = "page.html"
date = 2026-01-09T00:00:00Z

[taxonomies]
tags = ["Tech"]
+++

I've been very lucky to work with a team at [CircleCI](https://circleci.com/)
which has managed to maintain a very productive set of regular meetings for
several years. Many other teams I've worked with over the years have struggled
to make their meetings work. In particular,
[stand-ups](https://en.wikipedia.org/wiki/Stand-up_meeting) seem to be very
difficult to get right, so this team stands out.

The core features of their stand-up are:

- It is not a status meeting. If anyone cares about what someone is working on
  yesterday, today or tomorrow they can look in Jira. Moving tickets across
  the board is the responsibility of each member.
- There is a written record in Confluence. Each team member (including the
  Engineering Manager and Product Manager) takes a turn to be the host.
  There's a Confluence macro to generate a new page with the prompts laid out.
  The host reads the prompts and writes down anything team members say. They
  share their screen while they're doing this so that folks can help them get
  the wording right, correct mistakes/omissions/etc.
- It starts on time, regardless of who's there. Anyone who's late can catch up
  using the written record. (This doesn't have a 100% hit rate, but the desire
  is there and the success rate is good enough.)
- Nobody is required to speak. If you have nothing that needs to be added,
  that's great. Silence can be interpreted as "everything is going exactly to
  plan".
- The team owns the process. This format has been followed roughly for years
  but the small details have been tweaked by the team as circumstances have
  changed and members have joined and left.

The prompts for the stand-up are:

- Have you learned anything interesting for the team?
- Are you blocked or uncertain about how to proceed? Would you like some ideas
  or another pair of eyes?
- Are there any notable releases to production that the team should know
  about?
- What are you doing that's not on the board?
- Anything the team should know? Interesting happenings, vacations?

It's not unusual for several of the prompts to have nothing written under
them, particularly when the team is working well. Typical duration is five
minutes.

Should you copy this process verbatim and use it on your team? Probably not.
The reason this meeting structure works so well for this team is because they
designed it to solve their problems and they have the authority to change it
at any time if circumstances require it to. If I had to start from scratch and
build a stand-up process for a new team I would focus on the following
strengths I have identified in this team's process:

- **Keep it quick and high value.** A meeting where everyone's time is valued
  and respected gets a lot more buy-in from the participants. Start promptly,
  don't waste time by reading stuff out if it's already written down and stick
  to the important details. If an item needs more in-depth discussion, then
  either push it to the end or schedule a dedicated meeting for it. That
  doesn't mean that the content needs to be strictly business-only. If a team
  member shares that they didn't get enough sleep the night before because
  they have a sick child that's extremely valuable information. It gives
  useful context for their manager and colleagues and helps build
  relationships on the team.
- **Collective ownership and responsibility.** The meeting exists to serve the
  needs of the team so they own the structure and they're responsible for
  making it work. If there are issues with the meeting they're expected to
  bring them to the team's retrospective meeting. The manager should prompt
  them if necessary and facilitate the retrospective but the goal of a
  stand-up should be to help the team as a whole not as a reporting mechanism
  for the EM or PM.
- **Written records.** Keeping a written record that's reviewed in real time
  helps avoid miscommunications caused by choppy internet connections,
  unfamiliar idioms, accents, etc. It also makes it very easy to catch up
  after taking time off or if people get double-booked with other meetings. It
  allows people to share items ahead of time too which can help keep the
  meeting from running over time.

Those would be my starting positions, but I would expect the meeting to evolve
as the team experiments with it and figures out what works for them.
