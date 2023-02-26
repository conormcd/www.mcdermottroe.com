+++
title = "Limit your software or your customers will do it for you"
template = "page.html"
date = 2023-02-18T00:00:00Z

[taxonomies]
tags = ["Tech"]
+++

I wrote previously about [Coding Guidelines for an Easier Life](@/2021-06-11-coding-guidlines-for-an-easier-life.md)
and my brief comments on limits provoked the most interest with reactions like:

<blockquote class="twitter-tweet" data-conversation="none"><p lang="en" dir="ltr">The limits section really resonates with me the most. Before joining Amazon, I used to think that &quot;good&quot; system have no limits, that they should essentially be unbounded. But the more robust systems I&#39;ve worked on have known limits and are adjusted overtime.</p>&mdash; memattchung (@memattchung) <a href="https://twitter.com/memattchung/status/1405221384138018820?ref_src=twsrc%5Etfw">June 16, 2021</a></blockquote>
<blockquote class="twitter-tweet" data-conversation="none"><p lang="en" dir="ltr">Nice. All ring true, but running prod systems, &quot;everything should have limits&quot; is *so* helpful, and was a huge lesson to me. In practice, everything always has limits, but if you don&#39;t set them explicitly you only discover them (painfully) at runtime.</p>&mdash; Simon Frankau (@simon_frankau) <a href="https://twitter.com/simon_frankau/status/1405169647482281989?ref_src=twsrc%5Etfw">June 16, 2021</a></blockquote>

The last bit of Simon Frankau's tweet resonated particularly strongly with me: 
>In practice, everything always has limits, but if you don't set them explicitly you only discover them (painfully) at runtime.

_All_ systems have limits. As an engineer, your only choice is whether to make those limits explicit...
or to let users run headlong into them at a time when you might or might not be prepared. 

With that in mind, let's explore the topic of limits.


# Contents

