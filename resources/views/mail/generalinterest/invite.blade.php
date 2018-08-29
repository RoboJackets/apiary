@component('mail::message')
Hello Future RoboJackets,

First, if you are one of the new freshmen starting your journey at Georgia Tech, RoboJackets would like to welcome you to the GT family. Your time here will be full of excitement and challenge, and we hope RoboJackets will be a part of that experience.

If you are receiving this email, you expressed interest in joining RoboJackets, the competitive robotics club at Georgia Tech.

# How to Join
To better acquaint you with our teams, we would like to invite you to our General Interest events. RoboJackets will be holding two General Interest (GI) meetings that will give you the details of our teams’ activities and how to join.

These events will feature FREE PIZZA and lots of robots!

# General Interest Meetings
We will be hosting two General Interest meetings this year. The two meetings cover the same content, and you should only attend one of the two. Both meetings are located in the [Howey Lecture Hall, L3](https://goo.gl/fJGRNe).

# RSVP
Please RSVP to the meeting you want to attend using the link below.

@component('mail::button', ['url' => $app_url . "/events/7/rsvp?source=" . $visit_token])
    RSVP for Wednesday, September 4 (6-8pm)
@endcomponent

@component('mail::button', ['url' => $app_url . "/events/8/rsvp?source=" . $visit_token])
    RSVP for Thursday, September 5 (6-8pm)
@endcomponent

# Can't Make It
Don't worry if you can't make it to either of these dates. We'll be sending out another email after the meeting with all of the information we presented.

# Contact Information

Thank you for your interest in joining RoboJackets. If you have any questions, feel free to contact our leadership team at [info@robojackets.org](mailto:info@robojackets.org).

We hope to see you soon!

The RoboJackets Leadership Team
@endcomponent