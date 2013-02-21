BootstraPHPed
=============

Originally made up as a simple PHP+Bootstrap web site, then took the form of a CMS, eventually became a framework.

WARNING: STILL WORK IN PROGRESS!!


FEATURES
 - division in sections and subsections
 - 2 layouts are available for every View: a one-column or a two-column (with a sidemenu) layout
 - authentication
 - authorization: fine granularity (down to subsections); subsections can inherit from their parent section the roles required to access them
 - configurable hierarchy of user roles
 - users may be organized (not recursively) in groups
 - admin section

DESIGN GOALS
 - low coupling [always inspiring me]
 - cleanness
 - robustness
 - extendability
 - MVC style

DEPENDENCIES
 - Bootstrap
 - jQuery
 - ADOdb
 - PHPass
 - log4php

TODO
 - a lot!

DOING
Started the conversion to OO:
 - improved the directory structure: now it's much more logic and meaningful
 - focused on the User and Element classes, which can be considered done


PS: although quite far from the end, I've learnt a lot so far, while building my own framework.
I think that building one's own MVC framework is a step everyone should make, before studying a real-world one.
So actually now I think I'm ready to.
Basically, this is the reason why I am considering quitting this project and spend my time studying ZF, instead.
Or Symfony.
Or CodeIgniter.

Gosh, so many out there!

You know what? I think I'll go with Django! :P
I mean, really... I have already started the tutorial
