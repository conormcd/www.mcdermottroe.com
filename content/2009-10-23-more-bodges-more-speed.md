+++
title = "More bodges, more speed"
template = "page.html"
date = 2009-07-23T00:00:00Z

[taxonomies]
tags = ["Tech"]
+++

I don't like kludgey solutions to problems. They catch up with you eventually
and it's usually more expensive in the long run. Unfortunately, like buying a
house, sometimes you have to take on some debt now rather than spend decades
trying to save enough to buy without being in debt.

Recently I've been trying to squeeze some more performance out of a LAMP
application - forum software to be precise - and I've been forced to
compromise a little. The latest challenge was a query like this:

```sql
SELECT postid
  FROM post
    WHERE
    threadid = 1234 AND
    visible = 1
  ORDER BY TIMESTAMP
    LIMIT 30, 15;
```

It pulls out the IDs of the posts that should be displayed on a given page of
a particular thread. In this case, it's the third page of the thread with the
ID of 1234. Pretty simple, and fast enough. The problem happens when you have
a thread with over 5,000 pages at 15 posts per page. Then a query for page
2,820 looks like this:

```sql
SELECT postid
  FROM post
    WHERE
    threadid = 1234 AND
    visible = 1
  ORDER BY TIMESTAMP
    LIMIT 42300, 15;
```

Now the query is slow because it has to sort the results by timestamp and then
seek all the way through that sorted list until it finds the 15 IDs it wants.
Worse, the query plan looks something like this (some columns removed for
formatting purposes):

```
+-------------+------+---------------+----------+-------+-------+-----------------------------+
| select_type | type | possible_keys | key      | ref   | rows  | Extra                       |
+-------------+------+---------------+----------+-------+-------+-----------------------------+
| SIMPLE      | ref  | threadid      | threadid | const | 76161 | Using where; Using filesort |
+-------------+------+---------------+----------+-------+-------+-----------------------------+
```

One very obvious problem here is the "Using filesort" part. No-one wants to
sort large numbers of rows like that. The simplest approach is to add an index
which covers the timestamp so that the entries can be read from the index in
sorted order.

```sql
ALTER TABLE post ADD INDEX tvt (threadid, visible, TIMESTAMP);
```

A little over an hour later, the query plan is now a bit better:

```
+-------------+------+---------------+-----+-------+-------+-------------+
| select_type | type | possible_keys | key | ref   | rows  | Extra       |
+-------------+------+---------------+-----+-------+-------+-------------+
| SIMPLE      | ref  | threadid,tvt  | tvt | const | 76161 | Using where |
+-------------+------+---------------+-----+-------+-------+-------------+
```

Testing this change shows approximately a 3x speedup. Sounds great until you
realise you're going from a 6 second query to a 2 second query. It's still way
too slow and the reason is that we're still scanning a huge amount of data. We
have a good pool of memcache servers so perhaps the sensible option is to
cache the results of the query. Unfortunately, there are 5 different page
sizes and other user-configurable bits and bobs that make the query hard to
cache as-is. The solution I came round to is to cut down on the number of rows
being paged through. The easiest way to do that is to calculate "hints" for
the query so that it can skip most of the data in one go.

The end result is something like this:

```sql
-- limit = page_number * page_size
-- limit_rounded = floor(limit / 1000) * 1000
-- limit_new = limit - limit_rounded
--
-- Check memcached for the hint for {1234, limit_rounded}
-- If memcached returns a miss, then calculate the hint like so:
SELECT TIMESTAMP
  FROM post
    WHERE
    threadid = 1234 AND
    visible = 1
  ORDER BY TIMESTAMP
  LIMIT @limit_rounded, 1;
-- Stash that timestamp in memcached.
--
-- Now actually run the query:
SELECT postid
  FROM post
    WHERE
    threadid = 1234 AND
    visible = 1 AND
    TIMESTAMP >= @hint
  ORDER BY TIMESTAMP
  LIMIT @limit_new, 15;
```

Since the first query is only run on cache misses we're only really interested
in the performance of the second one. Here's an example query plan for a page
near the end of the thread:

```
+-------------+-------+---------------+-----+------+------+-------------+
| select_type | type  | possible_keys | key | ref  | rows | Extra       |
+-------------+-------+---------------+-----+------+------+-------------+
| SIMPLE      | range | threadid,tvt  | tvt | NULL | 301  | Using where |
+-------------+-------+---------------+-----+------+------+-------------+
```

Far fewer rows were examined and so the query executed much faster (0.01
seconds). All should be well, but I'm still left with a bunch of bugs (and
these are the ones I can think of immediately):

- The timestamp is only gated in one direction so pages at the start of the
  thread are much slower than pages at the end of the thread. This is (barely)
  acceptable for two reasons: 1) people tend to read the newer posts, not the
  older ones and 2) the cost of 2 cache misses would be in the 4 second range.
- If two posts are made in the same second and span a 1000 post boundary the
  paging will be off.
- If a post is deleted or hidden in the middle of a thread, the paging will be
  off by 1 until the cache expires.
- If memcached disappears and the cache call always misses, then the delay
  will be roughly twice the length of the unhinted query (4-5 seconds).

I'm not happy introducing all those bugs but performance requirements dictated
that some compromises were made. The original query was being run somewhere in
the region of 400,000 times per day, all that time adds up. Overall I think it
was a necessary bodge but I'm already dreading the day when I have to find a
less buggy solution to the problem.

What do you think? Was it worth it? Is there a bug-free way of doing that
query without taking much too long?
