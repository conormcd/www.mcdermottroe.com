+++
title = "CV"
template = "cv.html"
+++

<style>
.cv-company-name {
  margin-top: 1em;
  font-size: 1.25em;
  font-weight: 600;
}
.cv-role {
  margin-left: 2em;
  margin-top: 1em;
}
.cv-role-name {
  font-weight: 600;
}
.cv-role-description {
  font-size: 14px;
}
</style>

# About

I'm a software developer with experience in building and operating complex and
distributed systems. My most recent experience is in Clojure, but I have prior
professional experience with Java, Ruby, Python, PHP and Perl too. While I can
do and have done work on UI and operations teams, my main focus is on all the
systems that lie between those.

# Experience

<div class="cv-company">
  <div class="cv-company-name">CircleCI</div>

  <div class="cv-role">
    <span class="cv-role-name">Senior Staff Engineer</span>
    <span class="cv-role-dates">March 2015 - Present</span>
    <div class="cv-role-description">I am currently operating as the "solver"
      for the Core group of engineering teams at CircleCI. This means that I
      float between the teams in that group helping them solve their most
      complex or urgent problems. Sometimes that requires me to write code but
      more often it means that I spend time discussing the problem with the
      team and helping them break it down into manageable and measurable
      tasks. I also participate in incident response for those teams, using my
      broader understanding of the CircleCI product to shorten and reduce the
      impact of outages.
    </div>
    <div class="cv-role-description">Before my current role, I have worked
      across most parts of CircleCI's product including things like upgrading
      our AWS and Docker integrations, completing partial migrations of
      systems, improving our GitHub and Bitbucket API usage, modernising our
      permissions subsystem, designing and implementing our common tools and
      libraries, detecting and countering abusive behaviour, improving our
      on-premises product and sustaining legacy systems.
    </div>
  </div>
</div>

<div class="cv-company">
  <div class="cv-company-name">Engine Yard</div>

  <div class="cv-role">
    <span class="cv-role-name">Software Developer</span>
    <span class="cv-role-dates">November 2011 - March 2015</span>
    <div class="cv-role-description">I initially worked on the Orchestra PHP
      platform but I moved on to work on Engine Yard's other products. This
      mostly involved writing Chef, Ruby and some shell but I also wrote some
      Python and PHP. I created packages for Ubuntu and worked on a very large
      Rails application.
    </div>
  </div>
</div>

<div class="cv-company">
  <div class="cv-company-name">Distilled Media</div>

  <div class="cv-role">
    <span class="cv-role-name">Head of Infrastructure</span>
    <span class="cv-role-dates">July 2011 - November 2011</span>
    <div class="cv-role-description">I was responsible for improving and
      maintaining the infrastructure underlying Adverts.ie, Boards.ie, Daft.ie
      and TheJournal.ie. In practice, this meant sharing my knowledge of
      Capistrano, Puppet and Munin and trying to exploit commonalities between
      the sites to avoid duplication of effort.
    </div>
  </div>

  <div class="cv-role">
    <span class="cv-role-name">Senior Developer - Boards.ie</span>
    <span class="cv-role-dates">May 2009 - July 2011</span>
    <div class="cv-role-description">I was a combination sysadmin, developer
      and DBA for <a href="https://www.boards.ie">Boards.ie</a>. Here are some
      things I did in this job:
    </div>
    <div class="cv-role-description">
      <ul>
        <li>I made lots of speed improvements to Boards.ie, sometimes by
          improving the code but mostly by tweaking or avoiding database
          queries.</li>
        <li>I built an API for Boards.ie which is used to drive
          touch.boards.ie and the Boards.ie iPhone app.</li>
        <li>I built a redundant pair of firewall/reverse proxy cache machines
          using Varnish, Pound, CARP and pf.</li>
        <li>In cooperation with Ross Duggan, I re-worked the setup for
          Boards.ie to upgrade out of date hardware and operating systems,
          remove points of failure and improve the security of the entire
          system. A high-level overview is <a
          href="http://web.archive.org/web/20110812142318/http://blog.boards.ie/2010/05/27/cleaning-up-a-few-years-of-incremental-infrastructure-growth/">on
          the Boards.ie blog</a>.
        <li>I moved all the configuration of the machines to Puppet and all
          the code & configuration data to git.</li>
      </ul>
    </div>
    <div class="cv-role-description">I mostly worked with PHP, Perl and MySQL
      but I did some stuff in JavaScript and sh where appropriate.
    </div>
  </div>
