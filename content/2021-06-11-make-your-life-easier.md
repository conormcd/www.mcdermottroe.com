+++
title = "Make your life easier"
template = "page.html"
date = 2021-06-11T22:00:00Z

[taxonomies]
tags = ["Tech"]
+++

If you write software for long enough you begin to develop instincts about the
relative difficulty of different problems and some of those solidify into
rules of thumb. Like all such things they are not really _rules_ but patterns
of thinking that you can choose to deviate from when you have good reason to
do so. The following are some that I have come across recently. I can't claim
that any of them are original ideas, but they are some of the things that
guide my work on a daily basis.

# Choose simpler graph data structures

Many problems are graph problems of one sort or another and there's a
hierarchy of increasing complexity of graph types:

- list
- tree
- acyclic graph
- cyclic graph

All of those are technically graphs, but the amount of work you need to do to
operate on them gets noticeably harder as you work your way down the list. If
you can constrain your requirements to allow you to use structures nearer the
top of that list you will have fewer implementation bugs.

Sometimes you can't avoid a cyclic graph, but by remembering that it's harder
than the others you should stop and think "do I need more tests for this?"

# Default to immutable data

Cache invalidation is hard. If your data is immutable you rarely have to solve
that problem. Immutable data is also easier to archive and/or store in cheap
blob stores like S3 or in static files on a filesystem.

It can be very hard to make your data truly immutable. For example, the [GDPR
"right to be forgotten"](https://gdpr-info.eu/art-17-gdpr/) means that
anything containing [personal data](https://gdpr-info.eu/art-4-gdpr/) must be
deletable. So just like with graph data structures there's a kind of hierarchy
of mutability of data structures:

- Truly immutable. The data is created and is never destroyed.
- Can be atomicly created or destroyed but never modified.
- Can have portions appended or deleted, but previous data is never modified.
- Some portions of the data structure are immutable, others can be deleted or
  mutated in place.
- All parts of the data structure are mutable.

Stay as far up that list as you can and you will avoid many implementation
problems.

# Choose simpler programming models

There are lots of techniques to improve the performance of software. Use none
of them until you are forced to. Concurrency is hard to get right.
Event-driven systems can be difficult to reason about. Manual memory
management is a notorious source of bugs. Functions without side-effects are
easier to test than functions with them.

Start with single-threaded, single process code in the highest-level language
you can use and iterate from there. Use pure functions when you can, but don't
be a zealot about it. The ["Functional Core, Imperative
Shell"](https://www.destroyallsoftware.com/screencasts/catalog/functional-core-imperative-shell)
pattern can be very useful.

# Worry about all your public interfaces

[Hyrum's law](https://www.hyrumslaw.com/) teaches us that anything your
customer can perceive about your software is part of its public interface. If
you change it, [someone will complain](https://xkcd.com/1172/).

When you're designing new software, divide your problem space into features
and behaviours that are visible and invisible to customers. Put the bulk of
your effort into the visible parts, everything else is fixable later.

If you don't want your customer to depend on something, make it impossible to
depend upon. For example, if you have a function that returns a list of items
and for some reason you don't want the ordering to be defined then you may
need to deliberately shuffle the items to prevent people from depending on the
order.

# You probably can't avoid distributed systems

Even if you have only one web server running a monolithic application, if you
put a rich JavaScript web app in front of it you have a distributed system. If
you only work on static, single binary, CLI tools that don't touch the network
then you can avoid distributed systems but you might also be severely limiting
your career.

It's extremely hard to avoid distributed systems so you have to get good at
working on them. Here are some things to remember:

- There's no such thing as a zero-downtime atomic deploy. You can choose
  downtime if you want, but choosing a safe, non-atomic deployment pattern is
  much easier.
- Your system's topology is a graph. The guidance for graph data structures
  above applies here too. If you have cycles in your graph of services you
  will regret it.
- There's no such thing as a reliable network. It will fail, so don't use it
  unless you need it. If you make your services very "micro" then you are
  probably hitting the network more than you want.
- Eventual consistency is often good enough. Use it enthusiastically.
- Exactly-once delivery of messages is hard. At-least-once delivery of
  messages that are idempotent is much, much easier.
- Don't share data stores between different services. Have one, canonical
  owning service for every piece of data. Other services may cache the data,
  but they need to defer to the owning service.
- Use centralised logging and metrics systems from the beginning. Use a tool
  like [Honeycomb](https://www.honeycomb.io/) if you can. You will learn
  things about your system every day.

# Everything should have limits

<blockquote class="twitter-tweet"><p lang="en" dir="ltr">Every new feature should have a limit of 5</p>&mdash; Gordon Syme (@gordon_syme) <a href="https://twitter.com/gordon_syme/status/1400036732763049986?ref_src=twsrc%5Etfw">June 2, 2021</a></blockquote> <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

This is somewhat tongue-in-cheek but the point still stands. It's relatively
easy to relax a limitation, but if you don't have one to begin with you will
have to negotiate to introduce it.

Types of limits worth thinking about are:

- Request rate limits. An accidental denial of service by an overzealous
  customer is embarrassing for everyone.
- Request size limits. These apply on both input and output, for example
  limiting the size of a text field being inserted and the number of records
  that can be fetched at once.
- Pagination. If you return a sequence of anything it should be paginated and
  there should be a limit on the maximum size of the page.
- Application-specific limits. For example, if you're making an online store
  limit the number of product categories and limit the number of products per
  category. Everywhere you see a list, a one-to-many or a many-to-many
  relationship you should attempt to limit it.
- Time-based retention limits for data at rest. Don't promise to store things
  forever. If you don't want to implement the deletion immediately, hide it so
  that the customers are used to the idea that data goes away.
- Security limits. For example browser session lengths, failed login attempts,
  etc.

# Trust your gut

There are many other rules of thumb that I use in my day-to-day work, but it's
sometimes difficult to even notice that I'm doing it. If you want to become
more conscious about your own rules of thumb, trust your gut during code
reviews. If something seems wrong then you need to stop and think. Maybe
there's actually something wrong with the code or maybe your instincts are
wrong. Either way you should use that feeling to refine your own rules of
thumb and teach them to those that are coming after you.

Thanks to [Nathan Dintenfass](https://twitter.com/ndintenfass) for the prompt
to write this piece.
