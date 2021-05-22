+++
title = "Profiling PHP with XHProf"
template = "page.html"
date = 2010-07-06T00:00:00Z

[taxonomies]
tags = ["Tech"]
+++

If you find yourself writing any performance sensitive code in PHP, you
probably want a profiler to tell you where the slowest parts of your code are.
Sometimes you can get by with educated guessing and a few well-placed uses of
echo and time, but there really is no substitute for hard data. Luckily,
[Facebook have written and released a profiler for
PHP](http://www.facebook.com/note.php?note_id=62667953919) and it's pretty
easy to use.

First off, [download](http://pecl.php.net/package/xhprof) and install it. It's
a [PECL](http://pecl.php.net/) extension to PHP, so it should install like any
other PECL extension you have. I'm developing on top of
[FreeBSD](http://www.freebsd.org/), so I made a port for it. <strike>It's not
yet in the ports tree, but if you're running PHP on FreeBSD, you can extract
the port out of [the PR I
filed](http://www.freebsd.org/cgi/query-pr.cgi?pr=ports/148332).</strike> It's
in the ports tree as
[devel/pecl-xhprof](http://www.freshports.org/devel/pecl-xhprof).

Once you have it installed, here's how you use it:

```php
<?php
// Start the profiler
//
// XHPROF_FLAGS_MEMORY adds memory usage data, it's quite useful.
// See the docs for further flags.
xhprof_enable(XHPROF_FLAGS_MEMORY);

// Put the bulk of your code here

// Stop the profiler and get the profile data
$profile_data = xhprof_disable();
?>
```

After you get the profile data you can either save it somewhere and use the
[XHProf
UI](http://web.archive.org/web/20110514095512/http://mirror.facebook.net/facebook/xhprof/doc.html#ui_setup)
provided to browse the data or you can just process the data directly. I'm
working on [vBulletin](http://www.vbulletin.com/), so I integrated it into the
vBulletin debug output. If you're doing the processing yourself, the following
snippet is useful for converting the inclusive times returned by
`xhprof_disable()` to exclusive times.

```php
<?php
require_once('/path/to/xhprof/display/xhprof.php');
$profile_data_totals = array(); // Will contain data for the whole script
$profile_data_exclusive = xhprof_compute_flat_info($profile_data, $profile_data_totals);
?>
```

That's pretty much it. For anything more than that, refer to the [XHProf
documentation](http://web.archive.org/web/20110514095512/http://mirror.facebook.net/facebook/xhprof/doc.html)
or have a dig through the XHProf and XHProf UI sources.
