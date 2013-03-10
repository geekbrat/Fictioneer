<?php

// Default query strings used throughout the script.  You may need to alter these to bridge to other scripts or databases.

define ("_UIDFIELD", "author.uid");  // Do not change the aliasing (the "author." part)!
define ("_PENNAMEFIELD", "author.penname");  // Do not change the aliasing (the "author." part)!
define ("_EMAILFIELD", "author.email");  // Do not change the aliasing (the "author." part)!
define ("_PASSWORDFIELD", "author.password"); //  Do not change the aliasing (the "author." part)!
define ("_AUTHORTABLE", $tableprefix."fanfiction_authors as author"); // Do not change the aliasing (the "as author" part)!

define ("_STORYQUERY",  "SELECT stories.*, "._PENNAMEFIELD." as penname, UNIX_TIMESTAMP(stories.date) as date, UNIX_TIMESTAMP(stories.updated) as updated FROM ("._AUTHORTABLE.", ".$tableprefix."fanfiction_stories as stories) WHERE "._UIDFIELD." = stories.uid AND stories.validated > 0 ");
define ("_STORYCOUNT", "SELECT count(sid) FROM ".$tableprefix."fanfiction_stories as stories WHERE validated > 0");
define ("_SERIESQUERY", "SELECT series.*, "._PENNAMEFIELD." as penname FROM "._AUTHORTABLE.", ".$tableprefix."fanfiction_series as series WHERE "._UIDFIELD." = series.uid ");
define ("_SERIESCOUNT", "SELECT COUNT(seriesid) FROM ".$tableprefix."fanfiction_series as series ");
define ("_MEMBERLIST", "SELECT count( stories.sid ) as stories, "._PENNAMEFIELD." as penname, "._UIDFIELD." as uid FROM "._AUTHORTABLE." LEFT JOIN ".$tableprefix."fanfiction_authorprefs AS ap ON "._UIDFIELD." = ap.uid LEFT JOIN ".$tableprefix."fanfiction_stories AS stories ON stories.validated > 0 AND (FIND_IN_SET("._UIDFIELD.", stories.coauthors) > 0 OR stories.uid = "._UIDFIELD.") ");
define ("_MEMBERCOUNT", "SELECT COUNT(DISTINCT "._UIDFIELD.") FROM "._AUTHORTABLE." LEFT JOIN ".$tableprefix."fanfiction_stories as stories ON stories.validated > 0 AND (FIND_IN_SET("._UIDFIELD.", stories.coauthors) > 0 OR stories.uid = "._UIDFIELD.")  LEFT JOIN ".$tableprefix."fanfiction_authorprefs as ap ON "._UIDFIELD." = ap.uid");
?>