</div>

<div class="cv-company">
  <div class="cv-company-name">McConnells Digital</div>

  <div class="cv-role">
    <span class="cv-role-name">Technical Manager</span>
    <span class="cv-role-dates">August 2008 - May 2009</span>
    <div class="cv-role-description">McConnells was a large Irish advertising
      company and in mid 2008 they re-launched their online advertising
      section as McConnells Digital. I was hired as the all-round tech person
      for this department. I project managed banner and e-mail campaigns,
      helped with pitches for new campaigns, sourced designers and developers
      and wrote some code. When I wrote code I worked in PHP (MediaWiki &
      Wordpress), Visual Basic (for a client website), JavaScript (mostly
      jQuery & YUI) and ActionScript (helping the banner creators).
    </div>
  </div>
</div>

<div class="cv-company">
  <div class="cv-company-name">Edgespace</div>

  <div class="cv-role">
    <span class="cv-role-name">Co-founder, Software Developer, Sysadmin</span>
    <span class="cv-role-dates">June 2004 - Jan 2008</span>
    <div class="cv-role-description">Along with several friends I was a
      co-founder of Edgespace, a software development company based in Dublin.
      We worked as contract developers until March 2007 and then made several
      unsuccessful attempts to build our own products before winding the
      company up. While at Edgespace, I also ran our company infrastructure
      including DHCP, DNS, IMAP, SMTP, Subversion and Bugzilla servers.
    </div>
  </div>

  <div class="cv-role">
    <span class="cv-role-name">Software Developer - PassMark Security</span>
    <span class="cv-role-dates">April 2006 - March 2007</span>
    <div class="cv-role-description">Along with one of my colleagues I
      inherited a collection of ksh, Perl and Java which used MySQL to
      generate PDF reports from hundreds of megabytes a day of log files
      generated by PassMark’s clients. We slowly converted those into a
      robust, flexible and fast system which handled tens of gigabytes of log
      files a day with capacity to spare. The resulting system was built using
      sh, Java and Oracle and was deployed and managed in accordance with SAS
      70 and Sarbanes-Oxley regulations. When on-site in California I also
      helped interview candidates for software development roles within
      PassMark.
    </div>
  </div>

  <div class="cv-role">
    <span class="cv-role-name">Software Developer - Securify</span>
    <span class="cv-role-dates">August 2004 - March 2006</span>
    <div class="cv-role-description">Edgespace was initially hired to automate
      large chunks of the QA process for Securify’s SecurVantage network
      monitoring products. We wrapped the WWW::Mechanize Perl module to
      provide us and Securify’s developers with a set of primitives for
      exercising the user interface and driving their product. We then took
      over the maintenance of SecurVantage doing everything from high-level
      support to bug triage and repair to rolling minor releases. While at
      Securify I wrote mostly in Perl and Java but also patched bits and
      pieces of C and C++ code and did horrible things with make, sh and rpm.
      I also interviewed candidates for development and QA roles within
      Securify.
    </div>
  </div>
</div>

<div class="cv-company">
  <div class="cv-company-name">Bitbuzz</div>

  <div class="cv-role">
    <span class="cv-role-name">Software Developer</span>
    <span class="cv-role-dates">September 2003 - April 2004</span>
    <div class="cv-role-description">I joined Bitbuzz shortly before they
      launched and was their first (albeit part-time) employee. I wrote the
      first version of the captive portal that Bitbuzz users use to access
      their WiFi system. This involved glueing NoCatAuth, a credit card
      processor, an SMS payment gateway and the a WiFi roaming authentication
      system together using PHP and PostgreSQL.
    </div>
  </div>
</div>

# Education

I've a B.A. (Mod) Computer Science from <a href="https://www.tcd.ie/">TCD</a>.
