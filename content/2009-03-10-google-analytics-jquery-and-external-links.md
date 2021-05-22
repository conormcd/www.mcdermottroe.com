+++
title = "Google Analytics, jQuery and external links"
template = "page.html"
date = 2009-03-10T12:00:00Z

[taxonomies]
tags = ["Tech"]
+++

Want to add [Google Analytics](http://www.google.com/analytics/) tracking to
all the non-HTML resources on your site? How about the outbound links to other
websites? If you're like me, you've considered it and then rejected it for
being too annoying to add the tracking code manually.

Time for [jQuery](http://jquery.com/) to come to the rescue. By adding the
tracking code automatically to the links that need it you can avoid the hassle
of editing all of your existing pages.

```javascript
$(document).ready(
    function () {
        $("a").click(
            function () {
                var protocol = this.protocol;
                var link = $(this).attr("href");
                if (link.substring(0, protocol.length) == protocol) {
                    pageTracker._trackPageview('/exit/' + escape(link));
                } else {
                    link = this.pathname;
                    if  (
                            (link.substring(link.length - 1) != "/") &&
                            (link.substring(link.length - 4) != ".php")
                        )
                    {
                        pageTracker._trackPageview(link);
                    }
                }
            }
        );
    }
);
```

The following caveats apply:

1. You need to use the newer version of the Google Analytics tracking code
   (ga.js, not urchin.js).
2. It assumes that all links pointing to a URL ending in / or .php have
   tracking code installed. If you have other readily identifiable URLs that
   you want to exclude then exclude them in the obvious place above.

Spot anything that looks wrong? Does this not work on your browser of choice?
Let me know.