- [Principles of limiting](#principles-of-limiting)
- [Types of limits](#types-of-limits)
  - [Rate limits](#rate-limits)
  - [Size limits](#size-limits)
  - [Time limits](#time-limits)
  - [Application-specific limits](#application-specific-limits)
  - [Business-specific limits](#business-specific-limits)
- [You Know Your Limits, Now What?](#you-know-your-limits-now-what)
  - [How do you track the limits?](#how-do-you-track-the-limits)
  - [What should you do when a limit is hit?](#what-should-you-do-when-a-limit-is-hit)
  - [Monitoring & Alerting](#monitoring-alerting)
- [Conclusion](#conclusion)

# Principles of limiting

Before diving into the details, it's worth setting out some guiding
principles. Like almost everything else in software engineering, implementing
limits is a matter of judgement so it's not wrong to deviate from these
principles so long as you understand _why_ you're doing so.

1. **Understand how your systems work.** If your software is full of [haunted
   forests](https://increment.com/software-architecture/exit-the-haunted-forest/)
   you are going to struggle to implement sensible limits without endangering
   the stability of your system.
2. **Understand the impact of limiting your users.** Restricting what your
   users can do is a painful process. You may be forced to choose to forego
   additional revenue in order to protect the stability of your system. You
   may choose to put roadblocks on your sign up process to prevent abuse.
   These can be rational choices but you need to make them carefully, with
   data and with agreement from all the relevant stakeholders.
3. **Limit as close to the edge as possible.** If you can promptly and
   synchronously refuse your customer's request it's usually a cleaner user
   experience that makes it easier for them to understand. It's often the
   safest option when implementing defences against denial of service attacks
   because you can consume fewer resources per blocked request.
4. **Layer your defences.** You won't always be able to limit at the edge of
   your system. For example, if you want to limit each user to 10 requests per
   second you need to implement that after your authentication code has run.
   That can leave your authentication layer vulnerable so you could implement
   a global 10,000 requests per second limit in front of it, or a 1,000
   requests per second per IP limit.
5. **It's easier to relax a limit than tighten it.** Taking stuff away from
   your users rarely makes them happy. Heavy use of your system(s) can also be
   an indicator of a customer being heavily invested in your product and those
   are exactly the people you _don't_ want to annoy. With that in mind, when
   creating new limits you should always pick the tightest restriction you can
   get away with.
6. **Implement limits sooner rather than later.** Limiting something that was
   previously unlimited can be very painful for your users. Doing so during an
   outage in order to resolve the problem is very painful for you and your
   colleagues. The best way to avoid this pain is to make a discussion about
   limits an integral part of your design process.

# Types of limits

Now that we have some principles, we can start to enumerate the different
places you should consider defining limits. A mature system will have many
overlapping limits complementing each other so the best way to think of the
following items is as prompts. If you're not implementing all of them that's
OK, but you should either plan some work to add them or document why they
aren't appropriate in your case.

## Rate limits

Limiting the rate at which things are permitted to happen is both one of the
most common restrictions in production systems and also one of the most
frequently botched.

- Request rate limits
  - By IP – This is a very simple and cheap rate limit to implement because it
    can be done very close to the edge of your product before the request
    consumes many resources. If you run something like nginx in front of your
    application you can [easily implement a per-IP rate limit with
    it](https://www.nginx.com/blog/rate-limiting-nginx/). If you're in AWS you
    can implement it in [AWS WAF](https://aws.amazon.com/waf/).
  - By user – Users should have their own rate limits. Obviously these need to
    be enforced after authentication, so this rate limit enforcement will be
    more closely intertwined with your own product. This should prompt you to
    do two things: first, ensure that authentication is done as early as
    possible in the request handling processl; second, implement the rate
    limiting immediately after authentication.
  - By account/team/organization – If you don't charge per-user prices then
    you should probably consider rate limiting across the entire account (or
    whatever your billable entity is). Otherwise an enterprising customer can
    increase their rate limit by adding extra users to their account.
- Login attempt limits – This is a special case of limiting requests, where
  the primary goal is security and not protecting resource consumption. For
  user experience reasons you will need to permit short bursts of failed
  authentication attempts but you should be very wary of allowing more than a
  handful of failed requests. The [OWASP Authentication Cheat
  Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html#login-throttling)
  covers this in more detail.

## Size limits

- Request size limits – You should have a hard limit on the size of an inbound
  request and that should be enforced _before_ you parse it. If you're
  operating a typical JSON-over-HTTP service you should understand the RAM
  consumption of your JSON parser and set the limit on the JSON string length
  to a value consistent with an amount of RAM you're capable of allocating for
  each request. If you're using XML or YAML you should also be aware of the
  [billion laughs
  attack](https://en.wikipedia.org/wiki/Billion_laughs_attack) and configure
  your parser to limit the number or depth of expansions.
- Response size limits – Limiting the size of your responses is fully within
  your control but it can also be quite tricky to do effectively while keeping
  your customers happy.
  - Always paginate everything. Anywhere you have a sequence of items in an
    API response you must either paginate the response or put a hard limit on
    the sequence length.
  - Your pagination technique matters too. The page size must be either fixed
    or have a maximum size. There are many ways of indicating which page to
    fetch (e.g. page number, offset, opaque token encoding a cursor) and your
    choice will probably dictate your maximum page sizes.
  - Your pagination options should be decided by your data layer. Getting all
    the results for a query and then selecting the exact 10 item page
    that was requested is very wasteful. 
  - Even when you're paginating, the items that you paginate need to be kept
    as small as possible.
- Storage size limits – Anything that your users persist in your datastores
  needs to be constrained. If you present a `textarea` on a web page and then
  store the contents when it's submitted it must have a character limit. If
  you store binary files on behalf of your users you need a limit in bytes. Be
  particularly careful when one user is allowed to see the content saved by
  another user. A `textarea` that's viewable by more than one person will
  eventually be used as a chat system. File upload/download capabilities can
  very quickly become a file sharing platform. If you're paying for the
  storage you need to ensure that the constraints on the system discourage any
  use that doesn't align with your interests.

## Time limits

- Retention periods – It's very tempting to retain data forever just in case
  you might need it later. You will eventually run into technical, cost or
  governance constraints that force you to store less. Ideally you will
  implement retention limits before your colleagues in
  [GRC](https://en.wikipedia.org/wiki/Governance,_risk_management,_and_compliance)
  ask you to. You can often buy time to work around technical or cost
  constraints with increased spending or a few targeted hacks. If your
  software is in breach of a law, regulation or compliance system you can only
  engineer your way out of it by solving the problem directly. Doing that
  under extreme time pressure is something to be avoided at all costs.
  Sometimes it can be complex to implement the retention limits so if you're
  short on time at least implement some soft limits in your presentation
  layer. If old data is hidden from your customers you can figure out how to
  delete it later and they won't notice or complain. The exception to this is
  personal information or anything else your users might consider to be
  sensitive. For that, you probably need to hard delete anything the user
  can't see in order to maintain trust.
- Session limits – Sessions must expire and they should also be subject to
  idle timeouts. Both the maximum length of a session and the idle timeout are
  user experience concerns so you will need to choose values that align with
  the type of system you're building. Sensitive applications will trade user
  experience for shorter session times to improve security. The [OWASP Session
  Management Cheat
  Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html#session-expiration)
  has more detail on session durations and many other facets of securing your
  session management.
- Sum-of-duration limits – If some operations in your application consume more
  time or resources than others then you should be careful to limit their
  impact on your system's stability and cost. You need to defend both against
  denial of service attacks and innocent usage by customers that dramatically
  increase your costs. This can be done simply, by giving those operations
  lower rate limits. A more sophisticated approach would record the time or
  cost of every operation and implement rate limits in terms of consuming time
  or credits from a fixed budget.

## Application-specific limits

- Examine your data model – If you have one-to-many or many-to-many
  relationships in your data model you should consider limiting what "many"
  means for each relationship. Consider web forum software: there should be a
  limit on the number of threads per forum and a limit of posts per thread.
  That allows you to generate integration tests that simulate the worst case
  scenarios and ensure that performance is within acceptable bounds. It's much
  easier to fix performance tipping points in testing than it is when one of
  your customers finds it for you.
- Thread/connection pools – In many languages, frameworks and systems there will be
  resource pools. Examine all of them and ensure that they have limits that
  align with your hard constraints. For example, if you have a simple web app
  that uses an SQL database you should find out the maximum number of
  connections your database can tolerate. If that's say, 50,000 and you have
  10 application servers then you should ensure that your database connection
  pool size limited to at most 5,000 connections. If one application server
  maxes out at 5,000 connections it's much nicer to lose 10% of your traffic
  than let that continue on to consume everything on your database and
  eventually lose 100% of your traffic due to a saturated database.

## Business-specific limits

Every system will have some costs in terms of compute, RAM, storage,
bandwidth, etc. but each system will have its own blend unique to the problem
it's solving. Find your major costs and ensure that your limits help to keep
you control the biggest ones. You also need to align your costs, your limits
and the value you give to your customers. For example, if you're running a
video transcoding service you will probably have significant compute, storage
and bandwidth costs. You need to limit both the amount of video transcoded per
user but also how many output artifacts you store and for how long. The
customers probably value the transcoding higher than the storage, so you can
be more aggressive with the limit on storage than you can with the limit on
how many videos each user can transcode.

# You Know Your Limits, Now What?

Once you've determined which types of limits you want, you need to actually
implement them.

## How do you track the limits?

- Ready-made – Many pieces of software have configurable limits so once you
  set them up they'll take care of tracking and enforcing the limits. For
  example the Kong API gateway has [the ability to rate limit built
  in](https://docs.konghq.com/hub/kong-inc/rate-limiting/) so you can
  implement pretty sophisticated rate limiting through configuration alone.
- Hand-rolled – Sometimes you need to implement a limit in your own
  application code. For simple resource limits (e.g. max number of bytes
  stored) this is generally trivial. If you need to implement a rate limit
  yourself, I recommend using a [leaky
  bucket](https://en.wikipedia.org/wiki/Leaky_bucket) algorithm as there are
  numerous examples freely available on the web which you can learn from or
  adapt to your needs.

## What should you do when a limit is hit?

- Return appropriate machine-readable results – Ensure that a well-behaved
  client can react automatically to being limited. For HTTP this means using
  the HTTP status code of 429 or 503 and (if possible) some combination of
  `RateLimit-Limit`, `RateLimit-Remaining`, `RateLimit-Reset`, and
  `Retry-After` headers. For gRPC you could return the `RESOURCE_EXHAUSTED`
  status.
- Buffer work requests – If you have an API that is a request to do some work
  asynchronously it can be valuable to use a queue to implement a global rate
  limit. Rather than doing the work in the API handler, queue a request to do
  the work and then consume that queue at a known-safe rate. This allows some
  customers to provide bursts of requests without overwhelming the system. Be
  warned though, this will only help smooth over bursts of requests. [It
  doesn't give you extra
  capacity](https://ferd.ca/queues-don-t-fix-overload.html).
- Silently drop stuff on the floor – If you have tiered rate limits your
  outermost tier can be a crude connection or packet limit that
  unceremoniously drops traffic. For example, you might have a 1 req/sec/user
  limit in your application, a 10 req/sec/IP limit at your HTTP proxy and 100
  req/sec/IP at your firewall. The firewall protects the HTTP proxy by just
  dropping packets if the traffic is very high, then the HTTP proxy protects
  the app and returns customer-friendly 429s and then the app can return 429s
  with detailed `Retry-After` or `RateLimit-Limit` headers computed from the
  authenticated user's quota.
- Charge more! – If you're running a commercial system you should use your
  pricing structure to help control your costs. If you align your pricing with
  your costs then your users will implement limits for you and will naturally
  back off to fit within their own budgets.

## Monitoring & Alerting

You also need to monitor and alert on the performance of your limits in order
to ensure that they are behaving as expected. Here are a few items that you
should track:

- Percentage of requests which have been limited. If this is zero, then you
  should consider tightening your limits. You can always relax them later.
- Percentage of customers who have been limited. This can be used as an
  indicator of a broken limiting system.
- Percentage of customers who have been limited, weighted by revenue. If your
  highest-paying customers are being limited more often than your
  lowest-paying customers then it might point to a mismatch between your
  pricing structure and your resource limits.

# Conclusion

In summary, you should:

- Understand your systems so that you can reason about their inherent
  limitations.
- Design limits into your systems as early as possible.
- Understand how the limits impact your users.
- Limit as close to the edges of your systems as possible.
- Layer your defences to balance protection and user experience.
- Start with tight limits and then loosen them.
